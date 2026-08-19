<?php
//  首先引入XLSXWriter包

putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");
ob_start();
include('includes/session2.inc');
include('includes/SQL_CommonFunctions.inc');
unset($result);
 $sql = " select d.item_no mitem_no,d.item_name mitem_name,d.item_desc mitem_desc,a.plan_start_date,a.so_header_number,a.so_line_number,b.segment1,c.item_desc,c.item_name,c.units,b.comments,quantity_issued,b.required_quantity,
	a.wip_entity_name,b.quantity_per_assembly,b.operation_seq_num,(select sum(quantity_issued- required_quantity) 
	from wip_material_requierments w,wip_jobs_all j where w.segment1=c.item_no  and quantity_issued>required_quantity and w.wip_entity_name=j.wip_entity_name
	and j.status_type='开始' ) chaofa_qty,(select sum(cc.quantity)  from inv_onhand_quantity_all cc,locations dd where cc.stockid=c.item_no and cc.subinventory_code=dd.loccode and dd.baofei_flag='N'  ) onhand_quantity,(select sum(wait_inspect_quantity) from po_rcv_receipt_line p where p.stockid=b.segment1 and wait_inspect_quantity>0 )  wait_inspect_quantity,(select sum(wait_delivery_quantity) from po_rcv_receipt_line p where p.stockid=b.segment1 and wait_delivery_quantity>0 )  wait_delivery_quantity,(select sum(quantity-quantity_received) from po_lines_all p where p.stockid=b.segment1 and quantity>quantity_received and status='已签核' )  wait_received	
						from wip_jobs_all a,wip_material_requierments b,sf_item_no c,sf_item_no d 
                        where b.wip_entity_name =a.wip_entity_name and b.segment1=c.item_no
						and a.primary_item=d.item_no 
						and a.status_type='开始'
						and required_quantity<>quantity_issued
						";

    
     if (isset($_GET['wip_entity_name']) and $_GET['wip_entity_name'] != '') { 
		$sql = $sql . " and a.wip_entity_name " . LIKE . " '%" . $_GET['wip_entity_name'] . "%' ";
    }
 
    if (isset($_GET['item_no']) and $_GET['item_no'] != '') {
        $sql = $sql . " and b.segment1 " . LIKE . " '%" . $_GET['item_no'] . "%' ";
    }
	 if (isset($_GET['item_name']) and $_GET['item_name'] != '') {
        $sql = $sql . " and c.item_name " . LIKE . " '%" . $_GET['item_name'] . "%' ";
    }
	if (isset($_GET['item_desc']) and $_GET['item_desc'] != '') {
        $sql = $sql . " and c.item_desc " . LIKE . " '%" . $_GET['item_desc'] . "%' ";
    }
   
    if (empty($_GET['FromDate']) == 0) {
        $SQL_FromDate = strtotime($_GET['FromDate']);
        $sql .= " and a.plan_start_date >= '" . $SQL_FromDate . "' ";
    }
    if (empty($_GET['ToDate']) == 0) {
        $SQL_ToDate = strtotime($_GET['ToDate']) + 86400;
        //echo $SQL_ToDate;
        $sql .= " and a.plan_start_date <='" . $SQL_ToDate . "' ";
    }
	 $sql .= " order by b.operation_seq_num,b.segment1 "; 


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
  array('工单料况明细报表'),

);
$rows = array( 
  array('工单号','母件料号' ,'母件料号名称', '母件规格型号', '开工日期' ,'料号' ,'料号名称', '规格型号','数量','阶层','单位', '需求量', '库存量' , '超发量','已发量','欠料量','待检验','允收待入库','采购未收量','备注' ),

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
     $writer->writeSheetRow('Sheet1', array($v['wip_entity_name'],$v['mitem_no'],$v['mitem_name'],$v['mitem_desc'],date('Y-m-d',$v['plan_start_date']),
     $v['segment1'],
     $v['item_name'],$v['item_desc'],$v['quantity_per_assembly'].' ',$v['operation_seq_num'],$v['units'],$v['required_quantity'],$v['onhand_quantity'],$v['chaofa_qty'],$v['quantity_issued'],$wait,$v['wait_inspect_quantity'],$v['wait_delivery_quantity'],$v['wait_received'],$v['comments']  ));
	 }
       
$writer->writeToStdOut();
//$writer->writeToFile('example.xlsx');
//echo $writer->writeToString();
exit(0);
?>
