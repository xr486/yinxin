<?php

ob_start();
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include('includes/session.inc');
$Title = _('供给明细报表');
$ViewTopic = '供给明细报表';
$BookMark = '供给明细报表';

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


if (isset($_POST['Search']) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])) {
   
   

    $sql = " select  c.item_no,c.item_desc,c.item_name,c.units,type,source,quantity
						from wip_inv_onhand_detail_temp a,sf_item_no c  
                        where  a.stockid=c.item_no   ";

    
     if (isset($_POST['type']) and $_POST['type'] != '') { 
		$sql = $sql . " and a.type " . LIKE . " '%" . $_POST['type'] . "%' ";
    }
	  if (isset($_POST['source']) and $_POST['source'] != '') { 
		$sql = $sql . " and a.source " . LIKE . " '%" . $_POST['source'] . "%' ";
    }
 
    if (isset($_POST['item_no']) and $_POST['item_no'] != '') {
        $sql = $sql . " and c.item_no " . LIKE . " '%" . $_POST['item_no'] . "%' ";
    }
	 if (isset($_POST['item_name']) and $_POST['item_name'] != '') {
        $sql = $sql . " and c.item_name " . LIKE . " '%" . $_POST['item_name'] . "%' ";
    }
	if (isset($_POST['item_desc']) and $_POST['item_desc'] != '') {
        $sql = $sql . " and c.item_desc " . LIKE . " '%" . $_POST['item_desc'] . "%' ";
    }
 

	 $sql .= " order by c.item_no,c.item_desc,c.item_name "; 
      
    $result = DB_query($sql, $db);
    if (DB_num_rows($result) == 0) {
        unset($result);
        prnMsg(_('找不到该工单，请重新输入条件查询！'), 'error');
    }
}


echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('供给明细报表') . '</p>';
echo '<table cellpadding="3" class="selection">';
echo '<div class="text-nav">';
echo '<div class="text-nav-1"><div>' . _('来源') . ':</div>';
echo '<input type="text" name="source"   value="' . $_POST['source'] . '" size="10" maxlength="25" /></div>';
echo '<div class="text-nav-1"><div>' . _('类型') . ':</div>';
echo '<input type="text" name="type"   value="' . $_POST['type'] . '" size="10" maxlength="25" /></div>';
 echo '<div class="text-nav-1"><div>' . _('料号') . ':</div>';
echo '<input type="text" name="item_no" value="' . $_POST['item_no'] . '" size="25" maxlength="250" /></div>';
echo '<div class="text-nav-1"><div>' . _('料号名称') . ':</div>';
echo '<input type="text" name="item_name" value="' . $_POST['item_name'] . '" size="25" maxlength="250" /></div>';


echo '<div class="text-nav-1"><div>'. _('规格型号') . ':</div>';
echo '<input type="text" name="item_desc" value="' . $_POST['item_desc'] . '" size="25" maxlength="250" /></div>';
 
echo '</table><div class="centre"> 
<input type="submit" name="Search" value="查询"> </div>  </br>';
 
if (isset($_POST['Search']) and isset($result) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])) {
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
        echo '<br /><div class="centre">&nbsp;&nbsp;' . _('第') . '' . $_POST['PageOffset'] . ' ' . _('页，共') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ': ';
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
                    <input type="submit" name="Go1" value="' . _('Go') . '" />
                    <input type="submit" name="Previous" value="' . _('Previous') . '" />
                    <input type="submit" name="Next" value="' . _('Next') . '" />';
        echo '</div>';
    }
    echo '<div class="text-nav-table">
                    <table cellpadding="2" class="selection" >';
 
    echo '<tr>       <th bgcolor="#87CEFA"    >' . _('类型') . '</th> 
	<th bgcolor="#87CEFA"    >' . _('来源') . '</th> 
	  
                    <th bgcolor="#87CEFA"  width = 100>' . _('料号') . '</th>
					 <th bgcolor="#87CEFA"   >' . _('料号名称') . '</th>
					 <th bgcolor="#87CEFA"  >' . _('规格型号') . '</th> 
				 <th bgcolor="#87CEFA"  >' . _('单位') . '</th> 
					<th bgcolor="#87CEFA"  width = 70>' . _('供给数量') . '</th> 
            </tr>';
    $k = 0; //row counter to determine background colour 
    $RowIndex = 0;
  
    if (DB_num_rows($result) <> 0) {
        DB_data_seek($result, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);
        $i = 0; //counter for input controls
        while (($myrow = DB_fetch_array($result)) AND ( $RowIndex <> $_SESSION['DisplayRecordsMax'] )) {
            if ($k == 1) {
                echo '<tr class="EvenTableRows">';
                $k = 0;
            } else {
                echo '<tr class="OddTableRows">';
                $k = 1;
            }
			 
           echo '
		   
		   <td>'. $myrow['type'] . '</td> 
		    <td>' . $myrow['source'] . '</td> 
				 
                    <td>' . $myrow['item_no'] . '</td> 
					<td>' . $myrow['item_name'] . '</td> 
					<td>' . $myrow['item_desc'] . '</td> 
					<td>' . $myrow['units'] . '</td>  
					<td>' . $myrow['quantity'] . '</td> 
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
        echo '<br /><div class="centre">&nbsp;&nbsp;' . _('第') . '' . $_POST['PageOffset'] . ' ' . _('页，共') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ': ';
        echo '<select name="PageOffset2">';
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
                        <input type="submit" name="Go2" value="' . _('Go') . '" />
                        <input type="submit" name="Previous" value="' . _('Previous') . '" />
                        <input type="submit" name="Next" value="' . _('Next') . '" />';
        echo '</div>';
    }

	    echo '<br>
          <div class="centre">
                <a href="' . $RootPath . '/MRPSupplyReportExcel.php?wip_entity_name=' .$_POST['wip_entity_name'] . '&item_no=' .$_POST['item_no'].'&item_name=' .$_POST['item_name'] .'&item_desc=' .$_POST['item_desc'] . '">
                ' .'资料导出Excel表' . '
                </a>
          </div>';

}
echo '</div></form>';

include('includes/footer.inc');
