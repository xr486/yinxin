<?php
//  首先引入XLSXWriter包

putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");
ob_start();
include('includes/session2.inc');
include('includes/SQL_CommonFunctions.inc');
unset($result);


$sql = 'SELECT  rl.po_num,a.vendor_code, v.vendor_name,a.receive_remark, b.item_no,b.item_desc,b.item_name,b.units,a.receipt_num,rl.receipt_line,a.creation_date,rl.stockid,rl.lot_num,
ifnull(rl.quantity_received,0) this_received,rl.po_line,rl.unit_price,rl.line_amount
FROM  vendors v,sf_item_no b,po_rcv_receipt_line rl,po_rcv_receipt_header a
WHERE   b.item_no=rl.stockid
AND	a.receipt_num = rl.receipt_num
AND v.vendor_code = a.vendor_code';
 	

	 
     if (isset($_GET['receipt_num']) and $_GET['receipt_num'] != '') { 
		$sql = $sql . " and a.receipt_num " . LIKE . " '%" . $_GET['receipt_num'] ."%' ";
    }
     
    if (isset($_GET['vendor_name']) and $_GET['vendor_name'] != '') {
        $sql = $sql . " and v.vendor_name " . LIKE . " '%" . $_GET['vendor_name'] ."%' ";
    }
    if (isset($_GET['vendor']) and $_GET['vendor'] != '') { 
		$sql = $sql . " and v.vendor_code " . LIKE . " '%" . $_GET['vendor'] ."%' ";
    }
    if (isset($_GET['item_no']) and $_GET['item_no'] != '') { 
		$sql = $sql . " and b.item_no " . LIKE . " '%" . $_GET['item_no'] ."%' ";
    }
	
		 if (isset($_GET['item_name']) and $_GET['item_name'] != '') { 
		$sql = $sql . " and b.item_name " . LIKE . " '%" . $_GET['item_name'] ."%' ";
    }
	 
    if (empty($_GET['FromDate']) == 0) {
        $SQL_FromDate = strtotime($_GET['FromDate']);
        $sql .= " and rl.creation_date >= '" . $SQL_FromDate . "' ";
    }
    if (empty($_GET['ToDate']) == 0) {
        $SQL_ToDate = strtotime($_GET['ToDate']) + 86400;
        //echo $SQL_ToDate;
        $sql .= " and rl.creation_date <='" . $SQL_ToDate . "' ";
    }
	$sql .= " order by  rl.creation_date  desc ";

 $result_num = DB_query($sql, $db); 
//oci_execute($par);
include_once("xlsxwriter.class.php");
$date=date('YmdHis');
//ini_set('display_errors', 0);
//ini_set('log_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);

$filename = "采购收货明细报表".$date.".xlsx";
header('Content-disposition: attachment; filename="'.XLSXWriter::sanitize_filename($filename).'"');
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate');
header('Pragma: public');
$rows2 = array( 
  array('采购收货明细报表'),

);
if ($_SESSION['price_flag']=='N') {
$rows = array( 
  array('供应商编号',  '收货日期' ,'收料单号', '行', '料号', '料号名称', '规格型号', '单位', '收货量', '单价', '金额' ,'批号' ,'备注'),

); 
} else {
$rows = array( 
  array('供应商编号',  '收货日期' ,'收料单号', '行', '料号', '料号名称', '规格型号', '单位', '收货量', '批号' ,'备注'),

); 
} 

 
$writer = new XLSXWriter();
$writer->setAuthor('Shunfansoft'); 

//$writer->writeSheetHeader('Sheet1', $header);
 foreach($rows2 as $row2)
	$writer->writeSheetRow('Sheet1', $row2);
foreach($rows as $row)
	$writer->writeSheetRow('Sheet1', $row);

	while ($v = DB_fetch_array($result_num)) {

		
      if ($_SESSION['price_flag']=='N') {
     $writer->writeSheetRow('Sheet1', array($v['vendor_code'],date('Y-m-d',$v['creation_date']) ,
     $v['receipt_num'],$v['receipt_line'],$v['stockid'],$v['item_name'],$v['item_desc'],$v['units'],
     $v['this_received'],' '.$v['unit_price'],' '.sprintf("%.2f",$v['line_amount']),$v['lot_num'],$v['receive_remark'] ));
	 } else {
		 $writer->writeSheetRow('Sheet1', array($v['vendor_code'],date('Y-m-d',$v['creation_date']) ,
     $v['receipt_num'],$v['receipt_line'],$v['stockid'],$v['item_name'],$v['item_desc'],$v['units'],
     $v['this_received'],$v['lot_num'],$v['receive_remark'] ));
	 }


	 }
    
$writer->writeToStdOut();
//$writer->writeToFile('example.xlsx');
//echo $writer->writeToString();
exit(0);
?>
