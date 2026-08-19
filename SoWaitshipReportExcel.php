<?php
//  首先引入XLSXWriter包

putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");
ob_start();
include('includes/session2.inc');
include('includes/SQL_CommonFunctions.inc');
unset($result);
 
$sql = "SELECT
h.order_number,
s.quantity,s.uom,s.quantity_shiped,s.line,s.stockid,
h.customer_code,c.customer_name,h.creation_date,h.status,h.need_date,
d.item_name,d.item_desc
FROM
so_lines_all s,
so_headers_all h,
customers c,sf_item_no d
WHERE  s.order_number = h.order_number 
AND c.customer_code = h.customer_code 
and h.status = '已签核'
and s.quantity>s.quantity_shiped
and s.stockid=d.item_no";

if (isset($_GET['order_number']) and $_GET['order_number'] != '') { 
    $sql = $sql . " and h.order_number " . LIKE . " '%" . $_GET['order_number'] . "%' ";
}
if (isset($_GET['item_no']) and $_GET['item_no'] != '') { 
    $sql = $sql . " and d.item_no " . LIKE . " '%" . $_GET['item_no'] . "%' ";
}
if (isset($_GET['item_name']) and $_GET['item_name'] != '') { 
    $sql = $sql . " and d.item_name " . LIKE . " '%" . $_GET['item_name'] . "%' ";
}
if (isset($_GET['item_desc']) and $_GET['item_desc'] != '') { 
    $sql = $sql . " and d.item_desc " . LIKE . " '%" . $_GET['item_desc'] . "%' ";
}
if (isset($_GET['customer_name']) and $_GET['customer_name'] != '') {
    $sql = $sql . " and c.customer_name " . LIKE . " '%" . $_GET['customer_name'] . "%' ";
}

if (isset($_GET['customer_code']) and $_GET['customer_code'] != '') {
    $sql = $sql . " and c.customer_code " . LIKE . " '%" . $_GET['customer_code'] . "%' ";
}

if (empty($_GET['FromDate']) == 0) {
    $SQL_FromDate = strtotime($_GET['FromDate']);
    $sql .= " and h.creation_date >= '" . $SQL_FromDate . "' ";
}
if (empty($_GET['ToDate']) == 0) {
    $SQL_ToDate = strtotime($_POST['ToDate']) + 86400;
    //echo $SQL_ToDate;
    $sql .= " and h.creation_date <='" . $SQL_ToDate . "' ";
}

$sql .=" order by h.creation_date desc  ";
    $result_num = DB_query($sql,$db);
//oci_execute($par);
include_once("xlsxwriter.class.php");
$date=date('YmdHis');
//ini_set('display_errors', 0);
//ini_set('log_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);

$filename = "待出货明细".$date.".xlsx";
header('Content-disposition: attachment; filename="'.XLSXWriter::sanitize_filename($filename).'"');
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate');
header('Pragma: public');
$rows2 = array( 
  array('待出货明细'),
);

$rows = array( 
  array('订单号','状态','行','客户代码','成品料号','产品名称','规格型号','单位','数量','已出货量','待出货量'
   ,'需求日期','建单日期'),
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

     $writer->writeSheetRow('Sheet1', array($v['order_number'],$v['status'],$v['line'],$v['customer_code'],$v['stockid'].' ',
        $v['item_name'],$v['item_desc'],$v['uom'],$v['quantity'],$v['quantity_shiped'],$v_quantity,date('Y-m-d', $v['need_date']),date('Y-m-d', $v['creation_date'])
        ));
	 }
    
$writer->writeToStdOut();
//$writer->writeToFile('example.xlsx');
//echo $writer->writeToString();
exit(0);
?>
