<?php

 
include('includes/session.inc');

$Title = _('已对账未收款汇总');
$ViewTopic = 'Inventory';
$BookMark = 'FulfilRequest';

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
$_SESSION['DisplayRecordsMax']=10;

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
	$SQL_FromDate = FormatDateForSQL($_POST['FromDate']);
    $SQL_ToDate = FormatDateForSQL($_POST['ToDate']);
    $sql ="SELECT   ch.customer_code,ve.customer_name,sum(cl.check_amount) check_amount,sum(cl.payment_amount) payment_amount,sum(cl.dis_payment_amount) dis_payment_amount,sum(cl.check_amount-cl.payment_amount-cl.dis_payment_amount) wait_amount
                    FROM  so_headers_all ch,
                           so_lines_all cl,sf_item_no d,
						   customers ve
                   where ch.order_number=cl.order_number
				   and ch.customer_code=ve.customer_code
				   and cl.stockid=d.item_no
                   and (cl.check_amount-cl.payment_amount - cl.dis_payment_amount >0  )  
              and 1=1 " ;
    
if (empty($_POST['FromDate'])==0) {
    $SQL_FromDate = strtotime( $_POST['FromDate']);
    //echo $SQL_FromDate;
    $sql .= " and  ch.need_date >= '" . $SQL_FromDate . "' ";
}
if (empty($_POST['ToDate'])==0) {
    $SQL_ToDate = strtotime( $_POST['ToDate']) + 86400;
     //echo $SQL_ToDate;
    $sql .= " and  ch.need_date <='" . $SQL_ToDate . "' ";
}
if(isset($_POST['customer_code']) and $_POST['customer_code'] != ''){
        $sql = $sql." and customer_code ".LIKE." '%".$_POST['customer_code']."%' ";
    }
    if(isset($_POST['customer_name']) and $_POST['customer_name'] != ''){
        $sql = $sql." and customer_name ".LIKE." '%".$_POST['customer_name']."%' ";
    }

  $sql = $sql." group by ch.customer_code,ve.customer_name ";
    $result = DB_query($sql,$db);
    if (DB_num_rows($result)==0) {
        unset($result);
        prnMsg(_('未能找到相关资料，请重新输入条件查询！') ,'error');
    }

	
}


echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('查找已对账未收款汇总') . '</p>';
echo '<table cellpadding="3" class="selection">';
echo '<div class="text-nav">';
echo '<div class="text-nav-1"><div>' . _('客户名称') . ':</div>';
echo '<input type="text" name="customer_name" value="' . $_POST['customer_name'] . '" size="20" maxlength="25" /></div>';
echo '<div class="text-nav-1"><div>' . _('客户代号') . ':</div>
';
echo '<input type="text" name="customer_code" value="' . $_POST['customer_code'] . '" size="20" maxlength="25" /></div>';


if (!isset($_POST['FromDate'])) {
    $_POST['FromDate'] = date( "Y-m-d", mktime(0,0,0,date("m"),date("d")-30,date("Y")));
}
if (!isset($_POST['ToDate'])) {
    $_POST['ToDate'] = Date('Y-m-d');
}

echo '<div class="text-nav-1"><div>' . _('日期范围从') . ':</div>
';
echo '<input type="text" name="FromDate"   onfocus="WdatePicker()"  value="' . $_POST['FromDate'] . '" size="20" maxlength="25" /></div>';
//echo '</tr>';
echo '<div class="text-nav-1"><div>' . _('日期') . ':</div>
';
echo '<input type="text" name="ToDate"  onfocus="WdatePicker()"  value="' . $_POST['ToDate'] . '" size="20" maxlength="25" /></div>';
echo '</div>';

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
            echo '<br /><div class="centre">&nbsp;&nbsp;' . $_POST['PageOffset'] . ' ' . _('of') . ' ' . $ListPageMax . ' ' . _('页数') . '. ' . _('转到页') . ': ';
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
    echo '<br />
                    <table cellpadding="2" class="selection">';

    echo '<tr> 
				<th >' . _('客户代号') . '</th>
                     <th >' . _('客户名称') . '</th>
					
				<th >' . _('对账金额') . '</th>
				<th >' . _('已收款金额') . '</th>
				<th >' . _('免收款金额') . '</th> 		
				<th >' . _('未收款金额') . '</th>  
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
             $waitquantity=$myrow['check_amount']-$myrow['payment_amount']-$myrow['dis_payment_amount'] ;		
			 $v_all_waitamount= $v_all_waitamount + $waitquantity; 
			 $v_all_check_amount=$v_all_check_amount +   $myrow['check_amount'] ; 
			 $v_all_payment_amount=$v_all_payment_amount +   $myrow['payment_amount'] ; 
			 $v_all_dis_payment_amount=$v_all_dis_payment_amount +   $myrow['dis_payment_amount'] ; 
			 $approve_date= date('Y-m-d',$myrow['need_date']); 
        	echo ' 
				<td>' . $myrow['customer_code'] . '</td>   
				<td>' . $myrow['customer_name'] . '</td>
			 
				<td class="number">' .$myrow['check_amount'] . '</td> 
				<td>' . $myrow['payment_amount'] . '</td>  
				<td>' . $myrow['dis_payment_amount'] . '</td>    
				<td>' . $myrow['wait_amount'] . '</td>                  
			</tr>';
			$i++;
			$RowIndex++;
			//end of page full new headings if
		} //end loop through customers

		echo '  <td> '.'合计'.'</td> <td></td> <td> '.$v_all_check_amount.'</td>  <td>  '.$v_all_payment_amount.'</td>  <td>  '.$v_all_dis_payment_amount.'</td> <td>' .$v_all_waitamount  . '</td> ';
		echo '</table>';
                echo '<input type="hidden" name="JustSelectedACustomer" value="Yes" />';
                echo '<div>
                        <a href="' . $RootPath . '/ARSearchWaitPaymentSumExcel.php?FromDate=' .$_POST['FromDate'] .
                        '&ToDate=' .$_POST['ToDate'] . '&vendor_code=' .$_POST['vendor_code'] .
                        '&vendor_name=' .$_POST['vendor_name'] .' ">' .'资料导出Excel表' . '</a>
                </div>';
	}

        if (isset($ListPageMax) AND $ListPageMax > 1) {
                echo '<br /><div class="centre">&nbsp;&nbsp;' . $_POST['PageOffset'] . ' ' . _('of') . ' ' . $ListPageMax . ' ' . _('页数') . '. ' . _('转到页') . ': ';
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
                        <input type="submit" name="Go2" value="' . _('转到') . '" />
                        <input type="submit" name="Previous" value="' . _('上一页') . '" />
                        <input type="submit" name="Next" value="' . _('下一页') . '" />';
                echo '</div>';
    }//end if results to show

}

echo '</div>
      </form>';
include('includes/footer.inc');
?>

