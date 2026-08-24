<?php
 ob_start();
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include('includes/session.inc');
$Title = _('外协采购入库汇总报表');
$ViewTopic= '外协采购入库汇总报表';
$BookMark = '外协采购入库汇总报表';

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
$sql = "SELECT a.vendor_code, d.vendor_name,b.stockid,g.item_name,  b.operation_code, sum(prt.transaction_quantity) transaction_quantity
FROM po_headers_all a, po_lines_all b, vendors d, po_rcv_transactions prt,po_rcv_receipt_line prr,sf_item_no g
WHERE  a.po_num = b.po_num
and b.stockid=g.item_no
AND a.vendor_code = d.vendor_code
AND b.po_num = prr.po_num 
and prr.receipt_num=prt.receipt_num 
and  prt.transaction_type in ('POIN')
and b.stockid=prr.stockid
AND b.line = prr.po_line and a.order_type = '外协采购'   ";

$sql .=" group by a.vendor_code, d.vendor_name,b.stockid,g.item_name,  b.operation_code"; 
// $sql .=" order by prt.creation_date desc "; 
$result = DB_query($sql,$db);
if(isset($_POST['Search']) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])){
  

    $sql =  "SELECT a.vendor_code, d.vendor_name,b.stockid,g.item_name,  b.operation_code, sum(prt.transaction_quantity) transaction_quantity
FROM po_headers_all a, po_lines_all b, vendors d, po_rcv_transactions prt,po_rcv_receipt_line prr,sf_item_no g
WHERE  a.po_num = b.po_num
and b.stockid=g.item_no
AND a.vendor_code = d.vendor_code
AND b.po_num = prr.po_num 
and prr.receipt_num=prt.receipt_num 
and  prt.transaction_type in ('POIN')
and b.stockid=prr.stockid
AND b.line = prr.po_line  and a.order_type = '外协采购'    
  ";


    if(isset($_POST['vendorCode']) and $_POST['vendorCode'] != ''){
        $sql = $sql." and d.vendor_code ".LIKE." '%".$_POST['vendorCode']."%' ";
    }
    if(isset($_POST['vendorName']) and $_POST['vendorName'] != ''){
        $sql = $sql." and d.vendor_name ".LIKE." '%".$_POST['vendorName']."%' ";
    }
    // if(isset($_POST['FromDate']) and $_POST['FromDate'] != ''){
    //     $sql = $sql." and prt.delivery_date >=".strtotime($_POST['FromDate'])." ";
    // }
    //  if(isset($_POST['ToDate']) and $_POST['ToDate'] != ''){
    //     $sql = $sql." and prt.delivery_date <=".strtotime($_POST['ToDate'])." ";
    // }
	if (isset($_POST['Stockid_from']) and $_POST['Stockid_from'] != '') { 
        $sql = $sql." and prr.stockid ".LIKE." '%".$_POST['Stockid_from']."%' ";
    }
    if (isset($_POST['item_name']) and $_POST['item_name'] != '') { 
        $sql = $sql." and g.item_name ".LIKE." '%".$_POST['item_name']."%' ";
    }
    if (isset($_POST['receipt_num_from']) and $_POST['receipt_num_from'] != '') {
        $sql = $sql . " and prt.receipt_num ".LIKE." '%".$_POST['receipt_num_from']."%' ";
    }
     

	if (isset($_POST['po_num_from']) and $_POST['po_num_from'] != '') {
        $sql = $sql . " and a.po_num  ".LIKE." '%".$_POST['po_num_from']."%' ";
    }
    if (isset($_POST['operation_code']) and $_POST['operation_code'] != '') { 
        $sql = $sql." and b.operation_code ".LIKE." '%".$_POST['operation_code']."%' ";
    }

	$sql .=" group by a.vendor_code, d.vendor_name,b.stockid,g.item_name,   b.operation_code";
//    $sql .=" order by prt.delivery_date desc ";
    $result = DB_query($sql,$db);
    if (DB_num_rows($result)==0) {
       // unset($result);
        prnMsg(_('找不到该来料报检单，请重新输入条件查询！') ,'error');
    }
}


echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '
        <div class="centre" style="margin-bottom: 5px; display: flex;justify-content: flex-start;align-items: center;">
        <input type="submit" name="Search" value="查找"> &nbsp;&nbsp;';
        // echo '  <div class="export" >
        //   <a href="' . $RootPath . '/OspInPODeliverySumReportExcel.php?vendorName=' .$_POST['vendorName'] .'&vendorCode=' .$_POST['vendorCode'] .'&FromDate=' .$_POST['FromDate'] . '&ToDate=' .$_POST['ToDate'] .'&Stockid_from=' .$_POST['Stockid_from'] . '&item_name=' .$_POST[''] .'&po_num_from=' .$_POST['po_num_from'] .'&operation_code=' .$_POST['operation_code'] . '&receipt_num_from=' .$_POST['receipt_num_from'] .' ">' .'导出' . '</a>
        //   </div>';
          echo '</div>
';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('外协采购入库汇总报表') . '</p>';
echo '<table cellpadding="3" class="selection">';

echo '<div class="text-nav">';
echo '<div class="text-nav-1"><div>' . _('供应商名称') . ':</div>';
echo '<input type="text"   autocomplete="off"   id="text_slect_name" name="vendorName" value="' . $_POST['vendorName'] . '" size="20" maxlength="25" />
<a class="btn btn-info btn-xs" id="btn_slect_vendor" hfre="###" title="选择供应商">选</a>
</div>';
echo '<div class="text-nav-1"><div>' . _('供应商代码') . ':</div>
';
echo '<input type="text"   autocomplete="off"   id="text_slect_vendor" name="vendorCode" value="' . $_POST['vendorCode'] . '" size="20" maxlength="25" />
<a class="btn btn-info btn-xs" id="btn_slect_vendor_a" hfre="###" title="选择供应商">选</a>
</div>';
 
if (!isset($_POST['FromDate'])) {
    $_POST['FromDate'] = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 30, date("Y")));
}
if (!isset($_POST['ToDate'])) {
    $_POST['ToDate'] = Date('Y-m-d', mktime(0, 0, 0, date("m"), date("d") + 1, date("Y")));
}
echo '<div class="text-nav-1"><div>' . '入库日期' . _('起') . ':</div>
		<input type="text"   autocomplete="off"   onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="FromDate" maxlength="10" size="11" value="' . $_POST['FromDate'] . '" /></div>
		<div class="text-nav-1"><div>' . _('入库日期止') . ':</div>
		<input type="text"   autocomplete="off"   onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="ToDate" maxlength="10" size="11" value="' . $_POST['ToDate'] . '" /></div>
';

echo '<div class="text-nav-1"><div>' . _('零件图号') . ':</div>';
echo '<input type="text"   autocomplete="off"   id="text_slect_buliao" name="Stockid_from" value="' . $_POST['Stockid_from'] . '" size="20" maxlength="25" />
<a class="btn btn-info btn-xs" id="btn_slect_buliao" hfre="###" title="选择产品">选</a>
</div>';
echo '<div class="text-nav-1"><div>' . _('零件名称') . ':</div>
';
echo '<input type="text"   autocomplete="off"   id="text_slect_item_name" name="item_name" value="' . $_POST['item_name'] . '" size="20" maxlength="25" />
<a class="btn btn-info btn-xs" id="btn_slect_buliao2" hfre="###" title="选择产品">选</a>
</div>';
 

echo '<div class="text-nav-1"><div>' . _('采购单') . ':</div>';
echo '<input type="text"   autocomplete="off"   name="po_num_from" value="' . $_POST['po_num_from'] . '" size="20" maxlength="25" /></div>';
echo '<div class="text-nav-1"><div>' . _('工序名') . ':</div>
';
echo '<input type="text"   autocomplete="off"   name="operation_code" value="' . $_POST['operation_code'] . '" size="20" maxlength="25" /></div>';


echo '<div class="text-nav-1"><div>' . _('收料单') . ':</div>';
echo '<input type="text"   autocomplete="off"   name="receipt_num_from" value="' . $_POST['receipt_num_from'] . '" size="20" maxlength="25" /></div>';
 
echo '</div>';
 
echo '</table><div class="centre"></div>';

//总计
if ( isset($result)) {
	$total_line=0;
	 while (($myrow2 = DB_fetch_array($result))  ) {
		        
						$total_line=$total_line+1;
                        $total_transaction_quantity = $total_transaction_quantity+$myrow2['transaction_quantity'];
      }
  }




if (isset($_POST['Search']) or isset($result) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])){
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
    echo '<input type="hidden" name="PageOffset" value="' . $_POST['PageOffset'] . '" />';
    if ($ListPageMax > 1) {
        echo '<br /><div class="centre">&nbsp;&nbsp;' . _('第') . '' . $_POST['PageOffset'] . ' ' . _('页，共') . ' ' . $ListPageMax . ' ' . _('页') . '. ' . _('转到页') . ': ';
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

    echo '<tr>
                        <th width =100 >' . '供应商编码' . '</th>
										<th  width = 250>' . _('供应商名称') . '</th> 
                                        <th width =150>' . '零件图号' . '</th> 
                                        <th width =150>' . '零件名称' . '</th> 
                                         <th width =70 >' . '工序名' . '</th>
                                         <th width =80 >' . '入库数量' . '</th>      
                   
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
                    $all_transaction_quantity = $all_transaction_quantity+$myrow['transaction_quantity'];
		  
            echo '<td>' . $myrow['vendor_code'] . '</td>';
			echo '<td>' . $myrow['vendor_name'] . '</td>';              
            echo '<td>' . $myrow['stockid'] . ' </td>';             
            echo '<td>' . $myrow['item_name'] . ' </td>'; 
            echo '<td>' . $myrow['operation_code']. ' </td>';
            echo '<td>' . $myrow['transaction_quantity'] . ' </td>';  
			echo '
			</tr>';
			$i++;
			$RowIndex++;
			//end of page full new headings if
		} //end loop through vendors
        echo'<tr> <td>小计</td> <td>笔数</td> <td>'.$all_line.'</td>  <td></td> <td></td> <td>'.$all_transaction_quantity.'</td> </tr>'; 
        echo'<tr> <td>总计</td> <td>笔数</td> <td>'.$total_line.'</td>  <td></td> <td></td> <td>'.$total_transaction_quantity.'</td> </tr>'; 

		echo '</table></div>';
		echo '<input type="hidden" name="JustSelectedAvendor" value="Yes" />';
       
				echo '<div>
                 
                </div>';
	}

        if (isset($ListPageMax) AND $ListPageMax > 1) {          
            echo '<br /><div class="centre">&nbsp;&nbsp;' . _('第') . '' . $_POST['PageOffset'] . ' ' . _('页，共') . ' ' . $ListPageMax . ' ' . _('页') . '. ' . _('转到页') . ': ';
            echo '<select name="PageOffset2">';
                $ListPage = 1;
                while ($ListPage <= $ListPageMax) {
                        if ($ListPage == $_POST['PageOffset']) {
                                echo '<option value="' . $ListPage . '" selected="selected">' . $ListPage . '</option>';
                        } 
                        else {
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
if (isset($_POST['add_new'])) {
    header('Location: AddSupplier.php');
}
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