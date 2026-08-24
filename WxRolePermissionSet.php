<?php
ob_start();
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include('includes/session.inc');
$Title = _('APP角色权限管理');
$ViewTopic = 'APP角色权限管理';
$BookMark = 'APP角色权限管理';
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
$sql = "    
        select *  
        from fa_roles 
        where 1=1   
    ";
$sql = $sql . "order by role_name asc";
$result = DB_query($sql, $db);
if (isset($_POST['Search']) or isset($_POST['Go']) or isset($_POST['Next']) or isset($_POST['Previous'])) {
        $sql = "
        select *  
        from fa_roles 
        where 1=1
    ";
        if (isset($_POST['role_name']) and $_POST['role_name'] != '') {
                $sql = $sql . " and role_name " . LIKE . " '%" . $_POST['role_name'] . "%' ";
        }

        $sql = $sql . "order by role_name asc";
        $result = DB_query($sql, $db);
        if (DB_num_rows($result) == 0) {
                //unset($result);
                prnMsg(_('找不到该信息，请重新输入条件查询！'), 'error');
        }
}


echo '
    <form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">
        <div>
                <input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
                <p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('APP角色权限管理') . '</p>
                <table cellpadding="3" class="selection">
                        <tr>
                                <td>' . _('使用角色') . ':</td>
                                <td>
                                        <input type="text" name="role_name" value="' . $_POST['role_name'] . '" size="20" maxlength="25" />
                                </td>
                        </tr>
                </table>
                <div class="centre">
                        <input type="submit" name="Search" value="查找">
                        &nbsp;&nbsp; 
                </div>
    ';
if (isset($result)) {
        $total_line = 0;
        while (($myrow2 = DB_fetch_array($result))) {
                $total_line = $total_line + 1;
        }
}
if ((isset($_POST['Search']) or isset($result) or isset($_POST['Go']) or isset($_POST['Next']) or isset($_POST['Previous']))) {
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
        echo '<div class="text-nav-table">
                    <table cellpadding="2" class="selection" >';
        echo '<tr>
                <th class="ascending" >' . _('角色名称') . '</th>
                <th class="ascending" >' . _('最新修改人') . '</th>
                <th class="ascending" >' . _('最新修改日期') . '</th>
                <th class="ascending">' . _('权限管理') . '</th>        
            </tr>';
        $k = 0; //row counter to determine background colour
        $RowIndex = 0;
        $all_line = 0;
        //单页起始


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
                        $all_line = $all_line + 1;
                        //单页累加
                        echo '  
                                <td>' . $myrow['role_name'] . '</td>        
                                <td>' . $myrow['last_updated_by'] . '</td>
                                <td>' . date('Y-m-d h-i-s', $myrow['last_update_date']) . '</td>
                                <td>
                                    <a target="view_window" href="' . $RootPath . '/WxRolePermissionSet2.php?role_id=' . $myrow['role_id'] . '">
                                    权限管理
                                </td>
				';


                        echo '
			</tr>';
                        $i++;
                        $RowIndex++;
                }
                echo '</table>
                
        
                </div>';
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
        header('Location: AddCustomer.php');
}
include('includes/footer.inc');
?>
<script type="text/javascript">
        /* $('#btn_slect_customer').dialog({
            title:'选择客户',
            width: '1050px',
            height: 470,
            content:'url:BtnSearchCustomer517.php?fwValue=&cat=buliao',
            init:function(){
			    this.content.document.getElementById('cat').value = 'buliao';
                this.content.document.getElementById('fwValue').value = '';
            }
        }); */
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
<!-- <style>
    *{
        display: flex;
        align-items: center;
    }
</style> -->