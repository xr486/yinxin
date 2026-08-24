<?php 

include('includes/login2.inc');

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
$order_number  = isset($_REQUEST['order_number']) ? $_REQUEST['order_number'] : '';	
$ItemNo  = isset($_REQUEST['ItemNo']) ? $_REQUEST['ItemNo'] : '';	  
$where = '';

if($order_number) {
   $where .= ' and a.order_number like "%'.$order_number.'%"';
}

if($ItemNo) {
   $where .= ' and a.customer_order_number like "%'.$ItemNo.'%"';
}
 

if($cat) {
	
     
	$where .= "and a.customer_code='". $cat . "'"; 

}
 
$num = 10;
$off = $num*($page-1);
 
$count = db_sql('SELECT a.order_number,b.line,b.stockid,b.item_name,term_name, customer_order_number,a.creation_date, b.line_amount,  b.invoice_amount, 
               b.line_amount - b.invoice_amount   as wait_invoice_amount
          FROM  so_headers_all a,so_lines_all b
         WHERE a.order_number=b.order_number and   b.invoice_amount>0
                '.$where.'',3);

$pages = ceil($count/$num);
$sql = 'SELECT a.order_number,b.line,b.stockid,b.item_name,term_name, customer_order_number,a.creation_date, b.line_amount,  b.invoice_amount, 
               b.line_amount - b.invoice_amount   as wait_invoice_amount
          FROM  so_headers_all a,so_lines_all b
         WHERE a.order_number=b.order_number and   b.invoice_amount>0 and 1=1 '.$where.' ORDER BY a.order_number   limit '.$off.','.$num.' ';
   
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
        $(document).ready(function(){           
            var api = frameElement.api, W = api.opener;
 

		 $("#ck_company tr").slice(1).click(function () {
                var chks = $("input[type='radio']",this);
                var tag = $(this).attr("tag");
                if(tag=="selected"){
                    // 之前已选中，设置为未选中
                    $(this).attr("tag","");
                    chks.prop("checked",false);
                    console.log('on');
                }else{
                    var rel = $(this).children("td:last-child").children(".coupons").attr("rel");
									console.log($(this).children("td:last-child").children(".coupons").attr("rel"));
				c = rel.split(":");
				$("#form_order_number").text(c[0]);
				$("#form_line_amount").text(c[1]);
				$("#form_invoice_amount").text(c[2]);
				 $("#form_so_line").text(c[3]);
   			     $("#form_item_no").text(c[4]);
				 $("#form_item_name").text(c[5]);
				 $("#form_customer_order_number").text(c[6]);				
	W.document.getElementById('text_slect_order_number' +$('#fwValue').val()).value = $("label#form_order_number").text();			
	W.document.getElementById('text_slect_line_amount'+$('#fwValue').val()).value = $("label#form_line_amount").text();
	W.document.getElementById('text_slect_invoice_amount'+$('#fwValue').val()).value = $("label#form_invoice_amount").text();
    W.document.getElementById('text_slect_so_line'+$('#fwValue').val()).value = $("label#form_so_line").text();
	W.document.getElementById('text_slect_item_no'+$('#fwValue').val()).value = $("label#form_item_no").text();
	W.document.getElementById('text_slect_item_name'+$('#fwValue').val()).value = $("label#form_item_name").text();
    W.document.getElementById('text_slect_customer_order_number'+$('#fwValue').val()).value = $("label#form_customer_order_number").text();
				$("#xianshi").css("display","block");
						api.close();
                }
            });

        });
    </script>
<div id="CanvasDiv">
 
	<div id="BodyDiv">
		<div id="BodyWrapDiv">
			<p class="page_title_text">
				<img src="/css/xenos/images/magnifier.png" title="查询待立账采购单" alt="查询待立账采购单">查询待立账采购单
			</p>
			<form action="./SearchInvoiceSo.php?fwValue=<?=$cat?>&cat=<?=$cat?>" method ="POST">
				<div>
					 
					<table cellpadding="3" class="selection">
					<tr>
				 
					    <td> 订单号： </td>
						<td> <input type="text" name="order_number" value="<?=$order_number?>"> </td>
						<td> 料号： </td>
						<td> <input type="text" name="ItemNo" value="<?=$ItemNo?>"> </td>
						
					</tr>
					
					
					</table>
					<div class="centre">
					     
						<input type="submit" value="查找">
					</div>
					</form>
					<br/>
					<table cellpadding="2" class="selection" id="ck_company">
					<tr>
					  <th width="150">销售单号</th>
                       <th width="10" >客户订单</th>
                       <th width="10" >订单行</th>
                       <th width="10" >产品图号</th>
                       <th width="10" >产品名称</th>
                       <th width="10" >付款条件</th>
					   <th width="10" >订单金额</th>
					   <th width="10" >已开票金额</th>  
					</tr>
					<?php foreach($list as $arr=>$row2){?>
			    
					<tr class="EvenTableRows"> 
					   <td> <?=$row2['order_number']?> </td>
                        <td> <?=$row2['customer_order_number']?> </td>
						  <td> <?=$row2['line']?> </td>
						  <td> <?=$row2['stockid']?> </td> 
						  <td> <?=$row2['item_name']?> </td>   
                        <td> <?=$row2['term_name']?> </td>
					 
						<td> <?=$row2['line_amount']?> </td>
					    <td> <?=$row2['invoice_amount']?> </td> 
						</td>
						<td>
						<input name="a" type="hidden" value="选择" class="coupons" rel="<?=$row2['order_number']?>:<?=$row2['line_amount']?>:<?=$row2['invoice_amount']?>:<?=$row2['line']?>:<?=$row2['stockid']?>:<?=$row2['item_name']?>:<?=$row2['customer_order_number']?>">				 
						</td>
 
					</tr>

					
					<?php }?>
			 
					</table>
					<br/>
					<div class="centre">
					<?=show_page('?page=',$page,$pages,$count,'&fwValue='.$cat.'&cat='.$cat.'&ItemNo='.$ItemNo.'&order_number='.$order_number.'');?>
					
					</div>
				</div>
			
		</div>
	</div>
	<div id="FooterDiv">
		<div id="FooterWrapDiv">
			 
		</div>
	</div>
                  <div style="display:none">
		<p><label class="text-info">order_number</label>　<label id="form_order_number"></label></p>
	</div>
	
	<div style="display:none">
		<p><label class="text-info">term_name</label>　<label id="form_term_name"></label></p>
	</div>
	
	<div style="display:none">
		<p><label class="text-info">customer_order_number</label>　<label id="form_customer_order_number"></label></p>
	</div>
	<div style="display:none">
		<p><label class="text-info">so_line</label>　<label id="form_so_line"></label></p>
	</div>
	<div style="display:none">
		<p><label class="text-info">item_no</label>　<label id="form_item_no"></label></p>
	</div>
	<div style="display:none">
		<p><label class="text-info">item_name</label>　<label id="form_item_name"></label></p>
	</div>
	<div style="display:none">
		<p><label class="text-info">line_amount</label>　<label id="form_line_amount"></label></p>
	</div>
	<div style="display:none">
		<p><label class="text-info">invoice_amount</label>　<label id="form_invoice_amount"></label></p>
	</div>
                     
                    <div style="display:none">
                        <p><input type="hidden" name="cat" id="cat" value="<?=$cat?>"/></p>
						<p><input type="hidden" name="fwValue" value="<?=$fwValue?>" id="fwValue"/></p>
                    </div>
  
 
</body>
</html>

