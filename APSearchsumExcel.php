<?php

 
include('includes/session.inc');

$Title = _('采购应付款统计Excel导出');
$ViewTopic = 'Inventory';
$BookMark = 'FulfilRequest';

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
$_SESSION['DisplayRecordsMax']=10;
if(isset($_POST['Search']) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])){
	$SQL_FromDate = FormatDateForSQL($_POST['FromDate']);
    $SQL_ToDate = FormatDateForSQL($_POST['ToDate']);
    $sql ="SELECT vendor_code,vendor_name,jieqian_amount,kaipiaoweishou_amount FROM vendors where 1=1 " ;
 /* 
if (empty($_POST['FromDate'])==0) {
    $SQL_FromDate = strtotime( $_POST['FromDate']);
    //echo $SQL_FromDate;
    $sql .= " and  transaction_date >= '" . $SQL_FromDate . "' ";
}
if (empty($_POST['ToDate'])==0) {
    $SQL_ToDate = strtotime( $_POST['ToDate']) + 86400;
     //echo $SQL_ToDate;
    $sql .= " and  transaction_date <='" . $SQL_ToDate . "' ";
}*/
if(isset($_POST['vendor_code']) and $_POST['vendor_code'] != ''){
        $sql = $sql." and  vendor_code ".LIKE." '%".$_POST['vendor_code']."%' ";
    }
 

    if(isset($_POST['vendor_name']) and $_POST['vendor_name'] != ''){
        $sql = $sql." and ve.vendor_name ".LIKE." '%".$_POST['vendor_name']."%' ";
    }

  $sql = $sql." order by creation_date ";
    $result = DB_query($sql,$db);
    if (DB_num_rows($result)==0) {
        unset($result);
        prnMsg(_('未能找到相关资料，请重新输入条件查询！') ,'error');
    }

	
}


echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('采购应付款统计Excel导出') . '</p>';
echo '<table cellpadding="3" class="selection">';
echo '<tr><td >' . _('供应商名称') . ':</td><td>';
echo '<input type="text" name="vendor_name" value="' . $_POST['vendor_name'] . '" size="20" maxlength="25" /></td>';
echo '<td>' . _('供应商代码') . ':</td>
	<td>';
echo '<input type="text" name="vendor_code" value="' . $_POST['vendor_code'] . '" size="20" maxlength="25" /></td>';
 
echo '</tr>';


if (!isset($_POST['FromDate'])) {
    $_POST['FromDate'] = date( "Y-m-d", mktime(0,0,0,date("m"),date("d")-30,date("Y")));
}
if (!isset($_POST['ToDate'])) {
    $_POST['ToDate'] = Date('Y-m-d');
}

echo '  <td>' . _('收货日期从') . ':</td>
		<td>';
echo '<input type="text" name="FromDate"   onfocus="WdatePicker()"  value="' . $_POST['FromDate'] . '" size="20" maxlength="25" /></td>';
//echo '</tr>';
echo ' <td>' . _('到日期') . ':</td>
		<td>';
echo '<input type="text" name="ToDate"  onfocus="WdatePicker()"  value="' . $_POST['ToDate'] . '" size="20" maxlength="25" /></td>';
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
                     <th width="100">供应商代码</th>
	             <th width="200">供应商名称</th>
					<th width="80">收货金额</th> 
				   <th width="100">对账金额</th>	
				   <th width="100">未对账金额</th>				 
					<th width="80">未开票金额</th> 			 
					<th width="80">未付款金额</th> 
					<th width="80">本期退货</th>					
					<th width="80">本期已付</th>  
					<th width="80">本期欠款</th>   
            </tr>';

	 
    $k = 0; //row counter to determine background colour
    $RowIndex = 0;
    $v_all_waitamount = 0;
	$v_all_amount = 0;
	$v_all_already_amount = 0;


if (empty($_POST['FromDate'])==0) {
    $SQL_FromDate = strtotime( $_POST['FromDate']);
}
if (empty($_POST['ToDate'])==0) {
    $SQL_ToDate = strtotime( $_POST['ToDate']) + 86400;
}
  
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
              

	 


	//本期出货
    $sql_chuhuo = "SELECT sum(need_payment_amount) header_amont FROM po_rcv_receipt_line 
	WHERE vendor_code  = '".$myrow['vendor_code']."' 
	and transaction_date >= '". $SQL_FromDate . "'
	and transaction_date <= '". $SQL_ToDate . "'
	and need_payment_amount>0 ";

	//本期退货
    $sql_tuihuo = "SELECT sum(need_payment_amount) header_amont FROM po_rcv_receipt_line 
	WHERE vendor_code  = '".$myrow['vendor_code']."' 
	and transaction_date >= '". $SQL_FromDate . "'
	and transaction_date <= '". $SQL_ToDate . "'
	and need_payment_amount<0 ";

 
	//本期退货
	$sql_tuihuo = "SELECT sum(need_payment_amount) header_amont
	FROM po_rcv_receipt_line
	where vendor_code = '".$myrow['vendor_code']."'
	and transaction_date >= '". $SQL_FromDate . "'
	and transaction_date <= '". $SQL_ToDate . "'
	and need_payment_amount<0 " ;
    //本期已收
    $sql_benqi_receive_amount = "SELECT sum(already_invoice_amount) already_invoice_amount, sum( already_payment_amount ) already_receive_amount, sum(need_payment_amount-already_payment_amount -dis_payment_amount) to_receive_amount 
	FROM po_rcv_receipt_line
	where vendor_code = '".$myrow['vendor_code']."'
	and transaction_date >= '". $SQL_FromDate . "'
	and transaction_date <= '". $SQL_ToDate . "' " ;


    //本期结欠
    $sql_leiji_no_receive_amount = "SELECT sum(need_payment_amount - already_payment_amount -dis_payment_amount) to_receive_amount 
	FROM po_rcv_receipt_line 
	WHERE vendor_code  =  '".$myrow['vendor_code']."' 
	AND need_payment_amount <> (already_payment_amount + dis_payment_amount)  
	and transaction_date 	 >= '". $SQL_FromDate . "'
	and transaction_date 	 <= '". $SQL_ToDate . "'
	";

 

  $result2 =DB_query($sql_chuhuo,$db,$ErrMsg,$DbgMsg);
  $mysql_chuhuo=DB_fetch_array($result2);//header_amont
  $result3 =DB_query($sql_tuihuo,$db,$ErrMsg,$DbgMsg);
  $mysql_tuihuo=DB_fetch_array($result3);//header_amont
  $result4 =DB_query($sql_tuihuo,$db,$ErrMsg,$DbgMsg);
  $mysql_tuihuo=DB_fetch_array($result4); //already_invoice_amount  header_amont dis_receive_amount already_receive_amount
 
 
  
  $result7 =DB_query($sql_benqi_receive_amount,$db,$ErrMsg,$DbgMsg);
  $sql_benqi_receive_amount=DB_fetch_array($result7);//to_receive_amount

 
        	echo ' <td>' . $myrow['vendor_code'] . '</td> 
				<td>' . $myrow['vendor_name'] . '</td>
				<td class="number">' . $myrow['jieqian_amount'] . '</td>  
                <td class="number">' . $myrow['kaipiaoweishou_amount'] . '</td>
				<td class="number">' .$mysql_chuhuo['header_amont'] . '</td> 
				<td class="number">' .$mysql_tuihuo['header_amont'] . '</td> 
				<td class="number">' .$sql_benqi_receive_amount['already_receive_amount'] . '</td>
				<td class="number">' .$sql_benqi_receive_amount['to_receive_amount'] . '</td> 
			</tr>';
            $total_jieqian_amount=$total_jieqian_amount+$myrow['jieqian_amount'];
            $total_kaipiaoweishou_amount=$total_kaipiaoweishou_amount+$myrow['kaipiaoweishou_amount'];
            $total_mysql_chuhuo=$total_mysql_chuhuo+$mysql_chuhuo['header_amont'];
			$total_mysql_tuihuo=$total_mysql_tuihuo+$mysql_tuihuo['header_amont'];
            $total_sql_benqi=$total_sql_benqi+$sql_benqi_receive_amount['already_receive_amount'];
            $total_sql_benqi_receive_amount=$total_sql_benqi_receive_amount+$sql_benqi_receive_amount['to_receive_amount'];
			$i++;
			$RowIndex++;
			//end of page full new headings if
		} //end loop through vendors

		echo '  <td> '.'合计'.'</td><td></td><td> '.$total_jieqian_amount.'</td>  <td> '.$total_kaipiaoweishou_amount.'</td>  <td>'.$total_mysql_chuhuo.' </td> <td>' .$total_mysql_tuihuo.' </td> <td>' .$total_sql_benqi  . '</td>
		<td>' .$total_sql_benqi_receive_amount  . '</td>
		 ';
		echo '</table>';
		echo '<input type="hidden" name="JustSelectedAvendor" value="Yes" />';
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
     echo '<br><div class="centre">
    <a href="' . $RootPath . '/APSumExcel.php?C1=' .$_POST['vendor_name'] .'&C2=' .$_POST['vendor_code'] .'&C3=' .$_POST['FromDate'] . '&C4=' .$_POST['ToDate'] .  '">' .'导出Excel' . '
        </div>';

}
 // echo $SQL_FromDate; $SQL_ToDate vendor_code
echo '</div>
      </form>';
include('includes/footer.inc');
?>

