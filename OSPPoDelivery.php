<?php
 ob_start();
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include('includes/session.inc');
$Title = _('外协采购单检验');
$ViewTopic= '外协采购单检验';
$BookMark = '外协采购单检验';

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

$sql = "select  distinct
h.receipt_num,
v.vendor_code,
v.vendor_name,wla.wip_entity_name,wla.po_num,
h.creation_date,h.created_by
from po_rcv_receipt_header h,vendors v,po_rcv_receipt_line l ,po_lines_all wla,po_headers_all a
where h.vendor_code=v.vendor_code 
and h.receipt_num=l.receipt_num and l.po_num=wla.po_num    and l.po_line=wla.line and a.order_type = '外协采购' and a.po_num=wla.po_num 
and l.wait_inspect_quantity>0 ";
  $result = DB_query($sql,$db);

if(isset($_POST['Search']) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])){
    $sql = "select  distinct
h.receipt_num,
v.vendor_code,
v.vendor_name,wla.wip_entity_name,wla.po_num,
h.creation_date,h.created_by
from po_rcv_receipt_header h,vendors v,po_rcv_receipt_line l ,po_lines_all wla,po_headers_all a
where h.vendor_code=v.vendor_code 
and h.receipt_num=l.receipt_num and l.po_num=wla.po_num    and l.po_line=wla.line and a.order_type = '外协采购' and a.po_num=wla.po_num 
and l.wait_inspect_quantity>0 ";
    if(isset($_POST['vendorCode']) and $_POST['vendorCode'] != ''){
        $sql = $sql." and v.vendor_code ".LIKE." '%".$_POST['vendorCode']."%' ";
    }
    if(isset($_POST['vendorName']) and $_POST['vendorName'] != ''){
        $sql = $sql." and v.vendor_name ".LIKE." '%".$_POST['vendorName']."%' ";
    }
	if(isset($_POST['receipt_num']) and $_POST['receipt_num'] != ''){
        $sql = $sql." and h.receipt_num ".LIKE." '%".$_POST['receipt_num']."%' ";
    }
	if(isset($_POST['wip_entity_name']) and $_POST['wip_entity_name'] != ''){
        $sql = $sql." and wla.wip_entity_name ".LIKE." '%".$_POST['wip_entity_name']."%' ";
    }
	if(isset($_POST['po_num']) and $_POST['po_num'] != ''){
        $sql = $sql." and wla.po_num ".LIKE." '%".$_POST['po_num']."%' ";
    }

     if(isset($_POST['FromDate']) and $_POST['FromDate'] != ''){
        $sql = $sql." and h.creation_date >=".strtotime($_POST['FromDate'])." ";
    }
     if(isset($_POST['ToDate']) and $_POST['ToDate'] != ''){
        $sql = $sql." and h.creation_date <=".strtotime($_POST['ToDate'])." ";
    }
    
  
  
    $result = DB_query($sql,$db);
    if (DB_num_rows($result)==0) {
       // unset($result);
        prnMsg(_('找不到待检验的报检单，请重新输入条件查询！') ,'error');
    }
}


echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('查找待检验报检单') . '</p>';
echo '<table cellpadding="3" class="selection">';
echo '<div class="text-nav">';
echo '<div class="text-nav-1"><div>' . _('供应商名称') . ':</div>';
echo '<input type="text" id="text_slect_name" name="vendorName" value="' . $_POST['vendorName'] . '" size="20" maxlength="25" />
<image class="select_img" src="img/search.png" id="btn_slect_vendor"/>
</div>';
echo '<div class="text-nav-1"><div>' . _('供应商代码') . ':</div>';
echo '<input type="text" id="text_slect_vendor" name="vendorCode" value="' . $_POST['vendorCode'] . '" size="20" maxlength="25" />
<image class="select_img" src="img/search.png" id="btn_slect_vendor_a"/>
</div>';
echo '<div class="text-nav-1"><div>' . _('报检单号') . ':</div>';
echo '<input type="text" name="receipt_num" value="' . $_POST['receipt_num'] . '" size="20" maxlength="25" /></div>';
echo '<div class="text-nav-1"><div>' . _('工单号') . ':</div>';
echo '<input type="text" name="wip_entity_name" value="' . $_POST['wip_entity_name'] . '" size="20" maxlength="25" /></div>';
echo '<div class="text-nav-1"><div>' . _('采购单号') . ':</div>';
echo '<input type="text" name="po_num" value="' . $_POST['po_num'] . '" size="20" maxlength="25" /></div>';

//echo '</tr>';
if (!isset($_POST['FromDate'])) {
    $_POST['FromDate'] = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 30, date("Y")));
}
if (!isset($_POST['ToDate'])) {
    $_POST['ToDate'] = Date('Y-m-d', mktime(0, 0, 0, date("m"), date("d") + 1, date("Y")));
}
echo '<div class="text-nav-1"><div>' . '暂收日' . _('起') . ':</div>
		<input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="FromDate" maxlength="10" size="11" value="' . $_POST['FromDate'] . '" /></div>
                <div class="text-nav-1"><div>' . _('暂收日止') . ':</div>
                <input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="ToDate" maxlength="10" size="11" value="' . $_POST['ToDate'] . '" /></div>
	</div>';



echo '</table><div class="centre"><input type="submit" name="Search" value="查找"></div>';

if (isset($_POST['Search']) or isset($result) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])){
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
                    <th class="ascending" width = 100>' . _('报检单号') . '</th>
					<th class="ascending" width = 150>' . _('工单号') . '</th>
					<th class="ascending" width = 100>' . _('采购单号') . '</th>
                    <th class="ascending"width = 100>' . _('供应商') . '</th>
         
                    <th class="ascending"width = 250>' . _('供应商名称') . '</th>
                
                    <th class="ascending"width = 170>' . _('收货时间') . '</th>
                    <th class="ascending"width =150>' . _('收货账号') . '</th>
                   
            </tr>';
    $k = 0; //row counter to determine background colour
    $RowIndex = 0;
 


    if (DB_num_rows($result) <> 0) {
        DB_data_seek($result, ($_POST['PageOffset'] - 1) * 10);
        $i = 0; //counter for input controls
        while (($myrow = DB_fetch_array($result)) AND ($RowIndex <> 10)) {
			if ($k == 1) {
				echo '<tr class="EvenTableRows">';
				$k = 0;
			} else {
				echo '<tr class="OddTableRows">';
				$k = 1;
			}
			echo '  <td><a href="' . $RootPath . '/OSPPOCheck.php?NUM=' . $myrow['receipt_num'] . '">' . $myrow['receipt_num'] . '</td>
			<td>' . $myrow['wip_entity_name'] . '</td>
			<td>' . $myrow['po_num'] . '</td>
				<td>' . $myrow['vendor_code'] . '</td>
				<td>' . $myrow['vendor_name'] . '</td>
				<td>' . date('Y-m-d H:i:s',$myrow['creation_date']) . '</td>
				<td>' . $myrow['created_by'] . '</td>
				
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
</script>