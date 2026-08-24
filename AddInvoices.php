<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include('includes/DefineInvoiceClass.php');

include('includes/session.inc');
$Title = _('应付账款立账处理');
$ViewTopic = '应付账款立账处理';
$BookMark = '应付账款立账处理';
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
        if(isset($_GET['vendor_id'])){
            if(!isset($_SESSION['Contract'.$identifier]->vendor_code) or $_SESSION['Contract'.$identifier]->vendor_code ==''){
                $vendor_id = $_GET['vendor_id'];
                $sql = 'select vendor_code,vendor_name from vendors where vendor_id ='."$vendor_id";
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
		prnMsg( _('你必须选择一个预计付款日期'), 'error');
		$InputError=1;
	}
	if ($InputError==0) {
		$_SESSION['Contract'.$identifier]->InvoiceNum=$_POST['InvoiceNum'];
		$_SESSION['Contract'.$identifier]->InvoiceAmount=$_POST['InvoiceAmount'];
		$_SESSION['Contract'.$identifier]->TaxAmount=$_POST['TaxAmount'];
		$_SESSION['Contract'.$identifier]->InvoiceDate=$_POST['InvoiceDate'];
		$_SESSION['Contract'.$identifier]->SchedulePaymentDate=$_POST['SchedulePaymentDate'];
        $_SESSION['Contract'.$identifier]->Narrative=$_POST['Narrative'];
	}
}

if (isset($_POST['Edit'])) {
	$_SESSION['Contract'.$identifier]->LineItems[$_POST['LineNumber']]->BilledQuantity=$_POST['BilledQuantity'];
	$_SESSION['Contract'.$identifier]->LineItems[$_POST['LineNumber']]->LineNarrative=$_POST['LineNarrative'];
}

if (isset($_GET['Delete'])) {
	unset($_SESSION['Contract'.$identifier]->LineItems[$_GET['Delete']]);
	echo '<br />';
	prnMsg( _('The line was successfully deleted'), 'success');
	echo '<br />';
}
 
foreach ($_POST as $key => $value) {
	if (mb_strstr($key,'ItemNo')) {
	    $Index=mb_substr($key, 6);
		if (filter_number_format($_POST['BilledQuantity'.$Index])>0) {
			$ItemNo=$value;		
			$LineNarrative=$_POST['LineNarrative'.$Index];
			$PO_NUM=$_POST['PO_NUM'.$Index];
			$PO_LINE=$_POST['PO_LINE'.$Index];
			$ItemNo=$_POST['ItemNo'.$Index];
			$DeliverQuantity=$_POST['DeliverQuantity'.$Index];
			$AlreadyBilledQuantity=$_POST['AlreadyBilledQuantity'.$Index];
			$Price=$_POST['Price'.$Index]; 
			$BilledQuantity = $_POST['BilledQuantity'.$Index]; 
			$BilledAmount=$_POST['BilledQuantity'.$Index] * $_POST['Price'.$Index];
			$LineNarrative=$_POST['LineNarrative'.$Index];
			 $_SESSION['Contract'.$identifier]->AddLine($PO_NUM, $PO_LINE, $ItemNo,$DeliverQuantity,$AlreadyBilledQuantity,$Price,$BilledQuantity,$BilledAmount, $LineNarrative); 
         
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
		prnMsg( _('你必须输入一个预计付款日期'), 'error');
                echo '<br /><div class="centre"><a a href="'. htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?identifier='.$identifier.'">' . _('返回发票') . '</a></div>';
		$InputError=1;
	}
 
   $SumAmount=0;
   foreach ($_SESSION['Contract'.$identifier]->LineItems as $LineItems) {
			$SumAmount=$SumAmount+ $LineItems->BilledAmount; 
	 }



   if ($SumAmount!=$_SESSION['Contract'.$identifier]->InvoiceAmount ){	   
       prnMsg( _('发票金额'.$_SESSION['Contract'.$identifier]->InvoiceAmount.' 与明细金额合计'.$SumAmount.'不等,请确认修改'), 'error');
	    echo '<br /><div class="centre"><a a href="'. htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?identifier='.$identifier.'">' . _('返回发票') . '</a></div>';
	   $InputError=1;
   }

 
	if ($InputError==0) {
		$v_date = strtotime(Date('Y-m-d H:i:s'));
		$InvoiceDate = strtotime( $_SESSION['Contract'.$identifier]->InvoiceDate ); 
		$SchedulePaymentDate = strtotime( $_SESSION['Contract'.$identifier]->SchedulePaymentDate ); 
		$HeaderSQL="INSERT INTO ap_invoice_headers_all (InvoiceNum,
											InvoiceAmount,
											taxAmount,
											vendor_code, 
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
											 '" . $_SESSION['Contract'.$identifier]->vendor_code . "', 
                                             '" . $_SESSION['Contract'.$identifier]->Narrative . "',
                                             '" . $v_date . "',
                                             '" . $v_date . "',
										     '" .$v_date . "',
                                             '" .$v_date . "',
											 '" . $_SESSION['UserID'] . "',
											 '" . $_SESSION['UserID'] . "')";
		$ErrMsg =_('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('发票不能存入数据库，原因是：');//'" . FormatDateForSQL($_SESSION['Contract'.$identifier]->DispatchDate) . "',
		$DbgMsg = _('The following SQL to insert the request header record was used');
		$Result = DB_query($HeaderSQL,$db,$ErrMsg,$DbgMsg,true);

		foreach ($_SESSION['Contract'.$identifier]->LineItems as $LineItems) {
			 $v_date = strtotime(Date('Y-m-d H:i:s'));
			$LineSQL="INSERT INTO ap_invoice_lines_all (invoicelinenum,
													invoicenum,
													item_no,													
													po_num,
													po_line,
													price,													
													billed_quantity,
													amount,
													linenarrative,													
													creationdate,
                                                    lastupdatedate,
                                                    createdby,
                                                    lastupdatedby)
												VALUES(
													'".$LineItems->LineNumber."',
													'".$_SESSION['Contract'.$identifier]->InvoiceNum."',
													'".$LineItems->ItemNo."',
													'".$LineItems->PO_NUM."',
													'".$LineItems->PO_LINE."', 
													'".$LineItems->Price."',
													'".$LineItems->BilledQuantity."',
													'".$LineItems->BilledAmount."',
													'".$LineItems->LineNarrative."',
										            '".$v_date . "',
                                                    '".$v_date . "',
										        	'".$_SESSION['UserID'] . "',
										        	'".$_SESSION['UserID'] . "')";
				
        
			$ErrMsg =_('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('发票行不能存入数据库，原因是：');
			$DbgMsg = _('The following SQL to insert the request header record was used');
			$Result = DB_query($LineSQL,$db,$ErrMsg,$DbgMsg,true);

             
		   $sql = "SELECT quantity_billed  
			   FROM po_lines_all
			    WHERE LINE=' ".$LineItems->PO_LINE."'
				and po_num=' ".$LineItems->PO_NUM."'
				";
		   $ErrMsg = _('The order details for this product cannot be retrieved because');
		   $WoResult = DB_query($sql,$db,$ErrMsg);
	       $WoRow = DB_fetch_row($WoResult);
		   if ($WoRow[0]!=null){
			$OldAmount =  $WoRow[0];
		   } else {
		 	$OldAmount = 0;
		   }

            $SumAmount=$OldAmount+$LineItems->BilledQuantity;
			$v_date = strtotime(Date('Y-m-d H:i:s'));
		 	 $UPDATESQL="UPDATE po_lines_all
							set quantity_billed='".$SumAmount."',
							       last_update_date='".$v_date . "',
								   last_updated_by='".$_SESSION['UserID'] . "'
                        WHERE   line='".$LineItems->PO_LINE."'
						and  po_num='".$LineItems->PO_NUM."'  "; 

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
		<a href="' . $RootPath . '/InvoiceForAccount.php">返回重新选择供应商</a>
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
			<td>' . _('行') . '</td>
			<td>' . $_SESSION['Contract'.$identifier]->LineItems[$_GET['Edit']]->LineNumber . '</td>
		</tr>
		<tr>
			<td>' . _('料号') . '</td>
			<td>' . $_SESSION['Contract'.$identifier]->LineItems[$_GET['Edit']]->ItemNo . '</td>
		</tr>

		<tr>
			<td>' . _('采购单') . '</td>
			<td>' . $_SESSION['Contract'.$identifier]->LineItems[$_GET['Edit']]->PO_NUM . '</td>
		</tr>

		<tr>
			<td>' . _('采购单行') . '</td>
			<td>' . $_SESSION['Contract'.$identifier]->LineItems[$_GET['Edit']]->PO_LINE . '</td>
		</tr>
 

		<tr>
			<td>' . _('入库数量') . '</td>
			<td>' . $_SESSION['Contract'.$identifier]->LineItems[$_GET['Edit']]->DeliverQuantity . '</td>
		</tr>

		<tr>
			<td>' . _('已立账数量') . '</td>
			<td>' . $_SESSION['Contract'.$identifier]->LineItems[$_GET['Edit']]->AlreadyBilledQuantity . '</td>
		</tr>

		<tr>
			<td>' . _('单价') . '</td>
			<td>' . $_SESSION['Contract'.$identifier]->LineItems[$_GET['Edit']]->Price . '</td>
		</tr>
	
		<tr>
			<td>' . _('本次立账数量') . '</td>
			<td><input type="text" class="number" name="BilledQuantity" value="' .  $_SESSION['Contract'.$identifier]->LineItems[$_GET['Edit']]->BilledQuantity  . '" /></td>
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
		<td>' . _('发票金额') . ':</td>
                <td><input type="text" class="number" name="InvoiceAmount" maxlength="20" size="15" value="' . $_SESSION['Contract'.$identifier]->InvoiceAmount . '" /></td>
	</tr>';   
 
echo 	'<tr>
		<td>' . _('税金额') . ':</td>
                <td><input type="text" class="number" name="TaxAmount" maxlength="20" size="15" value="' . $_SESSION['Contract'.$identifier]->TaxAmount . '" /></td>
	</tr>';   

echo '</select></td>
	</tr>';
echo '<tr>'
        . '<td>供应商代码：</td>'
        . '<td>'.$_SESSION['Contract'.$identifier]->vendor_code.'</td>'
   . '</tr>';
echo '<tr>'
        . '<td>供应商名称：</td>'
        . '<td>'.$_SESSION['Contract'.$identifier]->vendor_name.'</td>'
   . '</tr>';
echo '  <tr>
              <td>' . _('发票日期'). ':</td>
              <td><input type="text" class="date" alt="'.$_SESSION['DefaultDateFormat'].'" name="InvoiceDate" required="required" maxlength="10" size=“11” value="'.$_SESSION['Contract'.$identifier]->InvoiceDate.'"/></td>
        </tr>';
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
		<th colspan="10"> ' . _('发票行维护') . ' </th>
	</tr>
	<tr>
		<th>' .  _('行') . '</th>
		<th class="ascending">' .  _('采购单') . '</th>
		<th class="ascending">' .  _('采购单行'). '</th>
		<th class="ascending">' .  _('料号'). '</th>
		<th class="ascending">' .  _('已入库数量'). '</th>
		<th class="ascending">' .  _('已立账数量'). '</th>
		<th class="ascending">' .  _('单价'). '</th>
		<th class="ascending">' .  _('本次立账数量'). '</th>
		<th class="ascending">' .  _('本次立账金额'). '</th>
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

	$LineItems->BilledAmount=$LineItems->Price*$LineItems->BilledQuantity;
	echo '<td>' . $LineItems->LineNumber . '</td>
	        <td>' . $LineItems->PO_NUM . '</td>
			<td>' . $LineItems->PO_LINE . '</td>
			<td>' . $LineItems->ItemNo . '</td>
			<td>' . $LineItems->DeliverQuantity . '</td>			
			<td>' . $LineItems->AlreadyBilledQuantity . '</td>
			<td>' . $LineItems->Price . '</td>			
			<td>' . $LineItems->BilledQuantity . '</td>			
			<td>' . $LineItems->BilledAmount . '</td>
			<td>' . $LineItems->LineNarrative . '</td>
			<td><a href="'. htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?identifier='.$identifier.'&Edit='.$LineItems->LineNumber.'">' . _('Edit') . '</a></td>
			<td><a href="'. htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?identifier='.$identifier.'&Delete='.$LineItems->LineNumber.'">' . _('Delete') . '</a></td>
		</tr>';
}
echo '</table>
	<br />
	<div class="centre">
		<input type="submit" name="Submit" value="' . _('确认提交') . '" />
	</div> 
    </div>
    </form>';

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post"><input type="hidden" name = "identifier" value ="'.$identifier.'">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('查找待立账单据'). '</p>';
echo '<table cellpadding="3"  >';
echo '<tr><td colspan="2">' . _('请输入采购单号') . ':</td><td>';
echo '<input type="text" name="car_header_code" value="' . $_POST['car_header_code'] . '" size="20" maxlength="25" /></td>';
echo '<td><b>' . _('OR') . '</b></td><td>' . _('输入部分料号') . ':</td>
	<td>';
echo '<input type="text" name="item_no" value="' . $_POST['item_no'] . '" size="20" maxlength="25" /></td>';
echo '</tr>';
echo '</table>';
echo ' 
	<div class="centre">
		<input type="submit" name="Search" value="' . _('查找') . '" />
	</div> 
	</div>
	</form>';

//or isset($_POST['Update']) 
if (isset($_POST['Search']) or isset($_POST['Next']) or isset($_POST['Prev']) or isset($_POST['Go'])){

        $vendor_code=$_SESSION['Contract'.$identifier]->vendor_code;
        $SQL = "SELECT ch.po_num,ch.vendor,cl.line,cl.stockid,cl.item_desc,cl.price,cl.quantity_deliveried,cl.quantity_billed,cl.item_desc,cl.quantity
                    FROM  po_headers_all ch,
                           po_lines_all cl
                   where ch.po_num=cl.po_num
                   and (quantity_deliveried>quantity_billed or (quantity_billed is null and quantity_deliveried>0)  )   
                   and ch.vendor='".$vendor_code."' ";
//        }
        if (isset($_POST['po_num']) and $_POST['po_num'] != ''){
            $SQL = $SQL." and ch.po_num ".LIKE." '%".$_POST['po_num']."%' ";
        }
        if (isset($_POST['item_no']) and $_POST['item_no'] != ''){
            $SQL = $SQL." and  	stockid ".LIKE." '%".$_POST['item_no']."%' ";
        }
        
        $SQL = $SQL." ORDER BY ch.po_num";

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
		prnMsg (_('找不到未立账的采购单 '),'info');
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
				<th>' . _('采购单') . '</th>
				<th>' . _('采购单行') . '</th>
				<th>' . _('已入库量') . '</th>
				<th>' . _('已立账金额') . '</th>
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

			echo '<td><input type="submit" name="Select" value="' . $myrow['stockid'] . '" /></td>
					<td>' . $myrow['po_num'] . '</td>
					<td>' . $myrow['line'] . '</td>
					<td>' . $myrow['quantity_deliveried'] . '</td>
					<td>' . $myrow['quantity_billed'] . '</td> 					
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
		<div class="page_help_text">' . _('通过输入需求数量来选择采购单行，当完成后点击添加至发票。') . '</div>
		 
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
				<th class="ascending">' . _('采购单') . '</th>
				<th class="ascending">' . _('采购单行') . '</th>
				<th class="ascending">' . _('料号') . '</th>
				<th class="ascending">' . _('订单数量') . '</th>
				<th class="ascending">' . _('已入库') . '</th>
				<th class="ascending">' . _('已立账数量') . '</th>
				<th class="ascending">' . _('单价') . '</th>
				<th class="ascending">' . _('未立账数量') . '</th>
				<th class="ascending">' . _('本次立账数量') . '</th>
				<th class="ascending">' . _('发票行备注') . '</th>				
				<th class="ascending">' . _('料号描述') . '</th>
			</tr>';
	$ImageSource = _('No Image');

	$k=0; //row colour counter
	$i=0;
	while ($myrow=DB_fetch_array($SearchResult)) {
		$waitquantity=$myrow['quantity_deliveried']-$myrow['quantity_billed'] ;		 

		echo   '<td>' . $myrow['po_num'] . '</td>
				<td>' . $myrow['line'] . '</td>
				<td>' . $myrow['stockid'] . '</td>		
				<td class="number">' . $myrow['quantity'] . '</td>		
                <td class="number">' . $myrow['quantity_deliveried'] . '</td>
				<td class="number">' .$myrow['quantity_billed'] . '</td>
				<td class="number">' .$myrow['price'] . '</td>
				<td class="number">' .$waitquantity. '</td>
			    <td><input class="number"= ' . ($i==0 ? 'autofocus="autofocus"':'') . ' tabindex="'.($j+7).'" type="text" size="6" name="BilledQuantity'.$i.'" value="0" />    
				</td>
				<td><input type="text" name="LineNarrative'.$i.'" value="' . $myrow['LineNarrative'] . '" /> </td>
				<td>' . $myrow['item_desc'] . '</td>
			</tr>';
	      echo ' <input type="hidden" name="ItemNo'.$i.'" value="'.$myrow['stockid'].'" />';
		  echo '<input type="hidden" name="DeliverQuantity'.$i.'" value="' . $myrow['quantity_deliveried']. '" />';
		  echo '<input type="hidden" name="Price'.$i.'" value="' . $myrow['price']. '" />';
		  echo '<input type="hidden" name="AlreadyBilledQuantity'.$i.'" value="' . $myrow['quantity_billed']. '" />';
		  echo '<input type="hidden" name="PO_NUM'.$i.'" value="' . $myrow['po_num'] . '" />';
		  echo '<input type="hidden" name="PO_LINE'.$i.'" value="' . $myrow['line'] . '" />';
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
