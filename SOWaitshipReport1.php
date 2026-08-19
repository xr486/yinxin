<?php
 ob_start(); 
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include('includes/session.inc');
$Title = _('待出货明细报表');
$ViewTopic= '待出货明细报表';
$BookMark = '待出货明细报表';

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
    $sql = "SELECT
       s.*,
       h.customer_code,h.need_date,h.status,
       c.*,d.item_name,d.item_desc
FROM
    so_lines_all s,
    so_headers_all h,
    customers c,sf_item_no d
WHERE  s.order_number = h.order_number 
AND c.customer_code = h.customer_code 
and h.status = 'APPROVED'
and s.stockid=d.item_no";
      if (isset($_POST['SO_from']) and $_POST['SO_from'] != '') { 
		$sql = $sql . " and a.order_number " . LIKE . " '%" . $_POST['SO_from'] . "%' ";
    }
    if (isset($_POST['item_no']) and $_POST['item_no'] != '') { 
		$sql = $sql . " and d.item_no " . LIKE . " '%" . $_POST['item_no'] . "%' ";
    }
	if (isset($_POST['item_name']) and $_POST['item_name'] != '') { 
		$sql = $sql . " and d.item_name " . LIKE . " '%" . $_POST['item_name'] . "%' ";
    }
	if (isset($_POST['item_desc']) and $_POST['item_desc'] != '') { 
		$sql = $sql . " and d.item_desc " . LIKE . " '%" . $_POST['item_desc'] . "%' ";
    }
    if (isset($_POST['customer_name']) and $_POST['customer_name'] != '') {
        $sql = $sql . " and c.customer_name " . LIKE . " '%" . $_POST['customer_name'] . "%' ";
    }

	if (isset($_POST['customer_code']) and $_POST['customer_code'] != '') {
        $sql = $sql . " and c.customer_code " . LIKE . " '%" . $_POST['customer_code'] . "%' ";
    }
  
    if (empty($_POST['FromDate']) == 0) {
        $SQL_FromDate = strtotime($_POST['FromDate']);
        $sql .= " and h.need_date >= '" . $SQL_FromDate . "' ";
    }
    if (empty($_POST['ToDate']) == 0) {
        $SQL_ToDate = strtotime($_POST['ToDate']) + 86400;
        //echo $SQL_ToDate;
        $sql .= " and h.need_date <='" . $SQL_ToDate . "' ";
    }
   
      $sql .=" order by h.order_number,s.line DESC";
    
  
    $result = DB_query($sql,$db);
    if (DB_num_rows($result)==0) {
        unset($result);
        prnMsg(_('找不到订单明细，请重新输入条件查询！') ,'error');
    }
}


echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('查找') . '</p>';
echo '<table cellpadding="3" class="selection">';

 
echo '<tr><td >' . _('订单') . ':</td><td>';
echo '<input type="text" name="SO_from" value="' . $_POST['SO_from'] . '" size="20" maxlength="25" /></td>';
echo '<td>' . _('料号') . ':</td>
	<td>';
echo '<input type="text" name="item_no" value="' . $_POST['item_no'] . '" size="20" maxlength="25" /></td>';
echo '<td>' . _('料号名称') . ':</td>
	<td>';
echo '<input type="text" name="item_name" value="' . $_POST['item_name'] . '" size="20" maxlength="25" /></td>';
echo '<td>' . _('规格型号') . ':</td>
	<td>';
echo '<input type="text" name="item_desc" value="' . $_POST['item_desc'] . '" size="20" maxlength="25" /></td>';
echo '</tr>';
echo '<tr><td >' . _('客户代码') . ':</td><td>';
echo '<input type="text" name="customer_code" value="' . $_POST['customer_code'] . '" size="10" maxlength="25" /></td>';
 
echo '<td >' . _('客户名称') . ':</td><td>';
echo '<input type="text" name="customer_name" value="' . $_POST['customer_name'] . '" size="20" maxlength="25" /></td>';
 

 
if (!isset($_POST['FromDate'])) {
    $_POST['FromDate'] = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 30, date("Y")));
}
if (!isset($_POST['ToDate'])) {
    $_POST['ToDate'] = Date('Y-m-d');
}
echo '<td>' . '需求日' . _('From') . ':</td>
		<td><input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="FromDate" maxlength="10" size="11" value="' . $_POST['FromDate'] . '" /></td>
		<td>' . _('To') . ':</td>
		<td><input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="ToDate" maxlength="10" size="11" value="' . $_POST['ToDate'] . '" /></td>
	</tr>';
	
echo '</table><div class="centre"><input type="submit" name="Search" value="查找"></div>';

if (isset($_POST['Search']) and isset($result) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])){
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
                    <th class="ascending" >' . _('订单号') . '</th>
                    <th width = 60>' . _('状态') . '</th>
                    <th   width = 20>' . _('行') . '</th>
                    <th class="ascending"width = 90>' . _('客户代码') . '</th> 
                    <th class="ascending"width = 120>' . _('成品料号') . '</th>   
                    <th class="ascending"width = 120>' . _('产品名称') . '</th>  
                    <th class="ascending"width = 120>' . _('规格型号') . '</th>   
                    <th class="ascending"width = 50>' . _('单位') . '</th> 
                    <th class="ascending"width = 90>' . _('数量') . '</th>  
                    <th class="ascending"width = 90>' . _('已出货量') . '</th>  
                    <th class="ascending"width = 90>' . _('待出货量') . '</th> 
                    <th class="ascending"width = 90>' . _('指导价') . '</th> 
                    <th class="ascending"width = 90>' . _('其它费用') . '</th> 
                    <th class="ascending"width = 100>' . _('销售单价') . '</th> 
                    <th class="ascending"width = 100>' . _('金额') . '</th>                        
                  
                    <th class="ascending"width = 100>' . _('需求日期') . '</th>
            </tr>';
    $k = 0; //row counter to determine background colour
    $RowIndex = 0;
    


    if (DB_num_rows($result) <> 0) {
        DB_data_seek($result, ($_POST['PageOffset'] - 1) * 10);
        $i = 0; //counter for input controls
        while (($myrow = DB_fetch_array($result)) AND ($RowIndex <> 10)) {
			if ($k == 1) {
				echo '<tr class="EvenTableRows">';
				$k = 0;
			} else {
				echo '<tr class="OddTableRows">';
				$k = 1;
			}
			 echo '  
                <td><a href="' . $RootPath . '/SearchSO2.php?Updateorder_number=' . $myrow['order_number'] . '" target="view_window">' . $myrow['order_number'] . '</td> 
				<td>' .  $v_status . '</td>
                <td>' . $myrow['line'] . '</td> 
                <td><a href="AddCustomers.php?UpdateCustomerCode='.$myrow['customer_code'].'" target="view_window">' . $myrow['customer_code'] . '</td>  
                <td>' . $myrow['stockid'] . '</td> 
                <td>' . $myrow['item_name'] . '</td> 
                <td>' . $myrow['item_desc'] . '</td> 
                <td>' . $myrow['uom'] . '</td> 
                <td>' . $myrow['quantity'] . '</td>  
                <td>' . $myrow['quantity_shiped'] . '</td> 
                <td>' . ($myrow['quantity']-$myrow['quantity_shiped'] ). '</td> 
                <td>' . $myrow['zhidao_price'] . '</td>  
                <td>' . $myrow['other_price'] . '</td>  
                <td>' . $myrow['price'] . '</td> 
                <td>' . $myrow['line_amount'] . '</td> 
                <td>' . date('Y-m-d',$myrow['need_date']) . '</td> 
                
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
                        } 
                        else {
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