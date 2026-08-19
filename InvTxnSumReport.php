<?php
 ob_start();
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include('includes/session.inc');
$Title = _('库存交易汇总表');
$ViewTopic= '库存交易汇总表';
$BookMark = '库存交易汇总表';

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
    $sql = "SELECT a.transaction_type,c.item_no, 
a.uom, a.subinventory_from,c.item_name,c.item_desc,
 sum(a.quantity) quantity
FROM inv_transactions_all a, sf_item_no c
WHERE a.item_no = c.item_no  ";
    
    if(isset($_POST['FromDate']) and $_POST['FromDate'] != ''){
        $sql = $sql." and a.transaction_date >=".strtotime($_POST['FromDate'])." ";
    }
     if(isset($_POST['ToDate']) and $_POST['ToDate'] != ''){
        $sql = $sql." and a.transaction_date <=".strtotime($_POST['ToDate'])." ";
    }
	if (isset($_POST['item_no']) and $_POST['item_no'] != '') { 		
        $sql = $sql." and c.item_no ".LIKE." '%".$_POST['item_no']."%' ";
    } 
	if (isset($_POST['item_name']) and $_POST['item_name'] != '') { 		
        $sql = $sql." and c.item_name ".LIKE." '%".$_POST['item_name']."%' ";
    } 
  
	if (isset($_POST['loccode_from']) and $_POST['loccode_from'] != '' and $_POST['loccode_from'] != '全部') {
		
        $sql = $sql." and a.subinventory_from ".LIKE." '%".$_POST['loccode_from']."%' "; 
    } 
	if (isset($_POST['transaction_type']) and $_POST['transaction_type'] != '') {
        $sql = $sql . " and  a.transaction_type =  '" . $_POST['transaction_type'] . "' ";
    }

	 $sql = $sql . "group by a.transaction_type,c.item_no, a.uom, a.subinventory_from,  c.item_name,c.item_desc ";
 
    $result = DB_query($sql,$db);
    if (DB_num_rows($result)==0) {
        unset($result);
        prnMsg(_('找不到该来料报检单，请重新输入条件查询！') ,'error');
    }
}


echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('库存交易汇总表') . '</p>';
echo '<table cellpadding="3" class="selection">';
echo '<div class="text-nav">';
 
if (!isset($_POST['FromDate'])) {
    $_POST['FromDate'] = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 30, date("Y")));
}
if (!isset($_POST['ToDate'])) {
    $_POST['ToDate'] = Date('Y-m-d', mktime(0, 0, 0, date("m"), date("d") + 1, date("Y")));
}
echo '<div class="text-nav-1"><div>' . '交易日期' . _('起') . ':</div>
		<input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="FromDate" maxlength="10" size="11" value="' . $_POST['FromDate'] . '" /></div>
		<div class="text-nav-1"><div>' . _('交易日期止') . ':</div>
		<input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="ToDate" maxlength="10" size="11" value="' . $_POST['ToDate'] . '" /></div>';
echo '<div class="text-nav-1"><div>' . _('仓库') . ':</div>';
echo '<select name="loccode_from">  '; 
$sql = "select '全部' loccode, '全部' locationname from dual union SELECT loccode,locationname FROM locations where managed='Y'  ";

 $result1 = DB_query($sql, $db);     
 while ($Salesmanrow = DB_fetch_array($result1)) {		
     if ($Salesmanrow['loccode']==$_POST['loccode_from'] ) {
          echo ' <option value=' . $Salesmanrow['loccode']  . ' selected="selected">  '.  $Salesmanrow['locationname'] . ' 
         </option>    ';  
     } 
      else {
    echo ' <option value=' . $Salesmanrow['loccode']  . ' >  '.  $Salesmanrow['locationname'] . ' 
         </option>    ';
          
      } 
      
 }

 echo ' </select> </div>';
echo '<div class="text-nav-1"><div>' . _('料号') . ':</div>';
echo '<input type="text" name="item_no" value="' . $_POST['item_no'] . '" size="20" maxlength="25" /></div>';
echo '<div class="text-nav-1"><div>' . _('料号名称') . ':</div>';
echo '<input type="text" name="item_name" value="' . $_POST['item_name'] . '" size="20" maxlength="25" /></div>'; 

echo '<div class="text-nav-1"><div>' . _('交易类型') . ':</div>';
echo '<input type="text" name="transaction_type" value="' . $_POST['transaction_type'] . '" size="20" maxlength="25" /></div>';
 
echo '</div>'; 
 
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
    echo '
                    <table cellpadding="2" class="selection" >';

    echo '<tr>
                        
                <th bgcolor="#87CEFA" width =100>' . _('交易类型') . '</th>
                <th bgcolor="#87CEFA" width =100>' . '料号' . '</th>
                <th bgcolor="#87CEFA" width =200>' . '料号名称' . '</th>
                <th bgcolor="#87CEFA" width =200>' . '规格型号' . '</th>
                <th bgcolor="#87CEFA" width =70 >' . '交易数量' . '</th> 
                <th bgcolor="#87CEFA" width =70 >' . '单位' . '</th> 
                <th bgcolor="#87CEFA" width =60 >' . '仓库' . '</th>
										  
                                       
                                     
                                              
                   
            </tr>';
    $k = 0; //row counter to determine background colour
    $RowIndex = 0;
    
 

    if (DB_num_rows($result) <> 0) {
        DB_data_seek($result, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);
        $i = 0; //counter for input controls
        while (($myrow = DB_fetch_array($result)) AND ($RowIndex <> $_SESSION['DisplayRecordsMax'] )) {
			if ($k == 1) {
				echo '<tr class="EvenTableRows">';
				$k = 0;
			} else {
				echo '<tr class="OddTableRows">';
				$k = 1;
			} 
		         
			echo '<td>' . $myrow['transaction_type'] . '</td>';   
			echo '<td>' . $myrow['item_no'] . ' </td>';
                        echo '<td>' . $myrow['item_name'] . ' </td>';
			echo '<td>' . $myrow['item_desc'] . ' </td>';
			echo '<td>' . $myrow['quantity'] . ' </td>'; 
			echo '<td>' . $myrow['uom'] . ' </td>'; 
			echo '<td>' . $myrow['subinventory_from']. ' </td>'; 
            
			echo '
			</tr>';
			$i++;
			$RowIndex++;
			//end of page full new headings if
		} //end loop through vendors
		echo '</table>';
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
    echo '<br>
          <div class="centre">
                <a href="' . $RootPath . '/InvTxnSumReportExcel.php?FromDate=' .$_POST['FromDate'] .'&ToDate=' .$_POST['ToDate'] .
                '&loccode_from=' .$_POST['loccode_from'] . '&item_no=' .$_POST['item_no'] .'&item_name=' .$_POST['item_name'] . 
                '&transaction_type=' .$_POST['transaction_type']. '">
                ' .'资料导出Excel表' . '
                </a>
          </div>';

}
echo '</div></form>';
if (isset($_POST['add_new'])) {
    header('Location: AddSupplier.php');
}
include('includes/footer.inc');