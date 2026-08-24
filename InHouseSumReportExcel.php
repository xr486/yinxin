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
if (isset($_GET['C5'])) {
    $C5 = $_GET['C5'];
} else if (isset($_POST['C5'])) {
    $C5 = $_POST['C5'];
}
if (isset($_GET['C6'])) {
    $C6 = $_GET['C6'];
} else if (isset($_POST['C6'])) {
    $C6 = $_POST['C6'];
}

if (isset($_GET['C7'])) {
    $C7 = $_GET['C7'];
} else if (isset($_POST['C7'])) {
    $C7 = $_POST['C7'];
}

if (isset($_GET['C8'])) {
    $C8 = $_GET['C8'];
} else if (isset($_POST['C8'])) {
    $C8 = $_POST['C8'];
}


if (isset($_GET['C9'])) {
    $C9 = $_GET['C9'];
} else if (isset($_POST['C9'])) {
    $C9 = $_POST['C9'];
}

if (isset($_GET['C10'])) {
    $C10 = $_GET['C10'];
} else if (isset($_POST['C10'])) {
    $C10 = $_POST['C10'];
}

if (isset($_GET['C11'])) {
    $C11 = $_GET['C11'];
} else if (isset($_POST['C11'])) {
    $C11 = $_POST['C11'];
}

 /*  $sql = "SELECT a.transaction_type,c.item_no, a.uom, a.subinventory_from,a.request_person,d.loccode, d.locationname,   c.item_name,c.item_desc,(a.quantity) quantity
FROM inv_transactions_all a,   sf_item_no c, locations d
WHERE a.item_no = c.item_no 
and  a.transaction_type in (select type_name from mtl_transaction_type)
AND a.subinventory_from = d.loccode "; */
$sql = "SELECT a.transaction_type,c.item_no, a.uom, a.subinventory_from,a.request_person,d.loccode, d.locationname, b.employee_num, b.employee_name, c.item_desc,c.item_name,(a.quantity) quantity
FROM inv_transactions_all a, hr_employees b, sf_item_no c, locations d
WHERE a.request_person = b.employee_num
AND a.item_no = c.item_no 
and  a.transaction_type in (select type_name from mtl_transaction_type)
AND a.subinventory_from = d.loccode ";

if (isset($C1) and $C1 != '')
{
  $sql = $sql." and b.employee_name ".LIKE." '%".$C1."%' ";
}
if (isset($C2) and $C2 != '')
{
  $sql = $sql." and b.employee_num ".LIKE." '%".$C2."%' ";
}
if (empty($C3)==0) 
{
    $SQL_fromdate = strtotime( $C3);
    $sql .= " and a.transaction_date >= '" . $SQL_fromdate . "' ";
}
if (empty($C4)==0) {
    $SQL_ToDate = strtotime( $C4) + 86400;
    $sql .= " and a.transaction_date <='" . $SQL_ToDate . "' ";
}
if (isset($C5) and $C5 != '') 
{
	$sql = $sql . " and c.item_no >=  '" . $C5 . "'";
}
if (isset($C6) and $C6 != '') 
{
	$sql = $sql . " and c.item_no <=  '" . $C6  . "' ";
}
if (isset($C7) and $C7 != '') 
{
  $sql = $sql . " and a.subinventory_from >=  '" . $C7 . "'";
}
if (isset($C8) and $C8 != '') 
{
   $sql = $sql . " and a.subinventory_from <=  '" . $C8 . "' ";
}
if (isset($C9) and $C9 != '') 
{
	$sql = $sql . " and a.trans_num >=  '" . $C9 . "'";
}

if (isset($C10) and $C10 != '') 
{
  $sql = $sql . " and  a.trans_num <=  '" . $C10 . "' ";
}
if (isset($C11) and $C11 != '') 
{
   $sql = $sql . " and  a.transaction_type =  '" . $C11 . "' ";
}

  //$sql = $sql . "group by a.transaction_type,c.item_no, a.uom, a.subinventory_from,a.request_person,d.loccode, d.locationname, a.request_person,c.item_name, c.item_desc ";
  $sql = $sql . "group by a.transaction_type,c.item_no, a.uom, a.subinventory_from,a.request_person,d.loccode, d.locationname, b.employee_num, b.employee_name,c.item_desc, c.item_name ";
  $result = DB_query($sql,$db);
    if (DB_num_rows($result)==0) {
        unset($result);
        prnMsg(_('找不到该来料报检单，请重新输入条件查询！') ,'error');
    }

// 创建一个处理对象实例       
$objExcel = new PHPExcel();       
      
// 创建文件格式写入对象实例, uncomment       
$objWriter = new PHPExcel_Writer_Excel5($objExcel);      
    
//设置文档基本属性       
$objProps = $objExcel->getProperties();       
$objProps->setCreator("Shunfansoft");       
$objProps->setLastModifiedBy("Shunfansoft");       
$objProps->setTitle("Residents pepole List");       
$objProps->setSubject("苏州鼎天");       
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
$objActSheet->getColumnDimension('B')->setWidth(16);    
$objActSheet->getColumnDimension('C')->setWidth(10);    
$objActSheet->getColumnDimension('D')->setWidth(10);    
$objActSheet->getColumnDimension('E')->setWidth(12);  
$objActSheet->getColumnDimension('F')->setWidth(10);  
$objActSheet->getColumnDimension('G')->setWidth(5); 
$objActSheet->getColumnDimension('H')->setWidth(5);  
$objActSheet->getColumnDimension('I')->setWidth(8);  
$objActSheet->getColumnDimension('J')->setWidth(12);  
$objActSheet->getColumnDimension('K')->setWidth(12);  
// $objActSheet->getColumnDimension('L')->setWidth(10);  
// $objActSheet->getColumnDimension('M')->setWidth(10);
// $objActSheet->getColumnDimension('N')->setWidth(10);
// $objActSheet->getColumnDimension('O')->setWidth(10);
// $objActSheet->getColumnDimension('P')->setWidth(10);
//$objActSheet->getColumnDimension('Q')->setWidth(15); 



$objActSheet->getRowDimension(1)->setRowHeight(30);    
// $objActSheet->getRowDimension(2)->setRowHeight(30);    
// $objActSheet->getRowDimension(3)->setRowHeight(30);    
    
//设置单元格的值     
$objActSheet->setCellValue('A1', '杂项出入库明细表');    
//合并单元格  
$objActSheet->mergeCells('A1:K1'); 
 

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
$objFontA1->setBold(true);  
$objStyleA1 = $objActSheet->getStyle('B2');       
$objStyleA1->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);   
$objFontA1 = $objStyleA1->getFont();       
$objFontA1->setName('宋体');           
$objFontA1->setBold(true);  
$objStyleA1 = $objActSheet->getStyle('C2');       
$objStyleA1->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);   
$objFontA1 = $objStyleA1->getFont();       
$objFontA1->setName('宋体');           
$objFontA1->setBold(true);
$objStyleA1 = $objActSheet->getStyle('D2');       
$objStyleA1->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);   
$objFontA1 = $objStyleA1->getFont();       
$objFontA1->setName('宋体');           
$objFontA1->setBold(true);        
$objStyleA1 = $objActSheet->getStyle('E2');       
$objStyleA1->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);   
$objFontA1 = $objStyleA1->getFont();       
$objFontA1->setName('宋体');           
$objFontA1->setBold(true);  
$objStyleA1 = $objActSheet->getStyle('F2');       
$objStyleA1->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);   
$objFontA1 = $objStyleA1->getFont();       
$objFontA1->setName('宋体');           
$objFontA1->setBold(true);  
$objStyleA1 = $objActSheet->getStyle('G2');       
$objStyleA1->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);   
$objFontA1 = $objStyleA1->getFont();       
$objFontA1->setName('宋体');           
$objFontA1->setBold(true);
$objStyleA1 = $objActSheet->getStyle('H2');       
$objStyleA1->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);   
$objFontA1 = $objStyleA1->getFont();       
$objFontA1->setName('宋体');           
$objFontA1->setBold(true);    
$objStyleA1 = $objActSheet->getStyle('I2');       
$objStyleA1->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);   
$objFontA1 = $objStyleA1->getFont();       
$objFontA1->setName('宋体');           
$objFontA1->setBold(true);  
$objStyleA1 = $objActSheet->getStyle('J2');       
$objStyleA1->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);   
$objFontA1 = $objStyleA1->getFont();       
$objFontA1->setName('宋体');           
$objFontA1->setBold(true);  
$objStyleA1 = $objActSheet->getStyle('K2');       
$objStyleA1->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);   
$objFontA1 = $objStyleA1->getFont();       
$objFontA1->setName('宋体');           
$objFontA1->setBold(true);  

// $objStyleA1 = $objActSheet->getStyle('A2');       
// $objStyleA1->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);   
// $objFontA1 = $objStyleA1->getFont();       
// $objFontA1->setName('宋体');       
// $objFontA1->setSize(12);     
// $objFontA1->setBold(true);     

// $objStyleA1 = $objActSheet->getStyle('A3');       
// $objStyleA1->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);   
// $objFontA1 = $objStyleA1->getFont();       
// $objFontA1->setName('宋体');       
// $objFontA1->setSize(10);     
// $objFontA1->setBold(true);



$objStyleA1 = $objActSheet->getStyle('B4');       
$objStyleA1->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);   
$objFontA1 = $objStyleA1->getFont();       
      
$objStyleA1 = $objActSheet->getStyle('B5');       
$objStyleA1->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);   
$objFontA1 = $objStyleA1->getFont();       
 
$objStyleA1 = $objActSheet->getStyle('B6');       
$objStyleA1->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);   
$objFontA1 = $objStyleA1->getFont();       
 
$objStyleA1 = $objActSheet->getStyle('B7');       
$objStyleA1->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);   
$objFontA1 = $objStyleA1->getFont();       
 
$objStyleA1 = $objActSheet->getStyle('B8');       
$objStyleA1->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);   
$objFontA1 = $objStyleA1->getFont();       
 



// $objStyleA1 = $objActSheet->getStyle('A11');       
// $objStyleA1->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);   
// $objFontA1 = $objStyleA1->getFont();       
// $objFontA1->setBold(true);

// $objStyleA1 = $objActSheet->getStyle('B11');       
// $objStyleA1->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);   
// $objFontA1 = $objStyleA1->getFont();       
// $objFontA1->setBold(true);

// $objStyleA1 = $objActSheet->getStyle('C11');       
// $objStyleA1->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);   
// $objFontA1 = $objStyleA1->getFont();       
// $objFontA1->setBold(true);

// $objStyleA1 = $objActSheet->getStyle('D11');       
// $objStyleA1->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);   
// $objFontA1 = $objStyleA1->getFont();       
// $objFontA1->setBold(true);

// $objStyleA1 = $objActSheet->getStyle('E11');       
// $objStyleA1->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);   
// $objFontA1 = $objStyleA1->getFont();       
// $objFontA1->setBold(true);

// $objStyleA1 = $objActSheet->getStyle('F11');       
// $objStyleA1->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);   
// $objFontA1 = $objStyleA1->getFont();       
// $objFontA1->setBold(true);

// $objStyleA1 = $objActSheet->getStyle('G11');       
// $objStyleA1->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);   
// $objFontA1 = $objStyleA1->getFont();       
// $objFontA1->setBold(true);


// $objStyleA1 = $objActSheet->getStyle('H11');       
// $objStyleA1->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);   
// $objFontA1 = $objStyleA1->getFont();       
// $objFontA1->setBold(true);


// $objStyleA1 = $objActSheet->getStyle('I11');       
// $objStyleA1->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);   
// $objFontA1 = $objStyleA1->getFont();       
// $objFontA1->setBold(true);


// $objStyleA1 = $objActSheet->getStyle('J11');       
// $objStyleA1->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);   
// $objFontA1 = $objStyleA1->getFont();       
// $objFontA1->setBold(true);

// $objStyleA1 = $objActSheet->getStyle('K11');       
// $objStyleA1->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);   
// $objFontA1 = $objStyleA1->getFont();       
// $objFontA1->setBold(true);


// $objStyleA1 = $objActSheet->getStyle('L11');       
// $objStyleA1->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);   
// $objFontA1 = $objStyleA1->getFont();       
// $objFontA1->setBold(true);


// $objStyleA1 = $objActSheet->getStyle('M11');       
// $objStyleA1->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);   
// $objFontA1 = $objStyleA1->getFont();       
// $objFontA1->setBold(true);


// $objStyleA1 = $objActSheet->getStyle('N11');       
// $objStyleA1->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);   
// $objFontA1 = $objStyleA1->getFont();       
// $objFontA1->setBold(true);

// $objStyleA1 = $objActSheet->getStyle('O11');       
// $objStyleA1->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);   
// $objFontA1 = $objStyleA1->getFont();       
// $objFontA1->setBold(true);

// $objStyleA1 = $objActSheet->getStyle('P11');       
// $objStyleA1->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);   
// $objFontA1 = $objStyleA1->getFont();       
// $objFontA1->setBold(true);



$objStyleA1 = $objActSheet->getStyle('C4');       
$objStyleA1->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);   
$objFontA1 = $objStyleA1->getFont();       
      
$objStyleA1 = $objActSheet->getStyle('C5');       
$objStyleA1->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);   
$objFontA1 = $objStyleA1->getFont();       
 
$objStyleA1 = $objActSheet->getStyle('C6');       
$objStyleA1->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);   
$objFontA1 = $objStyleA1->getFont();       
 
$objStyleA1 = $objActSheet->getStyle('C7');       
$objStyleA1->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);   
$objFontA1 = $objStyleA1->getFont();       
 
$objStyleA1 = $objActSheet->getStyle('C8');       
$objStyleA1->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);   
$objFontA1 = $objStyleA1->getFont();    

//设置居中对齐   
//$objActSheet->getStyle('A4')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);   
//$objActSheet->getStyle('B4')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);   
//$objActSheet->getStyle('C4')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);   
//$objActSheet->getStyle('D4')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER); 
$objActSheet->setCellValue('A2', '序号');   
$objActSheet->setCellValue('B2', '交易类型');   
$objActSheet->setCellValue('C2', '料号');    
$objActSheet->setCellValue('D2', '料号名称'); 
$objActSheet->setCellValue('E2', '规格型号');    
$objActSheet->setCellValue('F2', '交易数量');    
$objActSheet->setCellValue('G2', '单位');
$objActSheet->setCellValue('H2', '仓库');    
$objActSheet->setCellValue('I2', '仓库');  
$objActSheet->setCellValue('J2', '申请人工号');    
$objActSheet->setCellValue('K2', '申请人名称');    
 
  

 
//$objActSheet->setCellValue('G4', '数量小计');   
 //设置边框   
    $objActSheet->getStyle('A2')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('A2')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('A2')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('A2')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('B2')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('B2')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('B2')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('B2')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('C2')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('C2')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN ); 

    $objActSheet->getStyle('C2')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('C2')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('D2')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('D2')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('D2')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('D2')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN ); 
	$objActSheet->getStyle('E2')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('E2')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('E2')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('E2')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('F2')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('F2')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('F2')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('F2')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('G2')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('G2')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('G2')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('G2')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('H2')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('H2')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('H2')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('H2')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('I2')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('I2')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('I2')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('I2')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('J2')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('J2')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('J2')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('J2')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN ); 
    $objActSheet->getStyle('K2')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('K2')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('K2')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('K2')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN ); 
 //    $objActSheet->getStyle('L11')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
 //    $objActSheet->getStyle('L11')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
 //    $objActSheet->getStyle('L11')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
 //    $objActSheet->getStyle('L11')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN ); 
	// $objActSheet->getStyle('M11')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
 //    $objActSheet->getStyle('M11')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
 //    $objActSheet->getStyle('M11')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
 //    $objActSheet->getStyle('M11')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN ); 
 //    $objActSheet->getStyle('N11')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
 //    $objActSheet->getStyle('N11')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
 //    $objActSheet->getStyle('N11')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
 //    $objActSheet->getStyle('N11')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN ); 
 //    $objActSheet->getStyle('O11')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
 //    $objActSheet->getStyle('O11')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
 //    $objActSheet->getStyle('O11')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
 //    $objActSheet->getStyle('O11')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN ); 
 //    $objActSheet->getStyle('P11')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
 //    $objActSheet->getStyle('P11')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
 //    $objActSheet->getStyle('P11')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
 //    $objActSheet->getStyle('P11')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN ); 
//    $objActSheet->getStyle('Q11')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
//    $objActSheet->getStyle('Q11')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
//    $objActSheet->getStyle('Q11')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
//    $objActSheet->getStyle('Q11')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN ); 
//  

	
 
$result1 =DB_query($sql,$db,$ErrMsg,$DbgMsg);
$i=1;   
//从数据库取值循环输出   
while($mysql=DB_fetch_array($result1))
{   
//$FirstName=$mysql['FirstName'];   
//$ID=$mysql['LastName'];   
//$LastName=$mysql['LastName'];   
//$Ward=$mysql['Ward'];    
  
    $n=$i+2;   
       
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
    //设置数字类型文本
    $objActSheet->getStyle('C'.$n)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

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
    // $objActSheet->getStyle('L'.$n)->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    // $objActSheet->getStyle('L'.$n)->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    // $objActSheet->getStyle('L'.$n)->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    // $objActSheet->getStyle('L'.$n)->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    // $objActSheet->getStyle('M'.$n)->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    // $objActSheet->getStyle('M'.$n)->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    // $objActSheet->getStyle('M'.$n)->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    // $objActSheet->getStyle('M'.$n)->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    // $objActSheet->getStyle('N'.$n)->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    // $objActSheet->getStyle('N'.$n)->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    // $objActSheet->getStyle('N'.$n)->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    // $objActSheet->getStyle('N'.$n)->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    // $objActSheet->getStyle('O'.$n)->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    // $objActSheet->getStyle('O'.$n)->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    // $objActSheet->getStyle('O'.$n)->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    // $objActSheet->getStyle('O'.$n)->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    // $objActSheet->getStyle('P'.$n)->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    // $objActSheet->getStyle('P'.$n)->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    // $objActSheet->getStyle('P'.$n)->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    // $objActSheet->getStyle('P'.$n)->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
//    $objActSheet->getStyle('Q'.$n)->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
//    $objActSheet->getStyle('Q'.$n)->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
//    $objActSheet->getStyle('Q'.$n)->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
//    $objActSheet->getStyle('Q'.$n)->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
//   
   
  
    unset($v_status);
	if ($mysql['status'] == 'process') {
		$v_status = '待对账';
	} elseif ($mysql['status'] == 'checked') {
		$v_status = '已对账';
	} elseif ($mysql['status'] == 'invoiced') {
		$v_status = '已开票';
	} elseif ($mysql['status'] == 'payment') {
		$v_status = '已付款';
	}   else {
		$v_status = '';
	}

    if ( $mysql['check_date'] == null )
	{
	   $checkdate = ' ';
	}
	else
	{
	   $checkdate = date ('Y-m-d',$mysql['check_date']);
	}

       
    $objActSheet->setCellValue('A'.$n, $i);
	  $objActSheet->setCellValue('B'.$n,$mysql['transaction_type']); 
	//$objActSheet->setCellValue('C'.$n, $mysql['tracking_number']);  
	  $objActSheet->setCellValue('C'.$n, ' '.$mysql['item_no']);    
    $objActSheet->setCellValue('D'.$n, $mysql['item_name']);
	  $objActSheet->setCellValue('E'.$n, $mysql['item_desc']);
	  $objActSheet->setCellValue('F'.$n, $mysql['quantity']);  
	  $objActSheet->setCellValue('G'.$n, $mysql['uom']);    
    $objActSheet->setCellValue('H'.$n, $mysql['loccode']);    
    $objActSheet->setCellValue('I'.$n, $mysql['locationname']); 
	  $objActSheet->setCellValue('J'.$n, $mysql['request_person']);    
    $objActSheet->setCellValue('K'.$n, $mysql['employee_name']);    
   
    $total_quantity  = $total_quantity+$mysql['check_quantity'];
    $total_amount  = $total_amount+$mysql['check_amount'];
    $i++; 
    $n++;   
} 

$objStyleA1 = $objActSheet->getStyle("B$n");       
$objStyleA1->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);   
$objFontA1 = $objStyleA1->getFont();       
$objFontA1->setName('宋体');       
$objFontA1->setSize(10);     
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