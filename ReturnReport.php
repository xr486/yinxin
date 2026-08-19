<?php

ob_start();
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include('includes/session.inc');
$Title = _('查询退货单');
$ViewTopic = '查询退货单';
$BookMark = '查询退货单';

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

if (isset($_POST['Search']) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])) {
    $sql = "SELECT
	a.receipt_num,
	a.status,
	b.vendor_code,
	b.vendor_name,
	a.need_payment_amount  ,
	a.receive_remark,
	a.delivery_date, 
	a.creation_date,
    a.Approved_by,
    a.Approve_date,
    a.approve_remark
FROM po_rcv_receipt_all a,
	vendors b 
WHERE
	1 = 1
	and substr(receipt_num,1,2)='RT'
AND a.vendor_code = b.vendor_code  
 ";
// echo $sql;
    if (isset($_POST['SO_from']) and $_POST['SO_from'] != '') {
        $sql = $sql . " and a.receipt_num >=  '" . $_POST['SO_from'] . "'";
    }
    if (isset($_POST['SO_to']) and $_POST['SO_to'] != '') {
        $sql = $sql . " and a.receipt_num <=  '" . $_POST['SO_to'] . "' ";
    }
    if (isset($_POST['vendor_name']) and $_POST['vendor_name'] != '') {
        $sql = $sql . " and b.vendor_name " . LIKE . " '%" . $_POST['vendor_name'] . "%' ";
    }
   
    if (empty($_POST['FromDate']) == 0) {
        $SQL_FromDate = strtotime($_POST['FromDate']);
        $sql .= " and a.delivery_date >= '" . $SQL_FromDate . "' ";
    }
    if (empty($_POST['ToDate']) == 0) {
        $SQL_ToDate = strtotime($_POST['ToDate']) + 86400;
        //echo $SQL_ToDate;
        $sql .= " and a.delivery_date <='" . $SQL_ToDate . "' ";
    }
      if($_POST['checkresult']!=""){
        if($_POST['checkresult']=="APPROVED"){
            $sql .= " and a.status = 'APPROVED'";
        }
        if($_POST['checkresult']=="INPROCESS"){
             $sql .=" and a.status = 'INPROCESS'";
        }
        if($_POST['checkresult']=="Cancel"){
             $sql .= " and a.status = 'Cancel'";
        }
        if($_POST['checkresult']=="REJECTED"){
             $sql .=" and a.status = 'REJECTED'";
        }
		if($_POST['checkresult']=="closed"){
             $sql .=" and a.status = 'closed'";
        }
    }
    // echo $sql;
    $result = DB_query($sql, $db);
    if (DB_num_rows($result) == 0) {
        unset($result);
        prnMsg(_('找不到退货单，请重新输入条件查询！'), 'error');
    }
}


echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('查询退货单') . '</p>';
echo '<table cellpadding="3" class="selection">';

echo '<tr><td >' . _('退货单起') . ':</td><td>';
echo '<input type="text" name="SO_from" value="' . $_POST['SO_from'] . '" size="20" maxlength="25" /></td>';
echo '<td>' . _('退货单止') . ':</td>
	<td>';
echo '<input type="text" name="SO_to" value="' . $_POST['SO_to'] . '" size="20" maxlength="25" /></td>';
echo '<td >' . _('供应商名称') . ':</td><td>';
echo '<input type="text" name="vendor_name" value="' . $_POST['vendor_name'] . '" size="20" maxlength="25" /></td>';
 

echo '<tr><td>' . _('订单状态') . ':</td><td><select name="checkresult">';

            echo '<option  selected="selected" value=""></option>';
  
            echo '<option   value="INPROCESS">待签核</option>';

            echo '<option   value="APPROVED">已签核</option>';
            echo '<option   value="CANCELED">已取消</option>';
            echo '<option   value="REJECTED">已拒签</option>';
			echo '<option   value="closed">关闭</option>';
            echo '</select></td> ';
if (!isset($_POST['FromDate'])) {
    $_POST['FromDate'] = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 30, date("Y")));
}
if (!isset($_POST['ToDate'])) {
    $_POST['ToDate'] = Date('Y-m-d');
}
echo '<td>' . '退货日期' . _('From') . ':</td>
		<td><input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="FromDate" maxlength="10" size="11" value="" /></td>
		<td>' . _('To') . ':</td>
		<td><input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="ToDate" maxlength="10" size="11" value="" /></td>
	</tr>';

echo '</table><div class="centre"><input type="submit" name="Search" value="查找"> </div>'
 . '</br>';

if (isset($_POST['Search']) and isset($result) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])) {
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
                    <th class="ascending" width = 100>' . _('退货单号') . '</th>
                    <th class="ascending"width = 70>' . _('状态') . '</th>
                    <th class="ascending"width = 100>' . _('供应商编号') . '</th>
					 <th class="ascending"width = 250>' . _('供应商名称') . '</th>
                    <th class="ascending"width = 120>' . _('总额') . '</th>                           
                    <th class="ascending"width = 200>' . _('备注') . '</th>
                    <th class="ascending"width = 100>' . _('退货日期') . '</th>
                    <th class="ascending"width = 180>' . _('建立日期') . '</th>
            </tr>';
    $k = 0; //row counter to determine background colour
    $RowIndex = 0;



    if (DB_num_rows($result) <> 0) {
        DB_data_seek($result, ($_POST['PageOffset'] - 1) * 10);
        $i = 0; //counter for input controls
        while (($myrow = DB_fetch_array($result)) AND ( $RowIndex <> 10 )) {
            if ($k == 1) {
                echo '<tr class="EvenTableRows">';
                $k = 0;
            } else {
                echo '<tr class="OddTableRows">';
                $k = 1;
            }
            unset($v_status);
            // p($myrow);
            if ($myrow['status'] == 'INPROCESS') {
                $v_status = '待签核';
            } elseif ($myrow['status'] == 'APPROVED') {
                $v_status = '已签核';
            } elseif ($myrow['status'] == 'REJECTED') {
                $v_status = '已拒签';
            }   elseif ($myrow['status'] == 'closed') {
                $v_status = '关闭';
            } else {
                $v_status = '已取消';
            }

            echo '  <td><a href="' . $RootPath . '/ReturnReport2.php?Updatereceipt_num=' . $myrow['receipt_num'] . '">' . $myrow['receipt_num'] . '</td>
				<td>' . $v_status . '</td>
                    <td>' . $myrow['vendor_code'] . '</td> 
					<td>' . $myrow['vendor_name'] . '</td> 
                  <td>' . $myrow['need_payment_amount'] . '</td>
				<td>' . $myrow['receive_remark'] . '</td>
                   <td>' . date('Y-m-d', $myrow['delivery_date']) . '</td>
				<td>' . date('Y-m-d H:i:s', $myrow['creation_date']) . '</td>
                                 ';

$totalamount = $myrow['need_payment_amount']+$totalamount;
            echo '
			</tr>';
            $i++;
            $RowIndex++;
            //end of page full new headings if
        } //end loop through vendors
          echo  ' <tr>
            <td>'.合计总价：.'</td><td></td><td></td><td></td>
             <td><span style="color:red; font-size:20px;">'.$totalamount.'</span></td>
            </tr>';
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
            } else {
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

include('includes/footer.inc');
