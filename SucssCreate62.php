<?php
include('includes/session.inc');
$Title = '工单委外生产领用单创建';
include('includes/header.inc');
$OrderNum=$_GET['OrderNum'];
$msg = '工单委外生产领用单编号'.$OrderNum.'建立成功！';
                prnMsg($msg, success);
                 echo '<br /><div class="centre"><a href="' . $RootPath . '/OutHouseWipWeiWai.php">' . _('继续创建工单委外生产领用单') . '</a></div>';
                 echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . '工单委外生产领用单创建' .
 '" alt="" />' . ' ' . $Title . '
	</p>';


?>