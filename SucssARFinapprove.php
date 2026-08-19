<?php
include('includes/session.inc');
$Title = '订单财务签核';
include('includes/header.inc');
$OrderNum=$_GET['OrderNum'];
$msg = '订单编号'.$OrderNum.'财务签核成功！';
                prnMsg($msg, success);
                 echo '<br /><div class="centre"><a href="' . $RootPath . '/ARFinApprove.php?New=Y">' . _('继续订单财务签核') . '</a></div>';
                 echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . '订单财务签核处理' .
 '" alt="" />' . ' ' . $Title . '
	</p>';


?>