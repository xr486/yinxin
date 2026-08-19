<?php
//  首先引入XLSXWriter包

putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");
ob_start();
include('includes/session2.inc');
include('includes/SQL_CommonFunctions.inc');
unset($result);
 

$sql ="select a.*,(select ifnull(sum(quantity),0) from inv_onhand_quantity_all bb where bb.stockid=a.item_no ) onhand_quantity ,
(select type_name from sf_item_type b where a.item_type=b.item_type )  type_name
from sf_item_no a  where  1=1 " ;
if (isset($_GET['item_no']) and $_GET['item_no'] != '') {
        $sql = $sql . " and item_no like '%" . $_GET['item_no'] . "%' ";
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
    if(isset($_GET['locName'])and $_GET['locName'] != '' and $_GET['locName'] != '全部'){
      $sql = $sql." and a.sub_code ='".$_GET['locName']."'";
  }
 

$sql .=" order by item_no";
 $result_num = DB_query($sql, $db); 
//oci_execute($par);
include_once("xlsxwriter.class.php");
$date=date('YmdHis');
//ini_set('display_errors', 0);
//ini_set('log_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);

$filename = "料号明细报表".$date.".xlsx";
header('Content-disposition: attachment; filename="'.XLSXWriter::sanitize_filename($filename).'"');
header("Content-wip_entity_name: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate');
header('Pragma: public');
$rows2 = array( 
  array('料号明细报表'),

);
$rows = array( 
  array('料号','料号名称','规格型号','单位','最小订单量','安全水位低值','安全水位高值','生产周期','料号类型','产品分类','库位','库存'),

); 
 
$writer = new XLSXWriter();
$writer->setAuthor('Shunfansoft'); 

//$writer->writeSheetHeader('Sheet1', $header);
 foreach($rows2 as $row2)
	$writer->writeSheetRow('Sheet1', $row2);
foreach($rows as $row)
	$writer->writeSheetRow('Sheet1', $row);

	while ($v = DB_fetch_array($result_num)) {

		  
	 $writer->writeSheetRow('Sheet1', array($v['item_no'],$v['item_name'],$v['item_desc'],$v['units'],$v['min_order'],$v['safe_qty_min'],$v['safe_qty_max'],$v['manufacture_time'],
        $v['type_name'],$v['item_category1'],$v['sub_locator'],$v['onhand_quantity'],
        ));
	 }
    
$writer->writeToStdOut();
//$writer->writeToFile('example.xlsx');
//echo $writer->writeToString();
exit(0);
?>
