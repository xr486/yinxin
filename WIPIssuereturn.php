<?php 
date_default_timezone_set('Asia/Shanghai');


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
$sub     = isset($_REQUEST['sub']) ? $_REQUEST['sub'] : 'buliao';	
$page    = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;	
$operation_seq_num  = isset($_REQUEST['operation_seq_num']) ? $_REQUEST['operation_seq_num'] : '';	
$item_no  = isset($_REQUEST['item_no']) ? $_REQUEST['item_no'] : '';	
$item_name = isset($_REQUEST['item_name']) ? $_REQUEST['item_name'] : '';	 
$where = '   ';
if($operation_seq_num) {
   $where .= ' and b.operation_seq_num like "%'.$operation_seq_num.'%"';
}
if($item_no) {
   $where .= ' and c.item_no like "%'.$item_no.'%"';
}
 
if($item_name) {
   $where .= ' and c.item_name like "%'.$item_name.'%"';
}


 
$num = 10;
$off = $num*($page-1);


$count = db_sql("select  b.comments,required_quantity,
b.quantity_per_assembly,b.quantity_issued,b.operation_seq_num,c.item_no,c.item_desc,c.item_name,
(cc.quantity) onhand_quantity,cc.shengchan_date,cc.lot_num,
units,
(select sum(quantity_issued- required_quantity) from wip_material_requierments w,wip_jobs_all j where w.segment1=c.item_no and w.wip_entity_name<>b.wip_entity_name and quantity_issued>required_quantity and w.wip_entity_name=j.wip_entity_name and j.status_type='开始' ) chaofa_qty,
(required_quantity-quantity_issued) qianfa_qty  
               from wip_material_requierments b,sf_item_no c,inv_onhand_quantity_all cc
               where b.segment1=c.item_no   and cc.stockid=c.item_no and cc.subinventory_code='".$sub."'
               and b.wip_entity_name='".$cat."' 
               and required_quantity > b.quantity_issued ".$where."",3);

$pages = ceil($count/$num);

$sql = "select  b.comments,required_quantity,
b.quantity_per_assembly,b.quantity_issued,b.operation_seq_num,c.item_no,c.item_desc,c.item_name,
(cc.quantity) onhand_quantity,cc.shengchan_date,cc.lot_num,
units,
(select sum(quantity_issued- required_quantity) from wip_material_requierments w,wip_jobs_all j where w.segment1=c.item_no and w.wip_entity_name<>b.wip_entity_name and quantity_issued>required_quantity and w.wip_entity_name=j.wip_entity_name and j.status_type='开始' ) chaofa_qty,
(required_quantity-quantity_issued) qianfa_qty  
               from wip_material_requierments b,sf_item_no c,inv_onhand_quantity_all cc
               where b.segment1=c.item_no   and cc.stockid=c.item_no and cc.subinventory_code='".$sub."'
               and b.wip_entity_name='".$cat."' 
               and required_quantity > b.quantity_issued
               ".$where."
               order by b.operation_seq_num,c.item_no limit ".$off.",".$num."";
//   echo $sql;
 
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

<title>查询工单领用</title>
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
				$("#form_operation_seq_num").text(c[0]);
				$("#form_item_no").text(c[1]);
				$("#form_item_name").text(c[2]);
				$("#form_item_desc").text(c[3]); 
				$("#form_comments").text(c[4]);
				$("#form_onhand_quantity").text(c[5]); 
				$("#form_quantity_per_assembly").text(c[6]);
				$("#form_required_quantity").text(c[7]);
				$("#form_quantity_issued").text(c[8]); 
				$("#form_qianfa_qty").text(c[9]);
                $("#form_chaofa_qty").text(c[10]);
				$("#form_shengchan_date").text(c[11]);
                $("#form_lot_num").text(c[12]);
				$("#form_units").text(c[13]);
				  W.document.getElementById('text_slect_operation_seq_num'+$('#fwValue').val()).value = $("label#form_operation_seq_num").text();
                        W.document.getElementById('text_slect_item_no'+$('#fwValue').val()).value = $("label#form_item_no").text(); 
                        W.document.getElementById('text_slect_item_name'+$('#fwValue').val()).value = $("label#form_item_name").text(); 
						W.document.getElementById('text_slect_item_desc'+$('#fwValue').val()).value = $("label#form_item_desc").text();
					
						W.document.getElementById('text_slect_comments'+$('#fwValue').val()).value = $("label#form_comments").text();
						W.document.getElementById('text_slect_onhand_quantity'+$('#fwValue').val()).value = $("label#form_onhand_quantity").text();
						W.document.getElementById('text_slect_quantity_per_assembly'+$('#fwValue').val()).value = $("label#form_quantity_per_assembly").text();

						W.document.getElementById('text_slect_required_quantity'+$('#fwValue').val()).value = $("label#form_required_quantity").text(); 
						W.document.getElementById('text_slect_quantity_issued'+$('#fwValue').val()).value = $("label#form_quantity_issued").text(); 
						W.document.getElementById('text_slect_qianfa_qty'+$('#fwValue').val()).value = $("label#form_qianfa_qty").text(); 
						W.document.getElementById('text_slect_chaofa_qty'+$('#fwValue').val()).value = $("label#form_chaofa_qty").text(); 
						W.document.getElementById('text_slect_shengchan_date'+$('#fwValue').val()).value = $("label#form_shengchan_date").text(); 
						W.document.getElementById('text_slect_lot_num'+$('#fwValue').val()).value = $("label#form_lot_num").text(); 
						W.document.getElementById('text_slect_units'+$('#fwValue').val()).value = $("label#form_units").text(); 
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
				<img src="./css/xenos/images/magnifier.png" title="查询料号领用" alt="查询料号领用">查询料号领用
			</p>
			<form action="./WIPIssuereturn.php?fwValue=<?=$cat?>&cat=<?=$cat?>&sub=<?=$sub?>" method ="POST">
				<div>
					 
					<table cellpadding="3" class="selection">
					<div class="text-nav">
					    <div class="text-nav-1"><div>
							制程：
	</div>
						
							<input type="text" name="operation_seq_num" value="<?=$operation_seq_num?>">
	</div>
	<div class="text-nav-1"><div>
							料号
	</div>
						
				<input type="text" name="item_no" value="<?=$item_no?>">
	</div>
	<div class="text-nav-1"><div>
							料号名称
	</div>
						
							<input type="text" name="item_name" value="<?=$item_name?>">
	</div>
	</div>
					
					
					</table>
					<div class="centre">
					     
						<input type="submit" value="查找">
					</div>
					</form>
					<br/>
					<div class="text-nav-table"> 
					<table cellpadding="2" class="selection" id="ck_company">
					<tr>
                    <th bgcolor="#87CEFA" >制程</th>
                                
                                <th bgcolor="#87CEFA" width="100" >料号</th>
                                <th bgcolor="#87CEFA" width="140">料号名称</th>
								<th bgcolor="#87CEFA" width="40">规格型号</th>
								<th bgcolor="#87CEFA" width="40">备注</th>
                                <th bgcolor="#87CEFA" width="60">库存量</th> 
                                <th bgcolor="#87CEFA" width="40">单耗</th> 
                                <th bgcolor="#87CEFA" width="60">需求量</th>   
                                <th bgcolor="#87CEFA" width="70">已发量</th>  
                                <th bgcolor="#87CEFA" width="70">欠发量</th>  
                                <th bgcolor="#87CEFA" width="70">超发量</th> 
                                <th bgcolor="#87CEFA" width="80" >生产日期</th>
                                <th bgcolor="#87CEFA" width="80" >批号</th>
                                <th bgcolor="#87CEFA" width="10">单位</th>
						
					
					</tr>
					<?php foreach($list as $arr=>$row2){
						
						if($row2['shengchan_date'] > 0){
							$shengchan_date = date('Y-m-d',$row2['shengchan_date']);
						}else{
							$shengchan_date = '';
						}
						?>
					<tr class="EvenTableRows">
					<td>
							<?=$row2['operation_seq_num']?>
						</td>
						<td>
							<?=$row2['item_no']?>
						</td>
						
						<td> <?=$row2['item_name']?>
						</td>
						<td>
                             <?=$row2['item_desc']?>
						</td>
						<td> <?=$row2['comments']?>
						</td> 
						<td> <?=$row2['onhand_quantity']?> </td>
						<td> <?=$row2['quantity_per_assembly']?> </td> 
						<td> <?=$row2['required_quantity']?> </td>
						<td> <?=$row2['quantity_issued']?> </td> 
						<td> <?=$row2['qianfa_qty']?> </td> 
						<td> <?=$row2['chaofa_qty']?> </td> 
						<td> <?=$shengchan_date?> </td> 
                        <td> <?=$row2['lot_num']?> </td> 
                        <td> <?=$row2['units']?> </td> 
						<td>
						<input name="a" type="hidden" value="选择" class="coupons" rel="<?=$row2['operation_seq_num']?>:<?=$row2['item_no']?>:<?=$row2['item_name']?>:<?=$row2['item_desc']?>:<?=$row2['comments']?>:<?=$row2['onhand_quantity']?>:<?=$row2['quantity_per_assembly']?>:<?=$row2['required_quantity']?>:<?=$row2['quantity_issued']?>:<?=sprintf("%.3f",$row2['qianfa_qty'])?>:<?=$row2['chaofa_qty']?>:<?=$shengchan_date?>:<?=$row2['lot_num']?>:<?=$row2['units']?>">				 
						</td>
					</tr>
					<?php }?>
					 
					 
					</table></div>
					<br/>
					<div class="centre">
					<?=show_page('?page=',$page,$pages,$count,'&fwValue='.$cat.'&cat='.$cat.'&sub='.$sub.'&item_no='.$item_no.'&item_name'.$item_name.'');?>
					
					</div>
				</div>
			
		</div>
	</div>
	<div id="FooterDiv">
		<div id="FooterWrapDiv">
			 
		</div>
	</div>
                   <div style="display:none">
                         <p><label class="text-info">operation_seq_num:</label>　<label id="form_operation_seq_num"></label></p>
                    </div>
                    <div style="display:none">
                         <p><label class="text-info">item_no:</label>　<label id="form_item_no"></label></p>
                    </div>
                    <div style="display:none">
                         <p><label class="text-info">item_name:</label>　<label id="form_item_name"></label></p>
                    </div>
                    <div style="display:none">
                         <p><label class="text-info">item_desc:</label>　<label id="form_item_desc"></label></p>
                    </div>
                    <div style="display:none">
                         <p><label class="text-info">comments:</label>　<label id="form_comments"></label></p>
                    </div>
                    <div style="display:none">
                         <p><label class="text-info">onhand_quantity</label>　<label id="form_onhand_quantity"></label></p>
                    </div>
                    <div style="display:none">
                         <p><label class="text-info">quantity_per_assembly</label>　<label id="form_quantity_per_assembly"></label></p>
                    </div>
                    <div style="display:none">
                         <p><label class="text-info">required_quantity</label>　<label id="form_required_quantity"></label></p>
                    </div> 
                    <div style="display:none">
                         <p><label class="text-info">quantity_issued</label>　<label id="form_quantity_issued"></label></p>
                    </div>
					<div style="display:none">
                         <p><label class="text-info">qianfa_qty</label>　<label id="form_qianfa_qty"></label></p>
                    </div>
                    <div style="display:none">
                         <p><label class="text-info">chaofa_qty</label>　<label id="form_chaofa_qty"></label></p>
                    </div>
              
                    <div style="display:none">
                         <p><label class="text-info">shengchan_date</label>　<label id="form_shengchan_date"></label></p>
                    </div> 
                    <div style="display:none">
                         <p><label class="text-info">lot_num</label>　<label id="form_lot_num"></label></p>
                    </div>
                    <div style="display:none">
                         <p><label class="text-info">units</label>　<label id="form_units"></label></p>
                    </div> 
   	 
					
					
 
                   
                    <div style="display:none">
                        <p><input type="hidden" name="cat" id="cat" value="<?=$cat?>"/></p>
                        <p><input type="hidden" name="sub" id="cat" value="<?=$sub?>"/></p>
						<p><input type="hidden" name="fwValue" value="<?=$fwValue?>" id="fwValue"/></p>
                    </div>
  
 
</body>
</html>

