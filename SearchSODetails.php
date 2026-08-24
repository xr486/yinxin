<?php
ob_start();
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include('includes/session.inc');
$Title = _('查询订单');
$ViewTopic = '查询订单';
$BookMark = '查询订单';

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

if (isset($_POST['Search']) or isset($_POST['Go']) or isset($_POST['Next']) or isset($_POST['Previous'])) {

    $sql = "SELECT
       s.order_number,s.line,s.stockid,s.uom,s.quantity,s.price tax_price,(s.price/(1+h.tax_rate)) no_tax_price, s.quantity_shiped,s.line_amount tax_amount,(s.line_amount/(1+h.tax_rate)) not_tax_amount,
       h.customer_code,c.customer_name,h.creation_date,h.status,
       d.item_name,d.item_desc,d.gongyi,e.employee_name,h.customer_order_number
FROM
    so_lines_all s,
    so_headers_all h,
    customers c,sf_item_no d,hr_employees e
WHERE  s.order_number = h.order_number 
AND c.customer_code = h.customer_code  and e.employee_num=h.yewu
 and s.stockid=d.item_no
 and h.tax_flag='Y' ";

     
    if (isset($_POST['SO_from']) and $_POST['SO_from'] != '') {
        $sql = $sql . " and h.order_number " . LIKE . " '%" . $_POST['SO_from'] . "%' ";
    }
	if (isset($_POST['customer_order_number']) and $_POST['customer_order_number'] != '') {
        $sql = $sql . " and h.customer_order_number " . LIKE . " '%" . $_POST['customer_order_number'] . "%' ";
    } 
    if (isset($_POST['item_no']) and $_POST['item_no'] != '') {
        $sql = $sql . " and d.item_no " . LIKE . " '%" . $_POST['item_no'] . "%' ";
    }
    if (isset($_POST['item_name']) and $_POST['item_name'] != '') {
        $sql = $sql . " and d.item_name " . LIKE . " '%" . $_POST['item_name'] . "%' ";
    }
    if (isset($_POST['item_desc']) and $_POST['item_desc'] != '') {
        $sql = $sql . " and d.item_desc " . LIKE . " '%" . $_POST['item_desc'] . "%' ";
    }
    if (isset($_POST['customer_name']) and $_POST['customer_name'] != '') {
        $sql = $sql . " and c.customer_name " . LIKE . " '%" . $_POST['customer_name'] . "%' ";
    }

    if (isset($_POST['customer_code']) and $_POST['customer_code'] != '') {
        $sql = $sql . " and c.customer_code " . LIKE . " '%" . $_POST['customer_code'] . "%' ";
    }
    if (isset($_POST['employee_name']) and $_POST['employee_name'] != '') {
        $sql = $sql . " and e.employee_name " . LIKE . " '%" . $_POST['employee_name'] . "%' ";
    }
    if (isset($_POST['employee_num']) and $_POST['employee_num'] != '') {
        $sql = $sql . " and e.employee_num " . LIKE . " '%" . $_POST['employee_num'] . "%' ";
    }

    if (isset($_POST['dengji']) and $_POST['dengji'] != '') {
        $sql = $sql . " and c.dengji " . LIKE . " '%" . $_POST['dengji'] . "%' ";
    }


    if (empty($_POST['FromDate']) == 0) {
        $SQL_FromDate = strtotime($_POST['FromDate']);
        $sql .= " and h.creation_date >= '" . $SQL_FromDate . "' ";
    }
    if (empty($_POST['ToDate']) == 0) {
        $SQL_ToDate = strtotime($_POST['ToDate']) + 86400;
        //echo $SQL_ToDate;
        $sql .= " and h.creation_date <='" . $SQL_ToDate . "' ";
    }
    if ($_POST['status'] != "") {
        $sql .= " and h.status ='" . $_POST['status'] . "' ";
    }
   
$sql .= "union 
 SELECT
       s.order_number,s.line,s.stockid,s.uom,s.quantity,(s.price*(1+h.tax_rate)) ,s.price no_tax_price, s.quantity_shiped,(s.line_amount*(1+h.tax_rate)) ,s.line_amount ,
       h.customer_code,c.customer_name,h.creation_date,h.status,
       d.item_name,d.item_desc,d.gongyi,e.employee_name,h.customer_order_number
FROM
    so_lines_all s,
    so_headers_all h,
    customers c,sf_item_no d,hr_employees e
WHERE  s.order_number = h.order_number 
AND c.customer_code = h.customer_code  and e.employee_num=h.yewu
 and s.stockid=d.item_no
 and h.tax_flag='N' ";
if (isset($_POST['SO_from']) and $_POST['SO_from'] != '') {
        $sql = $sql . " and h.order_number " . LIKE . " '%" . $_POST['SO_from'] . "%' ";
    }
	if (isset($_POST['customer_order_number']) and $_POST['customer_order_number'] != '') {
        $sql = $sql . " and h.customer_order_number " . LIKE . " '%" . $_POST['customer_order_number'] . "%' ";
    } 
    if (isset($_POST['item_no']) and $_POST['item_no'] != '') {
        $sql = $sql . " and d.item_no " . LIKE . " '%" . $_POST['item_no'] . "%' ";
    }
    if (isset($_POST['item_name']) and $_POST['item_name'] != '') {
        $sql = $sql . " and d.item_name " . LIKE . " '%" . $_POST['item_name'] . "%' ";
    }
    if (isset($_POST['item_desc']) and $_POST['item_desc'] != '') {
        $sql = $sql . " and d.item_desc " . LIKE . " '%" . $_POST['item_desc'] . "%' ";
    }
    if (isset($_POST['customer_name']) and $_POST['customer_name'] != '') {
        $sql = $sql . " and c.customer_name " . LIKE . " '%" . $_POST['customer_name'] . "%' ";
    }

    if (isset($_POST['customer_code']) and $_POST['customer_code'] != '') {
        $sql = $sql . " and c.customer_code " . LIKE . " '%" . $_POST['customer_code'] . "%' ";
    }
    if (isset($_POST['employee_name']) and $_POST['employee_name'] != '') {
        $sql = $sql . " and e.employee_name " . LIKE . " '%" . $_POST['employee_name'] . "%' ";
    }
    if (isset($_POST['employee_num']) and $_POST['employee_num'] != '') {
        $sql = $sql . " and e.employee_num " . LIKE . " '%" . $_POST['employee_num'] . "%' ";
    }

    if (isset($_POST['dengji']) and $_POST['dengji'] != '') {
        $sql = $sql . " and c.dengji " . LIKE . " '%" . $_POST['dengji'] . "%' ";
    }


    if (empty($_POST['FromDate']) == 0) {
        $SQL_FromDate = strtotime($_POST['FromDate']);
        $sql .= " and h.creation_date >= '" . $SQL_FromDate . "' ";
    }
    if (empty($_POST['ToDate']) == 0) {
        $SQL_ToDate = strtotime($_POST['ToDate']) + 86400;
        //echo $SQL_ToDate;
        $sql .= " and h.creation_date <='" . $SQL_ToDate . "' ";
    }
    if ($_POST['status'] != "") {
        $sql .= " and h.status ='" . $_POST['status'] . "' ";
	}

  $sql .= " order by 13 desc";

    $result = DB_query($sql, $db);
    if (DB_num_rows($result) == 0) {
        unset($result);
        prnMsg(_('找不到订单明细，请重新输入条件查询！'), 'error');
    }
}


echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';

echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('查找订单明细') . '</p>';
echo '<table cellpadding="3" class="selection">';
echo '<div class="text-nav">';


echo '<div class="text-nav-1 "><div>' . _('订单号') . ':</div>';
echo '<input type="text" name="SO_from" value="' . $_POST['SO_from'] . '" size="20" maxlength="25" /></div>';
echo '<div class="text-nav-1 "><div>' . _('产品料号') . ':</div>';
echo '<input type="text" name="item_no" value="' . $_POST['item_no'] . '" size="20" maxlength="25" /></div>';
echo '<div class="text-nav-1 "><div>' . _('产品名称') . ':</div>
';
echo '<input type="text" name="item_name" value="' . $_POST['item_name'] . '" size="20" maxlength="25" /></div>';
echo '<div class="text-nav-1 "><div>' . _('规格型号') . ':</div>';
echo '<input type="text" name="item_desc" value="' . $_POST['item_desc'] . '" size="20" maxlength="25" /></div>';
echo '<div class="text-nav-1 "><div>' . _('客户订单号') . ':</div>';
echo '<input type="text" name="customer_order_number" value="' . $_POST['customer_order_number'] . '" size="20" maxlength="25" /></div>';
echo '<div class="text-nav-1 "><div>' . _('客户编号') . ':</div>';
echo '<input type="text" name="customer_code" id="text_slect_customer" value="' . $_POST['customer_code'] . '" size="10" maxlength="25" />
<image class="select_img" src="img/search.png" id="btn_slect_customer_code"/>
</div>';

echo '<div class="text-nav-1 "><div>' . _('客户名称') . ':</div>';
echo '<input type="text" name="customer_name" id="text_slect_name" value="' . $_POST['customer_name'] . '" size="20" maxlength="25" />
<image class="select_img" src="img/search.png" id="btn_slect_customer_name"/>
</div>';



echo '<div class="text-nav-1 "><div>' . _('订单状态') . ':</div><select name="status">';

echo '<option  selected="selected" value="' . $_POST['status'] . '">' . $_POST['status'] . '</option>';
echo '<option   value="">全部</option>';
echo '<option   value="待签核">待签核</option>';
echo '<option   value="待主管审核">待主管审核</option>';
echo '<option   value="已签核">已签核</option>';
echo '<option   value="已取消">已取消</option>';
echo '<option   value="已拒签">已拒签</option>';
echo '</select></div>';
echo '<div class="text-nav-1 "><div>' . _('业务员工号') . ':</div>';
echo '<input type="text" name="employee_num" value="' . $_POST['employee_num'] . '" size="10" maxlength="25" /></div>';
echo '<div class="text-nav-1 "><div>' . _('业务员姓名') . ':</div>';
echo '<input type="text" name="employee_name" value="' . $_POST['employee_name'] . '" size="10" maxlength="25" /></div>';
 
echo '<div class="text-nav-1 "><div>' . '下单日' . _('起') . ':</div>
		<input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="FromDate" maxlength="10" size="11" value="' . $_POST['FromDate'] . '" /></div>
		<div class="text-nav-1 "><div>' . _('下单日止') . ':</div>
		<input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="ToDate" maxlength="10" size="11" value="' . $_POST['ToDate'] . '" /></div>
	</div>';

echo '</table><div class="centre"><input type="submit" name="Search" value="查找"></div>';

if (isset($_POST['Search']) and isset($result) or isset($_POST['Go']) or isset($_POST['Next']) or isset($_POST['Previous'])) {
    $ListCount = DB_num_rows($result);
    $ListPageMax = ceil($ListCount / $_SESSION['DisplayRecordsMax']);

    if (isset($_POST['Next'])) {
        if ($_POST['PageOffset'] < $ListPageMax) {
            $_POST['PageOffset'] = $_POST['PageOffset'] + 1;
        }
    }
    if (isset($_POST['Previous'])) {
        if ($_POST['PageOffset'] > 1) {
            $_POST['PageOffset'] = $_POST['PageOffset'] - 1;
        }
    }
    echo '<input type="hidden" name="PageOffset" value="' . $_POST['PageOffset'] . '" />';
    if ($ListPageMax > 1) {
        echo '<br /><div class="centre">&nbsp;&nbsp;' . _('第') . '' . $_POST['PageOffset'] . ' ' . _('页，共') . ' ' . $ListPageMax . ' ' . _('页') . '. ' . _('转到页') . ': ';
        echo '<select name="PageOffset1">';
        $ListPage = 1;
        while ($ListPage <= $ListPageMax) {
            if ($ListPage == $_POST['PageOffset']) {
                echo '<option value="' . $ListPage . '" selected="selected">' . $ListPage . '</option>';
            } else {
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
    echo '<div style="overflow:scroll">
         <table  id="purchase_table" cellpadding="2" class="selection">';

    echo '<tr>
                    <th class="ascending" >' . _('订单号') . '</th>
                    <th width = 60>' . _('客户订单号') . '</th>
                    <th width = 60>' . _('状态') . '</th>
                    <th width = 60>' . _('销售') . '</th>
                    <th   width = 20>' . _('行') . '</th>
                    <th class="ascending"width = 100>' . _('客户简称') . '</th> 
                    <th class="ascending"width = 220>' . _('成品料号') . '</th>   
                    <th class="ascending"width = 220>' . _('产品名称') . '</th>  
                    <th class="ascending"width = 220>' . _('规格型号') . '</th>   
                    <th class="ascending"width = 50>' . _('单位') . '</th> 
                    <th class="ascending"width = 100>' . _('数量') . '</th>
					<th  class="ascending"width = 100>' . _('已出货量') . '</th>    ';
                    if($_SESSION['price_flag'] == 'N'){

    echo ' <th class="ascending"width = 80>' . _('未税单价') . '</th> 
				   <th class="ascending"width = 80>' . _('含税单价') . '</th> 
                    <th class="ascending"width = 80>' . _('未税金额') . '</th>
                    <th class="ascending"width = 80>' . _('含税金额') . '</th> ';
                    }
    echo ' 
                    <th class="ascending"width = 100>' . _('建单日期') . '</th>
            </tr>';
    $k = 0; //row counter to determine background colour
    $RowIndex = 0;

    $all_line_amount = 0;
    $all_line = 0;
    $all_amount = 0;
    $all_quantity = 0;
    $all_quantity_shiped = 0;
    if (DB_num_rows($result) <> 0) {
        DB_data_seek($result, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);
        $i = 0; //counter for input controls
        while (($myrow = DB_fetch_array($result)) and ($RowIndex <> $_SESSION['DisplayRecordsMax'])) {
            if ($k == 1) {
                echo '<tr class="EvenTableRows">';
                $k = 0;
            } else {
                echo '<tr class="OddTableRows">';
                $k = 1;
            }
            $all_line = $all_line + 1;
            $all_not_tax_amount = $all_not_tax_amount +  $myrow['not_tax_amount'];
            $all_amount = $all_amount + round(($myrow['line_amount'] + $myrow['tax_amount']), 2);
            $all_quantity = $all_quantity +  $myrow['quantity'];
            $all_quantity_shiped = $all_quantity_shiped +  $myrow['quantity_shiped'];
            
            echo '  
                <td><a href="' . $RootPath . '/SearchSO2.php?Updateorder_number=' . $myrow['order_number'] . '" target="view_window">' . $myrow['order_number'] . '</td>  
				<td>' .  $myrow['customer_order_number'] . '</td>
				<td>' .  $myrow['status'] . '</td>
                <td>' . $myrow['employee_name'] . '</td>
                <td>' . $myrow['line'] . '</td> 
                <td><a href="AddCustomers.php?UpdateCustomerCode=' . $myrow['customer_code'] . '" target="view_window">' . $myrow['customer_code'] . '</td>  
                <td>' . $myrow['stockid'] . '</td> 
                <td>' . $myrow['item_name'] . '</td> 
                <td>' . $myrow['item_desc'] . '</td>  
                <td>' . $myrow['uom'] . '</td> 
                <td>' . $myrow['quantity'] . '</td>
				<td>' . $myrow['quantity_shiped'] . '</td>  ';
                if($_SESSION['price_flag'] == 'N'){
 
            echo ' <td style="text-align:center;">' . sprintf("%.2f",$myrow['no_tax_price']). '</td> 
          <td style="text-align:center;">' . sprintf("%.2f",$myrow['tax_price']). '</td> 
           <td>' . sprintf("%.2f",$myrow['not_tax_amount']) . '</td> 
                <td>' . sprintf("%.2f",$myrow['tax_amount']) . '</td>  ';
                }
            echo ' 
                <td>' . date('Y-m-d', $myrow['creation_date']) . '</td> 
            
                ';

            echo '
			</tr>';
            $i++;
            $RowIndex++;
            //end of page full new headings if
        } //end loop through vendors
        if($_SESSION['price_flag'] == 'N'){

        echo '<td>合计</td>  <td> 行数</td>
	      <td>' . $all_line  . '</td>  
            <td> </td>
			<td> </td>
		 <td> </td> 
			<td> </td> 
			<td> </td> <td> </td><td> </td><td>' . $all_quantity  . '</td>  	<td>' . $all_quantity_shiped  . '</td>  <td> </td>  <td> </td> 
			<td>' . round($all_not_tax_amount, 2)  . '</td>  	<td>' . round($all_amount, 2)  . '</td>  
            ';
        }else{
            echo '<td>合计</td>  <td> 行数</td>
            <td>' . $all_line  . '</td>  
              <td> </td>
              <td> </td>
           <td> </td> 
              <td> </td> 
              <td> </td> <td> </td><td> </td><td>' . $all_quantity  . '</td>  	<td>' . $all_quantity_shiped  . '</td>  <td> </td>  <td> </td> 
             
              '; 
        }
        echo '</table></div>';
        echo '<input type="hidden" name="JustSelectedAvendor" value="Yes" />';
        echo '<div>
        <a href="' . $RootPath . '/SearchSODetailsReportExcel.php?customer_name=' . $_POST['customer_name'] .
            '&customer_code=' . $_POST['customer_code'] . '&dengji=' . $_POST['dengji'] . '&employee_num=' . $_POST['employee_num'] . '&status=' . $_POST['status'] . '&employee_name=' . $_POST['employee_name'] . '&SO_from=' . $_POST['SO_from'] . '&item_no=' . $_POST['item_no'] . '&item_name=' . $_POST['item_name'] .  '&item_desc=' . $_POST['item_desc'] .  '&FromDate=' . $_POST['FromDate'] . '&ToDate=' . $_POST['ToDate'] . ' ">' . '资料导出Excel表' . '</a>
    </div>';
    }

    if (isset($ListPageMax) and $ListPageMax > 1) {
        echo '<br /><div class="centre">&nbsp;&nbsp;' . _('第') . '' . $_POST['PageOffset'] . ' ' . _('页，共') . ' ' . $ListPageMax . ' ' . _('页') . '. ' . _('转到页') . ': ';
        echo '<select name="PageOffset2">';
        $ListPage = 1;
        while ($ListPage <= $ListPageMax) {
            if ($ListPage == $_POST['PageOffset']) {
                echo '<option value="' . $ListPage . '" selected="selected">' . $ListPage . '</option>';
            } else {
                echo '<option value="' . $ListPage . '">' . $ListPage . '</option>';
            }
            $ListPage++;
        }
        echo '</select>
        <input type="submit" name="Go2" value="' . _('转到') . '" />
        <input type="submit" name="Previous" value="' . _('上一页') . '" />
        <input type="submit" name="Next" value="' . _('下一页') . '" />';
        echo '</div>';
    }
}
echo '</div></form>';

include('includes/footer.inc');
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

    $("#btn_slect_customer_code").dialog({
        title: '选择客户',
        width: '1050px',
        height: 470,
        content: 'url:BtnSearchCustomerc1.php?fwValue=&cat=buliao',
        init: function() {
            this.content.document.getElementById('cat').value = 'buliao';
            this.content.document.getElementById('fwValue').value = '';
        }

    });
    $("#btn_slect_customer_name").dialog({
        title: '选择客户',
        width: '1050px',
        height: 470,
        content: 'url:BtnSearchCustomerc1.php?fwValue=&cat=buliao',
        init: function() {
            this.content.document.getElementById('cat').value = 'buliao';
            this.content.document.getElementById('fwValue').value = '';
        }

    });

    $("#btn_slect_customer_level").dialog({
        title: '选择客户',
        width: '1050px',
        height: 470,
        content: 'url:BtnSearchCustomerlevel.php?fwValue=&cat=buliao',
        init: function() {
            this.content.document.getElementById('cat').value = 'buliao';
            this.content.document.getElementById('fwValue').value = '';
        }

    });
</script>