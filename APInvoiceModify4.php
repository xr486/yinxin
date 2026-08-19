<?php

include('includes/session.inc');
$Title = _('供应商费用发票修改');
$ViewTopic= '供应商费用发票修改';
$BookMark = '供应商费用发票修改';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

if (isset($_GET['Updatepo_num']) ) {
$_POST['invoice_name']=$_GET['Updatepo_num'];
}
 

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
			$linesql = "UPDATE ap_invoice_lines_all 
			      set  remark ='" . $_POST['remark' . $i]  . "',
				  amount ='" . $_POST['amount' . $i]  . "',
				  item_name ='" . $_POST['item_name' . $i]  . "',
				  item_desc ='" . $_POST['item_desc' . $i]  . "',
				  uom ='" . $_POST['uom' . $i]  . "',
				  quantity ='" . $_POST['quantity' . $i]  . "',
				  price ='" . $_POST['price' . $i]  . "',
				  last_update_date='" . $time. "'
                    ,last_updated_by='" . $_SESSION['UserID'] . "'
				where invoice_line_id='" . $_POST['invoice_line_id'.$i]. "'  ";
		    //echo $linesql; 
			$result = DB_query($linesql,$db);
          
 
		}
	    DB_Txn_Commit($db);
      }
    }
  }//插入交易表
  if ($line > 0) {

	  $linesql = "update   ap_invoice_headers_all
	set status= '建立' 
	,invoice_amount='" . $_POST['invoice_amount']. "'
	,narrative ='" . $_POST['Header_Remark']. "' 
	,last_update_date='" . $time. "'
    ,last_updated_by='" . $_SESSION['UserID'] . "'
	 
	where invoice_name='" . $_POST['invoice_name']. "'";
	 //echo $linesql;
	$result = DB_query($linesql,$db);

		$msg = '修改成功！1秒后将跳转回主页！';
	
	  echo '<meta http-equiv="refresh" content="0; url=' . $RootPath .
                    '/APInvoiceModify4.php?Updatepo_num='. $_POST['invoice_name'] . '" />';
		 
	} else {
		prnMsg(_('行未选择，请选择'), 'error');
		unset($_SESSION['Contract' . $identifier]);
	}
}

if (isset($_POST['RejectUpdate'])) 
{      $time=time();
	 
	 
  	  $sql = "update   ap_invoice_headers_all   set  status ='撤销'
                  where  invoice_name  ='".$_POST['invoice_name']."'   ";
          $result = DB_query($sql,$db);

          
    DB_Txn_Commit($db);
 
    
	echo '<meta http-equiv="refresh" content="0; url=' . $RootPath .
                    '/APInvoiceModify4.php?Updatepo_num='. $_POST['invoice_name'] . '" />';
	 

}


if (isset($_POST['Delete'])) 
{      $time=time();
	 
	
	$sql = "delete from    ap_invoice_lines_all  where  invoice_name  ='".$_POST['invoice_name']."'  ";
    $result = DB_query($sql,$db);

   $sql = "delete from    ap_invoice_headers_all  where  invoice_name  ='".$_POST['invoice_name']."'  ";
 
   $result = DB_query($sql,$db);

          
    DB_Txn_Commit($db);
 
    prnMsg('发票'.$_POST['invoice_name'].'作废完成！',success);
    echo "<script>location.href='APInvoiceModify.php';</script>";
	 

}

?>
 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>供应商费用发票修改</title>
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

function  checkweight(s1){
	    var invoice_quantity=document.getElementById("quantity"+s1).value;
	    var price=document.getElementById("price"+s1).value; 
        if(parseFloat(invoice_quantity) <=0 ){
			alert('开票数量不可以小于0！');
         
            document.getElementById("quantity"+s1).value="";
            document.getElementById("quantity"+s1).focus();
        } else if(parseFloat(price) <= 0  ){
			alert('开票单价不可以小于0！'); 
            document.getElementById("price"+s1).value="";
            document.getElementById("price"+s1).focus();
        }  else {
		
		 document.getElementById("add_this_invoice_amount"+s1).value=Math.round(Number(invoice_quantity*price)*100)/100;
		}
	  
     }

    function checkall(){               
	
                      var allamount=0;  
                                
                              for(var j=1 ; j < 100; j++){
									 
									if (document.getElementById("add_this_invoice_amount" + j)==null)  {
									p=0;
										}
									else {
      
								   var  lineamount=0
                                   var lineamount=document.getElementById("add_this_invoice_amount"+j).value;

								   if( lineamount!=0 )
								   {
								   allamount=Number(allamount) + Number(lineamount);
                                   //如果input中有数据


                                   }
								    }
								}
       
		document.getElementById("invoice_amount_all").value=Math.round(Number(allamount)*100)/100;
    
     

  }
 </script>
</head>
<body>
 
<div id="CanvasDiv">
<div id="BodyDiv">
<div id="BodyWrapDiv">
<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="供应商费用发票修改" alt="供应商费用发票修改">供应商费用发票修改</p>
<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST"><input type="hidden" name="time"
value="<?=$time?>">
<div>
<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
<table class="selection">

<?php
	 

$sql ="SELECT a.invoice_name,a.currency_code,a.invoice_num, a.status, a.narrative, a.invoice_date,a.creation_date, a.invoice_amount,b.vendor_name,b.vendor_code,a.tax_amount,a.dis_amount,b.vendor_address,a.ap_invoice_type,a.tax_code,c.tax_mount,a.payment_amount
FROM ap_invoice_headers_all a, vendors b,tax_set c
WHERE a.vendor_code = b.vendor_code
and c.tax_name=a.tax_code
and a.invoice_name = '" .$_POST['invoice_name'] . "' ";
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
	   $_POST['invoice_date']=$myrow['invoice_date'] ; 
	   $_POST['invoice_amount']=$myrow['invoice_amount'] ; 
	   $_POST['invoice_dis_amount']=$myrow['dis_amount'] ; 
	   $_POST['tax_amount']=$myrow['tax_amount'] ; 
	   $_POST['payment_amount']=$myrow['payment_amount'] ; 
	   $_POST['Header_Remark']=$myrow['narrative'] ;
	   $_POST['Address']=$myrow['vendor_address'] ; 
	   $_POST['ap_invoice_type']=$myrow['ap_invoice_type'] ; 
	   $_POST['tax_code']=$myrow['tax_code'] ; 
	   $_POST['tax_rate']=$myrow['tax_mount'] ; 
	   $_POST['invoice_name']=$myrow['invoice_name'] ; 
	   $_POST['invoice_num']=$myrow['invoice_num'] ; 
	   $_POST['status']=$myrow['status'] ; 
 }      
?>

<div class="text-nav">
  <div class="text-nav-1  "><div>发票单号</div>
  <input type="text" readonly="readonly" maxlength="100" size="20" name="invoice_name"  value="<?=$_POST['invoice_name']?>" size="15" maxlength="20"/> </div>
  <div class="text-nav-1  "><div>状态</div>
  <input type="text" readonly="readonly" maxlength="100" size="20" name="status"  value="<?=$_POST['status']?>" size="15" maxlength="20"/> </div>
    <div class="text-nav-1  "><div>发票号码</div>
  <input type="text" readonly="readonly" maxlength="100" size="20" name="invoice_num"  value="<?=$_POST['invoice_num']?>" size="15" maxlength="20"/> </div>
  <div class="text-nav-1  "><div>供应商代码</div>
  <input type="text" readonly="readonly"  name="vendor_code" id="text_slect_vendor" value="<?=$_POST['vendor_code']?>" size="10" maxlength="25"/>
   </div>
  <div class="text-nav-1 "><div>币别</div>
  <input readonly="readonly" type="text"   name="currency_code" id="text_slect_currencycode" value="<?=$_POST['currency_code']?>" size="5" maxlength="10"/></div>
  <div class="text-nav-1  "><div>发票日期</div>
  <input type="text" readonly="readonly" name="invoice_date" maxlength="20" size="10"   value="<?=date('Y-m-d',$_POST['invoice_date'])?>" onfocus="WdatePicker() "></div>
  <div class="text-nav-1 required"><div>税别</div>
  <input type="text"   readonly="readonly"   name="tax_code"  value="<?=$_POST['tax_code']?>" size="10" maxlength="20"/></div>
  <div class="text-nav-2 "><div>供应商名称</div>
  <input readonly="readonly" type="text" name="vendor_name" id="text_slect_name" value="<?=$_POST['vendor_name']?>" size="70" maxlength="50"/></div>
  <div class="text-nav-1 "><div>税率</div>
  <input type="text"  readonly="readonly" name="tax_rate" id="text_slect_tax_rate"   value="<?=$_POST['tax_rate']?>" size="10" maxlength="20"/> </div>
  <div class="text-nav-2 "><div>发票地址</div>
  <input readonly="readonly" type="text"   name="Address" id="text_slect_address" value="<?=$_POST['Address']?>" size="70" maxlength="50"/></div>

  <div class="text-nav-1  "><div>发票金额</div>
  <input type="text" readonly="readonly" class="number"    name="invoice_amount"  id="invoice_amount_all" value="<?=$_POST['invoice_amount']?>" size="10" maxlength="20"/> 
  </div>
  <div class="text-nav-1  "><div>已付款金额</div>
  <input type="text" readonly="readonly" class="number"  id="payment_amount" name="payment_amount"  value="<?=$_POST['payment_amount']?>" size="10" maxlength="20"/> 
  </div>
  <div class="text-nav-1  "><div>发票税额</div>

  <input type="text" readonly="readonly" class="number"   id="tax_amount"  name="tax_amount_all"  value="<?=$_POST['tax_amount']?>" size="10" maxlength="20"/> 
 
</div>

<div class="text-nav-1 "><div>发票类型</div>
  <input type="text" class="number" readonly="readonly"  name="ap_invoice_type"  value="<?=$_POST['ap_invoice_type']?>" size="10" maxlength="20"/> 
  </div>
 
  <div class="text-nav-2 "><div>发票备注</div>
  <input type="text" readonly="readonly"  maxlength="200" size="70" name="Header_Remark"  value="<?=$_POST['Header_Remark']?>" size="20" maxlength="20"/> 
  <input type="hidden"  readonly="readonly"  name="invoice_name"  value="<?=$_POST['invoice_name']?>" size="10" maxlength="20"/>  </div>
</div>

</table>

<input type="hidden" name="PageOffset" value="1"/><br/>
<?php
if (isset($_POST['vendor_name']) and $_POST['vendor_name'] != '') {

	$sql ="select  *
				from ap_invoice_lines_all c 
				where  c.invoice_name = '" .$_POST['invoice_name'] . "' ";
	// echo $sql;
	$result = DB_query($sql,$db);
    if (DB_num_rows($result)==0) {
		$line_count=DB_num_rows($result);
        unset($result);
        prnMsg(_('该供应商没有需要开票，请重新输入条件查询！') ,'error');
    }

?>
<input id="purchase_table_lastRow" name="purchase_table_lastRow" type=hidden value="">

<div class="centre"> 
                        <p id="Prompt" style="color red;font-size 20px"></p>
                    </div>
<div class="text-nav-table">
<table id="purchase_table" cellpadding="2" class="selection">
 
<tr id="list-top"> 
                    <th bgcolor="#87CEFA" width="50">行</th>
  <th bgcolor="#87CEFA" width="160">项目名称</th>
                <th bgcolor="#87CEFA" width="10">规格型号</th>
                    <th bgcolor="#87CEFA" width="10">单位</th>
                    <th bgcolor="#87CEFA" width="10">数量</th>
                    <th bgcolor="#87CEFA" width="10">单价</th>
                    <th bgcolor="#87CEFA" width="100">金额</th> 
                    <th bgcolor="#87CEFA" width="50">备注</th>
 
</tr>
<?php   
$i=1;
 
 while ($myrow = DB_fetch_array($result)  )  {
	 
		  echo ' <tr   >		<td>' . $myrow['invoice_line'] . '</td>
			      ';


	 ?>
	  <td><input type="text"  style="background-color:#D2E9FF;" name="item_name<?=$i?>" value="<?=$myrow['item_name']?>" size="15" maxlength="25"/>  </td>
	  <td><input type="text"   name="item_desc<?=$i?>" value="<?=$myrow['item_desc']?>" size="7" maxlength="10"/></td>
          <td><input type="text"  id="uom<?=$i?>" name="uom<?=$i?>" value="<?=$myrow['uom']?>" size="2" maxlength="10"/></td>

        <td><input type="text" onblur="checkall()" onkeyup="checkweight(<?=$i?>)" class="number"  id="quantity<?=$i?>"  name="quantity<?=$i?>" value="<?=$myrow['quantity']?>" size="7" maxlength="10"  /></td>

        <td><input onblur="checkall()" onkeyup="checkweight(<?=$i?>)"  class="number" id="price<?=$i?>" 
						type="text" name="price<?=$i?>" value="<?=$myrow['price']?>" size="7" maxlength="10"  /> </td>
                
	  <td><input type="text"  class="number"  name="amount<?=$i?>" onkeyup="check(<?=$i?>)" onblur="checkall()"  id="add_this_invoice_amount<?=$i?>"    value="<?=  $myrow['amount']?>"     size="10" maxlength="10"/></td>
  <td><input type="text"  name="remark<?=$i?>"  value="<?=  $myrow['remark']?>"     size="12" maxlength="10"/></td>

 <input type="hidden"  name="invoice_line<?=$i?>"  value="<?= $myrow['invoice_line'] ?>" size="12" maxlength="10"/>
  <input type="hidden"  name="invoice_line_id<?=$i?>"  value="<?= $myrow['invoice_line_id'] ?>" size="12" maxlength="10"/>
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
            title'选择未开票的采购入库单',
            width '950px',
            height 520,
            content'urlSearchNoInvoicePo2.php?fwValue=<?=$i?>&cat=<?=$_POST['vendor_code']?>',
            initfunction(){
			    this.content.document.getElementById('cat').value = 'buliao';
                this.content.document.getElementById('fwValue').value = '<?=$i?>';
            }
        });
		<?php }?>


		 <?php for($i=1;$i<=50;$i++){?> 
        $('#btn_slect_subcode<?=$i?>').dialog({
            title'选择仓库',
            width '600px',
            height 370,
            content'urlSearchsubcode.php?fwValue=<?=$i?>&cat=buliao',
            initfunction(){
			    this.content.document.getElementById('cat').value = $_POST['vendor_code'];
                this.content.document.getElementById('fwValue').value = '<?=$i?>';
            }
        });
		<?php }?>


		$('#btn_slect_vendor').dialog({
            title'选择供应商',
            width '950px',
            height 470,
            content'urlBtnSearchAPVendor3.php?fwValue=&cat=buliao',
            initfunction(){
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

