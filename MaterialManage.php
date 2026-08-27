<?php
/* 物料管理（统一入口，左侧分类树 + 右侧详情 + 右键菜单 + 弹窗）
 * 集成：料号维护 / 料号修改 / 料号整批上传 三个老功能（已合并为右键菜单项）
 * 参照：DocFileCenter.php（左侧树/右侧卡）+ BOMSetup.php（lhgdialog 弹窗样式）
 * 说明：3 个老表单采用 ?embed=1 弹窗模式（保留原业务逻辑，仅剥皮）
 */
include('includes/session.inc');
include('includes/SQL_CommonFunctions.inc');

/* $RootPath 在 header.inc 里定义（用于构造回跳 URL），AJAX 模式需在此初始化 */
if (!isset($RootPath)) {
    $RootPath = dirname(htmlspecialchars($_SERVER['PHP_SELF']));
    if ($RootPath == '/' OR $RootPath == '\\') $RootPath = '';
}

/* AJAX 模式预处理：吞掉所有意外输出（warning/notice/echo），保证 JSON 纯净 */
$__isAjax = (isset($_REQUEST['act']) && in_array($_REQUEST['act'], array('batch_delete','batch_archive','batch_unarchive')) && isset($_REQUEST['ajax']) && $_REQUEST['ajax'] == '1');
if ($__isAjax) {
    ob_start(); // 捕获意外输出（session.inc 可能 echo 权限警告等）
    @ini_set('display_errors', '0');
}

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
 * 1.5 批量操作处理器（删除 / 归档 / 反归档）— AJAX 模式需在 header.inc 之前拦截
 * 入参 ?act=batch_delete|archive|unarchive&nos=xxx,yyy&csrf=xxx
 * AJAX 模式：返回 JSON；非 AJAX：跳转回原页面
 * 必须放在 header.inc 之前，否则 AJAX 输出会被 HTML 头污染
 * ============================================================ */
$BatchAct = isset($_REQUEST['act']) ? $_REQUEST['act'] : '';        // REQUEST 兼容 GET/POST
$BatchNos = isset($_REQUEST['nos']) ? trim($_REQUEST['nos']) : '';
$BatchCsrf = isset($_REQUEST['csrf']) ? $_REQUEST['csrf'] : '';
$BatchAjax = isset($_REQUEST['ajax']) ? $_REQUEST['ajax'] : '';
$BatchStatus = isset($_REQUEST['status']) ? trim($_REQUEST['status']) : ''; // batch_set_status 专用
$BatchCsrfExpected = md5(session_id() . $_SESSION['UserID']);
if (in_array($BatchAct, array('batch_delete','batch_archive','batch_unarchive','batch_set_status')) && $BatchNos != '') {
    if ($BatchCsrf !== $BatchCsrfExpected) {
        if ($BatchAjax == '1') {
            if (ob_get_level() > 0) { @ob_clean(); }
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(array('ok' => false, 'msg' => '安全校验失败，请刷新页面重试'));
            exit;
        }
        echo '<script>alert("安全校验失败，请刷新页面重试");history.back();</script>';
        exit;
    }
    // 保留过滤参数，移除 act/nos/csrf/ajax
    $backParam = $_GET;
    unset($backParam['act'], $backParam['nos'], $backParam['csrf'], $backParam['ajax']);
    $backUrl = $RootPath . '/MaterialManage.php' . (empty($backParam) ? '' : '?' . http_build_query($backParam));

    $nosArr = array_values(array_filter(array_map('trim', explode(',', $BatchNos)), function($x){ return preg_match('/^[A-Za-z0-9_.\-]+$/', $x); }));
    if (count($nosArr) == 0) {
        if ($BatchAjax == '1') {
            if (ob_get_level() > 0) { @ob_clean(); }
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(array('ok' => false, 'msg' => '无有效料号'));
            exit;
        }
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
    } elseif ($BatchAct === 'batch_set_status') {
        // 承认状态批量设置（草稿/试用/正式/冻结/报废 白名单校验）
        $StatusWhite = array('草稿', '试用', '正式', '冻结', '报废');
        if (!in_array($BatchStatus, $StatusWhite)) {
            if ($BatchAjax == '1') {
                if (ob_get_level() > 0) { @ob_clean(); }
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(array('ok' => false, 'msg' => '无效的状态值'));
                exit;
            }
            echo '<script>alert("无效的状态值");window.location="' . htmlspecialchars($backUrl, ENT_QUOTES) . '";</script>';
            exit;
        }
        DB_query("UPDATE sf_item_no SET item_status='" . DB_escape_string($BatchStatus) . "', last_update_date=" . time() . ", last_updated_by='" . DB_escape_string($_SESSION['UserID']) . "' WHERE item_no IN ($nosIn)", $db);
        $messages[] = '已将 ' . count($nosArr) . ' 个物料状态设为「' . $BatchStatus . '」';
    } else {
        // batch_archive / batch_unarchive
        $newFlag = ($BatchAct === 'batch_archive') ? 'N' : 'Y'; // 归档=停用(N)，反归档=启用(Y)
        $opName = ($BatchAct === 'batch_archive') ? '归档' : '反归档';
        DB_query("UPDATE sf_item_no SET able_flag='" . $newFlag . "', last_update_date=" . time() . ", last_updated_by='" . DB_escape_string($_SESSION['UserID']) . "' WHERE item_no IN ($nosIn)", $db);
        $messages[] = '已' . $opName . ' ' . count($nosArr) . ' 个物料';
    }

    $msgText = implode('；', $messages);

    // AJAX 模式：直接返回 JSON，不刷新页面
    if ($BatchAjax == '1') {
        // 清空意外输出（如 session.inc 的权限警告），保证 JSON 纯净
        if (ob_get_level() > 0) { @ob_clean(); }
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(array(
            'ok'    => true,
            'msg'   => $msgText,
            'count' => count($nosArr),
            'act'   => $BatchAct,
        ));
        exit;
    }

    echo '<script>alert("' . htmlspecialchars($msgText, ENT_QUOTES) . '");window.location="' . htmlspecialchars($backUrl, ENT_QUOTES) . '";</script>';
    exit;
}

/* AJAX handler 之后才渲染 HTML 头（避免污染 JSON 输出） */
$Title = _('物料管理');
$ViewTopic = '物料管理';
$BookMark = '物料管理';
include('includes/header.inc');

/* ============================================================
 * 2. 查左侧分类树（按 item_category1 聚合，全部物料含禁用）
 * ============================================================ */
$treeGroups = array();
$treeHasAny = false;
$sqlTree = "SELECT i.item_no, i.item_name, i.item_category1, i.able_flag
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
                   i.units, i.able_flag, i.item_category1, i.sub_code, i.creation_date,
                   i.created_by, i.item_remark, i.item_status,
                   i.safe_qty, i.unit_price, i.spec, i.material, i.weight, i.drawing_no,
                   i.std_part_type, i.priority, i.supplier_code, i.huohao,
                   i.last_updated_by, i.last_update_date
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
/* 撑满视口可用高度（视口高减去页头页脚约 100px），左/右两栏自然填到接近页脚 */
.mm-layout{display:flex;width:100%;height:calc(100vh - 100px);min-height:560px;gap:8px;margin-top:6px;margin-bottom:0}
.mm-left{width:340px;border:1px solid #c5c5c5;background:#f7f9fc;border-radius:4px;padding:12px;display:flex;flex-direction:column;height:100%;min-height:0;box-sizing:border-box}
.mm-right{flex:1;border:1px solid #c5c5c5;border-radius:4px;padding:14px;background:#fff;min-width:0;display:flex;flex-direction:column;height:100%;min-height:0;box-sizing:border-box;overflow:hidden}

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
.mm-left-body{flex:1;overflow:auto;padding-bottom:4px;min-height:0}
.bom-tree, .bom-tree ul{list-style:none;margin:0;padding:0}
.bom-tree{font-size:12px;line-height:22px;color:#333;user-select:none}
.bom-tree ul.bom-sub{margin:0;padding:0}
.bom-node{display:flex;flex-direction:column;min-width:0}
.bom-row{display:flex;align-items:center;height:22px;white-space:nowrap;cursor:pointer;min-width:0;padding-right:4px}
.bom-row:hover{background:#f0f4f8}
.mm-cat-item.current-cat>.bom-row,
.bom-node.active>.bom-row{background:#cfe3ff}
.mm-cat-item.current-cat>.bom-row .lbl,
.bom-node.active>.bom-row .lbl{font-weight:bold;color:#0d47a1}
.icon-label{display:flex;align-items:center;gap:0;margin-left:2px;flex:0 0 auto}
.bom-row .lbl{flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;min-width:0;padding:0 0 0 2px;line-height:22px}
.cat-icon{display:inline-block;width:14px;height:14px;background:#FFC107;border:1px solid #F57C00;border-radius:2px;margin-right:4px;vertical-align:middle}
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
/* 承认状态 5 枚举彩色标签（草稿灰/试用蓝/正式绿/冻结橙/报废红） */
.mm-status-draft{display:inline-block;padding:2px 8px;border-radius:3px;background:#f5f5f5;color:#616161;border:1px solid #d0d0d0;font-size:11px}
.mm-status-trial{display:inline-block;padding:2px 8px;border-radius:3px;background:#e3f2fd;color:#1565c0;border:1px solid #90caf9;font-size:11px}
.mm-status-formal{display:inline-block;padding:2px 8px;border-radius:3px;background:#e8f5e9;color:#2e7d32;border:1px solid #a5d6a7;font-size:11px}
.mm-status-frozen{display:inline-block;padding:2px 8px;border-radius:3px;background:#fff3e0;color:#e65100;border:1px solid #ffcc80;font-size:11px}
.mm-status-scrap{display:inline-block;padding:2px 8px;border-radius:3px;background:#fdecea;color:#c62828;border:1px solid #ef9a9a;font-size:11px}
.mm-empty{padding:30px;text-align:center;color:#999;font-size:13px;background:#fafbfc;border:1px dashed #d9d9d9;border-radius:4px}
.mm-tree-sum{font-size:11px;color:#888;padding-top:6px;border-top:1px dashed #d9d9d9}
.mm-tree-sum b{color:#1976D2}
/* ====== 分类视图（点击分类文件夹进入） ====== */
.mm-catview-search{display:flex;align-items:center;gap:8px;padding:10px 12px;background:#f5f9fd;border:1px solid #d6e4f0;border-radius:4px;margin-bottom:12px}
.mm-catview-search input{flex:1;height:30px;padding:4px 10px;border:1px solid #c5d3e0;border-radius:3px;font-size:13px;outline:none}
.mm-catview-search input:focus{border-color:#1976D2;box-shadow:0 0 0 2px rgba(25,118,210,0.15)}
.mm-catview-count{font-size:12px;color:#666;background:#fff;padding:4px 10px;border:1px solid #d6e4f0;border-radius:3px;white-space:nowrap}
.mm-catview-table{flex:1;overflow:auto;min-height:200px}
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
.mm-table-wrap{flex:1;overflow:auto;min-height:200px;margin-top:0}

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
.row-act-status{background:#6a1b9a}
.row-act-status:hover{background:#4a148c}
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
                    <?php foreach ($treeGroups as $cat => $items) { ?>
                    <li class="mm-cat-item<?php echo $CatFilter == $cat ? ' current-cat' : ''; ?>" data-cat="<?php echo htmlspecialchars($cat); ?>">
                        <div class="bom-row" onclick="MmFilterCat('<?php echo htmlspecialchars(addslashes($cat)); ?>')">
                            <span class="icon-label">
                                <span class="cat-icon"></span>
                                <span class="lbl" title="<?php echo htmlspecialchars($cat); ?>"><?php echo htmlspecialchars($cat); ?>（<?php echo count($items); ?>）</span>
                            </span>
                        </div>
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

        <!-- 工具栏（一行9个：6个操作 + 复制/导入物料/导出物料） -->
        <div class="mm-tool-rows">
            <div class="mm-tool-row">
                <button class="mm-tbtn mm-tbtn-view"   type="button" onclick="MmBtnView()">👁 查看</button>
                <button class="mm-tbtn mm-tbtn-add"   type="button" onclick="MmBtnAdd()">➕ 新增</button>
                <button class="mm-tbtn mm-tbtn-edit"  type="button" onclick="MmBtnEdit()">✏ 编辑</button>
                <button class="mm-tbtn mm-tbtn-del"   type="button" onclick="MmBtnDel()">× 删除</button>
                <button class="mm-tbtn mm-tbtn-arc"   type="button" onclick="MmBtnArchive()">↪ 归档</button>
                <button class="mm-tbtn mm-tbtn-unarc" type="button" onclick="MmBtnUnarchive()">↩ 反归档</button>
                <button class="mm-tbtn mm-tbtn-view" type="button" onclick="MmBtnSetStatus()" title="批量设置承认状态（草稿/试用/正式/冻结/报废）">🔄 设状态</button>
                <button class="mm-tbtn" style="background:#fff;color:#1976D2;border-color:#1976D2" type="button" onclick="MmBtnCopy()" title="敬请期待">📋 复制</button>
                <button class="mm-tbtn" style="background:#fff;color:#43A047;border-color:#43A047" type="button" onclick="MmBtnImport()" title="敬请期待">📥 导入物料</button>
                <button class="mm-tbtn" style="background:#fff;color:#FB8C00;border-color:#FB8C00" type="button" onclick="MmBtnExport()" title="敬请期待">📤 导出物料</button>
                <span style="margin-left:auto;position:relative;display:inline-flex;align-items:center;gap:8px">
                    <span class="mm-col-btn" onclick="MmToggleColMenu(event)">⚙ 列设置</span>
                    <span style="font-size:12px;color:#888">共 <b style="color:#1976D2"><?php echo $ListTotal; ?></b> 条</span>
                </span>
            </div>
        </div>

        <!-- 列设置浮层（点击"列设置"弹出） -->
        <div id="mmColMenu" class="mm-col-menu">
            <div class="mm-col-menu-head">⚙ 字段显示控制</div>
            <label class="mm-col-opt"><input type="checkbox" class="mm-col-cb" data-col="cb" checked> 复选框</label>
            <label class="mm-col-opt"><input type="checkbox" class="mm-col-cb" data-col="icon" checked> 图标</label>
            <label class="mm-col-opt"><input type="checkbox" class="mm-col-cb" data-col="no" checked> 物料编码</label>
            <label class="mm-col-opt"><input type="checkbox" class="mm-col-cb" data-col="name" checked> 物料名称</label>
            <label class="mm-col-opt"><input type="checkbox" class="mm-col-cb" data-col="cat" checked> 物料分类</label>
            <label class="mm-col-opt"><input type="checkbox" class="mm-col-cb" data-col="type" checked> 类型</label>
            <label class="mm-col-opt"><input type="checkbox" class="mm-col-cb" data-col="status" checked> 状态</label>
            <label class="mm-col-opt"><input type="checkbox" class="mm-col-cb" data-col="item_status" checked> 承认状态</label>
            <label class="mm-col-opt"><input type="checkbox" class="mm-col-cb" data-col="use" checked> 用途</label>
            <label class="mm-col-opt"><input type="checkbox" class="mm-col-cb" data-col="unit" checked> 单位</label>
            <label class="mm-col-opt"><input type="checkbox" class="mm-col-cb" data-col="safe_qty"> 安全库存</label>
            <label class="mm-col-opt"><input type="checkbox" class="mm-col-cb" data-col="price"> 单价</label>
            <label class="mm-col-opt"><input type="checkbox" class="mm-col-cb" data-col="spec"> 规格</label>
            <label class="mm-col-opt"><input type="checkbox" class="mm-col-cb" data-col="model"> 型号</label>
            <label class="mm-col-opt"><input type="checkbox" class="mm-col-cb" data-col="material"> 材质</label>
            <label class="mm-col-opt"><input type="checkbox" class="mm-col-cb" data-col="weight"> 重量</label>
            <label class="mm-col-opt"><input type="checkbox" class="mm-col-cb" data-col="drawing"> 图号</label>
            <label class="mm-col-opt"><input type="checkbox" class="mm-col-cb" data-col="stdtype"> 标准件</label>
            <label class="mm-col-opt"><input type="checkbox" class="mm-col-cb" data-col="priority"> 优先级</label>
            <label class="mm-col-opt"><input type="checkbox" class="mm-col-cb" data-col="vendor"> 主供应商</label>
            <label class="mm-col-opt"><input type="checkbox" class="mm-col-cb" data-col="huohao"> 货号</label>
            <label class="mm-col-opt"><input type="checkbox" class="mm-col-cb" data-col="remark"> 备注</label>
            <label class="mm-col-opt"><input type="checkbox" class="mm-col-cb" data-col="creator"> 创建人</label>
            <label class="mm-col-opt"><input type="checkbox" class="mm-col-cb" data-col="createtime"> 创建时间</label>
            <label class="mm-col-opt"><input type="checkbox" class="mm-col-cb" data-col="updatetime"> 更新时间</label>
            <label class="mm-col-opt"><input type="checkbox" class="mm-col-cb" data-col="ops" checked> 操作</label>
            <div class="mm-col-menu-foot">
                <a href="javascript:void(0)" onclick="MmColAll(true)">全选</a>
                <a href="javascript:void(0)" onclick="MmColAll(false)">全不选</a>
                <a href="javascript:void(0)" onclick="MmColReset()">重置</a>
            </div>
        </div>

        <!-- 数据表格（id 用于局部刷新：归档/反归档成功后只重拉这块） -->
        <div class="mm-table-wrap" id="mmTableWrap">
        <table class="mm-table" id="mmListTable">
            <thead>
            <tr>
                <th width="28" data-col="cb"><input type="checkbox" id="mmSelAll"></th>
                <th width="30" data-col="icon">图标</th>
                <th width="100" data-col="no">物料编码</th>
                <th width="140" data-col="name">物料名称</th>
                <th width="80" data-col="cat">物料分类</th>
                <th width="70" data-col="type">类型</th>
                <th width="60" data-col="status">状态</th>
                <th width="80" data-col="item_status">承认状态</th>
                <th width="60" data-col="use">用途</th>
                <th width="50" data-col="unit">单位</th>
                <th width="70" data-col="safe_qty">安全库存</th>
                <th width="78" data-col="price">单价</th>
                <th width="100" data-col="spec">规格</th>
                <th width="120" data-col="model">型号</th>
                <th width="80" data-col="material">材质</th>
                <th width="70" data-col="weight">重量</th>
                <th width="90" data-col="drawing">图号</th>
                <th width="80" data-col="stdtype">标准件</th>
                <th width="80" data-col="priority">优先级</th>
                <th width="150" data-col="vendor">主供应商</th>
                <th width="90" data-col="huohao">货号</th>
                <th width="110" data-col="remark">备注</th>
                <th width="70" data-col="creator">创建人</th>
                <th width="130" data-col="createtime">创建时间</th>
                <th width="130" data-col="updatetime">更新时间</th>
                <th width="220" data-col="ops">操作</th>
            </tr>
            </thead>
            <tbody>
            <?php if (count($ListRows) == 0) { ?>
                <tr><td colspan="26" class="mm-empty-cell">暂无数据，请调整查询条件</td></tr>
            <?php } else {
                $TypeMap = array('M'=>'原材料','B'=>'半成品','F'=>'成品','P'=>'采购件');
                foreach ($ListRows as $it):
                    $isDisabled = (isset($it['able_flag']) && $it['able_flag'] == 'N');
                    $tp = isset($TypeMap[$it['item_type']]) ? $TypeMap[$it['item_type']] : $it['item_type'];
                    $createTime = !empty($it['creation_date']) ? date('Y-m-d H:i', $it['creation_date']) : '—';
            ?>
            <tr data-itemno="<?php echo htmlspecialchars($it['item_no']); ?>" data-itemname="<?php echo htmlspecialchars($it['item_name']); ?>" data-disabled="<?php echo $isDisabled ? 1 : 0; ?>" class="<?php echo $isDisabled ? 'item-disabled' : ''; ?>">
                <td data-col="cb"><input type="checkbox" class="mm-row-cb" value="<?php echo htmlspecialchars($it['item_no']); ?>"></td>
                <td data-col="icon" class="mm-cell-center mm-cell-icon">📦</td>
                <td data-col="no"><a class="cat-item-link" href="javascript:void(0)" onclick="MmOpenView('<?php echo htmlspecialchars(addslashes($it['item_no'])); ?>')"><?php echo htmlspecialchars($it['item_no']); ?></a></td>
                <td data-col="name"><?php echo htmlspecialchars($it['item_name']); ?></td>
                <td data-col="cat" class="mm-cell-center"><?php echo htmlspecialchars($it['item_category1']); ?></td>
                <td data-col="type" class="mm-cell-center"><?php echo htmlspecialchars($tp); ?></td>
                <td data-col="status" class="mm-cell-center">
                    <?php if ($isDisabled) { ?>
                        <span class="mm-status-stop">停用</span>
                    <?php } else { ?>
                        <span class="mm-status-ok">启用</span>
                    <?php } ?>
                </td>
                <td data-col="item_status" class="mm-cell-center">
                    <?php
                    $StColor = array('草稿'=>'draft','试用'=>'trial','正式'=>'formal','冻结'=>'frozen','报废'=>'scrap');
                    $stCls = isset($StColor[$it['item_status']]) ? $StColor[$it['item_status']] : '';
                    echo $stCls != '' ? '<span class="mm-status-' . $stCls . '">' . htmlspecialchars($it['item_status']) . '</span>' : '<span style="color:#bbb">—</span>';
                    ?>
                </td>
                <td data-col="use" class="mm-cell-center"><?php echo $it['item_use']=='Y'?'研发':'生产'; ?></td>
                <td data-col="unit" class="mm-cell-center"><?php echo htmlspecialchars($it['units']); ?></td>
                <td data-col="safe_qty" class="mm-cell-center"><?php echo $it['safe_qty']!=='' && $it['safe_qty']!==null ? htmlspecialchars($it['safe_qty']) : '<span style="color:#bbb">—</span>'; ?></td>
                <td data-col="price" class="mm-cell-center" style="color:#d84315">¥<?php echo number_format(floatval($it['unit_price']), 2); ?></td>
                <td data-col="spec"><?php echo $it['spec']!=='' && $it['spec']!==null ? htmlspecialchars($it['spec']) : '<span style="color:#bbb">—</span>'; ?></td>
                <td data-col="model"><?php echo htmlspecialchars($it['item_desc']); ?></td>
                <td data-col="material"><?php echo $it['material']!=='' && $it['material']!==null ? htmlspecialchars($it['material']) : '<span style="color:#bbb">—</span>'; ?></td>
                <td data-col="weight" class="mm-cell-center"><?php echo $it['weight']!=='' && $it['weight']!==null ? htmlspecialchars($it['weight']).'kg' : '<span style="color:#bbb">—</span>'; ?></td>
                <td data-col="drawing"><?php echo $it['drawing_no']!=='' && $it['drawing_no']!==null ? htmlspecialchars($it['drawing_no']) : '<span style="color:#bbb">—</span>'; ?></td>
                <td data-col="stdtype"><?php echo $it['std_part_type']!=='' && $it['std_part_type']!==null ? htmlspecialchars($it['std_part_type']) : '<span style="color:#bbb">—</span>'; ?></td>
                <td data-col="priority"><?php echo $it['priority']!=='' && $it['priority']!==null ? htmlspecialchars($it['priority']) : '<span style="color:#bbb">—</span>'; ?></td>
                <td data-col="vendor"><?php echo $it['supplier_code']!=='' && $it['supplier_code']!==null ? htmlspecialchars($it['supplier_code']) : '<span style="color:#bbb">—</span>'; ?></td>
                <td data-col="huohao"><?php echo $it['huohao']!=='' && $it['huohao']!==null ? htmlspecialchars($it['huohao']) : '<span style="color:#bbb">—</span>'; ?></td>
                <td data-col="remark"><?php echo $it['item_remark']!=='' && $it['item_remark']!==null ? htmlspecialchars($it['item_remark']) : '<span style="color:#bbb">—</span>'; ?></td>
                <td data-col="creator" class="mm-cell-center"><?php echo htmlspecialchars($it['created_by']); ?></td>
                <td data-col="createtime" class="mm-cell-center mm-cell-time"><?php echo $createTime; ?></td>
                <td data-col="updatetime" class="mm-cell-center mm-cell-time"><?php echo !empty($it['last_update_date']) ? date('Y-m-d H:i', $it['last_update_date']) : '<span style="color:#bbb">—</span>'; ?></td>
                <td data-col="ops" class="mm-cell-ops">
                    <a class="row-act row-act-view"   href="javascript:void(0)" onclick="MmOpenView('<?php echo htmlspecialchars(addslashes($it['item_no'])); ?>')" title="查看">👁</a>
                    <a class="row-act row-act-edit"  href="javascript:void(0)" onclick="MmRowEdit('<?php echo htmlspecialchars(addslashes($it['item_no'])); ?>')" title="编辑">✏</a>
                    <a class="row-act row-act-del"   href="javascript:void(0)" onclick="MmRowDel('<?php echo htmlspecialchars(addslashes($it['item_no'])); ?>')" title="删除">×</a>
                    <?php if ($isDisabled) { ?>
                        <a class="row-act row-act-unarc" href="javascript:void(0)" onclick="MmRowUnarchive('<?php echo htmlspecialchars(addslashes($it['item_no'])); ?>')" title="反归档">↩</a>
                    <?php } else { ?>
                        <a class="row-act row-act-arc"   href="javascript:void(0)" onclick="MmRowArchive('<?php echo htmlspecialchars(addslashes($it['item_no'])); ?>')" title="归档">↪</a>
                    <?php } ?>
                    <a class="row-act row-act-status" href="javascript:void(0)" onclick="MmRowSetStatus('<?php echo htmlspecialchars(addslashes($it['item_no'])); ?>')" title="设置承认状态">🔁</a>
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

/* 统一弹窗：理想尺寸 1160×825（用户指定），但按当前视口做响应式上限，避免在低分辨率/小屏笔记本上被截断（不同电脑配置自适应） */
function mmOpenFrameDialog(title, url) {
    var vw = (typeof window.jQuery !== 'undefined') ? jQuery(window).width() : window.innerWidth;
    var vh = (typeof window.jQuery !== 'undefined') ? jQuery(window).height() : window.innerHeight;
    var dlgW = Math.min(1160, vw - 40);   // 窄屏自动缩，留 40px 边距
    var dlgH = Math.min(825, vh - 40);     // 小屏（如 768 高笔记本）自动缩，底部不超屏
    var d = $.dialog({
        title: title,
        width: dlgW,
        height: dlgH,
        content: 'url:' + url,
        lock: true,
        max: false,
        min: false,
        close: function() { window.location.reload(); }
    });
    return d;
}
var MM_CSRF = '<?php echo md5(session_id() . $_SESSION['UserID']); ?>';
/* webERP 全局 FormID（session.inc 强制校验：所有 POST 必须携带，否则必出"在表单确认时出现的错误"HTML 错误页污染 JSON 响应） */
var MM_FORM_ID = '<?php echo $_SESSION['FormID']; ?>';

/* 切换分类展开/折叠 */
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
    else if (act === 'select') { MmOpenView(MM_CUR_ITEM); return; }

    if (url) {
        mmOpenFrameDialog(title, url);
    }
});

/* 顶部 "+ 新增" 按钮 */
function MmOpenAddDialog() {
    mmOpenFrameDialog('料号建立', MM_ROOT + '/AddItemNo.php?embed=1');
}

/* 工具栏上的"修改物料"按钮 — 打开 lhgdialog 弹窗加载 UpdateItemNo.php */
function MmOpenEditDialog(itemNo) {
    mmOpenFrameDialog('料号修改：' + itemNo, MM_ROOT + '/UpdateItemNo.php?ItemID=' + encodeURIComponent(itemNo) + '&embed=1');
}

/* 字段显示控制：切换浮层 */
function MmToggleColMenu(e) {
    e.stopPropagation();
    var m = document.getElementById('mmColMenu');
    if (m.classList.contains('show')) m.classList.remove('show');
    else m.classList.add('show');
}

/* 字段显示控制：根据勾选状态显示/隐藏对应列（th + td 同步），并写入 localStorage */
function MmApplyCols() {
    var boxes = document.getElementById('mmColMenu').getElementsByTagName('input');
    var pref = {};
    for (var i = 0; i < boxes.length; i++) {
        var b = boxes[i];
        if (b.type !== 'checkbox') continue;
        var col = b.getAttribute('data-col');
        var disp = b.checked ? '' : 'none';
        pref[col] = b.checked ? 1 : 0;
        /* th */
        var ths = document.querySelectorAll('th[data-col="' + col + '"]');
        for (var k = 0; k < ths.length; k++) ths[k].style.display = disp;
        /* td */
        var tds = document.querySelectorAll('td[data-col="' + col + '"]');
        for (var j = 0; j < tds.length; j++) tds[j].style.display = disp;
    }
    try { localStorage.setItem('mm_col_pref', JSON.stringify(pref)); } catch (e) {}
}

/* 从 localStorage 恢复列显示偏好 */
function MmRestoreCols() {
    var pref = null;
    try { pref = JSON.parse(localStorage.getItem('mm_col_pref') || 'null'); } catch (e) {}
    if (!pref || typeof pref !== 'object') return;
    var boxes = document.getElementById('mmColMenu').getElementsByTagName('input');
    for (var i = 0; i < boxes.length; i++) {
        var b = boxes[i];
        if (b.type !== 'checkbox') continue;
        var col = b.getAttribute('data-col');
        if (typeof pref[col] === 'number') b.checked = (pref[col] == 1);
    }
    MmApplyCols();
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
    var btn = document.querySelector('.mm-col-btn');
    var menu = document.getElementById('mmColMenu');
    if (!btn) return;
    if (!btn.contains(e.target) && !menu.contains(e.target)) {
        menu.classList.remove('show');
    }
});

/* 启动时恢复列显示偏好 + 工具栏禁用提示 */
window.addEventListener('DOMContentLoaded', function() {
    MmRestoreCols();
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
    if (rows.length === 0) { MmToast('请先勾选要查看的物料（操作列左侧复选框）', 'warn'); return; }
    MmOpenView(rows[0].item_no);
}

/* 新增 */
function MmBtnAdd() {
    mmOpenFrameDialog('料号建立', MM_ROOT + '/AddItemNo.php?embed=1');
}

/* 复制（敬请期待）*/
function MmBtnCopy() {
    MmToast('「复制物料」功能开发中，敬请期待', 'warn');
}

/* 导入物料（敬请期待）*/
function MmBtnImport() {
    MmToast('「导入物料」功能开发中，敬请期待', 'warn');
}

/* 导出物料（敬请期待）*/
function MmBtnExport() {
    MmToast('「导出物料」功能开发中，敬请期待', 'warn');
}

/* 编辑：选中若干行则编辑第一个 */
function MmBtnEdit() {
    var rows = MmGetSelectedRows();
    if (rows.length === 0) { MmToast('请先勾选要编辑的物料', 'warn'); return; }
    if (rows.length > 1) { MmToast('一次只能编辑一个物料，已选中 ' + rows.length + ' 个，请只勾选 1 个', 'warn'); return; }
    MmRowEdit(rows[0].item_no);
}

/* 删除（批量） */
function MmBtnDel() {
    var rows = MmGetSelectedRows();
    if (rows.length === 0) { MmToast('请先勾选要删除的物料', 'warn'); return; }
    if (!confirm('确定要删除选中的 ' + rows.length + ' 个物料吗？\n\n注意：被 BOM 引用的物料不允许删除（安全校验会自动跳过）。')) return;
    var nos = MmJoinItemNos(rows);
    jQuery.ajax({
        url: MM_ROOT + '/MaterialManage.php',
        type: 'POST',
        data: { act: 'batch_delete', nos: nos, csrf: MM_CSRF, FormID: MM_FORM_ID, ajax: 1 },
        dataType: 'json',
        success: function(r) {
            if (r && r.ok) { MmToast(r.msg || ('已删除 ' + rows.length + ' 个物料'), 'ok'); MmReloadTable(); }
            else { MmToast((r && r.msg) || '操作失败', 'err'); }
        },
        error: function(xhr, status, err) {
            var dump = (xhr && xhr.responseText) ? xhr.responseText.substring(0, 300).replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ').trim() : ('status=' + status + ', err=' + err);
            console.error('AJAX error:', dump);
            MmToast('操作失败：' + dump.substring(0, 120), 'err');
        }
    });
}

/* 归档（批量）- AJAX 无刷新模式 */
function MmBtnArchive() { MmBatchArchive(false); }
/* 反归档（批量）- AJAX 无刷新模式 */
function MmBtnUnarchive() { MmBatchArchive(true); }

/* 设状态（批量）- 勾选多行 → 弹窗选状态 → AJAX */
function MmBtnSetStatus() {
    var rows = MmGetSelectedRows();
    if (rows.length === 0) { MmToast('请先勾选要设置状态的物料', 'warn'); return; }
    MmOpenStatusDialog(rows);
}

/* 设状态（单条行操作） */
function MmRowSetStatus(itemNo) {
    MmOpenStatusDialog([{ item_no: itemNo }]);
}

/* 统一状态选择弹窗（lhgdialog content 模式，非 iframe） */
function MmOpenStatusDialog(rows) {
    var opts = ['草稿', '试用', '正式', '冻结', '报废'];
    var selHtml = '<select id="mmStSel" style="width:170px;height:30px;padding:3px 6px;border:1px solid #c5d3e0;border-radius:3px;font-size:13px;outline:none;">';
    for (var i = 0; i < opts.length; i++) selHtml += '<option value="' + opts[i] + '">' + opts[i] + '</option>';
    selHtml += '</select>';
    $.dialog({
        title: '设置物料状态',
        content: '<div style="padding:20px 26px;font-size:13px;color:#333;min-width:320px;">为选中的 <b style="color:#1976D2;">' + rows.length + '</b> 个物料设置承认状态：<div style="margin-top:14px;">' + selHtml + '</div><div style="margin-top:10px;color:#999;font-size:12px;">草稿(灰) / 试用(蓝) / 正式(绿) / 冻结(橙) / 报废(红)</div></div>',
        okVal: '确定',
        ok: function() {
            var st = jQuery('#mmStSel').val();
            var nos = MmJoinItemNos(rows);
            var dlg = this;
            jQuery.ajax({
                url: MM_ROOT + '/MaterialManage.php',
                type: 'POST',
                data: { act: 'batch_set_status', nos: nos, status: st, csrf: MM_CSRF, FormID: MM_FORM_ID, ajax: 1 },
                dataType: 'json',
                success: function(r) {
                    if (r && r.ok) {
                        MmToast(r.msg || ('已设置状态为「' + st + '」'), 'ok');
                        MmReloadTable();
                        dlg.close();
                    } else {
                        MmToast((r && r.msg) || '操作失败', 'err');
                    }
                },
                error: function(xhr, status, err) {
                    var dump = (xhr && xhr.responseText) ? xhr.responseText.substring(0, 300).replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ').trim() : ('status=' + status + ', err=' + err);
                    console.error('set_status AJAX error:', dump);
                    MmToast('操作失败：' + dump.substring(0, 120), 'err');
                }
            });
            return false; // 由 success 决定关闭时机
        },
        cancelVal: '取消',
        cancel: true
    });
}

function MmBatchArchive(isUnarchive) {
    var rows = MmGetSelectedRows();
    if (rows.length === 0) {
        MmToast('请先勾选要' + (isUnarchive ? '反归档' : '归档') + '的物料（操作列左侧复选框）', 'warn');
        return;
    }
    var nos = MmJoinItemNos(rows);
    var act = isUnarchive ? 'batch_unarchive' : 'batch_archive';
    jQuery.ajax({
        url: MM_ROOT + '/MaterialManage.php',
        type: 'POST',
        data: { act: act, nos: nos, csrf: MM_CSRF, FormID: MM_FORM_ID, ajax: 1 },
        dataType: 'json',
        success: function(r) {
            if (r && r.ok) {
                MmToast(r.msg || ('已' + (isUnarchive ? '反归档' : '归档') + ' ' + rows.length + ' 个物料'), 'ok');
                MmReloadTable();
            } else {
                MmToast((r && r.msg) || '操作失败，请重试', 'err');
            }
        },
        error: function(xhr, status, err) {
            // 显示响应原文（截前 300 字），便于排查后端是否输出 HTML 污染
            var dump = (xhr && xhr.responseText) ? xhr.responseText.substring(0, 300) : ('status=' + status + ', err=' + err);
            // 去掉 HTML 标签让 toast 干净
            dump = dump.replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ').trim();
            console.error('MmBatchArchive error:', dump);
            MmToast('操作失败：' + dump.substring(0, 120), 'err');
        }
    });
}

/* 轻提示（顶部居中淡入淡出，2 秒自动消失，不阻塞操作，不依赖 lhgdialog） */
function MmToast(msg, type) {
    try {
        var bg = (type === 'err') ? '#c62828' : (type === 'warn' ? '#f57c00' : '#2e7d32');
        var $t = jQuery('<div class="mm-toast"></div>').text(msg).css({
            'position': 'fixed', 'top': '60px', 'left': '50%', 'transform': 'translateX(-50%)',
            'background': bg, 'color': '#fff', 'padding': '8px 18px', 'border-radius': '4px',
            'box-shadow': '0 2px 8px rgba(0,0,0,0.25)', 'font-size': '13px', 'z-index': '99999',
            'max-width': '80%', 'word-break': 'break-all'
        });
        jQuery('body').append($t);
        $t.hide().fadeIn(180);
        setTimeout(function(){ $t.fadeOut(220, function(){ jQuery(this).remove(); }); }, 1800);
    } catch(e) { try { alert(msg); } catch(e2) {} }
}

/* 局部刷新右侧列表 + 左侧分类树 + 统计数（不刷新整页） */
function MmReloadTable() {
    var url = window.location.href;
    // 右侧表格区：.load() 会把返回的 HTML 注入到 #mmTableWrap，selector 是过滤返回内容
    jQuery('#mmTableWrap').load(url + ' #mmListTable');
    // 左侧分类树
    jQuery('.mm-left-body').load(url + ' .mm-left-body > *');
    // 底部统计
    jQuery('.mm-tree-sum').load(url + ' .mm-tree-sum');
}

/* ====== 行操作（操作列图标） ====== */

function MmOpenView(itemNo) {
    mmOpenFrameDialog('物料详情：' + itemNo, MM_ROOT + '/MaterialDetail.php?item_no=' + encodeURIComponent(itemNo) + '&embed=1');
}

function MmRowEdit(itemNo) {
    mmOpenFrameDialog('料号修改：' + itemNo, MM_ROOT + '/UpdateItemNo.php?ItemID=' + encodeURIComponent(itemNo) + '&embed=1');
}

function MmRowDel(itemNo) {
    if (!confirm('确定要删除物料 "' + itemNo + '" 吗？\n\n注意：被 BOM 引用的物料不允许删除。')) return;
    jQuery.ajax({
        url: MM_ROOT + '/MaterialManage.php',
        type: 'POST',
        data: { act: 'batch_delete', nos: itemNo, csrf: MM_CSRF, FormID: MM_FORM_ID, ajax: 1 },
        dataType: 'json',
        success: function(r) {
            if (r && r.ok) { MmToast(r.msg || ('已删除物料 ' + itemNo), 'ok'); MmReloadTable(); }
            else { MmToast((r && r.msg) || '操作失败', 'err'); }
        },
        error: function(xhr, status, err) {
            var dump = (xhr && xhr.responseText) ? xhr.responseText.substring(0, 300).replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ').trim() : ('status=' + status + ', err=' + err);
            console.error('AJAX error:', dump);
            MmToast('操作失败：' + dump.substring(0, 120), 'err');
        }
    });
}

function MmRowArchive(itemNo) {
    jQuery.ajax({
        url: MM_ROOT + '/MaterialManage.php',
        type: 'POST',
        data: { act: 'batch_archive', nos: itemNo, csrf: MM_CSRF, FormID: MM_FORM_ID, ajax: 1 },
        dataType: 'json',
        success: function(r) {
            if (r && r.ok) { MmToast(r.msg || ('已归档物料 ' + itemNo), 'ok'); MmReloadTable(); }
            else { MmToast((r && r.msg) || '操作失败', 'err'); }
        },
        error: function(xhr, status, err) {
            var dump = (xhr && xhr.responseText) ? xhr.responseText.substring(0, 300).replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ').trim() : ('status=' + status + ', err=' + err);
            console.error('AJAX error:', dump);
            MmToast('操作失败：' + dump.substring(0, 120), 'err');
        }
    });
}

function MmRowUnarchive(itemNo) {
    jQuery.ajax({
        url: MM_ROOT + '/MaterialManage.php',
        type: 'POST',
        data: { act: 'batch_unarchive', nos: itemNo, csrf: MM_CSRF, FormID: MM_FORM_ID, ajax: 1 },
        dataType: 'json',
        success: function(r) {
            if (r && r.ok) { MmToast(r.msg || ('已反归档物料 ' + itemNo), 'ok'); MmReloadTable(); }
            else { MmToast((r && r.msg) || '操作失败', 'err'); }
        },
        error: function(xhr, status, err) {
            var dump = (xhr && xhr.responseText) ? xhr.responseText.substring(0, 300).replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ').trim() : ('status=' + status + ', err=' + err);
            console.error('AJAX error:', dump);
            MmToast('操作失败：' + dump.substring(0, 120), 'err');
        }
    });
}
</script>

<?php include('includes/footer.inc'); ?>
