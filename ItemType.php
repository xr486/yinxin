<?php
/* $Id: sf_item_category.php 6495 2013-12-11 23:05:30Z rchacon $*/

include('includes/session.inc');

$Title = _('材料分类维护');

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

	if (ContainsIllegalCharacters($_POST['type_name'])) {
		$InputError = 1;
		prnMsg( _('请输入正确的字符') ,'error');
	}
	if (trim($_POST['type_name']) == '') {
		$InputError = 1;
		prnMsg( _('材料分类不能为空'), 'error');
	}

	if (isset($_POST['SelectedMeasureID']) AND $_POST['SelectedMeasureID']!='' AND $InputError !=1) {


		/*SelectedMeasureID could also exist if submit had not been clicked this code would not run in this case cos submit is false of course  see the delete code below*/
		// Check the name does not clash
		$sql = "SELECT count(*) FROM sf_item_type
				WHERE item_type <> '" . $SelectedMeasureID ."'
				AND type_name ".LIKE." '" . $_POST['type_name'] . "'";
		$result = DB_query($sql,$db);
		$myrow = DB_fetch_row($result);
		if ( $myrow[0] > 0 ) {
			$InputError = 1;
			prnMsg( _('材料分类有重复，请重新输入'),'error');
		} else {
			// Get the old name and check that the record still exist neet to be very carefull here
			// idealy this is one of those sets that should be in a stored procedure simce even the checks are
			// relavant
			$sql = "SELECT type_name FROM sf_item_type
				WHERE item_type = '" . $SelectedMeasureID . "'";
			$result = DB_query($sql,$db);
			if ( DB_num_rows($result) != 0 ) {
				// This is probably the safest way there is
				$myrow = DB_fetch_row($result);
				$Oldtype_name = $myrow[0];
				$sql = array();
				$sql[] = "UPDATE sf_item_type
					SET type_name='" . $_POST['type_name'] . "'
					WHERE type_name ".LIKE." '".$Oldtype_name."'";
				$sql[] = "UPDATE sf_item_type
					SET item_type='" . $_POST['item_type'] . "'
					WHERE item_type ".LIKE." '" . $Oldtype_name . "'";
			} else {
				$InputError = 1;
				prnMsg( _('材料分类不存在.'),'error');
			}
		}
		$msg = _('材料分类已经更新');
	} elseif ($InputError !=1) {
		/*SelectedMeasureID is null cos no item selected on first time round so must be adding a record*/
		$sql = "SELECT count(*) FROM sf_item_type
				WHERE type_name " .LIKE. " '".$_POST['type_name'] ."'";
		$result = DB_query($sql,$db);
		$myrow = DB_fetch_row($result);
		if ( $myrow[0] > 0 ) {
			$InputError = 1;
			prnMsg( _('材料分类有重复，请重新输入'),'error');
		} else {
			$sql = "INSERT INTO sf_item_type (item_type,type_name )
					VALUES ('" . $_POST['item_type'] ."','" . $_POST['type_name'] ."')";
		}
		$msg = _('新的材料分类建立成功');
	}

	if ($InputError!=1){
		//run the SQL from either of the above possibilites
		if (is_array($sql)) {
			$result = DB_Txn_Begin($db);
			$tmpErr = _('不能更新材料分类');
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
	unset ($_POST['type_name']);

} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button
// PREVENT DELETES IF DEPENDENT RECORDS IN 'sf_item_type'
	// Get the original name of the unit of measure the ID is just a secure way to find the unit of measure
	$sql = "SELECT type_name FROM sf_item_type
		WHERE item_type = '" . $SelectedMeasureID . "'";
	$result = DB_query($sql,$db);
	if ( DB_num_rows($result) == 0 ) {
		// This is probably the safest way there is
		prnMsg( _('不能删除该材料分类，因为材料分类已经不存在'),'warn');
	} else {
		$myrow = DB_fetch_row($result);
		$Oldtype_name = $myrow[0];
		$sql= "SELECT COUNT(*) FROM sf_item_no WHERE item_type ".LIKE." '" . $Oldtype_name . "'";
		 
		$result = DB_query($sql,$db);
		$myrow = DB_fetch_row($result);
		if ($myrow[0]>0) {
			prnMsg( _('不能删除这个料号分类，因为有料号属于该材料分类'),'warn');
			echo '<br />' . _('There are') . ' ' . $myrow[0] . ' ' . _('个料号属于这个材料分类') . '</font>';
		} else {
			$sql="DELETE FROM sf_item_type WHERE type_name ".LIKE."'" . $Oldtype_name . "'";
			$result = DB_query($sql,$db);
			prnMsg( $Oldtype_name . ' ' . _('材料分类删除成功') . '!','success');
		}
	} //end if account group used in GL accounts
	unset ($SelectedMeasureID);
	unset ($_GET['SelectedMeasureID']);
	unset ($_GET['delete']);
	unset ($_POST['SelectedMeasureID']);
	unset ($_POST['MeasureID']);
	unset ($_POST['type_name']);
}

 if (!isset($SelectedMeasureID)) {

/* An unit of measure could be posted when one has been edited and is being updated
  or GOT when selected for modification
  SelectedMeasureID will exist because it was sent with the page in a GET .
  If its the first time the page has been displayed with no parameters
  then none of the above are true and the list of account groups will be displayed with
  links to delete or edit each. These will call the same page again and allow update/input
  or deletion of the records*/

	$sql = "SELECT item_type,
			type_name
			FROM sf_item_type
			ORDER BY item_type";

	$ErrMsg = _('不能获取材料分类');
	$result = DB_query($sql,$db,$ErrMsg);

	echo '<table class="selection">
			<tr>
				<th class="ascending">' . _('材料分类') . '</th>
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
		echo '<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?SelectedMeasureID=' . $myrow[0] . '">' . _('Edit') . '</a></td>';
		echo '<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?SelectedMeasureID=' . $myrow[0] . '&amp;delete=1" onclick="return confirm(\'' . _('是否要删除这个材料分类?') . '\');">' . _('Delete')  . '</a></td>';
		echo '</tr>';

	} //END WHILE LIST LOOP
	echo '</table><br />';
} //end of ifs and buts!


if (isset($SelectedMeasureID)) {
	echo '<div class="centre">
			<a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' . _('查看材料分类') . '</a>
		</div>';
}

echo '<br />';

if (! isset($_GET['delete'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .  '">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if (isset($SelectedMeasureID)) {
		//editing an existing section

		$sql = "SELECT item_type,
				type_name
				FROM sf_item_type
				WHERE item_type='" . $SelectedMeasureID . "'";

		$result = DB_query($sql, $db);
		if ( DB_num_rows($result) == 0 ) {
			prnMsg( _('找不到需要的材料分类，请重试.'),'warn');
			unset($SelectedMeasureID);
		} else {
			$myrow = DB_fetch_array($result);

			$_POST['MeasureID'] = $myrow['item_type'];
			$_POST['type_name']  = $myrow['type_name'];

			echo '<input type="hidden" name="SelectedMeasureID" value="' . $_POST['MeasureID'] . '" />';
			echo '<table class="selection">';
		}

	}  else {
		$_POST['type_name']='';
		echo '<table>';
	}
	echo '<tr>
		<td>' . _('材料分类') . ':' . '</td>
		<td><input required="required" pattern="(?!^ *$)[^+<>-]{1,}" type="text" name="type_name" title="'._('Cannot be blank or contains illegal characters').'" placeholder="'._('More than one character').'" size="30" maxlength="30" value="' . $_POST['type_name'] . '" /></td>
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
