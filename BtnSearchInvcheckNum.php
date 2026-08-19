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
$check_num  = isset($_REQUEST['check_num']) ? $_REQUEST['check_num'] : '';	
$subinventory_code = isset($_REQUEST['subinventory_code']) ? $_REQUEST['subinventory_code'] : '';	 
$where = '';
if($check_num) {
   $where .= ' and check_num like "%'.$check_num.'%"';
}
 
if($subinventory_code) {
   $where .= ' and subinventory_code like "%'.$subinventory_code.'%"';
}
 $where .= " and status= '开始' ";
 
$num = 10;
$off = $num*($page-1);
 
$count = db_sql('select * from inv_check_headers_all where 1=1 '.$where.'',3);

$pages = ceil($count/$num);
$sql = 'select * from inv_check_headers_all where 1=1 '.$where.' ORDER BY check_num  desc limit '.$off.','.$num.'';
   
 
 
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

<title>查询员工</title>
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
					$("#form_check_num").text(c[0]);
				$("#form_subinventory_code").text(c[1]);
				  W.document.getElementById('text_slect_check_num'+$('#fwValue').val()).value = $("label#form_check_num").text(); 
					    W.document.getElementById('text_slect_subinventory_code'+$('#fwValue').val()).value = $("label#form_subinventory_code").text();
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
				<img src="./css/xenos/images/magnifier.png" title="查询盘点单" alt="查询盘点单">查询盘点单
			</p>
			<form action="./BtnSearchInvcheckNum.php?fwValue=<?=$cat?>&cat=<?=$cat?>" method ="POST">
				<div>
					 
					<table cellpadding="3" class="selection">
					<tr>
						<td>
							盘点单号
						</td>
						<td>
							<input type="text" name="check_num" value="<?=$check_num?>">
						</td>
						<td>
							仓库名称：
						</td>
						<td>
							<input type="text" name="subinventory_code" value="<?=$subinventory_code?>">
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
							盘点单号
						</th>
						<th class="ascending" width ="200">
							仓库
						</th>
						 <th class="ascending" width ="150">
							盘点负责人
						</th>
						<th class="ascending" width ="80">
							日期
						</th>
						
					</tr>
					<?php foreach($list as $arr=>$row2){?>
					<tr class="EvenTableRows">
						<td>
							<?=$row2['check_num']?>
						</td>
						<td> <?=$row2['subinventory_code']?>
						</td>
						<td> <?=$row2['check_person']?> </td>
						<td> <?=date('Y-m-d',$row2['creation_date'])?> </td>
				 
						</td>
						<td>
						<input name="a" type="hidden" value="选择" class="coupons" rel="<?=$row2['check_num']?>:<?=$row2['subinventory_code']?>">				 
						</td>
					</tr>
					<?php }?>
					 
					 
					</table>
					<br/>
					<div class="centre">
					<?=show_page('?page=',$page,$pages,$count,'&fwValue='.$cat.'&cat='.$cat.'&check_num='.$check_num.'&subinventory_code'.$subinventory_code.'');?>
					
					</div>
				</div>
			
		</div>
	</div>
	<div id="FooterDiv">
		<div id="FooterWrapDiv">
			 
		</div>
	</div>
                   <div style="display:none">
                         <p><label class="text-info">check_num:</label>　<label id="form_check_num"></label></p>
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

