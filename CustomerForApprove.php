<?php
 ob_start();
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include('includes/session.inc');
$Title = _('客户资料修改');
$ViewTopic= '客户资料修改';
$BookMark = '客户资料修改';

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

unset($result);
$searchCustomerCode = $_POST['CustomerCode'];
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

if(isset($_POST['Search']) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])){
    $sql = 'select customer_code,customer_name,customers_status,Customer_contacts,contacts_phone,contacts_mail,tax_name,contacts_fax,enable_flag,customer_type,creation_date  from customers   where customers_status="已签核"';
        
    if ($_SESSION['SaleFlag']=='Y') {
		  $sql = $sql . " and   employee_num='".$_SESSION['SalesMan']."' ";
  }
    if(isset($_POST['CustomerCode']) and $_POST['CustomerCode'] != ''){
        $sql = $sql." and customer_code ".LIKE." '%".$_POST['CustomerCode']."%' ";
    }
    if(isset($_POST['CustomerName']) and $_POST['CustomerName'] != ''){
        $sql = $sql." and customer_name ".LIKE." '%".$_POST['CustomerName']."%' ";
    }
    if(isset($_POST['CustomerContacts']) and $_POST['CustomerContacts'] != ''){
        $sql = $sql." and Customer_contacts ".LIKE." '%".$_POST['CustomerContacts']."%' ";
    }
	if(isset($_POST['customer_type']) and $_POST['customer_type'] != ''){
        $sql = $sql." and customer_type ".LIKE." '%".$_POST['customer_type']."%' ";
    }

	if(isset($_POST['ContactsPhone']) and $_POST['ContactsPhone'] != ''){
        $sql = $sql." and contacts_phone ".LIKE." '%".$_POST['ContactsPhone']."%' ";
    }
	if(isset($_POST['tax_name']) and $_POST['tax_name'] != ''){
        $sql = $sql." and tax_name ".LIKE." '%".$_POST['tax_name']."%' ";
    }
 if(isset($_POST['contacts_fax']) and $_POST['contacts_fax'] != ''){
        $sql = $sql." and contacts_fax ".LIKE." '%".$_POST['contacts_fax']."%' ";
    }
	if(isset($_POST['enable_flag']) and $_POST['enable_flag'] != ''){
        $sql = $sql." and enable_flag ".LIKE." '%".$_POST['enable_flag']."%' ";
    }
    if(isset($_POST['dengji']) and $_POST['dengji'] != ''){
        $sql = $sql." and dengji='".$_POST['dengji']."' ";
    }



    $result = DB_query($sql,$db);
    if (DB_num_rows($result)==0) {
        unset($result);
        prnMsg(_('找不到该客户，请重新输入条件查询！') ,'error');
    }
}


echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('查找客户') . '</p>';
echo '<table cellpadding="3" class="selection">';
echo '<div class="text-nav">';
echo '<div class="text-nav-1 "><div>' . _('客户全称') . ':</div>';
echo '<input type="text" name="CustomerName" id="text_slect_name" value="' . $_POST['CustomerName'] . '" size="20" maxlength="25" />
<image class="select_img" src="img/search.png" id="btn_slect_customer_name"/>
</div>';
echo '<div class="text-nav-1 "><div>' . _('客户编号') . ':</div>
	';
echo '<input type="text" name="CustomerCode"  id="text_slect_customer" value="' . $_POST['CustomerCode'] . '" size="20" maxlength="25" />
<image class="select_img" src="img/search.png" id="btn_slect_customer_code"/>
</div>';
echo '<div class="text-nav-1 "><div>' . _('联系人姓名') . ':</div>
	';
echo '<input type="text" name="CustomerContacts" value="' . $_POST['CustomerContacts'] . '" size="20" maxlength="25" /></div>';
echo '<div class="text-nav-1 "><div>' . _('客户分类') . ':</div>
	';
echo '<input type="text" name="customer_type" value="' . $_POST['customer_type'] . '" size="20" maxlength="25" /></div>';

echo '<div class="text-nav-1 "><div>' . _('传真') . ':</div>
';
echo '<input type="text" name="contacts_fax" value="' . $_POST['contacts_fax'] . '" size="20" maxlength="25" /></div>';
echo '<div class="text-nav-1 "><div>' . _('税别') . ':</div>
';
echo '<input type="text" name="tax_name" value="' . $_POST['tax_name'] . '" size="20" maxlength="25" /></div>';


	echo '<div class="text-nav-1 required"><div>' . _('是否生效') . ':</div>
		<select required="required" name="enable_flag" value"">';
if ($_POST['enable_flag']=='Y' or $_POST['enable_flag']==''){
	echo '<option selected="selected" value="Y">' . _('是') . '</option>';
	echo '<option value="N">' . _('否') . '</option>';
} else {
 	echo '<option selected="selected" value="N">' . _('否') . '</option>';
	echo '<option value="Y">' . _('是') . '</option>';
}
echo '</select></div>';
echo '<div class="text-nav-1 "><div>' . _('等级') . ':</div>';
echo '<input type="text" name="dengji" id="text_slect_customer_level" value="" size="20" maxlength="25" />
<image class="select_img" src="img/search.png" id="btn_slect_customer_level"/>
</div></div>';
echo '</table><div class="centre"><input type="submit" name="Search" value="查找"> &nbsp;&nbsp;<input type="submit" name="add_new" value="新增"></div>';

if (isset($_POST['Search']) and isset($result) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])){
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
                    <th class="ascending" width = 150>' . _('客户编号') . '</th>
                    <th class="ascending"width = 250>' . _('客户全称') . '</th>

                    <th class="ascending"width = 150>' . _('联系人') . '</th>

                    <th class="ascending"width = 150>' . _('电话') . '</th>
                    <th class="ascending"width = 200>' . _('邮箱') . '</th>
                    <th class="ascending"width = 200>' . _('传真') . '</th>
						 <th class="ascending" >' . _('税别') . '</th>
						 <th class="ascending" >' . _('客户分类') . '</th>
						 <th class="ascending" >' . _('建立时间') . '</th>
						 <th  >' . _('是否生效') . '</th>
            </tr>';
    $k = 0; //row counter to determine background colour
    $RowIndex = 0;



    if (DB_num_rows($result) <> 0) {
        DB_data_seek($result, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);
        $i = 0; //counter for input controls
        while (($myrow = DB_fetch_array($result)) AND ($RowIndex <> $_SESSION['DisplayRecordsMax'])) {
			if ($k == 1) {
				echo '<tr class="EvenTableRows">';
				$k = 0;
			} else {
				echo '<tr class="OddTableRows">';
				$k = 1;
			}
			echo '  <td><a href="' . $RootPath . '/CustomerForApprove2.php?UpdateCustomerCode=' . $myrow['customer_code'] . '">' . $myrow['customer_code'] . '</td>
				<td>' . $myrow['customer_name'] . '</td>
				<td>' . $myrow['Customer_contacts'] . '</td>
				<td>' . $myrow['contacts_phone'] . '</td>
				<td>' . $myrow['contacts_mail'] . '</td>
					<td>' . $myrow['contacts_fax'] . '</td>
					<td>' . $myrow['tax_name'] . '</td>
					<td>' . $myrow['customer_type'] . '</td>
					<td>' . date('Y-m-d',$myrow['creation_date']) . '</td>
					<td>' . $myrow['enable_flag'] . '</td>
				';


			echo '
			</tr>';
			$i++;
			$RowIndex++;
			//end of page full new headings if
		} //end loop through customers
		echo '</table></div>';
                echo '<input type="hidden" name="JustSelectedAvendor" value="Yes" />';
                echo '<div>
                <a href="' . $RootPath . '/SearchCustomerDetailExcel.php?CustomerName=' . $_POST['CustomerName'] .
        '&CustomerCode=' . $_POST['CustomerCode'] . '&CustomerContacts=' . $_POST['CustomerContacts'] . '&customer_type=' . $_POST['customer_type'] . '&contacts_fax=' . $_POST['contacts_fax'] . '&dengji=' . $_POST['dengji'] . '&tax_name=' . $_POST['tax_name'] . '&enable_flag=' . $_POST['enable_flag'] .  ' ">' . '资料导出Excel表' . '</a>
            </div>';
	}

        if (isset($ListPageMax) AND $ListPageMax > 1) {
                echo '<br /><div class="centre">&nbsp;&nbsp;' . _('第') . '' . $_POST['PageOffset'] . ' ' . _('页，共') . ' ' . $ListPageMax . ' ' . _('页数') . '. ' . _('转到页') . ': ';
                echo '<select name="PageOffset2">';
                $ListPage = 1;
                while ($ListPage <= $ListPageMax) {
                        if ($ListPage == $_POST['PageOffset']) {
                                echo '<option value="' . $ListPage . '" selected="selected">' . $ListPage . '</option>';
                        }
                        else {
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
if (isset($_POST['add_new'])) {
        header('Location: AddCustomer.php?New=Y');
}
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





