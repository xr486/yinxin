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



$sql = "select ap_invoice_type,
			      pha.invoice_num,
			   pha.invoice_amount,
			       pha.tax_amount,
                  c.vendor_code,
                    pha.narrative,
				pha.currency_code,
                 pha.invoice_date,
                pha.creation_date,pha.created_by,pha.vendor_code,c.vendor_name,pha.status,pha.tax_code
	from ap_invoice_headers_all pha, vendors c
            where  pha.vendor_code=c.vendor_code ";
if(isset($invoice_num) and $invoice_num != ''){
    $sql = $sql." and pha.invoice_num ".LIKE." '%".$invoice_num."%' ";
}
if(isset($vendor_code) and $vendor_code != ''){
    $sql = $sql." and c.vendor_code ".LIKE." '%".$vendor_code."%' ";
}
if(isset($vendor_name) and $vendor_name != ''){
    $sql = $sql." and c.vendor_name ".LIKE." '%".$vendor_name."%' ";
}
if(isset($FromDate) and $FromDate != ''){
    $sql = $sql." and pha.invoice_date >=".strtotime($FromDate)." ";
}
if(isset($ToDate) and $ToDate != ''){
    $sql = $sql." and pha.invoice_date <=".strtotime($ToDate)." ";
}

 

$sql .=" order by pha.invoice_date ";
 $result_num = DB_query($sql, $db); 
//oci_execute($par);
include_once("xlsxwriter.class.php");
$date=date('YmdHis');
//ini_set('display_errors', 0);
//ini_set('log_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);

$filename = "供应商发票明细报表".$date.".xlsx";
header('Content-disposition: attachment; filename="'.XLSXWriter::sanitize_filename($filename).'"');
header("Content-wip_entity_name: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate');
header('Pragma: public');
$rows2 = array( 
  array('供应商发票明细报表'),

);
$rows = array( 
  array('发票号码','状态','供应商','供应商','总额','税别','税额','备注','发票日','建立时间','建立人员'),

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
            $v_waitquantity=$v['check_amount']-$v['invoice_amount']-$v['dis_invoice_amount'] ;
	 $writer->writeSheetRow('Sheet1', array($v['invoice_num'],$v['status'],$v['vendor_code'],$v['vendor_name'],$v['invoice_amount'],$v['tax_code'],$v['tax_amount'],$v['narrative'],date('Y-m-d', $v['invoice_date']),date('Y-m-d H:i:s', $v['plan_start_date']),$v['created_by'] ));
	 }
    
$writer->writeToStdOut();
//$writer->writeToFile('example.xlsx');
//echo $writer->writeToString();
exit(0);
?>
