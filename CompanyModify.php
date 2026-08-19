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
if (isset($_GET['UpdateEmployee_Num'])) {
    $UpdateEmployee_Num = $_GET['UpdateEmployee_Num'];
} else {
    $UpdateEmployee_Num = '';
}

$Title = _('公司维护');
$ViewTopic = '公司维护';
$BookMark = '公司维护';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/CountriesArray.php');

echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/customer.png" title="' . _('公司') .
 '" alt="" />' . ' ' . _('公司资料修改') . '
	</p>';


if (isset($_POST['Deleteemployee'])) {
    $CancelDelete = 0;

    if ($CancelDelete == 0) { //ie not cancelled the delete as a result of above tests
        $sql = "DELETE FROM companies WHERE coycode='" . $_POST['coycode'] . "'";
        $result = DB_query($sql, $db);
        prnMsg(_('公司') . ' ' . $_POST['coycode'] . ' ' . _('资料被成功删除') . ' !', 'success');
        echo '<br /><div class="centre"><a href="' . $RootPath . '/SearchCompany.php">' . _('查询公司') . '</a></div>';
        include('includes/footer.inc');
        unset($_SESSION['coycode']);
        
		exit;
    }
}

if (isset($_POST['Addemployee'])) {
    isset($_SESSION['num' . $identifier]) or die("no session");
    if ($_SESSION['num' . $identifier] == 400) {
        $_SESSION['num' . $identifier] = 500;

        $InputError = 0;
        $i = 1;
        $_POST['coycode'] = mb_strtoupper($_POST['coycode']);
        $sql2 = "SELECT COUNT(coycode) FROM companies WHERE coycode='" . $_POST['coycode'] . "'";
        $result2 = DB_query($sql2, $db);
        $myrow2 = DB_fetch_row($result2);
        $sql = "SELECT COUNT(coyname) FROM companies WHERE coyname='" . $_POST['coyname'] . "'";
        $result = DB_query($sql, $db);
        $myrow = DB_fetch_row($result);
        if ( mb_strlen($_POST['coyname']) == 0) {
            $InputError = 1;
            prnMsg(_('公司名称不能为空'), 'error');
            $Errors[$i] = 'coyname';
            $i++;
        }  elseif ( ( ContainsIllegalCharacters($_POST['coyname']) OR mb_strpos($_POST['coyname'], ' '))or( ContainsIllegalCharacters($_POST['coycode']) OR mb_strpos($_POST['coycode'], ' '))) {
        $InputError = 1;
        prnMsg(_('公司简称和公司名称不能包含特殊字符：\ ') . " . - ' &amp; + \" " . _('or a space'), 'error');
        $Errors[$i] = 'employee_num';
        $i++;
      } elseif (mb_strlen($_POST['telephone']) == 0) {
            $InputError = 1;
            prnMsg(_('联系电话不为空！'), 'error');
            $Errors[$i] = 'telephone';
            $i++;
        }  

        //没有错误，则执行如下
        //当是 update 则执行update 若是add 的时候，执行insert
        if ($InputError != 1) {

            $SQL_ClientSince = FormatDateForSQL($_POST['ClientSince']);
             $v_date =time();     
                DB_Txn_Begin($db);          
                   $sql = "UPDATE companies
                   SET coyname='" . $_POST['coyname'] . "', 
                  taxpayerid='" . $_POST['taxpayerid'] . "',
				  email='" . $_POST['email'] . "', 
				  coyname_en='" . $_POST['coyname_en'] . "', 
				  postcode='" . $_POST['postcode'] . "', 
				  bank_account='" . $_POST['bank_account'] . "', 
				  faren='" . $_POST['faren'] . "', 
				  bank_name='" . $_POST['bank_name'] . "',
				  address='" . $_POST['address'] . "',
				  bank_name='" . $_POST['bank_name'] . "',
			      telephone='" . $_POST['telephone'] . "',
			      last_updated_by='" . $_SESSION['UserID'] . "',
			      last_update_date='" . $v_date . "'
                WHERE coycode = '" . $_POST['coycode'] . "'"; 
                $ErrMsg = _('The customer could not be updated because');
                $result = DB_query($sql, $db, $ErrMsg);
//---------------------------
//---------------------------
                DB_Txn_Commit($db);
                prnMsg(_('数据已更新！'), 'success');
                unset($_POST['coycode']);
                unset($_POST['coyname']); 
                echo '<br />';
                echo '<br /><div class="centre"><a href="' . $RootPath . '/SearchCompany.php">' . _('查询公司') . '</a></div>';
            
        } else {
            prnMsg(_('验证失败') . '. ' . _('不能更新'), 'error');
        }
    } else {
        header('Location: SearchCompany.php');
    }
} else {
    session_start() or die("session is not started");
    $_SESSION['num' . $identifier] = 400;
}
if (isset($UpdateEmployee_Num) and $UpdateEmployee_Num != '') {
    //CreditLimit,
    $sql = "SELECT creation_date,created_by,coyname,coyname_en,coycode,postcode,taxpayerid,bank_account,bank_name,faren,address,telephone,email
 FROM companies
WHERE coycode= " . "'$UpdateEmployee_Num'";

    $ErrMsg = _('The customer details could not be retrieved because');
    $result = DB_query($sql, $db, $ErrMsg);
    $myrow = DB_fetch_array($result);
    $_POST['coycode'] = $myrow['coycode'];
    $_POST['coyname'] = $myrow['coyname']; 
    $_POST['coyname_en'] = $myrow['coyname_en']; 
	$_POST['taxpayerid'] = $myrow['taxpayerid'];
	$_POST['bank_name'] = $myrow['bank_name'];
	$_POST['bank_account'] = $myrow['bank_account'];
	$_POST['telephone'] = $myrow['telephone'];
    $_POST['email'] = $myrow['email'];
    $_POST['postcode'] = $myrow['postcode'];
    $_POST['faren'] = $myrow['faren'];
    $_POST['address'] = $myrow['address'];
    $_POST['creation_date'] = $myrow['creation_date'];
    $_POST['created_by'] = $myrow['created_by'];

    if (!isset($_GET['delete'])) {
        if (!isset($_POST['effective_date'])) {
            $_POST['effective_date'] = Date($_SESSION['DefaultDateFormat']);
        }
        echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">'
        . '<input type="hidden" name = "identifier" value ="' . $identifier . '">';
        echo '<div>';
        echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
        echo '<table class="selection" id="SignFrame">
			<tr><td valign="top">';
        echo '</select></td>
                        </tr>';

        echo '          <tr>
				<td bgcolor="#87CEFA">' . _('公司简称') . ':</td>
				<td>' . $_POST['coycode'] . '</td>
                                <input  type="hidden" name="coycode"  value="' . $_POST['coycode'] . '" />
			 
				<td bgcolor="#87CEFA">' . _('公司姓名') . ':</td>
				<td colspan="3"><input  type="text" name="coyname" required="required" autofocus="autofocus" value="' . $_POST['coyname'] . '" size="36" maxlength="140" /></td>
			</tr>
			<tr>
			
			<tr>
			<td bgcolor="#87CEFA">' . _('英文姓名') . ':</td>
				<td><input type="text" name="coyname_en" size="36" value="' . $_POST['coyname_en'] . '"  /></td>
			<td bgcolor="#87CEFA">' . _('法人') . ':</td>
				<td><input type="text" name="faren" size="36" value="' . $_POST['faren'] . '"  /></td>
				
				
			</tr>
			   <td bgcolor="#87CEFA">' . _('税号') . ':</td>
				<td><input  type="text" name="taxpayerid"  autofocus="autofocus" value="' . $_POST['taxpayerid'] . '" size="36" maxlength="40" /></td>
				<td bgcolor="#87CEFA">' . _('地址') . ':</td>
				<td colspan="3"><input type="text" name="address" size="36" maxlength="140" value="' . $_POST['address'] . '"  /></td>
			</tr>
				<td bgcolor="#87CEFA">' . _('开户银行') . ':</td>
				<td><input type="text" name="bank_name" size="36" maxlength="120" value="' . $_POST['bank_name'] . '"  /></td>
				<td bgcolor="#87CEFA">' . _('银行账号') . ':</td>
				<td><input type="text" name="bank_account" size="36" maxlength="60" value="' . $_POST['bank_account'] . '"  /></td>
			</tr>
			<tr>
			<td bgcolor="#87CEFA">' . _('联系电话') . ':</td>
				<td><input type="text" name="telephone" size="20" value="' . $_POST['telephone'] . '"  /></td>
				<td bgcolor="#87CEFA">' . _('Email') . ':</td>
				<td><input type="text" name="email" size="36"  maxlength="55" value="' . $_POST['email'] . '"  /></td>
			
			</tr>
			<tr> 
			<td bgcolor="#87CEFA">' . _('邮编') . ':</td>
				<td><input type="text" name="postcode" size="20" value="' . $_POST['postcode'] . '"  /></td>
				 
				</tr>
			 

				';

               
        echo '</table>';
        echo '<br />
			<div class="centre">
				<input type="submit" name="Addemployee" value="' . "更新公司" . '" />&nbsp;
				


</div>';
    }
    echo '</div>
          </form>';
} // end of main ifs
if (isset($_POST['return'])) {
    header('Location: SearchCompany.php');
}
include('includes/footer.inc');
?>
