<?php 

include('includes/login2.inc');
function db_sql($sql,$type=1)
{
  $arr = array(); 
  $sql = mysql_query($sql);
   // $row = mysql_fetch_array($sql);--这里执行会导致type=2再执行一次，里面第一行资料被捞过从第二行开始获取,结果少资料
  if ($type==1) return mysql_fetch_array($sql);
  if ($type==2) 
  {
	while ($row=mysql_fetch_array($sql)) 
	{
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
$customer_code  = isset($_REQUEST['customer_code']) ? $_REQUEST['customer_code'] : '';	
$order_number = isset($_REQUEST['order_number']) ? $_REQUEST['order_number'] : '';	 
$where = '';
if($customer_code) 
{
  $where .= ' and h.customer_code like "%'.$customer_code.'%"';
}
 
if($order_number) 
{
  $where .= ' and h.order_number  like "%'.$order_number.'%"';
}

if($cat) 
{
	
   $where .= '';

}
 
$num = 50;
$off = $num*($page-1);
 
$count = db_sql("SELECT
       s.*,
       h.customer_code,h.need_date,h.status,
       c.*,d.item_name,d.item_desc
FROM
    so_lines_all s,
    so_headers_all h,
    customers c,sf_item_no d
WHERE  s.order_number = h.order_number 
AND c.customer_code = h.customer_code  and s.change_pr='N'
and s.stockid=d.item_no and 1=1 ".$where."",3);

$pages = ceil($count/$num);
$sql = "SELECT
       s.*,
       h.customer_code,h.need_date,h.status,
       c.*,d.item_name,d.item_desc,d.gongyi
FROM
    so_lines_all s,
    so_headers_all h,
    customers c,sf_item_no d
WHERE  s.order_number = h.order_number
AND c.customer_code = h.customer_code
and s.change_pr='N'
and s.stockid=d.item_no and 1=1 ".$where." ORDER BY h.order_number  desc limit ".$off.",".$num;
$list = db_sql($sql,2);
//  echo $sql;

function show_page($url,$page,$pages,$total,$t0='')
{  
  $str = '';
  $page = $page > $pages ? $pages : $page;
  if ($page>1) 
  {
    $str .= '<a class="pre" href="'.$url.(1).$t0.'">上一页</a>&nbsp;';
  } 
  else 
  {
	$str .= '<a class="pre">上一页</a>&nbsp;';
  }
  if ($page<5) $start=1; $end=5;
  if ($page>=5)
  {
    $start = $page-2;
    $end = $page+3;
  }
  $end = $end > $pages ? $pages : $end;
  for ($i=$start;$i<=$end;$i++) 
  {
	if ($i==$page) 
	{
	  $str .= '<span class="cur">'.$i.'</span>&nbsp;';
	} 
	else 
	{
	  $str .= '<a href="'.$url.$i.$t0.'">'.$i.'</a>&nbsp;&nbsp;';
	}
  }
    
  if ($page>=1 && $page<$pages) 
  {
	$str .= '<a href="'.$url.($page+1).$t0.'">下一页</a>&nbsp;';
  } 
  else 
  {   
	$str .= '<a class="next">下一页</a>&nbsp;';
  }

  $str .= '<span class="pages_c">页次:'.$page.'/'.$pages.'&nbsp;&nbsp;&nbsp;总计:'.$total.' </span>';
  return $str;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>

<title>查询供应商</title>
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
 

    
			$(".coupons").livequery("click", function() {
				var rel = this.getAttribute('rel');
				c = rel.split(":");
				$("#form_order_number").text(c[0]);
			    $("#form_customer_code").text(c[1]);
				$("#form_stockid").text(c[2]); 
				$("#form_item_name").text(c[3]);
				$("#form_item_desc").text(c[4]);
			    $("#form_uom").text(c[5]);
				$("#form_quantity").text(c[6]);
				$("#form_need_date").text(c[7]);
			});

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
				$("#form_order_number").text(c[0]);
			    $("#form_customer_code").text(c[1]);
				$("#form_stockid").text(c[2]); 
				$("#form_item_name").text(c[3]);
				$("#form_item_desc").text(c[4]);
			    $("#form_uom").text(c[5]);
				$("#form_quantity").text(c[6]);
				$("#form_need_date").text(c[7]);
				$("#form_line").text(c[8]);
				$("#form_gongyi").text(c[9]);
				W.document.getElementById('text_slect_order_number'+$('#fwValue').val()).value = $("label#form_order_number").text(); 
					    W.document.getElementById('text_slect_customer_code'+$('#fwValue').val()).value = $("label#form_customer_code").text();
					    W.document.getElementById('text_slect_stockid'+$('#fwValue').val()).value = $("label#form_stockid").text(); 
					    W.document.getElementById('text_slect_item_name'+$('#fwValue').val()).value = $("label#form_item_name").text(); 
						W.document.getElementById('text_slect_item_desc'+$('#fwValue').val()).value = $("label#form_item_desc").text();
						W.document.getElementById('text_slect_uom'+$('#fwValue').val()).value = $("label#form_uom").text();
						W.document.getElementById('text_slect_quantity'+$('#fwValue').val()).value = $("label#form_quantity").text();
						W.document.getElementById('text_slect_need_date'+$('#fwValue').val()).value = $("label#form_need_date").text();
						W.document.getElementById('text_slect_line'+$('#fwValue').val()).value = $("label#form_line").text();
				
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
				<img src="/JXC/css/xenos/images/magnifier.png" title="查询订单" alt="查询订单">查询订单
			</p>
			<form action="./BtnSearchorder_number.php?fwValue=<?=$cat?>&cat=<?=$cat?>" method ="POST">
				<div>
					 
					<table cellpadding="3" class="selection">
					<tr>
						<td>
							  客户代码：
						</td>
						<td>
							<input type="text" name="customer_code" value="<?=$customer_code?>">
						</td>
						<td> 
							订单号码：
						</td>
						<td>
							<input type="text" name="order_number" value="<?=$order_number?>">
						</td>
					</tr>
					
					
					</table>
					<div class="centre">
					     
						<input type="submit" value="查找">
					</div>
					</form>
					<br/>
					<table cellpadding="2" class="selection"  id="ck_company">
					<tr>
						<th class="ascending" width ="80"> 客户代号 </th>

						<th   width ="130"> 订单号码 </th>
						<th > 行 </th>
						<th class="ascending" width ="180"> 料号 </th>
						<th   width ="180">  料号名称 </th>
						<th width ="180">	 规格型号 </th> 
						<th width ="180">	 工艺 </th> 
						<th width ="90">	 数量 </th> 
						<th width ="90">	 需求日期 </th> 
					</tr>
					<?php foreach($list as $arr=>$row2){?>
					<tr class="EvenTableRows">
						<td> <?=$row2['customer_code']?> </td>
						<td> <?=$row2['order_number']?></td>
						<td> <?=$row2['line']?></td>
						<td> <?=$row2['stockid']?></td>
						<td> <?=$row2['item_name']?> </td>
						<td> <?=$row2['item_desc']?> </td>
						<td> <?=$row2['gongyi']?> </td>
						<td> <?=$row2['quantity']?> </td>
						<td> <?= date('Y-m-d',$row2['need_date'])?> </td>
					 	</td>
					<td>
					   <input name="a" type="hidden" value="选择" class="coupons" rel="<?=$row2['order_number']?>:<?=$row2['customer_code']?>:<?=$row2['stockid']?>:<?=$row2['item_name']?>:<?=$row2['item_desc']?>:<?=$row2['uom']?>:<?=$row2['quantity']?>:<?=date('Y-m-d',$row2['need_date'])?>:<?=$row2['line']?>:<?=$row2['gongyi']?>">	  			 
					</td>
					
					</tr>
					<?php }?>
					 
					 
					</table>
					<br/>
					<div class="centre">
					<?=show_page('?page=',$page,$pages,$count,'&fwValue='.$cat.'&cat='.$cat.'&order_number='.$order_number.'&customer_code='.$customer_code.'');?>
					
					</div>
				</div>
			
		</div>
	</div>
	<div id="FooterDiv">
		<div id="FooterWrapDiv">
			 
		</div>
	</div>
                    <div style="display:none">
                         <p><label class="text-info">order_number:</label>　<label id="form_order_number"></label></p>
                    </div>

					<div style="display:none">
                         <p><label class="text-info">customer_code</label>　<label id="form_customer_code"></label></p>
                    </div>
 

					 <div style="display:none">
                         <p><label class="text-info">stockid</label>　<label id="form_stockid"></label></p>
                    </div>


					<div style="display:none">
                         <p><label class="text-info">item_name</label>　<label id="form_item_name"></label></p>
                    </div>
					<div style="display:none">
                         <p><label class="text-info">item_desc</label>　<label id="form_item_desc"></label></p>
                    </div>
					<div style="display:none">
                         <p><label class="text-info">uom</label>　<label id="form_uom"></label></p>
                    </div>
					<div style="display:none">
                         <p><label class="text-info">quantity</label>　<label id="form_quantity"></label></p>
                    </div>
					<div style="display:none">
                         <p><label class="text-info">need_date</label>　<label id="form_need_date"></label></p>
                    </div>
					<div style="display:none">
                         <p><label class="text-info">line</label>　<label id="form_line"></label></p>
                    </div>
					<div style="display:none">
                         <p><label class="text-info">gongyi</label>　<label id="form_gongyi"></label></p>
                    </div>
					 
              
                     
                    <div style="display:none">
                        <p><input type="hidden" name="cat" id="cat" value="<?=$cat?>"/></p>
						<p><input type="hidden" name="fwValue" value="<?=$fwValue?>" id="fwValue"/></p>
                    </div>
  
 
</body>
</html>

