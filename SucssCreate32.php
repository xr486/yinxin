<?php
include('includes/session.inc');
$Title = '仓库盘盈单创建';
include('includes/header.inc');
$OrderNum=$_GET['OrderNum'];
$msg = '仓库盘盈单编号'.$OrderNum.'建立成功！';
                prnMsg($msg, success);
                 echo '<br /><div class="centre"><a href="' . $RootPath . '/InventoryProfit.php">' . _('继续创建仓库盘盈单') . '</a></div>';
                 echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . '仓库盘盈单创建' .
 '" alt="" />' . ' ' . $Title . '
	</p>';


?>