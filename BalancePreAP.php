<?php

include('includes/session.inc');
$Title = _('预付冲销');
$ViewTopic= '预付冲销';
$BookMark = '预付冲销';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

unset($result);
if (isset($_POST['Save'])) 
{
  $errorflag = 1;
  $all_amount = 0;
  foreach ($_POST as $key => $value) 
  {
    if ($value != '') 
	{
      if (substr($key, 0,11)=='receipt_num') 
	  {
        $errorflag = 0;
        $i = substr($key, 11);
        if ($value != '') 
	    {
		  if ($_POST['this_payment_amount'.$i]=='') 
		  {
		    $errorflag = 1;
		    prnMsg($value.'付款金额未填写,请确认！',error);
		  }

//		  if ($_POST['this_payment_amount'.$i]< 0 ) 
//		  {
//		    $errorflag = 1;
//		    prnMsg($value.'付款金额不可小于0,请确认！',error);
//		  }

		  if ($_POST['this_payment_amount'.$i]< $_POST['this_payment_dis_amount'.$i] ) 
		  {
		    $errorflag = 1;
		    prnMsg($value.'付款金额不可小于付款优惠金额,请确认！',error);
		  }

		  if ($_POST['wait_payment_amount'.$i]=='') 
		  {
		    $errorflag = 1;
		    prnMsg($value.'待付款金额为空,请确认！',error);
		  }

          $this_quantity= $_POST['this_payment_amount'.$i] + $_POST['this_payment_dis_amount'.$i];
		  if ($this_quantity > $_POST['wait_payment_amount'.$i] ) 
		  {
		    $errorflag = 1;
		    prnMsg($value.'本次付款总金额'.$this_quantity .'不可以大于未付款金额'.$_POST['wait_payment_amount'.$i],error);
		  }

		  $all_amount = $all_amount + $_POST['this_payment_amount'.$i];
	  }
	}
   }
  }


  if ($_POST['chongxiao_amount']<>$all_amount) 
  {
	$errorflag = 1;
	prnMsg($value.'付款金额合计'.$all_amount.'不等于冲销金额'.$_POST['chongxiao_amount'].',请确认！',error);
  }
	
//  if ($_POST['chongxiao_amount']<$_POST['taxamount']) 
//  {
//	$errorflag = 1;
//	prnMsg($value.'手续费'.$_POST['handling_fee'].'大于付款金额'.$_POST['chongxiao_amount'].',请确认！',error);
//  }


//产生单号 Begin
  if ($errorflag ==0) 
  {
    $date = date('Ymd');
    $sql_num = "select  (
    CASE WHEN substr(max(payment_num) ,-2,1) = 0 THEN RIGHT ('100' + (max(substr(payment_num ,- 1)) + 1),2) ELSE
    substr(max(payment_num),-2,2) + 1 END) order_number 
	from ap_payment_headers_all where substr(payment_num,1,2) ='AP' and substr(payment_num,-10,8) = '" . $date . "'";
    $result_num = DB_query($sql_num, $db);
    while ($v = DB_fetch_array($result_num)) 
	{
      if ($v['order_number'] == null) 
	  {
        $OrderNum = 'AP'.$date . '01';
      } 
	  else 
	  {
        $OrderNum =  'AP'. $date . $v['order_number'];
      }
    }
  }
//产生单号 End

  if ($errorflag == 0) 
  {
    $Delivery_date = strtotime($_POST['Delivery_date']);
    DB_Txn_Begin($db);
    $time = time();
    foreach ($_POST as $key => $value) 
	{
      if ($value != '') 
	  {
        if (substr($key, 0,11)=='receipt_num') 
		{
          $i = substr($key, 11);
          if($_POST['this_dis_quantity'.$i]=='')
		  {
            $_POST['this_dis_quantity'.$i] = 0;
            $bumishu[$i] = 0;
          }
          $sql = "insert into ap_payment_lines_all 
		                             (payment_num,
									 payment_line,
									  receipt_num,
                                     receipt_line,
									  vendor_code,
                                           amount,
									   dis_amount,
									creation_date,
									   created_by,
								 last_update_date,
								   last_updated_by)
                             values('".$_POST['payment_num']."',
							        '".$i."',
									'".$_POST['receipt_num'.$i]."',
                                    '".$_POST['receipt_line'.$i]."',
									'".$_POST['vendor_code']."',
									'".$_POST['this_payment_amount'.$i]."',
									'".$_POST['this_payment_dis_amount'.$i]."',
									'".$time."',
									'".$_SESSION['UserID']."',
									'".$time."',
									'".$_SESSION['UserID']."') ";

          $result = DB_query($sql,$db);

          $sql = "update  po_rcv_receipt_line
                     set payment_amount     = payment_amount +'".$_POST['this_payment_amount'.$i]."'
                        ,payment_dis_amount = payment_dis_amount +'".$_POST['this_payment_dis_amount'.$i]."'
                        ,status = 'payment'
						,payment_flag = 'Y'
                   where  receipt_num  ='".$_POST['receipt_num'.$i]."' 
				     and  receipt_line ='".$_POST['receipt_line'.$i]."' ";

          $result = DB_query($sql,$db);
        }
      }
    }


    $sql = "update ap_payment_headers_all
               set prepayment_used=prepayment_used+'".$_POST['chongxiao_amount']."'
        where payment_type='预付货款'
          and vendor_code='".$_POST['vendor_code']."'
          and payment_num='".$_POST['payment_num']."'
         ";
	echo $sql; 
    $result = DB_query($sql,$db);
    DB_Txn_Commit($db);
    prnMsg('供应商付款'.$_POST['payment_num'].'付款冲销完成！',success);
   // echo "<script>location.href='index.php';</script>";

  }
}

?>


 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>预付冲账</title>
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
<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="预付冲账" alt="预付冲账">预付冲账</p>
<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST"><input type="hidden" name="time"
value="<?=$time?>">
<div>
<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
<table class="selection">
<tr>
  <td>预付款单号:</td>
  <td><input readonly="readonly" type="text"   name="payment_num" id="text_slect_payment_num" value="<?=$_POST['payment_num']?>" size="20" maxlength="10"/>
  <a class="btn btn-info btn-xs" id="btn_slect_prepayment<?=$i?>" hfre="###" title="选择预付款单号">选择</a> </td>
  <td>预付款金额:</td>
  <td  ><input readonly="readonly" type="text"   name="payment_amount" id="text_slect_payment_amount" value="<?=$_POST['payment_amount']?>" size="10" maxlength="10"/></td>
  <td>已冲销金额:</td>
  <td  ><input readonly="readonly" type="text"   name="prepayment_used" id="text_slect_prepayment_used" value="<?=$_POST['prepayment_used']?>" size="10" maxlength="10"/></td>
  <td>待冲销金额:</td>
  <td><input   readonly="readonly" type="text"   name="wait_used_amount" id="text_slect_wait_used_amount" value="<?=$_POST['wait_used_amount']?>" size="10" maxlength="10"/></td>
</tr>

<tr>
  <td>币别：</td>
  <td><input readonly="readonly" type="text"   name="currency_code" id="text_slect_currency_code" value="<?=$_POST['currency_code']?>" size="5" maxlength="10"/></td>
  <td>银行转账收款凭证号:</td>
  <td><input readonly="readonly" type="text"   name="bankchangenum" id="text_slect_bankchangenum" value="<?=$_POST['bankchangenum']?>" size="15" maxlength="10"/></td>
</tr>

<tr>
  <td>供应商代码：</td>
  <td><input type="text" name="vendor_code" id="text_slect_vendor_code" value="<?=$_POST['vendor_code']?>" size="10" maxlength="25"/> </td>
  <td>供应商名称：</td>
  <td colspan="5"><input readonly="readonly" type="text" name="vendor_name" id="text_slect_vendor_name" value="<?=$_POST['vendor_name']?>" size="70" maxlength="50"/></td>
</tr>

<tr>
  <td>本次冲销金额:</td>
  <td><input type="text" class="number" required="required" maxlength="100" size="20" name="chongxiao_amount"  value="<?=$_POST['chongxiao_amount']?>" size="20" maxlength="20"/> </td>
  <td>付款备注：</td>
  <td colspan="5"><input type="text"  maxlength="200" size="70" name="Header_Remark"  value="<?=$_POST['Header_Remark']?>" size="20" maxlength="20"/> </td>
</tr>

</table>
<div class="centre">
<input type="submit" name="Hearder" value="保存并选择需要冲销的采购入库单">
</div>
<input type="hidden" name="PageOffset" value="1"/><br/>
<?php
if (isset($_POST['vendor_name']) and $_POST['vendor_name'] != '') {
?>
<input id="purchase_table_lastRow" name="purchase_table_lastRow" type=hidden value="">
<table id="purchase_table" cellpadding="2" class="selection">
<tr id="list-top">
  <th width="100">采购入库单号</th>
  <th width="50">项目</th>
  <th width="10">入库单备注</th>
  <th width="10">入库日期</th>
  <th width="30">应付款金额</th>
  <th width="30">已付款金额</th>
  <th width="30">已优惠金额</th>
  <th width="30">未付款金额</th>
  <th width="30">本次付款金额</th>
  <th width="30">本次优惠金额</th>
  <th width="50" align="center">操作</th>
</tr>
<?php for($i=1;$i<=50;$i++){?>

<tr id="purchase_table_<?=$i?>" <?php echo $i>10&&$_POST['receipt_num'.$i]==''?'style="display:none"':''?> class="mouse click">

  <td> <input type="text" name="receipt_num<?=$i?>" id="text_slect_receipt_num<?=$i?>" value="<?=$_POST['receipt_num'.$i]?>" size="14" maxlength="25"/>
  <a class="btn btn-info btn-xs" id="btn_slect_po<?=$i?>" hfre="###" title="选择采购单">选择</a> </td>

  <td> <input type="text" name="receipt_line<?=$i?>" id="text_slect_receipt_line<?=$i?>" value="<?=$_POST['receipt_line'.$i]?>" size="5" maxlength="50"/></td>

  <td> <input readonly="readonly" type="text" name="remark<?=$i?>" id="text_slect_remark<?=$i?>" value="<?=$_POST['remark'.$i]?>" size="20" maxlength="200"/></td>
  <td> <input type="text" readonly="readonly" name="transaction_date<?=$i?>"  id="text_slect_transaction_date<?=$i?>" value="<?=$_POST['transaction_date'.$i]?>" size="8" maxlength="10"/></td>
  <td><input type="text" readonly="readonly" name="check_amount<?=$i?>" id="text_slect_check_amount<?=$i?>" value="<?=$_POST['check_amount'.$i]?>" size="6" maxlength="25"/>
  <td><input type="text" readonly="readonly" name="payment_amount<?=$i?>" id="text_slect_payment_amount<?=$i?>" value="<?=$_POST['payment_amount'.$i]?>" size="6" maxlength="25"/>
  <td><input type="text" readonly="readonly" name="payment_dis_amount<?=$i?>"  id="text_slect_payment_dis_amount<?=$i?>" value="<?=$_POST['payment_dis_amount'.$i]?>" size="6" maxlength="10"/></td>
  <td><input type="text" readonly="readonly" name="wait_payment_amount<?=$i?>"  id="text_slect_wait_payment_amount<?=$i?>" value="<?=$_POST['wait_payment_amount'.$i]?>" size="6" maxlength="10"/></td>
  <td><input type="text" class="number" name="this_payment_amount<?=$i?>"  value="<?=$_POST['this_payment_amount'.$i]?>" size="8" maxlength="10"/></td>
  <td><input type="text" class="number" name="this_payment_dis_amount<?=$i?>"  value="<?=$_POST['this_payment_dis_amount'.$i]?>" size="8" maxlength="10"/></td>
  <td><a onclick="delettr($(this));" style="padding:0px 5px;" href="javascript:;">删除</a></td>
</tr>
<?php }?>

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
            title:'选择采购入库单',
            width: '950px',
            height: 520,
            content:'url:SearchNoPaymentInvoice1.php?fwValue=<?=$i?>&cat=<?=$_POST['vendor_code']?>',
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


       $('#btn_slect_prepayment').dialog({
            title:'选择待冲销的预付款',
            width: '1050px',
            height: 470, 
		    content:'url:BtnSearchUnPrePayment3.php?fwValue=<?=$i?>&cat=<?=$_POST['vendor_code']?>',
            init:function(){
			    this.content.document.getElementById('cat').value = 'buliao';
                this.content.document.getElementById('fwValue').value = '';
            }
        });

     


		$('#btn_slect_vendor').dialog({
            title:'选择供应商',
            width: '950px',
            height: 470,
            content:'url:BtnSearchPrePaymentVendor.php?fwValue=&cat=buliao',
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

