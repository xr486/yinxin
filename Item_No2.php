<?php 
	include('includes/session.inc');
	$Title = _('料号查询');

	$ViewTopic= '料号查询';
	$BookMark = '料号查询';

	include('includes/header.inc');
	include('includes/SQL_CommonFunctions.inc');

	if (isset($_GET['ItemID'])) {
		$ItemID = $_GET['ItemID'];
	}else if(isset($_POST['ItemID'])){
		$ItemID = $_POST['ItemID'];
	}
	if (isset($_GET['ItemNo'])) {
		$item_no = $_GET['ItemNo'];
	} else if (isset($_POST['ItemNo'])) {
		$item_no = $_POST['ItemNo'];
	}
	if (!isset($ItemID)) {
		header('Location: segment1set.php');
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
        
       
        echo '<br /><div class="centre"><a href="' . $RootPath . '/segment1set.php">' . _('查询料号') . '</a></div>';
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
              echo $_FILES["Pic"]["type"];
              echo $_FILES["Pic"]["tmp_name"]; 
            echo $_FILES["Pic"]["type"];move_uploaded_file($_FILES["Pic"]["tmp_name"],"itempic/" . $_POST['ItemNo'] .".jpg");
            $_POST['PicPath'] = "itempic/" . $_POST['ItemNo'] .".jpg";
			}
			else
			{
			  $msg = "系统只支持gif,jpeg,pjpeg图片";
			  prnMsg( $msg, 'error');
			  $uploadflag = 2;
			}
			
		}else{
			$sql = "select pic_path from sf_item_no where item_id ='".$ItemID."' ";
			$result = DB_query($sql,$db);
			while ($v = DB_fetch_array($result)) {
				$_POST['PicPath'] = $v['pic_path'];
			}
		}

		if ($uploadflag == 1) {
			

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
                                    item_desc='".$_POST['item_desc']."',
                                    min_order='".$_POST['MinOty']."',
                                    item_category1='".$_POST['item_category1']."', 
                                    item_type='".$_POST['item_type']."', 
                                    
                                    safe_qty='".$_POST['SafeQty']."', sub_code='".$_POST['sub_code']."',
                                    pic_path='".$_POST['PicPath']."',  sub_locator='".$_POST['sub_locator']."',
                                    po_price='".$_POST['po_price']."',
                                    so_flag='".$_POST['Flag1']."',
                                    disable_flag='".$_POST['Flag']."',
                                    last_update_date='".$time."',
                                    last_updated_by='".$_SESSION['UserID']."' , 
                                    lead_time = '".$_POST['lead_time']."' ,
									manufacture_time = '".$_POST['manufacture_time']."' ,
                                    huohao = '".$_POST['huohao']."' 
                              where item_id = '".$_POST['ItemID']."' ";
			$result = DB_query($sql,$db);

			prnMsg( _('料号更新成功！'), 'success');
			unset($_POST);
		}

	}


	$sql = "select sub_code,sub_locator,item_type,item_id,huohao,lead_time,manufacture_time,item_no,item_name,item_desc,so_flag,units,item_category1,min_order,safe_qty,po_price,disable_flag,wendu,light,shidu,item_use,conditions,project_name from sf_item_no a where item_id ='".$ItemID."' ";
        $sql = $sql."  order by item_no";
	$result = DB_query($sql,$db);
	while ($v = DB_fetch_array($result)) {
		$_POST['ItemID'] = $v['item_id'];
	$_POST['ItemNo'] = $v['item_no'];
	$_POST['item_type'] = $v['item_type'];
	$_POST['item_name'] = $v['item_name'];
	$_POST['item_desc'] = $v['item_desc'];
	$_POST['Units'] = $v['units'];
	$_POST['item_category1'] = $v['item_category1'];
	$_POST['MinOty'] = $v['min_order'];
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

	$sql2=" select * from  sf_item_no_file where item_no='".$v['item_no']."'   ";
	$result2 = DB_query($sql2,$db);
               
	}
?>
<div class="centre"><a href="<?=$RootPath?>/Item_No.php">返回查找料号</a></div>
<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/maintenance.png" title="料号查询" alt="料号查询">料号查询</p>
<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST" enctype="multipart/form-data">
<div>
	<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
<br>
<table class="selection">

<tr>
<input type="hidden" name="ItemID" 
			value="<?= $_POST['ItemID'] ?>">
	<td bgcolor="#87CEFA">料号编码：</td>
	<td colspan="1"><input type="text" name="ItemNo"  id="text_slect_buliao"
			value="<?= $_POST['ItemNo'] ?>" required="required"><span style="color:red">*</span></td>

	<td bgcolor="#87CEFA">料号名称：</td>
	<td colspan="3"><input type="text" size="70" name="item_name" value="<?= $_POST['item_name'] ?>"
			required="required"><span style="color:red">*</span></td>

</tr>
<tr>
	<td bgcolor="#87CEFA">料号类型：</td>
	<td>
		<select name="item_type" id="">
			<?php
			$sql = "select item_type,type_name from sf_item_type order by item_type_id";
			$result = DB_query($sql, $db);
			while ($v = DB_fetch_array($result)) {
				if ($v['item_type'] == $_POST['item_type']) {
					?>
					<option value="<?= $v['item_type'] ?>" selected="selected"><?= $v['type_name'] ?>
					</option>
				<?php } else { ?>
					<option value="<?= $v['item_type'] ?>"><?= $v['type_name'] ?></option>
				<?php }
			}
			?>
		</select><span style="color:red">*</span>
	</td>

	<td bgcolor="#87CEFA">料号分类：</td>
	<td>
		<select name="item_category1" id="">
			<?php
			$sql = "select unitname from sf_item_set order by unitid";
			$result = DB_query($sql, $db);
			while ($v = DB_fetch_array($result)) {
				if ($v['unitname'] == $_POST['item_category1']) {
					?>
					<option value="<?= $v['unitname'] ?>" selected="selected"><?= $v['unitname'] ?>
					</option>
				<?php } else { ?>
					<option value="<?= $v['unitname'] ?>"><?= $v['unitname'] ?></option>
				<?php }
			}
			?>
		</select><span style="color:red">*</span>
	</td>
	<td bgcolor="#87CEFA">单位：</td>
	<td>
		<select name="Units" id="">
			<?php
			$sql = "select unitname from unitsofmeasure order by unitid";
			$result = DB_query($sql, $db);
			while ($v = DB_fetch_array($result)) {
				if ($v['unitname'] == $_POST['Units']) {
					?>
					<option value="<?= $v['unitname'] ?>" selected="selected"><?= $v['unitname'] ?>
					</option>
				<?php } else { ?>
					<option value="<?= $v['unitname'] ?>"><?= $v['unitname'] ?></option>
					<?php
				}
			}
			?>
		</select>
		<span style="color:red">*</span>
	</td>

</tr>
<tr>

	<td bgcolor="#87CEFA">规格型号</td>
	<td colspan="1"><input type="text" required="required" name="item_desc" 
			value="<?= $_POST['item_desc'] ?>"><span style="color:red">*</span></td>

	<td bgcolor="#87CEFA">是否启用保存条件：</td>

	<td>
		<?php
		if ($_POST['conditions'] == 'N') {
			?>
			<input type="radio" onchange="check(this)" name="conditions" value='Y'>是
			<input type="radio" onchange="check(this)" name="conditions" value='N' checked=checked>否
			<?php
		} else {
			?>
			<input type="radio" onchange="check(this)" name="conditions" value='Y' checked=checked>是
			<input type="radio" onchange="check(this)" name="conditions" value='N'>否
			<?php
		}
		?>
	</td>
	<td bgcolor="#87CEFA">默认仓库：</td>
	<td>
		<select name="sub_code" id="">
			<?php
			$sql = "select loccode,locationname from locations  ";
			$result = DB_query($sql, $db);
			while ($v = DB_fetch_array($result)) {
				if ($v['loccode'] == $_POST['sub_code']) {
					?>
					<option value="<?= $v['loccode'] ?>" selected="selected"><?= $v['locationname'] ?>
					</option>
				<?php } else { ?>
					<option value="<?= $v['loccode'] ?>"><?= $v['locationname'] ?></option>
				<?php }
			}
			?>
		</select><span style="color:red">*</span>
	</td>
</tr>
<?php if ($_POST['conditions'] == 'N') {
	?>
	<tr id="hidden" style="display:none">
		<td bgcolor="#87CEFA" id="wendu">温度：</td>
		<td id="wendu1"><input type="text" name="wendu" value="<?= $_POST['wendu'] ?>"></td>
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
		<td id="shidu1"><input type="text" name="shidu" value="<?= $_POST['shidu'] ?>"></td>
	</tr>
	<?php
} else {
	?>
	<tr id="hidden">
		<td bgcolor="#87CEFA" id="wendu">温度：</td>
		<td id="wendu1"><input type="text" name="wendu" value="<?= $_POST['wendu'] ?>"></td>
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
		<td id="shidu1"><input type="text" name="shidu" value="<?= $_POST['shidu'] ?>"></td>
	</tr>
	<?php
}
?>


<tr>
	<td bgcolor="#87CEFA">料号用途：</td>
	<td>
		<select name="item_use" id="">
			<?php
			$sql = "select item_type,type_name from sf_item_use order by item_type_id";
			$result = DB_query($sql, $db);
			while ($v = DB_fetch_array($result)) {
				if ($v['item_type'] == $_POST['item_use']) {
					?>
					<option value="<?= $v['item_type'] ?>" selected="selected"><?= $v['type_name'] ?>
					</option>
				<?php } else { ?>
					<option value="<?= $v['item_type'] ?>"><?= $v['type_name'] ?></option>
				<?php }
			}
			?>
		</select><span style="color:red">*</span>
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
	<td bgcolor="#87CEFA">是否检验：</td>
	<td>
		<?php
		if ($_POST['inspect_flag'] == 'N') {
			?>
			<input type="radio" name="inspect_flag" value='Y'>是
			<input type="radio" name="inspect_flag" value='N' checked=checked>否
			<?php
		} else {
			?>
			<input type="radio" name="inspect_flag" value='Y' checked=checked>是
			<input type="radio" name="inspect_flag" value='N'>否
			<?php
		}
		?>
	</td>

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
 
</form>
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
<?php
  include('includes/footer.inc');
?>