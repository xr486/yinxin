<?php

include('includes/session.inc');
$Title = _('供应商发票审核');
$ViewTopic= '供应商发票审核';
$BookMark = '供应商发票审核';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

unset($result);

if (isset($_GET['Updatepo_num']) ) {
$_POST['invoice_name']=$_GET['Updatepo_num'];
}
if (isset($_GET['vendor_code']) ) {
$_POST['invoice_name']=$_GET['vendor_code'];
}


if (isset($_POST['UpdateStatus']) ) {
 
$time = time();
if (  $_POST['hangover'] =='Y'  )  {
	   prnMsg('其中一行开票金额超过了待开票金额', 'error');
	}  else {
	    $sql2="UPDATE ap_invoice_headers_all 
                    SET   	status= '核准' ,approve_remark='".$_POST['Header_Remark']."' 			 
					,last_update_date='" . $time. "'
                    ,last_updated_by='" . $_SESSION['UserID'] . "'
                    WHERE  invoice_name='".$_POST['invoice_name']."'  
                    "; 	
		$result = DB_query($sql2,$db);
//echo $sql2;
		

		prnMsg('发票'.$_POST['invoice_name'].'审核完成！',success);
    echo "<script>location.href='APInvoiceApproved.php';</script>";
	}
}



if (isset($_POST['RejectBack']) ) {
	$time = time();
      $sql2="UPDATE ap_invoice_headers_all 
                    SET   	status= '拒绝' 	,approve_remark='".$_POST['Header_Remark']."' 			 
					,last_update_date='" . $time. "'
                    ,last_updated_by='" . $_SESSION['UserID'] . "'
                    WHERE  invoice_name='".$_POST['invoice_name']."'  
                    "; 	
		$result = DB_query($sql2,$db);

		$sql3="select * from ap_invoice_lines_all  
                     WHERE  invoice_name='".$_POST['invoice_name']."'  
                    "; 	
		$result3 = DB_query($sql3,$db); 
		while ($myrow = DB_fetch_array($result3))  {

			 //echo $sql3;
			  $sqlso = "update  po_rcv_receipt_line
                    set invoice_amount     = invoice_amount     + '".$myrow['this_invoice_amount'.$i]."'
                       
                  where  receipt_num  ='".$myrow['receipt_num'.$i]."' 
				  and receipt_line  ='".$myrow['receipt_line'.$i]."'  ";
 
              $result = DB_query($sqlso,$db);
			 //echo $sql;
		}
//echo $sql2;

		
		prnMsg('发票'.$_POST['invoice_name'].'已拒绝！',success);
    echo "<script>location.href='APInvoiceApproved.php';</script>";

}
 	 	


?>
 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>预付款</title>
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
<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="供应商发票审核" alt="供应商发票审核">供应商发票审核</p>
<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST"><input type="hidden" name="time"
value="<?=$time?>">
<div>
<?php
	 

$sql ="SELECT a.invoice_name,a.currency_code,a.invoice_num, a.status, a.narrative, a.invoice_date,a.creation_date,a.created_by,  
a.invoice_amount, b.vendor_name,b.vendor_code,a.tax_amount,a.dis_amount,a.ap_invoice_type,a.tax_code,a.narrative
FROM ap_invoice_headers_all a, vendors b
WHERE a.vendor_code = b.vendor_code 
and a.status='建立'
AND invoice_name= '" .$_POST['invoice_name'] . "'";
	 //echo $sql;
	$result = DB_query($sql,$db);
    if (DB_num_rows($result)==0) {
        unset($result);
        prnMsg(_('没有预测，请重新输入条件查询！') ,'error');
	 }
 while ($myrow = DB_fetch_array($result))  {
       $_POST['vendor_code']=$myrow['vendor_code'] ;
       $_POST['vendor_name']=$myrow['vendor_name'] ;   
	   $_POST['invoice_name']=$myrow['invoice_name'] ; 
	   $_POST['invoice_num']=$myrow['invoice_num'] ; 
	   $_POST['currency_code']=$myrow['currency_code'] ; 
	   $_POST['invoice_date']=$myrow['invoice_date'] ; 
	   $_POST['invoice_amount']=$myrow['invoice_amount'] ; 
	   $_POST['dis_amount']=$myrow['dis_amount'] ; 
	   $_POST['tax_amount']=$myrow['tax_amount'] ; 
	   $_POST['ap_invoice_type']=$myrow['ap_invoice_type'] ; 
	   $_POST['tax_code']=$myrow['tax_code'] ; 
	   $_POST['creation_date']=date('Y-m-d H:i:s',$myrow['creation_date']) ; 
	   $_POST['created_by']=$myrow['created_by'] ; 
       $_POST['narrative']=$myrow['narrative'] ; 


 }
 
     
?>

<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
<table class="selection">

<div class="text-nav">
<div class="text-nav-1"><div>发票序号:</div>
  <input type="text" readonly="readonly"   name="invoice_name"  value="<?=$_POST['invoice_name']?>" size="16" maxlength="25"/> </div>
<div class="text-nav-1"><div>发票号码:</div>
  <input type="text" readonly="readonly"   name="invoice_num"  value="<?=$_POST['invoice_num']?>" size="16" maxlength="25"/> </div>
  <div class="text-nav-1"><div>供应商代码:</div>
 <input type="text" readonly="readonly"  name="vendor_code" id="text_slect_vendor" value="<?=$_POST['vendor_code']?>" size="10" maxlength="25"/> </div>
 <div class="text-nav-1"><div>供应商名称:</div>
<input readonly="readonly" type="text" name="vendor_name" id="text_slect_name" value="<?=$_POST['vendor_name']?>" size="50" maxlength="250"/></div>

<div class="text-nav-1"><div>发票金额:</div>
    <input type="text" class="number" readonly="readonly"   name="invoice_amount"  value="<?=$_POST['invoice_amount']?>" size="10" maxlength="30"/> </div>
 <div class="text-nav-1"><div>税金:</div>
  <input type="text" class="number" readonly="readonly"  name="tax_amount"  value="<?=$_POST['tax_amount']?>" size="7" maxlength="30"/> </div>
 <div class="text-nav-1"><div>币别：</div>
  <input type="text" readonly="readonly"  name="currency_code" id="text_slect_currency_code" value="<?=$_POST['currency_code']?>" size="10" maxlength="25"/> </div>
  <div class="text-nav-1"><div>发票日期:</div>
  <input type="text" readonly="readonly"  name="invoice_date" maxlength="20" size="12"  value="<?=date('Y-m-d',$_POST['invoice_date'])?>"onfocus="WdatePicker() "></div>
  
  <div class="text-nav-1"><div>税别：</div>
  <input type="text" readonly="readonly"  name="tax_code" id="text_slect_currency_code" value="<?=$_POST['tax_code']?>" size="10" maxlength="25"/> </div>
 
  <div class="text-nav-1"><div>发票类型:</div>
    <input type="text  readonly="readonly"  name="ap_invoice_type"  value="<?=$_POST['ap_invoice_type']?>" size="7" maxlength="30"/> </div>  <div class="text-nav-1"><div>发票备注:</div>
    <input type="text"   readonly="readonly"  name="narrative"  value="<?=$_POST['narrative']?>" size="7" maxlength="30"/> </div>
  <div class="text-nav-1"><div>建立日期:</div>
    <input type="text  readonly="readonly"  name="creation_date"  value="<?=$_POST['creation_date']?>" size="7" maxlength="30"/> </div> <div class="text-nav-1"><div>建立人员:</div>
    <input type="text"  readonly="readonly"  name="created_by"  value="<?=$_POST['created_by']?>" size="7" maxlength="30"/> </div>
    <div class="text-nav-2"><div>审核备注：</div>
    <input type="text"  maxlength="200" size="50" name="Header_Remark"  value="<?=$_POST['Header_Remark']?>"/> </div>

</div>

</table>
<?php
 
$sql ="select invoice_line,a.po_num,b.po_line,c.receipt_num,c.receipt_line,c.amount,a.stockid,sf.item_no,sf.item_name
				from ap_invoice_lines_all c,po_lines_all a, po_rcv_receipt_line b,sf_item_no sf
				where  c.po_num=a.po_num and c.receipt_line=b.receipt_line and c.receipt_num=b.receipt_num
				and a.line=b.po_line and sf.item_no=a.stockid
				and  c.invoice_name= '" .$_POST['invoice_name'] . "'";
    	 	 	  	 	 	
	 //echo $sql;
	$result = DB_query($sql,$db);
    if (DB_num_rows($result)==0) {
        unset($result);
        prnMsg(_('没有需要付款的行，请重新输入条件查询！') ,'error');
    }
 echo '<br /><div class="text-nav-table"> 
                    <table cellpadding="2" class="selection">';  

    echo '<tr> 	  <th   >' . _('行') . '</th>                 
					<th   width="100" >' . _('采购单') . '</th>
					<th    >' . _('采购单行') . '</th>
					<th   width="100" >' . _('收料单') . '</th>
					<th    >' . _('收料单行') . '</th>
					<th   width="100" >' . _('料号') . '</th>
					<th   width="100" >' . _('料号名称') . '</th> 
					  <th  >' . _('本次开票金额') . '</th> 
					<th width="100" >' . _('备注') . '</th>
				  
            </tr>'; 
$i=0;
 while ($myrow = DB_fetch_array($result))  {
	 $i=$i+1;
	  
	  $invoice_amount2=$myrow['po_all_amount']-$myrow['invoice_amount'];
	  echo ' 		<td>' . $myrow['invoice_line'] . '</td>
			    <td>' . $myrow['po_num']  . '</td>
			    <td>' . $myrow['po_line']  . '</td>
				<td>' . $myrow['receipt_num']  . '</td>
				<td>' . $myrow['receipt_line']  . '</td>
				<td>' . $myrow['stockid']  . '</td>
				<td>' . $myrow['item_name']  . '</td>
			 
			    <td>' . $myrow['amount'] . '</td>
				<td>' . $myrow['remark']  . '</td> ';

	  
				
				
				
				?>
  <?php 
		 
           
          echo  '
            </tr>';
            $i++;
			   } //end loop through customers
        echo '</table></div>';
        echo '<input type="hidden" name="JustSelectedACustomer" value="Yes" />';
   

echo '<a name="end"></a><div class="centre"><input type="submit" name="UpdateStatus"   value="核准" />
<input type="submit" name="RejectBack"   value="拒绝" />

</div>  
  ';
 

  ?>


<input type="hidden" name="PageOffset" value="1"/><br/>

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
        
</script>
</body>

</html>
<?
include('includes/footer.inc');
?>

