<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include('includes/DefineCompactClass.php');

include('includes/session.inc');
$Title = _('新建合同');
$ViewTopic = '新建合同';
$BookMark = '新建合同';
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
	$_SESSION['Contract'.$identifier] = new CompactHeader();
        if(isset($_GET['CustomerID'])){
            if(!isset($_SESSION['Contract'.$identifier]->customer_code) or $_SESSION['Contract'.$identifier]->customer_code ==''){
                $customerid = $_GET['CustomerID'];
                $sql = 'select customer_code,customer_name from customers where customer_id ='."$customerid";
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
	if ($_POST['ContractCode']=='') {
		prnMsg( _('你必须输入一个合同编号'), 'error');
		$InputError=1;
	}
	if ($_POST['SalesMan']=='') {
		prnMsg( _('你必须选择一个业务员'), 'error');
		$InputError=1;
	}
        if ($_POST['EffectiveDate']=='') {
		prnMsg( _('你必须选择一个生效日期'), 'error');
		$InputError=1;
	}
        if ($_POST['DisableDate']=='') {
		prnMsg( _('你必须选择一个失效日期'), 'error');
		$InputError=1;
	}
	if ($InputError==0) {
		$_SESSION['Contract'.$identifier]->compact_code=$_POST['ContractCode'];
		$_SESSION['Contract'.$identifier]->sales_man=$_POST['SalesMan'];
		$_SESSION['Contract'.$identifier]->effective_date=$_POST['EffectiveDate'];
		$_SESSION['Contract'.$identifier]->disable_date=$_POST['DisableDate'];
                $_SESSION['Contract'.$identifier]->description=$_POST['Description'];
	}
}

if (isset($_POST['Edit'])) {
	$_SESSION['Contract'.$identifier]->LineItems[$_POST['LineNumber']]->Quantity=$_POST['Quantity'];
}

if (isset($_GET['Delete'])) {
	unset($_SESSION['Contract'.$identifier]->LineItems[$_GET['Delete']]);
	echo '<br />';
	prnMsg( _('The line was successfully deleted'), 'success');
	echo '<br />';
}

$flag = 0;
foreach ($_POST as $key => $value) {
	if (mb_strstr($key,'StockID')) {
		$Index=mb_substr($key, 7);
		if (filter_number_format($_POST['Quantity'.$Index])>0) {
			$StockID=$value; 
			$ItemDescription=$_POST['ItemDescription'.$Index];
			$DecimalPlaces=$_POST['DecimalPlaces'.$Index];
			$NewItem_array[$StockID] = filter_number_format($_POST['Quantity'.$Index]);
			$_POST['Units'.$StockID]=$_POST['Units'.$Index];
                        $ItemCost = $_POST['ItemCost'.$Index];
                        $DisposalType = $_POST['DisposalType'.$Index];
                        if ($ItemCost<=0 or $ItemCost==''){
                            $flag = 1;
                        } else{
                            $amount = $ItemCost * $NewItem_array[$StockID];
                            $_SESSION['Contract'.$identifier]->AddLine($StockID, $ItemDescription, $NewItem_array[$StockID],$ItemCost,$_POST['Units'.$StockID], $DecimalPlaces,$DisposalType,$amount);    
                        }
                }
	}
}
if ($flag != 0 ){
    prnMsg( _('处理单价为零或为空的不会添加为细节，请重新填写'), 'warn');
}


if (isset($_POST['Submit'])) {
	DB_Txn_Begin($db);
	$InputError=0;
	if ($_SESSION['Contract'.$identifier]->compact_code=='') {
		prnMsg( _('你必须输入一个合同编号'), 'error');
                echo '<br /><div class="centre"><a a href="'. htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?identifier='.$identifier.'">' . _('返回合同') . '</a></div>';
		$InputError=1;
	}
        if ($_SESSION['Contract'.$identifier]->sales_man=='') {
		prnMsg( _('你必须选择一个业务员'), 'error');
                echo '<br /><div class="centre"><a a href="'. htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?identifier='.$identifier.'">' . _('返回合同') . '</a></div>';
		$InputError=1;
	}
        if ($_SESSION['Contract'.$identifier]->effective_date=='') {
		prnMsg( _('你必须输入一个生效日期'), 'error');
                echo '<br /><div class="centre"><a a href="'. htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?identifier='.$identifier.'">' . _('返回合同') . '</a></div>';
		$InputError=1;
	}
        if ($_SESSION['Contract'.$identifier]->disable_date=='') {
		prnMsg( _('你必须输入一个失效日期'), 'error');
                echo '<br /><div class="centre"><a a href="'. htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?identifier='.$identifier.'">' . _('返回合同') . '</a></div>';
		$InputError=1;
	}
//	if ($_SESSION['Contract'.$identifier]->Location=='') {
//		prnMsg( _('You must select a Location to request the items from'), 'error');
//		$InputError=1;
//	}
	if ($InputError==0) {
//		$RequestNo = GetNextTransNo(38, $db);
		$HeaderSQL="INSERT INTO compactheaders (compact_code,
											sales_man,
											customer_code,
											customer_name,
											description,
                                                                                        effective_date,
                                                                                        disable_date,
                                                                                        status,
                                                                                        creation_date,
                                                                                        last_update_date,
                                                                                        created_by,
                                                                                        last_updated_by)
										VALUES(
											'" . $_SESSION['Contract'.$identifier]->compact_code . "',
											'" . $_SESSION['Contract'.$identifier]->sales_man . "',
											'" . $_SESSION['Contract'.$identifier]->customer_code . "',
                                                                                        '" . $_SESSION['Contract'.$identifier]->customer_name . "',
                                                                                        '" . $_SESSION['Contract'.$identifier]->description . "',
                                                                                        '" . $_SESSION['Contract'.$identifier]->effective_date . "',
                                                                                        '" . $_SESSION['Contract'.$identifier]->disable_date . "',
                                                                                        0,
											now(),
                                                                                        now(),
											'" . $_SESSION['UserID'] . "','" . $_SESSION['UserID'] . "')";
		$ErrMsg =_('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('合同不能存入数据库，原因是：');//'" . FormatDateForSQL($_SESSION['Contract'.$identifier]->DispatchDate) . "',
		$DbgMsg = _('The following SQL to insert the request header record was used');
		$Result = DB_query($HeaderSQL,$db,$ErrMsg,$DbgMsg,true);

		foreach ($_SESSION['Contract'.$identifier]->LineItems as $LineItems) {
			$LineSQL="INSERT INTO compactlines (compact_code,
													line_num,
													item_no,
													item_desc,
													uom,
													item_cost,
                                                                                                        quantity,
                                                                                                        disposal_type,
                                                                                                        creation_date,
                                                                                                        last_update_date,
                                                                                                        created_by,
                                                                                                        last_updated_by)
												VALUES(
                                                                                                        '" . $_SESSION['Contract'.$identifier]->compact_code . "',
													'".$LineItems->LineNumber."',
													'".$LineItems->StockID."',
                                                                                                        '".$LineItems->ItemDescription."',
                                                                                                        '".$LineItems->UOM."',
                                                                                                        '".$LineItems->ItemCost."',
													'".$LineItems->Quantity."',
													'".$LineItems->Type."',
													now(),
                                                                                                        now(),
                                                                                                        '".$_SESSION['UserID']."', '".$_SESSION['UserID']."')";
			$ErrMsg =_('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('合同细节不能存入数据库，原因是：');
			$DbgMsg = _('The following SQL to insert the request header record was used');
			$Result = DB_query($LineSQL,$db,$ErrMsg,$DbgMsg,true);
		}

//		$EmailSQL="SELECT email
//					FROM www_users, departments
//					WHERE departments.authoriser = www_users.userid
//						AND departments.departmentid = '" . $_SESSION['Contract'.$identifier]->Department ."'";
//		$EmailResult = DB_query($EmailSQL,$db);
//		if ($myEmail=DB_fetch_array($EmailResult)){
//			$ConfirmationText = _('An internal stock request has been created and is waiting for your authoritation');
//			$EmailSubject = _('Internal Stock Request needs your authoritation');
//			 if($_SESSION['SmtpSetting']==0){
//			       mail($myEmail['email'],$EmailSubject,$ConfirmationText);
//			}else{
//				include('includes/htmlMimeMail.php');
//				$mail = new htmlMimeMail();
//				$mail->setSubject($EmailSubject);
//				$mail->setText($ConfirmationText);
//				$result = SendmailBySmtp($mail,array($myEmail['email']));
//			}
//
//		}
            DB_Txn_Commit($db);
            prnMsg( _('合同建立成功！'), 'success');
            echo '<br /><div class="centre"><a href="' . $RootPath . '/SearchCustomerForCompact.php">' . _('建立新的合同') . '</a></div>';
            unset($_SESSION['Contract'.$identifier]);
	}
	include('includes/footer.inc');
	exit;
}
echo '	<div class="centre">
		<a href="' . $RootPath . '/SearchCustomerForCompact.php">返回选择客户</a>
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
			<th colspan="2"><h4>' . _('Edit the Request Line') . '</h4></th>
		</tr>';
	echo '<tr>
			<td>' . _('Line number') . '</td>
			<td>' . $_SESSION['Contract'.$identifier]->LineItems[$_GET['Edit']]->LineNumber . '</td>
		</tr>
		<tr>
			<td>' . _('Stock Code') . '</td>
			<td>' . $_SESSION['Contract'.$identifier]->LineItems[$_GET['Edit']]->StockID . '</td>
		</tr>
		<tr>
			<td>' . _('Item Description') . '</td>
			<td>' . $_SESSION['Contract'.$identifier]->LineItems[$_GET['Edit']]->ItemDescription . '</td>
		</tr>
		<tr>
			<td>' . _('Unit of Measure') . '</td>
			<td>' . $_SESSION['Contract'.$identifier]->LineItems[$_GET['Edit']]->UOM . '</td>
		</tr>
		<tr>
			<td>' . _('Quantity Requested') . '</td>
			<td><input type="text" class="number" name="Quantity" value="' . locale_number_format($_SESSION['Contract'.$identifier]->LineItems[$_GET['Edit']]->Quantity, $_SESSION['Contract'.$identifier]->LineItems[$_GET['Edit']]->DecimalPlaces) . '" /></td>
		</tr>';
	echo '<input type="hidden" name="LineNumber" value="' . $_SESSION['Contract'.$identifier]->LineItems[$_GET['Edit']]->LineNumber . '" />';
	echo '</table>
		<br />';
	echo '<div class="centre">
			<input type="submit" name="Edit" value="' . _('Update Line') . '" />
		</div>
        </div>
		</form>';
	include('includes/footer.inc');
	exit;
}

echo '<form action="'. htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post"><input type="hidden" name = "identifier" value ="'.$identifier.'">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<table class="selection">';
echo '<tr>
		<th colspan="2"><h4>' . _('合同总体信息') . '</h4></th>
	</tr>';
echo 	'<tr>
		<td>' . _('合同编号') . ':</td>
                <td><input type="text" name="ContractCode" maxlength="20" size="15" value="' . $_SESSION['Contract'.$identifier]->compact_code . '" /></td>
	</tr>';        
echo	'<tr>
		<td>' . _('业务员') . ':</td>';

// any internal department allowed
$sql = "SELECT salesmanname FROM salesman ORDER by salesmancode";

$result=DB_query($sql, $db);
echo '<td><select name="SalesMan">';
while( $Salesmanrow = DB_fetch_array($result) ) {
	 if (isset($_SESSION['Contract'.$identifier]->sales_man) AND $_SESSION['Contract'.$identifier]->sales_man==$Salesmanrow['salesmanname']){
		echo '<option selected="selected" value="' . $Salesmanrow['salesmanname'] . '">' . $Salesmanrow['salesmanname'] . '</option>';
	 } else {
		echo '<option value="' . $Salesmanrow['salesmanname'] . '">' . $Salesmanrow['salesmanname']  . '</option>';
	 }
}

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
              <td>' . _('生效日期'). ':</td>
              <td><input type="text" class="date" alt="'.$_SESSION['DefaultDateFormat'].'" name="EffectiveDate" required="required" maxlength="10" size=“11” value="'.$_SESSION['Contract'.$identifier]->effective_date.'"/></td>
        </tr>';
echo '  <tr>
              <td>' . _('失效日期'). ':</td>
              <td><input type="text" class="date" alt="'.$_SESSION['DefaultDateFormat'].'" name="DisableDate" required="required" maxlength="10" size=“11” value="'.$_SESSION['Contract'.$identifier]->disable_date.'"/></td>
        </tr>';
echo '  <tr>
              <td>' . _('备注'). ':</td>
              <td><textarea  name="Description" cols="40" rows="3">' . stripslashes($_SESSION['Contract'.$identifier]->description) . '</textarea></td>
        </tr>';
echo '</table>';

echo '<div class="centre">
		<input type="submit" name="Update" value="' . _('更新合同总体信息并添加细节') . '" />
	</div>
    </div>
	</form>';

if (!isset($_SESSION['Contract'.$identifier]->compact_code)) {
	include('includes/footer.inc');
	exit;
}
//if (!isset($_SESSION['Contract'.$identifier]->sales_man)) {
//	include('includes/footer.inc');
//	exit;
//}
//if (!isset($_SESSION['Contract'.$identifier]->effective_date)) {
//	include('includes/footer.inc');
//	exit;
//}
//if (!isset($_SESSION['Contract'.$identifier]->disable_date)) {
//	include('includes/footer.inc');
//	exit;
//}


$i = 0; //Line Item Array pointer
echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post"><input type="hidden" name = "identifier" value ="'.$identifier.'">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<br />
	<table class="selection">
	<tr>
		<th colspan="7"><h4>' . _('合同细节维护') . '</h4></th>
	</tr>
	<tr>
		<th>' .  _('Line Number') . '</th>
		<th class="ascending">' .  _('Item Code') . '</th>
		<th class="ascending">' .  _('Item Description'). '</th>
                <th class="ascending">' .  _('处置方式'). '</th>
		<th class="ascending">' .  _('签订数量'). '</th>
		<th>' .  _('UOM'). '</th>
                <th>' .  _('处理单价'). '</th>
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
			<td>' . $LineItems->StockID . '</td>
			<td>' . $LineItems->ItemDescription . '</td>
                        <td>' . $LineItems->Type . '</td>
			<td class="number">' . locale_number_format($LineItems->Quantity, $LineItems->DecimalPlaces) . '</td>
			<td>' . $LineItems->UOM . '</td>
                        <td class="number">' . locale_number_format($LineItems->ItemCost, $LineItems->DecimalPlaces) . '</td>
			<td><a href="'. htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?identifier='.$identifier.'&Edit='.$LineItems->LineNumber.'">' . _('Edit') . '</a></td>
			<td><a href="'. htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?identifier='.$identifier.'&Delete='.$LineItems->LineNumber.'">' . _('Delete') . '</a></td>
		</tr>';
}
echo '</table>
	<br />
	<div class="centre">
		<input type="submit" name="Submit" value="' . _('Submit') . '" />
	</div>
	<br />
    </div>
    </form>';

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post"><input type="hidden" name = "identifier" value ="'.$identifier.'">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('查找物品'). '</p>';
echo '<table cellpadding="3" class="selection">';
echo '<tr><td colspan="2">' . _('请输入部分料号名称') . ':</td><td>';
echo '<input type="text" name="StockKey" value="' . $_POST['StockKey'] . '" size="20" maxlength="25" /></td>';
echo '<td><b>' . _('OR') . '</b></td><td>' . _('输入部分料号描述') . ':</td>
	<td>';
echo '<input type="text" name="StockDesc" value="' . $_POST['StockDesc'] . '" size="20" maxlength="25" /></td>';
echo '</tr>';
echo '</table>';
echo '
	<br />
	<div class="centre">
		<input type="submit" name="Search" value="' . _('Search Now') . '" />
	</div>
	<br />
	</div>
	</form>';
//or isset($_POST['Update']) 
if (isset($_POST['Search']) or isset($_POST['Next']) or isset($_POST['Prev']) or isset($_POST['Go'])){

//	if ($_POST['Keywords']!='' AND $_POST['StockCode']=='') {
//		prnMsg ( _('Order Item description has been used in search'), 'warn' );
//	} elseif ($_POST['StockCode']!='' AND $_POST['Keywords']=='') {
//		prnMsg ( _('Stock Code has been used in search'), 'warn' );
//	} elseif ($_POST['Keywords']=='' AND $_POST['StockCode']=='') {
//		prnMsg ( _('Stock Category has been used in search'), 'warn' );
//	}
//        $SQL = '1';
//	if (isset($_POST['Keywords']) AND mb_strlen($_POST['Keywords'])>0) {
//		//insert wildcard characters in spaces
//		$_POST['Keywords'] = mb_strtoupper($_POST['Keywords']);
//		$SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';
//                 
//		if ($_POST['StockCat']=='All'){
//			$SQL = "SELECT stockmaster.stockid,
//							stockmaster.description,
//							stockmaster.units as stockunits,
//							stockmaster.decimalplaces
//					FROM stockmaster,
//						stockcategory
//					WHERE stockmaster.categoryid=stockcategory.categoryid
//						AND stockmaster.mbflag <>'G'
//						AND stockmaster.description " . LIKE . " '" . $SearchString . "'
//						AND stockmaster.discontinued=0
//					ORDER BY stockmaster.stockid";
//		} else {
//			$SQL = "SELECT stockmaster.stockid,
//							stockmaster.description,
//							stockmaster.units as stockunits,
//							stockmaster.decimalplaces
//					FROM stockmaster,
//						stockcategory
//					WHERE stockmaster.categoryid=stockcategory.categoryid
//						AND stockmaster.mbflag <>'G'
//						AND stockmaster.discontinued=0
//						AND stockmaster.description " . LIKE . " '" . $SearchString . "'
//						AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
//					ORDER BY stockmaster.stockid";
//		}
//
//	} elseif (mb_strlen($_POST['StockCode'])>0){
//
//		$_POST['StockCode'] = mb_strtoupper($_POST['StockCode']);
//		$SearchString = '%' . $_POST['StockCode'] . '%';
//
//		if ($_POST['StockCat']=='All'){
//			$SQL = "SELECT stockmaster.stockid,
//							stockmaster.description,
//							stockmaster.units as stockunits,
//							stockmaster.decimalplaces
//					FROM stockmaster,
//						stockcategory
//					WHERE stockmaster.categoryid=stockcategory.categoryid
//						AND stockmaster.stockid " . LIKE . " '" . $SearchString . "'
//						AND stockmaster.mbflag <>'G'
//						AND stockmaster.discontinued=0
//					ORDER BY stockmaster.stockid";
//		} else {
//			$SQL = "SELECT stockmaster.stockid,
//							stockmaster.description,
//							stockmaster.units as stockunits,
//							stockmaster.decimalplaces
//					FROM stockmaster,
//						stockcategory
//					WHERE stockmaster.categoryid=stockcategory.categoryid
//						AND stockmaster.stockid " . LIKE . " '" . $SearchString . "'
//						AND stockmaster.mbflag <>'G'
//						AND stockmaster.discontinued=0
//						AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
//					ORDER BY stockmaster.stockid";
//		}
//
//	} else {
//		if ($_POST['StockCat']=='All'){
//			$SQL = "SELECT stockmaster.stockid,
//							stockmaster.description,
//							stockmaster.units as stockunits,
//							stockmaster.decimalplaces
//					FROM stockmaster,
//						stockcategory
//					WHERE stockmaster.categoryid=stockcategory.categoryid
//					ORDER BY stockmaster.stockid";
//		} else {
//			$SQL = "SELECT stockmaster.stockid,
//							stockmaster.description,
//							stockmaster.units as stockunits,
//							stockmaster.decimalplaces
//					FROM stockmaster,
//						stockcategory
//					WHERE stockmaster.categoryid=stockcategory.categoryid
//						AND stockmaster.mbflag <>'G'
//						AND stockmaster.discontinued=0
//						AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
//					ORDER BY stockmaster.stockid";
//		}
//	}
//        if ($SQL == '1'){
        $SQL = "SELECT stockmaster.stockid,
                                                    stockmaster.description,
                                                    stockmaster.units as stockunits,
                                                    stockmaster.decimalplaces,
                                                    stockmaster.disposal
                                    FROM stockmaster
                                    WHERE stockmaster.discontinued=0";
//        }
        if (isset($_POST['StockKey']) and $_POST['StockKey'] != ''){
            $SQL = $SQL." and stockid ".LIKE." '%".$_POST['StockKey']."%' ";
        }
        if (isset($_POST['StockDesc']) and $_POST['StockDesc'] != ''){
            $SQL = $SQL." and description ".LIKE." '%".$_POST['StockDesc']."%' ";
        }
        
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
		prnMsg (_('There are no products available meeting the criteria specified'),'info');
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
				<th>' . _('Code') . '</th>
				<th>' . _('Description') . '</th>
				<th>' . _('Total Qty On Hand') . '</th>
				<th>' . _('Units') . '</th>
				<th>' . _('Stock Status') . '</th>
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
				$qoh = locale_number_format($myrow['qoh'], $myrow['decimalplaces']);
			}
			if ($myrow['discontinued']==1){
				$ItemStatus = '<p class="bad">' . _('Obsolete') . '</p>';
			} else {
				$ItemStatus ='';
			}

			echo '<td><input type="submit" name="Select" value="' . $myrow['stockid'] . '" /></td>
					<td>' . $myrow['description'] . '</td>
					<td class="number">' . $qoh . '</td>
					<td>' . $myrow['units'] . '</td>
					<td><a target="_blank" href="' . $RootPath . '/StockStatus.php?StockID=' . $myrow['stockid'].'">' . _('View') . '</a></td>
					<td>' . $ItemStatus . '</td>
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
	echo '<br />
		<div class="page_help_text">' . _('通过输入需求数量来选择物料，当完成后点击添加至合同。') . '</div>
		<br />
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
				<input tabindex="'.($j+9).'" type="submit" value="'._('添加至合同').'" /></td>';
		if ($Offset >= 0){
        echo '
                            <td>
                                    <input type="hidden" name="NextList" value="'.($Offset+1).'" />
                                    <input tabindex="'.($j+10).'" type="submit" name="Next" value="'._('Next').'" /></td>';
        }
		echo '
			</tr>
			<tr>
				<th class="ascending">' . _('Code') . '</th>
				<th class="ascending">' . _('Description') . '</th>
				<th>' . _('Units') . '</th>
                                <th>'._('处置方式').'</th>
				<th class="ascending">' . _('Quantity') . '</th>
                                <th class="ascending">' . _('处理单价') . '</th>
			</tr>';
	$ImageSource = _('No Image');

	$k=0; //row colour counter
	$i=0;
	while ($myrow=DB_fetch_array($SearchResult)) {
		if ($myrow['decimalplaces']=='') {
			$DecimalPlacesSQL="SELECT decimalplaces
								FROM stockmaster
								WHERE stockid='" .$myrow['stockid'] . "'";
			$DecimalPlacesResult = DB_query($DecimalPlacesSQL, $db);
			$DecimalPlacesRow = DB_fetch_array($DecimalPlacesResult);
			$DecimalPlaces = $DecimalPlacesRow['decimalplaces'];
		} else {
			$DecimalPlaces=$myrow['decimalplaces'];
		}

		$QOHSQL = "SELECT sum(locstock.quantity) AS qoh
							   FROM locstock
							   WHERE locstock.stockid='" .$myrow['stockid'] . "' AND
							   loccode = '" . $_SESSION['Contract'.$identifier]->Location . "'";
		$QOHResult =  DB_query($QOHSQL,$db);
		$QOHRow = DB_fetch_array($QOHResult);
		$QOH = $QOHRow['qoh'];

		// Find the quantity on outstanding sales orders
		$sql = "SELECT SUM(salesorderdetails.quantity-salesorderdetails.qtyinvoiced) AS dem
				 FROM salesorderdetails INNER JOIN salesorders
				 ON salesorders.orderno = salesorderdetails.orderno
				 WHERE salesorders.fromstkloc='" . $_SESSION['Contract'.$identifier]->Location . "'
				 AND salesorderdetails.completed=0
				 AND salesorders.quotation=0
				 AND salesorderdetails.stkcode='" . $myrow['stockid'] . "'";
		$ErrMsg = _('The demand for this product from') . ' ' . $_SESSION['Contract'.$identifier]->Location . ' ' . _('cannot be retrieved because');
		$DemandResult = DB_query($sql,$db,$ErrMsg);

		$DemandRow = DB_fetch_row($DemandResult);
		if ($DemandRow[0] != null){
			$DemandQty =  $DemandRow[0];
		} else {
		  $DemandQty = 0;
		}

		// Find the quantity on purchase orders
		$sql = "SELECT SUM(purchorderdetails.quantityord-purchorderdetails.quantityrecd)*purchorderdetails.conversionfactor AS dem
				 FROM purchorderdetails LEFT JOIN purchorders
					ON purchorderdetails.orderno=purchorders.orderno
				 WHERE purchorderdetails.completed=0
				 AND purchorders.status<>'Cancelled'
				 AND purchorders.status<>'Rejected'
				 AND purchorders.status<>'Completed'
				AND purchorderdetails.itemcode='" . $myrow['stockid'] . "'";

		$ErrMsg = _('The order details for this product cannot be retrieved because');
		$PurchResult = DB_query($sql,$db,$ErrMsg);

		$PurchRow = DB_fetch_row($PurchResult);
		if ($PurchRow[0]!=null){
			$PurchQty =  $PurchRow[0];
		} else {
			$PurchQty = 0;
		}

		// Find the quantity on works orders
		$sql = "SELECT SUM(woitems.qtyreqd - woitems.qtyrecd) AS dedm
			   FROM woitems
			   WHERE stockid='" . $myrow['stockid'] ."'";
		$ErrMsg = _('The order details for this product cannot be retrieved because');
		$WoResult = DB_query($sql,$db,$ErrMsg);

		$WoRow = DB_fetch_row($WoResult);
		if ($WoRow[0]!=null){
			$WoQty =  $WoRow[0];
		} else {
			$WoQty = 0;
		}

		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}
		$OnOrder = $PurchQty + $WoQty;
		$Available = $QOH - $DemandQty + $OnOrder;
		echo '<td>' . $myrow['stockid'] . '</td>
				<td class="number">' . $myrow['description'] . '</td>
				<td class="number">' . $myrow['stockunits'] . '</td>
                                <td>' . $myrow['disposal'] . '</td>
				<td><input class="number" ' . ($i==0 ? 'autofocus="autofocus"':'') . ' tabindex="'.($j+7).'" type="text" size="6" name="Quantity'.$i.'" value="0" />
                                <td><input class="number" ' . ($i==0 ? 'autofocus="autofocus"':'') . ' tabindex="'.($j+8).'" type="text" size="6" name="ItemCost'.$i.'" value="" />
				<input type="hidden" name="StockID'.$i.'" value="'.$myrow['stockid'].'" />
				</td>
			</tr>';
		echo '<input type="hidden" name="DecimalPlaces'.$i.'" value="' . $myrow['decimalplaces'] . '" />';
                echo '<input type="hidden" name="DisposalType'.$i.'" value="' . $myrow['disposal'] . '" />';
		echo '<input type="hidden" name="ItemDescription'.$i.'" value="' . $myrow['description'] . '" />';
		echo '<input type="hidden" name="Units'.$i.'" value="' . $myrow['stockunits'] . '" />';
		$i++;
	}
#end of while loop
	echo '<tr>
			<td><input type="hidden" name="Previous" value="'.($Offset-1).'" />
				<input tabindex="'.($j+7).'" type="submit" name="Prev" value="'._('Prev').'" /></td>
			<td style="text-align:center" colspan="6"><input type="hidden" name="order_items" value="1" />
				<input tabindex="'.($j+8).'" type="submit" value="'._('添加至合同').'" /></td>';
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
