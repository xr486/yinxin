<?php
//  首先引入XLSXWriter包

putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");
ob_start();
include('includes/session2.inc');
include('includes/SQL_CommonFunctions.inc');
unset($result);


$sql = "SELECT a.transaction_type,c.item_no, 
a.uom, a.subinventory_from,c.item_name,c.item_desc,
 sum(a.quantity) quantity
FROM inv_transactions_all a, sf_item_no c
WHERE a.item_no = c.item_no ";

if(isset($_GET['FromDate']) and $_GET['FromDate'] != ''){
    $sql = $sql." and a.transaction_date >=".strtotime($_GET['FromDate'])." ";
}
 if(isset($_GET['ToDate']) and $_GET['ToDate'] != ''){
    $sql = $sql." and a.transaction_date <=".strtotime($_GET['ToDate'])." ";
}
if (isset($_GET['item_no']) and $_GET['item_no'] != '') { 		
    $sql = $sql." and c.item_no ".LIKE." '%".$_GET['item_no']."%' ";
} 
if (isset($_GET['item_name']) and $_GET['item_name'] != '') { 		
    $sql = $sql." and c.item_name ".LIKE." '%".$_GET['item_name']."%' ";
} 

if (isset($_GET['loccode_from']) and $_GET['loccode_from'] != '' and $_GET['loccode_from'] != '全部') {
    
    $sql = $sql." and a.subinventory_from ".LIKE." '%".$_GET['loccode_from']."%' "; 
} 
if (isset($_GET['transaction_type']) and $_GET['transaction_type'] != '') {
    $sql = $sql . " and  a.transaction_type =  '" . $_GET['transaction_type'] . "' ";
}

 $sql = $sql . " group by a.transaction_type,c.item_no, a.uom, a.subinventory_from,  c.item_name,c.item_desc ";


$result = DB_query($sql,$db);
//oci_execute($par);
include_once("xlsxwriter.class.php");
$date = date('YmdHis');
//ini_set('display_errors', 0);
//ini_set('log_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);

$filename = "库存交易汇总表" . $date . ".xlsx";
header('Content-disposition: attachment; filename="' . XLSXWriter::sanitize_filename($filename) . '"');
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate');
header('Pragma: public');
$rows2 = array(
    array('库存交易汇总表'),
);

$rows = array(
    array(
        '交易类型', '料号' , '料号名称' , '规格型号' , '交易数量' ,'单位'  ,'仓库'  
    ),
);

$writer = new XLSXWriter();
$writer->setAuthor('Shunfansoft');

//$writer->writeSheetHeader('Sheet1', $header);
foreach ($rows2 as $row2)
    $writer->writeSheetRow('Sheet1', $row2);
foreach ($rows as $row)
    $writer->writeSheetRow('Sheet1', $row);

while ($v = DB_fetch_array($result)) {


    $writer->writeSheetRow('Sheet1', array(
        $v['transaction_type'], $v['item_no'], $v['item_name'], $v['item_desc'], $v['quantity'], $v['uom'],
        $v['subinventory_from']
    ));
}

$writer->writeToStdOut();
//$writer->writeToFile('example.xlsx');
//echo $writer->writeToString();
exit(0);
