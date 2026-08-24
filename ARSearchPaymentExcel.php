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

$sql = "select  pha.transaction_type,
			    pha.bankaccountname,
			    pha.bankchangenum,
				pha.transaction_num,
				pha.transaction_amount,dis_amount,
				pha.tax_amount,
                pha.customer_code,
                pha.narrative,
				pha.currency_code,
                pha.transaction_date,
                pha.creation_date,pha.created_by,pha.customer_code,c.customer_name,pha.status
	from fin_bank_transaction_headers_all pha, customers c
            where pha.status='核准'
			and pha.transaction_type in ('AR收款','AR退款')
			and pha.customer_code=c.customer_code ";
    
    if(isset($bankchangenum) and $bankchangenum != ''){
        $sql = $sql." and pha.bankchangenum ".LIKE." '%".$bankchangenum."%' ";
    }
    if(isset($transaction_num) and $transaction_num != ''){
        $sql = $sql." and pha.transaction_num ".LIKE." '%".$transaction_num."%' ";
    }
    if(isset($bankaccountname) and $bankaccountname != ''){
        $sql = $sql." and pha.bankaccountname ".LIKE." '%".$bankaccountname."%' ";
    }
    if(isset($customer_name) and $customer_name != ''){
        $sql = $sql." and c.customer_name ".LIKE." '%".$customer_name."%' ";
    }
    if(isset($customer_code) and $customer_code != ''){
        $sql = $sql." and c.customer_code ".LIKE." '%".$customer_code."%' ";
    }
	if(isset($FromDate) and $FromDate != ''){
        $sql = $sql." and pha.transaction_date >=".strtotime($FromDate)." ";
    }
    if(isset($ToDate) and $ToDate != ''){
        $sql = $sql." and pha.transaction_date <=".strtotime($ToDate)." ";
    }
  
    $result_num = DB_query($sql,$db);
//oci_execute($par);
include_once("xlsxwriter.class.php");
$date=date('YmdHis');
//ini_set('display_errors', 0);
//ini_set('log_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);

$filename = " 客户收款明细".$date.".xlsx";
header('Content-disposition: attachment; filename="'.XLSXWriter::sanitize_filename($filename).'"');
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate');
header('Pragma: public');
$rows2 = array( 
  array(' 客户收款明细'),
);

$rows = array( 
  array('流水号','收款/转账单号','交易类型','客户','客户','收款金额','免收款金额','备注','收款日','建立时间','建立人员'),
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

     $writer->writeSheetRow('Sheet1', array($v['transaction_num'],$v['bankchangenum'],$v['transaction_type'],
       $v['customer_code'],$v['customer_name'],$v['transaction_amount'],$v['dis_amount'],$v['narrative'],
       date('Y-m-d', $v['transaction_date']),date('Y-m-d H:i:s', $v['creation_date']),$v['created_by']));
	 }
    
$writer->writeToStdOut();
//$writer->writeToFile('example.xlsx');
//echo $writer->writeToString();
exit(0);
?>
