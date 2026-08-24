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



$sql5= "SELECT * FROM companies ";
 $result5 = DB_query($sql5, $db);
 $myrow5 = DB_fetch_array($result5);

$sql8="SELECT a.customer_po,a.customer_code,b.customer_name,a.delivery_num,a.creation_date,ifnull(a.ship_address,b.customer_address) ship_address,b.bank_name,b.customer_contacts,b.bank_account,b.contacts_phone,b.contacts_fax,a.trackingcompany,a.narrative,(SELECT realname FROM www_users where userid = a.created_by) realname,(SELECT realname FROM www_users where userid = a.sub_approved_by) sub_approved_realname,(SELECT realname FROM www_users where userid = a.approved_by) approved_realname   
FROM so_delivery_headers_all a, customers b where a.customer_code=b.customer_code 
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
        .prall { width: 950px;margin: 0 auto;height: auto; overflow: hidden; border: 1px solid #e7e7e7;  border-radius: 5px; padding: 10px; }

        .prall_tit { width: 100%; height: 30px; text-align: right;}

        .prall_tit input {width: 80px;height: 30px;font-size: 10px;color: #333; border-radius: 5px;border: 1px solid #e7e7e7;margin-left: 10px;cursor: pointer;}

        .tab {width: 100%;height: auto;overflow: hidden;margin: 5px 0;font-size: 18px;line-height: 21px;}/*字大小和行距*/

        .tab input {vertical-align: middle;margin: 0 10px 0 10px;}
        .prall_btm{width:100%;height: auto;overflow: hidden;text-align: right;margin:15px 0 0;}
        .prall_btm input:first-child{width:80px;height: 30px;text-align: center;font-size: 19px;color: #fff;background: #52B100;border:1px solid #e7e7e7;border-radius: 5px;margin-right: 80px;cursor: pointer;}
        .prall_btm input:last-child{width:80px;height: 26px;text-align: center;font-size: 17px;color: #333;border:1px solid #e7e7e7;border-radius: 5px;margin-right: 0px;cursor: pointer;}/*打印按钮*/
        .prall_ctit{font-size: 19px;color:#333;font-weight:bold;text-align: center;}
        *{
            font-size: 16px;
            font-family: "仿宋";
        }
        .p1{
            font-size: 20px!important;
            font-family: "仿宋";
            font-weight: bold;
            text-align: center;
            margin-top: 0px;
            margin-bottom: 24px;


        }
        .p2{
            font-size: 24px!important;
            font-family: "仿宋";
            background-color: #deecfe;
            text-align: center;
            margin-top: 24px;
            margin-bottom: 0px;
        }
        .version{
            display: flex;
            justify-content: space-between;
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
        .content_tab th, .content_tab td{
            border: 1px solid #8daed9; 
        }
        .content_tab{
            border-collapse: collapse;
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

<p class="p2">销售出库单</p>
<p class="p1"><?php echo $myrow5['coyname'];?></p>
<div class="version">
    <span>记录编号：YXZD-CC-ZD-002-R02</span>
    <span>版本号：A/0</span>
</div>

    <table width="100%" border="0" cellpadding="0" cellspacing="0" >

        <tr>
            <td width="120" >出库日期：</td>
            <td><?php echo date('Y-m-d', $myrow6['creation_date']);?></td>
            <td width="130">出库单号：</td>
            <td><?php echo $Updatedelivery_num;?></td>
            <td width="50"></td>
            <td></td>
        </tr>
        <tr>
            <td>客户编码：</td>
            <td><?php echo $myrow6['customer_code'];?></td>
            <td>购货单位：</td>
            <td><?php  echo $myrow6['customer_name']; ?></td>
            <td>备注：</td>
            <td><?php echo $myrow6['narrative'];?></td>
        </tr>
        <tr>
            <td>地址：</td>
            <td><?php echo $myrow6['ship_address'];?></td>
            <td>联系人：</td>
            <td><?php echo $myrow6['customer_contacts'];?></td>
            <td>电话：</td>
            <td><?php echo $myrow6['contacts_phone'];?></td>
        </tr>
        <tr>
            <td>生产许可证号：</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
    </table>

 

   

     <?php
  $sql2 = "SELECT c.so_order_number ,c.delivery_line,c.stockid,b.item_name,b.item_desc,c.uom,c.shiped_quantity,c.remark,b.item_no,c.price,c.delivery_quantity,c.lot_num,c.expiring_date,c.zhucezhenghao,c.transportation_conditions,b.sub_locator
    FROM  so_delivery_all c ,sf_item_no b
        where   c.stockid=b.item_no
        and c.delivery_num = '" .$Updatedelivery_num."'
         ";
        $result2 = DB_query($sql2, $db);
?>

   
 
    

    <table width="100%"  cellpadding="0" cellspacing="0" class="content_tab" >
    
        <tr>
            <th style="text-align:left;background-color: #deecfe;" width="40"><center>序号 </center></th>    
            <th style="text-align:left;background-color: #deecfe;"  width="100" ><center>物料编码</center></th>
            <th style="text-align:left;background-color: #deecfe;" width="128"><center>产品名称 </center></th>  
            <th style="text-align:left;background-color: #deecfe;" width="80"><center>规格型号 </center></th>
            <th style="text-align:left;background-color: #deecfe;" width="50"><center>单位</center></th>
            <th style="text-align:left;background-color: #deecfe;" width="72"><center> 数量 </center></th>  
            <th style="text-align:left;background-color: #deecfe;" width="32"><center>SN号 </center></th>    
            <th style="text-align:left;background-color: #deecfe;" width="72"><center> 注册证号或备案凭证编号 </center></th>  
            <th style="text-align:left;background-color: #deecfe;" width="72"><center> 储运条件 </center></th>  
        </tr>

  

         <?php 
         $c=0;
		 $heji=0;
         $line=1;
         while ($myrow2 = DB_fetch_array($result2)) {
           
         ?>
    
		<tr>
            <td style="text-align:left;"><center><?php echo $line;?></center></td> 
            <td style="text-align:left;" ><center><?php echo $myrow2['stockid'];?><br/></center></td>
            <td style="text-align:left;"><center><?php echo $myrow2['item_name'];?></center></td>
            <td style="text-align:left;" ><center><?php echo $myrow2['item_desc'];?></center></td>
            <td style="text-align:left;"><center><?php echo $myrow2['uom'];?></center></td>
            <td style="text-align:left;"><center><?php echo $myrow2['delivery_quantity'];?></center></td>
            <td style="text-align:left;"><center><?php echo $myrow2['lot_num'];?></center></td> 
            <td style="text-align:left;"><center><?php echo $myrow2['zhucezhenghao'];?></center> </td>
            <td style="text-align:left;"><center><?php echo $myrow2['transportation_conditions'];?></center> </td>
        </tr >
			
			
					   
        <?php 
        $line++;
        $heji=$heji+$myrow2['delivery_quantity'];
        } 
                

        ?>


        <tr>
            <td  style="text-align:left;" ><center><?php echo '合计';?><br/></center></td>
            <td style="text-align:left;"> <?php echo '';?> </td>
            <td style="text-align:left;"><center> <?php echo '';?> </center></td>
            <td style="text-align:left;"><center><?php echo '';?></center></td> 
            <td style="text-align:left;"> <?php echo '';?> </td>
            <td style="text-align:left;"> <center><?php echo $heji;?></center> </td>
            <td style="text-align:left;"> <?php echo '';?> </td>
            <td style="text-align:left;"> <?php echo '';?> </td>
            <td style="text-align:left;"> <?php echo '';?> </td>
        </tr >

    </table>
             
			 <span>备注：货物签收时请协助认真核对，如有问题请在24小时内提出，如未提出均视为无异常。</span><br/>
			 <span>地址：苏州高新区富春江路188 号9 号楼301、302 室  验收验证支持，请联系18811345295；验收后技术服务，请联系15366106977</span>

    <table width="100%" border="0" cellpadding="0" cellspacing="0">
    <tr>
        <td width="50">制单人：</td>
        <td width="150"><?php echo $myrow6['realname'];?></td>
        <td width="85">制单部门审核：</td>
        <td width="72"><?php echo $myrow6['approved_realname'];?></td>
        <td width="100">仓储物流部审核：</td>
        <td width="72"><?php echo $myrow6['sub_approved_realname'];?></td>
        <td width="50">发料人：</td>
        <td width="62"></td>
    </tr>
    </table>
    <span>第一联（仓库）                第二联（客户）                    第三联（销售部）                   第四联（财务部）</span>

</div>


<div class="prall_btm" style="text-align:center">

<input type="button" onclick="print_list()" value="打印"/>

</div>

</body>
</html>