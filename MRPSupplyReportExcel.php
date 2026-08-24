<?php
//  首先引入XLSXWriter包

putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");
ob_start();
include('includes/session2.inc');
include('includes/SQL_CommonFunctions.inc');
unset($result);
 
 $sql = " select  c.item_no,c.item_desc,c.item_name,c.units,type,source,quantity
						from wip_inv_onhand_detail_temp a,sf_item_no c  
                        where  a.stockid=c.item_no   ";

    
     if (isset($_POST['type']) and $_POST['type'] != '') { 
		$sql = $sql . " and a.type " . LIKE . " '%" . $_POST['type'] . "%' ";
    }
	  if (isset($_POST['source']) and $_POST['source'] != '') { 
		$sql = $sql . " and a.source " . LIKE . " '%" . $_POST['source'] . "%' ";
    }
 
    if (isset($_POST['item_no']) and $_POST['item_no'] != '') {
        $sql = $sql . " and c.item_no " . LIKE . " '%" . $_POST['item_no'] . "%' ";
    }
	 if (isset($_POST['item_name']) and $_POST['item_name'] != '') {
        $sql = $sql . " and c.item_name " . LIKE . " '%" . $_POST['item_name'] . "%' ";
    }
	if (isset($_POST['item_desc']) and $_POST['item_desc'] != '') {
        $sql = $sql . " and c.item_desc " . LIKE . " '%" . $_POST['item_desc'] . "%' ";
    }
 

	 $sql .= " order by c.item_no,c.item_desc,c.item_name "; 


 $result_num = DB_query($sql, $db); 
//oci_execute($par);
include_once("xlsxwriter.class.php");
$date=date('YmdHis');
//ini_set('display_errors', 0);
//ini_set('log_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);

$filename = "供给明细报表".$date.".xlsx";
header('Content-disposition: attachment; filename="'.XLSXWriter::sanitize_filename($filename).'"');
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate');
header('Pragma: public');
$rows2 = array( 
  array('供给明细报表'),

);
$rows = array( 
  array('类型','来源','料号' ,'料号名称', '规格型号','单位', '供给数量'),

); 
 
 
$writer = new XLSXWriter();
$writer->setAuthor('Shunfansoft'); 

//$writer->writeSheetHeader('Sheet1', $header);
 foreach($rows2 as $row2)
	$writer->writeSheetRow('Sheet1', $row2);
foreach($rows as $row)
	$writer->writeSheetRow('Sheet1', $row);

	while ($v = DB_fetch_array($result_num)) {
 
       
     $writer->writeSheetRow('Sheet1', array($v['type'],$v['source'],
     $v['item_no'],
     $v['item_name'],$v['item_desc'],$v['units'].' ',$v['quantity']  ));
	 }
       
$writer->writeToStdOut();
//$writer->writeToFile('example.xlsx');
//echo $writer->writeToString();
exit(0);
?>
