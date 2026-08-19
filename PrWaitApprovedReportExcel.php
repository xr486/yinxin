<?php
//  首先引入XLSXWriter包
putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");
ob_start();
include('includes/session2.inc');
include('includes/SQL_CommonFunctions.inc');
unset($result);


    $sql = "select a.pr_num,a.status,a.creation_date ,a.created_by,p.chang,p.kuan,p.gao,  depart_name,a.need_date,a.remark,p.line,stockid,s.item_name,s.item_desc,p.uom,p.quantity,p.po_num,p.po_line,p.subinventory_code,a.approve_date,a.approve_by,(select realname from www_users where userid=a.created_by) realname
    from pr_headers_all a, pr_lines_all p,sf_item_no s where p.stockid=s.item_no AND a.pr_num=p.pr_num
    and a.status='INPROCESS'  
    and p.quantity>0 ";   

    if(isset($_GET['pr_num_from']) and $_GET['pr_num_from'] != ''){ 
        $sql = $sql." and a.pr_num ".LIKE." '%".$_GET['pr_num_from']."%' "; 
    }
	if(isset($_GET['item_no']) and $_GET['item_no'] != ''){ 
        $sql = $sql." and p.stockid ".LIKE." '%".$_GET['item_no']."%' "; 
    }
	if(isset($_GET['item_name']) and $_GET['item_name'] != ''){ 
        $sql = $sql." and s.item_name ".LIKE." '%".$_GET['item_name']."%' "; 
    }

	if(isset($_GET['item_desc']) and $_GET['item_desc'] != ''){ 
        $sql = $sql." and s.item_desc ".LIKE." '%".$_GET['item_desc']."%' "; 
    }

	if(isset($_GET['depart_name']) and $_GET['depart_name'] != ''){ 
        $sql = $sql." and a.depart_name ".LIKE." '%".$_GET['depart_name']."%' "; 
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

$sql .=" order by a.creation_date,p.line";

$result_num = DB_query($sql, $db);
//oci_execute($par);
include_once("xlsxwriter.class.php");
$date = date('YmdHis');
//ini_set('display_errors', 0);
//ini_set('log_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);

$filename = "待审核请购单明细表" . $date . ".xlsx";
header('Content-disposition: attachment; filename="' . XLSXWriter::sanitize_filename($filename) . '"');
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate');
header('Pragma: public');
$rows2 = array(
    array('待审核请购单明细表'),
);

    $rows = array(
        array(
            '请购单号',
            '部门',
            '需求日期',
            '下单日',
            '下单人',
            '行',
            '料号',
            '料号名称',
            '规格型号', 
            '数量',
            '单位'

        ),
    );


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
 
        $writer->writeSheetRow('Sheet1', array(
            $v['pr_num'],
            $v['depart_name'],
            date('Y-m-d', $v['need_date']),
            date('Y-m-d H:i:s', $v['creation_date']) ,
            $v['realname'],
            $v['line'],
            $v['stockid'],
            $v['item_name'],
            $v['item_desc'], 
            $v['quantity'],
            $v['uom'],
         
         
         
        ));
    
}

$writer->writeToStdOut();
//$writer->writeToFile('example.xlsx');
//echo $writer->writeToString();
exit(0);
