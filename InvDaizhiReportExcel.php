<?php
//  首先引入XLSXWriter包

putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");
ob_start();
include('includes/session2.inc');
include('includes/SQL_CommonFunctions.inc');
unset($result);

$sql = 'select b.item_no,
                    b.safe_qty,
                    b.min_qty,
                    b.max_qty,
                    b.item_desc,
                    b.item_name,
                    b.units, 
            sum(a.quantity ) quantity ,
                    a.subinventory_code,(select  price from po_lines_all c where c.stockid=b.item_no 
					and po_line_id in (select max(po_line_id) from po_lines_all d where d.stockid=b.item_no  ) ) price
            from inv_onhand_quantity_all a,
                    sf_item_no b
            where a.stockid=b.item_no 
			and a.quantity>0 ';

    if (isset($_GET['item_no']) and $_GET['item_no'] != '') { 
		$sql = $sql." and b.item_no ".LIKE." '%".$_GET['item_no']."%' ";
    }
    
	 if ($_SESSION['UserID']=='CZCK') {
     $sql = $sql." and a.subinventory_code  like '常州%'";
   }

    if(isset($_GET['locName'])and $_GET['locName'] != '' and $_GET['locName'] != '全部'){
        $sql = $sql." and a.subinventory_code ='".$_GET['locName']."'";
    }
     if(isset($_GET['item_name']) and $_GET['item_name'] != ''){
        $sql = $sql." and b.item_name ".LIKE." '%".$_GET['item_name']."%' ";
     }
	 if(isset($_GET['item_desc']) and $_GET['item_desc'] != ''){
        $sql = $sql." and b.item_desc ".LIKE." '%".$_GET['item_desc']."%' ";
     }

	 if (empty($_GET['FromDate']) == 0) {
        $SQL_FromDate = strtotime($_GET['FromDate']);
        $sql .= " and b.item_no not in (select item_no from inv_transactions_all ita where  
		  ita.transaction_date >= '" . $SQL_FromDate . "'";
		if(isset($_GET['locName'])and $_GET['locName'] != '' and $_GET['locName'] != '全部'){
        $sql = $sql." and ita.subinventory_from ='".$_GET['locName']."'";
        }

		if(isset($_GET['txn_type']) and $_GET['txn_type'] != ''){
        $sql = $sql." and ita.transaction_type ='".$_GET['txn_type']."' ";
     }

		 $sql .= " ) ";
    }
  
  $_SESSION['locName' . $identifier]=$_GET['locName'];

  $sql = $sql."GROUP BY a.subinventory_code, b.item_no,b.units,b.safe_qty,b.min_qty,b.max_qty,b.item_name,b.item_desc";
   //echo $sql;
    $result = DB_query($sql,$db);
//oci_execute($par);
include_once("xlsxwriter.class.php");
$date = date('YmdHis');
//ini_set('display_errors', 0);
//ini_set('log_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);

$filename = "呆滞料明细报表" . $date . ".xlsx";
header('Content-disposition: attachment; filename="' . XLSXWriter::sanitize_filename($filename) . '"');
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate');
header('Pragma: public');
$rows2 = array(
    array('呆滞料明细报表'),
);

$rows = array(
    array(
        '仓库', '料号', '料号名称', '规格型号', '单位', '库存数量', '单价', '金额' 
    ),
);

$writer = new XLSXWriter();
$writer->setAuthor('Shunfansoft');

//$writer->writeSheetHeader('Sheet1', $header);
foreach ($rows2 as $row2)
    $writer->writeSheetRow('Sheet1', $row2);
foreach ($rows as $row)
    $writer->writeSheetRow('Sheet1', $row);

while ($v = DB_fetch_array($result)) {


    $writer->writeSheetRow('Sheet1', array(
        $v['subinventory_code'], $v['item_no'], $v['item_name'], $v['item_desc'], $v['units'], $v['quantity'],
        $v['price'], ($v['quantity']*$v['price'])  
    ));
}

$writer->writeToStdOut();
//$writer->writeToFile('example.xlsx');
//echo $writer->writeToString();
exit(0);
