<?php
include('includes/session.inc');
$Title = '调拨单创建';
include('includes/header.inc');
$OrderNum=$_GET['OrderNum'];
$msg = '调拨单编号'.$OrderNum.'建立成功！';
                prnMsg($msg, success);
                 echo '<br /><div class="centre"><a href="' . $RootPath . '/InchangeHouse.php">' . _('继续创建调拨单') . '</a></div>';
                 echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . '调拨单创建' .
 '" alt="" />' . ' ' . $Title . '
	</p>';


?>