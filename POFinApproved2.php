<?php
if(isset($_GET['data'])){
	 
	 include_once("connect.php"); 
	 $sql = "select * from fin_bank_alls where bankaccountname = '".$_GET['data']."'";
	 $result_num = mysql_query($sql, $db);
	 $res = mysql_fetch_assoc($result_num);
	 echo $res['bankaccountname'].':'.$res['bankname'].':'.$res['bankaccount'].':'.$res['currency_code'].':'.$res['bank_onhand'];
	 return ;
 } 
include('includes/session.inc');
$Title = _('采购单财务签核');
$ViewTopic= '采购单财务签核';
$BookMark = '采购单财务签核';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

unset($result);

if (isset($_GET['Updatepo_num']) ) {
$_POST['po_num']=$_GET['Updatepo_num'];
}

if (isset($_POST['RejectBack']) ) {
$time=time();
	    $sql2="UPDATE po_headers_all 
                    SET   	status= 'REJECTED', 	
					fin_approved_date='" . $time. "'
					, 	fin_approved_by='" . $_SESSION['UserID'] . "'
					,last_update_date='" . $time. "'
                    ,last_updated_by='" . $_SESSION['UserID'] . "'
                    WHERE  po_num='".$_POST['po_num']."'
                    "; 	
		$result = DB_query($sql2,$db);
		echo $sql2;
		header("Location: SucssPOFinapproved.php?OrderNum=$TransNum");
}


if (isset($_POST['UpdateStatus']) ) {
$time=time();
        $errorflag = 0;
         $all_amount = 0;

 
 
  if ($errorflag ==0) 
  {
    $date = date('Ymd');
    $sql_num = "select  (CASE WHEN substr(max(transaction_num) ,-2,1) = 0 THEN RIGHT ('100' + (max(substr(transaction_num ,- 1)) + 1),2)
                ELSE substr(max(transaction_num),-2,2) + 1 END) order_number 
			    from fin_bank_transaction_headers_all 
				where substr(transaction_num,-10,8)= '" . $date . "' and transaction_num like 'AF%'";
    $result_num = DB_query($sql_num, $db);
    while ($v = DB_fetch_array($result_num)) 
	{
      if ($v['order_number'] == null) 
	  {
        $OrderNum = 'AF'.$date . '01';
      } else 
	  {
        $OrderNum =  'AF'. $date . $v['order_number'];
      }
    }
 
//产生单号 End

    DB_Txn_Begin($db);
    $Delivery_date = strtotime($_POST['Delivery_date']);
            $time = strtotime(Date('Y-m-d H:i:s'));
        
    if ($_POST['tax_amount_all']=='') {
			$_POST['tax_amount_all']=0;
			}

			$sql_line = "insert into fin_bank_transaction_lines_all 
                                (transaction_type,
							  bankaccountname,
							    bankchangenum,
							      transaction_num,
							   transaction_amount,
								   tax_amount,
                                  vendor_code, 
								currency_code,po_num,
                                 transaction_date,
                                creation_date,
                                   created_by,
                             last_update_date,
                             last_updated_by) 
			                      values ('".'AP付款'."',
								  '".$_POST['bankaccountname']."',
								  '".$_POST['bankchangenum']."',
								  '".$OrderNum."',
								  '".$_POST['payment_amount_all']."',
                                  '0',
								  '".$_POST['vendor_code']."', 
								  '".$_POST['currencycode']."',
								  '".$_POST['po_num']."',
								  '".$Delivery_date."',
								  '".$time."',
								  '".$_SESSION['UserID']."',
								  '".$time."',
								  '".$_SESSION['UserID']."')";
       
		$result = DB_query($sql_line,$db);

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
			                      values ('".'AP付款'."','核准',
								  '".$_POST['bankaccountname']."',
								  '".$_POST['bankchangenum']."',
								  '".$OrderNum."',
								  '".$_POST['payment_amount_all']."',
                                  '".$_POST['tax_amount_all']."',
								  '".$_POST['vendor_code']."',
								  '".$_POST['Header_Remark']."',
								  '".$_POST['currencycode']."',
								  '".$Delivery_date."',
								  '".$time."',
								  '".$_SESSION['UserID']."',
								  '".$time."',
								  '".$_SESSION['UserID']."')";
      // echo $sql;
		$result = DB_query($sql,$db);

		 $sql2="UPDATE po_headers_all 
                    SET   	fin_approved= 'Y'
					,payment_amount='" . $_POST['payment_amount_all']. "'	
					, 	fin_approved_date='" . $time. "'
					, 	fin_approved_by='" . $_SESSION['UserID'] . "'
					,last_update_date='" . $time. "'
                    ,last_updated_by='" . $_SESSION['UserID'] . "'
                    WHERE  po_num='".$_POST['po_num']."'
                    "; 	
		$result = DB_query($sql2,$db);

		 $sql2="UPDATE fin_bank_alls 
                    SET   bank_onhand = bank_onhand - '" . $_POST['payment_amount_all']. "'				
					,last_update_date='" . $time. "'
                    ,last_updated_by='" . $_SESSION['UserID'] . "'
                    WHERE  bankaccountname='".$_POST['bankaccountname']."'
                    "; 	
		$result = DB_query($sql2,$db);
		
        
       // header("Location: SucssPOFinapproved.php?OrderNum=$Updateorder_number");
	   if ($errorflag==0) {
			DB_Txn_Commit($db);
			prnMsg('其他原因出库单编号'.$TransNum.'建立成功！',success);
			}
			header("Location: SucssPOFinapproved.php?OrderNum=$TransNum");


        }//插入交易表


        }



?>
 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>新建订单</title>
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
<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="采购单财务签核" alt="采购单财务签核">采购单财务签核</p>
<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST"><input type="hidden" name="time"
value="<?=$time?>">
<div>
<?php
	 

$sql ="SELECT a.po_num, a.status, note,a.po_invoice_amount,a.payment_term,a.po_payment_amount,a.need_date,a.app_remark,a.creation_date,a.youhui_amount, a.po_all_amount,a.po_all_amount, b.vendor_name,b.vendor_code
FROM po_headers_all a, vendors b
WHERE a.vendor_code = b.vendor_code
AND po_num= '" .$_POST['po_num'] . "'";
	//echo $sql;
	$result = DB_query($sql,$db);
    if (DB_num_rows($result)==0) {
        unset($result);
        prnMsg(_('没有预测，请重新输入条件查询！') ,'error');
	 }
 while ($myrow = DB_fetch_array($result))  {
       $_POST['vendor_code']=$myrow['vendor_code'] ;
       $_POST['vendor_name']=$myrow['vendor_name'] ;  
       $_POST['youhui_amount']=$myrow['youhui_amount'] ;  
       $_POST['amount']=$myrow['amount'] ;  
       $_POST['note']=$myrow['note'] ;   
       $_POST['po_num']=$myrow['po_num'] ;  
       $_POST['po_all_amount']=$myrow['po_all_amount'] ;
       $_POST['po_payment_amount']=$myrow['po_payment_amount'] ;
       $_POST['po_invoice_amount']=$myrow['po_invoice_amount'] ;  
       $_POST['payment_term']=$myrow['payment_term'] ;  
       
 }

 
	 
	  if (!isset($_POST['Delivery_date'])) {
      $_POST['Delivery_date'] = Date('Y-m-d');
     }
 
     
?>

<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
<table class="selection">

<tr>
  <td>采购单号:</td>
  <td><input type="text" readonly="readonly"   name="po_num"  value="<?=$_POST['po_num']?>" size="16" maxlength="25"/> </td>
 <td>供应商代码:</td>
  <td><input type="text" readonly="readonly"  name="vendor_code" id="text_slect_vendor" value="<?=$_POST['vendor_code']?>" size="10" maxlength="25"/> </td>
   <td>供应商名称:</td>
  <td colspan="3"><input readonly="readonly" type="text" name="vendor_name" id="text_slect_name" value="<?=$_POST['vendor_name']?>" size="50" maxlength="250"/></td>
  
 
</tr>
<tr>
<td>订单总总额:</td>
  <td><input type="text" readonly="readonly"  name="po_all_amount" id="text_slect_po_all_amount" value="<?=$_POST['po_all_amount']?>" size="10" maxlength="25"/> </td>
<td>订单应付金额:</td>
  <td><input type="text" readonly="readonly"  name="po_payment_amount" id="text_slect_amount" value="<?=$_POST['po_payment_amount']?>" size="10" maxlength="25"/> </td>
  <td>优惠金额:</td>
  <td><input type="text" readonly="readonly"  name="youhui_amount" id="text_slect_youhui_amount" value="<?=$_POST['youhui_amount']?>" size="10" maxlength="25"/> </td>
  <tr>
  <td>订单应开票金额:</td>
  <td><input type="text" readonly="readonly"  name="po_invoice_amount" id="text_slect_po_invoice_amount" value="<?=$_POST['po_invoice_amount']?>" size="10" maxlength="25"/> </td>
  <td>供应商付款条件:</td>
  <td><input type="text" readonly="readonly"  name="payment_term" id="text_slect_payment_term" value="<?=$_POST['payment_term']?>" size="16" maxlength="25"/> </td>
   </tr>
   <tr>
   <td>备注:</td>
  <td colspan="3"><input readonly="readonly" type="text" name="note"  value="<?=$_POST['note']?>" size="50" maxlength="250"/></td>
    </tr>
</tr>
<tr>
  <td bgcolor="#87CEFA">银行账户名称:</td>
  <td colspan="3"><input type="text" required="required" name="bankaccountname" id="text_slect_bankaccountname" value="<?=$_POST['bankaccountname']?>" size="40" maxlength="250" onblur="sel()"/>
  <a class="btn btn-info btn-xs" id="btn_slect_bank<?=$i?>" hfre="###" title="选择银行账户">选择</a> </td>
   <td>银行名称:</td>
  <td colspan="34"><input readonly="readonly" type="text"   name="mybankname" id="text_slect_mybankname" value="<?=$_POST['mybankname']?>" size="50" maxlength="100"/></td>
  </tr>
  <tr>
  <td>账户余额：</td>
  <td><input type="text" readonly="readonly"  name="currencycode" id="text_slect_bank_onhand" value="<?=$_POST['bank_onhand']?>" size="10" maxlength="25"/> </td>
 <td>币别：</td>
  <td><input type="text" readonly="readonly"  name="currencycode" id="text_slect_currency_code" value="<?=$_POST['currencycode']?>" size="10" maxlength="25"/> </td>
   <td>付款日期:</td>
  <td><input type="text" name="Delivery_date" maxlength="20" size="12" required="required" value="<?=$_POST['Delivery_date']?>"onfocus="WdatePicker() "></td>
</tr>
<tr>
   <td>付款/转账单号:</td>
  <td colspan="3" ><input type="text"   maxlength="100" size="30" name="bankchangenum"  value="<?=$_POST['bankchangenum']?>" /> </td>
  <td>银行账号:</td>
  <td colspan="2"><input readonly="readonly" type="text"   name="mybankaccount" id="text_slect_mybankaccount" value="<?=$_POST['mybankaccount']?>" size="50" maxlength="100"/></td>
</tr>



  <td bgcolor="#87CEFA">付款金额:</td>
  <td  colspan="3"><input type="text" class="number" required="required"  name="payment_amount_all" id="payment_amount_all"  value="<?=$_POST['payment_amount_all']?>" size="30" maxlength="30" onblur="checkall()"/> </td>
  
 
<td>付款备注：</td>
<td colspan="4"><input type="text"  maxlength="200" size="50" name="Header_Remark"  value="<?=$_POST['Header_Remark']?>"/> </td>

  <td  ><input  type="hidden"   name="currency_code" id="text_slect_currencycode" value="<?=$_POST['currency_code']?>" size="5" maxlength="10"/></td>
   <td  ><input type="hidden" class="number" required="required" maxlength="100" size="20" name="tax_amount_all"  value="<?=$_POST['tax_amount_all']?>" size="20" maxlength="20"/> </td>

</tr>

<div class="centre"> 
                        <p id="Prompt" style="color: red;font-size: 20px"></p>
                    </div>
</table>
<?php

$sql ="select po_line_id,po_num,a.line,status,a.stockid,b.item_desc,quantity,price,line_amount,ifnull(quantity_received,0) quantity_received,ifnull(quantity_accepted,0) quantity_accepted,ifnull(quantity_deliveried,0) quantity_deliveried,ifnull(quantity_cancelled,0) quantity_cancelled,a.uom,b.item_name,a.line_remark
				from po_lines_all a,sf_item_no b where a.stockid=b.item_no and po_num = '" .$_POST['po_num'] . "'";
	//echo $sql;
	$result = DB_query($sql,$db);
    if (DB_num_rows($result)==0) {
        unset($result);
        prnMsg(_('没有需要付款的行，请重新输入条件查询！') ,'error');
    }
 echo '<br />
                    <table cellpadding="2" class="selection">';  

    echo '<tr> 	  <th class="ascending"  >' . _('行') . '</th>                 
					<th width="150" >' . _('料号') . '</th>
					<th width="150" >' . _('料号名称') . '</th> 
					<th width="150" >' . _('料号描述') . '</th> 
					<th   >' . _('单位') . '</th>
					   <th  >' . _('数量') . '</th>				
					<th   >' . _('单价') . '</th>			
					<th   >' . _('金额') . '</th> 
					  <th  >' . _('备注') . '</th>  
            </tr>'; 
$i=0;
 while ($myrow = DB_fetch_array($result))  {
	 $i=$i+1;
 echo ' 		<td>' . $myrow['line'] . '</td>
			    <td>' . $myrow['stockid']  . '</td>
				<td>' . $myrow['item_name']  . '</td>
				<td>' . $myrow['item_desc']  . '</td>
				<td>' . $myrow['uom']  . '</td> 
			    <td>' . $myrow['quantity'] . '</td>
			    <td>' . $myrow['price'] . '</td>
			    <td>' . $myrow['line_amount'] . '</td>
			    <td>' . $myrow['line_remark'] . '</td>
				 
                ';?>
  <?php 
		 
		echo '
		<input type="hidden"  name="po_line_id'.$i.'" value="' . $myrow['po_line_id'] . '" />
		<input type="hidden"    id="po_num'.$i.'" name="po_num'.$i.'" value="' . $myrow['po_num'] . '"  size="8" /></t
		<input type="hidden"    id="po_line'.$i.'" name="po_line'.$i.'" value="' . $myrow['line'] . '"  size="8" /></t
          </td>';
 
           
          echo  '
            </tr>';
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
 
 
		function  checkall(){
		var a=document.getElementById("text_slect_bank_onhand").value;
		var b=document.getElementById("text_slect_amount").value;
		var c=0;//document.getElementById("text_slect_youhui_amount").value;
		var d=document.getElementById("payment_amount_all").value;
		if(c==""){
			c=0;
		}
		e=Number(b) - Number(c);
		if(parseInt(d)>parseInt(a)){
            document.getElementById("Prompt").innerHTML="付款金额"+d+"不可以大于账号余额！"+a;
            document.getElementById("payment_amount_all").value="";
            document.getElementById("payment_amount_all").focus();
        }  else if(parseInt(d) <0 ){
            document.getElementById("Prompt").innerHTML="付款金额不可以小于0！";
            document.getElementById("payment_amount_all").value="";
            document.getElementById("payment_amount_all").focus();
        } else if(parseInt(d)>parseInt(e)){
            document.getElementById("Prompt").innerHTML="付款金额不可以大于订单金额-优惠金额！";
            document.getElementById("payment_amount_all").value="";
            document.getElementById("payment_amount_all").focus();
        }  else {
            document.getElementById("Prompt").innerHTML="";
        }

	}

	$(function(){
		$( "#text_slect_bankaccountname" ).autocomplete({
			source: "autosearchbank.php",
			minLength: 2,
			autoFocus: true
		});
	});

	      
  
	function sel(){
		var name=$('#text_slect_bankaccountname').val()
		$.get("","data="+name,function(res){
			name = res.split(":")		   
				$("#text_slect_mybankname").val(name[1])
				$("#text_slect_mybankaccount").val(name[2])
				$("#text_slect_currency_code").val(name[3])
                $("#text_slect_bank_onhand").val(name[4])
   
		})	
	}    

</script>
</body>

</html>
<?
include('includes/footer.inc');
?>

