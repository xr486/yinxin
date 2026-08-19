<?php 
	include('includes/session.inc');
	$Title = _('报价申请签核');
	$ViewTopic= '报价申请签核';
	$BookMark = '报价申请签核';
	include('includes/header.inc');
	include('includes/SQL_CommonFunctions.inc');
 require_once 'upload.class.php';
 if (isset($_GET['OrderNum'])) {
$_SESSION['OrderNum' . $identifier]=$_GET['OrderNum'];
 }
$msg = '报价申请编号'.$_SESSION['OrderNum' . $identifier].'签核成功！';
                prnMsg($msg, success);
                 echo '<br /><div class="centre"><a href="' . $RootPath . '/QuoteToCustomerapproved.php?New=Y">' . _('继续报价签核') . '</a></div>';
                    echo '<br /><div class="centre"><a href="' . $RootPath . '/PrintQuote.php?Updatedelivery_num='.$_SESSION['OrderNum' . $identifier].'" target="_blank"  >' . _('打印外部报价单') . '</a></div>
					<div class="centre"><a href="' . $RootPath . '/PrintQuote2.php?Updatedelivery_num='.$_SESSION['OrderNum' . $identifier].'" target="_blank"  >' . _('打印内部报价单') . '</a></div>';
				 	

?>


<?php
  include('includes/footer.inc');
?>