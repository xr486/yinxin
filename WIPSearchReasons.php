<?php
include('includes/session.inc');
$Title = _('不良原因和报废原因维护');

$ViewTopic = '不良原因和报废原因维护';
$BookMark = '不良原因和报废原因维护';

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

if (isset($_POST['Search']) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])) {
    //日期格式化为SQL格式
    $SQL_FromDate = FormatDateForSQL($_POST['FromDate']);
    $SQL_ToDate = FormatDateForSQL($_POST['ToDate']);
    //查找数据的SQL
    $sql = 'select * from wip_reasons where 1=1';

    //SQL添加条件
    if (isset($_POST['reason_id']) and $_POST['reason_id'] != '') {
        $sql = $sql . " and reason_id like '%" . $_POST['reason_id'] . "%' ";
    }
    if (isset($_POST['reason_type']) and $_POST['reason_type'] != '') {
        $sql = $sql . " and reason_type like '%" . $_POST['reason_type'] . "%' ";
    }
      if (isset($_POST['material_quality']) and $_POST['material_quality'] != '') {
        $sql = $sql . " and material_quality = '" . $_POST['material_quality'] . "' ";
    }
    
    if (isset($_POST['polish_unit_price']) and $_POST['polish_unit_price'] != '') {
        $sql = $sql . " and polish_unit_price like '%" . $_POST['polish_unit_price'] . "%' ";
    }
    if (isset($_POST['stock_count']) and $_POST['stock_count'] != '') {
        $sql = $sql . " and stock_count like '%" . $_POST['stock_count'] . "%' ";
    }
    if (isset($_POST['production_oneday']) and $_POST['production_oneday'] != '') {
        $sql = $sql . " and production_oneday like '%" . $_POST['production_oneday'] . "%' ";
    }

    $sql .= " ORDER BY reason_id"; //SQL排序
    $result = DB_query($sql, $db);
    if (DB_num_rows($result) == 0) {
        unset($result);
        prnMsg(_('找不到数据，请重新输入条件查询！'), 'error');
    }
}
?>
<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/magnifier.png" title="查询不良原因和报废原因" alt="查询不良原因和报废原因">查询不良原因和报废原因</p>
<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST">
    <div>
        <input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
        <table cellpadding="3" class="selection">
            <tr>  
                <td>原因编号</td>
                <td><input   type="text" name="reason_id" value=<?= $_POST['reason_id'] ?> >
                </td>

                <td>原因类型</td>

                <td><input   type="text" name="reason_type" value=<?= $_POST['reason_type'] ?> >
                </td> 
            </tr>

           
        </table>
        <div class="centre"><input type="submit" name="Search" value="查找"></div>

        <?php
        if (isset($_POST['Search']) and isset($result) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])) {
            $ListCount = DB_num_rows($result);
            $ListPageMax = ceil($ListCount / 10); //$_SESSION['DisplayRecordsMax']

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
            ?>
            <input type="hidden" name="PageOffset" value=<?= $_POST['PageOffset'] ?> />

            <br />
            <table cellpadding="2" class="selection">
                <tr>
                    <th class="ascending" width = "100" >原因编号</th>
                    <th class="ascending" width = "200" >原因描述</th>
                    <th class="ascending" width = "120" >原因类型</th>
                    <th class="ascending" width = "80" >工序</th>
                    <th class="ascending" width = "40" >是否生效</th>
                </tr>
                <?php
                $k = 0; //row counter to determine background colour
                $RowIndex = 0;
                if (DB_num_rows($result) <> 0) {
                    DB_data_seek($result, ($_POST['PageOffset'] - 1) * 10); // $_SESSION['DisplayRecordsMax']
                    $i = 0; //counter for input controls
                    while (($myrow = DB_fetch_array($result)) AND ( $RowIndex <> 10)) {//$_SESSION['DisplayRecordsMax']
                        if ($k == 1) {
                            echo '<tr class="EvenTableRows">';
                            $k = 0;
                        } else {
                            echo '<tr class="OddTableRows">';
                            $k = 1;
                        }
                        ?>
                       
                        <td>
                            <a href="<?= $RootPath ?>/WIPUpdateReasons.php?reason_id=<?= $myrow['reason_id'] ?>"><?= $myrow['reason_id'] ?>
                        </td>
                        <td><?= $myrow['reason_detail'] ?>   </td>
                        <td><?= $myrow['reason_type'] ?> </td>
                        <td><?= $myrow['type'] ?> </td>
                        <td><?= ($myrow['flag'] == 1 ? '是' : '否') ?> </td>
                        </tr>
                        <?php
                        $i++;
                        $RowIndex++;
                    }
                    ?>
                </table>
                <?php
            }
            if (isset($ListPageMax) AND $ListPageMax > 1) {
                ?>
                <br />

                  <div class="centre">&nbsp;&nbsp;第&nbsp;<?= $_POST['PageOffset'] ?>&nbsp;页，共&nbsp;<?= $ListPageMax ?>&nbsp;页&nbsp;&nbsp; 跳转至页: 
                    <select name="PageOffset1">
                        <?php
                        $ListPage = 1;
                        while ($ListPage <= $ListPageMax) {
                            if ($ListPage == $_POST['PageOffset']) {
                                echo '<option value="' . $ListPage . '" selected="selected">' . $ListPage . '</option>';
                            } else {
                                echo '<option value="' . $ListPage . '">' . $ListPage . '</option>';
                            }
                            $ListPage++;
                        }
                        ?>
                    </select>

                    <input type="submit" name="Go1" value="跳转" />
                    <input type="submit" name="Previous" value="上一页" />
                    <input type="submit" name="Next" value="下一页" />

                </div>
    <?php }
} ?>

    </div>
</form>
<?php
include('includes/footer.inc');
?>