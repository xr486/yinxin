<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include('includes/session.inc');
$Title = _('应付发票匹配采购入库明细');
$ViewTopic= '应付发票匹配采购入库明细';
$BookMark = '应付发票匹配采购入库明细';

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
    $sql = 'select ap_invoice_type,ah.invoice_num,ah.invoice_amount,ah.tax_amount,
	               ah.invoice_date,ah.narrative,ah.vendor_code,ve.vendor_name,
				   ah.created_by,ah.creation_date,ah.currency_code,al.invoice_line,al.amount,al.dis_amount, 
				   al.receipt_num,al.receipt_line
			from  ap_invoice_headers_all ah, 
			      ap_invoice_lines_all al,
			      vendors ve 
			where ve.vendor_code=ah.vendor_code
			  and ah.invoice_num=al.invoice_num';

    if(isset($_POST['vendor_code']) and $_POST['vendor_code'] != ''){
        $sql = $sql." and ve.vendor_code ".LIKE." '%".$_POST['vendor_code']."%' ";
    }
    if(isset($_POST['vendor_name']) and $_POST['vendor_name'] != ''){
        $sql = $sql." and ve.vendor_name ".LIKE." '%".$_POST['vendor_name']."%' ";
    }

	if(isset($_POST['invoice_num']) and $_POST['invoice_num'] != ''){
        $sql = $sql." and ah.invoice_num = '".$_POST['invoice_num']."' ";
    }
	if(isset($_POST['receipt_num']) and $_POST['receipt_num'] != ''){
        $sql = $sql." and al.receipt_num = '".$_POST['receipt_num']."' ";
    }
   
  if (empty($_POST['FromDate'])==0) {
    $SQL_FromDate = strtotime( $_POST['FromDate']);
    //echo $SQL_FromDate;
    $sql .= " and  invoice_date >= '" . $SQL_FromDate . "' ";
}
if (empty($_POST['ToDate'])==0) {
    $SQL_ToDate = strtotime( $_POST['ToDate']) + 86400;
     //echo $SQL_ToDate;
    $sql .= " and  invoice_date <='" . $SQL_ToDate . "' ";
}
 
    $result = DB_query($sql,$db);
    if (DB_num_rows($result)==0) {
        unset($result);
        prnMsg(_('找不到发票信息，请重新输入条件查询！') ,'error');
    }
}


echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('查询条件') . '</p>';
echo '<table cellpadding="3" class="selection">';
echo '<tr><td >' . _('供应商名称') . ':</td><td>';
echo '<input type="text" name="vendor_name" value="' . $_POST['vendor_name'] . '" size="20" maxlength="25" /></td>';
echo ' <td>' . _('供应商代码') . ':</td>
	<td>';
echo '<input type="text" name="vendor_code" value="' . $_POST['vendor_code'] . '" size="20" maxlength="25" /></td>';
echo '</tr>';
 echo ' <td>' . _('发票号码') . ':</td> <td>';
echo '<input type="text" name="invoice_num" value="' . $_POST['invoice_num'] . '" size="20" maxlength="55" /></td>';
 echo ' <td>' . _('采购入库单号') . ':</td> <td>';
echo '<input type="text" name="receipt_num" value="' . $_POST['receipt_num'] . '" size="20" maxlength="55" /></td>'; 
echo '</tr>';

if (!isset($_POST['FromDate'])) {
    $_POST['FromDate'] = date( "Y-m-d", mktime(0,0,0,date("m"),date("d")-30,date("Y")));
}
if (!isset($_POST['ToDate'])) {
    $_POST['ToDate'] = Date('Y-m-d');
}


echo ' 	<td>' . _('立账日期范围从') . ':</td>
		<td>';
echo '<input type="text" name="FromDate"   onfocus="WdatePicker()"  value="' . $_POST['FromDate'] . '" size="20" maxlength="25" /></td>';

//echo '</tr>';
echo '
		<td>' . _('日期') . ':</td>
		<td>';
echo '<input type="text" name="ToDate"  onfocus="WdatePicker()"  value="' . $_POST['ToDate'] . '" size="20" maxlength="25" /></td>';
echo '</tr>';

echo '</table><div class="centre"><input type="submit" name="Search" value="查找资料"></div>';

if (isset($_POST['Search']) and isset($result) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])){
    $ListCount = DB_num_rows($result);
    $ListPageMax = ceil($ListCount / $_SESSION['DisplayRecordsMax']);
    
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
            echo '<br /><div class="centre">&nbsp;&nbsp;' . $_POST['PageOffset'] . ' ' . _('of') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ': ';
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
                    <th >' . _('供应商代号') . '</th>
                    <th >' . _('供应商名称') . '</th> 
                    <th >' . _('发票号码') . '</th>
                    <th >' . _('发票项目') . '</th>
					<th >' . _('发票类型') . '</th>
                    <th >' . _('发票日期') . '</th>
                    <th >' . _('建单日期') . '</th>
                    <th >' . _('发票金额') . '</th>
                    <th >' . _('税金') . '</th> 
                    <th >' . _('发票备注') . '</th> 
                    <th >' . _('币别') . '</th>
					<th >' . _('入库单号') . '</th> 
					<th >' . _('入库单项目') . '</th>
					<th >' . _('开票金额') . '</th>
					<th >' . _('优惠金额') . '</th>
            </tr>';
    $k = 0; //row counter to determine background colour
    $RowIndex = 0; 
    if (DB_num_rows($result) <> 0) {
        DB_data_seek($result, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);
        $i = 0; //counter for input controls
        while (($myrow = DB_fetch_array($result)) AND ($RowIndex <> $_SESSION['DisplayRecordsMax'])) {
			if ($k == 1) {
				echo '<tr class="EvenTableRows">';
				$k = 0;
			} else {
				echo '<tr class="OddTableRows">';
				$k = 1;
			}  
			$invoice_date= date('Y-m-d',$myrow['invoice_date']); 
			$creation_date= date('Y-m-d',$myrow['creation_date']); 
		echo '  <td>' . $myrow['vendor_code'] . '</td>
                <td>' . $myrow['vendor_name'] . '</td> 
				<td>' . $myrow['invoice_num'] . '</td>
				<td>' . $myrow['invoice_line'] . '</td>
				<td>' . $myrow['ap_invoice_type'] . '</td>
                <td>' . $invoice_date . '</td>
                <td>' . $creation_date . '</td>
				<td>' . $myrow['invoice_amount'] . '</td>
				<td>' . $myrow['tax_amount'] . '</td> 
                <td>' . $myrow['narrative'] . '</td>                           
				<td>' . $myrow['currency_code'] . '</td>
				<td>' . $myrow['receipt_num'] . '</td> 
				<td>' . $myrow['receipt_line'] . '</td>
				<td>' . $myrow['amount'] . '</td>
				<td>' . $myrow['dis_amount'] . '</td>
                                </tr>';
 
			$i++;
			$RowIndex++;
			//end of page full new headings if
		} //end loop through customers
		echo '</table>';
		echo '<input type="hidden" name="JustSelectedACustomer" value="Yes" />';
	}

        if (isset($ListPageMax) AND $ListPageMax > 1) {
                echo '<br /><div class="centre">&nbsp;&nbsp;' . $_POST['PageOffset'] . ' ' . _('of') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ': ';
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