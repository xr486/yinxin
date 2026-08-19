<?php
ob_start();
include ('includes/login2.inc');
include('includes/session.inc');
include('myfunction.php');
if (isset($_GET['Updatewip_entity_name'])) {
    $Updatewip_entity_name = $_GET['Updatewip_entity_name'];
} else {
    $Updatewip_entity_name = '';
}

if (isset($_POST['return'])) {
    header('Location: DeliveryReport.php');
}

$sql5= "SELECT * FROM companies ";
 $result5 = DB_query($sql5, $db);
 $myrow5 = DB_fetch_array($result5);

$sql8="SELECT  c.plan_start_date,c.so_header_number, c.version,f.item_no,c.creation_date,f.item_name,f.item_desc,c.start_quantity, c.wip_entity_name,(
  select customer_order_number from so_headers_all b where b.order_number=c.so_header_number ) customer_order_number,(
  select customer_code from so_headers_all b where b.order_number=c.so_header_number ) customer_code
FROM    wip_jobs_all c, sf_item_no f 
where c.primary_item=f.item_no 
  and c.wip_entity_name = '" .$Updatewip_entity_name."'  ";
 
  $result6 = DB_query($sql8, $db);
 $myrow6 = DB_fetch_array($result6);

$sql9="SELECT  b.realname
FROM bom_routings_all a,www_users b
where  assembly_item_no = '" .$myrow6['stockid']."' 
and a.created_by=b.userid  ";  
$result9 = DB_query($sql9, $db);
 $myrow9 = DB_fetch_array($result9); 

?>




<!DOCTYPE html>
<html>
<head>
    <title>打印流程卡</title>
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
  <td style="text-align:center;" ><font size="6" ><?php echo $myrow5['coyname'];?></font></td>
  <td id="number" style="display:none;"><?php echo $Updatewip_entity_name;?></td>
       
       <td colspan="4" rowspan="2"><center><img id="barcode" src="" height="50px;"></center></td>
 </tr>
</table>

     <table width="100%" border="1" cellpadding="0" cellspacing="0">
	  <tr> 

  </tr> 
  <tr>
  </tr>
	    <tr>
    <td>工单号</td>
  <td><?php echo $myrow6['wip_entity_name'];?> </td>
    
 <td>规格型号</td>
  <td><?php echo $myrow6['item_desc'];?>  </td>
  </tr>
	   <tr>
    <td >料号</td> 
	<td ><?php echo $myrow6['item_no'];?> </td>

   <td>客户订单</td>
   <td ><?php echo $myrow6['customer_order_number'];?> </td>
 
  
  </tr>
 

  
 
   <tr>
    
   <td>版本:</td>
  <td><?php echo $myrow6['version'];?>  </td>
   <td>机型名称:</td>
  <td><?php echo $myrow6['item_name'];?>  </td>
  </tr>

   <tr>
   
 
    <td>开工日期:</td>
  <td><?php  echo date('Y-m-d', $myrow6['plan_start_date']) ?></td>
  <td>投单日期:</td>
  <td><?php  echo date('Y-m-d', $myrow6['creation_date']) ?></td>
  
  </tr>

 <tr>
    
 <td >工艺建立人:</td>
  <td ><?php echo $myrow9['realname']; ?> </td>
     <td>客户:</td>
  <td><?php echo $myrow6['customer_code'];?>  </td>
  </tr>
 
</table> 

    <div class="tab">


     <?php
  $sql2 = "SELECT a.*  FROM  wip_operation_plan a  
        where   a.wip_entity_name = '" .$Updatewip_entity_name."'
       order by CONVERT(operation_seq_num,SIGNED)   ";
        $result2 = DB_query($sql2, $db);
?>


    <!-- <HR width="100%"  color="black" SIZE=1/> -->


        <table width="100%" border="1" cellpadding="0" cellspacing="0"  style="table-layout:fixed;">
            <tr>
                <th style="text-align:left;" width="40" ><center>序号</th>
                 <th style="text-align:left;" width="80"><center>工序名称 </center></th>  
                 <th style="text-align:left;" width="90"><center>预计工时(秒) </center></th>
                 <th style="text-align:left;" width="40"><center>标准产能 </center></th>
                 <!-- <th style="text-align:left;" width="40"><center>标准人力 </center></th> -->
                 <th style="text-align:left;" width="190"><center>工作内容 </center></th>
               <th style="text-align:left;" width="70"><center>预产数量 </center></th>   
               <th style="text-align:left;" width="70"><center>交件数量 </center></th>      
                <th style="text-align:left;" width="70"><center> 作业者 </center></th>      
                <th style="text-align:left;" width="70"><center> 检验首件确认 </center></th>  
            </tr>


  </tr>


         <?php
         $c=0;
		 $heji=0;
         while ($myrow2 = DB_fetch_array($result2)) {

         ?>

		<tr>
                 <td style="text-align:left;" height="50px" ><center><?php echo $myrow2['operation_seq_num'];?><br/></center></td>
				<td style="text-align:left;"><center><?php echo $myrow2['operation_code'];?></center></td> 
				<td style="text-align:left;"><center><?php echo $myrow2['standard_time'];?></center></td>
				<td style="text-align:left;"><center><?php echo $myrow2['channeng'];?></center></td>
				<!-- <td style="text-align:left;"><center><?php echo $myrow2['renli'];?></center></td> -->
				<td style="text-align:left;"><center><?php echo $myrow2['shengguan_remark'];?></center></td>
				<td style="text-align:left;;"><center><?php echo round($myrow2['begin_quantity']);?></center></td>
        <td style="text-align:left;;"><center><?php ?></center></td>
				 <td  style="text-align:left;word-wrap:break-word"> <?php ?> </td>
				 <td  style="text-align:left;word-wrap:break-word"> <?php ?> </td></tr >



					   <?php
					   $heji=$heji+$myrow2['delivery_quantity'];
					   }


					 ?>


				 
           
					  </table>

					 
            <div class="tab">
     <?php
  $sql2 = "select a.plan_start_date,a.so_header_number,a.so_line_number,b.segment1,c.item_desc,c.item_name,b.comments,quantity_issued,b.required_quantity,a.wip_entity_name,b.quantity_per_assembly,b.operation_seq_num,(select sum(quantity_issued- required_quantity) from wip_material_requierments w,wip_jobs_all j where w.segment1=c.item_no  and quantity_issued>required_quantity and w.wip_entity_name=j.wip_entity_name and j.status_type='开始' ) chaofa_qty,(select sum(cc.quantity)  from inv_onhand_quantity_all cc where cc.stockid=c.item_no  ) onhand_quantity
						from wip_jobs_all a,wip_material_requierments b,sf_item_no c 
                        where b.wip_entity_name =a.wip_entity_name and b.segment1=c.item_no and  a.wip_entity_name = '" .$myrow6['wip_entity_name']."'
       order by b.operation_seq_num,segment1  ";
        $result2 = DB_query($sql2, $db);
?>
    <!-- <HR width="100%"  color="black" SIZE=1/> -->


        <table width="100%" border="1" cellpadding="0" cellspacing="0"  style="table-layout:fixed;">
            <tr> <th style="text-align:left;" width="15"   ><center>序号</th>
		 
                <th style="text-align:left;" width="60"   ><center>料号</th>
                 <th style="text-align:left;" width="150"><center>料号名称 </center></th>   
               <th style="text-align:left;" width="30"><center>单耗 </center></th>  
               <th style="text-align:left;" width="30"><center>需求量 </center></th>     
                <th style="text-align:left;" width="30"><center> 实发量 </center></th>    
                <th style="text-align:left;" width="40"><center> 退料量 </center></th> 
                <th style="text-align:left;" width="40"><center> 备注 </center></th>   
            </tr>
  </tr>
   
         <?php
         $c=0;
		 $heji=0;
		 $line=0;
         while ($myrow2 = DB_fetch_array($result2)) {
          $line=$line+1;
         ?>
  
				 
		<tr><td style="text-align:left;"><?php echo $line;?></td> 
 
                 <td style="text-align:left;font-size: 17px;" height="30px"><?php echo $myrow2['segment1'];?><br/></td>
				<td style="text-align:left;"><?php echo $myrow2['item_name'].$myrow2['item_desc'];?></td> 
				
			 
				
				<td style="text-align:center;font-size: 17px;"><?php echo $myrow2['quantity_per_assembly'];?></td>  
				<td style="text-align:center;font-size: 17px;"><?php echo $myrow2['required_quantity'];?></td>  
				<td style="text-align:right;"><?php echo '';?></td> 
				<td style="text-align:right;"><?php echo '';?></td> 
				<td style="text-align:right;"><?php echo '';?></td> 
				   </tr >
                  <?php
					   $heji=$heji+$myrow2['delivery_quantity'];
					   }
				 ?>
					  </table>		 



         <!-- <?php echo date('Y') ?> -->


        <div class="prall_btm" style="text-align:center">

            <input type="button" onclick="print_list()" value="打印"/>

        </div>
    </div>
</div>
<script src="javascript/jquery-1.10.2.min.js" type="text/javascript" ></script>
<!-- 生成条形码 -->
<script type="text/javascript">
var number=$('#number').text();
$.ajax({
  url: 'barcode.php',
  type: 'post',
  dataType: 'json',
  data: {'number': number},
  success:function(data){
    if(data.code=="000000"){
      $('#barcode').attr({'src':'./' + data.url});
    }else{
      alert('页面加载失败请刷新！');
    }
  }
});
</script>
<script type="text/javascript">
var code=$('#code').text();
$.ajax({
  url: 'phpcode.php',
  type: 'post',
  dataType: 'json',
  data: {'code': code},
  success:function(data){
    if(data.code=="000000"){
      $('#phpcode').attr({'src':'./' + data.url});
    }else{
      alert('页面加载失败请刷新！');
    }
  }
});

</script>
</body>
</html>