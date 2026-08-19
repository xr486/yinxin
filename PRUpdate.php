<?php
 ob_start();
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include('includes/session.inc');
$Title = _('采购申请单修改');
$ViewTopic= '采购申请单修改';
$BookMark = '采购申请单修改';

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
	 a.pr_num,a.status,a.creation_date ,a.created_by,depart_name,need_customer_code,need_order_number,need_date,remark,approve_date,approve_by
FROM
	pr_headers_all a
WHERE
	1 = 1
AND STATUS in( 'REJECTED','INPROCESS')
and a.pr_num in (select pr_num from pr_lines_all p where p.po_num is null)
  ";

  
	 if ($_SESSION['modify_flag']=='N') {
		$sql = $sql . " and created_by ='" .$_SESSION['UserID']."' ";
	}
   
    if(isset($_POST['pr_num_from']) and $_POST['pr_num_from'] != ''){ 
        $sql = $sql." and a.pr_num ".LIKE." '%".$_POST['pr_num_from']."%' "; 
    }

	if(isset($_POST['depart_name']) and $_POST['depart_name'] != ''){ 
        $sql = $sql." and a.depart_name ".LIKE." '%".$_POST['depart_name']."%' "; 
    }

 
     if (empty($_POST['FromDate']) == 0) {
        $SQL_FromDate = strtotime($_POST['FromDate']);
        $sql .= " and creation_date >= '" . $SQL_FromDate . "' ";
    }
    if (empty($_POST['ToDate']) == 0) {
        $SQL_ToDate = strtotime($_POST['ToDate']) + 86400;
        //echo $SQL_ToDate;
        $sql .= " and creation_date <='" . $SQL_ToDate . "' ";
    }
     $sql .= " order by pr_num ";
	// echo $sql;
    $result = DB_query($sql,$db);
    if (DB_num_rows($result)==0) {
        unset($result);
        prnMsg(_('找不到该请购单，请重新输入条件查询！') ,'error');
    }
}


echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('查找采购申请单') . '</p>';
echo '<table cellpadding="3" class="selection">';
echo '<div class="text-nav">';

echo '<div class="text-nav-1"><div>' . _('请购单号') . ':</div>';
echo '<input type="text" name="pr_num_from" value="' . $_POST['pr_num_from'] . '" size="20" maxlength="25" /></div>';
 echo '<div class="text-nav-1"><div>' . _('请购部门') . ':</div>';
echo '<input type="text" name="depart_name" value="' . $_POST['depart_name'] . '" size="20" maxlength="25" /></div>';
if (!isset($_POST['FromDate'])) {
    $_POST['FromDate'] = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 30, date("Y")));
}
if (!isset($_POST['ToDate'])) {
    $_POST['ToDate'] = Date('Y-m-d');
}
 

echo '<div class="text-nav-1"><div>' . '建立日' . _('起') . ':</div>
		<input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="FromDate" maxlength="10" size="11" value="' . $_POST['FromDate'] . '" /></div>
		<div class="text-nav-1"><div>'. '建立日' . _('止') . ':</div>
		<input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="ToDate" maxlength="10" size="11" value="' . $_POST['ToDate'] . '" /></div>
	</div>';

echo '</table><div class="centre"><input type="submit" name="Search" value="查找"> </div>'
. '</br>';

if (isset($_POST['Search']) and isset($result) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])){
    $ListCount = DB_num_rows($result);
    $ListPageMax = ceil($ListCount / 15);
    
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
    echo '<div class="text-nav-table">
                    <table cellpadding="2" class="selection" >';

    echo '<tr>
                    <th class="ascending" width = 100>' . _('请购单号') . '</th>
                    <th  width = 80>' . _('状态') . '</th> 
                    <th class="ascending"width = 120>' . _('申请部门') . '</th>
                    <th  width = 90>' . _('需求日期') . '</th>
                    <th class="ascending"width = 150>' . _('备注') . '</th>
                    <th  width = 90>' . _('签核日') . '</th>
                    <th class="ascending"width = 100>' . _('签核人') . '</th>
                    <th  width = 160>' . _('下单日') . '</th>
					<th class="ascending"width = 120>' . _('下单人') . '</th> 
            </tr>';
    $k = 0; //row counter to determine background colour
    $RowIndex = 0;
    
 

    if (DB_num_rows($result) <> 0) {
        DB_data_seek($result, ($_POST['PageOffset'] - 1) * 15);
        $i = 0; //counter for input controls
        while (($myrow = DB_fetch_array($result)) AND ($RowIndex <> 15)) {
			if ($k == 1) {
				echo '<tr class="EvenTableRows">';
				$k = 0;
			} else {
				echo '<tr class="OddTableRows">';
				$k = 1;
			}
			if($myrow['status'] == 'INPROCESS') {
                            $v_status = '待签核';
                        }elseif($myrow['status'] == 'APPROVED'){
                            $v_status = '核准';
                        }elseif($myrow['status'] == 'REJECTED'){
                            $v_status = '拒绝';
                        }else{
                             $v_status = '已取消';
                        }
						$approve_date='';
				 if ($myrow['approve_date'] >1 ) {
                    $approve_date=date('Y-m-d', $myrow['approve_date']);
				 }
			echo '  <td><a href="' . $RootPath . '/PRUpdate2.php?New=Yes&Updatepo_num=' . $myrow['pr_num'] . '">' . $myrow['pr_num'] . '</td>
				<td>' . $v_status . '</td> 
				<td>' . $myrow['depart_name'] . '</td>
				<td>' . date('Y-m-d',$myrow['need_date']) . '</td>
				<td>' . $myrow['remark'] . '</td>
				<td>' . $approve_date . '</td>
				<td>' . $myrow['approve_by'] . '</td>
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

include('includes/footer.inc');