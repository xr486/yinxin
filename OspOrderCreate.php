<?php
if(isset($_GET['data'])){
	 
	 include_once("connect.php"); 
	 $sql = "select a.*,(select tax_mount from tax_set b where a.tax_code=b.tax_name) tax_rate from vendors a where enable_flag='Y' and vendor_code = '".$_GET['data']."'";
	 $result_num = mysql_query($sql, $db);
	 $res = mysql_fetch_assoc($result_num);
	 echo $res['vendor_name'].':'.$res['vendor_address'].':'.$res['vendor_contacts'].':'.$res['currencycode'].':'.$res['tax_code'].':'.$res['tax_rate'].':'.$res['tax_flag'];
	 return ;
 } 	
include('includes/session.inc');
$Title     = _('外协采购单建立');
$ViewTopic = '外协采购单建立';
$BookMark  = '外协采购单建立';

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
 
if (isset($_POST['Go1']) OR isset($_POST['Go2'])) {
	$_POST['PageOffset'] = (isset($_POST['Go1']) ? $_POST['PageOffset1'] : $_POST['PageOffset2']);
	$_POST['Go'] = '';
}
if (!isset($_POST['PageOffset'])) {
	$_POST['PageOffset'] = 1;
} else {
	if ($_POST['PageOffset'] == 0) {
		$_POST['PageOffset'] = 1;
	}
}

if (isset($_POST['UpdateStatus']) ) 
{ 
  $errorflag = 0;
  $sqlv = "select * from vendors where vendor_code = '" . $_POST['vendorcode'] . "'";
	 //echo $sqlv;
		$resultv = DB_query($sqlv, $db);
		$rownum = DB_num_rows($resultv);
		// echo $rownum.'aa';
		if ($rownum==0) {
						$errorflag = 1;
						prnMsg($value.'供应商未选择！',error);
		 }

         if ($_POST['vendorcode']=='') {
						$errorflag = 1;
						prnMsg($value.'供应商未选择！',error);
		 }
		 if ($_POST['vendor_name']=='') {
						$errorflag = 1;
						prnMsg($value.'供应商未选择！',error);
		 }
		 if (strlen($_POST['vendor_name']) < 2 ) {
						$errorflag = 1;
						prnMsg($value.'供应商未选择！',error);
		 }
		  

  foreach ($_POST as $key => $value) 
  {
	 if (substr($key, 0,7)=='line_id') 
    {
     
      $i = substr($key, 7);
	 //   echo $i;
		// echo $_POST['unitprice'.$i];
        if ($_POST['unitprice'.$i]=='') {
						$errorflag = 1;
						prnMsg($value.'未填写单价，请填写单价！',error);
					}
	    if ($_POST['quantity'.$i]=='') {
						$errorflag = 1;
						prnMsg($value.'未填写数量，请填写数量！',error);
		 }
		  
    }
  }  
  $time = time();
  $time2 = $time - 10;

  if ($_SESSION['lastsearchtime'] > $time2) {
      $errorflag = 1;
      prnMsg($value . '重复提交！', error);
  }
  if ($errorflag == 0) 
  {
	    $date1 = date('Ymd');
		$date=substr($date1,2,4) ;
		$sql_num = "select lpad((max( substr( po_num, 7 ) ) +1 ) , 4, 0) po_num   from po_headers_all where substr(po_num,3,4) = '" . $date . "'";
		// echo 	$sql_num;
		$result_num = DB_query($sql_num, $db);
		$rownum = DB_num_rows($result_num);
		while ($v = DB_fetch_array($result_num)) {
			if ($v['po_num'] == null) {
				$OrderNum = 'WX'.$date . '0001';
			} else {
				$OrderNum =  'WX'. $date . $v['po_num'];
			}
		}
       $j=0;
	    $time = time();
    foreach ($_POST as $key => $value)
	{
      if (mb_substr($key,0,7)=='line_id') 
	  {
        $receipt_line_id =mb_substr($key,7);
		$i = $_POST[$key];   
			
       
        if ($_POST['quantity'.$i]>0) 
	    {
		   $lineamount  =$_POST['quantity'.$i] * $_POST['unitprice'.$i] ; 
		 $j=$j+1;

		//  if ($_POST['operation_code'.$i] =='全工序') {
		//  $sql4="update  wip_jobs_all
        //               set po_quantity= po_quantity + '".$_POST['quantity'.$i]."' ,
		// 			  last_updated_by ='".$_SESSION['UserID']."',
		// 			  last_update_date ='".$time."'
		// 			  where wip_entity_name ='".$_POST['wip_entity_name'.$i]."' ";			   
        //        $result = DB_query($sql4, $db);
		//  } else { 
        //    $sql4="update  wip_operation_plan
        //               set po_quantity= po_quantity + '".$_POST['quantity'.$i]."' ,
		// 			  last_updated_by ='".$_SESSION['UserID']."',
		// 			  last_update_date ='".$time."'
		// 			  where wip_entity_name ='".$_POST['wip_entity_name'.$i]."'
		// 			  and operation_seq_num ='".$_POST['operation_seq_num'.$i]."' 
		// 			 and operation_code ='".$_POST['operation_code'.$i]."' ";			   
        //        $result = DB_query($sql4, $db);
		//  }


		 $sql4="update  pr_lines_all
                      set po_num='".$OrderNum."',
					  po_line='".$j."' ,
					  subinventory_code='".$_POST['Subinventory_code']."',
					  prtopo_date='".$time."',
					  prtopo_quantity='".$_POST['quantity'.$i]."', 	
					  prtopo_unitprice='".$_POST['unitprice'.$i]."',
					  prtopo_vendor='".$_POST['vendorcode']."',
					  line_amount ='".$lineamount ."',
					  last_updated_by ='".$_SESSION['UserID']."',
					  last_update_date ='".$time."'
					  where stockid ='".$_POST['stockid'.$i]."' 
					  and line ='".$_POST['pr_line'.$i]."'  and pr_num ='".$_POST['pr_num'.$i]."' 
					    "; 
			//    echo  $sql4 ;
               $result = DB_query($sql4, $db);
               if ($_POST['last_price'.$i]=='') {
			   $_POST['last_price'.$i]=0;
			   }
				$sql = "insert into po_lines_all(po_num,line,line_remark,need_date,subinventory_code,uom,price,last_price,quantity,wip_entity_name,operation_seq_num,operation_code,stockid,line_amount,status,
						creation_date,created_by,last_update_date,last_updated_by)
						values('".$OrderNum."','".$j."','".$_POST['remark'.$i]."','".strtotime($_POST['need_date'.$i])."','".$_POST['Subinventory_code']."','".$_POST['uom'.$i]."','".$_POST['unitprice'.$i]."','".$_POST['last_price'.$i]."',
						'".$_POST['quantity'.$i]."','".$_POST['wip_entity_name'.$i]."','".$_POST['operation_seq_num'.$i]."','".$_POST['operation_code'.$i]."','".$_POST['stockid'.$i]."','".$_POST['line_amount'.$i]."',
						'INPROCESS','".$time."','".$_SESSION['UserID']."','".$time."','".$_SESSION['UserID']."') ";
					$all_amount=$all_amount + $_POST['line_amount'.$i];
					$result = DB_query($sql,$db);
				//	ECHO $sql;
 
			
    }  
		//echo $sql2;        
	    
        
	 
      
    }
  }
    if ($_POST['youhui_amount']=='') {
						$_POST['youhui_amount'] = 0;
		 }
   
		$sql = "insert into po_headers_all (po_num, 
                                              vendor_code,
                                                          status,
                                                          order_date,order_type,
														  need_date,
                                                         po_all_amount,tax_amount,
                                                         youhui_amount,tax_flag,
														 note,currency_code,
                                                         all_line_amount,
                                                         payment_term,
														 creation_date,
														 created_by,
														 last_update_date,
														 last_updated_by,payment_type,
                                                         tax_name,tax_rate, 
														 delivery_coyname,
														 delivery_date)
														 values('".$OrderNum."',
														 '".$_POST['vendorcode']."',
														 'INPROCESS',
														 '". strtotime($_POST['OrderDate']) ."','外协采购',
                                                         '". strtotime($_POST['need_date']) ."', 
														 '".$_POST['po_all_amount']."','".$_POST['tax_amount']."',
														 '".$_POST['youhui_amount']."','".$_POST['tax_flag']."',
														 '".$_POST['Header_Remark']."','".$_POST['currency_code']."',
                                                         '".$_POST['all_line_amount']."',
                                                         '".$_POST['payment_term']."',
														 '".$time."',
														 '".$_SESSION['UserID']."',
														 '".$time."',
														 '".$_SESSION['UserID']."','".$_POST['payment_type']."',
                                                         '".$_POST['tax_name']."','".$_POST['tax_rate']."', 
                                                         '".$_POST['delivery_coyname']."',
                                                         '". strtotime($_POST['delivery_date'])."')";
		$result = DB_query($sql,$db);
		$_SESSION['lastsearchtime'] = $time;
	    DB_Txn_Commit($db);
	    prnMsg('采购单编号'.$OrderNum.'建立成功！',success); 
     
	 header("Location: SucssCreate35.php?OrderNum=$OrderNum");
}
}
 
 //取消的foecast不再显示
if(isset($_POST['Search']) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous']))
{
 

  $sql ="select b.wip_entity_name,b.need_date,b.operation_code,a.primary_item,c.item_name,c.units,b.quantity wait_quantity,b.remark,b.line,b.pr_num
  from wip_jobs_all a,pr_lines_all b,sf_item_no c
where  a.wip_entity_name=b.wip_entity_name and po_num is  null and a.primary_item = c.item_no
	  and a.status_type = '开始' ";
	  if (isset($_POST['wip_entity_name']) and $_POST['wip_entity_name'] != '') 
	  { 
		$sql = $sql . " and a.wip_entity_name " . LIKE . " '%" . $_POST['wip_entity_name'] . "%' ";
	  }
	if (isset($_POST['order_number']) and $_POST['order_number'] != '') 
  { 
	$sql = $sql . " and b.operation_code " . LIKE . " '%" . $_POST['order_number'] . "%' ";
  }
	  
	  if (isset($_POST['item_name']) and $_POST['item_name'] != '') 
	  { 
		$sql = $sql . " and a.item_name " . LIKE . " '%" . $_POST['item_name'] . "%' ";
	  }

	  $sql = $sql ."union select a.order_number,b.need_date,b.operation_code,a.stockid primary_item,d.item_name,d.units,b.quantity wait_quantity,b.remark,b.line,b.pr_num
	  from so_lines_all a,pr_lines_all b,so_headers_all c,sf_item_no d
	where  a.order_number=b.wip_entity_name and po_num is  null and a.stockid = d.item_no
		  and c.wip_status = '开始' ";
		  if (isset($_POST['wip_entity_name']) and $_POST['wip_entity_name'] != '') 
		  { 
			$sql = $sql . " and a.order_number " . LIKE . " '%" . $_POST['wip_entity_name'] . "%' ";
		  }
		if (isset($_POST['order_number']) and $_POST['order_number'] != '') 
	  { 
		$sql = $sql . " and b.operation_code " . LIKE . " '%" . $_POST['order_number'] . "%' ";
	  }
		  
		  if (isset($_POST['item_name']) and $_POST['item_name'] != '') 
		  { 
			$sql = $sql . " and a.item_name " . LIKE . " '%" . $_POST['item_name'] . "%' ";
		  }
	

 
  $result = DB_query($sql,$db);
  if (DB_num_rows($result)==0) 
  {
    unset($result);
    prnMsg(_('没有未找到未转的生产单，请重新输入条件查询！') ,'error');
  }
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>新建订单</title>
<link rel="shortcut icon" href="/favicon.ico"/>
<link rel="icon" href="/favicon.ico"/>
<meta http-equiv="Content-Type" content="application/html; charset=utf-8"/>
<link href="/css/xenos/default.css" rel="stylesheet" type="text/css"/>
<script type="text/javascript" src ="./JXC/javascripts/miscfunctions.js"></script>
<script type="text/javascript" src ="./JXC/javascripts/wdatepicker.js"></script>
<script type="text/javascript">var basepath='./JXC/statics/base/images';</script>
<script type="text/javascript" src="./JXC/statics/base/js/metvar.js"></script>
<script type="text/javascript" src="./JXC/statics/base/js/jQuery1.7.2.js"></script>
<script type="text/javascript" src="./JXC/statics/base/js/uploadify/jquery.uploadify.v2.1.4.min.js"></script>
<script type="text/javascript" src="./JXC/statics/base/js/iframes.js"></script>
<script type="text/javascript" src="./JXC/statics/base/js/cookie.js"></script>
<script type="text/javascript" src="./JXC/statics/base/js/jquery.livequery.js"></script>

<link rel="stylesheet" href="jquery.ui.autocomplete.css">
<script type="text/javascript" src="ui/jquery.ui.core.js"></script>
<script type="text/javascript" src="ui/jquery.ui.widget.js"></script>
<script type="text/javascript" src="ui/jquery.ui.position.js"></script>
<script type="text/javascript" src="ui/jquery.ui.autocomplete.js"></script>

<script src="./JXC/javascript/jquery-1.7.2.min.js"></script>
<script src="./JXC/javascript/lhgdialog.min.js?self=true&skin=chrome"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
<script src="./javascript/bootstrap.min.js"></script>

<script type="text/javascript">
/*ajax执行*/
var lang = 'cn';
var metimgurl='./JXC/statics/base/images/';
var depth='';
$(document).ready(function(){
	ifreme_methei();
});
</script>
<script type="text/javascript">
function metreturn(url){
	if(url){
		location.href=url;
	}else if($.browser.msie){
		history.go(-1);
	}else{
		history.go(-1);
	}
} 


		function addsave()
		{

			var v = $('#idcount').val();
			$("#purchase_table_"+v).css("display","");
			var c = parseInt(v) + 1;
			$('#idcount').val(c);
		}


	</script>
</head>
<body>
 <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST">
 <input type="hidden" name="time" value="<?=$time?>">
<?php
 

echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('外协采购单建立') . '</p>';

  if (!isset($_POST['schedule_payment_date'])) {
      $_POST['schedule_payment_date'] = Date('Y-m-d');
     } 
	 if (!isset($_POST['need_date'])) {
      
	  $_POST['need_date'] =Date('Y-m-d', mktime(0, 0, 0, date("m"), date("d") + 1, date("Y")));
     } 
   	 if (!isset($_POST['OrderDate'])) {
      $_POST['OrderDate'] = Date('Y-m-d');
     }
	 if (!isset($_POST['delivery_date'])) {
      $_POST['delivery_date'] = Date('Y-m-d');
     }  
	 if (!isset($_POST['dangqian_date'])) {
      $_POST['dangqian_date'] = Date('Y-m-d');
     }  
	 
 
?>
 <input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
					<table class="selection">
         <div class="text-nav">
					<div class="text-nav-1 ">
						<div>供应商代码：</div>
							<input  type="text" required="required" name="vendorcode" id="text_slect_vendor" value="<?=$_POST['vendorcode']?>" size="16" maxlength="25" onblur="sel()"/>
								<image class="select_img" src="img/search.png" id="btn_slect_vendor"/></div>
			 
                         
					
                <div class="text-nav-2 ">
						<div>供应商名称：</div>
							<input readonly="readonly"   type="text"  name="vendor_name" id="text_slect_name" value="<?=$_POST['vendor_name']?>" size="55" maxlength="46"  /> 
							</div> 
				
		
	<div class="text-nav-1 required"> <div>是否含税</div>
			<select name="tax_flag" onchange="checkalla()" id="text_slect_tax_flag">
				<?php
					$sql5 = "select type_code,type_name from sys_type";
					$result5 = DB_query($sql5,$db);
					while ($v5 = DB_fetch_array($result5)) {
						if ($v5['type_code']==$_POST['tax_flag']) {
				?>
					<option value="<?=$v5['type_code']?>" selected="selected"><?=$v5['type_name']?></option>
				<?php }else{?>
				<option value="<?=$v5['type_code']?>"><?=$v5['type_name']?></option>
				<?php		}
					}
				?>
			</select>
				</div> 	
				<div class="text-nav-1 required">
						<div>含税金额：</div>
							<input  type="text"  readonly="readonly" class="number" name="po_all_amount" id="po_all_amount" value="<?=$_POST['po_all_amount']?>" size="10" maxlength="10"/></div>
		<div class="text-nav-1 ">
						<div>未税金额：</div>
							<input  type="text" readonly="readonly" class="number" name="all_line_amount" id="all_line_amount" value="<?=$_POST['all_line_amount']?>" size="10" maxlength="10"/></div>    
		<div class="text-nav-1 ">
						<div>税金：</div>
							<input  onblur="check55()" type="text" readonly="readonly" class="number" name="tax_amount" id="tax_amount" value="<?=$_POST['tax_amount']?>" size="10" maxlength="10"/></div> 
							
		<div class="text-nav-1 required">
						<div>采购日期：</div>
							<input type="text" onblur="checkdate()" id="OrderDate" name="OrderDate" maxlength="15" size="12" required="required" value="<?=$_POST['OrderDate']?>" onfocus="WdatePicker() "></div>  
							
					<div class="text-nav-1 required">
						<div>需求日期：</div>
							<input type="text" onblur="checkdate()" id="need_date" name="need_date" maxlength="15" size="12"  value="<?=$_POST['need_date']?>" onfocus="WdatePicker() "></div>
		<div class="text-nav-1 required">
						<div>币别：</div>
		<input readonly="readonly" type="text"   name="currency_code" id="text_slect_currency_code" value="<?=$_POST['currency_code']?>" size="10" maxlength="10"/></div>
       	<div  class="text-nav-1 required">
			<div>税别</div> 		  
			 <input type="text" readonly="readonly" onblur="check55()" name="tax_name" id="text_slect_tax_name" value="<?=$_POST['tax_name']?>" size="10" maxlength="100"/>
					 <image class="select_img" src="img/search.png" id="btn_slect_tax_name"/>
	</div>   
	<div  class="text-nav-1 required">
			<div>税率</div> 		  
			 <input type="text" readonly="readonly" onblur="check55()" name="tax_rate" id="text_slect_tax_rate" value="<?=$_POST['tax_rate']?>" size="10" maxlength="100"/>
 
	</div>                        
		
		
		 
		<div class="text-nav-1 ">
						<div>采购单备注：</div>
							<input type="text"  maxlength="200" size="50" name="Header_Remark"  value="<?=$_POST['Header_Remark']?>" /> </div>							
		<div class="text-nav-1 ">
						<div>付款条件：</div>
						<input type="text"   name="payment_term" id="text_slect_term_name" value="<?=$_POST['payment_term']?>" size="10" maxlength="100"/>
					 <image class="select_img" src="img/search.png" id="btn_slect_term_name"/></div>
		<div class="text-nav-1 ">
						<div>付款方式：</div>
		                  <input type="text"   name="payment_type" id="text_slect_payment_type" value="<?=$_POST['payment_type']?>" size="10" maxlength="10"/></div>
						  
		 
				<div class="text-nav-1 ">
					<div>交货日期:</div>
					<input type="text" onblur="checkdate()" id="delivery_date" name="delivery_date" maxlength="15" size="12"  value="<?=$_POST['delivery_date']?>" onfocus="WdatePicker() "></div>
					</div>
						
                	</table>	
				<table class="selection">
              <div class="text-nav">
			  <div class="text-nav-1 "><div>生产号码:</div>
					<input type="text"  maxlength="200" size="50" name="wip_entity_name"  value="<?=$_POST['wip_entity_name']?>" />
				</div>	
				 <div class="text-nav-1 "><div>工序名称:</div>
					<input type="text"  maxlength="200" size="50" name="order_number"  value="<?=$_POST['order_number']?>" />
				</div>
				<div class="text-nav-1 "><div>料号:</div>
					<input type="text"   name="item_name"  value="<?=$_POST['item_name']?>" />
					<input type="hidden"   id="dangqian_date"  name="dangqian_date"  value="<?=$_POST['dangqian_date']?>" />
					<input  type="hidden"  class="number" name="youhui_amount" id="youhui_amount" value="<?=$_POST['youhui_amount']?>" size="10" maxlength="10"   onblur="check55()"/>
				</div>	
				 
				
				  
                </div>	</table>	
              
             

<?php
echo '</table><div class="centre"><input type="submit" name="Search" value="查询需转采购单明细"></div>';

if (isset($_POST['Search']) and isset($result) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous']))
{
  $ListCount = DB_num_rows($result);
  $ListPageMax = ceil($ListCount / 35 );
  
   if (isset($_POST['Next'])) {
        if ($_POST['PageOffset'] < $ListPageMax) {
            $_POST['PageOffset'] = $_POST['PageOffset'] + 1;
        }
    }
    if (isset($_POST['Previous'])) {
        if ($_POST['PageOffset'] > 1) {
            $_POST['PageOffset'] = $_POST['PageOffset'] - 1;
        }
    }
 
  echo '<input type="hidden" name="PageOffset" value="' . $_POST['PageOffset'] . '" />';
  if ($ListPageMax > 1) 
  {
   echo '<br /><div class="centre">&nbsp;&nbsp;' . _('第') . '' . $_POST['PageOffset'] . ' ' . _('页，共') . ' ' . $ListPageMax . ' ' . _('页') . '. ' . _('转到页') . ': ';
    echo '<select name="PageOffset1">';
    $ListPage = 1;
    while ($ListPage <= $ListPageMax) 
	{
      if ($ListPage == $_POST['PageOffset']) 
	  {
        echo '<option value="' . $ListPage . '" selected="selected">' . $ListPage . '</option>';
      } else 
	  {
        echo '<option value="' . $ListPage . '">' . $ListPage . '</option>';
      }
      $ListPage++;
    }
	echo '</select>
	 <input type="submit" name="Go1" value="' . _('转到') . '" />
        <input type="submit" name="Previous" value="' . _('上一页') . '" />
        <input type="submit" name="Next" value="' . _('下一页') . '" />';
	echo '</div>';
  }
  echo '<div class="centre"> 
                        <p id="Prompt" style="color: red;font-size: 20px"></p>
                    </div> <br /><div class="text-nav-table">
  <table cellpadding="2" class="selection">';
  echo '<tr>
         <th width =40 >' . '选择' . '</th>
        <th class="ascending"  >' . _('需求日期') . '</th>	
        <th class="ascending"  >' . _('生产单号') . '</th>	
   	
        <th class="ascending"  >' . _('工序名称') . '</th>	
        <th class="ascending"  >' . _('料号') . '</th>	
        <th  >' . _('料号名称') . '</th>				 
	    <th  >' . _('单位') . '</th>
	    <th   width = 60>' . _('待下单') . '</th> 
	    <th   width = 60>' . _('本次下单') . '</th> 
	    <th   width = 60>' . _('单价') . '</th>  
	    <th  width = 20>'  . _('需求日期') . '</th>
	    <th  width = 20>'  . _('金额') . '</th>
	
	    <th  width = 60>'  . _('备注') . '</th> 
       
	    
       </tr>';  
  $k = 0; //row counter to determine background colour
  $RowIndex = 0;
 
  if (DB_num_rows($result) <> 0) 
  {
    DB_data_seek($result, ($_POST['PageOffset'] - 1) * 35 );
    $i = 1; //counter for input controls
    while (($myrow = DB_fetch_array($result)) AND ($RowIndex <> 35 )) 
	{
      if ($k == 1) 
	  {
        echo '<tr class="EvenTableRows">';
        $k = 0;
      } else 
	  {
        echo '<tr class="OddTableRows">';
        $k = 1;
      }

	   
	   
	//var_dump($myrow);
      $_SESSION['status_id' . $identifier]=100;  
	  $line_amount=$myrow['quantity']*$myrow['last_price'];
	  $need_date=$myrow['need_date'] -  86400 - 86400 ;
	   
      echo '   <td><input type="checkbox" onclick="OncheckBox(this)" id="status'.$i.'" checked="false" name="line_id'.$i.'" value="'.$i.'" /></td>
	   <td>' . date('Y-m-d',$myrow['need_date']) . '</td>
	  <td>' . $myrow['wip_entity_name'] . '</td>
	
			<td>' . $myrow['operation_code'] . '</td>
			<td>' . $myrow['primary_item'] . '</td>
			<td>' . $myrow['item_name'] . '</td>
            <td>' . $myrow['units']  . '</td>
            <td>' . $myrow['wait_quantity']  . '</td>
		 
		';?>
       <?php
	   echo ' <td><input id="quantity' .$i.'" onkeyup=checkqty('.$i.') onblur="checkalla()" style="background-color:yellow" type="text"  name="quantity'.$i.'" class="number" size="6"  value="' . $myrow['wait_quantity']  . '" /></td> ';
      echo ' <td><input id="text_slect_unit_price' .$i.'" onkeyup=checkqty('.$i.') onblur="checkalla()" style="background-color:yellow" type="text" name="unitprice'.$i.'" class="number" size="6" value="' . $myrow['last_price']  . '" /></td> ';
	  echo ' <td><input type="text" id="need_date' .$i.'" onblur=checkqty('.$i.') onfocus="WdatePicker()" name="need_date'.$i.'" size="8"  value="' .date('Y-m-d',$need_date) . '" /></td> ';
 
	  echo ' <td><input type="text" id="lineamount' .$i.'" readonly="readonly" name="line_amount'.$i.'" class="number" size="8"  value="' . $line_amount  . '" /></td>
	  
          '; 
      echo ' <td><input type="text" name="remark'.$i.'"   size="10" value="' .$myrow['remark']. '" /></td> ';	
	  echo '
	  <input type="hidden" name="wip_entity_name'.$i.'"   size="10" value="' .$myrow['wip_entity_name']. '" />
	  <input type="hidden" name="operation_seq_num'.$i.'"   size="10" value="' .$myrow['operation_seq_num']. '" />
	  <input type="hidden" name="operation_code'.$i.'"   size="10" value="' .$myrow['operation_code']. '" /> 
	  <input type="hidden" name="uom'.$i.'"   size="10" value="' .$myrow['uom']. '" /> 
	  <input type="hidden" name="stockid'.$i.'"   size="10" value="' .$myrow['primary_item']. '" /> 
	  <input type="hidden" name="wait_quantity'.$i.'" id="wait_quantity'.$i.'"  size="10" value="' .$myrow['wait_quantity']. '" /> 
	  <input type="hidden" name="need_quantity'.$i.'"   size="10" value="' .$myrow['quantity']. '" /> 
	  <input type="hidden" name="last_price'.$i.'" id="text_slect_last_price'.$i.'"  size="10" value="' .$myrow['last_price']. '" /> 
	  <input type="hidden" name="pr_line'.$i.'"   size="10" value="' .$myrow['line']. '" /></td>   
	  <input type="hidden" name="pr_num'.$i.'"   size="10" value="' .$myrow['pr_num']. '" /></td>   ';
      echo  '</tr>';
      $i++;
      $RowIndex++;

    }   
    
	 
	
	//end loop through customers
	echo '</table></div>';
	echo '<input type="hidden" name="JustSelectedACustomer" value="Yes" />';
  }



  if (isset($ListPageMax) AND $ListPageMax > 1) 
  {
  echo '<br /><div class="centre">&nbsp;&nbsp;' . _('第') . '' . $_POST['PageOffset'] . ' ' . _('页，共') . ' ' . $ListPageMax . ' ' . _('页') . '. ' . _('转到页') . ': ';
        echo '<select name="PageOffset2">';
    $ListPage = 1;
    while ($ListPage <= $ListPageMax) 
	{
      if ($ListPage == $_POST['PageOffset']) 
	  {
        echo '<option value="' . $ListPage . '" selected="selected">' . $ListPage . '</option>';
      } //$ListPage == $_POST['PageOffset']
      else 
	  {
        echo '<option value="' . $ListPage . '">' . $ListPage . '</option>';
      }
      $ListPage++;
    } //$ListPage <= $ListPageMax
	echo '</select>
			  <input type="submit" name="Go2" value="' . _('转到') . '" />
        <input type="submit" name="Previous" value="' . _('上一页') . '" />
        <input type="submit" name="Next" value="' . _('下一页') . '" />';
	echo '</div>';
  }//end if results to show

echo '<table border="0"  ><tr><td colspan="11"><p><input type="checkbox" name="selectall" onclick="checkall(this.form);"/>全选/取消全选</p></td>
	<td ><input type="hidden" name="flag" value="<?=$i-1?>" size="15" maxlength="45"/></td></tr>  </table>';

  echo '<a name="end"></a><br /><div class="centre"><input type="submit" name="UpdateStatus"   value="转单确认" />
  </div> ';
}
?>

<script type="text/javascript">
  


	function sel(){
		var name=$('#text_slect_vendor').val()
		$.get("","data="+name,function(res){
			name = res.split(":")		  
				$("#text_slect_name").val(name[0]) 
				$("#text_slect_address").val(name[1])
				$("#text_slect_contacts").val(name[2])
				$("#text_slect_currency_code").val(name[3])
                $("#text_slect_tax_name").val(name[4])
                $("#text_slect_tax_rate").val(name[5])
                $("#text_slect_tax_flag").val(name[6])
   
		})	
	}  
 

function checkdate()  { 
		 var need_date=document.getElementById("need_date").value; 
		 alert(need_date);
		 var delivery_date=document.getElementById("delivery_date").value;
		 alert(delivery_date);
		 var OrderDate=document.getElementById("OrderDate").value;
		 alert(OrderDate);
		 var dangqian_date=document.getElementById("dangqian_date").value;
		  alert(dangqian_date);
			 if ( need_date <  dangqian_date)
		     { 
               document.getElementById("Prompt").innerHTML="需求日期不可以小于当前日期！";
			   document.getElementById("need_date").value=dangqian_date;	
               document.getElementById("need_date").focus(); 
		     } else if ( delivery_date <  dangqian_date)
		     { 
                document.getElementById("Prompt").innerHTML="交货日期不可以小于当前日期！";
			   document.getElementById("delivery_date").value=dangqian_date;	
               document.getElementById("delivery_date").focus(); 
		     } else if ( OrderDate <  dangqian_date)
		     { 
               document.getElementById("Prompt").innerHTML="采购日期不可以小于当前日期！";
			   document.getElementById("OrderDate").value=dangqian_date;	
               document.getElementById("OrderDate").focus(); 
		     }  else {
            document.getElementById("Prompt").innerHTML="";
        }
}

function  check55(){
	   
		   var tax_rate=document.getElementById("text_slect_tax_rate").value;
		   var tax_flag=document.getElementById("text_slect_tax_flag").value;
		   var all_rate = Number(1) + Number(tax_rate);
         if (tax_flag=='N')
         {  var all_line_amount=document.getElementById("all_line_amount").value;
		  var tax_amount=  Math.round(Number(tax_rate)*Number(all_line_amount) *100)/100;  
		  var han_tax_amount = Number(tax_amount) + Number(all_line_amount);
		  document.getElementById("po_all_amount").value=Math.round(Number(po_all_amount)*100)/100; 
         } else {
		  var po_all_amount=document.getElementById("po_all_amount").value;
		  var tax_amount=   Number(po_all_amount)/ Number(all_rate) ;  
		  var no_tax_amount = Number(po_all_amount) - Number(tax_amount);
		  document.getElementById("all_line_amount").value=Math.round(Number(no_tax_amount)*100)/100; 
		 }
		  
		  
        
		document.getElementById("tax_amount").value=Math.round(Number(tax_amount)*100)/100; 
        
      var a=document.getElementById("po_all_amount").value;
        var b=document.getElementById("youhui_amount").value;  
      if(parseInt(b)>parseInt(a)){
            document.getElementById("Prompt").innerHTML="优惠金额"+b+"不可以大于总金额！"+a;
            document.getElementById("youhui_amount").value="";
            document.getElementById("youhui_amount").focus();
        } 
        else {
            document.getElementById("Prompt").innerHTML="";
        }
     } 

function checkqty(s)  {
         var wait_quantity=document.getElementById("wait_quantity"+s).value;
		 var shuliang=document.getElementById("quantity"+s).value;
		 var need_date=document.getElementById("need_date"+s).value;
		 var price=document.getElementById("text_slect_unit_price"+s).value;
		 var dangqian_date=document.getElementById("dangqian_date").value;
			  if(parseFloat(shuliang) > parseFloat(wait_quantity)){
               document.getElementById("Prompt").innerHTML="数量不可以大于待转量！";
			   document.getElementById("quantity"+s).value=null;	
               document.getElementById("quantity"+s).focus();		  
		     }  else if (parseFloat(shuliang)<=0)
		     { 
               document.getElementById("Prompt").innerHTML="数量必须大于0！";
			   document.getElementById("quantity"+s).value=null;	
               document.getElementById("quantity"+s).focus(); 
		     } else if (parseFloat(price)<0)
		     { 
               document.getElementById("Prompt").innerHTML="价格不能小于0！";
			   document.getElementById("text_slect_unit_price"+s).value=null;	
               document.getElementById("text_slect_unit_price"+s).focus(); 
		     } else {
            document.getElementById("Prompt").innerHTML="";
        }

		/*else if ( need_date <  dangqian_date)
		     { 
               document.getElementById("Prompt").innerHTML="需求日期不可以小于当前日期！";
			   document.getElementById("need_date"+s).value=dangqian_date;	
               document.getElementById("need_date"+s).focus(); 
		     }
			 */
}


 function checkalla(){                               
                                var allamount=0; 
                            var youhui_amount=document.getElementById("youhui_amount").value;
							var tax_rate=document.getElementById("text_slect_tax_rate").value;
							var tax_flag=document.getElementById("text_slect_tax_flag").value;
							var all_rate = Number(1) + Number(tax_rate); 	
                                for(var i=1 ; i < 200; i++){   
									if (document.getElementById("lineamount" + i)==null)  {
									p=0;
										}
									else {	 
                                   var shuliang=document.getElementById("quantity"+i).value;
		                           var danjia=document.getElementById("text_slect_unit_price"+i).value;
		                           var last_price=document.getElementById("text_slect_last_price"+i).value;
								   var ischecked=document.getElementById("status" + i).checked;
								   if(shuliang==""){
			                         shuliang=0;
		                               }
		                           if(danjia==""){
		                           	danjia=0;
		                           }
								   if (shuliang>0   && ischecked )
								   {
									  
									   document.getElementById("lineamount"+i).value=Math.round(Number(shuliang)* Number(danjia)*100)/100 ; 
									    
                                   var lineamount=document.getElementById("lineamount"+i).value;
									   allamount=Number(allamount) + Number(lineamount);

								   }
								   if ( parseFloat(danjia)> parseFloat(last_price)  ) {
									document.getElementById("lineamount"+i).style.color = "red";
									document.getElementById("text_slect_unit_price"+i).style.color = "red";
									}

		                            
								    }
								}
								
        if (tax_flag=='N')
		{ 
		  var tax_amount= Math.round(Number(allamount)*Number(tax_rate) *100)/100;
		  var no_tax_amount=allamount;
		  var han_tax_amount=Number(tax_amount) + Number(allamount);

		} else {
		  var no_tax_amount= Math.round(Number(allamount) / Number(all_rate) *100)/100;
		  var tax_amount= Number(allamount) - Number(no_tax_amount);
		  var han_tax_amount=allamount;
		}
		
         
        document.getElementById("all_line_amount").value=Math.round(Number(no_tax_amount)*100)/100; 
		document.getElementById("tax_amount").value=Math.round(Number(tax_amount)*100)/100; 
		document.getElementById("po_all_amount").value=Math.round(Number(han_tax_amount)*100)/100;
        
        var a=document.getElementById("po_all_amount").value;
        var b=document.getElementById("youhui_amount").value;  
      if(parseInt(b)>parseInt(a)){
            document.getElementById("Prompt").innerHTML="优惠金额"+b+"不可以大于总金额！"+a;
            document.getElementById("youhui_amount").value="";
            document.getElementById("youhui_amount").focus();
        } 
        else {
            document.getElementById("Prompt").innerHTML="";
        }
        
        
     }


	
// $(function(){
// 		$( "#text_slect_vendor" ).autocomplete({
// 			source: "autosearchvendor.php",
// 			minLength: 2,
// 			autoFocus: true
// 		});
// 	});
 
 function OncheckBox(index){
      var allamount=0; 
                            var youhui_amount=document.getElementById("youhui_amount").value;
							var tax_rate=document.getElementById("text_slect_tax_rate").value;
                                for(var i=1 ; i < 200; i++){   
									if (document.getElementById("lineamount" + i)==null)  {
									p=0;
										}
									else {										 
								  
                                   
								   var shuliang=document.getElementById("quantity"+i).value;
		                           var danjia=document.getElementById("text_slect_unit_price"+i).value;
		                           var last_price=document.getElementById("text_slect_last_price"+i).value;
								   var ischecked=document.getElementById("status" + i).checked;
								   if(shuliang==""){
			                         shuliang=0;
		                               }
		                           if(danjia==""){
		                           	danjia=0;
		                           }
								   if (shuliang>0   && ischecked )
								   {
									   document.getElementById("lineamount"+i).value=Math.round(Number(shuliang)* Number(danjia)*100)/100 ;
									    
                                   var lineamount=document.getElementById("lineamount"+i).value;
									   allamount=Number(allamount) + Number(lineamount);

								   }
								   if ( parseFloat(danjia)> parseFloat(last_price)  ) {
									document.getElementById("lineamount"+i).style.color = "red";
									document.getElementById("text_slect_unit_price"+i).style.color = "red";
									}

		                            
								    }
								}
								
         

		document.getElementById("po_all_amount").value=Math.round(Number(allamount)*100)/100;
		var tax_rate = Number(1) + Number(tax_rate);
        var all_line_amount= Math.round(Number(allamount)/Number(tax_rate) *100)/100; 
		var tax_amount=Number(allamount) - Number(all_line_amount);
        document.getElementById("all_line_amount").value=Math.round(Number(all_line_amount)*100)/100; 
		document.getElementById("tax_amount").value=Math.round(Number(tax_amount)*100)/100; 
        
        var a=document.getElementById("po_all_amount").value;
        var b=document.getElementById("youhui_amount").value;  
      if(parseInt(b)>parseInt(a)){
            document.getElementById("Prompt").innerHTML="优惠金额"+b+"不可以大于总金额！"+a;
            document.getElementById("youhui_amount").value="";
            document.getElementById("youhui_amount").focus();
        } 
        else {
            document.getElementById("Prompt").innerHTML="";
        }
        
		 


}


	  
 </script>

<script type="text/javascript">
function checkall(thisform){for(var i=0;i<thisform.elements.length;i++){if(thisform.elements[i].type=="checkbox"&&thisform.elements[i].checked==false&&thisform.elements[i].name!="selectall"){thisform.elements[i].checked=true;}else if(thisform.elements[i].type=="checkbox"&&thisform.elements[i].checked==true&&thisform.elements[i].name!="selectall"){thisform.elements[i].checked=false;}} }

    $(document).ready(function(){

        $('.divToilet table tr td a').click(function(){
            $(this).parent('td').toggleClass('highlight');
            if(!($(this).parent('td').hasClass('highlight'))) {
                $(this).next().val('0');
            }else {
                $(this).next().val('1');
            }
        });
      

	    
		
     
       $('#btn_slect_vendor').dialog({
            title:'选择供应商',
            width: '950px',
            height: 470,
            content:'url:BtnSearchVendor.php?fwValue=&cat=buliao',
            init:function(){
                this.content.document.getElementById('cat').value = 'buliao';
                this.content.document.getElementById('fwValue').value = '';
            }
        });
       $('#btn_slect_vendor_a').dialog({
            title:'选择供应商',
            width: '950px',
            height: 470,
            content:'url:BtnSearchVendor.php?fwValue=&cat=buliao',
            init:function(){
                this.content.document.getElementById('cat').value = 'buliao';
                this.content.document.getElementById('fwValue').value = '';
            }
        });

        $('#btn_slect_item_no').dialog({
            title:'选择料号',
            width: '850px',
            height: 470,
            content:'url:BtnSearchitem_no.php?fwValue=&cat=buliao',
            init:function(){
                this.content.document.getElementById('cat').value = 'buliao';
                this.content.document.getElementById('fwValue').value = '';
            }
        });

 $('#btn_slect_term_name').dialog({
            title:'选择付款条件',
            width: '550px',
            height: 470,
            content:'url:BtnSearchterm.php?fwValue=&cat=buliao',
            init:function(){
			    this.content.document.getElementById('cat').value = 'buliao';
                this.content.document.getElementById('fwValue').value = '';
            }
        });

		 $('#btn_slect_tax_name').dialog({
            title:'选择税别',
            width: '550px',
            height: 470,
            content:'url:BtnSearchtax.php?fwValue=&cat=buliao',
            init:function(){
			    this.content.document.getElementById('cat').value = 'buliao';
                this.content.document.getElementById('fwValue').value = '';
            }
        });

        //Function to get URL arguments
        function getRequest() {
            var url = location.search; //获取url中"?"符后的字串
            var theRequest = new Object();
            if (url.indexOf("?") != -1) {
                var str = url.substr(1);
                strs = str.split("&");
                for(var i = 0; i < strs.length; i ++) {
                    theRequest[strs[i].split("=")[0]]=(strs[i].split("=")[1]);
                }
            }
            return theRequest;
        }
          
 
    });


 

</script>

  <?php
  echo '</div>
      </form>';
include('includes/footer.inc');
?>

