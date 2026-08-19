<?php
 ob_start();
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include('includes/session.inc');
$Title = _('待检验采购单信息查询');
$ViewTopic= '待检验采购单信息查询';
$BookMark = '待检验采购单信息查询';

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
    $sql = "select 
                rh.receipt_num,
                a.po_num,
                b.line,
                b.stockid,b.uom,
                c.item_desc,
                c.item_name,
                a.vendor_code,
                d.vendor_name,
                rl.receipt_line,
                rl.quantity_received,
                (select  sum(transaction_quantity) 
                 from    po_rcv_transactions prt 
                 where   transaction_type='REJECT' 
                 and     prt.receipt_num=rl.receipt_num 
                 and     rl.receipt_line=prt.receipt_line  ) 
                 reject_qty ,
                (select  sum(transaction_quantity) 
                 from    po_rcv_transactions prt  
                 where   transaction_type='ACCEPT' 
                 and     prt.receipt_num=rl.receipt_num 
                 and     rl.receipt_line=prt.receipt_line ) 
                 accept_qty ,
                 ifnull(rl.quantity_received,0)-ifnull(rl.already_inspection_qty,0) qty,
                 rl.subinventory_code,
                 b.need_date,
                 rh.creation_date,rl.wait_inspect_quantity
                 FROM  po_headers_all a,
                       po_lines_all b,
			           po_rcv_receipt_header rh,
			           po_rcv_receipt_line  rl,
                       sf_item_no c,
                       vendors d
                 WHERE   a.po_num=b.po_num    
                 and     a.vendor_code=d.vendor_code
                 and     b.po_num=rl.po_num
                 and     b.line=rl.po_line
                 and     b.stockid=c.item_no 
                 and     rl.receipt_num=rh.receipt_num
				 and rl.wait_inspect_quantity>0 ";
    if(isset($_POST['vendorCode']) and $_POST['vendorCode'] != ''){
        $sql = $sql." and d.vendor_code ".LIKE." '%".$_POST['vendorCode']."%' ";
    }
    if(isset($_POST['vendorName']) and $_POST['vendorName'] != ''){
        $sql = $sql." and d.vendor_name ".LIKE." '%".$_POST['vendorName']."%' ";
    }
	if(isset($_POST['item_no']) and $_POST['item_no'] != ''){
        $sql = $sql." and c.item_no ".LIKE." '%".$_POST['item_no']."%' ";
    }
	if(isset($_POST['item_name']) and $_POST['item_name'] != ''){
        $sql = $sql." and c.item_name ".LIKE." '%".$_POST['item_name']."%' ";
    }
	if(isset($_POST['item_desc']) and $_POST['item_desc'] != ''){
        $sql = $sql." and c.item_desc ".LIKE." '%".$_POST['item_desc']."%' ";
    }
    if(isset($_POST['FromDate']) and $_POST['FromDate'] != ''){
        $sql = $sql." and rh.creation_date >=".strtotime($_POST['FromDate'])." ";
    }
     if(isset($_POST['ToDate']) and $_POST['ToDate'] != ''){
        $sql = $sql." and rh.creation_date <=".strtotime($_POST['ToDate'])." ";
    }
   if(isset($_POST['receipt_num']) and $_POST['receipt_num'] != ''){
        $sql = $sql." and rh.receipt_num ".LIKE." '%".$_POST['receipt_num']."%' ";
    }
	 if(isset($_POST['po_num']) and $_POST['po_num'] != ''){
        $sql = $sql." and rl.po_num ".LIKE." '%".$_POST['po_num']."%' ";
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
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('待检验采购单信息查询') . '</p>';
echo '<table cellpadding="3" class="selection">';
echo '<div class="text-nav">';
 
echo '<div class="text-nav-1"><div>' . _('收料单号') . ':</div>';
echo '<input type="text" name="receipt_num" value="' . $_POST['receipt_num'] . '" size="20" maxlength="25" /></div>';
echo '<div class="text-nav-1"><div>' . _('采购单号') . ':</div>';
echo '<input type="text" name="po_num" value="' . $_POST['po_num'] . '" size="20" maxlength="25" /></div>';
echo '<div class="text-nav-1"><div>' . _('供应商名称') . ':</div>';
echo '<input type="text" name="vendorName" value="' . $_POST['vendorName'] . '" size="20" maxlength="25" /></div>';
echo '<div class="text-nav-1"><div>' . _('供应商简称') . ':</div> ';
echo ' <input type="text" name="vendorCode" value="' . $_POST['vendorCode'] . '" size="15" maxlength="25" /></div>';
echo '<div class="text-nav-1"><div>' . _('料号') . ':</div> ';
echo ' <input type="text" name="item_no" value="' . $_POST['item_no'] . '" size="15" maxlength="25" /></div>';
echo '<div class="text-nav-1"><div>' . _('料号名称') . ':</div> ';
echo ' <input type="text" name="item_name" value="' . $_POST['item_name'] . '" size="15" maxlength="25" /></div>';
echo '<div class="text-nav-1"><div>' . _('规格型号') . ':</div> ';
echo ' <input type="text" name="item_desc" value="' . $_POST['item_desc'] . '" size="15" maxlength="25" /></div>';
if (!isset($_POST['FromDate'])) {
    $_POST['FromDate'] = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 30, date("Y")));
}
if (!isset($_POST['ToDate'])) {
    $_POST['ToDate'] = Date('Y-m-d', mktime(0, 0, 0, date("m"), date("d") + 1, date("Y")));
}
echo '<div class="text-nav-1"><div>' . _('来料报检日期起') . ':</div>';
echo '<input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="FromDate" maxlength="10" size="20" value="' . $_POST['FromDate'] . '"  /></div>';
echo '<div class="text-nav-1"><div>' . _('来料报检日期止') . ':</div>';
echo '<input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="ToDate" maxlength="10" size="20" value="' . $_POST['ToDate'] . '"  /> </div>';
echo '</table><div class="centre"><input type="submit" name="Search" value="查找"> </div>'. '</br>';

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
    echo '<div class="text-nav-table">
                    <table cellpadding="2" class="selection" >';

    echo '<tr>
                    <th bgcolor="#87CEFA" width =100>' . '来料报检单号' . '</th>
										  <th bgcolor="#87CEFA" width =20>' . '行' . '</th>
                                         <th bgcolor="#87CEFA" width =90 >' . '供应商简称' . '</th>
	                                     <th bgcolor="#87CEFA" width =100>' . '采购单号' . '</th>
                                         <th bgcolor="#87CEFA" width =20>' . '行' . '</th>
                                         <th bgcolor="#87CEFA" width =150>' . '料号' . '</th>
                                         <th bgcolor="#87CEFA" width =250>' . '料号名称' . '</th>
                                         <th bgcolor="#87CEFA" width =250>' . '规格型号' . '</th>
                                         <th bgcolor="#87CEFA"  >' . '单位' . '</th> 
                                         <th bgcolor="#87CEFA"  width = 70>' . '收货量' . '</th> 
                                         <th bgcolor="#87CEFA" width =70 >' . '合格量' . '</th>
                                         <th bgcolor="#87CEFA" width =70 >' . '不合格量' . '</th>
                                         <th bgcolor="#87CEFA" width =70 >' . '待检验量' . '</th>
										 <th bgcolor="#87CEFA" width =160 >' . '来料报检日期' . '</th>
                   
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
			 echo '<td>' . $myrow['receipt_num'] . '</td>';
			echo '<td>' . $myrow['receipt_line'] . '</td>';
                echo '<td>' . $myrow['vendor_code'] . '</td>';
                echo '<td><font color="red">' . $myrow['po_num'] . '</font></td>';
            echo '<td>' . $myrow['line'] . ' </td>';
            echo '<td>' . $myrow['stockid'] . ' </td>';
            echo '<td>' . $myrow['item_name'] . ' </td>';
            echo '<td>' . $myrow['item_desc'] . ' </td>';
            echo '<td>' . $myrow['uom'] . ' </td>';
            echo '<td>' . $myrow['quantity_received'] . ' </td>';
            echo '<td>' . $myrow['accept_qty'] . ' </td>';
             echo '<td>' . $myrow['reject_qty'] . ' </td>';
            echo '<td>' . $myrow['wait_inspect_quantity'] . ' </td>';
			echo '<td>' . date('Y-m-d H:i:s',$myrow['creation_date']) . '</td>';

       
			echo '
			</tr>';
			$i++;
			$RowIndex++;
			//end of page full new headings if
		} //end loop through vendors
		echo '</table></div>';
		echo '<input type="hidden" name="JustSelectedAvendor" value="Yes" />';
		echo '<div>
                 <a href="' . $RootPath . '/InPOCheck3Excel.php?vendor_name=' .$_POST['vendorName'] .'&vendor_code=' .$_POST['vendorCode'] .'&FromDate=' .$_POST['FromDate'] . '&ToDate=' .$_POST['ToDate'] .'&item_no=' .$_POST['item_no'] . '&item_name=' .$_POST['item_name']. '&item_desc=' .$_POST['item_desc']  .'&po_num=' .$_POST['po_num'] .'&receipt_num=' .$_POST['receipt_num'] .' ">' .'资料导出成Excel表' . '</a>
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