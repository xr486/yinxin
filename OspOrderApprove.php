<?php
 ob_start();
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include('includes/session.inc');
$Title = _('外协采购单审核');
$ViewTopic= '外协采购单审核';
$BookMark = '外协采购单审核';

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
$sql = "SELECT po_num, 
status , note, a.creation_date, a.order_date, a.need_date, a.po_all_amount, a.vendor_code, b.vendor_name, 1, a.created_by
FROM po_headers_all a, vendors b
WHERE status in( 'INPROCESS','REJECTED')
AND a.vendor_code = b.vendor_code
"	; 
$sql .= " order by  po_num desc ";
	
    $result = DB_query($sql,$db);
if(isset($_POST['Search']) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])){
    $sql = "SELECT po_num, 
status , note, a.creation_date, a.order_date, a.need_date, a.po_all_amount, a.vendor_code, b.vendor_name, 1, a.created_by
FROM po_headers_all a, vendors b
WHERE status in( 'INPROCESS','REJECTED')
AND a.vendor_code = b.vendor_code
"	; 
   
    if(isset($_POST['po_num']) and $_POST['po_num'] != ''){
         $sql = $sql . " and po_num " . LIKE . " '%" . $_POST['po_num'] . "%' ";
    }
 
    if (isset($_POST['vendor']) and $_POST['vendor'] != '') {
        $sql = $sql . " and b.vendor_code =  '" . $_POST['vendor'] . "'";
    }  
     if (isset($_POST['vendor_name']) and $_POST['vendor_name'] != '') {
        $sql = $sql . " and vendor_name " . LIKE . " '%" . $_POST['vendor_name'] . "%' ";
    }
     if (empty($_POST['FromDate']) == 0) {
        $SQL_FromDate = strtotime($_POST['FromDate']);
        $sql .= " and order_date >= '" . $SQL_FromDate . "' ";
    }
    if (empty($_POST['ToDate']) == 0) {
        $SQL_ToDate = strtotime($_POST['ToDate']) + 86400;
        //echo $SQL_ToDate;
        $sql .= " and order_date <='" . $SQL_ToDate . "' ";
    }
	$sql .= " order by  po_num desc ";
	
    $result = DB_query($sql,$db);
    if (DB_num_rows($result)==0) {
       // unset($result);
        prnMsg(_('找不到该外协采购单，请重新输入条件查询！') ,'error');
    }
}


echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('外协采购单审核') . '</p>';
echo '<table cellpadding="3" class="selection">';
echo '<div class="text-nav">';

echo '<div class="text-nav-1"><div>' . _('外协采购单号') . ':</div>';
echo '<input type="text"   autocomplete="off"   name="po_num" value="' . $_POST['po_num'] . '" size="20" maxlength="25" /></div>';
  

echo '<div class="text-nav-1"><div>' . _('供应商代号') . ':</div>
';
echo '<input type="text"   autocomplete="off"   id="text_slect_vendor" name="vendor" value="' . $_POST['vendor'] . '" size="20" maxlength="25" />
<image class="select_img" src="img/search.png" id="btn_slect_vendor_a"/>
</div>';
echo '<div class="text-nav-1"><div>' . _('供应商名称') . ':</div>';
echo '<input type="text"   autocomplete="off"   id="text_slect_name" name="vendor_name" value="' . $_POST['vendor_name'] . '" size="20" maxlength="25" />
<image class="select_img" src="img/search.png" id="btn_slect_vendor"/>
</div>';

if (!isset($_POST['FromDate'])) {
    $_POST['FromDate'] = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 30, date("Y")));
}
if (!isset($_POST['ToDate'])) {
    $_POST['ToDate'] = Date('Y-m-d');
}
echo '<div class="text-nav-1"><div>' . '采购日期' . _('起') . ':</div>
		<input type="text"   autocomplete="off"   onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="FromDate" maxlength="10" size="11" value="" /></div>
		<div class="text-nav-1"><div>' . _('采购日期止') . ':</div>
		<input type="text"   autocomplete="off"   onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="ToDate" maxlength="10" size="11" value="" /></div>
	</div>';

echo '</table><div class="centre"><input type="submit" name="Search" value="查找"> </div>'
. '</br>';

if (isset($_POST['Search']) or isset($result) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])){
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
                    <th class="ascending"width = 100>' . _('外协采购单号') . '</th>
                    <th class="ascending"width = 80>'  . _('状态') . '</th>
					<th class="ascending"width = 120>' . _('供应商编号') . '</th>
                    <th class="ascending"width = 120>' . _('供应商名称') . '</th>
                    <th class="ascending"width = 100>' . _('总额') . '</th>
                    <th class="ascending"width = 300>' . _('备注') . '</th>
                    <th class="ascending"width = 100>' . _('采购日期') . '</th>              <th class="ascending"width = 100>' . _('需求日期') . '</th>
                    <th class="ascending"width = 100>' . _('下单时间') . '</th>
                    <th class="ascending"width = 80>'  . _('下单人员') . '</th>
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
			if($myrow['status'] == 'INPROCESS') {
                            $v_status = '待签核';
                        }elseif($myrow['status'] == 'APPROVED'){
                            $v_status = '已签核';
                        }elseif($myrow['status'] == 'REJECTED'){
                            $v_status = '已拒签';
                        }else{
                             $v_status = '已取消';
                        }
			echo '  <td><a href="' . $RootPath . '/OspOrderApprove2.php?Updatepo_num=' . $myrow['po_num'] . '">' . $myrow['po_num'] . '</td>
				<td>' . $v_status . '</td>
				<td>' . $myrow['vendor_code'] . '</td>  
                <td>' . $myrow['vendor_name'] . '</td>  
                <td>' . $myrow['po_all_amount'] . '</td> 
				<td>' . $myrow['note'] . '</td>
                <td>' . date('Y-m-d', $myrow['order_date']) . '</td>                                    
				<td>' . date('Y-m-d', $myrow['need_date']) . '</td>
                <td>' . date('Y-m-d  H:i:s', $myrow['creation_date']) . '</td>
				<td>' . $myrow['created_by'] . '</td>
                                 ';

       
			echo '
			</tr>';
			$i++;
			$RowIndex++;
			//end of page full new headings if
		} //end loop through vendors
        echo '</table>
        </div>';
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

include('includes/footer.inc');
?>
<script type="text/javascript">
    $('#btn_slect_vendor').dialog({
        title: '选择供应商',
        width: '950px',
        height: 470,
        content: 'url:BtnSearchVendor517.php?fwValue=&cat=buliao',
        init: function() {
            this.content.document.getElementById('cat').value = 'buliao';
            this.content.document.getElementById('fwValue').value = '';
        }
    });
    $('#btn_slect_vendor_a').dialog({
        title: '选择供应商',
        width: '950px',
        height: 470,
        content: 'url:BtnSearchVendor517.php?fwValue=&cat=buliao',
        init: function() {
            this.content.document.getElementById('cat').value = 'buliao';
            this.content.document.getElementById('fwValue').value = '';
        }
    });
</script>