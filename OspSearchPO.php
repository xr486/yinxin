<?php

ob_start();
/*
* To change this license header, choose License Headers in Project Properties.
* To change this template file, choose Tools | Templates
* and open the template in the editor.
*/

include('includes/session.inc');
$Title = _('外协采购单查询');
$ViewTopic = '外协采购单查询';
$BookMark = '外协采购单查询';

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
$sql = 'SELECT DISTINCT pha.po_num, pha.status, pha.note, pha.creation_date,
pha.order_date, pha.need_date, pha.po_all_amount,pha.youhui_amount,
pha.tax_amount,pha.all_line_amount,pha.tax_name,v.vendor_name, pha.need_date, pha.created_by
FROM po_headers_all pha, po_lines_all pla, vendors v
WHERE pla.po_num = pha.po_num
AND v.vendor_code = pha.vendor_code and pha.order_type = "外协采购"';
    $sql .= " order by pha.creation_date desc";
    $result = DB_query($sql, $db);
if (
    isset($_POST['Search']) or isset($_POST['Go']) or isset($_POST['Next']) or
    isset($_POST['Previous'])
) {
    $sql = 'SELECT DISTINCT pha.po_num, 
                            pha.status, 
                            pha.note, 
                            pha.creation_date,
                            pha.order_date, 
                            pha.need_date,
                            pha.po_all_amount,pha.youhui_amount,
                            pha.tax_amount,pha.all_line_amount,pha.tax_name,
                            v.vendor_name, 
                            pha.need_date, 
                            pha.created_by
            FROM po_headers_all pha, 
                            po_lines_all pla, 
                            vendors v
            WHERE pla.po_num = pha.po_num
            AND v.vendor_code = pha.vendor_code and pha.order_type = "外协采购"';

    if (isset($_POST['po_num']) and $_POST['po_num'] != '') {
        $sql = $sql . " and pha.po_num " . LIKE . " '%" . $_POST['po_num'] . "%' ";
    }

    if (isset($_POST['vendor_name']) and $_POST['vendor_name'] != '') {
        $sql = $sql . " and v.vendor_name " . LIKE . " '%" . $_POST['vendor_name'] . "%' ";
    }
    if (isset($_POST['vendor']) and $_POST['vendor'] != '') {
        $sql = $sql . " and v.vendor_code " . LIKE . " '%" . $_POST['vendor'] . "%' ";
    }
    if (isset($_POST['stockid']) and $_POST['stockid'] != '') {
        $sql = $sql . " and pla.stockid " . LIKE . " '%" . $_POST['stockid'] . "%' ";
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
        // echo '  <div class="export" >
        //   <a href="' . $RootPath . '/OspSearchPOExcel.php?po_num=' . $_POST['po_num'] . '&vendor_name=' . $_POST['vendor_name'] . '&vendor=' . $_POST['vendor'] . '&stockid=' . $_POST['stockid'] . '&checkresult=' . $_POST['checkresult'] . '&FromDate=' . $_POST['FromDate'] . '&ToDate=' . $_POST['ToDate'] . '">' . '导出' . '</a>
        //   </div>';
          echo ' </div>
';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme .
    '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('外协采购单查询') .
    '</p>';
echo '<table cellpadding="3" class="selection">';
echo '<div class="text-nav">';
echo '<div class="text-nav-1"><div>' . _('外协采购单') . ':</div>';
echo '<input type="text"   autocomplete="off"   name="po_num" value="' . $_POST['po_num'] .
    '" size="20" maxlength="25" /></div>';


echo '<div class="text-nav-1"><div>' . _('供应商名称') . ':</div>';
echo '<input type="text"   autocomplete="off"   id="text_slect_name" name="vendor_name" value="' . $_POST['vendor_name'] .
    '" size="20" maxlength="25" />
    <a class="btn btn-info btn-xs" id="btn_slect_vendor" hfre="###" title="选择供应商">选</a>
    </div>';
echo '<div class="text-nav-1"><div>' . _(' 供应商代号') . ':</div>
';
echo '<input type="text"   autocomplete="off"   id="text_slect_vendor" name="vendor" value="' . $_POST['vendor'] .
    '" size="20" maxlength="25" />
    <a class="btn btn-info btn-xs" id="btn_slect_vendor_a" hfre="###" title="选择供应商">选</a>
    </div>';

echo '<div class="text-nav-1" style="display: none"><div>' . _('图号') . ':</div>';
echo '<input type="text"   autocomplete="off"   id="text_slect_buliao" name="stockid" value="' . $_POST['stockid'] .
    '" size="20" maxlength="25" />
    <a class="btn btn-info btn-xs" id="btn_slect_buliao" hfre="###" title="选择产品">选</a>
    </div>';

echo '<div class="text-nav-1"><div>' . _('状态') . ':</div><select name="checkresult">';
echo '<option  selected="selected" value=""></option>';
echo '<option   value="INPROCESS">待签核</option>';
echo '<option   value="APPROVED">已签核</option>';
echo '<option   value="Cancel">已取消</option>';
echo '<option   value="REJECTED">已拒签</option>';
echo '</select></div> ';


echo '<div class="text-nav-1"><div>' . '采购日期' . _('起') . ':</div>
		<input type="text"   autocomplete="off"   onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] .
    '" name="FromDate" maxlength="10" size="11" value="' . $_POST['FromDate'] . '" /></div>
    <div class="text-nav-1"><div>' . _('采购日期止') . ':</div>
		<input type="text"   autocomplete="off"   onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] .
    '" name="ToDate" maxlength="10" size="11" value="' . $_POST['ToDate'] . '" /></div>
	</div>';

echo '</table><div class="centre"></div>' .'';
//总计
if ( isset($result)) {
	$total_line=0;
	 while (($myrow2 = DB_fetch_array($result))  ) {
		        
						$total_line=$total_line+1;
                        $total_po_all_amount = $total_po_all_amount + $myrow2['po_all_amount'];
                        $total_all_line_amount = $total_all_line_amount + $myrow2 ['all_line_amount'];
                        $total_tax_amount = $total_tax_amount + $myrow2['tax_amount'];
                        
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
                    <th class="ascending" width = 100>' . _('外协采购单号') . '</th>
                    <th class="ascending"width = 60>' . _('状态') . '</th>
                    <th class="ascending"width = 240>' . _('供应商') . '</th>
                   
					';
    if ($_SESSION['price_flag'] == 'N') {
        echo '  <th  width = 100>' . _('含税金额') . '</th>  
                    <th width = 100>' . _('未税金额') . '</th>  
                    <th  width = 100>' . _('税别') . '</th>    
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
            $all_po_all_amount = $all_po_all_amount + $myrow['po_all_amount'];
            $all_all_line_amount = $all_all_line_amount + $myrow ['all_line_amount'];
            $all_tax_amount = $all_tax_amount + $myrow['tax_amount'];
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


            echo '  <td><a href="' . $RootPath . '/OspSearchPO2.php?Updatepo_num=' . $myrow['po_num'] . '" target="_blank" >' . $myrow['po_num'] . '</td>
				<td>' . $v_status . '</td>
                <td>' . $myrow['vendor_name'] . '</td> ';
            if ($_SESSION['price_flag'] == 'N') {
                echo '    <td>' . $myrow['po_all_amount'] . '</td>
                <td>' . $myrow['all_line_amount'] . '</td>
                <td>' . $myrow['tax_name'] . '</td>
                <td>' . $myrow['tax_amount'] . '</td>';
            }
                echo '
				<td>' . $myrow['note'] . '</td>
                <td>' . date('Y-m-d', $myrow['order_date']) . '</td>
                <td>' . date('Y-m-d', $myrow['need_date']) . '</td>                               
				<td>' . date('Y-m-d', $myrow['creation_date']) . '</td>
				<td>' . $myrow['created_by'] . '</td>
				 <td><a href="' . $RootPath . '/PrintOSPPOQuote.php?Updatedelivery_num=' . $myrow['po_num'] . '"target="_blank">打印 </td>
				 ';



            echo '
			</tr>';
            $i++;
            $RowIndex++;
            //end of page full new headings if
        } //end loop through vendors
        echo '<tr><td>小计</td><td>笔数</td><td>'. $all_line . '</td> <td>'.$all_po_all_amount.'</td> <td>'.$all_all_line_amount.'</td> <td></td> <td>'.$all_tax_amount.'</td> <td></td> <td></td> <td></td> <td></td> <td></td> <td></td> </tr>';
        echo '<tr><td>总计</td><td>笔数</td><td>' . $total_line . '</td> <td>'.$total_po_all_amount.'</td> <td>'.$total_all_line_amount.'</td> <td></td> <td>'.$total_tax_amount.'</td> <td></td> <td></td> <td></td> <td></td> <td></td> <td></td> </tr>';


        echo '</table>
        
        </div>';
        echo '<input type="hidden" name="JustSelectedAvendor" value="Yes" />';
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
        content: 'url:Searchbuliao5171.php?fwValue=&cat=buliao',
        init: function() {
            this.content.document.getElementById('cat').value = 'buliao';
            this.content.document.getElementById('fwValue').value = '';
        }
    });
</script>