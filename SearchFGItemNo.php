<?php
ob_start();
include('includes/session.inc');
$Title = _('成品料号维护');

$ViewTopic = '成品料号维护';
$BookMark = '成品料号维护';

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
    $sql = "select item_id,item_no,item_desc,units,min_order,safe_qty,item_name,lead_time,manufacture_time,yanse,(select zhidao_price from bom_headers_all b where a.item_no=b.assembly_item_no) zhidao_price,a.item_category1
	from sf_item_no a where item_type='F' ";

    //SQL添加条件
    if (isset($_POST['ItemNo']) and $_POST['ItemNo'] != '') {
        $sql = $sql . " and item_no like '%" . $_POST['ItemNo'] . "%' ";
    }
    if (isset($_POST['Item_name']) and $_POST['Item_name'] != '') {
        $sql = $sql . " and item_name like '%" . $_POST['Item_name'] . "%' ";
    }

      if (isset($_POST['item_category1']) and $_POST['item_category1'] != '') {
        $sql = $sql . " and item_category1 = '" . $_POST['item_category1'] . "' ";
    }
    
    if (isset($_POST['item_desc']) and $_POST['item_desc'] != '') {
        $sql = $sql . " and item_desc like '%" . $_POST['item_desc'] . "%' ";
    }
	$_SESSION['item_category1' . $identifier]=$_POST['item_category1'];
   
    $sql .= " ORDER BY item_no   "; //SQL排序
    $result = DB_query($sql, $db);
    if (DB_num_rows($result) == 0) {
        unset($result);
        prnMsg(_('找不到数据，请重新输入条件查询！'), 'error');
    }
}
?>
<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/magnifier.png" title="成品料号维护" alt="成品料号维护">成品料号维护</p>
<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST">
    <div>
        <input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
        <table cellpadding="3" class="selection">
            <tr>	
                <td>料号：</td>
                <td><input   type="text"  size="60" name="ItemNo" value=<?= $_POST['ItemNo'] ?> >
                </td>

                <td>料号名称：</td>
                <td><input   type="text"  size="60" name="Item_name" value=<?= $_POST['Item_name'] ?> >
                </td> 
				<tr></tr>
				  <td>规格型号：</td>
                <td><input   type="text"  size="60" name="item_desc" value=<?= $_POST['item_desc'] ?> >
                </td> 

				  <td>成品分类：</td>
			<?php
			$_POST['item_category1']=$_SESSION['item_category1' . $identifier];
   
    $sql = "SELECT unitname FROM sf_item_category ";
    $result1 = DB_query($sql, $db);
    echo '<td><select name="item_category1">';
	echo '<option value="' . $_POST['item_category1'] . '">' . $_POST['item_category1'].  '</option>';
		echo '<option value="">所有</option>';
    while ($Salesmanrow = DB_fetch_array($result1)) {
		
        echo '<option value="' . $Salesmanrow['unitname'] . '">' . $Salesmanrow['unitname'] .  '</option>';
    }
	 
            
               ?>
            
               
            </tr>
        </table>
        <div class="centre"><input type="submit" name="Search" value="查询">&nbsp;&nbsp;<input type="submit" name="add_new" value="新增成品料号"></div>

        <?php
        if (isset($_POST['Search']) and isset($result) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])) {
            $ListCount = DB_num_rows($result);
            $ListPageMax = ceil($ListCount / 30); //$_SESSION['DisplayRecordsMax']

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
                    <th class="ascending" width = "300" >料号</th>
                    <th class="ascending" width = "200" >料号名称</th>
                    <th class="ascending" width = "120" >规格型号</th>
                    <th class="ascending" width = "80" >指导价</th>
                    <th class="ascending" width = "40" >单位</th>
                    <th class="ascending" width = "90" >最小订单量</th>
                    <th class="ascending" width = "80" >安全库存</th>
					<th class="ascending" width = "80" >生产周期</th>
					<th class="ascending" width = "120" >成品分类</th>
                </tr>
                <?php
                $k = 0; //row counter to determine background colour
                $RowIndex = 0;
                if (DB_num_rows($result) <> 0) {
                    DB_data_seek($result, ($_POST['PageOffset'] - 1) * 30); // $_SESSION['DisplayRecordsMax']
                    $i = 0; //counter for input controls
                    while (($myrow = DB_fetch_array($result)) AND ( $RowIndex <> 30)) {//$_SESSION['DisplayRecordsMax']
                        if ($k == 1) {
                            echo '<tr class="EvenTableRows">';
                            $k = 0;
                        } else {
                            echo '<tr class="OddTableRows">';
                            $k = 1;
                        }
                        ?>
                        <td>
                            <a href="<?= $RootPath ?>/UpdateFGItemNo.php?ItemID=<?= $myrow['item_id'] ?>"><?= $myrow['item_no'] ?>
                        </td>
                        <td><?= $myrow['item_name'] ?>   </td>
                        <td><?= $myrow['item_desc'] ?> </td>
                        <td><?= $myrow['zhidao_price'] ?> </td>
                        <td>  <?= $myrow['units'] ?> </td>
                        <td><?= $myrow['min_order'] ?></td>
                        <td><?= $myrow['safe_qty'] ?></td>
						<td><?= $myrow['manufacture_time'] ?></td>
						<td><?= $myrow['item_category1'] ?></td>
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
if (isset($_POST['add_new'])) {
    header('Location: AddFGItemNo.php');
}
include('includes/footer.inc');
?>