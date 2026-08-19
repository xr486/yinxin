<?php

ob_start();
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include('includes/session.inc');
$Title = _('供应商付款明细查询');
$ViewTopic = '供应商付款明细查询';
$BookMark = '供应商付款明细查询';

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

if (isset($_POST['Search']) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])) {
	$_POST['To1Date'] = Date('Y-m-d');             
	    $SQL_From1Date = strtotime( $_POST['To1Date']);
	    $SQL_To1Date = strtotime( $_POST['To1Date']) + 86400;
    $sql = "select pha.transaction_type,
			      pha.bankaccountname,
							    pha.bankchangenum,
							      pha.transaction_num,
							   pha.transaction_amount,dis_amount,
								   pha.tax_amount,
                                  pha.vendor_code,
                                    pha.narrative,
								pha.currency_code,
                                 pha.transaction_date,
                pha.creation_date,pha.created_by,pha.vendor_code,c.vendor_name,pha.status
	from fin_bank_transaction_headers_all pha, vendors c
            where pha.status='核准'
			and pha.transaction_type in ('AP付款','AP退款','AP预付款')
			and pha.vendor_code=c.vendor_code 
			and pha.transaction_amount>0 ";

    
    if (isset($_POST['bankchangenum']) and $_POST['bankchangenum'] != '') {		
        $sql = $sql . " and pha.bankchangenum " . LIKE . " '%" . $_POST['bankchangenum'] . "%' ";
    }
	if (isset($_POST['transaction_num']) and $_POST['transaction_num'] != '') {		
        $sql = $sql . " and pha.transaction_num " . LIKE . " '%" . $_POST['transaction_num'] . "%' ";
    }
	if (isset($_POST['bankaccountname']) and $_POST['bankaccountname'] != '') {		
        $sql = $sql . " and pha.bankaccountname " . LIKE . " '%" . $_POST['bankaccountname'] . "%' ";
    }
    if (isset($_POST['vendor_name']) and $_POST['vendor_name'] != '') {
        $sql = $sql . " and c.vendor_name " . LIKE . " '%" . $_POST['vendor_name'] . "%' ";
    }
    if (isset($_POST['vendor_code']) and $_POST['vendor_code'] != '') {
        $sql = $sql . " and c.vendor_code " . LIKE . " '%" . $_POST['vendor_code'] . "%' ";
    }
  
    if (empty($_POST['FromDate']) == 0) {
        $SQL_FromDate = strtotime($_POST['FromDate']);
        $sql .= " and pha.transaction_date >= '" . $SQL_FromDate . "' ";
    }
    if (empty($_POST['ToDate']) == 0) {
        $SQL_ToDate = strtotime($_POST['ToDate']) + 86400;
        //echo $SQL_ToDate;
        $sql .= " and pha.transaction_date <='" . $SQL_ToDate . "' ";
    }
     $sql .= " order by pha.transaction_date  desc";
    $result = DB_query($sql, $db);
    if (DB_num_rows($result) == 0) {
        unset($result);
        prnMsg(_('找不到该发票资料，请重新输入条件查询！'), 'error');
    }
}


echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('供应商付款明细查询') . '</p>';
echo '<table cellpadding="3" class="selection">';
echo '<div class="text-nav">';
echo '<div class="text-nav-1"><div>' . _('流水号码') . ':</div>';
echo '<input type="text" name="transaction_num" value="' . $_POST['transaction_num'] . '" size="20" maxlength="25" /></div>';
echo '<div class="text-nav-1"><div>' . _('付款/转账单号') . ':</div>';
echo '<input type="text" name="bankchangenum" value="' . $_POST['bankchangenum'] . '" size="20" maxlength="25" /></div>';
echo '<div class="text-nav-1"><div>' . _('银行账户名称') . ':</div>';
echo '<input type="text" name="bankaccountname" value="' . $_POST['bankaccountname'] . '" size="20" maxlength="25" /></div>';
echo '<div class="text-nav-1"><div>' . _('供应商名称') . ':</div>';
echo '<input type="text" name="vendor_name" value="' . $_POST['vendor_name'] . '" size="20" maxlength="25" /></div>';

 
if (!isset($_POST['FromDate'])) {
    $_POST['FromDate'] = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 30, date("Y")));
}
if (!isset($_POST['ToDate'])) {
    $_POST['ToDate'] = Date('Y-m-d');
}
echo '<div class="text-nav-1"><div>' . '付款日' . _('起') . ':</div>
		<input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="FromDate" maxlength="10" size="11" value="' . $_POST['FromDate'] . '" /></div>
		<div class="text-nav-1"><div>' . _('付款日止') . ':</div>
		<input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="ToDate" maxlength="10" size="11" value="' . $_POST['ToDate'] . '" /></div>
	';
echo '<div class="text-nav-1"><div>' . _('供应商代码') . ':</div>
';
echo '<input type="text" name="vendor_code" value="' . $_POST['vendor_code'] . '" size="20" maxlength="25" /></div></div>';
echo '</table><div class="centre"><input type="submit" name="Search" value="查找"> </div>'
 . '</br>';

if (isset($_POST['Search']) and isset($result) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])) {
    $ListCount = DB_num_rows($result);
    $ListPageMax = ceil($ListCount / 50);

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
        echo '<br /><div class="centre">&nbsp;&nbsp;' . _('第') . '' . $_POST['PageOffset'] . ' ' . _('页，共') . ' ' . $ListPageMax . ' ' . _('页数') . '. ' . _('转到页') . ': ';
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
    echo '			  <div class="text-nav-table">
                    <table cellpadding="2" class="selection" >';

    echo '<tr>
                    <th class="ascending" width = 100>' . _('流水号') . '</th>
					<th class="ascending" width = 100>' . _('付款/转账单号') . '</th> 
					<th width = 80>' . _('交易类型') . '</th>
                    <th width = 80>' . _('供应商') . '</th>
                    <th class="ascending"width = 200>' . _('供应商') . '</th>
                    <th  width = 90>' . _('付款金额') . '</th>                               
                    <th  width = 150>' . _('备注') . '</th>
                    <th class="ascending"width = 90>' . _('付款日') . '</th>
                    <th class="ascending"width = 180>' . _('建立时间') . '</th>
                    <th class="ascending"width = 80>' . _('建立人员') . '</th>
            </tr>';
    $k = 0; //row counter to determine background colour
    $RowIndex = 0;



    if (DB_num_rows($result) <> 0) {
        DB_data_seek($result, ($_POST['PageOffset'] - 1) * 50);
        $i = 0; //counter for input controls
        while (($myrow = DB_fetch_array($result)) AND ( $RowIndex <> 50 )) {
            if ($k == 1) {
                echo '<tr class="EvenTableRows">';
                $k = 0;
            } else {
                echo '<tr class="OddTableRows">';
                $k = 1;
            }
            unset($v_status);
           
            echo '  <td><a href="' . $RootPath . '/APSearchPayment2.php?New=Yes&Updatepo_num=' . $myrow['transaction_num'] . '">' . $myrow['transaction_num'] . '</td>
				<td>' . $myrow['bankchangenum'] . '</td> 
                <td>' . $myrow['transaction_type'] . '</td> 
                <td>' . $myrow['vendor_code'] . '</td> 
                <td>' . $myrow['vendor_name'] . '</td> 
                <td>' . $myrow['transaction_amount'] . '</td>
				<td>' . $myrow['narrative'] . '</td>
                <td>' . date('Y-m-d', $myrow['transaction_date']) . '</td>
				<td>' . date('Y-m-d H:i:s', $myrow['creation_date']) . '</td>
				<td>'  . $myrow['created_by'] . '</td>
                                 ';
 

            echo '
			</tr>';
            $i++;
            $RowIndex++;
            //end of page full new headings if
        } //end loop through vendors
        echo '</table></div>';
        echo '<input type="hidden" name="JustSelectedAvendor" value="Yes" />';
        echo '<div>
                <a href="' . $RootPath . '/APSearchPaymentExcel.php?bankchangenum=' .$_POST['bankchangenum'] .
                '&transaction_num='  .$_POST['transaction_num'] . 
                '&bankaccountname='  .$_POST['bankaccountname'] . 
                '&vendor_code='  .$_POST['vendor_code'] . 
                '&vendor_name='  .$_POST['vendor_name'] . 
                '&FromDate='  .$_POST['FromDate'] .
                '&ToDate=' .$_POST['ToDate']  .' ">' .'资料导出Excel表' . '</a>
                </div>';
    }

    if (isset($ListPageMax) AND $ListPageMax > 1) {
        echo '<br /><div class="centre">&nbsp;&nbsp;' . _('第') . '' . $_POST['PageOffset'] . ' ' . _('页，共') . ' ' . $ListPageMax . ' ' . _('页数') . '. ' . _('转到页') . ': ';
        echo '<select name="PageOffset2">';
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
                        <input type="submit" name="Go2" value="' . _('转到') . '" />
                        <input type="submit" name="Previous" value="' . _('上一页') . '" />
                        <input type="submit" name="Next" value="' . _('下一页') . '" />';
        echo '</div>';
    }
}
echo '</div></form>';

include('includes/footer.inc');
