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

if (isset($_POST['return'])) {
    header('Location: DeliveryReport.php');
}  


$sql8="SELECT b.customer_name,a.delivery_num,a.creation_date,ifnull(a.ship_address,b.customer_address) ship_address,b.bank_name,b.customer_contacts,b.created_by,b.bank_account,b.contacts_phone,b.contacts_fax,a.trackingcompany  FROM so_delivery_headers_all a, customers b where a.customer_code=b.customer_code 
  and a.delivery_num = '" .$Updatedelivery_num."' ";
 $result6 = DB_query($sql8, $db);
 $myrow6 = DB_fetch_array($result6);
 

 $sql = "SELECT distinct so_order_number
    FROM  so_delivery_all c ,sf_item_no b
        where   c.stockid=b.item_no
        and c.delivery_num = '" .$Updatedelivery_num."'
         ";
        $result = DB_query($sql, $db);
		while ($myrow = DB_fetch_array($result)) {
		$so_num=$so_num.$myrow['so_order_number'];
		}

        $sql5= "SELECT * FROM companies ";
        $result5 = DB_query($sql5, $db);
        $myrow5 = DB_fetch_array($result5);
?>
 
   


<!DOCTYPE html>
<html>
<head>
    <title>打印退货单</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <link href="" rel="stylesheet" type="text/css">
    <script type="text/javascript" src="/JXC/statics/base/js/jQuery1.7.2.js"></script>
    <style>
        .prall { width: 850px;margin: 0 auto;height: auto; overflow: hidden; border: 1px solid #e7e7e7;  border-radius: 5px; padding: 10px; }

        .prall_tit { width: 100%; height: 30px; text-align: right;}

        .prall_tit input {width: 80px;height: 30px;font-size: 10px;color: #333; border-radius: 5px;border: 1px solid #e7e7e7;margin-left: 10px;cursor: pointer;}

        .tab {width: 100%;height: auto;overflow: hidden;margin: 5px 0;font-size: 18px;line-height: 21px;}/*字大小和行距*/

        .tab input {vertical-align: middle;margin: 0 10px 0 10px;}
        .prall_btm{width:100%;height: auto;overflow: hidden;text-align: right;margin:15px 0 0;}
        .prall_btm input:first-child{width:80px;height: 30px;text-align: center;font-size: 19px;color: #fff;background: #52B100;border:1px solid #e7e7e7;border-radius: 5px;margin-right: 80px;cursor: pointer;}
        .prall_btm input:last-child{width:80px;height: 26px;text-align: center;font-size: 17px;color: #333;border:1px solid #e7e7e7;border-radius: 5px;margin-right: 0px;cursor: pointer;}/*打印按钮*/
        .prall_ctit{font-size: 19px;color:#333;font-weight:bold;text-align: center;}
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

<table width="600" border="0">
  <tr>
  <td style="text-align:center;" ><font size="6" ><?php echo $myrow5['coyname'];?></font></td>
 </tr>
 <!-- <tr>
  <td style="text-align:center;" ><font size="4" >苏州市吴中区天鹅荡路2639号  </font></td>
 </tr>
 <tr>
  <td style="text-align:center;" ><font size="3" >Tel：0512-80965880   Fax：0512-80965882  </font></td>
 </tr> -->
  <tr>
  <td style="text-align:center;" ><font size="6" >退货单</font></td>
 </tr>
</table>
  
     <table width="100%" border="1" cellpadding="0" cellspacing="0">
	  <tr> 
   
  </tr>
	    <tr>
    <td>客户名称:</td>
  <td><?php echo $myrow6['customer_name'];?> </td>
     <td>送货单号：</td>
     <td  ><?php echo $Updatedelivery_num;?></td>
  </tr>
	   <tr>
    <td >客户地址:</td> 
	<td ><?php echo $myrow6['ship_address'];?> </td>

   <td>送货日期:</td>
    <td><?php  echo date('Y-m-d', $myrow6['creation_date']) ?></td>
  </tr>
 
 
  <tr>
    
    
  </tr>
  
 
   <tr>
    <td >联系人:</td>
  <td ><?php echo $myrow6['customer_contacts'];?> </td>
    <td>运输方式:</td>
  <td><?php echo $myrow6['trackingcompany'];?>  </td>
  </tr>
  

</table> 
   
 
    <div class="tab">
   

     <?php
  $sql2 = "SELECT c.so_order_number 	,c.delivery_line,c.stockid,b.item_name,b.item_desc,c.uom,c.delivery_quantity,c.remark,b.item_no,c.price
    FROM  so_delivery_all c ,sf_item_no b
        where   c.stockid=b.item_no
        and c.delivery_num = '" .$Updatedelivery_num."'
         ";
        $result2 = DB_query($sql2, $db);
?>

   
    <!-- <HR width="100%"  color="black" SIZE=1/> -->
    

        <table width="100%" border="1" cellpadding="0" cellspacing="0">
            <tr>
                <th style="text-align:left;" width="40" ><center>序号</th>
                 <th style="text-align:left;" width="200"><center>订单号码 </center></th> 
                 <th style="text-align:left;" width="200"><center>料号 </center></th>
                 <th style="text-align:left;" width="200"><center>产品名称 </center></th>
               <th style="text-align:left;" width="290"><center>规格型号 </center></th>
                  <td style="text-align:left;" width="40"><center>单位 </center></th>   
				<th style="text-align:left;" width="50"><center> 数量 </center></th>               
                <th style="text-align:left;" width="200"><center> 备注 </center></th>  
            </tr>
    
  
  </tr>
  

         <?php 
         $c=0;
		 $heji=0;
         while ($myrow2 = DB_fetch_array($result2)) {
           
         ?>
    
		<tr>
                <td style="text-align:left;" ><center><?php echo $myrow2['delivery_line'];?><br/></center></td>
				<td style="text-align:left;"><center><?php echo $myrow2['so_order_number'];?></center></td>
				<td style="text-align:left;"><center><?php echo $myrow2['item_no'];?></center></td>
				<td style="text-align:left;"><center><?php echo $myrow2['item_name'];?></center></td>
				<td style="text-align:left;"><center><?php echo $myrow2['item_desc'];?></center></td>
                <td style="text-align:left;"><center><?php echo $myrow2['uom'];?></center></td> 
				<td style="text-align:left;"><center><?php echo $myrow2['delivery_quantity'];?></center></td>
     
				 <td style="text-align:left;"> <?php echo $myrow2['remark'];?> </td></tr >
			
			
					   
					   <?php 
					   $heji=$heji+$myrow2['delivery_quantity'];
					   } 
					   		 
             
					 ?>


					 <tr>
                <td colspan="3" style="text-align:left;" ><center><?php echo '共计';?><br/></center></td>
                <td style="text-align:left;"> <?php echo '';?> </td>
				<td style="text-align:left;"> <?php echo '';?> </td>
				<td style="text-align:left;"><center><?php echo '件';?></center></td> 
			 	  <td style="text-align:left;"><center> <?php echo $heji;?> </center></td>
				<td style="text-align:left;"> <?php echo '';?> </td>
				 </tr >
            <tr>
                <td colspan="2"  style="text-align:left;" ><center><?php echo '1存根-白';?><br/></center></td>
                 <td style="text-align:left;"><center><?php echo '2客户-红';?></center></td> 
                 <td colspan="2"  style="text-align:left;"><center><?php echo '3仓库-蓝';?></center></td>  
                 <td colspan="3" style="text-align:left;"><center><?php echo '4财务-黄';?></center></td>   
					  </table>
			
					   <table>
                </table>
				<table>
				<tr><td colspan="2" >
      备注:1.收货方签单前请确认数量以及货物状态、如发现有破损,塌陷,脚印,等有损外观的情况,请立即通知厂方,协调处理,如收货签字前未通知厂方,将视为正常交货.   </td>
	 </tr >
	 <tr><td colspan="2" >
       <span>  2.收货方签字内容为：姓名、日期。 </span><br></td> </tr >
	  <tr> <td colspan="2" >3.若货物有损,请在货运公司签收单上明确标注,以便我司索赔，谢谢！ </span><br> </td></tr >
         
		<tr><td>	 <span>  送货单位：苏州伍赢尔电子有限公司       </td><td>     收货单位（签章）： </span><br>  </td></tr >
		<tr><td>	 <span>  发 货  人：                             </td> <td>     收货人： </span><br> </td> </tr >
			<body>
 </table>
			 


         <!-- <?php echo date('Y') ?> -->
       

        <div class="prall_btm" style="text-align:center">
        
            <input type="button" onclick="print_list()" value="打印"/>
          
        </div>
    </div>
</div>
</body>
</html>