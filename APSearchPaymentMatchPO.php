<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include('includes/session.inc');
$Title = _('供应商付款对应采购明细查询');

$ViewTopic= '供应商付款对应采购明细查询';
$BookMark = '供应商付款对应采购明细查询';

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
$_SESSION['DisplayRecordsMax']=50;
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
    $sql = "select pha.transaction_type,
			      pha.bankaccountname,pha.transaction_date,
							    pha.bankchangenum,
							      pha.transaction_num,
							   pha.transaction_amount header_transaction_amount,pha.dis_amount header_dis_amount,
								   pha.tax_amount,
                                  pha.vendor_code,
                                    pha.narrative,
								pha.currency_code,a.po_num,a.check_amount,c.transaction_amount,c.dis_amount,pha.vendor_code,d.vendor_name 
				from fin_bank_transaction_lines_all c,po_headers_all a ,fin_bank_transaction_headers_all pha, vendors d
				where  c.po_num=a.po_num 
				and  pha.status='核准'
			and pha.transaction_type in ('AP付款','AP退款')
			and pha.transaction_num=c.transaction_num
			and pha.vendor_code=d.vendor_code
				";
    if(isset($_POST['vendor_code']) and $_POST['vendor_code'] != ''){
        $sql = $sql." and d.vendor_code ".LIKE." '%".$_POST['vendor_code']."%' ";
    }
    if(isset($_POST['vendor_name']) and $_POST['vendor_name'] != ''){
        $sql = $sql." and d.vendor_name ".LIKE." '%".$_POST['vendor_name']."%' ";
    } 
	if(isset($_POST['po_num']) and $_POST['po_num'] != ''){
        $sql = $sql." and a.po_num ".LIKE." '%".$_POST['po_num']."%' ";
    } 
	if (isset($_POST['bankchangenum']) and $_POST['bankchangenum'] != '') {		
        $sql = $sql . " and pha.bankchangenum " . LIKE . " '%" . $_POST['bankchangenum'] . "%' ";
    }
	if (isset($_POST['transaction_num']) and $_POST['transaction_num'] != '') {		
        $sql = $sql . " and pha.transaction_num " . LIKE . " '%" . $_POST['transaction_num'] . "%' ";
    }
	if (isset($_POST['bankaccountname']) and $_POST['bankaccountname'] != '') {		
        $sql = $sql . " and pha.bankaccountname " . LIKE . " '%" . $_POST['bankaccountname'] . "%' ";
    }

	if (empty($_POST['FromDate'])==0) {
    $SQL_FromDate = strtotime( $_POST['FromDate']);
    //echo $SQL_FromDate;
    $sql .= " and    pha.transaction_date >= '" . $SQL_FromDate . "' ";
}
if (empty($_POST['ToDate'])==0) {
    $SQL_ToDate = strtotime( $_POST['ToDate']) + 86400;
     //echo $SQL_ToDate;
    $sql .= " and    pha.transaction_date <='" . $SQL_ToDate . "' ";
}
    $sql .= " order by pha.transaction_date desc";
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


if (!isset($_POST['FromDate'])) {
    $_POST['FromDate'] = date( "Y-m-d", mktime(0,0,0,date("m"),date("d")-30,date("Y")));
}
if (!isset($_POST['ToDate'])) {
    $_POST['ToDate'] = Date('Y-m-d');
}
echo '<div class="text-nav"><div class="text-nav-1">
<div >' . _('流水号码') . ':</div> ';
echo '<input type="text" name="transaction_num" value="' . $_POST['transaction_num'] . '" size="20" maxlength="25" /></div>';
echo '<div class="text-nav-1"><div >' . _('采购单号') . ':</div> ';
echo '<input type="text" name="po_num" value="' . $_POST['po_num'] . '" size="20" maxlength="25" /></div>';
echo '<div class="text-nav-1"><div >' . _('付款/转账单号') . ':</div> ';
echo '<input type="text" name="bankchangenum" value="' . $_POST['bankchangenum'] . '" size="20" maxlength="25" /></div>';
echo '<div class="text-nav-1"><div >' . _('银行账户名称') . ':</div> ';
echo '<input type="text" name="bankaccountname" value="' . $_POST['bankaccountname'] . '" size="20" maxlength="25" /></div>';


echo ' ';
 
if (!isset($_POST['FromDate'])) {
    $_POST['FromDate'] = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 30, date("Y")));
}
if (!isset($_POST['ToDate'])) {
    $_POST['ToDate'] = Date('Y-m-d');
}
echo '<div class="text-nav-1"><div >' . '付款日' . _('From') . ':</div>
		 <input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="FromDate" maxlength="10" size="11" value="' . $_POST['FromDate'] . '" /></div>
         <div class="text-nav-1"><div>' . _('To') . ':</div>
		 <input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="ToDate" maxlength="10" size="11" value="' . $_POST['ToDate'] . '" /></div>
	';
echo '<div class="text-nav-1"><div>' . _('供应商代码') . ':</div>
	 ';
echo '<input type="text" name="vendor_code" value="' . $_POST['vendor_code'] . '" size="20" maxlength="25" /></div>';
echo '<div class="text-nav-1"><div >' . _('供应商名称') . ':</div> ';
echo '<input type="text" name="vendor_name" value="' . $_POST['vendor_name'] . '" size="20" maxlength="25" /></div></tr>';

echo '</div></table><div class="centre"><input type="submit" name="Search" value="查找资料"></div>';

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
                    <th class="ascending" width = 100>' . _('流水号') . '</th>
					<th  width = 150>' . _('付款/转账单号') . '</th> 
                    <th width = 120>' . _('供应商') . '</th> 
                    <th  width = 90>' . _('付款金额') . '</th>    
                    <th  width = 90>' . _('免付款金额') . '</th>                             
                    <th  width = 150>' . _('备注') . '</th>
                    <th class="ascending"width = 90>' . _('付款日') . '</th> 
					<th width="100"  >' . _('采购单') . '</th> 
					  <th  >' . _('付款金额') . '</th> 
					  <th  >' . _('优惠金额') . '</th> 



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
			$paymentdate= date('Y-m-d',$myrow['paymentdate']); 
			echo '<td>' . $myrow['transaction_num'] . '</td> 
			<td>' . $myrow['bankchangenum'] . '</td>
                <td>' . $myrow['vendor_code'] . '</td>  
                <td>' . $myrow['header_transaction_amount'] . '</td> 
                <td>' . $myrow['header_dis_amount'] . '</td>
				<td>' . $myrow['narrative'] . '</td>
                <td>' . date('Y-m-d', $myrow['transaction_date']) . '</td>
				 
				<td><a href="' . $RootPath . '/SearchPO2.php?Updatepo_num=' . $myrow['po_num'] .'" target="_blank" >' . $myrow['po_num']  . '</td>  
			    <td>' . $myrow['transaction_amount'] . '</td>
			    <td>' . $myrow['dis_amount'] . '</td>
              </tr>';
				 
			$i++;
			$RowIndex++;
			//end of page full new headings if
		} //end loop through customers
		echo '</table>';
        echo '<input type="hidden" name="JustSelectedACustomer" value="Yes" />';
        echo '<div>
                <a href="' . $RootPath . '/APSearchPaymentMatchPOExcel.php?vendor_code=' .$_POST['vendor_code'] .
                '&vendor_name='  .$_POST['vendor_name'] . 
                '&po_num='  .$_POST['po_num'] . 
                '&bankchangenum='  .$_POST['bankchangenum'] . 
                '&transaction_num='  .$_POST['transaction_num'] . 
                '&bankaccountname='  .$_POST['bankaccountname'] . 
                '&FromDate='  .$_POST['FromDate'] .
                '&ToDate=' .$_POST['ToDate']  .' ">' .'资料导出Excel表' . '</a>
                </div>';
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