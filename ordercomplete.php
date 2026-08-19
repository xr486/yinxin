<?php
if(isset($_GET['data'])){
	 
	 include_once("connect.php"); 
	 $sql = "select * from vendors where vendor_code = '".$_GET['data']."'";
	 $result_num = mysql_query($sql, $db);
	 $res = mysql_fetch_assoc($result_num);
	 echo $res['vendor_name'].':'.$res['vendor_address'].':'.$res['vendor_contacts'].':'.$res['currencycode'];
	 return ;
 } 	 	
if(isset($_GET['data2'])){
	 //$sql = "select * from customers where customer_name = '".$_GET['data']."'";
	 include_once("connect.php"); 
	 $sql = "select * from vendors where vendor_name = '".$_GET['data2']."'";
	 $result_num = mysql_query($sql, $db);
	 $res_customer_name = mysql_fetch_assoc($result_num);
	 echo $res_customer_name['vendor_code'].':'.$res_customer_name['vendor_address'].':'.$res_customer_name['vendor_contacts'].':'.$res_customer_name['currencycode'];
	 return ;
 }
  if(isset($_GET['data3'])){
	 //$sql = "select * from customers where customer_name = '".$_GET['data']."'";
	 include_once("connect.php"); 
	 $sql = "select * from sf_item_no where item_category1<>'成品料号' and item_no = '".$_GET['data3']."'";
	 $result_num = mysql_query($sql, $db);
	 $res_item = mysql_fetch_assoc($result_num);
	 echo $res_item['item_name'].':'.$res_item['item_desc'].':'.$res_item['units'];
	 return ;
 }
include('includes/session.inc');
$Title = _('订单完工入库');
$ViewTopic= '订单入库';
$BookMark = '订单入库';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

unset($result);
if (isset($_POST['Save'])) {
        $errorflag = 1;
        foreach ($_POST as $key => $value) {
            if ($value != '') {
                if (substr($key, 0,7)=='stockid') {
                    $errorflag = 0;
                    $i = substr($key, 7);
                  
                }
            }
        }
        if ($errorflag ==0) {
            foreach ($_POST as $key => $value) {
                if ($value != '') {
                    if (substr($key, 0,7)=='stockid') {
                        $i = substr($key, 7);
                     
                        $lineamount[$i] = $_POST['quantity'.$i] * $_POST['unitprice'.$i] ;
                        
                    }
                }
            }
        }
 
        
        
        
        
        
    if ($errorflag == 0) 
  {
    $sumamount=0.00;
         $date = date('Ymd');
        $sql_num = "select  (
        CASE WHEN substr(max(receipt_num) ,-2,1) = 0 THEN
            RIGHT (
                '100' + (
                    max(substr(receipt_num ,- 1)) + 1
                ),
                2
            )
        ELSE
            substr(max(receipt_num),-2,2) + 1
        END
        ) trans_num from po_rcv_receipt_header where substr(receipt_num,1,2)='RE'  and substr(receipt_num,-10,8) = '" . $date . "'";

        $result_num = DB_query($sql_num, $db);
        $rownum = DB_num_rows($result_num);
        while ($v = DB_fetch_array($result_num)) {
            if ($v['trans_num'] == null) {
                $OrderNum = 'RE'.$date . '01';
            } else {
                $OrderNum =  'RE'. $date . $v['trans_num'];
            }
        } 
            DB_Txn_Begin($db);
            $time = time();
            $order_amount = 0;
			$line_num=0;
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
            
                        $line_num=$line_num+1;
             

          /*$sql = "insert into po_rcv_receipt_line(
							receipt_num,
							receipt_line,
							po_num,
							po_line,
							stockid,
						
							quantity_received,
                            transaction_quantity,
							remark,
							transaction_date,
							line_amount,
							unit_price,
							subinventory_code,uom,
							creation_date,
							created_by,
							last_update_date,
							last_updated_by,
							status,
							invoice_amount,
							invoice_dis_amount,
							payment_amount,
							payment_dis_amount,
							inspection_bad_return_vendor,
							RETURN_TO_VENDOR)
						values
						('".$OrderNum."',
						 '".$line_num."',
						 '".$_POST['po_num'.$i]."',
						 '".$_POST['po_line'.$i]."',
						 '".$_POST['stockid'.$i]."',
						 
						 '".$_POST['quantity_received'.$i]."',
						 '".$_POST['wait_amount'.$i]."',
						 '".$_POST['remark'.$i]."',
						 '".$time."',
						 '".$lineamount[$i]."',
						 '".$_POST['price'.$i]."',
						 '".$_POST['subinventory_code'.$i]."',
						 '".$_POST['UOM'.$i]."',
						 '".$time."',
						 '".$_SESSION['UserID']."',
						 '".$time."',
						 '".$_SESSION['UserID']."',
						 'INPROCESS',
                         0,
						 0,
						 0,
						 0,
						 0,
						 0) ";
						
						$result = DB_query($sql,$db);
						$sql = "insert into po_rcv_transactions(
							receipt_num,receipt_line,po_num,po_line,stockid,
							transaction_quantity,transaction_date,transaction_type,
							creation_date,created_by,last_update_date,last_updated_by)
						values('".$OrderNum."','".$line_num."','".$_POST['po_num'.$i]."','".$_POST['po_line'.$i]."','".$_POST['stockid'.$i]."','".$_POST['this_quantity'.$i]."','".$time."','RECEIVE','".$time."','".$_SESSION['UserID']."','".$time."','".$_SESSION['UserID']."') ";
						
						$result = DB_query($sql,$db);*/

						$sqlinsertinv = "insert into inv_onhand_quantity_all(stockid,quantity,subinventory_code,last_update_date,last_updated_by,creation_date,created_by) values('" . $_POST['stockid'.$i] . "','" . $_POST['this_quantity'.$i] . "','" . $_POST['subinventory_code'.$i] . "','" . $time . "','" . $_SESSION['UserID'] . "','" . $time . "','" . $_SESSION['UserID'] . "')";
            $result_inv = DB_query($sqlinsertinv, $db);


			$sqlinvtrancsation = "insert into inv_transactions_all(so_order_number,transaction_type,transaction_date,quantity,uom,item_no,subinventory_from,wip_entity_name,creation_date,created_by,last_update_date,last_updated_by,trans_num) ";
            $sqlinvtrancsation.="values('" . $_POST['order_number'.$i] . "','ORDER入库','" . $time . "', '" . $_POST['this_quantity'.$i] . "','" .$_POST['UOM'.$i] . "','" . $_POST['stockid'.$i] . "','" . $_POST['subinventory_code'.$i] . "','" . $_POST['Header_Remark']  . $_POST['remark'.$i] . "','" . $time . "','" . $_SESSION['UserID'] . "','" . $time . "','" . $_SESSION['UserID'] . "','" . $OrderNum . "')";
            $result_invtrancsation = DB_query($sqlinvtrancsation, $db);
          
                        

                        $sqlso = "UPDATE  so_lines_all
                        SET
						quantity_completed= quantity_completed  +'".$_POST['this_quantity'.$i]."'
                        where  order_line_id ='".$_POST['order_line_id'.$i]."'";                        
                        $resultso = DB_query($sqlso,$db);

						 $lineamount[$i]= $_POST['quantity'.$i] * $_POST['unitprice'.$i];

						$order_amount = $order_amount + $lineamount[$i];
        }
      }
      
         /*$sql = "insert into po_rcv_receipt_header ( receipt_num, vendor_code, 
                                                           delivery_date,
                                                          need_payment_amount,receive_remark,
                                                           creation_date,created_by,last_update_date,last_updated_by,receipt_type)
                                                           values('".$OrderNum."','".$_POST['vendor_code']."',
                                                            '".$time."',
                                                          '".$order_amount."','".$_POST['Header_Remark']."',
                                                           '".$time."','".$_SESSION['UserID']."','".$time."','".$_SESSION['UserID']."','入库')";
             $result = DB_query($sql,$db);*/
            
            DB_Txn_Commit($db);
            prnMsg('订单完工入库编号'.$OrderNum.'建立成功！',success);
            
           // header("Location: SucssCreate78.php?OrderNum=$OrderNum");
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
<script src="/javascript/bootstrap.min.js"></script>

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
<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="订单完工入库" alt="订单完工入库">订单完工入库</p>
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

  
<tr>
  <td bgcolor="#87CEFA">客户简称：</td>  
			<td ><input type="text"  name="customercode" id="text_slect_customer" value="<?=$_POST['customercode']?>" size="10" maxlength="25" onblur="sel()"/>
                       <a class="btn btn-info btn-xs" id="btn_slect_customer<?=$i?>" hfre="###" title="选择客户">选</a> </td>
      
		<td bgcolor="#87CEFA">客户名称：</td>
		 <td colspan="3"><input  type="text" name="customername" id="text_slect_name" value="<?=$_POST['customername']?>" size="60" maxlength="150" onblur="sel_name()"/></td>



</tr>



</table>
<div class="centre">
<input type="submit" name="Hearder" value="保存订单完工入库头信息">
</div>
<input type="hidden" name="PageOffset" value="1"/><br/>
<?php
if (isset($_POST['customercode']) and $_POST['customercode'] != '') {


$sql ="select a.order_line_id,a.order_number,a.line,a.stockid,b.item_desc,quantity,price,line_amount,a.uom,b.item_name,a.quantity,(a.quantity-a.quantity_completed) wait_amount,a.quantity_completed,a.subinventory_code,d.locationname
	from so_lines_all a,sf_item_no b,so_headers_all c,locations d
				where a.stockid=b.item_no 
				and a.order_number = c.order_number
                and a.subinventory_code=d.loccode
				and a.stockid = b.item_no   and a.quantity > a.quantity_completed and fin_approved= 'Y' and status not in ('REJECTED')
				and c.customer_code= '" .$_POST['customercode'] . "'";
	// echo $sql;
	$result = DB_query($sql,$db);
    if (DB_num_rows($result)==0) {
		$line_count=DB_num_rows($result);
        unset($result);
        prnMsg(_('该客户没有符合的订单，请重新输入条件查询！，请重新输入条件查询！') ,'error');
    }

?>
<input id="purchase_table_lastRow" name="purchase_table_lastRow" type=hidden value="">

<div class="centre"> 
                        <p id="Prompt" style="color: red;font-size: 20px"></p>
                    </div>

<table id="purchase_table" cellpadding="2" class="selection">
 
<tr id="list-top">
  <th width="10">订单</th>
  <th width="2">行</th>
  <th width="10" >料号</th>
  <th width="20" >料号名称</th>
  <th width="10" >规格型号</th>
  <th width="10" >订单数量</th>
  <th width="5" >单位</th>
 
 <th width="20" >仓库</th>

  <th width="10" >未入库量</th>
  <th width="10" ><font color="#1E90FF">本次入库量</font></th>
  <th width="10" >备注</th>
  <th width="15" align="center">选择</th>
 
</tr>
<?php   
$i=1;
 
 while ($myrow = DB_fetch_array($result)  )  {
	 $_POST['this_invoice_dis_amount'.$i]=0;
	 ?>
	
    	 
<tr id="purchase_table_<?=$i?>" >

  <td> <input type="text" readonly="readonly" name="order_number<?=$i?>" id="text_slect_receipt_num<?=$i?>" value="<?= $myrow['order_number'] ?>" size="13" maxlength="25"/> </td>

  <td> <input type="text" readonly="readonly" name="so_line<?=$i?>" id="text_slect_receipt_line<?=$i?>" value="<?= $myrow['line'] ?>" size="2" maxlength="3"/></td>

  <td><input readonly="readonly" type="text" name="stockid<?=$i?>" id="text_slect_remark<?=$i?>" value="<?= $myrow['stockid'] ?>" size="20" maxlength="150"/></td>

  <td><input type="text" readonly="readonly" name="item_name<?=$i?>"  id="text_slect_transaction_date<?=$i?>" value="<?= $myrow['item_name'] ?>" size="20" maxlength="10"/></td>

  <td><input type="text" readonly="readonly" name="item_desc<?=$i?>" id="text_slect_check_amount<?=$i?>" value="<?= $myrow['item_desc'] ?>" size="10" maxlength="20"/></td>

  <td><input type="text" readonly="readonly" name="quantity<?=$i?>" id="text_slect_invoice_amount<?=$i?>" value="<?= $myrow['quantity'] ?>" size="6" maxlength="15"/></td>

  <td><input type="text" readonly="readonly" name="uom<?=$i?>"  id="text_slect_invoice_dis_amount<?=$i?>" value="<?= $myrow['uom'] ?>" size="4" maxlength="10"/></td>

 <td><input type="text" readonly="readonly" name="subinventory_code<?=$i?>"  id="text_slect_subinventory<?=$i?>" value="<?= $myrow['subinventory_code'] ?>" size="4" maxlength="10"/></td>
    
  <td><input type="text" readonly="readonly" name="wait_amount<?=$i?>"  id="text_slect_wait_amount<?=$i?>" value="<?= $myrow['wait_amount'] ?>" size="6" maxlength="10"/></td>  

 <td><input style="background-color:#D2E9FF;" id="this_receive_quantity<?=$i?>" type="text" name="this_quantity<?=$i?>" value="<?=$myrow['wait_amount']?>" size="4" maxlength="10"  onblur="check55(<?=$i?>)"/></td>
 <td>
<input type="text" name="remark<?=$i?>" value="<?=$_POST['remark'.$i]?>" size="15"maxlength="200"/>
  <td><input type="checkbox" name="status<?=$i?>" /></td>
 <td>
  <input type="hidden"  name="order_line_id<?=$i?>"  value="<?= $myrow['order_line_id'] ?>" size="8" maxlength="10"/>
 
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
        var d=parseInt(a) + parseInt(b);
      if(parseInt(a)>parseInt(c)){
            document.getElementById("Prompt").innerHTML="本次开票金额不可以大于待开票金额！！！！";
            document.getElementById("text_this_invoice_amount"+s1).value="";
            document.getElementById("text_this_invoice_amount"+s1).focus();
        } else if(parseInt(b)>parseInt(c)){
            document.getElementById("Prompt").innerHTML="本次免开票金额不可以大于待开票金额！！！！";
            document.getElementById("text_this_invoice_dis_amount"+s1).value="";
            document.getElementById("text_this_invoice_dis_amount"+s1).focus();
        }else if(parseInt(d)>parseInt(c)){
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
            content:'url:SearchNoInvoicePo2.php?fwValue=<?=$i?>&cat=<?=$_POST['vendor_code']?>',
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


	
             
             
             	$('#btn_slect_customer').dialog({
            title:'选择客户',
            width: '1050px',
            height: 470,
            content:'url:BtnSearchCustomer123.php?fwValue=&cat=buliao',
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
 

	function checkinvoice_num(){
var invoice_num = $("#invoice_num").val();
var vendor_code = $("#text_slect_vendor").val();
$.get("checkvendorinvoice.php",{invoice_num: invoice_num,vendor_code: vendor_code,type: '普通发票'},function(txt){
if(txt == 0){ 
	document.getElementById("Prompt").innerHTML=invoice_num+"此供应商该发票号码已录入,不能重复录入,请确认";
	document.getElementById("invoice_num").value="";
    document.getElementById("invoice_num").focus();
	}
});
}
function checkall(thisform){for(var i=0;i<thisform.elements.length;i++){if(thisform.elements[i].type=="checkbox"&&thisform.elements[i].checked==false&&thisform.elements[i].name!="selectall"){thisform.elements[i].checked=true;}else if(thisform.elements[i].type=="checkbox"&&thisform.elements[i].checked==true&&thisform.elements[i].name!="selectall"){thisform.elements[i].checked=false;}} }


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
function  check55(s1){
	    var a=document.getElementById("text_slect_wait_amount"+s1).value;
        var b=document.getElementById("this_receive_quantity"+s1).value;  
      if(parseInt(b)>parseInt(a)){
            document.getElementById("Prompt").innerHTML="本次入库数量"+b+"不可以大于未入库数量！"+a;
            document.getElementById("this_receive_quantity"+s1).value="";
            document.getElementById("this_receive_quantity"+s1).focus();
        } else if(parseInt(b) <=0 ){
            document.getElementById("Prompt").innerHTML="入库数量必须大于0！"+a;
            document.getElementById("this_receive_quantity"+s1).value="";
            document.getElementById("this_receive_quantity"+s1).focus();
        } else {
            document.getElementById("Prompt").innerHTML="";
        }
     }
</script>
</body>

</html>
<?
 
include('includes/footer.inc');
?>

