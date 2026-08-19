 <?php
include('includes/session.inc');
$Title     = _('已对账明细报表');
$ViewTopic = '已对账明细报表';
$BookMark  = '已对账明细报表';

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
$_SESSION['DisplayRecordsMax']=20;


 
if(isset($_POST['Search']) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous']))
{
  $sql ="SELECT  a.delivery_id,
                 d.delivery_date,
				 a.delivery_num,
                 a.deliveryline,
                 a.so_order_number,
                 a.so_line_no,
                 d.customer_code,
                 c.customer_name,
	             b.stockid,
	             b.item_name,
	             b.item_desc,
			     a.uom ,
				 a.shiped_quantity,
			     a.price,
				 a.check_quantity,
                 a.check_price,
				 a.check_amount,
				 a.check_date,
				 d.delivery_date				 
		  from so_delivery_all a, 
               customers c,
			   so_delivery_headers_all d,
			   so_lines_all b
	     where a.stockid =b.stockid
	       and a.so_order_number = b.order_number
	       and a.so_line_no = b.line
		   and d.customer_code = c.customer_code
		   and a.delivery_num  = d.delivery_num  
		   and a.check_flag='Y'
		   and d.is_debit = 'Y' ";
    
	    //   
  if (isset($_POST['order_number']) and $_POST['order_number'] != '') 
  { 
	$sql = $sql . " and a.delivery_num " . LIKE . " '%" . $_POST['order_number'] . "%' ";
  }
  
  if (isset($_POST['customer_name']) and $_POST['customer_name'] != '') 
  { 
	$sql = $sql . " and c.customer_name " . LIKE . " '%" . $_POST['customer_name'] . "%' ";
  }
 if (isset($_POST['customer_code']) and $_POST['customer_code'] != '') 
  { 
	$sql = $sql . " and c.customer_code " . LIKE . " '%" . $_POST['customer_code'] . "%' ";
  }
  if (isset($_POST['stockid']) and $_POST['stockid'] != '') 
  { 
	$sql = $sql . " and b.stockid " . LIKE . " '%" . $_POST['stockid'] . "%' ";
  }


  if (empty($_POST['FromDate2']) == 0) 
  {
    $SQL_FromDate2 = strtotime($_POST['FromDate2']);
    $sql .= " and d.delivery_date >= '" . $SQL_FromDate2 . "' ";
  }
   
  if (empty($_POST['ToDate2']) == 0) 
  {
     $SQL_ToDate2 = strtotime($_POST['ToDate2']) + 86400;
     $sql .= " and d.delivery_date <='" . $SQL_ToDate2 . "' ";
  }
   $sql .= " order by d.delivery_date,a.delivery_num ";
 //echo $sql;
  $result = DB_query($sql,$db);
  if (DB_num_rows($result)==0) 
  {
    unset($result);
    prnMsg(_('没有未对账单据，请重新输入条件查询！') ,'error');
  }
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>已对账明细报表</title>
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
<style type="text/css">
    #div0 {width:200px;}
</style>
<style type="text/css">
    #div1 {width:1200px;}
</style>
<style type="text/css">
    #div2 {width:500px;}
</style>
<style type="text/css">
    #div3 {width:550px;}
</style>
<style type="text/css">
    #div4 {width:450px;}
</style>
<style type="text/css">
    #div5 {width:900px;}
</style>
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



function webdesign<?=$i?>()
{
var a=document.getElementById("webdesign1<?=$i?>").value;
var b=document.getElementById("webdesign2<?=$i?>").value;
 
if(a==null||a.trim=="")
a=0;
if(b==null||b.trim=="")
b=0;
 
var c=(a*1)*(b*1)*(-1);
document.getElementById("webdesign3<?=$i?>").value=c;
}
 

</script>
</head>

<?php
echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('已对账明细报表处理') . '</p>';
echo '<table cellpadding="3" class="selection">';
echo '</tr>'; 

echo '<tr><td >' . _('出货单号') . ':</td><td>';
echo '<input type="text" name="order_number"    value="' . $_POST['order_number'] . '"  size="15" maxlength="25" />';

echo ' <td >' . _('客户代号') . ':</td><td>';
echo '<input type="text" name="customer_code"   value="' . $_POST['customer_code'] . '" size="15" maxlength="25" />';
echo '</td>';
echo ' <td >' . _('客户名称') . ':</td><td>';
echo '<input type="text" name="customer_name"   value="' . $_POST['customer_name'] . '" size="15" maxlength="25" />';
echo '</td>';
echo '</tr>';

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


echo '<tr>'; 
echo ' <td >' . _('料号') . ':</td><td>';
echo '<input type="text" name="stockid"   value="' . $_POST['stockid'] . '" size="15" maxlength="25" />';
echo '</td>';
echo ' <td >' . _('出货日期起') . ':</td><td>';
echo '<input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="FromDate2" maxlength="10" size="15" value="" />';
echo '</td>';

echo ' <td >' . _('出货日期止') . ':</td><td>';
echo '<input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="ToDate2" maxlength="10" size="15" value="" />';
echo '</td>';
echo '</tr>';

echo '</table><div class="centre"><input type="submit" name="Search" value="查询明细"></div>';

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
    echo '<br /><div class="centre">&nbsp;&nbsp;' . $_POST['PageOffset'] . ' ' . _('of') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ': ';
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
  <table cellpadding="2" class="selection">';
  echo '<tr><th class="ascending" >' . _('客户') . '</th>
        <th class="ascending" >' . _('出货单') . '</th>
        <th class="ascending" >' . _('订单号') . '</th>
        <th   >' . _('订单行') . '</th>
	    <th   >' . _('料号') . '</th>
        <th   >' . _('料号名称') . '</th>
	    <th   >' . _('规格型号') . '</th>
		<th   >' . _('出货日期') . '</th>
        <th   >' . _('出货数量') . '</th>
		<th  >' . _('出货单价') . '</th>
		<th  >' . _('对账数量') . '</th>
		<th  >' . _('对账单价') . '</th>
		<th  >' . _('对账金额') . '</th>
		<th  >' . _('对账日期') . '</th>
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
	
	 
      $_SESSION['status_id' . $identifier]=100;  
	 
      echo '<td>' . $myrow['customer_code'] . '</td> 
	      <td>' . $myrow['delivery_num'] . '</td> 
            <td>' . $myrow['so_order_number'] . '</td>
			<td>' . $myrow['so_line_no'] . '</td>
			<td>' . $myrow['stockid'] . '</td>          
			<td>' . $myrow['item_name']  . '</td>
			<td>' . $myrow['item_desc']  . '</td>
			 <td>' . date("Y-m-d",$myrow['delivery_date']) . '</td>     
			<td>' . $myrow['shiped_quantity']  . '</td>
			<td>' . $myrow['price']  . '</td>
			<td>' . $myrow['check_quantity']  . '</td>
			<td>' . $myrow['check_price']  . '</td>
			<td>' . $myrow['check_amount']  . '</td>
			 <td>' . date("Y-m-d",$myrow['check_date']) . '</td>   
			 
            
            ';?>

       <?php 
	  // echo ' <td><input id="check_quantity' .$i.'" onblur="webdesign(' .$i.')" style="background-color:yellow" type="text"  name="check_quantity'.$i.'" class="number" size="8"  value="' . $myrow['delivery_quantity']  . '" />
		 //   </td> ';


   //    echo ' <td><input id="check_price' .$i.'" onblur="webdesign(' .$i.')" style="background-color:yellow" type="text" name="check_price'.$i.'" class="number" size="8" value="' . $myrow['price']  . '" /></td> ';

	  // echo ' <td><input type="text" id="check_amount' .$i.'" readonly="readonly" name="check_amount'.$i.'" class="number" size="8"  value="' . $myrow['check_amount']  . '" /></td> ';



   //    echo ' <td><input type="text" name="check_date'.$i.'"   onfocus="WdatePicker()" size="10" value="' .$myrow['check_date']. '" /></td> ';

      echo  '</tr>';
      $i++;
      $RowIndex++;

    } //end loop through customers
	echo '</table>';
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

  echo '<a name="end"></a><br />';
}


?>


<script type="text/javascript">

 function check(){
 	 var a = $("#checkbox").attr("checked");
 	 if( a == 'checked'){
        $(".checkbox").attr("checked","checked");
 	 } else {
        $(".checkbox").removeAttr("checked");
 	 }
 }


function webdesign(s1)
{
var a=document.getElementById("check_quantity"+s1).value;
var b=document.getElementById("check_price"+s1).value;
document.getElementById("check_amount"+s1).value=a*b;
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
            title:'选择产品',
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

