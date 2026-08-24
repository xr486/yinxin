<?php
//  首先引入XLSXWriter包

putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");
ob_start();
include('includes/session2.inc');
include('includes/SQL_CommonFunctions.inc');
unset($result);
 
$sql = "SELECT aaa.* from(SELECT a.last_update_date,a.subinventory_from,
            (SELECT ifnull(after_onhand,0) from inv_transactions_all aa where a.item_no = aa.item_no and a.subinventory_from = aa.subinventory_from";
             if (empty($_POST['ToDate']) == 0) 
             {
               $SQL_ToDate = strtotime($_POST['ToDate']) + 86400;
               $sql .= " and aa.last_update_date < '" . $SQL_ToDate . "' ";
             }
              $sql = $sql." order by aa.transaction_id desc limit 1 ) qimo_after_onhand, 
             (SELECT ifnull(after_amount,0) from inv_transactions_all aa where a.item_no = aa.item_no and a.subinventory_from = aa.subinventory_from";
             if (empty($_POST['ToDate']) == 0) 
             {
               $SQL_ToDate = strtotime($_POST['ToDate']) + 86400;
               $sql .= " and aa.last_update_date < '" . $SQL_ToDate . "' ";
             }
             $sql = $sql." order by aa.transaction_id desc limit 1 ) qimo_after_amount, 
             c.item_no, c.item_name , c.item_desc 
             FROM inv_transactions_all a,sf_item_no c WHERE a.item_no = c.item_no) aaa where aaa.qimo_after_onhand > 0 and aaa.subinventory_from in (SELECT loccode from locations) ";


if (isset($_GET['item_no']) and $_GET['item_no'] != '') { 		
$sql = $sql." and item_no ".LIKE." '%".$_GET['item_no']."%' ";
} 
if (isset($_GET['item_name']) and $_GET['item_name'] != '') { 		
$sql = $sql." and item_name ".LIKE." '%".$_GET['item_name']."%' ";
} 
if (isset($_GET['item_desc']) and $_GET['item_desc'] != '') { 		
$sql = $sql." and item_desc ".LIKE." '%".$_GET['item_desc']."%' ";
} 

if (isset($_GET['sub_code']) and $_GET['sub_code'] != ''  and $_GET['sub_code'] != '全部') {	
$sql = $sql." and subinventory_from ".LIKE." '%".$_GET['sub_code']."%' "; 
} 
if (empty($_GET['FromDate']) == 0) 
{
$SQL_FromDate = strtotime($_GET['FromDate']);
$sql .= " and last_update_date >= '" . $SQL_FromDate . "' ";
}
if (empty($_GET['ToDate']) == 0) 
{
$SQL_ToDate = strtotime($_GET['ToDate']) + 86400;
$sql .= " and last_update_date <='" . $SQL_ToDate . "' ";
}
$sql = $sql . " group by subinventory_from,item_no, item_name";
$sql = $sql . " order by last_update_date,item_no, item_name";
 $result_num = DB_query($sql, $db); 
//oci_execute($par);
include_once("xlsxwriter.class.php");
$date=date('YmdHis');
//ini_set('display_errors', 0);
//ini_set('log_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);

$filename = "历史库存金额报表".$date.".xlsx";
header('Content-disposition: attachment; filename="'.XLSXWriter::sanitize_filename($filename).'"');
header("Content-wip_entity_name: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate');
header('Pragma: public');
$rows2 = array( 
  array('历史库存金额报表'),

);
$rows = array( 
  array('仓库','料号','料号名称','规格型号','数量','金额'),

);
$writer = new XLSXWriter();
$writer->setAuthor('Shunfansoft'); 

//$writer->writeSheetHeader('Sheet1', $header);
 foreach($rows2 as $row2)
	$writer->writeSheetRow('Sheet1', $row2);
foreach($rows as $row)
	$writer->writeSheetRow('Sheet1', $row);

	while ($v = DB_fetch_array($result_num)) {

		  
	 $writer->writeSheetRow('Sheet1', array($v['subinventory_from'],$v['item_no'],$v['item_name'],$v['item_desc'],$v['qimo_after_onhand'],round($v['qimo_after_amount'],2)
        ));
	 }
    
$writer->writeToStdOut();
//$writer->writeToFile('example.xlsx');
//echo $writer->writeToString();
exit(0);
?>
