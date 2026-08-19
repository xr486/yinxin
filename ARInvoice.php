<?php

if(isset($_GET['data'])){
	 
	 include_once("connect.php"); 
	 $sql = "select * from customers where customer_code = '".$_GET['data']."'";
	 $result_num = mysql_query($sql, $db);
	 $res = mysql_fetch_assoc($result_num);
	 echo $res['customer_name'].':'.$res['currency_code'].':'.$res['tax_name'];
	 return ;
 }
if(isset($_GET['data2'])){
	 //$sql = "select * from customers where customer_name = '".$_GET['data']."'";
	 include_once("connect.php"); 
	 $sql = "select * from customers where customerti_name = '".$_GET['data2']."'";
	 $result_num = mysql_query($sql, $db);
	 $res_customer_name = mysql_fetch_assoc($result_num);
	 echo $res_customer_name['customer_code'].':'.$res_customer_name['currency_code'].':'.$res_customer_name['tax_name'];
	 return ;
 }

include('includes/session.inc');
$Title = _('客户发票录入');
$ViewTopic= '客户发票录入';
$BookMark = '客户发票录入';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

unset($result);

if (isset($_POST['Save'])) {
	$errorflag = 1;
	foreach ($_POST as $key => $value) {
		if ($value != '') {
			if (substr($key, 0,12)=='order_number') {
				$errorflag = 0;
				$i = substr($key, 12);
				if ($value != '') {
					if ($_POST['this_invoice_amount'.$i]=='') {
						$errorflag = 1;
						prnMsg($value.'未填写金额！',error);
					}
					if ($_POST['this_invoice_amount'.$i] <0 ) {
						$errorflag = 1;
						prnMsg($value.'金额不能小于0！',error);
					}
					 

				}
			}
		}
	}
	$time = time();
	$time2 = $time - 10;
  
	if ($_SESSION['lastsearchtime'] > $time2) {
		$errorflag = 1;
		prnMsg($value . '重复提交！', error);
	}
	if ($errorflag == 0) {

		$sumamount=0.00;
	 	 
		$date = date('Ymd');
        $sql_num = "select max(substr(invoice_name,-3,3)) + 1 
			order_number from ar_invoice_headers_all where substr(invoice_name,3,8) = '" . $date . "'";
	  
        $result_num = DB_query($sql_num, $db); 
        while ($v = DB_fetch_array($result_num)) {
            if ($v['order_number'] == null) {
                $OrderNum = 'AR'.$date . '001';
            } else {
                $OrderNum =  'AR'. $date . str_pad($v['order_number'],3,'0',STR_PAD_LEFT);
            }
        }


		$need_date = strtotime($_POST['need_date']); 
        $OrderDate = strtotime($_POST['OrderDate']);
        
		DB_Txn_Begin($db);
		$time = time();
		$order_amount = 0;
		$j=0;
		 
		foreach ($_POST as $key => $value) {
			if ($value != '') {
				if (substr($key, 0,12)=='order_number') {
					$i = substr($key, 12);
					
					if($_POST['stockid'.$i]==''){
						$_POST['stockid'.$i] = 'NULL';
						$bumishu[$i] = 0;
					}
					 
					$j=$j+1;
					 
                   if ($_POST['this_invoice_amount'.$i] > 0) {
					$sql = "insert into ar_invoice_lines_all 
		                              (ar_invoice_type,invoice_num,invoice_name,
								      invoice_line,
								       so_num,so_line,line_remark, 
									   status,
									        amount,
									    dis_amount,
									   customer_code,
                                     creation_date,
									 
									    created_by,
								  last_update_date,
								   last_updated_by)
                            values('".'正常发票'."','".$_POST['invoice_num']."','".$OrderNum."',
							       '".$i."',
								   '".$_POST['order_number'.$i]."','".$_POST['so_line'.$i]."','".$_POST['line_remark'.$i]."',
									'".'建立'."',								   
								   '".$_POST['this_invoice_amount'.$i]."',
								   '".$_POST['this_invoice_dis_amount'.$i]."',
								   '".$_POST['customer_code']."',
                                   '".$time."',
								   '".$_SESSION['UserID']."',
								   '".$time."',
								   '".$_SESSION['UserID']."') ";
                  $result = DB_query($sql,$db);

				  $sql2 = "update so_lines_all 
		                   set invoice_amount= invoice_amount + '".$_POST['this_invoice_amount'.$i]."' 
							where order_number=   '".$_POST['order_number'.$i]."'
							and line = '".$_POST['so_line'.$i]."' ";
						 echo $sql2;
                  $result = DB_query($sql2,$db);

				   }

					 

					   
				}
			}
		}

		 
   
		$sql4 = "insert into ar_invoice_headers_all
             (ar_invoice_type,invoice_type,
			      invoice_num,invoice_name,
			   invoice_amount, dis_amount,
			       tax_amount,tax_code,
                  customer_code,
				  status,
                    remark,
				currency_code,
                 invoice_date,
                creation_date,
				   created_by,
             last_update_date,
              last_updated_by) 
			        values 
		( '".$_POST['invoice_type']."','".'销售发票'."',
		 '".$_POST['invoice_num']."','".$OrderNum."', 
		 '".$_POST['invoice_amount_all']."',
		 '".$_POST['invoice_dis_amount']."',
         '".$_POST['tax_amount_all']."',
         '".$_POST['tax_name']."',
         '".$_POST['customer_code']."',
		 '".'建立'."',
		 '".$_POST['Header_Remark']."',
		 '".$_POST['currency_code']."',
		 '".strtotime($_POST['invoice_date'])."',
		 '".$time."',
		 '".$_SESSION['UserID']."',
		 '".$time."',
		 '".$_SESSION['UserID']."')";
		$result = DB_query($sql4,$db);

		DB_Txn_Commit($db);
		$_SESSION['lastsearchtime'] = $time;
		prnMsg('采购单编号'.$OrderNum.'建立成功！',success);
 
	  header("Location: SussCreate.php?OrderNum=" . $OrderNum . "&type=ARInvoice");

	}
}


?>
 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>客户发票录入</title>
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
	var c = parseFloat(v) + 1;
	$('#idcount').val(c);     
}

 </script>
</head>
<body>
 
<div id="CanvasDiv">
<div id="BodyDiv">
<div id="BodyWrapDiv">
<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="客户发票录入" alt="客户发票录入">客户发票录入</p>
<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST"><input type="hidden" name="time"
value="<?=$time?>">
<div>
<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
<table class="selection">
<?php 
if (!isset($_POST['invoice_date'])) {
    $_POST['invoice_date'] = date( "Y-m-d", mktime(0,0,0,date("m"),date("d"),date("Y")));
	$_POST['schedule_recevie_date'] = date( "Y-m-d", mktime(0,0,0,date("m"),date("d")+30,date("Y")));
	$_POST['invoice_dis_amount']=0;
    $_POST['tax_amount']=0;
} 
?>
<div class="text-nav">

<div class="text-nav-1 required"><div>客户代码：</div>  
        <input type="text"   name="customer_code" id="text_slect_customer" value="<?=$_POST['customer_code']?>" size="10" maxlength="25" onblur="sel()"/>
			   <image class="select_img" src="img/search.png" id="btn_slect_customer<?=$i?>"/>
			</div>
<div class="text-nav-1 required"><div>客户名称：</div>
    <input   type="text" name="customername" id="text_slect_name" value="<?=$_POST['customername']?>" size="70" maxlength="50" onblur="sel_name()"/></div>
    <div class="text-nav-1"><div>币别:</div>
<input type="text" class="number"  maxlength="10" size="5" name="currency_code"  id="text_slect_currency_code" value="<?=$_POST['currency_code']?>" /> </div>

 
<div class="text-nav-1 required"><div>发票号码:</div>
  <input type="text" autocomplete="off" required="required" maxlength="100" size="20" name="invoice_num"  value="<?=$_POST['invoice_num']?>"  /></div>
  <div class="text-nav-1"><div>发票金额</div>
  <input type="text" class="number" readonly="readonly"  id="invoice_amount_all"    name="invoice_amount_all"  value="<?=$_POST['invoice_amount_all']?>" size="10" maxlength="20"/></div>
  <div class="text-nav-1 required"><div>发票税额</div>
  <input type="text" class="number" required="required"   name="tax_amount"  id="tax_amount" value="<?=$_POST['tax_amount']?>" size="10" maxlength="20"/> </div>   
  <div class="text-nav-1 required"><div>税别：</div>  

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
  
  <div class="text-nav-1 required"><div>发票日期：</div>
  <input type="text" name="invoice_date" maxlength="20" size="10" required="required" value="<?=$_POST['invoice_date']?>"onfocus="WdatePicker() "></div>
  <div class="text-nav-1 required"><div>预计收款日期：</div>
  <input type="text" name="schedule_recevie_date" maxlength="20" size="10" required="required" value="<?=$_POST['schedule_recevie_date']?>"onfocus="WdatePicker() "></div>
 
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
		 <div class="text-nav-2"><div>发票备注：</div>
  <input type="text"  maxlength="200" size="70" name="Header_Remark"  value="<?=$_POST['Header_Remark']?>" size="20" maxlength="20"/>
  <input type="hidden"  size="70" id="text_slect_tax_rate" name="tax_rate"  value="<?=$_POST['tax_rate']?>" size="20" maxlength="20"/>
  </div>
</div>

</table>
<div class="centre">
<input type="submit" name="Hearder" value="录入发票选择销售订单">
<?php

// echo '&nbsp;&nbsp;
//   <a href="' . $RootPath . '/ARInvoiceUpload.php">批量上传</a></div>';
?>
</div>
<input type="hidden" name="PageOffset" value="1"/><br/>
<?php

 $flag=1;
	       $sqlso = "select *
	              from ar_invoice_headers_all  
				  where  invoice_num  ='".$_POST['invoice_num']."'
				  and customer_code ='".$_POST['customer_code']."' 
				  and  invoice_type in ('销售发票') ";
          $result0 = DB_query($sqlso,$db); 
		  if (DB_num_rows($result0) == 0) {
           $flag=0;
		  } else {
		  echo '发票号码已存在';
		  }

if (isset($_POST['customername']) and $_POST['customername'] != '' and $flag==0 ) {
	 
?>
<input id="purchase_table_lastRow" name="purchase_table_lastRow" type=hidden value="">
<div class="centre">  <p id="Prompt" style="color: red;font-size: 20px"></p>
                    </div>
  <div style="overflow:scroll">
<table id="purchase_table" cellpadding="2" class="selection">
<tr id="list-top"> 
 <th width="170">订单号</th>
 <th width="150">客户订单</th>
  <th width="80" >订单行</th>
  <th width="80" >产品图号</th>
  <th width="80" >产品名称</th>
  <th width="80" >未开票金额</th>
  <th width="120" ><font color="red">本次开票</font></th>
  <th width="80" >付款条件</th>
  <th width="80" >订单金额</th> 
  <th width="80" >已开票金额</th>
   
  <th width="80" >备注</th> 
</tr>

<?php for($i=1;$i<=50;$i++){?>
            
                    <tr id="purchase_table_<?=$i?>" <?php echo $i>5 &&$_POST['order_number'.$i]==''?'style="display:none"':''?> class="mouse click">
                    <!--采购单号-->
                    <td><input type="text"  style="background-color:#D2E9FF;" autocomplete="off" name="order_number<?=$i?>" id="text_slect_order_number<?=$i?>" value="<?=$_POST['order_number'.$i]?>" size="15" maxlength="25"/>
					  <image class="select_img" src="img/search.png" id="btn_slect_ship<?=$i?>"/>
                    </td>
                    <!--采购单行号 -->
     <td><input type="text"  readonly="readonly" id="text_slect_customer_order_number<?=$i?>" name="customer_order_number<?=$i?>" value="<?=$_POST['customer_order_number'.$i]?>" size="15" maxlength="20"/></td>
	 <td><input type="text"  readonly="readonly" id="text_slect_so_line<?=$i?>" name="so_line<?=$i?>" value="<?=$_POST['so_line'.$i]?>" size="1" maxlength="20"/></td>
	 <td><input type="text"  readonly="readonly" id="text_slect_item_no<?=$i?>" name="item_no<?=$i?>" value="<?=$_POST['item_no'.$i]?>" size="10" maxlength="20"/></td>
	 <td><input type="text"  readonly="readonly" id="text_slect_item_name<?=$i?>" name="item_name<?=$i?>" value="<?=$_POST['item_name'.$i]?>" size="10" maxlength="20"/></td> 
	  <td><input type="text" class="number"  readonly="readonly" id="text_slect_wait_amount<?=$i?>" name="wait_amount<?=$i?>" value="<?=$_POST['wait_amount'.$i]?>" size="7" maxlength="10"  /></td>

                        <td><input onblur="checktotal()" autocomplete="off" onkeyup="check(<?=$i?>)" style="background-color:#D2E9FF;" class="number" id="this_invoice_amount<?=$i?>" 
						type="text" name="this_invoice_amount<?=$i?>"  value="<?=$_POST['this_invoice_amount'.$i]?>" size="7" maxlength="10"  /> </td>
	 <td><input type="text"  readonly="readonly" id="text_slect_term_name<?=$i?>" name="term_name<?=$i?>" value="<?=$_POST['term_name'.$i]?>" size="10" maxlength="20"/></td>
                        <td><input type="text" class="number"  readonly="readonly" id="text_slect_order_all_amount<?=$i?>" name="order_all_amount<?=$i?>" value="<?=$_POST['order_all_amount'.$i]?>" size="7" maxlength="10"/></td>
						 
                        <td><input type="text" class="number"  readonly="readonly" id="text_slect_invoice_amount<?=$i?>" name="invoice_amount<?=$i?>" value="<?=$_POST['invoice_amount'.$i]?>" size="7" maxlength="10"/></td>

                       
                        <td>
                            <input type="text" autocomplete="off" name="line_remark<?=$i?>" value="<?=$_POST['line_remark'.$i]?>" size="15"maxlength="200"/>
                        </td>
                    

                      <td>  <a onclick="delettr($(this));" style="padding:0px 5px;" href="javascript:;">删除</a></td>
					    <input type="hidden" name="unitprice<?=$i?>" id="text_slect_price<?=$i?>" value="<?=$_POST['unitprice'.$i]?>" size="4" maxlength="10"/> 
 <input  type="hidden" name="po_line_id<?=$i?>" id="text_slect_line_id<?=$i?>" value="<?=$_POST['po_line_id'.$i]?>" size="8" maxlength="25"/>

                         
                    </tr>
                    
                    <?php }?>
 
</table>

</div>
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

function checktotal(){       
								   var  all_invoice_amount=0;              
                                for(var i=1 ; i < 50; i++){   
									if (document.getElementById("this_invoice_amount" + i)==null)  {
									p=0;
										}
									else {							
								   var  invoice_amount=0;
								  
                                   var invoice_amount=document.getElementById("this_invoice_amount"+i).value;                               								     
								   if( invoice_amount>0 )
								   {  
								   all_invoice_amount=Number(all_invoice_amount) + Number(invoice_amount);                                 
                                   }
								   

								    }}
									
                     var tax_rate=document.getElementById("text_slect_tax_rate").value;  

		            document.getElementById("invoice_amount_all").value=all_invoice_amount;
		            document.getElementById("tax_amount").value=Math.round(Number(all_invoice_amount*tax_rate)*100)/100;
								}
   
function checkall(thisform){for(var i=0;i<thisform.elements.length;i++){if(thisform.elements[i].type=="checkbox"&&thisform.elements[i].checked==false&&thisform.elements[i].name!="selectall"){thisform.elements[i].checked=true;}else if(thisform.elements[i].type=="checkbox"&&thisform.elements[i].checked==true&&thisform.elements[i].name!="selectall"){thisform.elements[i].checked=false;}} }
    
function  check(s1){
	    var a=document.getElementById("this_invoice_amount"+s1).value; 
        var c=document.getElementById("text_slect_wait_amount"+s1).value;
      
      
      if(parseFloat(a)>parseFloat(c)){
		  alert("本次开票金额不可以大于未开票金额！！！！");
          
            document.getElementById("this_invoice_amount"+s1).value="";
            document.getElementById("this_invoice_amount"+s1).focus();
        }  else if(parseFloat(a)<0){
            alert("开票金额不可以小于0！！！！");   
            document.getElementById("this_invoice_amount"+s1).value="";
            document.getElementById("this_invoice_amount"+s1).focus();
        }   else {
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
        $('#btn_slect_ship<?=$i?>').dialog({
            title:'选择未开票送货单',
            width: '1050px',
            height: 520,
             content:'url:SearchNoInvoiceship.php?fwValue=<?=$i?>&cat=<?=$_POST['customer_code']?>',
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
            width: '950px',
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

	$(function(){
		$( "#text_slect_customer" ).autocomplete({
			source: "autosearchcustomer.php",
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
				$("#text_slect_tax_name").val(name[2])
		})	
	}

 
</script>
</body>

</html>
<?
include('includes/footer.inc');
?>

