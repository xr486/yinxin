<?php

include('includes/session.inc');
$Title = _('其它收入/支出修改');
$ViewTopic= '其它收入/支出修改';
$BookMark = '其它收入/支出修改';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

if (isset($_GET['Updatepo_num']) ) {
$_POST['transaction_num']=$_GET['Updatepo_num'];
}



unset($result);
if (isset($_POST['Save'])) 
{
  $errorflag = 0;
  $all_amount = 0;
  $all_invoice_dis_amount = 0;
  $k =0;
   for ($i=1; $i<=$_POST['flag'];$i++)
  {
    if($_POST['status'.$i]<>'')
	{   
     
          if ($_POST['transaction_amount'.$i]=='') 
		  {
            $errorflag = 1;
            prnMsg($value.'行金额未填写,请确认！',error);
          }
		  if ($_POST['transaction_amount'.$i]<0 ) 
		  {
            $errorflag = 1;
            prnMsg($value.'行金额不可以小于0,请确认！',error);
          }

 
          if ($_POST['narrative'.$i]=='') 
		 {
            $errorflag = 1;
            prnMsg($value.'备注为空,请确认！',error);
         }

		  $all_amount = $all_amount + $_POST['transaction_amount'.$i] ;
  
    }


  }
 
 if ($_POST['transaction_amount']<>$all_amount) 
  {
    $errorflag = 1;
   prnMsg($value.'立账金额合计'.$all_amount.'不等于发票总金额'.$_POST['transaction_amount'].',请确认！',error);
  }
 


  if ($errorflag == 0) 
  {
    $Delivery_date = strtotime($_POST['Delivery_date']);
    DB_Txn_Begin($db);
    $time = time();
    $delivery_amount = 0;
    $k =0;
   for ($i=1; $i<=$_POST['flag'];$i++)
	{
       $k = $k +1; 
	   if($_POST['status'.$i]<>'')
		   {
         
          if($_POST['transaction_amount'.$i]=='')
		  {
            $_POST['transaction_amount'.$i] = '0';
            $bumishu[$i] = 0;
          }  
          if($_POST['this_dis_quantity'.$i]=='')
		  {
            $_POST['this_dis_quantity'.$i] = 0;
          }
 
    
          $sql = "update  fin_bank_transaction_lines_all
                    set transaction_amount     =  '".$_POST['transaction_amount'.$i]."'
                       ,narrative =  '".$_POST['narrative'.$i]."'
                  where  transaction_num  ='".$_POST['transaction_num']."' 
				   and transaction_id ='".$_POST['transaction_id'.$i]."' ";
          $result = DB_query($sql,$db);
		   
 
        }
      } 	
      if($_POST['dis_amount']=='')
		  {
            $_POST['dis_amount'] = 0;
          }
		  if($_POST['tax_amount_all']=='')
		  {
            $_POST['tax_amount_all'] = 0;
          }
	  $sql = "update  fin_bank_transaction_headers_all
                    set transaction_amount     = '".$_POST['transaction_amount']."'
                       ,dis_amount = '".$_POST['dis_amount']."'
					   ,tax_amount = '".$_POST['tax_amount_all']."'
					   ,narrative = '".$_POST['Header_Remark']."'
					   ,status='建立'
                  where  transaction_num  ='".$_POST['transaction_num']."'   ";
          $result = DB_query($sql,$db);
          
    DB_Txn_Commit($db);
	if ($k>0) {
    prnMsg('其他收入支出单'.$_POST['transaction_num'].'修改完成！',success);
    echo "<script>location.href='index.php';</script>";
	}
        
    } 
   
}


if (isset($_POST['Delete'])) 
{
  $errorflag = 0;
  $all_amount = 0;
  $all_invoice_dis_amount = 0;
  
  if ($errorflag == 0) 
  {
     
    $sql = "delete from fin_bank_transaction_headers_all 
                  where  transaction_num  ='".$_POST['transaction_num']."'   ";
     $result = DB_query($sql,$db);

	 $sql2 = "delete from fin_bank_transaction_lines_all 
                  where  transaction_num  ='".$_POST['transaction_num']."'   ";
     $result = DB_query($sql2,$db);
          
    DB_Txn_Commit($db);
	 
    prnMsg('其他收入单据'.$_POST['transaction_num'].'删除完成！',success);
    echo "<script>location.href='index.php';</script>";
	 
}
}
?>
 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>其它收入/支出修改</title>
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
<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="其它收入/支出修改" alt="其它收入/支出修改">其它收入/支出修改</p>
<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST"><input type="hidden" name="time"
value="<?=$time?>">
<div>
<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
<table class="selection">

<?php
	 

$sql ="select pha.transaction_type,
			      pha.bankaccountname,
							    pha.bankchangenum,
							      pha.transaction_num,
							   pha.transaction_amount,
								   pha.tax_amount,
                                  pha.other_person,
                                    pha.narrative,
								pha.currency_code,
                                 pha.transaction_date,
                pha.creation_date,pha.created_by,pha.status,type_code
	from fin_bank_transaction_headers_all pha,fin_exp_types a
            where pha.status='拒绝'
			and pha.transaction_type=a.exp_type_name
			and type_code IN ('支出','收入')
AND transaction_num= '" .$_POST['transaction_num'] . "'";
	//echo $sql;
	$result = DB_query($sql,$db);
    if (DB_num_rows($result)==0) {
        unset($result);
        prnMsg(_('没有预测，请重新输入条件查询！') ,'error');
	 }
 while ($myrow = DB_fetch_array($result))  {
        $_POST['other_person']=$myrow['other_person'] ;  
	   $_POST['currency_code']=$myrow['currency_code'] ; 
	   $_POST['invoice_date']=$myrow['transaction_date'] ; 
	   $_POST['bankchangenum']=$myrow['bankchangenum'] ; 
	   $_POST['transaction_num']=$myrow['transaction_num'] ; 
	   $_POST['bankaccountname']=$myrow['bankaccountname'] ;
	   $_POST['narrative']=$myrow['narrative'] ;
	   $_POST['transaction_amount']=$myrow['transaction_amount'] ; 
	   $_POST['type_code']=$myrow['type_code'] ; 
	   $_POST['transaction_type']=$myrow['transaction_type'] ;
 }     
?>
<tr>
 <td>流水号:</td>
  <td><input type="text" readonly="readonly"   name="transaction_num"  value="<?=$_POST['transaction_num']?>" size="16" maxlength="25"/> </td>
  <td>收款/转账单号:</td>
  <td colspan="2"><input type="text" readonly="readonly"   name="bankchangenum"  value="<?=$_POST['bankchangenum']?>" size="25" maxlength="250"/> </td>
<td>银行账户名:</td>
  <td colspan="5"><input readonly="readonly" type="text" name="bankaccountname" id="text_slect_name" value="<?=$_POST['bankaccountname']?>" size="50" maxlength="250"/></td>

 
</tr>



<tr>
 <td>单位/个人名称:</td>
  <td  colspan="3"><input type="text" readonly="readonly"  name="other_person" id="text_slect_vendor" value="<?=$_POST['other_person']?>" size="65"  /> </td>
   
   <td> 日期:</td>
  <td><input type="text" readonly="readonly"  name="invoice_date" maxlength="20" size="9"  value="<?=date('Y-m-d',$_POST['invoice_date'])?>"onfocus="WdatePicker() "></td>
  
  <td>币别：</td>
  <td><input type="text" readonly="readonly"  name="currency_code" id="text_slect_currency_code" value="<?=$_POST['currency_code']?>" size="6" maxlength="25"/> </td>
</tr>


<td>费用类别:</td>
  <td  ><input type="text"  readonly="readonly"  maxlength="100" size="10" name="type_code"  value="<?=$_POST['type_code']?>" size="10" maxlength="30"/> </td>
   <td> 类型:</td>
  <td  ><input type="text"  readonly="readonly"  maxlength="100" size="10" name="transaction_type"  value="<?=$_POST['transaction_type']?>" size="10" maxlength="30"/> </td>
  <td> 金额:</td>
  <td  ><input type="text" class="number" maxlength="100" size="10" name="transaction_amount"  value="<?=$_POST['transaction_amount']?>" size="10" maxlength="30"/> </td>
  
  
 
<td> 备注：</td>
<td colspan="4"><input type="text"  maxlength="200" size="50" name="Header_Remark"  value="<?=$_POST['Header_Remark']?>"/> </td>

</tr><tr>
  <td  >上传新附件：</td>
	 
	<?php 
	echo '  <td><a  target="_blank"  href="' . $RootPath . '/SussCreateModify.php?OrderNum=' . $_POST['transaction_num'] . '">上传</td>';
	?>
</tr>
</table>

<input type="hidden" name="PageOffset" value="1"/><br/>
<?php
if (isset($_POST['transaction_num']) and $_POST['transaction_num'] != '') {

	$sql ="select  c.transaction_amount, c.narrative
				from fin_bank_transaction_lines_all c
				where  c.transaction_num 	= '" .$_POST['transaction_num'] . "'";
	// echo $sql;
	$result = DB_query($sql,$db);
    if (DB_num_rows($result)==0) {
		$line_count=DB_num_rows($result);
        unset($result);
        prnMsg(_('该客户没有需要开票，请重新输入条件查询！') ,'error');
    }

?>
<input id="purchase_table_lastRow" name="purchase_table_lastRow" type=hidden value="">

<div class="centre"> 
                        <p id="Prompt" style="color: red;font-size: 20px"></p>
                    </div>

<table id="purchase_table" cellpadding="2" class="selection">
 
<tr id="list-top">
  <th width="550">事项说明</th>
  <th width="120">付款金额</th>
  
  <th width="15" align="center">选择</th>
 
</tr>
<?php   
$i=1;
 
 while ($myrow = DB_fetch_array($result)  )  {
	
	 ?>
		 
<tr id="purchase_table_<?=$i?>" >

  
  <td><input  type="text" name="narrative<?=$i?>" id="text_slect_remark<?=$i?>" value="<?= $myrow['narrative'] ?>" size="75" maxlength="150"/></td>

 
  <td><input type="text" class="number"  name="transaction_amount<?=$i?>" onblur="check(<?=$i?>)"  id="transaction_amount<?=$i?>"    value="<?=  $myrow['transaction_amount']?>"     size="18" maxlength="100"/></td>
<td><input type="checkbox" name="status<?=$i?>" checked /></td>
 <td> 

  <input type="hidden"  name="transaction_id<?=$i?>"  value="<?= $myrow['transaction_id'] ?>" size="8" maxlength="10"/>
  <input type="hidden"  name="wait_amount<?=$i?>"  id="text_wait_amount<?=$i?>"  value="<?= $myrow['wait_amount'] ?>" size="8" maxlength="10"/>
  </td>
</tr>
<?php 
 $i=$i+1;
  
 }
  ?>

  	<tr><td colspan="11"><p><input type="checkbox" name="selectall" onclick="checkall(this.form);"/>全选/取消全选</p></td></tr>
	<td ><input type="hidden" name="flag" value="<?=$i-1?>" size="15" maxlength="45"/></td> 
</table>

<div class="centre">
<input type="submit" name="Save" value="提交">
<input type="submit" name="Delete" value="删除">
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
    
function  check(s1){
	    var a=document.getElementById("text_this_invoice_amount"+s1).value;
        var b=document.getElementById("text_this_invoice_dis_amount"+s1).value;
        var c=document.getElementById("text_wait_amount"+s1).value;
        var d=parseFloat(a) + parseFloat(b);
		var checkamount=document.getElementById("text_check_amount"+s1).value;
		if (checkamount>0) {
      if(parseFloat(a)>parseFloat(c)){
            document.getElementById("Prompt").innerHTML="本次开票金额不可以大于待开票金额！！！！";
           // document.getElementById("text_this_invoice_amount"+s1).value="";
          //  document.getElementById("text_this_invoice_amount"+s1).focus();
        } else if(parseFloat(b)>parseFloat(c)){
            document.getElementById("Prompt").innerHTML="本次免开票金额不可以大于待开票金额！！！！";
           // document.getElementById("text_this_invoice_dis_amount"+s1).value="";
           // document.getElementById("text_this_invoice_dis_amount"+s1).focus();
        }else if(parseFloat(d)>parseFloat(c)){
            document.getElementById("Prompt").innerHTML="开票金额+免开票金额不可以大于待开票金额！！！！";
          //  document.getElementById("text_this_invoice_dis_amount"+s1).value="";
          //  document.getElementById("text_this_invoice_dis_amount"+s1).focus();
        } else {
            document.getElementById("Prompt").innerHTML="";
        }
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
        <?php for($i=1;$i<=50;$i++){?> 
        $('#btn_slect_po<?=$i?>').dialog({
            title:'选择未开票的采购入库单',
            width: '950px',
            height: 520,
            content:'url:SearchNoInvoicePo2.php?fwValue=<?=$i?>&cat=<?=$_POST['customer_code']?>',
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


		$('#btn_slect_vendor').dialog({
            title:'选择客户',
            width: '950px',
            height: 470,
            content:'url:BtnSearchAPVendor3.php?fwValue=&cat=buliao',
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
	function checkall(thisform){for(var i=0;i<thisform.elements.length;i++){if(thisform.elements[i].type=="checkbox"&&thisform.elements[i].checked==false&&thisform.elements[i].name!="selectall"){thisform.elements[i].checked=true;}else if(thisform.elements[i].type=="checkbox"&&thisform.elements[i].checked==true&&thisform.elements[i].name!="selectall"){thisform.elements[i].checked=false;}} }
</script>
</body>

</html>
<?
 
include('includes/footer.inc');
?>

