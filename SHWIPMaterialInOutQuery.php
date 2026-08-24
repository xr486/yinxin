<?php

ob_start();
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include('includes/session.inc');
$Title = _('售后工单领料明细报表');
$ViewTopic = '售后工单领料明细报表';
$BookMark = '售后工单领料明细报表';

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

unset($result);

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

if (isset($_POST['Search']) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])) {
    $sql = "select  d.yiqi_sn,d.yiqi_desc,d.shiji_desc,d.lot_num,b.item_no ,c.after_onhand,a.wip_entity_name,c.transaction_type,c.transaction_date,a.creation_date,c.created_by,b.item_desc,b.item_name,c.quantity,c.subinventory_from,c.trans_num,b.gongyi,a.make_factory,c.remark,d.lianluodan 
				FROM wip_jobs_all a,sf_item_no b,inv_transactions_all c,so_qc_bad_all d
           WHERE   c.transaction_type in ('售后工单领料') 
				and c.item_no=b.item_no and a.lianluodan=d.lianluodan 
				and a.wip_entity_name=c.wip_entity_name
				and b.item_no=c.item_no";

  
     if (isset($_POST['wip_entity_name']) and $_POST['wip_entity_name'] != '') { 
		$sql = $sql . " and a.wip_entity_name " . LIKE . " '%" . $_POST['wip_entity_name'] . "%' ";
    }
   
	if (isset($_POST['trans_num']) and $_POST['trans_num'] != '') { 
		$sql = $sql . " and c.trans_num " . LIKE . " '%" . $_POST['trans_num'] . "%' ";
    }
    if (isset($_POST['item_no']) and $_POST['item_no'] != '') {
        $sql = $sql . " and b.item_no " . LIKE . " '%" . $_POST['item_no'] . "%' ";
    }
	if (isset($_POST['item_name']) and $_POST['item_name'] != '') {
        $sql = $sql . " and b.item_name " . LIKE . " '%" . $_POST['item_name'] . "%' ";
    }
	if (isset($_POST['item_desc']) and $_POST['item_desc'] != '') {
        $sql = $sql . " and b.item_desc " . LIKE . " '%" . $_POST['item_desc'] . "%' ";
    }
	if (isset($_POST['loccode_from']) and $_POST['loccode_from'] != '') {
        $sql = $sql . " and c.subinventory_from " . LIKE . " '%" . $_POST['loccode_from'] . "%' ";
    }
   
    if (empty($_POST['FromDate']) == 0) {
        $SQL_FromDate = strtotime($_POST['FromDate']);
        $sql .= " and c.transaction_date >= '" . $SQL_FromDate . "' ";
    }
    if (empty($_POST['ToDate']) == 0) {
        $SQL_ToDate = strtotime($_POST['ToDate']) + 86400;
        //echo $SQL_ToDate;
        $sql .= " and c.transaction_date <='" . $SQL_ToDate . "' ";
    }
      $sql .= " order by c.transaction_date desc ";
    $result = DB_query($sql, $db);
    if (DB_num_rows($result) == 0) {
        unset($result);
        prnMsg(_('找不到资料，请重新输入条件查询！'), 'error');
    }
}


echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('查询工单') . '</p>';
echo '<table cellpadding="3" class="selection">';
echo '<div class="text-nav">';
echo '<div class="text-nav-1"><div>' . _('工单名') . ':</div>';
echo '<input type="text" name="wip_entity_name"   value="' . $_POST['wip_entity_name'] . '" size="10" maxlength="25" /></div>';


echo '<div class="text-nav-1"><div>' . _('交易单号') . ':</div>';
echo '<input type="text" name="trans_num"   value="' . $_POST['trans_num'] . '" size="10" maxlength="25" /></div>';
echo '<div class="text-nav-1"><div>' . _('料号') . ':</div>';
echo '<input type="text" name="item_no" value="' . $_POST['item_no'] . '" size="20" maxlength="25" /></div>';
echo '<div class="text-nav-1"><div>' . _('料号名称') . ':</div>';
echo '<input type="text" name="item_name" value="' . $_POST['item_name'] . '" size="20" maxlength="25" /></div>'; 
echo '<div class="text-nav-1"><div>' . _('规格型号') . ':</div>';
echo '<input type="text" name="item_desc" value="' . $_POST['item_desc'] . '" size="20" maxlength="25" /></div>';
echo '<div class="text-nav-1"><div>' . _('仓库') . ':</div>';
echo '<input type="text" name="loccode_from" value="' . $_POST['loccode_from'] . '" size="20" maxlength="25" /></div>';

if (!isset($_POST['FromDate'])) {
    $_POST['FromDate'] = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 30, date("Y")));
}
if (!isset($_POST['ToDate'])) {
    $_POST['ToDate'] = Date('Y-m-d');
}
echo '<div class="text-nav-1"><div>' . _('交易日期起') . ':</div>';
echo '<input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" autocomplete="off" name="FromDate" maxlength="10" size="20" value="' . $_POST['FromDate'] . '"  /></div>';

echo '<div class="text-nav-1"><div>' . _('交易日期止') . ':</div>'; 
echo '<input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" autocomplete="off" name="ToDate" maxlength="10" size="20" value="' . $_POST['ToDate'] . '"  /> </div>';;
echo '</table><div class="centre"><input type="submit" name="Search" value="查找"> </div>'. '</br>';

if (isset($_POST['Search']) and isset($result) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])) {
    $ListCount = DB_num_rows($result);
    $ListPageMax = ceil($ListCount / $_SESSION['DisplayRecordsMax']);

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
    echo '<input type="hidden" name="PageOffset" value="' . $_POST['PageOffset'] . '" />';
    if ($ListPageMax > 1) {
        echo '<br /><div class="centre">&nbsp;&nbsp;' . _('第') . '' . $_POST['PageOffset'] . ' ' . _('页，共') . ' ' . $ListPageMax . ' ' . _('页') . '. ' . _('转到页') . ': ';
        echo '<select name="PageOffset1">';
        $ListPage = 1;
        while ($ListPage <= $ListPageMax) {
            if ($ListPage == $_POST['PageOffset']) {
                echo '<option value="' . $ListPage . '" selected="selected">' . $ListPage . '</option>';
            } else {
                echo '<option value="' . $ListPage . '">' . $ListPage . '</option>';
            }
            $ListPage++;
        }
        echo '</select>
                    <input type="submit" name="Go1" value="' . _('转到') . '" />
                    <input type="submit" name="Previous" value="' . _('上一页') . '" />
                    <input type="submit" name="Next" value="' . _('下一页') . '" />';
        echo '</div>';
    }
    echo '<div class="text-nav-table">
                    <table cellpadding="2" class="selection" >';

    echo '<tr>
                    <th bgcolor="#87CEFA" class="ascending"  >' . _('问题反馈单号') . '</th>
                    <th bgcolor="#87CEFA"  width = 70>' . _('仪器SN号') . '</th>
                    <th bgcolor="#87CEFA"  width = 70>' . _('仪器规格型号') . '</th>
                    <th bgcolor="#87CEFA"  width = 70>' . _('试剂（耗材）批号') . '</th>
                    <th bgcolor="#87CEFA"  width = 70>' . _('试剂（耗材）类型') . '</th>
                    <th bgcolor="#87CEFA" class="ascending"  >' . _('售后工单号') . '</th>
                    <th bgcolor="#87CEFA"  width = 70>' . _('交易类型') . '</th>
                    <th bgcolor="#87CEFA" class="ascending" width = 110>' . _('料号') . '</th>
					 <th  bgcolor="#87CEFA"  width = 190>' . _('料号名称') . '</th>
					 <th bgcolor="#87CEFA"   width = 90>' . _('规格型号') . '</th> 
                    <th bgcolor="#87CEFA"   >' . _('交易数量') . '</th> 
					<th bgcolor="#87CEFA"   >' . _('仓库') . '</th>
					<th bgcolor="#87CEFA"  width = 80>' . _('交易单号') . '</th>       
                    <th bgcolor="#87CEFA" width = 180>' . _('备注') . '</th>   
                    <th bgcolor="#87CEFA" width = 80>' . _('做账人员') . '</th>
                    <th bgcolor="#87CEFA" class="ascending" width = 160>' . _('交易日期') . '</th>
            </tr>';
    $k = 0; //row counter to determine background colour
    $RowIndex = 0;



    if (DB_num_rows($result) <> 0) {
        DB_data_seek($result, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);
        $k = 0; //counter for input controls
        while (($myrow = DB_fetch_array($result)) AND ( $RowIndex <> $_SESSION['DisplayRecordsMax'] )) {
            if ($k == 1) {
                echo '<tr class="EvenTableRows">';
                $k = 0;
            } else {
                echo '<tr class="OddTableRows">';
                $k = 1;
            }			 

			  echo '<td>'. $myrow['lianluodan'] . '</td>
              <td>' . $myrow['yiqi_sn'] . '</td> 
              <td>' . $myrow['yiqi_desc'] . '</td> 
              <td>' . $myrow['lot_num'] . '</td> 
              <td>' . $myrow['shiji_desc'] . '</td> 
                    <td>'. $myrow['wip_entity_name'] . '</td>
				<td>' .  $myrow['transaction_type']. '</td>
                    <td>' . $myrow['item_no'] . '</td> 
					<td>' . $myrow['item_name'] . '</td> 
					<td>' . $myrow['item_desc'] . '</td>  
					<td>' . $myrow['quantity'] . '</td> 
					<td>' . $myrow['subinventory_from'] . '</td>
					<td>' . $myrow['trans_num'] . '</td> 
					<td>' . $myrow['remark'] . '</td> 
					<td>' . $myrow['created_by'] . '</td> 
                   <td>' . date('Y-m-d h:i:s', $myrow['transaction_date']) . '</td>
             ';
			 
            echo '
			</tr>';
           
            $RowIndex++;
            //end of page full new headings if
        } //end loop through vendors
        echo '</table></div>';
 
        echo '<input type="hidden" name="JustSelectedAvendor" value="Yes" />';
    }

    if (isset($ListPageMax) AND $ListPageMax > 1) {
        echo '<br /><div class="centre">&nbsp;&nbsp;' . _('第') . '' . $_POST['PageOffset'] . ' ' . _('页，共') . ' ' . $ListPageMax . ' ' . _('页') . '. ' . _('转到页') . ': ';
        echo '<select name="PageOffset2">';
        $ListPage = 1;
        while ($ListPage <= $ListPageMax) {
            if ($ListPage == $_POST['PageOffset']) {
                echo '<option value="' . $ListPage . '" selected="selected">' . $ListPage . '</option>';
            } else {
                echo '<option value="' . $ListPage . '">' . $ListPage . '</option>';
            }
            $ListPage++;
        }
        echo '</select>
                        <input type="submit" name="Go2" value="' . _('转到') . '" />
                        <input type="submit" name="Previous" value="' . _('上一页') . '" />
                        <input type="submit" name="Next" value="' . _('下一页') . '" />';
        echo '</div>';
    }
}
echo '</div></form>';

include('includes/footer.inc');
