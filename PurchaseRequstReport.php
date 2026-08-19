<?php

ob_start();
/*
* To change this license header, choose License Headers in Project Properties.
* To change this template file, choose Tools | Templates
* and open the template in the editor.
*/

include ('includes/session.inc');
$Title = _('询价单报表');
$ViewTopic = '询价单报表';
$BookMark = '询价单报表';

include ('includes/header.inc');
include ('includes/SQL_CommonFunctions.inc');

unset($result);

if (isset($_POST['Go1']) or isset($_POST['Go2'])) {
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

if (isset($_POST['Search']) or isset($_POST['Go']) or isset($_POST['Next']) or
    isset($_POST['Previous'])) {
    $sql = 'SELECT  pha.po_num, pha.status, pha.note, pha.creation_date,   pla.line_amount,pla.quantity,pla.line,pla.price,v.vendor_code, v.vendor_name, pha.need_date, pha.created_by,b.item_no,b.item_desc,b.item_name,b.units,
ifnull(pla.quantity_received,0) this_received
FROM po_request_headers_all pha, po_request_lines_all pla, vendors v,sf_item_no b
WHERE pla.po_num = pha.po_num
AND  b.item_no=pla.stockid
AND v.vendor_code = pha.vendor_code ';

    if (isset($_POST['po_num']) and $_POST['po_num'] != '') { 
		$sql = $sql . " and pha.po_num " . LIKE . " '%" . $_POST['po_num'] . "%' ";
    }
    
    if (isset($_POST['vendor_name']) and $_POST['vendor_name'] != '') {
        $sql = $sql . " and v.vendor_name " . LIKE . " '%" . $_POST['vendor_name'] .
            "%' ";
    }
    if (isset($_POST['vendor_code']) and $_POST['vendor_code'] != '') {
        $sql = $sql . " and pha.vendor_code =  '" . $_POST['vendor_code'] . "'";
    }
    if (isset($_POST['stockid']) and $_POST['stockid'] != '') { 
		$sql = $sql . " and pla.stockid  " . LIKE . " '%" . $_POST['stockid'] . "%' ";
    }
	if (isset($_POST['item_name']) and $_POST['item_name'] != '') { 
		$sql = $sql . " and b.item_name " . LIKE . " '%" . $_POST['item_name'] . "%' ";
    }
    if (empty($_POST['FromDate']) == 0) {
        $SQL_FromDate = strtotime($_POST['FromDate']);
        $sql .= " and pha.creation_date >= '" . $SQL_FromDate . "' ";
    }
    if (empty($_POST['ToDate']) == 0) {
        $SQL_ToDate = strtotime($_POST['ToDate']) + 86400;
        //echo $SQL_ToDate;
        $sql .= " and pha.creation_date <='" . $SQL_ToDate . "' ";
    }
   
    $result = DB_query($sql, $db);
    if (DB_num_rows($result) == 0) {
        unset($result);
        prnMsg(_('找不到该询价单，请重新输入条件查询！'), 'error');
    }
}


echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES,
    'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme .
    '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('查找询价单明细') .
    '</p>';
echo '<table cellpadding="3" class="selection">';

echo '<tr><td >' . _('询价单') . ':</td><td>';
echo '<input type="text" name="po_num" value="' . $_POST['po_num'] .
    '" size="20" maxlength="25" /></td>';

 
echo ' <td >' . _('供应商名称') . ':</td><td>';
echo '<input type="text" name="vendor_name" value="' . $_POST['vendor_name'] .
    '" size="20" maxlength="25" /></td>';
echo '<td>' . _('供应商代号') . ':</td>
	<td>';
echo '<input type="text" name="vendor_code" value="' . $_POST['vendor_code'] .
    '" size="20" maxlength="25" /></td>';
echo '</tr>';
echo '<tr><td >' . _('料号') . ':</td><td>';
echo '<input type="text" name="stockid" value="' . $_POST['stockid'] .
    '" size="20" maxlength="25" /></td>';
 
 echo '<td >' . _('料号名称') . ':</td><td>';
echo '<input type="text" name="item_name" value="' . $_POST['item_name'] .
    '" size="20" maxlength="25" /></td>';
 
if (!isset($_POST['FromDate'])) {
    $_POST['FromDate'] = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 30,
        date("Y")));
}
if (!isset($_POST['ToDate'])) {
    $_POST['ToDate'] = Date('Y-m-d');
}
echo '<td>' . '建立日' . _('From') . ':</td>
		<td><input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] .
    '" name="FromDate" maxlength="10" size="11" value="' . $_POST['FromDate'] . '" /></td>
		<td>' . _('To') . ':</td>
		<td><input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] .
    '" name="ToDate" maxlength="10" size="11" value="' . $_POST['ToDate'] . '" /></td>
	</tr>';

echo '</table><div class="centre"><input type="submit" name="Search" value="查找"> </div>' .
    '</br>';

if (isset($_POST['Search']) and isset($result) or isset($_POST['Go']) or isset($_POST['Next']) or
    isset($_POST['Previous'])) {
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
    echo '<input type="hidden" name="PageOffset" value="' . $_POST['PageOffset'] .
        '" />';
    if ($ListPageMax > 1) {
        echo '<br /><div class="centre">&nbsp;&nbsp;' . _('第') . '' . $_POST['PageOffset'] .
            ' ' . _('页，共') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') .
            ': ';
        echo '<select name="PageOffset1">';
        $ListPage = 1;
        while ($ListPage <= $ListPageMax) {
            if ($ListPage == $_POST['PageOffset']) {
                echo '<option value="' . $ListPage . '" selected="selected">' . $ListPage .
                    '</option>';
            } else {
                echo '<option value="' . $ListPage . '">' . $ListPage . '</option>';
            }
            $ListPage++;
        }
        echo '</select>
                    <input type="submit" name="Go1" value="' . _('Go') . '" />
                    <input type="submit" name="Previous" value="' . _('Previous') .
            '" />
                    <input type="submit" name="Next" value="' . _('Next') .
            '" />';
        echo '</div>';
    }
    echo '
                    <table cellpadding="2" class="selection" >';

    echo '<tr>      
	               <th class="ascending" width = 90>' . _('供应商代码') . '</th>
                   <th class="ascending" width = 90>' . _('询价单号') . '</th> 
                   <th class="ascending"width = 100>' . _('备注') . '</th>
                   <th class="ascending"width = 90>' . _('需求日期') . '</th>
				   <th class="ascending"width = 160>' . _('建立日期') . '</th>
				   <th width = 30>' . _('建立人员') . '</th>
                   <th width = 20>' . _('行') . '</th>
                   <th width = 100>' . _('料号') . '</th>
                   <th width = 150>' . _('料号名称') . '</th>
				   <th width = 190>' . _('规格型号') . '</th>
                   <th width = 30>' . _('单位') . '</th>
                   <th width = 30>' . _('单价') . '</th>
                   <th width = 90>' . _('数量') .'</th>
				   <th width = 30>' . _('金额') . '</th> 
                   
				  

                   
                   
                   
            </tr>';
    $k = 0; //row counter to determine background colour
    $RowIndex = 0;


    if (DB_num_rows($result) <> 0) {
        DB_data_seek($result, ($_POST['PageOffset'] - 1) * 10);
        $i = 0; //counter for input controls
        while (($myrow = DB_fetch_array($result)) and ($RowIndex <> 10)) {
            if ($k == 1) {
                echo '<tr class="EvenTableRows">';
                $k = 0;
            } else {
                echo '<tr class="OddTableRows">';
                $k = 1;
            }
          

           echo '  
			    <td>' . $myrow['vendor_code'] . '</td>
				<td> ' . $myrow['po_num'] . '</td>  
                <td>' . $myrow['note'] . '</td> 
				<td>' . date('Y-m-d', $myrow['need_date']) . '</td>
				<td>' . date('Y-m-d H:i:s', $myrow['creation_date']) . '</td>
				<td>' . $myrow['created_by'] . '</td>
				<td>' . $myrow['line'] . '</td>
                <td>' . $myrow['item_no'] . '</td>
					 <td>' . $myrow['item_name'] . '</td>
					 <td>' . $myrow['item_desc'] . '</td>
					 <td>' . $myrow['units'] . '</td>
					 <td>' . $myrow['price'] . '</td>
					 <td>' . $myrow['quantity'] . '</td>
					<td>' . $myrow['line_amount'] . '</td> ';


            echo '
			</tr>';
            $i++;
            $RowIndex++;
            //end of page full new headings if
        } //end loop through vendors
        echo '</table>';
        echo '<input type="hidden" name="JustSelectedAvendor" value="Yes" />';
		 
        
    }

    if (isset($ListPageMax) and $ListPageMax > 1) {
        echo '<br /><div class="centre">&nbsp;&nbsp;' . _('第') . '' . $_POST['PageOffset'] .
            ' ' . _('页，共') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') .
            ': ';
        echo '<select name="PageOffset2">';
        $ListPage = 1;
        while ($ListPage <= $ListPageMax) {
            if ($ListPage == $_POST['PageOffset']) {
                echo '<option value="' . $ListPage . '" selected="selected">' . $ListPage .
                    '</option>';
            } else {
                echo '<option value="' . $ListPage . '">' . $ListPage . '</option>';
            }
            $ListPage++;
        }
        echo '</select>
                        <input type="submit" name="Go2" value="' . _('Go') .
            '" />
                        <input type="submit" name="Previous" value="' . _('Previous') .
            '" />
                        <input type="submit" name="Next" value="' . _('Next') .
            '" />';
        echo '</div>';
    }
}
echo '</div></form>';

include ('includes/footer.inc');
