<?php

include('includes/session.inc');
$Title = _('客户收款明细查询');
$ViewTopic= '客户收款明细查询';
$BookMark = '客户收款明细查询';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

unset($result);

if (isset($_GET['Updatepo_num']) ) {
$_POST['transaction_num']=$_GET['Updatepo_num'];
}



?>
 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>预收款</title>
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
<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="客户收款明细查询" alt="客户收款明细查询">客户收款明细查询</p>
<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST"><input type="hidden" name="time"
value="<?=$time?>">
<div>
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
            where  pha.transaction_type='AR收款'
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
	   $_POST['dis_amount']=$myrow['dis_amount'] ; 


 }
 
     
?>

<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
<table class="selection">

<tr>
 <td>流水号:</td>
  <td><input type="text" readonly="readonly"   name="transaction_num"  value="<?=$_POST['transaction_num']?>" size="16" maxlength="25"/> </td>
  <td>收款/转账单号:</td>
  <td><input type="text" readonly="readonly"   name="bankchangenum"  value="<?=$_POST['bankchangenum']?>" size="16" maxlength="25"/> </td>
 <td>客户代码:</td>
  <td><input type="text" readonly="readonly"  name="customer_code" id="text_slect_vendor" value="<?=$_POST['customer_code']?>" size="10" maxlength="25"/> </td>
  <td>币别：</td>
  <td><input type="text" readonly="readonly"  name="currency_code" id="text_slect_currency_code" value="<?=$_POST['currency_code']?>" size="10" maxlength="25"/> </td>
 
</tr>

<tr>
  <td>客户名称:</td>
  <td colspan="3"><input readonly="readonly" type="text" name="customer_name" id="text_slect_name" value="<?=$_POST['customer_name']?>" size="50" maxlength="250"/></td>
   <td>收款日期:</td>
  <td><input type="text" readonly="readonly"  name="invoice_date" maxlength="20" size="12"  value="<?=date('Y-m-d',$_POST['invoice_date'])?>"onfocus="WdatePicker() "></td>
</tr>


   
  <td>收款金额:</td>
  <td  ><input type="text" class="number" readonly="readonly"  maxlength="100" size="20" name="transaction_amount"  value="<?=$_POST['transaction_amount']?>" size="10" maxlength="30"/> </td>
    <td>优惠金额:</td>
  <td  ><input type="text" class="number" readonly="readonly"  maxlength="100" size="20" name="dis_amount"  value="<?=$_POST['dis_amount']?>" size="10" maxlength="30"/> </td>
  
 
<td>收款备注：</td>
<td colspan="4"><input type="text"  maxlength="200" size="50" name="Header_Remark"  value="<?=$_POST['Header_Remark']?>"/> </td>

</tr>

</table>
<?php

$sql ="select  a.order_all_amount,a.order_number, a.check_amount,c.transaction_amount,c.dis_amount 
				from fin_bank_transaction_lines_all c,so_headers_all a 
				where so_num=a.order_number and c.transaction_num 	= '" .$_POST['transaction_num'] . "'";
    	 	 	  	 	  	 	 	
	// echo $sql;
	$result = DB_query($sql,$db);
    if (DB_num_rows($result)==0) {
        unset($result);
        prnMsg(_('没有需要收款的行，请重新输入条件查询！') ,'error');
    }
 echo '<br />
                    <table cellpadding="2" class="selection">';  

    echo '<tr> 	         
					 
					<th width="100"  >' . _('业务订单') . '</th>
					   <th  >' . _('订单金额') . '</th>			 
					<th   >' . _('对账金额') . '</th>	
					  <th  >' . _('收款金额') . '</th> 
					  <th  >' . _('优惠金额') . '</th> 
				  
            </tr>'; 
$i=0;
 while ($myrow = DB_fetch_array($result))  {
	 $i=$i+1;
 echo ' 		<td><a href="' . $RootPath . '/SearchSO2.php?Updateorder_number=' . $myrow['order_number'] .'" target="_blank" >' . $myrow['order_number']  . '</td> 
			    <td>' . $myrow['order_all_amount'] . '</td>
			    <td>' . $myrow['check_amount'] . '</td>
			    <td>' . $myrow['transaction_amount'] . '</td>
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
            content:'url:SearchNoPaymentInvoice.php?fwValue=<?=$i?>&cat=<?=$_POST['customer_code']?>',
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
            title:'选择客户',
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

