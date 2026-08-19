<?php

ob_start();
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include('includes/session.inc');
$Title = _('工单领料处理');
$ViewTopic = '工单领料处理';
$BookMark = '工单领料处理';

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
    $sql = "select  lot_number,b.item_no ,wip_entity_name,a.status_type,quantity_plan,plan_start_date,
	plan_end_date,a.creation_date,a.CREATED_BY,b.item_desc,ifnull(a.input_quantity,0) input_quantity,ifnull(a.QUANTITY_COMPLETE,0) QUANTITY_COMPLETED
				FROM so_lines_all a,sf_item_no b
              WHERE   a.status_type in ('核发') 
				and a.stockid=b.item_no    
				and a.quantity_plan>ifnull(a.input_quantity,0)   ";

    
     if (isset($_POST['wip_entity_name_from']) and $_POST['wip_entity_name_from'] != '') {
        $sql = $sql . " and a.wip_entity_name >=  '" . $_POST['wip_entity_name_from'] . "'";
    }
    if (isset($_POST['wip_entity_name_to']) and $_POST['wip_entity_name_to'] != '') {
        $sql = $sql . " and a.wip_entity_name <=  '" . $_POST['wip_entity_name_to'] . "' ";
    }
    if (isset($_POST['item_no']) and $_POST['item_no'] != '') {
        $sql = $sql . " and b.item_no " . LIKE . " '%" . $_POST['item_no'] . "%' ";
    }
   
    if (empty($_POST['FromDate']) == 0) {
        $SQL_FromDate = strtotime($_POST['FromDate']);
        $sql .= " and a.plan_start_date >= '" . $SQL_FromDate . "' ";
    }
    if (empty($_POST['ToDate']) == 0) {
        $SQL_ToDate = strtotime($_POST['ToDate']) + 86400;
        //echo $SQL_ToDate;
        $sql .= " and a.plan_start_date <='" . $SQL_ToDate . "' ";
    }
      
    $result = DB_query($sql, $db);
    if (DB_num_rows($result) == 0) {
        unset($result);
        prnMsg(_('找不到该工单，请重新输入条件查询！'), 'error');
    }
}


echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('查询工单') . '</p>';
echo '<table cellpadding="3" class="selection">';

echo '<tr><td >' . _('工单起') . ':</td><td>';
echo '<input type="text" name="wip_entity_name_from" value="' . $_POST['wip_entity_name_from'] . '" size="20" maxlength="25" /></td>';
echo '<td>' . _('工单止') . ':</td>
	<td>';
echo '<input type="text" name="wip_entity_name_to" value="' . $_POST['wip_entity_name_to'] . '" size="20" maxlength="25" /></td>';
echo '</tr>';
echo '<tr><td >' . _('成品图号') . ':</td><td>';
echo '<input type="text" name="item_no" value="' . $_POST['item_no'] . '" size="20" maxlength="25" /></td>';
echo '</tr>';

if (!isset($_POST['FromDate'])) {
    $_POST['FromDate'] = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 30, date("Y")));
}
if (!isset($_POST['ToDate'])) {
    $_POST['ToDate'] = Date('Y-m-d');
}
echo '<td>' . '预计开工日期' . _('From') . ':</td>
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
                    <th class="number" width = 100>' . _('工单号') . '</th>
                    <th class="number" width = 60>' . _('状态') . '</th>
                    <th class="number" width = 100>' . _('成品图号') . '</th>
					 <th class="number" width = 200>' . _('成品描述') . '</th>
					 <th class="number" width = 100>' . _('批号') . '</th>
                    <th class="number" width = 80>' . _('开工数量') . '</th>
					<th class="number" width = 80>' . _('已入库数量') . '</th>   
					<th class="number" width = 80>' . _('领料数量') . '</th>    
					<th class="number" width = 80>' . _('待领料数量') . '</th> 
                    <th class="number"width = 100>' . _('建立人员') . '</th>
                    <th class="number"width = 100>' . _('预计开工日期') . '</th>
                    <th class="number"width = 100>' . _('建立日期') . '</th>
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
            $wati_qty=$myrow['quantity_plan'] - $myrow['input_quantity'];
			  echo '  <td><a href="' . $RootPath . '/WIPSnUpdate.php?Updatewip_entity_name=' . $myrow['wip_entity_name'] . '">' . $myrow['wip_entity_name'] . '</td>
				<td>' . $myrow['status_type'] . '</td>
                    <td>' . $myrow['item_no'] . '</td> 
					<td>' . $myrow['item_desc'] . '</td> 
					<td>' . $myrow['lot_number'] . '</td> 
					<td>' . $myrow['quantity_plan'] . '</td> 
					<td>' . $myrow['QUANTITY_COMPLETED'] . '</td> 
					<td>' . $myrow['input_quantity'] . '</td> 
					<td>' . $wati_qty . '</td> 
					<td>' . $myrow['CREATED_BY'] . '</td> 
                   <td>' . date('Y-m-d', $myrow['plan_start_date']) . '</td>
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
