<?php

ob_start();
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include('includes/session.inc');
$Title = _('查询订单');
$ViewTopic = '查询订单';
$BookMark = '查询订单';

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

unset($result);

    $sql = "SELECT
    a.order_number,
    a.status,
    b.customer_code,
    a.order_all_amount,
    a.header_remark,
    a.need_date,
    a.creation_date,approve_date,contract_order_number	
FROM
    so_headers_all a,
    customers b

WHERE
    1 = 1
AND a.customer_code = b.customer_code 
";
 $sql .=" order by a.order_number DESC";

$result = DB_query($sql, $db);
    if (DB_num_rows($result) == 0) {
        unset($result);
        prnMsg(_('找不到该订单，请重新输入条件查询！'), 'error');
  }


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
    
    
    $_POST['To1Date'] = Date('Y-m-d');             
	    $SQL_From1Date = strtotime( $_POST['To1Date']);
	    $SQL_To1Date = strtotime( $_POST['To1Date']) + 86400; 
    
    $sql = "SELECT
	a.order_number,
	a.status,
	b.customer_code,
	a.order_all_amount,
	a.header_remark,
	a.need_date,
	a.creation_date,approve_date,contract_order_number	
FROM
	so_headers_all a,
	customers b

WHERE
	1 = 1
AND a.customer_code = b.customer_code 
and a.fin_approved_date >='" . $SQL_From1Date . "'
		   and   a.fin_approved_date <='" . $SQL_To1Date . "'
";

    if (isset($_POST['SO_from']) and $_POST['SO_from'] != '') {
        $sql = $sql . " and a.order_number >=  '" . $_POST['SO_from'] . "'";
    }
    if (isset($_POST['SO_to']) and $_POST['SO_to'] != '') {
        $sql = $sql . " and a.order_number <=  '" . $_POST['SO_to'] . "' ";
    }
    if (isset($_POST['customer_name']) and $_POST['customer_name'] != '') {
        $sql = $sql . " and b.customer_name " . LIKE . " '%" . $_POST['customer_name'] . "%' ";
    }
	if (isset($_POST['customer_code']) and $_POST['customer_code'] != '') {
        $sql = $sql . " and b.customer_code " . LIKE . " '%" . $_POST['customer_code'] . "%' ";
    }
  
    if (empty($_POST['FromDate']) == 0) {
        $SQL_FromDate = strtotime($_POST['FromDate']);
        $sql .= " and a.need_date >= '" . $SQL_FromDate . "' ";
    }
    if (empty($_POST['ToDate']) == 0) {
        $SQL_ToDate = strtotime($_POST['ToDate']) + 86400;
        //echo $SQL_ToDate;
        $sql .= " and a.need_date <='" . $SQL_ToDate . "' ";
    }
      if($_POST['checkresult']!=""){
        if($_POST['checkresult']=="APPROVED"){
            $sql .= " and a.status = 'APPROVED'";
        }
        if($_POST['checkresult']=="INPROCESS"){
             $sql .=" and a.status = 'INPROCESS'";
        }
        if($_POST['checkresult']=="CANCELED"){
             $sql .= " and a.status = 'CANCELED'";
        }
        if($_POST['checkresult']=="REJECTED"){
             $sql .=" and a.status = 'REJECTED'";
        }
    }
      $sql .=" order by a.order_number DESC";
    $result = DB_query($sql, $db);
    if (DB_num_rows($result) == 0) {
        unset($result);
        prnMsg(_('找不到该订单，请重新输入条件查询！'), 'error');
    }
}


echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('查找订单') . '</p>';
echo '<table cellpadding="3" class="selection">';

echo '<tr><td >' . _('订单起') . ':</td><td>';
echo '<input type="text" name="SO_from" value="' . $_POST['SO_from'] . '" size="20" maxlength="25" /></td>';
echo '<td>' . _('订单止') . ':</td>
	<td>';
echo '<input type="text" name="SO_to" value="' . $_POST['SO_to'] . '" size="20" maxlength="25" /></td>';
echo '</tr>';
echo '<tr><td >' . _('客户代号') . ':</td><td>';
echo '<input type="text" name="customer_code" value="' . $_POST['customer_code'] . '" size="10" maxlength="25" /></td>';
echo '<td >' . _('客户名称') . ':</td><td>';
echo '<input type="text" name="customer_name" value="' . $_POST['customer_name'] . '" size="20" maxlength="25" /></td>';
echo '</tr>';

echo '<tr><td>' . _('订单状态') . ':</td><td><select name="checkresult">';

            echo '<option  selected="selected" value=""></option>';
  
            echo '<option   value="INPROCESS">待签核</option>';

            echo '<option   value="APPROVED">已签核</option>';
            echo '<option   value="CANCELED">已取消</option>';
            echo '<option   value="REJECTED">已拒签</option>';
            echo '</select></td>';
if (!isset($_POST['FromDate'])) {
    $_POST['FromDate'] = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 30, date("Y")));
}
if (!isset($_POST['ToDate'])) {
    $_POST['ToDate'] = Date('Y-m-d');
}
echo '<td>' . '需求日' . _('From') . ':</td>
		<td><input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="FromDate" maxlength="10" size="11" value="" /></td>
		<td>' . _('To') . ':</td>
		<td><input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="ToDate" maxlength="10" size="11" value="" /></td>
	</tr>';

echo '</table><div class="centre"><input type="submit" name="Search" value="查找"> </div>'
 . '</br>';

if (  isset($result) OR (isset($_POST['Search']) OR  isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous']) ) ) {
    $ListCount = DB_num_rows($result);
    $ListPageMax = ceil($ListCount / 15);

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
                    <th class="ascending"  >' . _('订单号') . '</th>
                    <th class="ascending"width = 90>' . _('状态') . '</th>
                    <th class="ascending" >' . _('客户代号') . '</th>
                    <th class="ascending"width = 120>' . _('总额') . '</th>  
                    <th class="ascending"width = 120>' . _('合同号码') . '</th>                           
                  
                    <th class="ascending"width = 120>' . _('备注') . '</th> 
                    <th class="ascending"width = 100>' . _('需求日期') . '</th> 
                    <th class="ascending"width = 100>' . _('核准日期') . '</th>
                    <th class="ascending"width = 180>' . _('下单日期') . '</th>
            </tr>';
    $k = 0; //row counter to determine background colour
    $RowIndex = 0;



    if (DB_num_rows($result) <> 0) {
        DB_data_seek($result, ($_POST['PageOffset'] - 1) * 15);
        $i = 0; //counter for input controls
        while (($myrow = DB_fetch_array($result)) AND ( $RowIndex <> 15 )) {
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
            }   else {
                $v_status = '已取消';
            }
			
		//	<td><a href="' . $RootPath . '/PrintSo.php?OrderNum=' .$myrow['order_number'] . '">' . '打印' .'<img src="%s/css/' . $Theme . '/images/pdf.png" title="' . _('Click for PDF') . '" alt="" /></a></td> 
            echo '  <td><a href="' . $RootPath . '/SearchSO2.php?Updateorder_number=' . $myrow['order_number'] . '"target="_blank">' . $myrow['order_number'] . '</td>
			
				<td>' . $v_status . '</td>
                                <td>' . $myrow['customer_code'] . '</td> 
                                <td>' . $myrow['order_all_amount'] . '</td> 
				
                                <td>' . $myrow['contract_order_number'] . '</td>
                                <td>' . $myrow['remark'] . '</td>
                                <td>' . date('Y-m-d', $myrow['need_date']) . '</td>
								
                                <td>' . date('Y-m-d', $myrow['approve_date']) . '</td>
				<td>' . date('Y-m-d H:i:s', $myrow['creation_date']) . '</td>
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
