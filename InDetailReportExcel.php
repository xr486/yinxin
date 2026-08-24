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
a.transaction_date,a.created_by ,
a.after_onhand,a.lot_num,a.shengchan_date,e.realname,a.price
FROM inv_transactions_all a,sf_item_no c , 
locations d,www_users e
WHERE   a.item_no = c.item_no 
AND a.subinventory_from = d.loccode  and a.quantity > 0 and e.userid = a.created_by ";

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
if (isset($_GET['transaction_type']) and $_GET['transaction_type'] != '') {
    $sql = $sql . " and  a.transaction_type =  '" . $_GET['transaction_type'] . "' ";
} 
 

$sql = $sql . " order by transaction_id desc ";

 $result_num = DB_query($sql, $db); 
//oci_execute($par);
include_once("xlsxwriter.class.php");
$date=date('YmdHis');
//ini_set('display_errors', 0);
//ini_set('log_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);

$filename = "入库明细表".$date.".xlsx";
header('Content-disposition: attachment; filename="'.XLSXWriter::sanitize_filename($filename).'"');
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate');
header('Pragma: public');
$rows2 = array( 
  array('入库明细表'),

);
$rows = array( 
  array('入库日期','料号' ,'料号名称', '规格型号','单位','入库数量' ,'单价', '入库金额','需求人', '批号/SN号','仓库', '备注'  ,'建立日期', '交易单号', '交易类型' ),

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

        //     $price=0;
        //     $sql2="select a.price,b.tax_flag, b.tax_rate from po_lines_all a ,po_headers_all b where 
        //          po_line_id in (select max(po_line_id) 
        //           from po_headers_all ph, po_lines_all pl 
        //           where ph.status<>'已取消'
        //           and ph.po_num=pl.po_num and pl.status<>'已取消' and pl.stockid='" . $v['item_no'] . "'  ) and a.po_num=b.po_num ";
        //       $result2 = DB_query($sql2,$db);
        //      // echo $sql2;
        //   while ($myrow2 = DB_fetch_array($result2)) {
        //                      if($myrow2['tax_flag'] == 'Y') {
        //                              $price=$myrow2['price']/(1+$myrow2['tax_rate']);
        //                      }else{
                                     
        //                              $price=$myrow2['price']; 
        //                      }
        //   }
       
     $writer->writeSheetRow('Sheet1', array(date('Y-m-d H:i:s',$v['transaction_date']),
     $v['item_no'],$v['item_name'],$v['item_desc'],$v['uom'],$v['quantity'],round($v['price'],6),round($v['price'] * $v['quantity'],2 ),$v['realname'],$v['lot_num'],$v['loccode'],$v['remark'],date('Y-m-d',$v['creation_date']),$v['trans_num'],$v['transaction_type'] ));
	 }
    
$writer->writeToStdOut();
//$writer->writeToFile('example.xlsx');
//echo $writer->writeToString();
exit(0);
?>
