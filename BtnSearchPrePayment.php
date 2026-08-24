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
$vendor_code  = isset($_REQUEST['vendor_code']) ? $_REQUEST['vendor_code'] : '';	
$transaction_num = isset($_REQUEST['transaction_num']) ? $_REQUEST['transaction_num'] : '';	 
$where = '';
if($vendor_code) {
   $where .= ' and a.vendor_code like "%'.$vendor_code.'%"';
}
 
if($transaction_num) {
   $where .= ' and transaction_num like "%'.$transaction_num.'%"';
}
if($cat) {
	
   $where .= " and b.transaction_type='AP预付款' and b.status='核准' and b.pre_amount>b.transaction_amount>0 "; 
	

}
 
$num = 10;
$off = $num*($page-1);
 
$count = db_sql('select a.vendor_code,a.vendor_name,b.currency_code,b.pre_amount,b.transaction_num,b.transaction_amount,b.transaction_date from  vendors a,fin_bank_transaction_headers_all b where  a.vendor_code=b.vendor_code '.$where.'',3);

$pages = ceil($count/$num);
$sql = 'select b.bankchangenum,a.vendor_code,a.vendor_name,b.currency_code,(b.pre_amount-b.transaction_amount) wait_amount,b.pre_amount,b.transaction_num,b.transaction_amount,b.transaction_date from  vendors a,fin_bank_transaction_headers_all b where  a.vendor_code=b.vendor_code '.$where.' ORDER BY a.vendor_code  desc limit '.$off.','.$num.'';
// echo $sql;
 
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

<title>查询预付款单</title>
<link rel="shortcut icon" href="/JXC/favicon.ico"/>
<link rel="icon" href="/JXC/favicon.ico"/>
<meta http-equiv="Content-Type" content="application/html; charset=utf-8"/>
<link href="css/xenos/default.css" rel="stylesheet" type="text/css"/>
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
				$("#form_vendor_code").text(c[0]);
				$("#form_name").text(c[1]);
				$("#form_currency_code").text(c[2]);
				$("#form_transaction_date").text(c[3]); 
				 $("#form_transaction_num").text(c[4]); 
				 $("#form_transaction_amount").text(c[5]); 
				 $("#form_pre_amount").text(c[6]); 
				 $("#form_wait_amount").text(c[7]);
				 $("#form_bankchangenum").text(c[8]);
				W.document.getElementById('text_slect_vendor_code'+$('#fwValue').val()).value = $("label#form_vendor_code").text();
				W.document.getElementById('text_slect_vendor_name'+$('#fwValue').val()).value = $("label#form_name").text();
				W.document.getElementById('text_slect_currency_code'+$('#fwValue').val()).value = $("label#form_currency_code").text();
				W.document.getElementById('text_slect_transaction_date'+$('#fwValue').val()).value = $("label#form_transaction_date").text();
				$("#xianshi").css("display","block");
				W.document.getElementById('text_slect_transaction_num'+$('#fwValue').val()).value = $("label#form_transaction_num").text();
				W.document.getElementById('text_slect_transaction_amount'+$('#fwValue').val()).value = $("label#form_transaction_amount").text();
				W.document.getElementById('text_slect_pre_amount'+$('#fwValue').val()).value = $("label#form_pre_amount").text();
				W.document.getElementById('text_slect_wait_amount'+$('#fwValue').val()).value = $("label#form_wait_amount").text();
				W.document.getElementById('text_slect_bankchangenum'+$('#fwValue').val()).value = $("label#form_bankchangenum").text();
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
				<img src="/JXC/css/xenos/images/magnifier.png" title="查询预付款单" alt="查询预付款单">查询预付款单
			</p>
			<form action="./BtnSearchPrePayment.php?fwValue=<?=$cat?>&cat=<?=$cat?>" method ="POST">
				<div>
					  
				<div class="text-nav2">
                            <div class="text-nav-1 ">
                            <div>供应商代号：
						</div>
						
							<input type="text" name="vendor_code" value="<?=$vendor_code?>">
						</div>
						<div class="text-nav-1 ">
                            <div>预收款单号：
						</div> <input type="text" name="transaction_num" value="<?=$transaction_num?>">
						</div>
					</div>
					
					
					</table>
					<div class="centre">
					     
						<input type="submit" value="查找">
					</div>
					</form>
					<br/>
					<table cellpadding="2" class="selection" id="ck_company">
					<tr>
						<th class="ascending" width ="100">
							供应商代号
						</th>
						<th class="ascending" width ="100"> 预付款单号 </th>
						<th width ="50"> 预付款日 </th>  
 						<th width ="80"> 币别 </th>    
 						<th width ="80"> 预付款金额 </th>    
 						<th width ="80"> 已冲销金额 </th>    
 						<th width ="80"> 待冲销金额 </th> 
						  
					</tr>
					<?php foreach($list as $arr=>$row2){?>
					<tr class="EvenTableRows">
						<td> <?=$row2['vendor_code']?> </td>
						<td> <?=$row2['transaction_num']?> </td>
						<td> <?=date('Y-m-d',$row2['transaction_date'])?> </td>
 						<td> <?=$row2['currency_code']?> </td> 
				        <td> <?=$row2['pre_amount']?> </td> 
				        <td> <?=$row2['transaction_amount']?> </td> 
				        <td> <?=$row2['wait_amount']?> </td> 
						</td>
						<td>
						<input name="a" type="hidden" value="选择" class="coupons" rel="<?=$row2['vendor_code']?>:<?=$row2['vendor_name']?>:<?=$row2['currency_code']?>:<?=date('Y-m-d',$row2['transaction_date'])?>:<?=$row2['transaction_num']?>:<?=$row2['transaction_amount']?>:<?=$row2['pre_amount']?>:<?=$row2['wait_amount']?>:<?=$row2['bankchangenum']?>">				 
						</td>
					</tr>
					<?php }?>
					
					 
					</table>
					<br/>
					<div class="centre">
					<?=show_page('?page=',$page,$pages,$count,'&fwValue='.$cat.'&cat='.$cat.'&vendor_code='.$vendor_code.'&transaction_num'.$transaction_num.'');?>
					
					</div>
				</div>
			
		</div>
	</div>
	<div id="FooterDiv">
		<div id="FooterWrapDiv">
			 
		</div>
	</div>
                   <div style="display:none">
                         <p><label class="text-info">vendor_code:</label>　<label id="form_vendor_code"></label></p>
                    </div>

					 <div style="display:none">
                         <p><label class="text-info">customername</label>　<label id="form_name"></label></p>
                    </div>
					<div style="display:none">
                         <p><label class="text-info">currency_code</label>　<label id="form_currency_code"></label></p>
                    </div>

 					<div style="display:none">  
                           <p><label class="text-info">transaction_date</label>　<label id="form_transaction_date"></label></p>  
                     </div> 
					 <div style="display:none">  
                           <p><label class="text-info">transaction_num</label>　<label id="form_transaction_num"></label></p>  
                     </div>  
					 <div style="display:none">  
                           <p><label class="text-info">transaction_amount</label>　<label id="form_transaction_amount"></label></p>  
                     </div>  
					 <div style="display:none">  
                           <p><label class="text-info">pre_amount</label>　<label id="form_pre_amount"></label></p>  
                     </div>  
					 <div style="display:none">  
                           <p><label class="text-info">wait_amount</label>　<label id="form_wait_amount"></label></p>  
                     </div> 
					 <div style="display:none">  
                           <p><label class="text-info">bankchangenum</label>　<label id="form_bankchangenum"></label></p>  
                     </div> 
    
                     
                    <div style="display:none">
                        <p><input type="hidden" name="cat" id="cat" value="<?=$cat?>"/></p>
						<p><input type="hidden" name="fwValue" value="<?=$fwValue?>" id="fwValue"/></p>
                    </div>
  
 
</body>
</html>

