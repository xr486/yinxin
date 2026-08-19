<?php
include ('includes/session.inc');
$Title = _('料号审核');

$ViewTopic = '料号审核';
$BookMark = '料号审核';

include ('includes/header.inc');
include ('includes/SQL_CommonFunctions.inc');

if (isset($_GET['ItemID'])) {
	$ItemID = $_GET['ItemID'];
} else if (isset($_POST['ItemID'])) {
	$ItemID = $_POST['ItemID'];
}
if (isset($_GET['ItemNo'])) {
	$item_no = $_GET['ItemNo'];
} else if (isset($_POST['ItemNo'])) {
	$item_no = $_POST['ItemNo'];
}

// if (!isset($ItemID)) {
// 	header('Location: segment1set.php');
// }

if (isset($_POST['Deletecustomer'])) {
	$sql = "update sf_item_no 
                                set 
                                   	 item_status = '已拒签'
                              where item_id = '" . $_POST['ItemID'] . "' ";
			$result = DB_query($sql, $db);


		prnMsg(_('料号') . ' ' . $_POST['ItemNo'] . ' ' . _('资料被成功拒绝') . ' !', 'success');
	
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

		
	
			$sql = "update sf_item_no 
                                set 
                                   	 item_status = '已签核'
                                   
                              where item_id = '" . $_POST['ItemID'] . "' ";
			$result = DB_query($sql, $db);


		// echo $sql;

		prnMsg(_('料号审核成功！'), 'success');
	}
    header('Location: segment1Approve.php');
}


$sql = "select *,(select count(*) from sf_item_no_file bsa where bsa.item_no=a.item_no ) item_count,(select type_name from sf_item_type b where b.item_type = a.item_type ) type_name,(select type_name from sf_item_use c where c.item_type = a.item_use ) item_use from sf_item_no a where item_id ='" . $ItemID . "' ";
$sql = $sql . "  order by item_no";
$result = DB_query($sql, $db);
while ($v = DB_fetch_array($result)) {
	$_POST['ItemID'] = $v['item_id'];
	$_POST['ItemNo'] = $v['item_no'];
	$_POST['type_name'] = $v['type_name'];
	$_POST['item_name'] = $v['item_name'];
	$_POST['item_desc'] = $v['item_desc'];
	$_POST['Units'] = $v['units'];
	$_POST['item_category1'] = $v['item_category1'];
	$_POST['MinOty'] = $v['min_order'];


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
	$_POST['youxiaoqi'] = $v['youxiaoqi'];
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

	$sql2 = " select * from  sf_item_no_file where item_no='" . $v['item_no'] . "'   ";
	$result2 = DB_query($sql2, $db);

}
?>
<div class="centre"><a href="<?= $RootPath ?>/segment1Approve.php">返回查找料号</a></div>
<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/maintenance.png"
		title="料号审核" alt="料号审核">料号审核</p>
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
				<td colspan="1"><input type="text" name="ItemNo" size="30" id="text_slect_buliao"
						value="<?= $_POST['ItemNo'] ?>" readonly="readonly"></td>

				<td bgcolor="#87CEFA">料号名称：</td>
				<td colspan="3"><input type="text" size="70" name="item_name" value="<?= $_POST['item_name'] ?>"
				readonly="readonly"></td>

			</tr>
			<tr>
				<td bgcolor="#87CEFA">料号类型：</td>
				<td>
				<input type="text" name="item_type" size="30" id="text_slect_item_type" value="<?= $_POST['type_name'] ?>" readonly="readonly">
					
				</td>

				<td bgcolor="#87CEFA">料号分类：</td>
				<td>
				<input type="text" name="item_category1"  id="text_slect_item_category1" value="<?= $_POST['item_category1'] ?>" readonly="readonly">

					
				</td>
				<td bgcolor="#87CEFA">单位：</td>
				<td>
				<input type="text" name="Units" size="30" id="text_slect_Units" value="<?= $_POST['Units'] ?>" readonly="readonly">

					
					
				</td>

			</tr>
			<tr>

				<td bgcolor="#87CEFA">规格型号：</td>
				<td colspan="1"><input type="text" readonly="readonly" name="item_desc" size="30" value="<?= $_POST['item_desc'] ?>"></td>

				<td bgcolor="#87CEFA">是否启用保存条件：</td>

				<td>
				<input type="text" name="conditions"  id="text_slect_conditions" value="<?= $_POST['conditions'] ?>" readonly="readonly">

					
				</td>
				<td bgcolor="#87CEFA">默认仓库：</td>
				<td>
				<input type="text" name="sub_code" size="30" id="text_slect_sub_code" value="<?= $_POST['sub_code'] ?>" readonly="readonly">

					
				</td>
			</tr>
			<?php if ($_POST['conditions'] == 'N') {
				?>
				<tr id="hidden" style="display:none">
					<td bgcolor="#87CEFA" id="wendu">温度：</td>
					<td id="wendu1"><input type="text" name="wendu" size="30" readonly="readonly" value="<?= $_POST['wendu'] ?>"></td>
					<td bgcolor="#87CEFA" id="ligth">是否避光：</td>
					<td id="ligth1">
				<input type="text" name="light"  id="text_slect_light" value="<?= $_POST['light'] ?>" readonly="readonly">

						
					</td>
					<td bgcolor="#87CEFA" id="shidu">湿度范围：</td>
					<td id="shidu1"><input type="text" size="30" name="shidu" readonly="readonly" value="<?= $_POST['shidu'] ?>"></td>
				</tr>
				<?php
			} else {
				?>
				<tr id="hidden">
					<td bgcolor="#87CEFA" id="wendu">温度：</td>
					<td id="wendu1"><input type="text" name="wendu" size="30" readonly="readonly" value="<?= $_POST['wendu'] ?>"></td>
					<td bgcolor="#87CEFA" id="ligth">是否避光：</td>
					<td id="ligth1">
				<input type="text" name="light"  id="text_slect_light" value="<?= $_POST['light'] ?>" readonly="readonly">

						
					</td>
					<td bgcolor="#87CEFA" id="shidu">湿度范围：</td>
					<td id="shidu1"><input type="text" size="30" name="shidu" readonly="readonly" value="<?= $_POST['shidu'] ?>"></td>
				</tr>
				<?php
			}
			?>


			<tr>
				<td bgcolor="#87CEFA">料号用途：</td>
				<td>
				<input type="text" name="item_use" size="30" id="text_slect_item_use" value="<?= $_POST['item_use'] ?>" readonly="readonly">

					
				</td>
				<td bgcolor="#87CEFA">可出售：</td>
				<td>
				<input type="text" name="Flag1"  id="text_slect_Flag1" value="<?= $_POST['Flag1'] ?>" readonly="readonly">

					
				</td>
				<td bgcolor="#87CEFA">有效期(月)：</td>
				<td><input type="text" name="youxiaoqi" size="30" readonly="readonly" value="<?= $_POST['youxiaoqi'] ?>"></td>
			</tr>

			<tr>
				<td bgcolor="#87CEFA">货号：</td>
				<td><input type="text" name="huohao" size="30" readonly="readonly" value="<?= $_POST['huohao'] ?>"></td>

				<td bgcolor="#87CEFA">最小订单量：</td>
				<td><input type="text" class="number" name="MinOty" readonly="readonly" value="<?= $_POST['MinOty'] ?>"></td>
				<td bgcolor="#87CEFA">采购周期(天)：</td>
				<td><input type="text" class="number" size="30" name="manufacture_time" readonly="readonly" value="<?= $_POST['manufacture_time'] ?>">
				</td>

			</tr>

			<tr>
				<td bgcolor="#87CEFA">安全库存：</td>
				<td><input type="text" name="SafeQty" size="30" readonly="readonly" value="<?= $_POST['SafeQty'] ?>"></td>
				</td>


				<td bgcolor="#87CEFA">库位：</td>
				<td><input type="text" name="sub_locator" readonly="readonly" value="<?= $_POST['sub_locator'] ?>"></td>






				<td bgcolor="#87CEFA">是否生效：</td>
				<td>
				<input type="text" name="Flag" size="30" readonly="readonly" value="<?= $_POST['Flag'] ?>">
					
				</td>


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
			<tr>
				<?php if ($_POST['item_use']=='研发'){?>
					<td bgcolor="#87CEFA">项目名称：</td>
				<td><input type="text" name="project_name" size="30" readonly="readonly" value="<?= $_POST['project_name'] ?>"></td>
				</td>
				<?php
			}?>
				


				<td bgcolor="#87CEFA">备注：</td>
				<td><input type="text" name="item_remark" readonly="readonly" value="<?= $_POST['item_remark'] ?>"></td>




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

		<?php
$sql2 = "SELECT
file_patch,creation_date,created_by,file_name,item_no,itemid
FROM sf_item_no_file  
where  item_no = '" .$item_no."'";
$result2 = DB_query($sql2, $db);
if (DB_num_rows($result2) == 0) {
   unset($result2);
 //  prnMsg(_('无附件'), 'info');
} else {
   echo '<p class="page_title_text">
<img src="' . $RootPath . '/css/' . $Theme . '/images/customer.png" title="' . _('料号附件信息') .
'" alt="" />' . ' ' . _('料号附件信息') . '
</p>';
   
   echo '<div>';
   echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
   echo '<table class="selection" align="center" >';
   $tableheader = '<tr>
					  
							   <th width =150 >' . '附件名称' . '</th>
							   <th width =190 >' . '上传时间' . '</th>
							   <th width =80 >' . '上传人员' . '</th>
							   <th  width =50>' . '下载' . '</th>
							   <th  width =50>' . '删除' . '</th>
							
							  
							  
	   </tr>';
			  
   echo $tableheader;
   $RowCounter = 1;
   $k = 0; //row colour counter
   while ($myrow = DB_fetch_array($result2)) {
	   if ($k == 1) {
		   echo '<tr class="EvenTableRows">';
		   $k = 0;
	   } else {
		   echo '<tr class="EvenTableRows">';
		   $k++;
	   }

	   echo '
			 <td>' . $myrow['file_name'] . '</td>
			 <td>' . date('Y-m-d H:i:s',$myrow['creation_date']) . '</td>
			 <td>' . $myrow['created_by'] . '</td>                      
			 <td><a href="' . $RootPath . '/' . $myrow['file_patch'] . '" target="_blank">' . '下载' . '</td>
			 <td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?file_11patch=' .$myrow['file_patch'] .'&item_no=' .$myrow['item_no'] .'&itemid=' .$myrow['itemid'] . '&ItemID=' .$_POST['ItemID'] . '&amp;delete=1" onclick="return confirm(\'' . _('是否要删除这个文件?') . '\');">' . _('删除')  . '</a></td>
			
			   

</tr>';
	   $RowCounter++;
	   If ($RowCounter == 500) {
		   $RowCounter = 1;
		   echo $tableheader;
	   }
   }
   echo '</table> ';


   echo '</div>';
}

?>
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
		<input type="submit" name="Save" value="核准">
		<input type="submit" name="Deletecustomer" value="拒绝"
			onclick="return confirm(\'' . _('Are You Sure?') . '\');" />
	</div>
</form>


<?php
include ('includes/footer.inc');
?>