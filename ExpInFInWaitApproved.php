<?php

ob_start();
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include('includes/session.inc');
$Title = _('其它收入/支出待财务审核');
$ViewTopic = '其它收入/支出待财务审核';
$BookMark = '其它收入/支出待财务审核';

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
							   pha.transaction_amount,
								   pha.tax_amount,
                                  pha.other_person,
                                    pha.narrative,
								pha.currency_code,
                                 pha.transaction_date,
                pha.creation_date,pha.created_by,pha.status,a.type_code
	from fin_bank_transaction_headers_all pha,fin_exp_types a
            where pha.status='主管核准'
			and pha.transaction_type=a.exp_type_name
			and type_code IN ('收入','支出' )
			";

    
    if (isset($_POST['bankchangenum']) and $_POST['bankchangenum'] != '') {		
        $sql = $sql . " and pha.bankchangenum " . LIKE . " '%" . $_POST['bankchangenum'] . "%' ";
    }
	if (isset($_POST['transaction_num']) and $_POST['transaction_num'] != '') {		
        $sql = $sql . " and pha.transaction_num " . LIKE . " '%" . $_POST['transaction_num'] . "%' ";
    }
	if (isset($_POST['bankaccountname']) and $_POST['bankaccountname'] != '') {		
        $sql = $sql . " and pha.bankaccountname " . LIKE . " '%" . $_POST['bankaccountname'] . "%' ";
    }
   
    if (isset($_POST['other_person']) and $_POST['other_person'] != '') {
        $sql = $sql . " and pha.other_person " . LIKE . " '%" . $_POST['other_person'] . "%' ";
    }
  if (isset($_POST['type_code']) and $_POST['type_code'] != '') {
        $sql = $sql . " and  a.type_code " . LIKE . " '%" . $_POST['type_code'] . "%' ";
    }
    if (empty($_POST['FromDate']) == 0) {
        $SQL_FromDate = strtotime($_POST['FromDate']);
        $sql .= " and pha.invoice_date >= '" . $SQL_FromDate . "' ";
    }
    if (empty($_POST['ToDate']) == 0) {
        $SQL_ToDate = strtotime($_POST['ToDate']) + 86400;
        //echo $SQL_ToDate;
        $sql .= " and pha.invoice_date <='" . $SQL_ToDate . "' ";
    }
 
    $result = DB_query($sql, $db);
    if (DB_num_rows($result) == 0) {
        unset($result);
        prnMsg(_('找不到该发票资料，请重新输入条件查询！'), 'error');
    }
}


echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('其它收入/支出待财务审核') . '</p>';
echo '<table cellpadding="3" class="selection">';

echo '<tr><td >' . _('流水号码') . ':</td><td>';
echo '<input type="text" name="transaction_num" value="' . $_POST['transaction_num'] . '" size="20" maxlength="25" /></td>';
echo '<td >' . _('付款/转账单号') . ':</td><td>';
echo '<input type="text" name="bankchangenum" value="' . $_POST['bankchangenum'] . '" size="20" maxlength="25" /></td>';
echo '<td >' . _('银行账户名称') . ':</td><td>';
echo '<input type="text" name="bankaccountname" value="' . $_POST['bankaccountname'] . '" size="20" maxlength="25" /></td>';
echo '<td >' . _('费用类别') . ':</td><td>';
echo '<input type="text" name="type_code" value="' . $_POST['type_code'] . '" size="20" maxlength="25" /></td>';

echo '</tr>';
 
if (!isset($_POST['FromDate'])) {
    $_POST['FromDate'] = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 30, date("Y")));
}
if (!isset($_POST['ToDate'])) {
    $_POST['ToDate'] = Date('Y-m-d');
}
echo '<tr><td >' . '日期' . _('From') . ':</td>
		<td><input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="FromDate" maxlength="10" size="11" value="" /></td>
		<td>' . _('To') . ':</td>
		<td><input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="ToDate" maxlength="10" size="11" value="" /></td>
	';
echo '<td>' . _('单位/个人名称') . ':</td>
	<td>';
echo '<input type="text" name="other_person" value="' . $_POST['other_person'] . '" size="20" maxlength="25" /></td></tr>';
echo '</table><div class="centre"><input type="submit" name="Search" value="查找"> </div>'
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
    echo '
                    <table cellpadding="2" class="selection" >';

    echo '<tr>
                    <th class="ascending" width = 100>' . _('流水号') . '</th>
					<th class="ascending" width = 100>' . _('付款/转账单号') . '</th> 
					<th  >' . _('费用类别') . '</th>
					<th class="ascending" width = 100>' . _('类型') . '</th> 
                    <th  >' . _('单位/个人名称') . '</th>
                    <th  >' . _('银行账户名称') . '</th>
                    <th  width = 90>' . _('金额') . '</th>    
                    <th  width = 90>' . _('币别') . '</th>                             
                    <th  width = 150>' . _('备注') . '</th>
                    <th class="ascending"width = 90>' . _('日期') . '</th>
                    <th class="ascending"width = 160>' . _('建单时间') . '</th>
                    <th class="ascending"width = 80>' . _('建单人员') . '</th>
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
           
			   echo '  <td><a href="' . $RootPath . '/ExpInFInWaitApproved2.php?New=Yes&Updatepo_num=' . $myrow['transaction_num'] . '" target="_blank">' . $myrow['transaction_num'] . '</td> '; 
		  
          

			 echo '	<td>' . $myrow['bankchangenum'] . '</td> 
                <td>' . $myrow['type_code'] . '</td> 
                <td>' . $myrow['transaction_type'] . '</td> 
                <td>' . $myrow['other_person'] . '</td> 
                <td>' . $myrow['bankaccountname'] . '</td> 
                <td>' . $myrow['transaction_amount'] . '</td>
                <td>' . $myrow['currency_code'] . '</td>
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

include('includes/footer.inc');
