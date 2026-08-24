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
$subcode     = isset($_REQUEST['subcode']) ? $_REQUEST['subcode'] : 'buliao';	
$page    = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;	
$po_num  = isset($_REQUEST['po_num']) ? $_REQUEST['po_num'] : '';	
$ItemNo  = isset($_REQUEST['ItemNo']) ? $_REQUEST['ItemNo'] : '';	
$ItemDesc = isset($_REQUEST['ItemDesc']) ? $_REQUEST['ItemDesc'] : '';	 
$where = ' and A.status="APPROVED"  ';
if($po_num) {
   $where .= ' and b.po_num like "%'.$po_num.'%"';
}
if($ItemNo) {
   $where .= ' and c.item_no like "%'.$ItemNo.'%"';
}
 
if($ItemDesc) {
   $where .= ' and c.item_desc like "%'.$ItemDesc.'%"';
}

 if($cat) {
   $where .= ' and a.vendor_code ="'. $cat .' " ';
} 
 
$num = 10;
$off = $num*($page-1);


$count = db_sql("SELECT b.po_line_id, b.po_num,b.line,b.stockid,b.price,b.uom,b.quantity_received,b.quantity,c.item_desc,c.item_spec FROM po_headers_all a , po_lines_all b,sf_item_no c
WHERE a.po_num=b.po_num and b.stockid = c.item_no and a.status='APPROVED' and b.quantity_received >0  ".$where."",3);

$pages = ceil($count/$num);

$sql = "SELECT b.po_line_id,b.po_num,b.line,b.need_date,b.stockid,b.price,b.uom,b.quantity_received,b.quantity,c.item_desc ,c.item_spec,(select  sum(quantity) from  inv_onhand_quantity_all d where d.stockid=b.stockid and subinventory_code='".$subcode."' ) onhand_quantity
FROM po_headers_all a , po_lines_all b,sf_item_no c
WHERE a.po_num=b.po_num and b.stockid = c.item_no and a.status='APPROVED' and b.quantity_received >0   ".$where." ORDER BY b.po_num  desc limit ".$off.",".$num."";
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

                        W.document.getElementById('text_slect_po_num'+$('#fwValue').val()).value = $("label#form_po_num").text();
                        W.document.getElementById('text_slect_line'+$('#fwValue').val()).value = $("label#form_line").text(); 
                        W.document.getElementById('text_slect_buliao'+$('#fwValue').val()).value = $("label#form_ItemNo").text(); 
						W.document.getElementById('text_slect_ItemDesc'+$('#fwValue').val()).value = $("label#form_ItemDesc").text();
						W.document.getElementById('text_slect_item_spec'+$('#fwValue').val()).value = $("label#form_item_spec").text();
						W.document.getElementById('text_slect_units'+$('#fwValue').val()).value = $("label#form_units").text();
						W.document.getElementById('text_slect_price'+$('#fwValue').val()).value = $("label#form_price").text();
						W.document.getElementById('text_slect_quantity'+$('#fwValue').val()).value = $("label#form_quantity").text();

						W.document.getElementById('text_slect_quantity_received'+$('#fwValue').val()).value = $("label#form_quantity_received").text();
						W.document.getElementById('text_slect_quantity_received2'+$('#fwValue').val()).value = $("label#form_quantity_received2").text();
						W.document.getElementById('text_slect_line_id'+$('#fwValue').val()).value = $("label#form_line_id").text();
						W.document.getElementById('text_slect_onhand_quantity'+$('#fwValue').val()).value = $("label#form_onhand_quantity").text();

						
                       
						
						$("#xianshi").css("display","block"); 
                        break;
                    default :
                        alert('Data Post Error');
                }
            };
            
			
			$(".coupons").livequery("click", function() {
				var rel = this.getAttribute('rel');
				c = rel.split(":");
				$("#form_po_num").text(c[0]);
				$("#form_line").text(c[1]);
				$("#form_ItemNo").text(c[2]);
				$("#form_line_id").text(c[3]); 
				$("#form_units").text(c[4]);
				$("#form_price").text(c[5]); 
				$("#form_quantity").text(c[6]);
				$("#form_quantity_received").text(c[7]); 
				$("#form_ItemDesc").text(c[8]);
				$("#form_onhand_quantity").text(c[9]);
				$("#form_item_spec").text(c[10]);
				
			});
			 
			
			
        });
    </script>
<div id="CanvasDiv">
 
	<div id="BodyDiv">
		<div id="BodyWrapDiv">
			<p class="page_title_text">
				<img src="./css/xenos/images/magnifier.png" title="查询料号" alt="查询料号">查询料号
			</p>
			<form action="./Searchbuliao6.php?fwValue=<?=$cat?>&cat=<?=$cat?>" method ="POST">
				<div>
					 
					<table cellpadding="3" class="selection">
					<tr>
					    <td>
							订单号：
						</td>
						<td>
							<input type="text" name="po_num" value="<?=$po_num?>">
						</td>
						<td>
							产品
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
						<th class="ascending" width ="150">
							订单号
						</th>
						<th  width ="20">
							行
						</th>
						<th class="ascending" width ="150">
							产品
						</th>
						<th class="ascending" width ="150">
							产品名称
						</th>
						<th  width ="150">
							规格型号
						</th>
						<th  width ="50">
							单位
						</th>
						 
						<th  width ="50">
							采购数量
						</th>
						<th  width ="80">
							已收数量
						</th>
						
						<th  width ="100">
							需求时间
						</th>
						<th  width ="80">
							仓库
						</th>
						
						
						<th  width ="50">
							操作
						</th>
					</tr>
					<?php foreach($list as $arr=>$row2){?>
					<tr class="EvenTableRows">
					<td>
							<?=$row2['po_num']?>
						</td>
						<td>
							<?=$row2['line']?>
						</td>
						<td>
							<?=$row2['stockid']?>
						</td>
						<td> <?=$row2['item_desc']?>
						</td>
						<td> <?=$row2['item_spec']?>
						</td>
						<td> <?=$row2['uom']?>
						</td> 
						<td> <?=$row2['quantity']?> </td>
						<td> <?=$row2['quantity_received']?> </td>
						<td> <?=date('Y-m-d',$row2['need_date']);?> </td>
						<td> <?=$row2['onhand_quantity']?> </td>
						
						<td>
						<input name="a" type="radio" value="选择" class="coupons" rel="<?=$row2['po_num']?>:<?=$row2['line']?>:<?=$row2['stockid']?>:<?=$row2['po_line_id']?>:<?=$row2['uom']?>:<?=$row2['price']?>:<?=$row2['quantity']?>:<?=$row2['quantity_received']?>:<?=$row2['item_desc']?>:<?=$row2['onhand_quantity']?>:<?=$row2['item_spec']?>">				 
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
                         <p><label class="text-info">line_id</label>　<label id="form_line_id"></label></p>
                    </div>
					<div style="display:none">
                         <p><label class="text-info">onhand_quantity</label>　<label id="form_onhand_quantity"></label></p>
                    </div>
                    
 
                   
                    <div style="display:none">
                        <p><input type="hidden" name="cat" id="cat" value="<?=$cat?>"/></p>
						<p><input type="hidden" name="fwValue" value="<?=$fwValue?>" id="fwValue"/></p>
                    </div>
  
 
</body>
</html>

