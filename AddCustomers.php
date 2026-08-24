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

$Title = _('客户查询');
$ViewTopic = '客户查询';
$BookMark = '客户查询';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/CountriesArray.php');

echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/customer.png" title="' . _('客户') .
 '" alt="" />' . ' ' . _('客户信息') . '
	</p>';



if (isset($UpdateCustomerCode) and $UpdateCustomerCode != '') {
    //CreditLimit,
    $sql = "SELECT customer_code,
	               employee_num,
							customer_name,
                            term_name,
							customer_contacts,zhuce_address,
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
                       tax_name,
					   contacts_fax,
							typeid,currency_code,(select currency from currencies c where currabrev=currency_code )  currency,
							(select employee_name from hr_employees c where c.employee_num=a.employee_num ) employee_name
				FROM customers a 
				WHERE customer_code= '" . $UpdateCustomerCode." '
				     ";

    $ErrMsg = _('The customer details could not be retrieved because');
    $result = DB_query($sql, $db, $ErrMsg);
    $myrow = DB_fetch_array($result);
    $_POST['customer_code'] = $myrow['customer_code'];
    $_POST['customer_address'] = $myrow['customer_address'];
    $_POST['zhuce_address'] = $myrow['zhuce_address'];
	$_POST['invoice_address'] = $myrow['invoice_address'];
	$_POST['bank_name'] = $myrow['bank_name'];
	$_POST['bank_account'] = $myrow['bank_account'];
    $_POST['customer_name'] = $myrow['customer_name'];
      $_POST['term_name'] = $myrow['term_name'];
    $_POST['customer_contacts'] = $myrow['customer_contacts'];
    $_POST['effective_date'] = $myrow['effective_date'];
    $_POST['employee_num'] = $myrow['employee_num'];
    $_POST['employee_name'] = $myrow['employee_name'];
    $_POST['disable_date'] = $myrow['disable_date'];
    $_POST['contacts_phone'] = $myrow['contacts_phone'];
    $_POST['contacts_mail'] = $myrow['contacts_mail']; 
	 $_POST['taxpayerid'] = $myrow['taxpayerid']; 
     $_POST['tax_name'] = $myrow['tax_name']; 
	  $_POST['contacts_fax'] = $myrow['contacts_fax'];
	$_POST['postcode'] = $myrow['postcode']; 
	$_POST['currency'] = $myrow['currency']; 
	$_POST['currency_code'] = $myrow['currency_code']; 
	$_POST['enable_flag'] = $myrow['enable_flag']; 




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
				<td bgcolor="#87CEFA">' . _('客户编号') . ':&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp</td>
				<td>' . $_POST['customer_code'] . '</td>
                                <input  type="hidden" name="customer_code"  value="' . $_POST['customer_code'] . '" />
			</tr>
		        <tr>
				<td bgcolor="#87CEFA">' . _('客户名称') . ':</td>
				<td  >' . $_POST['customer_name'] . '</td>
                <td bgcolor="#87CEFA">' . _('客户付款条件') . ':</td>
				<td  >' . $_POST['term_name'] . '</td>
			</tr>
			<tr>
				<td bgcolor="#87CEFA">' . _('联系人') . ':</td>
				<td  width = 250>' . $_POST['customer_contacts'] . '</td>
				 
			 
				<td bgcolor="#87CEFA">' . _('联系电话') . ':</td>
				<td  width = 250>' . $_POST['contacts_phone'] . '</td>
				 
			</tr>
			<tr>
				<td bgcolor="#87CEFA">' . _('contacts_mail') . ':</td>
				<td>' . $_POST['contacts_mail'] . '</td>
				
			
				<td bgcolor="#87CEFA">' . _('传真') . ':</td>
				<td>' . $_POST['contacts_fax'] . '</td>

			</tr>
              <tr>
				<td bgcolor="#87CEFA">' . _('邮编') . ':</td>
				<td>' . $_POST['postcode'] . '</td>
				<td bgcolor="#87CEFA">' . _('币别') . ':</td>
				<td>' . $_POST['currency_code'] .' ' . $_POST['currency'] . '</td>
			</tr>
			 <tr>
				<td bgcolor="#87CEFA">' . _('业务员工号') . ':</td>
				<td>' . $_POST['employee_num'] . '</td>
				<td bgcolor="#87CEFA">' . _('姓名') . ':</td>
				<td>' . $_POST['employee_name'] .'</td>
                
			</tr>


				<tr>
				<td bgcolor="#87CEFA">' . _('纳税人识别号') . ':</td>
				<td>' . $_POST['taxpayerid'] . '</td>
                
                	<td bgcolor="#87CEFA">' . _('税率') . ':</td>
				<td>' . $_POST['tax_name'] . '</td>
				 
			</tr>    
                                 <tr>
				<td bgcolor="#87CEFA">' . _('注册地址') . ':</td>
				<td>' . $_POST['zhuce_address'] . '</td>
				<td bgcolor="#87CEFA">' . _('收货地址') . ':</td>
				<td>' . $_POST['customer_address'] . '</td>
                       </tr>
			 <tr>
				<td bgcolor="#87CEFA">' . _('发票地址') . ':</td>
				<td> '. $_POST['invoice_address'] . '
                               </td>           
                       </tr>
					   <tr>
				<td bgcolor="#87CEFA">' . _('银行名称') . ':</td>
				<td>' . $_POST['bank_name'] . '
                               </td>           
                       </tr>
					   <tr>
				<td bgcolor="#87CEFA">' . _('银行账号') . ':</td>
				<td>' . $_POST['bank_account'] . '
                               </td>           
                       </tr>';
                      /* <tr>
				<td>' . _('Credit Limit') . ':</td>
				<td><input type="text" class="integer" name="CreditLimit" required="required" size="16" maxlength="14" value="' . $_POST['CreditLimit'] . '" /></td>
			</tr> */
                       echo ' <tr>
				<td bgcolor="#87CEFA">' . _('生效日期') . ':</td>
				<td>' . date('Y-m-d', $_POST['effective_date']) . '</td>
				<td bgcolor="#87CEFA">' . _('是否生效') . ':</td>
				<td>' . $_POST['enable_flag'] . '</td>
		 
			</tr> 	';
                         
      
        echo '</table>';
        echo '<br />
			<div class="centre">
			 
                                <input type="submit" name="return" value="' . "关闭当前页面" . '" />&nbsp;
                                 
</div>';
    }
    echo '</div>
          </form>';
} // end of main ifs
if (isset($_POST['return'])) { 
	
  echo '<script>window.close();</script>'; 
}
include('includes/footer.inc');
?>
