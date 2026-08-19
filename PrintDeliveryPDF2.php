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

$sql5= "SELECT * FROM companies2 ";
 $result5 = DB_query($sql5, $db);
 $myrow5 = DB_fetch_array($result5);

$sql8="SELECT a.customer_po,a.customer_code,b.customer_name,a.delivery_num,a.creation_date,ifnull(a.ship_address,b.customer_address) ship_address,b.bank_name,b.customer_contacts,b.created_by,b.bank_account,b.contacts_phone,b.contacts_fax,a.trackingcompany  FROM so_delivery_headers_all a, customers b where a.customer_code=b.customer_code 
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

?>
 
   


<!DOCTYPE html>
<html>
<head>
    <title>打印出货单</title>
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
        *{
            font-size: 14px;
            font-family: "宋体";
        }
        .p1{
            font-size: 20px!important;
            font-family: "楷体";
        }
        .p2{
            font-size: 16px!important;
            font-family: "宋体";
        }
        table tr td{
          padding: 5px;

        }
        .z1{
            display: flex;
            position: relative;
        }
        .x1{
            writing-mode:vertical-rl;
            position: absolute;
            left:91%;
        }

        .tab{
            display: none;
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
<table width="100%" border="0">
<tr>
    <td><center ><b class="p1">标品杰电子科技有限公司</td>
</tr>
<tr>
    <td><center><b class="p2">转常州在制品</td>
</tr>
</table>
<div class="z1">
<table width="100%" border="1" cellpadding="0" cellspacing="0" >

<tr>
    <td width="79" >客户编号：</td>
	<td colspan="2">常州天地</td>
    
    <td width="111">出货单号：</td>
    <td width="72" colspan="3"><?php echo $Updatedelivery_num;?></td>


</tr>
<tr>
    <td>客户名称：</td>
    <td colspan="2">常州天地</td>
    <td>出货日期：</td>
    <td colspan="3"><?php  echo date('Y-m-d', $myrow6['creation_date']) ?></td>
</tr>
<tr>
    <td>客  户PO：</td>
   <td colspan="2"><?php echo $myrow6['customer_po'];?></td>
    <td>公司电话：</td>
    <td colspan="3">0512-63958189</td>

</tr>
<tr>
    <td>交货地点:</td>
    <td colspan="2">常州</td>
    <td>公司地址：</td>
    <td colspan="3">苏州市吴江区交通路1178号</td>

</tr>

   
 
    <div class="tab">
   

     <?php
  $sql2 = "SELECT   b.item_no,b.item_name,b.item_desc,b.units,c.line_remark,abs(c.transaction_quantity) transaction_quantity
    FROM  wip_changzhou_all c ,sf_item_no b,wip_jobs_all a
        where   c.wip_entity_name=a.wip_entity_name
		and b.item_no=a.primary_item 
        and c.transaction_num = '" .$Updatedelivery_num."'
         ";
        $result2 = DB_query($sql2, $db);
?>

            <tr>
                <td style="text-align:left;"  width="18" ><center>序号</center></th>  
                 <td style="text-align:left;" ><center>标的名称 </center></th>
                 <td style="text-align:left;" ><center>产品名称 </center></th>
               <td style="text-align:left;"><center>规格 / 说明 </center></th>
               <td style="text-align:left;" width="72"><center> 数量 </center></th>  
                  <td style="text-align:left;" width="28"><center>单位 </center></th>   
             
                <td style="text-align:left;" width="72"><center> 备注 </center></th>  
            </tr>
  </tr>
  

         <?php 
         $c=0;
		 $heji=0;
		 $line=0;
         while ($myrow2 = DB_fetch_array($result2)) {
           $line=$line+1; 
         ?>
    
		<tr>
                <td style="text-align:left;" ><center><?php echo $line;?> </center></td>
				 <td style="text-align:left;"><center><?php echo $myrow2['item_no'];?></center></td>
				<td style="text-align:left;"><center><?php echo $myrow2['item_name'];?></center></td>
				<td style="text-align:left;"><center><?php echo $myrow2['item_desc'];?></center></td>

                <td style="text-align:left;"><center><?php echo $myrow2['transaction_quantity'];?></center></td>
                <td style="text-align:left;"><center><?php echo $myrow2['units'];?></center></td> 
     
				 <td style="text-align:left;"><center><?php echo $myrow2['remark'];?> </td></tr >
			
			
					   
					   <?php 
					   $heji=$heji+$myrow2['delivery_quantity'];
					   } 
					   		 
             
					 ?>

                      </table>
                 

			<body>
       
 </div>
        <div class="prall_btm" style="text-align:center">
        
            <input type="button" onclick="print_list()" value="打印"/>
          
        </div>
   
</div>
</body>
</html>