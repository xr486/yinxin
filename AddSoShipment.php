<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include('includes/DefineAddSoShipmentClass.php');
 $_SESSION['DefaultDisplayRecordsMax'] = 10;
include('includes/session.inc');
$Title = _('销售订单出货');
$ViewTopic = '销售订单出货';
$BookMark = '销售订单出货';
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
	$_SESSION['Contract'.$identifier] = new ShipmentRequest();
        if(isset($_GET['customer_id'])){
            if(!isset($_SESSION['Contract'.$identifier]->Customer_code) or $_SESSION['Contract'.$identifier]->Customer_code ==''){
                $CustomerID = $_GET['customer_id']; 
				$sql = "select customer_id,customer_code,customer_name from customers where customer_id ='".$CustomerID."' ";
				
                $CustResult = DB_query($sql, $db);
                while ($myrow=  DB_fetch_array($CustResult)){
					$_SESSION['Contract'.$identifier]->Customer_id = $myrow['customer_id'];
                    $_SESSION['Contract'.$identifier]->Customer_code = $myrow['customer_code'];
                    $_SESSION['Contract'.$identifier]->Customer_name = $myrow['customer_name'];


		


                }
            }
        }
}
 
if (isset($_POST['Update'])) {
	$InputError=0;
	/*
   if ($_POST['Delivery_num']=='') {
		prnMsg( _('你必须输入一个出货单号'), 'error');
		$InputError=1;
	}*/
	/*if ($_POST['ShipmentAmount']=='') {
		prnMsg( _('你必须输入收款总金额'), 'error');
		$InputError=1;
	}*/
	/*
	if ($_POST['Tracking_Number']=='') {
		prnMsg( _('你必须输入物流单号'), 'error');
		$InputError=1;
	}*/
        if ($_POST['Trackingcompany']=='') {
		prnMsg( _('你必须输入物流公司'), 'error');
		$InputError=1;
	}
       
	if ($InputError==0) {
		$_SESSION['Contract'.$identifier]->Delivery_num=$_POST['Delivery_num'];
		$_SESSION['Contract'.$identifier]->ShipmentAmount=$_POST['ShipmentAmount'];
		$_SESSION['Contract'.$identifier]->Tracking_Number=$_POST['Tracking_Number'];
		$_SESSION['Contract'.$identifier]->Trackingcompany=$_POST['Trackingcompany'];
		$_SESSION['Contract'.$identifier]->ShipInvoiceNum=$_POST['ShipInvoiceNum'];
        $_SESSION['Contract'.$identifier]->Narrative=$_POST['Narrative'];
	}
}

if (isset($_POST['Edit'])) {
	$_SESSION['Contract'.$identifier]->LineItems[$_POST['LineNumber']]->ShipmentAmount=$_POST['ShipmentAmount'];
	$_SESSION['Contract'.$identifier]->LineItems[$_POST['LineNumber']]->LineNarrative=$_POST['LineNarrative'];
}

if (isset($_GET['Delete'])) {
	unset($_SESSION['Contract'.$identifier]->LineItems[$_GET['Delete']]);
	echo '<br />';
	prnMsg( _('The line was successfully deleted'), 'success');
	echo '<br />';
}
 
foreach ($_POST as $key => $value) {
  
	if (mb_strstr($key,'UpdateLine')) { 
		 
	    $Index=mb_substr($key, 10);  
		if (mb_substr($key,0,10)=='UpdateLine') {
			$order_line_id=mb_substr($key,10);
			$j = $_POST[$key]; 
			$Amount = $_POST['paymentamount' . $j];	
			$so_num = $_POST['so_num' . $j];	
			$DeliveryQuantity=$_POST['DeliveryQuantity'.$j]; 
			$Quantity_shiped=$_POST['ShipQuantity'.$j]; 
			$Order_Quantity=$_POST['Order_Quantity'.$j]; 
			$WaitQuantity=$_POST['WaitQuantity'.$j];
			$Stockid=$_POST['Stockid'.$j];
			$Subinventory_Code=$_POST['Subinventory_Code'.$j];
			$Onhand_Quantity=$_POST['Onhand_Quantity'.$j];
			$Line=$_POST['Line'.$j];	 	 
			$LineNarrative=$_POST['LineNarrative'.$j];

			if ($Onhand_Quantity<$DeliveryQuantity) {
		   prnMsg( _($Stockid.'出货数量 '.$DeliveryQuantity.'不可以大于库存数量'.$Onhand_Quantity), 'error');
		   $InputError=1;
	        }

			if ($WaitQuantity<$DeliveryQuantity) {
		   prnMsg( _($Stockid.'出货数量 '.$DeliveryQuantity.'不可以大于待出货数量'.$WaitQuantity), 'error');
		   $InputError=1;
	        }


             if ($InputError==0) {
			    $_SESSION['Contract'.$identifier]->AddLine($so_num, $Line,$DeliveryQuantity, $Order_Quantity,$Quantity_shiped,$WaitQuantity,$Stockid,$Subinventory_Code,$Onhand_Quantity,$LineNarrative); 
             }
		 }
	}
}


if (isset($_POST['Submit'])) {
	DB_Txn_Begin($db);
	$InputError=0;
	 
   /*
	if ($_SESSION['Contract'.$identifier]->Tracking_Number=='') {
		prnMsg( _('你必须物流单号'), 'error');
                echo '<br /><div class="centre"><a a href="'. htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?identifier='.$identifier.'">' . _('返回收款单') . '</a></div>';
		$InputError=1;
	}

        if ($_SESSION['Contract'.$identifier]->Trackingcompany=='') {
		prnMsg( _('你必须输入物流公司'), 'error');
                echo '<br /><div class="centre"><a a href="'. htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?identifier='.$identifier.'">' . _('返回收款单') . '</a></div>';
		$InputError=1;
	}
        */
   $SumAmount=0;
   foreach ($_SESSION['Contract'.$identifier]->LineItems as $LineItems) {
			$SumAmount=$SumAmount+ $LineItems->ShipmentAmount; 
	 }


   $_SESSION['Contract'.$identifier]->ShipmentAmount=$SumAmount;
  
 
	if ($InputError==0) {


   $date=date('Ymd');
		$sql = "select 	(
		CASE WHEN substr(max(Delivery_num) ,-2,1) = 0 THEN
			RIGHT (
				'100' + (
					max(substr(Delivery_num ,- 1)) + 1
				),
				2
			)
		ELSE
			substr(max(Delivery_num),-2,2) + 1
		END
        ) number from so_delivery_headers_all 
		where substr(Delivery_num,-10,8) = '".$date."'";
		$result=DB_query($sql, $db);
		$rownum = DB_num_rows($result);
		while ($v=DB_fetch_array($result)) {
			if ($v['number']==null) {
				$_SESSION['Contract'.$identifier]->Delivery_num = 'DE'.$date.'01';
			}else{
				$_SESSION['Contract'.$identifier]->Delivery_num = 'DE'.$date.$v['number'];
			}
		}
        $v_date = strtotime(Date('Y-m-d H:i:s')); 
		$HeaderSQL="INSERT INTO so_delivery_headers_all (Delivery_num, 
											tracking_number,
											customer_id, 
											narrative,
                                            trackingcompany,
										    invoicenum,
                                            creation_date,
                                            last_update_date,
                                            created_by,
                                            last_updated_by)
										VALUES(
											 '" . $_SESSION['Contract'.$identifier]->Delivery_num . "', 
											 '" . $_SESSION['Contract'.$identifier]->Tracking_Number . "',
											 '" . $_SESSION['Contract'.$identifier]->Customer_id . "', 
                                             '" . $_SESSION['Contract'.$identifier]->Narrative . "',
                                             '" . $_SESSION['Contract'.$identifier]->Trackingcompany . "', 
											 '" . $_SESSION['Contract'.$identifier]->ShipInvoiceNum . "', 
										     '" .$v_date  . "',
                                             '" .$v_date . "',
											 '" . $_SESSION['UserID'] . "',
											 '" . $_SESSION['UserID'] . "')";
		$ErrMsg =_('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('出货单不能存入数据库，原因是：');//'" . FormatDateForSQL($_SESSION['Contract'.$identifier]->DispatchDate) . "',
		$DbgMsg = _('The following SQL to insert the request header record was used');
		$Result = DB_query($HeaderSQL,$db,$ErrMsg,$DbgMsg,true);

		foreach ($_SESSION['Contract'.$identifier]->LineItems as $LineItems) {
			$v_date = strtotime(Date('Y-m-d H:i:s'));
			$LineSQL="INSERT INTO so_delivery_all (deliveryline,
													delivery_num, 
													customer_id,											
													delivery_quantity, 
													linenarrative,
													so_order_number,
													so_line_no, 
													delivery_date,
													stockid,
													Subinventory_Code,
													creation_date,
                                                    last_update_date,
                                                    created_by,
                                                    last_updated_by)
												VALUES(
													'".$LineItems->LineNumber."',
													'".$_SESSION['Contract'.$identifier]->Delivery_num."',
													'".$_SESSION['Contract'.$identifier]->Customer_id."', 
													'".$LineItems->DeliveryQuantity."',  
													'".$LineItems->LineNarrative."',
													'".$LineItems->so_num."',
													'".$LineItems->Line."', 
													'".$v_date . "',
													'".$LineItems->Stockid."',
													'".$LineItems->Subinventory_Code."',
										            '".$v_date . "',
                                                    '".$v_date . "',
										        	'".$_SESSION['UserID'] . "',
										        	'".$_SESSION['UserID'] . "')";
				
        
			$ErrMsg =_('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('收款单行不能存入数据库，原因是：');
			$DbgMsg = _('The following SQL to insert the request header record was used');
			$Result = DB_query($LineSQL,$db,$ErrMsg,$DbgMsg,true);

           //插入交易表
		  $TXNSQL=" insert into  inv_transactions_all ( transaction_type ,quantity,item ,subinventory_from ,
		  creation_date ,create_by,deliveryline, delivery_num,
		  remark ) VALUES( 'SHIPPING','".$LineItems->DeliveryQuantity."','".$LineItems->Stockid."','".$LineItems->Subinventory_Code."',
		  '".$v_date . "','".$_SESSION['UserID'] . "','".$LineItems->LineNumber."',
													'".$_SESSION['Contract'.$identifier]->Delivery_num."',
													'".$LineItems->LineNarrative."')";

          $ErrMsg =_('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('出货不能插入交易，原因是：');
		  $DbgMsg = _('The following SQL to insert inv_transactions_all is error');
		  $Result = DB_query($TXNSQL,$db,$ErrMsg,$DbgMsg,true);


		   //插入交易表 end
            
		   $sql = "SELECT  quantity_shiped  
			   FROM  so_lines_all
			    WHERE  so_num ='".$LineItems->so_num."' 
				and line='".$LineItems->Line."'  ";
		   $ErrMsg = _('The order details for this product cannot be retrieved because');
		   $WoResult = DB_query($sql,$db,$ErrMsg);
	       $WoRow = DB_fetch_row($WoResult);
		   if ($WoRow[0]!=null){
			$OldAmount =  $WoRow[0];
		   } else {
		 	$OldAmount = 0;
		   }
              
            $SumAmount=$OldAmount+$LineItems->DeliveryQuantity;			 
			$v_date = strtotime(Date('Y-m-d H:i:s'));
		 	 $UPDATESQL="UPDATE  so_lines_all
							set quantity_shiped='".$SumAmount."',
							       last_update_date='".$v_date . "',
								   last_updated_by='".$_SESSION['UserID'] . "'
                        WHERE    so_num ='".$LineItems->so_num."' 
				                and line='".$LineItems->Line."' "; 

			$ErrMsg =_('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('出货数量不能更新数据库，原因是：');
			$DbgMsg = _('The following SQL to insert the request header record was used');
			$Result = DB_query($UPDATESQL,$db,$ErrMsg,$DbgMsg,true);
			
          $Onhand_Quantity=0;
			$sql = "SELECT  sum(quantity)
			   FROM  inv_onhand_quantity_all
			    WHERE  Stockid ='".$LineItems->Stockid."' 
				and Subinventory_Code='".$LineItems->Subinventory_Code."'  ";
		   $ErrMsg = _('The order details for this product cannot be retrieved because');
		   $WoResult = DB_query($sql,$db,$ErrMsg);
	       $WoRow = DB_fetch_row($WoResult);
		   if ($WoRow[0]!=null){
			$Onhand_Quantity =  $WoRow[0];
		   } else {
		 	$Onhand_Quantity = 0;
		   }

		   if  ($Onhand_Quantity < $LineItems->DeliveryQuantity) {
		   $ErrMsg = _('库存量小于出货量'); 
		   $DbgMsg = _('库存量小于出货量');
		   } else {
	  
	    $temp=$LineItems->DeliveryQuantity; 
       $sqlsubcode="select id,Stockid,quantity from inv_onhand_quantity_all where Stockid='".$LineItems->Stockid."'
	      and Subinventory_Code='".$LineItems->Subinventory_Code."' ";
       $result_subcode = DB_query($sqlsubcode, $db); 
 
       while ($v = DB_fetch_array($result_subcode)) {
           if($temp>0){
            if ($v['quantity'] <=$temp) {
                $UpdateSubCode="delete from  inv_onhand_quantity_all where id=".$v['id']."";
 
                $result_updatesubcode = DB_query($UpdateSubCode, $db); 
                unset($UpdateSubCode);
                $temp=$temp-$v['quantity'];
            } else {
                $UpdateSubCode1="Update inv_onhand_quantity_all set quantity=quantity-".$temp.",last_update_date='".$v_date . "',
								   last_updated_by='".$_SESSION['UserID'] . "' where id=".$v['id']."";
 
                $result_updatesubcode1 = DB_query($UpdateSubCode1, $db); 
                $temp=0;
            }
        }
    } //end while 
		   } //end 扣减库存
           


       }


            DB_Txn_Commit($db);
            prnMsg( _('出货完成出货单号:'.$_SESSION['Contract'.$identifier]->Delivery_num), 'success');
            echo '<br /><div class="centre"><a href="' . $RootPath . '/SearchShipOrder.php">' . _('选择其他订单出货') . '</a></div>';
            unset($_SESSION['Contract'.$identifier]);
	}
	include('includes/footer.inc');
	exit;
}
echo '	<div class="centre">
		<a href="' . $RootPath . '/SearchShipOrder.php">返回重新选择客户</a>
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
			<th colspan="2"><h4>' . _('修改收款行信息') . '</h4></th>
		</tr>';
	echo '<tr>
			<td>' . _('行') . '</td>
			<td>' . $_SESSION['Contract'.$identifier]->LineItems[$_GET['Edit']]->LineNumber . '</td>
		</tr> 
		 

		<tr>
			<td>' . _('订单号码') . '</td>
			<td>' . $_SESSION['Contract'.$identifier]->LineItems[$_GET['Edit']]->so_num . '</td>
		</tr>

		<tr>
			<td>' . _('订单行') . '</td>
			<td>' . $_SESSION['Contract'.$identifier]->LineItems[$_GET['Edit']]->Line . '</td>
		</tr> 
       
	   <tr>
			<td>' . _('订单数量') . '</td>
			<td>' . $_SESSION['Contract'.$identifier]->LineItems[$_GET['Edit']]->Order_Quantity . '</td>
		</tr>

		<tr>
			<td>' . _('已出货数量') . '</td>
			<td>' . $_SESSION['Contract'.$identifier]->LineItems[$_GET['Edit']]->Quantity_shiped . '</td>
		</tr>
		<tr>
			<td>' . _('待出货数量') . '</td>
			<td>' . $_SESSION['Contract'.$identifier]->LineItems[$_GET['Edit']]->WaitQuantity . '</td>
		</tr>
        
		<tr>
			<td>' . _('料号') . '</td>
			<td>' . $_SESSION['Contract'.$identifier]->LineItems[$_GET['Edit']]->Stockid . '</td>
		</tr>

		<tr>
			<td>' . _('仓库') . '</td>
			<td>' . $_SESSION['Contract'.$identifier]->LineItems[$_GET['Edit']]->Subinventory_Code . '</td>
		</tr>

		<tr>
			<td>' . _('库存数量') . '</td>
			<td>' . $_SESSION['Contract'.$identifier]->LineItems[$_GET['Edit']]->Onhand_Quantity . '</td>
		</tr>

		<tr>
			<td>' . _('本次出货数量') . '</td>
			<td><input type="text" class="number" name="DeliveryQuantity" value="' .  $_SESSION['Contract'.$identifier]->LineItems[$_GET['Edit']]->DeliveryQuantity  . '" /></td>
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
			<input type="submit" name="Edit" value="' . _('更新收款单行') . '" />
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
		<th colspan="2">' . _('出货订单头信息') . '</th>
	</tr>';
 

 /*	
echo 	'<tr>
		<td>' . _('出货单号') . ':</td>
                <td><input type="text" name="Delivery_num" maxlength="20" size="20" value="' . $_SESSION['Contract'.$identifier]->Delivery_num . '" /></td>
	</tr>';

echo 	'<tr>
		<td>' . _('出货总金额') . ':</td>
                <td><input type="text" class="number" name="ShipmentAmount" maxlength="20" size="20" value="' . $_SESSION['Contract'.$identifier]->ShipmentAmount . '" /></td>
	</tr>';   
 */

 echo '<tr>'
        . '<td>客户代码：</td>'
        . '<td>'.$_SESSION['Contract'.$identifier]->Customer_code.'</td>'
   . '</tr>';
echo '<tr>'
        . '<td>客户名称：</td>'
        . '<td>'.$_SESSION['Contract'.$identifier]->Customer_name.'</td>'
   . '</tr>';

echo 	'<tr>
		<td>' . _('发票号码') . ':</td>
                <td><input type="text"   name="ShipInvoiceNum" maxlength="30" size="30" value="' . $_SESSION['Contract'.$identifier]->ShipInvoiceNum . '" /></td>
	</tr>'; 
echo '</select></td>
	</tr>';

 echo 	'<tr>
		<td>' . _('快递物流公司') . ':</td>
                <td><input type="text"   name="Trackingcompany" maxlength="30" size="30" value="' . $_SESSION['Contract'.$identifier]->Trackingcompany . '" /></td>
	</tr>';   

echo 	'<tr>
		<td>' . _('快递单号') . ':</td>
                <td><input type="text"   name="Tracking_Number" maxlength="30" size="30" value="' . $_SESSION['Contract'.$identifier]->Tracking_Number . '" /></td>
	</tr>';   


  


echo '  <tr>
              <td>' . _('备注'). ':</td>
              <td><textarea  name="Narrative" cols="40" rows="1">' . stripslashes($_SESSION['Contract'.$identifier]->Narrative) . '</textarea></td>
        </tr>';
echo '</table>';
 
echo '<div class="centre">
	<input type="submit" name="Update" value="' . _('保存订单头信息并添加行') . '" />
	</div>
    </div>
	</form>';
 
if (!isset($_SESSION['Contract'.$identifier]->Trackingcompany)) {
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
		<th colspan="10"> ' . _('出货单行维护') . ' </th>
	</tr>
	<tr>
		<th>' .  _('行') . '</th>  
		<th class="ascending">' . _('订单号码') . '</th>
		<th class="ascending">' . _('订单行') . '</th>
		<th class="ascending">' . _('订单数量') . '</th> 
		<th class="ascending">' . _('已出货数量') . '</th>			
		 <th class="ascending">' . _('待出货数量') . '</th>
		 <th class="ascending">' . _('料号') . '</th>
		 <th class="ascending">' . _('出货仓库') . '</th>
		 <th class="ascending">' . _('库存数量') . '</th>
		 <th class="ascending">' . _('本次出货数量') . '</th> 				
		 <th class="ascending">' . _('行备注') . '</th> 
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
			<td>' . $LineItems->so_num . '</td>
			<td>' . $LineItems->Line . '</td> 
			<td>' . $LineItems->Order_Quantity  . '</td>	 
			<td>' . $LineItems->Quantity_shiped . '</td>						
			<td>' . $LineItems->WaitQuantity . '</td> 
			<td>' . $LineItems->Stockid . '</td> 
			<td>' . $LineItems->Subinventory_Code . '</td> 		
			<td>' . $LineItems->Onhand_Quantity . '</td> 		
			<td>' . $LineItems->DeliveryQuantity . '</td> 
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

echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('查找待出货订单'). '</p>';
echo '<table cellpadding="3"  >';
echo '<tr><td colspan="2">' . _('请输入待出货订单号码') . ':</td><td>';
echo '<input type="text" name="so_num" value="' . $_POST['so_num'] . '" size="20" maxlength="25" /></td>';
echo '</tr>';
echo '</table>';
echo ' 
	<div class="centre">
		<input type="submit" name="Search" value="' . _('查找待出货的订单') . '" />
	</div> 
	</div>
	</form>';

//or isset($_POST['Update']) 
if (isset($_POST['Search']) or isset($_POST['Next']) or isset($_POST['Prev']) or isset($_POST['Go'])){
 
        $Customer_id=$_SESSION['Contract'.$identifier]->Customer_id;
        $SQL = "SELECT  a.so_num,b.Line,b.quantity Order_Quantity,b.quantity_cancelled,b.note,b.Quantity_shiped,b.price,b.order_line_id,b.Subinventory_Code,b.Stockid
           FROM  so_headers_all a,
              so_lines_all b 
         where a.so_num=b.so_num 
           and (b.quantity>(b.Quantity_shiped+b.quantity_cancelled) or b.Quantity_shiped is null )  
		   and a.status='APPROVED'
		   and a.so_quote='SO'
            and a.customer_id=".$Customer_id;
//        }
        if (isset($_POST['so_num']) and $_POST['so_num'] != ''){
            $SQL = $SQL." and a.so_num ".LIKE." '%".$_POST['so_num']."%' ";
        }
     
        
        $SQL = $SQL." ORDER BY a.so_num,b.Line";

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
    $ListPageMax = ceil($ListCount / 10);//$_SESSION['DisplayRecordsMax']
    if ($_POST['Previous']==-2) {
        $Offset = $ListPageMax+$_POST['Previous'];
    }
    if (isset($_POST['Go']) and isset($_POST['PageOffset'])){
        $Offset = $_POST['PageOffset'] - 1;
    } 
     $_SESSION['DefaultDisplayRecordsMax']=10;
	$SQL = $SQL . ' LIMIT ' . $_SESSION['DefaultDisplayRecordsMax'] . ' OFFSET ' . ($_SESSION['DefaultDisplayRecordsMax']*$Offset);
	$ErrMsg = _('There is a problem selecting the part records to display because');
	$DbgMsg = _('The SQL used to get the part selection was');
	$SearchResult = DB_query($SQL,$db,$ErrMsg, $DbgMsg);

	if (DB_num_rows($SearchResult)==0 ){
		prnMsg (_('找不到待出货的订单号码 '),'info');
	}
	if (DB_num_rows($SearchResult)<10){//$_SESSION['DisplayRecordsMax']
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
			DB_data_seek($searchresult, ($_POST['PageOffset'] - 1) * 10);//$_SESSION['DisplayRecordsMax']
		}
		while (($myrow = DB_fetch_array($searchresult)) AND ($RowIndex <> 10)) {//$_SESSION['DisplayRecordsMax']
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

			echo '<td><input type="submit" name="Select" value="' . $myrow['Stockid'] . '" /></td>
					<td>' . $myrow['DeliveryQuantity'] . '</td>
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
		<div class="page_help_text">' . _('通过输入需求出货数量来选择出货乎订单，当完成后点击添加至出货单。') . '</div>
		 
		<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post" id="orderform"><input type="hidden" name = "identifier" value ="'.$identifier.'">';
		$ListCount = DB_num_rows($searchResult);
	  
	if ($ListCount > 0) {
		// If the user hit the search button and there is more than one item to show
		$ListPageMax = ceil($ListCount / 10);//$_SESSION['DisplayRecordsMax']
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
				<input tabindex="'.($j+9).'" type="submit" value="'._('添加至出货单行').'" /></td>';
		if ($Offset >= 0){
        echo '
                            <td>
                                    <input type="hidden" name="NextList" value="'.($Offset+1).'" />
                                    <input tabindex="'.($j+10).'" type="submit" name="Next" value="'._('Next').'" /></td>';
        }
 
		echo '
			</tr>
			<tr>
				<th  >' . _('订单号码') . '</th>
		        <th  >' . _('订单行') . '</th> 
		        <th  >' . _('订单数量') . '</th> 
		        <th  >' . _('已出货数量') . '</th>			
				<th  >' . _('待出货数量') . '</th> 
				<th  >' . _('料号') . '</th>
				<th  >' . _('出货仓库') . '</th>
				<th  >' . _('库存量') . '</th>
				<th  >' . _('本次出货数量') . '</th> 
				<th  >' . _('出货单行备注') . '</th> 
				<th  >' . _('选择') . '</th> 
			</tr>';
	$ImageSource = _('No Image');


	$k=0; //row colour counter
	$i=0;
	while ($myrow=DB_fetch_array($SearchResult)) {
		$WaitQuantity=$myrow['Order_Quantity']-$myrow['Quantity_shiped'] -$myrow['quantity_cancelled'] ;
           $sql = "SELECT  sum(quantity)
			   FROM  inv_onhand_quantity_all
			    WHERE  Stockid ='".$myrow['Stockid']."' 
				and Subinventory_Code='".$myrow['Subinventory_Code']."'  ";
		   $ErrMsg = _('The order details for this product cannot be retrieved because');
		   $WoResult = DB_query($sql,$db,$ErrMsg);
	       $WoRow = DB_fetch_row($WoResult);
		   if ($WoRow[0]!=null){
			$Onhand_Quantity =  $WoRow[0];
		   } else {
		 	$Onhand_Quantity = 0;
		   }

		echo   ' <td class="number">' . $myrow['so_num'] . '</td>
				<td class="number">' .$myrow['Line'] . '</td>  
				<td class="number">' .$myrow['Order_Quantity'] . '</td>  
                <td class="number">' . $myrow['Quantity_shiped'] . '</td>
				<td class="number">' .$WaitQuantity. '</td> 
				<td class="number">' . $myrow['Stockid'] . '</td>
				<td class="number">' . $myrow['Subinventory_Code'] . '</td>
				<td class="number">' . $Onhand_Quantity . '</td>
			    <td><input class="number"= ' . ($i==0 ? 'autofocus="autofocus"':'') . ' tabindex="'.($j+7).'" type="text" size="10" name="DeliveryQuantity'.$i.'" value="' . $WaitQuantity . '"  />  </td>
				<td><input type="text" name="LineNarrative'.$i.'" value="' . $myrow['LineNarrative'] . '" /> </td> 
			';

           echo ' <td><input type="checkbox" name="UpdateLine'.$myrow['order_line_id'].'" value="'.$i.'" /> </td> </tr>';
			


	      echo ' <input type="hidden" name="ShipQuantity'.$i.'" value="'.$myrow['Quantity_shiped'].'" />';  
		  echo ' <input type="hidden" name="Order_Quantity'.$i.'" value="'.$myrow['Order_Quantity'].'" />';  
		  echo '<input type="hidden" name="so_num'.$i.'" value="' . $myrow['so_num'] . '" />';  
		  echo '<input type="hidden" name="Line'.$i.'" value="' . $myrow['Line'] . '" />'; 
		  echo '<input type="hidden" name="WaitQuantity'.$i.'" value="' . $WaitQuantity . '" />'; 
		  echo '<input type="hidden" name="Stockid'.$i.'" value="' . $myrow['Stockid'] . '" />'; 
		  echo '<input type="hidden" name="Subinventory_Code'.$i.'" value="' . $myrow['Subinventory_Code'] . '" />'; 
		   echo '<input type="hidden" name="Onhand_Quantity'.$i.'" value="' . $Onhand_Quantity . '" />'; 
		$i++;
	}

 



#end of while loop
	echo '<tr>
			<td><input type="hidden" name="Previous" value="'.($Offset-1).'" />
				<input tabindex="'.($j+7).'" type="submit" name="Prev" value="'._('Prev').'" /></td>
			<td style="text-align:center" colspan="6"><input type="hidden" name="order_items" value="1" />
				<input tabindex="'.($j+8).'" type="submit" value="'._('添加至出货单行').'" /></td>';
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
