<?php
//  首先引入XLSXWriter包

putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");
ob_start();
include('includes/session2.inc');
include('includes/SQL_CommonFunctions.inc');
unset($result);
 

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
if (isset($_GET['vendor_code'])) {
    $vendor_code = $_GET['vendor_code'];
} else if (isset($_POST['vendor_code'])) {
    $vendor_code = $_POST['vendor_code'];
}
if (isset($_GET['vendor_name'])) {
    $vendor_name = $_GET['vendor_name'];
} else if (isset($_POST['vendor_name'])) {
    $vendor_name = $_POST['vendor_name'];
}

$sql ="SELECT   ch.customer_code,ve.customer_name,sum(cl.check_amount) check_amount,sum(cl.invoice_amount) invoice_amount,sum(cl.dis_invoice_amount) dis_invoice_amount,sum(cl.check_amount-cl.invoice_amount-cl.dis_invoice_amount) wait_amount
                    FROM  so_headers_all ch,
                           so_lines_all cl,sf_item_no d,
						   customers ve
                   where ch.order_number=cl.order_number
				   and ch.customer_code=ve.customer_code
				   and cl.stockid=d.item_no
                   and (cl.check_amount-cl.invoice_amount - cl.dis_invoice_amount >0  )  
              and 1=1 " ;
    
    if(isset($FromDate) and $FromDate != ''){
        $sql = $sql." and ch.need_date >=".strtotime($FromDate)." ";
    }
    if(isset($ToDate) and $ToDate != ''){
        $sql = $sql." and ch.need_date <=".strtotime($ToDate)." ";
    }
    if(isset($vendor_code) and $vendor_code != ''){
        $sql = $sql." and ve.vendor_code ".LIKE." '%".$vendor_code."%' ";
    }
    if(isset($vendor_name) and $vendor_name != ''){
        $sql = $sql." and ve.vendor_name ".LIKE." '%".$vendor_name."%' ";
    }
  
    $sql = $sql." group by ch.customer_code,ve.customer_name ";
    $result_num = DB_query($sql,$db);
//oci_execute($par);
include_once("xlsxwriter.class.php");
$date=date('YmdHis');
//ini_set('display_errors', 0);
//ini_set('log_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);

$filename = "已对账未开票汇总".$date.".xlsx";
header('Content-disposition: attachment; filename="'.XLSXWriter::sanitize_filename($filename).'"');
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate');
header('Pragma: public');
$rows2 = array( 
  array('已对账未开票汇总'),
);

$rows = array( 
  array('客户代号','客户名称','对账金额','已开票金额','免开票金额','未开票金额'),
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

     $writer->writeSheetRow('Sheet1', array($v['customer_code'],$v['customer_name'],$v['check_amount'],
       $v['invoice_amount'],$v['dis_invoice_amount'],$v['wait_amount']));
	 }
    
$writer->writeToStdOut();
//$writer->writeToFile('example.xlsx');
//echo $writer->writeToString();
exit(0);
?>
