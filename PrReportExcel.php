<?php
//  首先引入XLSXWriter包
putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");
ob_start();
include('includes/session2.inc');
include('includes/SQL_CommonFunctions.inc');
unset($result);

$sql = "select a.pr_num,a.status,a.creation_date ,a.created_by,need_date,remark,a.approve_date,(select realname from www_users where userid=a.created_by) realname,(select realname from www_users where userid=a.approve_by) approve_by_realname
from pr_headers_all a  where 1=1";

if (isset($_GET['pr_num_from']) and $_GET['pr_num_from'] != '') {
    $sql = $sql . " and a.pr_num " . LIKE . " '%" . $_GET['pr_num_from'] . "%' ";
}

if (isset($_GET['need_customer_code']) and $_GET['need_customer_code'] != '') {
    $sql = $sql . " and a.need_customer_code " . LIKE . " '%" . $_GET['need_customer_code'] . "%' ";
}

if (isset($_GET['need_order_number']) and $_GET['need_order_number'] != '') {
    $sql = $sql . " and a.need_order_number " . LIKE . " '%" . $_GET['need_order_number'] . "%' ";
}
 

if (empty($_GET['FromDate']) == 0) {
    $SQL_FromDate = strtotime($_GET['FromDate']);
    $sql .= " and a.creation_date >= '" . $SQL_FromDate . "' ";
}
if (empty($_GET['ToDate']) == 0) {
    $SQL_ToDate = strtotime($_GET['ToDate']) + 86400;
    //echo $SQL_ToDate;
    $sql .= " and a.creation_date <='" . $SQL_ToDate . "' ";
}

  

  /* $sql .= " order by customer_code  "; */

$result_num = DB_query($sql, $db);
//oci_execute($par);
include_once("xlsxwriter.class.php");
$date = date('YmdHis');
//ini_set('display_errors', 0);
//ini_set('log_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);

$filename = "请购单报表" . $date . ".xlsx";
header('Content-disposition: attachment; filename="' . XLSXWriter::sanitize_filename($filename) . '"');
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate');
header('Pragma: public');
$rows2 = array(
    array('请购单报表'),
);
if ($_SESSION['price_flag'] == 'N') {
    $rows = array(
        array(
            '请购单号',
            '状态',
           
            '需求日期',
            '备注',
            '签核日',
            '签核人',
            '下单日',
            '下单人'
        ),
    );
} else {
    $rows = array(
        array(
            '请购单号',
            '状态',
         
            '需求日期',
            '备注',
            '签核日',
            '签核人',
            '下单日',
            '下单人'
        ),
    );
}

$writer = new XLSXWriter();
$writer->setAuthor('Shunfansoft');
// $writer->abc(); 

// $writer->writeSheetHeader('Sheet1', $header);

foreach ($rows2 as $row2)
    $writer->writeSheetRow('Sheet1', $row2);
foreach ($rows as $row)
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
    if ($v['approve_date'] > 0) {
        $approve_date = date('Y-m-d', $v['approve_date']);
}else {
        $approve_date = '';
}
    if ($_SESSION['price_flag'] == 'N') {
        $writer->writeSheetRow('Sheet1', array(
            $v['pr_num'],
            $v_status,
        
            date('Y-m-d', $v['need_date']),
            $v['remark'],
            $approve_date,
            $v['approve_by_realname'],
            date('Y-m-d H:i:s', $v['creation_date']),
            $v['realname'],
        ));
    } else {
        $writer->writeSheetRow('Sheet1', array(
            $v['pr_num'],
            $v_status,
         
            date('Y-m-d', $v['need_date']),
            $v['remark'],
            $approve_date,
            $v['approve_by_realname'],
            date('Y-m-d H:i:s', $v['creation_date']),
            $v['realname'],
        ));
    }
}

$writer->writeToStdOut();
//$writer->writeToFile('example.xlsx');
//echo $writer->writeToString();
exit(0);
