<?php
//  首先引入XLSXWriter包

putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");
ob_start();
include('includes/session2.inc');
include('includes/SQL_CommonFunctions.inc');
unset($result);
 

if (isset($_GET['order_number'])) {
    $order_number = $_GET['order_number'];
} else if (isset($_POST['order_number'])) {
    $order_number = $_POST['order_number'];
}
if (isset($_GET['po_num'])) {
    $po_num = $_GET['po_num'];
} else if (isset($_POST['po_num'])) {
    $po_num = $_POST['po_num'];
}
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
if (isset($_GET['item_no'])) {
    $item_no = $_GET['item_no'];
} else if (isset($_POST['item_no'])) {
    $item_no = $_POST['item_no'];
}
if (isset($_GET['item_name'])) {
    $item_name = $_GET['item_name'];
} else if (isset($_POST['item_name'])) {
    $item_name = $_POST['item_name'];
}
if (isset($_GET['item_desc'])) {
    $item_desc = $_GET['item_desc'];
} else if (isset($_POST['item_desc'])) {
    $item_desc = $_POST['item_desc'];
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
if (isset($_GET['FromDate2'])) {
    $FromDate2 = $_GET['FromDate2'];
} else if (isset($_POST['FromDate2'])) {
    $FromDate2 = $_POST['FromDate2'];
}
if (isset($_GET['ToDate2'])) {
    $ToDate2 = $_GET['ToDate2'];
} else if (isset($_POST['ToDate2'])) {
    $ToDate2 = $_POST['ToDate2'];
}
$sql ="SELECT  a.receipt_line_id,
                 a.receipt_num,
	             a.receipt_line,
				 a.uom,
				 a.unit_price,
				 a.transaction_quantity,
				 a.check_quantity,
				 a.check_price,
				 a.check_amount,
				 a.check_remark,a.check_date,
				 a.creation_date,
	             a.stockid,
				 b.delivery_date,
	             b.vendor_code,
                 d.vendor_name,
				 c.item_no,c.item_name,
				 c.item_desc,
				 a.po_num,a.po_line,b.receipt_type
		  from po_rcv_receipt_line a, 
		       po_rcv_receipt_header b,
	           sf_item_no c,
               vendors d
	     where a.receipt_num = b.receipt_num
		   and a.stockid =c.item_no 
		   and b.vendor_code = d.vendor_code
	       and a.check_flag='Y'	  ";
           
    if(isset($order_number) and $order_number != ''){
        $sql = $sql . " and a.receipt_num " .LIKE." '%".$order_number."%' ";
    }
	if(isset($po_num) and $po_num != ''){
        $sql = $sql . " and a.po_num " .LIKE." '%".$po_num."%' ";
    }
    if(isset($vendor_name) and $vendor_name != ''){
        $sql = $sql." and d.vendor_name ".LIKE." '%".$vendor_name."%' ";
    }
    if(isset($vendor_code) and $vendor_code != ''){
        $sql = $sql." and d.vendor_code ".LIKE." '%".$vendor_code."%' ";
    }
    if(isset($item_no) and $item_no != ''){
        $sql = $sql." and c.item_no ".LIKE." '%".$item_no."%' ";
    }
    if(isset($item_name) and $item_name != ''){
        $sql = $sql." and c.item_name ".LIKE." '%".$item_name."%' ";
    }
    if(isset($item_desc) and $item_desc != ''){
        $sql = $sql." and c.item_desc ".LIKE." '%".$item_desc."%' ";
    }
    
	if(isset($FromDate) and $FromDate != ''){
        $sql = $sql." and b.delivery_date >=".strtotime($FromDate)." ";
    }
    if(isset($ToDate) and $ToDate != ''){
        $sql = $sql." and b.delivery_date <=".strtotime($ToDate)." ";
    }
    if(isset($FromDate2) and $FromDate2 != ''){
        $sql = $sql." and a.check_date >=".strtotime($FromDate2)." ";
    }
    if(isset($ToDate2) and $ToDate2 != ''){
        $sql = $sql." and a.check_date <=".strtotime($ToDate2)." ";
    }
  
    $result_num = DB_query($sql,$db);
//oci_execute($par);
include_once("xlsxwriter.class.php");
$date=date('YmdHis');
//ini_set('display_errors', 0);
//ini_set('log_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);

$filename = "采购对账明细".$date.".xlsx";
header('Content-disposition: attachment; filename="'.XLSXWriter::sanitize_filename($filename).'"');
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate');
header('Pragma: public');
$rows2 = array( 
  array('采购对账明细'),
);

$rows = array( 
  array('入库日期','类型','采购入库单','行','供应商','采购单号','行','料号','料号名称','规格型号','单位','入库数量','采购单价','对账数量',
        '对账单价','对账金额','对账日期','对账备注'),
); 
  
$writer = new XLSXWriter();
$writer->setAuthor('Shunfansoft'); 

//$writer->writeSheetHeader('Sheet1', $header);
 foreach($rows2 as $row2)
	$writer->writeSheetRow('Sheet1', $row2);
foreach($rows as $row)
	$writer->writeSheetRow('Sheet1', $row);

	while ($v = DB_fetch_array($result_num)) {

		 
     $writer->writeSheetRow('Sheet1', array(date('Y-m-d', $v['delivery_date']),$v['receipt_type'],$v['receipt_num'],$v['receipt_line'],
     $v['vendor_code'],$v['po_num'],$v['po_line'],$v['stockid'],$v['item_name'],$v['item_desc'],$v['uom'],$v['transaction_quantity'],
     $v['unit_price'],$v['check_quantity'],$v['check_price'],$v['check_amount'],
     date('Y-m-d', $v['check_date']),$v['check_remark'] ));
	 }
        
$writer->writeToStdOut();
//$writer->writeToFile('example.xlsx');
//echo $writer->writeToString();
exit(0);
?>
