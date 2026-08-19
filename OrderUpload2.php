<?php

if(isset($_GET['data'])){
	
	 include_once("connect.php"); 
	 $sql = "select a.* ,(select tax_mount from tax_set b where a.tax_name=b.tax_name) tax_rate from customers a where enable_flag='Y' and customer_code = '".$_GET['data']."'";
	 $result_num = mysql_query($sql, $db);
	 $res = mysql_fetch_assoc($result_num);
	 echo $res['customer_name'].':'.$res['customer_address'].':'.$res['customer_contacts'].':'.$res['currency_code'].':'.$res['tax_name'].':'.$res['tax_rate'];
	 return ;
 } 


putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");
ob_start();
include('includes/session.inc');
$Title = _('订单整批上传确认');

$ViewTopic= '订单整批上传确认';
$BookMark = '订单整批上传确认';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

unset($result);

	
	if (isset($_POST['Save'])) {
		$errorflag = 0;
      

	for ($i=1;$i<=$_POST['flag'];$i++){
		if ($_POST['status'.$i]<>''){
			if  ($_POST["remark".$i] <>'') {	
				 prnMsg(_('有错误,不能选择！'), 'error');
				$errorflag=1;							
			}  
		
			       
    }
  }
 

	$date = date('Ymd');
			
		if ($errorflag == 0) {

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


		date_default_timezone_set('Asia/Shanghai'); 	
         $date  = Date('Y-m-d H:i:s');		    
         $need_date = strtotime($_POST['need_date']);
		 $qianding_date = strtotime($_POST['qianding_date']);

			DB_Txn_Begin($db);
			$time = time();
		  $flag=0;
		  $line=0;
            for ($i=1;$i<=$_POST['flag'];$i++)
	    {
	       
          // echo $_POST['status'.$i];
           if ($_POST['status'.$i]<>''){
               
			  	     $flag=$flag+1;
				 
		               $line=$line+1;
					   
				  $_POST['wip_entity_name'.$i]= $OrderNum.'-'.str_pad($line,3,0,STR_PAD_LEFT);    
					 if ($_POST['stockid'.$i]=='') {
	               $_POST['stockid'.$i]=$_POST['wip_entity_name'.$i];
	              }


				  $sql3 = " select * from sf_item_no where   item_no ='".$_POST['stockid'.$i]."' " ;		
				 $result3 = DB_query($sql3, $db);
				 
		         if (DB_num_rows($result3)==0) {
                     $sql = "insert into sf_item_no(item_no,item_name,item_desc,units,item_category1,item_type,
						creation_date,created_by,last_update_date,last_updated_by)
						select '".$_POST['stockid'.$i]."',item_name,item_desc,uom,'成品','F',
						'".$time."','".$_SESSION['UserID']."','".$time."','".$_SESSION['UserID']."'
						from so_lines_upload  where line_id ='".$_POST['line_id'.$i]."' ";
						
						$result = DB_query($sql,$db);

				 }

					 $sql = "insert into so_lines_all(order_number,line,line_remark,uom,price,other_price,zhidao_price,
						quantity,stockid,item_name,line_amount,need_date,
						creation_date,created_by,last_update_date,last_updated_by)
						select '".$OrderNum."','".$line."',line_remark,uom,'".$_POST['unitprice'.$i]."','0','0',
						'".$_POST['quantity'.$i]."','".$_POST['stockid'.$i]."',item_name,'".$_POST['line_amount'.$i]."'
						,need_date,
						'".$time."','".$_SESSION['UserID']."','".$time."','".$_SESSION['UserID']."'
						from so_lines_upload  where line_id ='".$_POST['line_id'.$i]."' ";
						
						$result = DB_query($sql,$db);
						$order_payment_amount = $order_payment_amount + $_POST['line_amount'.$i];
                        $order_yingshou_amount=$order_payment_amount-$_POST['youhui_amount'];

						}
		   
           
					//echo $sql;
                
		}
		}
            if($flag==0){
                $msg = '请选中更改项';
            prnMsg($msg, 'error');
            }else{     
				
				
	   $status='待签核';
	 
		               $line=$line+1;

     if ($_POST['yunfei_amount']=='') {
			 $_POST['yunfei_amount'] = 0;
		 }

		  if ($_POST['customer_order_number']=='') {
	   $_POST['customer_order_number']=$OrderNum;
	  }
	   if ($_POST['youhui_amount']=='') {
						$_POST['youhui_amount'] = 0;
		                }

		$sql = "insert into so_headers_all
(customer_order_number,order_type,yewu,all_line_amount,term_name,yunfei_amount,tax_amount,tax_flag,ship_address,ship_city,coycode,youhui_amount,order_number,customer_code,customer_contact,need_date,qianding_date,status,currency_code,tax_rate,tax_name,delivery_date,project,jiaohuotiaojian,youxiaoxing1,youxiaoxing2,baozhuang,zhiliangbaozheng,mainfeifuwu,order_all_amount,creation_date,header_remark,created_by,last_update_date,last_updated_by)values('".$_POST['customer_order_number']."','".$_POST['order_type']."','".$_POST['yewu']."','".$_POST['all_line_amount']."','".$_POST['term_name']."','".$_POST['yunfei_amount']."','".$_POST['tax_amount']."','".$_POST['tax_flag']."','".$_POST['ship_address']."','".$_POST['ship_city']."','".$_POST['coycode']."','".$_POST['youhui_amount']."','".$OrderNum."','".$_POST['customercode']."','".$_POST['customer_contact']."'
,'".$need_date."','".$qianding_date."','".$status."','".$_POST['currency_code']."','".$_POST['tax_rate']."','".$_POST['tax_name']."','".strtotime($_POST['delivery_date'])."','".$_POST['project']."','".$_POST['jiaohuotiaojian']."','".$_POST['youxiaoxing1']."','".$_POST['youxiaoxing2']."','".$_POST['baozhuang']."','".$_POST['zhiliangbaozheng']."','".$_POST['mainfeifuwu']."','".$_POST['order_all_amount']."','".$time."','".$_POST['Header_Remark']."','".$_SESSION['UserID']."','".$time."','".$_SESSION['UserID']."')";
			$result = DB_query($sql,$db);
    DB_Txn_Commit($db);
    prnMsg('订单上传完成',success);
    
	header("Location: SussCreate1.php?OrderNum=".$OrderNum);
  
 
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
 <?php 
 if(isset($OrderNum)){
    }else{
 ?>
<div id="CanvasDiv">
	<div id="BodyDiv">
		<div id="BodyWrapDiv">
			<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="订单整批上传确认" alt="订单整批上传确认">订单整批上传确认</p>
			<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST"><input type="hidden" name="time" value="<?=$time?>">
				<div>
			
				
	<?php
	 if (!isset($_POST['qianding_date'])) {
      $_POST['qianding_date'] = Date('Y-m-d');
     }
	  if (!isset($_POST['need_date'])) {
      $_POST['need_date'] = Date('Y-m-d');
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
	 $_POST['yewu']=$_SESSION['UserID'];
 
?>
				<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
				<table class="selection">
		 
		<div class="text-nav">

			<div class="text-nav-1 required">
			<div>客户简称:</div>  
		 <input type="text" readonly="readonly" name="customercode" id="text_slect_customer" value="<?=$_POST['customercode']?>" size="10" maxlength="25"   onblur="sel()"/>
	
			<image class="select_img" src="img/search.png" id="btn_slect_customer"/>
			</div>
			 
			<div class="text-nav-1">
			<div>联系人:</div>			 
			
            <input readonly="readonly" type="text"   name="customer_contact" id="text_slect_customer_contact" value="<?=$_POST['customer_contact']?>" size="10" maxlength="10"/></div> 
			<div class="text-nav-1">
			<div>币别:</div>			 
			
            <input readonly="readonly" type="text"   name="currency_code" id="text_slect_currency_code" value="<?=$_POST['currency_code']?>" size="5" maxlength="10"/></div> 
			     <div  class="text-nav-1 required"> <div>交货日期:</div>
			<input type="text" name="delivery_date" maxlength="20" size="12" required="required" value="<?=$_POST['delivery_date']?>" 
onfocus="WdatePicker() "></div>  
		 
		<div class="text-nav-2 required">
			<div>出货地址:</div>
		 <input  type="text" name="ship_address" id="text_slect_customer_address" value="<?=$_POST['ship_address']?>" size="90" maxlength="250" /></div>
		<div class="text-nav-1  required">
			<div>客户订单:</div>			 
			<input  type="text"  name="customer_order_number" value="<?=$_POST['customer_order_number']?>" size="10" maxlength="100"/></div> 
			<div class="text-nav-1 required">
			<div>签订日期:</div>
			<input type="text" name="qianding_date" maxlength="20" size="12" required="required" value="<?=$_POST['qianding_date']?>" 
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
				 <div class="text-nav-1 required">
			<div>需求日期:</div>
			<input type="text" name="need_date" maxlength="20" size="10" required="required" value="<?=$_POST['need_date']?>" onfocus="WdatePicker() "></div>
		
				<div class="text-nav-2">
			<div>订单备注:</div> 
			 <input type="text"  maxlength="200" size="60" name="Header_Remark"  value="<?=$_POST['Header_Remark']?>"  /> </div>
			
				 
			<div  class="text-nav-1 required">
			<div>税别</div> 		  
			 <input type="text" readonly="readonly" onblur="check56()" name="tax_name" id="text_slect_tax_name" value="<?=$_POST['tax_name']?>" size="10" maxlength="100"/>
					
	</div>   
	<div  class="text-nav-1 required">
			<div>税率</div> 		  
			 <input type="text" readonly="readonly" onblur="check56()" name="tax_rate" id="text_slect_tax_rate" value="<?=$_POST['tax_rate']?>" size="10" maxlength="100"/> </div>   	 
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
			<div>优惠:</div>
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
</select></div> 
<div class="text-nav-1">
			<div>合计数量:</div>
		 <input  type="text"  class="number" name="all_qty" id="all_qty" value="<?=$_POST['all_qty']?>" size="10" maxlength="10"  onblur="check55()"/></div>
 <input readonly="readonly" type="hidden" name="customername" id="text_slect_name" value="<?=$_POST['customername']?>" size="60" maxlength="150" />
	  
							 
   </div>
		

	</table>

	<input type="hidden" name="PageOffset" value="1"/><br/>
	<div class="centre"> 
             <p id="Prompt" style="color: red;font-size: 20px"></p>
          </div>
	<?php
		if (1==1) {
	?>
             <div style="overflow:scroll">   
        <table cellpadding="2" id="purchase_table" class="selection">
        <tr id="list-top">
		<th>选择</th>
		<th width="150" bgcolor="#87CEFA">料号</th>
					<th width="150" bgcolor="#87CEFA">料号名称</th> 
					<th width="150" bgcolor="#87CEFA">规格型号</th> 
					<th width="5" bgcolor="#87CEFA">单位</th>		 
					<th bgcolor="#87CEFA">数量</th>	 
				    <th bgcolor="#87CEFA">单价 </th>
				    <th bgcolor="#87CEFA">金额</th>	
					<th width="5" bgcolor="#87CEFA">需求日期</th>		 
					<th width="30">备注</th> 
        </tr>
            <?php
            $sql=" select * from so_lines_upload a  where  a.created_by = '" . $_SESSION['UserID'] . "' ";
            //echo $sql;
            $result = DB_query($sql,$db); 
            $i=1;
			$linenum=DB_num_rows($result);
			 
			
            if  (DB_num_rows($result) == 0) {
                unset($result);
                prnMsg('请确认是否有上传最新文件',error);
            } else {
            
            while  ($myrow = DB_fetch_array($result)) {

			 
					
 
              
              
                ?>
			 	 
		 
		<tr > <td><input type="checkbox" name="status<?=$i?>" /></td>
			<td><input type="text" onblur="checkall()" name="stockid<?=$i?>"  value="<?=$myrow['item_no'] ?>" size="19" maxlength="34"/></td> 
           
            <td><?=$myrow['item_name']  ?></td>
            <td><?=$myrow['item_desc']  ?></td>
            <td><?=$myrow['uom']  ?></td>
		
			
            
            <td><input type="text" name="quantity<?=$i?>"  id="quantity<?=$i?>" step="1"  min="0"  onkeyup="this.value= this.value.match(/\d+(\.\d{0,2})?/) ? this.value.match(/\d+(\.\d{0,2})?/)[0] : ''" value="<?=$myrow['quantity'] ?>" size="8" maxlength="34"  onblur="checkall()"/></td>     
            <td><input type="text" name="unitprice<?=$i?>" step="1"  min="0"  onkeyup="this.value= this.value.match(/\d+(\.\d{0,5})?/) ? this.value.match(/\d+(\.\d{0,5})?/)[0] : ''"   id="text_slect_unit_price<?=$i?>"  value="<?=$myrow['price'] ?>" size="8" maxlength="34" onblur="checkall()"/></td>   
            <td><input type="text" onkeyup="this.value= this.value.match(/\d+(\.\d{0,3})?/) ? this.value.match(/\d+(\.\d{0,3})?/)[0] : ''" id="line_amount<?=$i?>" name="line_amount<?=$i?>"  value="<?= ($myrow['quantity']*$myrow['price']) ?>" size="8" maxlength="34"   /></td>       
			<td><?=date('Y-m-d',$myrow['need_date'])  ?></td>  
			
			<td><?=$myrow['line_remark']  ?> 
			<input type="hidden"   name="line_id<?=$i?>"  value="<?=$myrow['line_id'] ?>" size="8" maxlength="34"/>  
			
			 </td>  
        </tr>
        <?php
              $i=$i+1;
            }
					}
          ?>
			    <tr>
            <td colspan="11"><p><input type="checkbox" name="selectall" onclick="checkalls(this.form);"/>全选/反选</p></td></tr>
						<td ><input type="hidden" name="flag" value="<?=$i-1?>" size="15" maxlength="45"/></td> 
           </tr>
		</table>	 </div>			
			<div class="centre">
	            <input type="submit" name="Save" value="保存"> &nbsp;
			</div>
	<?php
		}
	?>
            <input type="hidden" name="idcount" id='idcount' value="1"/>
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
<?php
}
?>
<script type="text/javascript">
    $(document).ready(function(){

        $('.divToilet table tr td a').click(function(){
            $(this).parent('td').toggleClass('highlight');
            if(!($(this).parent('td').hasClass('highlight'))) {
                $(this).next().val('0');
            }else {
                $(this).next().val('1');
            }
        });
       
     $('#btn_slect_customer2').dialog({
            title:'选择客户',
            width: '1050px',
            height: 470,
            content:'url:BtnSearchCustomer888.php?fwValue=&cat=<?=$_SESSION['SalesMan']?>',
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

	$(function(){
		$( "#text_slect_customer" ).autocomplete({
			source: "autosearchcustomer.php",
			minLength: 2,
			autoFocus: true
		});
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
   
		})	
	}  


	function checkalls(thisform)
	{for(var i=0;i<thisform.elements.length;i++)
	{if(thisform.elements[i].type=="checkbox"&&thisform.elements[i].checked==false&&thisform.elements[i].name!="selectall")
	{
		 thisform.elements[i].checked=true;
		 
		}
	else if(thisform.elements[i].type=="checkbox"&&thisform.elements[i].checked==true&&thisform.elements[i].name!="selectall")
	{thisform.elements[i].checked=false;}
	} 
	}


 function checkall(){                               
                                var allamount=0; 
                                var youhui_amount=document.getElementById("youhui_amount").value;
                                var yunfei_amount=document.getElementById("yunfei_amount").value;
                                var tax_rate=document.getElementById("text_slect_tax_rate").value;
                                var tax_flag=document.getElementById("text_slect_tax_flag").value;
								var all_rate = Number(1) + Number(tax_rate) ;	
							    var all_qty=0;
								 

								
                                
                                for(var i=1 ; i < 269; i++){   
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
								   {     document.getElementById("line_amount"+i).value=Math.round(Number(shuliang)* Number(danjia)*1000)/1000 ; 
									   all_qty=  Number(all_qty) + Number(shuliang);
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

	
     document.getElementById("all_qty").value= all_qty;
	 if (tax_flag=='N')
	 {
	  var tax_amount=Number(allamount) * Number(tax_rate);
	  var no_tax_amount=allamount;
	  var  han_tax_amount=Number(allamount) +  Number(tax_amount);
	 } else {
	  var no_tax_amount=Number(allamount) / Number(all_rate);
	  var tax_amount= Number(allamount) - Number(no_tax_amount);
	  var  han_tax_amount=Number(allamount);
	 }
	
	 	 document.getElementById("all_line_amount").value= Math.round(Number(no_tax_amount)*100)/100;		
		document.getElementById("tax_amount").value= Math.round(Number(tax_amount)*100)/100;
		document.getElementById("order_all_amount").value= Math.round(Number(han_tax_amount)*100)/100; 
  
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
                             
     function check_amount(){                               
                    var allamount=0; 
                    var header_amount=document.getElementById("header_amount").value;
                    var youhui_amount=document.getElementById("youhui_amount").value;
                                                  
            var order_yingshou_amount=Number(header_amount)-Number(youhui_amount);                      
						
        document.getElementById("order_yingshou_amount").value=Math.round(Number(order_yingshou_amount)*1000)/1000;
        document.getElementById("text_order_invoice_amount").value=Math.round(Number(order_yingshou_amount)*1000)/1000;
        
        var a=document.getElementById("header_amount").value;
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
        var header_amount2=document.getElementById("header_amount2").value;
        var youhui_amount=document.getElementById("youhui_amount").value;					
		var yunfei_amount=document.getElementById("yunfei_amount").value;
        var tax_amount=document.getElementById("tax_amount").value;
		
       var header_amount=Number(header_amount2)+Number(yunfei_amount)+Number(tax_amount); 
        var order_yingshou_amount=Number(header_amount)-Number(youhui_amount);                                          
        var order_yingshou_amount=Number(header_amount)-Number(youhui_amount);                      
		document.getElementById("header_amount").value=Math.round(Number(header_amount)*1000)/1000;	 				
        document.getElementById("order_yingshou_amount").value=Math.round(Number(order_yingshou_amount)*1000)/1000;
        document.getElementById("text_order_invoice_amount").value=Math.round(Number(order_yingshou_amount)*1000)/1000;
        
        var a=document.getElementById("header_amount").value;
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

function  check55(){
	    var a=document.getElementById("header_amount").value;
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

