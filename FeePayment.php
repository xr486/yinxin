<?php
/* $Id: Product_type.php 6310 2013-08-29 10:42:50Z daintree $*/
//此文件，对于stockmaster部分去除，之后更换为派车单表
include('includes/session.inc');

$Title = _('费用付款处理');
//$Title = _('Units Of Measure');
include('includes/header.inc');
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' .
		_('Search') . '" alt="" />' . ' ' . $Title . '</p>';


if (isset($_POST['Submit'])) {
	$InputError = 0;
	if (ContainsIllegalCharacters($_POST['PaySupplier'])) {
		$InputError = 1;
		prnMsg( '付款单位不可包含特殊字符。' ,'error');
	}
	if (trim($_POST['PaySupplier']) == '') {
		$InputError = 1;
		prnMsg( '付款单位不能为空', 'error');
	}
        if (trim($_POST['ContactsPhone']) == '') {
		$InputError = 1;
		prnMsg( '收款人电话不可为空', 'error');
	}
	if ($InputError !=1) {
		//$sql = "SELECT count(*) FROM ap_fee_invoice_pay_headers_all
			//	WHERE PayName=  "'.$_POST['PayName'].'" ';
         $sql = 'SELECT count(*) FROM ap_fee_pay_headers_all WHERE PayName= "'.$_POST['PayName'].'" ';

		$result = DB_query($sql,$db);
		$myrow = DB_fetch_row($result);
		if ( $myrow[0] > 0 ) {
			$InputError = 1;
			prnMsg( '付款单据已存在','error');
		}
		else {
			$sql = "INSERT INTO ap_fee_pay_headers_all (PayName,PaySupplier,PayPersonName,ContactsPhone,
                            PayAmount,PayRemark,PaymentDate,PayType,bankname,creationdate,createdby,lastupdatedate,lastupdatedby)
					VALUES ('" . $_POST['PayName'] ."','" . $_POST['PaySupplier'] ."',
					        '" . $_POST['PayPersonName'] ."',
							'" . $_POST['ContactsPhone'] ."',
							'" . $_POST['PayAmount'] ."',
							'" . $_POST['PayRemark'] ."',
							'" . $_POST['PaymentDate'] ."',
							'" . $_POST['PayType'] ."',
							'" . $_POST['bankname'] ."',
							  '" .Date('Y-m-d H:i:s') . "',
											 '" . $_SESSION['UserID'] . "',  '" .Date('Y-m-d H:i:s') . "',
											 '" . $_SESSION['UserID'] . "'
							)";
		}
		$msg = '费用付款单据已创建';
	}  
	if ($InputError!=1){
		if (is_array($sql)) {
			$result = DB_Txn_Begin($db);
			$tmpErr = '无法更新类型';
			$tmpDbg = _('The sql that failed was') . ':';
			foreach ($sql as $stmt ) {
				$result = DB_query($stmt,$db, $tmpErr,$tmpDbg,true);
				if(!$result) {
					$InputError = 1;
					break;
				}
			}
			if ($InputError!=1){
				$result = DB_Txn_Commit($db);
			} else {
				$result = DB_Txn_Rollback($db);
			}
		} else {
			$result = DB_query($sql,$db);
		}
		prnMsg($msg,'success');
	} 
	unset ($_POST['PayName']);
	unset ($_POST['PaySupplier']);
	unset ($_POST['PayPersonName']);
	unset ($_POST['ContactsPhone']); 
    unset ($_POST['PaymentDate']);
    unset ($_POST['PayAmount']);
    unset ($_POST['PayRemark']);  
	unset ($_POST['bankname']);  

} 


echo '<br />';

if (! isset($_GET['delete'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .  '">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	        $_POST['PayName']='';
		$_POST['PaySupplier']='';
		$_POST['PayPersonName']='';
		$_POST['ContactsPhone']='';
                $_POST['PaymentDate']='';
                $_POST['PayAmount']='';
                $_POST['PayRemark']='';
				 $_POST['bankname']='';
		echo '<table>';
	echo '<tr>	
		<td>' . '收款单号' . ':' . '</td>
		<td><input required="required" pattern="(?!^ *$)[^+<>-]{1,}" type="text" name="PayName" title="' . _('Cannot be blank or contains illegal characters') . '" placeholder="' . _('输入收款单号') . '" size="30" maxlength="30" value="' . $_POST['PayName'] . '" /></td>		
		</tr>';
      
	 echo '<tr>	
		<td>' . '收款单位' . ':' . '</td>
		<td><input required="required" pattern="(?!^ *$)[^+<>-]{1,}" type="text" name="PaySupplier" title="' . _('Cannot be blank or contains illegal characters') . '" placeholder="' . _('输入接收款项单位') . '" size="40" maxlength="40" value="' . $_POST['PaySupplier'] . '" /></td>		
		</tr>';
 echo '<tr>	
		<td>' . '收款人姓名' . ':' . '</td>
		<td><input required="required" pattern="(?!^ *$)[^+<>-]{1,}" type="text" name="PayPersonName" title="' . _('Cannot be blank or contains illegal characters') . '" placeholder="' . _('输入接收款项人员') . '" size="30" maxlength="30" value="' . $_POST['PayPersonName'] . '" /></td>		
		</tr>';
 echo '<tr>	
		<td>' . '收款人联系方式' . ':' . '</td>
		<td><input required="required" pattern="(?!^ *$)[^+<>]{1,}" type="text" name="ContactsPhone" title="' . _('Cannot be blank or contains illegal characters') . '" placeholder="' . _('输入电话或手机') . '" size="30" maxlength="30" value="' . $_POST['ContactsPhone'] . '" /></td>		
		</tr>';


echo	'<tr>
		<td>' . _('付款银行') . ':</td>';

// any internal department allowed
$sql = "SELECT bankname,bankaccount FROM ap_bank_alls ORDER by bankname";

$result=DB_query($sql, $db);
echo '<td><select name="bankname">';
while( $apbanknow = DB_fetch_array($result) ) {
	if (isset($_POST['bankname']) AND $_POST['bankname']==$apbanknow['bankname'] ){
		echo '<option selected="selected" value="' . $apbanknow['bankname']  . '">' .$apbanknow['bankname'] .'  ' .$apbanknow['bankaccount'] . '</option>';
	} else {
		echo '<option value="' . $apbanknow['bankname']  . '">' . $apbanknow['bankname']  . '  ' .$apbanknow['bankaccount'] .'</option>';
	}

}

echo '</select></td>
	</tr>';

echo	'<tr>
		<td>' . _('付款类型') . ':</td>';

// any internal department allowed
$sql = "SELECT paytype FROM ap_fee_pay_types ORDER by paytype";

$result=DB_query($sql, $db);
echo '<td><select name="PayType">';
while( $Salesmanrow = DB_fetch_array($result) ) {
	if (isset($_POST['PayType']) AND $_POST['PayType']==$Salesmanrow['paytype'] ){
		echo '<option selected="selected" value="' . $Salesmanrow['paytype']  . '">' .$Salesmanrow['paytype']  . '</option>';
	} else {
		echo '<option value="' . $Salesmanrow['paytype']  . '">' . $Salesmanrow['paytype']  . '</option>';
	}

}

echo '</select></td>
	</tr>';




 echo '<tr>	
		<td>' . '付款日期' . ':' . '</td>
		<td><input type="text" required="required" autofocus="autofocus" class="date" alt="' . 'Y/m/d' . '" name="PaymentDate" size="11" value="' .
    date("Y/m/d") . '" /></td> </tr>';
 
  echo '<tr>	
		<td>' . '付款金额' . ':' . '</td>
		<td><input required="required"  type="text"  class="number"  name="PayAmount" title="' . _('Cannot be blank or contains illegal characters') . '" placeholder="' . _('付款金额') . '" size="20" maxlength="30" value="' . $_POST['PayAmount'] . '" /></td>		
		</tr>';
  
   echo '<tr>	
		<td>' . '款项备注' . ':' . '</td>
		<td><textarea  name="PayRemark" cols="40" rows="1">' . $_POST['PayRemark'] . '</textarea></td>
</tr>';
	echo '</table>';

	//echo $_SESSION['DefaultDateFormat'];

	echo '<div class="centre">
			<input type="submit" name="Submit" value="' . _('Enter Information') . '" />
		</div>';

	echo '</div>
          </form>';

}
include('includes/footer.inc');
?>
