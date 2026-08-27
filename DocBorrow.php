<?php
/* =====================================================================
 * 文档借阅管理（DocPLM 文档管理体系 · 二期）
 * ---------------------------------------------------------------------
 * 流程：借阅人提交申请 → 审批通过（借出）→ 归还；可驳回 / 撤消申请。
 * 超期：借出日期 + 借阅周期（DocBorrowLimitDays 默认 7 天）未归还 → 红色超期标记。
 * 审批权限：审批人所在部门须对该文档具有「写」权限（未配置规则默认放行）。
 * 样式：浅蓝 Material（#2196F3 主色），与 DocPLM 文档工作区一致。
 * 表：doc_borrow
 * ===================================================================== */
include('includes/session.inc');
require_once 'includes/doc_perm.inc';
DocPermBootstrap($db);

$user = $_SESSION['UserID'];
$deptName = DocUserDeptName($db, $user);
$tab = isset($_GET['tab']) ? trim($_GET['tab']) : 'my';
if (!in_array($tab, array('my', 'approve', 'all'))) { $tab = 'my'; }
$time = time();
$limitDays = DocBorrowLimitDays();

/* ================= 申请借阅 =================
 * 注：POST 处理必须在 header 输出之前执行，否则 header('Location') 重定向会因「headers already sent」失效。
 * ============================================ */
if (isset($_POST['borrow_apply'])) {
	$docId = intval($_POST['doc_id']);
	$reason = isset($_POST['reason']) ? trim($_POST['reason']) : '';
	$r = DB_query("SELECT doc_code, doc_name, status FROM doc_master WHERE doc_id=" . $docId, $db);
	if (!($doc = DB_fetch_array($r))) {
		$_SESSION['BorrowMsg'][] = array('type' => 'error', 'msg' => '文档不存在！');
	} elseif ($doc['status'] != '正常' && $doc['status'] != '已归档') {
		$_SESSION['BorrowMsg'][] = array('type' => 'error', 'msg' => '仅「正常 / 已归档」状态的文档可申请借阅！');
	} elseif (DocBorrowActive($db, $docId)) {
		$_SESSION['BorrowMsg'][] = array('type' => 'error', 'msg' => '该文档当前已被借出（未归还），暂不可再借！');
	} else {
		DB_query("INSERT INTO doc_borrow (doc_id, borrow_user, reason, status, borrow_date, return_date, operator, created_by, creation_date)
			VALUES (" . $docId . ", '" . $user . "', '" . $reason . "', '待审批', " . $time . ", 0, '', '" . $user . "', " . $time . ")", $db);
		DocLogAdd($db, $docId, '借阅申请', $reason);
		$_SESSION['BorrowMsg'][] = array('type' => 'success', 'msg' => '借阅申请已提交，等待审批！');
	}
	header('Location: ' . $RootPath . '/DocBorrow.php?tab=' . $tab);
	exit;
}

/* ================= 审批通过（借出） ================= */
if (isset($_POST['borrow_approve'])) {
	$borrowId = intval($_POST['borrow_id']);
	$r = DB_query("SELECT b.*, m.doc_code, m.doc_name FROM doc_borrow b JOIN doc_master m ON m.doc_id=b.doc_id WHERE b.borrow_id=" . $borrowId . " AND b.status='待审批'", $db);
	if ($row = DB_fetch_array($r)) {
		if (!DocEffectivePerm($db, 'doc', $row['doc_id'], 'write', $deptName)) {
			$_SESSION['BorrowMsg'][] = array('type' => 'error', 'msg' => '您所在部门没有该文档的「写」权限，无权审批！');
		} else {
			DB_query("UPDATE doc_borrow SET status='借出', borrow_date=" . $time . ", operator='" . $user . "' WHERE borrow_id=" . $borrowId, $db);
			DocLogAdd($db, $row['doc_id'], '借阅审批通过', '借出给 ' . $row['borrow_user']);
			$_SESSION['BorrowMsg'][] = array('type' => 'success', 'msg' => '已审批通过，文档借出！');
		}
	} else {
		$_SESSION['BorrowMsg'][] = array('type' => 'error', 'msg' => '记录不存在或已处理！');
	}
	header('Location: ' . $RootPath . '/DocBorrow.php?tab=approve');
	exit;
}

/* ================= 驳回 ================= */
if (isset($_POST['borrow_reject'])) {
	$borrowId = intval($_POST['borrow_id']);
	$r = DB_query("SELECT b.*, m.doc_code FROM doc_borrow b JOIN doc_master m ON m.doc_id=b.doc_id WHERE b.borrow_id=" . $borrowId . " AND b.status='待审批'", $db);
	if ($row = DB_fetch_array($r)) {
		DB_query("UPDATE doc_borrow SET status='已驳回', operator='" . $user . "' WHERE borrow_id=" . $borrowId, $db);
		DocLogAdd($db, $row['doc_id'], '借阅驳回', '');
		$_SESSION['BorrowMsg'][] = array('type' => 'success', 'msg' => '已驳回该借阅申请！');
	} else {
		$_SESSION['BorrowMsg'][] = array('type' => 'error', 'msg' => '记录不存在或已处理！');
	}
	header('Location: ' . $RootPath . '/DocBorrow.php?tab=approve');
	exit;
}

/* ================= 归还 ================= */
if (isset($_POST['borrow_return'])) {
	$borrowId = intval($_POST['borrow_id']);
	$r = DB_query("SELECT b.*, m.doc_code FROM doc_borrow b JOIN doc_master m ON m.doc_id=b.doc_id WHERE b.borrow_id=" . $borrowId . " AND b.status='借出'", $db);
	if ($row = DB_fetch_array($r)) {
		DB_query("UPDATE doc_borrow SET status='已归还', return_date=" . $time . ", operator='" . $user . "' WHERE borrow_id=" . $borrowId, $db);
		DocLogAdd($db, $row['doc_id'], '借阅归还', '');
		$_SESSION['BorrowMsg'][] = array('type' => 'success', 'msg' => '文档已归还！');
	} else {
		$_SESSION['BorrowMsg'][] = array('type' => 'error', 'msg' => '记录不存在或未处于借出状态！');
	}
	header('Location: ' . $RootPath . '/DocBorrow.php?tab=my');
	exit;
}

/* ================= 撤消申请（仅本人待审批） ================= */
if (isset($_POST['borrow_cancel'])) {
	$borrowId = intval($_POST['borrow_id']);
	DB_query("DELETE FROM doc_borrow WHERE borrow_id=" . $borrowId . " AND status='待审批' AND borrow_user='" . $user . "'", $db);
	$_SESSION['BorrowMsg'][] = array('type' => 'success', 'msg' => '借阅申请已撤消！');
	header('Location: ' . $RootPath . '/DocBorrow.php?tab=my');
	exit;
}

$Title = _('文档借阅管理');
$ViewTopic = '文档借阅管理';
$BookMark = '文档借阅管理';
include('includes/header.inc');
include('includes/doc_nav.inc');
include('includes/SQL_CommonFunctions.inc');

/* ---------- 会话消息 ---------- */
if (isset($_SESSION['BorrowMsg']) && is_array($_SESSION['BorrowMsg'])) {
	foreach ($_SESSION['BorrowMsg'] as $m) { prnMsg($m['msg'], $m['type']); }
	unset($_SESSION['BorrowMsg']);
}

/* ================= 借阅文档检索（申请借阅用） ================= */
$docSel = isset($_GET['docsel']) ? trim($_GET['docsel']) : '';
$docList = array();
if ($docSel != '') {
	$safe = str_replace(array('%', '_'), array('\\%', '\\_'), $docSel);
	$r = DB_query("SELECT doc_id, doc_code, doc_name, status FROM doc_master WHERE status<>'已删除' AND (doc_code LIKE '%" . $safe . "%' OR doc_name LIKE '%" . $safe . "%') ORDER BY doc_id DESC LIMIT 20", $db);
	while ($row = DB_fetch_array($r)) { $docList[] = $row; }
}

/* ================= 列表数据 ================= */
$rows = array();
if ($tab == 'my') {
	$r = DB_query("SELECT b.*, m.doc_code, m.doc_name, m.status AS m_status FROM doc_borrow b JOIN doc_master m ON m.doc_id=b.doc_id WHERE b.borrow_user='" . $user . "' ORDER BY b.borrow_id DESC", $db);
} elseif ($tab == 'approve') {
	$r = DB_query("SELECT b.*, m.doc_code, m.doc_name, m.status AS m_status FROM doc_borrow b JOIN doc_master m ON m.doc_id=b.doc_id WHERE b.status='待审批' ORDER BY b.borrow_id DESC", $db);
} else {
	$r = DB_query("SELECT b.*, m.doc_code, m.doc_name, m.status AS m_status FROM doc_borrow b JOIN doc_master m ON m.doc_id=b.doc_id ORDER BY b.borrow_id DESC LIMIT 300", $db);
}
while ($row = DB_fetch_array($r)) { $rows[] = $row; }

$canApprove = array(); /* borrow_id => 是否可审批 */
foreach ($rows as $row) {
	$canApprove[$row['borrow_id']] = DocEffectivePerm($db, 'doc', $row['doc_id'], 'write', $deptName);
}
?>
<style type="text/css">
.borrow-wrap{width:100%}
/* 内嵌标签页 */
.br-tabs{display:flex;gap:0;border-bottom:2px solid #2196F3;margin-bottom:12px}
.br-tab{padding:7px 20px;cursor:pointer;font-size:13px;color:#555;background:#f5f5f5;border:1px solid #d9d9d9;border-bottom:none;border-radius:4px 4px 0 0;margin-right:4px;text-decoration:none;font-weight:500}
.br-tab:hover{background:#e3f2fd;color:#1976D2;text-decoration:none}
.br-tab.active{background:#2196F3;color:#fff;border-color:#2196F3;font-weight:bold}
.br-tab .cnt{font-size:11px;opacity:.9}
/* 申请借阅卡片 */
.br-card{background:#f9fbfd;border:1px solid #d6e4f0;border-radius:4px;padding:12px 14px;margin-bottom:14px}
.br-card h3{margin:0 0 10px 0;font-size:14px;color:#0d47a1;display:flex;align-items:center;gap:6px}
.br-search{display:flex;gap:8px;align-items:center;flex-wrap:wrap}
.br-search input[type=text]{padding:5px 8px;border:1px solid #c8ced6;border-radius:3px;font-size:12px;width:300px}
.br-search input[type=text]:focus{outline:none;border-color:#2196F3}
.br-search button{background:#4a90e2;color:#fff;border:1px solid #4a90e2;padding:5px 16px;border-radius:3px;cursor:pointer;font-size:12px}
.br-search button:hover{background:#357abd}
.br-info{background:#e3f2fd;border:1px solid #bbdefb;border-radius:4px;padding:8px 12px;font-size:12px;color:#0d47a1;margin-bottom:10px}
.br-table{width:100%;border-collapse:separate;border-spacing:0;border:1px solid #e0e0e0;border-radius:3px;background:#fff}
.br-table th{background:#2196F3;color:#fff;font-weight:bold;padding:7px 10px;text-align:center;white-space:nowrap;border-right:1px solid #1e88e5;font-size:13px}
.br-table th:last-child{border-right:none}
.br-table td{padding:7px 10px;text-align:center;font-size:12px;white-space:nowrap;border-right:1px solid #e0e0e0;border-bottom:1px solid #e0e0e0}
.br-table tr:last-child td{border-bottom:none}
.br-table tr:hover td{background:#f1f8ff}
.br-table td.left{text-align:left}
.br-table a{color:#1976D2;text-decoration:none}
.br-table a:hover{text-decoration:underline}
.tag{padding:2px 8px;border-radius:10px;font-size:11px;color:#fff}
.tg-wait{background:#fb8c00}.tg-out{background:#1976D2}.tg-back{background:#2e7d32}.tg-rej{background:#c62828}
.tg-overdue{background:#c62828;border:1px solid #ef9a9a;color:#fff;font-weight:bold}
.br-ops a{margin-right:8px}
.btn-act{display:inline-block;padding:3px 12px;border-radius:3px;font-size:12px;cursor:pointer;border:1px solid;text-decoration:none}
.btn-ok{background:#4caf50;border-color:#4caf50;color:#fff}
.btn-ok:hover{background:#43a047;color:#fff;text-decoration:none}
.btn-no{background:#fff;border-color:#ef9a9a;color:#c62828}
.btn-no:hover{background:#c62828;color:#fff;text-decoration:none}
.btn-pri{background:#4a90e2;border-color:#4a90e2;color:#fff}
.btn-pri:hover{background:#357abd;color:#fff;text-decoration:none}
.btn-dim{background:#fff;border-color:#d9d9d9;color:#888}
.btn-dim:hover{background:#f5f5f5;color:#555;text-decoration:none}
</style>

<div class="borrow-wrap">

	<!-- 内嵌标签页 -->
	<div class="br-tabs">
		<?php
		$tabDefs = array('my' => '我的借阅', 'approve' => '借阅审批', 'all' => '借阅记录');
		foreach ($tabDefs as $k => $label) {
			$cls = ($tab == $k) ? ' active' : '';
			echo '<a class="br-tab' . $cls . '" href="' . $RootPath . '/DocBorrow.php?tab=' . $k . '">' . $label . '</a>';
		}
		?>
	</div>

	<!-- 申请借阅 -->
	<div class="br-card">
		<h3>📥 申请借阅</h3>
		<div class="br-search">
			<form method="GET" action="DocBorrow.php" style="display:flex;gap:8px;align-items:center;margin:0;">
				<input type="hidden" name="tab" value="my" />
				<input type="text" name="docsel" value="<?php echo htmlspecialchars($docSel); ?>" placeholder="输入文档编号或名称检索…" />
				<button type="submit">检索</button>
			</form>
		</div>
		<?php if ($docSel != '' && count($docList) == 0) { ?>
			<div class="br-info" style="background:#fff8e1;border-color:#ffe0b2;color:#e65100;">未检索到相关文档。</div>
		<?php } ?>
		<?php if (count($docList) > 0) { ?>
			<div class="br-info">检索到 <?php echo count($docList); ?> 个文档，选择后填写借阅理由：</div>
			<?php foreach ($docList as $dl) { ?>
				<form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" style="display:flex;gap:8px;align-items:center;margin:0 0 8px 0;padding:8px 10px;border:1px solid #e0e0e0;border-radius:4px;background:#fff;flex-wrap:wrap;">
					<input type="hidden" name="FormID" value="<?php echo $_SESSION['FormID']; ?>" />
					<input type="hidden" name="doc_id" value="<?php echo $dl['doc_id']; ?>" />
					<input type="hidden" name="tab" value="my" />
					<span style="font-size:12px;color:#333;"><b><?php echo htmlspecialchars($dl['doc_code']); ?></b> / <?php echo htmlspecialchars(mb_substr($dl['doc_name'], 0, 20)); ?></span>
					<span class="tag tg-<?php echo $dl['status'] == '正常' ? 'back' : 'out'; ?>" style="background:<?php echo $dl['status'] == '正常' ? '#2e7d32' : '#1976D2'; ?>;"><?php echo $dl['status']; ?></span>
					<input type="text" name="reason" placeholder="借阅理由（必填）" style="flex:1;min-width:200px;padding:5px 8px;border:1px solid #c8ced6;border-radius:3px;font-size:12px;" />
					<button type="submit" name="borrow_apply" class="btn-act btn-ok" value="1">提交申请</button>
				</form>
			<?php } ?>
		<?php } elseif ($docSel == '') { ?>
			<div style="font-size:12px;color:#aaa;margin-top:6px;">检索文档后填写理由提交借阅申请；借阅周期默认 <?php echo $limitDays; ?> 天，超期需及时归还。</div>
		<?php } ?>
	</div>

	<!-- 列表 -->
	<div class="br-card">
		<h3>📋 <?php echo $tabDefs[$tab]; ?> <span style="font-weight:normal;font-size:12px;color:#888;">（共 <?php echo count($rows); ?> 条）</span></h3>
		<?php if (count($rows) == 0) { ?>
			<div style="font-size:12px;color:#aaa;padding:14px 0;">暂无<?php echo $tab == 'approve' ? '待审批的' : ''; ?>借阅记录。</div>
		<?php } else { ?>
		<table class="br-table">
			<tr>
				<th>单号</th>
				<th style="text-align:left;">文档</th>
				<th>借阅人</th>
				<th>状态</th>
				<th>借出时间</th>
				<th>归还时间</th>
				<th>借阅周期</th>
				<th style="text-align:left;">理由</th>
				<th>操作人</th>
				<th width="150">操作</th>
			</tr>
			<?php foreach ($rows as $row) {
				$overdue = false;
				$periodTxt = '—';
				if ($row['status'] == '借出') {
					$due = intval($row['borrow_date']) + $limitDays * 86400;
					$overdue = ($time > $due);
					$periodTxt = '应还 ' . date('Y-m-d', $due) . ($overdue ? '（已超期）' : '');
				}
				$tagCls = 'tg-' . ($row['status'] == '待审批' ? 'wait' : ($row['status'] == '借出' ? 'out' : ($row['status'] == '已归还' ? 'back' : 'rej')));
				echo '<tr>';
				echo '<td>#B' . $row['borrow_id'] . '</td>';
				echo '<td class="left"><b>' . htmlspecialchars($row['doc_code']) . '</b> / ' . htmlspecialchars(mb_substr($row['doc_name'], 0, 18)) . '</td>';
				echo '<td>' . htmlspecialchars($row['borrow_user']) . '</td>';
				echo '<td>' . ($overdue ? '<span class="tag tg-overdue">超期</span> ' : '') . '<span class="tag ' . $tagCls . '">' . $row['status'] . '</span></td>';
				echo '<td>' . ($row['borrow_date'] > 0 ? date('Y-m-d', $row['borrow_date']) : '—') . '</td>';
				echo '<td>' . ($row['return_date'] > 0 ? date('Y-m-d', $row['return_date']) : '—') . '</td>';
				echo '<td>' . $periodTxt . '</td>';
				echo '<td class="left" style="max-width:200px;overflow:hidden;text-overflow:ellipsis;">' . htmlspecialchars($row['reason'] != '' ? $row['reason'] : '—') . '</td>';
				echo '<td>' . ($row['operator'] != '' ? htmlspecialchars($row['operator']) : '—') . '</td>';
				echo '<td class="br-ops">';
				if ($tab == 'approve' && $row['status'] == '待审批') {
					if ($canApprove[$row['borrow_id']]) {
						echo '<form method="POST" action="' . $RootPath . '/DocBorrow.php?tab=approve" style="display:inline;margin:0;">';
						echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" /><input type="hidden" name="borrow_id" value="' . $row['borrow_id'] . '" />';
						echo '<button type="submit" name="borrow_approve" class="btn-act btn-ok" value="1" onclick="return confirm(\'通过该借阅申请并借出？\');">通过</button> ';
						echo '<button type="submit" name="borrow_reject" class="btn-act btn-no" value="1" onclick="return confirm(\'确定驳回该借阅申请？\');">驳回</button>';
						echo '</form>';
					} else {
						echo '<span style="color:#c62828;font-size:11px;">无审批权限</span>';
					}
				} elseif ($tab == 'my') {
					if ($row['status'] == '待审批') {
						echo '<form method="POST" action="' . $RootPath . '/DocBorrow.php?tab=my" style="display:inline;margin:0;">';
						echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" /><input type="hidden" name="borrow_id" value="' . $row['borrow_id'] . '" />';
						echo '<button type="submit" name="borrow_cancel" class="btn-act btn-dim" value="1" onclick="return confirm(\'确定撤消该借阅申请？\');">撤消申请</button>';
						echo '</form>';
					} elseif ($row['status'] == '借出') {
						echo '<form method="POST" action="' . $RootPath . '/DocBorrow.php?tab=my" style="display:inline;margin:0;">';
						echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" /><input type="hidden" name="borrow_id" value="' . $row['borrow_id'] . '" />';
						echo '<button type="submit" name="borrow_return" class="btn-act btn-pri" value="1" onclick="return confirm(\'确认归还该文档？\');">归还</button>';
						echo '</form>';
					}
				}
				echo '</td></tr>';
			} ?>
		</table>
		<?php } ?>
	</div>

	<div class="br-card" style="background:#fff8e1;border-color:#ffe0b2;">
		<div style="font-size:12px;color:#e65100;line-height:1.7;">
			<b>借阅规则</b>：① 仅「正常 / 已归档」文档可借；② 借出后默认 <?php echo $limitDays; ?> 天内归还，超期红色标记；③ 审批需具有该文档「写」权限（未配置权限规则时默认放行）；④ 已借出未归还的文档不可重复借阅。
		</div>
	</div>

</div>

<?php include('includes/footer.inc'); ?>
