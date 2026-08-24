<?php

include('includes/session.inc');

$Title = _('仓库维护');

include('includes/header.inc');
include('includes/CountriesArray.php');

if (isset($_GET['SelectedLocation'])){
	$SelectedLocation = $_GET['SelectedLocation'];
} elseif (isset($_POST['SelectedLocation'])){
	$SelectedLocation = $_POST['SelectedLocation'];
}
//仓库只有：编码，名称，管理员，区域，容量
if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	$_POST['LocCode']=mb_strtoupper($_POST['LocCode']);
	if( trim($_POST['LocCode']) == '' ) {
		$InputError = 1;
		prnMsg( _('The location code may not be empty'), 'error');
	}
	if (isset($SelectedLocation) AND $InputError !=1) {

		/* Set the managed field to 1 if it is checked, otherwise 0 */
		

		$sql = "UPDATE locations SET loccode='" . $_POST['LocCode'] . "',
									locationname='" . $_POST['LocationName'] . "',
									address='" . $_POST['address'] . "',									
									managed='" . $_POST['managed'] . "',
									tel='" . $_POST['tel'] . "',
									fax='" . $_POST['fax'] . "',
									email='" . $_POST['email'] . "',
                                    stock='" . $_POST['Stock'] . "',
									wip_issue_flag='" . $_POST['wip_issue_flag'] . "',
									baofei_flag='" . $_POST['baofei_flag'] . "',
									contact='" . $_POST['Contact'] . "'
						WHERE loccode = '" . $SelectedLocation . "'";
     //echo $sql;
		$ErrMsg = _('An error occurred updating the') . ' ' . $SelectedLocation . ' ' . _('location record because');
		$DbgMsg = _('The SQL used to update the location record was');

		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

		prnMsg( _('The location record has been updated'),'success');
		unset($_POST['LocCode']);
		unset($_POST['LocationName']);
		unset($_POST['address']);
		unset($_POST['DelAdd2']);
		unset($_POST['DelAdd3']);
		unset($_POST['DelAdd4']);
		unset($_POST['DelAdd5']);
		unset($_POST['DelAdd6']);
		unset($_POST['tel']);
		unset($_POST['fax']);
		unset($_POST['email']);
		unset($_POST['TaxProvince']);
		unset($_POST['managed']);
		unset($_POST['CashSaleCustomer']); 
		unset($SelectedLocation);
		unset($_POST['Contact']);
                unset($_POST['Stock']);
		unset($_POST['InternalRequest']);


	} elseif ($InputError !=1) {
	 
		if($_POST['InternalRequest'] == 'Yes') {
			$_POST['InternalRequest'] = 1;
		} else {
			$_POST['InternalRequest'] = 0;
		}
            $sql = "INSERT INTO locations (loccode,
										locationname,
										address,managed,
										tel,
										fax,
										email,
										contact,wip_issue_flag,
                                         stock,baofei_flag
										)
						VALUES ('" . $_POST['LocCode'] . "',
								'" . $_POST['LocationName'] . "',
								'" . $_POST['address'] ."','" . $_POST['managed'] ."',
								'" . $_POST['tel'] . "',
								'" . $_POST['fax'] . "',
								'" . $_POST['email'] . "',
								'" . $_POST['Contact'] . "',
								'" . $_POST['wip_issue_flag'] . "',
                                '" . $_POST['Stock'] . "',
                                '" . $_POST['baofei_flag'] . "'
								)";

		$ErrMsg =  _('An error occurred inserting the new location record because');
		$DbgMsg =  _('The SQL used to insert the location record was');
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

		prnMsg( _('The new location record has been added'),'success'); 
		unset($_POST['LocCode']);
		unset($_POST['LocationName']);
		unset($_POST['address']);
		unset($_POST['tel']);
		unset($_POST['fax']);
		unset($_POST['email']);
		unset($_POST['TaxProvince']);
		unset($_POST['CashSaleCustomer']);  
		unset($SelectedLocation);
		unset($_POST['Contact']);
		unset($_POST['InternalRequest']);
                unset($_POST['Stock']);

	}

} elseif (isset($_GET['delete'])) {
	$CancelDelete = 0;
 
				$sql= "SELECT COUNT(*) FROM inv_onhand_quantity_all
						WHERE subinventory_code='" . $SelectedLocation . "'";
				$result = DB_query($sql,$db);
				$myrow = DB_fetch_row($result);
				if ($myrow[0]>0) {
					$CancelDelete = 1;
					prnMsg(_('不能被删除,该仓库有库存'),'warn');
				 
				}

					$sql= "SELECT COUNT(*) FROM po_lines_all
							WHERE SUBINVENTORY_CODE='" . $SelectedLocation . "'";
					$result = DB_query($sql,$db);
					$myrow = DB_fetch_row($result);
					if ($myrow[0]>0) {
						$CancelDelete = 1;
						prnMsg(_('已用于采购，不可删除'),'warn');
	 
					}
 					 
 					$sql= "SELECT COUNT(*) FROM inv_transactions_all
 								WHERE subinventory_from='" . $SelectedLocation . "'";
 						$result = DB_query($sql,$db);
 						$myrow = DB_fetch_row($result);
 						if ($myrow[0]>0) {
 							$CancelDelete = 1;
 							prnMsg( _('该仓库已有交易,不能删除'),'warn'); 
 						} 
 				 
			
		
	
	if (! $CancelDelete) {

		 
		$result = DB_query("DELETE FROM locations WHERE loccode='" . $SelectedLocation . "'",$db);

		prnMsg( _('Location') . ' ' . $SelectedLocation . ' ' . _('has been deleted') . '!', 'success');
		unset ($SelectedLocation);
	}  
	unset($SelectedLocation);
	unset($_GET['delete']);
}

if (!isset($SelectedLocation)) {

	$sql = "SELECT loccode,
				locationname,contact,tel,fax,email,address as address,stock,wip_issue_flag,			
				managed,baofei_flag
			FROM locations 
			";
	$result = DB_query($sql,$db);

	if (DB_num_rows($result)==0){
		prnMsg (_('There are no locations that match up with a tax province record to display. Check that tax provinces are set up for all dispatch locations'),'error');
	}
	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/supplier.png" title="' .
			_('Inventory') . '" alt="" />' . ' ' . $Title . '</p>';

	echo '<table class="selection">';
	echo '<tr>
			<th bgcolor="#87CEFA">' . _('简称') . '</th>
			<th bgcolor="#87CEFA">' . _('全称') . '</th>
			<th bgcolor="#87CEFA">' . _('管理员') . '</th>
                        <th bgcolor="#87CEFA">' . _('地址') . '</th>
						<th bgcolor="#87CEFA">' . _('电话') . '</th>
						<th bgcolor="#87CEFA">' . _('传真') . '</th> 
                        <th bgcolor="#87CEFA">' . _('容量') . '</th>
                        <th bgcolor="#87CEFA">' . _('是否现场仓') . '</th>
						<th bgcolor="#87CEFA">' . _('是否生效') . '</th>
						<th bgcolor="#87CEFA">' . _('是否报废仓') . '</th>
                        <th>' . _('编辑') . '</th>
                        <th>' . _('删除') . '</th>
		</tr>';

$k=0; //row colour counter
while ($myrow = DB_fetch_array($result)) {
	if ($k==1){
		echo '<tr class="EvenTableRows">';
		$k=0;
	} else {
		echo '<tr class="OddTableRows">';
		$k=1;
	}
	printf('<td>%s</td>
			<td>%s</td>
			<td>%s</td>
                        <td>%s</td>
                        <td>%s</td>
						<td>%s</td>
						<td>%s</td>
                        <td>%s</td>
						<td>%s</td> 
						<td>%s</td>
			<td><a href="%sSelectedLocation=%s">' . _('Edit') . '</a></td>
			<td><a href="%sSelectedLocation=%s&amp;delete=1" onclick="return confirm(\'' . _('Are you sure you wish to delete this inventory location?') . '\');">' . _('Delete') . '</a></td>
			</tr>',
			$myrow['loccode'],
			$myrow['locationname'],
			$myrow['contact'],
                        $myrow['address'],
				$myrow['tel'],
				$myrow['fax'], 
                        $myrow['stock'],
                        $myrow['wip_issue_flag'],
				$myrow['managed'],
				$myrow['baofei_flag'],
			htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
			$myrow['loccode'],
			htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
			$myrow['loccode']);

	}
	//END WHILE LIST LOOP
	echo '</table>';
}

//end of ifs and buts!

echo '<br />';
if (isset($SelectedLocation)) {
	echo '<a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' . _('Review Records') . '</a>';
}
echo '<br />';

if (!isset($_GET['delete'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if (isset($SelectedLocation)) {
		//editing an existing Location
		echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/supplier.png" title="' .
			_('Inventory') . '" alt="" />' . ' ' . $Title . '</p>';

		$sql = "SELECT loccode,
					locationname,
					address,
					contact,
					fax,
					tel,
					email,
                    stock,
					taxprovinceid,
					cashsalecustomer, 
					managed,wip_issue_flag,
					internalrequest,baofei_flag
				FROM locations
				WHERE loccode='" . $SelectedLocation . "'";

		$result = DB_query($sql, $db);
		$myrow = DB_fetch_array($result);

		$_POST['LocCode'] = $myrow['loccode'];
		$_POST['LocationName']  = $myrow['locationname'];
		$_POST['address'] = $myrow['address'];
		$_POST['Contact'] = $myrow['contact'];
		$_POST['tel'] = $myrow['tel'];
		$_POST['fax'] = $myrow['fax'];
		$_POST['email'] = $myrow['email'];
		$_POST['wip_issue_flag'] = $myrow['wip_issue_flag'];
		$_POST['TaxProvince'] = $myrow['taxprovinceid'];
		$_POST['CashSaleCustomer'] = $myrow['cashsalecustomer']; 
		$_POST['managed'] = $myrow['managed'];
		$_POST['InternalRequest'] = $myrow['internalrequest'];
                $_POST['Stock'] = $myrow['stock'];
 $_POST['baofei_flag'] = $myrow['baofei_flag'];

		echo '<input type="hidden" name="SelectedLocation" value="' . $SelectedLocation . '" />';
		echo '<input type="hidden" name="LocCode" value="' . $_POST['LocCode'] . '" />';
		echo '<table class="selection">';
		echo '<tr>
				<th colspan="2">' . _('Amend Location details') . '</th>
			</tr>';
		echo '<tr>
				<td bgcolor="#87CEFA">' . _('Location Code') . ':</td>
				<td>' . $_POST['LocCode'] . '</td>
			</tr>';
	} else { //end of if $SelectedLocation only do the else when a new record is being entered
		if (!isset($_POST['LocCode'])) {
			$_POST['LocCode'] = '';
		}
		echo '<table class="selection">
				<tr>
					<th colspan="2"><h3>' . _('新建仓库资料') . '</h3></th>
				</tr>';
		echo '<tr>
				<td bgcolor="#87CEFA">' . _('仓库简称') . ':</td>
				<td><input type="text" autofocus="autofocus" required="required"  data-type="no-illegal-chars" name="LocCode" value="' . $_POST['LocCode'] . '" size="21" maxlength="20" /><span style="color:red">*</span></td>
			</tr>';
	}
	if (!isset($_POST['LocationName'])) {
		$_POST['LocationName'] = '';
	}
	if (!isset($_POST['Contact'])) {
		$_POST['Contact'] = '';
	}
	if (!isset($_POST['address'])) {
		$_POST['address'] = '';
	}
	if (!isset($_POST['DelAdd2'])) {
		$_POST['DelAdd2'] = '';
	}
	if (!isset($_POST['DelAdd3'])) {
		$_POST['DelAdd3'] = '';
	}
	if (!isset($_POST['DelAdd4'])) {
		$_POST['DelAdd4'] = '';
	}
	if (!isset($_POST['DelAdd5'])) {
		$_POST['DelAdd5'] = '';
	}
	if (!isset($_POST['DelAdd6'])) {
		$_POST['DelAdd6'] = '';
	}
	if (!isset($_POST['tel'])) {
		$_POST['tel'] = '';
	}
	if (!isset($_POST['fax'])) {
		$_POST['fax'] = '';
	}
	if (!isset($_POST['email'])) {
		$_POST['email'] = '';
	}
	if (!isset($_POST['CashSaleCustomer'])) {
		$_POST['CashSaleCustomer'] = '';
	} 
	 
        if (!isset($_POST['Stock'])) {
		$_POST['Stock'] = 0;
	}

	echo '<tr>
			<td bgcolor="#87CEFA">' .  _('全称') . ':' . '</td>
			<td><input type="text" name="LocationName" required="required" value="'. $_POST['LocationName'] . '" title="' . _('Enter the inventory location name this could be either a warehouse or a factory') . '" namesize="21" maxlength="50" /><span style="color:red">*</span></td>
		 
			<td bgcolor="#87CEFA">' . _('管理员') . ':' . '</td>
			<td><input type="text" name="Contact" required="required" value="' . $_POST['Contact'] . '" title="' . _('Enter the name of the responsible person to contact for this inventory location') . '" size="21" maxlength="30" /><span style="color:red">*</span></td>
		</tr>
		<tr>
			<td bgcolor="#87CEFA">' .  _('地址') . ':' . '</td>
			<td  colspan="3"><input type="text" name="address" value="' . $_POST['address'] . '" size="60" maxlength="60" /></td>
		</tr>
		<tr>
			<td bgcolor="#87CEFA">' .  _('电话') . ':' . '</td>
			<td><input type="text" name="tel" value="' . $_POST['tel'] . '" size="21" maxlength="40" /></td>
			<td bgcolor="#87CEFA">' .  _('传真') . ':' . '</td>
			<td><input type="text" name="fax" value="' . $_POST['fax'] . '" size="21" maxlength="40" /></td>
		</tr>
		<tr>
			
		 
		 
			<td bgcolor="#87CEFA">' .  _('容量') . ':' . '</td>
			<td bgcolor="#87CEFA"><input type="text" name="Stock"  value="' . $_POST['Stock'] . '" size="21" maxlength="30" title="' . _('仓库的容量') . '" /></td>
		';
	 
		
   
	   echo ' 
		<td bgcolor="#87CEFA">' . _('是否现场仓') . ':</td>
		<td><select required="required" name="wip_issue_flag">';
if ($_POST['wip_issue_flag']=='Y'){
	echo '<option selected="selected" value="Y">' . _('是') . '</option>';
	echo '<option value="N">' . _('否') . '</option>';
} else {
 	echo '<option selected="selected" value="N">' . _('否') . '</option>';
	echo '<option value="Y">' . _('是') . '</option>';
}
echo '</select><span style="color:red">*</span></td> ';
 echo ' </tr><td bgcolor="#87CEFA">' . _('是否报废仓') . ':</td>
		<td><select required="required" name="baofei_flag">';
if ($_POST['baofei_flag']=='Y'){
	echo '<option selected="selected" value="Y">' . _('是') . '</option>';
	echo '<option value="N">' . _('否') . '</option>';
} else {
 	echo '<option selected="selected" value="N">' . _('否') . '</option>';
	echo '<option value="Y">' . _('是') . '</option>';
}
echo '</select><span style="color:red">*</span></td> ';
	
	echo ' 
		<td bgcolor="#87CEFA">' . _('是否生效') . ':</td>
		<td><select required="required" name="managed">';
if ($_POST['managed']=='Y'){
	echo '<option selected="selected" value="Y">' . _('是') . '</option>';
	echo '<option value="N">' . _('否') . '</option>';
} else {
 	echo '<option selected="selected" value="N">' . _('否') . '</option>';
	echo '<option value="Y">' . _('是') . '</option>';
}
echo '</select><span style="color:red">*</span></td>
	</tr>';

		
	echo '</table>
		<br />
		<div class="centre">
			<input type="submit" name="submit" value="' .  _('确认保存') . '" />
		</div>
        </div>
		</form>';

} //end if record deleted no point displaying form to add record

include('includes/footer.inc');
?>
