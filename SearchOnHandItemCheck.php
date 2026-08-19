<?php 
include('includes/login2.inc'); 
date_default_timezone_set('Asia/Shanghai');
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
$item_name  = isset($_REQUEST['item_name']) ? $_REQUEST['item_name'] : '';	
$ItemDesc = isset($_REQUEST['ItemDesc']) ? $_REQUEST['ItemDesc'] : '';	 
$where = '';
if($ItemNo) {
   $where .= ' and a.item_no like "%'.$ItemNo.'%"';
}
 
if($ItemDesc) {
   $where .= ' and a.item_desc like "%'.$ItemDesc.'%"';
}
if($item_name) {
   $where .= ' and a.item_name like "%'.$item_name.'%"';
}

$where2 = '';
if($ItemNo) {
   $where2 .= ' and a.item_no like "%'.$ItemNo.'%"';
}
 
if($ItemDesc) {
   $where2 .= ' and a.item_desc like "%'.$ItemDesc.'%"';
}
if($item_name) {
   $where2 .= ' and a.item_name like "%'.$item_name.'%"';
}
 
$num = 10;
$off = $num*($page-1);
 
$count = db_sql("select a.item_no,a.item_desc,a.item_name,a.units,sum(ioq.quantity) quantity,a.youxiaoqi,ioq.lot_num,ioq.shengchan_date,ioq.cost_price
 from sf_item_no a,inv_onhand_quantity_all ioq  where ioq.subinventory_code='". $cat . "' ".$where." and a.item_no = ioq.stockid group by a.item_no,a.item_desc,a.item_name,a.units,a.youxiaoqi,ioq.lot_num,ioq.shengchan_date,ioq.cost_price
 union select a.item_no,a.item_desc,a.item_name,a.units,'' quantity,a.youxiaoqi, '' lot_num, '' shengchan_date, '' cost_price from sf_item_no a  where a.item_no not in (select stockid from inv_onhand_quantity_all) ".$where2." ",3);


$pages = ceil($count/$num);
$sql = "select a.item_no,a.item_desc,a.item_name,a.units,sum(ioq.quantity) quantity,a.youxiaoqi,ioq.lot_num,ioq.shengchan_date,ioq.cost_price
 from sf_item_no a,inv_onhand_quantity_all ioq  where ioq.subinventory_code='". $cat . "' ".$where." and a.item_no = ioq.stockid group by a.item_no,a.item_desc,a.item_name,a.units,a.youxiaoqi,ioq.lot_num,ioq.shengchan_date,ioq.cost_price
 union select a.item_no,a.item_desc,a.item_name,a.units,' ' quantity,a.youxiaoqi, ' ' lot_num, ' ' shengchan_date, '' cost_price from sf_item_no a  where  a.item_no not in (select stockid from inv_onhand_quantity_all) ".$where2." 
 
 limit ".$off.",".$num."";
   
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
    if ($page<10) $start=1; $end=10;
	if ($page>=10){
	   $start = $page-9;
	   $end = $page+10;
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
	<style>
        .choosecheck{
            border-radius: 4px;
            border: 1px solid #555;
			box-shadow: 0 4px rgba(0, 0, 0, 0.3);
			margin:30px auto;
        }
    </style>
</head>
<body>
<script type="text/javascript">
        window.onload=function(){        
            var api = frameElement.api, W = api.opener;
			var ch_checked=document.getElementById("chsecheck");
		 
			ch_checked.onclick=function(){
				var arr1=document.getElementsByName("boxlist");
				for(i=0;i<arr1.length;i++){ 
					if(arr1[i].checked==true){
						var f2=document.getElementsByClassName("coupons");
						var bot=document.getElementById("fwValue");
						var botint=parseInt(bot.value);
						rel=f2[i].value;
						c = rel.split(":");
				$("#form_ItemNo").text(c[0]);
				$("#form_units").text(c[1]);
				$("#form_ItemDesc").text(c[2]);
                $("#form_item_spec").text(c[3]);
				$("#form_Onhand_Quantity").text(c[4]); 
				$("#form_youxiaoqi").text(c[5]); 
				$("#form_lot_num").text(c[6]); 
				$("#form_shengchan_date").text(c[7]); 
				$("#form_cost_price").text(c[8]); 
				 W.document.getElementById('text_slect_buliao'+$('#fwValue').val()).value = $("label#form_ItemNo").text(); 
					    W.document.getElementById('text_slect_units'+$('#fwValue').val()).value = $("label#form_units").text();
						W.document.getElementById('text_slect_ItemDesc'+$('#fwValue').val()).value = $("label#form_ItemDesc").text();
						W.document.getElementById('text_slect_item_spec'+$('#fwValue').val()).value = $("label#form_item_spec").text();
						W.document.getElementById('text_slect_Onhand_Quantity'+$('#fwValue').val()).value = $("label#form_Onhand_Quantity").text();
						W.document.getElementById('text_slect_youxiaoqi'+$('#fwValue').val()).value = $("label#form_youxiaoqi").text();
						W.document.getElementById('text_slect_lot_num'+$('#fwValue').val()).value = $("label#form_lot_num").text();
						W.document.getElementById('text_slect_shengchan_date'+$('#fwValue').val()).value = $("label#form_shengchan_date").text();
						W.document.getElementById('text_slect_stock_price'+$('#fwValue').val()).value = $("label#form_cost_price").text();
                botint++;
				bot.value=botint+'';
					}
				}api.close();
			}
		}

			 
			
    </script>

<div id="CanvasDiv">
 
	<div id="BodyDiv">
		<div id="BodyWrapDiv">
			<p class="page_title_text">
				<img src="./css/xenos/images/magnifier.png" title="查询料号" alt="查询料号">查询料号
			</p>
			<form action="./SearchOnHandItemCheck.php?fwValue=<?=$cat?>&cat=<?=$cat?>" method ="POST">
				<div>					 
				<div class="text-nav">
					<div class="text-nav-1">
						<div>
							料号：
						</div>
					
							<input type="text" name="ItemNo" value="<?=$ItemNo?>">
						</div>
						<div class="text-nav-1"><div>
							料号名称：
						</div>
					
							<input type="text" name="item_name" value="<?=$item_name?>">
						</div>
						<div class="text-nav-1"><div>
							规格型号：
						</div>
					
							<input type="text" name="ItemDesc" value="<?=$ItemDesc?>">
						</div>
						</div>
					
					
					
					
					<div class="centre">
					     
						<input type="submit" value="查找">
					</div>
					</form>
					<br/>
					<table cellpadding="2" class="selection" id="ck_company">
					<tr><th  width ="20"> 选择 </th> 
						<th class="ascending" width ="80">
							料号
						</th>
						<th class="ascending" width ="200">
							料号名称
						</th>
						<th class="ascending" width ="200">
							规格型号
						</th>
						<th  width ="50">
							单位
						</th>
						<th  width ="50">
							数量
						</th> 
						<th  width ="50">
							批号
						</th> 
						<th  width ="50">
							生产日期
						</th> 
						<th  width ="50">
							单价
						</th>
					 
				
					</tr>
					<?php foreach($list as $arr=>$row2){
						
						if($row2['shengchan_date'] > 0){
							$shengchan_date = date('Y-m-d',$row2['shengchan_date']);
						}else{
							$shengchan_date = '';
						}
						
						?>
					<tr class="EvenTableRows">
						<td> <input type="checkbox" style="height: 24px;width: 24px;" name="boxlist"> </td>
						<td>
							<?=$row2['item_no']?>
						</td>
						<td> <?=$row2['item_name']?>
						</td>
                       <td> <?=$row2['item_desc']?>
						</td>
						<td> <?=$row2['units']?> </td>
						 
						 <td> <?=$row2['quantity']?> </td>
						 <td> <?=$row2['lot_num']?> </td>
						 <td> <?=$shengchan_date?> </td>
						 <td> <?=round($row2['cost_price'],9)?> </td>

						 
						<td>
						<input name="a" type="hidden" class="coupons" value="<?=$row2['item_no']?>:<?=$row2['units']?>:<?=$row2['item_name']?>:<?=$row2['item_desc']?>:<?=$row2['quantity']?>:<?=$row2['youxiaoqi']?>:<?=$row2['lot_num']?>:<?=$shengchan_date?>:<?=round($row2['cost_price'],9)?>">				 
						</td>
					</tr>
					<?php }?>
					 
					 
					</table>
					<input type="button" class="choosecheck" id="chsecheck" style="height: 24px;width: 100px;" value="确定">
					<br/>
					<div class="centre">
					<?=show_page('?page=',$page,$pages,$count,'&fwValue='.$cat.'&cat='.$cat.'&ItemNo='.$ItemNo.'&item_name='.$item_name.'&ItemDesc'.$ItemDesc.'');?>
					
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
                         <p><label class="text-info">item_spec</label>　<label id="form_item_spec"></label></p>
                    </div>
					<div style="display:none">
                         <p><label class="text-info">Onhand_Quantity</label>　<label id="form_Onhand_Quantity"></label></p>
                    </div>
					<div style="display:none">
                         <p><label class="text-info">youxiaoqi</label>　<label id="form_youxiaoqi"></label></p>
                    </div>
					<div style="display:none">
                         <p><label class="text-info">lot_num</label>　<label id="form_lot_num"></label></p>
                    </div>
					<div style="display:none">
                         <p><label class="text-info">shengchan_date</label>　<label id="form_shengchan_date"></label></p>
                    </div>
					<div style="display:none">
                         <p><label class="text-info">cost_price</label>　<label id="form_cost_price"></label></p>
                    </div>
                    
                    <div style="display:none">
                        <p><input type="hidden" name="cat" id="cat" value="<?=$cat?>"/></p>
						<p><input type="hidden" name="fwValue" value="<?=$fwValue?>" id="fwValue"/></p>
                    </div>
  
 
</body>
</html>

