<?php

 
include('includes/session.inc');

$Title = _('供应商预付款明细查询');
$ViewTopic = 'Inventory';
$BookMark = 'FulfilRequest';

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
$_SESSION['DisplayRecordsMax']=10;
if(isset($_POST['Search']) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])){
	$SQL_FromDate = FormatDateForSQL($_POST['FromDate']);
    $SQL_ToDate = FormatDateForSQL($_POST['ToDate']);
    $sql =" SELECT  vendor_name,ch.paymentNum,
											ch.paymentAmount,
											ch.taxAmount,
											ch.vendor_code, 
											ch.narrative,
                                            ch.paymentDate,ch.bankchangenum,ch.currency_code,ch.bankaccountname,ch.prepayment_used,ch.payment_type 	,ch.chong_prepaymentnum
											,ch.creation_date	
                    FROM  ap_payment_headers_all ch , 
					  vendors a
                   where  a.vendor_code=ch.vendor_code
				   and ch.payment_type='预付款' " ;
  
if (empty($_POST['FromDate'])==0) {
    $SQL_FromDate = strtotime( $_POST['FromDate']);
    //echo $SQL_FromDate;
    $sql .= " and  paymentDate >= '" . $SQL_FromDate . "' ";
}
if (empty($_POST['ToDate'])==0) {
    $SQL_ToDate = strtotime( $_POST['ToDate']) + 86400;
     //echo $SQL_ToDate;
    $sql .= " and  paymentDate <='" . $SQL_ToDate . "' ";
}
if(isset($_POST['vendor_code']) and $_POST['vendor_code'] != ''){
        $sql = $sql." and a.vendor_code ".LIKE." '%".$_POST['vendor_code']."%' ";
    }
    if(isset($_POST['vendor_name']) and $_POST['vendor_name'] != ''){
        $sql = $sql." and vendor_name ".LIKE." '%".$_POST['vendor_name']."%' ";
    }

 if($_POST['checkresult']!=""){
        if($_POST['checkresult']=="1"){
            $sql .= " and ch.prepayment_used = ch.paymentAmount ";
        }
        if($_POST['checkresult']=="2"){
             $sql .=" and ch.prepayment_used <  ch.paymentAmount  and ch.prepayment_used > 0  ";
        }
        if($_POST['checkresult']=="3"){
             $sql .= " and ch.prepayment_used = 0 ";
        }
       
    }

  $sql = $sql." order by paymentDate ";
    $result = DB_query($sql,$db);
    if (DB_num_rows($result)==0) {
        unset($result);
        prnMsg(_('未能找到相关资料，请重新输入条件查询！') ,'error');
    }

	
}


echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('供应商预付款明细查询') . '</p>';
echo '<table cellpadding="3" class="selection">';
echo '<tr><td  >' . _('输入部分供应商名称') . ':</td><td>';
echo '<input type="text" name="vendor_name" value="' . $_POST['vendor_name'] . '" size="20" maxlength="25" /></td>';
echo '<td><b>' . _('OR') . '</b></td><td>' . _('输入部分供应商代号') . ':</td>
	<td>';
echo '<input type="text" name="vendor_code" value="' . $_POST['vendor_code'] . '" size="20" maxlength="25" /></td>';
echo '</tr>';


if (!isset($_POST['FromDate'])) {
    $_POST['FromDate'] = date( "Y-m-d", mktime(0,0,0,date("m"),date("d")-30,date("Y")));
}
if (!isset($_POST['ToDate'])) {
    $_POST['ToDate'] = Date('Y-m-d');
}

echo '  <td>' . _('付款日期范围从') . ':</td>
		<td>';
echo '<input type="text" name="FromDate"   onfocus="WdatePicker()"  value="' . $_POST['FromDate'] . '" size="20" maxlength="25" /></td>';
//echo '</tr>';
echo '<td><b>' . _('到') . '</b></td>
		<td>' . _('日期') . ':</td>
		<td>';
echo '<input type="text" name="ToDate"  onfocus="WdatePicker()"  value="' . $_POST['ToDate'] . '" size="20" maxlength="25" /></td>';
echo '</tr>';

echo '<tr><td>' . _('冲销状态') . ':</td><td><select name="checkresult">';

            echo '<option  selected="selected" value=""></option>';
  
            echo '<option   value="1">已全部冲销</option>';

            echo '<option   value="2">部分冲销</option>';
            echo '<option   value="3">未冲销</option>'; 
            echo '</select></td></tr>';
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
                  <th >' . _('付款单号') . '</th>  
				<th  >' . _('付款金额') . '</th>
				<th  >' . _('税额') . '</th>
				<th  >' . _('付款日期') . '</th>
				<th  >' . _('银行付款号码/凭证') . '</th>
				<th  >' . _('币别') . '</th>
				<th >' . _('银行账户') . '</th>
				<th >' . _('已冲销金额') . '</th>
				<th >' . _('供应商代码') . '</th>
				<th >' . _('供应商名称') . '</th>				
				<th  >' . _('备注') . '</th>
				<th >' . _('单据建立日期') . '</th>
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
	 
             $paymentDate= date('Y-m-d',$myrow['paymentDate']); 
			 $creationdate= date('Y-m-d H:i:s',$myrow['creation_date']);  
        	echo ' <td>' . $myrow['paymentNum'] . '</td> 				 
				<td class="number">' .$myrow['paymentAmount'] . '</td> 
				<td class="number">' .$myrow['taxAmount'] . '</td> 
				<td >' . $paymentDate . '</td>
				<td  >' . $myrow['bankchangenum'] . '</td>	 
				<td  >' . $myrow['currency_code'] . '</td>
				<td  >' . $myrow['bankaccountname'] . '</td>
				<td class="number" >' . $myrow['prepayment_used'] . '</td>
				<td>' . $myrow['vendor_code'] . '</td> 
				<td width=200>' . $myrow['vendor_name'] . '</td>  				
				<td  >' . $myrow['narrative'] . '</td>	
				<td  class="datetime" width=150>' . $creationdate . '</td> 
			</tr>';
			$i++;
			$RowIndex++;
			//end of page full new headings if
		} //end loop through vendors

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

