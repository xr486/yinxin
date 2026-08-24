<?php

include('includes/session.inc');
$Title = _('供应商退款审核');
$ViewTopic= '供应商退款审核';
$BookMark = '供应商退款审核';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

unset($result);

if (isset($_GET['Updatepo_num']) ) {
$_POST['transaction_num']=$_GET['Updatepo_num'];
}


if (isset($_POST['UpdateStatus']) ) {

	
   if (  $_POST['transaction_amount'] > $_POST['bank_onhand'] )  {
	 prnMsg('支出单据金额不能大于账户余额', 'error');
	} else {
    $time = time();

		$sql2="UPDATE fin_bank_transaction_headers_all 
                    SET   	status= '核准' 						 
					,last_update_date='" . $time. "'
                    ,last_updated_by='" . $_SESSION['UserID'] . "'
                    WHERE  transaction_num='".$_POST['transaction_num']."' 
                    "; 	
		$result = DB_query($sql2,$db);
           // echo $sql2;
        $sql1="UPDATE fin_bank_alls 
                    SET   bank_onhand = bank_onhand - '" . $_POST['transaction_amount']. "'				
					,last_update_date='" . $time. "'
                    ,last_updated_by='" . $_SESSION['UserID'] . "'
                    WHERE  bankaccountname='".$_POST['bankaccountname']."'
                    "; 	
		$result = DB_query($sql1,$db) ;
//echo $sql1;
		


		prnMsg('付款'.$_POST['invoice_num'].'审核完成！',success);
    echo "<script>location.href='APPaymentApproved.php';</script>";
	}
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
              $sql = "update  ap_invoice_headers_all
                    set payment_amount     = payment_amount  + '". $myrow['transaction_amount'] ."'
                  where  invoice_name  ='".$myrow['invoice_name']."'  ";
              $result = DB_query($sql,$db); 
		} 

      
		prnMsg('付款'.$_POST['invoice_num'].'已拒绝！',success);


		
    echo "<script>location.href='APPaymentApproved.php';</script>";

}
 	 	


?>
 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>供应商退款审核</title>
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
<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="供应商退款审核" alt="供应商退款审核">供应商退款审核</p>
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
                                  pha.vendor_code,
                                    pha.narrative,
								pha.currency_code,
                                 pha.transaction_date,
                pha.creation_date,pha.created_by,pha.vendor_code,c.vendor_name,pha.status,d.bank_onhand,d.bank_onhand-pha.transaction_amount chaoguo
	from fin_bank_transaction_headers_all pha, vendors c,fin_bank_alls d
            where pha.status='建立'
			and pha.transaction_type in ('AP退款')
			and pha.vendor_code=c.vendor_code
			and pha.bankaccountname=d.bankaccountname
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
	   $_POST['creation_date']=date('Y-m-d H:i:s',$myrow['creation_date']) ;
	   $_POST['created_by']=$myrow['created_by'] ;
	   $_POST['transaction_num']=$myrow['transaction_num'] ; 
	   $_POST['bankaccountname']=$myrow['bankaccountname'] ;
	   $_POST['narrative']=$myrow['narrative'] ;
	   $_POST['transaction_amount']=$myrow['transaction_amount'] ; 
	   $_POST['dis_amount']=$myrow['dis_amount'] ; 
	   $_POST['transaction_type']=$myrow['transaction_type'] ; 
	   $_POST['bank_onhand']=$myrow['bank_onhand'] ; 


 }
 
     
?>

<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
<table class="selection">

<div class="text-nav">
<div class="text-nav-1"><div>流水号:</div>
  <input type="text" readonly="readonly"   name="transaction_num"  value="<?=$_POST['transaction_num']?>" size="16" maxlength="25"/> </div>
  <div class="text-nav-1"><div>付款/转账单号:</div>
 <input type="text" readonly="readonly"   name="bankchangenum"  value="<?=$_POST['bankchangenum']?>" size="25" maxlength="250"/> </div>
  <div class="text-nav-1"><div>银行账户名:</div>
  <input readonly="readonly" type="text" name="bankaccountname" id="text_slect_name" value="<?=$_POST['bankaccountname']?>" size="50" maxlength="250"/></div>

  <div class="text-nav-1"><div>供应商代码:</div>
  <input type="text" readonly="readonly"  name="vendor_code" id="text_slect_vendor" value="<?=$_POST['vendor_code']?>" size="10" maxlength="25"/> </div>
  <div class="text-nav-2"><div>供应商名称:</div>
  <input readonly="readonly" type="text" name="vendor_name" id="text_slect_name" value="<?=$_POST['vendor_name']?>" size="50" maxlength="250"/></div>
  <div class="text-nav-1"><div>付款日期:</div>
    <input type="text" readonly="readonly"  name="invoice_date" maxlength="20" size="9"  value="<?=date('Y-m-d',$_POST['invoice_date'])?>"onfocus="WdatePicker() "></div>
  
  <div class="text-nav-1"><div>币别：</div>
  <input type="text" readonly="readonly"  name="currency_code" id="text_slect_currency_code" value="<?=$_POST['currency_code']?>" size="6" maxlength="25"/> </div>

 
<div class="text-nav-1"><div>账户余额:</div>
 <input type="text"  readonly="readonly"  maxlength="100" size="10" name="bank_onhand"  value="<?=$_POST['bank_onhand']?>"  /> </div>
  
   <?php if ($_POST['bank_onhand']<$_POST['transaction_amount']) { ?> 
    <div class="text-nav-1"><div>付款金额: </div>
  <input type="text" class="number" readonly="readonly"   name="transaction_amount"  value="<?=$_POST['transaction_amount']?>" size="10" maxlength="30"/>  </div>
  <?php  } else {
  ?>
<div class="text-nav-1"><div>付款金额: </div>
    <input type="text" class="number" readonly="readonly"  name="transaction_amount"  value="<?=$_POST['transaction_amount']?>" size="10" maxlength="30"/> </div>

  <?php  } 
  ?>
  <div class="text-nav-1"><div>建单日期</div>
<input type="text" readonly="readonly" name="creation_date"  value="<?=$_POST['creation_date']?>"/></div>
  <div class="text-nav-1"><div>建单人员</div>
<input type="text" readonly="readonly" name="creation_date"  value="<?=$_POST['created_by']?>"/></div>

 
  <div class="text-nav-2"><div>付款备注：</div>
<input type="text"  maxlength="200" size="50" name="Header_Remark"  value="<?=$_POST['Header_Remark']?>"/>
<input type="hidden"  readonly="readonly"  name="transaction_type"  value="<?=$_POST['transaction_type']?>" size="10" maxlength="30"/></div>

  </div>

</table>
<?php

$sql ="select  b.invoice_name,b.payment_amount,b.invoice_date,b.invoice_amount,c.transaction_amount
				from fin_bank_transaction_headers_all a,ap_invoice_headers_all b,fin_bank_transaction_lines_all c
				where c.transaction_num=a.transaction_num 
				and b.invoice_name=c.ap_invoice_name
				and a.transaction_num 	= '" .$_POST['transaction_num'] . "'";
    	 	 	  	 	  	 	 	
	 //echo $sql; //已付款金额	
	$result = DB_query($sql,$db);
    if (DB_num_rows($result)==0) {
        unset($result);
        prnMsg(_('没有需要付款的行，请重新输入条件查询！') ,'error');
    }
 echo '<br /><div class="text-nav-table"> 
                    <table cellpadding="2" class="selection">';  

    echo '<tr> 	         
					
					<th width="100"  >' . _('发票单号') . '</th>			
					<th   >' . _('发票金额') . '</th>					
					<th   >' . _('已付款金额') . '</th>	 
					<th   >' . _('待付款金额') . '</th>	
					  <th  >' . _('本次付款金额') . '</th> 
					  <th  >' . _('发票日期') . '</th> 
            </tr>'; 
			
$i=0;

 while ($myrow = DB_fetch_array($result))  {
	 $i=$i+1;
 
$wait_amount=$myrow['invoice_amount']-$myrow['payment_amount'];
	 //if ( $wait_amount < 0 ) {
		 
		echo '<tr >
				<td>' . $myrow['invoice_name']  . '</td>
				<td>' . $myrow['invoice_amount']  . '</td>
				<td>' . $myrow['payment_amount']  . '</td>
				<td>' . $wait_amount . '</td>
				<td>' . $myrow['transaction_amount']  . '</td>  
			    <td>' . date('Y-m-d',$myrow['invoice_date']) . '</td>

	  
           
  
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

