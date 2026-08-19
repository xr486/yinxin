<?php
ob_start();
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include('includes/session.inc');
$Title = _('采购申请单查询');
$ViewTopic = '采购申请单查询';
$BookMark = '采购申请单查询';

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

$sql = "select a.pr_num,a.status,a.creation_date ,a.created_by,need_date,remark,a.approve_date,(select realname from www_users where userid=a.created_by) realname,(select realname from www_users where userid=a.approve_by) approve_by_realname
from pr_headers_all a  where 1=1";

$sql = $sql . " order by a.creation_date desc   ";
$result = DB_query($sql, $db);

if (isset($_POST['Search']) or isset($_POST['Go']) or isset($_POST['Next']) or isset($_POST['Previous'])) {
        $sql = "select a.pr_num,a.status,a.creation_date ,a.created_by,need_date,remark,a.approve_date,(select realname from www_users where userid=a.created_by) realname,(select realname from www_users where userid=a.approve_by) approve_by_realname
 from pr_headers_all a  where 1=1";
        if (isset($_POST['pr_num_from']) and $_POST['pr_num_from'] != '') {
                $sql = $sql . " and a.pr_num " . LIKE . " '%" . $_POST['pr_num_from'] . "%' ";
        }

        if (isset($_POST['need_customer_code']) and $_POST['need_customer_code'] != '') {
                $sql = $sql . " and a.need_customer_code " . LIKE . " '%" . $_POST['need_customer_code'] . "%' ";
        }

        if (isset($_POST['need_order_number']) and $_POST['need_order_number'] != '') {
                $sql = $sql . " and a.need_order_number " . LIKE . " '%" . $_POST['need_order_number'] . "%' ";
        }
        

        if (empty($_POST['FromDate']) == 0) {
                $SQL_FromDate = strtotime($_POST['FromDate']);
                $sql .= " and a.creation_date >= '" . $SQL_FromDate . "' ";
        }
        if (empty($_POST['ToDate']) == 0) {
                $SQL_ToDate = strtotime($_POST['ToDate']) + 86400;
                //echo $SQL_ToDate;
                $sql .= " and a.creation_date <='" . $SQL_ToDate . "' ";
        }
		 $sql = $sql . " order by a.creation_date desc   ";
        $result = DB_query($sql, $db);
        if (DB_num_rows($result) == 0) {
                unset($result);
                prnMsg(_('找不到该采购申请单，请重新输入条件查询！'), 'error');
        }
}


echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('查找采购申请单') . '</p>';
echo '<table cellpadding="3" class="selection">';
echo '<div class="text-nav3">';


echo '</div>';
if (!isset($_POST['FromDate'])) {
        $_POST['FromDate'] = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 30, date("Y")));
}
if (!isset($_POST['ToDate'])) {
        $_POST['ToDate'] = Date('Y-m-d');
}
echo '<div class="text-nav3">';
echo '<div class="text-nav-1 " ><div>' . _('请购单号') . ':</div>';
echo '<input type="text" name="pr_num_from" value="' . $_POST['pr_num_from'] . '" size="20" maxlength="25" /></div>';


echo '<div class="text-nav-1 "><div>' . '建立日起' .  ':</div>
        <input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="FromDate" maxlength="10" size="11" value="' . $_POST['FromDate'] . '" />
</div>
      <div class="text-nav-1 "><div>' .'建立日止' .   ':</div>
        <input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="ToDate" maxlength="10" size="11" value="' . $_POST['ToDate'] . '" />
</div>
</div>';

echo '</table><div class="centre"><input type="submit" name="Search" value="查找"> </div>'
        . '</br>';

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
                    <table cellpadding="2" class="selection" >';

        echo '<tr> <th class="ascending" width = 100>' . _('请购单号') . '</th>
                    <th  width = 80>' . _('状态') . '</th>
                 
                    <th  width = 90>' . _('需求日期') . '</th>
                    <th class="ascending"width = 150>' . _('备注') . '</th>
                    <th  width = 90>' . _('签核日') . '</th>
                    <th  width = 100>' . _('签核人') . '</th>
                    <th  width = 160>' . _('下单日') . '</th>
					<th  width = 120>' . _('下单人') . '</th> 
				
            </tr>';
        $k = 0; //row counter to determine background colour
        $RowIndex = 0;



        if (DB_num_rows($result) <> 0) {
                DB_data_seek($result, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax'] );
                $i = 0; //counter for input controls
                while (($myrow = DB_fetch_array($result)) and ($RowIndex <> $_SESSION['DisplayRecordsMax'] )) {
                        if ($k == 1) {
                                echo '<tr class="EvenTableRows">';
                                $k = 0;
                        } else {
                                echo '<tr class="OddTableRows">';
                                $k = 1;
                        }
                        if ($myrow['status'] == 'INPROCESS') {
                                $v_status = '待签核';
                        } elseif ($myrow['status'] == 'APPROVED') {
                                $v_status = '已签核';
                        } elseif ($myrow['status'] == 'REJECTED') {
                                $v_status = '已拒签';
                        } else {
                                $v_status = '已取消';
                        }
                        if ($myrow['approve_date'] > 0) {
                                $approve_date = date('Y-m-d', $myrow['approve_date']);
                        }else {
                                $approve_date = '';
                        }
                        echo '  <td><a href="' . $RootPath . '/PrReport2.php?Updatepr_num=' . $myrow['pr_num'] . '" target="_blank" >' . $myrow['pr_num'] . '</td>
				<td>' . $v_status . '</td>			
			
				<td>' . date('Y-m-d', $myrow['need_date']) . '</td>
				<td>' . $myrow['remark'] . '</td>
				<td>' . $approve_date . '</td>
				<td>' . $myrow['approve_by_realname'] . '</td>
				<td>' . date('Y-m-d H:i:s', $myrow['creation_date']) . '</td>
				<td>' . $myrow['realname'] . '</td> 
				
				';
				echo '  <td><a href="' . $RootPath . '/PrintPrReport.php?Updatedelivery_num=' . $myrow['pr_num'] . '"target="_blank">打印 </td>';
                        echo '
			</tr>';
                        $i++;
                        $RowIndex++;
                        //end of page full new headings if
                } //end loop through vendors
                echo '</table>
                <a href="' . $RootPath . '/PrReportExcel.php?pr_num_from=' . $_POST['pr_num_from'] . '&need_customer_code=' . $_POST['need_customer_code'] . '&need_order_number=' . $_POST['need_order_number'] . '&need_stockid=' . $_POST['need_stockid'] . '&FromDate=' . $_POST['FromDate'] . '&ToDate=' . $_POST['ToDate'] . '">' . '资料导出Excel表' . '</a>
                
               
                ';
               
               
                
  
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

include('includes/footer.inc');
