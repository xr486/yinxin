<?php
 ob_start();
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include('includes/session.inc');
$Title = _('盘点结果审核');
$ViewTopic= '盘点结果审核';
$BookMark = '盘点结果审核';

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
    $sql = "select * from inv_check_headers_all b    where status='待审核'
	  ";
	  
    if(isset($_POST['subinventory_code']) and $_POST['subinventory_code'] != ''){
        $sql = $sql." and subinventory_code ".LIKE." '%".$_POST['subinventory_code']."%' ";
    }
    if(isset($_POST['check_num']) and $_POST['check_num'] != ''){
        $sql = $sql." and check_num ".LIKE." '%".$_POST['check_num']."%' ";
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
	 
  
  
    $result = DB_query($sql,$db);
    if (DB_num_rows($result)==0) {
        unset($result);
        prnMsg(_('找不到该客户，请重新输入条件查询！') ,'error');
    }
}


echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('查找待审核盘点单') . '</p>';
echo '<table cellpadding="3" class="selection">';
echo '<div class="text-nav">';

echo '<div class="text-nav-1 ">
<div>' . _('仓库') . ':</div>';
echo '<input type="text" name="subinventory_code" value="' . $_POST['subinventory_code'] . '" size="20" maxlength="25" /></div>';
echo '<div class="text-nav-1 ">
<div>' . _('盘点单号') . ':</div>';
echo '<input type="text" name="check_num" value="' . $_POST['check_num'] . '" size="20" maxlength="25" /></div>';



echo '</div></table><div class="centre"><input type="submit" name="Search" value="查找"> </div>';

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
                    <th class="ascending"  >' . _('盘点单号') . '</th>
                    <th  >' . _('仓库') . '</th>
          <th   >' . _('状态') . '</th>
                    <th  width = 100>' . _('仓管负责人') . '</th>
                
                    <th  width = 100>' . _('盘点负责人') . '</th>
                    <th  >' . _('建立日期') . '</th> 
						 <th  >' . _('建立人') . '</th> 
						 <th  >' . _('差异审核') . '</th>
						 <th  >' . _('明细查询') . '</th>
						 
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

			if ($myrow['approve_date']>1) {
				$approve_date=date('Y-m-d H:i:s',$myrow['approve_date']);
			}  else {
				$approve_date='';
			}  
			echo '  <td>' . $myrow['check_num'] . '</td>
				<td>' . $myrow['subinventory_code'] . '</td>
				<td>' . $myrow['status'] . '</td>
				<td>' . $myrow['subinventory_person'] . '</td>
				<td>' . $myrow['check_person'] . '</td>
				<td>' . date('Y-m-d H:i:s',$myrow['creation_date']) . '</td> 
					<td>' . $myrow['created_by'] . '</td> 
					<td><a href="' . $RootPath . '/InvCheckResultApprove2.php?Updatecheck_num=' . $myrow['check_num'] . '">差异审核</td>
					<td><a href="' . $RootPath . '/InvCheckResultApprove3.php?Updatecheck_num=' . $myrow['check_num'] . '">所有明细</td>
			 
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
    header('Location: InvCheckCreate2.php');
}
include('includes/footer.inc');