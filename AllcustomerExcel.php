<?php

/* $Id: PrintCustTrans.php 6310 2013-08-29 10:42:50Z daintree $ */

include('includes/session.inc');

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
 
if (isset($_GET['C1'])) {
    $C1 = $_GET['C1'];
} else if (isset($_POST['C1'])) {
    $C1 = $_POST['C1'];
}
if (isset($_GET['C2'])) {
    $C2 = $_GET['C2'];
} else if (isset($_POST['C2'])) {
    $C2 = $_POST['C2'];
}
if (isset($_GET['C3'])) {
    $C3 = $_GET['C3'];
} else if (isset($_POST['C3'])) {
    $C3 = $_POST['C3'];
}
if (isset($_GET['C4'])) {
    $C4 = $_GET['C4'];
} else if (isset($_POST['C4'])) {
    $C4 = $_POST['C4'];
}

// $sql="SELECT
//     a.order_number,
//     a.status,
//     b.customer_code,
//     b.customer_name,    
//     e.delivery_num, 
//     d.item_desc,
//     e.price,
//     c.uom, 
//     e.line_amount,
//     f.delivery_date,
//     e.delivery_quantity 
// FROM
//     so_headers_all a,
//     customers b,
//     so_lines_all c,
//     sf_item_no d,
//     so_delivery_all e,
//     so_delivery_headers_all f
// WHERE
//     1 = 1
// AND a.customer_code = b.customer_code 
// and a.order_number=c.order_number
// and c.stockid =d.item_no  
// and e.stockid = d.item_no 
 
//  and e.stockid = c.stockid
// and e.delivery_num = f.delivery_num
// and a.order_number = e.so_order_number
// and c.order_number = e.so_order_number
// and f.customer_code = b.customer_code 
// and f.customer_code = a.customer_code 
//  ";   
 $sql = "SELECT a.delivery_num,b.delivery_date,a.price,a.uom ,a.stockid,a.line_amount,d.customer_code,a.delivery_quantity,a.so_order_number,c.item_desc,d.customer_name
  FROM so_delivery_all a,so_delivery_headers_all b, sf_item_no c,customers d
  WHERE b.delivery_num = a.delivery_num AND c.item_no = a.stockid  AND d.customer_code = b.customer_code order by d.customer_code desc ";
  //   if (isset($C1) and $C1 != '') {
  //       $sql = $sql . " and d.customer_name like   '%" . $C1  . "%' ";
  //   }
	 // if (isset($C2) and $C2 != '') {
  //       $sql = $sql . " and weituofang like   '%" . $C2  . "%' ";
  //   }
	 // if (isset($C3) and $C3 != '') {
  //       $sql = $sql . " and tidanhao like   '%" . $C3  . "%' ";
  //   }
// echo $sql;
// 创建一个处理对象实例       
$objExcel = new PHPExcel();       
      
// 创建文件格式写入对象实例, uncomment       
$objWriter = new PHPExcel_Writer_Excel5($objExcel);      
    
//设置文档基本属性       
$objProps = $objExcel->getProperties();       
$objProps->setCreator("Shunfansoft");       
$objProps->setLastModifiedBy("Shunfansoft");       
$objProps->setTitle("Residents pepole List");       
$objProps->setSubject("苏州东珠龙旺消防器材有限公司");       
$objProps->setDescription("www.Shunfansoft.com");       
$objProps->setKeywords("Residents");       
$objProps->setCategory("Residents Reports"); 



$objProps = $objExcel->getProperties();       
$objProps->setCreator("Shunfansoft");       
$objProps->setLastModifiedBy("Shunfansoft");       
$objProps->setTitle("Residents pepole List");       
$objProps->setSubject("'.$C1.'");       
$objProps->setDescription("www.Shunfansoft.com");       
$objProps->setKeywords("Residents");       
$objProps->setCategory("Residents Reports");  

$objProps = $objExcel->getProperties();       
$objProps->setCreator("Shunfansoft");       
$objProps->setLastModifiedBy("Shunfansoft");       
$objProps->setTitle("Residents pepole List");       
$objProps->setSubject("$time");       
$objProps->setDescription("www.Shunfansoft.com");       
$objProps->setKeywords("Residents");       
$objProps->setCategory("Residents Reports");        

      
//*************************************       
//设置当前的sheet索引，用于后续的内容操作。       
//一般只有在使用多个sheet的时候才需要显示调用。       
//缺省情况下，PHPExcel会自动创建第一个sheet被设置SheetIndex=0       
$objExcel->setActiveSheetIndex(0);       
$objActSheet = $objExcel->getActiveSheet();       
      
//设置当前活动sheet的名称       
$objActSheet->setTitle('Residents');       
      
//*************************************       
//       
//设置宽度，这个值和EXCEL里的不同，不知道是什么单位，略小于EXCEL中的宽度   
$objActSheet->getColumnDimension('A')->setWidth(5);    
$objActSheet->getColumnDimension('B')->setWidth(20);    
$objActSheet->getColumnDimension('C')->setWidth(30);    
$objActSheet->getColumnDimension('D')->setWidth(20);    
$objActSheet->getColumnDimension('E')->setWidth(20);  
$objActSheet->getColumnDimension('F')->setWidth(10);  
$objActSheet->getColumnDimension('G')->setWidth(30); 
$objActSheet->getColumnDimension('H')->setWidth(30);  
$objActSheet->getColumnDimension('I')->setWidth(10); 
$objActSheet->getColumnDimension('J')->setWidth(10);  
$objActSheet->getColumnDimension('K')->setWidth(10);  
$objActSheet->getColumnDimension('L')->setWidth(20);  
$objActSheet->getColumnDimension('M')->setWidth(10); 
$objActSheet->getColumnDimension('N')->setWidth(20); 
$objActSheet->getColumnDimension('O')->setWidth(10); 
 

$objActSheet->getRowDimension(1)->setRowHeight(30);    
$objActSheet->getRowDimension(2)->setRowHeight(30);    
$objActSheet->getRowDimension(3)->setRowHeight(30);    
    
//设置单元格的值     
$objActSheet->setCellValue('A1', '苏州东珠龙旺消防器材有限公司');    
$objActSheet->setCellValue('A3', "日期： $time ");    
//合并单元格   
$objActSheet->mergeCells('A1:O1'); 
// $objActSheet->getStyle('A'. 1)->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   

$objActSheet->mergeCells('A3:C3');    

$objActSheet->setCellValue('A2', "销售送货对账单");    
//合并单元格   
$objActSheet->mergeCells('A2:O2');  
//设置样式   
$objStyleA1 = $objActSheet->getStyle('A1');       
$objStyleA1->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);   
$objFontA1 = $objStyleA1->getFont();       
$objFontA1->setName('宋体');       
$objFontA1->setSize(18);     
$objFontA1->setBold(true);       


$objStyleA1 = $objActSheet->getStyle('A2');       
$objStyleA1->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);   
$objFontA1 = $objStyleA1->getFont();       
$objFontA1->setName('宋体');       
$objFontA1->setSize(18);     
$objFontA1->setBold(true);     

$objStyleA1 = $objActSheet->getStyle('A3');       
$objStyleA1->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);   
$objFontA1 = $objStyleA1->getFont();       
$objFontA1->setName('宋体');       
$objFontA1->setSize(18);     
$objFontA1->setBold(true);  



//设置居中对齐   
$objActSheet->getStyle('A4')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);   
$objActSheet->getStyle('B4')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);   
$objActSheet->getStyle('C4')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);   
//$objActSheet->getStyle('D4')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);   
  
$objActSheet->setCellValue('A4', 'NO');    
$objActSheet->setCellValue('B4', '客户编码');    
$objActSheet->setCellValue('C4', '客户名称');    
$objActSheet->setCellValue('D4', '订单编号');    
$objActSheet->setCellValue('E4', '送货单号');    
$objActSheet->setCellValue('F4', '送货日期');   
$objActSheet->setCellValue('G4', '规格型号');    
$objActSheet->setCellValue('H4', '产品名称');    
$objActSheet->setCellValue('I4', '单位');    
$objActSheet->setCellValue('J4', '数量');   
$objActSheet->setCellValue('K4', '单价');    
$objActSheet->setCellValue('L4', '货款调整');    
$objActSheet->setCellValue('M4', '金额');    
$objActSheet->setCellValue('N4', '发票编号');   
$objActSheet->setCellValue('P4', '状态');    
// $objActSheet->setCellValue('N2', '应付USD');    
// $objActSheet->setCellValue('O2', '应付汇率');    
// $objActSheet->setCellValue('P2', '应付开票日期');   
// $objActSheet->setCellValue('Q2', '应付销账日期');    
// $objActSheet->setCellValue('R2', '供应商3');    
// $objActSheet->setCellValue('S2', '应付RMB');    
// $objActSheet->setCellValue('T2', '应付USD');   
// $objActSheet->setCellValue('U2', '应付汇率');    
// $objActSheet->setCellValue('V2', '应付开票日期');    
// $objActSheet->setCellValue('W2', '应付销账日期');    
// $objActSheet->setCellValue('X2', '国外代理');   
// $objActSheet->setCellValue('Y2', '关税');    
// $objActSheet->setCellValue('Z2', '清关费');  
// $objActSheet->setCellValue('AA2', '总计'); 
// $objActSheet->setCellValue('AB2', '提单号');
// $objActSheet->setCellValue('AC2', '船名航次');
// $objActSheet->setCellValue('AD2', '船公司');
// $objActSheet->setCellValue('AE2', '起运港');
// $objActSheet->setCellValue('AF2', '目的港'); 
// $objActSheet->setCellValue('AG2', '箱型箱量');
// $objActSheet->setCellValue('AH2', '小件数');
// $objActSheet->setCellValue('AI2', '件数');
// $objActSheet->setCellValue('AJ2', '毛重');
// $objActSheet->setCellValue('AK2', '体积');
// $objActSheet->setCellValue('AL2', '报关单数'); 
// $objActSheet->setCellValue('AM2', '应收单位1');
// $objActSheet->setCellValue('AN2', '应收RMB');
// $objActSheet->setCellValue('AO2', '应收USD');
// $objActSheet->setCellValue('AP2', '应收汇率');
// $objActSheet->setCellValue('AQ2', '应收开票日期');
// $objActSheet->setCellValue('AR2', '应收销账日期');
// $objActSheet->setCellValue('AS2', '应收单位2');
// $objActSheet->setCellValue('AT2', '应收RMB');
// $objActSheet->setCellValue('AU2', '应收USD'); 
// $objActSheet->setCellValue('AV2', '应收汇率');
// $objActSheet->setCellValue('AW2', '应收开票日期');
// $objActSheet->setCellValue('AX2', '应收销账日期'); 
//设置边框   
    $objActSheet->getStyle('A4')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('A4')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('A4')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('A4')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('B4')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('B4')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('B4')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('B4')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('C4')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('C4')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('C4')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('C4')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('D4')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('D4')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('D4')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('D4')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN ); 
	$objActSheet->getStyle('E4')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('E4')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('E4')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('E4')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('F4')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('F4')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('F4')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('F4')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('G4')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('G4')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('G4')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('G4')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('H4')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('H4')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('H4')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('H4')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN ); 
	$objActSheet->getStyle('I4')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('I4')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('I4')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('I4')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('J4')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('J4')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('J4')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('J4')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('K4')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('K4')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('K4')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('K4')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('L4')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('L4')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('L4')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );  
    $objActSheet->getStyle('L4')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );  
    $objActSheet->getStyle('M4')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('M4')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('M4')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('M4')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );
    $objActSheet->getStyle('N4')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('N4')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN ); 
    $objActSheet->getStyle('N4')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );
    $objActSheet->getStyle('N4')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );
    $objActSheet->getStyle('O4')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('O4')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN ); 
    $objActSheet->getStyle('O4')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );
    $objActSheet->getStyle('O4')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );  
      
    
  
  
//$query=$data->query($sql);   
$result1 =DB_query($sql,$db,$ErrMsg,$DbgMsg);
$i=1;   
//从数据库取值循环输出   
while($mysql=DB_fetch_array($result1)){   
$FirstName=$mysql['FirstName'];   
$ID=$mysql['LastName'];   
$LastName=$mysql['LastName'];   
$Ward=$mysql['Ward'];    
  
    $n=$i+4;   
       
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
    $objActSheet->getStyle('G'.$n)->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('G'.$n)->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('G'.$n)->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('G'.$n)->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('H'.$n)->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('H'.$n)->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('H'.$n)->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('H'.$n)->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN ); 
	$objActSheet->getStyle('I'.$n)->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('I'.$n)->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('I'.$n)->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('I'.$n)->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('J'.$n)->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('J'.$n)->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('J'.$n)->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('J'.$n)->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('K'.$n)->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('K'.$n)->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('K'.$n)->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('K'.$n)->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('L'.$n)->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('L'.$n)->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('L'.$n)->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('L'.$n)->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN ); 

    $objActSheet->getStyle('M'.$n)->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('M'.$n)->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('M'.$n)->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('M'.$n)->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN ); 

    $objActSheet->getStyle('N'.$n)->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('N'.$n)->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('N'.$n)->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('N'.$n)->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN ); 
    $objActSheet->getStyle('O'.$n)->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('O'.$n)->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('O'.$n)->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('O'.$n)->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN ); 

 
 
    
       
    $objActSheet->setCellValue('A'.$n, $i);    
    $objActSheet->setCellValue('B'.$n, $mysql['customer_code']);    
    $objActSheet->setCellValue('C'.$n, $mysql['customer_name']);    
    $objActSheet->setCellValue('D'.$n, $mysql['so_order_number']);    
    $objActSheet->setCellValue('E'.$n, $mysql['delivery_num']);    
    $objActSheet->setCellValue('F'.$n, date('Y-m-d',$mysql['delivery_date']));   
	$objActSheet->setCellValue('G'.$n, $mysql['stockid']);  
	$objActSheet->setCellValue('H'.$n, $mysql['item_desc']);    
    $objActSheet->setCellValue('I'.$n, $mysql['uom']);    
    $objActSheet->setCellValue('J'.$n, $mysql['delivery_quantity']);    
    $objActSheet->setCellValue('K'.$n, $mysql['price']);   
	$objActSheet->setCellValue('L'.$n,  $mysql[' '] );  
	$objActSheet->setCellValue('M'.$n, $mysql['line_amount'] );    
    $objActSheet->setCellValue('N'.$n,  $mysql['']);   
	$objActSheet->setCellValue('O'.$n, $mysql['']);
	// $objActSheet->setCellValue('N'.$n, $mysql['vendor2yingfuusd']);  
    $total_amount  = $total_amount+$mysql['line_amount'];
 
    $i++; 
    $n++;   
} 
 $objActSheet->setCellValue('B'.$n,'合计：');    $objActSheet->setCellValue('C'.$n, $total_amount); 

 $objStyleA1 = $objActSheet->getStyle("B$n");       
$objStyleA1->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);   
$objFontA1 = $objStyleA1->getFont();       
$objFontA1->setName('宋体');       
$objFontA1->setSize(18);     
$objFontA1->setBold(true);       
 $objStyleA1 = $objActSheet->getStyle("C$n");       
$objStyleA1->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);   
$objFontA1 = $objStyleA1->getFont();       
$objFontA1->setName('宋体');       
$objFontA1->setSize(18);     
$objFontA1->setBold(true);     

   $n++;
   $n++;
  //设置下面的特殊字符样式
 
// $objActSheet->setCellValue('D4', '送货日期');   
// $objActSheet->setCellValue('B'.$n,'票未开：');    $objActSheet->setCellValue('C'.$n, $mysql['so_order_number']);    
//  $n++;
// $objActSheet->setCellValue('B'.$n,'上次结欠：'); $objActSheet->setCellValue('C'.$n, $mysql['so_order_number']);
// $objActSheet->setCellValue('E'.$n,'收到确认后请签字盖章回传，以便开票谢谢！'); 
// $objActSheet->mergeCells("E$n:L$n");         
//   $n++;    
// $objActSheet->setCellValue('B'.$n,'本次结欠：');  $objActSheet->setCellValue('C'.$n, $mysql['so_order_number']); 
// $objActSheet->setCellValue('E'.$n,'FAX： ');
// $objActSheet->mergeCells("E$n:L$n");$n++;  
// $objActSheet->setCellValue('B'.$n,'本期回款：'); $objActSheet->setCellValue('C'.$n, $mysql['so_order_number']);  $n++;  
// $objActSheet->setCellValue('B'.$n,'累积结欠：');    
  
//*************************************       
//输出内容       
//       
  
//$outputFileName = "addminus.xls";       
//到文件       
//$objWriter->save("addminus.xls");       
//$objWriter->save($outputFileName);   
//下面这个输出我是有个页面用Ajax接收返回的信息   
//echo("<a href="tables/"."addminus.xls" mce_href="tables/"."addminus.xls" target='_blank'>点击下载电子表</a>");   

//echo("<a href="addminus.xls" mce_href="addminus.xls" target='_blank'>点击下载电子表</a>");   

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