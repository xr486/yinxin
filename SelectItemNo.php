<?php
/* SelectItemNo.php - 料号选择页
 * 替代 segment1set.php 中的「料号维护」Tab，作为 UpdateItemNo.php「点击选择料号」跳转目标。
 * 特点：无 3 个 Tab、初始即展示所有数据、查询条件单行。
 */
$PageSecurity = 1; // 料号选择页：所有已登录用户均可访问，无需重新登录即可通过权限校验
include('includes/session.inc');
$Title = _('料号选择');
$ViewTopic = '料号选择';
$BookMark = '料号选择';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

// 分页参数
if (isset($_POST['Go1']) OR isset($_POST['Go2'])) {
	$_POST['PageOffset'] = (isset($_POST['Go1']) ? $_POST['PageOffset1'] : $_POST['PageOffset2']);
	$_POST['Go'] = '';
}
if (!isset($_POST['PageOffset']) OR $_POST['PageOffset'] == 0) {
	$_POST['PageOffset'] = 1;
}

// 始终查询（初始即展示所有数据）
$sql = "SELECT a.*, (SELECT IFNULL(SUM(quantity),0) FROM inv_onhand_quantity_all b WHERE b.stockid=a.item_no) AS onhand_quantity FROM sf_item_no a WHERE 1=1 ";
if (isset($_POST['ItemNo']) and $_POST['ItemNo'] != '') {
	$sql .= " AND item_no LIKE '%" . $_POST['ItemNo'] . "%' ";
}
if (isset($_POST['item_name']) and $_POST['item_name'] != '') {
	$sql .= " AND item_name LIKE '%" . $_POST['item_name'] . "%' ";
}
if (isset($_POST['item_desc']) and $_POST['item_desc'] != '') {
	$sql .= " AND item_desc LIKE '%" . $_POST['item_desc'] . "%' ";
}
if (isset($_POST['item_category1']) and $_POST['item_category1'] != '') {
	$sql .= " AND item_category1 LIKE '%" . $_POST['item_category1'] . "%' ";
}
$sql .= " ORDER BY item_no ";
$result = DB_query($sql, $db);

$ListCount = DB_num_rows($result);
$ListPageMax = ceil($ListCount / $_SESSION['DisplayRecordsMax']);

if (isset($_POST['Next'])) {
	if ($_POST['PageOffset'] < $ListPageMax) {
		$_POST['PageOffset'] = $_POST['PageOffset'] + 1;
	}
}
if (isset($_POST['Previous'])) {
	if ($_POST['PageOffset'] > 1) {
		$_POST['PageOffset'] = $_POST['PageOffset'] - 1;
	}
}
?>

<link rel="stylesheet" href="<?php echo $RootPath; ?>/css/bom_style.css">
<style>
/* 单行紧凑布局：920 弹窗内不超宽 */
.select-query{display:flex;flex-wrap:nowrap;gap:6px 12px;align-items:center;overflow-x:auto;padding-bottom:6px}
.select-query .q{display:flex;align-items:center;gap:5px;flex:0 0 auto;font-size:13px;color:#546e7a}
.select-query .q span{white-space:nowrap}
.select-query .q input[type=text]{width:110px;padding:5px 8px;border:1px solid #cfd8e3;border-radius:4px;font-size:12px;background:#fff}
.select-query .q input[type=text]:focus{outline:none;border-color:#1976D2}
.select-query .q-btn{margin-left:auto;flex:0 0 auto}
.select-query .q-btn input{padding:5px 18px;background:#1976D2;color:#fff;border:none;border-radius:4px;cursor:pointer;font-size:13px}
.select-query .q-btn input:hover{background:#1565C0}
</style>
<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method="POST">
	<div>
		<input type="hidden" name="FormID" value="<?php echo $_SESSION['FormID']; ?>">
		<input type="hidden" name="PageOffset" value="<?= $_POST['PageOffset'] ?>" />

		<div class="bom-card bom-query-card">
			<div class="select-query">
				<div class="q"><span>料号：</span><input type="text" name="ItemNo" value="<?= htmlspecialchars($_POST['ItemNo']) ?>"></div>
				<div class="q"><span>料号名称：</span><input type="text" name="item_name" value="<?= htmlspecialchars($_POST['item_name']) ?>"></div>
				<div class="q"><span>规格型号：</span><input type="text" name="item_desc" value="<?= htmlspecialchars($_POST['item_desc']) ?>"></div>
				<div class="q"><span>产品类别：</span><input type="text" name="item_category1" value="<?= htmlspecialchars($_POST['item_category1']) ?>"></div>
				<div class="q-btn"><input type="submit" name="Search" value="查询"></div>
			</div>
		</div>

		<div class="bom-card bom-result-card">
			<div class="hier-toolbar">
				<span class="version-tag">料号选择结果（<?= $ListCount ?> 条）</span>
				<div class="bom-page-bar">
					第&nbsp;<?= $_POST['PageOffset'] ?>&nbsp;页，共&nbsp;<?= $ListPageMax ?>&nbsp;页&nbsp;&nbsp;
					<?php if ($ListPageMax > 1) { ?>
					跳转至页:
					<select name="PageOffset1">
						<?php
						$ListPage = 1;
						while ($ListPage <= $ListPageMax) {
							if ($ListPage == $_POST['PageOffset']) {
								echo '<option value="' . $ListPage . '" selected="selected">' . $ListPage . '</option>';
							} else {
								echo '<option value="' . $ListPage . '">' . $ListPage . '</option>';
							}
							$ListPage++;
						}
						?>
					</select>
					<input type="submit" name="Go1" value="跳转" />
					<input type="submit" name="Previous" value="上一页" />
					<input type="submit" name="Next" value="下一页" />
					<?php } ?>
				</div>
			</div>
			<div style="overflow:auto">
				<table cellpadding="2" class="selection bom-table">
					<tr>
						<th class="ascending">料号</th>
						<th class="ascending">料号名称</th>
						<th class="ascending">规格型号</th>
						<th class="ascending">单位</th>
						<th class="ascending">最小订单量</th>
						<th class="ascending">安全库存</th>
						<th class="ascending">生产周期</th>
						<th class="ascending">料号分类</th>
						<th class="ascending">默认仓库</th>
						<th class="ascending">库存量</th>
					</tr>
					<?php
					if (DB_num_rows($result) > 0) {
						DB_data_seek($result, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);
						$k = 0;
						$RowIndex = 0;
						while (($myrow = DB_fetch_array($result)) AND ($RowIndex <> $_SESSION['DisplayRecordsMax'])) {
							if ($k == 1) {
								echo '<tr class="EvenTableRows">';
								$k = 0;
							} else {
								echo '<tr class="OddTableRows">';
								$k = 1;
							}
							?>
							<td>
								<a href="<?= $RootPath ?>/UpdateItemNo.php?ItemID=<?= $myrow['item_id'] ?>&ItemNo=<?= $myrow['item_no'] ?>"><?= $myrow['item_no'] ?></a>
                            </td>
							<td><?= $myrow['item_name'] ?></td>
							<td><?= $myrow['item_desc'] ?></td>
							<td><?= $myrow['units'] ?></td>
							<td><?= $myrow['min_order'] ?></td>
							<td><?= $myrow['safe_qty'] ?></td>
							<td><?= $myrow['manufacture_time'] ?></td>
							<td><?= $myrow['item_category1'] ?></td>
							<td><?= $myrow['sub_code'] ?></td>
							<td><?= $myrow['onhand_quantity'] ?></td>
							</tr>
							<?php
							$RowIndex++;
						}
					} else {
						echo '<tr><td colspan="10" style="padding:24px;text-align:center;color:#999">暂无数据</td></tr>';
					}
					?>
				</table>
			</div>
		</div>
	</div>
</form>

<?php include('includes/footer.inc'); ?>