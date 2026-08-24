<?php
 ob_start();
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include('includes/session.inc');
$Title = _('料号交易明细表');
$ViewTopic= '料号交易明细表';
$BookMark = '料号交易明细表';

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
 

  $sql = "SELECT a.trans_num,a.transaction_type,
                   c.item_no, a.uom, c.item_name,
                   a.subinventory_from,a.request_person,
                   d.loccode, d.locationname,  
                   a.request_person,(SELECT b.employee_name       
                                     FROM hr_employees b
                                     WHERE a.request_person = b.employee_num) 
                   employee_name, 
                   c.item_desc, a.quantity,
                   a.creation_date,a.remark,
                   a.transaction_date
            FROM inv_transactions_all a,sf_item_no c, 
                 locations d
            WHERE   a.item_no = c.item_no 
            AND a.subinventory_from = d.loccode ";
if (isset($_GET['item_no']) and $_GET['item_no'] != '') { 		
    $sql = $sql." and c.item_no ".LIKE." '%".$_GET['item_no']."%' ";
} 
if (isset($_GET['subinventory_code']) and $_GET['subinventory_code'] != ''  and $_GET['subinventory_code'] != '全部') { 		
    $sql = $sql." and a.subinventory_from ".LIKE." '%".$_GET['subinventory_code']."%' ";
} 
if (empty($_GET['FromDate']) == 0) 
{
  $SQL_FromDate = strtotime($_GET['FromDate']);
  $sql .= " and a.last_update_date >= '" . $SQL_FromDate . "' ";
}
if (empty($_GET['ToDate']) == 0) 
{
   $SQL_ToDate = strtotime($_GET['ToDate']) + 86400;
   $sql .= " and a.last_update_date <='" . $SQL_ToDate . "' ";
}
			  $sql = $sql . " order by a.creation_date desc";
			 $result = DB_query($sql,$db);



echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('料号交易明细表') . '</p>';



if (isset($result) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])){
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
						<th  width = 100>' . _('交易类型') . '</th> 
						<th width =100>' . '料号' . '</th>
                        <th width =150>' . '料号名称' . '</th>
						<th width =150>' . '规格型号' . '</th>
						<th width =70 >' . '交易日期' . '</th> 
						<th width =70 >' . '交易数量' . '</th> 
						<th width =70 >' . '单位' . '</th> 
						<th width =60 >' . '仓库' . '</th>
						<th width =60 >' . '仓库' . '</th>
						<th width =80 >' . '申请人工号' . '</th>
						<th  width = 100>' . _('申请人名称') . '</th> 									
						<th  width = 100>' . _('备注') . '</th> 
						<th  width = 100>' . _('单据建立日期') . '</th>                                                               
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
		         
			echo '<td>' . $myrow['trans_num'] . ' </td>';
			echo '<td>' . $myrow['transaction_type'] . '</td>';  
		    echo '<td>' . $myrow['item_no'] . ' </td>';
            echo '<td>' . $myrow['item_name'] . ' </td>';  
            echo '<td>' . $myrow['item_desc'] . ' </td>';
			echo '<td>' . date('Y-m-d',$myrow['transaction_date']) . '</td>';
			echo '<td>' . $myrow['quantity'] . ' </td>'; 
			echo '<td>' . $myrow['uom'] . ' </td>'; 
            if($myrow['transaction_type']=='无订单出货' or $myrow['transaction_type']=='无订单退货'){
                echo '<td></td>';
			    echo '<td> </td>';
            }else{
                echo '<td>' . $myrow['loccode']. ' </td>';
			    echo '<td>' . $myrow['locationname']. ' </td>';
            }
			
            echo '<td>' . $myrow['request_person'] . '</td>';
			echo '<td>' . $myrow['employee_name'] . '</td>';     
			echo '<td>' . $myrow['remark'] . '</td>';   
			echo '<td>' . date('Y-m-d H:i:s',$myrow['creation_date']) . '</td>'; 
            
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
   
    //echo '' . $RootPath . '/InOutSOCheckListExcel.php?C1=' .$_POST['FromDate'] . '&C2=' .$_POST['ToDate'] . '&C3=' .$_POST['Stockid_from'] . '&C4=' .$_POST['Stockid_to'] . '&C5=' .$_POST['trans_num_from'] . '&C6=' .$_POST['trans_num_to'] . '&C7=' .$_POST['loccode_from']. '&C8=' .$_POST['loccode_to']. '&C9=' .$_POST['transaction_type'] . '';
}

echo '</div></form>';
if (isset($_POST['add_new'])) {
    header('Location: AddSupplier.php');
}
include('includes/footer.inc');
?>
<script type="text/javascript">
    $('#btn_slect_buliao').dialog({
        title: '选择产品',
        width: '1200px',
        height: 470,
        content: 'url:Searchbuliao517.php?fwValue=&cat=buliao',
        init: function() {
            this.content.document.getElementById('cat').value = 'buliao';
            this.content.document.getElementById('fwValue').value = '';
        }
    });
    $('#btn_slect_buliao2').dialog({
        title: '选择产品',
        width: '1200px',
        height: 470,
        content: 'url:Searchbuliao517.php?fwValue=&cat=buliao',
        init: function() {
            this.content.document.getElementById('cat').value = 'buliao';
            this.content.document.getElementById('fwValue').value = '';
        }
    });
</script>