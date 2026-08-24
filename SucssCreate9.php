<?php
include('includes/session.inc');
$Title = '盘盈入库单创建';
include('includes/header.inc');
$OrderNum=$_GET['OrderNum'];
$msg = '盘盈入库单编号'.$OrderNum.'建立成功！';
                prnMsg($msg, success);
                 echo '<br /><div class="centre"><a href="' . $RootPath . '/InventoryProfit.php">' . _('继续创建盘盈入库单') . '</a></div>';
                 echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . '盘盈入库单创建' .
 '" alt="" />' . ' ' . $Title . '
	</p>';


?>