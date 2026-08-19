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
$function_name  = isset($_REQUEST['function_name']) ? $_REQUEST['function_name'] : '';	
$model_name  = isset($_REQUEST['model_name']) ? $_REQUEST['model_name'] : '';	
$where = "and function_name not in (select function_name from user_power where user_id='".$cat."')";

if($function_name) {
   $where .= ' and function_name like "%'.$function_name.'%"';
}
if($model_name) {
   $where .= ' and model_name like "%'.$model_name.'%"';
}
  
 
$num = 10;
$off = $num*($page-1);
 
$count = db_sql("select * from functions
where 1=1  ".$where."",3);

$pages = ceil($count/$num);
$sql = "select   * from functions
where 1=1 ".$where."  ORDER BY model_name  desc limit ".$off.",".$num;
   
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

<title>查询功能</title>
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
				$("#form_function_name").text(c[0]); 
				$("#form_model_name").text(c[1]); 
				 W.document.getElementById('text_slect_function_name'+$('#fwValue').val()).value = $("label#form_function_name").text(); 
					 W.document.getElementById('text_slect_model_name'+$('#fwValue').val()).value = $("label#form_model_name").text();
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
				<img src="./css/xenos/images/magnifier.png" title="查询料号" alt="查询料号">查询料号
			</p>
			<form action="./btn_slect_user_funtion.php?fwValue=<?=$cat?>&cat=<?=$cat?>" method ="POST">
				<div>
					 
					<table cellpadding="3" class="selection">
					<tr>
						<td>
							功能权限：
						</td>
						<td>
							<input type="text" name="function_name" value="<?=$function_name?>">
						</td>
						<td>
							模组：
						</td>
						<td>
							<input type="text" name="model_name" value="<?=$model_name?>">
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
						<th class="ascending" width ="400">
							功能权限
						</th>
						<th class="ascending" width ="200">
							模组
						</th>
					
					</tr>
					<?php foreach($list as $arr=>$row2){?>
					<td  >
							<?=$row2['function_name']?>
							
						</td  >
						 		 
						<td> <?=$row2['model_name']?> </td>
						<td>
						<input name="a" type="hidden" value="选择" class="coupons"  rel="<?=$row2['function_name']?>:<?=$row2['model_name']?> 	">	 
						</td>
					</tr>
					<?php }?>
					
					 
					</table>
					<br/>
					<div class="centre">
					<?=show_page('?page=',$page,$pages,$count,'&fwValue='.$cat.'&cat='.$cat.'&function_name='.$function_name.'&model_name'.$model_name.'');?>
					
					</div>
				</div>
			
		</div>
	</div>
	<div id="FooterDiv">
		<div id="FooterWrapDiv">
			 
		</div>
	</div>
                    <div style="display:none">
                         <p><label class="text-info">function_name:</label>　<label id="form_function_name"></label></p>
						 <p><label class="text-info">model_name:</label>　<label id="form_model_name"></label></p>
                    </div>
                    <div style="display:none">
                        <p><input type="hidden" name="cat" id="cat" value="<?=$cat?>"/></p>
						<p><input type="hidden" name="fwValue" value="<?=$fwValue?>" id="fwValue"/></p>
                    </div>
   	
 
</body>
</html>

