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
	echo '<style>body{padding:6px;margin:0;background:#fafbfc;font-family:Verdana,Arial,sans-serif;font-size:13px}.embed-title{font-size:15px;font-weight:bold;color:#1976D2;border-bottom:2px solid #1976D2;padding-bottom:6px;margin-bottom:10px}input[type=text],input.number,input[size],textarea,select{text-align:left!important}.mm-detail{width:100%;border-collapse:separate;border-spacing:0;background:#fff;border:1px solid #c5d9ee;border-radius:3px;overflow:hidden;font-size:13px}.mm-detail th{background:linear-gradient(180deg,#42a5f5 0%,#1976D2 100%);color:#fff;font-weight:bold;padding:7px 10px;text-align:right;width:90px;border-right:1px solid #1565C0;border-bottom:1px solid #1565C0;font-size:12px;white-space:nowrap;vertical-align:middle}.mm-detail th:first-child{border-left:1px solid #1565C0}.mm-detail td{padding:6px 10px;text-align:left;border-right:1px solid #e0e0e0;border-bottom:1px solid #e0e0e0;color:#333;font-size:13px;word-break:break-all;vertical-align:middle}.mm-detail td.empty{color:#bbb;font-style:italic;text-align:center}.mm-detail tr:hover td{background:#f1f8ff}.mm-detail-status-ok{display:inline-block;padding:2px 8px;border-radius:3px;background:#e8f5e9;color:#2e7d32;border:1px solid #c8e6c9;font-size:11px;font-weight:bold}.mm-detail-status-stop{display:inline-block;padding:2px 8px;border-radius:3px;background:#fbe9e7;color:#c62828;border:1px solid #ffcdd2;font-size:11px;text-decoration:line-through}
/* Tab 面板默认隐藏，激活才显示（修复 Tab 2/3/4 误显问题） */
.mm-tabs-panel{display:none;background:#fff;min-height:80px}
.mm-tabs-panel.active{display:block}
/* Tab 内容区承载各 panel 切换 */
/* 物料属性 4 列网格 field box（参考老物料详情 label+value 独立盒风格） */
.mf-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:8px;margin-top:4px}
.mf-box{background:#f5f7fa;border:1px solid #dde3ea;border-radius:3px;padding:6px 10px;min-height:38px;display:flex;flex-direction:column;justify-content:center}
.mf-box-wide{grid-column:span 4}
.mf-label{font-size:11px;color:#1976D2;font-weight:bold;margin-bottom:2px;letter-spacing:0.3px;line-height:1.2}
.mf-value{font-size:13px;color:#222;word-break:break-all;line-height:1.4}
.mf-value .item-code{color:#0d47a1;font-weight:bold}
.mf-value .price{color:#d84315;font-weight:bold}
.mf-value.empty{color:#bbb;font-style:italic}</style>';
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

/* 5. 供应商名称 */
$VendorName = '';
if (!empty($ItemInfo['supplier_code'])) {
	$resV = DB_query("SELECT vendor_name FROM vendors WHERE vendor_code='" . DB_escape_string($ItemInfo['supplier_code']) . "'", $db);
	if ($rv = DB_fetch_array($resV)) $VendorName = $rv['vendor_name'];
}

/* 类型/状态/用途字典 */
$DisableFlag = isset($ItemInfo['able_flag']) ? $ItemInfo['able_flag'] : '';
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
	<div class="mf-grid">
		<div class="mf-box"><div class="mf-label">料号编码</div><div class="mf-value"><span class="item-code"><?php echo htmlspecialchars($ItemInfo['item_no']); ?></span></div></div>
		<div class="mf-box"><div class="mf-label">料号名称</div><div class="mf-value"><b><?php echo htmlspecialchars($ItemInfo['item_name']); ?></b></div></div>
		<div class="mf-box"><div class="mf-label">内部 ID</div><div class="mf-value"><?php echo $ItemInfo['item_id']; ?></div></div>
		<div class="mf-box"><div class="mf-label">规格型号</div><div class="mf-value"><?php echo htmlspecialchars($ItemInfo['item_desc']); ?></div></div>

		<div class="mf-box"><div class="mf-label">物料分类</div><div class="mf-value"><?php echo htmlspecialchars($ItemInfo['item_category1']); ?></div></div>
		<div class="mf-box"><div class="mf-label">物料类型</div><div class="mf-value"><?php echo htmlspecialchars($TypeDisp); ?><span style="color:#888;font-size:11px">（<?php echo $ItemType; ?>）</span></div></div>
		<div class="mf-box"><div class="mf-label">状态</div><div class="mf-value"><span class="<?php echo $StatusClass; ?>"><?php echo $Status; ?></span></div></div>
		<div class="mf-box"><div class="mf-label">用途</div><div class="mf-value"><?php echo htmlspecialchars($ItemUseName); ?><span style="color:#888;font-size:11px">（<?php echo $ItemUse; ?>）</span></div></div>

		<div class="mf-box"><div class="mf-label">单位</div><div class="mf-value"><?php echo htmlspecialchars($ItemInfo['units']); ?></div></div>
		<div class="mf-box"><div class="mf-label">安全库存</div><div class="mf-value"><?php echo htmlspecialchars($ItemInfo['safe_qty']); ?></div></div>
		<div class="mf-box"><div class="mf-label">单价</div><div class="mf-value"><span class="price">¥<?php echo number_format(floatval($ItemInfo['unit_price']), 2); ?></span></div></div>
		<div class="mf-box"><div class="mf-label">仓库 / 库位</div><div class="mf-value"><?php echo htmlspecialchars($ItemInfo['sub_code']); ?> / <?php echo htmlspecialchars($ItemInfo['sub_locator']); ?></div></div>

		<div class="mf-box"><div class="mf-label">材质</div><div class="mf-value<?php echo empty($ItemInfo['material']) ? ' empty' : ''; ?>"><?php echo !empty($ItemInfo['material']) ? htmlspecialchars($ItemInfo['material']) : '—'; ?></div></div>
		<div class="mf-box"><div class="mf-label">规格</div><div class="mf-value<?php echo empty($ItemInfo['spec']) ? ' empty' : ''; ?>"><?php echo !empty($ItemInfo['spec']) ? htmlspecialchars($ItemInfo['spec']) : '—'; ?></div></div>
		<div class="mf-box"><div class="mf-label">型号</div><div class="mf-value<?php echo empty($ItemInfo['model']) ? ' empty' : ''; ?>"><?php echo !empty($ItemInfo['model']) ? htmlspecialchars($ItemInfo['model']) : '—'; ?></div></div>
		<div class="mf-box"><div class="mf-label">重量</div><div class="mf-value<?php echo (isset($ItemInfo['weight']) && $ItemInfo['weight'] !== null && $ItemInfo['weight'] !== '') ? '' : ' empty'; ?>"><?php echo (isset($ItemInfo['weight']) && $ItemInfo['weight'] !== null && $ItemInfo['weight'] !== '') ? htmlspecialchars($ItemInfo['weight']) . ' kg' : '—'; ?></div></div>

		<div class="mf-box"><div class="mf-label">图号</div><div class="mf-value<?php echo empty($ItemInfo['drawing_no']) ? ' empty' : ''; ?>"><?php echo !empty($ItemInfo['drawing_no']) ? htmlspecialchars($ItemInfo['drawing_no']) : '—'; ?></div></div>
		<div class="mf-box"><div class="mf-label">标准件类型</div><div class="mf-value<?php echo empty($ItemInfo['std_part_type']) ? ' empty' : ''; ?>"><?php echo !empty($ItemInfo['std_part_type']) ? htmlspecialchars($ItemInfo['std_part_type']) : '—'; ?></div></div>
		<div class="mf-box"><div class="mf-label">物料优先级</div><div class="mf-value<?php echo empty($ItemInfo['priority']) ? ' empty' : ''; ?>"><?php echo !empty($ItemInfo['priority']) ? htmlspecialchars($ItemInfo['priority']) : '—'; ?></div></div>
		<div class="mf-box"><div class="mf-label">主供应商</div><div class="mf-value<?php echo empty($ItemInfo['supplier_code']) ? ' empty' : ''; ?>"><?php echo !empty($ItemInfo['supplier_code']) ? htmlspecialchars($ItemInfo['supplier_code']) . ' <span style="color:#555">/</span> ' . htmlspecialchars($VendorName) : '—'; ?></div></div>

		<div class="mf-box"><div class="mf-label">承认状态</div><div class="mf-value<?php echo empty($ItemInfo['item_status']) ? ' empty' : ''; ?>"><?php
			$StMap = array('草稿'=>array('#f5f5f5','#616161'),'试用'=>array('#e3f2fd','#1565c0'),'正式'=>array('#e8f5e9','#2e7d32'),'冻结'=>array('#fff3e0','#e65100'),'报废'=>array('#fdecea','#c62828'));
			if (!empty($ItemInfo['item_status'])) {
				$st = $ItemInfo['item_status'];
				$stBg = isset($StMap[$st]) ? $StMap[$st][0] : '#e8f5e9';
				$stFg = isset($StMap[$st]) ? $StMap[$st][1] : '#2e7d32';
				echo '<span style="display:inline-block;padding:2px 8px;border-radius:3px;background:' . $stBg . ';color:' . $stFg . ';border:1px solid ' . $stFg . ';font-size:11px;">' . htmlspecialchars($st) . '</span>';
			} else { echo '—'; }
		?></div></div>
		<div class="mf-box"><div class="mf-label">货号</div><div class="mf-value<?php echo empty($ItemInfo['huohao']) ? ' empty' : ''; ?>"><?php echo !empty($ItemInfo['huohao']) ? htmlspecialchars($ItemInfo['huohao']) : '—'; ?></div></div>
		<div class="mf-box mf-box-wide"><div class="mf-label">内部备注</div><div class="mf-value<?php echo empty($ItemInfo['item_remark']) ? ' empty' : ''; ?>"><?php echo !empty($ItemInfo['item_remark']) ? htmlspecialchars($ItemInfo['item_remark']) : '—'; ?></div></div>

		<div class="mf-box"><div class="mf-label">创建人</div><div class="mf-value"><?php echo htmlspecialchars($ItemInfo['created_by']); ?></div></div>
		<div class="mf-box"><div class="mf-label">最后更新</div><div class="mf-value"><?php echo htmlspecialchars($ItemInfo['last_updated_by']); ?></div></div>
		<div class="mf-box"><div class="mf-label">创建时间</div><div class="mf-value<?php echo empty($ItemInfo['creation_date']) ? ' empty' : ''; ?>"><?php echo !empty($ItemInfo['creation_date']) ? date('Y-m-d H:i:s', $ItemInfo['creation_date']) : '—'; ?></div></div>
		<div class="mf-box"><div class="mf-label">更新时间</div><div class="mf-value<?php echo empty($ItemInfo['last_update_date']) ? ' empty' : ''; ?>"><?php echo !empty($ItemInfo['last_update_date']) ? date('Y-m-d H:i:s', $ItemInfo['last_update_date']) : '—'; ?></div></div>
	</div>
</div>

<!-- Tab 2: BOM 结构 -->
<div class="mm-tabs-panel<?php echo $ActiveTab == 2 ? ' active' : ''; ?>" data-tab="2">
	<?php if (count($itemBoms) > 0) { ?>
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
	<?php if (count($itemFiles) > 0) { ?>
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
	<?php if (count($itemRoutes) > 0) { ?>
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