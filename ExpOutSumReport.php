<?php
 ob_start();
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include('includes/session.inc');
$Title = _('收入支出汇总查询');
$ViewTopic= '收入支出汇总查询';
$BookMark = '收入支出汇总查询';

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
    $sql = "select bankaccountname,bankaccount,transaction_amount,transaction_type,currency_code,sum(transaction_amount)
	from ( SELECT s.transaction_num,s.transaction_amount,s.dis_amount,s.tax_amount,s.transaction_date,s.bankchangenum,s.currency_code,s.transaction_type,b.customer_code,b.customer_name,a.bankaccountname,a.bankaccount
     FROM
    fin_bank_transaction_headers_all s,
   fin_bank_alls a,customers b
    WHERE  s.bankaccountname = a.bankaccountname 
    and status='核准' and substr(s.transaction_type,1,2)   in ('AR')
    and s.customer_code=b.customer_code
    union
    SELECT s.transaction_num,s.transaction_amount,s.dis_amount,s.tax_amount,s.transaction_date,s.bankchangenum,s.currency_code,s.transaction_type,s.other_person,s.other_person,a.bankaccountname,a.bankaccount
    FROM
    fin_bank_transaction_headers_all s,
   fin_bank_alls a
    WHERE  s.bankaccountname = a.bankaccountname 
    and status='核准' and substr(s.transaction_type,1,2) not in ('AR','AP')
union
   SELECT s.transaction_num,s.transaction_amount,s.dis_amount,s.tax_amount,s.transaction_date,s.bankchangenum,s.currency_code,s.transaction_type,b.vendor_code,b.vendor_name,a.bankaccountname,a.bankaccount
FROM
    fin_bank_transaction_headers_all s,
   fin_bank_alls a,vendors b
WHERE  s.bankaccountname = a.bankaccountname and s.vendor_code=b.vendor_code 
and status='核准' and substr(s.transaction_type,1,2)   in ('AP') ) aa where 1=1 
";
      if (isset($_POST['bankaccountname']) and $_POST['bankaccountname'] != '') { 
		$sql = $sql . " and  bankaccountname " . LIKE . " '%" . $_POST['bankaccountname'] . "%' ";
    }
    if (isset($_POST['transaction_date']) and $_POST['transaction_date'] != '') { 
		$sql = $sql . " and  transaction_date " . LIKE . " '%" . $_POST['transaction_date'] . "%' ";
    }
	if (isset($_POST['transaction_type']) and $_POST['transaction_type'] != '') { 
		$sql = $sql . " and transaction_type " . LIKE . " '%" . $_POST['transaction_type'] . "%' ";
    }
	if (isset($_POST['transaction_num']) and $_POST['transaction_num'] != '') { 
		$sql = $sql . " and transaction_num " . LIKE . " '%" . $_POST['transaction_num'] . "%' ";
    }
    if (isset($_POST['bankaccount']) and $_POST['bankaccount'] != '') {
        $sql = $sql . " and bankaccount " . LIKE . " '%" . $_POST['bankaccount'] . "%' ";
    }
    if (isset($_POST['bankchangenum']) and $_POST['bankchangenum'] != '') {
        $sql = $sql . " and bankchangenum " . LIKE . " '%" . $_POST['bankchangenum'] . "%' ";
    }
  
    if (empty($_POST['FromDate']) == 0) {
        $SQL_FromDate = strtotime($_POST['FromDate']);
        $sql .= " and  transaction_date >= '" . $SQL_FromDate . "' ";
    }
    if (empty($_POST['ToDate']) == 0) {
        $SQL_ToDate = strtotime($_POST['ToDate']) + 86400;
        //echo $SQL_ToDate;
        $sql .= " and  transaction_date <='" . $SQL_ToDate . "' ";
    }
    
      $sql .=" group by bankaccountname,bankaccount,transaction_amount,transaction_type,currency_code";
    
  
    $result = DB_query($sql,$db);
    if (DB_num_rows($result)==0) {
        unset($result);
        prnMsg(_('找不到订单明细，请重新输入条件查询！') ,'error');
    }
}


echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('收入支出汇总查询') . '</p>';
echo '<table cellpadding="3" class="selection">';

 
echo '<tr><td >' . _('银行账户名称') . ':</td><td>';
echo '<input type="text" name="bankaccountname" value="' . $_POST['bankaccountname'] . '" size="20" maxlength="25" /></td>';
echo '<td>' . _('银行账号') . ':</td>
	<td>';
echo '<input type="text" name="bankaccount" value="' . $_POST['bankaccount'] . '" size="20" maxlength="25" /></td>';
echo '<td>' . _('类型') . ':</td>
	<td>';
echo '<input type="text" name="transaction_type" value="' . $_POST['transaction_type'] . '" size="20" maxlength="25" /></td>';
echo '<td>' . _('流水号') . ':</td>
	<td>';
echo '<input type="text" name="transaction_num" value="' . $_POST['transaction_num'] . '" size="20" maxlength="25" /></td>';
echo '</tr>';
echo '<tr><td >' . _('银行/转账单号') . ':</td><td>';
echo '<input type="text" name="bankchangenum" value="' . $_POST['bankchangenum'] . '" size="10" maxlength="25" /></td>';
  
if (!isset($_POST['FromDate'])) {
    $_POST['FromDate'] = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 30, date("Y")));
}
if (!isset($_POST['ToDate'])) {
    $_POST['ToDate'] = Date('Y-m-d');
}
echo '<td>' . '交易日期' . _('From') . ':</td>
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
                    <th class="ascending" >' . _('银行/账号') . '</th>
                    <th width = 60>' . _('账号') . '</th> 
                    <th class="ascending"width = 90>' . _('类型') . '</th>  
                    <th class="ascending"width = 100>' . _('币别') . '</th> 
                    <th class="ascending"width = 100>' . _('金额') . '</th>   
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
                <td>' . $myrow['bankaccountname'] . '</td>                 
                <td>' . $myrow['bankaccount'] . '</td> 						 
                <td>' . $myrow['transaction_type'] . '</td> 
               
                <td>' . $myrow['currency_code'] . '</td>   
                <td>' . $myrow['transaction_amount'] . '</td>   
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