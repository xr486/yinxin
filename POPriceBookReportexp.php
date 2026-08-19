<?php
//  首先引入XLSXWriter包

//putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");
ob_start();
include('includes/session.inc');
include('includes/SQL_CommonFunctions.inc');
unset($result);
$sql_num ="select b.segment1,b.description
from  mtl_items_all b ";
//$par = oci_parse($conn, $sql_num);
//oci_execute($par);

$sql_num ="select *
from  po_item_price_book b ";
 $result_num = DB_query($sql_num, $db); 
//oci_execute($par);
include_once("xlsxwriter.class.php");

//ini_set('display_errors', 0);
//ini_set('log_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);

$filename = "example.xlsx";
header('Content-disposition: attachment; filename="'.XLSXWriter::sanitize_filename($filename).'"');
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate');
header('Pragma: public');

$rows = array( 
  array('料号','料号描述','生效日期','失效日期','含税价格','不含税','币别','税别','供应商','供应商'),

);

$writer = new XLSXWriter();
$writer->setAuthor('Some Author'); 

//$writer->writeSheetHeader('Sheet1', $header);
 
foreach($rows as $row)
	$writer->writeSheetRow('Sheet1', $row);

	while ($v = DB_fetch_array($result_num)) {
	 $writer->writeSheetRow('Sheet1', array($v['segment1'],$v['description'],$v['effectivity_date'],$v['disable_date'],$v['no_tax_price'],$v['all_price'],$v['curreny_code'],$v['tax_code'],$v['vendor_code'],$v['vendor_name']));
	 }
 
$writer->writeToStdOut();
//$writer->writeToFile('example.xlsx');
//echo $writer->writeToString();
exit(0);
?>
