<?php

ob_start();
/* $Id: Vendors.php 6338 2013-09-28 05:10:46Z daintree $ */

include('includes/session.inc');
if (isset($_POST['customercode'])) {
    $UpdateCustomerCode = $_POST['customercode'];
} else {
    $UpdateCustomerCode = '';
}
//echo $_POST['customercode'];
//echo  $_GET['UpdateCustomerCode'];

$Title = _('ContactLogs Maintenance');
/* webERP manual links before header.inc */
$ViewTopic = 'ContactLogs';
$BookMark = 'ContactLogs';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/CountriesArray.php');
include('includes/CurrenciesArray.php');
echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/customer.png" title="' . _('Customer') .
 '" alt="" />' . ' ' . _('新增联系人') . '
	</p>';
//echo $_SESSION['UserID'];

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
                //不增加联系人
                $sql = "INSERT INTO contact_log (
							customer_id,
							contact_type,
							product,
							degree,
							remark,
							keypoint,
							nexttime,
							
                            salesman,
                            contact_date
							
							
							)
				VALUES ('" .$UpdateCustomerCode  . "',
						'" . $_POST['contact_type'] . "',
						'" . $_POST['product'] . "',
						'" . $_POST['degree'] . "',
                       
						'" . $_POST['remark'] . "',
						'" . $_POST['keypoint'] . "',
					    '" . $_POST['nexttime'] . "',
                       
                          '" .$_SESSION['UserID']  . "',
                        '" .  $v_date . "'
						)";
            



            $ErrMsg = _('This vendor could not be added because');
            $result = DB_query($sql, $db, $ErrMsg);
            prnMsg(_('联系记录新建成功'), 'success');
            unset($_POST['customercode']);
            unset($_POST['contact_type']);
            unset($_POST['product']);
            unset($_POST['degree']);

            unset($_POST['remark']);
            unset($_POST['keypoint']);
            unset($_POST['nexttime']);
            
            //unset($_POST['CreditLimit']);
            echo '<br />';
        }
    } else {
        prnMsg(_('新增联系记录失败！'), 'error');
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
				<td>' . _('联系方式') . ':</td>
				<td><input ' . (in_array('contact_type', $Errors) ? 'class="inputerror"' : '' ) . ' type="text" name="contact_type"  autofocus="autofocus" value="' . $_POST['contact_type'] . '" size="16" maxlength="40" /></td>
			</tr>

		     <tr>
				<td>' . _('产品') . ':</td>
				<td><input ' . (in_array('product', $Errors) ? 'class="inputerror"' : '' ) . ' type="text" name="product"  autofocus="autofocus" value="' . $_POST['product'] . '" size="1" maxlength="40" /></td>
			</tr>
			
			<tr>
				<td>' . _('成熟度') . ':</td>
				<td><input ' . (in_array('degree', $Errors) ? 'class="inputerror"' : '' ) . ' type="text" name="degree" size="16" maxlength="40" value="' . $_POST['degree'] . '" /></td>
			</tr>
                      
			<tr>
				<td>' . _('备注') . ':</td>
				<td><input ' . (in_array('remark', $Errors) ? 'class="inputerror"' : '' ) . ' type="text" name="remark" size="16" maxlength="40" value="' . $_POST['remark'] . '" /></td>
			</tr>
			<tr>
				<td>' . _('跟进要点') . ':</td>
				<td><input ' . (in_array('keypoint', $Errors) ? 'class="inputerror"' : '' ) . ' type="text" name="keypoint" size="16" maxlength="40" value="' . $_POST['keypoint'] . '" /></td>
			</tr>

			<tr>
				<td>' . _('下次预约') . ':</td>
				<td><input ' . (in_array('nexttime', $Errors) ? 'class="inputerror"' : '' ) . ' type="text" name="nexttime"  size="42" maxlength="40" value="' . $_POST['nexttime'] . '" /></td>
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
