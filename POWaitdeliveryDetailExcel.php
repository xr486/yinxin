<?php
//  首先引入XLSXWriter包

putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");
ob_start();
include('includes/session2.inc');
include('includes/SQL_CommonFunctions.inc');
unset($result);
 
$time =date("Y-m-d");

$sql = 'SELECT  pha.po_num, pha.status, pha.note, pha.creation_date,   pla.line_amount,pla.quantity,pla.line,pla.price,v.vendor_code, v.vendor_name, pha.need_date, pha.created_by,b.item_no,b.item_desc,b.item_name,b.units,
ifnull(pla.quantity_received,0) this_received,(select realname from www_users where userid=pha.created_by) realname
FROM po_headers_all pha, po_lines_all pla, vendors v,sf_item_no b
WHERE pla.po_num = pha.po_num
AND  b.item_no=pla.stockid
and pla.quantity-pla.quantity_received>0
AND v.vendor_code = pha.vendor_code ';

if (isset($_GET['po_num']) and $_GET['po_num'] != '') { 
    $sql = $sql . " and pha.po_num " . LIKE . " '%" . $_GET['po_num'] . "%' ";
}

if (isset($_GET['vendor_name']) and $_GET['vendor_name'] != '') {
    $sql = $sql . " and v.vendor_name " . LIKE . " '%" . $_GET['vendor_name'] .
        "%' ";
}
if (isset($_GET['vendor_code']) and $_GET['vendor_code'] != '') {
    $sql = $sql . " and pha.vendor_code =  '" . $_GET['vendor_code'] . "'";
}
if (isset($_GET['stockid']) and $_GET['stockid'] != '') { 
    $sql = $sql . " and pla.stockid  " . LIKE . " '%" . $_GET['stockid'] . "%' ";
}
if (isset($_GET['item_name']) and $_GET['item_name'] != '') { 
    $sql = $sql . " and b.item_name " . LIKE . " '%" . $_GET['item_name'] . "%' ";
}
if (empty($_GET['FromDate']) == 0) {
    $SQL_FromDate = strtotime($_GET['FromDate']);
    $sql .= " and pha.creation_date >= '" . $SQL_FromDate . "' ";
}
if (empty($_GET['ToDate']) == 0) {
    $SQL_ToDate = strtotime($_GET['ToDate']) + 86400;
    //echo $SQL_ToDate;
    $sql .= " and pha.creation_date <='" . $SQL_ToDate . "' ";
}
  $sql .= " and pha.status = '已签核' ";
  $sql .= " order by pha.creation_date desc";

 $result_num = DB_query($sql, $db); 
//oci_execute($par);
include_once("xlsxwriter.class.php");
$date=date('YmdHis');
//ini_set('display_errors', 0);
//ini_set('log_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);

$filename = "采购未收货明细报表".$date.".xlsx";
header('Content-disposition: attachment; filename="'.XLSXWriter::sanitize_filename($filename).'"');
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate');
header('Pragma: public');
$rows2 = array( 
  array('采购未收货明细报表'),

);
$rows = array( 
  array('供应商代码','采购单号','签核状态','备注','需求日期','建立日期','建立人员','行','料号','料号名称','规格型号','单位','采购数量','收货数量','未收货量'),

); 

 
$writer = new XLSXWriter();
$writer->setAuthor('Shunfansoft'); 

//$writer->writeSheetHeader('Sheet1', $header);
 foreach($rows2 as $row2)
	$writer->writeSheetRow('Sheet1', $row2);
foreach($rows as $row)
	$writer->writeSheetRow('Sheet1', $row);

	while ($v = DB_fetch_array($result_num)) {

	 
       $wait_receievd=$v['quantity']-$v['this_received'];
	 $writer->writeSheetRow('Sheet1', array($v['vendor_code'],$v['po_num'],$v['status'],$v['note'],date('Y-m-d', $v['need_date']),date('Y-m-d', $v['creation_date']),$v['realname'],$v['line'],$v['item_no'].' ',$v['item_name'],$v['item_desc'],$v['units'],$v['quantity'],$v['this_received'],$wait_receievd ));
	 }
    
$writer->writeToStdOut();
//$writer->writeToFile('example.xlsx');
//echo $writer->writeToString();
exit(0);
?>
