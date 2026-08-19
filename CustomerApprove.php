<?php
 ob_start();
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include('includes/session.inc');
$Title = _('客户审核');
$ViewTopic= '客户审核';
$BookMark = '客户审核';

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

unset($result);

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
    $sql = 'select customer_code,customer_name,customers_status,Customer_contacts,contacts_phone,contacts_mail,tax_name,customer_address,enable_flag,b.employee_num, (select employee_name from hr_employees d where b.employee_num= d.employee_num)  employee_name   from customers b    where customers_status = "待签核"
	  ';
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
	 if(isset($_POST['employee_name']) and $_POST['employee_name'] != ''){
        $sql = $sql." and b.employee_num in (select employee_num from hr_employees where  employee_name ".LIKE." '%".$_POST['employee_name']."%' )";
    }
	if(isset($_POST['employee_num']) and $_POST['employee_num'] != ''){
        $sql = $sql." and b.employee_num ".LIKE." '%".$_POST['employee_num']."%' ";
    }
   
	if(isset($_POST['ContactsPhone']) and $_POST['ContactsPhone'] != ''){
        $sql = $sql." and contacts_phone ".LIKE." '%".$_POST['ContactsPhone']."%' ";
    }
	if(isset($_POST['tax_name']) and $_POST['tax_name'] != ''){
        $sql = $sql." and tax_name ".LIKE." '%".$_POST['tax_name']."%' ";
    }
 if(isset($_POST['customer_address']) and $_POST['customer_address'] != ''){
        $sql = $sql." and customer_address ".LIKE." '%".$_POST['customer_address']."%' ";
    }
	 if(isset($_POST['enable_flag']) and $_POST['enable_flag'] != ''){
        $sql = $sql." and enable_flag ".LIKE." '%".$_POST['enable_flag']."%' ";
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
echo '<table cellpadding="3" class="selection">';
echo '<div class="text-nav">';

echo '<div class="text-nav-1 ">
<div>' . _('客户全称') . ':</div>';
echo '<input type="text" name="CustomerName" value="' . $_POST['CustomerName'] . '" size="20" maxlength="25" /></div>';
echo '<div class="text-nav-1 ">
<div>' . _('客户简称') . ':</div>';
echo '<input type="text" name="CustomerCode" value="' . $_POST['CustomerCode'] . '" size="20" maxlength="25" /></div>';
echo '<div class="text-nav-1 ">
<div>' . _('联系人姓名') . ':</div>';
echo '<input type="text" name="CustomerContacts" value="' . $_POST['CustomerContacts'] . '" size="20" maxlength="25" /></div>';

echo '<div class="text-nav-1 ">
<div>' . _('地址') . ':</div>';
echo '<input type="text" name="customer_address" value="' . $_POST['customer_address'] . '" size="20" maxlength="25" /></div>';
echo '<div class="text-nav-1 ">
<div>' . _('税别') . ':</div>';
echo '<input type="text" name="tax_name" value="' . $_POST['tax_name'] . '" size="20" maxlength="25" /></div>';

	
	echo ' <div class="text-nav-1 ">
        <div>' . _('是否生效') . ':</div>
		<select required="required" name="enable_flag" value"">';
if ($_POST['enable_flag']=='Y' or $_POST['enable_flag']==''){
	echo '<option selected="selected" value="Y">' . _('是') . '</option>';
	echo '<option value="N">' . _('否') . '</option>';
} else {
 	echo '<option selected="selected" value="N">' . _('否') . '</option>';
	echo '<option value="Y">' . _('是') . '</option>';
}
echo '</select></div>
	';
echo '<div class="text-nav-1 ">
<div>' . _('业务员名称') . ':</div>';
echo '<input type="text" name="employee_name" value="' . $_POST['employee_name'] . '" size="10" maxlength="25" /></div>';

echo '<div class="text-nav-1 ">
<div>' . _('业务员工号') . ':</div>';
echo '<input type="text" name="employee_num" value="' . $_POST['employee_num'] . '" size="10" maxlength="25" /></div>';
 

echo '</div></table><div class="centre"><input type="submit" name="Search" value="查找"></div>';

if (isset($_POST['Search']) and isset($result) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])){
    $ListCount = DB_num_rows($result);
    $ListPageMax = ceil($ListCount / 50);
    
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
            echo '<br /><div class="centre">&nbsp;&nbsp;' . _('第') . '' . $_POST['PageOffset'] . ' ' . _('页，共') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ': ';
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
                    <input type="submit" name="Go1" value="' . _('Go') . '" />
                    <input type="submit" name="Previous" value="' . _('Previous') . '" />
                    <input type="submit" name="Next" value="' . _('Next') . '" />';
            echo '</div>';
    }
    echo '<div style="overflow:auto;">';
    echo '
                    <table cellpadding="2" class="selection" >';

    echo '<tr>
                    <th class="ascending" width = 150>' . _('客户简称') . '</th>
                    <th class="ascending"width = 250>' . _('客户全称') . '</th>
                    <th class="ascending">' . _('状态') . '</th>
         
                    <th class="ascending"width = 150>' . _('联系人') . '</th>
                
                    <th class="ascending"width = 150>' . _('电话') . '</th>
                    <th class="ascending"width = 200>' . _('地址') . '</th> 
						 <th class="ascending" >' . _('税别') . '</th>
						 <th class="ascending" >' . _('业务员') . '</th>
						 <th  >' . _('是否生效') . '</th>
            </tr>';
    $k = 0; //row counter to determine background colour
    $RowIndex = 0;
    


    if (DB_num_rows($result) <> 0) {
        DB_data_seek($result, ($_POST['PageOffset'] - 1) * 50);
        $i = 0; //counter for input controls
        while (($myrow = DB_fetch_array($result)) AND ($RowIndex <> 50)) {
			if ($k == 1) {
				echo '<tr class="EvenTableRows">';
				$k = 0;
			} else {
				echo '<tr class="OddTableRows">';
				$k = 1;
			}
			echo '  <td><a href="' . $RootPath . '/CustomerApprove2.php?UpdateCustomerCode=' . $myrow['customer_code'] . '">' . $myrow['customer_code'] . '</td>
				<td>' . $myrow['customer_name'] . '</td>
				<td>' . $myrow['customers_status'] . '</td>
				<td>' . $myrow['Customer_contacts'] . '</td>
				<td>' . $myrow['contacts_phone'] . '</td>
				<td>' . $myrow['customer_address'] . '</td> 
					<td>' . $myrow['tax_name'] . '</td>
					<td>' . $myrow['employee_name'] . '</td>
					<td>' . $myrow['enable_flag'] . '</td>
				';

       
			echo '
			</tr>';
			$i++;
			$RowIndex++;
			//end of page full new headings if
		} //end loop through customers
		echo '</table></div>';
		echo '<input type="hidden" name="JustSelectedAvendor" value="Yes" />';
	}

        if (isset($ListPageMax) AND $ListPageMax > 1) {
                echo '<br /><div class="centre">&nbsp;&nbsp;' . _('第') . '' . $_POST['PageOffset'] . ' ' . _('页，共') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ': ';
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
                        <input type="submit" name="Go2" value="' . _('Go') . '" />
                        <input type="submit" name="Previous" value="' . _('Previous') . '" />
                        <input type="submit" name="Next" value="' . _('Next') . '" />';
                echo '</div>';
    }

}
echo '</div></form>';
if (isset($_POST['add_new'])) {
    header('Location: AddCustomer.php');
}
include('includes/footer.inc');