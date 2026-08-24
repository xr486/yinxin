<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include('includes/session.inc');
$Title = _('查询订单明细');
$ViewTopic = '查询订单明细';
$BookMark = '查询订单明细';

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
    $sql = 'SELECT
                order_id,
                order_number,
                customer_number,
                customer_name,
                sales_man_name,item_name,order_quantity,schedule_ship_date,doc_path,unit_price,
				urgent_flag,purchase_flag,purchase_remarks,invoice_flag,express_flag,order_desc
           from sf_orders_all 
	  WHERE 1=1  ';
   
if (isset($_POST['CustomerID']) and $_POST['CustomerID']!= '') {
			$sql = $sql." and customer_number like '%".$_POST['CustomerID']."%' ";
		}
		if (isset($_POST['CustomerName']) and $_POST['CustomerName']!= '') {
			$sql = $sql." and customer_name like '%".$_POST['CustomerName']."%' ";
		}
		if (isset($_POST['order_number']) and $_POST['order_number']!= '') {
			$sql = $sql." and order_number like '%".$_POST['order_number']."%' ";
		}
        if (isset($_POST['sales_man_name']) and $_POST['sales_man_name']!= '') {
			$sql = $sql." and sales_man_name like '%".$_POST['sales_man_name']."%' ";
		}

    $sql .= " ORDER BY order_number desc ";
    $result = DB_query($sql, $db);
    if (DB_num_rows($result) == 0) {
        unset($result);
        prnMsg(_('找不到数据，请重新输入条件查询！'), 'error');
    }
}


echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('查询订单') . '</p>';
echo '<table cellpadding="3" class="selection">';

echo '<tr>	
			<td>' . '订单号码' . ':</td>
			<td><input  pattern="(?!^ *$)[^+<>-]{1,}" type="text" name="order_number" value="' . $_POST['order_number'] . '" /></td> 
			 ';
echo ' 
			<td>' . '客户代号：' . ':</td>
			<td><input   type="text" name="CustomerID" value="' . $_POST['CustomerID'] . '" /></td> 
			</tr>';
echo '<tr>	
			<td>' . '客户名称：' . ':</td>
			<td><input   type="text" name="CustomerName" value="' . $_POST['CustomerName'] . '" /></td> 
			 ';
echo ' 	
			<td>' . '业务人员：' . ':</td>
			<td><input   type="text" name="sales_man_name" value="' . $_POST['sales_man_name'] . '" /></td> 
			</tr>';		

echo '</table>'
 . '<div class="centre"><input type="submit" name="Search" value="查找"></div>';

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
    echo '<br />
                    <table cellpadding="2" class="selection">';

    echo '<tr>
                    <th class="ascending">' . _('订单') . '</th>
                    <th class="ascending">' . _('业务员') . '</th>
                    <th class="ascending">' . _('客户代码') . '</th>
                    <th class="ascending">' . _('客户名称') . '</th>
					<th class="ascending">' . _('项目名称') . '</th>
					<th class="ascending">' . _('订单数量') . '</th>
					<th class="ascending">' . _('文件路径') . '</th>
					<th class="ascending" width =60>' . _('紧急') . '</th>
					<th class="ascending"  width =140>' . _('采购钢网') . '</th>
					<th class="ascending">' . _('钢网注意事项') . '</th>
					<th class="ascending" width =60>' . _('开票') . '</th>
					<th class="ascending" width =60>' . _('快递') . '</th>
					<th class="ascending">' . _('业务信息备注') . '</th>
					<th class="ascending">' . _('需求日期') . '</th>
					 
                   
            </tr>';
    $k = 0; //row counter to determine background colour
    $RowIndex = 0;

    if (DB_num_rows($result) <> 0) {
        DB_data_seek($result, ($_POST['PageOffset'] - 1) * 10);// $_SESSION['DisplayRecordsMax']
        $i = 0; //counter for input controls
        while (($myrow = DB_fetch_array($result)) AND ($RowIndex <> 10)) {//$_SESSION['DisplayRecordsMax']
            if ($k == 1) {
                echo '<tr class="EvenTableRows">';
                $k = 0;
            } else {
                echo '<tr class="OddTableRows">';
                $k = 1;
            }
            echo '  <td>' . $myrow['order_number'] . '</td>';
                                 echo '<td width =90>' . $myrow['sales_man_name'] . '</td>';
                                 
                                 echo '<td><a href="' . $RootPath . '/AddCustomers.php?CustomerNum=' . $myrow['customer_number'] . '">' . $myrow['customer_number'] . '</td>'
;
								 echo '<td width =250>' . $myrow['customer_name'] . '</td>';
								 echo '<td width =200>' . $myrow['item_name'] . '</td>';
								 echo '<td width =100>' . $myrow['order_quantity'] . '</td>';
								 echo '<td width =140>' . $myrow['doc_path'] . '</td>';								 
								 echo '<td >' . $myrow['urgent_flag'] . '</td>';
								 echo '<td >' . $myrow['purchase_flag'] . '</td>';
								 echo '<td width =200>' . $myrow['purchase_remarks'] . '</td>';
								 echo '<td >' . $myrow['invoice_flag'] . '</td>';
								 echo '<td>' . $myrow['express_flag'] . '</td>';
								 echo '<td width =200>' . $myrow['order_desc'] . '</td>';
                                 echo '<td width = 100>' . date('Y-m-d', $myrow['schedule_ship_date']) . '</td>   
								 
 
			</tr>';
            $i++;
            $RowIndex++;
            //end of page full new headings if
        } //end loop through customers
        echo '</table>';
    }

    if (isset($ListPageMax) AND $ListPageMax > 1) {
        echo '<br /><div class="centre">&nbsp;&nbsp; ' .'第' . '' . $_POST['PageOffset'] . ' ' . _('页，共') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ': ';
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


