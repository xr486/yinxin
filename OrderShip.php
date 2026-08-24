<?php 
	include('includes/session.inc');
	$Title = _('订单出货');

	$ViewTopic= '订单出货';
	$BookMark = '订单出货';

	include('includes/header.inc');
	include('includes/SQL_CommonFunctions.inc');
	if (isset($_GET['OrderNum']) or isset($_POST['OrderNum'])) {
		if (!isset($_POST['CustomerNum']) or $_POST['CustomerNum']=='') {
			$OrderNum = isset($_GET['OrderNum'])?$_GET['OrderNum']:$_POST['OrderNum'];
			$sql = "select order_number,
                                       customer_number,
                                       customer_name,
                                       sales_man_name,
                                       order_quantity,
                                       delivery_qty,
                                       schedule_ship_date ,
                                       unit_price
                                  from sf_orders_all where order_number = '".$OrderNum."'";
			$result=DB_query($sql, $db);
			while ($v=DB_Fetch_Array($result)) {
				$_POST['OrderNum'] = $v['order_number'];
				$_POST['CustomerNum'] = $v['customer_number'];
				$_POST['CustomerName'] = $v['customer_name'];
				$_POST['SalesName'] = $v['sales_man_name'];
				$_POST['Orderqty'] = $v['order_quantity'];
				$_POST['Deliveryqty'] = $v['delivery_qty'];
				$_POST['ScheduleDate'] = date('Y-m-d',$v['schedule_ship_date']);
                                $_POST['order_quantity'] = $v['order_quantity'];
                                $_POST['unit_price'] = $v['unit_price'];
			}
		}

	}else{
		prnMsg( _('请先选择订单，即将跳转订单查询界面'), 'error');
		header("Location: SearchShipOrder.php");
	}
	if (isset($_POST['Confirm'])) {
		$ShipDate = strtotime($_POST['ShipDate']);
		$sql = "update sf_orders_all set ship_qty='".$_POST['ShipQty']."',ship_date='".$ShipDate."',ship_amount='".$_POST['ShipAmount']."',shipment_number='".$_POST['ShipmentNo']."' "
                        . "where order_number='".$_POST['OrderNum']."' ";
		//echo $sql;
                $result=DB_query($sql, $db);
		prnMsg( _('订单资料确认完成'), 'success');
	}

?>
<?php if(!isset($_POST['Print'])){ ?>
<a href="' . $RootPath . '/Print.php?OrderNum=' . $myrow['OrderNum'] . '"></a>
<?php } ?>
<?php if(!isset($_POST['Confirm'])){ ?>
<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="订单出货" alt="订单出货">订单出货</p>

<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST">
<div>
	<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
	<table class="selection">
		<tr>
			<td>订单编号：</td>
			<td><?=$_POST['OrderNum']?></td>
			<input type="hidden" name='OrderNum' value='<?=$_POST['OrderNum']?>'>
		</tr>
			<tr>
			<td>客户编号：</td>
			<td><?=$_POST['CustomerNum']?></td>
			<input type="hidden" name='CustomerNum' value='<?=$_POST['CustomerNum']?>'>
		</tr>
			<tr>
			<td>客户名称：</td>
			<td><?=$_POST['CustomerName']?></td>
			<input type="hidden" name='CustomerName' value='<?=$_POST['CustomerName']?>'>
		</tr>
			<tr>
			<td>业务员：</td>
			<td><?=$_POST['SalesName']?></td>
			<input type="hidden" name='SalesName' value='<?=$_POST['SalesName']?>'>
		</tr>
			<tr>
			<td>订单数量：</td>
			<td><?=$_POST['Orderqty']?></td>
			<input type="hidden" name='Orderqty' value='<?=$_POST['Orderqty']?>'>
		</tr>
		</tr>
			<tr>
			<td>入库数量：</td>
			<td><?=$_POST['Deliveryqty']?></td>
			<input type="hidden" name='Deliveryqty' value='<?=$_POST['Deliveryqty']?>'>
		</tr>
		</tr>
			<tr>
			<td>预计出货时间：</td>
			<td><?=$_POST['ScheduleDate']?></td>
			<input type="hidden" name='ScheduleDate' value='<?=$_POST['ScheduleDate']?>'>
		</tr>
		<tr>
			<td>出货数量：</td>
			<td><input type="text" class="number" required="required" name="ShipQty" size="15" value="<?=$_POST['order_quantity']?>"></td>
		</tr>
		<tr>
			<td>出货金额：</td>
			<td><input type="text" class="number" required="required" name="ShipAmount" size="10" value="<?=$_POST['unit_price']?>"></td>
		</tr>
		<tr>
			<td>快递单号：</td>
			<td><input type="text" name="ShipmentNo" required="required" size="30" value="<?=$_POST['ShipmentNo']?>"></td>
		</tr>
		</tr>
			<tr>
			<td>出货时间：</td>
			<td><?=date('Y-m-d H:i:s')?></td>
			<input type="hidden" name='ShipDate' value='<?=date('Y-m-d H:i:s')?>'>
		</tr>
	</table>

</div>
<div class="centre"><input type="submit" name="Confirm" value="确认"><input type="submit" name="Print" value="打印"></div>
</form>
<?php

}
  include('includes/footer.inc');
?>