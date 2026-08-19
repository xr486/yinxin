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
$effective_date = isset($_REQUEST['effective_date']) ? $_REQUEST['effective_date'] : '';
$disable_date = isset($_REQUEST['disable_date']) ? $_REQUEST['disable_date'] : '';	 
$vendor_code = isset($_REQUEST['vendor_code']) ? $_REQUEST['vendor_code'] : '';	 
$price = isset($_REQUEST['price']) ? $_REQUEST['price'] : '';
$vendor_name = isset($_REQUEST['vendor_name']) ? $_REQUEST['vendor_name'] : '';	 
$approved_status = isset($_REQUEST['approved_status']) ? $_REQUEST['approved_status'] : '';	 
$where = '';
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
 
$count = db_sql('SELECT a.item_no, a.item_desc, b.vendor_code, c.vendor_name, b.price,b.approved_status, b.effective_date, b.disable_date
FROM sf_item_no AS a
LEFT JOIN (
po_item_prices_all AS b
LEFT JOIN vendors AS c ON c.vendor_code = b.vendor_code
) ON b.stockid = a.item_no and 1=1 '.$where.'',3);

$pages = ceil($count/$num);
$sql = 'SELECT a.item_no, a.item_desc, b.vendor_code, c.vendor_name,b.price, b.approved_status,b.effective_date, b.disable_date
FROM sf_item_no AS a
LEFT JOIN (
po_item_prices_all AS b
LEFT JOIN vendors AS c ON c.vendor_code = b.vendor_code
) ON b.stockid = a.item_no where 1=1 '.$where.' ORDER BY item_no  desc limit '.$off.','.$num.'';
   
 
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

<title>查询银行</title>
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
                        W.document.getElementById('text_slect_item_no'+$('#fwValue').val()).value = $("label#form_item_no").text(); 
					    W.document.getElementById('text_slect_item_desc'+$('#fwValue').val()).value = $("label#form_item_desc").text();
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
			});

			 
			
			
        });
    </script>
<div id="CanvasDiv">
 
	<div id="BodyDiv">
		<div id="BodyWrapDiv">
			<p class="page_title_text">
				<img src="/JXC/css/xenos/images/magnifier.png" title="查询料号" alt="查询料号">查询料号
			</p>
			<form action="/JXC/BtnSearchItem.php?fwValue=<?=$cat?>&cat=<?=$cat?>" method ="POST">
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
						<th class="ascending" width ="150">
							料号
						</th>
						<th class="ascending" width ="200">
							料号名称
						</th>
						<th class="ascending" width ="150">
							对应供应商代号
						</th>
                        <th class="ascending" width ="200">
							对应供应商名称
						</th>
						<th class="ascending" width ="100">
							对应价格
						</th>
                        <th class="ascending" width ="150">
							签核状态
						</th>
						<th class="ascending" width ="150">
							生效时间
						</th>
                        <th class="ascending" width ="150">
							失效时间
						</th>
						<th   width ="50">
							操作
						</th>
					</tr>
					<?php foreach($list as $arr=>$row2){?>
					<tr class="EvenTableRows">
						<td>
							<?=$row2['item_no']?>
						</td>
						<td> <?=$row2['item_desc']?></td>
                        <td> <?=$row2['vendor_code']?></td>
                        <td> <?=$row2['vendor_name']?></td>
                        <td> <?=$row2['price']?></td>
                        <?php     unset($v_status);
            // p($myrow);
            if ($row2['approved_status'] == 'INPROCESS') {
                $v_status = '待签核';
            } elseif ($row2['approved_status'] == 'APPROVED') {
                $v_status = '已签核';
            } elseif ($row2['approved_status'] == 'REJECTED') {
                $v_status = '已拒签';
            } elseif ($row2['approved_status'] == 'CANCELED') {
                $v_status = '已取消';
            }else {
                $v_status= '';
            }?>
                        <?php echo '<td>' . $v_status . '</td>'?>
                        <?php if ($row2['effective_date']==''){?>
                        <td> <?=$row2['effective_date']?></td>
                        <?php  }else {?>
                        <td> <?=date('Y-m-d',$row2['effective_date'])?></td>
                        <?php }?>
                        <?php if ($row2['disable_date']==''){?>
                        <td> <?=$row2['disable_date']?></td>
                        <?php  }else {?>
                        <td> <?=date('Y-m-d',$row2['disable_date'])?></td>
                        <?php }?>
						<td>
						<input name="a" type="radio" value="选择" class="coupons" rel="<?=$row2['item_no']?>:<?=$row2['item_desc']?>">				 
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
                        <p><input type="hidden" name="cat" id="cat" value="<?=$cat?>"/></p>
						<p><input type="hidden" name="fwValue" value="<?=$fwValue?>" id="fwValue"/></p>
                    </div>
  
 
</body>
</html>

