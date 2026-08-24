<?php
 if(isset($_GET['data3'])){
	 //$sql = "select * from customers where customer_name = '".$_GET['data']."'";
	 include_once("connect.php"); 
	 $sql = "select aa.*, (select bb.price from po_lines_all bb where ((bb.stockid = aa.item_no) and bb.po_line_id in (select max(a.po_line_id) from (po_lines_all a join po_headers_all b) where ((a.po_num = b.po_num)   and (a.stockid = aa.item_no))))) AS last_price from sf_item_no aa where item_no = '".$_GET['data3']."'";
	 $result_num = mysql_query($sql, $db);
	 $res_item = mysql_fetch_assoc($result_num);
	 echo $res_item['item_name'].':'.$res_item['item_desc'].':'.$res_item['units'].':'.$res_item['last_price'];
	 return ;
 }

/*
* To change this license header, choose License Headers in Project Properties.
* To change this template file, choose Tools | Templates
* and open the template in the editor.
*/
include ('includes/DefinePOUpdateClass.php');
include ('includes/session.inc');
$Title = _('采购单结案');
$ViewTopic = '采购单结案';
$BookMark = '采购单结案';
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

		$sql_num = "select 	count(*) line from po_lines_all where  po_num  = '" . $_POST['po_num']. "'";
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
					if($_POST['stockid'.$i]==''){
						$_POST['stockid'.$i] = 'NULL';
						$bumishu[$i] = 0;
					}
					$line=$line+1;

					$sql = "insert into po_lines_all(po_num,line,line_remark,subinventory_code,uom,price,quantity,stockid,line_amount,status,
						creation_date,created_by,last_update_date,last_updated_by)
						values('".$_POST['po_num']."','".$line."','".$_POST['remark'.$i]."','".$_POST['subinventory_code']."','".$_POST['UOM'.$i]."','".$_POST['unitprice'.$i]."',
						'".$_POST['quantity'.$i]."','".$_POST['stockid'.$i]."','".$lineamount[$i]."',
						'待签核','".$time."','".$_SESSION['UserID']."','".$time."','".$_SESSION['UserID']."') ";

			//echo $sql;

					$result = DB_query($sql,$db);
					$order_amount = $order_amount + $lineamount[$i];
				}
			}
		}
       

		$deletesql1 = "update po_headers_all 
		              set po_all_amount=". $_POST['po_all_amount_new']." ,
					  tax_amount=". $_POST['tax_amount_new']." ,
					  all_line_amount=". $_POST['all_line_amount_new']." ,
					  last_update_date='" . $time . "',
						  last_updated_by='" . $_SESSION['UserID'] . "',
					  status='待签核' 
                       where po_num='" . $_POST['po_num'] . "'";
					  // echo $deletesql1;
        $Resultdelete1 = DB_query($deletesql1, $db);

			
		DB_Txn_Commit($db);
		$msg = '采购单新增行成功！1秒后将跳转回上一页！';
    prnMsg($msg, 'success');
   echo '<meta http-equiv="refresh" content="0; url=' . $RootPath .
                    '/POUpdate2.php?New=Yes&Updatepo_num='. $_POST['po_num'] . '" />';
	}
}

//新增行处理 end

if (isset($_GET['New'])) {

    unset($_SESSION['Contract' . $identifier]);
    $_SESSION['Contract' . $identifier] = new ReceiveRequest();
    if (isset($_GET['Updatepo_num'])) {
        if (!isset($_SESSION['Contract' . $identifier]->po_num) or $_SESSION['Contract' .
            $identifier]->po_num == '') {
            $po_num = $_GET['Updatepo_num'];
            $sql = 'SELECT a.tax_flag,a.po_num, a.status, a.payment_term,note, a.order_date, a.need_date,a.app_remark,a.creation_date,  a.po_all_amount,a.all_line_amount,a.tax_amount,a.tax_rate,b.vendor_name, b.vendor_code,a.tax_name,a.currency_code,a.order_type,a.payment_type,a.version
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
                $_SESSION['Contract' . $identifier]->tax_flag= $myrow['tax_flag'];      
                $_SESSION['Contract' . $identifier]->order_type = $myrow['order_type'];           
                $_SESSION['Contract' . $identifier]->payment_type = $myrow['payment_type'];      
                $_SESSION['Contract' . $identifier]->version = $myrow['version'];       
				 

            }

			$sql = 'SELECT  subinventory_code from po_lines_all WHERE line=1 and  po_num=' . "'" . "$po_num" . "'";
			 //echo $sql;
            $CustResult = DB_query($sql, $db);
            while ($myrow = DB_fetch_array($CustResult)) {               
				$_SESSION['Contract' . $identifier]->header_sub = $myrow['subinventory_code'];
            }
	 

            if (!isset($_POST['Update'])) {
                $sql = 'select po_line_id,po_num,a.line,status,a.stockid,b.item_desc,b.item_name,quantity,price,line_amount,ifnull(quantity_received,0) quantity_received,ifnull(quantity_accepted,0) quantity_accepted,ifnull(quantity_deliveried,0) quantity_deliveried,ifnull(quantity_cancelled,0) quantity_cancelled,a.uom,(select locationname from  locations ll where ll.loccode=a.subinventory_code) subinventory_code,line_remark
				from po_lines_all a,sf_item_no b where a.stockid=b.item_no and po_num = ' . "'" .$_SESSION['Contract' . $identifier]->po_num . "'
				order by a.line"; 
                $resultline = DB_query($sql, $db);
               
            }																																																		
        }
    }
}
if (isset($_POST['Update'])) {
    $InputError = 0;
    $v_date = strtotime(Date('Y-m-d H:i:s'));
   
   $HeaderSQL = "update po_headers_all  
                                 set note= '" . $_POST['note'] . "',
                                     need_date= '" . strtotime($_POST['need_date']) . "',
                                     order_date= '" . strtotime($_POST['order_date']) . "',  
									 po_all_amount='" . $_POST['po_all_amount'] . "',
                                     all_line_amount='" . $_POST['all_line_amount'] . "',
                                     version='" . $_POST['version'] . "',
                                     tax_amount='" . $_POST['tax_amount'] . "',
                                     payment_term='" . $_POST['payment_term'] . "', 
                                     payment_type='" . $_POST['payment_type'] . "', 
                                     tax_rate='" . $_POST['tax_rate'] . "',
                                     tax_name='" . $_POST['tax_name'] . "',
                                     tax_flag='" . $_POST['tax_flag'] . "',
                                     order_type='" . $_POST['order_type'] . "',                                    
                                     status='待签核',
                                     last_update_date='" . $v_date . "',
                                     last_updated_by= '" . $_SESSION['UserID'] .
                    "'
		               where po_num='" . $_POST['po_num'] . "'";
					   echo $HeaderSQL ;
     $Resultdelete1 = DB_query($HeaderSQL, $db);

	 $lineSQL = "update po_lines_all  
                                 set   subinventory_code='" . $_POST['header_sub'] . "', 
                                     last_update_date='" . $v_date . "',
                                     last_updated_by= '" . $_SESSION['UserID'] .
                    "'
		               where po_num='" . $_POST['po_num'] . "'";
     
	 $Resultdelete1 = DB_query($lineSQL, $db);
	   DB_Txn_Commit($db);
 

    prnMsg(_('订单头修改成功！'), 'success');
 
	echo '<meta http-equiv="refresh" content="0; url=' . $RootPath .
                    '/POUpdate2.php?New=Yes&Updatepo_num='. $_POST['po_num'] . '" />';
 
 
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
                    '/POUpdate2.php?New=Yes&Updatepo_num='. $_GET['po_num'] . '" />';

	 } else {
		 $sum_amount=0;
		 $tax_amount=0;
         $all_amount=0;
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
		              set  status='待签核',
					  po_all_amount=". $all_amount."   ,
					  tax_amount =". $tax_amount."   ,
					   all_line_amount=". $sum_amount." , 
					  last_update_date='" . $time . "',
						  last_updated_by='" . $_SESSION['UserID'] . "' 
                       where po_num='" . $_GET['po_num'] . "'";
					  // echo $deletesql1;
        $Resultdelete1 = DB_query($deletesql1, $db);


     
    prnMsg(_('删除成功！'), 'success');
	echo '<meta http-equiv="refresh" content="0; url=' . $RootPath .
                    '/POUpdate2.php?New=Yes&Updatepo_num='. $_GET['po_num'] . '" />';
    //DB_Txn_Commit($db);
   
	 }
}




 

if (isset($_POST['Submit'])) {

    //var_dump($_POST['po_num']);

    isset($_SESSION['num' . $identifier]) or die("no session");
    if ($_SESSION['num' . $identifier] == 400) {
        $_SESSION['num' . $identifier] = 500;
        DB_Txn_Begin($db);
        $InputError = 0;
		  
        $v_date = strtotime(Date('Y-m-d H:i:s'));
        $sumamount = 0.00;
        if ($InputError == 0) {
          $count=null;
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
                                line  ='" . $_POST['line'.$i]  . "',
                                price  ='" . $_POST['price'.$i]  . "',
                                line_amount ='" . $_POST['line_amount'.$i]  . "', 
                                line_remark ='" . $_POST['line_remark'.$i]  . "',
                                last_update_date ='" . $v_date . "',
                                last_updated_by= '" . $_SESSION['UserID'] . "',
                                status= '待签核'
                        where po_line_id='" . $po_line_id . "'  ";
				//echo $linesql;
              $Result = DB_query($linesql, $db); 
			  }
              
           }
        }
             

            if ($count > 0) {
                //var_dump($sumamount);
         
 
		$deletesql1 = "update po_headers_all 
		              set  status='待签核',
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
                $msg = '采购单结案成功！1秒后将跳转回主页！';
                prnMsg($msg, 'success');
               echo '<meta http-equiv="refresh" content="0; url=' . $RootPath .
                    '/POUpdate2.php?New=Yes&Updatepo_num='. $_POST['po_num'] . '" />';
              
            } else {
                prnMsg(_('采购单行无数据，如果确定全部取消请选择：全部取消'), 'error');
                unset($_SESSION['Contract' . $identifier]);
            }
            DB_Txn_Commit($db);
        }
        include ('includes/footer.inc');
        exit;
    } else {
        header('Location: POUpdate2.php');
    }
} elseif (isset($_POST['Cancel'])) {
    $po_num = $_SESSION['Contract' . $identifier]->po_num;
	$v_date =time();
    $HeaderSQL = "update po_headers_all   
                          set status= '已取消', last_update_date='" . $v_date . "',
						  last_updated_by='" . $_SESSION['UserID'] . "'
		        where po_num='" . $po_num . "'";
    $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') .
        ': ' . _('数据存入数据库，原因是：'); //'" . FormatDateForSQL($_SESSION['Contract'.$identifier]->DispatchDate) . "',
    $DbgMsg = _('The following SQL to insert the request header record was used');
    $Result = DB_query($HeaderSQL, $db, $ErrMsg, $DbgMsg, true);
    $HeaderSQL2 = "update po_lines_all 
                          set status= '已取消',
                              quantity=0, last_update_date='" . $v_date . "',
						  last_updated_by='" . $_SESSION['UserID'] . "'
		        where po_num='" . $po_num . "'";
    $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') .
        ': ' . _('数据存入数据库，原因是：'); //'" .  
    $DbgMsg = _('The following SQL to insert the request header record was used');
    $Result2 = DB_query($HeaderSQL2, $db, $ErrMsg, $DbgMsg, true);

    $msg = '采购单取消成功！1秒后将跳转回上一页！';
    prnMsg($msg, 'success');
     echo '<meta http-equiv="refresh" content="0; url=' . $RootPath .
                    '/POUpdate2.php?New=Yes&Updatepo_num='. $_POST['po_num'] . '" />';
    exit;
} elseif (isset($_POST['closed'])) {
	$v_date =time();
    $po_num = $_SESSION['Contract' . $identifier]->po_num;
    $HeaderSQL = "update po_headers_all   
                          set status= '已关闭', last_update_date='" . $v_date . "',
						  last_updated_by='" . $_SESSION['UserID'] . "'
		        where po_num='" . $po_num . "'";
    $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') .
        ': ' . _('数据存入数据库，原因是：'); //'" . FormatDateForSQL($_SESSION['Contract'.$identifier]->DispatchDate) . "',
    $DbgMsg = _('The following SQL to insert the request header record was used');
    $Result = DB_query($HeaderSQL, $db, $ErrMsg, $DbgMsg, true);
    $HeaderSQL2 = "update po_lines_all 
                          set status= '已关闭',
                                last_update_date='" . $v_date . "',
						  last_updated_by='" . $_SESSION['UserID'] . "'
		        where po_num='" . $po_num . "'";
    $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') .
        ': ' . _('数据存入数据库，原因是：'); //'" .  
    $DbgMsg = _('The following SQL to insert the request header record was used');
    $Result2 = DB_query($HeaderSQL2, $db, $ErrMsg, $DbgMsg, true);

    $msg = '采购单取消成功！1秒后将跳转回上一页！';
    prnMsg($msg, 'success');

    exit;
} else {
    session_start() or die("session is not started");
    $_SESSION['num' . $identifier] = 400;
}
echo '	<div class="centre">
		<a href="' . $RootPath . '/POClose.php">返回重新选择采购单</a>
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
 

 
$v_need_date = date('Y-m-d', $_SESSION['Contract' . $identifier]->need_date);  
$v_create_date = date('Y-m-d', $_SESSION['Contract' . $identifier]->create_date);
$v_order_date = date('Y-m-d', $_SESSION['Contract' . $identifier]->order_date);  
echo '<div class="text-nav">
		<div class="text-nav-1"><div>' . _('采购单') . ':</div><input type="text" readonly="readonly" value="' . $_SESSION['Contract' . $identifier]->po_num . '" /></div> <input type="hidden" class="text"  name="po_num" value="' . $_SESSION['Contract' . $identifier]->po_num . '" />
<div class="text-nav-1"><div>创建日期：</div>' . '<input type="text" readonly="readonly" value="' . $v_create_date . '" /></div>
<div class="text-nav-1"><div>状态：</div>' . '<input type="text" readonly="readonly" value="' . $_SESSION['Contract' . $identifier]->status . '" /></div>';

echo '<div class="text-nav-1"><div>采购日期：</div>' .
    '<input type="text" onfocus="WdatePicker()"  name="order_date" maxlength="10" size="11" value="' .
    $v_order_date . '" /></div>
       
	<div class="text-nav-1"><div>版本：</div>
      <input type="text"  name="version" maxlength="2" size="11" value="' .
    $_SESSION['Contract' . $identifier]->version  . '" /></div>
	<div class="text-nav-1"><div>需求日期：</div>
      <input type="text" onfocus="WdatePicker()"  name="need_date" maxlength="10" size="11" value="' .
    $v_need_date . '" /></div>
	<div class="text-nav-1"><div>' . _('供应商') . ':</div>
    <input type="text" readonly="readonly" value="' . $_SESSION['Contract' . $identifier]->vendor_code . '" /></div> 
	<div class="text-nav-1"><div>' . _('供应商') . ':</div>
    <input type="text" readonly="readonly" value="' . $_SESSION['Contract' . $identifier]->vendor_name . '" /></div> ';
  /*  <div class="text-nav-1"><div>' . _('仓库') . ':</div>
	 
			<select name="header_sub" id="">'; 
				 
					$sql = "select loccode,locationname from  locations  order by paixu";
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
*/
echo '  <div class="text-nav-1"><div>税别</div>
	     <input type="text"  class="number" size="11" name="tax_name" id="text_slect_tax_name"  value="' . $_SESSION['Contract' . $identifier]->tax_name . '" />
			<image class="select_img" src="img/search.png" id="btn_slect_tax_name"/></div>
	  <div class="text-nav-1"><div>税率</div>
	  <input type="text" class="number" size="11" name="tax_rate" id="text_slect_tax_rate"    value="' . round($_SESSION['Contract' . $identifier]->tax_rate,2) . '" /> 
			</div>
		 ';
 
  echo '<div class="text-nav-1"><div>' . _('是否含税') . '</div>

			<select name="tax_flag" id="text_slect_tax_flag">';
 	
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
			 <input type="text" readonly="readonly" class="number" size="11" name="all_line_amount" id="all_line_amount" value="' . round($_SESSION['Contract' . $identifier]->all_line_amount,2) . '" /> </div>  

	   <div class="text-nav-1"><div>税金</div>   
			 <input type="text" readonly="readonly" class="number" size="11" name="tax_amount" id="tax_amount" value="' . $_SESSION['Contract' . $identifier]->tax_amount . '" /> </div> 
			<div class="text-nav-1"><div>含税金额</div> 
		  <input type="text" readonly="readonly" class="number" size="11" name="po_all_amount" id="po_all_amount" value="' . $_SESSION['Contract' . $identifier]->po_all_amount . '" /> </div> 
		 
			
			
					
			'; 
 
echo ' 
         <div class="text-nav-1"><div>付款条件</div>      
			<input type="text"  size="11" name="payment_term" id="payment_term" value="' . $_SESSION['Contract' . $identifier]->payment_term . '" /> </div>
			<div class="text-nav-1"><div>' . _('付款方式') . ':</div>
			<input type="text"  size="11" name="payment_type" id="payment_type" value="' . $_SESSION['Contract' . $identifier]->payment_type . '" /> </div>
           ';
 
echo '  
<div class="text-nav-2"><div>采购单备注</div>   
          <input type="text"  name="note"   maxlength="100" size="80"  value="'. $_SESSION['Contract' .
    $identifier]->note . '"/></div>
        </tr>';

		echo '<div class="text-nav-1"><div>' . _('订单类型') . ':</div>

<select name="order_type" id="text_slect_order_type">';

		$sql = "select order_type from po_order_type order by order_type_id";
		$result = DB_query($sql,$db);
		while ($v = DB_fetch_array($result)) {
			if ($v['order_type']==$_SESSION['Contract' . $identifier]->order_type ) {

	echo '<option value="'.$v['order_type'].'" selected="selected">'.$v['order_type'].'</option>';
		}else{
	echo '<option value="'.$v['order_type'].'">'.$v['order_type'].'</option>';
				}
		}

echo '</select></div>';
 

echo ' <div class="text-nav-2"><div>审核备注</div> 
			<input type="text"  name="app_remark" pattern="^[^?.\+<>!&’:,;?$\^]+$" maxlength="10" size="11" value="' . $_SESSION['Contract' . $identifier]->app_remark . '" />
              </div>  
   <td  >' . _('上传新附件') . ':</td>
			  <td><a href="' . $RootPath . '/POUploadNewfile.php?OrderNum=' . $_SESSION['Contract' . $identifier]->po_num . '"target="_blank">上传 </td>

			<td>  <input type="hidden" name="po_all_amount_old" id="po_all_amount_old" value="'. $_SESSION['Contract'.$identifier]->po_all_amount . '"/> 
			<input type="hidden" name="tax_amount_old" id="tax_amount_old_old" value="'. $_SESSION['Contract'.$identifier]->tax_amount_old . '"/>
			<input type="hidden" name="all_line_amount_old" id="all_line_amount_old" value="'. $_SESSION['Contract'.$identifier]->all_line_amount . '"/></td>
        </tr>';
    
echo '</table>';

echo '<div class="centre">

	<input type="submit" name="closed" value="' . _('关闭') . '" />
	</div>
    </div>
	</form>';

if (!isset($_SESSION['Contract' . $identifier]->po_num)) {
    include ('includes/footer.inc');
    exit;
}

$i = 0; //Line Item Array pointer
echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES,
    'UTF-8') . '" method="post"><input type="hidden" name = "identifier" value ="' .
    $identifier . '">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
//用隐藏域保存采购单表号
echo ' <input type="hidden" class="text"  name="po_num" value="' . $_SESSION['Contract' . $identifier]->po_num . '" />';
 
echo ' <input type="hidden" class="text"  name="po_all_amount" id="po_all_amount2" value="' . $_SESSION['Contract' . $identifier]->po_all_amount . '" />';
echo ' <input type="hidden" class="text"  name="all_line_amount" id="all_line_amount2"  value="' . $_SESSION['Contract' . $identifier]->all_line_amount . '" />';
echo ' <input type="hidden" class="text"  name="tax_amount" id="tax_amount2" value="' . $_SESSION['Contract' . $identifier]->tax_amount . '" />';
echo '<br /> ';
  

echo '<div class="text-nav-table"> 
<table class="selection">
	<tr>
		<th>' . _('行') . '</th>
		<th   width=190>' . _('料号') . '</th>
		<th   width=150>' . _('名称') . '</th>	 
		<th   width=220>' . _('规格型号') . '</th>
		<th   width=50>' . _('单位') . '</th>  
                    <th   width=80>' . _('入库数量') . '</th> 
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

	 echo '<td><input type="text"  name="line'.$i.'" class="number" size="3"  value="' . $myrow['line']  . '" /></td> 
	        <td>' . $myrow['stockid'] . '</td>
			<td>' . $myrow['item_name'] . '</td>
			<td>' . $myrow['item_desc'] . '</td>'; 
          echo '   <td>' . $myrow['uom']  . '</td> 
			<td>' . $myrow['quantity_received']  . '</td> ';
       echo ' <td><input id="quantity' .$i.'" onblur="checkall()" onkeyup="webdesign(' .$i.')"   style="background-color:yellow" type="text"  name="quantity'.$i.'" class="number" size="8"  value="' . $myrow['quantity']  . '" /></td> ';
	   echo ' <td><input id="price' .$i.'" onblur="checkall()"  onkeyup="webdesign(' .$i.')" style="background-color:yellow" type="text"  name="price'.$i.'" class="number" size="8"  value="' . sprintf("%.5f",$myrow['price']) . '" /></td> ';
	   echo ' <td><input id="line_amount' .$i.'" readonly="readonly" style="background-color:yellow" type="text"  name="line_amount'.$i.'" class="number" size="8"  value="' . $myrow['line_amount']  . '" />  
	   <td><input id="line_remark' .$i.'"  type="text"  name="line_remark'.$i.'"  size="10"  value="' . $myrow['line_remark']  . '" /> 
     
	   
	   <input type="hidden" name="po_line_id'.$myrow['po_line_id'].'" value="'.$i.'" /></td> ';
    

       echo ' 
     </tr>';            
      $i++;
	echo ' </tr>';

}
echo '</table></div>
   
	

       
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
                      var tax_rate=document.getElementById("text_slect_tax_rate").value;
                      var tax_flag=document.getElementById("text_slect_tax_flag").value;
					  var all_rate = Number(1) + Number(tax_rate) ;	
                                for(var j=1 ; j < 150; j++){   
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
     { var all_line_amount_old=document.getElementById("all_line_amount_old").value; 
		 var no_tax_amount=Number(all_line_amount_old) + Number(allamount)    ; 
		var tax_amount=Number(no_tax_amount) * Number(tax_rate);
		var han_tax_amount=Number(no_tax_amount) + Number(tax_amount)  ;	
     } else {
		 var po_all_amount_old=document.getElementById("po_all_amount_old").value; 
		 var all_amount=Number(allamount) + Number(po_all_amount_old) 
        var no_tax_amount= Number(all_amount)/ Number(all_rate) ; 
		var tax_amount=Number(all_amount) - Number(no_tax_amount);
		var han_tax_amount=Number(all_amount) ;		
	 }						
		
		 
		document.getElementById("all_line_amount").value= Math.round(Number(no_tax_amount)*100)/100;
       document.getElementById("po_all_amount").value=Math.round(Number(han_tax_amount)*100)/100;
       document.getElementById("tax_amount").value=Math.round(Number(tax_amount)*100)/100;
	   document.getElementById("all_line_amount_new").value=Math.round(Number(no_tax_amount)*100)/100;
	   document.getElementById("po_all_amount_new").value= Math.round(Number(han_tax_amount)*100)/100;
       document.getElementById("tax_amount_new").value=Math.round(Number(tax_amount)*100)/100; 
     

  }


 function checkall(){                               
                      var allamount=0; 
					  var tax_rate=document.getElementById("text_slect_tax_rate").value;
					  var tax_flag=document.getElementById("text_slect_tax_flag").value;
					  var all_rate = Number(1) + Number(tax_rate) ;	
                                
                                for(var i=1 ; i < 150; i++){   
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

        if (tax_flag=='N')
     {
		 var no_tax_amount= allamount ; 
		var tax_amount=Number(allamount) * Number(tax_rate);
		var han_tax_amount=Number(no_tax_amount) + Number(tax_amount)  ;	
     } else {
        var no_tax_amount= Number(allamount)/ Number(all_rate) ; 
		var tax_amount=Number(allamount) - Number(no_tax_amount);
		var han_tax_amount=Number(allamount)  ;		
	 }
		document.getElementById("po_all_amount").value= Math.round(Number(han_tax_amount)*100)/100;
       document.getElementById("tax_amount").value=Math.round(Number(tax_amount)*100)/100;
       document.getElementById("all_line_amount").value=Math.round(Number(no_tax_amount)*100)/100; 
	   document.getElementById("po_all_amount2").value= Math.round(Number(han_tax_amount)*100)/100;
       document.getElementById("tax_amount2").value=Math.round(Number(tax_amount)*100)/100;
       document.getElementById("all_line_amount2").value=Math.round(Number(no_tax_amount)*100)/100;
  
  
 

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
		<?php for($j=1;$j<=150;$j++){?>
		$('#btn_slect_buliao<?=$j?>').dialog({
			title:'选择料号',
			width: '800px',
			height: 470,
			content:'url:Searchbuliaopo.php?fwValue=<?=$j?>&cat=buliao',
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
		$('#btn_slect_tax_name').dialog({
            title:'选择税别',
            width: '550px',
            height: 470,
            content:'url:BtnSearchtax.php?fwValue=&cat=buliao',
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

	
$(function(){
		$( "#text_slect_vendor" ).autocomplete({
			source: "autosearchvendor.php",
			minLength: 2,
			autoFocus: true
		});
	});
	$(function(){
		$( "#text_slect_name" ).autocomplete({
			source: "autosearchvendor2.php",
			minLength: 2,
			autoFocus: true
		});
	});
	<?php for($j=1;$j<=150;$j++){?> 
	$(function(){
		$( "#text_slect_buliao<?=$j?>" ).autocomplete({
			source: "autosearchstockpo.php",
			minLength: 2,
			autoFocus: true
		});
	});
	<?php }?>



	 

function sel_item(s1){
		var name=$('#text_slect_buliao'+s1).val()
		$.get("","data3="+name,function(res_item){
			name = res_item.split(":")		  
				$("#text_slect_item_name"+s1).val(name[0])
				$("#text_slect_item_spec"+s1).val(name[1])
				$("#text_slect_units"+s1).val(name[2]) 
				$("#text_slect_last_price"+s1).val(name[3]) 
				$("#text_slect_unit_price"+s1).val(name[3]) 
		})	
				 
	      document.getElementById("quantity"+s1).focus();
	}  
	 
                     
   
</script>
</body>
</html>
<?php
include ('includes/footer.inc');
?>