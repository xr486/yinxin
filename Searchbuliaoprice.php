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
$ItemDesc = isset($_REQUEST['ItemDesc']) ? $_REQUEST['ItemDesc'] : '';	 
$where = "   ";
if($ItemNo) {
   $where .= ' and item_no like "%'.$ItemNo.'%"';
}
if($ItemName) {
   $where .= ' and item_name like "%'.$ItemName.'%"';
} 
if($ItemDesc) {
   $where .= ' and item_desc like "%'.$ItemDesc.'%"';
}
 
 
$num = 10;
$off = $num*($page-1);
 
$count = db_sql('select * from sf_item_no where 1=1 '.$where.'',3);

$pages = ceil($count/$num);
$sql = "select a.* ,( select price from po_vendor_item_price p where p.item_no=a.item_no and p.vendor_code= '".$cat ."'
    ) xieyi_price
from sf_item_no  a where 1=1 ".$where." ORDER BY item_id  desc limit ".$off.",".$num."";
   
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
				$("#form_ItemName").text(c[1]);
				$("#form_ItemSpec").text(c[2]);
				$("#form_units").text(c[3]); 
                $("#form_xieyi_price").text(c[4]);
				 W.document.getElementById('text_slect_buliao'+$('#fwValue').val()).value = $("label#form_ItemNo").text();
						W.document.getElementById('text_slect_item_name'+$('#fwValue').val()).value = $("label#form_ItemName").text();
                        W.document.getElementById('text_slect_item_spec'+$('#fwValue').val()).value = $("label#form_ItemSpec").text(); 
					    W.document.getElementById('text_slect_units'+$('#fwValue').val()).value = $("label#form_units").text();
                        W.document.getElementById('text_slect_xieyi_price'+$('#fwValue').val()).value = $("label#form_xieyi_price").text();
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
			<form action="./Searchbuliaoprice.php?fwValue=<?=$cat?>&cat=<?=$cat?>" method ="POST">
				<div>
					 
					<table cellpadding="3" class="selection">
					<div class="text-nav">
					<div class="text-nav-1"><div>料号：
						</div>
							<input type="text" name="ItemNo" value="<?=$ItemNo?>">
						</div>
						<div class="text-nav-1"><div>名称：
						</div>
							<input type="text" name="ItemName" value="<?=$ItemName?>">
						</div>
						<div class="text-nav-1"><div>规格：
						</div>
							<input type="text" name="ItemDesc" value="<?=$ItemDesc?>">
						</div>
					</div>
					
					
					</table>
					<div class="centre">
					     
						<input type="submit" value="查找">
					</div>
					</form>
					<br/>
					<table cellpadding="2" class="selection" id="ck_company">
					<tr> <th  width ="20"> 选择 </th> 
						<th class="ascending" width ="290">
							料号
						</th>
					    <th class="ascending" width ="290">
							材料名称
						</th>
						<th class="ascending" width ="290">
							规格型号
						</th>
						<th class="ascending" width ="50">
							单位
						</th>
						
						 <th class="ascending" width ="90">
							上次协议单价
						</th>
						 
					
					</tr>
					<?php foreach($list as $arr=>$row2){?>
					<tr class="EvenTableRows">
					 <td>
							<input type="checkbox" style="height: 24px;width: 24px;" name="boxlist">
						</td>
						<td>
							<?=$row2['item_no']?>
						</td>
						<td>
							<?=$row2['item_name']?>
						</td>
						<td> <?=$row2['item_desc']?>
						</td>
						<td> <?=$row2['units']?> </td>
					
                         <td> <?=$row2['xieyi_price']?> </td>
						</td>
						<td>
						<input name="a" type="hidden"  class="coupons" value="<?=$row2['item_no']?>:<?=$row2['item_name']?>:<?=$row2['item_desc']?>:<?=$row2['units']?>:<?=$row2['xieyi_price']?>">	
					 	</td>
					</tr>
					<?php }?>
					 
					 
					</table>
					<input type="button" class="choosecheck" id="chsecheck" style="height: 24px;width: 100px;" value="确定">
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
                         <p><label class="text-info">ItemName</label>　<label id="form_ItemName"></label></p>
                    </div>
					<div style="display:none">
                         <p><label class="text-info">ItemSpec</label>　<label id="form_ItemSpec"></label></p>
                    </div>                
                    
					<div style="display:none">
                         <p><label class="text-info">Units:</label>　<label id="form_units"></label></p>
                    </div>
                    <div style="display:none">
                         <p><label class="text-info">xieyi_price</label>　<label id="form_xieyi_price"></label></p>
                    </div>
                    <div style="display:none">
                        <p><input type="hidden" name="cat" id="cat" value="<?=$cat?>"/></p>
						<p><input type="hidden" name="fwValue" value="<?=$fwValue?>" id="fwValue"/></p>
                    </div>
  
 
</body>
</html>

