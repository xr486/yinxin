<?php

ob_start();
/* $Id: Vendors.php 6338 2013-09-28 05:10:46Z daintree $ */

include('includes/session.inc');

if (isset($_POST['Edit']) or isset($_GET['Edit']) or isset($_GET['DebtorNo'])) {
    $ViewTopic = 'AccountsReceivable';
    $BookMark = 'AmendVendor';
} else {
    $ViewTopic = 'AccountsReceivable';
    $BookMark = 'NewVendor';
}

$Title = _('人事资料建立');
/* webERP manual links before header.inc */
$ViewTopic = '人事资料建立';
$BookMark = '人事资料建立';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/CountriesArray.php');
include('includes/CurrenciesArray.php');
echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/customer.png" title="' . _('Customer') .
 '" alt="" />' . ' ' . _('人事资料建立') . '
	</p>';


if (isset($Errors)) {
    unset($Errors);
}
$Errors = array();


if (isset($_POST['AddVendor'])) {

    //initialise no input errors assumed initially before we test
    $InputError = 0;
    $i = 1;
    $_POST['employee_num'] = mb_strtoupper($_POST['employee_num']);
    $sql2 = "SELECT COUNT(employee_num) FROM hr_employees WHERE employee_num='" . $_POST['employee_num'] . "'";
    $result2 = DB_query($sql2, $db);
    $myrow2 = DB_fetch_row($result2);
    //echo 'AAA';
    if ($myrow2[0] > 0 AND isset($_POST['AddVendor']) and $InputError <> 1) {
       // echo 'BBB';
        $InputError = 1;
        prnMsg(_('人事资料系统已存在'), 'error');
        $Errors[$i] = 'employee_num';
        $i++;
    }

    $sql = "SELECT COUNT(employee_name) FROM hr_employees WHERE employee_name='" . $_POST['employee_name'] . "'";
    $result = DB_query($sql, $db);
    $myrow = DB_fetch_row($result);
    if ($myrow[0] > 0 AND isset($_POST['AddVendor']) and $InputError <> 1) {
        $InputError = 1;
        prnMsg(_('员工名称系统已存在'), 'error');
        $Errors[$i] = 'employee_name';
        $i++;
    } elseif (mb_strlen($_POST['employee_name']) > 10 OR mb_strlen($_POST['employee_name']) == 0) {
        $InputError = 1;
        prnMsg(_('供应商名称不超过10个字且不能为空！'), 'error');
        $Errors[$i] = 'employee_name';
        $i++;
    } elseif ($_SESSION['employee_num'] == 0 AND mb_strlen($_POST['employee_num']) == 0) {
        $InputError = 1;
        prnMsg(_('员工工号不能为空！'), 'error');
        $Errors[$i] = 'employee_num';
        $i++;
    } elseif ($_SESSION['AutoDebtorNo'] == 0 AND ( ContainsIllegalCharacters($_POST['employee_num']) OR mb_strpos($_POST['employee_num'], ' '))) {
        $InputError = 1;
        prnMsg(_('The employee code cannot contain any of the following characters') . " . - ' &amp; + \" " . _('or a space'), 'error');
        $Errors[$i] = 'employee_num';
        $i++;
    }    /* elseif (mb_strlen($_POST['effective_date']) == 0) {
        $InputError = 1;
        prnMsg(_('生效日期不能为空！'), 'error');
        $Errors[$i] = 'effective_date';
        $i++;
    } *//* elseif (!is_numeric(filter_number_format($_POST['CreditLimit']))) {
        $InputError = 1;
        prnMsg(_('The credit limit must be numeric'), 'error');
        $Errors[$i] = 'CreditLimit';
        $i++;
    }*/

    //没有错误，则执行如下
    //当是 update 则执行update 若是add 的时候，执行insert
    if ($InputError != 1) {

        $SQL_ClientSince = FormatDateForSQL($_POST['ClientSince']);
        if ($_POST['xinzi']=='') {
			$_POST['xinzi']=0;
		}
		if ($_POST['nianjia']=='') {
			$_POST['nianjia']=0;
		}
		if ($_POST['yixiujia']=='') {
			$_POST['yixiujia']=0;
		}
        if (isset($_POST['AddVendor'])) { //it is a new vendor
            $v_date = strtotime(Date('Y-m-d H:i:s'));
            $sql = "INSERT INTO hr_employees(
                employee_num,
                employee_name,
                shenfenzheng,
                huji,
                telephone,
                zhaopian,
                address,
                jinji_person,
                jinjin_person_telephone,
                created_by,
                creation_date,
                last_updated_by,
                last_update_date,
                home_address,
                depart_code,
                email,
                xinzi,
                nianjia,
                yixiujia)
            
    VALUES ('" . $_POST['employee_num'] . "',
            '" . $_POST['employee_name'] . "',
            '" . $_POST['shenfenzheng'] . "',
            '" . $_POST['huji'] . "',
            '" . $_POST['telephone'] . "',
            '" . $_POST['zhaopian'] . "',
            '" . $_POST['address'] . "',
            '" . $_POST['jinji_person'] . "',
            '" . $_POST['jinjin_person_telephone'] . "',
            '" . $_SESSION['UserID'] . "',
            '" . $v_date . "',
            '" . $_SESSION['UserID'] . "',
            '" . $v_date . "',
            '" . $_POST['home_address'] . "',
            '" . $_POST['depart_code'] . "',
            '" . $_POST['email'] . "',
            '" . $_POST['xinzi'] . "',
            '" . $_POST['nianjia'] . "',
            '" . $_POST['yixiujia'] . "'
            )";

            $ErrMsg = _('This employee could not be added because');
            $result = DB_query($sql,$db, $ErrMsg);
            prnMsg(_('人事资料新建成功'), 'success');
            unset($_POST['employee_num']);
            unset($_POST['employee_name']);
            unset($_POST['shenfenzheng']);
            unset($_POST['huji']);
            unset($_POST['telephone']);
            unset($_POST['zhaopian']);
			unset($_POST['address']);
            unset($_POST['jinji_person']);
            unset($_POST['jinjin_person_telephone']);
            unset($_POST['home_address']);
            unset($_POST['depart_code']);
            unset($_POST['email']);
            unset($_POST['$xinzi']);
            unset($_POST['$nianjia']);
            unset($_POST['$yixiujia']);
            
            //unset($_POST['CreditLimit']);
            echo '<br />';
        }
    } else {
        prnMsg(_('新增人事资料失败！'), 'error');
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

    /* if (!isset($_POST['effective_date'])) {
        $_POST['effective_date'] = Date("Y-m-d");
    } */
   

    echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
    echo '<div>';
    echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
    echo '<table class="selection">
			<tr><td valign="top">';

    echo '</select></td>
			</tr>';

    echo '<tr>
        <td>' . _('工号') . ':</td>
        <td><input ' . (in_array('employee_num', $Errors) ? 'class="inputerror"' : '' ) . ' type="text"  name="employee_num"  autofocus="autofocus" value="' . $_POST['employee_num'] . '" size="25" maxlength="40" /></td>

        <td>' . _('姓名') . ':</td>
        <td><input ' . (in_array('employee_name', $Errors) ? 'class="inputerror"' : '' ) . ' type="text"  name="employee_name"  autofocus="autofocus" value="' . $_POST['employee_name'] . '" size="25" maxlength="40" /></td>

        <td>' . _('身份证号') . ':</td>
        <td colspan="3"><input ' . (in_array('shenfenzheng', $Errors) ? 'class="inputerror"' : '' ) . ' type="text"    name="shenfenzheng" size="25" maxlength="40" value="' . $_POST['shenfenzheng'] . '" /></td>
	<tr>		 
		<td>' . _('户籍') . ':</td>
        <td><input ' . (in_array('huji', $Errors) ? 'class="inputerror"' : '' ) . ' type="text" name="huji" size="25" maxlength="40" value="' . $_POST['huji'] . '" /></td>	
        
        <td>' . _('电话') . ':</td>
        <td><input ' . (in_array('telephone', $Errors) ? 'class="inputerror"' : '' ) . ' type="text" name="telephone" size="25" maxlength="40" value="' . $_POST['telephone'] . '" /></td>	
        
        <td>' . _('地址') . ':</td>
        <td colspan="3"><input ' . (in_array('address', $Errors) ? 'class="inputerror"' : '' ) . ' type="text"    name="address"  size="55" maxlength="40" value="' . $_POST['address'] . '" /></td>
	</tr>
    <tr>
        <td>' . _('紧急联系人') . ':</td>
        <td><input ' . (in_array('jinji_person', $Errors) ? 'class="inputerror"' : '' ) . ' type="text" name="jinji_person" value="' . $_POST['jinji_person'] . '" size="25" maxlength="40" /></td>
    
        <td>' . _('紧急联系人电话') . ':</td>
        <td><input ' . (in_array('jinjin_person_telephone', $Errors) ? 'class="inputerror"' : '' ) . ' type="text"    name="jinjin_person_telephone"  size="25" maxlength="40" value="' . $_POST['jinjin_person_telephone'] . '" /></td>

        <td>' . _('家庭住址') . ':</td>
        <td colspan="3"><input ' . (in_array('home_address', $Errors) ? 'class="inputerror"' : '' ) . ' type="text"    name="home_address"  size="55" maxlength="40" value="' . $_POST['home_address'] . '" /></td>
    </tr> ';
   
    echo '<tr><td>部门</td> ';
     $sql = "SELECT depart_name FROM hr_departs  ORDER by depart_name ";  
    $result1 = DB_query($sql, $db);
    echo '<td><select name="depart_code">';
    while ($Salesmanrow = DB_fetch_array($result1)) {
        echo '<option value="' . $Salesmanrow['depart_name'] . '">' . $Salesmanrow['depart_name'] .
            '</option>';
    }
    echo '</select> <span style="color:red">*</span>  </td>

    <td>' . _('薪资 ') . ':</td>
	<td colspan="3"><input ' . (in_array('xinzi', $Errors) ? 'class="inputerror"' : '' ) . ' type="text" name="xinzi"  size="25" maxlength="40" value="' . $_POST['xinzi'] . '" /></td>
    </tr>';     
                
    echo'<tr>
        <td>' . _('邮箱') . ':</td>
        <td><input ' . (in_array('email', $Errors) ? 'class="inputerror"' : '' ) . ' type="text" name="email" size="25" maxlength="40" value="' . $_POST['email'] . '" placeholder="' . _('e.g. user@domain.com') . '" /></td>
        
        <td>' . _('年假天数 ') . ':</td>
        <td><input ' . (in_array('nianjia', $Errors) ? 'class="inputerror"' : '' ) . ' type="text" name="nianjia"  size="25" maxlength="40" value="' . $_POST['nianjia'] . '" /></td>		  

        <td>' . _('已休假天数') . ':</td>
        <td colspan="3"><input ' . (in_array('yixiujia', $Errors) ? 'class="inputerror"' : '' ) . ' type="text" name="yixiujia" value="' . $_POST['yixiujia'] . '" size="25" maxlength="40" /></td>			
	</tr>';
    
    /* echo '</td>';
			 echo '
                  <tr> 		
				<td>' . _('effective_date') . ':</td>
				<td colspan="3"><input type="text" onfocus="WdatePicker()"  alt="' . $_SESSION['DefaultDateFormat'] . '" name="effective_date"    required="required" size="16" maxlength="10" title="' . _('effective_date .') . '" value="' . $_POST['effective_date'] . '" /></td>
			
				

			</tr>'; */
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
    header('Location: HrEmployeeReport.php');
}

include('includes/footer.inc');
?>
