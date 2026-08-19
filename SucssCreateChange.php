<?php
include('includes/session.inc');
$Title = '暂估单创建';
include('includes/header.inc');
$OrderNum=$_GET['OrderNum'];
$msg = '暂估单号'.$OrderNum.'建立成功！';
                prnMsg($msg, success);
                 echo '<br /><div class="centre"><a href="' . $RootPath . '/ProvisionalModify.php">' . _('继续创建暂估单') . '</a></div>';
                 echo '<br /><div class="centre"><a href="' . $RootPath . '/PrintProvisional.php?Updatedelivery_num='.$OrderNum.'" target="_blank">' . _('打印') . '</a></div>';
                 echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . '暂估单创建' .
 '" alt="" />' . ' ' . $Title . '
	</p>';


?>