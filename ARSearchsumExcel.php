<?php

 
include('includes/session.inc');

$Title = _('销售应收款统计Excel导出');
$ViewTopic = 'Inventory';
$BookMark = 'FulfilRequest';

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
$_SESSION['DisplayRecordsMax']=10;
if(isset($_POST['Search']) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])){
	$SQL_FromDate = FormatDateForSQL($_POST['FromDate']);
    $SQL_ToDate = FormatDateForSQL($_POST['ToDate']);
    $sql ="SELECT customer_code,customer_name,jieqian_amount,kaipiaoweishou_amount FROM customers where 1=1 " ;
 /* 
if (empty($_POST['FromDate'])==0) {
    $SQL_FromDate = strtotime( $_POST['FromDate']);
    //echo $SQL_FromDate;
    $sql .= " and  delivery_date >= '" . $SQL_FromDate . "' ";
}
if (empty($_POST['ToDate'])==0) {
    $SQL_ToDate = strtotime( $_POST['ToDate']) + 86400;
     //echo $SQL_ToDate;
    $sql .= " and  delivery_date <='" . $SQL_ToDate . "' ";
}*/
if(isset($_POST['customer_code']) and $_POST['customer_code'] != ''){
        $sql = $sql." and  customer_code ".LIKE." '%".$_POST['customer_code']."%' ";
    }
 

    if(isset($_POST['customer_name']) and $_POST['customer_name'] != ''){
        $sql = $sql." and ve.customer_name ".LIKE." '%".$_POST['customer_name']."%' ";
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
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('销售应收款统计Excel导出') . '</p>';
echo '<table cellpadding="3" class="selection">';
echo '<tr><td >' . _('客户名称') . ':</td><td>';
echo '<input type="text" name="customer_name" value="' . $_POST['customer_name'] . '" size="20" maxlength="25" /></td>';
echo '<td>' . _('客户代码') . ':</td>
	<td>';
echo '<input type="text" name="customer_code" value="' . $_POST['customer_code'] . '" size="20" maxlength="25" /></td>';
 
echo '</tr>';


if (!isset($_POST['FromDate'])) {
    $_POST['FromDate'] = date( "Y-m-d", mktime(0,0,0,date("m"),date("d")-30,date("Y")));
}
if (!isset($_POST['ToDate'])) {
    $_POST['ToDate'] = Date('Y-m-d');
}

echo '  <td>' . _('送货日期从') . ':</td>
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
                     <th width="100">客户代码</th>
	             <th width="200">客户名称</th>
				 <th width="100">结欠金额</th>
				 
					<th width="80" >开票未收金额</th> 
					<th width="80">本期出货</th>
					<th width="80">本期退货</th>
					
					<th width="80">本期已收</th>  
					<th width="80">本期结欠</th>   
            </tr>';

	 
    $k = 0; //row counter to determine background colour
    $RowIndex = 0;
    $v_all_waitamount = 0;
	$v_all_amount = 0;
	$v_all_already_amount = 0;
	$mytime= date("Y", strtotime("-1 year"));
$a = strtotime('Y-m-d',$mytime-12-31);
echo $a;
echo strtotime('Y-m-d',$mytime.'-12-31');
$mytime= date("Y", strtotime("-1 year"));
$sql_todate=strtotime($mytime.'-12-31');

if (empty($_POST['FromDate'])==0) {
    $SQL_FromDate = strtotime( $_POST['FromDate']);
    // echo $SQL_FromDate;
    $sql .= " and  delivery_date >= '" . $SQL_FromDate . "' ";
}
if (empty($_POST['ToDate'])==0) {
    $SQL_ToDate = strtotime( $_POST['ToDate']) + 86400;
     //echo $SQL_ToDate;
    $sql .= " and  delivery_date <='" . $SQL_ToDate . "' ";
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
    $sql_chuhuo = "SELECT sum(delivery_amount) header_amont FROM so_delivery_headers_all 
	WHERE customer_code = '".$myrow['customer_code']."' 
	and delivery_date >= '". $SQL_FromDate . "'
	and delivery_date <= '". $SQL_ToDate . "'
	and delivery_amount>0 ";

 
	//本期退货
	$sql_tuihuo = "SELECT sum(delivery_amount) header_amont
	FROM so_delivery_headers_all
	where customer_code= '".$myrow['customer_code']."'
	and delivery_date >= '". $SQL_FromDate . "'
	and delivery_date <= '". $SQL_ToDate . "'
	and delivery_amount<0 " ;
    //本期已收
    $sql_benqi_receive_amount = "SELECT sum(already_invoice_amount) already_invoice_amount, sum( already_receive_amount ) already_receive_amount, sum(delivery_amount-already_receive_amount -dis_receive_amount) to_receive_amount 
	FROM so_delivery_headers_all
	where customer_code= '".$myrow['customer_code']."'
	and delivery_date >= '". $SQL_FromDate . "'
	and delivery_date <= '". $SQL_ToDate . "' " ;


    //本期结欠
    $sql_leiji_no_receive_amount = "SELECT sum(delivery_amount-already_receive_amount -dis_receive_amount) to_receive_amount 
	FROM so_delivery_headers_all 
	WHERE customer_code =  '".$myrow['customer_code']."' 
	AND delivery_amount <> (already_receive_amount + dis_receive_amount)  
	and delivery_date >= '". $SQL_FromDate . "'
	and delivery_date <= '". $SQL_ToDate . "'
	";

 

  $result2 =DB_query($sql_chuhuo,$db,$ErrMsg,$DbgMsg);
  $mysql_chuhuo=DB_fetch_array($result2);//header_amont
  $result4 =DB_query($sql_tuihuo,$db,$ErrMsg,$DbgMsg);
  $mysql_tuihuo=DB_fetch_array($result4); //already_invoice_amount  delivery_amount dis_receive_amount already_receive_amount
 

  
  $result7 =DB_query($sql_benqi_receive_amount,$db,$ErrMsg,$DbgMsg);
  $sql_benqi_receive_amount=DB_fetch_array($result7);//to_receive_amount

 
        	echo ' <td>' . $myrow['customer_code'] . '</td> 
				<td>' . $myrow['customer_name'] . '</td>
				<td class="number">' . $myrow['jieqian_amount'] . '</td>  
                <td class="number">' . $myrow['kaipiaoweishou_amount'] . '</td>
				<td class="number">' .$mysql_chuhuo['header_amont'] . '</td>
				<td class="number">' .$mysql_tuihuo['header_amont'] . '</td>  
				<td class="number">' .$sql_benqi_receive_amount['already_receive_amount'] . '</td>
				<td class="number">' .$sql_benqi_receive_amount['to_receive_amount'] . '</td> 
			</tr>';
            $total = $myrow['jieqian_amount']+$total;
            $total_kai = $myrow['kaipiaoweishou_amount']+$total_kai;
            $total_chuhuo = $mysql_chuhuo['header_amont']+$total_chuhuo;
            $total_tuihuo = $mysql_tuihuo['header_amont']+$total_tuihuo;
            $total_sql_benqi=$sql_benqi_receive_amount['already_receive_amount']+$total_sql_benqi;
            $total_sql_benqi_receive=$sql_benqi_receive_amount['to_receive_amount']+$total_sql_benqi_receive;
			$i++;
			$RowIndex++;
			//end of page full new headings if
		} //end loop through customers


		echo '  <td> '.'合计'.'</td> <td></td><td> '.$total.'</td>  <td> '.$total_kai.'</td>  <td>'.$total_chuhuo.' </td> <td>' .$total_tuihuo  . '</td>
		<td>' .$total_sql_benqi  . '</td>
		<td>' .$total_sql_benqi_receive  . '</td>
		 ';
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
     echo '<br><div class="centre">
    <a href="' . $RootPath . '/ARSumExcel.php?C1=' .$_POST['customer_name'] .'&C2=' .$_POST['customer_code'] .'&C3=' .$_POST['FromDate'] . '&C4=' .$_POST['ToDate'] .  '">' .'导出Excel' . '
        </div>';

}
 // echo $SQL_FromDate; $SQL_ToDate customer_code
echo '</div>
      </form>';
include('includes/footer.inc');
?>

