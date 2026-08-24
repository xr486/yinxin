<?php
ob_start();
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include('includes/session.inc');
$Title = _('ECN审批');
$ViewTopic = 'ECN审批';
$BookMark = 'ECN审批';

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
  

 

 $sql = "select distinct b.change_name,a.assembly_item_no,a.creation_date,b.created_by, c.item_name
 from bom_headers_all a,bom_lines_modify_record b, sf_item_no c
 where a.assembly_item_no=b.order_number  and a.assembly_item_no = c.item_no and b.status='待审核' ";
 $sql = $sql . " order by creation_date desc";
 $result = DB_query($sql, $db);

if (isset($_POST['Search']) or isset($_POST['Go']) or isset($_POST['Next']) or isset($_POST['Previous'])) {
        $sql = "select distinct b.change_name,a.assembly_item_no,a.creation_date,b.created_by , c.item_name from bom_headers_all a,bom_lines_modify_record b, sf_item_no c
 where a.assembly_item_no=b.order_number  and a.assembly_item_no = c.item_no  and b.status='待审核'  ";
         
         if (isset($_POST['assembly_item_no']) and $_POST['assembly_item_no'] != '') {
                $sql = $sql . " and assembly_item_no " . LIKE . " '%" . $_POST['assembly_item_no'] . "%' ";
        }
        if (isset($_POST['item_name']) and $_POST['item_name'] != '') {
                $sql = $sql . " and c.item_name " . LIKE . " '%" . $_POST['item_name'] . "%' ";
        }
        if (isset($_POST['change_name']) and $_POST['change_name'] != '') {
                $sql = $sql . " and change_name " . LIKE . " '%" . $_POST['change_name'] . "%' ";
        }

          if (empty($_POST['FromDate']) == 0) {
        $SQL_FromDate = strtotime($_POST['FromDate']);
        $sql .= " and  b.creation_date >= '" . $SQL_FromDate . "' ";
    }
    if (empty($_POST['ToDate']) == 0) {
        $SQL_ToDate = strtotime($_POST['ToDate']) + 86400;
        //echo $SQL_ToDate;
        $sql .= " and  b.creation_date <='" . $SQL_ToDate . "' ";
    }
        
     $sql = $sql . " order by b.creation_date desc";

        $result = DB_query($sql, $db);
        if (DB_num_rows($result) == 0) {
               // unset($result);
                prnMsg(_('找不到该资料，请重新输入条件查询！'), 'error');
        }
}


echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('ECN审批') . '</p>';
echo '<table cellpadding="3" class="selection">';
echo '<div class="text-nav">';
echo '<div class="text-nav-1 "><div>' . _('母件料号') . ':</div> ';
echo '<input type="text" name="assembly_item_no" value="' . $_POST['assembly_item_no'] . '" size="20" maxlength="250" /></div>';
echo '<div class="text-nav-1 "><div>' . _('料号名称') . ':</div>';
echo '<input type="text" id="text_slect_customer" name="item_name" value="' . $_POST['item_name'] . '" size="20" maxlength="250" /></div>';
echo '<div class="text-nav-1 "><div>' . _('变更单号') . ':</div>';
echo '<input type="text" id="text_slect_name" name="change_name" value="' . $_POST['change_name'] . '" size="20" maxlength="250" /></div>';
/*
if (!isset($_POST['FromDate'])) {
    $_POST['FromDate'] = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 30, date("Y")));
}
if (!isset($_POST['ToDate'])) {
    $_POST['ToDate'] = Date('Y-m-d');
}*/
echo '  <div class="text-nav-1">
                <div>' . '建立日' . _('起') . ':</div>
	        <input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="FromDate" maxlength="10" size="11" value="' . $_POST['FromDate'] . '" />
        </div>
        <div class="text-nav-1">
                <div>'.'建立日' . _('止') . ':</div>
		<input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="ToDate" maxlength="10" size="11" value="' . $_POST['ToDate'] . '" />
        </div>
</div>';

echo '</table><div class="centre"><input type="submit" name="Search" value="查找"> </div>';

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
        echo '	<div class="text-nav-table">
                    <table cellpadding="2" class="selection" >';

        echo '<tr> 
	        <th>' . _('变更单号') . '</th>
		<th>' . _('母件料号') . '</th>
                <th>' . _('料号名称') . '</th>
		<th>' . _('建立日期') . '</th>
		<th>' . _('建立人') . '</th>
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
                        echo '  <td><a href="' . $RootPath . '/ECNApprove2.php?New=Yes&Updateorder_number=' . $myrow['assembly_item_no'] . '&Updatechange_name=' . $myrow['change_name'] .'">' . $myrow['change_name'] . '</td>
				<td>' . $myrow['assembly_item_no'] . '</td>
				<td>' . $myrow['item_name'] . '</td>	
				<td>' . date('Y-m-d H:i:s',$myrow['creation_date']) . '</td>
			        <td>' . $myrow['created_by'] . '</td>
			';
                        echo '</tr>';
                        $i++;
                        $RowIndex++;
                        //end of page full new headings if
                } //end loop through 项目概况建立
                echo '</table></div>';
                echo '<input type="hidden" name="JustSelectedAvendor" value="Yes" />';
        }

        if (isset($ListPageMax) and $ListPageMax > 1) {
                echo '<br /><div class="centre">&nbsp;&nbsp;' . _('第') . '' . $_POST['PageOffset'] . ' ' . _('页，共') . ' ' . $ListPageMax . ' ' . _('页数') . '. ' . _('转到页') . ': ';
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
        header('Location: ProjectCreate.php?New=Y');
}
include('includes/footer.inc');
?>
<script type="text/javascript">
        $('#btn_slect_customer').dialog({
                title: '选择客户',
                width: '1050px',
                height: 470,
                content: 'url:BtnSearchCustomer517.php?fwValue=&cat=buliao',
                init: function() {
                        this.content.document.getElementById('cat').value = 'buliao';
                        this.content.document.getElementById('fwValue').value = '';
                }
        });
        $('#btn_slect_customer2').dialog({
                title: '选择客户',
                width: '1050px',
                height: 470,
                content: 'url:BtnSearchCustomer517.php?fwValue=&cat=buliao',
                init: function() {
                        this.content.document.getElementById('cat').value = 'buliao';
                        this.content.document.getElementById('fwValue').value = '';
                }
        });
</script>