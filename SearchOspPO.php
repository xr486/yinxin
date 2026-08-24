<?php

ob_start();
/*
* To change this license header, choose License Headers in Project Properties.
* To change this template file, choose Tools | Templates
* and open the template in the editor.
*/

include ('includes/session.inc');
$Title = _('查找外协采购单');
$ViewTopic = '查找外协采购单';
$BookMark = '查找外协采购单';

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
    $sql = 'SELECT DISTINCT wha.waixie_num, 
                            wha.status, 
                            wha.creation_date,
                            wha.need_date,
                            wha.po_all_amount,wha.youhui_amount,
                            wha.po_payment_amount,wha.po_invoice_amount,
                            v.vendor_name, 
                            wha.need_date, 
                            wha.created_by
            FROM waixie_headers_all wha, 
                            waixie_lines_all pla, 
                            vendors v
            WHERE pla.waixie_num = wha.waixie_num
            AND v.vendor_code = wha.vendor_code';

    if (isset($_POST['waixie_num_from']) and $_POST['waixie_num_from'] != '') { 
        $sql = $sql . " and wha.waixie_num ".LIKE." '%".$_POST['waixie_num_from']."%' ";
    }
     
    if (isset($_POST['vendor_name']) and $_POST['vendor_name'] != '') {
        $sql = $sql . " and v.vendor_name ".LIKE." '%".$_POST['vendor_name']."%' ";
            
    }
    if (isset($_POST['vendor']) and $_POST['vendor'] != '') { 
		$sql = $sql . " and v.vendor_code ".LIKE." '%".$_POST['vendor']."%' ";
           
    }
    if (isset($_POST['order_number']) and $_POST['order_number'] != '') {
        $sql = $sql . " and pla.order_number ".LIKE." '%".$_POST['order_number']."%' ";
    }
    if (empty($_POST['FromDate']) == 0) {
        $SQL_FromDate = strtotime($_POST['FromDate']);
        $sql .= " and wha.creation_date >= '" . $SQL_FromDate . "' ";
    }
    if (empty($_POST['ToDate']) == 0) {
        $SQL_ToDate = strtotime($_POST['ToDate']) + 86400;
        //echo $SQL_ToDate;
        $sql .= " and wha.creation_date <='" . $SQL_ToDate . "' ";
    }
    if ($_POST['checkresult'] != "") {
        if ($_POST['checkresult'] == "APPROVED") {
            $sql .= " and wha.status = 'APPROVED'";
        }
        if ($_POST['checkresult'] == "INPROCESS") {
            $sql .= " and wha.status = 'INPROCESS'";
        }
        if ($_POST['checkresult'] == "Cancel") {
            $sql .= " and wha.status = 'Cancel'";
        }
        if ($_POST['checkresult'] == "REJECTED") {
            $sql .= " and wha.status = 'REJECTED'";
        }
    }
    $sql .= " order by wha.waixie_num desc";
    $result = DB_query($sql, $db);
    if (DB_num_rows($result) == 0) {
        unset($result);
        prnMsg(_('找不到该外协采购单，请重新输入条件查询！'), 'error');
    }
}


echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES,
    'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme .
    '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('查找外协采购单') .
    '</p>';
echo '<table cellpadding="3" class="selection">';

echo '<tr><td >' . _('外协采购单号') . ':</td><td>';
echo '<input type="text" name="waixie_num_from" value="' . $_POST['waixie_num_from'] .
    '" size="20" maxlength="25" /></td>';
 
 
echo ' <td >' . _('供应商名称') . ':</td><td>';
echo '<input type="text" name="vendor_name" value="' . $_POST['vendor_name'] .
    '" size="20" maxlength="25" /></td>';
echo '<td>' . _(' 供应商代号') . ':</td>
	<td>';
echo '<input type="text" name="vendor" value="' . $_POST['vendor'] .
    '" size="20" maxlength="25" /></td>';
echo '</tr>';
echo '<tr><td >' . _('订单号') . ':</td><td>';
echo '<input type="text" name="order_number" value="' . $_POST['order_number'] .
    '" size="20" maxlength="25" /></td>';
 
echo ' <td>' . _('外协采购单状态') . ':</td><td><select name="checkresult">';
        echo '<option  selected="selected" value=""></option>';
        echo '<option   value="INPROCESS">待签核</option>';
        echo '<option   value="APPROVED">已签核</option>';
        echo '<option   value="Cancel">已取消</option>';
        echo '<option   value="REJECTED">已拒签</option>';
echo '</select></td> ';

if (!isset($_POST['FromDate'])) {
    $_POST['FromDate'] = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 30, date("Y")));
}
if (!isset($_POST['ToDate'])) {
    $_POST['ToDate'] = Date('Y-m-d');
}
echo '<td>' . '创建日期' . _('From') . ':</td>
		<td><input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] .
    '" name="FromDate" maxlength="10" size="11" value="' . $_POST['FromDate'] . '" /></td>
		<td>' . _('To') . ':</td>
		<td><input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] .
    '" name="ToDate" maxlength="10" size="11" value="' . $_POST['ToDate'] . '" /></td>
	</tr>';

echo '</table><div class="centre"><input type="submit" name="Search" value="查找"> </div>' .
    '</br>';

if (isset($_POST['Search']) and isset($result) or isset($_POST['Go']) or isset($_POST['Next']) or
    isset($_POST['Previous'])) {
    $ListCount = DB_num_rows($result);
    $ListPageMax = ceil($ListCount / 50);

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
                    <th class="ascending" width = 150>' . _('外协采购单号') . '</th>
                    <th class="ascending"width = 70>' . _('状态') . '</th>
                    <th class="ascending"width = 180>' . _('供应商') . '</th>
                    <th  width = 100>' . _('订单总金额') . '</th>   
                    <th  width = 100>' . _('优惠金额') . '</th>    
                    <th  width = 100>' . _('订单应付金额') . '</th>
                    <th  width = 120>' . _('订单应开票金额') . '</th> 
                    <th class="ascending"width = 100>' . _('需求日') . '</th>
                    <th class="ascending"width = 100>' . _('下单日期') . '</th>
                     <th class="ascending"width = 80>' . _('下单人员') . '</th>
            </tr>';
    $k = 0; //row counter to determine background colour
    $RowIndex = 0;


    if (DB_num_rows($result) <> 0) {
        DB_data_seek($result, ($_POST['PageOffset'] - 1) * 50);
        $i = 0; //counter for input controls
        while (($myrow = DB_fetch_array($result)) and ($RowIndex <> 50)) {
            if ($k == 1) {
                echo '<tr class="EvenTableRows">';
                $k = 0;
            } else {
                echo '<tr class="OddTableRows">';
                $k = 1;
            }
            unset($v_status);
            if ($myrow['status'] == 'INPROCESS') {
                $v_status = '待签核';
            } elseif ($myrow['status'] == 'APPROVED') {
                $v_status = '已签核';
            } elseif ($myrow['status'] == 'REJECTED') {
                $v_status = '已拒签';
            } else {
                $v_status = '已取消';
            }

			 if ($myrow['status'] == 'APPROVED') { 
            echo '  <td><a href="' . $RootPath . '/SearchOspPO2.php?Updatewaixie_num=' . $myrow['waixie_num'] .'" target="_blank" >' . $myrow['waixie_num'] . '</td>
				<td>' . $v_status . '</td>
                <td>' . $myrow['vendor_name'] . '</td> 
                <td>' . $myrow['po_all_amount'] . '</td>
                <td>' . $myrow['youhui_amount'] . '</td>
                <td>' . $myrow['po_payment_amount'] . '</td>
                <td>' . $myrow['po_invoice_amount'] . '</td>
                <td>' . date('Y-m-d', $myrow['need_date']) .'</td>                               
				<td>' . date('Y-m-d', $myrow['creation_date']) . '</td>
				<td>' . $myrow['created_by'] . '</td>
				 ';
			 }
			 else { 
				 echo '  <td><a href="' . $RootPath . '/SearchOspPO2.php?Updatewaixie_num=' . $myrow['waixie_num'] .'" target="_blank" >' . $myrow['waixie_num'] . '</td>
				<td>' . $v_status . '</td>
                <td>' . $myrow['vendor_name'] . '</td> 
                <td>' . $myrow['po_all_amount'] . '</td>
                <td>' . $myrow['youhui_amount'] . '</td>
                <td>' . $myrow['po_payment_amount'] . '</td>
                <td>' . $myrow['po_invoice_amount'] . '</td>
                <td>' . date('Y-m-d', $myrow['need_date']) .'</td>                               
				<td>' . date('Y-m-d', $myrow['creation_date']) . '</td>
				<td>' . $myrow['created_by'] . '</td> 
				 ';
				 }


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
