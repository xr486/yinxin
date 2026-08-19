<?php

include('includes/session.inc');
$Title = _('客户发票修改');
$ViewTopic= '客户发票修改';
$BookMark = '客户发票修改';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

if (isset($_GET['Updatepo_num']) ) {
$_POST['invoice_name']=$_GET['Updatepo_num'];
}
 

unset($result);

if (isset($_POST['UpdateStatus']) ) 
{ $time = time();
  $errorflag = 0;
  $line=0;
  if ($errorflag == 0) 
  {  	 	 	 	
    foreach ($_POST as $key => $value)
	{
      if (mb_substr($key,0,15)=='invoice_line_id') 
	  {
        $i =mb_substr($key,15);		  
        $time = strtotime(Date('Y-m-d H:i:s'));   
        if (isset($_POST['invoice_line_id'.$i]))
	    {   $line=$line+1;
			$linesql = "UPDATE ar_invoice_lines_all 
			      set   
				  line_remark ='" . $_POST['line_remark' . $i]  . "',
				  amount ='" . $_POST['amount' . $i]  . "',
				  last_update_date='" . $time. "'
                    ,last_updated_by='" . $_SESSION['UserID'] . "'
				where invoice_line_id='" . $_POST['invoice_line_id'.$i]. "'  ";
		    //echo $linesql; 
			$result = DB_query($linesql,$db);
            if ($_POST['invoice_type']=='红字发票') {
			 $sql2 = "update  so_lines_all
                    set invoice_amount     = invoice_amount  -  '".$_POST['amount' . $i]."' 
                  where  order_number  ='".$_POST['so_num'.$i]."'
				  and line= '".$_POST['so_line'.$i]."' ";
             $result2 = DB_query($sql2,$db);
			} else {
			 $sql2 = "update  so_lines_all
                    set invoice_amount     = invoice_amount  +  '".$_POST['amount' . $i]."' 
                  where  order_number  ='".$_POST['so_num'.$i]."'
				  and line= '".$_POST['so_line'.$i]."' ";
             $result2 = DB_query($sql2,$db);
			}

		}
	    DB_Txn_Commit($db);
      }
    }
  }//插入交易表
  if ($line > 0) {

	  $linesql = "update   ar_invoice_headers_all
	set status= '建立' 
	,invoice_amount='" . $_POST['invoice_amount']. "'
	,last_update_date='" . $time. "'
    ,last_updated_by='" . $_SESSION['UserID'] . "'
	 
	where invoice_name='" . $_POST['invoice_name']. "'";
	 //echo $linesql;
	$result = DB_query($linesql,$db);

		$msg = '修改成功！1秒后将跳转回主页！';
	
	  echo '<meta http-equiv="refresh" content="0; url=' . $RootPath .
                    '/ARInvoiceModify2.php?Updatepo_num='. $_POST['invoice_name'] . '" />';
		 
	} else {
		prnMsg(_('行未选择，请选择'), 'error');
		unset($_SESSION['Contract' . $identifier]);
	}
}

if (isset($_POST['RejectUpdate'])) 
{      $time=time();
	 $sql1 = "select * from ar_invoice_lines_all  
                  where  invoice_name  ='".$_POST['invoice_name']."'   ";
          $result1 = DB_query($sql1,$db);
		  while ($v = DB_fetch_array($result1)) {
             if ($_POST['invoice_type']=='红字发票') {
			  $sql2 = "update  so_lines_all
                    set invoice_amount     = invoice_amount     + '".$v['amount']."' 
                  where  order_number  ='".$v['so_num']."'
				  and line= '".$v['so_line']."' ";
             $result2 = DB_query($sql2,$db);
			 } else {
			  $sql2 = "update  so_lines_all
                    set invoice_amount     = invoice_amount  - '".$v['amount']."' 
                  where  order_number  ='".$v['so_num']."'
				  and line= '".$v['so_line']."' ";
             $result2 = DB_query($sql2,$db);
			 }
 	
		  }
	
	$sql = "update    ar_invoice_lines_all 
	set  status ='撤销'
					,last_updated_by =  '".$_SESSION['UserID']."'
					 ,last_update_date =  '".$time."'
                  where  invoice_name  ='".$_POST['invoice_name']."'  ";
          $result = DB_query($sql,$db);

  	  $sql = "update   ar_invoice_headers_all   set  status ='撤销'
                  where  invoice_name  ='".$_POST['invoice_name']."'   ";
          $result = DB_query($sql,$db);

          
    DB_Txn_Commit($db);
 
    
	echo '<meta http-equiv="refresh" content="0; url=' . $RootPath .
                    '/ARInvoiceModify2.php?Updatepo_num='. $_POST['invoice_name'] . '" />';
	 

}


if (isset($_POST['Delete'])) 
{      $time=time();
	 
	
	$sql = "delete from    ar_invoice_lines_all  where  invoice_name  ='".$_POST['invoice_name']."'  ";
    $result = DB_query($sql,$db);

   $sql = "delete from    ar_invoice_headers_all  where  invoice_name  ='".$_POST['invoice_name']."'  ";
 
   $result = DB_query($sql,$db);

          
    DB_Txn_Commit($db);
 
    prnMsg('发票'.$_POST['invoice_name'].'作废完成！',success);
    echo "<script>location.href='ARInvoiceModify.php';</script>";
	 

}


?>
 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>客户发票修改</title>
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
<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="客户发票修改" alt="客户发票修改">客户发票修改</p>
<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST"><input type="hidden" name="time"
value="<?=$time?>">
<div>
<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
<table class="selection">

<?php
$sql ="SELECT a.invoice_name,a.currency_code,a.invoice_num, a.status, a.remark, a.invoice_date,a.creation_date,a.created_by,
 a.invoice_amount, b.customer_name,b.customer_code,a.tax_amount,a.dis_amount,a.ar_invoice_type,
 a.tax_code,a.invoice_type,a.payment_amount
FROM ar_invoice_headers_all a, customers b
WHERE a.customer_code = b.customer_code  
and invoice_name= '" .$_POST['invoice_name'] . "'";
	// echo $sql;
	$result = DB_query($sql,$db);
    if (DB_num_rows($result)==0) {
        unset($result);
        prnMsg(_('没有预测，请重新输入条件查询！') ,'error');
	 }
 while ($myrow = DB_fetch_array($result))  {
        $_POST['customer_code']=$myrow['customer_code'] ;
       $_POST['customer_name']=$myrow['customer_name'] ;  
	   $_POST['currency_code']=$myrow['currency_code'] ; 
	   $_POST['invoice_date']=  date('Y-m-d',$myrow['invoice_date']) ; 
	   $_POST['invoice_amount']=$myrow['invoice_amount'] ; 
	   $_POST['payment_amount']=$myrow['payment_amount'] ; 
	   $_POST['dis_amount']=$myrow['dis_amount'] ; 
	   $_POST['tax_amount']=$myrow['tax_amount'] ; 
	   $_POST['ar_invoice_type']=$myrow['ar_invoice_type'] ; 
	   $_POST['invoice_type']=$myrow['invoice_type'] ;
	   $_POST['tax_code']=$myrow['tax_code'] ; 
	   $_POST['remark']=$myrow['remark'] ; 
	   $_POST['invoice_name']=$myrow['invoice_name'] ; 
	   $_POST['status']=$myrow['status'] ; 
	   $_POST['invoice_num']=$myrow['invoice_num'] ; 
	   $_POST['creation_date']=date('Y-m-d H:i:s',$myrow['creation_date']) ; 
	   $_POST['created_by']=$myrow['created_by'] ; 

 }     
?>
<div class="text-nav">
<div class="text-nav-1"><div>流水单号</div>
 <input type="text" readonly="readonly"   name="invoice_name"  value="<?=$_POST['invoice_name']?>" size="16" maxlength="25"/> </div>
<div class="text-nav-1"><div>发票号码:</div>
 <input type="text" readonly="readonly"   name="invoice_num"  value="<?=$_POST['invoice_num']?>" size="16" maxlength="25"/> </div>
 <div class="text-nav-1"><div>发票类型:</div>
    <input type="text"  readonly="readonly"  name="ar_invoice_type"  value="<?=$_POST['ar_invoice_type']?>" size="7" maxlength="30"/> </div>
 <div class="text-nav-1"><div>类型:</div>
    <input type="text"  readonly="readonly"  name="invoice_type"  value="<?=$_POST['invoice_type']?>" size="7" maxlength="30"/> </div> 
<div class="text-nav-1"><div>状态:</div>
    <input type="text"  readonly="readonly"  name="status"  value="<?=$_POST['status']?>" size="7" maxlength="30"/> </div> 
<div class="text-nav-1"><div>客户代码:</div>
    <input type="text" readonly="readonly"  name="customer_code" id="text_slect_vendor" value="<?=$_POST['customer_code']?>" size="10" maxlength="25"/> </div>
<div class="text-nav-2"><div>客户名称:</div>
    <input readonly="readonly" type="text" name="customer_name" id="text_slect_name" value="<?=$_POST['customer_name']?>" size="50" maxlength="250"/></div>

<div class="text-nav-1"><div>发票金额:</div>
  <input type="text" class="number" readonly="readonly" id="invoice_amount_all"  name="invoice_amount"  value="<?=$_POST['invoice_amount']?>" size="10" maxlength="30"/> </div>
  <div class="text-nav-1"><div>已收款金额:</div>
  <input type="text" class="number" readonly="readonly"   name="payment_amount"  value="<?=$_POST['payment_amount']?>" size="10" maxlength="30"/> </div>
  <div class="text-nav-1"><div>免开票金额:</div>
  <input type="text" class="number" readonly="readonly"   name="dis_amount"  value="<?=$_POST['dis_amount']?>" size="7" maxlength="30"/> </div>
  <div class="text-nav-1"><div>税金:</div>
    <input type="text" class="number" readonly="readonly"  name="tax_amount"  value="<?=$_POST['tax_amount']?>" size="7" maxlength="30"/> </div>
    <div class="text-nav-1"><div>币别：</div>
    <input type="text" readonly="readonly"  name="currency_code" id="text_slect_currency_code" value="<?=$_POST['currency_code']?>" size="10" maxlength="25"/> </div>
    <div class="text-nav-1"><div>发票日期:</div>
    <input type="text" readonly="readonly"  name="invoice_date" maxlength="20" size="12"  value="<?= $_POST['invoice_date'] ?>"onfocus="WdatePicker() "></div>
 
    <div class="text-nav-1"><div>税别:</div>
    <input type="text"  readonly="readonly"  name="tax_code"  value="<?=$_POST['tax_code']?>" size="9" maxlength="30"/> </div>
  
   
    <div class="text-nav-1"><div>类型:</div>
    <input type="text" readonly="readonly"  name="ar_invoice_type"  value="<?=$_POST['ar_invoice_type']?>" size="7" maxlength="30"/> </div>
 
    <div class="text-nav-2"><div>开票备注：</div>
    <input type="text" readonly="readonly" maxlength="200" size="50" name="remark"  value="<?=$_POST['remark']?>"/> </div>
	 <div class="text-nav-1"><div>建立日期</div>
    <input type="text" readonly="readonly"  name="creation_date"  value="<?=$_POST['creation_date']?>" size="7" maxlength="30"/> </div>
	 <div class="text-nav-1"><div>建立人</div>
    <input type="text" readonly="readonly"  name="created_by"  value="<?=$_POST['created_by']?>" size="7" maxlength="30"/> </div>
	</div>
</table>

<input type="hidden" name="PageOffset" value="1"/><br/>
<?php
if (isset($_POST['customer_name']) and $_POST['customer_name'] != '') {

	$sql ="select invoice_line,c.so_num,a.term_name,a.check_amount,c.amount,c.dis_amount,b.invoice_amount,a.dis_invoice_amount,(b.line_amount-b.invoice_amount ) wait_amount,b.line_amount,c.line_remark,b.stockid,b.item_name,c.so_line,c.invoice_line_id
				from ar_invoice_lines_all c,so_headers_all a,so_lines_all b 
				where  c.so_num=a.order_number and a.order_number = b.order_number
				and c.so_num=b.order_number and c.so_num=b.order_number
				
				and c.customer_code = '" .$_POST['customer_code'] . "'
				and  c.invoice_num= '" .$_POST['invoice_num'] . "'";
	// echo $sql;
	$result = DB_query($sql,$db);
    if (DB_num_rows($result)==0) {
		$line_count=DB_num_rows($result);
      //  unset($result);
        prnMsg(_('该客户没有需要开票，请重新输入条件查询！') ,'error');
    }

?>
<input id="purchase_table_lastRow" name="purchase_table_lastRow" type=hidden value="">

<div class="centre"> 
                        <p id="Prompt" style="color: red;font-size: 20px"></p>
                    </div>

<table id="purchase_table" cellpadding="2" class="selection">
 
<tr id="list-top">
  <th bgcolor="#87CEFA" width="10">行</th>
  <th bgcolor="#87CEFA" width="10">订单号码</th>
  <th bgcolor="#87CEFA" width="10">订单行</th>
  <th bgcolor="#87CEFA" width="10">产品图号</th>
  <th bgcolor="#87CEFA" width="10">产品名称</th>
  
  <th bgcolor="#87CEFA" width="10" >付款条件</th> 
  <th bgcolor="#87CEFA" width="10" >订单金额</th>  
  <th bgcolor="#87CEFA" width="10" >已开票金额</th> 
  <th bgcolor="#87CEFA" width="10" > <font color="red">本次开票金额</font> </th>
  <th bgcolor="#87CEFA" width="10" >备注 </th>
  
 
</tr>
<?php   
$i=1;
 
 while ($myrow = DB_fetch_array($result)  )  {
	
	 ?>
		 
<tr id="purchase_table_<?=$i?>" >
<td><?= $myrow['invoice_line'] ?></td>
<td><?= $myrow['so_num'] ?></td>
<td><?= $myrow['so_line'] ?></td>
<td><?= $myrow['stockid'] ?></td>
<td><?= $myrow['item_name'] ?></td>
 

   <td><input type="text" readonly="readonly" name="term_name<?=$i?>"   value="<?= $myrow['term_name'] ?>" size="6" maxlength="15"/></td>
<td><input type="text" readonly="readonly" name="line_amount<?=$i?>" id="text_slect_line_amount<?=$i?>" value="<?= $myrow['line_amount'] ?>" size="6" maxlength="15"/></td>
    
  <td><input type="text" readonly="readonly" name="invoice_amount<?=$i?>"  id="text_invoice_amount<?=$i?>" value="<?= $myrow['invoice_amount'] ?>" size="7" maxlength="10"/></td>  
  <td><input type="text"   class="number"  name="amount<?=$i?>"  onblur="checktotal()"  id="text_this_invoice_amount<?=$i?>"    value="<?=  $myrow['amount']?>"     size="9" maxlength="10"/></td>

 
  <td><input type="text"  name="line_remark<?=$i?>"   onblur="checktotal()"   value="<?= $myrow['line_remark']?>" size="11" maxlength="10"/></td> 
 <td>  
  <input type="hidden"  name="wait_amount<?=$i?>"  id="text_wait_amount<?=$i?>"  value="<?= $myrow['wait_amount'] ?>" size="8" maxlength="10"/>
  <input type="hidden"  name="so_num<?=$i?>"   value="<?= $myrow['so_num'] ?>" size="8" maxlength="10"/>
  <input type="hidden"  name="so_line<?=$i?>"  id="text_wait_amount<?=$i?>"  value="<?= $myrow['so_line'] ?>" size="8" maxlength="10"/>
  <input type="hidden"  name="invoice_line_id<?=$i?>"  value="<?= $myrow['invoice_line_id'] ?>" size="8" maxlength="10"/> 
  <input type="hidden"  name="wait_amount<?=$i?>"  id="text_wait_amount<?=$i?>"  value="<?= $myrow['wait_amount'] ?>" size="8" maxlength="10"/>
  </td>
</tr>
<?php 
 $i=$i+1;
  
 }
 echo  '</table>';
     if ( $_POST['status']=='核准' and $_POST['payment_amount']==0 ) {
   echo ' <div class="centre"><input type="submit" name="RejectUpdate" value="撤销审核"></div>';
   } else if ( $_POST['status']=='核准' and $_POST['payment_amount']>0 ) {
   echo ' <div class="centre"> </div>';
   } else if ( $_POST['status']=='建立'  ) {
   echo ' <div class="centre"> <input type="submit" name="RejectUpdate" value="撤销"> </div>';
   } else if ( $_POST['status']=='撤销' or  $_POST['status']=='拒绝' )   {
	echo ' <div class="centre"><input type="submit" name="Delete" value="删除">
	<input type="submit" name="UpdateStatus" value="修改送签"></div>';
   } 
}
?>
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
									if (document.getElementById("text_this_invoice_amount" + i)==null)  {
									p=0;
										}
									else {							
								   var  invoice_amount=0;
								   var  dis_amount=0;
                                   var invoice_amount=document.getElementById("text_this_invoice_amount"+i).value;   
                                                              								     
								   if( invoice_amount>0 )
								   {  
								   all_invoice_amount=Number(all_invoice_amount) + Number(invoice_amount);                                 
                                   }
								   

								    }}
									
                    // var tax_rate=document.getElementById("text_slect_tax_rate").value;  

		            document.getElementById("invoice_amount_all").value=all_invoice_amount;
		           
		            //document.getElementById("tax_amount").value=Math.round(Number(all_invoice_amount*tax_rate)*100)/100;
								}

function  check(s1){
	    var a=document.getElementById("text_this_invoice_amount"+s1).value;
        var b=document.getElementById("text_this_invoice_dis_amount"+s1).value;
        var c=document.getElementById("text_wait_amount"+s1).value;
        var d=parseFloat(a) + parseFloat(b);
      if(parseFloat(a)>parseFloat(c)){
            document.getElementById("Prompt").innerHTML="本次开票金额不可以大于待开票金额！！！！";
            document.getElementById("text_this_invoice_amount"+s1).value="";
            document.getElementById("text_this_invoice_amount"+s1).focus();
        } else if(parseFloat(b)>parseFloat(c)){
            document.getElementById("Prompt").innerHTML="本次免开票金额不可以大于待开票金额！！！！";
            document.getElementById("text_this_invoice_dis_amount"+s1).value="";
            document.getElementById("text_this_invoice_dis_amount"+s1).focus();
        }else if(parseFloat(d)>parseFloat(c)){
            document.getElementById("Prompt").innerHTML="开票金额+免开票金额不可以大于待开票金额！！！！";
            document.getElementById("text_this_invoice_dis_amount"+s1).value="";
            document.getElementById("text_this_invoice_dis_amount"+s1).focus();
        } else {
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
	function checkall(thisform){
		for(var i=0;i<thisform.elements.length;i++){
		if(thisform.elements[i].type=="checkbox"&&thisform.elements[i].checked==false&&thisform.elements[i].name!="selectall")
		{thisform.elements[i].checked=true;}
		else if(thisform.elements[i].type=="checkbox"&&thisform.elements[i].checked==true&&thisform.elements[i].name!="selectall")
		{thisform.elements[i].checked=false;}} }
</script>
</body>

</html>
<?

include('includes/footer.inc');
?>

