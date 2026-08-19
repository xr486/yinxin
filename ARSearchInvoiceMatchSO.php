<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include('includes/session.inc');
$Title = _('客户发票对应业务订单明细报表');

$ViewTopic= '客户发票对应业务订单明细报表';
$BookMark = '客户发票对应业务订单明细报表';

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
    $sql = 'select ah.ar_invoice_type,ah.invoice_num,ah.invoice_amount,ah.tax_amount,ah.invoice_date,ah.narrative
            ,ah.customer_code,ve.customer_name,ah.created_by,ah.creation_date,ah.currency_code,al.invoice_line,al.so_num , al.amount,al.dis_amount 
			from  ar_invoice_headers_all ah, 
			 ar_invoice_lines_all al,
			customers ve 
			where  ve.customer_code=ah.customer_code
			and al.customer_code=ah.customer_code
			and ah.ar_invoice_type=al.ar_invoice_type
			and ah.invoice_num=al.invoice_num';
    if(isset($_POST['customer_code']) and $_POST['customer_code'] != ''){
        $sql = $sql." and ve.customer_code ".LIKE." '%".$_POST['customer_code']."%' ";
    }
    if(isset($_POST['customer_name']) and $_POST['customer_name'] != ''){
        $sql = $sql." and ve.customer_name ".LIKE." '%".$_POST['customer_name']."%' ";
    }

	if(isset($_POST['invoice_num']) and $_POST['invoice_num'] != ''){ 
		$sql = $sql." and ah.invoice_num ".LIKE." '%".$_POST['invoice_num']."%' ";
    }
	if(isset($_POST['so_num']) and $_POST['so_num'] != ''){ 
		$sql = $sql." and al.so_num ".LIKE." '%".$_POST['so_num']."%' ";
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
echo '<div class="text-nav">';
echo '<div class="text-nav-1"><div>' . _('客户名称') . ':</div>';
echo '<input type="text" name="customer_name" value="' . $_POST['customer_name'] . '" size="20" maxlength="25" /></div>';
echo '<div class="text-nav-1"><div>' . _('客户代码') . ':</div>
';
echo '<input type="text" name="customer_code" value="' . $_POST['customer_code'] . '" size="20" maxlength="25" /></div>';

 echo '<div class="text-nav-1"><div>' . _('发票号码') . ':</div>';
echo '<input type="text" name="invoice_num" value="' . $_POST['invoice_num'] . '" size="20" maxlength="55" /></div>';
 echo '<div class="text-nav-1"><div>' . _('订单号码') . ':</div>';
echo '<input type="text" name="so_num" value="' . $_POST['so_num'] . '" size="20" maxlength="55" /></div>'; 


if (!isset($_POST['FromDate'])) {
    $_POST['FromDate'] = date( "Y-m-d", mktime(0,0,0,date("m"),date("d")-30,date("Y")));
}
if (!isset($_POST['ToDate'])) {
    $_POST['ToDate'] = Date('Y-m-d');
}


echo '<div class="text-nav-1"><div>' . _('发票日期范围从') . ':</div>
';
echo '<input type="text" name="FromDate" class="date" alt="'.$_SESSION['DefaultDateFormat'].'"  onfocus="WdatePicker() " value="' . $_POST['FromDate'] . '" size="20" maxlength="25" /></div>';
//echo '</tr>';
echo '
<div class="text-nav-1"><div>' . _('日期') . ':</div>
		';
echo '<input type="text" name="ToDate" class="date" alt="'.$_SESSION['DefaultDateFormat'].'" onfocus="WdatePicker() " value="' . $_POST['ToDate'] . '" size="20" maxlength="25" /></div>';
echo '</div>';

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
            echo '<br /><div class="centre">&nbsp;&nbsp;' . $_POST['PageOffset'] . ' ' . _('of') . ' ' . $ListPageMax . ' ' . _('页数') . '. ' . _('转到页') . ': ';
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
                    <input type="submit" name="Go1" value="' . _('转到') . '" />
                    <input type="submit" name="Previous" value="' . _('上一页') . '" />
                    <input type="submit" name="Next" value="' . _('下一页') . '" />';
            echo '</div>';
    }
    echo '<br />			  <div class="text-nav-table">
                    <table cellpadding="2" class="selection">';
    echo '<tr>
                    <th >' . _('客户代号') . '</th>
                    <th >' . _('客户名称') . '</th> 
                    <th >' . _('发票号码') . '</th>
					<th >' . _('发票类型') . '</th>
                    <th >' . _('发票日期') . '</th>
                    <th >' . _('建单日期') . '</th>
                    <th >' . _('发票金额') . '</th>
                    <th >' . _('税金') . '</th> 
                    <th >' . _('发票备注') . '</th> 
                    <th >' . _('币别') . '</th>
					<th >' . _('发票行') . '</th>
					<th >' . _('业务订单') . '</th>  
					<th >' . _('开票金额') . '</th>
					<th >' . _('优惠金额') . '</th>
            </tr>';
    $k = 0; //row counter to determine background colour
    $RowIndex = 0; 
    $all_line=0;
    $all_invoice_amount=0;
    $all_tax_amount=0;
    $all_amount=0;
    $all_dis_amount=0;
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
			$creation_date= date('Y-m-d h:i:s',$myrow['creation_date']);   
                        $all_line=$all_line+1;
                        $all_invoice_amount=$all_invoice_amount+$myrow['invoice_amount'];
                        $all_tax_amount=$all_tax_amount + $myrow['tax_amount'];
                        $all_amount=$all_amount + $myrow['amount'];
                        $all_dis_amount=$all_dis_amount + $myrow['dis_amount'];
			echo '  <td>' . $myrow['customer_code'] . '</td>
                                <td>' . $myrow['customer_name'] . '</td> 
				<td>' . $myrow['invoice_num'] . '</td>
				<td>' . $myrow['ar_invoice_type'] . '</td>
                                <td>' .  $invoice_date . '</td>
                                <td>' .  $creation_date . '</td>
				<td>' . $myrow['invoice_amount'] . '</td>
				<td>' . $myrow['tax_amount'] . '</td> 
                     <td>' . $myrow['narrative'] . '</td>                           
				<td>' . $myrow['currency_code'] . '</td>
				<td>' . $myrow['invoice_line'] . '</td>
				<td><a href="' . $RootPath . '/SearchSO2.php?Updateorder_number=' . $myrow['so_num'] .'" target="_blank" >' . $myrow['so_num'] . '</td>  
				<td>' . $myrow['amount'] . '</td>
				<td>' . $myrow['dis_amount'] . '</td>
                                </tr>';
 
			$i++;
			$RowIndex++;
			//end of page full new headings if
		} //end loop through customers
		echo '<tr><td>总计</td><td>笔数</td><td>' . $all_line . '</td> <td></td><td></td><td>' . $all_invoice_amount . '</td><td>' . $all_tax_amount . '</td><td></td><td></td><td></td><td></td><td></td><td>' . $all_amount . '</td><td>' . $all_dis_amount . '</td></tr>';
		echo '</table></div>';
                echo '<input type="hidden" name="JustSelectedACustomer" value="Yes" />';
                echo '<div>
                    <a href="' . $RootPath . '/ARSearchInvoiceMatchSOExcel.php?FromDate=' .$_POST['FromDate'] .
                    '&ToDate=' .$_POST['ToDate'] . '&invoice_num=' .$_POST['invoice_num'] .
                    '&customer_name=' .$_POST['customer_name'] . '&customer_code=' .$_POST['customer_code'] .
                    '&so_num=' .$_POST['so_num'] .' ">' .'资料导出Excel表' . '</a>
            </div>';
	}

        if (isset($ListPageMax) AND $ListPageMax > 1) {
                echo '<br /><div class="centre">&nbsp;&nbsp;' . $_POST['PageOffset'] . ' ' . _('of') . ' ' . $ListPageMax . ' ' . _('页数') . '. ' . _('转到页') . ': ';
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
                        <input type="submit" name="Go2" value="' . _('转到') . '" />
                        <input type="submit" name="Previous" value="' . _('上一页') . '" />
                        <input type="submit" name="Next" value="' . _('下一页') . '" />';
                echo '</div>';
    }//end if results to show

}
echo '</div></form>';
include('includes/footer.inc');