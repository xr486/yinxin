<?php
//  首先引入XLSXWriter包

putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");
ob_start();
include('includes/session2.inc');
include('includes/SQL_CommonFunctions.inc');
unset($result);
$sql = 'select customer_code,customer_name,customers_status,Customer_contacts,contacts_phone,contacts_mail,tax_name,contacts_fax,enable_flag,customer_type,creation_date  from customers   where customers_status="已签核"';
        
    if ($_SESSION['SaleFlag']=='Y') {
		  $sql = $sql . " and   employee_num='".$_SESSION['SalesMan']."' ";
  }
    if(isset($_GET['CustomerCode']) and $_GET['CustomerCode'] != ''){
        $sql = $sql." and customer_code ".LIKE." '%".$_GET['CustomerCode']."%' ";
    }
    if(isset($_GET['CustomerName']) and $_GET['CustomerName'] != ''){
        $sql = $sql." and customer_name ".LIKE." '%".$_GET['CustomerName']."%' ";
    }
    if(isset($_GET['CustomerContacts']) and $_GET['CustomerContacts'] != ''){
        $sql = $sql." and Customer_contacts ".LIKE." '%".$_GET['CustomerContacts']."%' ";
    }
	if(isset($_GET['customer_type']) and $_GET['customer_type'] != ''){
        $sql = $sql." and customer_type ".LIKE." '%".$_GET['customer_type']."%' ";
    }

	if(isset($_GET['ContactsPhone']) and $_GET['ContactsPhone'] != ''){
        $sql = $sql." and contacts_phone ".LIKE." '%".$_GET['ContactsPhone']."%' ";
    }
	if(isset($_GET['tax_name']) and $_GET['tax_name'] != ''){
        $sql = $sql." and tax_name ".LIKE." '%".$_GET['tax_name']."%' ";
    }
 if(isset($_GET['contacts_fax']) and $_GET['contacts_fax'] != ''){
        $sql = $sql." and contacts_fax ".LIKE." '%".$_GET['contacts_fax']."%' ";
    }
	if(isset($_GET['enable_flag']) and $_GET['enable_flag'] != ''){
        $sql = $sql." and enable_flag ".LIKE." '%".$_GET['enable_flag']."%' ";
    }


 $result_num = DB_query($sql, $db); 
//oci_execute($par);
include_once("xlsxwriter.class.php");
$date=date('YmdHis');
//ini_set('display_errors', 0);
//ini_set('log_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);

$filename = "客户资料".$date.".xlsx";
header('Content-disposition: attachment; filename="'.XLSXWriter::sanitize_filename($filename).'"');
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate');
header('Pragma: public');
$rows2 = array( 
  array('客户资料'),

);
$rows = array( 
  array('客户编号', '客户全称','联系人','电话','邮箱','传真','税别' ,'客户分类','建立时间' , '是否生效' ),

); 
     	

$writer = new XLSXWriter();
$writer->setAuthor('Shunfansoft'); 

//$writer->writeSheetHeader('Sheet1', $header);
 foreach($rows2 as $row2)
	$writer->writeSheetRow('Sheet1', $row2);
foreach($rows as $row)
	$writer->writeSheetRow('Sheet1', $row);

	while ($v = DB_fetch_array($result_num)) {

     $writer->writeSheetRow('Sheet1', array($v['customer_code'],$v['customer_name'],$v['Customer_contacts'],
     $v['contacts_phone'],
     $v['contacts_mail'],$v['contacts_fax'],$v['tax_name'].' ',$v['customer_type'],date('Y-m-d H:i:s',$v['creation_date']) ,$v['enable_flag'] 
		  ));
	 }
  
$writer->writeToStdOut();
//$writer->writeToFile('example.xlsx');
//echo $writer->writeToString();
exit(0);
?>
