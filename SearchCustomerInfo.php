<?php
 ob_start();
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include('includes/session.inc');
$Title = _('资料查询客户');
$ViewTopic= '资料查询客户';
$BookMark = '资料查询客户';

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

unset($result);


 
  $sql = "select customer_code,customer_name,Customer_contacts,contacts_phone,contacts_mail,customers_status  
 from customers where 1=1   ";
 
if ($_SESSION['SaleFlag']=='Y') {
		  $sql = $sql . " and  employee_num='".$_SESSION['SalesMan']."' ";
  }

  $result = DB_query($sql,$db);
    if (DB_num_rows($result)==0) {
        unset($result);
    }


if (isset($_POST['Go1']) OR isset($_POST['Go2'])) {
	$_POST['PageOffset'] = (isset($_POST['Go1']) ? $_POST['PageOffset1'] : $_POST['PageOffset2']);
	$_POST['Go'] = '';
}
if (!isset($_POST['PageOffset'])) {
	$_POST['PageOffset'] = 1;
} else {
	if ($_POST['PageOffset'] == 0) {
		$_POST['PageOffset'] = 1;
	}
}

if(isset($_POST['Search']) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])){
   
        $sql = "select customer_code,customer_name,Customer_contacts,contacts_phone,contacts_mail ,customers_status 
       from customers where 1=1   ";
       

	  if ($_SESSION['SaleFlag']=='Y') {
		  $sql = $sql . " and   employee_num='".$_SESSION['SalesMan']."' ";
  }

    if(isset($_POST['CustomerCode']) and $_POST['CustomerCode'] != ''){
        $sql = $sql." and customer_code ".LIKE." '%".$_POST['CustomerCode']."%' ";
    }
    if(isset($_POST['CustomerName']) and $_POST['CustomerName'] != ''){
        $sql = $sql." and customer_name ".LIKE." '%".$_POST['CustomerName']."%' ";
    }
    if(isset($_POST['CustomerContacts']) and $_POST['CustomerContacts'] != ''){
        $sql = $sql." and Customer_contacts ".LIKE." '%".$_POST['CustomerContacts']."%' ";
    }
   
	if(isset($_POST['contacts_phone']) and $_POST['contacts_phone'] != ''){
        $sql = $sql." and contacts_phone ".LIKE." '%".$_POST['contacts_phone']."%' ";
    }
 
    
  
    $result = DB_query($sql,$db);
    if (DB_num_rows($result)==0) {
        unset($result);
        prnMsg(_('找不到该客户，请重新输入条件查询！') ,'error');
    }
}


echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('查找客户') . '</p>';
echo '<div class="text-nav">';
echo '<div class="text-nav-1 "><div>' . _('客户名称') . ':</div>';
echo '<input type="text" name="CustomerName" value="' . $_POST['CustomerName'] . '" size="20" maxlength="25" /></div>';
echo '<div class="text-nav-1 "><div>' . _('客户代码') . ':</div>';
echo '<input type="text" name="CustomerCode" value="' . $_POST['CustomerCode'] . '" size="20" maxlength="25" /></div>';
echo '<div class="text-nav-1 "><div>' . _('联系人姓名') . ':</div>';
echo '<input type="text" name="CustomerContacts" value="' . $_POST['CustomerContacts'] . '" size="20" maxlength="25" /></div>';
echo '<div class="text-nav-1 "><div>' . _('电话') . ':</div>';
echo '<input type="text" name="contacts_phone" value="' . $_POST['contacts_phone'] . '" size="20" maxlength="25" /></div>';
echo '</div>';
echo '</table><div class="centre"><input type="submit" name="Search" value="查找"> &nbsp;&nbsp;</div>';

if (  ( isset($_POST['Search']) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])) ){
    $ListCount = DB_num_rows($result);
    $ListPageMax = ceil($ListCount / $_SESSION['DisplayRecordsMax']);
    
    if (isset($_POST['Next'])) {
            if ($_POST['PageOffset'] < $ListPageMax) {
                    $_POST['PageOffset'] = $_POST['PageOffset'] + 1;
            }
    }
    if (isset($_POST['Previous'])) {
            if ($_POST['PageOffset'] > 1) {
                    $_POST['PageOffset'] = $_POST['PageOffset'] - 1;
            }
    }
    echo '<input type="hidden" name="PageOffset" value="' . $_POST['PageOffset'] . '" />';
    if ($ListPageMax > 1) {
            echo '<br /><div class="centre">&nbsp;&nbsp;' . _('第') . '' . $_POST['PageOffset'] . ' ' . _('页，共') . ' ' . $ListPageMax . ' ' . _('页') . '. ' . _('转到页') . ': ';
            echo '<select name="PageOffset1">';
            $ListPage = 1;
            while ($ListPage <= $ListPageMax) {
                    if ($ListPage == $_POST['PageOffset']) {
                            echo '<option value="' . $ListPage . '" selected="selected">' . $ListPage . '</option>';
                    } else {
                            echo '<option value="' . $ListPage . '">' . $ListPage . '</option>';
                    }
                    $ListPage++;
            }
            echo '</select>
                    <input type="submit" name="Go1" value="' . _('转到') . '" />
                    <input type="submit" name="Previous" value="' . _('上一页') . '" />
                    <input type="submit" name="Next" value="' . _('下一页') . '" />';
            echo '</div>';
    }
    echo '
                    <table cellpadding="2" class="selection" >';

    echo '<tr>
                    <th bgcolor="#87CEFA" class="ascending" >' . _('客户编号') . '</th>
                    <th bgcolor="#87CEFA" >' . _('状态') . '</th>
                    <th bgcolor="#87CEFA" class="ascending"width = 250>' . _('客户名称') . '</th>
         
                    <th bgcolor="#87CEFA" class="ascending"width = 150>' . _('联系人') . '</th>
                
                    <th bgcolor="#87CEFA" class="ascending"width = 150>' . _('电话') . '</th>
                    <th bgcolor="#87CEFA" class="ascending"width = 200>' . _('邮箱') . '</th>
                   
            </tr>';
    $k = 0; //row counter to determine background colour
    $RowIndex = 0;
    


    if (DB_num_rows($result) <> 0) {
        DB_data_seek($result, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);
        $i = 0; //counter for input controls
        while (($myrow = DB_fetch_array($result)) AND ($RowIndex <> $_SESSION['DisplayRecordsMax'])) {
			if ($k == 1) {
				echo '<tr class="EvenTableRows">';
				$k = 0;
			} else {
				echo '<tr class="OddTableRows">';
				$k = 1;
			}
			echo '  <td><a target="view_window" href="' . $RootPath . '/AddCustomers.php?UpdateCustomerCode=' . $myrow['customer_code'] . '">' . $myrow['customer_code'] . '</td>
				<td>' . $myrow['customers_status'] . '</td>
				<td>' . $myrow['customer_name'] . '</td>
				<td>' . $myrow['Customer_contacts'] . '</td>
				<td>' . $myrow['contacts_phone'] . '</td>
				<td>' . $myrow['contacts_mail'] . '</td>
				';

       
			echo '
			</tr>';
			$i++;
			$RowIndex++;
			//end of page full new headings if
		} //end loop through customers
		echo '</table>';
		echo '<input type="hidden" name="JustSelectedAvendor" value="Yes" />';
	}

        if (isset($ListPageMax) AND $ListPageMax > 1) {
                echo '<br /><div class="centre">&nbsp;&nbsp;' . _('第') . '' . $_POST['PageOffset'] . ' ' . _('页，共') . ' ' . $ListPageMax . ' ' . _('页') . '. ' . _('转到页') . ': ';
                echo '<select name="PageOffset2">';
                $ListPage = 1;
                while ($ListPage <= $ListPageMax) {
                        if ($ListPage == $_POST['PageOffset']) {
                                echo '<option value="' . $ListPage . '" selected="selected">' . $ListPage . '</option>';
                        } 
                        else {
                                echo '<option value="' . $ListPage . '">' . $ListPage . '</option>';
                        }
                        $ListPage++;
                } 
                echo '</select>
                        <input type="submit" name="Go2" value="' . _('转到') . '" />
                        <input type="submit" name="Previous" value="' . _('上一页') . '" />
                        <input type="submit" name="Next" value="' . _('下一页') . '" />';
                echo '</div>';
    }

}
echo '</div></form>';
if (isset($_POST['add_new'])) {
    header('Location: AddCustomer.php');
}
include('includes/footer.inc');