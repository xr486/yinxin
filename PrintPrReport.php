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


$sql1= "SELECT a.depart_name,a.creation_date,a.remark,a.created_by,a.approve_by,a.order_type,c.item_use FROM pr_headers_all a, pr_lines_all b, sf_item_no c where a.pr_num = b.pr_num and b.stockid = c.item_no
and a.pr_num = " . "'".$Updatedelivery_num."'";

 $result1 = DB_query($sql1, $db);
 $myrow1 = DB_fetch_array($result1);

$sql5= "SELECT realname,phone FROM www_users WHERE userid='".$myrow1['created_by']."'";
 
 $result5 = DB_query($sql5, $db);
 $myrow5 = DB_fetch_array($result5);

$sql6= "SELECT realname,phone FROM www_users WHERE userid='".$myrow1['approve_by']."'";
 
 $result6 = DB_query($sql6, $db);
 $myrow6 = DB_fetch_array($result6);
?>
   


<!DOCTYPE html>
<html>
<head>
    <title>打印合同</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <link href="" rel="stylesheet" type="text/css">
    <script type="text/javascript" src="/JXC/statics/base/js/jQuery1.7.2.js"></script>
    <style>
        .prall { width: 720px;margin: 0 auto;height: auto; overflow: hidden; border: 1px solid #e7e7e7;  border-radius: 5px; padding: 10px; }

        .prall_tit { width: 100%; height: 30px; text-align: right;}

        .prall_tit input {width: 80px;height: 30px; font-size: 12px;color: #333; border-radius: 5px;border: 1px solid #e7e7e7;margin-left: 10px;cursor: pointer;}

        .tab {width: 100%;height: auto;overflow: hidden;margin: 5px 0;font-size: 14px;line-height: 18px;}

        .tab input {vertical-align: middle;margin: 0 10px 0 10px;}
        .prall_btm{width:100%;height: auto;font-family:courier; overflow: hidden;text-align: right;margin:15px 0 0;}
        .prall_btm input:first-child{width:80px;height: 30px;text-align: center;font-size: 14px;color: #fff;background: #52B100;border:1px solid #e7e7e7;border-radius: 5px;margin-right: 80px;cursor: pointer;}
        .prall_btm input:last-child{width:80px;height: 30px;text-align: center;font-size: 14px;color: #333;border:1px solid #e7e7e7;border-radius: 5px;margin-right: 0px;cursor: pointer;}
        .prall_ctit{font-size: 18px;color:#333;font-weight:bold;text-align: center;}
        td{
          padding: 4px;
          white-space: nowrap;
        }
        .p1,
        .p1 td{
          font-size: 14px;
        }
        .p2,
        .p2 td{
          font-size: 16px;
          
          
        }
        .p3,
        .p3 td{
          font-size: 12px;
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


<div class="prall" style="position: relative;">
 
<table width="700" border="0">
  <tr><td>
<table width="400" border="0">
</table>
</td>
<td>

</td>
  </tr>

        <!-- 打印时间：<? echo date('Y-m-d h:i:s')?> -->
    <!-- </div> -->  

    <div class="tab">

 
    


   
    <!-- <HR width="100%"  color="black" SIZE=1/> -->
    

        <table width="100%" border="1" cellpadding="0" cellspacing="0">
		
            <tr>
<td colspan="8" ><center style="font-size: 36px;">请  购  单</td>
            </tr>
            <tr class="p2">
              <td colspan="2" style="text-align:left;"><b>请购部门：</td>
              <td colspan="2" style="text-align:left;"><center><?php echo $myrow1['depart_name'];?></td>
              <td colspan="2" style="text-align:left;"><b>请购单号：</td>
              <td colspan="2" style="text-align:left;"><?php echo $Updatedelivery_num;?></td>
            </tr>
            <tr class="p2">
              <td colspan="2"><b>请购人：</td>
              <td><center><?php echo $myrow5['realname'];?></td>
              <td><b>请购时间：</td>
              <td colspan="4"><center><?php echo  date('Y-m-d',$myrow1['creation_date']) ;?></td>
            </tr>
            <?php 	 	
        $sql2 = "SELECT pr_num,p.line,stockid,s.item_name,s.item_desc,uom,quantity,all_quantity,po_num,po_line,need_date,subinventory_code,po_num,po_line,prtopo_date,prtopo_quantity,p.remark,p.chang,p.kuan,p.gao,s.item_use,s.project_name
        FROM pr_lines_all p,sf_item_no s
       where 1=1 and p.stockid=s.item_no
and pr_num = " . "'".$Updatedelivery_num."'";
$sql2 .= "  order by  p.line";
$result2 = DB_query($sql2, $db);
// $myrow2 = mysqli_fetch_array($result2);


?>

   
    <!-- <HR width="100%"  color="black" SIZE=1/> -->
    


		
            <tr class="p1">
                <td style="text-align:left;" width="56" ><center><b>项次</td> 
                <td style="text-align:left;" width="146" ><center><b>料号</td> 
                <td style="text-align:left;" width="125"><center><b>料号名称 </center></td>  
                 <td style="text-align:left;"  width="298"><center><b>规格型号 </center></td>
                  <td style="text-align:left;" width="66"><center><b>单位 </center></td>  
                <td style="text-align:left;" width="66"><center> <b>数量 </center></td> 
				<td style="text-align:left;" width="116"><center> <b>需求时间 </center></td> 
<?php
if($myrow1['item_use'] == 'Y'){
  ?>
<td style="text-align:left;" width="116"><center> <b>项目名称 </center></td> 

  <?php
}


?>
		

 
            </tr>

    
    
  

  


            <?php 
         $c=0;
     $heji=0;
         while ($mysql2 = DB_fetch_array($result2)) {
           if ($mysql2['uom']=='KG') {
            if($myrow1['order_type'] == '方料'){

              $mysql2['item_desc']=$mysql2['chang'].'*'.$mysql2['kuan'].'*'.$mysql2['gao'];
            }else {
              $mysql2['item_desc']=$mysql2['chang'].'*'.$mysql2['kuan'];

            }
		   } else {
		     $mysql2['item_desc']=$mysql2['item_desc'];
		   }
         ?>
          
		<tr class="p3">
                <td style="text-align:center;" ><center><b><?php echo $mysql2['line'];?></center></td>
				<td style="text-align:center;" ><?php echo $mysql2['stockid'];?></td>
				<td style="text-align:center;" ><?php echo $mysql2['item_name'];?></td>
                <td style="text-align:center;"><center><?php echo $mysql2['item_desc'];?></center></td>
				 
				<td style="text-align:center;"><center><?php echo $mysql2['uom'];?></center></td>
    
             
                <td style="text-align:center;"><center><?php echo $mysql2['quantity'];?></center></td>
                <td style="text-align:center;"><center><?php echo date('Y-m-d',$mysql2['need_date']);?></center></td>
       
                <?php
if($myrow1['item_use'] == 'Y'){
  ?>
                <td style="text-align:center;"><center><?php echo $mysql2['project_name'];?></center></td>


  <?php
}


?>
			
					   
					   <?php 
            //  $heji=$heji+($myrow2['quantity'] * $myrow['price']) ;
					  //  $line=$myrow2['line']+1;
					   } 				   		 
             
					 ?>
           <tr class="p2">
             <td colspan="2">请购人：<?php echo $myrow5['realname'];?></td>
             <td>部门主管：<?php echo $myrow6['realname'];?></td>
             <td colspan="3">总经理： </td>
             <td colspan="2">采购： </td>
           </tr>

  
<!-- 
         <?php 
         $c=0;
		 $heji=0;
         while ($myrow2 = DB_fetch_array($result2)) {
           
         ?>
       
		 <tr>
                <td style="text-align:left;" ><center>@<br/></center></td>
				<td style="text-align:left;" "><center>@</center></td>
				<td style="text-align:left;"><center>@</center></td>
				<td style="text-align:left;"><center>@</center></td>
				
				<td style="text-align:left;"><center>@</center></td>
    
             
                <td style="text-align:left;"><center>@</center></td>

			
					   
					   <?php 
					   $heji=$heji+$myrow2['line_amount'];
					   } 
					   		 
             
           ?>  -->
                      
					  </table>
			

			<body>
        <br>


         <!-- <?php echo date('Y') ?> -->
       

        <div class="prall_btm" style="text-align:center">

            <input type="button" onclick="print_list()" value="打印"/>
          
        </div>
    </div>
</div>
</body>
</html>