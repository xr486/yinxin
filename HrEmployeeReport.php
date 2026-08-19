<?php
 ob_start();
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include('includes/session.inc');
$Title = _('人事资料查询');
$ViewTopic= '人事资料查询';
$BookMark = '人事资料查询';

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
    $sql = 'select employee_num,employee_name,shenfenzheng,huji,created_by,creation_date           
            from hr_employees where 1=1';
    if(isset($_POST['employee_num']) and $_POST['employee_num'] != ''){
        $sql = $sql." and employee_num ".LIKE." '%".$_POST['employee_num']."%' ";
    }
    if(isset($_POST['employee_name']) and $_POST['employee_name'] != ''){
        $sql = $sql." and employee_name ".LIKE." '%".$_POST['employee_name']."%' ";
    }
    if(isset($_POST['shenfenzheng']) and $_POST['shenfenzheng'] != ''){
        $sql = $sql." and shenfenzheng ".LIKE." '%".$_POST['shenfenzheng']."%' ";
    }
  
    $result = DB_query($sql,$db);
    if (DB_num_rows($result)==0) {
        unset($result);
        prnMsg(_('找不到该人事资料，请重新输入条件查询！') ,'error');
    }
}

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('查找员工资料') . '</p>';
echo '<table cellpadding="3" class="selection">';

echo '<tr><td>' . _('工号') . ':</td><td>';
echo '<input type="text" name="employee_num" value="' . $_POST['employee_num'] . '" size="20" maxlength="25" /></td>';
echo '<td>' . _('姓名') . ':</td>
	<td>';
echo '<input type="text" name="employee_name" value="' . $_POST['employee_name'] . '" size="20" maxlength="25" /></td>';
echo '<td>' . _('身份证') . ':</td><td>';
echo '<input type="text" name="shenfenzheng" value="' . $_POST['shenfenzheng'] . '" size="20" maxlength="25" /></td>';
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
        <th class="ascending"width = 150>' . _('工号') . '</th>
        <th class="ascending"width = 150>' . _('姓名') . '</th>
        <th class="ascending"width = 150>' . _('身份证') . '</th>
        <th class="ascending"width = 150>' . _('户籍') . '</th>
        <th class="ascending"width = 150>' . _('建单人员') . '</th>
        <th class="ascending"width = 150>' . _('建单日期') . '</th>      
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
                echo '  <td>' . $myrow['employee_num'] . '</td>
                        <td>' . $myrow['employee_name'] . '</td>
                        <td>' . $myrow['shenfenzheng'] . '</td>
                        <td>' . $myrow['huji'] . '</td>
                        <td>' . $myrow['created_by'] . '</td>
                        <td>' . date('Y-m-d',$myrow['creation_date']) . '</td>
                        ';

                echo '
                </tr>';
                $i++;
                $RowIndex++;
                //end of page full new headings if
        } //end loop through vendors
        echo '</table>';
        echo '<input type="hidden" name="JustSelectedAvendor" value="Yes" />';
        echo '<div>
          <a href="' . $RootPath . '/HrEmployeeReportExcel.php?employee_num=' .$_POST['employee_num'] .
            '&employee_name='.$_POST['employee_name'] .'&shenfenzheng=' .$_POST['shenfenzheng'] .' ">' .'资料导出Excel表' . '</a>
         </div>';
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
    header('Location: HrEmployee.php');
}
include('includes/footer.inc');