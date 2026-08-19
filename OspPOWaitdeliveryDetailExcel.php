<?php
//  首先引入XLSXWriter包

putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");
ob_start();
include('includes/session2.inc');
include('includes/SQL_CommonFunctions.inc');
unset($result);

$sql = 'SELECT pha.po_num, pha.status, pha.note, pha.creation_date,   pla.line_amount,pla.quantity,pla.line,pla.price,v.vendor_code, v.vendor_name, pla.need_date, pha.created_by,c.stockid,c.item_name,c.uom,
ifnull(pla.quantity_received,0) this_received,pla.operation_code,pla.operation_seq_num,pla.wip_entity_name
FROM waixie_headers_all pha,  waixie_lines_all pla, vendors v,wip_jobs_all b,so_lines_all c
WHERE pla.wip_entity_name=b.wip_entity_name and  b.so_header_number=c.order_number and  	b.so_line_number=c.line and  pla.po_num = pha.po_num 
and pla.quantity-pla.quantity_received>0
AND v.vendor_code = pha.vendor_code ';

if (isset($_GET['wip_entity_name']) and $_GET['wip_entity_name'] != '') {
    $sql = $sql . " and pla.wip_entity_name " . LIKE . " '%" . $_GET['wip_entity_name'] . "%' ";
}
if (isset($_GET['operation_seq_num']) and $_GET['operation_seq_num'] != '') {
    $sql = $sql . " and pla.operation_seq_num " . LIKE . " '%" . $_GET['operation_seq_num'] . "%' ";
}
if (isset($_GET['operation_code']) and $_GET['operation_code'] != '') {
    $sql = $sql . " and pla.operation_code " . LIKE . " '%" . $_GET['operation_code'] . "%' ";
}
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
    $sql = $sql . " and c.item_name " . LIKE . " '%" . $_GET['item_name'] . "%' ";
}
if (empty($_GET['FromDate']) == 0) {
    $SQL_FromDate = strtotime($_GET['FromDate']);
    $sql .= " and pha.need_date >= '" . $SQL_FromDate . "' ";
}
if (empty($_GET['ToDate']) == 0) {
    $SQL_ToDate = strtotime($_GET['ToDate']) + 86400;
    //echo $SQL_ToDate;
    $sql .= " and pha.need_date <='" . $SQL_ToDate . "' ";
}
$sql .= " and pha.status = 'APPROVED'";
 
$sql .= " order by pha.creation_date desc ";

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
  array('供应商代码','采购单号','签核状态','备注','需求日期','行','生产单号', '工序号', '工序名称','零件图号','零件名称','单位','采购数量','收货量','待收货量','建单日期','建单人员'),

); 

 
$writer = new XLSXWriter();
$writer->setAuthor('Shunfansoft'); 

//$writer->writeSheetHeader('Sheet1', $header);
 foreach($rows2 as $row2)
	$writer->writeSheetRow('Sheet1', $row2);
foreach($rows as $row)
	$writer->writeSheetRow('Sheet1', $row);

	while ($v = DB_fetch_array($result_num)) {
        unset($v_status);
        if ($v['status'] == 'INPROCESS') {
            $v_status = '待签核';
        } elseif ($v['status'] == 'APPROVED') {
            $v_status = '已签核';
        } elseif ($v['status'] == 'REJECTED') {
            $v_status = '已拒签';
        } else {
            $v_status = '已取消';
        }


       $wait_receievd=$v['quantity']-$v['this_received'];
     $writer->writeSheetRow('Sheet1', array($v['vendor_code'],$v['po_num'],$v_status,$v['note'],date('Y-m-d', $v['need_date']),$v['line'], 
     $v['wip_entity_name'],$v['operation_seq_num'],$v['operation_code'],$v['stockid'],$v['item_name'],$v['uom'],$v['quantity'],$v['this_received'],$wait_receievd,
     date('Y-m-d H:i:s', $v['creation_date']),$v['created_by'],
    
    ));
	 }
    
$writer->writeToStdOut();
//$writer->writeToFile('example.xlsx');
//echo $writer->writeToString();
exit(0);
?>
