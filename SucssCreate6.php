<?php
include('includes/session.inc');
$Title = '其他原因出库单创建';
include('includes/header.inc');
$OrderNum=$_GET['OrderNum'];
$msg = '出库单编号'.$OrderNum.'建立成功！';
                prnMsg($msg, success);
                 echo '<br /><div class="centre"><a href="' . $RootPath . '/OutHouse.php">' . _('继续创建其他原因出库单') . '</a></div>';
                 echo '<a href="' . $RootPath . '/PrintOutHouse.php?Updatedelivery_num='.$OrderNum.'" target="_blank"  >打印</a>';
                 echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . '出库单创建' .
 '" alt="" />' . ' ' . $Title . '
	</p>';


?>