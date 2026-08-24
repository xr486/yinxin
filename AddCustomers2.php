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

$Title = _('客户维护');
$ViewTopic = '客户维护';
$BookMark = '客户维护';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/CountriesArray.php');

echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/customer.png" title="' . _('客户') .
 '" alt="" />' . ' ' . _('客户信息') . '
	</p>';


if (isset($_POST['Deletecustomer'])) {
    $CancelDelete = 0;
   
    if ($CancelDelete == 0) { //ie not cancelled the delete as a result of above tests
        $sql = "DELETE FROM customers WHERE customer_code='" . $_POST['customer_code'] . "'";
        $result = DB_query($sql, $db);
        prnMsg(_('客户') . ' ' . $_POST['customer_code'] . ' ' . _('资料被成功删除') . ' !', 'success');
        include('includes/footer.inc');
        unset($_SESSION['customer_code']);
        exit;
        echo '<br /><div class="centre"><a href="' . $RootPath . '/SearchCustomer.php">' . _('查询客户') . '</a></div>';
    }
}

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
        }  elseif ( ( ContainsIllegalCharacters($_POST['customer_name']) OR mb_strpos($_POST['customer_name'], ' '))or( ContainsIllegalCharacters($_POST['customer_code']) OR mb_strpos($_POST['customer_code'], ' '))) {
        $InputError = 1;
        prnMsg(_('客户代码和名称不能包含特殊字符：\ ') . " . - ' &amp; + \" " . _('or a space'), 'error');
        $Errors[$i] = 'customer_code';
        $i++;
      }elseif (mb_strlen($_POST['customer_contacts']) == 0) {
            $InputError = 1;
            prnMsg(_('联系人不为空！'), 'error');
            $Errors[$i] = 'customer_contacts';
            $i++;
        } elseif (mb_strlen($_POST['contacts_phone']) == 0) {
            $InputError = 1;
            prnMsg(_('联系电话不为空！'), 'error');
            $Errors[$i] = 'contacts_phone';
            $i++;
        } elseif (mb_strlen($_POST['effective_date']) == 0) {
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
                if($_POST['disable_date'] == ''){
                $sql = "UPDATE customers 
                          SET customer_name='" . $_POST['customer_name'] . "',
			      customer_contacts='" . $_POST['customer_contacts'] . "',
			      contacts_phone='" . $_POST['contacts_phone'] . "',
			      contacts_mail='" . $_POST['contacts_mail'] . "',
			      customer_address ='" . $_POST['customer_address'] . "', 
                              effective_date = '" . strtotime($_POST['effective_date']) . "',
                             
			      last_updated_by='" . $_SESSION['UserID'] . "',
			      last_update_date='" . $v_date . "'
                WHERE customer_code = '" . $_POST['customer_code'] . "'";}
                else{ $sql = "UPDATE customers 
                          SET customer_name='" . $_POST['customer_name'] . "',
			      customer_contacts='" . $_POST['customer_contacts'] . "',
			      contacts_phone='" . $_POST['contacts_phone'] . "',
			      contacts_mail='" . $_POST['contacts_mail'] . "',
			      customer_address ='" . $_POST['customer_address'] . "', 
                              effective_date = '" . strtotime($_POST['effective_date']) . "',
                              disable_date  = '" . strtotime($_POST['disable_date']) . "',
			      last_updated_by='" . $_SESSION['UserID'] . "',
			      last_update_date='" . $v_date . "'
                WHERE customer_code = '" . $_POST['customer_code'] . "'";}
                $ErrMsg = _('The customer could not be updated because');
                $result = DB_query($sql, $db, $ErrMsg);
//---------------------------
//---------------------------   
                DB_Txn_Commit($db);
                prnMsg(_('数据已更新！'), 'success');
                unset($_POST['customer_code']);
                unset($_POST['customer_name']);
                unset($_POST['customer_contacts']);
                unset($_POST['disable_date']);
                unset($_POST['effective_date']);
                //unset($_POST['CreditLimit']);
                unset($_POST['customer_address']);
                unset($_POST['contacts_phone']);
                unset($_POST['contacts_mail']);

                echo '<br />';
                echo '<br /><div class="centre"><a href="' . $RootPath . '/SearchCustomer.php">' . _('查询客户') . '</a></div>';
            }
        } else {
            prnMsg(_('Validation failed') . '. ' . _('No updates or deletes took place'), 'error');
        }
    } else {
        header('Location: SearchCustmoer.php');
    }
} else {
    session_start() or die("session is not started");
    $_SESSION['num' . $identifier] = 400;
}
if (isset($UpdateCustomerCode) and $UpdateCustomerCode != '') {
    //CreditLimit,
    $sql = "SELECT customer_code,
							customer_name,
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
                                                        disable_date,
							typeid
				FROM customers
				WHERE customer_code= " . "'$UpdateCustomerCode'";

    $ErrMsg = _('The customer details could not be retrieved because');
    $result = DB_query($sql, $db, $ErrMsg);
    $myrow = DB_fetch_array($result);
    $_POST['customer_code'] = $myrow['customer_code'];
    $_POST['customer_address'] = $myrow['customer_address'];
	$_POST['invoice_address'] = $myrow['invoice_address'];
	$_POST['bank_name'] = $myrow['bank_name'];
	$_POST['bank_account'] = $myrow['bank_account'];
    $_POST['customer_name'] = $myrow['customer_name'];
    $_POST['customer_contacts'] = $myrow['customer_contacts'];
    $_POST['effective_date'] = $myrow['effective_date'];
    //$_POST['CreditLimit'] = $myrow['CreditLimit'];
    $_POST['disable_date'] = $myrow['disable_date'];
    $_POST['contacts_phone'] = $myrow['contacts_phone'];
    $_POST['contacts_mail'] = $myrow['contacts_mail']; 
    //$_POST['typeid'] = $myrow['typeid'];
    if (!isset($_GET['delete'])) {
        if (!isset($_POST['effective_date'])) {
            $_POST['effective_date'] = Date($_SESSION['DefaultDateFormat']);
        }
        echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">'
        . '<input type="hidden" name = "identifier" value ="' . $identifier . '">';
        echo '<div>';
        echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
        echo '<table  class="selection" id="SignFrame">
			<tr><td valign="top">';
        echo '</td>
                        </tr>';

        echo '          <tr>
				<td>' . _('客户代码') . ':&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp</td>
				<td>' . $_POST['customer_code'] . '</td>
                                <input  type="hidden" name="customer_code"  value="' . $_POST['customer_code'] . '" />
			</tr>
		        <tr>
				<td>' . _('客户名称') . ':</td>
				<td>' . $_POST['customer_name'] . '</td>
			</tr>
			<tr>
				<td>' . _('联系人') . ':</td>
				<td>' . $_POST['customer_contacts'] . '</td>
				 
			</tr>
           
			<tr>
				<td>' . _('contacts_phone') . ':</td>
				<td>' . $_POST['contacts_phone'] . '</td>
				 
			</tr>
			<tr>
				<td>' . _('contacts_mail') . ':</td>
				<td>' . $_POST['contacts_mail'] . '</td>
				 
			</tr>
                         <tr>
				<td>' . _('地址') . ':</td>
				<td>' . $_POST['customer_address'] . '</td>
			          
                       </tr>
			 <tr>
				<td>' . _('发票地址') . ':</td>
				<td> '. $_POST['invoice_address'] . '
                               </td>           
                       </tr>
					   <tr>
				<td>' . _('银行名称') . ':</td>
				<td>' . $_POST['bank_name'] . '
                               </td>           
                       </tr>
					   <tr>
				<td>' . _('银行账号') . ':</td>
				<td>' . $_POST['bank_account'] . '
                               </td>           
                       </tr>';
                      /* <tr>
				<td>' . _('Credit Limit') . ':</td>
				<td><input type="text" class="integer" name="CreditLimit" required="required" size="16" maxlength="14" value="' . $_POST['CreditLimit'] . '" /></td>
			</tr> */
                       echo ' <tr>
				<td>' . _('生效日期') . ':</td>
				<td>' . date('Y-m-d', $_POST['effective_date']) . '</td>
		 
			</tr> 	';
                        if($_POST['disable_date'] == ''){
                             echo'
                        <tr>
				<td>' . _('失效日期') . ':</td>		
				<td>' .  '' . '</td>
			 
			</tr> 	 
                        ';
                        }
                        else{
                      echo'
                        <tr>
				<td>' . _('失效日期') . ':</td>
				<td>' . date('Y-m-d', $_POST['disable_date']) . '</td>
		 
			</tr> 	
			 	
                        ';}
      
        echo '</table>';
        echo '<br />
			<div class="centre">
			 
                                <input type="submit" name="return" value="' . "返回上一层" . '" />&nbsp;
                                 
</div>';
    }
    echo '</div>
          </form>';
} // end of main ifs
if (isset($_POST['return'])) {
    header('Location: SearchSOList.php');
}
include('includes/footer.inc');
?>
