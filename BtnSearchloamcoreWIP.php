<?php

include('includes/session.inc');

$Title = _('泥芯工单领模具');

include('includes/header3.inc');
include('includes/CountriesArray.php');

if (isset($_GET['SelectedWIPNAME'])){
    $SelectedWIPNAME = $_GET['SelectedWIPNAME'];
} elseif (isset($_POST['SelectedWIPNAME'])){
    $SelectedWIPNAME = $_POST['SelectedWIPNAME'];
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
$sql = "SELECT  loamcoreid,
					loamcorename,
					stockid,
					remark,
					iphone,
					scheduled_start_date,
					scheduled_completion_date,
					start_quantity,wip_entity_name,creation_date
				FROM wip_loamcore_work
            WHERE 1=1 and insubquantity<>start_quantity
			";
if (isset($_POST['WIP_ENTITY_NAME_from']) and $_POST['WIP_ENTITY_NAME_from'] != '') {
    $sql = $sql . " and wip_entity_name >=  '" . $_POST['WIP_ENTITY_NAME_from'] . "'";
}
if (isset($_POST['WIP_ENTITY_NAME_to']) and $_POST['WIP_ENTITY_NAME_to'] != '') {
    $sql = $sql . " and wip_entity_name <=  '" . $_POST['WIP_ENTITY_NAME_to'] . "' ";
}
if (isset($_POST['item_no']) and $_POST['item_no'] != '') {
    $sql = $sql . " and stockid " . LIKE . " '%" . $_POST['item_no'] . "%' ";
}

if (empty($_POST['FromDate']) == 0) {
    $SQL_FromDate = strtotime($_POST['FromDate']);
    $sql .= " and scheduled_start_date >= '" . $SQL_FromDate . "' ";
}
if (empty($_POST['ToDate']) == 0) {
    $SQL_ToDate = strtotime($_POST['ToDate']);
    //echo $SQL_ToDate;
    $sql .= " and scheduled_completion_date <='" . $SQL_ToDate . "' ";
}
//echo $sql;

$result = DB_query($sql, $db);
if (@DB_num_rows($result) == 0) {
    unset($result);
    prnMsg(_('找不到该工单，请重新输入条件查询！'), 'error');
}
$ListCount = @DB_num_rows($result);
$ListPageMax = ceil($ListCount / 10); //$_SESSION['DisplayRecordsMax']

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('查询泥芯工单') . '</p>';
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
    $_POST['ToDate'] = Date('Y-m-d');
}
echo '<td>' . '预计开工日期' . _('From') . ':</td>
		<td><input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="FromDate" maxlength="10" size="11" value="" /></td>
		<td>' . _('To') . ':</td>
		<td><input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="ToDate" maxlength="10" size="11" value="" /></td>
	</tr>';

echo '</table><div class="centre"><input type="submit" name="Search" value="查找"> </div>'
    . '</br>';



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
        _('泥芯工单领模具') . '" alt="" />' . ' ' . $Title . '</p>';
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
			<th>泥芯工单号</th>
			<th>' . _('半成品泥芯料号') . '</th>
			<th>半成品泥芯名称</th>
			<th>' . _('成品料号') . '</th>
			<th>' . _('预计开工日期') . '</th>
			<th>' . _('预计完工日期') . '</th>
            <th>' . _('本工单开工量') . '</th>
            <th>选择</th>
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
	<td>' . $myrow['loamcoreid'] . '</td>
			<td>' . $myrow['loamcorename'] . '</td>
			<td>  ' . $myrow['stockid'] . '</td>
			<td>' . date("Y-m-d", $myrow['scheduled_start_date']) . '</td>
            <td>' . date("Y-m-d", $myrow['scheduled_completion_date']) . '</td>
			<td>' . $myrow['start_quantity'] . '  </td>
			<td><input name="a" type="radio" value="选择" class="coupons" rel="' . $myrow['wip_entity_name'] . ':' . $myrow['stockid'] . ':' . $myrow['loamcoreid'] . ':' . $myrow['start_quantity'] . ' :' . date("Y-m-d", $myrow['scheduled_start_date']) . ':' . date("Y-m-d", $myrow['scheduled_completion_date']) . ':' . date("Y-m-d", $myrow['creation_date']) . '
			"></td>
			</tr>
			</form>
	';
            /*
             *  loamcoreid,
                                loamcorename,
                                stockid,
                                remark,
                                iphone,
                                scheduled_start_date,
                                scheduled_completion_date，
                                start_quantity,wip_entity_name
             */
            $i++;
            $RowIndex++;
        }
        //END WHILE LIST LOOP
        echo '</table>';



} //end if record deleted no point displaying form to add record

include('includes/footer.inc');
echo '';
?>
<link rel="shortcut icon" href="/JXC/favicon.ico"/>
<link rel="icon" href="/JXC/favicon.ico"/>
<meta http-equiv="Content-Type" content="application/html; charset=utf-8"/>
<link href="/JXC/css/xenos/default.css" rel="stylesheet" type="text/css"/>
<script type="text/javascript" src ="/JXC/javascripts/miscfunctions.js"></script>
<script type="text/javascript" src ="/JXC/javascripts/wdatepicker.js"></script>
<script src="/JXC/javascript/jquery-1.10.2.min.js"></script>
<script src="/JXC/javascript/bootstrap.min.js"></script>
<script src="/JXC/javascript/jquery.dataTables.js"></script>
<script src="/JXC/javascript/jquery.livequery.js"></script>
<script type="text/javascript">
    $(document).ready(function(){
        var api = frameElement.api, W = api.opener;
        api.button({
            id:'valueOk',
            name:'确定',
            focus: true,
            callback:ok
        });

        function ok()
        {
            switch ($('#cat').val()){
                case 'buliao':
                    W.document.getElementById('wip_entity_name').value = $("label#wip_entity_name").text();
                    W.document.getElementById('stockid').value = $("label#stockid").text();
                    W.document.getElementById('loamcoreid').value = $("label#loamcoreid").text();
                    W.document.getElementById('start_quantity').value = $("label#start_quantity").text();
                    W.document.getElementById('scheduled_start_date').value = $("label#scheduled_start_date").text();
                    W.document.getElementById('scheduled_completion_date').value = $("label#scheduled_completion_date").text();
                    W.document.getElementById('creation_date').value = $("label#creation_date").text();



                    $("#xianshi").css("display","block");
                    break;
                default :
                    alert('Data Post Error');
            }
        };


        $(".coupons").livequery("click", function() {
            var rel = this.getAttribute('rel');
            c = rel.split(":");
            $("#wip_entity_name").text(c[0]);
            $("#stockid").text(c[1]);
            $("#loamcoreid").text(c[2]);
            $("#start_quantity").text(c[3]);
            $("#scheduled_start_date").text(c[4]);
            $("#scheduled_completion_date").text(c[5]);
            $("#creation_date").text(c[6]);
        });



    });
</script>

<div style="display:none">
    <p><label class="text-info">wip_entity_name:</label>　<label id="wip_entity_name"></label></p>
</div>
<div style="display:none">
    <p><label class="text-info">stockid:</label>　<label id="stockid"></label></p>
</div>

<div style="display:none">
    <p><label class="text-info">loamcoreid:</label>　<label id="loamcoreid"></label></p>
</div>
<div style="display:none">
    <p><label class="text-info">start_quantity</label>　<label id="start_quantity"></label></p>
</div>
<div style="display:none">
    <p><label class="text-info">scheduled_start_date</label>　<label id="scheduled_start_date"></label></p>
</div>
<div style="display:none">
    <p><label class="text-info">scheduled_completion_date</label>　<label id="scheduled_completion_date"></label></p>
</div>
<div style="display:none">
    <p><label class="text-info">creation_date</label>　<label id="creation_date"></label></p>
</div>


<div style="display:none">
    <p><input type="hidden" name="cat" id="cat" value="<?=$cat?>"/></p>
    <p><input type="hidden" name="fwValue" value="<?=$fwValue?>" id="fwValue"/></p>
</div>
