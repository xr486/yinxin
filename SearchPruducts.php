<?php 

/*$db_host='localhost';
$db_user='a0316105426';
$db_pass='1ea5bca31';
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
$ItemNo  = isset($_REQUEST['ItemNo']) ? $_REQUEST['ItemNo'] : '';	
$ItemDesc = isset($_REQUEST['ItemDesc']) ? $_REQUEST['ItemDesc'] : '';	 
$where = '';
if($ItemNo) {
   $where .= ' and p_id like "%'.$ItemNo.'%"';
}
 
if($ItemDesc) {
   $where .= ' and p_name like "%'.$ItemDesc.'%"';
}
 
 
$num = 10;
$off = $num*($page-1);
 
$count = db_sql('select * from wip_endproducts where 1=1 '.$where.'',3);

$pages = ceil($count/$num);
$sql = 'select * from wip_endproducts where 1=1 '.$where.' ORDER BY p_id  desc limit '.$off.','.$num.'';
   
 
 
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

<title>查询料号</title>
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
                        W.document.getElementById('text_slect_buliao'+$('#fwValue').val()).value = $("label#form_ItemNo").text(); 
					    W.document.getElementById('text_slect_units'+$('#fwValue').val()).value = $("label#form_units").text();
						W.document.getElementById('text_slect_ItemDesc'+$('#fwValue').val()).value = $("label#form_ItemDesc").text();
                        W.document.getElementById('text_slect_buliao_img'+$('#fwValue').val()).setAttribute('src',$("img#form_Img").attr('src'));
						W.document.getElementById('text_slect_model'+$('#fwValue').val()).value = $("label#form_model").text();
						W.document.getElementById('text_slect_polish'+$('#fwValue').val()).value = $("label#form_polish").text();
						W.document.getElementById('text_slect_total'+$('#fwValue').val()).value = $("label#form_total").text();
						W.document.getElementById('text_slect_total1'+$('#fwValue').val()).value = $("label#form_total1").text();
						$("#xianshi").css("display","block"); 
                        break;
                    default :
                        alert('Data Post Error');
                }
            };
            
			
			$(".coupons").livequery("click", function() {
				var rel = this.getAttribute('rel');
				c = rel.split(":");
				$("#form_ItemNo").text(c[0]);
				$("#form_units").text(c[1]);
				$("#form_ItemDesc").text(c[2]);
				$("#form_Img").attr("src",c[3]);
				$("#form_model").text(c[4]);
				$("#form_polish").text(c[5]);
				$("#form_total").text(c[6]);
				$("#form_total1").text(c[7]);
			});
			 
			
			
        });
    </script>
<div id="CanvasDiv">
 
	<div id="BodyDiv">
		<div id="BodyWrapDiv">
			<p class="page_title_text">
				<img src="/JXC/css/xenos/images/magnifier.png" title="查询产品" alt="查询产品">查询产品
			</p>
			<form action="./SearchPruducts.php?fwValue=<?=$cat?>&cat=<?=$cat?>" method ="POST">
				<div>
					 
					<table cellpadding="3" class="selection">
					<tr>
						<td>
							料号：
						</td>
						<td>
							<input type="text" name="ItemNo" value="<?=$ItemNo?>">
						</td>
						<td>
							料号描述：
						</td>
						<td>
							<input type="text" name="ItemDesc" value="<?=$ItemDesc?>">
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
						<th class="ascending" width ="150">
							成品料号
						</th>
						<th class="ascending" width ="200">
							成品描述
						</th>
						<th class="ascending" width ="50">
							单位
						</th>
						<th class="ascending" width ="50">
							造型单价
						</th>
						<th class="ascending" width ="50">
							打磨单价
						</th>
						<th class="ascending" width ="50">
							泥芯总价
						</th>
						<th class="ascending" width ="50">
							单价/kg
						</th>
						<th class="ascending" width ="50">
							重量
						</th>
						<th class="ascending" width ="120">
							图片
						</th>
						 
						<th class="ascending" width ="50">
							操作
						</th>
					</tr>
					<?php foreach($list as $arr=>$row2){?>
					<tr class="EvenTableRows">
						<td>
							<?=$row2['p_id']?>
						</td>
						<td> <?=$row2['p_name']?>
						</td>
						<td> 个
						</td>
						<td> <?=$row2['model_unit_price']?> </td>
						 <td> <?=$row2['polish_unit_price']?> </td>
						 <td> <?=$row2['polish_unit_price']?> </td>
						 <td> <?=$row2['endproduct_unitprice']?> </td>
						 <td> <?=$row2['p_weight']?> </td>
						<td> <img src="<?=$row2['img_path']?>" width="60" height="60"/>
						</td>
						<td>
						<?php
						  $total=$row2['endproduct_unitprice']*$row2['p_weight'];
						?>
						<input name="a" type="radio" value="选择" class="coupons" rel="<?=$row2['p_id']?>:<?='个'?>:<?=$row2['p_name']?>:<?=$row2['img_path']?>:<?=$row2['model_unit_price']?>:<?=$row2['model_unit_price']?>:<?=$row2['polish_unit_price']?>:<?=$total?>">				 
						</td>
					</tr>
					<?php }?>
					 
					 
					</table>
					<br/>
					<div class="centre">
					<?=show_page('?page=',$page,$pages,$count,'&fwValue='.$cat.'&cat='.$cat.'&ItemNo='.$ItemNo.'&ItemDesc'.$ItemDesc.'');?>
					
					</div>
				</div>
			
		</div>
	</div>
	<div id="FooterDiv">
		<div id="FooterWrapDiv">
			 
		</div>
	</div>
                   <div style="display:none">
                         <p><label class="text-info">ItemNo:</label>　<label id="form_ItemNo"></label></p>
                    </div>

					 <div style="display:none">
                         <p><label class="text-info">Units:</label>　<label id="form_units"></label></p>
                    </div>
					<div style="display:none">
                         <p><label class="text-info">ItemDesc</label>　<label id="form_ItemDesc"></label></p>
                    </div>
 
                    <div style="display:none">
                        <p><label class="text-info">Images:</label>　<img src="" id="form_Img" width="64px" height="64px"/></p>
                    </div>
					<div style="display:none">
                         <p><label class="text-info">model</label>　<label id="form_model"></label></p>
                    </div>
					<div style="display:none">
                         <p><label class="text-info">polish</label>　<label id="form_polish"></label></p>
                    </div>
					<div style="display:none">
                         <p><label class="text-info">total</label>　<label id="form_total"></label></p>
                    </div>
					<div style="display:none">
                         <p><label class="text-info">total1</label>　<label id="form_total1"></label></p>
                    </div>
                    <div style="display:none">
                        <p><input type="hidden" name="cat" id="cat" value="<?=$cat?>"/></p>
						<p><input type="hidden" name="fwValue" value="<?=$fwValue?>" id="fwValue"/></p>
                    </div>
  
 
</body>
</html>

