<?php 
include('includes/login2.inc');
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
$vendorcode  = isset($_REQUEST['vendorcode']) ? $_REQUEST['vendorcode'] : '';	
$vendorname = isset($_REQUEST['vendorname']) ? $_REQUEST['vendorname'] : '';	 
$where = '';
if($vendorcode) {
   $where .= ' and vendor_code like "%'.$vendorcode.'%"';
}
 
if($vendorname) {
   $where .= ' and vendor_name like "%'.$vendorname.'%"';
}
$where .= " and enable_flag='Y' ";
 
$num = 10;
$off = $num*($page-1);
			 
				 
$count = db_sql('SELECT d.*  FROM vendors d  WHERE   d.vendor_code in (SELECT a.vendor_code FROM waixie_headers_all a,waixie_lines_all b 
WHERE a.waixie_num = b.waixie_num AND b.quantity > b.quantity_deliveried  
AND a.status = "APPROVED" )'.$where.'',3);

$pages = ceil($count/$num);
$sql = 'SELECT d.* FROM vendors d WHERE  d.vendor_code in (SELECT a.vendor_code FROM waixie_headers_all a,waixie_lines_all b WHERE a.waixie_num = b.waixie_num AND b.quantity > b.quantity_deliveried AND a.status = "APPROVED"  )'.$where.' ORDER BY vendor_code  desc limit '.$off.','.$num.'';
 
 
 
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

<title>查询供应商</title>
<link rel="shortcut icon" href="favicon.ico"/>
<link rel="icon" href="favicon.ico"/>
<meta http-equiv="Content-Type" content="application/html; charset=utf-8"/>
<link href="css/xenos/default.css" rel="stylesheet" type="text/css"/>
<script type="text/javascript" src ="javascripts/miscfunctions.js"></script>
<script type="text/javascript" src ="javascripts/wdatepicker.js"></script>
<script src="javascript/jquery-1.10.2.min.js"></script>
    <script src="javascript/bootstrap.min.js"></script>
    <script src="javascript/jquery.dataTables.js"></script>
	<script src="javascript/jquery.livequery.js"></script>
</head>
<body>


<script type="text/javascript">
        $(document).ready(function(){           
            var api = frameElement.api, W = api.opener;

    
            function ok()
            { 
                switch ($('#cat').val()){
                    case 'buliao':
                        W.document.getElementById('text_slect_vendor'+$('#fwValue').val()).value = $("label#form_vendorcode").text(); 
					    W.document.getElementById('text_slect_name'+$('#fwValue').val()).value = $("label#form_name").text(); 
						W.document.getElementById('text_slect_contacts'+$('#fwValue').val()).value = $("label#form_contacts").text();
						W.document.getElementById('text_slect_address'+$('#fwValue').val()).value = $("label#form_address").text();
						W.document.getElementById('text_slect_currency_code'+$('#fwValue').val()).value = $("label#form_currency_code").text(); 
                        break;
                    default :
                        alert('Data Post Error');
                }
				console.log("ok");
				console.log($('#cat').val());
            };
            
			
			$(".coupons").livequery("click", function() {
				var rel = this.getAttribute('rel');
				c = rel.split(":");
				$("#form_vendorcode").text(c[0]);
				$("#form_name").text(c[1]); 
				$("#form_contacts").text(c[2]);
				$("#form_address").text(c[3]);
				$("#form_currency_code").text(c[4]); 
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
				$("#form_vendorcode").text(c[0]);
				$("#form_name").text(c[1]); 
				$("#form_contacts").text(c[2]);
				$("#form_address").text(c[3]);
				$("#form_currency_code").text(c[4]);
				W.document.getElementById('text_slect_vendor'+$('#fwValue').val()).value = $("label#form_vendorcode").text(); 
					    W.document.getElementById('text_slect_name'+$('#fwValue').val()).value = $("label#form_name").text(); 
						W.document.getElementById('text_slect_contacts'+$('#fwValue').val()).value = $("label#form_contacts").text();
						W.document.getElementById('text_slect_address'+$('#fwValue').val()).value = $("label#form_address").text();
						W.document.getElementById('text_slect_currency_code'+$('#fwValue').val()).value = $("label#form_currency_code").text();
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
				<img src="/JXC/css/xenos/images/magnifier.png" title="查询供应商" alt="查询供应商">查询供应商
			</p>
			<form action="./BtnSearchVendor12.php?fwValue=<?=$cat?>&cat=<?=$cat?>" method ="POST">
				<div>
					 
					<table cellpadding="3" class="selection">
					<tr>
						<td>
							供应商代号：
						</td>
						<td>
							<input type="text" name="vendorcode" value="<?=$vendorcode?>">
						</td>
						<td>
							供应商名称：
						</td>
						<td>
							<input type="text" name="vendorname" value="<?=$vendorname?>">
						</td>
					</tr>
					
					
					</table>
					<div class="centre">
					     
						<input type="submit" value="查找">
					</div>
					</form>
					<br/>
					<table cellpadding="2" class="selection"  id="ck_company">
					<tr>
						<th class="ascending" width ="200">
							供应商代号
						</th>
						<th class="ascending" width ="300">
							供应商名称
						</th>
						
					
					</tr>
					<?php foreach($list as $arr=>$row2){?>
					<tr class="EvenTableRows">
						<td>
							<?=$row2['vendor_code']?>
						</td>
						<td> <?=$row2['vendor_name']?>
						</td>
						
				 
						</td>
						<td>
						<input name="a" type="hidden" value="选择" class="coupons" rel="<?=$row2['vendor_code']?>:<?=$row2['vendor_name']?>:<?=$row2['vendor_contacts']?>:<?=$row2['vendor_address']?>:<?=$row2['currencycode']?>">				 
						</td>
					</tr>
					<?php }?>
					 
					 
					</table>
					<br/>
					<div class="centre">
					<?=show_page('?page=',$page,$pages,$count,'&fwValue='.$cat.'&cat='.$cat.'&vendorcode='.$vendorcode.'&vendorname'.$vendorname.'');?>
					
					</div>
				</div>
			
		</div>
	</div>
	<div id="FooterDiv">
		<div id="FooterWrapDiv">
			 
		</div>
	</div>
                   <div style="display:none">
                         <p><label class="text-info">vendorcode:</label>　<label id="form_vendorcode"></label></p>
                    </div>

					 <div style="display:none">
                         <p><label class="text-info">vendorname</label>　<label id="form_name"></label></p>
                    </div>

					<div style="display:none">
                         <p><label class="text-info">customercontacts</label>　<label id="form_contacts"></label></p>
                    </div>

					<div style="display:none">
                         <p><label class="text-info">customeraddress</label>　<label id="form_address"></label></p>
                    </div>

					<div style="display:none">
                         <p><label class="text-info">currencycode</label>　<label id="form_currency_code"></label></p>
                    </div>
                    
                    <div style="display:none">
                        <p><input type="hidden" name="cat" id="cat" value="<?=$cat?>"/></p>
						<p><input type="hidden" name="fwValue" value="<?=$fwValue?>" id="fwValue"/></p>
                    </div>
  
 
</body>
</html>

