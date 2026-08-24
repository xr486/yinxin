<?php 
include('includes/login2.inc'); 
date_default_timezone_set('Asia/Shanghai');
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
$sub     = isset($_REQUEST['sub']) ? $_REQUEST['sub'] : '';	
$page    = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;	
$lot_num  = isset($_REQUEST['lot_num']) ? $_REQUEST['lot_num'] : '';		 
$where = '';
if($lot_num) {
   $where .= ' and ioq.lot_num like "%'.$lot_num.'%"';
}

if($cat) {     
	$where .= "and ioq.stockid='".$cat."' "; 
}
if($sub) {     
	$where .= "and ioq.subinventory_code='".$sub."' "; 
}

$num = 10;
$off = $num*($page-1);
 
$count = db_sql('select  ioq.lot_num,ioq.shengchan_date,f.youxiaoqi from inv_onhand_quantity_all ioq,sf_item_no f where  ioq.stockid = f.item_no and 1=1 '.$where.' group by ioq.lot_num,ioq.shengchan_date,f.youxiaoqi ORDER BY lot_num ',3);

$pages = ceil($count/$num);
$sql = 'select  ioq.lot_num,ioq.shengchan_date,f.youxiaoqi from inv_onhand_quantity_all ioq,sf_item_no f where ioq.stockid = f.item_no and  1=1 '.$where.' group by ioq.lot_num,ioq.shengchan_date,f.youxiaoqi ORDER BY lot_num  limit '.$off.','.$num.'';
   
// echo $sql; 
 
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
	<style>
        .choosecheck{
            border-radius: 4px;
            border: 1px solid #555;
			box-shadow: 0 4px rgba(0, 0, 0, 0.3);
			margin:30px auto;
        }
    </style>
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
				$("#form_lot_num").text(c[0]);
				$("#form_shengchan_date").text(c[1]);
				$("#form_shixiao_date").text(c[2]);
				
				  W.document.getElementById('lot_num'+$('#fwValue').val()).value = $("label#form_lot_num").text(); 
					    W.document.getElementById('shengchan_date'+$('#fwValue').val()).value = $("label#form_shengchan_date").text(); 
					    W.document.getElementById('expiring_date'+$('#fwValue').val()).value = $("label#form_shixiao_date").text(); 
					       
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
			<form action="./SearchOnHandItemLot.php?fwValue=<?=$fwValue?>&cat=<?=$cat?>&sub=<?=$sub?>" method ="POST">
				<div>
					 
					<table cellpadding="3" class="selection">
					<tr>
                    <td>
							料号：
						</td>
						<td>
							<input type="text" readonly="readonly" name="cat" value="<?=$cat?>">
						</td>
						<td>
							批号：
						</td>
						<td>
							<input type="text" name="lot_num" value="<?=$lot_num?>">
						</td>
						
					</tr>
					<tr>
                    <td>
							仓库：
						</td>
						<td>
							<input type="text" readonly="readonly" name="sub" value="<?=$sub?>">
						</td>
						<td>
							
						</td>
						<td>
							
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
						
						<th  width ="50">
							批号
						</th> 
						<th  width ="50">
							生产日期
						</th> 
                        <th  width ="50">
							失效日期
						</th> 
				
					</tr>
					<?php foreach($list as $arr=>$row2){
						
						if($row2['shengchan_date'] == 0){
							$shengchan_date = '';
							$expirationDate = '';
						}else{
							$shengchan_date = date('Y-m-d',$row2['shengchan_date']);

                            // 生产日期（格式：YYYY-MM-DD）
                            $productionDate = date('Y-m-d',$row2['shengchan_date']);

                            // 有效期天数
                            $validityDays = $row2['youxiaoqi'];

                            // 将有效期天数转换为年数
                            $validityYears = floor($validityDays / 365);

                            // 将生产日期字符串转换为 DateTime 对象
                            $productionDateObj = new DateTime($productionDate);

                            // 计算失效日期
                            $expirationDateObj = clone $productionDateObj;
                            $expirationDateObj->modify('+ ' . $validityYears . ' years');

                            // 将失效日期格式化为 YYYY-MM-DD
                            $expirationDate = $expirationDateObj->format('Y-m-d');



                            // $shixiao_date = date('Y-m-d',$row2['shengchan_date']+$row2['youxiaoqi']*86400);
						}
                        
						
						?>
					<tr class="EvenTableRows">
						
						 <td> <?=$row2['lot_num']?> </td>
						 <td> <?=$shengchan_date?> </td>
						 <td> <?=$expirationDate?> </td>

						 
						<td>
						<input name="a" type="hidden"  value="选择"  class="coupons" rel="<?=$row2['lot_num']?>:<?=$shengchan_date?>:<?=$expirationDate?>">				 
						</td>
					</tr>
					<?php }?>
					 
					 
					</table>
					<br/>
					<div class="centre">
					<?=show_page('?page=',$page,$pages,$count,'&fwValue='.$fwValue.'&cat='.$cat.'&lot_num='.$lot_num.'&sub='.$sub.'');?>
					
					</div>
				</div>
			
		</div>
	</div>
	<div id="FooterDiv">
		<div id="FooterWrapDiv">
			 
		</div>
	</div>
                  
					<div style="display:none">
                         <p><label class="text-info">lot_num</label>　<label id="form_lot_num"></label></p>
                    </div>
                    <div style="display:none">
                         <p><label class="text-info">shengchan_date</label>　<label id="form_shengchan_date"></label></p>
                    </div>
                    <div style="display:none">
                         <p><label class="text-info">shixiao_date</label>　<label id="form_shixiao_date"></label></p>
                    </div>
					
                    <div style="display:none">
                        <p><input type="hidden" name="cat" id="cat" value="<?=$cat?>"/></p>
						<p><input type="hidden" name="fwValue" value="<?=$fwValue?>" id="fwValue"/></p>
                    </div>
  
 
</body>
</html>

