<?php

/* $Id: vendors.php 6338 2013-09-28 05:10:46Z daintree $ */
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
if (isset($_GET['UpdatevendorCode'])) {
    $UpdatevendorCode = $_GET['UpdatevendorCode'];
} else {
    $UpdatevendorCode = '';
}

$Title = _('人事资料修改');
$ViewTopic = '人事资料修改';
$BookMark = '人事资料修改';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/CountriesArray.php');


echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/customer.png" title="' . _('人事资料') .
 '" alt="" />' . ' ' . _('人事资料修改信息') . '
	</p>';


if (isset($_POST['Deletevendor'])) {
    $CancelDelete = 0;
	 $sql2 = "select * FROM  hr_employees WHERE employee_name='" . $_POST['employee_name'] . "'";
     $result2 = DB_query($sql2, $db);
	 $CancelDelete = DB_num_rows($result2);
   
    if ($CancelDelete == 0) { //ie not cancelled the delete as a result of above tests
        $sql = "DELETE FROM vendors WHERE employee_name='" . $_POST['employee_name'] . "'";
        $result = DB_query($sql, $db);
        prnMsg(_('员工') . ' ' . $_POST['employee_name'] . ' ' . _('资料被成功删除') . ' !', 'success');
        include('includes/footer.inc');
        unset($_SESSION['employee_name']);
        exit;
        echo '<br /><div class="centre"><a href="' . $RootPath . '/HrEmployeeModify.php">' . _('查询人事资料') . '</a></div>';
    } else {
	prnMsg(_('员工') . ' ' . $_POST['employee_name'] . ' ' . _('已建立员工资料,无法再删除,您可以失效') . ' !', 'error');
	}
}

if (isset($_POST['Addvendor'])) {
    isset($_SESSION['num' . $identifier]) or die("no session");
    if ($_SESSION['num' . $identifier] == 400) {
        $_SESSION['num' . $identifier] = 500;

        $InputError = 0;
        $i = 1;
        //$_POST['employee_num'] = mb_strtoupper($_POST['employee_num']);
        $sql2 = "SELECT COUNT(employee_num) FROM hr_employees WHERE employee_num='" . $_POST['employee_num'] . "'";
        $result2 = DB_query($sql2, $db);
        $myrow2 = DB_fetch_row($result2);
        $sql = "SELECT COUNT(employee_name) FROM hr_employees WHERE employee_name='" . $_POST['employee_name'] . "'";
        $result = DB_query($sql, $db);
        $myrow = DB_fetch_row($result);
        if (mb_strlen($_POST['employee_name']) > 10 OR mb_strlen($_POST['employee_name']) == 0) {
            $InputError = 1;
            prnMsg(_('姓名不超过10个字且不为空'), 'error');
            $Errors[$i] = 'employee_name';
            $i++;
        }  elseif ( ( ContainsIllegalCharacters($_POST['employee_name']) OR mb_strpos($_POST['employee_name'], ' '))or( ContainsIllegalCharacters($_POST['employee_num']) OR mb_strpos($_POST['employee_num'], ' '))) {
        $InputError = 1;
        prnMsg(_('工号和姓名不能包含特殊字符：\ ') . " . - ' &amp; + \" " . _('or a space'), 'error');
        $Errors[$i] = 'employee_num';
        $i++;
      }  /* elseif (mb_strlen($_POST['effective_date']) == 0) {
            $InputError = 1;
            prnMsg(_('生效日期不能为空'), 'error');
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

            if (isset($_POST['Addvendor'])) {
                // echo 'asd';
                // echo $_POST['txtName13'];
				//disable_date = '" . $_POST['disable_date'] . "',
                DB_Txn_Begin($db);
                // CreditLimit='" . $_POST['CreditLimit'] . "' ,
				// typeid='" . $_POST['typeid'] . "' ,
                 $v_date = strtotime(Date('Y-m-d H:i:s'));
                
                $sql = "UPDATE hr_employees 
                 SET employee_num='" . $_POST['employee_num'] . "',
			        employee_name='" . $_POST['employee_name'] . "',
			        shenfenzheng='" . $_POST['shenfenzheng'] . "',
			        huji='" . $_POST['huji'] . "',
			        telephone ='" .$_POST['telephone'] . "',
                    zhaopian ='" .$_POST['zhaopian'] . "',
			        address='" .$_POST['address'] . "',
                    jinji_person='" .$_POST['jinji_person'] . "',
                    jinjin_person_telephone='" .$_POST['jinjin_person_telephone'] . "',
                    home_address='" .$_POST['home_address'] . "',
					depart_code='" .$_POST['depart_code'] . "',
					email='" .$_POST['email'] . "',
				    xinzi='" . $_POST['xinzi'] . "',
                    nianjia='" . $_POST['nianjia'] . "',
                    yixiujia='" . $_POST['yixiujia'] . "',
                    last_updated_by='" .$_SESSION['UserID'] . "',
			        last_update_date='" . $v_date . "'
                WHERE employee_num = '" . $_POST['employee_num'] . "'"; 
                $ErrMsg = _('The employee could not be updated because');
                $result = DB_query($sql, $db, $ErrMsg);
//---------------------------
//---------------------------   
                DB_Txn_Commit($db);
                prnMsg(_('数据已更新！'), 'success');
                unset($_POST['employee_num']);
                unset($_POST['employee_name']);
                unset($_POST['shenfenzheng']);
                unset($_POST['huji']);
				unset($_POST['telephone']);
                unset($_POST['zhaopian']);
                unset($_POST['address']);
                unset($_POST['jinji_person']);
                unset($_POST['jinjin_person_telephone']);
                unset($_POST['depart_code']);
                unset($_POST['email']);
				unset($_POST['xinzi']);
                unset($_POST['nianjia']);
				unset($_POST['yixiujia']);

                echo '<br />';
                echo '<br /><div class="centre"><a href="' . $RootPath . '/HrEmployeeModify.php">' . _('查询人事资料') . '</a></div>';
            }
        } else {
            prnMsg(_('Validation failed') . '. ' . _('No updates or deletes took place'), 'error');
        }
    } else {
        header('Location: HrEmployeeModify.php');
    }
} else {
    session_start() or die("session is not started");
    $_SESSION['num' . $identifier] = 400;
}
if (isset($UpdatevendorCode) and $UpdatevendorCode != '') {
    //CreditLimit,
    $sql = "SELECT employee_num,
                    employee_name,
                    shenfenzheng,
                    huji,
                    telephone,
                    zhaopian,
                    created_by,
                    creation_date,
                    last_updated_by,
                    last_update_date,
                    address,
                    jinji_person,
                    jinjin_person_telephone,
                    home_address,
                    depart_code,
                    email,
                    xinzi,
                    nianjia,
                    yixiujia
				FROM hr_employees
				WHERE employee_num= " . "'$UpdatevendorCode'";

    $ErrMsg = _('The employee details could not be retrieved because');
    $result = DB_query($sql, $db, $ErrMsg);
    $myrow = DB_fetch_array($result);
    $_POST['employee_num'] = $myrow['employee_num'];
    $_POST['employee_name'] = $myrow['employee_name'];
    $_POST['shenfenzheng'] = $myrow['shenfenzheng'];
    $_POST['huji'] = $myrow['huji'];
    $_POST['telephone'] = $myrow['telephone'];
    $_POST['zhaopian'] = $myrow['zhaopian'];
    $_POST['address'] = $myrow['address'];
    $_POST['jinji_person'] = $myrow['jinji_person'];
    $_POST['jinjin_person_telephone'] = $myrow['jinjin_person_telephone'];
    $_POST['home_address'] = $myrow['home_address'];
    $_POST['depart_code'] = $myrow['depart_code'];
    $_POST['email'] = $myrow['email'];
    $_POST['xinzi'] = $myrow['xinzi'];
    $_POST['nianjia'] = $myrow['nianjia'];
    $_POST['yixiujia'] = $myrow['yixiujia'];
    if (!isset($_GET['delete'])) {
       /*  if (!isset($_POST['effective_date'])) {
            $_POST['effective_date'] = Date($_SESSION['DefaultDateFormat']);
        } */
        echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">'
        . '<input type="hidden" name = "identifier" value ="' . $identifier . '">';
        echo '<div>';
        echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
        echo '<table class="selection" id="SignFrame">
			<tr><td valign="top">';
        echo '</select></td></tr>';

        echo '<tr>
            <td>' . _('工号') . ':</td>
            <td><input  type="text"  required="required" name="employee_num" size="25" maxlength="40" value="' . $_POST['employee_num'] . '" /></td>
            
            <td>' . _('姓名') . ':</td>
            <td><input  type="text" name="employee_name" required="required" autofocus="autofocus" value="' . $_POST['employee_name'] . '" size="25" maxlength="40" /></td>
       
            <td>' . _('身份证号') . ':</td>
            <td colspan="3"><input  type="text" name="shenfenzheng" size="25" maxlength="40" value="' . $_POST['shenfenzheng'] . '" /></td>
        </tr>
        <tr>
            <td>' . _('户籍') . ':</td>
            <td><input type="text" name="huji" size="25" maxlength="40" value="' . $_POST['huji'] . '" /></td>
        
            <td>' . _('电话') . ':</td>
            <td><input  type="text" name="telephone" size="25" maxlength="40" value="' . $_POST['telephone'] . '" /></td>

            <td>' . _('住址') . ':</td>
            <td colspan="3"><input  type="text" name="address" size="35" maxlength="40" value="' . $_POST['address'] . '" /></td>
        </tr>
		<tr>
            <td>' . _('紧急联系人') . ':</td>
            <td><input type="text" name="jinji_person" size="25" maxlength="40" value="' . $_POST['jinji_person'] . '"  /></td>  
                    
            <td>' . _('紧急联系人电话') . ':</td>
            <td><input  type="text" name="jinjin_person_telephone" size="25" maxlength="40" value="' . $_POST['jinjin_person_telephone'] . '" /></td>

            <td>' . _('家庭住址') . ':</td>
            <td><input  type="text" name="home_address" size="35" maxlength="40" value="' . $_POST['home_address'] . '" /></td>
        </tr>';
                
         
    echo '<tr><td>部门:</td>';
    echo '<td><select name="depart_code" required="required">';
        $sql ="select depart_num,depart_name from  hr_departs order by depart_num";
                    $result = DB_query($sql, $db);
   while ($v = DB_fetch_array($result)) {
                        if ($v['depart_num'] == $_POST['depart_code']) {
                            ?>
                                <option value="<?= $v['depart_num'] ?>" selected="selected"><?= $v['depart_num'] ?></option>
                            <?php } else { ?>
                                <option value="<?= $v['depart_num'] ?>"><?= $v['depart_num'] ?></option>
                            <?php
                            }
                        }
    echo '</select> <span style="color:red">*</span>  </td> 
    <td>' . _('薪资 ') . ':</td>
    <td colspan="3"><input type="text" name="xinzi"  size="25" maxlength="40" value="' . $_POST['xinzi'] . '"  /></td>';

	echo '	</tr>';
    echo '  <tr>
        <td>' . _('邮箱') . ':</td>
        <td><input  placeholder="' . _('e.g. user@domain.com') . '" type="text" name="email" size="25" maxlength="40"  value="' . $_POST['email'] . '" /></td>

        <td>' . _('年假天数') . ':</td>
        <td><input  type="text" name="nianjia" size="25" maxlength="40" value="' . $_POST['nianjia'] . '" /></td>

        <td>' . _('已休假天数') . ':</td>
        <td><input  type="text" name="yixiujia" size="25" maxlength="40" value="' . $_POST['yixiujia'] . '" /></td>';

    echo'</tr>';

    echo '</table>';
    echo '<br />
        <div class="centre">
            <input type="submit" name="Addvendor" value="' . "更新员工" . '" />&nbsp;
            <input type="submit" name="Deletevendor" value="' . _('删除员工') . '" onclick="return confirm(\'' . _('Are You Sure?') . '\');" />
            <input type="submit" name="return" value="' . "返回上一层" . '" />&nbsp;                     
        </div>';
    }
    echo '</div>
          </form>';
} // end of main ifs
if (isset($_POST['return'])) {
    header('Location: HrEmployeeModify.php');
}
include('includes/footer.inc');
?>
