<?php
ob_start();
include ('includes/login2.inc');
include('includes/session.inc');
include('myfunction.php');
if (isset($_GET['Updatedelivery_num'])) {
    $Updatedelivery_num = $_GET['Updatedelivery_num'];
} else {
    $Updatedelivery_num = '';
}
 // $Updatedelivery_num = $_GET['Updatedelivery_num'];
 // echo $Updatedelivery_num;
if (isset($_POST['return'])) {
    header('Location: DeliveryReport.php');
}  
// var_dump($_GET['Updatedelivery_num']);
// $Updatedelivery_num=QO2017041702;

$sql= "SELECT * FROM companies2 ";
 $result = DB_query($sql, $db);
 $myrow = DB_fetch_array($result);
$sql6= "SELECT * FROM companies ";
 $result6 = DB_query($sql6, $db);
 $myrow6 = DB_fetch_array($result6);

$sql4= "SELECT  a.note,b.vendor_name,a.order_type,a.approved_by,a.need_date,a.creation_date,b.contacts_mail,b.currencycode,b.vendor_faren,b.vendor_address,b.vendor_contacts,b.contacts_phone,b.created_by,b.bank_name,b.bank_account,b.postcode,b.contacts_fax,b.taxpayerid,d.phone,d.salesman,d.email,a.tax_name,a.payment_type,d.depart_code,d.realname,a.po_all_amount,a.all_line_amount,a.tax_amount,a.currency_code,a.payment_term,a.version
    FROM    po_headers_all a,vendors b,www_users d
        where  b.vendor_code=a.vendor_code and d.userid=a.created_by
        and a.po_num = '" .$Updatedelivery_num."'
         ";
        $result4 = DB_query($sql4, $db);
 $myrow4 = DB_fetch_array($result4);

 $sql5= "SELECT d.realname,d.email
    FROM   
       po_headers_all a, www_users d
        where a.approved_by=d.userid
        and a.po_num = '" .$Updatedelivery_num."'
         ";
        $result5 = DB_query($sql5, $db);
 $myrow5 = DB_fetch_array($result5);

?>
 


<!DOCTYPE html>
<html>
<head>
    <title>打印采购单</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <link href="" rel="stylesheet" type="text/css">
    <script type="text/javascript" src="/JXC/statics/base/js/jQuery1.7.2.js"></script>
    <style>
        .prall { width: 750px;margin: 0 auto;height: auto; overflow: hidden; border: 1px solid #e7e7e7;  border-radius: 5px; padding: 10px; }

        .prall_tit { width: 100%; height: 30px; text-align: right;}

        .prall_tit input {width: 80px;height: 30px; font-size: 12px;color: #333; border-radius: 5px;border: 1px solid #e7e7e7;margin-left: 10px;cursor: pointer;}

        .tab {width: 100%;height: auto;overflow: hidden;margin: 5px 0;font-size: 12px;line-height: 18px;}

        .tab input {vertical-align: middle;margin: 0 10px 0 10px;}
        .prall_btm{width:100%;height: auto;font-family:courier; overflow: hidden;text-align: right;margin:15px 0 0;}
        .prall_btm input:first-child{width:80px;height: 30px;text-align: center;font-size: 14px;color: #fff;background: #52B100;border:1px solid #e7e7e7;border-radius: 5px;margin-right: 80px;cursor: pointer;}
        .prall_btm input:last-child{width:80px;height: 30px;text-align: center;font-size: 14px;color: #333;border:1px solid #e7e7e7;border-radius: 5px;margin-right: 0px;cursor: pointer;}
        .prall_ctit{font-size: 18px;color:#333;font-weight:bold;text-align: center;}
        *{
          font-family:"楷体";
          font-size: 12px;
        }
        table tr td{
          padding: 3px;

        }
    </style>
	<script>
		function print_list () {
			$('.prall_btm').hide();
			if (window.print()) {
				
			} else {
				$('.prall_btm').show();
			}
		}
	</script>
</head>
<body>

<!--打印配菜单开始-->

<div class="prall">

   

     <span><center> <h3 style="font-size: 28px;margin-bottom: 0;"><?php echo $myrow6['coyname'];?></h3></center> </span>   
     
     <table width="100%" border="0">
<tr>
  <td width="80">制表日期: </td>
  <td width="125"><?php echo Date('Y-m-d', $myrow4['creation_date']);?></td>
  <td width="90"></td>
  <td width="80">供应商名称： </td>
  <td ><?php echo $myrow4['vendor_name'];?></td>
</tr>
<tr>
  <td>币别:</td>
  <td><?php echo $myrow4['currency_code'];?></td>
  <td></td>
  <td>供应商地址：</td>
  <td><?php echo $myrow4['vendor_address'];?></td>
</tr>
<tr>
  <td>采购单号: </td>
  <td> <?php echo $Updatedelivery_num.'  版本  '. $myrow4['version'] ;?> </td>
  <td></td>
  <td>供应商电话：</td>
  <td><?php echo $myrow4['contacts_phone'];?></td>
</tr>
<tr>
  <td>交货日期：</td>
  <td><?php echo Date('Y-m-d', $myrow4['need_date']);?></td>
  <td></td>
  <td>供应商mail：</td>
  <td><?php echo $myrow4['contacts_mail'];?></td>
</tr>
<tr>
  <td>请购部门: </td>
  <td><?php echo $myrow4['depart_code'];?></td>
  <td></td>
  <td>联络人：</td>
  <td><?php echo $myrow4['vendor_contacts'];?></td>
</tr>
<tr>
  <td>制表人</td>
  <td><?php echo $myrow4['realname'];?></td>
  <td></td>
  <td>付款方式：</td>
  <td><?php echo $myrow4['payment_term'];?></td>
    <td style="text-align: right;"></td>
</tr>
 
<tr>
  <td>备注：</td>
  <td colspan="3"><?php echo $myrow4['note'];?></td>
 
</tr>
</table> 
   



   <!--  <span><center><h3>Suzhou &nbsp; East &nbsp; Dragon  &nbsp;FIFE  &nbsp;CO,LTD. </h3></center></span>
    <span>Tel:+86 &nbsp; 512-65757069 &nbsp; Fax:+86 &nbsp;512-65757069 &nbsp; &nbsp;Web: &nbsp;www.dzlwsz-fire.com.cn</span> <br/>
    <span>地址：江苏省苏州工业园区唯华路3号君地曼哈顿广场8号楼7010室</span> 
    <div class="prall_tit">
        <!-- 打印时间：<? echo date('Y-m-d h:i:s')?> -->
    <!-- </div> -->  

    <div class="tab">

 
    

     <?php
  $sql2 = "SELECT c.line,c.price,c.line_remark,a.po_payment_amount,c.uom,c.quantity,c.stockid,d.item_desc,d.item_name,c.line_amount,c.line_remark
    FROM  po_lines_all c,
       po_headers_all a,vendors b,
	   sf_item_no d
        where  a.po_num=c.po_num and b.vendor_code=a.vendor_code and d.item_no=c.stockid
        and c.po_num = '" .$Updatedelivery_num."'
		order by c.line
         ";
        $result2 = DB_query($sql2, $db);
?>

   
    <!-- <HR width="100%"  color="black" SIZE=1/> -->
    

        <table width="100%" border="1" cellpadding="0" cellspacing="0">

            <tr>
                <td style="text-align:left;"  width="30"><center>序号</td> 
               <td style="text-align:left;"  width="30"><center>物料编码</td> 
				  <td style="text-align:left;" width="300" ><center>品名规格</td>
                 <td style="text-align:left;" width="30"><center> 单位 </center></td> 
                 <td style="text-align:left;" width="72"><center> 单价 </center></td> 
               <td style="text-align:left;" width="72"><center>数量 </center></td>

<td style="text-align:left;" width="72"><center> 合计 </center></td> 
                 

				<td style="text-align:left;" width="72"><center> 备注 </center></td> 

            </tr>
    
  

  

         <?php 
         $c=0;
     $heji=0;
     $shu=0;
         while ($myrow2 = DB_fetch_array($result2)) {
           
         ?>
       
		<tr>
                <td style="text-align:left;" ><center><?php echo $myrow2['line'];?><br/></center></td> 
                 <td style="text-align:left;"><center><?php echo $myrow2['stockid'];?></center></td>
				<td style="text-align:left;"><center><?php echo $myrow2['item_name'];?></center></td>
				<td style="text-align:left;"><center><?php echo $myrow2['uom'];?></center></td>
        <td style="text-align:left;"><center><?php echo $myrow2['price'];?></center></td>
				<td style="text-align:left;"><center><?php echo $myrow2['quantity'];?></center></td>

     <td style="text-align:left;"><center><?php echo $myrow2['line_amount'];?></center></td> 

                <td style="text-align:left;"><center><?php echo $myrow2['line_remark'];?></center></td> 

			
			
					   
					   <?php 
					   $heji=$heji+$myrow2['line_amount'];
             $shu=$shu+$myrow2['quantity'];
             
             
					   } 
					   		 
             
					 ?>
					  </table>
			

		 
			<body>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
  <?php
  // $wei=$heji*$heji/($heji* 0.01 *$myrow4['tax_name']+$heji);
  // $format_num = sprintf("%.2f",$wei);
  ?>
<tr>
  <td colspan="6">&nbsp;</td>
  <td>&nbsp;</td>
</tr>
<tr>  
  <td width="50">数量合计: </td>
  <td width="55"><?php echo $shu;?></td>
  <td width="91" style="text-align: right;">未税金额：</td>
  <td width="106"><?php echo $myrow4['all_line_amount'];?></td>
  <td width="99" style="text-align: right;">税金：</td>
  <td width="72"><?php echo $myrow4['tax_amount'];?></td>
  <td width="90"></td>
</tr>  
<tr>
  <td>含税金额：</td>
  <td><?php echo $myrow4['po_all_amount'];?></td>
  <td style="text-align: right;">税别</td>
  <td><?php echo $myrow4['tax_name'] ;?></td>
  <td></td>
  <td></td>
  <td></td>
</tr>

<tr> 
<td></td>
<td></td>
<td></td>
<td></td>
<td></td>
<td></td>
<td></td>
</tr>

<tr>
  <td>&nbsp;</td>
</tr>
<tr>
  <td>核准：</td>
  <td></td>
  <td>审批：</td>
  <td></td>
  <td>联系人：</td>
  <td></td>
  <td></td>
</tr>
  
 
</table>
			







         <!-- <?php echo date('Y') ?> -->
       

        <div class="prall_btm" style="text-align:center">
        
            <input type="button" onclick="print_list()" value="打印"/>
          
        </div>
    </div>
</div>
</body>
</html>