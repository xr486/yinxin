<?php

$OrderNum=$_GET['OrderNum'];
$type=$_GET['type'];


if($type=='update'){
   include('includes/session.inc');
$Title = '报价单修改';
include('includes/header.inc');
   $msg = '报价单编号'.$OrderNum.'修改成功！';
                prnMsg($msg, success);
                 echo '<br /><div class="centre"><a href="' . $RootPath . '/QuoteUpdate.php?New=Y">' . _('继续修改报价单') . '</a></div>';
                 echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . '报价单修改' .
 '" alt="" />' . ' ' . $Title . '
	</p>';

} else if($type=='BOMRoutes'){
   
   include('includes/session.inc');
$Title = '产品工艺建立完成';
include('includes/header.inc');
   $msg = '产品工艺建立完成'.$OrderNum.'建立成功！';
    prnMsg($msg, success);
    echo '<br /><div class="centre"><a href="' . $RootPath . '/BOMRouteModify.php?New=Y">' . _('继续上传产品工艺') . '</a></div>';
                 echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . '产品工艺建立完成' .
 '" alt="" />' . ' ' . $Title . '
	</p>';
} else if($type=='PrCreate'){
   
   include('includes/session.inc');
$Title = '请购单建立';
include('includes/header.inc');
   $msg = '请购单编号'.$OrderNum.'建立成功！';
                prnMsg($msg, success);
                 echo '<br /><div class="centre"><a href="' . $RootPath . '/PrCreate.php?New=Y">' . _('继续建立请购单') . '</a></div>';
                 echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . '请购单' .
 '" alt="" />' . ' ' . $Title . '
	</p>';
} else if($type=='PrCreate'){
   
   include('includes/session.inc');
$Title = '请购单建立';
include('includes/header.inc');
   $msg = '请购单编号'.$OrderNum.'建立成功！';
                prnMsg($msg, success);
                 echo '<br /><div class="centre"><a href="' . $RootPath . '/PrCreate.php?New=Y">' . _('继续建立请购单') . '</a></div>';
                 echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . '请购单' .
 '" alt="" />' . ' ' . $Title . '
	</p>';
} else if($type=='OSPCreate'){
   
      include('includes/session.inc');
      $Title = '外协单建立';
      include('includes/header.inc');
         $msg = '外协单编号'.$OrderNum.'建立成功！';
          prnMsg($msg, success);
        echo '<br /><div class="centre"><a href="' . $RootPath . '/OSPCreate.php?New=Y">' . _('继续建立外协单') . '</a></div>';
        echo '<br /><div class="centre"><a href="' . $RootPath . '/PrintOSPPO.php?Updatedelivery_num='.$OrderNum .'" target="_blank"  >' . _('打印外协加工单') . '</a></div>';
		
		echo '<p class="page_title_text"> <img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . '外协单' .'" alt="" />' . ' ' . $Title . ' </p>';
}

else if($type=='POupload'){
   
   include('includes/session.inc');
$Title = '采购单建立';
include('includes/header.inc');
   $msg = '采购单编号'.$OrderNum.'建立成功！';
                prnMsg($msg, success);
                 echo '<br /><div class="centre"><a href="' . $RootPath . '/POupload.php?New=Y">' . _('继续上传采购单') . '</a></div>';
                 echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . '采购单' .
 '" alt="" />' . ' ' . $Title . '
	</p>';
}else if($type=='Prupload'){
   
   include('includes/session.inc');
$Title = '请购单建立';
include('includes/header.inc');
   $msg = '请购单编号'.$OrderNum.'建立成功！';
                prnMsg($msg, success);
                 echo '<br /><div class="centre"><a href="' . $RootPath . '/PrCreateUpload.php">' . _('继续上传请购单') . '</a></div>';
                 echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . '请购单' .
 '" alt="" />' . ' ' . $Title . '
	</p>';
} else if($type=='OutHouseBatch'){
   
   include('includes/session.inc');
$Title = '其他原因出库单建立';
include('includes/header.inc');
   $msg = '其他原因出库单编号'.$OrderNum.'建立成功！';
                prnMsg($msg, success);
                 echo '<br /><div class="centre"><a href="' . $RootPath . '/OutHouseBatch.php?New=Y">' . _('继续上传其他原因出库单') . '</a></div>';
                 echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . '其他原因出库单' .
 '" alt="" />' . ' ' . $Title . '
	</p>';
}  else if($type=='AddNewFGItem'){
    
   include('includes/session.inc');
$Title = '料号建立';
include('includes/header.inc');
   $msg = '料号'.$OrderNum.'建立成功！';
                prnMsg($msg, success);
                 echo '<br /><div class="centre"><a href="' . $RootPath . '/AddFGItemNo.php">' . _('继续新建料号') . '</a></div>';
				// echo '<br /><div class="centre"><a href="' . $RootPath . '/BOMSetup.php" target="_blank">' . _('为成品料号建立BOM') . '</a></div>';
				 
                 echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . '料号新建成功' .
 '" alt="" />' . ' ' . $Title . '
	</p>';
} else if($type=='SearchVendorReceipt'){
    
   include('includes/session.inc');
$Title = '供应商批量暂收';
include('includes/header.inc');
   $msg = '供应商批量暂收'.$OrderNum.'建立成功！';
                prnMsg($msg, success);
                 echo '<br /><div class="centre"><a href="' . $RootPath . '/SearchVendorReceipt.php">' . _('继续供应商批量暂收') . '</a></div>'; 
				 	 echo '<br /><div class="centre"><a href="' . $RootPath . '/printSearchPOReceipt.php?Updatedelivery_num='.$OrderNum .'" target="_blank"  >' . _('打印') . '</a></div>';
                 echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . '供应商批量暂收成功' .
 '" alt="" />' . ' ' . $Title . '
	</p>';
} else if($type=='AddItem'){
    
   include('includes/session.inc');
$Title = '料号建立';
include('includes/header.inc');
   $msg = '料号'.$OrderNum.'建立成功！';
                prnMsg($msg, success);
                 echo '<br /><div class="centre"><a href="' . $RootPath . '/QuoteToItem.php">' . _('继续报价需求转料号') . '</a></div>'; 
				 
                 echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . '料号新建成功' .
 '" alt="" />' . ' ' . $Title . '
	</p>';
} else if($type=='AddNewItem'){
    
   include('includes/session.inc');
$Title = '料号建立';
include('includes/header.inc');
   $msg = '料号'.$OrderNum.'建立成功！';
                prnMsg($msg, success);
                 echo '<br /><div class="centre"><a href="' . $RootPath . '/AddItemNo.php">' . _('继续新建料号') . '</a></div>';
				// echo '<br /><div class="centre"><a href="' . $RootPath . '/BOMSetup.php" target="_blank">' . _('为成品料号建立BOM') . '</a></div>';
				 
                 echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . '料号新建成功' .
 '" alt="" />' . ' ' . $Title . '
	</p>';
} else if($type=='CustomerItem'){
   
   include('includes/session.inc');
$Title = '客户料号关系维护';
include('includes/header.inc');
   $msg = '客户料号关系维护'.$OrderNum.'建立成功！';
                prnMsg($msg, success);
                 echo '<br /><div class="centre"><a href="' . $RootPath . '/CustomerItem.php">' . _('继续客户料号关系维护') . '</a></div>';
                 echo '<p class="page_title_text">
    <img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . '客户料号关系维护' .
 '" alt="" />' . ' ' . $Title . '
  </p>';
} else if($type=='VendorItem'){
   
   include('includes/session.inc');
$Title = '供应商料号关系维护';
include('includes/header.inc');
   $msg = '供应商料号关系维护'.$OrderNum.'建立成功！';
                prnMsg($msg, success);
                 echo '<br /><div class="centre"><a href="' . $RootPath . '/VendorItem.php">' . _('继续供应商料号关系维护') . '</a></div>';
                 echo '<p class="page_title_text">
    <img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . '供应商料号关系维护' .
 '" alt="" />' . ' ' . $Title . '
  </p>';
}else if($type=='do_purchase'){
   
   include('includes/session.inc');
$Title = '报价单采购协助';
include('includes/header.inc');
   $msg = '报价单编号'.$OrderNum.'协助成功！';
                prnMsg($msg, success);
                 echo '<br /><div class="centre"><a href="' . $RootPath . '/DOPurchaseQuote.php">' . _('继续报价单采购协助') . '</a></div>';
                 echo '<p class="page_title_text">
    <img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . '报价单采购协助' .
 '" alt="" />' . ' ' . $Title . '
  </p>';
} else if($type=='sendSigned'){
   
  include('includes/session.inc');
  $Title = '报价单送签';
  include('includes/header.inc');
   $msg = '报价单编号'.$OrderNum.'送签成功！';
                prnMsg($msg, success);
                 echo '<br /><div class="centre"><a href="' . $RootPath . '/SendSigned.php">' . _('继续报价单送签') . '</a></div>';
                 echo '<p class="page_title_text">
    <img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . '报价单送签' .
 '" alt="" />' . ' ' . $Title . '
  </p>';
} else if($type=='QuoteToSo'){
   
  include('includes/session.inc');
  $Title = '报价单转销售订单';
  include('includes/header.inc');
   $msg = '报价单编号'.$OrderNum.'转订单成功！';
                prnMsg($msg, success);
                 echo '<br /><div class="centre"><a href="' . $RootPath . '/QuoteToSo.php">' . _('继续报价转销售订单') . '</a></div>';
                 echo '<p class="page_title_text">
    <img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . '报价单转销售订单' .
 '" alt="" />' . ' ' . $Title . '
  </p>';
} else if($type=='AddNewOrder'){
   
  include('includes/session.inc');
  $Title = '销售订单建立';
  include('includes/header.inc');
   $msg = '销售订单编号'.$OrderNum.'创建成功！';
                prnMsg($msg, success);
                 echo '<br /><div class="centre"><a href="' . $RootPath . '/AddNewOrder.php">' . _('继续销售订单建立') . '</a></div>';
                 echo '<p class="page_title_text">
    <img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . '销售订单建立' .
 '" alt="" />' . ' ' . $Title . '
  </p>';
}  else if($type=='updateSo'){
   
  include('includes/session.inc');
  $Title = '销售订单修改';
  include('includes/header.inc');
   $msg = '销售订单编号'.$OrderNum.'修改成功！';
                prnMsg($msg, success);
                 echo '<br /><div class="centre"><a href="' . $RootPath . '/SoUpdate.php">' . _('继续销售订单修改') . '</a></div>';
                 echo '<p class="page_title_text">
    <img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . '销售订单修改' .
 '" alt="" />' . ' ' . $Title . '
  </p>';
}  else if($type=='signSo'){
   
  include('includes/session.inc');
  $Title = '销售订单签核';
  include('includes/header.inc');
   $msg = '销售订单编号'.$OrderNum.'签核成功';
                prnMsg($msg, success);
                 echo '<br /><div class="centre"><a href="' . $RootPath . '/SearchSoForApprove.php">' . _('继续销售订单签核') . '</a></div>';
                 echo '<p class="page_title_text">
    <img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . '销售订单签核' .
 '" alt="" />' . ' ' . $Title . '
  </p>';
} else if($type=='ShipOrder'){
   
  include('includes/session.inc');
  $Title = '出货单建立';
  include('includes/header.inc');
   $msg = '出货单编号'.$OrderNum.'建立成功';
                prnMsg($msg, success);
                 echo '<br /><div class="centre"><a href="' . $RootPath . '/SearchShipOrder.php">' . _('继续创建出货单') . '</a></div>';
                 echo '<p class="page_title_text">
    <img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . '继续创建出货单' .
 '" alt="" />' . ' ' . $Title . '
  </p>';
} else if($type=='InHouse'){
   
  include('includes/session.inc');
  $Title = '其它原因入库完成';
  include('includes/header.inc');
   $msg = '其它原因入库单'.$OrderNum.'成功';
                prnMsg($msg, success);
                 echo '<br /><div class="centre"><a href="' . $RootPath . '/InHouse.php">' . _('继续其它原因入库') . '</a></div>';
                 echo '<a href="' . $RootPath . '/PrintInHouse.php?OrderNum='.$OrderNum.'" target="_blank"  >打印</a>';
                 echo '<p class="page_title_text">
    <img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . '继续其它原因入库' .
 '" alt="" />' . ' ' . $Title . '
  </p>';
}  else if($type=='InHouseBatch'){
   
  include('includes/session.inc');
  $Title = '其它原因整批入库完成';
  include('includes/header.inc');
   $msg = '其它原因整批入库单'.$OrderNum.'成功';
                prnMsg($msg, success);
                 echo '<br /><div class="centre"><a href="' . $RootPath . '/InHouseBatch.php">' . _('继续其它原因整批入库') . '</a></div>';
                 echo '<p class="page_title_text">
    <img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . '继续其它原因整批入库' .
 '" alt="" />' . ' ' . $Title . '
  </p>';
} else if($type=='OrderIssue'){
   
  include('includes/session.inc');
  $Title = '出货单扣账';
  include('includes/header.inc');
   $msg = '出货单编号'.$OrderNum.'扣账成功';
                prnMsg($msg, success);
                 echo '<br /><div class="centre"><a href="' . $RootPath . '/ShipOrderIssue.php">' . _('继续出货单扣账') . '</a></div>';
                 echo '<p class="page_title_text">
    <img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . '继续出货单扣账' .
 '" alt="" />' . ' ' . $Title . '
  </p>';
} else if($type=='OrderReturn'){
   
  include('includes/session.inc');
  $Title = '出货单拉回';
  include('includes/header.inc');
   $msg = '出货单编号'.$OrderNum.'拉回成功';
                prnMsg($msg, success);
                 echo '<br /><div class="centre"><a href="' . $RootPath . '/ShipOrderReturn.php">' . _('继续出货单拉回') . '</a></div>';
                 echo '<p class="page_title_text">
    <img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . '继续出货单拉回' .
 '" alt="" />' . ' ' . $Title . '
  </p>';
} 
else if($type=='duizhang'){
   
  include('includes/session.inc');
  $Title = '出货单对账';
  include('includes/header.inc');
   $msg = '出货单'.$OrderNum.'对账成功';
                prnMsg($msg, success);
                 echo '<br /><div class="centre"><a href="' . $RootPath . '/CheckSODelivery.php">' . _('继续出货单对账') . '</a></div>';
                 echo '<p class="page_title_text">
    <img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . '继续出货单对账' .
 '" alt="" />' . ' ' . $Title . '
  </p>';
}

else if($type=='BOMCopy'){
   
  include('includes/session.inc');
  $Title = 'BOM复制完成';
  include('includes/header.inc');
   $msg = '新BOM'.$OrderNum.'复制完成';
                prnMsg($msg, success);
				echo '<br /><a href="' . $RootPath . '/BOMModify4.php?New=Yes&UpdateBOMItem=' . $OrderNum . '"  target="_blank" >修改BOM子料</a>';
                 echo '<br /><br /><div class="centre"><a href="' . $RootPath . '/BOMCopy.php">' . _('继续复制BOM') . '</a></div>';

                 echo '<p class="page_title_text">
    <img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . '继续复制BOM' .
 '" alt="" />' . ' ' . $Title . '
  </p>';
}

else  if($type=='duizhang2'){
   
  include('includes/session.inc');
  $Title = '出货单对账取消';
  include('includes/header.inc');
   $msg = '出货单'.$OrderNum.'对账取消成功';
                prnMsg($msg, success);
                 echo '<br /><div class="centre"><a href="' . $RootPath . '/CheckSODeliveryUpdate.php">' . _('继续出货单对账取消') . '</a></div>';
                 echo '<p class="page_title_text">
    <img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . '继续出货单对账取消' .
 '" alt="" />' . ' ' . $Title . '
  </p>';
}else if($type=='ARInvoice') {
	include('includes/session.inc');
$Title = '客户发票创建';
include('includes/header.inc');
	$msg = '发票单号'.$OrderNum.'建立成功！';
                prnMsg($msg, success);
                 echo '<br /><div class="centre"><a href="' . $RootPath . '/ARInvoice.php?New=Y">' . _('继续创建客户发票') . '</a></div>';
                 echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . '客户发票创建' .
 '" alt="" />' . ' ' . $Title . '
	</p>';
}else if($type=='ARInvoice1') {
	include('includes/session.inc');
$Title = '客户发票创建';
include('includes/header.inc');
	$msg = '发票单号'.$OrderNum.'建立成功！';
                prnMsg($msg, success);
                 echo '<br /><div class="centre"><a href="' . $RootPath . '/ARInvoice1.php?New=Y">' . _('继续创建客户发票') . '</a></div>';
                 echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . '客户发票创建' .
 '" alt="" />' . ' ' . $Title . '
	</p>';
}else if($type=='APPayment'){
   include('includes/session.inc');
$Title = '供应商付款';
include('includes/header.inc');
   $msg = '供应商付款录入'.$OrderNum.'！';
                prnMsg($msg, success);
                 echo '<br /><div class="centre"><a href="' . $RootPath . '/APPayment.php?New=Y">' . _('继续供应商付款录入') . '</a></div>';
                 echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . '供应商付款录入完成' .
 '" alt="" />' . ' ' . $Title . '
	</p>';

}  else if($type=='APInvoice'){
   include('includes/session.inc');
$Title = '费用发票录入';
include('includes/header.inc');
   $msg = '商费用发票录入'.$OrderNum.'！';
                prnMsg($msg, success);
                 echo '<br /><div class="centre"><a href="' . $RootPath . '/APInvoice.php?New=Y">' . _('继续费用发票录入') . '</a></div>';
                 echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . '费用发票录入完成' .
 '" alt="" />' . ' ' . $Title . '
	</p>';

} else if($type=='APInvoiceBack'){
   include('includes/session.inc');
$Title = '红字发票录入';
include('includes/header.inc');
   $msg = '红字发票录入'.$OrderNum.'！';
                prnMsg($msg, success);
                 echo '<br /><div class="centre"><a href="' . $RootPath . '/APInvoiceBack.php?New=Y">' . _('继续红字发票录入') . '</a></div>';
                 echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . '红字发票录入完成' .
 '" alt="" />' . ' ' . $Title . '
	</p>';

}
else if($type=='APInvoice1'){
   include('includes/session.inc');
$Title = '供应商发票录入';
include('includes/header.inc');
   $msg = '供应商发票录入'.$OrderNum.'！';
                prnMsg($msg, success);
                 echo '<br /><div class="centre"><a href="' . $RootPath . '/APInvoice1.php?New=Y">' . _('继续供应商发票录入') . '</a></div>';
                 echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . '供应商发票录入完成' .
 '" alt="" />' . ' ' . $Title . '
	</p>';

} 
else if($type=='WIPMaterialReturn'){
   include('includes/session.inc');
$Title = '生产退料';
include('includes/header.inc');
   $msg = '生产退料'.$OrderNum.'！';
                prnMsg($msg, success);
                 echo '<br /><div class="centre"><a href="' . $RootPath . '/WIPMaterialReturn.php?New=Y">' . _('继续生产退料') . '</a></div>';
                 echo '<br /><div class="centre"><a href="' . $RootPath . '/PrintWIPReturn.php?Updatedelivery_num='.$OrderNum .'" target="_blank"  >' . _('打印退料单') . '</a></div>';

                 echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . '生产退料完成' .
 '" alt="" />' . ' ' . $Title . '
	</p>';

} 
else if($type=='WIPMaterialBatch'){
   include('includes/session.inc');
$Title = '生产领料';
include('includes/header.inc');
   $msg = '生产领料'.$OrderNum.'！';
                prnMsg($msg, success);
                 echo '<br /><div class="centre"><a href="' . $RootPath . '/WIPMaterialBatch.php?New=Y">' . _('继续生产领料') . '</a></div>';
                 echo '<br /><div class="centre"><a href="' . $RootPath . '/PrintWIPIssue.php?Updatedelivery_num='.$OrderNum .'" target="_blank"  >' . _('打印领料单') . '</a></div>';
                 echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . '生产领料完成' .
 '" alt="" />' . ' ' . $Title . '
	</p>';

}else if($type=='OSPMaterialBatch'){
   include('includes/session.inc');
$Title = '生产领料';
include('includes/header.inc');
   $msg = '生产领料'.$OrderNum.'！';
                prnMsg($msg, success);
                 echo '<br /><div class="centre"><a href="' . $RootPath . '/OSPMaterialBatch.php?New=Y">' . _('继续外协领料') . '</a></div>';
                 echo '<br /><div class="centre"><a href="' . $RootPath . '/PrintOSPIssue.php?Updatedelivery_num='.$OrderNum .'" target="_blank"  >' . _('打印领料单') . '</a></div>';
                 echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . '生产领料完成' .
 '" alt="" />' . ' ' . $Title . '
	</p>';

} else if($type=='OspOrderCreate'){
   include('includes/session.inc');
$Title = '外协请购建立完成';
include('includes/header.inc');
   $msg = '外协请购编号'.$OrderNum.'！';
                prnMsg($msg, success);
                 echo '<br /><div class="centre"><a href="' . $RootPath . '/WIPOSPApply.php">' . _('继续建立外协请购单') . '</a></div>';
                 echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . '外协请购建立完成' .
 '" alt="" />' . ' ' . $Title . '
	</p>';

} else if($type=='OSPVendorReceipt'){
   include('includes/session.inc');
$Title = '外协采购单批量暂收';
include('includes/header.inc');
   $msg = '外协采购单批量暂收单'.$OrderNum.'！';
                prnMsg($msg, success);
                 echo '<br /><div class="centre"><a href="' . $RootPath . '/OSPVendorReceipt.php?New=Y">' . _('继续外协采购单批量暂收') . '</a></div>';
                 echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . '外协采购单批量暂收完成' .
 '" alt="" />' . ' ' . $Title . '
	</p>';

}else if($type=='CheckOSPPODelivery'){
   
   include('includes/session.inc');
   $Title = '外协采购对账完成';
   include('includes/header.inc');
    $msg = '外协采购对账完成';
                 prnMsg($msg, success);
              
                  echo '<br /><br /><div class="centre"><a href="' . $RootPath . '/CheckOSPPODelivery.php">' . _('继续外协采购对账') . '</a></div>';
 
                  echo '<p class="page_title_text">
     <img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . '继续外协采购对账' .
  '" alt="" />' . ' ' . $Title . '
   </p>';
 } else if($type=='CheckOSPPODeliveryUpdate'){
   
   include('includes/session.inc');
   $Title = '外协采购对账取消完成';
   include('includes/header.inc');
    $msg = '外协采购对账取消完成';
                 prnMsg($msg, success);
              
                  echo '<br /><br /><div class="centre"><a href="' . $RootPath . '/CheckOSPPODeliveryUpdate.php">' . _('继续外协采购对账取消') . '</a></div>';
 
                  echo '<p class="page_title_text">
     <img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . '继续外协采购对账取消' .
  '" alt="" />' . ' ' . $Title . '
   </p>';
 } 
 else {
	include('includes/session.inc');
$Title = '报价单创建';
include('includes/header.inc');
	$msg = '报价单编号'.$OrderNum.'建立成功！';
                prnMsg($msg, success);
                 echo '<br /><div class="centre"><a href="' . $RootPath . '/CreateQuote.php?New=Y">' . _('继续创建报价单') . '</a></div>';
                 echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . '报价单创建' .
 '" alt="" />' . ' ' . $Title . '
	</p>';
}


?>