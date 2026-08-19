<?php
//  首先引入XLSXWriter包

putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");
ob_start();
include('includes/session2.inc');
include('includes/SQL_CommonFunctions.inc');
unset($result);
 
$time =date("Y-m-d");

$sql = "SELECT a.trans_num,a.transaction_type,
c.item_no, a.uom, c.item_name,
a.subinventory_from,a.request_person,
d.loccode,   
a.request_person,(SELECT b.employee_name       
                  FROM hr_employees b
                  WHERE a.request_person = b.employee_num) 
employee_name, 
c.item_desc, a.quantity,
a.creation_date,a.remark,
a.transaction_date,(SELECT realname from www_users where userid=a.created_by) realname,
a.after_onhand,a.lot_num,a.shengchan_date
FROM inv_transactions_all a,sf_item_no c , 
locations d
WHERE   a.item_no = c.item_no 
AND a.subinventory_from = d.loccode and  a.transaction_type = '现场不合格' ";

if(isset($_GET['FromDate']) and $_GET['FromDate'] != ''){
    $sql = $sql." and a.transaction_date >=".strtotime($_GET['FromDate'])." ";
}
if(isset($_GET['ToDate']) and $_GET['ToDate'] != ''){
     $SQL_ToDate = strtotime($_GET['ToDate']) + 86400;
    $sql = $sql." and a.transaction_date <".$SQL_ToDate." ";
}
if (isset($_GET['item_no']) and $_GET['item_no'] != '') { 		
    $sql = $sql." and c.item_no ".LIKE." '%".$_GET['item_no']."%' ";
} 
if (isset($_GET['item_name']) and $_GET['item_name'] != '') { 		
    $sql = $sql." and c.item_name ".LIKE." '%".$_GET['item_name']."%' ";
} 
if (isset($_GET['item_desc']) and $_GET['item_desc'] != '') { 		
    $sql = $sql." and c.item_desc ".LIKE." '%".$_GET['item_desc']."%' ";
} 
if (isset($_GET['trans_num']) and $_GET['trans_num'] != '') { 		
    $sql = $sql." and a.trans_num ".LIKE." '%".$_GET['trans_num']."%' ";
} 
if (isset($_GET['loccode_from']) and $_GET['loccode_from'] != '') {	
    $sql = $sql." and a.subinventory_from ".LIKE." '%".$_GET['loccode_from']."%' "; 
} 
if (isset($_GET['sn']) and $_GET['sn'] != '') {	
    $sql = $sql." and a.sn ".LIKE." '%".$_GET['sn']."%' "; 
} 


$sql = $sql . " order by transaction_id desc ";

 $result_num = DB_query($sql, $db); 
//oci_execute($par);
include_once("xlsxwriter.class.php");
$date=date('YmdHis');
//ini_set('display_errors', 0);
//ini_set('log_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);

$filename = "采购不合格退货报表".$date.".xlsx";
header('Content-disposition: attachment; filename="'.XLSXWriter::sanitize_filename($filename).'"');
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate');
header('Pragma: public');
$rows2 = array( 
  array('采购不合格退货报表'),

);
$rows = array( 
  array('交易单号', '交易类型' ,'料号' ,'料号名称', '规格型号','批号','生产日期', '交易日期', '交易数量' , '单位',
   '仓库', '申请人工号' ,'申请人名称' ,  '备注'  ,'建立日期','建立人'),

); 

 
$writer = new XLSXWriter();
$writer->setAuthor('Shunfansoft'); 

//$writer->writeSheetHeader('Sheet1', $header);
 foreach($rows2 as $row2)
	$writer->writeSheetRow('Sheet1', $row2);
foreach($rows as $row)
	$writer->writeSheetRow('Sheet1', $row);

	while ($v = DB_fetch_array($result_num)) {

		 if ($v['status'] == 'INPROCESS') {
                $v_status = '待签核';
            } elseif ($v['status'] == 'APPROVED') {
                $v_status = '已签核';
            } elseif ($v['status'] == 'REJECTED') {
                $v_status = '已拒签';
            } else {
                $v_status = $v['status'];
            }
       
     $writer->writeSheetRow('Sheet1', array($v['trans_num'],$v['transaction_type'],
     $v['item_no'],
     $v['item_name'],$v['item_desc'],$v['lot_num'],$v['shengchan_date'],date('Y-m-d',$v['transaction_date']),$v['quantity'],$v['uom'],$v['loccode'],$v['request_person'],
     $v['employee_name'], $v['remark'],date('Y-m-d H:i:s',$v['creation_date']), $v['realname'] ));
	 }
    
$writer->writeToStdOut();
//$writer->writeToFile('example.xlsx');
//echo $writer->writeToString();
exit(0);
?>
