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

$sql = 'SELECT  pha.po_num, pha.status, pha.note, pha.creation_date, pha.order_date, pla.line_amount,pla.quantity,pla.line,pla.price,v.vendor_code, v.vendor_name, pha.need_date, pha.created_by,b.item_no,b.item_desc,b.item_name,b.units,
ifnull(pla.quantity_received,0) this_received
FROM po_headers_all pha, po_lines_all pla, vendors v,sf_item_no b
WHERE pla.po_num = pha.po_num
AND  b.item_no=pla.stockid
AND v.vendor_code = pha.vendor_code';
if(isset($C1) and $C1 != ''){
    $sql = $sql." and v.vendor_name " . LIKE . " '%" . $C1 .
            "%' ";
}
if(isset($C2) and $C2 != ''){
    $sql = $sql." and pha.vendor =  '" . $C2 . "' ";
}
if(isset($C3) and $C3 != ''){
    $sql = $sql." and pha.order_date >=".strtotime($C3)." ";
}
if(isset($C4) and $C4 != ''){
    $sql = $sql." and pha.order_date <=".strtotime($C4)." ";
}
if (isset($C5) and $C5 != '') {
    $sql = $sql . " and pla.stockid  " . LIKE . " '%" . $C5 . "%'";
}
if (isset($C6) and $C6 != '') {
    $sql = $sql . " and pha.po_num " . LIKE . " '%" . $C6 . "%' ";
}
if (isset($C7) and $C7 != '') {
    $sql = $sql . " and b.item_name " . LIKE . " '%" . $C7 . "%' ";
}
if ($C8 != "") {
        if ($C8 == "APPROVED") {
            $sql .= " and pha.status = 'APPROVED'";
        }
        if ($C8 == "INPROCESS") {
            $sql .= " and pha.status = 'INPROCESS'";
        }
        if ($C8 == "Cancel") {
            $sql .= " and pha.status = 'Cancel'";
        }
        if ($C8 == "REJECTED") {
            $sql .= " and pha.status = 'REJECTED'";
        }
    }
$sql .=" order by pha.po_num desc ";

$result1 =DB_query($sql,$db,$ErrMsg,$DbgMsg);

// 创建一个处理对象实例
$objExcel = new PHPExcel();

// 创建文件格式写入对象实例, uncomment
$objWriter = new PHPExcel_Writer_Excel5($objExcel);

//设置文档基本属性
$objProps = $objExcel->getProperties();
$objProps->setCreator("Shunfansoft");
$objProps->setLastModifiedBy("Shunfansoft");
$objProps->setTitle("Residents pepole List");
$objProps->setSubject("江阴市宇诺自动化设备有限公司");
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
$objActSheet->getColumnDimension('F')->setWidth(20);
$objActSheet->getColumnDimension('G')->setWidth(20);
$objActSheet->getColumnDimension('H')->setWidth(10);
$objActSheet->getColumnDimension('I')->setWidth(5);
$objActSheet->getColumnDimension('J')->setWidth(20);
$objActSheet->getColumnDimension('K')->setWidth(20);
$objActSheet->getColumnDimension('L')->setWidth(25);
$objActSheet->getColumnDimension('M')->setWidth(10);
$objActSheet->getColumnDimension('N')->setWidth(10);
$objActSheet->getColumnDimension('O')->setWidth(10);
$objActSheet->getColumnDimension('P')->setWidth(10);
$objActSheet->getColumnDimension('Q')->setWidth(10);


$objActSheet->getRowDimension(1)->setRowHeight(30);
$objActSheet->getRowDimension(2)->setRowHeight(30);
$objActSheet->getRowDimension(3)->setRowHeight(30);

//设置单元格的值
$objActSheet->setCellValue('A1', '江阴市宇诺自动化设备有限公司');
$objActSheet->setCellValue('A3', "日期： $time ");
//合并单元格
$objActSheet->mergeCells('A1:Q1');
// $objActSheet->getStyle('A'. 1)->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );

$objActSheet->mergeCells('A3:C3');

$objActSheet->setCellValue('A2', " $C1  采购明细报表");
//合并单元格
$objActSheet->mergeCells('A2:Q2');
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
$objFontA1->setSize(12);
$objFontA1->setBold(true);

$objStyleA1 = $objActSheet->getStyle('A3');
$objStyleA1->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
$objFontA1 = $objStyleA1->getFont();
$objFontA1->setName('宋体');
$objFontA1->setSize(10);
$objFontA1->setBold(true);

$objStyleA1 = $objActSheet->getStyle('A4:Q4');
$objStyleA1->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$objFontA1 = $objStyleA1->getFont();
$objFontA1->setName('宋体');
$objFontA1->setSize(12);
$objFontA1->setBold(true);


//设置居中对齐
$objActSheet->getStyle('A4')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$objActSheet->getStyle('B4')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$objActSheet->getStyle('C4')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$objActSheet->getStyle('D4')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$objActSheet->getStyle('E4')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$objActSheet->getStyle('F4')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$objActSheet->getStyle('G4')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$objActSheet->getStyle('H4')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$objActSheet->getStyle('I4')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$objActSheet->getStyle('J4')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$objActSheet->getStyle('K4')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$objActSheet->getStyle('L4')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$objActSheet->getStyle('M4')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$objActSheet->getStyle('N4')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$objActSheet->getStyle('O4')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$objActSheet->getStyle('P4')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$objActSheet->getStyle('Q4')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
//$objActSheet->getStyle('D4')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$objActSheet->setCellValue('A4', '序号');
$objActSheet->setCellValue('B4', '供应商代码');
$objActSheet->setCellValue('C4', '采购单号');
$objActSheet->setCellValue('D4', '签核状态');
$objActSheet->setCellValue('E4', '备注	');
$objActSheet->setCellValue('F4', '采购日期');
$objActSheet->setCellValue('G4', '建立日期');
$objActSheet->setCellValue('H4', '建立人员');
$objActSheet->setCellValue('I4', '行');
$objActSheet->setCellValue('J4', '料号');
$objActSheet->setCellValue('K4', '料号名称');
$objActSheet->setCellValue('L4', '规格型号');
$objActSheet->setCellValue('M4', '单位');
$objActSheet->setCellValue('N4', '单价');
$objActSheet->setCellValue('O4', '采购数量');
$objActSheet->setCellValue('P4', '采购金额');
$objActSheet->setCellValue('Q4', '收货数量');

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

$objActSheet->getStyle('P4')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );
$objActSheet->getStyle('P4')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );
$objActSheet->getStyle('P4')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );
$objActSheet->getStyle('P4')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );

$objActSheet->getStyle('Q4')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );
$objActSheet->getStyle('Q4')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );
$objActSheet->getStyle('Q4')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );
$objActSheet->getStyle('Q4')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );
//$query=$data->query($sql);
$result1 =DB_query($sql,$db,$ErrMsg,$DbgMsg);
$i=1;
//从数据库取值循环输出
/*
 * prt.receipt_num,prr.receipt_line,a.po_num, b.line, b.stockid, c.item_spec,c.item_desc, a.vendor_code, d.vendor_name,
 * prt.transaction_quantity, prt.transaction_type, prr.subinventory_code, b.need_date, prt.transaction_date,prt.created_by
 */
while($mysql=DB_fetch_array($result1)){
    $vendor_code=$mysql['vendor_code'];
    $po_num=$mysql['po_num'];
    $status=$mysql['status'];
    $note=$mysql['note'];
    $order_date=$myrow['order_date'];
    $creation_date=$myrow['creation_date'];
    $created_by=$mysql['created_by'];
    $line=$mysql['line'];
    $item_no=$mysql['item_no'];
    $item_name=$mysql['item_name'];
    $item_desc=$mysql['item_desc'];
    $units=$mysql['units'];
    $price=$mysql['price'];
    $quantity=$mysql['quantity'];
    $line_amount=$mysql['line_amount'];
    $this_received=$mysql['this_received'];
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
    $objActSheet->getStyle('P'.$n)->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );
    $objActSheet->getStyle('P'.$n)->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );
    $objActSheet->getStyle('P'.$n)->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );
    $objActSheet->getStyle('P'.$n)->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );
    $objActSheet->getStyle('Q'.$n)->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );
    $objActSheet->getStyle('Q'.$n)->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );
    $objActSheet->getStyle('Q'.$n)->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );
    $objActSheet->getStyle('Q'.$n)->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );

/*
 *     $receipt_num=$mysql['receipt_num'];
    $receipt_line=$mysql['receipt_line'];
    $vendor_code=$mysql['vendor_code'];
    $po_num=$mysql['po_num'];
    $line=$mysql['line'];
    $stockid=$mysql['stockid'];
    $item_desc=$mysql['item_desc'];
    $item_spec=$mysql['item_spec'];
    $subinventory_code=$mysql['subinventory_code'];
    $transaction_quantity=$mysql['transaction_quantity'];
    $transaction_date=$mysql['transaction_date'];
    $created_by=$mysql['created_by'];
 */
         unset($v_status);
            if ($status == 'INPROCESS') {
                $v_status = '待签核';
            } elseif ($status == 'APPROVED') {
                $v_status = '已签核';
            }elseif ($status == 'REJECTED') {
                $v_status = '已拒签';
            } else {
                $v_status = '已取消';
            }
    $objActSheet->setCellValue('A'.$n, $i);
    $objActSheet->setCellValue('B'.$n, $vendor_code);
    $objActSheet->setCellValue('C'.$n, $mysql['po_num']);
    $objActSheet->setCellValue('D'.$n, $v_status);
    $objActSheet->setCellValue('E'.$n, $note);
    $objActSheet->setCellValue('F'.$n, date('Y-m-d',$mysql['order_date']));
    $objActSheet->setCellValue('G'.$n, date('Y-m-d H:i:s',$mysql['creation_date']));
    $objActSheet->setCellValue('H'.$n, $mysql['created_by']);
    $objActSheet->setCellValue('I'.$n, $mysql['line']);
    $objActSheet->setCellValue('J'.$n, $mysql['item_no']);
    $objActSheet->setCellValue('K'.$n, $mysql['item_name']);
    $objActSheet->setCellValue('L'.$n, $item_desc);
    $objActSheet->setCellValue('M'.$n, $mysql['units']);
    $objActSheet->setCellValue('N'.$n, $mysql['price']);
    $objActSheet->setCellValue('O'.$n, $mysql['quantity']);
    $objActSheet->setCellValue('P'.$n, $mysql['line_amount']);
    $objActSheet->setCellValue('Q'.$n, $mysql['this_received']);

    $total_quantity  = $total_quantity+$mysql['quantity_sum'];

    $i++;
    $n++;
}
// $objActSheet->setCellValue('B'.$n,'合计：');
// $objActSheet->setCellValue('C'.$n, $total_amount);

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
//
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
header('Content-type: text/csv;charset= utf-8');
header('Content-Disposition: attachment;filename="'.$outputFileName.'"');
header('Cache-Control: max-age=0');


$objWriter = PHPExcel_IOFactory::createWriter($objExcel, 'Excel5');
$objWriter->save('php://output');
//include('includes/footer.inc');
?>