<?php
//  首先引入XLSXWriter包
putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");
ob_start();
include('includes/session2.inc');
include('includes/SQL_CommonFunctions.inc');
unset($result);


 $from=date('Ymd',strtotime($_GET['FromDate'].'-'.$_GET['ToDate'].'-'.'01') );
  
 
 $to=date('Ymd',strtotime($_GET['FromDate'].'-'.$_GET['ToDate'].'-'.'20') );
 
 $oldDate = strtotime(date('Y-m',strtotime("$from -10 day")).'-'.'21');
 $time = strtotime($to) + 86400;



$sql6 = "SELECT   a.vendor_code,b.vendor_name,sum(c.line_amount) as amount ,sum(e.osp_yugu_price*c.transaction_quantity)  as osp_yugu_amount
		  from waixie_rcv_receipt_header a,vendors b,waixie_rcv_receipt_line c,waixie_lines_all d,wip_operation_plan e
	     where a.delivery_date > ".$oldDate." and a.delivery_date < ".$time."
		 and a.vendor_code=b.vendor_code and a.receipt_num=c.receipt_num
		 and d.po_num=c.po_num 	 and  d.line=c.po_line 
and e.wip_entity_name=d.wip_entity_name and e.operation_seq_num=d.operation_seq_num
	     GROUP BY a.vendor_code,b.vendor_name
		 order by sum(c.line_amount) desc";



$result_num = DB_query($sql6, $db);
//oci_execute($par);
include_once("xlsxwriter.class.php");
$date = date('YmdHis');
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);

$filename = "外协下单金额汇总" . $date . ".xlsx";
header('Content-disposition: attachment; filename="' . XLSXWriter::sanitize_filename($filename) . '"');
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate');
header('Pragma: public');
$rows2 = array(
    array('外协下单金额汇总'),
);
 
    $rows = array(
        array(
            '供应商编号' ,'供应商名称','外协核定金额', '入库金额'
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
        $writer->writeSheetRow('Sheet1', array(   
            $v['vendor_code'],
			$v['vendor_name'],
			$v['osp_yugu_amount'],
			$v['amount'],

          
        ));
    }

$writer->writeToStdOut();
//$writer->writeToFile('example.xlsx');
//echo $writer->writeToString();
exit(0);
