<?php

/* $Id: customers.php 6338 2013-09-28 05:10:46Z daintree $ */
 ob_start();
include('includes/session.inc');
if (isset($_GET['identifier'])) {
    $_POST['identifier'] = $_GET['identifier'];
}

if (!isset($_POST['identifier'])) {
    $identifier = date('U');
} else {
    $identifier = $_POST['identifier'];
}
if (isset($_GET['Updateitem_relation_id'])) {
    $Updateitem_relation_id = $_GET['Updateitem_relation_id'];
} else {
    $Updateitem_relation_id = '';
}

$Title = _('供应商料号维护');
$ViewTopic = '供应商料号维护';
$BookMark = '供应商料号维护';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/CountriesArray.php');

echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/customer.png" title="' . _('公司') .
 '" alt="" />' . ' ' . _('供应商料号维护') . '
	</p>';


if (isset($_POST['deleteRec'])) {
    $CancelDelete = 0;

    if ($CancelDelete == 0) { //ie not cancelled the delete as a result of above tests
        $sql = "DELETE FROM vendor_item_relation WHERE item_relation_id='" . $_POST['item_relation_id'] . "'";
        $result = DB_query($sql, $db);
        prnMsg(_('供应商料号被成功删除') . ' !', 'success');
        echo '<br /><div class="centre"><a href="' . $RootPath . '/SearchVendorItem.php">' . _('查询其它供应商料号') . '</a></div>';
        include('includes/footer.inc');
        unset($_SESSION['coycode']);
        
		exit;
    }
}

if (isset($_POST['Addemployee'])) {
    isset($_SESSION['num' . $identifier]) or die("no session");
    if ($_SESSION['num' . $identifier] == 400) {
        $_SESSION['num' . $identifier] = 500;

        $InputError = 0;
        $i = 1;
         
        //没有错误，则执行如下
        //当是 update 则执行update 若是add 的时候，执行insert
        if ($InputError == 0) {

            $SQL_ClientSince = FormatDateForSQL($_POST['ClientSince']);
             $v_date =time();     
                DB_Txn_Begin($db);          
                   $sql = "UPDATE vendor_item_relation
                   SET enable_flag='" . $_POST['enable_flag'] . "',
				   remark='" . $_POST['remark'] . "',                    
			      last_updated_by='" . $_SESSION['UserID'] . "',
			      last_update_date='" . $v_date . "'
                WHERE item_relation_id = '" . $_POST['item_relation_id'] . "'"; 
 
				 
                $ErrMsg = _('The vendor could not be updated because');
                $result = DB_query($sql, $db, $ErrMsg);
//---------------------------
//---------------------------
                DB_Txn_Commit($db);
                prnMsg(_('数据已更新！'), 'success');
                unset($_POST['item_relation_id']);
                unset($_POST['coyname']); 
                echo '<br />';
                echo '<br /><div class="centre"><a href="' . $RootPath . '/SearchVendorItem.php">' . _('查询供应商对应料号') . '</a></div>';
            
        } else {
            prnMsg(_('验证失败') . '. ' . _('不能更新'), 'error');
        }
    } else {
        header('Location: SearchVendorItem.php');
    }
} else {
    session_start() or die("session is not started");
    $_SESSION['num' . $identifier] = 400;
}
if (isset($Updateitem_relation_id) and $Updateitem_relation_id != '') {
    //CreditLimit,
    $sql = "select a.vendor_code,a.vendor_name,b.remark,c.item_no,c.item_name,c.item_desc,b.enable_flag,b.item_relation_id,b.creation_date,b.created_by 
	from vendors a,vendor_item_relation b,sf_item_no c  where a.vendor_code=b.vendor_code
	and b.item_no=c.item_no 
     and item_relation_id= " . "'$Updateitem_relation_id'";

    $ErrMsg = _('The vendor details could not be retrieved because');
    $result = DB_query($sql, $db, $ErrMsg);
    $myrow = DB_fetch_array($result);
    $_POST['item_no'] = $myrow['item_no'];
    $_POST['item_name'] = $myrow['item_name']; 
    $_POST['item_desc'] = $myrow['item_desc']; 
	
	$_POST['vendor_code'] = $myrow['vendor_code'];
	$_POST['vendor_name'] = $myrow['vendor_name'];
	$_POST['remark'] = $myrow['remark'];
    $_POST['enable_flag'] = $myrow['enable_flag']; 
    $_POST['creation_date'] = $myrow['creation_date'];
    $_POST['created_by'] = $myrow['created_by'];
    $_POST['item_relation_id'] = $myrow['item_relation_id'];

    if (!isset($_GET['delete'])) {
        if (!isset($_POST['effective_date'])) {
            $_POST['effective_date'] = Date($_SESSION['DefaultDateFormat']);
        }
        echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">'
        . '<input type="hidden" name = "identifier" value ="' . $identifier . '">';
        echo '<div>';
        echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
        echo '<table class="selection" id="SignFrame">
			<tr><td valign="top">';
        echo '</select></td>
                        </tr>';

        echo '          <tr>
				<td bgcolor="#87CEFA">' . _('料号') . ':</td>
				<td>' . $_POST['item_no'] . '</td>
                <input  type="hidden" name="item_no"  value="' . $_POST['item_no'] . '" />
                <input  type="hidden" name="item_relation_id"  value="' . $_POST['item_relation_id'] . '" />
			  </tr>
			<tr>
				<td bgcolor="#87CEFA">' . _('料号名称') . ':</td>
				<td colspan="3">' . $_POST['item_name'] . '</td>
			 
			</tr>
			<tr>
			
			<tr>
			<td bgcolor="#87CEFA">' . _('规格型号') . ':</td>
			<td colspan="3">' . $_POST['item_desc'] . '</td>
			 
				
				
			</tr>
			
				<td bgcolor="#87CEFA">' . _('供应商简称') . ':</td>
				<td colspan="3">' . $_POST['vendor_code'] . '</td>
				 
				</tr>
				<td bgcolor="#87CEFA">' . _('供应商全称') . ':</td>
				<td colspan="3">' . $_POST['vendor_name'] . '</td>
			</tr>  
				
			<tr>
			<td bgcolor="#87CEFA">' . _('备注') . ':</td>
				<td><input type="text" name="remark" size="20" value="' . $_POST['remark'] . '"  /></td>
			 
			</tr> ';
     echo '  <td>' . _('是否生效') . ':</td>
		<td><select required="required" name="enable_flag">';
if ($_POST['enable_flag']=='Y'){
	echo '<option selected="selected" value="Y">' . _('是') . '</option>';
	echo '<option value="N">' . _('否') . '</option>';
} else {
 	echo '<option selected="selected" value="N">' . _('否') . '</option>';
	echo '<option value="Y">' . _('是') . '</option>';
}
echo '</select></td>
	</tr>';
               
        echo '</table>';
        echo '<br />
			<div class="centre">
				<input type="submit" name="Addemployee" value="' . "修改保存" . '" />&nbsp;
				<input type="submit" name="deleteRec" value="' . "删除供应商料号" . '" />&nbsp;
				


</div>';
    }
    echo '</div>
          </form>';
} // end of main ifs
if (isset($_POST['return'])) {
    header('Location: SearchCompany.php');
}
include('includes/footer.inc');
?>
