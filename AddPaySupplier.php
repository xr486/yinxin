k﻿<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include('includes/DefinePaySupplierClass.php');

include('includes/session.inc');
$Title = _('供应商付款');
$ViewTopic = '供应商付款';
$BookMark = '供应商付款';
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
        if(isset($_GET['vendor_code'])){
            if(!isset($_SESSION['Contract'.$identifier]->vendor_code) or $_SESSION['Contract'.$identifier]->vendor_code ==''){
                $vendor_code = $_GET['vendor_code'];
                $sql = 'select vendor_code,vendor_name from vendors where vendor_code = "'.$vendor_code.'" ';
			  //  $sql = 'select vendor_code,vendor_name from suppliers where vendor_code ="TX" ';
                $CustResult = DB_query($sql, $db);
                while ($myrow=  DB_fetch_array($CustResult)){
                    $_SESSION['Contract'.$identifier]->vendor_code = $myrow['vendor_code'];
                    $_SESSION['Contract'.$identifier]->vendor_name = $myrow['vendor_name'];
                }
            }
        }
}

if (isset($_POST['Update'])) {
	$InputError=0;
	$date=date('YmdHi');
	$sql = "select count(*) from ap_invoice_pay_headers_all";
	$result=DB_query($sql, $db);
	$myrow = DB_fetch_array($result);
	
	$_SESSION['Contract'.$identifier]->InvoiceNum = 'XJ'.$date.str_pad($myrow[0]+1,6,"0",STR_PAD_LEFT);
	$_POST['InvoiceNum'] = $_SESSION['Contract'.$identifier]->InvoiceNum;
	if ($_POST['InvoiceAmount']=='') {
		prnMsg( _('你必须输入发票总金额'), 'error');
		$InputError=1;
	}
	if ($_POST['TaxAmount']=='') {
		prnMsg( _('你必须输入发票税额'), 'error');
		$InputError=1;
	}

	if ($_POST['PaymentAmount']=='') {
		prnMsg( _('你必须输入付款金额'), 'error');
		$InputError=1;
	}
        if ($_POST['InvoiceDate']=='') {
		prnMsg( _('你必须选择一个发票日期'), 'error');
		$InputError=1;
	}
         
	if ($InputError==0) {
		$_SESSION['Contract'.$identifier]->InvoiceNum=$_POST['InvoiceNum'];
		$_SESSION['Contract'.$identifier]->InvoiceAmount=$_POST['InvoiceAmount'];
		$_SESSION['Contract'.$identifier]->TaxAmount=$_POST['TaxAmount'];
		$_SESSION['Contract'.$identifier]->InvoiceDate=$_POST['InvoiceDate'];
		$_SESSION['Contract'.$identifier]->PaymentAmount=$_POST['PaymentAmount'];
		$_SESSION['Contract'.$identifier]->BankName=$_POST['BankName'];
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
			$ReceiveDate=$_POST['ReceiveDate'.$Index];
			$ItemName=$_POST['ItemName'.$Index];
			$PaymentVendorAmount=$_POST['PaymentVendorAmount'.$Index];
			$PurchaseAmount=$_POST['PurchaseAmount'.$Index];
			$Amount=$_POST['Amount'.$Index]; 
			$LineNarrative=$_POST['LineNarrative'.$Index];
			 $_SESSION['Contract'.$identifier]->AddLine($OrderNumber, $ReceiveDate, $ItemName,$PurchaseAmount,$PaymentVendorAmount,$Amount, $LineNarrative); 
         
		 }
	}
}



if (isset($_POST['Submit'])) {
	DB_Txn_Begin($db);
	$InputError=0;   
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

   if ($_SESSION['Contract'.$identifier]->PaymentAmount=='') {
		prnMsg( _('你必须输入本次付款金额'), 'error');
                echo '<br /><div class="centre"><a a href="'. htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?identifier='.$identifier.'">' . _('返回发票') . '</a></div>';
		$InputError=1;
	}
   
   if ($_SESSION['Contract'.$identifier]->BankName=='') {
		prnMsg( _('你必须输入选择付款账号'), 'error');
                echo '<br /><div class="centre"><a a href="'. htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?identifier='.$identifier.'">' . _('返回发票') . '</a></div>';
		$InputError=1;
	}

   if ($_SESSION['Contract'.$identifier]->InvoiceDate=='') {
		prnMsg( _('你必须输入一个发票日期'), 'error');
                echo '<br /><div class="centre"><a a href="'. htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?identifier='.$identifier.'">' . _('返回发票') . '</a></div>';
		$InputError=1;
	}
         
 
   $SumAmount=0;
   foreach ($_SESSION['Contract'.$identifier]->LineItems as $LineItems) {
			$SumAmount=$SumAmount+ $LineItems->Amount; 
	 }



   if ($SumAmount!=$_SESSION['Contract'.$identifier]->PaymentAmount ){	   
       prnMsg( _('付款金额与明细金额不等,请确认修改'), 'error');
	    echo '<br /><div class="centre"><a a href="'. htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?identifier='.$identifier.'">' . _('返回发票') . '</a></div>';
	   $InputError=1;
   }

 
	if ($InputError==0) {
             $creationdate = strtotime(Date('Y-m-d H:i:s'));
			 $InvoiceDate = strtotime($_SESSION['Contract'.$identifier]->InvoiceDate);
		$HeaderSQL="INSERT INTO ap_invoice_pay_headers_all (InvoiceNum,
											InvoiceAmount,
											taxAmount,
											paymentamount,
											bankname,
											vendor_code, 
											narrative,
                                            InvoiceDate, 
                                            creationdate,
                                            lastupdatedate,
                                            createdby,
                                            lastupdatedby)
										VALUES(
											 '" . $_SESSION['Contract'.$identifier]->InvoiceNum . "',
											 '" . $_SESSION['Contract'.$identifier]->InvoiceAmount . "',
											 '" . $_SESSION['Contract'.$identifier]->TaxAmount . "',
											 '" . $_SESSION['Contract'.$identifier]->PaymentAmount . "',
											 '" . $_SESSION['Contract'.$identifier]->BankName . "',
											 '" . $_SESSION['Contract'.$identifier]->vendor_code . "', 
                                             '" . $_SESSION['Contract'.$identifier]->Narrative . "',
                                             '" . $InvoiceDate . "', 
										     '" .$creationdate . "',
                                             '" .$creationdate . "',
											 '" . $_SESSION['UserID'] . "',
											 '" . $_SESSION['UserID'] . "')";
		$ErrMsg =_('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('发票不能存入数据库，原因是：');//'" . FormatDateForSQL($_SESSION['Contract'.$identifier]->DispatchDate) . "',
		$DbgMsg = _('The following SQL to insert the request header record was used');
		$Result = DB_query($HeaderSQL,$db,$ErrMsg,$DbgMsg,true);

		foreach ($_SESSION['Contract'.$identifier]->LineItems as $LineItems) {
			$creationdate = strtotime(Date('Y-m-d H:i:s'));
			$LineSQL="INSERT INTO ap_invoice_pay_lines_all (invoicelinenum,
													invoicenum,
													item_name,
													amount,
													ordernumber,
													vendor_code,
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
													'" . $_SESSION['Contract'.$identifier]->vendor_code . "', 
													'".$LineItems->LineNarrative."',
										            '".$creationdate . "',
                                                    '".$creationdate . "',
										        	'".$_SESSION['UserID'] . "',
										        	'".$_SESSION['UserID'] . "')";
				
        
			$ErrMsg =_('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('发票行不能存入数据库，原因是：');
			$DbgMsg = _('The following SQL to insert the request header record was used');
			$Result = DB_query($LineSQL,$db,$ErrMsg,$DbgMsg,true);

             
		   $sql = "SELECT paymentvendoramount  
			   FROM sf_orders_all
			    WHERE order_number=' ".$LineItems->OrderNumber."'";
		   $ErrMsg = _('The order details for this product cannot be retrieved because');
		   $WoResult = DB_query($sql,$db,$ErrMsg);
	       $WoRow = DB_fetch_row($WoResult);
		   echo $WoRow[0];
		   if ($WoRow[0]!=null){
			$OldAmount =  $WoRow[0];
		   } else {
		 	$OldAmount = 0;
		   }
		   echo $WoRow[0];
		   echo '$OldAmount';
		   
		   echo $LineItems->OrderNumber;

            $SumAmount=$OldAmount+$LineItems->Amount;
		 	 $UPDATESQL="UPDATE sf_orders_all
							set paymentvendoramount='".$SumAmount."'
                        WHERE   order_number='".$LineItems->OrderNumber."'";

			$ErrMsg =_('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('立账金额不能更新数据库，原因是：');
			$DbgMsg = _('The following SQL to insert the request header record was used');
			$Result = DB_query($UPDATESQL,$db,$ErrMsg,$DbgMsg,true);		
       }


            DB_Txn_Commit($db);
            prnMsg( _('供应商付款完成！'), 'success');
            echo '<br /><div class="centre"><a href="' . $RootPath . '/PaySupplier.php">' . _('新增加供应商付款') . '</a></div>';
            unset($_SESSION['Contract'.$identifier]);
	}
	include('includes/footer.inc');
	exit;
}
echo '	<div class="centre">
		<a href="' . $RootPath . '/PaySupplier.php">返回重新选择供应商</a>
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
			<td>' . _('采购业务订单') . '</td>
			<td>' . $_SESSION['Contract'.$identifier]->LineItems[$_GET['Edit']]->OrderNumber . '</td>
		</tr>

		<tr>
			<td>' . _('收货日期') . '</td>
			<td>' . $_SESSION['Contract'.$identifier]->LineItems[$_GET['Edit']]->ReceiveDate . '</td>
		</tr>
 

		<tr>
			<td>' . _('采购金额') . '</td>
			<td>' . $_SESSION['Contract'.$identifier]->LineItems[$_GET['Edit']]->PurchaseAmount . '</td>
		</tr>

		<tr>
			<td>' . _('已开票数量') . '</td>
			<td>' . $_SESSION['Contract'.$identifier]->LineItems[$_GET['Edit']]->PaymentVendorAmount . '</td>
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
echo '<tr>'
        . '<td>发票(收据)号码:</td>'
        . '<td>'.$_SESSION['Contract'.$identifier]->InvoiceNum.'</td>'
   . '</tr>';	
echo 	'<tr>
		<td>' . _('发票金额') . ':</td>
                <td><input type="text" class="number" name="InvoiceAmount" maxlength="20" size="15" value="' . $_SESSION['Contract'.$identifier]->InvoiceAmount . '" /></td>
	</tr>';   
 
echo 	'<tr>
		<td>' . _('税金额') . ':</td>
                <td><input type="text" class="number" name="TaxAmount" maxlength="20" size="15" value="' . $_SESSION['Contract'.$identifier]->TaxAmount . '" /></td>
	</tr>';   

echo 	'<tr>
		<td>' . _('付款金额') . ':</td>
                <td><input type="text" class="number" name="PaymentAmount" maxlength="20" size="15" value="' . $_SESSION['Contract'.$identifier]->PaymentAmount . '" /></td>
	</tr>';  

echo	'<tr>
		<td>' . _('收款银行') . ':</td>';

// any internal department allowed
$sql = "SELECT BankName,bankaccount FROM ap_bank_alls where disableflag=0 ORDER by BankName";

$result=DB_query($sql, $db);
echo '<td><select name="BankName">';
while( $apbanknow = DB_fetch_array($result) ) {
	if (isset($_SESSION['Contract'.$identifier]->BankName) AND $_SESSION['Contract'.$identifier]->BankName==$apbanknow['BankName'] ){
		echo '<option selected="selected" value="' . $apbanknow['BankName']  . '">' .$apbanknow['BankName']  .'  '.$apbanknow['bankaccount']  . '</option>';
	} else {
		echo '<option value="' . $apbanknow['BankName']   . '">' .$apbanknow['BankName']  .' '. $apbanknow['bankaccount']  . '</option>';
	}

}

echo '	</select></td>
		</tr>';




echo '</select></td>
	</tr>';
echo '<tr>'
        . '<td>供应商编号：</td>'
        . '<td>'.$_SESSION['Contract'.$identifier]->vendor_code.'</td>'
   . '</tr>';
echo '<tr>'
        . '<td>供应商名称：</td>'
        . '<td>'.$_SESSION['Contract'.$identifier]->vendor_name.'</td>'
   . '</tr>';
echo '  <tr>
              <td>' . _('发票日期'). ':</td>
              <td><input type="text" onfocus="WdatePicker()"  name="InvoiceDate" required="required" maxlength="10" size=“11” value="'.$_SESSION['Contract'.$identifier]->InvoiceDate.'"/></td>
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
		<th class="ascending">' .  _('采购业务订单') . '</th>
		<th class="ascending">' .  _('收货日期'). '</th>
		<th class="ascending">' .  _('料号'). '</th>
		<th class="ascending">' .  _('采购金额'). '</th>
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
	$ReceiveDate=date('Y-m-d H:i:s',$LineItems->ReceiveDate); 
	echo '<td>' . $LineItems->LineNumber . '</td>
	        <td>' . $LineItems->OrderNumber . '</td>
			<td>' . $ReceiveDate . '</td>
			<td>' . $LineItems->ItemName . '</td>
			<td>' . $LineItems->PurchaseAmount . '</td>			
			<td>' . $LineItems->PaymentVendorAmount . '</td>
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
echo '<tr><td colspan="2">' . _('请输入已收货的采购订单') . ':</td><td>';
echo '<input type="text" name="order_number" value="' . $_POST['order_number'] . '" size="20" maxlength="25" /></td>';
echo '<td><b>' . _('OR') . '</b></td><td>' . _('输入部分料号') . ':</td>
	<td>';
echo '<input type="text" name="item_name" value="' . $_POST['item_name'] . '" size="20" maxlength="25" /></td>';
echo '</tr>';
echo '</table>';
echo ' 
	<div class="centre">
		<input type="submit" name="Search" value="' . _('查找未开票或付款的采购订单,并输入数量增加到发票中') . '" />
	</div> 
	</div>
	</form>';

//or isset($_POST['Update']) 
if (isset($_POST['Search']) or isset($_POST['Next']) or isset($_POST['Prev']) or isset($_POST['Go'])){

        $vendor_name=$_SESSION['Contract'.$identifier]->vendor_name;
        $SQL = "SELECT  order_number,purchase_amount,receive_date,paymentvendoramount,item_name
                    FROM  sf_orders_all
                   where   (purchase_amount>paymentvendoramount or paymentvendoramount is null )    
				   and purchase_amount>0
                   and  supplier_name='".$vendor_name."' ";
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
				<th>' . _('订单规格说明') . '</th>
				<th>' . _('采购业务订单') . '</th>
				<th>' . _('收货日期') . '</th>
				<th>' . _('采购金额') . '</th>
				<th>' . _('已付款金额') . '</th>
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
             $receive_date=date('Y-m-d H:i:s', $myrow['receive_date']); 
			echo '<td><input type="submit" name="Select" value="' . $myrow['ItemName'] . '" /></td>
					<td>' . $myrow['order_number'] . '</td>
					<td>' . $receive_date . '</td>
					<td>' . $myrow['purchase_amount'] . '</td>
					<td>' . $myrow['paymentvendoramount'] . '</td> 					
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
				<th class="ascending">' . _('采购业务订单') . '</th>
				<th class="ascending">' . _('收货日期') . '</th>
				<th class="ascending">' . _('订单规格说明') . '</th>
				<th class="ascending">' . _('采购金额') . '</th>
				<th class="ascending">' . _('已付款金额') . '</th>
				<th class="ascending">' . _('未付款金额') . '</th>
				<th class="ascending">' . _('本次付款金额') . '</th>
				<th class="ascending">' . _('行备注') . '</th>
			</tr>';
	$ImageSource = _('No Image');

	$k=0; //row colour counter
	$i=0;
	while ($myrow=DB_fetch_array($SearchResult)) {
		$waitamount=$myrow['purchase_amount']-$myrow['paymentvendoramount'] ;		 
        $receive_date=date('Y-m-d H:i:s', $myrow['receive_date']); 
		echo   '<td>' . $myrow['order_number'] . '</td>
				<td>' . $receive_date . '</td>
				<td>' . $myrow['item_name'] . '</td>				
                <td class="number">' . $myrow['purchase_amount'] . '</td>
				<td class="number">' .$myrow['paymentvendoramount'] . '</td>
				<td class="number">' .$waitamount. '</td>
			    <td><input class="number"= ' . ($i==0 ? 'autofocus="autofocus"':'') . ' tabindex="'.($j+7).'" type="text" size="6" name="Amount'.$i.'" value="0" />    
				</td>
				<td><input type="text" name="LineNarrative'.$i.'" value="' . $myrow['LineNarrative'] . '" /> </td>
			</tr>';
	      echo ' <input type="hidden" name="ItemName'.$i.'" value="'.$myrow['item_name'].'" />';
		  echo '<input type="hidden" name="PurchaseAmount'.$i.'" value="' . $myrow['purchase_amount']. '" />';
		  echo '<input type="hidden" name="PaymentVendorAmount'.$i.'" value="' . $myrow['paymentvendoramount']. '" />';
		  echo '<input type="hidden" name="ReceiveDate'.$i.'" value="' . $myrow['receive_date'] . '" />';
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
