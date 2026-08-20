<?php
/* 物料管理（统一入口，左侧分类树 + 右侧详情 + 右键菜单 + 弹窗）
 * 集成：料号维护 / 料号修改 / 料号整批上传 三个老功能（已合并为右键菜单项）
 * 参照：DocFileCenter.php（左侧树/右侧卡）+ BOMSetup.php（lhgdialog 弹窗样式）
 * 说明：3 个老表单采用 ?embed=1 弹窗模式（保留原业务逻辑，仅剥皮）
 */
include('includes/session.inc');
$Title = _('物料管理');
$ViewTopic = '物料管理';
$BookMark = '物料管理';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

/* ============================================================
 * 1. 入参：当前选中物料、搜索关键字、操作动作
 * ============================================================ */
$ItemNo = '';
if (isset($_GET['item_no'])) {
    $ItemNo = trim($_GET['item_no']);
} elseif (isset($_POST['item_no'])) {
    $ItemNo = trim($_POST['item_no']);
}
if ($ItemNo != '' && !preg_match('/^[A-Za-z0-9_.\-]+$/', $ItemNo)) {
    $ItemNo = '';
}
$SearchFilter = isset($_GET['q']) ? trim($_GET['q']) : '';
$autoOpen = isset($_GET['auto_open']) ? intval($_GET['auto_open']) : 0; // 搜索定位后弹右键菜单用
$CatFilter = isset($_GET['cat']) ? trim($_GET['cat']) : ''; // 当前选中的分类（点击左侧分类文件夹）

/* ============================================================
 * 2. 查当前选中物料 + BOM + 图档
 * ============================================================ */
$ItemInfo = null;
$itemBoms = array(); // 顶层 BOM 列表
$itemFiles = array(); // 图档列表
if ($ItemNo != '') {
    $resItem = DB_query(
        "SELECT i.item_id, i.item_no, i.item_name, i.item_desc, i.item_category1, i.item_type, i.item_use,
                i.units, i.safe_qty, i.unit_price, i.disable_flag, i.effective_date, i.creation_date,
                i.created_by, i.last_updated_by, i.last_update_date, i.sub_code, i.sub_locator,
                (SELECT COUNT(*) FROM bom_headers_all WHERE assembly_item_no=i.item_no) AS bom_count
         FROM sf_item_no i WHERE i.item_no='" . $ItemNo . "'",
        $db
    );
    if (DB_num_rows($resItem) > 0) {
        $ItemInfo = DB_fetch_array($resItem);

        // 该物料的 BOM 头（多版本）
        $resB = DB_query(
            "SELECT bom_header_id, version, status, is_current, creation_date, last_update_date
             FROM bom_headers_all WHERE assembly_item_no='" . $ItemNo . "'
             ORDER BY is_current DESC, version DESC",
            $db
        );
        while ($row = DB_fetch_array($resB)) {
            $itemBoms[] = $row;
        }

        // 该物料的图档
        $resF = DB_query(
            "SELECT file_patch, file_name, creation_date, created_by FROM sf_item_no_file WHERE item_no='" . $ItemNo . "' ORDER BY creation_date DESC LIMIT 20",
            $db
        );
        while ($row = DB_fetch_array($resF)) {
            $itemFiles[] = $row;
        }
    } else {
        $ItemNo = ''; // 物料不存在，忽略
    }
}

/* ============================================================
 * 3. 查左侧分类树（按 item_category1 聚合，全部物料含禁用）
 * ============================================================ */
$treeGroups = array();
$treeHasAny = false;
$sqlTree = "SELECT i.item_no, i.item_name, i.item_category1, i.disable_flag
            FROM sf_item_no i WHERE 1=1";
if ($SearchFilter != '') {
    $safeQ = str_replace(array('%', '_'), array('\\%', '\\_'), $SearchFilter);
    $sqlTree .= " AND (i.item_no LIKE '%" . $safeQ . "%' OR i.item_name LIKE '%" . $safeQ . "%')";
}
$sqlTree .= " ORDER BY i.item_category1, i.item_no";
$resTree = DB_query($sqlTree, $db);
while ($row = DB_fetch_array($resTree)) {
    $cat = $row['item_category1'] != '' ? $row['item_category1'] : '未分类';
    if (!isset($treeGroups[$cat])) {
        $treeGroups[$cat] = array();
    }
    $treeGroups[$cat][] = $row;
    $treeHasAny = true;
}
ksort($treeGroups);

$CurrentCat = '';
if ($ItemInfo) {
    $CurrentCat = $ItemInfo['item_category1'];
} elseif ($CatFilter != '') {
    $CurrentCat = $CatFilter;
}

$totalItems = 0;
foreach ($treeGroups as $g) { $totalItems += count($g); }
?>
<link href="<?php echo $RootPath; ?>/css/bom_style.css" rel="stylesheet" type="text/css"/>
<script src="<?php echo $RootPath; ?>/javascript/jquery-1.7.2.min.js"></script>
<script src="<?php echo $RootPath; ?>/JXC/javascript/lhgdialog.min.js?self=true&skin=chrome"></script>

<style type="text/css">
/* ====== 顶层布局 ====== */
.mm-layout{display:flex;width:100%;min-height:600px;gap:8px;margin-top:6px}
.mm-left{width:340px;border:1px solid #c5c5c5;background:#f7f9fc;border-radius:4px;padding:12px;display:flex;flex-direction:column;max-height:78vh}
.mm-right{flex:1;border:1px solid #c5c5c5;border-radius:4px;padding:14px;background:#fff;min-width:0;display:flex;flex-direction:column;overflow:auto;max-height:78vh}

/* ====== 左侧头部 + 工具按钮（参照 DocFileCenter） ====== */
.mm-left-head{display:flex;justify-content:space-between;align-items:center;padding-bottom:10px;margin-bottom:10px;border-bottom:1px solid #d9d9d9;font-size:14px;font-weight:bold;color:#333}
.mm-actions{display:flex;gap:6px;flex-wrap:wrap}
.mm-actions button{background:#fff;color:#555;border:1px solid #c5c5c5;padding:4px 10px;border-radius:3px;cursor:pointer;font-size:12px;font-weight:normal}
.mm-actions button:hover{background:#f0f0f0}
.mm-actions button.primary{background:#4caf50;color:#fff;border-color:#4caf50}
.mm-actions button.primary:hover{background:#43a047;border-color:#43a047}

/* ====== 搜索框 ====== */
.mm-search-row{display:flex;gap:6px;margin-bottom:10px;align-items:center}
.mm-search-row input[type=text]{flex:1;padding:5px 8px;border:1px solid #c8ced6;border-radius:3px;font-size:12px;min-width:0}
.mm-search-row input[type=text]:focus{outline:none;border-color:#2196F3;box-shadow:0 0 0 2px rgba(33,150,243,0.15)}
.mm-search-row button.primary{background:#4caf50;color:#fff;border-color:#4caf50;padding:5px 12px;border-radius:3px;cursor:pointer;font-size:12px}
.mm-search-row a{font-size:12px;color:#888;text-decoration:none}

/* ====== 树状结构（直接复用 BOMSetup .bom-tree 规范） ====== */
.mm-left-body{flex:1;overflow:auto;padding-bottom:4px}
.bom-tree, .bom-tree ul{list-style:none;margin:0;padding:0}
.bom-tree{font-size:12px;line-height:22px;color:#333;user-select:none}
.bom-tree ul.bom-sub{margin:0;padding:0}
.bom-node{display:flex;flex-direction:column;min-width:0}
.bom-row{display:flex;align-items:center;height:22px;white-space:nowrap;cursor:pointer;min-width:0;padding-right:4px}
.bom-row:hover{background:#f0f4f8}
.bom-node.active>.bom-row{background:#cfe3ff}
.bom-node.active>.bom-row .lbl{font-weight:bold;color:#0d47a1}
.bom-glyphs{display:inline-flex;align-items:center;height:22px;flex-shrink:0}
.tree-cell{width:20px;height:22px;position:relative;flex-shrink:0;box-sizing:border-box}
.indent-cell .tree-vbar{position:absolute;left:50%;top:0;bottom:0;width:0;border-left:1px dotted #b0b8c0;transform:translateX(-50%)}
.indent-cell.no-sibling .tree-vbar{bottom:11px}
.node-cell{position:relative}
.node-cell .tree-hbar{position:absolute;left:0;right:13px;top:11px;border-top:1px dotted #999}
.tw{position:absolute;left:auto;right:2px;top:50%;transform:translateY(-50%);width:11px;height:11px;line-height:9px;border:1px solid #808890;background:#fff;font-size:9px;text-align:center;cursor:pointer;color:#333;z-index:2;font-family:"Courier New",monospace}
.tw:hover{border-color:#2196F3;color:#2196F3}
.leaf-node .node-cell .tw{display:none}
.icon-label{display:flex;align-items:center;gap:0;margin-left:2px;flex:0 0 auto}
.bom-row .lbl{flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;min-width:0;padding:0 0 0 2px;line-height:22px}
.bom-node.leaf-node>.bom-row>.lbl{color:#555}
.cat-icon{display:inline-block;width:14px;height:14px;background:#FFC107;border:1px solid #F57C00;border-radius:2px;margin-right:4px;vertical-align:middle}
.item-icon{display:inline-block;width:12px;height:12px;background:#e3f2fd;border:1px solid #2196F3;border-radius:50%;margin-right:6px;vertical-align:middle}
.item-disabled{opacity:0.5;text-decoration:line-through}

/* ====== 右键菜单（参照 BOMSetup 右键菜单） ====== */
#ctxMenu{position:fixed;z-index:10000;background:#fff;border:1px solid #c5c5c5;border-radius:4px;box-shadow:0 4px 16px rgba(0,0,0,0.18);min-width:200px;padding:6px 0;display:none}
#ctxMenu div{padding:8px 18px;font-size:13px;cursor:pointer;color:#333}
#ctxMenu div:hover{background:#e3f2fd;color:#1976D2}
#ctxMenu div.sep{height:1px;margin:4px 0;background:#e8e8e8;padding:0}
#ctxMenu div.disabled{color:#bbb;cursor:not-allowed}
#ctxMenu div.disabled:hover{background:transparent;color:#bbb}

/* ====== 右侧：工具栏 + Tab + 表格 ====== */
.mm-toolbar{display:flex;align-items:center;gap:8px;padding:8px 12px;background:#fafbfc;border:1px solid #e4e9f0;border-radius:3px;margin-bottom:10px;flex-wrap:wrap}
.mm-toolbar-stat{margin-left:auto;font-size:12px;color:#888}
.mm-tool-btn{display:inline-flex;align-items:center;gap:4px;padding:4px 12px;background:#fff;border:1px solid #c5c5c5;border-radius:3px;color:#555;font-size:12px;line-height:1.6;cursor:pointer;transition:all .15s;text-decoration:none}
.mm-tool-btn:hover{text-decoration:none;color:#333;border-color:#999}
.mm-tool-btn-blue{border-color:#1976D2;color:#1976D2}
.mm-tool-btn-blue:hover{background:#E3F2FD;color:#1565C0;border-color:#1565C0}
.mm-tool-btn-green{border-color:#43A047;color:#2e7d32}
.mm-tool-btn-green:hover{background:#E8F5E9;color:#1b5e20;border-color:#2e7d32}
.mm-tool-btn-orange{border-color:#FB8C00;color:#e65100}
.mm-tool-btn-orange:hover{background:#FFF3E0;color:#bf360c;border-color:#e65100}
.mm-tool-btn-purple{border-color:#8E24AA;color:#6a1b9a}
.mm-tool-btn-purple:hover{background:#F3E5F5;color:#4a148c;border-color:#6a1b9a}
.mm-tool-btn-gray{border-color:#9e9e9e;color:#616161}
.mm-tool-btn-gray:hover{background:#f5f5f5;color:#333;border-color:#616161}
.mm-tabs{display:flex;border-bottom:2px solid #2196F3;margin-bottom:0;align-items:center}
.mm-tab{padding:8px 18px;cursor:pointer;font-size:14px;color:#555;background:#f5f5f5;border:1px solid #d9d9d9;border-bottom:none;border-radius:4px 4px 0 0;margin-right:4px;position:relative;top:2px;font-weight:500}
.mm-tab:hover{background:#e3f2fd;color:#1976D2}
.mm-tab.active{background:#2196F3;color:#fff;border-color:#2196D2;font-weight:bold}
.mm-tab .ct{margin-left:4px;font-size:11px;opacity:0.85;font-weight:normal}
.mm-tabs-panel{display:none;padding-top:14px}
.mm-tabs-panel.active{display:block}
.mm-table{width:100%;border-collapse:separate;border-spacing:0;font-size:12px;background:#fff;border:1px solid #e0e0e0;border-radius:3px;overflow:hidden}
.mm-table th{background:#2196F3;color:#fff;font-weight:bold;padding:8px 10px;text-align:center;border-right:1px solid #1e88e5;white-space:nowrap}
.mm-table th:last-child{border-right:none}
.mm-table td{padding:8px 12px;text-align:left;border-bottom:1px solid #e8e8e8;border-right:1px solid #e8e8e8;color:#333}
.mm-table td:last-child{border-right:none}
.mm-table tr:last-child td{border-bottom:none}
.mm-table tr:hover td{background:#f5f9ff}
.mm-table-scroll{overflow-x:auto;width:100%}
.mm-table-row th, .mm-table-row td{white-space:nowrap}
/* 字段显示控制（嵌入 Tab 栏右侧） */
.mm-tabs-extra{margin-left:auto;position:relative;display:flex;align-items:center;padding-right:6px;height:100%}
.mm-col-btn{display:inline-flex;align-items:center;gap:3px;padding:3px 11px;background:#fff;border:1px solid #1976D2;color:#1976D2;border-radius:3px;font-size:12px;cursor:pointer;line-height:1.6;transition:all .15s}
.mm-col-btn:hover{background:#E3F2FD}
.mm-col-menu{display:none;position:absolute;right:0;top:36px;z-index:50;width:190px;max-height:340px;overflow-y:auto;background:#fff;border:1px solid #d0d7de;border-radius:4px;box-shadow:0 4px 14px rgba(0,0,0,.15);padding:8px 10px;text-align:left}
.mm-col-menu.show{display:block}
.mm-col-menu-head{font-size:12px;font-weight:bold;color:#333;padding-bottom:6px;margin-bottom:6px;border-bottom:1px solid #eee}
.mm-col-opt{display:block;font-size:12px;color:#444;padding:3px 2px;cursor:pointer;white-space:nowrap}
.mm-col-opt:hover{background:#f1f8ff}
.mm-col-opt input{vertical-align:-1px;margin-right:5px}
.mm-col-menu-foot{margin-top:6px;padding-top:6px;border-top:1px solid #eee;font-size:12px}
.mm-col-menu-foot a{color:#1976D2;margin-right:10px;text-decoration:none}
.mm-col-menu-foot a:hover{text-decoration:underline}
.mm-status-ok{display:inline-block;padding:2px 8px;border-radius:3px;background:#e8f5e9;color:#2e7d32;border:1px solid #c8e6c9;font-size:11px}
.mm-status-stop{display:inline-block;padding:2px 8px;border-radius:3px;background:#fafafa;color:#888;border:1px solid #e0e0e0;font-size:11px;text-decoration:line-through}
.mm-empty{padding:30px;text-align:center;color:#999;font-size:13px;background:#fafbfc;border:1px dashed #d9d9d9;border-radius:4px}
.mm-tree-sum{font-size:11px;color:#888;padding-top:6px;border-top:1px dashed #d9d9d9}
.mm-tree-sum b{color:#1976D2}
/* ====== 分类视图（点击分类文件夹进入） ====== */
.mm-catview-search{display:flex;align-items:center;gap:8px;padding:10px 12px;background:#f5f9fd;border:1px solid #d6e4f0;border-radius:4px;margin-bottom:12px}
.mm-catview-search input{flex:1;height:30px;padding:4px 10px;border:1px solid #c5d3e0;border-radius:3px;font-size:13px;outline:none}
.mm-catview-search input:focus{border-color:#1976D2;box-shadow:0 0 0 2px rgba(25,118,210,0.15)}
.mm-catview-count{font-size:12px;color:#666;background:#fff;padding:4px 10px;border:1px solid #d6e4f0;border-radius:3px;white-space:nowrap}
.mm-catview-table{flex:1;overflow:auto;max-height:60vh}
.mm-catview-table .mm-table th{position:sticky;top:0;z-index:2}
.bom-tool-mini{display:inline-block;padding:4px 12px;background:#fff;border:1px solid #1976D2;color:#1976D2;border-radius:3px;font-size:12px;text-decoration:none}
.bom-tool-mini:hover{background:#E3F2FD}
.cat-item-link{color:#1976D2;text-decoration:none;font-weight:500}
.cat-item-link:hover{text-decoration:underline}
.cat-row{cursor:default}
.cat-row:hover td{background:#f1f8ff !important}
tr.cat-row.item-disabled td{color:#999}
tr.cat-row.item-disabled .cat-item-link{color:#999;text-decoration:line-through}
</style>

<div class="mm-layout">

    <!-- ===================== 左侧分类树 ===================== -->
    <div class="mm-left">
        <div class="mm-left-head">
            <span>📁 物料分类</span>
            <div class="mm-actions">
                <button type="button" onclick="MmExpandAll(true)">展开</button>
                <button type="button" onclick="MmExpandAll(false)">折叠</button>
                <button type="button" class="primary" onclick="MmOpenAddDialog()">+ 新增</button>
            </div>
        </div>
        <form id="mmSearchForm" method="GET" action="<?php echo $RootPath; ?>/MaterialManage.php" onsubmit="return true;">
            <div class="mm-search-row">
                <input type="text" id="mmQ" name="q" value="<?php echo htmlspecialchars($SearchFilter); ?>" placeholder="搜索料号 / 名称…" />
                <button type="submit" class="primary">查询</button>
                <?php if ($SearchFilter != '') { ?>
                    <a href="<?php echo $RootPath; ?>/MaterialManage.php">清空</a>
                <?php } ?>
            </div>
        </form>
        <div class="mm-left-body">
            <?php if (!$treeHasAny) { ?>
                <div style="padding:14px;color:#888;font-size:12px;text-align:center;">
                    <?php echo $SearchFilter != '' ? '未找到匹配的物料' : '暂无物料数据'; ?>
                </div>
            <?php } else { ?>
                <ul class="bom-tree" id="mmTree">
                    <?php foreach ($treeGroups as $cat => $items) {
                        $isCurrentCat = ($CurrentCat != '' && $CurrentCat == $cat);
                        $openByDefault = ($SearchFilter != '' || $isCurrentCat);
                    ?>
                    <li class="bom-node top-level<?php echo $openByDefault ? '' : ' collapsed'; ?>" data-cat="<?php echo htmlspecialchars($cat); ?>">
                        <div class="bom-row" onclick="MmOpenCat('<?php echo htmlspecialchars(addslashes($cat)); ?>')">
                            <span class="bom-glyphs">
                                <span class="tree-cell node-cell">
                                    <input type="checkbox">
                                    <span class="tw" onclick="event.stopPropagation(); MmToggleCat(this.parentNode.parentNode.parentNode);"><?php echo $openByDefault ? '-' : '+'; ?></span>
                                </span>
                            </span>
                            <span class="icon-label">
                                <span class="cat-icon"></span>
                                <span class="lbl" title="<?php echo htmlspecialchars($cat); ?>"><?php echo htmlspecialchars($cat); ?>（<?php echo count($items); ?>）</span>
                            </span>
                        </div>
                        <?php if (count($items) > 0) { ?>
                        <ul class="bom-sub" <?php echo $openByDefault ? '' : 'style="display:none"'; ?>>
                            <?php foreach ($items as $idx => $it):
                                $isLast = ($idx == count($items) - 1);
                                $selected = ($it['item_no'] == $ItemNo);
                                $isDisabled = (isset($it['disable_flag']) && $it['disable_flag'] != 'N' && $it['disable_flag'] != 'Y');
                            ?>
                            <li class="bom-node leaf-node<?php echo $selected ? ' active' : ''; ?><?php echo $isDisabled ? ' item-disabled' : ''; ?>" data-item="<?php echo htmlspecialchars($it['item_no']); ?>">
                                <div class="bom-row" onclick="MmSelectItem('<?php echo htmlspecialchars(addslashes($it['item_no'])); ?>')" oncontextmenu="MmShowContext(event, '<?php echo htmlspecialchars(addslashes($it['item_no'])); ?>', '<?php echo htmlspecialchars(addslashes($it['item_name'])); ?>', <?php echo $isDisabled ? 0 : 1; ?>); return false;">
                                    <span class="bom-glyphs">
                                        <span class="tree-cell indent-cell<?php echo $isLast ? ' no-sibling' : ''; ?>"><span class="tree-vbar"></span></span>
                                        <span class="tree-cell node-cell"><span class="tree-hbar"></span></span>
                                    </span>
                                    <span class="icon-label">
                                        <span class="item-icon"></span>
                                        <span class="lbl" title="<?php echo htmlspecialchars($it['item_no'] . ' / ' . $it['item_name']); ?>">
                                            <?php echo htmlspecialchars($it['item_no']); ?> <?php echo htmlspecialchars(mb_substr($it['item_name'], 0, 14)); ?>
                                        </span>
                                    </span>
                                </div>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                        <?php } ?>
                    </li>
                    <?php } ?>
                </ul>
            <?php } ?>
        </div>
        <div class="mm-tree-sum">
            共 <b><?php echo $totalItems; ?></b> 个物料 / <b><?php echo count($treeGroups); ?></b> 个分类
        </div>
    </div>

    <!-- ===================== 右侧主区 ===================== -->
    <div class="mm-right">
        <?php if ($ItemInfo === null) {
            if ($CatFilter != '' && isset($treeGroups[$CatFilter])) {
                // ====== 分类列表视图（点击分类文件夹进入） ======
                $CategoryItems = $treeGroups[$CatFilter];
                $TypeMap = array('M' => '原材料', 'B' => '半成品', 'F' => '成品', 'P' => '采购件');
        ?>
            <div class="mm-toolbar">
                <span class="version-tag">📂 分类：<?php echo htmlspecialchars($CatFilter); ?></span>
                <span class="bom-link" style="color:#666;font-size:12px">共 <b style="color:#1976D2"><?php echo count($CategoryItems); ?></b> 个物料</span>
                <span style="flex:1"></span>
                <a class="bom-tool-mini" href="<?php echo $RootPath; ?>/MaterialManage.php">← 返回全部</a>
            </div>
            <div class="mm-catview-search">
                <input type="text" id="catFilter" placeholder="过滤当前分类下的料号 / 名称 / 规格…（实时筛选）" />
                <span class="mm-catview-count" id="catFilterCount">显示 0 / 共 0</span>
            </div>
            <div class="mm-catview-table">
            <table class="mm-table" id="catTable">
                <thead>
                <tr>
                    <th width="40">#</th>
                    <th width="120">料号</th>
                    <th>名称</th>
                    <th>规格型号</th>
                    <th width="80">类型</th>
                    <th width="70">用途</th>
                    <th width="60">单位</th>
                    <th width="70">状态</th>
                </tr>
                </thead>
                <tbody id="catTableBody">
                <?php $rowIdx = 1; foreach ($CategoryItems as $it):
                    $isDisabled = (isset($it['disable_flag']) && $it['disable_flag'] != 'N' && $it['disable_flag'] != 'Y');
                    $q = strtolower($it['item_no'] . ' ' . $it['item_name'] . ' ' . $it['item_desc']);
                    $status = ($it['disable_flag'] == 'N' ? '启用' : '停用');
                    $statusCls = ($it['disable_flag'] == 'N' ? 'mm-status-ok' : 'mm-status-stop');
                    $tp = isset($TypeMap[$it['item_type']]) ? $TypeMap[$it['item_type']] : $it['item_type'];
                    $iu = ($it['item_use'] == 'Y' ? '研发' : '生产');
                ?>
                <tr data-q="<?php echo htmlspecialchars($q); ?>" class="cat-row<?php echo $isDisabled ? ' item-disabled' : ''; ?>">
                    <td style="text-align:center"><?php echo $rowIdx++; ?></td>
                    <td><a class="cat-item-link" href="<?php echo $RootPath; ?>/MaterialManage.php?item_no=<?php echo urlencode($it['item_no']); ?>" data-itemno="<?php echo htmlspecialchars($it['item_no']); ?>" data-itemname="<?php echo htmlspecialchars(addslashes($it['item_name'])); ?>" data-enabled="<?php echo $isDisabled ? 0 : 1; ?>"><?php echo htmlspecialchars($it['item_no']); ?></a></td>
                    <td><?php echo htmlspecialchars($it['item_name']); ?></td>
                    <td><?php echo htmlspecialchars($it['item_desc']); ?></td>
                    <td style="text-align:center"><?php echo htmlspecialchars($tp); ?></td>
                    <td style="text-align:center"><?php echo htmlspecialchars($iu); ?></td>
                    <td style="text-align:center"><?php echo htmlspecialchars($it['units']); ?></td>
                    <td style="text-align:center"><span class="<?php echo $statusCls; ?>"><?php echo $status; ?></span></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            </div>
            <script>
            (function(){
                var inp = document.getElementById('catFilter');
                var tbody = document.getElementById('catTableBody');
                var cntSpan = document.getElementById('catFilterCount');
                var rows = tbody ? tbody.querySelectorAll('tr.cat-row') : [];
                var total = rows.length;
                cntSpan.textContent = '显示 ' + total + ' / 共 ' + total;
                inp.value = '';
                inp.addEventListener('input', function(){
                    var q = this.value.trim().toLowerCase();
                    var show = 0;
                    for (var i = 0; i < rows.length; i++) {
                        var dq = rows[i].getAttribute('data-q') || '';
                        var match = (q === '' || dq.indexOf(q) !== -1);
                        rows[i].style.display = match ? '' : 'none';
                        if (match) show++;
                    }
                    cntSpan.textContent = '显示 ' + show + ' / 共 ' + total;
                });
                // 行点击进入详情；右键弹菜单（复用 MmShowContext）
                if (tbody) {
                    tbody.addEventListener('click', function(e){
                        var a = e.target.closest('a.cat-item-link');
                        if (a) return; // 链接自带跳转
                    });
                    tbody.addEventListener('contextmenu', function(e){
                        var tr = e.target.closest('tr.cat-row');
                        if (!tr) return;
                        var a = tr.querySelector('a.cat-item-link');
                        if (!a) return;
                        var itemNo = a.getAttribute('data-itemno');
                        var itemName = a.getAttribute('data-itemname');
                        var enabled = a.getAttribute('data-enabled') == '1' ? 1 : 0;
                        MmShowContext(e, itemNo, itemName, enabled);
                    });
                }
            })();
            </script>
        <?php } else { ?>
            <div style="padding:80px 20px;text-align:center;color:#888;">
                <p style="font-size:18px;margin:0 0 12px 0;color:#1976D2;">📦 请从左侧选择物料或分类以查看详情</p>
                <p style="font-size:13px;color:#aaa;margin:0;">左侧树按物料分类（item_category1）聚合</p>
                <p style="font-size:12px;color:#bbb;margin:8px 0 0 0;">• 点击物料 → 查看该物料的详情 / BOM / 图档<br>• 点击分类文件夹 → 查看该分类下所有物料<br>• 右键点击物料 → 弹出新建/维护/修改/上传菜单</p>
            </div>
        <?php }
        } else {
            $DisableFlag = isset($ItemInfo['disable_flag']) ? $ItemInfo['disable_flag'] : '';
            $IsActive = ($DisableFlag == 'N'); // N=启用（顺帆约定：非N=启用，N=停用）— 此处保留原状仅展示
            $Status = ($DisableFlag == 'N' ? '启用' : '停用');
            $StatusClass = ($DisableFlag == 'N' ? 'mm-status-ok' : 'mm-status-stop');
            $ItemType = isset($ItemInfo['item_type']) ? $ItemInfo['item_type'] : '';
            $ItemTypeName = array('M' => '原材料', 'B' => '半成品', 'F' => '成品', 'P' => '采购件');
            $TypeDisp = isset($ItemTypeName[$ItemType]) ? $ItemTypeName[$ItemType] : $ItemType;
            $ItemUse = isset($ItemInfo['item_use']) ? $ItemInfo['item_use'] : '';
            $ItemUseName = ($ItemUse == 'Y' ? '研发' : '生产');
            $ActiveTab = isset($_GET['tab']) ? intval($_GET['tab']) : 1;
            if ($ActiveTab < 1 || $ActiveTab > 3) $ActiveTab = 1;
        ?>
            <!-- 工具栏：DocFileCenter 风格白底彩边小方块按钮 -->
            <div class="mm-toolbar">
                <button class="mm-tool-btn mm-tool-btn-blue" type="button" onclick="MmOpenEditDialog('<?php echo htmlspecialchars(addslashes($ItemNo)); ?>')">✏ 修改物料</button>
                <button class="mm-tool-btn mm-tool-btn-green" type="button" onclick="window.open('<?php echo $RootPath; ?>/SegmentUpload.php?embed=1', '_blank')">⇧ 整批上传</button>
                <span class="mm-toolbar-stat">当前物料：<b><?php echo htmlspecialchars($ItemInfo['item_no']); ?></b> / <?php echo htmlspecialchars($ItemInfo['item_name']); ?></span>
            </div>

            <!-- Tab 切换：基本信息 / BOM / 图档 -->
            <div class="mm-tabs">
                <div class="mm-tab<?php echo $ActiveTab == 1 ? ' active' : ''; ?>" onclick="MmSwitchTab(1)">📋 基本信息</div>
                <div class="mm-tab<?php echo $ActiveTab == 2 ? ' active' : ''; ?>" onclick="MmSwitchTab(2)">📐 BOM 结构 <span class="ct">（<?php echo count($itemBoms); ?>）</span></div>
                <div class="mm-tab<?php echo $ActiveTab == 3 ? ' active' : ''; ?>" onclick="MmSwitchTab(3)">📎 图档 <span class="ct">（<?php echo count($itemFiles); ?>）</span></div>
                <?php if ($ActiveTab == 1) { ?>
                <div class="mm-tabs-extra">
                    <button class="mm-col-btn" type="button" onclick="MmToggleColMenu(event)">⚙ 字段</button>
                    <div class="mm-col-menu" id="mmColMenu">
                        <div class="mm-col-menu-head">显示字段（勾选显示）</div>
                        <label class="mm-col-opt"><input class="mm-col-cb" type="checkbox" checked data-col="item_no"> 料号编码</label>
                        <label class="mm-col-opt"><input class="mm-col-cb" type="checkbox" checked data-col="item_name"> 料号名称</label>
                        <label class="mm-col-opt"><input class="mm-col-cb" type="checkbox" checked data-col="item_id"> 内部ID</label>
                        <label class="mm-col-opt"><input class="mm-col-cb" type="checkbox" checked data-col="item_desc"> 规格型号</label>
                        <label class="mm-col-opt"><input class="mm-col-cb" type="checkbox" checked data-col="item_category1"> 物料分类</label>
                        <label class="mm-col-opt"><input class="mm-col-cb" type="checkbox" checked data-col="item_type"> 物料类型</label>
                        <label class="mm-col-opt"><input class="mm-col-cb" type="checkbox" checked data-col="status"> 状态</label>
                        <label class="mm-col-opt"><input class="mm-col-cb" type="checkbox" checked data-col="item_use"> 用途</label>
                        <label class="mm-col-opt"><input class="mm-col-cb" type="checkbox" checked data-col="units"> 单位</label>
                        <label class="mm-col-opt"><input class="mm-col-cb" type="checkbox" checked data-col="safe_qty"> 安全库存</label>
                        <label class="mm-col-opt"><input class="mm-col-cb" type="checkbox" checked data-col="unit_price"> 单价</label>
                        <label class="mm-col-opt"><input class="mm-col-cb" type="checkbox" checked data-col="sub_code"> 仓库</label>
                        <label class="mm-col-opt"><input class="mm-col-cb" type="checkbox" checked data-col="sub_locator"> 库位</label>
                        <label class="mm-col-opt"><input class="mm-col-cb" type="checkbox" checked data-col="created_by"> 创建人</label>
                        <label class="mm-col-opt"><input class="mm-col-cb" type="checkbox" checked data-col="last_updated_by"> 最后更新</label>
                        <label class="mm-col-opt"><input class="mm-col-cb" type="checkbox" checked data-col="creation_date"> 创建时间</label>
                        <label class="mm-col-opt"><input class="mm-col-cb" type="checkbox" checked data-col="last_update_date"> 最后更新时间</label>
                        <div class="mm-col-menu-foot">
                            <a href="javascript:void(0)" onclick="MmColAll(true)">全选</a>
                            <a href="javascript:void(0)" onclick="MmColAll(false)">全不选</a>
                            <a href="javascript:void(0)" onclick="MmColReset()">重置</a>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>

            <!-- Tab 1: 基本信息（全部字段单排横排，仿 BOMSetup 表头一行/值一行） -->
            <div class="mm-tabs-panel<?php echo $ActiveTab == 1 ? ' active' : ''; ?>" data-tab="1">
                <div class="mm-table-scroll">
                <table class="mm-table mm-table-row">
                    <tr>
                        <th data-col="item_no">料号编码</th>
                        <th data-col="item_name">料号名称</th>
                        <th data-col="item_id">内部ID</th>
                        <th data-col="item_desc">规格型号</th>
                        <th data-col="item_category1">物料分类</th>
                        <th data-col="item_type">物料类型</th>
                        <th data-col="status">状态</th>
                        <th data-col="item_use">用途</th>
                        <th data-col="units">单位</th>
                        <th data-col="safe_qty">安全库存</th>
                        <th data-col="unit_price">单价</th>
                        <th data-col="sub_code">仓库</th>
                        <th data-col="sub_locator">库位</th>
                        <th data-col="created_by">创建人</th>
                        <th data-col="last_updated_by">最后更新</th>
                        <th data-col="creation_date">创建时间</th>
                        <th data-col="last_update_date">最后更新时间</th>
                    </tr>
                    <tr>
                        <td data-col="item_no"><b><?php echo htmlspecialchars($ItemInfo['item_no']); ?></b></td>
                        <td data-col="item_name"><?php echo htmlspecialchars($ItemInfo['item_name']); ?></td>
                        <td data-col="item_id"><?php echo $ItemInfo['item_id']; ?></td>
                        <td data-col="item_desc"><?php echo htmlspecialchars($ItemInfo['item_desc']); ?></td>
                        <td data-col="item_category1"><?php echo htmlspecialchars($ItemInfo['item_category1']); ?></td>
                        <td data-col="item_type"><?php echo htmlspecialchars($TypeDisp); ?>（<?php echo $ItemType; ?>）</td>
                        <td data-col="status"><span class="<?php echo $StatusClass; ?>"><?php echo $Status; ?></span></td>
                        <td data-col="item_use"><?php echo htmlspecialchars($ItemUseName); ?>（<?php echo $ItemUse; ?>）</td>
                        <td data-col="units"><?php echo htmlspecialchars($ItemInfo['units']); ?></td>
                        <td data-col="safe_qty"><?php echo htmlspecialchars($ItemInfo['safe_qty']); ?></td>
                        <td data-col="unit_price">¥<?php echo number_format(floatval($ItemInfo['unit_price']), 2); ?></td>
                        <td data-col="sub_code"><?php echo htmlspecialchars($ItemInfo['sub_code']); ?></td>
                        <td data-col="sub_locator"><?php echo htmlspecialchars($ItemInfo['sub_locator']); ?></td>
                        <td data-col="created_by"><?php echo htmlspecialchars($ItemInfo['created_by']); ?></td>
                        <td data-col="last_updated_by"><?php echo htmlspecialchars($ItemInfo['last_updated_by']); ?></td>
                        <td data-col="creation_date"><?php echo !empty($ItemInfo['creation_date']) ? date('Y-m-d H:i:s', $ItemInfo['creation_date']) : '—'; ?></td>
                        <td data-col="last_update_date"><?php echo !empty($ItemInfo['last_update_date']) ? date('Y-m-d H:i:s', $ItemInfo['last_update_date']) : '—'; ?></td>
                    </tr>
                </table>
                </div>
            </div>

            <!-- Tab 2: BOM 结构 -->
            <div class="mm-tabs-panel<?php echo $ActiveTab == 2 ? ' active' : ''; ?>" data-tab="2">
                <?php if (count($itemBoms) == 0) { ?>
                    <div class="mm-empty">该物料暂无 BOM 头（如需建 BOM，请前往 BOM 管理）</div>
                <?php } else { ?>
                <table class="mm-table">
                    <tr>
                        <th width="80">版本</th>
                        <th width="100">状态</th>
                        <th width="100">是否当前</th>
                        <th width="160">创建时间</th>
                        <th>操作</th>
                    </tr>
                    <?php foreach ($itemBoms as $bh): ?>
                    <tr>
                        <td>v<?php echo $bh['version']; ?></td>
                        <td><?php echo htmlspecialchars($bh['status']); ?></td>
                        <td><?php echo ($bh['is_current'] ? '✓ 当前' : '—'); ?></td>
                        <td><?php echo $bh['creation_date'] ? date('Y-m-d H:i:s', $bh['creation_date']) : '—'; ?></td>
                        <td><a class="bom-link" href="<?php echo $RootPath; ?>/BOMSetup.php?item_no=<?php echo urlencode($ItemNo); ?>" target="_blank">查看/编辑 BOM →</a></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
                <?php } ?>
            </div>

            <!-- Tab 3: 图档 -->
            <div class="mm-tabs-panel<?php echo $ActiveTab == 3 ? ' active' : ''; ?>" data-tab="3">
                <?php if (count($itemFiles) == 0) { ?>
                    <div class="mm-empty">该物料暂无图档（如需上传，请前往图文档中心）</div>
                <?php } else { ?>
                <table class="mm-table">
                    <tr>
                        <th width="40">#</th>
                        <th>文件名称</th>
                        <th width="100">上传人</th>
                        <th width="160">上传时间</th>
                    </tr>
                    <?php $i = 1; foreach ($itemFiles as $f): ?>
                    <tr>
                        <td><?php echo $i++; ?></td>
                        <td style="text-align:left"><?php echo htmlspecialchars($f['file_name']); ?> <span style="color:#888;font-size:11px">（<?php echo basename($f['file_patch']); ?>）</span></td>
                        <td><?php echo htmlspecialchars($f['created_by']); ?></td>
                        <td><?php echo date('Y-m-d H:i:s', $f['creation_date']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
                <?php } ?>
            </div>
        <?php } ?>
    </div>
</div>

<!-- ===================== 右键菜单 ===================== -->
<div id="ctxMenu">
    <div data-act="add">➕ 新增料号</div>
    <div data-act="edit">✏ 料号修改</div>
    <div data-act="upload">⇧ 料号整批上传</div>
    <div class="sep"></div>
    <div data-act="refresh">🔄 刷新数据</div>
    <div data-act="select">👁 查看详情</div>
</div>

<script type="text/javascript">
var MM_ROOT = '<?php echo $RootPath; ?>';
var MM_CUR_ITEM = '<?php echo htmlspecialchars(addslashes($ItemNo), ENT_QUOTES); ?>';

/* 切换分类展开/折叠 */
function MmToggleCat(row) {
    var node = row.parentNode;
    var sub = node.querySelector('ul.bom-sub');
    var tw = row.querySelector('.tw');
    if (sub) {
        if (sub.style.display === 'none') {
            sub.style.display = '';
            node.classList.remove('collapsed');
            if (tw) tw.textContent = '-';
        } else {
            sub.style.display = 'none';
            node.classList.add('collapsed');
            if (tw) tw.textContent = '+';
        }
    }
}

/* 点击分类文件夹行 → 进入分类视图（右侧展示该分类下所有物料） */
function MmOpenCat(cat) {
    window.location.href = MM_ROOT + '/MaterialManage.php?cat=' + encodeURIComponent(cat);
}

/* 全部展开/折叠 */
function MmExpandAll(open) {
    var tree = document.getElementById('mmTree');
    if (!tree) return;
    var subs = tree.querySelectorAll('ul.bom-sub');
    for (var i = 0; i < subs.length; i++) {
        subs[i].style.display = open ? '' : 'none';
    }
    var nodes = tree.querySelectorAll('.top-level');
    for (var j = 0; j < nodes.length; j++) {
        if (open) nodes[j].classList.remove('collapsed');
        else nodes[j].classList.add('collapsed');
    }
    var tws = tree.querySelectorAll('.tw');
    for (var k = 0; k < tws.length; k++) tws[k].textContent = open ? '-' : '+';
}

/* 选中物料（左侧列表点击） */
function MmSelectItem(itemNo) {
    window.location.href = MM_ROOT + '/MaterialManage.php?item_no=' + encodeURIComponent(itemNo);
}

/* Tab 切换 */
function MmSwitchTab(tabNo) {
    window.location.href = MM_ROOT + '/MaterialManage.php?item_no=' + encodeURIComponent(MM_CUR_ITEM) + '&tab=' + tabNo;
}

/* 显示右键菜单 */
function MmShowContext(event, itemNo, itemName, isEnabled) {
    event.preventDefault();
    var menu = document.getElementById('ctxMenu');
    MM_CUR_ITEM = itemNo;

    // 「料号修改」仅当物料启用时可点
    var editDiv = menu.querySelector('div[data-act="edit"]');
    if (isEnabled) editDiv.classList.remove('disabled');
    else editDiv.classList.add('disabled');

    menu.style.display = 'block';
    menu.style.left = (event.clientX) + 'px';
    menu.style.top = (event.clientY) + 'px';

    // 防止超出右/下边界
    var rect = menu.getBoundingClientRect();
    if (rect.right > window.innerWidth) {
        menu.style.left = (window.innerWidth - rect.width - 10) + 'px';
    }
    if (rect.bottom > window.innerHeight) {
        menu.style.top = (window.innerHeight - rect.height - 10) + 'px';
    }
}

/* 点击其他地方隐藏右键菜单 */
document.addEventListener('click', function(e) {
    var menu = document.getElementById('ctxMenu');
    if (menu && menu.style.display !== 'none') {
        menu.style.display = 'none';
    }
});

/* 右键菜单点击动作 */
document.getElementById('ctxMenu').addEventListener('click', function(e) {
    var div = e.target.closest('div[data-act]');
    if (!div || div.classList.contains('disabled')) return;
    var act = div.getAttribute('data-act');
    this.style.display = 'none';

    var url = '';
    var title = '';
    if (act === 'add') { url = MM_ROOT + '/AddItemNo.php?embed=1'; title = '料号建立'; }
    else if (act === 'edit') { url = MM_ROOT + '/UpdateItemNo.php?ItemID=' + encodeURIComponent(MM_CUR_ITEM) + '&embed=1'; title = '料号修改：' + MM_CUR_ITEM; }
    else if (act === 'upload') { url = MM_ROOT + '/SegmentUpload.php?embed=1'; title = '料号整批上传'; }
    else if (act === 'refresh') { window.location.reload(); return; }
    else if (act === 'select') { window.location.href = MM_ROOT + '/MaterialManage.php?item_no=' + encodeURIComponent(MM_CUR_ITEM); return; }

    if (url) {
        $.dialog({
            title: title,
            width: 920,
            height: 640,
            content: 'url:' + url,
            lock: true,
            max: false,
            min: false,
            resize: false,
            drag: false,
            close: function() {
                // 关闭后刷新物料管理页面（树与右侧会重新加载）
                window.location.reload();
            }
        });
    }
});

/* 顶部 "+ 新增" 按钮 */
function MmOpenAddDialog() {
    $.dialog({
        title: '料号建立',
        width: 920,
        height: 640,
        content: 'url:' + MM_ROOT + '/AddItemNo.php?embed=1',
        lock: true,
        max: false,
        min: false,
        resize: false,
        drag: false,
        close: function() { window.location.reload(); }
    });
}

/* 工具栏上的"修改物料"按钮 — 打开 lhgdialog 弹窗加载 UpdateItemNo.php */
function MmOpenEditDialog(itemNo) {
    $.dialog({
        title: '料号修改：' + itemNo,
        width: 920,
        height: 640,
        content: 'url:' + MM_ROOT + '/UpdateItemNo.php?ItemID=' + encodeURIComponent(itemNo) + '&embed=1',
        lock: true,
        max: false,
        min: false,
        resize: false,
        drag: false,
        close: function() { window.location.reload(); }
    });
}

/* 搜索定位（如 ItemNo 与当前选中不同，跳转到选中状态） */
<?php if ($SearchFilter != '' && $ItemNo != '') { ?>
window.onload = function() {
    // 滚到选中行
    var sel = document.querySelector('.bom-node.leaf-node.active');
    if (sel) {
        sel.scrollIntoView({block: 'center'});
    }
};
<?php } ?>

/* 字段显示控制：切换浮层 */
function MmToggleColMenu(e) {
    e.stopPropagation();
    var m = document.getElementById('mmColMenu');
    if (m.classList.contains('show')) m.classList.remove('show');
    else m.classList.add('show');
}

/* 字段显示控制：根据勾选状态显示/隐藏对应列（th + td 同步） */
function MmApplyCols() {
    var boxes = document.getElementById('mmColMenu').getElementsByTagName('input');
    for (var i = 0; i < boxes.length; i++) {
        var b = boxes[i];
        if (b.type !== 'checkbox') continue;
        var col = b.getAttribute('data-col');
        var cells = document.querySelectorAll('.mm-table-row [data-col="' + col + '"]');
        for (var j = 0; j < cells.length; j++) {
            cells[j].style.display = b.checked ? '' : 'none';
        }
    }
}

/* 全选 / 全不选 */
function MmColAll(state) {
    var boxes = document.getElementById('mmColMenu').getElementsByTagName('input');
    for (var i = 0; i < boxes.length; i++) {
        if (boxes[i].type === 'checkbox') boxes[i].checked = state;
    }
    MmApplyCols();
}

/* 重置为全部显示 */
function MmColReset() {
    MmColAll(true);
}

/* 勾选变化即应用 */
document.addEventListener('change', function(e) {
    var t = e.target;
    if (t && t.className && t.className.indexOf('mm-col-cb') >= 0) {
        MmApplyCols();
    }
});

/* 点击浮层外部关闭 */
document.addEventListener('click', function(e) {
    var ctrl = document.querySelector('.mm-tabs-extra');
    var menu = document.getElementById('mmColMenu');
    if (!ctrl) return;
    if (!ctrl.contains(e.target)) {
        menu.classList.remove('show');
    }
});
</script>

<?php include('includes/footer.inc'); ?>
