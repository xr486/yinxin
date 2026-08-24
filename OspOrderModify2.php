<?php
if(isset($_GET['pdata'])){
	
	 include_once("connect.php"); 
	 $sql = "select tax_mount from tax_set a where tax_name = '".$_GET['pdata']."'";
	 $result_num = mysql_query($sql, $db);
	 $res = mysql_fetch_assoc($result_num);
	 echo $res['tax_mount'];
	 return ;
 } 
 if(isset($_GET['data3'])){
	 //$sql = "select * from customers where customer_name = '".$_GET['data']."'";
	 include_once("connect.php"); 
	 $sql = "select * from sf_item_no_v where item_category1<>'成品料号' and item_no = '".$_GET['data3']."'";
	 $result_num = mysql_query($sql, $db);
	 $res_item = mysql_fetch_assoc($result_num);
	 echo $res_item['item_name'].':'.$res_item['item_desc'].':'.$res_item['units'].':'.$res_item['last_price'];
	 return ;
 }

 
include ('includes/DefinePOUpdateClass.php');
include ('includes/session.inc');
$Title = _('外协采购单修改');
$ViewTopic = '外协采购单修改';
$BookMark = '外协采购单修改';
include ('includes/header.inc');
include ('includes/SQL_CommonFunctions.inc');
$_SESSION['DisplayRecordsMax'] = 10;



if (isset($_GET['identifier'])) {
    $_POST['identifier'] = $_GET['identifier'];
}

if (!isset($_POST['identifier'])) {
    $identifier = date('U');
} else {
    $identifier = $_POST['identifier'];
}

if (isset($_GET['New'])) {

    unset($_SESSION['Contract' . $identifier]);
    $_SESSION['Contract' . $identifier] = new ReceiveRequest();
    if (isset($_GET['Updatepo_num'])) {
        if (!isset($_SESSION['Contract' . $identifier]->po_num) or $_SESSION['Contract' .
            $identifier]->po_num == '') {
            $po_num = $_GET['Updatepo_num'];
            $sql = 'SELECT a.po_num, a.status, a.payment_term,note, a.order_date, a.need_date,a.app_remark,a.creation_date,  a.po_all_amount,a.all_line_amount,a.tax_amount,a.tax_rate,b.vendor_name, b.vendor_code,a.tax_name,a.tax_flag,a.currency_code,a.order_type,a.delivery_date,a.delivery_coyname,a.payment_type
FROM po_headers_all a, vendors b
WHERE a.vendor_code = b.vendor_code
AND po_num=' . "'" . "$po_num" . "'";
            $CustResult = DB_query($sql, $db);
            while ($myrow = DB_fetch_array($CustResult)) {
                $_SESSION['Contract' . $identifier]->po_num = $myrow['po_num'];
                $_SESSION['Contract' . $identifier]->status = $myrow['status'];
                $_SESSION['Contract' . $identifier]->order_date = $myrow['order_date'];
                $_SESSION['Contract' . $identifier]->need_date = $myrow['need_date'];
				$_SESSION['Contract' . $identifier]->app_remark = $myrow['app_remark'];
                $_SESSION['Contract' . $identifier]->create_date = $myrow['creation_date'];
                $_SESSION['Contract' . $identifier]->payment_term = $myrow['payment_term'];
                $_SESSION['Contract' . $identifier]->note = $myrow['note'];
                $_SESSION['Contract' . $identifier]->vendor_name = $myrow['vendor_name'];
				$_SESSION['Contract' . $identifier]->vendor_code= $myrow['vendor_code']; 
                $_SESSION['Contract' . $identifier]->po_all_amount = $myrow['po_all_amount'];  
                $_SESSION['Contract' . $identifier]->tax_amount = $myrow['tax_amount'];
                $_SESSION['Contract' . $identifier]->all_line_amount = $myrow['all_line_amount']; 
                $_SESSION['Contract' . $identifier]->tax_rate = $myrow['tax_rate'];    
                $_SESSION['Contract' . $identifier]->tax_name = $myrow['tax_name'];    
                $_SESSION['Contract' . $identifier]->tax_flag = $myrow['tax_flag'];       
                $_SESSION['Contract' . $identifier]->order_type = $myrow['order_type'];       
                $_SESSION['Contract' . $identifier]->delivery_date = $myrow['delivery_date'];    
                $_SESSION['Contract' . $identifier]->delivery_coyname = $myrow['delivery_coyname'];      
                $_SESSION['Contract' . $identifier]->payment_type = $myrow['payment_type'];      
				 

            }

			$sql = 'SELECT  subinventory_code from po_lines_all WHERE line=1 and  po_num=' . "'" . "$po_num" . "'";
			 //echo $sql;
            $CustResult = DB_query($sql, $db);
            while ($myrow = DB_fetch_array($CustResult)) {               
				$_SESSION['Contract' . $identifier]->header_sub = $myrow['subinventory_code'];
            }
	 

            if (!isset($_POST['Update'])) {
                $sql = 'select po_line_id,po_num,a.line,a.status,a.stockid,a.need_date,f.item_name,a.quantity,a.price,a.line_amount,ifnull(a.quantity_received,0) quantity_received,ifnull(a.quantity_accepted,0) quantity_accepted,ifnull(quantity_deliveried,0) quantity_deliveried,ifnull(a.quantity_cancelled,0) quantity_cancelled,f.units,a.line_remark,a.operation_code,a.operation_seq_num,a.wip_entity_name
				from po_lines_all a,wip_jobs_all b, sf_item_no f where a.wip_entity_name=b.wip_entity_name  and a.stockid=f.item_no and po_num = ' . "'" .$_SESSION['Contract' . $identifier]->po_num . "'
				order by a.line"; 
                $resultline = DB_query($sql, $db);
               
            }																																																		
        }
    }
}
if (isset($_POST['Update'])) {
    $InputError = 0;
    $v_date = strtotime(Date('Y-m-d H:i:s'));
   $need_date = strtotime($_POST['need_date']);
   $order_date = strtotime($_POST['order_date']);
   $HeaderSQL = "update po_headers_all  
                                 set note= '" . $_POST['note'] . "',
                                     need_date= '" . $need_date . "',
                                     order_date= '" . $order_date . "', 
									 delivery_date= '" . strtotime($_POST['delivery_date']) . "', 
									 delivery_coyname= '" . $_POST['delivery_coyname'] . "',
									 po_all_amount='" . $_POST['po_all_amount'] . "',
                                     all_line_amount='" . $_POST['all_line_amount'] . "',
                                     tax_amount='" . $_POST['tax_amount'] . "',
                                     payment_term='" . $_POST['payment_term'] . "', 
                                     payment_type='" . $_POST['payment_type'] . "', 
                                     tax_rate='" . $_POST['tax_rate'] . "',
                                     tax_name='" . $_POST['tax_name'] . "',
                                     order_type='" . $_POST['order_type'] . "',                                    
                                     status='INPROCESS',
                                     last_update_date='" . $v_date . "',
                                     last_updated_by= '" . $_SESSION['UserID'] .
                    "'
		               where po_num='" . $_POST['po_num'] . "'";
     $Resultdelete1 = DB_query($HeaderSQL, $db);
 
	   DB_Txn_Commit($db);
 

    prnMsg(_('订单头修改成功！'), 'success');
 
	echo '<meta http-equiv="refresh" content="0; url=' . $RootPath .
                    '/OspOrderModify2.php?New=Yes&Updatepo_num='. $_POST['po_num'] . '" />';
 
 
}

if (isset($_POST['DeleteAll'])) {
    $InputError = 0;
    
     $HeaderSQL = "delete from  po_headers_all  where po_num='" . $_POST['po_num'] . "'";
     $Resultdelete1 = DB_query($HeaderSQL, $db);
 
	   DB_Txn_Commit($db);
 

    prnMsg(_('订单头修改成功！'), 'success');
 
	echo '<meta http-equiv="refresh" content="0; url=' . $RootPath .
                    '/OspOrderModify2.php?New=Yes&Updatepo_num='. $_POST['po_num'] . '" />';
 
 
}

if (isset($_POST['Edit'])) {
    if ($_POST["quantity"] > $_SESSION['Contract' . $identifier]->LineItems[$_POST['LineNumber']]->
        quantity_received) {
        $_SESSION['Contract' . $identifier]->LineItems[$_POST['LineNumber']]->quantity =
            $_POST['quantity'];
        $_SESSION['Contract' . $identifier]->LineItems[$_POST['LineNumber']]->price = $_POST['price'];
        $_SESSION['Contract' . $identifier]->LineItems[$_POST['LineNumber']]->amount = $_POST['quantity'] *
            $_POST['price'];
        $line = $_SESSION['Contract' . $identifier]->LineItems[$_GET['Delete']]->line;
    } else {
        prnMsg(_('修改失败，输入量小于来料报检量！'), 'error');

    }
}

 
if (isset($_GET['delete'])   ) {
   $time = time();
	$sql2 = "select count(*) as count from po_rcv_receipt_line   where  po_num= '" . $_GET['po_num'] . "' 
	   and po_line  = '" . $_GET['po_line'] . "'  ";
	  $result = DB_query($sql2,$db);
	  $row=DB_fetch_array($result);
	 if($row['count']>0){
		 
		   
          prnMsg(_('采购单行已收货记录,不能删除'), 'error');
		 
		  echo '<meta http-equiv="refresh" content="0; url=' . $RootPath .
                    '/OspOrderModify2.php?New=Yes&Updatepo_num='. $_GET['po_num'] . '" />';

	 } else {

//把对应工单上外协已下单数量也减少
    $sql33 = "select *  from po_lines_all   where  po_line_id= '" . $_GET['po_line_id'] . "' ";
	echo $sql33;
	   $result33 = DB_query($sql33,$db);
    while ($row=DB_fetch_array($result33)) {
                    if ($row['operation_code']=='全工序') {
					 $lineq  = "update  wip_jobs_all
					    set po_quantity= po_quantity - '" . $row['quantity']  . "' 
					 where   wip_entity_name ='" . $row['wip_entity_name']  . "'   "; 
				 $Resultq = DB_query($lineq, $db); 
					} else {
					 $lineq  = "update  wip_operation_plan
					    set po_quantity= po_quantity - '" . $row['quantity']  . "' 
					 where   wip_entity_name ='" . $row['wip_entity_name']  . "' 
                               and  operation_code ='" . $row['operation_code']  . "' 
                               and  operation_seq_num ='" . $row['operation_seq_num']  . "' "; 
				 $Resultq = DB_query($lineq, $db); 
					}
	}
//把对应工单上外协已下单数量也减少
       $sum_amount=0;  
       $sql = "delete from po_lines_all   where  po_line_id= '" . $_GET['po_line_id'] . "' ";
	   $result = DB_query($sql,$db);

	  $sql3 = "select ifnull(sum(line_amount),0) sum_amount from po_lines_all where po_num= '" . $_GET['po_num'] . "' ";
	   //echo $sql3;
	   $result3 = DB_query($sql3,$db);
	   while ($row=DB_fetch_array($result3)) {
	     $sum_amount=$row['sum_amount'];
	   }
	    $sql4 = "select tax_rate  from po_headers_all where po_num= '" . $_GET['po_num'] . "' ";
	   //echo $sql3;
	   $result3 = DB_query($sql4,$db);
	   while ($row=DB_fetch_array($result3)) {
	     $tax_rate=$row['tax_rate'];
	   }

	   $tax_amount=$sum_amount * $tax_rate;
	   $all_amount=$sum_amount + $tax_amount;
 

	   $deletesql1 = "update po_headers_all 
		              set  status='INPROCESS',
					  po_all_amount=". $all_amount."   ,					  
					  tax_amount =". $tax_amount."   ,
					   all_line_amount=". $sum_amount." , 
					  last_update_date='" . $time . "',
						  last_updated_by='" . $_SESSION['UserID'] . "',
					  status='INPROCESS' 
                       where po_num='" . $_GET['po_num'] . "'";
					  // echo $deletesql1;
        $Resultdelete1 = DB_query($deletesql1, $db);


     
    prnMsg(_('删除成功！'), 'success');
	echo '<meta http-equiv="refresh" content="0; url=' . $RootPath .
                    '/OspOrderModify2.php?New=Yes&Updatepo_num='. $_GET['po_num'] . '" />';
    //DB_Txn_Commit($db);
   
	 }
}




 

if (isset($_POST['Submit'])) {

    //var_dump($_POST['po_num']);
  $InputError = 0;
 
    isset($_SESSION['num' . $identifier]) or die("no session");
    if ( $InputError == 0) {
        
        DB_Txn_Begin($db);
        
	
        $v_date = strtotime(Date('Y-m-d H:i:s'));
        $sumamount = 0.00;
        if ($InputError == 0) {
          $count=0;
          $sumamount = null;
          foreach ($_POST as $key => $value){
          	 
        //$receipt_line_id =mb_substr($key,15);
		
           if (mb_substr($key,0,10)=='po_line_id') {
              $po_line_id =mb_substr($key,10);
			  $i = $_POST[$key];   
			//  echo $po_line_id ;
               //var_dump($i);
			  if ( $po_line_id>0 ) {
              //var_dump( $_POST['amount'.$line]);    
              $count = $count + 1;
              $linesql = "UPDATE po_lines_all " . " 
			  set quantity=  " . $_POST['quantity'.$i] . ",
                                price  ='" . $_POST['price'.$i]  . "',
                                line_amount ='" . $_POST['line_amount'.$i]  . "', 
                                need_date ='" . strtotime($_POST['need_date'.$i]) . "', 
                                line_remark ='" . $_POST['line_remark'.$i]  . "',
                                last_update_date ='" . $v_date . "',
                                last_updated_by= '" . $_SESSION['UserID'] . "',
                                status= 'INPROCESS'
                        where po_line_id='" . $po_line_id . "'  ";
				//echo $linesql;
              $Result = DB_query($linesql, $db); 
             
			  //修改后工单更新已外协数量begin
			   $quantity=0;
			  if ( $_POST['operation_code'.$i]=='全工序') {
				  $lineq  = "select sum(quantity) quantity from  po_lines_all where 
                                wip_entity_name ='" . $_POST['wip_entity_name'.$i]  . "'   ";
				 $Resultq = DB_query($lineq, $db); 
				  while ($row=DB_fetch_array($Resultq)) {
	                    $quantity=$row['quantity'];
	                }
					 $lineq  = "update  wip_jobs_all
					    set po_quantity='" . $quantity  . "' 
					 where   wip_entity_name ='" . $_POST['wip_entity_name'.$i]  . "' ";
				 $Resultq = DB_query($lineq, $db);
			  
			  } else {
                
                 $lineq  = "select sum(quantity) quantity from  po_lines_all where 
                                wip_entity_name ='" . $_POST['wip_entity_name'.$i]  . "' 
                               and  operation_code ='" . $_POST['operation_code'.$i]  . "' 
                               and  operation_seq_num ='" . $_POST['operation_seq_num'.$i]  . "' ";
				 $Resultq = DB_query($lineq, $db); 
				  while ($row=DB_fetch_array($Resultq)) {
	                    $quantity=$row['quantity'];
	                }
					 $lineq  = "update  wip_operation_plan
					    set po_quantity='" . $quantity  . "' 
					 where   wip_entity_name ='" . $_POST['wip_entity_name'.$i]  . "' 
                               and  operation_code ='" . $_POST['operation_code'.$i]  . "' 
                               and  operation_seq_num ='" . $_POST['operation_seq_num'.$i]  . "' ";
				 $Resultq = DB_query($lineq, $db); 
			  }

				 //修改后工单更新已外协数量end


			  }
              
           }
        }
             	 	   

            if ($count > 0) {
                //var_dump($sumamount);
         
 
		$deletesql1 = "update po_headers_all 
		              set  status='INPROCESS',
					  tax_amount=".$_POST['tax_amount']." ,
	                      po_all_amount=".$_POST['po_all_amount']." , 
	                      all_line_amount=".$_POST['all_line_amount']." ,
						  last_update_date='" . $v_date . "',
						  last_updated_by='" . $_SESSION['UserID'] . "'
                       where po_num='" . $_POST['po_num']. "'"; 
                 $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') .
                    ': ' . _('数据存入数据库，原因是：'); //'" . FormatDateForSQL($_SESSION['Contract'.$identifier]->DispatchDate) . "',
                $DbgMsg = _('The following SQL to insert the request header record was used');
                $Result = DB_query($deletesql1, $db, $ErrMsg, $DbgMsg, true);
                DB_Txn_Commit($db);
                $msg = '外协采购单修改成功！1秒后将跳转回主页！';
                prnMsg($msg, 'success');
                echo '<meta http-equiv="refresh" content="0; url=' . $RootPath .
                    '/OspOrderModify2.php?New=Yes&Updatepo_num='. $_POST['po_num'] . '" />';
              
            } else {
                prnMsg(_('采购单行无数据，如果确定全部取消请选择：全部取消'), 'error');
                unset($_SESSION['Contract' . $identifier]);
            }
            DB_Txn_Commit($db);
        }
        include ('includes/footer.inc');
        exit;
    } else {
        header('Location: OspOrderModify2.php');
    }
} elseif (isset($_POST['Cancel'])) {
    $po_num = $_SESSION['Contract' . $identifier]->po_num;
    $HeaderSQL = "update po_headers_all   
                          set status= 'Cancel', last_update_date='" . $v_date . "',
						  last_updated_by='" . $_SESSION['UserID'] . "'
		        where po_num='" . $po_num . "'";
    $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') .
        ': ' . _('数据存入数据库，原因是：'); //'" . FormatDateForSQL($_SESSION['Contract'.$identifier]->DispatchDate) . "',
    $DbgMsg = _('The following SQL to insert the request header record was used');
    $Result = DB_query($HeaderSQL, $db, $ErrMsg, $DbgMsg, true);
    $HeaderSQL2 = "update po_lines_all 
                          set status= 'Cancel',
                              quantity=0, last_update_date='" . $v_date . "',
						  last_updated_by='" . $_SESSION['UserID'] . "'
		        where po_num='" . $po_num . "'";
    $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') .
        ': ' . _('数据存入数据库，原因是：'); //'" . FormatDateForSQL($_SESSION['Contract'.$identifier]->DispatchDate) . "',
    $DbgMsg = _('The following SQL to insert the request header record was used');
    $Result2 = DB_query($HeaderSQL2, $db, $ErrMsg, $DbgMsg, true);

    $msg = '采购单取消成功！1秒后将跳转回上一页！';
    prnMsg($msg, 'success');
    echo '<meta http-equiv="refresh" content="1; url=' . $RootPath .
        '/OspOrderModify.php" />';
    echo '<br />';
    include ('includes/footer.inc');
    exit;
} else {
    session_start() or die("session is not started");
    $_SESSION['num' . $identifier] = 400;
}
echo '	<div class="centre">
		<a href="' . $RootPath . '/OspOrderModify.php">返回重新选择采购单</a>
	</div>';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme .
    '/images/supplier.png" title="' . _('Dispatch') . '" alt="" />' . ' ' . $Title .
    '</p>';



echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES,
    'UTF-8') . '" method="post"><input type="hidden" name = "identifier" value ="' .
    $identifier . '">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<div class="centre"> 
<p id="Prompt" style="color: red;font-size: 20px"></p>
                    </div>';
echo '<table class="selection">';
 

if ($_SESSION['Contract' . $identifier]->status == 'INPROCESS') {
    $v_status = '待签核';
} elseif ($_SESSION['Contract' . $identifier]->status == 'APPROVED') {
    $v_status = '已签核';
} elseif ($_SESSION['Contract' . $identifier]->status == 'REJECTED') {
    $v_status = '已拒签';
} else {
    $v_status = '已取消';
} 
if (!isset($_POST['dangqian_date'])) {
      $_POST['dangqian_date'] = Date('Y-m-d');
     }
$v_order_date = date('Y-m-d', $_SESSION['Contract' . $identifier]->order_date);
$v_delivery_date = date('Y-m-d', $_SESSION['Contract' . $identifier]->delivery_date);
$v_need_date = date('Y-m-d', $_SESSION['Contract' . $identifier]->need_date);
$v_create_date = date('Y-m-d', $_SESSION['Contract' . $identifier]->create_date);
echo '<div class="text-nav">
		<div class="text-nav-1"><div>' . _('采购单') . ':</div><input type="text" readonly="readonly" value="' . $_SESSION['Contract' . $identifier]->po_num . '" /></div> <input type="hidden" class="text"  name="po_num" value="' . $_SESSION['Contract' . $identifier]->po_num . '" />
<div class="text-nav-1"><div>创建日期：</div>' . '<input type="text" readonly="readonly" value="' . $v_create_date . '" /></div>
<div class="text-nav-1"><div>状态：</div>' . '<input type="text" readonly="readonly" value="' . $v_status . '" /></div>';

echo '<div class="text-nav-1"><div>采购日期：</div>' .
    '<input type="text" onfocus="WdatePicker()"  name="order_date" maxlength="10" size="11" value="' .
    $v_order_date . '" /></div>
       
	<div class="text-nav-1"><div>需求日期：</div>
      <input type="text" onfocus="WdatePicker()"  name="need_date" maxlength="10" size="11" value="' .
    $v_need_date . '" /></div>
	<div class="text-nav-1"><div>' . _('供应商编号') . ':</div>
    <input type="text" readonly="readonly" value="' . $_SESSION['Contract' . $identifier]->vendor_code . '" /></div> 
	<div class="text-nav-2"><div>' . _('供应商名称') . ':</div>
    <input type="text" readonly="readonly" value="' . $_SESSION['Contract' . $identifier]->vendor_name . '" /></div> 
    <div class="text-nav-1"><div>' . _('仓库') . ':</div>
	 
			<select name="header_sub" id="">'; 
				 
					$sql = "select loccode,locationname from  locations  where managed='Y' order by loccode";
					$result = DB_query($sql,$db);
					while ($v = DB_fetch_array($result)) {
						if ($v['loccode']==$_SESSION['Contract' . $identifier]->header_sub ) {
				 
				echo '<option value="'.$v['loccode'].'" selected="selected">'.$v['locationname'].'</option>';
				  }else{ 
				echo '<option value="'.$v['loccode'].'">'.$v['locationname'].'</option>';
				 		}
					}
				 
			echo '</select>
		</div>';

 	 
echo ' <div class="text-nav-1 required"><div>' . _('税别') . '</div>

			<select name="tax_name" onchange="psel()"   id="text_slect_tax_name">';
				
					$sql = "select tax_mount,tax_name from tax_set order by tax_name";
					$result = DB_query($sql,$db);
					while ($v = DB_fetch_array($result)) {
						if ($v['tax_name']== $_SESSION['Contract' . $identifier]->tax_name ) {
				
				echo  '<option value="'.$v['tax_name'].'" selected="selected">'.$v['tax_name'].'</option>';
				 }else{
				echo '<option value="'.$v['tax_name'].'">'.$v['tax_name'].'</option>';
					}
				}
			echo '</select></div>
	';		 	
echo '	<div class="text-nav-1  ">
			<div>税率:</div>			 
			<input  type="text" readonly="readonly" class="number" autocomplete="off" id="text_slect_tax_rate" name="tax_rate" value="'. $_SESSION['Contract' . $identifier]->tax_rate.'" size="10" maxlength="100"/></div> 
';




  echo '<div class="text-nav-1"><div>' . _('是否含税') . '</div>

			<select name="tax_flag" id="text_slect_tax_flag"   onchange="checkall()">';
 	
					$sql5 = "select type_code,type_name from sys_type ";
					$result5 = DB_query($sql5,$db);
					while ($v = DB_fetch_array($result5)) {
						if ($v['type_code']==$_SESSION['Contract' . $identifier]->tax_flag ) {

				echo '<option value="'.$v['type_code'].'" selected="selected">'.$v['type_name'].'</option>';
				  }else{
				echo '<option value="'.$v['type_code'].'">'.$v['type_name'].'</option>';
				 		}
					}

			echo '</select></div>
			 	';

	

echo '<div class="text-nav-1"><div>未税金额</div>  
			 <input type="text" readonly="readonly" class="number" size="11" onblur="check55()" name="all_line_amount" id="all_line_amount" value="' . $_SESSION['Contract' . $identifier]->all_line_amount . '" /> </div>  

	   <div class="text-nav-1"><div>税金</div>   
			 <input type="text" readonly="readonly" class="number" size="11" name="tax_amount" onblur="check55()" id="tax_amount" value="' . $_SESSION['Contract' . $identifier]->tax_amount . '" /> </div> 
			<div class="text-nav-1"><div>含税金额</div> 
		  <input type="text" readonly="readonly" class="number" size="11" name="po_all_amount" onblur="check55()" id="order_all_amount" value="' . $_SESSION['Contract' . $identifier]->po_all_amount . '" /> </div> 
		 
			
			
					
			'; 
 
echo ' 
         <div class="text-nav-1"><div>付款条件</div>      
			<input type="text"  size="11" name="payment_term" id="payment_term" value="' . $_SESSION['Contract' . $identifier]->payment_term . '" /> </div>
			<div class="text-nav-1"><div>' . _('付款方式') . ':</div>
			<input type="text"  size="11" name="payment_type" id="payment_type" value="' . $_SESSION['Contract' . $identifier]->payment_type . '" /> </div>
           ';
 echo '<div class="text-nav-1"><div>交货日期：</div>' .
		'<input type="text" onfocus="WdatePicker()"  name="delivery_date" maxlength="10" size="11" value="' .
		$v_delivery_date . '" /></div>
	   ';

echo '  
<div class="text-nav-2"><div>采购单备注</div>   
          <input type="text"  name="note"   maxlength="100" size="80"  value="'. $_SESSION['Contract' .
    $identifier]->note . '"/></div>
        </tr>';


 


echo ' <div class="text-nav-2"><div>审核备注</div> 
			<input type="text"  name="app_remark" pattern="^[^?.\+<>!&’:,;?$\^]+$" maxlength="10" size="11" value="' . $_SESSION['Contract' . $identifier]->app_remark . '" />
              </div>  
   <td  >' . _('上传新附件') . ':</td>
			  <td><a href="' . $RootPath . '/POUploadNewfile.php?OrderNum=' . $_SESSION['Contract' . $identifier]->po_num . '"target="_blank">上传 </td>

			<td>  <input type="hidden" name="po_all_amount_old" id="po_all_amount_old" value="'. $_SESSION['Contract'.$identifier]->po_all_amount . '"/> 
			<input type="hidden" name="tax_amount_old" id="tax_amount_old_old" value="'. $_SESSION['Contract'.$identifier]->tax_amount_old . '"/>
			<input type="hidden" name="dangqian_date" id="dangqian_date" value="'.$_POST['dangqian_date'] . '"/>
			<input type="hidden" name="all_line_amount_old" id="all_line_amount_old" value="'. $_SESSION['Contract'.$identifier]->all_line_amount . '"/></td>
        </tr>';
    
echo '</table>';

echo '<div class="centre">
	<input type="submit" name="Update" value="' . _('采购单头修改保存') . '" />';
 $sql33 = "select *  from po_lines_all   where  po_num= '" . $_SESSION['Contract' . $identifier]->po_num . "' "; 
 //echo $sql33;
  $result33 = DB_query($sql33,$db);
  if (DB_num_rows($result33)==0) {
echo '	<input type="submit" name="DeleteAll" value="' . _('删除') . '" />';
} 
	echo '</div>
    </div> ';


if (!isset($_SESSION['Contract' . $identifier]->po_num)) {
    include ('includes/footer.inc');
    exit;
}

$i = 0; //Line Item Array pointer
 
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
//用隐藏域保存采购单表号
echo ' <input type="hidden" class="text"  name="po_num" value="' . $_SESSION['Contract' . $identifier]->po_num . '" />';
 
echo ' <input type="hidden" class="text"  name="po_all_amount2" id="po_all_amount2" value="' . $_SESSION['Contract' . $identifier]->po_all_amount . '" />';
echo ' <input type="hidden" class="text"  name="all_line_amount2" id="all_line_amount2"  value="' . $_SESSION['Contract' . $identifier]->all_line_amount . '" />';
echo ' <input type="hidden" class="text"  name="tax_amount2" id="tax_amount2" value="' . $_SESSION['Contract' . $identifier]->tax_amount . '" />';
echo '<br /> ';
  

echo ' <div class="text-nav-table"> <table class="selection">
	<tr>
		<th>' . _('行') . '</th>
		<th   >' . _('工单') . '</th>
		<th   >' . _('工序名称') . '</th>
		<th   >' . _('料号') . '</th>
		<th  >' . _('料号名称') . '</th>	  
		<th   width=50>' . _('单位') . '</th>  
                    <th   width=80>' . _('已收货量') . '</th> 
		<th   width=50>' . _('需求日期') . '</th>  
                 <th   width=80>' . _('数量') . '</th> 
                 <th   width=80>' . _('单价') . '</th> 
                 <th   width=80>' . _('金额') . '</th>  
                 <th   width=80>' . _('备注') . '</th>             
                
	</tr>';

$k = 0;
$i = 1;

while ($myrow = DB_fetch_array($resultline)) {
    
    if ($k == 1) {
        echo '<tr class="EvenTableRows">';
        $k = 0;
    } else {
        echo '<tr class="OddTableRows">';
        $k++;
    }
 
    
	 echo ' <td>' . $myrow['line'] . '</td>
	 <td>' . $myrow['wip_entity_name'] . '</td>
	 <td>' . $myrow['operation_code'] . '</td>
	        <td>' . $myrow['stockid'] . '</td>
			<td>' . $myrow['item_name'] . '</td>
			<td>' . $myrow['units'] . '</td>'; 
          echo '  
			<td>' . $myrow['quantity_received']  . '</td> ';
       echo '<td><input id="need_date' .$i.'"  onblur="checkneeddate(' .$i.')" onfocus="WdatePicker()" style="background-color:yellow" type="text"  name="need_date'.$i.'"   size="9"  value="' . date('Y-m-d',$myrow['need_date'])  . '" /></td> ';
       echo ' <td><input id="quantity' .$i.'" onblur="checkall()" onkeyup="webdesign(' .$i.')"   style="background-color:yellow" type="text"  name="quantity'.$i.'" class="number" size="8"  value="' . $myrow['quantity']  . '" /></td> ';
	   echo ' <td><input id="price' .$i.'" onblur="checkall()"  onkeyup="webdesign(' .$i.')" style="background-color:yellow" type="text"  name="price'.$i.'" class="number" size="8"  value="' . $myrow['price']  . '" /></td> ';
	   echo ' <td><input id="line_amount' .$i.'" readonly="readonly" style="background-color:yellow" type="text"  name="line_amount'.$i.'" class="number" size="8"  value="' . $myrow['line_amount']  . '" /> 
          <td><input  style="background-color:yellow" type="text"  name="line_remark'.$i.'"   size="12"  value="' . $myrow['line_remark']  . '" /> ';
	 

	   if ($myrow['quantity_received']==0 ) {
		echo '<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?po_num=' .$_SESSION['Contract' . $identifier]->po_num .'&po_line_id=' .$myrow['po_line_id'] . '&amp;delete=1" onclick="return confirm(\'' . _('是否要删除这个料号?') . '\');">' . _('删除')  . '</a></td>';
	   }


	  echo ' <input type="hidden" name="po_line_id'.$myrow['po_line_id'].'" value="'.$i.'" />
	   <input  type="hidden"  name="wip_entity_name'.$i.'" value="' . $myrow['wip_entity_name']  . '" /> 
	   <input  type="hidden" id="quantity_received'.$i.'" name="quantity_received'.$i.'" value="' . $myrow['quantity_received']  . '" /> 
	   <input  type="hidden"  name="operation_seq_num'.$i.'" value="' . $myrow['operation_seq_num']  . '" /> 
	   <input  type="hidden"  name="operation_code'.$i.'" value="' . $myrow['operation_code']  . '" />  </td> ';
    

       echo ' 
     </tr>';            
      $i++;
	echo ' </tr>';

}
echo '</table></div>
   
	
	<div class="centre">
                <input type="submit" id="submit" name="Submit" value="' . _('采购单行修改保存') .
    '" />   
	</div>
       
    </div>
    </form>';
 
//*********************************************************************************************************
 
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>新建采购订单</title>
<link rel="shortcut icon" href="/favicon.ico"/>
<link rel="icon" href="/favicon.ico"/>
<meta http-equiv="Content-Type" content="application/html; charset=utf-8"/>
<link href="/css/xenos/default.css" rel="stylesheet" type="text/css"/>
<script type="text/javascript" src ="./JXC/javascripts/miscfunctions.js"></script>
<script type="text/javascript" src ="./JXC/javascripts/wdatepicker.js"></script>
<script type="text/javascript">var basepath='./JXC/statics/base/images';</script>
<script type="text/javascript" src="./JXC/statics/base/js/metvar.js"></script>
<script type="text/javascript" src="./JXC/statics/base/js/jQuery1.7.2.js"></script>
<script type="text/javascript" src="./JXC/statics/base/js/uploadify/jquery.uploadify.v2.1.4.min.js"></script>
<script type="text/javascript" src="./JXC/statics/base/js/iframes.js"></script>
<script type="text/javascript" src="./JXC/statics/base/js/cookie.js"></script>
<script type="text/javascript" src="./JXC/statics/base/js/jquery.livequery.js"></script>

<link rel="stylesheet" href="jquery.ui.autocomplete.css">
<script type="text/javascript" src="ui/jquery.ui.core.js"></script>
<script type="text/javascript" src="ui/jquery.ui.widget.js"></script>
<script type="text/javascript" src="ui/jquery.ui.position.js"></script>
<script type="text/javascript" src="ui/jquery.ui.autocomplete.js"></script>

<script src="./JXC/javascript/jquery-1.7.2.min.js"></script>
<script src="./JXC/javascript/lhgdialog.min.js?self=true&skin=chrome"></script>
   
<script src="./javascript/bootstrap.min.js"></script>

<script type="text/javascript">
/*ajax执行*/
var lang = 'cn';
var metimgurl='./JXC/statics/base/images/';
var depth='';
$(document).ready(function(){
	ifreme_methei();
});
</script>
<script type="text/javascript">

function checkaddall(){               
	
                      var allamount=0;  
                                
                                for(var j=1 ; j < 50; j++){   
									if (document.getElementById("lineamount" + j)==null)  {
									p=0;
										}
									else {	
										  
										 var shuliang=document.getElementById("add_quantity"+j).value;
		                           var danjia=document.getElementById("text_slect_unit_price"+j).value;
								   if(shuliang==""){
			                         shuliang=0;
		                               }
		                           if(danjia==""){
		                           	danjia=0;
		                           }
								   if (shuliang>0   )
								   {
									   document.getElementById("lineamount"+j).value=Math.round(Number(shuliang)* Number(danjia)*100)/100 ;
								   }

								   var  lineamount=0 
                                   var lineamount=document.getElementById("lineamount"+j).value;
                                 
								   if( lineamount>0 )
								   {  
								   allamount=Number(allamount) + Number(lineamount);
                                   //如果input中有数据
                       
                                  
                                   }
								    }
								}
								
		var all_line_amount_old=document.getElementById("all_line_amount_old").value; 
		var  all_line_amount= parseFloat(allamount)+parseFloat(all_line_amount_old); 
        var tax_rate= document.getElementById("text_slect_tax_rate").value;   
       
        var tax_amount= Math.round(Number(all_line_amount)*Number(tax_rate) *100)/100;  	
		var  po_all_amount=  Number(tax_amount) + Number(all_line_amount); 		
		document.getElementById("all_line_amount").value= Math.round(Number(all_line_amount)*100)/100;
       document.getElementById("po_all_amount").value=Math.round(Number(po_all_amount)*100)/100;
       document.getElementById("tax_amount").value=Math.round(Number(tax_amount)*100)/100;
	   document.getElementById("all_line_amount_new").value==Math.round(Number(all_line_amount)*100)/100;
	   document.getElementById("po_all_amount_new").value= Math.round(Number(po_all_amount)*100)/100;
       document.getElementById("tax_amount_new").value=Math.round(Number(tax_amount)*100)/100; 
     

  }

  function psel(){
		var name=$('#text_slect_tax_name').val()
		$.get("","pdata="+name,function(res){
			name = res.split(":")		  
				$("#text_slect_tax_rate").val(name[0])  
   
		})
   ;
   //alert(name);
checkall();
	}  



 function checkall(){                               
			    var allamount=0; 
			    var tax_rate=document.getElementById("text_slect_tax_rate").value;
				var tax_flag=document.getElementById("text_slect_tax_flag").value; 
				var all_rate = Number(1) + Number(tax_rate) ;				
				for(var i=1 ; i < 50; i++){   
					if (document.getElementById("line_amount" + i)==null)  {
						p=0;
					}
					else {	
						
						var shuliang=document.getElementById("quantity"+i).value;
						var danjia=document.getElementById("price"+i).value;
						if(shuliang==""){
							shuliang=0;
						}
						if(danjia==""){
							danjia=0;
						}
					   if (shuliang>0   )
					   {
							document.getElementById("line_amount"+i).value=Math.round(Number(shuliang)* Number(danjia)*100)/100 ;
					   }

					   var  lineamount=0 
					   var lineamount=document.getElementById("line_amount"+i).value;
					 
					   if( lineamount>0 )
					   {  
					   allamount=Number(allamount) + Number(lineamount);
					   //如果input中有数据
				   
						  
						   }
							}
						}
		
		
		if (tax_flag=='Y')
	 {   
		 var  not_tax_amount=Number(allamount) /  Number(all_rate);
		 var tax_amount =Number(allamount) -  Number(not_tax_amount);
		  document.getElementById("all_line_amount").value=Math.round(Number(not_tax_amount)*100)/100;
	     document.getElementById("order_all_amount").value= Math.round(Number(allamount)*100)/100; 
		document.getElementById("tax_amount").value=Math.round(Number(tax_amount)*100)/100; 

           
	 } else {
	 var tax_amount=Number(allamount) * Number(tax_rate);		
	 var  order_all_amount=Number(allamount) +  Number(tax_amount);
	 document.getElementById("all_line_amount").value= Math.round(Number(allamount)*100)/100;
	 document.getElementById("order_all_amount").value= Math.round(Number(order_all_amount)*100)/100; 
		document.getElementById("tax_amount").value=Math.round(Number(tax_amount)*100)/100; 
	 }
		
		
		 var a=document.getElementById("order_all_amount").value;
        var b=document.getElementById("youhui_amount").value;  
      if(parseInt(b)>parseInt(a)){
            document.getElementById("Prompt").innerHTML="优惠金额"+b+"不可以大于总金额！"+a;
            document.getElementById("youhui_amount").value="";
            document.getElementById("youhui_amount").focus();
        } 
        else {
            document.getElementById("Prompt").innerHTML="";
        }
		/*
        var tax_rate=document.getElementById("text_slect_tax_rate").value;
        var all_line_amount = allamount;
        var tax_amount= Math.round(Number(all_line_amount)*Number(tax_rate) *100)/100; 
		var po_all_amount =Number(tax_amount) + Number(allamount);			
		document.getElementById("po_all_amount").value= Math.round(Number(po_all_amount)*100)/100;
       document.getElementById("tax_amount").value=Math.round(Number(tax_amount)*100)/100;
       document.getElementById("all_line_amount").value=Math.round(Number(all_line_amount)*100)/100; 
	   document.getElementById("po_all_amount2").value= Math.round(Number(po_all_amount)*100)/100;
       document.getElementById("tax_amount2").value=Math.round(Number(tax_amount)*100)/100;
       document.getElementById("all_line_amount2").value=Math.round(Number(all_line_amount)*100)/100;
  */
 

  }

 function  check55(){
	 
	 
	 
	 
	 var all_line_amount=document.getElementById("all_line_amount").value;
		   var tax_rate=document.getElementById("text_slect_tax_rate").value;
          var tax_amount=  Math.round(Number(tax_rate)*Number(all_line_amount) *100)/100;  
		  var po_all_amount = Number(tax_amount) + Number(all_line_amount);
		  
        document.getElementById("order_all_amount").value=Math.round(Number(po_all_amount)*100)/100; 
		document.getElementById("tax_amount").value=Math.round(Number(tax_amount)*100)/100; 
        
      var a=document.getElementById("order_all_amount").value;
        var b=document.getElementById("youhui_amount").value;  
      if(parseInt(b)>parseInt(a)){
            document.getElementById("Prompt").innerHTML="优惠金额"+b+"不可以大于总金额！"+a;
            document.getElementById("youhui_amount").value="";
            document.getElementById("youhui_amount").focus();
        } 
        else {
            document.getElementById("Prompt").innerHTML="";
        }
     }   
		
	      function check56(){
		var allamount=0; 
        var all_line_amount=document.getElementById("all_line_amount").value;
         var tax_rate=document.getElementById("text_slect_tax_rate").value;
        var tax_amount= Number(all_line_amount) * Number(tax_rate); 	
		var order_all_amount= Number(all_line_amount) + Number(tax_amount); 	 
		document.getElementById("order_all_amount").value=Math.round(Number(order_all_amount)*100)/100;	 
		document.getElementById("tax_amount").value=Math.round(Number(tax_amount)*100)/100;	 		
		 
        var b=document.getElementById("youhui_amount").value;  
      if(parseInt(b)>parseInt(order_all_amount)){
            document.getElementById("Prompt").innerHTML="优惠金额"+b+"不可以大于总金额！"+order_all_amount;
            document.getElementById("youhui_amount").value="";
            document.getElementById("youhui_amount").focus();
        } 
        else {
            document.getElementById("Prompt").innerHTML="";
        }
	   /* var all_line_amount=document.getElementById("all_line_amount").value;
		   var tax_rate=document.getElementById("text_slect_tax_rate").value;
          var tax_amount=  Math.round(Number(tax_rate)*Number(all_line_amount) *100)/100;  
		  var po_all_amount = Number(tax_amount) + Number(all_line_amount);
		  
        document.getElementById("po_all_amount").value=Math.round(Number(po_all_amount)*100)/100; 
		document.getElementById("tax_amount").value=Math.round(Number(tax_amount)*100)/100; 
        
      var a=document.getElementById("po_all_amount").value;
        var b=document.getElementById("youhui_amount").value;  
      if(parseInt(b)>parseInt(a)){
            document.getElementById("Prompt").innerHTML="优惠金额"+b+"不可以大于总金额！"+a;
            document.getElementById("youhui_amount").value="";
            document.getElementById("youhui_amount").focus();
        } 
        else {
            document.getElementById("Prompt").innerHTML="";
        }
		*/
     }     

function checkneeddate(s1)
{
var a=document.getElementById("need_date"+s1).value;
var b=document.getElementById("dangqian_date").value; 
      if( a< b ){
            document.getElementById("Prompt").innerHTML="修改日期不能小于当前日期！";
            document.getElementById("need_date"+s1).value=b;
            document.getElementById("need_date"+s1).focus();
        }  else {
            document.getElementById("Prompt").innerHTML="";
        }
 
}

 function webdesign(s1)
{
var a=document.getElementById("quantity"+s1).value;
var b=document.getElementById("price"+s1).value;
var c=document.getElementById("quantity_received"+s1).value; 
      if( parseFloat(a)<0){
            document.getElementById("Prompt").innerHTML="数量不可以小于0！"+a;
            document.getElementById("quantity"+s1).value=0;
            document.getElementById("quantity"+s1).focus();
        } else if ( parseFloat(a)<parseFloat(c)){
            document.getElementById("Prompt").innerHTML="数量不可以小于已收货数量！"+c;
            document.getElementById("quantity"+s1).value=c;
            document.getElementById("quantity"+s1).focus();
        } 
        else if( parseFloat(b)<0){
            document.getElementById("Prompt").innerHTML="价格不可以小于0！"+b;
            document.getElementById("price"+s1).value=0;
            document.getElementById("price"+s1).focus();
        } else {
            document.getElementById("Prompt").innerHTML="";
        }

document.getElementById("line_amount"+s1).value=Math.round(Number(a*b)*100)/100;
}

 	  function  check2(s1){
		var quantity=document.getElementById("quantity"+s1).value;
		var price=document.getElementById("price"+s1).value;
		if(quantity==""){
			quantity=0;
		}
		if(price==""){
			price=0;
		}
		document.getElementById("amount"+s1).value=Math.round(Number(Number(quantity)* Number(price)) *100)/100;

	}
	 
 


function metreturn(url){
	if(url){
		location.href=url;
	}else if($.browser.msie){
		history.go(-1);
	}else{
		history.go(-1);
	}
} 



	</script>
</head>

</html>
<?php
include ('includes/footer.inc');
?>