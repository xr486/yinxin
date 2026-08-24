<?php
 ob_start();
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include('includes/session.inc');
$Title = _('成品庫存明細表');
$ViewTopic= '成品庫存明細表';
$BookMark = '成品庫存明細表';

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
    $sql = "SELECT a.sub_code, 
                   c.item_no, c.item_name,c.item_desc,c.units,a.start_quantity,a.in_quantity,a.out_quantity,a.ym,a.end_quantity,a.cost_price
                    
            FROM cst_inv_yuejie_all a,sf_item_no c  
            WHERE   a.item_no = c.item_no 
			and c.item_type='F' and a.end_quantity>0 ";
  
    
    if (isset($_POST['item_no']) and $_POST['item_no'] != '') { 		
        $sql = $sql." and c.item_no ".LIKE." '%".$_POST['item_no']."%' ";
    } 
    if (isset($_POST['item_name']) and $_POST['item_name'] != '') { 		
        $sql = $sql." and c.item_name ".LIKE." '%".$_POST['item_name']."%' ";
    } 
	if (isset($_POST['item_desc']) and $_POST['item_desc'] != '') { 		
        $sql = $sql." and c.item_desc ".LIKE." '%".$_POST['item_desc']."%' ";
    } 
    if (isset($_POST['ym']) and $_POST['ym'] != '') { 		
        $sql = $sql." and a.ym ='".$_POST['ym']."' ";
    } 
    if (isset($_POST['sub_code']) and $_POST['sub_code'] != '') {	
        $sql = $sql." and a.sub_code ".LIKE." '%".$_POST['sub_code']."%' "; 
    } 
	 
	$sql = $sql . " order by a.sub_code,c.item_no, c.item_name";
	 
  
    $result = DB_query($sql,$db);
    if (DB_num_rows($result)==0)
    {
        unset($result);
        prnMsg(_('找不到该来料报检单，请重新输入条件查询！') ,'error');
    }
}


echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('成品庫存明細表') . '</p>';
echo '<table cellpadding="3" class="selection">';
echo '<div class="text-nav">';
 
if (!isset($_POST['FromDate'])) {
    $_POST['FromDate'] = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 30, date("Y")));
}
if (!isset($_POST['ToDate'])) {
    $_POST['ToDate'] = Date('Y-m-d', mktime(0, 0, 0, date("m"), date("d") + 1, date("Y")));
}
 
echo '<div class="text-nav-1"><div>' . _('仓库') . ':</div>';
echo '<input type="text" name="sub_code" value="' . $_POST['sub_code'] . '" size="20" maxlength="25" /></div>';
echo '<div class="text-nav-1"><div>' . _('料号') . ':</div>';
echo '<input type="text" name="item_no" value="' . $_POST['item_no'] . '" size="20" maxlength="25" /></div>';
echo '<div class="text-nav-1"><div>' . _('料号名称') . ':</div>';
echo '<input type="text" name="item_name" value="' . $_POST['item_name'] . '" size="20" maxlength="25" /></div>'; 
echo '<div class="text-nav-1"><div>' . _('规格型号') . ':</div>';
echo '<input type="text" name="item_desc" value="' . $_POST['item_desc'] . '" size="20" maxlength="25" /></div>';
 
echo '<div class="text-nav-1"><div>' . _('年月(YYYYMM)') . ':</div>';
echo '<input type="text" name="ym"  required="required" value="' . $_POST['ym'] . '" size="20" maxlength="25" /></div>';
  
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
    echo '			  <div class="text-nav-table">
                    <table cellpadding="2" class="selection" >';

    echo '<tr>                       
						<th width =100>' . '年月' . '</th>
						<th  width = 100>' . _('仓库') . '</th> 
						<th width =100>' . '料号' . '</th>
                        <th width =150>' . '料号名称' . '</th>
                        <th width =150>' . '规格型号' . '</th> 
						<th width =60 >' . '期未数量' . '</th>                                                                
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
		         
			echo '<td>' . $myrow['ym'] . ' </td>';
			echo '<td>' . $myrow['sub_code'] . '</td>';  
		    echo '<td>' . $myrow['item_no'] . ' </td>';
            echo '<td>' . $myrow['item_name'] . ' </td>'; 
            echo '<td>' . $myrow['item_desc'] . ' </td>'; 
           
            echo '<td>' . $myrow['end_quantity'] . ' </td>';  
           
             
                    
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
    echo '<br>
          <div class="centre">
                <a href="' . $RootPath . '/CstINVOnhandReportExcel.php?sub_code=' .$_POST['sub_code'] . '&item_no=' .$_POST['item_no'].'&sn=' .$_POST['sn'] . '&item_name=' .$_POST['item_name'] .'&item_desc=' .$_POST['item_desc'] .'&ym=' .$_POST['ym'] .'&transaction_type=' .$_POST['transaction_type'] . '">
                ' .'资料导出Excel表' . '
                </a>
          </div>';
    
}
 
echo '</div></form>';
if (isset($_POST['add_new'])) {
    header('Location: AddSupplier.php');
}
include('includes/footer.inc');