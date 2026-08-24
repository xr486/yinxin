<?php
 ob_start();
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include('includes/session.inc');
$Title = _('仓库库存查询');
$ViewTopic= '仓库库存查询';
$BookMark = '仓库库存查询';

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
    $sql = 'select aa.* from (select sum(b.quantity) onhand,b.stockid,c.safe_qty,c.item_name,c.item_desc,c.item_category1
            from   inv_onhand_quantity_all b,sf_item_no c where b.stockid = c.item_no GROUP BY b.stockid,c.safe_qty,c.item_name,c.item_desc,c.item_category1) aa
            where aa.safe_qty>aa.onhand ';

    if (isset($_POST['Stockid_from']) and $_POST['Stockid_from'] != '') {
        $sql = $sql . " and aa.stockid >=  '" . $_POST['Stockid_from'] . "'";
    }
    if (isset($_POST['Stockid_to']) and $_POST['Stockid_to'] != '') {
        $sql = $sql . " and aa.stockid <=  '" . $_POST['Stockid_to'] . "' ";
    }
   
     if(isset($_POST['item_name']) and $_POST['item_name'] != ''){
        $sql = $sql." and aa.item_name ".LIKE." '%".$_POST['item_name']."%' ";
    }
	if(isset($_POST['item_desc']) and $_POST['item_desc'] != ''){
        $sql = $sql." and aa.item_desc ".LIKE." '%".$_POST['item_desc']."%' ";
    }
	if(isset($_POST['item_category1']) and $_POST['item_category1'] != ''){
        $sql = $sql." and aa.item_category1 ".LIKE." '%".$_POST['item_category1']."%' ";
    }
	 
  
  $sql = $sql." order BY aa.stockid";
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
echo  '<div class="text-nav-1"> <div>'. _('料号起') . ':</div>';
echo '<input type="text" name="Stockid_from" value="' . $_POST['Stockid_from'] . '" size="20" maxlength="25" /></div>';

echo '<div class="text-nav-1">';
echo '<div>' . _('料号止') . ':</div>';
echo '<input type="text" name="Stockid_to" value="' . $_POST['Stockid_to'] . '" size="20" maxlength="25" /></div>';
echo '<div class="text-nav-1"><div>' . _('料号名称') . ':</div>';
echo '<input type="text" name="item_name" value="' . $_POST['item_name'] . '" size="20" maxlength="25" /></div>';
 echo '<div class="text-nav-1"><div>' . _('规格型号') . ':</div>';
echo '<input type="text" name="item_desc" value="' . $_POST['item_desc'] . '" size="20" maxlength="25" /></div>';
 echo '<div class="text-nav-1"><div>' . _('产品分类') . ':</div>';
echo '<input type="text" name="item_category1" value="' . $_POST['item_category1'] . '" size="20" maxlength="25" /></div>';

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
                    <th bgcolor="#87CEFA" class="ascending"width =100>' . _('产品分类') . '</th>
                    <th bgcolor="#87CEFA" class="ascending"width = 150>' . _('料号') . '</th>
                    <th bgcolor="#87CEFA" class="ascending"width = 250>' . _('料号名称') . '</th>
					<th bgcolor="#87CEFA" class="ascending"width = 250>' . _('规格型号') . '</th>        
                    <th bgcolor="#87CEFA" width = 50>' . _('单位') . '</th>                
                    <th bgcolor="#87CEFA"  width = 150>' . _('库存数量') . '</th>
                    <th bgcolor="#87CEFA"  width = 100>' . _('安全库存') . '</th>  
                    
					
                   
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

			if($myrow['safe_qty']>$myrow['quantity']){
               $Mvalue=" 库存量现太低";
			}elseif($myrow['safe_qty']<$myrow['quantity']){
              $Mvalue = "库存量高了";
            }else{
              $Mvalue = "";
            }

           // echo "这是echo：wxImg['.$i.']<br>";


           

            echo ' <td>' . $myrow['item_category1'] . '</td>
                    <td>' . $myrow['stockid'] . '</td>
                    <td>' . $myrow['item_name'] . '</td>
				    <td>' . $myrow['item_desc'] . '</td>
				    <td>' . $myrow['units'] . '</td>
				    <td>' . $myrow['onhand'] . '</td>
				    <td>' . $myrow['safe_qty'] . '</td>  '; 


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

