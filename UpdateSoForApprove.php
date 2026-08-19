<?php

ob_start();
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include('includes/session.inc');
$Title = _('销售订单修改');
$ViewTopic = '销售订单修改';
$BookMark = '销售订单修改';

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

unset($result);

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

if (isset($_POST['Search']) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])) {

 
  $sql="SELECT 
    b.customer_code,
    b.customer_name, 
    p.order_number,
    p.status,p.approve_date,
    p.header_remark, 	order_all_amount,customer_order_number,
    b.customer_contacts,
    p.need_date, 
    p.creation_date,
    p.created_by,(SELECT realname FROM www_users w WHERE w.userid=p.created_by) realname,
    p.last_update_date,
    p.last_updated_by,
    p.customer_contact,p.coycode 
 FROM so_headers_all p,customers b   
 WHERE p.customer_code = b.customer_code 
 and  p.status in ('待签核','已拒绝','已取消','已拒签','待主管审核','已签核') " ;
   
 if ($_SESSION['SaleFlag']=='Y') {
		  $sql = $sql . " and  b.employee_num='".$_SESSION['SalesMan']."' ";
  }
    if (isset($_POST['order_number']) and $_POST['order_number'] != '') {
        $sql = $sql . " and p.order_number " . LIKE . " '%" . $_POST['order_number'] . "'";
    }
	if (isset($_POST['customer_order_number']) and $_POST['customer_order_number'] != '') {
        $sql = $sql . " and p.customer_order_number " . LIKE . " '%" . $_POST['customer_order_number'] . "'";
    }
    if (isset($_POST['customer_name']) and $_POST['customer_name'] != '') {
        $sql = $sql . " and b.customer_name " . LIKE . " '%" . $_POST['customer_name'] . "%' ";
    }
   
    if (empty($_POST['FromDate']) == 0) {
        $SQL_FromDate = strtotime($_POST['FromDate']);
        $sql .= " and p.creation_date >= '" . $SQL_FromDate . "' ";
    }
    if (empty($_POST['ToDate']) == 0) {
        $SQL_ToDate = strtotime($_POST['ToDate']) + 86400;
        //echo $SQL_ToDate;
        $sql .= " and p.creation_date <='" . $SQL_ToDate . "' ";
    }
	
 
    $sql .= " order by p.creation_date  desc ";
    $result = DB_query($sql, $db);
    if (DB_num_rows($result) == 0) {
        unset($result);
        prnMsg(_('找不到待签核订单，请重新输入条件查询！'), 'error');
    }
}


echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('销售订单修改') . '</p>';
echo '';
echo '<div class="text-nav">';
echo '<div class="text-nav-1"><div>' . _('订单号') . ':</div>';
echo '<input type="text" name="order_number" value="' . $_POST['order_number'] . '" size="20" maxlength="25" /></div>';

echo '<div class="text-nav-1"><div>' . _('客户编号') . ':</div>';
echo '<input type="text" name="customer_code" id="text_slect_customer" value="' . $_POST['customer_code'] . '" size="20" maxlength="25" />
<image class="select_img" src="img/search.png" id="btn_slect_customer_code"/>
</div>';
echo '<div class="text-nav-1"><div>' . _('客户名称') . ':</div>';
echo '<input type="text" name="customer_name" id="text_slect_name" value="' . $_POST['customer_name'] . '" size="20" maxlength="25" />
<image class="select_img" src="img/search.png" id="btn_slect_customer_name"/>
</div>';
 
 echo '<div class="text-nav-1"><div>' . _('客户订单号') . ':</div>';
echo '<input type="text" name="customer_order_number" value="' . $_POST['customer_order_number'] . '" size="20" maxlength="25" /></div>';

/*if (!isset($_POST['FromDate'])) {
    $_POST['FromDate'] = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 7, date("Y")));
}
 
if (!isset($_POST['ToDate'])) {
    $_POST['ToDate'] = Date('Y-m-d');
}*/
 
echo '<div class="text-nav-1"><div> 下单日期起:</div>
		<input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="FromDate" maxlength="10" size="11"  value="' . $_POST['FromDate'] . '" /></div>
		<div class="text-nav-1"><div>' . _('下单日期止') . ':</div>
		<input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="ToDate" maxlength="10" size="11"  value="' . $_POST['ToDate'] . '" /></div>
';
echo '</div>';

echo '</table><div class="centre"><input type="submit" name="Search" value="查找"> </div>'
 . '</br>';

if (isset($_POST['Search']) and isset($result) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])) {
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
    echo '			  <div class="text-nav-table">
                    <table cellpadding="2" class="selection" >';

    echo '<tr>
                    <th class="ascending" width = 100>' . _('订单号') . '</th>
                    <th class="ascending"width = 60>' . _('状态') . '</th>
                    <th  class="ascending"width = 100>' . _('客户编号') . '</th>
					 <th class="ascending"width = 250>' . _('客户名称') . '</th>
                    <th class="ascending" width = 100>' . _('客户订单号') . '</th>
                    <th class="ascending"width = 120>' . _('总额') . '</th>                              
                    <th class="ascending"width = 200>' . _('备注') . '</th>
                    <th class="ascending"width = 90>' . _('需求日期') . '</th>
                    <th class="ascending"width = 90>' . _('签核日期') . '</th>
                    <th  class="ascending"width = 180>' . _('下单日期') . '</th>
					 <th  width = 90>' . _('建单者') . '</th> 
            </tr>';
    $k = 0; //row counter to determine background colour
    $RowIndex = 0;



    if (DB_num_rows($result) <> 0) {
        DB_data_seek($result, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);
        $i = 0; //counter for input controls
        while (($myrow = DB_fetch_array($result)) AND ( $RowIndex <> $_SESSION['DisplayRecordsMax'] )) {
            if ($k == 1) {
                echo '<tr class="EvenTableRows">';
                $k = 0;
            } else {
                echo '<tr class="OddTableRows">';
                $k = 1;
            }
            unset($v_status);
           $approve_date='';
			if ($myrow['approve_date']>1) {
				 $approve_date=date('Y-m-d', $myrow['approve_date']);
			}
          
			 
            echo '   <td><a href="' . $RootPath . '/QuoteSoUpdate2.php?New=Yes&Updateorder_number=' . $myrow['order_number'] . '">' . $myrow['order_number'] . '</td>
				<td>' . $myrow['status']. '</td>
                    <td>' . $myrow['customer_code'] . '</td> 
					<td>' . $myrow['customer_name'] . '</td> 
					<td>' . $myrow['customer_order_number'] . '</td> 
                  <td>' . $myrow['order_all_amount'] . '</td> 
				<td>' . $myrow['header_remark'] . '</td>
                   <td>' . date('Y-m-d', $myrow['need_date']) . '</td>
				<td>' . $approve_date . '</td>
				<td>' . date('Y-m-d H:i:s', $myrow['creation_date']) . '</td>
				<td>' . $myrow['realname'] . '</td>
                                 ';

            echo '
			</tr>';
		 
            $i++;
            $RowIndex++;
            //end of page full new headings if
        } //end loop through vendors
        echo '</table>
        </div>';
        echo '<input type="hidden" name="JustSelectedAvendor" value="Yes" />';
    }

    if (isset($ListPageMax) AND $ListPageMax > 1) {
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