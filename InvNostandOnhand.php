<?php

ob_start();
/*
* To change this license header, choose License Headers in Project Properties.
* To change this template file, choose Tools | Templates
* and open the template in the editor.
*/

include ('includes/session.inc');
$Title = _('非标准材料库存明细报表');
$ViewTopic = '非标准材料库存明细报表';
$BookMark = '非标准材料库存明细报表';

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
    $sql = 'SELECT   v.vendor_code,c.subinventory_code , v.vendor_name, pha.po_num, c.po_line, a.order_number, b.customer_code, a.item_no, a.item_name, a.item_description, c.uom, sum(c.onhand_quantity) onhand_quantity
FROM po_headers_all pha,  
so_line_details_all a, 
so_headers_all b, 
inv_nonstand_onhand_quantity c, 
vendors v
WHERE  v.vendor_code = pha.vendor_code
AND a.so_line_detail_id = c.so_line_detail_id
AND b.order_number = a.order_number
AND c.po_number = pha.po_num
';

    if (isset($_POST['po_num_from']) and $_POST['po_num_from'] != '') {
        $sql = $sql . " and pha.po_num >=  '" . $_POST['po_num_from'] . "'";
    }
    if (isset($_POST['po_num_to']) and $_POST['po_num_to'] != '') {
        $sql = $sql . " and pha.po_num <=  '" . $_POST['po_num_to'] . "' ";
    }
    if (isset($_POST['vendor_name']) and $_POST['vendor_name'] != '') {
        $sql = $sql . " and v.vendor_name " . LIKE . " '%" . $_POST['vendor_name'] .
            "%' ";
    }
		 if (isset($_POST['customer_code']) and $_POST['customer_code'] != '') {
        $sql = $sql . " and b.customer_code " . LIKE . " '%" . $_POST['customer_code'] .
            "%' ";
    }

	 if (isset($_POST['vendor']) and $_POST['vendor'] != '') {
        $sql = $sql . " and pha.vendor " . LIKE . " '%" . $_POST['vendor'] .
            "%' ";
    }
	 if (isset($_POST['order_number']) and $_POST['order_number'] != '') {
        $sql = $sql . " and b.order_number " . LIKE . " '%" . $_POST['order_number'] .
            "%' ";
    }

    
    if (isset($_POST['stockid_from']) and $_POST['stockid_from'] != '') {
        $sql = $sql . " and a.item_no =  '" . $_POST['stockid_from'] . "'";
    }
	if (isset($_POST['stockid_to']) and $_POST['stockid_to'] != '') {
        $sql = $sql . " and a.item_no =  '" . $_POST['stockid_to'] . "'";
    }
    if (empty($_POST['FromDate']) == 0) {
        $SQL_FromDate = strtotime($_POST['FromDate']);
        $sql .= " and pha.creation_date >= '" . $SQL_FromDate . "' ";
    }
    if (empty($_POST['ToDate']) == 0) {
        $SQL_ToDate = strtotime($_POST['ToDate']) + 86400;
        //echo $SQL_ToDate;
        $sql .= " and pha.creation_date <='" . $SQL_ToDate . "' ";
    }
	$sql .= " GROUP BY v.vendor_code,c.subinventory_code , v.vendor_name, pha.po_num, c.po_line, a.order_number, b.customer_code, a.item_no, a.item_name, a.item_description, c.uom   ";

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
    '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('非标准材料库存明细报表') .
    '</p>';
echo '<table cellpadding="3" class="selection">';
echo '<tr><td>' . _('客户代号') . ':</td>
	<td>';
echo '<input type="text" name="customer_code" value="' . $_POST['customer_code'] .
    '" size="20" maxlength="25" /></td>';
echo '<td >' . _('采购单起') . ':</td><td>';
echo '<input type="text" name="po_num_from" value="' . $_POST['po_num_from'] .
    '" size="20" maxlength="25" /></td>';
echo '<td>' . _('采购单止') . ':</td>
	<td>';
echo '<input type="text" name="po_num_to" value="' . $_POST['po_num_to'] .
    '" size="20" maxlength="25" /></td>';
 
echo ' <td >' . _('供应商名称') . ':</td><td>';
echo '<input type="text" name="vendor_name" value="' . $_POST['vendor_name'] .
    '" size="20" maxlength="25" /></td>';
echo '<td>' . _('供应商代号') . ':</td>
	<td>';
echo '<input type="text" name="vendor" value="' . $_POST['vendor'] .
    '" size="20" maxlength="25" /></td>';
	
echo '</tr>';

echo '<tr><td>' . _('客户订单') . ':</td>
	<td>';
echo '<input type="text" name="order_number" value="' . $_POST['order_number'] .
    '" size="20" maxlength="25" /></td>';

echo '<td >' . _('料号起') . ':</td><td>';
echo '<input type="text" name="stockid_from" value="' . $_POST['stockid_from'] .
    '" size="20" maxlength="25" /></td>';
echo '<td >' . _('料号止') . ':</td><td>';
echo '<input type="text" name="stockid_to" value="' . $_POST['stockid_to'] .
    '" size="20" maxlength="25" /></td>';

 
echo '<td>' . '采购日期' . _('From') . ':</td>
		<td><input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] .
    '" name="FromDate" maxlength="10" size="11" value="' . $_POST['FromDate'] . '" /></td>
		<td>' . _('To') . ':</td>
		<td><input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] .
    '" name="ToDate" maxlength="10" size="11" value="' . $_POST['ToDate'] . '" /></td></tr>';

echo '</table><div class="centre"><input type="submit" name="Search" value="查找"> </div>' .
    '</br>';

if (isset($_POST['Search']) and isset($result) or isset($_POST['Go']) or isset($_POST['Next']) or
    isset($_POST['Previous'])) {
    $ListCount = DB_num_rows($result);
    $ListPageMax = ceil($ListCount / 40);

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
        echo '<br /><div class="centre">&nbsp;&nbsp;' . _('第') . '' . $_POST['PageOffset'] .
            ' ' . _('页，共') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') .
            ': ';
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
                    <input type="submit" name="Go1" value="' . _('Go') . '" />
                    <input type="submit" name="Previous" value="' . _('Previous') .
            '" />
                    <input type="submit" name="Next" value="' . _('Next') .
            '" />';
        echo '</div>';
    }
    echo '
                    <table cellpadding="2" class="selection" >';

    echo '<tr>      

                        <th class="ascending"  >' . _('供应商代码') . '</th> 
						<th class="ascending" width = 90>' . _('客户') . '</th>
						<th class="ascending" width = 90>' . _('客户订单') . '</th>
						<th class="ascending" width = 90>' . _('采购单号') . '</th>
						<th class="ascending" width = 30>' . _('行') . '</th>
						<th >' . _('仓库') . '</th>
                        <th >' . _('料号') . '</th>
						<th  >' . _('料号名称') . '</th>
						<th  >' . _('规格型号') . '</th>
						<th class="ascending" width = 40>' . _('单位') . '</th>
                        <th class="ascending"width = 60>' . _('数量') . '</th> 
                   
                   
            </tr>';
    $k = 0; //row counter to determine background colour
    $RowIndex = 0;


    if (DB_num_rows($result) <> 0) {
        DB_data_seek($result, ($_POST['PageOffset'] - 1) * 40);
        $i = 0; //counter for input controls
        while (($myrow = DB_fetch_array($result)) and ($RowIndex <> 40)) {
            if ($k == 1) {
                echo '<tr class="EvenTableRows">';
                $k = 0;
            } else {
                echo '<tr class="OddTableRows">';
                $k = 1;
            }
            
           echo '  

                     <td>' .$myrow['vendor_code'] . '</td>  
					 <td>' .$myrow['customer_code'] . '</td>
					 <td>' .$myrow['order_number'] . '</td>
					 <td>' .$myrow['po_num'] . '</td>
					 <td>' .$myrow['po_line'] . '</td>
					 <td>' .$myrow['subinventory_code'] . '</td>
					 <td>' .$myrow['item_no'] . '</td>
					 <td>' .$myrow['item_name'] . '</td>
					 <td>' .$myrow['item_description'] . '</td> 
					 <td>' .$myrow['uom'] . '</td> 
					 <td>' .$myrow['onhand_quantity'] . '</td> 
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

    if (isset($ListPageMax) and $ListPageMax > 1) {
        echo '<br /><div class="centre">&nbsp;&nbsp;' . _('第') . '' . $_POST['PageOffset'] .
            ' ' . _('页，共') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') .
            ': ';
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
                        <input type="submit" name="Go2" value="' . _('Go') .
            '" />
                        <input type="submit" name="Previous" value="' . _('Previous') .
            '" />
                        <input type="submit" name="Next" value="' . _('Next') .
            '" />';
        echo '</div>';
    }
}
echo '</div></form>';

include ('includes/footer.inc');
