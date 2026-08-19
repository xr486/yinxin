<?php
 ob_start();
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include('includes/session.inc');
$Title = _('采购入库明细报表');
$ViewTopic= '采购入库明细报表';
$BookMark = '采购入库明细报表';

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
    $sql = "SELECT prt.receipt_num,prr.receipt_line,a.po_num,b.line,b.stockid, c.item_name,c.item_desc, c.units, a.vendor_code, d.vendor_name, prt.transaction_quantity, prt.transaction_type, prr.subinventory_code, b.need_date, prt.transaction_date,prt.created_by, b.price,b.line_amount,a.tax_name,a.tax_rate,(select realname from www_users where userid=prt.created_by) realname,a.tax_flag,prt.delivery_num,b.quantity
FROM po_headers_all a, po_lines_all b, sf_item_no c, vendors d, po_rcv_transactions prt,po_rcv_receipt_line prr
WHERE  a.po_num = b.po_num
AND a.vendor_code = d.vendor_code
AND b.po_num = prt.po_num
and prr.receipt_num=prt.receipt_num
and prt.po_num=prr.po_num
and prt.po_line=prr.po_line 
and b.stockid=prr.stockid
and  prt.transaction_type in ('POIN')
AND b.line = prt.po_line
AND b.stockid = c.item_no  ";
    if(isset($_POST['vendor_code']) and $_POST['vendor_code'] != ''){
        $sql = $sql." and d.vendor_code ".LIKE." '%".$_POST['vendor_code']."%' ";
    }
    if(isset($_POST['vendor_name']) and $_POST['vendor_name'] != ''){
        $sql = $sql." and d.vendor_name ".LIKE." '%".$_POST['vendor_name']."%' ";
    }
    if(isset($_POST['FromDate']) and $_POST['FromDate'] != ''){
        $sql = $sql." and prt.transaction_date >=".strtotime($_POST['FromDate'])." ";
    }
     if(isset($_POST['ToDate']) and $_POST['ToDate'] != ''){
        $sql = $sql." and prt.transaction_date <=".strtotime($_POST['ToDate'])." ";
    }
	if (isset($_POST['item_no']) and $_POST['item_no'] != '') { 
        $sql = $sql." and prt.stockid ".LIKE." '%".$_POST['item_no']."%' ";
    }
    if (isset($_POST['item_name']) and $_POST['item_name'] != '') { 
        $sql = $sql." and c.item_name ".LIKE." '%".$_POST['item_name']."%' ";
    }
	if (isset($_POST['item_desc']) and $_POST['item_desc'] != '') { 
        $sql = $sql." and c.item_desc ".LIKE." '%".$_POST['item_desc']."%' ";
    }
    if (isset($_POST['receipt_num']) and $_POST['receipt_num'] != '') {
        $sql = $sql . " and prt.receipt_num ".LIKE." '%".$_POST['receipt_num']."%' ";
    }
    if (isset($_POST['delivery_num']) and $_POST['delivery_num'] != '') {
        $sql = $sql . " and prt.delivery_num ".LIKE." '%".$_POST['delivery_num']."%' ";
    }

	if (isset($_POST['po_num']) and $_POST['po_num'] != '') {
        $sql = $sql . " and a.po_num  ".LIKE." '%".$_POST['po_num']."%' ";
    }
    if (isset($_POST['sub_code']) and $_POST['sub_code'] != '') { 
        $sql = $sql." and prr.subinventory_code ".LIKE." '%".$_POST['sub_code']."%' ";
    }

	$sql .=" order by prt.transaction_date desc, prr.receipt_num, prr.receipt_line ,prr.po_num, prr.po_line"; 
  
    $result = DB_query($sql,$db);
    if (DB_num_rows($result)==0) {
        unset($result);
        prnMsg(_('找不到该来料报检单，请重新输入条件查询！') ,'error');
    }
}


echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('采购入库明细报表') . '</p>';
echo '<table cellpadding="3" class="selection">';


echo '<div class="text-nav">';
echo '<div class="text-nav-1"><div>' . _('供应商名称') . ':</div>';
echo '<input type="text" name="vendor_name" value="' . $_POST['vendor_name'] . '" size="20" maxlength="25" /></div>';
echo '<div class="text-nav-1"><div>' . _('供应商代码') . ':</div>
';
echo '<input type="text" name="vendor_code" value="' . $_POST['vendor_code'] . '" size="20" maxlength="25" /></div>';
 
if (!isset($_POST['FromDate'])) {
    $_POST['FromDate'] = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 30, date("Y")));
}
if (!isset($_POST['ToDate'])) {
    $_POST['ToDate'] = Date('Y-m-d', mktime(0, 0, 0, date("m"), date("d") + 1, date("Y")));
}
echo '<div class="text-nav-1"><div>' . '入库日期' . _('起') . ':</div>
		<input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="FromDate" maxlength="10" size="11" value="' . $_POST['FromDate'] . '" /></div>
		<div class="text-nav-1"><div>' . _('入库日期止') . ':</div>
		<input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="ToDate" maxlength="10" size="11" value="' . $_POST['ToDate'] . '" /></div>
';

echo '<div class="text-nav-1"><div>' . _('料号') . ':</div>';
echo '<input type="text" name="item_no" value="' . $_POST['item_no'] . '" size="20" maxlength="25" /></div>';
echo '<div class="text-nav-1"><div>' . _('料号名称') . ':</div>
';
echo '<input type="text" name="item_name" value="' . $_POST['item_name'] . '" size="20" maxlength="25" /></div>';
 echo '<div class="text-nav-1"><div>' . _('规格型号') . ':</div>
';
echo '<input type="text" name="item_desc" value="' . $_POST['item_desc'] . '" size="20" maxlength="25" /></div>';


echo '<div class="text-nav-1"><div>' . _('仓库简称') . ':</div>
';
echo '<input type="text" name="sub_code" value="' . $_POST['sub_code'] . '" size="20" maxlength="25" /></div>';

echo '<div class="text-nav-1"><div>' . _('采购单号') . ':</div>';
echo '<input type="text" name="po_num" value="' . $_POST['po_num'] . '" size="20" maxlength="25" /></div>';
echo '<div class="text-nav-1"><div>' . _('收料单号') . ':</div>';
echo '<input type="text" name="receipt_num" value="' . $_POST['receipt_num'] . '" size="20" maxlength="25" /></div>';
echo '<div class="text-nav-1"><div>' . _('入库单号') . ':</div>';
echo '<input type="text" name="delivery_num" value="' . $_POST['delivery_num'] . '" size="20" maxlength="25" /></div>';

echo '</div>';
 
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
    echo '			  <div class="text-nav-table">
                    <table cellpadding="2" class="selection" >';

    echo '<tr>
                     
					                    <th width =100>' . '入库单号' . '</th>
					                    <th width =100>' . '收料单号' . '</th>
										<th width =20>' . '行' . '</th>
                                        <th width =90 >' . '供应商编码' . '</th> 
                                        <th width =90 >' . '供应商名称' . '</th> 
	                                    <th width =100>' . '采购单号' . '</th>
                                        <th width =20>' . '行' . '</th>
                                        <th width =150>' . '料号' . '</th>
                                         <th width =200>' . '料号名称' . '</th> 
										<th width =200>' . '规格型号' . '</th> 

                                         <th width =60 >' . '仓库' . '</th>
                                         <th width =70 >' . '入库数量' . '</th>
										 ';
                      if ($_SESSION['price_flag']=='N') {
                   echo '<th class="ascending"width = 30>' . _('单价') . '</th>
				   <th class="ascending"width = 30>' . _('未税金额') . '</th>
                   <th class="ascending"width = 30>' . _('含税金额') . '</th>';
					  }
    echo ' <th width =160 >' . '税率' . '</th>';
                   echo ' <th width =160 >' . '入库日期' . '</th>
										   <th width =80 >' . '入库人员' . '</th>
										   <th >' . '含价格' . '</th>
										   <th >' . '未含价格' . '</th>
                                           
            </tr>';
    $k = 0; //row counter to determine background colour
    $RowIndex = 0;
    $all_qty=0;
    $all_amount=0;

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

		 $all_qty=$all_qty + $myrow['transaction_quantity'];
         $all_amount=$all_amount +round(($myrow['price'] * $myrow['transaction_quantity']) ,2);
			 echo '<td>' . $myrow['delivery_num'] . '</td>';
			 echo '<td>' . $myrow['receipt_num'] . '</td>';
			 echo '<td>' . $myrow['receipt_line'] . '</td>';
                echo '<td>' . $myrow['vendor_code'] . '</td>'; 
                echo '<td>' . $myrow['vendor_name'] . '</td>'; 
                echo '<td><font color="red">' . $myrow['po_num'] . '</font></td>';
            echo '<td>' . $myrow['line'] . ' </td>';
            echo '<td>' .$myrow['stockid'] . ' </td>';
            echo '<td>' . $myrow['item_name'] . ' </td>'; 
			echo '<td>' . $myrow['item_desc'] . ' </td>';


            echo '<td style="text-align:center;">' . $myrow['subinventory_code'] . ' </td>';          
             echo '<td style="text-align:center;">' . $myrow['transaction_quantity'] . ' </td>'; 
			 
                      if ($_SESSION['price_flag']=='N') {
						echo   '<td style="text-align:center;">' . $myrow['price'] . '</td>' ;  
             
             if($myrow['tax_flag']=='Y'){
                echo   '<td style="text-align:center;">' . round(($myrow['line_amount'] / $myrow['quantity'] * $myrow['transaction_quantity'])/(1+$myrow['tax_rate']),2). '</td>' ;  
                echo   '<td style="text-align:center;">' . round(($myrow['line_amount'] / $myrow['quantity'] * $myrow['transaction_quantity']),2) . '</td>' ;  
              }else{
                echo   '<td style="text-align:center;">' . round(($myrow['line_amount'] / $myrow['quantity'] * $myrow['transaction_quantity']),2). '</td>' ;  
                echo   '<td style="text-align:center;">' . round(($myrow['line_amount'] / $myrow['quantity'] * $myrow['transaction_quantity'])*(1+$myrow['tax_rate']),2) . '</td>' ;  
              }
					  }  
                     
                      echo '<td>' . $myrow['tax_name'] . ' </td>';
                         
            echo '<td>' . date('Y-m-d H:i:s',$myrow['transaction_date']) . '</td>';
            echo '<td>' . $myrow['realname'] . ' </td>';
            if($myrow['delivery_num'] != '') {
            echo '<td><a href="' . $RootPath . '/PrintInPODelivery.php?Updatedelivery_num='.$myrow['delivery_num'].'" target="_blank"  >打印</a></td>';
            echo '<td><a href="' . $RootPath . '/PrintInPODeliveryNoPrice.php?Updatedelivery_num='.$myrow['delivery_num'].'" target="_blank"  >打印</a></td>';

            }else{
                echo '<td><a href="' . $RootPath . '/OldPrintInPODelivery.php?Updatedelivery_num='.$myrow['receipt_num'].'" target="_blank"  >打印</a></td>';
            echo '<td><a href="' . $RootPath . '/OldPrintInPODeliveryNoPrice.php?Updatedelivery_num='.$myrow['receipt_num'].'" target="_blank"  >打印</a></td>';
            }
			echo '
			</tr>';
			$i++;
			$RowIndex++;
			//end of page full new headings if
		} //end loop through vendors
		echo '<tr><td>合计</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td style="text-align:center;">' . $all_qty . '</td><td></td><td></td></tr>';
		echo '</table></div>';
		echo '<input type="hidden" name="JustSelectedAvendor" value="Yes" />';
          echo '<div>
                 <a href="' . $RootPath . '/InPODeliveryReportExcel.php?vendor_name=' .$_POST['vendor_name'] .'&vendor_code=' .$_POST['vendor_code'] .'&FromDate=' .$_POST['FromDate'] . '&ToDate=' .$_POST['ToDate'] .'&item_no=' .$_POST['item_no'] . '&item_name=' .$_POST['item_name']. '&item_desc=' .$_POST['item_desc']  .'&po_num=' .$_POST['po_num'] .'&receipt_num=' .$_POST['receipt_num'] . '&subinventory_code=' .$_POST['sub_code'] .' ">' .'资料导出成Excel表' . '</a>
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
/*
 *
echo '<tr><td>' . _('供应商名称') . ':</td><td>';
echo '<input type="text" name="vendor_name" value="' . $_POST['vendor_name'] . '" size="20" maxlength="25" /></td>';
echo '<td>' . _('供应商代码') . ':</td>
	<td>';
echo '<input type="text" name="vendor_code" value="' . $_POST['vendor_code'] . '" size="20" maxlength="25" /></td>';

if (!isset($_POST['FromDate'])) {
    $_POST['FromDate'] = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 30, date("Y")));
}
if (!isset($_POST['ToDate'])) {
    $_POST['ToDate'] = Date('Y-m-d', mktime(0, 0, 0, date("m"), date("d") + 1, date("Y")));
}
echo '<td>' . '入库日期' . _('From') . ':</td>
		<td><input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="FromDate" maxlength="10" size="11" value="' . $_POST['FromDate'] . '" /></td>
		<td>' . _('To') . ':</td>
		<td><input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="ToDate" maxlength="10" size="11" value="' . $_POST['ToDate'] . '" /></td>
	</tr>';

echo '<tr><td >' . _('料号起') . ':</td><td>';
echo '<input type="text" name="Stockid_from" value="' . $_POST['Stockid_from'] . '" size="20" maxlength="25" /></td>';
echo '<td>' . _('料号止') . ':</td>
	<td>';
echo '<input type="text" name="Stockid_to" value="' . $_POST['Stockid_to'] . '" size="20" maxlength="25" /></td>';


echo '<td >' . _('采购单起') . ':</td><td>';
echo '<input type="text" name="po_num" value="' . $_POST['po_num'] . '" size="20" maxlength="25" /></td>';
echo '<td>' . _('采购单止') . ':</td>
	<td>';
echo '<input type="text" name="po_num_to" value="' . $_POST['po_num_to'] . '" size="20" maxlength="25" /></td>';
echo '</tr>';

echo '<tr><td >' . _('收料单起') . ':</td><td>';
echo '<input type="text" name="receipt_num" value="' . $_POST['receipt_num'] . '" size="20" maxlength="25" /></td>';
echo '<td>' . _('收料单止') . ':</td>
	<td>';
echo '<input type="text" name="receipt_num_to" value="' . $_POST['receipt_num_to'] . '" size="20" maxlength="25" /></td>';
echo '</tr>';
 */