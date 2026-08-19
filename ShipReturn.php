<?php

include('includes/session.inc');
$Title = _('退货单建立');

$ViewTopic= '退货单建立';
$BookMark = '退货单建立';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

unset($result);

	 
	if (isset($_POST['Save'])) {
		$errorflag = 1;
		foreach ($_POST as $key => $value) {
			if ($value != '') {
				if (substr($key, 0,7)=='stockid') {
					$errorflag = 0;
					$i = substr($key, 7);
					if ($value != '') {
						if ($_POST['UOM'.$i]=='') {
						$errorflag = 1;
						prnMsg($value.'未填写单位，请填写单位！',error);
						}
					
						if ($_POST['this_qty'.$i]=='') {
							$errorflag = 1;
							prnMsg($value.'未填写退货数量，请填写退货数量！',error);
						}
					
					}
				}
			}
		}
	
		if ($errorflag == 0) {
			
			$sumamount=0.00;
         $date = date('Ymd');
       

			$ScheduleDate = strtotime($_POST['ScheduleDate']);
			DB_Txn_Begin($db);
			$time = time();
			$order_amount = 0;
            $order_amount1 = 0;
            $check=1;
            for ($i=1;$i<=$_POST['flag'];$i++)
	    {
	       //echo $_POST['s_num'];
          // echo $_POST['status'.$i];
           if ($_POST['status'.$i]<>''){
                  $check=0;
						$lineamount[$i] =$_POST['sale_qty'.$i] * $_POST['unitprice'.$i] ;
                        $lineamount1[$i] =$_POST['sale_qty'.$i] * $_POST['sale_unitprice'.$i] ;
					$sql="update so_lines_all set quantity_return=ifnull(quantity_return,0)+'".$_POST['this_qty'.$i]."' 
                    where order_number='" .$_POST[s_num]."' 
                    and stockid='".$_POST['stockid'.$i]."'
                     ";
							$result = DB_query($sql,$db);
                            					 
					/*	$sql = "insert into sale_line_all(transaction_type,order_num,line,stock_id,remark,subinventory_code,uom,sale_qty,sale_unitprice,total_price,
						creation_date,created_by,last_update_date,last_updated_by)
						values('XIAOSHOUTUIKU','" .$_POST[order_num]."','".$i."','".$_POST['stockid'.$i]."','".$_POST['remark'.$i]."','".$_POST['subinventory']."','".$_POST['UOM'.$i]."','".$_POST['sale_qty'.$i]."',
						'".$_POST['sale_unitprice'.$i]."','".$lineamount1[$i]."',
					 '".$time."','".$_SESSION['UserID']."','".$time."','".$_SESSION['UserID']."') ";
						
						$result = DB_query($sql,$db);
		*/
						$sqlinvtrancsation = "insert into inv_transactions_all(transaction_type,transaction_date,quantity,cost_price,item,uom,remark,subinventory_from,creation_date,created_by,last_update_date,last_updated_by,trans_num) ";
            $sqlinvtrancsation.="values('XIAOSHOUTUIKU','" . $ScheduleDate . "','".$_POST['this_qty'.$i]."','". $_POST['price'.$i] . "','" . $_POST['stockid'.$i] . "','"  . $_POST['UOM'.$i] . "','" . $_POST['remark'.$i] . "','" . $_POST['subinventory'] . "','" . $time . "','" . $_SESSION['UserID'] . "','" . $time . "','" . $_SESSION['UserID'] . "','" . $_POST[order_num] . "')";
            $result_invtrancsation = DB_query($sqlinvtrancsation, $db);
                        
                     
                         $sql = "insert into inv_onhand_quantity_all(stockid,quantity,subinventory_code,cost_price,
						creation_date,created_by,last_update_date,last_updated_by)
						values('".$_POST['stockid'.$i]."','".$_POST['this_qty'.$i]."','".$_POST['subinventory']."','".$_POST['price'.$i]."','".$time."','".$_SESSION['UserID']."','".$time."','".$_SESSION['UserID']."') ";
						
						$result = DB_query($sql,$db);
              }          
			}
            if($check==1){
                $msg = '请选中更改项';
            prnMsg($msg, 'error');
            }else{
                 
		/*	$sql = "insert into sale_header_all (transaction_type,order_num,subinventory_code, customer_code,sale_type,pay_type,operator_person,need_payment,remark,creation_date,created_by,last_update_date,last_updated_by) values('XIAOSHOUTUIKU','" .$_POST[order_num]."','".$_POST[subinventory]."','".$_POST['customer_code']."',
														 'RT','".$_POST['pay_type']."','".$_POST['requireemployee']."',
														 '".$order_amount1."','".$_POST['Header_Remark']."',
														 '".$ScheduleDate."','".$_SESSION['UserID']."','".$time."','".$_SESSION['UserID']."')";
			$result = DB_query($sql,$db);
		*/
			DB_Txn_Commit($db);
			prnMsg('退货单'.$_POST[order_num].'建立成功！',success);
		$OrderNum=$_POST[order_num];
         echo '<br />';
            //echo '<a href="' . $RootPath . '/PrintPartReturnDetail.php?Updatepo_num=' . $OrderNum . '"  target="_blank" >打印</a>';
           //  echo '<br />';
            // echo '<br />';
              
            echo '<a href="' . $RootPath . '/ShipReturn.php" >返回上一层</a>';
           
           // echo '<meta http-equiv=refresh content="1;url=PartReturn.php?">';
            //unset($_POST['CreditLimit']);
            echo '<br />';}
		}
	}

 ?>
 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>退货单</title>
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
    <!-- Include all compiled plugins (below), or include individual files as needed -->
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
	var c = parseInt(v) + 1;
	$('#idcount').val(c);     
}

 </script>
</head>
<body>
 <?php 
 if(isset($OrderNum)){
    }else{
 ?>
<div id="CanvasDiv">
	<div id="BodyDiv">
		<div id="BodyWrapDiv">
			<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="新建采购单" alt="新建订
单">退货单</p>
			<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST"><input type="hidden" name="time" 

value="<?=$time?>">
				<div>
				<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
				<table >
	    <tr>
			<td>订单单号：</td>  
			<td><input type="text" required="required" name="order_num1" id="text_slect_order_num" value="<?=$_POST['order_num1']?>" size="14" maxlength="12"/>  
               <a class="btn btn-info btn-xs" id="btn_slect_customer" hfre="###" title="选择客户">选择</a></td>
            
       
			<td>客户编号：</td>  
			<td><input type="text" readonly="readonly" name="customer_code" id="text_slect_customer" value="<?=$_POST['customer_code']?>" size="14" maxlength="12"/>
                   </td>
            
		 <td>客户名称：</td>
		 <td ><input readonly="readonly" type="text" name="customer_name" id="text_slect_name" value="<?=$_POST['customer_name']?>" size="25" maxlength="50"/></td>
          			 
			
              </tr>
         <tr>
			<td>联系人：</td>			 
			<td ><input readonly="readonly" type="text"   name="customer_contacts" id="text_slect_contacts" value="<?=$_POST['customer_contacts']?>" size="8" maxlength="50"/></td>
		
			<td>联系电话：</td> 
			 <td  ><input   type="text"  readonly="readonly"  name="phone_no" id="text_slect_phone_no" value="<?=$_POST['phone_no']?>" size="20" maxlength="20"/></td>
		
         
			<td>联系地址：</td>			 
			<td  colspan="3"><input readonly="readonly" type="text"   name="customer_address" id="text_slect_address" value="<?=$_POST['customer_address']?>" size="25" maxlength="50"/></td>
		

         	</tr>
       <tr>
       <?php    
      
       $_POST['ScheduleDate']=date('Y-m-d');
       	$date = date('Ymd');
        $sql_num = "select 	(
		CASE WHEN substr(max(trans_num) ,-2,1) = 0 THEN
			RIGHT (
				'100' + (
					max(substr(trans_num ,- 1)) + 1
				),
				2
			)
		ELSE
			substr(max(trans_num),-2,2) + 1
		END
        ) trans_num from inv_transactions_all where substr(trans_num,-12,10) = '" .'RT'. $date . "'";
        $result_num = DB_query($sql_num, $db);
        $rownum = DB_num_rows($result_num);
        while ($v = DB_fetch_array($result_num)) {
            if ($v['trans_num'] == null) {
                $order_num = 'RT'.$date . '01';
            } else {
                $order_num =  'RT'. $date .$v['trans_num'];
            }
        }
        
       ?>
       <tr>
        <td>日期：</td>
			<td><input type="text" name="ScheduleDate" onfocus="WdatePicker() "  readonly="readonly" maxlength="20" size="12" required="required" value="<?=$_POST['ScheduleDate']?>" "></td>
	
			<td>单号：</td> 
			 <td  ><input   type="text" required="required" name="order_num"  value="<?= $order_num ?>" size="20" maxlength="20"/></td>
	     </tr>

		
		
		<tr>
			<td>备注：</td> 
			 <td colspan="3"><input type="text"  maxlength="200" size="70" name="Header_Remark"  value="<?=$_POST['Header_Remark']?>" size="20" maxlength="20"/> </td>
		</tr>

	</table>
	<div class="centre">
		<input type="submit" name="Hearder" value="保存退货单头信息">
	</div>
	<input type="hidden" name="PageOffset" value="1"/><br/>
	<?php
		if (isset($_POST['customer_code']) and $_POST['customer_code'] != '') {
	?>
            
					<table cellpadding="2" class="selection">
					<tr id="list-top">
                    <th  width = 50>选择</th> 
					<th width="180">料号</th>
					<th width="150">料号描述</th>
					<th width="30" >单位</th>
					<th width="30" >单价</th>
					<th width="10">可退量</th>	
					<th width="10">退货量</th>
                  
					<th width="30">备注</th>
				
					</tr>
					<?php
                    $sql="select * FROM so_lines_all sl,sf_item_no sf  where sl.stockid=sf.item_no
                     and (sl.quantity-ifnull(sl.quantity_return,0))>0 and sl.order_number ='" .$_POST['order_num1'] . "' ";
                    $result = DB_query($sql, $db);
                    $i=1;
                    $count= DB_num_rows($result);
                    if ($count==0){
                        
                        prnMsg('该订单没有可退料！',error);
                    }
                  while ($myrow = DB_fetch_array($result)) {
                    $qt=$myrow['sale_qty']-$myrow['return_qty'];
                    
                     ?>
			
					<tr ><input type="hidden" name="s_num" value="<?=$_POST['order_num1']?>" size="15" maxlength="45"/>
                    <td><input type="checkbox" name="status<?=$i?>" /></td>
					<td><input type="text"  readonly="readonly" name="stockid<?=$i?>" id="text_slect_buliao<?=$i?>" value="<?=$myrow['stockid']?>" size="20" maxlength="25"/>
                      
                       </td>
					   <td ><input readonly="readonly" type="text" name="ItemDesc<?=$i?>"  value="<?=$myrow['item_desc']?>" size="30" maxlength="60"/></td>
					   <td><input readonly="readonly" type="text" name="UOM<?=$i?>"  value="<?=$myrow['uom']?>" size="4" maxlength="4"/></td>
                      
                       <td><input type="text" class="number" readonly="readonly" name="price<?=$i?>" value="<?=$myrow['price']?>" size="6" maxlength="10"/></td>
                       
                       
                    
						<td class="list-text"><input class="number" type="text" name="qty<?=$i?>" value="<?= ($myrow['quantity']-$myrow['quantity_return']) ?>" size="5" maxlength="45"/></td>
                        <td class="list-text"><input type="text" class="number" name="this_qty<?=$i?>" value="<?=0?>" size="5" maxlength="45"/></td>
                 

						<td class="list-text"><input type="text" name="remark<?=$i?>" value="<?=$myrow['remark']?>" size="15" maxlength="45"/></td> 
                    

					  <td><input  type="hidden" name="Subinventory_name<?=$i?>" id="text_slect_locationname<?=$i?>" value="<?=$_POST['Subinventory_name'.$i]?>" size="8" maxlength="25"/> 

					     
					</tr>
					<?php
                    $i=$i+1;
                     }
                     ?>
			      	<tr><td colspan="11"><p><input type="checkbox" name="selectall" onclick="checkall(this.form);"/>全选/取消全选</p></td></tr>
						<td ><input type="hidden" name="flag" value="<?=$i-1?>" size="15" maxlength="45"/></td> 
					</table>
					
	           

					<div class="centre">
	                <input type="submit" name="Save" value="保存">
					</div>
	<?php
		}
	?>
					<input type="hidden" name="idcount" id='idcount' value="1"/>
					<input type="hidden" name="JustSelectedACustomer" value="Yes"/>
				</div>
			</form>
		</div>
	</div>
	
	<div id="FooterDiv">
		<div id="FooterWrapDiv">
		 	 
		</div>
	</div>
</div>
<?php
}
?>
<script type="text/javascript">
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
        $('#btn_slect_buliao<?=$i?>').dialog({
            title:'添加配件',
            width: '1000px',
            height: 470,
            content:'url:Searchbuliaoforreturn.php?fwValue=<?=$i?>&cat=buliao',
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
			    this.content.document.getElementById('cat').value = 'buliao';
                this.content.document.getElementById('fwValue').value = '<?=$i?>';
            }
        });
		<?php }?>
         
         
		$('#btn_slect_employee').dialog({
            title:'选择经办人',
            width: '550px',
            height: 470,
            content:'url:BtnSearchemployee.php?fwValue=&cat=buliao',
            init:function(){
			    this.content.document.getElementById('cat').value = 'buliao';
                this.content.document.getElementById('fwValue').value = '';
            }
        });

		$('#btn_slect_customer').dialog({
            title:'选择客户',
            width: '950px',
            height: 470,
            content:'url:BtnSearchOrderForReturn.php?fwValue=&cat=buliao',
            init:function(){
			    this.content.document.getElementById('cat').value = 'buliao';
                this.content.document.getElementById('fwValue').value = '';
            }
        });
	currency_code
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
</script>
</body>

</html>
<?
echo '<script language="javascript" type="text/javascript">';
echo 'function checkall(thisform){for(var i=0;i<thisform.elements.length;i++){if(thisform.elements[i].type=="checkbox"&&thisform.elements[i].checked==false&&thisform.elements[i].name!="selectall"){thisform.elements[i].checked=true;}else if(thisform.elements[i].type=="checkbox"&&thisform.elements[i].checked==true&&thisform.elements[i].name!="selectall"){thisform.elements[i].checked=false;}} }';
echo '</script>';
include('includes/footer.inc');
?>

