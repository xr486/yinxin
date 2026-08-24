<?php

include('includes/session.inc');
$Title = _('其它应收款立账处理');

$ViewTopic= '其它应收款立账处理';
$BookMark = '其它应收款立账处理';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

unset($result);
 
	if (isset($_POST['Save'])) {
		$errorflag = 1;
		$all_amount = 0;
		foreach ($_POST as $key => $value) {
			if ($value != '') {
				if (substr($key, 0,11)=='line_remark') {
					$errorflag = 0;
					$i = substr($key, 11);
				
					if ($value != '') {
						 
						if ($_POST['this_amount'.$i]=='') {
						$errorflag = 1;
						prnMsg($value.'立账金额未填写,请确认！',error);
						}
  
						$all_amount = $all_amount + $_POST['this_amount'.$i];
                    }
				}
			}
		}
	 
	 
		 if ($_POST['invoiceamount']<>$all_amount) {
						$errorflag = 1;
						prnMsg($value.'立账金额合计'.$all_amount.'不等于发票金额'.$_POST['invoiceamount'].',请确认！',error);
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
					if (substr($key, 0,11)=='line_remark') {
						$i = substr($key, 11);
						
						if($_POST['stockid'.$i]==''){
							$_POST['stockid'.$i] = 'NULL';
							$bumishu[$i] = 0;
						}
                       
                          $sql = "insert into ar_invoice_lines_all (invoicenum,invoicelinenum,customer_code,
						  amount,line_remark,creation_date,created_by,last_update_date,last_updated_by)
						values('".$_POST['invoicenum']."','".$i."','".$_POST['customercode']."',
						'".$_POST['this_amount'.$i]."','".$_POST['line_remark'.$i]."',
						'".$time."','".$_SESSION['UserID']."','".$time."','".$_SESSION['UserID']."') ";
						
						$result = DB_query($sql,$db);
						
					}
				}
			}

		 
			$sql = "insert into ar_invoice_headers_all
                                         (ar_invoice_type,invoicenum,invoiceamount,
											taxamount,
											customer_code, 
											narrative,currency_code,
                                            invoicedate,schedulereceivedate,
                                            creation_date,
                                            created_by,
                                            last_update_date,
                                            last_updated_by) values ('".'其它应收款立账'."','".$_POST['invoicenum']."','".$_POST['invoiceamount']."','".$_POST['taxamount']."','".$_POST['customercode']."','".$_POST['Header_Remark']."','".$_POST['currency_code']."','".$Delivery_date."','".$schedulereceivedate."','".$time."','".$_SESSION['UserID']."','".$time."','".$_SESSION['UserID']."')";
			$result = DB_query($sql,$db);
			
			DB_Txn_Commit($db);
			prnMsg('出货单'.$_POST['invoicenum'].'立账开票完成！',success);
			echo "<script>location.href='index.php';</script>";

		}
	}

 ?>
 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>其它应收款立账处理</title>
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
			<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="其它应收款立账处理" alt="其它应收款立账处理">其它应收款立账处理</p>
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
			<td>客户代码：</td>  
			<td><input type="text" required="required" name="customercode" id="text_slect_customer" value="<?=$_POST['customercode']?>" size="10" maxlength="25"/>
                       <a class="btn btn-info btn-xs" id="btn_slect_customer<?=$i?>" hfre="###" title="选择客户">选择</a> </td>
       <td>币别：</td>			 
			<td  ><input readonly="readonly" type="text"   name="currency_code" id="text_slect_currency_code" value="<?=$_POST['currency_code']?>" size="5" maxlength="10"/></td>

		</tr>
         <tr>
		 <td>客户名称：</td>
		 <td colspan="5"><input readonly="readonly" type="text" name="customername" id="text_slect_name" value="<?=$_POST['customername']?>" size="70" maxlength="50"/></td>
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
		 	 <td>开票日期：</td>
			<td><input type="text" name="Delivery_date" maxlength="20" size="12" required="required" value="<?=$_POST['Delivery_date']?>" 
onfocus="WdatePicker() "></td>	
            <td>预计收款日期：</td>
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
		if (isset($_POST['customername']) and $_POST['customername'] != '') {
	?>
              <input id="purchase_table_lastRow" name="purchase_table_lastRow" type=hidden value="">
					<table id="purchase_table" cellpadding="2" class="selection">
					<tr id="list-top"> 
					<th width="30">立账金额</th>
					<th width="110">事项说明</th> 
					<th width="50" align="center">操作</th>
					</tr>
					<?php for($i=1;$i<=50;$i++){?>
			
					<tr id="purchase_table_<?=$i?>" <?php echo $i>10&&$_POST['stockid'.$i]==''?'style="display:none"':''?> class="mouse click">
					  
				    <td><input type="text"  name="this_amount<?=$i?>"  value="<?=$_POST['this_amount'.$i]?>" size="16" maxlength="16"/></td> 
					<td> <input type="text" name="line_remark<?=$i?>" value="<?=$_POST['line_remark'.$i]?>" size="55" maxlength="100"/>
						
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
       
 

		$('#btn_slect_customer').dialog({
            title:'选择客户',
            width: '950px',
            height: 470,
            content:'url:BtnSearchARCustomer.php?fwValue=&cat=buliao',
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

