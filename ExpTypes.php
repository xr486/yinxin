<?php

include('includes/session.inc');

$Title = _('日常费用类型设置');

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

	$_POST['exp_type_name']=mb_strtoupper($_POST['exp_type_name']);
	if( trim($_POST['exp_type_name']) == '' ) {
		$InputError = 1;
		prnMsg( _('费用类型名称不可为空'), 'error');
	}
	if (isset($SelectedLocation) AND $InputError !=1) {

		 

		$sql = "UPDATE fin_exp_types SET exp_description='" . $_POST['exp_description'] . "',
									enableflag='" . $_POST['enableflag'] . "' 
						WHERE exp_type_name = '" . $SelectedLocation . "' 
						 ";
          
		$ErrMsg = _('An error occurred updating the') . ' ' . $SelectedLocation . ' ' . _('location record because');
		$DbgMsg = _('The SQL used to update the location record was');

		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

		prnMsg( _('The  record has been updated'),'success');
		unset($_POST['exp_type_name']);
		unset($_POST['exp_description']); 
		unset($_POST['enableflag']); 
		unset($SelectedLocation); 


	} elseif ($InputError !=1) {
		if($_POST['enableflag'] == 'Y') {
			$_POST['enableflag'] = 'Y';
		} else {
			$_POST['enableflag'] = 'N';
		}
		 
            $sql = "INSERT INTO fin_exp_types ( type_code, 
			                           exp_type_name,
										exp_description ,
										enableflag
										 
										)
						VALUES ( '" . $_POST['type_code'] . "',
						       '" . $_POST['exp_type_name'] . "',
								'" . $_POST['exp_description'] . "',
								'" . $_POST['enableflag'] . "'
								
								
								)";

		$ErrMsg =  _('An error occurred inserting the new location record because');
		$DbgMsg =  _('The SQL used to insert the location record was');
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

		prnMsg( _('新费用类型新建成功'),'success'); 
		unset($_POST['exp_type_name']);
		unset($_POST['exp_description']);
		unset($_POST['enableflag']);   
		unset($SelectedLocation); 

	}

} elseif (isset($_GET['delete'])) {
	$CancelDelete = 0;
   
 					$sql= "SELECT COUNT(*) FROM  fin_bank_transaction_headers_all
 								WHERE transaction_type='" . $SelectedLocation . "'   ";
 						$result = DB_query($sql,$db);
 						$myrow = DB_fetch_row($result);
 						if ($myrow[0]>0) {
 							$CancelDelete = 1;
 							prnMsg( _('该费用类型有交易,不能删除'),'warn'); 
 						} 
 				 
			
		
	
	if (! $CancelDelete) {

		 
		$result = DB_query("DELETE FROM fin_exp_types WHERE exp_type_name='" . $SelectedLocation ."'",$db);

		prnMsg( _('费用类型') . ' ' . $SelectedLocation . ' ' . _('has been deleted') . '!', 'success');
		unset ($SelectedLocation);
	}  
	unset($SelectedLocation);
	unset($_GET['delete']);
}

if (!isset($SelectedLocation)) {

	$sql = "SELECT type_code,exp_type_name,
				exp_description,			
				enableflag
			FROM fin_exp_types 
			";
	$result = DB_query($sql,$db);

	if (DB_num_rows($result)==0){
		prnMsg (_('查询不到资料'),'error');
	}
	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/supplier.png" title="' .
			_('Inventory') . '" alt="" />' . ' ' . $Title . '</p>';

	echo '<table class="selection">';
	echo '<tr><th>' . _('类别') . '</th>
			<th>' . _('费用类型') . '</th>
			<th>' . _('费用类型说明') . '</th> 
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
			$myrow['type_code'],
			$myrow['exp_type_name'],
			$myrow['exp_description'], 
			$myrow['enableflag'],
			htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
			$myrow['exp_type_name'],
			htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
			$myrow['exp_type_name']);

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

		$sql = "SELECT type_code,exp_type_name,
					exp_description,  
					enableflag
				FROM fin_exp_types
				WHERE exp_type_name='" . $SelectedLocation . "'
				";

		$result = DB_query($sql, $db);
		$myrow = DB_fetch_array($result);

		$_POST['type_code'] = $myrow['type_code'];
		$_POST['exp_type_name'] = $myrow['exp_type_name'];
		$_POST['exp_description']  = $myrow['exp_description']; 
		$_POST['enableflag'] = $myrow['enableflag']; 


		echo '<input type="hidden" name="SelectedLocation" value="' . $SelectedLocation . '" />';
		echo '<input type="hidden" name="exp_type_name" value="' . $_POST['exp_type_name'] . '" />';
		echo '<table class="selection">';
		echo '<tr>
				<th colspan="2">' . _('修改费用类别') . '</th>
			</tr>';

			   echo '<tr>
			<td>' . _('费用类别') . ':</td>
			<td><select required="required" name="type_code">';
	if ($_POST['type_code']=='收入'){
		echo '<option selected="selected" value="收入">' . _('收入') . '</option>';
		echo '<option value="支出">' . _('支出') . '</option>';
	} else {
		echo '<option selected="selected" value="支出">' . _('支出') . '</option>';
		echo '<option value="收入">' . _('收入') . '</option>';
	}

		echo '<tr>
				<td>' . _('费用类型名称') . ':</td>
				<td>' . $_POST['exp_type_name'] . '</td>
			</tr>';
	} else { //end of if $SelectedLocation only do the else when a new record is being entered
		if (!isset($_POST['exp_type_name'])) {
			$_POST['exp_type_name'] = '';
		}
		echo '<table class="selection">
				<tr>
					<th colspan="2"><h3>' . _('新增费用类型') . '</h3></th>
				</tr>';
				 echo '<tr>
			<td>' . _('费用类别') . ':</td>
			<td><select required="required" name="type_code">';
	if ($_POST['type_code']=='收入'){
		echo '<option selected="selected" value="收入">' . _('收入') . '</option>';
		echo '<option value="支出">' . _('支出') . '</option>';
	} else {
		echo '<option selected="selected" value="支出">' . _('支出') . '</option>';
		echo '<option value="收入">' . _('收入') . '</option>';
	}

		echo '<tr>
				<td>' . _('费用类型名称') . ':</td>
				<td><input type="text" autofocus="autofocus" required="required" title="' . _('输入最多20个字符') . '" data-type="no-illegal-chars" name="exp_type_name" value="' . $_POST['exp_type_name'] . '" size="20" maxlength="20" /></td>
			</tr>';
	}
	if (!isset($_POST['exp_description'])) {
		$_POST['exp_description'] = '';
	}
	
	 

	echo '<tr>
			<td>' .  _('说明') . ':' . '</td>
			<td><input type="text" name="exp_description" required="required" value="'. $_POST['exp_description'] . '" title="' . _('输入类型说明') . '" namesize="30" maxlength="100" /></td>
		 </tr> ';
		echo '<tr>
			
		</tr>';
		
    echo '<tr>
			<td>' . _('是否生效?') . ':</td>
			<td><select required="required" name="enableflag">';
	if ($_POST['enableflag']=='N'){
		echo '<option selected="selected" value="N">' . _('No') . '</option>';
		echo '<option value="Y">' . _('Yes') . '</option>';
	} else {
		echo '<option selected="selected" value="Y">' . _('Yes') . '</option>';
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
