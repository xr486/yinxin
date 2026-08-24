<?php

include('includes/session.inc');

$Title = _('工序强制关闭处理');

include('includes/header.inc');

if (isset($_GET['SelectedWIPNAME'])){
	$SelectedWIPNAME = $_GET['SelectedWIPNAME'];
} elseif (isset($_POST['SelectedWIPNAME'])){
	$SelectedWIPNAME = $_POST['SelectedWIPNAME'];
}
if (isset($_GET['osn'])){
	$osn = $_GET['osn'];
} elseif (isset($_POST['osn'])){
	$osn = $_POST['osn'];
}
if (isset($_GET['oc'])){
	$oc =$_GET['oc'];
} elseif (isset($_POST['oc'])){
	$oc = $_GET['oc'];
}
if (isset($_GET['emnu'])){
	$emnu =$_GET['emnu'];
} elseif (isset($_POST['emnu'])){
	$emnu = $_GET['emnu'];
}
if (isset($_GET['begin_time'])){
	$begin_time =$_GET['begin_time'];
} elseif (isset($_POST['begin_time'])){
	$begin_time = $_GET['begin_time'];
}

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('查询工序') . '</p>';
echo '<table cellpadding="3" class="selection">';

echo '<div class="text-nav">';
if (!isset($_POST['FromDate'])) {
    $_POST['FromDate'] = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 30, date("Y")));
}
if (!isset($_POST['ToDate'])) {
    $_POST['ToDate'] = Date('Y-m-d');
}
echo '<div class="text-nav-1"><div>' . _('工单号码') . ':</div>';
echo '<input type="text" name="WIP_ENTITY_NAME_from" value="' . $_POST['WIP_ENTITY_NAME_from'] . '" size="20" maxlength="25" /></div>';
echo '<div class="text-nav-1"><div>' . _('工序号') . ':</div>';
echo '<input type="text" name="operation_seq_num" value="' . $_POST['operation_seq_num'] . '" size="20" maxlength="25" /></div>';
echo '<div class="text-nav-1"><div>' . _('产品名称') . ':</div>';
echo '<input type="text" name="operation_code" value="' . $_POST['operation_code'] . '" size="20" maxlength="25" /></div>';
echo '<div class="text-nav-1"><div>' . _('员工工号') . ':</div>';
echo '<input type="text" name="employee_num" value="' . $_POST['employee_num'] . '" size="20" maxlength="25" /></div>';
echo '<div class="text-nav-1"><div>' . _('开工日期起') . ':</div>';
echo '<input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="FromDate" maxlength="10" size="20" value="'.$_POST['FromDate'].'" /></div>';

echo '<div class="text-nav-1"><div>' . _('开工日期止') . ':</div>';
echo '<input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="ToDate" maxlength="10" size="20" value="'.$_POST['ToDate'].'" /> </div>';
echo '</table><div class="centre"><input type="submit" name="Search" value="查找"> </div>'. '</br>';


if (isset($_GET['closed'])) {
    //插入数据到完工表
    $remark = $_SESSION['UserID'] . '强制关闭';
    $sql_ins = "insert into wip_transactions
        (
            operation_seq_num,
            operation_code,
            wip_entity_name,
            transaction_date,
            transaction_type,
            transaction_quantity,
            bad_quantity,
            employee_num,
            last_update_date,
            last_updated_by,
            creation_date,
            created_by,
            begin_date,
            end_date,
            remark
        )
    value
        (
            '".$osn."' ,
            '".$oc."' ,
            '".$SelectedWIPNAME."' ,
            '".time()."' ,
            '良品', 
            0,
            0, 
            '".$emnu."' ,
            '".time()."' ,
            '".$_SESSION['UserID']."',
            '".time()."' ,
            '".$_SESSION['UserID']."',
            '".$begin_time."' ,
            '".time()."' ,
            '".$remark ."'
        )
    ";
    $result_ins = DB_query($sql_ins,$db);
	//删除生产表中的数据 
	$sql_del = "delete from wip_production where wip_entity_name = '".$SelectedWIPNAME."' and operation_code = '".$oc."' and operation_seq_num = '".$osn."'";
    // echo $sql_del;
    $result_del = DB_query($sql_del,$db);
	prnMsg( _('工序') . ' ' . $oc . ' ' . _('关闭成功') . '!', 'success');
	unset($SelectedWIPNAME);
	unset($oc);	
	unset($osn);	
	unset($emnu);	
	unset($begin_time);	
	unset($_GET['closed']);
}


if (!isset($SelectedWIPNAME)  and  isset($_POST['Search']) ) {
	 	
	$sql = "SELECT a.*,h.employee_name,c.begin_quantity ,f.item_name
	    FROM wip_production a,hr_employees h,wip_operation_plan c ,wip_jobs_all b,sf_item_no f
	    WHERE a.employee_num = h.employee_num 
	    and a.wip_entity_name =c.wip_entity_name 
	    and a.operation_seq_num = c.operation_seq_num 
	    and a.wip_entity_name = b.wip_entity_name
	    and b.primary_item = f.item_no
	    ";
    
	if (isset($_POST['WIP_ENTITY_NAME_from']) and $_POST['WIP_ENTITY_NAME_from'] != '') {
        $sql = $sql . " and a.wip_entity_name " . LIKE . " '%" . $_POST['WIP_ENTITY_NAME_from'] . "%' ";
    }
    if (isset($_POST['operation_seq_num']) and $_POST['operation_seq_num'] != '') {
        $sql = $sql . " and a.operation_seq_num " . LIKE . " '%" . $_POST['operation_seq_num'] . "%' ";
    }
	if (isset($_POST['operation_code']) and $_POST['operation_code'] != '') {
        $sql = $sql . " and a.operation_code " . LIKE . " '%" . $_POST['operation_code'] . "%' ";
    }
	if (isset($_POST['employee_num']) and $_POST['employee_num'] != '') {
        $sql = $sql . " and a.employee_num " . LIKE . " '%" . $_POST['employee_num'] . "%' ";
	}
    if (empty($_POST['FromDate']) == 0) {
        $SQL_FromDate = strtotime($_POST['FromDate']);
        $sql .= " and a.begin_date >= '" . $SQL_FromDate . "' ";
    }
    if (empty($_POST['ToDate']) == 0) {
        $SQL_ToDate = strtotime($_POST['ToDate']) + 86400;
        //echo $SQL_ToDate;
        $sql .= " and a.begin_date <='" . $SQL_ToDate . "' ";
    }
      
    $result = DB_query($sql, $db);
    if (DB_num_rows($result) == 0) {
        unset($result);
        prnMsg(_('找不到该工序，请重新输入条件查询！'), 'error');
    }
	 
	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/supplier.png" title="' .
			_('Inventory') . '" alt="" />' . ' ' . $Title . '</p>';

	echo '<table class="selection">';
	echo '<tr>
			<th bgcolor="#87CEFA" width = 100>' . _('工单号码') . '</th>
			<th bgcolor="#87CEFA" width = 140>' . _('产品名称') . '</th>
			<th bgcolor="#87CEFA" width = 160>' . _('开工数量') . '</th> 
			<th bgcolor="#87CEFA" width = 100>' . _('工序号') . '</th>
			<th bgcolor="#87CEFA" width = 100>' . _('工序名称') . '</th>
			<th bgcolor="#87CEFA" width = 160>' . _('员工工号') . '</th>
            <th bgcolor="#87CEFA" width = 160>' . _('员工姓名') . '</th>
            <th bgcolor="#87CEFA" width = 160>' . _('生产工时') . '</th>
			<th bgcolor="#87CEFA" width = 100>' . _('开始时间') . '</th> 
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
	printf('<td>%s</td>
			<td>%s</td>
			<td>%s</td>
            <td>%s</td>
            <td>%s</td>  
			<td>%s</td>	
			<td>%s</td>	
			<td>%s</td>	
			<td>%s</td>	
			<td><a href="%sSelectedWIPNAME=%s&amp;closed=1&amp;osn=%s&amp;oc=%s&amp;emnu=%s&amp;begin_time=%s" onclick="return confirm(\'' . _('你确定工序关闭工序?') . '\');">' . _('关闭工序') . '</a></td>
			</tr>',
			$myrow['wip_entity_name'],
			$myrow['item_name'], 
			$myrow['begin_quantity'],  
			$myrow['operation_seq_num'],
			$myrow['operation_code'],
			$myrow['employee_num'],
			$myrow['employee_name'],
			round(((time()-$myrow['begin_date'])/3600),3),
			date('Y-m-d h:i:s',$myrow['begin_date']), 
			htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
			urlencode($myrow['wip_entity_name']),
            urlencode($myrow['operation_seq_num']),
            urlencode($myrow['operation_code']),
            urlencode($myrow['employee_num']),
            urlencode($myrow['begin_date']));
	} 
}
	//END WHILE LIST LOOP
	echo '</table>';
}

//end of ifs and buts!

echo '<br />';
if (isset($SelectedWIPNAME)) {
	echo '<a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' . _('回到工序选择界面') . '</a>';
}
echo '<br />';


include('includes/footer.inc');
?>
