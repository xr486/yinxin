<?php
 ob_start();
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include('includes/session.inc');
$Title = _('员工资料维护');
$ViewTopic= '员工资料维护';
$BookMark = '员工资料维护';

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
    $sql = "SELECT
employee_num,
in_date,
lev_date,
created_by,
telephone,
employee_name,shenfenzheng,depart_code,(select depart_name from hr_departs b where a.depart_code= b.depart_code) depart_name
FROM hr_employees a
 where 1=1";
    if(isset($_POST['Employee_Name']) and $_POST['Employee_Name'] != ''){
        $sql = $sql." and Employee_Name ".LIKE." '%".$_POST['Employee_Name']."%' ";
    }
    if(isset($_POST['Employee_Num']) and $_POST['Employee_Num'] != ''){
        $sql = $sql." and Employee_Num ".LIKE." '%".$_POST['Employee_Num']."%' ";
    }
	if(isset($_POST['depart_code']) and $_POST['depart_code'] != ''){
        $sql = $sql." and depart_code ".LIKE." '%".$_POST['depart_code']."%' ";
    }
   
 
    
  
    $result = DB_query($sql,$db);
    if (DB_num_rows($result)==0) {
        unset($result);
        prnMsg(_('找不到该员工，请重新输入条件查询！') ,'error');
    }
}


echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('查找员工') . '</p>';
echo '<table cellpadding="3" class="selection">';

echo '<tr><td colspan="2">' . _('员工名称') . ':</td><td>';
echo '<input type="text" name="Employee_Name" value="' . $_POST['Employee_Name'] . '" size="20" maxlength="25" /></td>';
echo '<td>' . _('员工工号') . ':</td>
	<td>';
echo '<input type="text" name="Employee_Num" value="' . $_POST['Employee_Num'] . '" size="20" maxlength="25" /></td>';
echo '</tr>';

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
                    <th bgcolor="#87CEFA" class="ascending" width = 100>' . _('员工工号') . '</th>
                    <th bgcolor="#87CEFA" class="ascending"width = 150>' . _('员工名称') . '</th>
                    <th bgcolor="#87CEFA" class="ascending"width = 250>' . _('身份证') . '</th>
                    <th bgcolor="#87CEFA" class="ascending"width = 150>' . _('部门代号') . '</th>
                    <th bgcolor="#87CEFA" class="ascending"width = 250>' . _('部门名称') . '</th>
         
                    <th bgcolor="#87CEFA" class="ascending"width = 100>' . _('入职日期') . '</th>
                
                    <th bgcolor="#87CEFA" class="ascending"width = 100>' . _('离职日期') . '</th>
                    <th bgcolor="#87CEFA" class="ascending"width = 200>' . _('电话') . '</th>
                    <th bgcolor="#87CEFA" class="ascending"width =100>' . _('创建者') . '</th>
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
			echo '  <td><a href="' . $RootPath . '/AddEmployee2.php?UpdateEmployee_Num=' . $myrow['employee_num'] . '">' . $myrow['employee_num'] . '</td>
				<td>' . $myrow['employee_name'] . '</td>
				<td>' . $myrow['shenfenzheng'] . '</td>
				<td>' . $myrow['depart_code'] . '</td>
				<td>' . $myrow['depart_name'] . '</td>
				<td>' . date('Y-m-d',$myrow['in_date'] ). '</td>';
		  if ($myrow['lev_date']==''){
		        echo '<td>' .''. '</td>';
		  }else{
				echo '<td>' .date('Y-m-d', $myrow['lev_date'] ). '</td>';
		  }
				echo '<td>' . $myrow['telephone'] . '</td>
				<td>' . $myrow['created_by'] . '</td>
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

}
echo '</div></form>';
if (isset($_POST['add_new'])) {
    header('Location: AddEmployee.php');
}
include('includes/footer.inc');