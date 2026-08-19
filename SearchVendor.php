<?php
 ob_start();
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include('includes/session.inc');
$Title = _('查找供应商');
$ViewTopic= '查找供应商';
$BookMark = '查找供应商';

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
    $sql = 'select vendor_code,vendor_name,vendor_contacts,contacts_phone,contacts_mail'
            . ' from vendors where 1=1';
    if(isset($_POST['vendor_code']) and $_POST['vendor_code'] != ''){
        $sql = $sql." and vendor_code ".LIKE." '%".$_POST['vendor_code']."%' ";
    }
    if(isset($_POST['vendor_name']) and $_POST['vendor_name'] != ''){
        $sql = $sql." and vendor_name ".LIKE." '%".$_POST['vendor_name']."%' ";
    }
    if(isset($_POST['vendor_contacts']) and $_POST['vendor_contacts'] != ''){
        $sql = $sql." and vendor_contacts ".LIKE." '%".$_POST['vendor_contacts']."%' ";
    }
   
	if(isset($_POST['contacts_phone']) and $_POST['contacts_phone'] != ''){
        $sql = $sql." and contacts_phone ".LIKE." '%".$_POST['contacts_phone']."%' ";
    }

    
  
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
echo '<div class="text-nav">';

echo '<div class="text-nav-1"><div>' . _('供应商代号') . ':</div>';
echo '<input type="text" name="vendor_code"    value="' . $_POST['vendor_code'] . '"  size="15" maxlength="25" /></div>';
echo '<div class="text-nav-1"><div>' . _('供应商名称') . ':</div>';
echo '<input type="text" name="vendor_name"    value="' . $_POST['vendor_name'] . '"  size="15" maxlength="25" /></div>';
echo '<div class="text-nav-1"><div>' . _('联系人') . ':</div>';
echo '<input type="text" name="vendor_contacts"    value="' . $_POST['vendor_contacts'] . '"  size="15" maxlength="25" /></div>';
echo '</div>';

echo '</table><div class="centre"><input type="submit" name="Search" value="查找"></div>';

if (isset($_POST['Search']) and isset($result) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])){
    $ListCount = DB_num_rows($result);
    $ListPageMax = ceil($ListCount / $_SESSION['DisplayRecordsMax'] );
    
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
                    <th bgcolor="#87CEFA" class="ascending" width = 120>' . _('供应商代码') . '</th>
                    <th bgcolor="#87CEFA" class="ascending"width = 290>' . _('供应商名称') . '</th>
         
                    <th bgcolor="#87CEFA" class="ascending"width = 150>' . _('联系人') . '</th>
                
                    <th bgcolor="#87CEFA" class="ascending"width = 150>' . _('电话') . '</th>
                    <th bgcolor="#87CEFA" class="ascending"width = 200>' . _('邮箱') . '</th>
                   
            </tr>';
    $k = 0; //row counter to determine background colour
    $RowIndex = 0;
    


    if (DB_num_rows($result) <> 0) {
        DB_data_seek($result, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);
        $i = 0; //counter for input controls
        while (($myrow = DB_fetch_array($result)) AND ($RowIndex <> $_SESSION['DisplayRecordsMax'] )) {
			if ($k == 1) {
				echo '<tr class="EvenTableRows">';
				$k = 0;
			} else {
				echo '<tr class="OddTableRows">';
				$k = 1;
			}
			echo '  <td><a href="' . $RootPath . '/SearchVendor2.php?Updatevendor_code=' . $myrow['vendor_code'] . '">' . $myrow['vendor_code'] . '</td>
				<td>' . $myrow['vendor_name'] . '</td>
				<td>' . $myrow['vendor_contacts'] . '</td>
				<td>' . $myrow['contacts_phone'] . '</td>
				<td>' . $myrow['contacts_mail'] . '</td>
				';

       
			echo '
			</tr>';
			$i++;
			$RowIndex++;
			//end of page full new headings if
		} //end loop through vendors
		echo '</table>';
		 echo '<div>
                <a href="' . $RootPath . '/SearchVendorExcel.php?vendor_code=' .$_POST['vendor_code'] .'&vendor_name=' .$_POST['vendor_name'] .'&vendor_contacts=' .$_POST['vendor_contacts'] .' ">' .'资料导出Excel表' . '</a>
               </div>';     
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

include('includes/footer.inc');