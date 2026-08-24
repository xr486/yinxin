<?php

include('includes/session.inc');

$Title = _('工单类型维护');

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

	$_POST['type_code']=mb_strtoupper($_POST['type_code']);
	if( trim($_POST['type_code']) == '' ) {
		$InputError = 1;
		prnMsg( _('The location code may not be empty'), 'error');
	}
	if (isset($SelectedLocation) AND $InputError !=1) {

		/* Set the used_flag field to 1 if it is checked, otherwise 0 */
		 
         $v_date = strtotime(Date('Y-m-d H:i:s'));
		$sql = "UPDATE wip_types SET type_code='" . $_POST['type_code'] . "',
									type_name='" . $_POST['type_name'] . "',
									type_desc='" . $_POST['type_desc'] . "', 
									last_update_date='" . $v_date . "', 
									last_updated_by='" . $_SESSION['UserID'] . "',									
									contact='" . $_POST['Contact'] . "',
									used_flag='" . $_POST['used_flag'] . "'
						WHERE type_code = '" . $SelectedLocation . "'";

		$ErrMsg = _('修改工单类型发生错误') . ' ' . $SelectedLocation . ' ' . _('发生错误');
		$DbgMsg = _('修改工单类型');

		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

		prnMsg( _('已完成修改'),'success');
		unset($_POST['type_code']);
		unset($_POST['type_name']);
		unset($_POST['type_desc']); 	
		unset($_POST['used_flag']);


	} elseif ($InputError !=1) {
		 
		 $v_date = strtotime(Date('Y-m-d H:i:s'));
            $sql = "INSERT INTO wip_types (type_code,
										type_name,
										type_desc,
										creation_date,
										created_by,
										last_updated_by,
										last_update_date,Contact,used_flag )
						VALUES ('" . $_POST['type_code'] . "',
								'" . $_POST['type_name'] . "',
								'" . $_POST['type_desc'] ."',
								'" . $v_date . "',
								'" . $_SESSION['UserID'] . "',
								'" . $_SESSION['UserID']. "',
								'" . $v_date . "',
								'" . $_POST['Contact'] . "' ,'是'
								)";

		$ErrMsg =  _('新工单类型增加未成功原因');
		$DbgMsg =  _('The SQL used to insert the location record was');
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

		prnMsg( _('新工单类型增加完成'),'success'); 
		unset($_POST['type_code']);
		unset($_POST['type_name']);
		unset($_POST['type_desc']);
	
		unset($_POST['used_flag']);
		unset($_POST['Contact']);

	}

} elseif (isset($_GET['delete'])) {
	$CancelDelete = 0;

 
				$sql= "SELECT COUNT(*) FROM wip_jobs_all
						WHERE JOB_TYPE='" . $SelectedLocation . "'";
				$result = DB_query($sql,$db);
				$myrow = DB_fetch_row($result);
				if ($myrow[0]>0) {
					$CancelDelete = 1;
					prnMsg(_('你不能删除,改类型已经有工单在使用') . '. ' . _('The user record must be modified first'),'warn');
					 
				}
		
	
	if (! $CancelDelete) {

		 
		$result = DB_query("DELETE FROM wip_types WHERE type_code='" . $SelectedLocation . "'",$db);

		prnMsg( _('工单类型') . ' ' . $SelectedLocation . ' ' . _('被删除') . '!', 'success');
		unset ($SelectedLocation);
	} //end if Delete Location
	unset($SelectedLocation);
	unset($_GET['delete']);
}

if (!isset($SelectedLocation)) {

	$sql = "SELECT type_code, type_name,contact,creation_date,created_by,last_update_date,type_desc,last_updated_by,				
				used_flag
			FROM wip_types 
			";
	$result = DB_query($sql,$db);

	if (DB_num_rows($result)==0){
		prnMsg (_('没有适合的工单类型资料可以显示'),'error');
	}
	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/supplier.png" title="' .
			_('Inventory') . '" alt="" />' . ' ' . $Title . '</p>';

	echo '<table class="selection">';
	echo '<tr>
			<th>' . _('工单类型简称') . '</th>
			<th>' . _('类型名称') . '</th>
			<th>' . _('负责人') . '</th>
                        <th>' . _('描述说明') . '</th>
                        <th>' . _('建立日期') . '</th>
						<th>' . _('建立人') . '</th>
						<th>' . _('是否可用') . '</th>
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
			<td><a href="%sSelectedLocation=%s">' . _('Edit') . '</a></td>
			<td><a href="%sSelectedLocation=%s&amp;delete=1" onclick="return confirm(\'' . _('你确定要删除?') . '\');">' . _('Delete') . '</a></td>
			</tr>',
			$myrow['type_code'],
			$myrow['type_name'],
			$myrow['contact'],
              $myrow['type_desc'],
			  date('Y-m-d',$myrow['creation_date']),
              $myrow['created_by'],
				$myrow['used_flag'],
			htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
			$myrow['type_code'],
			htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
			$myrow['type_code']);

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

		$sql = "SELECT type_code,
					type_name,
					type_desc,
					contact,
					created_by,
					creation_date,
					last_update_date,
                    last_updated_by, 
					used_flag 
				FROM wip_types
				WHERE type_code='" . $SelectedLocation . "'";

		$result = DB_query($sql, $db);
		$myrow = DB_fetch_array($result);

		$_POST['type_code'] = $myrow['type_code'];
		$_POST['type_name']  = $myrow['type_name'];
		$_POST['type_desc'] = $myrow['type_desc'];
		$_POST['Contact'] = $myrow['contact'];
		$_POST['creation_date'] = $myrow['creation_date'];
		$_POST['created_by'] = $myrow['created_by'];
		$_POST['last_update_date'] = $myrow['last_update_date'];
		
		$_POST['used_flag'] = $myrow['used_flag'];
                $_POST['last_updated_by'] = $myrow['last_updated_by'];


		echo '<input type="hidden" name="SelectedLocation" value="' . $SelectedLocation . '" />';
		echo '<input type="hidden" name="type_code" value="' . $_POST['type_code'] . '" />';
		echo '<table class="selection">';
		echo '<tr>
				<th colspan="2">' . _('修改工单类型资料') . '</th>
			</tr>';
		echo '<tr>
				<td>' . _('工单类型简称') . ':</td>
				<td>' . $_POST['type_code'] . '</td>
			</tr>';
	} else { //end of if $SelectedLocation only do the else when a new record is being entered
		if (!isset($_POST['type_code'])) {
			$_POST['type_code'] = '';
		}
		echo '<table class="selection">
				<tr>
					<th colspan="2"><h3>' . _('新工单类型资料') . '</h3></th>
				</tr>';
		echo '<tr>
				<td>' . _('工单类型简称') . ':</td>
				<td><input type="text" autofocus="autofocus" required="required" title="' . _('输入最多10个字符的仓库编码') . '" data-type="no-illegal-chars" name="type_code" value="' . $_POST['type_code'] . '" size="10" maxlength="10" /></td>
			</tr>';
	}
	if (!isset($_POST['type_name'])) {
		$_POST['type_name'] = '';
	}
	if (!isset($_POST['Contact'])) {
		$_POST['Contact'] = '';
	}
	if (!isset($_POST['type_desc'])) {
		$_POST['type_desc'] = '';
	}
	if (!isset($_POST['creation_date'])) {
		$_POST['creation_date'] = '';
	}
	if (!isset($_POST['last_update_date'])) {
		$_POST['last_update_date'] = '';
	}
	if (!isset($_POST['created_by'])) {
		$_POST['created_by'] = '';
	}
	if (!isset($_POST['last_updated_by'])) {
		$_POST['last_updated_by'] = '';
	}
 
	 
	if (!isset($_POST['used_flag'])) {
		$_POST['used_flag'] = '是';
	}
        
	echo '<tr>
			<td>' .  _('类型名称') . ':' . '</td>
			<td><input type="text" name="type_name" required="required" value="'. $_POST['type_name'] . '" title="' . _('Enter the inventory location name this could be either a warehouse or a factory') . '" namesize="51" maxlength="50" /></td>
		</tr>
		<tr>
			<td>' . _('管理人员') . ':' . '</td>
			<td><input type="text" name="Contact" required="required" value="' . $_POST['Contact'] . '" title="' . _('Enter the name of the responsible person to contact for this inventory location') . '" size="31" maxlength="30" /></td>
		</tr>
		<tr>
			<td>' .  _('类型描述') . ':' . '</td>
			<td><input type="text" name="type_desc" value="' . $_POST['type_desc'] . '" size="41" maxlength="40" /></td>
		</tr>';

		echo '<tr>
		<td>' . _('是否可用') . ':</td>
		<td><select required="required" name="used_flag">';
if ($_POST['used_flag']==0){
	echo '<option selected="selected" value="是">' . _('是') . '</option>';
	echo '<option value="否">' . _('否') . '</option>';
} else {
 	echo '<option selected="selected" value="否">' . _('否') . '</option>';
	echo '<option value="是">' . _('是') . '</option>';
}
echo '</select></td>
	</tr>';

		
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
