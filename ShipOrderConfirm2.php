<?php

include('includes/session.inc');
$Title = _('出货单仓库确认');
$ViewTopic= '出货单仓库确认';
$BookMark = '出货单仓库确认';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

if (isset($_GET['Updatedelivery_num']) ) {
$_POST['delivery_num']=$_GET['Updatedelivery_num'];
}
 

unset($result);
 
if (isset($_POST['Reject'])) 
{
  $errorflag = 0;
  $all_amount = 0;
  $all_invoice_dis_amount = 0;
  $k =0;
 
  if ($errorflag == 0) 
  {
    $Delivery_date = strtotime($_POST['Delivery_date']);
    DB_Txn_Begin($db);
    $time = time();
    $delivery_amount = 0;
    $k =0;
   

  $sql = "update  so_delivery_headers_all
                    set  status='拒绝' 
                  where  delivery_num  ='".$_POST['delivery_num']."'   ";
          $result = DB_query($sql,$db);
		  
    DB_Txn_Commit($db);
	 
    prnMsg('出货单'.$_POST['delivery_num'].'拒绝！',success);
    echo "<script>location.href='ShipOrderConfirm.php';</script>";
	 
        
    }    
}


 
 if (isset($_POST['Agree'])) 
{
  $errorflag = 0;
  $all_amount = 0;
  $all_invoice_dis_amount = 0;
  $k =0;
   for ($i=1; $i<=$_POST['flag'];$i++)
  {
    if($_POST['status'.$i]<>'')
	{        
         
		   if ($_POST['ship_quantity'.$i]=='') 
		  {
            $errorflag = 1;
            prnMsg($value.'出货数量未填写,请确认！',error);
          }	 
		  if ($_POST['ship_quantity'.$i]<=0 ) 
		  {
            $errorflag = 1;
            prnMsg($value.'出货数量必须大于0,请确认！',error);
          }	
		  if ($_POST['ship_quantity'.$i] > $_POST['onhand_quantity'.$i] ) 
		  {
            $errorflag = 1;
            prnMsg($value.'出货数量不可以大于库存量,请确认！',error);
          }	  


		  
      }
  }

    $time = time();
	$time2=$time - 5;
	 
	if ($_SESSION['lastsearchtime'] > $time2 )  {
	$errorflag = 1;
	prnMsg($value.'重复提交！',error);
	}

  if ($errorflag == 0) 
  {
    $Delivery_date = strtotime($_POST['Delivery_date']);
    DB_Txn_Begin($db);
 
    $delivery_amount = 0;
    $k =0;
   for ($i=1; $i<=$_POST['flag'];$i++)
	{
       $k = $k +1; 
	   if($_POST['status'.$i]<>'')
		   {         
          if ($_POST['ship_quantity'.$i]=='') 
		  {
            $errorflag = 1;
            prnMsg($value.'出货数量未填写,请确认！',error);
          }	 
		  if ($_POST['ship_quantity'.$i]<=0 ) 
		  {
            $errorflag = 1;
            prnMsg($value.'出货数量必须大于0,请确认！',error);
          }	  

		 
    
          $sql = "update  so_delivery_all
                    set  shiped_quantity   =  '".$_POST['ship_quantity'.$i]."'  
                  where  delivery_num  ='".$_POST['delivery_num']."' 
				   and   delivery_id ='".$_POST['delivery_id'.$i]."' ";
          $result = DB_query($sql,$db);

		  $sql2 = "update  so_lines_all
                    set  quantity_shiped   = quantity_shiped +  '".$_POST['ship_quantity'.$i]."'  
                  where  order_number  ='".$_POST['so_order_number'.$i]."' 
				   and   line ='".$_POST['so_line_no'.$i]."' ";
          $result2 = DB_query($sql2,$db);

		  $v_ship_qty=0-$_POST['ship_quantity'.$i];
          $sqlprice = "select  cost_price
          from inv_onhand_quantity_all where lot_num='" .$_POST['lot_num'.$i]. "'  and stockid='" . $_POST['item_no'. $i] . "' and subinventory_code ='" . $_POST['subinventory_code' . $i]  . "' ";
          $result_price = DB_query($sqlprice, $db);
           $v1 = DB_fetch_array($result_price);

			    //扣减库存 begin
				$temp=$_POST['ship_quantity'.$i];
 
                if($_POST['shengchan_date'.$i] == '' and $_POST['lot_num'.$i] != '') {
                    $sqlsubcode = "select id,stockid,quantity,lot_num,shengchan_date from inv_onhand_quantity_all where stockid='" .$_POST['item_no'.$i]. "' and subinventory_code ='" . $_POST['subinventory_code'.$i]  . "' and lot_num='" .$_POST['lot_num'.$i]. "'  order by id";
                // echo $sqlsubcode;
                   $result_subcode = DB_query($sqlsubcode, $db );
                  
              }else if ($_POST['lot_num'.$i] == '' and $_POST['shengchan_date'.$i] != ''){
                   $sqlsubcode = "select id,stockid,quantity,lot_num,shengchan_date from inv_onhand_quantity_all where stockid='" .$_POST['item_no'.$i]. "' and subinventory_code ='" . $_POST['subinventory_code'.$i]  . "' and shengchan_date='" .strtotime($_POST['shengchan_date'.$i]). "'  order by id";
                // echo $sqlsubcode;
                   $result_subcode = DB_query($sqlsubcode , $db);
              }else if($_POST['shengchan_date'.$i] == '' and $_POST['lot_num'.$i] == ''){
                   $sqlsubcode = "select id,stockid,quantity,lot_num,shengchan_date from inv_onhand_quantity_all where stockid='" .$_POST['item_no'.$i]. "' and subinventory_code ='" . $_POST['subinventory_code'.$i]  . "'   order by id";
                // echo $sqlsubcode;
                   $result_subcode = DB_query($sqlsubcode, $db );
              }else{
                   $sqlsubcode = "select id,stockid,quantity,lot_num,shengchan_date from inv_onhand_quantity_all where stockid='" .$_POST['item_no'.$i]. "' and subinventory_code ='" . $_POST['subinventory_code'.$i]  . "' and shengchan_date='" .strtotime($_POST['shengchan_date'.$i]). "' and lot_num='" .$_POST['lot_num'.$i]. "'  order by id";
                // echo $sqlsubcode;
                   $result_subcode = DB_query($sqlsubcode, $db );
              }
              
             
               
              while ($v = DB_fetch_array($result_subcode)) {
                
               
                
                 if($temp>0){

					  

                   if ($v['quantity'] <=$temp) {
                      $UpdateSubCode="delete from  inv_onhand_quantity_all where id=".$v['id']."";
                      $result_updatesubcode = DB_query($UpdateSubCode, $db); 
                      unset($UpdateSubCode);
                      
                      $sql7 = "select sum(quantity) quantity, sum(cost_price*quantity) cost_amount 
                      from inv_onhand_quantity_all where stockid='" . $_POST['item_no' . $i] . "'
                      and subinventory_code='" . $_POST['subinventory_code' . $i] . "' ";
                      $result7 = DB_query($sql7, $db);
                      $v7 = DB_fetch_array($result7);
                      
                      $sqlinvtrancsation="insert into inv_transactions_all(subinventory_from,transaction_type,price,transaction_date,quantity,after_onhand,after_amount,remark,item_no,uom,project_name,delivery_num,deliveryline,so_order_number,so_line_number,lot_num,shengchan_date,creation_date,created_by,last_update_date,last_updated_by) ";
                      $sqlinvtrancsation.="values('".$_POST['subinventory_code'.$i]."','SALESHIP','" . $v1['cost_price'] . "','".$time."', '-".$temp."','" . $v7['quantity'] . "','" . $v7['cost_amount'] . "','".$_POST['remark'.$i]."','".$_POST['item_no'.$i]."','".$_POST['uom'.$i]."','".$_POST['project_name'.$i]."','".$_POST['delivery_num']."','".$_POST['delivery_line'.$i]."','".$_POST['so_order_number'.$i]."','".$_POST['so_line_no'.$i]."','" . $v['lot_num'] . "','" . $v['shengchan_date'] . "','".$time."','".$_SESSION['UserID']."','".$time."','".$_SESSION['UserID']."')";
                      $result_invtrancsation = DB_query($sqlinvtrancsation, $db); 
                      
                      $temp=$temp-$v['quantity'];
                      
                    } else {
                      
                      $UpdateSubCode1="Update inv_onhand_quantity_all set quantity=quantity-".$temp.",
                      last_update_date='".$time . "',
                      last_updated_by='".$_SESSION['UserID'] . "' where id=".$v['id']."";
                      $result_updatesubcode1 = DB_query($UpdateSubCode1, $db); 
                      $sql7 = "select sum(quantity) quantity, sum(cost_price*quantity) cost_amount 
                      from inv_onhand_quantity_all where stockid='" . $_POST['item_no' . $i] . "'
                      and subinventory_code='" . $_POST['subinventory_code' . $i] . "' ";
                      $result7 = DB_query($sql7, $db);
                      $v7 = DB_fetch_array($result7);
                      
                      $sqlinvtrancsation="insert into inv_transactions_all(subinventory_from,transaction_type,price,transaction_date,quantity,after_onhand,after_amount,remark,item_no,uom,project_name,delivery_num,deliveryline,so_order_number,so_line_number,lot_num,shengchan_date,creation_date,created_by,last_update_date,last_updated_by) ";
                      $sqlinvtrancsation.="values('".$_POST['subinventory_code'.$i]."','SALESHIP','" . $v1['cost_price'] . "','".$time."', '-".$temp."','" . $v7['quantity'] . "','" . $v7['cost_amount'] . "','".$_POST['remark'.$i]."','".$_POST['item_no'.$i]."','".$_POST['uom'.$i]."','".$_POST['project_name'.$i]."','".$_POST['delivery_num']."','".$_POST['delivery_line'.$i]."','".$_POST['so_order_number'.$i]."','".$_POST['so_line_no'.$i]."','" . $v['lot_num'] . "','" . $v['shengchan_date'] . "','".$time."','".$_SESSION['UserID']."','".$time."','".$_SESSION['UserID']."')";
                      
                      $result_invtrancsation = DB_query($sqlinvtrancsation, $db); 
                      
                      $temp=0;

                    }




                }
            }
 
                    
           //扣减库存 end

		   
 
        }
      } 	    
 
	   

          
    DB_Txn_Commit($db);
	if ($k>0) {
		$_SESSION['lastsearchtime']=$time;
		$sql22 = "update  so_delivery_headers_all
                    set status='完成' , 
                    sub_approve_date='".$time."' ,
                    sub_approved_by  ='".$_SESSION['UserID']."'
                  where  delivery_num  ='".$_POST['delivery_num']."'   ";
          $result22 = DB_query($sql22,$db);

    prnMsg('出货单'.$_POST['delivery_num'].'同意！',success);
    echo "<script>location.href='ShipOrderConfirm.php';</script>";
	}
        
    }    
}


?>
 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>出货单仓库确认</title>
<link rel="shortcut icon" href="/JXC/favicon.ico"/>
<link rel="icon" href="/JXC/favicon.ico"/>
<meta http-equiv="Content-Type" content="application/html; charset=utf-8"/>
<link href="/css/xenos/default.css" rel="stylesheet" type="text/css"/>
<script type="text/javascript" src ="/JXC/javascripts/miscfunctions.js"></script>
<script type="text/javascript" src ="/JXC/javascripts/wdatepicker.js"></script>
<script type="text/javascript">var basepath='/JXC/statics/base/images';</script>
<script type="text/javascript" src="/JXC/statics/base/js/metvar.js"></script>
<script type="text/javascript" src="/JXC/statics/base/js/jQuery1.7.2.js"></script>
<script type="text/javascript" src="/JXC/statics/base/js/uploadify/jquery.uploadify.v2.1.4.min.js"></script>
<script type="text/javascript" src="/JXC/statics/base/js/iframes.js"></script>
<script type="text/javascript" src="/JXC/statics/base/js/cookie.js"></script>
<script type="text/javascript" src="/JXC/statics/base/js/jquery.livequery.js"></script>



<script src="/JXC/javascript/jquery-1.7.2.min.js"></script>
<script src="/JXC/javascript/lhgdialog.min.js?self=true&skin=chrome"></script>
<script src="/javascript/bootstrap.min.js"></script>

<script type="text/javascript">
/*ajax执行*/
var lang = 'cn';
var metimgurl='/JXC/statics/base/images/';
var depth='';
$(document).ready(function(){
	ifreme_methei();
});
</script>
<script type="text/javascript">
function metreturn(url){
	if(url){
		location.href=url;
	}else if($.browser.msie){
		history.go(-1);
	}else{
		history.go(-1);
	}
} 

function addsave() 
{

  var v = $('#idcount').val();
  $("#purchase_table_"+v).css("display","");
  var c = parseFloat(v) + 1;
  $('#idcount').val(c);     
}

 </script>
</head>
<body>
 
<div id="CanvasDiv">
<div id="BodyDiv">
<div id="BodyWrapDiv">
<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="出货单仓库确认" alt="出货单仓库确认">出货单仓库确认</p>
<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST"><input type="hidden" name="time"
value="<?=$time?>">
<div>
<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
<table class="selection">

<?php
	 

$sql ="SELECT  b.customer_code,
    b.customer_name, 
    p.delivery_num,
    p.delivery_date,    
    p.creation_date,
    p.created_by,p.narrative,
    p.last_update_date,
    p.last_updated_by  
FROM so_delivery_headers_all p, customers b
WHERE p.customer_code = b.customer_code
and p.status='核准'
and p.delivery_num = '" .$_POST['delivery_num'] . "' ";
	// echo $sql;
	$result = DB_query($sql,$db);
    if (DB_num_rows($result)==0) {
        unset($result);
        prnMsg(_('该单据已验收，请重新查询！') ,'error');
	 }
 while ($myrow = DB_fetch_array($result))  {
       $_POST['customer_code']=$myrow['customer_code'] ;
       $_POST['customer_name']=$myrow['customer_name'] ;  
	   $_POST['created_by']=$myrow['created_by'] ; 
	   $_POST['delivery_date']=$myrow['delivery_date'] ; 
	   $_POST['delivery_num']=$myrow['delivery_num'] ;  
	   $_POST['creation_date']=$myrow['creation_date'] ; 
 }     
?>

<tr>
 <td>出货单号码:</td>
  <td  ><input type="text" required="required" maxlength="100" size="13" name="delivery_num"  value="<?=$_POST['delivery_num']?>" size="15" maxlength="20"/> </td>
  <td>客户简称：</td>
  <td><input type="text" required="required" name="customer_code" id="text_slect_vendor" value="<?=$_POST['customer_code']?>" size="15" maxlength="25"/> </td>
  <td>建单者：</td>
  <td  ><input readonly="readonly" type="text"   name="created_by" id="text_slect_currencycode" value="<?=$_POST['created_by']?>" size="5" maxlength="10"/></td>
   <td>出货日期：</td>
  <td><input type="text" name="delivery_date" maxlength="20" size="10" required="required" value="<?=date('Y-m-d',$_POST['delivery_date'])?>" onfocus="WdatePicker() "></td>
</tr>

<tr>
  <td>客户名称：</td>
  <td colspan="5"><input readonly="readonly" type="text" name="customer_name" id="text_slect_name" value="<?=$_POST['customer_name']?>" size="70" maxlength="50"/></td>
  <td>建单日期：</td>
  <td  ><input readonly="readonly" type="text"   name="creation_date" value="<?=date('Y-m-d h:i:s',$_POST['creation_date'])?>" size="18" maxlength="20"/></td>
 
</tr>
 
 
</table>

<input type="hidden" name="PageOffset" value="1"/><br/>
<?php
if (isset($_POST['customer_name']) and $_POST['customer_name'] != '') {

	$sql ="select c.*,d.item_no,d.item_name,d.item_desc,a.quantity,a.quantity_shiped,(select ifnull(sum(quantity),0)
	from inv_onhand_quantity_all b where b.subinventory_code=c.subinventory_code and  b.stockid=d.item_no and b.lot_num = c.lot_num and b.shengchan_date = c.shengchan_date ) onhand_quantity 
				from so_delivery_all c,sf_item_no d,so_lines_all a
				where  c.stockid=d.item_no and c.so_order_number=a.order_number and c.so_line_no=a.line
				and c.delivery_num= '" .$_POST['delivery_num'] . "'
				and a.quantity > a.quantity_shiped 
				 ";
	// echo $sql;
	$result = DB_query($sql,$db);
    if (DB_num_rows($result)==0) {
		$line_count=DB_num_rows($result);
        unset($result);
        prnMsg(_('该出货单无待出货明细！') ,'error');
    }

?>
<input id="purchase_table_lastRow" name="purchase_table_lastRow" type=hidden value="">

<div class="centre"> 
      <p id="Prompt" style="color: red;font-size: 20px"></p>
                    </div>
<div class="text-nav-table">
<table id="purchase_table" cellpadding="2" class="selection">
 
<tr id="list-top">  
<th style="display:none;" align="center">选择</th>
          <th >行</th>
                <th bgcolor="#87CEFA">订单号码</th>
					<th  >订单行</th>  
					<th  >料号</th>  
					<th  >料号名称</th>
					<th  >规格型号</th>
					<th >单位</th>	  
					<th >订单量</th>	  
					<th >已出货量</th>	   
					<th >仓库</th>	  
					<th >库存量</th>	 
					<th bgcolor="#87CEFA">出货数量</th>
					<th bgcolor="#87CEFA">确认数量</th>
          <th bgcolor="#87CEFA">批号</th>	 
					<th bgcolor="#87CEFA">生产日期</th>	 
					<th bgcolor="#87CEFA">失效日期</th>	 
					<th bgcolor="#87CEFA">注册证号或备案凭证编号</th>	 
					<th bgcolor="#87CEFA">储运条件</th>	 
					<th bgcolor="#87CEFA">备注</th>
                     


 
</tr>
<?php   
$i=1;
 
 while ($myrow = DB_fetch_array($result)  )  {
   $wait_quantity=$myrow['quantity']-$myrow['quantity_shiped'] ;
   if($myrow['shengchan_date'] == 0){
    $shengchan_date = '';
    $expiring_date = '';

  }else{
    $shengchan_date = date('Y-m-d',$myrow['shengchan_date']);
    $expiring_date = date('Y-m-d',$myrow['expiring_date']);

  }
	 ?>
		  
<tr id="purchase_table_<?=$i?>" >
<td style="display:none;"><input type="checkbox" name="status<?=$i?>" checked /></td>
  <td> <input type="text" readonly="readonly" name="delivery_line<?=$i?>" id="text_slect_receipt_line<?=$i?>" value="<?= $myrow['delivery_line'] ?>" size="1" maxlength="10"/></td>
  <td><input readonly="readonly" type="text" name="so_order_number<?=$i?>"   value="<?= $myrow['so_order_number'] ?>" size="13" maxlength="150"/></td>
  <td><input readonly="readonly" type="text" name="so_line_no<?=$i?>"   value="<?= $myrow['so_line_no'] ?>" size="3" maxlength="150"/></td> 
     
    <td><input readonly="readonly" type="text" name="item_no<?=$i?>" value="<?=$myrow['item_no']?>" size="15" maxlength="150"/>
	<td><input readonly="readonly" type="text" name="item_name<?=$i?>" value="<?=$myrow['item_name']?>" size="15" maxlength="150"/> 
	<td><input readonly="readonly" type="text" name="item_desc<?=$i?>" value="<?=$myrow['item_desc']?>" size="15" maxlength="150"/> 
	<td><input readonly="readonly" type="text" name="uom<?=$i?>"   value="<?=$myrow['uom']?>" size="3" maxlength="50"  /> 
	<td><input readonly="readonly" type="text" name="quantity<?=$i?>"  value="<?=$myrow['quantity']?>" size="5" maxlength="50"  /> 
	<td><input readonly="readonly" type="text" name="quantity_shiped<?=$i?>" id="quantity_shiped<?=$i?>" value="<?=$myrow['quantity_shiped']?>" size="4" maxlength="50"  />
	<td><input readonly="readonly" type="text" name="subinventory_code<?=$i?>" id="subinventory_code<?=$i?>" value="<?=$myrow['subinventory_code']?>" size="4" maxlength="50"  />
	<td><input readonly="readonly" type="text" name="onhand_quantity<?=$i?>" id="onhand_quantity<?=$i?>" value="<?=$myrow['onhand_quantity']?>" size="4" maxlength="50"  />
   <td><input readonly="readonly" type="text" name="delivery_quantity<?=$i?>" id="delivery_quantity<?=$i?>" value="<?=$myrow['delivery_quantity']?>" size="5" maxlength="50"  /> 
   <td><input type="text" name="ship_quantity<?=$i?>" onblur="check(<?=$i?>)" id="ship_quantity<?=$i?>" value="<?=$myrow['delivery_quantity']?>" size="5" maxlength="50"  /> 
   <td><input type="text" readonly="readonly" name="lot_num<?=$i?>" id="lot_num<?=$i?>" value="<?=$myrow['lot_num']?>" size="10" maxlength="50"  /> 
   <td><input type="text" readonly="readonly" name="shengchan_date<?=$i?>" id="shengchan_date<?=$i?>" value="<?=$shengchan_date?>" size="7" maxlength="50"  /> 
 
   <td><input type="text" readonly="readonly" name="expiring_date<?=$i?>" id="expiring_date<?=$i?>" value="<?=$expiring_date?>" size="7" maxlength="50"  /> 
   <td><input type="text" readonly="readonly" name="zhucezhenghao<?=$i?>" id="zhucezhenghao<?=$i?>" value="<?=$myrow['zhucezhenghao']?>" size="10" maxlength="50"  /> 
   <td><input type="text" readonly="readonly" name="transportation_conditions<?=$i?>" id="transportation_conditions<?=$i?>" value="<?=$myrow['transportation_conditions']?>" size="10" maxlength="50"  /> 
   <td><input type="text" readonly="readonly" name="remark<?=$i?>" id="remark<?=$i?>" value="<?=$myrow['remark']?>" size="10" maxlength="50"  /> 
 
 <td> 
<input type="hidden"  name="delivery_id<?=$i?>"  value="<?= $myrow['delivery_id'] ?>" size="8" maxlength="10"/>
  <input type="hidden"  name="delivery_num<?=$i?>"  value="<?= $myrow['delivery_num'] ?>" size="8" maxlength="10"/>
 <input type="hidden"  name="wait_quantity<?=$i?>" id="wait_quantity<?=$i?>" value="<?= $wait_quantity ?>" size="8" maxlength="10"/>
 <input type="hidden"  name="uom<?=$i?>" id="uom<?=$i?>" value="<?= $myrow['uom'] ?>" size="8" maxlength="10"/>
 <input type="hidden"  name="project_name<?=$i?>" id="project_name<?=$i?>" value="<?= $myrow['project_name'] ?>" size="8" maxlength="10"/>
  </td>
</tr>
<?php 
 $i=$i+1;
  
 }
  ?>

  	<!-- <tr><td colspan="15"><p><input type="checkbox" name="selectall" onclick="checkall(this.form);"/>全选/取消全选</p></td></tr> -->
	<td ><input type="hidden" name="flag" value="<?=$i-1?>" size="15" maxlength="45"/></td> 
</table>
</div>
<div class="centre">
<input type="submit" name="Agree" value="核准"> &nbsp;&nbsp; 
<input type="submit" name="Reject" value="拒绝"> &nbsp;&nbsp;
 
</div>
 <br/>
<?php

}
?>
<input type="hidden" name="idcount" id='idcount' value="1"/>
<input type="hidden" name="JustSelectedAvendor" value="Yes"/>
</div>
</form>
</div>
</div>

<div id="FooterDiv">
<div id="FooterWrapDiv">

</div>
</div>
</div>


<script type="text/javascript">
          function check1all(s1) {
        var all_qy = 0;
        //料号
		var text_slect_ItemNo = document.getElementById("text_slect_ItemNo" + s1).value;
		//该料号对应库存
		var onhand_qy = document.getElementById("onhand_quantity" + s1).value;

		for (var j = 1; j < 50; j++) {
			var text_slect_ItemNo1 = document.getElementById("text_slect_ItemNo" + j).value;
			if (text_slect_ItemNo == text_slect_ItemNo1) {
				var quantity1 = document.getElementById("quantity" + j).value;
				all_qy = Number(all_qy) + Number(quantity1);
			}
		}
		if (parseFloat(all_qy) > parseFloat(onhand_qy)) {
			alert("合计出货量" + all_qy + "不可以超过库存数量" + onhand_qy);
			//document.getElementById("Prompt").innerHTML="合计樘数"+all_tangshu+"不可以超过待转数量"+wait_quantity;
			document.getElementById("quantity" + s1).value = 0;
			document.getElementById("quantity" + s1).focus();
		}
    }
	  
function  check(s1){
	    var a=document.getElementById("wait_quantity"+s1).value;
        var b=document.getElementById("ship_quantity"+s1).value;
		var c=document.getElementById("onhand_quantity"+s1).value; 
		if (parseFloat(b) >parseFloat(c) )
		{  document.getElementById("Prompt").innerHTML="本次出货量不可以大于库存量！！！！";
		   document.getElementById("ship_quantity"+s1).value="";
            document.getElementById("ship_quantity"+s1).focus();
		}  else if (parseFloat(b) > parseFloat(a) )
		{  document.getElementById("Prompt").innerHTML="本次出货量不可以大于订单待出货量！！！！";
		   document.getElementById("ship_quantity"+s1).value="";
            document.getElementById("ship_quantity"+s1).focus();
		}  else if  (parseFloat(b) < 0 ){ 
			 
			document.getElementById("Prompt").innerHTML="出货数量不可以小于0！！！！";
            document.getElementById("ship_quantity"+s1).value="";
            document.getElementById("ship_quantity"+s1).focus();
		}
		else { 
            document.getElementById("Prompt").innerHTML="";
        }
        
    
     }

 
    $(document).ready(function(){

        $('.divToilet table tr td a').click(function(){
            $(this).parent('td').toggleClass('highlight');
            if(!($(this).parent('td').hasClass('highlight'))) {
                $(this).next().val('0');
            }else {
                $(this).next().val('1');
            }
        });
        <?php for($i=1;$i<=50;$i++){?> 
        $('#btn_slect_po<?=$i?>').dialog({
            title:'选择未开票的采购入库单',
            width: '950px',
            height: 520,
            content:'url:SearchNoInvoicePo2.php?fwValue=<?=$i?>&cat=<?=$_POST['customer_code']?>',
            init:function(){
			    this.content.document.getElementById('cat').value = 'buliao';
                this.content.document.getElementById('fwValue').value = '<?=$i?>';
            }
        });
		<?php }?>


		 <?php for($i=1;$i<=50;$i++){?> 
        $('#btn_slect_subcode<?=$i?>').dialog({
            title:'选择仓库',
            width: '600px',
            height: 370,
            content:'url:Searchsubcode.php?fwValue=<?=$i?>&cat=buliao',
            init:function(){
			    this.content.document.getElementById('cat').value = $_POST['customer_code'];
                this.content.document.getElementById('fwValue').value = '<?=$i?>';
            }
        });
		<?php }?>


		$('#btn_slect_vendor').dialog({
            title:'选择客户',
            width: '950px',
            height: 470,
            content:'url:BtnSearchAPVendor3.php?fwValue=&cat=buliao',
            init:function(){
			    this.content.document.getElementById('cat').value ='buliao';
                this.content.document.getElementById('fwValue').value = '';
            }
        });



	
        //Function to get URL arguments

        function getRequest() {
            var url = location.search; //获取url中"?"符后的字串
            var theRequest = new Object();
            if (url.indexOf("?") != -1) {
                var str = url.substr(1);
                strs = str.split("&");
                for(var i = 0; i < strs.length; i ++) {
                    theRequest[strs[i].split("=")[0]]=(strs[i].split("=")[1]);
                }
            }
            return theRequest;
        }
          
 
    });
	function checkall(thisform){
		for(var i=0;i<thisform.elements.length;i++){
		if(thisform.elements[i].type=="checkbox"&&thisform.elements[i].checked==false&&thisform.elements[i].name!="selectall")
		{thisform.elements[i].checked=true;}
		else if(thisform.elements[i].type=="checkbox"&&thisform.elements[i].checked==true&&thisform.elements[i].name!="selectall")
		{thisform.elements[i].checked=false;}} }
</script>
</body>

</html>
<?

include('includes/footer.inc');
?>

