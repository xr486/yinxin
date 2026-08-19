<?php
ob_start();
 
include('includes/session.inc');

$Title = _('出货明细查询');
$ViewTopic = 'Inventory';
$BookMark = 'FulfilRequest';

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
$_SESSION['DisplayRecordsMax']=15;
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
    $sql ="SELECT
	b.so_order_number,
	c.customer_code,
	c.customer_name,
	a.delivery_num,
	b.deliveryline,
	b.delivery_quantity,
	b.ship_quantity,
	a.tracking_number,
	a.trackingcompany,
	a.creation_date,
	a.narrative,
	a.created_by,
	b.so_order_number,
	b.so_line_no,
	b.stockid,
	d.p_name item_desc
FROM
	so_delivery_headers_all a,
	so_delivery_all b,
	customers c,
	wip_endproducts d
WHERE
	a.delivery_num = b.delivery_num
AND c.customer_code = a.customer_code
AND b.stockid = d.p_id " ;
 
if (empty($_POST['FromDate'])==0) {
    $SQL_FromDate = strtotime( $_POST['FromDate']);
    //echo $SQL_FromDate;
    $sql .= " and  a.creation_date >= '" . $SQL_FromDate . "' ";
}
if (empty($_POST['ToDate'])==0) {
    $SQL_ToDate = strtotime( $_POST['ToDate']) + 86400;
     //echo $SQL_ToDate;
    $sql .= " and  a.creation_date <='" . $SQL_ToDate . "' ";
}
if (isset($_POST['Stockid_from']) and $_POST['Stockid_from'] != '') {
        $sql = $sql . " and b.stockid >=  '" . $_POST['Stockid_from'] . "'";
    }
if (isset($_POST['Stockid_to']) and $_POST['Stockid_to'] != '') {
        $sql = $sql . " and b.stockid <=  '" . $_POST['Stockid_to'] . "' ";
    }
 if (isset($_POST['so_order_number']) and $_POST['so_order_number']!= '') {
			  $sql = $sql." and b.so_order_number ".LIKE." '%".$_POST['so_order_number']."%' ";
		}

    if(isset($_POST['trackingcompany']) and $_POST['trackingcompany'] != ''){
        $sql = $sql." and trackingcompany ".LIKE." '%".$_POST['trackingcompany']."%' ";
    }

 if (isset($_POST['customer_name']) and $_POST['customer_name']!= '') {
			  $sql = $sql." and customer_name ".LIKE." '%".$_POST['customer_name']."%' ";
		}

 if (isset($_POST['customer_code']) and $_POST['customer_code']!= '') {
			  $sql = $sql." and a.customer_code ".LIKE." '%".$_POST['customer_code']."%' ";
		}
 

    $sql = $sql." order by a.creation_date";
    $result = DB_query($sql,$db);
    if (DB_num_rows($result)==0) {
        unset($result);
        prnMsg(_('未能找到相关资料，请重新输入条件查询！') ,'error');
    }

	
}


echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('出货明细查询') . '</p>';
echo '<table cellpadding="3" class="selection">';
echo '<tr><td colspan="2">' . _('输入部分订单名称') . ':</td><td>';
echo '<input type="text" name="so_order_number" value="' . $_POST['so_order_number'] . '" size="20" maxlength="25" /></td>';
echo '<td><b>' . _('OR') . '</b></td><td>' . _('输入部分物流公司名称') . ':</td>
	<td>';
echo '<input type="text" name="trackingcompany" value="' . $_POST['trackingcompany'] . '" size="20" maxlength="25" /></td>';
echo '</tr>';

echo '<tr><td colspan="2">' . _('输入部分客户名称') . ':</td><td>';
echo '<input type="text" name="customer_name" value="' . $_POST['customer_name'] . '" size="20" maxlength="25" /></td>';
echo '<td><b>' . _('OR') . '</b></td><td>' . _('输入部分客户代号') . ':</td>
	<td>';
echo '<input type="text" name="customer_code" value="' . $_POST['customer_code'] . '" size="20" maxlength="25" /></td>';
echo '</tr>';

if (!isset($_POST['FromDate'])) {
    $_POST['FromDate'] = date( "Y-m-d", mktime(0,0,0,date("m"),date("d")-30,date("Y")));
}
if (!isset($_POST['ToDate'])) {
    $_POST['ToDate'] = Date('Y-m-d');
}

echo '<td><b>' . _('或') . '</b></td>
		<td>' . _('出货日期范围从') . ':</td>
		<td>';
echo '<input type="text" name="FromDate"   onfocus="WdatePicker()"  value="' . $_POST['FromDate'] . '" size="20" maxlength="25" /></td>';
//echo '</tr>';
echo '<td><b>' . _('到') . '</b></td>
		<td>' . _('日期') . ':</td>
		<td>';
echo '<input type="text" name="ToDate"  onfocus="WdatePicker()"  value="' . $_POST['ToDate'] . '" size="20" maxlength="25" /></td>';
echo '</tr>';
echo '<td><b>' . _('或') . '</b></td>
		<td>' . _('料号起') . ':</td>
		<td>';
echo '<input type="text" name="Stockid_from"     value="' . $_POST['Stockid_from'] . '" size="20" maxlength="25" /></td>';
//echo '</tr>';
echo '<td><b>' . _('到') . '</b></td>
		<td>' . _('料号止') . ':</td>
		<td>';
echo '<input type="text" name="Stockid_to"   value="' . $_POST['Stockid_to'] . '" size="20" maxlength="25" /></td>';
echo '</tr>';
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
         echo '<br /><div class="centre">&nbsp;&nbsp;第' . $_POST['PageOffset'] . ' ' . _('页，共') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ': ';
            //echo '<br /><div class="centre">&nbsp;&nbsp;' . $_POST['PageOffset'] . ' ' . _('of') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ': ';
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




    echo '<tr>    <th class="ascending">' . _('客户代码') . '</th>
	            <th class="ascending">' . _('客户名称') . '</th> 
                     <th class="ascending">' . '出货单号码' . '</th>
					 <th  >' . _('出货单行') . '</th>                         
						<th  >' . '出货日期' . '</th>					 
						  <th  >' . _('出货数量') . '</th>  
						  			 
						  <th  >' . _('扣账数量') . '</th> 
						  <th  >' . _('料号') . '</th>
						  <th  >' . _('料号描述') . '</th>
                       <th  >' . _('出货人') . '</th> 
					    <th  >' . _('订单号码') . '</th> 
						   <th >' . _('订单行') . '</th>	
						   <th   width = 100>' . _('物流公司名称') . '</th>
						<th  >' . _('快递单号')  . '</th>
						<th  >' . '出货单备注' . '</th>
						   
						
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
         
			 $creation_date= date('Y-m-d',$myrow['creation_date']);  
 	
        	echo '  <td>' . $myrow['customer_code']  . '</td> 
			<td>' . $myrow['customer_name']  . '</td> 
			<td>' . $myrow['delivery_num']  . '</td>
			<td>' . $myrow['deliveryline']  . '</td> 			
			<td>' . $creation_date . '</td> 			
			<td>' . $myrow['delivery_quantity']  . '</td> 
			
			<td>' . $myrow['ship_quantity']  . '</td> 
			<td>' . $myrow['stockid']  . '</td> 
			<td>' . $myrow['item_desc']  . '</td> 
			<td>' . $myrow['created_by']  . '</td> 	       
			<td>' . $myrow['so_order_number']  . '</td> 
			<td>' . $myrow['so_line_no']  . '</td>  
			<td>' . $myrow['trackingcompany']  . '</td>            
            <td>' . $myrow['tracking_number']  . '</td> 
			<td>' . $myrow['narrative']  . '</td> 
		 
			</tr>';
			$i++;
			$RowIndex++;
			//end of page full new headings if
		} //end loop through customers
		 
		echo '</table>';
		echo '<input type="hidden" name="JustSelectedACustomer" value="Yes" />';
	}

        if (isset($ListPageMax) AND $ListPageMax > 1) {
             echo '<br /><div class="centre">&nbsp;&nbsp;第' . $_POST['PageOffset'] . ' ' . _('页，共') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ': ';
                //echo '<br /><div class="centre">&nbsp;&nbsp;' . $_POST['PageOffset'] . ' ' . _('of') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ': ';
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

