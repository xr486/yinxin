<?php
//  首先引入XLSXWriter包

putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");
ob_start();
include('includes/session2.inc');
include('includes/SQL_CommonFunctions.inc');
unset($result);

if (isset($_GET['vendorCode'])) {
    $vendorCode = $_GET['vendorCode'];
} else if (isset($_POST['vendorCode'])) {
    $vendorCode = $_POST['vendorCode'];
}
if (isset($_GET['vendorName'])) {
    $vendorName = $_GET['vendorName'];
} else if (isset($_POST['vendorName'])) {
    $vendorName = $_POST['vendorName'];
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
if (isset($_GET['po_num_from'])) {
    $po_num_from = $_GET['po_num_from'];
} else if (isset($_POST['po_num_from'])) {
    $po_num_from = $_POST['po_num_from'];
}
if (isset($_GET['receipt_num_from'])) {
    $receipt_num_from = $_GET['receipt_num_from'];
} else if (isset($_POST['receipt_num_from'])) {
    $receipt_num_from = $_POST['receipt_num_from'];
}
if (isset($_GET['operation_code'])) {
    $operation_code = $_GET['operation_code'];
} else if (isset($_POST['operation_code'])) {
    $operation_code = $_POST['operation_code'];
}

  
$sql = "SELECT prt.receipt_num,prr.receipt_line,a.waixie_num,b.waixie_line, a.vendor_code, d.vendor_name, prt.transaction_quantity, prt.transaction_type, prr.subinventory_code, b.need_date, prt.transaction_date,prt.created_by,b.need_date,b.uom,
                            b.order_number,b.operation_code,b.need_remark,b.gongshi
FROM waixie_headers_all a, waixie_lines_all b, vendors d, po_rcv_transactions prt,po_rcv_receipt_line prr
WHERE  a.waixie_num = b.waixie_num
AND a.vendor_code = d.vendor_code
AND b.waixie_num = prt.po_num
and prr.receipt_num=prt.receipt_num
and prr.receipt_line=prt.receipt_line
and prt.po_num=prr.po_num
and prt.po_line=prr.po_line  
and  prt.transaction_type in ('RECEIVE')
AND b.waixie_line = prt.po_line 
and b.waixie_num=prr.po_num
";


if(isset($vendorName) and $vendorName != ''){
    $sql = $sql." and d.vendor_name ".LIKE." '%".$vendorName."%' ";
}
if(isset($vendorCode) and $vendorCode != ''){
    $sql = $sql." and d.vendor_code ".LIKE." '%".$vendorCode."%' ";
}

if (isset($po_num_from) and $po_num_from != '') { 
	$sql = $sql." and a.waixie_num ".LIKE." '%".$po_num_from."%' ";
}     
if (isset($receipt_num_from) and $receipt_num_from != '') { 
	$sql = $sql." and prt.receipt_num ".LIKE." '%".$receipt_num_from."%' ";
}
if (isset($operation_code) and $operation_code != '') { 
	$sql = $sql." and b.operation_code ".LIKE." '%".$operation_code."%' ";
}
 
 
	  if (empty($FromDate) == 0) 
  {
    $SQL_FromDate = strtotime($FromDate);
    $sql .= " and d.creation_date 	 >= '" . $SQL_FromDate . "' ";
  }
  if (empty($ToDate) == 0) 
  {
     $SQL_ToDate = strtotime($ToDate) + 86400;
     $sql .= " and d.creation_date 	 <='" . $SQL_ToDate . "' ";
  }
  
$sql .=" order by  prt.receipt_num,prr.receipt_line  ";

    $result_num = DB_query($sql,$db);
//oci_execute($par);
include_once("xlsxwriter.class.php");
$date=date('YmdHis');
//ini_set('display_errors', 0);
//ini_set('log_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);

$filename = "外协采购单收货明细报表".$date.".xlsx";
header('Content-disposition: attachment; filename="'.XLSXWriter::sanitize_filename($filename).'"');
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate');
header('Pragma: public');
$rows2 = array( 
  array('外协采购单收货明细'),
);
 
$rows = array( 
  array('收料单号','行','供应商简称','采购单号','行','订单号','外协工序','要求','单位','入库数量','入库日期','入库人员' ),
); 
 

$writer = new XLSXWriter();
$writer->setAuthor('Shunfansoft'); 

//$writer->writeSheetHeader('Sheet1', $header);
 foreach($rows2 as $row2)
	$writer->writeSheetRow('Sheet1', $row2);
foreach($rows as $row)
	$writer->writeSheetRow('Sheet1', $row);

	while ($v = DB_fetch_array($result_num)) {

		  

     $writer->writeSheetRow('Sheet1', array($v['receipt_num'],$v['receipt_line'],$v['vendor_code'],
        $v['waixie_num'],$v['waixie_line'],$v['order_number'],$v['operation_code'],$v['need_remark'],$v['uom'],$v['transaction_quantity'],date('Y-m-d H:i:s', $v['transaction_date']),$v['created_by']
         ));
	 }
    

$writer->writeToStdOut();
//$writer->writeToFile('example.xlsx');
//echo $writer->writeToString();
exit(0);
?>
