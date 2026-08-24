<?php 

include('includes/login2.inc');
function db_sql($sql,$type=1)
{
  $arr = array(); 
  $sql = mysql_query($sql);
   // $row = mysql_fetch_array($sql);--这里执行会导致type=2再执行一次，里面第一行资料被捞过从第二行开始获取,结果少资料
  if ($type==1) return mysql_fetch_array($sql);
  if ($type==2) 
  {
	while ($row=mysql_fetch_array($sql)) 
	{
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
$wip_entity_name  = isset($_REQUEST['wip_entity_name']) ? $_REQUEST['wip_entity_name'] : '';	 
$where = '';
if($wip_entity_name) 
{
  $where .= ' and h.wip_entity_name like "%'.$wip_entity_name.'%"';
}
 
 
if($cat) 
{
	
   $where .= '';

}
 
$num = 10;
$off = $num*($page-1);
 
$count = db_sql('select b.customer_code, wip_entity_name,plan_start_date,h.start_quantity,h.quantity_completed,a.item_no,a.item_name,a.item_desc,a.gongyi
from wip_jobs_all h ,sf_item_no a,so_headers_all b
where h.start_quantity >h.quantity_completed
and so_header_number=b.order_number 
and h.primary_item 	=a.item_no
and 1=1 '.$where.'',3);
  	
$pages = ceil($count/$num);
$sql = 'select b.customer_code,(h.start_quantity-h.quantity_completed) wait_qty,wip_entity_name,plan_start_date,h.start_quantity,h.quantity_completed,a.item_no,a.item_name,a.item_desc,a.gongyi
from wip_jobs_all h ,sf_item_no a,so_headers_all b
where h.start_quantity >h.quantity_completed
and so_header_number=b.order_number 
and h.primary_item 	=a.item_no and 1=1 '.$where.' ORDER BY h.plan_start_date  desc limit '.$off.','.$num.'';
$list = db_sql($sql,2);
 

function show_page($url,$page,$pages,$total,$t0='')
{  
  $str = '';
  $page = $page > $pages ? $pages : $page;
  if ($page>1) 
  {
    $str .= '<a class="pre" href="'.$url.(1).$t0.'">上一页</a>&nbsp;';
  } 
  else 
  {
	$str .= '<a class="pre">上一页</a>&nbsp;';
  }
  if ($page<5) $start=1; $end=5;
  if ($page>=5)
  {
    $start = $page-2;
    $end = $page+3;
  }
  $end = $end > $pages ? $pages : $end;
  for ($i=$start;$i<=$end;$i++) 
  {
	if ($i==$page) 
	{
	  $str .= '<span class="cur">'.$i.'</span>&nbsp;';
	} 
	else 
	{
	  $str .= '<a href="'.$url.$i.$t0.'">'.$i.'</a>&nbsp;&nbsp;';
	}
  }
    
  if ($page>=1 && $page<$pages) 
  {
	$str .= '<a href="'.$url.($page+1).$t0.'">下一页</a>&nbsp;';
  } 
  else 
  {   
	$str .= '<a class="next">下一页</a>&nbsp;';
  }

  $str .= '<span class="pages_c">页次:'.$page.'/'.$pages.'&nbsp;&nbsp;&nbsp;总计:'.$total.' </span>';
  return $str;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>

<title>生产领料申请单</title>
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
				$("#form_customer_code").text(c[0]);
				$("#form_wip_entity_name").text(c[1]);
			    $("#form_item_no").text(c[2]);
			    $("#form_item_name").text(c[3]);
			    $("#form_item_desc").text(c[4]);
			    $("#form_gongyi").text(c[5]);
			    $("#form_start_quantity").text(c[6]);
			    $("#form_quantity_completed").text(c[7]);
			    $("#form_wait_qty").text(c[8]);
				$("#form_plan_start_date").text(c[9]); 
				    W.document.getElementById('text_slect_customer_code'+$('#fwValue').val()).value = $("label#form_customer_code").text(); 
				    W.document.getElementById('text_slect_wip_entity_name'+$('#fwValue').val()).value = $("label#form_wip_entity_name").text(); 
					    W.document.getElementById('text_slect_item_no'+$('#fwValue').val()).value = $("label#form_item_no").text();
						W.document.getElementById('text_slect_item_name'+$('#fwValue').val()).value = $("label#form_item_name").text();
						W.document.getElementById('text_slect_item_desc'+$('#fwValue').val()).value = $("label#form_item_desc").text();
						W.document.getElementById('text_slect_gongyi'+$('#fwValue').val()).value = $("label#form_gongyi").text();
						W.document.getElementById('text_slect_start_quantity'+$('#fwValue').val()).value = $("label#form_start_quantity").text();
						W.document.getElementById('text_slect_quantity_completed'+$('#fwValue').val()).value = $("label#form_quantity_completed").text();
						W.document.getElementById('text_slect_wait_qty'+$('#fwValue').val()).value = $("label#form_wait_qty").text();
					    W.document.getElementById('text_slect_plan_start_date'+$('#fwValue').val()).value = $("label#form_plan_start_date").text(); 
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
				<img src="/JXC/css/xenos/images/magnifier.png" title="查询订单" alt="查询订单">查询订单
			</p>
			<form action="./BtnSearchWIPWaitComplete.php?fwValue=<?=$cat?>&cat=<?=$cat?>" method ="POST">
				<div>
					 
					<table cellpadding="3" class="selection">
					<tr>
						
						<td> 
							工单号码：
						</td>
						<td>
							<input type="text" name="wip_entity_name" value="<?=$wip_entity_name?>">
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
						<th   width ="130"> 客户简称 </th>
						<th > 工单号码 </th> 
						<th > 料号 </th>
						<th > 料号名称 </th>
						<th > 规格型号 </th>
						<th > 工艺 </th>
						<th > 开工量 </th>
						<th > 已入库量 </th>
						<th width ="90">	 开工日期 </th> 
					</tr>
					<?php foreach($list as $arr=>$row2){?>
					<tr class="EvenTableRows">
						<td> <?=$row2['customer_code']?></td>
						<td> <?=$row2['wip_entity_name']?></td>
						<td> <?=$row2['item_no']?> </td>  
						<td> <?=$row2['item_name']?> </td>  
						<td> <?=$row2['item_desc']?> </td>  
						<td> <?=$row2['gongyi']?> </td>   
						<td> <?=$row2['start_quantity']?> </td>   
						<td> <?=$row2['quantity_completed']?> </td>  
						<td> <?= date('Y-m-d',$row2['plan_start_date'])?> </td>
					 	</td>
					<td>
					   <input name="a" type="hidden" value="选择" class="coupons" rel="<?=$row2['customer_code']?>:<?=$row2['wip_entity_name']?>:<?=$row2['item_no']?>:<?=$row2['item_name']?>:<?=$row2['item_desc']?>:<?=$row2['gongyi']?>:<?=$row2['start_quantity']?>:<?=$row2['quantity_completed']?>:<?=$row2['wait_qty']?>:<?=date('Y-m-d',$row2['plan_start_date'])?>">	  			 
					</td>
					
					</tr>
					<?php }?>
					 
					 
					</table>
					<br/>
					<div class="centre">
					<?=show_page('?page=',$page,$pages,$count,'&fwValue='.$cat.'&cat='.$cat.'&wip_entity_name='.$wip_entity_name.'');?>
					
					</div>
				</div>
			
		</div>
	</div>
	<div id="FooterDiv">
		<div id="FooterWrapDiv">
			 
		</div>
	</div>
                    <div style="display:none">
                         <p><label class="text-info">customer_code:</label>　<label id="form_customer_code"></label></p>
                    </div>
					<div style="display:none">
                         <p><label class="text-info">wip_entity_name:</label>　<label id="form_wip_entity_name"></label></p>
                    </div>

					<div style="display:none">
                         <p><label class="text-info">item_no</label>　<label id="form_item_no"></label></p>
                    </div>
					<div style="display:none">
                         <p><label class="text-info">item_name</label>　<label id="form_item_name"></label></p>
                    </div>
					<div style="display:none">
                         <p><label class="text-info">item_desc</label>　<label id="form_item_desc"></label></p>
                    </div>
					<div style="display:none">
                         <p><label class="text-info">gongyi，h</label>　<label id="form_gongyi，h"></label></p>
                    </div>
					<div style="display:none">
                         <p><label class="text-info">start_quantity</label>　<label id="form_start_quantity"></label></p>
                    </div>
					<div style="display:none">
                         <p><label class="text-info">quantity_completed</label>　<label id="form_quantity_completed"></label></p>
                    </div>
					<div style="display:none">
                         <p><label class="text-info">wait_qty</label>　<label id="form_wait_qty"></label></p>
                    </div>

					 <div style="display:none">
                         <p><label class="text-info">plan_start_date</label>　<label id="form_plan_start_date"></label></p>
                    </div>	
					 
                    <div style="display:none">
                        <p><input type="hidden" name="cat" id="cat" value="<?=$cat?>"/></p>
						<p><input type="hidden" name="fwValue" value="<?=$fwValue?>" id="fwValue"/></p>
                    </div>
  
 
</body>
</html>

