<?php
 ob_start();
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include('includes/session.inc');
$Title = _('供应商审核');
$ViewTopic= '供应商审核';
$BookMark = '供应商审核';

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

$creation_date = strtotime(Date('Y-m-d H:i:s'));
if (isset($_POST['Agree'])){


foreach ($_POST as $key => $value) {
        if ($value != '') {
                if (substr($key, 0,6)=='status') {
                        $errorflag = 0;
                        $i = substr($key, 6);
                        if ($value != '') {
                               
                                 
                                $sql = "update vendors set vendor_status = '已签核',c_approve_date=". $creation_date .",c_approve_remark='". $_POST['approve_remark']  ."',c_approved_by='" . $_SESSION['UserID'] . "' where vendor_code = '".$_POST['vendor_code' . $i]."' ";
                            
                                $result = DB_query($sql,$db);
                                
                        }
                }
        }
}
    
        prnMsg('签核成功！',success);
        header("Location: SupplierApprove.php");
        
    }

    if (isset($_POST['Reject'])){
        foreach ($_POST as $key => $value) {
                if ($value != '') {
                        if (substr($key, 0,6)=='status') {
                                $errorflag = 0;
                                $i = substr($key, 6);
                                if ($value != '') {
                                       
                                         
                                        $sql = "update vendors set vendor_status = '已拒签',c_approve_date=". $creation_date .",c_approve_remark='". $_POST['approve_remark']  ."',c_approved_by='" . $_SESSION['UserID'] . "' where vendor_code = '".$_POST['vendor_code' . $i]."' ";
                                    
                                        $result = DB_query($sql,$db);
                                        
                                }
                        }
                }
        }
        prnMsg('签核成功！',success);
        header("Location: SupplierApprove.php");
        
    }

    $sql = 'select vendor_code,vendor_name,vendor_status,vendor_contacts,contacts_phone,contacts_mail,vendor_address,tax_code,enable_flag from vendors     where vendor_status = "待签核"
    ';
    $result = DB_query($sql,$db);

if(isset($_POST['Search']) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])){
    $sql = 'select vendor_code,vendor_name,vendor_status,vendor_contacts,contacts_phone,contacts_mail,vendor_address,tax_code,enable_flag from vendors     where vendor_status = "待签核"
	  ';
// 	 if ($_SESSION['SaleFlag']=='Y') {
// 	$sql = $sql . " and b.employee_num in (select salses_man from user_salesman where user_id='".$_SESSION['UserID']."') ";
//   }

    if(isset($_POST['CustomerCode']) and $_POST['CustomerCode'] != ''){
        $sql = $sql." and vendor_code ".LIKE." '%".$_POST['CustomerCode']."%' ";
    }
    if(isset($_POST['CustomerName']) and $_POST['CustomerName'] != ''){
        $sql = $sql." and vendor_name ".LIKE." '%".$_POST['CustomerName']."%' ";
    }
    if(isset($_POST['CustomerContacts']) and $_POST['CustomerContacts'] != ''){
        $sql = $sql." and vendor_contacts ".LIKE." '%".$_POST['CustomerContacts']."%' ";
    }


   
	if(isset($_POST['ContactsPhone']) and $_POST['ContactsPhone'] != ''){
        $sql = $sql." and contacts_phone ".LIKE." '%".$_POST['ContactsPhone']."%' ";
    }

 if(isset($_POST['vendor_address']) and $_POST['vendor_address'] != ''){
        $sql = $sql." and vendor_address ".LIKE." '%".$_POST['vendor_address']."%' ";
    }
	 if(isset($_POST['enable_flag']) and $_POST['enable_flag'] != ''){
        $sql = $sql." and enable_flag ".LIKE." '%".$_POST['enable_flag']."%' ";
    }
 
  
  
    $result = DB_query($sql,$db);
    if (DB_num_rows($result)==0) {
        unset($result);
        prnMsg(_('找不到该供应商，请重新输入条件查询！') ,'error');
    }
}


echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('查找供应商') . '</p>';
echo '<table cellpadding="3" class="selection">';
echo '<div class="text-nav">';

echo '<div class="text-nav-1 ">
<div>' . _('供应商全称') . ':</div>';
echo '<input type="text" name="CustomerName" value="' . $_POST['CustomerName'] . '" size="20" maxlength="25" /></div>';
echo '<div class="text-nav-1 ">
<div>' . _('供应商简称') . ':</div>';
echo '<input type="text" name="CustomerCode" value="' . $_POST['CustomerCode'] . '" size="20" maxlength="25" /></div>';
echo '<div class="text-nav-1 ">
<div>' . _('联系人') . ':</div>';
echo '<input type="text" name="CustomerContacts" value="' . $_POST['CustomerContacts'] . '" size="20" maxlength="25" /></div>';

echo '<div class="text-nav-1 ">
<div>' . _('地址') . ':</div>';
echo '<input type="text" name="vendor_address" value="' . $_POST['vendor_address'] . '" size="20" maxlength="25" /></div>';
echo '<div class="text-nav-1 ">
<div>' . _('税别') . ':</div>';
echo '<input type="text" name="tax_code" value="' . $_POST['tax_code'] . '" size="20" maxlength="25" /></div>';

	
	echo ' <div class="text-nav-1 ">
        <div>' . _('是否生效') . ':</div>
		<select required="required" name="enable_flag" value"">';
if ($_POST['enable_flag']=='Y' or $_POST['enable_flag']==''){
	echo '<option selected="selected" value="Y">' . _('是') . '</option>';
	echo '<option value="N">' . _('否') . '</option>';
} else {
 	echo '<option selected="selected" value="N">' . _('否') . '</option>';
	echo '<option value="Y">' . _('是') . '</option>';
}
echo '</select></div>
	';

 

echo '</div></table><div class="centre"><input type="submit" name="Search" value="查找"></div>';

if (isset($_POST['Search']) and isset($result) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])){
    $ListCount = DB_num_rows($result);
    $ListPageMax = ceil($ListCount / 50);
    
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
    echo '<div style="overflow:auto;">';
    echo '
                    <table cellpadding="2" class="selection" >';

    echo '<tr> <th  >选择</th>
                    <th class="ascending" width = 150>' . _('供应商简称') . '</th>
                    <th class="ascending"width = 250>' . _('供应商全称') . '</th>
                    <th class="ascending">' . _('状态') . '</th>
         
                    <th class="ascending"width = 150>' . _('联系人') . '</th>
                
                    <th class="ascending"width = 150>' . _('电话') . '</th>
                    <th class="ascending"width = 200>' . _('地址') . '</th> 
						 <th class="ascending" >' . _('税别') . '</th>
						 <th  >' . _('是否生效') . '</th>
            </tr>';
    $k = 0; //row counter to determine background colour
    $RowIndex = 0;
    


    if (DB_num_rows($result) <> 0) {
        DB_data_seek($result, ($_POST['PageOffset'] - 1) * 50);
        $i = 0; //counter for input controls
        while (($myrow = DB_fetch_array($result)) AND ($RowIndex <> 50)) {
			if ($k == 1) {
				echo '<tr class="EvenTableRows">';
				$k = 0;
			} else {
				echo '<tr class="OddTableRows">';
				$k = 1;
			}
			echo '  <td><input type="checkbox" name="status'.$i.'" checked /></td>
                        
                        <td><a href="' . $RootPath . '/SupplierApprove2.php?UpdatevendorCode=' . $myrow['vendor_code'] . '">' . $myrow['vendor_code'] . '</a></td>
				<td>' . $myrow['vendor_name'] . '</td>
				<td>' . $myrow['vendor_status'] . '</td>
				<td>' . $myrow['vendor_contacts'] . '</td>
				<td>' . $myrow['contacts_phone'] . '</td>
				<td>' . $myrow['vendor_address'] . '</td> 
                                <td>' . $myrow['tax_code'] . '</td>
                                <td>' . $myrow['enable_flag'] . '</td>
				';
                                echo ' <td>
                                <input type="hidden" name="vendor_code'.$i.'" size="10"  value="' . $myrow['vendor_code']  . '" />
                                </td> ';
       
			echo '
			</tr>';
			$i++;
			$RowIndex++;
			//end of page full new headings if
		} //end loop through customers
                echo '<tr><td colspan="15"><p><input type="checkbox" name="selectall" onclick="checkall(this.form);"/>全选/取消全选</p></td></tr>';
		echo '</table></div>';
		echo '<input type="hidden" name="JustSelectedAvendor" value="Yes" />';
	}

        if (isset($ListPageMax) AND $ListPageMax > 1) {
                echo '<br /><div class="centre">&nbsp;&nbsp;' . _('第') . '' . $_POST['PageOffset'] . ' ' . _('页，共') . ' ' . $ListPageMax . ' ' . _('页') . '. ' . _('转到页') . ': ';
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
    echo '<div class="centre">
    <input type="submit" name="Agree" value="核准"> &nbsp;&nbsp; 
    <input type="submit" name="Reject" value="拒绝"> &nbsp;&nbsp;
     
    </div>';

}
echo '</div></form>';
if (isset($_POST['add_new'])) {
    header('Location: AddCustomer.php');
}
include('includes/footer.inc');
?>
<script>
        function checkall(thisform){
		for(var i=0;i<thisform.elements.length;i++){
		if(thisform.elements[i].type=="checkbox"&&thisform.elements[i].checked==false&&thisform.elements[i].name!="selectall")
		{thisform.elements[i].checked=true;}
		else if(thisform.elements[i].type=="checkbox"&&thisform.elements[i].checked==true&&thisform.elements[i].name!="selectall")
		{thisform.elements[i].checked=false;}} }
</script>