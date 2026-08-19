<?php

 
include('includes/session.inc');

$Title = _('客户预收款冲销明细查询');
$ViewTopic = 'Inventory';
$BookMark = 'FulfilRequest';

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
$_SESSION['DisplayRecordsMax']=10;
if(isset($_POST['Search']) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])){
	$SQL_FromDate = FormatDateForSQL($_POST['FromDate']);
    $SQL_ToDate = FormatDateForSQL($_POST['ToDate']);
    $sql =" SELECT  customer_name,ch.ReceiveNum,
											ch.ReceiveAmount, 
											ch.customer_code, 
											ch.narrative,
                                            ch.ReceiveDate,ch.bankchangenum,ch.currency_code,ch.bankaccountname,ch.prereceive_used,ch.receive_type 	,ch.chong_prereceivenum
											,c2.creation_date,c2.receivenum chongxiao_num,c2.receiveamount chongxiao_amount,
											c2.taxamount chongxiao_taxamount
                    FROM  ar_receive_headers_all ch,
					ar_receive_headers_all c2,
					  customers a
                   where  a.customer_code=ch.customer_code
				   and c2.chong_prereceivenum=ch.ReceiveNum
				   and a.customer_code=c2.customer_code
				   and ch.receive_type='客户预收款' " ;
  
if (empty($_POST['FromDate'])==0) {
    $SQL_FromDate = strtotime( $_POST['FromDate']);
    //echo $SQL_FromDate;
    $sql .= " and  ch.ReceiveDate >= '" . $SQL_FromDate . "' ";
}
if (empty($_POST['ToDate'])==0) {
    $SQL_ToDate = strtotime( $_POST['ToDate']) + 86400;
     //echo $SQL_ToDate;
    $sql .= " and  ch.ReceiveDate <='" . $SQL_ToDate . "' ";
}
if(isset($_POST['customer_code']) and $_POST['customer_code'] != ''){
        $sql = $sql." and a.customer_code ".LIKE." '%".$_POST['customer_code']."%' ";
    }
    if(isset($_POST['customer_name']) and $_POST['customer_name'] != ''){
        $sql = $sql." and customer_name ".LIKE." '%".$_POST['customer_name']."%' ";
    }

  $sql = $sql." order by ReceiveDate ";
    $result = DB_query($sql,$db);
    if (DB_num_rows($result)==0) {
        unset($result);
        prnMsg(_('未能找到相关资料，请重新输入条件查询！') ,'error');
    }

	
}


echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('客户预收款冲销明细查询') . '</p>';
echo '<table cellpadding="3" class="selection">';
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
		<td>' . _('收款日期范围从') . ':</td>
		<td>';
echo '<input type="text" name="FromDate"   onfocus="WdatePicker()"  value="' . $_POST['FromDate'] . '" size="20" maxlength="25" /></td>';
//echo '</tr>';
echo '<td><b>' . _('到') . '</b></td>
		<td>' . _('日期') . ':</td>
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
                  <th >' . _('收款单号') . '</th>  
				<th  >' . _('收款金额') . '</th> 
				<th  >' . _('收款日期') . '</th>
				<th  >' . _('银行收款号码/凭证') . '</th>
				<th  >' . _('币别') . '</th>
				<th >' . _('银行账户') . '</th>
				<th >' . _('已冲销金额') . '</th>
				<th >' . _('客户代码') . '</th>
				<th >' . _('客户名称') . '</th>				
				<th  >' . _('备注') . '</th>
				<th  >' . _('冲销单号') . '</th>
				<th  >' . _('冲销金额') . '</th>
				<th  >' . _('冲销税') . '</th>
				<th >' . _('冲销日期') . '</th>
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
	 
             $ReceiveDate= date('Y-m-d',$myrow['ReceiveDate']); 
			 $creationdate= date('Y-m-d H:i:s',$myrow['creation_date']);  
        	echo ' <td>' . $myrow['ReceiveNum'] . '</td> 				 
				<td class="number">' .$myrow['ReceiveAmount'] . '</td>  
				<td >' . $ReceiveDate . '</td>
				<td  >' . $myrow['bankchangenum'] . '</td>	 
				<td  >' . $myrow['currency_code'] . '</td>
				<td  >' . $myrow['bankaccountname'] . '</td>
				<td class="number" >' . $myrow['prereceive_used'] . '</td>
				<td>' . $myrow['customer_code'] . '</td> 
				<td width=200>' . $myrow['customer_name'] . '</td>  				
				<td  >' . $myrow['narrative'] . '</td>	
				<td  >' . $myrow['chongxiao_num'] . '</td>
				<td  class="number">' . $myrow['chongxiao_amount'] . '</td>
				<td  class="number">' . $myrow['chongxiao_taxamount'] . '</td>
				<td  class="datetime" width=150>' . $creationdate . '</td> 

 
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

