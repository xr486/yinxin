<?php
include('includes/session.inc');
$Title = '订单出货';
include('includes/header.inc');
$OrderNum=$_GET['OrderNum'];
$msg = '订单出货单号'.$OrderNum.'建立成功！';
                prnMsg($msg, success);
                 
                   echo '<br /><div class="centre"><a href="' . $RootPath . '/PrintDeliveryPDF.php?Updatedelivery_num='.$OrderNum.'" target="_blank"  >' . _('打印出货单') . '</a></div>';
				// echo '<br /><div class="centre"><a href="' . $RootPath . '/printDeliveryExcel.php?Updatedelivery_num='.$OrderNum .'" target="_blank"  >' . _('打印出货单Excel') . '</a></div>';

               echo '<br/><div class="centre"><a href="' . $RootPath . '/SearchShipOrder.php" >' . _('继续出货') . '</a></div>';
 


                 echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . '订单出货' .
 '" alt="" />' . ' ' . $Title . '</p>';
?>