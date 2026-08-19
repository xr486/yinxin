<?php
 ob_start();
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include('includes/session.inc');
$Title = _('采购单进货检测修改');
$ViewTopic= '采购单进货检测修改';
$BookMark = '采购单进货检测修改';

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

if(isset($_POST['Search']) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])){
    $sql = "select  distinct
h.receipt_num,
v.vendor_code,
v.vendor_name,
h.creation_date,h.created_by
from po_rcv_receipt_header h,vendors v,po_rcv_receipt_line l 
where h.vendor_code=v.vendor_code
and h.receipt_num=l.receipt_num
and  h.receipt_type='入库'
and l.delivery_quantity=0 ";
    if(isset($_POST['vendorCode']) and $_POST['vendorCode'] != ''){
        $sql = $sql." and v.vendor_code ".LIKE." '%".$_POST['vendorCode']."%' ";
    }
    if(isset($_POST['vendorName']) and $_POST['vendorName'] != ''){
        $sql = $sql." and v.vendor_name ".LIKE." '%".$_POST['vendorName']."%' ";
    }
	if(isset($_POST['receipt_num']) and $_POST['receipt_num'] != ''){
        $sql = $sql." and h.receipt_num ".LIKE." '%".$_POST['receipt_num']."%' ";
    }

     if(isset($_POST['FromDate']) and $_POST['FromDate'] != ''){
        $sql = $sql." and h.creation_date >=".strtotime($_POST['FromDate'])." ";
    }
     if(isset($_POST['ToDate']) and $_POST['ToDate'] != ''){
        $sql = $sql." and h.creation_date <=".strtotime($_POST['ToDate'])." ";
    }
    
  
  
    $result = DB_query($sql,$db);
    if (DB_num_rows($result)==0) {
        unset($result);
        prnMsg(_('找不到待检验的来料报检单，请重新输入条件查询！') ,'error');
    }
}


echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('采购单进货检测修改') . '</p>';
echo '<table cellpadding="3" class="selection">';
echo '<div class="text-nav">';
echo '<div class="text-nav-1"><div>' . _('供应商名称') . ':</div>';
echo '<input type="text" name="vendorName"   value="' . $_POST['vendorName'] . '" size="10" maxlength="25" /></div>';

echo '<div class="text-nav-1"><div>' . _('供应商代码') . ':</div>  ';
echo '<input type="text" name="vendorCode" value="' . $_POST['vendorCode'] . '" size="20" maxlength="25" /></div>';
echo '<div class="text-nav-1"><div>'. _('来料报检单号') . ':</div>  ';
echo '<input type="text" name="receipt_num" value="' . $_POST['receipt_num'] . '" size="20" maxlength="25" /></div>';
echo '</tr>';
//echo '</tr>';
if (!isset($_POST['FromDate'])) {
    $_POST['FromDate'] = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 30, date("Y")));
}
if (!isset($_POST['ToDate'])) {
    $_POST['ToDate'] = Date('Y-m-d', mktime(0, 0, 0, date("m"), date("d") + 1, date("Y")));
}
echo '<div class="text-nav-1"><div>' . _('来料报检日期起') . ':</div>';
echo '<input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="FromDate" maxlength="10" size="20" value="' . $_POST['FromDate'] . '"  /></div>';

echo '<div class="text-nav-1"><div>' . _('来料报检日期止') . ':</div>';
echo '<input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="ToDate" maxlength="10" size="20" value="' . $_POST['ToDate'] . '"  /> </div>';;
echo '</table><div class="centre"><input type="submit" name="Search" value="查找"> </div>'. '</br>';


if (isset($_POST['Search']) and isset($result) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])){
    $ListCount = DB_num_rows($result);
    $ListPageMax = ceil($ListCount /  $_SESSION['DisplayRecordsMax']);
    
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
    echo '<div class="text-nav-table">
                    <table cellpadding="2" class="selection" >';

    echo '<tr>
                    <th bgcolor="#87CEFA" class="ascending" width = 150>' . _('来料报检单号') . '</th>
                    <th bgcolor="#87CEFA" class="ascending"width = 150>' . _('供应商') . '</th>
         
                    <th bgcolor="#87CEFA" class="ascending"width = 250>' . _('供应商名称') . '</th>
                
                    <th bgcolor="#87CEFA" class="ascending"width = 170>' . _('收货时间') . '</th>
                    <th bgcolor="#87CEFA" class="ascending"width =150>' . _('收货账号') . '</th>
                   
            </tr>';
    $k = 0; //row counter to determine background colour
    $RowIndex = 0;
    


    if (DB_num_rows($result) <> 0) {
        DB_data_seek($result, ($_POST['PageOffset'] - 1) *  $_SESSION['DisplayRecordsMax']);
        $i = 0; //counter for input controls
        while (($myrow = DB_fetch_array($result)) AND ($RowIndex <>  $_SESSION['DisplayRecordsMax'])) {
			if ($k == 1) {
				echo '<tr class="EvenTableRows">';
				$k = 0;
			} else {
				echo '<tr class="OddTableRows">';
				$k = 1;
			}
			echo '  <td><a href="' . $RootPath . '/POCheckModify.php?NUM=' . $myrow['receipt_num'] . '">' . $myrow['receipt_num'] . '</td>
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