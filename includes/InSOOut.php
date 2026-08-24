<?php

ob_start();
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include('includes/session.inc');
$Title = _('订单出货查询');
$ViewTopic = '订单出货查询';
$BookMark = '订单出货查询';

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
    $sql = "SELECT distinct order_number,
order_man,
man_contact,
man_address,
schedule_ship_date,
order_amount
FROM sf_orders_all 
WHERE out_flag='1'";
    if (isset($_POST['CustomeName']) and $_POST['CustomeName'] != '') {
        $sql = $sql . " and order_man " . LIKE . " '%" . $_POST['CustomeName'] . "%' ";
    }
    if (isset($_POST['SO_num']) and $_POST['SO_num'] != '') {
        $sql = $sql . " and order_number " . LIKE . " '%" . $_POST['SO_num'] . "%' ";
    }
    if (isset($_POST['FromDate']) and $_POST['FromDate'] != '') {
        $sql = $sql . " and creation_date >=" . strtotime($_POST['FromDate']) . " ";
    }
    if (isset($_POST['ToDate']) and $_POST['ToDate'] != '') {
        $sql = $sql . " and creation_date <=" . strtotime($_POST['ToDate']) . " ";
    }
    if (isset($_POST['FromDate1']) and $_POST['FromDate1'] != '') {
        $sql = $sql . " and schedule_ship_date >=" . strtotime($_POST['FromDate1']) . " ";
    }
    if (isset($_POST['ToDate']) and $_POST['ToDate'] != '') {
        $sql = $sql . " and schedule_ship_date <=" . strtotime($_POST['ToDate1']) . " ";
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
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('查找出货订单') . '</p>';
echo '<table cellpadding="3" class="selection">';

echo '<tr><td colspan="2">' . _('客户名称') . ':</td><td>';
echo '<input type="text" name="CustomeName" value="' . $_POST['CustomeName'] . '" size="20" maxlength="25" /></td>';
echo '<td><b>' . _('OR') . '</b></td><td>' . _('订单号') . ':</td>
	<td>';
echo '<input type="text" name="SO_num" value="' . $_POST['SO_num'] . '" size="20" maxlength="25" /></td>';
echo '</tr>';
//echo '</tr>';
if (!isset($_POST['FromDate'])) {
    $_POST['FromDate'] = date("Y-m-d H:i:s", mktime(0, 0, 0, date("m"), date("d") - 30, date("Y")));
}
if (!isset($_POST['ToDate'])) {
    $_POST['ToDate'] = Date('Y-m-d H:i:s');
}
echo '<td colspan="2">' . '订单日' . _('From') . ':</td>
		<td><input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="FromDate" maxlength="10" size="11" value="' . $_POST['FromDate'] . '" /></td>
		<td>' . _('To') . ':</td>
		<td><input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="ToDate" maxlength="10" size="11" value="' . $_POST['ToDate'] . '" /></td>
	</tr>';


if (!isset($_POST['FromDate1'])) {
    $_POST['FromDate1'] = date("Y-m-d H:i:s", mktime(0, 0, 0, date("m"), date("d") - 30, date("Y")));
}
if (!isset($_POST['ToDate1'])) {
    $_POST['ToDate1'] = Date('Y-m-d H:i:s');
}
echo '<td colspan="2">' . '出货日' . _('From') . ':</td>
		<td><input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="FromDate1" maxlength="10" size="11" value="' . $_POST['FromDate1'] . '" /></td>
		<td>' . _('To') . ':</td>
		<td><input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="ToDate1" maxlength="10" size="11" value="' . $_POST['ToDate1'] . '" /></td>
	</tr>';



echo '</table><div class="centre"><input type="submit" name="Search" value="查找"></div>';

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
                    <th class="ascending" width = 150>' . _('订单号') . '</th>
                    <th class="ascending"width = 150>' . _('客户名称') . '</th>
         
                    <th class="ascending"width = 150>' . _('联系电话') . '</th>
                
                    <th class="ascending"width = 250>' . _('地址') . '</th>
                        <th class="ascending"width = 150>' . _('出货日期') . '</th>
                            <th class="ascending"width = 150>' . _('订单金额') . '</th>
                   
            </tr>';
    $k = 0; //row counter to determine background colour
    $RowIndex = 0;



    if (DB_num_rows($result) <> 0) {
        DB_data_seek($result, ($_POST['PageOffset'] - 1) * 10);
        $i = 0; //counter for input controls
        while (($myrow = DB_fetch_array($result)) AND ( $RowIndex <> 10)) {
            if ($k == 1) {
                echo '<tr class="EvenTableRows">';
                $k = 0;
            } else {
                echo '<tr class="OddTableRows">';
                $k = 1;
            }
            echo '  <td><a href="' . $RootPath . '/SOOut.php?NUM=' . $myrow['order_number'] . '">' . $myrow['order_number'] . '</td>
				<td>' . $myrow['order_man'] . '</td>
				<td>' . $myrow['man_contact'] . '</td>
                                    <td>' . $myrow['man_address'] . '</td>
				<td>' . date('Y-m-d H:i:s', $myrow['schedule_ship_date']) . '</td>
                                    <td>' . $myrow['order_amount'] . '</td>
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
if (isset($_POST['add_new'])) {
    header('Location: AddSupplier.php');
}
include('includes/footer.inc');
