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
$receivenum  = isset($_REQUEST['receivenum']) ? $_REQUEST['receivenum'] : '';	
$bankchangenum = isset($_REQUEST['bankchangenum']) ? $_REQUEST['bankchangenum'] : '';	 
$where = '';
if($receivenum) {
   $where .= ' and b.receivenum like "%'.$receivenum.'%"';
}
 
if($bankchangenum) {
   $where .= ' and b.bankchangenum like "%'.$bankchangenum.'%"';
}

if($cat) {
	
     
	$where .= " and banktranstype='客户预收款' and b.customer_code='". $cat . "' "; 

}
 
$num = 10;
$off = $num*($page-1);
 
$count = db_sql('SELECT b.customer_code, b.receivenum, b.receivedate, b.narrative, b.receiveamount, b.prereceive_used, b.bankaccountname, bankaccount, b.currency_code, b.bankchangenum
FROM bank_transaction_all a, ar_receive_headers_all b
WHERE   a.transaction_num = b.receivenum
and  b.receiveamount>b.prereceive_used
AND receive_type = banktranstype and 1=1  '.$where.'',3);

$pages = ceil($count/$num);
$sql = 'SELECT b.customer_code, b.receivenum, b.receivedate, b.narrative, b.receiveamount, b.prereceive_used, b.bankaccountname, bankaccount, b.currency_code, b.bankchangenum
FROM bank_transaction_all a, ar_receive_headers_all b
WHERE   a.transaction_num = b.receivenum
and  b.receiveamount>b.prereceive_used
AND receive_type = banktranstype and 1=1  '.$where.' ORDER BY b.receivenum  desc limit '.$off.','.$num.'';
   
 
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
                
                        W.document.getElementById('text_slect_receivenum'+$('#fwValue').val()).value = $("label#form_receivenum").text(); 
					    W.document.getElementById('text_slect_receiveamount'+$('#fwValue').val()).value = $("label#form_receiveamount").text(); 
						W.document.getElementById('text_slect_prereceive_used'+$('#fwValue').val()).value = $("label#form_prereceive_used").text(); 
						W.document.getElementById('text_slect_wait_used_amount'+$('#fwValue').val()).value = $("label#form_wait_used_amount").text(); 
						W.document.getElementById('text_slect_bankaccountname'+$('#fwValue').val()).value = $("label#form_bankaccountname").text(); 
						W.document.getElementById('text_slect_mybankaccount'+$('#fwValue').val()).value = $("label#form_bankaccount").text(); 
						W.document.getElementById('text_slect_bankchangenum'+$('#fwValue').val()).value = $("label#form_bankchangenum").text(); 
						W.document.getElementById('text_slect_currency_code'+$('#fwValue').val()).value = $("label#form_currency_code").text(); 
						$("#xianshi").css("display","block"); 
                     
            };
            
			
			$(".coupons").livequery("click", function() {
				var rel = this.getAttribute('rel');
				c = rel.split(":");
				$("#form_receivenum").text(c[0]);
				$("#form_receiveamount").text(c[1]);
				$("#form_prereceive_used").text(c[2]);
				$("#form_wait_used_amount").text(c[3]);
				$("#form_bankaccountname").text(c[4]);
				$("#form_bankaccount").text(c[5]);
				$("#form_bankchangenum").text(c[6]);
				$("#form_currency_code").text(c[7]);
			});
			 
			
			
        });
    </script>
<div id="CanvasDiv">
 
	<div id="BodyDiv">
		<div id="BodyWrapDiv">
			<p class="page_title_text">
				<img src="/JXC/css/xenos/images/magnifier.png" title="查询待冲销预付款" alt="查询待冲销预付款">查询待冲销预付款
			</p>
			<form action="/JXC/BtnSearchUnPrereceive.php?fwValue=<?=$cat?>&cat=<?=$cat?>" method ="POST">
				<div>
					 
					<table cellpadding="3" class="selection">
					<tr>
						<td>
							预收款单号：
						</td>
						<td>
							<input type="text" name="receivenum" value="<?=$receivenum?>">
						</td>
						<td>
							银行转账凭证/收款单号：
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
						<th class="ascending" width ="150"> 预收款单 </th>
						<th class="ascending" width ="150"> 银行转账凭证/收款单号 </th>
						 <th class="ascending" width ="60"> 币别 </th>
						 <th class="ascending" width ="80"> 预收款金额 </th>
						  <th class="ascending" width ="80"> 已冲销金额 </th>
						   <th class="ascending" width ="80"> 待冲销金额 </th>
						    <th class="ascending" width ="80"> 收款日期 </th>
						    <th class="ascending" width ="150"> 银行账户名 </th>
							<th class="ascending" width ="80"> 银行账户 </th>
						<th   width ="50">
							操作
						</th>
					</tr>
					<?php foreach($list as $arr=>$row2){
					 $wait_amount=$row2['receiveamount']-$row2['prereceive_used']; ?>
					<tr class="EvenTableRows">
					  
						<td> <?=$row2['receivenum']?> </td>
						<td> <?=$row2['bankchangenum']?> </td>
						<td> <?=$row2['currency_code']?> </td>
						<td> <?=$row2['receiveamount']?> </td>
						<td> <?=$row2['prereceive_used']?> </td>
						<td> <?=$wait_amount?> </td>
						<td> <?=date('Y-m-d',$row2[receivedate])?> </td>
						<td> <?=$row2['bankaccountname']?> </td>
						<td> <?=$row2['bankaccount']?> </td> 
				 
						</td>
						<td>
						<input name="a" type="radio" value="选择" class="coupons" rel="<?=$row2['receivenum']?>:<?=$row2['receiveamount']?>:<?=$row2['prereceive_used']?>:<?=$row2['receiveamount']-$row2['prereceive_used']?>:<?=$row2['bankaccountname']?>:<?=$row2['bankaccount']?>:<?=$row2['bankchangenum']?>:<?=$row2['currency_code']?>">				 
						</td>
					</tr>
					<?php }?>
					 
					 
					</table>
					<br/>
					<div class="centre">
					<?=show_page('?page=',$page,$pages,$count,'&fwValue='.$cat.'&cat='.$cat.'&receivenum='.$receivenum.'&bankchangenum'.$bankchangenum.'');?>
					
					</div>
				</div>
			
		</div>
	</div>
	<div id="FooterDiv">
		<div id="FooterWrapDiv">
			 
		</div>
	</div>
                  <div style="display:none">
                         <p><label class="text-info">receivenum:</label>　<label id="form_receivenum"></label></p>
                    </div>

					 <div style="display:none">
                         <p><label class="text-info">receiveamount</label>　<label id="form_receiveamount"></label></p>
                    </div>
					 <div style="display:none">
                         <p><label class="text-info">prereceive_used</label>　<label id="form_prereceive_used"></label></p>
                    </div>
					 <div style="display:none">
                         <p><label class="text-info">wait_used_amount</label>　<label id="form_wait_used_amount"></label></p>
                    </div>
					 <div style="display:none">
                         <p><label class="text-info">bankaccountname</label>　<label id="form_bankaccountname"></label></p>
                    </div>
					 <div style="display:none">
                         <p><label class="text-info">bankaccount</label>　<label id="form_bankaccount"></label></p>
                    </div>
					 <div style="display:none">
                         <p><label class="text-info">bankchangenum</label>　<label id="form_bankchangenum"></label></p>
                    </div>
					<div style="display:none">
                         <p><label class="text-info">currency_code</label>　<label id="form_currency_code"></label></p>
                    </div>

					
                     
                    <div style="display:none">
                        <p><input type="hidden" name="cat" id="cat" value="<?=$cat?>"/></p>
						<p><input type="hidden" name="fwValue" value="<?=$fwValue?>" id="fwValue"/></p>
                    </div>
  
 
</body>
</html>

