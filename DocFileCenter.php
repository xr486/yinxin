<?php
/* 图文档中心（基本版 · BOMSetup 风格统一）
 * 复用 BOMSetup 的视觉规范：左侧栏 bom-left + left-actions 彩色按钮 + bom-tree
 * 复用现有表：sf_item_no_file / bom_routing_all_file / bom_routing_public_file
 * 上传：复用 upload2.class.php（SO/ 目录）
 * 注意：session.inc 已对 $_POST / $_GET 统一 DB_escape_string，本页不再二次转义
 */
include('includes/session.inc');
$Title = _('图文档中心');
$ViewTopic = '图文档中心';
$BookMark = '图文档中心';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
require_once 'upload2.class.php';

/* ---------- 允许上传的文件类型 ---------- */
$AllowExt = array('jpeg', 'jpg', 'png', 'gif', 'bmp', 'step', 'stp', 'igs', 'dwg', 'dxf', 'pdf', 'zip', 'rar', '7z', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt');
$AllowMime = array('image/jpeg', 'image/png', 'image/gif', 'image/bmp', 'application/pdf', 'application/zip', 'application/x-zip-compressed', 'application/x-rar-compressed', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 'text/plain', 'application/octet-stream', 'application/x-msdownload');

/* ---------- 工具函数 ---------- */
function DocFileTypeInfo($patch) {
	$ext = strtolower(pathinfo($patch, PATHINFO_EXTENSION));
	$type = 'other';
	if (in_array($ext, array('jpg', 'jpeg', 'png', 'gif', 'bmp'))) {
		$type = 'img';
	} elseif ($ext == 'pdf') {
		$type = 'pdf';
	} elseif (in_array($ext, array('step', 'stp', 'igs', 'dwg', 'dxf'))) {
		$type = 'cad';
	} elseif (in_array($ext, array('zip', 'rar', '7z'))) {
		$type = 'zip';
	} elseif (in_array($ext, array('doc', 'docx'))) {
		$type = 'doc';
	} elseif (in_array($ext, array('xls', 'xlsx'))) {
		$type = 'xls';
	} elseif (in_array($ext, array('ppt', 'pptx'))) {
		$type = 'ppt';
	}
	return array($type, $ext);
}

function DocFileSize($patch) {
	if (!file_exists($patch)) {
		return '—';
	}
	$s = @filesize($patch);
	if ($s >= 1048576) {
		return round($s / 1048576, 2) . ' MB';
	}
	if ($s >= 1024) {
		return round($s / 1024, 1) . ' KB';
	}
	return $s . ' B';
}

function DocFileTypeName($type) {
	$map = array('img' => '图片', 'pdf' => 'PDF', 'cad' => '工程图', 'zip' => '压缩包', 'doc' => 'Word', 'xls' => 'Excel', 'ppt' => 'PPT', 'other' => '文件');
	return isset($map[$type]) ? $map[$type] : '文件';
}

/* ---------- 图标 SVG（复用 BOM 图标尺寸与风格） ---------- */
// 文件夹图标：物料分类节点用，BOMSetup 风格的黄色调
function DocIconFolder() {
	$svg = '<svg viewBox="0 0 16 16" width="16" height="16" class="bom-svg doc-svg" aria-hidden="true" style="display:inline-block;vertical-align:middle;line-height:0;width:16px;height:16px;margin:0 2px 0 0;flex:0 0 auto">'
		. '<path d="M1.5 4 L1.5 14 L14.5 14 L14.5 5.5 L8 5.5 L6.5 4 Z" fill="#FFC107" stroke="#F57C00" stroke-width="0.5"/>'
		. '<path d="M1.5 5.5 L14.5 5.5" stroke="#F57C00" stroke-width="0.4" opacity="0.6"/>'
		. '</svg>';
	return $svg;
}
// 文档图标：物料节点用，BOMSetup 风格的灰白配色 + 类型角标
function DocIconFile($ext = '') {
	$ext = strtolower($ext);
	$badge = '';
	if ($ext == 'pdf') {
		$badge = '<rect x="9.5" y="10.5" width="4.5" height="3.5" fill="#E53935" stroke="#B71C1C" stroke-width="0.3"/><text x="11.7" y="13.2" font-size="3" fill="#fff" text-anchor="middle" font-family="Arial">PDF</text>';
	} elseif (in_array($ext, array('step', 'stp', 'dwg', 'dxf', 'igs'))) {
		$badge = '<rect x="9.5" y="10.5" width="4.5" height="3.5" fill="#2196F3" stroke="#1565C0" stroke-width="0.3"/><text x="11.7" y="13.2" font-size="3" fill="#fff" text-anchor="middle" font-family="Arial">CAD</text>';
	} elseif (in_array($ext, array('xls', 'xlsx'))) {
		$badge = '<rect x="9.5" y="10.5" width="4.5" height="3.5" fill="#4CAF50" stroke="#2E7D32" stroke-width="0.3"/><text x="11.7" y="13.2" font-size="3" fill="#fff" text-anchor="middle" font-family="Arial">XLS</text>';
	} elseif (in_array($ext, array('doc', 'docx'))) {
		$badge = '<rect x="9.5" y="10.5" width="4.5" height="3.5" fill="#1565C0" stroke="#0D47A1" stroke-width="0.3"/><text x="11.7" y="13.2" font-size="3" fill="#fff" text-anchor="middle" font-family="Arial">DOC</text>';
	} elseif (in_array($ext, array('zip', 'rar', '7z'))) {
		$badge = '<rect x="9.5" y="10.5" width="4.5" height="3.5" fill="#795548" stroke="#3E2723" stroke-width="0.3"/><text x="11.7" y="13.2" font-size="3" fill="#fff" text-anchor="middle" font-family="Arial">ZIP</text>';
	} elseif (in_array($ext, array('jpg', 'jpeg', 'png', 'gif', 'bmp'))) {
		$badge = '<rect x="9.5" y="10.5" width="4.5" height="3.5" fill="#9C27B0" stroke="#6A1B9A" stroke-width="0.3"/><text x="11.7" y="13.2" font-size="3" fill="#fff" text-anchor="middle" font-family="Arial">IMG</text>';
	}
	$svg = '<svg viewBox="0 0 16 16" width="16" height="16" class="bom-svg doc-svg" aria-hidden="true" style="display:inline-block;vertical-align:middle;line-height:0;width:16px;height:16px;margin:0 4px 0 0;flex:0 0 auto">'
		. '<path d="M3 1 L10.5 1 L13 3.5 L13 15 L3 15 Z" fill="#fff" stroke="#5F6B7A" stroke-width="0.6"/>'
		. '<path d="M10.5 1 L10.5 3.5 L13 3.5" fill="#CFD8DC" stroke="#5F6B7A" stroke-width="0.4"/>'
		. '<line x1="4.5" y1="6" x2="11" y2="6" stroke="#90A4AE" stroke-width="0.5"/>'
		. '<line x1="4.5" y1="8" x2="11" y2="8" stroke="#90A4AE" stroke-width="0.5"/>'
		. $badge
		. '</svg>';
	return $svg;
}

/* ---------- 会话消息 ---------- */
if (isset($_SESSION['DocMsg']) && is_array($_SESSION['DocMsg'])) {
	foreach ($_SESSION['DocMsg'] as $m) {
		prnMsg($m['msg'], $m['type']);
	}
	unset($_SESSION['DocMsg']);
}

/* ---------- 当前物料 ---------- */
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

/* ================= 删除附件 ================= */
if ($ItemNo != '' && isset($_POST['Delete']) && isset($_POST['del_itemid']) && isset($_POST['del_type']) && isset($_POST['del_patch'])) {
	$delItemid = intval($_POST['del_itemid']);
	$delType = intval($_POST['del_type']);
	$delPatch = $_POST['del_patch'];
	$safePatch = (strpos($delPatch, 'SO/') === 0 && strpos($delPatch, '..') === false);
	if ($safePatch && file_exists($delPatch) && is_file($delPatch)) {
		@unlink($delPatch);
	}
	switch ($delType) {
		case 1:
			DB_query("DELETE FROM sf_item_no_file WHERE itemid=" . $delItemid . " AND item_no='" . $ItemNo . "'", $db);
			break;
		case 2:
			DB_query("DELETE FROM bom_routing_all_file WHERE itemid=" . $delItemid, $db);
			break;
		case 3:
			DB_query("DELETE FROM bom_routing_public_file WHERE itemid=" . $delItemid, $db);
			break;
	}
	if ($safePatch) {
		$_SESSION['DocMsg'][] = array('type' => 'success', 'msg' => '附件已删除！');
	} else {
		$_SESSION['DocMsg'][] = array('type' => 'error', 'msg' => '删除失败：文件路径不合法！');
	}
	header('Location: ' . $RootPath . '/DocFileCenter.php?item_no=' . urlencode($ItemNo));
	exit;
}

/* ================= 上传附件 ================= */
if ($ItemNo != '' && isset($_POST['Save'])) {
	$docType = isset($_POST['doc_type']) ? intval($_POST['doc_type']) : 1;
	$route_id = isset($_POST['route_id']) ? intval($_POST['route_id']) : 0;
	$time = time();
	$upload = new upload2('Pic', 'SO', false, 31457280, $AllowExt, $AllowMime);
	$dest = $upload->uploadFile();
	$uploaded = array_values($dest);
	$cnt = 0;
	$errInfo = '';
	foreach ($uploaded as $u) {
		if (isset($u['error']) && $u['error'] != '文件上传成功') {
			$errInfo = $u['error'];
			break;
		}
	}
	foreach ($_POST as $key => $value) {
		if ($value != '' && substr($key, 0, 9) == 'file_name') {
			$idx = (int) substr($key, 9) - 1;
			if (isset($uploaded[$idx]['dest']) && $uploaded[$idx]['dest'] != '') {
				$fileName = $value;
				$patch = $uploaded[$idx]['dest'];
				$by = $_SESSION['UserID'];
				switch ($docType) {
					case 2:
						if ($route_id == 0) {
							$errInfo = '请选择工艺路线后再上传工艺图档';
							break 2;
						}
						$sql = "INSERT INTO bom_routing_all_file (route_id, file_name, item_no, file_patch, creation_date, created_by) VALUES ('" . $route_id . "','" . $fileName . "','" . $ItemNo . "','" . $patch . "','" . $time . "','" . $by . "')";
						break;
					case 3:
						$sql = "INSERT INTO bom_routing_public_file (file_name, item_no, file_patch, creation_date, created_by) VALUES ('" . $fileName . "','" . $ItemNo . "','" . $patch . "','" . $time . "','" . $by . "')";
						break;
					default:
						$sql = "INSERT INTO sf_item_no_file (item_no, file_name, file_patch, creation_date, created_by) VALUES ('" . $ItemNo . "','" . $fileName . "','" . $patch . "','" . $time . "','" . $by . "')";
				}
				DB_query($sql, $db);
				$cnt++;
			}
		}
	}
	if ($cnt > 0) {
		$_SESSION['DocMsg'][] = array('type' => 'success', 'msg' => '成功上传 ' . $cnt . ' 个附件！');
	} else {
		$_SESSION['DocMsg'][] = array('type' => 'error', 'msg' => '上传失败：' . ($errInfo != '' ? $errInfo : '未选择文件或未填写附件名称'));
	}
	header('Location: ' . $RootPath . '/DocFileCenter.php?item_no=' . urlencode($ItemNo));
	exit;
}

/* ================= 查物料 + 图档 ================= */
$ItemInfo = null;
$itemFiles = array();
$routeList = array();
$routeFiles = array();
$publicFiles = array();

if ($ItemNo != '') {
	$resItem = DB_query("SELECT item_no, item_name, item_desc, item_category1, item_type, item_use, disable_flag FROM sf_item_no WHERE item_no='" . $ItemNo . "'", $db);
	if (DB_num_rows($resItem) > 0) {
		$ItemInfo = DB_fetch_array($resItem);

		$res = DB_query("SELECT file_patch, creation_date, created_by, file_name, itemid FROM sf_item_no_file WHERE item_no='" . $ItemNo . "' ORDER BY creation_date DESC", $db);
		while ($row = DB_fetch_array($res)) {
			$itemFiles[] = $row;
		}

		$resRoute = DB_query("SELECT route_id, operation_code, operation_seq_num, route_status FROM bom_routings_all WHERE assembly_item_no='" . $ItemNo . "' AND (disable_date IS NULL OR disable_date = 0) ORDER BY operation_seq_num", $db);
		while ($row = DB_fetch_array($resRoute)) {
			$routeList[] = $row;
		}

		$res = DB_query("SELECT f.file_patch, f.creation_date, f.created_by, f.file_name, f.itemid, f.route_id, r.operation_code FROM bom_routing_all_file f LEFT JOIN bom_routings_all r ON f.route_id = r.route_id WHERE f.item_no='" . $ItemNo . "' ORDER BY f.creation_date DESC", $db);
		while ($row = DB_fetch_array($res)) {
			$routeFiles[] = $row;
		}

		$res = DB_query("SELECT file_patch, creation_date, created_by, file_name, itemid FROM bom_routing_public_file WHERE item_no='" . $ItemNo . "' ORDER BY creation_date DESC", $db);
		while ($row = DB_fetch_array($res)) {
			$publicFiles[] = $row;
		}
	}
}

/* ================= 查左侧树（按物料分类聚合，只显示有图档的物料） ================= */
$treeGroups = array();
$treeHasAny = false;
$sqlTree = "SELECT DISTINCT i.item_no, i.item_name, i.item_category1,
    (SELECT COUNT(*) FROM sf_item_no_file WHERE item_no=i.item_no) AS c1,
    (SELECT COUNT(*) FROM bom_routing_all_file WHERE item_no=i.item_no) AS c2,
    (SELECT COUNT(*) FROM bom_routing_public_file WHERE item_no=i.item_no) AS c3
    FROM sf_item_no i
    WHERE (EXISTS (SELECT 1 FROM sf_item_no_file WHERE item_no=i.item_no)
        OR EXISTS (SELECT 1 FROM bom_routing_all_file WHERE item_no=i.item_no)
        OR EXISTS (SELECT 1 FROM bom_routing_public_file WHERE item_no=i.item_no))";
if ($SearchFilter != '') {
	$safeQ = str_replace(array('%', '_'), array('\\%', '\\_'), $SearchFilter);
	$sqlTree .= " AND (i.item_no LIKE '%" . $safeQ . "%' OR i.item_name LIKE '%" . $safeQ . "%')";
}
$sqlTree .= " ORDER BY i.item_category1, i.item_no";
$resTree = DB_query($sqlTree, $db);
while ($row = DB_fetch_array($resTree)) {
	$cat = $row['item_category1'] != '' ? $row['item_category1'] : '未未分类';
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
}

$totalItems = 0;
foreach ($treeGroups as $g) { $totalItems += count($g); }
?>
<style type="text/css">
/* ====== 整体布局（参照 BOMSetup .bom-layout） ====== */
.doc-layout{display:flex;width:100%;min-height:520px;gap:8px;margin-top:6px}
.doc-left{width:340px;border:1px solid #c5c5c5;background:#f7f9fc;border-radius:4px;padding:12px;display:flex;flex-direction:column}
.doc-right{flex:1;border:1px solid #c5c5c5;border-radius:4px;padding:12px;background:#fff;min-width:0;display:flex;flex-direction:column}
.doc-left-body{flex:1;overflow:auto;padding-bottom:4px}

/* ====== 左侧头部（参照 BOMSetup .bom-left-head） ====== */
.doc-left-head{display:flex;justify-content:space-between;align-items:center;padding-bottom:10px;margin-bottom:10px;border-bottom:1px solid #d9d9d9;font-size:14px;font-weight:bold;color:#333}
.left-actions{display:flex;gap:6px;flex-wrap:wrap}
.left-actions button{background:#fff;color:#555;border:1px solid #c5c5c5;padding:4px 10px;border-radius:3px;cursor:pointer;font-size:12px;font-weight:normal}
.left-actions button:hover{background:#f0f0f0}
.left-actions button.primary{background:#4a90e2;color:#fff;border-color:#4a90e2}
.left-actions button.primary:hover{background:#357abd;border-color:#357abd}

/* ====== 搜索框（紧贴 head 下方） ====== */
.doc-search-row{display:flex;gap:6px;margin-bottom:10px;align-items:center}
.doc-search-row input[type=text]{flex:1;padding:5px 8px;border:1px solid #c8ced6;border-radius:3px;font-size:12px;min-width:0}
.doc-search-row input[type=text]:focus{outline:none;border-color:#2196F3;box-shadow:0 0 0 2px rgba(33,150,243,0.15)}

/* ====== BOM 风格树（直接复用 BOMSetup .bom-tree 规范） ====== */
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

/* ====== 物料信息卡（参照 BOMSetup .attr-grid） ====== */
.doc-info-card{background:#f9fbfd;border:1px solid #d6e4f0;border-radius:4px;padding:12px 14px;margin-bottom:14px}
.doc-info-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:8px 12px}
.doc-info-item{display:flex;align-items:baseline;font-size:12px;line-height:1.6;background:#fff;border:1px solid #e8eef5;border-radius:3px;padding:5px 8px}
.doc-info-label{color:#666;min-width:5.5em;flex-shrink:0;margin-right:6px}
.doc-info-value{color:#333;font-weight:500;word-break:break-all}

/* ====== 顶部 Tab 切换（参照 BOMSetup .bom-tabs） ====== */
.doc-tabs{display:flex;border-bottom:2px solid #2196F3;margin-bottom:0;align-items:center}
.doc-tab{padding:8px 22px;cursor:pointer;font-size:14px;color:#555;background:#f5f5f5;border:1px solid #d9d9d9;border-bottom:none;border-radius:4px 4px 0 0;margin-right:4px;position:relative;top:2px;transition:all 0.12s;font-weight:500}
.doc-tab:hover{background:#e3f2fd;color:#1976D2}
.doc-tab.active{background:#2196F3;color:#fff;border-color:#2196F3;font-weight:bold}
.doc-tab .ct{margin-left:6px;font-size:12px;opacity:0.85;font-weight:normal}
.doc-tab.active .ct{color:#fff;opacity:0.95}

/* ====== 工具栏（参照 BOMSetup .hier-toolbar） ====== */
.doc-toolbar{display:flex;align-items:center;gap:8px;margin-top:10px;padding:8px 10px;background:#f5f5f5;border:1px solid #e0e0e0;border-radius:3px;flex-wrap:wrap}
.doc-toolbar button{background:#fff;color:#555;border:1px solid #c5c5c5;padding:5px 14px;border-radius:3px;cursor:pointer;font-size:12px;font-weight:500}
.doc-toolbar button:hover{background:#e3f2fd;border-color:#2196F3;color:#1976D2}
.doc-toolbar button.btn-import{background:#4caf50;color:#fff;border-color:#4caf50}
.doc-toolbar button.btn-import:hover{background:#43a047;border-color:#43a047;color:#fff}
.doc-toolbar button.btn-delete{background:#fff;color:#c62828;border-color:#ef9a9a}
.doc-toolbar button.btn-delete:hover{background:#c62828;color:#fff;border-color:#c62828}
.doc-toolbar button.btn-archive{background:#fff;color:#f57c00;border-color:#ffcc80}
.doc-toolbar button.btn-archive:hover{background:#f57c00;color:#fff;border-color:#f57c00}
.doc-toolbar button.btn-export{background:#4caf50;color:#fff;border-color:#4caf50}
.doc-toolbar button.btn-export:hover{background:#43a047;border-color:#43a047;color:#fff}
.doc-toolbar .path-tag{display:inline-flex;align-items:center;gap:4px;background:#fff;border:1px solid #c5c5c5;border-radius:3px;padding:4px 10px;font-size:12px;color:#555;margin-right:auto;min-width:0;max-width:50%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.doc-toolbar .file-count{font-size:12px;color:#666;margin-left:6px}
.doc-toolbar .tab-info{margin-right:auto;font-size:12px;color:#666}
.doc-toolbar .tab-info b{color:#1976D2;font-size:14px;font-weight:bold;margin:0 4px}

/* ====== 上传区 ====== */
.doc-upload{background:#fafbfc;border:1px solid #e0e0e0;border-radius:3px;padding:10px;margin-top:10px}
.doc-upload table{width:100%;border-collapse:collapse}
.doc-upload td{padding:4px 6px;font-size:12px;vertical-align:middle}
.doc-upload .lbl-upload{color:#5a6675;width:120px}
.doc-upload input[type=text]{padding:5px 8px;border:1px solid #c8ced6;border-radius:3px;font-size:12px;width:96%}
.doc-upload input[type=text]:focus{outline:none;border-color:#2196F3}
.doc-upload input[type=file]{font-size:12px;width:96%}
.doc-upload .upload-tip{font-size:11px;color:#888;margin:6px 0 4px 0}
.doc-upload input[type=submit]{background:#4caf50;color:#fff;border:1px solid #4caf50;padding:5px 18px;border-radius:3px;cursor:pointer;font-size:13px;font-weight:500;margin-top:6px}
.doc-upload input[type=submit]:hover{background:#43a047;border-color:#43a047}
.doc-empty{padding:14px;text-align:center;color:#999;font-size:12px;background:#fafbfc;border:1px dashed #d9d9d9;border-radius:3px;margin-top:10px}
.doc-tabs-panel{display:none;flex-direction:column}
.doc-tabs-panel.active{display:flex;flex:1;flex-direction:column}

/* ====== 文件表格（仿 BOMSetup #hierTable） ====== */
.doc-table-wrap{flex:1;overflow:auto;border:1px solid #e0e0e0;border-radius:3px}
.doc-table{width:auto;min-width:100%;border-collapse:separate;border-spacing:0;table-layout:auto}
.doc-table th{position:sticky;top:0;z-index:2;background:#2196F3;color:#fff;font-weight:bold;padding:7px 10px;text-align:center;white-space:nowrap;border-bottom:2px solid #1e88e5;border-right:1px solid #1e88e5;font-size:13px}
.doc-table td{padding:8px 10px;text-align:center;font-size:12px;white-space:nowrap;vertical-align:middle;border-right:1px solid #e0e0e0;border-bottom:1px solid #e0e0e0;line-height:1.6}
.doc-table tr:hover td{background:#f1f8ff}
.doc-table td.left{text-align:left}
.doc-table td .icon-label{display:inline-flex;align-items:center;gap:4px}
.doc-table .op a{margin:0 3px;color:#1976D2;text-decoration:none;font-size:12px}
.doc-table .op a:hover{text-decoration:underline;color:#0d47a1}
.doc-table .op a.del{color:#c62828}
.doc-table .op a.del:hover{color:#b71c1c}
.doc-table .type-tag{display:inline-block;padding:1px 8px;font-size:11px;border-radius:3px;background:#e3f2fd;color:#0d47a1;border:1px solid #bbdefb}
.doc-table .type-tag.cad{background:#fff3e0;color:#e65100;border-color:#ffe0b2}
.doc-table .type-tag.zip{background:#efebe9;color:#3e2723;border-color:#d7ccc8}
.doc-table .type-tag.doc{background:#e3f2fd;color:#1565c0;border-color:#bbdefb}
.doc-table .type-tag.xls{background:#e8f5e9;color:#2e7d32;border-color:#c8e6c9}
.doc-table .type-tag.img{background:#f3e5f5;color:#6a1b9a;border-color:#e1bee7}

/* ====== 删除弹窗 ====== */
.doc-modal{position:fixed;z-index:9999;left:0;top:0;width:100%;height:100%;background-color:rgba(0,0,0,0.5)}
.doc-modal-box{width:460px;margin:16% auto;background:#fff;border-radius:8px;border:1px solid #d5d5d5;box-shadow:0 4px 16px rgba(0,0,0,0.25)}
.doc-modal-title{background:#f0f0f0;padding:10px 16px;font-weight:bold;font-size:14px;border-bottom:1px solid #d5d5d5;border-radius:8px 8px 0 0}
.doc-modal-body{padding:20px 16px;font-size:14px}
.doc-modal-foot{padding:10px 16px;text-align:right;border-top:1px solid #e5e5e5}
.doc-modal-foot input[type=button]{padding:5px 16px;margin-left:10px;cursor:pointer;border:1px solid #aaa;background:#fff;border-radius:4px}

/* ====== 灯箱 ====== */
.doc-img-modal{position:fixed;z-index:9998;left:0;top:0;width:100%;height:100%;background-color:rgba(0,0,0,0.9);text-align:center;padding-top:40px}
.doc-img-modal img{max-width:90%;max-height:85%;margin:auto;border-radius:6px}
.doc-img-close{position:absolute;top:10px;right:30px;color:#fff;font-size:40px;cursor:pointer}
</style>

<div class="doc-layout">

	<!-- ========== 左侧树 ========== -->
	<div class="doc-left">
		<div class="doc-left-head">
			<span>📁 物料分类</span>
			<div class="left-actions">
				<button type="button" onclick="DocTreeExpand(true)">全部展开</button>
				<button type="button" onclick="DocTreeExpand(false)">全部折叠</button>
			</div>
		</div>
		<form id="docSearchForm" method="GET" action="<?php echo $RootPath; ?>/DocFileCenter.php" onsubmit="return DocOnSearch();">
			<div class="doc-search-row">
				<input type="text" id="docQ" name="q" value="<?php echo htmlspecialchars($SearchFilter); ?>" placeholder="搜索物料代码 / 名称…" />
				<button type="submit" class="primary" style="border-radius:3px;">查询</button>
				<?php if ($SearchFilter != '') { ?>
					<a href="<?php echo $RootPath; ?>/DocFileCenter.php<?php echo $ItemNo != '' ? '?item_no=' . urlencode($ItemNo) : ''; ?>" style="font-size:12px;color:#888;text-decoration:none;">清空</a>
				<?php } ?>
			</div>
		</form>
		<div class="doc-left-body">
			<?php if (!$treeHasAny) { ?>
				<div style="padding:14px;color:#888;font-size:12px;text-align:center;">
					<?php echo $SearchFilter != '' ? '未找到匹配的物料' : '暂无图档数据'; ?>
				</div>
			<?php } else { ?>
				<ul class="bom-tree" id="docTree">
					<?php foreach ($treeGroups as $cat => $items) {
						$isCurrentCat = ($CurrentCat != '' && $CurrentCat == $cat);
						$openByDefault = ($SearchFilter != '' || $isCurrentCat);
					?>
					<li class="bom-node top-level<?php echo $openByDefault ? '' : ' collapsed'; ?>" data-cat="<?php echo htmlspecialchars($cat); ?>">
						<div class="bom-row" onclick="DocToggleCat(this)">
							<span class="bom-glyphs">
								<span class="tree-cell node-cell">
									<input type="checkbox">
									<span class="tw"><?php echo $openByDefault ? '-' : '+'; ?></span>
								</span>
							</span>
							<span class="icon-label">
								<?php echo DocIconFolder(); ?>
								<span class="lbl" title="<?php echo htmlspecialchars($cat); ?>"><?php echo htmlspecialchars($cat); ?>（<?php echo count($items); ?>）</span>
							</span>
						</div>
						<?php if (count($items) > 0) { ?>
						<ul class="bom-sub" <?php echo $openByDefault ? '' : 'style="display:none"'; ?>>
							<?php foreach ($items as $idx => $it) {
								$isLast = ($idx == count($items) - 1);
								$selected = ($it['item_no'] == $ItemNo);
								$countBadge = ($it['c1'] + $it['c2'] + $it['c3']) > 0 ? ' [' . ($it['c1'] + $it['c2'] + $it['c3']) . ']' : '';
							?>
							<li class="bom-node leaf-node<?php echo $selected ? ' active' : ''; ?>" data-item="<?php echo htmlspecialchars($it['item_no']); ?>">
								<div class="bom-row" onclick="DocSelectItem('<?php echo htmlspecialchars(addslashes($it['item_no'])); ?>')">
									<span class="bom-glyphs">
										<span class="tree-cell indent-cell<?php echo $isLast ? ' no-sibling' : ''; ?>"><span class="tree-vbar"></span></span>
										<span class="tree-cell node-cell"><span class="tree-hbar"></span></span>
									</span>
									<span class="icon-label">
										<?php echo DocIconFile('file'); ?>
										<span class="lbl" title="<?php echo htmlspecialchars($it['item_no'] . ' / ' . $it['item_name']); ?>">
											<?php echo htmlspecialchars($it['item_no']); ?> <?php echo htmlspecialchars(mb_substr($it['item_name'], 0, 14)); ?><?php echo $countBadge; ?>
										</span>
									</span>
								</div>
							</li>
							<?php } ?>
						</ul>
						<?php } ?>
					</li>
					<?php } ?>
				</ul>
			<?php } ?>
		</div>
		<div style="font-size:11px;color:#888;padding-top:6px;border-top:1px dashed #d9d9d9;">
			共 <b style="color:#1976D2;"><?php echo $totalItems; ?></b> 个物料
		</div>
	</div>

	<!-- ========== 右侧主区 ========== -->
	<div class="doc-right">
		<?php if ($ItemInfo === null) { ?>
			<div style="padding:60px 20px;text-align:center;color:#888;">
				<p style="font-size:15px;margin:0 0 8px 0;">📄 请从左侧选择物料以查看其图档</p>
				<p style="font-size:12px;color:#aaa;margin:0;">左侧树只显示当前有图档的物料（按分类聚合）</p>
			</div>
		<?php } else { ?>
			<!-- 物料信息卡（仿 BOMSetup .attr-grid） -->
			<div class="doc-info-card">
				<div class="doc-info-grid">
					<div class="doc-info-item"><span class="doc-info-label">料号编码：</span><span class="doc-info-value"><?php echo $ItemInfo['item_no']; ?></span></div>
					<div class="doc-info-item"><span class="doc-info-label">料号名称：</span><span class="doc-info-value"><?php echo $ItemInfo['item_name']; ?></span></div>
					<div class="doc-info-item"><span class="doc-info-label">物料分类：</span><span class="doc-info-value"><?php echo $ItemInfo['item_category1']; ?></span></div>
					<div class="doc-info-item"><span class="doc-info-label">规格型号：</span><span class="doc-info-value"><?php echo $ItemInfo['item_desc']; ?></span></div>
					<div class="doc-info-item"><span class="doc-info-label">状态：</span><span class="doc-info-value"><?php echo ($ItemInfo['disable_flag'] == 'N' ? '启用' : '停用'); ?></span></div>
					<div class="doc-info-item"><span class="doc-info-label">用途：</span><span class="doc-info-value"><?php echo ($ItemInfo['item_use'] == 'Y' ? '研发' : '生产'); ?></span></div>
				</div>
			</div>

			<?php
			/* 当前 tab（默认 1 料号图档），tab=1/2/3 */
			$DocActiveTab = isset($_GET['tab']) ? intval($_GET['tab']) : 1;
			if ($DocActiveTab < 1 || $DocActiveTab > 3) {
				$DocActiveTab = 1;
			}
			$TabsConfig = array(
				1 => array('title' => '料号图档', 'data' => $itemFiles, 'docType' => 1, 'path' => '/' . $ItemInfo['item_no']),
				2 => array('title' => '工艺图档', 'data' => $routeFiles, 'docType' => 2, 'path' => '/' . $ItemInfo['item_no'] . ' / 工艺路线'),
				3 => array('title' => '公共图档', 'data' => $publicFiles, 'docType' => 3, 'path' => '/' . $ItemInfo['item_no'] . ' / 公共'),
			);
			?>

			<!-- 顶部 Tab 切换（料号 / 工艺 / 公共） -->
			<div class="doc-tabs">
				<?php foreach ($TabsConfig as $tid => $tcfg) {
					$active = ($DocActiveTab == $tid) ? ' active' : '';
					$tabUrl = '?item_no=' . urlencode($ItemNo) . '&tab=' . $tid;
					if ($SearchFilter != '') {
						$tabUrl .= '&q=' . urlencode($SearchFilter);
					}
				?>
				<div class="doc-tab<?php echo $active; ?>" onclick="window.location.href='DocFileCenter.php<?php echo htmlspecialchars($tabUrl, ENT_QUOTES); ?>'">
					📄 <?php echo $tcfg['title']; ?><span class="ct">（<?php echo count($tcfg['data']); ?>）</span>
				</div>
				<?php } ?>
			</div>

			<!-- 三个 Tab 主体（同时渲染，通过 CSS .active 切换显示） -->
			<?php foreach ($TabsConfig as $tid => $tcfg):
				$active = ($DocActiveTab == $tid) ? ' active' : '';
				$files = $tcfg['data'];
				$docType = $tcfg['docType'];
				$tabPath = $tcfg['path'];
			?>
			<div class="doc-tabs-panel<?php echo $active; ?>" data-tab="<?php echo $tid; ?>">

				<!-- 工具栏 -->
				<div class="doc-toolbar">
					<span class="path-tag">📁 <?php echo htmlspecialchars($tabPath); ?></span>
					<button class="btn-import" type="button" onclick="DocToggleUpload(<?php echo $tid; ?>)">⇧ 导入</button>
					<button class="btn-delete" type="button" onclick="DocBatchDelete(<?php echo $tid; ?>)">✕ 删除</button>
					<button class="btn-archive" type="button" onclick="DocArchive(<?php echo $tid; ?>)">📦 归档</button>
					<button class="btn-export" type="button" onclick="DocExport(<?php echo $tid; ?>)">⇩ 导出</button>
					<span class="file-count">共 <b><?php echo count($files); ?></b> 条</span>
				</div>

				<!-- 上传区（默认折叠，点击"导入"按钮展开） -->
				<div id="docUpload_<?php echo $tid; ?>" class="doc-upload" style="display:none;">
					<?php if ($docType == 2 && count($routeList) == 0) { ?>
						<div class="doc-empty">该物料暂无工艺路线，无法上传工艺图档（请先在「产品工艺设置」中维护工艺路线）。</div>
					<?php } else { ?>
					<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method="POST" enctype="multipart/form-data">
						<input type="hidden" name="FormID" value="<?php echo $_SESSION['FormID']; ?>" />
						<input type="hidden" name="item_no" value="<?php echo htmlspecialchars($ItemNo); ?>" />
						<input type="hidden" name="doc_type" value="<?php echo $docType; ?>" />
						<table>
							<?php if ($docType == 2 && count($routeList) > 0) { ?>
							<tr>
								<td class="lbl-upload">所属工艺路线：</td>
								<td colspan="2">
									<select name="route_id" style="padding:4px 8px;border:1px solid #c8ced6;border-radius:3px;font-size:12px;">
										<option value="0">-- 请选择工艺路线 --</option>
										<?php foreach ($routeList as $r) { ?>
										<option value="<?php echo $r['route_id']; ?>"><?php echo $r['operation_code']; ?>（路线<?php echo $r['route_id']; ?>）</option>
										<?php } ?>
									</select>
								</td>
							</tr>
							<?php } ?>
							<?php for ($jj = 1; $jj <= 5; $jj++) { ?>
							<tr>
								<td class="lbl-upload">附件名称<?php echo $jj; ?>：</td>
								<td><input type="text" name="file_name<?php echo $jj; ?>" placeholder="如：3D图、外箱印刷图档、说明书" /></td>
								<td><input type="file" name="Pic[]" /></td>
							</tr>
							<?php } ?>
						</table>
						<div class="upload-tip">支持 step/stp/dwg/dxf/pdf/zip/jpg/png/doc/xls 等工程文件，单个 ≤ 30MB</div>
						<input type="submit" name="Save" value="上传附件" />
					</form>
					<?php } ?>
				</div>

				<!-- 文件表格 -->
				<?php if (count($files) == 0) { ?>
					<div class="doc-empty">该分类暂无图档，点击「导入」按钮上传</div>
				<?php } else { ?>
				<div class="doc-table-wrap">
				<table class="doc-table">
					<thead>
						<tr>
							<th width="40"><input type="checkbox" onclick="DocToggleAll(this, <?php echo $tid; ?>)" /></th>
							<th width="60">序号</th>
							<th width="40">图标</th>
							<th width="80">状态</th>
							<th width="32%">文件名称</th>
							<th width="80">类型</th>
							<th width="90">大小</th>
							<th width="100">上传人</th>
							<th width="140">上传时间</th>
							<th width="120">所在目录</th>
							<th width="140">操作</th>
						</tr>
					</thead>
					<tbody>
					<?php $idx = 1; foreach ($files as $row):
						list($ftype, $fext) = DocFileTypeInfo($row['file_patch']);
						$fname = $row['file_name'];
						$fpath = $row['file_patch'];
						$fid = $row['itemid'];
						$extra = '';
						$dirLabel = $ItemInfo['item_no'];
						if ($docType == 2) {
							$dirLabel = isset($row['operation_code']) && $row['operation_code'] ? $row['operation_code'] : '工艺路线';
							$extra = ' <span class="type-tag">' . $dirLabel . '</span>';
						} elseif ($docType == 3) {
							$dirLabel = '公共';
						}
					?>
						<tr>
							<td><input type="checkbox" class="doc-row-check" data-tab="<?php echo $tid; ?>" value="<?php echo $fid; ?>" /></td>
							<td><?php echo $idx; ?></td>
							<td><?php echo DocIconFile($fext); ?></td>
							<td><span class="type-tag" style="background:#f5f5f5;color:#888;border-color:#e0e0e0;">正常</span></td>
							<td class="left"><span class="icon-label"><span><?php echo htmlspecialchars($fname); ?></span></span><?php echo $extra; ?></td>
							<td><span class="type-tag <?php echo $ftype; ?>"><?php echo DocFileTypeName($ftype); ?></span></td>
							<td><?php echo DocFileSize($fpath); ?></td>
							<td><?php echo $row['created_by']; ?></td>
							<td><?php echo date('Y-m-d H:i:s', $row['creation_date']); ?></td>
							<td><?php echo htmlspecialchars($dirLabel); ?></td>
							<td class="op">
								<?php if ($ftype == 'img' && file_exists($fpath)) { ?>
									<a href="javascript:void(0)" onclick="DocPreview('<?php echo htmlspecialchars(addslashes($RootPath . '/' . $fpath), ENT_QUOTES); ?>')">预览</a>
								<?php } elseif ($ftype == 'pdf' && file_exists($fpath)) { ?>
									<a href="<?php echo $RootPath . '/' . $fpath; ?>" target="_blank">预览</a>
								<?php } ?>
								<a href="<?php echo $RootPath . '/' . $fpath; ?>" download>下载</a>
								<a href="javascript:void(0)" class="del" onclick="DocAskDelete(<?php echo $fid; ?>,<?php echo $docType; ?>,'<?php echo htmlspecialchars(addslashes($fpath), ENT_QUOTES); ?>','<?php echo htmlspecialchars(addslashes($fname), ENT_QUOTES); ?>')">删除</a>
							</td>
						</tr>
					<?php $idx++; endforeach; ?>
					</tbody>
				</table>
				</div>
				<?php } ?>

				<!-- 行内删除表单（用于单条删除） -->
				<form id="delForm_<?php echo $tid; ?>" method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" style="display:none;">
					<input type="hidden" name="FormID" value="<?php echo $_SESSION['FormID']; ?>" />
					<input type="hidden" name="item_no" value="<?php echo htmlspecialchars($ItemNo); ?>" />
					<input type="hidden" name="Delete" value="1" />
					<input type="hidden" name="del_itemid" value="" />
					<input type="hidden" name="del_type" value="<?php echo $docType; ?>" />
					<input type="hidden" name="del_patch" value="" />
				</form>

			</div>
			<?php endforeach; ?>
		<?php } ?>
	</div>
</div>

<!-- 删除确认弹窗 -->
<div id="DocDelModal" class="doc-modal" style="display:none;">
	<div class="doc-modal-box">
		<div class="doc-modal-title">删除确认</div>
		<div class="doc-modal-body">
			<p>确定要删除附件「<span id="DocDelName"></span>」吗？</p>
			<p style="color:#c62828;font-size:12px;">删除后文件将无法恢复！</p>
		</div>
		<div class="doc-modal-foot">
			<input type="button" value="取消" onclick="document.getElementById('DocDelModal').style.display='none';" />
			<input type="button" value="确认删除" style="background:#c62828;color:#fff;border:none;padding:5px 16px;cursor:pointer;border-radius:4px;" onclick="DocDoDelete()" />
		</div>
	</div>
</div>

<!-- 图片预览灯箱 -->
<div id="DocImgModal" class="doc-img-modal" style="display:none;" onclick="this.style.display='none';">
	<img id="DocImgContent" src="" alt="预览" />
	<span class="doc-img-close" onclick="document.getElementById('DocImgModal').style.display='none';">&times;</span>
</div>

<?php include('includes/footer.inc'); ?>

<script type="text/javascript">
function DocTreeExpand(open) {
	var uls = document.querySelectorAll('#docTree ul.bom-sub');
	var tws = document.querySelectorAll('#docTree .tw');
	for (var i = 0; i < uls.length; i++) { uls[i].style.display = open ? '' : 'none'; }
	for (var j = 0; j < tws.length; j++) { tws[j].innerHTML = open ? '-' : '+'; }
}
function DocToggleCat(row) {
	var li = row.closest('li.bom-node');
	var ul = li.querySelector('ul.bom-sub');
	var tw = row.querySelector('.tw');
	if (!ul) return;
	if (ul.style.display === 'none') {
		ul.style.display = '';
		if (tw) tw.innerHTML = '-';
	} else {
		ul.style.display = 'none';
		if (tw) tw.innerHTML = '+';
	}
}
function DocSelectItem(itemNo) {
	var url = '<?php echo $RootPath; ?>/DocFileCenter.php?item_no=' + encodeURIComponent(itemNo);
	<?php if ($SearchFilter != '') { ?>
	url += '&q=' + encodeURIComponent('<?php echo addslashes($SearchFilter); ?>');
	<?php } ?>
	window.location.href = url;
}
function DocOnSearch() {
	var q = document.getElementById('docQ').value.trim();
	if (!q) { return false; }
	return true;
}
/* Tab 切换：点击 tab 跳转（已用 a 链接实现），此函数保留备用 */
function DocShowTab(tabId) {
	var panels = document.querySelectorAll('.doc-tabs-panel');
	for (var i = 0; i < panels.length; i++) { panels[i].classList.remove('active'); }
	var tabs = document.querySelectorAll('.doc-tab');
	for (var i = 0; i < tabs.length; i++) { tabs[i].classList.remove('active'); }
	var p = document.querySelector('.doc-tabs-panel[data-tab="' + tabId + '"]');
	if (p) p.classList.add('active');
	var t = document.querySelector('.doc-tab[data-tab="' + tabId + '"]');
	if (t) t.classList.add('active');
}
/* 工具栏：导入按钮（切换上传区） */
function DocToggleUpload(tabId) {
	var u = document.getElementById('docUpload_' + tabId);
	if (!u) return;
	u.style.display = (u.style.display === 'none') ? 'block' : 'none';
}
/* 工具栏：导出按钮（占位） */
function DocExport(tabId) { alert('导出 Excel 功能待开发（当前 tab: ' + tabId + '）'); }
/* 工具栏：归档按钮（占位） */
function DocArchive(tabId) { alert('归档功能待开发（当前 tab: ' + tabId + '，需要新增归档字段）'); }
/* 工具栏：批量删除（checkbox + 顶部删除） */
function DocToggleAll(box, tabId) {
	var checks = document.querySelectorAll('.doc-row-check[data-tab="' + tabId + '"]');
	for (var i = 0; i < checks.length; i++) { checks[i].checked = box.checked; }
}
function DocBatchDelete(tabId) {
	var checks = document.querySelectorAll('.doc-row-check[data-tab="' + tabId + '"]:checked');
	if (checks.length === 0) { alert('请先勾选要删除的文件'); return; }
	alert('⚠️ 批量删除功能待开发完整版（已勾选 ' + checks.length + ' 个）。\n当前为简化版：单条删除请用操作列「删除」链接。');
}
function DocPreview(url) {
	document.getElementById('DocImgContent').src = url;
	document.getElementById('DocImgModal').style.display = 'block';
}
var _delForm = null;
function DocAskDelete(itemid, type, patch, name) {
	document.getElementById('DocDelName').innerHTML = name;
	_delForm = 'delForm_' + type;
	document.getElementById('DocDelModal').style.display = 'block';
	var m = document.getElementById('DocDelModal');
	m.setAttribute('data-itemid', itemid);
	m.setAttribute('data-patch', patch);
}
function DocDoDelete() {
	var m = document.getElementById('DocDelModal');
	var f = document.getElementById(_delForm);
	if (!f) return;
	f.elements['del_itemid'].value = m.getAttribute('data-itemid');
	f.elements['del_patch'].value = m.getAttribute('data-patch');
	f.submit();
}
function DocPreview(url) {
	document.getElementById('DocImgContent').src = url;
	document.getElementById('DocImgModal').style.display = 'block';
}
</script>