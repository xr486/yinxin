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



$Title = _('员工维护');
/* webERP manual links before header.inc */
$ViewTopic = 'AccountsReceivable';
$BookMark = 'NewEmployee';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc'); 

echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/customer.png" title="' . _('员工') .
 '" alt="" />' . ' ' . _('员工维护') . '
	</p>';


if (isset($Errors)) {
    unset($Errors);
}
$Errors = array();


if (isset($_POST['AddEmployee'])) {

    //initialise no input errors assumed initially before we test
    $InputError = 0;
    $i = 1;
    $_POST['employee_num'] = mb_strtoupper($_POST['employee_num']);
   

    $sql = "SELECT COUNT(employee_num) FROM hr_employees WHERE employee_num='" . $_POST['employee_num'] . "'";
    $result = DB_query($sql, $db);
    $myrow = DB_fetch_row($result);
    if ($myrow[0] > 0 AND isset($_POST['employee_num']) and $InputError <> 1) {
        $InputError = 1;
        prnMsg(_('员工名称系统已存在'), 'error');
        $Errors[$i] = 'employee_name';
        $i++;
    } elseif (mb_strlen($_POST['employee_name']) == 0) {
        $InputError = 1;
        prnMsg(_('员工名称不能为空！'), 'error');
        $Errors[$i] = 'employee_name';
        $i++;
    } elseif ($_SESSION['employee_num'] == 0 AND mb_strlen($_POST['employee_num']) == 0) {
        $InputError = 1;
        prnMsg(_('员工代码不能为空！'), 'error');
        $Errors[$i] = 'employee_num';
        $i++;
    } elseif ($_SESSION['employee_num'] == 0 AND ( ContainsIllegalCharacters($_POST['employee_num']) OR mb_strpos($_POST['employee_num'], ' '))) {
        $InputError = 1;
        prnMsg(_('员工编号不能保护如下字符') . " . - ' &amp; + \" " . _('or a space'), 'error');
        $Errors[$i] = 'employee_num';
        $i++;
    } elseif (mb_strlen($_POST['telephone']) == 0) {
        $InputError = 1;
        prnMsg(_('员工电话不能为空'), 'error');
        $Errors[$i] = 'telephone';
        $i++;
    } elseif (mb_strlen($_POST['in_date']) == 0) {
        $InputError = 1;
        prnMsg(_('入职日期不能为空！'), 'error');
        $Errors[$i] = 'in_date';
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

        if (isset($_POST['AddEmployee'])) { //it is a new  Customer
			$v_date = strtotime(Date('Y-m-d H:i:s'));
            if ($_POST['lev_date'] == '') {
                //CreditLimit,  '" . $_POST['CreditLimit'] . "',
                 
                $sql = "INSERT INTO hr_employees (
							employee_num,
							employee_name,depart_code,shenfenzheng,email,
                            in_date,
                            creation_date,
                            created_by,huji,address,jinji_person,
                jinjin_person_telephone,home_address,
                            telephone
                            )
				VALUES ('" . $_POST['employee_num'] . "',
						'" . $_POST['employee_name'] . "','" . $_POST['depart_code'] . "','" . $_POST['shenfenzheng'] . "','" . $_POST['email'] . "',
						'" .strtotime( $_POST['in_date'] ). "',
						'" . $v_date . "', 
						'" . $_SESSION['UserID'] . "',
						'" . $_POST['huji'] . "',
						'" . $_POST['address'] . "',						
						'" . $_POST['jinji_person'] . "',
						'" . $_POST['jinjin_person_telephone'] . "',
						'" . $_POST['home_address'] . "',
						'" . $_POST['telephone'] . "'
						)";
            } else {
                 $sql = "INSERT INTO hr_employees (
							employee_num,
							employee_name,depart_code,shenfenzheng,email,
                            in_date,
							lev_date,
                            creation_date,
                            created_by,huji,address,jinji_person,
                jinjin_person_telephone,home_address,
                            telephone
                            )
				VALUES ('" . $_POST['employee_num'] . "',
						'" . $_POST['employee_name'] . "','" . $_POST['depart_code'] . "','" . $_POST['shenfenzheng'] . "','" . $_POST['email'] . "',
						'" .strtotime($_POST['in_date'] ). "',
						'" . strtotime($_POST['lev_date']). "',
						'" . $v_date . "', 
						'" . $_SESSION['UserID'] . "',
						'" . $_POST['huji'] . "',
						'" . $_POST['address'] . "',
						
						'" . $_POST['jinji_person'] . "',
						'" . $_POST['jinjin_person_telephone'] . "',
						'" . $_POST['home_address'] . "',
						'" . $_POST['telephone'] . "'
						)";
            }



            $ErrMsg = _('This customer could not be added because');
            $result = DB_query($sql, $db, $ErrMsg);
            prnMsg(_('员工新建成功'), 'success');
            unset($_POST['employee_num']);
            unset($_POST['employee_name']);
				unset($_POST['shenfenzheng']);
            unset($_POST['in_date']);
            unset($_POST['lev_date']);
			unset($_POST['telephone']);
			unset($_POST['email']);
            //unset($_POST['CreditLimit']);
            echo '<br />';
        }
    } else {
        prnMsg(_('新增员工失败！'), 'error');
    }
}


/* DebtorNo could be set from a post or a get when passed as a parameter to this page */

if (isset($_POST['employee_num'])) {
    $employee_num = $_POST['employee_num'];
} elseif (isset($_GET['employee_num'])) {
    $employee_num = $_GET['employee_num'];
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
				<td bgcolor="#87CEFA">' . _('员工工号') . ':</td>
				<td><input ' . (in_array('employee_num', $Errors) ? 'class="inputerror"' : '' ) . '  required="required" type="text" name="employee_num"  autofocus="autofocus" value="' . $_POST['employee_num'] . '" size="16" maxlength="40" /></td>
				<td bgcolor="#87CEFA">' . _('员工姓名') . ':</td>
				<td><input ' . (in_array('employee_name', $Errors) ? 'class="inputerror"' : '' ) . ' required="required" type="text" name="employee_name"  autofocus="autofocus" value="' . $_POST['employee_name'] . '" size="16" maxlength="40" /></td>
			</tr>
			<td bgcolor="#87CEFA">' . _('身份证号码') . ':</td>
				<td colspan="3"><input ' . (in_array('shenfenzheng', $Errors) ? 'class="inputerror"' : '' ) . '   type="text" name="shenfenzheng"  autofocus="autofocus" value="' . $_POST['shenfenzheng'] . '" size="36" maxlength="40" /></td>

		     <tr>
				<td bgcolor="#87CEFA">' . _('联系电话') . ':</td>
				<td><input  required="required" ' . (in_array('telephone', $Errors) ? 'class="inputerror"' : '' ) . ' type="text" name="telephone" size="16" maxlength="40" value="' . $_POST['telephone'] . '"  /></td>
			 
				<td bgcolor="#87CEFA">' . _('Email') . ':</td>
				<td><input ' . (in_array('email', $Errors) ? 'class="inputerror"' : '' ) . ' type="text" name="email" size="16" maxlength="40" value="' . $_POST['email'] . '"  /></td> 
			</tr>
			<tr>
			
			
			<td bgcolor="#87CEFA">' . _('户籍') . ':</td>
        <td><input ' . (in_array('huji', $Errors) ? 'class="inputerror"' : '' ) . ' type="text" name="huji" size="16" maxlength="40" value="' . $_POST['huji'] . '" /></td>	

			
			';
                     
	 echo ' <td bgcolor="#87CEFA">部门</td>';
    $sql = "SELECT depart_code,depart_name FROM hr_departs   ORDER by depart_code ";
    $result1 = DB_query($sql, $db);
    echo '<td><select name="depart_code">';
    while ($Salesmanrow = DB_fetch_array($result1)) {
        echo '<option value="' . $Salesmanrow['depart_code'] . '">' . $Salesmanrow['depart_name'] .
            '</option>';
    }
    echo '</select> <span style="color:red">*</span>  </td></tr>';
 	
 echo '<tr>
	 <td bgcolor="#87CEFA">' . _('地址') . ':</td>
        <td colspan="3"><input ' . (in_array('address', $Errors) ? 'class="inputerror"' : '' ) . ' type="text"    name="address"  size="65" maxlength="100" value="' . $_POST['address'] . '" /></td>
    </tr>
	<tr>
        <td bgcolor="#87CEFA">' . _('紧急联系人') . ':</td>
        <td><input ' . (in_array('jinji_person', $Errors) ? 'class="inputerror"' : '' ) . ' type="text" name="jinji_person" value="' . $_POST['jinji_person'] . '" size="16" maxlength="40" /></td>
    
        <td bgcolor="#87CEFA">' . _('联系人电话') . ':</td>
        <td><input ' . (in_array('jinjin_person_telephone', $Errors) ? 'class="inputerror"' : '' ) . ' type="text"    name="jinjin_person_telephone"  size="16" maxlength="100" value="' . $_POST['jinjin_person_telephone'] . '" /></td>

        
    </tr>
	<tr><td bgcolor="#87CEFA">' . _('家庭住址') . ':</td>
        <td colspan="3"><input ' . (in_array('home_address', $Errors) ? 'class="inputerror"' : '' ) . ' type="text"    name="home_address"  size="65" maxlength="100" value="' . $_POST['home_address'] . '" /></td></tr>
		<tr>
				<td bgcolor="#87CEFA">' . _('入职日期') . ':</td>
				<td><input type="text" onfocus="WdatePicker()"  alt="' . $_SESSION['DefaultDateFormat'] . '" required="required" name="in_date"   size="10" maxlength="10" title="' . _('in_date .') . '" value="' . $_POST['in_date'] . '" /></td>
			 
				<td bgcolor="#87CEFA">' . _('离职日期') . ':</td>
				<td><input type="text" onfocus="WdatePicker()"  alt="' . $_SESSION['DefaultDateFormat'] . '" name="lev_date"   size="10" maxlength="10" title="' . _('lev_date .') . '" value="' . $_POST['lev_date'] . '" /></td>
			</tr>';
 
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
