<?php
ob_start();
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include('includes/session.inc');
$Title = _('人员部门维护');
$ViewTopic = '人员部门维护';
$BookMark = '人员部门维护';

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

unset($result);

if (isset($_POST['Go1']) or isset($_POST['Go2'])) {
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

if (isset($_GET['delete'])) {
    

        
       $sql5 = "DELETE FROM hr_emp_departs_info WHERE id ='" .   $_GET['SelectedMeasureID']. "'";
	  // echo  $sql5;
            $result5 = DB_query($sql5,$db);
           
         
}

$sql = "select b.id, b.emp_number,c.realname,a.depart_name, b.created_by,b.creation_date from hr_departs a,hr_emp_departs_info b,www_users c
where b.emp_number=c.userid
and b.depart_code=a.depart_name";
$sql= $sql." order by b.emp_number ";
 $result = DB_query($sql, $db);
if (isset($_POST['Search']) or isset($_POST['Go']) or isset($_POST['Next']) or isset($_POST['Previous'])) {
 
    $sql = "select b.id, b.emp_number,c.realname,a.depart_name, b.created_by,b.creation_date from hr_departs a,hr_emp_departs_info b,www_users c
where b.emp_number=c.userid
and b.depart_code=a.depart_name ";

     if (isset($_POST['emp_number']) and $_POST['emp_number'] != '') {
       $sql = $sql . " and  b.emp_number  like   '%" . $_POST['emp_number'] . "%' ";    
    }
	if (isset($_POST['realname']) and $_POST['realname'] != '') {
          
		  $sql = $sql . " and  c.realname like   '%" . $_POST['realname'] . "%' ";
    }
   
    if (isset($_POST['depart_name']) and $_POST['depart_name'] != ''and $_POST['depart_name'] != '所有') {
        $sql = $sql . " and  a.depart_name like   '%" . $_POST['depart_name'] . "%' ";
    }

$sql= $sql." order by b.emp_number ";

    $result = DB_query($sql, $db);
    if (DB_num_rows($result) == 0) {
        // unset($result);
        prnMsg(_('找不到该部门人员，请重新输入条件查询！'), 'error');
    }
}


echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('查找部门人员') . '</p>';
echo '<table cellpadding="3" class="selection">';
echo '<div class="text-nav3">';
echo ' <div class="text-nav-1 "><div>账号</div> ';
echo '<input type="text" name="emp_number"   value="' . $_POST['emp_number'] . '" size="20" maxlength="25" /></div> ';
 echo ' <div class="text-nav-1 "><div>姓名</div> ';
echo '<input type="text" name="realname"   value="' . $_POST['realname'] . '" size="20" maxlength="25" /></div> ';
 
  echo ' <div class="text-nav-1 "><div>部门</div> ';
 
//echo '<input type="text" name="depart_name" value="' . $_POST['depart_name'] . '" size="40" maxlength="45" /></td></tr>';
 $sql2 = " select * from ( select '所有' depart_name  from dual union select depart_name from hr_departs where 1=1  ) a   "; 
		// echo $sql2;
    $result2 = DB_query($sql2, $db);
echo '<select name="depart_name">';
while ($v = DB_fetch_array($result2)) {
	if (isset($_POST['depart_name']) AND $_POST['depart_name']==$v['depart_name'] ){
		echo '<option selected="selected" value="' . $v['depart_name']  . '">' .$v['depart_name'].'</option>';
	} else {
		echo '<option value="' . $v['depart_name']  . '">' . $v['depart_name']  .'</option>';
	}
}
 echo '</select></div>
	</div>';

echo '</table><div class="centre"><input type="submit" name="Search" value="查找"> &nbsp;&nbsp;<input type="submit" name="add_new" value="新增"></div>';

if (isset($_POST['Search']) or isset($result) or isset($_POST['Go']) or isset($_POST['Next']) or isset($_POST['Previous'])) {
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
    echo '
    <div>
    <table cellpadding="2" class="selection" >';

    echo '<tr>
                   <th class="ascending" width = 80>' . _('账号') . '</th>
                    <th class="ascending"width = 80>' . _('姓名') . '</th>
                     <th class="ascending"width = 80>' . _('部门') . '</th>  
					<th class="ascending"width = 80>' . _('建立人') . '</th>
					<th class="ascending"width = 160>' . _('建立日期') . '</th>
                    <th class="ascending"width = 50>' . _('操作') . '</th>
                    
            </tr>';
    $k = 0; //row counter to determine background colour
    $RowIndex = 0;



    if (DB_num_rows($result) <> 0) {
        DB_data_seek($result, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);
        $i = 0; //counter for input controls
        while (($myrow = DB_fetch_array($result)) and ($RowIndex <> $_SESSION['DisplayRecordsMax'])) {
            if ($k == 1) {
                echo '<tr class="EvenTableRows">';
                $k = 0;
            } else {
                echo '<tr class="OddTableRows">';
                $k = 1;
            }
             echo ' 
                                 <td>' .$myrow['emp_number'] . '</td>
                                 <td>' .$myrow['realname'] . '</td>
                                <td>' . $myrow['depart_name'] . '</td>
                                <td>' . $myrow['created_by'] . '</td>                                
                                <td>' . date('Y-m-d H:i:s',$myrow['creation_date']) . '</td>
                                                                 ';
           echo '<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?SelectedMeasureID=' . $myrow['id'] . '&amp;delete=1" onclick="return confirm(\'' . _('要删除记录?') . '\');">' . _('删除') . '</a></td>';

            echo '
			</tr>';
            $i++;
            $RowIndex++;
            //end of page full new headings if
        } //end loop through customers
        echo '</table></div>';
        echo '<input type="hidden" name="JustSelectedAvendor" value="Yes" />';
    }

    if (isset($ListPageMax) and $ListPageMax > 1) {
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
if (isset($_POST['add_new'])) {
    header('Location: Adddepartrelate.php?New=Y');
}
include('includes/footer.inc');
