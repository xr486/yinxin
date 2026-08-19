<?php
include('includes/session.inc');
$Title = '采购入库&退库对账修改成功';
include('includes/header.inc');
//$OrderNum=$_GET['OrderNum'];采购
$msg = '采购入库&退库对账修改成功';
                prnMsg($msg, success);
                 echo '<br /><div class="centre"><a href="' . $RootPath . '/CheckPODeliveryUpdate.php">' . _('继续修改采购对账单') . '</a></div>';
				 echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . '采购入库&退库对账修改' .
 '" alt="" />' . ' ' . $Title . '
	</p>';


?>