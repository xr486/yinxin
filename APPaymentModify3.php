<?php

include('includes/session.inc');
$Title = _('供应商退款修改');
$ViewTopic= '供应商退款修改';
$BookMark = '供应商退款修改';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

if (isset($_GET['Updatepo_num']) ) {
$_POST['transaction_num']=$_GET['Updatepo_num'];
}



unset($result);

if (isset($_POST['Delete'])) 
{

	      $sql = "delete from fin_bank_transaction_lines_all 
                  where  transaction_num  ='".$_POST['transaction_num']."'   ";
          $result = DB_query($sql,$db);

		  $sql = "delete from fin_bank_transaction_headers_all 
                  where  transaction_num  ='".$_POST['transaction_num']."'   ";
          $result = DB_query($sql,$db);

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

 

          if ($_POST['this_invoice_amount'.$i]>$_POST['payment_amount'.$i] ) 
		 {
            $errorflag = 1;
            prnMsg($value.'退款金额'.$_POST['this_invoice_amount'.$i].'不可以大于已付款金额'.$_POST['payment_amount'.$i].',请确认！',error);
         }

		  
		  $all_amount = $all_amount + $_POST['this_invoice_amount'.$i] ; 
  
    }


  }
 
 if ($_POST['transaction_amount']<>$all_amount) 
  {
    $errorflag = 1;
   prnMsg($value.'退款总金额合计'.$all_amount.'不等于退款总金额'.$_POST['transaction_amount'].',请确认！',error);
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
                  where  transaction_num  ='".$_POST['transaction_num']."' 
				   and   po_num ='".$_POST['po_num'.$i]."' ";
          $result = DB_query($sql,$db);
		   
 
        }
      } 	
      if($_POST['tax_amount_all']=='')
		  {
            $_POST['tax_amount_all'] = 0;
          }
	  if($_POST['dis_amount']=='')
		  {
            $_POST['dis_amount'] = 0;
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
    echo "<script>location.href='index.php';</script>";
	}

        
    }


 
   
}

?>
 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>供应商退款修改</title>
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
<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="供应商退款修改" alt="供应商退款修改">供应商退款修改</p>
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
                                  pha.vendor_code,
                                    pha.narrative,
								pha.currency_code,
                                 pha.transaction_date,
                pha.creation_date,pha.created_by,pha.vendor_code,c.vendor_name,pha.status
	from fin_bank_transaction_headers_all pha, vendors c
            where pha.status='拒绝'
			and pha.transaction_type in ('AP退款')
			and pha.vendor_code=c.vendor_code
AND transaction_num= '" .$_POST['transaction_num'] . "'";
	//echo $sql;
	$result = DB_query($sql,$db);
    if (DB_num_rows($result)==0) {
        unset($result);
        prnMsg(_('没有预测，请重新输入条件查询！') ,'error');
	 }
 while ($myrow = DB_fetch_array($result))  {
        $_POST['vendor_code']=$myrow['vendor_code'] ;
       $_POST['vendor_name']=$myrow['vendor_name'] ;  
	   $_POST['currency_code']=$myrow['currency_code'] ; 
	   $_POST['invoice_date']=$myrow['transaction_date'] ; 
	   $_POST['bankchangenum']=$myrow['bankchangenum'] ; 
	   $_POST['transaction_num']=$myrow['transaction_num'] ; 
	   $_POST['bankaccountname']=$myrow['bankaccountname'] ;
	   $_POST['narrative']=$myrow['narrative'] ;
	   $_POST['transaction_amount']=$myrow['transaction_amount'] ; 
	   $_POST['transaction_type']=$myrow['transaction_type'] ; 
	   $_POST['dis_amount']=$myrow['dis_amount'] ; 
 }     
?>
<tr>
 <td bgcolor="#87CEFA">流水号:</td>
  <td><input type="text" readonly="readonly"   name="transaction_num"  value="<?=$_POST['transaction_num']?>" size="16" maxlength="25"/> </td>
  <td bgcolor="#87CEFA">退款/转账单号:</td>
  <td><input type="text" readonly="readonly"   name="bankchangenum"  value="<?=$_POST['bankchangenum']?>" size="25" maxlength="250"/> </td>
<td bgcolor="#87CEFA">银行账户名:</td>
  <td colspan="5"><input readonly="readonly" type="text" name="bankaccountname" id="text_slect_name" value="<?=$_POST['bankaccountname']?>" size="50" maxlength="250"/></td>

 
</tr>


<tr>
 <td bgcolor="#87CEFA">供应商代码:</td>
  <td><input type="text" readonly="readonly"  name="vendor_code" id="text_slect_vendor" value="<?=$_POST['vendor_code']?>" size="10" maxlength="25"/> </td>
  <td bgcolor="#87CEFA">供应商名称:</td>
  <td colspan="3"><input readonly="readonly" type="text" name="vendor_name" id="text_slect_name" value="<?=$_POST['vendor_name']?>" size="50" maxlength="250"/></td>
   <td bgcolor="#87CEFA">退款日期:</td>
  <td><input type="text" readonly="readonly"  name="invoice_date" maxlength="20" size="9"  value="<?=date('Y-m-d',$_POST['invoice_date'])?>"onfocus="WdatePicker() "></td>
  
  <td bgcolor="#87CEFA">币别：</td>
  <td><input type="text" readonly="readonly"  name="currency_code" id="text_slect_currency_code" value="<?=$_POST['currency_code']?>" size="6" maxlength="25"/> </td>
</tr>

<td bgcolor="#87CEFA">类型:</td>
  <td  ><input type="text"  maxlength="100" size="10" name="transaction_type"  value="<?=$_POST['transaction_type']?>"  /> </td>
   
  <td bgcolor="#87CEFA">退款金额:</td>
  <td  ><input type="text" class="number"   maxlength="100" size="10" id="transaction_amount" name="transaction_amount"  value="<?=$_POST['transaction_amount']?>"  /> </td>
  
  
 
<td bgcolor="#87CEFA">退款备注：</td>
<td colspan="5"><input type="text"  maxlength="200" size="50" name="Header_Remark"  value="<?=$_POST['Header_Remark']?>"/> </td>

</tr>
  

</table>

<input type="hidden" name="PageOffset" value="1"/><br/>
<?php
if (isset($_POST['vendor_name']) and $_POST['vendor_name'] != '') {

	$sql ="select a.po_num,a.check_amount,c.transaction_amount,c.dis_amount,a.po_all_amount,a.po_payment_amount,a.note,a.youhui_amount,a.payment_amount,a.dis_payment_amount,(check_amount-a.payment_amount-a.dis_payment_amount) wait_amount
				from fin_bank_transaction_lines_all c,po_headers_all a 
				where c.po_num=a.po_num 
				and  c.transaction_num 	= '" .$_POST['transaction_num'] . "'";
	// echo $sql;
	$result = DB_query($sql,$db);
    if (DB_num_rows($result)==0) {
		$line_count=DB_num_rows($result);
        unset($result);
        prnMsg(_('该供应商没有需要开票，请重新输入条件查询！') ,'error');
    }

?>
<input id="purchase_table_lastRow" name="purchase_table_lastRow" type=hidden value="">

<div class="centre"> 
                        <p id="Prompt" style="color: red;font-size: 20px"></p>
                    </div>

<table id="purchase_table" cellpadding="2" class="selection">
 
<tr id="list-top">
  <th bgcolor="#87CEFA" width="10">采购单</th>
  <th bgcolor="#87CEFA" width="10" >订单总金额</th>
  <th bgcolor="#87CEFA" width="10" >优惠金额</th> 
  <th bgcolor="#87CEFA" width="10" >订单应付金额</th> 
  <th bgcolor="#87CEFA" width="10">采购单备注</th>
  <th bgcolor="#87CEFA" width="10" >对账金额</th>
  <th bgcolor="#87CEFA" width="10" >已收款金额</th>
  <th bgcolor="#87CEFA" width="10" >已优惠金额</th>
  <th bgcolor="#87CEFA" width="10" ><font color="#1E90FF">本次退款金额</font></th>
  <th bgcolor="#87CEFA" width="18" align="center">选择</th>
 
</tr>
<?php   
$i=1;
 
 while ($myrow = DB_fetch_array($result)  )  {
	
	 ?>
		 
<tr id="purchase_table_<?=$i?>" >

  <td> <input type="text" readonly="readonly" name="po_num<?=$i?>" id="text_slect_receipt_num<?=$i?>" value="<?= $myrow['po_num'] ?>" size="13" maxlength="25"/> </td>

<td> <input type="text" readonly="readonly" name="po_all_amount<?=$i?>" id="text_slect_receipt_line<?=$i?>" value="<?= $myrow['po_all_amount'] ?>" size="10" maxlength="100"/></td>
  <td><input readonly="readonly" type="text" name="youhui_amount<?=$i?>" id="text_slect_remark<?=$i?>" value="<?= $myrow['youhui_amount'] ?>" size="9" maxlength="150"/></td>

<td> <input type="text" readonly="readonly" name="po_payment_amount<?=$i?>" id="text_slect_receipt_line<?=$i?>" value="<?= $myrow['po_payment_amount'] ?>" size="10" maxlength="100"/></td>
  <td><input type="text" readonly="readonly" name="note<?=$i?>"  id="text_slect_transaction_date<?=$i?>" value="<?= $myrow['note'] ?>" size="15" maxlength="100"/></td>
  <td><input type="text" readonly="readonly" name="check_amount<?=$i?>"  id="text_check_amount<?=$i?>" value="<?= $myrow['check_amount'] ?>" size="6" maxlength="10"/></td> 
  <td><input type="text" readonly="readonly" name="payment_amount<?=$i?>"  id="text_payment_amount<?=$i?>" value="<?= $myrow['payment_amount'] ?>" size="10" maxlength="10"/></td> 
  <td><input type="text" readonly="readonly" name="dis_payment_amount<?=$i?>"  id="text_dis_invoice_amount<?=$i?>" value="<?= $myrow['dis_payment_amount'] ?>" size="10" maxlength="10"/></td>
  
  <td><input type="text" class="number"  name="this_invoice_amount<?=$i?>" onkeyup="check(<?=$i?>)"  onblur="checktotal()" id="text_this_payment_amount<?=$i?>"    value="<?= $myrow['transaction_amount']?>"     size="12" maxlength="10"/></td>

  <td><input type="checkbox" size="5" name="status<?=$i?>" /></td>
 <td> 

  <input type="hidden"  name="po_line_id<?=$i?>"  value="<?= $myrow['po_line_id'] ?>" size="12" maxlength="10"/>
  
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
<input type="submit" name="Save" value="修改提交">
<input type="submit" name="Delete" value="删除单据">
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
									if (document.getElementById("text_this_payment_amount" + i)==null)  {
									p=0;
										}
									else {							 
		                            
								   var  invoice_amount=0; 
                                   var invoice_amount=document.getElementById("text_this_payment_amount"+i).value;                                								     
								   if( invoice_amount>0 )
								   {  
								   all_invoice_amount=Number(all_invoice_amount) + Number(invoice_amount);                                 
                                   }
								   

								    }}
		            document.getElementById("transaction_amount").value=all_invoice_amount; 
		 }
     

function  check(s1){
	    var a=document.getElementById("text_this_payment_amount"+s1).value;
        var b=document.getElementById("text_payment_amount"+s1).value; 
        var d=parseFloat(a) + parseFloat(b);
      if(parseFloat(a)>parseFloat(b)){
            document.getElementById("Prompt").innerHTML="本次退款金额不可以大于已付款金额！！！！";
            document.getElementById("text_this_payment_amount"+s1).value="";
            document.getElementById("text_this_payment_amount"+s1).focus();
        } else if(parseFloat(a) < 0 ){
            document.getElementById("Prompt").innerHTML="本次退款金额不可以小于0！！！！";
            document.getElementById("text_this_payment_dis_amount"+s1).value="";
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
        <?php for($i=1;$i<=50;$i++){?> 
        $('#btn_slect_po<?=$i?>').dialog({
            title:'选择未开票的采购入库单',
            width: '950px',
            height: 520,
            content:'url:SearchNoInvoicePo2.php?fwValue=<?=$i?>&cat=<?=$_POST['vendor_code']?>',
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

