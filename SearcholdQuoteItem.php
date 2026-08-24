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
$ItemNo  = isset($_REQUEST['ItemNo']) ? $_REQUEST['ItemNo'] : '';	
$ItemDesc = isset($_REQUEST['ItemDesc']) ? $_REQUEST['ItemDesc'] : '';	 
$where = "  ";
if($ItemNo) {
   $where .= ' and about_item like "%'.$ItemNo.'%"';
}
 
if($ItemDesc) {
   $where .= ' and leixing like "%'.$ItemDesc.'%"';
}

if($cat) {     
	$where .= "and about_item is not null  "; 
}
 
$num = 10;
$off = $num*($page-1);
 
$count = db_sql('select  a.* 
from quote_lines_all a  where  1=1 '.$where.'',3);

$pages = ceil($count/$num);
$sql = 'select  a.*  
from quote_lines_all a  where 1=1 '.$where.' ORDER BY about_item  desc limit '.$off.','.$num.'';
   
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
				$("#form_about_item").text(c[0]);
				$("#form_uom").text(c[1]); 
				$("#form_leixing").text(c[2]); 
					$("#form_chima").text(c[3]); 
					$("#form_need_remark").text(c[4]);
					$("#form_price").text(c[5]);
				  W.document.getElementById('text_slect_about_item'+$('#fwValue').val()).value = $("label#form_about_item").text(); 
					    W.document.getElementById('text_slect_uom'+$('#fwValue').val()).value = $("label#form_uom").text();   
						W.document.getElementById('text_slect_leixing'+$('#fwValue').val()).value = $("label#form_leixing").text(); 
						W.document.getElementById('text_slect_chima'+$('#fwValue').val()).value = $("label#form_chima").text();
						W.document.getElementById('text_slect_need_remark'+$('#fwValue').val()).value = $("label#form_need_remark").text();
						W.document.getElementById('text_slect_price'+$('#fwValue').val()).value = $("label#form_price").text();
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
				<img src="/JXC/css/xenos/images/magnifier.png" title="查询产品报价信息" alt="查询产品报价信息">查询产品报价信息
			</p>
			<form action="./SearcholdQuoteItem.php?fwValue=<?=$cat?>&cat=<?=$cat?>" method ="POST">
				<div>
					 
					<table cellpadding="3" class="selection">
					<tr>
						<td>
							料号
						</td>
						<td>
							<input type="text" name="ItemNo" value="<?=$ItemNo?>">
						</td>
						<td>
							料号名称
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
					<table cellpadding="2" class="selection" id="ck_company">
					<tr>
						<th class="ascending" width ="150">
							对应料号
						</th>
						<th class="ascending" width ="200">
							类型
						</th><th class="ascending" width ="200">
							尺码
						</th>
					
						<th class="ascending" width ="200">
							工艺
						</th><th class="ascending" width ="200">
							指导价格
						</th>
						<th class="ascending" width ="50">
							单位
						</th>
					</tr> 	
					<?php foreach($list as $arr=>$row2){?>
					<tr class="EvenTableRows">
						<td>
							<?=$row2['about_item']?>
						</td>
						<td> <?=$row2['leixing']?>
						</td>
						  <td> <?=$row2['chima']?>
						</td>  
						<td> <?=$row2['need_remark']?>
						</td>
						<td> <?=$row2['price']?>
						</td>
						<td> <?=$row2['uom']?> </td> 
						
						<td>
						<input name="a" type="hidden" value="选择" class="coupons" rel="<?=$row2['about_item']?>:<?=$row2['uom']?>:<?=$row2['leixing']?>:<?=$row2['chima']?>:<?=$row2['need_remark']?>:<?=$row2['price']?>">				 
						</td>
					</tr>
					<?php }?>
					 
					 
					</table>
					<br/>
					<div class="centre">
					<?=show_page('?page=',$page,$pages,$count,'&fwValue='.$cat.'&cat='.$cat.'&ItemNo='.$ItemNo.'&ItemDesc='.$ItemDesc.'');?>
					
					</div>
				</div>
			
		</div>
	</div>
	<div id="FooterDiv">
		<div id="FooterWrapDiv">
			 
		</div>
	</div>
                   <div style="display:none">
                         <p><label class="text-info">about_item:</label>　<label id="form_about_item"></label></p>
                    </div>

					 <div style="display:none">
                         <p><label class="text-info">uom:</label>　<label id="form_uom"></label></p>
                    </div> 
					<div style="display:none">
                         <p><label class="text-info">leixing:</label>　<label id="form_leixing"></label></p>
                    </div>
					<div style="display:none">
                         <p><label class="text-info">chima:</label>　<label id="form_chima"></label></p>
                    </div>
					<div style="display:none">
                         <p><label class="text-info">need_remark:</label>　<label id="form_need_remark"></label></p>
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