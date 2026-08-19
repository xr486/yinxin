<?php

 
include('includes/session.inc');

$Title = _('开票处理');
$ViewTopic = 'Inventory';
$BookMark = 'FulfilRequest';

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

if (isset($_GET['identifier'])){
    $_POST['identifier'] = $_GET['identifier'];
}

if (!isset($_POST['identifier'])) {
    $identifier=date('U');
} else {
    $identifier=$_POST['identifier'];
}	
$customerid = $_GET['customer_id'];

$_SESSION['DisplayRecordsMax']=10;
if(isset($_POST['Search']) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous']) or isset($customerid) ){

	
		  $CustomerID = $_GET['customer_id']; 
   $sql = "SELECT  c.so_order_number,c.so_line_no,c.delivery_date,c.delivery_num,c.deliveryline,c.delivery_quantity,a.invoicenum,a.created_by,c.stockid 	,c.delivery_amount,c.check_date,c.check_remark
                    FROM  so_delivery_headers_all a,so_delivery_all c
                  where   c.check_flag ='Y'	
				  and a.delivery_num = c.delivery_num
				  and a.customer_id='".$CustomerID."'  
				  order by c.delivery_num ";
// a.created_by='".$customerid."'  and
    $result =DB_query($sql,$db);

    if (DB_num_rows($result)==0) {
        unset($result);
        prnMsg(_('该客户待开发票的出货单，请重新输入条件查询！') ,'error');
        echo '<p ><a href="AppRecevie.php" ><h3 align ="center">' . _('返回选择其它加盟商') . '</h3></a></p>';
		include('includes/footer.inc');
	exit;
    }

	
}


echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('查找待开票的出货单号码') . '</p>';
echo '<table cellpadding="1" class="selection">';


if (isset($_POST['UpdateAll'])) {
        foreach ($_POST as $key => $value){



           if (mb_substr($key,0,10)=='UpdateLine') {
	        $order_number=mb_substr($key,10);
            $j = $_POST[$key]; 
            $billed_amount = $_POST['billed_amount' . $j];
			$tax_amount = $_POST['tax_amount' . $j];
			$order_line_id = $_POST['order_line_id' . $j]; 
			$chuanghu_name = $_POST['chuanghu_name' . $j]; 
			$line_no = $_POST['line_no' . $j]; 
			$order_number = $_POST['order_number' . $j]; 
			$invoicenumber = $_POST['invoicenumber' . $j]; 
			$created_by = $_POST['created_by' . $j]; 
			$narrative = $_POST['narrative' . $j]; 


			$sql="select billed_amount 
			      from sf_order_lines_all 
					WHERE   order_line_id = '".$order_line_id."'";
            $ErrMsg = _('更新收款记录不成功,原因');
			$result=DB_query($sql, $db,$ErrMsg);
			
				while (($myrow = DB_fetch_array($result))) {
				if (DB_num_rows($result) <> 0) {
				 
				$v_alreadinvoice_amount = $myrow['billed_amount']; }
				 else {
				$v_alreadinvoice_amount = 0;
			    }  }
 
			$sql="UPDATE sf_order_lines_all
					SET  billed_amount =   '".$v_alreadinvoice_amount."'   + '".$billed_amount."' 
					WHERE   order_line_id = '".$order_line_id."'";


            $ErrMsg = _('更新开票记录不成功,原因');
			$result=DB_query($sql, $db,$ErrMsg);	
           $v_date = strtotime(Date('Y-m-d H:i:s'));


		 $sql="INSERT INTO ar_invoice_headers_all (invoicenum,
													invoiceamount,
													taxamount,
													invoicedate,
													createdby,
													creationdate,
													lastupdatedby,
													lastupdatedate,
                                                    narrative,customer_code,
                                                    order_number,
													line_no,
													alreadyreceiveamount,
													order_line_id)
												VALUES(
													'".$invoicenumber."',
													'".$billed_amount."',
													'".$tax_amount."',
													'".$v_date."',
													'".$_SESSION['UserID']."',
										            '".$v_date . "',
                                                    '".$_SESSION['UserID']."',
													'".$v_date . "',
										        	'".$narrative . "',
													'".$created_by . "',
										        	'".$order_number . "',
										        	'".$line_no . "','0',
										        	'".$order_line_id . "' )"; 
			 
			$ErrMsg = _('插入开票记录不成功,原因');
			$result = DB_query($sql,$db,$ErrMsg); 
			
			//prnMsg( _('客户开票成功'),'success');
			// echo '<p ><a href="AppRecevie.php" ><h3 align ="center">' . _('继续开立发票') . '</h3></a></p>';
			
		}		
        }
		 DB_Txn_Commit($db);
            prnMsg( _('客户开票成功'), 'success');
            echo '<br /><div class="centre"><a href="' . $RootPath . '/AppRecevie.php">' . _('继续开立发票') . '</a></div>';
            unset($_SESSION['Contract'.$identifier]);
}

if (isset($_POST['Search']) and isset($result) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])  or isset($customerid) ){
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
                  <th>' . _('加盟商号码') . '</th>  	
				<th>' . _('订单号码') . '</th>
				<th>' . _('订单行') . '</th>
				<th>' . _('出货日期') . '</th>
				<th>' . _('出货数量') . '</th>
				<th>' . _('对账金额') . '</th>
				<th>' . _('已立账金额') . '</th>
				<th>' . _('待立账金额') . '</th>
				 <th >' . _('本次开票金额(元)') . '</a></th>
				 <th >' . _('税(元)') . '</a></th>
				 <th width=40>' . _('发票号码') . '</a></th>	
				 <th width=20>' . _('备注') . '</a></th>
				 <th>' . _('确认') . '</a></th>
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

			
			 $schedule_ship_date = date('Y-m-d',$myrow['schedule_ship_date']);
			 $tax_amount=0; 
			 $wait_amount=$myrow['reconciliation_amount']-$myrow['billed_amount'];
			echo ' <td>' . $myrow['created_by'] . '</td>
		        	<td>' . $myrow['order_number'] . '</td>
		         	 <td>' . $myrow['line_no'] . '</td>			
					<td>' . $schedule_ship_date . '</td>
					<td>' . $myrow['ship_quantity'] . '</td>
					<td>' . $myrow['reconciliation_amount'] . '</td>
					<td>' . $myrow['billed_amount'] . '</td>
					<td>' . $wait_amount . '</td>'; 

          echo '  <td><input   type="text" name="billed_amount'.$i.'" value="' .$wait_amount  . '" /></td>
		  <td><input   type="text" name="tax_amount'.$i.'" value="' . $tax_amount  . '" /></td>
		  <td><input   type="text" name="invoicenumber'.$i.'" value="' . $invoicenumber  . '" /></td>
		   <td><input   type="text" name="narrative'.$i.'" value="' . $narrative  . '" /></td>
		   <td><input type="checkbox" name="UpdateLine'.$myrow['order_number'].'" value="'.$i.'" /> </td> ';
           
		     echo  '<input type="hidden" name="created_by'.$i.'" value="' . $myrow['created_by']. '" />    ';
			  echo  '<input type="hidden" name="order_line_id'.$i.'" value="' . $myrow['order_line_id']. '" />    ';
			  echo  '<input type="hidden" name="order_number'.$i.'" value="' . $myrow['order_number']. '" />    ';
			  echo  '<input type="hidden" name="line_no'.$i.'" value="' . $myrow['line_no']. '" />    ';
          echo  ' </tr>';
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
echo '<a name="end"></a><br /><div class="centre"><input type="submit" name="UpdateAll"   value="立账开票保存" /></div>';
}

echo '</div>
      </form>';
include('includes/footer.inc');
?>

