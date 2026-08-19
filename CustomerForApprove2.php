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

$Title = _('客户资料维护');
$ViewTopic = '客户资料维护';
$BookMark = '客户资料维护';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/CountriesArray.php');

echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/customer.png" title="' . _('客户') .
 '" alt="" />' . ' ' . _('客户信息') . '
	</p>';

?>
<?php

if (isset($_POST['Deletecustomer'])) {
      $CancelDelete = 0;
	 $sql2 = "select * FROM so_headers_all WHERE customer_code='" . $_POST['customer_code'] . "'";
     $result2 = DB_query($sql2, $db);
	 $CancelDelete = DB_num_rows($result2);

    if ($CancelDelete == 0) { //ie not cancelled the delete as a result of above tests
        $sql = "DELETE FROM customers WHERE customer_code='" . $_POST['customer_code'] . "'";
        $result = DB_query($sql, $db);
//删除储存文件
$sql_patch = "select file_patch from customers_file where customer_code='" . $_POST['customer_code'] . "'";
$result_patch = DB_query($sql_patch, $db);
if (DB_num_rows($result_patch) == 0) {
}else{
    while ($myrow = DB_fetch_array($result_patch)) {
        if (file_exists($myrow['file_patch'])) {
            // 尝试删除文件
             if (unlink($myrow['file_patch'])) {
           
            echo "文件 {$myrow['file_patch']} 已成功删除。";
    
            } else {
    
            echo "删除文件 {$myrow['file_patch']} 失败。";
    
            }
        } else {
         echo "文件 {$myrow['file_patch']} 不存在。";
        }
    }
}
        $sql_file = "DELETE FROM customers_file WHERE customer_code='" . $_POST['customer_code'] . "'";
        $result_file = DB_query($sql_file, $db);

//删除储存文件
$sql_patcha = "select file_patch from customers_filea where customer_code='" . $_POST['customer_code'] . "'";
$result_patcha = DB_query($sql_patcha, $db);
if (DB_num_rows($result_patcha) == 0) {
}else{
    while ($myrowa = DB_fetch_array($result_patcha)) {
        if (file_exists($myrowa['file_patch'])) {
            // 尝试删除文件
             if (unlink($myrowa['file_patch'])) {
           
            echo "文件 {$myrowa['file_patch']} 已成功删除。";
    
            } else {
    
            echo "删除文件 {$myrowa['file_patch']} 失败。";
    
            }
        } else {
         echo "文件 {$myrowa['file_patch']} 不存在。";
        }
    }
}

        $sql_filea = "DELETE FROM customers_filea WHERE customer_code='" . $_POST['customer_code'] . "'";
        $result_filea = DB_query($sql_filea, $db);
        
        prnMsg(_('客户') . ' ' . $_POST['customer_code'] . ' ' . _('资料被成功删除') . ' !', 'success');


        echo '<br /><div class="centre"><a href="' . $RootPath . '/SearchCustomer.php">' . _('查询客户') . '</a></div>';
        include('includes/footer.inc');
        unset($_SESSION['customer_code']);
        exit;
    } else {
	prnMsg(_('客户') . ' ' . $_POST['customer_code'] . ' ' . _('已建立订单,无法再删除') . ' !', 'error');
	}
}
?>
<?php
if (isset($_POST['Addcustomer'])) {
    isset($_SESSION['num' . $identifier]) or die("no session");
    if ($_SESSION['num' . $identifier] == 400) {
        $_SESSION['num' . $identifier] = 500;

        $InputError = 0;
        $i = 1;
        $_POST['customer_code'] = mb_strtoupper($_POST['customer_code']);
        $sql2 = "SELECT COUNT(customer_code) FROM customers WHERE customer_code='" . $_POST['customer_code'] . "'";
        $result2 = DB_query($sql2, $db);
        $myrow2 = DB_fetch_row($result2);
        $sql = "SELECT COUNT(customer_name) FROM customers WHERE customer_name='" . $_POST['customer_name'] . "'";
        $result = DB_query($sql, $db);
        $myrow = DB_fetch_row($result);
        if (mb_strlen($_POST['customer_name']) > 40 OR mb_strlen($_POST['customer_name']) == 0) {
            $InputError = 1;
            prnMsg(_('客户名称不超过300个字且不为空'), 'error');
            $Errors[$i] = 'customer_name';
            $i++;
        } elseif ( ( ContainsIllegalCharacters($_POST['customer_name']) ) ) {
        $InputError = 1;
        prnMsg($_POST['customer_code']._('客户编号和名称不能包含特殊字符：\ ') . " . - ' &amp; + \" " . _('or a space'), 'error');
        $Errors[$i] = 'customer_code';
        $i++;
      }   elseif (mb_strlen($_POST['effective_date']) == 0) {
            $InputError = 1;
            prnMsg(_('生效日期不能为空'), 'error');
            $Errors[$i] = 'effective_date';
            $i++;
        }/* elseif (!is_numeric(filter_number_format($_POST['CreditLimit']))) {
        $InputError = 1;
        prnMsg(_('The credit limit must be numeric'), 'error');
        $Errors[$i] = 'CreditLimit';
        $i++;
    }*/

        //没有错误，则执行如下
        //当是 update 则执行update 若是add 的时候，执行insert
        if ($InputError != 1) {

            $SQL_ClientSince = FormatDateForSQL($_POST['ClientSince']);

            if (isset($_POST['Addcustomer'])) {
                // echo 'asd';
                // echo $_POST['txtName13'];
				//disable_date = '" . $_POST['disable_date'] . "',
                DB_Txn_Begin($db);
                // CreditLimit='" . $_POST['CreditLimit'] . "' ,
				// typeid='" . $_POST['typeid'] . "' ,
                 $v_date = strtotime(Date('Y-m-d H:i:s'));


                $sql = "UPDATE customers
                          SET customer_name='" . $_POST['customer_name'] . "',
                  paixu='" . $_POST['paixu'] . "',
			      customer_contacts='" . $_POST['customer_contacts'] . "',
			      contacts_phone='" . $_POST['contacts_phone'] . "',
			      contacts_mail='" . $_POST['contacts_mail'] . "',
				  currency_code='" . $_POST['currency_code'] . "',
				  dengji='" . $_POST['dengji'] . "',customer_type='" . $_POST['customer_type'] . "',
							    taxpayerid= '" . $_POST['taxpayerid'] . "',
                                tax_name= '" . $_POST['tax_name'] . "',tax_flag= '" . $_POST['tax_flag'] . "',
							  contacts_fax ='" . $_POST['contacts_fax'] . "',
								    postcode='" . $_POST['postcode'] . "',
			      customer_address ='" . $_POST['customer_address'] . "',
				  invoice_address ='" . $_POST['invoice_address'] . "',
				  bank_name ='" . $_POST['bank_name'] . "',
				  employee_num ='" . $_POST['requireemployee'] . "',
                   term_name ='" . $_POST['term_name'] . "',
				  bank_account ='" . $_POST['bank_account'] . "',
				  enable_flag ='" . $_POST['enable_flag'] . "',
                  effective_date = '" . strtotime($_POST['effective_date']) . "',

			      last_updated_by='" . $_SESSION['UserID'] . "',
			      last_update_date='" . $v_date . "'
                WHERE customer_code = '" . $_POST['customer_code'] . "'";

                $ErrMsg = _('The customer could not be updated because');
                $result = DB_query($sql, $db, $ErrMsg);
//---------------------------
//---------------------------
                DB_Txn_Commit($db);
                prnMsg(_('数据已更新！'), 'success');
                unset($_POST['customer_code']);
                unset($_POST['customer_name']);
                unset($_POST['term_name']);
                unset($_POST['customer_contacts']);
                unset($_POST['disable_date']);
                unset($_POST['effective_date']);
                unset($_POST['customer_address']);
               	unset($_POST['taxpayerid']);
				unset($_POST['invoice_address']);
				unset($_POST['requireemployee']);


				unset($_POST['bank_name']);
				unset($_POST['bank_account']);
                unset($_POST['contacts_phone']);
                unset($_POST['contacts_mail']);

               	unset($_POST['tax_name']);
				unset($_POST['contacts_fax']);
				unset($_POST['Packaging']);
			unset($_POST['spare_parts']);
			unset($_POST['transportation']);
			unset($_POST['test_ciiterion']);
			unset($_POST['iad']);
			unset($_POST['payments']);
			unset($_POST['lcm']);
			unset($_POST['postcode']);

                echo '<br />';
                echo '<br /><div class="centre"><a href="' . $RootPath . '/SearchCustomer.php">' . _('查询客户') . '</a></div>';
            }
        } else {
            prnMsg(_('Validation failed') . '. ' . _('No updates or deletes took place'), 'error');
        }
    } else {
        header('Location: SearchCustomer.php');
    }
} else {
    session_start() or die("session is not started");
    $_SESSION['num' . $identifier] = 400;
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
                       tax_name,tax_flag,
					   contacts_fax,
                       currency_code,paixu,
							typeid,dengji,customer_type
				FROM customers
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
    $_POST['dengji'] = $myrow['dengji'];
    $_POST['currency_code'] = $myrow['currency_code'];
    $_POST['paixu'] = $myrow['paixu'];
    $_POST['enable_flag'] = $myrow['enable_flag'];
    $_POST['contacts_phone'] = $myrow['contacts_phone'];
    $_POST['contacts_mail'] = $myrow['contacts_mail'];
     $_POST['customer_type'] = $myrow['customer_type'];
	$_POST['postcode'] = $myrow['postcode'];
    $_POST['tax_flag']=$myrow['tax_flag'];


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

        </select>

        <div class="text-nav">
<div class="text-nav-1"><div>客户编号</div>
<input type="text" readonly="readonly" value="<?= $_POST['customer_code'] ?>" />
                                <input  type="hidden" name="customer_code"  value="<?=$_POST['customer_code'] ?>" /></div>
                                <div class="text-nav-1"><div>业务员</div>

				<select name="requireemployee" id="">
				<?php
					$sql = "select employee_num,employee_name from hr_employees order by employee_num";
					$result = DB_query($sql,$db);
					while ($v = DB_fetch_array($result)) {
						if ($v['employee_num']==$_POST['requireemployee']) {
				?>
					<option value="<?=$v['employee_num']?>" selected="selected"><?=$v['employee_num'].$v['employee_name']?></option>
				<?php }else{?>
				<option value="<?=$v['employee_num']?>"><?=$v['employee_num'].$v['employee_name']?></option>
				<?php		}
					}
				?>
			</select>
                     </div>
                     <div class="text-nav-2 "><div>客户名称</div>
				<input  type="text" name="customer_name" readonly="readonly" required="required" autofocus="autofocus" value="<?=$_POST['customer_name']
				?>" size="42" maxlength="40" /></div>

                <div class="text-nav-1"><div>客户付款条件</div>
	            <input type="text"   name="term_name" id="text_slect_term_name" value="<?=$_POST['term_name']?>" size="26" maxlength="50"/>
					 <a class="btn btn-info btn-xs" id="btn_slect_term_name" hfre="###" title="选择">选</a>

		</div>

        <div class="text-nav-1"><div>联系人</div>
				<input  type="text" readonly="readonly" name="customer_contacts" size="26" maxlength="40"   value="<?= $_POST['customer_contacts']?>" /></div>

        <div class="text-nav-1"><div>联系电话</div>
				<input  type="text" readonly="readonly" name="contacts_phone" size="26" maxlength="40" value="<?= $_POST['contacts_phone'] ?>" /></div>

<div class="text-nav-1"><div>币别</div>
                    <select name="currency_code" id="">
                    <?php
                    $sql ="select currabrev,currency from currencies order by currency_id";
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

        <div class="text-nav-2"><div>E_mail</div>
				<input  placeholder="e.g. user@domain.com" type="text" name="contacts_mail" size="26" maxlength="40" readonly="readonly"  value="<?= $_POST['contacts_mail'] ?>" /></div>
        <div class="text-nav-1"><div>传真</div>
		        <input type="text" readonly="readonly" name="contacts_fax"  size="26" maxlength="60"value="<?=$_POST['contacts_fax'] ?>"  />
                               </div>
        <div class="text-nav-1"><div>邮编</div>
				<input type="text" readonly="readonly"  name="postcode"  size="26" maxlength="60"value="<?=$_POST['postcode'] ?>"  />
                               </div>
        <!-- <div class="text-nav-1"><div>客户等级</div>
                    <select name="dengji" id="">
                    <?php
                    $sql ="select dengji,dengji_name from  customer_level ";
                    $result = DB_query($sql, $db);
                    while ($v = DB_fetch_array($result)) {
                        if ($v['dengji'] == $_POST['dengji']) {
                            ?>
                                <option value="<?= $v['dengji'] ?>" selected="selected"><?= $v['dengji_name'] ?></option>
                            <?php } else { ?>
                                <option value="<?= $v['dengji'] ?>"><?= $v['dengji_name'] ?></option>
                            <?php
                            }
                        }
                        ?>
                    </select>
                </div> -->
				<div class="text-nav-1"><div>客户分类</div>
                    <!-- <select name="customer_type" id="">
                    <?php
                    $sql ="select  customer_type from  customer_type ";
                    $result = DB_query($sql, $db);
                    while ($v = DB_fetch_array($result)) {
                        if ($v['customer_type'] == $_POST['customer_type']) {
                            ?>
                                <option value="<?= $v['customer_type'] ?>" selected="selected"><?= $v['customer_type'] ?></option>
                            <?php } else { ?>
                                <option value="<?= $v['customer_type'] ?>"><?= $v['customer_type'] ?></option>
                            <?php
                            }
                        }
                        ?>
                    </select> -->
                    <input type="text" name="customer_type" readonly="readonly" size="62" maxlength="60"value="<?=$_POST['customer_type'] ?>"  />
                </div>
                <div class="text-nav-2"><div>客户地址</div>
				<input type="text" name="customer_address" readonly="readonly" size="62" maxlength="60"value="<?=$_POST['customer_address'] ?>"  />
                               </div>
                               <div class="text-nav-1"><div>税别</div>

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
		 <div class="text-nav-1"><div>是否含税</div>

			<select name="tax_flag" id="">
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
        <div class="text-nav-1"><div>纳税人识别号</div>
			    <input type="text" name="taxpayerid" readonly="readonly" size="62" maxlength="60"value="<?=$_POST['taxpayerid'] ?>"  />
                               </div>


                               <div class="text-nav-2"><div>发票地址</div>
				<input type="text" name="invoice_address"  size="62" maxlength="60"value="<?=$_POST['invoice_address'] ?>"  />
                       </div>
                       <div class="text-nav-1"><div>银行名称</div>
				<input type="text" name="bank_name" readonly="readonly" size="62" maxlength="60"value="<?=$_POST['bank_name'] ?>"  />
                               </div>
                               <div class="text-nav-1"><div>银行账号</div>
				<input type="text" name="bank_account" readonly="readonly" size="62" maxlength="60"value="<?=$_POST['bank_account'] ?>"  />
                               </div>
                <div class="text-nav-1"><div>生效日期</div>
				<input type="text" onfocus="WdatePicker()" readonly="readonly"  alt="<?php echo $_SESSION['DefaultDateFormat']  ?> " name="effective_date"   size="10" maxlength="10" title=" effective_date " value="<?=date('Y-m-d', $_POST['effective_date']) ?>" /></div>

                <div class="text-nav-1"><div>是否生效</div>
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
			

                       </div>

        </table>
        <br />
        
        <?php

    //商务附件
$sql2 = "SELECT
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
$sql2 = "SELECT
file_patch,creation_date,created_by,file_name
FROM customers_filea
where  customer_code = '" .$UpdateCustomerCode."'";
$result2 = DB_query($sql2, $db);
if (DB_num_rows($result2) == 0) {
   unset($result2);
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
				<input type="submit" name="Addcustomer" value="更新客户" />&nbsp;
				<input type="submit" name="Deletecustomer" value="删除客户" onclick="return confirm(\'' . _('Are You Sure?') . '\');" />
                   <input type="submit" name="return" value="返回上一层" />&nbsp;

</div>
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
    header('Location: SearchCustomer.php');
}
?>
<?php
include('includes/footer.inc');
?>
