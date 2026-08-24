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
$item_name = isset($_REQUEST['item_name']) ? $_REQUEST['item_name'] : ''; 	
$where = "";
if($item_no) {
   $where .= ' and b.item_no like "%'.$item_no.'%"';
}
 
if($item_name) {
   $where .= ' and b.item_name like "%'.$item_name.'%"';
}

if($cat) {
	
     
	$where .= " and 1=1 "; 

}

$num = 10;
$off = $num*($page-1);
 
    $count = db_sql("SELECT c.po_num,c.line,a.lot_num,b.item_no,b.item_name,b.item_desc,c.line_amount,d.tax_name,c.quantity,CAST(c.price AS DECIMAL(20, 6))/(1+CAST(d.tax_rate AS DECIMAL(20, 4))) notax_price,(SELECT (sum(f.cost_price*f.quantity)*(1+d.tax_rate)) onhand_amount  from inv_onhand_quantity_all f where f.subinventory_code = e.subinventory_from and f.stockid = c.stockid and a.lot_num = f.lot_num) onhand_amount,(SELECT sum(f.quantity) onhand_quantity  from inv_onhand_quantity_all f where f.subinventory_code = e.subinventory_from and f.stockid = c.stockid and a.lot_num = f.lot_num) onhand_quantity,(SELECT distinct cost_price  from inv_onhand_quantity_all f where f.subinventory_code = e.subinventory_from and f.stockid = c.stockid and a.lot_num = f.lot_num) cost_price
	FROM  sf_item_no b,po_rcv_transactions a,po_lines_all c,po_headers_all d,inv_transactions_all e
WHERE	d.po_num=c.po_num and a.po_line=c.line and c.stockid = b.item_no and a.stockid = c.stockid and a.transaction_type = 'POIN' and a.po_line = e.po_line and a.po_num = e.po_num and a.receipt_num = e.receipt_num and a.receipt_line = e.receipt_line
	".$where.'',3);

$pages = ceil($count/$num);
$sql = "SELECT c.po_num,c.line,a.lot_num,b.item_no,b.item_name,b.item_desc,c.line_amount,d.tax_name,c.quantity,CAST(c.price AS DECIMAL(20, 6))/(1+CAST(d.tax_rate AS DECIMAL(20, 4))) notax_price,(SELECT (sum(f.cost_price*f.quantity)*(1+d.tax_rate)) onhand_amount  from inv_onhand_quantity_all f where f.subinventory_code = e.subinventory_from and f.stockid = c.stockid and a.lot_num = f.lot_num) onhand_amount,(SELECT sum(f.quantity) onhand_quantity  from inv_onhand_quantity_all f where f.subinventory_code = e.subinventory_from and f.stockid = c.stockid and a.lot_num = f.lot_num) onhand_quantity,(SELECT distinct cost_price  from inv_onhand_quantity_all f where f.subinventory_code = e.subinventory_from and f.stockid = c.stockid and a.lot_num = f.lot_num) cost_price,(SELECT distinct subinventory_code  from inv_onhand_quantity_all f where f.subinventory_code = e.subinventory_from and f.stockid = c.stockid and a.lot_num = f.lot_num) subinventory_code
	FROM  sf_item_no b,po_rcv_transactions a,po_lines_all c,po_headers_all d,inv_transactions_all e
WHERE	d.po_num=c.po_num and a.po_line=c.line and c.stockid = b.item_no and a.stockid = c.stockid and a.transaction_type = 'POIN' and a.po_line = e.po_line and a.po_num = e.po_num and a.receipt_num = e.receipt_num and a.receipt_line = e.receipt_line    ".$where.' ORDER BY b.item_no  desc limit '.$off.','.$num.'';
   
//  echo $sql;
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

<title>查询客户</title>
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
				$("#form_po_num").text(c[0]);
				$("#form_line").text(c[1]);
				$("#form_item_no").text(c[2]);
				$("#form_item_name").text(c[3]);
				$("#form_item_desc").text(c[4]);
				$("#form_lot_num").text(c[5]);
				$("#form_line_amount").text(c[6]);
				$("#form_tax_name").text(c[7]);
				$("#form_quantity").text(c[8]);
				$("#form_notax_price").text(c[9]);
				$("#form_onhand_amount").text(c[10]);
				$("#form_onhand_quantity").text(c[11]);
				$("#form_cost_price").text(c[12]);
				$("#form_subinventory_code").text(c[13]);
				  W.document.getElementById('text_slect_po_num'+$('#fwValue').val()).value = $("label#form_po_num").text(); 
				  W.document.getElementById('text_slect_po_line'+$('#fwValue').val()).value = $("label#form_line").text(); 
                  W.document.getElementById('text_slect_item_no'+$('#fwValue').val()).value = $("label#form_item_no").text();
				 W.document.getElementById('text_slect_item_name'+$('#fwValue').val()).value = $("label#form_item_name").text();
				 W.document.getElementById('text_slect_item_desc'+$('#fwValue').val()).value = $("label#form_item_desc").text();
				 W.document.getElementById('text_slect_lot_num'+$('#fwValue').val()).value = $("label#form_lot_num").text();
				 W.document.getElementById('text_slect_po_line_amount'+$('#fwValue').val()).value = $("label#form_line_amount").text();
				 W.document.getElementById('text_slect_tax_name'+$('#fwValue').val()).value = $("label#form_tax_name").text();
                 W.document.getElementById('text_slect_po_quantity'+$('#fwValue').val()).value = $("label#form_quantity").text();
                 W.document.getElementById('text_slect_po_price'+$('#fwValue').val()).value = $("label#form_notax_price").text();
                 W.document.getElementById('text_slect_onhand_amount'+$('#fwValue').val()).value = $("label#form_onhand_amount").text();
                 W.document.getElementById('text_slect_tax_name1'+$('#fwValue').val()).value = $("label#form_tax_name").text();
                 W.document.getElementById('text_slect_tax_name1'+$('#fwValue').val()).value = $("label#form_tax_name").text();
                 W.document.getElementById('text_slect_onhand_quantity'+$('#fwValue').val()).value = $("label#form_onhand_quantity").text();
                 W.document.getElementById('text_slect_cost_price'+$('#fwValue').val()).value = $("label#form_cost_price").text();
                 W.document.getElementById('text_slect_subinventory_code'+$('#fwValue').val()).value = $("label#form_subinventory_code").text();
                 W.document.getElementById('text_slect_change_quantity'+$('#fwValue').val()).value = $("label#form_onhand_quantity").text();
                 
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
				<img src="/css/xenos/images/magnifier.png" title="查询料号" alt="查询料号">查询料号
			</p>
			<form action="./BtnSearchPORcv.php?fwValue=<?=$cat?>&cat=<?=$cat?>" method ="POST">
				<div>
					 

					<div class="text-nav">

						<div class="text-nav-1">

							<div>
							料号：
							</div>
						<input type="text" name="item_no" value="<?=$item_no?>">
						</div>
						<div class="text-nav-1">
							<div>
							料号名称：
							</div>
								<input type="text" name="item_name" value="<?=$item_name?>">
							</div>
					</div>
					
					

					<div class="centre">
					     
						<input type="submit" value="查找">
					</div>
					</form>
					<br/>
					<table cellpadding="2" class="selection" id="ck_company">
					<tr>
						<th  width ="70">
							采购单号
						</th>
                        <th  width ="20">
							采购单行
						</th>
                        <th  width ="70">
							料号
						</th>
						<th  width ="100">
							料号名称
						</th>
						<th  width ="100">
							规格型号
						</th>
						<th  width ="50">
							批号
						</th>
						<th  width ="50">
							采购含税金额
						</th>
                        <th  width ="50">
							税率
						</th>
                        <th  width ="50">
							采购数量
						</th>
                        <th  width ="50">
							采购未税单价
						</th>
                        <th  width ="50">
							库存含税金额
						</th>
                        <th  width ="50">
							库存数量
						</th>
                        <th  width ="50">
							库存未税单价
						</th>
                        <th  width ="50">
							仓库
						</th>
					 
					</tr>
					<?php foreach($list as $arr=>$row2){?>
					<tr class="EvenTableRows">
						<td><?=$row2['po_num']?></td>
						<td><?=$row2['line']?></td>
						<td><?=$row2['item_no']?></td>
						<td> <?=$row2['item_name']?></td>
						<td> <?=$row2['item_desc']?></td>
						<td> <?=$row2['lot_num']?></td>
						<td> <?=$row2['line_amount']?></td>
						<td> <?=$row2['tax_name']?></td>
						<td> <?=$row2['quantity']?></td>
						<td> <?=round($row2['notax_price'],6)?></td>
						<td> <?=round($row2['onhand_amount'],2)?></td>
						<td> <?=$row2['onhand_quantity']?></td>
						<td> <?=round($row2['cost_price'],9)?></td>
						<td> <?=$row2['subinventory_code']?></td>
						<td>
						<input name="a" type="hidden" value="选择" class="coupons" rel="<?=$row2['po_num']?>:<?=$row2['line']?>:<?=$row2['item_no']?>:<?=$row2['item_name']?>:<?=$row2['item_desc']?>:<?=$row2['lot_num']?>:<?=$row2['line_amount']?>:<?=$row2['tax_name']?>:<?=$row2['quantity']?>:<?=round($row2['notax_price'],6)?>:<?=round($row2['onhand_amount'],2)?>:<?=$row2['onhand_quantity']?>:<?=round($row2['cost_price'],9)?>:<?=$row2['subinventory_code']?>">				 
						</td>
					</tr>
					<?php }?>
					 
					
					</table>
					<br/>
					<div class="centre">
					<?=show_page('?page=',$page,$pages,$count,'&fwValue='.$cat.'&cat='.$cat.'&item_no='.$item_no.'&item_name'.$item_name.'');?>
					
					</div>
				</div>
			
		</div>
	</div>
	<div id="FooterDiv">
		<div id="FooterWrapDiv">
			 
		</div>
	</div>
                   <div style="display:none">
                         <p><label class="text-info">po_num</label>　<label id="form_po_num"></label></p>
                    </div>
                    <div style="display:none">
                         <p><label class="text-info">line</label>　<label id="form_line"></label></p>
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
                         <p><label class="text-info">lot_num</label>　<label id="form_lot_num"></label></p>
                    </div>		
             <div style="display:none">
                         <p><label class="text-info">line_amount</label>　<label id="form_line_amount"></label></p>
                    </div>	        
              <div style="display:none">
                         <p><label class="text-info">tax_name</label>　<label id="form_tax_name"></label></p>
                    </div>	
                    <div style="display:none">
                         <p><label class="text-info">quantity</label>　<label id="form_quantity"></label></p>
                    </div>	
                    <div style="display:none">
                         <p><label class="text-info">notax_price</label>　<label id="form_notax_price"></label></p>
                    </div>	
                    <div style="display:none">
                         <p><label class="text-info">onhand_amount</label>　<label id="form_onhand_amount"></label></p>
                    </div>	
                    <div style="display:none">
                         <p><label class="text-info">onhand_quantity</label>　<label id="form_onhand_quantity"></label></p>
                    </div>
                    <div style="display:none">
                         <p><label class="text-info">cost_price</label>　<label id="form_cost_price"></label></p>
                    </div>
                    <div style="display:none">
                         <p><label class="text-info">subinventory_code</label>　<label id="form_subinventory_code"></label></p>
                    </div>
					<div style="display:none">
                        <p><input type="hidden" name="cat" id="cat" value="<?=$cat?>"/></p>
						<p><input type="hidden" name="fwValue" value="<?=$fwValue?>" id="fwValue"/></p>
                    </div>
   
 
</body>
</html>

