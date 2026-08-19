<?php
include('includes/session.inc');
$Title = '退货单创建';
include('includes/header.inc');
$OrderNum=$_GET['OrderNum'];
$msg = '退货单编号'.$OrderNum.'建立成功！';
                prnMsg($msg, success);
                 echo '<br /><div class="centre"><a href="' . $RootPath . '/OrderReturn.php?New=Y">' . _('继续创建退货单') . '</a></div>';
                 // echo '<br /><div class="centre"><a href="' . $RootPath . '/PrintDeliveryReport.php?Updatedelivery_num='.$OrderNum.'" >' . _('打印') . '</a></div>';
                 echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . '退货单创建' .
 '" alt="" />' . ' ' . $Title . '
	</p>';


?>