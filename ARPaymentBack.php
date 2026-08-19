<?php
if(isset($_GET['data'])){
	 
	 include_once("connect.php"); 
	 $sql = "select * from customers where customer_code = '".$_GET['data']."'";
	 $result_num = mysql_query($sql, $db);
	 $res = mysql_fetch_assoc($result_num);
	 echo $res['customer_name'].':'.$res['currency_code'];
	 return ;
 }
if(isset($_GET['data2'])){
	 //$sql = "select * from customers where customer_name = '".$_GET['data']."'";
	 include_once("connect.php"); 
	 $sql = "select * from customers where customer_name = '".$_GET['data2']."'";
	 $result_num = mysql_query($sql, $db);
	 $res_customer_name = mysql_fetch_assoc($result_num);
	 echo $res_customer_name['customer_code'].':'.$res_customer_name['currency_code'];
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
$Title = _('客户退款录入');
$ViewTopic= '客户退款录入';
$BookMark = '客户退款录入';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

unset($result);
if (isset($_POST['Save'])) 
{
  $errorflag = 0;
  $all_amount = 0;
  $all_dis_amount=0;
  $k =0;
  foreach ($_POST as $key => $value) 
  {
    if ($value != '') 
	{
		if (substr($key, 0,12)=='invoice_name') 
	  {
        $errorflag = 0;
        $i = substr($key, 12);
     
          if ($_POST['this_payment_amount'.$i]=='') 
		  {
            $errorflag = 1;
            prnMsg($value.'付款金额未填写,请确认！',error);
          }
		  if ($_POST['this_payment_amount'.$i]<0 ) 
		  {
            $errorflag = 1;
            prnMsg($value.'付款金额不可以小于0,请确认！',error);
          }


           
          if ($_POST['this_payment_amount'.$i] > $_POST['wait_amount'.$i] ) 
		  {
            $errorflag = 1;
            prnMsg($value.'本次付款总金额'.$_POST['this_payment_amount'.$i].'不可以大于未付款金额'.$_POST['wait_amount'.$i],error);
          }

		  $all_amount = $all_amount + $_POST['this_payment_amount'.$i] ;
		   
  
    }

	}

  }

 if ($_POST['payment_amount_all']<>$all_amount) 
  {
    $errorflag = 1;
   prnMsg($value.'付款金额合计'.$all_amount.'不等于付款总金额'.$_POST['payment_amount_all'].',请确认！',error);
  }
 
  $time = time();
  $time2 = $time - 10;

  if ($_SESSION['lastsearchtime'] > $time2) {
      $errorflag = 1;
      prnMsg($value . '重复提交！', error);
  }

  if ($errorflag == 0) 
  {

	  $date = date('Ymd');
    $sql_num = "select  lpad((max( substr( transaction_num, 11 ) ) +1 ) , 3, 0) order_number 
			    from fin_bank_transaction_headers_all 
				where substr(transaction_num,3,8)= '" . $date . "' and transaction_num like 'AT%'";
        
    $result_num = DB_query($sql_num, $db);
    while ($v = DB_fetch_array($result_num)) 
	{
      if ($v['order_number'] == null) 
	  {
        $OrderNum = 'AT'.$date . '001';
      } else 
	  {
        $OrderNum =  'AT'. $date . $v['order_number'];
      }
    }

    $Delivery_date = strtotime($_POST['Delivery_date']);
    DB_Txn_Begin($db);
    $time = time();
    $delivery_amount = 0;
    $k =0;
   foreach ($_POST as $key => $value) 
  {
    if ($value != '') 
	{
       $k = $k +1; 
	   if (substr($key, 0,12)=='invoice_name') 
	  {
        $errorflag = 0;
        $i = substr($key, 12);
         
          if($_POST['tax_amount_all'.$i]=='')
		  {
            $_POST['tax_amount_all'.$i] = '0'; 
          }  
        
		  $sql_line = "insert into fin_bank_transaction_lines_all 
                                (transaction_type,
							  bankaccountname,
							    bankchangenum,
							      transaction_num,
							   transaction_amount,dis_amount,
								   tax_amount,
                                  customer_code,
                                    narrative,
								currency_code,ap_invoice_name, 
                                 transaction_date,
                                creation_date,
                                   created_by,
                             last_update_date,
                             last_updated_by) 
			                      values ('".'AR退款'."',
								  '".$_POST['bankaccountname']."',
								  '".$_POST['bankchangenum']."',
								  '".$OrderNum."',    
								  '".$_POST['this_payment_amount'.$i]."','0',
                                  '".$_POST['tax_amount_all'.$i]."',
								  '".$_POST['customer_code']."',
								  '".$_POST['line_remark'.$i]."',
								  '".$_POST['currency_code']."',
								  '".$_POST['invoice_name'.$i]."', 
								  '".$Delivery_date."',
								  '".$time."',
								  '".$_SESSION['UserID']."',
								  '".$time."',
								  '".$_SESSION['UserID']."')";
       
		$result = DB_query($sql_line,$db);

 
         
 
           $sql = "update  ar_invoice_headers_all
                    set payment_amount     = payment_amount + '".$_POST['this_payment_amount'.$i]."',
					last_updated_by =  '".$_SESSION['UserID']."'
					 ,last_update_date =  '".$time."' 
                  where  invoice_name  ='".$_POST['invoice_name'.$i]."' ";
          $result = DB_query($sql,$db);
		 // echo $sql;
		  
 
        }
      }
    }
  }

 
if($_POST['tax_amount_all']=='')
		  {
            $_POST['tax_amount_all'] = '0'; 
          }  
$sql = "update  fin_bank_alls
                    set bank_onhand     = bank_onhand - '".$_POST['payment_amount_all']."' 
                  where  bankaccountname  ='".$_POST['bankaccountname']."'  ";
      $result = DB_query($sql,$db);  
	  
	    

$sql = "insert into fin_bank_transaction_headers_all
                                (transaction_type,
							  bankaccountname,
							    bankchangenum,
							      transaction_num,
							   transaction_amount,dis_amount,
								   tax_amount,
                                  customer_code,
                                    narrative,
								currency_code,
                                 transaction_date,
                                creation_date,
                                   created_by,
                             last_update_date,
                             last_updated_by) 
			                      values ('".'AR退款'."',
								  '".$_POST['bankaccountname']."',
								  '".$_POST['bankchangenum']."',
								  '".$OrderNum."',
								  '".$_POST['payment_amount_all']."','0',
                                  '".$_POST['tax_amount_all']."',
								  '".$_POST['customer_code']."',
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
	if ($k>0) {
    $_SESSION['lastsearchtime'] = $time;
    prnMsg('退款'.$OrderNum.'完成！',success);
     echo "<script>location.href='ARPaymentBack.php';</script>";
	}

   
}

?>
 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>客户退款录入</title>
<link rel="shortcut icon" href="/JXC/favicon.ico"/>
<link rel="icon" href="/JXC/favicon.ico"/>
<meta http-equiv="Content-Type" content="application/html; charset=utf-8"/>
<link href="/css/xenos/default.css" rel="stylesheet" type="text/css"/>
<script type="text/javascript" src ="/JXC/javascripts/miscfunctions.js"></script>
<script type="text/javascript" src ="/JXC/javascripts/wdatepicker.js"></script>
<script type="text/javascript">var basepath='/JXC/statics/base/images';</script>
<script type="text/javascript" src="./JXC/statics/base/js/metvar.js"></script>
<script type="text/javascript" src="./JXC/statics/base/js/jQuery1.7.2.js"></script>
<script type="text/javascript" src="./JXC/statics/base/js/uploadify/jquery.uploadify.v2.1.4.min.js"></script>
<script type="text/javascript" src="./JXC/statics/base/js/iframes.js"></script>
<script type="text/javascript" src="./JXC/statics/base/js/cookie.js"></script>
<script type="text/javascript" src="./JXC/statics/base/js/jquery.livequery.js"></script>

<link href="/css/xenos/default.css" rel="stylesheet" type="text/css"/>
<link rel="stylesheet" href="jquery.ui.autocomplete.css">
<script type="text/javascript" src="ui/jquery.ui.core.js"></script>
<script type="text/javascript" src="ui/jquery.ui.widget.js"></script>
<script type="text/javascript" src="ui/jquery.ui.position.js"></script>
<script type="text/javascript" src="ui/jquery.ui.autocomplete.js"></script>
<script src="./JXC/javascript/jquery-1.7.2.min.js"></script>
<script src="./JXC/javascript/lhgdialog.min.js?self=true&skin=chrome"></script>
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
<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="客户退款录入" alt="客户退款录入">客户退款录入</p>
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
  <input type="text" required="required" name="bankaccountname" id="text_slect_bankaccountname" value="<?=$_POST['bankaccountname']?>" size="40" maxlength="250" onblur="selbank()"/>
  <image class="select_img" src="img/search.png" id="btn_slect_bank<?=$i?>"/>
</div>
  <div class="text-nav-1 required"><div>银行名称:</div>
  <input  type="text"   name="mybankname" id="text_slect_mybankname" value="<?=$_POST['mybankname']?>" size="50" maxlength="100"/></div>
  <div class="text-nav-1 "><div>账户余额：</div>
  <input readonly="readonly" type="text"   name="bank_onhand" id="text_slect_bank_onhand" value="<?=$_POST['bank_onhand']?>" size="5" maxlength="10"/></div>  
  <div class="text-nav-1 "><div>币别：</div>
  <input readonly="readonly" type="text"   name="currency_code" id="text_slect_currency_code" value="<?=$_POST['currency_code']?>" size="5" maxlength="10"/></div>  
  <div class="text-nav-1"><div>银行账号:</div>
  <input readonly="readonly" type="text"   name="mybankaccount" id="text_slect_mybankaccount" value="<?=$_POST['mybankaccount']?>" size="50" maxlength="100"/></div>
  <div class="text-nav-1 required"><div>客户代码：</div>  
        <input type="text"  name="customer_code" id="text_slect_customer" value="<?=$_POST['customer_code']?>" size="10" maxlength="25" onblur="sel()"/>
        <image class="select_img" src="img/search.png" id="btn_slect_customer<?=$i?>"/>
      </div>
  <div class="text-nav-2 required"><div>客户名称：</div>
  <input  type="text" name="customername" id="text_slect_name" value="<?=$_POST['customername']?>" size="70" maxlength="50"  /></div>



  <div class="text-nav-1 required"><div>退款/转账单号:</div>
  <input type="text" required="required" maxlength="100" size="30" name="bankchangenum"  value="<?=$_POST['bankchangenum']?>" /> </div>
 
  <div class="text-nav-1 required"><div>退款总金额:</div>
  <input type="text" readonly="readonly" class="number" onblur="check()"  id="payment_amount_all"  name="payment_amount_all"  value="<?=$_POST['payment_amount_all']?>" size="10" maxlength="30"/> </div>
  <div class="text-nav-1 "><div>税别:</div>
   <input  type="text" name="tax_name" id="text_slect_tax_name" value="<?=$_POST['tax_name']?>" size="20" maxlength="50"  /></div>
   <div class="text-nav-1 required"><div>退款日期：</div>
  <input type="text" name="Delivery_date" maxlength="20" size="10" required="required" value="<?=$_POST['Delivery_date']?>" onfocus="WdatePicker() "></div>
  <div class="text-nav-2"><div>退款备注：</div>
  <input type="text"  maxlength="200" size="70" name="Header_Remark"  value="<?=$_POST['Header_Remark']?>"  />
  <input  type="hidden" name="tax_rate" id="text_slect_tax_rate" value="<?=$_POST['tax_rate']?>" size="20" maxlength="50"  /></div>

</div>

</table>
<div class="centre">
<input type="submit" name="Hearder" value="确认退款头信息选择发票">
</div>
<input type="hidden" name="PageOffset" value="1"/><br/>
<?php
if (isset($_POST['customername']) and $_POST['customername'] != '') {

	 

?>
<input id="purchase_table_lastRow" name="purchase_table_lastRow" type=hidden value="">

<div class="centre"> 
                        <p id="Prompt" style="color: red;font-size: 20px"></p>
                    </div>

<table id="purchase_table" cellpadding="2" class="selection">
 
<tr id="list-top">
  <th width="190">发票单号</th>
  <th width="100">发票号码</th> 
  <th width="100">建单日</th> 
  <th width="100">应退款金额</th> 
  <th width="100" >已退款金额</th> 
  <th width="100" >待退款金额</th>
  <th width="100" >退款金额</th>
  <th width="55" align="center">操作</th>
 
<?php for($i=1;$i<=50;$i++){?>
 <tr id="purchase_table_<?=$i?>" <?php echo $i>10&&$_POST['invoice_name'.$i]==''?'style="display:none"':''?> class="mouse click">

  <td> <input type="text" name="invoice_name<?=$i?>" id="text_slect_invoice_name<?=$i?>" value="<?=$_POST['invoice_name'.$i]?>" size="15" maxlength="15"/>
  <image class="select_img" src="img/search.png" id="btn_slect_po<?=$i?>"/>
</td>
<td><input type="text" readonly="readonly" name="invoice_num<?=$i?>" id="text_slect_invoice_num<?=$i?>" value="<?=$_POST['invoice_num'.$i]?>" size="15" maxlength="25"/></td>
   
   <td><input type="text" readonly="readonly" name="creation_date<?=$i?>" id="text_slect_creation_date<?=$i?>" value="<?=$_POST['creation_date'.$i]?>" size="9" maxlength="15"/></td>

  <td><input type="text" readonly="readonly" name="invoice_amount<?=$i?>" id="text_slect_invoice_amount<?=$i?>" value="<?=$_POST['invoice_amount'.$i]?>" size="9" maxlength="15"/></td>

  <td><input type="text" readonly="readonly" name="payment_amount<?=$i?>"  id="text_slect_payment_amount<?=$i?>" value="<?=$_POST['payment_amount'.$i]?>" size="9" maxlength="15" /></td>
  <td><input type="text" readonly="readonly" name="wait_amount<?=$i?>"  id="text_slect_wait_amount<?=$i?>" value="<?=$_POST['wait_amount'.$i]?>" size="9" maxlength="15" /></td>
 
  
  <td><input type="text"  name="this_payment_amount<?=$i?>"  class="number"  id="this_payment_amount<?=$i?>" value="<?=$_POST['this_payment_amount'.$i]?>"     size="6" maxlength="10"  step="1"  min="0"  onkeyup="this.value= this.value.match(/\d+(\.\d{0,2})?/) ? this.value.match(/\d+(\.\d{0,2})?/)[0] : ''"   onblur="checktotal()" />
  <input type="hidden" name="schedule_recevie_id<?=$i?>"  id="text_slect_schedule_recevie_id<?=$i?>" value="<?=$_POST['schedule_recevie_id'.$i]?>" size="9" maxlength="15" /></td>

  <td>  <a onclick="delettr($(this));" style="padding:0px 5px;" href="javascript:;">删除</a>
 
  </td>
</tr> 
<?php 
 $i=$i+1;
  }?>

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
    function checktotal(){       
								   var  all_invoice_amount=0; 
								   var  all_dis_amount=0;                
                                for(var i=1 ; i < 50; i++){   
									if (document.getElementById("this_payment_amount" + i)==null)  {
									p=0;
										}
									else {							 
		                           var  invoice_amount=0;
								   var  dis_amount=0;
                                   var invoice_amount=document.getElementById("this_payment_amount"+i).value;   
								   var a=document.getElementById("text_slect_wait_amount"+i).value;
								   if(parseFloat(invoice_amount)>parseFloat(a)){
            					 document.getElementById("Prompt").innerHTML="退款金额不可以大于已付款金额！";
            					   document.getElementById("this_payment_amount"+i).value="";
            					   document.getElementById("this_payment_amount"+i).focus();
								   invoice_amount=0;
        					       }  else {
        					       document.getElementById("Prompt").innerHTML="";
      					           }
							     
								   if( invoice_amount>0 )
								   {  
								   all_invoice_amount=Number(all_invoice_amount) + Number(invoice_amount);                                 
                                   } 
								   }}
		            document.getElementById("payment_amount_all").value= Math.round(Number(all_invoice_amount)*100)/100; 
		 }

	 function  check(){
	    var a=document.getElementById("text_slect_bank_onhand"+s1).value;
        var b=document.getElementById("payment_amount_all"+s1).value;
    
      if(parseFloat(a)<parseFloat(b)){
            document.getElementById("Prompt").innerHTML="本次付款金额不可以大于账户余额！！！！";
            document.getElementById("payment_amount_all"+s1).value="";
            document.getElementById("payment_amount_all"+s1).focus();
        }  else {
            document.getElementById("Prompt").innerHTML="";
        }
     }
    
	 function  check55(s1){
	    var a=document.getElementById("text_slect_wait_amount"+s1).value;
        var b=document.getElementById("this_payment_amount"+s1).value;  
      if(parseFloat(b)>parseFloat(a)){
            document.getElementById("Prompt").innerHTML="退款金额不可以大于待退款金额！";
            document.getElementById("this_payment_amount"+s1).value="";
            document.getElementById("this_payment_amount"+s1).focus();
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


		$('#btn_slect_customer').dialog({
            title:'选择客户',
            width: '850px',
            height: 470,
            content:'url:BtnSearchARCustomer.php?fwValue=&cat=buliao',
            init:function(){
			    this.content.document.getElementById('cat').value ='buliao';
                this.content.document.getElementById('fwValue').value = '';
            }
        });

		 <?php for($i=1;$i<=50;$i++){?> 
        $('#btn_slect_po<?=$i?>').dialog({
            title:'选择已付款的发票',
            width: '950px',
            height: 420,
            content:'url:SearchPaymentSo.php?fwValue=<?=$i?>&cat=<?=$_POST['customer_code']?>',
            init:function(){
			    this.content.document.getElementById('cat').value = 'buliao';
                this.content.document.getElementById('fwValue').value = '<?=$i?>';
            }
        });
		<?php }?>



	
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
		$( "#text_slect_customer" ).autocomplete({
			source: "autosearchcustomer.php",
			minLength: 2,
			autoFocus: true
		});
	});
	$(function(){
		$( "#text_slect_name" ).autocomplete({
			source: "autosearchcustomer2.php",
			minLength: 2,
			autoFocus: true
		});
	});

	function sel(){
		var name=$('#text_slect_customer').val()
		$.get("","data="+name,function(res){
			name = res.split(":")		  
				$("#text_slect_name").val(name[0]) 
				$("#text_slect_currency_code").val(name[1])
		})	
	}

	 function sel_name(){
		var name=$('#text_slect_name').val()
		$.get("","data2="+name,function(res_customer_name){
			name = res_customer_name.split(":")		  
				$("#text_slect_customer").val(name[0]) 
				$("#text_slect_currency_code").val(name[1])
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

</script>
</body>

</html>
<?

include('includes/footer.inc');
?>

