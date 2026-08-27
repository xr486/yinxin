<?php
/* PLM 文档管理系统 - 工作区（一期）
 * 目录树 + 导入/列表/下载/删除 + 版本(升版/历史) + 状态(归档/废止) + 日志 + 属性 + 搜索
 * 表：doc_folder / doc_master / doc_file / doc_log（新表）
 * 上传复用 upload2.class.php（SO/ 目录）
 * 注意：session.inc 已对 $_POST/$_GET 统一 DB_escape_string，本页不再二次转义
 */
include('includes/session.inc');
$Title = _('文档工作区');
$ViewTopic = '文档工作区';
$BookMark = '文档工作区';
/* 模式（mode）决定页面标题与渲染分支：workspace / template / abolition / recycle */
$mode = isset($_GET['mode']) ? trim($_GET['mode']) : '';
if ($mode == 'template') {
	$Title = _('文档模板');
	$ViewTopic = '文档模板';
	$BookMark = '文档模板';
} elseif ($mode == 'abolition') {
	$Title = _('文件废止区');
	$ViewTopic = '文件废止区';
	$BookMark = '文件废止区';
} elseif ($mode == 'recycle') {
	$Title = _('文件回收站');
	$ViewTopic = '文件回收站';
	$BookMark = '文件回收站';
}

/* ================= AJAX 端点（弹窗内容，纯片段） ================= */
if (isset($_GET['op'])) {
	header('Content-Type: text/html; charset=utf-8');
	while (ob_get_level()) { ob_end_clean(); }
	$docId = intval($_GET['doc_id']);
	$op = $_GET['op'];
	if ($op == 'props') {
		$r = DB_query("SELECT m.*, f.file_ext, f.file_size, f.uploader, f.upload_date FROM doc_master m LEFT JOIN doc_file f ON f.doc_id=m.doc_id AND f.is_current='Y' WHERE m.doc_id=" . $docId, $db);
		if ($row = DB_fetch_array($r)) {
			echo '<table class="plm-form">';
			echo '<tr><td class="lb">文档名称：</td><td><input type="text" name="doc_name" value="' . htmlspecialchars($row['doc_name']) . '" /></td></tr>';
			echo '<tr><td class="lb">文档编码：</td><td><input type="text" name="doc_code" value="' . htmlspecialchars($row['doc_code']) . '" /></td></tr>';
			echo '<tr><td class="lb">文档类型：</td><td><input type="text" name="doc_type" value="' . htmlspecialchars($row['doc_type']) . '" placeholder="图纸/表单/报告…" /></td></tr>';
			echo '<tr><td class="lb">关联物料：</td><td><div style="display:flex;gap:4px;"><input type="text" id="doc_item" name="doc_item" value="' . htmlspecialchars($row['item_no']) . '" placeholder="物料编码(可空)" style="flex:1;" /><button type="button" onclick="PlmPickItem(\'doc_item\')" style="padding:2px 10px;background:#1976D2;color:#fff;border:none;border-radius:3px;cursor:pointer;font-size:12px;">选...</button></div></td></tr>';
			echo '<tr><td class="lb">备注：</td><td><textarea name="doc_remark">' . htmlspecialchars($row['remark']) . '</textarea></td></tr>';
			echo '<tr><td class="lb">状态：</td><td><b>' . $row['status'] . '</b> / 版本 <b>' . $row['current_version'] . '</b></td></tr>';
			echo '<tr><td class="lb">创建：</td><td>' . $row['created_by'] . ' @ ' . date('Y-m-d H:i:s', $row['creation_date']) . '</td></tr>';
			echo '</table>';
		} else { echo '文档不存在'; }
		exit;
	}
	if ($op == 'versions') {
		/* 关联 doc_master.status 显示整个文档状态 */
		$r = DB_query("SELECT v.*, m.doc_name, m.status AS m_status FROM doc_file v JOIN doc_master m ON m.doc_id=v.doc_id WHERE v.doc_id=" . $docId . " ORDER BY v.version_no", $db);
		echo '<table class="plm-table" style="min-width:0">';
		echo '<tr><th>版本</th><th>文件名</th><th>后缀</th><th>大小</th><th width="80">状态</th><th width="60">当前</th><th>上传人</th><th>上传时间</th><th>下载</th></tr>';
		while ($row = DB_fetch_array($r)) {
			$stClass = $row['m_status']==='正常' ? 'plm-tag st-ok' : ($row['m_status']==='已归档' ? 'plm-tag st-arch' : ($row['m_status']==='已废止' ? 'plm-tag st-abd' : 'plm-tag st-del'));
			echo '<tr>';
			echo '<td><b>' . $row['version_no'] . '</b></td>';
			echo '<td class="left">' . htmlspecialchars($row['file_name']) . '</td>';
			echo '<td>' . strtoupper($row['file_ext']) . '</td>';
			echo '<td>' . PlmSize($row['file_patch']) . '</td>';
			echo '<td><span class="' . $stClass . '">' . htmlspecialchars($row['m_status']) . '</span></td>';
			echo '<td>' . ($row['is_current'] == 'Y' ? '<span class="plm-tag st-ok">当前</span>' : '') . '</td>';
			echo '<td>' . $row['uploader'] . '</td>';
			echo '<td>' . date('Y-m-d H:i:s', $row['upload_date']) . '</td>';
			echo '<td>' . ($row['is_current'] == 'Y' && $row['file_patch'] && file_exists($row['file_patch']) ? '<a href="' . $RootPath . '/' . $row['file_patch'] . '" download="' . htmlspecialchars($row['file_name'] . ($row['file_ext'] !== '' ? '.' . $row['file_ext'] : '')) . '">下载</a>' : '<span style="color:#bbb;font-size:11px;">历史版</span>') . '</td>';
			echo '</tr>';
		}
		exit;
	}
	if ($op == 'log') {
		$r = DB_query("SELECT * FROM doc_log WHERE doc_id=" . $docId . " ORDER BY action_date DESC", $db);
		echo '<table class="plm-table" style="min-width:0">';
		echo '<tr><th>时间</th><th>操作</th><th>操作人</th><th>备注</th></tr>';
		while ($row = DB_fetch_array($r)) {
			echo '<tr><td>' . date('Y-m-d H:i:s', $row['action_date']) . '</td><td>' . htmlspecialchars($row['action']) . '</td><td>' . $row['operator'] . '</td><td class="left">' . htmlspecialchars($row['remark']) . '</td></tr>';
		}
		exit;
	}
	if ($op == 'item_cat_nodes') {
		/* 物料目录：分类展开 → 加载该分类的物料节点（AJAX 片段） */
		$cat = trim($_GET['cat']);
		if ($cat != '') {
			$r = DB_query("SELECT i.item_no, i.item_name, i.item_type,
			    (SELECT COUNT(*) FROM doc_master d WHERE d.item_no=i.item_no AND d.status<>'已删除') AS doc_cnt
			    FROM sf_item_no i WHERE i.item_category1='" . $cat . "' AND i.able_flag='Y' ORDER BY i.item_no", $db);
			while ($row = DB_fetch_array($r)) {
				echo '<li class="bom-node leaf-node" data-item="' . htmlspecialchars($row['item_no']) . '">';
				echo '<div class="bom-row" onclick="PlmGoItem(\'' . htmlspecialchars($row['item_no'], ENT_QUOTES) . '\')">';
				echo '<span class="bom-glyphs">';
				/* 物料目录虚拟层级：物料是第 2 级（物料目录根→分类→物料），用 2 级缩进占位 */
				echo '<span class="tree-cell indent-cell no-sibling"><span class="tree-vbar"></span></span>';
				echo '<span class="tree-cell indent-cell no-sibling"><span class="tree-vbar"></span></span>';
				echo '<span class="tree-cell node-cell"><span class="tree-hbar"></span></span>';
				echo '</span>';
				echo '<span class="icon-label">' . PlmIconItem() . '<span class="lbl">' . htmlspecialchars($row['item_no']) . ' ' . htmlspecialchars(mb_substr($row['item_name'], 0, 8)) . ($row['doc_cnt'] > 0 ? ' <span style="color:#1976D2;font-size:11px;">(' . $row['doc_cnt'] . ')</span>' : '') . '</span></span></div></li>';
			}
		}
		exit;
	}
	exit;
}

/* ================= 查询当前目录数据（主流程与 AJAX 共用） ================= */
function PlmQuery($db, $CurFolder) {
	$CurFolderName = '主目录';
	$r = DB_query("SELECT folder_name FROM doc_folder WHERE folder_id=" . intval($CurFolder), $db);
	if ($row = DB_fetch_array($r)) { $CurFolderName = $row['folder_name']; }
	$SearchName = isset($_GET['q']) ? trim($_GET['q']) : '';
	$SearchCode = isset($_GET['code']) ? trim($_GET['code']) : '';
	$SearchExt = isset($_GET['ext']) ? trim($_GET['ext']) : '';
	$HideArch = isset($_GET['hidearch']) ? 1 : 0;
	$SearchItem = isset($_GET['item']) ? trim($_GET['item']) : '';
	$docs = array();
	$where = array("m.status<>'已删除'");
	/* 硬隔离：模板模式只显示模板；其他模式（文档工作区/物料目录）一律排除模板 */
	$tplMode = (isset($_GET['tpl']) && $_GET['tpl'] == '1') || (isset($_GET['mode']) && $_GET['mode'] == 'template');
	$where[] = $tplMode ? "m.is_template='Y'" : "m.is_template<>'Y'";
	if (intval($CurFolder) > 0) { $where[] = "m.folder_id=" . intval($CurFolder); } /* folder=-1 = 物料目录（跨目录） */
	else if (intval($CurFolder) > 0) { $where[] = "m.folder_id=" . intval($CurFolder); } /* folder=-1 = 物料目录（跨目录按 item 过滤） */
	if ($SearchName != '') { $where[] = "m.doc_name LIKE '%" . str_replace(array('%', '_'), array('\\%', '\\_'), $SearchName) . "%'"; }
	if ($SearchCode != '') { $where[] = "m.doc_code LIKE '%" . str_replace(array('%', '_'), array('\\%', '\\_'), $SearchCode) . "%'"; }
	if ($SearchExt != '') { $where[] = "f.file_ext='" . str_replace(array('%', '_'), array('\\%', '\\_'), $SearchExt) . "'"; }
	if ($HideArch) { $where[] = "m.status='正常'"; }
	if ($SearchItem != '') { $where[] = "m.item_no='" . $SearchItem . "'"; }
	$sql = "SELECT m.doc_id, m.doc_code, m.doc_name, m.doc_type, m.folder_id, m.item_no, m.status, m.check_status, m.current_version, m.is_template, m.remark, m.created_by, m.creation_date,
	    f.file_patch, f.file_ext, f.file_size, f.file_name AS raw_name,
	    (SELECT folder_name FROM doc_folder df WHERE df.folder_id=m.folder_id) AS folder_name,
	    (SELECT action FROM doc_log WHERE doc_id=m.doc_id ORDER BY action_date DESC LIMIT 1) AS last_action,
	    i.item_name AS item_name, i.item_id AS item_id
	    FROM doc_master m LEFT JOIN doc_file f ON f.doc_id=m.doc_id
	    AND f.file_id = (SELECT MAX(file_id) FROM doc_file df WHERE df.doc_id=m.doc_id AND df.is_current='Y')
	    LEFT JOIN sf_item_no i ON i.item_no = m.item_no
	    WHERE " . implode(' AND ', $where) . " ORDER BY m.creation_date DESC";
	$r = DB_query($sql, $db);
	while ($row = DB_fetch_array($r)) { $docs[] = $row; }
	return array($CurFolderName, $SearchName, $SearchCode, $SearchExt, $HideArch, $SearchItem, $docs);
}

/* 废止区文档查询（status='已废止' 且非模板） */
function PlmGetAbolitionDocs($db, $CurFolder) {
	$awhere = array("m.status='已废止'", "m.is_template<>'Y'");
	if (intval($CurFolder) > 0) { $awhere[] = "m.folder_id=" . intval($CurFolder); }
	if (isset($_GET['q']) && trim($_GET['q']) != '') { $awhere[] = "m.doc_name LIKE '%" . str_replace(array('%', '_'), array('\\%', '\\_'), trim($_GET['q'])) . "%'"; }
	if (isset($_GET['code']) && trim($_GET['code']) != '') { $awhere[] = "m.doc_code LIKE '%" . str_replace(array('%', '_'), array('\\%', '\\_'), trim($_GET['code'])) . "%'"; }
	$docs = array();
	$r = DB_query("SELECT m.doc_id, m.doc_code, m.doc_name, m.doc_type, m.folder_id, m.status, m.current_version, m.remark, m.created_by, m.creation_date, m.abolish_date, m.abolish_remark,
	    f.file_patch, f.file_ext, f.file_size,
	    (SELECT folder_name FROM doc_folder df WHERE df.folder_id=m.folder_id) AS folder_name,
	    (SELECT action FROM doc_log WHERE doc_id=m.doc_id ORDER BY action_date DESC LIMIT 1) AS last_action
	    FROM doc_master m LEFT JOIN doc_file f ON f.doc_id=m.doc_id
	    AND f.file_id = (SELECT MAX(file_id) FROM doc_file df WHERE df.doc_id=m.doc_id AND df.is_current='Y')
	    WHERE " . implode(' AND ', $awhere) . " ORDER BY m.abolish_date DESC, m.creation_date DESC", $db);
	while ($row = DB_fetch_array($r)) { $docs[] = $row; }
	return $docs;
}

/* 回收站文档查询（status='已删除'，按删除时间范围 tr 过滤） */
function PlmGetRecycleDocs($db, $tr) {
	$trCond = '1=1';
	switch ($tr) {
		case 'today':     $trCond = "m.last_update_date >= UNIX_TIMESTAMP(CURDATE())"; break;
		case 'yesterday': $trCond = "m.last_update_date >= UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 1 DAY)) AND m.last_update_date < UNIX_TIMESTAMP(CURDATE())"; break;
		case 'thisweek':  $trCond = "m.last_update_date >= UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY))"; break;
		case 'lastweek':  $trCond = "m.last_update_date >= UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE())+7 DAY)) AND m.last_update_date < UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY))"; break;
		case 'thismonth': $trCond = "m.last_update_date >= UNIX_TIMESTAMP(DATE_FORMAT(CURDATE(), '%Y-%m-01'))"; break;
		case 'lastyear':  $trCond = "m.last_update_date < UNIX_TIMESTAMP(DATE_FORMAT(CURDATE(), '%Y-%m-01'))"; break;
	}
	$rwhere = array("m.status='已删除'", $trCond);
	if (isset($_GET['q']) && trim($_GET['q']) != '') { $rwhere[] = "m.doc_name LIKE '%" . str_replace(array('%', '_'), array('\\%', '\\_'), trim($_GET['q'])) . "%'"; }
	if (isset($_GET['code']) && trim($_GET['code']) != '') { $rwhere[] = "m.doc_code LIKE '%" . str_replace(array('%', '_'), array('\\%', '\\_'), trim($_GET['code'])) . "%'"; }
	$docs = array();
	$r = DB_query("SELECT m.doc_id, m.doc_code, m.doc_name, m.doc_type, m.folder_id, m.status, m.current_version, m.remark, m.created_by, m.creation_date, m.last_update_date,
	    f.file_patch, f.file_ext, f.file_size,
	    (SELECT folder_name FROM doc_folder df WHERE df.folder_id=m.folder_id) AS folder_name,
	    (SELECT action FROM doc_log WHERE doc_id=m.doc_id ORDER BY action_date DESC LIMIT 1) AS last_action
	    FROM doc_master m LEFT JOIN doc_file f ON f.doc_id=m.doc_id
	    AND f.file_id = (SELECT MAX(file_id) FROM doc_file df WHERE df.doc_id=m.doc_id AND df.is_current='Y')
	    WHERE " . implode(' AND ', $rwhere) . " ORDER BY m.last_update_date DESC", $db);
	while ($row = DB_fetch_array($r)) { $docs[] = $row; }
	return $docs;
}

/* 统一"切换当前版本"入口：doc_file.is_current 唯一性 + doc_master.current_version 强制同步
 * 任何版本写操作（升版/回滚/设当前）必须走这里，杜绝两表不一致 */
function PlmSetCurrentFile($db, $docId, $newFileId) {
	$docId = intval($docId);
	$newFileId = intval($newFileId);
	/* 1. 旧当前版本全部置 N（保证唯一 Y） */
	DB_query("UPDATE doc_file SET is_current='N' WHERE doc_id=" . $docId . " AND is_current='Y'", $db);
	/* 2. 新文件置 Y */
	DB_query("UPDATE doc_file SET is_current='Y' WHERE file_id=" . $newFileId . " AND doc_id=" . $docId, $db);
	/* 3. 强制同步 doc_master.current_version */
	$r = DB_query("SELECT version_no FROM doc_file WHERE file_id=" . $newFileId . " AND doc_id=" . $docId, $db);
	if ($row = DB_fetch_array($r)) {
		DB_query("UPDATE doc_master SET current_version='" . $row['version_no'] . "' WHERE doc_id=" . $docId, $db);
	}
	/* 4. 双保险：确保该 doc 只有 1 个 Y（历史脏数据清理） */
	$r = DB_query("SELECT file_id FROM doc_file WHERE doc_id=" . $docId . " AND is_current='Y' ORDER BY file_id DESC", $db);
	$first = true;
	while ($row = DB_fetch_array($r)) {
		if ($first) { $first = false; continue; }
		DB_query("UPDATE doc_file SET is_current='N' WHERE file_id=" . intval($row['file_id']), $db);
	}
}

/* 自检一致性（返回不一致的 doc_id 列表） */
function PlmCheckVersionConsistency($db) {
	$bad = array();
	$r = DB_query("SELECT m.doc_id, m.current_version, f.version_no
	    FROM doc_master m
	    LEFT JOIN doc_file f ON f.doc_id=m.doc_id AND f.is_current='Y' AND f.file_id=(SELECT MAX(file_id) FROM doc_file df WHERE df.doc_id=m.doc_id AND df.is_current='Y')
	    WHERE m.current_version <> COALESCE(f.version_no, '')", $db);
	while ($row = DB_fetch_array($r)) { $bad[] = $row; }
	return $bad;
}

/* ================= 右侧面板渲染（主流程与 AJAX 共用） ================= */
function PlmRenderRight($db, $CurFolder, $CurFolderName, $docs, $SearchName, $SearchCode, $SearchExt, $HideArch, $SearchItem = '', $isTpl = false) {
	global $RootPath;
	?>
	<!-- 搜索 -->
	<form id="plmSearchForm" class="plm-search" onsubmit="return PlmSearchSubmit();">
		<input type="hidden" name="folder" value="<?php echo intval($CurFolder); ?>" />
		<?php if ($isTpl) { ?><input type="hidden" name="tpl" value="1" /><?php } ?>
		<input type="text" id="plmQ" name="q" value="<?php echo htmlspecialchars($SearchName); ?>" placeholder="<?php echo $isTpl ? '模板名称…' : '文件名称…'; ?>" size="18" />
		<input type="text" id="plmCode" name="code" value="<?php echo htmlspecialchars($SearchCode); ?>" placeholder="文件编码…" size="14" />
		<input type="text" id="plmExt" name="ext" value="<?php echo htmlspecialchars($SearchExt); ?>" placeholder="后缀(如 pdf)" size="10" />
		<?php if (!$isTpl) { ?><input type="text" id="plmItem" name="item" value="<?php echo htmlspecialchars($SearchItem); ?>" placeholder="关联物料…" size="12" /><?php } ?>
		<label><input type="checkbox" id="plmHideArch" name="hidearch" <?php echo $HideArch ? 'checked' : ''; ?> /> 不显示已归档</label>
		<button type="submit" style="padding:5px 14px;border:1px solid #4a90e2;background:#4a90e2;color:#fff;border-radius:3px;cursor:pointer;font-size:12px;">查询</button>
		<?php if ($SearchName != '' || $SearchCode != '' || $SearchExt != '' || $HideArch || $SearchItem != '') { ?>
			<a href="javascript:void(0)" onclick="PlmGoFolder(<?php echo intval($CurFolder); ?>, true)" style="font-size:12px;color:#888;text-decoration:none;">清空</a>
		<?php } ?>
	</form>

	<!-- 工具栏 -->
	<div class="plm-toolbar">
		<span class="path-tag"><?php echo $isTpl ? ('📄 文档模板 / ' . htmlspecialchars($CurFolderName)) : ('📁 / ' . htmlspecialchars($CurFolderName)); ?></span>
		<?php if ($isTpl) { ?>
			<button class="btn-import" type="button" onclick="PlmOpenImport()">⇧ 导入模板</button>
		<?php } else { ?>
			<button class="btn-import" type="button" onclick="PlmOpenImport()">⇧ 导入</button>
			<button class="btn-arch" type="button" onclick="PlmAction('archive')">📦 归档</button>
			<button class="btn-arch" type="button" onclick="PlmAction('unarchive')">♻ 反归档</button>
			<button class="btn-arch" type="button" onclick="PlmAction('abandon')">🚫 废止</button>
			<button class="btn-arch" type="button" onclick="PlmAction('recover')">↩ 恢复</button>
			<button class="btn-del" type="button" onclick="PlmAction('delete')">✕ 删除</button>
			<button type="button" onclick="PlmUpgrade()">↻ 升版</button>
		<?php } ?>
		<span class="file-count">共 <b style="color:#1976D2;"><?php echo count($docs); ?></b> 个<?php echo $isTpl ? '模板' : '文档'; ?></span>
	</div>

	<!-- 导入弹窗入口（按钮在工具栏点"⇧ 导入"触发 PlmOpenImport） -->
<div id="plmImportBtnHook" onclick="PlmOpenImport()" style="display:none;"></div>

	<!-- 文件表格 -->
	<?php if (count($docs) == 0) { ?>
		<div class="plm-empty"><?php echo $isTpl ? '暂无文档模板，点击「导入模板」上传文件（勾选"导入为模板"）或到文档工作区把文档设为模板' : '该目录暂无文档，点击「导入」上传文件'; ?></div>
	<?php } else { ?>
	<div class="plm-table-wrap">
	<table class="plm-table">
		<thead>
			<tr>
				<th width="36"><input type="checkbox" onclick="PlmToggleAll(this)" /></th>
				<th width="42">序号</th>
				<th width="40">图标</th>
				<th width="80">状态</th>
				<th width="22%">文件名称</th>
				<th width="52">后缀</th>
				<th width="104">文件编码</th>
				<th width="78">大小</th>
				<th width="82">所在目录</th>
				<th width="130">对应物料</th>
				<th width="72">创建者</th>
				<th width="12%">备注</th>
				<th width="100">最近操作</th>
				<th width="170">操作</th>
			</tr>
		</thead>
		<tbody>
		<?php $i = 1; foreach ($docs as $d) { ?>
			<tr data-docid="<?php echo $d['doc_id']; ?>">
				<td><input type="checkbox" class="plm-row-check" value="<?php echo $d['doc_id']; ?>" /></td>
				<td><?php echo $i; ?></td>
				<td><?php echo PlmIconFile($d['file_ext']); ?></td>
				<td><?php echo PlmStatusTag($d['status']); ?></td>
				<td class="left"><?php echo htmlspecialchars($d['doc_name']); ?> <span style="color:#999;font-size:11px;">[<?php echo $d['current_version']; ?>]</span></td>
				<td><?php echo strtoupper($d['file_ext']); ?></td>
				<td><?php echo htmlspecialchars($d['doc_code']); ?></td>
				<td><?php echo PlmSize($d['file_patch']); ?></td>
				<td><?php echo htmlspecialchars($d['folder_name']); ?></td>
				<td>
					<?php if ($d['item_no'] != '') { ?>
						<a href="<?php echo $RootPath; ?>/Item_No2.php?ItemID=<?php echo intval($d['item_id']); ?>&amp;ItemNo=<?php echo urlencode($d['item_no']); ?>" target="_blank" title="查看物料主档" style="color:#1976D2;text-decoration:none;"><?php echo htmlspecialchars($d['item_no']); ?></a>
						<?php if ($d['item_name'] != '') { ?><span style="color:#999;font-size:11px;"><?php echo htmlspecialchars(mb_substr($d['item_name'], 0, 10)); ?></span><?php } ?>
					<?php } else { ?>
						<span style="color:#ccc;">—</span>
					<?php } ?>
				</td>
				<td><?php echo $d['created_by']; ?></td>
				<td class="left" title="<?php echo htmlspecialchars($d['remark']); ?>" style="max-width:140px;overflow:hidden;text-overflow:ellipsis;"><?php echo $d['remark'] != '' ? htmlspecialchars($d['remark']) : '—'; ?></td>
				<td><?php echo $d['last_action'] != '' ? htmlspecialchars($d['last_action']) : '—'; ?></td>
				<td class="op">
					<?php if ($isTpl) { ?>
						<?php if ($d['file_patch'] && file_exists($d['file_patch'])) {
							$fext = strtolower($d['file_ext']);
							if (in_array($fext, array('jpg', 'jpeg', 'png', 'gif', 'bmp'))) { ?>
								<a href="javascript:void(0)" onclick="PlmPreviewImg('<?php echo htmlspecialchars(addslashes($RootPath . '/' . $d['file_patch']), ENT_QUOTES); ?>')">预览</a>
							<?php } elseif ($fext == 'pdf') { ?>
								<a href="<?php echo $RootPath . '/' . $d['file_patch']; ?>" target="_blank">预览</a>
							<?php } ?>
							<a href="<?php echo $RootPath . '/' . $d['file_patch']; ?>" download="<?php echo htmlspecialchars(PlmDownloadName($d['doc_name'], $fext)); ?>">下载</a>
						<?php } ?>
						<a href="javascript:void(0)" onclick="PlmTemplateNew(<?php echo intval($d['doc_id']); ?>)" style="color:#1976D2;">新建文档</a>
						<a href="javascript:void(0)" onclick="PlmTplDelete(<?php echo intval($d['doc_id']); ?>)" style="color:#c62828;">删除</a>
					<?php } else { ?>
					<a href="javascript:void(0)" onclick="PlmProps(<?php echo $d['doc_id']; ?>)">属性</a>
					<a href="javascript:void(0)" onclick="PlmVersions(<?php echo $d['doc_id']; ?>)">版本</a>
					<a href="javascript:void(0)" onclick="PlmLogView(<?php echo $d['doc_id']; ?>)">日志</a>
					<?php if ($d['is_template'] == 'Y') { ?>
						<a href="javascript:void(0)" onclick="PlmTemplateOff(<?php echo $d['doc_id']; ?>)" style="color:#fb8c00;">模板</a>
					<?php } else { ?>
						<a href="javascript:void(0)" onclick="PlmTemplateSet(<?php echo $d['doc_id']; ?>)" style="color:#fb8c00;">设为模板</a>
					<?php } ?>
					<?php if ($d['file_patch'] && file_exists($d['file_patch'])) {
						$fext = strtolower($d['file_ext']);
						if (in_array($fext, array('jpg', 'jpeg', 'png', 'gif', 'bmp'))) { ?>
							<a href="javascript:void(0)" onclick="PlmPreviewImg('<?php echo htmlspecialchars(addslashes($RootPath . '/' . $d['file_patch']), ENT_QUOTES); ?>')">预览</a>
						<?php } elseif ($fext == 'pdf') { ?>
							<a href="<?php echo $RootPath . '/' . $d['file_patch']; ?>" target="_blank">预览</a>
						<?php } ?>
						<a href="<?php echo $RootPath . '/' . $d['file_patch']; ?>" download="<?php echo htmlspecialchars(PlmDownloadName($d['doc_name'], $fext)); ?>">下载</a>
					<?php } ?>
					<?php } ?>
				</td>
			</tr>
		<?php $i++; } ?>
		</tbody>
	</table>
	</div>
	<?php } ?>
	<?php
}

/* ================= 文件夹路径计算（回收站"位置"列用） ================= */
function PlmFolderPath($folderId) {
	global $folders;
	$map = array();
	foreach ($folders as $f) { $map[$f['folder_id']] = $f; }
	$parts = array();
	$cur = isset($map[$folderId]) ? $map[$folderId] : null;
	$guard = 0;
	while ($cur && $guard < 20) {
		array_unshift($parts, $cur['folder_name']);
		$pid = $cur['parent_id'];
		$cur = isset($map[$pid]) ? $map[$pid] : null;
		$guard++;
	}
	return count($parts) ? implode(' / ', $parts) : '主目录';
}

/* ================= 文件废止区 右侧渲染 ================= */
function PlmRenderAbolition($db, $CurFolder, $CurFolderName, $docs, $SearchName = '', $SearchCode = '') {
	global $RootPath;
	?>
	<!-- 搜索 -->
	<form id="plmSearchForm" class="plm-search" onsubmit="return PlmSearchSubmit();">
		<input type="hidden" name="folder" value="<?php echo intval($CurFolder); ?>" />
		<input type="hidden" name="mode" value="abolition" />
		<input type="text" id="plmQ" name="q" value="<?php echo htmlspecialchars($SearchName); ?>" placeholder="文件名称…" size="18" />
		<input type="text" id="plmCode" name="code" value="<?php echo htmlspecialchars($SearchCode); ?>" placeholder="文件编码…" size="14" />
		<button type="submit" style="padding:5px 14px;border:1px solid #4a90e2;background:#4a90e2;color:#fff;border-radius:3px;cursor:pointer;font-size:12px;">查询</button>
		<?php if ($SearchName != '' || $SearchCode != '') { ?>
			<a href="javascript:void(0)" onclick="PlmGoFolder(<?php echo intval($CurFolder); ?>, true)" style="font-size:12px;color:#888;text-decoration:none;">清空</a>
		<?php } ?>
	</form>

	<!-- 工具栏 -->
	<div class="plm-toolbar">
		<span class="path-tag">🚫 文件废止区 / <?php echo htmlspecialchars($CurFolderName); ?></span>
		<button class="btn-arch" type="button" onclick="PlmToolbarAction('abandon','abolition')">🚫 废止</button>
		<button class="btn-arch" type="button" onclick="PlmToolbarAction('recover','abolition')">↩ 恢复</button>
		<span class="file-count">共 <b style="color:#1976D2;"><?php echo count($docs); ?></b> 个废止文档</span>
	</div>

	<?php if (count($docs) == 0) { ?>
		<div class="plm-empty">该目录暂无废止文档</div>
	<?php } else { ?>
	<div class="plm-table-wrap">
	<table class="plm-table">
		<thead>
			<tr>
				<th width="36"><input type="checkbox" onclick="PlmToggleAll(this)" /></th>
				<th width="42">序号</th>
				<th width="40">图标</th>
				<th width="80">状态</th>
				<th width="22%">文件名称</th>
				<th width="120">废止时间</th>
				<th width="20%">废止备注</th>
				<th width="104">文件编码</th>
				<th width="78">大小</th>
				<th width="82">创建者</th>
				<th width="56">版本</th>
				<th width="120">创建时间</th>
				<th width="150">操作</th>
			</tr>
		</thead>
		<tbody>
		<?php $i = 1; foreach ($docs as $d) { ?>
			<tr data-docid="<?php echo $d['doc_id']; ?>">
				<td><input type="checkbox" class="plm-row-check" value="<?php echo $d['doc_id']; ?>" /></td>
				<td><?php echo $i; ?></td>
				<td><?php echo PlmIconFile($d['file_ext']); ?></td>
				<td><?php echo PlmStatusTag($d['status']); ?></td>
				<td class="left"><?php echo htmlspecialchars($d['doc_name']); ?> <span style="color:#999;font-size:11px;">[<?php echo $d['current_version']; ?>]</span></td>
				<td><?php echo $d['abolish_date'] ? date('Y-m-d H:i:s', $d['abolish_date']) : '—'; ?></td>
				<td class="left" title="<?php echo htmlspecialchars($d['abolish_remark']); ?>" style="max-width:160px;overflow:hidden;text-overflow:ellipsis;"><?php echo $d['abolish_remark'] != '' ? htmlspecialchars($d['abolish_remark']) : '—'; ?></td>
				<td><?php echo htmlspecialchars($d['doc_code']); ?></td>
				<td><?php echo PlmSize($d['file_patch']); ?></td>
				<td><?php echo $d['created_by']; ?></td>
				<td><?php echo $d['current_version']; ?></td>
				<td><?php echo date('Y-m-d H:i:s', $d['creation_date']); ?></td>
				<td class="op">
					<a href="javascript:void(0)" onclick="PlmProps(<?php echo intval($d['doc_id']); ?>)">属性</a>
					<a href="javascript:void(0)" onclick="PlmRecover(<?php echo intval($d['doc_id']); ?>)">恢复</a>
				</td>
			</tr>
		<?php $i++; } ?>
		</tbody>
	</table>
	</div>
	<?php } ?>
	<?php
}

/* ================= 文件回收站 右侧渲染 ================= */
function PlmRenderRecycle($db, $CurFolder, $CurFolderName, $docs, $SearchName = '', $SearchCode = '') {
	global $RootPath;
	?>
	<!-- 搜索 -->
	<form id="plmSearchForm" class="plm-search" onsubmit="return PlmSearchSubmit();">
		<input type="hidden" name="mode" value="recycle" />
		<input type="hidden" name="tr" value="<?php echo htmlspecialchars(isset($_GET['tr']) ? $_GET['tr'] : 'all'); ?>" />
		<input type="text" id="plmQ" name="q" value="<?php echo htmlspecialchars($SearchName); ?>" placeholder="文件名称…" size="18" />
		<input type="text" id="plmCode" name="code" value="<?php echo htmlspecialchars($SearchCode); ?>" placeholder="文件编码…" size="14" />
		<button type="submit" style="padding:5px 14px;border:1px solid #4a90e2;background:#4a90e2;color:#fff;border-radius:3px;cursor:pointer;font-size:12px;">查询</button>
		<?php if ($SearchName != '' || $SearchCode != '') { ?>
			<a href="javascript:void(0)" onclick="PlmGoRecycle('all', true)" style="font-size:12px;color:#888;text-decoration:none;">清空</a>
		<?php } ?>
	</form>

	<!-- 工具栏 -->
	<div class="plm-toolbar">
		<span class="path-tag">🗑 文件回收站 / <?php echo htmlspecialchars($CurFolderName); ?></span>
		<button class="btn-arch" type="button" onclick="PlmToolbarAction('restore','recycle')">↩ 恢复</button>
		<button class="btn-del" type="button" onclick="PlmToolbarAction('purge','recycle')">🗑 彻底删除</button>
		<span class="file-count">共 <b style="color:#1976D2;"><?php echo count($docs); ?></b> 个已删除文档</span>
	</div>

	<?php if (count($docs) == 0) { ?>
		<div class="plm-empty">该时间范围暂无已删除文档</div>
	<?php } else { ?>
	<div class="plm-table-wrap">
	<table class="plm-table">
		<thead>
			<tr>
				<th width="36"><input type="checkbox" onclick="PlmToggleAll(this)" /></th>
				<th width="42">序号</th>
				<th width="22%">名称</th>
				<th width="28%">位置</th>
				<th width="130">删除时间</th>
				<th width="70">类型</th>
				<th width="78">大小</th>
				<th width="150">操作</th>
			</tr>
		</thead>
		<tbody>
		<?php $i = 1; foreach ($docs as $d) { ?>
			<tr data-docid="<?php echo $d['doc_id']; ?>">
				<td><input type="checkbox" class="plm-row-check" value="<?php echo $d['doc_id']; ?>" /></td>
				<td><?php echo $i; ?></td>
				<td class="left"><?php echo htmlspecialchars($d['doc_name']); ?> <span style="color:#999;font-size:11px;">[<?php echo $d['current_version']; ?>]</span></td>
				<td class="left" title="<?php echo htmlspecialchars(PlmFolderPath($d['folder_id'])); ?>"><?php echo htmlspecialchars(PlmFolderPath($d['folder_id'])); ?></td>
				<td><?php echo $d['last_update_date'] ? date('Y-m-d H:i:s', $d['last_update_date']) : '—'; ?></td>
				<td><?php echo $d['doc_type'] != '' ? htmlspecialchars($d['doc_type']) : '文件'; ?></td>
				<td><?php echo PlmSize($d['file_patch']); ?></td>
				<td class="op">
					<a href="javascript:void(0)" onclick="PlmRestore(<?php echo intval($d['doc_id']); ?>)">恢复</a>
					<a href="javascript:void(0)" class="del" onclick="PlmPurge(<?php echo intval($d['doc_id']); ?>)">彻底删除</a>
				</td>
			</tr>
		<?php $i++; } ?>
		</tbody>
	</table>
	</div>
	<?php } ?>
	<?php
}

/* ================= AJAX 局部刷新（点击目录/搜索后只刷新右侧面板） ================= */
/* 物料目录：分类视图（右侧显示该分类物料及其文档） */
if (isset($_GET['cat']) && $_GET['cat'] != '') {
	header('Content-Type: text/html; charset=utf-8');
	while (ob_get_level()) { ob_end_clean(); }
	$cat = trim($_GET['cat']);
	/* 该分类下所有物料（含关联文档统计） */
	$r = DB_query("SELECT i.item_id, i.item_no, i.item_name, i.item_type,
	    (SELECT COUNT(*) FROM doc_master d WHERE d.item_no=i.item_no AND d.status<>'已删除') AS doc_cnt
	    FROM sf_item_no i WHERE i.item_category1='" . $cat . "' AND i.able_flag='Y' ORDER BY i.item_no", $db);
	$items = array();
	while ($row = DB_fetch_array($r)) { $items[] = $row; }
	?>
	<div class="plm-search" style="justify-content:flex-start;">
		<span style="font-size:13px;font-weight:bold;color:#0d47a1;">📦 物料目录 / <?php echo htmlspecialchars($cat); ?></span>
		<span style="margin-left:auto;font-size:12px;color:#888;">共 <b><?php echo count($items); ?></b> 个物料</span>
	</div>
	<div class="plm-table-wrap">
	<table class="plm-table">
		<thead><tr>
			<th width="50">序号</th><th width="150">物料编码</th><th width="24%">物料名称</th><th width="90">类型</th><th width="100">关联文档</th><th>操作</th>
		</tr></thead>
		<tbody>
		<?php $i = 1; foreach ($items as $it) { ?>
			<tr>
				<td><?php echo $i; ?></td>
				<td><a href="<?php echo $RootPath; ?>/Item_No2.php?ItemID=<?php echo intval($it['item_id']); ?>&amp;ItemNo=<?php echo urlencode($it['item_no']); ?>" target="_blank" style="color:#1976D2;text-decoration:none;"><?php echo htmlspecialchars($it['item_no']); ?></a></td>
				<td class="left"><?php echo htmlspecialchars($it['item_name']); ?></td>
				<td><?php echo htmlspecialchars($it['item_type']); ?></td>
				<td><?php echo $it['doc_cnt']; ?></td>
				<td class="op">
					<a href="javascript:void(0)" onclick="PlmGoItem('<?php echo htmlspecialchars($it['item_no'], ENT_QUOTES); ?>')">查看文档</a>
				</td>
			</tr>
		<?php $i++; } ?>
		</tbody>
	</table>
	</div>
	<?php
	exit;
}
if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
	header('Content-Type: text/html; charset=utf-8');
	while (ob_get_level()) { ob_end_clean(); }
	$ajaxFolder = isset($_GET['folder']) ? intval($_GET['folder']) : 1;
	if ($ajaxFolder == 0) { $ajaxFolder = 1; }
	$ajaxMode = isset($_GET['mode']) ? trim($_GET['mode']) : '';
	if ($ajaxFolder == -1) {
		/* ===== 物料目录：有 item 参数 → 该物料文档视图；无 → 总览 ===== */
		$ajaxItem = isset($_GET['item']) ? trim($_GET['item']) : '';
		if ($ajaxItem != '') {
			$itemInfo = array();
			$ri = DB_query("SELECT item_no, item_name, item_type, item_category1, item_id FROM sf_item_no WHERE item_no='" . $ajaxItem . "' LIMIT 1", $db);
			if ($ri) { $row = DB_fetch_array($ri); if ($row) $itemInfo = $row; }
			$rd = DB_query("SELECT m.doc_id, m.doc_code, m.doc_name, m.doc_type, m.item_no, m.status, m.current_version, m.remark, m.created_by, m.creation_date,
			    f.file_patch, f.file_ext, f.file_size,
			    (SELECT folder_name FROM doc_folder df WHERE df.folder_id=m.folder_id) AS folder_name,
			    (SELECT action FROM doc_log WHERE doc_id=m.doc_id ORDER BY action_date DESC LIMIT 1) AS last_action
			    FROM doc_master m LEFT JOIN doc_file f ON f.doc_id=m.doc_id
			    AND f.file_id = (SELECT MAX(file_id) FROM doc_file df WHERE df.doc_id=m.doc_id AND df.is_current='Y')
			    WHERE m.item_no='" . $ajaxItem . "' AND m.status<>'已删除' ORDER BY m.creation_date DESC", $db);
			$docs = array();
			while ($row = DB_fetch_array($rd)) { $docs[] = $row; }
			?>
			<div class="plm-search" style="justify-content:flex-start;">
				<span style="font-size:13px;font-weight:bold;color:#0d47a1;">� 物料目录 / <b><?php echo htmlspecialchars($ajaxItem); ?></b> <?php echo htmlspecialchars(isset($itemInfo['item_name']) ? $itemInfo['item_name'] : ''); ?> <span style="color:#888;font-weight:normal;font-size:12px;">[<?php echo htmlspecialchars(isset($itemInfo['item_category1']) ? $itemInfo['item_category1'] : ''); ?>]</span></span>
				<span style="margin-left:auto;font-size:12px;color:#888;">共 <b><?php echo count($docs); ?></b> 个文档</span>
			</div>
			<div class="plm-table-wrap">
			<table class="plm-table">
				<thead><tr><th width="36"><input type="checkbox" onclick="PlmToggleAll(this)" /></th><th width="42">序号</th><th width="40">图标</th><th width="80">状态</th><th width="22%">文件名称</th><th width="52">后缀</th><th width="104">文件编码</th><th width="78">大小</th><th width="82">所在目录</th><th width="72">创建者</th><th width="12%">备注</th><th width="100">最近操作</th><th width="170">操作</th></tr></thead>
				<tbody>
				<?php if (count($docs) == 0) { ?>
					<tr><td colspan="13" style="padding:30px;text-align:center;color:#888;">该物料暂未关联文档<br><span style="color:#aaa;font-size:12px;">在 DocPLM 文档工作区导入文档时填入物料号，或在文档属性里关联此物料</span></td></tr>
				<?php } else { $i = 1; foreach ($docs as $d) { ?>
					<tr><td><input type="checkbox" class="plm-row-check" value="<?php echo intval($d['doc_id']); ?>" /></td>
						<td><?php echo $i; ?></td>
						<td style="font-size:18px;"><?php $fext = strtolower($d['file_ext']); echo in_array($fext, array('jpg','jpeg','png','gif','bmp')) ? '🖼' : ($fext === 'pdf' ? '📕' : '�'); ?></td>
						<td><span class="plm-tag st-<?php echo $d['status']==='正常'?'ok':($d['status']==='已归档'?'arch':($d['status']==='已废止'?'abd':'del')); ?>"><?php echo htmlspecialchars($d['status']); ?></span></td>
						<td class="left"><?php echo htmlspecialchars($d['doc_name']); ?> <span style="color:#999;font-size:11px;">[<?php echo htmlspecialchars($d['current_version']); ?>]</span></td>
						<td><?php echo strtoupper($fext); ?></td>
						<td><?php echo htmlspecialchars($d['doc_code']); ?></td>
						<td><?php echo $d['file_size'] > 0 ? (round($d['file_size'] / 1024, 1) . ' KB') : '—'; ?></td>
						<td><?php echo htmlspecialchars($d['folder_name']); ?></td>
						<td><?php echo htmlspecialchars($d['created_by']); ?></td>
						<td class="left"><?php echo htmlspecialchars($d['remark']); ?></td>
						<td><?php echo $d['last_action'] != '' ? htmlspecialchars($d['last_action']) : '—'; ?></td>
						<td class="op">
							<a href="javascript:void(0)" onclick="PlmProps(<?php echo intval($d['doc_id']); ?>)">属性</a>
							<a href="javascript:void(0)" onclick="PlmVersions(<?php echo intval($d['doc_id']); ?>)">版本</a>
							<a href="javascript:void(0)" onclick="PlmLogView(<?php echo intval($d['doc_id']); ?>)">日志</a>
						<?php if ($d['file_patch'] && file_exists($d['file_patch'])) { ?>
							<a href="<?php echo $RootPath . '/' . $d['file_patch']; ?>" download="<?php echo htmlspecialchars(PlmDownloadName($d['doc_name'], $fext)); ?>">下载</a>
						<?php } ?>
						</td>
					</tr>
				<?php $i++; } } ?>
				</tbody>
			</table>
			</div>
			<?php
			exit;
		}
		/* ===== 物料目录总览（folder=-1）：按分类统计 ===== */
		$r = DB_query("SELECT i.item_category1, COUNT(*) AS item_cnt,
		    (SELECT COUNT(*) FROM doc_master d JOIN sf_item_no s2 ON s2.item_no=d.item_no WHERE s2.item_category1=i.item_category1 AND d.status<>'已删除') AS doc_cnt
		    FROM sf_item_no i WHERE i.item_category1<>'' AND i.able_flag='Y' GROUP BY i.item_category1 ORDER BY i.item_category1", $db);
		$cats = array();
		while ($row = DB_fetch_array($r)) { $cats[] = $row; }
		?>
		<div class="plm-search" style="justify-content:flex-start;">
			<span style="font-size:13px;font-weight:bold;color:#0d47a1;">📦 物料目录总览（自动聚合 item_category1，随物料分类实时更新）</span>
			<span style="margin-left:auto;font-size:12px;color:#888;">共 <b><?php echo count($cats); ?></b> 个分类</span>
		</div>
		<div class="plm-table-wrap">
		<table class="plm-table">
			<thead><tr><th width="50">序号</th><th width="30%">物料分类</th><th width="120">物料数</th><th width="120">关联文档数</th><th>操作</th></tr></thead>
			<tbody>
			<?php $i = 1; foreach ($cats as $c) { ?>
				<tr>
					<td><?php echo $i; ?></td>
					<td class="left" style="font-weight:bold;"><?php echo htmlspecialchars($c['item_category1']); ?></td>
					<td><?php echo $c['item_cnt']; ?></td>
					<td><?php echo $c['doc_cnt']; ?></td>
					<td class="op"><a href="javascript:void(0)" onclick="PlmGoCat('<?php echo htmlspecialchars($c['item_category1'], ENT_QUOTES); ?>')">查看分类</a></td>
				</tr>
			<?php $i++; } ?>
			</tbody>
		</table>
		</div>
		<?php
		exit;
	}
	/* 废止区 AJAX 局部刷新 */
	if ($ajaxMode == 'abolition') {
		$awhere = array("m.status='已废止'", "m.is_template<>'Y'");
		if ($ajaxFolder > 0) { $awhere[] = "m.folder_id=" . intval($ajaxFolder); }
		if (isset($_GET['q']) && trim($_GET['q']) != '') { $awhere[] = "m.doc_name LIKE '%" . str_replace(array('%', '_'), array('\\%', '\\_'), trim($_GET['q'])) . "%'"; }
		if (isset($_GET['code']) && trim($_GET['code']) != '') { $awhere[] = "m.doc_code LIKE '%" . str_replace(array('%', '_'), array('\\%', '\\_'), trim($_GET['code'])) . "%'"; }
		$ra = DB_query("SELECT m.doc_id, m.doc_code, m.doc_name, m.doc_type, m.folder_id, m.status, m.current_version, m.remark, m.created_by, m.creation_date, m.abolish_date, m.abolish_remark,
			f.file_patch, f.file_ext, f.file_size,
			(SELECT folder_name FROM doc_folder df WHERE df.folder_id=m.folder_id) AS folder_name,
			(SELECT action FROM doc_log WHERE doc_id=m.doc_id ORDER BY action_date DESC LIMIT 1) AS last_action
			FROM doc_master m LEFT JOIN doc_file f ON f.doc_id=m.doc_id
			AND f.file_id = (SELECT MAX(file_id) FROM doc_file df WHERE df.doc_id=m.doc_id AND df.is_current='Y')
			WHERE " . implode(' AND ', $awhere) . " ORDER BY m.abolish_date DESC, m.creation_date DESC", $db);
		$abdocs = array(); while ($row = DB_fetch_array($ra)) { $abdocs[] = $row; }
		$ajaxFolderName = '主目录';
		$rfn = DB_query("SELECT folder_name FROM doc_folder WHERE folder_id=" . intval($ajaxFolder), $db);
		if ($rfn && $rfr = DB_fetch_array($rfn)) { $ajaxFolderName = $rfr['folder_name']; }
		PlmRenderAbolition($db, $ajaxFolder, $ajaxFolderName, $abdocs, isset($_GET['q']) ? trim($_GET['q']) : '', isset($_GET['code']) ? trim($_GET['code']) : '');
		exit;
	}
	/* 回收站 AJAX 局部刷新 */
	if ($ajaxMode == 'recycle') {
		$trCond = '1=1';
		$tr = isset($_GET['tr']) ? trim($_GET['tr']) : 'all';
		switch ($tr) {
			case 'today':     $trCond = "m.last_update_date >= UNIX_TIMESTAMP(CURDATE())"; break;
			case 'yesterday': $trCond = "m.last_update_date >= UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 1 DAY)) AND m.last_update_date < UNIX_TIMESTAMP(CURDATE())"; break;
			case 'thisweek':  $trCond = "m.last_update_date >= UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY))"; break;
			case 'lastweek':  $trCond = "m.last_update_date >= UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE())+7 DAY)) AND m.last_update_date < UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY))"; break;
			case 'thismonth': $trCond = "m.last_update_date >= UNIX_TIMESTAMP(DATE_FORMAT(CURDATE(), '%Y-%m-01'))"; break;
			case 'lastyear':  $trCond = "m.last_update_date < UNIX_TIMESTAMP(DATE_FORMAT(CURDATE(), '%Y-%m-01'))"; break;
		}
		$rwhere = array("m.status='已删除'", $trCond);
		if (isset($_GET['q']) && trim($_GET['q']) != '') { $rwhere[] = "m.doc_name LIKE '%" . str_replace(array('%', '_'), array('\\%', '\\_'), trim($_GET['q'])) . "%'"; }
		if (isset($_GET['code']) && trim($_GET['code']) != '') { $rwhere[] = "m.doc_code LIKE '%" . str_replace(array('%', '_'), array('\\%', '\\_'), trim($_GET['code'])) . "%'"; }
		$rr = DB_query("SELECT m.doc_id, m.doc_code, m.doc_name, m.doc_type, m.folder_id, m.status, m.current_version, m.remark, m.created_by, m.creation_date, m.last_update_date,
			f.file_patch, f.file_ext, f.file_size,
			(SELECT folder_name FROM doc_folder df WHERE df.folder_id=m.folder_id) AS folder_name_lookup,
			(SELECT action FROM doc_log WHERE doc_id=m.doc_id ORDER BY action_date DESC LIMIT 1) AS last_action
			FROM doc_master m LEFT JOIN doc_file f ON f.doc_id=m.doc_id
			AND f.file_id = (SELECT MAX(file_id) FROM doc_file df WHERE df.doc_id=m.doc_id AND df.is_current='Y')
			WHERE " . implode(' AND ', $rwhere) . " ORDER BY m.last_update_date DESC", $db);
		$recdocs = array(); while ($row = DB_fetch_array($rr)) { $recdocs[] = $row; }
		PlmRenderRecycle($db, $ajaxFolder, '所有文件', $recdocs, isset($_GET['q']) ? trim($_GET['q']) : '', isset($_GET['code']) ? trim($_GET['code']) : '');
		exit;
	}
	list($ajaxFolderName, $ajaxQ, $ajaxCode, $ajaxExt, $ajaxHide, $ajaxItem, $ajaxDocs) = PlmQuery($db, $ajaxFolder);
	PlmRenderRight($db, $ajaxFolder, $ajaxFolderName, $ajaxDocs, $ajaxQ, $ajaxCode, $ajaxExt, $ajaxHide, $ajaxItem, isset($_GET['tpl']) && $_GET['tpl'] == '1');
	exit;
}

include('includes/header.inc');
include('includes/doc_nav.inc');
/* lhgdialog 在 yixin 自己目录下（header.inc 写死的 /JXC/ 根路径 404），单独加载正确路径 */
echo '<script src="' . $RootPath . '/javascript/lhgdialog.min.js?self=true&skin=chrome"></script>';
include('includes/SQL_CommonFunctions.inc');
require_once 'upload2.class.php';

$AllowExt = array('jpeg', 'jpg', 'png', 'gif', 'bmp', 'webp', 'tiff', 'svg', 'step', 'stp', 'igs', 'iges', 'dwg', 'dxf', 'sat', 'pdf', 'txt', 'md', 'csv', 'xml', 'json', 'log', 'rtf', 'odt', 'ods', 'odp', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'zip', 'rar', '7z', 'tar', 'gz', 'mp4', 'mp3', 'wav');
$AllowMime = array('image/jpeg', 'image/png', 'image/gif', 'image/bmp', 'application/pdf', 'application/zip', 'application/x-zip-compressed', 'application/x-rar-compressed', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 'text/plain', 'application/octet-stream', 'application/x-msdownload');

/* ---------- 图标 ---------- */
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
	elseif (in_array($ext, array('step', 'stp', 'dwg', 'dxf', 'igs'))) { $badge = '<rect x="9.5" y="10.5" width="4.5" height="3.5" fill="#2196F3" stroke="#1565C0" stroke-width="0.3"/><text x="11.7" y="13.2" font-size="3" fill="#fff" text-anchor="middle" font-family="Arial">CAD</text>'; }
	elseif (in_array($ext, array('xls', 'xlsx'))) { $badge = '<rect x="9.5" y="10.5" width="4.5" height="3.5" fill="#4CAF50" stroke="#2E7D32" stroke-width="0.3"/><text x="11.7" y="13.2" font-size="3" fill="#fff" text-anchor="middle" font-family="Arial">XLS</text>'; }
	elseif (in_array($ext, array('doc', 'docx'))) { $badge = '<rect x="9.5" y="10.5" width="4.5" height="3.5" fill="#1565C0" stroke="#0D47A1" stroke-width="0.3"/><text x="11.7" y="13.2" font-size="3" fill="#fff" text-anchor="middle" font-family="Arial">DOC</text>'; }
	elseif (in_array($ext, array('zip', 'rar', '7z'))) { $badge = '<rect x="9.5" y="10.5" width="4.5" height="3.5" fill="#795548" stroke="#3E2723" stroke-width="0.3"/><text x="11.7" y="13.2" font-size="3" fill="#fff" text-anchor="middle" font-family="Arial">ZIP</text>'; }
	elseif (in_array($ext, array('jpg', 'jpeg', 'png', 'gif', 'bmp'))) { $badge = '<rect x="9.5" y="10.5" width="4.5" height="3.5" fill="#9C27B0" stroke="#6A1B9A" stroke-width="0.3"/><text x="11.7" y="13.2" font-size="3" fill="#fff" text-anchor="middle" font-family="Arial">IMG</text>'; }
	return '<svg viewBox="0 0 16 16" width="16" height="16" style="display:inline-block;vertical-align:middle;width:16px;height:16px;margin:0 4px 0 0;flex:0 0 auto" aria-hidden="true"><path d="M3 1 L10.5 1 L13 3.5 L13 15 L3 15 Z" fill="#fff" stroke="#5F6B7A" stroke-width="0.6"/><path d="M10.5 1 L10.5 3.5 L13 3.5" fill="#CFD8DC" stroke="#5F6B7A" stroke-width="0.4"/><line x1="4.5" y1="6" x2="11" y2="6" stroke="#90A4AE" stroke-width="0.5"/><line x1="4.5" y1="8" x2="11" y2="8" stroke="#90A4AE" stroke-width="0.5"/>' . $badge . '</svg>';
}
function PlmSize($patch) {
	if (!file_exists($patch)) return '—';
	$s = @filesize($patch);
	if ($s >= 1048576) return round($s / 1048576, 2) . ' MB';
	if ($s >= 1024) return round($s / 1024, 1) . ' KB';
	return $s . ' B';
}
/* 拼接下载文件名：若名称本身已带该后缀则不重复追加（兼容历史脏数据） */
function PlmDownloadName($name, $ext) {
	$name = trim($name);
	if ($name === '') return $name;
	$ext = rtrim(strtolower((string)$ext), '.');
	if ($ext !== '' && strtolower(substr($name, -strlen($ext) - 1)) === '.' . $ext) return $name;
	return $ext !== '' ? $name . '.' . $ext : $name;
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
function PlmLog($docId, $action, $remark = '') {
	global $db;
	$sql = "INSERT INTO doc_log (doc_id, action, operator, action_date, remark) VALUES ('" . intval($docId) . "','" . $action . "','" . $_SESSION['UserID'] . "','" . time() . "','" . $remark . "')";
	DB_query($sql, $db);
}

/* ---------- 会话消息 ---------- */
if (isset($_SESSION['PlmMsg']) && is_array($_SESSION['PlmMsg'])) {
	foreach ($_SESSION['PlmMsg'] as $m) { prnMsg($m['msg'], $m['type']); }
	unset($_SESSION['PlmMsg']);
}

/* ================= 目录操作 ================= */
if (isset($_POST['folder_save'])) {
	$pname = trim($_POST['folder_name']);
	$pparent = intval($_POST['folder_parent']);
	if ($pname != '') {
		DB_query("INSERT INTO doc_folder (parent_id, folder_name, folder_code, folder_type, sort_order, disable_flag, created_by, creation_date) VALUES ('" . $pparent . "','" . $pname . "','','folder',0,'N','" . $_SESSION['UserID'] . "','" . time() . "')", $db);
		$_SESSION['PlmMsg'][] = array('type' => 'success', 'msg' => '目录「' . $pname . '」创建成功！');
	} else {
		$_SESSION['PlmMsg'][] = array('type' => 'error', 'msg' => '目录名称不能为空！');
	}
	header('Location: ' . $RootPath . '/DocPLM.php?folder=' . $pparent);
	exit;
}
if (isset($_POST['folder_rename'])) {
	$fid = intval($_POST['folder_id']);
	$fname = trim($_POST['folder_name']);
	if ($fid > 0 && $fname != '') {
		DB_query("UPDATE doc_folder SET folder_name='" . $fname . "', last_update_by='" . $_SESSION['UserID'] . "', last_update_date='" . time() . "' WHERE folder_id=" . $fid, $db);
		$_SESSION['PlmMsg'][] = array('type' => 'success', 'msg' => '目录已重命名！');
	}
	header('Location: ' . $RootPath . '/DocPLM.php?folder=' . intval($_POST['folder_parent']));
	exit;
}
if (isset($_POST['folder_delete'])) {
	$fid = intval($_POST['folder_id']);
	$cntChild = 0;
	$r = DB_query("SELECT COUNT(*) c FROM doc_folder WHERE parent_id=" . $fid . " AND disable_flag<>'Y'", $db);
	if ($row = DB_fetch_row($r)) { $cntChild = $row[0]; }
	$cntDoc = 0;
	$r = DB_query("SELECT COUNT(*) c FROM doc_master WHERE folder_id=" . $fid . " AND status<>'已删除'", $db);
	if ($row = DB_fetch_row($r)) { $cntDoc = $row[0]; }
	if ($cntChild > 0 || $cntDoc > 0) {
		$_SESSION['PlmMsg'][] = array('type' => 'error', 'msg' => '目录下有子目录或文档，不能删除！');
	} else {
		DB_query("UPDATE doc_folder SET disable_flag='Y' WHERE folder_id=" . $fid, $db);
		$_SESSION['PlmMsg'][] = array('type' => 'success', 'msg' => '目录已删除！');
	}
	header('Location: ' . $RootPath . '/DocPLM.php?folder=' . intval($_POST['folder_parent']));
	exit;
}

/* ================= 文档操作 ================= */
$CurFolder = isset($_GET['folder']) ? intval($_GET['folder']) : 0;
if ($CurFolder == 0) { $CurFolder = 1; } /* 默认主目录 */

/* ================= 文档模板视图（mode=template，UI 仿文档工作区，复用主页面结构） ================= */
$isTemplateMode = isset($_GET['mode']) && $_GET['mode'] == 'template';
$tmpls = array();
if ($isTemplateMode) {
	$r = DB_query("SELECT m.doc_id, m.doc_code, m.doc_name, m.doc_type, m.status, m.current_version, m.remark, m.created_by, m.creation_date,
	    f.file_patch, f.file_ext, f.file_size,
	    (SELECT folder_name FROM doc_folder df WHERE df.folder_id=m.folder_id) AS folder_name,
	    (SELECT action FROM doc_log WHERE doc_id=m.doc_id ORDER BY action_date DESC LIMIT 1) AS last_action
	    FROM doc_master m LEFT JOIN doc_file f ON f.doc_id=m.doc_id
	    AND f.file_id = (SELECT MAX(file_id) FROM doc_file df WHERE df.doc_id=m.doc_id AND df.is_current='Y')
	    WHERE m.is_template='Y' AND m.status<>'已删除' ORDER BY m.creation_date DESC", $db);
	while ($row = DB_fetch_array($r)) { $tmpls[] = $row; }
}

/* ================= 废止区 / 回收站 模式查询 ================= */
$isAbolition = ($mode == 'abolition');
$isRecycle = ($mode == 'recycle');
$abdocs = array();
$recdocs = array();
$recTimeRange = isset($_GET['tr']) ? trim($_GET['tr']) : 'all';
if ($isAbolition) {
	$awhere = array("m.status='已废止'", "m.is_template<>'Y'");
	if ($CurFolder > 0) { $awhere[] = "m.folder_id=" . intval($CurFolder); }
	if (isset($_GET['q']) && trim($_GET['q']) != '') { $awhere[] = "m.doc_name LIKE '%" . str_replace(array('%', '_'), array('\\%', '\\_'), trim($_GET['q'])) . "%'"; }
	if (isset($_GET['code']) && trim($_GET['code']) != '') { $awhere[] = "m.doc_code LIKE '%" . str_replace(array('%', '_'), array('\\%', '\\_'), trim($_GET['code'])) . "%'"; }
	$ra = DB_query("SELECT m.doc_id, m.doc_code, m.doc_name, m.doc_type, m.folder_id, m.status, m.current_version, m.remark, m.created_by, m.creation_date, m.abolish_date, m.abolish_remark,
		f.file_patch, f.file_ext, f.file_size,
		(SELECT folder_name FROM doc_folder df WHERE df.folder_id=m.folder_id) AS folder_name,
		(SELECT action FROM doc_log WHERE doc_id=m.doc_id ORDER BY action_date DESC LIMIT 1) AS last_action
		FROM doc_master m LEFT JOIN doc_file f ON f.doc_id=m.doc_id
		AND f.file_id = (SELECT MAX(file_id) FROM doc_file df WHERE df.doc_id=m.doc_id AND df.is_current='Y')
		WHERE " . implode(' AND ', $awhere) . " ORDER BY m.abolish_date DESC, m.creation_date DESC", $db);
	while ($row = DB_fetch_array($ra)) { $abdocs[] = $row; }
}
if ($isRecycle) {
	$trCond = '1=1';
	switch ($recTimeRange) {
		case 'today':     $trCond = "m.last_update_date >= UNIX_TIMESTAMP(CURDATE())"; break;
		case 'yesterday': $trCond = "m.last_update_date >= UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 1 DAY)) AND m.last_update_date < UNIX_TIMESTAMP(CURDATE())"; break;
		case 'thisweek':  $trCond = "m.last_update_date >= UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY))"; break;
		case 'lastweek':  $trCond = "m.last_update_date >= UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE())+7 DAY)) AND m.last_update_date < UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY))"; break;
		case 'thismonth': $trCond = "m.last_update_date >= UNIX_TIMESTAMP(DATE_FORMAT(CURDATE(), '%Y-%m-01'))"; break;
		case 'lastyear':  $trCond = "m.last_update_date < UNIX_TIMESTAMP(DATE_FORMAT(CURDATE(), '%Y-%m-01'))"; break;
	}
	$rwhere = array("m.status='已删除'", $trCond);
	if (isset($_GET['q']) && trim($_GET['q']) != '') { $rwhere[] = "m.doc_name LIKE '%" . str_replace(array('%', '_'), array('\\%', '\\_'), trim($_GET['q'])) . "%'"; }
	if (isset($_GET['code']) && trim($_GET['code']) != '') { $rwhere[] = "m.doc_code LIKE '%" . str_replace(array('%', '_'), array('\\%', '\\_'), trim($_GET['code'])) . "%'"; }
	$rr = DB_query("SELECT m.doc_id, m.doc_code, m.doc_name, m.doc_type, m.folder_id, m.status, m.current_version, m.remark, m.created_by, m.creation_date, m.last_update_date,
		f.file_patch, f.file_ext, f.file_size,
		(SELECT folder_name FROM doc_folder df WHERE df.folder_id=m.folder_id) AS folder_name,
		(SELECT action FROM doc_log WHERE doc_id=m.doc_id ORDER BY action_date DESC LIMIT 1) AS last_action
		FROM doc_master m LEFT JOIN doc_file f ON f.doc_id=m.doc_id
		AND f.file_id = (SELECT MAX(file_id) FROM doc_file df WHERE df.doc_id=m.doc_id AND df.is_current='Y')
		WHERE " . implode(' AND ', $rwhere) . " ORDER BY m.last_update_date DESC", $db);
	while ($row = DB_fetch_array($rr)) { $recdocs[] = $row; }
}

/* 导入文档 */
if (isset($_POST['doc_import'])) {
	$folderId = intval($_POST['folder_id']);
	if ($folderId <= 0) { $folderId = 1; } /* 物料目录(-1)等非目录模式导入 → 归入主目录 */
	$time = time();
	$upload = new upload2('Pic', 'SO', false, 31457280, $AllowExt, $AllowMime);
	$dest = $upload->uploadFile();
	$uploaded = array_values($dest);
	$errInfo = '';
	foreach ($uploaded as $u) {
		/* 只检查"已选但失败"的（已成功上传的跳过）；未选的 Pic（error=4 无 dest）忽略 */
		if (isset($u['error']) && $u['error'] != '文件上传成功' && isset($u['dest']) && $u['dest'] != '') {
			$errInfo = $u['error']; break;
		}
	}
	$cnt = 0;
	foreach ($_POST as $key => $value) {
			if ($value != '' && substr($key, 0, 9) == 'file_name') {
				$idx = (int) substr($key, 9) - 1;
				if (isset($uploaded[$idx]['dest']) && $uploaded[$idx]['dest'] != '') {
					$docName = $value;
					$docCode = isset($_POST['doc_code_' . ($idx + 1)]) && trim($_POST['doc_code_' . ($idx + 1)]) != '' ? $_POST['doc_code_' . ($idx + 1)] : PlmGenCode();
					$docItem = isset($_POST['item_no_' . ($idx + 1)]) ? trim($_POST['item_no_' . ($idx + 1)]) : '';
					$isTpl = (isset($_POST['as_template']) && $_POST['as_template'] == '1') ? 'Y' : 'N';
					$patch = $uploaded[$idx]['dest'];
					$ext = strtolower(pathinfo($patch, PATHINFO_EXTENSION));
					// 自动从文档名称去除后缀（防止原文件名带后缀导致下载时后缀重复）
					if ($ext !== '' && strtolower(substr($docName, -strlen($ext)-1)) === '.' . $ext) {
						$docName = substr($docName, 0, -strlen($ext)-1);
					}
					$fsize = @filesize($patch);
				DB_query("INSERT INTO doc_master (doc_code, doc_name, doc_type, folder_id, item_no, status, check_status, current_version, is_template, remark, created_by, creation_date, last_update_by, last_update_date) VALUES ('" . $docCode . "','" . $docName . "','','" . $folderId . "','" . $docItem . "','正常','在库','v1','" . $isTpl . "','','" . $_SESSION['UserID'] . "','" . $time . "','" . $_SESSION['UserID'] . "','" . $time . "')", $db);
				$docId = isset($_SESSION['LastInsertId']) ? intval($_SESSION['LastInsertId']) : 0;
				DB_query("INSERT INTO doc_file (doc_id, version_no, file_name, file_patch, file_ext, file_size, is_current, uploader, upload_date) VALUES ('" . $docId . "','v1','" . $docName . "','" . $patch . "','" . $ext . "','" . $fsize . "','Y','" . $_SESSION['UserID'] . "','" . $time . "')", $db);
				PlmLog($docId, '导入', '初始版本 v1');
				$cnt++;
			}
		}
	}
	if ($cnt > 0) {
		$_SESSION['PlmMsg'][] = array('type' => 'success', 'msg' => '成功导入 ' . $cnt . ' 个文档！');
	} else {
		$_SESSION['PlmMsg'][] = array('type' => 'error', 'msg' => '导入失败：' . ($errInfo != '' ? $errInfo : '未选择文件或未填写文档名称'));
	}
	/* 导入后跳回原页面（保持模板模式/物料目录模式） */
	$redirect = isset($_POST['redirect_to']) ? $_POST['redirect_to'] : '';
	if ($redirect === 'mode=template') { $loc = $RootPath . '/DocPLM.php?mode=template'; }
	else if ($redirect === 'tpl=1') { $loc = $RootPath . '/DocPLM.php?folder=' . $folderId . '&tpl=1'; }
	else if ($redirect === 'folder=-1') { $loc = $RootPath . '/DocPLM.php?folder=-1'; }
	else { $loc = $RootPath . '/DocPLM.php?folder=' . $folderId; }
	header('Location: ' . $loc);
	exit;
}

/* 状态/删除/升版（支持批量 doc_id[]） */
	if (isset($_POST['doc_action']) && isset($_POST['doc_id'])) {
	$docIds = $_POST['doc_id'];
	if (!is_array($docIds)) { $docIds = array($docIds); }
	$action = $_POST['doc_action'];
	$time = time();
	$successCount = 0;
	$failCount = 0;
	$lastDocId = 0;
	foreach ($docIds as $oneId) {
		$docId = intval($oneId);
		if ($docId <= 0) continue;
		$lastDocId = $docId;
		/* 读取当前状态 */
		$curStatus = '';
		$r = DB_query("SELECT status FROM doc_master WHERE doc_id=" . $docId, $db);
		if ($row = DB_fetch_array($r)) { $curStatus = $row['status']; }
		/* 状态流转校验 */
		$errMsg = '';
		switch ($action) {
		case 'archive':   if ($curStatus != '正常') { $errMsg = '只有「正常」状态的文档才能归档！'; } break;
		case 'unarchive': if ($curStatus != '已归档') { $errMsg = '只有「已归档」的文档才能反归档！'; } break;
		case 'abandon':   if ($curStatus != '正常') { $errMsg = '只有「正常」状态的文档才能废止！'; } break;
		case 'recover':   if ($curStatus != '已废止') { $errMsg = '只有「已废止」的文档才能恢复！'; } break;
		case 'restore':   if ($curStatus != '已删除') { $errMsg = '只有「已删除」状态的文档才能从回收站还原！'; } break;
		case 'purge':     if ($curStatus != '已删除') { $errMsg = '只有「已删除」状态的文档才能彻底删除！'; } break;
		case 'delete':    if ($curStatus != '正常') { $errMsg = '只有「正常」状态的文档才能删除（归档/废止文档请先恢复）！'; } break;
		/* 升版：允许正常/已归档（已归档 = "已签发"，升版生成新版本；不允许已废止） */
		case 'upgrade':   if ($curStatus != '正常' && $curStatus != '已归档') { $errMsg = '已废止的文档不能升版，请先恢复！'; } break;
		case 'props':     break;
	}
	if ($errMsg != '') {
		$_SESSION['PlmMsg'][] = array('type' => 'error', 'msg' => $errMsg);
	} else {
	switch ($action) {
		case 'archive':
			DB_query("UPDATE doc_master SET status='已归档', last_update_by='" . $_SESSION['UserID'] . "', last_update_date='" . $time . "' WHERE doc_id=" . $docId, $db);
			PlmLog($docId, '归档', '');
			$_SESSION['PlmMsg'][] = array('type' => 'success', 'msg' => '文档已归档！');
			break;
		case 'unarchive':
			DB_query("UPDATE doc_master SET status='正常', last_update_by='" . $_SESSION['UserID'] . "', last_update_date='" . $time . "' WHERE doc_id=" . $docId, $db);
			PlmLog($docId, '反归档', '');
			$_SESSION['PlmMsg'][] = array('type' => 'success', 'msg' => '文档已取消归档！');
			break;
		case 'abandon':
			$abRemark = isset($_POST['doc_remark']) ? trim($_POST['doc_remark']) : '';
			DB_query("UPDATE doc_master SET status='已废止', abolish_date='" . $time . "', abolish_remark='" . $abRemark . "', last_update_by='" . $_SESSION['UserID'] . "', last_update_date='" . $time . "' WHERE doc_id=" . $docId, $db);
			PlmLog($docId, '废止', $abRemark);
			$_SESSION['PlmMsg'][] = array('type' => 'success', 'msg' => '文档已废止！');
			break;
		case 'recover':
			DB_query("UPDATE doc_master SET status='正常', abolish_date=NULL, abolish_remark='', last_update_by='" . $_SESSION['UserID'] . "', last_update_date='" . $time . "' WHERE doc_id=" . $docId, $db);
			PlmLog($docId, '恢复', '');
			$_SESSION['PlmMsg'][] = array('type' => 'success', 'msg' => '文档已恢复！');
			break;
		case 'delete':
			$r = DB_query("SELECT file_patch FROM doc_file WHERE doc_id=" . $docId . " AND is_current='Y'", $db);
			if ($row = DB_fetch_array($r)) {
				if (file_exists($row['file_patch'])) { @unlink($row['file_patch']); }
			}
			DB_query("UPDATE doc_master SET status='已删除', last_update_by='" . $_SESSION['UserID'] . "', last_update_date='" . $time . "' WHERE doc_id=" . $docId, $db);
			PlmLog($docId, '删除', '');
			$_SESSION['PlmMsg'][] = array('type' => 'success', 'msg' => '文档已删除！');
			break;
		case 'upgrade':
			/* 升版：上传新文件 */
			$r = DB_query("SELECT current_version FROM doc_master WHERE doc_id=" . $docId, $db);
			$curV = 'v1';
			if ($row = DB_fetch_array($r)) { $curV = $row['current_version']; }
			$verNum = intval(substr($curV, 1)) + 1;
			$newV = 'v' . $verNum;
			$upload = new upload2('Pic', 'SO', false, 31457280, $AllowExt, $AllowMime);
			$dest = $upload->uploadFile();
			$uploaded = array_values($dest);
			if (isset($uploaded[0]['dest']) && $uploaded[0]['dest'] != '') {
				$patch = $uploaded[0]['dest'];
				$ext = strtolower(pathinfo($patch, PATHINFO_EXTENSION));
				$fsize = @filesize($patch);
				DB_query("INSERT INTO doc_file (doc_id, version_no, file_name, file_patch, file_ext, file_size, is_current, uploader, upload_date, remark) VALUES ('" . $docId . "','" . $newV . "','','" . $patch . "','" . $ext . "','" . $fsize . "','N','" . $_SESSION['UserID'] . "','" . $time . "','" . (isset($_POST['upgrade_remark']) ? $_POST['upgrade_remark'] : '') . "')", $db);
				$newFileId = isset($_SESSION['LastInsertId']) ? intval($_SESSION['LastInsertId']) : 0;
				if ($newFileId > 0) { PlmSetCurrentFile($db, $docId, $newFileId); }
				DB_query("UPDATE doc_master SET last_update_by='" . $_SESSION['UserID'] . "', last_update_date='" . $time . "' WHERE doc_id=" . $docId, $db);
				PlmLog($docId, '升版', '升级到 ' . $newV);
				$_SESSION['PlmMsg'][] = array('type' => 'success', 'msg' => '文档已升版到 ' . $newV . '！');
			} else {
				$_SESSION['PlmMsg'][] = array('type' => 'error', 'msg' => '升版失败：请选择新文件！');
			}
			break;
		case 'props':
			$r = DB_query("UPDATE doc_master SET doc_name='" . $_POST['doc_name'] . "', doc_code='" . $_POST['doc_code'] . "', doc_type='" . $_POST['doc_type'] . "', item_no='" . $_POST['doc_item'] . "', remark='" . $_POST['doc_remark'] . "', last_update_by='" . $_SESSION['UserID'] . "', last_update_date='" . $time . "' WHERE doc_id=" . $docId, $db);
			PlmLog($docId, '修改属性', '');
			$_SESSION['PlmMsg'][] = array('type' => 'success', 'msg' => '文档属性已保存！');
			break;
		case 'template_new':
			/* 从模板复制为普通文档（doc_master + 当前版本文件） */
			$rs = DB_query("SELECT * FROM doc_master WHERE doc_id=" . $docId . " AND is_template='Y'", $db);
			$src = DB_fetch_array($rs);
			if ($src) {
				$newCode = PlmGenCode();
				$newName = $src['doc_name'] . '（副本）';
				DB_query("INSERT INTO doc_master (doc_code, doc_name, doc_type, folder_id, item_no, status, check_status, current_version, is_template, remark, created_by, creation_date, last_update_by, last_update_date)
				    VALUES ('" . $newCode . "','" . $newName . "','" . $src['doc_type'] . "','" . intval($src['folder_id']) . "','" . $src['item_no'] . "','正常','在库','v1','N','由模板「" . $src['doc_name'] . "」复制创建','" . $_SESSION['UserID'] . "','" . $time . "','" . $_SESSION['UserID'] . "','" . $time . "')", $db);
				$newDocId = isset($_SESSION['LastInsertId']) ? intval($_SESSION['LastInsertId']) : 0;
				if ($newDocId > 0) {
					$rf = DB_query("SELECT * FROM doc_file WHERE doc_id=" . $docId . " AND is_current='Y' ORDER BY file_id DESC LIMIT 1", $db);
					if ($rowf = DB_fetch_array($rf)) {
						DB_query("INSERT INTO doc_file (doc_id, version_no, file_name, file_patch, file_ext, file_size, is_current, uploader, upload_date, remark)
						    VALUES ('" . $newDocId . "','v1','" . $rowf['file_name'] . "','" . $rowf['file_patch'] . "','" . $rowf['file_ext'] . "','" . $rowf['file_size'] . "','Y','" . $_SESSION['UserID'] . "','" . $time . "','从模板复制')", $db);
					}
					PlmLog($newDocId, '新建', '从模板「' . $src['doc_name'] . '」复制创建');
					$_SESSION['PlmMsg'][] = array('type' => 'success', 'msg' => '已从模板创建文档：' . $newName . '！');
				}
			} else {
				$_SESSION['PlmMsg'][] = array('type' => 'error', 'msg' => '模板不存在！');
			}
			break;
		case 'template_off':
			$curStatus = '';
			$r2 = DB_query("SELECT status FROM doc_master WHERE doc_id=" . $docId, $db);
			if ($row2 = DB_fetch_array($r2)) { $curStatus = $row2['status']; }
			if ($curStatus != '正常') {
				$_SESSION['PlmMsg'][] = array('type' => 'error', 'msg' => '只有「正常」状态的模板才能删除！');
			} else {
				DB_query("UPDATE doc_master SET status='已删除', last_update_by='" . $_SESSION['UserID'] . "', last_update_date='" . $time . "' WHERE doc_id=" . $docId, $db);
				PlmLog($docId, '删除模板', '');
				$_SESSION['PlmMsg'][] = array('type' => 'success', 'msg' => '模板已删除！');
			}
			break;
		case 'template_set':
			DB_query("UPDATE doc_master SET is_template='Y', last_update_by='" . $_SESSION['UserID'] . "', last_update_date='" . $time . "' WHERE doc_id=" . $docId, $db);
			PlmLog($docId, '设为模板', '');
			$_SESSION['PlmMsg'][] = array('type' => 'success', 'msg' => '已设为文档模板！');
			break;
		case 'restore':
			/* 回收站还原：已删除 → 正常（回到工作区可见） */
			DB_query("UPDATE doc_master SET status='正常', last_update_by='" . $_SESSION['UserID'] . "', last_update_date='" . $time . "' WHERE doc_id=" . $docId, $db);
			PlmLog($docId, '从回收站还原', '');
			$_SESSION['PlmMsg'][] = array('type' => 'success', 'msg' => '文档已从回收站还原！');
			break;
		case 'purge':
			/* 彻底删除：物理删除（磁盘文件 + doc_file + doc_log + doc_master），不可恢复 */
			$rf = DB_query("SELECT file_patch FROM doc_file WHERE doc_id=" . $docId, $db);
			while ($row = DB_fetch_array($rf)) {
				if ($row['file_patch'] && file_exists($row['file_patch'])) { @unlink($row['file_patch']); }
			}
			DB_query("DELETE FROM doc_file WHERE doc_id=" . $docId, $db);
			DB_query("DELETE FROM doc_log WHERE doc_id=" . $docId, $db);
			DB_query("DELETE FROM doc_master WHERE doc_id=" . $docId, $db);
			$_SESSION['PlmMsg'][] = array('type' => 'success', 'msg' => '文档已彻底删除！');
			break;
		}
		} /* end else: 实际执行 */
		if ($errMsg != '') {
			$failCount++;
		} else {
			$successCount++;
		}
	}
	/* 批量操作结果汇总 */
	if ($failCount > 0 && $successCount > 0) {
		$_SESSION['PlmMsg'][] = array('type' => 'warning', 'msg' => '批量操作完成：成功 ' . $successCount . ' 个，失败 ' . $failCount . ' 个（部分文档状态不符合条件）');
	} elseif ($successCount > 1) {
		$_SESSION['PlmMsg'][] = array('type' => 'success', 'msg' => '已批量处理 ' . $successCount . ' 个文档！');
	}
	/* 单条时保留原有提示，不覆盖 */
	$r = DB_query("SELECT folder_id FROM doc_master WHERE doc_id=" . $docId, $db);
	$backFolder = $CurFolder;
	if ($row = DB_fetch_array($r)) { $backFolder = $row['folder_id']; }
	/* 模板视图操作后跳回模板页 */
	if (in_array($action, array('template_new', 'template_off', 'template_set'))) {
		header('Location: ' . $RootPath . '/DocPLM.php?mode=template');
		exit;
	}
	/* 废止区/回收站操作后跳回对应页面（由前端表单 return_mode 传入） */
	$retMode = isset($_POST['return_mode']) ? trim($_POST['return_mode']) : '';
	if ($retMode == 'abolition') {
		header('Location: ' . $RootPath . '/DocPLM.php?mode=abolition');
		exit;
	}
	if ($retMode == 'recycle') {
		header('Location: ' . $RootPath . '/DocPLM.php?mode=recycle');
		exit;
	}
	header('Location: ' . $RootPath . '/DocPLM.php?folder=' . $backFolder);
	exit;
}

/* ================= 编码建议 ================= */
function PlmGenCode() {
	global $db;
	$r = DB_query("SELECT COUNT(*) c FROM doc_master", $db);
	$row = DB_fetch_row($r);
	return 'DOC-' . date('Ymd') . '-' . sprintf('%04d', $row[0] + 1);
}

/* ================= 查询数据 ================= */
/* 目录树 */
$folders = array();
$r = DB_query("SELECT folder_id, parent_id, folder_name, folder_code, folder_type FROM doc_folder WHERE disable_flag<>'Y' ORDER BY parent_id, sort_order, folder_id", $db);
while ($row = DB_fetch_array($r)) { $folders[] = $row; }

/* 当前目录 + 搜索 + 文档列表（复用 PlmQuery） */
list($CurFolderName, $SearchName, $SearchCode, $SearchExt, $HideArch, $SearchItem, $docs) = PlmQuery($db, $CurFolder);

/* 回收站：左侧为时间分类树，$CurFolderName 改为时间范围标签 */
if ($isRecycle) {
	$recLabels = array('all' => '所有文件', 'today' => '今日删除', 'yesterday' => '昨日删除', 'thisweek' => '本周删除', 'lastweek' => '上周删除', 'thismonth' => '本月删除', 'lastyear' => '去年删除');
	$CurFolderName = isset($recLabels[$recTimeRange]) ? $recLabels[$recTimeRange] : '所有文件';
}

/* 当前目录子目录数（用于左侧计数） */
$subCount = array();
$r = DB_query("SELECT parent_id, COUNT(*) c FROM doc_folder WHERE disable_flag<>'Y' GROUP BY parent_id", $db);
while ($row = DB_fetch_array($r)) { $subCount[$row['parent_id']] = $row['c']; }
$docCount = array();
/* 文档数：按当前页面模式过滤（文档工作区只看非模板；模板页只看模板），数字才和右侧实际可见一致 */
$dCountWhere = "m.status<>'已删除'";
if ($isTemplateMode) { $dCountWhere .= " AND m.is_template='Y'"; }
else { $dCountWhere .= " AND m.is_template<>'Y'"; }
$r = DB_query("SELECT m.folder_id, COUNT(*) c FROM doc_master m WHERE " . $dCountWhere . " GROUP BY m.folder_id", $db);
while ($row = DB_fetch_array($r)) { $docCount[$row['folder_id']] = $row['c']; }
?>
<style type="text/css">
/* ====== 布局（复用 BOMSetup 规范） ====== */
.plm-layout{display:flex;width:100%;min-height:520px;gap:8px;margin-top:6px}
.plm-left{width:330px;border:1px solid #c5c5c5;background:#f7f9fc;border-radius:4px;padding:12px;display:flex;flex-direction:column}
.plm-right{flex:1;border:1px solid #c5c5c5;border-radius:4px;padding:12px;background:#fff;min-width:0;display:flex;flex-direction:column}
.plm-left-body{flex:1;overflow:auto;padding-bottom:4px}
.plm-left-head{display:flex;justify-content:space-between;align-items:center;padding-bottom:10px;margin-bottom:10px;border-bottom:1px solid #d9d9d9;font-size:14px;font-weight:bold;color:#333}
.plm-left-head .la{display:flex;gap:6px}
.plm-left-head .la button{background:#fff;color:#555;border:1px solid #c5c5c5;padding:3px 9px;border-radius:3px;cursor:pointer;font-size:12px;font-weight:normal}
.plm-left-head .la button:hover{background:#f0f0f0}
.plm-left-head .la button.primary{background:#4a90e2;color:#fff;border-color:#4a90e2}
.plm-left-head .la button.primary:hover{background:#357abd}
/* BOM 风格树 */
.bom-tree,.bom-tree ul{list-style:none;margin:0;padding:0}
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
/* 右键菜单（仿 BOMSetup #ctxMenu） */
#plmCtxMenu{position:absolute;display:none;background:#fff;border:1px solid #999;box-shadow:2px 2px 6px rgba(0,0,0,.2);z-index:9999;font-size:13px;border-radius:3px;overflow:hidden;min-width:140px}
#plmCtxMenu div{padding:6px 18px;cursor:pointer;white-space:nowrap}
#plmCtxMenu div:hover{background:#e8f0fe}
#plmCtxMenu .ctx-head{padding:6px 18px;font-weight:bold;border-bottom:1px solid #eee;background:#f5f9ff;cursor:default;color:#333}
#plmCtxMenu .ctx-del{color:#c62828}
#plmCtxMenu .ctx-del:hover{background:#fce4ec;color:#c62828}
/* 图片预览灯箱 */
.plm-img-modal{position:fixed;z-index:9998;left:0;top:0;width:100%;height:100%;background-color:rgba(0,0,0,0.9);text-align:center;padding-top:40px}
.plm-img-modal img{max-width:90%;max-height:85%;margin:auto;border-radius:6px}
.plm-img-close{position:absolute;top:10px;right:30px;color:#fff;font-size:40px;cursor:pointer}
/* 搜索 + 工具栏 */
.plm-search{display:flex;gap:8px;margin-bottom:10px;align-items:center;flex-wrap:wrap}
.plm-search input[type=text]{padding:5px 8px;border:1px solid #c8ced6;border-radius:3px;font-size:12px}
.plm-search input[type=text]:focus{outline:none;border-color:#2196F3}
.plm-search label{font-size:12px;color:#555}
.plm-toolbar{display:flex;align-items:center;gap:8px;margin-bottom:10px;padding:8px 10px;background:#f5f5f5;border:1px solid #e0e0e0;border-radius:3px;flex-wrap:wrap}
.plm-toolbar .path-tag{display:inline-flex;align-items:center;gap:4px;background:#fff;border:1px solid #c5c5c5;border-radius:3px;padding:4px 10px;font-size:12px;color:#555;margin-right:auto;max-width:45%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.plm-toolbar button{background:#fff;color:#555;border:1px solid #c5c5c5;padding:5px 14px;border-radius:3px;cursor:pointer;font-size:12px;font-weight:500}
.plm-toolbar button:hover{background:#e3f2fd;border-color:#2196F3;color:#1976D2}
.plm-toolbar button.btn-import{background:#4caf50;color:#fff;border-color:#4caf50}
.plm-toolbar button.btn-import:hover{background:#43a047;color:#fff}
.plm-toolbar button.btn-del{color:#c62828;border-color:#ef9a9a}
.plm-toolbar button.btn-del:hover{background:#c62828;color:#fff;border-color:#c62828}
.plm-toolbar button.btn-arch{color:#f57c00;border-color:#ffcc80}
.plm-toolbar button.btn-arch:hover{background:#f57c00;color:#fff;border-color:#f57c00}
.plm-toolbar .file-count{font-size:12px;color:#666}
/* 导入弹窗（持续选择 + 一起上传） */
.plm-import-modal{position:fixed;z-index:9000;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,0.5);display:flex;align-items:center;justify-content:center}
.plm-import-box{width:780px;max-width:96vw;height:520px;background:#fff;border-radius:8px;box-shadow:0 8px 32px rgba(0,0,0,0.3);display:flex;flex-direction:column;position:relative}
.plm-import-title{padding:12px 18px;border-bottom:1px solid #e0e0e0;display:flex;align-items:center;background:#f5f9fc;border-radius:8px 8px 0 0;font-weight:bold;font-size:14px;color:#1976D2}
.plm-import-close{position:absolute;right:18px;top:10px;font-size:24px;color:#888;cursor:pointer;line-height:1}
.plm-import-close:hover{color:#c62828}
.plm-import-body{flex:1;overflow:auto;padding:0}
.plm-import-table{width:100%;border-collapse:separate;border-spacing:0}
.plm-import-table th{position:sticky;top:0;background:#2196F3;color:#fff;font-size:12px;padding:8px 10px;text-align:left;border-right:1px solid #1e88e5}
.plm-import-table th:last-child{border-right:none}
.plm-import-table td{padding:8px 10px;font-size:12px;border-bottom:1px solid #eee;vertical-align:middle}
.plm-import-table td.left{text-align:left}
.plm-import-table .plm-row-ok{color:#2e7d32;font-weight:500}
.plm-import-table .plm-row-fail{color:#c62828;font-weight:500}
.plm-import-table .plm-row-doing{color:#1976D2}
.plm-import-table a{color:#c62828;text-decoration:none;font-size:12px}
.plm-import-table a:hover{text-decoration:underline}
.plm-import-foot{padding:10px 16px;border-top:1px solid #e0e0e0;display:flex;align-items:center;background:#fafbfc;border-radius:0 0 8px 8px}
/* 表格 */
.plm-table-wrap{flex:1;overflow:auto;border:1px solid #e0e0e0;border-radius:3px}
.plm-table{width:auto;min-width:100%;border-collapse:separate;border-spacing:0;table-layout:auto}
.plm-table th{position:sticky;top:0;z-index:2;background:#2196F3;color:#fff;font-weight:bold;padding:7px 10px;text-align:center;white-space:nowrap;border-bottom:2px solid #1e88e5;border-right:1px solid #1e88e5;font-size:13px}
.plm-table td{padding:7px 10px;text-align:center;font-size:12px;white-space:nowrap;vertical-align:middle;border-right:1px solid #e0e0e0;border-bottom:1px solid #e0e0e0;line-height:1.6}
.plm-table tr:hover td{background:#f1f8ff}
.plm-table td.left{text-align:left}
.plm-table .op a{margin:0 3px;color:#1976D2;text-decoration:none;font-size:12px}
.plm-table .op a:hover{text-decoration:underline}
.plm-table .op a.del{color:#c62828}
.plm-tag{display:inline-block;padding:1px 8px;font-size:11px;border-radius:3px;background:#e3f2fd;color:#0d47a1;border:1px solid #bbdefb}
.plm-tag.st-ok{background:#e8f5e9;color:#2e7d32;border-color:#c8e6c9}
.plm-tag.st-arch{background:#fff8e1;color:#f57c00;border-color:#ffe0b2}
.plm-tag.st-abd{background:#fce4ec;color:#c62828;border-color:#f8bbd0}
.plm-tag.st-del{background:#f5f5f5;color:#888;border-color:#e0e0e0}
.plm-empty{padding:40px;text-align:center;color:#888;font-size:13px}
/* 弹窗 */
.plm-modal{position:fixed;z-index:9000;left:0;top:0;width:100%;height:100%;background-color:rgba(0,0,0,0.5);display:flex;align-items:center;justify-content:center}
.plm-modal-box{width:480px;margin:0 auto;background:#fff;border-radius:8px;border:1px solid #d5d5d5;box-shadow:0 4px 16px rgba(0,0,0,0.25);max-height:85vh;display:flex;flex-direction:column}
.plm-modal-title{background:#f0f0f0;padding:10px 16px;font-weight:bold;font-size:14px;border-bottom:1px solid #d5d5d5;border-radius:8px 8px 0 0;cursor:move;user-select:none;position:relative}
.plm-modal-body{padding:16px;font-size:13px;overflow:auto;flex:1}
.plm-modal-foot{padding:10px 16px;text-align:right;border-top:1px solid #e5e5e5;background:#fafbfc;border-radius:0 0 8px 8px;flex-shrink:0}
.plm-modal-foot input[type=button]{padding:5px 16px;margin-left:10px;cursor:pointer;border:1px solid #aaa;background:#fff;border-radius:4px}
/* 保证 lhgdialog 弹窗（选物料/快速添加物料等）在 DocPLM 所有层之上 */
.plm-form td{padding:5px 6px;font-size:13px}
.plm-form td.lb{color:#5a6675;width:100px;text-align:right}
.plm-form input[type=text],.plm-form select,.plm-form textarea{padding:5px 8px;border:1px solid #c8ced6;border-radius:3px;font-size:12px;width:100%;box-sizing:border-box}
.plm-form textarea{height:60px}
.plm-form input[type=text]:focus,.plm-form select:focus,.plm-form textarea:focus{outline:none;border-color:#2196F3}
</style>

<div class="plm-layout">

	<!-- ========== 左侧目录树（模板模式也复用同一棵树） ========== -->
	<div class="plm-left">
		<div class="plm-left-head">
			<span><?php
				if ($isAbolition) echo '🚫 文件废止区';
				elseif ($isRecycle) echo '🗑 文件回收站';
				elseif ($isTemplateMode) echo '📄 文档模板';
				else echo '📁 文档目录';
			?></span>
			<?php if (!$isTemplateMode && !$isAbolition && !$isRecycle) { ?>
			<div class="la">
				<button type="button" class="primary" onclick="PlmNewFolder(0)" title="新建顶级目录">+ 新建主目录</button>
				<button type="button" onclick="PlmExpandAll(true)">展开</button>
				<button type="button" onclick="PlmExpandAll(false)">折叠</button>
			</div>
			<?php } elseif ($isTemplateMode) { ?>
			<span style="font-size:12px;font-weight:normal;color:#666;margin-left:8px;">（共 <b style="color:#1976D2;"><?php echo count($tmpls); ?></b> 个模板）</span>
			<?php } ?>
		</div>
		<div class="plm-left-body">
			<ul class="bom-tree" id="plmTree">
			<?php
		/* ===== 虚拟"物料目录"根（仅文档工作区显示；模板/废止区/回收站均不显示） ===== */
		if (!$isTemplateMode && !$isAbolition && !$isRecycle) {
			$itemCats = array();
			$r = DB_query("SELECT DISTINCT item_category1 FROM sf_item_no WHERE item_category1<>'' AND able_flag='Y' ORDER BY item_category1", $db);
			while ($row = DB_fetch_array($r)) { $itemCats[] = $row['item_category1']; }
			$isItemRoot = ($CurFolder == -1 || isset($_GET['cat']));
			echo '<li class="bom-node' . ($isItemRoot ? ' active' : '') . '" data-folder="-1" data-type="itemroot">';
			echo '<div class="bom-row" onclick="PlmGoItemRoot()">';
			echo '<span class="bom-glyphs"><span class="tree-cell node-cell"><span class="tw">' . ($isItemRoot ? '−' : '+') . '</span></span></span>';
			echo '<span class="icon-label">' . PlmIconFolder() . '<span class="lbl"><b>📦 物料目录</b></span></span></div>';
			echo '<ul class="bom-sub">';
			foreach ($itemCats as $cat) {
				$isCat = (isset($_GET['cat']) && $_GET['cat'] == $cat);
				echo '<li class="bom-node' . ($isCat ? ' active' : '') . '" data-cat="' . htmlspecialchars($cat) . '">';
				echo '<div class="bom-row" onclick="PlmGoCat(\'' . htmlspecialchars($cat, ENT_QUOTES) . '\')">';
				/* 分类在物料目录根的 ul 内（1 级缩进）+ 自带 tw 占位 */
				echo '<span class="bom-glyphs">';
				echo '<span class="tree-cell indent-cell no-sibling"><span class="tree-vbar"></span></span>';
				echo '<span class="tree-cell node-cell"><span class="tw">+</span></span>';
				echo '</span>';
				echo '<span class="icon-label">' . PlmIconFolder() . '<span class="lbl">' . htmlspecialchars($cat) . '</span></span></div>';
				echo '<ul class="bom-sub" style="display:none"></ul>';
				echo '</li>';
			}
			echo '</ul></li>';
			} /* 模板模式不显示物料目录 */
			/* ===== 原有手工目录树 ===== */
			/* 递归渲染目录树 */
			function PlmRenderFolders($folders, $parentId, $level, $CurFolder, $subCount, $docCount) {
				$children = array();
				foreach ($folders as $f) {
					if ($f['parent_id'] == $parentId) { $children[] = $f; }
				}
				$total = count($children);
				$idx = 0;
				foreach ($children as $f) {
					$idx++;
					$isLast = ($idx == $total);
					$hasChild = isset($subCount[$f['folder_id']]) && $subCount[$f['folder_id']] > 0;
					$isActive = ($f['folder_id'] == $CurFolder);
					$dcount = isset($docCount[$f['folder_id']]) ? $docCount[$f['folder_id']] : 0;
					$label = $f['folder_name'] . ($dcount > 0 ? ' (' . $dcount . ')' : '');
					echo '<li class="bom-node' . ($hasChild ? '' : ' leaf-node') . ($isActive ? ' active' : '') . '" data-folder="' . $f['folder_id'] . '">';
					echo '<div class="bom-row" onclick="PlmGoFolder(' . $f['folder_id'] . ')">';
					echo '<span class="bom-glyphs">';
					for ($i = 0; $i < $level - 1; $i++) {
						echo '<span class="tree-cell indent-cell no-sibling"><span class="tree-vbar"></span></span>';
					}
					if ($level > 0) {
						echo '<span class="tree-cell indent-cell' . ($isLast ? ' no-sibling' : '') . '"><span class="tree-vbar"></span></span>';
					}
					echo '<span class="tree-cell node-cell">';
					if ($level > 0) { echo '<span class="tree-hbar"></span>'; }
					if ($hasChild) { echo '<span class="tw">+</span>'; }
					echo '</span>';
					echo '</span>';
echo '<span class="icon-label">' . PlmIconFolder() . '<span class="lbl">' . htmlspecialchars($label) . '</span></span>';
				echo '</div>';
					if ($hasChild) {
						echo '<ul class="bom-sub" style="display:none">';
						PlmRenderFolders($folders, $f['folder_id'], $level + 1, $CurFolder, $subCount, $docCount);
						echo '</ul>';
					}
					echo '</li>';
				}
			}
			if ($isRecycle) {
				/* 回收站：按删除时间分类的目录树 */
				$trItems = array(
					'all'       => '所有文件',
					'today'     => '今日删除',
					'yesterday' => '昨日删除',
					'thisweek'  => '本周删除',
					'lastweek'  => '上周删除',
					'thismonth' => '本月删除',
					'lastyear'  => '去年删除',
				);
				$curTr = isset($_GET['tr']) ? $_GET['tr'] : 'all';
				foreach ($trItems as $trk => $trl) {
					$trActive = ($curTr == $trk) ? ' active' : '';
					echo '<li class="bom-node' . $trActive . '" data-tr="' . htmlspecialchars($trk) . '" onclick="PlmGoRecycle(\'' . htmlspecialchars($trk) . '\')">';
					echo '<div class="bom-row"><span class="bom-glyphs"><span class="tree-cell node-cell"><span class="tw">•</span></span></span>';
					echo '<span class="icon-label">' . PlmIconFolder() . '<span class="lbl">' . htmlspecialchars($trl) . '</span></span></div></li>';
				}
			} else {
				PlmRenderFolders($folders, 0, 0, $CurFolder, $subCount, $docCount);
			} ?>
			</ul>
		</div>
	</div>

	<!-- ========== 右侧主区（AJAX 局部刷新） ========== -->
	<div class="plm-right" id="plmRight">
		<?php
		if ($isAbolition) {
			PlmRenderAbolition($db, $CurFolder, $CurFolderName, $abdocs, $SearchName, $SearchCode);
		} elseif ($isRecycle) {
			PlmRenderRecycle($db, $CurFolder, $CurFolderName, $recdocs, $SearchName, $SearchCode);
		} else {
			PlmRenderRight($db, $CurFolder, $CurFolderName, $docs, $SearchName, $SearchCode, $SearchExt, $HideArch, $SearchItem, $isTemplateMode);
		}
		?>
	</div>

<!-- 新建目录弹窗 -->
<div id="plmModalFolder" class="plm-modal" style="display:none;">
	<div class="plm-modal-box" style="width:400px;">
		<div class="plm-modal-title">新建目录</div>
		<form method="POST" action="<?php echo $RootPath; ?>/DocPLM.php">
			<input type="hidden" name="FormID" value="<?php echo $_SESSION['FormID']; ?>" />
			<input type="hidden" name="folder_save" value="1" />
			<input type="hidden" name="folder_parent" id="plmFolderParent" value="0" />
			<div class="plm-modal-body">
				<table class="plm-form">
					<tr><td class="lb">父目录：</td><td><input type="text" id="plmFolderParentName" readonly value="主目录" style="background:#f5f5f5;" /></td></tr>
					<tr><td class="lb">目录名称：</td><td><input type="text" name="folder_name" required placeholder="如：WHTEST 项目 / 20260804" /></td></tr>
				</table>
			</div>
			<div class="plm-modal-foot">
				<input type="button" value="取消" onclick="document.getElementById('plmModalFolder').style.display='none';" />
				<input type="submit" value="创建" style="background:#4a90e2;color:#fff;border:none;padding:5px 18px;border-radius:4px;cursor:pointer;" />
			</div>
		</form>
	</div>
</div>

<!-- 重命名目录弹窗 -->
<div id="plmModalRename" class="plm-modal" style="display:none;">
	<div class="plm-modal-box" style="width:400px;">
		<div class="plm-modal-title">重命名目录</div>
		<form method="POST" action="<?php echo $RootPath; ?>/DocPLM.php">
			<input type="hidden" name="FormID" value="<?php echo $_SESSION['FormID']; ?>" />
			<input type="hidden" name="folder_rename" value="1" />
			<input type="hidden" name="folder_id" id="plmRenameId" value="0" />
			<input type="hidden" name="folder_parent" id="plmRenameParent" value="0" />
			<div class="plm-modal-body">
				<table class="plm-form">
					<tr><td class="lb">目录名称：</td><td><input type="text" name="folder_name" id="plmRenameName" required /></td></tr>
				</table>
			</div>
			<div class="plm-modal-foot">
				<input type="button" value="取消" onclick="document.getElementById('plmModalRename').style.display='none';" />
				<input type="submit" value="保存" style="background:#4a90e2;color:#fff;border:none;padding:5px 18px;border-radius:4px;cursor:pointer;" />
			</div>
		</form>
	</div>
</div>

<!-- 文档属性弹窗 -->
<div id="plmModalProps" class="plm-modal" style="display:none;">
	<div class="plm-modal-box" style="width:520px;">
		<div class="plm-modal-title">文档属性</div>
		<form method="POST" action="<?php echo $RootPath; ?>/DocPLM.php">
			<input type="hidden" name="FormID" value="<?php echo $_SESSION['FormID']; ?>" />
			<input type="hidden" name="doc_action" value="props" />
			<input type="hidden" name="doc_id" id="plmPropsId" value="0" />
			<div class="plm-modal-body" id="plmPropsBody">加载中…</div>
			<div class="plm-modal-foot">
				<input type="button" value="取消" onclick="document.getElementById('plmModalProps').style.display='none';" />
				<input type="submit" value="保存" style="background:#4a90e2;color:#fff;border:none;padding:5px 18px;border-radius:4px;cursor:pointer;" />
			</div>
		</form>
	</div>
</div>

<!-- 历史版本弹窗 -->
<div id="plmModalVersions" class="plm-modal" style="display:none;">
	<div class="plm-modal-box" style="width:620px;">
		<div class="plm-modal-title">历史版本</div>
		<div class="plm-modal-body" id="plmVersionsBody">加载中…</div>
		<div class="plm-modal-foot">
			<input type="button" value="关闭" onclick="document.getElementById('plmModalVersions').style.display='none';" />
		</div>
	</div>
</div>

<!-- 操作日志弹窗 -->
<div id="plmModalLog" class="plm-modal" style="display:none;">
	<div class="plm-modal-box" style="width:640px;max-height:80vh;">
		<div class="plm-modal-title">操作日志</div>
		<div class="plm-modal-body" id="plmLogBody">加载中…</div>
		<div class="plm-modal-foot">
			<input type="button" value="关闭" onclick="document.getElementById('plmModalLog').style.display='none';" />
		</div>
	</div>
</div>

<!-- 升版弹窗 -->
<div id="plmModalUpgrade" class="plm-modal" style="display:none;">
	<div class="plm-modal-box" style="width:480px;">
		<div class="plm-modal-title">升版</div>
		<form method="POST" action="<?php echo $RootPath; ?>/DocPLM.php" enctype="multipart/form-data">
			<input type="hidden" name="FormID" value="<?php echo $_SESSION['FormID']; ?>" />
			<input type="hidden" name="doc_action" value="upgrade" />
			<input type="hidden" name="doc_id" id="plmUpgradeId" value="0" />
			<div class="plm-modal-body">
				<table class="plm-form">
					<tr><td class="lb">选择新文件：</td><td><input type="file" name="Pic[]" required style="border:none;" /></td></tr>
					<tr><td class="lb">版本备注：</td><td><textarea name="upgrade_remark" placeholder="本次升版说明…"></textarea></td></tr>
				</table>
			</div>
			<div class="plm-modal-foot">
				<input type="button" value="取消" onclick="document.getElementById('plmModalUpgrade').style.display='none';" />
				<input type="submit" value="确认升版" style="background:#4a90e2;color:#fff;border:none;padding:5px 18px;border-radius:4px;cursor:pointer;" />
			</div>
		</form>
	</div>
</div>

<!-- 右键菜单 -->
<div id="plmCtxMenu">
	<div class="ctx-head" id="plmCtxHead">目录操作</div>
	<div onclick="PlmCtxNew()">📁 新建子目录</div>
	<div onclick="PlmCtxRename()">✏ 重命名</div>
	<div class="ctx-del" onclick="PlmCtxDelete()">🗑 删除</div>
</div>

<!-- 文件导入弹窗（持续选择 + 一起上传） -->
<div id="plmImportModal" class="plm-import-modal" style="display:none;">
	<div class="plm-import-box">
		<div class="plm-import-title">
			<span>📁 文件导入</span>
			<span style="font-size:12px;color:#888;margin-left:12px;">目标目录：<b id="plmImportTarget">主目录</b></span>
			<span class="plm-import-close" onclick="PlmCloseImport()" title="关闭">&times;</span>
		</div>
		<div class="plm-import-body">
			<table class="plm-import-table">
				<thead>
					<tr>
						<th>文件名</th>
						<th width="100">大小</th>
						<th width="80">状态</th>
						<th>提示信息</th>
						<th width="80">文件类型</th>
						<th width="120">关联物料</th>
						<th width="60">操作</th>
					</tr>
				</thead>
				<tbody id="plmImportList">
					<tr id="plmImportEmpty"><td colspan="6" style="padding:40px 20px;text-align:center;color:#999;font-size:13px;">📂 暂未选择文件，点击下方"选择文件"按钮（支持多次选择）</td></tr>
				</tbody>
			</table>
		</div>
		<div class="plm-import-foot">
			<input type="file" id="plmFilesInput" multiple style="display:none;" onchange="PlmAddFiles(this.files)" />
			<label style="font-size:12px;color:#666;cursor:pointer;"><input type="checkbox" id="plmAsTemplate" style="vertical-align:-2px;" /> 导入为模板（新建文档时可复制）</label>
			<button type="button" onclick="document.getElementById('plmFilesInput').click()" style="background:#2196F3;color:#fff;border:none;padding:7px 18px;border-radius:3px;cursor:pointer;font-size:13px;font-weight:500;">📂 选择文件</button>
			<button type="button" id="plmStartUploadBtn" onclick="PlmStartUpload()" style="background:#4caf50;color:#fff;border:none;padding:7px 22px;border-radius:3px;cursor:pointer;font-size:13px;font-weight:500;margin-left:8px;">⇧ 开始上传</button>
			<span style="margin-left:auto;color:#888;font-size:12px;" id="plmImportStat">待上传：0 个 / 累计：0 个</span>
			<button type="button" onclick="PlmCloseImport()" style="background:#f5f5f5;color:#555;border:1px solid #ccc;padding:7px 14px;border-radius:3px;cursor:pointer;font-size:13px;margin-left:8px;">取消</button>
		</div>
	</div>
</div>

<!-- 图片预览灯箱 -->
<div id="plmImgModal" class="plm-img-modal" style="display:none;" onclick="this.style.display='none';">
	<img id="plmImgContent" src="" alt="预览" />
	<span class="plm-img-close" onclick="document.getElementById('plmImgModal').style.display='none';">&times;</span>
</div>

<?php include('includes/footer.inc'); ?>

<script type="text/javascript">
var _RootPath = '<?php echo $RootPath; ?>';
var _CurFolder = <?php echo $CurFolder; ?>;
/* 树 */
	function PlmGoFolder(fid, clearSearch) {
		var url = _RootPath + '/DocPLM.php?folder=' + fid + '&ajax=1';
		if (location.search.indexOf('tpl=1') >= 0 || location.search.indexOf('mode=template') >= 0) { url += '&tpl=1'; }
		if (location.search.indexOf('mode=abolition') >= 0) { url += '&mode=abolition'; }
		if (location.search.indexOf('mode=recycle') >= 0) { url += '&mode=recycle'; }
	if (!clearSearch) {
		var q = document.getElementById('plmQ');
		var code = document.getElementById('plmCode');
		var ext = document.getElementById('plmExt');
		var hide = document.getElementById('plmHideArch');
		var item = document.getElementById('plmItem');
		if (q && q.value) url += '&q=' + encodeURIComponent(q.value);
		if (code && code.value) url += '&code=' + encodeURIComponent(code.value);
		if (ext && ext.value) url += '&ext=' + encodeURIComponent(ext.value);
		if (hide && hide.checked) url += '&hidearch=1';
		if (item && item.value) url += '&item=' + encodeURIComponent(item.value);
	}
	url += '&_r=' + Date.now();
	PlmAjaxLoad(url, fid);
}
/* AJAX 局部刷新右侧面板 */
function PlmAjaxLoad(url, folderId, skipHighlight) {
	var right = document.getElementById('plmRight');
	if (!right) { window.location.href = _RootPath + '/DocPLM.php?folder=' + folderId; return; }
	right.style.opacity = '0.4';
	right.style.transition = 'opacity 0.15s';
	var x = new XMLHttpRequest();
	x.open('GET', url, true);
	x.onreadystatechange = function() {
		if (x.readyState === 4 && x.status === 200) {
			right.innerHTML = x.responseText;
			right.style.opacity = '1';
			var _modeParam = '';
			if (location.search.indexOf('mode=abolition') >= 0) _modeParam = '&mode=abolition';
			else if (location.search.indexOf('mode=recycle') >= 0) _modeParam = '&mode=recycle';
			else if (location.search.indexOf('mode=template') >= 0) _modeParam = '&mode=template';
			else if (url.indexOf('tpl=1') >= 0) _modeParam = '&tpl=1';
			history.replaceState(null, '', _RootPath + '/DocPLM.php?folder=' + folderId + _modeParam);
			if (!skipHighlight) {
				document.querySelectorAll('#plmTree li.bom-node').forEach(function(li) {
					li.classList.remove('active');
				});
				var sel = document.querySelector('#plmTree li.bom-node[data-folder="' + folderId + '"]');
				if (sel) sel.classList.add('active');
			}
		}
	};
	x.send();
}
/* 搜索表单（AJAX 提交） */
function PlmSearchSubmit() {
	var params = new URLSearchParams(location.search);
	if (params.get('mode') === 'recycle') { PlmGoRecycle(params.get('tr') || 'all'); return false; }
	var fid = document.querySelector('#plmSearchForm input[name="folder"]').value || 1;
	PlmGoFolder(fid);
	return false;
}
function PlmExpandAll(open) {
	var uls = document.querySelectorAll('#plmTree ul.bom-sub');
	var tws = document.querySelectorAll('#plmTree .tw');
	for (var i = 0; i < uls.length; i++) { uls[i].style.display = open ? '' : 'none'; }
	for (var j = 0; j < tws.length; j++) { tws[j].innerHTML = open ? '-' : '+'; }
	PlmPersistExpand();
}
/* 折叠符点击切换（分类节点首次展开时 AJAX 加载物料） */
function PlmToggleNode(tw) {
	var li = tw.closest('li.bom-node');
	var ul = li && li.querySelector('ul.bom-sub');
	if (!ul) return;
	var open = ul.style.display === 'none';
	var cat = li.getAttribute('data-cat');
	if (cat && open && ul.children.length === 0) {
		/* 分类节点：AJAX 加载该分类的物料列表 */
		var x = new XMLHttpRequest();
		x.open('GET', _RootPath + '/DocPLM.php?op=item_cat_nodes&cat=' + encodeURIComponent(cat) + '&_r=' + Date.now(), true);
		x.onreadystatechange = function() {
			if (x.readyState === 4 && x.status === 200) {
				ul.innerHTML = x.responseText;
				ul.style.display = '';
				tw.innerHTML = '-';
				PlmPersistExpand();
			}
		};
		x.send();
		return;
	}
	ul.style.display = open ? '' : 'none';
	tw.innerHTML = open ? '-' : '+';
	PlmPersistExpand();
}
/* 虚拟物料目录 */
function PlmGoItemRoot() {
	var url = _RootPath + '/DocPLM.php?ajax=1&folder=-1&_r=' + Date.now();
	PlmAjaxLoad(url, -1, true);
	PlmSetTreeActive(function(li) { return li.getAttribute('data-folder') === '-1'; });
}
function PlmGoCat(cat) {
	var url = _RootPath + '/DocPLM.php?cat=' + encodeURIComponent(cat) + '&_r=' + Date.now();
	PlmAjaxLoad(url, -1, true);
	PlmSetTreeActive(function(li) { return li.getAttribute('data-cat') === cat; });
}
function PlmGoItem(itemNo) {
	var url = _RootPath + '/DocPLM.php?ajax=1&folder=-1&item=' + encodeURIComponent(itemNo) + '&_r=' + Date.now();
	PlmAjaxLoad(url, -1, true);
	PlmSetTreeActive(function(li) { return li.getAttribute('data-item') === itemNo; });
}
function PlmSetTreeActive(matchFn) {
	document.querySelectorAll('#plmTree li.bom-node').forEach(function(li) {
		li.classList.remove('active');
		if (matchFn(li)) li.classList.add('active');
	});
}
/* 持久化展开状态（localStorage） */
function PlmPersistExpand() {
	var ids = [];
	document.querySelectorAll('#plmTree li.bom-node').forEach(function(li) {
		var ul = li.querySelector('ul.bom-sub');
		if (ul && ul.style.display !== 'none') {
			var fid = li.getAttribute('data-folder');
			if (fid) ids.push(fid);
		}
	});
	try { localStorage.setItem('plmExpand', ids.join(',')); } catch (e) {}
}
/* 加载时恢复展开状态 + 默认展开当前目录的祖先链 */
function PlmRestoreExpand() {
	var saved = '';
	try { saved = localStorage.getItem('plmExpand') || ''; } catch (e) {}
	if (saved) {
		var ids = saved.split(',');
		for (var i = 0; i < ids.length; i++) {
			var li = document.querySelector('#plmTree li.bom-node[data-folder="' + ids[i] + '"]');
			if (li) {
				var ul = li.querySelector('ul.bom-sub');
				if (ul) {
					ul.style.display = '';
					var tw = li.querySelector('.tw');
					if (tw) tw.innerHTML = '-';
				}
			}
		}
	}
	/* 默认展开当前选中目录的祖先链 */
	var active = document.querySelector('#plmTree li.bom-node.active');
	while (active) {
		var ul = active.querySelector('ul.bom-sub');
		if (ul) {
			ul.style.display = '';
			var tw = active.querySelector('.tw');
			if (tw) tw.innerHTML = '-';
		}
		active = active.parentElement && active.parentElement.closest('li.bom-node');
	}
}
/* 目录操作 */
function PlmNewFolder(parentId) {
	document.getElementById('plmFolderParent').value = parentId;
	document.getElementById('plmFolderParentName').value = parentId === 0 ? '顶级目录（主目录的同级）' : (parentId === 1 ? '主目录' : '目录 #' + parentId);
	document.getElementById('plmModalFolder').style.display = 'block';
}
function PlmRenameFolder(fid, parentId, name) {
	document.getElementById('plmRenameId').value = fid;
	document.getElementById('plmRenameParent').value = parentId;
	document.getElementById('plmRenameName').value = name;
	document.getElementById('plmModalRename').style.display = 'block';
}
function PlmDeleteFolder(fid, parentId) {
	if (confirm('确定删除该目录吗？\n目录下有子目录或文档时无法删除！')) {
		var f = document.createElement('form');
		f.method = 'POST'; f.action = _RootPath + '/DocPLM.php';
		f.innerHTML = '<input name="FormID" value="' + document.querySelector('input[name="FormID"]').value + '"><input name="folder_delete" value="1"><input name="folder_id" value="' + fid + '"><input name="folder_parent" value="' + parentId + '">';
		document.body.appendChild(f); f.submit();
	}
}
/* 导入弹窗（持续选择 + 一起上传） */
var _plmImportFiles = [];    /* [{fileObj, name, size, ext, status, msg}] */
var _plmImportDoneCnt = 0;
function PlmOpenImport() {
	_plmImportFiles = [];
	_plmImportDoneCnt = 0;
	renderImportList();
	/* 目标显示：优先物料模式（?item=xxx），否则目录模式 */
	var folderName = '';
	var itemNo = '';
	var itemName = '';
	var urlParams = new URLSearchParams(location.search);
	itemNo = urlParams.get('item') || '';
	if (itemNo) {
		/* 从当前行可读的关联物料单元格查找物料名称（已在 #plmRight 渲染过） */
		var match = null;
		document.querySelectorAll('#plmRight .plm-table tbody tr td:nth-child(10) a').forEach(function(a) {
			if (a.textContent.trim() === itemNo) {
				match = a.parentElement.innerText.trim();
			}
		});
		if (match) { itemName = match; }
	}
	var tg = document.getElementById('plmImportTarget');
	if (itemNo) {
		tg.innerHTML = '<span style="color:#1976D2;font-weight:bold;">物料</span> ' + escapeHtmlImpl(itemNo) +
			(itemName ? ' <span style="color:#999;font-size:11px;">' + escapeHtmlImpl(itemName.split(itemNo)[1] || '') + '</span>' : '');
	} else {
		var pathEl = document.querySelector('.path-tag');
		if (pathEl) folderName = pathEl.textContent.replace(/^📁\s*\/\s*/, '').trim();
		tg.textContent = folderName || ('目录 #' + _CurFolder);
	}
	/* 上下文感知：模板页导入 = 自动设为模板（勾选且禁用）；工作区导入 = 可选 */
	var asTpl = document.getElementById('plmAsTemplate');
	if (asTpl) {
		var inTplPage = location.search.indexOf('mode=template') >= 0;
		asTpl.checked = inTplPage;
		asTpl.disabled = inTplPage;
	}
	document.getElementById('plmImportModal').style.display = 'flex';
}
function PlmCloseImport() {
	document.getElementById('plmImportModal').style.display = 'none';
	_plmImportFiles = [];
}
var _AllowedExt = ['jpeg','jpg','png','gif','bmp','webp','tiff','svg','step','stp','igs','iges','dwg','dxf','sat','pdf','txt','md','csv','xml','json','log','rtf','odt','ods','odp','doc','docx','xls','xlsx','ppt','pptx','zip','rar','7z','tar','gz','mp4','mp3','wav'];
function PlmAddFiles(fileList) {
	/* 自动预填当前 ?item=xxx 的物料号（如果打开弹窗时 URL 带了物料参数） */
	var urlItem = '';
	try { urlItem = new URLSearchParams(location.search).get('item') || ''; } catch (e) {}
	for (var i = 0; i < fileList.length; i++) {
		var f = fileList[i];
		if (!f || f.size == 0) continue;
		var dot = f.name.lastIndexOf('.');
		var ext = dot >= 0 ? f.name.substring(dot + 1).toLowerCase() : '';
		var valid = _AllowedExt.indexOf(ext) >= 0;
		_plmImportFiles.push({
			fileObj: f,
			name: f.name,
			size: f.size,
			ext: ext,
			status: valid ? '等待' : '失败',
			msg: valid ? '' : '不支持的文件类型：' + (ext || '无后缀'),
			itemNo: urlItem
		});
	}
	document.getElementById('plmFilesInput').value = '';
	renderImportList();
}
function PlmRemoveImportFile(idx) {
	_plmImportFiles.splice(idx, 1);
	renderImportList();
}
function PlmClearImportList() {
	_plmImportFiles = [];
	_plmImportDoneCnt = 0;
	renderImportList();
}
function renderImportList() {
	var tbody = document.getElementById('plmImportList');
	/* ★ 重建前先把当前每行输入框的值存回数组（防止重建丢失用户输入） */
	if (tbody) {
		var oldInputs = tbody.querySelectorAll('.plm-imp-item');
		for (var oi = 0; oi < oldInputs.length; oi++) {
			var oidx = parseInt(oldInputs[oi].getAttribute('data-idx'), 10);
			if (!isNaN(oidx) && _plmImportFiles[oidx]) {
				_plmImportFiles[oidx].itemNo = oldInputs[oi].value.trim();
			}
		}
	}
	var html = '';
	if (_plmImportFiles.length === 0) {
		tbody.innerHTML = '<tr><td colspan="6" style="padding:40px 20px;text-align:center;color:#999;font-size:13px;">📂 暂未选择文件，点击下方"选择文件"按钮（支持多次选择）</td></tr>';
		updateImportStat(0, 0);
		return;
	}
	for (var i = 0; i < _plmImportFiles.length; i++) {
		var f = _plmImportFiles[i];
		var sz = f.size >= 1048576 ? (f.size / 1048576).toFixed(2) + ' MB' : Math.max(1, Math.round(f.size / 1024)) + ' KB';
		var stCls = f.status === '成功' ? 'plm-row-ok' : (f.status === '失败' ? 'plm-row-fail' : (f.status === '上传中' ? 'plm-row-doing' : ''));
		html += '<tr>' +
			'<td class="left" title="' + escapeHtmlImpl(f.name) + '" style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">' + escapeHtmlImpl(f.name) + '</td>' +
			'<td>' + sz + '</td>' +
			'<td class="' + stCls + '">' + escapeHtmlImpl(f.status) + '</td>' +
			'<td class="left">' + escapeHtmlImpl(f.msg || '') + '</td>' +
			'<td>' + (f.ext ? f.ext.toUpperCase() : '—') + '</td>' +
			'<td style="width:150px;"><div style="display:flex;gap:2px;"><input type="text" id="plm_imp_item_' + i + '" class="plm-imp-item" data-idx="' + i + '" placeholder="物料编码(可空)" value="' + escapeHtmlImpl(f.itemNo || '') + '" style="flex:1;width:auto;padding:3px 6px;border:1px solid #c8ced6;border-radius:3px;font-size:12px;" /><button type="button" onclick="PlmPickItem(\'plm_imp_item_' + i + '\')" style="padding:2px 6px;background:#1976D2;color:#fff;border:none;border-radius:3px;cursor:pointer;font-size:11px;">选</button></div></td>' +
			'<td><a href="javascript:void(0)" onclick="PlmRemoveImportFile(' + i + ')">移除</a></td>' +
			'</tr>';
	}
	tbody.innerHTML = html;
	updateImportStat(_plmImportFiles.length, _plmImportDoneCnt);
}
function updateImportStat(total, done) {
	var el = document.getElementById('plmImportStat');
	if (el) el.textContent = '待上传：' + (total - done) + ' 个 / 累计：' + total + ' 个';
}
function escapeHtmlImpl(s) {
	return String(s == null ? '' : s).replace(/[&<>"']/g, function(c) {
		return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
	});
}
function PlmStartUpload() {
	if (_plmImportFiles.length === 0) { alert('请先选择文件'); return; }
	var validList = [];
	for (var i = 0; i < _plmImportFiles.length; i++) {
		if (_plmImportFiles[i].status === '失败') continue;
		validList.push(i);
	}
	if (validList.length === 0) { alert('没有可上传的文件（都已失败）'); return; }
	/* ★ 先收集每行关联物料（必须在 renderImportList 重建表格之前！） */
	var itemInputs = document.querySelectorAll('.plm-imp-item');
	var itemMap = {};
	for (var ii = 0; ii < itemInputs.length; ii++) {
		itemMap[itemInputs[ii].getAttribute('data-idx')] = itemInputs[ii].value.trim();
	}
	document.getElementById('plmStartUploadBtn').disabled = true;
	for (var k = 0; k < validList.length; k++) {
		_plmImportFiles[validList[k]].status = '上传中';
		_plmImportFiles[validList[k]].msg = '上传中…';
	}
	renderImportList();
	var fd = new FormData();
	fd.append('FormID', document.querySelector('input[name="FormID"]').value);
	fd.append('doc_import', '1');
	fd.append('folder_id', _CurFolder);
	fd.append('as_template', document.getElementById('plmAsTemplate') && document.getElementById('plmAsTemplate').checked ? '1' : '');
	/* 上传后跳回原页面（保持模板/物料目录模式） */
	if (location.search.indexOf('mode=template') >= 0) { fd.append('redirect_to', 'mode=template'); }
	else if (location.search.indexOf('tpl=1') >= 0) { fd.append('redirect_to', 'tpl=1'); }
	else if (location.search.indexOf('folder=-1') >= 0) { fd.append('redirect_to', 'folder=-1'); }
	for (var k = 0; k < _plmImportFiles.length; k++) {
		fd.append('Pic[]', _plmImportFiles[k].fileObj);
		fd.append('file_name' + (k + 1), _plmImportFiles[k].name);
		fd.append('item_no_' + (k + 1), itemMap[k] || '');
	}
	var x = new XMLHttpRequest();
	x.open('POST', _RootPath + '/DocPLM.php', true);
	x.onreadystatechange = function() {
		if (x.readyState === 4) {
			document.getElementById('plmStartUploadBtn').disabled = false;
			if (x.status === 200) {
				/* 检查 PHP prnMsg 输出（精确匹配 class="success" 块里的"成功导入 N 个文档"） */
				var resp = x.responseText;
				var ok = /class="success"[^>]*>[\s\S]{0,200}?成功导入\s*\d+\s*个文档/.test(resp);
				for (var k = 0; k < _plmImportFiles.length; k++) {
					if (_plmImportFiles[k].status === '上传中') {
						if (ok) {
							_plmImportFiles[k].status = '成功';
							_plmImportFiles[k].msg = '✓ 上传成功！';
						} else {
							_plmImportFiles[k].status = '失败';
							_plmImportFiles[k].msg = '上传失败，请查看页面提示';
						}
					}
				}
				_plmImportDoneCnt = ok ? _plmImportFiles.length : _plmImportDoneCnt;
				renderImportList();
				if (ok) {
					setTimeout(function() {
						PlmCloseImport();
						PlmGoFolder(_CurFolder, true);
					}, 1200);
				}
			} else {
				for (var k = 0; k < _plmImportFiles.length; k++) {
					if (_plmImportFiles[k].status === '上传中') {
						_plmImportFiles[k].status = '失败';
						_plmImportFiles[k].msg = '网络错误 ' + x.status;
					}
				}
				renderImportList();
			}
		}
	};
	x.send(fd);
}
/* 选中行 */
function PlmSelectedDocIds() {
	var cs = document.querySelectorAll('.plm-row-check:checked');
	var ids = [];
	for (var i = 0; i < cs.length; i++) { ids.push(cs[i].value); }
	return ids;
}
function PlmSelectedDocId() {
	var ids = PlmSelectedDocIds();
	return ids.length > 0 ? ids[0] : '';
}
function PlmToggleAll(box) {
	var cs = document.querySelectorAll('.plm-row-check');
	for (var i = 0; i < cs.length; i++) { cs[i].checked = box.checked; }
}
/* 工具栏批量操作（支持多选） */
function PlmAction(act, mode) {
	var ids = PlmSelectedDocIds();
	if (ids.length === 0) { alert('请先勾选文档'); return; }
	var msgs = { archive: '确定归档选中的 ' + ids.length + ' 个文档吗？归档后只读留底', abandon: '确定废止选中的 ' + ids.length + ' 个文档吗？废止后禁止使用', delete: '确定删除选中的 ' + ids.length + ' 个文档吗？删除后文件不可恢复！' };
	if (!confirm(msgs[act] || '确定对选中的 ' + ids.length + ' 个文档执行该操作吗？')) return;
	var f = document.createElement('form');
	f.method = 'POST'; f.action = _RootPath + '/DocPLM.php';
	var extra = (mode ? '<input name="return_mode" value="' + mode + '">' : '');
	var html = '<input name="FormID" value="' + document.querySelector('input[name="FormID"]').value + '"><input name="doc_action" value="' + act + '">';
	for (var i = 0; i < ids.length; i++) { html += '<input name="doc_id[]" value="' + ids[i] + '">'; }
	f.innerHTML = html + extra;
	document.body.appendChild(f); f.submit();
}
function PlmUpgrade() {
	var id = PlmSelectedDocId();
	if (!id) { alert('请先勾选一个文档'); return; }
	document.getElementById('plmUpgradeId').value = id;
	document.getElementById('plmModalUpgrade').style.display = 'block';
}
/* 废止区 / 回收站 操作 */
function PlmToolbarAction(act, mode) {
	PlmAction(act, mode);
}
/* 对单个文档执行后端 action（行内"恢复/彻底删除"用） */
function PlmActionOnDoc(act, docId, confirmMsg) {
	if (confirmMsg && !confirm(confirmMsg)) return;
	var retMode = '';
	if (location.search.indexOf('mode=recycle') >= 0) { retMode = 'recycle'; }
	else if (location.search.indexOf('mode=abolition') >= 0) { retMode = 'abolition'; }
	var f = document.createElement('form');
	f.method = 'POST'; f.action = _RootPath + '/DocPLM.php';
	var extra = (retMode ? '<input name="return_mode" value="' + retMode + '">' : '');
	f.innerHTML = '<input name="FormID" value="' + document.querySelector('input[name="FormID"]').value + '"><input name="doc_action" value="' + act + '"><input name="doc_id" value="' + docId + '">' + extra;
	document.body.appendChild(f); f.submit();
}
function PlmRecover(docId) { PlmActionOnDoc('recover', docId, '确定恢复该废止文档吗？恢复后回到文档工作区正常可用。'); }
function PlmRestore(docId) { PlmActionOnDoc('restore', docId, '确定从回收站还原该文件吗？'); }
function PlmPurge(docId) { PlmActionOnDoc('purge', docId, '彻底删除后不可恢复！确定永久删除该文件吗？'); }
/* 回收站：按时间范围切换（整页刷新，左侧时间树高亮） */
function PlmGoRecycle(tr) {
	var url = _RootPath + '/DocPLM.php?mode=recycle&tr=' + encodeURIComponent(tr || 'all');
	var q = document.getElementById('plmQ');
	var code = document.getElementById('plmCode');
	if (q && q.value) url += '&q=' + encodeURIComponent(q.value);
	if (code && code.value) url += '&code=' + encodeURIComponent(code.value);
	window.location.href = url;
}
function PlmProps(id) {
	id = id || PlmSelectedDocId();
	if (!id) { alert('请先勾选一个文档'); return; }
	document.getElementById('plmPropsId').value = id;
	var b = document.getElementById('plmPropsBody');
	b.innerHTML = '加载中…';
	var x = new XMLHttpRequest();
	x.open('GET', _RootPath + '/DocPLM.php?op=props&doc_id=' + id + '&_r=' + Date.now(), true);
	x.onreadystatechange = function() { if (x.readyState === 4 && x.status === 200) { b.innerHTML = x.responseText; } };
	x.send();
	document.getElementById('plmModalProps').style.display = 'block';
}
function PlmVersions(id) {
	id = id || PlmSelectedDocId();
	if (!id) { alert('请先勾选一个文档'); return; }
	var b = document.getElementById('plmVersionsBody');
	b.innerHTML = '加载中…';
	var x = new XMLHttpRequest();
	x.open('GET', _RootPath + '/DocPLM.php?op=versions&doc_id=' + id + '&_r=' + Date.now(), true);
	x.onreadystatechange = function() { if (x.readyState === 4 && x.status === 200) { b.innerHTML = x.responseText; } };
	x.send();
	document.getElementById('plmModalVersions').style.display = 'block';
}
function PlmLogView(id) {
	id = id || PlmSelectedDocId();
	if (!id) { alert('请先勾选一个文档'); return; }
	var b = document.getElementById('plmLogBody');
	b.innerHTML = '加载中…';
	var x = new XMLHttpRequest();
	x.open('GET', _RootPath + '/DocPLM.php?op=log&doc_id=' + id + '&_r=' + Date.now(), true);
	x.onreadystatechange = function() { if (x.readyState === 4 && x.status === 200) { b.innerHTML = x.responseText; } };
	x.send();
	document.getElementById('plmModalLog').style.display = 'block';
}
function PlmPreviewImg(url) {
	document.getElementById('plmImgContent').src = url;
	document.getElementById('plmImgModal').style.display = 'block';
}
/* 模板操作：新建文档（复制）/ 取消模板 / 设为模板（表单提交带 FormID 校验） */
function PlmTemplateNew(docId) {
	if (!confirm('从该模板复制创建一份新文档（新编码 v1，不修改模板）？')) return;
	PlmTemplateAction(docId, 'template_new');
}
function PlmTplDelete(docId) {
	if (!confirm('确定删除该模板吗？删除后不可恢复！')) return;
	PlmTemplateAction(docId, 'template_off');
}
function PlmTemplateSet(docId) {
	if (!confirm('将文档设为模板？')) return;
	PlmTemplateAction(docId, 'template_set');
}
function PlmTemplateAction(docId, act) {
	var f = document.createElement('form');
	f.method = 'POST';
	f.action = _RootPath + '/DocPLM.php';
	var fields = [
		['FormID', document.querySelector('input[name="FormID"]').value],
		['doc_action', act],
		['doc_id', docId]
	];
	for (var i = 0; i < fields.length; i++) {
		var h = document.createElement('input');
		h.type = 'hidden'; h.name = fields[i][0]; h.value = fields[i][1];
		f.appendChild(h);
	}
	document.body.appendChild(f);
	f.submit();
}
/* 快捷选物料：用 lhgdialog iframe 加载 BOMSetup.php?op=quick_item 复用其分类物料选择器 */
function PlmPickItem(targetInputId) {
	var url = _RootPath + '/BOMSetup.php?op=quick_item&target=' + encodeURIComponent(targetInputId) + '&nameTarget=' + encodeURIComponent(targetInputId) + 'Name&_r=' + Date.now();
	if (typeof $.dialog === 'function') {
		try {
			/* lhgdialog 第三参数 parent = window.parent，让 dialog DOM 创建在父页面 document */
			$.dialog({title:'选择物料（点击左侧分类查看成员料）', width: 1100, height: 660, content: 'url:' + url, lock: true}, function(){}, window.parent);
		} catch(e) {
			$.dialog({title:'选择物料（点击左侧分类查看成员料）', width: 1100, height: 660, content: 'url:' + url, lock: true});
		}
		/* lhgdialog 用内联 style 设 z-index（约 1978），CSS 类选择器匹配不到——直接 JS 把弹窗容器 z-index 提到最顶（遮罩保持其下） */
		setTimeout(function(){
			try {
				$('body > div').each(function(){
					if (this.id === 'ldg_lockmask') return; /* 跳过全屏遮罩，避免盖住弹窗内容 */
					var z = parseInt($(this).css('z-index'), 10);
					var p = $(this).css('position');
					if (!isNaN(z) && z >= 1000 && z < 30000 && (p === 'absolute' || p === 'fixed')) {
						$(this).css('z-index', 30000);
					}
				});
				/* 遮罩固定在弹窗之下、其他弹窗之上 */
				$('#ldg_lockmask').css('z-index', 29000);
			} catch(e){}
		}, 200);
	} else {
		window.open(url, '_blank', 'width=1100,height=660');
	}
}
/* 初始化：绑定折叠符点击 + 右键菜单 + 恢复展开状态 */
var _ctxFolderId = 0;
var _ctxFolderName = '';
var _ctxFolderParent = 0;

/* 弹窗拖动支持（标题栏 mousedown 拖拽整个弹窗） */
(function() {
	function bindDraggable(box) {
		var title = box.querySelector('.plm-modal-title');
		if (!title) return;
		var startX = 0, startY = 0, origLeft = 0, origTop = 0, dragging = false;
		title.addEventListener('mousedown', function(e) {
			dragging = true;
			startX = e.clientX; startY = e.clientY;
			var rect = box.getBoundingClientRect();
			origLeft = rect.left; origTop = rect.top;
			/* 切换到 absolute 定位以便精确拖动 */
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
	}
	document.querySelectorAll('.plm-modal-box').forEach(bindDraggable);
	/* 弹窗每次显示后再绑定（新插入的 box） */
	var obs = new MutationObserver(function(muts) {
		muts.forEach(function(m) {
			m.addedNodes.forEach(function(n) {
				if (n.nodeType === 1 && n.classList && n.classList.contains('plm-modal-box')) bindDraggable(n);
				if (n.nodeType === 1 && n.querySelectorAll) n.querySelectorAll('.plm-modal-box').forEach(bindDraggable);
			});
		});
	});
	obs.observe(document.body, { childList: true, subtree: true });
})();
(function() {
	document.querySelectorAll('#plmTree .tw').forEach(function(tw) {
		tw.addEventListener('click', function(e) {
			e.stopPropagation();
			PlmToggleNode(tw);
		});
	});
	/* 目录节点右键菜单 */
	document.querySelectorAll('#plmTree li.bom-node').forEach(function(li) {
		li.addEventListener('contextmenu', function(e) {
			e.preventDefault();
			e.stopPropagation();
			var fid = li.getAttribute('data-folder');
			if (!fid) return;
			/* 虚拟物料目录节点（data-folder="-1"）与分类/物料节点不弹目录菜单 */
			if (li.getAttribute('data-type') === 'itemroot' || li.getAttribute('data-cat') || li.getAttribute('data-item')) { return; }
			_ctxFolderId = parseInt(fid, 10);
			var lbl = li.querySelector('.lbl');
			_ctxFolderName = lbl ? lbl.textContent.replace(/\s*\(\d+\)\s*$/, '').trim() : '';
			_ctxFolderParent = parseInt(li.getAttribute('data-parent') || '0', 10);
			var m = document.getElementById('plmCtxMenu');
			document.getElementById('plmCtxHead').innerHTML = '目录操作：' + _ctxFolderName;
			m.style.display = 'block';
			m.style.left = e.clientX + 'px';
			m.style.top = e.clientY + 'px';
		});
	});
	/* 点击其他地方隐藏菜单 */
	document.addEventListener('click', function(e) {
		var m = document.getElementById('plmCtxMenu');
		if (m && m.style.display === 'block' && !m.contains(e.target)) { m.style.display = 'none'; }
	});
	PlmRestoreExpand();
})();

/* 右键菜单动作（基于 _ctxFolderId/_ctxFolderName/_ctxFolderParent） */
function PlmCtxNew() {
	document.getElementById('plmCtxMenu').style.display = 'none';
	PlmNewFolder(_ctxFolderId);
}
function PlmCtxRename() {
	document.getElementById('plmCtxMenu').style.display = 'none';
	PlmRenameFolder(_ctxFolderId, _ctxFolderParent, _ctxFolderName);
}
function PlmCtxDelete() {
	document.getElementById('plmCtxMenu').style.display = 'none';
	PlmDeleteFolder(_ctxFolderId, _ctxFolderParent);
}
</script>
