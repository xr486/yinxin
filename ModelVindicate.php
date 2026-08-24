<?php
include('includes/session.inc');
$Title = _('报表');
$ViewTopic = '报表';
$BookMark = '报表';

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
    //$sql = 'select item_id,item_no,item_desc,units,min_order,safe_qty,item_spec,lead_time,manufacture_time,yanse from sf_item_no where 1=1 ';
//    $sql = 'select sandboxid,sandboxname,sandboxsize,sandweight,sandboxnum,sandboxuse
//            from sandbox where 1=1 ';
    $sql = "select  *
            from wip_transactions
            where operation='浇注'
            OR operation='造型'
            OR operation='打磨'
            OR operation='入库'
            ";


    //d_id,model_station,goodProductCount,noGoodCount,noGoodCountReason,rejectCount,rejectReason,person,wip_entity_id

    if (isset($_POST['wip_entity_name']) and $_POST['wip_entity_name'] != '') {
        $sql = $sql . " and wip_entity_name like '%".$_POST['wip_entity_name']."%' ";
    }
    if (isset($_POST['model_station']) and $_POST['model_station'] != '') {
        $sql = $sql . " and model_station like '%" . $_POST['model_station'] . "%' ";
    }
    if (isset($_POST['goodProductCount']) and $_POST['goodProductCount'] != '') {
        $sql = $sql . " and goodProductCount = '" . $_POST['goodProductCount'] . "' ";
    }

    if (isset($_POST['noGoodCount']) and $_POST['noGoodCount'] != '') {
        $sql = $sql . " and noGoodCount like '%" . $_POST['noGoodCount'] . "%' ";
    }
    if (isset($_POST['noGoodCountReason']) and $_POST['noGoodCountReason'] != '') {
        $sql = $sql . " and noGoodCountReason like '%" . $_POST['noGoodCountReason'] . "%' ";
    }
    if (isset($_POST['rejectCount']) and $_POST['rejectCount'] != '') {
        $sql = $sql . " and rejectCount like '%" . $_POST['rejectCount'] . "%' ";
    }
    if (isset($_POST['rejectReason']) and $_POST['rejectReason'] != '') {
        $sql = $sql . " and rejectReason like '%" . $_POST['rejectReason'] . "%' ";
    }
    if (isset($_POST['person']) and $_POST['person'] != '') {
        $sql = $sql . " and person like '%" . $_POST['person'] . "%' ";
    }
    if (isset($_POST['wip_entity_id']) and $_POST['wip_entity_id'] != '') {
        $sql = $sql . " and wip_entity_id like '%" . $_POST['wip_entity_id'] . "%' ";
    }



    $sql .= " ORDER BY wip_entity_name   "; //SQL排序
    $result = DB_query($sql, $db);
    if (DB_num_rows($result) == 0) {
        unset($result);
        prnMsg(_('找不到数据，请重新输入条件查询！'), 'error');
    }
}

?>
<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/magnifier.png" title="报表" alt="报表">报表</p>
<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST">
    <div>
        <input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
        <table cellpadding="3" class="selection">
            <tr>
                <td>工单号：</td>
                <td><input   type="text" name="wip_entity_name" value=<?= $_POST['wip_entity_name'] ?> >
                </td>

                <td>工位号：</td>
                <td><input   type="text" name="model_station" value=<?= $_POST['model_station'] ?> >
                </td>
            </tr>
            <!--            <tr>-->
            <!--                <td>单位：</td>-->
            <!--                <td><input   type="text" name="Units" value=--><?//= $_POST['Units'] ?><!-- >-->
            <!--                </td>-->
            <!---->
            <!--                <td>最小订单量：</td>-->
            <!--                <td><input   type="text" name="MinQty" value=--><?//= $_POST['MinQty'] ?><!-- >-->
            <!--                </td>-->
            <!--            </tr>-->
            <!--            <tr>-->
            <!--                <td>安全库存：</td>-->
            <!--                <td><input   type="text" name="SafeQty" value=--><?//= $_POST['SafeQty'] ?><!-- >-->
            <!--                </td>-->
            <!--                <td >类型':</td>-->
            <!--                --><?php
            //                $sql = "SELECT unitname FROM sf_item_category ";
            //                $result1 = DB_query($sql, $db);
            //                echo '<td><select name="catogery">';
            //                echo '<option  selected="selected" value=""></option>';
            //                while ($Salesmanrow = DB_fetch_array($result1)) {
            //                    echo '<option value="' . $Salesmanrow['unitname'] . '">' . $Salesmanrow['unitname'] . '</option>';
            //                }
            //
            //                echo '</select></td>';
            //                ?>
            <!--            </tr>-->
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

                <th class="ascending" width = "100" >工序名称</th>
                <th class="ascending" width = "120" >工单号</th>

                <th class="ascending" width = "120" >生产日期</th>
                <th class="ascending" width = "120" >工位</th>
                <th class="ascending" width = "80" >良品数量</th>
                <th class="ascending" width = "80" >不良数量</th>
                <th class="ascending" width = "80" >不良原因</th>
                <th class="ascending" width = "90" >报废数量</th>
                <th class="ascending" width = "90" >报废原因</th>
                <th class="ascending" width = "90" >工作人员1</th>
                <th class="ascending" width = "90" >工作人员2</th>

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
                    <td><?= $myrow['operation']?></td>
                    <td><?= $myrow['wip_entity_name']?></td>
                    <td><?= date('Y-m-d',$myrow['transaction_date'])?></td>
                    <td><?= $myrow['model_station']?></td>
                    <td><?= $myrow['goodProductCount']?></td>
                    <td><?= $myrow['badCount']?></td>
                    <td><?= $myrow['badCountReason']?></td>
                    <td><?= $myrow['scrapCount']?></td>
                    <td><?= $myrow['scrapReason']?></td>
                    <td><?= $myrow['person1']?></td>
                    <td><?= $myrow['person2']?></td>
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
<!--/**-->
<!-- * Created by PhpStorm.-->
<!-- * User: asus-->
<!-- * Date: 2017/12/25-->
<!-- * Time: 9:09-->
<!-- */-->