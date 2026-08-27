<?php

include ('includes/session.inc');

/* ============================================================
 * 编码规则生成料号接口（GET，供"⚙ 按规则生成"按钮 AJAX 调用）
 * 返回 JSON：{ok, no, msg}；生成逻辑与下方自动编码完全一致（config 规则 + max 流水 +1）
 * 必须放在 header.inc / embed echo 之前，保证 JSON 纯净
 * ============================================================ */
if (isset($_GET['act']) && $_GET['act'] == 'gen_no') {
	include ('includes/SQL_CommonFunctions.inc');
	$encPrefix = ''; $encDigit = 6; $encSep = ''; $encAuto = 'Y';
	$resConf = DB_query("SELECT confname, confvalue FROM config WHERE confname IN ('encod_prefix','encod_digit','encod_separator','encod_auto')", $db);
	while ($cf = DB_fetch_array($resConf)) {
		if ($cf['confname'] == 'encod_prefix')         $encPrefix = $cf['confvalue'];
		elseif ($cf['confname'] == 'encod_digit')      $encDigit  = max(1, min(12, intval($cf['confvalue'])));
		elseif ($cf['confname'] == 'encod_separator')  $encSep    = $cf['confvalue'];
		elseif ($cf['confname'] == 'encod_auto')       $encAuto   = $cf['confvalue'];
	}
	if ($encAuto != 'Y') { $encPrefix = ''; $encDigit = 6; $encSep = ''; } // 关闭自定义规则 → 默认 6 位纯数字
	$head = $encPrefix . $encSep;
	$headLen = strlen($head);
	$sql_num = "select lpad((max( cast(substr(item_no, " . ($headLen + 1) . ", " . $encDigit . ") as unsigned) ) + 1), " . $encDigit . ", 0) po_num
				from sf_item_no where item_no <> '999999'
				and char_length(item_no) = " . ($headLen + $encDigit) . "
				and substr(item_no, 1, " . $headLen . ") = '" . DB_escape_string($head) . "'
				and substr(item_no, " . ($headLen + 1) . ", " . $encDigit . ") REGEXP '^[0-9]+$'";
	$result_num = DB_query($sql_num, $db);
	$genNo = null;
	while ($v = DB_fetch_array($result_num)) {
		if ($v['po_num'] !== null) $genNo = $head . $v['po_num'];
	}
	if ($genNo === null) $genNo = $head . str_pad('1', $encDigit, '0', STR_PAD_LEFT);
	if (ob_get_level() > 0) { @ob_clean(); }
	header('Content-Type: application/json; charset=utf-8');
	echo json_encode(array('ok' => true, 'no' => $genNo, 'rule' => ($encAuto == 'Y' ? 'custom' : 'default')));
	exit;
}

$Title = _('料号建立');
$ViewTopic = '料号建立';
$BookMark = '料号建立';

// 物料管理 MaterialManage 弹窗模式：?embed=1 时输出精简 HTML（不调用 webERP 全局 header/footer）
$isEmbed = isset($_GET['embed']) && $_GET['embed'] == '1';
if ($isEmbed) {
	$Theme = isset($_SESSION['Theme']) ? $_SESSION['Theme'] : 'xenos';
	echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>' . htmlspecialchars($Title) . '</title>';
	echo '<link href="' . $RootPath . '/css/' . $Theme . '/default.css" rel="stylesheet" type="text/css"/>';
	echo '<link href="' . $RootPath . '/css/bom_style.css" rel="stylesheet" type="text/css"/>';
	echo '<script src="' . $RootPath . '/javascript/jquery-1.7.2.min.js"></script>';
	echo '<style>body{padding:4px 6px;margin:0;background:#fafbfc;font-family:Verdana,Arial,sans-serif;font-size:12px;overflow-x:hidden}.embed-title{font-size:14px;font-weight:bold;color:#1976D2;border-bottom:2px solid #1976D2;padding-bottom:4px;margin-bottom:6px}input[type=text],input.number,input[size],textarea,select{text-align:left!important}table.selection{width:100%!important;border-collapse:collapse;table-layout:fixed}table.selection td{padding:2px 4px!important;font-size:12px;vertical-align:middle;word-break:break-all}table.selection input[type=text],table.selection input[type=number],table.selection input[type=file],table.selection select,table.selection textarea{width:100%!important;max-width:100%!important;box-sizing:border-box!important;min-width:0}table.selection input[size]{max-width:100%!important;width:100%!important}table.selection td[bgcolor="#87CEFA"]{width:80px!important;font-size:12px;white-space:nowrap}table.selection button.mm-gen-btn{display:block;margin-top:4px;width:auto;max-width:100%;padding:3px 12px;background:#1976D2;color:#fff;border:none;border-radius:3px;cursor:pointer;font-size:12px}.mm-success{background:#e8f5e9;border:1px solid #a5d6a7;padding:10px 14px;margin:6px 0 10px;border-radius:4px;font-size:12px}.mm-success b{color:#2e7d32;font-size:13px}.mm-success a{color:#1976D2;margin-right:10px;text-decoration:none}</style>';
	echo '</head><body><div class="embed-title">📦 料号建立</div>';
} else {
	include ('includes/header.inc');
}
include ('includes/SQL_CommonFunctions.inc');
/* 物料附件上传（自实现）：upload.class.php::uploadFile() 失败时 exit() 中断页面，无法集成到一站式流程；这里手写：检查+移动+入库，失败不中断。 */

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

	/* 编码逻辑统一：料号已填 → 用输入值；留空 → 按规则自动生成（不区分生产/研发） */
	$ItemNo = trim(isset($_POST['ItemNo']) ? $_POST['ItemNo'] : '');
	$project_name = trim(isset($_POST['project_name']) ? $_POST['project_name'] : '');
	if ($ItemNo == '') {
		/* 读取编码规则（config 表：encod_prefix/encod_digit/encod_separator/encod_auto） */
		$encPrefix = ''; $encDigit = 6; $encSep = ''; $encAuto = 'Y';
		$resConf = DB_query("SELECT confname, confvalue FROM config WHERE confname IN ('encod_prefix','encod_digit','encod_separator','encod_auto')", $db);
		while ($cf = DB_fetch_array($resConf)) {
			if ($cf['confname'] == 'encod_prefix')         $encPrefix = $cf['confvalue'];
			elseif ($cf['confname'] == 'encod_digit')      $encDigit  = max(1, min(12, intval($cf['confvalue'])));
			elseif ($cf['confname'] == 'encod_separator')  $encSep    = $cf['confvalue'];
			elseif ($cf['confname'] == 'encod_auto')       $encAuto   = $cf['confvalue'];
		}
		if ($encAuto == 'Y') {
			/* 自定义规则：前缀+分隔符+流水位数（查全部匹配规则形态的料号，不限 item_use） */
			$head = $encPrefix . $encSep;
			$headLen = strlen($head);
			$sql_num = "select lpad((max( cast(substr(item_no, " . ($headLen + 1) . ", " . $encDigit . ") as unsigned) ) + 1), " . $encDigit . ", 0) po_num
						from sf_item_no where item_no <> '999999'
						and char_length(item_no) = " . ($headLen + $encDigit) . "
						and substr(item_no, 1, " . $headLen . ") = '" . DB_escape_string($head) . "'
						and substr(item_no, " . ($headLen + 1) . ", " . $encDigit . ") REGEXP '^[0-9]+$'";
			$result_num = DB_query($sql_num, $db);
			$ItemNo = null;
			while ($v = DB_fetch_array($result_num)) {
				if ($v['po_num'] !== null) $ItemNo = $head . $v['po_num'];
			}
			if ($ItemNo === null) $ItemNo = $head . str_pad('1', $encDigit, '0', STR_PAD_LEFT);
		} else {
			/* 默认 6 位纯数字（查全部 6 位数字料号，长度+数字校验防 8 位料号干扰） */
			$sql_num = "select lpad((max( cast(substr(item_no, -6, 6) as unsigned) ) + 1), 6, 0) po_num
						from sf_item_no where item_no <> '999999'
						and char_length(item_no) = 6
						and substr(item_no, -6, 6) REGEXP '^[0-9]+$'";
			$result_num = DB_query($sql_num, $db);
			$ItemNo = '000001';
			while ($v = DB_fetch_array($result_num)) {
				if ($v['po_num'] !== null) $ItemNo = $v['po_num'];
			}
		}
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
	/* 已统一：不再区分生产/研发的特殊校验（项目名必填 / S 料号必填 / S 料号 6 位限制 均已移除） */




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
	able_flag,
	pic_path,
	youxiaoqi,
	creation_date,
	created_by,
	last_update_date,
	last_updated_by, 
        lead_time,manufacture_time,
        huohao ,wendu,light,shidu,item_use,conditions,item_remark,project_name,inspect_flag
		, material, spec, model, weight, drawing_no, supplier_code, std_part_type, priority, item_status
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
		, '" . $_POST['material'] . "'
		, '" . $_POST['spec'] . "'
		, '" . $_POST['model'] . "'
		, " . ($_POST['weight'] !== '' ? "'" . $_POST['weight'] . "'" : 'NULL') . "
		, '" . $_POST['drawing_no'] . "'
		, '" . $_POST['supplier_code'] . "'
		, '" . $_POST['std_part_type'] . "'
		, '" . $_POST['priority'] . "'
		, '" . $_POST['item_status'] . "'
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

		/* 一站式附件上传：建料同时把用户选的文件存盘 + 写 sf_item_no_file（不跳出当前页） */
		$mmAttachCnt = 0; $mmAttachErr = '';
		if (!empty($_FILES['AttachFile']['tmp_name']) && is_uploaded_file($_FILES['AttachFile']['tmp_name'])) {
			$src = $_FILES['AttachFile']['tmp_name'];
			$origName = $_FILES['AttachFile']['name'];
			$ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
			/* 简易扩展名白名单（与 SussCreateItemNo/upload.class 默认允许的类型一致；不严校验可放行） */
			$AllowExt = array('pptx','docx','dotx','xlsx','ppt','xls','doc','pdf','7z','rar','zip','bmp','jpeg','jpg','png','gif','dxf','dwg','step','stp','txt');
			if (!in_array($ext, $AllowExt)) {
				$mmAttachErr = '附件类型不允许（.' . htmlspecialchars($ext) . '）';
			} elseif ($_FILES['AttachFile']['size'] > 50 * 1024 * 1024) {
				$mmAttachErr = '附件超过 50MB';
			} else {
				if (!is_dir('SO')) { @mkdir('SO', 0777, true); }
				$uniName = md5(uniqid(microtime(true), true));
				$dest = 'SO/' . $uniName . ($ext !== '' ? ('.' . $ext) : '');
				if (move_uploaded_file($src, $dest)) {
					$attachName = trim(isset($_POST['attach_name']) ? $_POST['attach_name'] : '');
					if ($attachName == '') $attachName = $origName;
					DB_query("INSERT INTO sf_item_no_file (file_name, item_no, file_patch, creation_date, created_by) VALUES ('" . DB_escape_string($attachName) . "', '" . $ItemNo . "', '" . DB_escape_string($dest) . "', " . time() . ", '" . DB_escape_string($_SESSION['UserID']) . "')", $db);
					$mmAttachCnt = 1;
				} else {
					$mmAttachErr = '文件保存失败（请检查 SO/ 目录写权限）';
				}
			}
		}
		$mmCreatedNo = $ItemNo; // 渲染成功卡片用
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

				<?php if (isset($mmCreatedNo)) { /* 一站式成功卡片：建料后停留当前页，不再跳 SussCreateItemNo */ ?>
				<div style="background:#e8f5e9;border:1px solid #a5d6a7;padding:14px 18px;margin:8px 0 14px;border-radius:4px;">
					<div style="font-size:15px;color:#2e7d32;font-weight:bold;">✓ 料号 <?php echo htmlspecialchars($mmCreatedNo); ?> 建立成功</div>
					<?php if ($mmAttachCnt > 0) { ?>
					<div style="color:#2e7d32;margin-top:6px;">📎 已上传 <?php echo intval($mmAttachCnt); ?> 个附件</div>
					<?php } elseif (!empty($mmAttachErr)) { ?>
					<div style="color:#c62828;margin-top:6px;">⚠ 附件未上传：<?php echo htmlspecialchars($mmAttachErr); ?></div>
					<?php } ?>
					<div style="margin-top:10px;">
						<a href="AddItemNo.php?New=Y" style="color:#1976D2;margin-right:14px;text-decoration:none;font-weight:500;">➕ 继续新建</a>
						<a href="MaterialDetail.php?item_no=<?php echo urlencode($mmCreatedNo); ?>&amp;embed=1" target="_blank" style="color:#1976D2;margin-right:14px;text-decoration:none;font-weight:500;">👁 查看物料详情</a>
						<a href="DocPLM.php?item=<?php echo urlencode($mmCreatedNo); ?>" target="_blank" style="color:#1976D2;margin-right:14px;text-decoration:none;font-weight:500;">📁 物料文档</a>
						<a href="ItemDupCheck.php?name=<?php echo urlencode($mmCreatedNo); ?>&amp;do=1" target="_blank" style="color:#1976D2;text-decoration:none;font-weight:500;">🔍 查重</a>
					</div>
				</div>
				<?php } ?>

				<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method="POST" enctype="multipart/form-data">
					<input type="hidden" name="time" value="<?= $time ?>">

					<div>

						<input type="hidden" name="FormID" value="<?php echo $_SESSION['FormID']; ?>">

						<table class="selection">

							<tr>
								<td bgcolor="#87CEFA">料号编码：</td>
								<td colspan="1"><input type="text" name="ItemNo"  id="text_slect_buliao"
										value="<?= $_POST['ItemNo'] ?>" ><span
										style="color:red">*</span>
										<button type="button" class="mm-gen-btn" onclick="AddGenNo()" title="按编码规则自动生成料号">⚙ 按规则生成</button>
								</td>

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
								<td bgcolor="#87CEFA" id="project_name">项目名称：</td>
								<td id="project_name1">
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

													<tr>
								<td bgcolor="#87CEFA">材质：</td>
								<td colspan="1"><input type="text" name="material" value="<?= $_POST['material'] ?>"></td>
								<td bgcolor="#87CEFA">规格：</td>
								<td colspan="1"><input type="text" name="spec" value="<?= $_POST['spec'] ?>"></td>
								<td bgcolor="#87CEFA">型号：</td>
								<td colspan="1"><input type="text" name="model" value="<?= $_POST['model'] ?>"></td>
							</tr>
							<tr>
								<td bgcolor="#87CEFA">重量(kg)：</td>
								<td colspan="1"><input type="text" class="number" name="weight" value="<?= $_POST['weight'] ?>"></td>
								<td bgcolor="#87CEFA">图号：</td>
								<td colspan="1"><input type="text" name="drawing_no" value="<?= $_POST['drawing_no'] ?>"></td>
								<td bgcolor="#87CEFA">标准件类型：</td>
								<td colspan="1"><input type="text" name="std_part_type" value="<?= $_POST['std_part_type'] ?>"></td>
							</tr>
							<tr>
								<td bgcolor="#87CEFA">物料优先级：</td>
								<td colspan="1"><input type="text" name="priority" value="<?= $_POST['priority'] ?>"></td>
								<td bgcolor="#87CEFA">主供应商：</td>
								<td colspan="1">
									<select name="supplier_code" id="">
										<option value="">--</option>
										<?php
										$sql = "select vendor_code, vendor_name from vendors order by vendor_code";
										$result = DB_query($sql, $db);
										while ($v = DB_fetch_array($result)) {
											if ($v['vendor_code'] == $_POST['supplier_code']) {
												?>
												<option value="<?= $v['vendor_code'] ?>" selected="selected"><?= $v['vendor_code'] . ' ' . $v['vendor_name'] ?></option>
											<?php } else { ?>
												<option value="<?= $v['vendor_code'] ?>"><?= $v['vendor_code'] . ' ' . $v['vendor_name'] ?></option>
											<?php }
										}
										?>
									</select>
								</td>
								<td bgcolor="#87CEFA">承认状态：</td>
								<td colspan="1"><select name="item_status" style="height:28px;padding:3px 6px;border:1px solid #c5d3e0;border-radius:3px;font-size:13px;">
									<option value="草稿" <?= (isset($_POST['item_status']) && $_POST['item_status']=='草稿') ? 'selected' : '' ?>>草稿</option>
									<option value="试用" <?= (isset($_POST['item_status']) && $_POST['item_status']=='试用') ? 'selected' : '' ?>>试用</option>
									<option value="正式" <?= (!isset($_POST['item_status']) || $_POST['item_status']=='' || $_POST['item_status']=='正式') ? 'selected' : '' ?>>正式</option>
									<option value="冻结" <?= (isset($_POST['item_status']) && $_POST['item_status']=='冻结') ? 'selected' : '' ?>>冻结</option>
									<option value="报废" <?= (isset($_POST['item_status']) && $_POST['item_status']=='报废') ? 'selected' : '' ?>>报废</option>
								</select></td>
								</tr>

								<tr>
									<td bgcolor="#87CEFA">附件名称：</td>
									<td><input type="text" name="attach_name" placeholder="附件显示名（留空用文件名）" size="30"></td>
									<td bgcolor="#87CEFA">上传附件：</td>
									<td><input type="file" name="AttachFile"></td>
								</tr>
								<tr>
									<td colspan="4" style="color:#888;font-size:12px;padding:4px 8px;">可选——可在建立料号同时上传图纸/规格书/检验规范等附件，存到 <code>SO/</code> 目录与 <code>sf_item_no_file</code> 表；与 <b>主图（Pic）</b> 独立，上传时建议使用英文/数字文件名避免乱码</td>
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
			/* 已统一编码逻辑：不区分生产/研发。
			 * 项目名称非必填、始终显示；检验标志保留用户选择；料号可手动输入或留空自动生成 */
			var project_name = document.getElementById('project_name');
			var project_name1 = document.getElementById('project_name1');
			var inspect_flag = document.getElementById('inspect_flag');
			var inspect_flag1 = document.getElementById('inspect_flag1');
			if (project_name) project_name.style.display = '';
			if (project_name1) project_name1.style.display = '';
			if (inspect_flag) inspect_flag.style.display = '';
			if (inspect_flag1) inspect_flag1.style.display = '';
			$('#text_slect_project_name').removeAttr('required');
			$('#text_slect_buliao').removeAttr('readonly');
		}
		check1();
/* ⚙ 按规则生成料号：AJAX 调 gen_no 接口，生成后填入输入框并解除只读 */
function AddGenNo() {
	var box = document.getElementById('text_slect_buliao');
	jQuery.ajax({
		url: 'AddItemNo.php?act=gen_no&t=' + new Date().getTime(),
		type: 'GET',
		dataType: 'json',
		success: function(r) {
			if (r && r.ok) {
				box.value = r.no;
				box.removeAttribute('readonly');
			} else {
				alert((r && r.msg) || '生成失败，请先配置编码规则');
			}
		},
		error: function() {
			alert('生成失败，请检查编码规则配置（物料管理-编码规则）');
		}
	});
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

<!-- embed 模式已包含 </body></html>；正常模式仅引 footer.inc -->
<?php if (!$isEmbed) { include ('includes/footer.inc'); } ?>