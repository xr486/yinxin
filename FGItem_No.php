<?php
include ('includes/session.inc');
$Title = _('查询成品料号');

$ViewTopic = '查询成品料号';
$BookMark = '查询成品料号';

include ('includes/header.inc');
include ('includes/SQL_CommonFunctions.inc');

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
if (isset($_POST['add'])) {
    header('Location: AddFGItemNo.php');
}
if (isset($_POST['Search']) or isset($_POST['Go']) or isset($_POST['Next']) or
    isset($_POST['Previous'])) {
    //日期格式化为SQL格式
    $SQL_FromDate = FormatDateForSQL($_POST['FromDate']);
    $SQL_ToDate = FormatDateForSQL($_POST['ToDate']);
    //查找数据的SQL
    $sql = 'select  a.assembly_item_no,a.weight,a.units,a.item_id,a.item_no,a.tuhao,a.item_desc,a.customer_code,b.customer_name from sf_item_no a,customers b where a.customer_code=b.customer_code and item_type=3 ';

    //SQL添加条件
    if (isset($_POST['ItemNo']) and $_POST['ItemNo'] != '') {
        $sql = $sql . " and a.item_no like '%" . $_POST['ItemNo'] . "%' ";
    }
    if (isset($_POST['ItemDesc']) and $_POST['ItemDesc'] != '') {
        $sql = $sql . " and a.item_desc like '%" . $_POST['ItemDesc'] . "%' ";
    }  
	 if (isset($_POST['customer_name']) and $_POST['customer_name'] != '') {
        $sql = $sql . " and b.customer_name like '%" . $_POST['customer_name'] . "%' ";
    } 
	 if (isset($_POST['customer_code']) and $_POST['customer_code'] != '') {
        $sql = $sql . " and a.customer_code like '%" . $_POST['customer_code'] . "%' ";
    } 

	if (isset($_POST['ItemDesc']) and $_POST['ItemDesc'] != '') {
        $sql = $sql . " and a.tuhao = '" . $_POST['ItemDesc'] . "' ";
    } 
	if (isset($_POST['assembly_item_no']) and $_POST['assembly_item_no'] != '') {
        $sql = $sql . " and a.assembly_item_no = '" . $_POST['assembly_item_no'] . "' ";
    } 

    $sql .= " ORDER BY item_no   "; //SQL排序
    $result = DB_query($sql, $db);
    if (DB_num_rows($result) == 0) {
        unset($result);
        prnMsg(_('找不到数据，请重新输入条件查询！'), 'error');
    }
}
?>
<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/magnifier.png" title="查询成品料号" alt="查询成品料号">查询成品料号</p>
<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES,
'UTF-8'); ?>" method ="POST">
    <div>
        <input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
        <table cellpadding="3" class="selection">
            <tr>	
                <td>成品图号：</td>
                <td><input  size="10"  type="text" name="ItemNo" value=<?= $_POST['ItemNo'] ?> >
                </td>
				<td>客户图号：</td>
                <td><input  size="10"  type="text" name="tuhao" value=<?= $_POST['tuhao'] ?> > </td>

                <td>成品料号描述：</td>
                <td><input   size="20" type="text" name="ItemDesc" value=<?= $_POST['ItemDesc'] ?> >
                </td> 
				
				
				 </tr> 
				<td>粉组成：</td>
                <td><input   size="10" type="text" name="assembly_item_no" value=<?= $_POST['assembly_item_no'] ?> > </td>
				<td>客户代码：</td>
                <td><input   size="10" type="text" name="customer_code" value=<?= $_POST['customer_code'] ?> > </td>
				<td>客户名称：</td>
                <td><input   size="20" type="text" name="customer_name" value=<?= $_POST['customer_name'] ?> > </td>
            </tr> 
            <tr>
        </table>
        <div class="centre"><input type="submit" name="Search" value="查找"> &nbsp;&nbsp;&nbsp;</div>

        <?php
if (isset($_POST['Search']) and isset($result) or isset($_POST['Go']) or isset($_POST['Next']) or
    isset($_POST['Previous'])) {
    $ListCount = DB_num_rows($result);
    $ListPageMax = ceil($ListCount / 150); //$_SESSION['DisplayRecordsMax']

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
                echo '<option value="' . $ListPage . '" selected="selected">' . $ListPage .
                    '</option>';
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
                    <th class="ascending" width = "100" >成品料号</th>
                    <th class="ascending" width = "200" >成品料号描述</th> 
                    <th class="ascending" width = "100" >图号</th>  
					<th class="ascending" width = "100" >粉组成</th>  
					<th class="ascending" width = "100" >重量</th>  
					<th class="ascending" width = "100" >单位</th>  
                    <th class="ascending" width = "90" >客户代号</th>
					<th class="ascending" width = "300" >客户名称</th>
                </tr>
                <?php
    $k = 0; //row counter to determine background colour
    $RowIndex = 0;
    if (DB_num_rows($result) <> 0) {
        DB_data_seek($result, ($_POST['PageOffset'] - 1) * 150); // $_SESSION['DisplayRecordsMax']
        $i = 0; //counter for input controls
        while (($myrow = DB_fetch_array($result)) and ($RowIndex <> 150)) { //$_SESSION['DisplayRecordsMax']
            if ($k == 1) {
                echo '<tr class="EvenTableRows">';
                $k = 0;
            } else {
                echo '<tr class="OddTableRows">';
                $k = 1;
            }
?> 
                        <td>
                            <?= $myrow['item_no'] ?>
                        </td>
                        <td>   <?= $myrow['item_desc'] ?>
                        </td> 
                        <td>   <?= $myrow['tuhao'] ?> </td> 
						<td>   <?= $myrow['assembly_item_no'] ?> </td>
						<td>   <?= $myrow['weight'] ?> </td>
						<td>   <?= $myrow['units'] ?> </td>
                        <td>
            <?= $myrow['customer_code'] ?>
                        </td>
						     <td>
            <?= $myrow['customer_name'] ?>
                        </td>
                        </tr>
                        <?php
            $i++;
            $RowIndex++;
        }
?>
                </table>
                <?php
    }
    if (isset($ListPageMax) and $ListPageMax > 1) {
?>
                <br />

                <div class="centre">&nbsp;&nbsp;第&nbsp;<?= $_POST['PageOffset'] ?>&nbsp;页，共&nbsp;<?= $ListPageMax ?>&nbsp;页&nbsp;&nbsp; 跳转至页: 
                    <select name="PageOffset2">
                        <?php
        $ListPage = 1;
        while ($ListPage <= $ListPageMax) {
            if ($ListPage == $_POST['PageOffset']) {
                echo '<option value="' . $ListPage . '" selected="selected">' . $ListPage .
                    '</option>';
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
include ('includes/footer.inc');
?>