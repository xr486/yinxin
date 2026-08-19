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

$sql5= "SELECT * FROM companies2 ";
 $result5 = DB_query($sql5, $db);
 $myrow5 = DB_fetch_array($result5);

$sql8="SELECT  f.item_no,c.creation_date, f.item_name,
 f.units, c.start_quantity, c.wip_entity_name,c.plan_start_date, c.wip_entity_id,replace(c.wip_entity_name,'-','') wip_name 
FROM  wip_jobs_all c,sf_item_no f, wip_mo_print g
where   c.primary_item=f.item_no AND c.wip_entity_name = g.wip_entity_name
  and g.created_by = '" .$_SESSION['UserID']."'
  ";
   
  $result6 = DB_query($sql8, $db);
 
?>




<!DOCTYPE html>
<html>

<head>
    <title>打印工单</title>
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
<?php
while ($myrow6 = DB_fetch_array($result6)) {

   ?>

<table width="100%" border="0">

 
  <tr>
  <td style="text-align:center;" ><font size="6" >工单打印</font></td>
  <td id="number_<?php echo $myrow6['wip_entity_id']  ?>" style="display:none;"><?php echo $myrow6['wip_entity_name'];?></td>
	   <td colspan="4" rowspan="2"><center><img id="barcode_<?php echo $myrow6['wip_entity_name'] ?>" src="<?= './uplode/'.$myrow6['wip_entity_name'].'.png'?>" height="50px;"></center></td>

     <script src="javascript/jquery-1.10.2.min.js" type="text/javascript" ></script>
<!-- 生成条形码 -->
<script type="text/javascript">
var number_<?php echo $myrow6['wip_entity_id'] ?>=$('#number_'+<?php echo $myrow6['wip_entity_id']?>).text();
$.ajax({
  url: 'barcode.php',
  type: 'post',
  dataType: 'json',
  data: {'number': number_<?php echo $myrow6['wip_entity_id']  ?>},
  success:function(data){
    if(data.code=="000000"){
      $('#barcode_'+<?php echo $myrow6['wip_entity_id']  ?>).attr({'src':'./' + data.url});
    }else{
      alert('页面加载失败请刷新！');
    }
  }
});
</script>
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
  <tr>
     <td>产品料号</td>
     <td><?php echo $myrow6['item_no'];?> </td>
   <tr>
     <td>产品名称</td>
     <td colspan="4"><?php echo $myrow6['item_name'];?> </td>
	   <tr>
	  <td>开工数量</td>
     <td><?php echo $myrow6['start_quantity'];?> </td>
	  <td>开工日期</td>
     <td><?php echo date('Y-m-d',$myrow6['plan_start_date']);?> </td>
 

  </tr>
</table> 
 </br>
     <?php 
  }
?>

        <div class="prall_btm" style="text-align:center">
            <input type="button" onclick="print_list()" value="打印"/>
        </div>    
</div>


</body>

</html>
