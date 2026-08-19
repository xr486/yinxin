<?php

/* $Id: InternalStockRequest.php 4576 2011-05-27 10:59:20Z daintree $ */

include('includes/DefineStockRequestClass.php');

include('includes/session.inc');
$Title = _('请购单申请');
$ViewTopic = 'Inventory';
$BookMark = 'CreateRequest';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');


if (isset($_GET['New']) ) {
    unset($_SESSION['Request']);
     
    $_SESSION['Request'] = new StockRequest();
    //echo $_SESSION['Request'.$identifier]->pr;
    if(!isset($_SESSION['Request'.$identifier]->pr)){
    $_SESSION['Request'.$identifier]->pr = GetNextTransNo(19, $db);
    //echo  $_SESSION['Request'.$identifier]->pr;
    }
}
$display = 5;
if (isset($_POST['Update'])) {

        $_SESSION['Request']->Department = $_POST['Department'];
        $_SESSION['Request']->Location = 'A';
        $_SESSION['Request']->Need_date = $_POST['Need_date'];
        $_SESSION['Request']->Narrative = $_POST['Narrative'];

}

if (isset($_POST['Edit'])) {
    $_SESSION['Request']->LineItems[$_POST['LineNumber']]->Quantity = $_POST['Quantity'];
}

if (isset($_GET['Delete'])) {
    unset($_SESSION['Request']->LineItems[$_GET['Delete']]);
    echo '<br />';
    prnMsg(_('The line was successfully deleted'), 'success');
    echo '<br />';
}

foreach ($_POST as $key => $value) {
    if (mb_strstr($key, 'StockID')) {
        $Index = mb_substr($key, 7);
        if (filter_number_format($_POST['Quantity' . $Index]) > 0) {
            $StockID = $value;
            $ItemDescription = $_POST['ItemDescription' . $Index];
            $NewItem_array[$StockID] = filter_number_format($_POST['Quantity' . $Index]);
            $_POST['Units' . $StockID] = $_POST['Units' . $Index];
//            echo $StockID;
//            echo $ItemDescription;
//            echo  $NewItem_array[$StockID];
//            echo $_POST['Units' . $StockID];
            $_SESSION['Request']->AddLine($StockID, $ItemDescription, $NewItem_array[$StockID], $_POST['Units' . $StockID]);
        }
    }
}

if (isset($_POST['Submit'])) {
    DB_Txn_Begin($db);
    $InputError = 0;
    if ($_SESSION['Request']->Department == '') {
        prnMsg(_('You must select a Department for the request'), 'error');
        $InputError = 1;
    }
    if ($InputError == 0) {
         $v_date = strtotime(Date('Y-m-d H:i:s'));
         $v_need_date = strtotime($_SESSION['Request']->Need_date);
        $HeaderSQL = "insert into pr_headers_all(pr_num,
                                                 status,
                                                 description,
                                                 need_date,
                                                 created_by,
                                                 creation_date)
                                        VALUES( '" . $_SESSION['Request']->pr . "',
                                                'INPROCESS',
                                                '" . $_SESSION['Request']->Narrative . "',
                                                $v_need_date,
                                                '" . $_SESSION['UserID'] . "',
                                               $v_date)";
        $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The request header record could not be inserted because');
        $DbgMsg = _('The following SQL to insert the request header record was used');
        $Result = DB_query($HeaderSQL, $db, $ErrMsg, $DbgMsg, true);

        foreach ($_SESSION['Request']->LineItems as $LineItems) {
            $LineSQL = "insert into pr_lines_all(pr_num,
                                                 status,
                                                 line,
                                                 item,
                                                 item_desc,
                                                 uom,
                                                 quantity,
                                                 need_date,
                                                 created_by,
                                                 creation_date,
                                                 last_update_by,
                                                 last_update_date)
                                        VALUES( '" . $_SESSION['Request']->pr . "',
                                                'INPROCESS',
                                                '" . $LineItems->LineNumber . "',
                                                '" . $LineItems->StockID . "',
                                                '" . $LineItems->ItemDescription . "', 
                                                '" . $LineItems->UOM . "', 
                                                '" . $LineItems->Quantity . "',
                                                $v_need_date,
                                                 '" . $_SESSION['UserID'] . "',
                                               $v_date,
                                                '" . $_SESSION['UserID'] . "',
                                               $v_date)";
            $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The request line record could not be inserted because');
            $DbgMsg = _('The following SQL to insert the request header record was used');
            $Result = DB_query($LineSQL, $db, $ErrMsg, $DbgMsg, true);
        }
    }
    DB_Txn_Commit($db);
    prnMsg(_('请购单已建立！'), 'success');
    echo '<br /><div class="centre"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?New=Yes">' . _('Create another request') . '</a></div>';
    include('includes/footer.inc');
    unset($_SESSION['Request']);
    exit;
}

echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . _('Dispatch') .
 '" alt="" />' . ' ' . $Title . '</p>';

if (isset($_GET['Edit'])) {
    echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
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
			<td>' . $_SESSION['Request']->LineItems[$_GET['Edit']]->StockID . '</td>
		</tr>
		<tr>
			<td width = 150>' . _('Item Description') . '</td>
			<td>' . $_SESSION['Request']->LineItems[$_GET['Edit']]->ItemDescription . '</td>
		</tr>
		<tr>
			<td>' . _('Unit of Measure') . '</td>
			<td>' . $_SESSION['Request']->LineItems[$_GET['Edit']]->UOM . '</td>
		</tr>
                <td>' . _('Quantity Requested') . '</td>
			<td><input type="text" class="number" name="Quantity" value="'.$_SESSION['Request']->LineItems[$_GET['Edit']]->Quantity  . '" /></td>
		';
    echo '<input type="hidden" name="LineNumber" value="' . $_SESSION['Request']->LineItems[$_GET['Edit']]->LineNumber . '" />';
    echo '</table>
		<br />';
    echo '<div class="centre">
			<input type="submit" name="Edit" value="' . _('Update Line') . '" />
		</div>
        </div>
		</form>';
    include('includes/footer.inc');
    exit;
}

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<table class="selection">';
echo '<tr>
		<th colspan="6"><h4>' . _('请购单细节') . '</h4></th>
	</tr>
	<tr>
		<td>' . _('请购单号：') . '</td><td>' .$_SESSION['Request'.$identifier]->pr . '</td>'
        . '<td>' . _('申请人') . ':</td>';
          $sql = "SELECT userid,
                         realname
                    FROM www_users
                    where userid = '" . $_SESSION['UserID'] . "'
                    ORDER BY realname";
$result = DB_query($sql, $db);
echo '<td width = "50"><select name="Department">';
while ($myrow = DB_fetch_array($result)) {
    if (isset($result)) {
        echo '<option selected="True" value="' . $myrow['realname'] . '">' . htmlspecialchars($myrow['realname'], ENT_QUOTES, 'UTF-8') . '</option>';
    }
}
if (!isset($_SESSION['Request']->Need_date)) {
    $_SESSION['Request']->Need_date = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 30, date("Y")));
}
echo '</select></td>
		<td>' . _('需求日期') . ':</td>';
echo '<td><input type="text"  onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '"  name="Need_date" maxlength="10" size="11" value="' . $_SESSION['Request']->Need_date . '" /></td>
      </tr>';

echo '<tr>
		<td>' . _('Narrative') . ':</td>
		<td colspan="5" ><textarea name="Narrative" cols="50" rows="5">' . $_SESSION['Request']->Narrative . '</textarea></td>
	</tr>
	</table>
	<br />';

echo '<div class="centre">
		<input type="submit" name="Update" value="' . _('更新') . '" />
	</div>
    </div>
	</form>';

if (!isset($_SESSION['Request']->Location)) {
    include('includes/footer.inc');
    exit;
}

$i = 0; //Line Item Array pointer
echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<br />
	<table class="selection">
	<tr>
		<th colspan="7"><h4>' . _('Details of Items Requested') . '</h4></th>
	</tr>
	<tr>
		<th>' . _('Line Number') . '</th>
		<th class="ascending">' . _('料号') . '</th>
		<th class="ascending" width = 150>' . _('Item Description') . '</th>
		<th class="ascending">' . _('Quantity Required') . '</th>
		<th>' . _('UOM') . '</th>
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
			<td>' . $LineItems->StockID . '</td>
			<td>' . $LineItems->ItemDescription . '</td>
                        <td>' . $LineItems->Quantity . '</td>
                        <td>' . $LineItems->UOM . '</td>
			<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?Edit=' . $LineItems->LineNumber . '">' . _('Edit') . '</a></td>
			<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?Delete=' . $LineItems->LineNumber . '">' . _('Delete') . '</a></td>
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

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('查找料号') . '</p>';
echo '<table>
			<tr>
				<td>' . '采购单' . _('From') . ':</td>
		<td><input type="text"  name="ItemFrom"  value="' . $_POST['ItemFrom'] . '" /></td>
		<td>' . _('To') . ':</td>
		<td><input type="text"  name="ItemTo"  value="' . $_POST['ItemTo'] . '" /></td>
	</tr>
	</table>
	<br />
	<div class="centre">
		<input type="submit" name="Search" value="' . _('查询') . '" />
	</div>
	<br />
	</div>
	</form>';

if (isset($_POST['Search']) or isset($_POST['Next']) or isset($_POST['Prev'])  or isset($_POST['Go'])) {
  // echo $_POST['ItemFrom'];
    $SQL = "select item_no stockid,item_desc description,units stockunits from sf_item_no where 1 = 1";
      if (empty($_POST['ItemFrom']) == 0) {
        $SQL .= " AND item_no >=  '" . $_POST['ItemFrom'] . "'";
    }
    if (empty($_POST['ItemTo']) == 0) {
        $SQL .= " AND item_no <= '" . $_POST['ItemTo'] . "'";
    }

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
        prnMsg(_('There are no products available meeting the criteria specified'), 'info');
    }
    if (DB_num_rows($SearchResult) < $display) {
        $Offset = -1;
    }
} //end of if search
/* display list if there is more than one record */
if (isset($searchresult) AND ! isset($_POST['Select'])) {
    echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
    echo '<div>';
    echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
    $ListCount = DB_num_rows($searchresult);
    if ($ListCount > 0) {
        // If the user hit the search button and there is more than one item to show
        $ListPageMax = ceil($ListCount / $display);
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
        if ($_POST['PageOffset'] > $ListPageMax) {
            $_POST['PageOffset'] = $ListPageMax;
        }
        if ($ListPageMax > 1) {
            echo '<div class="centre"><br />&nbsp;&nbsp;' . $_POST['PageOffset'] . ' ' . _('of') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ': ';
            echo '<select name="PageOffset">';
            $ListPage = 1;
            while ($ListPage <= $ListPageMax) {
                if ($ListPage == $_POST['PageOffset']) {
                    echo '<option value=' . $ListPage . ' selected>' . $ListPage . '</option>';
                } else {
                    echo '<option value=' . $ListPage . '>' . $ListPage . '</option>';
                }
                $ListPage++;
            }
            echo '</select>
				<input type="submit" name="Go" value="' . _('Go') . '" />
				<input type="submit" name="Previous" value="' . _('Previous') . '" />
				<input type="submit" name="Next" value="' . _('Next') . '" />
				<input type="hidden" name=Keywords value="' . $_POST['Keywords'] . '" />
				<input type="hidden" name=StockCat value="' . $_POST['StockCat'] . '" />
				<input type="hidden" name=StockCode value="' . $_POST['StockCode'] . '" />
				<br />
				</div>';
        }
        echo '<table cellpadding="2">';
        echo '<tr>
				<th>' . _('料号') . '</th>
				<th width = 150>' . _('Description') . '</th>
				<th>' . _('Total Qty On Hand') . '</th>
				<th>' . _('Units') . '</th>
				<th>' . _('Stock Status') . '</th>
			</tr>';
        $j = 1;
        $k = 0; //row counter to determine background colour
        $RowIndex = 0;
        if (DB_num_rows($searchresult) <> 0) {
            DB_data_seek($searchresult, ($_POST['PageOffset'] - 1) * $display);
        }
        while (($myrow = DB_fetch_array($searchresult)) AND ( $RowIndex <> $display)) {
            if ($k == 1) {
                echo '<tr class="EvenTableRows">';
                $k = 0;
            } else {
                echo '<tr class="OddTableRows">';
                $k++;
            }

            if ($myrow['discontinued'] == 1) {
                $ItemStatus = '<p class="bad">' . _('Obsolete') . '</p>';
            } else {
                $ItemStatus = '';
            }

            echo '<td><input type="submit" name="Select" value="' . $myrow['stockid'] . '" /></td>
					<td width = 120>' . $myrow['description'] . '</td>
					<td class="number">' . $qoh . '</td>
					<td>' . $myrow['units'] . '</td>
					<td><a target="_blank" href="' . $RootPath . '/StockStatus.php?StockID=' . $myrow['stockid'] . '">' . _('View') . '</a></td>
					<td>' . $ItemStatus . '</td>
				</tr>';
            //end of page full new headings if
        }
        //end of while loop
        echo '</table>
              </div>
              </form>
              <br />';
    }
}
/* end display list if there is more than one record */

if (isset($SearchResult)) {
    $j = 1;
    echo '<br />
		<div class="page_help_text">' . _('Select an item by entering the quantity required.  Click Order when ready.') . '</div>
		<br />
		<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post" id="orderform">';
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
            echo '<div class="centre"><br />&nbsp;&nbsp; ' . _('当前页数为：第') . '' . $count . '' . _('页') . '&nbsp;&nbsp ' . _('总页数为：') . ' ' . $ListPageMax . ' ' . _('页') . '&nbsp;&nbsp' . _('跳转至：第') . ' ';
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
				<input tabindex="' . ($j + 8) . '" type="submit" name="Prev" value="' . _('Prev') . '" /></td>
				<td style="text-align:center" colspan="6">
				<input type="hidden" name="order_items" value="1" />
				<input tabindex="' . ($j + 9) . '" type="submit" value="' . _('Add to Requisition') . '" /></td>';
    if ($Offset >= 0) {
        echo '
                            <td>
                                    <input type="hidden" name="NextList" value="' . ($Offset + 1) . '" />
                                    <input tabindex="' . ($j + 10) . '" type="submit" name="Next" value="' . _('Next') . '" /></td>';
    }
    echo '
			</tr>
			<tr>
				<th class="ascending">' . _('料号') . '</th>
				<th class="ascending" width = 150>' . _('Description') . '</th>
				<th>' . _('Units') . '</th>
				<th class="ascending">' . _('Quantity') . '</th>
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
        echo '<td>' . $myrow['stockid'] . '</td>
				<td class="number">' . $myrow['description'] . '</td>
				<td class="number">' . $myrow['stockunits'] . '</td>
				<td><input class="number" ' . ($i == 0 ? 'autofocus="autofocus"' : '') . ' tabindex="' . ($j + 7) . '" type="text" size="6" name="Quantity' . $i . '" value="0" />
				<input type="hidden" name="StockID' . $i . '" value="' . $myrow['stockid'] . '" />
				</td>
			</tr>';

        echo '<input type="hidden" name="ItemDescription' . $i . '" value="' . $myrow['description'] . '" />';
        echo '<input type="hidden" name="Units' . $i . '" value="' . $myrow['stockunits'] . '" />';
        $i++;
    }
#end of while loop
    echo '<tr>
			<td><input type="hidden" name="Previous" value="' . ($Offset - 1) . '" />
				<input tabindex="' . ($j + 7) . '" type="submit" name="Prev" value="' . _('Prev') . '" /></td>
			<td style="text-align:center" colspan="6"><input type="hidden" name="order_items" value="1" />
				<input tabindex="' . ($j + 8) . '" type="submit" value="' . _('Add to Requisition') . '" /></td>';
    if ($Offset >= 0) {
        echo '
                            <td>
                                    <input type="hidden" name="NextList" value="' . ($Offset + 1) . '" />
                                    <input tabindex="' . ($j + 10) . '" type="submit" name="Next" value="' . _('Next') . '" /></td>';
    }
    echo '	
		<tr/>
		</table>
       </div>
       </form>';
}#end if SearchResults to show
//*********************************************************************************************************
include('includes/footer.inc');
?>
