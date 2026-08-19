<?php
include('includes/session.inc');
$Title = _('查询模具');

$ViewTopic = '查询模具';
$BookMark = '查询模具';

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
    /*
     *
     * <th class="ascending" width = "80" >模具类别</th>
                        <th class="ascending" width = "80" >模具图号</th>
                        <th class="ascending" width = "80" >产品图号</th>
                        <th class="ascending" width = "80" >模具材质</th>
                        <th class="ascending" width = "80" >图纸版本</th>
                        <th class="ascending" width = "80" >来模日期</th>
                        <th class="ascending" width = "80" >记录人</th>
                        <th class="ascending" width = "80" >模具使用次数</th>
                        <th class="ascending" width = "80" >对应半成品料号</th>

     */
    //$sql = 'select item_id,item_no,item_desc,units,min_order,safe_qty,item_spec,lead_time,manufacture_time,yanse from sf_item_no where 1=1 ';
    $sql = 'select
    mold_class,
	mould_number,
	product_map_number,
	mold_material,
	version_drawing,
	die_date,
	note_taker,
	number_of_use,
	product_number,
	work_id,stauts,flag
    from wip_mold where 1=1 ';

    //SQL添加条件
    if (isset($_POST['mould_number']) and $_POST['mould_number'] != '') {
        $sql = $sql . " and mould_number like '%" . $_POST['mould_number'] . "%' ";
    }
    if (isset($_POST['product_map_number']) and $_POST['product_map_number'] != '') {
        $sql = $sql . " and product_map_number like '%" . $_POST['product_map_number'] . "%' ";
    }
    if (isset($_POST['mold_material']) and $_POST['mold_material'] != '') {
        $sql = $sql . " and mold_material like '%" . $_POST['product_map_number'] . "%' ";
    }

    if (isset($_POST['die_date']) and $_POST['die_date'] != '') {
        $date=strtotime($_POST['die_date']);
        $sql = $sql . " and die_date like '%" . $date . "%' ";
    }
    if (isset($_POST['note_taker']) and $_POST['note_taker'] != '') {
        $sql = $sql . " and note_taker like '%" . $_POST['note_taker'] . "%' ";
    }
    if (isset($_POST['mold_class']) and $_POST['mold_class'] != '') {
        $sql = $sql . " and mold_class like '%" . $_POST['mold_class'] . "%' ";
    }

    //$sql .= " ORDER BY item_no   "; //SQL排序
    $result = DB_query($sql, $db);
    if (DB_num_rows($result) == 0) {
        unset($result);
        prnMsg(_('找不到数据，请重新输入条件查询！'), 'error');
    }
}
?>
    <p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/magnifier.png" title="查询模具" alt="查询模具">查询模具</p>
    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST">
        <div>
            <input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
            <table cellpadding="3" class="selection">
                <tr>
                    <td>模具图号：</td>
                    <td><input   type="text" name="mould_number" value=<?= $_POST['mould_number'] ?> >
                    </td>

                    <td>产品图号：</td>
                    <td><input   type="text" name="product_map_number" value=<?= $_POST['product_map_number'] ?> >
                    </td>
                </tr>
                <tr>
                    <td>模具材质：</td>
                    <td><input   type="text" name="mold_material" value=<?= $_POST['mold_material'] ?> >
                    </td>

                    <td>来模日期：</td>
                    <td><input type="text" onfocus="WdatePicker()"  name="die_date" />
                    </td>
                </tr>
                <tr>
                    <td>记录人：</td>
                    <td><input   type="text" name="note_taker" value=<?= $_POST['note_taker'] ?> >
                    </td>
                    <td >模具类别:</td>
                    <td><select name="mold_class" id="mold_class">
                    <option  value="finished_mold"  <?php if($_POST['mold_class']=='finished_mold'){ ?>selected="selected"<?php } ?> >成品模具</option>
                    <option  value="mud_core_mold"  <?php if($_POST['mold_class']=='mud_core_mold'){ ?>selected="selected"<?php } ?> >泥芯模具</option>
                    </select></td>
                </tr>
            </table>
            <div class="centre"><input type="submit" onclick="test1()" name="Search" value="查找"></div>
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

                <?php
                if ($ListPageMax > 1) {
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
        <?php } ?>

                <br />
                <table cellpadding="2" class="selection">
                    <tr>
                        <th class="ascending" width = "100" >模具类别</th>
                        <th class="ascending" width = "100" >模具图号</th>
                        <th class="ascending" width = "100" >产品图号</th>
                        <th class="ascending" width = "100" >模具材质</th>
                        <th class="ascending" width = "100" >图纸版本</th>
                        <th class="ascending" width = "130" >来模日期</th>
                        <th class="ascending" width = "100" >记录人</th>
                        <th class="ascending" width = "120" >模具使用次数</th>
                        <th class="ascending" width = "120" >对应半成品料号</th>
                        <th class="ascending" width = "120" >是否领用</th>
                        <th class="ascending" width = "120" >领取状态</th>

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
                            <td id="test02">
                                <?= $myrow['mold_class']=='finished_mold' ? '成品模具':'泥芯模具' ?>
                            </td>
                            <td>
                            <a href="<?= $RootPath ?>/Updatemold.php?work_id=<?= $myrow['work_id'] ?>  " target="_blank">
                            <?= $myrow['mould_number'] ?>   </td>
                            <td><?= $myrow['product_map_number'] ?> </td>
                            <td><?= $myrow['mold_material'] ?> </td>
                            <td><?= $myrow['version_drawing'] ?> </td>
                            <td>  <?=date("Y-m-d",$myrow['die_date'])   ?> </td>
                            <td><?= $myrow['note_taker'] ?></td>
                            <td><?= $myrow['number_of_use'] ?></td>
                            <td><?= $myrow['product_number'] ?></td>
                            <td><?= $myrow['stauts']=='1' ? '是':'否' ?></td>
                            <td><?= $myrow['flag']=='1' ? '有效':'失效' ?></td>
                            </tr>
                            <?php
                            $i++;
                            $RowIndex++;
                        }
                        /*
                         *
       mold_class,
	mould_number,
	product_map_number,
	mold_material,
	version_drawing,
	die_date,
	note_taker,
	number_of_use,
	product_number
                         */
                        ?>
                    </table>
                    <?php
                }
                if (isset($ListPageMax) AND $ListPageMax > 1) {
                    ?>
                    <br />

                    <div class="centre">&nbsp;&nbsp;第&nbsp;<?= $_POST['PageOffset'] ?>&nbsp;页，共&nbsp;<?= $ListPageMax ?>&nbsp;页&nbsp;&nbsp; 跳转至页:
                        <select name="PageOffset2">
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