<?php

ob_start();
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include('includes/session.inc');
$Title = _('查询待签核估价单');
$ViewTopic = '查询待签核估价单';
$BookMark = '查询待签核估价单';

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
	*
FROM valuation_list_header 
WHERE check_flag in (0,2)";

    if (isset($_POST['re_from']) and $_POST['re_from'] != '') {
        $sql = $sql . " and valuation_list_header_no >=  '" . $_POST['re_from'] . "'";
    }
    if (isset($_POST['re_to']) and $_POST['re_to'] != '') {
        $sql = $sql . " and valuation_list_header_no <=  '" . $_POST['re_to'] . "' ";
    }
    if (isset($_POST['customer_name']) and $_POST['customer_name'] != '') {
        $sql = $sql . " and customer_name " . LIKE . " '%" . $_POST['customer_name'] . "%' ";
    }
   
    if (empty($_POST['FromDate']) == 0) {
        $SQL_FromDate = strtotime($_POST['FromDate']);
        $sql .= " and comein_date >= '" . $SQL_FromDate . "' ";
    }
    if (empty($_POST['ToDate']) == 0) {
        $SQL_ToDate = strtotime($_POST['ToDate']) + 86400;
        //echo $SQL_ToDate;
        $sql .= " and comein_date <='" . $SQL_ToDate . "' ";
    }
     
    $result = DB_query($sql, $db);
    if (DB_num_rows($result) == 0) {
        unset($result);
        prnMsg(_('找不到待签核估价单，请重新输入条件查询！'), 'error');
    }
}


echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('查询查询待签核估价单') . '</p>';
echo '<table cellpadding="3" class="selection">';

echo '<tr><td >' . _('估价单起') . ':</td><td>';
echo '<input type="text" name="re_from" value="' . $_POST['re_from'] . '" size="20" maxlength="25" /></td>';
echo '<td>' . _('估价单止') . ':</td>
	<td>';
echo '<input type="text" name="re_to" value="' . $_POST['re_to'] . '" size="20" maxlength="25" /></td>';
echo '</tr>';
echo '<tr><td >' . _('客户名称') . ':</td><td>';
echo '<input type="text" name="customer_name" value="' . $_POST['customer_name'] . '" size="20" maxlength="25" /></td>';
echo '</tr>';

if (!isset($_POST['FromDate'])) {
    $_POST['FromDate'] = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 30, date("Y")));
}
if (!isset($_POST['ToDate'])) {
    $_POST['ToDate'] = Date('Y-m-d');
}
echo '<td>' . '进厂日期' . _('From') . ':</td>
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
                    <th class="ascending" width = 100>' . _('估价单号') . '</th>
                    <th class="ascending"width = 70>' . _('状态') . '</th>
					 <th class="ascending"width = 250>' . _('客户名称') . '</th>
                     <th class="ascending"width = 250>' . _('配件金额') . '</th>
                     <th class="ascending"width = 250>' . _('快修金额') . '</th>
                    <th class="ascending"width = 120>' . _('总额') . '</th>                           
                    <th class="ascending"width = 200>' . _('备注') . '</th>
                    <th class="ascending"width = 100>' . _('进厂日期') . '</th>
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
            if ($myrow['check_flag'] == 0) {
                $v_status = '待签核';
            } elseif ($myrow['check_flag'] == 1) {
                $v_status = '已签核';
            } elseif ($myrow['check_flag'] == 2) {
                $v_status = '已拒签';
            } else {
                $v_status = '已取消';
            }
            echo '  <td><a href="' . $RootPath . '/REToApprove.php?Updatevaluation_list_header_no=' . $myrow['valuation_list_header_no'] . '">' . $myrow['valuation_list_header_no'] . '</td>
				<td>' . $v_status . '</td>
					<td>' . $myrow['customer_name'] . '</td> 
                  <td>' . $myrow['g_fittings_value'] . '</td>
                  <td>' . $myrow['g_source_value'] . '</td>
                  <td>' . ($myrow['g_fittings_value']+$myrow['g_source_value']) . '</td>
				<td>' . $myrow['remark'] . '</td>
				<td>' . date('Y-m-d H:i:s', $myrow['comein_date']) . '</td>
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
