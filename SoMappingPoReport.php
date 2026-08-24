<?php
 ob_start();
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include('includes/session.inc');
$Title = _('业务订单对应采购单明细查询');
$ViewTopic= '业务订单对应采购单明细查询';
$BookMark = '业务订单对应采购单明细查询';

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
    $sql = "select a.so_num,a.so_line,a.creation_date ,a.created_by,a.po_num,a.po_line,a.assign_qty,b.customer_code,d.vendor_code,c.	item_no,c.item_name,c.item_desc,c.units 
 from so_po_mapping a, so_headers_all b,sf_item_no c,po_headers_all d   where a.item_no=c.item_no AND a.po_num=d.po_num
 and a.so_num=b.order_number    ";    
   if(isset($_POST['po_num']) and $_POST['po_num'] != ''){ 
        $sql = $sql." and a.po_num ".LIKE." '%".$_POST['po_num']."%' "; 
    }
	if(isset($_POST['item_no']) and $_POST['item_no'] != ''){ 
        $sql = $sql." and a.item_no ".LIKE." '%".$_POST['item_no']."%' "; 
    }
	if(isset($_POST['item_name']) and $_POST['item_name'] != ''){ 
        $sql = $sql." and c.item_name ".LIKE." '%".$_POST['item_name']."%' "; 
    }

	if(isset($_POST['customer_code']) and $_POST['customer_code'] != ''){ 
        $sql = $sql." and b.customer_code ".LIKE." '%".$_POST['customer_code']."%' "; 
    }
	if(isset($_POST['vendor_code']) and $_POST['vendor_code'] != ''){ 
        $sql = $sql." and d.vendor_code ".LIKE." '%".$_POST['vendor_code']."%' "; 
    }

	if(isset($_POST['order_number']) and $_POST['order_number'] != ''){ 
        $sql = $sql." and a.so_num ".LIKE." '%".$_POST['order_number']."%' "; 
    } 
	 

     if (empty($_POST['FromDate']) == 0) {
        $SQL_FromDate = strtotime($_POST['FromDate']);
        $sql .= " and a.creation_date >= '" . $SQL_FromDate . "' ";
    }
    if (empty($_POST['ToDate']) == 0) {
        $SQL_ToDate = strtotime($_POST['ToDate']) + 86400;
        //echo $SQL_ToDate;
        $sql .= " and a.creation_date <='" . $SQL_ToDate . "' ";
    }
    $result = DB_query($sql,$db);
    if (DB_num_rows($result)==0) {
        unset($result);
        prnMsg(_('找不到该请购单，请重新输入条件查询！') ,'error');
    }
}


echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('查找业务订单对应采购订单') . '</p>';
echo '<table cellpadding="3" class="selection">';
echo '<tr><td>' . _('供应商代码') . ':</td> ';
echo '<td><input type="text" name="vendor_code" value="' . $_POST['vendor_code'] . '" size="20" maxlength="25" /></td>';
echo '<td >' . _('采购单') . ':</td><td>';
echo '<input type="text" name="po_num" value="' . $_POST['po_num'] . '" size="20" maxlength="25" /></td>';
echo '<td>' . _('客户代码') . ':</td> ';
echo '<td><input type="text" name="customer_code" value="' . $_POST['customer_code'] . '" size="20" maxlength="25" /></td>';
echo '<td>' . _('业务订单') . ':</td> ';
echo '<td><input type="text" name="order_number" value="' . $_POST['order_number'] . '" size="20" maxlength="25" /></td>';


echo '</tr>';
if (!isset($_POST['FromDate'])) {
    $_POST['FromDate'] = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 30, date("Y")));
}
if (!isset($_POST['ToDate'])) {
    $_POST['ToDate'] = Date('Y-m-d');
}
 
echo '<td>' . '匹配日期' . _('From') . ':</td>
		<td><input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="FromDate" maxlength="10" size="11" value="' . $_POST['FromDate'] . '" /></td>
		<td>' . _('To') . ':</td>
		<td><input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="ToDate" maxlength="10" size="11" value="' . $_POST['ToDate'] . '" /></td>
	 ';
 echo '<td>' . _('料号') . ':</td> ';
echo '<td><input type="text" name="item_no" value="' . $_POST['item_no'] . '" size="20" maxlength="25" /></td>';
echo '<td>' . _('料号名称') . ':</td> ';
echo '<td><input type="text" name="item_name" value="' . $_POST['item_name'] . '" size="20" maxlength="25" /></td>';

echo '</table><div class="centre"><input type="submit" name="Search" value="查找"> </div>'
. '</br>';

if (isset($_POST['Search']) and isset($result) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])){
    $ListCount = DB_num_rows($result);
    $ListPageMax = ceil($ListCount / 15);
    
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

    echo '<tr> <th class="ascending" width = 100>' . _('业务订单') . '</th>
                    <th class="ascending"width = 60>' . _('订单行') . '</th>
                    <th class="ascending"width = 80>' . _('客户') . '</th>
                    <th class="ascending"width = 120>' . _('采购订单') . '</th>
                    <th  width = 70>' . _('采购单行') . '</th>
                    <th  width =  60>' . _('供应商') . '</th>
					<th   width = 70>' . _('分配数量') . '</th> 
					 <th width =160>' . '分配日期' . '</th>
                                        <th width =50 >' . '人员' . '</th>
                                        <th  width =150>' . '料号' . '</th>
                                        <th  width =150>' . '料号名称' . '</th>
                                        <th width =150 >' . '规格型号' . '</th> 
                                        <th width =50 >' . '单位' . '</th>
				
            </tr>';
    $k = 0; //row counter to determine background colour
    $RowIndex = 0;
    


    if (DB_num_rows($result) <> 0) {
        DB_data_seek($result, ($_POST['PageOffset'] - 1) * 15);
        $i = 0; //counter for input controls
        while (($myrow = DB_fetch_array($result)) AND ($RowIndex <> 15)) {
			if ($k == 1) {
				echo '<tr class="EvenTableRows">';
				$k = 0;
			} else {
				echo '<tr class="OddTableRows">';
				$k = 1;
			}
			if($myrow['status'] == 'INPROCESS') {
                            $v_status = '待签核';
                        }elseif($myrow['status'] == 'APPROVED'){
                            $v_status = '已签核';
                        }elseif($myrow['status'] == 'REJECTED'){
                            $v_status = '已拒签';
                        }else{
                             $v_status = '已取消';
                        }
			
			echo '  <td><a href="' . $RootPath . '/SearchSO4.php?Updateorder_number=' . $myrow['so_num'] . '" target="_blank" >' . $myrow['so_num'] . '</td>	
				<td>' . $myrow['so_line'] . '</td>
				<td>' . $myrow['customer_code'] . '</td> 
				 <td><a href="' . $RootPath . '/SearchPO4.php?Updatepo_num=' . $myrow['po_num'] . '" target="_blank" >' . $myrow['po_num'] . '</td>	
				<td>' . $myrow['po_line'] . '</td> 
				<td>' . $myrow['vendor_code'] . '</td> 
				<td>' . $myrow['assign_qty'] . '</td> 
				<td>' . date('Y-m-d H:i:s', $myrow['creation_date']) . '</td>
				<td>' . $myrow['created_by'] . '</td>  
				<td>' . $myrow['item_no'] . '</td> 
				<td>' . $myrow['item_name'] . '</td> 
				<td>' . $myrow['item_desc'] . '</td>  
				<td>' . $myrow['units'] . '</td>   
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