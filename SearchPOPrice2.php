<?php

/* $Id: vendors.php 6338 2013-09-28 05:10:46Z daintree $ */
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
if (isset($_GET['UpdatevendorCode'])) {
    $po_item_id = $_GET['UpdatevendorCode'];
} else {
    $po_item_id = '';
}

$Title = _('供应商料号修改');
$ViewTopic = '供应商料号修改';
$BookMark = '供应商料号修改';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/CountriesArray.php');


echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/customer.png" title="' . _('供应商料号') .
 '" alt="" />' . ' ' . _('供应商料号信息') . '
	</p>';


if (isset($_POST['Deletevendor'])) {
    $CancelDelete = 0;
   
    if ($CancelDelete == 0) { //ie not cancelled the delete as a result of above tests
        $sql = "DELETE FROM vendors WHERE vendor_code='" . $_POST['vendor_code'] . "'";
        $result = DB_query($sql, $db);
        prnMsg(_('供应商') . ' ' . $_POST['vendor_code'] . ' ' . _('资料被成功删除') . ' !', 'success');
        include('includes/footer.inc');
        unset($_SESSION['vendor_code']);
        exit;
        echo '<br /><div class="centre"><a href="' . $RootPath . '/Searchvendor.php">' . _('查询供应商') . '</a></div>';
    }
}

if (isset($_POST['Addvendor'])) {
    isset($_SESSION['num' . $identifier]) or die("no session");
    if ($_SESSION['num' . $identifier] == 400) {
        $_SESSION['num' . $identifier] = 500;

        $InputError = 0;
        $i = 1;
        
        if (mb_strlen($_POST['vendor_item']) > 40 OR mb_strlen($_POST['vendor_item']) == 0) {
            $InputError = 1;
            prnMsg(_('供应商料号不超过300个字且不为空'), 'error');
            $Errors[$i] = 'vendor_name';
            $i++;
        }  /* elseif (!is_numeric(filter_number_format($_POST['CreditLimit']))) {
        $InputError = 1;
        prnMsg(_('The credit limit must be numeric'), 'error');
        $Errors[$i] = 'CreditLimit';
        $i++;
    }*/

        //没有错误，则执行如下
        //当是 update 则执行update 若是add 的时候，执行insert
        if ($InputError != 1) {

            $SQL_ClientSince = FormatDateForSQL($_POST['ClientSince']);

            if (isset($_POST['Addvendor'])) {
               
                DB_Txn_Begin($db);
                // CreditLimit='" . $_POST['CreditLimit'] . "' ,
				// typeid='" . $_POST['typeid'] . "' ,
                 $v_date = strtotime(Date('Y-m-d H:i:s'));
                 
                $sql = "UPDATE po_item_prices_all 
                 SET vendor_item='" . $_POST['vendor_item'] . "', 
				 enable_flag='" . $_POST['enable_flag'] . "', 
                  last_updated_by='" .$_SESSION['UserID'] . "',  
			      last_update_date='" . $v_date . "'
                WHERE po_item_id = '" . $_POST['po_item_id'] . "'"; 

                $ErrMsg = _('The vendor could not be updated because');
                $result = DB_query($sql, $db, $ErrMsg);
 
                DB_Txn_Commit($db);
                prnMsg(_('数据已更新！'), 'success');
                unset($_POST['vendor_code']);
                unset($_POST['vendor_name']);
                unset($_POST['vendor_contacts']);
                unset($_POST['disable_date']);
				 
                echo '<br />';
                echo '<br /><div class="centre"><a href="' . $RootPath . '/SearchPOprice.php">' . _('查询供应商料号') . '</a></div>';
            }
        } else {
            prnMsg(_('Validation failed') . '. ' . _('No updates or deletes took place'), 'error');
        }
    } else {
        header('Location: SearchPOprice.php');
    }
} else {
    session_start() or die("session is not started");
    $_SESSION['num' . $identifier] = 400;
}
if (isset($po_item_id) and $po_item_id != '') {
    //CreditLimit,
    $sql = "select b.po_item_id,a.vendor_code,a.vendor_name,c.item_no,c.item_name,c.item_desc,b.price,b.enable_flag,
	b.creation_date,b.vendor_item  from vendors a,po_item_prices_all b,sf_item_no c  where a.vendor_code=b.vendor_code
			and c.item_no=b.stockid
				and b.po_item_id= " . "'$po_item_id'";

    $ErrMsg = _('The vendor details could not be retrieved because');
    $result = DB_query($sql, $db, $ErrMsg);
    $myrow = DB_fetch_array($result);
    $_POST['vendor_code'] = $myrow['vendor_code'];
    $_POST['vendor_name'] = $myrow['vendor_name'];
    $_POST['item_no'] = $myrow['item_no'];
    $_POST['item_name'] = $myrow['item_name'];
    $_POST['item_desc'] = $myrow['item_desc'];
    $_POST['creation_date'] = date('Y-m-d H:i:s',$myrow['creation_date']);
    $_POST['enable_flag'] = $myrow['enable_flag'];
	$_POST['vendor_item'] = $myrow['vendor_item'];
	$_POST['po_item_id'] = $myrow['po_item_id'];
  
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
				<td>' . _('供应商代码') . ':</td>
				<td  >' . $_POST['vendor_code'] . '</td>
                                <input  type="hidden"  required="required" name="vendor_code"  value="' . $_POST['vendor_code'] . '" />
		        
				<td>' . _('供应商名称') . ':</td>
				<td  >' . $_POST['vendor_name'] . '</td> 
			</tr>
 
			<tr>
				<td>' . _('料号') . ':</td> 
				<td ><input  type="text"  readonly="readonly"  name="item_no" size="25" maxlength="40" value="' . $_POST['item_no'] . '" /></td>

				<td>' . _('料号名称') . ':</td> 
				<td ><input  type="text"  readonly="readonly"   name="item_name" size="25" maxlength="40" value="' . $_POST['item_name'] . '" /></td>
			</tr>
           
            <tr>
    <td >' . _('规格型号') . ':</td>
				<td  >' . $_POST['item_desc'] . '</td> 
				 <td >' . _('建立日期') . ':</td>
				<td  >' . $_POST['creation_date'] . '</td> 

			</tr> ';
				  
		  echo '<tr>
			<td>' . _('是否可用') . ':</td>
			<td><select required="required" name="enable_flag">';
	if ($_POST['enable_flag']=='Y'){
		echo '<option selected="selected" value="Y">' . _('Y') . '</option>';
		echo '<option value="N">' . _('N') . '</option>';
	} else {
		echo '<option selected="selected" value="N">' . _('N') . '</option>';
		echo '<option value="Y">' . _('Y') . '</option>';
	}

    echo '</select> <span style="color:red">*</span>  </td>';   
	 echo '<td>' . _('供应商料号') . ':    </td> 
		 <td ><input  type="text"  style="color:red" name="vendor_item" size="25" maxlength="40" value="' . $_POST['vendor_item'] . '" />
		 <input  type="hidden"   name="po_item_id" size="25" maxlength="40" value="' . $_POST['po_item_id'] . '" /></td> 
	</tr>';	
           
			
        echo '</table>';
        echo '<br />
			<div class="centre">
				<input type="submit" name="Addvendor" value="' . "更新资料" . '" />&nbsp;
				 
                                <input type="submit" name="return" value="' . "返回上一层" . '" />&nbsp;
                                 
</div>';
    }
    echo '</div>
          </form>';
} // end of main ifs
if (isset($_POST['return'])) {
    header('Location: SearchPOprice.php');
}
include('includes/footer.inc');
?>
