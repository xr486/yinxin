<?php
/* $Id: ap_fee_pay_types.php 6310 2014-03-20 10:42:50 sheng $*/

include('includes/session.inc');

$Title = _('付款费用类型维护');

include('includes/header.inc');
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' .
		_('Search') . '" alt="" />' . ' ' . $Title . '</p>';

//$sysdate=  FormatDateForSQL(date("Y-m-d"));

if ( isset($_GET['Selectedpaytypeid']) )
	$Selectedpaytypeid = $_GET['Selectedpaytypeid'];
elseif (isset($_POST['Selectedpaytypeid']))
	$Selectedpaytypeid = $_POST['Selectedpaytypeid'];

if (isset($_POST['Submit'])) {

	//initialise no input errors assumed initially before we test

	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	if (ContainsIllegalCharacters($_POST['paytype'])) {
		$InputError = 1;
		prnMsg( _('新建的费用类型含有非法的字符，请输入正确的字符！') ,'error');
	}
	if (trim($_POST['paytype']) == '') {
		$InputError = 1;
		prnMsg( _('费用类型不能为空！'), 'error');
	}

	if (isset($_POST['Selectedpaytypeid']) AND $_POST['Selectedpaytypeid']!='' AND $InputError !=1) {


		/*Selectedpaytypeid could also exist if submit had not been clicked this code would not run in this case cos submit is false of course  see the delete code below*/
		// Check the name does not clash
		$sql = "SELECT count(*) FROM ap_fee_pay_types
				WHERE paytypeid <> '" . $Selectedpaytypeid ."'
				AND paytype ".LIKE." '" . $_POST['paytype'] . "'";
		$result = DB_query($sql,$db);
		$myrow = DB_fetch_row($result);
		if ( $myrow[0] > 0 ) {
			$InputError = 1;
			prnMsg( _('已存在该费用类型！'),'error');
		} else {
			// Get the old name and check that the record still exist neet to be very carefull here
			// idealy this is one of those sets that should be in a stored procedure simce even the checks are
			// relavant
			$sql = "SELECT paytype FROM ap_fee_pay_types
				WHERE paytypeid = '" . $Selectedpaytypeid . "'";
			$result = DB_query($sql,$db);
			if ( DB_num_rows($result) != 0 ) {
				// This is probably the safest way there is
				$myrow = DB_fetch_row($result);
				$Oldpaytype = $myrow[0];
				$sql = array();
				$sql[] = "UPDATE ap_fee_pay_types
					SET paytype='" . $_POST['paytype'] . "',
                                            last_updated_by = '" . $_SESSION['UserID'] . "',
                                            last_update_date = now()
					WHERE paytype ".LIKE." '".$Oldpaytype."'";
				$sql[] = "UPDATE stockmaster
					SET Disposal='" . $_POST['paytype'] . "'
					WHERE Disposal ".LIKE." '" . $Oldpaytype . "'";
			} else {
				$InputError = 1;
				prnMsg( _('The unit of measure no longer exist.'),'error');
			}
		}
		$msg = _('费用类型变更成功！');
	} elseif ($InputError !=1) {
		/*Selectedpaytypeid is null cos no item selected on first time round so must be adding a record*/
		$sql = "SELECT count(*) FROM ap_fee_pay_types
				WHERE paytype " .LIKE. " '".$_POST['paytype'] ."'";
		$result = DB_query($sql,$db);
		$myrow = DB_fetch_row($result);
		if ( $myrow[0] > 0 ) {
			$InputError = 1;
			prnMsg( _('已存在该费用类型！'),'error');
		} else {
			$sql = "INSERT INTO ap_fee_pay_types (paytype,created_by,creation_date,last_updated_by,last_update_date )
					VALUES ('" . $_POST['paytype'] ."','" . $_SESSION['UserID'] ."',now(),'" . $_SESSION['UserID'] ."',now())";
		}
		$msg = _('新的费用类型已建立成功');
	}

	if ($InputError!=1){
		//run the SQL from either of the above possibilites
		if (is_array($sql)) {
			$result = DB_Txn_Begin($db);
			$tmpErr = _('不能建立新的费用类型');
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
	unset ($Selectedpaytypeid);
	unset ($_POST['Selectedpaytypeid']);
	unset ($_POST['paytype']);

} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button
// PREVENT DELETES IF DEPENDENT RECORDS IN 'stockmaster'
	// Get the original name of the unit of measure the ID is just a secure way to find the unit of measure
	$sql = "SELECT paytype FROM ap_fee_pay_types
		WHERE paytypeid = '" . $Selectedpaytypeid . "'";
	$result = DB_query($sql,$db);
	if ( DB_num_rows($result) == 0 ) {
		// This is probably the safest way there is
		prnMsg( _('改费用类型已不存在！'),'warn');
	} else {
		$myrow = DB_fetch_row($result);
		$Oldpaytype = $myrow[0];
		$sql= "SELECT COUNT(*) FROM stockmaster WHERE Disposal ".LIKE." '" . $Oldpaytype . "'";
		$result = DB_query($sql,$db);
		$myrow = DB_fetch_row($result);
		if ($myrow[0]>0) {
			prnMsg( _('不能删除该处置方式，因为有料号使用该处置方式！'),'warn');
			echo '<br />' . _('There are') . ' ' . $myrow[0] . ' ' . _('料号使用该处置方式！') . '</font>';
		} else {
			$sql="DELETE FROM ap_fee_pay_types WHERE paytype ".LIKE."'" . $Oldpaytype . "'";
			$result = DB_query($sql,$db);
			prnMsg( $Oldpaytype . ' ' . _('处置方式已删除！') . '!','success');
		}
	} //end if account group used in GL accounts
	unset ($Selectedpaytypeid);
	unset ($_GET['Selectedpaytypeid']);
	unset($_GET['delete']);
	unset ($_POST['Selectedpaytypeid']);
	unset ($_POST['DisposalID']);
	unset ($_POST['paytype']);
}

 if (!isset($Selectedpaytypeid)) {

/* An unit of measure could be posted when one has been edited and is being updated
  or GOT when selected for modification
  Selectedpaytypeid will exist because it was sent with the page in a GET .
  If its the first time the page has been displayed with no parameters
  then none of the above are true and the list of account groups will be displayed with
  links to delete or edit each. These will call the same page again and allow update/input
  or deletion of the records*/

	$sql = "SELECT paytypeid,
			paytype
			FROM ap_fee_pay_types
			ORDER BY paytypeid";

	$ErrMsg = _('不能获取费用类型，原因是：');
	$result = DB_query($sql,$db,$ErrMsg);

	echo '<table class="selection">
			<tr>
				<th class="ascending">' . _('费用类型') . '</th>
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
		echo '<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?Selectedpaytypeid=' . $myrow[0] . '">' . _('Edit') . '</a></td>';
		echo '<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?Selectedpaytypeid=' . $myrow[0] . '&amp;delete=1" onclick="return confirm(\'' . _('你确定要删除该费用类型?') . '\');">' . _('Delete')  . '</a></td>';
		echo '</tr>';

	} //END WHILE LIST LOOP
	echo '</table><br />';
} //end of ifs and buts!


if (isset($Selectedpaytypeid)) {
	echo '<div class="centre">
			<a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' . _('返回费用类型') . '</a>
		</div>';
}

echo '<br />';

if (! isset($_GET['delete'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .  '">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if (isset($Selectedpaytypeid)) {
		//editing an existing section

		$sql = "SELECT paytypeid,
				paytype
				FROM ap_fee_pay_types
				WHERE paytypeid='" . $Selectedpaytypeid . "'";

		$result = DB_query($sql, $db);
		if ( DB_num_rows($result) == 0 ) {
			prnMsg( _('找不到该费用类型，请重试.'),'warn');
			unset($Selectedpaytypeid);
		} else {
			$myrow = DB_fetch_array($result);
                        echo $myrow[0];
			$_POST['DisposalID'] = $myrow['paytypeid'];
			$_POST['paytype']  = $myrow['paytype'];

			echo '<input type="hidden" name="Selectedpaytypeid" value="' . $_POST['DisposalID'] . '" />';
			echo '<table class="selection">';
		}

	}  else {
		$_POST['paytype']='';
		echo '<table>';
	}
	echo '<tr>
		<td>' . _('费用付款类型') . ':' . '</td>
		<td><input required="required" pattern="(?!^ *$)[^+<>-]{1,}" type="text" name="paytype" title="'._('Cannot be blank or contains illegal characters').'" placeholder="'._('More than one characters').'" size="30" maxlength="30" value="' . $_POST['paytype'] . '" /></td>
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
