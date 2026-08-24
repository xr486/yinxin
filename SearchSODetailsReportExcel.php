<?php
//  首先引入XLSXWriter包
putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");
ob_start();
include('includes/session2.inc');
include('includes/SQL_CommonFunctions.inc');
unset($result);
 
 
$sql = "SELECT
       s.order_number,s.line,s.stockid,s.uom,s.quantity,s.price tax_price,(s.price/(1+h.tax_rate)) no_tax_price, s.quantity_shiped,s.line_amount tax_amount,(s.line_amount/(1+h.tax_rate)) not_tax_amount,
       h.customer_code,c.customer_name,h.creation_date,h.status,
       d.item_name,d.item_desc,d.gongyi,e.employee_name,h.customer_order_number
FROM
    so_lines_all s,
    so_headers_all h,
    customers c,sf_item_no d,hr_employees e
WHERE  s.order_number = h.order_number 
AND c.customer_code = h.customer_code  and e.employee_num=h.yewu
 and s.stockid=d.item_no
 and h.tax_flag='Y' ";

     
    if (isset($_GET['SO_from']) and $_GET['SO_from'] != '') {
        $sql = $sql . " and h.order_number " . LIKE . " '%" . $_GET['SO_from'] . "%' ";
    }
	if (isset($_GET['customer_order_number']) and $_GET['customer_order_number'] != '') {
        $sql = $sql . " and h.customer_order_number " . LIKE . " '%" . $_GET['customer_order_number'] . "%' ";
    } 
    if (isset($_GET['item_no']) and $_GET['item_no'] != '') {
        $sql = $sql . " and d.item_no " . LIKE . " '%" . $_GET['item_no'] . "%' ";
    }
    if (isset($_GET['item_name']) and $_GET['item_name'] != '') {
        $sql = $sql . " and d.item_name " . LIKE . " '%" . $_GET['item_name'] . "%' ";
    }
    if (isset($_GET['item_desc']) and $_GET['item_desc'] != '') {
        $sql = $sql . " and d.item_desc " . LIKE . " '%" . $_GET['item_desc'] . "%' ";
    }
    if (isset($_GET['customer_name']) and $_GET['customer_name'] != '') {
        $sql = $sql . " and c.customer_name " . LIKE . " '%" . $_GET['customer_name'] . "%' ";
    }

    if (isset($_GET['customer_code']) and $_GET['customer_code'] != '') {
        $sql = $sql . " and c.customer_code " . LIKE . " '%" . $_GET['customer_code'] . "%' ";
    }
    if (isset($_GET['employee_name']) and $_GET['employee_name'] != '') {
        $sql = $sql . " and e.employee_name " . LIKE . " '%" . $_GET['employee_name'] . "%' ";
    }
    if (isset($_GET['employee_num']) and $_GET['employee_num'] != '') {
        $sql = $sql . " and e.employee_num " . LIKE . " '%" . $_GET['employee_num'] . "%' ";
    }

    if (isset($_GET['dengji']) and $_GET['dengji'] != '') {
        $sql = $sql . " and c.dengji " . LIKE . " '%" . $_GET['dengji'] . "%' ";
    }


    if (empty($_GET['FromDate']) == 0) {
        $SQL_FromDate = strtotime($_GET['FromDate']);
        $sql .= " and h.creation_date >= '" . $SQL_FromDate . "' ";
    }
    if (empty($_GET['ToDate']) == 0) {
        $SQL_ToDate = strtotime($_GET['ToDate']) + 86400;
        //echo $SQL_ToDate;
        $sql .= " and h.creation_date <='" . $SQL_ToDate . "' ";
    }
    if ($_GET['status'] != "") {
        $sql .= " and h.status ='" . $_GET['status'] . "' ";
    }
   
$sql .= "union 
 SELECT
       s.order_number,s.line,s.stockid,s.uom,s.quantity,(s.price*(1+h.tax_rate)) ,s.price no_tax_price, s.quantity_shiped,(s.line_amount*(1+h.tax_rate)) ,s.line_amount ,
       h.customer_code,c.customer_name,h.creation_date,h.status,
       d.item_name,d.item_desc,d.gongyi,e.employee_name,h.customer_order_number
FROM
    so_lines_all s,
    so_headers_all h,
    customers c,sf_item_no d,hr_employees e
WHERE  s.order_number = h.order_number 
AND c.customer_code = h.customer_code  and e.employee_num=h.yewu
 and s.stockid=d.item_no
 and h.tax_flag='N' ";
if (isset($_GET['SO_from']) and $_GET['SO_from'] != '') {
        $sql = $sql . " and h.order_number " . LIKE . " '%" . $_GET['SO_from'] . "%' ";
    }
	if (isset($_GET['customer_order_number']) and $_GET['customer_order_number'] != '') {
        $sql = $sql . " and h.customer_order_number " . LIKE . " '%" . $_GET['customer_order_number'] . "%' ";
    } 
    if (isset($_GET['item_no']) and $_GET['item_no'] != '') {
        $sql = $sql . " and d.item_no " . LIKE . " '%" . $_GET['item_no'] . "%' ";
    }
    if (isset($_GET['item_name']) and $_GET['item_name'] != '') {
        $sql = $sql . " and d.item_name " . LIKE . " '%" . $_GET['item_name'] . "%' ";
    }
    if (isset($_GET['item_desc']) and $_GET['item_desc'] != '') {
        $sql = $sql . " and d.item_desc " . LIKE . " '%" . $_GET['item_desc'] . "%' ";
    }
    if (isset($_GET['customer_name']) and $_GET['customer_name'] != '') {
        $sql = $sql . " and c.customer_name " . LIKE . " '%" . $_GET['customer_name'] . "%' ";
    }

    if (isset($_GET['customer_code']) and $_GET['customer_code'] != '') {
        $sql = $sql . " and c.customer_code " . LIKE . " '%" . $_GET['customer_code'] . "%' ";
    }
    if (isset($_GET['employee_name']) and $_GET['employee_name'] != '') {
        $sql = $sql . " and e.employee_name " . LIKE . " '%" . $_GET['employee_name'] . "%' ";
    }
    if (isset($_GET['employee_num']) and $_GET['employee_num'] != '') {
        $sql = $sql . " and e.employee_num " . LIKE . " '%" . $_GET['employee_num'] . "%' ";
    }

    if (isset($_GET['dengji']) and $_GET['dengji'] != '') {
        $sql = $sql . " and c.dengji " . LIKE . " '%" . $_GET['dengji'] . "%' ";
    }


    if (empty($_GET['FromDate']) == 0) {
        $SQL_FromDate = strtotime($_GET['FromDate']);
        $sql .= " and h.creation_date >= '" . $SQL_FromDate . "' ";
    }
    if (empty($_GET['ToDate']) == 0) {
        $SQL_ToDate = strtotime($_GET['ToDate']) + 86400;
        //echo $SQL_ToDate;
        $sql .= " and h.creation_date <='" . $SQL_ToDate . "' ";
    }
    if ($_GET['status'] != "") {
        $sql .= " and h.status ='" . $_GET['status'] . "' ";
	}

  $sql .= " order by 1,2";
   
	$result_num = DB_query($sql,$db);
//oci_execute($par);
include_once("xlsxwriter.class.php");
$date=date('YmdHis');
//ini_set('display_errors', 0);
//ini_set('log_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);

$filename = "业务订单明细".$date.".xlsx";
header('Content-disposition: attachment; filename="'.XLSXWriter::sanitize_filename($filename).'"');
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate');
header('Pragma: public');
$rows2 = array( 
  array('业务订单明细'),
);
 
if($_SESSION['price_flag'] == 'N'){
 
	$rows = array( 
  array('订单号','销售','状态','行','客户名称','成品料号','产品名称','规格型号','单位','数量',
       '已出货量','未税单价','含税单价','未税金额','含税金额','建单日期'),
);
}else{
    $rows = array( 
        array('订单号','销售','状态','行','客户名称','成品料号','产品名称','规格型号','单位','数量',
             '已出货量','建单日期'),
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
if($_SESSION['price_flag'] == 'N'){
    
     $writer->writeSheetRow('Sheet1', array($v['order_number'],$v['employee_name'],$v['status'],$v['line'],$v['customer_name'],$v['stockid'].' ',
        $v['item_name'],$v['item_desc'],$v['uom'],$v['quantity'],$v['quantity_shiped'],' '.sprintf("%.2f",$v['no_tax_price']),' '.sprintf("%.2f",$v['tax_price']),
        ' '.sprintf("%.2f",$v['not_tax_amount']),
		  ' '.sprintf("%.2f",$v['tax_amount']), date('Y-m-d', $v['creation_date']),
        ));
    }else{
        $writer->writeSheetRow('Sheet1', array($v['order_number'],$v['employee_name'],$v['status'],$v['line'],$v['customer_name'],$v['stockid'].' ',
        $v['item_name'],$v['item_desc'],$v['uom'],$v['quantity'],$v['quantity_shiped'], date('Y-m-d', $v['creation_date']),
        ));
    }

	 }
      
$writer->writeToStdOut();
//$writer->writeToFile('example.xlsx');
//echo $writer->writeToString();
exit(0);
?>
