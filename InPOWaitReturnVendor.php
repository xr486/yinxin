<?php
 ob_start();
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include('includes/session.inc');
$Title = _('采购拒收未退厂商明细报表');
$ViewTopic= '采购拒收未退厂商明细报表';
$BookMark = '采购拒收未退厂商明细报表';

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
    $sql ="select 
rh.receipt_num,rl.receipt_line,
a.po_num,
b.line,
b.stockid,
c.Item_name,
	c.item_desc,c.units,
a.vendor_code,
d.vendor_name,
ifnull(b.quantity,0) quantity,
ifnull(rl.quantity_received,0) this_received,
ifnull(rl.reject_area_quantity,0) wait_return_qty, 
ifnull(rl.inspection_bad_return_vendor,0) inspection_bad_return_qty, 
rl.subinventory_code,
b.need_date,rh.creation_date
FROM  po_headers_all a,
      po_lines_all b,
			po_rcv_receipt_header rh,
			po_rcv_receipt_line  rl,
                        sf_item_no c,vendors d
WHERE  a.vendor_code=d.vendor_code
and a.po_num=b.po_num    
and b.po_num=rl.po_num
and b.line=rl.po_line
and rl.receipt_num=rh.receipt_num
and b.stockid=c.item_no 
and ifnull(rl.reject_area_quantity,0)>0 ";
  if(isset($_POST['Stockid_from']) and $_POST['Stockid_from'] != ''){
        $sql = $sql." and  c.item_no ".LIKE." '%".$_POST['Stockid_from']."%' ";
    }
	 if(isset($_POST['item_name']) and $_POST['item_name'] != ''){
        $sql = $sql." and  c.item_name ".LIKE." '%".$_POST['item_name']."%' ";
    }
	 
	if(isset($_POST['po_num']) and $_POST['po_num'] != ''){
        $sql = $sql." and  b.po_num ".LIKE." '%".$_POST['po_num']."%' ";
    }
   
	 
	if(isset($_POST['receipt_num']) and $_POST['receipt_num'] != ''){
        $sql = $sql." and  rh.receipt_num ".LIKE." '%".$_POST['receipt_num']."%' ";
    }
    if(isset($_POST['vendor_code']) and $_POST['vendor_code'] != ''){
        $sql = $sql." and d.vendor_code ".LIKE." '%".$_POST['vendor_code']."%' ";
    }
    if(isset($_POST['vendor_name']) and $_POST['vendor_name'] != ''){
        $sql = $sql." and d.vendor_name ".LIKE." '%".$_POST['vendor_name']."%' ";
    }
    if(isset($_POST['FromDate']) and $_POST['FromDate'] != ''){
        $sql = $sql." and rh.creation_date >=".strtotime($_POST['FromDate'])." ";
    }
     if(isset($_POST['ToDate']) and $_POST['ToDate'] != ''){
        $sql = $sql." and rh.creation_date <=".strtotime($_POST['ToDate'])." ";
    }
    $sql .= " order by rh.creation_date desc";
  
    $result = DB_query($sql,$db);
    if (DB_num_rows($result)==0) {
        unset($result);
        prnMsg(_('找不到该供应商，请重新输入条件查询！') ,'error');
    }
}


echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('查找采购进料拒收未退厂商明细') . '</p>';
echo '<table cellpadding="3" class="selection">';

echo '<div class="text-nav">
<div class="text-nav-1"><div>' . _('供应商名称') . ':</div>';
echo '<input type="text" name="vendorName" value="' . $_POST['vendorName'] . '" size="20" maxlength="25" /></div>';
echo '<div class="text-nav-1"><div>' . _('供应商代码') . ':</div>
	';
echo '<input type="text" name="vendorCode" value="' . $_POST['vendorCode'] . '" size="20" maxlength="25" /></div>';


echo ' <div class="text-nav-1"><div>' . _('料号') . ':</div>';
echo '<input type="text" name="Stockid_from" value="' . $_POST['Stockid_from'] . '" size="20" maxlength="25" /></div>';
 echo ' <div class="text-nav-1"><div>' . _('料号名称') . ':</div>';
echo '<input type="text" name="item_name" value="' . $_POST['item_name'] . '" size="20" maxlength="25" /></div>';
 
 
echo '<div class="text-nav-1"><div>' . _('来料报检单号') . ':</div>';
echo '<input type="text" name="receipt_num" value="' . $_POST['receipt_num'] . '" size="20" maxlength="25" /></div>';
echo '<div class="text-nav-1"><div>' . _('采购单号') . ':</div>
	';
echo '<input type="text" name="po_num" value="' . $_POST['po_num'] . '" size="20" maxlength="25" /></div>';
echo ' ';
if (!isset($_POST['FromDate'])) {
    $_POST['FromDate'] = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 30, date("Y")));
}
if (!isset($_POST['ToDate'])) {
    $_POST['ToDate'] = Date('Y-m-d', mktime(0, 0, 0, date("m"), date("d") + 1, date("Y")));
}
echo '<div class="text-nav-1"><div>收货日起:</div>
		<input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="FromDate" maxlength="20" size="11" value="' . $_POST['FromDate'] . '" /></div>
		<div class="text-nav-1"><div>' . _('收货日止') . ':</div>
		<input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="ToDate" maxlength="20" size="11" value="' . $_POST['ToDate'] . '" /></div>
        </div>';

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
	                                 <th bgcolor="#87CEFA" width =100>' . '来料报检单号' . '</th>
									 <th bgcolor="#87CEFA" width =20>' . '行' . '</th>
                                        <th bgcolor="#87CEFA" width =90 >' . '供应商编码' . '</th> 
	                                   <th bgcolor="#87CEFA" width =100>' . '采购单号' . '</th>
                                        <th bgcolor="#87CEFA" width =20>' . '行' . '</th>
                                        <th bgcolor="#87CEFA" width =150>' . '料号' . '</th>
                                            <th bgcolor="#87CEFA" width =200>' . '料号名称' . '</th>
										 <th bgcolor="#87CEFA" width =200>' . '规格型号' . '</th>

                                         <th bgcolor="#87CEFA"  width = 90>' . '采购数量' . '</th> 
                                          <th bgcolor="#87CEFA" width =70 >' . '来料报检量' . '</th>
                                         <th bgcolor="#87CEFA" width =70 >' . '已退量' . '</th>
                                          <th bgcolor="#87CEFA" width =80 >' . '待退厂商量' . '</th>    
										  <th bgcolor="#87CEFA"  width = 160>' . _('来料报检日期') . '</th> 
                                         <th bgcolor="#87CEFA" width =80 >' . '仓库' . '</th>
                   
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
			echo '<td>' . $myrow['receipt_num'] . '</td>';
				echo '<td>' . $myrow['receipt_line'] . '</td>';
            echo '<td>' . $myrow['vendor_code'] . '</td>'; 
            echo '<td><font color="red">' . $myrow['po_num'] . '</font></td>';
            echo '<td>' . $myrow['line'] . ' </td>';
            echo '<td>' . $myrow['stockid'] . ' </td>';
            echo '<td>' . $myrow['Item_name'] . ' </td>';
             echo '<td>' . $myrow['item_desc'] . ' </td>';
            echo '<td>' . $myrow['quantity'] . ' </td>';
            echo '<td>' . $myrow['this_received'] . ' </td>';
            echo '<td>' . $myrow['inspection_bad_return_qty'] . ' </td>';
			echo '<td>' . $myrow['wait_return_qty'] . ' </td>';
			echo '<td>' . date('Y-m-d H:i:s',$myrow['creation_date']) . '</td>';
            echo '<td>' . $myrow['subinventory_code'] . ' </td>';
       
			echo '
			</tr>';
			$i++;
			$RowIndex++;
			//end of page full new headings if
		} //end loop through vendors
		echo '</table></div>';
		echo '<input type="hidden" name="JustSelectedAvendor" value="Yes" />';

		echo '<div>
        <a href="' . $RootPath . '/InPOWaitReturnVendorExcel.php?receipt_num=' .$_POST['receipt_num'] .
        '&vendor_name=' .$_POST['vendor_name'] .'&vendor_code=' .$_POST['vendor_code'] .'&po_num=' .$_POST['po_num'] .  
        '&item_no=' .$_POST['item_no'] .'&item_name=' .$_POST['item_name'] .
         '&item_name=' .$_POST['item_name'] .'&FromDate=' .$_POST['FromDate'] . '&ToDate=' .$_POST['ToDate'] .' ">' .'资料导出成Excel表' . '</a>
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