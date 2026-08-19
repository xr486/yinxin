<?php
 ob_start();
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include('includes/session.inc');
$Title = _('退货单签核');
$ViewTopic= '退货单签核';
$BookMark = '退货单签核';

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
    $sql = "SELECT receipt_num, a.delivery_date,
a.status , receive_remark, a.creation_date, a.vendor_code, b.vendor_name,a.receive_remark,a.created_by
FROM po_rcv_receipt_all a, vendors b
WHERE status in( 'INPROCESS','REJECTED')
AND a.vendor_code = b.vendor_code "	;
    // echo $_POST['receipt_num_from'];
    if(isset($_POST['receipt_num_from']) and $_POST['receipt_num_from'] != ''){
        $sql = $sql." and a.receipt_num >=  '" .$_POST['receipt_num_from']. "' " ;
 
    }
    if(isset($_POST['receipt_num_to']) and $_POST['receipt_num_to'] != ''){
         $sql = $sql." and a.receipt_num <=  '" .$_POST['receipt_num_from']. "' ";
    }
    if (isset($_POST['vendor_name']) and $_POST['vendor_name'] != '') {
        $sql = $sql . " and b.vendor_name " . LIKE . " '%" . $_POST['vendor_name'] . "%' ";
    }
    if (isset($_POST['vendor']) and $_POST['vendor'] != '') {
        $sql = $sql . " and a.vendor_code =  '" . $_POST['vendor'] . "'";
    }
     if (empty($_POST['FromDate']) == 0) {
        $SQL_FromDate = strtotime($_POST['FromDate']);
        $sql .= " and a.delivery_date >= '" . $SQL_FromDate . "' ";
    }
    if (empty($_POST['ToDate']) == 0) {
        $SQL_ToDate = strtotime($_POST['ToDate']) + 86400;
        //echo $SQL_ToDate;
        $sql .= " and a.delivery_date <='" . $SQL_ToDate . "' ";
    }
    $result = DB_query($sql,$db);
    if (DB_num_rows($result)==0) {
        unset($result);
        prnMsg(_('找不到该退货单，请重新输入条件查询！') ,'error');
    }
}


echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('查找待签核退货单') . '</p>';
echo '<table cellpadding="3" class="selection">';

echo '<tr><td >' . _('退货单起') . ':</td><td>';
echo '<input type="text" name="receipt_num_from" value="' . $_POST['receipt_num_from'] . '" size="20" maxlength="25" /></td>';
echo '<td>' . _('退货单止') . ':</td>
	<td>';
echo '<input type="text" name="receipt_num_to" value="' . $_POST['receipt_num_to'] . '" size="20" maxlength="25" /></td>';
 
echo ' <td >' . _('供应商名称') . ':</td><td>';
echo '<input type="text" name="vendor_name" value="' . $_POST['vendor_name'] . '" size="20" maxlength="25" /></td>';
echo '</tr>';
echo '<td>' . _('或者 供应商代号') . ':</td>
	<td>';
echo '<input type="text" name="vendor" value="' . $_POST['vendor'] . '" size="20" maxlength="25" /></td>';

if (!isset($_POST['FromDate'])) {
    $_POST['FromDate'] = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 30, date("Y")));
}
if (!isset($_POST['ToDate'])) {
    $_POST['ToDate'] = Date('Y-m-d');
}
echo '<td>' . '需求日' . _('From') . ':</td>
		<td><input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="FromDate" maxlength="10" size="11" value="" /></td>
		<td>' . _('To') . ':</td>
		<td><input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="ToDate" maxlength="10" size="11" value="" /></td>
	</tr>';

echo '</table><div class="centre"><input type="submit" name="Search" value="查找"> </div>'
. '</br>';

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
                    <th class="ascending" width = 100>' . _('退货单号') . '</th>
                    <th class="ascending"width = 80>' . _('状态') . '</th>
					 <th class="ascending"width = 120>' . _('供应商编号') . '</th>
                        <th class="ascending"width = 190>' . _('供应商名称') . '</th> 
                    <th class="ascending"width = 350>' . _('备注') . '</th>                  
                    <th class="ascending"width = 150>' . _('退货时间') . '</th>
                        <th class="ascending"width = 80>' . _('下单人员') . '</th>
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
 
			if($myrow['status'] == 'INPROCESS') {
                            $v_status = '待签核';
                        }elseif($myrow['status'] == 'APPROVED'){
                            $v_status = '已签核';
                        }elseif($myrow['status'] == 'REJECTED'){
                            $v_status = '已拒签';
                        }else{
                             $v_status = '已取消';
                        }

			echo '  <td><a href="' . $RootPath . '/ReturnApprovedCheck2.php?Updatereceipt_num=' . $myrow['receipt_num'] . '">' . $myrow['receipt_num'] . '</td>
				<td>' . $v_status . '</td>
				<td>' . $myrow['vendor_code'] . '</td>  
                 <td>' . $myrow['vendor_name'] . '</td>   
				<td>' . $myrow['receive_remark'] . '</td>                                    
				<td>' . date('Y-m-d  H:i:s', $myrow['creation_date']) . '</td>
                <td>' . $myrow['created_by'] . '</td>
				
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