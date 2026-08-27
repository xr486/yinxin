<?php
/* =====================================================================
 * 文档权限 / 借阅 / 冻结 辅助库（DocPLM 文档管理体系 · 二期）
 * ---------------------------------------------------------------------
 * 说明：
 *  1. 权限按【部门】授权：www_users.depart_code（部门名称）= hr_departs.depart_name。
 *     doc_permission.grp_type = 'dept'（部门）/ 'all'（所有部门，grp_value='*'）。
 *  2. 权限类型：perm_type = 'folder'（文件夹）/ 'doc'（文档）。
 *     权限列：can_read 读 / can_write 写 / can_download 下载 / can_delete 删除 / can_print 打印。
 *  3. 生效规则：
 *     - 文件夹权限向上继承（子目录未配置则取最近的有规则祖先）；
 *     - 文档未单独配置权限时，继承其所属文件夹的权限；
 *     - 整条链路都未配置任何规则 → 默认放行（保持与一期行为一致，向后兼容）。
 *  4. 冻结（doc_lock）用于锁定文档禁止升版/修改/删除，仅可读、可下载。
 *  5. 借阅（doc_borrow）流程：待审批 → 借出 → 已归还 / 已驳回。
 * ===================================================================== */

/* ---------- 建表引导（幂等）：权限 / 借阅 / 冻结 + 页面注册 ---------- */
function DocPermBootstrap($db) {
	DB_query("CREATE TABLE IF NOT EXISTS doc_permission (
		perm_id int(11) NOT NULL AUTO_INCREMENT,
		perm_type varchar(10) NOT NULL DEFAULT 'folder',
		target_id int(11) NOT NULL DEFAULT '0',
		grp_type varchar(10) NOT NULL DEFAULT 'user',
		grp_value varchar(60) NOT NULL DEFAULT '*',
		can_read varchar(1) NOT NULL DEFAULT 'N',
		can_write varchar(1) NOT NULL DEFAULT 'N',
		can_download varchar(1) NOT NULL DEFAULT 'N',
		can_delete varchar(1) NOT NULL DEFAULT 'N',
		can_print varchar(1) NOT NULL DEFAULT 'N',
		created_by varchar(50) DEFAULT '',
		creation_date int(11) DEFAULT '0',
		PRIMARY KEY (perm_id),
		KEY idx_perm (perm_type,target_id),
		KEY idx_grp (grp_type,grp_value)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8", $db);
	DB_query("CREATE TABLE IF NOT EXISTS doc_borrow (
		borrow_id int(11) NOT NULL AUTO_INCREMENT,
		doc_id int(11) NOT NULL DEFAULT '0',
		borrow_user varchar(50) DEFAULT '',
		reason varchar(200) DEFAULT '',
		status varchar(20) NOT NULL DEFAULT '待审批',
		borrow_date int(11) DEFAULT '0',
		return_date int(11) DEFAULT '0',
		operator varchar(50) DEFAULT '',
		created_by varchar(50) DEFAULT '',
		creation_date int(11) DEFAULT '0',
		PRIMARY KEY (borrow_id),
		KEY idx_doc (doc_id)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8", $db);
	DB_query("CREATE TABLE IF NOT EXISTS doc_lock (
		doc_id int(11) NOT NULL DEFAULT '0',
		freeze_flag varchar(1) NOT NULL DEFAULT 'N',
		freeze_reason varchar(200) DEFAULT '',
		freeze_by varchar(50) DEFAULT '',
		freeze_date int(11) DEFAULT '0',
		unfreeze_by varchar(50) DEFAULT '',
		unfreeze_date int(11) DEFAULT '0',
		PRIMARY KEY (doc_id)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8", $db);
	/* 目录模板：保存目录结构（含子目录层级/名称），用模板一键建目录 */
	DB_query("CREATE TABLE IF NOT EXISTS doc_folder_tpl (
		tpl_id int(11) NOT NULL AUTO_INCREMENT,
		tpl_name varchar(100) DEFAULT '',
		tpl_desc varchar(200) DEFAULT '',
		src_folder_id int(11) DEFAULT '0',
		node_cnt int(11) DEFAULT '0',
		created_by varchar(50) DEFAULT '',
		creation_date int(11) DEFAULT '0',
		PRIMARY KEY (tpl_id)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8", $db);
	DB_query("CREATE TABLE IF NOT EXISTS doc_folder_tpl_node (
		node_id int(11) NOT NULL AUTO_INCREMENT,
		tpl_id int(11) NOT NULL DEFAULT '0',
		parent_node_id int(11) NOT NULL DEFAULT '0',
		node_name varchar(100) DEFAULT '',
		sort_order int(11) DEFAULT '0',
		PRIMARY KEY (node_id),
		KEY idx_tpl (tpl_id)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8", $db);

	/* 页面注册：新增文档管理页面写入 scripts（function_name='共用' 全员可用，与 DocPLM.php 一致） */
	$pages = array(
		array('DocPerm.php',      '文档权限管理'),
		array('DocBorrow.php',    '文档借阅管理'),
		array('DocLock.php',      '文档冻结管理'),
		array('DocDownload.php',  '文档下载'),
		array('DocPreview.php',   '文档在线预览'),
		array('DocFolderTpl.php', '目录模板管理'),
	);
	$time = time();
	foreach ($pages as $p) {
		DB_query("INSERT INTO scripts (script, pagesecurity, description, creation_date, created_by, function_name)
			VALUES ('" . $p[0] . "', 1, '" . $p[1] . "', '" . $time . "', 'system', '共用')
			ON DUPLICATE KEY UPDATE description=VALUES(description), function_name='共用'", $db);
	}
}

/* ---------- 当前用户部门（名称） ---------- */
function DocUserDeptName($db, $userid) {
	$userid = trim($userid);
	if ($userid == '') { return ''; }
	$r = DB_query("SELECT depart_code FROM www_users WHERE userid='" . $userid . "'", $db);
	if ($row = DB_fetch_array($r)) { return trim($row['depart_code']); }
	return '';
}

/* ---------- 部门列表（hr_departs，enable_flag='Y'） ---------- */
function DocDeptList($db) {
	$depts = array();
	$r = DB_query("SELECT depart_code, depart_name FROM hr_departs WHERE enable_flag='Y' ORDER BY depart_code", $db);
	while ($row = DB_fetch_array($r)) {
		$depts[] = array('code' => trim($row['depart_code']), 'name' => trim($row['depart_name']));
	}
	return $depts;
}

/* ---------- 读取某目标（文件夹/文档）的权限规则 ---------- */
function DocPermRuleRows($db, $permType, $targetId) {
	$rows = array();
	$r = DB_query("SELECT * FROM doc_permission WHERE perm_type='" . $permType . "' AND target_id=" . intval($targetId) . " ORDER BY perm_id", $db);
	while ($row = DB_fetch_array($r)) { $rows[] = $row; }
	return $rows;
}

/* ---------- 判断某部门是否在某组规则中被授予某权限 ---------- */
function DocPermGranted($rows, $deptName, $perm) {
	$col = 'can_' . $perm;
	foreach ($rows as $row) {
		$isAll = ($row['grp_type'] == 'all');
		$isDept = ($row['grp_type'] == 'dept' && $row['grp_value'] == $deptName);
		if ($isAll || $isDept) {
			if (isset($row[$col]) && $row[$col] == 'Y') { return true; }
		}
	}
	return false;
}

/* ---------- 计算当前部门对某目标的某权限（含继承，无规则默认放行） ---------- */
function DocEffectivePerm($db, $permType, $targetId, $perm, $deptName) {
	$targetId = intval($targetId);
	if ($targetId <= 0) { return true; }
	if ($permType == 'doc') {
		$rows = DocPermRuleRows($db, 'doc', $targetId);
		if (count($rows) > 0) { return DocPermGranted($rows, $deptName, $perm); }
		$r = DB_query("SELECT folder_id FROM doc_master WHERE doc_id=" . $targetId, $db);
		if ($row = DB_fetch_array($r)) {
			return DocEffectivePerm($db, 'folder', $row['folder_id'], $perm, $deptName);
		}
		return true;
	}
	/* folder：沿父级向上找最近有规则的目录 */
	$fid = $targetId;
	$guard = 0;
	while ($fid > 0 && $guard < 50) {
		$rows = DocPermRuleRows($db, 'folder', $fid);
		if (count($rows) > 0) { return DocPermGranted($rows, $deptName, $perm); }
		$r = DB_query("SELECT parent_id FROM doc_folder WHERE folder_id=" . $fid, $db);
		if ($row = DB_fetch_array($r)) { $fid = intval($row['parent_id']); } else { $fid = 0; }
		$guard++;
	}
	return true;
}

/* ---------- 某目录（或祖先）是否已配置权限规则 ---------- */
function DocFolderRulesExist($db, $folderId) {
	$fid = intval($folderId);
	$guard = 0;
	while ($fid > 0 && $guard < 50) {
		$r = DB_query("SELECT COUNT(*) c FROM doc_permission WHERE perm_type='folder' AND target_id=" . $fid, $db);
		$row = DB_fetch_row($r);
		if ($row[0] > 0) { return true; }
		$r2 = DB_query("SELECT parent_id FROM doc_folder WHERE folder_id=" . $fid, $db);
		if ($row2 = DB_fetch_array($r2)) { $fid = intval($row2['parent_id']); } else { $fid = 0; }
		$guard++;
	}
	return false;
}

/* ---------- 写入/更新一条权限规则（perm 为可写列集合） ---------- */
function DocPermUpsert($db, $permType, $targetId, $grpType, $grpValue, $perms) {
	$permType = ($permType == 'doc') ? 'doc' : 'folder';
	$grpType = ($grpType == 'all') ? 'all' : 'dept';
	$grpValue = trim($grpValue);
	if ($grpType == 'all') { $grpValue = '*'; }
	if ($grpValue == '' || $grpValue == '*') { $grpType = 'all'; $grpValue = '*'; }
	$grpValue = DB_escape_string($grpValue);
	$r = DB_query("SELECT perm_id FROM doc_permission WHERE perm_type='" . $permType . "' AND target_id=" . intval($targetId) . " AND grp_type='" . $grpType . "' AND grp_value='" . $grpValue . "'", $db);
	$sets = '';
	$vals = array();
	foreach (array('read', 'write', 'download', 'delete', 'print') as $p) {
		$v = (isset($perms[$p]) && $perms[$p] == 'Y') ? 'Y' : 'N';
		$sets .= ($sets == '' ? '' : ',') . "can_" . $p . "='" . $v . "'";
		$vals[] = "'" . $v . "'";
	}
	if ($row = DB_fetch_array($r)) {
		DB_query("UPDATE doc_permission SET " . $sets . " WHERE perm_id=" . intval($row['perm_id']), $db);
	} else {
		DB_query("INSERT INTO doc_permission (perm_type, target_id, grp_type, grp_value, can_read, can_write, can_download, can_delete, can_print, created_by, creation_date)
			VALUES ('" . $permType . "', " . intval($targetId) . ", '" . $grpType . "', '" . $grpValue . "', " . implode(',', $vals) . ", '" . (isset($_SESSION['UserID']) ? $_SESSION['UserID'] : '') . "', " . time() . ")", $db);
	}
}

/* ---------- 删除一条权限规则 ---------- */
function DocPermDelete($db, $permType, $targetId, $grpType, $grpValue) {
	$grpType = ($grpType == 'all') ? 'all' : 'dept';
	if ($grpType == 'all') { $grpValue = '*'; }
	$grpValue = DB_escape_string($grpValue);
	DB_query("DELETE FROM doc_permission WHERE perm_type='" . (($permType == 'doc') ? 'doc' : 'folder') . "' AND target_id=" . intval($targetId) . " AND grp_type='" . $grpType . "' AND grp_value='" . $grpValue . "'", $db);
}

/* ---------- 冻结信息 / 冻结状态 ---------- */
function DocFrozenInfo($db, $docId) {
	$r = DB_query("SELECT * FROM doc_lock WHERE doc_id=" . intval($docId), $db);
	if ($row = DB_fetch_array($r)) { return $row; }
	return null;
}
function DocIsFrozen($db, $docId) {
	$info = DocFrozenInfo($db, $docId);
	return ($info && $info['freeze_flag'] == 'Y');
}

/* ---------- 冻结 / 解冻 ---------- */
function DocLockSet($db, $docId, $flag, $reason) {
	$docId = intval($docId);
	$user = isset($_SESSION['UserID']) ? $_SESSION['UserID'] : '';
	$time = time();
	$exists = DocFrozenInfo($db, $docId);
	if ($exists) {
		if ($flag == 'Y') {
			DB_query("UPDATE doc_lock SET freeze_flag='Y', freeze_reason='" . $reason . "', freeze_by='" . $user . "', freeze_date='" . $time . "' WHERE doc_id=" . $docId, $db);
		} else {
			DB_query("UPDATE doc_lock SET freeze_flag='N', unfreeze_by='" . $user . "', unfreeze_date='" . $time . "' WHERE doc_id=" . $docId, $db);
		}
	} else {
		DB_query("INSERT INTO doc_lock (doc_id, freeze_flag, freeze_reason, freeze_by, freeze_date, unfreeze_by, unfreeze_date)
			VALUES (" . $docId . ", '" . $flag . "', '" . $reason . "', '" . $user . "', " . ($flag == 'Y' ? $time : 0) . ", '', 0)", $db);
	}
	DocLogAdd($db, $docId, ($flag == 'Y' ? '冻结' : '解冻'), $reason);
}

/* ---------- 借阅：当前未归还的借出记录 ---------- */
function DocBorrowActive($db, $docId) {
	$r = DB_query("SELECT * FROM doc_borrow WHERE doc_id=" . intval($docId) . " AND status='借出' ORDER BY borrow_id DESC LIMIT 1", $db);
	if ($row = DB_fetch_array($r)) { return $row; }
	return null;
}
function DocBorrowLatest($db, $docId) {
	$r = DB_query("SELECT * FROM doc_borrow WHERE doc_id=" . intval($docId) . " ORDER BY borrow_id DESC LIMIT 1", $db);
	if ($row = DB_fetch_array($r)) { return $row; }
	return null;
}

/* ---------- 通用日志（doc_log） ---------- */
function DocLogAdd($db, $docId, $action, $remark = '') {
	$user = isset($_SESSION['UserID']) ? $_SESSION['UserID'] : '';
	DB_query("INSERT INTO doc_log (doc_id, action, action_date, operator, remark) VALUES (" . intval($docId) . ", '" . $action . "', " . time() . ", '" . $user . "', '" . $remark . "')", $db);
}

/* ---------- 借阅周期（天）：用于展示超期提醒 ---------- */
function DocBorrowLimitDays() { return 7; }

/* =====================================================================
 * 删除权限：仅 创建者 或 系统管理员 可删除
 * 规则：DocUserIsAdmin()（拥有 WWW_Users 安全令牌 = 系统管理员）
 *       或 当前登录用户 == 文档/附件创建者(created_by)
 * ===================================================================== */
function DocUserIsAdmin() {
	return (isset($_SESSION['PageSecurityArray']['WWW_Users.php'])
		&& isset($_SESSION['AllowedPageSecurityTokens'])
		&& in_array($_SESSION['PageSecurityArray']['WWW_Users.php'], $_SESSION['AllowedPageSecurityTokens']));
}

/* 判断当前用户是否允许删除「指定创建者」的文档/附件 */
function DocCanDeleteByCreator($creator) {
	if (DocUserIsAdmin()) { return true; }
	$u = isset($_SESSION['UserID']) ? trim($_SESSION['UserID']) : '';
	return ($u != '' && $creator != '' && $u === trim($creator));
}

/* =====================================================================
 * 通用展示辅助（图标 / 大小 / 状态标签）
 * 原定义在 DocPLM.php，供 DocPLM/DocPerm/DocBorrow/DocLock 共用
 * ===================================================================== */
function PlmIconFolder() {
	return '<svg viewBox="0 0 16 16" width="16" height="16" style="display:inline-block;vertical-align:middle;width:16px;height:16px;margin:0 2px 0 0;flex:0 0 auto" aria-hidden="true"><path d="M1.5 4 L1.5 14 L14.5 14 L14.5 5.5 L8 5.5 L6.5 4 Z" fill="#FFC107" stroke="#F57C00" stroke-width="0.5"/><path d="M1.5 5.5 L14.5 5.5" stroke="#F57C00" stroke-width="0.4" opacity="0.6"/></svg>';
}
function PlmIconItem() {
	return '<svg viewBox="0 0 16 16" width="16" height="16" style="display:inline-block;vertical-align:middle;width:16px;height:16px;margin:0 2px 0 0;flex:0 0 auto" aria-hidden="true"><rect x="3" y="2" width="10" height="12" fill="#E3F2FD" stroke="#1976D2" stroke-width="0.5" rx="1"/><rect x="5" y="4" width="6" height="1.5" fill="#1976D2"/><rect x="5" y="6.5" width="6" height="1" fill="#90CAF9"/><rect x="5" y="8.5" width="6" height="1" fill="#90CAF9"/><rect x="5" y="10.5" width="4" height="1" fill="#90CAF9"/></svg>';
}
function PlmIconFile($ext = '') {
	$ext = strtolower($ext);
	$badge = '';
	if ($ext == 'pdf') { $badge = '<rect x="9.5" y="10.5" width="4.5" height="3.5" fill="#E53935" stroke="#B71C1C" stroke-width="0.3"/><text x="11.7" y="13.2" font-size="3" fill="#fff" text-anchor="middle" font-family="Arial">PDF</text>'; }
	elseif (in_array($ext, array('step', 'stp', 'igs', 'iges', 'dwg', 'dxf', 'sat'))) { $badge = '<rect x="9.5" y="10.5" width="4.5" height="3.5" fill="#2196F3" stroke="#1565C0" stroke-width="0.3"/><text x="11.7" y="13.2" font-size="3" fill="#fff" text-anchor="middle" font-family="Arial">CAD</text>'; }
	elseif (in_array($ext, array('xls', 'xlsx', 'ods'))) { $badge = '<rect x="9.5" y="10.5" width="4.5" height="3.5" fill="#4CAF50" stroke="#2E7D32" stroke-width="0.3"/><text x="11.7" y="13.2" font-size="3" fill="#fff" text-anchor="middle" font-family="Arial">XLS</text>'; }
	elseif (in_array($ext, array('doc', 'docx', 'odt', 'rtf'))) { $badge = '<rect x="9.5" y="10.5" width="4.5" height="3.5" fill="#1565C0" stroke="#0D47A1" stroke-width="0.3"/><text x="11.7" y="13.2" font-size="3" fill="#fff" text-anchor="middle" font-family="Arial">DOC</text>'; }
	elseif (in_array($ext, array('ppt', 'pptx', 'odp'))) { $badge = '<rect x="9.5" y="10.5" width="4.5" height="3.5" fill="#FB8C00" stroke="#E65100" stroke-width="0.3"/><text x="11.7" y="13.2" font-size="3" fill="#fff" text-anchor="middle" font-family="Arial">PPT</text>'; }
	elseif (in_array($ext, array('zip', 'rar', '7z', 'tar', 'gz'))) { $badge = '<rect x="9.5" y="10.5" width="4.5" height="3.5" fill="#795548" stroke="#3E2723" stroke-width="0.3"/><text x="11.7" y="13.2" font-size="3" fill="#fff" text-anchor="middle" font-family="Arial">ZIP</text>'; }
	elseif (in_array($ext, array('jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'tiff', 'svg'))) { $badge = '<rect x="9.5" y="10.5" width="4.5" height="3.5" fill="#9C27B0" stroke="#6A1B9A" stroke-width="0.3"/><text x="11.7" y="13.2" font-size="3" fill="#fff" text-anchor="middle" font-family="Arial">IMG</text>'; }
	elseif (in_array($ext, array('mph', 'inp', 'cae', 'odb', 'db', 'wbpj', 'rst', 'dsdb', 'cas', 'msh', 'slx', 'mdl', 'sim', 'bdf', 'nas'))) { $badge = '<rect x="9.5" y="10.5" width="4.5" height="3.5" fill="#00897B" stroke="#00695C" stroke-width="0.3"/><text x="11.7" y="13.2" font-size="3" fill="#fff" text-anchor="middle" font-family="Arial">SIM</text>'; }
	return '<svg viewBox="0 0 16 16" width="16" height="16" style="display:inline-block;vertical-align:middle;width:16px;height:16px;margin:0 4px 0 0;flex:0 0 auto" aria-hidden="true"><path d="M3 1 L10.5 1 L13 3.5 L13 15 L3 15 Z" fill="#fff" stroke="#5F6B7A" stroke-width="0.6"/><path d="M10.5 1 L10.5 3.5 L13 3.5" fill="#CFD8DC" stroke="#5F6B7A" stroke-width="0.4"/><line x1="4.5" y1="6" x2="11" y2="6" stroke="#90A4AE" stroke-width="0.5"/><line x1="4.5" y1="8" x2="11" y2="8" stroke="#90A4AE" stroke-width="0.5"/>' . $badge . '</svg>';
}
function PlmSize($patch) {
	if (!file_exists($patch)) return '—';
	$s = @filesize($patch);
	if ($s >= 1048576) return round($s / 1048576, 2) . ' MB';
	if ($s >= 1024) return round($s / 1024, 1) . ' KB';
	return $s . ' B';
}
function PlmStatusTag($status) {
	switch ($status) {
		case '正常': return '<span class="plm-tag st-ok">正常</span>';
		case '已归档': return '<span class="plm-tag st-arch">已归档</span>';
		case '已废止': return '<span class="plm-tag st-abd">已废止</span>';
		case '已删除': return '<span class="plm-tag st-del">已删除</span>';
		default: return '<span class="plm-tag">' . htmlspecialchars($status) . '</span>';
	}
}
