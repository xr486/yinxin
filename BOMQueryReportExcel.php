<?php
//  首先引入XLSXWriter包
putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");
ob_start();
include('includes/session2.inc');
include('includes/SQL_CommonFunctions.inc');
unset($result);
 

 $sql = "SELECT a.bom_header_id,a.version,a.assembly_item_no,b.item_name,b.item_desc,a.creation_date,a.created_by,item_category1 
	FROM bom_headers_all a,
	 sf_item_no b
WHERE	1 = 1
AND b.item_no = a.assembly_item_no
";

    if (isset($_GET['item_no']) and $_GET['item_no'] != '') {
		$sql = $sql." and b.item_no like '%".$_GET['item_no']."%' ";
    }
    if (isset($_GET['item_name']) and $_GET['item_name'] != '') {
       $sql = $sql." and b.item_name like '%".$_GET['item_name']."%' ";
    }
	  if (isset($_GET['item_category1']) and $_GET['item_category1'] != '') {
       $sql = $sql." and b.item_category1 like '%".$_GET['item_category1']."%' ";
    }
	 if (isset($_GET['item_desc']) and $_GET['item_desc'] != '') {
       $sql = $sql." and b.item_desc like '%".$_GET['item_desc']."%' ";
    }
  
    if (empty($_GET['FromDate']) == 0) {
        $SQL_FromDate = strtotime($_GET['FromDate']);
        $sql .= " and a.creation_date >= '" . $SQL_FromDate . "' ";
    }
    if (empty($_GET['ToDate']) == 0) {
        $SQL_ToDate = strtotime($_GET['ToDate']) + 86400;
        //echo $SQL_ToDate;
        $sql .= " and a.creation_date <='" . $SQL_ToDate . "' ";
    }
       
      $sql .=" order by b.item_no";
   
	$result_num = DB_query($sql,$db);
//oci_execute($par);
include_once("xlsxwriter.class.php");
$date=date('YmdHis');
//ini_set('display_errors', 0);
//ini_set('log_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);

$filename = "BOM明细".$date.".xlsx";
header('Content-disposition: attachment; filename="'.XLSXWriter::sanitize_filename($filename).'"');
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate');
header('Pragma: public');
$rows2 = array( 
  array('BOM明细'),
);
 
 
	$rows = array( 
  array('料号','料号名称','规格型号','版本','分类','建立日期','建立人员'),
);
    
 
$writer = new XLSXWriter();
$writer->setAuthor('Shunfansoft'); 

//$writer->writeSheetHeader('Sheet1', $header);
 foreach($rows2 as $row2)
	$writer->writeSheetRow('Sheet1', $row2);
foreach($rows as $row)
	$writer->writeSheetRow('Sheet1', $row);

	while ($v = DB_fetch_array($result_num)) {
        if ($v['quantity'] > 0) {
            $hanshui_amount = ($v['line_amount'] + $v['tax_amount']) / $v['quantity'];
        } else {
            $hanshui_amount = 0;
        }
	 
     $writer->writeSheetRow('Sheet1', array($v['assembly_item_no'],$v['item_name'],$v['item_desc'],$v['version'],$v['item_category1'],date('Y-m-d', $v['creation_date']),
        $v['created_by'],
        ));
	  

	 }
      
$writer->writeToStdOut();
//$writer->writeToFile('example.xlsx');
//echo $writer->writeToString();
exit(0);
?>
