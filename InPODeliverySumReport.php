<?php
 ob_start();
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include('includes/session.inc');
$Title = _('采购入库汇总报表');
$ViewTopic= '采购入库汇总报表';
$BookMark = '采购入库汇总报表';

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
    $sql = "SELECT  b.stockid, c.item_desc, c.item_name,a.vendor_code, d.vendor_name, prt.transaction_type, prr.subinventory_code,sum(prt.transaction_quantity) transaction_quantity 
FROM po_headers_all a, po_lines_all b, sf_item_no c, vendors d, po_rcv_transactions prt,po_rcv_receipt_line prr
WHERE a.po_num = b.po_num
AND a.vendor_code = d.vendor_code
AND b.po_num = prt.po_num
and prr.receipt_num=prt.receipt_num
and prt.po_num=prr.po_num
and prt.po_line=prr.po_line 
and b.stockid=prr.stockid
and prt.receipt_line=prr.receipt_line
and  prt.transaction_type in ('POIN')
AND b.line = prt.po_line
AND b.stockid = prt.stockid
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
        $sql = $sql." and prt.stockid ".LIKE." '%".$_POST['Stockid_from']."%' ";
    }
    if (isset($_POST['item_name']) and $_POST['item_name'] != '') { 
        $sql = $sql." and c.item_name ".LIKE." '%".$_POST['item_name']."%' ";
    }
    if (isset($_POST['receipt_num_from']) and $_POST['receipt_num_from'] != '') {
        $sql = $sql . " and prt.receipt_num ".LIKE." '%".$_POST['receipt_num_from']."%' ";
    }
     

	if (isset($_POST['po_num_from']) and $_POST['po_num_from'] != '') {
        $sql = $sql . " and a.po_num  ".LIKE." '%".$_POST['po_num_from']."%' ";
    }
    if (isset($_POST['sub_code']) and $_POST['sub_code'] != '') { 
        $sql = $sql." and prr.subinventory_code ".LIKE." '%".$_POST['sub_code']."%' ";
    }

	$sql .=" group by b.stockid, c.item_desc,c.item_name, a.vendor_code, d.vendor_name, prt.transaction_type, prr.subinventory_code"; 
  
    $result = DB_query($sql,$db);
    if (DB_num_rows($result)==0) {
        unset($result);
        prnMsg(_('找不到该来料报检单，请重新输入条件查询！') ,'error');
    }
}


echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('采购入库汇总报表') . '</p>';
echo '<table cellpadding="3" class="selection">';
echo '<div class="text-nav">';
echo '<div class="text-nav-1"><div>' . _('供应商名称') . ':</div>';
echo '<input type="text" name="vendorName" value="' . $_POST['vendorName'] .'" size="20" maxlength="25" /></div>';
echo '<div class="text-nav-1"><div>' . _('供应商代码') . ':</div>';
echo '<input type="text" name="vendorCode" value="' . $_POST['vendorCode'] .'" size="20" maxlength="25" /></div>';
    if (!isset($_POST['FromDate'])) {
        $_POST['FromDate'] = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 30, date("Y")));
    }
    if (!isset($_POST['ToDate'])) {
        $_POST['ToDate'] = Date('Y-m-d', mktime(0, 0, 0, date("m"), date("d") + 1, date("Y")));
    }
    echo '<div class="text-nav-1"><div>' . '入库日期' . _('起') . ':</div>
    <input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] .
'" name="FromDate" maxlength="10" size="11" value="' . $_POST['FromDate'] . '" /></div>
<div class="text-nav-1"><div>' . _('入库日期止') . ':</div>
    <input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] .
'" name="ToDate" maxlength="10" size="11" value="' . $_POST['ToDate'] . '" /></div>';
echo '<div class="text-nav-1"><div>' . _('料号') . ':</div>';
echo '<input type="text" name="Stockid_from" value="' . $_POST['Stockid_from'] .'" size="20" maxlength="25" /></div>';

echo '<div class="text-nav-1"><div>' . _('料号名称') . ':</div>';
echo '<input type="text" name="item_name" value="' . $_POST['item_name'] .'" size="20" maxlength="25" /></div>';  

echo '<div class="text-nav-1"><div>' . _('采购单') . ':</div>';
echo '<input type="text" name="po_num_from" value="' . $_POST['po_num_from'] .'" size="20" maxlength="25" /></div>';

echo '<div class="text-nav-1"><div>' . _('仓库简称') . ':</div>';
echo '<input type="text" name="sub_code" value="' . $_POST['sub_code'] .'" size="20" maxlength="25" /></div>';

echo '<div class="text-nav-1"><div>' . _('收料单') . ':</div>';
echo '<input type="text" name="receipt_num_from" value="' . $_POST['receipt_num_from'] .'" size="20" maxlength="25" /></div>';

echo '</tr>';
 
echo '</table><div class="centre"><input type="submit" name="Search" value="查找"></div>';

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
    echo ' <div class="text-nav-table">
                    <table cellpadding="2" class="selection" >';

    echo '<tr>
                        <th bgcolor="#87CEFA" width =100 >' . '供应商编码' . '</th>
										<th bgcolor="#87CEFA"  width = 250>' . _('供应商名称') . '</th> 
                                        <th bgcolor="#87CEFA" width =150>' . '料号' . '</th>
                                         <th bgcolor="#87CEFA" width =200>' . '料号名称' . '</th>
							            <th bgcolor="#87CEFA" width =350>' . '规格型号' . '</th>
                                         <th bgcolor="#87CEFA" width =70 >' . '仓库' . '</th>
                                         <th bgcolor="#87CEFA" width =80 >' . '入库数量' . '</th>      
                   
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

		  
            echo '<td>' . $myrow['vendor_code'] . '</td>';
			echo '<td>' . $myrow['vendor_name'] . '</td>';              
            echo '<td>' . $myrow['stockid'] . ' </td>';
            echo '<td>' . $myrow['item_name'] . ' </td>';
             echo '<td>' . $myrow['item_desc'] . ' </td>';
            echo '<td>' . $myrow['subinventory_code']. ' </td>';
             echo '<td>' . $myrow['transaction_quantity'] . ' </td>';  
			echo '
			</tr>';
			$i++;
			$RowIndex++;
			//end of page full new headings if
		} //end loop through vendors
		echo '</table></div>';
		echo '<input type="hidden" name="JustSelectedAvendor" value="Yes" />';
       
				echo '<div>
                 <a href="' . $RootPath . '/InPODeliverySumReportExcel.php?vendor_name=' .$_POST['vendorName'] .'&vendor_code=' .$_POST['vendorCode'] .'&FromDate=' .$_POST['FromDate'] . '&ToDate=' .$_POST['ToDate'] .'&stockid=' .$_POST['Stockid_from'] . '&item_name=' .$_POST['item_name'] .'&po_num=' .$_POST['po_num_from'] .'&receipt_num=' .$_POST['receipt_num_from'] . '&subinventory_code=' .$_POST['sub_code'] .' ">' .'资料导出成Excel表' . '</a>
                </div>';
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
include('includes/footer.inc');