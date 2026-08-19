<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include('includes/DefineQuoterClass.php');

include('includes/session.inc');
$Title = _('新建销售订单');
$ViewTopic = '新建销售订单';
$BookMark = '新建销售订单';
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
        if(isset($_GET['customer_id'])){
            if(!isset($_SESSION['Contract'.$identifier]->customer_code) or $_SESSION['Contract'.$identifier]->customer_code ==''){
                $customer_id = $_GET['customer_id'];
                $sql = 'select customer_code,customer_name from customers where customer_id ='."$customer_id"; 
				$_SESSION['Contract'.$identifier]->customer_id=$customer_id;
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
//	if ($_POST['ContractCode']=='') {
//		prnMsg( _('你必须输入一个销售订单编号'), 'error');
//		$InputError=1;
//	}
        
//        $sql = "select count(*) flag from compactheaders where compact_code = '" . $_POST['ContractCode'] . "' ";
//        $result = DB_query($sql, $db);
//        while ($myrow = DB_fetch_array($result)){
//            if ($myrow['flag'] != 0){
//                prnMsg( _('该销售订单编号已存在，请重新输入销售订单编号'), 'error');
//		$InputError=1;
//            }
//        }
//        $_SESSION['Contract']->Narrative = $_POST['Narrative'];
//	if ($_POST['SalesMan']=='') {
//		prnMsg( _('你必须选择一个报价员'), 'error');
//		$InputError=1;
//	}
//        if ($_POST['EffectiveDate']=='') {
//		prnMsg( _('你必须选择一个生效日期'), 'error');
//		$InputError=1;
//	}
	if ($InputError==0) {
		$_SESSION['Contract'.$identifier]->so_num=$_POST['so_num'];
		
//                $_SESSION['Contract'.$identifier]->compact_type=$_POST['CompactType'];
		$_SESSION['Contract'.$identifier]->sales_man=$_POST['SalesMan'];
//		$_SESSION['Contract'.$identifier]->effective_date=  FormatDateForSQL($_POST['EffectiveDate']);
//                if ($_POST['DisableDate'] !==''){
//		  $_SESSION['Contract'.$identifier]->disable_date=  FormatDateForSQL($_POST['DisableDate']);
//                }else{
//                    $_SESSION['Contract'.$identifier]->disable_date='';
//                }
                $_SESSION['Contract'.$identifier]->NeedDate=$_POST['NeedDate'];
                $_SESSION['Contract'.$identifier]->description=$_POST['Description'];
	}
}

if (isset($_POST['Edit'])) {
	$_SESSION['Contract'.$identifier]->LineItems[$_POST['LineNumber']]->Quantity=$_POST['Quantity'];
        $_SESSION['Contract'.$identifier]->LineItems[$_POST['LineNumber']]->ItemCost=$_POST['ItemCost'];
        $_SESSION['Contract'.$identifier]->LineItems[$_POST['LineNumber']]->amount=$_POST['ItemCost']*$_POST['Quantity'];
}

if (isset($_GET['Delete'])) {
	unset($_SESSION['Contract'.$identifier]->LineItems[$_GET['Delete']]);
	echo '<br />';
	prnMsg( _('The line was successfully deleted'), 'success');
	echo '<br />';
}

$flag = 0;
foreach ($_POST as $key => $value) {
	if (mb_strstr($key,'item_no')) {
		$Index=mb_substr($key, 7);
//                 echo $value;
		if (filter_number_format($_POST['Quantity'.$Index])>0) {
			$StockID=$value; 
			$ItemDescription=$_POST['item_desc'.$Index];
			$NewItem_array[$StockID] = filter_number_format($_POST['Quantity'.$Index]);
			$_POST['Units'.$StockID]=$_POST['Units'.$Index];
                        $ItemCost = filter_number_format($_POST['po_price'.$Index]);
//                        $UOM=$_POST['UOM'.$Index];
//                        if ($ItemCost<=0 or $ItemCost==''){
//                            $flag = 1;
//                        } else{
                            $amount = $ItemCost * $NewItem_array[$StockID];
//                            echo $amount;
                            $_SESSION['Contract'.$identifier]->AddLine($StockID, $ItemDescription, $NewItem_array[$StockID],$ItemCost,$_POST['Units'.$StockID], $amount);    
//                        }
                }
	}
}
if ($flag != 0 ){
    prnMsg( _('处理单价为零或为空的不会添加为细节，请重新填写'), 'warn');
}


if (isset($_POST['Submit'])) {
    $sumamount=0.00;
     $date = date('Ymd');
        $sql_num = "select 	(
		CASE WHEN substr(max(so_num) ,-2,1) = 0 THEN
			RIGHT (
				'100' + (
					max(substr(so_num ,- 1)) + 1
				),
				2
			)
		ELSE
			substr(max(so_num),-2,2) + 1
		END
        ) so_num from so_headers_all where substr(so_num,-10,8) = '" . $date . "'";
        $result_num = DB_query($sql_num, $db);
        $rownum = DB_num_rows($result_num);
//    echo $rownum;
        while ($v = DB_fetch_array($result_num)) {
            if ($v['so_num'] == null) {
                $OrderNum = 'SO'.$date . '01';
            } else {
                $OrderNum =  'SO'. $date . $v['so_num'];
            }
        }
        $_SESSION['Contract'.$identifier]->so_num=$OrderNum;
	DB_Txn_Begin($db);
	$InputError=0;
//	if ($_SESSION['Contract'.$identifier]->compact_code=='') {
//		prnMsg( _('你必须输入一个销售订单编号'), 'error');
//                echo '<br /><div class="centre"><a a href="'. htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?identifier='.$identifier.'">' . _('返回销售订单') . '</a></div>';
//		$InputError=1;
//	}    
//        if ($_SESSION['Contract'.$identifier]->sales_man=='') {
//		prnMsg( _('你必须选择一个报价员'), 'error');
//                echo '<br /><div class="centre"><a a href="'. htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?identifier='.$identifier.'">' . _('返回销售订单') . '</a></div>';
//		$InputError=1;
//	}
//        if ($_SESSION['Contract'.$identifier]->effective_date=='') {
//		prnMsg( _('你必须输入一个生效日期'), 'error');
//                echo '<br /><div class="centre"><a a href="'. htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?identifier='.$identifier.'">' . _('返回销售订单') . '</a></div>';
//		$InputError=1;
//	}
       if (empty($_SESSION['Contract'.$identifier]->LineItems)){
                prnMsg( _('你必须输入填入一个销售订单行'), 'error');
                echo '<br /><div class="centre"><a a href="'. htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?identifier='.$identifier.'">' . _('返回销售订单') . '</a></div>';
                $InputError=1;
        }
//	if ($_SESSION['Contract'.$identifier]->Location=='') {
//		prnMsg( _('You must select a Location to request the items from'), 'error');
//		$InputError=1;
//	}
	if ($InputError==0) {
//		$RequestNo = GetNextTransNo(38, $db);
            $create_date = strtotime(Date('Y-m-d H:i:s'));
            $need_date=strtotime($_SESSION['Contract'.$identifier]->NeedDate);

		foreach ($_SESSION['Contract'.$identifier]->LineItems as $LineItems) {
			$LineSQL="INSERT INTO so_lines_all (so_num,
													line,
													stockid,
													item_desc,
													price,
                                                    amount,
                                                    quantity,
                                                    uom,
                                                    note,
                                                    status,
                                                    creation_date,
                                                    last_update_date,
                                                    created_by,
                                                    last_updated_by)
												VALUES(
                                                    '" . $_SESSION['Contract'.$identifier]->so_num . "',
													'".$LineItems->LineNumber."',
													'".$LineItems->StockID."',
                                                    '".$LineItems->ItemDescription."',
                                                    '".$LineItems->ItemCost."',
                                                    '".$LineItems->amount."',  
													'".$LineItems->Quantity."',
                                                    '".$LineItems->UOM."', 
                                                    '".$LineItems->note."',
                                                    'INPROCESS',
													$create_date,
                                                     $create_date,
                                                       '".$_SESSION['UserID']."', '".$_SESSION['UserID']."')";
			$ErrMsg =_('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR  SEEK ASSISTANCE') . ': ' . _('销售订单Line不能存入数据库，原因是：');
			$DbgMsg = _('The following SQL to insert the request header record was used');
			$Result = DB_query($LineSQL,$db,$ErrMsg,$DbgMsg,true);
                        $sumamount=$sumamount+$LineItems->amount;
                        
		}
                $HeaderSQL="INSERT INTO so_headers_all ( so_num, 
							                             customer_id,
                                                         status,
                                                         amount,
                                                         note,
                                                         need_date,
														 so_quote,
                                                         create_date,
                                                         last_update_date,
                                                         created_by,
                                                         last_update_by)
						VALUES(
                                                        '" . $_SESSION['Contract'.$identifier]->so_num . "', 
                                                          '" . $_SESSION['Contract'.$identifier]->customer_id  . "',
                                                           'INPROCESS',
                                                                                         '".$sumamount."',
                                                                                         '" . $_SESSION['Contract'.$identifier]->description . "',
                                                                                             '" . $need_date . "',
																							  'SO'  ,
											$create_date,
                                                                                        $create_date,
											'" . $_SESSION['UserID'] . "','" . $_SESSION['UserID'] . "')";
		$ErrMsg =_('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('销售订单不能存入数据库，原因是：');//'" . FormatDateForSQL($_SESSION['Contract'.$identifier]->DispatchDate) . "',
		$DbgMsg = _('The following SQL to insert the request header record was used');
		$Result = DB_query($HeaderSQL,$db,$ErrMsg,$DbgMsg,true);

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
            unset($_SESSION['Contract'.$identifier]);
//            echo '<meta http-equiv="refresh" content="1; url=' . $RootPath . '/index.php" />';
			echo '<br />';
            prnMsg( _('销售订单建立成功！销售订单号为：'.$OrderNum), 'success');
//            echo '<br /><div class="centre"><a href="'.$RootPath.'/AddPurchaseOrder.php">' . _('建立新的销售订单') . '</a></div>';
	}
	include('includes/footer.inc');
	exit;
}


echo '	<div class="centre">
		<a href="'.$RootPath.'/AddPurchaseOrder.php">返回选择客户</a>
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
		</tr>UOM
		<tr>
			<td>' . _('Quantity Requested') . '</td>
			<td><input type="text" class="number" name="Quantity" value="' . $_SESSION['Contract'.$identifier]->LineItems[$_GET['Edit']]->Quantity. '" /></td>
		</tr>
                <tr>
			<td>' . _('单价') . '</td>
			<td><input type="text" class="number" name="ItemCost" value="' . $_SESSION['Contract'.$identifier]->LineItems[$_GET['Edit']]->ItemCost. '" /></td>
		</tr>';
	echo '<input type="hidden" name="LineNumber" value="' . $_SESSION['Contract'.$identifier]->LineItems[$_GET['Edit']]->LineNumber . '" />';
	echo '</table>
		<br />';
	echo '<div class="centre">
			<input type="hidden" name="flag" value = "2" />
			<input type="submit" name="Edit" value="' . _('Update Line') . '" />
		</div>
        </div>
		</form>';
	include('includes/footer.inc');
	exit;
}

if (!isset($_POST['flag'])) {
	$_SESSION['Flag'.$identifier] = 1;
}else{
	$_SESSION['Flag'.$identifier] = $_POST['flag'];
}

if ($_SESSION['Flag'.$identifier] == 1){
echo '<form action="'. htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post"><input type="hidden" name = "identifier" value ="'.$identifier.'">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<table class="selection">';
echo '<tr>
		<th colspan="2" width=550 ><h4>' . _('销售订单总体信息') . '</h4></th>
	</tr>';
echo 	'<tr>
	
                <td><input type="hidden" name="so_num" maxlength="20" size="15" value="' . $_SESSION['Contract'.$identifier]->so_num . '" /></td>
	</tr>';        
echo	'<tr>
		<td>' . _('报价员') . ':</td>'
        . '<td>' . $_SESSION['UserID'] . '</td>';

// any internal department allowed
//$sql = "SELECT salesmanname FROM salesman ORDER by salesmancode";
//
//$result=DB_query($sql, $db);
//echo '<td><select name="SalesMan">';
//while( $Salesmanrow = DB_fetch_array($result) ) {
//	 if (isset($_SESSION['Contract'.$identifier]->sales_man) AND $_SESSION['Contract'.$identifier]->sales_man==$Salesmanrow['salesmanname']){
//		echo '<option selected="selected" value="' . $Salesmanrow['salesmanname'] . '">' . $Salesmanrow['salesmanname'] . '</option>';
//	 } else {
//		echo '<option value="' . $Salesmanrow['salesmanname'] . '">' . $Salesmanrow['salesmanname']  . '</option>';
//	 }
//}
//
//echo '</select></td>
//	</tr>';
echo '<tr>'
        . '<td>客户代码：</td>'
        . '<td>'.$_SESSION['Contract'.$identifier]->customer_code.'</td>'
   . '</tr>';
echo '<tr>'
        . '<td>客户名称：</td>'
        . '<td>'.$_SESSION['Contract'.$identifier]->customer_name.'</td>'
   . '</tr>';
//echo	'<tr>
//		<td>' . _('销售订单种类') . ':</td>';
//echo '<td><select name="CompactType">';
//
// if ($_SESSION['Contract'.$identifier]->compact_type=='YEAR'){
//    echo '<option selected="selected" value="YEAR">包年</option>';
//    echo '<option value="STANDARD">普通</option>';
// } else {
//    echo '<option selected="selected" value="STANDARD">普通</option>';
//    echo '<option value="YEAR">包年</option>';
// }

//echo '</select></td>
//	</tr>';
//echo '  <tr>
//              <td>' . _('生效日期'). ':</td>
//              <td><input type="text" onfocus="WdatePicker()" alt="'.$_SESSION['DefaultDateFormat'].'" name="EffectiveDate" required="required" maxlength="10" size=“11” value="'.$_SESSION['Contract'.$identifier]->effective_date.'"/></td>
//        </tr>';
//'.$_SESSION['Contract'.$identifier]->disable_date.
echo '  <tr>
              <td>' . _('需求日期'). ':</td>
              <td><input type="text" onfocus="WdatePicker()" alt="'.$_SESSION['DefaultDateFormat'].'" name="NeedDate" required="required" maxlength="10" size="11" value="'.$_SESSION['Contract'.$identifier]->NeedDate.'"/></td>
        </tr>';
echo '  <tr>
              <td>' . _('备注'). ':</td>
              <td><textarea  name="Description" cols="60" rows="3" value="'.$_SESSION['Contract'.$identifier]->description.'">' . stripslashes($_SESSION['Contract'.$identifier]->description) . '</textarea></td>
        </tr>';
echo '</table>';

echo '<div class="centre">
		<input type="hidden" name="flag" value = "2" />
		<input type="submit" name="Update" value="' . _('更新销售订单头信息并添加行') . '" />
	</div>
    </div>
	</form>';
}
if (!isset($_SESSION['Contract'.$identifier]->so_num) or $_SESSION['Flag'.$identifier] == 1) {
	include('includes/footer.inc');
	exit;
}

 
echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post"><input type="hidden" name = "identifier" value ="'.$identifier.'">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<br /><div class="centre"><h4>' . _('查找料号') . '</h4></div>';

// echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('查找物品'). '</p>';
echo '<table cellpadding="3" class="selection">';
echo '<tr><td colspan="2">' . _('请输入部分料号名称') . ':</td><td>';
echo '<input type="text" name="Item_no" value="' . $_POST['Item_no'] . '" size="20" maxlength="25" /></td>';
echo '<td><b>' . _('OR') . '</b></td><td>' . _('输入部分料号描述') . ':</td>
	<td>';
echo '<input type="text" name="Item_desc" value="' . $_POST['Item_desc'] . '" size="20" maxlength="25" /></td>';
echo '</tr>';
echo '</table>';
echo '
	<br />
	<div class="centre">
		<input type="hidden" name="flag" value = "2" />
		<input type="submit" name="Search" value="' . _('查找选择料号') . '" />
	</div>
	<br />
	</div>
	</form>';
//or isset($_POST['Update']) 
if (isset($_POST['Search']) or isset($_POST['Next']) or isset($_POST['Prev']) or isset($_POST['Go'])){
        $SQL = "SELECT
                        sf_item_no.item_id,
                        sf_item_no.item_no,
                        sf_item_no.item_desc,
                        sf_item_no.units ,
                        sf_item_no.po_price ,
                        sf_item_no.min_order,
                        sf_item_no.disable_flag
                FROM   sf_item_no where 1=1  ";
              
//        }
        if (isset($_POST['Item_no']) and $_POST['Item_no'] != ''){
            $SQL = $SQL." and Item_no ".LIKE." '%".$_POST['Item_no']."%' ";
        }
        if (isset($_POST['Item_desc']) and $_POST['Item_desc'] != ''){
            $SQL = $SQL." and item_desc  ".LIKE." '%".$_POST['Item_desc']."%' ";
        }
//        echo  "order by sf_item_no.item_id";
        
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
    $_SESSION['DisplayRecordsMax'] = 15;
    $_SESSION['DefaultDisplayRecordsMax'] = 15;
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
$_SESSION['DisplayRecordsMax']=15;
if (isset($SearchResult)) {
	$j = 1;
	echo '<br />
		<div class="page_help_text">' . _('通过输入需求数量来选择物料，当完成后点击添加至销售订单。') . '</div>
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
		<input type="hidden" name="flag" value = "2" />
		<table>
		<tr>
			<td>
				<input type="hidden" name="Previous" value="'.($Offset-1).'" />
				<input tabindex="'.($j+8).'" type="submit" name="Prev" value="'._('Prev').'" /></td>
				<td style="text-align:center" colspan="6">
				<input type="hidden" name="order_items" value="1" />
				<input tabindex="'.($j+9).'" type="submit" value="'._('添加至销售订单').'" /></td>';
		if ($Offset >= 0){
        echo '
                            <td>
                                    <input type="hidden" name="NextList" value="'.($Offset+1).'" />
                                    <input tabindex="'.($j+10).'" type="submit" name="Next" value="'._('Next').'" /></td>';
        }
		echo '
			</tr>
			<tr>
				<th class="ascending">' . _('料号') . '</th>
				<th class="ascending">' . _('Description') . '</th>
				<th>' . _('Units') . '</th>
				<th class="ascending">' . _('Quantity') . '</th>
                                <th class="ascending">' . _('单价') . '</th>
			</tr>';
	$ImageSource = _('No Image');

	$k=0; //row colour counter
	$i=0;
	while ($myrow=DB_fetch_array($SearchResult)) {
		

		$QOHSQL = "SELECT sum(locstock.quantity) AS qoh
							   FROM locstock
							   WHERE locstock.stockid='" .$myrow['stockid'] . "' AND
							   loccode = '" . $_SESSION['Contract'.$identifier]->Location . "'";
		$QOHResult =  DB_query($QOHSQL,$db);
		$QOHRow = DB_fetch_array($QOHResult);
		$QOH = $QOHRow['qoh'];

		// Find the quantity on outstanding sales orders
//		$sql = "SELECT SUM(salesorderdetails.quantity-salesorderdetails.qtyinvoiced) AS dem
//				 FROM salesorderdetails INNER JOIN salesorders
//				 ON salesorders.orderno = salesorderdetails.orderno
//				 WHERE salesorders.fromstkloc='" . $_SESSION['Contract'.$identifier]->Location . "'
//				 AND salesorderdetails.completed=0
//				 AND salesorders.quotation=0
//				 AND salesorderdetails.stkcode='" . $myrow['stockid'] . "'";
                $sql="";
		$ErrMsg = _('The demand for this product from') . ' ' . $_SESSION['Contract'.$identifier]->Location . ' ' . _('cannot be retrieved because');
//		$DemandResult = DB_query($sql,$db,$ErrMsg);
//
//		$DemandRow = DB_fetch_row($DemandResult);
//		if ($DemandRow[0] != null){
//			$DemandQty =  $DemandRow[0];
//		} else {
//		  $DemandQty = 0;
//		}

		// Find the quantity on purchase orders
//		$sql = "SELECT SUM(purchorderdetails.quantityord-purchorderdetails.quantityrecd)*purchorderdetails.conversionfactor AS dem
//				 FROM purchorderdetails LEFT JOIN purchorders
//					ON purchorderdetails.orderno=purchorders.orderno
//				 WHERE purchorderdetails.completed=0
//				 AND purchorders.status<>'Cancelled'
//				 AND purchorders.status<>'Rejected'
//				 AND purchorders.status<>'Completed'
//				AND purchorderdetails.itemcode='" . $myrow['stockid'] . "'";
                $sql="";

		$ErrMsg = _('The order details for this product cannot be retrieved because');
//		$PurchResult = DB_query($sql,$db,$ErrMsg);

//		$PurchRow = DB_fetch_row($PurchResult);
//		if ($PurchRow[0]!=null){
//			$PurchQty =  $PurchRow[0];
//		} else {
//			$PurchQty = 0;
//		}

		// Find the quantity on works orders
//		$sql = "SELECT SUM(woitems.qtyreqd - woitems.qtyrecd) AS dedm
//			   FROM woitems
//			   WHERE stockid='" . $myrow['stockid'] ."'";
                $sql="";
		$ErrMsg = _('The order details for this product cannot be retrieved because');
//		$WoResult = DB_query($sql,$db,$ErrMsg);

//		$WoRow = DB_fetch_row($WoResult);
//		if ($WoRow[0]!=null){
//			$WoQty =  $WoRow[0];
//		} else {
//			$WoQty = 0;
//		}

		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}
		$OnOrder = $PurchQty + $WoQty;
		$Available = $QOH - $DemandQty + $OnOrder;
		echo '<td>' . $myrow['item_no'] . '</td>
				<td class="number">' . $myrow['item_desc'] . '</td>
				<td class="number">' . $myrow['units'] . '</td>
				<td><input class="number" ' . ($i==0 ? 'autofocus="autofocus"':'') . ' tabindex="'.($j+7).'" type="text" size="6" name="Quantity'.$i.'" value="0" />
                                <td class="number">' . $myrow['po_price'] . '</td>
                             
				<input type="hidden" name="Item_id'.$i.'" value="'.$myrow['item_id'].'" />
				</td>
			</tr>';
//		echo '<input type="hidden" name="DecimalPlaces'.$i.'" value="' . $myrow['decimalplaces'] . '" />';
              echo '<input type="hidden" name="item_no'.$i.'" value="' . $myrow['item_no'] . '" />';
		echo '<input type="hidden" name="item_desc'.$i.'" value="' . $myrow['item_desc'] . '" />';
		echo '<input type="hidden" name="Units'.$i.'" value="' . $myrow['units'] . '" />';
                echo '<input type="hidden" name="po_price'.$i.'" value="' . $myrow['po_price'] . '" />';
		$i++;
	}
#end of while loop
	echo '<tr>
			<td><input type="hidden" name="Previous" value="'.($Offset-1).'" />
				<input tabindex="'.($j+7).'" type="submit" name="Prev" value="'._('Prev').'" /></td>
			<td style="text-align:center" colspan="6"><input type="hidden" name="order_items" value="1" />
				<input tabindex="'.($j+8).'" type="submit" value="'._('添加至销售订单').'" /></td>';
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

$i = 0; //Line Item Array pointer
echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post"><input type="hidden" name = "identifier" value ="'.$identifier.'">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<p class="page_title_text">' . _('销售订单行维护'). '</p>';
// echo '<br /><div class="centre"><h4>' . _('销售订单Line维护') . '</h4></div>';
echo '  <table class="selection">
	<tr>
		<th>' .  _('Line Number') . '</th>
		<th class="ascending">' .  _('Item Code') . '</th>
		<th class="ascending">' .  _('Item Description'). '</th>
              
		<th class="ascending">' .  _('签订数量'). '</th>
		<th>' .  _('UOM'). '</th>
                <th>' .  _('单价'). '</th>  
                     <th>' .  _('金额'). '</th>  
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
                      
			<td class="number">' . locale_number_format($LineItems->Quantity) . '</td>
			<td>' . $LineItems->UOM . '</td>
                        <td class="number">' . locale_number_format($LineItems->ItemCost) . '</td>
                            <td>' . locale_number_format($LineItems->amount) . '</td>
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
echo '	<div class="centre">
		<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
		<input type="hidden" name="flag" value = "1" />
		<input type="submit" name="SubmitFlag" value="' . _('返回销售订单头信息') . '" />
	</div>';
echo '</form>';

//*********************************************************************************************************
include('includes/footer.inc');
?>