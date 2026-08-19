<?php
//  首先引入XLSXWriter包

putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");
ob_start();
include('includes/session2.inc');
include('includes/SQL_CommonFunctions.inc');
unset($result);
$sql ="SELECT  a.status_type,a.so_header_number,a.so_line_number,
	a.wip_entity_name,e.item_no,e.item_name,e.item_desc,a.plan_start_date,a.creation_date,
	e.units,a.start_quantity,a.quantity_completed,a.quantity_completed
	 from wip_jobs_all a,sf_item_no e
	where  a.primary_item=e.item_no 
	and a.start_quantity > (a.quantity_completed + a.quantity_scrapped)
	and a.status_type='开始'
	";
 
	// echo $sql;

   if (empty($_GET['FromDate']) == 0) 
  {
    $SQL_FromDate = strtotime($_GET['FromDate']);
    $sql .= " and plan_start_date >= '" . $SQL_FromDate . "' ";
  }
  if (empty($_GET['ToDate']) == 0) 
  {
     $SQL_ToDate = strtotime($_GET['ToDate']) + 86400;
     $sql .= " and plan_start_date <='" . $SQL_ToDate . "' ";
  }
              
    if (isset($_GET['order_number']) and $_GET['order_number'] != '') { 
        $sql = $sql . " and a.so_header_number " . LIKE . " '%" . $_GET['order_number'] . "%' ";
    }

    if (isset($_GET['item_no']) and $_GET['item_no'] != '') { 
        $sql = $sql . " and e.item_no " . LIKE . " '%" . $_GET['item_no'] . "%' ";
    } 
   
	if (isset($_GET['wip_name']) and $_GET['wip_name'] != '') { 
        $sql = $sql . " and a.wip_entity_name " . LIKE . " '%" . $_GET['wip_name'] . "%' ";
    }
	if (isset($_GET['item_name']) and $_GET['item_name'] != '') { 
        $sql = $sql . " and e.item_name " . LIKE . " '%" . $_GET['item_name'] . "%' ";
    }
	
   


 $result_num = DB_query($sql, $db); 
//oci_execute($par);
include_once("xlsxwriter.class.php");
$date=date('YmdHis');
//ini_set('display_errors', 0);
//ini_set('log_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);

$filename = "工单待入库明细".$date.".xlsx";
header('Content-disposition: attachment; filename="'.XLSXWriter::sanitize_filename($filename).'"');
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate');
header('Pragma: public');
$rows2 = array( 
  array('工单待入库明细'),

);
$rows = array( 
  array('工单名', '订单号','行','产品料号','料号名称','规格型号','单位' ,'开工量','完工量' ,'报废量', '生产日期' ),

); 
     
$writer = new XLSXWriter();
$writer->setAuthor('Shunfansoft'); 

//$writer->writeSheetHeader('Sheet1', $header);
 foreach($rows2 as $row2)
	$writer->writeSheetRow('Sheet1', $row2);
foreach($rows as $row)
	$writer->writeSheetRow('Sheet1', $row);

	while ($v = DB_fetch_array($result_num)) {

     $writer->writeSheetRow('Sheet1', array($v['wip_entity_name'],$v['so_header_number'],$v['so_line_number'],
     $v['item_no'],
     $v['item_name'],$v['item_desc'],$v['units'].' ',$v['start_quantity'],$v['quantity_completed'],$v['quantity_scrapped'],date('Y-m-d H:i:s',$v['plan_start_date']) 
		  ));
	 }
  
$writer->writeToStdOut();
//$writer->writeToFile('example.xlsx');
//echo $writer->writeToString();
exit(0);
?>
