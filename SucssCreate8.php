<?php
include('includes/session.inc');
$Title = '采购单创建';
include('includes/header.inc');
$OrderNum=$_GET['OrderNum'];
$msg = '采购单编号'.$OrderNum.'建立成功！';
                prnMsg($msg, success);
                 echo '<br /><div class="centre"><a href="' . $RootPath . '/MRPCreatePo.php">' . _('继续创建采购单') . '</a></div>';
                 echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . '采购单创建' .
 '" alt="" />' . ' ' . $Title . '
	</p>';


?>