<?php
 ob_start();
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include('includes/session.inc');
$Title = _('供应商产品维护');
$ViewTopic= '供应商产品维护';
$BookMark = '供应商产品维护';

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
    $sql = 'select b.po_item_id,a.vendor_code,vendor_name,c.item_no,c.item_name,c.item_desc,b.price,b.enable_flag,vendor_item  from vendors a,po_item_prices_all b,sf_item_no c  where a.vendor_code=b.vendor_code
			and c.item_no=b.stockid ';
    if(isset($_POST['vendorCode']) and $_POST['vendorCode'] != ''){
        $sql = $sql." and a.vendor_code ".LIKE." '%".$_POST['vendorCode']."%' ";
    }
    if(isset($_POST['vendorName']) and $_POST['vendorName'] != ''){
        $sql = $sql." and a.vendor_name ".LIKE." '%".$_POST['vendorName']."%' ";
    }
    if(isset($_POST['item_name']) and $_POST['item_name'] != ''){
        $sql = $sql." and c.item_name ".LIKE." '%".$_POST['item_name']."%' ";
    }
   
	if(isset($_POST['item_desc']) and $_POST['item_desc'] != ''){
        $sql = $sql." and c.item_desc ".LIKE." '%".$_POST['item_desc']."%' ";
    }
	if(isset($_POST['stockid']) and $_POST['stockid'] != ''){
        $sql = $sql." and c.item_no ".LIKE." '%".$_POST['stockid']."%' ";
    }
	 if(isset($_POST['vendor_item']) and $_POST['vendor_item'] != ''){
        $sql = $sql." and b.vendor_item ".LIKE." '%".$_POST['vendor_item']."%' ";
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

echo '<tr><td>' . _('供应商代码') . ':</td><td>';
echo '<input type="text" name="vendorCode" value="' . $_POST['vendorCode'] . '" size="20" maxlength="25" /></td>';
echo '<td>' . _('供应商名称') . ':</td>
	<td>';
echo '<input type="text" name="vendorName" value="' . $_POST['vendorName'] . '" size="20" maxlength="25" /></td>';
echo '<td>' . _('料号') . ':</td>
	<td>';
echo '<input type="text" name="stockid" value="' . $_POST['stockid'] . '" size="20" maxlength="25" /></td>';
echo '</tr>';
echo '<td>' . _('料号名称') . ':</td>
	<td>';
echo '<input type="text" name="item_name" value="' . $_POST['item_name'] . '" size="20" maxlength="25" /></td>';
echo '<td>' . _('规格型号') . ':</td>
	<td>';
echo '<input type="text" name="item_desc" value="' . $_POST['item_desc'] . '" size="20" maxlength="25" /></td>';
echo '<td>' . _('供应商料号') . ':</td>
	<td>';
echo '<input type="text" name="vendor_item" value="' . $_POST['vendor_item'] . '" size="20" maxlength="25" /></td>';

echo '</table><div class="centre"><input type="submit" name="Search" value="查找"> &nbsp;&nbsp;<input type="submit" name="add_new" value="新增"></div>';

if (isset($_POST['Search']) and isset($result) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])){
    $ListCount = DB_num_rows($result);
    $ListPageMax = ceil($ListCount / 10);
    
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
    echo '
                    <table cellpadding="2" class="selection" >';

    echo '<tr>
                    <th class="ascending"width = 100>' . _('供应商代码') . '</th>
                    <th class="ascending"width = 200>' . _('供应商名称') . '</th>
                    <th class="ascending"width = 150>' . _('料号') . '</th>
                    <th class="ascending"width = 150>' . _('料号名称') . '</th>
				    <th class="ascending"width = 200>' . _('规格型号') . '</th>
				    <th class="ascending"width = 100>' . _('供应商料号') . '</th>
				    <th class="ascending"width = 50>' . _('生效') . '</th> 
                   
          </tr>';
    $k = 0; //row counter to determine background colour
    $RowIndex = 0;
    


    if (DB_num_rows($result) <> 0) {
        DB_data_seek($result, ($_POST['PageOffset'] - 1) * 10);
        $i = 0; //counter for input controls
        while (($myrow = DB_fetch_array($result)) AND ($RowIndex <> 10)) {
			if ($k == 1) {
				echo '<tr class="EvenTableRows">';
				$k = 0;
			} else {
				echo '<tr class="OddTableRows">';
				$k = 1;
			} 
			echo '  <td><a href="' . $RootPath . '/SearchPOPrice2.php?UpdatevendorCode=' . $myrow['po_item_id'] . '">' . $myrow['vendor_code'] . '</td>
				<td>' . $myrow['vendor_name'] . '</td>
				<td>' . $myrow['item_no'] . '</td>
				<td>' . $myrow['item_name'] . '</td>
				<td>' . $myrow['item_desc'] . '</td>
				<td>' . $myrow['vendor_item'] . '</td>
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
    header('Location: AddVendorPOPrice.php');
}
include('includes/footer.inc');