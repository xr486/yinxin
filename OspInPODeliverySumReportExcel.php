<?php
//  首先引入XLSXWriter包

putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");
ob_start();
include('includes/session2.inc');
include('includes/SQL_CommonFunctions.inc');
unset($result);
 
 
$sql = "SELECT a.vendor_code, d.vendor_name,b.stockid,c.item_name,   prt.receipt_type, b.operation_code, sum(prr.transaction_quantity) transaction_quantity
FROM waixie_headers_all a, waixie_lines_all b,so_lines_all c, vendors d, waixie_rcv_receipt_header prt,waixie_rcv_receipt_line prr
WHERE  a.po_num = b.po_num
AND a.vendor_code = d.vendor_code
AND b.po_num = prr.po_num and b.wip_entity_name=c.wip_entity_name
and prr.receipt_num=prt.receipt_num
and b.stockid=prr.stockid
AND b.line = prr.po_line   ";

if(isset($_GET['vendorCode']) and $_GET['vendorCode'] != ''){
    $sql = $sql." and d.vendor_code ".LIKE." '%".$_GET['vendorCode']."%' ";
}
if(isset($_GET['vendorName']) and $_GET['vendorName'] != ''){
    $sql = $sql." and d.vendor_name ".LIKE." '%".$_GET['vendorName']."%' ";
}
if(isset($_GET['FromDate']) and $_GET['FromDate'] != ''){
    $sql = $sql." and prt.delivery_date >=".strtotime($_GET['FromDate'])." ";
}
 if(isset($_GET['ToDate']) and $_GET['ToDate'] != ''){
    $sql = $sql." and prt.delivery_date <=".strtotime($_GET['ToDate'])." ";
}
if (isset($_GET['Stockid_from']) and $_GET['Stockid_from'] != '') { 
    $sql = $sql." and prr.stockid ".LIKE." '%".$_GET['Stockid_from']."%' ";
}
if (isset($_GET['item_name']) and $_GET['item_name'] != '') { 
    $sql = $sql." and c.item_name ".LIKE." '%".$_GET['item_name']."%' ";
}
if (isset($_GET['receipt_num_from']) and $_GET['receipt_num_from'] != '') {
    $sql = $sql . " and prt.receipt_num ".LIKE." '%".$_GET['receipt_num_from']."%' ";
}
 

if (isset($_GET['po_num_from']) and $_GET['po_num_from'] != '') {
    $sql = $sql . " and a.po_num  ".LIKE." '%".$_GET['po_num_from']."%' ";
}
if (isset($_GET['operation_code']) and $_GET['operation_code'] != '') { 
    $sql = $sql." and b.operation_code ".LIKE." '%".$_GET['operation_code']."%' ";
}
$sql .=" group by a.vendor_code, d.vendor_name,b.stockid,c.item_name,   prt.receipt_type, b.operation_code"; 

 $result_num = DB_query($sql, $db); 
//oci_execute($par);
include_once("xlsxwriter.class.php");
$date=date('YmdHis');
//ini_set('display_errors', 0);
//ini_set('log_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);

$filename = "采购入库汇总报表".$date.".xlsx";
header('Content-disposition: attachment; filename="'.XLSXWriter::sanitize_filename($filename).'"');
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate');
header('Pragma: public');
$rows2 = array( 
  array('采购入库汇总报表'),

);
$rows = array( 
  array('供应商编码','供应商名称','零件图号','零件名称','工序名', '入库数量'),

); 
 
      
$writer = new XLSXWriter();
$writer->setAuthor('Shunfansoft'); 

//$writer->writeSheetHeader('Sheet1', $header);
 foreach($rows2 as $row2)
	$writer->writeSheetRow('Sheet1', $row2);
foreach($rows as $row)
	$writer->writeSheetRow('Sheet1', $row);

	while ($v = DB_fetch_array($result_num)) {
  
	 $writer->writeSheetRow('Sheet1', array($v['vendor_code'],$v['vendor_name'],$v['stockid'],$v['item_name'],$v['operation_code'],$v['transaction_quantity'] ));
	 }
    
	

$writer->writeToStdOut();
//$writer->writeToFile('example.xlsx');
//echo $writer->writeToString();
exit(0);
