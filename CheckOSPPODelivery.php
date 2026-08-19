<?php
include('includes/session.inc');
$Title     = _('外协采购对账');
$ViewTopic = '外协采购对账';
$BookMark  = '外协采购对账';

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

unset($result);

if (isset($_POST['Go1']) or isset($_POST['Go2'])) {
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
    if (substr($key, 0,15)=='receipt_line_id') 
    {
     
      $i = substr($key, 15);
//      if ($_POST['unit_price'.$i]== 0) 
//	  {
//          $errorflag = 1;
//          prnMsg($value.'未填写价格，请填写价格！',error);
//       }
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
    foreach ($_POST as $key => $value)
	{
      if (mb_substr($key,0,15)=='receipt_line_id') 
	  {
        $receipt_line_id =mb_substr($key,15);
		$i = $_POST[$key];   
        $time = strtotime(Date('Y-m-d H:i:s'));
        if ($_POST['check_price'.$i]>0) 
	    {
		  //$lineamount  =$_POST['check_quantity'.$i] * $_POST['check_price'.$i] ;
          $checkamount =$_POST['check_quantity'.$i] * $_POST['check_price'.$i] ;
		 
          $sql2="UPDATE po_rcv_receipt_line 
                    SET check_price =   '" . $_POST['check_price'.$i]. "' 
					   ,check_quantity ='" . $_POST['check_quantity'.$i]. "'
					   ,check_remark ='" . $_POST['check_remark'.$i]. "'
                       ,check_amount ='"  . $checkamount. "'
					   ,check_date  ='"   . $time. "'
                       ,last_update_date = '" . $time. "'
					   ,check_flag = 'Y'
					   ,status =  'checked'
					   ,last_updated_by = '".$_SESSION['UserID']."'
                 WHERE  receipt_line_id  = '".$receipt_line_id."'   "; 
		    $result = DB_query($sql2,$db);

			  if  ($_POST['receipt_type'.$i]=='入库') {            
		$sql3="UPDATE po_headers_all 
                    SET check_amount = check_amount +  '" . $checkamount. "' 					 
                       ,last_update_date = '" . $time. "'
					   ,last_updated_by = '".$_SESSION['UserID']."'
                 WHERE  po_num  = '".$_POST['po_num'.$i]."'   "; 
		
		$result = DB_query($sql3,$db);
		} else {            
		 $sql3="UPDATE po_headers_all 
                    SET check_amount = check_amount -  '" . $checkamount. "' 					 
                       ,last_update_date = '" . $time. "'
					   ,last_updated_by = '".$_SESSION['UserID']."'
                 WHERE  po_num  = '".$_POST['po_num'.$i]."'   "; 
		
		$result = DB_query($sql3,$db);
		} 
    
    }  
		//echo $sql2;        
	    

    
  }
  
}
$_SESSION['lastsearchtime'] = $time;
DB_Txn_Commit($db);
prnMsg('采购入库单对账完成！',success);
echo "<script>location.href='index.php';</script>";
  // header("Location: SussCreate.php?OrderNum=" . $TransNum . "&type=CheckOSPPODelivery");
}
}
// $sql ="SELECT  a.receipt_line_id,
// a.receipt_num,
// g.units,
// a.receipt_line,
// a.unit_price,
// a.transaction_quantity, 
// a.check_quantity,
// a.check_price,
// a.check_amount,
// a.remark,
// a.creation_date,
// a.stockid,
// b.delivery_date,
// b.vendor_code,
// d.vendor_name,
// a.stockid, a.po_num,a.po_line,b.receipt_type,g.item_name
// from po_rcv_receipt_line a, 
// po_rcv_receipt_header b, 
// vendors d,po_lines_all e,po_headers_all f,sf_item_no g
// where a.receipt_num = b.receipt_num  and e.stockid=g.item_no
// and b.vendor_code = d.vendor_code
// and a.check_flag ='N' 	
// and a.transaction_quantity>0
// and a.wait_inspect_quantity=0
// and f.order_type='外协采购'
// and f.po_num=e.po_num

// and a.po_line=e.line and a.po_num=e.po_num and e.wip_entity_name=e.wip_entity_name
// ";

 
// $sql .=" order by b.delivery_date "; 
// $result = DB_query($sql,$db);
 //取消的foecast不再显示
if(isset($_POST['Search']) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous']))
{
  $sql ="SELECT  a.receipt_line_id,
a.receipt_num,

g.units,
a.receipt_line,

a.unit_price,
a.transaction_quantity, 

a.check_quantity,
a.check_price,
a.check_amount,
a.remark,
a.creation_date,
a.stockid,
b.delivery_date,
b.vendor_code,
d.vendor_name,
a.stockid, a.po_num,a.po_line,b.receipt_type,g.item_name,e.operation_code
from po_rcv_receipt_line a, 
po_rcv_receipt_header b, 
vendors d,po_lines_all e,po_headers_all f,sf_item_no g
where a.receipt_num = b.receipt_num  and e.stockid=g.item_no
and b.vendor_code = d.vendor_code
and a.check_flag ='N' 	
and a.transaction_quantity>0
and a.wait_inspect_quantity=0
and f.order_type='外协采购'
and f.po_num=e.po_num
and a.po_line=e.line and a.po_num=e.po_num and e.wip_entity_name=e.wip_entity_name
		  ";
  // echo $sql;	 	
  if (isset($_POST['operation_code']) and $_POST['operation_code'] != '') 
  { 
	$sql = $sql . " and e.operation_code " . LIKE . " '%" . $_POST['operation_code'] . "%' ";
  } 
  if (isset($_POST['order_number']) and $_POST['order_number'] != '') 
  { 
	$sql = $sql . " and a.receipt_num " . LIKE . " '%" . $_POST['order_number'] . "%' ";
  }
 if (isset($_POST['stockid']) and $_POST['stockid'] != '') 
  { 
	$sql = $sql . " and a.stockid " . LIKE . " '%" . $_POST['stockid'] . "%' ";
  }
  if (isset($_POST['item_name']) and $_POST['item_name'] != '') 
  { 
	$sql = $sql . " and g.item_name " . LIKE . " '%" . $_POST['item_name'] . "%' ";
  } 
 
  if (isset($_POST['vendor_name']) and $_POST['vendor_name'] != '') 
  { 
	$sql = $sql . " and d.vendor_name " . LIKE . " '%" . $_POST['vendor_name'] . "%' ";
  }
  if (isset($_POST['vendor_code']) and $_POST['vendor_code'] != '') 
  { 
	$sql = $sql . " and d.vendor_code " . LIKE . " '%" . $_POST['vendor_code'] . "%' ";
  }


  if (empty($_POST['FromDate']) == 0) 
  {
    $SQL_FromDate = strtotime($_POST['FromDate']);
    $sql .= " and a.creation_date >= '" . $SQL_FromDate . "' ";
  }
   
  if (empty($_POST['ToDate']) == 0) 
  {
     $SQL_ToDate = strtotime($_POST['ToDate']) + 86400;
     $sql .= " and a.creation_date <='" . $SQL_ToDate . "' ";
  }

  if (empty($_POST['FromDate2']) == 0) 
  {
    $SQL_FromDate2 = strtotime($_POST['FromDate2']);
    $sql .= " and b.delivery_date >= '" . $SQL_FromDate2 . "' ";
  }
   
  if (empty($_POST['ToDate2']) == 0) 
  {
     $SQL_ToDate2 = strtotime($_POST['ToDate2']) + 86400;
     $sql .= " and b.delivery_date <='" . $SQL_ToDate2 . "' ";
  }
  
  $sql .=" order by b.delivery_date "; 
  $result = DB_query($sql,$db);
  if (DB_num_rows($result)==0) 
  {
    //unset($result);
    prnMsg(_('没有未对账采购入库单据，请重新输入条件查询！') ,'error');
  }
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>外协采购对账</title>
<link rel="shortcut icon" href="/favicon.ico"/>
<link rel="icon" href="/favicon.ico"/>
<meta http-equiv="Content-Type" content="application/html; charset=utf-8"/>
<link href="/css/xenos/default.css" rel="stylesheet" type="text/css"/>
<script type="text/javascript" src ="/javascripts/miscfunctions.js"></script>
<script type="text/javascript" src ="/javascripts/wdatepicker.js"></script>
<script type="text/javascript">var basepath='/statics/base/images';</script>
<script type="text/javascript" src="/statics/base/js/metvar.js"></script>
<script type="text/javascript" src="/statics/base/js/jQuery1.7.2.js"></script>
<script type="text/javascript" src="/statics/base/js/uploadify/jquery.uploadify.v2.1.4.min.js"></script>
<script type="text/javascript" src="/statics/base/js/iframes.js"></script>
<script type="text/javascript" src="/statics/base/js/cookie.js"></script>
<script type="text/javascript" src="/statics/base/js/jquery.livequery.js"></script>
<script src="/javascript/jquery-1.7.2.min.js"></script>
<script src="/javascript/lhgdialog.min.js?self=true&skin=chrome"></script>
<script src="/javascript/bootstrap.min.js"></script>
 
<script type="text/javascript">
/*ajax执行*/
var lang = 'cn';
var metimgurl='/statics/base/images/';
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
 
    var v = $('#ilot_numberount').val();
    $("#purchase_table_"+v).css("display","");
    var c = parseInt(v) + 1;
    $('#ilot_numberount').val(c);     
}

function webdesign(s1)
{
var a=document.getElementById("check_quantity"+s1).value;
var b=document.getElementById("check_price"+s1).value;
 
 
if(a==null||a.trim=="")
a=0;
if(b==null||b.trim=="")
b=0;
 
var c=(a*1)*(b*1);
document.getElementById("check_amount"+s1).value=c;
}



</script>
</head>

<?php
echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('外协采购对账') . '</p>';
echo '<table cellpadding="3" class="selection">'; 

echo '<div class="text-nav">';


echo '<div class="text-nav-1"><div>' . _('外协采购入库单号') . ':</div>';
echo '<input type="text"   autocomplete="off"   name="order_number"   value="' . $_POST['order_number'] . '" size="20" maxlength="25" /> ';
echo '</div>';

echo '<div class="text-nav-1"><div>' . _('供应商代号') . ':</div>';
echo '<input type="text"   autocomplete="off"   name="vendor_code"   value="' . $_POST['vendor_code'] . '" size="20" maxlength="25" /> ';
echo '</div>';

echo '<div class="text-nav-1"><div>' . _('供应商名称') . ':</div>';
echo '<input type="text"   autocomplete="off"   name="vendor_name"   value="' . $_POST['vendor_name'] . '" size="20" maxlength="25" />  </div>';
echo '<div class="text-nav-1"><div>' . _('零件图号') . ':</div>';
echo '<input type="text"   autocomplete="off"   name="stockid"   value="' . $_POST['stockid'] . '" size="20" maxlength="25" />  </div>';
 echo '<div class="text-nav-1"><div>' . _('零件名称') . ':</div>';
echo '<input type="text"   autocomplete="off"   name="item_name"   value="' . $_POST['item_name'] . '" size="20" maxlength="25" />  </div>'; 
 echo '<div class="text-nav-1"><div>' . _('工艺') . ':</div>';
echo '<input type="text"   autocomplete="off"   name="operation_code"   value="' . $_POST['operation_code'] . '" size="20" maxlength="25" />  </div>'; 

if (!isset($_POST['FromDate'])) 
{
  $_POST['FromDate'] = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 30, date("Y")));
}
if (!isset($_POST['ToDate'])) 
{
  $_POST['ToDate'] = Date('Y-m-d');
}

if (!isset($_POST['FromDate2'])) 
{
  $_POST['FromDate2'] = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 30, date("Y")));
}
if (!isset($_POST['ToDate2'])) 
{
  $_POST['ToDate2'] = Date('Y-m-d');
}


echo '<div class="text-nav-1"><div>' . _('建立日期起') . ':</div>';
echo '<input type="text"   autocomplete="off"  onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="FromDate"   value="' . $_POST['FromDate'] . '" size="10" maxlength="20" /> ';
echo '</div>';

echo '<div class="text-nav-1"><div>' . _('建立日期止') . ':</div>';
echo '<input type="text"   autocomplete="off"  onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="ToDate"   value="' . $_POST['ToDate'] . '" size="10" maxlength="20" /> ';
echo '</div>';

echo '<div class="text-nav-1"><div>' . _('入库日期起') . ':</div>';
echo '<input type="text"   autocomplete="off"  onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="FromDate2"   value="' . $_POST['FromDate2'] . '" size="10" maxlength="20" /> ';
echo '</div>';

echo '<div class="text-nav-1"><div>' . _('入库日期止') . ':</div>';
echo '<input type="text"   autocomplete="off"  onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="ToDate2"   value="' . $_POST['ToDate2'] . '" size="10" maxlength="20" /> ';
echo '</div>';



echo '</table><div class="centre"><input type="submit" name="Search" value="查询未对账明细"></div>';

if (isset($_POST['Search']) or isset($result) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous']))
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
    echo '<br /><div class="centre">&nbsp;&nbsp;' . _('第') . '' . $_POST['PageOffset'] . ' ' . _('页，共') . ' ' . $ListPageMax . ' ' . _('页') . '. ' . _('转到页') . ': ';
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
  <input type="submit" name="Go1" value="' . _('转到') . '" />
  <input type="submit" name="Previous" value="' . _('上一页') . '" />
  <input type="submit" name="Next" value="' . _('下一页') . '" />';
	echo '</div>';
  }
  
  echo '<br /><div class="text-nav-table">
  <table cellpadding="2" class="selection">';
  echo '<tr>
  <th width =40 >' . '选择' . '</th>
        <th class="ascending" width = 40>' . _('入库日期') . '</th>
        <th  width = 50>' . _('类型') . '</th>
        <th class="ascending" width = 60>' . _('采购入库单') . '</th>	
        <th  >' . _('项') . '</th>				
	    <th class="ascending" width = 80>' . _('供应商') . '</th>	
	    <th class="ascending" width = 80>' . _('外协采购单号') . '</th>
	    <th    >' . _('行') . '</th>
        <th  >' . _('零件图号') . '</th> 
        <th  >' . _('零件名称') . '</th> 
        <th  >' . _('工艺') . '</th> 
	    <th  width = 20>'  . _('单位') . '</th> 

	    <th  width = 60>'  . _('入库数量') . '</th>
        <th   width = 60>'  . _('采购单价') . '</th>
		<th bgcolor="#87CEFA"   width = 30>'  . _('对账数量') . '</th>
	    <th  bgcolor="#87CEFA"  width = 30>'  . _('对账单价') . '</th>
		<th  width = 30>'  . _('对账金额') . '</th>
        <th  width = 30>'  . _('备注') . '</th>
       
       </tr>';  
  $k = 0; //row counter to determine background colour
  $RowIndex = 0;
 
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

	  if (!isset($myrow['check_date'])) 
	  {
        $myrow['check_date'] = Date('Y-m-d');
      }
			//var_dump($myrow);
      $_SESSION['status_id' . $identifier]=100;  
	  $check_amount=$myrow['transaction_quantity']*$myrow['unit_price'];
      echo '
      <td><input type="checkbox" style="height: 24px;width: 24px;" name="receipt_line_id'.$myrow['receipt_line_id'].'" value="'.$i.'" /></td>
      <td>' .  date('Y-m-d',$myrow['delivery_date']) . '</td>
			<td>' . $myrow['receipt_type'] . '</td>  
	        <td>' . $myrow['receipt_num'] . '</td>
			<td>' . $myrow['receipt_line'] . '</td>
			<td>' . $myrow['vendor_code'] . '</td>
            <td>' . $myrow['po_num']  . '</td>
            <td>' . $myrow['po_line']  . '</td>
            <td>' . $myrow['stockid']  . '</td> 
            <td>' . $myrow['item_name']  . '</td> 
            <td>' . $myrow['operation_code']  . '</td> 
			<td>' . $myrow['units']  . '</td>         
            <td>' . $myrow['transaction_quantity']  . '</td>
            <td>' . $myrow['unit_price']  . '</td>
			 
		';?>
       <?php 
	   echo ' <td><input id="check_quantity' .$i.'" onblur="webdesign(' .$i.')" style="background-color:yellow" type="text"   autocomplete="off"    name="check_quantity'.$i.'" class="number" size="8"  value="' . $myrow['transaction_quantity']  . '" /></td> ';
      echo ' <td><input id="check_price' .$i.'" onblur="webdesign(' .$i.')" style="background-color:yellow" type="text"   autocomplete="off"   name="check_price'.$i.'" class="number" size="8" value="' . $myrow['unit_price']  . '" /></td> ';
	  echo ' <td><input type="text"   autocomplete="off"   id="check_amount' .$i.'" readonly="readonly" name="check_amount'.$i.'" class="number" size="8"  value="' . $check_amount  . '" /></td> ';
      echo ' <td><input type="text"   autocomplete="off"   name="check_remark'.$i.'"   size="10" value="' .$myrow['check_remark']. '" /></td> ';	
	  echo ' <td>
	  <input type="hidden" name="po_num'.$i.'"   size="10" value="' .$myrow['po_num']. '" />
	  <input type="hidden" name="receipt_type'.$i.'"   size="10" value="' .$myrow['receipt_type']. '" />
	  <input type="hidden" name="po_line'.$i.'"   size="10" value="' .$myrow['po_line']. '" /></td>   ';
      echo  '</tr>';
      $i++;
      $RowIndex++;

    } 
	
	echo '<tr><td colspan="15"><p><input type="checkbox" style="height: 24px;width: 24px;" name="selectall" onclick="checkall(this.form);"/>全选/取消全选</p></td></tr>
	<td ><input type="hidden" name="flag" value="<?=$i-1?>" size="15" maxlength="45"/></td> ';
	//end loop through customers
	echo '</table></div>';
	echo '<input type="hidden" name="JustSelectedACustomer" value="Yes" />';
  }

  if (isset($ListPageMax) AND $ListPageMax > 1) 
  {
    echo '<br /><div class="centre">&nbsp;&nbsp;' . _('第') . '' . $_POST['PageOffset'] . ' ' . _('页，共') . ' ' . $ListPageMax . ' ' . _('页') . '. ' . _('转到页') . ': ';
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
  <input type="submit" name="Go2" value="' . _('转到') . '" />
  <input type="submit" name="Previous" value="' . _('上一页') . '" />
  <input type="submit" name="Next" value="' . _('下一页') . '" />';
	echo '</div>';
  }//end if results to show

  echo '<a name="end"></a><br /><div class="centre"><input type="submit" name="UpdateStatus"   value="对账确认" />
  </div> ';
}
?>

<script type="text/javascript">


function webdesign(s1)
{
var a=document.getElementById("check_quantity"+s1).value;
var b=document.getElementById("check_price"+s1).value;
document.getElementById("check_amount"+s1).value=a*b;
}

 </script>

<script type="text/javascript">
function checkall(thisform){for(var i=0;i<thisform.elements.length;i++){if(thisform.elements[i].type=="checkbox"&&thisform.elements[i].checked==false&&thisform.elements[i].name!="selectall"){thisform.elements[i].checked=true;}else if(thisform.elements[i].type=="checkbox"&&thisform.elements[i].checked==true&&thisform.elements[i].name!="selectall"){thisform.elements[i].checked=false;}} }

    $(document).ready(function(){

        $('.divToilet table tr td a').click(function(){
            $(this).parent('td').toggleClass('highlight');
            if(!($(this).parent('td').hasClass('highlight'))) {
                $(this).next().val('0');
            }else {
                $(this).next().val('1');
            }
        });
      

     
       $('#btn_slect_vendor').dialog({
            title:'选择供应商',
            width: '950px',
            height: 470,
            content:'url:BtnSearchVendor.php?fwValue=&cat=buliao',
            init:function(){
                this.content.document.getElementById('cat').value = 'buliao';
                this.content.document.getElementById('fwValue').value = '';
            }
        });

        $('#btn_slect_item_no').dialog({
            title:'选择料号',
            width: '850px',
            height: 470,
            content:'url:BtnSearchitem_no.php?fwValue=&cat=buliao',
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

  <?php
  echo '</div>
      </form>';
include('includes/footer.inc');
?>

