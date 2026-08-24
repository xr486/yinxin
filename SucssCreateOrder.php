<?php
include('includes/session.inc');
$Title = '订单签核';
include('includes/header.inc');
$OrderNum=$_GET['OrderNum'];
$msg = '订单编号'.$OrderNum.'签核成功！';
                prnMsg($msg, success);
                 echo '<br /><div class="centre"><a href="' . $RootPath . '/SearchSoForApprove.php?New=Y">' . _('继续签核订单') . '</a></div>';
                 echo '<br /><div class="centre"><a href="' . $RootPath . '/PrintQuote.php?Updatedelivery_num='.$_SESSION['OrderNum' . $identifier].'" target="_blank"  >' . _('打印') . '</a></div>';
                 echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . '订单创建' .
 '" alt="" />' . ' ' . $Title . '
	</p>';


?>