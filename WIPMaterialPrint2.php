<?php
ob_start();
include('includes/session.inc');
include('includes/SQL_CommonFunctions.inc');
//include ('includes/login2.inc');

//include('myfunction.php');
if (isset($_GET['Updatepo_num'])) {
    $Updatepo_num = $_GET['Updatepo_num'];
} else {
    $Updatepo_num = '';
}
if (isset($_POST['return'])) {
    header('Location: PartAccountsReceivable.php');
}  
?>
<!DOCTYPE html>
<html>
<head>

    <title></title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <link href="" rel="stylesheet" type="text/css">
    <script type="text/javascript" src="./statics/base/js/jQuery1.7.2.js"></script>
    <style>
        .prall { width: 800px;margin: 0px auto;height: auto; overflow: hidden; border: 0px solid #e7e7e7;  border-radius: 0px; padding: 0px; }

        .prall_tit { width: 100%; height: 0px; text-align: right;}

        .prall_tit input {width: 80px;height: 20px;font-size: 12px;color: #333; border-radius: 5px;border: 1px solid #e7e7e7;margin-left: 10px;cursor: pointer;}

        .tab {width: 100%;height: auto;overflow: hidden;margin: 3px 0;font-size: 15px;line-height: 15px;}

        .tab input {vertical-align: middle;margin: 0 10px 0 10px;}
        .prall_btm{width:100%;height: auto;overflow: hidden;text-align: right;margin:15px 0 0;}
        .prall_btm input:first-child{width:80px;height: 30px;text-align: center;font-size: 14px;color: #fff;background: #52B100;border:1px solid #e7e7e7;border-radius: 5px;margin-right: 80px;cursor: pointer;}
        .prall_btm input:last-child{width:80px;height: 30px;text-align: center;font-size: 14px;color: #333;border:1px solid #e7e7e7;border-radius: 5px;margin-right: 0px;cursor: pointer;}
        .prall_ctit{font-size: 18px;color:#333;font-weight:bold;text-align: center;height: 10px;}
    </style>

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


    <table>
   <tr> 
    
   
  </tr>
    </table>
	<p  style="text-align:center;font-family:verdana;font-size:25px;color:black">工单领料申请单
    <div class="tab">
    <?php


  $sql = "select distinct request_name,	
h.creation_date,h.created_by
FROM  wip_material_request_lines h
WHERE   h.request_name='" . $Updatepo_num . "' ";

   $result = DB_query($sql, $db);
    while ($myrow = DB_fetch_array($result)) {
		
    ?>
        <table width="100%" border="0" cellpadding="0" cellspacing="0">
            <tr>
                <td style="text-align:left;">申请单号：&nbsp;</td>
                <td style="text-align:left;"><?php echo $myrow['request_name']; ?></td>
              <td style="text-align:left;">打印日期：&nbsp;</td>
               <td style="text-align:left;"><?php echo Date('Y-m-d H:i:s'); ?></td>
            </tr>
            
            <tr> 
            
                <td style="text-align:left;">申请人员</td>
                 <td style="text-align:left;"><?php echo $myrow['created_by']; ?></td>
                <td style="text-align:left;">申请日期:&nbsp;</td>
               <td style="text-align:left;"><?php echo date('Y-m-d H:i:s',$myrow['creation_date']); ?></td>
              
            </tr>
          
            
            </br>
        </table>



   <?php

   }
  $sql2 = "select 
a.request_name,a.need_quantity 	, 
a.item_no,
c.item_desc,
	c.item_name, 
c.units  
FROM  wip_material_request_lines a,
        sf_item_no c 
WHERE  a.item_no=c.item_no 
and a.request_name='" . $Updatepo_num . "'  "  ;


    ?> 
        <table width="100%" border="1" cellpadding="1" cellspacing="0">
            <tr>
                <th style="text-align:left;" width = 50>项目</th>
                <th style="text-align:left;" width = 180>物料</th>
                <th style="text-align:left;" width = 210>物料名称</th>                
                <th style="text-align:left;" width = 210>规格型号</th> 
                <th style="text-align:left;" width = 50>需求量 </th> 
                <th style="text-align:left;" width = 70>实发量 </th>
                <th style="text-align:left;" width = 70>收料人 </th> 
            </tr>
			 

         <?php 
    
             $result2 = DB_query($sql2, $db);
		 $flag=1;
		 $all_amount=0;
		 $line=0;
    while ($myrow2 = DB_fetch_array($result2)) {
         $all_amount= $all_amount + $myrow2['line_amount'] ;
		 $line=$line+1;
         ?>
       
				<tr> 
				<td style="text-align:left;"><?php echo $line;?></td>  
				<td style="text-align:left;"><?php echo $myrow2['item_no'];?></td>  
                <td style="text-align:left;"><?php echo  $myrow2['item_name'];?></td>
                <td style="text-align:left;"><?php echo  $myrow2['item_desc'];?></td> 
                <td style="text-align:left;"><?php echo  $myrow2['need_quantity'];?></td> 
                <td style="text-align:left;"><?php echo  '';?></td>					
                <td style="text-align:left;"><?php echo  '';?></td>	
			 
				</tr>
				
               
		<?php
         $flag=$flag+1;
         }
		 
		 
		 for ($i=$flag;$i<=$flag;$i++){
		 ?>
	
		 <tr>
				<td style="text-align:left;">&nbsp;</td>
				<td style="text-align:left;">&nbsp;</td>
				<td style="text-align:left;">&nbsp;</td>
     
                <td style="text-align:left;">&nbsp;</td>
                <td style="text-align:left;">&nbsp;</td>
              
                <td style="text-align:left;">&nbsp;</td>
                <td style="text-align:left;">&nbsp;</td> 
			
				</tr>
<?php
		 }
		 ?>


      
		</table>
       	</table>

        
        <div class="prall_btm" style="text-align:center">
        
            <input type="button" onclick="print_list()" value="打印"/>
          
        </div>
    </div>
</div>
</body>
</html>