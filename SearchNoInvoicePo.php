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
$po_num  = isset($_REQUEST['po_num']) ? $_REQUEST['po_num'] : '';	
$ItemNo  = isset($_REQUEST['ItemNo']) ? $_REQUEST['ItemNo'] : '';	  
$where = '';

if($po_num) {
   $where .= ' and c.po_num like "%'.$po_num.'%"';
}

 

if($cat) {
	
     
	$where .= "and c.vendor_code='". $cat . "' and   c.status='已签核' "; 

}
 
$num = 10;
$off = $num*($page-1);
 
$count = db_sql('SELECT  b.*,a.stockid
				FROM  po_headers_all c,po_lines_all a, po_rcv_receipt_line b
				WHERE  a.po_num=c.po_num  and a.po_num=b.po_num and a.line=b.po_line and  b.invoice_amount>0
                '.$where.'',3);

$pages = ceil($count/$num);
$sql = 'SELECT  b.*,a.stockid 
				FROM  po_headers_all c,po_lines_all a, po_rcv_receipt_line b
				WHERE  a.po_num=c.po_num  and a.po_num=b.po_num and a.line=b.po_line and  b.invoice_amount>0 and 1=1 '.$where.' ORDER BY c.po_num limit '.$off.','.$num.' ';
   
//  echo $sql;

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

<title>查询待立账采购单</title>
<link rel="shortcut icon" href="/JXC/favicon.ico"/>
<link rel="icon" href="/JXC/favicon.ico"/>
<meta http-equiv="Content-Type" content="application/html; charset=utf-8"/>
<link href="css/xenos/default.css" rel="stylesheet" type="text/css"/>
<script type="text/javascript" src ="javascripts/miscfunctions.js"></script>
<script type="text/javascript" src ="javascripts/wdatepicker.js"></script>
<script src="javascript/jquery-1.10.2.min.js"></script>
    <script src="javascript/bootstrap.min.js"></script>
    <script src="javascript/jquery.dataTables.js"></script>
	<script src="javascript/jquery.livequery.js"></script>
</head>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">



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
				$("#form_po_num").text(c[0]);
				$("#form_po_line").text(c[1]);  
                 $("#form_receipt_num").text(c[2]);
					$("#form_receipt_line").text(c[3]);
					$("#form_item_no").text(c[4]);
					$("#form_stockid").text(c[5]);
				$("#form_invoice_amount").text(c[6]); 
				W.document.getElementById('text_slect_po_num' + $('#fwValue').val()).value = $("label#form_po_num").text();
					W.document.getElementById('text_slect_po_line' + $('#fwValue').val()).value = $("label#form_po_line").text();
				W.document.getElementById('text_slect_receipt_num' + $('#fwValue').val()).value = $("label#form_receipt_num").text();
					W.document.getElementById('text_slect_receipt_line' + $('#fwValue').val()).value = $("label#form_receipt_line").text();
					W.document.getElementById('text_slect_item_no' + $('#fwValue').val()).value = $("label#form_item_no").text();
					W.document.getElementById('text_slect_stockid' + $('#fwValue').val()).value = $("label#form_stockid").text();
					W.document.getElementById('text_slect_invoice_amount' + $('#fwValue').val()).value = $("label#form_invoice_amount").text();
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
				<img src="./css/xenos/images/magnifier.png" title="查询待立账采购单" alt="查询待立账采购单">查询待立账采购单
			</p>
			<form action="./SearchNoInvoicePo.php?fwValue=<?=$cat?>&cat=<?=$cat?>" method ="POST">
				<div>
					 
					<table cellpadding="3" class="selection">
					<tr>
				 
					    <td> 采购订单号： </td>
						<td> <input type="text" name="po_num" value="<?=$po_num?>"> </td>
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
					 <th class="ascending" width="10"> 采购单号 </th>
							<th width="50"> 采购行 </th>
							<th width="50"> 入库单 </th>
							<th width="50"> 入库单行 </th>
							<th width="50"> 料号 </th>
							<th width="50"> 规格型号 </th> 
							<th width="50"> 已开票金额 </th> 
					</tr>
					<?php foreach($list as $arr=>$row2){?>
			    
					<tr class="EvenTableRows"> 
					   <td> <?=$row2['po_num']?> </td>
						<td><?=$row2['po_line']?>	</td> 
					  <td> <?= $row2['receipt_num'] ?> </td> 
								<td> <?= $row2['receipt_line'] ?> </td> 
								<td> <?= $row2['stockid'] ?> </td> 
								<td> <?= $row2['stockid'] ?> </td>  
					    <td> <?=$row2['invoice_amount']?> </td>   
						</td>
						<td>

						<input name="a" type="hidden" value="选择" class="coupons" rel="<?=$row2['po_num']?>:<?=$row2['po_line']?>:<?=$row2['receipt_num']?>:<?=$row2['receipt_line']?>:<?=$row2['stockid']?>:<?=$row2['stockid']?>:<?=$row2['invoice_amount']?> ">	 
						</td>
					</tr>

					
					<?php }?>
			 
					</table>
					<br/>
					<div class="centre">
					<?=show_page('?page=',$page,$pages,$count,'&fwValue='.$cat.'&cat='.$cat.'&ItemNo='.$ItemNo.'&po_num='.$po_num.'');?>
					
					</div>
				</div>
			
		</div>
	</div>
	 
       <div style="display:none">
		<p><label class="text-info">po_num:</label>　<label id="form_po_num"></label></p>
	</div>
	
	 
	<div style="display:none">
		<p><label class="text-info">invoice_amount</label>　<label id="form_invoice_amount"></label></p>
	</div>
 
	<div style="display:none">
		<p><label class="text-info">po_line</label>　<label id="form_po_line"></label></p>
	</div>
	
	<div style="display:none">
		<p><label class="text-info">receipt_num</label>　<label id="form_receipt_num"></label></p>
	</div>
	
	<div style="display:none">
		<p><label class="text-info">receipt_line</label>　<label id="form_receipt_line"></label></p>
	</div>
	<div style="display:none">
		<p><label class="text-info">item_no</label>　<label id="form_item_no"></label></p>
	</div>
	
	<div style="display:none">
		<p><label class="text-info">stockid</label>　<label id="form_stockid"></label></p>
	</div>
                    <div style="display:none">
                        <p><input type="hidden" name="cat" id="cat" value="<?=$cat?>"/></p>
						<p><input type="hidden" name="fwValue" value="<?=$fwValue?>" id="fwValue"/></p>
                    </div>
  
 
</body>
</html>

