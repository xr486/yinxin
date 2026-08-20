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
 * 1.5 批量操作处理器（删除 / 归档 / 反归档）
 * 入参 ?act=batch_delete|archive|unarchive&nos=xxx,yyy&csrf=xxx
 * 完成后重定向到原页面（保留过滤参数）
 * ============================================================ */
$BatchAct = isset($_GET['act']) ? $_GET['act'] : '';
$BatchNos = isset($_GET['nos']) ? trim($_GET['nos']) : '';
$BatchCsrf = isset($_GET['csrf']) ? $_GET['csrf'] : '';
$BatchCsrfExpected = md5(session_id() . $_SESSION['UserID']);
if (in_array($BatchAct, array('batch_delete','batch_archive','batch_unarchive')) && $BatchNos != '') {
    if ($BatchCsrf !== $BatchCsrfExpected) {
        echo '<script>alert("安全校验失败，请刷新页面重试");history.back();</script>';
        exit;
    }
    // 保留过滤参数，移除 act/nos/csrf
    $backParam = $_GET;
    unset($backParam['act'], $backParam['nos'], $backParam['csrf']);
    $backUrl = $RootPath . '/MaterialManage.php' . (empty($backParam) ? '' : '?' . http_build_query($backParam));

    $nosArr = array_values(array_filter(array_map('trim', explode(',', $BatchNos)), function($x){ return preg_match('/^[A-Za-z0-9_.\-]+$/', $x); }));
    if (count($nosArr) == 0) {
        echo '<script>alert("无有效料号");window.location="' . htmlspecialchars($backUrl, ENT_QUOTES) . '";</script>';
        exit;
    }
    $nosEscaped = array_map(function($x){ return "'" . DB_escape_string($x) . "'"; }, $nosArr);
    $nosIn = implode(',', $nosEscaped);

    $messages = array();
    if ($BatchAct === 'batch_delete') {
        // 安全校验：该物料是否被任何 BOM 引用（既查作为父头，也查作为子件引用）
        $sqlRef = "(SELECT DISTINCT assembly_item_no AS no FROM bom_headers_all WHERE assembly_item_no IN ($nosIn))
                   UNION
                   (SELECT DISTINCT component AS no FROM bom_lines_all WHERE component IN ($nosIn))";
        $resRef = DB_query($sqlRef, $db);
        $refNos = array();
        while ($r = DB_fetch_array($resRef)) $refNos[] = $r['no'];
        if (count($refNos) > 0) {
            $messages[] = '以下物料已被 BOM 引用，未删除：' . implode(', ', $refNos);
        }
        $delArr = array_values(array_diff($nosArr, $refNos));
        if (count($delArr) > 0) {
            $delEscaped = array_map(function($x){ return "'" . DB_escape_string($x) . "'"; }, $delArr);
            $delIn = implode(',', $delEscaped);
            // 级联删除：子件引用 → 替代料 → 工艺路由 → 图档 → BOM 头 → 物料主数据
            DB_query("DELETE FROM bom_substitutes_all WHERE assembly IN ($delIn) OR component IN (SELECT component FROM bom_lines_all WHERE assembly IN ($delIn))", $db);
            DB_query("DELETE FROM bom_lines_all WHERE assembly IN ($delIn) OR component IN ($delIn)", $db);
            DB_query("DELETE FROM bom_headers_all WHERE assembly_item_no IN ($delIn)", $db);
            DB_query("DELETE FROM bom_routings_all WHERE assembly_item_no IN ($delIn)", $db);
            DB_query("DELETE FROM sf_item_no_file WHERE item_no IN ($delIn)", $db);
            DB_query("DELETE FROM sf_item_upload WHERE item_no IN ($delIn)", $db);
            DB_query("DELETE FROM sf_item_no WHERE item_no IN ($delIn)", $db);
            $messages[] = '已成功删除 ' . count($delArr) . ' 个物料';
        }
    } else {
        // batch_archive / batch_unarchive
        $newFlag = ($BatchAct === 'batch_archive') ? 'N' : 'Y'; // 归档=停用(N)，反归档=启用(Y)
        $opName = ($BatchAct === 'batch_archive') ? '归档' : '反归档';
        DB_query("UPDATE sf_item_no SET disable_flag='" . $newFlag . "', last_update_date=" . time() . ", last_updated_by='" . DB_escape_string($_SESSION['UserID']) . "' WHERE item_no IN ($nosIn)", $db);
        $messages[] = '已' . $opName . ' ' . count($nosArr) . ' 个物料';
    }

    $msgText = implode('；', $messages);
    echo '<script>alert("' . htmlspecialchars($msgText, ENT_QUOTES) . '");window.location="' . htmlspecialchars($backUrl, ENT_QUOTES) . '";</script>';
    exit;
}

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

/* ============================================================
 * 3.5 列表视图查询（带分页 + 多过滤）
 * ============================================================ */
$ListKeyword = isset($_GET['q2']) ? trim($_GET['q2']) : '';
$ListItemNo = isset($_GET['li_item_no']) ? trim($_GET['li_item_no']) : '';
$ListItemName = isset($_GET['li_item_name']) ? trim($_GET['li_item_name']) : '';
$ListItemType = isset($_GET['li_item_type']) ? trim($_GET['li_item_type']) : '';
$PageSize = isset($_GET['ps']) ? max(5, intval($_GET['ps'])) : 20;
$PageNo = isset($_GET['pn']) ? max(1, intval($_GET['pn'])) : 1;

// 拼 where
$listWhere = ' WHERE 1=1';
if ($CatFilter != '') {
    $listWhere .= " AND i.item_category1='" . DB_escape_string($CatFilter) . "'";
}
if ($ListKeyword != '') {
    $k = DB_escape_string(str_replace(array('%','_'), array('\\%','\\_'), $ListKeyword));
    $listWhere .= " AND (i.item_no LIKE '%" . $k . "%' OR i.item_name LIKE '%" . $k . "%' OR i.item_desc LIKE '%" . $k . "%')";
}
if ($ListItemNo != '') {
    $listWhere .= " AND i.item_no LIKE '%" . DB_escape_string($ListItemNo) . "%'";
}
if ($ListItemName != '') {
    $listWhere .= " AND i.item_name LIKE '%" . DB_escape_string($ListItemName) . "%'";
}
if ($ListItemType != '') {
    $listWhere .= " AND i.item_type='" . DB_escape_string($ListItemType) . "'";
}

$sqlCount = "SELECT COUNT(*) FROM sf_item_no i" . $listWhere;
$resCount = DB_query($sqlCount, $db);
$rowCount = DB_fetch_array($resCount);
$ListTotal = intval($rowCount[0]);
$ListPageCount = ($ListTotal > 0) ? ceil($ListTotal / $PageSize) : 1;
if ($PageNo > $ListPageCount) $PageNo = $ListPageCount;
$ListOffset = ($PageNo - 1) * $PageSize;

$sqlList = "SELECT i.item_id, i.item_no, i.item_name, i.item_desc, i.item_type, i.item_use,
                   i.units, i.disable_flag, i.item_category1, i.sub_code, i.creation_date,
                   i.created_by, i.item_remark
            FROM sf_item_no i" . $listWhere . "
            ORDER BY i.item_no LIMIT " . $ListOffset . "," . $PageSize;
$resList = DB_query($sqlList, $db);
$ListRows = array();
while ($r = DB_fetch_array($resList)) { $ListRows[] = $r; }
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
/* ====== 列表视图（顶部查询条 + 三行按钮 + 表格 + 操作列 + 分页） ====== */
.mm-query-form{display:flex;align-items:center;gap:6px;padding:10px 12px;background:#f5f9fd;border:1px solid #d6e4f0;border-radius:4px;margin-bottom:10px;flex-wrap:wrap}
.mm-qlabel{font-size:12px;color:#555;white-space:nowrap}
.mm-qinput{height:28px;padding:3px 8px;border:1px solid #c5d3e0;border-radius:3px;font-size:13px;outline:none;width:130px;background:#fff}
.mm-qinput-wide{width:180px}
.mm-qinput:focus,.mm-qselect:focus{border-color:#1976D2;box-shadow:0 0 0 2px rgba(25,118,210,0.15)}
.mm-qselect{height:28px;padding:3px 6px;border:1px solid #c5d3e0;border-radius:3px;font-size:13px;background:#fff;outline:none}
.mm-qbtn{height:28px;padding:0 14px;background:#fff;border:1px solid #c5d3e0;color:#555;border-radius:3px;cursor:pointer;font-size:13px;transition:all .15s}
.mm-qbtn:hover{background:#f0f0f0}
.mm-qbtn-primary{background:#1976D2;color:#fff;border-color:#1976D2}
.mm-qbtn-primary:hover{background:#1565C0;border-color:#1565C0}
.mm-cat-chip{display:inline-flex;align-items:center;gap:4px;padding:3px 10px;background:#e3f2fd;border:1px solid #90caf9;border-radius:12px;color:#1565C0;font-size:12px;margin-left:6px}
.mm-cat-chip a{color:#1565C0;text-decoration:none;font-weight:bold;font-size:13px;padding:0 3px}
.mm-cat-chip a:hover{color:#0d47a1}
.mm-tool-rows{display:flex;flex-direction:column;gap:4px;margin-bottom:10px}
.mm-tool-row{display:flex;flex-wrap:wrap;gap:6px}
.mm-tbtn{display:inline-flex;align-items:center;gap:3px;height:28px;padding:0 14px;border:1px solid transparent;border-radius:3px;font-size:13px;cursor:pointer;transition:all .15s;font-family:inherit;white-space:nowrap}
.mm-tbtn:disabled{cursor:not-allowed;opacity:0.5}
.mm-tbtn-view{background:#1976D2;color:#fff;border-color:#1976D2}
.mm-tbtn-view:hover:not(:disabled){background:#1565C0;border-color:#1565C0}
.mm-tbtn-add{background:#43a047;color:#fff;border-color:#43a047}
.mm-tbtn-add:hover:not(:disabled){background:#388e3c;border-color:#388e3c}
.mm-tbtn-edit{background:#FB8C00;color:#fff;border-color:#FB8C00}
.mm-tbtn-edit:hover:not(:disabled){background:#ef6c00;border-color:#ef6c00}
.mm-tbtn-del{background:#e53935;color:#fff;border-color:#e53935}
.mm-tbtn-del:hover:not(:disabled){background:#c62828;border-color:#c62828}
.mm-tbtn-arc{background:#fff;color:#5e35b1;border-color:#5e35b1}
.mm-tbtn-arc:hover:not(:disabled){background:#ede7f6}
.mm-tbtn-unarc{background:#fff;color:#1976D2;border-color:#1976D2}
.mm-tbtn-unarc:hover:not(:disabled){background:#e3f2fd}
.mm-tbtn-disabled{background:#fafafa;color:#999;border-color:#e0e0e0}
.mm-table-wrap{flex:1;overflow:auto;max-height:62vh}
.mm-cell-center{text-align:center}
.mm-cell-icon{font-size:16px}
.mm-cell-time{font-size:11px;color:#666}
.mm-cell-ops{text-align:center;white-space:nowrap}
.mm-empty-cell{padding:40px;text-align:center;color:#999;font-size:13px}
.row-act{display:inline-block;width:24px;height:24px;line-height:24px;text-align:center;border-radius:3px;text-decoration:none;font-size:14px;color:#fff;margin:0 1px;transition:all .15s}
.row-act-view{background:#1976D2}
.row-act-view:hover{background:#1565C0}
.row-act-edit{background:#FB8C00}
.row-act-edit:hover{background:#ef6c00}
.row-act-del{background:#e53935}
.row-act-del:hover{background:#c62828}
.row-act-arc{background:#5e35b1}
.row-act-arc:hover{background:#4527a0}
.row-act-unarc{background:#1976D2}
.row-act-unarc:hover{background:#1565C0}
.mm-pagination{display:flex;align-items:center;justify-content:flex-end;gap:6px;padding:10px 4px;font-size:13px;color:#555}
.mm-page-info{color:#555}
.mm-page-info b{color:#1976D2;padding:0 3px}
.mm-page-cur{padding:3px 10px;background:#1976D2;color:#fff;border-radius:3px;font-weight:bold;margin:0 6px}
.mm-page-btn{display:inline-block;padding:3px 10px;background:#fff;border:1px solid #c5d3e0;border-radius:3px;color:#1976D2;text-decoration:none;transition:all .15s}
.mm-page-btn:hover{background:#e3f2fd;border-color:#1976D2}
.mm-page-disabled{background:#fafafa;color:#bbb;cursor:not-allowed;border-color:#e0e0e0}
.mm-page-disabled:hover{background:#fafafa;border-color:#e0e0e0}
#mmListTable .cat-item-link{color:#1976D2;text-decoration:none;font-weight:500;cursor:pointer}
#mmListTable .cat-item-link:hover{text-decoration:underline}
#mmListTable tr.item-disabled td{color:#999}
#mmListTable tr.item-disabled .cat-item-link{color:#999;text-decoration:line-through}
#mmListTable input[type=checkbox]{cursor:pointer;vertical-align:middle}
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
                        $openByDefault = ($SearchFilter != '' || $isCurrentCat || $CatFilter == $cat);
                    ?>
                    <li class="bom-node top-level<?php echo $openByDefault ? '' : ' collapsed'; ?><?php echo $CatFilter == $cat ? ' current-cat' : ''; ?>" data-cat="<?php echo htmlspecialchars($cat); ?>">
                        <div class="bom-row" onclick="MmFilterCat('<?php echo htmlspecialchars(addslashes($cat)); ?>')">
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

    <!-- ===================== 右侧主区：统一列表视图（物料↔BOM↔图档↔工艺 4 向互动） ===================== -->
    <div class="mm-right">
        <!-- 顶部查询条 -->
        <form method="GET" action="<?php echo $RootPath; ?>/MaterialManage.php" class="mm-query-form" id="mmQueryForm">
            <?php if ($CatFilter != '') { ?>
                <input type="hidden" name="cat" value="<?php echo htmlspecialchars($CatFilter); ?>">
            <?php } ?>
            <span class="mm-qlabel">🔍 模糊搜索</span>
            <input type="text" name="q2" value="<?php echo htmlspecialchars($ListKeyword); ?>" placeholder="料号 / 名称 / 规格" class="mm-qinput mm-qinput-wide">
            <span class="mm-qlabel">物料编码</span>
            <input type="text" name="li_item_no" value="<?php echo htmlspecialchars($ListItemNo); ?>" class="mm-qinput">
            <span class="mm-qlabel">物料名称</span>
            <input type="text" name="li_item_name" value="<?php echo htmlspecialchars($ListItemName); ?>" class="mm-qinput">
            <span class="mm-qlabel">物料类型</span>
            <select name="li_item_type" class="mm-qselect">
                <option value="">全部</option>
                <option value="M" <?php echo $ListItemType=='M'?'selected':''; ?>>原材料</option>
                <option value="B" <?php echo $ListItemType=='B'?'selected':''; ?>>半成品</option>
                <option value="F" <?php echo $ListItemType=='F'?'selected':''; ?>>成品</option>
                <option value="P" <?php echo $ListItemType=='P'?'selected':''; ?>>采购件</option>
            </select>
            <button type="submit" class="mm-qbtn mm-qbtn-primary">查询</button>
            <button type="button" class="mm-qbtn" onclick="MmResetQuery()">↻ 重置</button>
            <?php if ($CatFilter != '') { ?>
                <span class="mm-cat-chip">📂 <?php echo htmlspecialchars($CatFilter); ?> <a href="<?php echo $RootPath; ?>/MaterialManage.php" title="清除分类过滤">×</a></span>
            <?php } ?>
        </form>

        <!-- 三行工具栏 -->
        <div class="mm-tool-rows">
            <div class="mm-tool-row">
                <button class="mm-tbtn mm-tbtn-view"   type="button" onclick="MmBtnView()">👁 查看</button>
                <button class="mm-tbtn mm-tbtn-add"   type="button" onclick="MmBtnAdd()">➕ 新增</button>
                <button class="mm-tbtn mm-tbtn-edit"  type="button" onclick="MmBtnEdit()">✏ 编辑</button>
                <button class="mm-tbtn mm-tbtn-del"   type="button" onclick="MmBtnDel()">× 删除</button>
                <button class="mm-tbtn mm-tbtn-arc"   type="button" onclick="MmBtnArchive()">↪ 归档</button>
                <button class="mm-tbtn mm-tbtn-unarc" type="button" onclick="MmBtnUnarchive()">↩ 反归档</button>
            </div>
            <div class="mm-tool-row">
                <button class="mm-tbtn mm-tbtn-disabled" disabled title="敬请期待">📋 复制</button>
                <button class="mm-tbtn mm-tbtn-disabled" disabled title="敬请期待">📥 导入物料</button>
                <button class="mm-tbtn mm-tbtn-disabled" disabled title="敬请期待">📤 导出物料</button>
                <button class="mm-tbtn mm-tbtn-disabled" disabled title="敬请期待">📥 导入工价</button>
                <button class="mm-tbtn mm-tbtn-disabled" disabled title="敬请期待">📤 导出工艺</button>
                <button class="mm-tbtn mm-tbtn-disabled" disabled title="敬请期待">📥 导入工艺模板</button>
                <button class="mm-tbtn mm-tbtn-disabled" disabled title="敬请期待">📥 导入物料工艺序</button>
            </div>
            <div class="mm-tool-row">
                <button class="mm-tbtn mm-tbtn-disabled" disabled title="敬请期待">⚙ 自定义列</button>
                <button class="mm-tbtn mm-tbtn-disabled" disabled title="敬请期待">✏ 批量修改</button>
                <button class="mm-tbtn mm-tbtn-disabled" disabled title="敬请期待">📁 转移目录</button>
                <button class="mm-tbtn mm-tbtn-disabled" disabled title="敬请期待">🔄 同步到ERP</button>
                <button class="mm-tbtn mm-tbtn-disabled" disabled title="敬请期待">🔄 同步到生产物料</button>
            </div>
        </div>

        <!-- 数据表格 -->
        <div class="mm-table-wrap">
        <table class="mm-table" id="mmListTable">
            <thead>
            <tr>
                <th width="30"><input type="checkbox" id="mmSelAll"></th>
                <th width="30">图标</th>
                <th width="120">物料编码</th>
                <th>物料名称</th>
                <th width="70">物料类型</th>
                <th width="120">备注</th>
                <th width="80">创建人</th>
                <th>型号</th>
                <th width="60">材料</th>
                <th width="130">创建时间</th>
                <th width="220">操作</th>
            </tr>
            </thead>
            <tbody>
            <?php if (count($ListRows) == 0) { ?>
                <tr><td colspan="11" class="mm-empty-cell">暂无数据，请调整查询条件</td></tr>
            <?php } else {
                $TypeMap = array('M'=>'原材料','B'=>'半成品','F'=>'成品','P'=>'采购件');
                foreach ($ListRows as $it):
                    $isDisabled = (isset($it['disable_flag']) && $it['disable_flag'] == 'N');
                    $tp = isset($TypeMap[$it['item_type']]) ? $TypeMap[$it['item_type']] : $it['item_type'];
                    $createTime = !empty($it['creation_date']) ? date('Y-m-d H:i', $it['creation_date']) : '—';
            ?>
            <tr data-itemno="<?php echo htmlspecialchars($it['item_no']); ?>" data-itemname="<?php echo htmlspecialchars($it['item_name']); ?>" data-disabled="<?php echo $isDisabled ? 1 : 0; ?>" class="<?php echo $isDisabled ? 'item-disabled' : ''; ?>">
                <td><input type="checkbox" class="mm-row-cb" value="<?php echo htmlspecialchars($it['item_no']); ?>"></td>
                <td class="mm-cell-center mm-cell-icon">📦</td>
                <td><a class="cat-item-link" href="javascript:void(0)" onclick="MmOpenView('<?php echo htmlspecialchars(addslashes($it['item_no'])); ?>')"><?php echo htmlspecialchars($it['item_no']); ?></a></td>
                <td><?php echo htmlspecialchars($it['item_name']); ?></td>
                <td class="mm-cell-center"><?php echo htmlspecialchars($tp); ?></td>
                <td><?php echo htmlspecialchars($it['item_remark']); ?></td>
                <td class="mm-cell-center"><?php echo htmlspecialchars($it['created_by']); ?></td>
                <td><?php echo htmlspecialchars($it['item_desc']); ?></td>
                <td class="mm-cell-center">—</td>
                <td class="mm-cell-center mm-cell-time"><?php echo $createTime; ?></td>
                <td class="mm-cell-ops">
                    <a class="row-act row-act-view"   href="javascript:void(0)" onclick="MmOpenView('<?php echo htmlspecialchars(addslashes($it['item_no'])); ?>')" title="查看">👁</a>
                    <a class="row-act row-act-edit"  href="javascript:void(0)" onclick="MmRowEdit('<?php echo htmlspecialchars(addslashes($it['item_no'])); ?>')" title="编辑">✏</a>
                    <a class="row-act row-act-del"   href="javascript:void(0)" onclick="MmRowDel('<?php echo htmlspecialchars(addslashes($it['item_no'])); ?>')" title="删除">×</a>
                    <?php if ($isDisabled) { ?>
                        <a class="row-act row-act-unarc" href="javascript:void(0)" onclick="MmRowUnarchive('<?php echo htmlspecialchars(addslashes($it['item_no'])); ?>')" title="反归档">↩</a>
                    <?php } else { ?>
                        <a class="row-act row-act-arc"   href="javascript:void(0)" onclick="MmRowArchive('<?php echo htmlspecialchars(addslashes($it['item_no'])); ?>')" title="归档">↪</a>
                    <?php } ?>
                </td>
            </tr>
            <?php endforeach; } ?>
            </tbody>
        </table>
        </div>

        <!-- 分页 -->
        <?php
        $pageParamBase = $_GET;
        unset($pageParamBase['pn']);
        $pageUrlBase = $RootPath . '/MaterialManage.php?' . http_build_query($pageParamBase) . '&pn=';
        ?>
        <div class="mm-pagination">
            <span class="mm-page-info">共 <b><?php echo $ListTotal; ?></b> 条 / 每页
                <select onchange="MmChangePageSize(this.value)">
                    <option value="20" <?php echo $PageSize==20?'selected':''; ?>>20</option>
                    <option value="50" <?php echo $PageSize==50?'selected':''; ?>>50</option>
                    <option value="100" <?php echo $PageSize==100?'selected':''; ?>>100</option>
                </select>
                条</span>
            <a class="mm-page-btn<?php echo $PageNo<=1?' mm-page-disabled':''; ?>" href="<?php echo $PageNo<=1?'#':htmlspecialchars($pageUrlBase.'1'); ?>">首页</a>
            <a class="mm-page-btn<?php echo $PageNo<=1?' mm-page-disabled':''; ?>" href="<?php echo $PageNo<=1?'#':htmlspecialchars($pageUrlBase.($PageNo-1)); ?>">上一页</a>
            <span class="mm-page-cur">第 <?php echo $PageNo; ?> / <?php echo $ListPageCount; ?> 页</span>
            <a class="mm-page-btn<?php echo $PageNo>=$ListPageCount?' mm-page-disabled':''; ?>" href="<?php echo $PageNo>=$ListPageCount?'#':htmlspecialchars($pageUrlBase.($PageNo+1)); ?>">下一页</a>
            <a class="mm-page-btn<?php echo $PageNo>=$ListPageCount?' mm-page-disabled':''; ?>" href="<?php echo $PageNo>=$ListPageCount?'#':htmlspecialchars($pageUrlBase.$ListPageCount); ?>">末页</a>
        </div>
    </div>
</div>


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
var MM_CSRF = '<?php echo md5(session_id() . $_SESSION['UserID']); ?>';

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

/* 点击分类文件夹行 → 切换该分类作为列表过滤条件（再次点击取消过滤） */
function MmFilterCat(cat) {
    var cur = '';
    try { cur = decodeURIComponent((new URLSearchParams(window.location.search)).get('cat') || ''); } catch(e) {}
    if (cur === cat) {
        window.location.href = MM_ROOT + '/MaterialManage.php';
    } else {
        window.location.href = MM_ROOT + '/MaterialManage.php?cat=' + encodeURIComponent(cat);
    }
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

/* ============================================================
 * 列表视图：6 个按钮 + 行操作 + 全选 + 分页 + 弹窗
 * ============================================================ */

/* 取所有勾选的行（返回 [{item_no, item_name, disabled}, ...]） */
function MmGetSelectedRows() {
    var rows = [];
    var cbs = document.querySelectorAll('#mmListTable .mm-row-cb:checked');
    for (var i = 0; i < cbs.length; i++) {
        var tr = cbs[i].closest('tr');
        rows.push({
            item_no: cbs[i].value,
            item_name: tr ? (tr.getAttribute('data-itemname') || '') : '',
            disabled: tr ? (tr.getAttribute('data-disabled') == '1') : false
        });
    }
    return rows;
}

/* 把当前选中的 item_no 列表拼成 URL 参数 */
function MmJoinItemNos(rows) {
    var arr = [];
    for (var i = 0; i < rows.length; i++) arr.push(rows[i].item_no);
    return arr.join(',');
}

/* 全选/反选 */
document.addEventListener('change', function(e) {
    if (e.target && e.target.id === 'mmSelAll') {
        var checked = e.target.checked;
        var cbs = document.querySelectorAll('#mmListTable .mm-row-cb');
        for (var i = 0; i < cbs.length; i++) cbs[i].checked = checked;
    }
});

/* 重置查询条件 */
function MmResetQuery() {
    var cat = '';
    try { cat = decodeURIComponent((new URLSearchParams(window.location.search)).get('cat') || ''); } catch(e) {}
    window.location.href = MM_ROOT + '/MaterialManage.php' + (cat ? '?cat=' + encodeURIComponent(cat) : '');
}

/* 修改每页条数 */
function MmChangePageSize(ps) {
    var url = new URL(window.location.href);
    url.searchParams.set('ps', ps);
    url.searchParams.delete('pn');
    window.location.href = url.toString();
}

/* ====== 6 个第一行按钮 ====== */

/* 查看：选中若干行则打开第一个；提示选择 */
function MmBtnView() {
    var rows = MmGetSelectedRows();
    if (rows.length === 0) { alert('请先勾选要查看的物料（操作列左侧复选框）'); return; }
    MmOpenView(rows[0].item_no);
}

/* 新增 */
function MmBtnAdd() {
    $.dialog({
        title: '料号建立',
        width: 920, height: 640,
        content: 'url:' + MM_ROOT + '/AddItemNo.php?embed=1',
        lock: true, max: false, min: false, resize: false, drag: false,
        close: function() { window.location.reload(); }
    });
}

/* 编辑：选中若干行则编辑第一个 */
function MmBtnEdit() {
    var rows = MmGetSelectedRows();
    if (rows.length === 0) { alert('请先勾选要编辑的物料'); return; }
    if (rows.length > 1) { alert('一次只能编辑一个物料，已选中 ' + rows.length + ' 个，请只勾选 1 个'); return; }
    MmRowEdit(rows[0].item_no);
}

/* 删除（批量） */
function MmBtnDel() {
    var rows = MmGetSelectedRows();
    if (rows.length === 0) { alert('请先勾选要删除的物料'); return; }
    if (!confirm('确定要删除选中的 ' + rows.length + ' 个物料吗？\n\n注意：被 BOM 引用的物料不允许删除（安全校验会自动跳过）。')) return;
    var nos = MmJoinItemNos(rows);
    window.location.href = MM_ROOT + '/MaterialManage.php?act=batch_delete&nos=' + encodeURIComponent(nos) + '&csrf=' + encodeURIComponent(MM_CSRF);
}

/* 归档（批量） */
function MmBtnArchive() {
    var rows = MmGetSelectedRows();
    if (rows.length === 0) { alert('请先勾选要归档的物料'); return; }
    if (!confirm('确定要将选中的 ' + rows.length + ' 个物料「归档」（停用）吗？')) return;
    var nos = MmJoinItemNos(rows);
    window.location.href = MM_ROOT + '/MaterialManage.php?act=batch_archive&nos=' + encodeURIComponent(nos) + '&csrf=' + encodeURIComponent(MM_CSRF);
}

/* 反归档（批量） */
function MmBtnUnarchive() {
    var rows = MmGetSelectedRows();
    if (rows.length === 0) { alert('请先勾选要反归档的物料'); return; }
    if (!confirm('确定要将选中的 ' + rows.length + ' 个物料「反归档」（恢复启用）吗？')) return;
    var nos = MmJoinItemNos(rows);
    window.location.href = MM_ROOT + '/MaterialManage.php?act=batch_unarchive&nos=' + encodeURIComponent(nos) + '&csrf=' + encodeURIComponent(MM_CSRF);
}

/* ====== 行操作（操作列图标） ====== */

function MmOpenView(itemNo) {
    $.dialog({
        title: '物料详情：' + itemNo,
        width: 1080, height: 720,
        content: 'url:' + MM_ROOT + '/MaterialDetail.php?item_no=' + encodeURIComponent(itemNo) + '&embed=1',
        lock: true, max: false, min: false, resize: false, drag: false,
        close: function() { window.location.reload(); }
    });
}

function MmRowEdit(itemNo) {
    $.dialog({
        title: '料号修改：' + itemNo,
        width: 920, height: 640,
        content: 'url:' + MM_ROOT + '/UpdateItemNo.php?ItemID=' + encodeURIComponent(itemNo) + '&embed=1',
        lock: true, max: false, min: false, resize: false, drag: false,
        close: function() { window.location.reload(); }
    });
}

function MmRowDel(itemNo) {
    if (!confirm('确定要删除物料 "' + itemNo + '" 吗？\n\n注意：被 BOM 引用的物料不允许删除。')) return;
    window.location.href = MM_ROOT + '/MaterialManage.php?act=batch_delete&nos=' + encodeURIComponent(itemNo) + '&csrf=' + encodeURIComponent(MM_CSRF);
}

function MmRowArchive(itemNo) {
    if (!confirm('确定要归档物料 "' + itemNo + '" 吗？')) return;
    window.location.href = MM_ROOT + '/MaterialManage.php?act=batch_archive&nos=' + encodeURIComponent(itemNo) + '&csrf=' + encodeURIComponent(MM_CSRF);
}

function MmRowUnarchive(itemNo) {
    if (!confirm('确定要反归档物料 "' + itemNo + '" 吗？')) return;
    window.location.href = MM_ROOT + '/MaterialManage.php?act=batch_unarchive&nos=' + encodeURIComponent(itemNo) + '&csrf=' + encodeURIComponent(MM_CSRF);
}
</script>

<?php include('includes/footer.inc'); ?>
