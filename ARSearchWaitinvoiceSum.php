<?php

 
include('includes/session.inc');

$Title = _('客户未开票汇总');
$ViewTopic = 'Inventory';
$BookMark = 'FulfilRequest';

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');


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
	 
    $sql ="SELECT cl.tax_name,cl.yewu,cl.currency_code,cl.customer_code,ve.customer_name,sum(d.line_amount) order_all_amount,sum(d.invoice_amount)  invoice_amount
                    FROM  so_headers_all cl,so_lines_all d,
						   customers ve
                   where  cl.customer_code=ve.customer_code and cl.status='已签核'
				   and cl.order_number=d.order_number
                   and  d.line_amount-d.invoice_amount>0  
              and 1=1 " ;
    
 if(isset($_POST['order_number']) and $_POST['order_number'] != ''){
        $sql = $sql." and cl.order_number ".LIKE." '%".$_POST['order_number']."%' ";
    }  
  if(isset($_POST['customer_order_number']) and $_POST['customer_order_number'] != ''){
        $sql = $sql." and cl.customer_order_number ".LIKE." '%".$_POST['customer_order_number']."%' ";
    }  
	if(isset($_POST['stockid']) and $_POST['stockid'] != ''){
        $sql = $sql." and d.stockid ".LIKE." '%".$_POST['stockid']."%' ";
    } 
	if(isset($_POST['item_name']) and $_POST['item_name'] != ''){
        $sql = $sql." and d.item_name ".LIKE." '%".$_POST['item_name']."%' ";
    } 

if (empty($_POST['FromDate'])==0) {
    $SQL_FromDate = strtotime( $_POST['FromDate']);
    //echo $SQL_FromDate;
    $sql .= " and  cl.creation_date >= '" . $SQL_FromDate . "' ";
}
if (empty($_POST['ToDate'])==0) {
    $SQL_ToDate = strtotime( $_POST['ToDate']) + 86400;
     //echo $SQL_ToDate;
    $sql .= " and  cl.creation_date <='" . $SQL_ToDate . "' ";
}
if(isset($_POST['customer_code']) and $_POST['customer_code'] != ''){
        $sql = $sql." and ve.customer_code ".LIKE." '%".$_POST['customer_code']."%' ";
    }
    if(isset($_POST['customer_name']) and $_POST['customer_name'] != ''){
        $sql = $sql." and customer_name ".LIKE." '%".$_POST['customer_name']."%' ";
    }

  $sql = $sql." group by cl.tax_name,cl.yewu,cl.currency_code,cl.customer_code,ve.customer_name";
    $result = DB_query($sql,$db);
    if (DB_num_rows($result)==0) {
        unset($result);
        prnMsg(_('未能找到相关资料，请重新输入条件查询！') ,'error');
    }

	
}


echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('查找已对账未开票汇总') . '</p>';
echo '<table cellpadding="3" class="selection">';
echo '<div class="text-nav">';
echo '<div class="text-nav-1"><div>' . _('订单号码') . ':</div>';
echo '<input type="text" name="order_number" value="' . $_POST['order_number'] . '" size="20" maxlength="25" /></div>';
echo '<div class="text-nav-1"><div>' . _('客户订单') . ':</div>';
echo '<input type="text" name="customer_order_number" value="' . $_POST['customer_order_number'] . '" size="20" maxlength="25" /></div>';
echo '<div class="text-nav-1"><div>' . _('产品图号') . ':</div>';
echo '<input type="text" name="stockid" value="' . $_POST['stockid'] . '" size="20" maxlength="25" /></div>';
echo '<div class="text-nav-1"><div>' . _('产品名称') . ':</div>';
echo '<input type="text" name="item_name" value="' . $_POST['item_name'] . '" size="20" maxlength="25" /></div>';
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
echo '</table><div class="centre"><input type="submit" name="Search" value="查找">&nbsp;&nbsp; 
 <a href="' . $RootPath . '/ARSearchWaitinvoiceSumExcel.php?FromDate=' .$_POST['FromDate'] .
                        '&ToDate=' .$_POST['ToDate'] . '&customer_code=' .$_POST['customer_code'] .
                        '&customer_name=' .$_POST['customer_name'] .' ">' .'导出' . '</a> </div>';

 

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
                     <th >' . _('税别') . '</th>
                     <th >' . _('业务') . '</th>
                     <th >' . _('币别') . '</th>
					
				<th >' . _('订单金额') . '</th>
				<th >' . _('已开票金额') . '</th> 		
				<th >' . _('未开票金额') . '</th>  
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
             $waitquantity=$myrow['order_all_amount']-$myrow['invoice_amount']-$myrow['dis_invoice_amount'] ;		
			 $v_all_waitamount= $v_all_waitamount + $waitquantity; 
			 $v_all_check_amount=$v_all_check_amount +   $myrow['order_all_amount'] ; 
			 $v_all_invoice_amount=$v_all_invoice_amount +   $myrow['invoice_amount'] ;  
			 $approve_date= date('Y-m-d',$myrow['need_date']); 
        	echo ' 
				<td>' . $myrow['customer_code'] . '</td>   
				<td>' . $myrow['customer_name'] . '</td> 
				<td>' . $myrow['tax_name'] . '</td> 
				<td>' . $myrow['yewu'] . '</td> 
				<td>' . $myrow['currency_code'] . '</td>
			  
				<td class="number">' .$myrow['order_all_amount'] . '</td> 
				<td>' . $myrow['invoice_amount'] . '</td>   
				<td>' . $waitquantity . '</td>                  
			</tr>';
			$i++;
			$RowIndex++;
			//end of page full new headings if
		} //end loop through customers

		echo '  <td> '.'合计'.'</td> <td></td><td></td><td></td><td></td> <td> '.$v_all_check_amount.'</td>  <td>  '.$v_all_invoice_amount.'</td>    <td>' .$v_all_waitamount  . '</td> ';
		echo '</table>';
                echo '<input type="hidden" name="JustSelectedACustomer" value="Yes" />';
               
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

