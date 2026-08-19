<?php

/* $Id: PrintCustTrans.php 6310 2013-08-29 10:42:50Z daintree $ */

include('includes/session2.inc');

$ViewTopic = 'PrintResidents';
$BookMark = 'PrintResidents';
//include ('includes/class.pdf.php');
//include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

require_once 'PHPExcel.php';       
require_once 'PHPExcel/Writer/Excel5.php';    
//require_once("..\include\mysqlconn.php");   
// $sdate=$_POST["sdate"];//接受传递过来的生成时间段   
$time =date("Y-m-d");
 
if (isset($_GET['FromDate2'])) {
    $FromDate2 = $_GET['FromDate2'];
} else if (isset($_POST['FromDate2'])) {
    $FromDate2 = $_POST['FromDate2'];
}
if (isset($_GET['ToDate2'])) {
    $ToDate2 = $_GET['ToDate2'];
} else if (isset($_POST['ToDate2'])) {
    $ToDate2 = $_POST['ToDate2'];
}
if (isset($_GET['Stockid_from'])) {
    $Stockid_from = $_GET['Stockid_from'];
} else if (isset($_POST['Stockid_from'])) {
    $Stockid_from = $_POST['Stockid_from'];
}
if (isset($_GET['Stockid_to'])) {
    $Stockid_to = $_GET['Stockid_to'];
} else if (isset($_POST['Stockid_to'])) {
    $Stockid_to = $_POST['Stockid_to'];
}
 
$sql = "SELECT  a.item_no,a.item_name,a.item_desc,a.units,b.book_order_date,sum(b.supplyquantity) supplyquantity
	FROM mrpsupplies b, sf_item_no a 
	   WHERE a.item_no = b.part
        AND  ordertype='PLANPR'  ";





 

if (empty($FromDate2)==0) {
    $SQL_FromDate2 = strtotime( $FromDate2);
    //echo $SQL_FromDate;
    $sql .= " and  book_order_date >= '" . $SQL_FromDate2 . "' ";
}
if (empty($ToDate2)==0) {
    $SQL_ToDate2 = strtotime( $ToDate2) + 86400;
     //echo $SQL_ToDate;
    $sql .= " and  book_order_date <='" . $SQL_ToDate2 . "' ";
}

if (isset($Stockid_from) and $Stockid_from != '') {
        $sql = $sql . " and a.item_no >=  '" . $Stockid_from . "'";
    }
if (isset($Stockid_to) and $Stockid_to != '') {
        $sql = $sql . " and a.item_no <=  '" . $Stockid_to . "' ";
    }
 


  $sql = $sql." group by a.item_no,a.item_name,a.item_desc,a.units,b.book_order_date ";
//echo $sql;

// 创建一个处理对象实例       
$objExcel = new PHPExcel();       
      
// 创建文件格式写入对象实例, uncomment       
$objWriter = new PHPExcel_Writer_Excel5($objExcel);
      
//*************************************       
//设置当前的sheet索引，用于后续的内容操作。       
//一般只有在使用多个sheet的时候才需要显示调用。       
//缺省情况下，PHPExcel会自动创建第一个sheet被设置SheetIndex=0       
$objExcel->setActiveSheetIndex(0);       
$objActSheet = $objExcel->getActiveSheet();       
      
//设置当前活动sheet的名称       
$objActSheet->setTitle('MRP建议采购报表');
      
//*************************************       
//       
//设置宽度，这个值和EXCEL里的不同，不知道是什么单位，略小于EXCEL中的宽度   
$objActSheet->getColumnDimension('A')->setWidth(20);    
$objActSheet->getColumnDimension('B')->setWidth(26);    
$objActSheet->getColumnDimension('C')->setWidth(25);    
$objActSheet->getColumnDimension('D')->setWidth(6);    
$objActSheet->getColumnDimension('E')->setWidth(16);  
$objActSheet->getColumnDimension('F')->setWidth(10);   




$objActSheet->getRowDimension(1)->setRowHeight(30);    
$objActSheet->getRowDimension(2)->setRowHeight(30);    
//$objActSheet->getRowDimension(3)->setRowHeight(30);
    
//设置单元格的值     
$objActSheet->setCellValue('A1', 'MRP建议采购报表');
//合并单元格  
$objActSheet->mergeCells('A1:F1');

//设置单元格的值  
$objActSheet->setCellValue('A2', '');
//合并单元格   
$objActSheet->mergeCells('A2:F2');


 

//设置样式   
$objStyleA1 = $objActSheet->getStyle('A1');       
$objStyleA1->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);   
$objFontA1 = $objStyleA1->getFont();       
$objFontA1->setName('宋体');       
$objFontA1->setSize(18);     
$objFontA1->setBold(true);       




 



$objStyleA1 = $objActSheet->getStyle('A3');
$objStyleA1->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);   
$objFontA1 = $objStyleA1->getFont();       
$objFontA1->setBold(true);

$objStyleA1 = $objActSheet->getStyle('B3');
$objStyleA1->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);   
$objFontA1 = $objStyleA1->getFont();       
$objFontA1->setBold(true);

$objStyleA1 = $objActSheet->getStyle('C3');
$objStyleA1->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);   
$objFontA1 = $objStyleA1->getFont();       
$objFontA1->setBold(true);

$objStyleA1 = $objActSheet->getStyle('D3');
$objStyleA1->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);   
$objFontA1 = $objStyleA1->getFont();       
$objFontA1->setBold(true);

$objStyleA1 = $objActSheet->getStyle('E3');
$objStyleA1->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);   
$objFontA1 = $objStyleA1->getFont();       
$objFontA1->setBold(true);

$objStyleA1 = $objActSheet->getStyle('F3');
$objStyleA1->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);   
$objFontA1 = $objStyleA1->getFont();       
$objFontA1->setBold(true);


//设置居中对齐   
//$objActSheet->getStyle('A4')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
//$objActSheet->getStyle('B4')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
//$objActSheet->getStyle('C4')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
//$objActSheet->getStyle('D4')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

$objActSheet->setCellValue('A3', '料号');
$objActSheet->setCellValue('B3', '料号名称');
$objActSheet->setCellValue('C3', '规格型号');
$objActSheet->setCellValue('D3', '单位');
$objActSheet->setCellValue('E3', '需求日期');
$objActSheet->setCellValue('F3', '数量'); 

  

 
//$objActSheet->setCellValue('G4', '数量小计');   
 //设置边框   
    $objActSheet->getStyle('A3')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );
    $objActSheet->getStyle('A3')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );
    $objActSheet->getStyle('A3')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );
    $objActSheet->getStyle('A3')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );
    $objActSheet->getStyle('B3')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );
    $objActSheet->getStyle('B3')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );
    $objActSheet->getStyle('B3')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );
    $objActSheet->getStyle('B3')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );
    $objActSheet->getStyle('C3')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );
    $objActSheet->getStyle('C3')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );
    $objActSheet->getStyle('C3')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );
    $objActSheet->getStyle('C3')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );
    $objActSheet->getStyle('D3')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );
    $objActSheet->getStyle('D3')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );
    $objActSheet->getStyle('D3')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );
    $objActSheet->getStyle('D3')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );
	$objActSheet->getStyle('E3')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );
    $objActSheet->getStyle('E3')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );
    $objActSheet->getStyle('E3')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );
    $objActSheet->getStyle('E3')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );
    $objActSheet->getStyle('F3')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );
    $objActSheet->getStyle('F3')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );
    $objActSheet->getStyle('F3')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );
    $objActSheet->getStyle('F3')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );



$result1 =DB_query($sql,$db,$ErrMsg,$DbgMsg);
$i=1;   
//从数据库取值循环输出   
while($mysql=DB_fetch_array($result1)){   
 
    $n=$i+3;
       
    $objActSheet->getStyle('B'.$n)->getNumberFormat()->setFormatCode('@');    
       
    $objActSheet->getRowDimension($n)->setRowHeight(16);    
       
    $objActSheet->getStyle('A'.$n)->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('A'.$n)->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('A'.$n)->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('A'.$n)->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('B'.$n)->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('B'.$n)->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('B'.$n)->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('B'.$n)->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('C'.$n)->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('C'.$n)->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('C'.$n)->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('C'.$n)->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('D'.$n)->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('D'.$n)->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('D'.$n)->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('D'.$n)->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('E'.$n)->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('E'.$n)->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('E'.$n)->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('E'.$n)->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('F'.$n)->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('F'.$n)->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('F'.$n)->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('F'.$n)->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   


 

	 
   IF (isset( $mysql['item_name'] )) {
	$item_name=$mysql['item_name'];}
   ELSE  {
   $item_name=' ';}

   IF (isset($mysql['item_desc'])) {
   $item_desc=$mysql['item_desc'];}
   ELSE  {
   $item_desc=' ';}
       
     $objActSheet->setCellValue('A'.$n, $mysql['item_no']);
	$objActSheet->setCellValue('B'.$n, $mysql['item_name']);
	$objActSheet->setCellValue('C'.$n, $mysql['item_desc']);
	 $objActSheet->setCellValue('D'.$n, $mysql['units']);
    $objActSheet->setCellValue('E'.$n, date('Y-m-d',$mysql['book_order_date']));
	 $objActSheet->setCellValue('F'.$n, $mysql['supplyquantity']); 

   
    //$total_quantity  = $total_quantity+$mysql['check_quantity'];
    //$total_amount  = $total_amount+$mysql['check_amount'];
 
    $i++; 
    $n++;   
} 

 

  $dateString=date('YmdHis').".xls";
    $outputFileName=$dateString;
    header('Content-Type: application/vnd.ms-excel');
    header('Content-type: text/csv');
    header('Content-Disposition: attachment;filename="'.$outputFileName.'"');
    header('Cache-Control: max-age=0');
    
    $objWriter = PHPExcel_IOFactory::createWriter($objExcel, 'Excel5');
    $objWriter->save('php://output');
//include('includes/footer.inc');
?>