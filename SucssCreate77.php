<?php
include('includes/session.inc');
$Title = '外协采购单收货创建';
include('includes/header.inc');
$OrderNum=$_GET['OrderNum'];
$msg = '外协采购单收货单号'.$OrderNum.'建立成功！';
                prnMsg($msg, success);
                 echo '<br /><div class="centre"><a href="' . $RootPath . '/OSPSearchPOReceipt.php">' . _('继续创建外协采购单收货') . '</a></div>';
                 echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . '外协采购单收货' .
 '" alt="" />' . ' ' . $Title . '
	</p>';


?>