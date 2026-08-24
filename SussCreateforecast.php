<?php
include('includes/session.inc');
$Title = '预测计划创建';
include('includes/header.inc');
$OrderNum=$_GET['OrderNum'];
$msg = '预测计划编号'.$OrderNum.'建立成功！';
                prnMsg($msg, success);
                 echo '<br /><div class="centre"><a href="' . $RootPath . '/CreateForecast.php?New=Y">' . _('继续创建预测计划') . '</a></div>';
                 echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . '预测计划创建' .
 '" alt="" />' . ' ' . $Title . '
	</p>';


?>