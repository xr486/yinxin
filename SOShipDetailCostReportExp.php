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
if (isset($_GET['order_number'])) {
    $order_number = $_GET['order_number'];
} else if (isset($_POST['order_number'])) {
    $order_number = $_POST['order_number'];
}
if (isset($_GET['stockid'])) {
    $stockid = $_GET['stockid'];
} else if (isset($_POST['stockid'])) {
    $stockid = $_POST['stockid'];
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

$sql ="SELECT  a.delivery_id,
                 d.delivery_date,
				 a.delivery_num,
                 a.delivery_line,
                 a.so_order_number,
                 a.so_line_no,
                 d.customer_code,
                 c.customer_name,
	             b.stockid,
	             e.item_name,
	             e.item_desc,
			     a.uom ,
				 a.shiped_quantity,
			     a.price,
				 a.check_quantity,
                 a.check_price,
				 a.check_amount,
				 a.check_date,
				 d.delivery_date,(-1*f.quantity) quantity,f.cost_price
		  from so_delivery_all a, 
               customers c,
			   so_delivery_headers_all d,
			   so_lines_all b,
	sf_item_no e,inv_transaction_so f
	     where a.stockid =b.stockid
	       and a.so_order_number = b.order_number
	       and a.so_line_no = b.line and f.delivery_num= a.delivery_num 
		   and f.deliveryline=a.delivery_line 
		   and d.customer_code = c.customer_code
		   and a.delivery_num  = d.delivery_num  
		   AND b.stockid = e.item_no  ";

    if(isset($customer_name) and $customer_name != ''){
        $sql = $sql." and c.customer_name ".LIKE." '%".$customer_name."%' ";
    }
    if(isset($customer_code) and $customer_code != ''){
        $sql = $sql." and c.customer_code ".LIKE." '%".$customer_code."%' ";
    }
    if(isset($order_number) and $order_number != ''){
        $sql = $sql." and a.delivery_num ".LIKE." '%".$order_number."%' ";
    }
    if(isset($stockid) and $stockid != ''){
        $sql = $sql." and b.stockid ".LIKE." '%".$stockid."%' ";
    }
	if(isset($FromDate2) and $FromDate2 != ''){
        $sql = $sql." and d.delivery_date >=".strtotime($FromDate2)." ";
    }
    if(isset($ToDate2) and $ToDate2 != ''){
        $sql = $sql." and d.delivery_date <=".strtotime($ToDate2)." ";
    }
    $sql .= " order by d.delivery_date,a.delivery_num ";

    $result_num = DB_query($sql,$db);
//oci_execute($par);
include_once("xlsxwriter.class.php");
$date=date('YmdHis');
//ini_set('display_errors', 0);
//ini_set('log_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);

$filename = "出货单明细毛利分析报表".$date.".xlsx";
header('Content-disposition: attachment; filename="'.XLSXWriter::sanitize_filename($filename).'"');
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate');
header('Pragma: public');
$rows2 = array( 
  array('出货单明细毛利分析报表'),
);

$rows = array( 
  array('客户','出货单','出货单行','订单号','订单行','料号','料号名称','规格型号','出货日期','出货数量','出货单价','采购单价','毛利'),
); 
 
$writer = new XLSXWriter();
$writer->setAuthor('Shunfansoft'); 

//$writer->writeSheetHeader('Sheet1', $header);
 foreach($rows2 as $row2)
	$writer->writeSheetRow('Sheet1', $row2);
foreach($rows as $row)
	$writer->writeSheetRow('Sheet1', $row);

	while ($v = DB_fetch_array($result_num)) {
         $lirun=0;
		$lirun=$v['quantity']*($v['price']-$v['cost_price']);
     $writer->writeSheetRow('Sheet1', array($v['customer_code'],$v['delivery_num'],$v['delivery_line'],$v['so_order_number'],
        $v['so_line_no'],$v['stockid'],$v['item_name'],$v['item_desc'],
        date('Y-m-d', $v['delivery_date']),$v['quantity'],$v['price'],$v['cost_price'],$lirun));
	 }
    
$writer->writeToStdOut();
//$writer->writeToFile('example.xlsx');
//echo $writer->writeToString();
exit(0);
?>
