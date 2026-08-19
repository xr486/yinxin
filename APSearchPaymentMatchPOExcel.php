<?php
//  首先引入XLSXWriter包

putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");
ob_start();
include('includes/session2.inc');
include('includes/SQL_CommonFunctions.inc');
unset($result);
 

if (isset($_GET['vendor_code'])) {
    $vendor_code = $_GET['vendor_code'];
} else if (isset($_POST['vendor_code'])) {
    $vendor_code = $_POST['vendor_code'];
}
if (isset($_GET['vendor_name'])) {
    $vendor_name = $_GET['vendor_name'];
} else if (isset($_POST['vendor_name'])) {
    $vendor_name = $_POST['vendor_name'];
}
if (isset($_GET['po_num'])) {
    $po_num = $_GET['po_num'];
} else if (isset($_POST['po_num'])) {
    $po_num = $_POST['po_num'];
}
if (isset($_GET['bankchangenum'])) {
    $bankchangenum = $_GET['bankchangenum'];
} else if (isset($_POST['bankchangenum'])) {
    $bankchangenum = $_POST['bankchangenum'];
}
if (isset($_GET['transaction_num'])) {
    $transaction_num = $_GET['transaction_num'];
} else if (isset($_POST['transaction_num'])) {
    $transaction_num = $_POST['transaction_num'];
}
if (isset($_GET['bankaccountname'])) {
    $bankaccountname = $_GET['bankaccountname'];
} else if (isset($_POST['bankaccountname'])) {
    $bankaccountname = $_POST['bankaccountname'];
}
if (isset($_GET['FromDate'])) {
    $FromDate = $_GET['FromDate'];
} else if (isset($_POST['FromDate'])) {
    $FromDate = $_POST['FromDate'];
}
if (isset($_GET['ToDate'])) {
    $ToDate = $_GET['ToDate'];
} else if (isset($_POST['ToDate'])) {
    $ToDate = $_POST['ToDate'];
}



$sql = "select pha.transaction_type,
			      pha.bankaccountname,pha.transaction_date,
							    pha.bankchangenum,
							      pha.transaction_num,
							   pha.transaction_amount header_transaction_amount,pha.dis_amount header_dis_amount,
								   pha.tax_amount,
                                  pha.vendor_code,
                                    pha.narrative,
								pha.currency_code,a.po_num,a.check_amount,c.transaction_amount,c.dis_amount,pha.vendor_code,d.vendor_name 
				from fin_bank_transaction_lines_all c,po_headers_all a ,fin_bank_transaction_headers_all pha, vendors d
				where  c.po_num=a.po_num 
				and  pha.status='核准'
			and pha.transaction_type in ('AP付款','AP退款')
			and pha.transaction_num=c.transaction_num
			and pha.vendor_code=d.vendor_code
                ";

if(isset($vendor_code) and $vendor_code != ''){
    $sql = $sql." and d.vendor_code ".LIKE." '%".$vendor_code."%' ";
}
if(isset($vendor_name) and $vendor_name != ''){
    $sql = $sql." and d.vendor_name ".LIKE." '%".$vendor_name."%' ";
}
if(isset($po_num) and $po_num != ''){
    $sql = $sql." and a.po_num ".LIKE." '%".$po_num."%' ";
}
if(isset($bankchangenum) and $bankchangenum != ''){
    $sql = $sql." and pha.bankchangenum ".LIKE." '%".$bankchangenum."%' ";
}
if(isset($transaction_num) and $transaction_num != ''){
    $sql = $sql." and pha.transaction_num ".LIKE." '%".$transaction_num."%' ";
}
if(isset($bankaccountname) and $bankaccountname != ''){
    $sql = $sql." and pha.bankaccountname ".LIKE." '%".$bankaccountname."%' ";
}
if(isset($FromDate) and $FromDate != ''){
    $sql = $sql." and pha.transaction_date >=".strtotime($FromDate)." ";
}
if(isset($ToDate) and $ToDate != ''){
    $sql = $sql." and pha.transaction_date <=".strtotime($ToDate)." ";
}

 

$sql .=" order by  pha.transaction_date desc ";
 $result_num = DB_query($sql, $db); 
//oci_execute($par);
include_once("xlsxwriter.class.php");
$date=date('YmdHis');
//ini_set('display_errors', 0);
//ini_set('log_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);

$filename = "已付款账款对应采购单明细报表".$date.".xlsx";
header('Content-disposition: attachment; filename="'.XLSXWriter::sanitize_filename($filename).'"');
header("Content-wip_entity_name: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate');
header('Pragma: public');
$rows2 = array( 
  array('已付款账款对应采购单明细报表'),

);
$rows = array( 
  array('流水号','付款/转账单号','供应商','付款金额','免付款金额','备注','付款日','采购单','付款金额','优惠金额'),

); 
 
$writer = new XLSXWriter();
$writer->setAuthor('Shunfansoft'); 

//$writer->writeSheetHeader('Sheet1', $header);
 foreach($rows2 as $row2)
	$writer->writeSheetRow('Sheet1', $row2);
foreach($rows as $row)
	$writer->writeSheetRow('Sheet1', $row);

	while ($v = DB_fetch_array($result_num)) {

		 /* if ($v['status'] == 'INPROCESS') {
                $v_status = '待签核';
            } elseif ($v['status'] == 'APPROVED') {
                $v_status = '已签核';
            } elseif ($v['status'] == 'REJECTED') {
                $v_status = '已拒签';
            } else {
                $v_status = '已取消';
            } */

            
	 $writer->writeSheetRow('Sheet1', array($v['transaction_num'],$v['bankchangenum'],$v['vendor_code'],$v['header_transaction_amount'],$v['header_dis_amount'],$v['narrative'],date('Y-m-d', $v['transaction_date']),$v['po_num'],$v['transaction_amount'],$v['dis_amount'] ));
	 }
    
$writer->writeToStdOut();
//$writer->writeToFile('example.xlsx');
//echo $writer->writeToString();
exit(0);
?>
