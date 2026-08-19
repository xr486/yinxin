<?php

 
include('includes/session.inc');

$Title = _('非标材料供需明细查询');
$ViewTopic = '非标材料供需明细查询';
$BookMark = '非标材料供需明细查询';

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
$_SESSION['DisplayRecordsMax']=10;
if(isset($_POST['Search']) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])){
	$SQL_FromDate = FormatDateForSQL($_POST['FromDate']);
    $SQL_ToDate = FormatDateForSQL($_POST['ToDate']);
    $sql ="SELECT a.customer_code,c.order_number,c.line,c.item_no,c.item_name,c.item_description ,c.userd_per_quantity ,c.userd_quantity,c.uom,b.need_date,d.po_num po_number,e.line po_line,c.po_quantity
	FROM so_headers_all a, so_lines_all b ,so_line_details_all c,po_headers_all d,po_lines_all e
	   WHERE a.order_number = b.order_number
	   and a.order_number = c.order_number
	   and c.line=b.line
	   and e.so_line_detail_id =c.so_line_detail_id 
	   and e.po_num=d.po_num
       and c.customer_flag='Y'
	   and c.userd_quantity>0
	   and po_quantity>0" ;
   	 	  	 	
if (empty($_POST['FromDate'])==0) {
    $SQL_FromDate = strtotime( $_POST['FromDate']);
    //echo $SQL_FromDate;
    $sql .= " and  b.need_date >= '" . $SQL_FromDate . "' ";
}
if (empty($_POST['ToDate'])==0) {
    $SQL_ToDate = strtotime( $_POST['ToDate']) + 86400;
     //echo $SQL_ToDate;
    $sql .= " and  b.need_date <='" . $SQL_ToDate . "' ";
}

 
if (isset($_POST['Stockid_from']) and $_POST['Stockid_from'] != '') {
        $sql = $sql . " and c.item_no >=  '" . $_POST['Stockid_from'] . "'";
    }
if (isset($_POST['Stockid_to']) and $_POST['Stockid_to'] != '') {
        $sql = $sql . " and c.item_no <=  '" . $_POST['Stockid_to'] . "' ";
    }
if (isset($_POST['orderno']) and $_POST['orderno'] != '') {
        $sql = $sql . " and c.order_number =  '" . $_POST['orderno'] . "' ";
    }
	 
	if (isset($_POST['customer_code']) and $_POST['customer_code'] != '') {
        $sql = $sql . " and a.customer_code " . LIKE . " '%" . $_POST['customer_code'] . "%' ";
    }

	if (isset($_POST['vendor_code']) and $_POST['vendor_code'] != '') {
        $sql = $sql . " and d.vendor_code " . LIKE . " '%" . $_POST['vendor_code'] . "%' ";
    }





  $sql = $sql." order by b.need_date ";
    $result = DB_query($sql,$db);
    if (DB_num_rows($result)==0) {
        unset($result);
        prnMsg(_('未能找到相关资料，请重新输入条件查询！') ,'error');
    }

	
}


echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('非标材料供需明细查询') . '</p>';
echo '<table cellpadding="3" class="selection">';
 
 
if (!isset($_POST['FromDate2'])) {
    $_POST['FromDate2'] = date( "Y-m-d", mktime(0,0,0,date("m"),date("d")-20,date("Y")));
}
if (!isset($_POST['ToDate2'])) {
    $_POST['ToDate2'] = date( "Y-m-d", mktime(0,0,0,date("m"),date("d")+30,date("Y")));
}

// $aa=10;
//echo date('Y-m-d',strtotime('-'. $aa.'day','1246982400'));
  
echo '  <td>' . _('需求日期从') . ':</td>
		<td>';
echo '<input type="text" name="FromDate"   onfocus="WdatePicker()"  value="' . $_POST['FromDate'] . '" size="20" maxlength="25" /></td>';
echo ' <td>' . _('到日期') . ':</td>
		<td>';
echo '<input type="text" name="ToDate"  onfocus="WdatePicker()"  value="' . $_POST['ToDate'] . '" size="20" maxlength="25" /></td>';
echo ' <td>' . _('需求单号') . ':</td> <td>';
echo '<input type="text" name="orderno"     value="' . $_POST['orderno'] . '" size="20" maxlength="35" /></td>';
echo ' <td>' . _('客户编号') . ':</td> <td>';
echo '<input type="text" name="customer_code"     value="' . $_POST['customer_code'] . '" size="5" maxlength="35" /></td>';
echo '</tr>';
echo '</tr>';
echo '  <td>' . _('建议下单日从') . ':</td>
		<td>';
echo '<input type="text" name="FromDate2"   onfocus="WdatePicker()"  value="' . $_POST['FromDate2'] . '" size="20" maxlength="25" /></td>';
echo ' <td>' . _('到日期') . ':</td>
		<td>';
echo '<input type="text" name="ToDate2"  onfocus="WdatePicker()"  value="' . $_POST['ToDate2'] . '" size="20" maxlength="25" /></td>';
echo ' <td>' . _('料号起') . ':</td>
		<td>';
echo '<input type="text" name="Stockid_from"     value="' . $_POST['Stockid_from'] . '" size="20" maxlength="25" /></td>';
//echo '</tr>';
echo ' <td>' . _('到料号止') . ':</td>
		<td>';
echo '<input type="text" name="Stockid_to"   value="' . $_POST['Stockid_to'] . '" size="20" maxlength="25" /></td>';
echo ' <td>' . _('供应商编号') . ':</td> <td>';
echo '<input type="text" name="vendor_code"     value="' . $_POST['vendor_code'] . '" size="5" maxlength="35" /></td>';
echo '</tr>';
 /*
echo '<td>' . _('From') . ':</td>
		<td><input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="FromDate" maxlength="10" size="11" value="' . $_POST['FromDate'] . '" /></td>
		<td>' . _('To') . ':</td>
		<td><input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="ToDate" maxlength="10" size="11" value="' . $_POST['ToDate'] . '" /></td>';

*/
echo '</table><div class="centre"><input type="submit" name="Search" value="查找"></div>';

 

if (isset($_POST['Search']) and isset($result) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])){
    $ListCount = DB_num_rows($result);
    $ListPageMax = ceil($ListCount / 10);//$_SESSION['DisplayRecordsMax']
    
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
            echo '<br /><div class="centre">&nbsp;&nbsp;' . $_POST['PageOffset'] . ' ' . _('of') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ': ';
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
    echo '<br />
                    <table cellpadding="2" class="selection">';

    echo '<tr>  
				<th >' . _('客户编号') . '</th>
				<th >' . _('订单号码') . '</th>
				<th >' . _('行') . '</th>
                     <th >' . _('料号') . '</th>
					 <th >' . _('料号名称') . '</th>
					 <th >' . _('规格型号') . '</th>
				<th >' . _('单位') . '</th>
				<th >' . _('单耗') . '</th>
				<th >' . _('需求数量') . '</th> 
				<th >' . _('需求日期') . '</th>
				<th >' . _('采购单号') . '</th>
				<th >' . _('采购行') . '</th> 
				<th >' . _('采购量') . '</th> 
				<th >' . _('预计到货日') . '</th>
				

            </tr>'; 
    $k = 0; //row counter to determine background colour
    $RowIndex = 0;
    $v_all_waitamount = 0;
	$v_waitamount = 0; 
    if (DB_num_rows($result) <> 0) {
        DB_data_seek($result, ($_POST['PageOffset'] - 1) * 10);//$_SESSION['DisplayRecordsMax']
        $i = 0; //counter for input controls
        while (($myrow = DB_fetch_array($result)) AND ($RowIndex <> 10)) {//$_SESSION['DisplayRecordsMax'])
			if ($k == 1) {
				echo '<tr class="EvenTableRows">';
				$k = 0;
			} else {
				echo '<tr class="OddTableRows">';
				$k = 1;
			} 	 
 
        	echo '
			   <td>' . $myrow['customer_code'] . '</td>
			   <td>' . $myrow['order_number'] . '</td>
			   <td>' . $myrow['line'] . '</td>
			   <td>' . $myrow['item_no'] . '</td>
			   <td>' . $myrow['item_name'] . '</td>
			   <td>' . $myrow['item_description'] . '</td>
			   <td>' . $myrow['uom'] . '</td> 
				<td>' . $myrow['userd_per_quantity'] . '</td>	
				<td>' . $myrow['userd_quantity'] . '</td>					
			   <td>' . date('Y-m-d',$myrow['need_date']) . '</td>
			    <td>' . $myrow['po_number'] . '</td> 
				<td>' . $myrow['po_line'] . '</td>	
				<td>' . $myrow['po_quantity'] . '</td>	 
				 <td>' . date('Y-m-d',$myrow['po_sch_receive_date']) . '</td>
			</tr>';
			$i++;
			$RowIndex++;
			//end of page full new headings if
		} //end loop through customers

		//echo '  <td> '.'合计'.'</td> <td></td> <td></td> <td></td> <td></td> <td></td> <td> '.$v_all_waitamount.'</td>  <td> </td>  <td> </td> <td>' //.$v_all_waitamount  . '</td> ';
		echo '</table>';
		echo '<input type="hidden" name="JustSelectedACustomer" value="Yes" />';
	}

        if (isset($ListPageMax) AND $ListPageMax > 1) {
                echo '<br /><div class="centre">&nbsp;&nbsp;' . $_POST['PageOffset'] . ' ' . _('of') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ': ';
                echo '<select name="PageOffset2">';
                $ListPage = 1;
                while ($ListPage <= $ListPageMax) {
                        if ($ListPage == $_POST['PageOffset']) {
                                echo '<option value="' . $ListPage . '" selected="selected">' . $ListPage . '</option>';
                        } //$ListPage == $_POST['PageOffset']
                        else {
                                echo '<option value="' . $ListPage . '">' . $ListPage . '</option>';
                        }
                        $ListPage++;
                } //$ListPage <= $ListPageMax
                echo '</select>
                        <input type="submit" name="Go2" value="' . _('Go') . '" />
                        <input type="submit" name="Previous" value="' . _('Previous') . '" />
                        <input type="submit" name="Next" value="' . _('Next') . '" />';
                echo '</div>';
    }//end if results to show

}

echo '</div>
      </form>';
include('includes/footer.inc');
?>

