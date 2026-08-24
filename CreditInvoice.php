<?php

include('includes/session.inc');
$Title = _('红字发票立账处理');
$ViewTopic= '红字发票立账处理';
$BookMark = '红字发票立账处理';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

unset($result);
 
	if (isset($_POST['Save'])) {
		$errorflag = 1;
		$all_amount = 0;
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
						if ($_POST['this_quantity'.$i]=='') {
						$errorflag = 1;
						prnMsg($value.'立账数量未填写,请确认！',error);
						}

						if ($_POST['wait_amount'.$i]=='') {
						$errorflag = 1;
						prnMsg($value.'待立账金额为空,请确认！',error);
						}
  
						if ($_POST['this_quantity'.$i] > $_POST['wait_quantity'.$i] ) {
							$errorflag = 1;
							prnMsg($value.'立账数量'.$_POST['this_quantity'.$i].'不可以大于未立账数量'.$_POST['wait_quantity'.$i],error);
						}
						 
						
                    }
				}
			}
		}
	 
	 
	 if ($errorflag ==0) {
			foreach ($_POST as $key => $value) {
				if ($value != '') {
					if (substr($key, 0,7)=='stockid') {
						$i = substr($key, 7);
					 
						$lineamount[$i] = $_POST['this_quantity'.$i] * $_POST['unitprice'.$i] ;
						$all_amount = $all_amount +$lineamount[$i];
						
					}
				}
			}
		}

		 if ($_POST['invoiceamount']<>$all_amount) {
						$errorflag = 1;
						prnMsg($value.'立账金额合计'.$all_amount.'不等于发票总金额'.$_POST['invoiceamount'].',请确认！',error);
						}
       if ($_POST['invoiceamount']<$_POST['taxamount']) {
						$errorflag = 1;
						prnMsg($value.'税金'.$_POST['taxamount'].'大于发票金额'.$_POST['invoiceamount'].',请确认！',error);
						}
        

		if ($errorflag == 0) {
			$Delivery_date = strtotime($_POST['Delivery_date']);
			$schedulereceivedate = strtotime($_POST['schedulereceivedate']);
			
			DB_Txn_Begin($db);
			$time = time();
			$delivery_amount = 0;
			foreach ($_POST as $key => $value) {
				if ($value != '') {
					if (substr($key, 0,7)=='stockid') {
						$i = substr($key, 7);
						
						if($_POST['stockid'.$i]==''){
							$_POST['stockid'.$i] = 'NULL';
							$bumishu[$i] = 0;
						}
                        $this_quantity[$i]=0-$_POST['this_quantity'.$i];
						$lin_eamount[$i]=0-$lineamount[$i];
                          $sql = "insert into ap_invoice_lines_all (invoicenum,invoicelinenum,vendor_code,item_no,price,
						  billed_quantity,amount,po_num,po_line,
						creation_date,created_by,last_update_date,last_updated_by)
						values('".$_POST['invoicenum']."','".$i."','".$_POST['vendor_code']."','".$_POST['stockid'.$i]."','".$_POST['unitprice'.$i]."',
						'".$this_quantity[$i]."','".$lin_eamount[$i]."','".$_POST['po_num'.$i]."','".$_POST['po_line'.$i]."',
						'".$time."','".$_SESSION['UserID']."','".$time."','".$_SESSION['UserID']."') ";
						
						$result = DB_query($sql,$db);
						

						$sql = "update po_lines_all
						set quantity_billed=quantity_billed-'".$_POST['this_quantity'.$i]."'
						where  po_num ='".$_POST['po_num'.$i]."'
						and line='".$_POST['po_line'.$i]."'"; 
						$result = DB_query($sql,$db);
 
		 

					}
				}
			}

		 
			$sql = "insert into ap_invoice_headers_all
                                         (ap_invoice_type,invoicenum,invoiceamount,
											taxamount,
											vendor_code, 
											narrative,currency_code,
                                            invoicedate,schedulepaymentdate,
                                            creation_date,created_by,
                                            last_update_date,
                                            last_updated_by) values ('".'红字发票立账'."','".$_POST['invoicenum']."','".'-'.$_POST['invoiceamount']."',
											'".$_POST['taxamount']."','".$_POST['vendor_code']."','".$_POST['Header_Remark']."','".$_POST['currency_code']."','".$Delivery_date."','".$schedulereceivedate."','".$time."','".$_SESSION['UserID']."','".$time."','".$_SESSION['UserID']."')";
			$result = DB_query($sql,$db);
			
			DB_Txn_Commit($db);
			prnMsg('红字发票'.$_POST['invoicenum'].'建立完成！',success);
			echo "<script>location.href='index.php';</script>";

		}
	}

 ?>
 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>红字发票立账处理</title>
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
			<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="红字发票立账处理" alt="红字发票立账处理">红字发票立账处理</p>
			<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST"><input type="hidden" name="time" 

value="<?=$time?>">
				<div>
				<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
				<table class="selection">
		 
		  <tr>
		    <td>发票号码:</td> 
			 <td  ><input type="text" required="required" maxlength="100" size="20" name="invoicenum"  value="<?=$_POST['invoicenum']?>" size="20" maxlength="20"/> </td>
			<td>发票金额:</td> 
			 <td  ><input type="text" required="required" maxlength="100" size="20" name="invoiceamount"  value="<?=$_POST['invoiceamount']?>" size="20" maxlength="20"/> </td>
			 <td>税金:</td> 
			 <td  ><input type="text" required="required" maxlength="100" size="20" name="taxamount"  value="<?=$_POST['taxamount']?>" size="20" maxlength="20"/> </td>
			 
		</tr>

		<tr>
			<td>供应商代码：</td>  
			<td><input type="text" required="required" name="vendor_code" id="text_slect_vendor" value="<?=$_POST['vendor_code']?>" size="10" maxlength="25"/>
                       <a class="btn btn-info btn-xs" id="btn_slect_vendor<?=$i?>" hfre="###" title="选择供应商">选择</a> </td>
       <td>币别：</td>			 
			<td  ><input readonly="readonly" type="text"   name="currency_code" id="text_slect_currencycode" value="<?=$_POST['currency_code']?>" size="5" maxlength="10"/></td>

		</tr>
         <tr>
		 <td>供应商名称：</td>
		 <td colspan="5"><input readonly="readonly" type="text" name="vendorname" id="text_slect_name" value="<?=$_POST['vendorname']?>" size="70" maxlength="50"/></td>
          </tr>

         <tr>
			<td>发票地址：</td>			 
			<td  colspan="5"><input readonly="readonly" type="text"   name="Address" id="text_slect_address" value="<?=$_POST['Address']?>" size="70" maxlength="50"/></td>	
		</tr>
		<tr>
			<td>银行名称：</td>			 
			<td colspan="2"><input readonly="readonly" type="text"   name="BankName" id="text_slect_bankname" value="<?=$_POST['BankName']?>" size="30" maxlength="50"/></td>
			<td>银行账号：</td>			 
			<td colspan="2"><input readonly="readonly" type="text"   name="BankAccount" id="text_slect_bankaccount" value="<?=$_POST['BankAccount']?>" size="40" maxlength="50"/></td>
		</tr>

		<tr>
			<td>联系方式：</td> 
			 <td  ><input   type="text" required="required" name="Concact" id="text_slect_contacts" value="<?=$_POST['Concact']?>" size="20" maxlength="20"/></td>
		 	 <td>发票日期：</td>
			<td><input type="text" name="Delivery_date" maxlength="20" size="12" required="required" value="<?=$_POST['Delivery_date']?>" 
onfocus="WdatePicker() "></td>	
            <td>预计付款日期：</td>
			<td><input type="text" name="schedulereceivedate" maxlength="20" size="12" required="required" value="<?=$_POST['schedulereceivedate']?>" 
onfocus="WdatePicker() "></td>	
		</tr>
       
		<tr>
			<td>发票备注：</td> 
			 <td colspan="5"><input type="text"  maxlength="200" size="70" name="Header_Remark"  value="<?=$_POST['Header_Remark']?>" size="20" maxlength="20"/> </td>
		</tr>

	</table>
	<div class="centre">
		<input type="submit" name="Hearder" value="保存发票头信息">
	</div>
	<input type="hidden" name="PageOffset" value="1"/><br/>
	<?php
		if (isset($_POST['vendorname']) and $_POST['vendorname'] != '') {
	?>
              <input id="purchase_table_lastRow" name="purchase_table_lastRow" type=hidden value="">
					<table id="purchase_table" cellpadding="2" class="selection">
					<tr id="list-top">
					<th width="150">采购单号</th>
					<th width="10">行</th>  
					<th width="110">料号</th> 
					<th width="10" >单位</th>
					<th width="10" >单价</th> 
					<th width="30">来料报检量</th>
					<th width="30">检验量</th> 
					<th width="30">入库量</th>  
					<th width="30">已立账量</th> 
					<th width="30">待退账量</th> 
					<th width="30">待退账金额</th>
					<th width="30">待退账数量</th>
					<th width="50" align="center">操作</th>
					</tr>
					<?php for($i=1;$i<=50;$i++){?>
			
					<tr id="purchase_table_<?=$i?>" <?php echo $i>10&&$_POST['stockid'.$i]==''?'style="display:none"':''?> class="mouse click">

					<td> <input type="text" name="po_num<?=$i?>" id="text_slect_po_num<?=$i?>" value="<?=$_POST['po_num'.$i]?>" size="11" maxlength="25"/>
					<a class="btn btn-info btn-xs" id="btn_slect_po<?=$i?>" hfre="###" title="选择采购单">选择</a> </td>

					<td><input type="text" readonly="readonly" name="po_line<?=$i?>" id="text_slect_po_line<?=$i?>" value="<?=$_POST['po_line'.$i]?>" size="2" maxlength="25"/>

					<td><input type="text" readonly="readonly" name="stockid<?=$i?>" id="text_slect_ItemNo<?=$i?>" value="<?=$_POST['stockid'.$i]?>" size="12" maxlength="25"/>

					 <td><input readonly="readonly" type="text" name="UOM<?=$i?>" id="text_slect_units<?=$i?>" value="<?=$_POST['UOM'.$i]?>" size="3" maxlength="4"/></td>

					  <td><input type="text" readonly="readonly" name="unitprice<?=$i?>"  id="text_slect_price<?=$i?>" value="<?=$_POST['unitprice'.$i]?>" size="8" maxlength="10"/></td> 

				 <td><input type="text" readonly="readonly" name="quantity_received<?=$i?>" id="text_slect_quantity_received<?=$i?>" value="<?=$_POST['quantity_received'.$i]?>" size="8" maxlength="25"/>
					  
					  <td><input type="text" readonly="readonly" name="quantity_accepted<?=$i?>" id="text_slect_quantity_accepted<?=$i?>" value="<?=$_POST['quantity_accepted'.$i]?>" size="8" maxlength="25"/>

					   <td><input type="text" readonly="readonly" name="quantity_deliveried<?=$i?>"  id="text_slect_quantity_deliveried<?=$i?>" value="<?=$_POST['quantity_deliveried'.$i]?>" size="8" maxlength="10"/></td>  
					   
					   <td><input type="text" readonly="readonly" name="quantity_billed<?=$i?>"  id="text_slect_quantity_billed<?=$i?>" value="<?=$_POST['quantity_billed'.$i]?>" size="8" maxlength="10"/></td>  


					   <td><input type="text" readonly="readonly" name="wait_quantity<?=$i?>"  id="text_slect_wait_quantity<?=$i?>" value="<?=$_POST['wait_quantity'.$i]?>" size="8" maxlength="10"/></td>   

						<td><input type="text" readonly="readonly" name="wait_amount<?=$i?>" id="text_slect_wait_amount<?=$i?>"  value="<?=$_POST['wait_amount'.$i]?>" size="8" maxlength="10"/></td>
						
						
						<td><input type="text"  name="this_quantity<?=$i?>"  value="<?=$_POST['this_quantity'.$i]?>" size="8" maxlength="10"/></td> 
						
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
            title:'选择退退货采购单',
            width: '950px',
            height: 520,
            content:'url:SearchAlreadyInvoicePo.php?fwValue=<?=$i?>&cat=<?=$_POST['vendor_code']?>',
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
			    this.content.document.getElementById('cat').value = $_POST['vendor_code'];
                this.content.document.getElementById('fwValue').value = '<?=$i?>';
            }
        });
		<?php }?>


		$('#btn_slect_vendor').dialog({
            title:'选择供应商',
            width: '950px',
            height: 470,
            content:'url:BtnSearchAPVendor.php?fwValue=&cat=buliao',
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

