<?php 

include('includes/login2.inc');
function db_sql($sql,$type=1){
    $arr = array(); 
    $sql = mysql_query($sql);
   
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
$stockid  = isset($_REQUEST['stockid']) ? $_REQUEST['stockid'] : '';
$wip_entity_name = isset($_REQUEST['wip_entity_name']) ? $_REQUEST['wip_entity_name'] : '';	
$item_name = isset($_REQUEST['item_name']) ? $_REQUEST['item_name'] : '';	 
$where = "   ";
if($stockid) {
   $where .= ' and item_no like "%'.$stockid.'%"';
}
if($wip_entity_name) {
   $where .= ' and a.wip_entity_name like "%'.$wip_entity_name.'%"';
} 
if($item_name) {
   $where .= ' and e.item_name like "%'.$item_name.'%"';
}
 
 
$num = 10;
$off = $num*($page-1);
 
$count = db_sql("select a.wip_entity_name,a.primary_item,e.item_name,e.item_desc,e.units,a.start_quantity
from wip_jobs_all a, sf_item_no e
where  a.primary_item=e.item_no  
	and   a.status_type = '开始'  ".$where."",3);

$pages = ceil($count/$num);
$sql = "select a.wip_entity_name,a.primary_item,e.item_name,e.item_desc,e.units,a.start_quantity
  from wip_jobs_all a, sf_item_no e
where  a.primary_item=e.item_no  
	  and   a.status_type = '开始'   ".$where." ORDER BY wip_entity_name  desc limit ".$off.",".$num."";
   
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

<title>查询工单</title>
<link rel="shortcut icon" href="./favicon.ico"/>
<link rel="icon" href="./favicon.ico"/>
<meta http-equiv="Content-Type" content="application/html; charset=utf-8"/>
<link href="./css/xenos/default.css" rel="stylesheet" type="text/css"/>
<script type="text/javascript" src ="./javascripts/miscfunctions.js"></script>
<script type="text/javascript" src ="./javascripts/wdatepicker.js"></script>
<script src="./javascript/jquery-1.10.2.min.js"></script>
    <script src="./javascript/bootstrap.min.js"></script>
    <script src="./javascript/jquery.dataTables.js"></script>
	<script src="./javascript/jquery.livequery.js"></script>
</head>
<body>

<script type="text/javascript">
         window.onload=function(){
            var ch_checked=document.getElementById("chsecheck");
            var api = frameElement.api, W = api.opener;
            ch_checked.onclick=function(){
                var arr1=document.getElementsByName("boxlist");
                for(i=0;i<arr1.length;i++){ 
					
                    if(arr1[i].checked==true){
						var f2=document.getElementsByClassName("coupons");
						var bot=document.getElementById("fwValue");
						var botint=parseInt(bot.value);
						rel=f2[i].value;
									//console.log($(this).children("td:last-child").children(".coupons").attr("rel"));
				c = rel.split(":");
				$("#form_wip_entity_name").text(c[0]); 
					$("#form_stockid").text(c[1]);
					$("#form_item_name").text(c[2]);
					$("#form_uom").text(c[3]);
					$("#form_start_quantity").text(c[4]); 
		
					$("#form_item_desc").text(c[5]); 
				 W.document.getElementById('wip_entity_name' + $('#fwValue').val()).value = $("label#form_wip_entity_name").text();
				 
					W.document.getElementById('stockid' + $('#fwValue').val()).value = $("label#form_stockid").text();
					W.document.getElementById('item_name' + $('#fwValue').val()).value = $("label#form_item_name").text();
					W.document.getElementById('uom' + $('#fwValue').val()).value = $("label#form_uom").text();
					W.document.getElementById('start_quantity' + $('#fwValue').val()).value = $("label#form_start_quantity").text();
			
				 W.document.getElementById('item_desc' + $('#fwValue').val()).value = $("label#form_item_desc").text();
						botint++;
						bot.value=botint+'';
						// $('#fwValue').val()=$('#fwValue').val()+'2';
						// 'text_slect_item_spec'+$('#fwValue').val()+=1;
						
						console.log($('#fwValue'));
				}
                }api.close();
            }

        }
    </script>


<div id="CanvasDiv">
 
	<div id="BodyDiv">
		<div id="BodyWrapDiv">
			<p class="page_title_text">
				<img src="./css/xenos/images/magnifier.png" title="查询工单" alt="查询工单">查询工单
			</p>
			<form action="./Searchwaitosppo.php?fwValue=<?=$cat?>&cat=<?=$cat?>" method ="POST">
				<div>
					 
					<table cellpadding="3" class="selection">
					<div class="text-nav">
								<div class="text-nav-1">
									<div>
										工单号：
									</div>

									<input type="text"   name="wip_entity_name" value="<?= $wip_entity_name ?>">
								</div>
								<div class="text-nav-1">
									<div>
										料号名称
									</div>

									<input type="text"   name="item_name" value="<?= $item_name ?>">
								</div>
								<div class="text-nav-1">
									<div>
										料号
									</div>

									<input type="text"   name="stockid" value="<?= $stockid ?>">
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
					<th  width =40 >选择</th>
						<th class="ascending" width="150">工单号</th>
							
							<th class="ascending" width="150">料号</th>
							<th class="ascending" width="150">名称</th>
							<th class="ascending" width="150">规格型号</th>
							<th width="50">单位</th>
							<th width="50">开工数量</th>
						
					
					</tr>
					<?php foreach($list as $arr=>$row2){?>
					<tr class="EvenTableRows">
					<td>
							<input type="checkbox" style="height: 24px;width: 24px;" name="boxlist">
						</td>
						<td><?= $row2['wip_entity_name'] ?></td>
								
								<td><?= $row2['primary_item'] ?></td>
								<td> <?= $row2['item_name'] ?></td> 
								<td> <?= $row2['item_desc'] ?></td> 
								<td> <?= $row2['units'] ?> </td> 
								<td> <?= $row2['start_quantity'] ?> </td>
							
						<td>
						<input name="a" type="hidden"  class="coupons" value="<?=$row2['wip_entity_name'] ?>:<?= $row2['primary_item'] ?>:<?= $row2['item_name'] ?>:<?= $row2['units'] ?>:<?= $row2['start_quantity'] ?>:<?= $row2['item_desc'] ?>">				 
						</td>
					</tr>
					<?php }?>
					 
					 
					</table>
					<input type="button" class="choosecheck" id="chsecheck" style="height: 24px;width: 100px;" value="确定">
					<br/>
					<div class="centre">
					<?=show_page('?page=',$page,$pages,$count,'&fwValue='.$cat.'&cat='.$cat.'&item_name=' . $item_name . '&wip_entity_name=' . $wip_entity_name .'&stockid=' . $stockid .'');?>
					
					</div>
				</div>
			
		</div>
	</div>
	<div id="FooterDiv">
		<div id="FooterWrapDiv">
			 
		</div>
	</div>
                  <div style="display:none">
		<p><label class="text-info">wip_entity_name:</label>　<label id="form_wip_entity_name"></label></p>
	</div>
	
	<div style="display:none">
		<p><label class="text-info">item_name:</label>　<label id="form_item_name"></label></p>
	</div>

	<div style="display:none">
		<p><label class="text-info">uom</label>　<label id="form_uom"></label></p>
	</div>
	<div style="display:none">
		<p><label class="text-info">start_quantity</label>　<label id="form_start_quantity"></label></p>
	</div>
	<div style="display:none">
		<p><label class="text-info">caizhi</label>　<label id="form_caizhi"></label></p>
	</div>
	<div style="display:none">
		<p><label class="text-info">tuhao</label>　<label id="form_tuhao"></label></p>
	</div>
	<div style="display:none">
		<p><label class="text-info">item_desc</label>　<label id="form_item_desc"></label></p>
	</div>
	 
	 
	<div style="display:none">
		<p><label class="text-info">stockid</label>　<label id="form_stockid"></label></p>
	</div>
                    <div style="display:none">
                        <p><input type="hidden" name="cat" id="cat" value="<?=$cat?>"/></p>
						<p><input type="hidden" name="fwValue" value="<?=$fwValue?>" id="fwValue"/></p>
                    </div>
  
 
</body>
</html>

