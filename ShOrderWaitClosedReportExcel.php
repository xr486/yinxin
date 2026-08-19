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
if (isset($_GET['moju_num'])) {
    $moju_num = $_GET['moju_num'];
} else if (isset($_POST['moju_num'])) {
    $moju_num = $_POST['moju_num'];
}
if (isset($_GET['moju_name'])) {
    $moju_name = $_GET['moju_name'];
} else if (isset($_POST['moju_name'])) {
    $moju_name = $_POST['moju_name'];
}
if (isset($_GET['sh_order_num'])) {
    $sh_order_num = $_GET['sh_order_num'];
} else if (isset($_POST['sh_order_num'])) {
    $sh_order_num = $_POST['sh_order_num'];
}

$sql="SELECT 
    c.customer_code,
    c.customer_name, 
    c.customer_address,
    a.sh_order_num,  
    a.moju_num,
    a.moju_name, 
    a.creation_date,
    a.created_by
 FROM sh_order_headers_all a,sh_order_lines_all b,customers c   
 WHERE a.customer_code = c.customer_code 
 and a.sh_order_num=b.sh_order_num " ;

    if(isset($customer_code) and $customer_code != ''){
        $sql = $sql . " and c.customer_code " .LIKE." '%".$customer_code."%'";
    }
    if(isset($customer_name) and $customer_name != ''){
        $sql = $sql." and c.customer_name ".LIKE." '%".$customer_name."%' ";
    }
    if(isset($moju_num) and $moju_num != ''){
        $sql = $sql." and a.moju_num ".LIKE." '%".$moju_num."%' ";
    }
    if(isset($moju_name) and $moju_name != ''){
        $sql = $sql." and a.moju_name ".LIKE." '%".$moju_name."%' ";
    }
    if(isset($sh_order_num) and $sh_order_num != ''){
        $sql = $sql." and a.sh_order_num ".LIKE." '%".$sh_order_num."%' ";
    }
    
    $sql .=" order by a.sh_order_num";
    $result_num = DB_query($sql,$db);
//oci_execute($par);
include_once("xlsxwriter.class.php");
$date=date('YmdHis');
//ini_set('display_errors', 0);
//ini_set('log_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);

$filename = "未结案服务单报表".$date.".xlsx";
header('Content-disposition: attachment; filename="'.XLSXWriter::sanitize_filename($filename).'"');
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate');
header('Pragma: public');
$rows2 = array( 
  array('售后服务单查询报表'),
);

$rows = array( 
  array('售后单号','模具编号','模具名称','客户简称','客户地址','建单人员','建单日期'),
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
            }
            $v_quantity= $v['quantity']-$v['quantity_shiped']; */

     $writer->writeSheetRow('Sheet1', array($v['sh_order_num'],$v['moju_num'],$v['moju_name'],$v['customer_code'],
        $v['customer_address'],$v['created_by'],date('Y-m-d H:i:s', $v['creation_date'])));
	 }
    
$writer->writeToStdOut();
//$writer->writeToFile('example.xlsx');
//echo $writer->writeToString();
exit(0);
?>
