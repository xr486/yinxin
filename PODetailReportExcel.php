<?php
//  首先引入XLSXWriter包

putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");
ob_start();
include('includes/session2.inc');
include('includes/SQL_CommonFunctions.inc');
unset($result);
 

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


if (isset($_GET['status'])) {
    $status = $_GET['status'];
} else if (isset($_POST['status'])) {
    $status = $_POST['status'];
}

$sql ="SELECT  pha.po_num, pha.status, pha.note, pha.creation_date,  pla.line_amount,pla.quantity,pla.line,pla.price,v.vendor_code, v.vendor_name, pha.need_date, pha.created_by,b.item_no,b.item_desc,b.item_name,b.units,
ifnull(pla.quantity_received,0) this_received,quantity_accepted,quantity_deliveried,(select realname from www_users where userid=pha.created_by) realname
FROM po_headers_all pha, po_lines_all pla, vendors v,sf_item_no b
WHERE pla.po_num = pha.po_num
AND  b.item_no=pla.stockid
AND v.vendor_code = pha.vendor_code  ";
if(isset($vendor_name) and $vendor_name != ''){
    $sql = $sql." and v.vendor_name ".LIKE." '%".$vendor_name."%' ";
}
if(isset($vendor_code) and $vendor_code != ''){
    $sql = $sql." and v.vendor_code ".LIKE." '%".$vendor_code."%' ";
}
if(isset($FromDate) and $FromDate != ''){
    $sql = $sql." and pha.creation_date >=".strtotime($FromDate)." ";
}
if(isset($ToDate) and $ToDate != ''){
    $sql = $sql." and pha.creation_date <=".strtotime($ToDate)." ";
}
if (isset($stockid) and $stockid != '') { 
	$sql = $sql." and pla.stockid ".LIKE." '%".$stockid."%' ";
}
if (isset($item_name) and $item_name != '') { 
	$sql = $sql." and b.item_name ".LIKE." '%".$item_name."%' ";
}
 
if (isset($po_num) and $po_num != '') { 
	$sql = $sql." and pha.po_num ".LIKE." '%".$po_num."%' ";
}
if (isset($status) and $status != '') { 
	$sql = $sql." and pha.status ".LIKE." '%".$status."%' ";
}

 

$sql .=" order by pha.creation_date,pla.line   ";
 $result_num = DB_query($sql, $db); 
//oci_execute($par);
include_once("xlsxwriter.class.php");
$date=date('YmdHis');
//ini_set('display_errors', 0);
//ini_set('log_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);

$filename = "采购单明细报表".$date.".xlsx";
header('Content-disposition: attachment; filename="'.XLSXWriter::sanitize_filename($filename).'"');
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate');
header('Pragma: public');
$rows2 = array( 
  array('采购单明细报表'),

);

$rows = array( 
  array('供应商代码','采购单号','签核状态','备注','需求日期','建立日期','建立人员','行',
  '料号','料号名称','规格型号','单位','单价','采购金额','采购数量','收货数量',),

); 


$writer = new XLSXWriter();
$writer->setAuthor('Shunfansoft'); 

//$writer->writeSheetHeader('Sheet1', $header);
 foreach($rows2 as $row2)
	$writer->writeSheetRow('Sheet1', $row2);
foreach($rows as $row)
	$writer->writeSheetRow('Sheet1', $row);

	while ($v = DB_fetch_array($result_num)) {

		 if ($v['status'] == 'INPROCESS') {
                $v_status = '待签核';
            } elseif ($v['status'] == 'APPROVED') {
                $v_status = '已签核';
            } elseif ($v['status'] == 'REJECTED') {
                $v_status = '已拒签';
            } else {
                $v_status = $v['status'];
            }

     $writer->writeSheetRow('Sheet1', array($v['vendor_code'],$v['po_num'],$v_status,$v['note'],
     date('Y-m-d', $v['need_date']),date('Y-m-d', $v['creation_date']),$v['realname'],$v['line'],
     $v['item_no'].' ',$v['item_name'],$v['item_desc'],$v['units'],' '.sprintf("%.5f",$v['price']),
     ' '.sprintf("%.5f",$v['line_amount']),$v['quantity'],$v['this_received'] ));
} 
$writer->writeToStdOut();
//$writer->writeToFile('example.xlsx');
//echo $writer->writeToString();
exit(0);
?>
