<?php

include ('includes/session.inc');

$Title = _('料号建立');
$ViewTopic = '料号建立';
$BookMark = '料号建立';
include ('includes/header.inc');
include ('includes/SQL_CommonFunctions.inc');

$uploadflag = 1;
unset($result);





if (isset($_POST['Save'])) {

	$errorflag = 0;

	if (!empty($_FILES["Pic"]["tmp_name"])) {
		if ((($_FILES["Pic"]["type"] == "image/gif") || ($_FILES["Pic"]["type"] == "image/jpeg") || ($_FILES["Pic"]["type"] == "image/pjpeg")) && ($_FILES["Pic"]["size"] < 20 * 1024 * 1024)) {
			if ($_FILES["Pic"]["error"] > 0) {
				$msg = "错误: " . $_FILES["Pic"]["error"];
				prnMsg($msg, 'error');
				$errorflag = 1;
			}
		} else {
			$msg = "系统只支持gif,jpeg,pjpeg图片";
			prnMsg($msg, 'error');
			$errorflag = 1;
		}
	}

	if($_POST['item_use'] == 'Y'){
		$project_name = $_POST['project_name'];
		$_POST['inspect_flag'] = 'N';
		$sql_num = "select lpad((max( substr(item_no, -6,6 ) ) +1 ) , 6, 0) po_num  from sf_item_no where  item_use = 'Y' and item_no <> '999999'  ";
		// echo $sql_num;
		$result_num = DB_query($sql_num, $db);
		while ($v = DB_fetch_array($result_num)) {
			if ($v['po_num'] == null) {
				$ItemNo = '000001';
			} else {
				$ItemNo =  $v['po_num'];
			}
		}

	}else{
		$ItemNo = $_POST['ItemNo'];
		$project_name = '';
	}
echo $ItemNo;
	$sql = "SELECT count(*) FROM sf_item_no
				WHERE item_no = '" . $ItemNo . "'
				";
	$result = DB_query($sql, $db);
	$myrow = DB_fetch_row($result);
	if ($myrow[0] > 0) {
		$errorflag = 1;
		prnMsg('料号名称不能重复,因为另一个具有相同名称已经存在', 'error');
	}

	if ($_POST['item_use'] == 'Y' and  $_POST['project_name'] == '') {

		$errorflag = 1;
		prnMsg('请输入项目名称', 'error');
	}
	if ($_POST['item_use'] == 'S' and  $_POST['ItemNo'] == '') {

		$errorflag = 1;
		prnMsg('请输入料号', 'error');
	}
	if ($_POST['item_use'] == 'S' and  strlen($_POST['ItemNo']) == 6) {

		$errorflag = 1;
		prnMsg('请检查料号位数', 'error');
	}




	if ($errorflag == 0) {

		DB_Txn_Begin($db);

		$time = time();

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

		$sql = "INSERT INTO sf_item_no_log (change_type,change_time,
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
	youxiaoqi,
	creation_date,
	created_by,
	last_update_date,
	last_updated_by, 
        lead_time,manufacture_time,
        huohao ,project_name,inspect_flag
)
VALUES
	('新建','" . $time . "',
		'" . $_POST['Flag1'] . "',
		'" . $ItemNo . "',
		'" . $_POST['item_name'] . "',
        '" . $_POST['item_desc'] . "',
		'" . $_POST['Units'] . "',	
        '" . $_POST['item_category1'] . "', 
		'" . $_POST['item_type'] . "', 
		'" . $_POST['MinOty'] . "',
		'" . $_POST['SafeQty'] . "','" . $_POST['sub_code'] . "','" . $_POST['sub_locator'] . "',
		'" . $_POST['Flag'] . "',
		'" . $_POST['PicPath'] . "',
		'" . $_POST['youxiaoqi'] . "',
		'" . $time . "',
		'" . $_SESSION['UserID'] . "',
		'" . $time . "',
		'" . $_SESSION['UserID'] . "', 
		'" . $_POST['lead_time'] . "',
		'" . $_POST['manufacture_time'] . "',
		'" . $_POST['huohao'] . "' ,
		'" . $project_name . "' ,
		'" . $_POST['inspect_flag'] . "'
	)";
		$result = DB_query($sql, $db);

		$sql = "INSERT INTO sf_item_no (
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
	youxiaoqi,
	creation_date,
	created_by,
	last_update_date,
	last_updated_by, 
        lead_time,manufacture_time,
        huohao ,wendu,light,shidu,item_use,conditions,item_remark,project_name,inspect_flag
)
VALUES
	(
		'" . $_POST['Flag1'] . "',
		'" . $ItemNo . "',
		'" . $_POST['item_name'] . "',
        '" . $_POST['item_desc'] . "',
		'" . $_POST['Units'] . "',	
        '" . $_POST['item_category1'] . "', 
		'" . $_POST['item_type'] . "', 
		'" . $_POST['MinOty'] . "',
		'" . $_POST['SafeQty'] . "','" . $_POST['sub_code'] . "','" . $_POST['sub_locator'] . "',
		'" . $_POST['Flag'] . "',
		'" . $_POST['PicPath'] . "',
		'" . $_POST['youxiaoqi'] . "',
		'" . $time . "',
		'" . $_SESSION['UserID'] . "',
		'" . $time . "',
		'" . $_SESSION['UserID'] . "', 
		'" . $_POST['lead_time'] . "',
		'" . $_POST['manufacture_time'] . "',
		'" . $_POST['huohao'] . "' ,'" . $_POST['wendu'] . "' ,'" . $_POST['light'] . "' ,'" . $_POST['shidu'] . "'  ,'" . $_POST['item_use'] . "'  ,'" . $_POST['conditions'] . "' ,'" . $_POST['item_remark'] . "' ,'" . $project_name . "','" . $_POST['inspect_flag'] . "'
	)";
		$result = DB_query($sql, $db);

		if ($_POST['customer_code'] != '') {
			$sql = "insert into customer_item_relation(customer_code,item_no,customer_item,enable_flag,remark,creation_date,created_by,last_update_date,last_updated_by)
			values('" . $_POST['customer_code'] . "','" . $_POST['ItemNo'] . "','" . $_POST['customer_item'] . "','Y','" . $_POST['remark'] . "',
			'" . $time . "','" . $_SESSION['UserID'] . "','" . $time . "','" . $_SESSION['UserID'] . "') ";
			$result = DB_query($sql, $db);

		}

		///相似料号，复制BOM和产品工艺


		DB_Txn_Commit($db);

		prnMsg('料号' . $ItemNo . '建立成功！', success);
		header("Location: SussCreateItemNo.php?OrderNum=" . $ItemNo);
		unset($_POST);




	}

}



?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
	<title>新建订单</title>
	<link rel="shortcut icon" href="/favicon.ico" />
	<link rel="icon" href="/favicon.ico" />
	<meta http-equiv="Content-Type" content="application/html; charset=utf-8" />
	<link href="/css/xenos/default.css" rel="stylesheet" type="text/css" />
	<script type="text/javascript" src="./JXC/javascripts/miscfunctions.js"></script>
	<script type="text/javascript" src="./JXC/javascripts/wdatepicker.js"></script>
	<script type="text/javascript">var basepath = './JXC/statics/base/images';</script>
	<script type="text/javascript" src="./JXC/statics/base/js/metvar.js"></script>
	<script type="text/javascript" src="./JXC/statics/base/js/jQuery1.7.2.js"></script>
	<script type="text/javascript" src="./JXC/statics/base/js/uploadify/jquery.uploadify.v2.1.4.min.js"></script>
	<script type="text/javascript" src="./JXC/statics/base/js/iframes.js"></script>
	<script type="text/javascript" src="./JXC/statics/base/js/cookie.js"></script>
	<script type="text/javascript" src="./JXC/statics/base/js/jquery.livequery.js"></script>

	<link rel="stylesheet" href="jquery.ui.autocomplete.css">
	<script type="text/javascript" src="ui/jquery.ui.core.js"></script>
	<script type="text/javascript" src="ui/jquery.ui.widget.js"></script>
	<script type="text/javascript" src="ui/jquery.ui.position.js"></script>
	<script type="text/javascript" src="ui/jquery.ui.autocomplete.js"></script>

	<script src="./JXC/javascript/jquery-1.7.2.min.js"></script>
	<script src="./JXC/javascript/lhgdialog.min.js?self=true&skin=chrome"></script>
	<!-- Include all compiled plugins (below), or include individual files as needed -->
	<script src="/javascript/bootstrap.min.js"></script>

	<script type="text/javascript">
		/*ajax执行*/
		var lang = 'cn';
		var metimgurl = './JXC/statics/base/images/';
		var depth = '';
		$(document).ready(function () {
			ifreme_methei();
		});
	</script>

	<script type="text/javascript">

		function metreturn(url) {

			if (url) {

				location.href = url;

			} else if ($.browser.msie) {

				history.go(-1);

			} else {

				history.go(-1);

			}

		}



		function addsave() {



			var v = $('#idcount').val();

			$("#purchase_table_" + v).css("display", "");

			var c = parseInt(v) + 1;

			$('#idcount').val(c);

		}



	</script>

</head>

<body>



	<div id="CanvasDiv">

		<div id="BodyDiv">

			<div id="BodyWrapDiv">

				<p class="page_title_text"><img
						src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="料号建立"
						alt="料号建立">料号建立</p>

				<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method="POST">
					<input type="hidden" name="time" value="<?= $time ?>">

					<div>

						<input type="hidden" name="FormID" value="<?php echo $_SESSION['FormID']; ?>">

						<table class="selection">

							<tr>
								<td bgcolor="#87CEFA">料号编码：</td>
								<td colspan="1"><input type="text" name="ItemNo"  id="text_slect_buliao"
										value="<?= $_POST['ItemNo'] ?>" ><span
										style="color:red">*</span></td>

								<td bgcolor="#87CEFA">料号名称：</td>
								<td colspan="3"><input type="text" size="70" name="item_name"
										value="<?= $_POST['item_name'] ?>" required="required"><span
										style="color:red">*</span></td>
									
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

								<td bgcolor="#87CEFA">规格型号：</td>
								<td colspan="1"><input type="text" required="required" name="item_desc" 
										value="<?= $_POST['item_desc'] ?>"><span
										style="color:red">*</span></td>

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
							
								<tr>
								<td bgcolor="#87CEFA"  id="wendu" >温度：</td>
								<td  id="wendu1" >
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
								<td bgcolor="#87CEFA" id="ligth" >是否避光：</td>
								<td  id="ligth1" >
									<?php
									if ($_POST['light'] == 'N') {
										?>
										<input type="radio"  name="light" value='Y'>是
										<input type="radio"   name="light" value='N' checked=checked>否
										<?php
									} else {
										?>
										<input type="radio"  name="light" value='Y' checked=checked>是
										<input type="radio" name="light" value='N'>否
										<?php
									}
									?>
								</td>
								<td bgcolor="#87CEFA" id="shidu" >湿度范围：</td>
								<td   id="shidu1" ><select name="shidu" id="" >
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
							
							<tr>
							<td bgcolor="#87CEFA">料号用途：</td>
								<td>
									<select name="item_use" id="item_use" onchange="check1()">
										<?php
										$sql = "select item_type,type_name from sf_item_use order by item_type_id";
										$result = DB_query($sql, $db);
										while ($v = DB_fetch_array($result)) {
											if ($v['item_type'] == $_POST['item_use']) {
												?>
												<option value="<?= $v['item_type'] ?>"  selected="selected"><?= $v['type_name'] ?>
												</option>
											<?php } else { ?>
												<option value="<?= $v['item_type'] ?>" ><?= $v['type_name'] ?></option>
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
							<td><input  type="text" class="number" name="manufacture_time"
									value="<?= $_POST['manufacture_time'] ?>"></td>
								
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
								<td bgcolor="#87CEFA" style="display: none;" id="project_name">项目名称：</td>
								<td id="project_name1" style="display: none;">
								<input type="text" name="project_name" value="<?= $_POST['project_name'] ?>" id="text_slect_project_name">
								<image class="select_img" src="img/search.png" id="btn_slect_project_name"/>
							</td>
								</td>
								<td bgcolor="#87CEFA">备注：</td>
								<td colspan="3"><input  size="70"  type="text" name="item_remark" value="<?= $_POST['item_remark'] ?>"></td>
								</tr>
								<tr>
								<td bgcolor="#87CEFA"  id="inspect_flag">是否检验：</td>
								<td id="inspect_flag1">
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

						<div class="centre">

							<input type="submit" name="Save" value="保存">

						</div>

						<input type="hidden" name="idcount" id='idcount' value="11" />

						<input type="hidden" name="JustSelectedACustomer" value="Yes" />

					</div>

				</form>

			</div>

		</div>



		<div id="FooterDiv">

			<div id="FooterWrapDiv">



			</div>

		</div>

	</div>

	<script type="text/javascript">
		function check1() {
			var item_use = document.getElementById('item_use');
			var project_name = document.getElementById('project_name');
			var project_name1 = document.getElementById('project_name1');
			var text_slect_project_name = document.getElementById('text_slect_project_name');
			var inspect_flag = document.getElementById('inspect_flag');
			var inspect_flag1 = document.getElementById('inspect_flag1');
			
			
			if (item_use.value == 'Y') {
				project_name.style.display = '';
				project_name1.style.display = '';
				inspect_flag.style.display = 'none';
				inspect_flag1.style.display = 'none';

				$('#text_slect_project_name').attr('required', 'required');
				$('#text_slect_buliao').attr('readonly', 'readonly');
			} else {
				project_name.style.display = 'none';
				project_name1.style.display = 'none';
				inspect_flag.style.display = '';
				inspect_flag1.style.display = '';
				$('#text_slect_project_name').removeAttr('required');
				$('#text_slect_buliao').removeAttr('readonly');

				
			}
		}
function check(radio) {

var wendu = document.getElementById('wendu');
var wendu1 = document.getElementById('wendu1');
var ligth = document.getElementById('ligth');
var ligth1 = document.getElementById('ligth1');

var shidu = document.getElementById('shidu');
var shidu1 = document.getElementById('shidu1');
if (radio.value === 'Y') {
	wendu.style.display = '';
	wendu1.style.display = '';
	ligth.style.display = '';
	ligth1.style.display = '';

	shidu.style.display = '';
	shidu1.style.display = '';
} else {
	wendu.style.display = 'none';
	wendu1.style.display = 'none';
	ligth.style.display = 'none';
	ligth1.style.display = 'none';

	shidu.style.display = 'none';
	shidu1.style.display = 'none';
}

}
		$('#btn_slect_customer').dialog({
			title: '选择客户',
			width: '1050px',
			height: 470,
			content: 'url:BtnSearchCustomer518.php?fwValue=&cat=buliao',
			init: function () {
				this.content.document.getElementById('cat').value = 'buliao';
				this.content.document.getElementById('fwValue').value = '';
			}
		});

		$(document).ready(function () {



			$('.divToilet table tr td a').click(function () {

				$(this).parent('td').toggleClass('highlight');

				if (!($(this).parent('td').hasClass('highlight'))) {

					$(this).next().val('0');

				} else {

					$(this).next().val('1');

				}

			});


			$(function () {
				$("#text_slect_buliao").autocomplete({
					source: "autosearchstockso.php",
					minLength: 2,
					autoFocus: true
				});
			});


			<?php for ($i = 1; $i <= 50; $i++) { ?>

				$('#btn_slect_subcode<?= $i ?>').dialog({

					title: '选择仓库',

					width: '600px',

					height: 370,

					content: 'url:Searchsubcode.php?fwValue=<?= $i ?>&cat=buliao',

					init: function () {

						this.content.document.getElementById('cat').value = 'buliao';

						this.content.document.getElementById('fwValue').value = '<?= $i ?>';

					}

				});

			<?php } ?>

			$('#btn_slect_project_name').dialog({
            title:'选择项目',
            width: '1050px',
            height: 470,
            content:'url:BtnSearchProjectName.php?fwValue=&cat=buliao',
            init:function(){
			    this.content.document.getElementById('cat').value = 'buliao';
                this.content.document.getElementById('fwValue').value = '';
            }
        });

			$('#btn_slect_item_no2').dialog({

				title: '选择目标成半品料号',

				width: '950px',

				height: 470,

				content: 'url:BtnSearchNoBomItem2.php?fwValue=&cat=buliao',

				init: function () {

					this.content.document.getElementById('cat').value = 'buliao';

					this.content.document.getElementById('fwValue').value = '';

				}

			});

			$('#btn_slect_item_no1').dialog({

				title: '选择源成半品料号',

				width: '950px',

				height: 470,

				content: 'url:BtnSearchNoBomItem1.php?fwValue=&cat=buliao',

				init: function () {

					this.content.document.getElementById('cat').value = 'buliao';

					this.content.document.getElementById('fwValue').value = '';

				}

			});



			//Function to get URL arguments

			function getRequest() {

				var url = location.search; //获取url中"?"符后的字串

				var theRequest = new Object();

				if (url.indexOf("?") != -1) {

					var str = url.substr(1);

					strs = str.split("&");

					for (var i = 0; i < strs.length; i++) {

						theRequest[strs[i].split("=")[0]] = (strs[i].split("=")[1]);

					}

				}

				return theRequest;

			}





		});

	</script>

</body>



</html>

<?
include ('includes/footer.inc');
?>