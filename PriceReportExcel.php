<?php
//  首先引入XLSXWriter包

putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");
ob_start();
include('includes/session2.inc');
include('includes/SQL_CommonFunctions.inc');
unset($result);
 

$sql = "SELECT a.last_update_date,a.last_updated_by,a.po_num,a.status,a.item_no,b.item_name,a.price,a.last_price,b.item_desc,b.units
FROM po_vendor_price_line a, sf_item_no b
where a.item_no=b.item_no  
"	; 

if(isset($_GET['item_no']) and $_GET['item_no'] != ''){ 
    $sql = $sql." and a.item_no ".LIKE." '%".$_GET['item_no']."%' "; 
}

if(isset($_GET['item_name']) and $_GET['item_name'] != ''){ 
    $sql = $sql." and b.item_name ".LIKE." '%".$_GET['item_name']."%' "; 
}

if(isset($_GET['status']) and $_GET['status'] != ''){ 
    $sql = $sql." and a.status ".LIKE." '%".$_GET['status']."%' "; 
}

if(isset($_GET['po_num']) and $_GET['po_num'] != ''){ 
    $sql = $sql." and a.po_num ".LIKE." '%".$_GET['po_num']."%' "; 
}


 if (empty($_GET['FromDate']) == 0) {
    $SQL_FromDate = strtotime($_GET['FromDate']);
    $sql .= " and a.last_update_date >= '" . $SQL_FromDate . "' ";
}
if (empty($_GET['ToDate']) == 0) {
    $SQL_ToDate = strtotime($_GET['ToDate']) + 86400;
    //echo $SQL_ToDate;
    $sql .= " and a.last_update_date <='" . $SQL_ToDate . "' ";
}




$sql .= " order by a.last_update_date desc ";
$result = DB_query($sql,$db);
//oci_execute($par);
include_once("xlsxwriter.class.php");
$date=date('YmdHis');
//ini_set('display_errors', 0);
//ini_set('log_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);

$filename = "材料价格明细报表".$date.".xlsx";
header('Content-disposition: attachment; filename="'.XLSXWriter::sanitize_filename($filename).'"');
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate');
header('Pragma: public');
$rows2 = array( 
  array('材料价格明细报表'),

);

$rows = array( 
  array('申请单号','签核状态','料号','料号名称','规格型号','单位','上次单价','单价',
  '建立人员','生效日期'),

); 


$writer = new XLSXWriter();
$writer->setAuthor('Shunfansoft'); 

//$writer->writeSheetHeader('Sheet1', $header);
 foreach($rows2 as $row2)
	$writer->writeSheetRow('Sheet1', $row2);
foreach($rows as $row)
	$writer->writeSheetRow('Sheet1', $row);

	while ($v = DB_fetch_array($result)) {



     $writer->writeSheetRow('Sheet1', array($v['po_num'],$v['status'],$v['item_no'],
     $v['item_name'],$v['item_desc'],
     $v['units'],''.sprintf("%.2f",$v['last_price']), ''.sprintf("%.2f",$v['price']) ,$v['last_updated_by'],
     date('Y-m-d H:i:s', $v['last_update_date']) ));
} 
$writer->writeToStdOut();
//$writer->writeToFile('example.xlsx');
//echo $writer->writeToString();
exit(0);
?>
