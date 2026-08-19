<?php

ob_start();
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include('includes/session.inc');
$Title = _('客户收款管理');
$ViewTopic = '客户收款管理';
$BookMark = '客户收款管理';

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
    $sql = "select pha.transaction_type,
			      pha.bankaccountname,
							    pha.bankchangenum,
							      pha.transaction_num,
							   pha.transaction_amount,dis_amount,
								   pha.tax_amount,
                                  pha.customer_code,
                                    pha.narrative,
								pha.currency_code,
                                 pha.transaction_date,
                pha.creation_date,pha.created_by,pha.customer_code,c.customer_name,pha.status
	from fin_bank_transaction_headers_all pha, customers c
            where pha.transaction_type in ('AR收款','AR预收款','AR退款')
			and pha.customer_code=c.customer_code ";

    
    if (isset($_POST['bankchangenum']) and $_POST['bankchangenum'] != '') {		
        $sql = $sql . " and pha.bankchangenum " . LIKE . " '%" . $_POST['bankchangenum'] . "%' ";
    }
	if (isset($_POST['transaction_num']) and $_POST['transaction_num'] != '') {		
        $sql = $sql . " and pha.transaction_num " . LIKE . " '%" . $_POST['transaction_num'] . "%' ";
    }
	if (isset($_POST['bankaccountname']) and $_POST['bankaccountname'] != '') {		
        $sql = $sql . " and pha.bankaccountname " . LIKE . " '%" . $_POST['bankaccountname'] . "%' ";
    }
    if (isset($_POST['customer_name']) and $_POST['customer_name'] != '') {
        $sql = $sql . " and c.customer_name " . LIKE . " '%" . $_POST['customer_name'] . "%' ";
    }
    if (isset($_POST['customer_code']) and $_POST['customer_code'] != '') {
        $sql = $sql . " and c.customer_code " . LIKE . " '%" . $_POST['customer_code'] . "%' ";
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
    $sql .= " order by pha.creation_date desc ";
    $result = DB_query($sql, $db);
    if (DB_num_rows($result) == 0) {
        unset($result);
        prnMsg(_('找不到该发票资料，请重新输入条件查询！'), 'error');
    }
}


echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('客户收款管理') . '</p>';
echo '<table cellpadding="3" class="selection">';
echo '<div class="text-nav">';
echo '<div class="text-nav-1"><div>' . _('流水号码') . ':</div>';
echo '<input type="text" name="transaction_num" value="' . $_POST['transaction_num'] . '" size="20" maxlength="25" /></div>';
echo '<div class="text-nav-1"><div>' . _('收款/转账单号') . ':</div>';
echo '<input type="text" name="bankchangenum" value="' . $_POST['bankchangenum'] . '" size="20" maxlength="25" /></div>';
echo '<div class="text-nav-1"><div>' . _('银行账户名称') . ':</div>';
echo '<input type="text" name="bankaccountname" value="' . $_POST['bankaccountname'] . '" size="20" maxlength="25" /></div>';
echo '<div class="text-nav-1"><div>' . _('客户名称') . ':</div>';
echo '<input type="text" name="customer_name" value="' . $_POST['customer_name'] . '" size="20" maxlength="25" /></div>';

if (!isset($_POST['FromDate'])) {
    $_POST['FromDate'] = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 30, date("Y")));
}
if (!isset($_POST['ToDate'])) {
    $_POST['ToDate'] = Date('Y-m-d');
}
echo '<div class="text-nav-1"><div>' . '收款日' . _('起') . ':</div>
		<input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" autocomplete="off" name="FromDate" maxlength="10" size="11" value="" /></div>
		<div class="text-nav-1"><div>' . _('收款日止') . ':</div>
		<input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" autocomplete="off" name="ToDate" maxlength="10" size="11" value="" /></div>
	';
echo '<div class="text-nav-1"><div>' . _('客户代码') . ':</div>
';
echo '<input type="text" name="customer_code" value="' . $_POST['customer_code'] . '" size="20" maxlength="25" /></div></div>';
echo '</table><div class="centre"><input type="submit" name="Search" value="查找">&nbsp;&nbsp;
<input type="submit" name="add_new" value="新增收款">&nbsp;&nbsp;
<input type="submit" name="add_fee" value="新增退款">&nbsp;&nbsp;
<input type="submit" name="yu_fee" value="新增预收款"> </div>'
 . '</br>';

if (isset($_POST['Search']) and isset($result) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])) {
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
    echo '			  <div class="text-nav-table">
                    <table cellpadding="2" class="selection" >';

    echo '<tr>
                    <th class="ascending" width = 100>' . _('流水号') . '</th>
					<th class="ascending" width = 100>' . _('收款/转账单号') . '</th> 
                    <th  >' . _('类型') . '</th>
                    <th  >' . _('状态') . '</th>
                    <th width = 80>' . _('客户简称') . '</th>
                    <th class="ascending"width = 200>' . _('客户名称') . '</th>
                    <th  width = 90>' . _('收款金额') . '</th>    
                    <th  width = 90>' . _('免收款金额') . '</th>                             
                    <th  width = 150>' . _('备注') . '</th>
                    <th class="ascending"width = 90>' . _('收款日') . '</th>
                    <th class="ascending"width = 180>' . _('建立时间') . '</th>
                    <th class="ascending"width = 80>' . _('建立人员') . '</th>
            </tr>';
    $k = 0; //row counter to determine background colour
    $RowIndex = 0;



    if (DB_num_rows($result) <> 0) {
        DB_data_seek($result, ($_POST['PageOffset'] - 1) * 10);
        $i = 0; //counter for input controls
        while (($myrow = DB_fetch_array($result)) AND ( $RowIndex <> 10 )) {
            if ($k == 1) {
                echo '<tr class="EvenTableRows">';
                $k = 0;
            } else {
                echo '<tr class="OddTableRows">';
                $k = 1;
            }
            unset($v_status);
            if ($myrow['transaction_type']=='AR退款') {
			echo '  <td><a href="' . $RootPath . '/ARPaymentModify3.php?New=Yes&Updateorder_number=' . $myrow['transaction_num'] . '">' . $myrow['transaction_num'] . '</td>';
			} else if ($myrow['transaction_type']=='AR预收款') {
			echo '  <td><a href="' . $RootPath . '/ARPaymentModify4.php?New=Yes&Updateorder_number=' . $myrow['transaction_num'] . '">' . $myrow['transaction_num'] . '</td>';
			}
            else {
				echo '  <td><a href="' . $RootPath . '/ARPaymentModify2.php?New=Yes&Updateorder_number=' . $myrow['transaction_num'] . '">' . $myrow['transaction_num'] . '</td>';
			}

				 echo '<td>' . $myrow['bankchangenum'] . '</td> 
                <td>' . $myrow['transaction_type'] . '</td> 
                <td>' . $myrow['status'] . '</td> 
                <td>' . $myrow['customer_code'] . '</td> 
                <td>' . $myrow['customer_name'] . '</td> 
                <td>' . $myrow['transaction_amount'] . '</td>
                <td>' . $myrow['dis_amount'] . '</td>
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
        } //end loop through customers
        echo '</table></div>';
        echo '<input type="hidden" name="JustSelectedAvendor" value="Yes" />';
    }

    if (isset($ListPageMax) AND $ListPageMax > 1) {
        echo '<br /><div class="centre">&nbsp;&nbsp;' . _('第') . '' . $_POST['PageOffset'] . ' ' . _('页，共') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ': ';
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
                        <input type="submit" name="Go2" value="' . _('Go') . '" />
                        <input type="submit" name="Previous" value="' . _('Previous') . '" />
                        <input type="submit" name="Next" value="' . _('Next') . '" />';
        echo '</div>';
    }
}
echo '</div></form>';
if (isset($_POST['add_new'])) {
        header('Location: ARPayment.php?New=Y');
}
if (isset($_POST['add_fee'])) {
        header('Location: ARPaymentBack.php?New=Y');
}
if (isset($_POST['yu_fee'])) {
        header('Location: ARPreReceive.php?New=Y');
}
include('includes/footer.inc');
