<?php

include('includes/session.inc');
$Title = _('客户收款审核');
$ViewTopic= '客户收款审核';
$BookMark = '客户收款审核';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

unset($result);

if (isset($_GET['Updateorder_number']) ) {
$_POST['transaction_num']=$_GET['Updateorder_number'];
}


if (isset($_POST['UpdateStatus']) ) {

    $time = time();
    
			$sql2="UPDATE fin_bank_transaction_headers_all 
                    SET   	status= '核准' 						 
					,last_update_date='" . $time. "'
                    ,last_updated_by='" . $_SESSION['UserID'] . "'
                    WHERE  transaction_num='".$_POST['transaction_num']."' 
                    "; 	
		$result = DB_query($sql2,$db);
           
		

		


		prnMsg('收款'.$_POST['invoice_num'].'审核完成！',success);
    echo "<script>location.href='ARPaymentApproved.php';</script>";
	  
}



if (isset($_POST['RejectBack']) ) {
	$time = time();
      $sql2="UPDATE fin_bank_transaction_headers_all 
                    SET   	status= '拒绝' 						 
					,last_update_date='" . $time. "'
                    ,last_updated_by='" . $_SESSION['UserID'] . "'
                    WHERE  transaction_num='".$_POST['transaction_num']."' 
                    "; 	
		$result = DB_query($sql2,$db);

		$sql3="select * from fin_bank_transaction_lines_all  
                     WHERE  transaction_num='".$_POST['transaction_num']."' 
                    "; 	
		$result3 = DB_query($sql3,$db); 
		while ($myrow = DB_fetch_array($result3))  {
            
			   $sql = "update  ar_invoice_headers_all
                    set payment_amount     = payment_amount  - '". $myrow['transaction_amount'] ."' 
                  where  invoice_name  ='".$myrow['ap_invoice_name']."'  ";
              $result = DB_query($sql,$db);
         // echo $sql;
          
        $sql1="UPDATE fin_bank_alls 
        SET   bank_onhand = bank_onhand - '" . $myrow['transaction_amount']. "'				
        ,last_update_date='" . $time. "'
        ,last_updated_by='" . $_SESSION['UserID'] . "'
        WHERE  bankaccountname='".$myrow['bankaccountname']."'
        "; 	
$result1 = DB_query($sql1,$db) ;
		}  

      
		prnMsg('收款'.$_POST['invoice_num'].'已拒绝！',success);


		
    echo "<script>location.href='ARPaymentApproved.php';</script>";

}
 	 	


?>
 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>客户收款审核</title>
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
<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="客户收款审核" alt="客户收款审核">客户收款审核</p>
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
            where pha.status='建立'
			and pha.transaction_type in ('AR收款','AR预收款','AR退款')
			and pha.customer_code=c.customer_code
AND transaction_num= '" .$_POST['transaction_num'] . "'";
	 //echo $sql;
	$result = DB_query($sql,$db);
    if (DB_num_rows($result)==0) {
        unset($result);
        prnMsg(_('没有预测，请重新输入条件查询！') ,'error');
	 }
 $myrow2 = DB_fetch_array($result);
     
 
     
?>

<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
<table class="selection">
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
  <input readonly="readonly" type="text"   value="<?=$myrow2['transaction_type']?>" size="5" maxlength="10"/></div>
  
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
<?php

$sql ="select e.invoice_name,e.invoice_num,c.transaction_amount,e.invoice_amount,e.payment_amount,e.invoice_type,e.ar_invoice_type,e.invoice_date,c.narrative
				from fin_bank_transaction_lines_all c,ar_invoice_headers_all e
				where c.ap_invoice_name=e.invoice_name 
				and  c.transaction_num 	= '" .$myrow2['transaction_num'] . "'";
    	 	 	  	 	  	 	 	
	// echo $sql; 	
	$result = DB_query($sql,$db);
    if (DB_num_rows($result)==0) {
        unset($result);
        prnMsg(_('没有需要收款的行，请重新输入条件查询！') ,'error');
    }
 echo ' <div class="text-nav-table">
                    <table cellpadding="2" class="selection">';  

    echo '<tr> 	     <th bgcolor="#87CEFA" width="100"  >' . _('发票单号') . '</th>  	
					<th bgcolor="#87CEFA"   >' . _('发票号码') . '</th>
					<th bgcolor="#87CEFA"   >' . _('发票类型') . '</th>		 
					<th bgcolor="#87CEFA"   >' . _('类型') . '</th>	 	 
					<th bgcolor="#87CEFA"   >' . _('发票日期') . '</th>	
					<th bgcolor="#87CEFA"   >' . _('发票金额') . '</th>	
					<th bgcolor="#87CEFA"   >' . _('已收款金额') . '</th>		
					<th bgcolor="#87CEFA"   >' . _('待收款金额') . '</th>	
					  <th bgcolor="#87CEFA"  >' . _('本次收款金额') . '</th> 
					  <th bgcolor="#87CEFA"  >' . _('备注') . '</th>
				  
            </tr>'; 
$i=0;
 while ($myrow = DB_fetch_array($result))  {
	 $i=$i+1;
	
			echo ' <input type="hidden"  maxlength="100" size="10"  name="hangover"  value="Y" size="10" maxlength="30"/>';

    echo '   	<td>' . $myrow['invoice_name']  . '</td>   
			    <td>' . $myrow['invoice_num'] . '</td>
			    <td>' . $myrow['ar_invoice_type'] . '</td>
			    <td>' . $myrow['invoice_type'] . '</td>
			    <td>' . date('Y-m-d',$myrow['invoice_date']) . '</td>
			    <td>' . $myrow['invoice_amount'] . '</td>
				<td>' . $myrow['payment_amount'] . '</td>
				<td>' . ($myrow['invoice_amount']-$myrow['transaction_amount']) . '</td>
			    <td>' . $myrow['transaction_amount'] . '</td>
			    <td>' . $myrow['narrative'] . '</td>	 			 
                ';
				?>
  <?php 
		 
           
          echo  ' </tr>';
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

