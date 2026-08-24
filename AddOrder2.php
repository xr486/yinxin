<?php 
	include('includes/session.inc');
	$Title = _('建立订单');

	$ViewTopic= '建立订单';
	$BookMark = '建立订单';

	include('includes/header.inc');
	include('includes/SQL_CommonFunctions.inc');

	if (isset($_POST['Update'])) {
		
		$date=date('YmdHi');
		$sql = "select max(substr(order_number,-1))+1 number from sf_orders_all where substr(order_number,1,12) = '".$date."' ";
		$result=DB_query($sql, $db);
		$rownum = DB_num_rows($result);
		while ($v=DB_fetch_array($result)) {
			if ($v['number']==null) {
				$OrderNum = $date.'1';
			}else{
				$OrderNum = $date.$v['number'];
			}
		}
		$_POST['OrderNum'] = $OrderNum;
		$CustomerName = $_POST['CustomerName'.$_POST['CustomerID']];
		$SalesManName = $_POST['SalesManName'.$_POST['SalesManID']];
		$ScheduleDate = strtotime($_POST['ScheduleDate']);
		$_POST['DocPath']=str_replace('\\\\','/',$_POST['DocPath']);
		$sql = "insert into sf_orders_all
				(order_number,customer_number,customer_name,sales_man_id,sales_man_name,order_quantity,unit_price,schedule_ship_date,doc_path,urgent_flag,purchase_flag,purchase_remarks)
				values 
				('".$_POST['OrderNum']."','".$_POST['CustomerID']."','".$CustomerName."','".$_POST['SalesManID']."','".$SalesManName."','".$_POST['OrderQty']."','".$_POST['UnitPrice']."','".$ScheduleDate."','".$_POST['DocPath']."','".$_POST['Urgent']."','".$_POST['POFlag']."','".$_POST['PORemarks']."')";
		$result=DB_query($sql, $db);
		prnMsg( _('订单建立成功'), 'success');
		
	}

?>

<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="建立订单" alt="建立订单">建立订单</p>
<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST">
<div>
	<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
	<table class="selection">
		<tr>
			<td>订单编号：</td>
			<td><?=$_POST['OrderNum'] ?></td>
			<input type="hidden" name="OrderNum" value="<?=$_POST['OrderNum']?>">
		</tr>
		<?php
			$sql = "SELECT customer_code,customer_name FROM customers";
			$result=DB_query($sql, $db);
			while($v=DB_fetch_array($result)){
		?>
		<input type="hidden" name="CustomerName<?=$v['customer_code']?>" value="<?=$v['customer_name']?>" >
		<?php }?>
		<tr>
			<td>选择客户：</td>
			<td><select name="CustomerID" id="">
				<?php
				   	$sql = "SELECT customer_code,customer_name FROM customers";
					$result=DB_query($sql, $db); 

					while($v=DB_fetch_array($result)){
						if ($v['customer_code']==$_POST['customer_code']) {		
				?>
				<option value="<?=$v['customer_code']?>" selected="selected"><?=$v['customer_name']?></option>
				<?php }else { ?>
				<option value="<?=$v['customer_code']?>"><?=$v['customer_name']?></option>
				<?php }} ?>
			</select></td>
		</tr>
		<?php
			$sql = "SELECT salesmancode,salesmanname FROM salesmans";
			$result=DB_query($sql, $db);
			while($v=DB_fetch_array($result)){
		?>
		<input type="hidden" name="SalesManName<?=$v['salesmancode']?>" value="<?=$v['salesmanname']?>" />
		<?php }?>
		<tr>
			<td>选择业务人员：</td>
			<td><select name="SalesManID" id="">
				<?php
				   	$sql = "SELECT salesmancode,salesmanname FROM salesmans";
					$result=DB_query($sql, $db); 

					while($v=DB_fetch_array($result)){
						if ($v['salesmancode']==$_POST['salesmancode']) {
				?>
				<option value="<?=$v['salesmancode']?>" selected="selected"><?=$v['salesmanname']?></option>
				<?php }else { ?>
				<option value="<?=$v['salesmancode']?>"><?=$v['salesmanname']?></option>
				<?php }} ?>
			</select></td>
		</tr>
		<tr>
			<td>订单数量：</td>
			<td><input type="text" name="OrderQty" required="required" maxlength="20" size="10" value ="<?=$_POST['OrderQty'] ?>"></td>
		</tr>
		<tr>
			<td>单价：</td>
			<td><input type="text" name="UnitPrice" required="required" maxlength="20" size="10" value ="<?=$_POST['UnitPrice'] ?>"></td>
		</tr>
		<tr>
			<td>预计交货日期：</td>
			<td><input type="text" name="ScheduleDate" required="required" maxlength="20" size="10" value ="<?=$_POST['ScheduleDate'] ?>" onfocus="WdatePicker() "></td>
		</tr>
		<tr>
			<td>资料路径：</td>
			<td><input type="text" name="DocPath" required="required" maxlength="100" size="30" value ='<?echo $_POST['DocPath'] ?>'></td>
		</tr>
		<tr>
			<td>是否钢网采购：</td>
			<td>
				<?php
					if ($_POST['POFlag']=='Y') {
				?>
				<input type="radio" name="POFlag" value='Y' checked=checked>是
				<input type="radio" name="POFlag" value='N' >否
				<?php
					}else{ 
				?>
				<input type="radio" name="POFlag" value='Y' >是
				<input type="radio" name="POFlag" value='N' checked=checked>否
				<?php
					}
				?>
			</td>
		</tr>
		<tr>
			<td>钢网注意事项：</td>
			<td>
				<textarea name="PORemarks" id="" cols="30" rows="3"><?=$_POST['PORemarks'] ?></textarea>
			</td>
		</tr>
		<tr>
			<td>是否加急：</td>
			<td>
				<?php
					if ($_POST['Urgent']=='Y') {
				?>
				<input type="radio" name="Urgent" value='Y' checked=checked>是
				<input type="radio" name="Urgent" value='N' >否
				<?php
					}else{ 
				?>
				<input type="radio" name="Urgent" value='Y' >是
				<input type="radio" name="Urgent" value='N' checked=checked>否
				<?php
					}
				?>
			</td>
		</tr>
	</table>
	<div class="centre"><input type="submit" name="Update" value="提交"></div>
</div>
</form>	
<?php
  include('includes/footer.inc');
?>