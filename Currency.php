<?php
/* $Id: sf_item_category.php 6495 2013-12-11 23:05:30Z rchacon $*/

include('includes/session.inc');

$Title = _('币别维护');

include('includes/header.inc');
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' .
		_('Search') . '" alt="" />' . ' ' . $Title . '</p>';

if ( isset($_GET['SelectedMeasureID']) )
	$SelectedMeasureID = $_GET['SelectedMeasureID'];
elseif (isset($_POST['SelectedMeasureID']))
	$SelectedMeasureID = $_POST['SelectedMeasureID'];

if (isset($_POST['Submit'])) {

	//initialise no input errors assumed initially before we test

	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	if (ContainsIllegalCharacters($_POST['MeasureName'])) {
		$InputError = 1;
		prnMsg( _('请输入正确的币别简称') ,'error');
	}
	if (trim($_POST['MeasureName']) == '') {
		$InputError = 1;
		prnMsg( _('币别简称不能为空'), 'error');
	}

	if (isset($_POST['SelectedMeasureID']) AND $_POST['SelectedMeasureID']!='' AND $InputError !=1) {


		/*SelectedMeasureID could also exist if submit had not been clicked this code would not run in this case cos submit is false of course  see the delete code below*/
		// Check the name does not clash
		$sql = "SELECT count(*) FROM currencies
				WHERE currency_id <> '" . $SelectedMeasureID ."'
				AND currabrev = '" . $_POST['MeasureName'] . "'";
		$result = DB_query($sql,$db);
		 
		$myrow = DB_fetch_row($result);
		if ( $myrow[0] > 0 ) {
			$InputError = 1;
			prnMsg( _('币别简称有重复，请重新输入'),'error');
		} else {
			// Get the old name and check that the record still exist neet to be very carefull here
			// idealy this is one of those sets that should be in a stored procedure simce even the checks are
			// relavant
			$sql = "SELECT currabrev,currency FROM currencies
				WHERE currency_id = '" . $SelectedMeasureID . "'";
			$result = DB_query($sql,$db);
			if ( DB_num_rows($result) != 0 ) {
				// This is probably the safest way there is
				$myrow = DB_fetch_row($result);
				$OldMeasureName = $myrow[0];
				$sql = array();
				$sql[] = "UPDATE currencies
					SET currabrev='" . $_POST['MeasureName'] . "',
                    
                    currency='" . $_POST['MeasureName2'] . "'
					WHERE currabrev ".LIKE." '".$OldMeasureName."'";
				$sql[] = "UPDATE currencies
					SET currabrev='" . $_POST['MeasureName'] . "',
                    
                    currency='" . $_POST['MeasureName2'] . "'
					WHERE currabrev ".LIKE." '".$OldMeasureName."'";
			} else {
				$InputError = 1;
				prnMsg( _('币别简称不存在.'),'error');
			}
		}
		$msg = _('币别简称已经更新');
	} elseif ($InputError !=1) {
		/*SelectedMeasureID is null cos no item selected on first time round so must be adding a record*/
		$sql = "SELECT count(*) FROM currencies
				WHERE currabrev = '".$_POST['MeasureName'] ."'";
		$result = DB_query($sql,$db);
		 
		$myrow = DB_fetch_row($result);
		if ( $myrow[0] > 0 ) {
			$InputError = 1;
			prnMsg( _('币别简称有重复，请重新输入'),'error');
		} else {
			$sql = "INSERT INTO currencies (currabrev,currency)
					VALUES ('" . $_POST['MeasureName'] ."','" . $_POST['MeasureName2'] ."')";
		}
		$msg = _('新的币别维护建立成功');
	}

	if ($InputError!=1){
		//run the SQL from either of the above possibilites
		if (is_array($sql)) {
			$result = DB_Txn_Begin($db);
			$tmpErr = _('不能更新币别全称');
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
	unset ($SelectedMeasureID);
	unset ($_POST['SelectedMeasureID']);
	unset ($_POST['MeasureName']);
   
    unset ($_POST['MeasureName2']);

} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button
// PREVENT DELETES IF DEPENDENT RECORDS IN 'sf_item_no'
	// Get the original name of the unit of measure the ID is just a secure way to find the unit of measure
	$sql = "SELECT currabrev,currency FROM currencies
		WHERE currency_id = '" . $SelectedMeasureID . "'";
	$result = DB_query($sql,$db);
	if ( DB_num_rows($result) == 0 ) {
		// This is probably the safest way there is
		prnMsg( _('不能删除该币别简称，因为币别简称已经不存在'),'warn');
	} else {
		$myrow = DB_fetch_row($result);
		$OldMeasureName = $myrow[0];
		$sql= "SELECT COUNT(*) FROM currencies WHERE currency ".LIKE." '" . $OldMeasureName . "'";
		$result = DB_query($sql,$db);
		$myrow = DB_fetch_row($result);
		if ($myrow[0]>0) {
			prnMsg( _('不能删除这个币别全称，因为有属于该币别全称'),'warn');
			echo '<br />' . _('There are') . ' ' . $myrow[0] . ' ' . _('个属于这个币别全称') . '</font>';
		} else {
			$sql="DELETE FROM currencies WHERE currabrev ".LIKE."'" . $OldMeasureName . "'";
			$result = DB_query($sql,$db);
			prnMsg( $OldMeasureName . ' ' . _('币别全称删除成功') . '!','success');
		}
	} //end if account group used in GL accounts
	unset ($SelectedMeasureID);
	unset ($_GET['SelectedMeasureID']);
	unset($_GET['delete']);
	unset ($_POST['SelectedMeasureID']);
	unset ($_POST['MeasureID']);
	unset ($_POST['MeasureName']);
   
    unset ($_POST['MeasureName2']);
}

 if (!isset($SelectedMeasureID)) {

/* An unit of measure could be posted when one has been edited and is being updated
  or GOT when selected for modification
  SelectedMeasureID will exist because it was sent with the page in a GET .
  If its the first time the page has been displayed with no parameters
  then none of the above are true and the list of account groups will be displayed with
  links to delete or edit each. These will call the same page again and allow update/input
  or deletion of the records*/

	$sql = "SELECT currency_id,currabrev,
			currency
			FROM currencies
			ORDER BY currency_id";

	$ErrMsg = _('不能获取币别全称');
	$result = DB_query($sql,$db,$ErrMsg);

	echo '<table class="selection">
			<tr>
				<th bgcolor="#87CEFA" class="ascending">' . _('币别简称') . '</th>
                
                <th bgcolor="#87CEFA" class="ascending">' . _('币别全称') . '</th>
                 <th bgcolor="#87CEFA">' . _('编辑') . '</th>
               <th bgcolor="#87CEFA">' . _('删除') . '</th>
			</tr>';

	$k=0; //row colour counter
	while ($myrow = DB_fetch_row($result)) {

		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k++;
		}

		echo '<td>' . $myrow[1] . '</td>';
        
        echo '<td>' . $myrow[2] . '</td>';
		echo '<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?SelectedMeasureID=' . $myrow[0] . '">' . _('Edit') . '</a></td>';
		echo '<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?SelectedMeasureID=' . $myrow[0] . '&amp;delete=1" onclick="return confirm(\'' . _('是否要删除这个币别全称类型?') . '\');">' . _('Delete')  . '</a></td>';
		echo '</tr>';

	} //END WHILE LIST LOOP
	echo '</table><br />';
} //end of ifs and buts!


if (isset($SelectedMeasureID)) {
	echo '<div class="centre">
			<a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' . _('查看币别全称') . '</a>
		</div>';
}

echo '<br />';

if (! isset($_GET['delete'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .  '">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if (isset($SelectedMeasureID)) {
		//editing an existing section

		$sql = "SELECT currency_id,
				currabrev,currency
				FROM currencies
				WHERE currency_id='" . $SelectedMeasureID . "'";

		$result = DB_query($sql, $db);
		if ( DB_num_rows($result) == 0 ) {
			prnMsg( _('找不到需要的币别全称，请重试.'),'warn');
			unset($SelectedMeasureID);
		} else {
			$myrow = DB_fetch_array($result);

			$_POST['MeasureID'] = $myrow['currency_id'];
			$_POST['MeasureName']  = $myrow['currabrev'];
            
            $_POST['MeasureName2']  = $myrow['currency'];

			echo '<input type="hidden" name="SelectedMeasureID" value="' . $_POST['MeasureID'] . '" />';
			echo '<table class="selection">';
		}

	}  else {
		$_POST['MeasureName']='';
		echo '<table>';
	}
	echo '<tr>
		<td bgcolor="#87CEFA">' . _('币别简称') . ':' . '</td>
		<td><input required="required" pattern="(?!^ *$)[^+<>-]{1,}" type="text" name="MeasureName" title="'._('Cannot be blank or contains illegal characters').'" placeholder="'._('请输入名称').'" size="30" maxlength="30" value="' . $_POST['MeasureName'] . '" /></td>
		</tr>';
        
      
        
     echo   '<tr>
		<td bgcolor="#87CEFA">' . _('币别全称') . ':' . '</td>
		<td><input " pattern="(?!^ *$)[^+<>-]{1,}" type="text" name="MeasureName2" title="'._('Cannot be blank or contains illegal characters').'" placeholder="'._('请输入币别全称').'" size="30" maxlength="30" value="' . $_POST['MeasureName2'] . '" /></td>
		</tr>';
        
	echo '</table>';

	echo '<div class="centre">
			<input type="submit" name="Submit" value="' . _('Enter Information') . '" />
		</div>';

	echo '</div>
          </form>';

} //end if record deleted no point displaying form to add record

include('includes/footer.inc');
?>
