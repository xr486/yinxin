<?php
ob_start();
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include('includes/session.inc');
$Title = _('工单领用泥芯明细');
$ViewTopic= '工单领用泥芯明细';
$BookMark = '泥芯工单领用泥芯明细';

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

if(isset($_POST['Search']) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])){
    $sql = "select a.wip_entity_name,a.line,a.endproduct,a.loamcoreid,a.quantity
 ,b.DATE_REQUIRED,b.REQUIRED_QUANTITY,b.QUANTITY_ISSUED,b.QUANTITY_PER_ASSEMBLY
 from wip_get_loamcore_detailed a,wip_material_requierments b where 1=1
  and a.wip_entity_name=b.WIP_ENTITY_NAME and a.loamcoreid=b.SEGMENT1
  ";
    if (isset($_POST['WIP_ENTITY_NAME_from']) and $_POST['WIP_ENTITY_NAME_from'] != '') {
        $sql = $sql . " and a.wip_entity_name >=  '" . $_POST['WIP_ENTITY_NAME_from'] . "'";
    }
    if (isset($_POST['WIP_ENTITY_NAME_to']) and $_POST['WIP_ENTITY_NAME_to'] != '') {
        $sql = $sql . " and a.wip_entity_name <=  '" . $_POST['WIP_ENTITY_NAME_to'] . "' ";
    }
    if (isset($_POST['item_no']) and $_POST['item_no'] != '') {
        $sql = $sql . " and a.endproduct " . LIKE . " '%" . $_POST['item_no'] . "%' ";
    }
 if (empty($_POST['FromDate']) == 0) {
     $SQL_FromDate = strtotime($_POST['FromDate']);
     $sql .= " and a.creation_date >= '" . $SQL_FromDate . "' ";
 }
    if (empty($_POST['ToDate']) == 0) {
        $SQL_ToDate = strtotime($_POST['ToDate']) ;
        //echo $SQL_ToDate;
        $sql .= " and a.creation_date <='" . $SQL_ToDate . "' ";
    }

    $result = DB_query($sql,$db);
    if (DB_num_rows($result)==0) {
        unset($result);
        prnMsg(_('暂未找到工单，请重新输入条件查询！') ,'error');
    }
}


echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('工单领用泥芯明细') . '</p>';
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
		<td><input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="FromDate" maxlength="10" size="11" value="' . $_POST['FromDate'] . '" /></td>
		<td>' . '预计结束日期'._('To') . ':</td>
		<td><input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="ToDate" maxlength="10" size="11" value="' . $_POST['ToDate'] . '" /></td>
	</tr>';

echo '</table><div class="centre"><input type="submit" name="Search" value="查找"> </div>'
    . '</br>';

if (isset($_POST['Search']) and isset($result) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])){
    $ListCount = DB_num_rows($result);
    $ListPageMax = ceil($ListCount / 20);

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
    echo '
                    <table cellpadding="2" class="selection" >';

    echo '<tr>
                    <th class="ascending" width = 150>' . _('工单号') . '</th>
					<th   width = 150>' . _('行') . '</th>
                    <th  width = 150>' . _('成品料号') . '</th>
                    <th  width = 150>' . _('泥芯编号') . '</th>
                    <th  width = 150>' . _('领取数量') . '</th>
                    <th  width = 150>' . _('需求时间') . '</th>
                    <th  width = 150>' . _('需求成品数量') . '</th>
                    <th  width = 150>' . _('已领数量') . '</th>
                    <th  width = 150>' . _('每个成品所需数量') . '</th>

            </tr>';
    $k = 0; //row counter to determine background colour
    $RowIndex = 0;

/*
 * $sql = "select a.wip_entity_name,a.line,a.endproduct,a.loamcoreid,a.quantity
 ,b.DATE_REQUIRED,b.REQUIRED_QUANTITY,b.QUANTITY_ISSUED,b.QUANTITY_PER_ASSEMBLY
 from get_loamcore_detailed a,wip_material_requierments b where 1=1
  and a.wip_entity_name=b.WIP_ENTITY_NAME and a.loamcoreid=b.SEGMENT1
 */

    if (DB_num_rows($result) <> 0) {
        DB_data_seek($result, ($_POST['PageOffset'] - 1) * 20);
        $i = 0; //counter for input controls
        while (($myrow = DB_fetch_array($result)) AND ($RowIndex <> 20)) {
            if ($k == 1) {
                echo '<tr class="EvenTableRows">';
                $k = 0;
            } else {
                echo '<tr class="OddTableRows">';
                $k = 1;
            }
            echo '  <td>' . $myrow['wip_entity_name'] . '</td>
			<td>' . $myrow['line'] . '</td>
				<td>' . $myrow['endproduct'] . '</td>
				<td>' . $myrow['loamcoreid'] . '</td>
				<td>' . $myrow['quantity'] . '</td>
				<td>' . $myrow['DATE_REQUIRED'] . '</td>
				<td>' . $myrow['REQUIRED_QUANTITY'] . '</td>
				<td>' . $myrow['QUANTITY_ISSUED'] . '</td>
				<td>' . $myrow['QUANTITY_PER_ASSEMBLY'] . '</td>
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



}
echo '</div></form>';
if (isset($_POST['add_new'])) {
    header('Location: AddSupplier.php');
}
include('includes/footer.inc');