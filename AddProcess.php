<?php

ob_start();
/* $Id: scripts.php 6338 2013-09-28 05:10:46Z daintree $ */

include('includes/session.inc');

if (isset($_POST['Edit']) or isset($_GET['Edit']) or isset($_GET['DebtorNo'])) {
    $ViewTopic = 'AccountsReceivable';
    $BookMark = 'AmendVendor';
} else {
    $ViewTopic = 'AccountsReceivable';
    $BookMark = 'NewVendor';
}



$Title = _('新程序名称新增');
/* webERP manual links before header.inc */
$ViewTopic = 'AccountsReceivable';
$BookMark = 'NewVendor';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/CountriesArray.php');

echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/customer.png" title="' . _('Customer') .
 '" alt="" />' . ' ' . _('程序名称新增') . '
	</p>';


if (isset($Errors)) {
    unset($Errors);
}
$Errors = array();


if (isset($_POST['Addscript'])) {

    //initialise no input errors assumed initially before we test
    $InputError = 0;
    $i = 1;
    //$_POST['script'] = mb_strtoupper($_POST['script']);
    $sql2 = "SELECT COUNT(script) FROM scripts WHERE script='" . $_POST['script'] . "'";
	 
    $result2 = DB_query($sql2, $db);
    $myrow2 = DB_fetch_row($result2);
 
  
    if ($myrow2[0] > 0 AND isset($_POST['Addscript']) and $InputError <> 1) {
        $InputError = 1;
        prnMsg(_('功能名称系统已存在'), 'error');
        $Errors[$i] = 'description';
        $i++;
    } elseif (mb_strlen($_POST['function_name']) > 30 OR mb_strlen($_POST['function_name']) == 0) {
        $InputError = 1;
        prnMsg(_('描述不超过30个字且不能为空！'), 'error');
        $Errors[$i] = 'description';
        $i++;
    } elseif ($_SESSION['script'] == 0 AND mb_strlen($_POST['script']) == 0) {
        $InputError = 1;
        prnMsg(_('功能名称不能为空！'), 'error');
        $Errors[$i] = 'script';
        $i++;
    }  

    //没有错误，则执行如下
    //当是 update 则执行update 若是add 的时候，执行insert
    if ($InputError != 1) {

        $SQL_ClientSince = FormatDateForSQL($_POST['ClientSince']);

        if (isset($_POST['Addscript'])) { //it is a new vendor
             
                 $v_date = strtotime(Date('Y-m-d H:i:s'));
                $sql = "INSERT INTO scripts (
							script,
							description,function_name ,
							creation_date,
							created_by	 )
				VALUES ('" . $_POST['script'] . "',
						'" . $_POST['description'] . "','" . $_POST['function_name'] . "',
						'" .  $v_date . "' ,
						'" . $_SESSION['UserID'] . "'   )";
           
            
            $ErrMsg = _('This vendor could not be added because');
            $result = DB_query($sql, $db, $ErrMsg);
            prnMsg(_('功能新建成功'), 'success');
            unset($_POST['script']);
            unset($_POST['function_name']); 
            //unset($_POST['CreditLimit']);
            echo '<br />';
        }
    } else {
        prnMsg(_('新增功能失败！'), 'error');
    }
}


/* DebtorNo could be set from a post or a get when passed as a parameter to this page */

if (isset($_POST['script'])) {
    $script = $_POST['script'];
} elseif (isset($_GET['script'])) {
    $script = $_GET['script'];
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
				<td>' . _('程序名称') . ':</td>
				<td><input ' . (in_array('script', $Errors) ? 'class="inputerror"' : '' ) . '  type="text" name="script"  autofocus="autofocus" value="' . $_POST['script'] . '" size="40" maxlength="40" /></td>
			</tr>
 
			 <tr>
				<td>' . _('功能名称') . ':</td>
				<td><input   type="text"  name="function_name"  autofocus="autofocus" value="' . $_POST['function_name'] . '" size="60" maxlength="80" /></td>
			</tr>
			
			 ';
                       
		 
    echo '</table>';

    echo'</td></tr></table>';







    echo '<div class="centre">
				<input type="submit" name="Addscript" value="' . _('新增') . '" />&nbsp;
				<input type="Reset" name="Reset" value="' . _('清空') . '" />&nbsp;
                                    <input type="submit" name="return" value="' . _('返回') . '" />
			</div>';


    echo '</div>
          </form>';
} // end of main ifs


if (isset($_POST['return'])) {
    header('Location: SetProcess.php');
}

include('includes/footer.inc');
?>
