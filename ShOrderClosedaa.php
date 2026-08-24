<?php
include('includes/session.inc');
$Title = _('售后单结案');
$ViewTopic= '售后单结案';
$BookMark = '售后单结案';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

if (isset($_GET['Updateorder_number']) ) {
$_POST['sh_order_num']=$_GET['Updateorder_number'];
}

unset($result);
 
if (isset($_POST['Reject'])) 
{
  $errorflag = 0;
  $all_amount = 0;
  $all_invoice_dis_amount = 0;
  $k =0;
   for ($i=1; $i<=$_POST['flag'];$i++){
    if($_POST['sh_order_num'.$i]<>''){        
      if ($_POST['chuli_method'.$i]==''){
        $errorflag = 1;
        prnMsg($value.'处理方法未填写,请确认！',error);
      }	 
		  if ($_POST['finish_date'.$i]==''){
        $errorflag = 1;
        prnMsg($value.'完成日期未填写,请确认！',error);
      }	  
		  if ($_POST['amount'.$i]=='') 
		  {
        $errorflag = 1;
        prnMsg($value.'金额未填写,请确认！',error);
      }	 
      if ($_POST['chuli_ok'.$i]=='') 
		  {
        $errorflag = 1;
        prnMsg($value.'是否解决未填写,请确认！',error);
      }	  
    }
  }
  if ($errorflag == 0) 
  {
    //$finish_date = strtotime($_POST['finish_date'.$i]);
    DB_Txn_Begin($db);
    $time = time();
    $delivery_amount = 0;
    $k =0;
   for ($i=1; $i<=$_POST['flag'];$i++){
       $k = $k +1; 
	    if($_POST['sh_order_num'.$i]<>''){         
        if($_POST['amount'.$i]==''){
          $_POST['amount'.$i] = '0';
          $bumishu[$i] = 0;
        }  
        $sql = "update sh_order_headers_all
                  last_update_date =  '".$time."',
                  last_updated_by = '".$_SESSION['UserID']."'
                where sh_order_num  ='".$_POST['sh_order_num']."' 
           ";
           
        $result = DB_query($sql,$db);
		   
      }
  } 	    
       
    DB_Txn_Commit($db);
	if ($k>0) {
    prnMsg('售后单'.$_POST['sh_order_num'].'取消！',success);
    echo "<script>location.href='index.php';</script>";
	}
        
    }    
}
 
 if (isset($_POST['Agree'])) 
{
  $errorflag = 0;
  $all_amount = 0;
  $all_invoice_dis_amount = 0;
  $k =0;
   for ($i=1; $i<=$_POST['flag'];$i++)
  {
    if($_POST['sh_order_num'.$i]<>'')
	{        
    if ($_POST['chuli_method'.$i]==''){
      $errorflag = 1;
      prnMsg($value.'处理方法未填写,请确认！',error);
    }	 
    if ($_POST['finish_date'.$i]==''){
      $errorflag = 1;
      prnMsg($value.'完成日期未填写,请确认！',error);
    }	  
    if ($_POST['amount'.$i]=='') 
    {
      $errorflag = 1;
      prnMsg($value.'金额未填写,请确认！',error);
    }	 
    if ($_POST['chuli_ok'.$i]=='') 
    {
      $errorflag = 1;
      prnMsg($value.'是否解决未填写,请确认！',error);
    }	  
  }
  }
  if ($errorflag == 0) 
  {
    DB_Txn_Begin($db);
    $time = time();
    $delivery_amount = 0;
    $k =0;
   for ($i=1; $i<=$_POST['flag'];$i++)
	{
       $k = $k +1; 
	   if($_POST['sh_order_num'.$i]<>'')
		   {         
        if($_POST['amount'.$i]==''){
          $_POST['amount'.$i] = '0';
          $bumishu[$i] = 0;
        }
          $finish_date = strtotime($_POST['finish_date'.$i]);
          $sql = "update sh_order_lines_all
                  set chuli_person = '".$_POST['chuli_person'.$i]."' ,
                      chuli_method = '".$_POST['chuli_method'.$i]."' ,
                      finish_date = '".$finish_date."',
                      amount = '".$_POST['amount'.$i]."' ,
                      chuli_ok = '".$_POST['chuli_ok'.$i]."',
                      last_update_date = '".$time."',
                      last_updated_by = '".$_SESSION['UserID']."'
                  where sh_order_num ='".$_POST['sh_order_num']."' 
                        and line_num ='".$_POST['line_num'.$i]."'  
                ";
          $result = DB_query($sql,$db);
        }
      } 	    
 
    DB_Txn_Commit($db);
	if ($k>0) {
		$sql = "update sh_order_headers_all
            set status = '结案',
                last_update_date = '".$time."',
                last_updated_by = '".$_SESSION['UserID']."'
            where sh_order_num ='".$_POST['sh_order_num']."'  
    ";
    $result = DB_query($sql,$db);
    prnMsg('售后单'.$_POST['sh_order_num'].'结案！',success);
    echo "<script>location.href='index.php';</script>";
	}  
  }    
}

if (isset($_POST['Save'])) 
{
  $errorflag = 0;
  $all_amount = 0;
  $all_invoice_dis_amount = 0;
  $k =0;
   for ($i=1; $i<=$_POST['flag'];$i++)
  {
    if($_POST['sh_order_num'.$i]<>'')
	{        
    if ($_POST['chuli_method'.$i]==''){
      $errorflag = 1;
      prnMsg($value.'处理方法未填写,请确认！',error);
    }	 
    if ($_POST['finish_date'.$i]==''){
      $errorflag = 1;
      prnMsg($value.'完成日期未填写,请确认！',error);
    }	  
    if ($_POST['amount'.$i]=='') 
    {
      $errorflag = 1;
      prnMsg($value.'金额未填写,请确认！',error);
    }	 
    if ($_POST['chuli_ok'.$i]=='') 
    {
      $errorflag = 1;
      prnMsg($value.'是否解决未填写,请确认！',error);
    }	  
    }
  }
  if ($errorflag == 0) 
  {
    $delivery_date = strtotime($_POST['delivery_date']);
    DB_Txn_Begin($db);
    $time = time();
    $delivery_amount = 0;
    $k =0;
   for ($i=1; $i<=$_POST['flag'];$i++)
	{
       $k = $k +1; 
       if($_POST['sh_order_num'.$i]<>'')
		   {         
        if($_POST['amount'.$i]==''){
          $_POST['amount'.$i] = '0';
          $bumishu[$i] = 0;
        }
          $finish_date = strtotime($_POST['finish_date'.$i]);
          $sql = "update sh_order_lines_all
                  set chuli_person = '".$_POST['chuli_person'.$i]."' ,
                      chuli_method = '".$_POST['chuli_method'.$i]."' ,
                      finish_date = '".$finish_date."',
                      amount = '".$_POST['amount'.$i]."' ,
                      chuli_ok = '".$_POST['chuli_ok'.$i]."',
                      last_update_date = '".$time."',
                      last_updated_by = '".$_SESSION['UserID']."'
                  where sh_order_num ='".$_POST['sh_order_num']."' 
                        and line_num ='".$_POST['line_num'.$i]."' 
                ";
          $result = DB_query($sql,$db);
        }
      } 	    
  
    DB_Txn_Commit($db);
	if ($k>0) {
		$sql = "update sh_order_headers_all
            set delivery_date = '".$delivery_date."',
                status = '保存' ,
                last_update_date = '".$time."',
                last_updated_by = '".$_SESSION['UserID']."'
            where sh_order_num ='".$_POST['sh_order_num']."'
          ";

    $result = DB_query($sql,$db);
    prnMsg('售后单'.$_POST['sh_order_num'].'修改完成！',success);
    echo "<script>location.href='index.php';</script>";
	}
        
    }    
}

?>
 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>售后单结案</title>
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
<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="售后服务单结案" alt="售后服务单结案">售后服务单结案</p>
<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST"><input type="hidden" name="time"
value="<?=$time?>">
<div>
<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
<table class="selection">

<?php


$sql ="SELECT c.customer_code,
    c.customer_name, 
    c.customer_contacts,
    c.contacts_phone,
    a.sh_order_num,
    a.moju_num,  
    a.moju_name,
    a.promise_date,
    -- a.delivery_date,
    a.problem_desc, 
    a.creation_date,
    a.created_by,
    a.last_update_date,
    a.last_updated_by
FROM sh_order_headers_all a,sh_order_lines_all b,customers c
WHERE a.customer_code = c.customer_code
and a.sh_order_num = '" .$_POST['sh_order_num'] . "'
and a.sh_order_num=b.sh_order_num";
	// echo $sql;
	$result = DB_query($sql,$db);
    if (DB_num_rows($result)==0) {
        unset($result);
        prnMsg(_('没有信息，请重新输入条件查询！') ,'error');
	 }
 while ($myrow = DB_fetch_array($result))  {
    $_POST['customer_code']=$myrow['customer_code'] ;
    $_POST['customer_name']=$myrow['customer_name'] ;  
	  $_POST['customer_contacts']=$myrow['customer_contacts'] ; 
	  $_POST['contacts_phone']=$myrow['contacts_phone'] ; 
	  $_POST['sh_order_num']=$myrow['sh_order_num'] ;  
    $_POST['moju_num']=$myrow['moju_num'] ; 
    $_POST['moju_name']=$myrow['moju_name'] ; 
    $_POST['promise_date']=$myrow['promise_date'] ; 
    // $_POST['delivery_date']=$myrow['delivery_date'] ; 
    $_POST['problem_desc']=$myrow['problem_desc'] ; 
 }     

 if (!isset($_POST['delivery_date'])) {
  $_POST['delivery_date'] = Date('Y-m-d');
 } 
?>

<tr>
  <td>售后单号:</td>
  <td  ><input type="text" readonly="readonly" name="sh_order_num" value="<?=$_POST['sh_order_num']?>" size="20" maxlength="25"/> </td>
  <td>模具编号:</td>
  <td><input type="text" readonly="readonly" name="moju_num" value="<?=$_POST['moju_num']?>" size="20" maxlength="25"/> </td>
  <td>模具名称：</td>
  <td  ><input type="text" readonly="readonly" name="moju_name" value="<?=$_POST['moju_name']?>" size="45" maxlength="50"/></td>
</tr>
<tr>
  <td>联系人:</td>
  <td><input type="text" readonly="readonly" name="customer_contacts" value="<?=$_POST['customer_contacts']?>" size="20" maxlength="20"/> </td>
  <td>客户简称：</td>
  <td><input type="text" readonly="readonly" name="customer_code" value="<?=$_POST['customer_code']?>" size="20" maxlength="25"/></td>
  <td>客户名称：</td>
  <td><input type="text" readonly="readonly" name="customer_name" value="<?=$_POST['customer_name']?>" size="45" maxlength="50"/></td>
</tr>
<tr>
  <td>预计交货日期：</td>
  <td><input type="text" readonly="readonly" name="promise_date" value="<?=date('Y-m-d',$_POST['promise_date'])?>" size="10" maxlength="20"></td>
  <td>电话:</td>
  <td><input type="text" readonly="readonly" name="contacts_phone" value="<?=$_POST['contacts_phone']?>" size="20" maxlength="25"/> </td>
  <td>问题描述：</td>
  <td  ><input type="text" readonly="readonly" name="problem_desc" value="<?=$_POST['problem_desc']?>" size="45" maxlength="50"/></td>
  <td>实际交货日期：</td>
  <td><input type="text" required="required" name="delivery_date" value="<?=$_POST['delivery_date']?>" onfocus="WdatePicker()" size="10" maxlength="20"></td>
</tr>
</table>

<input type="hidden" name="PageOffset" value="1"/><br/>
<?php
if (isset($_POST['customer_name']) and $_POST['customer_name'] != '') {

	$sql ="select *
				from sh_order_lines_all d
				where  d.sh_order_num= '" .$_POST['sh_order_num'] . "'
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
  <th width="130">问题描述</th>
  <th width="100">责任人</th>
  <th bgcolor="#87CEFA" width="100">处理人</th>
  <th width="100">预计完成日期</th>
  <th bgcolor="#87CEFA">处理方法</th>	 
  <th bgcolor="#87CEFA">完成日期</th>
  <th bgcolor="#87CEFA">金额</th>	
  <th bgcolor="#87CEFA">是否解决</th>		  
  <th width="15" align="center">选择</th>
</tr>
<?php   
$i=1;
 
 while ($myrow = DB_fetch_array($result)  )  {
	if (!isset($_POST['finish_date'.$i])) {
    $_POST['finish_date'.$i] = Date('Y-m-d');
    } 
    $_POST['chuli_person'.$i]=$myrow['chuli_person'] ;
	 ?>
		 
<tr id="purchase_table_<?=$i?>" >
  <td><input type="text" readonly="readonly" name="line_num<?=$i?>" id="text_slect_receipt_line<?=$i?>" value="<?= $myrow['line_num'] ?>" size="1" maxlength="10"/></td>
  <td><input type="text" readonly="readonly" name="problem_desc<?=$i?>" id="problem_desc<?=$i?>" value="<?= $myrow['problem_desc'] ?>" size="30" maxlength="150"/></td>
  <td><input type="text" readonly="readonly" name="zeren_person<?=$i?>" id="text_slect_buliao<?=$i?>" value="<?=$myrow['zeren_person']?>" size="9" maxlength="25"  />
  <td><input type="text" required="required" name="chuli_person<?=$i?>" id="text_slect_chuli_person<?=$i?>" value="<?=$_POST['chuli_person'.$i]?>" size="9" maxlength="25"  />
  <td><input type="text" readonly="readonly" name="yuji_date<?=$i?>" value="<?= date('Y-m-d',$myrow['yuji_date'])?>" size="9" maxlength="25"/></td>
   
  <td><input type="text" required="required" name="chuli_method<?=$i?>" id="text_slect_buliao<?=$i?>" value="<?=$_POST['chuli_method'.$i]?>" size="20" maxlength="25"  /> 
  <td><input type="text" required="required" name="finish_date<?=$i?>" value="<?= $_POST['finish_date'.$i]?>" onfocus="WdatePicker()" size="9" maxlength="25"/></td>
	<td><input type="text" required="required" name="amount<?=$i?>" id="text_slect_buliao<?=$i?>" value="<?=$_POST['amount'.$i]?>" size="5" maxlength="50"  /> 
  <td>
    <!-- <input type="text" required="required" class="chuli_ok" name="chuli_ok<?=$i?>" id="text_this_price<?=$i?>" value="<?=$_POST['chuli_ok'.$i]?>" size="5" maxlength="10"/> -->
    <select required="required" name="chuli_ok<?=$i?>">												 
        <option selected="selected" value="是">是</option>;
        <option value="否">否</option>';
    </select>
  </td> 
  <td><input type="checkbox" name="status<?=$i?>" checked /></td>
 <td> 

  <input type="hidden"  name="sh_order_num<?=$i?>"  value="<?= $myrow['sh_order_num'] ?>" size="8" maxlength="10"/>
 
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
<input type="submit" name="Save" value="保存"> 
<input type="submit" name="Agree" value="结案"> 
<input type="submit" name="Reject" value="取消"> &nbsp;&nbsp;
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
function  check2(s1){
	    var a=document.getElementById("text_this_price"+s1).value;
        var b=document.getElementById("text_this_baojia_rate"+s1).value;
		if (parseFloat(b) >= 0)
		{
		   document.getElementById("text_this_baojia"+s1).value=Math.round(Number(a*b)*1)/1;
		}  else if (parseFloat(b) < 0) {
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
    //全选/取消全选
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

