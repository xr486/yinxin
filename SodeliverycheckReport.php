 <?php
include('includes/session.inc');
$Title     = _('出货对账明细报表');
$ViewTopic = '出货对账明细报表';
$BookMark  = '出货对账明细报表';

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

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

 

$sql ="SELECT  a.delivery_id,
d.delivery_date,
a.delivery_num, 
a.delivery_line,
a.so_order_number,
a.so_line_no,
d.customer_code,
c.customer_name,
b.stockid, f.item_name,b.version,b.liaohao,
e.customer_order_number,
a.uom ,
a.shiped_quantity,
a.price,a.line_amount,
a.check_quantity,
a.check_price,
a.check_amount,
a.check_date,d.delivery_type,
d.delivery_date,b.order_line_id,b.wip_entity_name
from so_delivery_all a, 
customers c,
so_delivery_headers_all d,
so_lines_all b,so_headers_all e,sf_item_no f
where a.stockid =b.stockid
and b.order_number =e.order_number and b.stockid=f.item_no
and a.so_order_number = b.order_number
and a.so_line_no = b.line
and d.customer_code = c.customer_code
and a.delivery_num  = d.delivery_num 
and a.check_flag = 'Y'  ";
 $sql .= " order by d.delivery_date desc ";
 
 $result = DB_query($sql,$db);

if(isset($_POST['Search']) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous']))
{
  $sql ="SELECT  a.delivery_id,
d.delivery_date,
a.delivery_num, 
a.delivery_line,
a.so_order_number,
a.so_line_no,
d.customer_code,
c.customer_name,
b.stockid, f.item_name,b.version,b.liaohao,
e.customer_order_number,
a.uom ,
a.shiped_quantity,
a.price,a.line_amount,
a.check_quantity,
a.check_price,
a.check_amount,
a.check_date,d.delivery_type,
d.delivery_date,b.order_line_id,b.wip_entity_name
from so_delivery_all a, 
customers c,
so_delivery_headers_all d,
so_lines_all b,so_headers_all e,sf_item_no f
where a.stockid =b.stockid
and b.order_number =e.order_number and b.stockid=f.item_no
and a.so_order_number = b.order_number
and a.so_line_no = b.line
and d.customer_code = c.customer_code
and a.delivery_num  = d.delivery_num 
and a.check_flag = 'Y' ";

   
 if (isset($_POST['wip_entity_name']) and $_POST['wip_entity_name'] != '') {
      $sql = $sql . " and b.wip_entity_name " . LIKE . " '%" . $_POST['wip_entity_name'] . "%' ";
    }
    if (isset($_POST['delivery_num']) and $_POST['delivery_num'] != '') {
      $sql = $sql . " and a.delivery_num " . LIKE . " '%" . $_POST['delivery_num'] . "%' ";
    }
    if (isset($_POST['order_number']) and $_POST['order_number'] != '') {
      $sql = $sql . " and a.so_order_number " . LIKE . " '%" . $_POST['order_number'] . "%' ";
    }
 
    if (isset($_POST['customer_code']) and $_POST['customer_code'] != '') {
      $sql = $sql . " and c.customer_code " . LIKE . " '%" . $_POST['customer_code'] . "%' ";
    }
    if (isset($_POST['stockid']) and $_POST['stockid'] != '') {
      $sql = $sql . " and b.stockid " . LIKE . " '%" . $_POST['stockid'] . "%' ";
    }
    if (isset($_POST['item_name']) and $_POST['item_name'] != '') {
      $sql = $sql . " and b.item_name " . LIKE . " '%" . $_POST['item_name'] . "%' ";
    }
    if (isset($_POST['customer_order_number']) and $_POST['customer_order_number'] != '') {
      $sql = $sql . " and e.customer_order_number " . LIKE . " '%" . $_POST['customer_order_number'] . "%' ";
    }

  if (empty($_POST['FromDate']) == 0) 
  {
    $SQL_FromDate = strtotime($_POST['FromDate']);
    $sql .= " and d.delivery_date >= '" . $SQL_FromDate . "' ";
  }
   
  if (empty($_POST['ToDate']) == 0) 
  {
     $SQL_ToDate = strtotime($_POST['ToDate']) + 86400;
     $sql .= " and d.delivery_date <='" . $SQL_ToDate . "' ";
  }
  

  if (empty($_POST['FromDate2']) == 0) 
  {
    $SQL_FromDate2 = strtotime($_POST['FromDate2']);
    $sql .= " and a.check_date >= '" . $SQL_FromDate2 . "' ";
  }
   
  if (empty($_POST['ToDate2']) == 0) 
  {
     $SQL_ToDate2 = strtotime($_POST['ToDate2']) + 86400;
     $sql .= " and a.check_date <='" . $SQL_ToDate2 . "' ";
  }
 $sql .= " order by d.delivery_date desc ";
 
  $result = DB_query($sql,$db);
  if (DB_num_rows($result)==0) 
  {
    //unset($result);
    prnMsg(_('没有未对账单据，请重新输入条件查询！') ,'error');
  }
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>出货对账明细报表</title>
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

//function webdesign()
//{
//var a=document.getElementById("webdesign1").value;
//var b=document.getElementById("webdesign2").value;
// 
// 
//if(a==null||a.trim=="")
//a=0;
//if(b==null||b.trim=="")
//b=0;
// var c=(a*1)*(b*1)*(-1);
//document.getElementById("webdesign3").value=c;
//}

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
echo '
        <div class="centre" style="margin-bottom: 5px; display: flex;justify-content: flex-start;align-items: center;">
          <input id="input" type="submit" name="Search" value="查询"> &nbsp;&nbsp;
          <div class="export" >
            <a href="' . $RootPath . '/SodeliverycheckReportExcel.php?order_number=' .$_POST['order_number'] .
            '&delivery_num=' .$_POST['delivery_num'] .'&customer_code='.$_POST['customer_code'] .
            '&stockid=' .$_POST['stockid'] .'&item_name='.$_POST['item_name'] .
            '&wip_entity_name=' .$_POST['wip_entity_name'] .'&customer_order_number='.$_POST['customer_order_number'] .
            '&FromDate=' .$_POST['FromDate'] . '&ToDate=' .$_POST['ToDate'] .
            '&FromDate2=' .$_POST['FromDate2'] . '&ToDate2=' .$_POST['ToDate2'] .' ">' .'导出' . '</a>
          </div>
        </div>
';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('出货对账明细报表') . '</p>';
echo '<table cellpadding="3" class="selection">';
echo '</tr>'; 

echo '<div class="text-nav3">';

 echo '<div class="text-nav-1"><div>' . _('出货单') . ':</div>';
  echo '<input type="text" name="delivery_num"    value="' . $_POST['delivery_num'] . '"  size="15" maxlength="25" /></div>';

  echo '<div class="text-nav-1"><div>' . _('客户代号') . ':</div>';
  echo '<input type="text" id="text_slect_customer"  name="customer_code"   value="' . $_POST['customer_code'] . '" size="15" maxlength="25" />
  <image class="select_img" src="img/search.png" id="btn_slect_customer"/>
  </div>';

  echo '';

  echo '<div class="text-nav-1"><div>' . _('订单号') . ':</div>';
  echo '<input type="text" name="order_number"   value="' . $_POST['order_number'] . '" size="15" maxlength="25" /></div>';
   echo '<div class="text-nav-1"><div>' . _('工单号') . ':</div>';
  echo '<input type="text" name="wip_entity_name"   value="' . $_POST['wip_entity_name'] . '" size="15" maxlength="25" /></div>';
echo '<div class="text-nav-1"><div>' . _('料号') . ':</div>';
  echo '<input type="text" id="text_slect_buliao" name="stockid"   value="' . $_POST['stockid'] . '" size="15" maxlength="25" />
  <image class="select_img" src="img/search.png" id="btn_slect_buliao"/>';
  echo '</div>';
  echo '<div class="text-nav-1"><div>' . _('料号名称') . ':</div>';
  echo '<input type="text" id="text_slect_item_name" name="item_name"   value="' . $_POST['item_name'] . '" size="15" maxlength="25" />
  <image class="select_img" src="img/search.png" id="btn_slect_buliao2"/>
  </div>';
  echo '<div class="text-nav-1"><div>' . _('客户订单号') . ':</div>';
  echo '<input type="text" name="customer_order_number"   value="' . $_POST['customer_order_number'] . '" size="15" maxlength="25" /></div>';
if (!isset($_POST['FromDate2'])) 
{
  $_POST['FromDate2'] = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 30, date("Y")));
}
if (!isset($_POST['ToDate2'])) 
{
  $_POST['ToDate2'] = Date('Y-m-d');
}


echo '<div class="text-nav-1"><div>' . _('出货日期起') . ':</div>';
echo '<input type="text" autocomplete="off" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '"  name="FromDate" value="' . $_POST['FromDate'] . '" size="10" maxlength="15" />
</div>';

echo '<div class="text-nav-1"><div>' . _('出货日期止') . ':</div>';
echo '<input type="text" autocomplete="off" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '"  name="ToDate" value="' . $_POST['ToDate'] . '" size="10" maxlength="15" />
</div>';

echo '<div class="text-nav-1"><div>' . _('对账日期起') . ':</div>';
echo '<input type="text" autocomplete="off" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '"  name="FromDate2" value="' . $_POST['FromDate2'] . '" size="10" maxlength="15" />
</div>';

echo '<div class="text-nav-1"><div>' . _('对账日期止') . ':</div>';
echo '<input type="text" autocomplete="off" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '"  name="ToDate2" value="' . $_POST['ToDate2'] . '" size="10" maxlength="15" />
</div>';




echo '</table>';

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
        <th class="ascending" >' . _('出货日期') . '</th>
        <th class="ascending" width = 90>' . _('出货单') . '</th>	
        <th class="ascending" >' . _('行') . '</th>		
        <th   >' . _('客户代号') . '</th>		
	   <th class="ascending" >' . _('类型') . '</th>  
        <th   >' . _('订单号') . '</th>
        <th   >' . _('行') . '</th>
	    <th   >' . _('客户订单号') . '</th>
	    <th   >' . _('工单号') . '</th>  
		<th   >' . _('料号') . '</th>
        <th   >' . _('料号名称') . '</th>
        
        <th   >' . _('出货数量') . '</th>
		<th   >' . _('出货单价') . '</th> 
		<th   >' . _('出货金额') . '</th>
        <th   >' . _('对账数量') . '</th>
		<th   >' . _('对账单价') . '</th>
		<th   >' . _('对账金额') . '</th>
		<th   >' . _('对账日期') . '</th>
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
	 $check_amount=$myrow['delivery_quantity'] *  $myrow['price'];
      echo '<td>' . date('Y-m-d', $myrow['delivery_date']) . '</td> 
            <td>' . $myrow['delivery_num'] . '</td>
			<td>' . $myrow['delivery_line'] . '</td>
			<td>' . $myrow['customer_code'] . '</td>
          
			  <td>' . $myrow['delivery_type'] . '</td>
            <td>' . $myrow['so_order_number'] . '</td>
			<td>' . $myrow['so_line_no'] . '</td>
			<td>' . $myrow['customer_order_number']  . '</td>
			<td>' . $myrow['wip_entity_name']  . '</td>
      <td>' . $myrow['stockid'] . '</td>
          
      <td>' . $myrow['item_name']  . '</td> 
      
            <td>' . $myrow['shiped_quantity']  . '</td>
            <td>' . $myrow['price']  . '</td>
            <td>' . $myrow['line_amount']  . '</td>
            <td>' . $myrow['check_quantity']  . '</td>
            <td>' . $myrow['check_price']  . '</td>
            <td>' . $myrow['check_amount']  . '</td>
            <td>' . date('Y-m-d',$myrow['check_date'])  . '</td>
			        	
 
		';?>

       <?php 
	  
      echo  '</tr>';
      $i++;
      $RowIndex++;

    } //end loop through customers
	echo '</table></div>';
  echo '<input type="hidden" name="JustSelectedACustomer" value="Yes" />';
  echo '<div>
            
        </div>';
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
 
}


?>


<script type="text/javascript">

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
      

     
        $('#btn_slect_customer2').dialog({
            title:'选择客户',
            width: '1050px',
            height: 470,
            content:'url:BtnSearchCustomer517.php?fwValue=&cat=buliao',
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
<script type="text/javascript">
        $('#btn_slect_customer').dialog({
                title: '选择客户',
                width: '1050px',
                height: 470,
                content: 'url:BtnSearchCustomer517.php?fwValue=&cat=buliao',
                init: function() {
                        this.content.document.getElementById('cat').value = 'buliao';
                        this.content.document.getElementById('fwValue').value = '';
                }
        });
        $('#btn_slect_customer2').dialog({
                title: '选择客户',
                width: '1050px',
                height: 470,
                content: 'url:BtnSearchCustomer517.php?fwValue=&cat=buliao',
                init: function() {
                        this.content.document.getElementById('cat').value = 'buliao';
                        this.content.document.getElementById('fwValue').value = '';
                }
        });
        $('#btn_slect_buliao').dialog({
                title: '选择产品',
                width: '1200px',
                height: 470,
                content: 'url:Searchbuliao517.php?fwValue=&cat=buliao',
                init: function() {
                        this.content.document.getElementById('cat').value = 'buliao';
                        this.content.document.getElementById('fwValue').value = '';
                }
        });
        $('#btn_slect_buliao2').dialog({
                title: '选择产品',
                width: '1200px',
                height: 470,
                content: 'url:Searchbuliao517.php?fwValue=&cat=buliao',
                init: function() {
                        this.content.document.getElementById('cat').value = 'buliao';
                        this.content.document.getElementById('fwValue').value = '';
                }
        });
</script>

