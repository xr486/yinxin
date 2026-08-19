<?php
if (isset($_GET['data'])) {

	include_once("connect.php");
	$sql = "select * from locations where locationname = '" . $_GET['data'] . "'";
	$result_num = mysql_query($sql, $db);
	$res = mysql_fetch_assoc($result_num);
	echo $res['loccode'];
	return;
}

if (isset($_GET['data2'])) {

	include_once("connect.php");
	$sql = "select * from hr_employees where employee_name = '" . $_GET['data2'] . "'";
	$result_num = mysql_query($sql, $db);
	$res_customer_name = mysql_fetch_assoc($result_num);
	echo $res_customer_name['employee_num'];
	return;
}

if (isset($_GET['data3'])) {
	//$sql = "select * from customers where customer_name = '".$_GET['data']."'";
	include_once("connect.php");
	$sql = "select * from sf_item_no where item_no = '" . $_GET['data3'] . "'";
	$result_num = mysql_query($sql, $db);
	$res_item = mysql_fetch_assoc($result_num);
	echo $res_item['item_name'] . ':' . $res_item['item_desc'] . ':' . $res_item['units'] . ':' . $res_item['youxiaoqi'];
	return;
}

include('includes/session.inc');
$Title = _('其他原因入库');

$ViewTopic = '其他原因入库';
$BookMark = '其他原因入库';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

unset($result);


if (isset($_POST['Save'])) {
	$errorflag = 1;
	$time = time();

	foreach ($_POST as $key => $value) {
		if ($value != '') {
			if (substr($key, 0, 7) == 'stockid') {
				$errorflag = 0;
				$i = substr($key, 7);
				if ($value != '') {
					if ($_POST['UOM' . $i] == '') {
						$errorflag = 1;
						prnMsg($value . '未填写单位，请填写单位！', error);
					}

					if ($_POST['quantity' . $i] == '') {
						$errorflag = 1;
						prnMsg($value . '未填写数量，请填写数量！', error);
					}

					if ($_POST['quantity' . $i] <= 0) {
						$errorflag = 1;
						prnMsg($value . '入库数量小于等于0，请确认！', error);
					}
					if ($_POST['youxiaoqi' . $i] > 0 and $_POST['shengchan_date' . $i] == '' ) {
						$errorflag = 1;
						prnMsg($value . '请输入正确的生产日期！', error);
					}
					if ( strtotime($_POST['shengchan_date' . $i]) > $time) {
						$errorflag = 1;
						prnMsg($value . '请输入正确的生产日期！', error);
					}
				}
			}
		}
	}
	$time = time();
	$time2 = $time - 10;
	if ($_SESSION['lastsearchtime'] > $time2) {
		$errorflag = 1;
		prnMsg($value . '重复提交！', error);
	}

	if ($errorflag == 0) {

		$sumamount = 0.00;
		$date = date('Ymd');
		$sql_num = "select 	(
		CASE WHEN substr(max(trans_num) ,-2,1) = 0 THEN
			RIGHT (
				'100' + (
					max(substr(trans_num ,- 1)) + 1
				),
				2
			)
		ELSE
			substr(max(trans_num),-2,2) + 1
		END
        ) pr_num from inv_transactions_all_temp where  substr(trans_num,1,2)='ZR' and substr(trans_num,-10,8) = '" . $date . "'";
		$result_num = DB_query($sql_num, $db);
		$rownum = DB_num_rows($result_num);
		while ($v = DB_fetch_array($result_num)) {
			if ($v['pr_num'] == null) {
				$TransNum = 'ZR' . $date . '01';
			} else {
				$TransNum =  'ZR' . $date . $v['pr_num'];
			}
		}

		$change_date = strtotime($_POST['ScheduleDate']);
		DB_Txn_Begin($db);
		$time = time();
		$order_amount = 0;
		$j = 0;
		foreach ($_POST as $key => $value) {
			if ($value != '') {
				if (substr($key, 0, 7) == 'stockid') {
					$i = substr($key, 7);
					$j = $j + 1;
				}
				if ($value != '') {
					if (substr($key, 0, 7) == 'stockid') {
						$i = substr($key, 7);
						if ($_POST['unitprice' . $i] == '') {
							$_POST['unitprice' . $i] = 0;
						}
						$lineamount[$i] = $_POST['quantity' . $i] * $_POST['unitprice' . $i];
						if ($_POST['stockid' . $i] == '') {
							$_POST['stockid' . $i] = 'NULL';
							$bumishu[$i] = 0;
						}

						if ($_POST['youxiaoqi' . $i] == '0') {
							$_POST['shengchan_date' . $i] = '';
						
						}

				

						$sqlinvtrancsation = "insert into inv_transactions_all_temp(temp_type,status,transaction_type,transaction_date,quantity,uom,item_no,request_person,subinventory_from,remark,lot_num,creation_date,created_by,last_update_date,last_updated_by,trans_num,shengchan_date,youxiaoqi) ";
						$sqlinvtrancsation .= "values('其他原因入库','开始','" . $_POST['transaction_type']  . "','" . $change_date . "', '" . $_POST['quantity' . $i] . "','" . $_POST['UOM' . $i] . "','" . $_POST['stockid' . $i] . "','" . $_POST['requireemployee'] . "','" . $_POST['insubinventory']  . "','" . $_POST['Header_Remark']  . $_POST['remark' . $i] . "','" . $_POST['lot_num' . $i] . "','" . $time . "','" . $_SESSION['UserID'] . "','" . $time . "','" . $_SESSION['UserID'] . "','" . $TransNum . "','" . strtotime($_POST['shengchan_date' . $i]) . "','" . $_POST['youxiaoqi' . $i] . "')";
						$result_invtrancsation = DB_query($sqlinvtrancsation, $db);
					}
				}
			}
		}
		if ($errorflag == 0) {
			DB_Txn_Commit($db);
			$_SESSION['lastsearchtime'] = $time;
			prnMsg('其他原因入库编号' . $TransNum . '入库成功！', success);
			header("Location: SussCreate.php?OrderNum=" . $TransNum . "&type=InHouse");
		}
	}
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
	<title>其他原因入库</title>
	<link rel="shortcut icon" href="/JXC/favicon.ico" />
	<link rel="icon" href="/JXC/favicon.ico" />
	<meta http-equiv="Content-Type" content="application/html; charset=utf-8" />
	<link href="/css/xenos/default.css" rel="stylesheet" type="text/css" />
	<script type="text/javascript" src="/JXC/javascripts/miscfunctions.js"></script>
	<script type="text/javascript" src="/JXC/javascripts/wdatepicker.js"></script>
	<script type="text/javascript">
		var basepath = '/JXC/statics/base/images';
	</script>
	<script type="text/javascript" src="/JXC/statics/base/js/metvar.js"></script>
	<script type="text/javascript" src="/JXC/statics/base/js/jQuery1.7.2.js"></script>
	<script type="text/javascript" src="/JXC/statics/base/js/uploadify/jquery.uploadify.v2.1.4.min.js"></script>
	<script type="text/javascript" src="/JXC/statics/base/js/iframes.js"></script>
	<script type="text/javascript" src="/JXC/statics/base/js/cookie.js"></script>
	<script type="text/javascript" src="/JXC/statics/base/js/jquery.livequery.js"></script>



	<script src="/JXC/javascript/jquery-1.7.2.min.js"></script>
	<script src="/JXC/javascript/lhgdialog.min.js?self=true&skin=chrome"></script>
	<!-- Include all compiled plugins (below), or include individual files as needed -->
	<script src="/javascript/bootstrap.min.js"></script>

	<script type="text/javascript">
		/*ajax执行*/
		var lang = 'cn';
		var metimgurl = '/JXC/statics/base/images/';
		var depth = '';
		$(document).ready(function() {
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

		function addsave() 
{

	var v = $('#idcount').val();
    $("#purchase_table_"+v).css("display","");
	var v = parseInt(v) + 1;  
    $("#purchase_table_"+v).css("display","");
	var v = parseInt(v) + 1;  
    $("#purchase_table_"+v).css("display","");
	var v = parseInt(v) + 1;  
    $("#purchase_table_"+v).css("display","");
	var v = parseInt(v) + 1;  
    $("#purchase_table_"+v).css("display","");
	 
	var c = parseInt(v) + 1;
	$('#idcount').val(c); 
} 
	</script>
</head>

<body>

	<?php
	if (!isset($_POST['ScheduleDate'])) {
		$_POST['ScheduleDate'] = Date('Y-m-d');
	}

	?>

	<div id="CanvasDiv">
		<div id="BodyDiv">
			<div id="BodyWrapDiv">
				<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="其他原因入库" alt="其他原因入库">其他原因入库</p>
				<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method="POST"><input type="hidden" name="time" value="<?= $time ?>">
					<div>
						<input type="hidden" name="FormID" value="<?php echo $_SESSION['FormID']; ?>">
						<table class="selection">
							<div class="text-nav">
								<div class="text-nav-1 required">
									<div>申请人姓名：</div>

									<select type="text"   autocomplete="off"   required="required" name="requireemployee" id="text_slect_employee" value="<?= $_POST['employename'] ?>">
										<?php
										$sql = "select employee_num,employee_name from hr_employees ";
										$result = DB_query($sql, $db);
										while ($v = DB_fetch_array($result)) {
											if ($v['employee_num'] == $_POST['requireemployee']) {
										?>
												<option value="<?= $v['employee_num'] ?>" selected="selected"><?= $v['employee_num'] . '-' . $v['employee_name'] ?></option>
											<?php } else { ?>
												<option value="<?= $v['employee_num'] ?>"><?= $v['employee_num'] . '-' . $v['employee_name'] ?></option>
										<?php		}
										}
										?>
									</select>
								</div>


								<div class="text-nav-1 required">
									<div>仓库名称：</div>

									<select type="text"   autocomplete="off"   required="required" name="insubinventory" id="text_slect_insubinventoryname" value="<?= $_POST['insubinventory'] ?>" onblur="sel()">
										<?php
										$sql = "select loccode,locationname from locations where managed='Y'  ";
										$result = DB_query($sql, $db);
										while ($v = DB_fetch_array($result)) {
											if ($v['loccode'] == $_POST['insubinventory']) {
										?>
												<option value="<?= $v['loccode'] ?>" selected="selected"><?= $v['locationname'] ?></option>
											<?php } else { ?>
												<option value="<?= $v['loccode'] ?>"><?= $v['locationname'] ?></option>
										<?php		}
										}
										?>
									</select>
								</div>


								<div class="text-nav-1 required">
									<div>交易类型：</div>
									<select type="text"   autocomplete="off"   required="required" name="transaction_type" id="text_slect_inloccode" value="<?= $_POST['transaction_type'] ?>">
										<?php
										$sql = "select type_name from mtl_transaction_type where transaction_type='杂项入库' ";
										$result = DB_query($sql, $db);
										while ($v = DB_fetch_array($result)) {
											if ($v['type_name'] == $_POST['transaction_type']) {
										?>
												<option value="<?= $v['type_name'] ?>" selected="selected"><?= $v['type_name'] ?></option>
											<?php } else { ?>
												<option value="<?= $v['type_name'] ?>"><?= $v['type_name'] ?></option>
										<?php		}
										}
										?>
									</select>
								</div>
								<div class="text-nav-1 required">
									<div>入库时间：</div>
									<input type="text"   autocomplete="off"   name="ScheduleDate" maxlength="20" size="16" required="required" value="<?= $_POST['ScheduleDate'] ?>" onfocus="WdatePicker() ">
								</div>
								</tr>


								<div class="text-nav-2">
									<div>入库单备注：</div>
									<input type="text"   autocomplete="off"   name="Header_Remark" value="<?= $_POST['Header_Remark'] ?>" size="55" maxlength="200" />
								</div>


							</div>

						</table>
						<div class="centre">
							<input type="submit" name="Hearder" value="确认其他入库单头信息">
						</div>
						<input type="hidden" name="PageOffset" value="1" /><br />
						<?php
						if (isset($_POST['insubinventory']) and $_POST['insubinventory'] != '') {
							$lot_num = date('Ymd');

						?>
							<input id="purchase_table_lastRow" name="purchase_table_lastRow" type=hidden value="">
							<div class="text-nav-table">
								<table id="purchase_table" cellpadding="2" class="selection">
									<tr id="list-top">
										<th width="280">料号<span style="color:red">*</span></th>
										<th width="180">料号名称</th>
										<th width="180">规格型号</th>
										<th width="20">单位</th>
										<th width="20">有效期(天)</th>
										<th width="20">生产日期</th>
										<th width="120">入库数量<span style="color:red">*</span></th>
										<th width="30">备注</th>
										<th >批号</th>
										<th width="50" align="center">操作</th>
									</tr>
									<?php for ($i = 1; $i <= 50; $i++) {
										
										// $lot_num++;
										?>

										<tr id="purchase_table_<?= $i ?>" <?php echo $i > 10 && $_POST['stockid' . $i] == '' ? 'style="display:none"' : '' ?> class="mouse click">

											<td><input type="text"   autocomplete="off"   name="stockid<?= $i ?>" id="text_slect_buliao<?= $i ?>" value="<?= $_POST['stockid' . $i] ?>" size="24" maxlength="250" onblur="sel_item(<?= $i ?>)" />
												<image class="select_img" src="img/search.png" id="btn_slect_buliao<?= $i ?>"/>
											<td><input readonly="readonly" type="text"   autocomplete="off"   name="ItemDesc<?= $i ?>" id="text_slect_ItemDesc<?= $i ?>" value="<?= $_POST['ItemDesc' . $i] ?>" size="25" maxlength="42" /> </td>
											<td><input readonly="readonly" type="text"   autocomplete="off"   name="item_spec<?= $i ?>" id="text_slect_item_spec<?= $i ?>" value="<?= $_POST['item_spec' . $i] ?>" size="25" maxlength="42" /> </td>
											<td><input readonly="readonly" type="text"   autocomplete="off"   name="UOM<?= $i ?>" id="text_slect_units<?= $i ?>" value="<?= $_POST['UOM' . $i] ?>" size="3" maxlength="4" /></td>
											<td>
											<input type="text"  readonly="readonly" autocomplete="off"   name="youxiaoqi<?= $i ?>" id="text_slect_youxiaoqi<?= $i ?>" maxlength="20" size="4"  value="<?= $_POST['youxiaoqi' . $i] ?>" >
											</td>
											<td>
											<input type="text"   autocomplete="off"   name="shengchan_date<?= $i ?>" maxlength="20" size="16"  value="<?= $_POST['shengchan_date'] ?>" onfocus="WdatePicker() ">
											</td>
											<td><input type="text"   autocomplete="off"   class="number" name="quantity<?= $i ?>" id="quantity<?= $i ?>" value="<?= $_POST['quantity' . $i] ?>" size="8" maxlength="10" onblur="check(<?= $i ?>)" /></td>



											<td class="list-text"><input type="text"   autocomplete="off"   name="remark<?= $i ?>" value="<?= $_POST['remark' . $i] ?>" size="15" maxlength="45" /></td>
											<td class="list-text"><input type="text"   autocomplete="off"   name="lot_num<?= $i ?>" value="<?= $lot_num ?>" size="15" maxlength="45" /></td>


											<td> <a onclick="delettr($(this));" style="padding:0px 5px;" href="javascript:;">删除</a></td>



										</tr>
									<?php } ?>

								</table>
							</div>

							<div class="centre">


							</div>

							<div class="centre">
								<a onclick="addsave();">添加行</a>

							</div>

							<div class="centre">
								<input type="submit" name="Save" value="提交">
							</div>
						<?php
						}
						?>
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
		$(document).ready(function() {

			$('.divToilet table tr td a').click(function() {
				$(this).parent('td').toggleClass('highlight');
				if (!($(this).parent('td').hasClass('highlight'))) {
					$(this).next().val('0');
				} else {
					$(this).next().val('1');
				}
			});
			<?php for ($i = 1; $i <= 50; $i++) { ?>
				$('#btn_slect_buliao<?= $i ?>').dialog({
					title: '选择料号',
					width: '1030px',
					height: 470,
					content: 'url:SearchAllItem.php?fwValue=<?= $i ?>&cat=<?= $_POST['insubinventory'] ?>',
					init: function() {
						this.content.document.getElementById('cat').value = 'buliao';
						this.content.document.getElementById('fwValue').value = '<?= $i ?>';
					}
				});
			<?php } ?>




			$('#btn_slect_employee').dialog({
				title: '选择员工',
				width: '550px',
				height: 470,
				content: 'url:BtnSearchemployee.php?fwValue=&cat=buliao',
				init: function() {
					this.content.document.getElementById('cat').value = 'buliao';
					this.content.document.getElementById('fwValue').value = '';
				}
			});



			$('#btn_slect_insubinventory').dialog({
				title: '选择调入仓库',
				width: '550px',
				height: 470,
				content: 'url:BtnSearchinsubinventory.php?fwValue=&cat=<?= $_POST['outsubinventory'] ?>',
				init: function() {
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

		<?php for ($i = 1; $i <= 50; $i++) { ?>
			$(function() {
				$("#text_slect_buliao<?= $i ?>").autocomplete({
					source: "autosearchstock.php",
					minLength: 2,
					autoFocus: true
				});
			});
		<?php } ?>

		function sel_second() {
			var name = $('#text_slect_employee').val()
			$.get("", "data2=" + name, function(res_customer_name) {
				name = res_customer_name.split(":")
				$("#text_slect_requireemployee").val(name[0])

			})
		}

		function sel() {
			var name = $('#text_slect_insubinventoryname').val()
			$.get("", "data=" + name, function(res) {
				name = res.split(":")
				$("#text_slect_inloccode").val(name[0])

			})
		};





		function sel_item(s1) {
			var name = $('#text_slect_buliao' + s1).val()
			$.get("", "data3=" + name, function(res_item) {
				name = res_item.split(":")
				$("#text_slect_ItemDesc" + s1).val(name[0])
				$("#text_slect_item_spec" + s1).val(name[1])
				$("#text_slect_units" + s1).val(name[2])
				$("#text_slect_youxiaoqi" + s1).val(name[3])
			})
		}

	 

		function check(s1) {
			var b = document.getElementById("quantity" + s1).value;
			if (parseInt(b) < 0) {
				document.getElementById("Prompt").innerHTML = "本次交易数量不可以小于0！！！！";
				document.getElementById("quantity" + s1).value = "";
				document.getElementById("quantity" + s1).focus();
			} else {
				document.getElementById("Prompt").innerHTML = "";
			}
		}
	</script>
</body>

</html>
<?
include('includes/footer.inc');
?>