<?php 


include('includes/login2.inc');
if(!$con)die(mysql_error()); 
mysql_select_db($db_name); 
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
$p_id  = isset($_REQUEST['p_id']) ? $_REQUEST['p_id'] : '';	
$p_name = isset($_REQUEST['p_name']) ? $_REQUEST['p_name'] : '';	 
$where = '';


 
$num = 10;
$off = $num*($page-1);
 
$count = db_sql('select * from wip_endproducts where 1=1',3);

$pages = ceil($count/$num);
$where = 'select * from wip_endproducts where 1=1';
if($p_id) {
	$where .= ' and p_id like "%'.$p_id.'%"';
}

if($p_name) {
	$where .= ' and p_name like "%'.$p_name.'%"';
}
$where.=' ORDER BY p_id desc limit '.$off.','.$num.'';
 
$list = db_sql($where,2);
 

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

<title>查询供应商</title>
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
                        W.document.getElementById('p_id'+$('#fwValue').val()).value = $("label#p_id").text(); 
                        break;
                    default :
                        alert('Data Post Error');
                }
            };
            
			
			$(".coupons").livequery("click", function() {
				var rel = this.getAttribute('rel');
				c = rel.split(":");
				$("#p_id").text(c[0]);
			});
			 
			
			
        });
    </script>
<div id="CanvasDiv">
 
	<div id="BodyDiv">
		<div id="BodyWrapDiv">
			<p class="page_title_text">
				<img src="/JXC/css/xenos/images/magnifier.png" title="查询成品料号" alt="查询成品料号">查询成品料号
			</p>
			<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST">
				<div>
					 
					<table cellpadding="3" class="selection">
					<tr>
						<td>
							成品编号
						</td>
						<td>
							<input type="text" name="p_id" value="<?=$p_id?>">
						</td>
						<td>
							名称：
						</td>
						<td>
							<input type="text" name="p_name" value="<?=$p_name?>">
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
						<th class="ascending" width ="60">
							成品编号
						</th>
						<th class="ascending" width ="60">
						    名称
						</th>
						<th class="ascending" width ="60">
							 材质
						</th>
						<th   width ="60">
							造型单价
						</th>
						 
						 <th width ="60">
							库存数量
						</th>
						<th width ="60">
							一天生产个数
						</th>
						<th>
							操作
						</th>
					</tr>
					<?php foreach($list as $arr=>$row2){?>
					<tr class="EvenTableRows">
						<td>
							<?=$row2['p_id']?>
						</td>
						<td> <?=$row2['p_name']?>
						</td>
						<td> <?=$row2['material_quality']?> </td>
						<td> <?=$row2['model_unit_price']?> </td>
						<td> <?=$row2['stock_count']?> </td>
				        
						</td>
						<td> <?=$row2['production_oneday']?> </td>
						</td>
						<td>
						<input name="a" type="radio" value="选择" class="coupons" rel="<?=$row2['p_id']?>:<?=$row2['p_name']?>:<?=$row2['material_quality']?>:<?=$row2['model_unit_price']?>:<?=$row2['stock_count']?>">				 
						</td>
					</tr>
					<?php }?>
					 
					 
					</table>
					<br/>
					<div class="centre">
					<?=show_page('?page=',$page,$pages,$count,'&fwValue='.$cat.'&cat='.$cat.'&p_id='.$p_id.'&p_name'.$p_name.'');?>
					
					</div>
				</div>
			
		</div>
	</div>
	<div id="FooterDiv">
		<div id="FooterWrapDiv">
			 
		</div>
	</div>
                   <div style="display:none">
                         <p><label class="text-info">vendorcode:</label>　<label id="p_id"></label></p>
                    </div>

					 <div style="display:none">
                         <p><label class="text-info">vendorname</label>　<label id="form_name"></label></p>
                    </div>

					<div style="display:none">
                         <p><label class="text-info">customercontacts</label>　<label id="form_contacts"></label></p>
                    </div>

					<div style="display:none">
                         <p><label class="text-info">customeraddress</label>　<label id="form_address"></label></p>
                    </div>

					<div style="display:none">
                         <p><label class="text-info">currencycode</label>　<label id="form_currency_code"></label></p>
                    </div>
 
                     
                    <div style="display:none">
                        <p><input type="hidden" name="cat" id="cat" value="<?=$cat?>"/></p>
						<p><input type="hidden" name="fwValue" value="<?=$fwValue?>" id="fwValue"/></p>
                    </div>
  
 
</body>
</html>

