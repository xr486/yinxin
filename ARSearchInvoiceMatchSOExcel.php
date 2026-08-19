<?php
//  首先引入XLSXWriter包

putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");
ob_start();
include('includes/session2.inc');
include('includes/SQL_CommonFunctions.inc');
unset($result);
 

if (isset($_GET['customer_name'])) {
    $customer_name = $_GET['customer_name'];
} else if (isset($_POST['customer_name'])) {
    $customer_name = $_POST['customer_name'];
}
if (isset($_GET['customer_code'])) {
    $customer_code = $_GET['customer_code'];
} else if (isset($_POST['customer_code'])) {
    $customer_code = $_POST['customer_code'];
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
if (isset($_GET['invoice_num'])) {
    $invoice_num = $_GET['invoice_num'];
} else if (isset($_POST['invoice_num'])) {
    $invoice_num = $_POST['invoice_num'];
}
if (isset($_GET['so_num'])) {
    $so_num = $_GET['so_num'];
} else if (isset($_POST['so_num'])) {
    $so_num = $_POST['so_num'];
}

$sql = 'select ah.ar_invoice_type,ah.invoice_num,ah.invoice_amount,ah.tax_amount,ah.invoice_date,ah.narrative
            ,ah.customer_code,ve.customer_name,ah.created_by,ah.creation_date,ah.currency_code,al.invoice_line,al.so_num ,al.amount,al.dis_amount 
			from  ar_invoice_headers_all ah, 
			 ar_invoice_lines_all al,
			customers ve 
			where  ve.customer_code=ah.customer_code
			and al.customer_code=ah.customer_code
			and ah.ar_invoice_type=al.ar_invoice_type
			and ah.invoice_num=al.invoice_num';
    
    if(isset($invoice_num) and $invoice_num != ''){
        $sql = $sql." and ah.invoice_num ".LIKE." '%".$invoice_num."%' ";
    }
    if(isset($so_num) and $so_num != ''){
        $sql = $sql." and al.so_num ".LIKE." '%".$so_num."%' ";
    }
    if(isset($customer_name) and $customer_name != ''){
        $sql = $sql." and ve.customer_name ".LIKE." '%".$customer_name."%' ";
    }
    if(isset($customer_code) and $customer_code != ''){
        $sql = $sql." and ve.customer_code ".LIKE." '%".$customer_code."%' ";
    }
	if(isset($FromDate) and $FromDate != ''){
        $sql = $sql." and invoice_date >=".strtotime($FromDate)." ";
    }
    if(isset($ToDate) and $ToDate != ''){
        $sql = $sql." and invoice_date <=".strtotime($ToDate)." ";
    }
  
    $result_num = DB_query($sql,$db);
//oci_execute($par);
include_once("xlsxwriter.class.php");
$date=date('YmdHis');
//ini_set('display_errors', 0);
//ini_set('log_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);

$filename = " 客户发票对应业务订单明细".$date.".xlsx";
header('Content-disposition: attachment; filename="'.XLSXWriter::sanitize_filename($filename).'"');
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate');
header('Pragma: public');
$rows2 = array( 
  array(' 客户发票对应业务订单明细'),
);

$rows = array( 
  array('客户代号','客户名称','发票号码','发票类型','发票日期','建单日期','发票金额','税金','发票备注','币别','发票行',
    '业务订单','开票金额','优惠金额'),
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

     $writer->writeSheetRow('Sheet1', array($v['customer_code'],$v['customer_name'],$v['invoice_num'],
       $v['ar_invoice_type'],date('Y-m-d', $v['invoice_date']),date('Y-m-d H:i:s', $v['creation_date']),
       $v['invoice_amount'],$v['tax_amount'],$v['narrative'],$v['currency_code'],$v['invoice_line']
       ,$v['so_num'],$v['amount'],$v['dis_amount']));
	 }
    
$writer->writeToStdOut();
//$writer->writeToFile('example.xlsx');
//echo $writer->writeToString();
exit(0);
?>
