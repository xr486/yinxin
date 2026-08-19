<?php

include('includes/session.inc');
$Title = _('客户退款审核');
$ViewTopic= '客户退款审核';
$BookMark = '客户退款审核';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

unset($result);

if (isset($_GET['Updateorder_number']) ) {
$_POST['transaction_num']=$_GET['Updateorder_number'];
}


if (isset($_POST['UpdateStatus']) ) {

    $time = time();
    $time = time();
    $time2 = $time - 10;
  
  
     if (  $_POST['hangover'] =='Y'  )  {
	   prnMsg('本次退金额超过已收款金额', 'error');
	}else {
			$sql2="UPDATE fin_bank_transaction_headers_all 
                    SET   	status= '核准' 						 
					,last_update_date='" . $time. "'
                    ,last_updated_by='" . $_SESSION['UserID'] . "'
                    WHERE  transaction_num='".$_POST['transaction_num']."' 
                    "; 	
		$result = DB_query($sql2,$db);
           
		 
        $sql1="UPDATE fin_bank_alls 
                    SET   bank_onhand = bank_onhand - '" . $_POST['transaction_amount']. "'				
					,last_update_date='" . $time. "'
                    ,last_updated_by='" . $_SESSION['UserID'] . "'
                    WHERE  bankaccountname='".$_POST['bankaccountname']."'
                    "; 	
		$result = DB_query($sql1,$db) ;

		$sql3="select * from fin_bank_transaction_lines_all  
                     WHERE  transaction_num='".$_POST['transaction_num']."' 
                    "; 	
		$result3 = DB_query($sql3,$db); 
		while ($myrow = DB_fetch_array($result3))  {
             

			  $sql = "update  so_headers_all
                    set payment_amount     = payment_amount  - '". $myrow['transaction_amount'] ."'
                       ,dis_payment_amount = dis_payment_amount - '". $myrow['dis_amount']."'
                  where  order_number  ='".$myrow['so_num']."'  ";
              $result = DB_query($sql,$db);

		}  


		prnMsg('退款'.$_POST['invoice_num'].'审核完成！',success);
    echo "<script>location.href='index.php';</script>";
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

      
		prnMsg('退款'.$_POST['invoice_num'].'已拒绝！',success);


		
    echo "<script>location.href='ARPaymentApproved.php';</script>";

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
<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="客户退款审核" alt="客户退款审核">客户退款审核</p>
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
			and pha.transaction_type in ('AR退款')
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
	   $_POST['transaction_type']=$myrow['transaction_type'] ; 


 }
 
     
?>

<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
<table class="selection">

<tr>
 <td>流水号:</td>
  <td><input type="text" readonly="readonly"   name="transaction_num"  value="<?=$_POST['transaction_num']?>" size="16" maxlength="25"/> </td>
  <td>收款/转账单号:</td>
  <td><input type="text" readonly="readonly"   name="bankchangenum"  value="<?=$_POST['bankchangenum']?>" size="25" maxlength="250"/> </td>
<td>银行账户名:</td>
  <td colspan="5"><input readonly="readonly" type="text" name="bankaccountname" id="text_slect_name" value="<?=$_POST['bankaccountname']?>" size="50" maxlength="250"/></td>

 
</tr>

<tr>
 <td>客户代码:</td>
  <td><input type="text" readonly="readonly"  name="customer_code" id="text_slect_vendor" value="<?=$_POST['customer_code']?>" size="10" maxlength="25"/> </td>
  <td>客户名称:</td>
  <td colspan="4"><input readonly="readonly" type="text" name="customer_name" id="text_slect_name" value="<?=$_POST['customer_name']?>" size="50" maxlength="250"/></td>
   <td>收款票日期:</td>
  <td><input type="text" readonly="readonly"  name="invoice_date" maxlength="20" size="9"  value="<?=date('Y-m-d',$_POST['invoice_date'])?>"onfocus="WdatePicker() "></td>
  
  <td>币别：</td>
  <td><input type="text" readonly="readonly"  name="currency_code" id="text_slect_currency_code" value="<?=$_POST['currency_code']?>" size="6" maxlength="25"/> </td>
</tr>


   <td>收款/退款类型:</td>
  <td  ><input type="text"  readonly="readonly"  maxlength="100" size="10" name="transaction_type"  value="<?=$_POST['transaction_type']?>" size="10" maxlength="30"/> </td>
  <td>退款金额:</td>
  <td  ><input type="text" class="number" readonly="readonly"  maxlength="100" size="10" name="transaction_amount"  value="<?=$_POST['transaction_amount']?>" size="10" maxlength="30"/> </td>
    <td>优惠金额:</td>
  <td  ><input type="text" class="number" readonly="readonly"  maxlength="100" size="10" name="dis_amount"  value="<?=$_POST['dis_amount']?>" size="10" maxlength="30"/> </td>
  
 
<td>退款备注：</td>
<td colspan="4"><input type="text"  maxlength="200" size="50" name="Header_Remark"  value="<?=$_POST['Header_Remark']?>"/> </td>

</tr>

</table>
<?php

$sql ="select a.order_number,a.check_amount,c.transaction_amount,c.dis_amount,a.payment_amount,a.dis_payment_amount,a.order_all_amount,a.creation_date,a.yewu,(select transaction_amount from fin_bank_transaction_headers_all fb where c.transaction_num = fb.transaction_num)AS transaction_amount1
				from fin_bank_transaction_lines_all c,so_headers_all a ,ar_invoice_lines_all arl
				where arl.so_num=a.order_number   and arl.invoice_name = c.ap_invoice_name
                and arl.so_num = a.order_number
				and  c.transaction_num 	= '" .$_POST['transaction_num'] . "'";
    	 	 	  	 	  	 	 	
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
					<th   >' . _('建单日期') . '</th>  		
					<th   >' . _('业务') . '</th>  	
					<th   >' . _('订单金额') . '</th>	 
					<th   >' . _('对账金额') . '</th>	
					<th   >' . _('已收款金额') . '</th>	 
					  <th  ><font color="#1E90FF">' . _('本次退款金额') . '</font></th>  
				  
            </tr>'; 
$i=0;
 while ($myrow = DB_fetch_array($result))  {
	 $i=$i+1;
	if ($myrow['transaction_amount1'] < $myrow['transaction_amount'] ) {
		
	echo ' <input type="hidden"  maxlength="100" size="10"  name="hangover"  value="Y" size="10" maxlength="30"/>';
    echo '  <tr bgcolor="#EE9A00">  
				<td>' . $myrow['order_number']  . '</td> 			   
			    <td>' . date('Y-m-d',$myrow['creation_date']) . '</td>		   
			    <td>' . $myrow['yewu'] . '</td>	   
			    <td>' . $myrow['order_all_amount'] . '</td>
			    <td>' . $myrow['check_amount'] . '</td>
				<td>' . $myrow['transaction_amount1'] . '</td> 
			    <td>' . $myrow['transaction_amount'] . '</td> 
			    <td>本次退款金额大于已收款金额</td>				 
                ';
				} else {
    echo ' <td>' . $myrow['order_number']  . '</td> 			  
			    <td>' . date('Y-m-d',$myrow['creation_date']) . '</td>	
				<td>' . $myrow['yewu'] . '</td>	   
			    <td>' . $myrow['order_all_amount'] . '</td>
			    <td>' . $myrow['check_amount'] . '</td>
				<td>' . $myrow['transaction_amount1'] . '</td> 
			    <td>' . $myrow['transaction_amount'] . '</td> 		 
                ';}
				?>
  <?php 
		 
           
          echo  ' </tr>';
            $i++;
			   } //end loop through customers
        echo '</table>';
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

