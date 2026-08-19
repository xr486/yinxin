<?php
include('includes/session.inc');
$Title = '采购来料报检单创建';
include('includes/header.inc');
$OrderNum=$_GET['OrderNum'];
$msg = '采购来料报检单'.$OrderNum.'建立成功！';
                prnMsg($msg, success);
                 echo '<br /><div class="centre"><a href="' . $RootPath . '/SearchPOReceipt.php">' . _('继续创建采购来料报检单') . '</a></div>';
                 	 echo '<br /><div class="centre"><a href="' . $RootPath . '/printSearchPOReceipt.php?Updatedelivery_num='.$OrderNum .'" target="_blank"  >' . _('打印') . '</a></div>';
                 echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . '采购单来料报检库' .
 '" alt="" />' . ' ' . $Title . '
	</p>';


?>