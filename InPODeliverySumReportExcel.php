<?php
//  首先引入XLSXWriter包

putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");
ob_start();
include('includes/session2.inc');
include('includes/SQL_CommonFunctions.inc');
unset($result);
 
$time =date("Y-m-d");

if (isset($_GET['vendor_name'])) {
    $vendor_name = $_GET['vendor_name'];
} else if (isset($_POST['vendor_name'])) {
    $vendor_name = $_POST['vendor_name'];
}
if (isset($_GET['vendor_code'])) {
    $vendor_code = $_GET['vendor_code'];
} else if (isset($_POST['vendor_code'])) {
    $vendor_code = $_POST['vendor_code'];
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
if (isset($_GET['stockid'])) {
    $stockid = $_GET['stockid'];
} else if (isset($_POST['stockid'])) {
    $stockid = $_POST['stockid'];
}
if (isset($_GET['item_name'])) {
    $item_name = $_GET['item_name'];
} else if (isset($_POST['item_name'])) {
    $item_name = $_POST['item_name'];
}

 

if (isset($_GET['po_num'])) {
    $po_num = $_GET['po_num'];
} else if (isset($_POST['po_num'])) {
    $po_num = $_POST['po_num'];
}


if (isset($_GET['receipt_num'])) {
    $receipt_num = $_GET['receipt_num'];
} else if (isset($_POST['receipt_num'])) {
    $receipt_num = $_POST['receipt_num'];
}
if (isset($_GET['subinventory_code'])) {
    $subinventory_code = $_GET['subinventory_code'];
} else if (isset($_POST['subinventory_code'])) {
    $subinventory_code = $_POST['subinventory_code'];
}
$sql = "SELECT  b.stockid, c.item_desc, c.item_name,a.vendor_code, d.vendor_name, prt.transaction_type, prr.subinventory_code,sum(prt.transaction_quantity) transaction_quantity 
FROM po_headers_all a, po_lines_all b, sf_item_no c, vendors d, po_rcv_transactions prt,po_rcv_receipt_line prr
WHERE a.po_num = b.po_num
AND a.vendor_code = d.vendor_code
AND b.po_num = prt.po_num
and prr.receipt_num=prt.receipt_num
and prt.po_num=prr.po_num
and prt.po_line=prr.po_line 
and b.stockid=prr.stockid
and prt.receipt_line=prr.receipt_line
and  prt.transaction_type in ('POIN')
AND b.line = prt.po_line
AND b.stockid = prt.stockid
AND b.stockid = c.item_no ";
if(isset($vendor_name) and $vendor_name != ''){
    $sql = $sql." and d.vendor_name ".LIKE." '%".$vendor_name."%' ";
}
if(isset($vendor_code) and $vendor_code != ''){
    $sql = $sql." and d.vendor_code ".LIKE." '%".$vendor_code."%' ";
}
if(isset($FromDate) and $FromDate != ''){
    $sql = $sql." and prt.transaction_date >=".strtotime($FromDate)." ";
}
if(isset($ToDate) and $ToDate != ''){
    $sql = $sql." and prt.transaction_date <=".strtotime($ToDate)." ";
}
if (isset($stockid) and $stockid != '') { 
	$sql = $sql." and prt.stockid ".LIKE." '%".$stockid."%' ";
}
if (isset($item_name) and $item_name != '') { 
	$sql = $sql." and c.item_name ".LIKE." '%".$item_name."%' ";
}
 
if (isset($po_num) and $po_num != '') { 
	$sql = $sql." and a.po_num ".LIKE." '%".$po_num."%' ";
}
 if (isset($receipt_num) and $receipt_num != '') {
    $sql = $sql . " and prr.subinventory_code ".LIKE." '%".$receipt_num."%' ";
}

if (isset($subinventory_code) and $subinventory_code != '') {
    $sql = $sql . " and prt.subinventory_code ".LIKE." '%".$subinventory_code."%' ";
}

 

$sql .=" group by b.stockid, c.item_desc,c.item_name, a.vendor_code, d.vendor_name, prt.transaction_type, prr.subinventory_code ";
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
  array('供应商编码','供应商名称','料号','料号名称','规格型号','仓库','入库数量'),

); 
 
 
$writer = new XLSXWriter();
$writer->setAuthor('Shunfansoft'); 

//$writer->writeSheetHeader('Sheet1', $header);
 foreach($rows2 as $row2)
	$writer->writeSheetRow('Sheet1', $row2);
foreach($rows as $row)
	$writer->writeSheetRow('Sheet1', $row);

	while ($v = DB_fetch_array($result_num)) {
  
	 $writer->writeSheetRow('Sheet1', array($v['vendor_code'],$v['vendor_name'],$v['stockid'],$v['item_name'],$v['item_desc'],$v['subinventory_code'],$v['transaction_quantity'] ));
	 }
    
	

$writer->writeToStdOut();
//$writer->writeToFile('example.xlsx');
//echo $writer->writeToString();
exit(0);
?>
