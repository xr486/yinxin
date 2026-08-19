<?php
//  首先引入XLSXWriter包

putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");
ob_start();
include('includes/session2.inc');
include('includes/SQL_CommonFunctions.inc');
unset($result);
 

$sql ="select a.*,(select price from po_lines_all bb where bb.stockid=a.item_no and  po_line_id in (select  max(cc.po_line_id) from po_headers_all c,po_lines_all cc where cc.stockid=a.item_no and c.po_num=cc.po_num and c.status='已签核' )) price  
	from sf_item_no a where 1=1 " ;
if (isset($_GET['ItemNo']) and $_GET['ItemNo'] != '') {
        $sql = $sql . " and item_no like '%" . $_GET['ItemNo'] . "%' ";
    }
    if (isset($_GET['item_name']) and $_GET['item_name'] != '') {
        $sql = $sql . " and item_name like '%" . $_GET['item_name'] . "%' ";
    }
	if (isset($_GET['item_desc']) and $_GET['item_desc'] != '') {
        $sql = $sql . " and item_desc like '%" . $_GET['item_desc'] . "%' ";
    }
     if (isset($_GET['item_category1']) and $_GET['item_category1'] != '') { 
		$sql = $sql . " and item_category1 like '%" . $_GET['item_category1'] . "%' ";
    }
	 
 

$sql .=" order by item_no";
 $result_num = DB_query($sql, $db); 
//oci_execute($par);
include_once("xlsxwriter.class.php");
$date=date('YmdHis');
//ini_set('display_errors', 0);
//ini_set('log_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);

$filename = "采购单价格查询".$date.".xlsx";
header('Content-disposition: attachment; filename="'.XLSXWriter::sanitize_filename($filename).'"');
header("Content-wip_entity_name: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate');
header('Pragma: public');
$rows2 = array( 
  array('采购单价格查询'),

);
$rows = array( 
  array('料号','料号名称','规格型号','单位','价格'),

); 
 
$writer = new XLSXWriter();
$writer->setAuthor('Shunfansoft'); 

//$writer->writeSheetHeader('Sheet1', $header);
 foreach($rows2 as $row2)
	$writer->writeSheetRow('Sheet1', $row2);
foreach($rows as $row)
	$writer->writeSheetRow('Sheet1', $row);

	while ($v = DB_fetch_array($result_num)) {

		  
	 $writer->writeSheetRow('Sheet1', array($v['item_no'],$v['item_name'],$v['item_desc'],$v['units'],$v['price'],
        ));
	 }
    
$writer->writeToStdOut();
//$writer->writeToFile('example.xlsx');
//echo $writer->writeToString();
exit(0);
?>
