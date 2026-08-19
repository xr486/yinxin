<?php
 ob_start();
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include('includes/session.inc');
$Title = _('查询单据');
$ViewTopic= '查询单据';
$BookMark = '查询单据';

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
    $sql = "select *   from sales_all where 1=1 ";
    if(isset($_POST['jobno']) and $_POST['jobno'] != ''){
        $sql = $sql." and jobno ".LIKE." '%".$_POST['jobno']."%' ";
    }
    if(isset($_POST['weituofang']) and $_POST['weituofang'] != ''){
        $sql = $sql." and weituofang ".LIKE." '%".$_POST['weituofang']."%' ";
    }
    if(isset($_POST['tidanhao']) and $_POST['tidanhao'] != ''){
        $sql = $sql." and tidanhao ".LIKE." '%".$_POST['tidanhao']."%' ";
    }
   
	if(isset($_POST['chuanminghangci']) and $_POST['chuanminghangci'] != ''){
        $sql = $sql." and chuanminghangci ".LIKE." '%".$_POST['chuanminghangci']."%' ";
    }
	if(isset($_POST['chuangongsi']) and $_POST['chuangongsi'] != ''){
        $sql = $sql." and chuangongsi ".LIKE." '%".$_POST['chuangongsi']."%' ";
    }
 
    
  
    $result = DB_query($sql,$db);
    if (DB_num_rows($result)==0) {
        unset($result);
        prnMsg(_('找不到物流订单，请重新输入条件查询！') ,'error');
    }
}


echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('查找物流订单') . '</p>';
echo '<table cellpadding="3" class="selection">';


echo ' <td  >' . _('jobno') . ':</td><td>';
echo '<input type="text" name="jobno" value="' . $_POST['jobno'] . '" size="20" maxlength="25" /></td>';
echo '<td>' . _('委托方') . ':</td>
	<td>';
echo '<input type="text" name="weituofang" value="' . $_POST['weituofang'] . '" size="20" maxlength="25" /></td>';
echo '  <td>' . _('提单号码') . ':</td>
	<td>';
echo '<input type="text" name="tidanhao" value="' . $_POST['tidanhao'] . '" size="20" maxlength="25" /></td>';
echo '  <td>' . _('船名航次') . ':</td> <td>';
echo '<input type="text" name="chuanminghangci" value="' . $_POST['chuanminghangci'] . '" size="20" maxlength="25" /></td>';
echo '  <td>' . _('船公司') . ':</td> <td>';
echo '<input type="text" name="chuangongsi" value="' . $_POST['chuangongsi'] . '" size="20" maxlength="25" /></td>'; 
echo '</tr>';

echo '</table><div class="centre"><input type="submit" name="Search" value="查找"> &nbsp;&nbsp; </div>';

if (isset($_POST['Search']) and isset($result) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])){
    $ListCount = DB_num_rows($result);
    $ListPageMax = ceil($ListCount / 20);
    
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
                    <th class="ascending"width = 120>' . _('JOBNO') . '</th> 
					<th class="ascending" width = 80>' . _('ETD') . '</th>
                    <th class="ascending"width =60>' . _('类型') . '</th> 
					<th class="ascending"width = 100>' . _('销售') . '</th> 
                    <th class="ascending"width = 100>' . _('委托方') . '</th>
                
                    <th class="ascending"width = 120>' . _('提单号') . '</th>
                    <th class="ascending"width = 150>' . _('船名航次') . '</th> 
					<th class="ascending"width = 150>' . _('船公司') . '</th>
					<th class="ascending"width = 100>' . _('启运港') . '</th>
					<th class="ascending"width = 100>' . _('目的地') . '</th>

					
            </tr>';
    $k = 0; //row counter to determine background colour
    $RowIndex = 0;
    


    if (DB_num_rows($result) <> 0) {
        DB_data_seek($result, ($_POST['PageOffset'] - 1) * 20);
        $i = 0; //counter for input controls
        while (($myrow = DB_fetch_array($result)) AND ($RowIndex <> 20)) {
			if ($k == 1) {
				echo '<tr class="EvenTableRows">';
				$k = 0;
			} else {
				echo '<tr class="OddTableRows">';
				$k = 1;
			}
			echo '  <td><a href="' . $RootPath . '/SaleOrderQueryDetail.php?Updatejobno=' . $myrow['jobno'] . '" target="_blank">' . $myrow['jobno'] . '</td>
				   <td>' . date('Y-m-d', $myrow['etd_date']). '</td> 
				<td>' . $myrow['order_type'] . '</td>
                <td>' . $myrow['salesman'] . '</td>
				<td>' . $myrow['weituofang'] . '</td>
				<td>' . $myrow['tidanhao'] . '</td> 
				<td>' . $myrow['chuanminghangci'] . '</td> 
				<td>' . $myrow['chuangongsi'] . '</td> 
				<td>' . $myrow['qiyungang'] . '</td> 
				<td>' . $myrow['mudigang'] . '</td> 
				';

       
			echo '
			</tr>';
			$i++;
			$RowIndex++;
			//end of page full new headings if
		} //end loop through customers
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

 echo '<br><div class="centre">
	<a href="' . $RootPath . '/SaleSumExcelok.php?C1=' .$_POST['jobno'] . '&C2=' .$_POST['weituofang'] . '&C3=' .$_POST['tidanhao'] . '&C4=' .$_POST['chuanminghangci'] . '">' .'导出Excel' . '
		</div>';

}    
echo '</div></form>';
 
include('includes/footer.inc');