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
$item_no  = isset($_REQUEST['item_no']) ? $_REQUEST['item_no'] : '';	
$item_desc = isset($_REQUEST['item_desc']) ? $_REQUEST['item_desc'] : ''; 	
$where = "";
if($item_no) {
   $where .= ' and a.item_no like "%'.$item_no.'%"';
}
 
if($item_desc) {
   $where .= ' and a.item_desc like "%'.$item_desc.'%"';
}

if($cat) {
	
     
	$where .= " and 1=1 "; 

}

$num = 10;
$off = $num*($page-1);
 
    $count = db_sql("SELECT a.item_no,a.item_desc,a.units,b.quantity,b.wip_entity_name
    FROM sf_item_no  a,so_lines_all b where disable_flag<>'N'
	and a.item_no=b.stockid
	and b.quantity_plan=0

	and wip_entity_name  not in (select WIP_ENTITY_NAME from wip_operations)
	".$where.'',3);

$pages = ceil($count/$num);
$sql = "SELECT a.item_no,a.item_desc,a.units,b.quantity,b.wip_entity_name,b.order_number,b.line
    FROM sf_item_no  a,so_lines_all b where disable_flag<>'N'
	and a.item_no=b.stockid
	and b.quantity_plan=0
	and wip_entity_name  not in (select WIP_ENTITY_NAME from wip_operations)".$where.' ORDER BY item_no  desc limit '.$off.','.$num.'';
   
 
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
<link rel="shortcut icon" href="/favicon.ico"/>
<link rel="icon" href="/favicon.ico"/>
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
                        W.document.getElementById('text_slect_item_no'+$('#fwValue').val()).value = $("label#form_item_no").text(); 
					    W.document.getElementById('text_slect_item_desc'+$('#fwValue').val()).value = $("label#form_item_desc").text();
						W.document.getElementById('text_slect_units'+$('#fwValue').val()).value = $("label#form_units").text();
						W.document.getElementById('text_slect_quantity'+$('#fwValue').val()).value = $("label#form_quantity").text();
						W.document.getElementById('text_slect_wip_entity_name'+$('#fwValue').val()).value = $("label#form_wip_entity_name").text();
						W.document.getElementById('text_slect_order_number'+$('#fwValue').val()).value = $("label#form_order_number").text();
						W.document.getElementById('text_slect_line'+$('#fwValue').val()).value = $("label#form_line").text();
						$("#xianshi").css("display","block"); 
                        break;
                    default :
                        alert('Data Post Error');
                }
            };
    
			
			$(".coupons").livequery("click", function() {
				var rel = this.getAttribute('rel');
				c = rel.split(":");
				$("#form_item_no").text(c[0]);
				$("#form_item_desc").text(c[1]);
				$("#form_units").text(c[2]);
				$("#form_quantity").text(c[3]);
				$("#form_wip_entity_name").text(c[4]);
				$("#form_order_number").text(c[5]);
				$("#form_line").text(c[6]);
			});

			 
			
			
        });
    </script>
<div id="CanvasDiv">
 
	<div id="BodyDiv">
		<div id="BodyWrapDiv">
			<p class="page_title_text">
				<img src="/css/xenos/images/magnifier.png" title="查询料号" alt="查询料号">查询料号
			</p>
			<form action="/BtnSearchWIPNoPlan.php?fwValue=<?=$cat?>&cat=<?=$cat?>" method ="POST">
				<div>
					 
					<table cellpadding="3" class="selection">
					<tr>
						<td>
							料号：
						</td>
						<td>
							<input type="text" name="item_no" value="<?=$item_no?>">
						</td>
						<td>
							料号名称：
						</td>
						<td>
							<input type="text" name="item_desc" value="<?=$item_desc?>">
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
			
					 <th  width ="120">
							工单名称
						</th>
						<th  width ="100">
							订单号码
						</th>
						<th  width ="40">
							行
						</th>
						<th  width ="50">
							数量
						</th>

						<th  width ="200">
							成品图号
						</th>
						<th  width ="350">
							料号名称
						</th>
						<th  width ="60">
							单位
						</th>
					
						<th   width ="50">
							操作
						</th>
					</tr>
					<?php foreach($list as $arr=>$row2){?>
					<tr class="EvenTableRows">
					<td> <?=$row2['wip_entity_name']?> </td>
					<td> <?=$row2['order_number']?> </td>
					<td> <?=$row2['line']?> </td>
					<td> <?=$row2['quantity']?> </td>
						<td> <?=$row2['item_no']?> </td>
						<td> <?=$row2['item_desc']?></td>
						<td> <?=$row2['units']?></td>
						<td>
						<input name="a" type="radio" value="选择" class="coupons" rel="<?=$row2['item_no']?>:<?=$row2['item_desc']?>:<?=$row2['units']?>:<?=$row2['quantity']?>:<?=$row2['wip_entity_name']?>:<?=$row2['order_number']?>:<?=$row2['line']?>">				 
						</td>
					</tr>
					<?php }?>
					 
					    
					</table>
					<br/>
					<div class="centre">
					<?=show_page('?page=',$page,$pages,$count,'&fwValue='.$cat.'&cat='.$cat.'&item_no='.$item_no.'&item_desc'.$item_desc.'');?>
					
					</div>
				</div>
			
		</div>
	</div>
	<div id="FooterDiv">
		<div id="FooterWrapDiv">
			 
		</div>
	</div>
                   <div style="display:none">
                         <p><label class="text-info">item_no</label>　<label id="form_item_no"></label></p>
                    </div>

					 <div style="display:none">
                         <p><label class="text-info">item_desc</label>　<label id="form_item_desc"></label></p>
                    </div>		
					<div style="display:none">
                         <p><label class="text-info">units</label>　<label id="form_units"></label></p>
                    </div>	
					<div style="display:none">
                         <p><label class="text-info">quantity</label>　<label id="form_quantity"></label></p>
                    </div>
					<div style="display:none">
                         <p><label class="text-info">wip_entity_name</label>　<label id="form_wip_entity_name"></label></p>
                    </div>  
					<div style="display:none">
                         <p><label class="text-info">order_number</label>　<label id="form_order_number"></label></p>
                    </div>
					<div style="display:none">
                         <p><label class="text-info">line</label>　<label id="form_line"></label></p>
                    </div>  
                    
                    <div style="display:none">
                        <p><input type="hidden" name="cat" id="cat" value="<?=$cat?>"/></p>
						<p><input type="hidden" name="fwValue" value="<?=$fwValue?>" id="fwValue"/></p>
                    </div>
  
 
</body>
</html>

