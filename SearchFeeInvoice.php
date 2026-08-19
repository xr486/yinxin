<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include('includes/session.inc');
$Title = _('费用付款查询');

$ViewTopic= '费用付款查询';
$BookMark = '费用付款查询';

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
$_SESSION['DisplayRecordsMax'] = 10;
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
	$SQL_FromDate = FormatDateForSQL($_POST['FromDate']);
    $SQL_ToDate = FormatDateForSQL($_POST['ToDate']);
    $sql = 'select af.createdby,af.creationdate,PaymentDate,PayRemark
            ,PayAmount,PaySupplier,PayName,PayPersonName,ContactsPhone,Paytype,af.bankname,ab.bankaccount,ab.bankcompany
            from  ap_fee_pay_headers_all af,
			ap_bank_alls  ab
			where   af.bankname=ab.bankname ';
    if(isset($_POST['BankName']) and $_POST['BankName'] != ''){
        $sql = $sql." and af.BankName ".LIKE." '%".$_POST['BankName']."%' ";
    }
    if(isset($_POST['PaySupplier']) and $_POST['PaySupplier'] != ''){
        $sql = $sql." and PaySupplier ".LIKE." '%".$_POST['PaySupplier']."%' ";
    }
    if(isset($_POST['PayName']) and $_POST['PayName'] != ''){
        $sql = $sql." and PayName ".LIKE." '%".$_POST['PayName']."%' ";
    }
    if(isset($_POST['Paytype']) and $_POST['Paytype'] != ''){
        $sql = $sql." and Paytype ".LIKE." '%".$_POST['Paytype']."%' ";
    }
	if(isset($_POST['ContactsPhone']) and $_POST['ContactsPhone'] != ''){
        $sql = $sql." and ContactsPhone ".LIKE." '%".$_POST['ContactsPhone']."%' ";
    }
    if(isset($_POST['FromDate']) and $_POST['FromDate'] != ''){
        $sql = $sql." and PaymentDate >= ' " .$SQL_FromDate."' ";
    }
    
    if(isset($_POST['ToDate']) and $_POST['ToDate'] != ''){
        $sql = $sql." and PaymentDate <= ' " .$SQL_ToDate."' ";
    }
     $sql = $sql." order by PayPersonName,PaymentDate ";
    
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
echo '<tr><td colspan="2">' . _('部分收款单位') . ':</td><td>';
echo '<input type="text" name="PaySupplier" value="' . $_POST['PaySupplier'] . '" size="20" maxlength="25" /></td>';
echo '<td><b>' . _('OR') . '</b></td><td>' . _('付款银行账户名称') . ':</td>
	<td>';
echo '<input type="text" name="BankName" value="' . $_POST['BankName'] . '" size="20" maxlength="25" /></td>';
echo '</tr>';
echo '<tr>
          <td><b>' . _('OR') . '</b></td>
          <td>' . _('收款单号') . ':</td>
	<td>';
echo '<input type="text" name="PayName" value="' . $_POST['PayName'] . '" size="20" maxlength="25" /></td>';
echo '<td><b>' . _('OR') . '</b></td>
		<td>' . _('付款类型') . ':</td>
		<td>';
echo '<input type="text" name="Paytype" value="' . $_POST['Paytype'] . '" size="20" maxlength="25" /></td>';
echo '</tr>';
if (!isset($_POST['FromDate']) OR !Is_Date($_POST['FromDate'])){
   $_POST['FromDate'] = Date($_SESSION['DefaultDateFormat'], Mktime(0,0,0,Date('m'),Date('d')-30,Date('y')));
}
if (!isset($_POST['ToDate']) OR !Is_Date($_POST['ToDate'])){
   $_POST['ToDate'] = Date($_SESSION['DefaultDateFormat'], Mktime(0,0,0,Date('m'),Date('d')+2,Date('y')));
}
echo '<td><b>' . _('或') . '</b></td>
		<td>' . _('付款日期范围从') . ':</td>
		<td>';
echo '<input type="text" name="FromDate" class="date" alt="'.$_SESSION['DefaultDateFormat'].'" value="' . $_POST['FromDate'] . '" size="20" maxlength="25" /></td>';
//echo '</tr>';
echo '<td><b>' . _('到') . '</b></td>
		<td>' . _('日期') . ':</td>
		<td>';
echo '<input type="text" name="ToDate" class="date" alt="'.$_SESSION['DefaultDateFormat'].'" value="' . $_POST['ToDate'] . '" size="20" maxlength="25" /></td>';
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
                    <th class="ascending">' . _('收款单号') . '</th>
                    <th class="ascending">' . _('收款单位名称') . '</th>
                    <th class="ascending">' . _('收款人姓名') . '</th>
                    <th class="ascending">' . _('收款人联系方式') . '</th>
                    <th class="ascending">' . _('付款类型') . '</th>
                    <th class="ascending">' . _('付款日期') . '</th>
                    <th class="ascending">' . _('付款金额') . '</th>
					<th class="ascending">' . _('付款银行名称') . '</th>
					<th class="ascending">' . _('银行账户') . '</th>
					<th class="ascending">' . _('开户行名称') . '</th>
                    <th class="ascending">' . _('款项备注') . '</th> 
                    <th class="ascending">' . _('单据建立日期') . '</th>
                    <th class="ascending">' . _('单据建立人员') . '</th>
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
			echo '  <td>' . $myrow['PayName'] . '</td>
				 <td>' . $myrow['PaySupplier'] . '</td>
			<td>' . $myrow['PayPersonName'] . '</td>
                 <td>' . $myrow['ContactsPhone'] . '</td>
				<td>' . $myrow['Paytype'] . '</td>
				 <td>' .  ConvertSQLDate($myrow['PaymentDate']) . '</td>
				<td>' . $myrow['PayAmount'] . '</td>
				<td>' . $myrow['bankname'] . '</td>
				<td>' . $myrow['bankaccount'] . '</td>
				<td>' . $myrow['bankcompany'] . '</td>
				 <td>' . $myrow['PayRemark'] . '</td>                    
                <td>' . $myrow['creationdate'] . '</td>
				<td>' . $myrow['createdby'] . '</td>
                
                                
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