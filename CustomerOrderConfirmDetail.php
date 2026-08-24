<?php 
	include('includes/session.inc');
	$Title = _('订单资料确认');

	$ViewTopic= '订单资料确认';
	$BookMark = '订单资料确认';

	include('includes/header.inc');
	include('includes/SQL_CommonFunctions.inc');
	if (isset($_GET['OrderNum']) or isset($_POST['OrderNum'])) {
		if (!isset($_POST['StatusCode']) or $_POST['StatusCode']=='') {
			$OrderNum = isset($_GET['OrderNum'])?$_GET['OrderNum']:$_POST['OrderNum'];
			$sql = "select order_number,customer_number,doc_path,urgent_flag,purchase_flag,purchase_remarks,schedule_ship_date,customer_name,sales_man_name,order_quantity,unit_price,gerber_flag,gerber_remarks,coordinate_flag,coordinate_remarks,bom_flag,bom_remarks,point_flag,point_remarks,exception_remarks,confirm_date from sf_orders_all a,www_users b 
			where  b.userid=a.sales_man_id and order_number = '".$OrderNum."'";
			$result=DB_query($sql, $db);
			while ($v=DB_Fetch_Array($result)) {
				$_POST['OrderNum'] = $v['order_number'];
				$_POST['CustomerNum'] = $v['customer_number'];
				$_POST['CustomerName'] = $v['customer_name'];
				$_POST['SalesName'] = $v['sales_man_name'];
				$_POST['SteelFlag'] = $v['purchase_flag'];
				$_POST['SteelRE'] = $v['purchase_remarks'];
				$_POST['ScheduleDate'] = date('Y-m-d',$v['schedule_ship_date']);
				$_POST['OrderQty'] = $v['order_quantity'];
				$_POST['DocPath'] = $v['doc_path'];
				$_POST['UrgentFlag'] = $v['urgent_flag'];
				$_POST['UnitPrice'] = $v['unit_price'];
				$_POST['Gerber'] = $v['gerber_flag'];
				$_POST['GerberRe'] = $v['gerber_remarks'];
				$_POST['CoordInateFlag'] = $v['coordinate_flag'];
				$_POST['CoordInateRe'] = $v['coordinate_remarks'];
				$_POST['BomFlag'] = $v['bom_flag'];
				$_POST['BomRe'] = $v['bom_remarks'];
				$_POST['PointFlag'] = $v['point_flag'];
				$_POST['PointRe'] = $v['point_remarks'];
				$_POST['ExceptionRe'] = $v['exception_remarks'];
				$_POST['StatusCode'] = 'Y';
				$_POST['ConfirmDate'] = date('Y-m-d H:i:s',$v['confirm_date']<strtotime('1971-01-01')?strtotime("now"):$v['confirm_date']);
			}
		}

	}else{
		prnMsg( _('请先选择订单，即将跳转订单查询界面'), 'error');
		header("Location: CustomerOrderConfirm.php");
	}
	if (isset($_POST['Confirm'])) {
		if ($_POST['StatusCode']=="Y" or $_POST['StatusCode']=="WorkConfirm") {
			$ConfirmDate = strtotime($_POST['ConfirmDate']);
			$sql = "update sf_orders_all set gerber_flag='".$_POST['Gerber']."',gerber_remarks='".$_POST['GerberRe']."',coordinate_flag='".$_POST['CoordInateFlag']."',coordinate_remarks='".$_POST['CoordInateRe']."',bom_flag='".$_POST['BomFlag']."',bom_remarks='".$_POST['BomRe']."',point_flag='".$_POST['PointFlag']."',point_remarks='".$_POST['PointRe']."',exception_remarks='".$_POST['ExceptionRe']."',confirm_date='".$ConfirmDate."',order_flag = 'Y' where order_number='".$_POST['OrderNum']."' ";
			 $result=DB_query($sql, $db);
			prnMsg( _('订单资料确认完成'), 'success');
		}
	}
?>
<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>/images/transactions.png" title="订单资料确认" alt="订单资料确认">订单资料确认</p>
<div class="centre"><a href="CustomerOrderConfirm.php">返回选择订单</a></div>
<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST">
<div>
	<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
	
	<table class="selection">
		<tr>
			<td>订单编号：</td>
			<td><?=$_POST['OrderNum'] ?></td>
			<input type="hidden" name="OrderNum" value="<?=$_POST['OrderNum']?>">
		</tr>
		<tr>
			<td>客户编号：</td>
			<td><?=$_POST['CustomerNum']?></td>
			<input type="hidden" name='customer_code' value='<?=$_POST['CustomerNum']?>'>
		</tr>
		<tr>
			<td>客户名称：</td>
			<td><?=$_POST['CustomerName'] ?></td>
                <input type="hidden" name="customer_name"  value ="<?=$_POST['CustomerName'] ?>">
		</tr>
		<tr>
			<td>业务人员：</td>
			<td><?=$_SESSION['UsersRealName']?>
                             <input type="hidden" name="SalesManID"  value ="<?=$_SESSION['UserID'] ?>">
                            </td>
		</tr>
		<tr>
			<td>订单数量：</td>
			<td><?=$_POST['OrderQty'] ?></td>
			<td><input type="hidden" name="OrderQty" maxlength="20" size="10" value ="<?=$_POST['OrderQty'] ?>"></td>
		</tr>
		</tr>
			<tr>
			<td>预计出货时间：</td>
			<td><?=$_POST['ScheduleDate']?></td>
			<input type="hidden" name='ScheduleDate' value='<?=$_POST['ScheduleDate']?>'>
		</tr>
		</tr>
			<tr>
			<td>资料路径：</td>
			<td><?=$_POST['DocPath']?></td>
			<input type="hidden" name='DocPath' value='<?=$_POST['DocPath']?>'>
		</tr>
		<tr>
			<td>金额：</td>
			<td><?=$_POST['UnitPrice'] * $_POST['OrderQty']?></td>
			<td><input type="hidden" name="UnitPrice" maxlength="20" size="10" value ="<?=$_POST['UnitPrice'] * $_POST['OrderQty']?>"></td>
		</tr>
		<tr>
			<td>是否钢网采购：</td>
			<td>
				<?php 
					if ($_POST['SteelFlag']=='Y') {
						echo "是";
					}else{
						echo "否";
					}
				?></td>
			<input type="hidden" name='SteelFlag' value='<?=$_POST['SteelFlag']?>'>
		</tr>
		<tr>
			<td>钢网注意事项：</td>
			<td><?=$_POST['SteelRE']?></td>
			<input type="hidden" name='SteelRE' value='<?=$_POST['SteelRE']?>'>
		</tr>
		<tr>
			<td>是否加急：</td>
			<td>
				<?php 
					if ($_POST['UrgentFlag']=='Y') {
						echo "是";
					}else{
						echo "否";
					}
				?></td>
			<input type="hidden" name='UrgentFlag' value='<?=$_POST['UrgentFlag']?>'>
		</tr>

		<tr>
			<td>Gerber是否通过：</td>
			<td>
				<?php
					if ($_POST['Gerber']=='Y') {
				?>
				<input type="radio" name="Gerber" value='Y' checked=checked>是
				<input type="radio" name="Gerber" value='N' >否
				<input type="radio" name="Gerber" value='I' >待处理
				<?php
					}elseif ($_POST['Gerber']=='N'){ 
				?>
				<input type="radio" name="Gerber" value='Y' >是
				<input type="radio" name="Gerber" value='N' checked=checked>否
				<input type="radio" name="Gerber" value='I' >待处理
				<?php
					}else{
				?>
				<input type="radio" name="Gerber" value='Y' >是
				<input type="radio" name="Gerber" value='N' >否
				<input type="radio" name="Gerber" value='I' checked=checked>待处理
				<?php }?>
			</td>
		</tr>
		<tr>
			<td>Gerber备注：</td>
			<td><input type="text" name="GerberRe" value="<?=$_POST['GerberRe']?>" size="30"></td>
		</tr>

		<tr>
			<td>坐标文件是否通过：</td>
			<td>
				<?php
					if ($_POST['CoordInateFlag']=='Y') {
				?>
				<input type="radio" name="CoordInateFlag" value='Y' checked=checked>是
				<input type="radio" name="CoordInateFlag" value='N' >否
				<input type="radio" name="CoordInateFlag" value='I' >待处理
				<?php
					}elseif ($_POST['CoordInateFlag']=='N'){ 
				?>
				<input type="radio" name="CoordInateFlag" value='Y' >是
				<input type="radio" name="CoordInateFlag" value='N' checked=checked>否
				<input type="radio" name="CoordInateFlag" value='I' >待处理
				<?php
					}else{
				?>
				<input type="radio" name="CoordInateFlag" value='Y' >是
				<input type="radio" name="CoordInateFlag" value='N' >否
				<input type="radio" name="CoordInateFlag" value='I' checked=checked>待处理
				<?php }?>
			</td>
		</tr>
		<tr>
			<td>坐标文件备注：</td>
			<td><input type="text" name="CoordInateRe" value="<?=$_POST['CoordInateRe']?>" size="30"></td>
		</tr>

		<tr>
			<td>BOM文件是否通过：</td>
			<td>
				<?php
					if ($_POST['BomFlag']=='Y') {
				?>
				<input type="radio" name="BomFlag" value='Y' checked=checked>是
				<input type="radio" name="BomFlag" value='N' >否
				<input type="radio" name="BomFlag" value='I' >待处理
				<?php
					}elseif ($_POST['BomFlag']=='N'){ 
				?>
				<input type="radio" name="BomFlag" value='Y' >是
				<input type="radio" name="BomFlag" value='N' checked=checked>否
				<input type="radio" name="BomFlag" value='I' >待处理
				<?php
					}else{
				?>
				<input type="radio" name="BomFlag" value='Y' >是
				<input type="radio" name="BomFlag" value='N' >否
				<input type="radio" name="BomFlag" value='I' checked=checked>待处理
				<?php }?>
			</td>
		</tr>
		<tr>
			<td>BOM文件备注：</td>
			<td><input type="text" name="BomRe" value="<?=$_POST['BomRe']?>" size="30"></td>
		</tr>

		<tr>
			<td>点料是否通过：</td>
			<td>
				<?php
					if ($_POST['PointFlag']=='Y') {
				?>
				<input type="radio" name="PointFlag" value='Y' checked=checked>是
				<input type="radio" name="PointFlag" value='N' >否
				<input type="radio" name="PointFlag" value='I' >待处理
				<?php
					}elseif ($_POST['BomFlag']=='N'){ 
				?>
				<input type="radio" name="PointFlag" value='Y' >是
				<input type="radio" name="PointFlag" value='N' checked=checked>否
				<input type="radio" name="PointFlag" value='I' >待处理
				<?php
					}else{
				?>
				<input type="radio" name="PointFlag" value='Y' >是
				<input type="radio" name="PointFlag" value='N' >否
				<input type="radio" name="PointFlag" value='I' checked=checked>待处理
				<?php }?>
			</td>
		</tr>
		<tr>
			<td>点料备注：</td>
			<td><input type="text" name="PointRe" value="<?=$_POST['PointRe']?>" size="30"></td>
		</tr>
		
		<tr>
			<td>异常原因说明：</td>
			<td><textarea name="ExceptionRe" id="t1" cols="30" rows="3"><?=$_POST['ExceptionRe']?></textarea> 
		</td>
		</tr>

		<tr>
			<td>审核日期：</td>
			<td><?php
				if (isset($_POST['ConfirmDate']) or $_POST['ConfirmDate']=='') {
					echo date('Y-m-d H:i:s');
				}else{
					echo $_POST['ConfirmDate'];
				}
			?></td>
			<input type="hidden" name='ConfirmDate' value='<?=date('Y-m-d H:i:s')?>'>
		</tr>

		<input type="hidden" name="StatusCode" value="<?=$_POST['StatusCode']?>">
	</table>

	<div class="centre"><input type="submit" name="Confirm" value="确认"></div>

</div>
</form>
<?php
  include('includes/footer.inc');
?>