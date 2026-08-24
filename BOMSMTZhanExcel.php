<?php
//  首先引入XLSXWriter包

putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");
ob_start();
include('includes/session2.inc');
include('includes/SQL_CommonFunctions.inc');
unset($result);
 
 
$sql ="SELECT  *
FROM bom_smt_liaozhan 
WHERE 1=1  " ;

 
    if(isset( $_GET['item_no']) and  $_GET['item_no'] != ''){
        $sql = $sql." and  assembly_item_no ='". $_GET['item_no']."' ";
    }
     
    $sql = $sql." order by component_item";

    $result_num = DB_query($sql,$db);
//oci_execute($par);
include_once("xlsxwriter.class.php");
$date=date('YmdHis');
//ini_set('display_errors', 0);
//ini_set('log_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);

$filename = "SMT站别资料".$date.".xlsx";
header('Content-disposition: attachment; filename="'.XLSXWriter::sanitize_filename($filename).'"');
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate');
header('Pragma: public');
$rows2 = array( 
  array('SMT站别资料'),
);
 
$rows = array( 
  array('产品料号','料号名称','部件名称','优先生产','正反面','机器名','贴装台','地址','芯片名称','供料器名称','包装','使用数','贴装说明'),
); 
 
$writer = new XLSXWriter();
$writer->setAuthor('Shunfansoft'); 

//$writer->writeSheetHeader('Sheet1', $header);
 foreach($rows2 as $row2)
	$writer->writeSheetRow('Sheet1', $row2);
foreach($rows as $row)
	$writer->writeSheetRow('Sheet1', $row);

	while ($v = DB_fetch_array($result_num)) {

 

     $writer->writeSheetRow('Sheet1', array($v['assembly_item_no'],$v['assembly_item_name'],$v['youxian'],$v['youxian'],$v['mian'],
         $v['jiqiming'],$v['tiezhuangtai'],$v['address'],$v['xingpian_name'],$v['gongliaoqi_name'],
         $v['baozhuang'],
         $v['user_qty'],
         $v['weizhi'] ));
	 }
    
$writer->writeToStdOut();
//$writer->writeToFile('example.xlsx');
//echo $writer->writeToString();
exit(0);
?>
