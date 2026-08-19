<?php
//  首先引入XLSXWriter包
putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");
ob_start();
include('includes/session2.inc');
include('includes/SQL_CommonFunctions.inc');
unset($result);

 $sql2 = "select  b.wip_entity_name,b.start_quantity,c.item_no ,c.item_name,b.plan_start_date  
 from wip_jobs_all b,sf_item_no c
                        where b.primary_item=c.item_no  
						and b.wip_entity_name='".$_GET['wip_entity_name']."'   ";
$result2 = DB_query($sql2,$db);
$myrow = DB_fetch_array($result2);

$sql = "select  b.wip_entity_name,(required_quantity - b.quantity_issued) wait_quantity, required_quantity,b.quantity_per_assembly,b.quantity_issued,b.operation_seq_num,c.item_no,c.item_desc,c.item_name,(select sum(cc.quantity)  from inv_onhand_quantity_all cc where cc.stockid=c.item_no and cc.subinventory_code='".$_GET['insubinventory']."') onhand_quantity,units,(select sum(quantity_issued- required_quantity) from wip_material_requierments w,wip_jobs_all j where w.segment1=c.item_no and w.wip_entity_name<>b.wip_entity_name and quantity_issued>required_quantity and w.wip_entity_name=j.wip_entity_name and j.status_type='开始' ) chaofa_qty,(select sum(required_quantity-quantity_issued ) from wip_material_requierments ww where ww.segment1=c.item_no and ww.wip_entity_name=b.wip_entity_name ) qianfa_qty  
						from wip_material_requierments b,sf_item_no c
                        where b.segment1=c.item_no  
						and b.wip_entity_name='".$_GET['wip_entity_name']."' 
                        and required_quantity > b.quantity_issued
					  order by b.operation_seq_num,c.item_no   ";
     

   
	$result_num = DB_query($sql,$db);
//oci_execute($par);
include_once("xlsxwriter.class.php");
$date=date('YmdHis');
//ini_set('display_errors', 0);
//ini_set('log_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);

$filename = "工单材料领用报表".$date.".xlsx";
header('Content-disposition: attachment; filename="'.XLSXWriter::sanitize_filename($filename).'"');
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate');
header('Pragma: public');
$rows2 = array( 
  array('工单发料单'),
);

 
$rows = array( 
  array('序号','制程','贴装台','地址','料号','料号名称','规格型号','单位','单耗','库存量','需求量','实发量','退料量','正反面','机器名','备注'),
);


$writer = new XLSXWriter();
$writer->setAuthor('Shunfansoft'); 
	

 



 foreach($rows2 as $row2)
	$writer->writeSheetRow('Sheet1', $row2);
 
 $writer->writeSheetRow('Sheet1',array('工单号' ,$myrow['wip_entity_name']));
 $writer->writeSheetRow('Sheet1',array('产品料号' , $myrow['item_no']));
 $writer->writeSheetRow('Sheet1',array('料号名称' , $myrow['item_name']));
 $writer->writeSheetRow('Sheet1',array('数量' , $myrow['start_quantity']));
 $writer->writeSheetRow('Sheet1',array('开工日期' , date('Y-m-d',$myrow['plan_start_date'])));

foreach($rows as $row)
	$writer->writeSheetRow('Sheet1', $row);
    
$line=0;
	while ($v = DB_fetch_array($result_num)) {

    $chaofa='';
	$sql22="select w.wip_entity_name,w.operation_seq_num, (quantity_issued- required_quantity) chaofa
	from wip_material_requierments w,wip_jobs_all j 
	where w.segment1='".$v['item_no']."'  and w.wip_entity_name<>'".$v['wip_entity_name']."'  
	and quantity_issued>required_quantity and w.wip_entity_name=j.wip_entity_name and j.status_type='开始' ";
	$result22 = DB_query($sql22,$db);
	while ($myrow2 = DB_fetch_array($result22)){
	$chaofa=$chaofa.';'.$myrow2['wip_entity_name'].'-'.$myrow2['operation_seq_num'].'-'.$myrow2['chaofa'];
	} 

	              $sql23="select *
						from bom_smt_liaozhan 
                        where  assembly_item_no='".$myrow['item_no']."' 
                        and component_item='".$v['item_no']."' 
					  order by youxian  
                        ";
			      $result23 = DB_query($sql23,$db);
				  $myrow3 = DB_fetch_array($result23);

	$line=$line+1;

      $writer->writeSheetRow('Sheet1', array($line,$v['operation_seq_num'],$myrow3['tiezhuangtai'],$myrow3['address'],$v['item_no'],$v['item_name'],
        $v['item_desc'],$v['units'],$v['quantity_per_assembly'],$v['onhand_quantity'],$v['required_quantity'],$v['quantity_issued'],' ',$myrow3['mian'],$myrow3['jiqiming'], 
        
        ));
	 }
	  

$writer->writeToStdOut();
//$writer->writeToFile('example.xlsx');
//echo $writer->writeToString();
exit(0);
?>
