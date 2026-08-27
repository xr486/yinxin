<?php
/* =====================================================================
 * 目录模板管理（DocPLM 文档管理体系 · 三期）
 * ---------------------------------------------------------------------
 * 功能：
 *  1. 把任意已有目录（含子目录层级 / 名称 / 排序）保存为目录模板；
 *  2. 用模板一键创建新目录（可选把源目录的权限规则套用到新目录根）。
 * 表：doc_folder_tpl / doc_folder_tpl_node（由 DocPermBootstrap 自动建表）
 * 样式：浅蓝 Material（#2196F3 主色），与 DocPLM 文档工作区一致。
 * ===================================================================== */
include('includes/session.inc');
require_once 'includes/doc_perm.inc';
DocPermBootstrap($db);

$user = $_SESSION['UserID'];
$time = time();

/* ---------- 工具函数 ---------- */
/* 目录全路径（下拉选择用） */
function DocTplFolderPath($db, $folderId) {
	$parts = array();
	$cur = intval($folderId);
	$guard = 0;
	while ($cur > 0 && $guard < 30) {
		$r = DB_query("SELECT folder_id, parent_id, folder_name FROM doc_folder WHERE folder_id=" . $cur, $db);
		if ($row = DB_fetch_array($r)) {
			array_unshift($parts, $row['folder_name']);
			$cur = intval($row['parent_id']);
		} else { $cur = 0; }
		$guard++;
	}
	return count($parts) ? implode(' / ', $parts) : '主目录';
}

/* 收集某目录下所有子目录（递归；根目录本身不入模板，模板结构 = 其子目录树） */
function DocTplCollect($db, $folderId, $parent, &$nodes) {
	$r = DB_query("SELECT folder_id, folder_name, sort_order FROM doc_folder WHERE parent_id=" . intval($folderId) . " AND disable_flag<>'Y' ORDER BY sort_order, folder_id", $db);
	while ($row = DB_fetch_array($r)) {
		$nodes[] = array('folder_id' => intval($row['folder_id']), 'parent' => $parent, 'name' => $row['folder_name'], 'sort' => intval($row['sort_order']));
		DocTplCollect($db, intval($row['folder_id']), intval($row['folder_id']), $nodes);
	}
}

/* 按模板节点递归建目录（parent 用模板内 node_id 关联） */
function DocTplCreateChildren($db, $nodes, $parentNodeId, $parentFolderId, &$map, $user) {
	$time = time();
	foreach ($nodes as $n) {
		if (intval($n['parent_node_id']) != $parentNodeId) { continue; }
		DB_query("INSERT INTO doc_folder (parent_id, folder_name, folder_code, folder_type, sort_order, disable_flag, created_by, creation_date)
			VALUES ('" . $parentFolderId . "','" . $n['node_name'] . "','','folder','" . intval($n['sort_order']) . "','N','" . $user . "','" . $time . "')", $db);
		$newId = isset($_SESSION['LastInsertId']) ? intval($_SESSION['LastInsertId']) : 0;
		$map[intval($n['node_id'])] = $newId;
		DocTplCreateChildren($db, $nodes, intval($n['node_id']), $newId, $map, $user);
	}
}

/* ================= 保存目录为模板 =================
 * 注：POST 处理必须在 header 输出之前执行，否则 header('Location') 重定向会因「headers already sent」失效。
 * ================================================== */
if (isset($_POST['tpl_save'])) {
	$srcId = intval($_POST['src_folder_id']);
	$tplName = trim($_POST['tpl_name']);
	$tplDesc = trim($_POST['tpl_desc']);
	$err = '';
	if ($srcId <= 0) { $err = '请选择要保存的源目录！'; }
	elseif ($tplName == '') { $err = '请输入模板名称！'; }
	else {
		$r = DB_query("SELECT folder_name FROM doc_folder WHERE folder_id=" . $srcId . " AND disable_flag<>'Y'", $db);
		if (!($row = DB_fetch_array($r))) { $err = '源目录不存在或已被删除！'; }
	}
	if ($err != '') {
		$_SESSION['TplMsg'][] = array('type' => 'error', 'msg' => $err);
	} else {
		$nodes = array();
		DocTplCollect($db, $srcId, 0, $nodes);
		DB_query("INSERT INTO doc_folder_tpl (tpl_name, tpl_desc, src_folder_id, node_cnt, created_by, creation_date)
			VALUES ('" . $tplName . "','" . $tplDesc . "','" . $srcId . "','" . count($nodes) . "','" . $user . "','" . $time . "')", $db);
		$tplId = isset($_SESSION['LastInsertId']) ? intval($_SESSION['LastInsertId']) : 0;
		$map = array();
		foreach ($nodes as $n) {
			$pidNode = ($n['parent'] > 0 && isset($map[$n['parent']])) ? $map[$n['parent']] : 0;
			DB_query("INSERT INTO doc_folder_tpl_node (tpl_id, parent_node_id, node_name, sort_order)
				VALUES ('" . $tplId . "','" . $pidNode . "','" . $n['name'] . "','" . $n['sort'] . "')", $db);
			$map[$n['folder_id']] = isset($_SESSION['LastInsertId']) ? intval($_SESSION['LastInsertId']) : 0;
		}
		$_SESSION['TplMsg'][] = array('type' => 'success', 'msg' => '目录模板「' . $tplName . '」保存成功（含 ' . count($nodes) . ' 个子目录）！');
	}
	header('Location: ' . $RootPath . '/DocFolderTpl.php');
	exit;
}

/* ================= 用模板创建目录 ================= */
if (isset($_POST['tpl_apply'])) {
	$tplId = intval($_POST['tpl_id']);
	$parentId = intval($_POST['parent_id']);
	$newName = trim($_POST['new_name']);
	$copyPerm = (isset($_POST['copy_perm']) && $_POST['copy_perm'] == '1') ? 1 : 0;
	$err = '';
	$srcId = 0;
	$r = DB_query("SELECT * FROM doc_folder_tpl WHERE tpl_id=" . $tplId, $db);
	if (!($tpl = DB_fetch_array($r))) {
		$err = '模板不存在！';
	} else {
		$srcId = intval($tpl['src_folder_id']);
		if ($newName == '') { $err = '请输入新目录名称！'; }
	}
	if ($err == '') {
		/* 模板根节点（parent_node_id=0，即模板结构的顶层） */
		$nodes = array();
		$r = DB_query("SELECT * FROM doc_folder_tpl_node WHERE tpl_id=" . $tplId . " ORDER BY node_id", $db);
		while ($row = DB_fetch_array($r)) { $nodes[] = $row; }
		$rootNodes = array();
		foreach ($nodes as $n) { if (intval($n['parent_node_id']) == 0) { $rootNodes[] = $n; } }
		if (count($rootNodes) == 0 && count($nodes) > 0) { $err = '模板结构异常（缺少根节点）！'; }
	}
	if ($err != '') {
		$_SESSION['TplMsg'][] = array('type' => 'error', 'msg' => $err);
	} else {
		/* 建新根目录 */
		DB_query("INSERT INTO doc_folder (parent_id, folder_name, folder_code, folder_type, sort_order, disable_flag, created_by, creation_date)
			VALUES ('" . $parentId . "','" . $newName . "','','folder',0,'N','" . $user . "','" . $time . "')", $db);
		$rootFolderId = isset($_SESSION['LastInsertId']) ? intval($_SESSION['LastInsertId']) : 0;
		$map = array();
		/* 递归创建子目录（模板根节点挂在新建根目录下） */
		$created = 0;
		foreach ($rootNodes as $rn) {
			DB_query("INSERT INTO doc_folder (parent_id, folder_name, folder_code, folder_type, sort_order, disable_flag, created_by, creation_date)
				VALUES ('" . $rootFolderId . "','" . $rn['node_name'] . "','','folder','" . intval($rn['sort_order']) . "','N','" . $user . "','" . $time . "')", $db);
			$newId = isset($_SESSION['LastInsertId']) ? intval($_SESSION['LastInsertId']) : 0;
			$map[intval($rn['node_id'])] = $newId;
			DocTplCreateChildren($db, $nodes, intval($rn['node_id']), $newId, $map, $user);
			$created++;
		}
		/* 可选：把源目录权限规则复制到新根目录 */
		if ($copyPerm && $srcId > 0) {
			DB_query("INSERT INTO doc_permission (perm_type, target_id, grp_type, grp_value, can_read, can_write, can_download, can_delete, can_print, created_by, creation_date)
				SELECT perm_type, " . $rootFolderId . ", grp_type, grp_value, can_read, can_write, can_download, can_delete, can_print, '" . $user . "', " . time() . "
				FROM doc_permission WHERE perm_type='folder' AND target_id=" . $srcId, $db);
		}
		$_SESSION['TplMsg'][] = array('type' => 'success', 'msg' => '已用模板「' . $tpl['tpl_name'] . '」创建目录「' . $newName . '」（含 ' . $created . ' 个一级子目录）！');
	}
	header('Location: ' . $RootPath . '/DocFolderTpl.php');
	exit;
}

/* ================= 删除模板 ================= */
if (isset($_POST['tpl_delete'])) {
	$tplId = intval($_POST['tpl_id']);
	DB_query("DELETE FROM doc_folder_tpl_node WHERE tpl_id=" . $tplId, $db);
	DB_query("DELETE FROM doc_folder_tpl WHERE tpl_id=" . $tplId, $db);
	$_SESSION['TplMsg'][] = array('type' => 'success', 'msg' => '模板已删除！');
	header('Location: ' . $RootPath . '/DocFolderTpl.php');
	exit;
}

$Title = _('目录模板管理');
$ViewTopic = '目录模板管理';
$BookMark = '目录模板管理';
include('includes/header.inc');
include('includes/doc_nav.inc');
include('includes/SQL_CommonFunctions.inc');

/* ---------- 会话消息 ---------- */
if (isset($_SESSION['TplMsg']) && is_array($_SESSION['TplMsg'])) {
	foreach ($_SESSION['TplMsg'] as $m) { prnMsg($m['msg'], $m['type']); }
	unset($_SESSION['TplMsg']);
}

/* ================= 数据：目录下拉 ================= */
$folderOptions = array();
$r = DB_query("SELECT folder_id, parent_id, folder_name FROM doc_folder WHERE disable_flag<>'Y' ORDER BY parent_id, folder_id", $db);
$allFolders = array();
while ($row = DB_fetch_array($r)) { $allFolders[] = $row; }
foreach ($allFolders as $f) {
	$folderOptions[] = array('id' => intval($f['folder_id']), 'label' => DocTplFolderPath($db, $f['folder_id']));
}

/* ================= 数据：模板列表 ================= */
$tpls = array();
$r = DB_query("SELECT t.*, (SELECT COUNT(*) FROM doc_folder_tpl_node n WHERE n.tpl_id=t.tpl_id) AS node_cnt2
	FROM doc_folder_tpl t ORDER BY t.creation_date DESC", $db);
while ($row = DB_fetch_array($r)) {
	$row['node_cnt'] = intval($row['node_cnt']) > 0 ? intval($row['node_cnt']) : intval($row['node_cnt2']);
	$row['src_path'] = $row['src_folder_id'] > 0 ? DocTplFolderPath($db, $row['src_folder_id']) : '—';
	$tpls[] = $row;
}
?>
<style type="text/css">
.tpl-wrap{width:100%}
.tpl-card{background:#f9fbfd;border:1px solid #d6e4f0;border-radius:4px;padding:12px 14px;margin-bottom:14px}
.tpl-card h3{margin:0 0 10px 0;font-size:14px;color:#0d47a1;display:flex;align-items:center;gap:6px}
.tpl-form{display:flex;gap:8px;align-items:center;flex-wrap:wrap}
.tpl-form label{font-size:12px;color:#555}
.tpl-form select,.tpl-form input[type=text]{padding:5px 8px;border:1px solid #c8ced6;border-radius:3px;font-size:12px}
.tpl-form select:focus,.tpl-form input[type=text]:focus{outline:none;border-color:#2196F3}
.tpl-form input[type=text]{width:200px}
.tpl-form input[type=text].w-long{width:280px}
.tpl-info{background:#e3f2fd;border:1px solid #bbdefb;border-radius:4px;padding:8px 12px;font-size:12px;color:#0d47a1;margin-bottom:10px}
.tpl-table{width:100%;border-collapse:separate;border-spacing:0;border:1px solid #e0e0e0;border-radius:3px;background:#fff}
.tpl-table th{background:#2196F3;color:#fff;font-weight:bold;padding:7px 10px;text-align:center;white-space:nowrap;border-right:1px solid #1e88e5;font-size:13px}
.tpl-table th:last-child{border-right:none}
.tpl-table td{padding:7px 10px;text-align:center;font-size:12px;white-space:nowrap;border-right:1px solid #e0e0e0;border-bottom:1px solid #e0e0e0}
.tpl-table tr:last-child td{border-bottom:none}
.tpl-table tr:hover td{background:#f1f8ff}
.tpl-table td.left{text-align:left}
.tpl-table a{color:#1976D2;text-decoration:none}
.tpl-table a:hover{text-decoration:underline}
.tpl-table a.del{color:#c62828}
.btn-act{display:inline-block;padding:3px 14px;border-radius:3px;font-size:12px;cursor:pointer;border:1px solid;text-decoration:none}
.btn-un{background:#4a90e2;border-color:#4a90e2;color:#fff}
.btn-un:hover{background:#357abd;color:#fff;text-decoration:none}
.btn-gr{background:#4caf50;border-color:#4caf50;color:#fff}
.btn-gr:hover{background:#43a047;color:#fff;text-decoration:none}
.btn-no{background:#fff;border-color:#ef9a9a;color:#c62828}
.btn-no:hover{background:#c62828;color:#fff;text-decoration:none}
.tpl-tag{padding:2px 10px;border-radius:10px;font-size:11px;color:#fff;background:#1976D2}
/* 应用模板弹窗 */
.tpl-modal{position:fixed;z-index:9000;left:0;top:0;width:100%;height:100%;background-color:rgba(0,0,0,0.5);display:flex;align-items:center;justify-content:center}
.tpl-modal-box{width:460px;background:#fff;border-radius:8px;border:1px solid #d5d5d5;box-shadow:0 4px 16px rgba(0,0,0,0.25);max-height:85vh;display:flex;flex-direction:column}
.tpl-modal-title{background:#f0f0f0;padding:10px 16px;font-weight:bold;font-size:14px;border-bottom:1px solid #d5d5d5;border-radius:8px 8px 0 0;cursor:move;user-select:none}
.tpl-modal-body{padding:16px;font-size:13px;overflow:auto}
.tpl-modal-foot{padding:10px 16px;text-align:right;border-top:1px solid #e5e5e5;background:#fafbfc;border-radius:0 0 8px 8px}
.tpl-form2 td{padding:6px;font-size:13px}
.tpl-form2 td.lb{color:#5a6675;width:96px;text-align:right}
.tpl-form2 select,.tpl-form2 input[type=text]{padding:5px 8px;border:1px solid #c8ced6;border-radius:3px;font-size:12px;width:100%;box-sizing:border-box}
.tpl-form2 select:focus,.tpl-form2 input[type=text]:focus{outline:none;border-color:#2196F3}
.tpl-form2 label{font-size:12px;color:#555;cursor:pointer}
</style>

<div class="tpl-wrap">

	<!-- 保存目录为模板 -->
	<div class="tpl-card">
		<h3>💾 保存目录为模板</h3>
		<div class="tpl-info">把已有目录（含子目录层级 / 名称）保存为模板；之后可随时用模板一键创建同结构的目录。</div>
		<form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" class="tpl-form">
			<input type="hidden" name="FormID" value="<?php echo $_SESSION['FormID']; ?>" />
			<label>源目录：</label>
			<select name="src_folder_id">
				<?php foreach ($folderOptions as $fo) { ?>
					<option value="<?php echo $fo['id']; ?>"><?php echo htmlspecialchars($fo['label']); ?></option>
				<?php } ?>
			</select>
			<label>模板名称：</label>
			<input type="text" name="tpl_name" required placeholder="如：图纸目录模板" />
			<label>说明：</label>
			<input type="text" name="tpl_desc" class="w-long" placeholder="可选" />
			<button type="submit" name="tpl_save" class="btn-act btn-gr" value="1">保存模板</button>
		</form>
	</div>

	<!-- 模板列表 -->
	<div class="tpl-card">
		<h3>🗂 目录模板列表 <span style="font-weight:normal;font-size:12px;color:#888;">（共 <?php echo count($tpls); ?> 个）</span></h3>
		<?php if (count($tpls) == 0) { ?>
			<div style="font-size:12px;color:#aaa;padding:14px 0;">暂无目录模板，请在上方「保存目录为模板」中选择一个已有目录保存。</div>
		<?php } else { ?>
		<table class="tpl-table">
			<tr>
				<th style="text-align:left;">模板名称</th>
				<th>说明</th>
				<th>来源目录</th>
				<th>子目录数</th>
				<th>创建人</th>
				<th>创建时间</th>
				<th width="190">操作</th>
			</tr>
			<?php foreach ($tpls as $t) { ?>
			<tr>
				<td class="left"><b><?php echo htmlspecialchars($t['tpl_name']); ?></b></td>
				<td class="left" style="max-width:200px;overflow:hidden;text-overflow:ellipsis;"><?php echo $t['tpl_desc'] != '' ? htmlspecialchars($t['tpl_desc']) : '—'; ?></td>
				<td class="left" title="<?php echo htmlspecialchars($t['src_path']); ?>" style="max-width:180px;overflow:hidden;text-overflow:ellipsis;"><?php echo htmlspecialchars($t['src_path']); ?></td>
				<td><span class="tpl-tag"><?php echo intval($t['node_cnt']); ?></span></td>
				<td><?php echo htmlspecialchars($t['created_by']); ?></td>
				<td><?php echo date('Y-m-d H:i', $t['creation_date']); ?></td>
				<td>
					<button type="button" class="btn-act btn-un" onclick="TplApply(<?php echo intval($t['tpl_id']); ?>, '<?php echo htmlspecialchars(addslashes($t['tpl_name']), ENT_QUOTES); ?>')">用模板建目录</button>
					<form method="POST" action="<?php echo $RootPath; ?>/DocFolderTpl.php" style="display:inline;margin:0;">
						<input type="hidden" name="FormID" value="<?php echo $_SESSION['FormID']; ?>" />
						<input type="hidden" name="tpl_id" value="<?php echo intval($t['tpl_id']); ?>" />
						<button type="submit" name="tpl_delete" class="btn-act btn-no" value="1" onclick="return confirm('确定删除该目录模板？不影响已创建的目录。');">删除</button>
					</form>
				</td>
			</tr>
			<?php } ?>
		</table>
		<?php } ?>
	</div>

</div>

<!-- 用模板建目录弹窗 -->
<div id="tplModal" class="tpl-modal" style="display:none;">
	<div class="tpl-modal-box">
		<div class="tpl-modal-title">用模板创建目录</div>
		<form method="POST" action="<?php echo $RootPath; ?>/DocFolderTpl.php">
			<input type="hidden" name="FormID" value="<?php echo $_SESSION['FormID']; ?>" />
			<input type="hidden" name="tpl_id" id="tplApplyId" value="0" />
			<input type="hidden" name="tpl_apply" value="1" />
			<div class="tpl-modal-body">
				<table class="tpl-form2" style="width:100%">
					<tr><td class="lb">模板：</td><td><input type="text" id="tplApplyName" readonly style="background:#f5f5f5;" /></td></tr>
					<tr><td class="lb">父目录：</td><td>
						<select name="parent_id">
							<?php foreach ($folderOptions as $fo) { ?>
								<option value="<?php echo $fo['id']; ?>"><?php echo htmlspecialchars($fo['label']); ?></option>
							<?php } ?>
							<option value="0">主目录（顶级）</option>
						</select>
					</td></tr>
					<tr><td class="lb">新目录名称：</td><td><input type="text" name="new_name" id="tplNewName" required placeholder="输入新目录名称" /></td></tr>
					<tr><td class="lb">权限：</td><td><label><input type="checkbox" name="copy_perm" value="1" /> 把来源目录的权限规则套用到新目录根</label></td></tr>
				</table>
			</div>
			<div class="tpl-modal-foot">
				<input type="button" value="取消" style="padding:5px 16px;margin-right:10px;cursor:pointer;border:1px solid #aaa;background:#fff;border-radius:4px;" onclick="document.getElementById('tplModal').style.display='none';" />
				<input type="submit" value="创建目录" style="background:#4a90e2;color:#fff;border:none;padding:5px 18px;border-radius:4px;cursor:pointer;" />
			</div>
		</form>
	</div>
</div>

<script type="text/javascript">
function TplApply(id, name) {
	document.getElementById('tplApplyId').value = id;
	document.getElementById('tplApplyName').value = name;
	document.getElementById('tplNewName').value = name;
	document.getElementById('tplModal').style.display = 'flex';
}
/* 弹窗拖动 */
(function() {
	var box = document.querySelector('#tplModal .tpl-modal-box');
	if (!box) return;
	var title = box.querySelector('.tpl-modal-title');
	var startX = 0, startY = 0, origLeft = 0, origTop = 0, dragging = false;
	title.addEventListener('mousedown', function(e) {
		dragging = true;
		startX = e.clientX; startY = e.clientY;
		var rect = box.getBoundingClientRect();
		origLeft = rect.left; origTop = rect.top;
		box.style.position = 'fixed';
		box.style.margin = '0';
		box.style.left = origLeft + 'px';
		box.style.top = origTop + 'px';
		e.preventDefault();
	});
	document.addEventListener('mousemove', function(e) {
		if (!dragging) return;
		box.style.left = (origLeft + e.clientX - startX) + 'px';
		box.style.top = (origTop + e.clientY - startY) + 'px';
	});
	document.addEventListener('mouseup', function() { dragging = false; });
})();
</script>

<?php include('includes/footer.inc'); ?>
