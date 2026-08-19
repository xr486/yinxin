<?php
include('includes/session.inc');
$Title = '仓库盘亏单处理成功';
include('includes/header.inc');
$OrderNum=$_GET['OrderNum'];
$msg = '仓库盘亏单编号'.$OrderNum.'建立成功！';
                prnMsg($msg, success);
                 echo '<br /><div class="centre"><a href="' . $RootPath . '/InventoryLoss.php">' . _('继续仓库盘亏处理') . '</a></div>';
                 echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . '仓库盘亏处理' .
 '" alt="" />' . ' ' . $Title . '
	</p>';


?>