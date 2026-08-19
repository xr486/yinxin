<?php
//  首先引入XLSXWriter包

putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");
ob_start();
include('includes/session2.inc');
include('includes/SQL_CommonFunctions.inc');
unset($result);
$sql = "select  d.item_no master_item,d.item_name master_name,b.item_no ,c.after_onhand,a.wip_entity_name,c.transaction_type,c.transaction_date,a.creation_date,c.created_by,b.item_desc,b.item_name,c.quantity,c.subinventory_from,c.trans_num,b.gongyi,a.make_factory,c.remark,c.operation_seq_num,c.status
				FROM wip_jobs_all a,sf_item_no b,inv_transactions_all_temp c,sf_item_no d
           WHERE   c.transaction_type in ('工单退料','工单领料') 
				and c.item_no=b.item_no and a.primary_item=d.item_no 
				and a.wip_entity_name=c.wip_entity_name
				and b.item_no=c.item_no";

  
     if (isset($_GET['wip_entity_name']) and $_GET['wip_entity_name'] != '') { 
		$sql = $sql . " and a.wip_entity_name " . LIKE . " '%" . $_GET['wip_entity_name'] . "%' ";
    }
   
	if (isset($_GET['trans_num']) and $_GET['trans_num'] != '') { 
		$sql = $sql . " and c.trans_num " . LIKE . " '%" . $_GET['trans_num'] . "%' ";
    }
    if (isset($_GET['item_no']) and $_GET['item_no'] != '') {
        $sql = $sql . " and b.item_no " . LIKE . " '%" . $_GET['item_no'] . "%' ";
    }
	if (isset($_GET['item_name']) and $_GET['item_name'] != '') {
        $sql = $sql . " and b.item_name " . LIKE . " '%" . $_GET['item_name'] . "%' ";
    }
	if (isset($_GET['item_desc']) and $_GET['item_desc'] != '') {
        $sql = $sql . " and b.item_desc " . LIKE . " '%" . $_GET['item_desc'] . "%' ";
    }
	if (isset($_GET['loccode_from']) and $_GET['loccode_from'] != '') {
        $sql = $sql . " and c.subinventory_from " . LIKE . " '%" . $_GET['loccode_from'] . "%' ";
    }
   
    if (empty($_GET['FromDate']) == 0) {
        $SQL_FromDate = strtotime($_GET['FromDate']);
        $sql .= " and c.transaction_date >= '" . $SQL_FromDate . "' ";
    }
    if (empty($_GET['ToDate']) == 0) {
        $SQL_ToDate = strtotime($_GET['ToDate']) + 86400;
        //echo $SQL_ToDate;
        $sql .= " and c.transaction_date <='" . $SQL_ToDate . "' ";
    }
      $sql .= " order by c.transaction_date desc ";


 $result_num = DB_query($sql, $db); 
//oci_execute($par);
include_once("xlsxwriter.class.php");
$date=date('YmdHis');
//ini_set('display_errors', 0);
//ini_set('log_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);

$filename = "工单领退料明细".$date.".xlsx";
header('Content-disposition: attachment; filename="'.XLSXWriter::sanitize_filename($filename).'"');
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate');
header('Pragma: public');
$rows2 = array( 
  array('工单领退料明细'),

);
$rows = array( 
  array('工单号','状态', '产品料号','产品描述','交易类型' ,'交易日期','料号' ,'料号名称', '规格型号','交易数量','仓库', '交易单号', '工序' , '备注','做账人员' ),

); 

$writer = new XLSXWriter();
$writer->setAuthor('Shunfansoft'); 

//$writer->writeSheetHeader('Sheet1', $header);
 foreach($rows2 as $row2)
	$writer->writeSheetRow('Sheet1', $row2);
foreach($rows as $row)
	$writer->writeSheetRow('Sheet1', $row);

	while ($v = DB_fetch_array($result_num)) {

     $writer->writeSheetRow('Sheet1', array($v['wip_entity_name'],$v['status'],$v['master_item'],$v['master_name'],$v['transaction_type'],date('Y-m-d H:i:s',$v['transaction_date']),
     $v['item_no'],
     $v['item_name'],$v['item_desc'],$v['quantity'].' ',$v['subinventory_from'],$v['trans_num'],$v['operation_seq_num'],$v['remark'],$v['created_by']
		  ));
	 }
       
$writer->writeToStdOut();
//$writer->writeToFile('example.xlsx');
//echo $writer->writeToString();
exit(0);
?>
