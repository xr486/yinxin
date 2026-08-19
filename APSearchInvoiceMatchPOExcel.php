<?php
//  首先引入XLSXWriter包

putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");
ob_start();
include('includes/session2.inc');
include('includes/SQL_CommonFunctions.inc');
unset($result);
 

if (isset($_GET['invoice_num'])) {
    $invoice_num = $_GET['invoice_num'];
} else if (isset($_POST['invoice_num'])) {
    $invoice_num = $_POST['invoice_num'];
}
if (isset($_GET['po_num'])) {
    $po_num = $_GET['po_num'];
} else if (isset($_POST['po_num'])) {
    $po_num = $_POST['po_num'];
}
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



$sql = 'select ah.ap_invoice_type,ah.invoice_num,ah.invoice_amount,ah.tax_amount,ah.invoice_date,ah.narrative
            ,ah.vendor_code,ve.vendor_name,ah.created_by,ah.creation_date,ah.currency_code,al.invoice_line,al.po_num ,al.amount,al.dis_amount 
			from  ap_invoice_headers_all ah, 
			 ap_invoice_lines_all al,
			vendors ve 
			where  ve.vendor_code=ah.vendor_code
			and al.vendor_code=ah.vendor_code
			and ah.ap_invoice_type=al.ap_invoice_type
			and ah.invoice_num=al.invoice_num';
if(isset($invoice_num) and $invoice_num != ''){
    $sql = $sql." and ah.invoice_num ".LIKE." '%".$invoice_num."%' ";
}
if(isset($po_num) and $po_num != ''){
    $sql = $sql." and al.po_num ".LIKE." '%".$po_num."%' ";
}
if(isset($vendor_code) and $vendor_code != ''){
    $sql = $sql." and ve.vendor_code ".LIKE." '%".$vendor_code."%' ";
}
if(isset($vendor_name) and $vendor_name != ''){
    $sql = $sql." and ve.vendor_name ".LIKE." '%".$vendor_name."%' ";
}
if(isset($FromDate) and $FromDate != ''){
    $sql = $sql." and invoice_date >=".strtotime($FromDate)." ";
}
if(isset($ToDate) and $ToDate != ''){
    $sql = $sql." and invoice_date <=".strtotime($ToDate)." ";
}

 

$sql .=" order by  invoice_date desc ";
 $result_num = DB_query($sql, $db); 
//oci_execute($par);
include_once("xlsxwriter.class.php");
$date=date('YmdHis');
//ini_set('display_errors', 0);
//ini_set('log_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);

$filename = "供应商发票对应采购单明细报表".$date.".xlsx";
header('Content-disposition: attachment; filename="'.XLSXWriter::sanitize_filename($filename).'"');
header("Content-wip_entity_name: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate');
header('Pragma: public');
$rows2 = array( 
  array('供应商发票对应采购单明细报表'),

);
$rows = array( 
  array('供应商代号','供应商名称','发票号码','发票类型','发票日期','建单日期','发票金额','税金','发票备注','币别','发票行','采购单','开票金额','优惠金额'),

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

            
	 $writer->writeSheetRow('Sheet1', array($v['vendor_code'],$v['vendor_name'],$v['invoice_num'],$v['ap_invoice_type'],date('Y-m-d', $v['invoice_date']),date('Y-m-d h:i:s', $v['creation_date']),$v['invoice_amount'],$v['tax_amount'],$v['narrative'],$v['currency_code'],$v['invoice_line'],$v['po_num'],$v['amount'],$v['dis_amount'] ));
	 }
    
$writer->writeToStdOut();
//$writer->writeToFile('example.xlsx');
//echo $writer->writeToString();
exit(0);
?>
