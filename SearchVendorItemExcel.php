<?php
//  首先引入XLSXWriter包

putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");
ob_start();
include('includes/session2.inc');
include('includes/SQL_CommonFunctions.inc');
unset($result);
 

 $sql = 'select a.vendor_code,a.vendor_name,b.remark,c.item_no,c.item_name,c.item_desc,b.enable_flag,b.item_relation_id 
	from vendors a,vendor_item_relation b,sf_item_no c  where a.vendor_code=b.vendor_code
	and b.item_no=c.item_no ';
    if(isset($_GET['VendorCode']) and $_GET['VendorCode'] != ''){
        $sql = $sql." and a.vendor_code ".LIKE." '%".$_GET['VendorCode']."%' ";
    }
    if(isset($_GET['VendorName']) and $_GET['VendorName'] != ''){
        $sql = $sql." and a.vendor_name ".LIKE." '%".$_GET['VendorName']."%' ";
    }
    if(isset($_GET['item_no']) and $_GET['item_no'] != ''){
        $sql = $sql." and c.item_no ".LIKE." '%".$_GET['item_no']."%' ";
    }
   
	if(isset($_GET['item_name']) and $_GET['item_name'] != ''){
        $sql = $sql." and item_name ".LIKE." '%".$_GET['item_name']."%' ";
    }
    if(isset($_GET['item_desc']) and $_GET['item_desc'] != ''){
        $sql = $sql." and item_desc ".LIKE." '%".$_GET['item_desc']."%' ";
    }
    if(isset($_GET['enable_flag']) and $_GET['enable_flag'] != ''){
        $sql = $sql." and b.enable_flag='".$_GET['enable_flag']."'";
    }
	 
 

// $sql .=" order by vendor_code";
 $result_num = DB_query($sql, $db); 
//oci_execute($par);
include_once("xlsxwriter.class.php");
$date=date('YmdHis');
//ini_set('display_errors', 0);
//ini_set('log_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);

$filename = "供应商料号关系明细报表".$date.".xlsx";
header('Content-disposition: attachment; filename="'.XLSXWriter::sanitize_filename($filename).'"');
header("Content-wip_entity_name: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate');
header('Pragma: public');
$rows2 = array( 
  array('供应商料号关系明细报表'),

);
$rows = array( 
  array('供应商简称','供应商全称','料号','料号名称','规格型号','是否生效'),

); 
 
$writer = new XLSXWriter();
$writer->setAuthor('Shunfansoft'); 

//$writer->writeSheetHeader('Sheet1', $header);
 foreach($rows2 as $row2)
	$writer->writeSheetRow('Sheet1', $row2);
foreach($rows as $row)
	$writer->writeSheetRow('Sheet1', $row);

	while ($v = DB_fetch_array($result_num)) {
		  
	 $writer->writeSheetRow('Sheet1', array($v['vendor_code'],$v['vendor_name'],$v['item_no'],$v['item_name'],$v['item_desc'],$v['enable_flag']
        ));
	 }
   
$writer->writeToStdOut();
//$writer->writeToFile('example.xlsx');
//echo $writer->writeToString();
exit(0);
?>
