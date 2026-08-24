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
$loccode  = isset($_REQUEST['loccode']) ? $_REQUEST['loccode'] : '';	
$locationname = isset($_REQUEST['locationname']) ? $_REQUEST['locationname'] : '';	 
$where = '';
if($loccode) {
   $where .= ' and loccode like "%'.$loccode.'%"';
}
 
if($locationname) {
   $where .= ' and locationname like "%'.$locationname.'%"';
}
if($cat) {
	
    $array = array('buliao'=>'主布','shaliao'=>'纱','fuliao'=>'辅材','luomalianliao'=>'罗马帘');
	if (@$array[$cat]=='辅材') {
	$where .=  " and managed='Y' ";}
	else {
	$where .= " and managed='Y'  ";}

}
 
$num = 10;
$off = $num*($page-1);
 
$count = db_sql('select * from locations where 1=1 '.$where.'',3);

$pages = ceil($count/$num);
$sql = 'select * from locations where 1=1 '.$where.' ORDER BY loccode  desc limit '.$off.','.$num.'';
   
 
 
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

<title>查询仓库</title>
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
                        W.document.getElementById('text_slect_loccode'+$('#fwValue').val()).value = $("label#form_loccode").text(); 
						W.document.getElementById('text_slect_locationname'+$('#fwValue').val()).value = $("label#form_locationname").text();
						
						$("#xianshi").css("display","block"); 
                        break;
                    default :
                        alert('Data Post Error');
                }
            };
            
			
			$(".coupons").livequery("click", function() {
				var rel = this.getAttribute('rel');
				c = rel.split(":");
				$("#form_loccode").text(c[0]);
				$("#form_locationname").text(c[1]); 
			});
			 
			
			
        });
    </script>
<div id="CanvasDiv">
 
	<div id="BodyDiv">
		<div id="BodyWrapDiv">
			<p class="page_title_text">
				<img src="css/xenos/images/magnifier.png" title="查询仓库" alt="查询仓库">查询仓库
			</p>
			<form action="./Searchsubcode.php?fwValue=<?=$cat?>&cat=<?=$cat?>" method ="POST">
				<div>
					 
					<table cellpadding="3" class="selection">
					<tr>
						<td>
							仓库代码：
						</td>
						<td>
							<input type="text" name="loccode" value="<?=$loccode?>">
						</td>
						<td>
							仓库名称：
						</td>
						<td>
							<input type="text" name="locationname" value="<?=$locationname?>">
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
						<th class="ascending" width ="100">
							仓库代号
						</th>
						<th class="ascending" width ="100">
							仓库名称
						</th>
						<th class="ascending" width ="150">
							地址
						</th>
						<th class="ascending" width ="80">
							联系人
						</th>
						 
						<th class="ascending" width ="50">
							操作
						</th>
					</tr>
					<?php foreach($list as $arr=>$row2){?>
					<tr class="EvenTableRows">
						<td>
							<?=$row2['loccode']?>
						</td>
						<td> <?=$row2['locationname']?>
						</td>
						<td> <?=$row2['address']?> </td>
						<td> <?=$row2['contact']?> </td>						 
						<td>
						<input name="a" type="radio" value="选择" class="coupons" rel="<?=$row2['loccode']?>:<?=$row2['locationname']?>	">	 
						</td>
					</tr>
					<?php }?>
					 
					 
					</table>
					<br/>
					<div class="centre">
					<?=show_page('?page=',$page,$pages,$count,'&fwValue='.$cat.'&cat='.$cat.'&loccode='.$loccode.'&locationname'.$locationname.'');?>
					
					</div>
				</div>
			
		</div>
	</div>
	<div id="FooterDiv">
		<div id="FooterWrapDiv">
			 
		</div>
	</div>
                   <div style="display:none">
                         <p><label class="text-info">loccode:</label>　<label id="form_loccode"></label></p>
                    </div>

					<div style="display:none">
                         <p><label class="text-info">locationname</label>　<label id="form_locationname"></label></p>
                    </div>
  
                    <div style="display:none">
                        <p><input type="hidden" name="cat" id="cat" value="<?=$cat?>"/></p>
						<p><input type="hidden" name="fwValue" value="<?=$fwValue?>" id="fwValue"/></p>
                    </div>
  
 
</body>
</html>

