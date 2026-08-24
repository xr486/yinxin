<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include('includes/session.inc');
$Title = '采购单查询';
$ViewTopic = '采购单查询';
$BookMark = '采购单查询';

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
    $sql = 'select order_id,
               order_number,
               purchase_qty,
               purchase_amount purchase_price,
               supplier_name,
              customer_name,purchase_remarks,
               demand_date,
               purchase_desc,
	       receive_qty
          from sf_orders_all a where purchase_qty is not null';
    if (empty($_POST['order_number']) == 0) {
        $sql .= " AND a.order_number= '" . $_POST['order_number'] . "'";
    }
    if (empty($_POST['customer_name']) == 0) {
        $sql .= " AND a.supplier_name like '%" . $_POST['customer_name'] . "%'";
    }
    //$sql =  $sql. ' limit '. $_POST['PageOffset'].',' .   3;
    $result = DB_query($sql, $db);
    if (DB_num_rows($result) == 0) {
        unset($result);
        prnMsg(_('没有找到采购单，请重新输入条件查询!'), 'error');
    }
}


echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('查询采购单') . '</p>';

echo '<table cellpadding="3" class="selection">	<tr>	
			<td>' . '订单号' . ':</td>
			<td><input   type="text" name="order_number"  /></td>
                         <td>' . '供应商名称' . ':</td>
			<td><input   type="text" name="customer_name"  /></td>
                         '
 . '</tr><tr>';


//$_SESSION['DefaultDateFormat'],
if (!isset($_POST['FromDate'])) {
    $_POST['FromDate'] = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 30, date("Y")));
}
if (!isset($_POST['ToDate'])) {
    $_POST['ToDate'] = Date('Y-m-d');
}
echo '<td>' . '采购日期' . _('From') . ':</td>
		<td><input type="text" onfocus="WdatePicker()"  name="FromDate" maxlength="10" size="11" value="' . $_POST['FromDate'] . '" /></td>
		<td>' . _('To') . ':</td>
		<td><input type="text" onfocus="WdatePicker()"  name="ToDate" maxlength="10" size="11" value="' . $_POST['ToDate'] . '" /></td>
	</tr></table><div class="centre"><input type="submit" name="Search" value="查找"></div>';

if (isset($_POST['Search']) and isset($result) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])) {
    $ListCount = DB_num_rows($result);
    $ListPageMax = ceil($ListCount / 10);//$_SESSION['DisplayRecordsMax']

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
        echo '<br /><div class="centre">&nbsp;&nbsp;第' . $_POST['PageOffset'] . ' ' . _('页，共') . ' ' . $ListPageMax . ' ' . _('页') . '. ' . _('Go to Page') . ': ';
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
                   
	                                <th >' . '订单号' . '</th>
                                        <th >' . '客户' . '</th>
					<th >' . '供应商名称' . '</th>
                                        <th width = 80>' . '金额' . '</th>
					<th width = 80>' . '採購数量' . '</th>
					<th width = 80 >' . '入庫數量' . '</th>
                                        <th width = 90>' . '需求日' . '</th>
                                        <th width = 100 >' . '注意事项' . '</th>
					<th width = 100 >' . '描述' . '</th>
            </tr>';
    $k = 0; //row counter to determine background colour
    $RowIndex = 0;

    if (DB_num_rows($result) <> 0) {
        DB_data_seek($result, ($_POST['PageOffset'] - 1) * 10);//$_SESSION['DisplayRecordsMax']
        $i = 0; //counter for input controls
        while (($myrow = DB_fetch_array($result)) AND ( $RowIndex <> 10)) {//$_SESSION['DisplayRecordsMax']
            if ($k == 1) {
                echo '<tr class="EvenTableRows">';
                $k = 0;
            } else {
                echo '<tr class="OddTableRows">';
                $k = 1;
            }
            echo '<td ><font color="red">' . $myrow['order_number'] . '</font></td>';
            echo '<td ><font">' . $myrow['customer_name'] . '</font></td>';
            
		echo '<td width=200>' . $myrow['supplier_name'] . '</td>';
        echo '<td width=100>' . $myrow['purchase_price'] . '</td>';
        echo '<td width=100>' . $myrow['purchase_qty'] . '</td>
		 <td width=100>' . $myrow['receive_qty'] . '   </td>
               <td width=100>' . date('Y-m-d',$myrow['demand_date']) . '   </td>
               <td width=100>' . $myrow['purchase_remarks'] . '   </td>
               <td width=100>' . $myrow['purchase_desc'] . '   </td>
             <input type="hidden" name="order_id[]" value="' . $myrow['order_id'] . '" />
			</tr>';
            $i++;
            $RowIndex++;
            //end of page full new headings if
        } //end loop through customers
        echo '</table>';
        echo '<input type="hidden" name="JustSelectedACustomer" value="Yes" />';
    }

    if (isset($ListPageMax) AND $ListPageMax > 1) {
        echo '<br /><div class="centre">&nbsp;&nbsp;第' . $_POST['PageOffset'] . '页， ' . _('共') . ' ' . $ListPageMax . ' ' . _('页') . '. ' . _('Go to Page') . ': ';
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
