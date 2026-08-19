<?php
 
if(isset($_GET['data'])){
	
	 include_once("connect.php"); 
	 $sql = "select a.* ,(select tax_mount from tax_set b where a.tax_name=b.tax_name) tax_rate from customers a where enable_flag='Y' and customer_code = '".$_GET['data']."'";
	 $result_num = mysql_query($sql, $db);
	 $res = mysql_fetch_assoc($result_num);
	 echo $res['customer_name'].':'.$res['customer_address'].':'.$res['customer_contacts'].':'.$res['currency_code'].':'.$res['tax_name'].':'.$res['tax_rate'].':'.$res['tax_flag'];
	 return ;
 } 

  if(isset($_GET['data3'])){
	 //$sql = "select * from customers where customer_name = '".$_GET['data']."'";
	  include_once("connect.php"); 
	 $sql = "select aa.*,(select price from so_lines_all c where c.stockid=aa.item_no
and order_line_id in (select max(order_line_id) from so_lines_all d where d.stockid=aa.item_no
and d.quantity>0 ) ) zhidao_price  from sf_item_no aa where aa.item_no = '".$_GET['data3']."' ";
	 
	 $result_num = mysql_query($sql, $db); 
	 
	 $myrow = mysql_fetch_assoc($result_num);
	  		   
echo $myrow['item_name'].':'.$myrow['item_desc'].':'.$myrow['units'].':'.$myrow['zhidao_price'];
	 
	 

	 return ;
 }

include('includes/session.inc');
$Title = _('订单建立');

$ViewTopic= '订单建立';
$BookMark = '订单建立';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

unset($result);
 
	if (isset($_POST['Save'])) {
		$errorflag = 0;
		$lineflag=0;
		foreach ($_POST as $key => $value) {
			if ($value != '') {
				 
				if (substr($key, 0,3)=='UOM') {
					//$errorflag = 0;
					$i = substr($key, 3);
					if ($value != '') {
						if ($_POST['UOM'.$i]=='') {
						$errorflag = 1;
						$lineflag=0;
						prnMsg($value.'未填写单位，请填写单位！',error);
						}
						if ($_POST['unitprice'.$i]=='') {
							$errorflag = 1;
							$lineflag=0;
							prnMsg($value.'未填写单价，请填写单价！',error);
						}
						if ($_POST['suoding_flag'.$i]=='Y') {
							$errorflag = 1;
							$lineflag=0;
							prnMsg($_POST['stockid'.$i].$_POST['suoding_remark'.$i].'已被锁定,请与工程联系！',error);
						}
						if ($_POST['quantity'.$i]=='') {
							$errorflag = 1;
							$lineflag=0;
							prnMsg($value.'未填写数量，请填写数量！',error);
						}  else {
		                   $lineflag=1;
		                }
						
					}
				}
			}
		}

	 if  ( $lineflag == 0 ) {
	    prnMsg(_('资料至少存在一行有效！'), 'error');
	 }
	 $time = time();
	 $time2 = $time - 10;
   
	 if ($_SESSION['lastsearchtime'] > $time2) {
		 $errorflag = 1;
		 prnMsg($value . '重复提交！', error);
	 }
		if ($errorflag ==0 and $lineflag == 1) {

			$date = date('Ymd');
			
        $sql_num = "select 	(
		CASE WHEN substr(max(order_number) ,-2,1) = 0 THEN
			RIGHT (
				'100' + (
					max(substr(order_number ,- 1)) + 1
				),
				2
			)
		ELSE
			substr(max(order_number),-2,2) + 1
		END
        ) order_number from so_headers_all where substr(order_number,-10,8) = '" . $date . "'";
      // echo $sql_num;
        $result_num = DB_query($sql_num, $db);
        $rownum = DB_num_rows($result_num);
        while ($v = DB_fetch_array($result_num)) {
            if ($v['order_number'] == null) {
                $OrderNum = 'SO'.$date . '01';
            } else {
                $OrderNum =  'SO'. $date . $v['order_number'];
            }
        }

			foreach ($_POST as $key => $value) {
				if ($value != '') {
					if (substr($key, 0,3)=='UOM') {
						$i = substr($key, 3);
					 
						$lineamount[$i] = $_POST['quantity'.$i] * $_POST['unitprice'.$i] ;
						
					}
				}
			}
		}
        
		if ($errorflag == 0 and $lineflag == 1) {
			$need_date = strtotime($_POST['need_date']);
			$qianding_date = strtotime($_POST['qianding_date']);
			 
			DB_Txn_Begin($db);
			$time = time();
			$all_line_amount = 0;
			$line=0;
			foreach ($_POST as $key => $value) {
				if ($value != '') {
					if (substr($key, 0,3)=='UOM') {
						$i = substr($key, 3); 
						 
                        if ($_POST['other_price'.$i]=='') {
						$_POST['other_price'.$i] = 0.00;
		                 }
                        if ($_POST['zhidao_price'.$i]=='') {
						$_POST['zhidao_price'.$i] = 0.00;
		                }
						 
		               $line=$line+1;
                                    
                         
			    $sql = "insert into so_lines_all(order_number,line,line_remark,uom,price,customer_item,
						quantity,stockid,item_name,line_amount,subinventory_code,need_date,
						creation_date,created_by,last_update_date,last_updated_by)
						values('".$OrderNum."','".$line."','".$_POST['remark'.$i]."','".$_POST['UOM'.$i]."','".$_POST['unitprice'.$i]."','".$_POST['customer_item'.$i]."','".$_POST['quantity'.$i]."','".$_POST['stockid'.$i]."','".$_POST['item_name'.$i]."','".$_POST['line_amount'.$i]."'
						,'".$_POST['Subinventory_code'.$i]."','".strtotime($_POST['need_date'.$i])."',
						'".$time."','".$_SESSION['UserID']."','".$time."','".$_SESSION['UserID']."') ";
						
						$result = DB_query($sql,$db);
						
					}
				}
			}
            
            	if ($_POST['youhui_amount']=='') {
						$_POST['youhui_amount'] = 0;
		       }
			   if ($_POST['customer_order_number']=='') {
				$_POST['customer_order_number'] = $OrderNum;
	   }
        if ($line>0) {

	  $status='待签核';
			$sql = "insert into so_headers_all
(order_type,all_line_amount,term_name,yewu,delivery_date,schedule_recevie_date,delivery_type,yunfei_amount,tax_amount,ship_address,ship_city, youhui_amount,
order_number,customer_code,customer_contact,need_date,qianding_date,status,currency_code,customer_order_number,tax_name,tax_rate,tax_flag,project,jiaohuotiaojian,youxiaoxing1,
youxiaoxing2,baozhuang,zhiliangbaozheng,mainfeifuwu,order_all_amount,contract_number,creation_date,header_remark,created_by,last_update_date,last_updated_by)values
('".$_POST['order_type']."','".$_POST['all_line_amount']."','".$_POST['term_name']."','".$_POST['yewu']."','".strtotime($_POST['delivery_date'])."','".strtotime($_POST['schedule_recevie_date'])."','".$_POST['delivery_type']."','".$_POST['yunfei_amount']."','".$_POST['tax_amount']."','".$_POST['ship_address']."','".$_POST['ship_city']."', '".$_POST['youhui_amount']."','".$OrderNum."','".$_POST['customercode']."','".$_POST['customer_contact']."'
,'".$need_date."','".$qianding_date."','".$status."','".$_POST['currency_code']."','".$_POST['customer_order_number']."','".$_POST['tax_name']."','".$_POST['tax_rate']."','".$_POST['tax_flag']."','".$_POST['project']."','".$_POST['jiaohuotiaojian']."','".$_POST['youxiaoxing1']."','".$_POST['youxiaoxing2']."','".$_POST['baozhuang']."','".$_POST['zhiliangbaozheng']."','".$_POST['mainfeifuwu']."','".$_POST['order_all_amount']."','".$_POST['contract_number']."','".$time."','".$_POST['Header_Remark']."','".$_SESSION['UserID']."','".$time."','".$_SESSION['UserID']."')";
			$result = DB_query($sql,$db);
			//echo $sql;
			$_SESSION['lastsearchtime'] = $time;
			DB_Txn_Commit($db);
               	prnMsg('订单编号'.$OrderNum.'建立成功！',success);
             header("Location: SussCreate1.php?OrderNum=".$OrderNum);
		}

		} else {
		prnMsg( $errorflag.'有错误！' , 'error');
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
<!--<script type="text/javascript" src="./JXC/statics/base/js/jQuery1.7.2.js"></script> -->
<script type="text/javascript" src="./JXC/statics/base/js/uploadify/jquery.uploadify.v2.1.4.min.js"></script>
<script type="text/javascript" src="./JXC/statics/base/js/iframes.js"></script>
<script type="text/javascript" src="./JXC/statics/base/js/cookie.js"></script>
<script type="text/javascript" src="./JXC/statics/base/js/jquery.livequery.js"></script>

<link rel="stylesheet" href="jquery.ui.autocomplete.css">
<script type="text/javascript" src="ui/jquery.ui.core.js"></script>
<script type="text/javascript" src="ui/jquery.ui.widget.js"></script>
<script type="text/javascript" src="ui/jquery.ui.position.js"></script>
<script type="text/javascript" src="ui/jquery.ui.autocomplete.js"></script>

<!-- <script src="./JXC/javascript/jquery-1.7.2.min.js"></script> -->
<script src="./JXC/javascript/lhgdialog.min.js?self=true&skin=chrome"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
<script src="/javascript/bootstrap.min.js"></script>

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
 
<div id="CanvasDiv">
	<div id="BodyDiv">
		<div id="BodyWrapDiv">
			<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="新建订单" alt="新建订
单">新建订单</p>
			<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST"><input type="hidden" name="time" value="<?=$time?>">
				<div>
<?php
	 
	  if (!isset($_POST['need_date'])) {
      $_POST['need_date'] = Date('Y-m-d');
     }
	 if (!isset($_POST['delivery_date'])) {
      $_POST['delivery_date'] = Date('Y-m-d');
     }  
	 if (!isset($_POST['qianding_date'])) {
      $_POST['qianding_date'] = Date('Y-m-d');
     }  
	 if (!isset($_POST['schedule_recevie_date'])) {
      $_POST['schedule_recevie_date'] = Date('Y-m-d');
     }   
	 if (!isset($_POST['jiaohuotiaojian'])) {
      $_POST['jiaohuotiaojian'] = '合同签订后2-3周内交货';
     }
	 if (!isset($_POST['baozhuang'])) {
      $_POST['baozhuang'] = '出厂含符合国内运输条件的纸箱外包装';
     }
	  if (!isset($_POST['zhiliangbaozheng'])) {
      $_POST['zhiliangbaozheng'] = '高效过滤器含出厂合格证明';
     }
	 if (!isset($_POST['youxiaoxing1'])) {
      $_POST['youxiaoxing1'] = '本报价自报价之日起30天(含休息日)有效';
     }
	 if (!isset($_POST['youxiaoxing2'])) {
      $_POST['youxiaoxing2'] = '一经双方书面确认,具有正式合同法律效力';
     }
      if (!isset($_POST['mainfeifuwu'])) {
      $_POST['mainfeifuwu'] = '如有其他需要,需要另外商务协商';
     }
	
	 if (!isset($_POST['tax_amount'])) {
      $_POST['tax_amount'] = 0;
     }
	 if (!isset($_POST['yunfei_amount'])) {
      $_POST['yunfei_amount'] = 0;
     }
 
?>
				<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
				<div class="text-nav">
 
			<div class="text-nav-1 required">
			<div>客户编号:</div>  
			<input type="text"  name="customercode" id="text_slect_customer" value="<?=$_POST['customercode']?>" size="10" maxlength="25"  onblur="sel()"/>  
			<image class="select_img" src="img/search.png" id="btn_slect_customer"/>
			</div>
			<div class="text-nav-2 required">
			<div>客户名称:</div>
		 <input readonly="readonly" type="text" name="customername" id="text_slect_name" value="<?=$_POST['customername']?>" size="60" maxlength="150" />
			</div>
			<div class="text-nav-1"><div>客户订单号:</div>		 
			<input type="text"   name="customer_order_number" value="<?=$_POST['customer_order_number']?>" size="10" maxlength="50"/></div> 
			
			<div class="text-nav-1"><div>联系人:</div>		 
			<input readonly="readonly" type="text"   name="customer_contact" id="text_slect_customer_contact" value="<?=$_POST['customer_contact']?>" size="10" maxlength="10"/></div> 
		
				
<div class="text-nav-1 ">
			<div>币别:</div>
			<select name="currency_code" id="text_slect_currency_code">
				<?php
					$sql = "select currabrev,currency from currencies order by currency_id";
					$result = DB_query($sql,$db);
					while ($v = DB_fetch_array($result)) {
						if ($v['currabrev']==$_POST['currency_code']) {
				?>
					<option value="<?=$v['currabrev']?>" selected="selected"><?=$v['currency']?></option>
				<?php }else{?>
				<option value="<?=$v['currabrev']?>"><?=$v['currency']?></option>
				<?php		}
					}
				?>
			</select>
				</div> 	

		<div class="text-nav-2 ">
			<div>出货地址:</div>
		 <input  type="text" name="ship_address" id="text_slect_customer_address" value="<?=$_POST['ship_address']?>" size="90" maxlength="250" /></div>
		
			<div class="text-nav-1 required">
			<div>签订日期:</div>
			<input type="text" required="required" name="qianding_date" maxlength="20" size="12"  value="<?=$_POST['qianding_date']?>" 
onfocus="WdatePicker() "></div>
			
<div class="text-nav-1 required">
			<div>业务员</div>
			<select name="yewu" id="text_slect_employee_num">
				<?php
					$sql = "select employee_num,employee_name from hr_employees order by employee_num";
					$result = DB_query($sql,$db);
					while ($v = DB_fetch_array($result)) {
						if ($v['employee_num']==$_POST['yewu']) {
				?>
					<option value="<?=$v['employee_num']?>" selected="selected"><?=$v['employee_num'].$v['employee_name']?></option>
				<?php }else{?>
				<option value="<?=$v['employee_num']?>"><?=$v['employee_num'].$v['employee_name']?></option>
				<?php		}
					}
				?>
			</select>
				</div> 	
				<div class="text-nav-2">
			<div>订单备注:</div> 
			 <input type="text"  maxlength="200" size="60" name="Header_Remark"  value="<?=$_POST['Header_Remark']?>"  /> </div>
			
			<div  class="text-nav-1 required">
			<div>税别</div> 		  
			 <input type="text" readonly="readonly" onblur="check56()" name="tax_name" id="text_slect_tax_name" value="<?=$_POST['tax_name']?>" size="10" maxlength="100"/>
					<image class="select_img" src="img/search.png" id="btn_slect_tax_name"/>
	</div>   
	<div  class="text-nav-1 required">
			<div>税率</div> 		  
			 <input type="text" readonly="readonly" onblur="check56()" name="tax_rate" id="text_slect_tax_rate" value="<?=$_POST['tax_rate']?>" size="10" maxlength="100"/>
 
	</div>   	 
			 
			<div class="text-nav-1 required"> <div>是否含税</div>
			<select name="tax_flag" id="text_slect_tax_flag" onblur="checkall()">
				<?php
					$sql = "select type_code,type_name from sys_type";
					$result = DB_query($sql,$db);
					while ($v = DB_fetch_array($result)) {
						if ($v['type_code']==$_POST['tax_flag']) {
				?>
					<option value="<?=$v['type_code']?>" selected="selected"><?=$v['type_name']?></option>
				<?php }else{?>
				<option value="<?=$v['type_code']?>"><?=$v['type_name']?></option>
				<?php		}
					}
				?>
			</select>
				</div> 			
			 
				<div class="text-nav-1">
			<div>含税金额:</div>
			<input  type="text"  class="number" name="order_all_amount" id="order_all_amount" value="<?=$_POST['order_all_amount']?>" size="10" maxlength="10"/></div>
            <div class="text-nav-1">   
            <div>未税金额:</div>
   <input  type="text" readonly="readonly" class="number" name="all_line_amount" id="all_line_amount" value="<?=$_POST['all_line_amount']?>" size="10" maxlength="10"/></div>
         
			<div class="text-nav-1 ">
			<div>税金:</div>
			<input  type="text" class="number"  name="tax_amount" id="tax_amount" value="<?=$_POST['tax_amount']?>"  size="10" maxlength="15" onblur="check56()"/></div>  
			<div class="text-nav-1 ">
			<div>运费:</div>
			<input  type="text" class="number"  name="yunfei_amount" id="yunfei_amount" value="<?=$_POST['yunfei_amount']?>" size="10" maxlength="15" onblur="check56()" /></div>          
             

			<div class="text-nav-1 ">
			<div>付款条件:</div> 		  
			<input type="text"   name="term_name" id="text_slect_term_name" value="<?=$_POST['term_name']?>" size="10" maxlength="100"/>
					 <image class="select_img" src="img/search.png" id="btn_slect_term_name"/>
				</div>  
          
		   
			<div class="text-nav-1">
			<div>最终优惠价:</div>
		 <input  type="text"  class="number" name="youhui_amount" id="youhui_amount" value="<?=$_POST['youhui_amount']?>" size="10" maxlength="10" onkeyup="check_amount()" onblur="check55()"/></div>
		  
			
   <div class="text-nav-1 "><div>订单类型</div>

<select name="order_type" id="text_slect_order_type">
    <?php
$sql = "select order_type from so_order_type order by order_type_id";
$result = DB_query($sql,$db);
while ($v = DB_fetch_array($result)) {
if ($v['order_type']==$_POST['order_type']) {
?>
    <option value="<?=$v['order_type']?>" selected="selected"><?=$v['order_type']?></option>
    <?php }else{?>
    <option value="<?=$v['order_type']?>"><?=$v['order_type']?></option>
    <?php		}
}
?>
</select>

 
			<input type="hidden" name="need_date" maxlength="20" size="10" required="required" value="<?=$_POST['need_date']?>" onfocus="WdatePicker() "></div>
		

			<div class="text-nav-1 required">
			<div>合同编号:</div>
		 <input  type="text"  required="required" name="contract_number" id="contract_number" value="<?=$_POST['contract_number']?>" size="10" maxlength="50"/></div> 

</div> 
	</table>
	<div class="centre">
		<input type="submit" name="Hearder" value="确认订单头信息">
	</div>
	<input type="hidden" name="PageOffset" value="1"/>
    
     <div class="centre"> 
                        <p id="Prompt" style="color: red;font-size: 20px"></p>
                    </div>
     
	<?php
		if (isset($_POST['customername']) and $_POST['customername'] != '') {
			 		 
	?>
              <input id="purchase_table_lastRow" name="purchase_table_lastRow" type=hidden value="">
			  <div style="overflow:scroll">
					<table id="purchase_table" cellpadding="2" class="selection">
					<tr id="list-top">
					<th width="230" bgcolor="#87CEFA"><font >料号<span style="color:red">*</span></font> </th>
					<th width="150" bgcolor="#87CEFA">产品名称</th>
					<th width="150" bgcolor="#87CEFA">规格型号</th>
					<th bgcolor="#87CEFA">单位</th>  
				    <th bgcolor="#87CEFA">客户料号 </th>
				    <th bgcolor="#87CEFA">上次售价 </th> 
                   	<th bgcolor="#87CEFA"><font >数量<span style="color:red">*</span></font>  </th>	 
				    <th bgcolor="#87CEFA"><font >单价<span style="color:red">*</span></font>   </th>
				    <th bgcolor="#87CEFA"><font >金额<span style="color:red">*</span></font> </th>
				    <th bgcolor="#87CEFA">需求日期 </th> 	
				 
					
					<th  bgcolor="#87CEFA" width="50" align="center">操作</th>
					</tr>
					<?php for($i=1;$i<=69;$i++){?>
			
					<tr id="purchase_table_<?=$i?>" <?php echo $i>5&&$_POST['UOM'.$i]==''?'style="display:none"':''?> class="mouse click">
 

					<td style="white-space:nowrap;"><input  type="text" name="stockid<?=$i?>" readonly="readonly" id="text_slect_buliao<?=$i?>" value="<?=$_POST['stockid'.$i]?>" size="20" maxlength="240" onblur="sel_item(<?=$i?>)"/> 
					<image class="select_img" src="img/search.png" id="btn_slect_buliao<?=$i?>"/>
				</td>                     
					  
					   <td><input readonly="readonly" type="text" name="item_name<?=$i?>" id="text_slect_item_name<?=$i?>" value="<?=$_POST['item_name'.$i]?>" size="25" maxlength="500"  /></td>
					   <td><input readonly="readonly" type="text" name="item_desc<?=$i?>" id="text_slect_item_spec<?=$i?>" value="<?=$_POST['item_desc'.$i]?>"size="25" maxlength="500"  /></td> 
					   <td><input readonly="readonly" type="text" name="UOM<?=$i?>" id="text_slect_units<?=$i?>" value="<?=$_POST['UOM'.$i]?>" size="2" maxlength="4"/></td> 

					   <td><input readonly="readonly" type="text" name="customer_item<?=$i?>" id="text_slect_customer_item<?=$i?>" value="<?=$_POST['customer_item'.$i]?>" size="7" maxlength="14"/></td>
						<td style="display:none;"><input type="text" name="min_order<?=$i?>" id="min_order<?=$i?>" value="<?=$_POST['min_order'.$i]?>" /></td>
						   <td><input readonly="readonly" type="text" name="last_price<?=$i?>" id="text_slect_last_price<?=$i?>" value="<?=$_POST['last_price'.$i]?>" size="6" maxlength="14"/></td>
						    
                       <td><input type="text" class="number"  id="quantity<?=$i?>" step="1"  min="0"  onkeyup="this.value= this.value.match(/\d+(\.\d{0,2})?/) ? this.value.match(/\d+(\.\d{0,2})?/)[0] : ''"   name="quantity<?=$i?>" value="<?=$_POST['quantity'.$i]?>" size="4" maxlength="10" onblur="checkall()"/> </td>
					 

                          <td><input type="text" class="number"   name="unitprice<?=$i?>" step="1"  min="0"  onkeyup="this.value= this.value.match(/\d+(\.\d{0,5})?/) ? this.value.match(/\d+(\.\d{0,5})?/)[0] : ''"   id="text_slect_unit_price<?=$i?>" value="<?=$_POST['unitprice'.$i]?>" size="8" maxlength="10" onblur="checkall()"/> </td> 
						 
                          <td><input type="text" class="number" readonly="readonly" name="line_amount<?=$i?>"  onkeyup="this.value= this.value.match(/\d+(\.\d{0,3})?/) ? this.value.match(/\d+(\.\d{0,3})?/)[0] : ''" id="line_amount<?=$i?>" value="<?=$_POST['line_amount'.$i]?>" size="8" maxlength="10" /> </td> 
						  <td><input  type="text" name="need_date<?=$i?>" onfocus="WdatePicker() " autocomplete="off" value="<?=$_POST['need_date'.$i]?>" size="9" maxlength="14"/></td>
		<!--
		<td><select name="Subinventory_code<?=$i?>">  
   <?php  
   $sql = "SELECT loccode,locationname FROM locations where managed='Y' order by paixu";
    $result1 = DB_query($sql, $db);
     
    while ($Salesmanrow = DB_fetch_array($result1)) {
		 ?>
        <option style="width:40px;" value="<?=$Salesmanrow['loccode']?>">  <?=$Salesmanrow['locationname'] ?>
            </option> 
   <?php }
   ?>
     </select>  </td>  -->
						 
						 
 
                      <td style="white-space:nowrap;">  <a onclick="delettr($(this));" style="padding:0px 5px;" href="javascript:;">删除</a></td>

					  <td><input  type="hidden" name="Subinventory_name<?=$i?>" id="text_slect_locationname<?=$i?>" value="<?=$_POST['Subinventory_name'.$i]?>" size="8" maxlength="25"/>
					  <input  type="hidden" name="suoding_flag<?=$i?>" id="text_slect_suoding_flag<?=$i?>" value="<?=$_POST['suoding_flag'.$i]?>" size="8" maxlength="25"/>
					  <input  type="hidden" name="suoding_remark<?=$i?>" id="text_slect_suoding_remark<?=$i?>" value="<?=$_POST['suoding_remark'.$i]?>" size="8" maxlength="250"/>
					  
						 	

					</tr>
					<?php }?>
					
					</table>
					</div>
	               <div class="centre">
					<a onclick="addsave();">添加行</a>
	                
					</div>

					<div class="centre">
	                <input type="submit" name="Save" value="提交">
					</div>
	<?php
		}
	?>
					<input type="hidden" name="idcount" id='idcount' value="11"/>
					<input type="hidden" name="JustSelectedACustomer" value="Yes"/>
				</div>
			</form>
		</div>
	</div>
	
	<div id="FooterDiv">
		<div id="FooterWrapDiv">
		 	 
		</div>
	</div>
</div>
<script type="text/javascript">
   function  check(s1){
	    var a=document.getElementById("quantity"+s1).value;
        var b=document.getElementById("text_slect_unit_price"+s1).value;
		if(a==""){
			a=0;
		}
		if(b==""){
			b=0;
		}
       document.getElementById("line_amount"+s1).value=Math.round(Number(a*b)*100)/100;
     }

 	
		
    $(document).ready(function(){

        $('.divToilet table tr td a').click(function(){
            $(this).parent('td').toggleClass('highlight');
            if(!($(this).parent('td').hasClass('highlight'))) {
                $(this).next().val('0');
            }else {
                $(this).next().val('1');
            }
        });

		$(function(){
		$( "#text_slect_customer" ).autocomplete({
			source: "autosearchcustomer.php",
			minLength: 2,
			autoFocus: true
		});
	});

	<?php for($i=1;$i<=50;$i++){?> 
	$(function(){
		$( "#text_slect_buliao<?=$i?>" ).autocomplete({
			source: "autosearchstockso.php",
			minLength: 2,
			autoFocus: true
		});
	});
	<?php }?>

        <?php for($i=1;$i<=69;$i++){?> 
        $('#btn_slect_buliao<?=$i?>').dialog({
            title:'选择产品',
            width: '1200px',
            height: 470,
            content:'url:Searchbuliaoso.php?fwValue=<?=$i?>&cat=<?=$_POST['customercode']?>', 
            init:function(){
			    this.content.document.getElementById('cat').value = 'buliao';
                this.content.document.getElementById('fwValue').value = '<?=$i?>';
            }
        });
		<?php }?>


		 <?php for($i=1;$i<=69;$i++){?> 
        $('#btn_slect_subcode<?=$i?>').dialog({
            title:'选择仓库',
            width: '600px',
            height: 370,
            content:'url:Searchsubcode.php?fwValue=<?=$i?>&cat=buliao',
            init:function(){
			    this.content.document.getElementById('cat').value = 'buliao';
                this.content.document.getElementById('fwValue').value = '<?=$i?>';
            }
        });
		<?php }?>


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
		$('#btn_slect_customer').dialog({
            title:'选择客户',
            width: '1050px',
            height: 470,
            content:'url:BtnSearchCustomer999.php?fwValue=&cat=buliao',
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
 
 
     function sel(){
		var name=$('#text_slect_customer').val()
		$.get("","data="+name,function(res){
			name = res.split(":")		  
				$("#text_slect_name").val(name[0]) 
				$("#text_slect_customer_address").val(name[1])
				$("#text_slect_customer_contact").val(name[2])
				$("#text_slect_currency_code").val(name[3])
                $("#text_slect_tax_name").val(name[4])
                $("#text_slect_tax_rate").val(name[5])
                $("#text_slect_tax_flag").val(name[6])
   
		})	
	}  


		function sel_item(s1){
		var name=$('#text_slect_buliao'+s1).val()
		$.get("","data3="+name,function(res_item){
			name = res_item.split(":")		  
				$("#text_slect_item_name"+s1).val(name[0]);
				$("#text_slect_item_spec"+s1).val(name[1]);
				$("#text_slect_units"+s1).val(name[2]); 
				$("#text_slect_unit_price"+s1).val(name[3]) ;
				$("#text_slect_last_price"+s1).val(name[3]) ;
		})	
				 
	      document.getElementById("quantity"+s1).focus();
	}  
       
    function checkall(){                               
                                var allamount=0; 
                                var youhui_amount=document.getElementById("youhui_amount").value;
                                var yunfei_amount=document.getElementById("yunfei_amount").value;
                                var tax_rate=document.getElementById("text_slect_tax_rate").value;
                                var tax_flag=document.getElementById("text_slect_tax_flag").value;
								var all_rate = Number(1) + Number(tax_rate) ;	
							 
								
								for(var i=1 ; i < 69; i++){  
									var qua=document.getElementById("quantity"+i).value;
									var min=document.getElementById("min_order"+i).value;
									if(qua<min){
										document.getElementById("Prompt").innerHTML="下单量"+qua+"不可以小于该料最小下单量！"+min;
										document.getElementById("quantity"+i).value="";
										document.getElementById("quantity"+i).focus();
									}
									else{
										document.getElementById("Prompt").innerHTML="";
									}
								
									 }
								

								
                                
                                for(var i=1 ; i < 69; i++){   
									if (document.getElementById("line_amount" + i)==null)  {
									p=0;
										}
									else {	
										
										 var shuliang=document.getElementById("quantity"+i).value;
		                           var danjia=document.getElementById("text_slect_unit_price"+i).value;
								   if(shuliang==""){
			                         shuliang=0;
		                               }
		                           if(danjia==""){
		                           	danjia=0;
		                           }
								   if (shuliang>0   )
								   {
									   document.getElementById("line_amount"+i).value=Math.round(Number(shuliang)* Number(danjia)*1000)/1000 ;
								   }

								   var  lineamount=0 
                                   var lineamount=document.getElementById("line_amount"+i).value;
                                 
								   //var shuliang=document.getElementById("quantity"+i).value;
		                           //var danjia=document.getElementById("text_slect_unit_price"+i).value;
								   //var lineamount=shuliang*danjia;
								  
								   if( lineamount>0 )
								   {  
								   allamount=Number(allamount) + Number(lineamount);
                                   //如果input中有数据
                                   
                                  
                                   }
								    }
								} 

	 if (tax_flag=='Y')
	 {   
		 var  not_tax_amount=Number(allamount) /  Number(all_rate);
		 var tax_amount =Number(allamount) -  Number(not_tax_amount);
		document.getElementById("tax_amount").value=Math.round(Number(tax_amount)*100)/100;	 		
		 
	 document.getElementById("all_line_amount").value= Math.round(Number(not_tax_amount)*100)/100;
	 document.getElementById("order_all_amount").value= Math.round(Number(allamount)*100)/100; 
           
	 } else {
	 var tax_amount=Number(allamount) * Number(tax_rate);		
	 var  order_all_amount=Number(allamount) +  Number(tax_amount);
	 document.getElementById("tax_amount").value=Math.round(Number(tax_amount)*100)/100;	 		

	 document.getElementById("all_line_amount").value= Math.round(Number(allamount)*100)/100;
	 document.getElementById("order_all_amount").value= Math.round(Number(order_all_amount)*100)/100; 
	 }



        var a=document.getElementById("order_all_amount").value;
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
                             
                             
     function check_amount(){                               
                    var allamount=0; 
                    var order_all_amount=document.getElementById("order_all_amount").value;
                    var youhui_amount=document.getElementById("youhui_amount").value;
                                                   
        
        var a=document.getElementById("order_all_amount").value;
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

 function check56(){                               
        var allamount=0; 
        var all_line_amount=document.getElementById("all_line_amount").value;
         var tax_rate=document.getElementById("text_slect_tax_rate").value;
        var tax_amount= Number(all_line_amount) * Number(tax_rate); 	
		var order_all_amount= Number(all_line_amount) + Number(tax_amount); 	 
		document.getElementById("order_all_amount").value=Math.round(Number(order_all_amount)*100)/100;	 
		document.getElementById("tax_amount").value=Math.round(Number(tax_amount)*100)/100;	 		
		 
        var b=document.getElementById("youhui_amount").value;  
      if(parseInt(b)>parseInt(order_all_amount)){
            document.getElementById("Prompt").innerHTML="优惠金额"+b+"不可以大于总金额！"+order_all_amount;
            document.getElementById("youhui_amount").value="";
            document.getElementById("youhui_amount").focus();
        } 
        else {
            document.getElementById("Prompt").innerHTML="";
        }

       }

function  check55(){
	    var a=document.getElementById("order_all_amount").value;
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
</body>

</html>
<?
include('includes/footer.inc');
?>