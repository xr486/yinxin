<?php

ob_start();
/*
* To change this license header, choose License Headers in Project Properties.
* To change this template file, choose Tools | Templates
* and open the template in the editor.
*/

include('includes/session.inc');
$Title = _('外协采购单明细查询');
$ViewTopic = '外协采购单明细查询';
$BookMark = '外协采购单明细查询';

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

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
$sql = 'SELECT  pha.po_num, pha.status, pha.note, pha.creation_date,   pla.line_amount,pla.quantity,pla.line,pla.price,v.vendor_code, v.vendor_name, pha.need_date, pha.created_by,pla.stockid, g.item_name,g.units,e.realname,
ifnull(pla.quantity_received,0) this_received,pla.operation_code,pla.operation_seq_num,pla.wip_entity_name
FROM po_headers_all pha, po_lines_all pla, vendors v,www_users e,sf_item_no g
WHERE  pla.stockid=g.item_no  and pla.po_num = pha.po_num
 and pha.created_by=e.userid
AND v.vendor_code = pha.vendor_code and pha.order_type = "外协采购"';
    $sql .= " order by pha.creation_date desc";
    $result = DB_query($sql, $db);
if (
    isset($_POST['Search']) or isset($_POST['Go']) or isset($_POST['Next']) or
    isset($_POST['Previous'])
) {
    $sql = 'SELECT  pha.po_num, pha.status, pha.note, pha.creation_date,   pla.line_amount,pla.quantity,pla.line,pla.price,v.vendor_code, v.vendor_name, pha.need_date, pha.created_by,pla.stockid, g.item_name,g.units,e.realname,
ifnull(pla.quantity_received,0) this_received,pla.operation_code,pla.operation_seq_num,pla.wip_entity_name
FROM po_headers_all pha, po_lines_all pla, vendors v,www_users e,sf_item_no g
WHERE  pla.stockid=g.item_no  and pla.po_num = pha.po_num
 and pha.created_by=e.userid
AND v.vendor_code = pha.vendor_code and pha.order_type = "外协采购"';






  if (isset($_POST['wip_entity_name']) and $_POST['wip_entity_name'] != '') {
        $sql = $sql . " and pla.wip_entity_name " . LIKE . " '%" . $_POST['wip_entity_name'] . "%' ";
    }
 
	if (isset($_POST['operation_code']) and $_POST['operation_code'] != '') {
        $sql = $sql . " and pla.operation_code " . LIKE . " '%" . $_POST['operation_code'] . "%' ";
    }
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
        $sql = $sql . " and g.item_name " . LIKE . " '%" . $_POST['item_name'] . "%' ";
    }
    if (empty($_POST['FromDate']) == 0) {
        $SQL_FromDate = strtotime($_POST['FromDate']);
        $sql .= " and pha.need_date >= '" . $SQL_FromDate . "' ";
    }
    if (empty($_POST['ToDate']) == 0) {
        $SQL_ToDate = strtotime($_POST['ToDate']) + 86400;
        //echo $SQL_ToDate;
        $sql .= " and pha.need_date <='" . $SQL_ToDate . "' ";
    }
    if ($_POST['checkresult'] != "") {
        if ($_POST['checkresult'] == "APPROVED") {
            $sql .= " and pha.status = 'APPROVED'";
        }
        if ($_POST['checkresult'] == "INPROCESS") {
            $sql .= " and pha.status = 'INPROCESS'";
        }
        if ($_POST['checkresult'] == "Cancel") {
            $sql .= " and pha.status = 'Cancel'";
        }
        if ($_POST['checkresult'] == "REJECTED") {
            $sql .= " and pha.status = 'REJECTED'";
        }
    }
   $sql .= " order by pha.creation_date desc";
//    echo $sql;
    $result = DB_query($sql, $db);
    if (DB_num_rows($result) == 0) {
        //unset($result);
        prnMsg(_('找不到该外协采购单，请重新输入条件查询！'), 'error');
    }
}


echo '<form action="' . htmlspecialchars(
    $_SERVER['PHP_SELF'],
    ENT_QUOTES,
    'UTF-8'
) . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '
        <div class="centre" style="margin-bottom: 5px; display: flex;justify-content: flex-start;align-items: center;">
        <input type="submit" name="Search" value="查找"> &nbsp;&nbsp;';
        // echo ' <div class="export" >
        //   <a href="' . $RootPath . '/OspPODetailReportExcel.php?ToDate=' . $_POST['ToDate'] .'&FromDate=' . $_POST['FromDate'] .'&checkresult=' . $_POST['checkresult'] .'&po_num=' . $_POST['po_num'] . '&wip_entity_name=' . $_POST['wip_entity_name'] . '&operation_seq_num=' . $_POST['operation_seq_num'] . '&operation_code=' . $_POST['operation_code'] .'&wip_entity_name=' . $_POST['wip_entity_name'] .'&operation_seq_num=' . $_POST['operation_seq_num'] . '&operation_code=' . $_POST['operation_code'] . '&vendor_name=' . $_POST['vendor_name'] . '&vendor_code=' . $_POST['vendor_code'] . '&stockid=' . $_POST['stockid'] . '&item_name=' . $_POST['item_name'] . ' ">' . '导出' . '</a>
        //   </div>';
          echo ' </div>
';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme .
    '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('外协采购单明细查询') .
    '</p>';
echo '<table cellpadding="3" class="selection">';
echo '<div class="text-nav">';
echo '<div class="text-nav-1"><div>' . _('外协采购单') . ':</div>';
echo '<input type="text"   autocomplete="off"   name="po_num" value="' . $_POST['po_num'] .'"  /></div>';

echo '<div class="text-nav-1"><div>' . _('工单号') . ':</div>';
echo '<input type="text"   autocomplete="off"   name="wip_entity_name" value="' . $_POST['wip_entity_name'] .'"  /></div>';
 
echo '<div class="text-nav-1"><div>' . _('工序名称') . ':</div>';
echo '<input type="text"   autocomplete="off"   name="operation_code" value="' . $_POST['operation_code'] .'"  /></div>';

echo '<div class="text-nav-1"><div>' . _('供应商名称') . ':</div>';
echo '<input type="text"   autocomplete="off"   id="text_slect_name" name="vendor_name" value="' . $_POST['vendor_name'] .
    '" size="20" maxlength="25" />
    <a class="btn btn-info btn-xs" id="btn_slect_vendor" hfre="###" title="选择供应商">选</a>
    </div>';
echo '<div class="text-nav-1"><div>' . _('供应商代号') . ':</div>
';
echo '<input type="text"   autocomplete="off"   id="text_slect_vendor" name="vendor_code" value="' . $_POST['vendor_code'] .
    '" size="20" maxlength="25" />
    <a class="btn btn-info btn-xs" id="btn_slect_vendor_a" hfre="###" title="选择供应商">选</a>
    </div>';

echo '<div class="text-nav-1"><div>' . _('料号') . ':</div>';
echo '<input type="text"   autocomplete="off"   id="text_slect_buliao" name="stockid" value="' . $_POST['stockid'] .
    '" size="20" maxlength="25" />
    <a class="btn btn-info btn-xs" id="btn_slect_buliao" hfre="###" title="选择产品">选</a>
    </div>';

echo '<div class="text-nav-1"><div>' . _('料号名称') . ':</div>';
echo '<input type="text"   autocomplete="off"   id="text_slect_item_name" name="item_name" value="' . $_POST['item_name'] .
    '" size="20" maxlength="25" />
    <a class="btn btn-info btn-xs" id="btn_slect_buliao2" hfre="###" title="选择产品">选</a>
    </div>';
echo '<div class="text-nav-1"><div>' . _('签核状态') . ':</div><select name="checkresult">';

echo '<option  selected="selected" value=""></option>';

echo '<option   value="INPROCESS">待签核</option>';

echo '<option   value="APPROVED">已签核</option>';
echo '<option   value="Cancel">已取消</option>';
echo '<option   value="REJECTED">已拒签</option>';

echo '</select></div> ';
if (!isset($_POST['FromDate'])) {
    $_POST['FromDate'] = date("Y-m-d", mktime(
        0,
        0,
        0,
        date("m"),
        date("d") - 30,
        date("Y")
    ));
}
if (!isset($_POST['ToDate'])) {
    $_POST['ToDate'] = Date('Y-m-d');
}
echo '<div class="text-nav-1"><div>' . '需求日' . _('起') . ':</div>
		<input type="text"   autocomplete="off"   onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] .
    '" name="FromDate" maxlength="10" size="11" value="' . $_POST['FromDate'] . '" /></div>
    <div class="text-nav-1"><div>' . _('需求日止') . ':</div>
		<input type="text"   autocomplete="off"   onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] .
    '" name="ToDate" maxlength="10" size="11" value="' . $_POST['ToDate'] . '" /></div>
	</div>';

echo '</table><div class="centre"></div>' . '';
//总计
if ( isset($result)) {
	$total_line=0;
	 while (($myrow2 = DB_fetch_array($result))  ) {
		        
						$total_line=$total_line+1;
                        $total_line_amount = $total_line_amount + $myrow2['line_amount'];
                        $total_quantity = $total_quantity + $myrow2['quantity'];
                        $total_this_received = $total_this_received + $myrow2['this_received'];
            
                        
      }
  }

if (
    isset($_POST['Search']) or isset($result) or isset($_POST['Go']) or isset($_POST['Next']) or
    isset($_POST['Previous'])
) {
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
                   <th class="ascending" width = 90>' . _('外协采购单号') . '</th>
                   <th class="ascending"width = 90>' . _('签核状态') . '</th>
                   <th class="ascending"width = 100>' . _('备注') . '</th>
                   <th class="ascending"width = 90>' . _('需求日期') . '</th>
                   <th class="ascending"width = 20>' . _('行') . '</th>
                    <th   >' . _('工单号') . '</th>
		<th   >' . _('工序名称') . '</th>
		<th   >' . _('料号') . '</th>
		<th  >' . _('料号名称') . '</th>
                   <th class="ascending"width = 30>' . _('单位') . '</th>';
    if ($_SESSION['price_flag'] == 'N') {
        echo '<th class="ascending"width = 30>' . _('单价') . '</th>
				   <th class="ascending"width = 30>' . _('采购金额') . '</th>';
    }
    echo '<th class="ascending"width = 90>' . _('采购数量') . '</th>
				   <th class="ascending"width = 90>' . _('收货数量') . '</th>
                   
		
				   <th class="ascending"width = 90>' . _('建立日期') . '</th>
				   <th class="ascending"width = 30>' . _('建立人员') . '</th>		  

                   
                   
                   
            </tr>';
    $k = 0; //row counter to determine background colour
    $RowIndex = 0;
    $all_line=0;

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
            $all_line=$all_line+1;
            $all_line_amount = $all_line_amount + $myrow['line_amount'];
            $all_quantity = $all_quantity + $myrow['quantity'];
            $all_this_received = $all_this_received + $myrow['this_received'];

            unset($v_status);
            if ($myrow['status'] == 'INPROCESS') {
                $v_status = '待签核';
            } elseif ($myrow['status'] == 'APPROVED') {
                $v_status = '已签核';
            } elseif ($myrow['status'] == 'REJECTED') {
                $v_status = '已拒签';
            } else {
                $v_status = $myrow['status'];
            }

            echo '  
			    <td>' . $myrow['vendor_code'] . '</td>
				<td><a href="' . $RootPath . '/OspSearchPO2.php?Updatepo_num=' . $myrow['po_num'] . '" target="_blank" >' . $myrow['po_num'] . '</td> 
				<td>' . $v_status . '</td>
                <td>' . $myrow['note'] . '</td> 
				<td>' . date('Y-m-d', $myrow['need_date']) . '</td>
				<td>' . $myrow['line'] . '</td>
                <td>' . $myrow['wip_entity_name'] . '</td>
	            <td>' . $myrow['operation_code'] . '</td>
                <td>' . $myrow['stockid'] . '</td>
				<td>' . $myrow['item_name'] . '</td>
				<td>' . $myrow['units'] . '</td>';
            if ($_SESSION['price_flag'] == 'N') {
                echo '  <td>' . $myrow['price'] . '</td>
                       <td>' . $myrow['line_amount'] . '</td>';
            }
            echo '					 
					<td>' . $myrow['quantity'] . '</td>
					 <td>' . $myrow['this_received'] . '</td>					 
				<td>' . date('Y-m-d H:i:s', $myrow['creation_date']) . '</td>
				<td>' . $myrow['realname'] . '</td>';
            echo '
			</tr>';
            $i++;
            $RowIndex++;
            //end of page full new headings if
        } //end loop through vendors
		 if ($_SESSION['price_flag'] == 'N') {
        echo '<tr><td>小计</td><td>笔数</td><td>'. $all_line . '</td>  <td></td> <td></td> <td></td> <td></td> <td></td> <td></td> <td></td> <td></td> <td></td> <td></td> <td> '.$all_line_amount.' </td> <td>'.$all_quantity.'</td> <td>'.$all_this_received.'</td> <td></td> <td></td> </tr>';
        echo '<tr><td>总计</td><td>笔数</td><td>' . $total_line . '</td>  <td></td> <td></td> <td></td> <td></td> <td></td> <td></td> <td></td> <td></td> <td></td> <td></td> <td> '.$total_line_amount.' </td> <td>'.$total_quantity.'</td> <td>'.$total_this_received.'</td> <td></td> <td></td> </tr>';
		 } else {
		 echo '<tr><td>小计</td><td>笔数</td><td>'. $all_line . '</td>  <td></td> <td></td> <td></td> <td></td> <td></td> <td></td> <td></td> <td></td> <td></td>    <td>'.$all_quantity.'</td> <td>'.$all_this_received.'</td> <td></td> <td></td> </tr>';
        echo '<tr><td>总计</td><td>笔数</td><td>' . $total_line . '</td>  <td></td> <td></td> <td></td> <td></td> <td></td> <td></td> <td></td> <td></td> <td></td>     <td>'.$total_quantity.'</td> <td>'.$total_this_received.'</td> <td></td> <td></td> </tr>';
		 }

        echo '</table></div>';
        echo '<input type="hidden" name="JustSelectedAvendor" value="Yes" />';
        echo '<div>
                
                </div>';          
    }

    if (isset($ListPageMax) and $ListPageMax > 1) {
        echo '<br /><div class="centre">&nbsp;&nbsp;' . _('第') . '' . $_POST['PageOffset'] . ' ' . _('页，共') . ' ' . $ListPageMax . ' ' . _('页') . '. ' . _('转到页') . ': ';
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

include('includes/footer.inc');
?>
<script type="text/javascript">
    $('#btn_slect_vendor').dialog({
        title: '选择供应商',
        width: '950px',
        height: 470,
        content: 'url:BtnSearchVendor517.php?fwValue=&cat=buliao',
        init: function() {
            this.content.document.getElementById('cat').value = 'buliao';
            this.content.document.getElementById('fwValue').value = '';
        }
    });
    $('#btn_slect_vendor_a').dialog({
        title: '选择供应商',
        width: '950px',
        height: 470,
        content: 'url:BtnSearchVendor517.php?fwValue=&cat=buliao',
        init: function() {
            this.content.document.getElementById('cat').value = 'buliao';
            this.content.document.getElementById('fwValue').value = '';
        }
    });
    $('#btn_slect_buliao').dialog({
        title: '选择产品',
        width: '1200px',
        height: 470,
        content: 'url:Searchbuliao517.php?fwValue=&cat=buliao',
        init: function() {
            this.content.document.getElementById('cat').value = 'buliao';
            this.content.document.getElementById('fwValue').value = '';
        }
    });
    $('#btn_slect_buliao2').dialog({
        title: '选择产品',
        width: '1200px',
        height: 470,
        content: 'url:Searchbuliao517.php?fwValue=&cat=buliao',
        init: function() {
            this.content.document.getElementById('cat').value = 'buliao';
            this.content.document.getElementById('fwValue').value = '';
        }
    });
</script>