<?php
include('includes/session.inc');
$Title = '订单退货';
include('includes/header.inc');
$OrderNum=$_GET['OrderNum'];
$msg = '订单退货单号'.$OrderNum.'建立成功！';
                prnMsg($msg, success);
                 
                   echo '<br /><div class="centre"><a href="' . $RootPath . '/PrintTInstrumentDeliveryPDF.php?Updatedelivery_num='.$OrderNum.'" target="_blank"  >' . _('打印退货单') . '</a></div>';
				 

               echo '<br/><div class="centre"><a href="' . $RootPath . '/OrderReturn.php" >' . _('继续退货') . '</a></div>';
 


                 echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . '订单退货' .
 '" alt="" />' . ' ' . $Title . '</p>';
?>