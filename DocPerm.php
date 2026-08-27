<?php
/* =====================================================================
 * 文档权限管理（DocPLM 文档管理体系 · 二期）
 * ---------------------------------------------------------------------
 * 按【部门】授权：文件夹/文档 × 部门 矩阵，权限：读/写/下载/删除/打印。
 * 规则继承：目录未配置取最近有规则的祖先；文档未配置取所属目录；全无规则默认放行。
 * 样式：浅蓝 Material（#2196F3 主色），与 DocPLM 文档工作区一致。
 * 表：doc_permission
 * ===================================================================== */
include('includes/session.inc');
require_once 'includes/doc_perm.inc';
DocPermBootstrap($db);

$CurFolder = isset($_GET['folder']) ? intval($_GET['folder']) : 1;
if ($CurFolder <= 0) { $CurFolder = 1; }
$CurDoc = isset($_GET['doc']) ? intval($_GET['doc']) : 0;
$time = time();
$user = $_SESSION['UserID'];

/* ================= 保存：目录权限矩阵 =================
 * 注：POST 处理必须在 header 输出之前执行，否则 header('Location') 重定向会因「headers already sent」失效。
 * ===================================================== */
if (isset($_POST['save_folder_perm'])) {
	$fid = intval($_POST['folder_id']);
	if ($fid > 0) {
		$cnt = 0;
		foreach ($_POST['fp'] as $grpKey => $perms) {
			$clean = array();
			foreach (array('read', 'write', 'download', 'delete', 'print') as $p) {
				if (isset($perms[$p]) && $perms[$p] == 'Y') { $clean[$p] = 'Y'; }
			}
			if (strpos($grpKey, 'dept:') === 0) {
				$grpType = 'dept';
				$grpValue = substr($grpKey, 5);
			} else {
				$grpType = 'all';
				$grpValue = '*';
			}
			if (count($clean) > 0) { DocPermUpsert($db, 'folder', $fid, $grpType, $grpValue, $clean); $cnt++; }
			else { DocPermDelete($db, 'folder', $fid, $grpType, $grpValue); }
		}
		$_SESSION['PermMsg'][] = array('type' => 'success', 'msg' => '目录权限已保存（更新 ' . $cnt . ' 个部门规则）！');
	}
	header('Location: ' . $RootPath . '/DocPerm.php?folder=' . $fid . ($CurDoc > 0 ? '&doc=' . $CurDoc : ''));
	exit;
}
if (isset($_POST['clear_folder_perm'])) {
	$fid = intval($_POST['folder_id']);
	DB_query("DELETE FROM doc_permission WHERE perm_type='folder' AND target_id=" . $fid, $db);
	$_SESSION['PermMsg'][] = array('type' => 'success', 'msg' => '该目录全部权限规则已清除（恢复默认放行）！');
	header('Location: ' . $RootPath . '/DocPerm.php?folder=' . $fid . ($CurDoc > 0 ? '&doc=' . $CurDoc : ''));
	exit;
}

/* ================= 保存：文档权限矩阵 ================= */
if (isset($_POST['save_doc_perm'])) {
	$did = intval($_POST['doc_id']);
	if ($did > 0) {
		$cnt = 0;
		foreach ($_POST['dp'] as $grpKey => $perms) {
			$clean = array();
			foreach (array('read', 'write', 'download', 'delete', 'print') as $p) {
				if (isset($perms[$p]) && $perms[$p] == 'Y') { $clean[$p] = 'Y'; }
			}
			if (strpos($grpKey, 'dept:') === 0) { $grpType = 'dept'; $grpValue = substr($grpKey, 5); }
			else { $grpType = 'all'; $grpValue = '*'; }
			if (count($clean) > 0) { DocPermUpsert($db, 'doc', $did, $grpType, $grpValue, $clean); $cnt++; }
			else { DocPermDelete($db, 'doc', $did, $grpType, $grpValue); }
		}
		DocLogAdd($db, $did, '权限配置', '');
		$_SESSION['PermMsg'][] = array('type' => 'success', 'msg' => '文档权限已保存（更新 ' . $cnt . ' 个部门规则）！');
	}
	header('Location: ' . $RootPath . '/DocPerm.php?folder=' . $CurFolder . ($did > 0 ? '&doc=' . $did : ''));
	exit;
}
if (isset($_POST['clear_doc_perm'])) {
	$did = intval($_POST['doc_id']);
	DB_query("DELETE FROM doc_permission WHERE perm_type='doc' AND target_id=" . $did, $db);
	$_SESSION['PermMsg'][] = array('type' => 'success', 'msg' => '该文档全部权限规则已清除（继承目录权限）！');
	header('Location: ' . $RootPath . '/DocPerm.php?folder=' . $CurFolder . ($did > 0 ? '&doc=' . $did : ''));
	exit;
}

$Title = _('文档权限管理');
$ViewTopic = '文档权限管理';
$BookMark = '文档权限管理';
include('includes/header.inc');
include('includes/doc_nav.inc');
include('includes/SQL_CommonFunctions.inc');

/* ---------- 会话消息 ---------- */
if (isset($_SESSION['PermMsg']) && is_array($_SESSION['PermMsg'])) {
	foreach ($_SESSION['PermMsg'] as $m) { prnMsg($m['msg'], $m['type']); }
	unset($_SESSION['PermMsg']);
}

/* ================= 数据 ================= */
$folders = array();
$r = DB_query("SELECT folder_id, parent_id, folder_name FROM doc_folder ORDER BY sort_order, folder_id", $db);
while ($row = DB_fetch_array($r)) { $folders[] = $row; }

$CurFolderName = '主目录';
foreach ($folders as $f) { if ($f['folder_id'] == $CurFolder) { $CurFolderName = $f['folder_name']; break; } }

/* 当前目录下的文档（目录树中展示，点击即可配置文档级权限） */
$curFolderDocs = array();
$r = DB_query("SELECT m.doc_id, m.doc_code, m.doc_name, m.status, f.file_ext
	FROM doc_master m
	LEFT JOIN doc_file f ON f.doc_id=m.doc_id AND f.is_current='Y' AND f.file_id=(SELECT MAX(file_id) FROM doc_file df WHERE df.doc_id=m.doc_id AND df.is_current='Y')
	WHERE m.folder_id=" . $CurFolder . " AND m.status<>'已删除' ORDER BY m.doc_code", $db);
while ($row = DB_fetch_array($r)) { $curFolderDocs[] = $row; }

$depts = DocDeptList($db);
$deptByName = array();
foreach ($depts as $d) { $deptByName[$d['name']] = $d['code']; }

/* 当前目录/文档的规则映射 */
function PermRuleMap($db, $ptype, $target) {
	$map = array();
	foreach (DocPermRuleRows($db, $ptype, $target) as $row) {
		$key = ($row['grp_type'] == 'all') ? 'all' : 'dept:' . $row['grp_value'];
		$map[$key] = $row;
	}
	return $map;
}
$folderRules = PermRuleMap($db, 'folder', $CurFolder);
$docInfo = null;
$docRules = array();
if ($CurDoc > 0) {
	$r = DB_query("SELECT doc_id, doc_code, doc_name, status, folder_id FROM doc_master WHERE doc_id=" . $CurDoc, $db);
	if ($row = DB_fetch_array($r)) { $docInfo = $row; $docRules = PermRuleMap($db, 'doc', $CurDoc); }
	else { $CurDoc = 0; }
}

/* 目录树渲染（目录 + 当前目录下的文档，形成树状结构） */
function PermRenderFolders($folders, $parentId, $level, $CurFolder, $folderDocs, $CurDoc) {
	$children = array();
	foreach ($folders as $f) { if ($f['parent_id'] == $parentId) { $children[] = $f; } }
	$total = count($children);
	$idx = 0;
	foreach ($children as $f) {
		$idx++;
		$isLast = ($idx == $total);
		$hasChild = false;
		foreach ($folders as $g) { if ($g['parent_id'] == $f['folder_id']) { $hasChild = true; break; } }
		$isActive = ($f['folder_id'] == $CurFolder);
		$dcount = $isActive ? count($folderDocs) : 0;
		$label = $f['folder_name'] . ($dcount > 0 ? ' (' . $dcount . ')' : '');
		echo '<li class="bom-node' . ($hasChild ? '' : ' leaf-node') . ($isActive ? ' active' : '') . '" data-folder="' . $f['folder_id'] . '">';
		echo '<div class="bom-row" onclick="window.location.href=\'DocPerm.php?folder=' . $f['folder_id'] . '\'">';
		echo '<span class="bom-glyphs">';
		for ($i = 0; $i < $level - 1; $i++) { echo '<span class="tree-cell indent-cell no-sibling"><span class="tree-vbar"></span></span>'; }
		if ($level > 0) { echo '<span class="tree-cell indent-cell' . ($isLast ? ' no-sibling' : '') . '"><span class="tree-vbar"></span></span>'; }
		echo '<span class="tree-cell node-cell">';
		if ($level > 0) { echo '<span class="tree-hbar"></span>'; }
		if ($hasChild || $isActive) { echo '<span class="tw">' . ($isActive ? '−' : '+') . '</span>'; }
		echo '</span></span>';
		echo '<span class="icon-label">' . PlmIconFolder() . '<span class="lbl">' . htmlspecialchars($label) . '</span></span></div>';
		if ($hasChild || ($isActive && $dcount > 0)) {
			echo '<ul class="bom-sub" style="' . ($isActive ? 'display:block' : 'display:none') . '">';
			if ($hasChild) { PermRenderFolders($folders, $f['folder_id'], $level + 1, $CurFolder, $folderDocs, $CurDoc); }
			if ($isActive) {
				$dn = count($folderDocs);
				$di = 0;
				foreach ($folderDocs as $d) {
					$di++;
					$dIsLast = ($di == $dn);
					$isDocActive = ($d['doc_id'] == $CurDoc);
					$dlabel = ($d['doc_code'] != '') ? $d['doc_code'] : mb_substr($d['doc_name'], 0, 20);
					echo '<li class="bom-node leaf-node doc-node' . ($isDocActive ? ' active' : '') . '" data-doc="' . $d['doc_id'] . '">';
					echo '<div class="bom-row" title="' . htmlspecialchars($d['doc_name']) . '" onclick="window.location.href=\'DocPerm.php?folder=' . $CurFolder . '&doc=' . $d['doc_id'] . '\'">';
					echo '<span class="bom-glyphs">';
					for ($i = 0; $i < $level; $i++) { echo '<span class="tree-cell indent-cell no-sibling"><span class="tree-vbar"></span></span>'; }
					echo '<span class="tree-cell indent-cell' . ($dIsLast ? ' no-sibling' : '') . '"><span class="tree-vbar"></span></span>';
					echo '<span class="tree-cell node-cell"><span class="tree-hbar"></span></span>';
					echo '</span>';
					echo '<span class="icon-label">' . PlmIconFile($d['file_ext']) . '<span class="lbl">' . htmlspecialchars($dlabel) . '</span></span></div></li>';
				}
			}
			echo '</ul>';
		}
		echo '</li>';
	}
}
?>
<style type="text/css">
/* ====== 布局（复用 DocPLM .plm-layout 规范） ====== */
.perm-layout{display:flex;width:100%;min-height:520px;gap:8px;margin-top:6px}
.perm-left{width:300px;border:1px solid #c5c5c5;background:#f7f9fc;border-radius:4px;padding:12px;display:flex;flex-direction:column}
.perm-right{flex:1;border:1px solid #c5c5c5;border-radius:4px;padding:14px;background:#fff;min-width:0}
.perm-left-head{display:flex;justify-content:space-between;align-items:center;padding-bottom:10px;margin-bottom:10px;border-bottom:1px solid #d9d9d9;font-size:14px;font-weight:bold;color:#333}
.bom-tree,.bom-tree ul{list-style:none;margin:0;padding:0}
.bom-tree{font-size:12px;line-height:22px;color:#333;user-select:none}
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
.leaf-node .node-cell .tw{display:none}
.bom-node.doc-node>.bom-row .lbl{color:#1565c0;font-weight:500}
.icon-label{display:flex;align-items:center;gap:0;margin-left:2px;flex:0 0 auto}
.bom-row .lbl{flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;min-width:0;padding:0 0 0 2px;line-height:22px}
/* 右侧面板 */
.perm-card{background:#f9fbfd;border:1px solid #d6e4f0;border-radius:4px;padding:12px 14px;margin-bottom:14px}
.perm-card h3{margin:0 0 10px 0;font-size:14px;color:#0d47a1;display:flex;align-items:center;gap:6px}
.perm-tip{font-size:12px;color:#888;background:#fffbe6;border:1px solid #ffe58f;border-radius:4px;padding:8px 12px;margin-bottom:12px;line-height:1.6}
.perm-table{width:100%;border-collapse:separate;border-spacing:0;border:1px solid #e0e0e0;border-radius:3px}
.perm-table th{background:#2196F3;color:#fff;font-weight:bold;padding:7px 10px;text-align:center;white-space:nowrap;border-right:1px solid #1e88e5;font-size:13px}
.perm-table th:last-child{border-right:none}
.perm-table td{padding:7px 10px;text-align:center;font-size:12px;white-space:nowrap;border-right:1px solid #e0e0e0;border-bottom:1px solid #e0e0e0}
.perm-table tr:last-child td{border-bottom:none}
.perm-table td.left{text-align:left}
.perm-table tr:hover td{background:#f1f8ff}
.perm-table tr.perm-all-row td{background:#fafbfc}
.perm-save{padding:8px 24px;background:#4caf50;color:#fff;border:1px solid #4caf50;border-radius:3px;cursor:pointer;font-size:13px;font-weight:500;margin-top:12px}
.perm-save:hover{background:#43a047}
.perm-clear{background:#fff;color:#c62828;border:1px solid #ef9a9a;padding:8px 18px;border-radius:3px;cursor:pointer;font-size:13px;margin-top:12px;margin-left:10px}
.perm-clear:hover{background:#c62828;color:#fff}
.perm-docinfo{background:#e3f2fd;border:1px solid #bbdefb;border-radius:4px;padding:8px 12px;font-size:12px;color:#0d47a1;margin-bottom:10px}
</style>

<div class="perm-layout">

	<!-- ===== 左：目录树 ===== -->
	<div class="perm-left">
		<div class="perm-left-head">
			<span>🔒 文档目录</span>
		</div>
		<div style="flex:1;overflow:auto;">
			<ul class="bom-tree" id="permTree">
			<?php PermRenderFolders($folders, 0, 0, $CurFolder, $curFolderDocs, $CurDoc); ?>
			</ul>
		</div>
		<div style="font-size:11px;color:#888;padding-top:6px;border-top:1px dashed #d9d9d9;">
			点击目录配置其权限；文档可单独覆盖
		</div>
	</div>

	<!-- ===== 右：权限矩阵 ===== -->
	<div class="perm-right">

		<div class="perm-tip">
			<strong>授权说明</strong>：权限按部门授权（登录账号所属部门）。<b>读</b>=查看/预览，<b>写</b>=导入/升版/改属性/归档等，<b>下载</b>=下载文件，<b>删除</b>=删除文档，<b>打印</b>=打印。
			规则继承：目录未配置取最近有规则的上级目录；文档未配置取所属目录；<span style="color:#c62828;">整条链路都未配置任何规则时默认全部放行</span>（与一期行为一致）。
		</div>

		<!-- ===== 目录权限 ===== -->
		<div class="perm-card">
			<h3>📁 目录权限配置：<?php echo htmlspecialchars($CurFolderName); ?></h3>
			<?php if (DocFolderRulesExist($db, $CurFolder)) { ?>
				<div class="perm-docinfo">该目录已配置权限规则（未授权部门将被限制）。</div>
			<?php } else { ?>
				<div class="perm-docinfo" style="background:#fff8e1;border-color:#ffe0b2;color:#e65100;">该目录及上级目录尚未配置权限规则 → 当前为<b>默认放行</b>状态，保存后即生效。</div>
			<?php } ?>
			<form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>">
				<input type="hidden" name="FormID" value="<?php echo $_SESSION['FormID']; ?>" />
				<input type="hidden" name="folder_id" value="<?php echo $CurFolder; ?>" />
				<table class="perm-table">
					<tr>
						<th style="text-align:left;">部门</th>
						<th>读</th><th>写</th><th>下载</th><th>删除</th><th>打印</th>
					</tr>
					<?php
					$permLabels = array('read' => '读', 'write' => '写', 'download' => '下载', 'delete' => '删除', 'print' => '打印');
					$hasAnyFolderRule = false;
					foreach ($depts as $d) {
						$key = 'dept:' . $d['name'];
						$row = isset($folderRules[$key]) ? $folderRules[$key] : null;
						$rowHas = false;
						foreach (array_keys($permLabels) as $p) { if ($row && $row['can_' . $p] == 'Y') { $rowHas = true; $hasAnyFolderRule = true; break; } }
						echo '<tr>';
						echo '<td class="left"><span style="color:#333;font-weight:500;">' . htmlspecialchars($d['name']) . '</span>' . ($rowHas ? ' <span style="color:#2e7d32;font-size:11px;">✔已授权</span>' : '') . '</td>';
						foreach (array_keys($permLabels) as $p) {
							$checked = ($row && $row['can_' . $p] == 'Y') ? 'checked' : '';
							echo '<td><input type="checkbox" name="fp[' . $key . '][' . $p . ']" value="Y" ' . $checked . ' /></td>';
						}
						echo '</tr>';
					}
					/* 所有部门行 */
					$allRow = isset($folderRules['all']) ? $folderRules['all'] : null;
					echo '<tr class="perm-all-row"><td class="left"><span style="color:#333;font-weight:500;">* 所有部门（全局默认）</span></td>';
					foreach (array_keys($permLabels) as $p) {
						$checked = ($allRow && $allRow['can_' . $p] == 'Y') ? 'checked' : '';
						echo '<td><input type="checkbox" name="fp[all][' . $p . ']" value="Y" ' . $checked . ' /></td>';
					}
					echo '</tr>';
					?>
				</table>
				<input type="submit" name="save_folder_perm" class="perm-save" value="保存目录权限" />
				<input type="submit" name="clear_folder_perm" class="perm-clear" value="清除该目录全部规则" onclick="return confirm('确定清除该目录的全部权限规则？清除后默认放行。');" />
			</form>
		</div>

		<!-- ===== 文档级权限 ===== -->
		<div class="perm-card">
			<h3>📄 文档级权限覆盖（可选）</h3>
			<div class="perm-tip">在左侧目录树中点击文档，即可为其单独配置权限；不配置则继承所属目录权限。</div>
			<?php if ($CurDoc == 0) { ?>
				<div style="font-size:12px;color:#aaa;padding:6px 0;">尚未选择文档 —— 展开左侧目录树并点击某个文档即可开始配置。</div>
			<?php } ?>

			<?php if ($CurDoc > 0 && $docInfo) { ?>
				<form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>">
					<input type="hidden" name="FormID" value="<?php echo $_SESSION['FormID']; ?>" />
					<input type="hidden" name="doc_id" value="<?php echo $docInfo['doc_id']; ?>" />
					<div class="perm-docinfo">
						<b><?php echo htmlspecialchars($docInfo['doc_code']); ?></b> / <?php echo htmlspecialchars($docInfo['doc_name']); ?>
						&nbsp;状态：<span style="color:<?php echo ($docInfo['status'] == '正常') ? '#2e7d32' : '#c62828'; ?>;"><?php echo $docInfo['status']; ?></span>
						&nbsp;（未勾选任何部门则继承目录权限）
					</div>
					<table class="perm-table">
						<tr><th style="text-align:left;">部门</th><th>读</th><th>写</th><th>下载</th><th>删除</th><th>打印</th></tr>
						<?php
						$hasAnyDocRule = false;
						foreach ($depts as $d) {
							$key = 'dept:' . $d['name'];
							$row = isset($docRules[$key]) ? $docRules[$key] : null;
							$rowHas = false;
							foreach (array_keys($permLabels) as $p) { if ($row && $row['can_' . $p] == 'Y') { $rowHas = true; $hasAnyDocRule = true; break; } }
							echo '<tr><td class="left"><span style="font-weight:500;">' . htmlspecialchars($d['name']) . '</span>' . ($rowHas ? ' <span style="color:#2e7d32;font-size:11px;">✔</span>' : '') . '</td>';
							foreach (array_keys($permLabels) as $p) {
								$checked = ($row && $row['can_' . $p] == 'Y') ? 'checked' : '';
								echo '<td><input type="checkbox" name="dp[' . $key . '][' . $p . ']" value="Y" ' . $checked . ' /></td>';
							}
							echo '</tr>';
						}
						$allRow = isset($docRules['all']) ? $docRules['all'] : null;
						echo '<tr class="perm-all-row"><td class="left"><b>* 所有部门</b></td>';
						foreach (array_keys($permLabels) as $p) {
							$checked = ($allRow && $allRow['can_' . $p] == 'Y') ? 'checked' : '';
							echo '<td><input type="checkbox" name="dp[all][' . $p . ']" value="Y" ' . $checked . ' /></td>';
						}
						echo '</tr>';
						?>
					</table>
					<input type="submit" name="save_doc_perm" class="perm-save" value="保存文档权限" />
					<input type="submit" name="clear_doc_perm" class="perm-clear" value="清除该文档全部规则" onclick="return confirm('确定清除该文档的全部权限规则？清除后继承目录权限。');" />
				</form>
			<?php } ?>
		</div>

	</div>
</div>

<script type="text/javascript">
/* 目录树：自动展开当前目录的祖先 + +/- 展开收起 */
(function() {
	var tree = document.getElementById('permTree');
	if (!tree) { return; }
	/* 展开活动节点的祖先，确保当前目录/文档可见 */
	var active = tree.querySelector('li.bom-node.active');
	if (active) {
		var el = active.parentElement;
		while (el && el !== tree) {
			if (el.tagName === 'UL' && el.className.indexOf('bom-sub') >= 0) {
				el.style.display = 'block';
				var tw = el.parentElement ? el.parentElement.querySelector(':scope > .bom-row .tw') : null;
				if (tw) { tw.textContent = '\u2212'; }
			}
			el = el.parentElement;
		}
	}
	/* + / − 展开收起子级 */
	tree.querySelectorAll('.tw').forEach(function(tw) {
		tw.addEventListener('click', function(e) {
			e.stopPropagation();
			var li = tw.closest('li.bom-node');
			var sub = li ? li.querySelector(':scope > ul.bom-sub') : null;
			if (!sub) { return; }
			var open = (sub.style.display !== 'none');
			sub.style.display = open ? 'none' : 'block';
			tw.textContent = open ? '+' : '\u2212';
		});
	});
})();
</script>

<?php include('includes/footer.inc'); ?>
