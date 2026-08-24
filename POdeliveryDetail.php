<?php

ob_start();
/*
* To change this license header, choose License Headers in Project Properties.
* To change this template file, choose Tools | Templates
* and open the template in the editor.
*/

include ('includes/session.inc');
$Title = _('采购收货明细报表');
$ViewTopic = '采购收货明细报表';
$BookMark = '采购收货明细报表';

include ('includes/header.inc');
include ('includes/SQL_CommonFunctions.inc');

unset($result);

if (isset($_POST['Go1']) or isset($_POST['Go2'])) {
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

if (isset($_POST['Search']) or isset($_POST['Go']) or isset($_POST['Next']) or
    isset($_POST['Previous'])) {
    $sql = 'SELECT  rl.po_num,a.vendor_code, v.vendor_name,a.receive_remark, b.item_no,b.item_desc,b.item_name,b.units,a.receipt_num,rl.receipt_line,a.creation_date,rl.stockid,rl.lot_num,
ifnull(rl.quantity_received,0) this_received,rl.po_line,rl.unit_price,rl.line_amount,rl.wait_inspect_quantity
FROM  vendors v,sf_item_no b,po_rcv_receipt_line rl,po_rcv_receipt_header a
WHERE   b.item_no=rl.stockid
AND	a.receipt_num = rl.receipt_num
AND v.vendor_code = a.vendor_code';
 	
    
	 
    if (isset($_POST['receipt_num']) and $_POST['receipt_num'] != '') { 
		$sql = $sql . " and a.receipt_num " . LIKE . " '%" . $_POST['receipt_num'] ."%' ";
    }
     
    if (isset($_POST['vendor_name']) and $_POST['vendor_name'] != '') {
        $sql = $sql . " and v.vendor_name " . LIKE . " '%" . $_POST['vendor_name'] ."%' ";
    }
    if (isset($_POST['vendor']) and $_POST['vendor'] != '') { 
		$sql = $sql . " and v.vendor_code " . LIKE . " '%" . $_POST['vendor'] ."%' ";
    }
    if (isset($_POST['item_no']) and $_POST['item_no'] != '') { 
		$sql = $sql . " and b.item_no " . LIKE . " '%" . $_POST['item_no'] ."%' ";
    }
	
		 if (isset($_POST['item_name']) and $_POST['item_name'] != '') { 
		$sql = $sql . " and b.item_name " . LIKE . " '%" . $_POST['item_name'] ."%' ";
    }
	 
    if (empty($_POST['FromDate']) == 0) {
        $SQL_FromDate = strtotime($_POST['FromDate']);
        $sql .= " and rl.creation_date >= '" . $SQL_FromDate . "' ";
    }
    if (empty($_POST['ToDate']) == 0) {
        $SQL_ToDate = strtotime($_POST['ToDate']) + 86400;
        //echo $SQL_ToDate;
        $sql .= " and rl.creation_date <='" . $SQL_ToDate . "' ";
    }
	$sql .= " order by  rl.creation_date  desc ";

    $result = DB_query($sql, $db);
    if (DB_num_rows($result) == 0) {
        unset($result);
        prnMsg(_('找不到该采购单，请重新输入条件查询！'), 'error');
    }
}


echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES,
    'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme .
    '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('采购收货明细报表') .
    '</p>';
echo '<table cellpadding="3" class="selection">';
echo '<div class="text-nav">';
 
echo '<div class="text-nav-1"><div>' . _('收料单号') . ':</div>
';
echo '<input type="text" name="receipt_num" value="' . $_POST['receipt_num'] .
    '" size="20" maxlength="25" /></div>';
  
echo '<div class="text-nav-1"><div>' . _('供应商名称') . ':</div>';
echo '<input type="text" name="vendor_name" value="' . $_POST['vendor_name'] .
    '" size="20" maxlength="25" /></div>';
echo '<div class="text-nav-1"><div>' . _('供应商编号') . ':</div>
';
echo '<input type="text" name="vendor" value="' . $_POST['vendor'] .
    '" size="20" maxlength="25" /></div>';

echo '<div class="text-nav-1"><div>' . _('料号') . ':</div>';
echo '<input type="text" name="item_no" value="' . $_POST['item_no'] .
    '" size="20" maxlength="25" /></div>';
echo '<div class="text-nav-1"><div>' . _('料号名称') . ':</div>';
echo '<input type="text" name="item_name" value="' . $_POST['item_name'] .
    '" size="20" maxlength="50" /></div>';

if (!isset($_POST['FromDate'])) {
    $_POST['FromDate'] = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 30,
        date("Y")));
}
if (!isset($_POST['ToDate'])) {
    $_POST['ToDate'] = Date('Y-m-d');
}
echo '<div class="text-nav-1"><div>' . '收货日' . _('起') . ':</div>
		<input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] .
    '" name="FromDate" maxlength="10" size="11" value="' . $_POST['FromDate'] . '" /></div>
    <div class="text-nav-1"><div>' . _('收货日止') . ':</div>
		<input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] .
    '" name="ToDate" maxlength="10" size="11" value="' . $_POST['ToDate'] . '" /></div></div>';

echo '</table><div class="centre"><input type="submit" name="Search" value="查找"> </div>' .
    '</br>';

if (isset($_POST['Search']) and isset($result) or isset($_POST['Go']) or isset($_POST['Next']) or
    isset($_POST['Previous'])) {
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
    echo '<input type="hidden" name="PageOffset" value="' . $_POST['PageOffset'] .
        '" />';
    if ($ListPageMax > 1) {
        echo '<br /><div class="centre">&nbsp;&nbsp;' . _('第') . '' . $_POST['PageOffset'] . ' ' . _('页，共') . ' ' . $ListPageMax . ' ' . _('页') . '. ' . _('转到页') . ': ';
        echo '<select name="PageOffset1">';
            $ListPage = 1;
        while ($ListPage <= $ListPageMax) {
            if ($ListPage == $_POST['PageOffset']) {
                echo '<option value="' . $ListPage . '" selected="selected">' . $ListPage .
                    '</option>';
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


                        <th class="ascending"  >' . _('供应商编号') . '</th> 
						<th class="ascending" width = 90>' . _('收货日期') . '</th>
						<th class="ascending" width = 90>' . _('收料单号') . '</th>
						<th  >' . _('行') . '</th>
					 
                        <th width = 130>' . _('料号') . '</th>
						<th width = 150 >' . _('料号名称') . '</th>
						<th   >' . _('规格型号') . '</th>
						<th class="ascending" width = 40>' . _('单位') . '</th>
                        <th class="ascending"width = 60>' . _('收货量') . '</th>';
						 if ($_SESSION['price_flag']=='N') {
                      echo '  <th class="ascending"width = 60>' . _('单价') . '</th>
                        <th class="ascending"width = 60>' . _('金额') . '</th>';
						 }
                       echo '  <th class="ascending"width = 90>' . _('批号') . '</th>
                       <th class="ascending"width = 90>' . _('备注') . '</th>
                   
                   
            </tr>';
    $k = 0; //row counter to determine background colour
    $RowIndex = 0;


    if (DB_num_rows($result) <> 0) {
        DB_data_seek($result, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);
        $i = 0; //counter for input controls
        while (($myrow = DB_fetch_array($result)) and ($RowIndex <> $_SESSION['DisplayRecordsMax'])) {
            if ($k == 1) {
                echo '<tr class="EvenTableRows">';
                $k = 0;
            } else {
                echo '<tr class="OddTableRows">';
                $k = 1;
            }
            
           echo '  

                     <td>' .$myrow['vendor_code'] . '</td> 
					 <td>' .date('Y-m-d',$myrow['creation_date']) . '</td>
					 <td>' .$myrow['receipt_num'] . '</td>
					 <td>' .$myrow['receipt_line'] . '</td>
					 
					 <td>' .$myrow['item_no'] . '</td>
					 <td>' .$myrow['item_name'] . '</td>
					 <td>' .$myrow['item_desc'] . '</td>
					 <td>' .$myrow['units'] . '</td> 
						 <td>' . $myrow['this_received'] . '</td>';
						 if ($_SESSION['price_flag']=='N') {
						echo ' <td>' . $myrow['unit_price'] . '</td>
						 <td>' . $myrow['line_amount'] . '</td>';
						 }
						echo ' <td>' . $myrow['lot_num'] . '</td>
                        <td>' . $myrow['receive_remark'] . '</td>';
                        if($myrow['wait_inspect_quantity'] > 0){

                            echo '   
                               <td><a href="' . $RootPath . '/printSearchPOReceipt.php?Updatedelivery_num='.$myrow['receipt_num'] .'" target="_blank"  >' . _('打印') . '</a></td>
                            '; 
                        }



            echo '
			</tr>';
            $i++;
            $RowIndex++;
            //end of page full new headings if
        } //end loop through vendors
        echo '</table></div>';
        echo '<input type="hidden" name="JustSelectedAvendor" value="Yes" />';
        echo '<div>
        <a href="' . $RootPath . '/POdeliveryDetailExcel.php?receipt_num=' .$_POST['receipt_num'] .
        '&vendor_name=' .$_POST['vendor_name'] .'&vendor=' .$_POST['vendor'] . 
        '&item_no=' .$_POST['item_no'] .'&item_name=' .$_POST['item_name'] .
         '&item_name=' .$_POST['item_name'] .'&FromDate=' .$_POST['FromDate'] . '&ToDate=' .$_POST['ToDate'] .' ">' .'资料导出成Excel表' . '</a>
       </div>';
    }

    if (isset($ListPageMax) and $ListPageMax > 1) {       
        echo '<br /><div class="centre">&nbsp;&nbsp;' . _('第') . '' . $_POST['PageOffset'] . ' ' . _('页，共') . ' ' . $ListPageMax . ' ' . _('页数') . '. ' . _('转到页') . ': ';
        echo '<select name="PageOffset2">';
        $ListPage = 1;
        while ($ListPage <= $ListPageMax) {
            if ($ListPage == $_POST['PageOffset']) {
                echo '<option value="' . $ListPage . '" selected="selected">' . $ListPage .
                    '</option>';
            } else {
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

include ('includes/footer.inc');
