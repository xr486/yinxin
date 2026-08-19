<?php
//  首先引入XLSXWriter包

putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");
ob_start();
include('includes/session2.inc');
include('includes/SQL_CommonFunctions.inc');
unset($result);
 

if (isset($_GET['order_number'])) {
    $order_number = $_GET['order_number'];
} else if (isset($_POST['order_number'])) {
    $order_number = $_POST['order_number'];
}
if (isset($_GET['SO_to'])) {
    $SO_to = $_GET['SO_to'];
} else if (isset($_POST['SO_to'])) {
    $SO_to = $_POST['SO_to'];
}
if (isset($_GET['customer_name'])) {
    $customer_name = $_GET['customer_name'];
} else if (isset($_POST['customer_name'])) {
    $customer_name = $_POST['customer_name'];
}
if (isset($_GET['customer_code'])) {
    $customer_code = $_GET['customer_code'];
} else if (isset($_POST['customer_code'])) {
    $customer_code = $_POST['customer_code'];
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
if (isset($_GET['subinventory_code'])) {
    $subinventory_code = $_GET['subinventory_code'];
} else if (isset($_POST['subinventory_code'])) {
    $subinventory_code = $_POST['subinventory_code'];
}
if (isset($_GET['FromDate'])) {
    $FromDate = $_GET['FromDate'];
} else if (isset($_POST['FromDate'])) {
    $FromDate = $_POST['FromDate'];
}
if (isset($_GET['ToDate'])) {
    $ToDate = $_GET['ToDate'];
} else if (isset($_POST['ToDate'])) {
    $ToDate = $_POST['ToDate'];
}
if (isset($_GET['FromDate2'])) {
    $FromDate2 = $_GET['FromDate2'];
} else if (isset($_POST['FromDate2'])) {
    $FromDate2 = $_POST['FromDate2'];
}
if (isset($_GET['ToDate2'])) {
    $ToDate2 = $_GET['ToDate2'];
} else if (isset($_POST['ToDate2'])) {
    $ToDate2 = $_POST['ToDate2'];
}
$sql ="SELECT  a.delivery_id,
                 d.delivery_date,
				 a.delivery_num,
                 a.delivery_line,
                 d.customer_code,
                 c.customer_name,
	             a.stockid,
				 b.item_desc,b.item_name,
			     a.uom ,
				 a.delivery_quantity,
			     a.price,
				 a.check_quantity,
                 a.check_price,
				 a.check_amount,
				 
				 a.check_date,a.subinventory_code
				 
		  from so_delivery_all a, 
	           sf_item_no b,
               customers c,
			   so_delivery_headers_all d
	     where a.stockid =b.item_no 
		   and d.customer_code = c.customer_code
		   and a.delivery_num  = d.delivery_num 
           and a.check_flag = 'Y' ";
           
    if(isset($order_number) and $order_number != ''){
        $sql = $sql . " and a.delivery_num " .LIKE." '%".$order_number."%' ";
    }
    if(isset($customer_name) and $customer_name != ''){
        $sql = $sql." and c.customer_name ".LIKE." '%".$customer_name."%' ";
    }
    if(isset($customer_code) and $customer_code != ''){
        $sql = $sql." and c.customer_code ".LIKE." '%".$customer_code."%' ";
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
    if(isset($subinventory_code) and $subinventory_code != ''){
        $sql = $sql." and a.subinventory_code ".LIKE." '%".$subinventory_code."%' ";
    }
	if(isset($FromDate) and $FromDate != ''){
        $sql = $sql." and d.delivery_date >=".strtotime($FromDate)." ";
    }
    if(isset($ToDate) and $ToDate != ''){
        $sql = $sql." and d.delivery_date <=".strtotime($ToDate)." ";
    }
    if(isset($FromDate2) and $FromDate2 != ''){
        $sql = $sql." and a.check_date >=".strtotime($FromDate2)." ";
    }
    if(isset($ToDate2) and $ToDate2 != ''){
        $sql = $sql." and a.check_date <=".strtotime($ToDate2)." ";
    }
  
    $result_num = DB_query($sql,$db);
//oci_execute($par);
include_once("xlsxwriter.class.php");
$date=date('YmdHis');
//ini_set('display_errors', 0);
//ini_set('log_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);

$filename = "销售对账明细".$date.".xlsx";
header('Content-disposition: attachment; filename="'.XLSXWriter::sanitize_filename($filename).'"');
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate');
header('Pragma: public');
$rows2 = array( 
  array('销售对账明细'),
);

$rows = array( 
  array('出货日期','出货单','行','客户','料号','料号名称','规格型号','单位','仓库','出货数量','出货单价','出货金额','对账数量',
        '对账单价','对账金额','对账日期'),
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
                $v_status = '已取消';
            }

     $writer->writeSheetRow('Sheet1', array(date('Y-m-d', $v['delivery_date']),$v['delivery_num'],$v['delivery_line'],
     $v['customer_code'],$v['stockid'],$v['item_name'],$v['item_desc'],$v['uom'],$v['subinventory_code'],$v['delivery_quantity'],
     $v['price'],$v['line_amount'],$v['check_quantity'],$v['check_price'],$v['check_amount'],
     date('Y-m-d', $v['check_date'])));
	 }
    
$writer->writeToStdOut();
//$writer->writeToFile('example.xlsx');
//echo $writer->writeToString();
exit(0);
?>
