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

$Title = _('员工维护');
$ViewTopic = '员工维护';
$BookMark = '员工维护';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/CountriesArray.php');

echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/customer.png" title="' . _('员工') .
 '" alt="" />' . ' ' . _('员工资料修改') . '
	</p>';


if (isset($_POST['Deleteemployee'])) {
    $CancelDelete = 0;

    if ($CancelDelete == 0) { //ie not cancelled the delete as a result of above tests
        $sql = "DELETE FROM hr_employees WHERE employee_num='" . $_POST['employee_num'] . "'";
        $result = DB_query($sql, $db);
        prnMsg(_('员工') . ' ' . $_POST['employee_num'] . ' ' . _('资料被成功删除') . ' !', 'success');
        include('includes/footer.inc');
        unset($_SESSION['employee_num']);
        exit;
        echo '<br /><div class="centre"><a href="' . $RootPath . '/SearchEmployee.php">' . _('查询员工') . '</a></div>';
    }
}

if (isset($_POST['Addemployee'])) {
    isset($_SESSION['num' . $identifier]) or die("no session");
    if ($_SESSION['num' . $identifier] == 400) {
        $_SESSION['num' . $identifier] = 500;

        $InputError = 0;
        $i = 1;
        $_POST['employee_num'] = mb_strtoupper($_POST['employee_num']);
        $sql2 = "SELECT COUNT(employee_num) FROM hr_employees WHERE employee_num='" . $_POST['employee_num'] . "'";
        $result2 = DB_query($sql2, $db);
        $myrow2 = DB_fetch_row($result2);
        $sql = "SELECT COUNT(employee_name) FROM hr_employees WHERE employee_name='" . $_POST['employee_name'] . "'";
        $result = DB_query($sql, $db);
        $myrow = DB_fetch_row($result);
        if ( mb_strlen($_POST['employee_name']) == 0) {
            $InputError = 1;
            prnMsg(_('员工姓名不能为空'), 'error');
            $Errors[$i] = 'employee_name';
            $i++;
        }  elseif ( ( ContainsIllegalCharacters($_POST['employee_name']) OR mb_strpos($_POST['employee_name'], ' '))or( ContainsIllegalCharacters($_POST['employee_num']) OR mb_strpos($_POST['employee_num'], ' '))) {
        $InputError = 1;
        prnMsg(_('员工工号和姓名不能包含特殊字符：\ ') . " . - ' &amp; + \" " . _('or a space'), 'error');
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

            if (isset($_POST['Addemployee'])) {
                // echo 'asd';
                // echo $_POST['txtName13'];
				//disable_date = '" . $_POST['disable_date'] . "',
                DB_Txn_Begin($db);
                // CreditLimit='" . $_POST['CreditLimit'] . "' ,
				// typeid='" . $_POST['typeid'] . "' ,
                 $v_date = strtotime(Date('Y-m-d H:i:s'));
                if($_POST['lev_date'] == ''){ 
                $sql = "UPDATE hr_employees
                          SET employee_name='" . $_POST['employee_name'] . "',
			      in_date='" .strtotime( $_POST['in_date']) . "',
                  shenfenzheng='" . $_POST['shenfenzheng'] . "',email='" . $_POST['email'] . "', 
				  depart_code='" . $_POST['depart_code'] . "',
				  huji='" . $_POST['huji'] . "',
				  address='" . $_POST['address'] . "',
				  jinji_person='" . $_POST['jinji_person'] . "',
				  jinjin_person_telephone='" . $_POST['jinjin_person_telephone'] . "',
				  home_address='" . $_POST['home_address'] . "', 
			      telephone='" . $_POST['telephone'] . "',
			      last_updated_by='" . $_SESSION['UserID'] . "',
			      last_update_date='" . $v_date . "'
                WHERE employee_num = '" . $_POST['employee_num'] . "'";}

                else{ $sql =  "UPDATE hr_employees
                          SET employee_name='" . $_POST['employee_name'] . "',
			      in_date='" .strtotime( $_POST['in_date'] ). "',
                  shenfenzheng='" . $_POST['shenfenzheng'] . "',
				  email='" . $_POST['email'] . "', 
				  depart_code='" . $_POST['depart_code'] . "',
				   huji='" . $_POST['huji'] . "',
				  address='" . $_POST['address'] . "',
				  jinji_person='" . $_POST['jinji_person'] . "',
				  jinjin_person_telephone='" . $_POST['jinjin_person_telephone'] . "',
				  home_address='" . $_POST['home_address'] . "', 
			      telephone='" . $_POST['telephone'] . "',
				  lev_date = '" . strtotime($_POST['lev_date']) . "',
			      last_updated_by='" . $_SESSION['UserID'] . "',
			      last_update_date='" . $v_date . "'
                WHERE employee_num = '" . $_POST['employee_num'] . "'";}
                $ErrMsg = _('The customer could not be updated because');
                $result = DB_query($sql, $db, $ErrMsg);
//---------------------------
//---------------------------
                DB_Txn_Commit($db);
                prnMsg(_('数据已更新！'), 'success');
                unset($_POST['employee_num']);
                unset($_POST['employee_name']);
                unset($_POST['in_date']);
                unset($_POST['lev_date']);


                echo '<br />';
                echo '<br /><div class="centre"><a href="' . $RootPath . '/SearchEmployee.php">' . _('查询员工') . '</a></div>';
            }
        } else {
            prnMsg(_('Validation failed') . '. ' . _('No updates or deletes took place'), 'error');
        }
    } else {
        header('Location: SearchEmployee.php');
    }
} else {
    session_start() or die("session is not started");
    $_SESSION['num' . $identifier] = 400;
}
if (isset($UpdateEmployee_Num) and $UpdateEmployee_Num != '') {
    //CreditLimit,
    $sql = "SELECT
employee_num,
in_date,
lev_date,email,
created_by,
telephone,
employee_name,shenfenzheng,depart_code,(select depart_name from hr_departs h where h.depart_code= b.depart_code) depart_name,huji,address,jinji_person,
                jinjin_person_telephone,home_address
FROM hr_employees b
WHERE employee_num= " . "'$UpdateEmployee_Num'";

    $ErrMsg = _('The customer details could not be retrieved because');
    $result = DB_query($sql, $db, $ErrMsg);
    $myrow = DB_fetch_array($result);
    $_POST['employee_num'] = $myrow['employee_num'];
    $_POST['in_date'] = $myrow['in_date'];
	$_POST['lev_date'] = $myrow['lev_date'];
	$_POST['employee_name'] = $myrow['employee_name'];
	$_POST['telephone'] = $myrow['telephone'];
	$_POST['shenfenzheng'] = $myrow['shenfenzheng'];
	$_POST['depart_code'] = $myrow['depart_code'];
    $_POST['email'] = $myrow['email'];
	$_POST['depart_name'] = $myrow['depart_name'];
    $_POST['home_address'] = $myrow['home_address'];
    $_POST['huji'] = $myrow['huji'];
    $_POST['address'] = $myrow['address'];
    $_POST['address'] = $myrow['address'];
    $_POST['jinji_person'] = $myrow['jinji_person'];
    $_POST['jinjin_person_telephone'] = $myrow['jinjin_person_telephone'];
 

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
				<td bgcolor="#87CEFA">' . _('员工工号') . ':</td>
				<td  >' . $_POST['employee_num'] . '</td>
                                <input  type="hidden" name="employee_num"  value="' . $_POST['employee_num'] . '" />
		
				<td bgcolor="#87CEFA">' . _('员工姓名') . ':</td>
				<td><input  type="text" name="employee_name" required="required" autofocus="autofocus" value="' . $_POST['employee_name'] . '" size="16" maxlength="40" /></td>
			</tr>
			<td bgcolor="#87CEFA">' . _('身份证号码') . ':</td>
				<td colspan="3"><input type="text" name="shenfenzheng"  autofocus="autofocus" value="' . $_POST['shenfenzheng'] . '" size="36" maxlength="40" /></td>

		     <tr>
				<td bgcolor="#87CEFA">' . _('联系电话') . ':</td>
				<td><input  required="required"  type="text" name="telephone" size="16" maxlength="40" value="' . $_POST['telephone'] . '"  /></td>
			 
				<td bgcolor="#87CEFA">' . _('Email') . ':</td>
				<td><input  type="text" name="email" size="16" maxlength="40" value="' . $_POST['email'] . '"  /></td> 
			</tr>
			<tr>
			
			
			<td bgcolor="#87CEFA">' . _('户籍') . ':</td>
        <td><input  type="text" name="huji" size="16" maxlength="40" value="' . $_POST['huji'] . '" /></td>	

			
			';
                     
	 echo ' <td bgcolor="#87CEFA">部门</td>';
    $sql = "SELECT depart_code,depart_name FROM hr_departs   ORDER by depart_code ";
    $result1 = DB_query($sql, $db);
    echo '<td><select name="depart_code">';
    while ($Salesmanrow = DB_fetch_array($result1)) {
	 if ($Salesmanrow['depart_code']==$_POST['depart_code']) {

        echo '<option value="' . $Salesmanrow['depart_code'] . '" selected="selected">' . $Salesmanrow['depart_name'] .
            '</option>';
	 }
	 else {
		 echo '<option value="' . $Salesmanrow['depart_code'] . '">' . $Salesmanrow['depart_name'] .
            '</option>';
    }
	}
    echo '</select> <span style="color:red">*</span>  </td></tr>';
 	
 echo '<tr>
	 <td bgcolor="#87CEFA">' . _('地址') . ':</td>
        <td colspan="3"><input  type="text"    name="address"  size="65" maxlength="100" value="' . $_POST['address'] . '" /></td>
    </tr>
	<tr>
        <td bgcolor="#87CEFA">' . _('紧急联系人') . ':</td>
        <td><input   type="text" name="jinji_person" value="' . $_POST['jinji_person'] . '" size="16" maxlength="40" /></td>
    
        <td bgcolor="#87CEFA">' . _('联系人电话') . ':</td>
        <td><input  type="text"    name="jinjin_person_telephone"  size="16" maxlength="100" value="' . $_POST['jinjin_person_telephone'] . '" /></td>

        
    </tr>
	<tr><td bgcolor="#87CEFA">' . _('家庭住址') . ':</td>
        <td colspan="3"><input type="text"    name="home_address"  size="65" maxlength="100" value="' . $_POST['home_address'] . '" /></td></tr>
			</tr>

				';

                        if($_POST['lev_date'] == ''){
                             echo'
                        <tr>
						<td bgcolor="#87CEFA">' . _('入职日期') . ':</td>
				<td><input type="text" onfocus="WdatePicker()"  alt="' . $_SESSION['DefaultDateFormat'] . '" name="in_date"   size="10" maxlength="10"  value="' . date('Y-m-d', $_POST['in_date']) . '" /></td>
				<td bgcolor="#87CEFA">' . _('离职日期') . ':</td>
				<td><input type="text" onfocus="WdatePicker()"  alt="' . $_SESSION['DefaultDateFormat'] . '" name="lev_date"   size="10" maxlength="10" title="' . _('lev_date .') . '"  /></td>
			</tr>

                        ';
                        }
                        else{
                      echo'
                        <tr>
				<td bgcolor="#87CEFA">' . _('离职日期') . ':</td>
				<td><input type="text" onfocus="WdatePicker()"  alt="' . $_SESSION['DefaultDateFormat'] . '" name="lev_date"   size="10" maxlength="10" title="' . _('lev_date .') . '" value="' . date('Y-m-d', $_POST['lev_date']) . '" /></td>
			</tr>

                        ';}


  

        echo '</table>';
        echo '<br />
			<div class="centre">
				<input type="submit" name="Addemployee" value="' . "更新员工" . '" />&nbsp;
				<input type="submit" name="Deleteemployee" value="' . _('删除员工') . '" onclick="return confirm(\'' . _('Are You Sure?') . '\');" />
                                <input type="submit" name="return" value="' . "返回上一层" . '" />&nbsp;


</div>';
    }
    echo '</div>
          </form>';
} // end of main ifs
if (isset($_POST['return'])) {
    header('Location: SearchEmployee.php');
}
include('includes/footer.inc');
?>
