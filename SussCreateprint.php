<?php
include('includes/session.inc');
$Title = '报价单签核';
include('includes/header.inc');
$OrderNum=$_GET['OrderNum'];
$msg = '报价单编号'.$OrderNum.'签核成功！';
				// echo $OrderNum;
                prnMsg($msg, success);
                 echo '<br /><div class="centre"><a href="' . $RootPath . '/QuoteApprove.php?New=Y">' . _('继续签核报价单') . '</a></div>';
                //  echo '<br /><div class="centre"><a href="' . $RootPath . '/PrintQuote.php?Updatedelivery_num='.$OrderNum.'" target="_blank"  >' . _('打印') . '</a></div>';
				  echo '<br /><div class="centre"><a href="' . $RootPath . '/printQutoeExcel.php?Updatedelivery_num='.$OrderNum.'" target="_blank"  >' . _('打印Excel') . '</a></div>';
                 echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . '报价单签核' .
 '" alt="" />' . ' ' . $Title . '
	</p>';


?>