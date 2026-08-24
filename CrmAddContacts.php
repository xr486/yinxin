<?php

ob_start();
/* $Id: Vendors.php 6338 2013-09-28 05:10:46Z daintree $ */

include('includes/session.inc');
if (isset($_POST['customercode'])) {
    $UpdateCustomerCode = $_POST['customercode'];
} else {
    $UpdateCustomerCode = '';
}
echo $_POST['customercode'];
echo $UpdateCustomerCode;

$Title = _('新增联系人');
/* webERP manual links before header.inc */
$ViewTopic = '新增联系人';
$BookMark = '新增联系人';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/customer.png" title="' . _('Customer') .
 '" alt="" />' . ' ' . _('新增联系人') . '
	</p>';


if (isset($Errors)) {
    unset($Errors);
}
$Errors = array();


if (isset($_POST['AddVendor'])) {

    //initialise no input errors assumed initially before we test
    $InputError = 0;
    $i = 1;
    if ($InputError != 1) {

        if (isset($_POST['AddVendor'])) { //it is a new vendor
            
                //CreditLimit,  '" . $_POST['CreditLimit'] . "',
                 $v_date = strtotime(Date('Y-m-d H:i:s'));
                
                $sql = "INSERT INTO contacts (
							customer_code,
							person_name,
							person_sex,
							person_job,
							person_property,
							tel_no,
							phone_no,
						    person_qq,
							person_weixin,
							person_wang,
							person_mail )
				VALUES ('" . $UpdateCustomerCode . "',
						'" . $_POST['person_name'] . "',
						'" . $_POST['person_sex'] . "',
						'" . $_POST['person_job'] . "',
                       
						'" . $_POST['person_property'] . "',
						'" . $_POST['tel_no'] . "',
					    '" . $_POST['phone_no'] . "',
						'" . $_POST['person_qq'] . "',
						'" . $_POST['person_weixin'] . "',
						'" . $_POST['person_wang'] . "',
                        '" . $_POST['person_mail'] . "'
                        
						)";
            



            $ErrMsg = _('客户地址增加完成');
            $result = DB_query($sql, $db, $ErrMsg);
            prnMsg(_('联系人新建成功'), 'success');
         
            unset($_POST['person_name']);
            unset($_POST['person_sex']);
            unset($_POST['person_job']);

            unset($_POST['person_property']);
            unset($_POST['tel_no']);
            unset($_POST['phone_no']);
            unset($_POST['person_mail']);
			unset($_POST['person_qq']);
			unset($_POST['person_weixin']);
			unset($_POST['person_wang']);
            //unset($_POST['CreditLimit']);
            echo '<br />';
        }
    } else {
        prnMsg(_('新增联系人失败！'), 'error');
    }
}


/* DebtorNo could be set from a post or a get when passed as a parameter to this page */

if (isset($_POST['vendor_code'])) {
    $vendor_code = $_POST['vendor_code'];
} elseif (isset($_GET['vendor_code'])) {
    $vendor_code = $_GET['vendor_code'];
}

if (isset($_POST['Edit'])) {
    $Edit = $_POST['Edit'];
} elseif (isset($_GET['Edit'])) {
    $Edit = $_GET['Edit'];
} else {
    $Edit = '';
}

if (isset($_POST['Add'])) {
    $Add = $_POST['Add'];
} elseif (isset($_GET['Add'])) {
    $Add = $_GET['Add'];
}

if (!isset($_GET['delete'])) {
//DebtorNo exists - either passed when calling the form or from the form itself

  

    echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
    echo '<div>';
    echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
    echo '<input type="hidden" name="customercode" value="' .  $_POST['customercode']  . '" />';
    echo '<table class="selection">
			<tr><td valign="top">';

    echo '</select></td>
			</tr>';
  
    echo '<tr>
				<td>' . _('客户名称') . ':</td>
				<td><input ' . (in_array('customer_name', $Errors) ? 'class="inputerror"' : '' ) . ' type="text" readonly="readonly" name="customer_name"  autofocus="autofocus" value="' . $_POST['customer_name'] . '" size="16" maxlength="40" /></td>
			</tr>
          <tr>
				<td>' . _('联系人') . ':</td>
				<td><input ' . (in_array('person_name', $Errors) ? 'class="inputerror"' : '' ) . ' type="text" name="person_name"  autofocus="autofocus" value="' . $_POST['person_name'] . '" size="16" maxlength="40" /></td>
			</tr>

		     <tr>

			 <td>性别</td>
			    <td>
                <select name="person_sex">
				<option value="男"> 男</option>
                <option value="女"> 女</option>
                </select></td>
 
			</tr>
			
			<tr>
				<td>' . _('职位') . ':</td>
				<td><input ' . (in_array('person_job', $Errors) ? 'class="inputerror"' : '' ) . ' type="text" name="person_job" size="16" maxlength="40" value="' . $_POST['person_job'] . '" /></td>
			</tr>
                      
			<tr>
				<td>' . _('属性') . ':</td>
				<td><input ' . (in_array('person_property', $Errors) ? 'class="inputerror"' : '' ) . ' type="text" name="person_property" size="16" maxlength="40" value="' . $_POST['person_property'] . '" /></td>
			</tr>
			<tr>
				<td>' . _('电话') . ':</td>
				<td><input ' . (in_array('tel_no', $Errors) ? 'class="inputerror"' : '' ) . ' type="text" name="tel_no" size="16" maxlength="40" value="' . $_POST['tel_no'] . '" /></td>
			</tr>

			<tr>
				<td>' . _('手机') . ':</td>
				<td><input ' . (in_array('phone_no', $Errors) ? 'class="inputerror"' : '' ) . ' type="text" name="phone_no"  size="42" maxlength="40" value="' . $_POST['phone_no'] . '" /></td>
			</tr>
			<tr>
				<td>' . _('QQ') . ':</td>
				<td><input ' . (in_array('person_qq', $Errors) ? 'class="inputerror"' : '' ) . ' type="text" name="person_qq"  size="42" maxlength="40" value="' . $_POST['person_qq'] . '" /></td>
			</tr>
			<tr>
				<td>' . _('微信') . ':</td>
				<td><input ' . (in_array('person_weixin', $Errors) ? 'class="inputerror"' : '' ) . ' type="text" name="person_weixin"  size="42" maxlength="40" value="' . $_POST['person_weixin'] . '" /></td>
			</tr>
			<tr>
				<td>' . _('旺旺') . ':</td>
				<td><input ' . (in_array('person_wang', $Errors) ? 'class="inputerror"' : '' ) . ' type="text" name="person_wang"  size="42" maxlength="40" value="' . $_POST['person_wang'] . '" /></td>
			</tr>
            <tr>
				<td>' . _('E_mail') . ':</td>
				<td><input ' . (in_array('person_mail', $Errors) ? 'class="inputerror"' : '' ) . ' type="text" name="person_mail"  size="42" maxlength="40" value="' . $_POST['person_mail'] . '"  placeholder="' . _('e.g. user@domain.com') . '" /></td>
			</tr>
            ';
                       
		
        
		




    echo '</table>';

    echo'</td></tr></table>';


    echo '<div class="centre">
				<input type="submit" name="AddVendor" value="' . _('新增') . '" />&nbsp;
				<input type="Reset" name="Reset" value="' . _('Reset') . '" />&nbsp;
                                    <input type="submit" name="return" value="' . _('返回') . '" />
			</div>';


    echo '</div>
          </form>';
} // end of main ifs


if (isset($_POST['return'])) {
    header('Location: CRMMyCustomer.php');
}

include('includes/footer.inc');
?>
