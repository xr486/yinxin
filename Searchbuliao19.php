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
$po_num  = isset($_REQUEST['po_num']) ? $_REQUEST['po_num'] : '';	
$ItemNo  = isset($_REQUEST['ItemNo']) ? $_REQUEST['ItemNo'] : '';	
$ItemDesc = isset($_REQUEST['ItemDesc']) ? $_REQUEST['ItemDesc'] : '';	 
$where = ' and a.status="已签核"  ';
if($po_num) {
   $where .= ' and b.po_num like "%'.$po_num.'%"';
}
if($ItemNo) {
   $where .= ' and c.item_no like "%'.$ItemNo.'%"';
}
 
if($ItemDesc) {
   $where .= ' and c.item_name like "%'.$ItemDesc.'%"';
}

 if($cat) {
   $where .= ' and a.vendor_code ="'. $cat .' " ';
} 
 
$num = 10;
$off = $num*($page-1);


$count = db_sql("SELECT b.po_line_id, b.po_num,b.line,b.stockid,b.price,b.uom,b.quantity_received,b.quantity,c.item_desc,c.item_name FROM po_headers_all a , po_lines_all b,sf_item_no c
WHERE a.po_num=b.po_num and b.stockid = c.item_no and a.status='已签核'   and b.quantity > b.quantity_received   ".$where."",3);

$pages = ceil($count/$num);

$sql = "SELECT b.po_line_id,b.po_num,b.line,b.need_date,b.stockid,b.price,b.uom,b.quantity_received,b.quantity,c.item_name ,c.item_desc,a.need_date  estimate_date,c.sub_code,(select type_name from sf_item_use d where c.item_use=d.item_type )  item_use,c.project_name
FROM po_headers_all a , po_lines_all b,sf_item_no c
WHERE a.po_num=b.po_num and b.stockid = c.item_no and a.status='已签核'  and b.quantity > b.quantity_received  ".$where." ORDER BY b.po_num,b.line   limit ".$off.",".$num."";
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
				$("#form_po_num").text(c[0]);
				$("#form_line").text(c[1]);
				$("#form_ItemNo").text(c[2]);
				$("#form_line_id").text(c[3]); 
				$("#form_units").text(c[4]);
				$("#form_price").text(c[5]); 
				$("#form_quantity").text(c[6]);
				$("#form_quantity_received").text(c[7]);
				$("#form_wait_quantity").text(c[8]);
				$("#form_ItemDesc").text(c[9]);
				$("#form_item_spec").text(c[10]);
				$("#form_item_use").text(c[11]);
				$("#form_project_name").text(c[12]);
				
				 W.document.getElementById('text_slect_po_num'+$('#fwValue').val()).value = $("label#form_po_num").text();
                        W.document.getElementById('text_slect_line'+$('#fwValue').val()).value = $("label#form_line").text(); 
                        W.document.getElementById('text_slect_buliao'+$('#fwValue').val()).value = $("label#form_ItemNo").text(); 
						W.document.getElementById('text_slect_ItemDesc'+$('#fwValue').val()).value = $("label#form_ItemDesc").text();
						W.document.getElementById('text_slect_item_spec'+$('#fwValue').val()).value = $("label#form_item_spec").text();
						W.document.getElementById('text_slect_units'+$('#fwValue').val()).value = $("label#form_units").text();
						W.document.getElementById('text_slect_price'+$('#fwValue').val()).value = $("label#form_price").text();
						W.document.getElementById('text_slect_quantity'+$('#fwValue').val()).value = $("label#form_quantity").text();

						W.document.getElementById('text_slect_quantity_received'+$('#fwValue').val()).value = $("label#form_quantity_received").text();
						W.document.getElementById('text_slect_wait_quantity'+$('#fwValue').val()).value = $("label#form_wait_quantity").text();
						W.document.getElementById('text_slect_line_id'+$('#fwValue').val()).value = $("label#form_line_id").text();
						W.document.getElementById('text_slect_item_use'+$('#fwValue').val()).value = $("label#form_item_use").text();
						W.document.getElementById('text_slect_project_name'+$('#fwValue').val()).value = $("label#form_project_name").text();
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
			<form action="./Searchbuliao19.php?fwValue=<?=$cat?>&cat=<?=$cat?>" method ="POST">
				<div>
					 
					<table cellpadding="3" class="selection">
					<div class="text-nav">	 
					<div class="text-nav-1 ">
						<div>
						采购单号：
						</div>
							<input type="text" name="po_num" value="<?=$po_num?>">
						</div>
					<div class="text-nav-1 ">
					<div>料号</div>
					<input type="text" name="ItemNo" value="<?=$ItemNo?>">
					</div>
					<div class="text-nav-1 "><div>产品名称
					</div><input type="text" name="ItemDesc" value="<?=$ItemDesc?>">
					</div>
					</div>
					</table>
					<div class="centre">
					     
						<input type="submit" value="查找">
					</div>
					</form>
					<br/>
					<table cellpadding="2" class="selection" id="ck_company">
					<tr><th  width ="50"> 选择 </th> 
						<th class="ascending" width ="150">
							采购单号
						</th>
						<th  width ="20">
							行
						</th>
						<th class="ascending" width ="150">
							料号
						</th>
						<th class="ascending" width ="150">
							料号名称
						</th>
						<th  width ="150">
							规格型号
						</th>
						<th  width ="50">
							单位
						</th>
						<th  width ="50">
							项目名称
						</th>
						<th  width ="50">
							料号用途
						</th>
						<th  width ="50">
							采购数量
						</th>
						<th  width ="80">
							已收数量
						</th>
						<th  width ="80">
							未收数量
						</th>
						
			
					</tr>
					<?php foreach($list as $arr=>$row2){?>
					<tr class="EvenTableRows">
					 <td> <input type="checkbox" style="height: 24px;width: 24px;" name="boxlist"> </td>
					<td>
							<?=$row2['po_num']?>
						</td>
						<td>
							<?=$row2['line']?>
						</td>
						<td>
							<?=$row2['stockid']?>
						</td>
						<td> <?=$row2['item_name']?>
						</td>
						<td> <?=$row2['item_desc']?>
						</td>
						<td> <?=$row2['uom']?></td> 
						<td> <?=$row2['project_name']?></td> 
						<td> <?=$row2['item_use']?></td> 
						
						<td> <?=$row2['quantity']?> </td>
						<td> <?=$row2['quantity_received']?> </td>
						<td> <?=$wait_quantity  = $row2['quantity']-$row2['quantity_received']?> </td>
					
						
						<td>
						<input name="a" type="hidden"   class="coupons" value="<?=$row2['po_num']?>:<?=$row2['line']?>:<?=$row2['stockid']?>:<?=$row2['po_line_id']?>:<?=$row2['uom']?>:<?=$row2['price']?>:<?=$row2['quantity']?>:<?=$row2['quantity_received']?>:<?=$wait_quantity?>:<?=$row2['item_name']?>:<?=$row2['item_desc']?>:<?=$row2['item_use']?>:<?=$row2['project_name']?>">				 
						</td>
					</tr>
					<?php }?>
					 
					 
					</table>
					<input type="button" class="choosecheck" id="chsecheck" style="height: 24px;width: 100px;" value="确定">
					<br/>
					<div class="centre">
					<?=show_page('?page=',$page,$pages,$count,'&fwValue='.$cat.'&cat='.$cat.'&ItemNo='.$ItemNo.'&po_num='.$po_num.'&ItemDesc='.$ItemDesc.'');?>
					
					</div>
				</div>
			
		</div>
	</div>
	<div id="FooterDiv">
		<div id="FooterWrapDiv">
			 
		</div>
	</div>
                   <div style="display:none">
                         <p><label class="text-info">po_num:</label>　<label id="form_po_num"></label></p>
                    </div>
                    <div style="display:none">
                         <p><label class="text-info">line:</label>　<label id="form_line"></label></p>
                    </div>
                    <div style="display:none">
                         <p><label class="text-info">ItemNo:</label>　<label id="form_ItemNo"></label></p>
                    </div>
                    <div style="display:none">
                         <p><label class="text-info">ItemDesc</label>　<label id="form_ItemDesc"></label></p>
                    </div>
					<div style="display:none">
                         <p><label class="text-info">item_spec</label>　<label id="form_item_spec"></label></p>
                    </div>

					 <div style="display:none">
                         <p><label class="text-info">Units:</label>　<label id="form_units"></label></p>
                    </div> 
              
                    <div style="display:none">
                         <p><label class="text-info">price</label>　<label id="form_price"></label></p>
                    </div>
   					<div style="display:none">
                         <p><label class="text-info">quantity</label>　<label id="form_quantity"></label></p>
                    </div>
                    <div style="display:none">
                         <p><label class="text-info">quantity_received</label>　<label id="form_quantity_received"></label></p>
                    </div>
                    <div style="display:none">
                         <p><label class="text-info">wait_quantity</label>　<label id="form_wait_quantity"></label></p>
                    </div>
                    <div style="display:none">
                         <p><label class="text-info">line_id</label>　<label id="form_line_id"></label></p>
                    </div>
					<div style="display:none">
                         <p><label class="text-info">item_use</label>　<label id="form_item_use"></label></p>
                    </div>
					<div style="display:none">
                         <p><label class="text-info">project_name</label>　<label id="form_project_name"></label></p>
                    </div>
					
                    <div style="display:none">
                        <p><input type="hidden" name="cat" id="cat" value="<?=$cat?>"/></p>
						<p><input type="hidden" name="fwValue" value="<?=$fwValue?>" id="fwValue"/></p>
                    </div>
  
 
</body>
</html>

