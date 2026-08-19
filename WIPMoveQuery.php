<?php

ob_start();
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include('includes/session.inc');
$Title = _('工单搬站明细查询');
$ViewTopic = '工单搬站明细查询';
$BookMark = '工单搬站明细查询';

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
    $sql = "select  PRIMARY_ITEM ,a.WIP_ENTITY_NAME,a.JOB_TYPE,c.transaction_type,c.transaction_date,
	c.FM_OPERATION_SEQ,c.PRODUCT_LINE,c.TO_OPERATION_SEQ,c.TRANSACTION_QUANTITY,c.BAD_QUANTITY,c.SCRAPPED_QUANTITY,c.PRODUCT_PERSON,c.COST_TIME,c.COST_TIME_UOM,(select OPERATION_CODE from wip_operations d where c.FM_OPERATION_SEQ=d.operation_seq_num and a.wip_entity_name=d.wip_entity_name) FM_OPERATION_CODE,(select OPERATION_CODE from wip_operations d where c.TO_OPERATION_SEQ=d.operation_seq_num and a.wip_entity_name=d.wip_entity_name) TO_OPERATION_CODE
				FROM wip_jobs_all a,wip_moves_all c
           WHERE     a.wip_entity_name=c.wip_entity_name
		 ";

    
     if (isset($_POST['WIP_ENTITY_NAME_from']) and $_POST['WIP_ENTITY_NAME_from'] != '') {
        $sql = $sql . " and a.WIP_ENTITY_NAME >=  '" . $_POST['WIP_ENTITY_NAME_from'] . "'";
    }
    if (isset($_POST['WIP_ENTITY_NAME_to']) and $_POST['WIP_ENTITY_NAME_to'] != '') {
        $sql = $sql . " and a.WIP_ENTITY_NAME <=  '" . $_POST['WIP_ENTITY_NAME_to'] . "' ";
    }
    if (isset($_POST['PRIMARY_ITEM']) and $_POST['PRIMARY_ITEM'] != '') {
        $sql = $sql . " and b.PRIMARY_ITEM " . LIKE . " '%" . $_POST['PRIMARY_ITEM'] . "%' ";
    }
   
    if (empty($_POST['FromDate']) == 0) {
        $SQL_FromDate = strtotime($_POST['FromDate']);
        $sql .= " and c.transaction_date >= '" . $SQL_FromDate . "' ";
    }
    if (empty($_POST['ToDate']) == 0) {
        $SQL_ToDate = strtotime($_POST['ToDate']) + 86400;
        //echo $SQL_ToDate;
        $sql .= " and c.transaction_date <='" . $SQL_ToDate . "' ";
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
echo '<input type="text" name="WIP_ENTITY_NAME_from" value="' . $_POST['WIP_ENTITY_NAME_from'] . '" size="20" maxlength="25" /></td>';
echo '<td>' . _('工单止') . ':</td>
	<td>';
echo '<input type="text" name="WIP_ENTITY_NAME_to" value="' . $_POST['WIP_ENTITY_NAME_to'] . '" size="20" maxlength="25" /></td>';
echo '</tr>';
echo '<tr><td >' . _('料号名称') . ':</td><td>';
echo '<input type="text" name="PRIMARY_ITEM" value="' . $_POST['PRIMARY_ITEM'] . '" size="20" maxlength="25" /></td>';
echo '</tr>';

if (!isset($_POST['FromDate'])) {
    $_POST['FromDate'] = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 30, date("Y")));
}
if (!isset($_POST['ToDate'])) {
    $_POST['ToDate'] = Date('Y-m-d');
}
echo '<td>' . '搬站日期' . _('From') . ':</td>
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
					<th class="number" width = 70>' . _('产品料号') . '</th>
                    <th class="number" width = 70>' . _('交易类型') . '</th>
					<th class="number" width = 50>' . _('工序') . '</th>
					 <th class="number" width = 80>' . _('制程') . '</th>
                    <th class="number" width = 80>' . _('下一工序') . '</th>
					 <th class="number" width = 80>' . _('下一制程') . '</th>
                    <th class="number" width = 70>' . _('良品数量') . '</th> 
					<th class="number" width = 70>' . _('不良数量') . '</th>
					<th class="number" width = 80>' . _('报废数量') . '</th>     
                    <th class="number"width = 80>' . _('线别') . '</th>
                    <th class="number"width = 100>' . _('日期') . '</th>
					<th class="number"width = 100>' . _('制造人员') . '</th>
					<th class="number"width = 100>' . _('制造时间') . '</th>
					<th class="number"width = 100>' . _('时间单位') . '</th>

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

			  echo '<td>'. $myrow['WIP_ENTITY_NAME'] . '</td>
                    <td>' . $myrow['PRIMARY_ITEM'] . '</td> 
				<td>' . $myrow['transaction_type'] . '</td>
					<td>' . $myrow['FM_OPERATION_SEQ'] . '</td>
				<td>' . $myrow['FM_OPERATION_CODE'] . '</td> 
					<td>' . $myrow['TO_OPERATION_SEQ'] . '</td> 
					<td>' . $myrow['TO_OPERATION_CODE'] . '</td> 
					<td>' . $myrow['TRANSACTION_QUANTITY'] . '</td> 
					<td>' . $myrow['BAD_QUANTITY'] . '</td>
					<td>' . $myrow['SCRAPPED_QUANTITY'] . '</td> 
					<td>' . $myrow['PRODUCT_LINE'] . '</td> 
                   <td>' . date('Y-m-d', $myrow['transaction_date']) . '</td>
				   <td>' . $myrow['PRODUCT_PERSON'] . '</td> 
				   <td>' . $myrow['COST_TIME'] . '</td> 
				   <td>' . $myrow['COST_TIME_UOM'] . '</td> 
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
