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
$ItemDesc = isset($_REQUEST['ItemDesc']) ? $_REQUEST['ItemDesc'] : '';	 
$where = '';
if($ItemNo) {
   $where .= ' and a.item_no like "%'.$ItemNo.'%"';
}
 
if($ItemDesc) {
   $where .= ' and a.item_desc like "%'.$ItemDesc.'%"';
}
 
 
$num = 10;
$off = $num*($page-1);
 
$count = db_sql("SELECT b.id,a.item_no,a.item_desc,a.units,b.orderno,b.ordernoline,b.supplydate,b.book_order_date,
b.demanddate,b.supplyquantity
	FROM mrpsupplies b, sf_item_no a 
	   WHERE a.item_no = b.part
	   and b.already_order_flag='N'
        AND  ordertype='PLANPR'".$where."''",3);

$pages = ceil($count/$num);
$sql = "SELECT b.id,a.item_no,a.item_desc,a.units,b.orderno,b.ordernoline,b.supplydate,b.book_order_date,b.demanddate,b.supplyquantity
	FROM mrpsupplies b, sf_item_no a 
	   WHERE a.item_no = b.part
        AND  ordertype='PLANPR' 
		and b.already_order_flag='N'
		and 1=1 ".$where." ORDER BY item_id  desc limit ".$off.",".$num;
   
 //echo $sql;
 
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
                        W.document.getElementById('text_slect_buliao'+$('#fwValue').val()).value = $("label#form_ItemNo").text(); 
					    W.document.getElementById('text_slect_units'+$('#fwValue').val()).value = $("label#form_units").text();
						W.document.getElementById('text_slect_ItemDesc'+$('#fwValue').val()).value = $("label#form_ItemDesc").text();
						W.document.getElementById('text_slect_need_date'+$('#fwValue').val()).value = $("label#form_need_date").text();
						W.document.getElementById('text_slect_need_qty'+$('#fwValue').val()).value = $("label#form_need_qty").text();
						W.document.getElementById('text_slect_supplid'+$('#fwValue').val()).value = $("label#form_supplid").text();
                         
						
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
				$("#form_ItemDesc").text(c[2]); 
				$("#form_need_date").text(c[3]);
				$("#form_need_qty").text(c[4]); 
				$("#form_supplid").text(c[5]); 
			});
			 
			
			
        });
    </script>
<div id="CanvasDiv">
 
	<div id="BodyDiv">
		<div id="BodyWrapDiv">
			<p class="page_title_text">
				<img src="./css/xenos/images/magnifier.png" title="查询料号" alt="查询料号">查询料号
			</p>
			<form action="./Searchbumrppo.php?fwValue=<?=$cat?>&cat=<?=$cat?>" method ="POST">
				<div>
					 
					<table cellpadding="3" class="selection">
					<tr>
						<td>
							料号：
						</td>
						<td>
							<input type="text" name="ItemNo" value="<?=$ItemNo?>">
						</td>
						<td>
							料号描述：
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
							料号
						</th>
						<th class="ascending" width ="200">
							料号描述
						</th>
						<th class="ascending" width ="50">
							单位
						</th>
						<th class="ascending" width ="50">
							需求日期
						</th>
						<th class="ascending" width ="50">
							建议下单日
						</th>
						<th class="ascending" width ="50">
							数量
						</th>
						 
						 
						<th class="ascending" width ="50">
							操作
						</th>
					</tr>
					<?php foreach($list as $arr=>$row2){?>
					<tr class="EvenTableRows">
						<td>
							<?=$row2['item_no']?>
						</td>
						<td> <?=$row2['item_desc']?>
						</td>
						<td> <?=$row2['units']?> </td>
						<td> <?=date('Y-m-d',$row2['demanddate']) ?> </td>
						<td> <?=date('Y-m-d',$row2['book_order_date']) ?> </td>
						<td> <?=$row2['supplyquantity']?> </td>
						 
						<td>
						<input name="a" type="radio" value="选择" class="coupons" rel="<?=$row2['item_no']?>:<?=$row2['units']?>:<?=$row2['item_desc']?>:<?=date('Y-m-d',$row2['demanddate'])?>:<?=$row2['supplyquantity']?>:<?=$row2['id']?> ">				 
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
                         <p><label class="text-info">need_date</label>　<label id="form_need_date"></label></p>
                    </div>
					<div style="display:none">
                         <p><label class="text-info">need_qty</label>　<label id="form_need_qty"></label></p>
                    </div>
					<div style="display:none">
                         <p><label class="text-info">supplid</label>　<label id="form_supplid"></label></p>
                    </div>
				 
  
                    <div style="display:none">
                        <p><input type="hidden" name="cat" id="cat" value="<?=$cat?>"/></p>
						<p><input type="hidden" name="fwValue" value="<?=$fwValue?>" id="fwValue"/></p>
                    </div>
  
 
</body>
</html>

