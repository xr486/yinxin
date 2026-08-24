<?php
//  首先引入XLSXWriter包
putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");
ob_start();
include('includes/session2.inc');
include('includes/SQL_CommonFunctions.inc');
unset($result);
 
// $sql = "SELECT  f.item_name, f.units,a.wip_entity_name,a.primary_item,c.operation_seq_num,c.operation_code,c.line_code,c.employee_num ,(select e.employee_name from  hr_employees e   where c.employee_num=e.employee_num )  employee_name,c.transaction_date,c.bad_quantity,c.remark,c.transaction_type,c.transaction_quantity,c.begin_date,c.end_date,wancheng_bili,e.standard_time
// 				from wip_jobs_all a, wip_transactions c , sf_item_no f  ,wip_operation_plan e 
// where    a.wip_entity_name=c.wip_entity_name  and a.primary_item=f.item_no and e.operation_seq_num=c.operation_seq_num and e.wip_entity_name=c.wip_entity_name
//   ";
$sql = " select  aa.* from ( SELECT  f.item_name, f.units,a.wip_entity_name,a.primary_item,c.operation_seq_num,c.operation_code,c.line_code,c.employee_num ,(select e.employee_name from  hr_employees e   where c.employee_num=e.employee_num )  employee_name,c.transaction_date,c.bad_quantity,c.remark,c.transaction_type,c.transaction_quantity,c.begin_date,c.end_date,wancheng_bili,e.standard_time
				from wip_jobs_all a, wip_transactions c , sf_item_no f ,wip_operation_plan e 
 where   a.wip_entity_name=c.wip_entity_name  and a.primary_item=f.item_no and e.operation_seq_num=c.operation_seq_num and e.wip_entity_name=c.wip_entity_name )  aa where 1=1
   ";
  

  if (isset($_GET['wip_entity_name']) && $_GET['wip_entity_name'] != '') {
      $sql = $sql . " and aa.wip_entity_name " . LIKE . " '%" . $_GET['wip_entity_name'] . "%' ";
    }
  if (isset($_GET['primary_item']) and $_GET['primary_item'] != '') {
    $sql = $sql . " and primary_item " . LIKE . " '%" . $_GET['primary_item'] . "%' ";
  }

  if (isset($_GET['item_name']) and $_GET['item_name'] != '') {
    $sql = $sql . " and item_name " . LIKE . " '%" . $_GET['item_name'] . "%' ";
  }
  if (isset($_GET['line_code']) and $_GET['line_code'] != '') {
    $sql = $sql . " and line_code " . LIKE . " '%" . $_GET['line_code'] . "%' ";
  }
 if (isset($_GET['operation_code']) and $_GET['operation_code'] != '') {
    $sql = $sql . " and operation_code " . LIKE . " '%" . $_GET['operation_code'] . "%' ";
  }
 if (isset($_GET['employee_num']) and $_GET['employee_num'] != '') {
    $sql = $sql . " and employee_num " . LIKE . " '%" . $_GET['employee_num'] . "%' ";
  }
  if (isset($_GET['employee_name']) and $_GET['employee_name'] != '') {
    $sql = $sql . " and employee_name " . LIKE . " '%" . $_GET['employee_name'] . "%' ";
  }
  if (empty($_GET['FromDate']) == 0) {
    $SQL_FromDate = strtotime($_GET['FromDate']);
    $sql .= " and transaction_date >= '" . $SQL_FromDate . "' ";
  }
  if (empty($_GET['ToDate']) == 0) {
    $SQL_ToDate = strtotime($_GET['ToDate']) + 86400;
    $sql .= " and transaction_date <='" . $SQL_ToDate . "' ";
  }


//$sql .= " order by customer_code  ";

$result_num = DB_query($sql, $db);
//oci_execute($par);
include_once("xlsxwriter.class.php");
$date = date('YmdHis');
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);

$filename = "工单生产明细查询" . $date . ".xlsx";
header('Content-disposition: attachment; filename="' . XLSXWriter::sanitize_filename($filename) . '"');
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate');
header('Pragma: public');
$rows2 = array(
    array('工单生产明细查询'),
);
 
    $rows = array(
        array(
          '生产单号', '料号', '料号名称' , '单位' ,'工序','工序名称','生产人员','生产人员', '类型', '生产数量','不良数量','备注',
          '开始时间','结束时间','生产小时','标准时间s','工时效率'
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
 
    $jiagong_time_all=round((($v['end_date']-$v['begin_date'])/3600),3);
	  $jiagong_time=round(($jiagong_time_all/$v['transaction_quantity']),2);
	  $jixiaoleiji=($v['standard_time'] * $v['transaction_quantity']  * $v['wancheng_bili'])/100;
	  $shiji= ($jiagong_time * $v['transaction_quantity']  * $v['wancheng_bili'])/100 ;
	  if ($shiji==0) {
	   $shengchanxiaolv=0;
	  } else {
	  $shengchanxiaolv= round(($jixiaoleiji*100  / $shiji),2)   ;
	  }
    $begin_date=date('Y-m-d H:i:s',$v['begin_date']);
    $end_date=date('Y-m-d H:i:s',$v['end_date']);
    $hours_diff = round((($v['end_date']-$v['begin_date'])/3600),3);//工时
    $standard_time = isset($v['standard_time']) ? $v['standard_time'] : 0;
    
    //工时效率
    if($hours_diff>0 && $standard_time>0 && $v['transaction_quantity']>0){
        $hour_rate_value = round(($v['transaction_quantity'] * ($v['standard_time'] / 3600) / $hours_diff) * 100, 3);
        $hour_rate = $hour_rate_value . '%';
    }
    else{
        $hour_rate = '';
    }
   
        $writer->writeSheetRow('Sheet1', array( 
         
            $v['wip_entity_name'],
            $v['primary_item'],
            $v['item_name'],
            $v['units'],
             $v['operation_seq_num'], $v['operation_code'], 
            $v['employee_num'], $v['employee_name'],   $v['transaction_type'],
            $v['transaction_quantity'],
            $v['bad_quantity'],
            $v['remark'],
            $begin_date ,
            $end_date ,
            round((($v['end_date']-$v['begin_date'])/3600),3),
            ($standard_time?$standard_time:''),
            $hour_rate
            
        ));
    }


$writer->writeToStdOut();
//$writer->writeToFile('example.xlsx');
//echo $writer->writeToString();
exit(0);
