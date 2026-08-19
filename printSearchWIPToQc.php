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

 $sql4= "SELECT    a.*,b.*,(SELECT realname FROM www_users where userid=b.created_by) realname,d.item_use
 FROM  wip_jobs_all a, sf_item_no d,wip_qc_lines_all b
     where   b.trans_num = '" .$Updatedelivery_num."' and a.status_type in ('开始')  and a.primary_item=d.item_no and a.wip_entity_name=b.wip_entity_name

      ";
 
     $result4 = DB_query($sql4, $db);
$myrow4 = DB_fetch_array($result4);



$time=time();

?>
 
   


<!DOCTYPE html>
<html>
<head>
    <title>打印请验单</title>
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
        <span>记录编号：YXZD-WJ-14-R03</span>
        <span>版本号：A/1</span>
    </div>

<p class="p2">产品请验单</p>

    <table width="100%" border="0" cellpadding="0" cellspacing="0" >

        <tr>
            <td width="140" >工单号/采购单号：</td>
            <td><?php echo $myrow4['wip_entity_name'];?></td>
            <td width="80" >打印日期：</td>
            <td><?php echo date('Y-m-d', $time);?></td>
        </tr>
        
        <tr>
            
            <td></td>
            <td></td>
        </tr>
    </table>

 

   

     <?php
 
        $sql2 = "SELECT a.primary_item ,a.wip_entity_name,a.status_type,toqc_quantity,b.good_quantity,b.bad_quantity,a.plan_start_date,
        a.creation_date,d.item_name,(a.start_quantity-a.quantity_completed)  wait, a.toqc_qty,(b.toqc_quantity-b.good_quantity-b.bad_quantity) quantity_wait,a.wip_entity_id,d.units,b.wip_qc_id,a.all_osp,b.lot_num,b.shengchan_date,d.item_desc,d.item_use
              from wip_jobs_all a, sf_item_no d,wip_qc_lines_all b
       where   a.status_type in ('开始')  and a.primary_item=d.item_no
       and a.wip_entity_name=b.wip_entity_name
       and a.start_quantity>a.quantity_completed 	
       and b.toqc_quantity-b.good_quantity-b.bad_quantity > 0 
        and b.trans_num = '".$Updatedelivery_num."' ";
        $result2 = DB_query($sql2, $db);
?>

   
 
    

    <table width="100%"  cellpadding="0" cellspacing="0" class="content_tab" >
    
        <tr>
            <th style="text-align:left;"  width="50" ><center>序号</center></th>
            <th style="text-align:left;"  width="180" ><center>物料编码</center></th>
            <th style="text-align:left;" width="180"><center>物品名称 </center></th>  
            <th style="text-align:left;" width="180"><center>规格型号 </center></th>
            <th style="text-align:left;" width="180"><center>批号/SN </center></th>

            <?php
if($myrow4['item_use'] == 'Y'){
  ?>
<td style="text-align:left;" width="116"><center> <b>项目名称 </center></td> 

  <?php
}


?>
            <th style="text-align:left;" width="60"><center>单位</center></th>
            <th style="text-align:left;" width="72"><center> 检验数量 </center></th>  
            <th style="text-align:left;" width="72"><center> 不良数量 </center></th>  
            <th style="text-align:left;" width="72"><center> 检验结论 </center></th>  
            <th style="text-align:left;" width="72"><center> 备注 </center></th>  
        </tr>

         <?php 
         $c=0;
		 $heji=0;
         $line=1;
         while ($myrow2 = DB_fetch_array($result2)) {
           
         ?>
    
		<tr>
            <td style="text-align:left;" ><center><?php echo $line;?><br/></center></td>
            <td style="text-align:left;" ><center><?php echo $myrow2['primary_item'];?><br/></center></td>
            <td style="text-align:left;"><center><?php echo $myrow2['item_name'];?></center></td>
            <td style="text-align:left;" ><center><?php echo $myrow2['item_desc'];?></center></td>
            <td style="text-align:left;" ><center><?php echo $myrow2['lot_num'];?></center></td>
            <?php
if($myrow4['item_use'] == 'Y'){
  ?>
                <td style="text-align:center;"><center><?php echo $myrow2['project_name'];?></center></td>


  <?php
}


?>
            <td style="text-align:left;"><center><?php echo $myrow2['units'];?></center></td>
            <td style="text-align:left;"><center><?php echo $myrow2['quantity_wait'];?></center></td>
            <td style="text-align:left;"><center></center></td>
  
            <td style="text-align:left;"><center></center></td>
            <td style="text-align:left;"><center></center></td>
            
        </tr >
			
			
					   
        <?php 
        $line++;
        $heji=$heji+$myrow2['quantity'];
        } 
                

        ?>


       

    </table>
             
			

    <table width="100%" border="0" cellpadding="0" cellspacing="0">
    <tr>
        <td >制单人：<?php echo $myrow4['realname'];?>  </td>
        <td >检验员：</td>
        <td >仓库接收人：</td>
        
   
      
    
    </tr>
    </table>
    <span>仓储物料部（第一联）                制单部门（第二联）                    质量管理部（第三联）</span>

</div>


<div class="prall_btm" style="text-align:center">

<input type="button" onclick="print_list()" value="打印"/>

</div>

</body>
</html>