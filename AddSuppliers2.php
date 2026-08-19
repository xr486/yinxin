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
    $UpdatevendorCode = $_GET['UpdatevendorCode'];
} else {
    $UpdatevendorCode = '';
}

$Title = _('供应商修改');
$ViewTopic = '供应商修改';
$BookMark = '供应商修改';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/CountriesArray.php');


echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/customer.png" title="' . _('供应商') .
 '" alt="" />' . ' ' . _('供应商信息') . '
	</p>';

    if (isset($_GET['file_name'])   ) {
        if (file_exists($_GET['file_patch'])) {
    
            // 尝试删除文件
        
            if (unlink($_GET['file_patch'])) {
               
                echo "文件 {$_GET['file_patch']} 已成功删除。";
        
            } else {
        
                echo "删除文件 {$_GET['file_patch']} 失败。";
        
            }
        
        } else {
        
            echo "文件 {$_GET['file_patch']} 不存在。";
        
        }
            $sql = "delete from   vendors_file
            where  file_name= '" . $_GET['file_name'] . "' and creation_date= '" . $_GET['creation_date'] . "' and  vendor_code= '" . $_GET['UpdatevendorCode'] . "'   ";
            $result = DB_query($sql,$db);
     
          
          
         prnMsg(_('删除成功！'), 'success');
         echo '<meta http-equiv="refresh" content="0; url=' . $RootPath .
                         '/AddSuppliers2.php?UpdatevendorCode='. $_GET['UpdatevendorCode'] . '" />';
         //DB_Txn_Commit($db);
        
          
     }
if (isset($_POST['Deletevendor'])) {
    $CancelDelete = 0;
	 $sql2 = "select * FROM po_headers_all WHERE vendor_code='" . $_POST['vendor_code'] . "'";
     $result2 = DB_query($sql2, $db);
	 $CancelDelete = DB_num_rows($result2);
   
    if ($CancelDelete == 0) { //ie not cancelled the delete as a result of above tests
        $sql = "DELETE FROM vendors WHERE vendor_code='" . $_POST['vendor_code'] . "'";
        $result = DB_query($sql, $db);

        //删除储存文件
        $sql_patch = "select file_patch from vendors_file where vendor_code='" . $_POST['vendor_code'] . "'";
        $result_patch = DB_query($sql_patch, $db);
        if (DB_num_rows($result_patch) == 0) {
        }else{
            while ($myrow = DB_fetch_array($result_patch)) {
                if (file_exists($myrow['file_patch'])) {
                    // 尝试删除文件
                 
                     if (unlink($myrow['file_patch'])) {
                     
                    echo "文件 {$myrow['file_patch']} 已成功删除。";
            
                    } else {
            
                    echo "删除文件 {$myrow['file_patch']} 失败。";
            
                    }
                } else {
                 echo "文件 {$myrow['file_patch']} 不存在。";
                }
            }
        }
    //删除数据库存储记录
        $sql_file = "DELETE FROM vendors_file WHERE vendor_code='" . $_POST['vendor_code'] . "'";
        $result_file = DB_query($sql_file, $db);

        prnMsg(_('供应商') . ' ' . $_POST['vendor_code'] . ' ' . _('资料被成功删除') . ' !', 'success');
        echo '<br /><div class="centre"><a href="' . $RootPath . '/SearchSupplier.php">' . _('查询供应商') . '</a></div>';
        include('includes/footer.inc');
        unset($_SESSION['vendor_code']);
        exit;
    } else {
	prnMsg(_('供应商') . ' ' . $_POST['vendor_code'] . ' ' . _('已建立采购单单,无法再删除,您可以失效') . ' !', 'error');
	}
}

if (isset($_POST['Addvendor'])) {
    isset($_SESSION['num' . $identifier]) or die("no session");
    if ($_SESSION['num' . $identifier] == 400) {
        $_SESSION['num' . $identifier] = 500;

        $InputError = 0;
        $i = 1;
        $_POST['vendor_code'] = mb_strtoupper($_POST['vendor_code']);
        $sql2 = "SELECT COUNT(vendor_code) FROM vendors WHERE vendor_code='" . $_POST['vendor_code'] . "'";
        $result2 = DB_query($sql2, $db);
        $myrow2 = DB_fetch_row($result2);
        $sql = "SELECT COUNT(vendor_name) FROM vendors WHERE vendor_name='" . $_POST['vendor_name'] . "'";
        $result = DB_query($sql, $db);
        $myrow = DB_fetch_row($result);
        if (mb_strlen($_POST['vendor_name']) > 40 OR mb_strlen($_POST['vendor_name']) == 0) {
            $InputError = 1;
            prnMsg(_('供应商名称不超过300个字且不为空'), 'error');
            $Errors[$i] = 'vendor_name';
            $i++;
        }  elseif ( ( ContainsIllegalCharacters($_POST['vendor_name']) )or( ContainsIllegalCharacters($_POST['vendor_code']) OR mb_strpos($_POST['vendor_code'], ' '))) {
        $InputError = 1;
        prnMsg(_('供应商代码和名称不能包含特殊字符：\ ') . " . - ' &amp; + \" " . _('or a space'), 'error');
        $Errors[$i] = 'vendor_code';
        $i++;
      }  elseif (mb_strlen($_POST['effective_date']) == 0) {
            $InputError = 1;
            prnMsg(_('生效日期不能为空'), 'error');
            $Errors[$i] = 'effective_date';
            $i++;
        }/* elseif (!is_numeric(filter_number_format($_POST['CreditLimit']))) {
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
                // echo 'asd';
                // echo $_POST['txtName13'];
				//disable_date = '" . $_POST['disable_date'] . "',
                DB_Txn_Begin($db);
                // CreditLimit='" . $_POST['CreditLimit'] . "' ,
				// typeid='" . $_POST['typeid'] . "' ,
                 $v_date = strtotime(Date('Y-m-d H:i:s'));
                
                $sql = "UPDATE vendors 
                 SET vendor_status = '待签核',
                 vendor_name='" . $_POST['vendor_name'] . "',
			     vendor_contacts='" . $_POST['vendor_contacts'] . "',
			      contacts_phone='" . $_POST['contacts_phone'] . "',
			       contacts_mail='" . $_POST['contacts_mail'] . "',
			      vendor_address ='" .$_POST['vendor_address'] . "',
                  bank_address ='" .$_POST['bank_address'] . "',
			             Currcode='" .$_POST['Currcode'] . "',
                         currencycode='" .$_POST['currencycode'] . "',
                         tax_code='" .$_POST['tax_code'] . "',
                         payments='" .$_POST['term_name'] . "',
						 enable_flag='" .$_POST['enable_flag'] . "',
                   effective_date='" .strtotime($_POST['effective_date']) . "',
                  last_updated_by='" .$_SESSION['UserID'] . "',
					 contacts_fax='" .$_POST['contacts_fax'] . "',
				       taxpayerid='" . $_POST['taxpayerid'] . "',
                        bank_name='" . $_POST['bank_name'] . "',
                bank_account='" . $_POST['bank_account'] . "',
			      last_update_date='" . $v_date . "'
                WHERE vendor_code = '" . $_POST['vendor_code'] . "'"; 
                $ErrMsg = _('The vendor could not be updated because');
                $result = DB_query($sql, $db, $ErrMsg);
//---------------------------
//---------------------------   
                DB_Txn_Commit($db);
                prnMsg(_('数据已更新！'), 'success');
                unset($_POST['vendor_code']);
                unset($_POST['vendor_name']);
                unset($_POST['vendor_contacts']);
                unset($_POST['disable_date']);
				unset($_POST['term_name']);
                unset($_POST['effective_date']);
                unset($_POST['bank_address']);
                unset($_POST['vendor_address']);
                unset($_POST['contacts_phone']);
                unset($_POST['contacts_mail']);
                unset($_POST['contacts_fax']);
				unset($_POST['taxpayerid']);
                unset($_POST['bank_name']);
				unset($_POST['bank_account']);



                echo '<br />';
                echo '<br /><div class="centre"><a href="' . $RootPath . '/SearchSupplier.php">' . _('查询供应商') . '</a></div>';
            }
        } else {
            prnMsg(_('Validation failed') . '. ' . _('No updates or deletes took place'), 'error');
        }
    } else {
        header('Location: SearchSupplier.php');
    }
} else {
    session_start() or die("session is not started");
    $_SESSION['num' . $identifier] = 400;
}
if (isset($UpdatevendorCode) and $UpdatevendorCode != '') {
    //CreditLimit,
    $sql = "SELECT vendor_code,
							vendor_name,
							vendor_contacts,
							contacts_phone,
							contacts_mail,
							vendor_address,
							created_by,
							creation_date,
							last_updated_by,
							last_update_date,
                               effective_date,
                                enable_flag,
							typeid,
                            payments,
								contacts_fax,
							taxpayerid,

                            bank_name,
							bank_account,
							currencycode,
							Currcode,
                            tax_code,
                            bank_address
				FROM vendors
				WHERE vendor_code= " . "'$UpdatevendorCode'";

    $ErrMsg = _('The vendor details could not be retrieved because');
    $result = DB_query($sql, $db, $ErrMsg);
    $myrow = DB_fetch_array($result);
    $_POST['vendor_code'] = $myrow['vendor_code'];
    $_POST['vendor_address'] = $myrow['vendor_address'];
    $_POST['bank_address'] = $myrow['bank_address'];
    $_POST['vendor_name'] = $myrow['vendor_name'];
    $_POST['vendor_contacts'] = $myrow['vendor_contacts'];
    $_POST['effective_date'] = $myrow['effective_date'];
    $_POST['payments'] = $myrow['payments'];
    //$_POST['CreditLimit'] = $myrow['CreditLimit'];
    $_POST['enable_flag'] = $myrow['enable_flag'];
    $_POST['contacts_phone'] = $myrow['contacts_phone'];
    $_POST['contacts_mail'] = $myrow['contacts_mail'];

     $_POST['contacts_fax'] = $myrow['contacts_fax'];
       $_POST['taxpayerid'] = $myrow['taxpayerid'];

			 $_POST['bank_name'] = $myrow['bank_name'];
			 $_POST['bank_account'] = $myrow['bank_account'];
       $_POST['currencycode'] = $myrow['currencycode'];
        $_POST['tax_code'] = $myrow['tax_code'];
    $_POST['Currcode'] = $myrow['Currcode'];
    //$_POST['typeid'] = $myrow['typeid'];
    if (!isset($_GET['delete'])) {
        if (!isset($_POST['effective_date'])) {
            $_POST['effective_date'] = Date($_SESSION['DefaultDateFormat']);
        }
        echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">'
        . '<input type="hidden" name = "identifier" value ="' . $identifier . '">';
        echo '<div>';
        echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
        echo '<table class="selection" id="SignFrame">
			';
        echo '</select>';

        echo '<div class="text-nav">
        <div class="text-nav-1 "><div>' . _('供应商代码') . ':</div>
        <input type="text" readonly="readonly" value="' . $_POST['vendor_code'] . '" />
                                <input  type="hidden"  required="required" name="vendor_code"  value="' . $_POST['vendor_code'] . '" /></div>
		        
                                <div class="text-nav-2 required"><div>' . _('供应商名称') . ':</div>
				<input  type="text" name="vendor_name" required="required" autofocus="autofocus" value="' . $_POST['vendor_name'] . '" size="42" maxlength="40" /></div>
                <div class="text-nav-1"><div>' . _('联系人') . ':</div>
				<input  type="text"    name="vendor_contacts" size="25" maxlength="40" value="' . $_POST['vendor_contacts'] . '" /></div>

				<div class="text-nav-1"><div>' . _('电话') . ':</div>
				<input  type="text" name="contacts_phone"   size="25" maxlength="40" value="' . $_POST['contacts_phone'] . '" /></div>
                <div class="text-nav-1"><div>' . _('纳税人识别号') . ':</div>
				<input  type="text" name="taxpayerid" size="35" maxlength="40" value="' . $_POST['taxpayerid'] . '" /></div>
		
                <div class="text-nav-2"><div>' . _('E-mail') . ':</div>
				<input  placeholder="' . _('e.g. user@domain.com') . '" type="text" name="contacts_mail" size="25" maxlength="40"  value="' . $_POST['contacts_mail'] . '" /></div>

                <div class="text-nav-1"><div>' . _('开户行') . ':</div>
				<input type="text" name="bank_name" size="35" maxlength="40"value="' . $_POST['bank_name'] . '"  />
                               </div>  
					 
                <div class="text-nav-1"><div>' . _('传真') . ':</div>
				<input  type="text" name="contacts_fax" size="25" maxlength="40" value="' . $_POST['contacts_fax'] . '" /></div>

                <div class="text-nav-1"><div>' . _('帐号 ') . ':</div>
				<input type="text" name="bank_account"  size="35" maxlength="40" value="' . $_POST['bank_account'] . '"  /></div>';

            echo '<div class="text-nav-1 required"><div>币别:</div>';
    echo '<select name="currencycode" required="required">';
        $sql ="select currabrev,currency from  currencies  order by currabrev";
                    $result = DB_query($sql, $db);
   while ($v = DB_fetch_array($result)) {
                        if ($v['currabrev'] == $_POST['currencycode']) {
                            ?>
                                <option value="<?= $v['currabrev'] ?>" selected="selected"><?= $v['currabrev'] ?></option>
                            <?php } else { ?>
                                <option value="<?= $v['currabrev'] ?>"><?= $v['currabrev'] ?></option>
                            <?php
                            }
                        }
    echo '</select>  </div>';   
		 echo '';

 

     
   


		         echo '<div class="text-nav-2"><div>' . _('供应商地址') . ':</div>
				<input type="text"  name="vendor_address" size="35" maxlength="70"value="' . $_POST['vendor_address'] . '"  /></div>';
				echo '<div class="text-nav-1 required"><div>税别：</div>';
    echo '<select name="tax_code" required="required">';
        $sql = "SELECT tax_name FROM tax_set  ORDER by tax_name ";  
                    $result = DB_query($sql, $db);
   while ($v = DB_fetch_array($result)) {
                        if ($v['tax_name'] == $_POST['tax_code']) {
                            ?>
                                <option value="<?= $v['tax_name'] ?>" selected="selected"><?= $v['tax_name'] ?></option>
                            <?php } else { ?>
                                <option value="<?= $v['tax_name'] ?>"><?= $v['tax_name'] ?></option>
                            <?php
                            }
                        }
    echo '</select></div>'; 
				echo '<div class="text-nav-2"><div>' . _('开票地址') . ':</div>
				<input type="text"  name="bank_address" size="35" maxlength="70"value="' . $_POST['bank_address'] . '"  /></div>';
 echo '<div class="text-nav-1"><div>供应商付款条件：</div>';
    echo '<select name="term_name" >';
        $sql = "select term_name from term_set order by termid ";  
                    $result = DB_query($sql, $db);
   while ($v = DB_fetch_array($result)) {
                        if ($v['term_name'] == $_POST['payments']) {
                            ?>
                                <option value="<?= $v['term_name'] ?>" selected="selected"><?= $v['term_name'] ?></option>
                            <?php } else { ?>
                                <option value="<?= $v['term_name'] ?>"><?= $v['term_name'] ?></option>
                            <?php
                            }
                        }
    echo '</select></div>'; 
		 
                  echo'';
		   
           
				 echo '<div class="text-nav-1 required"><div>' . _('生效日期') . ':</div>
				<input type="text" onfocus="WdatePicker()"  alt="' . $_SESSION['DefaultDateFormat'] . '" name="effective_date"    required="required" size="16" maxlength="10" title="' . _('effective_date .') . '" value="' . date('Y-m-d', $_POST['effective_date']) . '" /></div>';
           
	echo '<div class="text-nav-1 required"><div>' . _('是否生效') . ':</div>
		<select required="required" name="enable_flag">';
if ($_POST['enable_flag']=='Y'){
	echo '<option selected="selected" value="Y">' . _('是') . '</option>';
	echo '<option value="N">' . _('否') . '</option>';
} else {
 	echo '<option selected="selected" value="N">' . _('否') . '</option>';
	echo '<option value="Y">' . _('是') . '</option>';
}
echo '</select></div>
<div class="text-nav-1">
<div><a href="' . $RootPath . '/SussVendor.php?OrderNum=' . $myrow['vendor_code'] . '">附件上传</a></div>
</div>

	</div>';

		 
                  echo'';
        echo '</table>';
       
$sql2 = "SELECT vendor_code,
file_patch,creation_date,created_by,file_name
FROM vendors_file
where  vendor_code = '" .$UpdatevendorCode."'";
$result2 = DB_query($sql2, $db);
if (DB_num_rows($result2) == 0) {
   unset($result2);
 //  prnMsg(_('无附件'), 'info');
} else {
   echo '<p class="page_title_text">
<img src="' . $RootPath . '/css/' . $Theme . '/images/vendor.png" title="' . _('订单头信息') .
'" alt="" />' . ' ' . _('订单附件信息') . '
</p>';
   echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '"><input type="hidden" name = "identifier" value ="' . $identifier . '">';
   echo '<div>';
   echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
   echo '<table class="selection" align="center" >';
   $tableheader = '<tr>

                               <th width =150 >' . '附件名称' . '</th>
                               <th width =190 >' . '上传时间' . '</th>
                               <th width =80 >' . '上传人员' . '</th>
                               <th  width =50>' . '下载' . '</th>
                               <th  width =50>' . '操作' . '</th>


       </tr>';

   echo $tableheader;
   $RowCounter = 1;
   $k = 0; //row colour counter
   while ($myrow = DB_fetch_array($result2)) {
       if ($k == 1) {
           echo '<tr class="EvenTableRows">';
           $k = 0;
       } else {
           echo '<tr class="EvenTableRows">';
           $k++;
       }

// read_pdf('./999.pdf');
// <td><a href="' . $RootPath . '/Quote3.php?New=Yes&Updateorder_number=' . $myrow['file_patch'] . '">预览</td>
       echo '
             <td>' . $myrow['file_name'] . '</td>
             <td>' . date('Y-m-d H:i:s',$myrow['creation_date']) . '</td>
             <td>' . $myrow['created_by'] . '</td>
             <td><a href="' . $RootPath . '/' . $myrow['file_patch'] . '" target="_blank">' . '下载' . '</td>
             <td><a href="' . $RootPath . '/AddSuppliers2.php?UpdatevendorCode='.$myrow['vendor_code'] .'&file_name=' . $myrow['file_name'] . '&creation_date='.$myrow['creation_date'].'&file_patch='.$myrow['file_patch'].'" >删除</td>



</tr>';
       $RowCounter++;
       If ($RowCounter == 500) {
           $RowCounter = 1;
           echo $tableheader;
       }
   }
   echo '</table> ';


   echo '</div>
 </form>';
}

        echo '<br />
			<div class="centre">
				<input type="submit" name="Addvendor" value="' . "更新供应商" . '" />&nbsp;
				<input type="submit" name="Deletevendor" value="' . _('删除供应商') . '" onclick="return confirm(\'' . _('Are You Sure?') . '\');" />
                                <input type="submit" name="return" value="' . "返回上一层" . '" />&nbsp;
                                 
</div>';
    }
    echo '</div>
          </form>';
} // end of main ifs
if (isset($_POST['return'])) {
    header('Location: SearchSupplier.php');
}
include('includes/footer.inc');
?>
