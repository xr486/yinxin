<?php
ob_start();
include ('includes/login2.inc');
include('includes/session.inc');
include('myfunction.php');
if (isset($_GET['OrderNum'])) {
    $Updatedelivery_num = $_GET['OrderNum'];
} else {
    $Updatedelivery_num = '';
}



$sql5= "SELECT * FROM companies ";
 $result5 = DB_query($sql5, $db);
 $myrow5 = DB_fetch_array($result5);

 $sql = "SELECT distinct a.transaction_date,a.wip_entity_name,c.depart_code,a.creation_date,c.realname,a.subinventory_from,a.trans_num,(SELECT realname from www_users d where d.userid = a.sub_approved_by ) sub_realname,a.sub_approve_date,e.vendor_name  FROM inv_transactions_all_temp a, www_users c,po_headers_all d,vendors e where  a.created_by = c.userid and a.trans_num = '" .$Updatedelivery_num."' and a.po_num = d.po_num and d.vendor_code = e.vendor_code ";
        //  echo $sql;
        $result = DB_query($sql, $db);
	$myrow = DB_fetch_array($result)

?>
 
   


<!DOCTYPE html>
<html>
<head>
    <title>打印退料单</title>
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
        <span>记录编号：YXZD-WJ-11-R05</span>
        <span>版本号：A/0</span>
    </div>

<p class="p2">采 购 退 料 单</p>

    <table width="100%" border="0" cellpadding="0" cellspacing="0" >

        <tr>
            
            <td width="100" >供应商名称：</td>
            <td><?php echo $myrow['vendor_name'];?></td>
            <td width="80" ></td>
            <td></td>
        </tr>
        <tr>
        <td  >退料单号：</td>
        <td><?php echo $myrow['trans_num'];?></td>
            <td>退料日期：</td>
            <td><?php echo date('Y-m-d', $myrow['creation_date']);?></td>
            
        </tr>
      
        <tr>
            
            <td></td>
            <td></td>
        </tr>
    </table>

 

   

     <?php
  $sql2 = "SELECT c.wip_entity_name,c.quantity,b.item_no,b.item_desc,b.item_name, c.uom,c.remark ,b.units,c.lot_num,c.subinventory_from
  FROM  inv_transactions_all_temp c,sf_item_no b  
      where  c.item_no=b.item_no and   c.trans_num = '" .$Updatedelivery_num."' 
       ";
		// ECHO  $sql2;
        $result2 = DB_query($sql2, $db);
?>

   
 
    

    <table width="100%"  cellpadding="0" cellspacing="0" class="content_tab" >
    
        <tr>
            <th style="text-align:left;"  width="40" ><center>序号</center></th>
            <th style="text-align:left;"  width="80" ><center>物料编码</center></th>
            <th style="text-align:left;" width="150"><center>物品名称 </center></th>  
            <th style="text-align:left;" width="100"><center>规格型号 </center></th>
            <th style="text-align:left;" width="100"><center>SN/批号</center></th>
            <th style="text-align:left;" width="40"><center>单位</center></th>
            <th style="text-align:left;" width="72"><center> 退料数量 </center></th>   
            <th style="text-align:left;" width="72"><center> 退料仓库 </center></th>  
          
        </tr>

  

         <?php 
         $c=0;
		 $heji=0;
         $line=1;
         while ($myrow2 = DB_fetch_array($result2)) {
           
         ?>
    
		<tr>
            <td style="text-align:left;" ><center><?php echo $line;?><br/></center></td>
            <td style="text-align:left;" ><center><?php echo $myrow2['item_no'];?><br/></center></td>
            <td style="text-align:left;"><center><?php echo $myrow2['item_name'];?></center></td>
            <td style="text-align:left;" ><center><?php echo $myrow2['item_desc'];?></center></td>
            <td style="text-align:left;"><center><?php echo $myrow2['lot_num'];?></center></td>
            <td style="text-align:left;"><center><?php echo $myrow2['units'];?></center></td>
            <td style="text-align:left;"><center><?php echo $myrow2['quantity'];?></center></td>
            <td style="text-align:left;"><center><?php echo $myrow2['subinventory_from'];?></center></td>
            
        </tr >
			
			
					   
        <?php 
        $line++;
        $heji=$heji+$myrow2['quantity'];
        } 
                

        ?>


       

    </table>
             
			<?php
            if($myrow['sub_approve_date'] > 0){
$sub_approve_date = date('Y-m-d',$myrow['sub_approve_date']);
            }else{
                $sub_approve_date = '';
            }
            ?>

    <table width="100%" border="0" cellpadding="0" cellspacing="0">
    <tr>
        <td width="30">申请人/日期：</td>
        <td width="150"><?php echo $myrow['realname'];?>   <?php echo date('Y-m-d',$myrow['creation_date']);?></td>
        <td width="30">审核人/日期：</td>
        <td width="72"><?php echo $myrow['sub_realname'];?>   <?php echo $sub_approve_date;?></td>
      
        
    </tr>
    </table>
    <span>仓库（第一联）                制单（第二联）                    财务（第三联）</span>

</div>


<div class="prall_btm" style="text-align:center">

<input type="button" onclick="print_list()" value="打印"/>

</div>

</body>
</html>