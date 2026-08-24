<?php
//  首先引入XLSXWriter包

putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");
ob_start();
include('includes/session2.inc');
include('includes/SQL_CommonFunctions.inc');
unset($result);
 
$time =date("Y-m-d");


$sql = "SELECT prt.receipt_num,prr.receipt_line,a.po_num,b.line,b.stockid, c.item_name,c.item_desc, c.units, a.vendor_code, d.vendor_name, prt.transaction_quantity, prt.transaction_type, prr.subinventory_code, b.need_date, prt.transaction_date,prt.created_by, b.price,b.line_amount,a.tax_name,a.tax_rate,(select realname from www_users where userid=prt.created_by) realname,a.tax_flag,prt.delivery_num,b.quantity
FROM po_headers_all a, po_lines_all b, sf_item_no c, vendors d, po_rcv_transactions prt,po_rcv_receipt_line prr
WHERE  a.po_num = b.po_num
AND a.vendor_code = d.vendor_code
AND b.po_num = prt.po_num
and prr.receipt_num=prt.receipt_num
and prt.po_num=prr.po_num
and prt.po_line=prr.po_line 
and b.stockid=prr.stockid
and  prt.transaction_type in ('POIN')
AND b.line = prt.po_line
AND b.stockid = c.item_no  ";

if(isset($_GET['vendor_code']) and $_GET['vendor_code'] != ''){
        $sql = $sql." and d.vendor_code ".LIKE." '%".$_GET['vendor_code']."%' ";
    }
    if(isset($_GET['vendor_name']) and $_GET['vendor_name'] != ''){
        $sql = $sql." and d.vendor_name ".LIKE." '%".$_GET['vendor_name']."%' ";
    }
    if(isset($_GET['FromDate']) and $_GET['FromDate'] != ''){
        $sql = $sql." and prt.transaction_date >=".strtotime($_GET['FromDate'])." ";
    }
     if(isset($_GET['ToDate']) and $_GET['ToDate'] != ''){
        $sql = $sql." and prt.transaction_date <=".strtotime($_GET['ToDate'])." ";
    }
	if (isset($_GET['item_no']) and $_GET['item_no'] != '') { 
        $sql = $sql." and prt.stockid ".LIKE." '%".$_GET['item_no']."%' ";
    }
    if (isset($_GET['item_name']) and $_GET['item_name'] != '') { 
        $sql = $sql." and c.item_name ".LIKE." '%".$_GET['item_name']."%' ";
    }
	if (isset($_GET['item_desc']) and $_GET['item_desc'] != '') { 
        $sql = $sql." and c.item_desc ".LIKE." '%".$_GET['item_desc']."%' ";
    }
    if (isset($_GET['receipt_num']) and $_GET['receipt_num'] != '') {
        $sql = $sql . " and prt.receipt_num ".LIKE." '%".$_GET['receipt_num']."%' ";
    }
     

	if (isset($_GET['po_num']) and $_GET['po_num'] != '') {
        $sql = $sql . " and a.po_num  ".LIKE." '%".$_GET['po_num']."%' ";
    }
    if (isset($_GET['sub_code']) and $_GET['sub_code'] != '') { 
        $sql = $sql." and prr.subinventory_code ".LIKE." '%".$_GET['sub_code']."%' ";
    }
 

$sql .=" order by prt.transaction_date desc, prr.receipt_num, prr.receipt_line ,prr.po_num, prr.po_line ";
 $result_num = DB_query($sql, $db); 
//oci_execute($par);
include_once("xlsxwriter.class.php");
$date=date('YmdHis');
//ini_set('display_errors', 0);
//ini_set('log_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);

$filename = "采购入库明细报表".$date.".xlsx";
header('Content-disposition: attachment; filename="'.XLSXWriter::sanitize_filename($filename).'"');
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate');
header('Pragma: public');
$rows2 = array( 
  array('采购入库明细报表'),

);

if ($_SESSION['price_flag']=='N') {
	$rows = array( 
  array('入库单号','来料报检单号','行','供应商编码','供应商名称','采购单号','行','料号','料号名称','规格型号','单位','仓库','入库数量','单价','未税金额','含税金额','税率','入库日期','入库人员'),

); 


} else {
$rows = array( 
  array('入库单号','来料报检单号','行','供应商编码','供应商名称','采购单号','行','料号','料号名称','规格型号','单位','仓库','入库数量','税率','入库日期','入库人员'),

); 

}

 
$writer = new XLSXWriter();
$writer->setAuthor('Shunfansoft'); 

//$writer->writeSheetHeader('Sheet1', $header);
 foreach($rows2 as $row2)
	$writer->writeSheetRow('Sheet1', $row2);
foreach($rows as $row)
	$writer->writeSheetRow('Sheet1', $row);

	while ($v = DB_fetch_array($result_num)) {
     if ($_SESSION['price_flag']=='N') {
        
        if($v['tax_flag']=='Y'){


            $writer->writeSheetRow('Sheet1', array($v['delivery_num'],$v['receipt_num'],$v['receipt_line'],$v['vendor_code'],$v['vendor_name'],$v['po_num'],$v['line'],$v['stockid'],$v['item_name'],
            $v['item_desc'],$v['units'],$v['subinventory_code'],$v['transaction_quantity'],$v['price'],(round(($v['price'] * $v['transaction_quantity'])/(1+$v['tax_rate']),2)),(round(($v['price'] * $v['transaction_quantity']),2)),$v['tax_name'],date('Y-m-d',$v['transaction_date']),$v['realname'] ));
          }else{
            
            $writer->writeSheetRow('Sheet1', array($v['delivery_num'],$v['receipt_num'],$v['receipt_line'],$v['vendor_code'],$v['vendor_name'],$v['po_num'],$v['line'],$v['stockid'],$v['item_name'],
            $v['item_desc'],$v['units'],$v['subinventory_code'],$v['transaction_quantity'],$v['price'],(round(($v['price'] * $v['transaction_quantity']),2)),(round(($v['price'] * $v['transaction_quantity'])*(1+$v['tax_rate']),2)),$v['tax_name'],date('Y-m-d',$v['transaction_date']),$v['realname'] ));
          }
          
	
	 } else {
	  $writer->writeSheetRow('Sheet1', array($v['delivery_num'],$v['receipt_num'],$v['receipt_line'],$v['vendor_code'],$v['vendor_name'],$v['po_num'],$v['line'],$v['stockid'],$v['item_name'],
		 $v['item_desc'],$v['units'],$v['subinventory_code'],$v['transaction_quantity'],$v['tax_name'],date('Y-m-d',$v['transaction_date']),$v['realname'] ));
	 
	 }
	}
    
	

$writer->writeToStdOut();
//$writer->writeToFile('example.xlsx');
//echo $writer->writeToString();
exit(0);
?>
