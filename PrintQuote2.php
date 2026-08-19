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

 

$sql4= "SELECT d.coyname,order_number,a.customer_code,need_date,a.creation_date, a.created_by, b.customer_name,a.subject,a.project,b.customer_contacts,b.contacts_phone,b.contacts_fax,b.currency_code,c.employee_name,c.telephone,c.email,a.term_name,a.jiaohuotiaojian,a.baozhuang,a.zhiliangbaozheng,a.mainfeifuwu,a.youxiaoxing1,a.youxiaoxing2,a.tax_name,a.yunfei
FROM quote_headers_all a, customers b,hr_employees c,companies2 d  WHERE a.coycode=d.coyname_code and c.employee_num =a.yewu AND a.customer_code = b.customer_code  
        and a.order_number = '" .$Updatedelivery_num."'";
          	
  $result4 = DB_query($sql4, $db);
 $myrow4 = DB_fetch_array($result4);

?>
 

<!DOCTYPE html>
<html>
<head>
    <title>打印报价单</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <link href="" rel="stylesheet" type="text/css">
    <script type="text/javascript" src="/JXC/statics/base/js/jQuery1.7.2.js"></script>
    <style>
        .prall { width: 760px;margin: 0 auto;height: auto; overflow: hidden; border: 1px solid #e7e7e7;  border-radius: 5px; padding: 10px; }

        .prall_tit { width: 100%; height: 30px; text-align: right;}

        .prall_tit input {width: 80px;height: 30px; font-size: 15px;color: #333; border-radius: 5px;border: 1px solid #e7e7e7;margin-left: 10px;cursor: pointer;}

        .tab {width: 100%;height: auto;overflow: hidden;margin: 5px 0;font-size: 15px;line-height: 18px;}

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
<table width="700" border="0">
  <tr>
 
<td>
<table width="180" border="0">
  <tr>  
  <td rowspan=3> <img src="./itempic/logo.png" width="120" height="60"/> </td></tr> 
</table>
</td>

 <td>
<table width="400" border="0">
  <tr>
  <td  ><font size="13"  >报价单</font>     <td>
  
 </tr>
 
</table>
</td>

  </tr>
   <tr>
  <td font-size: 10px;> <?php echo $myrow4['coyname'];?> <td>
 
  
</table>
   


	 
     <table width="800" border="0">
  <tr>
    <td width="150">收件人/To:</td>
    <td width="280"><?php echo $myrow4['customer_contacts'];?></td>
    <td width="220" >发件人／From:</td>
    <td width="333"><?php echo $myrow4['employee_name'];?></td>
  </tr>
  <tr>
    <td>公司／Company:</td>
  <td><?php echo $myrow4['customer_name'];?> <br/></center></td>
    <td>电话／Phone:</td>
    <td>0512-80965880</td>
  </tr>
  <tr>
    <td>电话／Phone:</td>
  <td><?php echo $myrow4['contacts_phone'];?> <br/></center></td>
    <td>移动电话／Mobile: </td>
   <td><?php echo $myrow4['telephone'];?> <br/></center></td>
  </tr>
  <tr>


    <td>传真／Fax:</td>
  <td><?php echo $myrow4['contacts_fax'];?> <br/></center></td>
    <td>回传传真/Return Fax: </td>
   <td>0512-80965882</td>
  </tr>
 <tr>
    <td>日期／Date :</td>
  <td><?php echo date('Y-m-d',$myrow4['need_date']);?> <br/></center></td>
    <td>Email : </td>
   <td><?php echo $myrow4['email'];?> <br/></center></td>
  </tr>
   <tr>
    <td>页码／Page:</td>
  <td> 1/1, 共1 页  </center></td>
    <td>文件编号／Our Ref.: </td>
   <td><?php echo $myrow4['order_number'];?> <br/></center></td>
  </tr>
  <tr>
    <td>抄送／Cc :</td>
  <td><?php echo $myrow4['cc_mail'];?> <br/></center></td>
    <td>报价货币／Currency: </td>
   <td><?php echo $myrow4['currency_code'];?> <br/></center></td>
  </tr>
  <tr>
    <td>主题/Subject :</td>
  <td><?php echo $myrow4['subject'];?> <br/></center></td>
    <td>项目/Project: </td>
   <td><?php echo $myrow4['project'];?> <br/></center></td>
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
  $sql2 = "SELECT c.line, leixing,need_date,need_remark,quantity,uom,price				
						  
    FROM  quote_lines_all c 
        where    c.order_number = '" .$Updatedelivery_num."'
        order by c.line ";
        $result2 = DB_query($sql2, $db);
?>

   
    <!-- <HR width="100%"  color="black" SIZE=1/> -->
    

        <table width="100%" border="1" cellpadding="0" cellspacing="0">
		
            <tr>
                <td style="text-align:left;" width="20" ><center>项目</td> 
                 <td style="text-align:left;"  width="280"><center>规格 </center></td>
                  <td style="text-align:left;" width="40"><center>单位 </center></td>  
               <td style="text-align:left;" width="40"><center>数量 </center></td>               
                <td style="text-align:left;" width="60"><center> 单价 </center></td> 
				<td style="text-align:left;" width="60"><center> 总价 </center></td> 
 
            </tr>
			 <tr>
                <td style="text-align:left;"  ><center>Item</td>
				<td style="text-align:left;"  ><center>Description </center></td> 
               <td style="text-align:left;"><center>Q. Unit </center></td>
                  <td style="text-align:left;"><center>QTY </center></td>                 
                <td style="text-align:left;"><center> Unit Price (￥) </center></td> 
				<td style="text-align:left;"><center> Total Price (￥) </center></td> 
 
            </tr>
    
    
  

  

         <?php 
         $c=0;
		 $heji=0;
         while ($myrow2 = DB_fetch_array($result2)) {
           
         ?>
          
		<tr>
                <td style="text-align:left;" ><center><?php echo $myrow2['line'];?><br/></center></td>
				<td style="text-align:left;" ><center><?php echo $myrow2['leixing']  ;?></center></td>
                <td style="text-align:left;"><center><?php echo $myrow2['uom'];?></center></td>
				 
				<td style="text-align:left;"><center><?php echo $myrow2['quantity'];?></center></td>
    
             
                <td style="text-align:left;"><center><?php echo $myrow2['price'];?></center></td>
                <td style="text-align:left;"> <?php echo ($myrow2['need_qty'] * $myrow2['baojia']) ;?> </td> 
			
			
					   
					   <?php 
					   $heji=$heji+($myrow2['quantity'] * $myrow2['price']) ;
					   $line=$myrow2['line']+1;
					   } 				   		 
             
					 ?>
              <tr>  
                <td style="text-align:left;" ><center><?php echo $line;?><br/></center></td>
				<td style="text-align:left;" "><center>运输：  以上报价<?php echo $myrow4['yunfei'];?> </center></td>
                <td style="text-align:left;"><center>  </center></td>
                <td style="text-align:left;"><center>   </center></td>
                <td style="text-align:left;"><center>  </center></td>
                <td style="text-align:left;"><center>  </center></td>

				 <?php 
					   $heji=$heji+$myrow2['line_amount'];
					   $line=$myrow2['line']+1;
				?>
				<tr>
                <td style="text-align:left;" ><center><?php echo $line;?><br/></center></td>
				<td style="text-align:left;" "><center>税金或关税：<?php echo $myrow4['tax_name'];?> </center></td>
                <td style="text-align:left;"><center>式  </center></td>
                <td style="text-align:left;"><center> 1  </center></td>
                <td style="text-align:left;"><center>  </center></td>
                <td style="text-align:left;"><center>  </center></td>
				 
				 
				
			


					  </table>
			
					   <table width="100%">
					     <tr>  <td style="text-align:left;"><left>总金额 / Total Amount： </left></td>   
						  <td style="text-align:left;"><center>  </center></td>
                <td style="text-align:left;"><center>  </center></td>
				      <td style="text-align:left;"><right> <?php echo '￥'.$heji;?> </right></td>           
				</tr > 
                </table>
				<table width="100%">
				<tr>  <td>备注说明：</td> </tr>
				<tr>  <td>Remarks:</td> 
				<td><left>1.付款条件/Payment Terms and Schedule：</left></td>  
				<td><?php echo $myrow4['term_name'];?></td>  </tr>
				
				<tr>  <td> </td> 
				<td><left>2.交货条件/ Deliver Terms and Date：        </left></td>  
				<td><?php echo $myrow4['jiaohuotiaojian'];?></td>  </tr>
				<tr>  <td> </td> 
				<td><left>3.包装／ Packing：　      </left></td>  
				<td><?php echo $myrow4['baozhuang'];?></td>  </tr>
				<tr>  <td> </td> 
				<td><left>4.质量保证／Quality Assurance：  </left></td>  
				<td><?php echo $myrow4['zhiliangbaozheng'];?></td>  </tr>
				<tr>  <td> </td> 
				<td><left>5.有效性声明/Validity Statement:    </left></td>  
				<td><?php echo $myrow4['youxiaoxing1'];?></td>  </tr> 
				<tr>  <td> </td> 
				<td><left>   </left></td>  
				<td><?php echo $myrow4['youxiaoxing2'];?></td>  </tr> 
				 
				<tr>  <td> </td> 
				<td><left>6.免费服务／Free Sales Service： </left></td>  
				<td><?php echo $myrow4['mainfeifuwu'];?></td>  </tr>
              </table>
			<body>
<table width="100%" border="1" cellpadding="0" cellspacing="0">
 
  <tr>
    <td width="50%"><?php echo $myrow4['coyname'];?> </td>
    <td width="50%">  客户:<?php echo $myrow4['customer_name'];?></td>
  </tr>
  <tr>
    <td>授权销售代表：  </td>
    <td>授权代表：</td>
  </tr>
  
  <tr> 
	<td>签名/印章： </td>
    <td>签名/印章：  </td>
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