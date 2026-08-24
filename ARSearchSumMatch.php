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

// $sql = 'select ah.receivenum,ah.receiveamount,ah.taxamount,ah.receivedate,ah.narrative ,ah.customer_code,ve.customer_name,ah.bankchangenum,ah.currency_code,ah.bankaccountname,ah.receive_type,ah.prereceive_used,ah.creation_date,al.receiveline,al.shipment_num,al.amount,al.dis_amount
//             from   ar_receive_headers_all ah,
//              ar_receive_lines_all al,
//             customers ve
//             where  ve.customer_code=ah.customer_code
//             and ve.customer_code =al.customer_code
//             and ah.receivenum=al.receivenum ';
// $sql = 'select ap_invoice_type,ah.invoicenum,ah.invoiceamount,ah.taxamount,ah.invoicedate,ah.narrative
//             ,ah.customer_code,ve.customer_name,ah.created_by,ah.creation_date,ah.currency_code,al.invoicelinenum,al.shipment_num ,al.amount,al.dis_amount 
//             from  ar_invoice_headers_all ah, 
//              ar_invoice_lines_all al,
//             customers ve 
//             where  ve.customer_code=ah.customer_code
//             and al.customer_code=ah.customer_code
//             and ah.invoicenum=al.invoicenum';

  $sql = 'select ap_invoice_type,ve.customer_name,al.shipment_num ,al.amount,al.dis_amount ,arh.receivenum,al.invoicenum,ah.invoiceamount,sdha.delivery_amount,sdha.already_receive_amount,sdha.dis_receive_amount,ah.invoicedate
            from  ar_invoice_headers_all ah, 
                ar_receive_lines_all arh,
             ar_invoice_lines_all al,
             so_delivery_headers_all sdha,
            customers ve 
            where  ve.customer_code=ah.customer_code
            and arh.shipment_num = al.shipment_num
            and al.customer_code=arh.customer_code
            and al.customer_code=ah.customer_code
            and al.customer_code=sdha.customer_code
            and sdha.customer_code=ah.customer_code
            and sdha.delivery_num=al.shipment_num
            and sdha.delivery_num=arh.shipment_num
            and ah.invoicenum=al.invoicenum';
    if (isset($C1) and $C1 != '') {
        $sql = $sql . " and ve.customer_name like   '%" . $C1  . "%' ";
    }
	 if (isset($C2) and $C2 != '') {
        $sql = $sql . " and weituofang like   '%" . $C2  . "%' ";
    }
	 if (isset($C3) and $C3 != '') {
        $sql = $sql . " and tidanhao like   '%" . $C3  . "%' ";
    }
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
$objProps->setSubject("东珠龙旺");       
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
$objActSheet->getColumnDimension('C')->setWidth(20);    
$objActSheet->getColumnDimension('D')->setWidth(20);    
$objActSheet->getColumnDimension('E')->setWidth(20);  
$objActSheet->getColumnDimension('F')->setWidth(15);  
$objActSheet->getColumnDimension('G')->setWidth(20); 
$objActSheet->getColumnDimension('H')->setWidth(20);  
$objActSheet->getColumnDimension('I')->setWidth(20); 
$objActSheet->getColumnDimension('J')->setWidth(20);  
$objActSheet->getColumnDimension('K')->setWidth(50);  
// $objActSheet->getColumnDimension('L')->setWidth(20);  
// $objActSheet->getColumnDimension('M')->setWidth(10); 
 

$objActSheet->getRowDimension(1)->setRowHeight(30);    
$objActSheet->getRowDimension(2)->setRowHeight(30);    
$objActSheet->getRowDimension(3)->setRowHeight(30);    
    
//设置单元格的值     
$objActSheet->setCellValue('A1', '东珠龙旺消防器材有限公司');    
$objActSheet->setCellValue('A3', "日期： $time "); 
$objActSheet->setCellValue('E3', "客户:"); 
$objActSheet->setCellValue('F3', "$C1"); 
$objActSheet->mergeCells('F3:K3'); 

//合并单元格   
$objActSheet->mergeCells('A1:L1'); 
// $objActSheet->getStyle('A'. 1)->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   

$objActSheet->mergeCells('A3:C3');    

$objActSheet->setCellValue('A2', "  销售发票清单");    
//合并单元格   
$objActSheet->mergeCells('A2:L2');  
//设置样式   
$objStyleA1 = $objActSheet->getStyle('A1');       
$objStyleA1->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);   
$objFontA1 = $objStyleA1->getFont();       
$objFontA1->setName('宋体');       
$objFontA1->setSize(18);     
$objFontA1->setBold(true);       

$objStyleA1 = $objActSheet->getStyle('E3');       
$objStyleA1->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);   
$objFontA1 = $objStyleA1->getFont();       
$objFontA1->setName('宋体');       
$objFontA1->setSize(18);     
$objFontA1->setBold(true); 

$objStyleA1 = $objActSheet->getStyle('F3');       
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
$objActSheet->setCellValue('B4', '日期');    
$objActSheet->setCellValue('C4', '送货单编号');    
$objActSheet->setCellValue('D4', '发票编号');   
$objActSheet->setCellValue('E4', '发票金额');    
$objActSheet->setCellValue('F4', '收款单号');    
$objActSheet->setCellValue('G4', '收款方式');    
$objActSheet->setCellValue('H4', '收款金额');   
$objActSheet->setCellValue('I4', '结欠金额');    
$objActSheet->setCellValue('J4', '货款调整');    
$objActSheet->setCellValue('K4', '备注');    
// $objActSheet->setCellValue('L4', ' ');   
// $objActSheet->setCellValue('M4', ' ');    
// $objActSheet->setCellValue('N2', '应付USD');    
// $objActSheet->setCellValue('O2', '应付汇率');    
// $objActSheet->setCellValue('P2', '应付开票日期');   
// $objActSheet->setCellValue('Q2', '应付销账日期');    
 
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
    // $objActSheet->getStyle('L4')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    // $objActSheet->getStyle('L4')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    // $objActSheet->getStyle('L4')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );  
    // $objActSheet->getStyle('L4')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );  
    // $objActSheet->getStyle('M4')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    // $objActSheet->getStyle('M4')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    // $objActSheet->getStyle('M4')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    // $objActSheet->getStyle('M4')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );  
    
  
  
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
    // $objActSheet->getStyle('L'.$n)->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    // $objActSheet->getStyle('L'.$n)->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    // $objActSheet->getStyle('L'.$n)->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    // $objActSheet->getStyle('L'.$n)->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN ); 

    // $objActSheet->getStyle('M'.$n)->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    // $objActSheet->getStyle('M'.$n)->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    // $objActSheet->getStyle('M'.$n)->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    // $objActSheet->getStyle('M'.$n)->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN ); 
 
 
    $invoicedate= date('Y-m-d',$mysql['invoicedate']); 
    $creation_date= date('Y-m-d h:i:s',$mysql['creation_date']); 
    $jieqian = $mysql['delivery_amount']-$mysql['already_receive_amount'] -$mysql['dis_receive_amount'];
    $objActSheet->setCellValue('A'.$n, $i);    
    $objActSheet->setCellValue('B'.$n, $invoicedate );    
    $objActSheet->setCellValue('C'.$n, $mysql['shipment_num']);    
    $objActSheet->setCellValue('D'.$n,  $mysql['invoicenum']);   
	$objActSheet->setCellValue('E'.$n, $mysql['invoiceamount']);  
	$objActSheet->setCellValue('F'.$n, $mysql['receivenum']);    
    $objActSheet->setCellValue('G'.$n, $mysql['ap_invoice_type']);    
    $objActSheet->setCellValue('H'.$n, $mysql['amount']);    
    $objActSheet->setCellValue('I'.$n, $jieqian);   
	$objActSheet->setCellValue('J'.$n,  $mysql[' '] );  
	$objActSheet->setCellValue('K'.$n, $mysql['narrative'] );    
    // $objActSheet->setCellValue('L'.$n,  $mysql['']);   
	// $objActSheet->setCellValue('M'.$n, $mysql['vendor2furmb']);
	// // $objActSheet->setCellValue('N'.$n, $mysql['vendor2yingfuusd']);  
 //   $total_amount  = $total_amount+$mysql['line_amount'];
 
    $i++; 
    $n++;   
} 
//  $objActSheet->setCellValue('B'.$n,'合计：');    $objActSheet->setCellValue('C'.$n, $total_amount); 

//  $objStyleA1 = $objActSheet->getStyle("B$n");       
// $objStyleA1->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);   
// $objFontA1 = $objStyleA1->getFont();       
// $objFontA1->setName('宋体');       
// $objFontA1->setSize(18);     
// $objFontA1->setBold(true);       
//  $objStyleA1 = $objActSheet->getStyle("C$n");       
// $objStyleA1->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);   
// $objFontA1 = $objStyleA1->getFont();       
// $objFontA1->setName('宋体');       
// $objFontA1->setSize(18);     
// $objFontA1->setBold(true);     

   $n++;
   $n++;
  //设置下面的特殊字符样式
 
// // $objActSheet->setCellValue('D4', '送货日期');   
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