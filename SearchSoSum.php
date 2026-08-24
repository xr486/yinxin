<?php

 
include('includes/session.inc');

$Title = _('加盟商订单统计');
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
    $sql ="SELECT a.order_number, a.order_man, a.man_contact, a.man_address, a.schedule_ship_date, a.order_amount, a.creation_date, b.line_no, b.chuanghu_name, b.line_amount, b.ship_quantity, b.chuanghu_quantity, line_amount
FROM sf_orders_all a, sf_order_lines_all b
WHERE a.order_number = b.order_number" ;
 
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

 if (isset($_POST['so_order_number']) and $_POST['so_order_number']!= '') {
			  $sql = $sql." and a.order_number ".LIKE." '%".$_POST['so_order_number']."%' ";
		}
if (isset($_POST['order_man']) and $_POST['order_man']!= '') {
			  $sql = $sql." and a.order_man ".LIKE." '%".$_POST['order_man']."%' ";
		}

 
 $sql = $sql." and  a.created_by= '".$_SESSION['UserID']." ' ";

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
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('加盟商订单统计') . '</p>';
echo '<table cellpadding="3" class="selection">';
echo '<tr><td colspan="2">' . _('输入部分订单名称') . ':</td><td>';
echo '<input type="text" name="so_order_number" value="' . $_POST['so_order_number'] . '" size="20" maxlength="25" /></td>';

echo ' <td colspan="2">' . _('部分客户名称') . ':</td><td>';
echo '<input type="text" name="order_man" value="' . $_POST['order_man'] . '" size="20" maxlength="25" /></td>';
echo '</tr>';
 

if (!isset($_POST['FromDate'])) {
    $_POST['FromDate'] = date( "Y-m-d", mktime(0,0,0,date("m"),date("d")-30,date("Y")));
}
if (!isset($_POST['ToDate'])) {
    $_POST['ToDate'] = Date('Y-m-d');
}

echo '<td><b>' . _('或') . '</b></td>
		<td>' . _('订单建立日期范围从') . ':</td>
		<td>';
echo '<input type="text" name="FromDate"   onfocus="WdatePicker()"  value="' . $_POST['FromDate'] . '" size="20" maxlength="25" /></td>';
//echo '</tr>';
echo '<td><b>' . _('到') . '</b></td>
		<td>' . _('日期') . ':</td>
		<td>';
echo '<input type="text" name="ToDate"  onfocus="WdatePicker()"  value="' . $_POST['ToDate'] . '" size="20" maxlength="25" /></td>';
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




    echo '<tr>    <th class="ascending">' . _('订单号码') . '</th>
	            <th class="ascending">' . _('客户名称') . '</th> 
                     <th class="ascending">' . '客户联系方式' . '</th>
                         <th class="ascending" width = 280>' . _('客户地址') . '</th>
						<th  >' . _('预计交货日期')  . '</th>
						<th  >' . '订单总金额' . '</th>
						<th  >' . '建立日期' . '</th>
                       <th  >' . _('订单行') . '</th> 
					    <th  >' . _('窗帘名称') . '</th> 
						   <th >' . _('行金额') . '</th>	 
						   <th  >' . _('订单数量') . '</th> 
						   <th  >' . _('工厂已发货数量') . '</th> 
						
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
              $schedule_ship_date= date('Y-m-d',$myrow['schedule_ship_date']);  
        	echo '  <td>' . $myrow['order_number']  . '</td> 
			<td>' . $myrow['order_man']  . '</td> 
			<td>' . $myrow['man_contact']  . '</td>
			<td>' . $myrow['man_address']  . '</td>            
            <td>' . $schedule_ship_date  . '</td> 
			<td>' . $myrow['order_amount']  . '</td> 
			<td>' . $creation_date . '</td> 
			<td>' . $myrow['line_no']  . '</td> 	       
			<td>' . $myrow['chuanghu_name']  . '</td> 
			<td>' . $myrow['line_amount']  . '</td> 
			<td>' . $myrow['chuanghu_quantity']  . '</td> 
            <td>' . $myrow['ship_quantity']  . '</td>  
			</tr>';
			$i++;
			$RowIndex++;
			//end of page full new headings if
		} //end loop through customers
		echo '  <td> '.'合计'.'</td> <td></td>  <td></td> <td> '.$v_all_invoiceamount.'</td>  <td>'.$v_all_taxamount.'</td> <td>' .$v_all_alreadyreceiveamount  . '</td>   <td>' .$v_all_waitamount  . '</td> ';
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

