<?php

include('includes/session.inc');

$Title = _('工单关闭处理');

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

echo '<div class="text-nav">';
echo '<div class="text-nav-1"><div>' . _('料号') . ':</div>';
echo '<input type="text" name="item_no"   value="' . $_POST['item_no'] . '" size="20" maxlength="25" /></div>';
 
echo '<div class="text-nav-1"><div>'. _('料号名称') . ':</div>';
echo '<input type="text" name="item_name" value="' . $_POST['item_name'] . '" size="20" maxlength="25" /></div>';
echo '<div class="text-nav-1"><div>' . _('规格型号') . ':</div>';
echo '<input type="text" name="item_desc" value="' . $_POST['item_desc'] . '" size="20" maxlength="25" /></div>';


if (!isset($_POST['FromDate'])) {
    $_POST['FromDate'] = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 30, date("Y")));
}
if (!isset($_POST['ToDate'])) {
    $_POST['ToDate'] = Date('Y-m-d');
}
echo '<div class="text-nav-1"><div>' . _('工单号码') . ':</div>';
echo '<input type="text" name="WIP_ENTITY_NAME_from" value="' . $_POST['WIP_ENTITY_NAME_from'] . '" size="20" maxlength="25" /></div>';
echo '<div class="text-nav-1"><div>' . _('开工日期起') . ':</div>';
echo '<input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="FromDate" maxlength="10" size="20" value="'.$_POST['FromDate'].'" /></div>';

echo '<div class="text-nav-1"><div>' . _('开工日期止') . ':</div>';
echo '<input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="ToDate" maxlength="10" size="20" value="'.$_POST['ToDate'].'" /> </div>';
echo '</table><div class="centre"><input type="submit" name="Search" value="查找"> </div>'. '</br>';


if (isset($_GET['closed'])) {
	$CancelDelete = 0;
                
                $wipsql= "SELECT STATUS_TYPE,START_QUANTITY,ifnull(QUANTITY_COMPLETED,0) QUANTITY_COMPLETED,ifnull(QUANTITY_SCRAPPED,0) QUANTITY_SCRAPPED
				        FROM  wip_jobs_all
						WHERE wip_entity_name 	='" . $SelectedWIPNAME . "' ";
				$wipresult = DB_query($wipsql,$db);
				while ($wiprow = DB_fetch_array($wipresult)) {

					if ($wiprow[0]=='关闭') {
					$CancelDelete = 1;
					prnMsg($SelectedWIPNAME.'工单目前已关闭无需修改状态??','error');					 
				    }	
                 
				$sql= "SELECT COUNT(*) FROM  wip_material_requierments
						WHERE WIP_ENTITY_NAME='" . $SelectedWIPNAME . "'
						and QUANTITY_ISSUED>0
						";
				$result = DB_query($sql,$db);
				while ($myrow = DB_fetch_row($result)) {

					
				  if ($myrow[0]>0) {
					if   ($wiprow['START_QUANTITY']<> round($wiprow['QUANTITY_COMPLETED'] + $wiprow['QUANTITY_SCRAPPED'],2)) {
						$CancelDelete = 1;
					  prnMsg($SelectedWIPNAME.'生产工单要全部产出才可关闭,目前完工量+报废量不等于开工数量','error');	
					} 						 
				  }
				}

				if  ($wiprow['QUANTITY_COMPLETED']>0) {
				$sql= "SELECT COUNT(*) FROM  wip_material_requierments
						WHERE WIP_ENTITY_NAME='" . $SelectedWIPNAME . "'
						and REQUIRED_QUANTITY>ifnull(QUANTITY_ISSUED,0)
						";
				$result = DB_query($sql,$db);
			    while ($myrow = DB_fetch_row($result)) {
				   if ($myrow[0]>0) { 
					   $CancelDelete = 1;
					  prnMsg($SelectedWIPNAME.'生产工单有产出,料没有发完毕','error');	
					 						 
				   }
				}
				}

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

if (!isset($SelectedWIPNAME)  and  isset($_POST['Search']) ) {
	 	
	$sql = "SELECT  so_line_number,so_header_number,a.primary_item ,
					wip_entity_name,  a.status_type,
					start_quantity,plan_start_date, c.item_desc,c.item_name,a.quantity_completed,a.quantity_scrapped
				FROM wip_jobs_all a,sf_item_no c
            WHERE  (a.STATUS_TYPE not in ('关闭'))   
				and a.PRIMARY_ITEM=c.item_no  ";
    
	 if (isset($_POST['WIP_ENTITY_NAME_from']) and $_POST['WIP_ENTITY_NAME_from'] != '') {
        $sql = $sql . " and a.wip_entity_name " . LIKE . " '%" . $_POST['WIP_ENTITY_NAME_from'] . "%' ";
    }
    if (isset($_POST['item_no']) and $_POST['item_no'] != '') {
        $sql = $sql . " and a.primary_item " . LIKE . " '%" . $_POST['item_no'] . "%' ";
    }
	if (isset($_POST['item_desc']) and $_POST['item_desc'] != '') {
        $sql = $sql . " and c.item_desc " . LIKE . " '%" . $_POST['item_desc'] . "%' ";
    }
	if (isset($_POST['item_name']) and $_POST['item_name'] != '') {
        $sql = $sql . " and c.item_name " . LIKE . " '%" . $_POST['item_name'] . "%' ";
    }
	if (isset($_POST['make_factory']) and $_POST['make_factory'] != '') {
        $sql = $sql . " and a.make_factory " . LIKE . " '%" . $_POST['make_factory'] . "%' ";
    }
   
    if (empty($_POST['FromDate']) == 0) {
        $SQL_FromDate = strtotime($_POST['FromDate']);
        $sql .= " and a.plan_start_date >= '" . $SQL_FromDate . "' ";
    }
    if (empty($_POST['ToDate']) == 0) {
        $SQL_ToDate = strtotime($_POST['ToDate']) + 86400;
        //echo $SQL_ToDate;
        $sql .= " and a.plan_start_date <='" . $SQL_ToDate . "' ";
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
			<th bgcolor="#87CEFA" width = 100>' . _('工单名称') . '</th>
			<th bgcolor="#87CEFA" width = 100>' . _('工单状态') . '</th>
			<th bgcolor="#87CEFA" width = 140>' . _('料号') . '</th>
			<th bgcolor="#87CEFA" width = 160>' . _('料号名称') . '</th>
                        <th bgcolor="#87CEFA" width = 160>' . _('规格型号') . '</th> 
						<th bgcolor="#87CEFA" width = 100>' . _('开工日期') . '</th> 
						<th bgcolor="#87CEFA" width = 90>' . _('开工数量') . '</th>
						<th bgcolor="#87CEFA" width = 90>' . _('入库数量') . '</th>
						<th bgcolor="#87CEFA" width = 90>' . _('报废数量') . '</th> 
						<th bgcolor="#87CEFA" width = 100>' . _('待完成数量') . '</th>
						<th bgcolor="#87CEFA">' . _('关闭') . '</th> 
		</tr>';

$k=0; //row colour counter
if (isset($result)) {
while ($myrow = DB_fetch_array($result)) {
	if ($k==1){
		echo '<tr class="EvenTableRows">';
		$k=0;
	} else {
		echo '<tr class="OddTableRows">';
		$k=1;
	}
	$wait_quantity=$myrow['START_QUANTITY']-$myrow['QUANTITY_COMPLETED']-$myrow['QUANTITY_SCRAPPED'];
	printf('<td>%s</td>
			<td>%s</td>
			<td>%s</td>
                        <td>%s</td>
                        <td>%s</td>  
						<td>%s</td>		
						<td>%s</td>	
						<td>%s</td>	
						<td>%s</td>	
						<td>%s</td>	
			<td><a href="%sSelectedWIPNAME=%s&amp;closed=1" onclick="return confirm(\'' . _('你确定工单关闭工单?') . '\');">' . _('关闭工单') . '</a></td>
			</tr>',
			$myrow['wip_entity_name'], 
			$myrow['status_type'],
				$myrow['primary_item'],
				$myrow['item_name'],
				$myrow['item_desc'],  
			  date('Y-m-d',$myrow['plan_start_date']), 
				$myrow['start_quantity'],
				$myrow['quantity_completed'], 
              $myrow['quantity_scrapped'], 
				$wait_quantity, 
			htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
			$myrow['wip_entity_name']);

	}
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
