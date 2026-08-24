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
$bankaccountname  = isset($_REQUEST['bankaccountname']) ? $_REQUEST['bankaccountname'] : '';	
$bankaccount = isset($_REQUEST['bankaccount']) ? $_REQUEST['bankaccount'] : '';	 
$where = '';
if($bankaccountname) {
   $where .= ' and bankaccountname like "%'.$bankaccountname.'%"';
}
 
if($bankaccount) {
   $where .= ' and bankaccount like "%'.$bankaccount.'%"';
}

if($cat) {
	
     
	$where .= "and disableflag=0 "; 

}

$num = 10;
$off = $num*($page-1);
 
$count = db_sql('select * from  fin_bank_alls where 1=1 '.$where.'',3);

$pages = ceil($count/$num);
$sql = 'select * from  fin_bank_alls where 1=1 '.$where.' ORDER BY bankaccountname  desc limit '.$off.','.$num.'';
   
 
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

<title>查询银行</title>
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
  
            function ok()
            { 
                switch ($('#cat').val()){
                    case 'buliao':
                        W.document.getElementById('text_slect_bankaccountname'+$('#fwValue').val()).value = $("label#form_bankaccountname").text(); 
					    W.document.getElementById('text_slect_mybankname'+$('#fwValue').val()).value = $("label#form_bankname").text(); 
						W.document.getElementById('text_slect_mybankaccount'+$('#fwValue').val()).value = $("label#form_bankaccount").text();
						W.document.getElementById('text_slect_currency_code'+$('#fwValue').val()).value = $("label#form_currency_code").text();
						W.document.getElementById('text_slect_bank_onhand'+$('#fwValue').val()).value = $("label#form_bank_onhand").text();
						$("#xianshi").css("display","block"); 
                        break;
                    default :
                        alert('Data Post Error');
                }
				 
            };
            
			
			$(".coupons").livequery("click", function() {
				var rel = this.getAttribute('rel');
				c = rel.split(":");
				$("#form_bankaccountname").text(c[0]);
				$("#form_bankname").text(c[1]);
				$("#form_bankaccount").text(c[2]);
				$("#form_currency_code").text(c[3]);
				$("#form_bank_onhand").text(c[4]);
			});

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
				$("#form_bankaccountname").text(c[0]);
				$("#form_bankname").text(c[1]);
				$("#form_bankaccount").text(c[2]);
				$("#form_currency_code").text(c[3]);
				$("#form_bank_onhand").text(c[4]);
				W.document.getElementById('text_slect_bankaccountname'+$('#fwValue').val()).value = $("label#form_bankaccountname").text(); 
					    W.document.getElementById('text_slect_mybankname'+$('#fwValue').val()).value = $("label#form_bankname").text(); 
						W.document.getElementById('text_slect_mybankaccount'+$('#fwValue').val()).value = $("label#form_bankaccount").text();
						W.document.getElementById('text_slect_currency_code'+$('#fwValue').val()).value = $("label#form_currency_code").text();
						W.document.getElementById('text_slect_bank_onhand'+$('#fwValue').val()).value = $("label#form_bank_onhand").text();
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
				<img src="/JXC/css/xenos/images/magnifier.png" title="查询银行" alt="查询银行">查询银行
			</p>
			<form action="./BtnSearchBank.php?fwValue=<?=$cat?>&cat=<?=$cat?>" method ="POST">
				<div>
					 

                    <div class="text-nav2">
                            <div class="text-nav-1 ">
                            <div>银行账户名：
						</div>
							<input type="text" name="bankaccountname" value="<?=$bankaccountname?>">
						</div>
						<div class="text-nav-1 ">
                            <div>银行账户：
						</div>

							<input type="text" name="bankaccount" value="<?=$bankaccount?>">
						</div>
					</div>
					
					

					<div class="centre">
					     
						<input type="submit" value="查找">
					</div>
					</form>
					<br/>
					<table cellpadding="2" class="selection" id="ck_company">
					<tr>
						<th class="ascending" width ="270">
							账户名称
						</th>
						<th class="ascending" width ="200">
							银行账户
						</th>
						 <th class="ascending" width ="200">
							银行名称
						</th>
						<th class="ascending" width ="280">
							银行地址
						</th>
						<th width ="50"> 
							币别
						</th>
					</tr>
					<?php foreach($list as $arr=>$row2){?>
					<tr class="EvenTableRows">
						<td>
							<?=$row2['bankaccountname']?>
						</td>
						<td> <?=$row2['bankaccount']?></td>
						<td> <?=$row2['bankname']?> </td>
						<td> <?=$row2['bankaddress']?> </td>
						<td> <?=$row2['currency_code']?> </td>
				 
						</td>
						<td>
						<input name="a" type="hidden" value="选择" class="coupons" rel="<?=$row2['bankaccountname']?>:<?=$row2['bankname']?>:<?=$row2['bankaccount']?>:<?=$row2['currency_code']?>:<?=$row2['bank_onhand']?>">				 
						</td>
					</tr>
					<?php }?>
					 
					 
					</table>
					<br/>
					<div class="centre">
					<?=show_page('?page=',$page,$pages,$count,'&fwValue='.$cat.'&cat='.$cat.'&bankaccountname='.$bankaccountname.'&bankaccount'.$bankaccount.'');?>
					
					</div>
				</div>
			
		</div>
	</div>
	<div id="FooterDiv">
		<div id="FooterWrapDiv">
			 
		</div>
	</div>
                   <div style="display:none">
                         <p><label class="text-info">bankaccountname:</label>　<label id="form_bankaccountname"></label></p>
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
                         <p><label class="text-info">bank_onhand</label>　<label id="form_bank_onhand"></label></p>
                    </div>
					
                     
                    <div style="display:none">
                        <p><input type="hidden" name="cat" id="cat" value="<?=$cat?>"/></p>
						<p><input type="hidden" name="fwValue" value="<?=$fwValue?>" id="fwValue"/></p>
                    </div>
  
 
</body>
</html>

