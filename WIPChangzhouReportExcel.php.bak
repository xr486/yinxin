<?php
//  首先引入XLSXWriter包
putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");
ob_start();
include('includes/session2.inc');
include('includes/SQL_CommonFunctions.inc');
unset($result);


$sql ="SELECT  a.version,a.status_type,a.so_header_number,a.so_line_number,
	a.wip_entity_name,e.item_no,e.item_name,e.item_desc,a.plan_start_date,
	e.units,a.start_quantity,a.quantity_completed,a.quantity_completed,a.new_plan_status,b.transaction_type,b.transaction_quantity,b.transaction_date,b.created_by,b.creation_date
	 from wip_jobs_all a,sf_item_no e,wip_changzhou_all b
	where  a.primary_item=e.item_no 
	and a.wip_entity_name=b.wip_entity_name
 
	";
 
	// echo $sql;


              
    if (isset($_POST['order_number']) and $_POST['order_number'] != '') { 
        $sql = $sql . " and a.so_header_number " . LIKE . " '%" . $_POST['order_number'] . "%' ";
    }
	if (isset($_POST['wip_entity_name']) and $_POST['wip_entity_name'] != '') { 
        $sql = $sql . " and a.wip_entity_name " . LIKE . " '%" . $_POST['wip_entity_name'] . "%' ";
    }
 
    if (isset($_POST['item_no']) and $_POST['item_no'] != '') { 
        $sql = $sql . " and e.item_no " . LIKE . " '%" . $_POST['item_no'] . "%' ";
    } 
	if (isset($_POST['item_name']) and $_POST['item_name'] != '') { 
        $sql = $sql . " and e.item_name " . LIKE . " '%" . $_POST['item_name'] . "%' ";
    } 

	 if (empty($_POST['FromDate']) == 0) 
  {
    $SQL_FromDate = strtotime($_POST['FromDate']);
    $sql .= " and transaction_date >= '" . $SQL_FromDate . "' ";
  }
  if (empty($_POST['ToDate']) == 0) 
  {
     $SQL_ToDate = strtotime($_POST['ToDate']) + 86400;
     $sql .= " and transaction_date <='" . $SQL_ToDate . "' ";
  }
    $sql .=" order by b.transaction_date desc ";
   
	$result_num = DB_query($sql,$db);
//oci_execute($par);
include_once("xlsxwriter.class.php");
$date=date('YmdHis');
//ini_set('display_errors', 0);
//ini_set('log_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);

$filename = "生产排程报表".$date.".xlsx";
header('Content-disposition: attachment; filename="'.XLSXWriter::sanitize_filename($filename).'"');
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate');
header('Pragma: public');
$rows2 = array( 
  array('生产排程报表'),
);

$rows = array( 
  array('工单名','订单','行','料号','料号名称','规格型号','版本','单位','交易类型','交易数量','交易日期','做账人员','做账时间'),
);
 

$writer = new XLSXWriter();
$writer->setAuthor('Shunfansoft'); 

//$writer->writeSheetHeader('Sheet1', $header);
 foreach($rows2 as $row2)
	$writer->writeSheetRow('Sheet1', $row2);
foreach($rows as $row)
	$writer->writeSheetRow('Sheet1', $row);

	while ($v = DB_fetch_array($result_num)) {
      $writer->writeSheetRow('Sheet1', array($v['wip_entity_name'],$v['so_header_number'],$v['so_line_number'],$v['item_no'],$v['item_name'],
        $v['item_desc'],$v['version'],$v['units'],$v['transaction_type'],$v['transaction_quantity'],
        date('Y-m-d', $v['transaction_date']),$v['created_by'],date('Y-m-d H:i:s', $v['creation_date']),
        ));
	 }
      
	  

$writer->writeToStdOut();
//$writer->writeToFile('example.xlsx');
//echo $writer->writeToString();
exit(0);
?>
