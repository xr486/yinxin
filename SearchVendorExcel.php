<?php
//  首先引入XLSXWriter包

putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");
ob_start();
include('includes/session2.inc');
include('includes/SQL_CommonFunctions.inc');
unset($result);
 

 $sql = 'select vendor_code,vendor_name,vendor_contacts,contacts_phone,contacts_mail'
            . ' from vendors where 1=1';
    if(isset($_GET['vendor_code']) and $_GET['vendor_code'] != ''){
        $sql = $sql." and vendor_code ".LIKE." '%".$_GET['vendor_code']."%' ";
    }
    if(isset($_GET['vendor_name']) and $_GET['vendor_name'] != ''){
        $sql = $sql." and vendor_name ".LIKE." '%".$_GET['vendor_name']."%' ";
    }
    if(isset($_GET['vendor_contacts']) and $_GET['vendor_contacts'] != ''){
        $sql = $sql." and vendor_contacts ".LIKE." '%".$_GET['vendor_contacts']."%' ";
    }
   
	if(isset($_GET['contacts_phone']) and $_GET['contacts_phone'] != ''){
        $sql = $sql." and contacts_phone ".LIKE." '%".$_GET['contacts_phone']."%' ";
    }
	 
 

$sql .=" order by vendor_code";
 $result_num = DB_query($sql, $db); 
//oci_execute($par);
include_once("xlsxwriter.class.php");
$date=date('YmdHis');
//ini_set('display_errors', 0);
//ini_set('log_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);

$filename = "供应商明细报表".$date.".xlsx";
header('Content-disposition: attachment; filename="'.XLSXWriter::sanitize_filename($filename).'"');
header("Content-wip_entity_name: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate');
header('Pragma: public');
$rows2 = array( 
  array('供应商明细报表'),

);
$rows = array( 
  array('供应商代码','供应商名称','联系人','电话','邮箱'),

); 
 
$writer = new XLSXWriter();
$writer->setAuthor('Shunfansoft'); 

//$writer->writeSheetHeader('Sheet1', $header);
 foreach($rows2 as $row2)
	$writer->writeSheetRow('Sheet1', $row2);
foreach($rows as $row)
	$writer->writeSheetRow('Sheet1', $row);

	while ($v = DB_fetch_array($result_num)) {

		  
	 $writer->writeSheetRow('Sheet1', array($v['vendor_code'],$v['vendor_name'],$v['vendor_contacts'],$v['contacts_phone'],$v['contacts_mail'],
        ));
	 }
   
$writer->writeToStdOut();
//$writer->writeToFile('example.xlsx');
//echo $writer->writeToString();
exit(0);
?>
