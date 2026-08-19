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
$operation_code  = isset($_REQUEST['operation_code']) ? $_REQUEST['operation_code'] : '';	
$remarks = isset($_REQUEST['remarks']) ? $_REQUEST['remarks'] : '';	 
$rate = isset($_REQUEST['rate']) ? $_REQUEST['rate'] : '';	 
$where = '';
if($operation_code) {
   $where .= ' and operation_code like "%'.$operation_code.'%"';
}
 
if($remarks) {
   $where .= ' and remarks like "%'.$remarks.'%"';
}
if($cat) {
	
    
	$where .= " and assembly_item_no='". $cat . "'  "; 

}


 
$num = 10;
$off = $num*($page-1);
 
$count = db_sql('select * from bom_routings_all where 1=1 '.$where.'',3);

$pages = ceil($count/$num);
$sql = 'select * from  bom_routings_all  where 1=1 '.$where.' ORDER BY operation_seq_num  desc limit '.$off.','.$num.'';
 
 
 
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

<title>查询工艺</title>
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
        window.onload=function(){        
            var api = frameElement.api, W = api.opener;
			var ch_checked=document.getElementById("chsecheck");
		 
			ch_checked.onclick=function(){
				var arr1=document.getElementsByName("boxlist");
				for(i=0;i<arr1.length;i++){ 
					if(arr1[i].checked==true){
						var f2=document.getElementsByClassName("coupons");
						var bot=document.getElementById("fwValue");
						var botint=parseInt(bot.value);
						rel=f2[i].value;
						c = rel.split(":");
				$("#form_operation_code").text(c[0]);
				$("#form_remarks").text(c[1]); 
				$("#form_operation_seq_num").text(c[2]); 
				$("#form_standtime").text(c[3]); 
                
				    W.document.getElementById('text_slect_operation_code'+$('#fwValue').val()).value = $("label#form_operation_code").text(); 
						W.document.getElementById('text_slect_operation_name'+$('#fwValue').val()).value = $("label#form_remarks").text();
                        W.document.getElementById('text_slect_operation_seq_num'+$('#fwValue').val()).value = $("label#form_operation_seq_num").text();
                        W.document.getElementById('text_slect_standtime'+$('#fwValue').val()).value = $("label#form_standtime").text();
                botint++;
				bot.value=botint+'';
					}
				}api.close();
			}
		}
   </script>

<div id="CanvasDiv">
 
	<div id="BodyDiv">
		<div id="BodyWrapDiv">
			<p class="page_title_text">
				<img src="./css/xenos/images/magnifier.png" title="查询工艺" alt="查询工艺">查询工艺
			</p>
			<form action="./Searchoperation_code1.php?fwValue=<?=$cat?>&cat=<?=$cat?>" method ="POST">
				<div>
					 
					<table cellpadding="3" class="selection">
					<tr>
						<td>
							工艺名称：
						</td>
						<td>
							<input type="text" name="operation_code" value="<?=$operation_code?>">
						</td>
						<td>
							工艺作业内容：
						</td>
						<td>
							<input type="text" name="remarks" value="<?=$remarks?>">
						</td>
					</tr>
					
					
					</table>
					<div class="centre">
					     
						<input type="submit" value="查找">
					</div>
					</form>
					<br/>
					<table cellpadding="2" class="selection" id="ck_company">
					<tr><th  width ="50"> 选择 </th>
						<th class="ascending" width ="100">
							工序
						</th>
                        <th class="ascending" width ="100">
							工艺名称
						</th>
						<th class="ascending" width ="100">
							工艺作业内容
						</th> 
						<th class="ascending" width ="100">
							作业时间
						</th> 
						 
					</tr>
					<?php foreach($list as $arr=>$row2){?>
					<tr class="EvenTableRows">
						<td> <input type="checkbox" style="height: 24px;width: 24px;" name="boxlist"> </td>
						<td>
							<?=$row2['operation_seq_num']?>
						</td>
                        <td>
							<?=$row2['operation_code']?>
						</td>
						<td> <?=$row2['remarks']?>
						</td> 
						<td> <?=$row2['rate']?>
						</td> 
							<td>
						<input name="a" type="hidden" class="coupons" value="<?=$row2['operation_code']?>:<?=$row2['remarks']?>:<?=$row2['operation_seq_num']?>:<?=$row2['rate']?>
						">				 
						</td>
 
					</tr>
					<?php }?>
					 
					 
					</table>
					<input type="button" class="choosecheck" id="chsecheck" style="height: 24px;width: 100px;" value="确定">
					<br/>
					<div class="centre">
					<?=show_page('?page=',$page,$pages,$count,'&fwValue='.$cat.'&cat='.$cat.'&operation_code='.$operation_code.'&remarks'.$remarks.'&rate'.$rate.'');?>
					
					</div>
				</div>
			
		</div>
	</div>
	<div id="FooterDiv">
		<div id="FooterWrapDiv">
			 
		</div>
	</div>
                   <div style="display:none">
                         <p><label class="text-info">operation_code:</label>　<label id="form_operation_code"></label></p>
                    </div>

					<div style="display:none">
                         <p><label class="text-info">remarks</label>　<label id="form_remarks"></label></p>
                    </div>
                    <div style="display:none">
                         <p><label class="text-info">operation_seq_num</label>　<label id="form_operation_seq_num"></label></p>
                    </div>
                     <div style="display:none">
                         <p><label class="text-info">rate</label>　<label id="form_standtime"></label></p>
                    </div>
                    <div style="display:none">
                        <p><input type="hidden" name="cat" id="cat" value="<?=$cat?>"/></p>
						<p><input type="hidden" name="fwValue" value="<?=$fwValue?>" id="fwValue"/></p>
                    </div>
  
 
</body>
</html>

