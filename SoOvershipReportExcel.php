<?php
//  首先引入XLSXWriter包

putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");
ob_start();
include('includes/session2.inc');
include('includes/SQL_CommonFunctions.inc');
unset($result);
 
if (isset($_GET['SO_from'])) {
    $SO_from = $_GET['SO_from'];
} else if (isset($_POST['SO_from'])) {
    $SO_from = $_POST['SO_from'];
}
if (isset($_GET['customer_name'])) {
    $customer_name = $_GET['customer_name'];
} else if (isset($_POST['customer_name'])) {
    $customer_name = $_POST['customer_name'];
}
if (isset($_GET['customer_code'])) {
    $customer_code = $_GET['customer_code'];
} else if (isset($_POST['customer_code'])) {
    $customer_code = $_POST['customer_code'];
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
if (isset($_GET['status'])) {
    $status = $_GET['status'];
} else if (isset($_POST['status'])) {
    $status = $_POST['status'];
}
$time = time();
$sql = " SELECT
h.order_number,
       s.quantity,s.uom,s.quantity_shiped,s.line,s.stockid,
       h.customer_code,h.creation_date,h.status,h.need_date,
       d.item_name,d.item_desc
FROM
so_lines_all s,
so_headers_all h,
customers c,sf_item_no d
WHERE  s.order_number = h.order_number 
AND c.customer_code = h.customer_code 
and s.stockid=d.item_no
and h.status = '已签核'
and quantity > quantity_shiped and h.need_date< '" . $time . "' "; 

    if(isset($SO_from) and $SO_from != ''){
        $sql = $sql . " and h.order_number " .LIKE." '%".$SO_from."%'";
    }
    if(isset($customer_name) and $customer_name != ''){
        $sql = $sql." and c.customer_name ".LIKE." '%".$customer_name."%' ";
    }
    if(isset($customer_code) and $customer_code != ''){
        $sql = $sql." and c.customer_code ".LIKE." '%".$customer_code."%' ";
    }
    if(isset($item_no) and $item_no != ''){
        $sql = $sql." and d.item_no ".LIKE." '%".$item_no."%' ";
    }
    if(isset($item_name) and $item_name != ''){
        $sql = $sql." and d.item_name ".LIKE." '%".$item_name."%' ";
    }
    if(isset($item_desc) and $item_desc != ''){
        $sql = $sql." and d.item_desc ".LIKE." '%".$item_desc."%' ";
    }
	if(isset($FromDate) and $FromDate != ''){
        $sql = $sql." and h.creation_date >=".strtotime($FromDate)." ";
    }
    if(isset($ToDate) and $ToDate != ''){
        $sql = $sql." and h.creation_date <=".strtotime($ToDate)." ";
    }
    $sql .=" order by h.order_number,s.line  ";
    $result_num = DB_query($sql,$db);
//oci_execute($par);
include_once("xlsxwriter.class.php");
$date=date('YmdHis');
//ini_set('display_errors', 0);
//ini_set('log_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);

$filename = "逾期未出货明细".$date.".xlsx";
header('Content-disposition: attachment; filename="'.XLSXWriter::sanitize_filename($filename).'"');
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate');
header('Pragma: public');
$rows2 = array( 
  array('逾期未出货明细'),
);

$rows = array( 
  array('订单号','状态','行','客户代码','成品料号','产品名称','规格型号','单位','数量','已出货量','待出货量','需求日期','建单日期'),
); 
 
$writer = new XLSXWriter();
$writer->setAuthor('Shunfansoft'); 

//$writer->writeSheetHeader('Sheet1', $header);
 foreach($rows2 as $row2)
	$writer->writeSheetRow('Sheet1', $row2);
foreach($rows as $row)
	$writer->writeSheetRow('Sheet1', $row);

	while ($v = DB_fetch_array($result_num)) {

		 
            $v_quantity= $v['quantity']-$v['quantity_shiped'];

     $writer->writeSheetRow('Sheet1', array($v['order_number'],$v['status'],$v['line'],$v['customer_code'],$v['stockid'],
        $v['item_name'],$v['item_desc'],$v['uom'],$v['quantity'],$v['quantity_shiped'],$v_quantity,date('Y-m-d', $v['need_date']),date('Y-m-d', $v['creation_date'])
        ));
	 }
    
$writer->writeToStdOut();
//$writer->writeToFile('example.xlsx');
//echo $writer->writeToString();
exit(0);
?>
