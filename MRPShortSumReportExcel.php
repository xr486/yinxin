<?php
//  首先引入XLSXWriter包

putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");
ob_start();
include('includes/session2.inc');
include('includes/SQL_CommonFunctions.inc');
unset($result);
 
$sql = " select  b.segment1, c.item_desc,c.item_name,c.units,sum(b.required_quantity - quantity_issued - fenpei_quantity) need_qty
						from wip_jobs_all a,wip_material_requierments b,sf_item_no c  
                        where b.wip_entity_name =a.wip_entity_name and b.segment1=c.item_no  
						and a.status_type='开始' 
						and b.required_quantity - quantity_issued - fenpei_quantity >0
						group by b.segment1, c.item_desc,c.item_name,c.units ";

    
     if (isset($_GET['wip_entity_name']) and $_GET['wip_entity_name'] != '') { 
		$sql = $sql . " and a.wip_entity_name " . LIKE . " '%" . $_GET['wip_entity_name'] . "%' ";
    }
 
    if (isset($_GET['item_no']) and $_GET['item_no'] != '') {
        $sql = $sql . " and c.item_no " . LIKE . " '%" . $_GET['item_no'] . "%' ";
    }
	 if (isset($_GET['item_name']) and $_GET['item_name'] != '') {
        $sql = $sql . " and c.item_name " . LIKE . " '%" . $_GET['item_name'] . "%' ";
    }
	if (isset($_GET['item_desc']) and $_GET['item_desc'] != '') {
        $sql = $sql . " and c.item_desc " . LIKE . " '%" . $_GET['item_desc'] . "%' ";
    }
	 
    if (empty($_GET['FromDate']) == 0) {
        $SQL_FromDate = strtotime($_GET['FromDate']);
        $sql .= " and a.plan_start_date >= '" . $SQL_FromDate . "' ";
    }
    if (empty($_GET['ToDate']) == 0) {
        $SQL_ToDate = strtotime($_GET['ToDate']) + 86400;
        //echo $SQL_ToDate;
        $sql .= " and a.plan_start_date <='" . $SQL_ToDate . "' ";
    }

	 $sql .= " order by a.plan_start_date,a.wip_entity_name "; 
	 $result_num = DB_query($sql, $db); 
//oci_execute($par);
include_once("xlsxwriter.class.php");
$date=date('YmdHis');
//ini_set('display_errors', 0);
//ini_set('log_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);

$filename = "料号明细报表".$date.".xlsx";
header('Content-disposition: attachment; filename="'.XLSXWriter::sanitize_filename($filename).'"');
header("Content-wip_entity_name: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate');
header('Pragma: public');
$rows2 = array( 
  array('料号明细报表'),

);
$rows = array( 
  array('料号','料号名称','规格型号','单位','缺料数量'),

); 
 
                 
$writer = new XLSXWriter();
$writer->setAuthor('Shunfansoft'); 

//$writer->writeSheetHeader('Sheet1', $header);
 foreach($rows2 as $row2)
	$writer->writeSheetRow('Sheet1', $row2);
foreach($rows as $row)
	$writer->writeSheetRow('Sheet1', $row);

	while ($v = DB_fetch_array($result_num)) {

		  
	 $writer->writeSheetRow('Sheet1', array($v['segment1'],$v['item_name'],$v['item_desc'],$v['units'],$v['need_qty'],
        ));
	 }
    
$writer->writeToStdOut();
//$writer->writeToFile('example.xlsx');
//echo $writer->writeToString();
exit(0);
?>
