<?php
include('includes/session.inc');
$Title = '请购单转采购单';
$ViewTopic= '请购单转采购单';
$BookMark = '请购单转采购单';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

 
 if (isset($_GET['OrderNum'])) {
$_SESSION['OrderNum' . $identifier]=$_GET['OrderNum'];
 }
$OrderNum=$_GET['OrderNum'];

$msg = '采购单编号'.$OrderNum.'签核成功！';
                prnMsg($msg, success);
                 echo '<br /><div class="centre"><a href="' . $RootPath . '/POApproved.php">' . _('继续采购单签核') . '</a></div>';
                 echo '<br /><div class="centre"><a href="' . $RootPath . '/PrintPOQuote.php?Updatedelivery_num='.$_SESSION['OrderNum' . $identifier].'" target="_blank"  >' . _('打印') . '</a></div>';
                 
	 
 
  include('includes/footer.inc');
?>