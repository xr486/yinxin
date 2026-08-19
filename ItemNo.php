<?php
 ob_start();
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include('includes/session.inc');
	$Title = _('查询料号');

	$ViewTopic= '查询料号';
	$BookMark = '查询料号';

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
		//日期格式化为SQL格式
	    $SQL_FromDate = FormatDateForSQL($_POST['FromDate']);
	    $SQL_ToDate = FormatDateForSQL($_POST['ToDate']);
	    //查找数据的SQL
	    $sql = 'select item_id,item_no,item_desc,units,min_order,safe_qty,pin,ming,shu,heng,yanse,item_category from sf_item_no where 1=1 ';
	   
	   //SQL添加条件
		if (isset($_POST['ItemNo']) and $_POST['ItemNo']!= '') {
			$sql = $sql." and item_no like '%".$_POST['ItemNo']."%' ";
		}
                    if (isset($_POST['catogery']) and $_POST['catogery'] != '') {
        $sql = $sql . " and item_category = '" . $_POST['catogery'] . "' ";
    }
		if (isset($_POST['ItemDesc']) and $_POST['ItemDesc']!= '') {
			$sql = $sql." and item_desc like '%".$_POST['ItemDesc']."%' ";
		}
		if (isset($_POST['Units']) and $_POST['Units']!= '') {
			$sql = $sql." and units like '%".$_POST['Units']."%' ";
		}
		
	    $sql .= " ORDER BY item_id  desc ";//SQL排序
	    $result = DB_query($sql, $db);
	    if (DB_num_rows($result) == 0) {
	        unset($result);
	        prnMsg(_('找不到数据，请重新输入条件查询！'), 'error');
	    }
	}

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('查询料号') . '</p>';
echo '<table cellpadding="3" class="selection">';

echo '<tr><td colspan="2">' . _('料号') . ':</td><td>';
echo '<input type="text" name="ItemNo" value="' . $_POST['ItemNo'] . '" size="20" maxlength="25" /></td>';
echo '<td><b>' . _(' ') . '</b></td><td>' . _('料号描述') . ':</td>
	<td>';
echo '<input type="text" name="ItemDesc" value="' . $_POST['ItemDesc'] . '" size="20" maxlength="25" /></td>';
echo '</tr>';
echo '<tr>
          <td><b>' . _(' ') . '</b></td>
          <td>' . _('单位：') . ':</td>
	<td>';
echo '<input type="text" name="Units" value="' . $_POST['Units'] . '" size="20" maxlength="25" /></td>';
echo '</tr>';

echo <td >'类型':</td>;
       $sql = "SELECT unitname FROM sf_item_category ";
       $result1 = DB_query($sql, $db);
       echo '<td><select name="catogery">';
       echo '<option  selected="selected" value=""></option>';
                while ($Salesmanrow = DB_fetch_array($result1)) {
                    echo '<option value="' . $Salesmanrow['unitname'] . '">' . $Salesmanrow['unitname'] . '</option>';
                }
     echo '</select></td>';


echo '</table><div class="centre"><input type="submit" name="Search" value="查找"> &nbsp;&nbsp;<input type="submit" name="add_new" value="新增"></div>';

if (isset($_POST['Search']) and isset($result) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])){
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
    echo '
                    <table cellpadding="2" class="selection" >';

    echo '<tr>
                   <th class="ascending" width = "100" >料号</th>
		<th class="ascending" width = "200" >料号描述</th>
		<th class="ascending" width = "40" >单位</th>
		<th class="ascending" width = "40" >类型</th>
		<th class="ascending" width = "90" >最小订单量</th>
		<th class="ascending" width = "80" >安全库存</th>
                <th class="ascending" width = "80" >品</th>
                <th class="ascending" width = "80" >名</th>
                <th class="ascending" width = "80" >颜色</th>
                <th class="ascending" width = "90" >竖花间距</th>
                <th class="ascending" width = "90" >横花间距</th>
                   
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
			echo '  <td>'.  $myrow['item_no'] . '</td>
				<td>' . $myrow['item_desc'] . '</td>
				<td>' . $myrow['units'] . '</td>
				<td>' . $myrow['item_category'] . '</td>
				<td>' . $myrow['min_order'] . '</td>
				<td>' . $myrow['safe_qty'] . '</td>
				<td>' . $myrow['pin'] . '</td>
				<td>' . $myrow['ming'] . '</td>
				<td>' . $myrow['yanse'] . '</td>
				<td>' . $myrow['shu'] . '</td>
				<td>' . $myrow['heng'] . '</td>
				';

       
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
                echo '<br /><div class="centre">&nbsp;&nbsp;' . _('第') . '' . $_POST['PageOffset'] . ' ' . _('页，共') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ': ';
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
                        <input type="submit" name="Go2" value="' . _('Go') . '" />
                        <input type="submit" name="Previous" value="' . _('Previous') . '" />
                        <input type="submit" name="Next" value="' . _('Next') . '" />';
                echo '</div>';
    }

}
echo '</div></form>';
if (isset($_POST['add_new'])) {
    header('Location: AddSupplier.php');
}
include('includes/footer.inc');