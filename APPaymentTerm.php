<?php

include('includes/session.inc');

$Title = _('供应商付款条件维护');

include('includes/header.inc');
include('includes/CountriesArray.php');

if (isset($_GET['SelectedLocation'])){
	$SelectedLocation = $_GET['SelectedLocation'];
} elseif (isset($_POST['SelectedLocation'])){
	$SelectedLocation = $_POST['SelectedLocation'];
}
//仓库只有：编码，名称，管理员，区域，容量
if (isset($_POST['submit'])) {
	$_POST['Managed']='Y';
	//initialise no input errors assumed initially before we test
	$InputError = 0;

	$_POST['term_code']=mb_strtoupper($_POST['term_code']);
	if( trim($_POST['term_code']) == '' ) {
		$InputError = 1;
		prnMsg( _('The location code may not be empty'), 'error');
	}
	if (isset($SelectedLocation) AND $InputError !=1) {

		/* Set the Managed field to 1 if it is checked, otherwise 0 */
		if(isset($_POST['Managed']) and $_POST['Managed'] == 'Y'){
			$_POST['Managed'] = 'Y';
		} else {
			$_POST['Managed'] = 'N';
		}

		$sql = "UPDATE ap_payment_terms SET term_code='" . $_POST['term_code'] . "',
									term_name='" . $_POST['term_name'] . "',
									after_payment='" . $_POST['after_payment'] . "',									
									managed='" . $_POST['managed'] . "'  
						WHERE term_code = '" . $SelectedLocation . "'";

		$ErrMsg = _('An error occurred updating the') . ' ' . $SelectedLocation . ' ' . _('location record because');
		$DbgMsg = _('The SQL used to update the location record was');

		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

		prnMsg( _('The location record has been updated'),'success');
		unset($_POST['term_code']);
		unset($_POST['term_name']);
		unset($_POST['after_payment']);
		unset($_POST['managed']);
		unset($SelectedLocation);
		unset($_POST['Contact']);
                unset($_POST['Stock']);
		unset($_POST['InternalRequest']);


	} elseif ($InputError !=1) {
		if($_POST['managed'] == 'on') {
			$_POST['managed'] = 'Y';
		} else {
			$_POST['managed'] = 'N';
		}
		if($_POST['InternalRequest'] == '') {
			$_POST['InternalRequest'] = 1;
		} else {
			$_POST['InternalRequest'] = 0;
		}
		 $v_date =time();
            $sql = "INSERT INTO ap_payment_terms (term_code,
										term_name,
										after_payment,
										managed,
										creation_date,
										created_by 
										)
						VALUES ('" . $_POST['term_code'] . "',
								'" . $_POST['term_name'] . "',
								'" . $_POST['after_payment'] ."',
								'" . $_POST['managed'] . "', 
								'" . $v_date . "',
								'" . $_SESSION['UserID'] . "' 
								)";

		$ErrMsg =  _('An error occurred inserting the new location record because');
		$DbgMsg =  _('The SQL used to insert the location record was');
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

		prnMsg( _('新付款条件被新增'),'success'); 
		unset($_POST['term_code']);
		unset($_POST['term_name']);
		unset($_POST['after_payment']); 
		unset($SelectedLocation); 

	}

} elseif (isset($_GET['delete'])) {
	$CancelDelete = 0;
 
				$sql= "SELECT COUNT(*) FROM po_headers_all
						WHERE term_code='" . $SelectedLocation . "'";
				$result = DB_query($sql,$db);
				$myrow = DB_fetch_row($result);
				if ($myrow[0]>0) {
					$CancelDelete = 1;
					prnMsg(_('不能被删除,该付款条件有采购单'),'warn');
				 
				}
 
 					 
			
		
	
	if (! $CancelDelete) {

		 
		$result = DB_query("DELETE FROM ap_payment_terms WHERE term_code='" . $SelectedLocation . "'",$db);

		prnMsg( _('Location') . ' ' . $SelectedLocation . ' ' . _('has been deleted') . '!', 'success');
		unset ($SelectedLocation);
	}  
	unset($SelectedLocation);
	unset($_GET['delete']);
}

if (!isset($SelectedLocation)) {

	$sql = "SELECT term_code,
				term_name,after_payment , 	
				managed
			FROM ap_payment_terms 
			";
	$result = DB_query($sql,$db);

	if (DB_num_rows($result)==0){
		prnMsg (_('无付款条件'),'error');
	}
	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/supplier.png" title="' .
			_('Inventory') . '" alt="" />' . ' ' . $Title . '</p>';

	echo '<table class="selection">';
	echo '<tr>
			<th>' . _('付款条件代号') . '</th>
			<th>' . _('付款条件名称') . '</th>
			<th>' . _('付款天数') . '</th> 
						<th>' . _('是否生效') . '</th>
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
			<td><a href="%sSelectedLocation=%s">' . _('Edit') . '</a></td>
			<td><a href="%sSelectedLocation=%s&amp;delete=1" onclick="return confirm(\'' . _('Are you sure you wish to delete this inventory location?') . '\');">' . _('Delete') . '</a></td>
			</tr>',
			$myrow['term_code'],
			$myrow['term_name'], 
                        $myrow['after_payment'],
			 
				$myrow['managed'],
			htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
			$myrow['term_code'],
			htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
			$myrow['term_code']);

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

		$sql = "SELECT term_code,
					term_name,
					after_payment, 
					managed 
				FROM ap_payment_terms
				WHERE term_code='" . $SelectedLocation . "'";

		$result = DB_query($sql, $db);
		$myrow = DB_fetch_array($result);

		$_POST['term_code'] = $myrow['term_code'];
		$_POST['term_name']  = $myrow['term_name'];
		$_POST['after_payment'] = $myrow['after_payment'];
		 
		$_POST['managed'] = $myrow['managed'];  

		echo '<input type="hidden" name="SelectedLocation" value="' . $SelectedLocation . '" />';
		echo '<input type="hidden" name="term_code" value="' . $_POST['term_code'] . '" />';
		echo '<table class="selection">';
		echo '<tr>
				<th colspan="2">' . _('Amend Location details') . '</th>
			</tr>';
		echo '<tr>
				<td>' . _('Location Code') . ':</td>
				<td>' . $_POST['term_code'] . '</td>
			</tr>';
	} else { //end of if $SelectedLocation only do the else when a new record is being entered
		if (!isset($_POST['term_code'])) {
			$_POST['term_code'] = '';
		}
		echo '<table class="selection">
				<tr>
					<th colspan="2"><h3>' . _('新付款条件资料') . '</h3></th>
				</tr>';
		echo '<tr>
				<td>' . _('付款条件代码') . ':</td>
				<td><input type="text" autofocus="autofocus" required="required" title="' . _('输入最多五个字符的仓库编码') . '" data-type="no-illegal-chars" name="term_code" value="' . $_POST['term_code'] . '" size="5" maxlength="5" /></td>
			</tr>';
	}
	if (!isset($_POST['term_name'])) {
		$_POST['term_name'] = '';
	}
	if (!isset($_POST['Contact'])) {
		$_POST['Contact'] = '';
	}
	if (!isset($_POST['after_payment'])) {
		$_POST['after_payment'] = '';
	}
	 
	if (!isset($_POST['managed'])) {
		$_POST['managed'] = 'Y';
	}
        if (!isset($_POST['Stock'])) {
		$_POST['Stock'] = 0;
	}

	echo '<tr>
			<td>' .  _('付款条件名称') . ':' . '</td>
			<td><input type="text" name="term_name" required="required" value="'. $_POST['term_name'] . '" title="' . _('Enter the inventory location name this could be either a warehouse or a factory') . '"  size="30" maxlength="50" /></td>
		 
			 
		</tr>
		<tr>
			<td>' .  _('天数') . ':' . '</td>
			<td   ><input type="text" name="after_payment" class="number" value="' . $_POST['after_payment'] . '" size="15" maxlength="60" /></td>
		</tr>
		 ';
		echo '<tr>
			
		</tr>';
		
    echo '<tr>
			<td>' . _('是否生效?') . ':</td>
			<td><select name="managed">';
	if ($_POST['managed']=='Y'){
		echo '<option selected="selected" value="Y">' . _('Yes') . '</option>';
	} else {
		echo '<option value="Y">' . _('Yes') . '</option>';
	}
	if ($_POST['managed']=='N'){
		echo '<option selected="selected" value="N">' . _('No') . '</option>';
	} else {
		echo '<option value="N">' . _('No') . '</option>';
	}
		
	echo '</table>
		<br />
		<div class="centre">
			<input type="submit" name="submit" value="' .  _('Enter Information') . '" />
		</div>
        </div>
		</form>';

} //end if record deleted no point displaying form to add record

include('includes/footer.inc');
?>
