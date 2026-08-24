<?php 

/*$db_host='localhost';
$db_user='a0316105426';
$db_pass='1ea5bca3';
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
$OrderNumber  = isset($_REQUEST['OrderNumber']) ? $_REQUEST['OrderNumber'] : '';	
$ItemNo  = isset($_REQUEST['ItemNo']) ? $_REQUEST['ItemNo'] : '';	
$ItemDesc = isset($_REQUEST['ItemDesc']) ? $_REQUEST['ItemDesc'] : '';	 
$where = '';

if($OrderNumber) {
   $where .= ' and order_number like "%'.$OrderNumber.'%"';
}

if($ItemNo) {
   $where .= ' and item_no like "%'.$ItemNo.'%"';
}
 
if($ItemDesc) {
   $where .= ' and item_desc like "%'.$ItemDesc.'%"';
}


// if($cat) {
//    $where .= "  and  sh.customer_code='". $cat . "'  "; 

// }
 
$num = 10;
$off = $num*($page-1);
 
 $count = db_sql('select ioq.stockid,si.item_desc,si.units, ioq.price,
 sum(quantity) onhand_quantity
from  
inv_onhand_quantity_all ioq,
sf_item_no si
where   ioq.stockid=si.item_no
 and 1=1  '.$where.'group by ioq.stockid,si.item_desc,si.units , ioq.price',3);

$pages = ceil($count/$num);
$sql = 'select ioq.stockid,si.item_desc,si.units, ioq.price,
 sum(quantity) onhand_quantity
from  
inv_onhand_quantity_all ioq,
sf_item_no si
where   ioq.stockid=si.item_no
 and 1=1 '.$where.' group by ioq.stockid,si.item_desc,si.units , ioq.price
 ORDER BY ioq.stockid  desc limit '.$off.','.$num.'';
   
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

<title>查询待出货订单</title>
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
                        
					    W.document.getElementById('text_slect_units'+$('#fwValue').val()).value = $("label#form_units").text();
						W.document.getElementById('text_slect_ItemNo'+$('#fwValue').val()).value = $("label#form_ItemNo").text();
						W.document.getElementById('text_slect_ItemDesc'+$('#fwValue').val()).value = $("label#form_ItemDesc").text();
						 
						W.document.getElementById('text_slect_onhand_quantity'+$('#fwValue').val()).value = $("label#form_onhand_quantity").text(); 
						W.document.getElementById('text_slect_price'+$('#fwValue').val()).value = $("label#form_price").text(); 
                    
						
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
			 	$("#form_price").text(c[2]);
				$("#form_onhand_quantity").text(c[3]); 
				
				$("#form_ItemDesc").text(c[4]);
				
				
			});
			 
			
			
        });
    </script>
<div id="CanvasDiv">
 
	<div id="BodyDiv">
		<div id="BodyWrapDiv">
			<p class="page_title_text">
				<img src="/JXC/css/xenos/images/magnifier.png" title="查询料号" alt="查询料号">查询料号
			</p>
			<form action="/JXC/Searchwaitship.php?fwValue=<?=$cat?>&cat=<?=$cat?>" method ="POST">
				<div>
					 
					<table cellpadding="3" class="selection">
					<tr>
				 
						<td>
							规格型号
						</td>
						<td>
							<input type="text" name="ItemNo" value="<?=$ItemNo?>">
						</td>
						<td>
							产品名称
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
					 
						
						<th   width ="150">
							规格型号
						</th>
						<th   width ="200">
							产品名称
						</th>
						<th   width ="50">
							单位
						</th>
 
						<th  width ="80">
							单价
						</th>
						
						<th  width ="80">
							库存量
						</th>

						
						 
						<th class="ascending" width ="50">
							操作
						</th>
					</tr>
					<?php foreach($list as $arr=>$row2){?>
					<tr class="EvenTableRows">  
					
						<td><?=$row2['stockid']?> </td>
						<td> <?=$row2['item_desc']?> </td>
						<td> <?=$row2['units']?> </td>
						<td> <?=$row2['price']?> </td>
										 
						<!-- <td> <?=$row2['subinventory_code']?> </td>   -->
						<td> <?=$row2['onhand_quantity']?> </td>  
						
						<td>

						<input name="a" type="radio" value="选择" class="coupons" rel="<?=$row2['stockid']?>:<?=$row2['units']?>:<?=$row2['price']?>:<?=$row2['onhand_quantity']?>:<?=$row2['item_desc']?>">				 
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
                         <p><label class="text-info">order_Quantity:</label>　<label id="form_order_quantity"></label></p>
                    </div>

				
				
					<div style="display:none">
                         <p><label class="text-info">Onhand_quantity:</label>　<label id="form_onhand_quantity"></label></p>
                    </div>
                    
					<div style="display:none">
                         <p><label class="text-info">price:</label>　<label id="form_price"></label></p>
                    </div>
 
                   
                    <div style="display:none">
                        <p><input type="hidden" name="cat" id="cat" value="<?=$cat?>"/></p>
						<p><input type="hidden" name="fwValue" value="<?=$fwValue?>" id="fwValue"/></p>
                    </div>
  
 
</body>
</html>

