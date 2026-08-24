<?php
//  首先引入XLSXWriter包

putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");
ob_start();
include('includes/session2.inc');
include('includes/SQL_CommonFunctions.inc');
unset($result);

$sql8="SELECT  b.item_no,b.item_name,b.item_desc,c.start_quantity,c.wip_entity_name,c.wip_entity_id  FROM sf_item_no b,wip_jobs_all c where b.item_no=c.primary_item 
  and c.wip_entity_name = '" .$_GET['Updatewip_entity_name']."' ";
  
  $result6 = DB_query($sql8, $db);
$myrow6 = DB_fetch_array($result6);
 $sql = " select a.plan_start_date,a.so_header_number,a.so_line_number,b.segment1,c.item_desc,c.item_name,b.comments,quantity_issued,b.required_quantity,a.wip_entity_name,b.quantity_per_assembly,b.operation_seq_num,(select sum(quantity_issued- required_quantity) from wip_material_requierments w,wip_jobs_all j where w.segment1=c.item_no  and quantity_issued>required_quantity and w.wip_entity_name=j.wip_entity_name and j.status_type='开始' ) chaofa_qty,(select sum(cc.quantity)  from inv_onhand_quantity_all cc where cc.stockid=c.item_no  ) onhand_quantity
						from wip_jobs_all a,wip_material_requierments b,sf_item_no c 
                        where b.wip_entity_name =a.wip_entity_name and b.segment1=c.item_no and  a.wip_entity_name = '" .$myrow6['wip_entity_name']."'
       order by b.operation_seq_num,segment1  
						";
 

 $result_num = DB_query($sql, $db); 
//oci_execute($par);
include_once("xlsxwriter.class.php");
$date=date('YmdHis');
//ini_set('display_errors', 0);
//ini_set('log_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);

$filename = "工艺单".$date.".xlsx";
header('Content-disposition: attachment; filename="'.XLSXWriter::sanitize_filename($filename).'"');
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate');
header('Pragma: public');
$rows2 = array( 
  array('工艺单'),

);

$rows3 = array( 
  array('标的名称','机型' ,'工单号', '订单量', '备料日期'   ),

);
$rows4 = array( 
  array($myrow6['item_no'],$myrow6['item_name'] ,$myrow6['wip_entity_name'], $myrow6['start_quantity'],date('Y-m-d')  ),

);
$rows = array( 
  array('序号','制程' ,'料号', '料号名称', '单耗' ,'需求量' ,'实发量', '退料量','备注'  ),

); 
 
  
 
$writer = new XLSXWriter();
$writer->setAuthor('Shunfansoft'); 

//$writer->writeSheetHeader('Sheet1', $header);
 foreach($rows2 as $row2)
	$writer->writeSheetRow('Sheet1', $row2);

	foreach($rows3 as $row3)
	$writer->writeSheetRow('Sheet1', $row3);

	foreach($rows4 as $row4)
	$writer->writeSheetRow('Sheet1', $row4);

foreach($rows as $row)
	$writer->writeSheetRow('Sheet1', $row);
$line=0;
	while ($v = DB_fetch_array($result_num)) {

	       $line=$line+1; 
     $writer->writeSheetRow('Sheet1', array($line,$v['operation_seq_num'],$v['segment1'],$v['item_name'].$myrow2['item_desc'],$v['quantity_per_assembly'],$v['required_quantity']  ));
	 }
       
 

$writer->writeToStdOut();
//$writer->writeToFile('example.xlsx');
//echo $writer->writeToString();
exit(0);
?>
