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
$mytime= date("Y", strtotime("-1 year"));
$a = strtotime('Y-m-d',$mytime-12-31);
echo $a;
echo strtotime('Y-m-d',$mytime.'-12-31');
// echo date('Y-m-d',$a);
// date('Y-m-d',)
 
 // $sql = "SELECT a.delivery_num,b.delivery_date,a.price,a.uom ,a.stockid,a.line_amount,a.delivery_quantity,a.so_order_number,c.item_desc,d.customer_name
 //  FROM so_delivery_all a,so_delivery_headers_all b, sf_item_no c,customers d
 //  WHERE b.delivery_num = a.delivery_num AND c.item_no = a.stockid  AND d.customer_code = b.customer_code";
$mytime= date("Y", strtotime("-1 year"));
$sql_todate=strtotime($mytime.'-12-31');
//     $sql ="SELECT pr.customer_code,ve.customer_name,pr.*
//                     FROM  so_delivery_headers_all pr,
// 						   customers ve
//                    where  pr.customer_code=ve.customer_code
// 				   and delivery_amount > already_receive_amount + dis_receive_amount " ;
 

// $sql = "SELECT ve.vendor_code,ve.vendor_name, pr.*,
// 			sum(pr.delivery_amount-pr.already_receive_amount -pr.dis_receive_amount),
// 			sum(pr.delivery_amount-pr.already_invoice_amount -pr.dis_invoice_amount) 
// 	FROM vendors ve, so_delivery_headers_all pr 
// 	where pr.vendor_code=ve.vendor_code 
// 	and  pr.delivery_amount > pr.already_receive_amount +pr.dis_receive_amount 
// 	AND pr.delivery_date > '". $sql_todate . "'
// 	group by ve.vendor_code,ve.vendor_name";
// 	echo $sql;
$sql = "SELECT vendor_code,vendor_name,jieqian_amount,kaipiaoweishou_amount FROM vendors";
 
 
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
$objProps->setSubject("东珠龙旺消防器材有限公司");       
$objProps->setDescription("www.Shunfansoft.com");       
$objProps->setKeywords("Residents");       
$objProps->setCategory("Residents Reports"); 


// $objProps = $objExcel->getProperties();       
// $objProps->setCreator("Shunfansoft");       
// $objProps->setLastModifiedBy("Shunfansoft");       
// $objProps->setTitle("Residents pepole List");       
// $objProps->setSubject("'.$C1.'");       
// $objProps->setDescription("www.Shunfansoft.com");       
// $objProps->setKeywords("Residents");       
// $objProps->setCategory("Residents Reports");  

// $objProps = $objExcel->getProperties();       
// $objProps->setCreator("Shunfansoft");       
// $objProps->setLastModifiedBy("Shunfansoft");       
// $objProps->setTitle("Residents pepole List");       
// $objProps->setSubject("$time");       
// $objProps->setDescription("www.Shunfansoft.com");       
// $objProps->setKeywords("Residents");       
// $objProps->setCategory("Residents Reports");        

      
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
$objActSheet->getColumnDimension('A')->setWidth(20);    
$objActSheet->getColumnDimension('B')->setWidth(40);    
$objActSheet->getColumnDimension('C')->setWidth(20);    
$objActSheet->getColumnDimension('D')->setWidth(20);    
$objActSheet->getColumnDimension('E')->setWidth(20);  
$objActSheet->getColumnDimension('F')->setWidth(20);  
$objActSheet->getColumnDimension('G')->setWidth(20); 
$objActSheet->getColumnDimension('H')->setWidth(20);  
$objActSheet->getColumnDimension('I')->setWidth(20); 
$objActSheet->getColumnDimension('J')->setWidth(20);  
$objActSheet->getColumnDimension('K')->setWidth(20);  
$objActSheet->getColumnDimension('L')->setWidth(20);  
// $objActSheet->getColumnDimension('M')->setWidth(10); 
 

$objActSheet->getRowDimension(1)->setRowHeight(30);    
$objActSheet->getRowDimension(2)->setRowHeight(30);    
$objActSheet->getRowDimension(3)->setRowHeight(30);    
    
//设置单元格的值     
$objActSheet->setCellValue('A1', '东珠龙旺消防器材有限公司');    
$objActSheet->setCellValue('A3', "日期");    
$objActSheet->setCellValue('B3', "$time");    
//合并单元格   
$objActSheet->mergeCells('A1:L1'); 
// $objActSheet->mergeCells('C3:M3'); 
// $objActSheet->getStyle('A'. 1)->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   

// $objActSheet->mergeCells('A3:B3');    
$objActSheet->mergeCells('B3:C3');    

$objActSheet->setCellValue('A2', "采购应付款统计");    
//合并单元格   
$objActSheet->mergeCells('A2:L2');  
//设置样式   
$objStyleA1 = $objActSheet->getStyle('A1');       
$objStyleA1->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);   
$objFontA1 = $objStyleA1->getFont();       
$objFontA1->setName('宋体');       
$objFontA1->setSize(18);     
$objFontA1->setBold(true);  

$objStyleA1 = $objActSheet->getStyle('B3');       
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
$objActSheet->getStyle('E4')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER); 
$objActSheet->getStyle('K4')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);   
//$objActSheet->getStyle('D4')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);   
  
$objActSheet->setCellValue('A4', 'NO');    
$objActSheet->setCellValue('B4', '供应商名称');    
$objActSheet->setCellValue('C4', '上年结余'); 
// $objActSheet->setCellValue('A2', "销售应收款统计"); 
$objActSheet->mergeCells('C4:D4');        
$objActSheet->setCellValue('E4', '本期金额');    
$objActSheet->mergeCells('E4:H4');     
$objActSheet->setCellValue('I4', '累计结欠金额');    
$objActSheet->setCellValue('J4', '本期开票');    
$objActSheet->setCellValue('K4', '发票累计'); 
$objActSheet->mergeCells('K4:L4');  

$objActSheet->setCellValue('C5', '结欠金额');    
$objActSheet->setCellValue('D5', '开票未付金额');    
$objActSheet->setCellValue('E5', '本期进货');    
$objActSheet->setCellValue('F5', '本期退货');   
$objActSheet->setCellValue('G5', '本期已付');   
$objActSheet->setCellValue('H5', '本期结欠');   
$objActSheet->setCellValue('K5', '开票金额');   
$objActSheet->setCellValue('L5', '开票未付金额');   
 
//设置边框   
    $objActSheet->getStyle('A4')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('A4')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('A4')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
     $objActSheet->getStyle('A5')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('A5')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN ); 
    $objActSheet->getStyle('A5')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('B4')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('B4')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('B4')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );
     $objActSheet->getStyle('B5')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('B5')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('B4')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('C4')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('C4')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('C4')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );  
     $objActSheet->getStyle('C5')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('C4')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('D4')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('D4')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('D4')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );  
     $objActSheet->getStyle('D5')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN ); 
    $objActSheet->getStyle('D4')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN ); 
	$objActSheet->getStyle('E4')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('E4')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('E4')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
       $objActSheet->getStyle('E5')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('E5')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('E4')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('F4')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('F4')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('F4')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
     $objActSheet->getStyle('F5')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('F5')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('F4')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('G4')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('G4')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('G4')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN ); 
     $objActSheet->getStyle('G5')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('G5')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('G4')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('H4')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('H4')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('H4')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('H4')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN ); 
    $objActSheet->getStyle('H5')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );  
    $objActSheet->getStyle('H5')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN ); 
    $objActSheet->getStyle('H5')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN ); 
	$objActSheet->getStyle('I4')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('I4')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('I4')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN ); 
    $objActSheet->getStyle('I5')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('I5')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('I4')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('J4')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('J4')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('J4')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('J5')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('J5')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );
    $objActSheet->getStyle('J4')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('K4')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('K4')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('K4')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('K5')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('K5')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );  
    $objActSheet->getStyle('K4')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('L4')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('L4')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('L4')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );  
     $objActSheet->getStyle('L5')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    $objActSheet->getStyle('L5')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN ); 
    $objActSheet->getStyle('L4')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );  
     
    
  
  
//$query=$data->query($sql);   
$result1 =DB_query($sql,$db,$ErrMsg,$DbgMsg);
$i=1;   
//从数据库取值循环输出   
while($mysql=DB_fetch_array($result1)){   
$FirstName=$mysql['FirstName'];   
$ID=$mysql['LastName'];   
$LastName=$mysql['LastName'];   
$Ward=$mysql['Ward'];    
  
  if (empty($C3)==0) {
    $SQL_FromDate = strtotime( $C3 );
    // echo $SQL_FromDate;
    $sql .= " and  delivery_date >= '" . $SQL_FromDate . "' ";
}
if (empty($C4)==0) {
    $SQL_ToDate = strtotime( $C4) + 86400;
     //echo $SQL_ToDate;
    $sql .= " and  delivery_date <='" . $SQL_ToDate . "' ";
}

    $n=$i+5; 
    //前期未收款
	 $sql_no_receive_amount =   "SELECT sum(need_payment_amount-already_payment_amount - dis_payment_amount) no_receive_amount 
	FROM po_rcv_receipt_all 
	WHERE vendor_code =  '".$mysql['vendor_code']."' 
	AND need_payment_amount <> (already_payment_amount + dis_payment_amount )
	AND delivery_date < '". $sql_todate . "'";
    //前期未开票
    $sql_no_invoice_amount = "SELECT sum(need_payment_amount-already_invoice_amount -dis_invoice_amount) no_invoice_amount 
	FROM po_rcv_receipt_all 
	WHERE vendor_code =  '".$mysql['vendor_code']."' 
	AND need_payment_amount <> (already_invoice_amount + dis_invoice_amount)  
	AND delivery_date < '". $sql_todate . "'";



	//本期出货
    $sql_chuhuo = "SELECT sum(need_payment_amount) header_amont FROM po_rcv_receipt_all 
	WHERE vendor_code = '".$mysql['vendor_code']."' 
	and delivery_date >= '". $SQL_FromDate . "'
	and delivery_date <= '". $SQL_ToDate . "'
	and need_payment_amount>0 ";

	//本期退货
	$sql_tuihuo = "SELECT sum(need_payment_amount) header_amont
	FROM po_rcv_receipt_all
	where vendor_code= '".$mysql['vendor_code']."'
	and delivery_date >= '". $SQL_FromDate . "'
	and delivery_date <= '". $SQL_ToDate . "'
	and need_payment_amount<0 " ;
    //本期已收
    $sql_benqi_receive_amount = "SELECT sum(already_invoice_amount) already_invoice_amount, sum( already_payment_amount ) already_receive_amount, sum(need_payment_amount-already_payment_amount -dis_payment_amount) to_receive_amount 
	FROM po_rcv_receipt_all
	where vendor_code= '".$mysql['vendor_code']."'
	and delivery_date >= '". $SQL_FromDate . "'
	and delivery_date <= '". $SQL_ToDate . "'  " ;


    //本期结欠
    $sql_leiji_no_receive_amount = "SELECT sum(need_payment_amount-already_payment_amount -dis_payment_amount) to_receive_amount 
	FROM po_rcv_receipt_all 
	WHERE vendor_code =  '".$mysql['vendor_code']."' 
	and delivery_date >= '". $SQL_FromDate . "'
	and delivery_date <= '". $SQL_ToDate . "'
	AND need_payment_amount <> (already_payment_amount + dis_payment_amount)  ";

	 $sql_leiji_amount = "SELECT sum(already_invoice_amount) already_invoice_amount 
	FROM po_rcv_receipt_all 	
	WHERE vendor_code =  '".$mysql['vendor_code']."' 
	and delivery_date >= '". $SQL_FromDate . "'
	and delivery_date <= '". $SQL_ToDate . "'  ";

  $sql_leiji_no_invoice = "SELECT sum(need_payment_amount-already_invoice_amount -dis_invoice_amount) to_invoice_amount 
  FROM po_rcv_receipt_all
  WHERE vendor_code =  '".$mysql['vendor_code']."' 
  and delivery_date >= '". $SQL_FromDate . "'
	and delivery_date <= '". $SQL_ToDate . "'
  AND need_payment_amount > (already_invoice_amount + dis_invoice_amount)  ";
 
  $result5 =DB_query($sql_no_receive_amount,$db,$ErrMsg,$DbgMsg);
  $mysql_no_receive_amount=DB_fetch_array($result5);//no_receive_amount
  $result6 =DB_query($sql_no_invoice_amount,$db,$ErrMsg,$DbgMsg);
  $mysql_no_invoice_amount=DB_fetch_array($result6);//no_invoice_amount


  $result2 =DB_query($sql_chuhuo,$db,$ErrMsg,$DbgMsg);
  $mysql_chuhuo=DB_fetch_array($result2);//header_amont
  $result4 =DB_query($sql_tuihuo,$db,$ErrMsg,$DbgMsg);
  $mysql_tuihuo=DB_fetch_array($result4); //already_invoice_amount  delivery_amount dis_receive_amount already_receive_amount
 

  
  $result7 =DB_query($sql_benqi_receive_amount,$db,$ErrMsg,$DbgMsg);
  $sql_benqi_receive_amount=DB_fetch_array($result7);//to_receive_amount


  $result7 =DB_query($sql_leiji_no_receive_amount,$db,$ErrMsg,$DbgMsg);
  $mysql_leiji_no_receive_amount=DB_fetch_array($result7);//to_receive_amount

  $result7 =DB_query($sql_leiji_amount,$db,$ErrMsg,$DbgMsg);
  $mysql_leiji_amount=DB_fetch_array($result7);//to_receive_amount

  //$result8 =DB_query($sql_leiji_no_invoice,$db,$ErrMsg,$DbgMsg);
  //$mysql_leiji_no_invoice=DB_fetch_array($result8);//to_invoice_amount
  $mysql_leiji_no_invoice=$mysql['kaipiaoweishou_amount'] +  $mysql_leiji_amount['already_invoice_amount']  + $sql_benqi_receive_amount['already_receive_amount'] ;




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

    // $objActSheet->getStyle('M'.$n)->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    // $objActSheet->getStyle('M'.$n)->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    // $objActSheet->getStyle('M'.$n)->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );   
    // $objActSheet->getStyle('M'.$n)->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN ); 
 
 
    
       
    $objActSheet->setCellValue('A'.$n, $i);    
    $objActSheet->setCellValue('B'.$n, $mysql['vendor_name']); 
	$objActSheet->setCellValue('C'.$n, $mysql['jieqian_amount']);
	$objActSheet->setCellValue('D'.$n, $mysql['kaipiaoweishou_amount']); 
	$objActSheet->setCellValue('E'.$n, $mysql_chuhuo['header_amont']);  
	$objActSheet->setCellValue('F'.$n, $mysql_tuihuo['header_amont']);    
    $objActSheet->setCellValue('G'.$n, $sql_benqi_receive_amount['already_receive_amount']);    
    $objActSheet->setCellValue('H'.$n, $sql_benqi_receive_amount['to_receive_amount']);    
    $objActSheet->setCellValue('I'.$n, $mysql_leiji_no_receive_amount['to_receive_amount']);   
	$objActSheet->setCellValue('J'.$n,  $sql_benqi_receive_amount['already_invoice_amount'] );  
	$objActSheet->setCellValue('K'.$n, $mysql_leiji_amount['already_invoice_amount'] );    
    $objActSheet->setCellValue('L'.$n,  $mysql_leiji_no_invoice);   
        
    $i++; 
    $n++;   
} 
    
    
      
  
  
//到文件       
 
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