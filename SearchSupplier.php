<?php
 ob_start();
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include('includes/session.inc');
$Title = _('维护供应商');
$ViewTopic= '维护供应商';
$BookMark = '维护供应商';

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
    $sql = 'select vendor_code,vendor_name,vendor_status,vendor_contacts,contacts_phone,contacts_mail,contacts_fax,taxpayerid,enable_flag from vendors  where 1=1';
    if(isset($_POST['vendorCode']) and $_POST['vendorCode'] != ''){
        $sql = $sql." and vendor_code ".LIKE." '%".$_POST['vendorCode']."%' ";
    }
    if(isset($_POST['vendorName']) and $_POST['vendorName'] != ''){
        $sql = $sql." and vendor_name ".LIKE." '%".$_POST['vendorName']."%' ";
    }
    if(isset($_POST['vendorContacts']) and $_POST['vendorContacts'] != ''){
        $sql = $sql." and vendor_contacts ".LIKE." '%".$_POST['vendorContacts']."%' ";
    }
   
	if(isset($_POST['ContactsPhone']) and $_POST['ContactsPhone'] != ''){
        $sql = $sql." and contacts_phone ".LIKE." '%".$_POST['ContactsPhone']."%' ";
    }
	if(isset($_POST['contacts_fax']) and $_POST['contacts_fax'] != ''){
        $sql = $sql." and contacts_fax ".LIKE." '%".$_POST['contacts_fax']."%' ";
    }
	if(isset($_POST['taxpayerid']) and $_POST['taxpayerid'] != ''){
        $sql = $sql." and taxpayerid ".LIKE." '%".$_POST['taxpayerid']."%' ";
    }
	if(isset($_POST['enable_flag']) and $_POST['enable_flag'] != ''){
        $sql = $sql." and enable_flag ".LIKE." '%".$_POST['enable_flag']."%' ";
    }
	
 
 
    
    $sql = $sql." order by vendor_code ";
  
    $result = DB_query($sql,$db);
    if (DB_num_rows($result)==0) {
        unset($result);
        prnMsg(_('找不到该供应商，请重新输入条件查询！') ,'error');
    }
}


echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('查找供应商') . '</p>';
echo '<table cellpadding="3" class="selection">';

echo '<tr><td>' . _('供应商代码') . ':</td><td>';
echo '<input type="text" name="vendorCode" value="' . $_POST['vendorCode'] . '" size="20" maxlength="25" /></td>';
echo '<td>' . _('供应商名称') . ':</td>
	<td>';
echo '<input type="text" name="vendorName" value="' . $_POST['vendorName'] . '" size="20" maxlength="25" /></td>';
echo '<td>' . _('联系人') . ':</td>
	<td>';
echo '<input type="text" name="vendorContacts" value="' . $_POST['vendorContacts'] . '" size="20" maxlength="25" /></td>';
echo '</tr>';
echo '<td>' . _('传真') . ':</td>
	<td>';
echo '<input type="text" name="contacts_fax" value="' . $_POST['contacts_fax'] . '" size="20" maxlength="25" /></td>';
echo '<td>' . _('纳税人识别号') . ':</td>
	<td>';
echo '<input type="text" name="taxpayerid" value="' . $_POST['taxpayerid'] . '" size="20" maxlength="25" /></td>';
	echo ' <td>' . _('是否生效') . ':</td>
		<td><select required="required" name="enable_flag">';
if ($_POST['enable_flag']=='N'){
	echo '<option selected="selected" value="N">' . _('否') . '</option>';
	echo '<option value="Y">' . _('是') . '</option>';
	
} else {
 	echo '<option selected="selected" value="Y">' . _('是') . '</option>';
	echo '<option value="N">' . _('否') . '</option>';
}
echo '</select></td>
	</tr>';
echo '</table><div class="centre"><input type="submit" name="Search" value="查找"> &nbsp;&nbsp;<input type="submit" name="add_new" value="新增">&nbsp;&nbsp;<input type="submit" name="add_news" value="上传"></div>';

if (isset($_POST['Search']) and isset($result) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])){
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
                    <th class="ascending"width = 150>' . _('供应商代码') . '</th>
                    <th class="ascending"width = 250>' . _('供应商名称') . '</th>
                    <th >' . _('状态') . '</th>
                    <th class="ascending"width = 150>' . _('联系人') . '</th>
                    <th class="ascending"width = 150>' . _('电话') . '</th>
				    <th class="ascending"width = 200>' . _('邮箱') . '</th>
				    <th class="ascending"width = 150>' . _('传真') . '</th>
					<th class="ascending"width = 200>' . _('纳税人识别号') . '</th>
					<th class="ascending">' . _('是否生效') . '</th>
                   
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
			echo '  <td><a href="' . $RootPath . '/AddSuppliers2.php?UpdatevendorCode=' . $myrow['vendor_code'] . '">' . $myrow['vendor_code'] . '</td>
				<td>' . $myrow['vendor_name'] . '</td>
                                <td>' . $myrow['vendor_status'] . '</td>
				<td>' . $myrow['vendor_contacts'] . '</td>
				<td>' . $myrow['contacts_phone'] . '</td>
				<td>' . $myrow['contacts_mail'] . '</td>
				<td>' . $myrow['contacts_fax'] . '</td>
				<td>' . $myrow['taxpayerid'] . '</td>
				<td>' . $myrow['enable_flag'] . '</td>
				';

       
			echo '
			</tr>';
			$i++;
			$RowIndex++;
			//end of page full new headings if
		} //end loop through vendors
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
    header('Location: AddSupplier.php');
}
if (isset($_POST['add_news'])) {
        header('Location: SearchSupplierupload.php?New=Y');
    }
include('includes/footer.inc');