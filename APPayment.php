<?php
if(isset($_GET['data'])){
	 
	 include_once("connect.php"); 
	 $sql = "select * from vendors where vendorcode = '".$_GET['data']."' and enable_flag='Y ";
	 $result_num = mysql_query($sql, $db);
	 $res = mysql_fetch_assoc($result_num);
	 echo $res['vendor_name'].':'.$res['vendor_address'].':'.$res['vendor_contacts'].':'.$res['currencycode'];
	 return ;
 } 	 	
if(isset($_GET['data2'])){
	 //$sql = "select * from customers where customer_name = '".$_GET['data']."'";
	 include_once("connect.php"); 
	 $sql = "select * from vendors where vendor_name = '".$_GET['data2']."' and enable_flag='Y ";
	 $result_num = mysql_query($sql, $db);
	 $res_customer_name = mysql_fetch_assoc($result_num);
	 echo $res_customer_name['vendorcode'].':'.$res_customer_name['vendor_address'].':'.$res_customer_name['vendor_contacts'].':'.$res_customer_name['currencycode'];
	 return ;
 }
include('includes/session.inc');
$Title = _('供应商付款录入');

$ViewTopic= '供应商付款录入';
$BookMark = '供应商付款录入';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

unset($result);

     
    if (isset($_POST['Save'])) {
 $errorflag = 0;
  $all_amount = 0;
  $all_invoice_dis_amount = 0;
  $k =0;

  $lineflag = 0;
	foreach ($_POST as $key => $value) {
		if ($value != '') {

		if (substr($key, 0, 12) == 'invoice_name') {
				//$errorflag = 0;
				$i = substr($key, 12);
				if ($value != '') {
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
					if ($_POST['this_payment_amount' . $i] == '') {
						$errorflag = 1;
						$lineflag = 0;
						prnMsg($value . '未填写付款，请填写付款！', error);
					} else {
						$lineflag = 1;
					}
				}
			}
		}
	}

	if ($lineflag == 0) {
		prnMsg(_('资料至少存在一行有效！'), 'error');
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
    $sql_num = "select lpad((max( substr( transaction_num, 11 ) ) +1 ) , 3, 0) order_number 
			    from fin_bank_transaction_headers_all 
				where substr(transaction_num,3,8)= '" . $date . "' and transaction_num like 'AF%'";
    $result_num = DB_query($sql_num, $db);
    while ($v = DB_fetch_array($result_num)) 
	{
      if ($v['order_number'] == null) 
	  {
        $OrderNum = 'AF'.$date . '001';
      } else 
	  {
        $OrderNum =  'AF'. $date . $v['order_number'];
      }
    }

    $Delivery_date = strtotime($_POST['Delivery_date']);
    DB_Txn_Begin($db);
    $time = time();
    $delivery_amount = 0;
    $k =0;
   foreach ($_POST as $key => $value) {
		if ($value != '') {
       
	   
        
        
          if (substr($key, 0, 12) == 'invoice_name') {
             $k = $k +1; 
			 $i = substr($key, 12);
		  $sql_line = "insert into fin_bank_transaction_lines_all 
                                (transaction_type,
							  bankaccountname,
							    bankchangenum,
							      transaction_num,ap_invoice_name,
							   transaction_amount, 
                                  vendor_code,
                                    narrative,
								currency_code, 
                                 transaction_date,
                                creation_date,
                                   created_by,
                             last_update_date,
                             last_updated_by) 
			                      values ('".'AP付款'."',
								  '".$_POST['bankaccountname']."',
								  '".$_POST['bankchangenum']."',
								  '".$OrderNum."','".$_POST['invoice_name'.$i]."',
								  '".$_POST['this_payment_amount'.$i]."',
								  '".$_POST['vendorcode']."',
								  '".$_POST['line_remark'.$i]."',
								  '".$_POST['currency_code']."', 
								  '".$Delivery_date."',
								  '".$time."',
								  '".$_SESSION['UserID']."',
								  '".$time."',
								  '".$_SESSION['UserID']."')";
       
		$result = DB_query($sql_line,$db);

  //echo $sql_line;
         
 
           $sql = "update  ap_invoice_headers_all
                    set payment_amount     = payment_amount     + '".$_POST['this_payment_amount'.$i]."'
                  where  invoice_name  ='".$_POST['invoice_name'.$i]."'  ";
          $result = DB_query($sql,$db);
		  }
		    //echo $sql;
 
        } 
   }
	  $sql2 = "update   fin_bank_alls
                    set bank_onhand     = bank_onhand - '".$_POST['payment_amount_all']."' 
                  where  bankaccountname  ='".$_POST['bankaccountname']."'  ";
      $result = DB_query($sql2,$db);
 //echo $sql2;
$sql = "insert into fin_bank_transaction_headers_all
                                (transaction_type,status,
							  bankaccountname,
							    bankchangenum,
							      transaction_num,
							   transaction_amount,
								   tax_amount,
                                  vendor_code,
                                    narrative,
								currency_code,
                                 transaction_date,
                                creation_date,
                                   created_by,
                             last_update_date,
                             last_updated_by) 
			                      values ('AP付款','建立',
								  '".$_POST['bankaccountname']."',
								  '".$_POST['bankchangenum']."',
								  '".$OrderNum."',
								  '".$_POST['payment_amount_all']."',
                                  '".$_POST['tax_amount_all']."',
								  '".$_POST['vendorcode']."',
								  '".$_POST['Header_Remark']."',
								  '".$_POST['currency_code']."',
								  '".$Delivery_date."',
								  '".$time."',
								  '".$_SESSION['UserID']."',
								  '".$time."',
								  '".$_SESSION['UserID']."')";
       //echo $sql;
		$result = DB_query($sql,$db);
    }

     

    
    DB_Txn_Commit($db);
	if ($k>0) {
        $_SESSION['lastsearchtime'] = $time;
    prnMsg('付款单'.$_POST['OrderNum'].'成功建立！',success);
 
	header("Location: SussCreate.php?OrderNum=".$invoice_name."&type=APPayment");
	}
		
    }

 ?>
 

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>供应商付款录入</title>
<link rel="shortcut icon" href="/favicon.ico"/>
<link rel="icon" href="/favicon.ico"/>
<meta http-equiv="Content-Type" content="application/html; charset=utf-8"/>
<link href="/css/xenos/default.css" rel="stylesheet" type="text/css"/>
<script type="text/javascript" src ="./JXC/javascripts/miscfunctions.js"></script>
<script type="text/javascript" src ="./JXC/javascripts/wdatepicker.js"></script>
<script type="text/javascript">var basepath='./JXC/statics/base/images';</script>
<script type="text/javascript" src="./JXC/statics/base/js/metvar.js"></script>
<script type="text/javascript" src="./JXC/statics/base/js/jQuery1.7.2.js"></script>
<script type="text/javascript" src="./JXC/statics/base/js/uploadify/jquery.uploadify.v2.1.4.min.js"></script>
<script type="text/javascript" src="./JXC/statics/base/js/iframes.js"></script>
<script type="text/javascript" src="./JXC/statics/base/js/cookie.js"></script>
<script type="text/javascript" src="./JXC/statics/base/js/jquery.livequery.js"></script>

<link rel="stylesheet" href="jquery.ui.autocomplete.css">
<script type="text/javascript" src="ui/jquery.ui.core.js"></script>
<script type="text/javascript" src="ui/jquery.ui.widget.js"></script>
<script type="text/javascript" src="ui/jquery.ui.position.js"></script>
<script type="text/javascript" src="ui/jquery.ui.autocomplete.js"></script>

<script src="./JXC/javascript/jquery-1.7.2.min.js"></script>
<script src="./JXC/javascript/lhgdialog.min.js?self=true&skin=chrome"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
<script src="./javascript/bootstrap.min.js"></script>

<script type="text/javascript">
/*ajax执行*/
var lang = 'cn';
var metimgurl='./JXC/statics/base/images/';
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
            <p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="供应商付款录入处理" alt="供应商付款录入处理">供应商付款录入处理</p>
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST"><input type="hidden" name="time" 

      value="<?=$time?>">
                <div>
                <input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
                <table class="selection">
				<?php 
if (!isset($_POST['Delivery_date'])) {
    $_POST['Delivery_date'] = date( "Y-m-d", mktime(0,0,0,date("m"),date("d"),date("Y")));

} 
?>
                <div class="text-nav">
				<div class="text-nav-1 required"><div>银行账户名称:</div>
  <input type="text" required="required" name="bankaccountname" id="text_slect_bankaccountname" value="<?=$_POST['bankaccountname']?>" size="40" maxlength="250"  onblur="selbank()"/>
  <image class="select_img" src="img/search.png" id="btn_slect_bank"/>
</div>
  <div class="text-nav-1 required"><div>银行名称:</div>
  <input readonly="readonly" type="text"   name="mybankname" id="text_slect_mybankname" value="<?=$_POST['mybankname']?>" size="50" maxlength="100"/></div>

  <div class="text-nav-1" style="display:none;"><div>账户余额：</div>
  <input readonly="readonly" type="text"  class="number" name="bank_onhand" id="text_slect_bank_onhand" value="<?=$_POST['bank_onhand']?>" size="5" maxlength="10"/></div>  
		 <div class="text-nav-1"><div>币别：</div>
  <input readonly="readonly" type="text"   name="currency_code" id="text_slect_currency_code" value="<?=$_POST['currency_code']?>" size="5" maxlength="10"/></div>  
  <div class="text-nav-1 required"><div>银行账号:</div>
  <input readonly="readonly" type="text"   name="mybankaccount" id="text_slect_mybankaccount" value="<?=$_POST['mybankaccount']?>" size="50" maxlength="100"/></div>
  <div class="text-nav-1 required">
			<div>供应商代号：</div>  
			<input type="text"  required="required" name="vendorcode" id="text_slect_vendor" value="<?=$_POST['vendorcode']?>" size="10" maxlength="25" />
            <image class="select_img" src="img/search.png" id="btn_slect_vendor"/>
		</div>

        <div class="text-nav-2 ">
			<div>供应商名称：</div>  
			<input type="text"   name="vendorname" id="text_slect_name" value="<?=$_POST['vendorname']?>" size="10" maxlength="25" />
		</div>

        <div class="text-nav-1"><div>发票地址：</div>
  <input readonly="readonly" type="text"   name="Address" id="text_slect_address" value="<?=$_POST['Address']?>" size="70" maxlength="50"/></div>
  <div class="text-nav-1"><div>税别：</div>
  <input readonly="readonly" type="text"   name="tax_code" id="text_slect_tax_name" value="<?=$_POST['tax_code']?>" size="8" maxlength="10"/></div> 
  <div class="text-nav-1 required"><div>付款/转账单号:</div>
  <input type="text"  maxlength="100" size="30" name="bankchangenum"  value="<?=$_POST['bankchangenum']?>" /></div>
 
  <div class="text-nav-1 required"><div>付款总金额:</div>
  <input type="text" class="number"  readonly="readonly"   name="payment_amount_all"   id="payment_amount_all" value="<?=$_POST['payment_amount_all']?>" size="10" maxlength="30"  onblur="checkhead()"/></div>
  <div class="text-nav-1"><div>优惠总金额:</div>
  <input type="text" class="number" readonly="readonly"  name="dis_amount"  id="dis_amount"  value="<?=$_POST['dis_amount']?>" size="10" maxlength="30"/></div>
  <div class="text-nav-1 required"><div>付款日期：</div>
  <input type="text" name="Delivery_date" maxlength="20" size="10" required="required" value="<?=$_POST['Delivery_date']?>" onfocus="WdatePicker() "></div>
  <div class="text-nav-2"><div>付款备注：</div>
  <input type="text"  maxlength="200" size="70" name="Header_Remark"  value="<?=$_POST['Header_Remark']?>" size="20" maxlength="20"/>
  <input type="hidden" name="tax_rate" id="text_slect_tax_rate"  value="<?=$_POST['tax_rate']?>" size="20" maxlength="20"/></div>


    </table>
    <div class="centre">
<input type="submit" name="Hearder" value="确认付款单查询待付款发票">
</div>
    <label id="alert" style="color:red;"></label>
    <input type="hidden" name="PageOffset" value="1"/><br/>
    <?php
        if (isset($_POST['vendorname']) and $_POST['vendorname'] != '') {
		//check56();
		// onclick="check56();"
    ?>

 

              <input id="purchase_table_lastRow" name="purchase_table_lastRow" type=hidden value="">
 
			  <div class="centre"> 
                        <p id="Prompt" style="color: red;font-size: 20px"></p>
                    </div>
					<div class="text-nav-table">
                    <table id="purchase_table" cellpadding="2" class="selection">
                    <tr id="list-top">
                      <th width="10">发票单号</th>
					  <th width="10" >发票号码</th>
					  <th width="10" >应付金额</th> 
					  <th width="10" >已付金额</th> 
					  <th width="10" >待付款金额</th>
					  <th width="10" > 本次付款金额 </th>
					  <th width="90" > 备注  </th>
					  <th width="90" > 发票日期  </th>
                
                    <th bgcolor="#87CEFA" width="50" align="center">操作</th>
                    </tr>
                    <?php for($i=1;$i<=50;$i++){?>
            
<tr id="purchase_table_<?=$i?>" <?php echo $i>5 &&$_POST['invoice_name'.$i]==''?'style="display:none"':''?> class="mouse click">
  <td> <input type="text" readonly="readonly" name="invoice_name<?=$i?>" id="text_slect_invoice_name<?=$i?>" value="<?= $_POST['invoice_name'.$i] ?>" size="12" maxlength="25"/> 
                        <image class="select_img" src="img/search.png" id="btn_slect_buliao<?=$i?>"/>
                    
                    </td>
  <td> <input type="text" readonly="readonly" name="invoice_num<?=$i?>" id="text_slect_invoice_num<?=$i?>" value="<?= $_POST['invoice_num'.$i] ?>" size="10" maxlength="25"/> </td>
 <td> <input type="text" readonly="readonly" name="amount<?=$i?>" id="text_slect_amount<?=$i?>" value="<?= $_POST['amount'.$i] ?>" size="8" maxlength="10"/></td>  
<td> <input type="text" readonly="readonly" name="payment_amount<?=$i?>" id="text_slect_payment_amount<?=$i?>" value="<?= $_POST['payment_amount'.$i] ?>" size="8" maxlength="10"/></td>
  <td><input readonly="readonly" type="text" name="wait_amount<?=$i?>" id="text_slect_wait_amount<?=$i?>" value="<?= $_POST['wait_amount'.$i] ?>" size="8" maxlength="150"/></td> 
  <td><input type="text" class="number" id="text_this_payment_amount<?=$i?>" name="this_payment_amount<?=$i?>" onkeyup="check(<?=$i?>)"  onblur="checktotal()"     value="<?=$_POST['this_payment_amount'.$i]?>"     size="8" maxlength="10"/><span style="color:red">*</span></td>
 <td><input type="text" name="line_remark<?=$i?>" value="<?=$_POST['line_remark'.$i]?>"   size="8" maxlength="100"/> </td>
  <td><input type="text" readonly="readonly" name="invoice_date<?=$i?>"  id="text_slect_invoice_date<?=$i?>" value="<?= $_POST['invoice_date'.$i] ?>" size="10" maxlength="100"/> </td>
 

                     <td> <a onclick="delettr($(this));" style="padding:0px 5px;" href="javascript:;">删除</a></td>
					    <input type="hidden" name="unitprice<?=$i?>" id="text_slect_price<?=$i?>" value="<?=$_POST['unitprice'.$i]?>" size="4" maxlength="10"/> 
 <input  type="hidden" name="po_line_id<?=$i?>" id="text_slect_line_id<?=$i?>" value="<?=$_POST['po_line_id'.$i]?>" size="8" maxlength="25"/>

                         
                    </tr>
               
                    <?php }?>
					<tr> 
                    </table> </div>
                    
                 
                   <div class="centre">
                    <a onclick="addsave();">添加行</a>
                    
                    </div>

                    <div class="centre">
                    <input type="submit" id="submit" name="Save" value="提交">
                    </div>
    <?php
        }
    ?>
                    <input type="hidden" name="idcount" id='idcount' value="6"/>
                    <input type="hidden" name="JustSelectedACustomer" value="Yes"/>
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
	 function  checkhead(){
		var a=document.getElementById("text_slect_bank_onhand").value; 
		var b=document.getElementById("this_payment_amount_all").value;
		  
		 if(parseFloat(b) <0 ){
            document.getElementById("Prompt").innerHTML="付款金额不可以小于0！";
            document.getElementById("this_payment_amount_all").value="";
            document.getElementById("this_payment_amount_all").focus();
        }   else {
            document.getElementById("Prompt").innerHTML="";
        }

	}
   function  check(s1){
	    var a=document.getElementById("text_this_payment_amount"+s1).value; 
        var c=document.getElementById("text_slect_wait_amount"+s1).value;
        
        if(parseFloat(a) < 0 ){
			alert('本次付款金额不可以小于0！！！！');
            
            document.getElementById("text_this_payment_amount"+s1).value="";
            document.getElementById("text_this_payment_amount"+s1).focus();
        }  else if(parseFloat(a)>parseFloat(c) ){
			alert('付款金额超过待付金额！！！！'); 
            document.getElementById("text_this_payment_amount"+s1).value="";
            document.getElementById("text_this_payment_amount"+s1).focus();
        }  
     }
 function checktotal(){       
								   var  all_invoice_amount=0;               
                                for(var i=1 ; i < 50; i++){   
									if (document.getElementById("text_this_payment_amount" + i)==null)  {
									p=0;
										}
									else {								 
		                            
								   var  invoice_amount1=0;
                                   var invoice_amount1=document.getElementById("text_this_payment_amount"+i).value;   
                                     
								   if( invoice_amount1>0 )
								   {  
								   all_invoice_amount=Number(all_invoice_amount) + Number(invoice_amount1);                                 
                                   }
								   

								    }}
		            document.getElementById("payment_amount_all").value=all_invoice_amount;
		 }

   window.onload = function(){   
	    

          document.getElementById("submit").onclick = function(){
                                return check();
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

		function check56(){ 
        document.getElementById('text_slect_subinventory_code').disabled=true;
        document.getElementById('text_slect_vendor').disabled=true;
		}

        <?php for($i=1;$i<=50;$i++){?> 
        $('#btn_slect_buliao<?=$i?>').dialog({
            title:'选择待付款发票单',
            width: '1060px',
            height: 470,
            content:'url:Searchbuliao55.php?fwValue=<?=$i?>&cat=<?=$_POST['vendorcode']?>',
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
                this.content.document.getElementById('cat').value = 'buliao';
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
            content:'url:BtnSearchAPVendor66.php?fwValue=&cat=buliao',
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
	$(function(){
		$( "#text_slect_vendor" ).autocomplete({
			source: "autosearchvendor.php",
			minLength: 2,
			autoFocus: true
		});
	});
	$(function(){
		$( "#text_slect_name" ).autocomplete({
			source: "autosearchvendor2.php",
			minLength: 2,
			autoFocus: true
		});
	});

	function sel(){
		var name=$('#text_slect_vendor').val()
		$.get("","data="+name,function(res){
			name = res.split(":")		  
				$("#text_slect_name").val(name[0]) 
				$("#text_slect_address").val(name[1])
				$("#text_slect_contacts").val(name[2])
				$("#text_slect_currency_code").val(name[3])
		})	
	}    
 
	 function sel_name(){
		var name=$('#text_slect_name').val()
		$.get("","data2="+name,function(res_customer_name){
			name = res_customer_name.split(":")		  
				$("#text_slect_vendor").val(name[0]) 
				$("#text_slect_address").val(name[1])
				$("#text_slect_contacts").val(name[2])
				$("#text_slect_currency_code").val(name[3])
		})	
	}
		function checkall(thisform){for(var i=0;i<thisform.elements.length;i++){if(thisform.elements[i].type=="checkbox"&&thisform.elements[i].checked==false&&thisform.elements[i].name!="selectall"){thisform.elements[i].checked=true;}else if(thisform.elements[i].type=="checkbox"&&thisform.elements[i].checked==true&&thisform.elements[i].name!="selectall"){thisform.elements[i].checked=false;}} }

</script>
</body>

</html>
<?
include('includes/footer.inc');
?>

