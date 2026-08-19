<?php
include ('includes/session.inc');
$Title = _('料号修改');

$ViewTopic = '料号修改';
$BookMark = '料号修改';

include ('includes/header.inc');
include ('includes/SQL_CommonFunctions.inc');

if (isset($_GET['ItemID'])) {
	$ItemID = $_GET['ItemID'];
} else if (isset($_POST['ItemID'])) {
	$ItemID = $_POST['ItemID'];
}

// if (!isset($ItemID)) {
// 	header('Location: segment1set.php');
// }

if (isset($_POST['Deletecustomer'])) {
	$CancelDelete = 0;
	$CancelDelete2 = 0;
	$CancelDelete3 = 0;
	$CancelDelete4 = 0;

	$sql2 = "select * FROM po_lines_all WHERE stockid='" . $_POST['ItemNo'] . "'";
	$result2 = DB_query($sql2, $db);
	$CancelDelete = DB_num_rows($result2);

	$sql2 = "select * FROM wip_jobs_all WHERE primary_item='" . $_POST['ItemNo'] . "'";
	$result2 = DB_query($sql2, $db);
	$CancelDelete3 = DB_num_rows($result2);

	$sql2 = "select * FROM so_lines_all WHERE stockid='" . $_POST['ItemNo'] . "'";
	$result2 = DB_query($sql2, $db);
	$CancelDelete2 = DB_num_rows($result2);

	$sql2 = "select * FROM inv_transactions_all WHERE item_no='" . $_POST['ItemNo'] . "'";
	$result2 = DB_query($sql2, $db);
	$CancelDelete4 = DB_num_rows($result2);

	$item_line = $CancelDelete + $CancelDelete2 + $CancelDelete3 + $CancelDelete4;

	if ($item_line == 0) { //ie not cancelled the delete as a result of above tests
		$time = time();

		$sql = "insert into sf_item_no_log(change_type,change_time,item_id,
					so_flag, 		
	item_no,
	item_name,
    item_desc,
	units,
    item_category1,  
	item_type,
	min_order,
	safe_qty,sub_code,sub_locator,
	disable_flag,
	pic_path,
	creation_date,
	created_by,
	last_update_date,
	last_updated_by, 
        lead_time,manufacture_time,
        huohao)
						select '删除','" . $time . "',item_id,
					so_flag, 		
	item_no,
	item_name,
    item_desc,
	units,
    item_category1,  
	item_type,
	min_order,
	safe_qty,sub_code,sub_locator,
	disable_flag,
	pic_path,
	creation_date,
	created_by,
	last_update_date,
	last_updated_by, 
        lead_time,manufacture_time,
        huohao from sf_item_no
                     where  item_no='" . $_POST['ItemNo'] . "'";
		$result = DB_query($sql, $db);

		$sql = "DELETE FROM sf_item_no WHERE item_no='" . $_POST['ItemNo'] . "'";
		$result = DB_query($sql, $db);
		prnMsg(_('料号') . ' ' . $_POST['ItemNo'] . ' ' . _('资料被成功删除') . ' !', 'success');


		echo '<br /><div class="centre"><a href="' . $RootPath . '/segment1Up.php">' . _('查询料号') . '</a></div>';
		include ('includes/footer.inc');
		unset($_SESSION['customer_code']);
		exit;
	} else {
		prnMsg(_('料号') . ' ' . $_POST['ItemNo'] . ' ' . _('已建立订单/工单/采购单,无法再删除') . ' !', 'error');
	}
}


$uploadflag = 1;

if (isset($_POST['Save'])) {
	if (!empty($_FILES["Pic"]["tmp_name"])) {
		if (
			(($_FILES["Pic"]["type"] == "image/gif")
				|| ($_FILES["Pic"]["type"] == "image/jpeg")
				|| ($_FILES["Pic"]["type"] == "image/pjpeg"))
			&& ($_FILES["Pic"]["size"] < 20 * 1024 * 1024)
		) {

			if ($_FILES["Pic"]["error"] > 0) {
				$msg = "错误: " . $_FILES["Pic"]["error"];
				prnMsg($msg, 'error');
				$uploadflag = 2;
			}
			echo $_FILES["Pic"]["type"];
			echo $_FILES["Pic"]["tmp_name"];
			echo $_FILES["Pic"]["type"];
			move_uploaded_file($_FILES["Pic"]["tmp_name"], "itempic/" . $_POST['ItemNo'] . ".jpg");
			$_POST['PicPath'] = "itempic/" . $_POST['ItemNo'] . ".jpg";
		} else {
			$msg = "系统只支持gif,jpeg,pjpeg图片";
			prnMsg($msg, 'error');
			$uploadflag = 2;
		}

	} else {
		$sql = "select pic_path from sf_item_no where item_id ='" . $ItemID . "' ";
		$result = DB_query($sql, $db);
		while ($v = DB_fetch_array($result)) {
			$_POST['PicPath'] = $v['pic_path'];
		}
	}

	if ($uploadflag == 1) {


		if (empty($_POST['MinOty'])) {
			$_POST['MinOty'] = 0;
		}
		if (empty($_POST['SafeQty'])) {
			$_POST['SafeQty'] = 0;
		}
		if (empty($_POST['UnitPrice'])) {
			$_POST['UnitPrice'] = 0;
		}
		if (empty($_POST['franchise_price'])) {
			$_POST['franchise_price'] = 0;
		}
		if (empty($_POST['po_price'])) {
			$_POST['po_price'] = 0;
		}
		if (empty($_POST['SafeQty'])) {
			$_POST['SafeQty'] = 0;
		}
		if (empty($_POST['SafeQty'])) {
			$_POST['SafeQty'] = 0;
		}
		if (empty($_POST['SafeQty'])) {
			$_POST['SafeQty'] = 0;
		}
		if (empty($_POST['lead_time'])) {
			$_POST['lead_time'] = 0;
		}
		if (empty($_POST['manufacture_time'])) {
			$_POST['manufacture_time'] = 0;
		}
		$time = time();

		$sql = "insert into sf_item_no_log(change_type,change_time,item_id,
					so_flag, 		
	item_no,
	item_name,
    item_desc,
	units,
    item_category1,  
	item_type,
	min_order,
	safe_qty,sub_code,sub_locator,
	disable_flag,
	pic_path,
	creation_date,
	created_by,
	last_update_date,
	last_updated_by, 
        lead_time,manufacture_time,
        huohao)
						select '修改','" . $time . "',item_id,
					so_flag, 		
	item_no,
	item_name,
    item_desc,
	units,
    item_category1,  
	item_type,
	min_order,
	safe_qty,sub_code,sub_locator,
	disable_flag,
	pic_path,
	creation_date,
	created_by,
	last_update_date,
	last_updated_by, 
        lead_time,manufacture_time,
        huohao from sf_item_no
                     where  item_id='" . $_POST['ItemID'] . "'";
		$result = DB_query($sql, $db);

			$sql = "update sf_item_no 
                                set min_order='" . $_POST['MinOty'] . "',
                                    safe_qty='" . $_POST['SafeQty'] . "', 
                                    sub_locator='" . $_POST['sub_locator'] . "',
                                    disable_flag='" . $_POST['Flag'] . "',
                                    last_update_date='" . $time . "',
                                    last_updated_by='" . $_SESSION['UserID'] . "' , 
									manufacture_time = '" . $_POST['manufacture_time'] . "' ,
                                    wendu = '" . $_POST['wendu'] . "' ,
									light = '" . $_POST['light'] . "' ,
									conditions = '" . $_POST['conditions'] . "'  ,
									shidu = '" . $_POST['shidu'] . "' ,
									youxiaoqi = '" . $_POST['youxiaoqi'] . "' ,
									project_name = '" . $_POST['project_name'] . "' ,
									item_remark = '" . $_POST['item_remark'] . "' 
									
                              where item_id = '" . $_POST['ItemID'] . "' ";
			$result = DB_query($sql, $db);
		
		
		// echo $sql;

		prnMsg(_('料号更新成功！'), 'success');
	}

}


$sql = "select *,(select count(*) from sf_item_no_file bsa where bsa.item_no=a.item_no ) item_count from sf_item_no a where item_id ='" . $ItemID . "' ";
$sql = $sql . "  order by item_no";
$result = DB_query($sql, $db);
while ($v = DB_fetch_array($result)) {
	$_POST['ItemID'] = $v['item_id'];
	$_POST['ItemNo'] = $v['item_no'];
	$_POST['item_type'] = $v['item_type'];
	$_POST['item_name'] = $v['item_name'];
	$_POST['item_desc'] = $v['item_desc'];
	$_POST['Units'] = $v['units'];
	$_POST['item_category1'] = $v['item_category1'];
	$_POST['MinOty'] = $v['min_order'];
	$_POST['youxiaoqi'] = $v['youxiaoqi'];
	$_POST['item_category1'] = $v['item_category1'];
	$_POST['SafeQty'] = $v['safe_qty'];
	$_POST['PicPath'] = $v['pic_path'];
	$_POST['zhidao_price'] = $v['zhidao_price'];
	$_POST['Flag1'] = $v['so_flag'];
	$_POST['Flag'] = $v['disable_flag'];
	$_POST['lead_time'] = $v['lead_time'];
	$_POST['sub_code'] = $v['sub_code'];
	$_POST['sub_locator'] = $v['sub_locator'];
	$_POST['manufacture_time'] = $v['manufacture_time'];
	$_POST['huohao'] = $v['huohao'];
	$_POST['suoding_flag'] = $v['suoding_flag'];
	$_POST['suoding_remark'] = $v['suoding_remark'];
	$_POST['item_count'] = $v['item_count'];
	$_POST['wendu'] = $v['wendu'];
	$_POST['light'] = $v['light'];
	$_POST['shidu'] = $v['shidu'];
	$_POST['conditions'] = $v['conditions'];
	$_POST['item_use'] = $v['item_use'];
	$_POST['item_remark'] = $v['item_remark'];
	$_POST['project_name'] = $v['project_name'];
	$_POST['inspect_flag'] = $v['inspect_flag'];


	$sql2 = " select * from  sf_item_no_file where item_no='" . $v['item_no'] . "'   ";
	$result2 = DB_query($sql2, $db);

}
?>
<div class="centre"><a href="<?= $RootPath ?>/segment1Up.php">返回查找料号</a></div>
<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/maintenance.png"
		title="料号修改" alt="料号修改">料号修改</p>
<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method="POST"
	enctype="multipart/form-data">
	<div>
		<input type="hidden" name="FormID" value="<?php echo $_SESSION['FormID'];

		?>">
		<br>
		<table class="selection">

			<tr>
			<input type="hidden" name="ItemID" 
						value="<?= $_POST['ItemID'] ?>">
				<td bgcolor="#87CEFA">料号编码：</td>
				<td colspan="1"><input type="text" readonly="readonly" name="ItemNo"  id="text_slect_buliao"
						value="<?= $_POST['ItemNo'] ?>" ><span style="color:red">*</span></td>

				<td bgcolor="#87CEFA">料号名称：</td>
				<td colspan="3"><input type="text"  readonly="readonly" size="70" name="item_name" value="<?= $_POST['item_name'] ?>"
						required="required"><span style="color:red">*</span></td>

			</tr>
			<tr>
				<td bgcolor="#87CEFA">料号类型：</td>
				<td>
               
					
						<?php
						$sql = "select item_type,type_name from sf_item_type order by item_type_id";
						$result = DB_query($sql, $db);
						while ($v = DB_fetch_array($result)) {
							if ($v['item_type'] == $_POST['item_type']) {
								?>
								<input type="text"  readonly="readonly" size="20" name="item_type" value="<?= $v['type_name'] ?>"
						required="required"><span style="color:red">*</span>
							<?php }
						}
						?>
					
				</td>

				<td bgcolor="#87CEFA">料号分类：</td>
				<td>
				
						<?php
						$sql = "select unitname from sf_item_set order by unitid";
						$result = DB_query($sql, $db);
						while ($v = DB_fetch_array($result)) {
							if ($v['unitname'] == $_POST['item_category1']) {
								?>
								<input type="text"  readonly="readonly" size="20" name="item_category1" value="<?= $v['unitname'] ?>"
						required="required"><span style="color:red">*</span>
							<?php }
						}
						?>
					
				</td>
				<td bgcolor="#87CEFA">单位：</td>
				<td>
					
						<?php
						$sql = "select unitname from unitsofmeasure order by unitid";
						$result = DB_query($sql, $db);
						while ($v = DB_fetch_array($result)) {
							if ($v['unitname'] == $_POST['Units']) {
								?>
								<input type="text"  readonly="readonly" size="20" name="Units" value="<?= $v['unitname'] ?>"
						required="required"><span style="color:red">*</span>
								<?php
							}
						}
						?>
					
				</td>

			</tr>
			<tr>

				<td bgcolor="#87CEFA">规格型号：</td>
				<td colspan="1"><input type="text" readonly="readonly" required="required" name="item_desc" 
						value="<?= $_POST['item_desc'] ?>"><span style="color:red">*</span></td>

				<td bgcolor="#87CEFA">是否启用保存条件：</td>

				<td>
					<?php
					if ($_POST['conditions'] == 'N') {
						?>
						<input type="radio" onchange="check(this)"  name="conditions" value='Y'>是
						<input type="radio" onchange="check(this)" name="conditions" value='N' checked=checked>否
						<?php
					} else {
						?>
						<input type="radio" onchange="check(this)" name="conditions" value='Y' checked=checked>是
						<input type="radio" onchange="check(this)"  name="conditions" value='N'>否
						<?php
					}
					?>
				</td>
				<td bgcolor="#87CEFA">默认仓库：</td>
				<td>
				
						<?php
						$sql = "select loccode,locationname from locations  ";
						$result = DB_query($sql, $db);
						while ($v = DB_fetch_array($result)) {
							if ($v['loccode'] == $_POST['sub_code']) {
								?>
								<input type="text"  readonly="readonly" size="20" name="sub_code" value="<?= $v['locationname'] ?>"
						required="required"><span style="color:red">*</span>
							<?php }
						}
						?>
					
				</td>
			</tr>
			<?php if ($_POST['conditions'] == 'N') {
				?>
				<tr id="hidden" style="display:none">
					<td bgcolor="#87CEFA" id="wendu">温度：</td>
					<td id="wendu1">

					<select name="wendu" id="">
										<?php
										$sql = "select wendu from sf_item_wendu  ";
										$result = DB_query($sql, $db);
										while ($v = DB_fetch_array($result)) {
											if ($v['wendu'] == $_POST['wendu']) {
												?>
												<option value="<?= $v['wendu'] ?>" selected="selected"><?= $v['wendu'] ?>
												</option>
											<?php } else { ?>
												<option value="<?= $v['wendu'] ?>"><?= $v['wendu'] ?></option>
											<?php }
										}
										?>
									</select>
					</td>
					<td bgcolor="#87CEFA" id="ligth">是否避光：</td>
					<td id="ligth1">
						<?php
						if ($_POST['light'] == 'N') {
							?>
							<input type="radio" name="light" value='Y'>是
							<input type="radio" name="light" value='N' checked=checked>否
							<?php
						} else {
							?>
							<input type="radio" name="light" value='Y' checked=checked>是
							<input type="radio" name="light" value='N'>否
							<?php
						}
						?>
					</td>
					<td bgcolor="#87CEFA" id="shidu">湿度范围：</td>
					<td id="shidu1">

					<select name="shidu" id="" >
										<?php
										$sql = "select shidu from sf_item_shidu  ";
										$result = DB_query($sql, $db);
										while ($v = DB_fetch_array($result)) {
											if ($v['shidu'] == $_POST['shidu']) {
												?>
												<option value="<?= $v['shidu'] ?>" selected="selected"><?= $v['shidu'] ?>
												</option>
											<?php } else { ?>
												<option value="<?= $v['shidu'] ?>"><?= $v['shidu'] ?></option>
											<?php }
										}
										?>
									</select>
					</td>
				</tr>
				<?php
			} else {
				?>
				<tr id="hidden">
					<td bgcolor="#87CEFA" id="wendu">温度：</td>
					<td id="wendu1"><select name="wendu" id="">
										<?php
										$sql = "select wendu from sf_item_wendu  ";
										$result = DB_query($sql, $db);
										while ($v = DB_fetch_array($result)) {
											if ($v['wendu'] == $_POST['wendu']) {
												?>
												<option value="<?= $v['wendu'] ?>" selected="selected"><?= $v['wendu'] ?>
												</option>
											<?php } else { ?>
												<option value="<?= $v['wendu'] ?>"><?= $v['wendu'] ?></option>
											<?php }
										}
										?>
									</select></td>
					<td bgcolor="#87CEFA" id="ligth">是否避光：</td>
					<td id="ligth1">
						<?php
						if ($_POST['light'] == 'N') {
							?>
							<input type="radio" name="light" value='Y'>是
							<input type="radio" name="light" value='N' checked=checked>否
							<?php
						} else {
							?>
							<input type="radio" name="light" value='Y' checked=checked>是
							<input type="radio" name="light" value='N'>否
							<?php
						}
						?>
					</td>
					<td bgcolor="#87CEFA" id="shidu">湿度范围：</td>
					<td id="shidu1"><select name="shidu" id="" >
										<?php
										$sql = "select shidu from sf_item_shidu  ";
										$result = DB_query($sql, $db);
										while ($v = DB_fetch_array($result)) {
											if ($v['shidu'] == $_POST['shidu']) {
												?>
												<option value="<?= $v['shidu'] ?>" selected="selected"><?= $v['shidu'] ?>
												</option>
											<?php } else { ?>
												<option value="<?= $v['shidu'] ?>"><?= $v['shidu'] ?></option>
											<?php }
										}
										?>
									</select></td>
				</tr>
				<?php
			}
			?>


			<tr>
				<td bgcolor="#87CEFA">料号用途：</td>
				<td>
				
						<?php
						$sql = "select item_type,type_name from sf_item_use order by item_type_id";
						$result = DB_query($sql, $db);
						while ($v = DB_fetch_array($result)) {
							if ($v['item_type'] == $_POST['item_use']) {
								?>
								<input type="text"  readonly="readonly" size="20" name="item_use" value="<?= $v['type_name'] ?>"
						required="required"><span style="color:red">*</span>
							<?php }
						}
						?>
					
				</td>
				<td bgcolor="#87CEFA">可出售：</td>
				<td>
					<?php
					if ($_POST['Flag1'] == 'N') {
						?>
						<input type="radio" name="Flag1" value='Y'>是
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
				<td bgcolor="#87CEFA">有效期(天)：</td>
							<td><input  type="text" class="number" name="youxiaoqi"
									value="<?= $_POST['youxiaoqi'] ?>"></td>
			</tr>

			<tr>
				<td bgcolor="#87CEFA">货号：</td>
				<td><input type="text" name="huohao" value="<?= $_POST['huohao'] ?>"></td>

				<td bgcolor="#87CEFA">最小订单量：</td>
				<td><input type="text" class="number" name="MinOty" value="<?= $_POST['MinOty'] ?>"></td>
				<td bgcolor="#87CEFA">采购周期(天)：</td>
				<td><input type="text" class="number" name="manufacture_time" value="<?= $_POST['manufacture_time'] ?>">
				</td>

			</tr>

			<tr>
				<td bgcolor="#87CEFA">安全库存：</td>
				<td><input type="text" name="SafeQty" value="<?= $_POST['SafeQty'] ?>"></td>
				</td>


				<td bgcolor="#87CEFA">库位：</td>
				<td><input type="text" name="sub_locator" value="<?= $_POST['sub_locator'] ?>"></td>






				<td bgcolor="#87CEFA">是否生效：</td>
				<td>
					<?php
					if ($_POST['Flag'] == 'N') {
						?>
						<input type="radio" name="Flag" value='Y'>是
						<input type="radio" name="Flag" value='N' checked=checked>否
						<?php
					} else {
						?>
						<input type="radio" name="Flag" value='Y' checked=checked>是
						<input type="radio" name="Flag" value='N'>否
						<?php
					}
					?>
				</td>
				
			</tr>

<tr>
<?php 
				if ($_POST['item_use']=='Y'){
					?>
<td bgcolor="#87CEFA"  id="project_name">项目名称：</td>
								<td id="project_name1" >
									<input type="text"  readonly="readonly" size="20" name="project_name" value="<?= $v['project_name'] ?>" required="required">	
								</td>
					<?php
				}else{
					?>
					
									<td bgcolor="#87CEFA" style="display: none;" id="project_name">项目名称：</td>
								<td id="project_name1" style="display: none;">
							
								<input type="text"  readonly="readonly" size="20" name="project_name" value="<?= $v['project_name'] ?>" required="required">
								</td>
					<?php
				}

				?>
				<td bgcolor="#87CEFA">备注：</td>
								<td colspan="3"><input  size="70"  type="text" name="item_remark" value="<?= $_POST['item_remark'] ?>"></td>

				<!-- <td bgcolor="#87CEFA">客户简称:</td>
								<td><input type="text" id="text_slect_customer" name="customer_code"
										value="<?= $_POST['customer_code'] ?>" size="10" maxlength="25" />
									<a class="btn btn-info btn-xs" id="btn_slect_customer" hfre="###" title="选择客户">选</a>
								</td>
								<td bgcolor="#87CEFA">客户名称:</td>
								<td><input type="text" id="text_slect_name" name="customer_name"
										value="<?= $_POST['customer_name'] ?>" size="20" maxlength="25" />

								</td> -->
			</tr>

		</table>
		<table>
			<?php

			// while ($v2 = DB_fetch_array($result2)) { 
			?>
			<!-- <tr> 
	 <td colspan="2" align="center" ><div style="width:200px; height:200px;">
	 <img src="<?= $v2['file_patch'] ?>" alt="料号图片" width="100%" height="100%"></div>
	 </td>
	  </tr>  -->

			<?php
			//}
			
			?>
		</table>


	</div>
	<script type="text/javascript">
	function check(radio) {

		var hidden = document.getElementById('hidden');
		console.log(radio.value);
		if (radio.value === 'Y') {
			hidden.style.display = '';

		} else {
			hidden.style.display = 'none';

		}

	};




</script>
	<div class="centre">
		<input type="submit" name="Save" value="保存">
		<!-- <input type="submit" name="Deletecustomer" value="删除料号"
			onclick="return confirm(\'' . _('Are You Sure?') . '\');" /> -->
	</div>
</form>


<?php
include ('includes/footer.inc');
?>