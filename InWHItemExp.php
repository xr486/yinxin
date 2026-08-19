<?php
//  首先引入XLSXWriter包

putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");
ob_start();
include('includes/session2.inc');
include('includes/SQL_CommonFunctions.inc');
unset($result);
 

if (isset($_GET['subinventory_code'])) {
    $subinventory_code = $_GET['subinventory_code'];
} else if (isset($_POST['subinventory_code'])) {
    $subinventory_code = $_POST['subinventory_code'];
} 
if (isset($_GET['item_no'])) {
    $item_no = $_GET['item_no'];
} else if (isset($_POST['item_no'])) {
    $item_no = $_POST['item_no'];
}
if (isset($_GET['item_name'])) {
    $item_name = $_GET['item_name'];
} else if (isset($_POST['item_name'])) {
    $item_name = $_POST['item_name'];
}
if (isset($_GET['item_desc'])) {
    $item_desc = $_GET['item_desc'];
} else if (isset($_POST['item_desc'])) {
    $item_desc = $_POST['item_desc'];
}
 
 
$sql ="select b.item_no,
                    b.safe_qty,
                    b.min_qty,
                    b.max_qty,
                    b.item_desc,
                    b.item_name,
                    b.units, 
            sum(a.quantity ) quantity ,
                    a.subinventory_code
            from inv_onhand_quantity_all a,
                    sf_item_no b
            where a.stockid=b.item_no	  ";
           
   if(isset($subinventory_code )and $subinventory_code != '' and $subinventory_code != '全部'){
        $sql = $sql." and a.subinventory_code ='".$subinventory_code."'";
    }
    if(isset($item_no) and $item_no != ''){
        $sql = $sql." and b.item_no ".LIKE." '%".$item_no."%' ";
    }
    if(isset($item_name) and $item_name != ''){
        $sql = $sql." and b.item_name ".LIKE." '%".$item_name."%' ";
    }
    if(isset($item_desc) and $item_desc != ''){
        $sql = $sql." and b.item_desc ".LIKE." '%".$item_desc."%' ";
    }
	 $sql = $sql."GROUP BY a.subinventory_code, b.item_no,b.units,b.safe_qty,b.min_qty,b.max_qty,b.item_name,b.item_desc";
    
	// echo $sql;
    $result_num = DB_query($sql,$db);
//oci_execute($par);
include_once("xlsxwriter.class.php");
$date=date('YmdHis');
//ini_set('display_errors', 0);
//ini_set('log_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);

$filename = "仓库库存查询".$date.".xlsx";
header('Content-disposition: attachment; filename="'.XLSXWriter::sanitize_filename($filename).'"');
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate');
header('Pragma: public');
$rows2 = array( 
  array('仓库库存查询'),
);

$rows = array( 
  array('仓库','料号','料号名称','规格型号','单位','库存数量','安全库存',
        '最低库存','最高库存' ),
); 
  
$writer = new XLSXWriter();
$writer->setAuthor('Shunfansoft'); 

//$writer->writeSheetHeader('Sheet1', $header);
 foreach($rows2 as $row2)
	$writer->writeSheetRow('Sheet1', $row2);
foreach($rows as $row)
	$writer->writeSheetRow('Sheet1', $row);

	while ($v = DB_fetch_array($result_num)) {

		 
     $writer->writeSheetRow('Sheet1', array($v['subinventory_code'],$v['item_no'],$v['item_name'],$v['item_desc'],$v['units'],$v['quantity'],
     $v['safe_qty'],$v['min_qty'],$v['max_qty'] ));
	 }
        
$writer->writeToStdOut();
//$writer->writeToFile('example.xlsx');
//echo $writer->writeToString();
exit(0);
?>
