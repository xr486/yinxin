<?php

ob_start();
/* $Id: customers.php 6338 2013-09-28 05:10:46Z daintree $ */

include('includes/session.inc');

if (isset($_POST['Edit']) or isset($_GET['Edit']) or isset($_GET['DebtorNo'])) {
    $ViewTopic = 'AddEmployee';
    $BookMark = 'AddEmployee';
} else {
    $ViewTopic = 'NewEmployee';
    $BookMark = 'NewEmployee';
}



$Title = _('公司维护');
/* webERP manual links before header.inc */
$ViewTopic = 'AccountsReceivable';
$BookMark = 'NewEmployee';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc'); 

echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/customer.png" title="' . _('员工') .
 '" alt="" />' . ' ' . _('公司维护') . '
	</p>';


if (isset($Errors)) {
    unset($Errors);
}
$Errors = array();


if (isset($_POST['AddEmployee'])) {

    //initialise no input errors assumed initially before we test
    $InputError = 0;
    $i = 1;
    $_POST['coyname_code'] = mb_strtoupper($_POST['coyname_code']);
    $sql2 = "SELECT COUNT(coyname_code) FROM companies2 WHERE coyname_code='" . $_POST['coyname_code'] . "'";
    $result2 = DB_query($sql2, $db);
    $myrow2 = DB_fetch_row($result2);
    //echo 'AAA';
    if ($myrow2[0] > 0 AND isset($_POST['coyname_code']) and $InputError <> 1) {
       // echo 'BBB';
        $InputError = 1;
        prnMsg(_('员工工号系统已存在'), 'error');
        $Errors[$i] = 'coyname_code';
        $i++;
    }

    $sql = "SELECT COUNT(coyname) FROM companies2 WHERE coyname='" . $_POST['coyname'] . "'";
    $result = DB_query($sql, $db);
    $myrow = DB_fetch_row($result);
    if ($myrow[0] > 0 AND isset($_POST['coyname']) and $InputError <> 1) {
        $InputError = 1;
        prnMsg(_('员工名称系统已存在'), 'error');
        $Errors[$i] = 'coyname';
        $i++;
    } elseif (mb_strlen($_POST['coyname']) == 0) {
        $InputError = 1;
        prnMsg(_('员工名称不能为空！'), 'error');
        $Errors[$i] = 'coyname';
        $i++;
    } elseif ($_SESSION['coyname_code'] == 0 AND mb_strlen($_POST['coyname_code']) == 0) {
        $InputError = 1;
        prnMsg(_('员工代码不能为空！'), 'error');
        $Errors[$i] = 'coyname_code';
        $i++;
    } elseif ($_SESSION['coyname_code'] == 0 AND ( ContainsIllegalCharacters($_POST['coyname_code']) OR mb_strpos($_POST['coyname_code'], ' '))) {
        $InputError = 1;
        prnMsg(_('员工编号不能保护如下字符') . " . - ' &amp; + \" " . _('or a space'), 'error');
        $Errors[$i] = 'coyname_code';
        $i++;
    } elseif (mb_strlen($_POST['telephone']) == 0) {
        $InputError = 1;
        prnMsg(_('员工电话不能为空'), 'error');
        $Errors[$i] = 'telephone';
        $i++;
    }  

    //没有错误，则执行如下
    //当是 update 则执行update 若是add 的时候，执行insert
    if ($InputError != 1) {

        $SQL_ClientSince = FormatDateForSQL($_POST['ClientSince']);

        if (isset($_POST['AddEmployee'])) { //it is a new  Customer
			$v_date = strtotime(Date('Y-m-d H:i:s'));
         
                 $sql = "INSERT INTO companies2 (
							coyname_code,coyname,coyname_en,postcode,taxpayerid,bank_account,bank_name,faren,address,telephone,email,creation_date,created_by
                            )
				VALUES ('" . $_POST['coyname_code'] . "',
						'" . $_POST['coyname'] . "','" . $_POST['coyname_en'] . "',
						 '" . $_POST['postcode'] . "','" . $_POST['taxpayerid'] . "','" . $_POST['bank_account'] . "',
						 '" . $_POST['bank_name'] . "','" . $_POST['faren'] . "','" . $_POST['address'] . "',
						 '" . $_POST['telephone'] . "','" . $_POST['email'] . "', 
						'" . $v_date . "', 
						'" . $_SESSION['UserID'] . "' 
						)";
            

            $ErrMsg = _('This customer could not be added because');
            $result = DB_query($sql, $db, $ErrMsg);
            prnMsg(_('新公司建立成功'), 'success');
            unset($_POST['coyname_code']);
            unset($_POST['coyname']);
			unset($_POST['coyname_en']);
            unset($_POST['postcode']);
            unset($_POST['taxpayerid']);
			unset($_POST['bank_account']);
            unset($_POST['address']);
            unset($_POST['bank_name']);
            unset($_POST['faren']);
            echo '<br />';
        }
    } else {
        prnMsg(_('新增公司失败！'), 'error');
    }
}


/* DebtorNo could be set from a post or a get when passed as a parameter to this page */

if (isset($_POST['coyname_code'])) {
    $coyname_code = $_POST['coyname_code'];
} elseif (isset($_GET['coyname_code'])) {
    $coyname_code = $_GET['coyname_code'];
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
    echo '<table class="selection">
			<tr><td valign="top">';

    echo '</select></td>
			</tr>';

    echo '<tr>
				<td>' . _('公司简称') . ':</td>
				<td><input ' . (in_array('coyname_code', $Errors) ? 'class="inputerror"' : '' ) . '  required="required" type="text" name="coyname_code"  autofocus="autofocus" value="' . $_POST['coyname_code'] . '" size="16" maxlength="40" /></td>
			 
			<td>' . _('公司姓名') . ':</td>
				<td colspan="3"><input  type="text" name="coyname" required="required" autofocus="autofocus" value="' . $_POST['coyname'] . '" size="36" maxlength="140" /></td>
			</tr>
			<tr>
			
			<tr>
			<td>' . _('英文姓名') . ':</td>
				<td><input type="text" name="coyname_en" size="36" value="' . $_POST['coyname_en'] . '"  /></td>
			<td>' . _('法人') . ':</td>
				<td><input type="text" name="faren" size="36" value="' . $_POST['faren'] . '"  /></td>
				
				
			</tr>
			   <td>' . _('税号') . ':</td>
				<td><input  type="text" name="taxpayerid"  autofocus="autofocus" value="' . $_POST['taxpayerid'] . '" size="36" maxlength="40" /></td>
				<td>' . _('地址') . ':</td>
				<td colspan="3"><input type="text" name="address" size="36" maxlength="140" value="' . $_POST['address'] . '"  /></td>
			</tr>
				<td>' . _('开户银行') . ':</td>
				<td><input type="text" name="bank_name" size="36" maxlength="120" value="' . $_POST['bank_name'] . '"  /></td>
				<td>' . _('银行账号') . ':</td>
				<td><input type="text" name="bank_account" size="36" maxlength="60" value="' . $_POST['bank_account'] . '"  /></td>
			</tr>
			<tr>
			<td>' . _('联系电话') . ':</td>
				<td><input type="text" name="telephone" size="20" value="' . $_POST['telephone'] . '"  /></td>
				<td>' . _('Email') . ':</td>
				<td><input type="text" name="email" size="36"  maxlength="55" value="' . $_POST['email'] . '"  /></td>
			
			</tr>
			<tr> 
			<td>' . _('邮编') . ':</td>
				<td><input type="text" name="postcode" size="20" value="' . $_POST['postcode'] . '"  /></td>

			</tr>
			';
                       
	 
 
    echo '</table>';

    echo'</td></tr></table>';







    echo '<div class="centre">
				<input type="submit" name="AddEmployee" value="' . _('新增') . '" />&nbsp;
				<input type="Reset" name="Reset" value="' . _('Reset') . '" />&nbsp;
             
			</div>';


    echo '</div>
          </form>';
} // end of main ifs


if (isset($_POST['return'])) {
    header('Location: SearchEmployee.php');
}

include('includes/footer.inc');
?>
