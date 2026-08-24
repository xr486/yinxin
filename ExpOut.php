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
$Title = _('其它支出处理');
$ViewTopic= '其它支出处理';
$BookMark = '其它支出处理';
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
		if (substr($key, 0,11)=='receipt_num') 
	  {
        $errorflag = 0;
        $i = substr($key, 11);
     
          if ($_POST['this_payment_amount'.$i]=='') 
		  {
            $errorflag = 1;
            prnMsg($value.'支出款金额未填写,请确认！',error);
          }
		  if ($_POST['this_payment_amount'.$i]<0 ) 
		  {
            $errorflag = 1;
            prnMsg($value.'支出款金额不可以小于0,请确认！',error);
          }


          

		  $all_amount = $all_amount + $_POST['this_payment_amount'.$i] ;
		   
  
    }

	}

  }

 if ($_POST['payment_amount_all']<>$all_amount) 
  {
    $errorflag = 1;
   prnMsg($value.'支出款金额合计'.$all_amount.'不等于支出款总金额'.$_POST['payment_amount_all'].',请确认！',error);
  }
 
  

  if ($errorflag == 0) 
  {

	  $date = date('Ymd');
    $sql_num = "select  (CASE WHEN substr(max(transaction_num) ,-2,1) = 0 THEN RIGHT ('100' + (max(substr(transaction_num ,- 1)) + 1),2)
                ELSE substr(max(transaction_num),-2,2) + 1 END) order_number 
			    from fin_bank_transaction_headers_all 
				where substr(transaction_num,-10,8)= '" . $date . "' and transaction_num like 'QC%'";
    $result_num = DB_query($sql_num, $db);
    while ($v = DB_fetch_array($result_num)) 
	{
      if ($v['order_number'] == null) 
	  {
        $OrderNum = 'QC'.$date . '01';
      } else 
	  {
        $OrderNum =  'QC'. $date . $v['order_number'];
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
	   if (substr($key, 0,11)=='receipt_num') 
	  {
        $errorflag = 0;
        $i = substr($key, 11);
         
          if($_POST['this_payment_amount'.$i]=='')
		  {
            $_POST['this_payment_amount'.$i] = '0';
            $bumishu[$i] = 0;
          }  
        
		  $sql_line = "insert into fin_bank_transaction_lines_all 
                                (transaction_type,
							  bankaccountname,
							    bankchangenum,
							      transaction_num,
							   transaction_amount,dis_amount,
                                    narrative,
								currency_code,
                                 transaction_date,
                                creation_date,
                                   created_by,
                             last_update_date,
                             last_updated_by) 
			                      values (
								  '".$_POST['transaction_type']."',
								  '".$_POST['bankaccountname']."',
								  '".$_POST['bankchangenum']."',
								  '".$OrderNum."', 
								  '".$_POST['this_payment_amount'.$i]."','0',
								  '".$_POST['receipt_num'.$i]."',
								  '".$_POST['currency_code']."', 
								  '".$Delivery_date."',
								  '".$time."',
								  '".$_SESSION['UserID']."',
								  '".$time."',
								  '".$_SESSION['UserID']."')";
       
		$result = DB_query($sql_line,$db);

 
         
 
        /*  $sql = "update  so_lines_all
                    set payment_amount     = payment_amount     +'".$_POST['this_payment_amount'.$i]."'
                       ,dis_payment_amount = dis_payment_amount +'".$_POST['this_payment_dis_amount'.$i]."'
                  where  po_num  ='".$_POST['po_num'.$i]."' 
				   and   line ='".$_POST['so_line'.$i]."' ";
          $result = DB_query($sql,$db);
		  */
 
        }
      }
    }
  }

 

$sql = "insert into fin_bank_transaction_headers_all
                                (transaction_type,status,
							  bankaccountname,
							    bankchangenum,
							      transaction_num,
							   transaction_amount,dis_amount,
                                  other_person,
                                    narrative,
								currency_code,
                                 transaction_date,
                                creation_date,
                                   created_by,
                             last_update_date,
                             last_updated_by) 
			                      values ('".$_POST['transaction_type']."','核准',
								  '".$_POST['bankaccountname']."',
								  '".$_POST['bankchangenum']."',
								  '".$OrderNum."',
								  '".$_POST['payment_amount_all']."','0',
								  '".$_POST['other_person']."',
								  '".$_POST['Header_Remark']."',
								  '".$_POST['currency_code']."',
								  '".$Delivery_date."',
								  '".$time."',
								  '".$_SESSION['UserID']."',
								  '".$time."',
								  '".$_SESSION['UserID']."')";
      // echo $sql;
		$result = DB_query($sql,$db);

    $sql1="UPDATE fin_bank_alls 
                    SET   bank_onhand = bank_onhand - '" . $_POST['payment_amount_all']. "'				
					,last_update_date='" . $time. "'
                    ,last_updated_by='" . $_SESSION['UserID'] . "'
                    WHERE  bankaccountname='".$_POST['bankaccountname']."'
                    "; 	
		$result = DB_query($sql1,$db) ;

    DB_Txn_Commit($db);
	if ($k>0) {
    prnMsg('其它支出款'.$_POST['OrderNum'].'完成！',success);
    echo "<script>location.href='index.php';</script>";
	}

   
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
<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="其它支出处理" alt="其它支出处理">其它支出处理</p>
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
<tr>

  <td bgcolor="#87CEFA">银行账户名称:</td>
  <td colspan="3"><input type="text" required="required" name="bankaccountname" id="text_slect_bankaccountname" value="<?=$_POST['bankaccountname']?>" size="40" maxlength="250" onblur="sel()"/>
  <a class="btn btn-info btn-xs" id="btn_slect_bank<?=$i?>" hfre="###" title="选择银行账户">选择</a> </td>
   <td>银行名称:</td>
  <td colspan="4"><input readonly="readonly" type="text"   name="mybankname" id="text_slect_mybankname" value="<?=$_POST['mybankname']?>" size="50" maxlength="100"/></td>
  </tr>
<tr>
  <td>账户余额：</td>
  <td  ><input readonly="readonly" type="text"   name="bank_onhand" id="text_slect_bank_onhand" value="<?=$_POST['bank_onhand']?>" size="5" maxlength="10"/></td>  
  <td>币别：</td>
  <td><input readonly="readonly" type="text"   name="currency_code" id="text_slect_currency_code" value="<?=$_POST['currency_code']?>"  size="5" maxlength="10"/></td>  
  <td>银行账号:</td>
  <td colspan="4"><input readonly="readonly" type="text"   name="mybankaccount" id="text_slect_mybankaccount" value="<?=$_POST['mybankaccount']?>" size="50" maxlength="100"/></td>
</tr>


<tr>

</tr>

<tr>
<td>单位/个人名称:</td>
  <td colspan="9" ><input type="text" required="required" maxlength="150" size="100" name="other_person"  value="<?=$_POST['other_person']?>" /> </td>
 
 </tr>


<tr>
    <td>支出类型:</td> 
  	
  <td>
       <select name="transaction_type" id="">
<?php
$sql = "select exp_type_name from  fin_exp_types where type_code='支出' order by exp_type_name";
$result = DB_query($sql, $db);
while ($v = DB_fetch_array($result)) {
    if ($v['exp_type_name'] == $_POST['transaction_type']) {
        ?>
                                <option value="<?= $v['exp_type_name'] ?>" selected="selected"><?= $v['exp_type_name'] ?></option>
                            <?php } else { ?>
                                <option value="<?= $v['exp_type_name'] ?>"><?= $v['exp_type_name'] ?></option>
                            <?php
                            }
                        }
                        ?>
                    </select>
                </td>


 <td>收款/转账单号:</td>
  <td colspan="3" ><input type="text" required="required" maxlength="100" size="30" name="bankchangenum"  value="<?=$_POST['bankchangenum']?>" /> </td>
 
    <td>支出金额:</td>
  <td   ><input type="text" class="number" required="required"   id="this_payment_amount_all"  onblur="checkhead()"  name="payment_amount_all"  value="<?=$_POST['payment_amount_all']?>" size="10" maxlength="30"/> </td>
   
</tr> 

<tr>
  <td>支出日期：</td>
  <td><input type="text" name="Delivery_date" maxlength="20" size="10" required="required" value="<?=$_POST['Delivery_date']?>" onfocus="WdatePicker() "></td>
  <td>支出备注：</td>
  <td colspan="5"><input type="text"  maxlength="200" size="70" name="Header_Remark"  value="<?=$_POST['Header_Remark']?>" size="20" maxlength="20"/> </td>
   
</tr>

</table>
<div class="centre">
<input type="submit" name="Hearder" value="保存支出头信息输入明细">
</div>
<input type="hidden" name="PageOffset" value="1"/><br/>
<?php
if (isset($_POST['currency_code']) and $_POST['currency_code'] != '') {
 

?>
<input id="purchase_table_lastRow" name="purchase_table_lastRow" type=hidden value="">

<div class="centre"> 
                        <p id="Prompt" style="color: red;font-size: 20px"></p>
                    </div>

<table id="purchase_table" cellpadding="2" class="selection">
 
<tr id="list-top">
  <th width="350">事项说明</th>
  <th width="50" >金额</th>
  <th width="50" align="center">操作</th>
 
<?php for($i=1;$i<=50;$i++){?>
 <tr id="purchase_table_<?=$i?>" <?php echo $i>10&&$_POST['receipt_num'.$i]==''?'style="display:none"':''?> class="mouse click">

  <td> <input type="text" name="receipt_num<?=$i?>" id="text_slect_order_number<?=$i?>" value="<?=$_POST['receipt_num'.$i]?>" size="60" maxlength="150"/>
  
  
  <td><input type="text"  name="this_payment_amount<?=$i?>"  class="number"  id="this_payment_amount<?=$i?>" value="<?=$_POST['this_payment_amount'.$i]?>"  onblur="check(<?=$i?>)"   size="12" maxlength="10" /></td>

  <td>  <a onclick="delettr($(this));" style="padding:0px 5px;" href="javascript:;">删除</a>
 

  </td>
</tr>
<?php 
 $i=$i+1;
  }?>

	<td ><input type="hidden" name="flag" value="<?=$i-1?>" size="15" maxlength="45"/></td> 
</table>

<div class="centre">
							<a onclick="addsave();">添加行</a>

						</div>

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
    
	 function  checkhead(){
		var a=document.getElementById("text_slect_bank_onhand").value; 
		var b=document.getElementById("this_payment_amount_all").value;
		  
		if(parseInt(b)>parseInt(a)){
            document.getElementById("Prompt").innerHTML="付款金额不可以大于账号余额！"+a;
            document.getElementById("this_payment_amount_all").value="";
            document.getElementById("this_payment_amount_all").focus();
        }  else if(parseInt(b) <0 ){
            document.getElementById("Prompt").innerHTML="付款金额不可以小于0！";
            document.getElementById("this_payment_amount_all").value="";
            document.getElementById("this_payment_amount_all").focus();
        }   else {
            document.getElementById("Prompt").innerHTML="";
        }

	}
    
	 function  check(s1){ 
        var b=document.getElementById("this_payment_amount"+s1).value;  
      if(parseInt(b) <= 0 ){
            document.getElementById("Prompt").innerHTML="支出金额"+b+"不可以小于0！";
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
            title:'选择已付款的业务订单',
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

