<?php
//  首先引入XLSXWriter包

putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");
ob_start();
include('includes/session2.inc');
include('includes/SQL_CommonFunctions.inc');
unset($result);
 

if (isset($_GET['bankchangenum'])) {
    $bankchangenum = $_GET['bankchangenum'];
} else if (isset($_POST['bankchangenum'])) {
    $bankchangenum = $_POST['bankchangenum'];
}
if (isset($_GET['transaction_num'])) {
    $transaction_num = $_GET['transaction_num'];
} else if (isset($_POST['transaction_num'])) {
    $transaction_num = $_POST['transaction_num'];
}
if (isset($_GET['bankaccountname'])) {
    $bankaccountname = $_GET['bankaccountname'];
} else if (isset($_POST['bankaccountname'])) {
    $bankaccountname = $_POST['bankaccountname'];
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



$sql = "select pha.transaction_type,
			      pha.bankaccountname,
							    pha.bankchangenum,
							      pha.transaction_num,
							   pha.transaction_amount,dis_amount,
								   pha.tax_amount,
                                  pha.vendor_code,
                                    pha.narrative,
								pha.currency_code,
                                 pha.transaction_date,
                pha.creation_date,pha.created_by,pha.vendor_code,c.vendor_name,pha.status
	from fin_bank_transaction_headers_all pha, vendors c
            where pha.status='核准'
			and pha.transaction_type in ('AP付款','AP退款')
			and pha.vendor_code=c.vendor_code ";
if(isset($bankchangenum) and $bankchangenum != ''){
    $sql = $sql." and pha.bankchangenum ".LIKE." '%".$bankchangenum."%' ";
}
if(isset($transaction_num) and $transaction_num != ''){
    $sql = $sql." and pha.transaction_num ".LIKE." '%".$transaction_num."%' ";
}
if(isset($bankaccountname) and $bankaccountname != ''){
    $sql = $sql." and pha.bankaccountname ".LIKE." '%".$bankaccountname."%' ";
}
if(isset($vendor_code) and $vendor_code != ''){
    $sql = $sql." and c.vendor_code ".LIKE." '%".$vendor_code."%' ";
}
if(isset($vendor_name) and $vendor_name != ''){
    $sql = $sql." and c.vendor_name ".LIKE." '%".$vendor_name."%' ";
}
if(isset($FromDate) and $FromDate != ''){
    $sql = $sql." and pha.transaction_date >=".strtotime($FromDate)." ";
}
if(isset($ToDate) and $ToDate != ''){
    $sql = $sql." and pha.transaction_date <=".strtotime($ToDate)." ";
}

 

$sql .=" order by  pha.transaction_date desc ";
 $result_num = DB_query($sql, $db); 
//oci_execute($par);
include_once("xlsxwriter.class.php");
$date=date('YmdHis');
//ini_set('display_errors', 0);
//ini_set('log_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);

$filename = "供应商付款明细查询报表".$date.".xlsx";
header('Content-disposition: attachment; filename="'.XLSXWriter::sanitize_filename($filename).'"');
header("Content-wip_entity_name: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate');
header('Pragma: public');
$rows2 = array( 
  array('供应商付款明细查询报表'),

);
$rows = array( 
  array('流水号','付款/转账单号','交易类型','供应商','供应商','付款金额','免付款金额','备注','付款日','建立时间','建立人员'),

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

            
	 $writer->writeSheetRow('Sheet1', array($v['transaction_num'],$v['bankchangenum'],$v['transaction_type'],$v['vendor_code'],$v['vendor_name'],$v['transaction_amount'],$v['dis_amount'],$v['narrative'],date('Y-m-d', $v['transaction_date']),date('Y-m-d h:i:s', $v['creation_date']),$v['created_by'] ));
	 }
    
$writer->writeToStdOut();
//$writer->writeToFile('example.xlsx');
//echo $writer->writeToString();
exit(0);
?>
