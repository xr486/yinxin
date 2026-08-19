<?php

ob_start();
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include('includes/session.inc');
$Title = _('BOM成本查询');
$ViewTopic = 'BOM成本查询';
$BookMark = 'BOM成本查询';

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
    $sql = "SELECT a.bom_header_id,a.version,a.assembly_item_no,b.item_name,b.item_desc,a.creation_date,a.created_by,item_category1 
	FROM bom_headers_all a,
	 sf_item_no b
WHERE	1 = 1
AND b.item_no = a.assembly_item_no
";

    if (isset($_POST['item_no']) and $_POST['item_no'] != '') {
		$sql = $sql." and b.item_no like '%".$_POST['item_no']."%' ";
    }
    if (isset($_POST['item_name']) and $_POST['item_name'] != '') {
       $sql = $sql." and b.item_name like '%".$_POST['item_name']."%' ";
    }
	  if (isset($_POST['item_category1']) and $_POST['item_category1'] != '') {
       $sql = $sql." and b.item_category1 like '%".$_POST['item_category1']."%' ";
    }
	 if (isset($_POST['item_desc']) and $_POST['item_desc'] != '') {
       $sql = $sql." and b.item_desc like '%".$_POST['item_desc']."%' ";
    }
    
  if (isset($_POST['gongyi']) and $_POST['gongyi'] != '') {
       $sql = $sql." and b.gongyi like '%".$_POST['gongyi']."%' ";
    }
    if (empty($_POST['FromDate']) == 0) {
        $SQL_FromDate = strtotime($_POST['FromDate']);
        $sql .= " and a.creation_date >= '" . $SQL_FromDate . "' ";
    }
    if (empty($_POST['ToDate']) == 0) {
        $SQL_ToDate = strtotime($_POST['ToDate']) + 86400;
        //echo $SQL_ToDate;
        $sql .= " and a.creation_date <='" . $SQL_ToDate . "' ";
    }
       
      $sql .=" order by a.creation_date desc";
    $result = DB_query($sql, $db);
    if (DB_num_rows($result) == 0) {
        unset($result);
        prnMsg(_('找不到该BOM，请重新输入条件查询！'), 'error');
    }
}


echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('BOM成本查询') . '</p>';
echo '<table cellpadding="3" class="selection">';
echo '<div class="text-nav">';

echo '<div class="text-nav-1"><div>' . _('料号') . ':</div>';
echo '<input type="text" name="item_no" value="' . $_POST['item_no'] . '" size="20" maxlength="55" /></div>';
echo '<div class="text-nav-2"><div>' . _('料号名称') . ':</div>
';
echo '<input type="text" name="item_name" value="' . $_POST['item_name'] . '" size="40" maxlength="205" /></div>';

echo '<div class="text-nav-2"><div>' . _('规格型号') . ':</div>';
echo '<input type="text" name="item_desc" value="' . $_POST['item_desc'] . '" size="40" maxlength="55" /></div>';
 
echo '<div class="text-nav-1"><div>' . _('分类') . ':</div>';
echo '<input type="text" name="item_category1" value="' . $_POST['item_category1'] . '" size="20" maxlength="55" /></div>';
if (!isset($_POST['FromDate'])) {
    $_POST['FromDate'] = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 30, date("Y")));
}
if (!isset($_POST['ToDate'])) {
    $_POST['ToDate'] = Date('Y-m-d');
}
echo '<div class="text-nav-1"><div>' . '建立日' . _('起') . ':</div>
		<input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="FromDate" maxlength="10" size="11" value="" /></div>
		<div class="text-nav-1"><div>' . _('建立日止') . ':</div>
		<input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="ToDate" maxlength="10" size="11" value="" /></div>
	</div>';

echo '</table><div class="centre"><input type="submit" name="Search" value="查找"> </div>'
 . '</br>';

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
                    <th class="ascending" width = 150>' . _('料号') . '</th>
					<th class="ascending" >' . _('料号名称') . '</th>
					<th class="ascending" >' . _('规格型号') . '</th> 
					<th class="ascending" >' . _('版本') . '</th> 
                    <th class="ascending"width = 90>' . _('分类') . '</th>                   
					<th class="ascending"width = 160>' . _('建立日期') . '</th>
                    <th class="ascending"width = 100>' . _('建立人员') . '</th>
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
        
            echo '  <td><a href="' . $RootPath . '/BOMCostDetail.php?searchitem_no=' . $myrow['bom_header_id'] . '" target="_blank">' . $myrow['assembly_item_no'] . '</td>			                
                                <td>' . $myrow['item_name'] . '</td> 
                                <td>' . $myrow['item_desc'] . '</td>  
                                <td>' . $myrow['version'] . '</td>  
                                <td>' . $myrow['item_category1'] . '</td>                            
				<td>' . date('Y-m-d H:i:s', $myrow['creation_date']) . '</td>
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
