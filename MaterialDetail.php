<?php
/* MaterialDetail.php - 物料详情（4 Tab：基本信息/BOM结构/图档/工艺路线）
 * 物料↔BOM↔图档↔工艺 4 向互动的查看入口
 * 入参 ?item_no=X（必填），支持 ?embed=1（弹窗模式）
 */
$PageSecurity = 1;
include('includes/session.inc');

$isEmbed = isset($_GET['embed']) && $_GET['embed'] == '1';
$Title = _('物料详情');
$ItemNo = isset($_GET['item_no']) ? trim($_GET['item_no']) : '';
if ($ItemNo != '' && !preg_match('/^[A-Za-z0-9_.\-]+$/', $ItemNo)) { $ItemNo = ''; }
$ActiveTab = isset($_GET['tab']) ? intval($_GET['tab']) : 1;
if ($ActiveTab < 1 || $ActiveTab > 4) $ActiveTab = 1;

if ($isEmbed) {
	$Theme = isset($_SESSION['Theme']) ? $_SESSION['Theme'] : 'xenos';
	echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>' . htmlspecialchars($Title) . '</title>';
	echo '<link href="' . $RootPath . '/css/' . $Theme . '/default.css" rel="stylesheet" type="text/css"/>';
	echo '<link href="' . $RootPath . '/css/bom_style.css" rel="stylesheet" type="text/css"/>';
	echo '<script src="' . $RootPath . '/javascript/jquery-1.7.2.min.js"></script>';
	echo '<style>body{padding:14px;margin:0;background:#fafbfc;font-family:Verdana,Arial,sans-serif;font-size:13px}.embed-title{font-size:15px;font-weight:bold;color:#1976D2;border-bottom:2px solid #1976D2;padding-bottom:8px;margin-bottom:14px}</style>';
	echo '</head><body>';
} else {
	$ViewTopic = '物料详情';
	$BookMark = '物料详情';
	include('includes/header.inc');
}
include('includes/SQL_CommonFunctions.inc');

if ($ItemNo == '') {
	echo '<div class="mm-empty">请提供 item_no 参数</div>';
	if ($isEmbed) echo '</body></html>'; else include('includes/footer.inc');
	exit;
}

/* 1. 查物料基本信息 */
$resItem = DB_query(
	"SELECT i.* FROM sf_item_no i WHERE i.item_no='" . DB_escape_string($ItemNo) . "'", $db
);
$ItemInfo = (DB_num_rows($resItem) > 0) ? DB_fetch_array($resItem) : null;
if ($ItemInfo === null) {
	echo '<div class="mm-empty">物料 ' . htmlspecialchars($ItemNo) . ' 不存在</div>';
	if ($isEmbed) echo '</body></html>'; else include('includes/footer.inc');
	exit;
}

/* 2. 查 BOM 头列表 */
$resB = DB_query(
	"SELECT bom_header_id, version, status, is_current, creation_date, last_update_date
	 FROM bom_headers_all WHERE assembly_item_no='" . DB_escape_string($ItemNo) . "'
	 ORDER BY is_current DESC, version DESC", $db
);
$itemBoms = array();
while ($r = DB_fetch_array($resB)) $itemBoms[] = $r;

/* 3. 查图档 */
$resF = DB_query(
	"SELECT file_patch, file_name, creation_date, created_by
	 FROM sf_item_no_file WHERE item_no='" . DB_escape_string($ItemNo) . "'
	 ORDER BY creation_date DESC LIMIT 50", $db
);
$itemFiles = array();
while ($r = DB_fetch_array($resF)) $itemFiles[] = $r;

/* 4. 查工艺路线（bom_routings_all） */
$resR = DB_query(
	"SELECT route_id, operation_seq_num, operation_code, route_status, effectivity_date,
	        creation_date, created_by, rate, channeng, remarks
	 FROM bom_routings_all WHERE assembly_item_no='" . DB_escape_string($ItemNo) . "'
	 ORDER BY operation_seq_num", $db
);
$itemRoutes = array();
while ($r = DB_fetch_array($resR)) $itemRoutes[] = $r;

/* 类型/状态/用途字典 */
$DisableFlag = isset($ItemInfo['disable_flag']) ? $ItemInfo['disable_flag'] : '';
$IsActive = ($DisableFlag != 'N');
$Status = ($DisableFlag == 'N' ? '停用' : '启用');
$StatusClass = ($DisableFlag == 'N' ? 'mm-status-stop' : 'mm-status-ok');
$ItemType = isset($ItemInfo['item_type']) ? $ItemInfo['item_type'] : '';
$TypeMap = array('M' => '原材料', 'B' => '半成品', 'F' => '成品', 'P' => '采购件');
$TypeDisp = isset($TypeMap[$ItemType]) ? $TypeMap[$ItemType] : $ItemType;
$ItemUse = isset($ItemInfo['item_use']) ? $ItemInfo['item_use'] : '';
$ItemUseName = ($ItemUse == 'Y' ? '研发' : '生产');
?>

<div class="embed-title">📋 物料详情：<?php echo htmlspecialchars($ItemInfo['item_no']); ?> <span style="color:#666;font-size:13px;font-weight:normal">/ <?php echo htmlspecialchars($ItemInfo['item_name']); ?></span></div>

<div class="mm-tabs">
	<div class="mm-tab<?php echo $ActiveTab == 1 ? ' active' : ''; ?>" onclick="MdSwitchTab(1)">📋 基本信息</div>
	<div class="mm-tab<?php echo $ActiveTab == 2 ? ' active' : ''; ?>" onclick="MdSwitchTab(2)">📐 BOM 结构 <span class="ct">（<?php echo count($itemBoms); ?>）</span></div>
	<div class="mm-tab<?php echo $ActiveTab == 3 ? ' active' : ''; ?>" onclick="MdSwitchTab(3)">📎 图档 <span class="ct">（<?php echo count($itemFiles); ?>）</span></div>
	<div class="mm-tab<?php echo $ActiveTab == 4 ? ' active' : ''; ?>" onclick="MdSwitchTab(4)">⚙ 工艺路线 <span class="ct">（<?php echo count($itemRoutes); ?>）</span></div>
</div>

<!-- Tab 1: 基本信息 -->
<div class="mm-tabs-panel<?php echo $ActiveTab == 1 ? ' active' : ''; ?>" data-tab="1">
	<table class="mm-table">
		<tr>
			<th width="100">料号编码</th><td><b><?php echo htmlspecialchars($ItemInfo['item_no']); ?></b></td>
			<th width="100">料号名称</th><td><?php echo htmlspecialchars($ItemInfo['item_name']); ?></td>
			<th width="100">内部ID</th><td><?php echo $ItemInfo['item_id']; ?></td>
			<th width="100">规格型号</th><td><?php echo htmlspecialchars($ItemInfo['item_desc']); ?></td>
		</tr>
		<tr>
			<th>物料分类</th><td><?php echo htmlspecialchars($ItemInfo['item_category1']); ?></td>
			<th>物料类型</th><td><?php echo htmlspecialchars($TypeDisp); ?>（<?php echo $ItemType; ?>）</td>
			<th>状态</th><td><span class="<?php echo $StatusClass; ?>"><?php echo $Status; ?></span></td>
			<th>用途</th><td><?php echo htmlspecialchars($ItemUseName); ?>（<?php echo $ItemUse; ?>）</td>
		</tr>
		<tr>
			<th>单位</th><td><?php echo htmlspecialchars($ItemInfo['units']); ?></td>
			<th>安全库存</th><td><?php echo htmlspecialchars($ItemInfo['safe_qty']); ?></td>
			<th>单价</th><td>¥<?php echo number_format(floatval($ItemInfo['unit_price']), 2); ?></td>
			<th>仓库 / 库位</th><td><?php echo htmlspecialchars($ItemInfo['sub_code']); ?> / <?php echo htmlspecialchars($ItemInfo['sub_locator']); ?></td>
		</tr>
		<tr>
			<th>创建人</th><td><?php echo htmlspecialchars($ItemInfo['created_by']); ?></td>
			<th>最后更新</th><td><?php echo htmlspecialchars($ItemInfo['last_updated_by']); ?></td>
			<th>创建时间</th><td><?php echo !empty($ItemInfo['creation_date']) ? date('Y-m-d H:i:s', $ItemInfo['creation_date']) : '—'; ?></td>
			<th>更新时间</th><td><?php echo !empty($ItemInfo['last_update_date']) ? date('Y-m-d H:i:s', $ItemInfo['last_update_date']) : '—'; ?></td>
		</tr>
	</table>
</div>

<!-- Tab 2: BOM 结构 -->
<div class="mm-tabs-panel<?php echo $ActiveTab == 2 ? ' active' : ''; ?>" data-tab="2">
	<?php if (count($itemBoms) == 0) { ?>
		<div class="mm-empty">该物料暂无 BOM 头（点击下方链接前往 BOMSetup 创建）</div>
	<?php } else { ?>
	<table class="mm-table">
		<tr><th width="80">版本</th><th width="100">状态</th><th width="100">是否当前</th><th width="160">创建时间</th><th>操作</th></tr>
		<?php foreach ($itemBoms as $bh): ?>
		<tr>
			<td>v<?php echo $bh['version']; ?></td>
			<td><?php echo htmlspecialchars($bh['status']); ?></td>
			<td><?php echo ($bh['is_current'] ? '✓ 当前' : '—'); ?></td>
			<td><?php echo $bh['creation_date'] ? date('Y-m-d H:i:s', $bh['creation_date']) : '—'; ?></td>
			<td><a class="bom-link" href="<?php echo $RootPath; ?>/BOMSetup.php?item_no=<?php echo urlencode($ItemNo); ?>" target="_blank">查看/编辑 BOM →</a></td>
		</tr>
		<?php endforeach; ?>
	</table>
	<?php } ?>
</div>

<!-- Tab 3: 图档 -->
<div class="mm-tabs-panel<?php echo $ActiveTab == 3 ? ' active' : ''; ?>" data-tab="3">
	<?php if (count($itemFiles) == 0) { ?>
		<div class="mm-empty">该物料暂无图档（如需上传，请前往图文档中心）</div>
	<?php } else { ?>
	<table class="mm-table">
		<tr><th width="40">#</th><th>文件名称</th><th width="100">上传人</th><th width="160">上传时间</th></tr>
		<?php $i = 1; foreach ($itemFiles as $f): ?>
		<tr>
			<td><?php echo $i++; ?></td>
			<td style="text-align:left"><?php echo htmlspecialchars($f['file_name']); ?> <span style="color:#888;font-size:11px">（<?php echo basename($f['file_patch']); ?>）</span></td>
			<td><?php echo htmlspecialchars($f['created_by']); ?></td>
			<td><?php echo date('Y-m-d H:i:s', $f['creation_date']); ?></td>
		</tr>
		<?php endforeach; ?>
	</table>
	<?php } ?>
</div>

<!-- Tab 4: 工艺路线 -->
<div class="mm-tabs-panel<?php echo $ActiveTab == 4 ? ' active' : ''; ?>" data-tab="4">
	<?php if (count($itemRoutes) == 0) { ?>
		<div class="mm-empty">该物料暂无工艺路线（如需建立，请前往 BOMRouteModify）</div>
	<?php } else { ?>
	<table class="mm-table">
		<tr>
			<th width="60">序号</th>
			<th width="120">工序编码</th>
			<th width="100">状态</th>
			<th width="100">产能</th>
			<th width="100">人力</th>
			<th width="140">生效日期</th>
			<th width="140">创建时间</th>
			<th>备注</th>
		</tr>
		<?php foreach ($itemRoutes as $r): ?>
		<tr>
			<td style="text-align:center"><?php echo $r['operation_seq_num']; ?></td>
			<td><?php echo htmlspecialchars($r['operation_code']); ?></td>
			<td style="text-align:center">
				<?php if ($r['route_status'] == '已签核') { ?>
					<span class="mm-status-ok">已签核</span>
				<?php } else { ?>
					<span class="mm-status-stop"><?php echo htmlspecialchars($r['route_status']); ?></span>
				<?php } ?>
			</td>
			<td style="text-align:center"><?php echo htmlspecialchars($r['channeng']); ?></td>
			<td style="text-align:center"><?php echo htmlspecialchars($r['renli']); ?></td>
			<td style="text-align:center"><?php echo $r['effectivity_date'] ? date('Y-m-d', $r['effectivity_date']) : '—'; ?></td>
			<td style="text-align:center"><?php echo $r['creation_date'] ? date('Y-m-d', $r['creation_date']) : '—'; ?></td>
			<td><?php echo htmlspecialchars($r['remarks']); ?></td>
		</tr>
		<?php endforeach; ?>
	</table>
	<div style="margin-top:10px;font-size:12px;color:#888">共 <b style="color:#1976D2"><?php echo count($itemRoutes); ?></b> 道工序</div>
	<?php } ?>
</div>

<script type="text/javascript">
var MD_CUR_ITEM = '<?php echo htmlspecialchars(addslashes($ItemNo), ENT_QUOTES); ?>';
function MdSwitchTab(t) {
	// 在 embed 模式下不刷新页面，只切换显示；非 embed 时刷 URL
	var inIframe = (window.parent !== window && window.parent.$.dialog);
	if (inIframe) {
		var panels = document.querySelectorAll('.mm-tabs-panel');
		var tabs = document.querySelectorAll('.mm-tab');
		for (var i = 0; i < panels.length; i++) panels[i].classList.remove('active');
		for (var j = 0; j < tabs.length; j++) tabs[j].classList.remove('active');
		var sel = document.querySelector('.mm-tabs-panel[data-tab="'+t+'"]');
		if (sel) sel.classList.add('active');
		var selTab = document.querySelectorAll('.mm-tab')[t-1];
		if (selTab) selTab.classList.add('active');
	} else {
		window.location.href = '<?php echo $RootPath; ?>/MaterialDetail.php?item_no=' + encodeURIComponent(MD_CUR_ITEM) + '&tab=' + t;
	}
}
</script>

<?php
if ($isEmbed) {
	echo '</body></html>';
} else {
	include('includes/footer.inc');
}
?>