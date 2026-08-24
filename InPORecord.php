<?php
 ob_start();
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include('includes/session.inc');
$Title = _('采购进料记录查询');
$ViewTopic= '采购进料记录查询';
$BookMark = '采购进料记录查询';

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
    $sql = "select * from (select a.receipt_num,a.receipt_line,
a.po_num,
a.po_line,
a.stockid,
case a.transaction_type 
	when 'RECEIVE' THEN '采购收货' 
	when 'ACCEPT' THEN '检验允收' 
	when 'REJECT' THEN '检验不良' 
	when 'POIN' THEN '采购入库' 
	when 'PORETURN' THEN '采购退库' 
	when 'REJECTTORETURN' THEN '不良退货' END  transaction_types,
a.transaction_quantity,
a.transaction_date,b.item_name,b.item_desc,rl.lot_num
from po_rcv_transactions a,sf_item_no b,po_rcv_receipt_line rl where a.stockid=b.item_no and rl.receipt_num=a.receipt_num and rl.receipt_line=a.receipt_line) aa where 1=1 ";
    if(isset($_POST['receipt_num']) and $_POST['receipt_num'] != ''){
        $sql = $sql." and receipt_num ".LIKE." '%".$_POST['receipt_num']."%' ";
    }
    if(isset($_POST['type']) and $_POST['type'] != ''){
        $sql = $sql." and transaction_types='".$_POST['type']."' ";
    }
	if(isset($_POST['po_num']) and $_POST['po_num'] != ''){
        $sql = $sql." and po_num ".LIKE." '%".$_POST['po_num']."%' ";
    }
	if(isset($_POST['item_no']) and $_POST['item_no'] != ''){
        $sql = $sql." and stockid ".LIKE." '%".$_POST['item_no']."%' ";
    }
	if(isset($_POST['item_name']) and $_POST['item_name'] != ''){
        $sql = $sql." and item_name ".LIKE." '%".$_POST['item_name']."%' ";
    }
	 
	  if (empty($_POST['FromDate']) == 0) {
        $SQL_FromDate = strtotime($_POST['FromDate']);
        $sql .= " and  transaction_date >= '" . $SQL_FromDate . "' ";
    }
    if (empty($_POST['ToDate']) == 0) {
        $SQL_ToDate = strtotime($_POST['ToDate']) + 86400;
        //echo $SQL_ToDate;
        $sql .= " and  transaction_date <='" . $SQL_ToDate . "' ";
    }
  
  
    $sql .=" order by aa.transaction_date desc, aa.receipt_num,aa.receipt_line  ";
    // echo $sql;
    $result = DB_query($sql,$db);
    if (DB_num_rows($result)==0) {
        unset($result);
        prnMsg(_('找不到该来料报检单，请重新输入条件查询！') ,'error');
    }
}


echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('查找采购进料记录') . '</p>';
echo '<table cellpadding="3" class="selection">';
echo '<div class="text-nav">';

echo '<div class="text-nav-1"><div>' . _('来料报检单号') . ':</div>';
echo '<input type="text" name="receipt_num" value="' . $_POST['receipt_num'] .'" size="20" maxlength="25" /></div>';

echo '<div class="text-nav-1"><div>' . _('交易类型') . ':</div><select name="type">';


$sql = "SELECT distinct case transaction_type 
	when 'RECEIVE' THEN '采购收货' 
	when 'ACCEPT' THEN '检验允收' 
	when 'REJECT' THEN '检验不良' 
	when 'POIN' THEN '采购入库' 
	when 'PORETURN' THEN '采购退库' 
	when 'REJECTTORETURN' THEN '不良退货' END  transaction_types FROM po_rcv_transactions ";
$result1 = DB_query($sql, $db);
echo '<option  selected="selected" value=""></option>';
while ($Salesmanrow = DB_fetch_array($result1)) {
       if ($Salesmanrow['transaction_types']==$_POST['type'] ) {
			 echo ' <option value=' . $Salesmanrow['transaction_types']  . ' selected="selected">  '.  $Salesmanrow['transaction_types'] . '</option>';  
		} 
		 else  {
			
			echo ' <option value=' . $Salesmanrow['transaction_types']  . ' >  '.  $Salesmanrow['transaction_types'] . '</option>';
		
		 } 
}

echo '</select></div>'; 

//  echo '<option  selected="selected" value=""></option>';
// echo '<option   value="RECEIVE">采购收货</option>';
// echo '<option   value="ACCEPT">检验允收</option>';
// echo '<option   value="REJECT">检验不良</option>';
// echo '<option   value="POIN">采购入库</option>';   //库存增加
// echo '<option   value="PORETURN">采购退库</option>';  //库存减少
// echo '<option   value="REJECTTORETURN">不良退货</option>';
// echo '</select></div> ';

echo '<div class="text-nav-1"><div>' . _('采购单号') . ':</div>';
echo '<input type="text" name="po_num" value="' . $_POST['po_num'] .'" size="20" maxlength="25" /></div>';
 

echo '<div class="text-nav-1"><div>' . _('料号') . ':</div>';
echo '<input type="text" name="item_no" value="' . $_POST['item_no'] .'" size="20" maxlength="25" /></div>';
echo '<div class="text-nav-1"><div>' . _('料号名称') . ':</div>';
echo '<input type="text" name="item_name" value="' . $_POST['item_name'] .'" size="20" maxlength="25" /></div>';
// echo '<input type="text" name="item_name" value="' . $_POST['item_name'] . '" size="20" maxlength="25" /></td>';
if (!isset($_POST['FromDate'])) {
    $_POST['FromDate'] = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 30,date("Y")));
}
if (!isset($_POST['ToDate'])) {
    $_POST['ToDate'] = Date('Y-m-d');
}

echo '<div class="text-nav-1"><div>' . '交易日期' . _('起') . ':</div>
		<input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] .
    '" name="FromDate" maxlength="10" size="11" value="' . $_POST['FromDate'] . '" /></div>
    <div class="text-nav-1"><div>' . _('交易日期止') . ':</div>
		<input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] .
    '" name="ToDate" maxlength="10" size="11" value="' . $_POST['ToDate'] . '" /></div>
	</div>';






echo '</table><div class="centre"><input type="submit" name="Search" value="查找"></div>';

if (isset($_POST['Search']) and isset($result) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])){
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
    echo ' <div class="text-nav-table">
                    <table cellpadding="2" class="selection" >';

    echo '<tr>
                    <th bgcolor="#87CEFA" class="ascending" width = 150>' . _('收货单号') . '</th>
					<th bgcolor="#87CEFA"   width = 20>' . _('行') . '</th>
                    <th bgcolor="#87CEFA"  width = 150>' . _('采购单号') . '</th>         
                    <th bgcolor="#87CEFA"  width = 20>' . _('行') . '</th>                
                    <th bgcolor="#87CEFA"  width = 150>' . _('料号') . '</th>         
                    <th bgcolor="#87CEFA"  width = 150>' . _('料号名称') . '</th>         
                    <th bgcolor="#87CEFA" >' . _('规格型号') . '</th>
                    <th bgcolor="#87CEFA"  width = 80>' . _('交易类别') . '</th>
                    <th bgcolor="#87CEFA"  width = 80>' . _('交易数量') . '</th>
                    <th bgcolor="#87CEFA"  width = 80>' . _('批号') . '</th>
                   <th bgcolor="#87CEFA"  width = 160>' . _('交易时间') . '</th>
                   <th bgcolor="#87CEFA">' . _('打印') . '</th>
                   
            </tr>';
    $k = 0; //row counter to determine background colour
    $RowIndex = 0;
    


    if (DB_num_rows($result) <> 0) {
        DB_data_seek($result, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);
        $i = 0; //counter for input controls
        while (($myrow = DB_fetch_array($result)) AND ($RowIndex <> $_SESSION['DisplayRecordsMax'])) {
      
			if ($k == 1) {
				echo '<tr class="EvenTableRows">';
				$k = 0;
			} else {
				echo '<tr class="OddTableRows">';
				$k = 1;
			}
			echo '  <td>' . $myrow['receipt_num'] . '</td>
			<td>' . $myrow['receipt_line'] . '</td>
				<td>' . $myrow['po_num'] . '</td>
				<td>' . $myrow['po_line'] . '</td>
				<td>' . $myrow['stockid'] . '</td>
				<td>' . $myrow['item_name'] . '</td>
				<td>' . $myrow['item_desc'] . '</td>
                <td>' . $myrow['transaction_types'] . '</td>
                <td>' . $myrow['transaction_quantity'] . '</td>
                <td>' . $myrow['lot_num'] . '</td>
                <td>' . date('Y-m-d H:i:s',$myrow['transaction_date']) . '</td>';
                if ($myrow['transaction_types'] == '采购退库') {
                echo '<td><a href="' . $RootPath . '/PrintPOReturn.php?OrderNum=' .$myrow['receipt_num'] . '"  target="_blank" >打印</a></td>';
			} else{
				echo '<td></td>';
			}
            // if ($myrow['transaction_type'] == 'POIN') {
			// 	echo '<td><font color="blue">' . $myrow['transaction_quantity'] . '</td>';
				 
			// } elseif($myrow['transaction_type'] == 'PORETURN') {
			// 	echo '<td><font color="red">' . $myrow['transaction_quantity'] . '</td>';
			 
			// }
			 
			
       
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