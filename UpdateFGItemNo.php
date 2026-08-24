<?php 
	include('includes/session.inc');
	$Title = _('修改成品料号');
	$ViewTopic= '修改成品料号';
	$BookMark = '修改成品料号';

	include('includes/header.inc');
	include('includes/SQL_CommonFunctions.inc');

	if (isset($_GET['ItemID'])) {
		$ItemID = $_GET['ItemID'];
	}else if(isset($_POST['ItemID'])){
		$ItemID = $_POST['ItemID'];
	}

	if (!isset($ItemID)) {
		header('Location: SearchFGItemNo.php');
	}

	if (isset($_POST['Deletecustomer'])) {
      $CancelDelete = 0;
	 $sql2 = "select * FROM po_lines_all WHERE stockid='" . $_POST['ItemNo'] . "'";
     $result2 = DB_query($sql2, $db);
	 $CancelDelete = DB_num_rows($result2);

	 $sql2 = "select * FROM so_lines_all WHERE stockid='" . $_POST['ItemNo'] . "'";
     $result2 = DB_query($sql2, $db);
	 $CancelDelete2 = DB_num_rows($result2);
	 $item_line= $CancelDelete +$CancelDelete2;
   
    if ($item_line == 0 ) { //ie not cancelled the delete as a result of above tests
        $sql = "DELETE FROM sf_item_no WHERE item_no='" . $_POST['ItemNo'] . "'";
        $result = DB_query($sql, $db);
        prnMsg(_('料号') . ' ' . $_POST['ItemNo'] . ' ' . _('资料被成功删除') . ' !', 'success');
        
       
        echo '<br /><div class="centre"><a href="' . $RootPath . '/SearchFGItemNo.php">' . _('查询料号') . '</a></div>';
        include('includes/footer.inc');
        unset($_SESSION['customer_code']);
        exit;
    } else {
	prnMsg(_('料号') . ' ' . $_POST['ItemNo'] . ' ' . _('已建立订单,无法再删除') . ' !', 'error');
	}
}



		$uploadflag = 1;

	if (isset($_POST['Save'])) {
		if (!empty($_FILES["Pic"]["tmp_name"])) {
			if ((($_FILES["Pic"]["type"] == "image/gif")
				|| ($_FILES["Pic"]["type"] == "image/jpeg")
				|| ($_FILES["Pic"]["type"] == "image/pjpeg"))
				&& ($_FILES["Pic"]["size"] < 20*1024*1024)){
				  if ($_FILES["Pic"]["error"] > 0){
				    $msg = "错误: " . $_FILES["Pic"]["error"];
				    prnMsg( $msg, 'error');
				    $uploadflag = 2;
				  }
			}
			else
			{
			  $msg = "系统只支持gif,jpeg,pjpeg图片";
			  prnMsg( $msg, 'error');
			  $uploadflag = 2;
			}
			$_POST['PicPath'] = "itempic/" . $_POST['ItemNo'] .".jpg";
		}else{
			$sql = "select pic_path from sf_item_no where item_id ='".$ItemID."' ";
			$result = DB_query($sql,$db);
			while ($v = DB_fetch_array($result)) {
				$_POST['PicPath'] = $v['pic_path'];
			}
		}

		if ($uploadflag == 1) {
			move_uploaded_file($_FILES["Pic"]["tmp_name"],"itempic/" . $_POST['ItemNo'] .".jpg");

			if (empty($_POST['MinOty'])) {
          $_POST['MinOty'] =0;
       }	
      if (empty($_POST['SafeQty'])) {
          $_POST['SafeQty'] =0;
       }
      if (empty($_POST['UnitPrice'])) {
          $_POST['UnitPrice'] =0;
       }
      if (empty($_POST['franchise_price'])) {
          $_POST['franchise_price'] =0;
       }
       if (empty($_POST['po_price'])) {
          $_POST['po_price'] =0;
       }
      if (empty($_POST['SafeQty'])) {
          $_POST['SafeQty'] =0;
       }	
       if (empty($_POST['SafeQty'])) {
          $_POST['SafeQty'] =0;
       }
      if (empty($_POST['SafeQty'])) {
          $_POST['SafeQty'] =0;
       }	   
	   if (empty($_POST['lead_time'])) {
          $_POST['lead_time'] =0;
       }
      if (empty($_POST['manufacture_time'])) {
          $_POST['manufacture_time'] =0;
       }	
			$time = time();
			$sql = "update sf_item_no 
                                set item_name='".$_POST['item_name']."',
                                    units='".$_POST['Units']."',
                                    min_order='".$_POST['MinOty']."',
                                    safe_qty='".$_POST['SafeQty']."',
                                    pic_path='".$_POST['PicPath']."',
				                    gongyi='".$_POST['gongyi']."',
                                    po_price='".$_POST['po_price']."',
									so_flag='".$_POST['Flag1']."',
                                    item_category1='".$_POST['item_category1']."',
                                    disable_flag='".$_POST['Flag']."',
                                    last_update_date='".$time."',
                                    last_updated_by='".$_SESSION['UserID']."' ,
                                    item_desc = '".$_POST['item_desc']."' ,
                                    lead_time = '".$_POST['lead_time']."' ,
									manufacture_time = '".$_POST['manufacture_time']."' ,
                                    yanse = '".$_POST['yanse']."' 
                              where item_id = '".$_POST['ItemID']."' ";
			$result = DB_query($sql,$db);

			prnMsg( _('料号更新成功！'), 'success');
			unset($_POST);
		}

	}

	$sql = "select item_type,item_id,item_desc,so_flag,gongyi,yanse,lead_time,manufacture_time,item_no,item_name,units,min_order,safe_qty,  pic_path,po_price,item_category1,disable_flag,(select zhidao_price from bom_headers_all b where a.item_no=b.assembly_item_no) zhidao_price from sf_item_no a where item_id ='".$ItemID."' ";
        $sql = $sql."  order by item_no";
	$result = DB_query($sql,$db);
	while ($v = DB_fetch_array($result)) {
		$_POST['ItemID'] = $v['item_id'];
		$_POST['ItemNo'] = $v['item_no'];
		$_POST['item_type'] = $v['item_type'];
		$_POST['item_name'] = $v['item_name'];
		$_POST['Units'] = $v['units'];
		$_POST['MinOty'] = $v['min_order'];
		$_POST['SafeQty'] = $v['safe_qty'];
		$_POST['PicPath'] = $v['pic_path'];
		$_POST['gongyi'] = $v['gongyi'];
        $_POST['zhidao_price'] = $v['zhidao_price'];
		$_POST['Flag1'] = $v['so_flag']; 
		$_POST['item_category1'] = $v['item_category1']; 
		$_POST['Flag'] = $v['disable_flag'];
        $_POST['item_desc'] = $v['item_desc'];
		$_POST['lead_time'] = $v['lead_time'];
		$_POST['manufacture_time'] = $v['manufacture_time'];
		$_POST['yanse'] = $v['yanse'];
                
	}
?>
<div class="centre"><a href="<?=$RootPath?>/SearchFGItemNo.php">返回查找料号</a></div>
<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/maintenance.png" title="修改成品料号" alt="修改成品料号">修改成品料号</p>
<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST" enctype="multipart/form-data">
<div>
	<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
<br>
<table class="selection">
	<tr>
		<td>料号：</td>
		<td><?=$_POST['ItemNo']?>
			<input type="hidden" name="ItemNo" value="<?=$_POST['ItemNo']?>">
			<input type="hidden" name="ItemID" value="<?=$_POST['ItemID']?>">
		</td>
	</tr>
	<tr>
		<td>料号名称：</td>
		<td colspan="4"> <input type="text" size="70" name="item_name" value="<?=$_POST['item_name']?>" required="required"></td>
	</tr>
	<tr>
	 <td>规格型号</td>
                <td colspan="4"><input size="70" type="text" name="item_desc" value="<?= $_POST['item_desc'] ?>"  ></td>

      </tr>
	<tr>
	  <td>工艺</td>
                <td colspan="4"><input type="text" size="70" name="gongyi" value="<?= $_POST['gongyi'] ?>" ></td>
		</tr>
	<tr>
	<td>单位：</td>
		<td>
			<select name="Units" id="">
				<?php
					$sql = "select unitname from unitsofmeasure order by unitid";
					$result = DB_query($sql,$db);
					while ($v = DB_fetch_array($result)) {
						if ($v['unitname']==$_POST['Units']) {
				?>
					<option value="<?=$v['unitname']?>" selected="selected"><?=$v['unitname']?></option>
				<?php }else{?>
				<option value="<?=$v['unitname']?>"><?=$v['unitname']?></option>
				<?php		}
					}
				?>
			</select>
		</td> 
                <td>安全库存：</td>
                <td><input type="text" name="SafeQty" value="<?= $_POST['SafeQty'] ?>"  ></td>
            </tr>
			
			
			<tr>
			                <td>可出售：</td>
			                <td>
			<?php
			if ($_POST['Flag1'] == 'N') {
			    ?>
			                        <input type="radio" name="Flag1" value='Y' >是
			                        <input type="radio" name="Flag1" value='N' checked=checked>否
			                        <?php
			                    } else {
			                        ?>
			                        <input type="radio" name="Flag1" value='Y' checked=checked>是
			                        <input type="radio" name="Flag1" value='N'>否
			                        <?php
			                    }
			                    ?>
			                </td>
			            </tr>
			
			<tr>
              <td>料号分类：</td>
		<td>
			<select name="item_category1" id="">
				<?php
					$sql = "select unitname from sf_item_category order by unitid";
					$result = DB_query($sql,$db);
					while ($v = DB_fetch_array($result)) {
						if ($v['unitname']==$_POST['item_category1']) {
				?>
					<option value="<?=$v['unitname']?>" selected="selected"><?=$v['unitname']?></option>
				<?php }else{?>
				<option value="<?=$v['unitname']?>"><?=$v['unitname']?></option>
				<?php		}
					}
				?>
			</select>
		</td> 
        
            </tr>
             
             <tr>
			  <td>指导价单价</td>
                <td><input type="text" name="zhidao_price" value="<?= $_POST['zhidao_price'] ?>"  ></td>
                
				 <td>生产周期</td>
                <td><input type="text" name="manufacture_time" value="<?= $_POST['manufacture_time'] ?>"  ></td>
				<td>最小订单量：</td>
                <td><input type="text" class="number" name="MinOty" value="<?= $_POST['MinOty'] ?>"  ></td> 
            </tr>
     
	<tr>
		<td>上传图片：</td>
		<td><input type="file" name="Pic"></td>
	</tr>
	<tr>
		<td colspan="2" align="center" ><div style="width:100px; height:100px;"><img src="<?=$_POST['PicPath']?>" alt="料号图片" width="100%" height="100%"></div></td>
	</tr>
	<tr>
		<td>是否生效：</td>
		<td>
			<?php
				if ($_POST['Flag']=='N') {
			?>
			<input type="radio" name="Flag" value='Y' >是
			<input type="radio" name="Flag" value='N' checked=checked>否
			<?php
				}else{ 
			?>
			<input type="radio" name="Flag" value='Y' checked=checked>是
			<input type="radio" name="Flag" value='N'>否
			<?php
				}
			?>
		</td>
	</tr>
</table>
</div>
<div class="centre">
	<input type="submit" name="Save" value="保存" >
	
	<input type="submit" name="Deletecustomer" value="删除料号" onclick="return confirm(\'' . _('Are You Sure?') . '\');" />
</div>
</form>
<?php
  include('includes/footer.inc');
?>