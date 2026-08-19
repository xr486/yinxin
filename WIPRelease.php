<?php

include('includes/session.inc');

$Title = _('工单状态变更');

include('includes/header.inc');

if (isset($_GET['SelectedWIPNAME'])){
	$SelectedWIPNAME = $_GET['SelectedWIPNAME'];
} elseif (isset($_POST['SelectedWIPNAME'])){
	$SelectedWIPNAME = $_POST['SelectedWIPNAME'];
}

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('查询工单') . '</p>';
echo '<table cellpadding="3" class="selection">';

echo '<tr><td >' . _('工单起') . ':</td><td>';
echo '<input type="text" name="WIP_ENTITY_NAME_from" value="' . $_POST['WIP_ENTITY_NAME_from'] . '" size="20" maxlength="25" /></td>';
echo '<td>' . _('工单止') . ':</td>
	<td>';
echo '<input type="text" name="WIP_ENTITY_NAME_to" value="' . $_POST['WIP_ENTITY_NAME_to'] . '" size="20" maxlength="25" /></td>';
echo '</tr>';
echo '<tr><td >' . _('料号名称') . ':</td><td>';
echo '<input type="text" name="item_no" value="' . $_POST['item_no'] . '" size="20" maxlength="25" /></td>';
echo '</tr>';

if (!isset($_POST['FromDate'])) {
    $_POST['FromDate'] = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 30, date("Y")));
}
if (!isset($_POST['ToDate'])) {
    $_POST['ToDate'] = Date('Y-m-d');
}
echo '<td>' . '预计开工日期' . _('From') . ':</td>
		<td><input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="FromDate" maxlength="10" size="11" value="" /></td>
		<td>' . _('To') . ':</td>
		<td><input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="ToDate" maxlength="10" size="11" value="" /></td>
	</tr>';

echo '</table><div class="centre"><input type="submit" name="Search" value="查找"> </div>'
 . '</br>';

if (isset($_GET['released'])) {
	$CancelDelete = 0;
 
				$sql= "SELECT OUTPUT_QUANTITY,STATUS_TYPE,START_QUANTITY FROM  wip_jobs_all
						WHERE wip_entity_name 	='" . $SelectedWIPNAME . "' ";
				$result = DB_query($sql,$db);
				$myrow = DB_fetch_row($result);
				if ($myrow[0]>0) {
					$CancelDelete = 1;
					prnMsg($SelectedWIPNAME.'工单已有产出,你不能修改为取消状态,请先退搬站资料??','warn');					 
				}		
				if ($myrow[1]=='关闭') {
					$CancelDelete = 1;
					prnMsg($SelectedWIPNAME.'工单已关闭,无法再修改状态??','warn');					 
				}
				if ($myrow[1]=='核发') {
					$CancelDelete = 1;
					prnMsg($SelectedWIPNAME.'工单目前已是核发无需修改状态??','warn');					 
				}
				$v_Start_qty=$myrow[2];

	         $sql= "SELECT COUNT(*) FROM  wip_material_requierments
						WHERE WIP_ENTITY_NAME='" . $SelectedWIPNAME . "'
						and QUANTITY_ISSUED>0
						";
				$result = DB_query($sql,$db);
				$myrow = DB_fetch_row($result);
				if ($myrow[0]>0) {
					$CancelDelete = 1;
					prnMsg($SelectedWIPNAME.'工单有料已领,你不能修改为核发状态,请先退料??','warn');
					 
				}	
				

				$sql= "SELECT min(OPERATION_SEQ_NUM) FROM  wip_operations
						WHERE wip_entity_name 	='" . $SelectedWIPNAME . "' ";
				$result = DB_query($sql,$db);
				$myrow = DB_fetch_row($result);
				$OPERATION_SEQ_NUM=$myrow[0];
					

	if (! $CancelDelete) {	
		$time = time(); 

		$result = DB_query("update wip_operations 
		              set QUANTITY_START=".$v_Start_qty.",
					  last_update_date ='".$time."',
				      last_updated_by='" . $_SESSION['UserID'] . "'	
					  WHERE WIP_ENTITY_NAME='" . $SelectedWIPNAME . "'
					  and OPERATION_SEQ_NUM=" . $OPERATION_SEQ_NUM . "
					  ",$db);


		$result = DB_query("update wip_jobs_all 
		              set STATUS_TYPE='".'核发'."',
					  DATE_RELEASED  ='".$time."',
					  last_update_date ='".$time."',
				      last_updated_by='" . $_SESSION['UserID'] . "'	
					  WHERE WIP_ENTITY_NAME='" . $SelectedWIPNAME . "'",$db);

		prnMsg( _('工单') . ' ' . $SelectedWIPNAME . ' ' . _('状态被改为核发') . '!', 'success');

	}
	unset($SelectedWIPNAME);	
	unset($_GET['released']);
}

if (isset($_GET['created'])) {
	$CancelDelete = 0; 
				$sql= "SELECT COUNT(*) FROM  wip_material_requierments
						WHERE WIP_ENTITY_NAME='" . $SelectedWIPNAME . "'
						and QUANTITY_ISSUED>0 ";
				$result = DB_query($sql,$db);
				$myrow = DB_fetch_row($result);
				if ($myrow[0]>0) {
					$CancelDelete = 1;
					prnMsg($SelectedWIPNAME.'工单有料已领,你不能修改为建立状态,请先退料??','warn');					 
				}

				$sql= "SELECT OUTPUT_QUANTITY,STATUS_TYPE FROM  wip_jobs_all
						WHERE wip_entity_name 	='" . $SelectedWIPNAME . "' ";
				$result = DB_query($sql,$db);
				$myrow = DB_fetch_row($result);
				if ($myrow[0]>0) {
					$CancelDelete = 1;
					prnMsg($SelectedWIPNAME.'工单已有产出,你不能修改为建立状态,请先退搬站资料??','warn');					 
				}	

				if ($myrow[1]=='关闭') {
					$CancelDelete = 1;
					prnMsg($SelectedWIPNAME.'工单已关闭,无法再修改状态??','warn');					 
				}
				if ($myrow[1]=='建立') {
					$CancelDelete = 1;
					prnMsg($SelectedWIPNAME.'工单目前已是建立无需修改状态??','warn');					 
				}		
	
	if (! $CancelDelete) {	
		$time = time(); 
		$result = DB_query("update wip_jobs_all 
		              set STATUS_TYPE='".'建立'."',
					  last_update_date ='".$time."',
				      last_updated_by='" . $_SESSION['UserID'] . "'	
					  WHERE WIP_ENTITY_NAME='" . $SelectedWIPNAME . "'",$db);

		prnMsg( _('工单') . ' ' . $SelectedWIPNAME . ' ' . _('状态被改为建立') . '!', 'success');

	}
	unset($SelectedWIPNAME);	
	unset($_GET['created']);
}

if (isset($_GET['cancelled'])) {
	$CancelDelete = 0; 
				$sql= "SELECT COUNT(*) FROM  wip_material_requierments
						WHERE WIP_ENTITY_NAME='" . $SelectedWIPNAME . "'
						and QUANTITY_ISSUED>0
						";
				$result = DB_query($sql,$db);
				$myrow = DB_fetch_row($result);
				if ($myrow[0]>0) {
					$CancelDelete = 1;
					prnMsg($SelectedWIPNAME.'工单有料已领,你不能修改为取消状态,请先退料??','warn');					 
				}
				$sql= "SELECT OUTPUT_QUANTITY,STATUS_TYPE FROM  wip_jobs_all
						WHERE wip_entity_name 	='" . $SelectedWIPNAME . "' ";
				$result = DB_query($sql,$db);
				$myrow = DB_fetch_row($result);
				if ($myrow[0]>0) {
					$CancelDelete = 1;
					prnMsg($SelectedWIPNAME.'工单已有产出,你不能修改为取消状态,请先退搬站资料??','warn');					 
				}		
				if ($myrow[1]=='关闭') {
					$CancelDelete = 1;
					prnMsg($SelectedWIPNAME.'工单已关闭,无法再修改状态??','warn');					 
				}
				if ($myrow[1]=='取消') {
					$CancelDelete = 1;
					prnMsg($SelectedWIPNAME.'工单目前已取消无需修改状态??','warn');					 
				}
	
	if (! $CancelDelete) {	
		$time = time(); 
		$result = DB_query("update wip_jobs_all 
		              set STATUS_TYPE='".'取消'."',
					  last_update_date ='".$time."',
				      last_updated_by='" . $_SESSION['UserID'] . "'		
					  WHERE WIP_ENTITY_NAME='" . $SelectedWIPNAME . "'",$db);
		prnMsg( _('工单') . ' ' . $SelectedWIPNAME . ' ' . _('状态被改为取消') . '!', 'success');

	}
	unset($SelectedWIPNAME);	
	unset($_GET['cancelled']);
}

if (isset($_GET['closed'])) {
	$CancelDelete = 0;

 
				$sql= "SELECT COUNT(*) FROM  wip_material_requierments
						WHERE WIP_ENTITY_NAME='" . $SelectedWIPNAME . "'
						and QUANTITY_ISSUED>0
						";
				$result = DB_query($sql,$db);
				$myrow = DB_fetch_row($result);
				if ($myrow[0]>0) {
					$CancelDelete = 1;
					prnMsg($SelectedWIPNAME.'工单有料已领,你不能修改为关闭状态,请先退料??','warn');
				}
				$sql= "SELECT OUTPUT_QUANTITY,STATUS_TYPE FROM  wip_jobs_all
						WHERE wip_entity_name 	='" . $SelectedWIPNAME . "' ";
				$result = DB_query($sql,$db);
				$myrow = DB_fetch_row($result);
				if ($myrow[0]>0) {
					$CancelDelete = 1;
					prnMsg($SelectedWIPNAME.'工单已有产出,你不能修改为取消状态,请先退搬站资料??','warn');					 
				}		
				if ($myrow[1]=='关闭') {
					$CancelDelete = 1;
					prnMsg($SelectedWIPNAME.'工单目前已关闭无需修改状态??','warn');					 
				}	
	
	if (! $CancelDelete) {		 
		$time = time(); 
		$result = DB_query("update wip_jobs_all 
		              set STATUS_TYPE='".'关闭'."',
					  DATE_CLOSED 	='".$time."',
					  last_update_date ='".$time."',
				      last_updated_by='" . $_SESSION['UserID'] . "'		
					  WHERE WIP_ENTITY_NAME='" . $SelectedWIPNAME . "'",$db);
		prnMsg( _('工单') . ' ' . $SelectedWIPNAME . ' ' . _('状态被改为关闭') . '!', 'success');
	}
	unset($SelectedWIPNAME);	
	unset($_GET['closed']);
}

if (isset($_GET['delete'])) {
	$CancelDelete = 0;
	
				$sql= "SELECT COUNT(*) FROM  wip_moves_all
						WHERE WIP_ENTITY_NAME='" . $SelectedWIPNAME . "'";
				$result = DB_query($sql,$db);
				$myrow = DB_fetch_row($result);
				if ($myrow[0]>0) {
					$CancelDelete = 1;
					prnMsg($SelectedWIPNAME.'你不能删除,工单已经有工单搬站交易??','warn');				
				}
				$sql= "SELECT COUNT(*) FROM inv_transactions_all
						WHERE wip_entity_name 	='" . $SelectedWIPNAME . "'";
				$result = DB_query($sql,$db);
				$myrow = DB_fetch_row($result);
				if ($myrow[0]>0) {
					$CancelDelete = 1;
					prnMsg($SelectedWIPNAME.'你不能删除,工单已经有工单领退料或者入库交易??','warn');					 
				}			
	if (! $CancelDelete) {		 
		$result = DB_query("DELETE FROM wip_jobs_all WHERE WIP_ENTITY_NAME='" . $SelectedWIPNAME . "'",$db);
		$result = DB_query("DELETE FROM  wip_moves_all WHERE WIP_ENTITY_NAME='" . $SelectedWIPNAME . "'",$db);
		$result = DB_query("DELETE FROM  wip_operations WHERE WIP_ENTITY_NAME='" . $SelectedWIPNAME . "'",$db);
		$result = DB_query("DELETE FROM  wip_material_requierments WHERE WIP_ENTITY_NAME='" . $SelectedWIPNAME . "'",$db);

		prnMsg( _('工单') . ' ' . $SelectedLocation . ' ' . _('被删除') . '!', 'success');
		unset ($SelectedWIPNAME);
		unset($_GET['delete']);
	}
	unset($SelectedWIPNAME);	
	unset($_GET['delete']);
}

if (!isset($SelectedWIPNAME)  and  isset($_POST['Search']) ) {

	$sql = "SELECT  a.PRIMARY_ITEM ,
					WIP_ENTITY_NAME,
					a.JOB_TYPE 	, a.STATUS_TYPE,
					START_QUANTITY,SCHEDULED_START_DATE,SCHEDULED_COMPLETION_DATE,b.item_desc
				FROM wip_jobs_all a,sf_item_no b
            WHERE  (a.STATUS_TYPE not in ('关闭'))  
				and a.PRIMARY_ITEM=b.item_no  ";
    if (isset($_POST['WIP_ENTITY_NAME_from']) and $_POST['WIP_ENTITY_NAME_from'] != '') {
        $sql = $sql . " and a.WIP_ENTITY_NAME >=  '" . $_POST['WIP_ENTITY_NAME_from'] . "'";
    }
    if (isset($_POST['WIP_ENTITY_NAME_to']) and $_POST['WIP_ENTITY_NAME_to'] != '') {
        $sql = $sql . " and a.WIP_ENTITY_NAME <=  '" . $_POST['WIP_ENTITY_NAME_to'] . "' ";
    }
    if (isset($_POST['item_no']) and $_POST['item_no'] != '') {
        $sql = $sql . " and b.item_no " . LIKE . " '%" . $_POST['item_no'] . "%' ";
    }
   
    if (empty($_POST['FromDate']) == 0) {
        $SQL_FromDate = strtotime($_POST['FromDate']);
        $sql .= " and a.SCHEDULED_START_DATE >= '" . $SQL_FromDate . "' ";
    }
    if (empty($_POST['ToDate']) == 0) {
        $SQL_ToDate = strtotime($_POST['ToDate']) + 86400;
        //echo $SQL_ToDate;
        $sql .= " and a.SCHEDULED_START_DATE <='" . $SQL_ToDate . "' ";
    }
      
    $result = DB_query($sql, $db);
    if (DB_num_rows($result) == 0) {
        unset($result);
        prnMsg(_('找不到该工单，请重新输入条件查询！'), 'error');
    }
	 
	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/supplier.png" title="' .
			_('Inventory') . '" alt="" />' . ' ' . $Title . '</p>';

	echo '<table class="selection">';
	echo '<tr>
			<th>' . _('工单名称') . '</th>
			<th>' . _('工单类型') . '</th>
			<th>' . _('工单状态') . '</th>
                        <th>' . _('料号描述') . '</th>
                        <th>' . _('建立日期') . '</th>
						<th>' . _('预计开工日期') . '</th>
						<th>' . _('预计完工日期') . '</th>
						<th>' . _('开工数量') . '</th>
                        <th>' . _('状态转换为建立') . '</th>
                        <th>' . _('核发') . '</th>
						<th>' . _('状态转换为取消') . '</th>
						<th>' . _('关闭') . '</th>
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
			<td><a href="%sSelectedWIPNAME=%s&amp;created=1" onclick="return confirm(\'' . _('你确定将该工单状态改为建立?') . '\');">' . _('建立') . '</a></td>
			<td><a href="%sSelectedWIPNAME=%s&amp;released=1" onclick="return confirm(\'' . _('你确定核发该工单?') . '\');">' . _('核发') . '</a></td>
			<td><a href="%sSelectedWIPNAME=%s&amp;cancelled=1" onclick="return confirm(\'' . _('你确定取消该工单?') . '\');">' . _('取消') . '</a></td>
			<td><a href="%sSelectedWIPNAME=%s&amp;closed=1" onclick="return confirm(\'' . _('你确定工单关闭工单?') . '\');">' . _('关闭工单') . '</a></td>
			<td><a href="%sSelectedWIPNAME=%s&amp;delete=1" onclick="return confirm(\'' . _('你确定工单删除工单?') . '\');">' . _('删除工单') . '</a></td>
			</tr>',
			$myrow['WIP_ENTITY_NAME'],
			$myrow['JOB_TYPE'],
			$myrow['STATUS_TYPE'],
				$myrow['PRIMARY_ITEM'],
              $myrow['item_desc'],
			  date('Y-m-d',$myrow['SCHEDULED_START_DATE']),
				date('Y-m-d',$myrow['SCHEDULED_COMPLETION_DATE']),
              $myrow['START_QUANTITY'], 
			htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
			$myrow['WIP_ENTITY_NAME'],
			htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
			$myrow['WIP_ENTITY_NAME'],
			htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
			$myrow['WIP_ENTITY_NAME'],
			htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
			$myrow['WIP_ENTITY_NAME'],
			htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
			$myrow['WIP_ENTITY_NAME']);

	}
	//END WHILE LIST LOOP
	echo '</table>';
}

//end of ifs and buts!

echo '<br />';
if (isset($SelectedWIPNAME)) {
	echo '<a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' . _('回到工单选择界面') . '</a>';
}
echo '<br />';


include('includes/footer.inc');
?>
