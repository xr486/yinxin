<?php
//  首先引入XLSXWriter包

putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");
ob_start();
include('includes/session2.inc');
include('includes/SQL_CommonFunctions.inc');
unset($result);
 
 
$sql ="SELECT  a.receipt_line_id,
                 a.receipt_num,
	             a.receipt_line,
				 a.uom,
				 a.unit_price,
				 a.transaction_quantity,
				 a.check_quantity,
				 a.check_price,
				 a.check_amount,
				 a.remark, 
				 a.creation_date,
	             a.stockid,
				 b.delivery_date,
	             b.vendor_code,
                 d.vendor_name, 
				 a.po_num,a.po_line,b.receipt_type,f.item_name
		  from waixie_rcv_receipt_line a, 
		       waixie_rcv_receipt_header b, 
               vendors d,waixie_lines_all e,so_lines_all f
	     where a.receipt_num = b.receipt_num 
		   and b.vendor_code = d.vendor_code
	       and a.check_flag='Y'	 and a.po_line=e.line and a.po_num=e.po_num and e.wip_entity_name=f.wip_entity_name
		  ";
  if (isset($_GET['po_num']) and $_GET['po_num'] != '') 
  { 
	$sql = $sql . " and a.po_num " . LIKE . " '%" . $_GET['po_num'] . "%' ";
  }

if (isset($_GET['order_number']) and $_GET['order_number'] != '') 
  { 
	$sql = $sql . " and a.receipt_num " . LIKE . " '%" . $_GET['order_number'] . "%' ";
  }

  
  if (isset($_GET['vendor_name']) and $_GET['vendor_name'] != '') 
  { 
	$sql = $sql . " and d.vendor_name " . LIKE . " '%" . $_GET['vendor_name'] . "%' ";
  }

  if (empty($_GET['FromDate']) == 0) 
  {
    $SQL_FromDate = strtotime($_GET['FromDate']);
    $sql .= " and a.creation_date >= '" . $SQL_FromDate . "' ";
  }
   
  if (empty($_GET['ToDate']) == 0) 
  {
     $SQL_ToDate = strtotime($_GET['ToDate']) + 86400;
     $sql .= " and a.creation_date <='" . $SQL_ToDate . "' ";
  }

  if (empty($_GET['FromDate2']) == 0) 
  {
    $SQL_FromDate2 = strtotime($_GET['FromDate2']);
    $sql .= " and b.delivery_date >= '" . $SQL_FromDate2 . "' ";
  }
   
  if (empty($_GET['ToDate2']) == 0) 
  {
     $SQL_ToDate2 = strtotime($_GET['ToDate2']) + 86400;
     $sql .= " and b.delivery_date <='" . $SQL_ToDate2 . "' ";
  }
  $sql .=" order by b.vendor_code,b.delivery_date,a.receipt_num,a.receipt_line"; 
 $result_num = DB_query($sql, $db); 
//oci_execute($par);
include_once("xlsxwriter.class.php");
$date=date('YmdHis');
//ini_set('display_errors', 0);
//ini_set('log_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);

$filename = "外协采购已对账报表".$date.".xlsx";
header('Content-disposition: attachment; filename="'.XLSXWriter::sanitize_filename($filename).'"');
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate');
header('Pragma: public');
$rows2 = array( 
  array('外协采购已对账报表'),

);
$rows = array( 
  array('入库日期', '类型', '采购入库单', '项', '供应商', '外协采购单号' , '行' ,'零件图号','零件名称', '单位' ,'入库数量', '采购单价', '对账数量','对账单价','对账金额','备注',),

); 
 
      
$writer = new XLSXWriter();
$writer->setAuthor('Shunfansoft'); 

//$writer->writeSheetHeader('Sheet1', $header);
 foreach($rows2 as $row2)
	$writer->writeSheetRow('Sheet1', $row2);
foreach($rows as $row)
	$writer->writeSheetRow('Sheet1', $row);

	while ($v = DB_fetch_array($result_num)) {
        $check_amount=$v['transaction_quantity']*$v['unit_price'];
     $writer->writeSheetRow('Sheet1', array(date('Y-m-d',$v['delivery_date']),$v['receipt_type'],$v['receipt_num'],$v['receipt_line'],$v['vendor_code'],$v['po_num'],$v['po_line'],$v['stockid'],$v['item_name'],
     $v['uom'],$v['transaction_quantity'],$v['unit_price'],$v['check_quantity'],$v['check_quantity'],$check_amount,$v['check_remark'], ));
	 }
    
	

$writer->writeToStdOut();
//$writer->writeToFile('example.xlsx');
//echo $writer->writeToString();
exit(0);
