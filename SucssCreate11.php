<?php
include('includes/session.inc');
$Title = '工单料况修改';
include('includes/header.inc');
$OrderNum=$_GET['OrderNum'];
$msg = '工单料况修改'.$OrderNum.'成功！';
                prnMsg($msg, success);
                 echo '<br /><div class="centre"><a href="' . $RootPath . '/WIPModify.php?New=Y">' . _('工单料况修改') . '</a></div>';
                 echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . '工单料况修改' .
 '" alt="" />' . ' ' . $Title . '
	</p>';


?>