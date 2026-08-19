<?php
 ob_start();
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include('includes/session.inc');
$Title = _('部门维护');
$ViewTopic= '部门维护';
$BookMark = '部门维护';

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

unset($result);


if (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button
// PREVENT DELETES IF DEPENDENT RECORDS IN 'sf_item_no'
	// Get the original name of the unit of measure the ID is just a secure way to find the unit of measure
	$sql = "SELECT * FROM hr_employees
		WHERE depart_code = '" .$_GET['Selectedepartcode'] . "'";
	$result = DB_query($sql,$db);
	if ( DB_num_rows($result) > 0 ) {
		// This is probably the safest way there is
		prnMsg( _('不能删除该部门，该部门已分配人员'),'warn');
	} else {
		 
		 
			$sql="DELETE FROM hr_departs WHERE depart_code ='" . $_GET['Selectedepartcode'] . "'";
	 
			$result = DB_query($sql,$db);
			prnMsg( $OldMeasureName . ' ' . _(' 删除成功') . '!','success');
		 
	} //end if account group used in GL accounts
	unset ($SelectedMeasureID);
	unset ($_GET['SelectedMeasureID']);
	unset($_GET['delete']);
	unset ($_POST['SelectedMeasureID']);
	unset ($_POST['MeasureID']);
	unset ($_POST['MeasureName']);
}


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
    $sql = "select * from hr_departs
 where 1=1";
    if (isset($_POST['depart_code']) and $_POST['depart_code'] != '') {
        $sql = $sql . " and depart_code like '%" . $_POST['depart_code'] . "%' ";
    }
    if (isset($_POST['depart_name']) and $_POST['depart_name'] != '') {
        $sql = $sql . " and depart_name like '%" . $_POST['depart_name'] . "%' ";
    }
    if (isset($_POST['FromDate']) and $_POST['FromDate'] != '') {
        $SQL_FromDate = strtotime($_POST['FromDate']);
        $sql = $sql . " and creation_date >= '" . $SQL_FromDate . "' ";
    }
    if (isset($_POST['ToDate']) and $_POST['ToDate'] != '') {
        $SQL_FromDate = strtotime($_POST['ToDate']);
        $sql = $sql . " and creation_date <= '" . $SQL_FromDate . "' ";
    }
   
 
    
  
    $result = DB_query($sql,$db);
    if (DB_num_rows($result)==0) {
        unset($result);
        prnMsg(_('找不到该部门，请重新输入条件查询！') ,'error');
    }
}


echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('查找部门') . '</p>';
echo '<table cellpadding="3" class="selection">';
echo '<div class="text-nav">';
echo '<div class="text-nav-1 "><div>' . _('部门编号') . ':</div>';
echo '<input type="text" pattern="^[^?.\+<>!&’:,;?$\^]+$" name="depart_code" value="' . $_POST['depart_code'] . '" size="20" maxlength="25" /></div>';
echo '<div class="text-nav-1 "><div>' . _('部门名称') . ':</div>
';
echo '<input type="text" pattern="^[^?.\+<>!&’:,;?$\^]+$" name="depart_name" value="' . $_POST['depart_name'] . '" size="20" maxlength="25" /></div>';
echo '</div>';

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
					<th class="ascending" width = "100" >部门编号</th>
                    <th class="ascending" width = "200" >部门名称</th>
                    <th class="ascending" width = "40" >创建日期</th>
                    <th class="ascending" width = "90" >创建人</th>
                    <th class="ascending" width = "100" >是否生效</th> 
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
			if ($myrow['disable_date'] >1 ) {
			$disable_date=date('Y-m-d',$myrow['disable_date'] );
				}
			echo '  <td><a href="' . $RootPath . '/DeptUpdate.php?depart_code=' . $myrow['depart_code'] . '">' . $myrow['depart_code'] . '</td>
				<td>' . $myrow['depart_name'] . '</td>
				<td>' . date('Y-m-d',$myrow['creation_date'] ) . '</td>
				<td>' . $myrow['created_by'] . '</td> 
				<td>' . $myrow['enable_flag'] . '</td>
				<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?Selectedepartcode=' . $myrow['depart_code'] . '&amp;delete=1" onclick="return confirm(\'' . _('是否要删除这个部门?') . '\');">' . _('删除')  . '</a></td>';
		   
       
			echo '
			</tr>';
			$i++;
			$RowIndex++;
			//end of page full new headings if
		} //end loop through customers
		echo '</table></div>';
		echo '<input type="hidden" name="JustSelectedAvendor" value="Yes" />';
	}

        if (isset($ListPageMax) AND $ListPageMax > 1) {
                echo '<br /><div class="centre">&nbsp;&nbsp;' . _('第') . '' . $_POST['PageOffset'] . ' ' . _('页，共') . ' ' . $ListPageMax . ' ' . _('页数') . '. ' . _('转到页') . ': ';
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
    header('Location: AddDepart.php');
}
include('includes/footer.inc');