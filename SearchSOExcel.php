<?php
//  首先引入XLSXWriter包
putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");
ob_start();
include('includes/session2.inc');
include('includes/SQL_CommonFunctions.inc');
unset($result);


$sql = "SELECT
    a.order_number,
    a.status,
    b.customer_name,b.customer_code,
    a.all_line_amount,a.tax_amount,
    a.header_remark, 
    a.creation_date,approve_date,a.coycode,a.tax_name,a.order_all_amount,(select sum(d.quantity_shiped * d.price) from so_lines_all d where d.order_number=a.order_number) ship_amount ,e.employee_name,customer_order_number,a.contract_number
FROM so_headers_all a, customers b,hr_employees e
WHERE  a.customer_code = b.customer_code and e.employee_num=a.yewu
"; 


if (isset($_GET['SO_from']) and $_GET['SO_from'] != '') { 
  $sql = $sql . " and a.order_number " . LIKE . " '%" . $_GET['SO_from'] . "%' ";
}

if (isset($_GET['customer_order_number']) and $_GET['customer_order_number'] != '') {
  $sql = $sql . " and a.customer_order_number " . LIKE . " '%" .  $_GET['customer_order_number'] . "%' ";
}
if (isset($_GET['customer_name']) and $_GET['customer_name'] != '') {
  $sql = $sql . " and b.customer_name " . LIKE . " '%" . $_GET['customer_name'] . "%' ";
}
if (isset($_GET['customer_code']) and $_GET['customer_code'] != '') {
  $sql = $sql . " and b.customer_code " . LIKE . " '%" . $_GET['customer_code'] . "%' ";
}

if (isset($_GET['employee_name']) and $_GET['employee_name'] != '') {
  $sql = $sql . " and e.employee_name " . LIKE . " '%" . $_GET['employee_name'] . "%' ";
}
if (isset($_GET['employee_num']) and $_GET['employee_num'] != '') {
  $sql = $sql . " and e.employee_num " . LIKE . " '%" . $_GET['employee_num'] . "%' ";
}
if (isset($_GET['dengji']) and $_GET['dengji'] != '') {
  $sql = $sql . " and b.dengji " . LIKE . " '%" . $_GET['dengji'] . "%' ";
}


if (empty($_GET['FromDate']) == 0) {
  $SQL_FromDate = strtotime($_GET['FromDate']);
  $sql .= " and a.creation_date >= '" . $SQL_FromDate . "' ";
}
if (empty($_GET['ToDate']) == 0) {
  $SQL_ToDate = strtotime($_GET['ToDate']) + 86400;
  //echo $SQL_ToDate;
  $sql .= " and a.creation_date <='" . $SQL_ToDate . "' ";
}
if($_GET['checkresult']!=""){
  if($_GET['checkresult']=="已签核"){
      $sql .= " and a.status = '已签核'";
  }
  if($_GET['checkresult']=="待签核"){
       $sql .=" and a.status = '待签核'";
  }
  if($_GET['checkresult']=="已取消"){
       $sql .= " and a.status = '已取消'";
  }
  if($_GET['checkresult']=="已拒绝"){
       $sql .=" and a.status = '已拒绝'";
  }
}


 

$sql .=" order by a.creation_date DESC";

$result_num = DB_query($sql, $db);
//oci_execute($par);
include_once("xlsxwriter.class.php");
$date = date('YmdHis');
//ini_set('display_errors', 0);
//ini_set('log_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);

$filename = "业务订单明细" . $date . ".xlsx";
header('Content-disposition: attachment; filename="' . XLSXWriter::sanitize_filename($filename) . '"');
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate');
header('Pragma: public');
$rows2 = array(
    array('业务订单明细'),
);
if($_SESSION['price_flag'] == 'N'){

$rows = array(
    array(
        '订单号','状态','客户简称','客户订单号','含税金额','未税金额','税金','已出货金额','税别','合同编号','备注','需求日期','下单日期'
    ),
);
}else{
  $rows = array(
    array(
      '订单号','状态','客户简称','客户订单号','税别','合同编号','备注','需求日期','下单日期'
    ),
);
}

$writer = new XLSXWriter();
$writer->setAuthor('Shunfansoft');

//$writer->writeSheetHeader('Sheet1', $header);
foreach ($rows2 as $row2)
    $writer->writeSheetRow('Sheet1', $row2);
foreach ($rows as $row)
    $writer->writeSheetRow('Sheet1', $row);

while ($v = DB_fetch_array($result_num)) {

  $approve_date='';
  if ($v['approve_date']>1) {
     $approve_date=date('Y-m-d', $v['approve_date']);
  }
if($_SESSION['price_flag'] == 'N'){

    $writer->writeSheetRow('Sheet1', array(
        $v['order_number'], $v['status'],$v['customer_code'],$v['customer_order_number'],sprintf("%.2f",$v['order_all_amount']),sprintf("%.2f",$v['all_line_amount']),sprintf("%.2f",$v['tax_amount']),sprintf("%.2f",$v['ship_amount']),$v['tax_name'],$v['contract_number'],
          $v['header_remark'],date('Y-m-d', $v['need_date']),date('Y-m-d', $v['creation_date'])
    ));
  }else{
    $writer->writeSheetRow('Sheet1', array(
      $v['order_number'], $v['status'],$v['customer_code'],$v['customer_order_number'],$v['tax_name'],$v['contract_number'],
          $v['header_remark'],date('Y-m-d', $v['need_date']),date('Y-m-d', $v['creation_date'])
  ));
  }
}

$writer->writeToStdOut();
//$writer->writeToFile('example.xlsx');
//echo $writer->writeToString();
exit(0);
