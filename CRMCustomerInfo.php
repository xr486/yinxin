<?php 
	include('includes/session.inc');
	$Title = _('客户详细信息');

	$ViewTopic= '客户详细信息';
	$BookMark = '客户详细信息';

	include('includes/header.inc');
	include('includes/SQL_CommonFunctions.inc');

	if (isset($_GET['customer_id'])) {
		$customer_id = $_GET['customer_id'];
	}

	if (!isset($ItemID)) {
		header('Location: CRMCustmerCreate.php');
	}

		$uploadflag = 1;

	$sql = "select * from  customers where customer_id= '".$customer_id."' ";
	$result = DB_query($sql,$db);
	while ($v = DB_fetch_array($result)) {
		$_POST['customer_code'] = $v['customer_code'];
		$_POST['customer_name'] = $v['customer_name'];
		$_POST['personal_resource'] = $v['personal_resource'];
		$_POST['customers_type'] = $v['customers_type'];
		$_POST['customers_area'] = $v['customers_area'];
		$_POST['intent'] = $v['intent'];
		$_POST['industry'] = $v['industry'];
		$_POST['customers_category'] = $v['customers_category'];
		$_POST['UnitPrice'] = $v['unit_price'];
		$_POST['customer_address'] = $v['customer_address'];
        $_POST['po_price'] = $v['po_price'];
		$_POST['Category'] = $v['item_category'];
		$_POST['Flag'] = $v['disable_flag'];
        $_POST['item_spec'] = $v['item_spec'];
		$_POST['lead_time'] = $v['lead_time'];
		$_POST['manufacture_time'] = $v['manufacture_time'];
		$_POST['yanse'] = $v['yanse'];
                
	}
?>
<div class="centre"><a href="<?=$RootPath?>/Searchmold.php">返回查找料号</a></div>
<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/maintenance.png" title="修改料号" alt="修改料号">修改料号</p>
<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST" enctype="multipart/form-data">
<div>
	<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
<br>
<table class="selection">
	<tr>
		<td>客户编号：<?=$_POST['customer_code']?> </td>
	</tr>
	<tr>
	<td>客户名称：<?=$_POST['customer_name']?> </td>
	</tr>
	<tr>
    <td>类型：<?=$_POST['customers_type']?> </td>
	<td>个人入库来源：<?=$_POST['personal_resource']?> </td>
	</tr>
	<tr>
<td>地区：<?=$_POST['customers_area']?> </td></tr>
	<tr>
	<td>当前意向：<?=$_POST['intent']?> </td>
	<td>行业：<?=$_POST['industry']?> </td>
	</tr>
</table>
</div>
</form>
<?php
  include('includes/footer.inc');
?>