k﻿<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include('includes/DefineInvoiceClass.php');

include('includes/session.inc');
$Title = _('财务开票处理');
$ViewTopic = '财务开票处理';
$BookMark = '财务开票处理';
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

if (isset($_GET['New'])) {

	unset($_SESSION['Contract'.$identifier]);
	$_SESSION['Contract'.$identifier] = new InvoiceRequest();
        if(isset($_GET['CustomerID'])){
            if(!isset($_SESSION['Contract'.$identifier]->customer_code) or $_SESSION['Contract'.$identifier]->customer_code ==''){
                $customerid = $_GET['CustomerID'];
                $sql = "select customer_code,customer_name from customers where customer_code = '" . $_GET['CustomerID'] . "'";
                $CustResult = DB_query($sql, $db);
                while ($myrow=  DB_fetch_array($CustResult)){
                    $_SESSION['Contract'.$identifier]->customer_code = $myrow['customer_code'];
                    $_SESSION['Contract'.$identifier]->customer_name = $myrow['customer_name'];
                }
            }
        }
}

if (isset($_POST['Update'])) {
	$InputError=0;
	if ($_POST['InvoiceNum']=='') {
		prnMsg( _('你必须输入一个发票号码'), 'error');
		$InputError=1;
	}
	if ($_POST['InvoiceAmount']=='') {
		prnMsg( _('你必须输入发票总金额'), 'error');
		$InputError=1;
	}
	if ($_POST['TaxAmount']=='') {
		prnMsg( _('你必须输入发票税额'), 'error');
		$InputError=1;
	}
        if ($_POST['InvoiceDate']=='') {
		prnMsg( _('你必须选择一个发票日期'), 'error');
		$InputError=1;
	}
        if ($_POST['SchedulePaymentDate']=='') {
		prnMsg( _('你必须选择一个预计收款日期'), 'error');
		$InputError=1;
	}
	if ($InputError==0) {
		//$InvoiceDate=strtotime($_POST['InvoiceDate']);
		//$SchedulePaymentDate=strtotime($_POST['SchedulePaymentDate']);
		$_SESSION['Contract'.$identifier]->InvoiceNum=$_POST['InvoiceNum'];
		$_SESSION['Contract'.$identifier]->InvoiceAmount=$_POST['InvoiceAmount'];
		$_SESSION['Contract'.$identifier]->TaxAmount=$_POST['TaxAmount'];
		$_SESSION['Contract'.$identifier]->InvoiceDate=$_POST['InvoiceDate'];
		$_SESSION['Contract'.$identifier]->SchedulePaymentDate=$_POST['SchedulePaymentDate'];
        $_SESSION['Contract'.$identifier]->Narrative=$_POST['Narrative'];
	}
}

if (isset($_POST['Edit'])) {
	$_SESSION['Contract'.$identifier]->LineItems[$_POST['LineNumber']]->Amount=$_POST['Amount'];
	$_SESSION['Contract'.$identifier]->LineItems[$_POST['LineNumber']]->LineNarrative=$_POST['LineNarrative'];
}

if (isset($_GET['Delete'])) {
	unset($_SESSION['Contract'.$identifier]->LineItems[$_GET['Delete']]);
	echo '<br />';
	prnMsg( _('The line was successfully deleted'), 'success');
	echo '<br />';
}
 
foreach ($_POST as $key => $value) {
	if (mb_strstr($key,'ItemName')) {
	    $Index=mb_substr($key, 8);
		if (filter_number_format($_POST['Amount'.$Index])>0) {
			$ItemName=$value;		
			$LineNarrative=$_POST['LineNarrative'.$Index];
			$OrderNumber=$_POST['OrderNumber'.$Index];
			$ShipDate=$_POST['ShipDate'.$Index];
			$ItemName=$_POST['ItemName'.$Index];
			$AlreadyInvoiceAmount=$_POST['AlreadyInvoiceAmount'.$Index];
			$ShipAmount=$_POST['ShipAmount'.$Index];
			$Amount=$_POST['Amount'.$Index]; 
			$LineNarrative=$_POST['LineNarrative'.$Index];
			 $_SESSION['Contract'.$identifier]->AddLine($OrderNumber, $ShipDate, $ItemName,$ShipAmount,$AlreadyInvoiceAmount,$Amount, $LineNarrative); 
         
		 }
	}
}



if (isset($_POST['Submit'])) {
	DB_Txn_Begin($db);
	$InputError=0;
	if ($_SESSION['Contract'.$identifier]->InvoiceNum=='') {
		prnMsg( _('你必须输入一个立账的发票号码'), 'error');
                echo '<br /><div class="centre"><a a href="'. htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?identifier='.$identifier.'">' . _('返回发票') . '</a></div>';
		$InputError=1;
	}
        
     
           
   if ($_SESSION['Contract'.$identifier]->InvoiceAmount=='') {
		prnMsg( _('你必须输入发票金额'), 'error');
                echo '<br /><div class="centre"><a a href="'. htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?identifier='.$identifier.'">' . _('返回发票') . '</a></div>';
		$InputError=1;
	}
	if ($_SESSION['Contract'.$identifier]->TaxAmount=='') {
		prnMsg( _('你必须输入税金额'), 'error');
                echo '<br /><div class="centre"><a a href="'. htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?identifier='.$identifier.'">' . _('返回发票') . '</a></div>';
		$InputError=1;
	}

        if ($_SESSION['Contract'.$identifier]->InvoiceDate=='') {
		prnMsg( _('你必须输入一个发票日期'), 'error');
                echo '<br /><div class="centre"><a a href="'. htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?identifier='.$identifier.'">' . _('返回发票') . '</a></div>';
		$InputError=1;
	}
        if ($_SESSION['Contract'.$identifier]->SchedulePaymentDate=='') {
		prnMsg( _('你必须输入一个预计收款日期'), 'error');
                echo '<br /><div class="centre"><a a href="'. htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?identifier='.$identifier.'">' . _('返回发票') . '</a></div>';
		$InputError=1;
	}
 
   $SumAmount=0;
   foreach ($_SESSION['Contract'.$identifier]->LineItems as $LineItems) {
			$SumAmount=$SumAmount+ $LineItems->Amount; 
	 }



   if ($SumAmount!=$_SESSION['Contract'.$identifier]->InvoiceAmount ){	   
       prnMsg( _('发票金额与明细金额不等,请确认修改'), 'error');
	    echo '<br /><div class="centre"><a a href="'. htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?identifier='.$identifier.'">' . _('返回发票') . '</a></div>';
	   $InputError=1;
   }

 
	if ($InputError==0) {
		echo $_SESSION['Contract'.$identifier]->SchedulePaymentDate;		
         $ScheduleDate = strtotime($_SESSION['Contract'.$identifier]->SchedulePaymentDate);
		
		$InvoiceDate = strtotime($_SESSION['Contract'.$identifier]->InvoiceDate);
		$ScheduleDate = strtotime(Date('Y-m-d H:i:s'));
		 $InvoiceDate = strtotime(Date('Y-m-d H:i:s'));
		$lastupdatedate = strtotime(Date('Y-m-d H:i:s'));
		$creationdate = strtotime(Date('Y-m-d H:i:s'));
		$HeaderSQL="INSERT INTO ar_invoice_headers_all (InvoiceNum,
											InvoiceAmount,
											taxAmount,
											customer_code, 
											narrative,
                                            InvoiceDate,
                                            SchedulePaymentDate,
                                            creationdate,
                                            lastupdatedate,
                                            createdby,
                                            lastupdatedby)
										VALUES(
											 '" . $_SESSION['Contract'.$identifier]->InvoiceNum . "',
											 '" . $_SESSION['Contract'.$identifier]->InvoiceAmount . "',
											 '" . $_SESSION['Contract'.$identifier]->TaxAmount . "',
											 '" . $_SESSION['Contract'.$identifier]->customer_code . "', 
                                             '" . $_SESSION['Contract'.$identifier]->Narrative . "',
                                             '" . $InvoiceDate . "',
                                             '" . $ScheduleDate . "',
										     '" .$creationdate . "',
                                             '" .$lastupdatedate . "',
											 '" . $_SESSION['UserID'] . "',
											 '" . $_SESSION['UserID'] . "')";
		$ErrMsg =_('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('发票不能存入数据库，原因是：');//'" . FormatDateForSQL($_SESSION['Contract'.$identifier]->DispatchDate) . "',
		$DbgMsg = _('The following SQL to insert the request header record was used');
		$Result = DB_query($HeaderSQL,$db,$ErrMsg,$DbgMsg,true);

		foreach ($_SESSION['Contract'.$identifier]->LineItems as $LineItems) {
			$LineSQL="INSERT INTO ar_invoice_lines_all (invoicelinenum,
													invoicenum,
													item_name,
													amount,
													ordernumber,
													linenarrative,
													creationdate,
                                                    lastupdatedate,
                                                    createdby,
                                                    lastupdatedby)
												VALUES(
													'".$LineItems->LineNumber."',
													'".$_SESSION['Contract'.$identifier]->InvoiceNum."',
													'".$LineItems->ItemName."',
													'".$LineItems->Amount."',
													'".$LineItems->OrderNumber."',
													'".$LineItems->LineNarrative."',
										            '".Date('Y-m-d H:i:s') . "',
                                                    '".Date('Y-m-d H:i:s') . "',
										        	'".$_SESSION['UserID'] . "',
										        	'".$_SESSION['UserID'] . "')";
				
        
			$ErrMsg =_('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('发票行不能存入数据库，原因是：');
			$DbgMsg = _('The following SQL to insert the request header record was used');
			$Result = DB_query($LineSQL,$db,$ErrMsg,$DbgMsg,true);

             
		   $sql = "SELECT alreadinvoice_amount  
			   FROM sf_orders_all
			    WHERE order_number=' ".$LineItems->OrderNumber."'";
		   $ErrMsg = _('The order details for this product cannot be retrieved because');
		   $WoResult = DB_query($sql,$db,$ErrMsg);
	       $WoRow = DB_fetch_row($WoResult);
		   if ($WoRow[0]!=null){
			$OldAmount =  $WoRow[0];
		   } else {
		 	$OldAmount = 0;
		   }

            $SumAmount=$OldAmount+$LineItems->Amount;
		 	 $UPDATESQL="UPDATE sf_orders_all
							set alreadinvoice_amount='".$SumAmount."'
                        WHERE   order_number='".$LineItems->OrderNumber."'";

			$ErrMsg =_('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('立账金额不能更新数据库，原因是：');
			$DbgMsg = _('The following SQL to insert the request header record was used');
			$Result = DB_query($UPDATESQL,$db,$ErrMsg,$DbgMsg,true);		
       }


            DB_Txn_Commit($db);
            prnMsg( _('发票建立成功！'), 'success');
            echo '<br /><div class="centre"><a href="' . $RootPath . '/InvoiceForAccount.php">' . _('建立新的发票') . '</a></div>';
            unset($_SESSION['Contract'.$identifier]);
	}
	include('includes/footer.inc');
	exit;
}
echo '	<div class="centre">
		<a href="' . $RootPath . '/InvoiceForAccount.php">返回重新选择客户</a>
	</div>';
echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/supplier.png" title="' . _('Dispatch') .
		'" alt="" />' . ' ' . $Title . '</p>';

if (isset($_GET['Edit'])) {
     $identifier = $_GET['identifier'];
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post"><input type="hidden" name = "identifier" value ="'.$identifier.'">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<table class="selection">';
	echo '<tr>
			<th colspan="2"><h4>' . _('修改发票行信息') . '</h4></th>
		</tr>';
	echo '<tr>
			<td>' . _('Line number') . '</td>
			<td>' . $_SESSION['Contract'.$identifier]->LineItems[$_GET['Edit']]->LineNumber . '</td>
		</tr>
		<tr>
			<td>' . _('料号') . '</td>
			<td>' . $_SESSION['Contract'.$identifier]->LineItems[$_GET['Edit']]->ItemName . '</td>
		</tr>

		<tr>
			<td>' . _('出货订单号') . '</td>
			<td>' . $_SESSION['Contract'.$identifier]->LineItems[$_GET['Edit']]->OrderNumber . '</td>
		</tr>

		<tr>
			<td>' . _('出货日期') . '</td>
			<td>' . $_SESSION['Contract'.$identifier]->LineItems[$_GET['Edit']]->ShipDate . '</td>
		</tr>
 

		<tr>
			<td>' . _('出货金额') . '</td>
			<td>' . $_SESSION['Contract'.$identifier]->LineItems[$_GET['Edit']]->ShipAmount . '</td>
		</tr>

		<tr>
			<td>' . _('已开票数量') . '</td>
			<td>' . $_SESSION['Contract'.$identifier]->LineItems[$_GET['Edit']]->AlreadyInvoiceAmount . '</td>
		</tr>
		<tr>
			<td>' . _('立账金额') . '</td>
			<td><input type="text" class="number" name="Amount" value="' . locale_number_format($_SESSION['Contract'.$identifier]->LineItems[$_GET['Edit']]->Amount, 2) . '" /></td>
		</tr>
		<tr>
			<td>' . _('行备注') . '</td>
			<td><input type="text"   name="LineNarrative" value="' .  $_SESSION['Contract'.$identifier]->LineItems[$_GET['Edit']]->LineNarrative  . '" /></td>
		</tr>
		';
	echo '<input type="hidden" name="LineNumber" value="' . $_SESSION['Contract'.$identifier]->LineItems[$_GET['Edit']]->LineNumber . '" />';
	echo '</table>
		<br />';
	echo '<div class="centre">
			<input type="submit" name="Edit" value="' . _('更新发票行') . '" />
		</div>
        </div>
		</form>';
	include('includes/footer.inc');
	exit;
}

echo '<form action="'. htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post"><input type="hidden" name = "identifier" value ="'.$identifier.'">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<table >';
echo '<tr>
		<th colspan="2">' . _('发票表头信息') . '</th>
	</tr>';
echo 	'<tr>
		<td>' . _('发票号码') . ':</td>
                <td><input type="text" name="InvoiceNum" maxlength="20" size="15" value="' . $_SESSION['Contract'.$identifier]->InvoiceNum . '" /></td>
	</tr>';
	
echo 	'<tr>
		<td>' . _('发票金额(需收款总金额)') . ':</td>
                <td><input type="text" class="number" name="InvoiceAmount" maxlength="20" size="15" value="' . $_SESSION['Contract'.$identifier]->InvoiceAmount . '" /></td>
	</tr>';   
 
echo 	'<tr>
		<td>' . _('税金额') . ':</td>
                <td><input type="text" class="number" name="TaxAmount" maxlength="20" size="15" value="' . $_SESSION['Contract'.$identifier]->TaxAmount . '" /></td>
	</tr>';   

echo '</select></td>
	</tr>';
echo '<tr>'
        . '<td>客户代码：</td>'
        . '<td>'.$_SESSION['Contract'.$identifier]->customer_code.'</td>'
   . '</tr>';
echo '<tr>'
        . '<td>客户名称：</td>'
        . '<td>'.$_SESSION['Contract'.$identifier]->customer_name.'</td>'
   . '</tr>';


echo '  <tr>
              <td>' . _('发票日期'). ':</td>
              <td><input type="text" class="date" alt="'.$_SESSION['DefaultDateFormat'].'" name="InvoiceDate" required="required" maxlength="10" size=“11” value="'.$_SESSION['Contract'.$identifier]->InvoiceDate.'"/></td>
        </tr>';

 
/*
echo '  <tr>
              <td>' . _('发票日期'). ':</td>
              <td><input type="text" onfocus="WdatePicker() " name="InvoiceDate" required="required" maxlength="10" size=“11” value="'.$_SESSION['Contract'.$identifier]->InvoiceDate.'"/></td>
        </tr>';
		*/
		

echo '  <tr>
              <td>' . _('预计收款日期'). ':</td>
              <td><input type="text" class="date" alt="'.$_SESSION['DefaultDateFormat'].'" name="SchedulePaymentDate" required="required" maxlength="10" size=“11” value="'.$_SESSION['Contract'.$identifier]->SchedulePaymentDate.'"/></td>
        </tr>';
echo '  <tr>
              <td>' . _('备注'). ':</td>
              <td><textarea  name="Narrative" cols="40" rows="1">' . stripslashes($_SESSION['Contract'.$identifier]->Narrative) . '</textarea></td>
        </tr>';
echo '</table>';
 
echo '<div class="centre">
	<input type="submit" name="Update" value="' . _('更新发票信息并添加行') . '" />
	</div>
    </div>
	</form>';
 
if (!isset($_SESSION['Contract'.$identifier]->InvoiceNum)) {
	include('includes/footer.inc');
	exit;
} 
$i = 0; //Line Item Array pointer
echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post"><input type="hidden" name = "identifier" value ="'.$identifier.'">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<br />
	<table >
	<tr>
		<th colspan="8"> ' . _('发票行维护') . ' </th>
	</tr>
	<tr>
		<th>' .  _('Line Number') . '</th>
		<th class="ascending">' .  _('出货订单') . '</th>
		<th class="ascending">' .  _('出货日期'). '</th>
		<th class="ascending">' .  _('料号'). '</th>
		<th class="ascending">' .  _('出货立账金额'). '</th>
		<th class="ascending">' .  _('已开票金额'). '</th>
		<th class="ascending">' .  _('本次开票金额'). '</th>
        <th class="ascending">' .  _('行备注'). '</th> 
	</tr>';

$k=0;

foreach ($_SESSION['Contract'.$identifier]->LineItems as $LineItems) {

	if ($k==1){
		echo '<tr class="EvenTableRows">';
		$k=0;
	} else {
		echo '<tr class="OddTableRows">';
		$k++;
	}
	echo '<td>' . $LineItems->LineNumber . '</td>
	        <td>' . $LineItems->OrderNumber . '</td>
			<td>' . $LineItems->ShipDate . '</td>
			<td>' . $LineItems->ItemName . '</td>
			<td>' . $LineItems->ShipAmount . '</td>			
			<td>' . $LineItems->AlreadyInvoiceAmount . '</td>
			<td>' . $LineItems->Amount . '</td>
			<td>' . $LineItems->LineNarrative . '</td>
			<td><a href="'. htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?identifier='.$identifier.'&Edit='.$LineItems->LineNumber.'">' . _('Edit') . '</a></td>
			<td><a href="'. htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?identifier='.$identifier.'&Delete='.$LineItems->LineNumber.'">' . _('Delete') . '</a></td>
		</tr>';
}
echo '</table>
	<br />
	<div class="centre">
		<input type="submit" name="Submit" value="' . _('确认保存') . '" />
	</div> 
    </div>
    </form>';

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post"><input type="hidden" name = "identifier" value ="'.$identifier.'">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('查找待开票的出货订单'). '</p>';
echo '<table cellpadding="3"  >';
echo '<tr><td colspan="2">' . _('请输入已出货的订单号') . ':</td><td>';
echo '<input type="text" name="order_number" value="' . $_POST['order_number'] . '" size="20" maxlength="25" /></td>';
echo '<td><b>' . _('OR') . '</b></td><td>' . _('输入部分料号') . ':</td>
	<td>';
echo '<input type="text" name="item_name" value="' . $_POST['item_name'] . '" size="20" maxlength="25" /></td>';
echo '</tr>';
echo '</table>';
echo ' 
	<div class="centre">
		<input type="submit" name="Search" value="' . _('查找未开票的出货订单,并输入数量增加到发票中') . '" />
	</div> 
	</div>
	</form>';

//or isset($_POST['Update']) 
if (isset($_POST['Search']) or isset($_POST['Next']) or isset($_POST['Prev']) or isset($_POST['Go'])){

        $customer_code=$_SESSION['Contract'.$identifier]->customer_code;
        $SQL = "SELECT  order_number,ship_amount,ship_date,alreadinvoice_amount,item_name
                    FROM  sf_orders_all
                   where   (ship_amount>alreadinvoice_amount or alreadinvoice_amount is null )                    	
                   and  customer_number='".$customer_code."' ";
//        }
        if (isset($_POST['order_number']) and $_POST['order_number'] != ''){
            $SQL = $SQL." and  order_number ".LIKE." '%".$_POST['order_number']."%' ";
        }
        if (isset($_POST['item_name']) and $_POST['item_name'] != ''){
            $SQL = $SQL." and item_name ".LIKE." '%".$_POST['item_name']."%' ";
        }
        
        $SQL = $SQL." ORDER BY order_number";

	if (isset($_POST['Next'])) {
		$Offset = $_POST['NextList'];
	}
	if (isset($_POST['Prev'])) {
		$Offset = $_POST['Previous'];
	}
	if (!isset($Offset) or $Offset<0) {
		$Offset=0;
	}

    $searchResult = DB_query($SQL,$db,$ErrMsg, $DbgMsg);
    $ListCount = DB_num_rows($searchResult);
    $ListPageMax = ceil($ListCount / $_SESSION['DisplayRecordsMax']);
    if ($_POST['Previous']==-2) {
        $Offset = $ListPageMax+$_POST['Previous'];
    }
    if (isset($_POST['Go']) and isset($_POST['PageOffset'])){
        $Offset = $_POST['PageOffset'] - 1;
    } 
	$SQL = $SQL . ' LIMIT ' . $_SESSION['DefaultDisplayRecordsMax'] . ' OFFSET ' . ($_SESSION['DefaultDisplayRecordsMax']*$Offset);
	$ErrMsg = _('There is a problem selecting the part records to display because');
	$DbgMsg = _('The SQL used to get the part selection was');
	$SearchResult = DB_query($SQL,$db,$ErrMsg, $DbgMsg);

	if (DB_num_rows($SearchResult)==0 ){
		prnMsg (_('找不到未开票的出货订单 '),'info');
	}
	if (DB_num_rows($SearchResult)<$_SESSION['DisplayRecordsMax']){
		$Offset=-1;
	}

} //end of if search


/* display list if there is more than one record */
if (isset($searchresult) AND !isset($_POST['Select'])) {
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post"><input type="hidden" name = "identifier" value ="'.$identifier.'">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	$ListCount = DB_num_rows($searchresult);
	if ($ListCount > 0) {
		// If the user hit the search button and there is more than one item to show
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
		if ($_POST['PageOffset'] > $ListPageMax) {
			$_POST['PageOffset'] = $ListPageMax;
		}
		if ($ListPageMax > 1) {
			echo '<div class="centre"><br />&nbsp;&nbsp;' . $_POST['PageOffset'] . ' ' . _('of') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ': ';
			echo '<select name="PageOffset">';
			$ListPage = 1;
			while ($ListPage <= $ListPageMax) {
				if ($ListPage == $_POST['PageOffset']) {
					echo '<option value=' . $ListPage . ' selected>' . $ListPage . '</option>';
				} else {
					echo '<option value=' . $ListPage . '>' . $ListPage . '</option>';
				}
				$ListPage++;
			}
			echo '</select>
				<input type="submit" name="Go" value="' . _('Go') . '" />
				<input type="submit" name="Previous" value="' . _('Previous') . '" />
				<input type="submit" name="Next" value="' . _('Next') . '" />
				<input type="hidden" name=Keywords value="'.$_POST['Keywords'].'" />
				<input type="hidden" name=StockCat value="'.$_POST['StockCat'].'" />
				<input type="hidden" name=StockCode value="'.$_POST['StockCode'].'" />
				<br />
				</div>';
		}
		echo '<table cellpadding="2">';
		echo '<tr>
				<th>' . _('料号') . '</th>
				<th>' . _('出货订单') . '</th>
				<th>' . _('出货日期') . '</th>
				<th>' . _('出货金额') . '</th>
				<th>' . _('已开票金额') . '</th>
			</tr>';
		$j = 1;
		$k = 0; //row counter to determine background colour
		$RowIndex = 0;
		if (DB_num_rows($searchresult) <> 0) {
			DB_data_seek($searchresult, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);
		}
		while (($myrow = DB_fetch_array($searchresult)) AND ($RowIndex <> $_SESSION['DisplayRecordsMax'])) {
			if ($k == 1) {
				echo '<tr class="EvenTableRows">';
				$k = 0;
			} else {
				echo '<tr class="OddTableRows">';
				$k++;
			}
			if ($myrow['mbflag'] == 'D') {
				$qoh = _('N/A');
			} else {
				$qoh = locale_number_format($myrow['qoh'], 2);
			}
			if ($myrow['discontinued']==1){
				$ItemStatus = '<p class="bad">' . _('Obsolete') . '</p>';
			} else {
				$ItemStatus ='';
			}

			echo '<td><input type="submit" name="Select" value="' . $myrow['ItemName'] . '" /></td>
					<td>' . $myrow['order_number'] . '</td>
					<td>' . $myrow['ship_date'] . '</td>
					<td>' . $myrow['ship_amount'] . '</td>
					<td>' . $myrow['alreadinvoice_amount'] . '</td> 					
				</tr>';
			//end of page full new headings if
		}
		//end of while loop
		echo '</table>
              </div>
              </form>
              <br />';
	}
}


/* end display list if there is more than one record */

if (isset($SearchResult)) {
	$j = 1;
	echo ' 
		<div class="page_help_text">' . _('通过输入需求数量来选择出货订单，当完成后点击添加至发票。') . '</div>
		 
		<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post" id="orderform"><input type="hidden" name = "identifier" value ="'.$identifier.'">';
		$ListCount = DB_num_rows($searchResult);
	if ($ListCount > 0) {
		// If the user hit the search button and there is more than one item to show
		$ListPageMax = ceil($ListCount / $_SESSION['DisplayRecordsMax']);
                if ($ListPageMax > 1) {
                        if ($Offset == -1) {
                            $count = $ListPageMax;
                        }else{
                            $count = $Offset + 1;
                        }
			echo '<div class="centre"><br />&nbsp;&nbsp; ' . _('当前页数为：第') . '' . $count . '' . _('页') .'&nbsp;&nbsp ' . _('总页数为：') . ' ' . $ListPageMax . ' ' . _('页') .'&nbsp;&nbsp' . _('跳转至：第') .' ';
                        echo '<select name="PageOffset">';
                        $ListPage = 1;
			while ($ListPage <= $ListPageMax) {
				if ($ListPage == $count) {
					echo '<option value=' . $ListPage . ' selected>' . $ListPage . '</option>';
				} else {
					echo '<option value=' . $ListPage . '>' . $ListPage . '</option>';
				}
				$ListPage++;
			}
                     echo '</select>页<input type="submit" name="Go" value="' . _('Go') . '" />';
                }
                
        }	
        echo   '<div>
		<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
		<table>
		<tr>
			<td>
				<input type="hidden" name="Previous" value="'.($Offset-1).'" />
				<input tabindex="'.($j+8).'" type="submit" name="Prev" value="'._('Prev').'" /></td>
				<td style="text-align:center" colspan="6">
				<input type="hidden" name="order_items" value="1" />
				<input tabindex="'.($j+9).'" type="submit" value="'._('添加至发票行').'" /></td>';
		if ($Offset >= 0){
        echo '
                            <td>
                                    <input type="hidden" name="NextList" value="'.($Offset+1).'" />
                                    <input tabindex="'.($j+10).'" type="submit" name="Next" value="'._('Next').'" /></td>';
        }
		echo '
			</tr>
			<tr>
				<th class="ascending">' . _('出货订单') . '</th>
				<th class="ascending">' . _('出货日期') . '</th>
				<th class="ascending">' . _('料号') . '</th>
				<th class="ascending">' . _('出货立账金额') . '</th>
				<th class="ascending">' . _('已开票金额') . '</th>
				<th class="ascending">' . _('未开票金额') . '</th>
				<th class="ascending">' . _('本次开票金额') . '</th>
				<th class="ascending">' . _('发票行备注') . '</th>
			</tr>';
	$ImageSource = _('No Image');

	$k=0; //row colour counter
	$i=0;
	while ($myrow=DB_fetch_array($SearchResult)) {
		$waitamount=$myrow['ship_amount']-$myrow['alreadinvoice_amount'] ;		 

		echo   '<td>' . $myrow['order_number'] . '</td>
				<td>' . $myrow['ship_date'] . '</td>
				<td>' . $myrow['item_name'] . '</td>				
                <td class="number">' . $myrow['ship_amount'] . '</td>
				<td class="number">' .$myrow['alreadinvoice_amount'] . '</td>
				<td class="number">' .$waitamount. '</td>
			    <td><input class="number"= ' . ($i==0 ? 'autofocus="autofocus"':'') . ' tabindex="'.($j+7).'" type="text" size="6" name="Amount'.$i.'" value="0" />    
				</td>
				<td><input type="text" name="LineNarrative'.$i.'" value="' . $myrow['LineNarrative'] . '" /> </td>
			</tr>';
	      echo ' <input type="hidden" name="ItemName'.$i.'" value="'.$myrow['item_name'].'" />';
		  echo '<input type="hidden" name="ShipAmount'.$i.'" value="' . $myrow['ship_amount']. '" />';
		  echo '<input type="hidden" name="AlreadyInvoiceAmount'.$i.'" value="' . $myrow['alreadinvoice_amount']. '" />';
		  echo '<input type="hidden" name="ShipDate'.$i.'" value="' . $myrow['ship_date'] . '" />';
		  echo '<input type="hidden" name="OrderNumber'.$i.'" value="' . $myrow['order_number'] . '" />';
		$i++;
	}

 



#end of while loop
	echo '<tr>
			<td><input type="hidden" name="Previous" value="'.($Offset-1).'" />
				<input tabindex="'.($j+7).'" type="submit" name="Prev" value="'._('Prev').'" /></td>
			<td style="text-align:center" colspan="6"><input type="hidden" name="order_items" value="1" />
				<input tabindex="'.($j+8).'" type="submit" value="'._('添加至发票行').'" /></td>';
				if ($Offset >= 0){
        echo '
                            <td>
                                    <input type="hidden" name="NextList" value="'.($Offset+1).'" />
                                    <input tabindex="'.($j+10).'" type="submit" name="Next" value="'._('Next').'" /></td>';
        }		
		echo '	
		<tr/>
		</table>
       </div>
       </form>';
}#end if SearchResults to show

//*********************************************************************************************************
include('includes/footer.inc');
?>
