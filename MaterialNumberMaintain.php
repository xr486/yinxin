<?php
include('includes/session.inc');
$Title = _('查询料号');

$ViewTopic = '查询料号';
$BookMark = '查询料号';

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
    $sql = 'select * from sf_item_no where 1=1 ';

    //SQL添加条件
    if (isset($_POST['ItemNo']) and $_POST['ItemNo'] != '') {
        $sql = $sql . " and item_no like '%" . $_POST['ItemNo'] . "%' ";
    }
    if (isset($_POST['ItemDesc']) and $_POST['ItemDesc'] != '') {
        $sql = $sql . " and item_desc like '%" . $_POST['ItemDesc'] . "%' ";
    }
      if (isset($_POST['catogery']) and $_POST['catogery'] != '') {
        $sql = $sql . " and item_category = '" . $_POST['catogery'] . "' ";
    }
    
    if (isset($_POST['Units']) and $_POST['Units'] != '') {
        $sql = $sql . " and units like '%" . $_POST['Units'] . "%' ";
    }
    if (isset($_POST['MinQty']) and $_POST['MinQty'] != '') {
        $sql = $sql . " and min_order like '%" . $_POST['MinQty'] . "%' ";
    }
    if (isset($_POST['SafeQty']) and $_POST['SafeQty'] != '') {
        $sql = $sql . " and safe_qty like '%" . $_POST['SafeQty'] . "%' ";
    }

    $sql .= " ORDER BY item_no   "; //SQL排序
    $result = DB_query($sql, $db);
    if (DB_num_rows($result) == 0) {
        unset($result);
        prnMsg(_('找不到数据，请重新输入条件查询！'), 'error');
    }
}
?>
<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/magnifier.png" title="查询料号" alt="查询料号">查询料号</p>
<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST">
    <div>
        <input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
        <table cellpadding="3" class="selection">
            <tr>	
                 
                <td>产品名称</td>
                <td><input   type="text" name="ItemDesc" value=<?= $_POST['ItemDesc'] ?> >
                </td> 
				<td>规格型号</td>
                <td><input   type="text" name="ItemNo" value=<?= $_POST['ItemNo'] ?> >
                </td>
          <!--   </tr>
            <tr> -->	
                <td>单位：</td>
                <td><input   type="text" name="Units" value=<?= $_POST['Units'] ?> >
                </td>
                <!-- <td>安全库存：</td>
                <td><input   type="text" name="SafeQty" value=<?= $_POST['SafeQty'] ?> >
                </td> -->

                <!-- <td>最小订单量：</td>
                <td><input   type="text" name="MinQty" value=<?= $_POST['MinQty'] ?> >
                </td>  -->
            </tr>
           <!--  <tr>	
                <td>安全库存：</td>
                <td><input   type="text" name="SafeQty" value=<?= $_POST['SafeQty'] ?> >
                </td>
              <!--   <td >类型':</td>
                <?php
                $sql = "SELECT unitname FROM sf_item_category ";
                $result1 = DB_query($sql, $db);
                echo '<td><select name="catogery">';
                echo '<option  selected="selected" value=""></option>';
                while ($Salesmanrow = DB_fetch_array($result1)) {
                    echo '<option value="' . $Salesmanrow['unitname'] . '">' . $Salesmanrow['unitname'] . '</option>';
                }

                echo '</select></td>';
                ?> -->
            <!-- </tr> -->
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
                    
                    <th class="ascending" width = "200" >规格型号</th>
					<th class="ascending" width = "200" >产品名称</th>
                    <!-- <th class="ascending" width = "120" >料号规格</th> -->
                    <!-- <th class="ascending" width = "80" >颜色</th> -->
                    <th class="ascending" width = "40" >单位</th>
                    <th class="ascending" width = "100" >供应商</th>
                    <th class="ascending" width = "100" >采购价</th>
                    <th class="ascending" width = "100" >售价</th>
                    <!-- <th class="ascending" width = "90" >最小订单量</th> -->
                    <!-- <th class="ascending" width = "80" >安全库存</th> -->
                    <!-- 
                    <th class="ascending" width = "80" >采购周期</th>
					<th class="ascending" width = "80" >生产周期</th> -->
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
                            <a href="<?= $RootPath ?>/UpdateMaterialNumber.php?ItemID=<?= $myrow['item_id'] ?>"><?= $myrow['item_no'] ?>
                        </td>
                        <td><?= $myrow['item_desc'] ?>   </td>
                        <!-- <td><?= $myrow['item_spec'] ?> </td> -->
                        <!-- <td><?= $myrow['yanse'] ?> </td>、 -->
                        <td>  <?= $myrow['units'] ?> </td>
                        <td>  <?= $myrow['supplier_name'] ?> </td>
                        <td>  <?= $myrow['po_price'] ?> </td>
                        <td>  <?= $myrow['unit_price'] ?> </td>
                        <!-- <td><?= $myrow['min_order'] ?></td> -->
                     <!--    <td><?= $myrow['safe_qty'] ?></td>
                        <td><?= $myrow['lead_time'] ?></td>
						<td><?= $myrow['manufacture_time'] ?></td> -->
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