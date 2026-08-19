<?php

ob_start();
/*
* To change this license header, choose License Headers in Project Properties.
* To change this template file, choose Tools | Templates
* and open the template in the editor.
*/

include ('includes/session.inc');
$Title = _('查找采购单');
$ViewTopic = '查找采购单';
$BookMark = '查找采购单';

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
    $sql = 'SELECT DISTINCT pha.po_num, 
                            pha.status, 
                            pha.note, 
                            pha.creation_date,
                            pha.order_date, 
                            pha.need_date,
                            pha.po_all_amount,pha.youhui_amount,
                            pha.tax_amount,pha.all_line_amount,
                            v.vendor_name, pha.tax_name,pha.tax_flag,
                            pha.need_date, 
                            pha.created_by,(select realname from www_users where userid=pha.created_by) realname
            FROM po_headers_all pha, 
                            po_lines_all pla, 
                            vendors v
            WHERE pla.po_num = pha.po_num
            AND v.vendor_code = pha.vendor_code';

    if (isset($_POST['po_num_from']) and $_POST['po_num_from'] != '') { 
        $sql = $sql . " and pha.po_num ".LIKE." '%".$_POST['po_num_from']."%' ";
    }
     
    if (isset($_POST['vendor_name']) and $_POST['vendor_name'] != '') {
        $sql = $sql . " and v.vendor_name ".LIKE." '%".$_POST['vendor_name']."%' ";
            
    }
    if (isset($_POST['vendor']) and $_POST['vendor'] != '') { 
		$sql = $sql . " and v.vendor_code ".LIKE." '%".$_POST['vendor']."%' ";
           
    }
    if (isset($_POST['stockid']) and $_POST['stockid'] != '') {
        $sql = $sql . " and pla.stockid ".LIKE." '%".$_POST['stockid']."%' ";
    }
    if (empty($_POST['FromDate']) == 0) {
        $SQL_FromDate = strtotime($_POST['FromDate']);
        $sql .= " and pha.order_date >= '" . $SQL_FromDate . "' ";
    }
    if (empty($_POST['ToDate']) == 0) {
        $SQL_ToDate = strtotime($_POST['ToDate']) + 86400;
        //echo $SQL_ToDate;
        $sql .= " and pha.order_date <='" . $SQL_ToDate . "' ";
    }
    if ($_POST['checkresult'] != "") {
		$sql .= " and pha.status  ='" . $_POST['checkresult'] . "' ";
        
    }
    $sql .= " order by pha.creation_date desc";
	//echo $sql;
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
    '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('查找采购单') .
    '</p>';
echo '<table cellpadding="3" class="selection">';
echo '<div class="text-nav">';
echo '<div class="text-nav-1"><div>' . _('采购单') . ':</div>';
echo '<input type="text" name="po_num_from" value="' . $_POST['po_num_from'] .
    '" size="20" maxlength="25" /></div>';
 
 
echo '<div class="text-nav-1"><div>' . _('供应商名称') . ':</div>';
echo '<input type="text" name="vendor_name" value="' . $_POST['vendor_name'] .
    '" size="20" maxlength="25" /></div>';
echo '<div class="text-nav-1"><div>' . _(' 供应商编号') . ':</div>';
echo '<input type="text" name="vendor" value="' . $_POST['vendor'] .
    '" size="20" maxlength="25" /></div>';

echo '<div class="text-nav-1"><div>' . _('料号') . ':</div>';
echo '<input type="text" name="stockid" value="' . $_POST['stockid'] .
    '" size="20" maxlength="25" /></div>';
 
echo '<div class="text-nav-1"><div>' . _('采购单状态') . ':</div><select name="checkresult">';
        echo '<option  selected="selected" value="' . $_POST['checkresult'] .'">' . $_POST['checkresult'] .'</option>';
        echo '<option   value="待签核">待签核</option>';
        echo '<option   value="已签核">已签核</option>';
        echo '<option   value="已取消">已取消</option>';
        echo '<option   value="已拒签">已拒签</option>';
echo '</select></div> ';

if (!isset($_POST['FromDate'])) {
    $_POST['FromDate'] = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 30, date("Y")));
}
if (!isset($_POST['ToDate'])) {
    $_POST['ToDate'] = Date('Y-m-d');
}
echo '<div class="text-nav-1"><div>' . '采购日期' . _('起') . ':</div>
		<input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] .
    '" name="FromDate" maxlength="10" size="11" value="' . $_POST['FromDate'] . '" /></div>
    <div class="text-nav-1"><div>' . _('采购日期止') . ':</div>
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
                    <th class="ascending" width = 100>' . _('采购单号') . '</th>
                    <th class="ascending"width = 60>' . _('状态') . '</th>
                    <th class="ascending"width = 240>' . _('供应商名称') . '</th>
                    <th  width = 100>' . _('税别') . '</th>   
                    <th   >' . _('是否含税') . '</th>   
                   
					';
	  if ($_SESSION['price_flag']=='N') {				 
             echo '  <th  width = 100>' . _('含税金额') . '</th>  
                    <th width = 100>' . _('未税金额') . '</th>   
                    <th  width = 100>' . _('税金') . '</th>  ';
					}
              echo ' 
                    <th class="ascending"width = 150>' . _('备注') . '</th>
                    <th class="ascending"width = 90>' . _('采购日期') . '</th>
                    <th class="ascending"width = 90>' . _('需求日') . '</th>
                    <th class="ascending"width = 90>' . _('下单日期') . '</th>
                     <th  width = 70>' . _('下单人员') . '</th>
                     <th  >' . _('打印') . '</th>
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
         

	
            echo '  <td><a href="' . $RootPath . '/SearchPO2.php?Updatepo_num=' . $myrow['po_num'] .'" target="_blank" >' . $myrow['po_num'] . '</td>
				<td>' . $myrow['status']. '</td>
                <td>' . $myrow['vendor_name'] . '</td>
                <td>' . $myrow['tax_name'] . '</td> 
                <td>' . $myrow['tax_flag'] . '</td> ';
	           if ($_SESSION['price_flag']=='N') {				 
               echo '    <td>' . $myrow['po_all_amount'] . '</td>
                <td>' . $myrow['all_line_amount'] . '</td>
                <td>' . $myrow['tax_amount'] . '</td>';
			 }
           echo '
                
				<td>' . $myrow['note'] . '</td>
                <td>' . date('Y-m-d', $myrow['order_date']) .'</td>
                <td>' . date('Y-m-d', $myrow['need_date']) .'</td>                               
				<td>' . date('Y-m-d', $myrow['creation_date']) . '</td>
				<td>' . $myrow['realname'] . '</td>
				 <td><a href="' . $RootPath . '/PrintPOQuote.php?Updatedelivery_num=' . $myrow['po_num'] . '"target="_blank">打印 </td>
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
                <a href="' . $RootPath . '/SearchPOExcel.php?po_num_from=' .$_POST['po_num_from'] .'&vendor_name=' .$_POST['vendor_name'] .'&vendor=' .$_POST['vendor'] .'&stockid=' .$_POST['stockid'] .'&FromDate=' .$_POST['FromDate'] . '&ToDate=' .$_POST['ToDate']  .' ">' .'资料导出Excel表' . '</a>
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
        echo '</div>';
    }
}
echo '</div></form>';

include ('includes/footer.inc');
