<?php
 ob_start();
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include('includes/session.inc');
$Title = _('采购进料检验记录明细报表');
$ViewTopic= '采购进料检验记录明细报表';
$BookMark = '采购进料检验记录明细报表';

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
    $sql = "SELECT prt.receipt_num, a.po_num, b.line, b.stockid, c.item_desc,c.item_spec, a.vendor_code, d.vendor_name, prt.transaction_quantity, prt.transaction_type, b.subinventory_code, b.need_date, prt.transaction_date,prt.created_by,prt.receipt_line
FROM po_headers_all A, po_lines_all B, sf_item_no c, vendors d, po_rcv_transactions prt
WHERE a.po_num = b.po_num
AND a.vendor_code = d.vendor_code
AND b.po_num = prt.po_num
and  prt.transaction_type in ('REJECT','ACCEPT')
AND b.line = prt.po_line
AND b.stockid = c.item_no  ";
    if(isset($_POST['vendorCode']) and $_POST['vendorCode'] != ''){
        $sql = $sql." and d.vendor_code ".LIKE." '%".$_POST['vendorCode']."%' ";
    }
    if(isset($_POST['vendorName']) and $_POST['vendorName'] != ''){
        $sql = $sql." and d.vendor_name ".LIKE." '%".$_POST['vendorName']."%' ";
    }
    if(isset($_POST['FromDate']) and $_POST['FromDate'] != ''){
        $sql = $sql." and prt.transaction_date >=".strtotime($_POST['FromDate'])." ";
    }
     if(isset($_POST['ToDate']) and $_POST['ToDate'] != ''){
        $sql = $sql." and prt.transaction_date <=".strtotime($_POST['ToDate'])." ";
    }
	if (isset($_POST['Stockid_from']) and $_POST['Stockid_from'] != '') {
        $sql = $sql . " and prt.stockid >=  '" . $_POST['Stockid_from'] . "'";
    }
    if (isset($_POST['Stockid_to']) and $_POST['Stockid_to'] != '') {
        $sql = $sql . " and prt.stockid <=  '" . $_POST['Stockid_to'] . "' ";
    }
    if (isset($_POST['receipt_num_from']) and $_POST['receipt_num_from'] != '') {
        $sql = $sql . " and prt.receipt_num >=  '" . $_POST['receipt_num_from'] . "'";
    }
    if (isset($_POST['receipt_num_to']) and $_POST['receipt_num_to'] != '') {
        $sql = $sql . " and prt.receipt_num <=  '" . $_POST['receipt_num_to'] . "' ";
    }

	if (isset($_POST['po_num_from']) and $_POST['po_num_from'] != '') {
        $sql = $sql . " and a.po_num >=  '" . $_POST['po_num_from'] . "'";
    }
    if (isset($_POST['po_num_to']) and $_POST['po_num_to'] != '') {
        $sql = $sql . " and  a.po_num <=  '" . $_POST['po_num_to'] . "' ";
    }

	 if($_POST['checkresult']!=""){
        if($_POST['checkresult']=="合格"){
            $sql .= " and prt.transaction_type = 'ACCEPT'";
        }
        if($_POST['checkresult']=="不合格"){
             $sql .=" and prt.transaction_type = 'REJECT'";
        }     
      }
  
  
    $result = DB_query($sql,$db);
    if (DB_num_rows($result)==0) {
        unset($result);
        prnMsg(_('找不到该来料报检单，请重新输入条件查询！') ,'error');
    }
}


echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('采购进料检验记录明细报表') . '</p>';
echo '<table cellpadding="3" class="selection">';

echo '<tr><td  >' . _('供应商名称') . ':</td><td>';
echo '<input type="text" name="vendorName" value="' . $_POST['vendorName'] . '" size="20" maxlength="25" /></td>';
echo '<td>' . _('供应商代码') . ':</td>
	<td>';
echo '<input type="text" name="vendorCode" value="' . $_POST['vendorCode'] . '" size="20" maxlength="25" /></td>';
 
if (!isset($_POST['FromDate'])) {
    $_POST['FromDate'] = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 30, date("Y")));
}
if (!isset($_POST['ToDate'])) {
    $_POST['ToDate'] = Date('Y-m-d', mktime(0, 0, 0, date("m"), date("d") + 1, date("Y")));
}
echo '<td  >' . '检验日期' . _('From') . ':</td>
		<td><input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="FromDate" maxlength="10" size="11" value="' . $_POST['FromDate'] . '" /></td>
		<td>' . _('To') . ':</td>
		<td><input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="ToDate" maxlength="10" size="11" value="' . $_POST['ToDate'] . '" /></td>
	</tr>';

echo '<tr><td >' . _('料号起') . ':</td><td>';
echo '<input type="text" name="Stockid_from" value="' . $_POST['Stockid_from'] . '" size="20" maxlength="25" /></td>';
echo '<td>' . _('料号止') . ':</td>
	<td>';
echo '<input type="text" name="Stockid_to" value="' . $_POST['Stockid_to'] . '" size="20" maxlength="25" /></td>';
 

echo ' <td >' . _('采购单起') . ':</td><td>';
echo '<input type="text" name="po_num_from" value="' . $_POST['po_num_from'] . '" size="20" maxlength="25" /></td>';
echo '<td>' . _('采购单止') . ':</td>
	<td>';
echo '<input type="text" name="po_num_to" value="' . $_POST['po_num_to'] . '" size="20" maxlength="25" /></td>';
echo '</tr>';

echo '<tr><td >' . _('收料单起') . ':</td><td>';
echo '<input type="text" name="receipt_num_from" value="' . $_POST['receipt_num_from'] . '" size="20" maxlength="25" /></td>';
echo '<td>' . _('收料单止') . ':</td>
	<td>';
echo '<input type="text" name="receipt_num_to" value="' . $_POST['receipt_num_to'] . '" size="20" maxlength="25" /></td>';
 
echo ' <td>' . _('检验结果') . ':</td><td><select name="checkresult">';

            echo '<option  selected="selected" value=""></option>';
  
            echo '<option   value="合格">合格</option>';

            echo '<option   value="不合格">不合格</option>'; 

            echo '</select></td></tr>';

echo '</table><div class="centre"><input type="submit" name="Search" value="查找"></div>';

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
                     
					                    <th width =100>' . '来料报检单号' . '</th>
										 <th width =20>' . '行' . '</th>
                                        <th width =90 >' . '供应商编码' . '</th> 
	                                    <th width =100>' . '采购单号' . '</th>
                                        <th width =20>' . '行' . '</th>
                                        <th width =150>' . '料号' . '</th>
                                        <th width =150>' . '料号名称' . '</th> 
										<th width =150>' . '规格型号' . '</th> 
                                         <th width =70 >' . '判定结果' . '</th>
                                         <th width =70 >' . '检验量' . '</th>
                                         
										  <th width =160 >' . '检验日期' . '</th>
										   <th width =80 >' . '检验人员' . '</th>
                   
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

			if  ($myrow['transaction_type']== 'ACCEPT') {
			    $transaction_type='合格';
					}
				 else  {
				 $transaction_type='不合格';}
 

			 echo '<td>' . $myrow['receipt_num'] . '</td>';
echo '<td>' . $myrow['receipt_line'] . '</td>'; 
                echo '<td>' . $myrow['vendor_code'] . '</td>'; 
                echo '<td><font color="red">' . $myrow['po_num'] . '</font></td>';
            echo '<td>' . $myrow['line'] . ' </td>';
            echo '<td>' . $myrow['stockid'] . ' </td>';
            echo '<td>' . $myrow['item_desc'] . ' </td>';
			echo '<td>' . $myrow['item_spec'] . ' </td>';
            echo '<td>' . $transaction_type. ' </td>';
             echo '<td>' . $myrow['transaction_quantity'] . ' </td>'; 
            echo '<td>' . date('Y-m-d H:i:s',$myrow['transaction_date']) . '</td>';
            echo '<td>' . $myrow['created_by'] . ' </td>';
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
    header('Location: AddSupplier.php');
}
include('includes/footer.inc');