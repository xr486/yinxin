<?php
 ob_start();
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include('includes/session.inc');
$Title = _('其他原因出入库状态/打印报表');
$ViewTopic= '其他原因出入库状态/打印报表';
$BookMark = '其他原因出入库状态/打印报表';

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

if(isset($_POST['Search']) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])){
    $sql = "SELECT a.trans_num,a.transaction_type,
                   c.item_no, a.uom, c.item_name,
                   a.subinventory_from,a.request_person,
                   d.loccode,   
                   a.request_person,(SELECT b.employee_name       
                                     FROM hr_employees b
                                     WHERE a.request_person = b.employee_num) 
                   employee_name, 
                   c.item_desc, a.quantity,
                   a.creation_date,a.remark,
                   a.transaction_date,a.created_by ,
                   a.after_onhand,a.lot_num,a.shengchan_date,a.status,a.temp_type
            FROM inv_transactions_all_temp a,sf_item_no c , 
                 locations d
            WHERE   a.item_no = c.item_no 
            AND a.subinventory_from = d.loccode and a.temp_type in ('其他原因出库','其他原因入库','仓库调拨') and a.status <> '拒绝' ";
  
    if(isset($_POST['FromDate']) and $_POST['FromDate'] != ''){
        $sql = $sql." and a.transaction_date >=".strtotime($_POST['FromDate'])." ";
    }
    if(isset($_POST['ToDate']) and $_POST['ToDate'] != ''){
		 $SQL_ToDate = strtotime($_POST['ToDate']) + 86400;
        $sql = $sql." and a.transaction_date <".$SQL_ToDate." ";
    }
    if (isset($_POST['item_no']) and $_POST['item_no'] != '') { 		
        $sql = $sql." and c.item_no ".LIKE." '%".$_POST['item_no']."%' ";
    } 
    if (isset($_POST['item_name']) and $_POST['item_name'] != '') { 		
        $sql = $sql." and c.item_name ".LIKE." '%".$_POST['item_name']."%' ";
    } 
	if (isset($_POST['item_desc']) and $_POST['item_desc'] != '') { 		
        $sql = $sql." and c.item_desc ".LIKE." '%".$_POST['item_desc']."%' ";
    } 
    if (isset($_POST['trans_num']) and $_POST['trans_num'] != '') { 		
        $sql = $sql." and a.trans_num ".LIKE." '%".$_POST['trans_num']."%' ";
    } 
    if (isset($_POST['loccode_from']) and $_POST['loccode_from'] != '') {	
        $sql = $sql." and a.subinventory_from ".LIKE." '%".$_POST['loccode_from']."%' "; 
    } 
	if (isset($_POST['sn']) and $_POST['sn'] != '') {	
        $sql = $sql." and a.sn ".LIKE." '%".$_POST['sn']."%' "; 
    } 
    if (isset($_POST['transaction_type']) and $_POST['transaction_type'] != '') {
        $sql = $sql . " and  a.transaction_type =  '" . $_POST['transaction_type'] . "' ";
    } 
	$sql = $sql . " order by transaction_id desc ";
	 
  
    $result = DB_query($sql,$db);
    if (DB_num_rows($result)==0)
    {
        unset($result);
        prnMsg(_('找不到该单据，请重新输入条件查询！') ,'error');
    }
}


echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('其他原因出入库状态/打印报表') . '</p>';
echo '<table cellpadding="3" class="selection">';
echo '<div class="text-nav">';
 
if (!isset($_POST['FromDate'])) {
    $_POST['FromDate'] = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 30, date("Y")));
}
if (!isset($_POST['ToDate'])) {
    $_POST['ToDate'] = Date('Y-m-d', mktime(0, 0, 0, date("m"), date("d") + 1, date("Y")));
}
echo '<div class="text-nav-1"><div>' . '交易日期' . _('起') . ':</div>
		<input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" autocomplete="off" name="FromDate" maxlength="10" size="11" value="' . $_POST['FromDate'] . '" /></div>
		<div class="text-nav-1"><div>' . _('交易日期止') . ':</div>
		<input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" autocomplete="off" name="ToDate" maxlength="10" size="11" value="' . $_POST['ToDate'] . '" /></div>';
echo '<div class="text-nav-1"><div>' . _('仓库') . ':</div>';
echo '<input type="text" name="loccode_from" value="' . $_POST['loccode_from'] . '" size="20" maxlength="25" /></div>';
echo '<div class="text-nav-1"><div>' . _('料号') . ':</div>';
echo '<input type="text" name="item_no" value="' . $_POST['item_no'] . '" size="20" maxlength="25" /></div>';
echo '<div class="text-nav-1"><div>' . _('料号名称') . ':</div>';
echo '<input type="text" name="item_name" value="' . $_POST['item_name'] . '" size="20" maxlength="25" /></div>'; 
echo '<div class="text-nav-1"><div>' . _('规格型号') . ':</div>';
echo '<input type="text" name="item_desc" value="' . $_POST['item_desc'] . '" size="20" maxlength="25" /></div>';
 
echo '<div class="text-nav-1"><div>' . _('交易单号') . ':</div>';
echo '<input type="text" name="trans_num" value="' . $_POST['trans_num'] . '" size="20" maxlength="25" /></div>';
 
echo '<div class="text-nav-1"><div>' . _('交易类型') . ':</div>';
$sql = "SELECT distinct transaction_type FROM inv_transactions_all_temp ";
$result1 = DB_query($sql, $db);
echo '<select name="transaction_type">';
echo '<option  selected="selected" value=""></option>';
while ($Salesmanrow = DB_fetch_array($result1)) {
       if ($Salesmanrow['transaction_type']==$_POST['transaction_type'] ) {
			 echo ' <option value=' . $Salesmanrow['transaction_type']  . ' selected="selected">  '.  $Salesmanrow['transaction_type'] . ' 
            </option>    ';  
		} 
		 else  {
			
			echo ' <option value=' . $Salesmanrow['transaction_type']  . ' >  '.  $Salesmanrow['transaction_type'] . ' 
            </option>  ';
		
		 } 
}

echo '</select></div>'; 
echo '</div>'; 
 
echo '</table><div class="centre"><input type="submit" name="Search" value="查找"></div>';

if (isset($_POST['Search']) and isset($result) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])){
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
    echo '			  <div class="text-nav-table">
                    <table cellpadding="2" class="selection" >';

    echo '<tr>                       
						<th width =100>' . '交易单号' . '</th>
						<th>' . '状态' . '</th>
						<th  width = 100>' . _('交易类型') . '</th> 
						<th width =100>' . '料号' . '</th>
                        <th width =150>' . '料号名称' . '</th>
						<th width =150>' . '规格型号' . '</th>
						<th >' . '批号/SN号' . '</th>
						<th >' . '生产日期' . '</th>
						
						<th width =70 >' . '交易日期' . '</th> 
						<th width =70 >' . '交易数量' . '</th> 
					
						<th width =70 >' . '单位' . '</th> 
						<th width =60 >' . '仓库' . '</th> 
						<th width =80 >' . '申请人工号' . '</th>
						<th  width = 100>' . _('申请人名称') . '</th> 	 										
						<th  width = 100>' . _('备注') . '</th> 
						<th  width = 100>' . _('建立日期') . '</th>  
						<th  width = 100>' . _('建立人') . '</th>                                                               
						<th>' . _('打印') . '</th>                                                               
            </tr>';
    $k = 0; //row counter to determine background colour
    $RowIndex = 0;
    
     

    if (DB_num_rows($result) <> 0) {
        DB_data_seek($result, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);
        $i = 0; //counter for input controls
        while (($myrow = DB_fetch_array($result)) AND ($RowIndex <> $_SESSION['DisplayRecordsMax'])) {
			if ($k == 1) {
				echo '<tr class="EvenTableRows">';
				$k = 0;
			} else {
				echo '<tr class="OddTableRows">';
				$k = 1;
			} 
		         if($myrow['shengchan_date'] > 0){
                    $shengchan_date = date('Y-m-d',$myrow['shengchan_date']);
                 }else{
                    $shengchan_date = '';

                 }
                 if($myrow['status'] == '开始' and $myrow['temp_type'] == '其他原因出库'){
                    
                    $status = '待部门签核';
                 }else{
                    $status = $myrow['status'];
                 }
			echo '<td>' . $myrow['trans_num'] . ' </td>';
			echo '<td>' . $status . ' </td>';
			echo '<td>' . $myrow['transaction_type'] . '</td>';  
		    echo '<td>' . $myrow['item_no'] . ' </td>';
            echo '<td>' . $myrow['item_name'] . ' </td>';  
            echo '<td>' . $myrow['item_desc'] . ' </td>';
            echo '<td>' . $myrow['lot_num'] . ' </td>';
            echo '<td>' . $shengchan_date . ' </td>';
			echo '<td>' . date('Y-m-d',$myrow['transaction_date']) . '</td>';
			echo '<td>' . $myrow['quantity'] . ' </td>';
        
            echo '<td>' . $myrow['uom'] . ' </td>';
			echo '<td>' . $myrow['loccode']. ' </td>'; 
            echo '<td>' . $myrow['request_person'] . '</td>';
			echo '<td>' . $myrow['employee_name'] . '</td>';   
			echo '<td>' . $myrow['remark'] . '</td>';   
			echo '<td>' . date('Y-m-d H:i:s',$myrow['creation_date']) . '</td>'; 
			echo '<td>' . $myrow['created_by'] . '</td>';  
            if($myrow['temp_type'] == '其他原因出库') {
                
                echo '<td><a href="' . $RootPath . '/PrintOutHouse.php?Updatedelivery_num='.$myrow['trans_num'].'" target="_blank"  >打印</a></td>';   
            }elseif($myrow['temp_type'] == '仓库调拨'){
                echo '<td><a href="' . $RootPath . '/PrintOnChangeHouse.php?Updatedelivery_num='.$myrow['trans_num'].'" target="_blank"  >打印</a></td>';   
                
            }else{
                echo '<td><a href="' . $RootPath . '/PrintInHouse.php?OrderNum='.$myrow['trans_num'].'" target="_blank"  >打印</a></td>';   

            }
            
			echo '
			</tr>';
			$i++;
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
                        } 
                        else {
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
if (isset($_POST['add_new'])) {
    header('Location: AddSupplier.php');
}
include('includes/footer.inc');