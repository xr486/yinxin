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

 $sql4= "SELECT    a.*,b.vendor_name,c.delivery_num
 FROM  po_rcv_receipt_header a,vendors b,po_rcv_transactions c
     where   c.delivery_num = '" .$Updatedelivery_num."' and a.vendor_code = b.vendor_code and c.receipt_num = a.receipt_num

      ";
     $result4 = DB_query($sql4, $db);
$myrow4 = DB_fetch_array($result4);


$sql6= "SELECT  DISTINCT b.item_category1,f.creation_date,f.created_by,w.realname,

depart_code


FROM  sf_item_no b,po_rcv_transactions f,www_users w
  where f.stockid = b.item_no and f.created_by = w.userid and f.delivery_num = '" .$Updatedelivery_num."'
   ";
  $result6 = DB_query($sql6, $db);
$myrow6 = DB_fetch_array($result6);

$sql8= "SELECT  DISTINCT po_num
FROM  po_rcv_transactions f
  where   f.delivery_num = '" .$Updatedelivery_num."'
   ";
  $result8 = DB_query($sql8, $db);
$sql7= "SELECT   b.item_category1,f.creation_date,f.approved_by,f.approve_date,w.realname
FROM  sf_item_no b,po_rcv_transactions f,www_users w
  where   f.delivery_num = '" .$Updatedelivery_num."'
  and f.stockid = b.item_no and f.approved_by = w.userid and f.transaction_type = 'POIN'
   ";
  $result7 = DB_query($sql7, $db);
$myrow7 = DB_fetch_array($result7);
$time=time();

?>
 
   


<!DOCTYPE html>
<html>
<head>
    <title>打印入库单</title>
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
    <p class="p1"><?php echo $myrow5['coyname'];?></p>
    <div class="version">
        <span>记录编号：YXZD-WJ-11-R04</span>
        <span>版本号：A/1</span>
    </div>

<p class="p2">入库单</p>

    <table width="100%" border="0" cellpadding="0" cellspacing="0" >

        <tr>
            <td width="80" >入库单号：</td>
            <td><?php echo $myrow4['delivery_num'];?></td>
            <td width="170" >供应商/入库部门：</td>

            <td><?php echo $myrow6['depart_code'];?></td>

        </tr>
        <tr>
            <td>入库日期：</td>
            <td><?php echo date('Y-m-d', $myrow6['creation_date']);?></td>
            <td>采购单号/生产工单号：</td>
            <td><?php 
            $i = 1;
    $ListCount = DB_num_rows($result8);

               while ($myrow8 = DB_fetch_array($result8)) {
                if($i == $ListCount){
                    
                    echo $myrow8['po_num'];
                }else{
                    echo $myrow8['po_num'].'/';
                    
                }
                $i++;
               }
            
            ?></td>
        </tr>
        <tr>
        <td>备注：</td>
            <td></td>
        </tr>
        <tr>
            
            <td></td>
            <td></td>
        </tr>
    </table>

 

   

     <?php
 
        $sql2 = "select rl.receipt_line_id,
        rh.receipt_num,rl.receipt_line,
        a.po_num,
        b.line,
        b.stockid,
        c.item_desc,
        c.item_name,
        a.vendor_code,
        d.vendor_name,
        rl.uom,
        ifnull(b.quantity,0) quantity,
        ifnull(rl.DELIVERY_QUANTITY,0) DELIVERY_QUANTITY,
        rl.subinventory_code,b.price,b.line_amount,
        CAST(b.price AS DECIMAL(20, 6))/(1+CAST(a.tax_rate AS DECIMAL(20, 4))) no_tax_price,
        f.transaction_quantity,f.lot_num,c.huohao,c.units,a.tax_rate,a.tax_flag,(CAST(b.line_amount AS DECIMAL(20, 6))/CAST(b.quantity AS DECIMAL(20, 6))/(1+CAST(a.tax_rate AS DECIMAL(20, 4)))*CAST(f.transaction_quantity AS DECIMAL(20, 2))) no_tax_amount,


        FROM  po_headers_all a, po_lines_all b, po_rcv_receipt_header rh, po_rcv_receipt_line  rl, sf_item_no c,vendors d,po_rcv_transactions f
        WHERE a.vendor_code=d.vendor_code
        and a.po_num=b.po_num    
        and b.po_num=rl.po_num
        and b.line=rl.po_line
        and rl.receipt_num=rh.receipt_num
        and f.receipt_num=rh.receipt_num
        and rl.receipt_line=f.receipt_line   
        and f.po_num=rl.po_num
        and f.po_line=rl.po_line
        and f.transaction_type='POIN'
        and b.stockid=c.item_no 
        and f.delivery_num = '".$Updatedelivery_num."'
        and a.tax_flag = 'Y' ";

              $sql2 .= "union select rl.receipt_line_id,
        rh.receipt_num,rl.receipt_line,
        a.po_num,
        b.line,
        b.stockid,
        c.item_desc,
        c.item_name,
        a.vendor_code,
        d.vendor_name,
        rl.uom,
        ifnull(b.quantity,0) quantity,
        ifnull(rl.DELIVERY_QUANTITY,0) DELIVERY_QUANTITY,
        rl.subinventory_code,b.price,b.line_amount,
        b.price no_tax_price, f.transaction_quantity,f.lot_num,c.huohao,c.units,a.tax_rate,a.tax_flag,(CAST(b.line_amount AS DECIMAL(20, 6))/CAST(b.quantity AS DECIMAL(20, 6))*CAST(f.transaction_quantity AS DECIMAL(20, 2))) no_tax_amount
        FROM  po_headers_all a, po_lines_all b, po_rcv_receipt_header rh, po_rcv_receipt_line  rl, sf_item_no c,vendors d,po_rcv_transactions f
        WHERE a.vendor_code=d.vendor_code
        and a.po_num=b.po_num    
        and b.po_num=rl.po_num
        and b.line=rl.po_line
        and rl.receipt_num=rh.receipt_num
        and f.receipt_num=rh.receipt_num
        and rl.receipt_line=f.receipt_line   
        and f.po_num=rl.po_num
        and f.po_line=rl.po_line
        and f.transaction_type='POIN'
        and b.stockid=c.item_no 
        and f.delivery_num = '".$Updatedelivery_num."'
        and a.tax_flag = 'N'
              ";
            //   echo $sql2;
        $result2 = DB_query($sql2, $db);
?>

   
 
    

    <table width="100%"  cellpadding="0" cellspacing="0" class="content_tab" >
    
        <tr>
            <th style="text-align:left;"  width="60" ><center>序号</center></th>
            <th style="text-align:left;"  width="100" ><center>物料编码</center></th>
            <th style="text-align:left;" width="120"><center>物品名称 </center></th>  
            <th style="text-align:left;" width="120"><center>规格 </center></th>
            <th style="text-align:left;" width="120"><center>SN/批号 </center></th>
            <th style="text-align:left;" width="40"><center>单位</center></th>
            <th style="text-align:left;" width="72"><center> 数量 </center></th>  
            <th style="text-align:left;" width="100"><center> 未税单价 </center></th>   
            <th style="text-align:left;" width="100"><center> 未税金额 </center></th>  
            <th style="text-align:left;" width="100"><center> 入库仓库 </center></th>  

          
        </tr>

  

         <?php 
         $c=0;
		 $heji=0;
         $line=1;
         while ($myrow2 = DB_fetch_array($result2)) {
           
         ?>
    
		<tr>
            <td style="text-align:left;" ><center><?php echo $line;?><br/></center></td>
            <td style="text-align:left;" ><center><?php echo $myrow2['stockid'];?><br/></center></td>
            <td style="text-align:left;"><center><?php echo $myrow2['item_name'];?></center></td>
            <td style="text-align:left;" ><center><?php echo $myrow2['item_desc'];?></center></td>
            <td style="text-align:left;" ><center><?php echo $myrow2['lot_num'];?></center></td>
            <td style="text-align:left;"><center><?php echo $myrow2['units'];?></center></td>
            <td style="text-align:left;"><center><?php echo $myrow2['transaction_quantity'];?></center></td>

         
            <td style="text-align:left;"><center><?php echo round($myrow2['no_tax_price'],6);?></center></td>
            <td style="text-align:left;"><center><?php echo round($myrow2['no_tax_amount'],2);?></center></td>
            <td style="text-align:left;"><center><?php echo $myrow2['subinventory_code'];?></center></td>
            
        </tr >
			
			
					   
        <?php 
        $line++;
        $heji=$heji+$myrow2['no_tax_amount'];
        } 
                

        ?>

<tr>
            <td style="text-align:left;"><center>合计</center></td>
            <td style="text-align:left;"></td>
            <td style="text-align:left;"></td>
            <td style="text-align:left;"></td>
            <td style="text-align:left;"></td>
            <td style="text-align:left;"></td>
            <td style="text-align:left;"></td>
            <td style="text-align:left;"></td>
            <td style="text-align:left;"><center><?php echo round($heji,2);?><br/></center></td>
            <td style="text-align:left;"></td>
            </tr >
       

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
        <td >入库人/日期：<?php echo $myrow6['realname'];?>  <?php echo date('Y-m-d', $myrow6['creation_date']);?></td>
        <td >审核人/日期：<?php echo $myrow7['realname'];?>  <?php echo $approve_date;?></td>
        

    
    </tr>
    </table>
    <span>仓库（第一联）                制单（第二联）                    财务（第三联）</span>

</div>


<div class="prall_btm" style="text-align:center">

<input type="button" onclick="print_list()" value="打印"/>

</div>

</body>
</html>