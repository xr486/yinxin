<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include('includes/session.inc');
$Title = _('修改料号');
$ViewTopic = '修改料号';
$BookMark = '修改料号';

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
    $SQL_FromDate = FormatDateForSQL($_POST['FromDate']);
    $SQL_ToDate = FormatDateForSQL($_POST['ToDate']);
    $sql = "SELECT
	sm.stockid,
	sm.description,
	sm.units
FROM
	stockmaster sm
WHERE sm.categoryid = '原料'
and discontinued = 0 ";

    if (isset($_POST['ItemNo']) and $_POST['ItemNo']!='') {
        $sql = $sql." and sm.stockid ".LIKE." '%".$_POST['ItemNo']."%' ";
    }
    if (isset($_POST['ItemDesc']) and $_POST['ItemDesc']!='') {
        $sql = $sql." and sm.description ".LIKE." '%".$_POST['ItemDesc']."%' ";
    }
    if (isset($_POST['ProductType']) and $_POST['ProductType']!='') {
        $sql = $sql." and pt.type_name ".LIKE." '%".$_POST['ProductType']."%' ";
    }
    if (isset($_POST['ItemType']) and $_POST['ItemType']!='') {
        $sql = $sql." and sm.itemtype ".LIKE." '%".$_POST['ItemType']."%' ";
    }
    if (isset($_POST['Disposal']) and $_POST['Disposal']!='') {
        $sql = $sql." and dw.disposalname ".LIKE." '%".$_POST['Disposal']."%' ";
    }
    if (isset($_POST['categoryid']) and $_POST['categoryid']!='') {
        $sql = $sql." and sm.categoryid ".LIKE." '%".$_POST['categoryid']."%' ";
    }
    $sql .= " ORDER BY sm.stockid";
    $result = DB_query($sql, $db);
    if (DB_num_rows($result) == 0) {
        unset($result);
        prnMsg(_('找不到数据，请重新输入条件查询！'), 'error');
    }
}


echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('选择物料') . '</p>';
echo '<table cellpadding="3" class="selection">';

echo '<tr>	
			<td>' . '部分料号编号' . ':</td>
			<td><input  type="text" name="ItemNo" value="' . $_POST['ItemNo'] . '" /></td>';
echo '<td>' . _('或') . '</td>';
echo '<td>' . _('部分料号名称') . ':</td><td><input  type="text" name="ItemDesc" value="' . $_POST['ItemDesc'] . '" /></td>';

echo '</tr><tr>	
			<td>' . '部分用处' . ':</td>
			<td><input  type="text" name="Disposal" value="' . $_POST['Disposal'] . '" /></td>';
echo '<td>' . _('或') . '</td>';
echo '<td>' . _('部分存储') . ':</td><td><input  type="text" name="ItemType" value="' . $_POST['ItemType'] . '" /></td></tr></table>';
echo '<div class="centre"><input type="submit" name="Search" value="' . _('查找') . '"></div>';

if (isset($_POST['Search']) and isset($result) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])) {
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
        echo '<br /><div class="centre">&nbsp;&nbsp;' . $_POST['PageOffset'] . ' ' . _('of') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ': ';
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
    echo '<br />
                    <table cellpadding="2" class="selection">';

    echo '<tr>
                    <th class="ascending">' .  _('物料编号') . '</th>
                    <th class="ascending">' .  _('物料名称') . '</th>
                    <th class="ascending">' .  _('单位'). '</th>
		    <th class="ascending">' .  _('用处'). '</th>
                    <th>' .  _('修改'). '</th>
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
            echo '  <td>' . $myrow['stockid'] . '</td>
                    <td>' . $myrow['description'] . '</td>
                    <td>' . $myrow['units'] . '</td>
		    <td>' . $myrow['disposalname'] . '</td>
                    <td><a href="' . $RootPath . '/StocksP.php?StockID=' . $myrow['stockid'] . '">修改</td>
                </tr>';
            $i++;
            $RowIndex++;
            //end of page full new headings if
        } //end loop through customers
        echo '</table>';
    }

    if (isset($ListPageMax) AND $ListPageMax > 1) {
        echo '<br /><div class="centre">&nbsp;&nbsp;' . $_POST['PageOffset'] . ' ' . _('of') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ': ';
        echo '<select name="PageOffset2">';
        $ListPage = 1;
        while ($ListPage <= $ListPageMax) {
            if ($ListPage == $_POST['PageOffset']) {
                echo '<option value="' . $ListPage . '" selected="selected">' . $ListPage . '</option>';
            } //$ListPage == $_POST['PageOffset']
            else {
                echo '<option value="' . $ListPage . '">' . $ListPage . '</option>';
            }
            $ListPage++;
        } //$ListPage <= $ListPageMax
        echo '</select>
                        <input type="submit" name="Go2" value="' . _('Go') . '" />
                        <input type="submit" name="Previous" value="' . _('Previous') . '" />
                        <input type="submit" name="Next" value="' . _('Next') . '" />';
        echo '</div>';
    }//end if results to show
}
echo '</div></form>';
include('includes/footer.inc');
