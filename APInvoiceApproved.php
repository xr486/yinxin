<?php

ob_start();
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include('includes/session.inc');
$Title = _('供应商发票签核');
$ViewTopic = '供应商发票签核';
$BookMark = '供应商发票签核';

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
    $sql = "select pha.invoice_name,ap_invoice_type,invoice_type,
			      pha.invoice_num,
			   pha.invoice_amount,
			       pha.tax_amount,
                  c.vendor_code,
                    pha.narrative,
				pha.currency_code,
                 pha.invoice_date,
                pha.creation_date,pha.created_by,pha.vendor_code,c.vendor_name,pha.status
	from ap_invoice_headers_all pha, vendors c
            where pha.status='建立'
			and pha.vendor_code=c.vendor_code ";

    
    if (isset($_POST['invoice_num']) and $_POST['invoice_num'] != '') {		
        $sql = $sql . " and pha.invoice_num " . LIKE . " '%" . $_POST['invoice_num'] . "%' ";
    }
    if (isset($_POST['vendor_name']) and $_POST['vendor_name'] != '') {
        $sql = $sql . " and c.vendor_name " . LIKE . " '%" . $_POST['vendor_name'] . "%' ";
    }
    if (isset($_POST['vendor_code']) and $_POST['vendor_code'] != '') {
        $sql = $sql . " and c.vendor_code " . LIKE . " '%" . $_POST['vendor_code'] . "%' ";
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
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('供应商发票审核') . '</p>';
echo '<table cellpadding="3" class="selection">
<div class="text-nav">';

echo '<div class="text-nav-1"><div>' . _('发票号码') . ':</div>';
echo '<input type="text" name="invoice_num" value="' . $_POST['invoice_num'] . '" size="20" maxlength="25" /></div>';

echo '<div class="text-nav-2"><div>' . _('供应商名称') . ':</div>';
echo '<input type="text" name="vendor_name" value="' . $_POST['vendor_name'] . '" size="20" maxlength="25" /></div>';
echo '<div class="text-nav-1"><div>' . _('供应商代码') . ':</div>
';
echo '<input type="text" name="vendor_code" value="' . $_POST['vendor_code'] . '" size="20" maxlength="25" /></div>';

 
if (!isset($_POST['FromDate'])) {
    $_POST['FromDate'] = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 30, date("Y")));
}
if (!isset($_POST['ToDate'])) {
    $_POST['ToDate'] = Date('Y-m-d');
}
echo '<div class="text-nav-1"><div>' . '发票日' . _('起') . ':</div>
		<input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" autocomplete="off" name="FromDate" maxlength="10" size="11" value="" /></div>
		<div class="text-nav-1"><div>' . _('发票日止') . ':</div>
		<input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" autocomplete="off" name="ToDate" maxlength="10" size="11" value="" /></div>
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
                    <th class="ascending" width = 100>' . _('发票序号') . '</th>     
					<th class="ascending" width = 100>' . _('发票号码') . '</th>
					<th   width = 70>' . _('发票类型') . '</th>
                    <th   width = 70>' . _('状态') . '</th>
					<th   width = 70>' . _('类型') . '</th>
                    <th class="ascending"width = 50>' . _('供应商') . '</th>
                    <th class="ascending"width = 250>' . _('供应商') . '</th>
                    <th class="ascending"width = 90>' . _('总额') . '</th>             
<th class="ascending"width = 250>' . _('备注') . '</th>
                    <th class="ascending"width = 120>' . _('发票日') . '</th>
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
           if ($myrow['invoice_type']=='红字发票') {
		    echo '  <td><a href="' . $RootPath . '/APInvoiceApproved3.php?Updatepo_num=' . $myrow['invoice_name'] . '">' . $myrow['invoice_name'] . '</td>';
		   } else if ($myrow['invoice_type']=='费用发票') {
            echo '  <td><a href="' . $RootPath . '/APInvoiceApproved4.php?Updatepo_num=' . $myrow['invoice_name'] . '">' . $myrow['invoice_name'] . '</td>';
		   } else {
            echo '  <td><a href="' . $RootPath . '/APInvoiceApproved2.php?Updatepo_num=' . $myrow['invoice_name'] . '">' . $myrow['invoice_name'] . '</td>';
		   }
				echo '<td>' . $myrow['invoice_num'] . '</td>
                <td>' . $myrow['ap_invoice_type'] . '</td>
				<td>' . $myrow['status'] . '</td>
                <td>' . $myrow['invoice_type'] . '</td>
                <td>' . $myrow['vendor_code'] . '</td> 
                <td>' . $myrow['vendor_name'] . '</td> 
                <td>' . $myrow['invoice_amount'] . '</td>
				<td>' . $myrow['narrative'] . '</td>
                <td>' . date('Y-m-d', $myrow['invoice_date']) . '</td>
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
