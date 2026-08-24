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
$ItemNo  = isset($_REQUEST['ItemNo']) ? $_REQUEST['ItemNo'] : '';
$ItemName = isset($_REQUEST['ItemName']) ? $_REQUEST['ItemName'] : '';	
$wip_entity_name = isset($_REQUEST['wip_entity_name']) ? $_REQUEST['wip_entity_name'] : '';	 
 $where = " and 1=1 ";
if($ItemNo) {
   $where .= ' and b.item_no like "%'.$ItemNo.'%"';
}
if($ItemName) {
   $where .= ' and b.item_name like "%'.$ItemName.'%"';
} 
if($wip_entity_name) {
   $where .= ' and a.wip_entity_name like "%'.$wip_entity_name.'%"';
}
 $where .= " and a.status_type<>'关闭' ";
  
 
$num = 10;
$off = $num*($page-1);
 
$count = db_sql("SELECT  a.plan_start_date , 
					a.start_quantity,a.wip_entity_name,a.quantity_completed,a.primary_item,b.item_desc,b.item_name,
					a.creation_date,c.request_name
				FROM wip_jobs_all a ,sf_item_no b,wip_material_request c
            WHERE  1=1 and a.primary_item=b.item_no and a.wip_entity_name=c.wip_entity_name and c.status='正常'  ".$where."",3);

$pages = ceil($count/$num);
$sql = "SELECT  a.plan_start_date , 
					a.start_quantity,a.wip_entity_name,a.quantity_completed,a.primary_item,b.item_desc,b.item_name,
					a.creation_date,c.request_name
				FROM wip_jobs_all a ,sf_item_no b,wip_material_request c
            WHERE  1=1 and a.primary_item=b.item_no and a.wip_entity_name=c.wip_entity_name and c.status='正常' ".$where."  ORDER BY a.wip_entity_name  desc limit ".$off.",".$num;
   
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

<title>查询料号</title>
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
				$("#wip_entity_name").text(c[0]);
                $("#primary_item").text(c[1]);
                $("#quantity").text(c[2]);
                $("#plan_start_date").text(c[3]); 
				$("#item_name").text(c[4]);
				$("#request_name").text(c[5]);
				$("#item_desc").text(c[6]);
				   W.document.getElementById("wip_entity_name").value = $("label#wip_entity_name").text();
                        W.document.getElementById("primary_item").value = $("label#primary_item").text();
                        W.document.getElementById("quantity").value = $("label#quantity").text();
                        W.document.getElementById("plan_start_date").value = $("label#plan_start_date").text(); 
						 W.document.getElementById("item_name").value = $("label#item_name").text();
						 W.document.getElementById("request_name").value = $("label#request_name").text();
						  W.document.getElementById("item_desc").value = $("label#item_desc").text();
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
				<img src="./css/xenos/images/magnifier.png" title="查询工单" alt="查询工单">查询工单
			</p>
			<form action="./BtnSearchWIPIssueRuest.php?fwValue=<?=$cat?>&cat=<?=$cat?>" method ="POST">
				<div>
					 
					<table cellpadding="3" class="selection">
					<tr>
					<td>
							工单名称：
						</td>
						<td>
							<input type="text" name="wip_entity_name" value="<?=$wip_entity_name?>">
						</td>
						<td>
							料号：
						</td>
						<td>
							<input type="text" name="ItemNo" value="<?=$ItemNo?>">
						</td>
                        <td>
							名称：
						</td>
						<td>
							<input type="text" name="ItemName" value="<?=$ItemName?>">
						</td>
						
					</tr>
					
					
					</table>
					<div class="centre">
					     
						<input type="submit" value="查找">
					</div>
					</form>
					<br/>
					<table cellpadding="2" class="selection" id="ck_company">
					<tr><th width =120>申请单号 </th> 
						<th width =120>工单名称 </th> 
			<th width =120> 料号 </th>
			<th width =170>料号名称 </th>
			<th width =170> 规格型号 </th>
			<th width =120> 预计开工日 </th> 
            <th width =90> 开工量 </th> 
					
					</tr>
					<?php foreach($list as $arr=>$row2){?>
					<tr class="EvenTableRows">
					<td> <?=$row2['request_name']?> </td>
					<td> <?=$row2['wip_entity_name']?> </td>
					<td> <?=$row2['primary_item']?> </td>
					<td> <?=$row2['item_name']?> </td>
					<td> <?=$row2['item_desc']?> </td>
					<td> <?=date('Y-m-d',$row2['plan_start_date'])?> </td> 
						<td> <?=$row2['start_quantity']?> </td>
						 
						<td>
						<input name="a" type="hidden" value="选择" class="coupons" rel="<?=$row2['wip_entity_name']?>:<?=$row2['primary_item']?>:<?=$row2['start_quantity']?>:<?=date('Y-m-d',$row2['plan_start_date'])?>:<?=$row2['item_name']?>:<?=$row2['request_name']?>:<?=$row2['item_desc']?>">				 
						</td>
					</tr>
					<?php }?>
					
					 
					</table>
					<br/>
					<div class="centre">
					<?=show_page('?page=',$page,$pages,$count,'&fwValue='.$cat.'&cat='.$cat.'&ItemNo='.$ItemNo.'&wip_entity_name'.$wip_entity_name.'');?>
					
					</div>
				</div>
			
		</div>
	</div>
	<div id="FooterDiv">
		<div id="FooterWrapDiv">
			 
		</div>
	</div>
                    <div style="display:none">
        <p><label class="text-info">wip_entity_name:</label>　<label id="wip_entity_name"></label></p>
    </div>
     <div style="display:none">
        <p><label class="text-info">primary_item:</label>　<label id="primary_item"></label></p>
    </div>
     <div style="display:none">
        <p><label class="text-info">quantity:</label>　<label id="quantity"></label></p>
    </div>
   <div style="display:none">
        <p><label class="text-info">plan_start_date:</label>　<label id="plan_start_date"></label></p>
    </div> 
	</div> <div style="display:none">
        <p><label class="text-info">item_name:</label>　<label id="item_name"></label></p>
    </div>
	</div> <div style="display:none">
        <p><label class="text-info">request_name</label>　<label id="request_name"></label></p>
    </div>
	</div> <div style="display:none">
        <p><label class="text-info">item_desc:</label>　<label id="item_desc"></label></p>
    </div>
                    <div style="display:none">
                        <p><input type="hidden" name="cat" id="cat" value="<?=$cat?>"/></p>
						<p><input type="hidden" name="fwValue" value="<?=$fwValue?>" id="fwValue"/></p>
                    </div>
   	
 
</body>
</html>

