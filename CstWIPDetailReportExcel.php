<?php
//  首先引入XLSXWriter包

putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");
ob_start();
include('includes/session2.inc');
include('includes/SQL_CommonFunctions.inc');
unset($result);
 
 $sql = "SELECT a.wip_entity_name, 
                   c.item_no, c.item_name,c.item_desc,c.units,a.start_quantity,a.end_quantity,a.plan_start_date,a.ym 
            FROM cst_wip_yuejie_all a,sf_item_no c,wip_jobs_all b  
            WHERE  a.wip_entity_name=b.wip_entity_name and  b.primary_item = c.item_no 
			and c.item_type='F' and a.end_quantity>0 ";
   	
    
    if (isset($_GET['item_no']) and $_GET['item_no'] != '') { 		
        $sql = $sql." and c.item_no ".LIKE." '%".$_GET['item_no']."%' ";
    } 
    if (isset($_GET['item_name']) and $_GET['item_name'] != '') { 		
        $sql = $sql." and c.item_name ".LIKE." '%".$_GET['item_name']."%' ";
    } 
	if (isset($_GET['item_desc']) and $_GET['item_desc'] != '') { 		
        $sql = $sql." and c.item_desc ".LIKE." '%".$_GET['item_desc']."%' ";
    } 
    if (isset($_GET['ym']) and $_GET['ym'] != '') { 		
        $sql = $sql." and a.ym ='".$_GET['ym']."' ";
    } 
    if (isset($_GET['wip_entity_name']) and $_GET['wip_entity_name'] != '') {	
        $sql = $sql." and a.wip_entity_name ".LIKE." '%".$_GET['wip_entity_name']."%' "; 
    } 
	 
	$sql = $sql . " order by a.wip_entity_name,c.item_no, c.item_name";
 $result_num = DB_query($sql, $db); 
//oci_execute($par);
include_once("xlsxwriter.class.php");
$date=date('YmdHis');
//ini_set('display_errors', 0);
//ini_set('log_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);

$filename = "成品进耗存明细表".$date.".xlsx";
header('Content-disposition: attachment; filename="'.XLSXWriter::sanitize_filename($filename).'"');
header("Content-wip_entity_name: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate');
header('Pragma: public');
$rows2 = array( 
  array('成品进耗存明细表'),

);
$rows = array( 
  array('年月','工单编号','料号','料号名称','规格型号','开工批量','开工日期','期未数量'),

); 
$writer = new XLSXWriter();
$writer->setAuthor('Shunfansoft'); 

//$writer->writeSheetHeader('Sheet1', $header);
 foreach($rows2 as $row2)
	$writer->writeSheetRow('Sheet1', $row2);
foreach($rows as $row)
	$writer->writeSheetRow('Sheet1', $row);

	while ($v = DB_fetch_array($result_num)) {

		  
	 $writer->writeSheetRow('Sheet1', array($v['ym'],$v['wip_entity_name'],$v['item_no'],$v['item_name'],$v['item_desc'],$v['start_quantity'],date('Y-m-d',$v['plan_start_date']),$v['end_quantity'],
        ));
	 }
  
$writer->writeToStdOut();
//$writer->writeToFile('example.xlsx');
//echo $writer->writeToString();
exit(0);
?>
