<?php

 
include('includes/session.inc');

$Title = _('未开票汇总报表');
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
	$SQL_FromDate = FormatDateForSQL($_POST['FromDate']);
    $SQL_ToDate = FormatDateForSQL($_POST['ToDate']);
    $sql ="SELECT cl.vendor_code,ve.vendor_name,sum(cl.po_all_amount) po_all_amount,sum(rl.invoice_amount) invoice_amount,sum(cl.dis_invoice_amount) dis_invoice_amount
                    FROM  po_headers_all cl, po_rcv_receipt_line rl,
						   vendors ve
                   where  cl.vendor_code=ve.vendor_code and rl.po_num = cl.po_num
                   and  po_all_amount-rl.invoice_amount - dis_invoice_amount >0   
              and 1=1 " ;
    
if (empty($_POST['FromDate'])==0) {
    $SQL_FromDate = strtotime( $_POST['FromDate']);
    //echo $SQL_FromDate;
    $sql .= " and  cl.need_date >= '" . $SQL_FromDate . "' ";
}
if (empty($_POST['ToDate'])==0) {
    $SQL_ToDate = strtotime( $_POST['ToDate']) + 86400;
     //echo $SQL_ToDate;
    $sql .= " and  cl.need_date <='" . $SQL_ToDate . "' ";
}
if(isset($_POST['vendor_code']) and $_POST['vendor_code'] != ''){
        $sql = $sql." and ve.vendor_code ".LIKE." '%".$_POST['vendor_code']."%' ";
    }
    if(isset($_POST['vendor_name']) and $_POST['vendor_name'] != ''){
        $sql = $sql." and vendor_name ".LIKE." '%".$_POST['vendor_name']."%' ";
    }

  $sql = $sql." group by cl.vendor_code,ve.vendor_name";
    $result = DB_query($sql,$db);
    if (DB_num_rows($result)==0) {
        unset($result);
        prnMsg(_('未能找到相关资料，请重新输入条件查询！') ,'error');
    }

	
}


echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('未开票汇总报表') . '</p>';
echo '<table cellpadding="3" class="selection">';

echo '<div class="text-nav">';
echo '<div class="text-nav-1"><div>' . _('供应商名称') . ':</div>';
echo '<input type="text" name="vendor_name" value="' . $_POST['vendor_name'] . '" size="20" maxlength="25" /></div>';
echo '<div class="text-nav-1"><div>' . _('供应商代号') . ':</div>
';
echo '<input type="text" name="vendor_code" value="' . $_POST['vendor_code'] . '" size="20" maxlength="25" /></div>';

if (!isset($_POST['FromDate'])) {
    $_POST['FromDate'] = date( "Y-m-d", mktime(0,0,0,date("m"),date("d")-30,date("Y")));
}
if (!isset($_POST['ToDate'])) {
    $_POST['ToDate'] = Date('Y-m-d');
}

echo '<div class="text-nav-1"><div>' . _('需求日期从') . ':</div>
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
echo '</table><div class="centre"><input type="submit" name="Search" value="查找">
 <a href="' . $RootPath . '/APWaitInvoicesumExcel.php?vendor_code=' .$_POST['vendor_code'] .
                '&vendor_name='  .$_POST['vendor_name'] . 
                '&FromDate='  .$_POST['FromDate'] .
                '&ToDate=' .$_POST['ToDate']  .' ">' .'导出' . '</a></div>';

 

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
				<th  width="80">' . _('供应商代号') . '</th> 
				<th  width="280">' . _('供应商名称') . '</th> 
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
        DB_data_seek($result, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);//
        $i = 0; //counter for input controls
        while (($myrow = DB_fetch_array($result)) AND ($RowIndex <> $_SESSION['DisplayRecordsMax'])) {//$_SESSION['DisplayRecordsMax'])
			if ($k == 1) {
				echo '<tr class="EvenTableRows">';
				$k = 0;
			} else {
				echo '<tr class="OddTableRows">';
				$k = 1;
			}
             $waitquantity=$myrow['po_all_amount']-$myrow['invoice_amount']-$myrow['dis_invoice_amount'] ;		
			 $v_all_waitamount= $v_all_waitamount + $waitquantity; 
			 $v_all_po_all_amount=$v_all_po_all_amount +   $myrow['po_all_amount'] ; 
			 $v_all_invoice_amount=$v_all_invoice_amount +   $myrow['invoice_amount'] ; 
			 $v_all_dis_invoice_amount=$v_all_dis_invoice_amount +   $myrow['dis_invoice_amount'] ; 
			 $approve_date= date('Y-m-d',$myrow['need_date']); 
        	echo ' 
				<td>' . $myrow['vendor_code'] . '</td>   
				<td>' . $myrow['vendor_name'] . '</td>  
				<td class="number">' .$myrow['po_all_amount'] . '</td> 
				<td  class="number">' . $myrow['invoice_amount'] . '</td>    
				<td class="number">' .round($waitquantity,2). '</td>                 
			</tr>';
			$i++;
			$RowIndex++;
			//end of page full new headings if
		} //end loop through customers

		echo '  <td> '.'合计'.'</td>   <td></td>  <td  class="number"> '.$v_all_po_all_amount.'</td>  <td  class="number">  '.$v_all_invoice_amount.'</td> <td  class="number">' .$v_all_waitamount  . '</td> ';
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

