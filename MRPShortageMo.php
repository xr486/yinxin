<?php

 
include('includes/session.inc');

$Title = _('MRP建议生产报表');
$ViewTopic = 'Inventory';
$BookMark = 'FulfilRequest';

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
$_SESSION['DisplayRecordsMax']=50;
if(isset($_POST['Search']) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])){
	$SQL_FromDate = FormatDateForSQL($_POST['FromDate']);
    $SQL_ToDate = FormatDateForSQL($_POST['ToDate']);
    $sql ="SELECT a.item_no,a.item_desc,a.units,b.orderno,b.supplydate,b.supplyquantity
	FROM mrpsupplies b, sf_item_no a 
	   WHERE a.item_no = b.part
        AND ordertype = 'PLANMO'    " ;
  
if (empty($_POST['FromDate'])==0) {
    $SQL_FromDate = strtotime( $_POST['FromDate']);
    //echo $SQL_FromDate;
    $sql .= " and  supplydate >= '" . $SQL_FromDate . "' ";
}
if (empty($_POST['ToDate'])==0) {
    $SQL_ToDate = strtotime( $_POST['ToDate']) + 86400;
     //echo $SQL_ToDate;
    $sql .= " and  supplydate <='" . $SQL_ToDate . "' ";
}
if (isset($_POST['Stockid_from']) and $_POST['Stockid_from'] != '') {
        $sql = $sql . " and a.item_no >=  '" . $_POST['Stockid_from'] . "'";
    }
if (isset($_POST['Stockid_to']) and $_POST['Stockid_to'] != '') {
        $sql = $sql . " and a.item_no <=  '" . $_POST['Stockid_to'] . "' ";
    }

  $sql = $sql." order by supplydate ";
    $result = DB_query($sql,$db);
    if (DB_num_rows($result)==0) {
        unset($result);
        prnMsg(_('未能找到相关资料，请重新输入条件查询！') ,'error');
    }

	
}


echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('MRP建议生产报表') . '</p>';
echo '<table cellpadding="3" class="selection">';
 
 
if (!isset($_POST['FromDate'])) {
    $_POST['FromDate'] = date( "Y-m-d", mktime(0,0,0,date("m"),date("d")-20,date("Y")));
}
if (!isset($_POST['ToDate'])) {
    $_POST['ToDate'] = date( "Y-m-d", mktime(0,0,0,date("m"),date("d")+30,date("Y")));
}

echo ' 
		<td>' . _('需求日期范围从') . ':</td>
		<td>';
echo '<input type="text" name="FromDate"   onfocus="WdatePicker()"  value="' . $_POST['FromDate'] . '" size="20" maxlength="25" /></td>';
//echo '</tr>';
echo '<td><b>' . _('到') . '</b></td>
		<td>' . _('日期') . ':</td>
		<td>';
echo '<input type="text" name="ToDate"  onfocus="WdatePicker()"  value="' . $_POST['ToDate'] . '" size="20" maxlength="25" /></td>';
echo '</tr>';
echo '</tr>';
echo ' <td>' . _('料号起') . ':</td>
		<td>';
echo '<input type="text" name="Stockid_from"     value="' . $_POST['Stockid_from'] . '" size="20" maxlength="25" /></td>';
//echo '</tr>';
echo '<td><b>' . _('到') . '</b></td>
		<td>' . _('料号止') . ':</td>
		<td>';
echo '<input type="text" name="Stockid_to"   value="' . $_POST['Stockid_to'] . '" size="20" maxlength="25" /></td>';
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
    $ListPageMax = ceil($ListCount / $_SESSION['DisplayRecordsMax']);//$_SESSION['DisplayRecordsMax']
    
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
                     <th >' . _('料号') . '</th>
					 <th >' . _('料号描述') . '</th>
				<th >' . _('单位') . '</th>
				<th >' . _('需求单号') . '</th>
				<th >' . _('需求日期') . '</th>
				<th >' . _('需求数量') . '</th> 
            </tr>'; 
    $k = 0; //row counter to determine background colour
    $RowIndex = 0;
    $v_all_waitamount = 0;
	$v_waitamount = 0; 
    if (DB_num_rows($result) <> 0) {
        DB_data_seek($result, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);//$_SESSION['DisplayRecordsMax']
        $i = 0; //counter for input controls
        while (($myrow = DB_fetch_array($result)) AND ($RowIndex <> $_SESSION['DisplayRecordsMax'])) {//$_SESSION['DisplayRecordsMax'])
			if ($k == 1) {
				echo '<tr class="EvenTableRows">';
				$k = 0;
			} else {
				echo '<tr class="OddTableRows">';
				$k = 1;
			} 
			 $v_waitamount=$myrow['check_amount']-$myrow['AlreadyInvoiceAmount'];
			 $v_all_waitamount=$v_all_waitamount+$v_waitamount; 
			 $supplydate= date('Y-m-d',$myrow['supplydate']); 
			 

        	echo '<td>' . $myrow['item_no'] . '</td>
			   <td>' . $myrow['item_desc'] . '</td>
			   <td>' . $myrow['units'] . '</td>
			   <td>' . $myrow['orderno'] . '</td>
			   <td>' . $supplydate . '</td>
				<td>' . $myrow['supplyquantity'] . '</td>	
			 
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

