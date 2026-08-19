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
$item_name  = isset($_REQUEST['item_name']) ? $_REQUEST['item_name'] : '';
$item_desc = isset($_REQUEST['item_desc']) ? $_REQUEST['item_desc'] : '';	 
$where = '';
if($ItemNo) {
   $where .= ' and a.item_no like "%'.$ItemNo.'%"';
}
 
if($item_name) {
   $where .= ' and a.item_name like "%'.$item_name.'%"';
}
if($item_desc) {
   $where .= ' and a.item_desc like "%'.$item_desc.'%"';
}
if($cat) {
   $where .= " and c.assembly_item_no in (select primary_item from wip_jobs_all where wip_entity_name =  '".$cat."' )" ;
}


 
$num = 20;
$off = $num*($page-1);
 
$count = db_sql("select a.*,c.component_item  
 from sf_item_no a, bom_substitutes_all b,bom_lines_all c
 where  a.item_no=b.substitute_item 
 and b.component_sequence_id=c.component_sequence_id  ".$where.' 
 ORDER BY item_no  desc',3);

$pages = ceil($count/$num);
$sql = "select a.*,c.component_item  
 from sf_item_no a, bom_substitutes_all b,bom_lines_all c
 where  a.item_no=b.substitute_item 
 and b.component_sequence_id=c.component_sequence_id  ".$where.' 
 ORDER BY item_no  desc limit '.$off.','.$num.'';
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
				$("#form_ItemNo").text(c[0]);
				$("#form_units").text(c[1]);
				$("#form_item_name").text(c[2]);
				$("#form_item_desc").text(c[3]); 
				  		W.document.getElementById('text_slect_item_no'+$('#fwValue').val()).value = $("label#form_ItemNo").text(); 
					    W.document.getElementById('text_slect_units'+$('#fwValue').val()).value = $("label#form_units").text();
						W.document.getElementById('text_slect_item_name'+$('#fwValue').val()).value = $("label#form_item_name").text();
						W.document.getElementById('text_slect_item_desc'+$('#fwValue').val()).value = $("label#form_item_desc").text();    
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
			<form action="./SearchAllItemWIP.php?fwValue=<?=$cat?>&cat=<?=$cat?>" method ="POST">
				<div>
					 
					<table cellpadding="3" class="selection">
					<tr>
						<td>
							料号：
						</td>
						<td>
							<input type="text"  name="ItemNo" value="<?=$ItemNo?>">
						</td>
						<td>
							料号名称：
						</td>
						<td>
							<input type="text" name="item_name" value="<?=$item_name?>">
						</td>
						<td>
							规格型号：
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
					<table cellpadding="2" class="selection" id="ck_company">
					<tr><th class="ascending" width ="60"> 主料  </th> 
						<th class="ascending" width ="60"> 料号  </th> 
						<th class="ascending" width ="90">
							料号名称
						</th> 
						<th class="ascending" width ="90">
							规格型号
						</th> 
						<th  width ="50">
							单位
						</th>  
						
						
						 
					
					</tr>
					<?php foreach($list as $arr=>$row2){?>
					<tr class="EvenTableRows">
						<td>
							<?=$row2['component_item']?>
						</td>  <td>
							<?=$row2['item_no']?>
						</td> 
						<td> <?=$row2['item_name']?>
						</td>
						<td> <?=$row2['item_desc']?> </td> 
						<td> <?=$row2['units']?> </td> 
						  
						<td>
						<input name="a" type="hidden" value="选择" class="coupons" rel="<?=$row2['item_no']?>:<?=$row2['units']?>:<?=$row2['item_name']?>:<?=$row2['item_desc']?>">				 
						</td>
					</tr>
					<?php }?>
					 
					 
					</table>
					<br/>
					<div class="centre">
					<?=show_page('?page=',$page,$pages,$count,'&fwValue='.$cat.'&cat='.$cat.'&ItemNo='.$ItemNo.'&item_name='.$item_name.'&item_desc'.$item_desc.'');?>
					
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
                         <p><label class="text-info">item_name:</label>　<label id="form_item_name"></label></p>
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

