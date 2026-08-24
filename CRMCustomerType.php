<?php
/* $Id:  industry.php 6495 2013-12-11 23:05:30Z rchacon $*/

include('includes/session.inc');

$Title = _('客户行业类别');

include('includes/header.inc');
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' .
		_('Search') . '" alt="" />' . ' ' . $Title . '</p>';

if ( isset($_GET['SelectedIndustryID']) )
	$SelectedIndustryID = $_GET['SelectedIndustryID'];
elseif (isset($_POST['SelectedIndustryID']))
	$SelectedIndustryID = $_POST['SelectedIndustryID'];

if (isset($_POST['Submit'])) {

	//initialise no input errors assumed initially before we test

	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	if (ContainsIllegalCharacters($_POST['Industry_Name'])) {
		$InputError = 1;
		prnMsg( _('请输入正确的字符') ,'error');
	}
	if (trim($_POST['Industry_Name']) == '') {
		$InputError = 1;
		prnMsg( _('客户行业类别不能为空'), 'error');
	}

	if (isset($_POST['SelectedIndustryID']) AND $_POST['SelectedIndustryID']!='' AND $InputError !=1) {


		/*SelectedIndustryID could also exist if submit had not been clicked this code would not run in this case cos submit is false of course  see the delete code below*/
		// Check the name does not clash
		$sql = "SELECT count(*) FROM  crm_industry
				WHERE  industry_name ".LIKE." '" . $_POST['Industry_Name'] . "'";
		$result = DB_query($sql,$db);
		$myrow = DB_fetch_row($result);
		if ( $myrow[0] > 0 ) {
			$InputError = 1;
			prnMsg( _('客户行业类别有重复，请重新输入'),'error');
		} else {
			// Get the old name and check that the record still exist neet to be very carefull here
			// idealy this is one of those sets that should be in a stored procedure simce even the checks are
			// relavant
			$sql = "SELECT industry_name FROM  crm_industry
				WHERE industry_id = '" . $SelectedIndustryID . "'";
			$result = DB_query($sql,$db);
			if ( DB_num_rows($result) != 0 ) {
				// This is probably the safest way there is
				$myrow = DB_fetch_row($result);
				$OldIndustry_Name = $myrow[0];
				$sql = array();
				$sql[] = "UPDATE  crm_industry
					SET industry_name='" . $_POST['Industry_Name'] . "'
					WHERE industry_id = '" . $SelectedIndustryID . "'";
			 
			} else {
				$InputError = 1;
				prnMsg( _('客户行业类别不存在.'),'error');
			}
		}
		$msg = _('客户行业类别已经更新');
	} elseif ($InputError !=1) {
		/*SelectedIndustryID is null cos no item selected on first time round so must be adding a record*/
		$sql = "SELECT count(*) FROM  crm_industry
				WHERE industry_name " .LIKE. " '".$_POST['Industry_Name'] ."'";
		$result = DB_query($sql,$db);
		$myrow = DB_fetch_row($result);
		if ( $myrow[0] > 0 ) {
			$InputError = 1;
			prnMsg( _('客户行业类别有重复，请重新输入'),'error');
		} else {
			$sql = "INSERT INTO  crm_industry (industry_name )
					VALUES ('" . $_POST['Industry_Name'] ."')";
		}
		$msg = _('新的客户行业类别建立成功');
	}

	if ($InputError!=1){
		//run the SQL from either of the above possibilites
		if (is_array($sql)) {
			$result = DB_Txn_Begin($db);
			$tmpErr = _('不能更新客户行业类别');
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
	unset ($SelectedIndustryID);
	unset ($_POST['SelectedIndustryID']);
	unset ($_POST['Industry_Name']);

} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button
// PREVENT DELETES IF DEPENDENT RECORDS IN 'sf_item_no'
	// Get the original name of the unit of measure the ID is just a secure way to find the unit of measure
	$sql = "SELECT industry_name FROM  crm_industry
		WHERE industry_id = '" . $SelectedIndustryID . "'";
	$result = DB_query($sql,$db);
	if ( DB_num_rows($result) == 0 ) {
		// This is probably the safest way there is
		prnMsg( _('不能删除该客户行业类别，因为客户行业类别已经不存在'),'warn');
	} else {
		$myrow = DB_fetch_row($result);
		$OldIndustry_Name = $myrow[0];
		$sql= "SELECT COUNT(*) FROM customers WHERE industry ".LIKE." '" . $OldIndustry_Name . "'";
		$result = DB_query($sql,$db);
		$myrow = DB_fetch_row($result);
		if ($myrow[0]>0) {
			prnMsg( _('不能删除这个客户行业类别，因为有料号属于该客户行业类别'),'warn');
			echo '<br />' . _('There are') . ' ' . $myrow[0] . ' ' . _('个料号属于这个客户行业类别') . '</font>';
		} else {
			$sql="DELETE FROM  crm_industry WHERE industry_name ".LIKE."'" . $OldIndustry_Name . "'";
			$result = DB_query($sql,$db);
			prnMsg( $OldIndustry_Name . ' ' . _('客户行业类别删除成功') . '!','success');
		}
	} //end if account group used in GL accounts
	unset ($SelectedIndustryID);
	unset ($_GET['SelectedIndustryID']);
	unset($_GET['delete']);
	unset ($_POST['SelectedIndustryID']);
	unset ($_POST['IndustryID']);
	unset ($_POST['Industry_Name']);
}

 if (!isset($SelectedIndustryID)) {

/* An unit of measure could be posted when one has been edited and is being updated
  or GOT when selected for modification
  SelectedIndustryID will exist because it was sent with the page in a GET .
  If its the first time the page has been displayed with no parameters
  then none of the above are true and the list of account groups will be displayed with
  links to delete or edit each. These will call the same page again and allow update/input
  or deletion of the records*/

	$sql = "SELECT industry_id,
			industry_name
			FROM  crm_industry
			ORDER BY industry_id";

	$ErrMsg = _('不能获取客户行业类别');
	$result = DB_query($sql,$db,$ErrMsg);

	echo '<table class="selection">
			<tr>
				<th class="ascending">' . _('客户行业类别') . '</th>
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
		echo '<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?SelectedIndustryID=' . $myrow[0] . '">' . _('Edit') . '</a></td>';
		echo '<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?SelectedIndustryID=' . $myrow[0] . '&amp;delete=1" onclick="return confirm(\'' . _('是否要删除这个客户行业类别?') . '\');">' . _('Delete')  . '</a></td>';
		echo '</tr>';

	} //END WHILE LIST LOOP
	echo '</table><br />';
} //end of ifs and buts!


if (isset($SelectedIndustryID)) {
	echo '<div class="centre">
			<a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' . _('查看客户行业类别') . '</a>
		</div>';
}

echo '<br />';

if (! isset($_GET['delete'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .  '">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if (isset($SelectedIndustryID)) {
		//editing an existing section

		$sql = "SELECT industry_id,
				industry_name
				FROM  crm_industry
				WHERE industry_id='" . $SelectedIndustryID . "'";

		$result = DB_query($sql, $db);
		if ( DB_num_rows($result) == 0 ) {
			prnMsg( _('找不到需要的客户行业类别，请重试.'),'warn');
			unset($SelectedIndustryID);
		} else {
			$myrow = DB_fetch_array($result);

			$_POST['IndustryID'] = $myrow['industry_id'];
			$_POST['Industry_Name']  = $myrow['industry_name'];

			echo '<input type="hidden" name="SelectedIndustryID" value="' . $_POST['IndustryID'] . '" />';
			echo '<table class="selection">';
		}

	}  else {
		$_POST['Industry_Name']='';
		echo '<table>';
	}
	echo '<tr>
		<td>' . _('客户行业类别') . ':' . '</td>
		<td><input required="required" pattern="(?!^ *$)[^+<>-]{1,}" type="text" name="Industry_Name" title="'._('Cannot be blank or contains illegal characters').'" placeholder="'._('More than one character').'" size="30" maxlength="30" value="' . $_POST['Industry_Name'] . '" /></td>
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
