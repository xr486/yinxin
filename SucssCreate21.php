<?php
include('includes/session.inc');
$Title = '采购退货单创建';
include('includes/header.inc');
$OrderNum=$_GET['OrderNum'];
$msg = '采购退货单'.$OrderNum.'建立成功！';
                prnMsg($msg, success);
                echo '<br /><div class="centre"><a href="' . $RootPath . '/InPOReturn.php">' . _('继续创建采购退货单') . '</a></div>';
                echo '<br /><div class="centre"><a href="' . $RootPath . '/PrintPOReturn.php?OrderNum=' .$OrderNum . '"  target="_blank" >打印采购退料单</a></div>';

                 echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . '采购单退货单' .
 '" alt="" />' . ' ' . $Title . '
	</p>';


?>