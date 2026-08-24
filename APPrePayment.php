<?php
if(isset($_GET['data'])){
	 
	 include_once("connect.php"); 
	 $sql = "select * from vendors where vendor_code = '".$_GET['data']."'";
	 $result_num = mysql_query($sql, $db);
	 $res = mysql_fetch_assoc($result_num);
	 echo $res['vendor_name'].':'.$res['vendor_address'].':'.$res['vendor_contacts'].':'.$res['currencycode'].':'.$res['tax_code'];
	 return ;
 } 	 	
 
  if(isset($_GET['data3'])){
	 
	 include_once("connect.php"); 
	 $sql = "select * from fin_bank_alls where bankaccountname = '".$_GET['data3']."'";
	 $result_num = mysql_query($sql, $db);
	 $resbank = mysql_fetch_assoc($result_num);
	 echo $resbank['bankname'].':'.$resbank['bankaccount'].':'.$resbank['currency_code'].':'.$resbank['bank_onhand'];
	 return ;
 } 
include('includes/session.inc');
$Title = _('供应商预付款录入');
$ViewTopic= '供应商预付款录入';
$BookMark = '供应商预付款录入';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

unset($result);
if (isset($_POST['Save'])) 
{
  $errorflag = 0;
  $all_amount = 0;
  $all_dis_amount=0;
  $k =0;

  $time = time();
  $time2 = $time - 10;

  if ($_SESSION['lastsearchtime'] > $time2) {
      $errorflag = 1;
      prnMsg($value . '重复提交！', error);
  }

  if ($errorflag == 0) 
  {
  
	  $date = date('Ymd');
    $sql_num = "select  (CASE WHEN substr(max(transaction_num) ,-3,3) = 0 THEN RIGHT ('1000' + (max(substr(transaction_num ,- 1)) + 1),3)
                ELSE substr(max(transaction_num),-3,3) + 1 END) order_number 
			    from fin_bank_transaction_headers_all 
				where substr(transaction_num,3,8)= '" . $date . "' and transaction_num like 'YF%'";
    $result_num = DB_query($sql_num, $db);
    while ($v = DB_fetch_array($result_num)) 
	{
      if ($v['order_number'] == null) 
	  {
        $OrderNum = 'YF'.$date . '001';
      } else 
	  {
        $OrderNum =  'YF'. $date . $v['order_number'];
      }
    }

    $Delivery_date = strtotime($_POST['Delivery_date']);
    DB_Txn_Begin($db);
    $time = time();
    $delivery_amount = 0;
    $k =0;
	if ($_POST['tax_amount_all']=='') 
		 {
           $_POST['tax_amount_all']=0; 
         }

$sql = "update   fin_bank_alls
                    set bank_onhand     = bank_onhand - '".$_POST['payment_amount_all']."' 
                  where  bankaccountname  ='".$_POST['bankaccountname']."'  ";
      $result = DB_query($sql,$db);

$sql = "insert into fin_bank_transaction_headers_all
                                (transaction_type,
							  bankaccountname,
							    bankchangenum,
							      transaction_num,
							   pre_amount,dis_amount,transaction_amount,
								   tax_amount,status,
                                  vendor_code,
                                    narrative,
								currency_code,
                                 transaction_date,
                                creation_date,
                                   created_by,
                             last_update_date,
                             last_updated_by) 
			                      values ('".'AP预付款'."',
								  '".$_POST['bankaccountname']."',
								  '".$_POST['bankchangenum']."',
								  '".$OrderNum."',
								  '".$_POST['payment_amount_all']."','".$_POST['dis_amount']."','0',
                                  '".$_POST['tax_amount_all']."','建立',
								  '".$_POST['vendor_code']."',
								  '".$_POST['Header_Remark']."',
								  '".$_POST['currency_code']."',
								  '".$Delivery_date."',
								  '".$time."',
								  '".$_SESSION['UserID']."',
								  '".$time."',
								  '".$_SESSION['UserID']."')";
      // echo $sql;
		$result = DB_query($sql,$db);

    
    DB_Txn_Commit($db);
    $_SESSION['lastsearchtime'] = $time;
    prnMsg('付款单'.$OrderNum.'成功建立！',success);
    echo "<script>location.href='APPrePayment.php';</script>";
	 

   
}
}
?>
 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>新建订单</title>
<link rel="shortcut icon" href="/favicon.ico"/>
<link rel="icon" href="/favicon.ico"/>
<meta http-equiv="Content-Type" content="application/html; charset=utf-8"/>
<link href="/css/xenos/default.css" rel="stylesheet" type="text/css"/>
<script type="text/javascript" src ="./JXC/javascripts/miscfunctions.js"></script>
<script type="text/javascript" src ="./JXC/javascripts/wdatepicker.js"></script>
<script type="text/javascript">var basepath='./JXC/statics/base/images';</script>
<script type="text/javascript" src="./JXC/statics/base/js/metvar.js"></script>
<script type="text/javascript" src="./JXC/statics/base/js/jQuery1.7.2.js"></script>
<script type="text/javascript" src="./JXC/statics/base/js/uploadify/jquery.uploadify.v2.1.4.min.js"></script>
<script type="text/javascript" src="./JXC/statics/base/js/iframes.js"></script>
<script type="text/javascript" src="./JXC/statics/base/js/cookie.js"></script>
<script type="text/javascript" src="./JXC/statics/base/js/jquery.livequery.js"></script>

<link rel="stylesheet" href="jquery.ui.autocomplete.css">
<script type="text/javascript" src="ui/jquery.ui.core.js"></script>
<script type="text/javascript" src="ui/jquery.ui.widget.js"></script>
<script type="text/javascript" src="ui/jquery.ui.position.js"></script>
<script type="text/javascript" src="ui/jquery.ui.autocomplete.js"></script>

<script src="./JXC/javascript/jquery-1.7.2.min.js"></script>
<script src="./JXC/javascript/lhgdialog.min.js?self=true&skin=chrome"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
<script src="./javascript/bootstrap.min.js"></script>

<script type="text/javascript">
/*ajax执行*/
var lang = 'cn';
var metimgurl='./JXC/statics/base/images/';
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
<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="供应商预付款录入" alt="供应商预付款录入">供应商预付款录入</p>
<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST"><input type="hidden" name="time"
value="<?=$time?>">
<div>
<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
<table class="selection">
<?php
  if (!isset($_POST['Delivery_date'])) {
    $_POST['Delivery_date'] = date( "Y-m-d", mktime(0,0,0,date("m"),date("d"),date("Y")));
	$_POST['dis_amount']=0; 
}
?>
<div class="text-nav">

  <div class="text-nav-1 required"><div>银行账户名称:</div>
  <input type="text" required="required" name="bankaccountname" id="text_slect_bankaccountname" value="<?=$_POST['bankaccountname']?>" size="40" maxlength="250"  onblur="selbank()"/>
  <image class="select_img" src="img/search.png" id="btn_slect_bank<?=$i?>"/>
</div>
  <div class="text-nav-1 required"><div>银行名称:</div>
  <input readonly="readonly" type="text"   name="mybankname" id="text_slect_mybankname" value="<?=$_POST['mybankname']?>" size="50" maxlength="100"/></div>

  <div class="text-nav-1"  style="display:none;"><div>账户余额：</div>
  <input readonly="readonly" type="text"  class="number" name="bank_onhand" id="text_slect_bank_onhand" value="<?=$_POST['bank_onhand']?>" size="5" maxlength="10"/></div>  
  <div class="text-nav-1"><div>币别：</div>
  <input readonly="readonly" type="text"   name="currency_code" id="text_slect_currency_code" value="<?=$_POST['currency_code']?>" size="5" maxlength="10"/></div>  
  <div class="text-nav-1 required"><div>银行账号:</div>
  <input readonly="readonly" type="text"   name="mybankaccount" id="text_slect_mybankaccount" value="<?=$_POST['mybankaccount']?>" size="50" maxlength="100"/></div>
  <div class="text-nav-1 required"><div>供应商代码：</div>
  <input type="text" required="required" name="vendor_code" id="text_slect_vendor" value="<?=$_POST['vendor_code']?>" size="10" maxlength="25"  onblur="sel()"/>
  <image class="select_img" src="img/search.png" id="btn_slect_vendor<?=$i?>"/>
</div>
  <div class="text-nav-2 required"><div>供应商名称：</div>
  <input readonly="readonly" type="text" name="vendor_name" id="text_slect_name" value="<?=$_POST['vendor_name']?>" size="70" maxlength="50"/></div>
 
  <div class="text-nav-1"><div>税别：</div>
  <input readonly="readonly" type="text"   name="tax_code" id="text_slect_tax_name" value="<?=$_POST['tax_code']?>" size="8" maxlength="10"/></div> 
  <div class="text-nav-1 required"><div>付款/转账单号:</div>
  <input type="text"  maxlength="100" size="30" name="bankchangenum"  value="<?=$_POST['bankchangenum']?>" /></div>
 
  <div class="text-nav-1 required"><div>付款总金额:</div>
  <input type="text" class="number" required="required" name="payment_amount_all"   id="this_payment_amount_all" value="<?=$_POST['payment_amount_all']?>" size="10" maxlength="30"  onblur="checkhead()"/></div>
  <div class="text-nav-1"><div>优惠总金额:</div>
  <input type="text" class="number" readonly="readonly"  name="dis_amount"  id="dis_amount"  value="<?=$_POST['dis_amount']?>" size="10" maxlength="30"/></div>
  <div class="text-nav-1 required"><div>付款日期：</div>
  <input type="text" name="Delivery_date" maxlength="20" size="10" required="required" value="<?=$_POST['Delivery_date']?>" onfocus="WdatePicker() "></div>
  <div class="text-nav-2"><div>付款备注：</div>
  <input type="text"  maxlength="200" size="70" name="Header_Remark"  value="<?=$_POST['Header_Remark']?>" size="20" maxlength="20"/>
  <input readonly="readonly" type="hidden"   name="tax_rate" id="text_slect_tax_rate" value="<?=$_POST['tax_rate']?>" size="8" maxlength="10"/>
  <input readonly="readonly" type="hidden"   name="Address" id="text_slect_address" value="<?=$_POST['Address']?>" size="70" maxlength="50"/></div>

</div>

</table>
<div class="centre">
<input type="submit" name="Save" value="确认保存">
</div>
<input type="hidden" name="PageOffset" value="1"/><br/>
<?php
if (isset($_POST['vendor_name']) and $_POST['vendor_name'] != '') {

	$sql ="select c.po_num,po_payment_amount,po_all_amount,note,youhui_amount,check_amount,payment_amount,dis_payment_amount,(po_all_amount-payment_amount) wait_amount
	from po_headers_all c 
				where po_all_amount-payment_amount>0
				and c.vendor_code= '" .$_POST['vendor_code'] . "'";
	// echo $sql;
	$result = DB_query($sql,$db);
    if (DB_num_rows($result)==0) {
        unset($result);
        prnMsg(_('没有需要付款的行，请重新输入条件查询！') ,'error');
    }

?>
<input id="purchase_table_lastRow" name="purchase_table_lastRow" type=hidden value="">

<div class="centre"> 
                        <p id="Prompt" style="color: red;font-size: 20px"></p>
                    </div>

<table id="purchase_table" cellpadding="2" class="selection">
 
<tr id="list-top">
  <th width="130">采购单</th>
  <th width="10" >订单总金额</th>
  <th width="10" >订单优惠金额</th> 
  <th width="10" >订单应付金额</th>
  <th width="10">采购单备注</th>
  <th width="10" >对账金额</th>
  <th width="10" >已付款金额</th>
  <th width="10" >已优惠金额</th>
  <th width="10" >待付款金额</th>
  <th width="110" > 本次付款金额 </th>
  <th width="90" > 本次优惠金额  </th>
  <th width="15" align="center">选择</th>
 
</tr>
<?php   $i=1;
 while ($myrow = DB_fetch_array($result))  {
	 $_POST['this_invoice_dis_amount'.$i]=0;
	 ?>
		 
<tr id="purchase_table_<?=$i?>" >

  <td> <input type="text" readonly="readonly" name="po_num<?=$i?>" id="text_slect_receipt_num<?=$i?>" value="<?= $myrow['po_num'] ?>" size="10" maxlength="25"/> <span style="color:red">*</span></td>
<td> <input type="text" readonly="readonly" name="po_all_amount<?=$i?>" id="text_slect_po_all_amount<?=$i?>" value="<?= $myrow['po_all_amount'] ?>" size="8" maxlength="10"/></td>

  
  <td><input readonly="readonly" type="text" name="youhui_amount<?=$i?>" id="text_slect_remark<?=$i?>" value="<?= $myrow['youhui_amount'] ?>" size="8" maxlength="150"/></td>
<td> <input type="text" readonly="readonly" name="po_payment_amount<?=$i?>" id="text_po_payment_amount<?=$i?>" value="<?= $myrow['po_payment_amount'] ?>" size="8" maxlength="10"/></td>

  <td><input type="text" readonly="readonly" name="note<?=$i?>"  id="text_slect_transaction_date<?=$i?>" value="<?= $myrow['note'] ?>" size="12" maxlength="100"/></td>
  
  <td><input type="text" readonly="readonly" name="check_amount<?=$i?>"  id="text_check_amount<?=$i?>" value="<?= $myrow['check_amount'] ?>" size="6" maxlength="10"/></td> 
  <td><input type="text" readonly="readonly" name="payment_amount<?=$i?>"  id="text_payment_amount<?=$i?>" value="<?= $myrow['payment_amount'] ?>" size="11" maxlength="10"/></td> 
  <td><input type="text" readonly="readonly" name="dis_payment_amount<?=$i?>"  id="text_dis_payment_amount<?=$i?>" value="<?= $myrow['dis_payment_amount'] ?>" size="11" maxlength="10"/></td>
  <td><input type="text"  name="wait_amount<?=$i?>"  id="text_wait_amount<?=$i?>"  value="<?= $myrow['wait_amount'] ?>" size="12" maxlength="10"/></td>
  <td><input type="text" class="number" required="required"  name="this_payment_amount<?=$i?>" onkeyup="check(<?=$i?>)"  onblur="checktotal()"  id="text_this_payment_amount<?=$i?>"    value="<?=$_POST['this_payment_amount'.$i]?>"     size="8" maxlength="10"/><span style="color:red">*</span></td>

  <td><input type="text"  class="number"  name="this_payment_dis_amount<?=$i?>" onkeyup="check(<?=$i?>)"  onblur="checktotal()" id="text_this_payment_dis_amount<?=$i?>"  value="<?=$_POST['this_payment_dis_amount'.$i]?>" size="8" maxlength="10"/></td>
  <td><input type="checkbox" name="status<?=$i?>" /></td>

</tr>
<?php 
 $i=$i+1;
  }?>

  	<tr><td colspan="11"><p><input type="checkbox" name="selectall" onclick="checkall(this.form);"/>全选/取消全选</p></td></tr>
	<td ><input type="hidden" name="flag" value="<?=$i-1?>" size="15" maxlength="45"/></td> 
</table>

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
     
 


	 function  checkhead(){
		var a=document.getElementById("text_slect_bank_onhand").value; 
		var b=document.getElementById("this_payment_amount_all").value;
		  
		 if(parseFloat(b) <0 ){
            document.getElementById("Prompt").innerHTML="付款金额不可以小于0！";
            document.getElementById("this_payment_amount_all").value="";
            document.getElementById("this_payment_amount_all").focus();
        }   else {
            document.getElementById("Prompt").innerHTML="";
        }

	}
   
 function checktotal(){       
								   var  all_invoice_amount=0; 
								   var  all_dis_amount=0;                
                                for(var i=1 ; i < 50; i++){   
									if (document.getElementById("text_this_payment_amount" + i)==null)  {
									p=0;
										}
									else {								 
		                            
								   var  invoice_amount=0;
								   var  dis_amount=0;
                                   var invoice_amount=document.getElementById("text_this_payment_amount"+i).value;   
                                   var dis_amount=document.getElementById("text_this_payment_dis_amount"+i).value;                               
								     
								   if( invoice_amount>0 )
								   {  
								   all_invoice_amount=Number(all_invoice_amount) + Number(invoice_amount);                                 
                                   }
								   if( dis_amount>0 )
								   {  
								   all_dis_amount=Number(all_dis_amount) + Number(dis_amount);                                 
                                   }

								    }}
		            document.getElementById("this_payment_amount_all").value=all_invoice_amount;
		            document.getElementById("dis_amount").value=all_dis_amount;
		 }
     
function  check(s1){
	    var a=document.getElementById("text_this_payment_amount"+s1).value;
        var b=document.getElementById("text_this_payment_dis_amount"+s1).value;
        var c=document.getElementById("text_wait_amount"+s1).value;
        var d=parseFloat(a) + parseFloat(b);
		var e=document.getElementById("text_po_payment_amount"+s1).value;
		var f=document.getElementById("text_payment_amount"+s1).value;
		var g=document.getElementById("text_dis_payment_amount"+s1).value; 
		var shengyu=parseFloat(e) - parseFloat(f) - parseFloat(g);
        if(parseFloat(a) < 0 ){
            document.getElementById("Prompt").innerHTML="本次免付款金额不可以小于0！！！！";
            document.getElementById("text_this_payment_dis_amount"+s1).value="";
            document.getElementById("text_this_payment_dis_amount"+s1).focus();
        }else if(parseFloat(b) < 0 ){
            document.getElementById("Prompt").innerHTML="本次免付款金额不可以小于0！！！！";
            document.getElementById("text_this_payment_amount"+s1).value="";
            document.getElementById("text_this_payment_amount"+s1).focus();
        } else if(parseFloat(d)>parseFloat(shengyu) ){
            document.getElementById("Prompt").innerHTML="本次总付款总金额+历史已付总金额不可以超过订单应付金额！！！！";
            document.getElementById("text_this_payment_amount"+s1).value="";
            document.getElementById("text_this_payment_amount"+s1).focus();
        }  else {
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
         



			$('#btn_slect_bank').dialog({
            title:'选择银行',
            width: '950px',
            height: 470,
            content:'url:BtnSearchBank.php?fwValue=&cat=buliao',
            init:function(){
			    this.content.document.getElementById('cat').value = 'buliao';
                this.content.document.getElementById('fwValue').value = '';
            }
        });


		$('#btn_slect_vendor').dialog({
            title:'选择供应商',
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

	$(function(){
		$( "#text_slect_vendor" ).autocomplete({
			source: "autosearchvendor.php",
			minLength: 2,
			autoFocus: true
		});
	});
	 
	 function sel(){
		var name=$('#text_slect_vendor').val()
		$.get("","data="+name,function(res){
			name = res.split(":")		  
				$("#text_slect_name").val(name[0]) 
				$("#text_slect_address").val(name[1])
				$("#text_slect_contacts").val(name[2])
				$("#text_slect_currency_code").val(name[3])
				$("#text_slect_tax_name").val(name[4])
		})	
	} 

	 $(function(){
		$( "#text_slect_bankaccountname" ).autocomplete({
			source: "autosearchbank.php",
			minLength: 2,
			autoFocus: true
		});
	});


			function selbank(){
		var name=$('#text_slect_bankaccountname').val()
		$.get("","data3="+name,function(resbank){
			name = resbank.split(":")		  
				$("#text_slect_mybankname").val(name[0])
				$("#text_slect_mybankaccount").val(name[1])
				$("#text_slect_currency_code").val(name[2])
                $("#text_slect_bank_onhand").val(name[3])
   
		})	
	}    


	function checkall(thisform){for(var i=0;i<thisform.elements.length;i++){if(thisform.elements[i].type=="checkbox"&&thisform.elements[i].checked==false&&thisform.elements[i].name!="selectall"){thisform.elements[i].checked=true;}else if(thisform.elements[i].type=="checkbox"&&thisform.elements[i].checked==true&&thisform.elements[i].name!="selectall"){thisform.elements[i].checked=false;}} }
</script>
</body>

</html>
<? 
include('includes/footer.inc');
?>

