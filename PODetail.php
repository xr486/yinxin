<?php

ob_start();
/*
* To change this license header, choose License Headers in Project Properties.
* To change this template file, choose Tools | Templates
* and open the template in the editor.
*/

include ('includes/session.inc');
$Title = _('采购单报表');
$ViewTopic = '采购单报表';
$BookMark = '采购单报表';

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
ifnull(pla.quantity_received,0) this_received,(select sum(amount)  from ap_invoice_lines_all ala where  pla.line=ala.po_line and pla.po_num = ala.po_num) amount,(select realname from www_users where userid=pha.created_by) realname
FROM po_headers_all pha, po_lines_all pla, vendors v,sf_item_no b
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
     if ($_POST['checkresult'] != "") {        
	 $sql .= " and pha.status = '" . $_POST['checkresult'] . "' ";
     }
	$sql .= " order by pha.creation_date desc,pla.line";
    $result = DB_query($sql, $db);
    if (DB_num_rows($result) == 0) {
        unset($result);
        prnMsg(_('找不到该采购单，请重新输入条件查询！'), 'error');
    }
}


echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES,
    'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme .
    '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('查找采购单明细') .
    '</p>';
echo '<table cellpadding="3" class="selection">';
echo '<div class="text-nav">';
echo '<div class="text-nav-1"><div>' . _('采购单') . ':</div>';
echo '<input type="text" name="po_num" value="' . $_POST['po_num'] .
    '" size="20" maxlength="25" /></div>';

 
echo '<div class="text-nav-1"><div>' . _('供应商名称') . ':</div>';
echo '<input type="text" name="vendor_name" value="' . $_POST['vendor_name'] .
    '" size="20" maxlength="25" /></div>';
echo '<div class="text-nav-1"><div>' . _('供应商代号') . ':</div>
';
echo '<input type="text" name="vendor_code" value="' . $_POST['vendor_code'] .
    '" size="20" maxlength="25" /></div>';

echo '<div class="text-nav-1"><div>' . _('料号') . ':</div>';
echo '<input type="text" name="stockid" value="' . $_POST['stockid'] .
    '" size="20" maxlength="25" /></div>';
 
 echo '<div class="text-nav-1"><div>' . _('料号名称') . ':</div>';
echo '<input type="text" name="item_name" value="' . $_POST['item_name'] .
    '" size="20" maxlength="25" /></div>';
echo '<div class="text-nav-1"><div>' . _('采购单状态') . ':</div><select name="checkresult">';
        echo '<option  selected="selected" value="' . $_POST['checkresult'] .'">' . $_POST['checkresult'] .'</option>';
        echo '<option   value="待签核">待签核</option>';
        echo '<option   value="已签核">已签核</option>';
        echo '<option   value="已取消">已取消</option>';
        echo '<option   value="已拒签">已拒签</option>';

echo '</select></div> ';
if (!isset($_POST['FromDate'])) {
    $_POST['FromDate'] = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 30,
        date("Y")));
}
if (!isset($_POST['ToDate'])) {
    $_POST['ToDate'] = Date('Y-m-d');
}
echo '<div class="text-nav-1"><div>' . '建单日' . _('起') . ':</div>
		<input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] .
    '" name="FromDate" maxlength="10" size="11" value="' . $_POST['FromDate'] . '" /></div>
    <div class="text-nav-1"><div>' . _('建单日止') . ':</div>
		<input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] .
    '" name="ToDate" maxlength="10" size="11" value="' . $_POST['ToDate'] . '" /></div>
	</div>';

echo '</table><div class="centre"><input type="submit" name="Search" value="查找"> </div>' .
    '</br>';

if (isset($_POST['Search']) and isset($result) or isset($_POST['Go']) or isset($_POST['Next']) or
    isset($_POST['Previous'])) {
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
    echo '<input type="hidden" name="PageOffset" value="' . $_POST['PageOffset'] .
        '" />';
    if ($ListPageMax > 1) {
       echo '<br /><div class="centre">&nbsp;&nbsp;' . _('第') . '' . $_POST['PageOffset'] . ' ' . _('页，共') . ' ' . $ListPageMax . ' ' . _('页') . '. ' . _('转到页') . ': ';
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
                     <input type="submit" name="Go1" value="' . _('转到') . '" />
                <input type="submit" name="Previous" value="' . _('上一页') . '" />
                <input type="submit" name="Next" value="' . _('下一页') . '" />';
         
        echo '</div>';
    }
    echo '			  <div class="text-nav-table">
                    <table cellpadding="2" class="selection" >';

    echo '<tr>      
	               <th class="ascending" width = 90>' . _('供应商代码') . '</th>
                   <th class="ascending" width = 90>' . _('采购单号') . '</th>
                   <th class="ascending"width = 90>' . _('签核状态') . '</th>
                   <th class="ascending"width = 100>' . _('备注') . '</th>
                   <th class="ascending"width = 90>' . _('需求日期') . '</th>
				   <th class="ascending"width = 90>' . _('建立日期') . '</th>
				   <th class="ascending"width = 30>' . _('建立人员') . '</th>
                   <th class="ascending"width = 20>' . _('行') . '</th>
                   <th class="ascending"width = 100>' . _('料号') . '</th>
                   <th class="ascending"width = 150>' . _('料号名称') . '</th>
				   <th class="ascending"width = 150>' . _('规格型号') . '</th>
                   <th class="ascending"width = 30>' . _('单位') . '</th>';
                      if ($_SESSION['price_flag']=='N') {
                   echo '<th class="ascending"width = 30>' . _('单价') . '</th>
				   <th class="ascending"width = 30>' . _('采购金额') . '</th>';
					  }
                   echo '
                   <th class="ascending"width = 90>' . _('已开票金额') .'</th>
                   <th class="ascending"width = 90>' . _('采购数量') .'</th>
				   <th class="ascending"width = 90>' . _('收货数量') . '</th>
                   
				  

                   
                   
                   
            </tr>';
    $k = 0; //row counter to determine background colour
    $RowIndex = 0;


    if (DB_num_rows($result) <> 0) {
        DB_data_seek($result, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);
        $i = 0; //counter for input controls
        while (($myrow = DB_fetch_array($result)) and ($RowIndex <> $_SESSION['DisplayRecordsMax'])) {
            if ($k == 1) {
                echo '<tr class="EvenTableRows">';
                $k = 0;
            } else {
                echo '<tr class="OddTableRows">';
                $k = 1;
            }
            

           echo '  
			    <td>' . $myrow['vendor_code'] . '</td>
				<td><a href="' . $RootPath . '/SearchPO2.php?Updatepo_num=' . $myrow['po_num'] .'" target="_blank" >' . $myrow['po_num'] . '</td> 
				<td>' . $myrow['status']. '</td>
                <td>' . $myrow['note'] . '</td> 
				<td>' . date('Y-m-d', $myrow['need_date']) . '</td>
				<td>' . date('Y-m-d H:i:s', $myrow['creation_date']) . '</td>
				<td>' . $myrow['realname'] . '</td>
				<td>' . $myrow['line'] . '</td>
                <td>' . $myrow['item_no'] . '</td>
					 <td>' . $myrow['item_name'] . '</td>
					 <td>' . $myrow['item_desc'] . '</td>
					 <td>' . $myrow['units'] . '</td>';
                      if ($_SESSION['price_flag']=='N') {
						 echo '  <td>' . sprintf("%.5f",$myrow['price']) . '</td>
                       <td>' . sprintf("%.5f",$myrow['line_amount']) . '</td>';
					  }  
                      echo '		
                      <td>' . $myrow['amount'] . '</td>			 
					<td>' . $myrow['quantity'] . '</td>
					 <td>' . $myrow['this_received'] . '</td> ';


            echo '
			</tr>';
            $i++;
            $RowIndex++;
            //end of page full new headings if
        } //end loop through vendors
        echo '</table></div>';
        echo '<input type="hidden" name="JustSelectedAvendor" value="Yes" />';
		 echo '<div>
                 <a href="' . $RootPath . '/PODetailReportExcel.php?vendor_name=' .$_POST['vendor_name'] .'&vendor_code=' .$_POST['vendor_code'] .'&FromDate=' .$_POST['FromDate'] . '&ToDate=' .$_POST['ToDate'] .'&stockid=' .$_POST['stockid'] . '&item_name=' .$_POST['item_name'] .'&po_num=' .$_POST['po_num'] . '&status=' .$_POST['checkresult'] .' ">' .'资料导出Excel表' . '</a>
                </div>';
    }

    if (isset($ListPageMax) and $ListPageMax > 1) {
        echo '<br /><div class="centre">&nbsp;&nbsp;' . _('第') . '' . $_POST['PageOffset'] . ' ' . _('页，共') . ' ' . $ListPageMax . ' ' . _('页数') . '. ' . _('转到页') . ': ';
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
                         <input type="submit" name="Go2" value="' . _('转到') . '" />
                <input type="submit" name="Previous" value="' . _('上一页') . '" />
                <input type="submit" name="Next" value="' . _('下一页') . '" />';
            '" />';
        echo '</div>';
    }
}
echo '</div></form>';

include ('includes/footer.inc');
