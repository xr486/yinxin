<?php
 ob_start();
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include('includes/session.inc');
	$Title = _('查询成品 ');

	$ViewTopic= '查询成品 ';
	$BookMark = '查询成品 ';

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
	    $sql = "select item_id,item_no,item_name,units,min_order,safe_qty,item_desc,lead_time,manufacture_time,yanse,item_category,(select zhidao_price from bom_headers_all b where a.item_no=b.assembly_item_no) zhidao_price from sf_item_no a where item_type='F' ";
	   
	   //SQL添加条件
		if (isset($_POST['ItemNo']) and $_POST['ItemNo']!= '') {
			$sql = $sql." and item_no like '%".$_POST['ItemNo']."%' ";
		}
                    if (isset($_POST['catogery']) and $_POST['catogery'] != '') {
        $sql = $sql . " and item_category = '" . $_POST['catogery'] . "' ";
    }
		if (isset($_POST['item_name']) and $_POST['item_name']!= '') {
			$sql = $sql." and item_name like '%".$_POST['item_name']."%' ";
		}
		if (isset($_POST['item_desc']) and $_POST['item_desc']!= '') {
			$sql = $sql." and item_desc like '%".$_POST['item_desc']."%' ";
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
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('查询成品 ') . '</p>';
echo '<table cellpadding="3" class="selection">';

echo '<tr><td colspan="2">' . _('成品料号:') . '</td><td>';
echo '<input type="text" name="ItemNo" value="' . $_POST['ItemNo'] . '" size="20" maxlength="25" /></td>';
echo '<td>' . _('成品名称:') . '</td>
	<td>';
echo '<input type="text" name="item_name" value="' . $_POST['item_name'] . '" size="20" maxlength="25" /></td>';
echo '<td>' . _('规格型号:') . '</td>
	<td>';
echo '<input type="text" name="item_desc" value="' . $_POST['item_desc'] . '" size="20" maxlength="25" /></td>';
 

echo '</tr>';
echo '</table><div class="centre"><input type="submit" name="Search" value="查找"> &nbsp;&nbsp; </div>';

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
        <th class="ascending" width = "100" >成品料号 </th>
		<th class="ascending" width = "200" >成品名称</th>
        <th class="ascending" width = "120" >规格型号</th>
                    <th class="ascending" width = "80" >指导价</th>
		<th class="ascending" width = "40" >单位</th>
		<th class="ascending" width = "90" >最小订单量</th>
		<th class="ascending" width = "80" >安全库存</th>
		<th class="ascending" width = "80" >生产周期</th>
                   
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
				<td>' . $myrow['item_name'] . '</td>
				<td>' . $myrow['item_desc'] . '</td>
				<td>' . $myrow['zhidao_price'] . '</td> 
				<td>' . $myrow['units'] . '</td>
				<td>' . $myrow['item_category'] . '</td>
				<td>' . $myrow['min_order'] . '</td>
				<td>' . $myrow['safe_qty'] . '</td>
				<td>' . $myrow['lead_time'] . '</td>
				<td>' . $myrow['manufacture_time'] . '</td>
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