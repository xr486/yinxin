<?php

include('includes/session.inc');
$Title = _('供应商发票明细查询');
$ViewTopic= '供应商发票明细查询';
$BookMark = '供应商发票明细查询';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

unset($result);

if (isset($_GET['Updatepo_num']) ) {
$_POST['invoice_num']=$_GET['Updatepo_num'];
}
if (isset($_GET['vendor_code']) ) {
$_POST['vendor_code']=$_GET['vendor_code'];
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
<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="供应商发票明细查询" alt="供应商发票明细查询">供应商发票明细查询</p>
<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST"><input type="hidden" name="time"
value="<?=$time?>">
<div>
<div?php
	 

$sql ="SELECT a.currency_code,a.invoice_num,a.invoice_name,a.status, a.narrative, a.invoice_date,a.creation_date, a.invoice_amount, b.vendor_name,b.vendor_code,a.tax_amount,a.tax_code
FROM ap_invoice_headers_all a, vendors b
WHERE a.vendor_code = b.vendor_code
and a.vendor_code= '" .$_POST['vendor_code'] . "' 
AND invoice_name= '" .$_POST['invoice_num'] . "'";
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
	   $_POST['invoice_date']=$myrow['invoice_date'] ; 
	   $_POST['invoice_amount']=$myrow['invoice_amount'] ; 
	   $_POST['tax_amount']=$myrow['tax_amount'] ; 
	   $_POST['tax_code']=$myrow['tax_code'] ; 


 }
 
     
?>

<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
<table class="selection">

<div class="text-nav">
    <div class="text-nav-1">
  <div>发票号码:</div>
  <input type="text" readonly="readonly"   name="invoice_num"  value="<?=$_POST['invoice_num']?>" size="16" maxlength="25"/> </div>
  <div class="text-nav-1"><div>供应商代码:</div>
 <input type="text" readonly="readonly"  name="vendor_code" id="text_slect_vendor" value="<?=$_POST['vendor_code']?>" size="10" maxlength="25"/> </div>
 <div class="text-nav-1"><div>币别：</div>
  <input type="text" readonly="readonly"  name="currency_code" id="text_slect_currency_code" value="<?=$_POST['currency_code']?>" size="10" maxlength="25"/> </div>
  <div class="text-nav-1"><div>税别：</div>
   <input type="text" readonly="readonly"  name="tax_code" id="text_slect_currency_code" value="<?=$_POST['tax_code']?>" size="10" maxlength="25"/> </div>
<div class="text-nav-1">
  <div>供应商名称:</div>
   <input readonly="readonly" type="text" name="vendor_name" id="text_slect_name" value="<?=$_POST['vendor_name']?>" size="50" maxlength="250"/></div>
   <div class="text-nav-1"> <div>发票日期:</div>
   <input type="text" readonly="readonly"  name="invoice_date" maxlength="20" size="12"  value="<?=date('Y-m-d',$_POST['invoice_date'])?>"onfocus="WdatePicker() "></div>


 

 
   <div class="text-nav-1">
  <div>发票金额:</div>
   <input type="text" class="number" readonly="readonly"  maxlength="100" size="20" name="invoice_amount"  value="<?=$_POST['invoice_amount']?>" size="10" maxlength="30"/> </div>
   <div class="text-nav-1"><div>税金:</div>
   <input type="text" class="number" readonly="readonly"  maxlength="100" size="20" name="tax_amount"  value="<?=$_POST['tax_amount']?>" size="10" maxlength="30"/> </div>
  
   <div class="text-nav-1">
<div>付款备注：</div>
 <input type="text"  maxlength="200" size="50" name="Header_Remark"  value="<?=$_POST['Header_Remark']?>"/> </div>


</div>
</table>
<?php

$sql ="select invoice_line,a.po_num,a.check_amount,c.amount,c.dis_amount,a.note,a.po_all_amount,a.youhui_amount,a.po_invoice_amount
				from ap_invoice_lines_all c,po_headers_all a 
				where  c.po_num=a.po_num 
				and c.vendor_code= '" .$_POST['vendor_code'] . "' 
				and  c.invoice_name= '" .$_POST['invoice_num'] . "'";
    	 	 	  	 	 	
	//echo $sql;
	$result = DB_query($sql,$db);
    if (DB_num_rows($result)==0) {
        unset($result);
        prnMsg(_('没有需要付款的行，请重新输入条件查询！') ,'error');
    }
 echo '<br />
                    <table cellpadding="2" class="selection">';  

    echo '<tr> 	  <th  width="50" >' . _('行') . '</th>                 
					 
					<th width="150"  >' . _('采购单') . '</th> 
				 <th  width="100"  >' . _('订单总金额') . '</th>	
				 <th width="100"   >' . _('订单优惠金额') . '</th>		
				 <th width="100"   >' . _('订单应付金额') . '</th>	
				 <th  width="190"  >' . _('备注') . '</th>	
					<th  width="150"  >' . _('对账金额') . '</th>	
					  <th width="150"  >' . _('开票金额') . '</th> 
					  <th width="150"  >' . _('免开票金额') . '</th> 
				  
            </tr>'; 
$i=0;
 while ($myrow = DB_fetch_array($result))  {
	 $i=$i+1;
 echo ' 		<td>' . $myrow['invoice_line'] . '</td>
			    
				<td><a href="' . $RootPath . '/SearchPO2.php?Updatepo_num=' . $myrow['po_num'] .'" target="_blank" >' . $myrow['po_num']  . '</td>  
				<td>' . $myrow['po_all_amount'] . '</td>
			    <td>' . $myrow['youhui_amount'] . '</td>
			    <td>' . $myrow['po_invoice_amount'] . '</td>
			    <td>' . $myrow['note'] . '</td>
			    <td>' . $myrow['check_amount'] . '</td>
			    <td>' . $myrow['amount'] . '</td>
			    <td>' . $myrow['dis_amount'] . '</td>
				 
                ';?>
  <?php 
		 
           
          echo  '
            </tr>';
            $i++;
			   } //end loop through customers
        echo '</table>';
        echo '<input type="hidden" name="JustSelectedACustomer" value="Yes" />';
   

echo '<a name="end"></a> 
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
        $('#btn_slect_invoice<?=$i?>').dialog({
            title:'选择发票号码',
            width: '950px',
            height: 520,
            content:'url:SearchNoPaymentInvoice.php?fwValue=<?=$i?>&cat=<?=$_POST['vendor_code']?>',
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
            content:'url:BtnSearchAPVendor2.php?fwValue=&cat=buliao',
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

