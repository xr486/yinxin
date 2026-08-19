<?php
 ob_start();
include('includes/session.inc');
$Title = _('料号查询');
$ViewTopic = '料号查询';
$BookMark = '料号查询';
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
    $sql = "select a.*,(select ifnull(sum(quantity),0) from inv_onhand_quantity_all bb where bb.stockid=a.item_no ) onhand_quantity ,
	  (select type_name from sf_item_type b where a.item_type=b.item_type )  type_name
	from sf_item_no a  where  1=1 ";

    //SQL添加条件
    if (isset($_POST['ItemNo']) and $_POST['ItemNo'] != '') {
        $sql = $sql . " and item_no like '%" . $_POST['ItemNo'] . "%' ";
    }
    if (isset($_POST['item_name']) and $_POST['item_name'] != '') {
        $sql = $sql . " and item_name like '%" . $_POST['item_name'] . "%' ";
    }
	if (isset($_POST['item_desc']) and $_POST['item_desc'] != '') {
        $sql = $sql . " and item_desc like '%" . $_POST['item_desc'] . "%' ";
    }
     if (isset($_POST['item_category1']) and $_POST['item_category1'] != '') { 
		$sql = $sql . " and item_category1 like '%" . $_POST['item_category1'] . "%' ";
    }
	 
	  if(isset($_POST['locName'])and $_POST['locName'] != '' and $_POST['locName'] != '全部'){
        $sql = $sql." and a.sub_code ='".$_POST['locName']."'";
    }
    
     
    $sql .= " ORDER BY item_no   "; //SQL排序
	  
    $result = DB_query($sql, $db);
    if (DB_num_rows($result) == 0) {
        unset($result);
        prnMsg(_('找不到数据，请重新输入条件查询！'), 'error');
    }
}




?>


    

<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/magnifier.png" title="料号查询" alt="料号查询">料号查询</p>
<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST">
    <div>
        <input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
        <table cellpadding="3" class="selection">

        <div class="text-nav">	 
					<div class="text-nav-1 ">
						<div>
						料号：
						</div>
							<input type="text" name="ItemNo" value="<?=$_POST['ItemNo']?>">
						</div>
                        <div class="text-nav-1 ">
						<div>
						料号名称：
						</div>
							<input type="text" name="item_name" value="<?=$_POST['item_name']?>">
						</div>

                        <div class="text-nav-1 "> <div> 规格型号： </div>
							<input type="text" name="item_desc" value="<?=$_POST['item_desc']?>">
						</div>
						 <div class="text-nav-1 "> <div> 产品类别： </div>
							<input type="text" name="item_category1" value="<?=$_POST['item_category1'] ?>">
						</div>
<?php
echo '<div class="text-nav-1"><div>' . _('仓库') . ':</div>';
 echo '<select name="locName">  '; 
   $sql = "select '全部' loccode, '全部' locationname from dual union SELECT loccode,locationname FROM locations where managed='Y'";
   
    $result1 = DB_query($sql, $db);     
    while ($Salesmanrow = DB_fetch_array($result1)) {		
		if ($Salesmanrow['loccode']==$_POST['locName'] ) {
			 echo ' <option value=' . $Salesmanrow['loccode']  . ' selected="selected">  '.  $Salesmanrow['locationname'] . ' 
            </option>    ';  
		} 
		 else {
       echo ' <option value=' . $Salesmanrow['loccode']  . ' >  '.  $Salesmanrow['locationname'] . ' 
            </option>    ';
			 
		 } 
		 
	}
   
	echo ' </select> </div>';
?>

                         
							
 
        </div>   
        </table>
        <div class="centre"><input type="submit" name="Search" value="查询">&nbsp;&nbsp;
	</div>

        <?php
        if (isset($_POST['Search']) and isset($result) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])) {
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
    
                       

            <br /><div style="overflow:scroll">
            <table cellpadding="2" class="selection">
                <tr>
                    <th class="ascending"   >料号</th>
                    <th class="ascending"   >料号名称</th>
                    <th class="ascending"   >规格型号</th>
                    <th class="ascending"   >单位</th>
                    <th  >最小订单量</th>
                    <th   >安全库存</th>
					<th   >生产周期</th>
					<th class="ascending"   >料号类型</th> 
					<th class="ascending"   >料号分类</th> 
					<!-- <th class="ascending"   >默认仓库</th>  -->
					<th   >库位</th> 
					<th >库存量</th> 
					<th >历史</th> 
                </tr>
                <?php
                $k = 0; //row counter to determine background colour
                $RowIndex = 0;
                if (DB_num_rows($result) <> 0) {
                    DB_data_seek($result, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']); // $_SESSION['DisplayRecordsMax']
                    $i = 0; //counter for input controls
                    while (($myrow = DB_fetch_array($result)) AND ( $RowIndex <> $_SESSION['DisplayRecordsMax'])) {//$_SESSION['DisplayRecordsMax']
                        if ($k == 1) {
                            echo '<tr class="EvenTableRows">';
                            $k = 0;
                        } else {
                            echo '<tr class="OddTableRows">';
                            $k = 1;
                        }



                        ?>
						<td>
                            <a href="<?= $RootPath ?>/Item_No2.php?ItemID=<?= $myrow['item_id'] ?>&ItemNo=<?= $myrow['item_no']?>"><?= $myrow['item_no'] ?>
                        </td> 
                        <td><?= $myrow['item_name'] ?>   </td>
                        <td><?= $myrow['item_desc'] ?> </td>
                        <td>  <?= $myrow['units'] ?> </td>
                        <td><?= $myrow['min_order'] ?></td>
                        <td><?= $myrow['safe_qty'] ?></td>
						<td><?= $myrow['manufacture_time'] ?></td>
						<td><?= $myrow['type_name'] ?></td>
						<td><?= $myrow['item_category1'] ?></td> 
						<!-- <td><?= $myrow['sub_code'] ?></td>   -->
						<td><?= $myrow['sub_locator'] ?></td>    
						<td><?= $myrow['onhand_quantity'] ?></td> 
						<td>
                            <a href="<?= $RootPath ?>/Item_No3.php?ItemID=<?= $myrow['item_no'] ?>" target="_blank">历史
                        </td> 
                        </tr>
                        <?php
                        $i++;
                        $RowIndex++;
                    }
                    ?>
                </table></div>
                <?php
						echo '<div>
        <a href="' . $RootPath . '/segment1setExcel.php?item_no=' .$_POST['ItemNo'] .
        '&item_name=' .$_POST['item_name'] .'&item_desc=' .$_POST['item_desc'] .'&item_category1=' .$_POST['item_category1'] .'&locName=' .$_POST['locName'] .' ">' .'资料导出Excel表' . '</a>
    </div>';

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
    header('Location: AddItemNo.php');
}
include('includes/footer.inc');
?>
