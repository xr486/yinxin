<?php

include('includes/session.inc');

$Title = _('工单未核发查询');

include('includes/header.inc');
include('includes/CountriesArray.php');

if (isset($_GET['SelectedWIPNAME'])){
    $SelectedWIPNAME = $_GET['SelectedWIPNAME'];
} elseif (isset($_POST['SelectedWIPNAME'])){
    $SelectedWIPNAME = $_POST['SelectedWIPNAME'];
}

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
echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('工单未核发查询') . '</p>';
echo '<table cellpadding="3" class="selection">';

echo '<tr><td >' . _('工单起') . ':</td><td>';
echo '<input type="text" name="WIP_ENTITY_NAME_from" value="' . $_POST['WIP_ENTITY_NAME_from'] . '" size="20" maxlength="25" /></td>';
echo '<td>' . _('工单止') . ':</td>
	<td>';
echo '<input type="text" name="WIP_ENTITY_NAME_to" value="' . $_POST['WIP_ENTITY_NAME_to'] . '" size="20" maxlength="25" /></td>';
echo '</tr>';
echo '<tr><td >' . _('料号名称') . ':</td><td>';
echo '<input type="text" name="item_no" value="' . $_POST['item_no'] . '" size="20" maxlength="25" /></td>';
echo '</tr>';


if (!isset($_POST['FromDate'])) {
    $_POST['FromDate'] = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 30, date("Y")));
}
if (!isset($_POST['ToDate'])) {
    $_POST['ToDate'] = Date('Y-m-d', mktime(0, 0, 0, date("m"), date("d") + 1, date("Y")));
}
echo '<td>' . '预计开工日期' . _('From') . ':</td>
		<td><input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="FromDate" maxlength="10" size="11" value="'.$_POST['FromDate'].'" /></td>
		<td>' . _('To') . ':</td>
		<td><input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="ToDate" maxlength="10" size="11" value="'.$_POST['ToDate'].'" /></td>
	</tr>';

echo '</table><div class="centre"><input type="submit" name="Search" value="查找"> </div>'
    . '</br>';



if (!isset($SelectedWIPNAME)  and  isset($_POST['Search']) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous']) ) {

    $sql = "SELECT  a.scheduled_start_date ,
					a.scheduled_completion_date,
					a.start_quantity,a.wip_entity_name,a.not_quantity,a.stockid,a.so_line_number,b.order_number,
					b.quantity,a.quantity_shiped
				FROM wip_jobs_all a,so_lines_all b
            WHERE 1=1 and a.so_header_number=b.order_number and a.so_line_number=b.line and a.approve_status=0
			";
    if (isset($_POST['WIP_ENTITY_NAME_from']) and $_POST['WIP_ENTITY_NAME_from'] != '') {
        $sql = $sql . " and a.so_header_number >=  '" . $_POST['WIP_ENTITY_NAME_from'] . "'";
    }
    if (isset($_POST['WIP_ENTITY_NAME_to']) and $_POST['WIP_ENTITY_NAME_to'] != '') {
        $sql = $sql . " and a.so_header_number <=  '" . $_POST['WIP_ENTITY_NAME_to'] . "' ";
    }
    if (isset($_POST['item_no']) and $_POST['item_no'] != '') {
        $sql = $sql . " and b.stockid " . LIKE . " '%" . $_POST['item_no'] . "%' ";
    }

    if (empty($_POST['FromDate']) == 0) {
        $SQL_FromDate = strtotime($_POST['FromDate']);
        $sql .= " and a.scheduled_start_date >= '" . $SQL_FromDate . "' ";
    }
    if (empty($_POST['ToDate']) == 0) {
        $SQL_ToDate = strtotime($_POST['ToDate']);
        //echo $SQL_ToDate;
        $sql .= " and a.scheduled_completion_date <='" . $SQL_ToDate . "' ";
    }
    //echo $sql;

    $result = DB_query($sql, $db);
    if (@DB_num_rows($result) == 0) {
        unset($result);
        prnMsg(_('找不到该工单，请重新输入条件查询！'), 'error');
    }
    $ListCount = @DB_num_rows($result);
    $ListPageMax = ceil($ListCount / 10); //$_SESSION['DisplayRecordsMax']

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
    echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' .
        _('Inventory') . '" alt="" />' . ' ' . $Title . '</p>';
    if ($ListPageMax > 1) {
        ?>
        <br/>

        <div class="centre">&nbsp;&nbsp;第&nbsp;<?= $_POST['PageOffset'] ?>&nbsp;页，共&nbsp;<?= $ListPageMax ?>&nbsp;页&nbsp;&nbsp;
            跳转至页:
            <select name="PageOffset1">
                <?php
                $ListPage = 1;
                while ($ListPage <= $ListPageMax) {
                    if ($ListPage == $_POST['PageOffset']) {
                        echo '<option value="' . $ListPage . '" selected="selected">' . $ListPage . '</option>';
                    } else {
                        echo '<option value="' . $ListPage . '">' . $ListPage . '</option>';
                    }
                    $ListPage++;
                }
                ?>
            </select>

            <input type="submit" name="Go1" value="跳转"/>
            <input type="submit" name="Previous" value="上一页"/>
            <input type="submit" name="Next" value="下一页"/>

        </div>
    <?php } ?>
    <?php
    echo '<table class="selection">';
    echo '<tr>
			<th>' . _('工单名称') . '</th>
			<th>行</th>
			<th>' . _('成品料号') . '</th>
			<th>' . _('预计开工日期') . '</th>
			<th>' . _('预计完工日期') . '</th>
			<th>' . _('订单总量') . '</th>
			<th>' . _('订单已开工量') . '</th>
            <th>' . _('订单未开工数量') . '</th>
            <th>' . _('本工单开工量') . '</th>
		</tr> <input type="hidden" name="PageOffset" value=' . $_POST['PageOffset'] . ' />';

    $k = 0; //row colour counter
    $RowIndex = 0;
    $i = 0;
    if (@DB_num_rows($result) <> 0) {
        DB_data_seek($result, ($_POST['PageOffset'] - 1) * 10);
        while (($myrow = @DB_fetch_array($result)) AND ( $RowIndex <> 10)) {
            if ($k == 1) {
                echo '<tr class="EvenTableRows">';
                $k = 0;
            } else {
                echo '<tr class="OddTableRows">';
                $k = 1;
            }
            echo '
	<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">
	<td>' . $myrow['wip_entity_name'] . '</td>
	<td>' . $myrow['so_line_number'] . '</td>
			<td>' . $myrow['stockid'] . '</td>
			<td>' . date("Y-m-d", $myrow['scheduled_start_date']) . '</td>
            <td>' . date("Y-m-d", $myrow['scheduled_completion_date']) . '</td>
			<td>  ' . $myrow['quantity'] . '</td>
			<td>  ' . $myrow['quantity_shiped'] . '</td>
            <td>  ' . $myrow['not_quantity'] . '</td>
			<td>' . $myrow['start_quantity'] . '  </td>
			</tr>
			</form>
	';
            $i++;
            $RowIndex++;
        }
        //END WHILE LIST LOOP
        echo '</table>';
    }
}
//end of ifs and buts!

echo '<br />';
if (isset($SelectedWIPNAME)) {
    echo '<a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' . _('回到工单选择界面') . '</a>';
}
echo '<br />';
if (!isset($_GET['delete'])  and (isset($SelectedWIPNAME)) ) {

    echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
    echo '<div>';
    echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

    if (isset($SelectedWIPNAME)) {
        //editing an existing Location
        echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' .
            _('Inventory') . '" alt="" />' . ' ' . $Title . '</p>';

        $sql = "SELECT  a.wip_entity_name,
						a.scheduled_start_date,
						a.scheduled_completion_date,
						a.start_quantity,
						b.quantity
				FROM wip_jobs_all a,so_lines_all b
				WHERE a.wip_entity_name='" . $SelectedWIPNAME . "' and a.so_header_number=b.order_number";

        $result = DB_query($sql, $db);
        $myrow = DB_fetch_array($result);
        $_POST['WIP_ENTITY_NAME'] = $myrow['wip_entity_name'];
        $_POST['START_QUANTITY'] = $myrow['start_quantity'];
        $_POST['SCHEDULED_COMPLETION_DATE'] = date('Y-m-d', $myrow['scheduled_completion_date']);
        $_POST['SCHEDULED_START_DATE'] = date('Y-m-d', $myrow['scheduled_start_date']);

        echo '<input type="hidden" name="SelectedWIPNAME" value="' . $SelectedWIPNAME . '" />';
        echo '<input type="hidden" name="WIP_ENTITY_NAME" value="' . $_POST['WIP_ENTITY_NAME'] . '" />';
        echo '<table class="selection">';
        echo '<tr>
				<th colspan="2">' . _('修改工单') . '</th>
			</tr>';
        echo '<tr>
				<td>' . _('工单名称') . ':</td>
				<td>' . $_POST['WIP_ENTITY_NAME'] . '</td>
			</tr>';

        echo '
		<tr>
			<td>' . _('开工数量') . ':' . '</td>
			<td><input type="text" name="START_QUANTITY" size="8" required="required" value="' . $_POST['START_QUANTITY'] . '"  />
			<td><input type="hidden" name="START_QUANTITY_01" value="' . $_POST['START_QUANTITY'] . '"  />
			<input type="hidden" name="quantity" value="' . $myrow['quantity'] . '">
			</td>
			</td>
		</tr>';

        echo '<tr>
				<td>' . _('预计开工日期') . ' :</td>
				<td><input type="text" required="required" name="SCHEDULED_START_DATE" onfocus="WdatePicker()"alt="' . $_SESSION['DefaultDateFormat'] . '" size="11" maxlength="10" value="' . $_POST['SCHEDULED_START_DATE'] . '" /></td>
			</tr>
			<tr>
				<td>' . _('预计完工日期') . ':</td>
				<td><input type="text" required="required" name="SCHEDULED_COMPLETION_DATE" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" size="11" maxlength="10" value="' . $_POST['SCHEDULED_COMPLETION_DATE'] . '" /></td>
			</tr>';

    }

    echo '</table>
		<br />
		<div class="centre">
			<input type="submit" name="submit" value="' .  _('保存修改') . '" />
		</div>
        </div>
		</form>';

} //end if record deleted no point displaying form to add record

include('includes/footer.inc');
?>
