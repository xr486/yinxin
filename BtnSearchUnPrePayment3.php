<?php 

include('includes/login2.inc');

function db_sql($sql,$type=1)
{
	
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
$payment_num  = isset($_REQUEST['payment_num']) ? $_REQUEST['payment_num'] : '';	
$bankchangenum = isset($_REQUEST['bankchangenum']) ? $_REQUEST['bankchangenum'] : '';	 
$where = " and payment_type='预付货款' ";
if($payment_num) 
{
  $where .= ' and b.payment_num like "%'.$payment_num.'%"';
}
 
if($bankchangenum) 
{
  $where .= ' and b.bankchangenum like "%'.$bankchangenum.'%"';
}

if($cat) 
{
     
  $where .= " and payment_type='预付款' and b.vendor_code='". $cat . "' "; 
}
 
$num = 10;
$off = $num*($page-1);
 
$count = db_sql('SELECT b.vendor_code, b.payment_num, b.payment_date, b.narrative, b.payment_amount, b.prepayment_used, a.vendor_name,  b.currency_code, b.bankchangenum
FROM vendors a, ap_payment_headers_all b
WHERE a.vendor_code = b.vendor_code
and  b.payment_amount>b.prepayment_used  and 1=1  '.$where.'',3);
 
 
$pages = ceil($count/$num);
$sql = 'SELECT b.vendor_code, b.payment_num, b.payment_date, b.narrative, b.payment_amount, b.prepayment_used, a.vendor_name, b.currency_code, b.bankchangenum
FROM vendors a, ap_payment_headers_all b
WHERE a.vendor_code = b.vendor_code
  and b.payment_amount>b.prepayment_used and 1=1  '.$where.' ORDER BY b.payment_num  desc limit '.$off.','.$num.'';
   
 

$list = db_sql($sql,2);
 
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
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
			"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>

<title>查询待冲销预付款</title>
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
                
				W.document.getElementById('text_slect_payment_num'+$('#fwValue').val()).value = $("label#form_payment_num").text(); 
				W.document.getElementById('text_slect_payment_amount'+$('#fwValue').val()).value = $("label#form_payment_amount").text(); 
				W.document.getElementById('text_slect_prepayment_used'+$('#fwValue').val()).value = $("label#form_prepayment_used").text(); 
				W.document.getElementById('text_slect_wait_used_amount'+$('#fwValue').val()).value = $("label#form_wait_used_amount").text(); 
				W.document.getElementById('text_slect_vendor_name'+$('#fwValue').val()).value = $("label#form_vendor_name").text(); 
		        W.document.getElementById('text_slect_bankchangenum'+$('#fwValue').val()).value = $("label#form_bankchangenum").text(); 
				W.document.getElementById('text_slect_currency_code'+$('#fwValue').val()).value = $("label#form_currency_code").text(); 
				W.document.getElementById('text_slect_vendor_code'+$('#fwValue').val()).value = $("label#form_vendor_code").text(); 
				$("#xianshi").css("display","block"); 
                     
            };
            
			
			$(".coupons").livequery("click", function() {
				var rel = this.getAttribute('rel');
				c = rel.split(":");
				$("#form_payment_num").text(c[0]);
				$("#form_payment_amount").text(c[1]);
				$("#form_prepayment_used").text(c[2]);
				$("#form_wait_used_amount").text(c[3]);
				$("#form_vendor_name").text(c[4]); 
				$("#form_bankchangenum").text(c[5]);
				$("#form_currency_code").text(c[6]);
				$("#form_vendor_code").text(c[7]);
			});
			 
			
			
        });
    </script>
<div id="CanvasDiv">
 
	<div id="BodyDiv">
		<div id="BodyWrapDiv">
			<p class="page_title_text">
				<img src="/JXC/css/xenos/images/magnifier.png" title="查询待冲销预付款" alt="查询待冲销预付款">查询待冲销预付款
			</p>
			<form action="/JXC/BtnSearchUnPrepayment.php?fwValue=<?=$cat?>&cat=<?=$cat?>" method ="POST">
				<div>
					 
					<table cellpadding="3" class="selection">
					<tr>
						<td>
							预付款单号：
						</td>
						<td>
							<input type="text" name="payment_num" value="<?=$payment_num?>">
						</td>
						<td>
							银行转账凭证/付款单号：
						</td>
						<td>
							<input type="text" name="bankchangenum" value="<?=$bankchangenum?>">
						</td>
					</tr>
					
					
					</table>
					<div class="centre">
					     
						<input type="submit" value="查找">
					</div>
					</form>
					<br/>
					<table cellpadding="2" class="selection">
					<tr>
					  <th class="ascending" width ="150"> 预付款单 </th>
					  <th class="ascending" width ="80">  供应商代号 </th>
					  <th class="ascending" width ="180"> 供应商名称 </th>
					  <th class="ascending" width ="120"> 银行转账凭证/付款单号 </th>
					  <th class="ascending" width ="60">  币别 </th>
					  <th class="ascending" width ="80">  预付款金额 </th>
					  <th class="ascending" width ="80">  已冲销金额 </th>
					  <th class="ascending" width ="80">  待冲销金额 </th>
					  <th class="ascending" width ="80">  付款日期 </th> 
					  <th width ="50">
							操作
						</th>
					</tr>
					<?php foreach($list as $arr=>$row2){
					 $wait_amount=$row2['payment_amount']-$row2['prepayment_used']; ?>
					<tr class="EvenTableRows">
					  
						<td> <?=$row2['payment_num']?> </td>
						<td> <?=$row2['vendor_code']?> </td> 
						<td> <?=$row2['vendor_name']?> </td> 
						<td> <?=$row2['bankchangenum']?> </td>
						<td> <?=$row2['currency_code']?> </td>
						<td> <?=$row2['payment_amount']?> </td>
						<td> <?=$row2['prepayment_used']?> </td>
						<td> <?=$wait_amount?> </td>
						<td> <?=date('Y-m-d',$row2['payment_date'])?> </td> 
						
				        </td>
						<td>
						<input name="a" type="radio" value="选择" class="coupons" rel="<?=$row2['payment_num']?>:<?=$row2['payment_amount']?>:<?=$row2['prepayment_used']?>:<?=$row2['payment_amount']-$row2['prepayment_used']?>:<?=$row2['vendor_name']?>:<?=$row2['bankchangenum']?>:<?=$row2['currency_code']?>:<?=$row2['vendor_code']?>">				 
						</td>
					</tr>
					<?php }?>
					 
					 
					</table>
					<br/>
					<div class="centre">
					<?=show_page('?page=',$page,$pages,$count,'&fwValue='.$cat.'&cat='.$cat.'&payment_num='.$payment_num.'&bankchangenum'.$bankchangenum.'');?>
					
					</div>
				</div>
			
		</div>
	</div>
	<div id="FooterDiv">
		<div id="FooterWrapDiv">
			 
		</div>
	</div>
                  <div style="display:none">
                         <p><label class="text-info">payment_num:</label>　<label id="form_payment_num"></label></p>
                    </div>

					 <div style="display:none">
                         <p><label class="text-info">payment_amount</label>　<label id="form_payment_amount"></label></p>
                    </div>
					 <div style="display:none">
                         <p><label class="text-info">prepayment_used</label>　<label id="form_prepayment_used"></label></p>
                    </div>
					 <div style="display:none">
                         <p><label class="text-info">wait_used_amount</label>　<label id="form_wait_used_amount"></label></p>
                    </div>
					 <div style="display:none">
                         <p><label class="text-info">vendor_name</label>　<label id="form_vendor_name"></label></p>
                    </div> 
					 <div style="display:none">
                         <p><label class="text-info">bankchangenum</label>　<label id="form_bankchangenum"></label></p>
                    </div>
					<div style="display:none">
                         <p><label class="text-info">currency_code</label>　<label id="form_currency_code"></label></p>
                    </div>
					<div style="display:none">
                         <p><label class="text-info">vendor_code</label>　<label id="form_vendor_code"></label></p>
                    </div>

					
                     
                    <div style="display:none">
                        <p><input type="hidden" name="cat" id="cat" value="<?=$cat?>"/></p>
						<p><input type="hidden" name="fwValue" value="<?=$fwValue?>" id="fwValue"/></p>
                    </div>
  
 
</body>
</html>

