<?php
//  首先引入XLSXWriter包

putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");
ob_start();
include('includes/session2.inc');
include('includes/SQL_CommonFunctions.inc');
unset($result);
 $sql = " SELECT  a.operation_seq_num,a.seq_id,a.quantity_per_assembly,a.quantity_issued, a.date_required,a.required_quantity,
	a.wip_entity_name,e.item_no,e.item_name,e.item_desc,e.units,a.comments,a.creation_date,a.created_by,a.chaohao_quantity
	 from wip_material_requierments a,sf_item_no e
	where a.segment1=e.item_no  ";

    
     if (isset($_GET['wip_entity_name']) and $_GET['wip_entity_name'] != '') { 
		$sql = $sql . " and a.wip_entity_name " . LIKE . " '%" . $_GET['wip_entity_name'] . "%' ";
    }
  


 $result_num = DB_query($sql, $db); 
//oci_execute($par);
include_once("xlsxwriter.class.php");
$date=date('YmdHis');
//ini_set('display_errors', 0);
//ini_set('log_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);

$filename = "工单料况明细报表".$date.".xlsx";
header('Content-disposition: attachment; filename="'.XLSXWriter::sanitize_filename($filename).'"');
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate');
header('Pragma: public');
$rows2 = array( 
  array('工单用料详情'),

);
$rows = array( 
  array('工单号', '制程' ,'料号' ,'料号名称', '规格型号','单位','需求日期', '需求数量', '单位耗用' , '已发料量','超耗数量','备注','建立者','建立日期' ),

); 
 
 
$writer = new XLSXWriter();
$writer->setAuthor('Shunfansoft'); 

//$writer->writeSheetHeader('Sheet1', $header);
 foreach($rows2 as $row2)
	$writer->writeSheetRow('Sheet1', $row2);
foreach($rows as $row)
	$writer->writeSheetRow('Sheet1', $row);

	while ($v = DB_fetch_array($result_num)) {

	       if ($v['required_quantity'] > $v['quantity_issued']) {
			  $wait=$v['required_quantity']-$v['quantity_issued'] ;
			} else {
			  $wait=0;
			}	 
			if ($v['date_required']>1) {
                    $date_required= date('Y-m-d', $v['date_required']);
                } else {
                    $date_required='';
                }
     $writer->writeSheetRow('Sheet1', array($v['wip_entity_name'],$v['operation_seq_num'], $v['item_no'],
     $v['item_name'],$v['item_desc'],$v['units'].' ',$date_required,$v['required_quantity'],$v['quantity_per_assembly'],$v['quantity_issued'],$v['chaohao_quantity'],$v['comments'],$v['created_by'],date('Y-m-d H:i:s',$v['creation_date']) ));
	 }
       
$writer->writeToStdOut();
//$writer->writeToFile('example.xlsx');
//echo $writer->writeToString();
exit(0);
?>
