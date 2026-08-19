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

$sql= "SELECT * FROM companies ";
 $result = DB_query($sql, $db);
 $myrow = DB_fetch_array($result);

$sql4= "SELECT order_number,a.customer_code,a.approved_by,need_date,status,order_all_amount,all_line_amount,tax_amount, header_remark,a.creation_date, a.created_by, b.customer_name,a.tax_name,a.qianding_date,customer_address,customer_contacts,bank_name,bank_account,taxpayerid,contacts_phone,contacts_fax,postcode,a.term_name,a.ship_address,a.yunfei_amount  FROM so_headers_all a, customers b  WHERE 1 = 1 AND a.customer_code = b.customer_code  
        and a.order_number = '" .$Updatedelivery_num."'";
   // echo $sql4;      
        $result4 = DB_query($sql4, $db);
 $myrow4 = DB_fetch_array($result4);

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
          font-size: 14px;
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
<table width="700" border="0">
  <tr><td>
<table width="400" border="0">
  <!-- <tr>
  <td font-size: 10px;> <?php echo $myrow['coyname'];?> <td>
 
  <tr>
  <td  font-size: 10px;> <?php echo $myrow['coyname_en'];?></td>
 </tr>
 <tr>
  <td  font-size: 12px;> </td>
 </tr> -->
   
</table>
</td>
<td>

</td>
  </tr>
</table>

     <span><center> <h3>销售合同</h3></center> </span>   

	 
     <table width="800" border="0">

      <tr>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td width="80">合同编号：</td>
      <td><?php echo $Updatedelivery_num;?></td>
      </tr>
  <tr>
    <td width="145">订货单位（甲方）：</td>
    <td width="280"><?php echo $myrow4['customer_name'];?></td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td width="145">供货单位（乙方）：</td>
    <td width="333"><?php echo $myrow['coyname'];?> </td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
 
</table> 
   



   <!--  <span><center><h3>Suzhou &nbsp; East &nbsp; Dragon  &nbsp;FIFE  &nbsp;CO,LTD. </h3></center></span>
    <span>Tel:+86 &nbsp; 512-65757069 &nbsp; Fax:+86 &nbsp;512-65757069 &nbsp; &nbsp;Web: &nbsp;www.dzlwsz-fire.com.cn</span> <br/>
    <span>地址：江苏省苏州工业园区唯华路3号君地曼哈顿广场8号楼7010室</span> 
    <div class="prall_tit">
        <!-- 打印时间：<? echo date('Y-m-d h:i:s')?> -->
    <!-- </div> -->  

    <div class="tab">

 
    

     <?php
  $sql2 = "SELECT order_number,line, line_remark,uom,price,
						quantity,stockid,line_amount,need_date,subinventory_code,a.item_desc,a.item_name
						  
    FROM  so_lines_all c,
       sf_item_no a
        where  a.item_no=c.stockid
        and c.order_number = '" .$Updatedelivery_num."'
        order by line ";
		//echo $sql2;
        $result2 = DB_query($sql2, $db);
?>

   
    <!-- <HR width="100%"  color="black" SIZE=1/> -->
    

        <table width="100%" border="1" cellpadding="0" cellspacing="0">
		<span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;甲乙双方本着平等互利、真诚合作的原则，经友好协商，依照《中华人民共和国合同法》及相关法律规定，同意签署《订货合同》，以便共同遵照执行。今甲方向乙方订购以下产品</span><br>
            <tr>
                <td style="text-align:left;"  ><center>序号</td>
				<td style="text-align:left;"width="150"><center>设备名称</center></td>
				  <td style="text-align:left;" width="250" ><center>产品型号配置</td>
                  <td style="text-align:left;" width="40"><center>单位 </center></td> 
                 <td style="text-align:left;"><center>单价(元)</center></td>
               <td style="text-align:left;"><center>数量 </center></td>
                  <td style="text-align:left;"width="120"><center>合计(元)</center></td>
            </tr>

    
  

  

         <?php 
         $c=0;
		 $heji=0;
         while ($myrow2 = DB_fetch_array($result2)) {
           
         ?>
       
		<tr>
                <td style="text-align:left;" ><center><?php echo $myrow2['line'];?></center></td>
				<td style="text-align:left;" "><center><?php echo $myrow2['item_name'];?></center></td>
				<td style="text-align:left;"><center><?php echo $myrow2['item_desc'];?></center></td>
				<td style="text-align:left;"><center><?php echo $myrow2['uom'];?></center></td>
				<td style="text-align:left;"><center><?php echo round($myrow2['price'],2);?></center></td>
				
				<td style="text-align:left;"><center><?php echo $myrow2['quantity'];?></center></td>
    
             
                <td style="text-align:left;"><center><?php echo $myrow2['price'] * $myrow2['quantity'];?></center></td>

			
					   
					   <?php 
					   $heji=$heji+($myrow2['price'] * $myrow2['quantity']);
					   } 
					   		 
             
           ?>
		     <tr>
              <td colspan="6" style="text-align: right;"> 未税合计 </td>
			   <td  style="text-align: center;"><?php echo '￥'.round($myrow4['all_line_amount'],2);?></td>
			   
            </tr>
			<tr>
              <td colspan="6" style="text-align: right;"> <?php echo $myrow4['tax_name'];?> </td>
			   <td  style="text-align: center;"><?php echo '￥'.round($myrow4['tax_amount'],2);?></td>
			  </tr>  
                  <tr> 
			    <td colspan="6" style="text-align: right;"> 含税总计 </td>
              <td  style="text-align: center;"><?php echo '￥'.round($myrow4['order_all_amount'],2);?></td>
            </tr>
					  </table>
			  
					   <table>
                 <tr>
				                
             
					 <h style="text-align:left;"  ><b>订货条款：</b></h><br></tr > </table>
    <span><b>一、货物保留时间：</b>合同签定后，乙方将自动保留货物15天。15天后如甲方不提货本合同自行终止，已支付的预付款不予退回。重新提货需再签定合同。</span><br>
        
         <span><b>二、付款方式及结算：</b><?php echo $myrow4['term_name'];?></span><br>
         <span><b>三、交货地点及运费：</b><?php echo $myrow4['ship_address'];?>&nbsp;&nbsp;&nbsp;&nbsp;运费:<?php echo $myrow4['yunfei_amount'];?></span><br>
         <span><b>四、产品质量：</b>乙方提供的所有软、硬件必须原装正版。乙方做为原厂商及供货商， 所提供的产品质量不合格或者有缺陷，导致出现质量事故，应赔偿甲方因此遭受的所有损失。</span><br>
         <span><b>五、安装保修期及售后服务：</b></span><br>
		   <span>乙方对合同中列示的硬件产品提供<span style="text-decoration: underline;"> </span>年保修服务。（人为因素除外）
保修期内，提供免费零配件更换。（说明：如因操作系统，数据库，服务器出现问题导致系统出现问题则不属本公司产品之品质问题。）保修期后，硬件产品的保修收取硬件成本费，对于系统的日常维护，乙方可通过电话、传真向甲方提供咨询和技术支持。
</span><br>
         <span><b>六、违约责任：</b></span><br>
		  <span> 1)	除人力不可抗拒因素外，甲乙双方中任何一方废除合同，概由违约方承担经济责任，并向另一方赔偿由此造成的经济损失；如乙方未按合同规定的交货日期交货或因货物验收不合格及乙方原因而影响了正常交货的，每迟一天需按合同金额的0.5%向买方交纳违约金，总额不超过合同总金额的10%。甲方可直接在付给乙方的货款中扣除违约金。</span><br>
		  <span> 2)	如甲方未按合同规定的付款条款付款，每迟一天需按合同金额的0.5%向乙方交纳违约金，总额不超过合同总金额10%。</span><br>
		    <span> 3)	任何一方违反本合同的任何条款。且在守约一方发出书面通知后5日内仍不进行补救的，则守约的一方有权追究违约的一方相应违约责任。</span><br>
			<span> <b>七、谅解条款：</b>甲、乙双方中任何一方由于不可抗力的原因，未能如期履行合同的，应及时通知对方，在取得有关部门的证明和对方谅解的情况下，经双方协商一致，可延期履行合同，或更改合同中的某些条款。否则将承担违约责任。</span><br>
			<span> <b>八、知识产权的保护：</b>甲方有义务保护乙方产品预装软件版权和其他知识产权，出现任何非法使用和销售具有捷科存储产品商标的其他存储产品，乙方将视情况向有关法院提出诉讼。乙方必须保证所提供的产品拥有完全的知识产权，由此引起的法律纠纷以及给甲方造成的损失全部由乙方承担。</span><br>
			<span><b>九、保密条款：</b>此订货合同及相关的文件所涉及的商业秘密，双方均负有为对方保密之义务，不得泄露、公开、使用或允许他人使用对方秘密。否则，对方有权要求赔偿。</span><br>
			<span> <b>十、其它：</b></span><br>
			<span> 1)	本合同未尽事宜，甲乙双方应友好协商解决。</span><br>
			<span> 2)	因本合同产生的纠纷，双方均可向有管辖权的人民法院提起诉讼。</span><br>
			<span>3)本合同一式两份(每份2页），甲乙双方各执一份，连同附件具有同等法律效力。（本合同传真件有效）。</span><br>
			<span>本合同执行过程中，所有补充协议经甲乙双方签字盖章后即成为本合同的有效组成部分，其生效日期以签字盖章之日为准。</span><br><br>
			<body>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
<tr>
  <td width="50%"><b>甲方:<?php echo $myrow4['customer_name'];?></b></td>
  <td width="50%"><b>乙方：<?php echo $myrow['coyname'];?> </b></td>

</tr>
<!-- <div><img id="Seal" style="position: absolute;left:490px;bottom:90px;background-color: transparent;z-index: 2;" src="" alt="" height="180" width="180">
  </div> -->
<tr>
  <td width="50%"><b>授权人签字:</b></td>
  <td width="50%"><b>授权人签字:</b></td>
</tr>
<tr>
  <td width="50%"><b>电话:<?php echo $myrow4['contacts_phone'];?></b></td>
  <td width="50%"><b>电话:<?php echo $myrow['telephone'];?> </b></td>
</tr>
<tr>
   <td width="50%"><b>日期<?php echo date('Y',$myrow4['qianding_date']);?>年<?php echo date('m',$myrow4['qianding_date']);?>月<?php echo date('d',$myrow4['qianding_date']);?>日</b></td>
    <td width="50%"><b>日期<?php echo date('Y',$myrow4['qianding_date']);?>年<?php echo date('m',$myrow4['qianding_date']);?>月<?php echo date('d',$myrow4['qianding_date']);?>日</b></td>
</tr>
</table>
			







         <!-- <?php echo date('Y') ?> -->
       

        <div class="prall_btm" style="text-align:center">
        
            <input type="button" onclick="print_list()" value="打印"/>
          
        </div>
    </div>
</div>

  <script>
        //暂时只区分公司名不区分章名
        window.onload=function()
        {
            //var account_name = document.getElementById('account_name').innerText;
            // var name="苏州菲优特空气净化设备有限公司";

            var seal = document.getElementById('Seal');
			seal.setAttribute("src", "statics/base/images/SuzhouConSeal.png")
           /* if (account_name==="苏州菲优特空气净化设备有限公司") {

                seal.setAttribute("src", "statics/base/images/SuzhouConSeal.png")
             }
              else if(account_name==="菲优特环保净化工程(武汉)有限公司"){
                 seal.setAttribute("src", "statics/base/images/WuhanConSeal.png")

         }
        else{
            alert("公司名错误");
            }
			*/

        }
    </script>

</body>
</html>