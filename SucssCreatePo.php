<?php
include('includes/session.inc');
$Title = '采购单签核';
include('includes/header.inc');
$OrderNum=$_GET['OrderNum'];
$msg = '采购单编号'.$OrderNum.'签核成功！';
                prnMsg($msg, success);
                 echo '<br /><div class="centre"><a href="' . $RootPath . '/POApproved.php?New=Y">' . _('继续签核采购单') . '</a></div>';
                 // echo '<br /><div class="centre"><a href="' . $RootPath . '/PrintPoNuM.php?Updatedelivery_num='.$OrderNum.'" target="_blank">' . _('打印') . '</a></div>';
                 echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . '采购单签核' .
 '" alt="" />' . ' ' . $Title . '
	</p>';


?>