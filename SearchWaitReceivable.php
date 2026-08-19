<?php

 
include('includes/session.inc');

$Title = _('已出货未开票明细报表');
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
    $sql ="SELECT invoicenum,
                  invoiceamount,
                  taxamount,
                  invoicedate,
                  schedulepaymentdate,
                  ifnull(alreadyreceiveamount,0) alreadyreceiveamount,
                  cu.customer_code,
                  cu.customer_name
             FROM ar_invoice_headers_all ah,customers cu
            where (invoiceamount>alreadyreceiveamount or alreadyreceiveamount is null )
	      and cu.customer_code=ah.customer_code " ;
 
if (empty($_POST['FromDate'])==0) {
    $SQL_FromDate = strtotime( $_POST['FromDate']);
    //echo $SQL_FromDate;
    $sql .= " and  schedulepaymentdate >= '" . $SQL_FromDate . "' ";
}
if (empty($_POST['ToDate'])==0) {
    $SQL_ToDate = strtotime( $_POST['ToDate']) + 86400;
     //echo $SQL_ToDate;
    $sql .= " and  schedulepaymentdate <='" . $SQL_ToDate . "' ";
}
if(isset($_POST['CustomerCode']) and $_POST['CustomerCode'] != ''){
        $sql = $sql." and customer_number ".LIKE." '%".$_POST['CustomerCode']."%' ";
    }
    if(isset($_POST['CustomerName']) and $_POST['CustomerName'] != ''){
        $sql = $sql." and customer_name ".LIKE." '%".$_POST['CustomerName']."%' ";
    }
    $sql = $sql." order by customer_name";
    $result = DB_query($sql,$db);
    if (DB_num_rows($result)==0) {
        unset($result);
        prnMsg(_('未能找到相关资料，请重新输入条件查询！') ,'error');
    }

	
}


echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('查找已开票未收款') . '</p>';
echo '<table cellpadding="3" class="selection">';
echo '<tr><td colspan="2">' . _('输入部分客户名称') . ':</td><td>';
echo '<input type="text" name="CustomerName" value="' . $_POST['CustomerName'] . '" size="20" maxlength="25" /></td>';
echo '<td><b>' . _('OR') . '</b></td><td>' . _('输入部分客户代号') . ':</td>
	<td>';
echo '<input type="text" name="CustomerCode" value="' . $_POST['CustomerCode'] . '" size="20" maxlength="25" /></td>';
echo '</tr>';

if (!isset($_POST['FromDate'])) {
    $_POST['FromDate'] = date( "Y-m-d", mktime(0,0,0,date("m"),date("d")-30,date("Y")));
}
if (!isset($_POST['ToDate'])) {
    $_POST['ToDate'] = Date('Y-m-d');
}

echo '<td><b>' . _('或') . '</b></td>
		<td>' . _('预计收款日期范围从') . ':</td>
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

    echo '<tr>
                     <th class="ascending">' . '客户编号' . '</th>
                         <th class="ascending" width = 280>' . _('客户名称') . '</th>
						<th class="ascending">' . _('发票号码')  . '</th>
						<th class="ascending">' . '发票金额' . '</th>
						<th class="ascending">' . '税金' . '</th>
                                               <th class="ascending">' . _('已付款') . '</th>
						<th class="ascending">' . _('待付款') . '</th>
						<th class="ascending">' . _('发票日期') . '</th>
						<th class="ascending">' . _('预计付款日期') . '</th>
						
						
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
             $waitamount=$myrow['invoiceamount']-$myrow['alreadyreceiveamount'];
			 $invoicedate= date('Y-m-d',$myrow['invoicedate']); 
			 $schedulepaymentdate= date('Y-m-d',$myrow['schedulepaymentdate']); 
        	echo ' <td>' . $myrow['customer_code']  . '</td>
			<td>' . $myrow['customer_name']  . '</td>            
            <td>' . $myrow['invoicenum']  . '</td>
			 <td>' . $myrow['invoiceamount']  . '</td>
			<td>' . $myrow['taxamount']  . '</td>
                        <td>' . $myrow['alreadyreceiveamount']  . '</td> 
			<td>' . $waitamount  . '</td>
			<td>' . $invoicedate . '</td>
			<td>' . $schedulepaymentdate  . '</td>
			
	 
                     
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

