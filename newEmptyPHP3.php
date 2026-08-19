<?php 
	include('includes/session.inc');
	$Title = _('请购单建立');
	$ViewTopic= '请购单建立';
	$BookMark = '请购单建立';
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
	    $sql = ' select order_id,
                            order_number,
                            purchase_qty,
                            purchase_amount,
                            supplier_name,
                            customer_name,
                            customer_number
                       from sf_orders_all a where 1=1 ';
	   
	   //SQL添加条件
		if (isset($_POST['']) and $_POST['']!= '') {
			$sql = $sql." and  like '%".$_POST['']."%' ";
		}
	    $sql .= " ORDER BY  order_number desc ";//SQL排序
	    $result = DB_query($sql, $db);
	    if (DB_num_rows($result) == 0) {
	        unset($result);
	        prnMsg(_('找不到数据，请重新输入条件查询！'), 'error');
	    }
	}
?>
<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="标题" alt="标题">标题</p>
<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST">
<div>
	<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
	<table cellpadding="3" class="selection">
		<tr>	
			<td>料号起：</td>
			<td><input   type="text" name="order_number" value=<?=$_POST['']?> >
			</td>

			<td>料号迄：</td>
			<td><input   type="text" name="customer_name" value=<?=$_POST['']?> >
			</td> 
		</tr>
	</table>
	<div class="centre"><input type="submit" name="Search" value="查找"></div>

<?php
	if (isset($_POST['Search']) and isset($result) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])) {
    $ListCount = DB_num_rows($result);
    $ListPageMax = ceil($ListCount / 10);//$_SESSION['DisplayRecordsMax']

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
<input type="hidden" name="PageOffset" value=<?=$_POST['PageOffset']?> >

<?php
	if ($ListPageMax > 1) {
?>
<br />

<div class="centre">&nbsp;&nbsp;第&nbsp;<?=$_POST['PageOffset']?>&nbsp;页，共&nbsp;<?=$ListPageMax?>&nbsp;页&nbsp;&nbsp; 跳转至页: 
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
		<th class="ascending" width = "60" >确认</th>
                <th width =100>料号</th>
                <th  width =250>描述</th>
                <th width =100 >单位</th>
                <th  width =100>数量</th>
                <th width =250 >备注</th>
   </tr>
<?php
	$k = 0; //row counter to determine background colour
    $RowIndex = 0;
    if (DB_num_rows($result) <> 0) {
        DB_data_seek($result, ($_POST['PageOffset'] - 1) * 10);// $_SESSION['DisplayRecordsMax']
        $i = 0; //counter for input controls
        while (($myrow = DB_fetch_array($result)) AND ($RowIndex <> 10)) {//$_SESSION['DisplayRecordsMax']
            if ($k == 1) {
                echo '<tr class="EvenTableRows">';
                $k = 0;
            } else {
                echo '<tr class="OddTableRows">';
                $k = 1;
            }
?>
<td>
    <input type="checkbox" name="status' . $myrow['order_id'] . '" />

</td>
<td>
    <?=$myrow['order_number']?>
    <!-- <a href="<?=$RootPath?>/跳转页面.php?参数='<?=$myrow['customer_name']?>'"><?=$myrow['customer_name']?>-->
</td>
<td>
    <?=$myrow['customer_name']?>
</td>
<td>
    <?=$myrow['purchase_qty']?>
</td>
<td>
    <?=$myrow['purchase_qty']?>
</td>
<td>
    <?=$myrow['purchase_qty']?>
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
	if (isset($ListPageMax) AND $ListPageMax > 1) {
?>
<br />

<div class="centre">&nbsp;&nbsp;第&nbsp;<?=$_POST['PageOffset']?>&nbsp;页，共&nbsp;<?=$ListPageMax?>&nbsp;页&nbsp;&nbsp; 跳转至页: 
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
<br>
<div><input type="submit" name="submit" value="确认" /></div>
<?php }} ?>

</div>
</form>
<?php
  include('includes/footer.inc');
?>