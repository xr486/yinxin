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

$sql ="SELECT ch.approve_date,ch.order_number,ch.customer_code,ve.customer_name,cl.line,cl.stockid,d.item_desc,d.item_name,cl.price,
cl.quantity_shiped,cl.quantity,cl.check_amount,cl.invoice_amount,cl.dis_invoice_amount
                FROM  so_headers_all ch,
                       so_lines_all cl,sf_item_no d,
                       customers ve
               where ch.order_number=cl.order_number
               and ch.customer_code=ve.customer_code
               and cl.stockid=d.item_no
               and (cl.check_amount-cl.invoice_amount - cl.dis_invoice_amount >0  )  
          and 1=1 " ;

    if(isset($customer_name) and $customer_name != ''){
        $sql = $sql." and ve.customer_name ".LIKE." '%".$customer_name."%' ";
    }
    if(isset($customer_code) and $customer_code != ''){
        $sql = $sql." and ve.customer_code ".LIKE." '%".$customer_code."%' ";
    }
	if(isset($FromDate) and $FromDate != ''){
        $sql = $sql." and ch.need_date >=".strtotime($FromDate)." ";
    }
    if(isset($ToDate) and $ToDate != ''){
        $sql = $sql." and ch.need_date <=".strtotime($ToDate)." ";
    }
  
    $sql = $sql." order by ch.order_number ";
    $result_num = DB_query($sql,$db);
//oci_execute($par);
include_once("xlsxwriter.class.php");
$date=date('YmdHis');
//ini_set('display_errors', 0);
//ini_set('log_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);

$filename = "已对账未开票明细".$date.".xlsx";
header('Content-disposition: attachment; filename="'.XLSXWriter::sanitize_filename($filename).'"');
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate');
header('Pragma: public');
$rows2 = array( 
  array('已对账未开票明细'),
);

$rows = array( 
  array('客户代号','业务订单','需求日期','业务订单行','料号','料号名称','规格型号','订单数量','单价','对账金额','已开票金额',
       '免开票金额','未开票金额'),
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
    $v_waitquantity = $v['check_amount']-$v['invoice_amount']-$v['dis_invoice_amount'];

     $writer->writeSheetRow('Sheet1', array($v['customer_code'],$v['order_number'],date('Y-m-d', $v['need_date']),
     $v['line'],$v['stockid'],$v['item_name'],$v['item_desc'],$v['quantity'],$v['price'],
     $v['check_amount'],$v['invoice_amount'],$v['dis_invoice_amount'],$v));
	 }
    
$writer->writeToStdOut();
//$writer->writeToFile('example.xlsx');
//echo $writer->writeToString();
exit(0);
?>
