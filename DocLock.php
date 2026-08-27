<?php
/* =====================================================================
 * 文档冻结 / 解冻管理（DocPLM 文档管理体系 · 二期）
 * ---------------------------------------------------------------------
 * 冻结：锁定文档，禁止 升版/修改属性/删除/废止/归档 等写操作，仅可读、可下载、可打印。
 * 解冻：解除锁定，恢复可写。
 * 权限：冻结/解冻需所在部门对该文档具有「写」权限（未配置规则默认放行）。
 * 样式：浅蓝 Material（#2196F3 主色），与 DocPLM 文档工作区一致。
 * 表：doc_lock
 * ===================================================================== */
include('includes/session.inc');
require_once 'includes/doc_perm.inc';
DocPermBootstrap($db);

$user = $_SESSION['UserID'];
$deptName = DocUserDeptName($db, $user);
$time = time();
$show = isset($_GET['show']) ? trim($_GET['show']) : 'frozen';
if (!in_array($show, array('frozen', 'history'))) { $show = 'frozen'; }

/* ================= 冻结 =================
 * 注：POST 处理必须在 header 输出之前执行，否则 header('Location') 重定向会因「headers already sent」失效。
 * ========================================= */
if (isset($_POST['lock_set'])) {
	$docId = intval($_POST['doc_id']);
	$reason = isset($_POST['reason']) ? trim($_POST['reason']) : '';
	$r = DB_query("SELECT doc_code, doc_name, status FROM doc_master WHERE doc_id=" . $docId, $db);
	if (!($doc = DB_fetch_array($r))) {
		$_SESSION['LockMsg'][] = array('type' => 'error', 'msg' => '文档不存在！');
	} elseif ($doc['status'] != '正常') {
		$_SESSION['LockMsg'][] = array('type' => 'error', 'msg' => '仅「正常」状态的文档可冻结！');
	} elseif (DocIsFrozen($db, $docId)) {
		$_SESSION['LockMsg'][] = array('type' => 'error', 'msg' => '该文档已被冻结！');
	} elseif (!DocEffectivePerm($db, 'doc', $docId, 'write', $deptName)) {
		$_SESSION['LockMsg'][] = array('type' => 'error', 'msg' => '您所在部门没有该文档的「写」权限，无权冻结！');
	} else {
		DocLockSet($db, $docId, 'Y', $reason);
		$_SESSION['LockMsg'][] = array('type' => 'success', 'msg' => '文档已冻结：' . $doc['doc_code']);
	}
	header('Location: ' . $RootPath . '/DocLock.php?show=frozen');
	exit;
}

/* ================= 解冻 ================= */
if (isset($_POST['lock_unfreeze'])) {
	$docId = intval($_POST['doc_id']);
	$r = DB_query("SELECT doc_code, status FROM doc_master WHERE doc_id=" . $docId, $db);
	if (!($doc = DB_fetch_array($r))) {
		$_SESSION['LockMsg'][] = array('type' => 'error', 'msg' => '文档不存在！');
	} elseif (!DocIsFrozen($db, $docId)) {
		$_SESSION['LockMsg'][] = array('type' => 'error', 'msg' => '该文档当前未处于冻结状态！');
	} elseif (!DocEffectivePerm($db, 'doc', $docId, 'write', $deptName)) {
		$_SESSION['LockMsg'][] = array('type' => 'error', 'msg' => '您所在部门没有该文档的「写」权限，无权解冻！');
	} else {
		DocLockSet($db, $docId, 'N', '');
		$_SESSION['LockMsg'][] = array('type' => 'success', 'msg' => '文档已解冻：' . $doc['doc_code']);
	}
	header('Location: ' . $RootPath . '/DocLock.php?show=frozen');
	exit;
}

$Title = _('文档冻结管理');
$ViewTopic = '文档冻结管理';
$BookMark = '文档冻结管理';
include('includes/header.inc');
include('includes/doc_nav.inc');
include('includes/SQL_CommonFunctions.inc');

/* ---------- 会话消息 ---------- */
if (isset($_SESSION['LockMsg']) && is_array($_SESSION['LockMsg'])) {
	foreach ($_SESSION['LockMsg'] as $m) { prnMsg($m['msg'], $m['type']); }
	unset($_SESSION['LockMsg']);
}

/* ================= 冻结文档检索（冻结用） ================= */
$docSel = isset($_GET['docsel']) ? trim($_GET['docsel']) : '';
$docList = array();
if ($docSel != '') {
	$safe = str_replace(array('%', '_'), array('\\%', '\\_'), $docSel);
	$r = DB_query("SELECT m.doc_id, m.doc_code, m.doc_name, m.status, IF(l.freeze_flag='Y',1,0) AS frozen
		FROM doc_master m LEFT JOIN doc_lock l ON l.doc_id=m.doc_id
		WHERE m.status<>'已删除' AND (m.doc_code LIKE '%" . $safe . "%' OR m.doc_name LIKE '%" . $safe . "%') ORDER BY m.doc_id DESC LIMIT 20", $db);
	while ($row = DB_fetch_array($r)) { $docList[] = $row; }
}

/* ================= 数据：当前冻结列表 ================= */
$frozenDocs = array();
$r = DB_query("SELECT l.*, m.doc_code, m.doc_name, m.status, m.current_version
	FROM doc_lock l JOIN doc_master m ON m.doc_id=l.doc_id
	WHERE l.freeze_flag='Y' ORDER BY l.freeze_date DESC", $db);
while ($row = DB_fetch_array($r)) { $frozenDocs[] = $row; }

/* ================= 数据：冻结历史（全部 doc_lock 记录） ================= */
$history = array();
$r = DB_query("SELECT l.*, m.doc_code, m.doc_name, m.status
	FROM doc_lock l JOIN doc_master m ON m.doc_id=l.doc_id
	ORDER BY l.freeze_date DESC, l.unfreeze_date DESC LIMIT 200", $db);
while ($row = DB_fetch_array($r)) { $history[] = $row; }
?>
<style type="text/css">
.lock-wrap{width:100%}
.lk-tabs{display:flex;gap:0;border-bottom:2px solid #2196F3;margin-bottom:12px}
.lk-tab{padding:7px 20px;cursor:pointer;font-size:13px;color:#555;background:#f5f5f5;border:1px solid #d9d9d9;border-bottom:none;border-radius:4px 4px 0 0;margin-right:4px;text-decoration:none;font-weight:500}
.lk-tab:hover{background:#e3f2fd;color:#1976D2;text-decoration:none}
.lk-tab.active{background:#2196F3;color:#fff;border-color:#2196F3;font-weight:bold}
.lk-card{background:#f9fbfd;border:1px solid #d6e4f0;border-radius:4px;padding:12px 14px;margin-bottom:14px}
.lk-card h3{margin:0 0 10px 0;font-size:14px;color:#0d47a1;display:flex;align-items:center;gap:6px}
.lk-search{display:flex;gap:8px;align-items:center;flex-wrap:wrap}
.lk-search input[type=text]{padding:5px 8px;border:1px solid #c8ced6;border-radius:3px;font-size:12px;width:300px}
.lk-search input[type=text]:focus{outline:none;border-color:#2196F3}
.lk-search button{background:#4a90e2;color:#fff;border:1px solid #4a90e2;padding:5px 16px;border-radius:3px;cursor:pointer;font-size:12px}
.lk-search button:hover{background:#357abd}
.lk-info{background:#e3f2fd;border:1px solid #bbdefb;border-radius:4px;padding:8px 12px;font-size:12px;color:#0d47a1;margin-bottom:10px}
.lk-table{width:100%;border-collapse:separate;border-spacing:0;border:1px solid #e0e0e0;border-radius:3px;background:#fff}
.lk-table th{background:#2196F3;color:#fff;font-weight:bold;padding:7px 10px;text-align:center;white-space:nowrap;border-right:1px solid #1e88e5;font-size:13px}
.lk-table th:last-child{border-right:none}
.lk-table td{padding:7px 10px;text-align:center;font-size:12px;white-space:nowrap;border-right:1px solid #e0e0e0;border-bottom:1px solid #e0e0e0}
.lk-table tr:last-child td{border-bottom:none}
.lk-table tr:hover td{background:#f1f8ff}
.lk-table td.left{text-align:left}
.lk-table a{color:#1976D2;text-decoration:none}
.lk-table a:hover{text-decoration:underline}
.btn-act{display:inline-block;padding:3px 14px;border-radius:3px;font-size:12px;cursor:pointer;border:1px solid;text-decoration:none}
.btn-un{background:#4a90e2;border-color:#4a90e2;color:#fff}
.btn-un:hover{background:#357abd;color:#fff;text-decoration:none}
.btn-no{background:#fff;border-color:#ef9a9a;color:#c62828}
.btn-no:hover{background:#c62828;color:#fff;text-decoration:none}
.fz-tag{padding:2px 10px;border-radius:10px;font-size:11px;color:#fff}
.fz-yes{background:#c62828}.fz-no{background:#2e7d32}
</style>

<div class="lock-wrap">

	<!-- 内嵌标签页 -->
	<div class="lk-tabs">
		<?php
		$tabDefs = array('frozen' => '已冻结文档', 'history' => '冻结历史');
		foreach ($tabDefs as $k => $label) {
			$cls = ($show == $k) ? ' active' : '';
			echo '<a class="lk-tab' . $cls . '" href="' . $RootPath . '/DocLock.php?show=' . $k . '">' . $label . '</a>';
		}
		?>
	</div>

	<!-- 冻结 -->
	<div class="lk-card">
		<h3>🔒 冻结文档</h3>
		<div class="lk-search">
			<form method="GET" action="DocLock.php" style="display:flex;gap:8px;align-items:center;margin:0;">
				<input type="hidden" name="show" value="frozen" />
				<input type="text" name="docsel" value="<?php echo htmlspecialchars($docSel); ?>" placeholder="输入文档编号或名称检索…" />
				<button type="submit">检索</button>
			</form>
		</div>
		<?php if ($docSel != '' && count($docList) == 0) { ?>
			<div class="lk-info" style="background:#fff8e1;border-color:#ffe0b2;color:#e65100;">未检索到相关文档。</div>
		<?php } ?>
		<?php if (count($docList) > 0) { ?>
			<div class="lk-info">检索到 <?php echo count($docList); ?> 个文档，填写冻结原因后冻结：</div>
			<?php foreach ($docList as $dl) { ?>
				<form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" style="display:flex;gap:8px;align-items:center;margin:0 0 8px 0;padding:8px 10px;border:1px solid #e0e0e0;border-radius:4px;background:#fff;flex-wrap:wrap;">
					<input type="hidden" name="FormID" value="<?php echo $_SESSION['FormID']; ?>" />
					<input type="hidden" name="doc_id" value="<?php echo $dl['doc_id']; ?>" />
					<span style="font-size:12px;color:#333;"><b><?php echo htmlspecialchars($dl['doc_code']); ?></b> / <?php echo htmlspecialchars(mb_substr($dl['doc_name'], 0, 20)); ?></span>
					<?php if ($dl['frozen'] == 1) { ?>
						<span class="fz-tag fz-yes">已冻结</span>
						<button type="button" class="btn-act btn-no" onclick="alert('该文档已冻结，请到「已冻结文档」中解冻');">冻结</button>
					<?php } else { ?>
						<input type="text" name="reason" placeholder="冻结原因（必填）" style="flex:1;min-width:180px;padding:5px 8px;border:1px solid #c8ced6;border-radius:3px;font-size:12px;" />
						<button type="submit" name="lock_set" class="btn-act btn-un" value="1" onclick="return confirm('确定冻结该文档？冻结后禁止修改/升版/删除。');">冻结</button>
					<?php } ?>
				</form>
			<?php } ?>
		<?php } elseif ($docSel == '') { ?>
			<div style="font-size:12px;color:#aaa;margin-top:6px;">检索文档后填写原因冻结；冻结后文档仅可读、可下载、可打印，禁止升版 / 修改 / 删除。</div>
		<?php } ?>
	</div>

	<!-- 列表 -->
	<?php if ($show == 'frozen') { ?>
	<div class="lk-card">
		<h3>📌 已冻结文档 <span style="font-weight:normal;font-size:12px;color:#888;">（共 <?php echo count($frozenDocs); ?> 个）</span></h3>
		<?php if (count($frozenDocs) == 0) { ?>
			<div style="font-size:12px;color:#aaa;padding:14px 0;">当前没有冻结中的文档。</div>
		<?php } else { ?>
		<table class="lk-table">
			<tr>
				<th style="text-align:left;">文档</th>
				<th>版本</th>
				<th>状态</th>
				<th>冻结原因</th>
				<th>冻结人</th>
				<th>冻结时间</th>
				<th width="120">操作</th>
			</tr>
			<?php foreach ($frozenDocs as $row) { ?>
			<tr>
				<td class="left"><b><?php echo htmlspecialchars($row['doc_code']); ?></b> / <?php echo htmlspecialchars(mb_substr($row['doc_name'], 0, 18)); ?></td>
				<td><?php echo $row['current_version']; ?></td>
				<td><span class="fz-tag fz-yes">已冻结</span></td>
				<td class="left" style="max-width:200px;overflow:hidden;text-overflow:ellipsis;"><?php echo htmlspecialchars($row['freeze_reason'] != '' ? $row['freeze_reason'] : '—'); ?></td>
				<td><?php echo htmlspecialchars($row['freeze_by']); ?></td>
				<td><?php echo date('Y-m-d H:i', $row['freeze_date']); ?></td>
				<td>
					<form method="POST" action="<?php echo $RootPath; ?>/DocLock.php?show=frozen" style="display:inline;margin:0;">
						<input type="hidden" name="FormID" value="<?php echo $_SESSION['FormID']; ?>" />
						<input type="hidden" name="doc_id" value="<?php echo $row['doc_id']; ?>" />
						<button type="submit" name="lock_unfreeze" class="btn-act btn-un" value="1" onclick="return confirm('确定解冻该文档？解冻后恢复可写。');">解冻</button>
					</form>
				</td>
			</tr>
			<?php } ?>
		</table>
		<?php } ?>
	</div>
	<?php } else { ?>
	<div class="lk-card">
		<h3>🗂 冻结历史 <span style="font-weight:normal;font-size:12px;color:#888;">（共 <?php echo count($history); ?> 条）</span></h3>
		<?php if (count($history) == 0) { ?>
			<div style="font-size:12px;color:#aaa;padding:14px 0;">暂无冻结记录。</div>
		<?php } else { ?>
		<table class="lk-table">
			<tr>
				<th style="text-align:left;">文档</th>
				<th>当前状态</th>
				<th>冻结原因</th>
				<th>冻结人</th>
				<th>冻结时间</th>
				<th>解冻人</th>
				<th>解冻时间</th>
			</tr>
			<?php foreach ($history as $row) { ?>
			<tr>
				<td class="left"><b><?php echo htmlspecialchars($row['doc_code']); ?></b> / <?php echo htmlspecialchars(mb_substr($row['doc_name'], 0, 18)); ?></td>
				<td><span class="fz-tag <?php echo $row['freeze_flag'] == 'Y' ? 'fz-yes' : 'fz-no'; ?>"><?php echo $row['freeze_flag'] == 'Y' ? '已冻结' : '已解冻'; ?></span></td>
				<td class="left" style="max-width:200px;overflow:hidden;text-overflow:ellipsis;"><?php echo htmlspecialchars($row['freeze_reason'] != '' ? $row['freeze_reason'] : '—'); ?></td>
				<td><?php echo htmlspecialchars($row['freeze_by']); ?></td>
				<td><?php echo $row['freeze_date'] > 0 ? date('Y-m-d H:i', $row['freeze_date']) : '—'; ?></td>
				<td><?php echo htmlspecialchars($row['unfreeze_by'] != '' ? $row['unfreeze_by'] : '—'); ?></td>
				<td><?php echo $row['unfreeze_date'] > 0 ? date('Y-m-d H:i', $row['unfreeze_date']) : '—'; ?></td>
			</tr>
			<?php } ?>
		</table>
		<?php } ?>
	</div>
	<?php } ?>

</div>

<?php include('includes/footer.inc'); ?>
