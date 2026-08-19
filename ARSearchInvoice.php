<?php

ob_start();
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include('includes/session.inc');
$Title = _('客户发票查询');
$ViewTopic = '客户发票查询';
$BookMark = '客户发票查询';

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
    $sql = "select invoice_name,ar_invoice_type,
			      pha.invoice_num,pha.status,
			   pha.invoice_amount,
			       pha.tax_amount,
                  c.customer_code,
                    pha.remark,
				pha.currency_code,
                 pha.invoice_date,
                pha.creation_date,pha.created_by,pha.customer_code,c.customer_name,pha.tax_code
	from ar_invoice_headers_all pha, customers c
            where  pha.customer_code=c.customer_code   ";

    
    if (isset($_POST['invoice_name']) and $_POST['invoice_name'] != '') {		
        $sql = $sql . " and pha.invoice_name " . LIKE . " '%" . $_POST['invoice_name'] . "%' ";
    }
	if (isset($_POST['invoice_num']) and $_POST['invoice_num'] != '') {		
        $sql = $sql . " and pha.invoice_num " . LIKE . " '%" . $_POST['invoice_num'] . "%' ";
    }
	if (isset($_POST['status']) and $_POST['status'] != '') {		
        $sql = $sql . " and pha.status " . LIKE . " '%" . $_POST['status'] . "%' ";
    }
    if (isset($_POST['customer_name']) and $_POST['customer_name'] != '') {
        $sql = $sql . " and c.customer_name " . LIKE . " '%" . $_POST['customer_name'] . "%' ";
    }
    if (isset($_POST['customer_code']) and $_POST['customer_code'] != '') {
        $sql = $sql . " and c.customer_code " . LIKE . " '%" . $_POST['customer_code'] . "%' ";
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
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('客户发票查询') . '</p>';
 
 $_POST['status']='核准';
 
echo '<table cellpadding="3" class="selection">';
echo '<div class="text-nav">';
echo '<div class="text-nav-1"><div>' . _('发票号码') . ':</div>';
echo '<input type="text" name="invoice_num" value="' . $_POST['invoice_num'] . '" size="20" maxlength="25" /></div>';
echo '<div class="text-nav-1"><div>' . _('发票单号') . ':</div>';
echo '<input type="text" name="invoice_name" value="' . $_POST['invoice_name'] . '" size="20" maxlength="25" /></div>';

echo '<div class="text-nav-1"><div>' . _('客户名称') . ':</div>';
echo '<input type="text" name="customer_name" value="' . $_POST['customer_name'] . '" size="20" maxlength="25" /></div>';
echo '<div class="text-nav-1"><div>' . _('客户代码') . ':</div>
';
echo '<input type="text" name="customer_code" value="' . $_POST['customer_code'] . '" size="20" maxlength="25" /></div>';

echo '<div class="text-nav-1"><div>' . _('发票类型') . ':</div> ';
echo '<input type="text" name="ar_invoice_type" value="' . $_POST['ar_invoice_type'] . '" size="20" maxlength="25" /></div>';
echo '<div class="text-nav-1"><div>' . _('状态') . ':</div> ';
echo '<input type="text" name="status" value="' . $_POST['status'] . '" size="20" maxlength="25" /></div>'; 
if (!isset($_POST['FromDate'])) {
    $_POST['FromDate'] = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 30, date("Y")));
}
if (!isset($_POST['ToDate'])) {
    $_POST['ToDate'] = Date('Y-m-d');
}
echo '<div class="text-nav-1"><div>' . '发票日' . _('起') . ':</div>
		<input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="FromDate" maxlength="10" size="11"  value="' . $_POST['FromDate'] . '" /></div>
		<div class="text-nav-1"><div>' . _('发票日止') . ':</div>
		<input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="ToDate" maxlength="10" size="11"  value="' . $_POST['ToDate'] . '" /></div>
	</div>';

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

    echo '<tr> <th class="ascending" width = 100>' . _('发票单号') . '</th>
                    <th class="ascending" width = 100>' . _('发票号码') . '</th>
                    <th   width = 70>' . _('状态') . '</th>
                    <th   width = 70>' . _('类型') . '</th>
                    <th class="ascending"width = 50>' . _('客户') . '</th>
                    <th class="ascending"width = 250>' . _('客户') . '</th>
                    <th class="ascending"width = 90>' . _('总额') . '</th>  
                    <th class="ascending"width = 90>' . _('税别') . '</th>                          
                    <th class="ascending"width = 250>' . _('备注') . '</th>
                    <th class="ascending"width = 120>' . _('发票日') . '</th>
                    <th class="ascending"width = 180>' . _('建立时间') . '</th>
                    <th class="ascending"width = 80>' . _('建立人员') . '</th>
            </tr>';
    $k = 0; //row counter to determine background colour
    $RowIndex = 0;
    $all_line=0;
    $all_invoice_amount=0;



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
              
            $all_line=$all_line+1;
            $all_invoice_amount=$all_invoice_amount+$myrow['invoice_amount'];
            echo '  <td><a  target="_blank" href="' . $RootPath . '/ARSearchInvoice2.php?Updatepo_num=' . $myrow['invoice_name'] .'">' . $myrow['invoice_name'] . '</td>
			<td>' . $myrow['invoice_num'] . '</td> 
				<td>' . $myrow['status'] . '</td>
				<td>' . $myrow['ar_invoice_type'] . '</td>
                <td>' . $myrow['customer_code'] . '</td> 
                <td>' . $myrow['customer_name'] . '</td> 
                <td>' . $myrow['invoice_amount'] . '</td> 
                <td>' . $myrow['tax_code'] . '</td>
				<td>' . $myrow['remark'] . '</td>
                <td>' . date('Y-m-d', $myrow['invoice_date']) . '</td>
				<td>' . date('Y-m-d H:i:s', $myrow['creation_date']) . '</td>
				<td>'  . $myrow['created_by'] . '</td>
                                 ';
 

            echo '
			</tr>';
            $i++;
            $RowIndex++;
            //end of page full new headings if
        } //end loop through customers
        echo '<tr><td>总计</td><td>笔数</td><td>' . $all_line . '</td><td></td><td>' . $all_invoice_amount . '</td></tr>';
		echo '</table></div>';
        echo '<input type="hidden" name="JustSelectedAvendor" value="Yes" />';
        echo '<div>
                    <a href="' . $RootPath . '/ARSearchInvoiceExcel.php?FromDate=' .$_POST['FromDate'] .
                    '&ToDate=' .$_POST['ToDate'] . '&invoice_num=' .$_POST['invoice_num'] .'&invoice_name=' .$_POST['invoice_name'] .
                    '&customer_name=' .$_POST['customer_name'] .'&status=' .$_POST['status'] . '&customer_code=' .$_POST['customer_code'] .' ">' .'资料导出Excel表' . '</a>
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
