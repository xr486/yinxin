<?php
 ob_start();
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include('includes/session.inc');
$Title = _('呆滞料明细报表');
$ViewTopic= '呆滞料明细报表';
$BookMark = '呆滞料明细报表';

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
    $sql = 'select b.item_no,
                    b.safe_qty,
                    b.min_qty,
                    b.max_qty,
                    b.item_desc,
                    b.item_name,
                    b.units, 
            sum(a.quantity ) quantity ,
                    a.subinventory_code,(select  price from po_lines_all c where c.stockid=b.item_no 
					and po_line_id in (select max(po_line_id) from po_lines_all d where d.stockid=b.item_no  ) ) price
            from inv_onhand_quantity_all a,
                    sf_item_no b
            where a.stockid=b.item_no 
			and a.quantity>0 ';

    if (isset($_POST['item_no']) and $_POST['item_no'] != '') { 
		$sql = $sql." and b.item_no ".LIKE." '%".$_POST['item_no']."%' ";
    }
    
	 if ($_SESSION['UserID']=='CZCK') {
     $sql = $sql." and a.subinventory_code  like '常州%'";
   }

    if(isset($_POST['locName'])and $_POST['locName'] != '' and $_POST['locName'] != '全部'){
        $sql = $sql." and a.subinventory_code ='".$_POST['locName']."'";
    }
     if(isset($_POST['item_name']) and $_POST['item_name'] != ''){
        $sql = $sql." and b.item_name ".LIKE." '%".$_POST['item_name']."%' ";
     }
	 if(isset($_POST['item_desc']) and $_POST['item_desc'] != ''){
        $sql = $sql." and b.item_desc ".LIKE." '%".$_POST['item_desc']."%' ";
     }

	 if (empty($_POST['FromDate']) == 0) {
        $SQL_FromDate = strtotime($_POST['FromDate']);
        $sql .= " and b.item_no not in (select item_no from inv_transactions_all ita where  
		  ita.transaction_date >= '" . $SQL_FromDate . "'";
		if(isset($_POST['locName'])and $_POST['locName'] != '' and $_POST['locName'] != '全部'){
        $sql = $sql." and ita.subinventory_from ='".$_POST['locName']."'";
        }

		if(isset($_POST['txn_type']) and $_POST['txn_type'] != ''){
        $sql = $sql." and ita.transaction_type ='".$_POST['txn_type']."' ";
     }

		 $sql .= " ) ";
    }
  
  $_SESSION['locName' . $identifier]=$_POST['locName'];

  $sql = $sql."GROUP BY a.subinventory_code, b.item_no,b.units,b.safe_qty,b.min_qty,b.max_qty,b.item_name,b.item_desc";
   //echo $sql;
    $result = DB_query($sql,$db);
    if (DB_num_rows($result)==0) {
        unset($result);
        prnMsg(_('找不到库存，请重新输入条件查询！') ,'error');
    }
}


echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('查找料号') . '</p>';
echo '<table cellpadding="3" class="selection">';
echo '<div class="text-nav">';

echo '<div class="text-nav-1"><div>' . _('料号') . ':</div>';
echo '<input type="text" id="text_slect_buliao" name="item_no" value="' . $_POST['item_no'] . '" size="20" maxlength="25" />
</div>';
echo '<div class="text-nav-1"><div>' . _('料号名称') . ':</div>
';
echo '<input type="text" id="text_slect_item_name" name="item_name" value="' . $_POST['item_name'] . '" size="20" maxlength="25" />
</div>';
 echo '<div class="text-nav-1"><div>' . _('规格型号') . ':</div>';
echo '<input type="text" name="item_desc" value="' . $_POST['item_desc'] . '" size="20" maxlength="25" /></div>
<div class="text-nav-1"><div>' . _('交易类型') . ':</div>';
echo '<input type="text" name="txn_type" value="' . $_POST['txn_type'] . '" size="20" maxlength="25" /></div>

<div class="text-nav-1"><div>' . _('交易截止日期') . ':</div>
		<input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="FromDate" maxlength="10" size="11" value="' . $_POST['FromDate'] . '" /></div>';
echo '<div class="text-nav-1"><div>' . _('仓库') . ':</div>';
 echo '<select name="locName">  '; 
   $sql = "select '全部' loccode, '全部' locationname from dual union SELECT loccode,locationname FROM locations where managed='Y' ";
 
    $result1 = DB_query($sql, $db);     
    while ($Salesmanrow = DB_fetch_array($result1)) {		
		if ($Salesmanrow['loccode']==$_POST['locName'] ) {
			 echo ' <option value=' . $Salesmanrow['loccode']  . ' selected="selected">  '.  $Salesmanrow['locationname'] . ' 
            </option>    ';  
		} 
		 else {
       echo ' <option value=' . $Salesmanrow['loccode']  . ' >  '.  $Salesmanrow['locationname'] . ' 
            </option>    ';
			 
		 } 
		 
	}
  
	echo ' </select> </div>';
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
                    <th bgcolor="#87CEFA" class="ascending"width =80>' . _('仓库') . '</th>
                    <th bgcolor="#87CEFA" class="ascending"width = 150>' . _('料号') . '</th>
                    <th bgcolor="#87CEFA" class="ascending"width = 150>' . _('料号名称') . '</th>
					<th bgcolor="#87CEFA" class="ascending"width = 150>' . _('规格型号') . '</th>        
                    <th bgcolor="#87CEFA" class="ascending"width = 50>' . _('单位') . '</th>
                
                    <th bgcolor="#87CEFA" class="ascending"width = 150>' . _('数量') . '</th>  
                    <th bgcolor="#87CEFA" class="ascending"width = 150>' . _('单价') . '</th> 
                    <th bgcolor="#87CEFA" class="ascending"width = 150>' . _('金额') . '</th> 
                    
					
                   
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

			  echo ' <td>' . $myrow['subinventory_code'] . '</td>
                    <td>' . $myrow['item_no'] . '</td>
                    <td>' . $myrow['item_name'] . '</td>
				    <td>' . $myrow['item_desc'] . '</td>
				    <td>' . $myrow['units'] . '</td>
				    <td>' . $myrow['quantity'] . '</td> 
				    <td>' . $myrow['price'] . '</td>
				    <td>' . ($myrow['quantity'] * $myrow['price']) . '</td>  ';
 
           
            


//     echo '
//		<td colspan="2" align="center" ><div style="width:80px; height:80px;">
//		<img  width="80" height="80" src='.$_POST['PicPath'].'></div>
//		</td>
//	  ';
	    // echo "<img src='$PicPath'><br>\n";

			echo '
			</tr>';
			$i++;
			$RowIndex++;
			//end of page full new headings if
		} //end loop through vendors
		echo '</table>';
		echo '<input type="hidden" name="JustSelectedAvendor" value="Yes" />';

		echo '<div>
            <a href="' . $RootPath . '/InvDaizhiReportExcel.php?item_no=' .$_POST['item_no'].'&item_name='.$_POST['item_name'] .
            '&item_desc=' .$_POST['item_desc'] .'&txn_type=' .$_POST['txn_type'] .'&FromDate=' .$_POST['FromDate'] .'&subinventory_code='.$_POST['locName'] .' ">' .'资料导出Excel表' . '</a>
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

