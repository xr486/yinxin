<?php
 ob_start();
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include('includes/session.inc');
$Title = _('客户料号关系查询');
$ViewTopic= '客户料号关系查询';
$BookMark = '客户料号关系查询';

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
    $sql = 'select a.customer_code,a.customer_name,b.customer_item,b.remark,c.item_no,c.item_name,c.item_desc,b.enable_flag,b.item_relation_id 
	from customers a,customer_item_relation b,sf_item_no c  where a.customer_code=b.customer_code
	and b.item_no=c.item_no 
  ';
	 
    if(isset($_POST['CustomerCode']) and $_POST['CustomerCode'] != ''){
        $sql = $sql." and a.customer_code ".LIKE." '%".$_POST['CustomerCode']."%' ";
    }
    if(isset($_POST['CustomerName']) and $_POST['CustomerName'] != ''){
        $sql = $sql." and a.customer_name ".LIKE." '%".$_POST['CustomerName']."%' ";
    }
    if(isset($_POST['item_no']) and $_POST['item_no'] != ''){
        $sql = $sql." and c.item_no ".LIKE." '%".$_POST['item_no']."%' ";
    }

	if(isset($_POST['customer_item']) and $_POST['customer_item'] != ''){
        $sql = $sql." and b.customer_item ".LIKE." '%".$_POST['customer_item']."%' ";
    }
	 
 if(isset($_POST['item_name']) and $_POST['item_name'] != ''){
        $sql = $sql." and item_name ".LIKE." '%".$_POST['item_name']."%' ";
    }
	 if(isset($_POST['item_desc']) and $_POST['item_desc'] != ''){
        $sql = $sql." and item_desc ".LIKE." '%".$_POST['item_desc']."%' ";
    }
  if(isset($_POST['enable_flag']) and $_POST['enable_flag'] != ''){
        $sql = $sql." and b.enable_flag='".$_POST['enable_flag']."'";
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
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('查找客户料号') . '</p>';
echo '<table cellpadding="3" class="selection">';
echo '<div class="text-nav">';
echo '<div class="text-nav-1 "><div>' . _('客户全称') . ':</div>';
echo '<input type="text" name="CustomerName"  value="' . $_POST['CustomerName'] . '" size="20" maxlength="25" /></div>';
echo '<div class="text-nav-1 "><div>' . _('客户简称') . ':</div>';
echo '<input type="text" name="CustomerCode"   value="' . $_POST['CustomerCode'] . '" size="20" maxlength="25" /></div>';
echo '<div class="text-nav-1 "><div>' . _('客户料号') . ':</div>';
echo '<input type="text" name="customer_item" value="' . $_POST['customer_item'] . '" size="20" maxlength="25" /></div>';
echo '<div class="text-nav-1 "><div>' . _('公司料号') . ':</div>';
echo '<input type="text" name="item_no" value="' . $_POST['item_no'] . '" size="20" maxlength="25" /></div>';
echo '<div class="text-nav-1 "><div>' . _('料号名称') . ':</div>';
echo '<input type="text" name="item_name" value="' . $_POST['item_name'] . '" size="20" maxlength="25" /></div>';
echo '<div class="text-nav-1 "><div>' . _('规格型号') . ':</div>';
echo '<input type="text" name="item_desc" value="' . $_POST['item_desc'] . '" size="20" maxlength="25" /></div>';
echo '<div class="text-nav-1 required"><div>' . _('是否生效') . ':</div>
		<select required="required" name="enable_flag" value"">';   
if ($_POST['enable_flag']=='Y' or $_POST['enable_flag']==''){
echo '<option selected="selected" value="Y">' . _('是') . '</option>';
echo '<option value="N">' . _('否') . '</option>';
} else {
echo '<option selected="selected" value="N">' . _('否') . '</option>';
echo '<option value="Y">' . _('是') . '</option>';
 }
 echo '</select></div>';

echo '</table><div class="centre"><input type="submit" name="Search" value="查找"> &nbsp;&nbsp;<input type="submit" name="add_new" value="新增"> &nbsp;&nbsp;<a href="' . $RootPath . '/SearchCustomerItemExcel.php?CustomerCode=' .$_POST['CustomerCode'] .'&CustomerName=' .$_POST['CustomerName'] .'&item_no=' .$_POST['item_no'] .'&item_name=' .$_POST['item_name'] .'&item_desc=' .$_POST['item_desc'] .'&enable_flag=' .$_POST['enable_flag'] .'">导出Excel</a></div>';

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
                    <th bgcolor="#87CEFA" class="ascending" width = 150>' . _('客户简称') . '</th>
                    <th bgcolor="#87CEFA" class="ascending"width = 250>' . _('客户全称') . '</th>
         
                    <th bgcolor="#87CEFA" class="ascending"width = 150>' . _('料号') . '</th>
                
                    <th bgcolor="#87CEFA" class="ascending"width = 150>' . _('料号名称') . '</th>
                    <th bgcolor="#87CEFA" class="ascending"width = 200>' . _('规格型号') . '</th> 
                    <th bgcolor="#87CEFA" class="ascending"width = 200>' . _('客户料号') . '</th> 
					<th bgcolor="#87CEFA"  >' . _('是否生效') . '</th>
					<th bgcolor="#87CEFA"  >' . _('修改') . '</th>
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
			echo '  <td> ' . $myrow['customer_code'] . '</td>
				<td>' . $myrow['customer_name'] . '</td>
				<td>' . $myrow['item_no'] . '</td>
				<td>' . $myrow['item_name'] . '</td>
				<td>' . $myrow['item_desc'] . '</td>
					<td>' . $myrow['customer_item'] . '</td> 
					<td>' . $myrow['enable_flag'] . '</td>
					<td><a href="' . $RootPath . '/SearchCustomerItem2.php?Updateitem_relation_id=' . $myrow['item_relation_id'] . '">修改</td>
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
    header('Location: CustomerItem.php');
}
include('includes/footer.inc');