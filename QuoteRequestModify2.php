<?php
 
/*
* To change this license header, choose License Headers in Project Properties.
* To change this template file, choose Tools | Templates
* and open the template in the editor.
*/
include ('includes/DefinePOUpdateClass.php');
include ('includes/session.inc');
$Title = _('报价单修改');
$ViewTopic = '报价单修改';
$BookMark = '报价单修改';
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

//新增行处理 begin
if (isset($_POST['Save'])) {
	$errorflag = 1;
	foreach ($_POST as $key => $value) {
		if ($value != '') {
			if (substr($key, 0,9)=='item_name') {
				$errorflag = 0;
				$i = substr($key, 9);
				if ($value != '') {
					if ($_POST['UOM'.$i]=='') {
						$errorflag = 1;
						prnMsg($value.'未填写单位，请填写单位！',error);
					}
					if ($_POST['unitprice'.$i]=='') {
						$errorflag = 1;
						prnMsg($value.'未填写单价，请填写单价！',error);
					}
					if ($_POST['quantity'.$i]=='') {
						$errorflag = 1;
						prnMsg($value.'未填写数量，请填写数量！',error);
					}

				}
			}
		}
	}
	if ($errorflag ==0) {
		foreach ($_POST as $key => $value) {
			if ($value != '') {
				if (substr($key, 0,9)=='item_name') {
					$i = substr($key, 9);

					$lineamount[$i] = $_POST['quantity'.$i] * $_POST['unitprice'.$i] ;

				}
			}
		}
	}
	if ($errorflag == 0) {

		$sumamount=0.00;
		DB_Txn_Begin($db);
		$time = time();
		$order_amount = 0;

		$sql_num = "select 	max(line) line from quote_lines_all where  order_number  = '" . $_POST['order_number']. "'";
	 
		$result_num = DB_query($sql_num, $db);
		$rownum = DB_num_rows($result_num);
		while ($v = DB_fetch_array($result_num)) {
			$line =  $v['line'];			 
		}

		foreach ($_POST as $key => $value) {
			if ($value != '') {
				if (substr($key, 0,9)=='item_name') {
					$i = substr($key, 9);
					$lineamount[$i] =$_POST['quantity'.$i] * $_POST['unitprice'.$i] ;
					if($_POST['stockid'.$i]==''){
						$_POST['stockid'.$i] = 'NULL';
						$bumishu[$i] = 0;
					}
					$line=$line+1;

					$sql = "insert into quote_lines_all(order_number,line,item_no,item_name,item_desc,uom,price,need_date,
						quantity,need_remark,line_amount,
						creation_date,created_by,last_update_date,last_updated_by)
						values('".$_POST['order_number']."','".$line."','".$_POST['stockid'.$i]."','".$_POST['item_name'.$i]."','".$_POST['item_desc'.$i]."','".$_POST['UOM'.$i]."','".$_POST['unitprice'.$i]."','".strtotime($_POST['need_date'.$i])."',
						'".$_POST['quantity'.$i]."','".$_POST['need_remark'.$i]."','".$_POST['line_amount'.$i]."',
						'".$time."','".$_SESSION['UserID']."','".$time."','".$_SESSION['UserID']."') ";

			  echo $sql;

					$result = DB_query($sql,$db);
					$order_amount = $order_amount + $lineamount[$i];
				}
			}
		}
       

		$deletesql1 = "update quote_headers_all 
		              set order_all_amount=". $_POST['order_all_amount_new']." ,
					  order_payment_amount=". $_POST['order_payment_amount_new']." ,
					  order_invoice_amount=". $_POST['order_invoice_amount_new']." ,
					  youhui_amount=". $_POST['youhui_amount_new']." , last_update_date='" . $time . "',
					 last_updated_by='" . $_SESSION['UserID'] . "',
					 last_update_date='" . $time . "',
					  status='开始' 
                       where order_number='" . $_POST['order_number'] . "'";
					  // echo $deletesql1;
        $Resultdelete1 = DB_query($deletesql1, $db);

			
		DB_Txn_Commit($db);
		$msg = '报价单新增行成功！1秒后将跳转回上一页！';
        prnMsg($msg, 'success');
	 echo '<meta http-equiv="refresh" content="0; url=' . $RootPath .
                     '/QuoteRequestModify2.php?New=Yes&Updateorder_number='. $_POST['order_number'] . '" />';
    //echo '<meta http-equiv="refresh" content="2; url=' . $RootPath .'/QuoteRequestModify.php" />';
    echo '<br />';
    //include ('includes/footer.inc');
    //exit;

	}
}

//新增行处理 end

if (isset($_GET['New'])) {

    unset($_SESSION['Contract' . $identifier]);
    $_SESSION['Contract' . $identifier] = new ReceiveRequest();
    if (isset($_GET['Updateorder_number'])) {
        if (!isset($_SESSION['Contract' . $identifier]->order_number) or $_SESSION['Contract' .
            $identifier]->order_number == '') {
            $order_number = $_GET['Updateorder_number'];
            $sql = 'SELECT a.order_number, a.status, a.term_name,header_remark, a.need_date,a.approve_remark,a.creation_date, a.order_all_amount,a.youhui_amount,a.order_payment_amount,a.order_invoice_amount,b.customer_name, b.customer_code, a.subject,project,jiaohuotiaojian,baozhuang,youxiaoxing1,youxiaoxing2,zhiliangbaozheng,mainfeifuwu,a.yewu,a.currency_code,a.customer_contact,a.tax_name,a.yunfei,a.coycode
FROM quote_headers_all a, customers b
WHERE a.customer_code = b.customer_code
AND order_number=' . "'" . "$order_number" . "'";
            $CustResult = DB_query($sql, $db);
            while ($myrow = DB_fetch_array($CustResult)) {
                $_SESSION['Contract' . $identifier]->order_number = $myrow['order_number'];
                $_SESSION['Contract' . $identifier]->status = $myrow['status'];
                $_SESSION['Contract' . $identifier]->need_date = $myrow['need_date'];
				$_SESSION['Contract' . $identifier]->approve_remark = $myrow['approve_remark'];
                $_SESSION['Contract' . $identifier]->create_date = $myrow['creation_date'];
                $_SESSION['Contract' . $identifier]->term_name = $myrow['term_name'];
                $_SESSION['Contract' . $identifier]->header_remark = $myrow['header_remark'];
                $_SESSION['Contract' . $identifier]->customer_name = $myrow['customer_name'];
				$_SESSION['Contract' . $identifier]->customer_code= $myrow['customer_code'];
                $_SESSION['Contract' . $identifier]->amount = $myrow['amount']; 
                $_SESSION['Contract' . $identifier]->order_all_amount = $myrow['order_all_amount']; 
                $_SESSION['Contract' . $identifier]->youhui_amount = $myrow['youhui_amount']; 
                $_SESSION['Contract' . $identifier]->order_payment_amount = $myrow['order_payment_amount'];
                $_SESSION['Contract' . $identifier]->order_invoice_amount = $myrow['order_invoice_amount'];  
                $_SESSION['Contract' . $identifier]->subject = $myrow['subject']; 
                $_SESSION['Contract' . $identifier]->project = $myrow['project']; 
                $_SESSION['Contract' . $identifier]->jiaohuotiaojian = $myrow['jiaohuotiaojian']; 
                $_SESSION['Contract' . $identifier]->baozhuang = $myrow['baozhuang'];
                $_SESSION['Contract' . $identifier]->youxiaoxing1 = $myrow['youxiaoxing1'];     
                $_SESSION['Contract' . $identifier]->youxiaoxing2 = $myrow['youxiaoxing2'];     
                $_SESSION['Contract' . $identifier]->zhiliangbaozheng = $myrow['zhiliangbaozheng'];  
                $_SESSION['Contract' . $identifier]->mainfeifuwu = $myrow['mainfeifuwu'];  
                $_SESSION['Contract' . $identifier]->coycode = $myrow['coycode'];          
                $_SESSION['Contract' . $identifier]->yunfei = $myrow['yunfei'];             
                $_SESSION['Contract' . $identifier]->tax_name = $myrow['tax_name'];                
            }

			
            if (!isset($_POST['Update'])) {
                $sql = 'select *
				from quote_lines_all a where order_number = ' . "'" .$_SESSION['Contract' . $identifier]->order_number . "'
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
   $qianding_date = strtotime($_POST['qianding_date']);
   $HeaderSQL = "update quote_headers_all  
                                 set header_remark= '" . $_POST['header_remark'] . "',
                                     need_date= '" . $need_date . "',
                                     youhui_amount='" . $_POST['youhui_amount'] . "',
									 order_payment_amount='" . $_POST['order_payment_amount'] . "',
                                     order_invoice_amount='" . $_POST['order_invoice_amount'] . "',
                                     term_name='" . $_POST['term_name'] . "',  
									 subject='" . $_POST['subject'] . "',
									 project='" . $_POST['project'] . "',
                                     jiaohuotiaojian='" . $_POST['jiaohuotiaojian'] . "',
                                     baozhuang='" . $_POST['baozhuang'] . "',    
                                     youxiaoxing1='" . $_POST['youxiaoxing1'] . "',    
                                     youxiaoxing2='" . $_POST['youxiaoxing2'] . "',    
                                     zhiliangbaozheng='" . $_POST['zhiliangbaozheng'] . "',    
                                     mainfeifuwu='" . $_POST['mainfeifuwu'] . "',    
                                     yewu='" . $_POST['yewu'] . "',    
                                     header_remark='" . $_POST['header_remark'] . "',  
                                     coycode='" . $_POST['coycode'] . "',    
                                     tax_name='" . $_POST['tax_name'] . "',  
                                     yunfei='" . $_POST['yunfei'] . "',   
                                     status='开始',
                                     last_update_date='" . $v_date . "',
                                     last_updated_by= '" . $_SESSION['UserID'] .
                    "'
		               where order_number='" . $_POST['order_number'] . "'";
     $Resultdelete1 = DB_query($HeaderSQL, $db);

	   DB_Txn_Commit($db);
 

    prnMsg(_('报价单头修改成功！'), 'success');
	echo '<meta http-equiv="refresh" content="0; url=' . $RootPath .
                    '/QuoteRequestModify2.php?New=Yes&Updateorder_number='. $_POST['order_number'] . '" />';
 
    exit;
 
 
}

if (isset($_POST['Edit'])) {
    if ($_POST["quantity"] > $_SESSION['Contract' . $identifier]->LineItems[$_POST['LineNumber']]->
        quantity_shiped) {
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

 
if (isset($_GET['quote_line_id'])   ) {
   
	$sql2 = "select count(*) as count from so_delivery_all   where  so_order_number= '" . $_GET['so_order_number'] . "' 
	   and so_line_no  = '" . $_GET['so_line_no'] . "'  ";
	  $result = DB_query($sql2,$db);
	  $row=DB_fetch_array($result);
	 if($row['count']>0){
		 
		  $_GET['New']='Yes';
		  $_GET['Updateorder_number']= $_GET['so_order_number'];
          prnMsg(_('订单行已出货记录,不能删除'), 'error');
		  $_GET['New']='Yes';
		  $_GET['Updateorder_number']= $_GET['so_order_number'];
	 } else {

       $sql = "delete from quote_lines_all   where  quote_line_id= '" . $_GET['quote_line_id'] . "' ";
	   $result = DB_query($sql,$db);

	  $sql3 = "select sum(line_amount) sum_amount from quote_lines_all where order_number= '" . $_GET['so_order_number'] . "' ";
	  // echo $sql3;
	   $result3 = DB_query($sql3,$db);
	   while ($row=DB_fetch_array($result3)) {
	     $sum_amount=$row['sum_amount'];
	   }
	    $sql4 = "select youhui_amount from quote_headers_all where order_number= '" . $_GET['so_order_number'] . "' ";
	   $result4 = DB_query($sql4,$db);
	   while ($row4=DB_fetch_array($result4)) {
	     $youhui_amount=$row4['youhui_amount'];
	   }
       $order_payment_amount=$sum_amount-$youhui_amount;
       $time= time();
	   $deletesql1 = "update quote_headers_all 
		              set order_all_amount=". $sum_amount." ,
					  status='开始',
					  order_payment_amount=". $order_payment_amount."   ,
					  order_invoice_amount=". $order_payment_amount." , 
					  last_update_date='" . $time . "',
						  last_updated_by='" . $_SESSION['UserID'] . "' 
                       where order_number='" . $_GET['so_order_number'] . "'";
					  // echo $deletesql1;
        $Resultdelete1 = DB_query($deletesql1, $db);


    
	 $_GET['New']='Yes';
	 $_GET['Updateorder_number']= $_GET['so_order_number'];
    prnMsg(_('删除成功！'), 'success');
	echo '<meta http-equiv="refresh" content="0; url=' . $RootPath .
                    '/QuoteRequestModify2.php?New=Yes&Updateorder_number='. $_GET['so_order_number'] . '" />';
    //DB_Txn_Commit($db);
   
	 }
}

if (isset($_POST['Submit'])) {

    //var_dump($_POST['order_number']);

    isset($_SESSION['num' . $identifier]) or die("no session");
    if ($_SESSION['num' . $identifier] == 400) {
        $_SESSION['num' . $identifier] = 500;
        DB_Txn_Begin($db);
        $InputError = 0;
		  $time = time(); 
        $sumamount = 0.00;
        if ($InputError == 0) {
          $count=null;
          $sumamount = null;
          foreach ($_POST as $key => $value){
          
        //$receipt_line_id =mb_substr($key,15);
		
           if (mb_substr($key,0,13)=='quote_line_id') {
              $quote_line_id =mb_substr($key,13);
			  $i = $_POST[$key];   
			//  echo $quote_line_id ;
               //var_dump($i);
			  if ( $quote_line_id>0 ) {
              //var_dump( $_POST['amount'.$line]);    
              $count = $count + 1;
              $linesql = "UPDATE quote_lines_all " . " 
			  set quantity=  " . $_POST['quantity'.$i] . ",
                                price  ='" . $_POST['price'.$i]  . "',
                                line_amount ='" . $_POST['line_amount'.$i]  . "',
                                item_no ='" . $_POST['item_no'.$i]  . "',
                                item_name ='" . $_POST['item_name'.$i]  . "',
                                item_desc ='" . $_POST['item_desc'.$i]  . "',
                                need_remark ='" . $_POST['need_remark'.$i]  . "',
                                need_date ='" . strtotime($_POST['need_date'.$i])  . "',
                                last_update_date ='" . $time . "',
                                last_updated_by= '" . $_SESSION['UserID'] . "'
                        where quote_line_id='" . $quote_line_id . "'  ";
				//echo $linesql;
              $Result = DB_query($linesql, $db); 
			  }
              
           }
        }
             

            if ($count > 0) {
                //var_dump($sumamount);
          
 
		$deletesql1 = "update quote_headers_all 
		              set  status='开始',
					  order_all_amount=".$_POST['order_all_amount']." ,
	                      order_payment_amount=".$_POST['order_payment_amount']." ,
	                      youhui_amount=".$_POST['youhui_amount']." ,
	                      order_invoice_amount=".$_POST['order_invoice_amount']." ,
						  
						  last_update_date='" . $time . "',
						  last_updated_by='" . $_SESSION['UserID'] . "'
                       where order_number='" . $_POST['order_number']. "'"; 

                $ErrMsg = _('CRITICAL ERROR') . '! ' . _('header_remark DOWN THIS ERROR AND SEEK ASSISTANCE') .
                    ': ' . _('数据存入数据库，原因是：'); //'" . FormatDateForSQL($_SESSION['Contract'.$identifier]->DispatchDate) . "',
                $DbgMsg = _('The following SQL to insert the request header record was used');
                $Result = DB_query($deletesql1, $db, $ErrMsg, $DbgMsg, true);
                DB_Txn_Commit($db);
                $msg = '报价单修改成功！1秒后将跳转回主页！';
                prnMsg($msg, 'success');
               echo '<meta http-equiv="refresh" content="0; url=' . $RootPath .
                    '/QuoteRequestModify2.php?New=Yes&Updateorder_number='. $_POST['order_number'] . '" />';
                echo '<br />';
                echo '<br /><div class="centre"><a href="' . $RootPath . '/QuoteRequestModify.php">' . _('重新选择报价单') .
                    '</a></div>';
                unset($_SESSION['Contract' . $identifier]);
            } else {
                prnMsg(_('报价单行无数据，如果确定全部取消请选择：全部取消'), 'error');
                unset($_SESSION['Contract' . $identifier]);
            }
            DB_Txn_Commit($db);
        }
        include ('includes/footer.inc');
        exit;
    } else {
        header('Location: SelectTransferRequest.php');
    }
} elseif (isset($_POST['Cancel'])) {
    $order_number = $_SESSION['Contract' . $identifier]->order_number;
    $HeaderSQL = "update quote_headers_all   
                          set status= '取消', last_update_date='" . $v_date . "',
						  last_updated_by='" . $_SESSION['UserID'] . "'
		        where order_number='" . $order_number . "'";
    $ErrMsg = _('CRITICAL ERROR') . '! ' . _('header_remark DOWN THIS ERROR AND SEEK ASSISTANCE') .
        ': ' . _('数据存入数据库，原因是：'); //'" . FormatDateForSQL($_SESSION['Contract'.$identifier]->DispatchDate) . "',
    $DbgMsg = _('The following SQL to insert the request header record was used');
    $Result = DB_query($HeaderSQL, $db, $ErrMsg, $DbgMsg, true);
    $HeaderSQL2 = "update quote_lines_all 
                          set quantity=0, last_update_date='" . $v_date . "',
						  last_updated_by='" . $_SESSION['UserID'] . "'
		        where order_number='" . $order_number . "'";
    $ErrMsg = _('CRITICAL ERROR') . '! ' . _('header_remark DOWN THIS ERROR AND SEEK ASSISTANCE') .
        ': ' . _('数据存入数据库，原因是：'); //'" . FormatDateForSQL($_SESSION['Contract'.$identifier]->DispatchDate) . "',
    $DbgMsg = _('The following SQL to insert the request header record was used');
    $Result2 = DB_query($HeaderSQL2, $db, $ErrMsg, $DbgMsg, true);

    $msg = '报价单取消成功！1秒后将跳转回上一页！';
    prnMsg($msg, 'success');
    echo '<meta http-equiv="refresh" content="1; url=' . $RootPath .
        '/QuoteRequestModify.php" />';
    echo '<br />';
    include ('includes/footer.inc');
    exit;
} else {
    session_start() or die("session is not started");
    $_SESSION['num' . $identifier] = 400;
}
echo '	<div class="centre">
		<a href="' . $RootPath . '/QuoteRequestModify.php">返回重新选择报价单</a>
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
 

if ($_SESSION['Contract' . $identifier]->status == '开始') {
    $v_status = '开始';
} elseif ($_SESSION['Contract' . $identifier]->status == 'APPROVED') {
    $v_status = '已签核';
} elseif ($_SESSION['Contract' . $identifier]->status == 'REJECTED') {
    $v_status = '已拒签';
} else {
    $v_status = '已取消';
}
$v_need_date = date('Y-m-d', $_SESSION['Contract' . $identifier]->need_date);
$v_create_date = date('Y-m-d', $_SESSION['Contract' . $identifier]->create_date);
echo '<tr class="EvenTableRows">
		<td >' . _('报价单') . ':</td> <td >' . $_SESSION['Contract' . $identifier]->order_number . ' <input type="hidden" class="text"  name="order_number" value="' . $_SESSION['Contract' . $identifier]->order_number . '" /> </td>
	<td>创建日期:</td>' . '<td>' . $v_create_date . '</td>
	<td>状态：</td>' . '<td>' . $v_status . '</td></tr>';

echo '<tr class="OddTableRows">' . 
    ' <td >需求日期:</td>
      <td><input type="text" onfocus="WdatePicker()"  name="need_date" maxlength="10" size="11" value="' .
    $v_need_date . '" /></td>
      
	 <td  >' . _('客户') . ':</td>
      <td colspan="5">' . $_SESSION['Contract' . $identifier]->customer_name . '</td> ' . '</tr>';
 

echo '<tr class="EvenTableRows">
<td >' . _('订单总金额') . ':</td>
			<td  > <input type="text" readonly="readonly" class="number" size="11" name="order_all_amount" id="order_all_amount" value="' . $_SESSION['Contract' . $identifier]->order_all_amount . '" /> </td>
	 
			<td >' . _('订单应付金额') . ':</td>
			<td  > <input type="text" readonly="readonly" class="number" size="11" name="order_payment_amount" id="order_payment_amount" value="' . $_SESSION['Contract' . $identifier]->order_payment_amount . '" /> </td>
			<td >' . _('优惠金额') . ':</td>
			<td  > <input type="text" class="number" size="11" name="youhui_amount" id="youhui_amount"  onkeyup="check_amount1()" value="' . $_SESSION['Contract' . $identifier]->youhui_amount . '" /> </td>
			
			'; 

echo ' 
            <td >' . _('订单应开票金额') . ':</td>
			<td  > <input type="text" class="number"  onkeyup="check_kaipiao()"  size="11" name="order_invoice_amount" id="order_invoice_amount" value="' . $_SESSION['Contract' . $identifier]->order_invoice_amount . '" /> </td>
                      
           
            </tr> ';
 
 echo '<tr class="EvenTableRows">
 <td >' . _('业务员:') . '</td>
            <td>
			<select name="yewu" id="">'; 
				 
					$sql = "select employee_num,employee_name from hr_employees order by employee_num";
					$result = DB_query($sql,$db);
					while ($v = DB_fetch_array($result)) {
						if ($v['employee_num']==$_SESSION['Contract' . $identifier]->yewu ) {
				 
				echo '<option value="'.$v['employee_num'].'" selected="selected">'.$v['employee_num'].$v['employee_name'].'</option>';
				  }else{ 
				echo '<option value="'.$v['employee_num'].'">'.$v['employee_num'].$v['employee_name'].'</option>';
				 		}
					}
				 
			echo '</select>
            </td>
 
				
					
			
			'; 

echo '  <td >' . _('付款条件') . ':</td>
		<td  > <input type="text"   size="10"  maxlength="100" name="term_name"  value="' . $_SESSION['Contract' . $identifier]->term_name . '" /> </td>
            
			  
			 <td >' . _('下单抬头') . '</td>
            <td>
			<select name="coycode" id="">'; 
				 
					$sql = "select coyname_code from companies2 order by coycode";
					$result = DB_query($sql,$db);
					while ($v = DB_fetch_array($result)) {
						if ($v['coyname_code']==$_SESSION['Contract' . $identifier]->coycode ) {
				 
				echo '<option value="'.$v['coyname_code'].'" selected="selected">'.$v['coyname_code'].'</option>';
				  }else{ 
				echo '<option value="'.$v['coyname_code'].'">'.$v['coyname_code'].'</option>';
				 		}
					}
				 
			echo '</select>
            </td>
			   <td >' . _('税别') . ':</td>
            <td>
			<select name="tax_name" id="">'; 
				 
					$sql = "select tax_name from tax_set order by tax_id";
					$result = DB_query($sql,$db);
					while ($v = DB_fetch_array($result)) {
						if ($v['tax_name']==$_SESSION['Contract' . $identifier]->tax_name ) {
				 
				echo '<option value="'.$v['tax_name'].'" selected="selected">'.$v['tax_name'].'</option>';
				  }else{ 
				echo '<option value="'.$v['tax_name'].'">'.$v['tax_name'].'</option>';
				 		}
					}
				 
			echo '</select>
            </td>
			 
            
            </tr> ';
 
 
echo '  <tr class="EvenTableRows">
 <td >' . _('运费') . ':</td>
		<td  > <input type="text"   size="10"  maxlength="10" name="yunfei"  value="' . $_SESSION['Contract' . $identifier]->yunfei . '" /> </td>
            ';

echo '  
              <td  >' . _('备注') . ':</td>
              <td colspan="6"> <input type="text"  name="header_remark"   maxlength="100" size="80"  value="'. $_SESSION['Contract' .
    $identifier]->header_remark . '"/></td>
        </tr>';
 

echo '  <tr class="EvenTableRows">
              <td  >' . _('审核备注') . ':</td>
              <td colspan="5">' . $_SESSION['Contract' . $identifier]->approve_remark . '</td>
			  <td  >' . _('上传新附件') . ':</td>
			  <td><a href="' . $RootPath . '/SOUploadNewfile.php?OrderNum=' . $_SESSION['Contract' . $identifier]->order_number . '"target="_blank">上传 </td>

			<td>  <input type="hidden" name="order_all_amount_old" id="order_all_amount_old" value="'. $_SESSION['Contract'.$identifier]->order_all_amount . '"/>
			<input type="hidden" name="order_payment_amount_old" id="order_all_amount_old" value="'. $_SESSION['Contract'.$identifier]->order_payment_amount . '"/>
			<input type="hidden" name="youhui_amount_old" id="order_all_amount_old" value="'. $_SESSION['Contract'.$identifier]->youhui_amount . '"/>
			<input type="hidden" name="order_invoice_amount_old" id="order_all_amount_old" value="'. $_SESSION['Contract'.$identifier]->order_invoice_amount . '"/></td>
        </tr>';
    
echo '</table>';

echo '<div class="centre">
	<input type="submit" name="Update" value="' . _('报价单头修改保存') . '" />
	</div>
    </div>
	</form>';

if (!isset($_SESSION['Contract' . $identifier]->order_number)) {
    include ('includes/footer.inc');
    exit;
}

$i = 0; //Line Item Array pointer
echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES,
    'UTF-8') . '" method="post"><input type="hidden" name = "identifier" value ="' .
    $identifier . '">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
//用隐藏域保存报价单表号
echo ' <input type="hidden" class="text"  name="order_number" value="' . $_SESSION['Contract' . $identifier]->order_number . '" />';
echo ' <input type="hidden" class="text"  name="youhui_amount" id="youhui_amount2" value="' . $_SESSION['Contract' . $identifier]->youhui_amount . '" />';
echo ' <input type="hidden" class="text"  name="order_all_amount" id="order_all_amount2" value="' . $_SESSION['Contract' . $identifier]->order_all_amount . '" />';
echo ' <input type="hidden" class="text"  name="order_payment_amount" id="order_payment_amount2"  value="' . $_SESSION['Contract' . $identifier]->order_payment_amount . '" />';
echo ' <input type="hidden" class="text"  name="order_invoice_amount" id="order_invoice_amount2" value="' . $_SESSION['Contract' . $identifier]->order_invoice_amount . '" />';
echo '<br /> ';
  

echo '<table class="selection">
	<tr>
		<th>' . _('行') . '</th>           
                <th width="130" bgcolor="#87CEFA">料号</th>
                <th width="130" bgcolor="#87CEFA">料号名称</th>
                <th width="130" bgcolor="#87CEFA">规格型号</th>
					<th>单位</th>
					<th width="100">需求日期</th>
					<th width="140">要求</th>
					 
                   	<th bgcolor="#87CEFA">需求数量</th>	 
				    <th bgcolor="#87CEFA">单价 </th>
				    <th>金额</th>	  
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

	 echo ' <td>' . $myrow['line'] . '</td> '; 
		 echo ' <td><input style="background-color:yellow" type="text"  name="item_no'.$i.'"  size="12" maxlength="50"  value="' . $myrow['item_no']  . '" />'; 
		 echo ' <td><input style="background-color:yellow" type="text"  name="item_name'.$i.'"  size="25" maxlength="200"  value="' . $myrow['item_name']  . '" />'; 
		 echo ' <td><input style="background-color:yellow" type="text"  name="item_desc'.$i.'"  size="25" maxlength="200"  value="' . $myrow['item_desc']  . '" />'; 
		 echo ' <td><input style="background-color:yellow" type="text"  name="uom'.$i.'"  size="3"  value="' . $myrow['uom']  . '" />'; 
	   echo ' <td><input style="background-color:yellow" type="text"  name="need_date'.$i.'"  size="9"  value="' . date('Y-m-d',$myrow['need_date'])  . '" onfocus="WdatePicker() " />';
     echo ' <td><input style="background-color:yellow" type="text"  name="need_remark'.$i.'"  size="15"  value="' . $myrow['need_remark']  . '" />';
	 
       echo ' <td><input id="quantity' .$i.'" onblur="checkall()" onkeyup="webdesign(' .$i.')"   style="background-color:yellow" type="text"  name="quantity'.$i.'" class="number" size="8"  value="' . $myrow['quantity']  . '" /></td> ';
	   echo ' <td><input id="price' .$i.'" onblur="checkall()"  onkeyup="webdesign(' .$i.')" style="background-color:yellow" type="text"  name="price'.$i.'" class="number" size="8"  value="' . $myrow['price']  . '" /></td> ';
	   echo ' <td><input id="line_amount' .$i.'"  style="background-color:yellow" type="text"  name="line_amount'.$i.'" class="number" size="8"  value="' . $myrow['line_amount']  . '" /></td>';
	  
		echo '<td><a href="' . $RootPath . '/QuoteRequestModify2.php?so_order_number='.$_SESSION['Contract' . $identifier]->order_number .'&quote_line_id=' .$myrow['quote_line_id'] .'&so_line_no=' .$myrow['line'] .'"  >删除</td>
	   <input type="hidden" name="quote_line_id'.$myrow['quote_line_id'].'" value="'.$i.'" /></td> ';
    

       echo ' 
     </tr>';            
      $i++;
	echo ' </tr>';

}
echo '</table>
   
	
	<div class="centre">
                <input type="submit" id="submit" name="Submit" value="' . _('报价单行修改保存') .
    '" />   
	</div>
       
    </div>
    </form>';
 
//*********************************************************************************************************
 
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>新建报价单</title>
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
                      var youhui_amount=document.getElementById("youhui_amount").value;
					  var order_all_amount_old=document.getElementById("order_all_amount_old").value; 
                                
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
        var  youhui_amount2=  document.getElementById("youhui_amount").value;
		var  order_all_amount= Number(order_all_amount_old) + Number(allamount);		
		var  order_payment_amount= Number(order_all_amount_old) + Number(allamount) - Number(youhui_amount);			
		document.getElementById("order_all_amount").value= Math.round(Number(order_all_amount)*100)/100;
       document.getElementById("order_payment_amount").value=Math.round(Number(order_payment_amount)*100)/100;
       document.getElementById("order_invoice_amount").value=Math.round(Number(order_payment_amount)*100)/100;
	   document.getElementById("youhui_amount_new").value=youhui_amount2 ;
	   document.getElementById("order_all_amount_new").value= Math.round(Number(order_all_amount)*100)/100;
       document.getElementById("order_payment_amount_new").value=Math.round(Number(order_payment_amount)*100)/100;
       document.getElementById("order_invoice_amount_new").value=Math.round(Number(order_payment_amount)*100)/100;
        var a=document.getElementById("order_all_amount").value;
        var b=document.getElementById("youhui_amount").value;  
      if(parseFloat(b)>parseFloat(a)){
            document.getElementById("Prompt").innerHTML="优惠金额"+b+"超过总金额啦！"+a;
            document.getElementById("youhui_amount").value="";
            document.getElementById("youhui_amount").focus();
        } 
        else {
            document.getElementById("Prompt").innerHTML="";
        }

  }


 function checkall(){                               
                      var allamount=0; 
                      var youhui_amount=document.getElementById("youhui_amount").value;
                                
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
            var  youhui_amount2=  document.getElementById("youhui_amount").value;
		order_payment_amount=Number(allamount) - Number(youhui_amount);			
		document.getElementById("order_all_amount").value= Math.round(Number(allamount)*100)/100;
       document.getElementById("order_payment_amount").value=Math.round(Number(order_payment_amount)*100)/100;
       document.getElementById("order_invoice_amount").value=Math.round(Number(order_payment_amount)*100)/100;
	   document.getElementById("youhui_amount2").value=youhui_amount2 ;
	   document.getElementById("order_all_amount2").value= Math.round(Number(allamount)*100)/100;
       document.getElementById("order_payment_amount2").value=Math.round(Number(order_payment_amount)*100)/100;
       document.getElementById("order_invoice_amount2").value=Math.round(Number(order_payment_amount)*100)/100;
        var a=document.getElementById("order_all_amount").value;
        var b=document.getElementById("youhui_amount").value;  
      if(parseFloat(b)>parseFloat(a)){
            document.getElementById("Prompt").innerHTML="优惠金额"+b+"超过总金额啦！"+a;
            document.getElementById("youhui_amount").value="";
            document.getElementById("youhui_amount").focus();
        } 
        else {
            document.getElementById("Prompt").innerHTML="";
        }

  }

 function webdesign(s1)
{
var a=document.getElementById("quantity"+s1).value;
var b=document.getElementById("price"+s1).value;
      if( parseFloat(a)<0){
            document.getElementById("Prompt").innerHTML="数量不可以小于0！"+a;
            document.getElementById("quantity"+s1).value=0;
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
	check_kaipiao

function  check_kaipiao(){                               
         
          var order_invoice_amount=document.getElementById("order_invoice_amount").value;                 
		document.getElementById("order_invoice_amount_new").value=order_invoice_amount;
        document.getElementById("order_invoice_amount_old").value=order_invoice_amount;      
      
       } 

 function  check_amount1(){                               
                                var allamount=0; 
                                 var order_all_amount=document.getElementById("order_all_amount").value;
                                 var youhui_amount=document.getElementById("youhui_amount").value;
                                 
                                var po_yingfu_amount=order_all_amount-youhui_amount;
                                 
								
        document.getElementById("order_payment_amount").value=Math.round(Number(po_yingfu_amount)*100)/100;
        document.getElementById("order_invoice_amount").value=Math.round(Number(po_yingfu_amount)*100)/100;
		document.getElementById("order_payment_amount_new").value=Math.round(Number(po_yingfu_amount)*100)/100;
        document.getElementById("order_invoice_amount_new").value=Math.round(Number(po_yingfu_amount)*100)/100;
        
        var a=document.getElementById("order_all_amount").value;
        var b=document.getElementById("youhui_amount").value;  
      if(parseFloat(b)>parseFloat(a)){
            document.getElementById("Prompt").innerHTML="优惠金额"+b+"不可以大于总金额！"+a;
            document.getElementById("youhui_amount").value=0;
            document.getElementById("youhui_amount").focus();
            document.getElementById("order_payment_amount").value=order_all_amount;
            document.getElementById("order_invoice_amount").value=order_all_amount;
            document.getElementById("order_payment_amount_new").value=order_all_amount;
            document.getElementById("order_invoice_amount_new").value=order_all_amount;
        } 
        else {
            document.getElementById("Prompt").innerHTML="";
			document.getElementById("youhui_amount2").value=b;
			document.getElementById("youhui_amount_new").value=b;
        }
      
       } 
                             
          function  check_amount(){     
        var a=document.getElementById("order_all_amount").value;
        var b=document.getElementById("header_amount").value;
        var c=document.getElementById("youhui_amount").value;  
		var d=a-c;
      if (parseFloat(c)>parseFloat(a)){
            document.getElementById("Prompt").innerHTML="优惠金额"+c+"不可以大于总金额！"+a;
            document.getElementById("youhui_amount").value="";
           document.getElementById("youhui_amount").focus();
           
        }   else {
            document.getElementById("Prompt").innerHTML="";
			
		document.getElementById("header_amount").value=Math.round(Number(d)*100)/100;
        }       
        
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
<body>

<div id="CanvasDiv">
	<div id="BodyDiv">
		<div id="BodyWrapDiv">
			<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="新增报价单行" alt="新增报价单行">新增报价单行</p>
			<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST"><input type="hidden" name="time" value="<?=$time?>">
				<div>
				
				 <input readonly="readonly" type="hidden"   name="order_number"  value="<?=$_SESSION['Contract' . $identifier]->order_number?>" size="55" maxlength="46"/> 
				 <td>  <input type="hidden" name="order_all_amount_new" id="order_all_amount_new" value="<?=$_SESSION['Contract' . $identifier]->order_all_amount?>"/>
			<input type="hidden" name="order_payment_amount_new" id="order_payment_amount_new" value="<?=$_SESSION['Contract' . $identifier]->order_payment_amount?>"/>
			<input type="hidden" name="youhui_amount_new" id="youhui_amount_new" value="<?=$_SESSION['Contract' . $identifier]->youhui_amount?>"/>
			<input type="hidden" name="order_invoice_amount_new" id="order_invoice_amount_new" value="<?=$_SESSION['Contract' . $identifier]->order_invoice_amount?>"/></td>
					<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
					 
					 
					<input type="hidden" name="PageOffset" value="1"/><br/>
					<?php
					if (isset($_SESSION['Contract' . $identifier]->customer_name) and $_SESSION['Contract' . $identifier]->customer_name != '') {
						?>
						<input id="purchase_table_lastRow" name="purchase_table_lastRow" type=hidden value="">

						<div class="centre"> 
                        <p id="Prompt" style="color: red;font-size: 20px"></p>
                    </div>

						<table id="purchase_table" cellpadding="2" class="selection">
						   <tr id="list-top">
						   <th width="230" bgcolor="#87CEFA">料号 </th>
					<th width="150" bgcolor="#87CEFA">产品名称</th>
					<th width="150" bgcolor="#87CEFA">规格型号</th>
					     <th>单位</th>
					     <th width="100">需求日期</th>
					    <th width="150">要求</th>					 
                   	    <th bgcolor="#87CEFA">需求数量</th>	 
				       <th bgcolor="#87CEFA">单价 </th>
				       <th>金额</th>
					    <th width="50" align="center">操作</th>
							</tr>
					<?php for($j=1;$j<=50;$j++){
					$_POST['UOM'.$j]='个';
					?>
            
			<tr id="purchase_table_<?=$j?>" <?php echo $j>3&&$_POST['item_name'.$j]==''?'style="display:none"':''?> class="mouse click">
           <td><input readonly="readonly" type="text" name="stockid<?=$j?>" id="text_slect_buliao<?=$j?>" value="<?=$_POST['stockid'.$j]?>" size="20" maxlength="240"/> <a class="btn btn-info btn-xs" id="btn_slect_buliao<?=$j?>" hfre="###" title="选择产品">选</a> </td>  
					<td><input  type="text" name="item_name<?=$j?>" id="text_slect_item_name<?=$j?>" value="<?=$_POST['item_name'.$j]?>" size="20" maxlength="240"/>  </td>    
					<td><input   type="text" name="item_desc<?=$j?>" id="text_slect_item_spec<?=$j?>" value="<?=$_POST['item_desc'.$j]?>" size="20" maxlength="240"/>  </td>    
			  <td><select name="UOM<?=$j?>" id="text_slect_uom<?=$j?>">
							<?php
								$sql = "select unitname from unitsofmeasure order by unitid";
								$result = DB_query($sql,$db);
								while ($v = DB_fetch_array($result)) {
									if ($v['unitname']==$_POST['UOM<?=$j?>']) {
							?>
								<option value="<?=$v['unitname']?>" selected="selected"><?=$v['unitname']?></option>
							<?php }else{?>
							<option value="<?=$v['unitname']?>"><?=$v['unitname']?></option>
							<?php 
							}
								}
							?>
						</select>
					</td>
				 <td><input  style="background-color:#D2E9FF;" type="text" name="need_date<?=$j?>"  value="<?=$_POST['need_date'.$j]?>" size="10" maxlength="100"  onfocus="WdatePicker() " /> </td>
				 <td ><input type="text" name="need_remark<?=$j?>" id="text_slect_item_spec<?=$j?>" value="<?=$_POST['need_remark'.$j]?>" size="20" maxlength="150"/></td>								
				  
				  <td><input type="text" style="background-color:#D2E9FF;" class="number" id="add_quantity<?=$j?>"   name="quantity<?=$j?>" step="1"  min="0" onkeyup="this.value= this.value.match(/\d+(\.\d{0,2})?/) ? this.value.match(/\d+(\.\d{0,2})?/)[0] : ''"  value="<?=$_POST['quantity'.$j]?>" size="5" maxlength="10" onblur="checkaddall()"  /><span style="color:red">*</span></td>

				  <td><input type="text" style="background-color:#D2E9FF;" class="number" id="text_slect_unit_price<?=$j?>" onblur="checkaddall()"  step="1"  min="0" onkeyup="this.value= this.value.match(/\d+(\.\d{0,2})?/) ? this.value.match(/\d+(\.\d{0,2})?/)[0] : ''"   name="unitprice<?=$j?>" value="<?=$_POST['unitprice'.$j]?>" size="5" maxlength="10" /><span style="color:red">*</span></td>

				 
									
				<td><input type="text" readonly="readonly" id="lineamount<?=$j?>" class="number"  onkeyup="check(<?=$j?>)"  name="line_amount<?=$j?>" value="<?=$_POST['line_amount'.$j]?>" size="10" maxlength="10" /></td>
                <td>  <a onclick="delettr($(this));" style="padding:0px 5px;" href="javascript:;">删除</a></td>

									<td><input  type="hidden" name="Subinventory_name<?=$j?>" id="text_slect_locationname<?=$j?>" value="<?=$_POST['Subinventory_name'.$j]?>" size="8" maxlength="25"/>
 <input  type="hidden" name="zhidao_price<?=$j?>" id="text_slect_zhidao_price<?=$j?>" value="<?=$_POST['zhidao_price'.$j]?>" size="8" maxlength="25"/>

								</tr>
							<?php }?>

						</table>
                       
						<div class="centre">
							<a onclick="addsave();">添加行</a>

						</div>

						<div class="centre">
							<input type="submit" name="Save" value="提交">
						</div>
						<?php
					}
					?>
					<input type="hidden" name="idcount" id='idcount' value="11"/>
					<input type="hidden" name="JustSelectedACustomer" value="Yes"/>
				</div>
			</form>
		</div>
	</div>

	<div id="FooterDiv">
		<div id="FooterWrapDiv">

		</div>
	</div>
</div>
<script type="text/javascript">
 	
     

	function addsave()
		{

			var v = $('#idcount').val();
			$("#purchase_table_"+v).css("display","");
			var c = parseFloat(v) + 1;
			$('#idcount').val(c);
		}

	$(document).ready(function(){

		$('.divToilet table tr td a').click(function(){
			$(this).parent('td').toggleClass('highlight');
			if(!($(this).parent('td').hasClass('highlight'))) {
				$(this).next().val('0');
			}else {
				$(this).next().val('1');
			}
		});
		<?php for($j=1;$j<=50;$j++){?>
		$('#btn_slect_buliao<?=$j?>').dialog({
			title:'选择料号',
			width: '1100px',
			height: 470,
			content:'url:Searchbuliao2.php?fwValue=<?=$j?>&cat=buliao',
			init:function(){
				this.content.document.getElementById('cat').value = 'buliao';
				this.content.document.getElementById('fwValue').value = '<?=$j?>';
			}
		});
		<?php }?>


		$('#btn_slect_vendor').dialog({
			title:'选择供应商',
			width: '950px',
			height: 470,
			content:'url:BtnSearchVendor.php?fwValue=&cat=buliao',
			init:function(){
				this.content.document.getElementById('cat').value = 'buliao';
				this.content.document.getElementById('fwValue').value = '';
			}
		});
		
		//Function to get URL arguments

		function getRequest() {
			var url = location.search; //获取url中"?"符后的字串
			var theRequest = new Object();
			if (url.indexOf("?") != -1) {
				var str = url.substr(1);
				strs = str.split("&");
				for(var i = 0; i < strs.length; i ++) {
					theRequest[strs[i].split("=")[0]]=(strs[i].split("=")[1]);
				}
			}
			return theRequest;
		}


	});
	function  check(s1){
		var shuliang=document.getElementById("add_quantity"+s1).value;
		var danjia=document.getElementById("text_slect_unit_price"+s1).value;
		if(shuliang==""){
			shuliang=0;
		}
		if(danjia==""){
			danjia=0;
		}
		document.getElementById("lineamount"+s1).value=Math.round(Number(Number(shuliang)* Number(danjia)) *100)/100;

	}

  
	        
   
</script>
</body>
</html>
<?php
include ('includes/footer.inc');
?>