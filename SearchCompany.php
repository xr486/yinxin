<?php
 ob_start();
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include('includes/session.inc');
$Title = _('下单公司维护');
$ViewTopic= '下单公司维护';
$BookMark = '下单公司维护';

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
    $sql = "SELECT * FROM companies where 1=1";
    if(isset($_POST['coyname']) and $_POST['coyname'] != ''){
        $sql = $sql." and coyname ".LIKE." '%".$_POST['coyname']."%' ";
    }
    if(isset($_POST['Employee_Num']) and $_POST['Employee_Num'] != ''){
        $sql = $sql." and Employee_Num ".LIKE." '%".$_POST['Employee_Num']."%' ";
    }
	if(isset($_POST['coycode']) and $_POST['coycode'] != ''){
        $sql = $sql." and coycode ".LIKE." '%".$_POST['coycode']."%' ";
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
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('查找公司') . '</p>';
echo '<table cellpadding="3" class="selection">';
echo '<div class="text-nav">';
echo '<div class="text-nav-1"><div>' . _('公司名称') . ':</div> ';
echo '<input type="text" name="coyname" value="' . $_POST['coyname'] . '" size="20" maxlength="25" /></div>';
echo '<div class="text-nav-1"><div>' . _('公司代号') . ':</div> ';
echo '<input type="text" name="coycode" value="' . $_POST['coycode'] . '" size="20" maxlength="25" /></div>';

echo '</table><div class="centre"><input type="submit" name="Search" value="查找">  </div>';

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
                    <th bgcolor="#87CEFA" class="ascending" width = 100>' . _('公司简称') . '</th>
                    <th bgcolor="#87CEFA" class="ascending"width = 250>' . _('公司名称') . '</th> 
                    <th bgcolor="#87CEFA" class="ascending"width = 150>' . _('税号') . '</th>
                    <th bgcolor="#87CEFA" class="ascending"width = 250>' . _('地址') . '</th>
         
                    <th bgcolor="#87CEFA" class="ascending"width = 100>' . _('电话') . '</th>
                
                    <th bgcolor="#87CEFA" class="ascending"width = 100>' . _('建立日期') . '</th> 
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
			echo '  <td><a href="' . $RootPath . '/CompanyModify.php?UpdateEmployee_Num=' . $myrow['coycode'] . '">' . $myrow['coycode'] . '</td>
				<td>' . $myrow['coyname'] . '</td> 
				<td>' . $myrow['taxpayerid'] . '</td>
				<td>' . $myrow['address'] . '</td>
		        <td>' . $myrow['telephone'] . '</td>
				<td>' . date('Y-m-d',$myrow['creation_date'] ). '</td> 
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
    header('Location: AddCompany.php');
}
include('includes/footer.inc');