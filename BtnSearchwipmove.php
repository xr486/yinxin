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
$WIP_ENTITY_NAME  = isset($_REQUEST['WIP_ENTITY_NAME']) ? $_REQUEST['WIP_ENTITY_NAME'] : '';	
$PRIMARY_ITEM = isset($_REQUEST['PRIMARY_ITEM']) ? $_REQUEST['PRIMARY_ITEM'] : ''; 	
$where = "";
if($WIP_ENTITY_NAME) {
   $where .= ' and a.WIP_ENTITY_NAME like "%'.$WIP_ENTITY_NAME.'%"';
}
 
if($PRIMARY_ITEM) {
   $where .= ' and a.PRIMARY_ITEM like "%'.$PRIMARY_ITEM.'%"';
}

if($cat) {
	
     
	$where .= " and 1=1 "; 

}

$num = 10;
$off = $num*($page-1);
 
    $count = db_sql("SELECT PRIMARY_ITEM,WIP_ENTITY_NAME,START_QUANTITY,SCHEDULED_START_DATE
    FROM wip_jobs_all  a where status_type='核发'  
	".$where.'',3);

$pages = ceil($count/$num);
$sql = "SELECT PRIMARY_ITEM,WIP_ENTITY_NAME,START_QUANTITY,SCHEDULED_START_DATE
    FROM wip_jobs_all  a where status_type='核发'  
".$where.' ORDER BY WIP_ENTITY_NAME  desc limit '.$off.','.$num.'';
   
 
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

<title>查询已存在的源BOM</title>
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
                        W.document.getElementById('text_slect_WIP_ENTITY_NAME'+$('#fwValue').val()).value = $("label#form_WIP_ENTITY_NAME").text(); 
					    W.document.getElementById('text_slect_PRIMARY_ITEM'+$('#fwValue').val()).value = $("label#form_PRIMARY_ITEM").text();
						W.document.getElementById('text_slect_START_QUANTITY'+$('#fwValue').val()).value = $("label#form_START_QUANTITY").text();
						$("#xianshi").css("display","block"); 
                        break;
                    default :
                        alert('Data Post Error');
                }
            };
            
			
			$(".coupons").livequery("click", function() {
				var rel = this.getAttribute('rel');
				c = rel.split(":");
				$("#form_WIP_ENTITY_NAME").text(c[0]);
				$("#form_PRIMARY_ITEM").text(c[1]);
				$("#form_START_QUANTITY").text(c[2]);
			});

			 
			
			
        });
    </script>
<div id="CanvasDiv">
 
	<div id="BodyDiv">
		<div id="BodyWrapDiv">
			<p class="page_title_text">
				<img src="/JXC/css/xenos/images/magnifier.png" title="查询料号" alt="查询料号">查询料号
			</p>
			<form action="/JXC/BtnSearchNoBomItem.php?fwValue=<?=$cat?>&cat=<?=$cat?>" method ="POST">
				<div>
					 
					<table cellpadding="3" class="selection">
					<tr>
						<td>
							工单名称：
						</td>
						<td>
							<input type="text" name="WIP_ENTITY_NAME" value="<?=$WIP_ENTITY_NAME?>">
						</td>
						<td>
							料号：
						</td>
						<td>
							<input type="text" name="PRIMARY_ITEM" value="<?=$PRIMARY_ITEM?>">
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
						<th  width ="150">
							工单名称
						</th>
						<th  width ="150">
							料号
						</th>
						<th  width ="350">
							开工数量
						</th>
						<th  width ="350">
							开工日期
						</th>

					
						<th   width ="50">
							操作
						</th>
					</tr>
					<?php foreach($list as $arr=>$row2){?>
					<tr class="EvenTableRows">
						<td>
							<?=$row2['WIP_ENTITY_NAME']?>
						</td>
						<td> <?=$row2['PRIMARY_ITEM']?></td>
						<td> <?=$row2['START_QUANTITY']?></td>
						<td> <?=date('Y-m-d',$row2['SCHEDULED_START_DATE'])?></td>
						<td>
						<input name="a" type="radio" value="选择" class="coupons" rel="<?=$row2['WIP_ENTITY_NAME']?>:<?=$row2['PRIMARY_ITEM']?>:<?=$row2['START_QUANTITY']?>">				 
						</td>
					</tr>
					<?php }?>
					 
					 
					</table>
					<br/>
					<div class="centre">
					<?=show_page('?page=',$page,$pages,$count,'&fwValue='.$cat.'&cat='.$cat.'&WIP_ENTITY_NAME='.$WIP_ENTITY_NAME.'&PRIMARY_ITEM'.$PRIMARY_ITEM.'');?>
					
					</div>
				</div>
			
		</div>
	</div>
	<div id="FooterDiv">
		<div id="FooterWrapDiv">
			 
		</div>
	</div>
                   <div style="display:none">
                         <p><label class="text-info">WIP_ENTITY_NAME</label>　<label id="form_WIP_ENTITY_NAME"></label></p>
                    </div>

					 <div style="display:none">
                         <p><label class="text-info">PRIMARY_ITEM</label>　<label id="form_PRIMARY_ITEM"></label></p>
                    </div>
					<div style="display:none">
                         <p><label class="text-info">START_QUANTITY</label>　<label id="form_START_QUANTITY"></label></p>
                    </div>			
					
                     
                    <div style="display:none">
                        <p><input type="hidden" name="cat" id="cat" value="<?=$cat?>"/></p>
						<p><input type="hidden" name="fwValue" value="<?=$fwValue?>" id="fwValue"/></p>
                    </div>
  
 
</body>
</html>

