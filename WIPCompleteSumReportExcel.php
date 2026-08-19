<?php
//  首先引入XLSXWriter包
putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");
ob_start();
include('includes/session2.inc');
include('includes/SQL_CommonFunctions.inc');
unset($result);
 
$sql = "SELECT  a.primary_item,c.operation_code,f.item_name, d.uom, c.employee_num,e.realname,sum(c.transaction_quantity) transaction_quantity 
				from wip_jobs_all a, wip_transactions c ,so_lines_all d,www_users e,sf_item_no f
where    c.transaction_quantity>0 and a.wip_entity_name=c.wip_entity_name and a.so_header_number=d.order_number and d.stockid=f.item_no  and a.so_line_number=d.line  and c.created_by=e.userid  ";

  
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
  if (isset($_GET['employee_num']) and $_GET['employee_num'] != '') {
    $sql = $sql . " and c.employee_num " . LIKE . " '%" . $_GET['employee_num'] . "%' ";
  }
  if (isset($_GET['realname']) and $_GET['realname'] != '') {
    $sql = $sql . " and e.realname " . LIKE . " '%" . $_GET['realname'] . "%' ";
  }

  if (empty($_GET['FromDate']) == 0) {
    $SQL_FromDate = strtotime($_GET['FromDate']);
    $sql .= " and c.transaction_date >= '" . $SQL_FromDate . "' ";
  }
  if (empty($_GET['ToDate']) == 0) {
    $SQL_ToDate = strtotime($_GET['ToDate']) + 86400;
    $sql .= " and c.transaction_date <='" . $SQL_ToDate . "' ";
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
        array(  '料号', '料号名称' , '单位' ,'工序名称',  '生产人员','生产人员',  '生产数量' 
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
   
        $writer->writeSheetRow('Sheet1', array( 
         
             
            $v['primary_item'],
            $v['item_name'],
            $v['uom'],
            $v['operation_code'], 
            $v['employee_num'],  $v['realname'],  
            $v['transaction_quantity'] 
        ));
    }


$writer->writeToStdOut();
//$writer->writeToFile('example.xlsx');
//echo $writer->writeToString();
exit(0);
