<?php
//  首先引入XLSXWriter包

putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");
ob_start();
include('includes/session2.inc');
include('includes/SQL_CommonFunctions.inc');
unset($result);
 


    $sql = "select b.customer_code,b.customer_name,a.employee_num,a.employee_name,l.stockid,d.item_name,d.item_desc,sum(l.quantity) quantity,sum(quantity_shiped) quantity_shiped
  from so_lines_all l,so_headers_all h,
  customers b,sf_item_no d,hr_employees a
  where  l.order_number = h.order_number 
  and b.customer_code=h.customer_code
  and l.stockid=d.item_no and a.employee_num=h.yewu 
";

if (isset($_GET['order_number']) and $_GET['order_number'] != '') {
    $sql = $sql . " and h.order_number  " . LIKE . " '%" . $_GET['order_number'] . "%' ";
}

if (isset($_GET['customer_name']) and $_GET['customer_name'] != '') {
    $sql = $sql . " and b.customer_name " . LIKE . " '%" . $_GET['customer_name'] . "%' ";
}
if (isset($_GET['customer_code']) and $_GET['customer_code'] != '') {
    $sql = $sql . " and b.customer_code " . LIKE . " '%" . $_GET['customer_code'] . "%' ";
}
if (isset($_GET['item_no']) and $_GET['item_no'] != '') {
    $sql = $sql . " and l.stockid " . LIKE . " '%" . $_GET['item_no'] . "%' ";
}
if (isset($_GET['item_name']) and $_GET['item_name'] != '') {
    $sql = $sql . " and d.item_name " . LIKE . " '%" . $_GET['item_name'] . "%' ";
}
if (isset($_GET['item_desc']) and $_GET['item_desc'] != '') {
    $sql = $sql . " and d.item_desc " . LIKE . " '%" . $_GET['item_desc'] . "%' ";
}
if (isset($_GET['employee_num']) and $_GET['employee_num'] != '') {
    $sql = $sql . " and a.employee_num " . LIKE . " '%" . $_GET['employee_num'] . "%' ";
}
if (isset($_GET['employee_name']) and $_GET['employee_name'] != '') {
    $sql = $sql . " and a.employee_name " . LIKE . " '%" . $_GET['employee_name'] . "%' ";
}
if (isset($_GET['dengji']) and $_GET['dengji'] != '') {
    $sql = $sql . " and b.dengji " . LIKE . " '%" . $_GET['dengji'] . "%' ";
}

if (empty($_GET['FromDate']) == 0) {
    $SQL_FromDate = strtotime($_GET['FromDate']);
    $sql .= " and h.creation_date >= '" . $SQL_FromDate . "' ";
}
if (empty($_GET['ToDate']) == 0) {
    $SQL_ToDate = strtotime($_GET['ToDate']) + 86400;
    //echo $SQL_ToDate;
    $sql .= " and h.creation_date <='" . $SQL_ToDate . "' ";
}

 if (empty($_GET['FromDate2']) == 0) {
    $SQL_FromDate2 = strtotime($_GET['FromDate2']);
    $sql .= " and h.qianding_date >= '" . $SQL_FromDate2 . "' ";
}
if (empty($_GET['ToDate2']) == 0) {
    $SQL_ToDate2 = strtotime($_GET['ToDate2']) + 86400;
    //echo $SQL_ToDate;
    $sql .= " and h.qianding_date <='" . $SQL_ToDate2 . "' ";
}

  if($_GET['checkresult']!=""){
      $sql .= " and h.status ='" . $_GET['checkresult'] . "' ";
    
}

$sql.=' GROUP BY b.customer_code,b.customer_name,a.employee_num,a.employee_name,l.stockid,d.item_name,d.item_desc';

    $result_num = DB_query($sql,$db);
//oci_execute($par);
include_once("xlsxwriter.class.php");
$date=date('YmdHis');
//ini_set('display_errors', 0);
//ini_set('log_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);

$filename = "业务订单统计".$date.".xlsx";
header('Content-disposition: attachment; filename="'.XLSXWriter::sanitize_filename($filename).'"');
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate');
header('Pragma: public');
$rows2 = array( 
  array('业务订单统计'),
);

$rows = array( 
  array('客户简称','业务员工号','业务员姓名','料号','料号名称','规格型号','订单数量','出货数量'),
); 
 
$writer = new XLSXWriter();
$writer->setAuthor('Shunfansoft'); 

//$writer->writeSheetHeader('Sheet1', $header);
 foreach($rows2 as $row2)
	$writer->writeSheetRow('Sheet1', $row2);
foreach($rows as $row)
	$writer->writeSheetRow('Sheet1', $row);

	while ($v = DB_fetch_array($result_num)) {

		 

     $writer->writeSheetRow('Sheet1', array($v['customer_code'],$v['employee_num'],$v['employee_name'],$v['stockid'].' ',$v['item_name'],$v['item_desc'],
        $v['quantity'],$v['quantity_shiped']));
	 }
    
$writer->writeToStdOut();
//$writer->writeToFile('example.xlsx');
//echo $writer->writeToString();
exit(0);
?>
