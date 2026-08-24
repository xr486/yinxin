<?php

/* $Id: WWW_Users.php 6441 2013-11-27 01:22:14Z rchacon $*/

if (isset($_POST['UserID']) AND isset($_POST['ID'])){
	if ($_POST['UserID'] == $_POST['ID']) {
		$_POST['Language'] = $_POST['UserLanguage'];
	}
}
include('includes/session.inc');

$ModuleList = array(_('客户'),
					_('转移申请'),
					_('运输'),
					_('检验'),
					_('Inventory'),
					_('财务'),
					_('Setup'));

$PDFLanguages = array(_('Latin Western Languages'),
						_('Eastern European Russian Japanese Korean Vietnamese Hebrew Arabic Thai'),
						_('Chinese'),
						_('Free Serif'));

$Title = _('用户资料修改');
/* webERP manual links before header.inc */
$ViewTopic= 'GettingStarted';
$BookMark = 'UserMaintenance';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/group_add.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '</p>
	<br />';

// Make an array of the security roles
$sql = "SELECT secroleid,
				secrolename
		FROM securityroles
		ORDER BY secrolename";

$Sec_Result = DB_query($sql, $db);
$SecurityRoles = array();
// Now load it into an a ray using Key/Value pairs
while( $Sec_row = DB_fetch_row($Sec_Result) ) {
	$SecurityRoles[$Sec_row[0]] = $Sec_row[1];
}
DB_free_result($Sec_Result);

if (isset($_GET['SelectedUser'])){
	$SelectedUser = $_GET['SelectedUser'];
} elseif (isset($_POST['SelectedUser'])){
	$SelectedUser = $_POST['SelectedUser'];
}

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible
	if (mb_strlen($_POST['UserID'])<4){
		$InputError = 1;
		prnMsg(_('The user ID entered must be at least 4 characters long'),'error');
	} elseif (ContainsIlLegalCharacters($_POST['UserID'])) {
		$InputError = 1;
		prnMsg(_('User names cannot contain any of the following characters') . " - ' &amp; + \" \\ " . _('or a space'),'error');
	} elseif (mb_strlen($_POST['Password'])<5){
		if (!$SelectedUser){
			$InputError = 1;
			prnMsg(_('The password entered must be at least 5 characters long'),'error');
		}
	} elseif (mb_strstr($_POST['Password'],$_POST['UserID'])!= False){
		$InputError = 1;
		prnMsg(_('The password cannot contain the user id'),'error');
	} elseif ((mb_strlen($_POST['Cust'])>0)
				AND (mb_strlen($_POST['BranchCode'])==0)) {
		$InputError = 1;
		prnMsg(_('If you enter a Customer Code you must also enter a Branch Code valid for this Customer'),'error');
	} elseif ($AllowDemoMode AND $_POST['UserID'] == 'admin') {
		prnMsg(_('The demonstration user called demo cannot be modified.'),'error');
		$InputError = 1;
	}


	

	/* Make a comma separated list of modules allowed ready to update the database*/
	$i=0;
	$ModulesAllowed = '';
	while ($i < count($ModuleList)){
		$FormVbl = 'Module_' . $i;
		$ModulesAllowed .= $_POST[($FormVbl)] . ',';
		$i++;
	}
	$ModulesAllowed ='1,1,1,1,1,1,1,1,1,1,1,1,';
	$_POST['ModulesAllowed']= '1,1,1,1,1,1,1,1,1,1,1,1,';


	if ( $InputError !=1) {

/*SelectedUser could also exist if submit had not been clicked this code would not run in this case cos submit is false of course  see the delete code below*/

		if (!isset($_POST['Cust'])
			OR $_POST['Cust']==NULL
			OR $_POST['Cust']==''){

			$_POST['Cust']='';
			$_POST['BranchCode']='';
		}
		$UpdatePassword = '';
		if ($_POST['Password'] != ''){
			$UpdatePassword = "password='" . CryptPass($_POST['Password']) . "',";
		}

		$sql = "UPDATE www_users SET realname='" . $_POST['RealName'] . "', 
						phone='" . $_POST['Phone'] ."',
						email='" . $_POST['Email'] ."',
						" . $UpdatePassword . "  
						
						language ='" . $_POST['UserLanguage'] . "',  
						address='" . $_POST['address'] . "',
						displayrecordsmax='" . $_POST['displayrecordsmax'] . "' 
					WHERE userid = '" . $_SESSION['UserID'] . "'";

		prnMsg( _('用户信息已更新'), 'success' );
             
	}

	if ($InputError!=1){
		//run the SQL from either of the above possibilites
		$ErrMsg = _('The user alterations could not be processed because');
		$DbgMsg = _('The SQL that was used to update the user and failed was');
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
          
		unset($_POST['UserID']);
		unset($_POST['RealName']);
		unset($_POST['Cust']);
		unset($_POST['BranchCode']);
		unset($_POST['SupplierID']);
		unset($_POST['salesman']);
		unset($_POST['Phone']);
		unset($_POST['Email']);
		unset($_POST['Password']);
		unset($_POST['PageSize']);
		unset($_POST['Access']);
		unset($_POST['CanCreateTender']);
		unset($_POST['DefaultLocation']);
		unset($_POST['ModulesAllowed']);
		unset($_POST['Blocked']);
		unset($_POST['Theme']);
		unset($_POST['UserLanguage']);
		unset($_POST['PDFLanguage']);
		unset($_POST['address']);
		unset($_POST['customerflag']);
		unset($_POST['Department']);
		
	}

}





	echo '<div class="centre">' . _('用户信息') . '</div><br />';


echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	//editing an existing User

	$sql = "SELECT userid,
			realname,
			phone,
			email,
			customerid,
			password,
			branchcode,
			supplierid,
			salesman,
			pagesize,displayrecordsmax,
			fullaccess,
			cancreatetender,
			defaultlocation,
			modulesallowed,
			blocked,
			theme,
			language,
			pdflanguage,address,customerflag,
			department
		FROM www_users
		 where userid = '" . $_SESSION['UserID'] . "'";

	$result = DB_query($sql, $db);
	$myrow = DB_fetch_array($result);

	$_POST['UserID'] = $myrow['userid'];
	$_POST['RealName'] = $myrow['realname'];
	$_POST['Phone'] = $myrow['phone'];
	$_POST['Email'] = $myrow['email'];
	$_POST['Cust']	= $myrow['customerid'];
	$_POST['BranchCode']  = $myrow['branchcode'];
	$_POST['SupplierID'] = $myrow['supplierid'];
	$_POST['salesman'] = $myrow['salesman'];
	$_POST['PageSize'] = $myrow['pagesize'];
	$_POST['Access'] = $myrow['fullaccess'];
	$_POST['CanCreateTender'] = $myrow['cancreatetender'];
	$_POST['DefaultLocation'] = $myrow['defaultlocation'];
	$_POST['ModulesAllowed'] = $myrow['modulesallowed'];
	$_POST['Theme'] = $myrow['theme'];
	$_POST['UserLanguage'] = $myrow['language'];
	$_POST['Blocked'] = $myrow['blocked'];
	$_POST['PDFLanguage'] = $myrow['pdflanguage'];
	$_POST['address'] = $myrow['address'];
	$_POST['customerflag'] = $myrow['customerflag'];
	$_POST['Department'] = $myrow['department'];
	$_POST['displayrecordsmax'] = $myrow['displayrecordsmax'];

	echo '<input type="hidden" name="SelectedUser" value="' . $SelectedUser . '" />';
	echo '<input type="hidden" name="UserID" value="' . $_POST['UserID'] . '" />';
	echo '<input type="hidden" name="ModulesAllowed" value="' . $_POST['ModulesAllowed'] . '" />';

	echo '<table class="selection">
			<tr>
				<td>' . _('User code') . ':</td>
				<td>' . $_POST['UserID'] . '</td>
			</tr>';



if (!isset($_POST['Password'])) {
	$_POST['Password']='';
}
if (!isset($_POST['RealName'])) {
	$_POST['RealName']='';
}
if (!isset($_POST['Phone'])) {
	$_POST['Phone']='';
}
if (!isset($_POST['Email'])) {
	$_POST['Email']='';
}
echo '<tr>
		<td>' . _('Password') . ':</td>
		<td><input type="password" pattern=".{5,}" name="Password" ' . (!isset($SelectedUser) ? 'required="required"' : '') . ' size="22" maxlength="20" value="' . $_POST['Password'] . '" placeholder="'._('At least 5 characters').'" title="'._('Passwords must be 5 characters or more and cannot same as the users id. A mix of upper and lower case and some non-alphanumeric characters are recommended.').'" /></td>
	</tr>';
echo '<tr>
		<td>' . _('使用者姓名') . ':</td>
		<td><input type="text" name="RealName" ' . (isset($SelectedUser) ? 'autofocus="autofocus"' : '') . ' required="required" value="' . $_POST['RealName'] . '" size="36" maxlength="35" /></td>
	</tr>';
echo '<tr>
		<td>' . _('Telephone No') . ':</td>
		<td><input type="tel" name="Phone" pattern="[0-9+()\s-]*" value="' . $_POST['Phone'] . '"  size="32" maxlength="30" /></td>
	</tr>';
echo '<tr>
		<td>' . _('Email Address') .':</td>
		<td><input type="email" name="Email" placeholder="' . _('e.g. user@domain.com') . '" required="required" value="' . $_POST['Email'] .'" size="32" maxlength="55" title="'._('A valid email address is required').'" /></td>
	</tr>';

echo '<input type="hidden" name="Access" value="8">';
echo '<input type="hidden" name="ID" value="'.$_SESSION['UserID'].'" /></td>

    </tr>';



if (!isset($_POST['Cust'])) {
	$_POST['Cust']='';
}
if (!isset($_POST['BranchCode'])) {
	$_POST['BranchCode']='';
}
if (!isset($_POST['SupplierID'])) {
	$_POST['SupplierID']='';
}


echo '<input type="hidden" name="PageSize" value="A4">';



echo '<input type="hidden" name="UserLanguage" value="zh_CN.utf8">';



echo '<input type="hidden" name="PDFLanguage" value="2">';



echo '<tr>
	 
		<td><input type="hidden" name="department" size="15" value="' . $_POST['department'] .'"></td>
	  </tr>';
	  
 
	  
echo '<tr>
		<td>联系地址：</td>
		<td><input type="text" name="address" size="35" value="' . $_POST['address'] .'"></td>
	  </tr>';
 echo '<tr>
		<td>单页显示行数：</td>
		<td><input type="text" class="number" name="displayrecordsmax" size="10" value="' . $_POST['displayrecordsmax'] .'"></td>
	  </tr>';




echo '</table>
	<br />
	<div class="centre">
		<input type="submit" name="submit" value="' . _('Enter Information') . '" />
	</div>
    </div>
	</form>';

include('includes/footer.inc');
?>
