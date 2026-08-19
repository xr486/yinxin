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
$moju_num  = isset($_REQUEST['moju_num']) ? $_REQUEST['moju_num'] : '';	
$need_remark = isset($_REQUEST['need_remark']) ? $_REQUEST['need_remark'] : '';	 
$where = '';
if($moju_num) {
   $where .= ' and moju_num like "%'.$moju_num.'%"';
}
 
if($need_remark) {
   $where .= ' and need_remark like "%'.$need_remark.'%"';
}

$num = 10;
$off = $num*($page-1);
$count = db_sql('select a.*,b.*,c.* from so_lines_all a,so_headers_all b,customers c where 
a.order_number=b.order_number and b.customer_code=c.customer_code and	1=1 '.$where.'',3);

$pages = ceil($count/$num);
$sql = 'select a.moju_num,a.need_remark,c.customer_code,c.customer_name,c.customer_contacts,c.contacts_phone
from so_lines_all a,so_headers_all b,customers c where 
a.order_number=b.order_number and b.customer_code=c.customer_code and	1=1 '.$where.' ORDER BY a.moju_num limit '.$off.','.$num.'';
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

<title>查询模具</title>
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
                        W.document.getElementById('text_slect_moju'+$('#fwValue').val()).value = $("label#form_moju").text(); 
					              W.document.getElementById('text_slect_name'+$('#fwValue').val()).value = $("label#form_name").text(); 
												W.document.getElementById('text_slect_customer_code'+$('#fwValue').val()).value = $("label#form_customer_code").text(); 
                        W.document.getElementById('text_slect_customer_name'+$('#fwValue').val()).value = $("label#form_customer_name").text(); 					
						            W.document.getElementById('text_slect_customer_contact'+$('#fwValue').val()).value = $("label#form_customer_contact").text();
                        W.document.getElementById('text_slect_contacts_phone'+$('#fwValue').val()).value = $("label#form_contacts_phone").text();
						       $("#xianshi").css("display","block"); 
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
				$("#form_moju").text(c[0]);
				$("#form_name").text(c[1]);
        $("#form_customer_code").text(c[2]);
				$("#form_customer_name").text(c[3]);
        $("#form_customer_contact").text(c[4]);
				$("#form_contacts_phone").text(c[5]);
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
				$("#form_moju").text(c[0]);
				$("#form_name").text(c[1]);
        $("#form_customer_code").text(c[2]); 				
				$("#form_customer_name").text(c[3]);
        $("#form_customer_contact").text(c[4]); 
				$("#form_contacts_phone").text(c[5]); 
				W.document.getElementById('text_slect_moju'+$('#fwValue').val()).value = $("label#form_moju").text(); 
				W.document.getElementById('text_slect_name'+$('#fwValue').val()).value = $("label#form_name").text(); 					
				W.document.getElementById('text_slect_customer_code'+$('#fwValue').val()).value = $("label#form_customer_code").text();
        W.document.getElementById('text_slect_customer_name'+$('#fwValue').val()).value = $("label#form_customer_name").text();
				W.document.getElementById('text_slect_customer_contact'+$('#fwValue').val()).value = $("label#form_customer_contact").text();
				W.document.getElementById('text_slect_contacts_phone'+$('#fwValue').val()).value = $("label#form_contacts_phone").text();
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
				<img src="./css/xenos/images/magnifier.png" title="查询模具" alt="查询模具">查询模具
			</p>
			<form action="./BtnSearchMoju.php?fwValue=<?=$cat?>&cat=<?=$cat?>" method ="POST">
				<div>
					 
					<table cellpadding="3" class="selection">
					<tr>
						<td>模具编号：</td>
						<td>
							<input type="text" name="moju_num" value="<?=$moju_num?>">
						</td>
						<td>模具名称：</td>
						<td>
							<input type="text" name="need_remark" value="<?=$need_remark?>">
						</td>
					</tr>
					</table>
					<div class="centre">
					     
						<input type="submit" value="查找">
					</div>
					</form>
					<br/>
					<table cellpadding="2" class="selection" id="ck_company">
					<tr>
						<th class="ascending" width ="100">模具编号</th>
						<th class="ascending" width ="200">模具名称</th>
						<th class="ascending" width ="100">客户简称</th>
						<th class="ascending" width ="200">客户名称</th>
						<th class="ascending" width ="100">联系人</th>
						<th class="ascending" width ="100">电话</th>
					</tr>
					<?php foreach($list as $arr=>$row2){?>
					<tr class="EvenTableRows">
						<td>	<?=$row2['moju_num']?>
						</td>
						<td> <?=$row2['need_remark']?>
						</td>
	          <td> <?=$row2['customer_code']?>
						</td>
						<td> <?=$row2['customer_name']?>
						</td>
						<td> <?=$row2['customer_contacts']?>
						</td>
						<td> <?=$row2['contacts_phone']?>
						</td>
						<td>
						<input name="a" type="hidden" value="选择" class="coupons" rel="<?=$row2['moju_num']?>:<?=$row2['need_remark']?>:<?=$row2['customer_code']?>:<?=$row2['customer_name']?>:<?=$row2['customer_contacts']?>:<?=$row2['contacts_phone']?>">				 
						</td>
					</tr>
					<?php }?>
					 
					 
					</table>
					<br/>
					<div class="centre">
					<?=show_page('?page=',$page,$pages,$count,'&fwValue='.$cat.'&cat='.$cat.'&moju_num='.$moju_num.'&need_remark'.$need_remark.'');?>
					
					</div>
				</div>
			
		</div>
	</div>
	<div id="FooterDiv">
		<div id="FooterWrapDiv">
			 
		</div>
	</div>
	<div style="display:none">
		<p><label class="text-info">moju_num:</label>　<label id="form_moju"></label></p>
	</div>
	<div style="display:none">
		<p><label class="text-info">need_remark</label>　<label id="form_name"></label></p>
	</div>
	<div style="display:none">
		<p><label class="text-info">customer_code</label>　<label id="form_customer_code"></label></p>
	</div>
	<div style="display:none">
		<p><label class="text-info">customer_name</label>　<label id="form_customer_name"></label></p>
	</div>
	<div style="display:none">
		<p><label class="text-info">customer_contact</label>　<label id="form_customer_contact"></label></p>
	</div>	
	<div style="display:none">
		<p><label class="text-info">contacts_phone</label>　<label id="form_contacts_phone"></label></p>
	</div>

	<div style="display:none">
		<p><input type="hidden" name="cat" id="cat" value="<?=$cat?>"/></p>
    <p><input type="hidden" name="fwValue" value="<?=$fwValue?>" id="fwValue"/></p>
	</div>
</body>
</html>

