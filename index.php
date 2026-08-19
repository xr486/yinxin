<?php
include('includes/session.inc');
include('includes/header3.inc');
// include('includes/header_fixed.inc');

$Title = _('主页面');

$ip=$_SERVER["REMOTE_ADDR"];
//echo $ip;
 
if ($ip='223.106.40.73' ) {
   include('includes/MainMenuLinksArray.php');
}  else if ($ip='221.224.130.222' )   {
   include('includes/MainMenuLinksArray.php');
}   else if ($_SESSION['UserID']=='admin' )   {
   include('includes/MainMenuLinksArray.php');
}  else if ($_SESSION['role_name']=='管理' )   {
   include('includes/MainMenuLinksArray.php');
}   else if ($_SESSION['role_name']=='销售主管' )   {
   include('includes/MainMenuLinksArray.php');
}  

echo '<script src="./statics/base/js/jquery-1.8.3.min.js"></script>';
echo '<script src="./statics/base/js/demo2.js"></script>';
echo '
	<html>
		<style>
			a{
				color:#fbc118; 
			}

			a:visited{
				color:#fbc118;
			}
			.notification {
				position: fixed;
				top: 60px;
				right: 100px;
				padding: 15px;
				background-color: #f0f0f0;
				color: #333;
				border: 1px solid #ccc;
				border-radius: 5px;
				box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
				z-index: 9999;
				opacity: 1;
				transition: opacity 0.3s ease-in-out;
			}

			.notification:hover {
				opacity: 0.8;
			}

		</style>
		<body style="background-color:#F1F1F1;">
		<div style="width:100%;">
		<div id="refreshData">

		</div>
';
?>
<script>
	//刚进入页面执行一次ajax
	$(document).ready(function() {
		// 使用AJAX请求后端数据
		$.ajax({
			url: 'get_header_info.php', // 请替换为您的PHP文件路径
			type: 'POST',
			data: {}, // 如果有其他参数需要传递给PHP，则可以在这里添加
			success: function(response) {
				
				var data = JSON.parse(response);
				var pr_waitapprove_count = document.getElementById("pr_waitapprove_count");
				if (pr_waitapprove_count !== null) {
					document.getElementById("pr_waitapprove_count").innerHTML = data.pr_waitapprove_count;
				}
				var gongcheng_count = document.getElementById("gongcheng_count");
				if (gongcheng_count !== null) {
					document.getElementById("gongcheng_count").innerHTML = data.gongcheng_count;
				}
				var wait_waixie = document.getElementById("wait_waixie");
				if (wait_waixie !== null) {
					document.getElementById("wait_waixie").innerHTML = data.wait_waixie;
				}
				var zhuguan_count = document.getElementById("zhuguan_count");
				if (zhuguan_count !== null) {
					document.getElementById("zhuguan_count").innerHTML = data.zhuguan_count;
				}
				var pogaojie_count = document.getElementById("pogaojie_count");
				if (pogaojie_count !== null) {
					document.getElementById("pogaojie_count").innerHTML = data.pogaojie_count;
				}
				var wait_re_quantity = document.getElementById("wait_re_quantity");
				if (wait_re_quantity !== null) {
					document.getElementById("wait_re_quantity").innerHTML = data.wait_re_quantity;
				}
				var OSPNoPoFeeReceipt = document.getElementById("OSPNoPoFeeReceipt");
				if (OSPNoPoFeeReceipt !== null) {
					document.getElementById("OSPNoPoFeeReceipt").innerHTML = data.OSPNoPoFeeReceipt;
				}
				var WIPQcBadClosed = document.getElementById("WIPQcBadClosed");
				if (WIPQcBadClosed !== null) {
					document.getElementById("WIPQcBadClosed").innerHTML = data.OSPNoPoFeeReceipt;
				}
				var WIPWaitColse = document.getElementById("WIPWaitColse");
				if (WIPWaitColse !== null) {
					document.getElementById("WIPWaitColse").innerHTML = data.WIPWaitColse;
				}
				var OSPNoPoFeeReceipt2 = document.getElementById("OSPNoPoFeeReceipt2");
				if (OSPNoPoFeeReceipt2 !== null) {
					document.getElementById("OSPNoPoFeeReceipt2").innerHTML = data.OSPNoPoFeeReceipt;
				}
				var SoOvershipReport = document.getElementById("SoOvershipReport");
				if (SoOvershipReport !== null) {
					document.getElementById("SoOvershipReport").innerHTML = data.SoOvershipReport;
				}
				var OspPOWaitdeliveryDetail = document.getElementById("OspPOWaitdeliveryDetail");
				if (OspPOWaitdeliveryDetail !== null) {
					document.getElementById("OspPOWaitdeliveryDetail").innerHTML = data.OspPOWaitdeliveryDetai;
				}
				var WIPQcBadClosed1 = document.getElementById("WIPQcBadClosed1");
				if (WIPQcBadClosed1 !== null) {
					document.getElementById("WIPQcBadClosed1").innerHTML = data.WIPQcBadClosed;
				}
				var so_count = document.getElementById("so_count");
				if (so_count !== null) {
					document.getElementById("so_count").innerHTML = data.so_count;
					console.log(data.so_count,'下单量');
				}
				var so_caiwu_count = document.getElementById("so_caiwu_count");
				if (so_caiwu_count !== null) {
					document.getElementById("so_caiwu_count").innerHTML = data.so_caiwu_count;
				}
				var no_bom_count = document.getElementById("no_bom_count");
				if (no_bom_count !== null) {
					document.getElementById("no_bom_count").innerHTML = data.no_bom_count;
				}
				
				var so_gongcheng_count = document.getElementById("so_gongcheng_count");
				if (so_gongcheng_count !== null) {
					document.getElementById("so_gongcheng_count").innerHTML = data.so_gongcheng_count;
				}
				var so_approve_count = document.getElementById("so_approve_count");
				if (so_approve_count !== null) {
					document.getElementById("so_approve_count").innerHTML = data.so_approve_count;
				}
				var so_0price_count = document.getElementById("so_0price_count");
				if (so_0price_count !== null) {
					document.getElementById("so_0price_count").innerHTML = data.so_0price_count;
				}
				var waitship_count = document.getElementById("waitship_count");
				if (waitship_count !== null) {
					document.getElementById("waitship_count").innerHTML = data.waitship_count;
				}
				var chuhuo_count = document.getElementById("chuhuo_count");
				if (chuhuo_count !== null) {
					document.getElementById("chuhuo_count").innerHTML = data.chuhuo_count;
				}
				var InvLowsafestock = document.getElementById("InvLowsafestock");
				if (InvLowsafestock !== null) {
					document.getElementById("InvLowsafestock").innerHTML = data.InvLowsafestock;
				}
				var SoOvershipReport1 = document.getElementById("SoOvershipReport1");
				if (SoOvershipReport1 !== null) {
					document.getElementById("SoOvershipReport1").innerHTML = data.SoOvershipReport;
				}
				var WIPQcBadClosed2 = document.getElementById("WIPQcBadClosed2");
				if (WIPQcBadClosed2 !== null) {
					document.getElementById("WIPQcBadClosed2").innerHTML = data.WIPQcBadClosed;
				}
				var pr_waitapprove_count1 = document.getElementById("pr_waitapprove_count1");
				if (pr_waitapprove_count1 !== null) {
					document.getElementById("pr_waitapprove_count1").innerHTML = data.pr_waitapprove_count;
				}
				var pogaojie_count1 = document.getElementById("pogaojie_count1");
				if (pogaojie_count1 !== null) {
					document.getElementById("pogaojie_count1").innerHTML = data.pogaojie_count;
				}
				var OspOrderMaxApprove = document.getElementById("OspOrderMaxApprove");
				if (OspOrderMaxApprove !== null) {
					document.getElementById("OspOrderMaxApprove").innerHTML = data.OspOrderMaxApprove;
				}
				var so_count1 = document.getElementById("so_count1");
				if (so_count1 !== null) {
					document.getElementById("so_count1").innerHTML = data.so_count;
				}
				var OspPOWaitdeliveryDetail1 = document.getElementById("OspPOWaitdeliveryDetail1");
				if (OspPOWaitdeliveryDetail1 !== null) {
					document.getElementById("OspPOWaitdeliveryDetail1").innerHTML = data.OspPOWaitdeliveryDetail;
				}
				var SoOvershipReport2 = document.getElementById("SoOvershipReport2");
				if (SoOvershipReport2 !== null) {
					document.getElementById("SoOvershipReport2").innerHTML = data.SoOvershipReport;
				}
				var WIPQcBadClosed3 = document.getElementById("WIPQcBadClosed3");
				if (WIPQcBadClosed3 !== null) {
					document.getElementById("WIPQcBadClosed3").innerHTML = data.WIPQcBadClosed;
				}
				var WIPCompleteQc = document.getElementById("WIPCompleteQc");
				if (WIPCompleteQc !== null) {
					document.getElementById("WIPCompleteQc").innerHTML = data.WIPCompleteQc;
				}
				var OSPPoDelivery = document.getElementById("OSPPoDelivery");
				if (OSPPoDelivery !== null) {
					document.getElementById("OSPPoDelivery").innerHTML = data.OSPPoDelivery;
				}
				var WIPCompleteQcBad = document.getElementById("WIPCompleteQcBad");
				if (WIPCompleteQcBad !== null) {
					document.getElementById("WIPCompleteQcBad").innerHTML = data.WIPCompleteQcBad;
				}
				var WIPQcBadClosed4 = document.getElementById("WIPQcBadClosed4");
				if (WIPQcBadClosed4 !== null) {
					document.getElementById("WIPQcBadClosed4").innerHTML = data.WIPQcBadClosed4;
				}
			},
			error: function(xhr, status, error) {
				console.log(error); // 发生错误时打印错误信息
			}
		});
	});
	setInterval(function() {
		// 使用AJAX请求后端数据
		$.ajax({
			url: 'get_header_info.php', // example.php 是你的PHP文件名
			type: 'POST',
			data: {
				// data: 'data'
			}, // 如果有其他参数需要传递给PHP，则可以在这里添加
			success: function(response) {
				console.log(response)
				var data = JSON.parse(response);
				var pr_waitapprove_count = document.getElementById("pr_waitapprove_count");
				if (pr_waitapprove_count !== null) {
					document.getElementById("pr_waitapprove_count").innerHTML = data.pr_waitapprove_count;
				}
				var gongcheng_count = document.getElementById("gongcheng_count");
				if (gongcheng_count !== null) {
					document.getElementById("gongcheng_count").innerHTML = data.gongcheng_count;
				}
				var wait_waixie = document.getElementById("wait_waixie");
				if (wait_waixie !== null) {
					document.getElementById("wait_waixie").innerHTML = data.wait_waixie;
				}
				var zhuguan_count = document.getElementById("zhuguan_count");
				if (zhuguan_count !== null) {
					document.getElementById("zhuguan_count").innerHTML = data.zhuguan_count;
				}
				var pogaojie_count = document.getElementById("pogaojie_count");
				if (pogaojie_count !== null) {
					document.getElementById("pogaojie_count").innerHTML = data.pogaojie_count;
				}
				var wait_re_quantity = document.getElementById("wait_re_quantity");
				if (wait_re_quantity !== null) {
					document.getElementById("wait_re_quantity").innerHTML = data.wait_re_quantity;
				}
				var OSPNoPoFeeReceipt = document.getElementById("OSPNoPoFeeReceipt");
				if (OSPNoPoFeeReceipt !== null) {
					document.getElementById("OSPNoPoFeeReceipt").innerHTML = data.OSPNoPoFeeReceipt;
				}
				var WIPQcBadClosed = document.getElementById("WIPQcBadClosed");
				if (WIPQcBadClosed !== null) {
					document.getElementById("WIPQcBadClosed").innerHTML = data.OSPNoPoFeeReceipt;
				}
				var WIPWaitColse = document.getElementById("WIPWaitColse");
				if (WIPWaitColse !== null) {
					document.getElementById("WIPWaitColse").innerHTML = data.WIPWaitColse;
				}
				var OSPNoPoFeeReceipt2 = document.getElementById("OSPNoPoFeeReceipt2");
				if (OSPNoPoFeeReceipt2 !== null) {
					document.getElementById("OSPNoPoFeeReceipt2").innerHTML = data.OSPNoPoFeeReceipt;
				}
				var SoOvershipReport = document.getElementById("SoOvershipReport");
				if (SoOvershipReport !== null) {
					document.getElementById("SoOvershipReport").innerHTML = data.SoOvershipReport;
				}
				var OspPOWaitdeliveryDetail = document.getElementById("OspPOWaitdeliveryDetail");
				if (OspPOWaitdeliveryDetail !== null) {
					document.getElementById("OspPOWaitdeliveryDetail").innerHTML = data.OspPOWaitdeliveryDetai;
				}
				var WIPQcBadClosed1 = document.getElementById("WIPQcBadClosed1");
				if (WIPQcBadClosed1 !== null) {
					document.getElementById("WIPQcBadClosed1").innerHTML = data.WIPQcBadClosed;
				}
				var so_count = document.getElementById("so_count");
				if (so_count !== null) {
					document.getElementById("so_count").innerHTML = data.so_count;
				}
				var so_caiwu_count = document.getElementById("so_caiwu_count");
				if (so_caiwu_count !== null) {
					document.getElementById("so_caiwu_count").innerHTML = data.so_caiwu_count;
				}
				var no_bom_count = document.getElementById("no_bom_count");
				if (no_bom_count !== null) {
					document.getElementById("no_bom_count").innerHTML = data.no_bom_count;
				}
				var so_gongcheng_count = document.getElementById("so_gongcheng_count");
				if (so_gongcheng_count !== null) {
					document.getElementById("so_gongcheng_count").innerHTML = data.so_gongcheng_count;
				}
				var so_approve_count = document.getElementById("so_approve_count");
				if (so_approve_count !== null) {
					document.getElementById("so_approve_count").innerHTML = data.so_approve_count;
				}
				var so_0price_count = document.getElementById("so_0price_count");
				if (so_0price_count !== null) {
					document.getElementById("so_0price_count").innerHTML = data.so_0price_count;
				}
				var waitship_count = document.getElementById("waitship_count");
				if (waitship_count !== null) {
					document.getElementById("waitship_count").innerHTML = data.waitship_count;
				}
				var chuhuo_count = document.getElementById("chuhuo_count");
				if (chuhuo_count !== null) {
					document.getElementById("chuhuo_count").innerHTML = data.chuhuo_count;
				}
				var InvLowsafestock = document.getElementById("InvLowsafestock");
				if (InvLowsafestock !== null) {
					document.getElementById("InvLowsafestock").innerHTML = data.InvLowsafestock;
				}
				var SoOvershipReport1 = document.getElementById("SoOvershipReport1");
				if (SoOvershipReport1 !== null) {
					document.getElementById("SoOvershipReport1").innerHTML = data.SoOvershipReport;
				}
				var WIPQcBadClosed2 = document.getElementById("WIPQcBadClosed2");
				if (WIPQcBadClosed2 !== null) {
					document.getElementById("WIPQcBadClosed2").innerHTML = data.WIPQcBadClosed;
				}
				var pr_waitapprove_count1 = document.getElementById("pr_waitapprove_count1");
				if (pr_waitapprove_count1 !== null) {
					document.getElementById("pr_waitapprove_count1").innerHTML = data.pr_waitapprove_count;
				}
				var pogaojie_count1 = document.getElementById("pogaojie_count1");
				if (pogaojie_count1 !== null) {
					document.getElementById("pogaojie_count1").innerHTML = data.pogaojie_count;
				}
				var OspOrderMaxApprove = document.getElementById("OspOrderMaxApprove");
				if (OspOrderMaxApprove !== null) {
					document.getElementById("OspOrderMaxApprove").innerHTML = data.OspOrderMaxApprove;
				}
				var so_count1 = document.getElementById("so_count1");
				if (so_count1 !== null) {
					document.getElementById("so_count1").innerHTML = data.so_count;
				}
				var OspPOWaitdeliveryDetail1 = document.getElementById("OspPOWaitdeliveryDetail1");
				if (OspPOWaitdeliveryDetail1 !== null) {
					document.getElementById("OspPOWaitdeliveryDetail1").innerHTML = data.OspPOWaitdeliveryDetail;
				}
				var SoOvershipReport2 = document.getElementById("SoOvershipReport2");
				if (SoOvershipReport2 !== null) {
					document.getElementById("SoOvershipReport2").innerHTML = data.SoOvershipReport;
				}
				var WIPQcBadClosed3 = document.getElementById("WIPQcBadClosed3");
				if (WIPQcBadClosed3 !== null) {
					document.getElementById("WIPQcBadClosed3").innerHTML = data.WIPQcBadClosed;
				}
				var WIPCompleteQc = document.getElementById("WIPCompleteQc");
				if (WIPCompleteQc !== null) {
					document.getElementById("WIPCompleteQc").innerHTML = data.WIPCompleteQc;
				}
				var OSPPoDelivery = document.getElementById("OSPPoDelivery");
				if (OSPPoDelivery !== null) {
					document.getElementById("OSPPoDelivery").innerHTML = data.OSPPoDelivery;
				}
				var WIPCompleteQcBad = document.getElementById("WIPCompleteQcBad");
				if (WIPCompleteQcBad !== null) {
					document.getElementById("WIPCompleteQcBad").innerHTML = data.WIPCompleteQcBad;
				}
				var WIPQcBadClosed4 = document.getElementById("WIPQcBadClosed4");
				if (WIPQcBadClosed4 !== null) {
					document.getElementById("WIPQcBadClosed4").innerHTML = data.WIPQcBadClosed4;
				}
			},
			error: function(xhr, status, error) {
				console.log(error); // 发生错误时打印错误信息
			}
		});
	}, 1800000); // 1800000等于30分钟
</script>

<?php
if (isset($_SESSION['UserID']) && isset($RootPath)) {
	$sql = "
			select  b.depart_name
			from www_users a,hr_departs b
			where a.depart_code = b.depart_name
			and userid = '" . $_SESSION['UserID'] . "'
		";
	$result = DB_query($sql, $db);
	if ($row = DB_fetch_array($result)) {
		$departCode = $row['depart_name'];
		$role_name = $row['role_name'];
		//采购部
		if ($departCode == '采购部') {
			$default_url = $RootPath . '/POdepart.php';
			$default_name = '个人主页';
		}
		//销售部
		else if ($departCode == '销售部') {
			$default_url = $RootPath . '/SOdepart.php';
			$default_name = '个人主页';
		}
		
        //生产
		else if ($departCode == '生产管理部-试剂组' || $departCode == '生产管理部-仪器组') {
			$default_url = $RootPath . '/WIPdepart.php';
			// $default_url = $RootPath . '/WIPModify1.php';
			$default_name = '个人主页';
		}
        //生产
		else if ($departCode == '钣金部') {
			$default_url = $RootPath . '/WIPdepart1.php';
			// $default_url = $RootPath . '/WIPModify1.php';
			$default_name = '个人主页';
		}
		//品质
		else if ($departCode == '质量管理部') {
			$default_url = $RootPath . '/QCdepart.php'; 
			$default_name = '个人主页';
		}
		//仓库
		else if ($departCode == '仓库物流部') {
			$default_url = $RootPath . '/zicaidepart.php'; 
			$default_name = '个人主页';
		}
		else if ($departCode == '设计部') {
			$default_url = $RootPath . '/BOMWaitReport1.php'; 
			$default_name = '个人主页';
		}
		else {
			$default_url = $RootPath . '/sale_ekanban.php';
			$default_name = '销售看板';
		}
		$default_lab = [$default_url, $default_name];
		
	} else {
		prnMsg(_('找不到相应数据！'), 'error');
	}
}
?>

<?php
if (!isset($RootPath)) {
	$RootPath = dirname(htmlspecialchars($_SERVER['PHP_SELF']));
	if ($RootPath == '/' or $RootPath == "\\") {
		$RootPath = '';
	}
}
// 顶部样式
$_SESSION['CompanyRecord']['coyname'] = "翊新";
echo '<div style="background-color:#1e2e3d;float:left;width:100%;height:60px;">';
echo '<div>';
echo '<div  style="float:left;background-color:#1e2e3d;width:230px;height:60px;">'; //===HJ===
echo '<div style="float:left;font-size:16px;color:#fff;">';
echo '<img  style="width:30xp;height:30px;" src="' . $RootPath . '/css/' . $Theme . '/images/gongsi.png" title="' . _('Company') . '" alt="' . _('Company') . '"/>' . stripslashes($_SESSION['CompanyRecord']['coyname']);
echo '</div>';
echo '<div style="float:right;font-size:16px;color:#fff;">
		<img style="width:20xp;height:20px;" src="' . $RootPath . '/css/' . $Theme . '/images/guanli.png" title="User" alt="' . _('User') . '"/><span style="color:#fff;">&nbsp;' . stripslashes($_SESSION['UsersRealName']) . '&nbsp;</span> </div>';
echo '</div>'; // AppInfoDiv
?>
<div style="position:absolute;left:50%;margin-left:-350px;" id="box11">
	
	<style>
		#table_a a:hover {
			color: #ff9522;
			text-decoration: none;
		}
	</style>

	<!-- 顶部小表格 -->
	<!-- <table align="center" id="table_a" style="background-color:rgb(255,255,255,0);color:white;font-size:14px;border-collapse: collapse;" border="1px;">
	<?php if ($_SESSION['DepartCode']=='业务部') { ?>
 
 <tr>

  <td>今天下单量:<?php echo '<a id="so_count" href="' . $RootPath . '/SOTodycreate.php?UpdateCustomerCode=' . $_SESSION['UserID'] . '" target="_blank"><font style="color:#fbc118" siza="6">' . $so_count;  ?></td>
   <td>待订单主管审核量:<?php echo '<a id="so_gongcheng_count" href="' . $RootPath . '/SearchSoForApprove.php?UpdateCustomerCode=' . $_SESSION['UserID'] . '" target="_blank"><font style="color:red"  >' . $so_gongcheng_count      ;?></td>
<td>订单签核待签量:<?php echo '<a id="so_caiwu_count" href="' . $RootPath . '/ARFinApprove.php?UpdateCustomerCode=' . $_SESSION['UserID'] . '" target="_blank"><font style="color:red"  >' . $so_caiwu_count      ?></td>

 <td>当天出货量:<?php echo '<font  id="chuhuo_count" style="color:#fbc118" >' . $chuhuo_count      ?></td>
 <td>订单未建BOM量:
 <?php echo '<a id="no_bom_count" href="' . $RootPath . '/SoNoBom.php?UpdateCustomerCode=' . $_SESSION['UserID'] . '" target="_blank"><font style="color:red" size="2">'. $no_bom_count  ?></td>

</tr>
<?php }  ?>

<?php if ($_SESSION['DepartCode']=='客服部') { ?>

 <tr>
  <td>今天下单量:<?php echo '<a id="so_count" href="' . $RootPath . '/SOTodycreate.php?UpdateCustomerCode=' . $_SESSION['UserID'] . '" target="_blank"><font style="color:#fbc118" siza="6">' . $so_count;  ?></td>
   <td>待订单主管审核量:<?php echo '<a href="' . $RootPath . '/SearchSoForApprove.php?UpdateCustomerCode=' . $_SESSION['UserID'] . '" target="_blank"><font style="color:red" >' . $so_gongcheng_count      ?></td>
<td>订单签核待签量:<?php echo '<a href="' . $RootPath . '/ARFinApprove.php?UpdateCustomerCode=' . $_SESSION['UserID'] . '" target="_blank"><font style="color:red" >' . $so_caiwu_count      ?></td>

 <td>当天出货量:<?php echo '<font style="color:red" >' . $chuhuo_count      ?></td>
 <td>订单未建BOM量:
 <?php echo '<a href="' . $RootPath . '/SoNoBom.php?UpdateCustomerCode=' . $_SESSION['UserID'] . '" target="_blank"><font style="color:red" size="2">'. $no_bom_count  ?></td>

</tr>
<?php }  ?>
<?php if ($_SESSION['DepartCode']=='采购部') { ?>

 <tr>
 <td>待签核请购单量:
 <?php echo '<a href="' . $RootPath . '/PrWaitApprovedReport.php?UpdateCustomerCode=' . $_SESSION['UserID'] . '" target="_blank"><font style="color:red"  >'. $pr_count  ?></td>
 
 <td>今天下采购单量:
 <?php echo '<a href="' . $RootPath . '/POTodyCreate.php?UpdateCustomerCode=' . $_SESSION['UserID'] . '" target="_blank"><font style="color:red"  >'. $POTodyCreate   ?></td>
 <td>待签核采购单量:<?php echo '<a href="' . $RootPath . '/POApproved.php?UpdateCustomerCode=' . $_SESSION['UserID'] . '" target="_blank"><font style="color:red"  >' . $gongcheng_count ?></td>
 <td>当天签采购单量:<?php echo '<a href="' . $RootPath . '/potodayqianding.php?UpdateCustomerCode=' . $_SESSION['UserID'] . '" target="_blank"><font style="color:red" >' . $po_qianhe_count      ?></td>

<td>待财务签量:<?php echo '<a href="' . $RootPath . '/POFinApproved.php?UpdateCustomerCode=' . $_SESSION['UserID'] . '" target="_blank"><font style="color:red"  >' . $caiwu_count     ?></td>
<td> 当天需到货未到货:<?php echo '<a href="' . $RootPath . '/POTodyWaitReceive.php?UpdateCustomerCode=' . $_SESSION['UserID'] . '" target="_blank"><font style="color:red" >' . $wait_re_quantity         ?></td>

 <td> 当天入库量:<?php echo '<a href="' . $RootPath . '/POTodyReceive.php?UpdateCustomerCode=' . $_SESSION['UserID'] . '" target="_blank"><font style="color:red"  >' . $transaction_quantity         ?></td>
<td>订单未建BOM量:
 <?php echo '<a href="' . $RootPath . '/SoNoBom.php?UpdateCustomerCode=' . $_SESSION['UserID'] . '" target="_blank"><font style="color:red" size="2">'. $no_bom_count  ?></td>

</tr>
<?php }  ?>

<?php if ($_SESSION['DepartCode'] =='生产部') { ?>

 <tr> 
 <td>订单未建BOM量:
 <?php echo '<a href="' . $RootPath . '/SoNoBom.php?UpdateCustomerCode=' . $_SESSION['UserID'] . '" target="_blank"><font style="color:red" size="2">'. $no_bom_count  ?></td>

 <td>待签核采购单量:
 <?php echo '<a href="' . $RootPath . '/POApproved.php?UpdateCustomerCode=' . $_SESSION['UserID'] . '" target="_blank"><font style="color:red" siza="6">'. $gongcheng_count   ?></td>
 <td>待签核业务订单:<?php echo '<a href="' . $RootPath . '/SearchSoForApprove.php?UpdateCustomerCode=' . $_SESSION['UserID'] . '" target="_blank"><font style="color:red" siza="6">'. $so_gongcheng_count        ?></td>
 <td>当天签采购单量:<?php echo '<a href="' . $RootPath . '/potodayqianding.php?UpdateCustomerCode=' . $_SESSION['UserID'] . '" target="_blank"><font style="color:red" siza="6">' . $po_qianhe_count      ?></td>

 <td>当天签业务订单量:<?php echo '<a href="' . $RootPath . '/sotodayqianding.php?UpdateCustomerCode=' . $_SESSION['UserID'] . '" target="_blank"><font style="color:red" siza="6">' . $so_qianhe_count         ?></td>

  <td>当天付款量:<?php echo '<a href="' . $RootPath . '/POTodyPayment.php?UpdateCustomerCode=' . $_SESSION['UserID'] . '" target="_blank"><font style="color:red" siza="6">' . $pay_count    ?></td>
 
</tr>
<?php }  ?>

<?php if ($_SESSION['DepartCode']=='财务部') { ?>

 <tr>
 <td>待签核采购部单:
 <?php echo '<a href="' . $RootPath . '/POFinApproved.php?UpdateCustomerCode=' . $_SESSION['UserID'] . '" target="_blank"><font style="color:red" siza="6">'. $caiwu_count   ?></td>
 <td>待签核业务订单:<?php echo '<a href="' . $RootPath . '/ARFinApprove.php?UpdateCustomerCode=' . $_SESSION['UserID'] . '" target="_blank"><font style="color:red" siza="6">'. $so_caiwu_count        ?></td>
 <td>当天签采购部单量:<?php echo '<a href="' . $RootPath . '/pofndtodayqianding.php?UpdateCustomerCode=' . $_SESSION['UserID'] . '" target="_blank"><font style="color:red" siza="6">' . $po_fndqianhe_count      ?></td>

 <td>当天签业务订单量:<?php echo '<a href="' . $RootPath . '/sofndtodayqianding.php?UpdateCustomerCode=' . $_SESSION['UserID'] . '" target="_blank"><font style="color:red" siza="6">' . $so_fndqianhe_count         ?></td>
 <td>当天付款量:<?php echo '<a href="' . $RootPath . '/POTodyPayment.php?UpdateCustomerCode=' . $_SESSION['UserID'] . '" target="_blank"><font style="color:red" siza="6">'. $pay_count        ?></td>
 <td>当天收款量:<?php echo '<a href="' . $RootPath . '/SOTodyReceive.php?UpdateCustomerCode=' . $_SESSION['UserID'] . '" target="_blank"><font style="color:red" siza="6">'. $receive_count        ?></td>

</tr>
<?php }  ?>

<?php if ($_SESSION['DepartCode']=='销售部苏州' || $_SESSION['DepartCode']=='销售部武汉' || $_SESSION['DepartCode']=='销售部江西' || $_SESSION['DepartCode']=='销售部') { ?>

 <tr>
  <td>今天下单量:<?php echo '<font style="color:red" siza="6">'. $so_count        ?></td>
   <td>待主管签核量:<?php echo '<a href="' . $RootPath . '/SearchSoForApprove.php?UpdateCustomerCode=' . $_SESSION['UserID'] . '" target="_blank"><font style="color:red" siza="6">' . $so_gongcheng_count      ?></td>
<td>待财务部签量:<?php echo '<a href="' . $RootPath . '/ARFinApprove.php?UpdateCustomerCode=' . $_SESSION['UserID'] . '" target="_blank"><font style="color:red" siza="6">' . $so_caiwu_count      ?></td>

 <td>当天出货量:<?php echo '<font style="color:red" siza="6">' . $chuhuo_count      ?></td>
 <td>订单未建BOM量:
 <?php echo '<a href="' . $RootPath . '/SoNoBom.php?UpdateCustomerCode=' . $_SESSION['UserID'] . '" target="_blank"><font style="color:red" size="2">'. $no_bom_count  ?></td>

</tr>
<?php }  ?>

<?php if ($_SESSION['DepartCode']=='仓库部') { ?>

 <tr>
	 <td>今天业务下单量:<?php echo '<a href="' . $RootPath . '/SOTodycreate.php?UpdateCustomerCode=' . $_SESSION['UserID'] . '" target="_blank"><font style="color:red" siza="6">'. $so_count        ?></td>
	<td>今天采购下单量:
 <?php echo '<a href="' . $RootPath . '/POTodyCreate.php?UpdateCustomerCode=' . $_SESSION['UserID'] . '" target="_blank"><font style="color:red" siza="6">'. $POTodyCreate   ?></td>

<td> 当天需到货未到货:<?php echo '<a href="' . $RootPath . '/POTodyWaitReceive.php?UpdateCustomerCode=' . $_SESSION['UserID'] . '" target="_blank"><font style="color:red" siza="6">' . $wait_re_quantity         ?></td>

</tr>
<?php }  ?>

<?php if ($_SESSION['DepartCode']=='技术部') { ?>

 <tr> 
 <td>待处理报价单量:<?php echo '<a href="' . $RootPath . '/QuoteRequestDo.php?UpdateCustomerCode=' . $_SESSION['UserID'] . '" target="_blank"><font style="color:red" siza="6">'. $baojia_count        ?></td>
  
	 <td>今天业务下单量:<?php echo '<a href="' . $RootPath . '/SOTodycreate.php?UpdateCustomerCode=' . $_SESSION['UserID'] . '" target="_blank"><font style="color:red" siza="6">'. $so_count        ?></td>
	<td>今天采购下单量:
 <?php echo '<a href="' . $RootPath . '/POTodyCreate.php?UpdateCustomerCode=' . $_SESSION['UserID'] . '" target="_blank"><font style="color:red" siza="6">'. $POTodyCreate   ?></td>
<td>订单未建BOM量:
 <?php echo '<a href="' . $RootPath . '/SoNoBom.php?UpdateCustomerCode=' . $_SESSION['UserID'] . '" target="_blank"><font style="color:red" size="2">'. $no_bom_count  ?></td>

</tr>
<?php }  ?>

</table>  -->
	
</div>
<?php
echo '<div style="float:right;" >
			<ul  style="list-style-type:none" >';
echo '			<li style="float:right;margin:10px;padding:0px;">
					<a  target="_parent" href="' . $RootPath . '/Logout.php" onclick="return confirm(\'' . _('Are you sure you wish to logout?') . '\');">
						<img style="width:20xp;height:20px;"  src="' . $RootPath . '/css/' . $Theme . '/images/dengchu.png" />
					</a>
				</li>';
echo '			<li style="float:right;margin:10px;padding:0px;">
					<a target="right" href="' . $RootPath . '/WWW_User3.php">
						<img style="width:20xp;height:20px;"  src="' . $RootPath . '/css/' . $Theme . '/images/xiugai.png" />
					</a>
				</li>';
echo '			<li style="float:right;margin:10px;padding:0px;">
					<a id="state"   href="###">
						<img style="width:20xp;height:20px;"  src="' . $RootPath . '/css/' . $Theme . '/images/xiaoxi.png" />
					</a>
				</li>';
echo '</ul></div>'; // QuickMenuDiv
echo '<div id="msg" style="float:right;top:45px;position:absolute;right:30px;background-color:#fbc118;width:300px;display:none;">';
echo '</div>'; // QuickMenuDiv
echo '</div></div>'; // HeaderWrapDi
echo '
</div>
<div >';
?>






<!DOCTYPE html>
<html>

<head>
	<style>
		/* 左侧超链接列表 */
		.link {
			display: block;
			padding: 8px;
			background-color: #f2f2f2;
			cursor: pointer;
			/* border: 1px solid black; */
		}

		/* 顶部左侧隐藏图标 */
		.line1 {
			display: flex;
			align-items: center;
			width: 2%;
		}

		/* 顶部标签栏 */
		#tabsContainer {
			display: flex;
			align-items: center;
			overflow-x: auto;
			/* 添加横向滚动 */
			white-space: nowrap;
			/* 防止标签换行 */
			width: 96%;

		}

		#tabsContainer::-webkit-scrollbar {
			display: none;
			/* 隐藏Webkit浏览器的滚动条 */
		}

		.line3 {
			display: flex;
			align-items: center;
			width: 2%;
		}

		/* 删除全部页面 */
		.all_close {
			cursor: pointer;
			display: none;
			padding: 5px;
			border-radius: 5px;
		}

		.tab_position:hover .all_close {
			display: block;
			background-color: #ccc;
		}

		.tab_position {
			width: 100%;
			display: flex;
			align-items: center;
			justify-content: center;
		}

		/* 标签页 */
		.tab {
			display: flex;
			align-items: center;
			background-color: #ccc;
			cursor: pointer;
			position: relative;
			font-size: 14px;
			margin-right: 10px;
			border-radius: 3px;
		}

		/* 标签标题 */
		.tab .title {
			padding: 5px 15px;
		}

		/* 标签关闭按钮 */
		.tab .close-btn {
			font-size: 10px;
			font-weight: bold;
			cursor: pointer;
			padding: 5px;
		}

		/* 选中的标签 */
		.tab.active {
			border: 1px solid #cccccc;
			background-color: #f2f2f2;
		}

		/* 右侧内容 */
		#content {
			padding: 10px;
			overflow: auto;
			height: 95%;
		}

		/* 左侧导航栏 */
		.leftnav_all {
			float: left;
			width: 150px;
			background-color: #1e2e3d;
			height: calc(100% - 60px);
			font-size: 14px;
		}

		#left1 {
			z-index: 9999;
			overflow: visible;
		}

		.leftnav_first_ul {
			list-style-type: none;
			background-color: #1e2e3d;
			width: 150px !important;
			height: calc(100% - 60px);
			position: fixed;
			padding: 0px;
			margin: 0px;
			overflow: visible;
			/* overflow-y: scroll;
			overflow-x: hidden; */
		}

		.leftnav_first_li {
			list-style-type: none;
			padding: 10px 0 10px 0;
			width: 100%;
		}

		/* 第二层 */
		.leftnav_second_ul {
			display: none;
			position: relative;
			list-style-type: none;
			background-color: #226a9e;
			padding: 0px !important;
			margin: 10px 0px -10px 0px !important;
			line-height: 0;
		}
	</style>
</head>

<body>
	<div id="left" class="leftnav_all">
		<ul id="left1" class="leftnav_first_ul">
			<?php
			$i = 0;
			while ($i < count($ModuleLink)) {
				//第一层
				if ($_SESSION['ModulesEnabled'][$i] == 1) {
					$arr = array(
						'销售' => 'orders',
						'采购' => 'Purchase',
						'刀具' => 'DJ',
						'仓库' => 'INV',
						'BOM' => 'BOM',
						'生产' => 'WIP',
						'外协' => 'OSP',
						'品质' => 'QM',
						'财务' => 'FIN',
						'应付' => 'AP',
						'应收' => 'AR' ,
						'技服' => 'OSP' ,
						'月结' => 'AP' ,
						'固定资产' => 'AP',
						'模具' => 'MES',
						'人事' => 'OSP',
						'系统设置' => 'system'
					);
					echo '<li class="litem leftnav_first_li">
                        <a href="##" style="color:#fff;text-decoration:none;display:flex;align-items:center;">
                            <img style="width:15px;height:15px;margin-right:2px;" src="' . $RootPath . '/css/' . $Theme . '/images/' . $arr[$ModuleList[$i]] . '.png" />
                            <span>' . $ModuleList[$i] . '</span>
                            <img style="width:10px;height:10px;position:absolute;right:20px;" src="' . $RootPath . '/css/' . $Theme . '/images/jiantou.png" />
                        </a>';

					echo '<ul class="uitem leftnav_second_ul" ><br/>';

					// 输出子菜单项
					$_SESSION['Module'] = $ModuleLink[$i];
					if ($_SESSION['Module'] == 'system') {
						include 'sanjicaidan2.php';
					} else {
						include 'sanjicaidan.php';
					}

					echo '</ul>';
					echo '</li>';
				}
				$i++;
			}
			?>
		</ul>
	</div>

	<!-- 右侧内容 -->
	<div id="right" style="width:85%;height:90%;float:right;position:fixed;right:0px;top:75px;background-color:#F0F0F0;">
		<div class="tab_position">
			<div class="line1" id="yincang">
				<a href="#">
					<?php
					echo '<img style="padding: 8px 0 8px 2px;width:15px;height:15px;" src="' . $RootPath . '/css/' . $Theme . '/images/liebiao.png" />';
					?>
				</a>
			</div>
			<div id="tabsContainer"></div>
			<div class="line3">
				<div class="all_close" onclick="all_close()">X</div>
			</div>
		</div>

		<div id="content">

		</div>
	</div>

	<script>
		//隐藏左侧导航栏
		const a = $(window).width();
		$("#yincang").click(function() {
			if (!$("#left").width()) {
				$("#left").animate({
					width: 150
				}, "slow");
				$(".litem").show(300);
				$("#right").animate({
					width: a - 150
				}, "slow");
			} else {
				$("#left").animate({
					width: 0
				}, "slow");
				$(".litem").hide(300);
				$("#right").animate({
					width: a
				}, "slow");
			}
		});

		// 初始化标签页容器
		var tabsContainer = document.getElementById("tabsContainer");
		var contentContainer = document.getElementById("content");
		var activeTab = null;

		// 加载内容到右侧窗口
		function loadContent(url, tabName) {
			var existingTab = tabsContainer.querySelector('[data-url="' + url + '"]');
			if (existingTab) {
				setActiveTab(existingTab);
				return;
			}
			// 检查标签页数量是否已达上限
			var tabs = Array.from(tabsContainer.getElementsByClassName('tab'));
			if (tabs.length >= 15) {
				alert('已达到标签页数量上限,请先关闭一些页面！');
				return;
			}
			// 隐藏当前页面内容
			if (activeTab) {
				activeTab.classList.remove('active');
				var activeUrl = activeTab.getAttribute('data-url');
				var activeContent = document.getElementById(activeUrl);
				activeContent.style.display = 'none';
			}
			// 显示新页面内容
			var content = document.getElementById(url);
			if (content) {
				content.style.display = 'block';
			} else {
				content = document.createElement('div');
				content.id = url;
				content.style.display = 'block';
				contentContainer.appendChild(content);
				var iframe = document.createElement('iframe');
				iframe.src = url;
				iframe.frameborder = '0';
				iframe.style.width = '100%';
				iframe.style.height = '100%';
				iframe.style.border = 'none';
				content.appendChild(iframe);
			}
			createTabAndSetActive(url, tabName);
		}
		// 如果没有活动标签页或者关闭了全部标签页，则加载默认页面
		if (!activeTab || tabsContainer.getElementsByClassName('tab').length === 0) {
			//******************************** 
			var default_lab = JSON.parse('<?php echo json_encode($default_lab); ?>');
			//********************************* 
			loadContent(default_lab[0], default_lab[1]);
		
		}
		// 创建标签并将其激活
		function createTabAndSetActive(url, tabName) {
			// 如果标签已存在，则激活该标签
			var tab = tabsContainer.querySelector('[data-url="' + url + '"]');
			// console.log(tab);
			if (tab) {
				setActiveTab(tab);
				return;
			}
			var tab = document.createElement("div");
			tab.className = "tab";
			tab.setAttribute('data-url', url);
			var title = document.createElement("span");
			title.className = "title";
			title.innerText = tabName;
			tab.appendChild(title);
			// console.log(tab)
			var closeBtn = document.createElement("span");
			closeBtn.innerText = "X";
			closeBtn.className = "close-btn";
			//关闭默认页面的关闭按钮
			//******************************** 
			var default_lab = JSON.parse('<?php echo json_encode($default_lab); ?>');
			//********************************* 
			var salePath = default_lab[0];
			if (url === salePath) {
				closeBtn.style.display = 'none';
			} else {
				closeBtn.addEventListener("click", function(event) {
					event.stopPropagation();
					closeTab(tab);
				});
			}
			tab.appendChild(closeBtn)
			//点击标签页切换内容
			tab.addEventListener("click", function() {
				loadContent(url, tabName);
				setActiveTab(tab);
			});
			tabsContainer.appendChild(tab);
			setActiveTab(tab);
		}
		// 设置活动标签页样式
		function setActiveTab(tab) {
			var tabs = Array.from(tabsContainer.getElementsByClassName('tab'));
			tabs.forEach(function(t) {
				t.classList.remove('active');

				// 隐藏标签页对应的内容
				var tabUrl = t.getAttribute('data-url');
				var tabContent = document.getElementById(tabUrl);
				if (tabContent) {
					tabContent.style.display = 'none';
				}
			});

			tab.classList.add('active');
			activeTab = tab;

			// 显示当前标签页对应的内容
			var url = tab.getAttribute('data-url');
			var content = document.getElementById(url);
			if (content) {
				content.style.display = 'block';
			}
		}
		// 关闭标签页
		function closeTab(tab) {
			var isActiveTab = (tab === activeTab);
			var nextActiveTab = tab.previousElementSibling || tab.nextElementSibling;
			var tabUrl = tab.getAttribute('data-url');
			var tabContent = document.getElementById(tabUrl);
			tab.parentNode.removeChild(tab);
			tabContent.parentNode.removeChild(tabContent); // 移除对应的内容
			if (isActiveTab) {
				activeTab = null; // 清空活动标签页
			}
			// 如果关闭的是活动标签页且还有其他标签页存在，则将页面锁定到下一个标签页
			if (isActiveTab && nextActiveTab) {
				setActiveTab(nextActiveTab);
				loadContent(nextActiveTab.getAttribute('data-url'), nextActiveTab.querySelector('.title').innerText);
			}
			// 如果关闭的是活动标签页且没有其他标签页存在，则清空内容和活动标签页
			if (isActiveTab && !nextActiveTab) {
				contentContainer.innerHTML = '';
			}
		}

		// // 关闭标签页
		// function closeTab(tab) {
		// 	var isActiveTab = (tab === activeTab);
		// 	var nextActiveTab = tab.previousElementSibling || tab.nextElementSibling;
		// 	tab.parentNode.removeChild(tab);
		// 	// 如果关闭的是活动标签页且还有其他标签页存在，则将页面锁定到下一个标签页
		// 	if (isActiveTab && nextActiveTab) {
		// 		setActiveTab(nextActiveTab);
		// 		loadContent(nextActiveTab.getAttribute('data-url'), nextActiveTab.querySelector('.title').innerText);
		// 	}
		// 	// 如果关闭的是活动标签页且没有其他标签页存在，则清空内容和活动标签页
		// 	if (isActiveTab && !nextActiveTab) {
		// 		contentContainer.innerHTML = '';
		// 		activeTab = null;
		// 	}
		// 	// 隐藏当前页面内容
		// 	if (isActiveTab) {
		// 		var activeUrl = tab.getAttribute('data-url');
		// 		var activeContent = document.getElementById(activeUrl);
		// 		activeContent.style.display = 'none';
		// 	}
		// }
		// 关闭全部未激活标签页
		function all_close() {
			var confirmation = confirm('是否关闭全部标签页？'); // 显示确认和取消提示框
			if (confirmation) {
				var tabs = Array.from(tabsContainer.getElementsByClassName('tab'));
				tabs.forEach(function(tab) {
					if (!tab.classList.contains('active')) {
						closeTab(tab);
					}
				});
				// 清空内容、活动标签页和标签导航栏
				contentContainer.innerHTML = '';
				activeTab = null;
				tabsContainer.innerHTML = '';
				//******************************** 
				var default_lab = JSON.parse('<?php echo json_encode($default_lab); ?>');
				//********************************* 
				var salePath = default_lab[0];
				// 加载默认页面
				loadContent(salePath, default_lab[1]);
			} else {
				console.log('取消')
			}
		}
		// 监听菜单项的点击事件
		var menuLinks = document.querySelectorAll(".uitem > li > ul >li >a");
		menuLinks.forEach(function(link) {
			link.addEventListener("click", function(e) {
				e.preventDefault();
				var url = this.href;
				var tabName = this.innerText;
				loadContent(url, tabName);
			});
		});
		//顶部标签栏的左右滑动效果
		var isMouseDown = false;
		var startX = 0;
		var scrollLeft = 0;

		tabsContainer.addEventListener('mousedown', function(e) {
			isMouseDown = true;
			startX = e.pageX - tabsContainer.offsetLeft;
			scrollLeft = tabsContainer.scrollLeft;
		});

		tabsContainer.addEventListener('mouseleave', function() {
			isMouseDown = false;
		});

		tabsContainer.addEventListener('mouseup', function() {
			isMouseDown = false;
		});

		tabsContainer.addEventListener('mousemove', function(e) {
			if (!isMouseDown) return;
			e.preventDefault();
			var x = e.pageX - tabsContainer.offsetLeft;
			var walk = (x - startX) * 1; // 调整滚动速度
			tabsContainer.scrollLeft = scrollLeft - walk;
		});
	</script>