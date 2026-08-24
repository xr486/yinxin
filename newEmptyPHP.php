<?php

/* $Id: MainMenuLinksArray.php 6190 2013-08-12 02:12:02Z rchacon $ */

/* webERP menus with Captions and URLs. */

$ModuleLink = array('orders', 'work', 'Purchase', 'Quality', 'fina', 'system');
$ReportList = array('orders' => 'ord',
    'work' => 'work',
    'Purchase' => 'PO',
    'Quality' => 'QA',
    'fina' => 'fin',
    'system' => 'sys'
);

/* The headings showing on the tabs accross the main index used also in WWW_Users for defining what should be visible to the user */
$ModuleList = array(_('业务'),
    _('生产'),
    _('采购'),
    _('品保'),
    _('财务'),
    _('Setup'));

$MenuItems['orders']['Transactions']['Caption'] = array(_('建立订单'),
    _('订单出货'),
    _('订单收款'),);

$MenuItems['orders']['Transactions']['URL'] = array('/AddOrder.php',
    '/SearchShipOrder.php',
    '/OrderReceivables.php');

$MenuItems['orders']['Reports']['Caption'] = array(_('查询订单'),
    _('查询出货'));

$MenuItems['orders']['Reports']['URL'] = array('/SearchContractHeaders.php',
    '/SearchTransferRequest.php');

$MenuItems['orders']['Maintenance']['Caption'] = array(_('建立客户'),
    _('建立业务员'));

$MenuItems['orders']['Maintenance']['URL'] = array('/AddCustomer.php',
    '/AddSalesman.php');

$MenuItems['work']['Transactions']['Caption'] = array(_('生产资料确认'),
    _('分配生成人员'),
    _('开始生产确认'),
    _('完成生成确认'));

$MenuItems['work']['Transactions']['URL'] = array('/SearchConfirmOrder.php',
    '/DistributionWorkPeople.php',
    '/StartWorkConfirm.php',
    '/EndWorkConfirm.php');

$MenuItems['work']['Reports']['Caption'] = array( _('查询生产单'),
 _('完工入库查询'),
 _('贴片异常查询'));


$MenuItems['work']['Reports']['URL'] = array('/SearchCarRequestHeaders.php',
    '/SearchStorage.php',
    '/SearchException.php');

$MenuItems['work']['Maintenance']['Caption'] = array(_('贴片异常维护'));

$MenuItems['work']['Maintenance']['URL'] = array('/PatchException.php');

$MenuItems['Purchase']['Transactions']['Caption'] = array(_('建立采购单'),
    _('采购单入库'),
    _('付款供应商'));

$MenuItems['Purchase']['Transactions']['URL'] = array('/AddPurchaseOrder.php',
    '/POReceipt.php',
    '/PaySupplier.php');

$MenuItems['Purchase']['Reports']['Caption'] = array(_('查询采购单'),
    _('查询采购单入库量'));

$MenuItems['Purchase']['Reports']['URL'] = array('/SearchInspect.php',
    '/SearchDelivery.php');

$MenuItems['Purchase']['Maintenance']['Caption'] = array(_('维护供应商'));

$MenuItems['Purchase']['Maintenance']['URL'] = array('/SearchSupplier.php');

$MenuItems['Quality']['Transactions']['Caption'] = array(_('填写生产日报'));

$MenuItems['Quality']['Transactions']['URL'] = array('/WorkDailyReport.php');

$MenuItems['Quality']['Reports']['Caption'] = array(_('查询生产日报'));

$MenuItems['Quality']['Reports']['URL'] = array('/SearchLeaveFactory.php');

$MenuItems['Quality']['Maintenance']['Caption'] = array();

$MenuItems['Quality']['Maintenance']['URL'] = array();

$MenuItems['fina']['Transactions']['Caption'] = array(_('客户开发票'),
    _('客户收款'),
    _('钢网供应商付款'),
    //_('费用立账'),
    _('费用付款'));

$MenuItems['fina']['Transactions']['URL'] = array('/InvoiceForAccount.php',
    '/AccountsReceivable.php',
    '/PaySupplier.php',
    //  '/FeeInvoice.php',
    '/FeePayment.php');

$MenuItems['fina']['Reports']['Caption'] = array(_('客户开票明细查询'),
    _('客户收款查询'),
    _('钢网供应商付款查询'),
    _('费用立账查询'));

$MenuItems['fina']['Reports']['URL'] = array('/SearchInvoice.php',
    '/SearchReceivable.php',
    '/SearchPaySupplier.php',
    '/SearchFeeInvoice.php');

$MenuItems['fina']['Maintenance']['Caption'] = array(_('费用类型管理'));

$MenuItems['fina']['Maintenance']['URL'] = array('/FeeTypes.php');


$MenuItems['system']['Transactions']['Caption'] = array(_('帐号设置'),
    _('权限设置'));

$MenuItems['system']['Transactions']['URL'] = array('/WWW_Users.php',
    '/SearchUserForFunction.php');

$MenuItems['system']['Reports']['Caption'] = array();

$MenuItems['system']['Reports']['URL'] = array();

$MenuItems['system']['Maintenance']['Caption'] = array();

$MenuItems['system']['Maintenance']['URL'] = array();
?>
