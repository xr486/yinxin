<?php

/* $Id: WWW_Users.php 6441 2013-11-27 01:22:14Z rchacon $*/

 
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

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>微信小程序用户管理</title>
<link rel="shortcut icon" href="/JXC/favicon.ico"/>
<link rel="icon" href="/JXC/favicon.ico"/>
<meta http-equiv="Content-Type" content="application/html; charset=utf-8"/>
<link href="/css/xenos/default.css" rel="stylesheet" type="text/css"/>
<script type="text/javascript" src ="/JXC/javascripts/miscfunctions.js"></script>
<script type="text/javascript" src ="/JXC/javascripts/wdatepicker.js"></script>
<script type="text/javascript">var basepath='/JXC/statics/base/images';</script>
<script type="text/javascript" src="/JXC/statics/base/js/metvar.js"></script>
<script type="text/javascript" src="/JXC/statics/base/js/jQuery1.7.2.js"></script>
<script type="text/javascript" src="/JXC/statics/base/js/uploadify/jquery.uploadify.v2.1.4.min.js"></script>
<script type="text/javascript" src="/JXC/statics/base/js/iframes.js"></script>
<script type="text/javascript" src="/JXC/statics/base/js/cookie.js"></script>
<script type="text/javascript" src="/JXC/statics/base/js/jquery.livequery.js"></script>



<script src="/JXC/javascript/jquery-1.7.2.min.js"></script>
<script src="/JXC/javascript/lhgdialog.min.js?self=true&skin=chrome"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
<script src="/javascript/bootstrap.min.js"></script>

<script type="text/javascript">
/*ajax执行*/
var lang = 'cn';
var metimgurl='/JXC/statics/base/images/';
var depth='';
$(document).ready(function(){
	ifreme_methei();
});
</script>
<script type="text/javascript">
function metreturn(url){
	if(url){
		location.href=url;
	}else if($.browser.msie){
		history.go(-1);
	}else{
		history.go(-1);
	}
} 

function addsave() 
{
 
	var v = $('#idcount').val();
    $("#purchase_table_"+v).css("display","");
	var c = parseInt(v) + 1;
	$('#idcount').val(c);     
}


 </script>

<script type="text/javascript">
    $(document).ready(function(){

        $('.divToilet table tr td a').click(function(){
            $(this).parent('td').toggleClass('highlight');
            if(!($(this).parent('td').hasClass('highlight'))) {
                $(this).next().val('0');
            }else {
                $(this).next().val('1');
            }
        });
       
		$('#btn_slect_employee').dialog({
            title:'选择员工',
            width: '550px',
            height: 470,
            content:'url:BtnSearchemployee.php?fwValue=&cat=buliao',
            init:function(){
			    this.content.document.getElementById('cat').value = 'buliao';
                this.content.document.getElementById('fwValue').value = '';
            }
        });
 

        //Function to get URL arguments
        function getRequest() {
            var url = location.search; //获取url中"?"符后的字串
            var theRequest = new Object();
            if (url.indexOf("?") != -1) {
                var str = url.substr(1);
                strs = str.split("&");
                for(var i = 0; i < strs.length; i ++) {
                    theRequest[strs[i].split("=")[0]]=(strs[i].split("=")[1]);
                }
            }
            return theRequest;
        }
          
 
    });
</script>
</head>
<?php
$Title = _('微信小程序用户管理');
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
		prnMsg(_('账户不能低于4个字符'),'error');
	} elseif (ContainsIlLegalCharacters($_POST['UserID'])) {
		$InputError = 1;
		prnMsg(_('User names cannot contain any of the following characters') . " - ' &amp; + \" \\ " . _('or a space'),'error');
	} elseif (mb_strlen($_POST['password'])<5){
		if (!$SelectedUser){
			$InputError = 1;
			prnMsg(_('The password entered must be at least 5 characters long'),'error');
		}
	} elseif (mb_strstr($_POST['password'],$_POST['UserID'])!= False){
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
	
	if (!isset($SelectedUser)){
		/* check to ensure the user id is not already entered */
		$result = DB_query("SELECT userid FROM www_users WHERE userid='" . $_POST['UserID'] . "'",$db);
		if (DB_num_rows($result)==1){
			$InputError =1;
			prnMsg(_('The user ID') . ' ' . $_POST['UserID'] . ' ' . _('already exists and cannot be used again'),'error');
		}
	}

	if ((mb_strlen($_POST['BranchCode'])>0) AND ($InputError !=1)) {
		// check that the entered branch is valid for the customer code
		$sql = "SELECT custbranch.debtorno
				FROM custbranch
				WHERE custbranch.debtorno='" . $_POST['Cust'] . "'
				AND custbranch.branchcode='" . $_POST['BranchCode'] . "'";

		$ErrMsg = _('The check on validity of the customer code and branch failed because');
		$DbgMsg = _('The SQL that was used to check the customer code and branch was');
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

		if (DB_num_rows($result)==0){
			prnMsg(_('The entered Branch Code is not valid for the entered Customer Code'),'error');
			$InputError = 1;
		}
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


	if (isset($SelectedUser) AND $InputError !=1) {

/*SelectedUser could also exist if submit had not been clicked this code would not run in this case cos submit is false of course  see the delete code below*/

		if (!isset($_POST['Cust'])
			OR $_POST['Cust']==NULL
			OR $_POST['Cust']==''){

			$_POST['Cust']='';
			$_POST['BranchCode']='';
		}
		 
		
//theme='" . $_POST['Theme'] . "',
		$sql = "UPDATE fa_account_info SET employee_num='" . $_POST['employee_num'] . "',
					 
						phone='" . $_POST['Phone'] ."',
						 blocked='" . $_POST['blocked'] ."',
						password='" . $_POST['password'] ."',  
						gender='" . $_POST['gender'] . "'
					WHERE username = '". $SelectedUser . "'";

		prnMsg( _('The selected user record has been updated'), 'success' );
	} elseif ($InputError !=1) {
  	 
	

		$sql = "INSERT INTO fa_account_info (username,
						employee_num,
						group_id,
					 
						password,
						phone 
					)
					VALUES ('" . $_POST['UserID'] . "',
						'" . $_POST['employee_num'] ."',
						'0',
						'" . $_POST['password'] . "', 
						'" . $_POST['Phone'] . "' )";
		prnMsg( _('记录新增完成'), 'success' );
	}

	if ($InputError!=1){
		//run the SQL from either of the above possibilites
		$ErrMsg = _('The user alterations could not be processed because');
		$DbgMsg = _('The SQL that was used to update the user and failed was');
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

		unset($_POST['UserID']);
		unset($_POST['RealName']);
        unset($_POST['depart_code']);
		unset($_POST['Cust']);
		unset($_POST['BranchCode']);
		unset($_POST['SupplierID']);
		unset($_POST['gender']);
		unset($_POST['Phone']);
		unset($_POST['Email']);
		unset($_POST['password']);
		unset($_POST['PageSize']);
		unset($_POST['Access']);
		unset($_POST['CanCreateTender']);
		unset($_POST['DefaultLocation']);
		unset($_POST['ModulesAllowed']);
		unset($_POST['blocked']);
		unset($_POST['Theme']);
		unset($_POST['UserLanguage']);
		unset($_POST['PDFLanguage']);
		unset($_POST['address']);
		unset($_POST['customerflag']);
		unset($_POST['Department']);
		unset($SelectedUser);
	}

} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button


	if ($AllowDemoMode AND $SelectedUser == 'admin') {
		prnMsg(_('The demonstration user called demo cannot be deleted'),'error');
	} else {
		 

			$sql="DELETE FROM fa_account_info WHERE username='" . $SelectedUser . "'";
			$ErrMsg = _('The User could not be deleted because');;
			$result = DB_query($sql,$db,$ErrMsg);
			prnMsg(_('User Deleted'),'info');
		 
		unset($SelectedUser);
	}

}

if (!isset($SelectedUser)) {

/* If its the first time the page has been displayed with no parameters then none of the above are true and the list of Users will be displayed with links to delete or edit each. These will call the same page again and allow update/input or deletion of the records*/

	$sql = "SELECT username,
					employee_num, 
					phone,
					 blocked,(select  employee_name from hr_employees b where a.employee_num=b.employee_num) employee_name
				FROM fa_account_info a
				where 1=1  ";//customerflag='N'
	$result = DB_query($sql,$db);

	echo '<table class="selection">';
	echo '<tr><th>' . _('账户') . '</th>
				<th>' . _('工号') . '</th>
				<th>' . _('姓名') . '</th>
				<th>' . _('电话') . '</th> 
				
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

	if ($myrow[9]=='') {
		$LastVisitDate = Date($_SESSION['DefaultDateFormat']);
	} else {
		$LastVisitDate = ConvertSQLDate($myrow[9]);
	}

		/*The SecurityHeadings array is defined in config.php */

		printf('<td>%s</td>
					<td>%s</td>
                    <td>%s</td> 
                    <td>%s</td> 
					
					<td><a href="%s&amp;SelectedUser=%s">' . _('编辑') . '</a></td>
					<td><a href="%s&amp;SelectedUser=%s&amp;delete=1" onclick="return confirm(\'' . _('Are you sure you wish to delete this user?') . '\');">' . _('删除') . '</a></td>
					</tr>',
					$myrow['username'],
					$myrow['employee_num'], 
					$myrow['employee_name'], 
					$myrow['phone'], 
					htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8')  . '?',
					$myrow['username'],
					htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
					$myrow['username']);

	} //END WHILE LIST LOOP
	echo '</table><br />';
} //end of ifs and buts!


if (isset($SelectedUser)) {
	echo '<div class="centre"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8')  . '">' . _('Review Existing Users') . '</a></div><br />';
}

echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

if (isset($SelectedUser)) {
	//editing an existing User

	$sql = "SELECT *
		FROM fa_account_info
		WHERE username='" . $SelectedUser . "'";

	$result = DB_query($sql, $db);
	$myrow = DB_fetch_array($result);

	$_POST['UserID'] = $myrow['username'];
	$_POST['employee_num'] = $myrow['employee_num'];
	$_POST['Phone'] = $myrow['phone']; 
	$_POST['blocked'] = $myrow['blocked'];
	$_POST['PDFLanguage'] = $myrow['pdflanguage'];
	$_POST['address'] = $myrow['address'];
	$_POST['price_flag'] = $myrow['price_flag'];
   	$_POST['modify_flag'] = $myrow['modify_flag'];
	$_POST['Department'] = $myrow['department'];

	echo '<input type="hidden" name="SelectedUser" value="' . $SelectedUser . '" />';
	echo '<input type="hidden" name="UserID" value="' . $_POST['UserID'] . '" />';
	echo '<input type="hidden" name="ModulesAllowed" value="' . $_POST['ModulesAllowed'] . '" />';

	echo '<table class="selection">
			<tr>
				<td>' . _('账户') . ':</td>
				<td>' . $_POST['UserID'] . '</td>
			</tr>';

} else { //end of if $SelectedUser only do the else when a new record is being entered

	echo '<table class="selection">
			<tr>
				<td>' . _('账户') . ':</td>
				<td><input pattern="(?!^([aA]{1}[dD]{1}[mM]{1}[iI]{1}[nN]{1})$)[^?+.&\\>< ]{4,}" type="text" required="required" name="UserID" size="22" maxlength="20" placeholder="'._('At least 4 characters').'" title="'._('Please input not less than 4 characters and canot be admin or contains ilLegal characters').'"  /></td>
			</tr>';

	/*set the default modules to show to all
	this had trapped a few people previously*/
	$i=0;
	if (!isset($_POST['ModulesAllowed'])) {
		$_POST['ModulesAllowed']='';
	}
	foreach($ModuleList as $ModuleName){
		if ($i>0){
			$_POST['ModulesAllowed'] .=',';
		}
		$_POST['ModulesAllowed'] .= '1';
		$i++;
	}
}

if (!isset($_POST['password'])) {
	$_POST['password']='';
}
if (!isset($_POST['employee_num'])) {
	$_POST['employee_num']='';
}
if (!isset($_POST['Phone'])) {
	$_POST['Phone']='';
}
if (!isset($_POST['Email'])) {
	$_POST['Email']='';
}
echo '<tr>
		<td>' . _('密码') . ':</td>
		<td><input type="password" pattern=".{5,}" name="password" ' . (!isset($SelectedUser) ? 'required="required"' : '') . ' size="22" maxlength="20" value="' . $_POST['password'] . '" placeholder="'._('At least 5 characters').'" title="'._('passwords must be 5 characters or more and cannot same as the users id. A mix of upper and lower case and some non-alphanumeric characters are recommended.').'" /></td>
	</tr>';
echo '<tr>
		<td>' . _('工号') . ':</td>
		<td><input type="text" name="employee_num" ' . (isset($SelectedUser) ? 'autofocus="autofocus"' : '') . ' required="required" value="' . $_POST['employee_num'] . '" size="10" maxlength="35" /></td>
	</tr>';
    
    
echo '<tr>
		<td>' . _('电话') . ':</td>
		<td><input type="tel" name="Phone" pattern="[0-9+()\s-]*" value="' . $_POST['Phone'] . '"  size="12" maxlength="30" /></td>
	</tr>';
 

echo '<input type="hidden" name="Access" value="8">';
echo '<input type="hidden" name="ID" value="'.$_SESSION['UserID'].'" /></td>

    </tr>';



echo '<tr>
		<td>' . _('账户状态') . ':</td>
		<td><select required="required" name="blocked">';
if ($_POST['blocked']==0){
	echo '<option selected="selected" value="0">' . _('开') . '</option>';
	echo '<option value="1">' . _('关') . '</option>';
} else {
 	echo '<option selected="selected" value="1">' . _('关') . '</option>';
	echo '<option value="0">' . _('开') . '</option>';
}
echo '</select></td>
	</tr>';

echo '</table>
	<br />
	<div class="centre">
		<input type="submit" name="submit" value="' . _('保存') . '" />
	</div>
    </div>
	</form>';

include('includes/footer.inc');
?>
