<?php
//  首先引入XLSXWriter包

putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");
ob_start();
include('includes/session2.inc');
include('includes/SQL_CommonFunctions.inc');
unset($result);
 
$sql = "SELECT prt.receipt_num,prr.receipt_line,a.po_num,b.line,b.stockid,g.item_name, a.vendor_code, d.vendor_name, prr.transaction_quantity, prt.receipt_type, prr.subinventory_code, b.need_date, prt.delivery_date,prt.created_by,e.realname,b.operation_code,b.operation_seq_num,b.wip_entity_name
FROM waixie_headers_all a, waixie_lines_all b, so_lines_all c, vendors d, waixie_rcv_receipt_header prt,waixie_rcv_receipt_line prr,www_users e,sf_item_no g
WHERE  a.po_num = b.po_num
AND a.vendor_code = d.vendor_code and c.stockid=g.item_no
AND b.po_num = prr.po_num
AND b.wip_entity_name = c.wip_entity_name
And prr.receipt_num=prt.receipt_num and prt.created_by=e.userid
AND b.line = prr.po_line 
And prr.stockid=b.stockid
AND b.line = prr.po_line  ";

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
if (isset($_GET['item_no']) and $_GET['item_no'] != '') { 
    $sql = $sql." and prr.stockid ".LIKE." '%".$_GET['item_no']."%' ";
}

if (isset($_GET['wip_entity_name']) and $_GET['wip_entity_name'] != '') {
    $sql = $sql . " and b.wip_entity_name " . LIKE . " '%" . $_GET['wip_entity_name'] . "%' ";
}
if (isset($_GET['operation_seq_num']) and $_GET['operation_seq_num'] != '') {
    $sql = $sql . " and b.operation_seq_num " . LIKE . " '%" . $_GET['operation_seq_num'] . "%' ";
}
if (isset($_GET['operation_code']) and $_GET['operation_code'] != '') {
    $sql = $sql . " and b.operation_code " . LIKE . " '%" . $_GET['operation_code'] . "%' ";
}
if (isset($_GET['receipt_num_from']) and $_GET['receipt_num_from'] != '') {
    $sql = $sql . " and prt.receipt_num ".LIKE." '%".$_GET['receipt_num_from']."%' ";
}
 

if (isset($_GET['po_num_from']) and $_GET['po_num_from'] != '') {
    $sql = $sql . " and a.po_num  ".LIKE." '%".$_GET['po_num_from']."%' ";
}
if (isset($_GET['sub_code']) and $_GET['sub_code'] != '') { 
    $sql = $sql." and prr.subinventory_code ".LIKE." '%".$_GET['sub_code']."%' ";
}

 

$sql .=" order by prt.delivery_date desc "; 
 $result_num = DB_query($sql, $db); 
//oci_execute($par);
include_once("xlsxwriter.class.php");
$date=date('YmdHis');
//ini_set('display_errors', 0);
//ini_set('log_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);

$filename = "采购入库明细报表".$date.".xlsx";
header('Content-disposition: attachment; filename="'.XLSXWriter::sanitize_filename($filename).'"');
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate');
header('Pragma: public');
$rows2 = array( 
  array('采购入库明细报表'),

);
$rows = array( 
  array('来料报检单号','行','供应商编码','采购单号','行','生产单号','工序号','工序名称','零件图号','零件名称','入库数量','入库日期','入库人员'),

); 
      
 
$writer = new XLSXWriter();
$writer->setAuthor('Shunfansoft'); 

//$writer->writeSheetHeader('Sheet1', $header);
 foreach($rows2 as $row2)
	$writer->writeSheetRow('Sheet1', $row2);
foreach($rows as $row)
	$writer->writeSheetRow('Sheet1', $row);

	while ($v = DB_fetch_array($result_num)) {
  
     $writer->writeSheetRow('Sheet1', array($v['receipt_num'],$v['receipt_line'],$v['vendor_code'],$v['po_num'],$v['line'],$v['wip_entity_name'],
     $v['operation_seq_num'],$v['operation_code'],$v['stockid'],$v['item_name'],$v['transaction_quantity'],date('Y-m-d H:i:s',$v['delivery_date']),$v['created_by'] ));
	 }
      
	

$writer->writeToStdOut();
//$writer->writeToFile('example.xlsx');
//echo $writer->writeToString();
exit(0);
