<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include('includes/session.inc');
$Title = _('钢网供应商付款明细查询');

$ViewTopic= '钢网供应商付款明细查询';
$BookMark = '钢网供应商付款明细查询';

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

unset($result);
$_SESSION['DisplayRecordsMax'] = 10;
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
    $sql = 'select al.creationdate,ah.invoicenum,ah.invoiceamount,ah.taxamount,ah.invoicedate,ah.paymentamount,ah.narrative
            ,al.invoicelinenum,al.amount,al.ordernumber,al.item_name,al.linenarrative,cu.vendor_code,cu.vendor_name,cu.vendor_address,ab.bankname,ab.bankaccount,ab.bankcompany
            from  ap_invoice_pay_headers_all ah,
            ap_invoice_pay_lines_all al,
            sf_orders_all ch, 
            vendors cu,
			ap_bank_alls  ab
            where ah.invoicenum=al.invoicenum
            and al.ordernumber=ch.order_number 
			and ah.vendor_code=al.vendor_code
			and ah.bankname=ab.bankname
            and ah.vendor_code=cu.vendor_code';
    if(isset($_POST['vendor_code']) and $_POST['vendor_code'] != ''){
        $sql = $sql." and cu.vendor_code ".LIKE." '%".$_POST['vendor_code']."%' ";
    }
    if(isset($_POST['vendor_name']) and $_POST['vendor_name'] != ''){
        $sql = $sql." and cu.vendor_name ".LIKE." '%".$_POST['vendor_name']."%' ";
    }
    if(isset($_POST['SupplierContacts']) and $_POST['SupplierContacts'] != ''){
        $sql = $sql." and cu.vendor_contacts ".LIKE." '%".$_POST['SupplierContacts']."%' ";
    }
    if(isset($_POST['bankname']) and $_POST['bankname'] != ''){
        $sql = $sql." and ab.bankname ".LIKE." '%".$_POST['bankname']."%' ";
    }
	if(isset($_POST['ContactsPhone']) and $_POST['ContactsPhone'] != ''){
        $sql = $sql." and cu.contacts_phone ".LIKE." '%".$_POST['ContactsPhone']."%' ";
    }
	if (empty($_POST['FromDate'])==0) {
    $SQL_FromDate = strtotime( $_POST['FromDate']);
    //echo $SQL_FromDate;
    $sql .= " and  ah.invoicedate >= '" . $SQL_FromDate . "' ";
     }
    if (empty($_POST['ToDate'])==0) {
    $SQL_ToDate = strtotime( $_POST['ToDate']) + 86400;
     //echo $SQL_ToDate;
    $sql .= " and  ah.invoicedate <='" . $SQL_ToDate . "' ";
    }
      
     $sql = $sql." order by cu.vendor_code,ah.invoicedate, ah.invoicenum,al.invoicelinenum ";
    
    $result = DB_query($sql,$db);
    if (DB_num_rows($result)==0) {
        unset($result);
        prnMsg(_('找不到钢网供应商付款信息，请重新输入条件查询！') ,'error');
    }
}


echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('查询条件') . '</p>';
echo '<table cellpadding="3" class="selection">';
echo '<tr><td colspan="2">' . _('Enter a partial Name') . ':</td><td>';
echo '<input type="text" name="vendor_name" value="' . $_POST['vendor_name'] . '" size="20" maxlength="25" /></td>';
echo '<td><b>' . _('OR') . '</b></td><td>' . _('Enter a partial Code') . ':</td>
	<td>';
echo '<input type="text" name="vendor_code" value="' . $_POST['vendor_code'] . '" size="20" maxlength="25" /></td>';
echo '</tr>';
echo '<tr>
          <td><b>' . _('OR') . '</b></td>
          <td>' . _('填入联系人姓名') . ':</td>
	<td>';
echo '<input type="text" name="SupplierContacts" value="' . $_POST['SupplierContacts'] . '" size="20" maxlength="25" /></td>';
echo '<td><b>' . _('OR') . '</b></td>
		<td>' . _('付款银行账户名称') . ':</td>
		<td>';
echo '<input type="text" name="bankname" value="' . $_POST['bankname'] . '" size="20" maxlength="25" /></td>';
echo '</tr>';

if (!isset($_POST['FromDate'])) {
    $_POST['FromDate'] = date( "Y-m-d", mktime(0,0,0,date("m"),date("d")-30,date("Y")));
}
if (!isset($_POST['ToDate'])) {
    $_POST['ToDate'] = Date('Y-m-d');
}

echo '<td><b>' . _('或') . '</b></td>
		<td>' . _('付款日期范围从') . ':</td>
		<td>';
echo '<input type="text" name="FromDate"   onfocus="WdatePicker()"  value="' . $_POST['FromDate'] . '" size="20" maxlength="25" /></td>';
//echo '</tr>';
echo '<td><b>' . _('到') . '</b></td>
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
            echo '<br /><div class="centre">&nbsp;&nbsp;第' . $_POST['PageOffset'] . ' ' . _('页，共') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ': ';
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
                    <th class="ascending">' . _('供应商编号') . '</th>
                    <th class="ascending">' . _('供应商名称') . '</th>
                    <th class="ascending">' . _('供应商地址') . '</th>
                    <th class="ascending">' . _('发票(收据)号码') . '</th>
                    <th class="ascending" width = 89>' . _('发票日期') . '</th>
                    <th class="ascending">' . _('付款金额') . '</th>
					<th class="ascending">' . _('付款银行名称') . '</th>
					<th class="ascending">' . _('银行账户') . '</th>
					<th class="ascending">' . _('开户行名称') . '</th>
                    <th class="ascending">' . _('发票金额') . '</th>
                    <th class="ascending">' . _('税金') . '</th> 
                    <th class="ascending">' . _('发票备注') . '</th>
                    <th class="ascending">' . _('发票行') . '</th>
                    <th class="ascending">' . _('行金额') . '</th>
                    <th class="ascending">' . _('采购订单号码') . '</th>
                    <th class="ascending">' . _('项目名称') . '</th>
                    <th class="ascending"width = 89>' . _('开票日期') . '</th>
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
			$invoicedate= date('Y-m-d',$myrow['invoicedate']);
		    $creationdate= date('Y-m-d',$myrow['creationdate']);

			echo '  <td>' . $myrow['vendor_code'] . '</td>
                   <td>' . $myrow['vendor_name'] . '</td>
				<td>' . $myrow['vendor_address'] . '</td>
				<td>' . $myrow['invoicenum'] . '</td>
                <td>' . $invoicedate . '</td>
                <td>' .  $myrow['paymentamount'] . '</td>
			    <td>' . $myrow['bankname'] . '</td>
				<td>' . $myrow['bankaccount'] . '</td>
				<td>' . $myrow['bankcompany'] . '</td>
				<td>' . $myrow['invoiceamount'] . '</td>
				<td>' . $myrow['taxamount'] . '</td>                            
                <td>' . $myrow['narrative'] . '</td>
				<td>' . $myrow['invoicelinenum'] . '</td>
                 <td>' . $myrow['amount'] . '</td>
                 <td>' . $myrow['ordernumber'] . '</td>                               
				<td>' . $myrow['item_name'] . '</td>
                 <td>' . $creationdate . '</td>
                                </tr>';
			$i++;
			$RowIndex++;
			//end of page full new headings if
		} //end loop through customers
		echo '</table>';
		echo '<input type="hidden" name="JustSelectedACustomer" value="Yes" />';
	}

        if (isset($ListPageMax) AND $ListPageMax > 1) {
                echo '<br /><div class="centre">&nbsp;&nbsp;第' . $_POST['PageOffset'] . ' ' . _('页，共') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ': ';
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