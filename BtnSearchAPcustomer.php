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
$customercode  = isset($_REQUEST['customercode']) ? $_REQUEST['customercode'] : '';	
$customername = isset($_REQUEST['customername']) ? $_REQUEST['customername'] : '';	 
$where = '';
if($customercode) {
   $where .= ' and customer_code like "%'.$customercode.'%"';
}
 
if($customername) {
   $where .= ' and customer_name like "%'.$customername.'%"';
}
if($cat) {
	
    $array = array('buliao'=>'主布','shaliao'=>'纱','fuliao'=>'辅材','luomalianliao'=>'罗马帘');
	if (@$array[$cat]=='辅材') {
	$where .=  " and disable_date is null  ";}
	else {
	$where .= ' and disable_date is null  ';}

}
 
$num = 10;
$off = $num*($page-1);
 
$count = db_sql('select * from customers where 1=1 '.$where.'',3);

$pages = ceil($count/$num);
$sql = 'select * from customers where 1=1 '.$where.' ORDER BY customer_code  desc limit '.$off.','.$num.'';
   
 
 
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

<title>查询客户</title>
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
                        W.document.getElementById('text_slect_customer'+$('#fwValue').val()).value = $("label#form_customercode").text(); 
					    W.document.getElementById('text_slect_name'+$('#fwValue').val()).value = $("label#form_name").text(); 
						W.document.getElementById('text_slect_contacts'+$('#fwValue').val()).value = $("label#form_contacts").text();
						W.document.getElementById('text_slect_address'+$('#fwValue').val()).value = $("label#form_address").text();
						W.document.getElementById('text_slect_currency_code'+$('#fwValue').val()).value = $("label#form_currency_code").text();
						W.document.getElementById('text_slect_bankname'+$('#fwValue').val()).value = $("label#form_bankname").text();
						W.document.getElementById('text_slect_bankaccount'+$('#fwValue').val()).value = $("label#form_bankaccount").text();
						$("#xianshi").css("display","block"); 
                        break;
                    default :
                        alert('Data Post Error');
                }
            };
            
			
			$(".coupons").livequery("click", function() {
				var rel = this.getAttribute('rel');
				c = rel.split(":");
				$("#form_customercode").text(c[0]);
				$("#form_name").text(c[1]); 
				$("#form_contacts").text(c[2]);
				$("#form_address").text(c[3]);
				$("#form_currency_code").text(c[4]);
				$("#form_bankname").text(c[5]);
				$("#form_bankaccount").text(c[6]);
			});
			 
			
        });
    </script>
<div id="CanvasDiv">
 
	<div id="BodyDiv">
		<div id="BodyWrapDiv">
			<p class="page_title_text">
				<img src="/JXC/css/xenos/images/magnifier.png" title="查询客户" alt="查询客户">查询客户
			</p>
			<form action="/JXC/BtnSearchAPcustomer.php?fwValue=<?=$cat?>&cat=<?=$cat?>" method ="POST">
				<div>
					 
					<table cellpadding="3" class="selection">
					<tr>
						<td>
							客户代号：
						</td>
						<td>
							<input type="text" name="customercode" value="<?=$customercode?>">
						</td>
						<td>
							客户名称：
						</td>
						<td>
							<input type="text" name="customername" value="<?=$customername?>">
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
						<th class="ascending" width ="50">
							客户代号
						</th>
						<th class="ascending" width ="300">
							客户名称
						</th>
						<th   width ="350">
							 出货地址
						</th> 
						<th  width ="120">
							联系人
						</th>
						<th   width ="80">
							币别
						</th>
						 
						<th   width ="50">
							操作
						</th>
					</tr>
					<?php foreach($list as $arr=>$row2){?>
					<tr class="EvenTableRows">
						<td>
							<?=$row2['customer_code']?>
						</td>
						<td> <?=$row2['customer_name']?>
						</td>
						<td> <?=$row2['customer_address']?> </td> 
						<td> <?=$row2['customer_contacts']?> </td>
						<td> <?=$row2['currency_code']?> </td>
				 
						</td>
						<td>
						<input name="a" type="radio" value="选择" class="coupons" rel="<?=$row2['customer_code']?>:<?=$row2['customer_name']?>:<?=$row2['customer_contacts']?>:<?=$row2['customer_address']?>:<?=$row2['currency_code']?>:<?=$row2['bank_name']?>:<?=$row2['bank_account']?>">				 
						</td>
					</tr>
					<?php }?>
					 
					 
					</table>
					<br/>
					<div class="centre">
					<?=show_page('?page=',$page,$pages,$count,'&fwValue='.$cat.'&cat='.$cat.'&customercode='.$customercode.'&customername'.$customername.'');?>
					
					</div>
				</div>
			
		</div>
	</div>
	<div id="FooterDiv">
		<div id="FooterWrapDiv">
			 
		</div>
	</div>
                   <div style="display:none">
                         <p><label class="text-info">customercode:</label>　<label id="form_customercode"></label></p>
                    </div>

					 <div style="display:none">
                         <p><label class="text-info">customername</label>　<label id="form_name"></label></p>
                    </div>

					<div style="display:none">
                         <p><label class="text-info">customercontacts</label>　<label id="form_contacts"></label></p>
                    </div>

					<div style="display:none">
                         <p><label class="text-info">customeraddress</label>　<label id="form_address"></label></p>
                    </div>
					<div style="display:none">
                         <p><label class="text-info">bankname</label>　<label id="form_bankname"></label></p>
                    </div>
					<div style="display:none">
                         <p><label class="text-info">bankaccount</label>　<label id="form_bankaccount"></label></p>
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

