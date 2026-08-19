<?php

ob_start();
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include('includes/session.inc');
$Title = _('客户退货明细查询');
$ViewTopic = '客户退货明细查询';
$BookMark = '客户退货明细查询';

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
	a.delivery_num,
	a.status,
	b.customer_code,
	b.customer_name,
	c.narrative,
	c.delivery_date,
	a.delivery_line,
	a.stockid,
	d.item_desc,
	a.price,
	a.uom, 
	a.so_order_number,
 
	a.line_amount,
	a.delivery_quantity ,d.item_spec,a.subinventory_code
FROM
	so_delivery_all a,
	customers b,
	so_delivery_headers_all c, 
	sf_item_no d   
WHERE
	1 = 1
AND a.customer_code = b.customer_code 
AND c.customer_code = b.customer_code 
and a.delivery_num=c.delivery_num
and a.stockid=d.item_no
AND a.line_amount<0
";

    if (isset($_POST['SO_from']) and $_POST['SO_from'] != '') {
        $sql = $sql . " and a.so_order_number " . LIKE . " '%" . $_POST['SO_from'] . "%' ";
    }
  
     if (isset($_POST['DE_from']) and $_POST['DE_from'] != '') {
         $sql = $sql . " and a.delivery_num " . LIKE . " '%" . $_POST['DE_from'] . "%' ";
    }
   if (isset($_POST['subinventory_code']) and $_POST['subinventory_code'] != '') {
         $sql = $sql . " and a.subinventory_code " . LIKE . " '%" . $_POST['subinventory_code'] . "%' ";
    }
	if (isset($_POST['Stockid_from']) and $_POST['Stockid_from'] != '') {
       $sql = $sql . " and c.stockid " . LIKE . " '%" . $_POST['Stockid_from'] . "%' ";
    }
    if (isset($_POST['item_desc']) and $_POST['item_desc'] != '') {
        $sql = $sql . " and d.item_desc " . LIKE . " '%" . $_POST['item_desc'] . "%' ";
    }
    if (isset($_POST['customer_name']) and $_POST['customer_name'] != '') {
        $sql = $sql . " and b.customer_name " . LIKE . " '%" . $_POST['customer_name'] . "%' ";
    }
	if (isset($_POST['customer_code']) and $_POST['customer_code'] != '') {
        $sql = $sql . " and b.customer_code " . LIKE . " '%" . $_POST['customer_code'] . "%' ";
    }
  
    if (empty($_POST['FromDate']) == 0) {
        $SQL_FromDate = strtotime($_POST['FromDate']);
        $sql .= " and c.delivery_date >= '" . $SQL_FromDate . "' ";
    }
    if (empty($_POST['ToDate']) == 0) {
        $SQL_ToDate = strtotime($_POST['ToDate']) + 86400;
        //echo $SQL_ToDate;
        $sql .= " and c.delivery_date <='" . $SQL_ToDate . "' ";
    }
      if($_POST['checkresult']!=""){
        if($_POST['checkresult']=="APPROVED"){
            $sql .= " and a.status = 'APPROVED'";
        }
        if($_POST['checkresult']=="INPROCESS"){
             $sql .=" and a.status = 'INPROCESS'";
        }
        if($_POST['checkresult']=="CANCELED"){
             $sql .= " and a.status = 'CANCELED'";
        }
        if($_POST['checkresult']=="REJECTED"){
             $sql .=" and a.status = 'REJECTED'";
        }
    }

	if($_POST['checkship']!=""){
        if($_POST['checkship']=="2"){
            $sql .= " and c.quantity=c.quantity_shiped+c.quantity_cancelled  ";
        }
        if($_POST['checkship']=="1"){
             $sql .=" and  c.quantity_shiped>0 ";
        }
        if($_POST['checkship']=="0"){
             $sql .= " and c.quantity_shiped = 0 ";
        } 
    }

 
      $sql .=" order by a.delivery_num,a.delivery_line"; 
    $result = DB_query($sql, $db);
    if (DB_num_rows($result) == 0) {
        unset($result);
        prnMsg(_('找不到该退货单，请重新输入条件查询！'), 'error');
    }
}


echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('查找订单退货') . '</p>';
echo '<table cellpadding="3" class="selection">';

echo '<tr><td >' . _('订单') . ':</td><td>';
echo '<input type="text" name="SO_from" value="' . $_POST['SO_from'] . '" size="20" maxlength="25" /></td>';

 echo '<td >' . _('退货单') . ':</td><td>';
echo '<input type="text" name="DE_from" value="' . $_POST['DE_from'] . '" size="20" maxlength="25" /></td>';
echo '<td>' . _('仓库') . ':</td>  <td>';
echo '<input type="text" name="subinventory_code" value="' . $_POST['subinventory_code'] . '" size="20" maxlength="25" /></td>';
echo '<td>' . _('客户代号') . ':</td>  <td>';
echo '<input type="text" name="customer_code" value="' . $_POST['customer_code'] . '" size="20" maxlength="25" /></td>';
echo ' <td >' . _('客户名称') . ':</td><td>';
echo '<input type="text" name="customer_name" value="' . $_POST['customer_name'] . '" size="20" maxlength="25" /></td></tr>';
// <td>' . _('订单签核状态') . ':  <select name="checkresult">

//             echo '<option  selected="selected" value=""></option>';
  
//             echo '<option   value="INPROCESS">待签核</option>';

//             echo '<option   value="APPROVED">已签核</option>';
//             echo '<option   value="CANCELED">已取消</option>';
//             echo '<option   value="REJECTED">已拒签</option>';
//             echo '</select></td> ';
		 
// echo '</tr>';


if (!isset($_POST['FromDate'])) {
    $_POST['FromDate'] = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 30, date("Y")));
}
if (!isset($_POST['ToDate'])) {
    $_POST['ToDate'] = Date('Y-m-d');
}
echo '<td>' . '退货日' . _('From') . ':</td>
		<td><input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="FromDate" maxlength="10" size="11" value="" /></td>
		<td>' . _('To') . ':</td>
		<td><input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="ToDate" maxlength="10" size="11" value="" /></td>
	 <td >' . _('料号') . ':</td><td>';
echo '<input type="text" name="Stockid_from" value="' . $_POST['Stockid_from'] . '" size="20" maxlength="25" /></td>';


echo '<td>' . _('料号名称') . ':</td> ';
echo '<td><input type="text" name="item_desc" value="' . $_POST['item_desc'] . '" size="20" maxlength="25" /></td>';
echo '</tr>';
 

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
// <th  width = 70>' . _('打印') . '</th>
    echo '<tr>
                    <th  width = 100>' . _('订单号码') . '</th>
                   
                    <th   >' . _('客户名称') . '</th> 
                    <th  width = 100>' . _('退货单号') . '</th>
                    <th  width = 100>' . _('退货日期') . '</th>
					 <th width =50 >' . '行' . '</th>
                    <th  width =140>' . '料号' . '</th>
					<th  width =200>' . '产品名称' . '</th>
                    <th  width =140>' . '规格型号' . '</th>
					<th width =100 >' . '数量' . '</th> 
                    <th width =50 >' . '单位' . '</th>
                    <th  width =50>' . '单价' . '</th>
                    <th width =80 >' . '金额' . '</th>
					<th width =100 >' . '行备注' .'</th>                                
                   
            </tr>';
            // <th width =80 >' . '立账数量' . '</th>
                                        // <th width =80 >' . '退货数量' . '</th> 

    $k = 0; //row counter to determine background colour
    $RowIndex = 0;



    if (DB_num_rows($result) <> 0) {
        DB_data_seek($result, ($_POST['PageOffset'] - 1) * 15);
        $i = 0; //counter for input controls
        while (($myrow = DB_fetch_array($result)) AND ( $RowIndex <> 15 )) {
            if ($k == 1) {
                echo '<tr class="EvenTableRows">';
                $k = 0;
            } else {
                echo '<tr class="OddTableRows">';
                $k = 1;
            }
            unset($v_status);
            if ($myrow['status'] == 'INPROCESS') {
                $v_status = '待签';
            } elseif ($myrow['status'] == 'APPROVED') {
                $v_status = '核准';
            } elseif ($myrow['status'] == 'REJECTED') {
                $v_status = '拒绝';
            }   else {
                $v_status = '取消';
            }
            //  <td><a href="' . $RootPath . '/PrintSo.php?OrderNum=' .$myrow['order_number'] . '">' . '打印' .'<img src="%s/css/' . $Theme . '/images/pdf.png" title="' . _('Click for PDF') . '" alt="" /></a></td> 
            echo '  <td><a href="' . $RootPath . '/SearchSOList5.php?Updateorder_number=' . $myrow['so_order_number'] . '">' . $myrow['so_order_number'] . '</td>
		 
	                <td> <a href="' . $RootPath . '/AddCustomers5.php?UpdateCustomerCode=' . $myrow['customer_code'] . '">' . $myrow['customer_code'] . '</td>  
					<td>' .  $myrow['delivery_num']. '</td>
					<td>' . date('Y-m-d', $myrow['delivery_date']) . '</td>
					<td>' . $myrow['delivery_line'] . '</td>
	                <td>' . $myrow['stockid'] . '</td>
				    <td>' . $myrow['item_desc'] . '</td>
				    <td>' . $myrow['item_spec'] . '</td>
                    <td>' . $myrow['delivery_quantity'] . '</td> 
                    <td>' . $myrow['uom'] . '</td>
                    <td>' . $myrow['price'] . '</td>
                    <td>' .$myrow['line_amount'] . '</td>
                    <td>' . $myrow['narrative']  . '</td>                      
                    
                                 ';
                                  // <td>' . $myrow['quantity_billed'] . '</td>

                      // <td>' . $myrow['quantity_cancelled'] . '</td>
		$totalamount = $myrow['line_amount']+$totalamount;
		$totaldelivery_quantity = $myrow['delivery_quantity']+$totaldelivery_quantity;
            echo '
            </tr>';
            $i++;
            $RowIndex++;
            //end of page full new headings if
        } //end loop through vendors
        echo  ' <tr>
            <td>'.合计：.'</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td><span style="color:red; font-size:20px;">'.$totaldelivery_quantity.'</span></td><td></td><td></td>
            <td><span style="color:red; font-size:20px;">'.$totalamount.'</span></td>
            </tr>';
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
    //   echo '<br><div class="centre">
    // <a href="' . $RootPath . '/SaleSumExcelok.php?C1=' .$_POST['customer_name'] .  '">' .'导出Excel' . '
    //     </div>';
}
echo '</div></form>';

include('includes/footer.inc');
