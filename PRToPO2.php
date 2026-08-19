<?php

include ('includes/DefinePRToPOClass.php');

include ('includes/session.inc');
$Title = _('请购单转采购单');
$ViewTopic = '请购单转采购单';
$BookMark = '请购单转采购单';
include ('includes/header.inc');
include ('includes/SQL_CommonFunctions.inc');

if (isset($_GET['New'])) {
    unset($_SESSION['Request']);
    $_SESSION['Request'] = new VendorHeader();
    //if (!isset($_SESSION['Request' . $identifier]->PO)) {
    if (isset($_GET['vendor'])) {
        // $_SESSION['Request' . $identifier]->PO = GetNextTransNo(18, $db);
        if (!isset($_SESSION['Request' . $identifier]->vendor_code) or $_SESSION['Request' .
            $identifier]->vendor_code == '') {
            $vendor = $_GET['vendor'];
            $sql = "select vendor_code,vendor_name from vendors where vendor_id = " . $vendor;
            $CustResult = DB_query($sql, $db);
            while ($myrow = DB_fetch_array($CustResult)) {
                $_SESSION['Request' . $identifier]->vendor_code = $myrow['vendor_code'];
                $_SESSION['Request' . $identifier]->vendor_name = $myrow['vendor_name'];
            }
        }
    } else {
        prnMsg(_('出错，请重新登录！'), 'error');
    }
    //}
}
$display = 10;
if (isset($_POST['Update'])) {

    $_SESSION['Request']->Department = $_POST['Department'];
    if (!isset($_SESSION['Request']->status)) {
        $_SESSION['Request']->status = 'A';
    }
    $_SESSION['Request']->Need_date = $_POST['Need_date'];
    $_SESSION['Request']->Narrative = $_POST['Narrative'];
}

if (isset($_POST['Edit'])) {
    $_SESSION['Request']->LineItems[$_POST['LineNumber']]->Amount = $_POST['Quantity'] *
        $_POST['Price'];
    $_SESSION['Request']->LineItems[$_POST['LineNumber']]->Quantity = $_POST['Quantity'];
}

if (isset($_GET['Delete'])) {
    unset($_SESSION['Request']->LineItems[$_GET['Delete']]);
    echo '<br />';
    prnMsg(_('The line was successfully deleted'), 'success');
    echo '<br />';
}

foreach ($_POST as $key => $value) {
    if (mb_strstr($key, 'item_no')) {
        $Index = mb_substr($key, 7);
        if (filter_number_format($_POST['Quantity' . $Index]) > 0) {
            $item_no = $value;
            $PR = $_POST['pr_num' . $Index];
            $pr_line = $_POST['PR_Line' . $Index];
            $item_no = $_POST['item_no' . $Index];
            $item_name = $_POST['item_name' . $Index];
            $item_desc = $_POST['item_desc' . $Index];
            $PR_quantity = $_POST['PR_quantity' . $Index];
            $NewItem_array[$item_no] = filter_number_format($_POST['Quantity' . $Index]);
            $_POST['Units' . $item_no] = $_POST['Units' . $Index];
            $Price = $_POST['Price' . $Index];
            $Amount = $Price * $NewItem_array[$item_no];
             
            $_SESSION['Request']->AddLine($PR, $pr_line, $item_no, $item_name,$item_desc, $NewItem_array[$item_no],
                $PR_quantity, $Price, $_POST['Units' . $item_no], $Amount);
        }
    }
}

if (isset($_POST['Submit'])) {

    $InputError = 0;
    if ($_SESSION['Request']->Department == '') {
        prnMsg(_('You must select a Department for the request'), 'error');
        $InputError = 1;
    }
    if ($InputError == 0) {
        $date = date('Ymd');
        $sql_num = "select 	(
		CASE WHEN substr(max(po_num) ,-2,1) = 0 THEN
			RIGHT (
				'100' + (
					max(substr(po_num ,- 1)) + 1
				),
				2
			)
		ELSE
			substr(max(po_num),-2,2) + 1
		END
        ) po_num from po_headers_all where substr(po_num,-10,8) = '" . $date .
            "'";
        $result_num = DB_query($sql_num, $db);
        $rownum = DB_num_rows($result_num);
        //    echo $rownum;
        while ($v = DB_fetch_array($result_num)) {
            if ($v['po_num'] == null) {
                $OrderNum = 'PO' . $date . '01';
            } else {
                $OrderNum = 'PO' . $date . $v['po_num'];
            }
        }
        $_SESSION['Request' . $identifier]->PO = $OrderNum;

        $Result = DB_Txn_Begin($db);
        $v_date = strtotime(Date('Y-m-d H:i:s'));
        $v_need_date = strtotime($_SESSION['Request']->Need_date);
        $v_amount = 0;
        foreach ($_SESSION['Request']->LineItems as $LineItems) {
            
		      $price= $LineItems->Price;
			  $quantity= $LineItems->Quantity;
			  $line_amount=$quantity * $price;
			  $v_amount = $v_amount + $line_amount;
            $LineSQL = "INSERT INTO po_lines_all (
                                                    po_num,
                                                    line,
                                                    stockid,
                                                    STATUS,
                                                    price,
                                                    uom,line_amount,
                                                    quantity,
                                                    creation_date,
                                                    created_by,
                                                    last_update_date,
                                                    last_updated_by,
                                                    amount
                                            )
                                        VALUES( '" . $_SESSION['Request']->PO .  "',
                                                '" . $LineItems->LineNumber .  "',
                                                '" . $LineItems->item_no . "',
                                                'INPROCESS',
                                                '" . $LineItems->Price . "',
                                                '" . $LineItems->Units . "', 
                                                '" . $line_amount . "', 
                                                '" . $LineItems->Quantity . "', 
                                                $v_date,
                                                '" . $_SESSION['UserID'] . "',
                                               $v_date,
                                                '" . $_SESSION['UserID'] . "',
                                                $LineItems->Amount
                                               )";
            $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') .
                ': ' . _('The request line record could not be inserted because');
            $DbgMsg = _('The following SQL to insert the request header record was used');
            $Result = DB_query($LineSQL, $db, $ErrMsg, $DbgMsg, true);
            $UpdatePRsql = "update pr_lines_all 
                   set po_num = '" . $_SESSION['Request']->PO . "',
                       po_line= '" . $LineItems->LineNumber . "',
                       last_updated_by =  '" . $_SESSION['UserID'] . "',
                       last_update_date = $v_date
                  where pr_num =  '" . $LineItems->PR . "'" . "and line =  '" .
                $LineItems->Line . "'";
            $Result = DB_query($UpdatePRsql, $db);
        }
        $HeaderSQL = "INSERT INTO po_headers_all (
                                                    po_num,
                                                    STATUS,
                                                    note,
                                                    vendor_code, 
                                                    amount,po_all_amount,po_invoice_amount,	po_payment_amount,
                                                    need_date,
                                                    creation_date,
                                                    created_by,
                                                    last_update_date,
                                                    last_updated_by
                                            )
                                        VALUES( '" . $_SESSION['Request']->PO .
            "',
                                                'INPROCESS',
                                                '" . $_SESSION['Request']-> description . "',
                                                '" . $_SESSION['Request']-> vendor_code . "', 
                                                $v_amount,$v_amount,$v_amount,$v_amount,
                                                $v_need_date,
                                                $v_date,
                                                '" . $_SESSION['UserID'] . "',
                                               $v_date,
                                               '" . $_SESSION['UserID'] . "' )";
        $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') .
            ': ' . _('The request header record could not be inserted because');
        $DbgMsg = _('The following SQL to insert the request header record was used');
        $Result = DB_query($HeaderSQL, $db, $ErrMsg, $DbgMsg, true);

        $Result = DB_Txn_Commit($db);
        prnMsg(_('采购单建立成功！采购单号为：' . $OrderNum), 'success');

        echo '<br /><div class="centre"><a href="' . $RootPath . '/PRToPO.php">' . _('Create another request') .
            '</a></div>';
        include ('includes/footer.inc');
        unset($_SESSION['Request']);
        exit;
    }
}

echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme .
    '/images/supplier.png" title="' . _('Dispatch') . '" alt="" />' . ' ' . $Title .
    '</p>';

if (isset($_GET['Edit'])) {
    echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES,
        'UTF-8') . '" method="post">';
    echo '<div>';
    echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
    echo '<table class="selection">';
    echo '<tr>
			<th colspan="2"><h4>' . _('Edit the Request Line') . '</h4></th>
		</tr>';
    echo '<tr>
			<td>' . _('Line number') . '</td>
			<td>' . $_SESSION['Request']->LineItems[$_GET['Edit']]->LineNumber . '</td>
		</tr>
		<tr>
			<td>' . _('料号') . '</td>
			<td>' . $_SESSION['Request']->LineItems[$_GET['Edit']]->item_no . '</td>
		</tr>
		<tr>
			<td width = 150>' . _('料号名称') . '</td>
			<td>' . $_SESSION['Request']->LineItems[$_GET['Edit']]->item_name . '</td>
		</tr>
		<tr>
			<td width = 150>' . _('规格型号') . '</td>
			<td>' . $_SESSION['Request']->LineItems[$_GET['Edit']]->item_desc . '</td>
		</tr>
		<tr>
			<td>' . _('单位') . '</td>
			<td>' . $_SESSION['Request']->LineItems[$_GET['Edit']]->Units . '</td>
		</tr>
                <tr>
			<td>' . _('单价') . '</td>
			<td>' . $_SESSION['Request']->LineItems[$_GET['Edit']]->Price . '</td>
		</tr>
                <td>' . _('Quantity Requested') . '</td>
			<td><input type="text" class="number" name="Quantity" value="' . $_SESSION['Request']->
        LineItems[$_GET['Edit']]->Quantity . '" /></td>
		';
    echo '<input type="hidden" name="LineNumber" value="' . $_SESSION['Request']->
        LineItems[$_GET['Edit']]->LineNumber . '" />' .
        '<input type="hidden" name="Price" value="' . $_SESSION['Request']->LineItems[$_GET['Edit']]->
        Price . '" />';
    echo '</table>
		<br />';
    echo '<div class="centre">
			<input type="submit" name="Edit" value="' . _('Update Line') . '" />
		</div>
        </div>
		</form>';
    include ('includes/footer.inc');
    exit;
}

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES,
    'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<table class="selection">';
echo '<tr>
		<th colspan="6"><h4>' . _('采购单细节') . '</h4></th>
	</tr>
	<tr>
		<td>' . _('采购单号：') . '</td><td>' . $_SESSION['Request' . $identifier]->PO .
    '</td>' . '<td>' . _('申请人') . ':</td>';
$sql = "SELECT userid,
                         realname
                    FROM www_users
                    where userid = '" . $_SESSION['UserID'] . "'
                    ORDER BY realname";
$result = DB_query($sql, $db);
echo '<td width = "50"><select name="Department">';
while ($myrow = DB_fetch_array($result)) {
    if (isset($result)) {
        echo '<option selected="True" value="' . $myrow['realname'] . '">' .
            htmlspecialchars($myrow['realname'], ENT_QUOTES, 'UTF-8') . '</option>';
    }
}
if (!isset($_SESSION['Request']->Need_date)) {
    $_SESSION['Request']->Need_date = date("Y-m-d", mktime(0, 0, 0, date("m"), date
        ("d") + 1, date("Y")));
}
echo '</select></td>
		<td>' . _('需求日期') . ':</td>';
echo '<td><input type="text"  onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] .
    '"  name="Need_date" maxlength="10" size="11" value="' . $_SESSION['Request']->
    Need_date . '" /></td>
      </tr>';
echo '<tr><td>' . _('供应商编号') . ':</td><td>' . $_SESSION['Request']->vendor_code .
    '</td>
          <td>' . _('供应商名称') . ':</td><td colspan="3">' . $_SESSION['Request']->
    vendor_name . '</td>
        </tr>';
echo '<tr>
		<td>' . _('备注') . ':</td>
		<td colspan="5" ><textarea name="Narrative" cols="50" rows="5">' . $_SESSION['Request']->
    Narrative . '</textarea></td>
	</tr>
	</table>
	<br />';

echo '<div class="centre">
		<input type="submit" name="Update" value="' . _('保存采购单头') . '" />
	</div>
    </div>
	</form>';

if (isset($_SESSION['Request']->status) and $_SESSION['Request']->status == 'B') {
    $i = 0; //Line Item Array pointer
    echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES,
        'UTF-8') . '" method="post">';
    echo '<div>';
    echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
    echo '<br />
	<table class="selection">
	<tr>
		<th colspan="7"><h4>' . _('Details of Items Requested') . '</h4></th>
	</tr>
	<tr>
		<th>' . _('行') . '</th>
		<th>' . _('请购单') . '</th>
                <th>' . _('请购单行') . '</th>
                <th>' . _('料号') . '</th>
                <th width = 150>' . _('料号名称') . '</th>
                <th width = 150>' . _('规格型号') . '</th>
                <th>' . _('单价') . '</th>
                <th>' . _('请购单数量') . '</th>
                <th>' . _('下单量') . '</th>
                <th>' . _('单位') . '</th>
                <th>' . _('合计') . '</th>
                <th>' . _('编辑') . '</th>
                <th>' . _('删除') . '</th>
	</tr>';

    $k = 0;

    if (is_array($_SESSION['Request']->LineItems)) {
        foreach ($_SESSION['Request']->LineItems as $LineItems) {

            if ($k == 1) {
                echo '<tr class="EvenTableRows">';
                $k = 0;
            } else {
                echo '<tr class="OddTableRows">';
                $k++;
            }
            echo '<td>' . $LineItems->LineNumber . '</td>
              <td>' . $LineItems->PR . '</td>
              <td>' . $LineItems->Line . '</td>
              <td>' . $LineItems->item_no . '</td>
              <td>' . $LineItems->item_name . '</td>
              <td>' . $LineItems->item_desc . '</td>
              <td>' . $LineItems->Price . '</td>
              <td>' . $LineItems->PR_quantity . '</td>
              <td>' . $LineItems->Quantity . '</td>
              <td>' . $LineItems->Units . '</td>
              <td>' . $LineItems->Amount . '</td>
              <td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES,
                'UTF-8') . '?Edit=' . $LineItems->LineNumber . '">' . _('Edit') . '</a></td>
              <td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES,
                'UTF-8') . '?Delete=' . $LineItems->LineNumber . '">' . _('Delete') . '</a></td>
        </tr>';
        }
    }
    echo '</table>
	<br />
	<div class="centre">
		<input type="submit" name="Submit" value="' . _('Submit') . '" />
	</div>
	<br />
    </div>
    </form>';
}
if (isset($_SESSION['Request']->status) and $_SESSION['Request']->status == 'B' or
    $_SESSION['Request']->status == 'A') {
    echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES,
        'UTF-8') . '" method="post">';
    echo '<div>';
    echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

    echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme .
        '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('查找请购单') .
        '</p>';
    echo '<table>
			<tr>
				<td>' . '请购单' . _('From') . ':</td>
		<td><input type="text"  name="PRFrom"  value="' . $_POST['PRFrom'] .
        '" /></td>
		<td>' . _('To') . ':</td>
		<td><input type="text"  name="PRTo"  value="' . $_POST['PRTo'] . '" /></td>
	</tr>
	</table>
	<br />
	<div class="centre">
		<input type="submit" name="Search" value="' . _('查询待转请购单') . '" />
	</div>
	<br />
	</div>
	</form>';
} else {
    include ('includes/footer.inc');
    exit;
}


if (isset($_POST['Search']) or isset($_POST['Next']) or isset($_POST['Prev']) or
    isset($_POST['Go'])) {
    $_SESSION['Request']->status = 'B';
    $SQL = "SELECT prh.pr_num, prl.Line, prl.stockid, si.item_no,si.item_name,si.item_desc, si.units, prl.quantity PR_quantity, ifnull( si.po_price, 0 ) po_price
FROM pr_headers_all prh, pr_lines_all prl, sf_item_no si
WHERE prh.pr_num = prl.PR_num
AND prh.STATUS =  'APPROVED'
AND prl.Po_num IS NULL 
AND prl.quantity >0
AND prl.stockid = si.item_no ";
    if (empty($_POST['PRFrom']) == 0) {
        $SQL .= " AND prl.pr_num >=  '" . $_POST['PRFrom'] . "'";
    }
    if (empty($_POST['PRTo']) == 0) {
        $SQL .= " AND prl.pr_num <= '" . $_POST['PRTo'] . "'";
    }
    $SQL .= " order by prh.pr_num,prl.Line   ";
    if (isset($_POST['Next'])) {
        $Offset = $_POST['NextList'];
    }
    if (isset($_POST['Prev'])) {
        $Offset = $_POST['Previous'];
    }
    if (!isset($Offset) or $Offset < 0) {
        $Offset = 0;
    }
    $searchResult = DB_query($SQL, $db, $ErrMsg, $DbgMsg);
    $ListCount = DB_num_rows($searchResult);
    $ListPageMax = ceil($ListCount / $display);
    if ($_POST['Previous'] == -2) {
        $Offset = $ListPageMax + $_POST['Previous'];
    }
    if (isset($_POST['Go']) and isset($_POST['PageOffset'])) {
        $Offset = $_POST['PageOffset'] - 1;
    }
    if ($Offset < 0) {
        $Offset = 0;
    }
    $SQL = $SQL . ' LIMIT ' . $display . ' OFFSET ' . ($display * $Offset);
    $ErrMsg = _('There is a problem selecting the part records to display because');
    $DbgMsg = _('The SQL used to get the part selection was');
    $SearchResult = DB_query($SQL, $db, $ErrMsg, $DbgMsg);

    if (DB_num_rows($SearchResult) == 0) {
        prnMsg(_('There are no products available meeting the criteria specified'),
            'info');
    }
    if (DB_num_rows($SearchResult) < $display) {
        $Offset = -1;
    }
} //end of if search
/* display list if there is more than one record */

if (isset($SearchResult)) {
    $j = 1;
    echo '<br />
		<div class="page_help_text">' . _('通过输入需求数量来选择物料。选择完成并要选择更多的行则点击下一页,若不需要更多选择点增加至申请单.') .
        '</div>
		<br />
		<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') .
        '" method="post" id="orderform">';
    $ListCount = DB_num_rows($searchResult);
    if ($ListCount > 0) {
        // If the user hit the search button and there is more than one item to show
        $ListPageMax = ceil($ListCount / $display);
        if ($ListPageMax > 1) {
            if ($Offset == -1) {
                $count = $ListPageMax;
            } else {
                $count = $Offset + 1;
            }
            echo '<div class="centre"><br />&nbsp;&nbsp; ' . _('当前页数为：第') . '' . $count . '' .
                _('页') . '&nbsp;&nbsp ' . _('总页数为：') . ' ' . $ListPageMax . ' ' . _('页') .
                '&nbsp;&nbsp' . _('跳转至：第') . ' ';
            echo '<select name="PageOffset" >';
            $ListPage = 1;
            while ($ListPage <= $ListPageMax) {
                if ($ListPage == $count) {
                    echo '<option value=' . $ListPage . ' selected>' . $ListPage . '</option>';
                } else {
                    echo '<option value=' . $ListPage . '>' . $ListPage . '</option>';
                }
                $ListPage++;
            }
            echo '</select>页<input type="submit" name="Go" value="' . _('Go') . '" />';
        }
    }
    echo '<div>
		<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
		<table>
		<tr>
			<td>
				<input type="hidden" name="Previous" value="' . ($Offset - 1) . '" />
				<input tabindex="' . ($j + 8) . '" type="submit" name="Prev" value="' . _('Prev') .
        '" /></td>
				<td style="text-align:center" colspan="6">
				<input type="hidden" name="order_items" value="1" />
				<input tabindex="' . ($j + 9) . '" type="submit" value="' . _('Add to Requisition') .
        '" /></td>';
    if ($Offset >= 0) {
        echo '
                            <td>
                                    <input type="hidden" name="NextList" value="' . ($Offset +
            1) . '" />
                                    <input tabindex="' . ($j + 10) .
            '" type="submit" name="Next" value="' . _('Next') . '" /></td>';
    }
    echo '
			</tr>
			<tr>
				<th>' . _('请购单') . '</th>
                                <th>' . _('行') . '</th>
				<th>' . _('料号') . '</th>
				<th width = 150>' . _('料号名称') . '</th>
				
				<th width = 150>' . _('规格型号') . '</th>
                                <th>' . _('请购单数量') . '</th>
                                <th>' . _('下单量') . '</th>
								<th>' . _('单价') . '</th>
                                <th>' . _('单位') . '</th>
			</tr>';
    $ImageSource = _('No Image');

    $k = 0; //row colour counter
    $i = 0;
    while ($myrow = DB_fetch_array($SearchResult)) {
        if ($k == 1) {
            echo '<tr class="EvenTableRows">';
            $k = 0;
        } else {
            echo '<tr class="OddTableRows">';
            $k = 1;
        }
        echo '<td>' . $myrow['pr_num'] . '</td>
              <td>' . $myrow['Line'] . '</td>
              <td>' . $myrow['stockid'] . '</td>
              <td class="number">' . $myrow['item_name'] . '</td> 
              <td class="number">' . $myrow['item_desc'] . '</td> 
              <td class="number">' . $myrow['PR_quantity'] . '</td>
              <td><input class="number" ' . ($i == 0 ? 'autofocus="autofocus"' :
            '') . ' tabindex="' . ($j + 7) . '" type="text" size="6" name="Quantity' . $i .
            '" value="0" />
			  <td><input type="text" name="Price' . $i . '" value="' . $myrow['po_price'] .
            '" /> </td> 
              <td class="number">' . $myrow['units'] . '</td>
               <input type="hidden" name="item_no' . $i . '" value="' . $myrow['item_no'] .
            '" />
              </td>
            </tr>';

        echo '<input type="hidden" name="item_name' . $i . '" value="' . $myrow['item_name'] .
            '" /><input type="hidden" name="item_desc' . $i . '" value="' . $myrow['item_desc'] .
            '" />
              <input type="hidden" name="PR_quantity' . $i . '" value="' . $myrow['PR_quantity'] .
            '" />             
              <input type="hidden" name="PR_Line' . $i . '" value="' . $myrow['Line'] .
            '" />
              <input type="hidden" name="pr_num' . $i . '" value="' . $myrow['pr_num'] .
            '" />  ';
        echo '<input type="hidden" name="Units' . $i . '" value="' . $myrow['units'] .
            '" />';
        $i++;
    }
    #end of while loop
    echo '<tr>
			<td><input type="hidden" name="Previous" value="' . ($Offset - 1) . '" />
				<input tabindex="' . ($j + 7) . '" type="submit" name="Prev" value="' . _('Prev') .
        '" /></td>
			<td style="text-align:center" colspan="6"><input type="hidden" name="order_items" value="1" />
				<input tabindex="' . ($j + 8) . '" type="submit" value="' . _('Add to Requisition') .
        '" /></td>';
    if ($Offset >= 0) {
        echo '
                            <td>
                                    <input type="hidden" name="NextList" value="' . ($Offset +
            1) . '" />
                                    <input tabindex="' . ($j + 10) .
            '" type="submit" name="Next" value="' . _('Next') . '" /></td>';
    }
    echo '	
		<tr/>
		</table>
       </div>
       </form>';
} #end if SearchResults to show
//*********************************************************************************************************
include ('includes/footer.inc');
?>
