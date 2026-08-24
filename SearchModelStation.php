<?php

include('includes/session.inc');

$Title = _('工位维护');

include('includes/header.inc');

if (isset($_GET['SelectedLocation'])){
    $SelectedLocation = $_GET['SelectedLocation'];
} elseif (isset($_POST['SelectedLocation'])){
    $SelectedLocation = $_POST['SelectedLocation'];
}
if (isset($_POST['Go1']) OR isset($_POST['Go2'])) {
    $_POST['PageOffset'] = (isset($_POST['Go1']) ? $_POST['PageOffset1'] : $_POST['PageOffset2']);
    $_POST['Go'] = '';
}
if (!isset($_POST['PageOffset'])) {
    $_POST['PageOffset'] = 1;
} else {
    if ($_POST['PageOffset'] == 0) {
        $_POST['PageOffset'] = 1;
    }
}
//仓库只有：编码，名称，管理员，区域，容量
if (isset($_POST['submit'])) {

    //initialise no input errors assumed initially before we test
    $InputError = 0;

    if (isset($SelectedLocation) AND $InputError !=1) {

        /* Set the use_status field to 1 if it is checked, otherwise 0 */
        $time=time();
        $sql3="update wip_modelstation set
    station='".$_POST['station']."',
    tranaction='".$_POST['tranaction']."',
    enable_flag='".$_POST['enable_flag']."',
    last_update_date='".$time."',
    last_updated_by='" . $_SESSION['UserID'] . "'
    where station_id='" . $_POST['station_id'] . "'
    ";

        $ErrMsg = _('修改工位发生错误') . ' ' . $SelectedLocation . ' ' . _('发生错误');
        $DbgMsg = _('修改生产工位');

        $result = DB_query($sql3,$db,$ErrMsg,$DbgMsg);

        prnMsg( _('已完成修改'),'success');
        unset($_POST['station']);
        unset($_POST['tranaction']);
        unset($_POST['enable_flag']);


    } elseif ($InputError !=1) {

        $time=time();
        $sql4="select COUNT(*) from wip_modelstation where tranaction='" . $_POST['tranaction'] . "'
        ";
        $result4 = DB_query($sql4,$db);
        $myrow4 = DB_fetch_row($result4);
        if($myrow4[0]>0){
            prnMsg( _('存在相同的工位！！'),'success');
        }else{
        $sql = "INSERT INTO wip_modelstation (tranaction,
										station,
										creation_date,
										created_by,
										last_updated_by,
										last_update_date,enable_flag )
						VALUES ('" . $_POST['tranaction'] . "',
								'" . $_POST['station'] . "',
								'" . $time . "',
								'" . $_SESSION['UserID'] . "',
								'" . $_SESSION['UserID']. "',
								'" . $time . "',
								'" . $_POST['enable_flag'] . "'
								)";

        $ErrMsg =  _('新工位增加未成功原因');
        $DbgMsg =  _('The SQL used to insert the location record was');
        $result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

        prnMsg( _('新工位增加完成'),'success');
        unset($_POST['operation_code']);
        unset($_POST['operation_name']);
        unset($_POST['use_status']);
        }
    }

} elseif (isset($_GET['delete'])) {
    $CancelDelete = 0;
    if (! $CancelDelete) {


        $result = DB_query("DELETE FROM wip_modelstation WHERE station_id='" . $_GET['delete'] . "'",$db);

        prnMsg( '删除成功！！', 'success');
        unset ($SelectedLocation);
    }
    unset($SelectedLocation);
    unset($_GET['delete']);
}

if (!isset($SelectedLocation) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])) {

    $sql = "SELECT station,tranaction,creation_date,created_by,last_update_date,last_updated_by,enable_flag,station_id
			FROM wip_modelstation
			";
    $result = DB_query($sql,$db);
    $ListCount = @DB_num_rows($result);
    $ListPageMax = ceil($ListCount / 10); //$_SESSION['DisplayRecordsMax']

    if (isset($_POST['Next'])) {
        if ($_POST['PageOffset'] < $ListPageMax) {
            $_POST['PageOffset'] = $_POST['PageOffset'] + 1;
        }
    }
    if (isset($_POST['Previous'])) {
        if ($_POST['PageOffset'] > 1) {
            $_POST['PageOffset'] = $_POST['PageOffset'] - 1;
        }
    }
    if (DB_num_rows($result)==0){
        prnMsg (_('没有工位资料，请维护'),'warning');
    }
    echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/supplier.png" title="' .
        _('Inventory') . '" alt="" />' . ' ' . $Title . '</p>';


    echo '<table class="selection">';
    echo '<tr>
			<th>' . _('工序') . '</th>
			<th>' . _('工位编号') . '</th>
                        <th>' . _('建立日期') . '</th>
						<th>' . _('建立人') . '</th>
						<th>' . _('是否可用') . '</th>
                        <th>' . _('编辑') . '</th>
                        <th>' . _('删除') . '</th>
		</tr>';

    $k=0; //row colour counter
    $RowIndex = 0;
    $i = 0;
if (@DB_num_rows($result) <> 0) {
    DB_data_seek($result, ($_POST['PageOffset'] - 1) * 10);
    while (($myrow = @DB_fetch_array($result)) AND ( $RowIndex <> 10)) {
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
			<td><a href="SearchModelStation.php?SelectedLocation=&update='.$myrow['station_id'].'">' . _('修改') . '</a></td>
			<td><a href="SearchModelStation.php?delete='.$myrow['station_id'].'" onclick="return confirm(\'' . _('你确定要删除?') . '\');">' . _('删除') . '</a></td>
			</tr>',
            $myrow['tranaction'],
            $myrow['station'],
            date('Y-m-d',$myrow['creation_date']),
            $myrow['created_by'],
            $myrow['enable_flag']==0?'不可用':'可用',
            htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
            $myrow['tranaction'],
            htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
            $myrow['tranaction']);
        $i++;
        $RowIndex++;
    }
    //END WHILE LIST LOOP
    echo '</table>';
}}

//end of ifs and buts!
echo '<br />';
if (isset($SelectedLocation)) {
    echo '<a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' . _('返回查看页面') . '</a>';
}
echo '<br />';

if (!isset($_GET['delete'])) {

    echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
    echo '<div>';
    echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
    if ($ListPageMax > 1) {
        echo '<br/><div class="centre">&nbsp;&nbsp;第&nbsp;'.$_POST['PageOffset'].'&nbsp;页，共&nbsp;'.$ListPageMax.'&nbsp;页&nbsp;&nbsp;
                跳转至页:<select name="PageOffset1">	';
        $ListPage = 1;
        while ($ListPage <= $ListPageMax) {
            if ($ListPage == $_POST['PageOffset']) {
                echo '<option value="' . $ListPage . '" selected="selected">' . $ListPage . '</option>';
            } else {
                echo '<option value="' . $ListPage . '">' . $ListPage . '</option>';
            }
            $ListPage++;
        }

        echo '</select>';
        echo '
			<input type="submit" name="Go1" value="跳转"/>
			<input type="submit" name="Previous" value="上一页"/>
			<input type="submit" name="Next" value="下一页"/></div>';


    }
    if (isset($SelectedLocation)) {
        //editing an existing Location
        echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/supplier.png" title="' .
            _('Inventory') . '" alt="" />' . ' ' . $Title . '</p>';
        $stationid=$_GET['update'];
        $sql2 = "SELECT station,tranaction,enable_flag,station_id
			FROM wip_modelstation where station_id='".$stationid."'
			";
        $result2 = DB_query($sql2,$db);
        $myrow2 = DB_fetch_array($result2);
        $_POST['station'] = $myrow2['station'];
        $_POST['tranaction']  = $myrow2['tranaction'];
        $_POST['enable_flag'] = $myrow2['enable_flag'];
        $_POST['station_id'] = $myrow2['station_id'];


        echo '<input type="hidden" name="SelectedLocation" value="' . $SelectedLocation . '" />';
        echo '<input type="hidden" name="operation_code" value="' . $_POST['operation_code'] . '" />';
        echo '<table class="selection">';
        echo '<tr>
				<th colspan="2">' . _('修改工位资料') . '</th>
			</tr>';
        echo '<tr>
				<td>' . _('工序') . ':</td>
				<td><input type="text" name="tranaction" value="' . $_POST['tranaction'] . '"></td>
			</tr>';
    } else { //end of if $SelectedLocation only do the else when a new record is being entered
        if (!isset($_POST['station'])) {
            $_POST['station'] = '';
        }
        echo '<table class="selection">
				<tr>
					<th colspan="2"><h3>' . _('新工位资料') . '</h3></th>
				</tr>';
        echo '<tr>
				<td>' . _('工序') . ':</td>
				<td><input type="text" name="tranaction" value="' . $_POST['tranaction'] . '"  /></td>
			</tr>';
    }
    echo '<tr>
			<td>' .  _('工位') . ':' . '</td>
			<td><input type="text" name="station" value="'. $_POST['station'] . '"  /></td>
		</tr>  <input type="hidden" name="station_id" value="'.$_POST['station_id'].'">';

    echo '<tr>
		<td>' . _('是否可用') . ':</td>
		<td><select required="required" name="enable_flag">';
    if ($_POST['enable_flag']==1){
        echo '<option selected="selected" value="1">' . _('是') . '</option>';
        echo '<option value="0">' . _('否') . '</option>';
    } else {
        echo '<option selected="selected" value="0">' . _('否') . '</option>';
        echo '<option value="1">' . _('是') . '</option>';
    }
    echo '</select></td>
	</tr>';


    echo '</table>
		<br />
		<div class="centre">
			<input type="submit" name="submit" value="' .  _('保存') . '" />
		</div>
        </div>
		</form>';

} //end if record deleted no point displaying form to add record

include('includes/footer.inc');
?>
