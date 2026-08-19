<?php
include('includes/session.inc');
$Title = '工单退料创建';
include('includes/header.inc');
$OrderNum=$_GET['OrderNum'];
$msg = '退料单单编号'.$OrderNum.'建立成功！';
                prnMsg($msg, success);
                 echo '<br /><div class="centre"><a href="' . $RootPath . '/wipreturnsegment.php">' . _('继续创建退料单') . '</a></div>';
                 echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . '退料单创建' .
 '" alt="" />' . ' ' . $Title . '
	</p>';


?>