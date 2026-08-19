<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include('includes/session.inc');
$Title = _('客户收款对应订单明细查询');

$ViewTopic= '客户收款对应订单明细查询';
$BookMark = '客户收款对应订单明细查询';

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
    $sql = "select pha.transaction_type,
			      pha.bankaccountname,pha.transaction_date,
							    pha.bankchangenum,
							      pha.transaction_num,
							   pha.transaction_amount header_transaction_amount,pha.dis_amount header_dis_amount,
								   pha.tax_amount,
                                  pha.customer_code,
                                    pha.narrative,
								pha.currency_code,c.so_num,c.transaction_amount,c.dis_amount,d.customer_name 
				from fin_bank_transaction_lines_all c,
				fin_bank_transaction_headers_all pha, customers d
				where   pha.status='核准'
			and pha.transaction_type in ('AR收款','AR退款')
			and pha.transaction_num=c.transaction_num
			and pha.customer_code=d.customer_code
				";
    if(isset($_POST['customer_code']) and $_POST['customer_code'] != ''){
        $sql = $sql." and d.customer_code ".LIKE." '%".$_POST['customer_code']."%' ";
    }
    if(isset($_POST['customer_name']) and $_POST['customer_name'] != ''){
        $sql = $sql." and d.customer_name ".LIKE." '%".$_POST['customer_name']."%' ";
    } 
	if(isset($_POST['order_number']) and $_POST['order_number'] != ''){
        $sql = $sql." and a.order_number ".LIKE." '%".$_POST['order_number']."%' ";
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
echo '<div class="text-nav">
<div class="text-nav-1"><div >' . _('流水号码') . ':</div> ';
echo '<input type="text" name="transaction_num" value="' . $_POST['transaction_num'] . '" size="20" maxlength="25" /></div>';
echo '<div class="text-nav-1"><div >' . _('订单单号') . ':</div> ';
echo '<input type="text" name="order_number" value="' . $_POST['order_number'] . '" size="20" maxlength="25" /></div>';
echo '<div class="text-nav-1"><div >' . _('收款/转账单号') . ':</div> ';
echo '<input type="text" name="bankchangenum" value="' . $_POST['bankchangenum'] . '" size="20" maxlength="25" /></div>';
echo '<div class="text-nav-1"><div >' . _('银行账户名称') . ':</div> ';
echo '<input type="text" name="bankaccountname" value="' . $_POST['bankaccountname'] . '" size="20" maxlength="25" /></div>';


 

echo '<div class="text-nav-1"><div >' . '收款日' . _('From') . ':</div>
		 <input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="FromDate" maxlength="10" size="11" value="' . $_POST['FromDate'] . '" /></div>
         <div class="text-nav-1"><div>' . _('To') . ':</div>
		 <input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="ToDate" maxlength="10" size="11" value="' . $_POST['ToDate'] . '" /></div>
	';
echo '<div class="text-nav-1"><div>' . _('客户代码') . ':</div>
	 ';
echo '<input type="text" name="customer_code" value="' . $_POST['customer_code'] . '" size="20" maxlength="25" /></div>';
echo '<div class="text-nav-1"><div >' . _('客户名称') . ':</div> ';
echo '<input type="text" name="customer_name" value="' . $_POST['customer_name'] . '" size="20" maxlength="25" /></div></tr>';

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
					<th class="ascending" width = 100>' . _('收款/转账单号') . '</th> 
                    <th width = 80>' . _('客户') . '</th> 
                    <th  width = 90>' . _('收款金额') . '</th>    
                    <th  width = 90>' . _('免收款金额') . '</th>                             
                    <th  width = 150>' . _('备注') . '</th>
                    <th class="ascending"width = 90>' . _('收款日') . '</th>
				
					<th width="100"  >' . _('订单号') . '</th> 
					  <th  >' . _('收款金额') . '</th> 
					  <th  >' . _('优惠金额') . '</th> 



            </tr>';
    $k = 0; //row counter to determine background colour
    $RowIndex = 0; 
    $all_line=0;
    $all_header_transaction_amount=0;
    $all_header_dis_amount=0;
    $all_transaction_amount=0;
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
			$paymentdate= date('Y-m-d',$myrow['paymentdate']); 
            $all_line=$all_line+1;
            $all_header_transaction_amount=$all_header_transaction_amount+$myrow['header_transaction_amount'];
            $all_header_dis_amount=$all_header_dis_amount + $myrow['header_dis_amount'];
            $all_transaction_amount=$all_transaction_amount+$myrow['transaction_amount'];
            $all_dis_amount=$all_dis_amount + $myrow['dis_amount'];
			echo '<td>' . $myrow['transaction_num'] . '</td> 
			<td>' . $myrow['bankchangenum'] . '</td>
                <td>' . $myrow['customer_code'] . '</td>  
                <td>' . $myrow['header_transaction_amount'] . '</td> 
                <td>' . $myrow['header_dis_amount'] . '</td>
				<td>' . $myrow['narrative'] . '</td>
                <td>' . date('Y-m-d', $myrow['transaction_date']) . '</td>
				<td><a href="' . $RootPath . '/SearchSO2.php?Updateorder_number=' . $myrow['order_number'] .'" target="_blank" >' . $myrow['so_num']  . '</td> 
			    <td>' . $myrow['transaction_amount'] . '</td>
			    <td>' . $myrow['dis_amount'] . '</td>
              </tr>';
				 
			$i++;
			$RowIndex++;
			//end of page full new headings if
		} //end loop through customers
		echo '<tr><td>总计</td><td>笔数</td><td>' . $all_line . '</td><td>' . $all_header_transaction_amount . '</td><td>' . $all_header_dis_amount . '</td><td></td><td></td><td></td><td>' . $all_transaction_amount . '</td><td>' . $all_dis_amount . '</td></tr>';
		echo '</table>';
        echo '<input type="hidden" name="JustSelectedACustomer" value="Yes" />';
        echo '<div>
                    <a href="' . $RootPath . '/ARSearchPaymentMatchSOExcel.php?FromDate=' .$_POST['FromDate'] .
                    '&ToDate=' .$_POST['ToDate'] . '&bankchangenum=' .$_POST['bankchangenum'] .
                    '&customer_name=' .$_POST['customer_name'] . '&customer_code=' .$_POST['customer_code'] .
                    '&order_number=' .$_POST['order_number'] .'&bankaccountname=' .$_POST['bankaccountname'] .
                    '&transaction_num=' .$_POST['transaction_num'] .' ">' .'资料导出Excel表' . '</a>
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