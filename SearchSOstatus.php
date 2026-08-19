<?php

ob_start();
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include('includes/session.inc');
$Title = _('订单状态查询');
$ViewTopic = '订单状态查询';
$BookMark = '订单状态查询';

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
//    $sql = 'select distinct so.order_number,so.creation_date,so.schedule_ship_date,'
//            . 'so.order_amount,sol.status,so.created_by ,wu.realname,wu.salesman,sol.line_no'
//            . ' from sf_orders_all so,www_users wu,sf_order_lines_all sol where wu.userid=so.created_by'
//            . ' and sol.order_number=so.order_number ';
    $sql="SELECT
	a.order_number,
	a.status,
	b.realname,
	a.order_amount,
	a.description,
	a.schedule_ship_date,
	a.creation_date,a.man_contact
FROM sf_orders_all a,
	www_users b
WHERE
	1 = 1
AND a.created_by = b.userid
and EXISTS(select 1 from sf_order_lines_all c where  a.order_number = c.order_number)
";

    if (isset($_POST['order_number_from']) and $_POST['order_number_from'] != '') {
        $sql = $sql . " and a.order_number >=  '" . $_POST['order_number_from'] . "'";
    }
    if (isset($_POST['order_number_to']) and $_POST['order_number_to'] != '') {
        $sql = $sql . " and a.order_number <=  '" . $_POST['order_number_to'] . "' ";
    }
    if (isset($_POST['realname']) and $_POST['realname'] != '') {
        $sql = $sql . " and b.realname " . LIKE . " '%" . $_POST['realname'] . "%' ";
    }
  
    if (empty($_POST['FromDate']) == 0) {
        $SQL_FromDate = strtotime($_POST['FromDate']);
        $sql .= " and so.schedule_ship_date >= '" . $SQL_FromDate . "' ";
    }
    if (empty($_POST['ToDate']) == 0) {
        $SQL_ToDate = strtotime($_POST['ToDate']) + 86400;
        //echo $SQL_ToDate;
        $sql .= " and so.schedule_ship_date <='" . $SQL_ToDate . "' ";
    }
      if($_POST['checkresult']!=""){
        if($_POST['checkresult']=="待签核"){
            $sql .= " and so.status = 'INPROCESS'";
        }
        if($_POST['checkresult']=="已签核"){
             $sql .=" and so.status = 'APPROVED'";
        }
        if($_POST['checkresult']=="生产已签核"){
             $sql .= " and so.status = 'P_APPROVED'";
        }
		if($_POST['checkresult']=="已拒签"){
             $sql .= " and so.status = 'REJECTED'";
        }
		if($_POST['checkresult']=="已取消"){
             $sql .= " and so.status = 'CANCELED'";
        }
       
    }
    $result = DB_query($sql, $db);
    if (DB_num_rows($result) == 0) {
        unset($result);
        prnMsg(_('找不到该订单，请重新输入条件查询！'), 'error');
    }
}


echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('查询订单状态') . '</p>';
echo '<table cellpadding="3" class="selection">';

echo '<tr><td >' . _('订单起') . ':</td><td>';
echo '<input type="text" name="order_number_from" value="' . $_POST['order_number_from'] . '" size="20" maxlength="25" /></td>';
echo '<td>' . _('订单止') . ':</td>
	<td>';
echo '<input type="text" name="order_number_to" value="' . $_POST['order_number_to'] . '" size="20" maxlength="25" /></td>';
echo '</tr>';
echo '<tr><td >' . _('加盟商名称') . ':</td><td>';
echo '<input type="text" name="realname" value="' . $_POST['realname'] . '" size="20" maxlength="25" /></td>';
//echo '<td>' . _('或者 业务人员') . ':</td>
//	<td>';
//echo '<input type="text" name="man_contact" value="' . $_POST['man_contact'] . '" size="20" maxlength="25" /></td>';
echo '</tr>';
//echo '<tr><td>' . _('订单完成状态') . ':</td><td><select name="checkresult">';
//
//            echo '<option  selected="selected" value=""></option>';
//  
//            echo '<option   value="INPROCESS">待签核</option>';
//            echo '<option   value="APPROVED">待生管签</option>';
//            echo '<option   value="P_APPROVED">签核完成</option>';    
//
//            echo '</select></td></tr>';
if (!isset($_POST['FromDate'])) {
    $_POST['FromDate'] = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 30, date("Y")));
}
if (!isset($_POST['ToDate'])) {
    $_POST['ToDate'] = Date('Y-m-d');
}
echo '<td>' . '订单出货日期' . _('From') . ':</td>
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
                    <th class="ascending" width = 100>' . _('订单号') . '</th>
                    <th class="ascending"width = 120>' . _('状态') . '</th>
                    <th class="ascending"width = 120>' . _('加盟商') . '</th> 
                    <th class="ascending"width = 120>' . _('出货日') . '</th>
                    <th class="ascending"width = 180>' . _('下单日') . '</th>
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
            if ($myrow['status'] == 'INPROCESS') {
                $v_status = '待签核';
            } elseif ($myrow['status'] == 'APPROVED') {
                $v_status = '待生管签核';
            } elseif ($myrow['status'] == 'P_APPROVED') {
                $v_status = '已签核';
            } elseif ($myrow['status'] == 'CANCELED') {
                $v_status = '已取消';
            }elseif ($myrow['status'] == 'REJECTED') {
                $v_status = '已拒签';
            }else {
                $v_status = '异常';
            }
			
            echo '  <td><a href="' . $RootPath . '/SearchSOstatus2.php?Updateorder_number=' . $myrow['order_number'] . '">' . $myrow['order_number'] . '</td>
				
                                 <td>' . $v_status . '</td>
                                <td>' . $myrow['realname'] . '</td>  
                                <td>' . date('Y-m-d H:i:s', $myrow['schedule_ship_date']) . '</td>
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
