<?php

include('includes/session.inc');
$Title = _('客户收款冲销处理');

$ViewTopic= '客户收款冲销处理';
$BookMark = '客户收款冲销处理';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

unset($result);
 
	if (isset($_POST['Save'])) {
		$errorflag = 1;
		$all_amount = 0;
		foreach ($_POST as $key => $value) {
			if ($value != '') {
				if (substr($key, 0,10)=='invoicenum') {
					$errorflag = 0;
					$i = substr($key, 10);
			 
					if ($value != '') {
						 
						if ($_POST['this_amount'.$i]=='') {
						$errorflag = 1;
						prnMsg($value.'立账金额未填写,请确认！',error);
						}

						if ($_POST['wait_amount'.$i]=='') {
						$errorflag = 1;
						prnMsg($value.'待立账金额为空,请确认！',error);
						}
    
						if ($_POST['this_amount'.$i] > $_POST['wait_amount'.$i] ) {
							$errorflag = 1;
							prnMsg($value.'收款金额'.$_POST['this_amount'.$i].'不可以大于未收款金额'.$_POST['wait_amount'.$i],error);
						}
						$all_amount = $all_amount + $_POST['this_amount'.$i];
                    }
				}
			}
		}
	 
	 
		    if ($_POST['chongxiao_amount']<>$all_amount) {
						$errorflag = 1;
						prnMsg($value.'收款金额合计'.$all_amount.'不等于本次冲销金额'.$_POST['chongxiao_amount'].',请确认！',error);
						}
            if ($_POST['receiveamount']<$_POST['taxamount']) {
						$errorflag = 1;
						prnMsg($value.'税费'.$_POST['taxamount'].'大于收款金额'.$_POST['chongxiao_amount'].',请确认！',error);
						}


//产生单号 Begin
     if ($errorflag ==0) {

		$date = date('Ymd');
        $sql_num = "select 	(
		CASE WHEN substr(max(receivenum) ,-2,1) = 0 THEN
			RIGHT (
				'100' + (
					max(substr(receivenum ,- 1)) + 1
				),
				2
			)
		ELSE
			substr(max(receivenum),-2,2) + 1
		END
        ) order_number from ar_receive_headers_all where substr(receivenum,-10,8) = '" . $date . "'";
        $result_num = DB_query($sql_num, $db); 
        while ($v = DB_fetch_array($result_num)) {
            if ($v['order_number'] == null) {
                $OrderNum = 'RE'.$date . '01';
            } else {
                $OrderNum =  'RE'. $date . $v['order_number'];
            }
        }
 
		}
//产生单号 End

		if ($errorflag == 0) {
			 
			DB_Txn_Begin($db);
			$time = time(); 
			foreach ($_POST as $key => $value) {
				if ($value != '') {
					if (substr($key, 0,10)=='invoicenum') {
						$i = substr($key, 10);
						
						if($_POST['invoicenum'.$i]==''){
							$_POST['invoicenum'.$i] = 'NULL';
							$bumishu[$i] = 0;
						}
                        $sql = "insert into ar_receive_lines_all (receivenum,receiveline,invoicenum,customer_code,
						  receiveamount,creation_date,created_by,last_update_date,last_updated_by)
						values('".$OrderNum."','".$i."','".$_POST['invoicenum'.$i]."','".$_POST['customercode']."','".$_POST['this_amount'.$i]."',
						'".$time."','".$_SESSION['UserID']."','".$time."','".$_SESSION['UserID']."') ";
						
						$result = DB_query($sql,$db);
						

						$sql = "update ar_invoice_headers_all
						set alreadyreceiveamount=alreadyreceiveamount+'".$_POST['this_amount'.$i]."'
						where  invoicenum ='".$_POST['invoicenum'.$i]."'
						and customer_code='".$_POST['customercode']."'  "; 
						$result = DB_query($sql,$db);
 
		 

					}
				}
			}

		 
			$sql = "insert into ar_receive_headers_all
                                         (receive_type,bankaccountname,bankchangenum,receivenum,chong_prereceivenum,receiveamount,taxamount,
											customer_code, 
											narrative,currency_code,
                                            receivedate, 
                                            creation_date,
                                            created_by,
                                            last_update_date,
                                            last_updated_by) values ('".'冲预付款'."','".$_POST['bankaccountname']."','".$_POST['bankchangenum']."','".$OrderNum."','".$_POST['receivenum']."','".$_POST['chongxiao_amount']."','".$_POST['taxamount']."','".$_POST['customercode']."','".$_POST['Header_Remark']."','".$_POST['currency_code']."','".$time."','".$time."','".$_SESSION['UserID']."','".$time."','".$_SESSION['UserID']."')";
			$result = DB_query($sql,$db);
			
			$sql = "insert into bank_transaction_all
                                         (banktranstype,bankaccountname,bankaccount,bankchangenum,
										 transaction_num,amount,remark,currency_code,
                                            transaction_date, 
                                            creation_date,created_by,
                                            last_update_date,
                                            last_updated_by) values (
											'".'冲预付款'."','".$_POST['bankaccountname']."','".$_POST['mybankaccount']."','".$_POST['bankchangenum']."',
											'".$OrderNum."','".$_POST['chongxiao_amount']."','".$_POST['Header_Remark']."','".$_POST['currency_code']."','".$time."','".$time."','".$_SESSION['UserID']."','".$time."','".$_SESSION['UserID']."')";
			$result = DB_query($sql,$db);

			$sql = "update ar_receive_headers_all
                      set prereceive_used=prereceive_used+'".$_POST['chongxiao_amount']."'
					  where receive_type='客户预收款'
					    and bankaccountname='".$_POST['bankaccountname']."'
						and receivenum='".$_POST['receivenum']."' 
						";
			$result = DB_query($sql,$db);

			DB_Txn_Commit($db);
			prnMsg('客户预收款单'.$_POST['receivenum'].'收款冲销完成！',success);
			echo "<script>location.href='index.php';</script>";

		}
	}

 ?>
 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>客户收款冲销处理</title>
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
			<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="客户收款冲销处理" alt="客户收款冲销处理">客户收款冲销处理</p>
			<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST"><input type="hidden" name="time" 
           value="<?=$time?>">
				<div>
				<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
				<table class="selection">
		 

		<tr>
			<td>客户代码：</td>  
			<td><input type="text" required="required" name="customercode" id="text_slect_customer" value="<?=$_POST['customercode']?>" size="10" maxlength="25"/>
                       <a class="btn btn-info btn-xs" id="btn_slect_customer<?=$i?>" hfre="###" title="选择客户">选择</a> </td>
       
		 <td>客户名称：</td>
		 <td colspan="5"><input readonly="readonly" type="text" name="customername" id="text_slect_name" value="<?=$_POST['customername']?>" size="70" maxlength="50"/></td>
          </tr>

		   <tr>
		    <td>银行账户名称：</td>  
			<td><input type="text" required="required" name="bankaccountname" id="text_slect_bankaccountname" value="<?=$_POST['bankaccountname']?>" size="20" maxlength="25"/>
                       <a class="btn btn-info btn-xs" id="btn_slect_prereceive<?=$i?>" hfre="###" title="选择预收款单">选择</a> </td>
       
			 <td>银行账号：</td>			 
			<td  ><input readonly="readonly" type="text"   name="mybankaccount" id="text_slect_mybankaccount" value="<?=$_POST['mybankaccount']?>" size="20" maxlength="10"/></td>
			 <td>币别：</td>			 
			<td  ><input readonly="readonly" type="text"   name="currency_code" id="text_slect_currency_code" value="<?=$_POST['currency_code']?>" size="5" maxlength="10"/></td>
			<td>银行转账收款凭证号:</td>			 
			<td  ><input readonly="readonly" type="text"   name="bankchangenum" id="text_slect_bankchangenum" value="<?=$_POST['bankchangenum']?>" size="15" maxlength="10"/></td>
		  </tr>


          <tr>		   
			 <td>预收款单号:</td>			 
			<td  ><input readonly="readonly" type="text"   name="receivenum" id="text_slect_receivenum" value="<?=$_POST['receivenum']?>" size="20" maxlength="10"/></td>

			<td>预收款金额:</td>			 
			<td  ><input readonly="readonly" type="text"   name="receiveamount" id="text_slect_receiveamount" value="<?=$_POST['receiveamount']?>" size="10" maxlength="10"/></td>
			 <td>已冲销金额:</td>			 
			<td  ><input readonly="readonly" type="text"   name="prereceive_used" id="text_slect_prereceive_used" value="<?=$_POST['prereceive_used']?>" size="10" maxlength="10"/></td>
			<td>待冲销金额:</td>			 
			<td  ><input readonly="readonly" type="text"   name="wait_used_amount" id="text_slect_wait_used_amount" value="<?=$_POST['wait_used_amount']?>" size="10" maxlength="10"/></td> 

		 
			 		 
		</tr>
  
		 

		<tr>
		<td>本次冲销金额:</td> 
			 <td  ><input type="text" required="required" maxlength="100" size="20" name="chongxiao_amount"  value="<?=$_POST['chongxiao_amount']?>" size="10" maxlength="20"/> </td>	
		<td>税费:</td> 
			 <td  ><input type="text" required="required" maxlength="100" size="20" name="taxamount"  value="<?=$_POST['taxamount']?>" size="10" maxlength="20"/> </td>	
			<td>收款备注：</td> 
			 <td colspan="5"><input type="text"  maxlength="200" size="70" name="Header_Remark"  value="<?=$_POST['Header_Remark']?>" size="20" maxlength="20"/> </td>
		</tr>

	</table>
	<div class="centre">
		<input type="submit" name="Hearder" value="保存发票头信息">
   
	</div>
	<input type="hidden" name="PageOffset" value="1"/><br/>
	<?php
		if (isset($_POST['customername']) and $_POST['customername'] != ''  ) {
	?>
              <input id="purchase_table_lastRow" name="purchase_table_lastRow" type=hidden value="">
					<table id="purchase_table" cellpadding="2" class="selection">
					<tr id="list-top">
					<th width="150">发票号码</th>
					<th width="10">发票开立日期</th> 
					<th width="110">预计收款日</th>   
					<th width="30">发票金额</th> 
					<th width="30">税金额</th> 
					<th width="30">已收款金额</th> 
					<th width="30">未收款金额</th>
					<th width="30">本次收款</th>
					<th width="50" align="center">操作</th>
					</tr>
					<?php for($i=1;$i<=50;$i++){?>
			
					<tr id="purchase_table_<?=$i?>" <?php echo $i>10&&$_POST['invoicenum'.$i]==''?'style="display:none"':''?> class="mouse click">

					<td> <input type="text" name="invoicenum<?=$i?>" id="text_slect_invoicenum<?=$i?>" value="<?=$_POST['invoicenum'.$i]?>" size="11" maxlength="25"/>
					<a class="btn btn-info btn-xs" id="btn_slect_invoice<?=$i?>" hfre="###" title="选择发票">选择</a> </td>
					<td><input type="text" name="invoicedate<?=$i?>" id="text_slect_invoicedate<?=$i?>" value="<?=$_POST['invoicedate'.$i]?>" size="11" maxlength="25"/>
				
					<td><input type="text" name="schedulereceivedate<?=$i?>" id="text_slect_schedulereceivedate<?=$i?>" value="<?=$_POST['schedulereceivedate'.$i]?>" size="12" maxlength="25"/>
							</td>
					
                       	 
					   <td><input readonly="readonly" type="text" name="invoiceamount<?=$i?>" id="text_slect_invoiceamount<?=$i?>" value="<?=$_POST['invoiceamount'.$i]?>" size="8" maxlength="4"/></td>

					   <td><input type="text" readonly="readonly" name="taxamount<?=$i?>"  id="text_slect_taxamount<?=$i?>" value="<?=$_POST['taxamount'.$i]?>" size="8" maxlength="10"/></td>  
					    

					   <td><input type="text" readonly="readonly" name="alreadyreceiveamount<?=$i?>"  id="text_slect_alreadyreceiveamount<?=$i?>" value="<?=$_POST['alreadyreceiveamount'.$i]?>" size="8" maxlength="10"/></td>   

						<td><input type="text" readonly="readonly" name="wait_amount<?=$i?>" id="text_slect_wait_amount<?=$i?>"  value="<?=$_POST['wait_amount'.$i]?>" size="8" maxlength="10"/></td>
						
						
						<td><input type="text"  name="this_amount<?=$i?>"  value="<?=$_POST['this_amount'.$i]?>" size="8" maxlength="10"/></td> 
						
                      <td>  <a onclick="delettr($(this));" style="padding:0px 5px;" href="javascript:;">删除</a></td>
  
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
            content:'url:SearchNoReceiveInvoice.php?fwValue=<?=$i?>&cat=<?=$_POST['customercode']?>',
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
			    this.content.document.getElementById('cat').value = $_POST['customercode'];
                this.content.document.getElementById('fwValue').value = '<?=$i?>';
            }
        });
		<?php }?>


		$('#btn_slect_customer').dialog({
            title:'选择客户',
            width: '950px',
            height: 470,
            content:'url:BtnSearchPreReceiveCustomer.php?fwValue=&cat=buliao',
            init:function(){
			    this.content.document.getElementById('cat').value ='buliao';
                this.content.document.getElementById('fwValue').value = '';
            }
        });

		$('#btn_slect_prereceive').dialog({
            title:'选择待冲销客户预收款',
            width: '950px',
            height: 470, 
		    content:'url:BtnSearchUnPrereceive.php?fwValue=&cat=<?=$_POST['customercode']?>',
            init:function(){
			    this.content.document.getElementById('cat').value = 'buliao';
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

