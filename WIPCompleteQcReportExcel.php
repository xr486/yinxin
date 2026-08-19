<?php
//  首先引入XLSXWriter包

putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");
ob_start();
include('includes/session2.inc');
include('includes/SQL_CommonFunctions.inc');
unset($result);

$sql ="SELECT a.primary_item ,a.wip_entity_name,a.status_type,start_quantity,
f.item_name,a.wip_entity_id,f.units,c.good_quantity,c.bad_quantity,c.last_update_date creation_date,c.last_updated_by created_by,e.realname,c.lot_num
      from wip_jobs_all a,wip_qc_lines_all c,www_users e,sf_item_no f
where   c.wip_entity_name=a.wip_entity_name and c.last_updated_by=e.userid  and a.primary_item = f.item_no and (c.good_quantity >0 or c.bad_quantity >0)
   ";

if (isset($_GET['wip_entity_name']) and $_GET['wip_entity_name'] != '') 
  { 
	$sql = $sql . " and a.wip_entity_name " . LIKE . " '%" . $_GET['wip_entity_name'] . "%' ";
  }
  if (isset($_GET['primary_item']) and $_GET['primary_item'] != '')
  {
	$sql = $sql . " and primary_item " . LIKE . " '%" . $_GET['primary_item'] . "%' ";
  }
  if (isset($_GET['item_name']) and $_GET['item_name'] != '')
  { 
	$sql = $sql . " and f.item_name " . LIKE . " '%" . $_GET['item_name'] . "%' ";
  }

   
   
  if (empty($_GET['FromDate']) == 0) 
  {
    $SQL_FromDate = strtotime($_GET['FromDate']);
    $sql .= " and c.last_update_date >= '" . $SQL_FromDate . "' ";
  }
  if (empty($_GET['ToDate']) == 0) 
  {
     $SQL_ToDate = strtotime($_GET['ToDate']) + 86400;
     $sql .= " and c.last_update_date <='" . $SQL_ToDate . "' ";
  }
   
  $sql .=" order by  c.last_update_date desc "; 
 


 $result_num = DB_query($sql, $db); 
//oci_execute($par);
include_once("xlsxwriter.class.php");
$date=date('YmdHis');
//ini_set('display_errors', 0);
//ini_set('log_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);

$filename = "工艺单检验信息结果报表".$date.".xlsx";
header('Content-disposition: attachment; filename="'.XLSXWriter::sanitize_filename($filename).'"');
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate');
header('Pragma: public');
$rows2 = array( 
  array('待检验工艺单信息查询报表'),

);
$rows = array( 
  array('工单号', '料号', '料号名称' ,'单位' ,'SN/批号','良品数量' ,'不良数量' ,'检验时间' ,'检验人',),

); 

 
$writer = new XLSXWriter();
$writer->setAuthor('Shunfansoft'); 

//$writer->writeSheetHeader('Sheet1', $header);
 foreach($rows2 as $row2)
	$writer->writeSheetRow('Sheet1', $row2);
foreach($rows as $row)
	$writer->writeSheetRow('Sheet1', $row);

	while ($v = DB_fetch_array($result_num)) {
       

       $wait_receievd=$v['quantity']-$v['this_received'];
     $writer->writeSheetRow('Sheet1', array($v['wip_entity_name'], 
     $v['primary_item'],$v['item_name'],$v['units'],$v['lot_num'],$v['good_quantity'],$v['bad_quantity'],date('Y-m-d',$v['creation_date']),$v['realname'],
    
    ));
	 }
    
$writer->writeToStdOut();
//$writer->writeToFile('example.xlsx');
//echo $writer->writeToString();
exit(0);
?>
