<?php

 
include('includes/session.inc');

$Title = _('已采购入库未开票明细');
$ViewTopic = '已采购入库未开票明细';
$BookMark  = '已采购入库未开票明细';

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
$_SESSION['DisplayRecordsMax']=10;
if(isset($_POST['Search']) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])){
	$SQL_FromDate = FormatDateForSQL($_POST['FromDate']);
    $SQL_ToDate = FormatDateForSQL($_POST['ToDate']);
    $sql ="SELECT a.vendor_code,b.vendor_name,a.*,c.delivery_date
        FROM  po_rcv_receipt_line a,vendors b,po_rcv_receipt_header c
       WHERE  a.vendor_code=b.vendor_code
	     AND  a.receipt_num = c.receipt_num
         AND  a.check_amount > a.invoice_amount + a.invoice_dis_amount  " ;

if (empty($_POST['FromDate'])==0) {
    $SQL_FromDate = strtotime( $_POST['FromDate']);
    //echo $SQL_FromDate;
    $sql .= " and  delivery_date >= '" . $SQL_FromDate . "' ";
}
if (empty($_POST['ToDate'])==0) {
    $SQL_ToDate = strtotime( $_POST['ToDate']) + 86400;
     //echo $SQL_ToDate;
    $sql .= " and  delivery_date <='" . $SQL_ToDate . "' ";
}
if(isset($_POST['vendor_code']) and $_POST['vendor_code'] != ''){
        $sql = $sql." and pr.vendor_code ".LIKE." '%".$_POST['vendor_code']."%' ";
    }
    if(isset($_POST['vendor_name']) and $_POST['vendor_name'] != ''){
        $sql = $sql." and ve.vendor_name ".LIKE." '%".$_POST['vendor_name']."%' ";
    }

  $sql = $sql." order by delivery_date ";
    $result = DB_query($sql,$db);
    if (DB_num_rows($result)==0) {
        unset($result);
        prnMsg(_('未能找到相关资料，请重新输入条件查询！') ,'error');
    }

	
}


echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('已采购入库未开票明细报表') . '</p>';
echo '<table cellpadding="3" class="selection">';
echo '<tr><td >' . _('输入部分供应商名称') . ':</td><td>';
echo '<input type="text" name="vendor_name" value="' . $_POST['vendor_name'] . '" size="20" maxlength="25" /></td>';
echo '<td>' . _('输入部分供应商代号') . ':</td>
	<td>';
echo '<input type="text" name="vendor_code" value="' . $_POST['vendor_code'] . '" size="20" maxlength="25" /></td>';
echo '</tr>';


if (!isset($_POST['FromDate'])) {
    $_POST['FromDate'] = date( "Y-m-d", mktime(0,0,0,date("m"),date("d")-30,date("Y")));
}
if (!isset($_POST['ToDate'])) {
    $_POST['ToDate'] = Date('Y-m-d');
}

echo '  <td>' . _('收货日期范围从') . ':</td>
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
	                <th width="250">供应商名称</th>
				    <th width="150">采购入库单号</th>
                    <th width="40">采购项次</th>
					<th width="200">入库备注</th>   
					<th width="80" >收货日</th> 
					<th width="80">对账金额</th>
					
					<th width="80">已开票金额</th>  
					<th width="80">已优惠金额</th>  
					<th width="80">未开票金额</th>
				 
            </tr>';

	 
    $k = 0; //row counter to determine background colour
    $RowIndex = 0;
    $v_all_waitamount = 0;
	$v_all_amount = 0;
	$v_all_already_amount = 0;
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
             
			 $v_wait_invoice_amount=$myrow['check_amount']-$myrow['invoice_amount'] - $myrow['invoice_dis_amount'];
			 $v_wait_payment_amount=$myrow['need_payment_amount']-$myrow['already_payment_amount'] - $myrow['dis_payment_amount'];
			 $delivery_date= date('Y-m-d',$myrow['delivery_date']); 
			  $need_payment_date= date('Y-m-d',$myrow['need_payment_date']); 
			  $need_payment_amount=$need_payment_amount + $myrow['need_payment_amount'];
			  $return_amount=$return_amount + $myrow['return_amount'];
			  $already_invoice_amount=$already_invoice_amount + $myrow['already_invoice_amount'];
			  $dis_invoice_amount=$dis_invoice_amount + $myrow['dis_invoice_amount'];
              $v_all_wait_amount= $v_all_wait_amount + $v_wait_amount;

			  $already_payment_amount=$already_payment_amount + $myrow['already_payment_amount'];
			  $dis_payment_amount=$dis_payment_amount + $myrow['dis_payment_amount'];
              $v_all_wait_payment_amount= $v_all_wait_payment_amount + $v_wait_payment_amount;


        	echo '  <td>' . $myrow['vendor_code'] . '</td> 
				<td>' . $myrow['vendor_name'] . '</td>
				<td>' . $myrow['receipt_num'] . '</td> 
                <td>' . $myrow['receipt_line'] . '</td> 

				<td>' . $myrow['remark'] . '</td> 	
				<td >' . $delivery_date . '</td> 
                <td class="number">' . $myrow['check_amount'] . '</td>
				 
				<td class="number">' .$myrow['invoice_amount'] . '</td>
				<td class="number">' .$myrow['invoice_dis_amount'] . '</td> 
				<td class="number">' .$v_wait_invoice_amount. '</td>   
				    
			</tr>';
			$i++;
			$RowIndex++;
			//end of page full new headings if
		} //end loop through customers

	 
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

