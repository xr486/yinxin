<?php 

/*$db_host='localhost';
$db_user='a0316105426';
$db_pass='1ea5bca3';
$db_name='a0316105426';
$con=mysql_connect($db_host,$db_user,$db_pass);
mysql_query("set names 'utf8'");
if(!$con)die(mysql_error()); 
mysql_select_db($db_name); 
*/

$db_host='localhost';
$db_user='a0316105426';
$db_pass='1ea5bca3';
$db_name='a0316105426';
$con=mysql_connect($db_host,$db_user,$db_pass);
mysql_query("set names 'utf8'");
if(!$con)die(mysql_error()); 
mysql_select_db($db_name); 
function db_sql($sql,$type=1){
    $arr = array(); 
    $sql = mysql_query($sql);
   // $row = mysql_fetch_array($sql);--这里执行会导致type=2再执行一次，里面第一行资料被捞过从第二行开始获取,结果少资料
	if ($type==1) return mysql_fetch_array($sql);
	if ($type==2) {
	    while ($row=mysql_fetch_array($sql)) {
			$arr[]=$row;
		}
		return $arr;
	}
	if ($type==3)   
		return mysql_num_rows($sql)   ;
    return array();
}



$fwValue = isset($_REQUEST['fwValue']) ? $_REQUEST['fwValue'] : '';	
$cat     = isset($_REQUEST['cat']) ? $_REQUEST['cat'] : 'buliao';	
$page    = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;	
$OrderNumber  = isset($_REQUEST['OrderNumber']) ? $_REQUEST['OrderNumber'] : '';	
$ItemNo  = isset($_REQUEST['ItemNo']) ? $_REQUEST['ItemNo'] : '';	
$delivery_num = isset($_REQUEST['delivery_num']) ? $_REQUEST['delivery_num'] : '';	 
$where = '';

if($OrderNumber) {
   $where .= ' and order_number like "%'.$OrderNumber.'%"';
}

if($ItemNo) {
   $where .= ' and item_no like "%'.$ItemNo.'%"';
}
 
if($delivery_num) {
   $where .= ' and sh.delivery_num like "%'.$delivery_num.'%"';
}
if($cat) {
	
     
	$where .= "and sh.customer_code='". $cat . "' and   check_flag='Y'  "; 

}
 
$num = 10;
$off = $num*($page-1);
 
$count = db_sql('select sl.delivery_num, sl.deliveryline, si.item_no, sl.so_order_number order_number, sl.so_line_no line, sl.price, si.units, sl.delivery_quantity, sl.check_amount,  sl.alreadyinvoiceamount 
FROM so_delivery_headers_all sh, so_delivery_all sl, sf_item_no si
WHERE sh.delivery_num = sl.delivery_num
AND sh.customer_code = sl.customer_code
and sl.alreadyinvoiceamount >0
AND sl.stockid = si.item_no and  '.$where.'',3);

$pages = ceil($count/$num);
$sql = 'select sl.delivery_num, sl.deliveryline, si.item_no, sl.so_order_number order_number, sl.so_line_no line, sl.price, si.units, sl.delivery_quantity, sl.check_amount,  sl.alreadyinvoiceamount 
FROM so_delivery_headers_all sh, so_delivery_all sl, sf_item_no si
WHERE sh.delivery_num = sl.delivery_num
AND sh.customer_code = sl.customer_code
and sl.alreadyinvoiceamount >0
AND sl.stockid = si.item_no and 1=1 '.$where.' ORDER BY sh.delivery_num  desc limit '.$off.','.$num.' ';
   
//echo $sql;
 
$list = db_sql($sql,2);
 

function show_page($url,$page,$pages,$total,$t0=''){  
	$str = '';
	$page = $page > $pages ? $pages : $page;
	if ($page>1) {
        $str .= '<a class="pre" href="'.$url.(1).$t0.'">上一页</a>&nbsp;';
	} else {
	    $str .= '<a class="pre">上一页</a>&nbsp;';
	}
    if ($page<5) $start=1; $end=5;
	if ($page>=5){
	   $start = $page-2;
	   $end = $page+3;
	}
	$end = $end > $pages ? $pages : $end;
	for ($i=$start;$i<=$end;$i++) {
		if ($i==$page) {
		    $str .= '<span class="cur">'.$i.'</span>&nbsp;';
		} else {
		    $str .= '<a href="'.$url.$i.$t0.'">'.$i.'</a>&nbsp;&nbsp;';
		}
    }
    if ($page>=1 && $page<$pages) {
		$str .= '<a href="'.$url.($page+1).$t0.'">下一页</a>&nbsp;';
	} else {   
	    $str .= '<a class="next">下一页</a>&nbsp;';
	}
	$str .= '<span class="pages_c">页次:'.$page.'/'.$pages.'&nbsp;&nbsp;&nbsp;总计:'.$total.' </span>';
    return $str;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
			"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>

<title>查询待出货订单</title>
<link rel="shortcut icon" href="/JXC/favicon.ico"/>
<link rel="icon" href="/JXC/favicon.ico"/>
<meta http-equiv="Content-Type" content="application/html; charset=utf-8"/>
<link href="/JXC/css/xenos/default.css" rel="stylesheet" type="text/css"/>
<script type="text/javascript" src ="/JXC/javascripts/miscfunctions.js"></script>
<script type="text/javascript" src ="/JXC/javascripts/wdatepicker.js"></script>
<script src="/JXC/javascript/jquery-1.10.2.min.js"></script>
    <script src="/JXC/javascript/bootstrap.min.js"></script>
    <script src="/JXC/javascript/jquery.dataTables.js"></script>
	<script src="/JXC/javascript/jquery.livequery.js"></script>
</head>
<body>


<script type="text/javascript">
        $(document).ready(function(){           
            var api = frameElement.api, W = api.opener;
api.button({
    id:'valueOk',
    name:'确定',
	focus: true,
    callback:ok
});

    
            function ok()
            { 
                switch ($('#cat').val()){
                    case 'buliao':
                        W.document.getElementById('text_slect_so_number'+$('#fwValue').val()).value = $("label#form_so_number").text(); 
					    W.document.getElementById('text_slect_so_line'+$('#fwValue').val()).value = $("label#form_so_line").text(); 
					    W.document.getElementById('text_slect_units'+$('#fwValue').val()).value = $("label#form_units").text();
						W.document.getElementById('text_slect_ItemNo'+$('#fwValue').val()).value = $("label#form_ItemNo").text();
						W.document.getElementById('text_slect_delivery_quantity'+$('#fwValue').val()).value = $("label#form_delivery_quantity").text();  
						W.document.getElementById('text_slect_price'+$('#fwValue').val()).value = $("label#form_price").text();  
						W.document.getElementById('text_slect_check_amount'+$('#fwValue').val()).value = $("label#form_check_amount").text();
						W.document.getElementById('text_slect_alreadyinvoiceamount'+$('#fwValue').val()).value = $("label#form_alreadyinvoiceamount").text();
						W.document.getElementById('text_slect_wait_amount'+$('#fwValue').val()).value = $("label#form_wait_amount").text();
						W.document.getElementById('text_slect_delivery_num'+$('#fwValue').val()).value = $("label#form_delivery_num").text(); 
						W.document.getElementById('text_slect_deliveryline'+$('#fwValue').val()).value = $("label#form_deliveryline").text(); 
						
						
						$("#xianshi").css("display","block"); 
                        break;
                    default :
                        alert('Data Post Error');
                }
            };
            
			
			$(".coupons").livequery("click", function() {
				var rel = this.getAttribute('rel');
				c = rel.split(":");
				
				$("#form_so_number").text(c[0]);
				$("#form_so_line").text(c[1]);
				$("#form_ItemNo").text(c[2]);
				$("#form_units").text(c[3]);
				$("#form_delivery_quantity").text(c[4]);
				$("#form_price").text(c[5]); 
				$("#form_check_amount").text(c[6]);				
				$("#form_alreadyinvoiceamount").text(c[7]); 
				$("#form_wait_amount").text(c[8]); 
				$("#form_delivery_num").text(c[9]); 
				$("#form_deliveryline").text(c[10]); 
				
				
			});
			 
			
			
        });
    </script>
<div id="CanvasDiv">
 
	<div id="BodyDiv">
		<div id="BodyWrapDiv">
			<p class="page_title_text">
				<img src="/JXC/css/xenos/images/magnifier.png" title="查询立账出货单" alt="查询立账出货单">查询立账出货单
			</p>
			<form action="/JXC/SearchNoInvoiceship.php?fwValue=<?=$cat?>&cat=<?=$cat?>" method ="POST">
				<div>
					 
					<table cellpadding="3" class="selection">
					<tr>
					   <td> 出货单： </td>
						<td> <input type="text" name="delivery_num" value="<?=$delivery_num?>"> </td>
					    <td> 订单号： </td>
						<td> <input type="text" name="OrderNumber" value="<?=$OrderNumber?>"> </td>
						<td> 料号： </td>
						<td> <input type="text" name="ItemNo" value="<?=$ItemNo?>"> </td>
						
					</tr>
					
					
					</table>
					<div class="centre">
					     
						<input type="submit" value="查找">
					</div>
					</form>
					<br/>
					<table cellpadding="2" class="selection">
					<tr>
					    <th  width ="150">出货单 </th>
						<th   width ="20"> 行 </th>	
					    <th  width ="150"> 订单号码 </th>
						<th   width ="20"> 行 </th>						
						<th   width ="150"> 料号 </th> 
						<th   width ="50"> 单位 </th> 
						<th   width ="80"> 出货数量 </th>
						<th   width ="80"> 单价 </th>
、                      <th   width ="80"> 确认金额 </th>
						<th  width ="80"> 已立账金额 </th>
						<th class="ascending" width ="50"> 操作 </th>
					</tr>
					<?php foreach($list as $arr=>$row2){?>
					<tr class="EvenTableRows"> 
					    <td> <?=$row2['delivery_num']?> </td>  
				  		<td> <?=$row2['deliveryline']?> </td>   
					    <td> <?=$row2['order_number']?> </td>
						<td><?=$row2['line']?>	</td>
						<td><?=$row2['item_no']?> </td> 
						<td> <?=$row2['units']?> </td>
						<td> <?=$row2['delivery_quantity']?> </td>
						<td> <?=$row2['price']?> </td>
						<td> <?=$row2['check_amount']?> </td>
						<td> <?=$row2['alreadyinvoiceamount']?> </td>				 
						
						</td>
						<td>

						<input name="a" type="radio" value="选择" class="coupons" rel="<?=$row2['order_number']?>:<?=$row2['line']?>:<?=$row2['item_no']?>:<?=$row2['units']?>:<?=$row2['delivery_quantity']?>:<?=$row2['price']?>:<?=$row2['check_amount']?>:<?=$row2['alreadyinvoiceamount']?>:<?=$row2['alreadyinvoiceamount'] ?>:<?=$row2['delivery_num']?>:<?=$row2['deliveryline']?>">				 
						</td>
					</tr>

					
					<?php }?>
			 
					</table>
					<br/>
					<div class="centre">
					<?=show_page('?page=',$page,$pages,$count,'&fwValue='.$cat.'&cat='.$cat.'&ItemNo='.$ItemNo.'&check_amount'.$check_amount.'');?>
					
					</div>
				</div>
			
		</div>
	</div>
	<div id="FooterDiv">
		<div id="FooterWrapDiv">
			 
		</div>
	</div>
                   <div style="display:none">
                         <p><label class="text-info">delivery_num:</label>　<label id="form_delivery_num"></label></p>
                    </div>
					<div style="display:none">
                         <p><label class="text-info">deliveryline:</label>　<label id="form_deliveryline"></label></p>
                    </div>
					<div style="display:none">
                         <p><label class="text-info">OrderNum:</label>　<label id="form_so_number"></label></p>
                    </div>
					<div style="display:none">
                         <p><label class="text-info">SoLine:</label>　<label id="form_so_line"></label></p>
                    </div>
				   <div style="display:none">
                         <p><label class="text-info">ItemNo:</label>　<label id="form_ItemNo"></label></p>
                    </div>

					 <div style="display:none">
                         <p><label class="text-info">Units:</label>　<label id="form_units"></label></p>
                    </div>
					<div style="display:none">
                         <p><label class="text-info">delivery_quantity</label>　<label id="form_delivery_quantity"></label></p>
                    </div>
					<div style="display:none">
                         <p><label class="text-info">Price:</label>　<label id="form_price"></label></p>
                    </div>

					<div style="display:none">
                         <p><label class="text-info">check_amount</label>　<label id="form_check_amount"></label></p>
                    </div>
					<div style="display:none">
                         <p><label class="text-info">alreadyinvoiceamount</label>　<label id="form_alreadyinvoiceamount"></label></p>
                    </div>

					
					<div style="display:none">
                         <p><label class="text-info">wait_amount:</label>　<label id="form_wait_amount"></label></p>
                    </div>
					
                     
                    <div style="display:none">
                        <p><input type="hidden" name="cat" id="cat" value="<?=$cat?>"/></p>
						<p><input type="hidden" name="fwValue" value="<?=$fwValue?>" id="fwValue"/></p>
                    </div>
  
 
</body>
</html>

