<?php
//  首先引入XLSXWriter包
putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");
ob_start();
include('includes/session2.inc');
include('includes/SQL_CommonFunctions.inc');
unset($result);
 
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

 

if (isset($_GET['wip_entity_name'])) {
    $wip_entity_name = $_GET['wip_entity_name'];
} else if (isset($_POST['wip_entity_name'])) {
    $wip_entity_name = $_POST['wip_entity_name'];
}


if (isset($_GET['order_number'])) {
    $order_number = $_GET['order_number'];
} else if (isset($_POST['order_number'])) {
    $order_number = $_POST['order_number'];
}
 

$sql = "SELECT  a.version,a.status_type,a.so_header_number,a.so_line_number,
	a.wip_entity_name,e.item_no,e.item_name,e.item_desc,a.plan_start_date,a.creation_date,
	e.units,a.start_quantity,a.quantity_completed,a.quantity_completed,a.new_plan_status
	 from wip_jobs_all a,sf_item_no e
	where  a.primary_item=e.item_no 
	and a.status_type<>'关闭'   ";
     
if(isset($FromDate) and $FromDate != ''){
    $sql = $sql." and a.plan_start_date >=".strtotime($FromDate)." ";
}
if(isset($ToDate) and $ToDate != ''){
    $sql = $sql." and  a.plan_start_date <=".strtotime($ToDate)." ";
}
if (isset($item_no) and $item_no != '') { 
	$sql = $sql." and e.item_no ".LIKE." '%".$item_no."%' ";
}
if (isset($item_name) and $item_name != '') { 
	$sql = $sql." and e.item_name ".LIKE." '%".$item_name."%' ";
}
 
if (isset($wip_entity_name) and $wip_entity_name != '') { 
	$sql = $sql." and a.wip_entity_name ".LIKE." '%".$wip_entity_name."%' ";
}
if (isset($order_number) and $order_number != '') { 
	$sql = $sql." and a.so_header_number ".LIKE." '%".$order_number."%' ";
}
 
    $sql .=" order by a.creation_date  ";
   
	$result_num = DB_query($sql,$db);
//oci_execute($par);
include_once("xlsxwriter.class.php");
$date=date('YmdHis');
//ini_set('display_errors', 0);
//ini_set('log_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);

$filename = "生产排程报表".$date.".xlsx";
header('Content-disposition: attachment; filename="'.XLSXWriter::sanitize_filename($filename).'"');
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate');
header('Pragma: public');
$rows2 = array( 
  array('生产排程报表'),
);

$rows = array( 
  array('工单名','订单','行','料号','料号名称','规格型号','版本','单位','开工数量','完工数量','开工日期','建单日期'),
);
 

$writer = new XLSXWriter();
$writer->setAuthor('Shunfansoft'); 

//$writer->writeSheetHeader('Sheet1', $header);
 foreach($rows2 as $row2)
	$writer->writeSheetRow('Sheet1', $row2);
foreach($rows as $row)
	$writer->writeSheetRow('Sheet1', $row);

	while ($v = DB_fetch_array($result_num)) {
      $writer->writeSheetRow('Sheet1', array($v['wip_entity_name'],$v['so_header_number'],$v['so_line_number'],$v['item_no'],$v['item_name'],
        $v['item_desc'],$v['version'],$v['units'],$v['start_quantity'],$v['quantity_completed'],
        date('Y-m-d', $v['plan_start_date']),date('Y-m-d', $v['creation_date']),
        ));
	 }
      
	  

$writer->writeToStdOut();
//$writer->writeToFile('example.xlsx');
//echo $writer->writeToString();
exit(0);
?>
