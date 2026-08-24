<?php
//  首先引入XLSXWriter包

putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");
ob_start();
include('includes/session2.inc');
include('includes/SQL_CommonFunctions.inc');
unset($result);
 
if (isset($_GET['employee_num'])) {
    $employee_num = $_GET['employee_num'];
} else if (isset($_POST['employee_num'])) {
    $employee_num = $_POST['employee_num'];
}
if (isset($_GET['employee_name'])) {
    $employee_name = $_GET['employee_name'];
} else if (isset($_POST['employee_name'])) {
    $employee_name = $_POST['employee_name'];
}
if (isset($_GET['shenfenzheng'])) {
    $shenfenzheng = $_GET['shenfenzheng'];
} else if (isset($_POST['shenfenzheng'])) {
    $shenfenzheng = $_POST['shenfenzheng'];
}

$sql = 'select employee_num,employee_name,shenfenzheng,huji,created_by,creation_date           
            from hr_employees where 1=1';

    if(isset($employee_num) and $employee_num != ''){
        $sql = $sql . " and employee_num " .LIKE." '%".$employee_num."%'";
    }
    if(isset($employee_name) and $employee_name != ''){
        $sql = $sql." and employee_name ".LIKE." '%".$employee_name."%' ";
    }
    if(isset($shenfenzheng) and $shenfenzheng != ''){
        $sql = $sql." and shenfenzheng ".LIKE." '%".$shenfenzheng."%' ";
    }
    
    $result_num = DB_query($sql,$db);
//oci_execute($par);
include_once("xlsxwriter.class.php");
$date=date('YmdHis');
//ini_set('display_errors', 0);
//ini_set('log_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);

$filename = "人事资料".$date.".xlsx";
header('Content-disposition: attachment; filename="'.XLSXWriter::sanitize_filename($filename).'"');
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate');
header('Pragma: public');
$rows2 = array( 
  array('人事资料'),
);

$rows = array( 
  array('工号','姓名','身份证','户籍','建单人员','建单日期'),
); 
 
$writer = new XLSXWriter();
$writer->setAuthor('Shunfansoft'); 

//$writer->writeSheetHeader('Sheet1', $header);
 foreach($rows2 as $row2)
	$writer->writeSheetRow('Sheet1', $row2);
foreach($rows as $row)
	$writer->writeSheetRow('Sheet1', $row);

	while ($v = DB_fetch_array($result_num)) {

		 /* if ($v['status'] == 'INPROCESS') {
                $v_status = '待签核';
            } elseif ($v['status'] == 'APPROVED') {
                $v_status = '已签核';
            } elseif ($v['status'] == 'REJECTED') {
                $v_status = '已拒签';
            } else {
                $v_status = '已取消';
            }
            $v_quantity= $v['quantity']-$v['quantity_shiped']; */

     $writer->writeSheetRow('Sheet1', array($v['employee_num'],$v['employee_name'],' '.$v['shenfenzheng'],$v['huji'],
        $v['created_by'],date('Y-m-d H:i:s', $v['creation_date'])));
	 }
    
$writer->writeToStdOut();
//$writer->writeToFile('example.xlsx');
//echo $writer->writeToString();
exit(0);
?>
