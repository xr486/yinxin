<?php
 ob_start();
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include('includes/session.inc');
$Title = _('工单站间转移');
$ViewTopic= '工单站间转移';
$BookMark = '工单站间转移';

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

	$sql = "SELECT   WIP_ENTITY_NAME,PRIMARY_ITEM,START_QUANTITY,SCHEDULED_START_DATE,SCHEDULED_COMPLETION_DATE,JOB_TYPE,DATE_RELEASED
    FROM wip_jobs_all  a where status_type='核发' ";
    if(isset($_POST['WIP_ENTITY_NAME']) and $_POST['WIP_ENTITY_NAME'] != ''){
        $sql = $sql." and  WIP_ENTITY_NAME ".LIKE." '%".$_POST['WIP_ENTITY_NAME']."%' ";
    }
   
    if (empty($_POST['FromDate'])==0) {
    $SQL_FromDate = strtotime( $_POST['FromDate']); 
    $sql .= " and a.SCHEDULED_START_DATE >= '" . $SQL_FromDate . "' ";
   } 
   if (empty($_POST['ToDate'])==0) {
    $SQL_ToDate = strtotime( $_POST['ToDate']) + 86400;   
    $sql .= " and  a.SCHEDULED_START_DATE <='" . $SQL_ToDate . "' ";
   }
   
   $_SESSION['locationname' . $identifier]=$_POST['locationname'];
  // echo $sql;
    $result = DB_query($sql,$db);
    if (DB_num_rows($result)==0) {
        unset($result);
        prnMsg(_('找不到该工单，请重新输入条件查询！') ,'error');
    }
}


echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('查找工单') . '</p>';
echo '<table cellpadding="3" class="selection">';

echo '<tr><td >' . _('工单名称') . ':</td><td>';
echo '<input type="text" name="WIP_ENTITY_NAME" value="' . $_POST['WIP_ENTITY_NAME'] . '" size="20" maxlength="50" /></td>';
echo '<td>' . _('料号') . ':</td> <td>';
echo '<input type="text" name="item_no" value="' . $_POST['item_no'] . '" size="20" maxlength="25" /></td>';

if (!isset($_POST['FromDate'])) {
    $_POST['FromDate'] = date("Y-m-d", mktime(0, 0, 0, date("m")-5, date("d") ,
        date("Y")));
}
if (!isset($_POST['ToDate'])) {
    $_POST['ToDate'] = Date('Y-m-d');
}
echo '<td>' . '预计开工日' . _('From') . ':</td>
		<td><input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] .
    '" name="FromDate" maxlength="10" size="11" value="' . $_POST['FromDate'] .
    '" /></td>
		<td>' . _('To') . ':</td>
		<td><input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] .
    '" name="ToDate" maxlength="10" size="11" value="' . $_POST['ToDate'] .
    '" /></td> ';
echo '</tr>';

echo '</table><div class="centre"><input type="submit" name="Search" value="查找"></div>';

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
                    <th class="ascending" width = 60>' . _('工单号码') . '</th>					
					<th class="ascending"width = 150>' . _('料号') . '</th>
					<th class="ascending"width = 80>' . _('开工数量') . '</th>
					 <th class="ascending" >' . _('核发日期') . '</th>
                    <th class="ascending"width = 150>' . _('工单类型') . '</th>
                    <th class="ascending"width = 100>' . _('预计开工日期') . '</th>
					 <th class="ascending"width = 100>' . _('预计完工日期') . '</th> 
					 <th class="ascending" >' . _('完工') . '</th>
					 <th class="ascending" >' . _('退回') . '</th>
                   
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
			 
			echo '  <td>' . $myrow['WIP_ENTITY_NAME'] . '</td>
				<td>' . $myrow['PRIMARY_ITEM'] . '</td>
				<td>' . $myrow['START_QUANTITY'] . '</td>
				<td>' . date('Y-m-d',$myrow['DATE_RELEASED']) . '</td>
				<td>' . $myrow['JOB_TYPE'] . '</td>
				<td>' . date('Y-m-d',$myrow['SCHEDULED_START_DATE']) . '</td>
				<td>' . date('Y-m-d',$myrow['SCHEDULED_COMPLETION_DATE']) . '</td>
				<td><a href="' . $RootPath . '/WIPMovieStation2.php?Updatenum=' . $myrow['WIP_ENTITY_NAME'] . '">' . '搬站'. '</td>
				<td><a href="' . $RootPath . '/WIPMovieStation3.php?Updatenum=' . $myrow['WIP_ENTITY_NAME'] . '">' . '退站'. '</td>
				
				';
				// <td>' . $myrow['locationname'] . '</td>	

       
			echo '
			</tr>';
			$i++;
			$RowIndex++;
			//end of page full new headings if
		} //end loop through moju_details
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