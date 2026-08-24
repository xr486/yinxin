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
$quantity_accepted = isset($_REQUEST['quantity_accepted']) ? $_REQUEST['quantity_accepted'] : '';	 
$where = '';

if($po_num) {
   $where .= ' and po_num like "%'.$po_num.'%"';
}

if($ItemNo) {
   $where .= ' and item_no like "%'.$ItemNo.'%"';
}
 

if($cat) {
	
     
	$where .= "and sh.vendor_code='". $cat . "' and   sh.status='APPROVED' "; 

}
 
$num = 10;
$off = $num*($page-1);
 
$count = db_sql('SELECT sh.po_num, sl.line, si.item_no, sl.price, si.units, sl.quantity_received, sl.quantity_accepted, sl.quantity_deliveried,sl.quantity_billed, si.item_desc
FROM po_headers_all sh, po_lines_all sl, sf_item_no si
WHERE sh.po_num = sl.po_num
AND sl.quantity_billed>0
AND sl.stockid = si.item_no and  '.$where.'',3);

$pages = ceil($count/$num);
$sql = 'SELECT sh.po_num, sl.line, si.item_no, sl.price, si.units, sl.quantity_received, sl.quantity_accepted, sl.quantity_deliveried,sl.quantity_billed, si.item_desc
FROM po_headers_all sh, po_lines_all sl, sf_item_no si
WHERE sh.po_num = sl.po_num
AND sl.quantity_billed>0
AND sl.stockid = si.item_no and 1=1 '.$where.' ORDER BY sh.po_num, sl.line  limit '.$off.','.$num.' ';
   
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

<title>查询待退账采购单</title>
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
                        W.document.getElementById('text_slect_po_num'+$('#fwValue').val()).value = $("label#form_po_num").text(); 
					    W.document.getElementById('text_slect_po_line'+$('#fwValue').val()).value = $("label#form_po_line").text(); 
					    W.document.getElementById('text_slect_units'+$('#fwValue').val()).value = $("label#form_units").text();
						W.document.getElementById('text_slect_ItemNo'+$('#fwValue').val()).value = $("label#form_ItemNo").text();
						W.document.getElementById('text_slect_price'+$('#fwValue').val()).value = $("label#form_price").text();  
						W.document.getElementById('text_slect_quantity_received'+$('#fwValue').val()).value = $("label#form_quantity_received").text();		
						W.document.getElementById('text_slect_quantity_accepted'+$('#fwValue').val()).value = $("label#form_quantity_accepted").text(); 
						W.document.getElementById('text_slect_quantity_deliveried'+$('#fwValue').val()).value = $("label#form_quantity_deliveried").text();	
						W.document.getElementById('text_slect_quantity_billed'+$('#fwValue').val()).value = $("label#form_quantity_billed").text(); 
						W.document.getElementById('text_slect_wait_quantity'+$('#fwValue').val()).value = $("label#form_wait_quantity").text();
						W.document.getElementById('text_slect_wait_amount'+$('#fwValue').val()).value = $("label#form_wait_amount").text();
						
						
						$("#xianshi").css("display","block"); 
                        break;
                    default :
                        alert('Data Post Error');
                }
            };
            
			
			$(".coupons").livequery("click", function() {
				var rel = this.getAttribute('rel');
				c = rel.split(":");
				
				$("#form_po_num").text(c[0]);
				$("#form_po_line").text(c[1]);
				$("#form_ItemNo").text(c[2]);
				$("#form_units").text(c[3]);				
				$("#form_price").text(c[4]); 
				$("#form_quantity_received").text(c[5]);
				$("#form_quantity_accepted").text(c[6]);
				$("#form_quantity_deliveried").text(c[7]);
				$("#form_quantity_billed").text(c[8]);				
				$("#form_wait_quantity").text(c[9]); 
				$("#form_wait_amount").text(c[10]); 							
				
			});
			 
			
			
        });
    </script>
<div id="CanvasDiv">
 
	<div id="BodyDiv">
		<div id="BodyWrapDiv">
			<p class="page_title_text">
				<img src="/JXC/css/xenos/images/magnifier.png" title="查询待立账采购单" alt="查询待立账采购单">查询待立账采购单
			</p>
			<form action="/JXC/SearchAlreadyInvoicePo.php?fwValue=<?=$cat?>&cat=<?=$cat?>" method ="POST">
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
					<table cellpadding="2" class="selection">
					<tr>
					    <th width="150">采购单号</th>
				    	<th width="10">行</th>  
					 	<th width="110">料号</th> 
					 	<th width="10" >单位</th>
					 	<th width="10" >单价</th> 
					 	<th width="50">来料报检量</th>
					 	<th width="50">检验量</th> 
					 	<th width="50">入库量</th> 
						<th width="80">已立账数量</th>
、                      <th width ="80">可退账数量 </th>
						<th width ="80">可退账金额 </th> 
						<th width ="50">操作 </th>
					</tr>
					<?php foreach($list as $arr=>$row2){?>
			 
					<tr class="EvenTableRows"> 
					   <td> <?=$row2['po_num']?> </td>
						<td><?=$row2['line']?>	</td>
						<td><?=$row2['item_no']?> </td> 
						<td> <?=$row2['units']?> </td>
						<td> <?=$row2['price']?> </td>
						<td> <?=$row2['quantity_received']?> </td>
					    <td> <?=$row2['quantity_accepted']?> </td> 
						<td> <?=$row2['quantity_deliveried']?> </td>
				  		<td> <?=$row2['quantity_billed']?> </td>  
						<td> <?=$row2['quantity_billed']?> </td>						 
						<td> <?=$row2['quantity_billed'] * $row2['price'] ?> </td>
						</td>
						<td>

						<input name="a" type="radio" value="选择" class="coupons" rel="<?=$row2['po_num']?>:<?=$row2['line']?>:<?=$row2['item_no']?>:<?=$row2['units']?>:<?=$row2['price']?>:<?=$row2['quantity_received']?>:<?=$row2['quantity_accepted']?>:<?=$row2['quantity_deliveried']?>:<?=$row2['quantity_billed']?>:<?=$row2['quantity_billed']?>:<?=($row2['quantity_billed']) * $row2['price'] ?>">	 
						</td>
					</tr>

					
					<?php }?>
			 
					</table>
					<br/>
					<div class="centre">
					<?=show_page('?page=',$page,$pages,$count,'&fwValue='.$cat.'&cat='.$cat.'&ItemNo='.$ItemNo.'&quantity_received'.$quantity_received.'');?>
					
					</div>
				</div>
			
		</div>
	</div>
	<div id="FooterDiv">
		<div id="FooterWrapDiv">
			 
		</div>
	</div>
                   
					<div style="display:none">
                         <p><label class="text-info">po_num</label>　<label id="form_po_num"></label></p>
                    </div>
					<div style="display:none">
                         <p><label class="text-info">po_line</label>　<label id="form_po_line"></label></p>
                    </div>
				   <div style="display:none">
                         <p><label class="text-info">ItemNo:</label>　<label id="form_ItemNo"></label></p>
                    </div>

					 <div style="display:none">
                         <p><label class="text-info">Units:</label>　<label id="form_units"></label></p>
                    </div>
					<div style="display:none">
                         <p><label class="text-info">Price:</label>　<label id="form_price"></label></p>
                    </div>
					<div style="display:none">
                         <p><label class="text-info">quantity_received</label>　<label id="form_quantity_received"></label></p>
                    </div>

					<div style="display:none">
                         <p><label class="text-info">quantity_accepted:</label>　<label id="form_quantity_accepted"></label></p>
                    </div>
					<div style="display:none">
                         <p><label class="text-info">quantity_deliveried</label>　<label id="form_quantity_deliveried"></label></p>
                    </div>
					<div style="display:none">
                         <p><label class="text-info">quantity_billed:</label>　<label id="form_quantity_billed"></label></p>
                    </div>
					
					<div style="display:none">
                         <p><label class="text-info">wait_quantity</label>　<label id="form_wait_quantity"></label></p>
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

