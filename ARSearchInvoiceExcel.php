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

$sql = "select ar_invoice_type,
			      pha.invoice_num,
			   pha.invoice_amount,
			       pha.tax_amount,
                  c.customer_code,
                    pha.narrative,
				pha.currency_code,
                 pha.invoice_date,
                pha.creation_date,pha.created_by,pha.customer_code,c.customer_name,pha.status,pha.tax_code
	from ar_invoice_headers_all pha, customers c
            where  pha.customer_code=c.customer_code ";
    
    if(isset($invoice_num) and $invoice_num != ''){
        $sql = $sql." and pha.invoice_num ".LIKE." '%".$invoice_num."%' ";
    }
    if(isset($customer_name) and $customer_name != ''){
        $sql = $sql." and c.customer_name ".LIKE." '%".$customer_name."%' ";
    }
    if(isset($customer_code) and $customer_code != ''){
        $sql = $sql." and c.customer_code ".LIKE." '%".$customer_code."%' ";
    }
	if(isset($FromDate) and $FromDate != ''){
        $sql = $sql." and pha.invoice_date >=".strtotime($FromDate)." ";
    }
    if(isset($ToDate) and $ToDate != ''){
        $sql = $sql." and pha.invoice_date <=".strtotime($ToDate)." ";
    }
  
    $result_num = DB_query($sql,$db);
//oci_execute($par);
include_once("xlsxwriter.class.php");
$date=date('YmdHis');
//ini_set('display_errors', 0);
//ini_set('log_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);

$filename = " 客户发票明细".$date.".xlsx";
header('Content-disposition: attachment; filename="'.XLSXWriter::sanitize_filename($filename).'"');
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate');
header('Pragma: public');
$rows2 = array( 
  array(' 客户发票明细'),
);

$rows = array( 
  array('发票号码','类型','客户','客户','总额','税别','备注','发票日','建立时间','建立人员'),
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

     $writer->writeSheetRow('Sheet1', array($v['invoice_num'],$v['ar_invoice_type'],$v['customer_code'],
       $v['customer_name'],$v['invoice_amount'],$v['tax_code'],$v['narrative'],date('Y-m-d', $v['invoice_date']),
       date('Y-m-d H:i:s', $v['creation_date']),$v['created_by']));
	 }
    
$writer->writeToStdOut();
//$writer->writeToFile('example.xlsx');
//echo $writer->writeToString();
exit(0);
?>
