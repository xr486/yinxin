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
$receipt_num  = isset($_REQUEST['receipt_num']) ? $_REQUEST['receipt_num'] : '';	   
$where = '';

if($receipt_num) 
{
  $where .= ' and receipt_num like "%'.$receipt_num.'%"';
}

 
if($cat) 
{
  $where .= "and  b.vendor_code='". $cat . "'  "; 
}

$num = 10;
$off = $num*($page-1);
 
$count = db_sql('SELECT                     a.receipt_num,a.receipt_line,a.remark,a.transaction_date,a.check_amount,a.invoice_amount,a.invoice_dis_amount,
             a.check_amount - a.invoice_amount-a.invoice_dis_amount as wait_invoice_amount
         FROM po_rcv_receipt_line a,po_rcv_receipt_header b
         WHERE  a.receipt_num=b.receipt_num
		 and a.check_amount <> a.invoice_amount + a.invoice_dis_amount  and 1=1  '.$where. '',3);

$pages = ceil($count/$num);
$sql = 'SELECT                     a.receipt_num,a.receipt_line,a.remark,a.transaction_date,a.check_amount,a.invoice_amount,a.invoice_dis_amount,
             a.check_amount - a.invoice_amount-a.invoice_dis_amount as wait_invoice_amount
         FROM po_rcv_receipt_line a,po_rcv_receipt_header b
         WHERE  a.receipt_num=b.receipt_num
		 and a.check_amount <> a.invoice_amount + a.invoice_dis_amount  and 1=1  '.$where.' ORDER BY  a.receipt_num, a.receipt_line limit '.$off.','.$num.' ';

   
$list = db_sql($sql,2);
echo $sql;

function show_page($url,$page,$pages,$total,$t0='')
{  
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
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>

<title>查询待开票的采购入库单</title>
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
                        W.document.getElementById('text_slect_receipt_num' +$('#fwValue').val()).value = $("label#form_receipt_num").text();
					    W.document.getElementById('text_slect_receipt_line'+$('#fwValue').val()).value = $("label#form_receipt_line").text();
					    W.document.getElementById('text_slect_remark'+$('#fwValue').val()).value = $("label#form_remark").text(); 
					    W.document.getElementById('text_slect_transaction_date'+$('#fwValue').val()).value = $("label#form_transaction_date").text(); 
						W.document.getElementById('text_slect_check_amount'+$('#fwValue').val()).value = $("label#form_check_amount").text();		
						W.document.getElementById('text_slect_invoice_amount'+$('#fwValue').val()).value = $("label#form_invoice_amount").text(); 
						W.document.getElementById('text_slect_invoice_dis_amount'+$('#fwValue').val()).value = $("label#form_invoice_dis_amount").text(); 
						W.document.getElementById('text_slect_wait_invoice_amount'+$('#fwValue').val()).value = $("label#form_wait_invoice_amount").text(); 
						$("#xianshi").css("display","block"); 
                        break;
                    default :
                        alert('Data Post Error');
                }
            };
            
			
			$(".coupons").livequery("click", function() {
				var rel = this.getAttribute('rel');
				c = rel.split(":");
				
				$("#form_receipt_num").text(c[0]);
                $("#form_receipt_line").text(c[1]);
				$("#form_remark").text(c[2]); 
                $("#form_transaction_date").text(c[3]);		 
				$("#form_check_amount").text(c[4]);
				$("#form_invoice_amount").text(c[5]);
				$("#form_invoice_dis_amount").text(c[6]); 			
				$("#form_wait_invoice_amount").text(c[7]);  
			});
			 
			
			
        });
    </script>
<div id="CanvasDiv">
 
	<div id="BodyDiv">
		<div id="BodyWrapDiv">
			<p class="page_title_text">
				<img src="/JXC/css/xenos/images/magnifier.png" title="查询待开票的采购入库单" alt="查询待开票的采购入库单">查询待开票的采购入库单
			</p>
			<form action="/JXC/SearchNoInvoicePo2.php?fwValue=<?=$cat?>&cat=<?=$cat?>" method ="POST">
				<div>
					 
					<table cellpadding="3" class="selection">
					<tr>
				 
					    <td> 采购入库单号： </td>
						<td> <input type="text" name="receipt_num" value="<?=$receipt_num?>"> </td>
						 
						
					</tr>
					
					
					</table>
					<div class="centre">
					     
						<input type="submit" value="查找">
					</div>
					</form>
					<br/>
					<table cellpadding="2" class="selection">
					<tr>
					  <th width="150">采购入库单</th>
					  <th width="20">项目</th> 
					  <th width="200">备注</th> 
					  <th width="70" >入库日期</th> 
					  <th width="70">应开票金额</th>
					  <th width="70">已开票金额</th>  
					  <th width="70">已优惠金额</th>  
					  <th width="70">未开票金额</th>
					  <th width ="50">操作 </th>
					</tr>
					<?php foreach($list as $arr=>$row2){?>
			 
					<tr class="EvenTableRows"> 
		  			  <td> <?=$row2['receipt_num']?> </td>
                      <td> <?=$row2['receipt_line']?> </td>
					  <td> <?=$row2['remark']?>	</td> 
					  <td> <?=date('Y-m-d',$row2['transaction_date'])?> </td> 
					  <td> <?=$row2['check_amount']?> </td>
					  <td> <?=$row2['invoice_amount']?> </td> 
					  <td> <?=$row2['invoice_dis_amount']?> </td>   					 
					  <td> <?=($row2['check_amount']-$row2['invoice_amount'] - $row2['invoice_dis_amount']) ?> </td>
					  </td>
					  <td>

	<input name="a" type="radio" value="选择" class="coupons" rel="<?=$row2['receipt_num']?>:<?=$row2['receipt_line']?>:<?=$row2['remark']?>:<?=date('Y-m-d',$row2['transaction_date'])?>:<?=$row2['check_amount']?>:<?=
	$row2['invoice_amount']?>:<?=$row2['invoice_dis_amount']?>:<?=$row2['check_amount']-$row2['invoice_amount']-$row2['invoice_dis_amount']?>">
	</td>
</tr>

					

					<?php }?>
			 
					</table>
					<br/>
					<div class="centre">
					<?=show_page('?page=',$page,$pages,$count,'&fwValue='.$cat.'&cat='.$cat.'&receipt_num='.$receipt_num.'');?>
					
					</div>
				</div>
			
		</div>
	</div>
	<div id="FooterDiv">
		<div id="FooterWrapDiv">
			 
		</div>
	</div>
                   
					<div style="display:none">
                         <p><label class="text-info">receipt_num</label>　<label id="form_receipt_num"></label></p>
                    </div>
                    
					<div style="display:none">
                         <p><label class="text-info">receipt_line</label>　<label id="form_receipt_line"></label></p>
                    </div>

					<div style="display:none">
                         <p><label class="text-info">remark</label>　<label id="form_remark"></label></p>
                    </div> 

					 <div style="display:none">
                         <p><label class="text-info">transaction_date</label>　<label id="form_transaction_date"></label></p>
                    </div>
				 
					<div style="display:none">
                         <p><label class="text-info">check_amount</label>　<label id="form_check_amount"></label></p>
                    </div>

					<div style="display:none">
                         <p><label class="text-info">invoice_amount:</label>　<label id="form_invoice_amount"></label></p>
                    </div>
					<div style="display:none">
                         <p><label class="text-info">invoice_dis_amount</label>　<label id="form_invoice_dis_amount"></label></p>
                    </div> 
					<div style="display:none">
                         <p><label class="text-info">wait_invoice_amount</label>　<label id="form_wait_invoice_amount"></label></p>
                    </div>
                     
                    <div style="display:none">
                        <p><input type="hidden" name="cat" id="cat" value="<?=$cat?>"/></p>
						<p><input type="hidden" name="fwValue" value="<?=$fwValue?>" id="fwValue"/></p>
                    </div>
  
 
</body>
</html>


 