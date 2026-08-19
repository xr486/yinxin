<?php
//  首先引入XLSXWriter包

putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");
ob_start();
include('includes/session2.inc');
include('includes/SQL_CommonFunctions.inc');
unset($result);
$sql ="SELECT  a.version,a.status_type, a.so_header_number,a.so_line_number,
a.wip_entity_name,e.item_no,e.item_name,e.item_desc,a.plan_start_date,a.creation_date,
e.units,a.start_quantity,a.quantity_completed,e.gongyi,a.quantity_completed,a.date_closed,a.new_plan_status
 from wip_jobs_all a, sf_item_no e
where   a.primary_item=e.item_no
 
";

// echo $sql;


          
if (isset($_GET['order_number']) and $_GET['order_number'] != '') { 
    $sql = $sql . " and a.so_header_number " . LIKE . " '%" . $_GET['order_number'] . "%' ";
}
if (isset($_GET['wip_entity_name']) and $_GET['wip_entity_name'] != '') { 
    $sql = $sql . " and a.wip_entity_name " . LIKE . " '%" . $_GET['wip_entity_name'] . "%' ";
}

if (isset($_GET['item_no']) and $_GET['item_no'] != '') { 
    $sql = $sql . " and e.item_no " . LIKE . " '%" . $_GET['item_no'] . "%' ";
} 
if (isset($_GET['item_name']) and $_GET['item_name'] != '') { 
    $sql = $sql . " and e.item_name " . LIKE . " '%" . $_GET['item_name'] . "%' ";
} 

if (isset($_GET['gongyi']) and $_GET['gongyi'] != '') { 
    $sql = $sql . " and e.gongyi " . LIKE . " '%" . $_GET['gongyi'] . "%' ";
} 
if (isset($_GET['version']) and $_GET['version'] != '') { 
    $sql = $sql . " and a.version " . LIKE . " '%" . $_GET['version'] . "%' ";
} 

 
if(isset($_GET['FromDate']) and $_GET['FromDate'] != ''){
    $sql = $sql." and a.plan_start_date >=".strtotime($_GET['FromDate'])." ";
}
 if(isset($_GET['ToDate']) and $_GET['ToDate'] != ''){
    $sql = $sql." and a.plan_start_date <=".strtotime($_GET['ToDate'])." ";
}
 $result_num = DB_query($sql, $db); 
//oci_execute($par);
include_once("xlsxwriter.class.php");
$date=date('YmdHis');
//ini_set('display_errors', 0);
//ini_set('log_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);

$filename = "工单资料查询报表".$date.".xlsx";
header('Content-disposition: attachment; filename="'.XLSXWriter::sanitize_filename($filename).'"');
header("Content-wip_entity_name: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate');
header('Pragma: public');
$rows2 = array( 
  array('工单资料查询报表'),

);
$rows = array( 
  array('状态','工单号','订单号','行','料号','料号名称','规格型号','版本','单位','生产量','完工量','开工日期','关闭日期','建立日期'),

);  
$writer = new XLSXWriter();
$writer->setAuthor('Shunfansoft'); 

//$writer->writeSheetHeader('Sheet1', $header);
 foreach($rows2 as $row2)
	$writer->writeSheetRow('Sheet1', $row2);
foreach($rows as $row)
	$writer->writeSheetRow('Sheet1', $row);

	while ($v = DB_fetch_array($result_num)) {
        if ($v['date_closed']>0) {
            $date_closed= date('Y-m-d', $v['date_closed']);
        } else {
          $date_closed='';
        }

	 $writer->writeSheetRow('Sheet1', array($v['status_type'],$v['wip_entity_name'],$v['so_header_number'],$v['so_line_number'],$v['item_no'],$v['item_name'],$v['item_desc'],$v['version'],$v['units'],
     $v['start_quantity'],$v['quantity_completed'],date('Y-m-d', $v['plan_start_date']),$date_closed,date('Y-m-d', $v['creation_date']) ));
	 }
    
$writer->writeToStdOut();
//$writer->writeToFile('example.xlsx');
//echo $writer->writeToString();
exit(0);
?>
