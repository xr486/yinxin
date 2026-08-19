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


$sql= "SELECT * FROM companies ";
 $result = DB_query($sql, $db);
 $myrow = DB_fetch_array($result);

$sql4= "SELECT  b.vendor_name,a.creation_date,b.vendor_faren,b.vendor_address,b.vendor_contacts,b.contacts_phone,b.created_by,b.bank_name,b.bank_account,b.postcode,b.contacts_fax,b.taxpayerid,d.phone,d.salesman,a.tax_name,a.payment_type
    FROM  
       po_headers_all a,vendors b,www_users d
        where    b.vendor_code=a.vendor_code and d.userid=a.created_by
        and a.po_num = '" .$Updatedelivery_num."'
         ";
		// echo $sql4 ;
        $result4 = DB_query($sql4, $db);
 $myrow4 = DB_fetch_array($result4);

?>
 


<!DOCTYPE html>
<html>
<head>
    <title>打印采购单</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <link href="" rel="stylesheet" type="text/css">
    <script type="text/javascript" src="/JXC/statics/base/js/jQuery1.7.2.js"></script>
    <style>
        .prall { width: 750px;margin: 0 auto;height: 1270px; overflow: hidden; border: 1px solid #e7e7e7;  border-radius: 5px; padding: 10px; }

        .prall_tit { width: 100%; height: 30px; text-align: right;}

        .prall_tit input {width: 80px;height: 30px; font-size: 12px;color: #333; border-radius: 5px;border: 1px solid #e7e7e7;margin-left: 10px;cursor: pointer;}

        .tab {width: 100%;height: auto;overflow: hidden;margin: 5px 0;font-size: 12px;line-height: 18px;}

        .tab input {vertical-align: middle;margin: 0 10px 0 10px;}
        .prall_btm{width:100%;height: auto;font-family:courier; overflow: hidden;text-align: right;margin:15px 0 0;}
        .prall_btm input:first-child{width:80px;height: 30px;text-align: center;font-size: 14px;color: #fff;background: #52B100;border:1px solid #e7e7e7;border-radius: 5px;margin-right: 80px;cursor: pointer;}
        .prall_btm input:last-child{width:80px;height: 30px;text-align: center;font-size: 14px;color: #333;border:1px solid #e7e7e7;border-radius: 5px;margin-right: 0px;cursor: pointer;}
        .prall_ctit{font-size: 18px;color:#333;font-weight:bold;text-align: center;}
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

  <span><center> <h3>采购合同</h3></center> </span>   

  <table width="400" border="0">
    <tr>
      <td font-size: 10px;> <?php echo $myrow['coyname'];?> <td>
    </tr>
    <tr>
      <td  font-size: 10px;> <?php echo $myrow['coyname_en'];?></td>
    </tr>

  </table> 
	 
  <table width="800" border="0">
    <tr>
      <td>买方:</td>
      <td><?php echo $myrow['coyname'];?> </td>
      <td width="72">卖方:</td>
      <td width="280"><?php echo $myrow4['vendor_name'];?></td>
   
    </tr>
    <tr>
      <td>地址：</td>
      <td><?php echo $myrow['address'];?> </td>
      <td>地址：</td>
      <td width="280"><?php echo $myrow4['vendor_address'];?></td>
    </tr>
    <tr>
      <td>联系人：</td>
      <td><?php echo $myrow['faren'];?> </td>
      <td>联系人：</td>
      <td width="280"><?php echo $myrow4['vendor_contacts'];?></td>
    
    </tr>
    <tr> 
      <td>&nbsp;</td>
      <td>&nbsp;</td> 
      <td width="97">订单编号:</td>
      <td width="333"><?php echo $Updatedelivery_num;?></td>
   
    </tr>
  </table> 
   




    <div class="tab">


     <?php
  $sql2 = "SELECT c.line,c.price,c.line_remark,c.uom,c.quantity,c.stockid,c.operation_code,c.operation_seq_num,c.line_amount,c.wip_entity_name,d.item_name,e.need_date 
    FROM  po_lines_all c,
       po_headers_all a,vendors b,sf_item_no d,pr_lines_all e 
        where  a.po_num=c.po_num and b.vendor_code=a.vendor_code 
		and c.stockid =d.item_no and c.wip_entity_name =e.wip_entity_name and c.operation_code=e.operation_code
        and c.po_num = '" .$Updatedelivery_num."'
         ";
		// echo $sql2;
        $result2 = DB_query($sql2, $db);
?>

   
    <!-- <HR width="100%"  color="black" SIZE=1/> -->
    

        <table width="100%" border="1" cellpadding="0" cellspacing="0">
		<span>买卖双方通过友好协商，根据《中华人民共和国合同法》及相关法律法规，订立本合同，以资信守。</span><br>
		<span>一、 产品名称、规格型号、数量、价格 :</span><br>
            <tr>
                <td style="text-align:left;"  width="40"><center>序号</td> 
				  <td style="text-align:left;" width="150" ><center>工单号</td>
				  <td style="text-align:left;" width="120" ><center>料号</td>
				  <td style="text-align:left;" width="150" ><center>料号名称</td>
                 <td style="text-align:left;" width="72"><center>工序名称 </center></td>
               <td style="text-align:left;" width="72"><center>数量 </center></td> 
               <td style="text-align:left;" width="72"><center>需求日期 </center></td> 
                 
                <td style="text-align:left;" width="72"><center> 采购单价 </center></td> 
				<td style="text-align:left;" width="72"><center> 采购金额 </center></td> 
				 <td style="text-align:left;" width="112"><center>备注</center></td> 
            </tr>
    
  

  

         <?php 
         $c=0;
		 $heji=0;
         while ($myrow2 = DB_fetch_array($result2)) {
           
         ?> 	
       
		<tr>
                <td style="text-align:left;" ><center><?php echo $myrow2['line'];?><br/></center></td> 
				<td style="text-align:left;"><center><?php echo $myrow2['wip_entity_name'];?></center></td>
				<td style="text-align:left;"><center><?php echo $myrow2['stockid'];?></center></td>
				<td style="text-align:left;"><center><?php echo $myrow2['item_name'];?></center></td>
				<td style="text-align:left;"><center><?php echo $myrow2['operation_code'];?></center></td>
				
				<td style="text-align:left;"><center><?php echo $myrow2['quantity'];?></center></td>
				<td style="text-align:left;"><center><?php echo date('m-d',$myrow2['need_date']);?></center></td>
     
                <td style="text-align:left;"><center><?php echo $myrow2['price'];?></center></td>
                <td style="text-align:left;"> <?php echo $myrow2['line_amount'];?> </td> 
				 <td style="text-align:left;"> <?php echo $myrow2['line_remark'];?> </td></tr >
			
			
					   
					   <?php 
					   $heji=$heji+$myrow2['line_amount'];
					   } 
					   		 
             
					 ?>

					 <tr>
					 <td  colspan="8" style="text-align:left;" >  合计 </td> 
                <td style="text-align:left;" ><center><?php echo $heji;?><br/></center></td> 
                <td style="text-align:left;" ><center><br/></center></td> 
					  </table>
			
					  
    <span> 备注  </span><br>
        
         <span>1、以上价格含<?php echo  $myrow4['tax_name'];?>及运费。</span><br>
         <span>2、如果不能准时交货的，卖方应提前2天通知买方，如果买方同意延期，则按照新的交货期交货，如果买方不同意延期，则每延期一天，</span><br>
		 <span>应向买方支付合同总价千分之一为违约金，如果卖方无故拒绝送货，卖方应承担买方因此造成的损失。</span><br>
         <span>3、如果产品有质量问题，造成交货期延后，使买方无法正常出货，则卖方应支付买方支付合同总价千分之一作为违约金。</span><br>
         <span>4、交货条件：按照合同约定的交货期交货。</span><br>
		   <span>5、交货方式：卖方送货到买方指定地点。</span><br> 
		  <span> 6、付款方式：<?php echo  $myrow4['payment_type'];?></span><br>
		  <span> 7、解决合同纠纷的方式：双方所在地人民法院进行协调解决。</span><br>  
			<span> 8、合同更改：买卖双方需要更改合同上任一内容，必须由双方共同协商处理。合同未尽事宜双方本着友好协商的方式解决。</span><br>
			<span>9、本合同壹式贰份，双方各执一份，签字或盖章后生效。</span><br>
		 
			
<table width="100%" border="1" cellpadding="0" cellspacing="0">
  <tr>
    <td width="50%"><div align="center">出  卖  方</div></td>
    <td width="50%"><div align="center">受  买  方</div></td>
  </tr>
  <tr>
    <td>卖方(盖章）：  <?php echo  $myrow4['vendor_name'];?></td>
    <td> 买方(盖章）：  <?php echo  $myrow['coyname'];?></td>
  </tr>
  <tr>
    <td>地址： <?php echo  $myrow4['vendor_address'];?> </td>
    <td>地址： <?php echo  $myrow['address'];?> </td>
  </tr>
  <tr>
    <td>法定/授权代表： <?php echo  $myrow4['vendor_faren'];?> </td>
    <td>法定/授权代表： <?php echo  $myrow['faren'];?> </td>
  </tr>
  <tr>
    <td>签订日期：: <?php echo  '' ;?></td>
    <td>签订日期：:  <?php echo  date('Y-m-d',$myrow4['creation_date']) ;?> </td>
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