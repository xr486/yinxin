<?php

include('includes/session.inc');
$Title = _('销售开票处理');
$ViewTopic= '销售开票处理';
$BookMark = '销售开票处理';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

unset($result);
 
	if (isset($_POST['Save'])) {
		$errorflag = 1;
		$all_amount = 0;
		foreach ($_POST as $key => $value) {
			if ($value != '') {
				if (substr($key, 0,12)=='shipment_num') {
					$errorflag = 0;
					$i = substr($key, 12);
				
					if ($value != '') {
						 
						if ($_POST['this_quantity'.$i]=='') {
						$errorflag = 1;
						prnMsg($value.'开票金额未填写,请确认！',error);
						}

						if ($_POST['this_quantity'.$i]< 0 ) {
						$errorflag = 1;
						prnMsg($value.'开票金额不可小于0,请确认！',error);
						}

						if ($_POST['this_dis_quantity'.$i]< 0 ) {
						$errorflag = 1;
						prnMsg($value.'开票金额不可小于,请确认！',error);
						}

						$this_quantity= $_POST['this_quantity'.$i] + $_POST['this_dis_quantity'.$i];

						if ($_POST['wait_invoice_amount'.$i]=='') {
						$errorflag = 1;
						prnMsg($value.'待立账金额为空,请确认！',error);
						}
                         
						if ($this_quantity > $_POST['wait_invoice_amount'.$i] ) {
							$errorflag = 1;
							prnMsg($value.'本次开票总金额'.$_POST['this_quantity'.$i].'不可以大于未开票金额'.$_POST['wait_invoice_amount'.$i],error);
						}
						 
						
                    }
				}
			}
		}
	 
	 
	 if ($errorflag ==0) {
			foreach ($_POST as $key => $value) {
				if ($value != '') {
					if (substr($key, 0,12)=='shipment_num') {
						$i = substr($key, 12);
					 
						$lineamount[$i] =  $_POST['this_quantity'.$i]  ;
						$all_amount = $all_amount +$lineamount[$i]; 
					}
				}
			}
		}
 
		 if ($_POST['invoiceamount']<>$all_amount) {
						$errorflag = 1;
						prnMsg($value.'立账金额合计'.$all_amount.'不等于发票总金额'.$_POST['invoiceamount'].',请确认！',error);
						}
      

		if ($errorflag == 0) {
			$Delivery_date = strtotime($_POST['Delivery_date']); 
			
			DB_Txn_Begin($db);
			$time = time();
			$delivery_amount = 0;
			foreach ($_POST as $key => $value) {
				if ($value != '') {
					if (substr($key, 0,12)=='shipment_num') {
						$i = substr($key, 12);
						
						if($_POST['this_dis_quantity'.$i]==''){
							$_POST['this_dis_quantity'.$i] = 0;
							 
						}
                       
                          $sql = "insert into ar_invoice_lines_all (invoicenum,invoicelinenum,customer_code,amount,dis_amount,shipment_num,
						creation_date,created_by,last_update_date,last_updated_by)
						values('".$_POST['invoicenum']."','".$i."','".$_POST['customercode']."',
						'".$_POST['this_quantity'.$i]."','".$_POST['this_dis_quantity'.$i]."','".$_POST['shipment_num'.$i]."',
						'".$time."','".$_SESSION['UserID']."','".$time."','".$_SESSION['UserID']."') ";
						
						$result = DB_query($sql,$db);
						

						$sql = "update  so_delivery_headers_all
						set already_invoice_amount=already_invoice_amount+'".$_POST['this_quantity'.$i]."'
						,dis_invoice_amount=dis_invoice_amount+'".$_POST['this_dis_quantity'.$i]."'
						where  delivery_num ='".$_POST['shipment_num'.$i]."' "; 
						$result = DB_query($sql,$db);
 
		 

					}
				}
			}

		 
			$sql = "insert into ar_invoice_headers_all
                                         (ap_invoice_type,invoicenum,invoiceamount, 
											customer_code, 
											narrative,currency_code,
                                            invoicedate,
                                            creation_date,created_by,
                                            last_update_date,
                                            last_updated_by) values ('".'应付款立账'."','".$_POST['invoicenum']."','".$_POST['invoiceamount']."', '".$_POST['customercode']."','".$_POST['Header_Remark']."','".$_POST['currency_code']."','".$Delivery_date."','".$time."','".$_SESSION['UserID']."','".$time."','".$_SESSION['UserID']."')";
			$result = DB_query($sql,$db);
			
			DB_Txn_Commit($db);
			prnMsg('销项发票'.$_POST['invoicenum'].'开立完成！',success);
			echo "<script>location.href='index.php';</script>";

		}
	}

 ?>
 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>销售开票处理</title>
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
 
<div id="CanvasDiv">
	<div id="BodyDiv">
		<div id="BodyWrapDiv">
			<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="销售开票处理" alt="销售开票处理">销售开票处理</p>
			<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST"><input type="hidden" name="time" 

value="<?=$time?>">
				<div>
				<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
				<table class="selection">
		 
		

		<tr>
			<td>客户代码：</td>  
			<td><input type="text" required="required" name="customercode" id="text_slect_customer" value="<?=$_POST['customercode']?>" size="10" maxlength="25"/>
                       <a class="btn btn-info btn-xs" id="btn_slect_customer<?=$i?>" hfre="###" title="选择客户">选择</a> </td>
    
      		 <td>客户名称：</td>
		 <td colspan="5"><input readonly="readonly" type="text" name="customername" id="text_slect_name" value="<?=$_POST['customername']?>" size="50" maxlength="100"/></td>	

		</tr>
         

         <tr>
			<td>出货地址：</td>			 
			<td  colspan="3"><input readonly="readonly" type="text"   name="Address" id="text_slect_address" value="<?=$_POST['Address']?>" size="50" maxlength="100"/></td>	
			   <td>币别：</td>			 
			<td  ><input readonly="readonly" type="text"   name="currency_code" id="text_slect_currency_code" value="<?=$_POST['currency_code']?>" size="5" maxlength="10"/></td>
		</tr>
		<tr>
			<td>客户银行名称：</td>			 
			<td colspan="3"><input readonly="readonly" type="text"   name="BankName" id="text_slect_bankname" value="<?=$_POST['BankName']?>" size="30" maxlength="50"/></td>
			<td>客户银行账号：</td>			 
			<td colspan="2"><input readonly="readonly" type="text"   name="BankAccount" id="text_slect_bankaccount" value="<?=$_POST['BankAccount']?>" size="40" maxlength="50"/></td>

			 
			 <td  ><input   type="hidden"   required="required" name="Concact" id="text_slect_contacts" value="<?=$_POST['Concact']?>" size="20" maxlength="20"/></td>
		</tr>
		

		  <tr>
		    <td>发票号码:</td> 
			 <td  ><input type="text" required="required" maxlength="100" size="20" name="invoicenum"  value="<?=$_POST['invoicenum']?>" size="15" maxlength="20"/> </td>
			<td>发票金额:</td> 
			 <td  ><input type="text" class="number" required="required" maxlength="100" size="5" name="invoiceamount"  value="<?=$_POST['invoiceamount']?>" size="20" maxlength="20"/> </td>
		 
			 
		
</tr>
		<tr>
			 
		 	 <td>发票日期：</td>
			<td><input type="text" name="Delivery_date" maxlength="20" size="10" required="required" value="<?=$_POST['Delivery_date']?>" 
onfocus="WdatePicker() "></td>	
		 
			<td>发票备注：</td> 
			 <td colspan="5"><input type="text"  maxlength="200" size="70" name="Header_Remark"  value="<?=$_POST['Header_Remark']?>" size="20" maxlength="20"/> </td>
		</tr>

	</table>
	<div class="centre">
		<input type="submit" name="Hearder" value="保存发票匹配送货单">
	</div>
	<input type="hidden" name="PageOffset" value="1"/><br/>
	<?php
		if (isset($_POST['customername']) and $_POST['customername'] != '') {
	?>
              <input id="purchase_table_lastRow" name="purchase_table_lastRow" type=hidden value="">
					<table id="purchase_table" cellpadding="2" class="selection">
					<tr id="list-top">
					<th width="150">销售送货单号</th>
					<th width="10">送货单备注</th>  
					<th width="10" >送货日期</th> 
					<th width="30">应开票金额</th>
					<th width="30">已开票金额</th>  
					<th width="30">已优惠金额</th>  
					<th width="30">未开票金额</th>
					<th width="30">本次开票金额</th>
					<th width="30">免开票金额</th>
					<th width="50" align="center">操作</th>
					</tr>
					<?php for($i=1;$i<=50;$i++){?>
			
					<tr id="purchase_table_<?=$i?>" <?php echo $i>10&&$_POST['shipment_num'.$i]==''?'style="display:none"':''?> class="mouse click">

					<td> <input type="text" name="shipment_num<?=$i?>" id="text_slect_delivery_num<?=$i?>" value="<?=$_POST['shipment_num'.$i]?>" size="11" maxlength="25"/>
					<a class="btn btn-info btn-xs" id="btn_slect_po<?=$i?>" hfre="###" title="选择送货单">选择</a> </td>

					 <td><input readonly="readonly" type="text" name="narrative<?=$i?>" id="text_slect_narrative<?=$i?>" value="<?=$_POST['narrative'.$i]?>" size="20" maxlength="200"/></td>
  

					 <td><input type="text" readonly="readonly" name="delivery_date<?=$i?>"  id="text_slect_delivery_date<?=$i?>" value="<?=$_POST['delivery_date'.$i]?>" size="8" maxlength="10"/></td> 
 
				 <td><input type="text" readonly="readonly" name="need_payment_amount<?=$i?>" id="text_slect_delivery_amount<?=$i?>" value="<?=$_POST['need_payment_amount'.$i]?>" size="6" maxlength="25"/>
					  
					  <td><input type="text" readonly="readonly" name="already_invoice_amount<?=$i?>" id="text_slect_already_invoice_amount<?=$i?>" value="<?=$_POST['already_invoice_amount'.$i]?>" size="6" maxlength="25"/>

					   <td><input type="text" readonly="readonly" name="dis_invoice_amount<?=$i?>"  id="text_slect_dis_invoice_amount<?=$i?>" value="<?=$_POST['dis_invoice_amount'.$i]?>" size="6" maxlength="10"/></td>  
					    
					   <td><input type="text" readonly="readonly" name="wait_invoice_amount<?=$i?>"  id="text_slect_wait_invoice_amount<?=$i?>" value="<?=$_POST['wait_invoice_amount'.$i]?>" size="6" maxlength="10"/></td>    
						
						
						<td><input type="text"  name="this_quantity<?=$i?>"  value="<?=$_POST['this_quantity'.$i]?>" size="8" maxlength="10"/></td> 
						<td><input type="text"  name="this_dis_quantity<?=$i?>"  value="<?=$_POST['this_dis_quantity'.$i]?>" size="8" maxlength="10"/></td> 
						
                      <td>  <a onclick="delettr($(this));" style="padding:0px 5px;" href="javascript:;">删除</a></td>
  
					</tr>
					<?php }?>
					
					</table>
					
	               <div class="centre">
					<a onclick="addsave();">添加行</a>
	                
					</div>

					<div class="centre">
	                <input type="submit" name="Save" value="提交">
					</div>
	<?php
		}
	?>
					<input type="hidden" name="idcount" id='idcount' value="11"/>
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
            title:'选择未开票送货单',
            width: '950px',
            height: 520,
            content:'url:SearchNoInvoiceship.php?fwValue=<?=$i?>&cat=<?=$_POST['customercode']?>',
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


		$('#btn_slect_customer').dialog({
            title:'选择客户',
            width: '950px',
            height: 470,
            content:'url:BtnSearchAPcustomer.php?fwValue=&cat=buliao',
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
</script>
</body>

</html>
<?
include('includes/footer.inc');
?>

