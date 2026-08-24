<?php

ob_start();
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include('includes/session.inc');
$Title = _('MRP计算');
$ViewTopic = 'MRP计算';
$BookMark = 'MRP计算';

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

if (isset($_POST['Save'])) {  
$sql8 = "update wip_material_requierments  
        set fenpei_quantity =0
		and wip_entity_name in (select wip_entity_name from wip_jobs_all where status_type='开始' )";
        $result2 = DB_query($sql8, $db);
  
   $sql8 = "delete from wip_inv_onhand_temp ";
        $result2 = DB_query($sql8, $db);
  $sql8 = "delete from wip_inv_onhand_detail_temp ";
        $result2 = DB_query($sql8, $db);

  $sql2 = "insert into wip_inv_onhand_detail_temp  (stockid,type,source,quantity )
    select stockid,'库存',subinventory_code,sum(quantity) 
	from inv_onhand_quantity_all 
	group by stockid,subinventory_code ";
   $result2 = DB_query($sql2, $db);


   $sql2 = "insert into wip_inv_onhand_detail_temp  (stockid,type,source,quantity )
     SELECT pla.stockid, '采购', CONCAT( pha.po_num, '-', pla.line ) , (pla.quantity - pla.quantity_received)
FROM po_headers_all pha, po_lines_all pla
WHERE pla.po_num = pha.po_num
AND pha.status = '已签核'
AND pla.quantity - pla.quantity_received >0
GROUP BY pla.stockid, CONCAT( pha.po_num, '-', pla.line ) ";
   $result2 = DB_query($sql2, $db);

   $sql2 = "insert into wip_inv_onhand_detail_temp  (stockid,type,source,quantity )
     SELECT pla.stockid, '待检验', CONCAT( pla.po_num, '-', pla.po_line ) ,  wait_inspect_quantity 
FROM po_rcv_receipt_line pla
WHERE  wait_inspect_quantity >0
GROUP BY pla.stockid, CONCAT( pla.po_num, '-', pla.po_line ) ";
   $result2 = DB_query($sql2, $db);

    $sql2 = "insert into wip_inv_onhand_detail_temp  (stockid,type,source,quantity )
     SELECT pla.stockid, '待入库', CONCAT( pla.po_num, '-', pla.po_line ) ,  wait_delivery_quantity 
FROM po_rcv_receipt_line pla
WHERE  wait_delivery_quantity  >0
GROUP BY pla.stockid, CONCAT( pla.po_num, '-', pla.po_line ) ";
   $result2 = DB_query($sql2, $db);

   $sql2 = "insert into wip_inv_onhand_detail_temp  (stockid,type,source,quantity )
    select b.segment1,'超领料',a.wip_entity_name, (quantity_issued - b.required_quantity   - chaohao_quantity ) 
	from wip_jobs_all a,wip_material_requierments b
	 where b.wip_entity_name =a.wip_entity_name and a.status_type='开始'  
	 and   ( quantity_issued -b.required_quantity -  chaohao_quantity ) >0
	group by b.segment1,a.wip_entity_name ";
   $result2 = DB_query($sql2, $db);


   $sql2 = "insert into wip_inv_onhand_temp  ( stockid,onhand_quantity )
    select stockid,sum(quantity) 
	from wip_inv_onhand_detail_temp group by stockid ";
   $result2 = DB_query($sql2, $db);

   

   $sql4 = " select a.plan_start_date,b.segment1,c.item_desc,c.item_name,
   operation_seq_num,quantity_issued,b.required_quantity,a.wip_entity_name,b.fenpei_quantity,b.required_quantity - quantity_issued   need_qty
						from wip_jobs_all a,wip_material_requierments b,sf_item_no c 
                        where b.wip_entity_name =a.wip_entity_name and b.segment1=c.item_no 
						and a.status_type='开始' 
						and b.required_quantity - quantity_issued  >0
						and b.segment1 in (select stockid from wip_inv_onhand_temp ) 
						order by a.plan_start_date,a.wip_entity_name";
   $result4 = DB_query($sql4, $db);
   while ($myrow4 = DB_fetch_array($result4)) {
	   $shengyu_qty=0;
      $sql3 = "select onhand_quantity-fenpei_quantity shengyu_qty from wip_inv_onhand_temp 
	  where stockid ='".$myrow4['segment1']."' 
	  and onhand_quantity>fenpei_quantity  ";
     $result3 = DB_query($sql3, $db);
	 $myrow3 = DB_fetch_array($result3);
     $shengyu_qty=$myrow3['shengyu_qty'];
	 if ($shengyu_qty>0) {
	  if ( $shengyu_qty > $myrow4['need_qty'] ) {

		  $sql2 = "update wip_inv_onhand_temp  
        set fenpei_quantity =fenpei_quantity + '".$myrow4['need_qty']."' 
		where stockid='".$myrow4['segment1']."'  ";
        $result2 = DB_query($sql2, $db);

		$sql2 = "update wip_material_requierments  
        set fenpei_quantity =fenpei_quantity + '".$myrow4['need_qty']."' 
		where segment1='".$myrow4['segment1']."'
		and operation_seq_num='".$myrow4['operation_seq_num']."'
		and wip_entity_name='".$myrow4['wip_entity_name']."'  ";
        $result2 = DB_query($sql2, $db);
 
	  }  else {
	    $sql2 = "update wip_inv_onhand_temp  
        set fenpei_quantity =fenpei_quantity + '".$shengyu_qty."' 
		where stockid='".$myrow4['segment1']."'  ";
        //echo $sql2;
		$result2 = DB_query($sql2, $db);

		$sql2 = "update wip_material_requierments  
        set fenpei_quantity =fenpei_quantity + '".$shengyu_qty."' 
		where segment1 ='".$myrow4['segment1']."'
		and operation_seq_num='".$myrow4['operation_seq_num']."' 
		and wip_entity_name='".$myrow4['wip_entity_name']."' ";
		//echo $sql2;
        $result2 = DB_query($sql2, $db);
	  
	  }
	 
	 }

   }
   }

if (isset($_POST['Search']) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])) {
   
   

    $sql = " select d.item_no mitem_no,d.item_name mitem_name,a.plan_start_date,a.so_header_number,a.so_line_number,b.segment1,b.comments,c.item_desc,c.item_name,c.units,quantity_per_assembly,quantity_issued,b.required_quantity,a.wip_entity_name,b.fenpei_quantity,b.required_quantity - quantity_issued - fenpei_quantity need_qty
						from wip_jobs_all a,wip_material_requierments b,sf_item_no c ,sf_item_no d
                        where b.wip_entity_name =a.wip_entity_name and b.segment1=c.item_no and a.primary_item = d.item_no
						and a.status_type='开始' 
						and b.required_quantity - quantity_issued - fenpei_quantity >0
						 ";

    
     if (isset($_POST['wip_entity_name']) and $_POST['wip_entity_name'] != '') { 
		$sql = $sql . " and a.wip_entity_name " . LIKE . " '%" . $_POST['wip_entity_name'] . "%' ";
    }
 
    if (isset($_POST['item_no']) and $_POST['item_no'] != '') {
        $sql = $sql . " and b.segment1 " . LIKE . " '%" . $_POST['item_no'] . "%' ";
    }
	 if (isset($_POST['item_name']) and $_POST['item_name'] != '') {
        $sql = $sql . " and c.item_name " . LIKE . " '%" . $_POST['item_name'] . "%' ";
    }
	if (isset($_POST['item_desc']) and $_POST['item_desc'] != '') {
        $sql = $sql . " and c.item_desc " . LIKE . " '%" . $_POST['item_desc'] . "%' ";
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

	 $sql .= " order by a.plan_start_date,a.wip_entity_name "; 
      
    $result = DB_query($sql, $db);
    if (DB_num_rows($result) == 0) {
        unset($result);
        prnMsg(_('找不到该工单，请重新输入条件查询！'), 'error');
    }
}


echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('MRP计算') . '</p>';

echo '</table><div class="centre"><input type="submit" name="Save" value="计算">  </div>  </br>';
 
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
        echo '<br /><div class="centre">&nbsp;&nbsp;' . _('第') . '' . $_POST['PageOffset'] . ' ' . _('页，共') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ': ';
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
                    <input type="submit" name="Go1" value="' . _('Go') . '" />
                    <input type="submit" name="Previous" value="' . _('Previous') . '" />
                    <input type="submit" name="Next" value="' . _('Next') . '" />';
        echo '</div>';
    }
    echo '<div class="text-nav-table">
                    <table cellpadding="2" class="selection" >';
 
    echo '<tr>       <th bgcolor="#87CEFA"    >' . _('工单号') . '</th> 
	<th bgcolor="#87CEFA"    >' . _('产品料号') . '</th> 
	  <th bgcolor="#87CEFA"    >' . _('产品名称') . '</th>
                    <th bgcolor="#87CEFA"   width = 90>' . _('开工日期') . '</th>
                    <th bgcolor="#87CEFA"  width = 100>' . _('料号') . '</th>
					 <th bgcolor="#87CEFA"   >' . _('料号名称') . '</th>
					 <th bgcolor="#87CEFA"  >' . _('规格型号') . '</th>
					 <th bgcolor="#87CEFA"  >' . _('备注') . '</th>
					 <th bgcolor="#87CEFA"  >' . _('单位') . '</th>
                    <th bgcolor="#87CEFA"  width = 70>' . _('需求数量') . '</th> 
                    <th bgcolor="#87CEFA" width = 90>' . _('已领料数量') . '</th>
					<th bgcolor="#87CEFA"  width = 70>' . _('分配数量') . '</th> 
					<th bgcolor="#87CEFA"  width = 70>' . _('缺料数量') . '</th> 
            </tr>';
    $k = 0; //row counter to determine background colour 
    $RowIndex = 0;
  
    if (DB_num_rows($result) <> 0) {
        DB_data_seek($result, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);
        $i = 0; //counter for input controls
        while (($myrow = DB_fetch_array($result)) AND ( $RowIndex <> $_SESSION['DisplayRecordsMax'] )) {
            if ($k == 1) {
                echo '<tr class="EvenTableRows">';
                $k = 0;
            } else {
                echo '<tr class="OddTableRows">';
                $k = 1;
            }
			 
           echo '
		    
		   <td>'. $myrow['wip_entity_name'] . '</td> 
		    <td>' . $myrow['mitem_no'] . '</td> 
					<td>' . $myrow['mitem_name'] . '</td> 
                   <td>' . date('Y-m-d', $myrow['plan_start_date']) . '</td>
                    <td>' . $myrow['segment1'] . '</td> 
					<td>' . $myrow['item_name'] . '</td> 
					<td>' . $myrow['item_desc'] . '</td> 
					<td>' . $myrow['comments'] . '</td> 
					<td>' . $myrow['units'] . '</td>  
					<td>' . $myrow['required_quantity'] . '</td> 
					<td>' . $myrow['quantity_issued'] . '</td> 
					<td>' . $myrow['fenpei_quantity'] . '</td> 
					<td>' . $myrow['need_qty'] . '</td>  
                                 ';
    
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
        echo '<br /><div class="centre">&nbsp;&nbsp;' . _('第') . '' . $_POST['PageOffset'] . ' ' . _('页，共') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ': ';
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
                        <input type="submit" name="Go2" value="' . _('Go') . '" />
                        <input type="submit" name="Previous" value="' . _('Previous') . '" />
                        <input type="submit" name="Next" value="' . _('Next') . '" />';
        echo '</div>';
    }

	    echo '<br>
          <div class="centre">
                <a href="' . $RootPath . '/WIPShortMaterialExcel.php?wip_entity_name=' .$_POST['wip_entity_name'] . '&item_no=' .$_POST['item_no'].'&item_name=' .$_POST['item_name'] .'&item_desc=' .$_POST['item_desc'] . '">
                ' .'资料导出Excel表' . '
                </a>
          </div>';

}
echo '</div></form>';

include('includes/footer.inc');
