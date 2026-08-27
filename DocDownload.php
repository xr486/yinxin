<?php
/* =====================================================================
 * 受控下载 / 预览 / 打印端点（DocPLM 文档管理体系 · 二期）
 * ---------------------------------------------------------------------
 * 用途：所有文档的 下载/预览/打印 统一走本页，强制校验权限后再输出文件，
 *       避免通过直链绕过权限控制。
 * 参数：
 *   doc_id   int    必填，文档 ID（doc_master.doc_id）
 *   file_id  int    可选，指定版本文件（缺省取当前版本 is_current='Y'）
 *   op       string download=下载 / view=预览 / print=打印
 * 权限：view→读，download→下载，print→打印；冻结文档仍可读/下载（只禁改）。
 * ===================================================================== */
include('includes/session.inc');
require_once 'includes/doc_perm.inc';
DocPermBootstrap($db);

$docId = intval(isset($_GET['doc_id']) ? $_GET['doc_id'] : 0);
$fileId = intval(isset($_GET['file_id']) ? $_GET['file_id'] : 0);
$op = isset($_GET['op']) ? trim($_GET['op']) : 'download';
if (!in_array($op, array('download', 'view', 'print'))) { $op = 'download'; }

/* 校验文档存在 */
$r = DB_query("SELECT doc_id, doc_name, folder_id, status, is_template FROM doc_master WHERE doc_id=" . $docId, $db);
if (!($row = DB_fetch_array($r))) {
	DocDeny('文档不存在！');
}
if ($row['status'] == '已删除') { DocDeny('文档已被删除，无法访问！'); }

/* 定位版本文件：指定 file_id 或当前版本 */
if ($fileId > 0) {
	$rf = DB_query("SELECT file_id, version_no, file_name, file_patch, file_ext FROM doc_file WHERE file_id=" . $fileId . " AND doc_id=" . $docId, $db);
} else {
	$rf = DB_query("SELECT file_id, version_no, file_name, file_patch, file_ext FROM doc_file WHERE doc_id=" . $docId . " AND is_current='Y' ORDER BY file_id DESC LIMIT 1", $db);
}
if (!($f = DB_fetch_array($rf))) {
	DocDeny('未找到该文档的文件记录！');
}
if (($f['file_patch'] == '' || !file_exists($f['file_patch']))) {
	DocDeny('文件不存在或已被删除！');
}

/* 权限校验（按当前登录用户的部门） */
$deptName = DocUserDeptName($db, isset($_SESSION['UserID']) ? $_SESSION['UserID'] : '');
$needPerm = ($op == 'download') ? 'download' : (($op == 'print') ? 'print' : 'read');
if (!DocEffectivePerm($db, 'doc', $docId, $needPerm, $deptName)) {
	$permName = array('read' => '读', 'download' => '下载', 'print' => '打印');
	DocDeny('您所在部门没有该文档的「' . $permName[$needPerm] . '」权限！请联系管理员授权。');
}

/* 输出文件 */
$patch = $f['file_patch'];
$ext = strtolower(pathinfo($patch, PATHINFO_EXTENSION));
$fname = ($f['file_name'] != '' ? $f['file_name'] : $row['doc_name']) . ($ext != '' ? '.' . $ext : '');
$fname = preg_replace('/[^\w\.\-\x{4e00}-\x{9fa5} ]/u', '_', $fname);

$mime = DocMimeByExt($ext);
$disposition = ($op == 'download') ? 'attachment' : 'inline';
if ($op == 'print') { $disposition = 'inline'; }

while (ob_get_level()) { ob_end_clean(); }
header('Content-Type: ' . $mime . '; charset=utf-8');
if ($op == 'download') {
	header('Content-Disposition: attachment; filename="' . $fname . '"');
} else {
	header('Content-Disposition: inline; filename="' . $fname . '"');
}
header('Content-Length: ' . filesize($patch));
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, max-age=0');
readfile($patch);
exit;

/* ---------- 拒绝输出 ---------- */
function DocDeny($msg) {
	while (ob_get_level()) { ob_end_clean(); }
	header('Content-Type: text/html; charset=utf-8');
	echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>无权访问</title></head><body style="font-family:Microsoft YaHei,sans-serif;background:#f7f9fc;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;">';
	echo '<div style="background:#fff;border:1px solid #d5d5d5;border-radius:8px;padding:40px 50px;text-align:center;box-shadow:0 4px 16px rgba(0,0,0,.08);">';
	echo '<div style="font-size:44px;margin-bottom:12px;">🔒</div>';
	echo '<div style="font-size:16px;color:#c62828;font-weight:bold;margin-bottom:8px;">访问被拒绝</div>';
	echo '<div style="font-size:13px;color:#555;">' . htmlspecialchars($msg) . '</div>';
	echo '<div style="margin-top:20px;"><a href="' . (isset($GLOBALS['RootPath']) ? $GLOBALS['RootPath'] : '') . '/DocPLM.php" style="display:inline-block;background:#2196F3;color:#fff;text-decoration:none;padding:6px 20px;border-radius:3px;font-size:13px;">返回文档工作区</a></div>';
	echo '</div></body></html>';
	exit;
}

/* ---------- 类型映射 ---------- */
function DocMimeByExt($ext) {
	$map = array(
		'pdf' => 'application/pdf',
		'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png',
		'gif' => 'image/gif', 'bmp' => 'image/bmp', 'webp' => 'image/webp',
		'dwg' => 'application/acad', 'dxf' => 'application/dxf',
		'step' => 'application/step', 'stp' => 'application/step', 'igs' => 'application/iges', 'iges' => 'application/iges',
		'doc' => 'application/msword',
		'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
		'xls' => 'application/vnd.ms-excel',
		'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
		'ppt' => 'application/vnd.ms-powerpoint',
		'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
		'txt' => 'text/plain', 'log' => 'text/plain',
		'zip' => 'application/zip', 'rar' => 'application/x-rar-compressed', '7z' => 'application/x-7z-compressed',
	);
	return isset($map[$ext]) ? $map[$ext] : 'application/octet-stream';
}
