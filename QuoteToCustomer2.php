<?php
 
include('includes/session.inc');
$Title = _('向客户报价');
$ViewTopic= '向客户报价';
$BookMark = '向客户报价';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

if (isset($_GET['Updateorder_number']) ) {
$_POST['order_number']=$_GET['Updateorder_number'];
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
	   if ($_POST['baojia'.$i]=='') 
		  {
            $errorflag = 1;
            prnMsg($value.'客户报价未填写,请确认！',error);
          }
		 
    if($_POST['status'.$i]<>'')
	{   
     
          if ($_POST['baojia'.$i]=='') 
		  {
            $errorflag = 1;
            prnMsg($value.'客户报价未填写,请确认！',error);
          }
		 
  
    }


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

	   $sql = "update  quote_lines_all
                    set baojia     =  '".$_POST['baojia'.$i]."' ,
					baojia_rate =  '".$_POST['baojia_rate'.$i]."' ,
					customer_reply='待回复'
                  where  order_number  ='".$_POST['order_number']."' 
				   and   line ='".$_POST['line'.$i]."' ";
          $result = DB_query($sql,$db);

	   if($_POST['status'.$i]<>'')
		   {
         
        
    
          $sql = "update  quote_lines_all
                    set baojia     =  '".$_POST['baojia'.$i]."' ,
					baojia_rate =  '".$_POST['baojia_rate'.$i]."' ,
					customer_reply='待回复'
                  where  order_number  ='".$_POST['order_number']."' 
				   and   line ='".$_POST['line'.$i]."' ";
          $result = DB_query($sql,$db);
		   
 
        }
      } 	
      if($_POST['invoice_dis_amount']=='')
		  {
            $_POST['invoice_dis_amount'] = 0;
          }
 
	  $sql = "update  quote_headers_all
                    set  status='报价中',
					order_amount =  '".$_POST['order_amount']."' 
                  where  order_number  ='".$_POST['order_number']."'   ";
          $result = DB_query($sql,$db);

          
    DB_Txn_Commit($db);
	if ($k>0) {
    prnMsg('报价申请单'.$_POST['order_number'].'回复完成！',success);
    echo "<script>location.href='index.php';</script>";
	}

        
    }


 
   
}

?>
 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>报价申请研发处理</title>
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
<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="向客户报价" alt="向客户报价">向客户报价</p>
<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST"><input type="hidden" name="time"
value="<?=$time?>">
<div>
<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
<table class="selection">

<?php
	 

$sql ="SELECT b.customer_code,
    b.customer_name, 
    p.order_number,
    p.status,  
    p.yewu,
    p.need_date, 
    p.creation_date,
    p.created_by,
    p.last_update_date,
    p.last_updated_by ,(select employee_name from hr_employees where employee_num=yewu) employee_name
FROM quote_headers_all p, customers b
WHERE p.customer_code = b.customer_code
and p.order_number = '" .$_POST['order_number'] . "' ";
	// echo $sql;
	$result = DB_query($sql,$db);
    if (DB_num_rows($result)==0) {
        unset($result);
        prnMsg(_('没有预测，请重新输入条件查询！') ,'error');
	 }
 while ($myrow = DB_fetch_array($result))  {
       $_POST['customer_code']=$myrow['customer_code'] ;
       $_POST['customer_name']=$myrow['customer_name'] ;  
	   $_POST['employee_name']=$myrow['employee_name'] ; 
	   $_POST['need_date']=$myrow['need_date'] ; 
	   $_POST['order_number']=$myrow['order_number'] ;  
 }     
?>

<tr>
 <td>报价申请单号码:</td>
  <td  ><input type="text" required="required" maxlength="100" size="20" name="order_number"  value="<?=$_POST['order_number']?>" size="15" maxlength="20"/> </td>
  <td>客户代码：</td>
  <td><input type="text" required="required" name="customer_code" id="text_slect_vendor" value="<?=$_POST['customer_code']?>" size="10" maxlength="25"/> </td>
  <td>业务：</td>
  <td  ><input readonly="readonly" type="text"   name="employee_name" id="text_slect_currencycode" value="<?=$_POST['employee_name']?>" size="5" maxlength="10"/></td>
   <td>需求日期：</td>
  <td><input type="text" name="need_date" maxlength="20" size="10" required="required" value="<?=date('Y-m-d',$_POST['need_date'])?>" onfocus="WdatePicker() "></td>
</tr>

<tr>
  <td>客户名称：</td>
  <td colspan="4"><input readonly="readonly" type="text" name="customer_name" id="text_slect_name" value="<?=$_POST['customer_name']?>" size="70" maxlength="50"/></td>
  <td>税别：</td>
  <td  ><input readonly="readonly" type="text"   name="tax_code" id="text_slect_currencycode" value="<?=$_POST['tax_code']?>" size="10" maxlength="10"/></td>
</tr>
  <tr>      
  <td>报价合计：</td> 
			 <td  ><input type="text" class="number"  maxlength="200" size="20" id="order_amount" name="order_amount"  value="<?=$_POST['order_amount']?>"  /> </td> 
		 <td>Subject主题：</td> 
			 <td  ><input type="text"  maxlength="200" size="20" name="subject"  value="<?=$_POST['subject']?>"  /> </td> 
			 <td>Project项目号：</td> 
			 <td  ><input type="text"  maxlength="200" size="20" name="project"  value="<?=$_POST['project']?>"  /> </td>   
             </tr> 
 
</table>

<input type="hidden" name="PageOffset" value="1"/><br/>
<?php
if (isset($_POST['customer_name']) and $_POST['customer_name'] != '') {

	$sql ="select *
				from quote_lines_all c
				where  c.order_number= '" .$_POST['order_number'] . "'
				  ";
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
   
  <th width="2">行</th> 
 <th width="20" >类型</th>
					<th width="20">尺寸</th>
					<th width="180"  >工艺要求</th> 
					<th width="80"  >单位</th>
					<th width="80"  >数量</th>
  <th width="10" >指导价</th>
  <th width="10" >备注说明</th>
  <th width="10" bgcolor="#87CEFA" >客户报价</th>
  <th width="10"  bgcolor="#87CEFA">报价系数</th>
  
  <th width="15" align="center">选择</th>
 
</tr>
<?php   
$i=1;
 
 while ($myrow = DB_fetch_array($result)  )  {
	
	 ?>
		 
<tr id="purchase_table_<?=$i?>" >

  <td> <input type="text" readonly="readonly" name="line<?=$i?>" id="text_slect_receipt_line<?=$i?>" value="<?= $myrow['line'] ?>" size="1" maxlength="10"/></td>

  <td><input readonly="readonly" type="text" name="leixing<?=$i?>" id="leixing<?=$i?>" value="<?= $myrow['leixing'] ?>" size="10" maxlength="150"/></td>
 
<td><textarea  readonly="readonly" cols="10" rows="2" type="text" name="chima<?=$i?>" id="chima<?=$i?>"   >    <?= $myrow['chima'] ?>  </textarea></td>
 
  <td><textarea  readonly="readonly" cols="20" rows="2" type="text" name="need_remark<?=$i?>" id="text_slect_remark<?=$i?>"   >    <?= $myrow['need_remark'] ?>  </textarea></td>
 
   <td><input type="text" name="uom<?=$i?>" id="text_slect_buliao<?=$i?>" value="<?=$myrow['uom']?>" size="5" maxlength="50"  /> 
	 <td><input type="text" name="need_qty<?=$i?>" id="text_slect_need_qty<?=$i?>" value="<?=$myrow['need_qty']?>" size="5" maxlength="50"  /> 

  <td><input type="text" readonly="readonly" class="number"  name="price<?=$i?>"  id="text_this_price<?=$i?>"    value="<?=  $myrow['price']?>"     size="5" maxlength="10"/></td>
<td><textarea  readonly="readonly" cols="20" rows="2" type="text" name="yanfa_remark<?=$i?>" id="text_slect_yanfa_remark<?=$i?>"   >    <?= $myrow['yanfa_remark'] ?>  </textarea></td> 
  <td><input type="text" class="number" name="baojia<?=$i?>" onkeyup="check(<?=$i?>)" onblur="checktotal()" id="text_this_baojia<?=$i?>"  value="<?= $myrow['baojia']?>" size="5" maxlength="200"/></td>
  <td><input type="text"   class="number" onkeyup="check2(<?=$i?>)" onblur="checktotal()" name="baojia_rate<?=$i?>"  id="text_this_baojia_rate<?=$i?>"  value="<?= $myrow['baojia_rate']?>" size="5" maxlength="200"/></td>

  <td><input type="checkbox" name="status<?=$i?>" checked /></td>
 <td> 

  <input type="hidden"  name="order_number<?=$i?>"  value="<?= $myrow['order_number'] ?>" size="8" maxlength="10"/>
 
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
<input type="submit" name="Save" value="确认保存"> &nbsp;&nbsp;
<a href="javascript:window.opener=null;window.open('','_self');window.close();">关闭</a>
</div>
<br/>
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
									if (document.getElementById("text_this_baojia" + i)==null)  {
									p=0;
										}
									else {							
								   var  invoice_amount=0; 
                                   var baojia=document.getElementById("text_this_baojia"+i).value; 
                                   var need_qty=document.getElementById("text_slect_need_qty"+i).value; 
								   invoice_amount=baojia*need_qty;
								   if( invoice_amount>0 )
								   {  
								   all_invoice_amount=Number(all_invoice_amount) + Number(invoice_amount);                                 
                                   }							  

								    }}
									
                    // var tax_rate=document.getElementById("text_slect_tax_rate").value;  

		            document.getElementById("order_amount").value=all_invoice_amount;
		           
		            //document.getElementById("tax_amount").value=Math.round(Number(all_invoice_amount*tax_rate)*100)/100;
								}
  
function  check(s1){
	    var a=document.getElementById("text_this_baojia"+s1).value;
        var b=document.getElementById("text_this_price"+s1).value;
		if (a=>0)
		{  document.getElementById("text_this_baojia"+s1).value=Math.round(Number(a)*1)/1;
		   document.getElementById("text_this_baojia_rate"+s1).value=Math.round(Number(a/b)*100)/100;
		}  else if (a<0) {
			document.getElementById("Prompt").innerHTML="报价不可以小于0！！！！";
            document.getElementById("text_this_baojia_rate"+s1).value="";
            document.getElementById("text_this_baojia_rate"+s1).focus();
		}
		else { 
            document.getElementById("Prompt").innerHTML="";
        }
        
    
     }
	 function  check2(s1){
	    var a=document.getElementById("text_this_price"+s1).value;
        var b=document.getElementById("text_this_baojia_rate"+s1).value;
		if (b=>0)
		{
		   document.getElementById("text_this_baojia"+s1).value=Math.round(Number(a*b)*1)/1;
		}  else if (b< 0) {
			document.getElementById("Prompt").innerHTML="报价系数不可以小于0！！！！";
            document.getElementById("text_this_baojia_rate"+s1).value="";
            document.getElementById("text_this_baojia_rate"+s1).focus();
		}
		else { 
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

