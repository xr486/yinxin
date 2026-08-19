<?php
include('includes/session.inc');
$Title = '请购单创建';
include('includes/header.inc');
$OrderNum=$_GET['OrderNum'];
$msg = '请购单编号'.$OrderNum.'建立成功！';
                prnMsg($msg, success);
                 echo '<br /><div class="centre"><a href="' . $RootPath . '/PR_Header.php?New=Y">' . _('继续创建请购单') . '</a></div>';
                 echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . '请购单创建' .
 '" alt="" />' . ' ' . $Title . '
	</p>';


?>