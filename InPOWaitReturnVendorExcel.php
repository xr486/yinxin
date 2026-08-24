<?php
//  首先引入XLSXWriter包

putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");
ob_start();
include('includes/session2.inc');
include('includes/SQL_CommonFunctions.inc');
unset($result);


 $sql ="select 
rh.receipt_num,rl.receipt_line,
a.po_num,
b.line,
b.stockid,
c.Item_name,
	c.item_desc,c.units,
a.vendor_code,
d.vendor_name,
ifnull(b.quantity,0) quantity,
ifnull(rl.quantity_received,0) this_received,
ifnull(rl.reject_area_quantity,0) wait_return_qty, 
ifnull(rl.inspection_bad_return_vendor,0) inspection_bad_return_qty, 
rl.subinventory_code,
b.need_date,rh.creation_date
FROM  po_headers_all a,
      po_lines_all b,
			po_rcv_receipt_header rh,
			po_rcv_receipt_line  rl,
                        sf_item_no c,vendors d
WHERE  a.vendor_code=d.vendor_code
and a.po_num=b.po_num    
and b.po_num=rl.po_num
and b.line=rl.po_line
and rl.receipt_num=rh.receipt_num
and b.stockid=c.item_no 
and ifnull(rl.reject_area_quantity,0)>0  ";
    
    if(isset($_GET['item_no']) and $_GET['item_no'] != ''){
        $sql = $sql." and  c.item_no ".LIKE." '%".$_GET['item_no']."%' ";
    }
	 if(isset($_GET['item_name']) and $_GET['item_name'] != ''){
        $sql = $sql." and  c.item_name ".LIKE." '%".$_GET['item_name']."%' ";
    }
	 
	if(isset($_GET['po_num']) and $_GET['po_num'] != ''){
        $sql = $sql." and  b.po_num ".LIKE." '%".$_GET['po_num']."%' ";
    }
   
	 
	if(isset($_GET['receipt_num']) and $_GET['receipt_num'] != ''){
        $sql = $sql." and  rh.receipt_num ".LIKE." '%".$_GET['receipt_num']."%' ";
    }
    if(isset($_GET['vendor_code']) and $_GET['vendor_code'] != ''){
        $sql = $sql." and d.vendor_code ".LIKE." '%".$_GET['vendor_code']."%' ";
    }
    if(isset($_GET['vendor_name']) and $_GET['vendor_name'] != ''){
        $sql = $sql." and d.vendor_name ".LIKE." '%".$_GET['vendor_name']."%' ";
    }
    if(isset($_GET['FromDate']) and $_GET['FromDate'] != ''){
        $sql = $sql." and rh.creation_date >=".strtotime($_GET['FromDate'])." ";
    }
     if(isset($_GET['ToDate']) and $_GET['ToDate'] != ''){
        $sql = $sql." and rh.creation_date <=".strtotime($_GET['ToDate'])." ";
    }

	$sql .= " order by  rh.creation_date  desc ";

 $result_num = DB_query($sql, $db); 
//oci_execute($par);
include_once("xlsxwriter.class.php");
$date=date('YmdHis');
//ini_set('display_errors', 0);
//ini_set('log_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);

$filename = "采购拒收未退厂商明细报表".$date.".xlsx";
header('Content-disposition: attachment; filename="'.XLSXWriter::sanitize_filename($filename).'"');
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate');
header('Pragma: public');
$rows2 = array( 
  array('采购拒收未退厂商明细报表'),

);
$rows = array( 
  array('来料报检单号', '行', '供应商编号', '采购单号' ,'行' , '料号', '料号名称', '规格型号', '单位', '采购数量', '来料报检量', '已退量' ,'待退厂商量','来料报检日期','仓库'),

); 

 
$writer = new XLSXWriter();
$writer->setAuthor('Shunfansoft'); 

//$writer->writeSheetHeader('Sheet1', $header);
 foreach($rows2 as $row2)
	$writer->writeSheetRow('Sheet1', $row2);
foreach($rows as $row)
	$writer->writeSheetRow('Sheet1', $row);

	while ($v = DB_fetch_array($result_num)) {

 
     $writer->writeSheetRow('Sheet1', array($v['receipt_num'],$v['receipt_line'],$v['vendor_code'],
     $v['po_num'],$v['line'],$v['stockid'],$v['item_name'],$v['item_desc'],$v['units'],
     $v['quantity'],$v['this_received'],$v['inspection_bad_return_qty'],$v['wait_return_qty'],date('Y-m-d',$v['creation_date']) ,$v['subinventory_code'] ));
	 }
    
$writer->writeToStdOut();
//$writer->writeToFile('example.xlsx');
//echo $writer->writeToString();
exit(0);
?>
