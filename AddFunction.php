<?php

ob_start();
/* $Id: functions.php 6338 2013-09-28 05:10:46Z daintree $ */

include('includes/session.inc');

if (isset($_POST['Edit']) or isset($_GET['Edit']) or isset($_GET['DebtorNo'])) {
    $ViewTopic = 'AccountsReceivable';
    $BookMark = 'AmendVendor';
} else {
    $ViewTopic = 'AccountsReceivable';
    $BookMark = 'NewVendor';
}



$Title = _('功能维护');
/* webERP manual links before header.inc */
$ViewTopic = 'AccountsReceivable';
$BookMark = 'NewVendor';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/CountriesArray.php');

echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/customer.png" title="' . _('Customer') .
 '" alt="" />' . ' ' . _('功能维护') . '
	</p>';


if (isset($Errors)) {
    unset($Errors);
}
$Errors = array();


if (isset($_POST['AddVendor'])) {

    //initialise no input errors assumed initially before we test
    $InputError = 0;
    $i = 1;
    $_POST['function_name'] = mb_strtoupper($_POST['function_name']);
    $sql2 = "SELECT COUNT(function_name) FROM functions WHERE function_name='" . $_POST['function_name'] . "'";
    $result2 = DB_query($sql2, $db);
    $myrow2 = DB_fetch_row($result2);
 
  
    if ($myrow2[0] > 0 AND isset($_POST['AddVendor']) and $InputError <> 1) {
        $InputError = 1;
        prnMsg(_('功能名称系统已存在'), 'error');
        $Errors[$i] = 'model_name';
        $i++;
    } elseif (mb_strlen($_POST['model_name']) > 30 OR mb_strlen($_POST['model_name']) == 0) {
        $InputError = 1;
        prnMsg(_('功能名称不超过30个字且不能为空！'), 'error');
        $Errors[$i] = 'model_name';
        $i++;
    } elseif ($_SESSION['function_name'] == 0 AND mb_strlen($_POST['function_name']) == 0) {
        $InputError = 1;
        prnMsg(_('功能名称不能为空！'), 'error');
        $Errors[$i] = 'function_name';
        $i++;
    }  

    //没有错误，则执行如下
    //当是 update 则执行update 若是add 的时候，执行insert
    if ($InputError != 1) {

        $SQL_ClientSince = FormatDateForSQL($_POST['ClientSince']);

        if (isset($_POST['AddVendor'])) { //it is a new vendor
             
                 $v_date = strtotime(Date('Y-m-d H:i:s'));
                $sql = "INSERT INTO functions (
							function_name,
							model_name,  
							created_by,
							creation_date,
							last_update_by,
							last_update_date )
				VALUES ('" . $_POST['function_name'] . "',
						'" . $_POST['model_name'] . "', 
						'" . $_SESSION['UserID'] . "',
						'" . $v_date . "',
						'" . $_SESSION['UserID'] . "',
						'" . $v_date . "' )";
           

            $ErrMsg = _('This vendor could not be added because');
            $result = DB_query($sql, $db, $ErrMsg);
            prnMsg(_('功能新建成功'), 'success');
            unset($_POST['function_name']);
            unset($_POST['model_name']);
            unset($_POST['vendor_address']);
            unset($_POST['vendor_contacts']);

            unset($_POST['contacts_phone']);
            unset($_POST['contacts_mail']);
            unset($_POST['effective_date']);
            unset($_POST['$disable_date']);
            //unset($_POST['CreditLimit']);
            echo '<br />';
        }
    } else {
        prnMsg(_('新增功能失败！'), 'error');
    }
}


/* DebtorNo could be set from a post or a get when passed as a parameter to this page */

if (isset($_POST['function_name'])) {
    $function_name = $_POST['function_name'];
} elseif (isset($_GET['function_name'])) {
    $function_name = $_GET['function_name'];
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

 /*   echo '<tr>
				<td>' . _('功能名称') . ':</td>
				<td><input ' . (in_array('function_name', $Errors) ? 'class="inputerror"' : '' ) . ' type="text" name="function_name"  autofocus="autofocus" value="' . $_POST['function_name'] . '" size="40" maxlength="40" /></td>
			</tr>



		    <tr>
				<td>' . _('模组名称') . ':</td>
				<td><input ' . (in_array('model_name', $Errors) ? 'class="inputerror"' : '' ) . ' type="text" name="model_name"  autofocus="autofocus" value="' . $_POST['model_name'] . '" size="20" maxlength="40" /></td>
			</tr>
			
			 ';
  */                     
		
	echo '<tr>
				<td>' . _('功能名称') . ':</td>
				<td><input ' . (in_array('function_name', $Errors) ? 'class="inputerror"' : '' ) . ' type="text" name="function_name"  autofocus="autofocus" value="' . $_POST['function_name'] . '" size="40" maxlength="40" /></td>
			</tr>
				';	
	echo '<tr>
     	<td>' . _('模组名称') . ':</td>
		<td><select required="required" name="model_name">';

	echo '<option value="销售">' . _('销售') . '</option>';
	echo '<option value="采购">' . _('采购') . '</option>';
	echo '<option value="仓库">' . _('仓库') . '</option>';
	echo '<option value="售后">' . _('售后') . '</option>';
	echo '<option value="生产">' . _('生产') . '</option>';
	echo '<option value="应付">' . _('应付') . '</option>';
	echo '<option value="应收">' . _('应收') . '</option>';
	echo '<option value="财务">' . _('财务') . '</option>'; 
	echo '<option value="BOM">' . _('BOM') . '</option>'; 
	echo '<option value="外协">' . _('外协') . '</option>'; 
	echo '<option value="品质">' . _('品质') . '</option>'; 
	echo '<option value="技服">' . _('技服') . '</option>'; 
	echo '<option value="月结">' . _('月结') . '</option>'; 
	echo '<option value="系统设置">' . _('系统设置') . '</option>';
	echo '</select></td>
	</tr>
			 ';





		
    echo '</table>';

    echo'</td></tr></table>';







    echo '<div class="centre">
				<input type="submit" name="AddVendor" value="' . _('新增') . '" />&nbsp;
				<input type="Reset" name="Reset" value="' . _('清空') . '" />&nbsp;
                                    <input type="submit" name="return" value="' . _('返回') . '" />
			</div>';


    echo '</div>
          </form>';
} // end of main ifs


if (isset($_POST['return'])) {
    header('Location: SetFunction.php');
}

include('includes/footer.inc');
?>
