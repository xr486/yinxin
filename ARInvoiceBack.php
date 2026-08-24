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

include('includes/session.inc');
$Title = _('客户红字发票录入');
$ViewTopic= '客户红字发票录入';
$BookMark = '客户红字发票录入';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

unset($result);
if (isset($_POST['Save'])) 
{
  $errorflag = 0;
  $all_amount = 0;
  $all_invoice_dis_amount = 0;
  $k =0;
  foreach ($_POST as $key => $value) 
  {
    if ($value != '') 
	{
		if (substr($key, 0,11)=='receipt_num') 
	  {
        $errorflag = 0;
        $i = substr($key, 11);
     
          if ($_POST['this_invoice_amount'.$i]=='') 
		  {
            $errorflag = 1;
            prnMsg($value.'开票金额未填写,请确认！',error);
          }
		  if ($_POST['this_invoice_amount'.$i]<0 ) 
		  {
            $errorflag = 1;
            prnMsg($value.'开票金额不可以小于0,请确认！',error);
          }        

		  $all_amount = $all_amount + $_POST['this_invoice_amount'.$i] ; 
  
    }
  
  }

  }

 if ($_POST['invoice_amount_all']<>$all_amount) 
  {
    $errorflag = 1;
   prnMsg($value.'开票金额合计'.$all_amount.'不等于发票总金额'.$_POST['invoice_amount_all'].',请确认！',error);
  }
   
   $date = date('Ymd');
    $sql_num = "select  (CASE WHEN substr(max(invoice_name) ,-3,1) = 0 THEN RIGHT ('1000' + (max(substr(invoice_name ,- 1)) + 1),3)
                ELSE substr(max(invoice_name),-3,3) + 1 END) order_number 
			    from ar_invoice_headers_all 
				where substr(invoice_name,3,8)= '" . $date . "' and invoice_name like 'AB%'";
    $result_num = DB_query($sql_num, $db);
    while ($v = DB_fetch_array($result_num)) 
	{
      if ($v['order_number'] == null) 
	  {
        $invoice_name = 'AB'.$date . '001';
      } else 
	  {
        $invoice_name =  'AB'. $date . $v['order_number'];
      }
    }

	$time = time();
	$time2 = $time - 10;
  
	if ($_SESSION['lastsearchtime'] > $time2) {
		$errorflag = 1;
		prnMsg($value . '重复提交！', error);
	}
  if ($errorflag == 0) 
  {
    $Delivery_date = strtotime($_POST['Delivery_date']);
    DB_Txn_Begin($db);
    $time = time();
    $delivery_amount = 0;
    $k =0;
    foreach ($_POST as $key => $value) 
    {
       $k = $k +1; 
	   if ($value != '') 
	   {
		if (substr($key, 0,11)=='receipt_num') 
	     { 
          $i = substr($key, 11);
          
          
          if ($_POST['this_invoice_dis_amount'.$i]=='') {
		    $_POST['this_invoice_dis_amount'.$i]=0;
		  }
          $sql = "insert into ar_invoice_lines_all 
		                              (ar_invoice_type,invoice_num,invoice_name,
								      invoice_line,
								       so_num, so_line, 
									        amount,
									    dis_amount,
									   customer_code,
                                     creation_date,
									    created_by,
								  last_update_date,
								   last_updated_by)
                            values('".'红字发票'."',
							'".$_POST['invoice_num']."','".$invoice_name."',
							       '".$i."',
								   '".$_POST['receipt_num'.$i]."','".$_POST['so_line'.$i]."', 
								   '".$_POST['this_invoice_amount'.$i]."',
								   '".$_POST['this_invoice_dis_amount'.$i]."',
								   '".$_POST['customer_code']."',
                                   '".$time."',
								   '".$_SESSION['UserID']."',
								   '".$time."',
								   '".$_SESSION['UserID']."') ";
         // echo $sql;
          $result = DB_query($sql,$db);
         
      	 
          $sql = "update  so_lines_all
                    set invoice_amount     = invoice_amount     -'".$_POST['this_invoice_amount'.$i]."' 
                  where  order_number  ='".$_POST['receipt_num'.$i]."'
				  and line ='".$_POST['so_line'.$i]."' ";
          $result = DB_query($sql,$db);
		 
 
        }
      }}
      if ($_POST['invoice_dis_amount']=='') {
		    $_POST['invoice_dis_amount']=0;
		  }
		  if ($_POST['tax_amount_all']=='') {
		    $_POST['tax_amount_all']=0;
		  }
         $sql = "insert into ar_invoice_headers_all
             (ar_invoice_type,invoice_type,status,
			      invoice_num,invoice_name,
			   invoice_amount, dis_amount,
			       tax_amount,
                  customer_code,
                    remark,
				currency_code,tax_code,
                 invoice_date,
                creation_date,
				   created_by,
             last_update_date,
              last_updated_by) 
			        values 
		('".$_POST['invoice_type']."','红字发票','建立',
		 '".$_POST['invoice_num']."','".$invoice_name."',
		 '".$_POST['invoice_amount_all']."',
		 '".$_POST['invoice_dis_amount']."',
         '".$_POST['tax_amount_all']."',
         '".$_POST['customer_code']."',
		 '".$_POST['Header_Remark']."',
		 '".$_POST['currency_code']."',
		 '".$_POST['tax_name']."',
		 '".$Delivery_date."',
		 '".$time."',
		 '".$_SESSION['UserID']."',
		 '".$time."',
		 '".$_SESSION['UserID']."')";
    $result = DB_query($sql,$db);
    DB_Txn_Commit($db);
	if ($k>0) {
		$_SESSION['lastsearchtime'] = $time;
    prnMsg('发票'.$_POST['invoice_num'].'录入完成！',success);
    echo "<script>location.href='ARInvoiceBack.php';</script>";
	}

     
  }

 
   
}

?>
 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>客户红字发票录入</title>
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

var lang = 'cn';
var metimgurl='/JXC/statics/base/images/';
var depth='';
$(document).ready(function(){
	ifreme_methei();
});
 
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
<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="客户红字发票录入" alt="客户红字发票录入">客户红字发票录入</p>
<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST"><input type="hidden" name="time"
value="<?=$time?>">
<div>
<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
<table class="selection">
<?php 
if (!isset($_POST['Delivery_date'])) {
    $_POST['Delivery_date'] = date( "Y-m-d", mktime(0,0,0,date("m"),date("d"),date("Y")));
	$_POST['invoice_dis_amount']=0;
    $_POST['tax_amount_all']=0;
}
?>

  
<div class="text-nav">

<div class="text-nav-1 required"><div>客户代码：</div>  
  <input type="text" required="required" name="customer_code" id="text_slect_customer" value="<?=$_POST['customer_code']?>" size="10" maxlength="25" onblur="sel()"/>
			   <image class="select_img" src="img/search.png" id="btn_slect_customer"/>
			</div>
         <div class="text-nav-2 required"><div>客户名称：</div>
<input  type="text" name="customername" id="text_slect_name" value="<?=$_POST['customername']?>" size="70" maxlength="50" onblur="sel_name()"/></div>
 
<div class="text-nav-1 "><div>币别:</div>
<input type="text"   maxlength="10" size="5" name="currency_code"  id="text_slect_currency_code" value="<?=$_POST['currency_code']?>" /> </div>
  
<div class="text-nav-1 required"><div>发票号码:</div>
<input type="text" required="required" maxlength="100" size="20" name="invoice_num"  value="<?=$_POST['invoice_num']?>" size="15" maxlength="20" onblur="checkinvoice_num()"/> </div>
<div class="text-nav-1 required"><div>发票总金额:</div>
  <input type="text" class="number" readonly="readonly"  id="invoice_amount_all" name="invoice_amount_all"  value="<?=$_POST['invoice_amount_all']?>" size="10" maxlength="20"/>
  </div>
  
  <div class="text-nav-1 required"><div>发票税额:</div>

  <input type="text" class="number"     name="tax_amount_all"   value="<?=$_POST['tax_amount_all']?>" size="10" maxlength="20"/> 
  </div>
  <div class="text-nav-1 required"><div>税别</div>

			<select name="tax_name" id="text_slect_tax_name">
				<?php
					$sql = "select tax_name from tax_set order by tax_id";
					$result = DB_query($sql,$db);
					while ($v = DB_fetch_array($result)) {
						if ($v['tax_name']==$_POST['tax_name']) {
				?>
					<option value="<?=$v['tax_name']?>" selected="selected"><?=$v['tax_name']?></option>
				<?php }else{?>
				<option value="<?=$v['tax_name']?>"><?=$v['tax_name']?></option>
				<?php		}
					}
				?>
			</select>
		</div>
		 <div class="text-nav-1 required"><div>发票类型：</div>  

			<select name="invoice_type" id="invoice_type">
				<?php
					$sql = "select ap_invoice_type from gl_invoice_type ";
					$result = DB_query($sql,$db);
					while ($v = DB_fetch_array($result)) {
						if ($v['ap_invoice_type']==$_POST['invoice_type']) {
				?>
					<option value="<?=$v['ap_invoice_type']?>" selected="selected"><?=$v['ap_invoice_type']?></option>
				<?php }else{?>
				<option value="<?=$v['ap_invoice_type']?>"><?=$v['ap_invoice_type']?></option>
				<?php		}
					}
				?>
			</select>
		</div>
    <div class="text-nav-1 required"><div>发票日期：</div>
  <input type="text" name="Delivery_date" maxlength="20" size="10" required="required" value="<?=$_POST['Delivery_date']?>" onfocus="WdatePicker() "></div>
  <div class="text-nav-2"><div>发票备注：</div>
  <input type="text"  maxlength="200" size="70" name="Header_Remark"  value="<?=$_POST['Header_Remark']?>" size="20" maxlength="20"/><input type="hidden"    name="tax_rate" id="text_slect_tax_rate" value="<?=$_POST['tax_rate']?>" size="20" maxlength="20"/></div>
  
</div>

</table>
<div class="centre">
<input type="submit" name="Hearder" value="确认发票号码选择业务订单">
</div>
<input type="hidden" name="PageOffset" value="1"/><br/>
<?php

         $flag=1;
	       $sqlso = "select *
	              from ar_invoice_headers_all  
				  where  invoice_num  ='".$_POST['invoice_num']."'
				  and customer_code ='".$_POST['vendor_code']."' 
				  and invoice_type in ('红字发票') ";
          $result0 = DB_query($sqlso,$db); 
		  if (DB_num_rows($result0) == 0) {
           $flag=0;
		  } else {
		  echo '发票号码已存在';
		  }
if (isset($_POST['customername']) and $_POST['customername'] != '' and  $flag==0) {

?>
<input id="purchase_table_lastRow" name="purchase_table_lastRow" type=hidden value="">

<div class="centre"> 
                        <p id="Prompt" style="color: red;font-size: 20px"></p>
                    </div>

<table id="purchase_table" cellpadding="2" class="selection">
 
<tr id="list-top">
  <th width="190">业务订单</th>
   <th width="150">客户订单</th>
  <th width="20" >订单行</th>
  <th width="80" >产品图号</th>
  <th width="80" >产品名称</th>
  <th width="10" >订单金额</th> 
  <th width="10" >已开票金额</th> 
  <th width="10" >红字发票金额</th>
  <th width="47" align="center">操作</th>
 
</tr>
<?php for($i=1;$i<=50;$i++){?>
 <tr id="purchase_table_<?=$i?>" <?php echo $i>10&&$_POST['receipt_num'.$i]==''?'style="display:none"':''?> class="mouse click">

  <td> <input type="text" readonly="readonly" name="receipt_num<?=$i?>" id="text_slect_order_number<?=$i?>" value="<?=$_POST['receipt_num'.$i]?>" size="19" maxlength="25"/> 
  <image class="select_img" src="img/search.png" id="btn_slect_po<?=$i?>"/>
</td>
 <td><input type="text"  readonly="readonly" id="text_slect_customer_order_number<?=$i?>" name="customer_order_number<?=$i?>" value="<?=$_POST['customer_order_number'.$i]?>" size="15" maxlength="20"/></td>
	 <td><input type="text"  readonly="readonly" id="text_slect_so_line<?=$i?>" name="so_line<?=$i?>" value="<?=$_POST['so_line'.$i]?>" size="1" maxlength="20"/></td>
	 <td><input type="text"  readonly="readonly" id="text_slect_item_no<?=$i?>" name="item_no<?=$i?>" value="<?=$_POST['item_no'.$i]?>" size="12" maxlength="20"/></td>
	 <td><input type="text"  readonly="readonly" id="text_slect_item_name<?=$i?>" name="item_name<?=$i?>" value="<?=$_POST['item_name'.$i]?>" size="12" maxlength="20"/></td> 

 <td><input type="text" readonly="readonly" name="line_amount<?=$i?>"  id="text_slect_line_amount<?=$i?>" value="<?=$_POST['line_amount'.$i]?>" size="8" maxlength="15"   /></td>
  <td><input type="text" readonly="readonly" name="invoice_amount<?=$i?>"  id="text_slect_invoice_amount<?=$i?>" value="<?=$_POST['invoice_amount'.$i]?>" size="8" maxlength="15"   /></td>
 
  
  <td><input type="text"  name="this_invoice_amount<?=$i?>"  class="number"  id="this_invoice_amount<?=$i?>" value="<?=$_POST['this_invoice_amount'.$i]?>"     size="8" maxlength="10"   step="1"  min="0"  onkeyup="this.value= this.value.match(/\d+(\.\d{0,2})?/) ? this.value.match(/\d+(\.\d{0,2})?/)[0] : ''"   onblur="checktotal()" /> </span></td>

  <td>  <a onclick="delettr($(this));" style="padding:0px 5px;" href="javascript:;">删除</a>
  <td><input type="hidden" readonly="readonly" name="wait_invoice_amount<?=$i?>"  id="text_slect_wait_amount<?=$i?>" value="<?=$_POST['wait_invoice_amount'.$i]?>" size="6" maxlength="10"/></td>

  </td>
</tr>
<?php 
 $i=$i+1;
  
 }
  ?>
  
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




</body>
<script type="text/javascript">
    
/*ajax执行*/
 function checktotal(){       
								   var  all_invoice_amount=0; 
								   var  all_dis_amount=0;                
                                for(var i=1 ; i < 50; i++){   
									if (document.getElementById("this_invoice_amount" + i)==null)  {
									p=0;
										}
									else {								 
		                            
								   var  invoice_amount=0;
								   var  dis_amount=0;
                                   var invoice_amount=document.getElementById("this_invoice_amount"+i).value; 
								   var a=document.getElementById("text_slect_invoice_amount"+i).value;
								   if(parseFloat(invoice_amount)>parseFloat(a)){
									   alert("红字发票金额不可以大于已开票金额！");
         						    //  document.getElementById("Prompt").innerHTML="红字发票金额不可以大于已开票金额！";
         						      document.getElementById("this_invoice_amount"+i).value="";
									  invoice_amount=0;
         						      document.getElementById("this_invoice_amount"+i).focus();
       						       }  else {
       						        document.getElementById("Prompt").innerHTML="";
       						       }
								     
								   if( invoice_amount>0 )
								   {  
								   all_invoice_amount=Number(all_invoice_amount) + Number(invoice_amount);                                 
                                   }
								  

								    }}
		            document.getElementById("invoice_amount_all").value= Math.round(Number(all_invoice_amount)*100)/100; 
		 }
   
 function  check55(s1){
	    var a=document.getElementById("text_slect_invoice_amount"+s1).value;
        var b=document.getElementById("this_invoice_amount"+s1).value;  
      if(parseFloat(b)>parseFloat(a)){
            document.getElementById("Prompt").innerHTML="红字发票金额"+b+"不可以大于已开票金额！"+a;
            document.getElementById("this_invoice_amount"+s1).value="";
            document.getElementById("this_invoice_amount"+s1).focus();
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

 
        <?php for($i=1;$i<=50;$i++){?> 
        $('#btn_slect_po<?=$i?>').dialog({
            title:'选择已开票的业务订单',
            width: '950px',
            height: 420,
            content:'url:SearchInvoiceSo.php?fwValue=<?=$i?>&cat=<?=$_POST['customer_code']?>',
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

	function checkinvoice_num(){
var invoice_num = $("#invoice_num").val();
var customer_code = $("#text_slect_vendor").val();
$.get("checkvendorinvoice.php",{invoice_num: invoice_num,customer_code: customer_code,type: '红字发票'},function(txt){
if(txt == 0){ 
	document.getElementById("Prompt").innerHTML=invoice_num+"此客户该发票号码已录入,不能重复录入,请确认";
	document.getElementById("invoice_num").value="";
    document.getElementById("invoice_num").focus();
	}
});
}

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


</script>
</html>
<?
 
include('includes/footer.inc');
?>

