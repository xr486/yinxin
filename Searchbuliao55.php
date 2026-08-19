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
$invoice_num  = isset($_REQUEST['invoice_num']) ? $_REQUEST['invoice_num'] : '';
$invoice_name  = isset($_REQUEST['invoice_name']) ? $_REQUEST['invoice_name'] : '';
$where = ' and status="核准"  ';
if ($invoice_num) {
	$where .= ' and invoice_num like "%' . $invoice_num . '%"';
}
if ($invoice_name) {
	$where .= ' and invoice_name like "%' . $invoice_name . '%"';
}
if ($cat) {
	$where .= ' and vendor_code ="' . $cat . ' " ';
}
$num = 10;
$off = $num * ($page - 1);


$count = db_sql("SELECT *
FROM ap_invoice_headers_all
WHERE status='核准' and   invoice_type<>'红字发票' and invoice_amount > payment_amount " . $where . "", 3
				);

$pages = ceil($count / $num);

$sql = "SELECT *
FROM ap_invoice_headers_all
WHERE status='核准' and invoice_amount > payment_amount and   invoice_type<>'红字发票' " . $where . " ORDER BY invoice_num  desc limit " . $off . "," . $num . "";
//  echo $sql;

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

	<title>查询待付款发票</title>
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
         window.onload=function(){
            var ch_checked=document.getElementById("chsecheck");
            var api = frameElement.api, W = api.opener;
            ch_checked.onclick=function(){
                var arr1=document.getElementsByName("boxlist");
                for(i=0;i<arr1.length;i++){ 
					
                    if(arr1[i].checked==true){
						var f2=document.getElementsByClassName("coupons");
						var bot=document.getElementById("fwValue");
						var botint=parseInt(bot.value);
						rel=f2[i].value;
									//console.log($(this).children("td:last-child").children(".coupons").attr("rel"));
				c = rel.split(":");
				$("#form_invoice_num").text(c[0]);
					
					$("#form_invoice_name").text(c[1]);
					$("#form_amount").text(c[2]);
					$("#form_payment_amount").text(c[3]);
					$("#form_wait_amount").text(c[4]);
					$("#form_invoice_date").text(c[5]);
					
					W.document.getElementById('text_slect_invoice_num' + $('#fwValue').val()).value = $("label#form_invoice_num").text();
					W.document.getElementById('text_slect_invoice_name' + $('#fwValue').val()).value = $("label#form_invoice_name").text();
					W.document.getElementById('text_slect_amount' + $('#fwValue').val()).value = $("label#form_amount").text();
					W.document.getElementById('text_slect_payment_amount' + $('#fwValue').val()).value = $("label#form_payment_amount").text();
					W.document.getElementById('text_slect_wait_amount' + $('#fwValue').val()).value = $("label#form_wait_amount").text();
					W.document.getElementById('text_slect_invoice_date' + $('#fwValue').val()).value = $("label#form_invoice_date").text();
						botint++;
						bot.value=botint+'';
						// $('#fwValue').val()=$('#fwValue').val()+'2';
						// 'text_slect_item_spec'+$('#fwValue').val()+=1;
						
						console.log($('#fwValue'));
				}
                }api.close();
            }

        }
    </script>

	
	<div id="CanvasDiv">

		<div id="BodyDiv">
			<div id="BodyWrapDiv">
				<p class="page_title_text">
					<img src="./css/xenos/images/magnifier.png" title="查询待付款发票" alt="查询待付款发票">查询待付款发票
				</p>
				<form action="./Searchbuliao55.php?fwValue=<?= $cat ?>&cat=<?= $cat ?>" method="POST">
					<div>

						<table cellpadding="1" class="selection">
							<div class="text-nav1">
								<div class="text-nav-1">
									<div>
										发票单号：
									</div>

									<input type="text" autocomplete="off" name="invoice_num" value="<?= $invoice_num ?>">
								</div>
								<div class="text-nav-1">
									<div>
										发票号码：
									</div>

									<input type="text" autocomplete="off" name="invoice_name" value="<?= $invoice_name ?>">
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
						<th  width =40 >
						选择
					    </th>
							<th class="ascending" width="150">
								发票单号
							</th>
							

							<th width="50"> 发票号码 </th>
							<th width="50"> 发票类型 </th> 
							<th width="80">
								应付金额
							</th>
							<th width="80">
								已付金额
							</th>
							<th width="80">
								待付金额
							</th>
							<th width="80">
								发票日期
							</th>




						</tr>
						<?php foreach ($list as $arr => $row2) { ?>
							<tr class="EvenTableRows">
							<td>
							<input type="checkbox" style="height: 24px;width: 24px;" name="boxlist">
						</td>
								<td><?= $row2['invoice_name'] ?></td>
								<td><?= $row2['invoice_num'] ?></td>
								<td><?= $row2['invoice_type'] ?></td>
								<td><?= $row2['invoice_amount'] ?></td>
								
								<td> <?= $row2['payment_amount'] ?> </td>
								<td> <?=  ($row2['invoice_amount']-$row2['payment_amount']) ?> </td>
								<td> <?= $invoice_date=date('Y-m-d',$row2['invoice_date'])?> </td>
								

								<td>
<input name="a" type="hidden"  class="coupons" value="<?= $row2['invoice_num'] ?>:<?= $row2['invoice_name'] ?>:<?= $row2['invoice_amount'] ?>:<?= $row2['payment_amount'] ?>:<?= ($row2['invoice_amount']-$row2['payment_amount']) ?>:<?= $invoice_date ?>">
								</td>
							</tr>
						<?php } ?>


					</table>
					<input type="button" class="choosecheck" id="chsecheck" style="height: 24px;width: 100px;" value="确定">
				</div>
				<br />
				<div class="centre">
					<?= show_page('?page=', $page, $pages, $count, '&fwValue=' . $cat . '&cat=' . $cat . '&ItemNo=' . ''); ?>

				</div>
			</div>

		</div>
	</div>
	<div id="FooterDiv">
		<div id="FooterWrapDiv">

		</div>
	</div>
	<div style="display:none">
		<p><label class="text-info">invoice_num:</label>　<label id="form_invoice_num"></label></p>
	</div>
	
	<div style="display:none">
		<p><label class="text-info">invoice_name</label>　<label id="form_invoice_name"></label></p>
	</div>
	
	<div style="display:none">
		<p><label class="text-info">invoice_amount</label>　<label id="form_amount"></label></p>
	</div>
	<div style="display:none">
		<p><label class="text-info">payment_amount</label>　<label id="form_payment_amount"></label></p>
	</div>
	<div style="display:none">
		<p><label class="text-info">wait_amount</label>　<label id="form_wait_amount"></label></p>
	</div>
	<div style="display:none">
		<p><label class="text-info">invoice_date</label>　<label id="form_invoice_date"></label></p>
	</div>



	<div style="display:none">
		<p><input type="hidden" name="cat" id="cat" value="<?= $cat ?>" /></p>
		<p><input type="hidden" name="fwValue" value="<?= $fwValue ?>" id="fwValue" /></p>
	</div>


</body>

</html>