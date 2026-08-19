<?php
//  首先引入XLSXWriter包
putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");
ob_start();
include('includes/session2.inc');
include('includes/SQL_CommonFunctions.inc');
unset($result);

$sql = "SELECT   c.status,f.item_name, f.units,a.wip_entity_name,a.primary_item,c.operation_code,c.line_code,c.employee_num,d.employee_name,c.banbie,c.operation_seq_num,c.begin_date,c.wip_id
from wip_jobs_all a, wip_production c ,sf_item_no f,hr_employees d
where   a.wip_entity_name=c.wip_entity_name and a.primary_item=f.item_no and c.employee_num=d.employee_num";

  if (isset($_GET['wip_entity_name']) and $_GET['wip_entity_name'] != '') {
    $sql = $sql . " and  a.wip_entity_name " . LIKE . " '%" . $_GET['wip_entity_name'] . "%' ";
  }
  if (isset($_GET['primary_item']) and $_GET['primary_item'] != '') {
    $sql = $sql . " and primary_item " . LIKE . " '%" . $_GET['primary_item'] . "%' ";
  }

  if (isset($_GET['item_name']) and $_GET['item_name'] != '') {
    $sql = $sql . " and f.item_name " . LIKE . " '%" . $_GET['item_name'] . "%' ";
  }
  if (isset($_GET['line_code']) and $_GET['line_code'] != '') {
    $sql = $sql . " and c.line_code " . LIKE . " '%" . $_GET['line_code'] . "%' ";
  }
 if (isset($_GET['operation_code']) and $_GET['operation_code'] != '') {
    $sql = $sql . " and c.operation_code " . LIKE . " '%" . $_GET['operation_code'] . "%' ";
  }
  if (isset($_GET['employee_name']) and $_GET['employee_name'] != '') {
    $sql = $sql . " and d.employee_name " . LIKE . " '%" . $_GET['employee_name'] . "%' ";
  }
  if (isset($_GET['employee_num']) and $_GET['employee_num'] != '') {
    $sql = $sql . " and c.employee_num " . LIKE . " '%" . $_GET['employee_num'] . "%' ";
  }
 
   
  if (empty($_GET['FromDate']) == 0) {
    $SQL_FromDate = strtotime($_GET['FromDate']);
    $sql .= " and c.transaction_date >= '" . $SQL_FromDate . "' ";
  }
  if (empty($_GET['ToDate']) == 0) {
    $SQL_ToDate = strtotime($_GET['ToDate']) + 86400;
    $sql .= " and c.transaction_date <='" . $SQL_ToDate . "' ";
  }
            
    $sql .= " order by d.need_date,a.wip_entity_name,c.operation_seq_num  ";
$result_num = DB_query($sql, $db);
//oci_execute($par);
include_once("xlsxwriter.class.php");
$date = date('YmdHis');
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);

$filename = "工单生产中明细" . $date . ".xlsx";
header('Content-disposition: attachment; filename="' . XLSXWriter::sanitize_filename($filename) . '"');
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate');
header('Pragma: public');
$rows2 = array(
    array('工单生产中明细'),
);

    $rows = array(
        array(
            '生产单号' ,'产品料号', '料号名称' ,'单位', '工序号', '工序名称', '工号','姓名', '开始时间','状态' ,'已生产小时',
        ),
    );
 

$writer = new XLSXWriter();
$writer->setAuthor('Shunfansoft');
// $writer->abc(); 

// $writer->writeSheetHeader('Sheet1', $header);

foreach ($rows2 as $row2)
    $writer->writeSheetRow('Sheet1', $row2);
foreach ($rows as $row)
    $writer->writeSheetRow('Sheet1', $row);
  
while ($v = DB_fetch_array($result_num)) {
        $time=time();
       if($v['status']=='开始'){
    	     $time_hiff = round((($time-$v['begin_date'])/3600),3);
    	 }
    	 else{
    	    $time_hiff = 0; 
    	 }
        $writer->writeSheetRow('Sheet1', array(
    
            $v['wip_entity_name'],
            $v['primary_item'],
            $v['item_name'],
            $v['units'],
            $v['operation_seq_num'],
            $v['operation_code'], 
            $v['employee_num'],$v['employee_name'],
            date('Y-m-d H:i:s',$v['begin_date']),
            $v['status'],
            $time_hiff
            
        ));

}

$writer->writeToStdOut();
//$writer->writeToFile('example.xlsx');
//echo $writer->writeToString();
exit(0);
