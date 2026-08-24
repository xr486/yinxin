<?php



include('includes/login2.inc');
function db_sql($sql, $type = 1)
{
	$arr = array();
	$sql = mysql_query($sql);
	// $row = mysql_fetch_array($sql);--这里执行会导致type=2再执行一次，里面第一行资料被捞过从第二行开始获取,结果少资料
	if ($type == 1) return mysql_fetch_array($sql);
	if ($type == 2) {
		while ($row = mysql_fetch_array($sql)) {
			$arr[] = $row;
		}
		return $arr;
	}
	if ($type == 3)
		return mysql_num_rows($sql);
	return array();
}



$fwValue = isset($_REQUEST['fwValue']) ? $_REQUEST['fwValue'] : '';
$cat     = isset($_REQUEST['cat']) ? $_REQUEST['cat'] : 'buliao';
$page    = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
$po_num  = isset($_REQUEST['po_num']) ? $_REQUEST['po_num'] : '';
$ItemNo  = isset($_REQUEST['ItemNo']) ? $_REQUEST['ItemNo'] : '';
$wip_entity_name = isset($_REQUEST['wip_entity_name']) ? $_REQUEST['wip_entity_name'] : '';
$where = ' and a.status="APPROVED"  ';
if ($po_num) {
	$where .= ' and b.po_num like "%' . $po_num . '%"';
}
if ($ItemNo) {
	$where .= ' and c.item_no like "%' . $ItemNo . '%"';
}

if ($wip_entity_name) {
	$where .= ' and b.wip_entity_name like "%' . $wip_entity_name . '%"';
}

if ($cat) {
	$where .= ' and a.vendor_code ="' . $cat . ' " ';
}

$num = 10;
$off = $num * ($page - 1);


$count = db_sql("SELECT 
						b.po_line_id, b.po_num,b.line,
						b.stockid,b.price,b.uom,
						b.quantity_received,b.quantity 
				FROM 
						po_headers_all a , po_lines_all b,wip_jobs_all d,so_lines_all c 
				WHERE   b.wip_entity_name=d.wip_entity_name and  d.so_header_number=c.order_number and  d.so_line_number=c.line and  a.po_num=b.po_num    
						and b.stockid = c.stockid 
						and a.status='APPROVED'   
						and b.quantity > b.quantity_received   " . $where . "", 3
				);

$pages = ceil($count / $num);

$sql = "SELECT b.po_line_id,b.po_num,b.line,b.need_date,b.stockid,b.price,b.uom,b.quantity_received,b.quantity,c.item_name ,a.need_date  estimate_date,b.operation_seq_num,b.operation_code,b.wip_entity_name,c.units
FROM po_headers_all a , po_lines_all b,wip_jobs_all d,sf_item_no c
WHERE b.wip_entity_name=d.wip_entity_name and  a.po_num=b.po_num and b.stockid = c.item_no and a.status='APPROVED'  and b.quantity > b.quantity_received  " . $where . " ORDER BY b.po_num  desc limit " . $off . "," . $num . "";
 // echo $sql;

$list = db_sql($sql, 2);


function show_page($url, $page, $pages, $total, $t0 = '')
{
	$str = '';
	$page = $page > $pages ? $pages : $page;
	if ($page > 1) {
		$str .= '<a class="pre" href="' . $url . (1) . $t0 . '">上一页</a>&nbsp;';
	} else {
		$str .= '<a class="pre">上一页</a>&nbsp;';
	}
	if ($page < 5) $start = 1;
	$end = 5;
	if ($page >= 5) {
		$start = $page - 2;
		$end = $page + 3;
	}
	$end = $end > $pages ? $pages : $end;
	for ($i = $start; $i <= $end; $i++) {
		if ($i == $page) {
			$str .= '<span class="cur">' . $i . '</span>&nbsp;';
		} else {
			$str .= '<a href="' . $url . $i . $t0 . '">' . $i . '</a>&nbsp;&nbsp;';
		}
	}
	if ($page >= 1 && $page < $pages) {
		$str .= '<a href="' . $url . ($page + 1) . $t0 . '">下一页</a>&nbsp;';
	} else {
		$str .= '<a class="next">下一页</a>&nbsp;';
	}
	$str .= '<span class="pages_c">页次:' . $page . '/' . $pages . '&nbsp;&nbsp;&nbsp;总计:' . $total . ' </span>';
	return $str;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>

	<title>查询料号</title>
	<link rel="shortcut icon" href="favicon.ico" />
	<link rel="icon" href="favicon.ico" />
	<meta http-equiv="Content-Type" content="application/html; charset=utf-8" />
	<link href="css/xenos/default.css" rel="stylesheet" type="text/css" />
	<script type="text/javascript" src="javascripts/miscfunctions.js"></script>
	<script type="text/javascript" src="javascripts/wdatepicker.js"></script>
	<script src="javascript/jquery-1.10.2.min.js"></script>
	<script src="javascript/bootstrap.min.js"></script>
	<script src="javascript/jquery.dataTables.js"></script>
	<script src="javascript/jquery.livequery.js"></script>
</head>

<body>


	<script type="text/javascript">
		$(document).ready(function() {
			var api = frameElement.api,
				W = api.opener;



			$("#ck_company tr").slice(1).click(function() {
				var chks = $("input[type='radio']", this);
				var tag = $(this).attr("tag");
				if (tag == "selected") {
					// 之前已选中，设置为未选中
					$(this).attr("tag", "");
					chks.prop("checked", false);
					console.log('on');
				} else {
					var rel = $(this).children("td:last-child").children(".coupons").attr("rel");
					console.log($(this).children("td:last-child").children(".coupons").attr("rel"));
					c = rel.split(":");
					$("#form_po_num").text(c[0]);
					$("#form_line").text(c[1]);
					$("#form_ItemNo").text(c[2]);
					$("#form_line_id").text(c[3]);
					$("#form_units").text(c[4]);
					$("#form_price").text(c[5]);
					$("#form_quantity").text(c[6]);
					$("#form_quantity_received").text(c[7]);
					$("#form_quantity_received2").text(c[8]);
					$("#form_wip_entity_name").text(c[9]);
					$("#form_operation_seq_num").text(c[10]);
					$("#form_operation_code").text(c[11]);
					$("#form_item_name").text(c[12]);
					W.document.getElementById('text_slect_po_num' + $('#fwValue').val()).value = $("label#form_po_num").text();
					W.document.getElementById('text_slect_line' + $('#fwValue').val()).value = $("label#form_line").text();
					W.document.getElementById('text_slect_buliao' + $('#fwValue').val()).value = $("label#form_ItemNo").text();
					W.document.getElementById('text_slect_line_id' + $('#fwValue').val()).value = $("label#form_line_id").text();
					W.document.getElementById('text_slect_units' + $('#fwValue').val()).value = $("label#form_units").text();
					W.document.getElementById('text_slect_price' + $('#fwValue').val()).value = $("label#form_price").text();
					W.document.getElementById('text_slect_quantity' + $('#fwValue').val()).value = $("label#form_quantity").text();
					W.document.getElementById('text_slect_quantity_received' + $('#fwValue').val()).value = $("label#form_quantity_received").text();
					W.document.getElementById('text_slect_quantity_received2' + $('#fwValue').val()).value = $("label#form_quantity_received2").text();
					W.document.getElementById('this_receive_quantity' + $('#fwValue').val()).value = $("label#form_quantity_received2").text();
					W.document.getElementById('text_slect_wip_entity_name' + $('#fwValue').val()).value = $("label#form_wip_entity_name").text();
					W.document.getElementById('text_slect_operation_code' + $('#fwValue').val()).value = $("label#form_operation_code").text();
					W.document.getElementById('text_slect_item_name' + $('#fwValue').val()).value = $("label#form_item_name").text();
					$("#xianshi").css("display", "block");
					api.close();
				}
			});

		});
	</script>
	<div id="CanvasDiv">

		<div id="BodyDiv">
			<div id="BodyWrapDiv">
				<p class="page_title_text">
					<img src="./css/xenos/images/magnifier.png" title="查询料号" alt="查询料号">查询料号
				</p>
				<form action="./Searchwaitporcv.php?fwValue=<?= $cat ?>&cat=<?= $cat ?>" method="POST">
					<div>

						<table cellpadding="3" class="selection">
							<div class="text-nav">
								<div class="text-nav-1">
									<div>
										采购单号：
									</div>

									<input type="text"   autocomplete="off"   name="po_num" value="<?= $po_num ?>">
								</div>
								<div class="text-nav-1">
									<div>
										料号
									</div>

									<input type="text"   autocomplete="off"   name="ItemNo" value="<?= $ItemNo ?>">
								</div>
								<div class="text-nav-1">
									<div>
										工单号
									</div>

									<input type="text"   autocomplete="off"   name="wip_entity_name" value="<?= $wip_entity_name ?>">
								</div>
							</div>


						</table>
						<div class="centre">

							<input type="submit" value="查找">
						</div>
				</form>
				<br />
				<div class="text-nav-table">
					<table cellpadding="2" class="selection" id="ck_company">
						<tr>
							<th class="ascending" width="150">
								采购单号
							</th>
							<th width="20">
								行
							</th>
							<th class="ascending" width="150">
								料号
							</th>
							<th class="ascending" width="150">
								料号名称
							</th>
							<th width="150">
								工单号
							</th>
							<th width="50">
								单位
							</th>

							<th width="50">
								采购数量
							</th>
							<th width="80">
								已收数量
							</th>
							<th width="80">
								未收数量
							</th> 
							<th width="80">
								工序名
							</th>



						</tr>
						<?php foreach ($list as $arr => $row2) { ?>
							<tr class="EvenTableRows">
								<td>
									<?= $row2['po_num'] ?>
								</td>
								<td>
									<?= $row2['line'] ?>
								</td>
								<td>
									<?= $row2['stockid'] ?>
								</td>
								<td> <?= $row2['item_name'] ?>
								</td>
								<td> <?= $row2['wip_entity_name'] ?>
								</td>
								<td> <?= $row2['units'] ?>
								</td>
								<td> <?= $row2['quantity'] ?> </td>
								<td> <?= $row2['quantity_received'] ?> </td>
								<td> <?= $quantity_received2 = $row2['quantity'] - $row2['quantity_received'] ?> </td>
								 
								<td> <?= $row2['operation_code'] ?> </td>

								<td>
									<input name="a" type="hidden" value="选择" class="coupons" rel="<?= $row2['po_num'] ?>:<?= $row2['line'] ?>:<?= $row2['stockid'] ?>:<?= $row2['po_line_id'] ?>:<?= $row2['units'] ?>:<?= $row2['price'] ?>:<?= $row2['quantity'] ?>:<?= $row2['quantity_received'] ?>:<?= $quantity_received2 ?>:<?= $row2['wip_entity_name'] ?>:<?= $row2['operation_seq_num'] ?>:<?= $row2['operation_code'] ?>:<?= $row2['item_name'] ?>">
								</td>
							</tr>
						<?php } ?>


					</table>
				</div>
				<br />
				<div class="centre">
					<?= show_page('?page=', $page, $pages, $count, '&fwValue=' . $cat . '&cat=' . $cat . '&ItemNo=' . $ItemNo . '&wip_entity_name' . $wip_entity_name . ''); ?>

				</div>
			</div>

		</div>
	</div>
	<div id="FooterDiv">
		<div id="FooterWrapDiv">

		</div>
	</div>
	<div style="display:none">
		<p><label class="text-info">po_num:</label>　<label id="form_po_num"></label></p>
	</div>
	<div style="display:none">
		<p><label class="text-info">line:</label>　<label id="form_line"></label></p>
	</div>
	<div style="display:none">
		<p><label class="text-info">ItemNo:</label>　<label id="form_ItemNo"></label></p>
	</div>

	<div style="display:none">
		<p><label class="text-info">Units:</label>　<label id="form_units"></label></p>
	</div>

	<div style="display:none">
		<p><label class="text-info">price</label>　<label id="form_price"></label></p>
	</div>
	<div style="display:none">
		<p><label class="text-info">quantity</label>　<label id="form_quantity"></label></p>
	</div>
	<div style="display:none">
		<p><label class="text-info">quantity_received</label>　<label id="form_quantity_received"></label></p>
	</div>
	<div style="display:none">
		<p><label class="text-info">quantity_received2</label>　<label id="form_quantity_received2"></label></p>
	</div>
	<div style="display:none">
		<p><label class="text-info">line_id</label>　<label id="form_line_id"></label></p>
	</div>
	
	<div style="display:none">
		<p><label class="text-info">wip_entity_name</label>　<label id="form_wip_entity_name"></label></p>
	</div>
	<div style="display:none">
		<p><label class="text-info">operation_seq_num</label>　<label id="form_operation_seq_num"></label></p>
	</div>

	<div style="display:none">
		<p><label class="text-info">operation_code</label>　<label id="form_operation_code"></label></p>
	</div>

	<div style="display:none">
		<p><label class="text-info">item_name</label>　<label id="form_item_name"></label></p>
	</div>

	<div style="display:none">
		<p><input type="hidden" name="cat" id="cat" value="<?= $cat ?>" /></p>
		<p><input type="hidden" name="fwValue" value="<?= $fwValue ?>" id="fwValue" /></p>
	</div>


</body>

</html>