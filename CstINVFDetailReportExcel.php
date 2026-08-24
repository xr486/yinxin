<?php
//  首先引入XLSXWriter包

putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");
ob_start();
include('includes/session2.inc');
include('includes/SQL_CommonFunctions.inc');
unset($result);
 
 $sql = "SELECT a.sub_code, 
                   c.item_no, c.item_name,c.item_desc,c.units,a.start_quantity,a.in_quantity,a.out_quantity,a.ym,a.end_quantity,a.cost_price
                    
            FROM cst_inv_yuejie_all a,sf_item_no c  
            WHERE   a.item_no = c.item_no 
			and c.item_type='F' ";
  
    
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
    if (isset($_GET['sub_code']) and $_GET['sub_code'] != '') {	
        $sql = $sql." and a.sub_code ".LIKE." '%".$_GET['sub_code']."%' "; 
    } 
	 
	$sql = $sql . " order by a.sub_code,c.item_no, c.item_name";
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
  array('年月','仓库','料号','料号名称','规格型号','期初数量','期初金额','入库数量','入库金额','出库数量','出库金额','期未数量','期未金额'),

);
$writer = new XLSXWriter();
$writer->setAuthor('Shunfansoft'); 

//$writer->writeSheetHeader('Sheet1', $header);
 foreach($rows2 as $row2)
	$writer->writeSheetRow('Sheet1', $row2);
foreach($rows as $row)
	$writer->writeSheetRow('Sheet1', $row);

	while ($v = DB_fetch_array($result_num)) {

		  
	 $writer->writeSheetRow('Sheet1', array($v['ym'],$v['sub_code'],$v['item_no'],$v['item_name'],$v['item_desc'],$v['start_quantity'],($v['start_quantity'] * $v['cost_price']),$v['in_quantity'],($v['in_quantity'] * $v['cost_price']),$v['out_quantity'],($v['out_quantity'] * $v['cost_price']),$v['end_quantity'],($v['end_quantity'] * $v['cost_price']),
        ));
	 }
    
$writer->writeToStdOut();
//$writer->writeToFile('example.xlsx');
//echo $writer->writeToString();
exit(0);
?>
