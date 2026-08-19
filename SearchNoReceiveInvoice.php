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
$invoicenum  = isset($_REQUEST['invoicenum']) ? $_REQUEST['invoicenum'] : '';	 
$where = '';

if($invoicenum) {
   $where .= ' and invoicenum like "%'.$invoicenum.'%"';
}
 
if($cat) {
	
     
	$where .= " and invoiceamount>alreadyreceiveamount and customer_code='". $cat . "' "; 

}
 
$num = 10;
$off = $num*($page-1);
 
$count = db_sql('select  invoiceamount,taxamount,invoicedate,schedulereceivedate,alreadyreceiveamount,narrative,invoicenum,currency_code
FROM ar_invoice_headers_all
WHERE 1=1 and  '.$where.'',3);

$pages = ceil($count/$num);
$sql = 'select  invoiceamount,taxamount,invoicedate,schedulereceivedate,alreadyreceiveamount,narrative,invoicenum,currency_code
FROM ar_invoice_headers_all
WHERE 1=1 '.$where.' ORDER BY  invoicenum  desc limit '.$off.','.$num.' ';
   
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

<title>查询待收款发票</title>
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
                        W.document.getElementById('text_slect_invoicenum'+$('#fwValue').val()).value = $("label#form_invoicenum").text(); 
					    W.document.getElementById('text_slect_invoicedate'+$('#fwValue').val()).value = $("label#form_invoicedate").text(); 
					    W.document.getElementById('text_slect_invoiceamount'+$('#fwValue').val()).value = $("label#form_invoiceamount").text();
						W.document.getElementById('text_slect_schedulereceivedate'+$('#fwValue').val()).value = $("label#form_schedulereceivedate").text();
						W.document.getElementById('text_slect_taxamount'+$('#fwValue').val()).value = $("label#form_taxamount").text();  
						W.document.getElementById('text_slect_alreadyreceiveamount'+$('#fwValue').val()).value = $("label#form_alreadyreceiveamount").text();
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
				
				$("#form_invoicenum").text(c[0]);
				$("#form_invoicedate").text(c[1]);
				$("#form_schedulereceivedate").text(c[2]);
				$("#form_invoiceamount").text(c[3]);
				$("#form_taxamount").text(c[4]);		
				$("#form_alreadyreceiveamount").text(c[5]); 
				$("#form_wait_amount").text(c[6]); 
				
				
			});
			 
			
			
        });
    </script>
<div id="CanvasDiv">
 
	<div id="BodyDiv">
		<div id="BodyWrapDiv">
			<p class="page_title_text">
				<img src="/JXC/css/xenos/images/magnifier.png" title="查询待收款发票号码" alt="查询待收款发票号码">查询待收款发票号码
			</p>
			<form action="/JXC/SearchNoReceiveInvoice.php?fwValue=<?=$cat?>&cat=<?=$cat?>" method ="POST">
				<div>
					 
					<table cellpadding="3" class="selection">
					<tr>
					   <td> 发票号码： </td>
						<td> <input type="text" name="invoicenum" value="<?=$invoicenum?>"> </td> 
						
					</tr>
					
					
					</table>
					<div class="centre">
					     
						<input type="submit" value="查找">
					</div>
					</form>
					<br/>
					<table cellpadding="2" class="selection">
					<tr>
					    <th  width ="150">发票号码 </th>
						<th   width ="100"> 开票日期 </th>	
					    <th  width ="100"> 预计收款日期 </th>
						<th   width ="20"> 币别 </th>	
						<th   width ="100"> 发票金额 </th>						
						<th   width ="100"> 税金 </th> 
						<th   width ="100"> 已收款金额 </th> 
						<th   width ="100"> 未收款金额 </th> 
						<th   width ="100"> 备注 </th>
						<th  width ="50"> 操作 </th>
					</tr>
					<?php foreach($list as $arr=>$row2){?> 
					<tr class="EvenTableRows"> 

					
					    <td> <?=$row2['invoicenum']?> </td>  
				  		<td> <?=date('Y-m-d',$row2['invoicedate'])?> </td>  
						<td> <?=date('Y-m-d',$row2['schedulereceivedate'])?> </td>  
						<td> <?=$row2['currency_code']?> </td>
						<td><?=$row2['invoiceamount']?>	</td>
						<td><?=$row2['taxamount']?> </td> 
						<td> <?=$row2['alreadyreceiveamount']?> </td> 
						<td> <?=$row2['invoiceamount']-$row2['alreadyreceiveamount']?> </td>	
						<td> <?=$row2['narrative']?> </td> 

						
						</td>
						<td>

						<input name="a" type="radio" value="选择" class="coupons" rel="<?=$row2['invoicenum']?>:<?=date('Y-m-d',$row2['invoicedate'])?>:<?=date('Y-m-d',$row2['schedulereceivedate'])?>:<?=$row2['invoiceamount']?>:<?=$row2['taxamount']?>:<?=$row2['alreadyreceiveamount']?>:<?=$row2['invoiceamount']-$row2['alreadyreceiveamount']?>">				 
						</td>
					</tr>

					
					<?php }?>
			 
					</table>
					<br/>
					<div class="centre">
					<?=show_page('?page=',$page,$pages,$count,'&fwValue='.$cat.'&cat='.$cat.'&schedulereceivedate='.$schedulereceivedate.'&check_amount'.$check_amount.'');?>
					
					</div>
				</div>
			
		</div>
	</div>
	<div id="FooterDiv">
		<div id="FooterWrapDiv">
			 
		</div>
	</div>
                    
					<div style="display:none">
                         <p><label class="text-info">invoicenum</label>　<label id="form_invoicenum"></label></p>
                    </div>
					<div style="display:none">
                         <p><label class="text-info">invoicedate</label>　<label id="form_invoicedate"></label></p>
                    </div>
				   <div style="display:none">
                         <p><label class="text-info">schedulereceivedate:</label>　<label id="form_schedulereceivedate"></label></p>
                    </div>

					 <div style="display:none">
                         <p><label class="text-info">invoiceamount:</label>　<label id="form_invoiceamount"></label></p>
                    </div>
					<div style="display:none">
                         <p><label class="text-info">taxamount</label>　<label id="form_taxamount"></label></p>
                    </div> 
					<div style="display:none">
                         <p><label class="text-info">alreadyreceiveamount</label>　<label id="form_alreadyreceiveamount"></label></p>
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

