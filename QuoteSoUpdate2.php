<?php

/*
* To change this license header, choose License Headers in Project Properties.
* To change this template file, choose Tools | Templates
* and open the template in the editor.
*/
include ('includes/DefinePOUpdateClass.php');
include ('includes/session.inc');
$Title = _('业务订单修改');
$ViewTopic = '业务订单修改';
$BookMark = '业务订单修改';
include ('includes/header.inc');
include ('includes/SQL_CommonFunctions.inc');
    



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
			if (substr($key, 0,7)=='stockid') {
				$errorflag = 0;
				$i = substr($key, 7);
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
				if (substr($key, 0,7)=='stockid') {
					$i = substr($key, 7);

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

		$sql_num = "select 	max(line) line from so_lines_all where  order_number  = '" . $_POST['order_number']. "'";
		$result_num = DB_query($sql_num, $db);
		$rownum = DB_num_rows($result_num);
		while ($v = DB_fetch_array($result_num)) {
			$line =  $v['line'];
		}

		foreach ($_POST as $key => $value) {
			if ($value != '') {
				if (substr($key, 0,7)=='stockid') {
					$i = substr($key, 7);
					$lineamount[$i] =$_POST['quantity'.$i] * $_POST['unitprice'.$i] ;
					if($_POST['zhidao_price'.$i]==''){
						$_POST['zhidao_price'.$i] = '0';
					}
					$line=$line+1;

					$sql = "insert into so_lines_all(order_number,line,line_remark,subinventory_code,uom,zhidao_price,price,quantity,stockid,customer_item,line_amount,need_date,
						creation_date,created_by,last_update_date,last_updated_by)
						values('".$_POST['order_number']."','".$line."','".$_POST['remark'.$i]."',
						'".$_POST['subinventory_code'.$i]."','".$_POST['UOM'.$i]."','".$_POST['zhidao_price'.$i]."','".$_POST['unitprice'.$i]."',
						'".$_POST['quantity'.$i]."','".$_POST['stockid'.$i]."','".$_POST['customer_item'.$i]."','".$lineamount[$i]."',
						'".strtotime($_POST['need_date'.$i])."','".$time."','".$_SESSION['UserID']."','".$time."','".$_SESSION['UserID']."') ";

			//echo $sql;

					$result = DB_query($sql,$db);

                    $sql = "insert into so_lines_all_log(change_type,order_number,line,line_remark,subinventory_code,uom,zhidao_price,price,quantity,stockid,customer_item,line_amount,need_date,
						creation_date,created_by,last_update_date,last_updated_by)
						select '修改',order_number,line,line_remark,subinventory_code,uom,zhidao_price,price,quantity,stockid,customer_item,line_amount,need_date,
						creation_date,created_by,last_update_date,last_updated_by from so_lines_all
                     where  order_number  = '" . $_POST['order_number']. "'";
                    $result = DB_query($sql,$db);


					$order_amount = $order_amount + $lineamount[$i];
				}
			}
		}

	   $status='待签核';

		$deletesql1 = "update so_headers_all
		              set order_all_amount=". $_POST['order_all_amount_new']." ,
					   	all_line_amount=". $_POST['all_line_amount_new']." , 
					  youhui_amount=". $_POST['youhui_amount_new']." , 
					  last_update_date='" . $time . "',
					  tax_amount=". $_POST['tax_amount']." , 
					 last_updated_by='" . $_SESSION['UserID'] . "',
					 last_update_date='" . $time . "',
					  status='" . $status . "'
                       where order_number='" . $_POST['order_number'] . "'";
					  // echo $deletesql1;
        $Resultdelete1 = DB_query($deletesql1, $db);

        $sql = "insert into so_headers_all_log
(change_type,order_type,all_line_amount,term_name,yewu,delivery_date,schedule_recevie_date,delivery_type,yunfei_amount,tax_amount,ship_address,ship_city, youhui_amount,
order_number,customer_code,customer_contact,need_date,qianding_date,status,currency_code,customer_order_number,tax_name,tax_rate,tax_flag,project,jiaohuotiaojian,youxiaoxing1,
youxiaoxing2,baozhuang,zhiliangbaozheng,mainfeifuwu,order_all_amount,creation_date,header_remark,created_by,last_update_date,last_updated_by)select '修改',order_type,all_line_amount,term_name,yewu,delivery_date,schedule_recevie_date,delivery_type,yunfei_amount,tax_amount,ship_address,ship_city, youhui_amount,
order_number,customer_code,customer_contact,need_date,qianding_date,status,currency_code,customer_order_number,tax_name,tax_rate,tax_flag,project,jiaohuotiaojian,youxiaoxing1,
youxiaoxing2,baozhuang,zhiliangbaozheng,mainfeifuwu,order_all_amount,creation_date,header_remark,created_by,last_update_date,last_updated_by from so_headers_all 
        where order_number='" . $_POST['order_number'] . "'";
        $result = DB_query($sql,$db);
        
        

		DB_Txn_Commit($db);
		$msg = '业务订单新增行成功！1秒后将跳转回上一页！';
    prnMsg($msg, 'success');
	echo '<meta http-equiv="refresh" content="0; url=' . $RootPath .
                    '/QuoteSoUpdate2.php?New=Yes&Updateorder_number='. $_POST['order_number'] . '" />';
    //echo '<meta http-equiv="refresh" content="2; url=' . $RootPath .'/UpdateSoForApprove.php" />';
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
            $sql = 'SELECT a.customer_order_number,a.order_number, a.status, a.term_name,header_remark, a.qianding_date, a.need_date,a.approve_remark,a.creation_date, a.order_all_amount,a.youhui_amount,b.customer_name, b.customer_code, a.tax_flag,project,jiaohuotiaojian,baozhuang,youxiaoxing1,youxiaoxing2,zhiliangbaozheng,mainfeifuwu,a.yewu,a.currency_code,a.customer_contact,a.tax_name,a.yunfei_amount,a.tax_amount,a.coycode,a.ship_address,a.ship_city,a.all_line_amount,a.tax_rate,a.contract_number
FROM so_headers_all a, customers b
WHERE a.customer_code = b.customer_code
AND order_number=' . "'" . "$order_number" . "'";
            $CustResult = DB_query($sql, $db);
            while ($myrow = DB_fetch_array($CustResult)) {
                $_SESSION['Contract' . $identifier]->order_number = $myrow['order_number'];
                $_SESSION['Contract' . $identifier]->status = $myrow['status'];
                $_SESSION['Contract' . $identifier]->qianding_date = $myrow['qianding_date'];
                $_SESSION['Contract' . $identifier]->need_date = $myrow['need_date'];
				$_SESSION['Contract' . $identifier]->approve_remark = $myrow['approve_remark'];
                $_SESSION['Contract' . $identifier]->create_date = $myrow['creation_date'];
                $_SESSION['Contract' . $identifier]->term_name = $myrow['term_name'];
                $_SESSION['Contract' . $identifier]->contract_number = $myrow['contract_number'];
                $_SESSION['Contract' . $identifier]->header_remark = $myrow['header_remark'];
                $_SESSION['Contract' . $identifier]->customer_name = $myrow['customer_name'];
				$_SESSION['Contract' . $identifier]->customer_code= $myrow['customer_code'];
                $_SESSION['Contract' . $identifier]->amount = $myrow['amount'];
                $_SESSION['Contract' . $identifier]->order_all_amount =  sprintf("%.2f",$myrow['order_all_amount']);
                $_SESSION['Contract' . $identifier]->youhui_amount = sprintf("%.2f",$myrow['youhui_amount']);
                $_SESSION['Contract' . $identifier]->tax_flag = $myrow['tax_flag'];
                $_SESSION['Contract' . $identifier]->project = $myrow['project'];
                $_SESSION['Contract' . $identifier]->jiaohuotiaojian = $myrow['jiaohuotiaojian'];
                $_SESSION['Contract' . $identifier]->baozhuang = $myrow['baozhuang'];
                $_SESSION['Contract' . $identifier]->customer_order_number = $myrow['customer_order_number'];
                $_SESSION['Contract' . $identifier]->youxiaoxing2 = $myrow['youxiaoxing2'];
                $_SESSION['Contract' . $identifier]->zhiliangbaozheng = $myrow['zhiliangbaozheng'];
                $_SESSION['Contract' . $identifier]->mainfeifuwu = $myrow['mainfeifuwu'];
                $_SESSION['Contract' . $identifier]->coycode = $myrow['coycode'];
                $_SESSION['Contract' . $identifier]->tax_amount = sprintf("%.2f",$myrow['tax_amount']);
                $_SESSION['Contract' . $identifier]->yunfei_amount = sprintf("%.2f",$myrow['yunfei_amount']);
                $_SESSION['Contract' . $identifier]->tax_name = $myrow['tax_name'];
                $_SESSION['Contract' . $identifier]->tax_rate= sprintf("%.2f",$myrow['tax_rate']);
                $_SESSION['Contract' . $identifier]->yewu = $myrow['yewu'];
                $_SESSION['Contract' . $identifier]->ship_address = $myrow['ship_address'];
                $_SESSION['Contract' . $identifier]->ship_city = $myrow['ship_city'];
                $_SESSION['Contract' . $identifier]->all_line_amount = sprintf("%.2f",$myrow['all_line_amount']);
            }


            if (!isset($_POST['Update'])) {
                $sql = 'select order_line_id,order_number,a.line,a.stockid,b.item_desc,b.item_name,quantity,price,line_amount,ifnull(quantity_shiped,0) quantity_shiped,a.uom,a.subinventory_code,a.line_remark,a.customer_item,a.need_date
				from so_lines_all a,sf_item_no b where a.stockid=b.item_no and order_number = ' . "'" .$_SESSION['Contract' . $identifier]->order_number . "'
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


  $status='待签核';
   $HeaderSQL = "update so_headers_all
                                 set header_remark= '" . $_POST['header_remark'] . "',
                                     need_date= '" . $need_date . "',
                                     qianding_date= '" . $qianding_date . "',
                                     youhui_amount='" . $_POST['youhui_amount'] . "',
                                     term_name='" . $_POST['term_name'] . "',
									 subject='" . $_POST['subject'] . "',
									 project='" . $_POST['project'] . "',
                                     jiaohuotiaojian='" . $_POST['jiaohuotiaojian'] . "',
                                     baozhuang='" . $_POST['baozhuang'] . "',
                                     customer_order_number='" . $_POST['customer_order_number'] . "',
                                     youxiaoxing2='" . $_POST['youxiaoxing2'] . "',
                                     zhiliangbaozheng='" . $_POST['zhiliangbaozheng'] . "',
                                     mainfeifuwu='" . $_POST['mainfeifuwu'] . "',
                                     yewu='" . $_POST['yewu'] . "',
                                     tax_flag='" . $_POST['tax_flag'] . "',
                                     header_remark='" . $_POST['header_remark'] . "',
                                     ship_address='" . $_POST['ship_address'] . "',
                                     ship_city='" . $_POST['ship_city'] . "',
                                     yunfei_amount='" . $_POST['yunfei_amount'] . "',
                                     tax_amount='" . $_POST['tax_amount'] . "',
                                     coycode='" . $_POST['coycode'] . "',
                                     tax_name='" . $_POST['tax_name'] . "',
                                     order_all_amount='" . $_POST['order_all_amount'] . "',
                                     all_line_amount='" . $_POST['all_line_amount'] . "',
                                     contract_number='" . $_POST['contract_number'] . "',
                                     status='" . $status . "',
                                     last_update_date='" . $v_date . "',
                                     last_updated_by= '" . $_SESSION['UserID'] .
                    "'
		               where order_number='" . $_POST['order_number'] . "'";
     $Resultdelete1 = DB_query($HeaderSQL, $db);

    $sql = "insert into so_headers_all_log
(change_type,order_type,all_line_amount,term_name,yewu,delivery_date,schedule_recevie_date,delivery_type,yunfei_amount,tax_amount,ship_address,ship_city, youhui_amount,
order_number,customer_code,customer_contact,need_date,qianding_date,status,currency_code,customer_order_number,tax_name,tax_rate,tax_flag,project,jiaohuotiaojian,youxiaoxing1,
youxiaoxing2,baozhuang,zhiliangbaozheng,mainfeifuwu,order_all_amount,creation_date,header_remark,created_by,last_update_date,last_updated_by,contract_number)select '修改',order_type,all_line_amount,term_name,yewu,delivery_date,schedule_recevie_date,delivery_type,yunfei_amount,tax_amount,ship_address,ship_city, youhui_amount,
order_number,customer_code,customer_contact,need_date,qianding_date,status,currency_code,customer_order_number,tax_name,tax_rate,tax_flag,project,jiaohuotiaojian,youxiaoxing1,
youxiaoxing2,baozhuang,zhiliangbaozheng,mainfeifuwu,order_all_amount,creation_date,header_remark,created_by,last_update_date,last_updated_by,contract_number from so_headers_all 
        where order_number='" . $_POST['order_number'] . "'";
    $result = DB_query($sql,$db);

	   DB_Txn_Commit($db);


    prnMsg(_('订单头修改成功！'), 'success');
	echo '<meta http-equiv="refresh" content="0; url=' . $RootPath .
                    '/QuoteSoUpdate2.php?New=Yes&Updateorder_number='. $_POST['order_number'] . '" />';

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


if (isset($_GET['order_line_id'])   ) {

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
      $sum_amount=0;
	  $youhui_amount=0;
	  $tax_amount=0;
	  $yunfei_amount=0;

         $sql = "insert into so_lines_all_log(change_type,order_number,line,line_remark,subinventory_code,uom,zhidao_price,price,quantity,stockid,customer_item,line_amount,need_date,
						creation_date,created_by,last_update_date,last_updated_by)
						select '删除',order_number,line,line_remark,subinventory_code,uom,zhidao_price,price,quantity,stockid,customer_item,line_amount,need_date,
						creation_date,created_by,last_update_date,last_updated_by from so_lines_all
                     where  order_line_id= '" . $_GET['order_line_id'] . "' ";
         $result = DB_query($sql,$db);

       $sql = "delete from so_lines_all   where  order_line_id= '" . $_GET['order_line_id'] . "' ";
	   $result = DB_query($sql,$db);
	      DB_Txn_Commit($db);
	  $sql3 = "select ifnull(sum(line_amount),0) sum_amount from so_lines_all where order_number= '" . $_GET['so_order_number'] . "' ";
	 
	   $result3 = DB_query($sql3,$db);
	       echo DB_num_rows($result3);
	   if (DB_num_rows($result3)==0) {
		   $sum_amount=0; }
		   else {
	   while ($row=DB_fetch_array($result3)) {
	     $sum_amount=$row['sum_amount'];
		 
	   }
	  }

	    $sql4 = "select youhui_amount,tax_amount,yunfei_amount,tax_rate,tax_flag  from so_headers_all
		where order_number= '" . $_GET['so_order_number'] . "' ";
	 // echo $sql4;
	   $result4 = DB_query($sql4,$db);
	   while ($row4=DB_fetch_array($result4)) {
	     $youhui_amount=$row4['youhui_amount'];
	     $tax_amount=$row4['tax_amount'];
	     $yunfei_amount=$row4['yunfei_amount'];
	     $tax_rate=$row4['tax_rate'];
	     $tax_flag=$row4['tax_flag'];
	   } 
	   $tax_amount=$sum_amount * $tax_rate;
	   $all_sum_amount =  $sum_amount + $tax_amount ;

        $time= time();
       $status='待签核';
	   if ($tax_flag=='N') {
	   $tax_amount=$sum_amount * $tax_rate;
	   $all_sum_amount =  $sum_amount + $tax_amount ;
       $deletesql1 = "update so_headers_all
		              set order_all_amount=".  $all_sum_amount ." , 
					  tax_amount=". $tax_amount." ,
					  status='". $status."' ,
					  all_line_amount=". $sum_amount." ,
					  last_update_date='" . $time . "',
						  last_updated_by='" . $_SESSION['UserID'] . "'
                       where order_number='" . $_GET['so_order_number'] . "'";
		// echo $deletesql1;
        $Resultdelete1 = DB_query($deletesql1, $db);

           $sql = "insert into so_headers_all_log
(change_type,order_type,all_line_amount,term_name,yewu,delivery_date,schedule_recevie_date,delivery_type,yunfei_amount,tax_amount,ship_address,ship_city, youhui_amount,
order_number,customer_code,customer_contact,need_date,qianding_date,status,currency_code,customer_order_number,tax_name,tax_rate,tax_flag,project,jiaohuotiaojian,youxiaoxing1,
youxiaoxing2,baozhuang,zhiliangbaozheng,mainfeifuwu,order_all_amount,creation_date,header_remark,created_by,last_update_date,last_updated_by)select '删除',order_type,all_line_amount,term_name,yewu,delivery_date,schedule_recevie_date,delivery_type,yunfei_amount,tax_amount,ship_address,ship_city, youhui_amount,
order_number,customer_code,customer_contact,need_date,qianding_date,status,currency_code,customer_order_number,tax_name,tax_rate,tax_flag,project,jiaohuotiaojian,youxiaoxing1,
youxiaoxing2,baozhuang,zhiliangbaozheng,mainfeifuwu,order_all_amount,creation_date,header_remark,created_by,last_update_date,last_updated_by from so_headers_all 
         where order_number='" . $_GET['so_order_number'] . "'";
           $result = DB_query($sql,$db);

	   } else {

		   $not_tax_amount=$sum_amount / (1 + $tax_rate);
	       $tax_amount =  $sum_amount - $not_tax_amount ;
	   $deletesql1 = "update so_headers_all
		              set order_all_amount=". $sum_amount." , 
					  tax_amount=". $tax_amount." ,
					  status='". $status."' ,
					  all_line_amount=". $not_tax_amount." ,
					  last_update_date='" . $time . "',
						  last_updated_by='" . $_SESSION['UserID'] . "'
                       where order_number='" . $_GET['so_order_number'] . "'";
		 // echo $deletesql1;
        $Resultdelete1 = DB_query($deletesql1, $db);

           $sql = "insert into so_headers_all_log
(change_type,order_type,all_line_amount,term_name,yewu,delivery_date,schedule_recevie_date,delivery_type,yunfei_amount,tax_amount,ship_address,ship_city, youhui_amount,
order_number,customer_code,customer_contact,need_date,qianding_date,status,currency_code,customer_order_number,tax_name,tax_rate,tax_flag,project,jiaohuotiaojian,youxiaoxing1,
youxiaoxing2,baozhuang,zhiliangbaozheng,mainfeifuwu,order_all_amount,creation_date,header_remark,created_by,last_update_date,last_updated_by)select '删除',order_type,all_line_amount,term_name,yewu,delivery_date,schedule_recevie_date,delivery_type,yunfei_amount,tax_amount,ship_address,ship_city, youhui_amount,
order_number,customer_code,customer_contact,need_date,qianding_date,status,currency_code,customer_order_number,tax_name,tax_rate,tax_flag,project,jiaohuotiaojian,youxiaoxing1,
youxiaoxing2,baozhuang,zhiliangbaozheng,mainfeifuwu,order_all_amount,creation_date,header_remark,created_by,last_update_date,last_updated_by from so_headers_all 
        where order_number='" . $_GET['so_order_number'] . "'";
           $result = DB_query($sql,$db);

       }

	 $_GET['New']='Yes';
	 $_GET['Updateorder_number']= $_GET['so_order_number'];
    prnMsg(_('删除成功！'), 'success');
	echo '<meta http-equiv="refresh" content="0; url=' . $RootPath .
                   '/QuoteSoUpdate2.php?New=Yes&Updateorder_number='. $_GET['so_order_number'] . '" />';
    //DB_Txn_Commit($db);

	 }
}

if (isset($_POST['LineUpdate'])) {

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

           if (mb_substr($key,0,13)=='order_line_id') {
              $order_line_id =mb_substr($key,13);
			  $i = $_POST[$key];
			//  echo $order_line_id ;
               //var_dump($i);
			  if ( $order_line_id>0 ) {
              //var_dump( $_POST['amount'.$line]);
              $count = $count + 1;
              $linesql = "UPDATE so_lines_all " . "
			  set quantity=  " . $_POST['quantity'.$i] . ",
                                price  ='" . $_POST['price'.$i]  . "', 
                                line_amount ='" . $_POST['line_amount'.$i]  . "',
                                need_date ='" . strtotime($_POST['need_date'.$i])  . "',
                                subinventory_code ='" . $_POST['subinventory_code'.$i]  . "',
                                line_remark ='" . $_POST['line_remark'.$i]  . "',
                                last_update_date ='" . $time . "',
                                last_updated_by= '" . $_SESSION['UserID'] . "'
                        where order_line_id='" . $order_line_id . "'  ";
				//echo $linesql;
              $Result = DB_query($linesql, $db);

                  $sql = "insert into so_lines_all_log(change_type,order_number,line,line_remark,subinventory_code,uom,zhidao_price,price,quantity,stockid,customer_item,line_amount,need_date,
						creation_date,created_by,last_update_date,last_updated_by)
						select '修改',order_number,line,line_remark,subinventory_code,uom,zhidao_price,price,quantity,stockid,customer_item,line_amount,need_date,
						creation_date,created_by,last_update_date,last_updated_by from so_lines_all
                     where order_line_id='" . $order_line_id . "'  ";
                  $result = DB_query($sql,$db);

			  }

           }
        }


            if ($count > 0) {
                //var_dump($sumamount);

 $status='待签核';
		$deletesql1 = "update so_headers_all
		              set  status='".$status."' ,
					  order_all_amount=".$_POST['order_all_amount']." ,
	                      youhui_amount=".$_POST['youhui_amount']." ,
	                      all_line_amount=".$_POST['all_line_amount']." ,
	                      yunfei_amount=".$_POST['yunfei_amount']." ,
	                      tax_amount=".$_POST['tax_amount']." , 
						  last_update_date='" . $time . "',
						  last_updated_by='" . $_SESSION['UserID'] . "'
                       where order_number='" . $_POST['order_number']. "'";

                $ErrMsg = _('CRITICAL ERROR') . '! ' . _('header_remark DOWN THIS ERROR AND SEEK ASSISTANCE') .
                    ': ' . _('数据存入数据库，原因是：'); //'" . FormatDateForSQL($_SESSION['Contract'.$identifier]->DispatchDate) . "',
                $DbgMsg = _('The following SQL to insert the request header record was used');
                $Result = DB_query($deletesql1, $db, $ErrMsg, $DbgMsg, true);

                $sql = "insert into so_headers_all_log
(change_type,order_type,all_line_amount,term_name,yewu,delivery_date,schedule_recevie_date,delivery_type,yunfei_amount,tax_amount,ship_address,ship_city, youhui_amount,
order_number,customer_code,customer_contact,need_date,qianding_date,status,currency_code,customer_order_number,tax_name,tax_rate,tax_flag,project,jiaohuotiaojian,youxiaoxing1,
youxiaoxing2,baozhuang,zhiliangbaozheng,mainfeifuwu,order_all_amount,creation_date,header_remark,created_by,last_update_date,last_updated_by)select '修改',order_type,all_line_amount,term_name,yewu,delivery_date,schedule_recevie_date,delivery_type,yunfei_amount,tax_amount,ship_address,ship_city, youhui_amount,
order_number,customer_code,customer_contact,need_date,qianding_date,status,currency_code,customer_order_number,tax_name,tax_rate,tax_flag,project,jiaohuotiaojian,youxiaoxing1,
youxiaoxing2,baozhuang,zhiliangbaozheng,mainfeifuwu,order_all_amount,creation_date,header_remark,created_by,last_update_date,last_updated_by from so_headers_all 
        where order_number='" . $_POST['order_number']. "'";
                $result = DB_query($sql,$db);

                DB_Txn_Commit($db);
                $msg = '业务订单修改成功！1秒后将跳转回主页！';
                prnMsg($msg, 'success');
               echo '<meta http-equiv="refresh" content="0; url=' . $RootPath .
                    '/QuoteSoUpdate2.php?New=Yes&Updateorder_number='. $_POST['order_number'] . '" />';
                echo '<br />';
                echo '<br /><div class="centre"><a href="' . $RootPath . '/UpdateSoForApprove.php">' . _('重新选择业务订单') .
                    '</a></div>';
                unset($_SESSION['Contract' . $identifier]);
            } else {
                prnMsg(_('业务订单行无数据，如果确定全部取消请选择：全部取消'), 'error');
                unset($_SESSION['Contract' . $identifier]);
            }
            DB_Txn_Commit($db);
        }
        include ('includes/footer.inc');
        exit;
    } else {
        header('Location: UpdateSoForApprove.php');
    } 
} elseif (isset($_POST['CloseOrder'])) {
	$time = time();
    $order_number = $_SESSION['Contract' . $identifier]->order_number;
    $HeaderSQL = "update so_headers_all
                          set status= '结单', last_update_date='" . $time . "',
						  last_updated_by='" . $_SESSION['UserID'] . "'
		        where order_number='" . $order_number . "'";
   
    $Result = DB_query($HeaderSQL, $db, $ErrMsg, $DbgMsg, true);
    $msg = '业务订单关闭成功！1秒后将跳转回上一页！';
    prnMsg($msg, 'success');
    echo '<meta http-equiv="refresh" content="1; url=' . $RootPath .
        '/UpdateSoForApprove.php" />';
    echo '<br />';
    include ('includes/footer.inc');
    exit;
} elseif (isset($_POST['CancelOrder'])) {
	$time = time();
    $order_number = $_SESSION['Contract' . $identifier]->order_number;
    $HeaderSQL = "update so_headers_all
                          set status= '取消', last_update_date='" . $time . "',
						  last_updated_by='" . $_SESSION['UserID'] . "'
		        where order_number='" . $order_number . "'";

    $Result = DB_query($HeaderSQL, $db, $ErrMsg, $DbgMsg, true);

    $sql = "insert into so_headers_all_log
(change_type,order_type,all_line_amount,term_name,yewu,delivery_date,schedule_recevie_date,delivery_type,yunfei_amount,tax_amount,ship_address,ship_city, youhui_amount,
order_number,customer_code,customer_contact,need_date,qianding_date,status,currency_code,customer_order_number,tax_name,tax_rate,tax_flag,project,jiaohuotiaojian,youxiaoxing1,
youxiaoxing2,baozhuang,zhiliangbaozheng,mainfeifuwu,order_all_amount,creation_date,header_remark,created_by,last_update_date,last_updated_by)select '修改',order_type,all_line_amount,term_name,yewu,delivery_date,schedule_recevie_date,delivery_type,yunfei_amount,tax_amount,ship_address,ship_city, youhui_amount,
order_number,customer_code,customer_contact,need_date,qianding_date,status,currency_code,customer_order_number,tax_name,tax_rate,tax_flag,project,jiaohuotiaojian,youxiaoxing1,
youxiaoxing2,baozhuang,zhiliangbaozheng,mainfeifuwu,order_all_amount,creation_date,header_remark,created_by,last_update_date,last_updated_by from so_headers_all 
         where order_number='" . $order_number . "'";
    $result = DB_query($sql,$db);

    $HeaderSQL2 = "update so_lines_all
                          set quantity=0, last_update_date='" . $time . "',
						  last_updated_by='" . $_SESSION['UserID'] . "'
		        where order_number='" . $order_number . "'";
    
    $DbgMsg = _('The following SQL to insert the request header record was used');
    $Result2 = DB_query($HeaderSQL2, $db, $ErrMsg, $DbgMsg, true);

    $sql = "insert into so_lines_all_log(change_type,order_number,line,line_remark,subinventory_code,uom,zhidao_price,price,quantity,stockid,customer_item,line_amount,need_date,
						creation_date,created_by,last_update_date,last_updated_by)
						values('修改','".$_POST['order_number']."','".$line."','".$_POST['remark'.$i]."',
						'".$_POST['subinventory_code'.$i]."','".$_POST['UOM'.$i]."','".$_POST['zhidao_price'.$i]."','".$_POST['unitprice'.$i]."',
						'".$_POST['quantity'.$i]."','".$_POST['stockid'.$i]."','".$_POST['customer_item'.$i]."','".$lineamount[$i]."',
						'".strtotime($_POST['need_date'.$i])."','".$time."','".$_SESSION['UserID']."','".$time."','".$_SESSION['UserID']."') ";

    $result = DB_query($sql,$db);

    $msg = '业务订单取消成功！1秒后将跳转回上一页！';
    prnMsg($msg, 'success');
    echo '<meta http-equiv="refresh" content="1; url=' . $RootPath .
        '/UpdateSoForApprove.php" />';
    echo '<br />';
    include ('includes/footer.inc');
    exit;
} elseif (isset($_POST['DeleteOrder'])) {
 
	$so_order_line=0;
    $order_number = $_SESSION['Contract' . $identifier]->order_number;
    $sql = "select count(*)  so_order_line
	            from so_delivery_all    
		        where so_order_number='" . $order_number . "'"; 
    $result = DB_query($sql, $db);
    while  ($myrow = DB_fetch_array($result)) {
	  $so_order_line= $myrow['so_order_line'];
	}
    if ($so_order_line>0 ) {
       $msg = '订单已出货不能删除！';
       prnMsg($msg, 'error'); }
	else {
    $order_number = $_SESSION['Contract' . $identifier]->order_number;

        $sql = "insert into so_headers_all_log
(change_type,order_type,all_line_amount,term_name,yewu,delivery_date,schedule_recevie_date,delivery_type,yunfei_amount,tax_amount,ship_address,ship_city, youhui_amount,
order_number,customer_code,customer_contact,need_date,qianding_date,status,currency_code,customer_order_number,tax_name,tax_rate,tax_flag,project,jiaohuotiaojian,youxiaoxing1,
youxiaoxing2,baozhuang,zhiliangbaozheng,mainfeifuwu,order_all_amount,creation_date,header_remark,created_by,last_update_date,last_updated_by)select '删除',order_type,all_line_amount,term_name,yewu,delivery_date,schedule_recevie_date,delivery_type,yunfei_amount,tax_amount,ship_address,ship_city, youhui_amount,
order_number,customer_code,customer_contact,need_date,qianding_date,status,currency_code,customer_order_number,tax_name,tax_rate,tax_flag,project,jiaohuotiaojian,youxiaoxing1,
youxiaoxing2,baozhuang,zhiliangbaozheng,mainfeifuwu,order_all_amount,creation_date,header_remark,created_by,last_update_date,last_updated_by from so_headers_all 
         where order_number='" . $order_number . "'";
        $result = DB_query($sql,$db);

    $HeaderSQL = "delete from so_headers_all 
		        where order_number='" . $order_number . "'";
    
    $Result = DB_query($HeaderSQL, $db, $ErrMsg, $DbgMsg, true);

        $sql = "insert into so_lines_all_log(change_type,order_number,line,line_remark,subinventory_code,uom,zhidao_price,price,quantity,stockid,customer_item,line_amount,need_date,
						creation_date,created_by,last_update_date,last_updated_by)
						select '删除',order_number,line,line_remark,subinventory_code,uom,zhidao_price,price,quantity,stockid,customer_item,line_amount,need_date,
						creation_date,created_by,last_update_date,last_updated_by from so_lines_all
                   where order_number='" . $order_number . "'";
        $result = DB_query($sql,$db);

    $HeaderSQL2 = "delete from so_lines_all 
		        where order_number='" . $order_number . "'";
     
    $Result2 = DB_query($HeaderSQL2, $db, $ErrMsg, $DbgMsg, true);

    $msg = '业务订单删除！';
    prnMsg($msg, 'success');
    echo '<meta http-equiv="refresh" content="1; url=' . $RootPath .
        '/UpdateSoForApprove.php" />';
    echo '<br />';
    include ('includes/footer.inc');
    exit;
	}
} else {
    session_start() or die("session is not started");
    $_SESSION['num' . $identifier] = 400;
}
echo '	<div class="centre">
		<a href="' . $RootPath . '/UpdateSoForApprove.php">返回重新选择业务订单</a>
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


if ($_SESSION['Contract' . $identifier]->qianding_date>1) {
 $v_qianding_date = date('Y-m-d', $_SESSION['Contract' . $identifier]->qianding_date);
}
 if ($_SESSION['Contract' . $identifier]->need_date>1) {
 $v_need_date = date('Y-m-d', $_SESSION['Contract' . $identifier]->need_date);
}
 if ($_SESSION['Contract' . $identifier]->need_date>1) {
 $v_create_date = date('Y-m-d', $_SESSION['Contract' . $identifier]->create_date);
}

 
echo '<div class="text-nav">
		<div class="text-nav-1"><div>' . _('业务订单') . ':</div>
		<input type="text" readonly="readonly" value="' . $_SESSION['Contract' . $identifier]->order_number . '" />
		<input type="hidden" class="text"  name="order_number" value="' . $_SESSION['Contract' . $identifier]->order_number . '" />
		</div>
		<div class="text-nav-1"><div>创建日期:</div>' . '<input type="text" readonly="readonly" value="' . $v_create_date . '" /></div>
		<div class="text-nav-1"><div>状态：</div>' . '<input type="text" readonly="readonly" value="' . $_SESSION['Contract' . $identifier]->status . '" /></div>';

echo '<div class="text-nav-1"><div>签订日期:</div>' .
    '<input type="text" onfocus="WdatePicker()"  name="qianding_date" maxlength="10" size="11" value="' .
    $v_qianding_date . '" /></div>

	<div class="text-nav-1"><div>需求日期:</div>
     <input type="text" onfocus="WdatePicker()"  name="need_date" maxlength="10" size="11" value="' .
    $v_need_date . '" /></div>

	<div class="text-nav-2"><div>' . _('客户名称') . ':</div>
	<input type="text" readonly="readonly" value="' . $_SESSION['Contract' . $identifier]->customer_name . '" />' . '</div>';

echo '<div class="text-nav-1"><div>' . _('客户订单号') . ':</div>
			<input type="text" size="11" name="customer_order_number"   value="' . $_SESSION['Contract' . $identifier]->customer_order_number . '" /> </div>
			<div class="text-nav-2"><div>' . _('出货地址') . ':</div>
			<input type="text"  size="48" name="ship_address"  value="' . $_SESSION['Contract' . $identifier]->ship_address . '" /> </div>

			<div class="text-nav-1"><div>' . _('到达城市') . ':</div>
			<input type="text" size="11" name="ship_city"   value="' . $_SESSION['Contract' . $identifier]->ship_city . '" /> </div>
		
			<div class="text-nav-1"><div>' . _('含税金额') . ':</div>
			<input type="text"  size="11" onkeyup="check_amount2()" name="order_all_amount" id="order_all_amount"  value="' . $_SESSION['Contract' . $identifier]->order_all_amount . '" /> </div>
      <div class="text-nav-1"><div>' . _('未税金额') . ':</div>
			<input type="text"  size="11" onkeyup="check_amount2()" name="all_line_amount" id="all_line_amount"  value="' . $_SESSION['Contract' . $identifier]->all_line_amount . '" /> </div>
				<div class="text-nav-1"><div>' . _('税金') . ':</div>
			<input type="text"  size="11" onkeyup="check_amount2()" name="tax_amount" id="tax_amount"  value="' . $_SESSION['Contract' . $identifier]->tax_amount . '" /> </div>
			';
 echo '<div class="text-nav-1"><div>' . _('是否含税') . '</div>

			<select name="tax_flag" id="text_slect_tax_flag">';
 	
					$sql = "select type_code,type_name from sys_type ";
					$result = DB_query($sql,$db);
					while ($v = DB_fetch_array($result)) {
						if ($v['type_code']==$_SESSION['Contract' . $identifier]->tax_flag ) {

				echo '<option value="'.$v['type_code'].'" selected="selected">'.$v['type_name'].'</option>';
				  }else{
				echo '<option value="'.$v['type_code'].'">'.$v['type_name'].'</option>';
				 		}
					}

			echo '</select></div> ';
 
echo '<div class="text-nav-1"><div>' . _('税率') . ':</div>
			<input type="text" readonly="readonly" class="number" size="11" name="tax_rate" id="tax_rate" value="' . $_SESSION['Contract' . $identifier]->tax_rate . '" /> </div>


			<div class="text-nav-1"><div>' . _('优惠价') . ':</div>
			<input type="text" class="number" size="11" name="youhui_amount" id="youhui_amount"  onkeyup="check_amount1()" value="' . $_SESSION['Contract' . $identifier]->youhui_amount . '" /> </div>
			<div class="text-nav-1"><div>' . _('运费') . ':</div>
			<input type="text" class="number"  onkeyup="check_amount2()" size="11" name="yunfei_amount" id="yunfei_amount" value="' . $_SESSION['Contract' . $identifier]->yunfei_amount . '" /> </div>

			';
 

 echo '<div class="text-nav-1"><div>' . _('业务员:') . '</div>

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

			echo '</select></div>
			<div class="text-nav-1"><div>' . _('税别') . ':</div>
     <input type="text"   size="10"  maxlength="100" name="tax_name"  value="' . $_SESSION['Contract' . $identifier]->tax_name . '" /> </div>
			  
			<div class="text-nav-1"><div>' . _('付款条件') . ':</div>
		<input type="text"   size="10"  maxlength="100" name="term_name"  value="' . $_SESSION['Contract' . $identifier]->term_name . '" /> </div>
		<div class="text-nav-1"><div>' . _('合同编号') . ':</div>
		<input type="text"   size="10"  maxlength="100" name="contract_number"  value="' . $_SESSION['Contract' . $identifier]->contract_number . '" /> </div>
   	';



echo '
<div class="text-nav-2"><div>' . _('备注') . ':</div>
             <input type="text"  name="header_remark"   maxlength="100" size="80"  value="'. $_SESSION['Contract' .
    $identifier]->header_remark . '"/></div>
';
echo '<div class="text-nav-2"><div>' . _('审核备注') . ':</div>
<input type="text" readonly="readonly" value="' . $_SESSION['Contract' . $identifier]->approve_remark . '" /></div>
			  <div class="text-nav-1"><div>' . _('上传新附件') . ':</div>
			 <a style="width: 40px;" href="' . $RootPath . '/SOUploadNewfile.php?OrderNum=' . $_SESSION['Contract' . $identifier]->order_number . '"target="_blank">上传 </a></div>

			<input type="hidden" name="order_all_amount_old" id="order_all_amount_old" value="'. $_SESSION['Contract'.$identifier]->order_all_amount . '"/> 
			<input type="hidden" name="youhui_amount_old" id="order_all_amount_old" value="'. $_SESSION['Contract'.$identifier]->youhui_amount . '"/> 
			<input type="hidden" name="customer_code"  value="'. $_SESSION['Contract'.$identifier]->customer_code . '"/>
			<input type="hidden" name="all_line_amount" id="all_line_amount_old" value="' . $_SESSION['Contract' . $identifier]->all_line_amount . '" /> </td>
        </div>';

echo '</table>';
if ($_SESSION['modify_flag']=='Y') {
echo '<div class="centre">
	<input type="submit" name="Update" value="' . _('业务订单头修改保存') . '" />
	<input type="submit" name="CancelOrder" value="' . _('取消订单') . '" />
	<input type="submit" name="DeleteOrder" value="' . _('删除订单') . '" />
	<input type="submit" name="CloseOrder" value="' . _('结单') . '" />
	</div>
    </div>
	</form>';
} else {
echo '<div class="centre">
	<input type="submit" name="Update" value="' . _('业务订单头修改保存') . '" />
	<input type="submit" name="CancelOrder" value="' . _('取消订单') . '" />
	<input type="submit" name="DeleteOrder" value="' . _('删除订单') . '" />
	<input type="submit" name="CloseOrder" value="' . _('结单') . '" />
 
	</div>
    </div>
	</form>'; 
	}



$sql2 = "SELECT
	 	file_patch,creation_date,created_by,file_name
FROM so_headers_all_file
        where  order_number = '" .$_SESSION['Contract' . $identifier]->order_number."'";
        $result2 = DB_query($sql2, $db);
        if (DB_num_rows($result2) == 0) {
            unset($result2);
          //  prnMsg(_('无附件'), 'info');
        } else {
			echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/customer.png" title="' . _('订单头信息') .
 '" alt="" />' . ' ' . _('订单附件信息') . '
	</p>';
            echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '"><input type="hidden" name = "identifier" value ="' . $identifier . '">';
            echo '<div>';
            echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
            echo '<table class="selection" align="center" >';
            $tableheader = '<tr>

                                        <th width =150 >' . '附件名称' . '</th>
										<th width =190 >' . '上传时间' . '</th>
										<th width =80 >' . '上传人员' . '</th>
                                        <th  width =50>' . '下载' . '</th>



				</tr>';

            echo $tableheader;
            $RowCounter = 1;
            $k = 0; //row colour counter
            while ($myrow = DB_fetch_array($result2)) {
                if ($k == 1) {
                    echo '<tr class="EvenTableRows">';
                    $k = 0;
                } else {
                    echo '<tr class="EvenTableRows">';
                    $k++;
                }
 
	  // read_pdf('./999.pdf');
  
                echo '
		              <td>' . $myrow['file_name'] . '</td>
                      <td>' . date('Y-m-d H:i:s',$myrow['creation_date']) . '</td>
					  <td>' . $myrow['created_by'] . '</td>
					  <td><a href="' . $RootPath . '/' . $myrow['file_patch'] . '" target="_blank">' . '下载' . '</td>
					   <td><a href="' . $RootPath . '/Quote3.php?New=Yes&Updateorder_number=' . $myrow['file_patch'] . '">预览</td>
  


        </tr>';
                $RowCounter++;
                If ($RowCounter == 500) {
                    $RowCounter = 1;
                    echo $tableheader;
                }
            }
            echo '</table> ';


            echo '</div>
          </form>';
        }

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
//用隐藏域保存业务订单表号
echo ' <input type="hidden" class="text"  name="customer_code" value="' . $_SESSION['Contract' . $identifier]->customer_code . '" />';
echo ' <input type="hidden" class="text"  name="order_number" value="' . $_SESSION['Contract' . $identifier]->order_number . '" />';
echo ' <input type="hidden" class="text"  name="youhui_amount" id="youhui_amount2" value="' . $_SESSION['Contract' . $identifier]->youhui_amount . '" />';
echo ' <input type="hidden" class="text"  name="order_all_amount" id="order_all_amount2" value="' . $_SESSION['Contract' . $identifier]->order_all_amount . '" />';
echo ' <input type="hidden" class="text"  name="all_line_amount" id="all_line_amount2" value="' . $_SESSION['Contract' . $identifier]->all_line_amount . '" />';
 
echo ' <input type="hidden" class="text"  name="yunfei_amount" id="yunfei_amount2" value="' . $_SESSION['Contract' . $identifier]->yunfei_amount . '" />';
echo ' <input type="hidden" class="text"  name="tax_amount" id="tax_amount2" value="' . $_SESSION['Contract' . $identifier]->tax_amount . '" />';
echo ' <input type="hidden" class="text"  name="tax_rate" id="tax_rate2" value="' . $_SESSION['Contract' . $identifier]->tax_rate . '" />';
echo '<br /> ';


echo '<div class="text-nav-table">
<table class="selection">
	<tr>
		<th>' . _('行') . '</th>
		<th   width=100>' . _('料号') . '</th>
		<th   width=100>' . _('料号名称') . '</th>
		<th   width=100>' . _('规格型号') . '</th> 
		<th   width=50>' . _('单位') . '</th> 
                    <th   width=80>' . _('出货数量') . '</th>
                 <th   width=100>' . _('数量') . '</th>
                 <th   width=100>' . _('单价') . '</th>
                 <th   width=100>' . _('金额') . '</th>  
                 <th   width=100>' . _('需求日期') . '</th> 
                 <th   width=100>' . _('备注') . '</th> 

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
	        <td>' . $myrow['stockid'] . '</td>
			<td>' . $myrow['item_name'] . '</td>';
		 
	 echo ' <td><input readonly="readonly" id="item_desc' .$i.'"   style="" type="text"  name="item_desc'.$i.'"   size="15"  value="' . $myrow['item_desc']  . '" /></td> ';
          echo '    
		  <td>' . $myrow['uom']  . '</td> 
			<td>' . $myrow['quantity_shiped']  . '</td> ';
       echo ' <td><input id="quantity'.$i.'" onblur="checkall()" onkeyup="webdesign('.$i.')"   style="" type="text"  name="quantity'.$i.'" class="number" size="8"  value="' . sprintf("%.2f",$myrow['quantity'])  . '" /></td> ';
	   echo ' <td><input id="price'.$i.'" onblur="checkall()"  onkeyup="webdesign('.$i.')" style="" type="text"  name="price'.$i.'" class="number" size="8"  value="' . sprintf("%.2f",$myrow['price']) . '" /></td> ';
	   echo ' <td><input readonly="readonly" id="line_amount' .$i.'"  style="" type="text"  name="line_amount'.$i.'" class="number" size="8"  value="' .sprintf("%.2f",$myrow['line_amount']) . '" />';
	   if ($myrow['need_date']>0) {
	   echo ' <td><input  type="text"  name="need_date'.$i.'"  onfocus="WdatePicker()" size="9"  value="' .date('Y-m-d',$myrow['need_date']) . '" /></td>';
	   }else {
		echo ' <td><input  type="text"  name="need_date'.$i.'"  onfocus="WdatePicker()" size="9"  value="" /></td>';

	   }

/*
	  echo ' <td>
			<select name="subinventory_code'.$i.'" id="">';

				 $sql9 = "select loccode,locationname from  locations  where managed='Y' order by loccode";
					$result9 = DB_query($sql9,$db);
					while ($v9 = DB_fetch_array($result9)) {

			 if ($v9['loccode'] == $myrow['subinventory_code'] ) {

			  echo ' <option style="width:80px;" value="' . $v9['loccode']  . '" selected="selected"> '. $v9['locationname'].  '</option>';
				  }else{
			 echo '<option value="'.$v9['loccode'].'">'.$v9['locationname'].'</option>';
				 		}
					}


      echo ' </select>  </td>';

*/

	 echo ' <td><input id="line_remark' .$i.'"  style="" type="text"  name="line_remark'.$i.'"  size="8"  value="' . $myrow['line_remark']  . '" /></td> ';


		 if ($myrow['quantity_shiped']==0) {
		echo ' <td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?so_order_number=' .$_SESSION['Contract' . $identifier]->order_number .'&order_line_id=' .$myrow['order_line_id'] . '&amp;delete=1" onclick="return confirm(\'' . _('是否要删除这个料号?') . '\');">' . _('删除')  . '</a></td>';
       }

	  echo '  <input type="hidden" name="order_line_id'.$myrow['order_line_id'].'" value="'.$i.'" /></td> ';


       echo '
     </tr>';
      $i++;
	echo ' </tr>';

}
echo '</table></div>


	<div class="centre">
                <input type="submit" id="submit" name="LineUpdate" value="' . _('业务订单行修改保存') .
    '" />
	</div>

    </div>
    </form>';

//*********************************************************************************************************

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>新建业务订单</title>
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


public function read_pdf($file) {
        if(strtolower(substr(strrchr($file,'.'),1)) != 'pdf') {
            echo '文件格式不对.';
            return;
        }
        if(!file_exists($file)) {
            echo '文件不存在';
            return;
        }
        header('Content-type: application/pdf');
        header('filename='.$file);
        readfile($file);
    }

</script>

<script type="text/javascript">

function checkaddall(){

                      var allamount=0;
                      var youhui_amount=document.getElementById("youhui_amount").value;
					  var all_line_amount_old=document.getElementById("all_line_amount_old").value;
					  var tax_rate=document.getElementById("tax_rate").value;
					  var tax_flag=document.getElementById("text_slect_tax_flag").value;
					    var all_rate = Number(1) + Number(tax_rate) ;	


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
        
		if (tax_flag=='N')
        {  
		 var order_all_amount_old=document.getElementById("all_line_amount_old").value;		   
		   var  all_line_amount= Number(order_all_amount_old) + Number(allamount);
		    var  no_tax_amount= all_line_amount;
		    var  tax_amount_new= Number(all_line_amount) * Number(tax_rate);
		    var  han_tax_amount= Number(all_line_amount) + Number(tax_amount_new)  ;
        } else {
			
		   var order_all_amount_old=document.getElementById("order_all_amount_old").value;		   
		   var  all_line_amount= Number(order_all_amount_old) + Number(allamount);
		    var  no_tax_amount= Number(all_line_amount) / Number(all_rate);
		    var  tax_amount_new= Number(all_line_amount) - Number(no_tax_amount);
		    var  han_tax_amount= all_line_amount  ;
		}

		var  youhui_amount2=  document.getElementById("youhui_amount").value;
		var  all_line_amount= Number(all_line_amount_old) + Number(allamount);
		var  tax_amount_new= Number(all_line_amount) * Number(tax_rate);
		var  order_all_amount= Number(all_line_amount) + Number(tax_amount_new)  ;

		document.getElementById("order_all_amount").value= Math.round(Number(han_tax_amount)*100)/100;
	   document.getElementById("youhui_amount_new").value=youhui_amount2 ;
	   document.getElementById("order_all_amount_new").value= Math.round(Number(han_tax_amount)*100)/100;
	   document.getElementById("all_line_amount_new").value= Math.round(Number(no_tax_amount)*100)/100;
	   document.getElementById("all_line_amount").value= Math.round(Number(no_tax_amount)*100)/100;
	   document.getElementById("tax_amount").value= Math.round(Number(tax_amount_new)*100)/100;
	   document.getElementById("tax_amount_new").value= Math.round(Number(tax_amount_new)*100)/100;
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
            var yunfei_amount=document.getElementById("yunfei_amount").value;
            var tax_rate=document.getElementById("tax_rate").value;
            var tax_flag=document.getElementById("text_slect_tax_flag").value;
			var all_rate = Number(1) + Number(tax_rate);

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
        var  youhui_amount2= document.getElementById("youhui_amount").value;
        
		if ( tax_flag=='N')
        {
			var  tax_amount=Number(allamount) * Number(tax_rate) ;
			var no_tax_amount=allamount;
			var han_tax_amount=Number(allamount) + Number(tax_amount) ;
        } else {
		    var  no_tax_amount=Number(allamount) / Number(all_rate) ;
			var  tax_amount=Number(allamount) - Number(no_tax_amount);
			var han_tax_amount= allamount ;
		}

         document.getElementById("all_line_amount2").value= Math.round(Number(no_tax_amount)*100)/100; 
		document.getElementById("all_line_amount").value= Math.round(Number(no_tax_amount)*100)/100;
		document.getElementById("tax_amount2").value= Math.round(Number(tax_amount)*100)/100;
		document.getElementById("tax_amount").value= Math.round(Number(tax_amount)*100)/100;
	   document.getElementById("youhui_amount2").value=youhui_amount2 ;
	   document.getElementById("order_all_amount2").value= Math.round(Number(han_tax_amount)*100)/100;
		document.getElementById("order_all_amount").value= Math.round(Number(han_tax_amount)*100)/100;
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


 

 function  check_amount2(){
         var allamount=0;
        // var order_all_amount=document.getElementById("order_all_amount").value;
         var tax_amount=document.getElementById("tax_amount").value;
         var yunfei_amount=document.getElementById("yunfei_amount").value;
         var all_line_amount=document.getElementById("all_line_amount").value;
         var youhui_amount=document.getElementById("youhui_amount").value;
         var order_all_amount=Number(all_line_amount)+Number(yunfei_amount)+Number(tax_amount);
         var po_yingfu_amount=Number(order_all_amount)-Number(youhui_amount);

		document.getElementById("order_all_amount").value=Math.round(Number(order_all_amount)*100)/100;
        document.getElementById("order_payment_amount").value=Math.round(Number(po_yingfu_amount)*100)/100;
		document.getElementById("all_line_amount_new").value=Math.round(Number(po_yingfu_amount)*100)/100;

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

           


	function  check_amount1(){
            var allamount=0;
            var order_all_amount=document.getElementById("order_all_amount").value;
            var all_line_amount=document.getElementById("all_line_amount").value;
            var youhui_amount=document.getElementById("youhui_amount").value;
		    var yunfei_amount=document.getElementById("yunfei_amount").value;
		    var tax_amount=document.getElementById("tax_amount").value;
            var po_yingfu_amount=Number(order_all_amount)-Number(youhui_amount); 

        var a=document.getElementById("order_all_amount").value;
        var b=document.getElementById("youhui_amount").value;
      if(parseFloat(youhui_amount)>parseFloat(order_all_amount)){
            document.getElementById("Prompt").innerHTML="优惠金额"+b+"不可以大于总金额！"+a;
            document.getElementById("youhui_amount").value=0;
            document.getElementById("youhui_amount").focus(); 
        }
        else {
            document.getElementById("Prompt").innerHTML="";
			document.getElementById("youhui_amount2").value=b;
			document.getElementById("youhui_amount_new").value=b;
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
			<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="新增业务订单行" alt="新增业务订单行">新增业务订单行</p>
			<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST"><input type="hidden" name="time" value="<?=$time?>">
				<div>
				<input readonly="readonly" type="hidden"   name="customer_code"  value="<?=$_SESSION['Contract' . $identifier]->customer_code?>" size="55" maxlength="46"/>
				 <input readonly="readonly" type="hidden"   name="order_number"  value="<?=$_SESSION['Contract' . $identifier]->order_number?>" size="55" maxlength="46"/>
				 <td>  <input type="hidden" name="order_all_amount_new" id="order_all_amount_new" value="<?=$_SESSION['Contract' . $identifier]->order_all_amount?>"/>
			<input type="hidden" name="all_line_amount_new" id="all_line_amount_new" value="<?=$_SESSION['Contract' . $identifier]->all_line_amount?>"/>
			<input type="hidden" name="youhui_amount_new" id="youhui_amount_new" value="<?=$_SESSION['Contract' . $identifier]->youhui_amount?>"/>
			<input type="hidden" name="tax_amount" id="tax_amount_new" value="<?=$_SESSION['Contract' . $identifier]->tax_amount?>"/>
			 
					<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">


					<input type="hidden" name="PageOffset" value="1"/><br/>
					<?php
					if (isset($_SESSION['Contract' . $identifier]->customer_name) and $_SESSION['Contract' . $identifier]->customer_name != '') {
						?>
						<input id="purchase_table_lastRow" name="purchase_table_lastRow" type=hidden value="">

						<div class="centre">
                        <p id="Prompt" style="color: red;font-size: 20px"></p>
                    </div>
					<div class="text-nav-table">
						<table id="purchase_table" cellpadding="2" class="selection">
							<tr id="list-top">
								<th  width="230">料号</th>
								<th  width="100">材料名称</th>
								<th width="100">规格型号</th> 
								<th width="10">客户料号</th>
                                <th width="10">单位</th>
								<th width="80">数量</th>
								<th width="80">单价</th>
								<th width="120">金额</th> 
								<th width="20">需求日期</th> 
								<th width="30">备注</th>
								<th width="50" align="center">操作</th>
							</tr>
							<?php for($j=1;$j<=50;$j++){?>

								<tr id="purchase_table_<?=$j?>" <?php echo $j>3&&$_POST['stockid'.$j]==''?'style="display:none"':''?> class="mouse click">

									<td><input readonly="readonly" style="background-color:#D2E9FF;" type="text" name="stockid<?=$j?>" id="text_slect_buliao<?=$j?>" value="<?=$_POST['stockid'.$j]?>" size="20" maxlength="100"  />
										<image class="select_img" src="img/search.png" id="btn_slect_buliao<?=$j?>"/> </td>
									<td ><input readonly="readonly" type="text" name="item_name<?=$j?>" id="text_slect_item_name<?=$j?>" value="<?=$_POST['item_name'.$j]?>" size="20" maxlength="150"/></td>

									<td ><input readonly="readonly"  type="text" name="item_desc<?=$j?>" id="text_slect_item_spec<?=$j?>" value="<?=$_POST['item_desc'.$j]?>" size="20" maxlength="150"/></td> 
									<td><input readonly="readonly" type="text" name="customer_item<?=$j?>" id="text_slect_customer_item<?=$j?>" value="<?=$_POST['customer_item'.$j]?>" size="2" maxlength="4"/></td>

									<td><input readonly="readonly" type="text" name="UOM<?=$j?>" id="text_slect_units<?=$j?>" value="<?=$_POST['UOM'.$j]?>" size="2" maxlength="4"/></td>
									<td><input type="text" style="background-color:#D2E9FF;" class="number" id="add_quantity<?=$j?>"   name="quantity<?=$j?>" step="1"  min="0" onkeyup="this.value= this.value.match(/\d+(\.\d{0,2})?/) ? this.value.match(/\d+(\.\d{0,2})?/)[0] : ''"  value="<?=$_POST['quantity'.$j]?>" size="4" maxlength="10" onblur="checkaddall()"  /></td>
									<td><input type="text" style="background-color:#D2E9FF;" class="number" id="text_slect_unit_price<?=$j?>" onblur="checkaddall()"  step="1"  min="0" onkeyup="this.value= this.value.match(/\d+(\.\d{0,2})?/) ? this.value.match(/\d+(\.\d{0,2})?/)[0] : ''"   name="unitprice<?=$j?>" value="<?=$_POST['unitprice'.$j]?>" size="4" maxlength="10" /></td>


		 <td><input type="text" readonly="readonly" id="lineamount<?=$j?>" class="number"  onkeyup="check(<?=$j?>)"  name="lineamount<?=$j?>" value="<?=$_POST['lineamount'.$j]?>" size="8" maxlength="10" /></td>
		 <td><input   type="text" name="need_date<?=$j?>" onfocus="WdatePicker()" id="need_date<?=$j?>" value="<?=$_POST['need_date'.$j]?>" size="9" maxlength="14"/></td>
      <!-- <td><select name="subinventory_code<?=$j?>">
           <?php
            $sql = "SELECT loccode,locationname FROM locations where managed='Y' order by paixu";
               $result1 = DB_query($sql, $db);

               while ($Salesmanrow = DB_fetch_array($result1)) {
	             	 ?>
                <option style="width:40px;" value="<?=$Salesmanrow['loccode']?>">  <?=$Salesmanrow['locationname'] ?>
                  </option>
               <?php }
               ?>
                                    </select>  </td>

     -->

	 <td class="list-text"><input type="text" name="remark<?=$j?>" id="remark<?=$j?>" value="<?=$_POST['remark'.$j]?>" size="12" maxlength="45"/></td>
		 <td>  <a onclick="delettr($(this));" style="padding:0px 5px;" href="javascript:;">删除</a></td>

									<td><input  type="hidden" name="Subinventory_name<?=$j?>" id="text_slect_locationname<?=$j?>" value="<?=$_POST['Subinventory_name'.$j]?>" size="8" maxlength="25"/>

<input readonly="readonly" type="hidden" name="last_price<?=$j?>" id="text_slect_last_price<?=$j?>" value="<?=$_POST['last_price'.$j]?>" size="4" maxlength="4"/>

								</tr>
							<?php }?>

						</table></div>

						<div class="centre">
							<a onclick="addsave();">添加行</a>

						</div>

						<div class="centre">
							<input type="submit" name="Save" value="新增提交">
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
			width: '1550px',
			height: 470,
			content:'url:Searchbuliaoso.php?fwValue=<?=$j?>&cat=<?=$_SESSION['Contract' . $identifier]->customer_code?>',
			init:function(){
				this.content.document.getElementById('cat').value = 'buliao';
				this.content.document.getElementById('fwValue').value = '<?=$j?>';
			}
		});
		<?php }?>



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