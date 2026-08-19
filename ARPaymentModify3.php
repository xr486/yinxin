<?php

include('includes/session.inc');
$Title = _('客户退款修改');
$ViewTopic= '客户退款修改';
$BookMark = '客户退款修改';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

if (isset($_GET['Updateorder_number']) ) {
$_POST['transaction_num']=$_GET['Updateorder_number'];
}



unset($result);
 
if (isset($_POST['Delete'])) 
{     $sql = "delete from  fin_bank_transaction_lines_all 
                  where  transaction_num  ='".$_POST['transaction_num']."'  ";
          $result = DB_query($sql,$db);

  	  $sql = "delete from fin_bank_transaction_headers_all  
                  where  transaction_num  ='".$_POST['transaction_num']."'   ";
          $result = DB_query($sql,$db);

          
    DB_Txn_Commit($db);
 
    prnMsg('发票'.$_POST['invoice_num'].'删除完成！',success);
    echo "<script>location.href='ARInvoiceModify.php';</script>";
	 

}

if (isset($_POST['Save'])) 
{
  $errorflag = 0;
  $all_amount = 0;
  $all_invoice_dis_amount = 0;
  $k =0;
   for ($i=1; $i<=$_POST['flag'];$i++)
  {
    if($_POST['status'.$i]<>'')
	{   
     
          if ($_POST['this_invoice_amount'.$i]=='') 
		  {
            $errorflag = 1;
            prnMsg($value.'退款金额未填写,请确认！',error);
          }
		  if ($_POST['this_invoice_amount'.$i]<0 ) 
		  {
            $errorflag = 1;
            prnMsg($value.'退款金额不可以小于0,请确认！',error);
          }

 
          if ($_POST['payment_amount'.$i]=='') 
		 {
            $errorflag = 1;
            prnMsg($value.'待立账金额为空,请确认！',error);
         }

		 

          if ($_POST['payment_amount'.$i] < $_POST['this_invoice_amount'.$i]   ) 
		  {
            $errorflag = 1;
            prnMsg($value.'退款金额不可以大于已收款金额,请确认！',error);
          }

		  $all_amount = $all_amount + $_POST['this_invoice_amount'.$i] ;
		  $all_invoice_dis_amount = $all_invoice_dis_amount + $_POST['this_invoice_dis_amount'.$i];
  
    }


  }
 
 if ($_POST['transaction_amount']<>$all_amount) 
  {
    $errorflag = 1;
   prnMsg($value.'立账金额合计'.$all_amount.'不等于发票总金额'.$_POST['transaction_amount'].',请确认！',error);
  }
   if ($_POST['dis_amount']<>$all_invoice_dis_amount) 
  {
    $errorflag = 1;
   prnMsg($value.'免开票额合计'.$all_invoice_dis_amount.'不等于免开票总金额'.$_POST['dis_amount'].',请确认！',error);
  }


  if ($errorflag == 0) 
  {
    $Delivery_date = strtotime($_POST['Delivery_date']);
    DB_Txn_Begin($db);
    $time = time();
    $delivery_amount = 0;
    $k =0;
   for ($i=1; $i<=$_POST['flag'];$i++)
	{
       $k = $k +1; 
	   if($_POST['status'.$i]<>'')
		   {
         
          if($_POST['this_invoice_amount'.$i]=='')
		  {
            $_POST['this_invoice_amount'.$i] = '0';
            $bumishu[$i] = 0;
          }  
          if($_POST['this_invoice_dis_amount'.$i]=='')
		  {
            $_POST['this_invoice_dis_amount'.$i] = 0;
          }
 
    
          $sql = "update  fin_bank_transaction_lines_all
                    set transaction_amount     =  '".$_POST['this_invoice_amount'.$i]."'
                       ,dis_amount =  '".$_POST['this_invoice_dis_amount'.$i]."'
                  where  transaction_num  ='".$_POST['transaction_num']."' 
				   and   transaction_id ='".$_POST['transaction_id'.$i]."' ";
          $result = DB_query($sql,$db);
		   
 
        }
      } 	
      if($_POST['dis_amount']=='')
		  {
            $_POST['dis_amount'] = 0;
          }
		  if($_POST['tax_amount_all']=='')
		  {
            $_POST['tax_amount_all'] = 0;
          }
	  $sql = "update  fin_bank_transaction_headers_all
                    set transaction_amount     = '".$_POST['transaction_amount']."'
                       ,dis_amount = '".$_POST['dis_amount']."'
					   ,tax_amount = '".$_POST['tax_amount_all']."'
					   ,narrative = '".$_POST['Header_Remark']."'
					   ,status='建立'
                  where  transaction_num  ='".$_POST['transaction_num']."'   ";
          $result = DB_query($sql,$db);

          
    DB_Txn_Commit($db);
	if ($k>0) {
    prnMsg('发票'.$_POST['transaction_num'].'修改完成！',success);
    echo "<script>location.href='ARPaymentModify.php';</script>";
	}

        
    }


 
   
}

?>
 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>客户退款修改</title>
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
<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="客户退款修改" alt="客户退款修改">客户退款修改</p>
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
            where pha.status='拒绝'
			and pha.transaction_type in ('AR收款','AR退款')
			and pha.customer_code=c.customer_code
AND transaction_num= '" .$_POST['transaction_num'] . "'";
	//echo $sql;
	$result = DB_query($sql,$db);
    if (DB_num_rows($result)==0) {
        unset($result);
        prnMsg(_('没有预测，请重新输入条件查询！') ,'error');
	 }
 while ($myrow = DB_fetch_array($result))  {
        $_POST['customer_code']=$myrow['customer_code'] ;
       $_POST['customer_name']=$myrow['customer_name'] ;  
	   $_POST['currency_code']=$myrow['currency_code'] ; 
	   $_POST['invoice_date']=$myrow['transaction_date'] ; 
	   $_POST['bankchangenum']=$myrow['bankchangenum'] ; 
	   $_POST['transaction_num']=$myrow['transaction_num'] ; 
	   $_POST['bankaccountname']=$myrow['bankaccountname'] ;
	   $_POST['narrative']=$myrow['narrative'] ;
	   $_POST['transaction_amount']=$myrow['transaction_amount'] ; 
	   $_POST['transaction_type']=$myrow['transaction_type'] ; 
	   $_POST['dis_amount']=$myrow['dis_amount'] ; 
	   $_POST['created_by']=$myrow['created_by'] ; 
	   $_POST['creation_date']=$myrow['creation_date'] ; 
 }     
?>
<tr>
 <td bgcolor="#87CEFA">流水号:</td>
  <td><input type="text" readonly="readonly"   name="transaction_num"  value="<?=$_POST['transaction_num']?>" size="16" maxlength="25"/> </td>
  <td bgcolor="#87CEFA">收款/转账单号:</td>
  <td><input type="text" readonly="readonly"   name="bankchangenum"  value="<?=$_POST['bankchangenum']?>" size="25" maxlength="250"/> </td>
<td bgcolor="#87CEFA">银行账户名:</td>
  <td colspan="5"><input readonly="readonly" type="text" name="bankaccountname" id="text_slect_name" value="<?=$_POST['bankaccountname']?>" size="50" maxlength="250"/></td>

 
</tr>


<tr>
 <td bgcolor="#87CEFA">客户代码:</td>
  <td><input type="text" readonly="readonly"  name="customer_code" id="text_slect_vendor" value="<?=$_POST['customer_code']?>" size="10" maxlength="25"/> </td>
  <td bgcolor="#87CEFA">客户名称:</td>
  <td colspan="3"><input readonly="readonly" type="text" name="customer_name" id="text_slect_name" value="<?=$_POST['customer_name']?>" size="50" maxlength="250"/></td>
   <td bgcolor="#87CEFA">退款日期:</td>
  <td><input type="text" readonly="readonly"  name="invoice_date" maxlength="20" size="9"  value="<?=date('Y-m-d',$_POST['invoice_date'])?>"onfocus="WdatePicker() "></td>
  
  <td bgcolor="#87CEFA">币别：</td>
  <td><input type="text" readonly="readonly"  name="currency_code" id="text_slect_currency_code" value="<?=$_POST['currency_code']?>" size="6" maxlength="25"/> </td>
</tr>

<td bgcolor="#87CEFA">类型:</td>
  <td  ><input type="text"  maxlength="100" size="10" name="transaction_type"  value="<?=$_POST['transaction_type']?>"  /> </td>
   
  <td bgcolor="#87CEFA"> 退款金额: </td>
  <td  ><input type="text" class="number"   maxlength="100" size="10" name="transaction_amount" id="transaction_amount" value="<?=$_POST['transaction_amount']?>"  /> </td>
     
 
<td bgcolor="#87CEFA">退款备注：</td>
<td colspan="5"><input type="text"  maxlength="200" size="50" name="Header_Remark"  value="<?=$_POST['Header_Remark']?>"/> </td>

</tr>
<tr>
  <td bgcolor="#87CEFA">单据建立人:</td>
   <td><input type="text" readonly="readonly"  name="created_by" id="created_by" value="<?=$_POST['created_by']?>" size="6" maxlength="25"/> </td>
  
  <td bgcolor="#87CEFA">单据建立时间:</td>
  <td><input type="text" readonly="readonly"  name="creation_date" maxlength="20" size="18"  value="<?=date('Y-m-d H:i:s',$_POST['creation_date'])?>" ></td>
 
</tr>
</table>

<input type="hidden" name="PageOffset" value="1"/><br/>
<?php
if (isset($_POST['customer_name']) and $_POST['customer_name'] != '') {

	$sql ="select c.transaction_id,a.order_number,a.check_amount,c.transaction_amount,c.dis_amount,a.payment_amount,a.dis_payment_amount,(a.check_amount-a.payment_amount-a.dis_payment_amount) wait_amount,a.order_all_amount,a.creation_date,a.yewu
				from fin_bank_transaction_lines_all c, so_headers_all a
				where c.so_num=a.order_number
				and  c.transaction_num 	= '" .$_POST['transaction_num'] . "'";
	// echo $sql;
	$result = DB_query($sql,$db);
    if (DB_num_rows($result)==0) {
		$line_count=DB_num_rows($result);
        unset($result);
        prnMsg(_('该客户没有付款资料，请重新输入条件查询！') ,'error');
    }

?>
<input id="purchase_table_lastRow" name="purchase_table_lastRow" type=hidden value="">

<div class="centre"> 
                        <p id="Prompt" style="color: red;font-size: 20px"></p>
                    </div>

<table id="purchase_table" cellpadding="2" class="selection">
 
<tr id="list-top">
  <th bgcolor="#87CEFA" width="10">业务订单</th>
  <th bgcolor="#87CEFA" width="10" >订单日期</th> 
  <th bgcolor="#87CEFA" width="10" >业务</th> 
  <th bgcolor="#87CEFA" width="10" >订单金额</th> 
  <th bgcolor="#87CEFA" width="10" >对账金额</th>
  <th bgcolor="#87CEFA" width="10" >已收款金额</th> 
  <th bgcolor="#87CEFA" width="10" ><font color="red">本次退款金额</font></th> 
  <th bgcolor="#87CEFA" width="25" align="center">选择</th>
 
</tr>
<?php   
$i=1;
 
 while ($myrow = DB_fetch_array($result)  )  {
	
	 ?>
		 
<tr id="purchase_table_<?=$i?>" >

  <td> <input type="text" readonly="readonly" name="order_number<?=$i?>" id="text_slect_receipt_num<?=$i?>" value="<?= $myrow['order_number'] ?>" size="15" maxlength="25"/> </td>
<td> <input type="text" readonly="readonly" name="creation_date<?=$i?>"  value="<?= date('Y-m-d',$myrow['creation_date']) ?>" size="9" maxlength="10"/></td>
<td> <input type="text" readonly="readonly" name="yewu<?=$i?>"  value="<?= $myrow['yewu'] ?>" size="9" maxlength="10"/></td>
  <td> <input type="text" readonly="readonly" name="order_all_amount<?=$i?>" id="order_all_amount<?=$i?>" value="<?= $myrow['order_all_amount'] ?>" size="9" maxlength="10"/></td>
  
 
  <td><input type="text" readonly="readonly" name="check_amount<?=$i?>"  id="text_check_amount<?=$i?>" value="<?= $myrow['check_amount'] ?>" size="9" maxlength="10"/></td> 
  <td><input type="text" readonly="readonly" name="payment_amount<?=$i?>"  id="text_invoice_amount<?=$i?>" value="<?= $myrow['payment_amount'] ?>" size="9" maxlength="10"/></td> 
 
  <td><input type="text" class="number"  name="this_invoice_amount<?=$i?>"   onblur="checktotal()"  id="text_this_invoice_amount<?=$i?>"    value="<?=  $myrow['transaction_amount']?>"     size="9" maxlength="10"/></td>

   
  <td><input type="checkbox" name="status<?=$i?>" checked /></td>
 <td> 

  <input type="hidden"  name="order_line_id<?=$i?>"  value="<?= $myrow['order_line_id'] ?>" size="8" maxlength="10"/>
  <input type="hidden"  name="wait_amount<?=$i?>"  id="text_wait_amount<?=$i?>"  value="<?= $myrow['wait_amount'] ?>" size="8" maxlength="10"/>
  <input type="hidden"  name="transaction_id<?=$i?>"  id="transaction_id<?=$i?>"  value="<?= $myrow['transaction_id'] ?>" size="8" maxlength="10"/>
  </td>
</tr>
<?php 
 $i=$i+1;
  
 }
  ?>

  	<tr><td colspan="11"><p><input type="checkbox" name="selectall" onclick="checkall(this.form);"/>全选/取消全选</p></td></tr>
	<td ><input type="hidden" name="flag" value="<?=$i-1?>" size="15" maxlength="45"/></td> 
</table>

<div class="centre">
<input type="submit" name="Save" value="确认修改">
<input type="submit" name="Delete" value="删除确认">
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
     
	   function checktotal(){       
								   var  all_invoice_amount=0;           
                                for(var i=1 ; i < 50; i++){   
									if (document.getElementById("text_this_invoice_amount" + i)==null)  {
									p=0;
										}
									else {							 
		                            
								         var a=document.getElementById("text_this_invoice_amount"+i).value;
        							     var b=document.getElementById("text_invoice_amount"+i).value;   
		 
     							          if(parseFloat(a)>parseFloat(b)){
      							               document.getElementById("Prompt").innerHTML="本次退款金额不可以大于已收款金额！！！！";
       							               document.getElementById("text_this_invoice_amount"+i).value="";
        							             document.getElementById("text_this_invoice_amount"+i).focus();
       							          } else if(parseFloat(a)<=0 ){
       							              document.getElementById("Prompt").innerHTML="本次退款金额不可以小于0！！！！";
       							              document.getElementById("text_this_invoice_amount"+i).value="";
        							             document.getElementById("text_this_invoice_amount"+i).focus();
     							            }  else {
       							              document.getElementById("Prompt").innerHTML="";
     							            }

								   var  invoice_amount=0; 
                                   var invoice_amount=document.getElementById("text_this_invoice_amount"+i).value;                                								     
								   if( invoice_amount>0 )
								   {  
								   all_invoice_amount=Number(all_invoice_amount) + Number(invoice_amount);                                 
                                   }
								   

								    }}
		            document.getElementById("transaction_amount").value=all_invoice_amount; 
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

