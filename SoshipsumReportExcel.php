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
if (isset($_GET['Stockid_from'])) {
    $Stockid_from = $_GET['Stockid_from'];
} else if (isset($_POST['Stockid_from'])) {
    $Stockid_from = $_POST['Stockid_from'];
}
if (isset($_GET['Stockid_to'])) {
    $Stockid_to = $_GET['Stockid_to'];
} else if (isset($_POST['Stockid_to'])) {
    $Stockid_to = $_POST['Stockid_to'];
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

$sql ="SELECT
	c.customer_code,
	c.customer_name,a.delivery_type,
	sum(b.line_amount ) delivery_amount,
	sum(b.delivery_quantity) delivery_quantity,
	b.stockid,
	d.item_name ,
	d.item_desc 
FROM
	so_delivery_headers_all a,
	so_delivery_all b,
	customers c,
	sf_item_no d
WHERE
	a.delivery_num = b.delivery_num
AND c.customer_code = a.customer_code
AND b.stockid = d.item_no 
" ; 
    if(isset($customer_name) and $customer_name != ''){
        $sql = $sql." and c.customer_name ".LIKE." '%".$customer_name."%' ";
    }
    if(isset($customer_code) and $customer_code != ''){
        $sql = $sql." and c.customer_code ".LIKE." '%".$customer_code."%' ";
    }
    if(isset($Stockid_from) and $Stockid_from != ''){
        $sql = $sql." and b.stockid >='" . $Stockid_from . "'";
    }
    if(isset($Stockid_to) and $Stockid_to != ''){
        $sql = $sql." and b.stockid >='" . $Stockid_to . "'";
    }
	if(isset($FromDate) and $FromDate != ''){
        $sql = $sql." and a.creation_date >=".strtotime($FromDate)." ";
    }
    if(isset($ToDate) and $ToDate != ''){
        $sql = $sql." and a.creation_date <=".strtotime($ToDate)." ";
    }
    $sql = $sql." group by c.customer_code,c.customer_name,b.stockid,a.delivery_type,
	d.item_name ,d.item_desc
	order by c.customer_code,c.customer_name,b.stockid";

    $result_num = DB_query($sql,$db);
//oci_execute($par);
include_once("xlsxwriter.class.php");
$date=date('YmdHis');
//ini_set('display_errors', 0);
//ini_set('log_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);

$filename = "客户出货汇总".$date.".xlsx";
header('Content-disposition: attachment; filename="'.XLSXWriter::sanitize_filename($filename).'"');
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate');
header('Pragma: public');
$rows2 = array( 
  array('客户出货汇总'),
);

$rows = array( 
  array('客户代码','客户名称','料号','料号名称','规格型号','类型','出货总数量','出货总价'),
); 
 
$writer = new XLSXWriter();
$writer->setAuthor('Shunfansoft'); 

//$writer->writeSheetHeader('Sheet1', $header);
 foreach($rows2 as $row2)
	$writer->writeSheetRow('Sheet1', $row2);
foreach($rows as $row)
	$writer->writeSheetRow('Sheet1', $row);

	while ($v = DB_fetch_array($result_num)) {

		 /* if ($v['status'] == 'INPROCESS') {
                $v_status = '待签核';
            } elseif ($v['status'] == 'APPROVED') {
                $v_status = '已签核';
            } elseif ($v['status'] == 'REJECTED') {
                $v_status = '已拒签';
            } else {
                $v_status = '已取消';
            } */

     $writer->writeSheetRow('Sheet1', array($v['customer_code'],$v['customer_name'],$v['stockid'],
        $v['item_name'],$v['item_desc'],$v['delivery_type'],$v['delivery_quantity'],$v['delivery_amount']));
	 }
    
$writer->writeToStdOut();
//$writer->writeToFile('example.xlsx');
//echo $writer->writeToString();
exit(0);
?>
