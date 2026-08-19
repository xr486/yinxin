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
$Title     = _('销售单转采购单');
$ViewTopic = '销售单转采购单';
$BookMark  = '销售单转采购单';

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
         if ($_POST['vendorcode']=='') {
						$errorflag = 1;
						prnMsg($value.'供应商未选择！',error);
		 }
		 if ($_POST['vendorname']=='') {
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
		 if ($_POST['suoding_flag'.$i]=='Y') {
			 $errorflag = 1;
			 prnMsg($_POST['item_no'.$i].$_POST['suoding_remark'.$i].'已被锁定,请与工程联系！',error);
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
	  $date = date('Ymd');
		$sql_num = "select 	(
		CASE WHEN substr(max(po_num) ,-2,1) = 0 THEN
			RIGHT (
				'100' + (
					max(substr(po_num ,- 1)) + 1
				),
				2
			)
		ELSE
			substr(max(po_num),-2,2) + 1
		END
        ) po_num from po_headers_all where substr(po_num,-10,8) = '" . $date . "'";
		$result_num = DB_query($sql_num, $db);
		$rownum = DB_num_rows($result_num);
		while ($v = DB_fetch_array($result_num)) {
			if ($v['po_num'] == null) {
				$OrderNum = 'PO'.$date . '01';
			} else {
				$OrderNum =  'PO'. $date . $v['po_num'];
			}
		}
       $j=0;
    foreach ($_POST as $key => $value)
	{
      if (mb_substr($key,0,7)=='line_id') 
	  {
        $receipt_line_id =mb_substr($key,7);
		$i = $_POST[$key];   
		
		
        $time = strtotime(Date('Y-m-d H:i:s'));
        if ($_POST['quantity'.$i]>0) 
	    {
		   $lineamount  =$_POST['quantity'.$i] * $_POST['unitprice'.$i] ; 
		 $j=$j+1;

 
 
  


           $sql4="update  so_lines_all
                      set change_pr='Y', 
					  last_updated_by ='".$_SESSION['UserID']."',
					  last_update_date ='".$time."'
					  where  line ='".$_POST['so_line']."'  and order_number ='".$_POST['order_number']."' 
					    "; 
			//    echo  $sql4 ;  	 	
               $result = DB_query($sql4, $db);
 	  
               if ($_POST['last_price'.$i]=='') {  
			   $_POST['last_price'.$i]=0;
			   }
					$sql = "insert into po_lines_all(po_num,line,line_remark,need_date,subinventory_code,uom,price,last_price,quantity,so_issue_qty,stockid,line_amount,status,
						creation_date,created_by,last_update_date,last_updated_by)
						values('".$OrderNum."','".$j."','".$_POST['remark'.$i]."','".strtotime($_POST['need_date'.$i])."','".$_POST['Subinventory_code']."','".$_POST['uom'.$i]."','".$_POST['unitprice'.$i]."','".$_POST['last_price'.$i]."',
						'".$_POST['quantity'.$i]."','".$_POST['need_quantity'.$i]."','".$_POST['item_no'.$i]."','".$_POST['line_amount'.$i]."',
						'待签核','".$time."','".$_SESSION['UserID']."','".$time."','".$_SESSION['UserID']."') ";
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
                                                         youhui_amount,
														 note,currency_code,
                                                         all_line_amount,
                                                         payment_term,
														 creation_date,
														 created_by,
														 last_update_date,
														 last_updated_by,payment_type,
                                                         tax_name,tax_rate,
														 delivery_date)
														 values('".$OrderNum."',
														 '".$_POST['vendorcode']."',
														 '待签核',
														 '". strtotime($_POST['OrderDate']) ."','".$_POST['order_type']."',
                                                         '". strtotime($_POST['ScheduleDate']) ."',
														 '".$_POST['po_all_amount']."','".$_POST['tax_amount']."',
														 '".$_POST['youhui_amount']."',
														 '".$_POST['Header_Remark']."','".$_POST['currency_code']."',
                                                         '".$_POST['all_line_amount']."',
                                                         '".$_POST['payments']."',
														 '".$time."',
														 '".$_SESSION['UserID']."',
														 '".$time."',
														 '".$_SESSION['UserID']."','".$_POST['payment_type']."',
                                                         '".$_POST['tax_name']."','".$_POST['tax_rate']."',
                                                         '". strtotime($_POST['delivery_date'])."')";
		$result = DB_query($sql,$db);
	    DB_Txn_Commit($db);
		$_SESSION['lastsearchtime'] = $time;
	    prnMsg('采购单编号'.$OrderNum.'建立成功！',success);
	  //  echo "<script>location.href='index.php';</script>";
     
	 header("Location: SucssCreate34.php?OrderNum=".$OrderNum);
}
}
 
 //取消的foecast不再显示
if(isset($_POST['Search']) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous']))
{
 

  $sql ="select b.item_no,b.item_name,b.item_desc,b.units, c.component_quantity,ifnull((select sum(quantity-so_issue_qty) from po_lines_all pla,po_headers_all pha where pla.po_num=pha.po_num and pla.stockid=component_item and pha.status='APPROVED' ),0) onhand
	from  bom_lines_all c,sf_item_no b
				where  c.assembly_item_no= '" .$_POST['stockid'] . "' 
				and item_no=component_item
				and  disable_date=0    
		  ";
 
  
  

  // echo $sql;
  $result = DB_query($sql,$db);
  if (DB_num_rows($result)==0) 
  {
    unset($result);
    prnMsg(_('没有未找到需要转的请购单，请重新输入条件查询！') ,'error');
  }
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>销售单转采购单</title>
<link rel="shortcut icon" href="/favicon.ico"/>
<link rel="icon" href="/favicon.ico"/>
<meta http-equiv="Content-Type" content="application/html; charset=utf-8"/>
<link href="/css/xenos/default.css" rel="stylesheet" type="text/css"/>
<script type="text/javascript" src ="/javascripts/miscfunctions.js"></script>
<script type="text/javascript" src ="/javascripts/wdatepicker.js"></script>
<script type="text/javascript">var basepath='/statics/base/images';</script>
<script type="text/javascript" src="/statics/base/js/metvar.js"></script>
<!-- <script type="text/javascript" src="/statics/base/js/jQuery1.7.2.js"></script> -->
<script type="text/javascript" src="/statics/base/js/uploadify/jquery.uploadify.v2.1.4.min.js"></script>
<script type="text/javascript" src="/statics/base/js/iframes.js"></script>
<script type="text/javascript" src="/statics/base/js/cookie.js"></script>
<script type="text/javascript" src="/statics/base/js/jquery.livequery.js"></script>
<link rel="stylesheet" href="jquery.ui.autocomplete.css">
<script type="text/javascript" src="ui/jquery.ui.core.js"></script>
<script type="text/javascript" src="ui/jquery.ui.widget.js"></script>
<script type="text/javascript" src="ui/jquery.ui.position.js"></script>
<script type="text/javascript" src="ui/jquery.ui.autocomplete.js"></script>
<!-- <script src="/javascript/jquery-1.7.2.min.js"></script> -->
<script src="/javascript/lhgdialog.min.js?self=true&skin=chrome"></script>
<script src="/javascript/bootstrap.min.js"></script>
 
<script type="text/javascript">
/*ajax执行*/
var lang = 'cn';
var metimgurl='/statics/base/images/';
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
 
    var v = $('#ilot_numberount').val();
    $("#purchase_table_"+v).css("display","");
    var c = parseInt(v) + 1;
    $('#ilot_numberount').val(c);     
}

 



</script>
</head>
<body>
 <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST">
 <input type="hidden" name="time" value="<?=$time?>">
<?php
 

echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('销售单转采购单') . '</p>';


	 if (!isset($_POST['ScheduleDate'])) {
      $_POST['ScheduleDate'] = Date('Y-m-d');
     } 
   	 if (!isset($_POST['OrderDate'])) {
      $_POST['OrderDate'] = Date('Y-m-d');
     }
	 if (!isset($_POST['delivery_date'])) {
      $_POST['delivery_date'] = Date('Y-m-d');
     }  
	/* if (!isset($_POST['FromDate'])) {
      $_POST['FromDate'] =date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 1, date("Y")));
     }
	 if (!isset($_POST['ToDate'])) {
      $_POST['ToDate'] =date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") + 1, date("Y")));
     }*/
 
?>
 <input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
					<table class="selection">
         <div class="text-nav">

					<div class="text-nav-1 "> 	<div>供应商编号：</div>
							<input  type="text" required="required" name="vendorcode" id="text_slect_vendor" value="<?=$_POST['vendorcode']?>" size="16" maxlength="25" onblur="sel()"  />
								<image class="select_img" src="img/search.png" id="btn_slect_vendor"/></div>
				
                              
					<div class="text-nav-1 required">
						<div>需求日期：</div>
							<input type="text" name="ScheduleDate" maxlength="15" size="12"  value="<?=$_POST['ScheduleDate']?>" onfocus="WdatePicker() "></div>

               <div class="text-nav-1 ">
						<div>供应商名称：</div>
							<input readonly="readonly"   type="text"  name="vendorname" id="text_slect_name" value="<?=$_POST['vendorname']?>" size="55" maxlength="46"  /></div>
<div class="text-nav-1 required">
    <div>订单号：</div>
    <input type="text" required="required" name="order_number" id="text_slect_order_number" value="<?=$_POST['order_number']?>" size="13" maxlength="25" onblur="sel()"/>
	<image class="select_img" src="img/search.png" id="btn_slect_order_number"/>
  </div>
  <div  class="text-nav-1">
    <div>订单行:</div>
    <input type="text"   required="required" readonly="readonly" name="so_line"  value="<?=$_POST['so_line']?>"  id="text_slect_line"  size="5" maxlength="20"/> 
  </div>

   <div  class="text-nav-1">
    <div>成品料号：</div>
    <input readonly="readonly" readonly="readonly" type="text" name="stockid" id="text_slect_stockid" value="<?=$_POST['stockid']?>" size="150" maxlength="250" />
</div>
 <div  class="text-nav-1">
    <div>订单数量:</div>
    <input type="text" class="number" readonly="readonly" required="required"  name="quantity"  value="<?=$_POST['quantity']?>" id="text_slect_quantity" size="10" maxlength="20"/> 
	<input type="hidden"   name="wip_entity_name"  value="<?=$_POST['wip_entity_name']?>" id="text_slect_wip_entity_name" size="150" maxlength="250"   /> 
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
					<div  class="text-nav-1 required">
			<div>税别</div> 		  
			 <input type="text" readonly="readonly" onblur="check55()" name="tax_name" id="text_slect_tax_name" value="<?=$_POST['tax_name']?>" size="10" maxlength="100"/>
					 <image class="select_img" src="img/search.png" id="btn_slect_tax_name"/>
	</div>   
	
                            
		<div class="text-nav-1 required">
						<div>含税金额：</div>
							<input  type="text"  readonly="readonly" class="number" name="po_all_amount" id="po_all_amount" value="<?=$_POST['po_all_amount']?>" size="10" maxlength="10"/></div>
		
		<div class="text-nav-1 ">
						<div>税金：</div>
							<input  onblur="check55()" type="text" readonly="readonly" class="number" name="tax_amount" id="tax_amount" value="<?=$_POST['tax_amount']?>" size="10" maxlength="10"/></div>    
		<div class="text-nav-1 ">
						<div>未税金额：</div>
							<input  type="text" readonly="readonly" class="number" name="all_line_amount" id="all_line_amount" value="<?=$_POST['all_line_amount']?>" size="10" maxlength="10"/></div>    
							<style>
							#hidden_data {
								display: none;
							}

							.hidden_btn {
								display: flex;
								width: 100%;
								justify-content: center;
								align-items: center;
								margin: 1%;
							}

							

							.btn_psn {
								cursor: default;
								width: 50%;
								display: flex;
								justify-content: center;
								align-items: center;
								height: 15px;
								color: #000;
								background-image: linear-gradient(to right, rgb(196, 206, 218, 0) 0%, rgb(196, 206, 218, 0.5) 50%, rgb(196, 206, 218, 0) 100%);
								/* 多个渐变色 */
							}

							.btn_arrow {
								padding: 5px;
								height: 100%;
								/* 设置文本不能被选中 */
								user-select: none;
								transform: rotate(90deg);
							}

							.arrow-up {
								transform: rotate(-90deg);
							}

							.arrow-down {
								transform: rotate(90deg);
							}
						</style>
			<div class="text-nav" id="hidden_data">

			<div  class="text-nav-1 required">
			<div>税率</div> 		  
			 <input type="text" readonly="readonly" onblur="check55()" name="tax_rate" id="text_slect_tax_rate" value="<?=$_POST['tax_rate']?>" size="10" maxlength="100"/>
 
	</div>   	 
		<div class="text-nav-1 required">
						<div>币别：</div>
		<input readonly="readonly" type="text"   name="currency_code" id="text_slect_currency_code" value="<?=$_POST['currency_code']?>" size="10" maxlength="10"/></div>
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
		<div class="text-nav-1 required">
			<div>订单类型</div>

			<select name="order_type" id="text_slect_order_type">
				<?php
					$sql3 = "select order_type from po_order_type order by order_type_id";
					$result3 = DB_query($sql3,$db);
					while ($v = DB_fetch_array($result3)) {
						if ($v['order_type']==$_POST['order_type']) {
				?>
					<option value="<?=$v['order_type']?>" selected="selected"><?=$v['order_type']?></option>
				<?php }else{?>
				<option value="<?=$v['order_type']?>"><?=$v['order_type']?></option>
				<?php		}
					}
				?>
			</select>
				</div> 
			
  
  <div  class="text-nav-1">
    <div>单位:</div>
    <input type="text" readonly="readonly"  required="required"  name="uom"  value="<?=$_POST['uom']?>"  id="text_slect_uom"  size="5" maxlength="20"/> 
  </div>

  
 
<div  class="text-nav-1">
    <div>产品名称：</div>
    <input readonly="readonly" type="text"   name="item_name" id="text_slect_item_name" value="<?=$_POST['item_name']?>" size="150" maxlength="250"/>
  </div>
  <div  class="text-nav-1">
    <div>规格型号:</div>
    <input type="text" readonly="readonly"  name="item_desc"  value="<?=$_POST['item_desc']?>" id="text_slect_item_desc" size="150" maxlength="250"   /> 
  </div>
  <div  class="text-nav-1">
    <div>材质:</div>
    <input type="text"   name="caizhi" readonly="readonly" value="<?=$_POST['caizhi']?>" id="text_slect_caizhi" size="150" maxlength="250"   /> 
  </div>
    <div  class="text-nav-1">
    <div>客户简称：</div>
    <input  readonly="readonly" type="text"   name="customer_code" id="text_slect_customer_code" value="<?=$_POST['customer_code']?>" size="10" maxlength="10"/>
  </div>
  <div  class="text-nav-1">
    <div>销售订单备注：</div>
    <input type="text"  readonly="readonly" name="header_remark" id="text_slect_header_remark" value="<?=$_POST['header_remark']?>" size="100" maxlength="150"/>
  </div>
  <div  class="text-nav-1">
    <div>销售订单行备注：</div>
    <input type="text"  readonly="readonly" name="line_remark" id="text_slect_line_remark" value="<?=$_POST['line_remark']?>" size="100" maxlength="150"/>
  </div>
  <div  class="text-nav-1">
    <div>需求日期：</div>
    <input type="text"  name="need_date" maxlength="20" size="10" required="required" value="<?=$_POST['need_date']?>" id="text_slect_need_date" onfocus="WdatePicker() ">
	 <input type="hidden"  readonly="readonly" name="youhui_amount" id="youhui_amount" value="<?=$_POST['youhui_amount']?>" />
	 <input type="hidden" name="OrderDate" maxlength="15" size="12" required="required" value="<?=$_POST['OrderDate']?>" onfocus="WdatePicker() "></div>
  </div>
 	 
							 
 
				</div>

				</div>
						<!-- 按钮 -->
						<div class="hidden_btn">
							<div class="btn_psn" onclick="toggleHiddenBtn()">
								<div class="btn_arrow" id="arrow">&gt;</div>
							</div>
						</div>      
						<script>
							
							function toggleHiddenBtn()  {
								var hiddenBtn = document.getElementById("hidden_data");
								var arrow = document.getElementById("arrow");
								if (hiddenBtn.style.display === "flex") {
									hiddenBtn.style.display = "none";
									arrow.classList.remove("arrow-up");
									arrow.classList.add("arrow-down");
									
								} else {
									hiddenBtn.style.display = "flex";
									arrow.classList.remove("arrow-down");
									arrow.classList.add("arrow-up");
								}
							}
							
						</script>
                </div>							
              
             

<?php
echo '</table><div class="centre"><input type="submit" name="Search" value="查询需转采购单明细"></div>';

if (isset($_POST['Search']) and isset($result) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous']))
{
  $ListCount = DB_num_rows($result);
  $ListPageMax = ceil($ListCount / $_SESSION['DisplayRecordsMax']);
  
  if (isset($_POST['Next'])) 
  {
    if ($_POST['PageOffset'] < $ListPageMax) 
	{
      $_POST['PageOffset'] = $_POST['PageOffset'] + 1;
    }
  }

  if (isset($_POST['Previous'])) 
  {
	if ($_POST['PageOffset'] > 1) 
    {
	  $_POST['PageOffset'] = $_POST['PageOffset'] - 1;
    }
  }
 
  echo '<input type="hidden" name="PageOffset" value="' . $_POST['PageOffset'] . '" />';
  if ($ListPageMax > 1) 
  {
    echo '<br /><div class="centre">&nbsp;&nbsp;' . $_POST['PageOffset'] . ' ' . _('of') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ': ';
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
	<input type="submit" name="Go1" value="' . _('Go') . '" />
	<input type="submit" name="Previous" value="' . _('Previous') . '" />
	<input type="submit" name="Next" value="' . _('Next') . '" />';
	echo '</div>';
  }
  echo '<div class="centre"> 
                        <p id="Prompt" style="color: red;font-size: 20px"></p>
                    </div> <br /><div class="text-nav-table">
  <table cellpadding="2" class="selection">';
  echo '<tr>
         <th width =40 >' . '选择' . '</th>
        <th class="ascending" width = 150>' . _('材料料号') . '</th>	
        <th width = 150 >' . _('材料名称') . '</th>				
	    <th class="ascending" width = 150>' . _('规格型号') . '</th>
	    <th  width = 40>' . _('单位') . '</th>	
	    <th    >' . _('需求数量') . '</th>
        <th width = 60>' . _('上次单价') . '</th>
	    <th  width = 40>' . _('采购单位') . '</th>
	    <th   width = 60>' . _('数量') . '</th> 
	    <th   width = 60>' . _('单价') . '</th> 
	    <th  width = 20>'  . _('金额') . '</th>
	    <th  width = 20>'  . _('需求日期') . '</th>
	    <th  width = 60>'  . _('备注') . '</th> 
       
	    
       </tr>';  
  $k = 0; //row counter to determine background colour
  $RowIndex = 0;
 
  if (DB_num_rows($result) <> 0) 
  {
    DB_data_seek($result, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);
    $i = 1; //counter for input controls
    while (($myrow = DB_fetch_array($result)) AND ($RowIndex <> $_SESSION['DisplayRecordsMax'])) 
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
	  $line_amount=$myrow['quantity']*$myrow['price'];
	   
      echo '   <td><input type="checkbox" onclick="OncheckBox(this)" id="status'.$i.'" checked="false" name="line_id'.$i.'" value="'.$i.'" /></td>
			<td>' . $myrow['item_no'] . '</td>
			<td>' . $myrow['item_name'] . '</td>
			<td>' . $myrow['item_desc']  . '</td> 
			<td>' . $myrow['uom']  . '</td> 

			<td>' . sprintf("%.2f",$myrow['component_quantity']*$_POST['quantity']) . '</td>			
			<td>' . sprintf("%.2f",$myrow['last_price']) . '</td>

		
		';?>
		 <td><select name="uom<?=$i?>" id="text_slect_units<?=$i?>">
				<?php
					$sql4 = "select unitname from unitsofmeasure order by unitid";
					$result4 = DB_query($sql4,$db);
					while ($v = DB_fetch_array($result4)) {
						if ($v['unitname']==$myrow['uom']) {
				?>
					<option value="<?=$v['unitname']?>" selected="selected"><?=$v['unitname']?></option>
				<?php }else{?>
				<option value="<?=$v['unitname']?>"><?=$v['unitname']?></option>
				<?php 
				}
					}
				?> 
			</select>
		</td> 

       <?php 

	   echo ' <td><input id="quantity' .$i.'" onblur="checkalla()" style="background-color:yellow" type="text"  name="quantity'.$i.'" class="number" size="8"  value="' .sprintf("%.2f",$myrow['quantity'])  . '" /></td> ';
      echo ' <td><input id="text_slect_unit_price' .$i.'" readonly="readonly" type="text" name="unitprice'.$i.'" class="number" size="8" value="' .sprintf("%.9f",$myrow['price']) . '" /></td> ';
	  echo ' <td><input type="text" id="lineamount' .$i.'" onblur="checkalla()" style="background-color:yellow"  name="line_amount'.$i.'" class="number" size="8"  value="' . sprintf("%.2f",$line_amount)   . '" /></td> ';
	  echo ' <td><input type="text" id="need_date' .$i.'" onfocus="WdatePicker() " name="need_date'.$i.'" size="8"  value="' .$_POST['OrderDate'] . '" /></td> ';
      echo ' <td><input type="text" name="remark'.$i.'"   size="10" value="' .$myrow['remark']. '" /></td> ';	
	  echo '
	  <input type="hidden" name="pr_num'.$i.'"   size="10" value="' .$myrow['pr_num']. '" />
	  <input type="hidden" name="item_no'.$i.'"   size="10" value="' .$myrow['item_no']. '" />
	 
	  <input type="hidden" name="need_order_number'.$i.'"   size="10" value="' .$myrow['need_order_number']. '" /> 
	  <input type="hidden" name="need_so_line'.$i.'"   size="10" value="' .$myrow['need_so_line']. '" /> 
	  <input type="hidden" name="need_quantity'.$i.'"   size="10" value="' .$myrow['quantity']. '" /> 
	  <input type="hidden" name="last_price'.$i.'" id="text_slect_last_price'.$i.'"  size="10" value="' .$myrow['last_price']. '" /> 
	  <input type="hidden" name="pr_line'.$i.'"   size="10" value="' .$myrow['line']. '" />
	  <input type="hidden" name="suoding_flag'.$i.'"   size="10" value="' .$myrow['suoding_flag']. '" />
	  <input type="hidden" name="suoding_remark'.$i.'"   size="10" value="' .$myrow['suoding_remark']. '" /></td>   ';
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
    echo '<br /><div class="centre">&nbsp;&nbsp;' . $_POST['PageOffset'] . ' ' . _('of') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ': ';
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
			<input type="submit" name="Go2" value="' . _('Go') . '" />
			<input type="submit" name="Previous" value="' . _('Previous') . '" />
			<input type="submit" name="Next" value="' . _('Next') . '" />';
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
	$(function(){
		$( "#text_slect_vendor" ).autocomplete({
			source: "autosearchvendor.php",
			minLength: 2,
			autoFocus: true
		});
	});

 function checkalla(){                               
                                var allamount=0; 
                            var youhui_amount=document.getElementById("youhui_amount").value;
                            var tax_rate=document.getElementById("text_slect_tax_rate").value;
                            var tax_flag=document.getElementById("text_slect_tax_flag").value;
							var all_rate = Number(1) + Number(tax_rate) ;
                                for(var i=1 ; i < 200; i++){   
									if (document.getElementById("lineamount" + i)==null)  {
									p=0;
										}
									else {										 
								  
                                   
								   var shuliang=document.getElementById("quantity"+i).value;
		                        //    var danjia=document.getElementById("text_slect_unit_price"+i).value;
		                           var lineamount=document.getElementById("lineamount"+i).value;

		                           var last_price=document.getElementById("text_slect_last_price"+i).value;
								   var ischecked=document.getElementById("status" + i).checked;
								   if(shuliang==""){
			                         shuliang=0;
		                               }
		                        //    if(danjia==""){
		                        //    	danjia=0;
		                        //    }
								   if(lineamount==""){
		                           	lineamount=0;
		                           }
								   if (shuliang>0   && ischecked )
								   {
									document.getElementById("text_slect_unit_price"+i).value=Math.round(Number(lineamount)/ Number(shuliang)*1000000)/1000000 ;
									    
                                   var lineamount=document.getElementById("lineamount"+i).value;
									   allamount=Number(allamount) + Number(lineamount);

								   }
								//    if ( parseFloat(danjia)> parseFloat(last_price)  ) {
								// 	document.getElementById("lineamount"+i).style.color = "red";
								// 	document.getElementById("text_slect_unit_price"+i).style.color = "red";
								// 	}

		                            
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
		
        document.getElementById("po_all_amount").value=Math.round(Number(han_tax_amount)*100)/100; 
		document.getElementById("tax_amount").value=Math.round(Number(tax_amount)*100)/100; 
         document.getElementById("all_line_amount").value=Math.round(Number(no_tax_amount)*100)/100;
        
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

 
 function OncheckBox(index){
      var allamount=0; 
                            var youhui_amount=document.getElementById("youhui_amount").value;
							var tax_rate=document.getElementById("text_slect_tax_rate").value;
							var tax_flag=document.getElementById("text_slect_tax_flag").value;
							var all_rate = Number(1) + Number(tax_rate) ;
                                for(var i=1 ; i < 200; i++){   
									if (document.getElementById("lineamount" + i)==null)  {
									p=0;
										}
									else {										 
								  
                                   
								   var shuliang=document.getElementById("quantity"+i).value;
		                        //    var danjia=document.getElementById("text_slect_unit_price"+i).value;
		                           var lineamount=document.getElementById("lineamount"+i).value;

		                           var last_price=document.getElementById("text_slect_last_price"+i).value;
								   var ischecked=document.getElementById("status" + i).checked;
								   if(shuliang==""){
			                         shuliang=0;
		                               }
		                        //    if(danjia==""){
		                        //    	danjia=0;
		                        //    }
								if(lineamount==""){
		                           	lineamount=0;
		                           }
								   if (shuliang>0   && ischecked )
								   {
									document.getElementById("text_slect_unit_price"+i).value=Math.round(Number(lineamount)/ Number(shuliang)*1000000)/1000000 ;
									    
                                   var lineamount=document.getElementById("lineamount"+i).value;
									   allamount=Number(allamount) + Number(lineamount);

								   }
								//    if ( parseFloat(danjia)> parseFloat(last_price)  ) {
								// 	document.getElementById("lineamount"+i).style.color = "red";
								// 	document.getElementById("text_slect_unit_price"+i).style.color = "red";
								// 	}

		                            
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
		
		document.getElementById("po_all_amount").value=Math.round(Number(han_tax_amount)*100)/100;


        document.getElementById("all_line_amount").value=Math.round(Number(no_tax_amount)*100)/100; 
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

		
		$('#btn_slect_order_number').dialog({
            title:'选择订单',
            width: '1250px',
            height: 470,
            content:'url:BtnSearchorder_number.php?fwValue=&cat=buliao',
            init:function(){
			    this.content.document.getElementById('cat').value ='buliao';
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

