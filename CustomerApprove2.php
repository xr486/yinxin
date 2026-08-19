<?php

/* $Id: customers.php 6338 2013-09-28 05:10:46Z daintree $ */
 ob_start();
include('includes/session.inc');
if (isset($_GET['identifier'])) {
    $_POST['identifier'] = $_GET['identifier'];
}

if (!isset($_POST['identifier'])) {
    $identifier = date('U');
} else {
    $identifier = $_POST['identifier'];
}
if (isset($_GET['UpdateCustomerCode'])) {
    $UpdateCustomerCode = $_GET['UpdateCustomerCode'];
} else {
    $UpdateCustomerCode = '';
}

$Title = _('客户审核');
$ViewTopic = '客户审核';
$BookMark = '客户审核';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/CountriesArray.php');

echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/customer.png" title="' . _('客户') .
 '" alt="" />' . ' ' . _('客户信息') . '
	</p>';

?>
<?php


$creation_date = strtotime(Date('Y-m-d H:i:s'));

if (isset($_POST['approve'])){
    $sql = "update customers set customers_status = '已签核',c_approve_date=". $creation_date .",c_approve_remark='". $_POST['approve_remark']  ."',c_approved_by='" . $_SESSION['UserID'] . "' ,
    effective_date =". $creation_date ."
    where customer_code = '".$_POST['customer_code']."' ";

    $result = DB_query($sql,$db);

    prnMsg('签核成功！',success);
    header("Location: CustomerApprove.php");
    
}
if (isset($_POST['reject'])){
    $sql = "update customers set customers_status = '已拒签',c_approve_date=". $creation_date .",c_approve_remark='". $_POST['approve_remark']  ."',c_approved_by='" . $_SESSION['UserID'] . "' where customer_code = '".$_POST['customer_code']."' ";
    $result = DB_query($sql,$db);
    prnMsg('拒签成功！',success);
    header("Location: CustomerApprove.php");
    
}
?>
<?php



if (isset($_GET['customer_site_id'])) {
  $sql = " update customer_site set enable_flag='N' 
         where  customer_site_id= '" . $_GET['customer_site_id'] . "' ";
  $result = DB_query($sql, $db);
  prnMsg(_('客户联系信息失效成功！'), 'success');
    $customer_code = $_GET['UpdateCustomerCode'];    
		header("location:AddCustomer2.php?UpdateCustomerCode=$customer_code"); 
}
//删除商务附件
if (isset($_GET['file_name'])   ) {
    if (file_exists($_GET['file_patch'])) {

        // 尝试删除文件
    
        if (unlink($_GET['file_patch'])) {
           
            echo "文件 {$_GET['file_patch']} 已成功删除。";
    
        } else {
    
            echo "删除文件 {$_GET['file_patch']} 失败。";
    
        }
    
    } else {
    
        echo "文件 {$_GET['file_patch']} 不存在。";
    
    }

    $sql = "delete from   customers_file 
    where  file_name= '" . $_GET['file_name'] . "' and creation_date= '" . $_GET['creation_date'] . "' and  customer_code= '" . $_GET['UpdateCustomerCode'] . "'   ";
    $result = DB_query($sql,$db);

  
  
 prnMsg(_('删除成功！'), 'success');
 echo '<meta http-equiv="refresh" content="0; url=' . $RootPath .
                 '/CustomerApprove2.php?UpdateCustomerCode='. $_GET['UpdateCustomerCode'] . '" />';
 //DB_Txn_Commit($db);

  
}
//删除经营附件
if (isset($_GET['file_namea'])   ) {
    if (file_exists($_GET['file_patch'])) {

        // 尝试删除文件
    
        if (unlink($_GET['file_patch'])) {
           
            echo "文件 {$_GET['file_patch']} 已成功删除。";
    
        } else {
    
            echo "删除文件 {$_GET['file_patch']} 失败。";
    
        }
    
    } else {
    
        echo "文件 {$_GET['file_patch']} 不存在。";
    
    }

        $sql = "delete from   customers_filea 
        where  file_name= '" . $_GET['file_namea'] . "' and creation_date= '" . $_GET['creation_date'] . "' and  customer_code= '" . $_GET['UpdateCustomerCode'] . "'   ";
        $result = DB_query($sql,$db);
 
      
      
     prnMsg(_('删除成功！'), 'success');
     echo '<meta http-equiv="refresh" content="0; url=' . $RootPath .
                     '/CustomerApprove2.php?UpdateCustomerCode='. $_GET['UpdateCustomerCode'] . '" />';
     //DB_Txn_Commit($db);
    
      
 }



?>

<?php
if (isset($UpdateCustomerCode) and $UpdateCustomerCode != '') { 
	?>
 <?php
    $sql = "SELECT customer_code,
	               employee_num,
							customer_name,
                            term_name,
							customer_contacts,
							contacts_phone,
							contacts_mail,
							customer_address,
							zhuce_address,
							invoice_address,
							bank_account,
							bank_name,
							created_by,
							creation_date,
							last_updated_by,
							last_update_date,
                            effective_date,
                            enable_flag,
					   postcode,
                       taxpayerid,
                       tax_name,
					   contacts_fax,
							typeid,currency_code,(select count(*) from so_delivery_headers_all b where a.customer_code=b.customer_code) ship_flag
				FROM customers a
				WHERE customer_code= " . "'$UpdateCustomerCode'";

    $ErrMsg = _('The customer details could not be retrieved because');
    $result = DB_query($sql, $db, $ErrMsg);
    $myrow = DB_fetch_array($result);
	$sql1 ="select employee_name from hr_employees where employee_num='" . $myrow['employee_num'] . "'";
	$ErrMsg1 = _('The customer details could not be retrieved because');
    $result1 = DB_query($sql1, $db, $ErrMsg1);
    $myrow1 = DB_fetch_array($result1);
   
  
   
    $_POST['customer_code'] = $myrow['customer_code'];
    $_POST['customer_address'] = $myrow['customer_address'];
    $_POST['zhuce_address'] = $myrow['zhuce_address'];
    $_POST['term_name'] = $myrow['term_name'];
	$_POST['invoice_address'] = $myrow['invoice_address'];
	$_POST['bank_name'] = $myrow['bank_name'];
	$_POST['requireemployee'] = $myrow['employee_num'];
	$_POST['employeename'] = $myrow1['employee_name'];
   $_POST['taxpayerid'] = $myrow['taxpayerid'];
	$_POST['bank_account'] = $myrow['bank_account'];
    $_POST['customer_name'] = $myrow['customer_name'];
    $_POST['customer_contacts'] = $myrow['customer_contacts'];
    $_POST['effective_date'] = $myrow['effective_date'];
    $_POST['currency_code'] = $myrow['currency_code'];
    $_POST['enable_flag'] = $myrow['enable_flag'];
    $_POST['contacts_phone'] = $myrow['contacts_phone'];
    $_POST['contacts_mail'] = $myrow['contacts_mail']; 
    $_POST['ship_flag'] = $myrow['ship_flag']; 
	$_POST['postcode'] = $myrow['postcode']; 


	
    $_POST['tax_name']=$myrow['tax_name'];
	$_POST['contacts_fax']=$myrow['contacts_fax'];
    //$_POST['typeid'] = $myrow['typeid'];
	
    if (!isset($_GET['delete'])) {
		
        if (!isset($_POST['effective_date'])) {
            $_POST['effective_date'] = Date($_SESSION['DefaultDateFormat']);
        }
	?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>新建杂项入库</title>
<link rel="shortcut icon" href="/JXC/favicon.ico"/>
<link rel="icon" href="/JXC/favicon.ico"/>
<meta http-equiv="Content-Type" content="application/html; charset=utf-8"/>
<link href="/css/xenos/default.css" rel="stylesheet" type="text/css"/>
<script type="text/javascript" src ="/JXC/javascripts/miscfunctions.js"></script>
<script type="text/javascript" src ="/JXC/javascripts/wdatepicker.js"></script>
<script type="text/javascript">var basepath='/JXC/statics/base/images';</script>
<script type="text/javascript" src="/JXC/statics/base/js/metvar.js"></script>
<script type="text/javascript" src="/JXC/statics/base/js/jQuery1.7.2.js"></script>
<script type="text/javascript" src="/JXC/statics/base/js/uploadify/jquery.uploadify.v2.1.4.min.js"></script>
<script type="text/javascript" src="/JXC/statics/base/js/iframes.js"></script>
<script type="text/javascript" src="/JXC/statics/base/js/cookie.js"></script>
<script type="text/javascript" src="/JXC/statics/base/js/jquery.livequery.js"></script>



<script src="/JXC/javascript/jquery-1.7.2.min.js"></script>
<script src="/JXC/javascript/lhgdialog.min.js?self=true&skin=chrome"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
<script src="/javascript/bootstrap.min.js"></script>

<script type="text/javascript">
/*ajax执行*/
var lang = 'cn';
var metimgurl='/JXC/statics/base/images/';
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
        <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name = "identifier" value ="<?=$identifier ?>">
        <div>
        <input type="hidden" name="FormID" value="<?=$_SESSION['FormID'] ?>" />
        <table class="selection" id="SignFrame">
		 
        <div class="text-nav">
            <div class="text-nav-1"> 
                <div>客户代码</div>
			    <input type="text" name="customer_code"  value="<?=$_POST['customer_code'] ?>" />
            </div>
			<div class="text-nav-2"> 
                <div>客户名称</div>
				<?php
				if ($_POST['ship_flag'] >= 1) { 
					 ?>

				 
				 <input  type="text" name="customer_name" required="required" autofocus="autofocus" value="<?=$_POST['customer_name']
				?>" size="42" maxlength="40" /> </div>

				<?php 
				 } else { 
				?>

				<input  type="text" name="customer_name" required="required" autofocus="autofocus" value="<?=$_POST['customer_name']
				?>" size="42" maxlength="40" /></div>

				<?php 
				 }  
				?>
                
                <div class="text-nav-1"> 
                <div>客户付款条件</div>
		
			<select name="term_name" id="">
				<?php
					$sql = "select term_name from term_set order by termid";
					$result = DB_query($sql,$db);
					while ($v = DB_fetch_array($result)) {
						if ($v['term_name']==$_POST['term_name']) {
				?>
					<option value="<?=$v['term_name']?>" selected="selected"><?=$v['term_name']?></option>
				<?php }else{?>
				<option value="<?=$v['term_name']?>"><?=$v['term_name']?></option>
				<?php		}
					}
				?>
			</select>
		</div> 
                
        <div class="text-nav-1"> 
                <div>联系人</div>
				<input  type="text" name="customer_contacts" size="26" maxlength="40"   value="<?= $_POST['customer_contacts']?>" /></div>
			 
				<div class="text-nav-1"> 
                <div>联系电话</div>
				<input  type="text" name="contacts_phone" size="26" maxlength="40" value="<?= $_POST['contacts_phone'] ?>" /></div>
              
                
				
				<div class="text-nav-1"> 
                <div>传真</div>
				<input type="text" name="contacts_fax"  size="26" maxlength="60"value="<?=$_POST['contacts_fax'] ?>"  />
                               </div>   
                               <div class="text-nav-1"> 
                <div>邮编</div>
				<input type="text" name="postcode"  size="26" maxlength="60"value="<?=$_POST['postcode'] ?>"  />
                               </div> 
                               <div class="text-nav-1"> 
                <div>币别</div>	
				
                    <select name="currency_code" id="">
                    <?php
                    $sql ="select currabrev,currency from  currencies  order by currabrev";
                    $result = DB_query($sql, $db);
                    while ($v = DB_fetch_array($result)) {
                        if ($v['currabrev'] == $_POST['currency_code']) {
                            ?>
                                <option value="<?= $v['currabrev'] ?>" selected="selected"><?= $v['currency'] ?></option>
                            <?php } else { ?>
                                <option value="<?= $v['currabrev'] ?>"><?= $v['currency'] ?></option>
                            <?php
                            }
                        }
                        ?>
                    </select>
                </div>			   
                <div class="text-nav-1"> 
                <div>业务员工号</div>
				<input type="text"   name="requireemployee" id="text_slect_employee" value="<?=$_POST['requireemployee']?>" size="6" maxlength="25"/>
                       <a class="btn btn-info btn-xs" id="btn_slect_employee" hfre="###" title="选择">选</a> </div>
			 
                       <div class="text-nav-1"> 
                <div>业务员姓名</div>
				<input   type="text" name="employeename" id="text_slect_employename" value="<?=$_POST['employeename']?>" size="10" maxlength="50"/></div>
                <div class="text-nav-2"> 
                <div>注册地址</div>
				<input type="text" name="zhuce_address" size="62" maxlength="60"value="<?=$_POST['zhuce_address'] ?>"  />
                               </div>   
                <div class="text-nav-2"> 
                <div>收货地址</div>
				<input type="text" name="customer_address" size="62" maxlength="60"value="<?=$_POST['customer_address'] ?>"  />
                               </div>         
                               <div class="text-nav-1"> 
                <div>E_mail</div>
				<input  placeholder="e.g. user@domain.com" type="text" name="contacts_mail" size="26" maxlength="40"  value="<?= $_POST['contacts_mail'] ?>" /></div>
                <div class="text-nav-2"> 
                <div>纳税人识别号</div>
				<input type="text" name="taxpayerid"  size="62" maxlength="60"value="<?=$_POST['taxpayerid'] ?>"  />
                               </div>         
                               <div class="text-nav-1"> 
                <div>税别</div>
		
			<select name="tax_name" id="">
				<?php
					$sql = "select tax_name from tax_set order by tax_id";
					$result = DB_query($sql,$db);
					while ($v = DB_fetch_array($result)) {
						if ($v['tax_name']==$_POST['tax_name']) {
				?>
					<option value="<?=$v['tax_name']?>" selected="selected"><?=$v['tax_name']?></option>
				<?php }else{?>
				<option value="<?=$v['tax_name']?>"><?=$v['tax_name']?></option>
				<?php		}
					}
				?>
			</select>
		</div>   
                      
				
                         <div class="text-nav-2"> 
                <div>发票地址</div>
				<input type="text" name="invoice_address"  size="62" maxlength="60"value="<?=$_POST['invoice_address'] ?>"  /></div>
				<div class="text-nav-1"> 
                <div>是否生效</div>
				   	<select name="enable_flag" id="">
				<?php
					 
						if ( $_POST['enable_flag']=='Y') {
				?>
					<option value="Y" selected="selected">是</option>
					<option value="N" >否</option>
				<?php }else{?>
				 <option value="N" selected="selected">否</option>
					<option value="Y" >是</option>
				<?php		 
					}
				?>
			</select></div>
                       </tr>
                       <div class="text-nav-2"> 
                <div>银行名称</div>
				<input type="text" name="bank_name" size="62" maxlength="60"value="<?=$_POST['bank_name'] ?>"  />
                               </div>   
							   <div class="text-nav-1" style="display:none"> 
                <div>生效日期</div>
				<input type="text" onfocus="WdatePicker()"  alt="<?php echo $_SESSION['DefaultDateFormat']  ?> " name="effective_date"   size="10" maxlength="10" title=" effective_date " value="<?=date('Y-m-d', $_POST['effective_date']) ?>" /></div>
                <div class="text-nav-2"> 
                <div>银行账号</div>
				<input type="text" name="bank_account" size="62" maxlength="60" value="<?=$_POST['bank_account'] ?>"  /></div>  
                <div class="text-nav-2"> 
                <div>签核意见备注</div>
				<input type="text"  style="background-color:#FFF68F" size="50" maxlength="200" name="approve_remark" value="<?=$_POST['approve_remark'] ?>"> </div>         
                </div>
 
                 
        </table>
        
<?php
//商务附件
$sql2 = "SELECT customer_code,
file_patch,creation_date,created_by,file_name
FROM customers_file
where  customer_code = '" .$UpdateCustomerCode."'";
$result2 = DB_query($sql2, $db);
if (DB_num_rows($result2) == 0) {
   unset($result2);
 //  prnMsg(_('无附件'), 'info');
} else {
   echo '<p class="page_title_text">
<img src="' . $RootPath . '/css/' . $Theme . '/images/customer.png" title="' . _('订单头信息') .
'" alt="" />' . ' ' . _('订单商务附件信息') . '
</p>';
   echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '"><input type="hidden" name = "identifier" value ="' . $identifier . '">';
   echo '<div>';
   echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
   echo '<table class="selection" align="center" >';
   $tableheader = '<tr>

                               <th width =150 >' . '附件名称' . '</th>
                               <th width =190 >' . '上传时间' . '</th>
                               <th width =80 >' . '上传人员' . '</th>
                               <th  width =50>' . '下载' . '</th>
                               <th  width =50>' . '操作' . '</th>


       </tr>';

   echo $tableheader;
   $RowCounter = 1;
   $k = 0; //row colour counter
   while ($myrow = DB_fetch_array($result2)) {
       if ($k == 1) {
           echo '<tr class="EvenTableRows">';
           $k = 0;
       } else {
           echo '<tr class="EvenTableRows">';
           $k++;
       }

// read_pdf('./999.pdf');
// <td><a href="' . $RootPath . '/Quote3.php?New=Yes&Updateorder_number=' . $myrow['file_patch'] . '">预览</td>
       echo '
             <td>' . $myrow['file_name'] . '</td>
             <td>' . date('Y-m-d H:i:s',$myrow['creation_date']) . '</td>
             <td>' . $myrow['created_by'] . '</td>
             <td><a href="' . $RootPath . '/' . $myrow['file_patch'] . '" target="_blank">' . '下载' . '</td>
             <td><a href="' . $RootPath . '/CustomerApprove2.php?UpdateCustomerCode='.$myrow['customer_code'] .'&file_name=' . $myrow['file_name'] . '&creation_date='.$myrow['creation_date'].'&file_patch='.$myrow['file_patch'].'" >删除</td>



</tr>';
       $RowCounter++;
       If ($RowCounter == 500) {
           $RowCounter = 1;
           echo $tableheader;
       }
   }
   echo '</table> ';


   echo '</div>
 </form>';
}
?>
<?php
//经营附件
$sql3 = "SELECT customer_code,
file_patch,creation_date,created_by,file_name
FROM customers_filea
where  customer_code = '" .$UpdateCustomerCode."'";
$result3 = DB_query($sql3, $db);
if (DB_num_rows($result3) == 0) {
   unset($result3);
 //  prnMsg(_('无附件'), 'info');
} else {
   echo '<p class="page_title_text">
<img src="' . $RootPath . '/css/' . $Theme . '/images/customer.png" title="' . _('订单头信息') .
'" alt="" />' . ' ' . _('订单经营附件信息') . '
</p>';
   echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '"><input type="hidden" name = "identifier" value ="' . $identifier . '">';
   echo '<div>';
   echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
   echo '<table class="selection" align="center" >';
   $tableheader = '<tr>

                               <th width =150 >' . '附件名称' . '</th>
                               <th width =190 >' . '上传时间' . '</th>
                               <th width =80 >' . '上传人员' . '</th>
                               <th  width =50>' . '下载' . '</th>
                               <th  width =50>' . '操作' . '</th>



       </tr>';

   echo $tableheader;
   $RowCounter = 1;
   $k = 0; //row colour counter
   while ($myrow3 = DB_fetch_array($result3)) {
       if ($k == 1) {
           echo '<tr class="EvenTableRows">';
           $k = 0;
       } else {
           echo '<tr class="EvenTableRows">';
           $k++;
       }

// read_pdf('./999.pdf');
// <td><a href="' . $RootPath . '/Quote3.php?New=Yes&Updateorder_number=' . $myrow['file_patch'] . '">预览</td>
       echo '
             <td>' . $myrow3['file_name'] . '</td>
             <td>' . date('Y-m-d H:i:s',$myrow3['creation_date']) . '</td>
             <td>' . $myrow3['created_by'] . '</td>
             <td><a href="' . $RootPath . '/' . $myrow3['file_patch'] . '" target="_blank">' . '下载' . '</td>
             
             <td><a href="' . $RootPath . '/CustomerApprove2.php?UpdateCustomerCode='.$myrow3['customer_code'] .'&file_namea=' . $myrow3['file_name'] . '&creation_date='.$myrow3['creation_date'].'&file_patch='.$myrow['file_patch'].'" >删除</td>


</tr>';
       $RowCounter++;
       If ($RowCounter == 500) {
           $RowCounter = 1;
           echo $tableheader;
       }
   }
   echo '</table> ';


   echo '</div>
 </form>';
}
?>
 <div class="centre">
 <input type="submit" name="approve" value="核准" />&nbsp;&nbsp;&nbsp;<input type="submit" name="reject" value="拒绝" />&nbsp;&nbsp;&nbsp;
                                <!-- <input type="submit" name="return" value="返回上一层" />&nbsp; -->
                                 
</div>
                    <!-- <?php
                    $i = 0; //Line Item Array pointer
                    echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES, 'UTF-8') . '" method="post">
					<input type="hidden" name = "identifier" value ="' .$identifier . '">';
                    echo '<div>';
                    echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
                    //用隐藏域保存请购单表号
                    echo ' <input type="hidden" class="text"  name="customer_code" value="' . $_POST['customer_code'] . '" />';
                    
                    echo '<br /> ';
                    echo ' 
					<table class="selection">
                      <tr> <th  bgcolor="#87CEFA" width="30">联系人</th>
                    <th  bgcolor="#87CEFA" width="30">联系电话</th>
								     <th bgcolor="#87CEFA" width="40">职位</th> 
								      <th bgcolor="#87CEFA" width="10">是否生效</th>								 
                     <th   width=>' . _('删除') . '</th>
                
                      </tr>';

                    $k = 0;


                    $sql12 = "select * from customer_site  where  customer_code = '" .  $_POST['customer_code'] . "' ";

                    $resultline = DB_query($sql12, $db);

                    while ($myrow = DB_fetch_array($resultline)) {

                      if ($k == 1) {
                        echo '<tr class="EvenTableRows">';
                        $k = 0;
                      } else {
                        echo '<tr class="OddTableRows">';
                        $k++;
                      }
					  echo ' <td><input  readonly="readonly"    type="text"  name="customer_contacts' . $i . '" size="18"  value="' . $myrow['customer_contacts']  . '" /></td> ';
                      echo ' <td><input     type="text"  name="contacts_phone' . $i . '" size="18"  value="' . $myrow['contacts_phone']  . '" /></td> ';
                      echo ' <td><input     style="" type="text"  name="zhiwei' . $i . '" size="18"  value="' . $myrow['zhiwei']  . '" /></td> ';
                      echo ' <td><input  readonly="readonly" type="text"  name="enable_flag' . $i . '" size="2"  value="' . $myrow['enable_flag']  . '" /></td> ';
                     
                      echo '  <td><a href="' . $RootPath . '/AddCustomer2.php?UpdateCustomerCode=' . $_POST['customer_code']. '&customer_site_id=' . $myrow['customer_site_id'] . '"  >删除</td>

	                   <input type="hidden" name="customer_site_id' . $myrow['customer_site_id'] . '" value="' . $i . '" /></td> ';

					

                      echo '
 
                     </tr>';
                      $i++;
                      
                    }
                    echo '</table> 
   
	
	             
       
                    </div>
                       </form>';

              
                    ?>

                 
                   

        <br /> -->
        <!-- <input type="submit" name="approve" value="核准" />&nbsp;&nbsp;&nbsp;<input type="submit" name="reject" value="拒绝" />&nbsp;&nbsp;&nbsp; -->
<?php
    }
?>
    </div>
       </form>

<?php

} 
?>

<div id="FooterDiv">
		<div id="FooterWrapDiv">
		 	 
		</div>
	</div>
</div>
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
        <?php for($i=1;$i<=50;$i++){?> 
        $('#btn_slect_buliao<?=$i?>').dialog({
            title:'选择料号',
            width: '830px',
            height: 470,
            content:'url:SearchAllItem.php?fwValue=<?=$i?>&cat=<?=$_POST['insubinventory']?>',
            init:function(){
			    this.content.document.getElementById('cat').value = 'buliao';
                this.content.document.getElementById('fwValue').value = '<?=$i?>';
            }
        });
		<?php }?>

	 


		$('#btn_slect_employee').dialog({
            title:'选择员工',
            width: '550px',
            height: 470,
            content:'url:BtnSearchemployee.php?fwValue=&cat=buliao',
            init:function(){
			    this.content.document.getElementById('cat').value = 'buliao';
                this.content.document.getElementById('fwValue').value = '';
            }
        });
        
         $('#btn_slect_term').dialog({
            title:'选择付款条件',
            width: '550px',
            height: 470,
            content:'url:BtnSearchterm.php?fwValue=&cat=buliao',
            init:function(){
			    this.content.document.getElementById('cat').value = 'buliao';
                this.content.document.getElementById('fwValue').value = '';
            }
        });


 

		$('#btn_slect_insubinventory').dialog({
            title:'选择调入仓库',
            width: '550px',
            height: 470,
            content:'url:BtnSearchinsubinventory.php?fwValue=&cat=<?=$_POST['outsubinventory']?>',
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
</body>

</html>
<?php
if (isset($_POST['return'])) {
    header('Location: CustomerApprove.php');
}
?>
<?php
include('includes/footer.inc');
?>
