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
$wip_entity_name  = isset($_REQUEST['wip_entity_name']) ? $_REQUEST['wip_entity_name'] : '';	
  
$where = '';
if($wip_entity_name) {
   $where .= ' and w.wip_entity_name like "%'.$wip_entity_name.'%"';
}
 
 
if($cat) {
   $where .= " and w.segment1 =  '".$cat."'" ;
}
 
$num = 20;
$off = $num*($page-1);
 
$count = db_sql("select w.segment1,quantity_issued- required_quantity 
from wip_material_requierments w,wip_jobs_all j where quantity_issued>required_quantity and w.wip_entity_name=j.wip_entity_name and j.status_type='开始'  ".$where.'',3);

$pages = ceil($count/$num);
$sql = "select w.wip_entity_name,w.operation_seq_num,w.segment1,(quantity_issued- required_quantity) chaohao_qty 
from wip_material_requierments w,wip_jobs_all j where quantity_issued>required_quantity and w.wip_entity_name=j.wip_entity_name and j.status_type='开始'  ".$where.' ORDER BY segment1  desc limit '.$off.','.$num.'';
 
// echo $sql ;
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
				$("#form_wip_entity_name").text(c[0]);
				$("#form_chaohao_qty").text(c[1]); 
				$("#form_operation_seq_num").text(c[2]); 
				  W.document.getElementById('text_wip_entity_name'+$('#fwValue').val()).value = $("label#form_wip_entity_name").text(); 
				W.document.getElementById('text_slect_chaohao_qty'+$('#fwValue').val()).value = $("label#form_chaohao_qty").text();
				W.document.getElementById('text_slect_operation_seq_num'+$('#fwValue').val()).value = $("label#form_operation_seq_num").text();
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
				<img src="./css/xenos/images/magnifier.png" title="查询料号" alt="查询料号">查询料号
			</p>
			<form action="./SearchItemChaohaoWIP.php?fwValue=<?=$cat?>&cat=<?=$cat?>" method ="POST">
				<div>
					 
					<table cellpadding="3" class="selection">
					<tr>
						<td>
							工单名称：
						</td>
						<td>
							<input type="text"  name="wip_entity_name" value="<?=$wip_entity_name?>">
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
						<th class="ascending" width ="60">
							工单名称
						</th> 
						<th class="ascending" width ="90">
							料号 
						</th> 
						 
						<th class="ascending" width ="90">
							超耗数量
						</th> 
					 
						 
					</tr>
					<?php foreach($list as $arr=>$row2){?>
					<tr class="EvenTableRows">
						<td>
							<?=$row2['wip_entity_name']?>
						</td> 
						 <td> <?=$row2['segment1']?> </td> 
						<td> <?=$row2['chaohao_qty']?> </td>  
						  
						<td>
						<input name="a" type="hidden" value="选择" class="coupons" rel="<?=$row2['wip_entity_name']?>:<?=$row2['chaohao_qty']?>:<?=$row2['operation_seq_num']?>">				 
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
                         <p><label class="text-info">wip_entity_name:</label>　<label id="form_wip_entity_name"></label></p>
                    </div>

					 <div style="display:none">
                         <p><label class="text-info">chaohao_qty</label>　<label id="form_chaohao_qty"></label></p>
                    </div>
				 <div style="display:none">
                         <p><label class="text-info">operation_seq_num</label>　<label id="form_operation_seq_num"></label></p>
                    </div>
					 
                    <div style="display:none">
                        <p><input type="hidden" name="cat" id="cat" value="<?=$cat?>"/></p>
						<p><input type="hidden" name="fwValue" value="<?=$fwValue?>" id="fwValue"/></p>
                    </div>
  
 
</body>
</html>

