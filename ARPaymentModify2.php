<?php

include('includes/session.inc');
$Title = _('客户收款维护');
$ViewTopic= '客户收款维护';
$BookMark = '客户收款维护';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

if (isset($_GET['Updateorder_number']) ) {
$_POST['transaction_num']=$_GET['Updateorder_number'];
}



unset($result);
if (isset($_POST['Save'])) 
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
   

		  $sql1 = "select * from fin_bank_transaction_lines_all  
                  where  transaction_num  ='".$_POST['transaction_num']."'   ";
          $result1 = DB_query($sql1,$db);
		  while ($v = DB_fetch_array($result1)) {
             if ($_POST['transaction_type']=='AR收款') {
			  $sql2 = "update  ar_invoice_headers_all
                    set payment_amount     = payment_amount     - '".$v['transaction_amount']."'
                      ,last_updated_by =  '".$_SESSION['UserID']."'
					 ,last_update_date =  '".$time."'
                  where  invoice_name  ='".$v['ap_invoice_name']."' ";
             $result2 = DB_query($sql2,$db);
			// echo $sql2;
			 } else {
			  $sql2 = "update  ar_invoice_headers_all
                    set payment_amount     = payment_amount     + '".$v['transaction_amount']."' 
					,last_updated_by =  '".$_SESSION['UserID']."'
					 ,last_update_date =  '".$time."'
                  where  invoice_name  ='".$v['ap_invoice_name']."' ";
             $result2 = DB_query($sql2,$db);
			 }
 	  	 	 	 	 
		  }

		  if ($_POST['transaction_type']=='AR收款') {
$sql = "update  fin_bank_alls
                    set bank_onhand     = bank_onhand - '".$_POST['transaction_amount']."' 
                  where  bankaccountname  ='".$_POST['bankaccountname']."'  ";
      $result = DB_query($sql,$db);
		  } else {
$sql = "update  fin_bank_alls
                    set bank_onhand     = bank_onhand + '".$_POST['transaction_amount']."' 
                  where  bankaccountname  ='".$_POST['bankaccountname']."'  ";
      $result = DB_query($sql,$db);
		  } 

	  $sql = "update  fin_bank_transaction_headers_all
                    set  status='失效' ,
					last_updated_by =  '".$_SESSION['UserID']."'
					 ,last_update_date =  '".$time."'
                  where  transaction_num  ='".$_POST['transaction_num']."'   ";
          $result = DB_query($sql,$db);

          
    DB_Txn_Commit($db);
	 
    prnMsg('付款单'.$_POST['transaction_num'].'已作废完成！',success);
     echo "<script>location.href='ARPaymentModify.php';</script>";
	 

        
    }


 
   
}

?>
 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>客户收款维护</title>
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
<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="客户收款维护" alt="客户收款维护">客户收款维护</p>
<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST"><input type="hidden" name="time"
value="<?=$time?>">
<div>
<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
<table class="selection">

<?php
	 

$sql ="select pha.transaction_type,
			      pha.bankaccountname,
							    pha.bankchangenum,
							      pha.transaction_num,
							   pha.transaction_amount,dis_amount,
								   pha.tax_amount,
                                  pha.customer_code,
                                    pha.narrative,
								pha.currency_code,
                                 pha.transaction_date,
                pha.creation_date,pha.created_by,pha.customer_code,c.customer_name,pha.status
	from fin_bank_transaction_headers_all pha, customers c
            where  pha.transaction_type in ('AR收款','AR预收款','AR退款')
			and pha.customer_code=c.customer_code
AND transaction_num= '" .$_POST['transaction_num'] . "'";
	//echo $sql;
	$result = DB_query($sql,$db);
    if (DB_num_rows($result)==0) {
        unset($result);
        prnMsg(_('没有资料，请重新输入条件查询！') ,'error');
	 }
  $myrow2 = DB_fetch_array($result);
      
?>
<tr>
<div class="text-nav">
 <div class="text-nav-1 "><div>流水号</div>
  <input readonly="readonly" type="text"  name="transaction_num" id="text_slect_currency_code" value="<?=$myrow2['transaction_num']?>" size="5" maxlength="10"/></div>   <div class="text-nav-1 "><div>收款/转账单号</div>
  <input readonly="readonly" type="text"  name="bankchangenum" id="text_slect_currency_code" value="<?=$myrow2['bankchangenum']?>" size="5" maxlength="10"/></div>  
 <div class="text-nav-1 "><div>银行账户名</div>
  <input readonly="readonly" type="text"  name="bankaccountname" id="text_slect_currency_code" value="<?=$myrow2['bankaccountname']?>" size="5" maxlength="10"/></div>
  <div class="text-nav-1 "><div>客户代码</div>
  <input readonly="readonly" type="text"  value="<?=$myrow2['customer_code']?>" size="5" maxlength="10"/></div>
  <div class="text-nav-1 "><div>客户名称</div>
  <input readonly="readonly" type="text"   value="<?=$myrow2['customer_name']?>" size="5" maxlength="10"/></div>
  <div class="text-nav-1 "><div>收款票日期</div>
  <input readonly="readonly" type="text"  value="<?=date('Y-m-d',$myrow2['transaction_date'])?>" size="5" maxlength="10"/></div>
 
  <div class="text-nav-1 "><div>币别</div>
  <input readonly="readonly" type="text"   value="<?=$myrow2['currency_code']?>" size="5" maxlength="10"/></div>
  
  <div class="text-nav-1 "><div>收款/退款类型</div>
  <input readonly="readonly" name="transaction_type" type="text"   value="<?=$myrow2['transaction_type']?>" size="5" maxlength="10"/></div>
  
  <div class="text-nav-1 "><div>收款金额</div>
  <input readonly="readonly" type="text"   value="<?=$myrow2['transaction_amount']?>" size="5" maxlength="10"/></div>
 <div class="text-nav-2 "><div>收款备注</div>
  <input readonly="readonly" type="text"   value="<?=$myrow2['narrative']?>" size="5" maxlength="10"/></div>
<div class="text-nav-1 "><div>状态</div>
  <input readonly="readonly" type="text"   value="<?=$myrow2['status']?>" size="5" maxlength="10"/></div>
 <div class="text-nav-1 "><div>建立日期</div>
  <input readonly="readonly" type="text"   value="<?=date('Y-m-d H:i:s',$myrow2['creation_date'])?>" size="5" maxlength="10"/></div>
   <div class="text-nav-1 "><div>建立人</div>
  <input readonly="readonly" type="text"   value="<?=$myrow2['created_by']?>" size="5" maxlength="10"/></div>
 </div>
  

</table>

<input type="hidden" name="PageOffset" value="1"/><br/>
<?php
if (isset($myrow2['transaction_num']) and $myrow2['transaction_num'] != '') {

	$sql ="select c.transaction_id,a.invoice_name,c.transaction_amount ,c.dis_amount,c.tax_amount,a.invoice_num, a.tax_code,a.invoice_type ,a.ar_invoice_type,a.invoice_date,c.narrative
				from fin_bank_transaction_lines_all c,ar_invoice_headers_all a
				where    c.ap_invoice_name=a.invoice_name
				and  c.transaction_num 	= '" .$_POST['transaction_num'] . "'";
	 // echo $sql;
	$result = DB_query($sql,$db);
    if (DB_num_rows($result)==0) {
		$line_count=DB_num_rows($result);
      //  unset($result);
        prnMsg(_('该请重新输入条件查询！') ,'error');
    }

?>
<input id="purchase_table_lastRow" name="purchase_table_lastRow" type=hidden value="">

  <div class="text-nav-table">
<table id="purchase_table" cellpadding="2" class="selection">
 
<tr id="list-top">
  <th  width="10">发票单号</th>
 <th  width="10" >发票号码</th>
 <th  width="10" >发票类型</th> 
 <th  width="10" >类型</th>  
 <th  width="10" >发票日期</th>  
  <th  width="10" ><font color="#1E90FF">本次收款金额</font></th> 
 <th  width="10" >备注</th>  
 <th  width="10" >税别</th>  
 
</tr>
<?php   
$i=1;
 
 while ($myrow = DB_fetch_array($result)  )  {
	
	 ?>
		 
<tr id="purchase_table_<?=$i?>" >
 	  

    <td><input type="text" readonly="readonly" name="invoice_name<?=$i?>"  id="invoice_name<?=$i?>" value="<?= $myrow['invoice_name'] ?>" size="12" maxlength="10"/></td> 
  <td><input type="text" readonly="readonly" name="invoice_num<?=$i?>"  id="text_check_amount<?=$i?>" value="<?= $myrow['invoice_num'] ?>" size="9" maxlength="10"/></td> 
   <td><input type="text" readonly="readonly" name="ar_invoice_type<?=$i?>" id="ar_invoice_type<?=$i?>" value="<?= $myrow['ar_invoice_type']?>" size="11" maxlength="10"/></td>
    <td><input type="text" readonly="readonly" name="invoice_type<?=$i?>" id="invoice_type<?=$i?>" value="<?= $myrow['invoice_type']?>" size="11" maxlength="10"/></td>
  <td><input type="text" readonly="readonly" name="invoice_date<?=$i?>"  value="<?= date('Y-m-d',$myrow['invoice_date']) ?>" size="9" maxlength="10"/></td> 
  <td><input type="text" readonly="readonly" name="transaction_amount<?=$i?>"   value="<?= $myrow['transaction_amount'] ?>" size="9" maxlength="10"/></td>
  
  <td><input type="text" readonly="readonly" name="narrative<?=$i?>"   value="<?= $myrow['narrative'] ?>" size="9" maxlength="10"/></td>
<td><input type="text" readonly="readonly" name="tax_code<?=$i?>" id="tax_code<?=$i?>" value="<?= $myrow['tax_code']?>"     size="11" maxlength="10"/></td>

   
</tr>
<?php 
 $i=$i+1;
  
 }
  ?>

   
	<td ><input type="hidden" name="flag" value="<?=$i-1?>" size="15" maxlength="45"/></td> 
</table></div>

<?php 
  if ( $myrow2['status']=='核准' ) {
  ?>

<div class="centre">
<input type="submit" name="Save" value="作废">
</div>
<?php
}
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
  
  function checktotal(){       
								   var  all_invoice_amount=0;  
								   var  all_dis_amount=0;     
                                for(var i=1 ; i < 50; i++){   
									if (document.getElementById("text_this_invoice_amount" + i)==null)  {
									p=0;
										}
									else {							 
		                            
								    var a=document.getElementById("text_this_invoice_amount"+i).value;
        							 var b=document.getElementById("text_this_invoice_dis_amount"+i).value;  
        							 var c=document.getElementById("text_wait_amount"+i).value;  
		 
     							          if(parseFloat(a)>parseFloat(c)){
      							               document.getElementById("Prompt").innerHTML="本次收款金额不可以大于待收款金额！！！！";
       							               document.getElementById("text_this_invoice_amount"+i).value="";
        							             document.getElementById("text_this_invoice_dis_amount"+i).focus();
       							          } else if(parseFloat(a)<=0 ){
       							              document.getElementById("Prompt").innerHTML="本次收款金额不可以小于0！！！！";
       							              document.getElementById("text_this_invoice_amount"+i).value="";
        							           document.getElementById("text_this_invoice_amount"+i).focus();
     							            }  else {
       							              document.getElementById("Prompt").innerHTML="";
     							            }

						  var  invoice_amount=0; 
						  var  dis_amount=0;
                          var invoice_amount=document.getElementById("text_this_invoice_amount"+i).value;  
                          var dis_amount=document.getElementById("text_this_invoice_dis_amount"+i).value;       if( dis_amount>0 ) {     
							   all_dis_amount=Number(all_dis_amount) + Number(dis_amount); 
								  
                           }                        								     
						  if( invoice_amount>0 ) {  
								   all_invoice_amount=Number(all_invoice_amount) + Number(invoice_amount);                            
                           }
						  
						 }}
		            document.getElementById("transaction_amount").value=all_invoice_amount; 
					document.getElementById("all_dis_amount").value=all_dis_amount; 
				
		 }   

function  check(s1){
	    var a=document.getElementById("text_this_invoice_amount"+s1).value;
        var b=document.getElementById("text_this_invoice_dis_amount"+s1).value;
        var c=document.getElementById("text_wait_amount"+s1).value;
        var d=parseFloat(a) + parseFloat(b);
		var checkamount=document.getElementById("text_check_amount"+s1).value;
		if (checkamount>0) {
      if(parseFloat(a)>parseFloat(c)){
            document.getElementById("Prompt").innerHTML="本次开票金额不可以大于待开票金额！！！！";
             document.getElementById("text_this_invoice_amount"+s1).value="";
             document.getElementById("text_this_invoice_amount"+s1).focus();
        } else if(parseFloat(b)>parseFloat(c)){
            document.getElementById("Prompt").innerHTML="本次免开票金额不可以大于待开票金额！！！！";
             document.getElementById("text_this_invoice_dis_amount"+s1).value="";
            document.getElementById("text_this_invoice_dis_amount"+s1).focus();
        }else if(parseFloat(d)>parseFloat(c)){
            document.getElementById("Prompt").innerHTML="开票金额+免开票金额不可以大于待开票金额！！！！";
             document.getElementById("text_this_invoice_dis_amount"+s1).value="";
             document.getElementById("text_this_invoice_dis_amount"+s1).focus();
        } else {
            document.getElementById("Prompt").innerHTML="";
        }
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
	function checkall(thisform){for(var i=0;i<thisform.elements.length;i++){if(thisform.elements[i].type=="checkbox"&&thisform.elements[i].checked==false&&thisform.elements[i].name!="selectall"){thisform.elements[i].checked=true;}else if(thisform.elements[i].type=="checkbox"&&thisform.elements[i].checked==true&&thisform.elements[i].name!="selectall"){thisform.elements[i].checked=false;}} }
</script>
</body>

</html>
<?
 
include('includes/footer.inc');
?>

