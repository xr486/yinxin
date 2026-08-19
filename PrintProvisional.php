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

    $sql4= "SELECT a.*,(SELECT realname from www_users where a.created_by = userid ) realname,(SELECT realname from www_users where a.approved_by = userid ) approve_realname  FROM  inv_change_price a where a.change_num = '" .$Updatedelivery_num."' ";
    $result4 = DB_query($sql4, $db);
    $myrow4 = DB_fetch_array($result4);



$time=time();

?>
 
   


<!DOCTYPE html>
<html>
<head>
    <title>打印暂估单</title>
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
      
            text-align: center;
            margin-top: 0px;
            margin-bottom: 0px;


        }
        .p2{
            font-size: 24px!important;
            font-family: "仿宋";
          
            text-align: center;
            margin-top: 0px;
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
            border: 1px solid #000; 
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
   

<p class="p2">暂估调整单</p>

    <table width="100%" border="0" cellpadding="0" cellspacing="0" >

        <tr>
            <td width="80" >调整单号：</td>
            <td><?php echo $myrow4['change_num'];?></td>
            <td width="80" >仓库：</td>
            <td><?php echo $myrow4['subinventory_code'];?></td>
        </tr>
        <tr>
            <td>调整日期：</td>
            <td><?php echo date('Y-m-d', $myrow4['creation_date']);?></td>
            <td>采购单号：</td>
            <td><?php echo $myrow4['po_num'];?></td>
        </tr>
        
    </table>

 

   

     <?php
 
        $sql2 = "SELECT  a.*,b.item_name,b.item_desc, c.line_amount,d.tax_name,c.quantity,CAST(c.price AS DECIMAL(20, 6))/(1+CAST(d.tax_rate AS DECIMAL(20, 4))) notax_price,(SELECT (sum(f.cost_price*f.quantity)*(1+d.tax_rate)) onhand_amount  from inv_onhand_quantity_all f where f.subinventory_code = a.subinventory_code and f.stockid = c.stockid and a.lot_num = f.lot_num) onhand_amount,(SELECT sum(f.quantity) onhand_quantity  from inv_onhand_quantity_all f where f.subinventory_code = a.subinventory_code and f.stockid = c.stockid and a.lot_num = f.lot_num) onhand_quantity,(SELECT distinct cost_price  from inv_onhand_quantity_all f where f.subinventory_code = a.subinventory_code and f.stockid = c.stockid and a.lot_num = f.lot_num) cost_price from inv_change_price a, sf_item_no b,po_lines_all c,po_headers_all d where  a.po_num = c.po_num and a.po_line = c.line and a.item_no = b.item_no and c.po_num = d.po_num and a.change_num = '" . $Updatedelivery_num . "'";

             
            //   echo $sql2;
        $result2 = DB_query($sql2, $db);
?>

   
 
    

    <table width="100%"  cellpadding="0" cellspacing="0" class="content_tab" >
    
        <tr>
            <th style="text-align:left;"  width="100" ><center></center></th>
            <th style="text-align:left;"  width="100" ><center>料号</center></th>
            <th style="text-align:left;" width="120"><center>物料名称 </center></th>  
            <th style="text-align:left;" width="120"><center>规格/型号 </center></th>
            <th style="text-align:left;" width="120"><center>批号 </center></th>
            <th style="text-align:left;" width="72"><center> 数量 </center></th>    
            <th style="text-align:left;" width="100"><center> 含税金额 </center></th>  
            <th style="text-align:left;" width="100"><center> 税率 </center></th>  

          
        </tr>

  

         <?php 
         $c=0;
		 $heji=0;
         $line=1;
        //  while ($myrow2 = DB_fetch_array($result2)) {
  $myrow2 = DB_fetch_array($result2);
           
         ?>
    
		<tr>
            <td style="text-align:left;" ><center>初始信息</center></td>
            <td style="text-align:left;" ><center><?php echo $myrow2['item_no'];?></center></td>
            <td style="text-align:left;"><center><?php echo $myrow2['item_name'];?></center></td>
            <td style="text-align:left;" ><center><?php echo $myrow2['item_desc'];?></center></td>
            <td style="text-align:left;" ><center><?php echo $myrow2['lot_num'];?></center></td>
            <td style="text-align:left;"><center><?php echo $myrow2['quantity'];?></center></td>
            <td style="text-align:left;"><center><?php echo $myrow2['line_amount'];?></center></td>
            <td style="text-align:left;"><center><?php echo $myrow2['tax_name'];?></center></td>
        </tr >
        <tr>
            <td style="text-align:left;" ><center>库存信息</center></td>
            <td style="text-align:left;" ><center><?php echo $myrow2['item_no'];?></center></td>
            <td style="text-align:left;"><center><?php echo $myrow2['item_name'];?></center></td>
            <td style="text-align:left;" ><center><?php echo $myrow2['item_desc'];?></center></td>
            <td style="text-align:left;" ><center><?php echo $myrow2['lot_num'];?></center></td>
            <td style="text-align:left;"><center><?php echo $myrow2['onhand_quantity'];?></center></td>
            <td style="text-align:left;"><center><?php echo round($myrow2['onhand_amount'],2);?></center></td>
            <td style="text-align:left;"><center><?php echo $myrow2['tax_name'];?></center></td>
        </tr >
        <tr>
            <td style="text-align:left;" ><center>确认信息</center></td>
            <td style="text-align:left;" ><center></center></td>
            <td style="text-align:left;"><center></center></td>
            <td style="text-align:left;" ><center></center></td>
            <td style="text-align:left;" ><center></center></td>
            <td style="text-align:left;"><center></center></td>
            <td style="text-align:left;"><center><?php echo $myrow2['change_amount'];?></center></td>
            <td style="text-align:left;"><center><?php echo number_format($myrow2['change_tax_name'] * 100) . '%';?></center></td>
        </tr >
			
			
					   
        <?php 
        $line++;
        $heji=$heji+$myrow2['no_tax_amount'];
        // } 
                

        ?>


       

    </table>
             
		<?php
        if($myrow7['approve_date'] > 0){
$approve_date = date('Y-m-d', $myrow7['approve_date']);
        }else{
$approve_date = '';

        }
        
        ?>

    <table width="100%" border="0" cellpadding="0" cellspacing="0">
    <tr>
        <td >制单人：<?php echo $myrow4['realname'];?> </td>
        <td >审核人：<?php echo $myrow4['approve_realname'];?>  </td>
        

    
    </tr>
    </table>
    

</div>


<div class="prall_btm" style="text-align:center">

<input type="button" onclick="print_list()" value="打印"/>

</div>

</body>
</html>