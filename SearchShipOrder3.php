<?php
include('includes/session.inc');
$Title     = _('出货单建立');
$ViewTopic = '出货单建立';
$BookMark  = '出货单建立';

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
$_SESSION['DisplayRecordsMax']=20;
if (isset($_POST['Go1']) OR isset($_POST['Go2'])) {
	$_POST['PageOffset'] = (isset($_POST['Go1']) ? $_POST['PageOffset1'] : $_POST['PageOffset2']);
	$_POST['Go'] = '';
}
if (!isset($_POST['PageOffset'])) {
	$_POST['PageOffset'] = 1;
} else {
	if ($_POST['PageOffset'] == 0) {
		$_POST['PageOffset'] = 1;
	}
}

if (isset($_POST['UpdateStatus']) ) 
{ 
  $errorflag = 0;
  foreach ($_POST as $key => $value) 
  {
    if (substr($key, 0,13)=='order_line_id') 
    {
     
     $emp_id =mb_substr($key,13);
		    $i = $_POST[$key];  
     
        if ( $_POST['quantity'.$i] <=0)  {        
        $errorflag = 1;
        prnMsg($_POST['quantity'.$i].'数量有误！',error);
      }  
    }
  }

  if ($errorflag == 0) 
  {
 
	  $date = date('Ymd');
        $sql_num = "select 	(
		CASE WHEN substr(max(delivery_num) ,-2,1) = 0 THEN
			RIGHT (
				'100' + (
					max(substr(delivery_num ,- 1)) + 1
				),
				2
			)
		ELSE
			substr(max(delivery_num),-2,2) + 1
		END
        ) order_number from so_delivery_headers_all where substr(delivery_num,-10,8) = '" . $date . "'";
        $result_num = DB_query($sql_num, $db); 
        while ($v = DB_fetch_array($result_num)) {
            if ($v['order_number'] == null) {
                $OrderNum = 'DE'.$date . '01';
            } else {
                $OrderNum =  'DE'. $date . $v['order_number'];
            }
        }

    foreach ($_POST as $key => $value)
	{
      if (mb_substr($key,0,13)=='order_line_id') 
	  {
        $emp_id =mb_substr($key,13);
		    $i = $_POST[$key];   
        $time = strtotime(Date('Y-m-d H:i:s')); 
          $line=$line+1;  
		     $lineamount[$i]= $_POST['quantity'.$i] * $_POST['price'.$i];
						$sql = "insert into so_delivery_all (delivery_num,delivery_line,so_order_number,so_line_no,uom,price,
						delivery_quantity,shiped_quantity,stockid,remark,line_amount,customer_code,subinventory_code,
						creation_date,created_by,last_update_date,last_updated_by)
						values('".$OrderNum."','".$line."','".$_POST['order_number'.$i]."','".$_POST['line'.$i]."','".$_POST['uom'.$i]."','".$_POST['price'.$i]."',
						'".$_POST['quantity'.$i]."','0','".$_POST['stockid'.$i]."','".$_POST['remark'.$i]."','".$lineamount[$i]."','".$_POST['customercode']."','".$_POST['subinventory_code'.$i]."',
						'".$time."','".$_SESSION['UserID']."','".$time."','".$_SESSION['UserID']."') ";
						
						$result = DB_query($sql,$db);
						
						$delivery_amount = $delivery_amount + $lineamount[$i];
/*
						$sql = "update so_lines_all
						set quantity_shiped=quantity_shiped+'".$_POST['quantity'.$i]."'
						where  order_number ='".$_POST['order_number'.$i]."'
						and line='".$_POST['line'.$i]."'"; 
						$result = DB_query($sql,$db);
*/

  
          

		//echo $sql2;         
            
    }

 

  }
 if ($line>0) {
	$sql = "insert into so_delivery_headers_all
(delivery_type,delivery_num,customer_code,invoicenum,ship_address,tracking_number,trackingcompany,delivery_date,currency_code,delivery_amount,creation_date,narrative 	,created_by,last_update_date,last_updated_by) values ('出货','".$OrderNum."','".$_POST['customercode']."','".$_POST['invoicenum']."','".$_POST['ship_address']."','".$_POST['tracking_number']."','".$_POST['trackingcompany']."','".$time."','".$_POST['currency_code']."','".$delivery_amount."','".$time."','".$_POST['Header_Remark']."','".$_SESSION['UserID']."','".$time."','".$_SESSION['UserID']."')";
			$result = DB_query($sql,$db);
	    DB_Txn_Commit($db);
	    prnMsg('出货完成！',success);
		header("Location: SussCreate2.php?OrderNum=$OrderNum");
	    //echo "<script>location.href='SearchShipOrder.php';</script>";
  }
  }
}



 //取消的foecast不再显示
if(isset($_POST['Search']) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous']))
{
  $sql ="select sl.order_line_id,si.item_no,si.item_desc,si.item_name,si.pic_path,si.units,sh.order_number,sl.line,sl.price,si.units, 
sl.quantity,sl.quantity_cancelled,sl.quantity_shiped,sl.subinventory_code,(select ifnull(sum(quantity),0) from  inv_onhand_quantity_all ioq
  where ioq.stockid=sl.stockid and ioq.subinventory_code=sl.subinventory_code ) onhand_quantity
from so_headers_all sh,so_lines_all sl,sf_item_no si
where  sh.order_number=sl.order_number 
and sl.quantity - sl.quantity_cancelled - sl.quantity_shiped >0
and sl.stockid=si.item_no and 1=1 ";
   
   
	$sql = $sql . " and sh.order_number = '" . $_POST['order_number'] . "' ";
  
  
   
  $sql .=" order by sl.line"; 

 // echo $sql;
  $result = DB_query($sql,$db);
  if (DB_num_rows($result)==0) 
  {
    unset($result);
    prnMsg(_('没有待出货的资料 ，请重新输入条件查询！') ,'error');
  }
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>出货单建立处理</title>
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

<?php
echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div  >';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('出货单建立') . '</p>';
echo '<table cellpadding="3" class="selection">'; 

if (!isset($_POST['FromDate'])) 
{
  $_POST['FromDate'] = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 30, date("Y")));
}
if (!isset($_POST['ToDate'])) 
{
  $_POST['ToDate'] = Date('Y-m-d');
}
 
?>
<tr>
	  <td bgcolor="#87CEFA">客户代码：</td>  
	 <td><input type="text" readonly="readonly" required="required" name="customercode" id="text_slect_customer" value="<?=$_POST['customercode']?>" size="10" maxlength="25" />
       <a class="btn btn-info btn-xs" id="btn_slect_customer<?=$i?>" hfre="###" title="选择客户">选择</a> </td>
      <td bgcolor="#87CEFA">订单号码：</td>
	 <td><input type="text" readonly="readonly" name="order_number" id="text_slect_order_number" value="<?=$_POST['order_number']?>" size="15" maxlength="15"/></td>
     <td bgcolor="#87CEFA">已收款金额：</td>         
	 <td><input type="text" readonly="readonly" name="payment_amount" id="text_slect_payment_amount" value="<?=$_POST['payment_amount']?>" size="15" maxlength="15"/></td>
          
		</tr>
         <tr>
		 <td bgcolor="#87CEFA">客户名称：</td>
		 <td colspan="3"><input readonly="readonly" type="text" name="customername" id="text_slect_name" value="<?=$_POST['customername']?>" size="70" maxlength="50" /></td>
        <td>已出货金额：</td> 
			 <td  ><input type="text" readonly="readonly" maxlength="20" size="15" name="ship_amount" id="text_slect_ship_amount"  value="<?=$_POST['ship_amount']?>" /> </td>
                        
         </tr>
         
         <tr>
		   
         
          
			<td>联系地址：</td>			 
			<td  colspan="3"><input  type="text"   name="ship_address" id="text_slect_address" value="<?=$_POST['ship_address']?>" size="70" maxlength="50"/></td>
            
             <td>联系电话：</td> 
			 <td  ><input type="text"  maxlength="20" size="15" name="contacts_phone" id="text_slect_contacts_phone"  value="<?=$_POST['customer_contacts']?>" /> </td>
		
		</tr>

		<tr>
		<td>联系人：</td> 
			 <td  ><input   type="text"  name="Concact" id="text_slect_contacts" value="<?=$_POST['Concact']?>" size="20" maxlength="20"/></td>
			<td>币别：</td> 
			 <td  ><input   type="text"  readonly="readonly" name="currency_code" id="text_slect_currency_code" value="<?=$_POST['currency_code']?>" size="8" maxlength="20"/></td>
		 	 <td>税别</td>			 
			<td ><input readonly="readonly" type="text"   name="tax_name" id="text_slect_tax_name" value="<?=$_POST['tax_name']?>" size="15" maxlength="20"/></td>
               
		</tr>
        <tr>
			<td>运输方式：</td> 
			 <td  ><input type="text"  maxlength="100" size="20" name="trackingcompany"  value="<?=$_POST['trackingcompany']?>" size="20" maxlength="20"/> </td>
			 <td>货运单号：</td> 
			 <td  ><input type="text"  maxlength="100" size="20" name="tracking_number"  value="<?=$_POST['tracking_number']?>" size="20" maxlength="20"/> </td>
			 <td>发票号码</td> 
			 <td  ><input type="text"  maxlength="100" size="15" name="invoicenum"  value="<?=$_POST['invoicenum']?>"  /> </td>
		</tr>

		  

		<tr>
			<td>出货单备注：</td> 
			 <td colspan="3"><input type="text"  maxlength="200" size="70" name="Header_Remark"  value="<?=$_POST['Header_Remark']?>" size="20" maxlength="20"/> </td>
			 <td>付款条件：</td>
		  <td><input type="text" readonly="readonly" name="term_name" id="text_slect_term_name" value="<?=$_POST['term_name']?>" size="15" maxlength="15"/></td> 
		</tr>

	</table>
 <div class="centre"><input type="submit" name="Search" value="查询"></div>
<?php
if (isset($_POST['Search']) and isset($result) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous']))
{
  $ListCount = DB_num_rows($result);
  $ListPageMax = ceil($ListCount / $_SESSION['DisplayRecordsMax']);
  
  if (isset($_POST['Next'])) 
  {
    if ($_POST['PageOffset'] < $ListPageMax) 
	{
      $_POST['PageOffset'] = $_POST['PageOffset'] + 1;
    }
  }

  if (isset($_POST['Previous'])) 
  {
	if ($_POST['PageOffset'] > 1) 
    {
	  $_POST['PageOffset'] = $_POST['PageOffset'] - 1;
    }
  }
 
  echo '<input type="hidden" name="PageOffset" value="' . $_POST['PageOffset'] . '" />';
  if ($ListPageMax > 1) 
  {
    echo '<br /><div  class="centre">&nbsp;&nbsp;' . $_POST['PageOffset'] . ' ' . _('of') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ': ';
    echo '<select name="PageOffset1">';
    $ListPage = 1;
    while ($ListPage <= $ListPageMax) 
	{
      if ($ListPage == $_POST['PageOffset']) 
	  {
        echo '<option value="' . $ListPage . '" selected="selected">' . $ListPage . '</option>';
      } else 
	  {
        echo '<option value="' . $ListPage . '">' . $ListPage . '</option>';
      }
      $ListPage++;
    }
	echo '</select>
	<input type="submit" name="Go1" value="' . _('Go') . '" />
	<input type="submit" name="Previous" value="' . _('Previous') . '" />
	<input type="submit" name="Next" value="' . _('Next') . '" />';
	echo '</div>';
  }
  echo '<br />
  <div class="centre"> 
    <p id="Prompt" style="color: red;font-size: 20px"></p>
  </div>
  <div style="overflow:scroll"> 
  <table cellpadding="2" class="selection">';
  echo '<tr id="list-top">
               <th width="20">选择</th> 
					<th width="120" bgcolor="#87CEFA">订单号</th>
					<th width="10">行</th> 
					<th width="140">料号</th>
					<th width="100">料号名称</th>
					<th width="100">规格型号</th>
					<th width="10" >单位</th>
					<th width="30">待出货量</th>
					<th width="30">仓库</th> 
					<th width="30">库存量</th> 
					<th width="30" bgcolor="#87CEFA">本次出货量</th> 
					<th width="30">备注</th>   
			 
        
      </tr>';  
  $k = 0; //row counter to determine background colour
  $RowIndex = 0;
  // <th width =40 >' . '删除' . '</th>
  if (DB_num_rows($result) <> 0) 
  {
    DB_data_seek($result, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);
    $i = 0; //counter for input controls
    while (($myrow = DB_fetch_array($result)) AND ($RowIndex <> $_SESSION['DisplayRecordsMax'])) 
	{
      if ($k == 1) 
	  {
        echo '<tr class="EvenTableRows">';
        $k = 0;
      } else 
	  {
        echo '<tr class="OddTableRows">';
        $k = 1;
    }
        	   
      $wait_quantity=$myrow['quantity']-$myrow['quantity_shiped'];
	  if ($myrow['onhand_quantity']<$wait_quantity ) {
	   $quantity=$myrow['onhand_quantity'];
	  } else {
	   $quantity=$wait_quantity;
	  }
	  $quantity=0;
      echo '
          <td><input type="checkbox" name="order_line_id'.$myrow['order_line_id'].'" value="'.$i.'" /></td>
					<td><input type="text" readonly="true" name="order_number'.$i.'" size="12"  value="' . $myrow['order_number']  . '" /></td>
					
					<td><input type="text" readonly="true" name="line'.$i.'"  size="1" value="' . $myrow['line'] . '"  /></td>
					
					<td><input type="text" readonly="true" name="stockid'.$i.'"   size="45" value="' . $myrow['item_no'] . '"   /></td>
					<td><input type="text" readonly="true" name="item_name'.$i.'"   size="22" value="' . $myrow['item_name'] . '" /></td>
					<td><input type="text" readonly="true" name="item_desc'.$i.'"   size="12" value="' . $myrow['item_desc'] . '" /></td>
				 
					<td><input type="text" readonly="true" name="uom'.$i.'" size="3" value="' . $myrow['units'] . '" /></td> 
					<td><input type="text" readonly="true" name="wait_quantity'.$i.'"  id="text_slect_wait_quantity'.$i.'"  size="4" value="' . $wait_quantity . '" onblur="upquantity('.$i.')" /></td>
					<td><input type="text" readonly="true" name="subinventory_code'.$i.'"    size="4" value="' . $myrow['subinventory_code'] . '" /></td> 
					<td><input type="text" readonly="true" name="onhand_quantity'.$i.'" id="text_slect_onhand_quantity'.$i.'"  size="4" value="' . $myrow['onhand_quantity'] . '" /></td> 
					<td><input type="text" class="number" name="quantity'.$i.'" id="quantity'.$i.'" onblur="check('.$i.')" size="7" value="' . $quantity . '" /></td> 
					<td><input type="text" name="remark'.$i.'"  size="10" value="' . $myrow['remark'] . '" />
					<input type="hidden" name="price'.$i.'" id="price'.$i.'"  size="10" value="' . $myrow['price'] . '" /> </td> 
          
          
    ';?>
       <?php 
    // echo '<td><a href="' . $RootPath . '/UpdateEmployeeInfo.php?UpdatevendorCode=' . $myrow['emp_id'] . '">删除</a></td>';

      echo  '</tr>';
      $i++;
      $RowIndex++;

    } //end loop through customers

	echo '<tr><td colspan="11"><p><input type="checkbox" name="selectall" onclick="checkall(this.form);"/>全选/取消全选</p></td></tr>
	<td ><input type="hidden" name="flag" value="<?=$i-1?>" size="15" maxlength="45"/></td> ';

	echo '</table> </div>';
	echo '<input type="hidden" name="JustSelectedACustomer" value="Yes" />';
  }

  if (isset($ListPageMax) AND $ListPageMax > 1) 
  {
    echo '<br /><div class="centre">&nbsp;&nbsp;' . $_POST['PageOffset'] . ' ' . _('of') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ': ';
    echo '<select name="PageOffset2">';
    $ListPage = 1;
    while ($ListPage <= $ListPageMax) 
	{
      if ($ListPage == $_POST['PageOffset']) 
	  {
        echo '<option value="' . $ListPage . '" selected="selected">' . $ListPage . '</option>';
      } //$ListPage == $_POST['PageOffset']
      else 
	  {
        echo '<option value="' . $ListPage . '">' . $ListPage . '</option>';
      }
      $ListPage++;
    } //$ListPage <= $ListPageMax
	echo '</select>
			<input type="submit" name="Go2" value="' . _('Go') . '" />
			<input type="submit" name="Previous" value="' . _('Previous') . '" />
			<input type="submit" name="Next" value="' . _('Next') . '" />';
	echo '</div>';
  }//end if results to show

  echo '<a name="end"></a><br /><div class="centre">
    <input type="submit" name="UpdateStatus"   value="出货单建立确认" />&nbsp;&nbsp;&nbsp; 
	 
  </div> </div>
      </form>';
}
?>

 

	  <script type="text/javascript">
  	 function checkall(thisform){for(var i=0;i<thisform.elements.length;i++){if(thisform.elements[i].type=="checkbox"&&thisform.elements[i].checked==false&&thisform.elements[i].name!="selectall"){thisform.elements[i].checked=true;}else if(thisform.elements[i].type=="checkbox"&&thisform.elements[i].checked==true&&thisform.elements[i].name!="selectall"){thisform.elements[i].checked=false;}} }

function  check(s1){
	    var a=document.getElementById("quantity"+s1).value;
        var b=document.getElementById("text_slect_onhand_quantity"+s1).value;
        var c=document.getElementById("text_slect_wait_quantity"+s1).value;
      if(parseInt(a)>parseInt(b)){
            document.getElementById("Prompt").innerHTML="出货量不可以大于库存量！！！！";
            document.getElementById("quantity"+s1).value="";
            document.getElementById("quantity"+s1).focus();
        } else if(parseInt(a)>parseInt(c)){
            document.getElementById("Prompt").innerHTML="出货量不可以待出货量！！！！";
            document.getElementById("quantity"+s1).value="";
            document.getElementById("quantity"+s1).focus();
        } else {
            document.getElementById("Prompt").innerHTML="";
        }
     }

function  upquantity(s1){
	    
        var a=document.getElementById("text_slect_onhand_quantity"+s1).value;
        var b=document.getElementById("text_slect_wait_quantity"+s1).value;
      if(parseInt(a)>=parseInt(b)){
           
            document.getElementById("quantity"+s1).value=b; 
        } else if(parseInt(b)>parseInt(a)){ 
			  document.getElementById("quantity"+s1).value=a; 
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
        $('#btn_slect_waitship<?=$i?>').dialog({
            title:'选择出货订单',
            width: '950px',
            height: 520,
            content:'url:Searchwaitship2.php?fwValue=<?=$i?>&cat=<?=$_POST['customercode']?>&sub=<?=$_POST['insubinventory']?>',
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
            title:'选择客户订单',
            width: '950px',
            height: 470,
            content:'url:BtnSearchCustomerSo.php?fwValue=&cat=buliao',
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

<?php
include('includes/footer.inc');
?>

