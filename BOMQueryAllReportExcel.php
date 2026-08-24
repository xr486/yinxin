<?php
//  首先引入XLSXWriter包
putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");
ob_start();
include('includes/session2.inc');
include('includes/SQL_CommonFunctions.inc');
unset($result);
 

if (isset($_GET['item_no'])) {
    $item_no = $_GET['item_no'];
} else if (isset($_POST['item_no'])) {
    $item_no = $_POST['item_no'];
}
 

$sql = "SELECT b.component_sequence_id,b.assembly_item_no,b.weizhi,b.operation_seq_num,b.component_quantity,b.sunhao_rate,a.item_no,b.effectivity_date,
	b.disable_date, component_remarks,a.units,change_notice,item_num,item_name,item_desc ,(select  operation_code from 
	bom_routings_all c 
		where b.assembly_item_no=c.assembly_item_no
		and b.operation_seq_num=c.operation_seq_num ) operation_code,(select count(*) 
		from bom_substitutes_all bsa where b.component_sequence_id=bsa.component_sequence_id ) sub_count
FROM bom_lines_all b,sf_item_no a
        where a.item_no = b.component_item   
		and b.bom_header_id  ='" .$item_no."'";
    
    $sql .=" order by item_num  ";
   
	$result_num = DB_query($sql,$db);


	function DisplayBOMItems( $Component,$Level, $db ) {
 
		$sql4 = "SELECT a.component_item,a.item_num,
						b.item_name , b.item_desc , 
						a.component_quantity,
						a.effectivity_date,a.weizhi,
						a.disable_date,a.operation_seq_num,a.component_remarks,
						b.units,(select operation_code
						from bom_routings_all c 
						where c.assembly_item_no=a.assembly_item_no and c.operation_seq_num=a.operation_seq_num ) as operation_code
				FROM bom_lines_all a, sf_item_no b
				where a.component_item=b.item_no   
				AND a.assembly_item_no   = '".$Component."'
			order by a.component_item,a.item_num ";
  
	 
		$result4 = DB_query($sql4,$db);

		//echo $TableHeader;
		$RowCounter =0;

		while ($v2=DB_fetch_array($result4)) {

			$Level1 = str_repeat('-&nbsp;',$Level-1).$Level;
			 

			   $disable_date='';
				if   ($v2['disable_date']!='' and $v2['disable_date']!=0 ) {
				  $disable_date=date('Y-m-d H:i:s', $v2['disable_date']);
				}  	 
    $writer->writeSheetRow('Sheet1', array($Level1,$v2['item_num'],$v2['operation_seq_num'],$v2['component_item'],$v2['item_name'],$v2['item_desc'],
        $v2['component_quantity'],$v2['units'],$v2['weizhi'],date('Y-m-d H:i:s', $v2['effectivity_date']),$disable_date,$v2['component_remarks'],
        ));

		 
			   
 

		} //END WHILE LIST LOOP
} //end of function DisplayBOMItems

//oci_execute($par);
include_once("xlsxwriter.class.php");
$date=date('YmdHis');
//ini_set('display_errors', 0);
//ini_set('log_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);

$filename = "BOM子料明细".$date.".xlsx";
header('Content-disposition: attachment; filename="'.XLSXWriter::sanitize_filename($filename).'"');
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate');
header('Pragma: public');
$rows2 = array( 
  array('BOM子料明细'),
);
 
	$rows = array( 
  array('阶层','序号','制程','料号','料号名称','规格型号','用量','单位','零件位置','生效时间','失效时间','备注'),
);
 
 
$writer = new XLSXWriter();
$writer->setAuthor('Shunfansoft'); 

//$writer->writeSheetHeader('Sheet1', $header);
 foreach($rows2 as $row2)
	$writer->writeSheetRow('Sheet1', $row2);
foreach($rows as $row)
	$writer->writeSheetRow('Sheet1', $row);

	while ($v = DB_fetch_array($result_num)) {

	 $disable_date='';
				if   ($v['disable_date']!='' and $v['disable_date']!=0 ) {
				  $disable_date=date('Y-m-d H:i:s', $v['disable_date']);
				}  	 
     $writer->writeSheetRow('Sheet1', array(' '.'1',$v['item_num'],$v['operation_seq_num'],$v['item_no'],$v['item_name'],$v['item_desc'],
        $v['component_quantity'],$v['units'],$v['weizhi'],date('Y-m-d H:i:s', $v['effectivity_date']),$disable_date,$v['component_remarks'],
        ));
    
	
/*
     //第三层
	   $sql3 = "SELECT a.component_item,a.item_num,
						b.item_name , b.item_desc ,a.weizhi,  
						a.component_quantity,
						a.effectivity_date,
						a.disable_date,a.operation_seq_num,a.component_remarks,
						b.units,(select operation_code
						from bom_routings_all c 
						where c.assembly_item_no=a.assembly_item_no and c.operation_seq_num=a.operation_seq_num ) as operation_code
				FROM bom_lines_all a, sf_item_no b
				where a.component_item=b.item_no    
				AND a.assembly_item_no = '".$v2['component_item']."'";  	 
		$result3 = DB_query($sql3,$db);	
		while ($v3=DB_fetch_array($result3)) {				 
     $writer->writeSheetRow('Sheet1', array('--3',$v3['item_num'],$v3['operation_seq_num'],$v3['component_item'],$v3['item_name'],$v3['item_desc'],
        $v3['component_quantity'],$v3['units'],'',date('Y-m-d H:i:s', $v3['effectivity_date']),$disable_date,$v3['component_remarks'],
        ));
     
	 //第四层
	   $sql4 = "SELECT a.component_item,a.item_num,
						b.item_name , b.item_desc , a.weizhi,
						a.component_quantity,
						a.effectivity_date,
						a.disable_date,a.operation_seq_num,a.component_remarks,
						b.units,(select operation_code
						from bom_routings_all c 
						where c.assembly_item_no=a.assembly_item_no and c.operation_seq_num=a.operation_seq_num ) as operation_code
				FROM bom_lines_all a, sf_item_no b
				where a.component_item=b.item_no  
				AND a.assembly_item_no = '".$v3['component_item']."'";  	 
		$result4 = DB_query($sql4,$db);	
		while ($v4=DB_fetch_array($result4)) {				 
     $writer->writeSheetRow('Sheet1', array('---4',$v4['item_num'],$v3['operation_seq_num'],$v4['component_item'],$v4['item_name'],$v4['item_desc'],
        $v4['component_quantity'],$v4['units'],'',date('Y-m-d H:i:s', $v4['effectivity_date']),$disable_date,$v4['component_remarks'],
        ));


	 //第五层
	   $sql5 = "SELECT a.component_item,a.item_num,
						b.item_name , b.item_desc , a.weizhi,
						a.component_quantity,
						a.effectivity_date,
						a.disable_date,a.operation_seq_num,a.component_remarks,
						b.units,(select operation_code
						from bom_routings_all c 
						where c.assembly_item_no=a.assembly_item_no and c.operation_seq_num=a.operation_seq_num ) as operation_code
				FROM bom_lines_all a, sf_item_no b
				where a.component_item=b.item_no    
				AND a.assembly_item_no = '".$v3['component_item']."'";  	 
		$result5 = DB_query($sql5,$db);	
		while ($v5=DB_fetch_array($result5)) {				 
     $writer->writeSheetRow('Sheet1', array('----5',$v5['item_num'],$v3['operation_seq_num'],$v5['operation_seq_num'],$v5['component_item'],$v5['item_name'],$v5['item_desc'],
        $v5['component_quantity'],$v5['units'],'',$v5['weizhi'],date('Y-m-d H:i:s', $v5['effectivity_date']),$disable_date,$v5['component_remarks'],
        ));



    } //END 第五层



    } //END 第四层



    } //END 第三层


    } //END 第二层
*/
	 


	 }
      
$writer->writeToStdOut();
//$writer->writeToFile('example.xlsx');
//echo $writer->writeToString();
exit(0);
?>
