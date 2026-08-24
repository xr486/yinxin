<?php
//  首先引入XLSXWriter包

putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");
ob_start();
include('includes/session2.inc');
include('includes/SQL_CommonFunctions.inc');
unset($result);
 

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



$sql ="SELECT cl.vendor_code,ve.vendor_name,sum(cl.check_amount) check_amount,sum(cl.invoice_amount) invoice_amount,sum(cl.dis_invoice_amount) dis_invoice_amount
                    FROM  po_headers_all cl, 
						   vendors ve
                   where  cl.vendor_code=ve.vendor_code 
                   and  check_amount-invoice_amount - dis_invoice_amount >0   
              and 1=1 " ;

if(isset($vendor_code) and $vendor_code != ''){
    $sql = $sql." and ve.vendor_code ".LIKE." '%".$vendor_code."%' ";
}
if(isset($vendor_name) and $vendor_name != ''){
    $sql = $sql." and ve.vendor_name ".LIKE." '%".$vendor_name."%' ";
}
if(isset($FromDate) and $FromDate != ''){
    $sql = $sql." and cl.need_date >=".strtotime($FromDate)." ";
}
if(isset($ToDate) and $ToDate != ''){
    $sql = $sql." and cl.need_date <=".strtotime($ToDate)." ";
}

 

$sql .=" order by cl.vendor_code,ve.vendor_name ";
 $result_num = DB_query($sql, $db); 
//oci_execute($par);
include_once("xlsxwriter.class.php");
$date=date('YmdHis');
//ini_set('display_errors', 0);
//ini_set('log_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);

$filename = "已对账未开票汇总报表".$date.".xlsx";
header('Content-disposition: attachment; filename="'.XLSXWriter::sanitize_filename($filename).'"');
header("Content-wip_entity_name: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate');
header('Pragma: public');
$rows2 = array( 
  array('已对账未开票汇总报表'),

);
$rows = array( 
  array('供应商代号','供应商名称','对账金额','已开票金额','免开票金额','未开票金额'),

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
            $v_waitquantity=$v['check_amount']-$v['invoice_amount']-$v['dis_invoice_amount'] ;
	 $writer->writeSheetRow('Sheet1', array($v['vendor_code'],$v['vendor_name'],$v['check_amount'],$v['invoice_amount'],$v['dis_invoice_amount'],round($v_waitquantity,2) ));
	 }
    
$writer->writeToStdOut();
//$writer->writeToFile('example.xlsx');
//echo $writer->writeToString();
exit(0);
?>
