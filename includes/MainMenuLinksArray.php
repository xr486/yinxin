<?php

/* $Id: MainMenuLinksArray.php 6190 2013-08-12 02:12:02Z rchacon $ */

/* webERP menus with Captions and URLs. */
$ModuleLink = array(
// 'orders',
// 'Purchase',  
// 'INV',
'BOM',
'Doc',
// 'WIP',
// 'OSP',
// 'QM',
// 'AP', 
//  'AR', 
//  'SH', 
// 'FIN', 
//'MRP', 
'system',
'project_management');
$ReportList = array(
// 'orders' => 'ord', 
// 'Purchase' => 'PO',                      
// 'INV' => 'INV',
'BOM' => 'BOM',
'Doc' => 'DOC',
'project_management' => 'project_management',
// 'WIP' => 'WIP', 
// 'OSP' => 'OSP',
// 'QM' => 'QM',
// 'AP' => 'AP', 
// 'AR' => 'AR', 
// 'SH' => 'SH', 

// 'FIN' => 'FIN', 
//'MRP' => 'MRP', 
'system' => 'sys'  );

/* The headings showing on the tabs accross the main index used also in WWW_Users for defining what should be visible to the user */
$ModuleList = array(  
// _('销售'), 
// _('采购'), 

// _('仓库'),
_('BOM'),
_('图文档'),
// _('生产'),
// _('外协'),
// _('品质'), 
// _('应付'),
// _('应收'),
// _('技服'),
// _('月结'),
// _('MRP'), 
_('系统设置'),
_('项目管理'));

/*
 * ModulesEnabled is stored in the login session. Older sessions do not have
 * an entry for this newly added module, so enable its slot when the menu is
 * loaded. The child link remains protected by the existing user_power check.
 */
$projectManagementIndex = array_search('project_management', $ModuleLink, true);
if ($projectManagementIndex !== false) {
    if (!isset($_SESSION['ModulesEnabled']) || !is_array($_SESSION['ModulesEnabled'])) {
        $_SESSION['ModulesEnabled'] = array();
    }
    $_SESSION['ModulesEnabled'][$projectManagementIndex] = 1;
}

$MenuItems['orders']['Transactions']['Caption'] = array(  
_('客户维护'),
_('客户审核'),
_('客户资料修改'),
_('客户料号关系维护'),
/*_('报价单建立'),

_('报价申请'),
_('报价单修改'),
_('报价客户回复'),
_('报价单转销售订单'), */
_('订单建立'),
_('订单修改'),
_('订单签核'),
// _('订单高阶签核'),
_('出货单建立'),
_('出货单签核'),
_('销售退回'),
// _('销售退回审核'),
 _('销售退货部门审核'),
 _('销售退货收货人审核'),

_('出货对账'),
_('出货对账取消'),

);

$MenuItems['orders']['Transactions']['URL'] = array(
'/SearchCustomer.php',
'/CustomerApprove.php',
'/CustomerForApprove.php',
'/SearchCustomerItem.php',
/* '/QuoteRequest.php',
'/QuoteRequestModify.php',
'/QuoteCustomerReply.php',
'/QuoteToSo.php', */
'/AddNewOrder.php?New=Y',
'/UpdateSoForApprove.php',
'/SearchSoForApprove.php',
//  '/ARFinApprove.php',
'/SearchShipOrder.php',
'/ShipOrderApprove.php',
'/OrderReturn.php' ,
// '/OrderReturnApproved.php' ,
 '/OrderReturnDepartApproved.php' ,
 '/OrderReturnConsigneeApproved.php' ,
'/CheckSODelivery.php' ,
'/CheckSODeliveryUpdate.php' ,

);

$MenuItems['orders']['Reports']['Caption'] = array( 
_('客户资料查询'),
// _('报价单查询'),
/*_('报价申请明细报表'),
_('报价申请处理明细报表'),
_('报价单待签核报表'), */
_('业务订单查询'),
_('业务订单明细查询'),
_('业务订单统计报表'),
_('待出货明细报表'), 
_('逾期未出货报表'), 
_('出货单查询'),
_('出货明细查询'),
_('出货明细毛利报表'),
_('出货汇总查询'),
_('出货对账明细报表'),
_('已出货未对账明细报表'),

_('销售看板')
);

$MenuItems['orders']['Reports']['URL'] = array(
'/SearchCustomerInfo.php',
/*'/SearchQuote.php',
'/DOPODetailReport.php' ,
'/QuoteWaitReport.php',
'/PreSignReport.php',*/
'/SearchSO.php',
'/SearchSODetails.php',
'/SOCountReport.php',
'/SoWaitshipReport.php',
'/SoOvershipReport.php', 
'/SearchShipInfo.php', 
'/SOShipDetailReport.php', 
'/SOShipDetailCostReport.php', 
'/SoshipsumReport.php',
'/SodeliverycheckReport.php',
'/SodeliveryNocheckReport.php',

'/sale_ekanban.php'
);
$MenuItems['orders']['Maintenance']['Caption'] = array();
$MenuItems['orders']['Maintenance']['URL'] = array();

 $MenuItems['PMC']['Transactions']['Caption'] = array(	 
_('请购单申请'),
_('请购单审核'),
_('业务订单对应采购单关系删除'));

$MenuItems['PMC']['Transactions']['URL'] = array(	 
'/PrCreate.php',
'/PRApproved.php', 
'/SoMappingPoDelete.php'  );

$MenuItems['PMC']['Reports']['Caption'] = array(	
_('请购单报表'),
_('请购单明细'),
_('待转请购单明细查询'),
_('待审核请购单明细查询'),
_('业务订单对应采购单明细查询'),
_('虚拟仓采购单明细查询')  );

$MenuItems['PMC']['Reports']['URL'] = array(
'/PrReport.php',
'/PrDetailReport.php',
'/PrWaitToPoDetailReport.php',
'/PrWaitApprovedReport.php' ,
'/SoMappingPoReport.php',
'/PoNonMappingReport.php'  );

$MenuItems['PMC']['Maintenance']['Caption'] = array( );

$MenuItems['PMC']['Maintenance']['URL'] = array(  );

$MenuItems['Purchase']['Transactions']['Caption'] =  array(
_('供应商维护'),
_('供应商料号关系维护'),
_('供应商审核'),
_('采购申请单建立'),
_('安全库存请购'),
_('采购申请单修改'),
// _('采购申请单审核'),
_('采购申请单转采购单处理'),
_('销售单转采购单处理'),
_('采购单建立'), 
_('采购单修改'),
_('采购单签核'),
_('采购单结案'),
_('采购单上传'),
_('采购单审核撤销'),
_('采购来料报检单'),
_('供应商批量来料报检'),
_('来料报检单删除'),
_('采购单入库'),
_('采购单入库审核'),
_('采购检验不良退货'),

_('采购单退货') ,
_('采购单退货审核') ,

_('材料价格申请'),	
_('材料价格审核'),
_('采购对账'),	
_('采购对账取消'),	
_('暂估调整'),	
_('暂估调整审核'),	
);

$MenuItems['Purchase']['Transactions']['URL'] = array( 
'/SearchSupplier.php',
'/SearchVendorItem.php',
'/SupplierApprove.php',
'/PrCreate.php',
'/PRSafe.php',
'/PRUpdate.php',
// '/PRApproved.php',
// '/MrpAddPO.php',
//'/SafeAddPO.php', 
'/PRToPO.php',
'/SOToPO.php',
'/AddPurchaseOrder.php',
'/POUpdate.php',
'/POApproved.php',
'/POClose.php',
'/POupload.php',
'/POReverseApprove.php',
// '/POFinApproved.php',
'/SearchPOReceipt.php',	
'/SearchVendorReceipt.php',
'/POReceiptDelete.php',							
'/InPODelivery.php',
'/InPODeliveryApproved.php',
'/InPOCheckNo.php', 	
'/InPOReturn.php', 
'/InPOReturnDepartApproved.php', 
 
//'/InDeliveryNo.php',
'/Priceshengqing.php', 
'/PriceApprove.php',
'/CheckPODelivery.php', 
'/CheckPODeliveryUpdate.php', 
'/ProvisionalModify.php', 
'/ProvisionalApproved.php', 

);

$MenuItems['Purchase']['Reports']['Caption'] = array(   
_('供应商查询'),
_('采购申请单报表'),
_('采购申请单明细'),
_('待转采购申请单明细查询'),
_('待审核采购申请单明细查询'),
_('采购单查询'),
_('采购单价查询'),
_('采购单明细查询'),
_('采购单未收货明细查询'),
_('采购单请检明细查询'),
_('采购进料记录查询'),
_('采购进料允收未入库报表'),
_('采购进料拒收未退报表'),
_('采购入库明细报表'),
_('采购入库汇总报表') ,
_('采购价格明细表') ,
_('采购看板'),
_('暂估调整报表')
);

$MenuItems['Purchase']['Reports']['URL'] = array(
'/SearchVendor.php',
'/PrReport.php',
'/PrDetailReport.php',
'/PrWaitToPoDetailReport.php',
'/PrWaitApprovedReport.php' ,
'/SearchPO.php', 
'/POPriceReport.php', 
'/PODetail.php',
'/POWaitdeliveryDetail.php',
'/POdeliveryDetail.php',
'/InPORecord.php',
'/InPOWaitDelivery.php',
'/InPOWaitReturnVendor.php',
'/InPODeliveryReport.php',
'/InPODeliverySumReport.php' ,
'/PriceReport.php' ,
'/purchase_ekanban.php',
'/ProvisionalReport.php'
);

$MenuItems['Purchase']['Maintenance']['Caption'] = array(   );

$MenuItems['Purchase']['Maintenance']['URL'] = array( );

$MenuItems['SH']['Transactions']['Caption'] = array(	 
_('售后服务单填写'),
_('售后服务单结案') );

$MenuItems['SH']['Transactions']['URL'] = array(
'/ShOrdercreate.php',
'/ShOrderClosed.php' );

$MenuItems['SH']['Reports']['Caption'] = array(	
_('售后服务单查询报表'),
_('未结案服务单报表'));

$MenuItems['SH']['Reports']['URL'] = array(	
'/ShOrderReport.php',
'/ShOrderWaitClosedReport.php' );

$MenuItems['SH']['Maintenance']['Caption'] = array();

$MenuItems['SH']['Maintenance']['URL'] = array();

$MenuItems['INV']['Transactions']['Caption']=array(	  
_('其他原因入库'),
_('其他原因入库审核'),
_('其他原因出库'),
_('其他原因出库部门审核'),
_('其他原因出库仓库审核'),
_('其他原因出库红冲'),
_('仓库调拨') ,
_('仓库调拨审核') ,
_('出货单仓库确认'),
_('销售退货入库审核'),
_('采购单退货仓库审核') ,
_('仓库盘点单建立') ,
_('仓库盘点差异输入') ,
_('仓库盘点差异审核') ,
);

$MenuItems['INV']['Transactions']['URL'] = array(       
'/InHouse.php',
'/InHouseApproved.php',
'/OutHouse.php',	
'/OutHouseDepartApproved.php',	
'/OutHouseApproved.php',	
'/OutHouseHongChong.php',	
'/InchangeHouse.php' ,
'/InchangeHouseApproved.php' ,
'/ShipOrderConfirm.php',
'/OrderReturnSubApproved.php' ,
'/InPOReturnApproved.php',
'/InvCheckCreate.php' ,
'/InvCheckResult.php' ,
'/InvCheckResultApprove.php' ,
);
$MenuItems['INV']['Reports']['Caption'] = array(	    
_('料号查询') ,
_('库存明细报表'),
_('库存汇总报表'),
_('库存金额报表'),
_('历史库存金额报表'),
_('低于安全库存报表'),       
_('杂项出入库明细报表'),
_('杂项出入库汇总报表'),
_('其他原因出入库状态/打印报表'),
_('采购不合格退货报表'),
_('库存交易明细报表'),
_('库存交易汇总报表'),
_('库存呆滞料明细报表'),
//	_('仓库盘点报表')
);
$MenuItems['INV']['Reports']['URL'] = array(          
'/Item_No.php' , 
'/InWHItemReport.php',
'/InWHItem.php',
'/InWHItemCost.php',
'/HisInWHItemCost.php',
'/InvLowsafestock.php',  
'/InHouseReport.php',
'/InHouseSumReport.php',
'/OutHouseStatusPrintReport.php',
'/POBadReturnReport.php',
'/InvTxnReport.php',
'/InvTxnSumReport.php',
'/InvDaizhiReport.php',
//	'/StockChecks.php'
);
$MenuItems['INV']['Maintenance']['Caption'] = array( _('交易类型设置') );

$MenuItems['INV']['Maintenance']['URL'] = array( '/invclassset.php'	);

$MenuItems['BOM']['Transactions']['Caption'] = array(	  
_('料号维护'),
// _('料号审核'),
_('料号修改'),
_('料号整批上传'),
_('BOM建立'),  
_('BOM修改'),
_('BOM审核'),
// _('BOM复制'),
_('BOM上传'),
_('ECN单据建立'),
_('ECN内容建立'),
_('ECN审核'),
_('SMT料站表维护'),
_('产品工艺设置') ,
_('产品工艺修改') ,
_('产品工艺审核') ,
_('工艺设置') 
);

$MenuItems['BOM']['Transactions']['URL'] = array(	 
'/segment1set.php',
// '/segment1Approve.php',
'/segment1Up.php',
'/SegmentUpload.php',
'/BOMSetup.php',  
'/BOMModify.php',
'/BOMApprove.php',

'/BOMUpload.php',
'/ECNSetup.php',
'/ECNContentSetup.php',
'/ECNApprove.php',
'/BOMSMTZhan.php',
'/BOMRouteUpdate.php',
'/BOMRouteModify.php',
'/BOMRouteApprove.php',
'/BomOperation.php' 
);

$MenuItems['BOM']['Reports']['Caption'] = array(	 
_('料号查询') ,
_('当前BOM查询'),
_('历史BOM查询'),
_('BOM及替代料查询'),
// _('BOM及替代料零件位置查询'),
_('BOM多阶查询'),
 _('BOM差异比较查询'),
_('料号用途查询'),
_('BOM成本查询'),
// _('工单用量与BOM比对报表'),
_('产品工艺查询'),
);

$MenuItems['BOM']['Reports']['URL'] = array(	 
'/Item_No.php',
'/BOMQueryReport.php',
'/BOMQueryAllReport.php',
'/BOMSubQueryReport.php',
//  '/BOMSubQueryWeiReport.php',
'/BOMAllQueryReport.php',
'/BOMChayi.php',
'/BOMWhereUsed.php',
'/BOMCostReport.php',
//'/WipUsedCompBOM.php',
'/BOMRoutesReport.php'
);

$MenuItems['BOM']['Maintenance']['Caption'] = array( //_('工艺参数维护')
);

$MenuItems['BOM']['Maintenance']['URL'] = array( //'/BOMRouteParamenter.php'
);

$MenuItems['Doc']['Transactions']['Caption'] = array(
_('文档工作区'),
_('文档模板'),
_('文件废止区'),
_('文件回收站')
);

$MenuItems['Doc']['Transactions']['URL'] = array(
'/DocPLM.php',
'/DocPLM.php?mode=template',
'/DocPLM.php?mode=abolition',
'/DocPLM.php?mode=recycle'
);

$MenuItems['Doc']['Reports']['Caption'] = array(
);

$MenuItems['Doc']['Reports']['URL'] = array(
);

$MenuItems['Doc']['Maintenance']['Caption'] = array(
);

$MenuItems['Doc']['Maintenance']['URL'] = array(
);

$MenuItems['project_management']['Transactions']['Caption'] = array(
_('项目总览'),
_('项目模板'),
_('任务模板'),
_('项目实例'),
_('待启动项目'),
_('运行中项目'),
_('已暂停项目'),
_('已完成项目'),
_('项目角色'),
_('项目流程'),
_('设置'),
);

$MenuItems['project_management']['Transactions']['URL'] = array(
'/ProjectManagement.php?view=dashboard',
'/ProjectManagement.php?view=project_templates',
'/ProjectManagement.php?view=task_templates',
'/ProjectManagement.php?view=instances',
'/ProjectManagement.php?view=track&status=TO_BE_START',
'/ProjectManagement.php?view=track&status=STARTING',
'/ProjectManagement.php?view=track&status=PAUSED&scope=owner',
'/ProjectManagement.php?view=track&status=FINISHED',
'/ProjectManagement.php?view=roles',
'/ProjectManagement.php?view=workflow',
'/ProjectManagement.php?view=settings',
);

$MenuItems['project_management']['Reports']['Caption'] = array();
$MenuItems['project_management']['Reports']['URL'] = array();
$MenuItems['project_management']['Maintenance']['Caption'] = array();
$MenuItems['project_management']['Maintenance']['URL'] = array();

$MenuItems['Fina']['Transactions']['Caption'] = array(	
_('其它收入录入'),
_('其它支出录入'),
_('其它收入/支出主管审核'),
_('其它收入/支出财务审核'),
_('其它收入/支出修改')
);
$MenuItems['WIP']['Transactions']['Caption'] = array ( 
_('订单转工单处理'),
_('工单建立'),
_('工单修改'),
//  _('工单生产排程调整签核'),
//   _('工单用料修改'), 
//	_('工单工序维护'),
//	_('工单产出处理'),
//	 _('工单领料申请'),
//	_('工单领料单打印'),
_('工单领料'),
_('工单整批领料'),
_('外协整批领料'),
_('工单超耗领料'),
_('工单领料部门审核'),
_('工单领料仓库审核'),
_('工单退料'),
// _('工单整批退料'),
_('工单退料部门审核'),
_('工单退料仓库审核'),
_('工单产出资料修改'),
_('工单送检及报检单补打'),

_('工单完工入库处理'),
_('工单完工入库审核'),
_('工单成品返工处理'),
_('工单关闭'),
_('工单强制关闭'),
_('生产外协申请'),

_('转常州生产'),
_('常州回货'),
_('工序强制关闭')

);

$MenuItems['WIP']['Transactions']['URL'] = array(
'/WIPCreate.php', 
'/WIPJobCreate.php',
'/WIPModify.php',

// '/WIPOPComplete.php',
// '/WIPMaterialRequest.php',
//  '/WIPMaterialPrint.php',
// '/WIPMaterialIssue.php',
'/WIPIssue.php',
'/WIPMaterialBatch.php',
'/OSPMaterialBatch.php',
'/WIPMaterialOverIssue.php',
'/WIPIssueDepartApproved.php',	
'/WIPIssueSubApproved.php',	
'/WIPMaterialReturn.php',
// '/WIPReturnBatch.php',
'/WIPReturnDepartApproved.php',	
'/WIPReturnSubApproved.php',	
'/WIPMaterialHour.php' ,
'/WIPCompleteToQc.php' ,
'/WIPCompleteInSub.php' ,
'/WIPCompleteInSubApproved.php' ,
'/WIPCompleteOutSub.php' , 
'/WIPClose.php' 	, 
'/WIPClose2.php' 	, 
'/WIPOSPApply.php',

'/WIPToChangzhou.php' , 
'/WIPChangzhouBack.php',
'/WIPSeqNumClose.php',
);

$MenuItems['WIP']['Reports']['Caption'] = array( 
_('工单排程查询'),  
_('工单条码打印'),
// _('工单完工查询'),
// _('工单生产明细查询'),
//  _('工单工时明细查询'),
// _('工单排程变更历史查询'),
_('工单超耗记录查询'), 
_('工单料况明细报表'),
_('工单未领料明细报表'),
_('工单缺料明细报表'),
_('未入库工单明细查询'),
_('工单领退料明细报表'),
_('工单入库明细查询'),
_('已入库未关闭工单明细报表'),
_('工单资料查询报表'),
_('常州生产汇总报表'),
_('常州生产明细报表'),
_('工单产出明细查询'),
_('工单产出汇总查询'),
_('工单生产中明细'),
_('生产看板')
);

$MenuItems['WIP']['Reports']['URL'] = array(
'/WIPScheduling.php',
'/WIPSNPrint.php',
//'/WIPOPCompleteReport.php',
//'/WIPOPCompleteDetailReport.php',
//'/WIPResoucreReport.php',
//'/WIPMaterialPrintReport.php',
'/WIPMaterialOverReport.php',
'/WIPMaterialDetail.php',
'/WIPWaitMaterial.php',
'/WIPShortMaterial.php',
'/WIPWaitCompleteIn.php',
'/WIPMaterialInOutQuery.php',
'/WIPCompleteInOutQuery.php',								
'/WIPWaitColse.php',										 
'/WIPQueryDetail.php',										 
'/WIPChangzhouReport.php',									 
'/WIPChangzhouDetailReport.php',									 
'/WIPCompleteDetailReport.php',									 
'/WIPCompleteSumReport.php',
'/WIPProductingReport.php',
'/WIP_ekanban.php'
);

$MenuItems['WIP']['Maintenance']['Caption'] = array( 
_('工艺维护')
);

$MenuItems['WIP']['Maintenance']['URL'] = array( 
'/Route.php'
);
$MenuItems['OSP']['Transactions']['Caption'] =  array(
  _('外协采购单建立'),
  _('外协采购单修改'),
  _('外协采购单审核'),
  //  _('外协采购单高阶审核'),                                                          
  _('外协采购来料报检单'),
  _('外协采购单批量来料报检'),
  _('外协采购单入库'),
  //_('外协无采购单费用录入'),
  //_('外协无采购单对账'),
 // _('外协无采购单对账取消'),
  _('外协采购对账'),
  _('外协采购对账取消')
);

$MenuItems['OSP']['Transactions']['URL'] = array(
  '/OspOrderCreate.php',

  '/OspOrderModify.php',
  '/OspOrderApprove.php',
  //   '/OspOrderMaxApprove.php',
  // '/POFinApproved.php',
  '/OSPSearchPOReceipt.php',
  '/OSPVendorReceipt.php',
'/OSPInPODelivery.php',

  //'/OSPNoPoFeeReceipt.php',
  //'/OSPNoPoFeeCheck.php',
  //'/OSPNoPoFeeCheckCancel.php',
  '/CheckOSPPODelivery.php',
  '/CheckOSPPODeliveryUpdate.php'
);

$MenuItems['OSP']['Reports']['Caption'] = array(
  _('外协采购单查询'),
  _('外协采购单明细查询'),
  _('外协采购单未收货明细查询'),
  _('外协采购入库明细报表'),
  _('外协采购入库汇总报表'),
  _('外协采购待对账报表'),
  _('外协采购已对账报表'),
  //_('外协无采购单收货明细'),
  //_('外协无采购单已对账报表'),
  _('外协采购单统计'),
  _('外协合格率')
);

$MenuItems['OSP']['Reports']['URL'] = array(
  '/OspSearchPO.php',
  '/OspPODetail.php',
  '/OspPOWaitdeliveryDetail.php',
  '/OspInPODeliveryReport.php',
  '/OspInPODeliverySumReport.php',
  '/OspPOCheckWaitDelivery.php',
  '/OspPOCheckWaitedDelivery.php',
  //'/OspNotPODeliveryReport.php',
  //'/OspNotPOCheckReport.php',
  '/SearchOspVendorSum.php',
  '/WIPOspGoodRateReport.php'
);

$MenuItems['OSP']['Maintenance']['Caption'] = array();

$MenuItems['OSP']['Maintenance']['URL'] = array();

$MenuItems['FIN']['Transactions']['Caption'] = array(	
  _('库存月结'),
// _('在制品月结') 
);

$MenuItems['FIN']['Transactions']['URL'] = array(	
  '/InvStock.php',
// '/WIPStock.php'
);

$MenuItems['FIN']['Reports']['Caption'] = array(	
_('进耗存明细表'),
_('入库明细表'),
_('出库明细表'),
// _('原材料进耗存明細表'),
// _('库存WIP明细表'),
// _('成品进耗存明细表'),
// _('成品库存明细表') 
);

$MenuItems['FIN']['Reports']['URL'] = array(	
'/CstINVDetailReport.php',
'/InDetailReport.php',
'/OutDetailReport.php',
// '/CstINVMDetailReport.php',
// '/CstWIPDetailReport.php',
// '/CstINVFDetailReport.php',
// '/CstINVOnhandReport.php'  
);

$MenuItems['FIN']['Maintenance']['Caption'] = array(  
);

$MenuItems['Fina']['Maintenance']['URL'] = array(  '/BankSetup.php',
'/ExpTypes.php'  );

$MenuItems['Fina']['Transactions']['URL'] = array(	'/ExpInCreate.php',
'/ExpOutCreate.php',
'/ExpInApproved.php',
'/ExpInFInApproved.php',
'/ExpInModify.php');

$MenuItems['Fina']['Reports']['Caption'] = array(	_('银行/账户余额查询'),
_('账户流水查询'),
_('其它收入支出报表查询'),
_('收入支出报表汇总查询'),
_('其它收入/支出待主管审核'),
_('其它收入/支出待财务审核'));

$MenuItems['Fina']['Reports']['URL'] = array(	
'/BankonhandReport.php',
'/ExpDayListReport.php',
'/ExpOutDayReport.php',
'/ExpOutSumReport.php',
'/ExpInWaitApproved.php',
'/ExpInFInWaitApproved.php'  );

$MenuItems['Fina']['Maintenance']['Caption'] = array(  _('银行账户设置'),
_('收入和支出类型管理')
);

$MenuItems['Fina']['Maintenance']['URL'] = array(  '/BankSetup.php',
'/ExpTypes.php'  );

$MenuItems['AP']['Transactions']['Caption'] = array(
  _('供应商发票管理'), 
    // _('供应商发票录入'), 
_('供应商发票审核'),
// _('供应商发票修改'), 
_('供应商付款管理'),
// _('供应商付款录入'),
_('供应商付款签核'),
_('供应商付款执行'),
  _('预付款冲销发票处理'),
  _('预付款冲销发票审核')

// _('供应商付款修改'),
// _('供应商红字发票录入'), 
// _('供应商扣款录入')

);

$MenuItems['AP']['Transactions']['URL'] = array(	
'/APInvoiceModify.php',
//  '/APInvoice.php',
'/APInvoiceApproved.php',
// '/APInvoiceModify.php',
'/APPaymentModify.php',
// '/APPayment.php',
'/APPaymentApproved.php',
'/APPaymentExecute.php',
'/APPrePaymentPay.php',
'/APPrePaymentPayApproved.php'

// '/APPaymentModify.php',
// '/APInvoiceBack.php',
// '/APPaymentBack.php'

);

$MenuItems['AP']['Reports']['Caption'] = array(	  _('已对账未开票明细报表'),   
_('已对账未付款明细查询'),
_('已对账未开票汇总报表'),
_('已对账未付款汇总报表') ,            
_('供应商发票明细报表'),
_('供应商发票对应采购单明细报表'),
_('已付款明细查询'),
// _('已付款账款对应采购单明细报表')

);

$MenuItems['AP']['Reports']['URL'] = array(	  '/APSearchWaitinvoice.php',
'/APSearchWaitPayment.php',
'/APWaitInvoicesum.php',
'/APWaitPaymentsum.php',
'/APSearchInvoice.php',
'/APSearchInvoiceMatchPO.php',
'/APSearchPayment.php',
// '/APSearchPaymentMatchPO.php'
);

$MenuItems['AP']['Maintenance']['Caption'] = array(	_('供应商付款条件维护') );

$MenuItems['AP']['Maintenance']['URL'] = array(	 '/APPaymentTerm.php' );

$MenuItems['AR']['Transactions']['Caption'] = array(
_('客户发票管理'), 
//  _('客户发票录入'), 
_('客户发票审核'),
// _('客户发票修改'), 
_('客户收款管理'), 
// _('客户收款录入'),
_('客户收款签核'),
// _('客户收款修改'),
// _('客户红字发票录入'), 
// _('客户退款录入')

);

$MenuItems['AR']['Transactions']['URL'] =     array(  
'/ARInvoiceModify.php',
//  '/ARInvoice.php',
'/ARInvoiceApproved.php',
// '/ARInvoiceModify.php',
'/ARPaymentModify.php',
// '/ARPayment.php',
'/ARPaymentApproved.php',
// '/ARPaymentModify.php',
// '/ARInvoiceBack.php',
// '/ARPaymentBack.php'

);

$MenuItems['AR']['Reports']['Caption'] =           array(  _('已销售对账未开票明细报表'), 
_('未收款明细查询'),  
_('客户已对账未开票汇总报表'),
_('客户已对账未付款汇总报表'),
_('客户发票明细报表'),
_('客户发票与销售订单匹配报表'),
_('已收款明细查询'),
_('已收款冲销订单报表')

);
$MenuItems['AR']['Reports']['URL'] = array('/ARSearchWaitinvoice.php',
'/ARSearchWaitPayment.php',
'/ARSearchWaitinvoiceSum.php',
'/ARSearchWaitPaymentSum.php',
'/ARSearchInvoice.php',
'/ARSearchInvoiceMatchSO.php',
'/ARSearchPayment.php',
'/ARSearchPaymentMatchSO.php');
$MenuItems['AR']['Maintenance']['Caption'] = array(	 );
$MenuItems['AR']['Maintenance']['URL'] = array(	 );

$MenuItems['SH']['Transactions']['Caption'] = array(
  _('问题反馈单'),
  _('问题反馈单修改'),
  _('问题反馈单关闭'),
  _('售后工单建立'),
  _('售后工单领料'),
);
  
$MenuItems['SH']['Transactions']['URL'] = array(  
  '/ProblemFeedback.php' ,
  '/ProblemFeedbackModify.php' ,
  '/ProblemFeedbackClose.php' ,
  '/WIPAfterSale.php' ,
  '/WIPAfterSaleMaterial.php' ,

);

$MenuItems['SH']['Reports']['Caption'] = array(  
  _('问题反馈单查询'),
  _('售后工单领料明细报表'),

);
$MenuItems['SH']['Reports']['URL'] = array(
  '/ProblemFeedbackReport.php',
  '/SHWIPMaterialInOutQuery.php',
);

$MenuItems['SH']['Maintenance']['Caption'] = array(	 );
$MenuItems['SH']['Maintenance']['URL'] = array(	 );

$MenuItems['QM']['Transactions']['Caption']=array(	
  _('采购单进货检测'),
 _('采购单进货检测修改'),
_('任务单品质检验'),
_('外协采购单检验') ,

);

$MenuItems['QM']['Transactions']['URL'] = array(	
'/InPOCheck.php',
'/InPOCheckModify.php',
'/WIPCompleteQc.php',
'/OSPPoDelivery.php',

);
$MenuItems['QM']['Reports']['Caption'] = array(	      //  _('采购单进货检验结果查询'),
_('待检验采购单信息查询'), 
_('采购进料检验明细查询'), 
_('采购进料检验汇总查询'), 
_('工单检验明细查询')
);
$MenuItems['QM']['Reports']['URL'] = array(           //  '/InPOCheck2.php',
'/InPOCheck3.php', 
'/InPOCheck4.php', 
'/InPOCheck6.php',
'/WIPCompleteQcReport.php'
);

$MenuItems['QM']['Maintenance']['Caption'] = array(	);

$MenuItems['QM']['Maintenance']['URL'] = array(	);

$MenuItems['HR']['Transactions']['Caption'] = array(	   _('人事资料建立'),  
_('人事资料修改')
);

$MenuItems['HR']['Transactions']['URL'] = array(	   '/HrEmployee.php',  
'/HrEmployeeModify.php'
);

$MenuItems['HR']['Reports']['Caption'] = array(	 
_('人事资料查询')
);

$MenuItems['HR']['Reports']['URL'] = array(	 
'/HrEmployeeReports.php' 
);

$MenuItems['HR']['Maintenance']['Caption'] = array( 
_('部门新增'),
_('部门修改')
);

$MenuItems['HR']['Maintenance']['URL'] = array( '/AddDepart.php',
'/SearchDept.php'
);

$MenuItems['MRP']['Transactions']['Caption'] = array(	_('需求计算')  );

$MenuItems['MRP']['Transactions']['URL'] = array(	'/MRPcreate.php' );

$MenuItems['MRP']['Reports']['Caption'] = array(	 _('缺料明细报表'),
_('缺料汇总报表'),
_('需求明细报表'),
_('供给明细报表')   );

$MenuItems['MRP']['Reports']['URL'] = array(	'/MRPShortPlan.php',
'/MRPShortSumReport.php',
'/MRPDemanddetail.php' ,
'/MRPSupplyReport.php',
'/MRPexcess.php'    );

$MenuItems['MRP']['Maintenance']['Caption'] = array( );

$MenuItems['MRP']['Maintenance']['URL'] = array(  );

$MenuItems['system']['Transactions']['Caption'] = array(_('帐号设置'),
_('权限设置'),
_('用户功能设置'),
_('程序设置'),
_('微信小程序账号管理'),
//  _('税率维护'),                                                       
// _('料号大分类维护'),
//  _('料号小分类维护')
);

$MenuItems['system']['Transactions']['URL'] = array('/WWW_Users.php',
'/SearchUserForFunction.php',
'/SetFunction.php',
'/SetProcess.php',
'/WxAccountset.php',
// '/taxset.php',
//  '/segment1set2.php',
//  '/segment1set3.php',)
);

$MenuItems['system']['Reports']['Caption'] = array(

//_('成品料号查询'),
//_('材料料号维护'),
//_('成品料号维护'),
// _('单位维护'),
// _('材料类型维护'),
//_('材料分类维护'),
// _('仓库维护'),
//_('部门新增'),
// _('部门修改'),
// _('员工维护')
_('客户分类维护'),
_('人员部门维护'),
_('项目名称维护'),
_('币别维护'),
_('税率维护'),                                         
_('线别维护'),  
_('交易类型设置'),           
// _('产品类型维护'),
_('材料分类维护'),
_('单位维护'),
_('仓库维护'),
_('付款条件维护'),
_('温度维护'),
_('湿度维护'),
_('部门维护'),
_('员工维护'),
_('公司资料维护'),
);

$MenuItems['system']['Reports']['URL']   = array(
//'/ItemFG_No.php',
// '/SearchItemNo.php',
// '/SearchFGItemNo.php',
// '/UnitsOfMeasure.php',
// '/ItemCategory.php',
//  '/ItemType.php',                  
// '/Locations.php',
// '/AddDepart.php',
// '/SearchDept.php',
//'/SearchEmployee.php'
'/Customer_order_type.php',
'/updatedepartrelate.php',
'/project_name_type.php',
'/Currency.php',
'/taxset.php',
'/xianbie.php',
'/invclassset.php',
// '/segment1set2.php',
'/segment1set3.php',
'/UnitsOfMeasure.php',
'/Locations.php',
'/PaymentMain.php', 
'/WenduMain.php', 
'/ShiduMain.php', 
'/SearchDept.php', 
'/SearchEmployee.php',
'/SearchCompany.php',
);  
$MenuItems['system']['Maintenance']['Caption'] = array(

_('币别维护'),
_('税率维护'),                                         
_('线别维护'),              
// _('产品类型维护'),
_('材料分类维护'),
_('单位维护'),
_('仓库维护'),
_('付款条件维护'),
_('部门维护'),
_('员工维护'),
_('公司资料维护'),
_('采购订单类型维护'),
_('生产目标设定')

);

$MenuItems['system']['Maintenance']['URL'] = array(
// '/SearchFGItemNo.php',

'/Currency.php',
'/taxset.php',
'/xianbie.php',
// '/segment1set2.php',
'/segment1set3.php',
'/UnitsOfMeasure.php',
'/Locations.php',
'/PaymentMain.php', 
'/SearchDept.php', 
'/SearchEmployee.php',
'/SearchCompany.php',
'/po_order_type.php',
'/wip_aim.php'

);
?>

