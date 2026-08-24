<?php
 if(isset($_GET['data3'])){
	 //$sql = "select * from customers where customer_name = '".$_GET['data']."'";
	 include_once("connect.php"); 
	 $sql = "select * from sf_item_no_v where item_category1<>'成品料号' and item_no = '".$_GET['data3']."'";
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
include ('includes/DefinePRUpdateClass.php');
include ('includes/session.inc');
$Title = _('请购单修改');
$ViewTopic = '请购单修改';
$BookMark = '请购单修改';
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

		$sql_num = "select 	count(*) line from pr_lines_all where  pr_num  = '" . $_POST['pr_num']. "'";
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
					if($_POST['unitprice'.$i]==''){
						$_POST['unitprice'.$i] = 0;
						$bumishu[$i] = 0;
					}
					$line=$line+1;

					$sql = "insert into pr_lines_all(pr_num,line,remark,uom,price,quantity,stockid,line_amount,status,
						creation_date,created_by,last_update_date,last_updated_by)
						values('".$_POST['pr_num']."','".$line."','".$_POST['remark'.$i]."','".$_POST['UOM'.$i]."','".$_POST['unitprice'.$i]."',
						'".$_POST['quantity'.$i]."','".$_POST['stockid'.$i]."','".$lineamount[$i]."',
						'INPROCESS','".$time."','".$_SESSION['UserID']."','".$time."','".$_SESSION['UserID']."') ";

			//echo $sql;

					$result = DB_query($sql,$db);
					$order_amount = $order_amount + $lineamount[$i];
				}
			}
		}
       

		$deletesql1 = "update pr_headers_all 
		              set all_amount=". $_POST['all_amount_new']." , 
					  last_update_date='" . $time . "',
						  last_updated_by='" . $_SESSION['UserID'] . "',
					  status='INPROCESS' 
                       where pr_num='" . $_POST['pr_num'] . "'";
					  // echo $deletesql1;
        $Resultdelete1 = DB_query($deletesql1, $db);

			
		DB_Txn_Commit($db);
		$msg = '请购单新增行成功！1秒后将跳转回上一页！';
    prnMsg($msg, 'success');
   echo '<meta http-equiv="refresh" content="0; url=' . $RootPath .
                    '/PRUpdate2.php?New=Yes&Updatepo_num='. $_POST['pr_num'] . '" />';
	}
}

//新增行处理 end

if (isset($_GET['New'])) {

    unset($_SESSION['Contract' . $identifier]);
    $_SESSION['Contract' . $identifier] = new ReceiveRequest();
    if (isset($_GET['Updatepo_num'])) {
        if (!isset($_SESSION['Contract' . $identifier]->pr_num) or $_SESSION['Contract' .
            $identifier]->pr_num == '') {
            $pr_num = $_GET['Updatepo_num'];
            $sql = 'SELECT a.pr_num, a.status, a.depart_name,remark,  a.need_date, a.creation_date,  a.all_amount  ,a.pr_use
FROM pr_headers_all a 
WHERE   pr_num=' . "'" . "$pr_num" . "'";

            $CustResult = DB_query($sql, $db);
            while ($myrow = DB_fetch_array($CustResult)) {
                $_SESSION['Contract' . $identifier]->pr_num = $myrow['pr_num'];
                $_SESSION['Contract' . $identifier]->status = $myrow['status']; 
                $_SESSION['Contract' . $identifier]->need_date = $myrow['need_date']; 
                $_SESSION['Contract' . $identifier]->create_date = $myrow['creation_date'];
                $_SESSION['Contract' . $identifier]->depart_name = $myrow['depart_name'];
                $_SESSION['Contract' . $identifier]->remark = $myrow['remark'];  
                $_SESSION['Contract' . $identifier]->all_amount = $myrow['all_amount'];     
                $_SESSION['Contract' . $identifier]->pr_use = $myrow['pr_use'];     
				 

            }

			 
	 

            if (!isset($_POST['Update'])) {
                $sql = 'select line_id,pr_num,a.line,status,a.stockid,b.item_desc,b.item_name,quantity,price,line_amount,a.uom,remark
				from pr_lines_all a,sf_item_no b where a.stockid=b.item_no and pr_num = ' . "'" .$_SESSION['Contract' . $identifier]->pr_num . "'
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
   $HeaderSQL = "update pr_headers_all  
                                 set remark= '" . $_POST['remark'] . "',
                                     need_date= '" . $need_date . "', 
									 depart_name= '" . $_POST['depart_name'] . "', 
									 all_amount='" . $_POST['all_amount'] . "',                               
									 pr_use='" . $_POST['pr_use'] . "',                              
                                     status='INPROCESS',
                                     last_update_date='" . $v_date . "',
                                     last_updated_by= '" . $_SESSION['UserID'] .
                    "'
		               where pr_num='" . $_POST['pr_num'] . "'";
     $Resultdelete1 = DB_query($HeaderSQL, $db);

	 $lineSQL = "update pr_lines_all  
                                 set   subinventory_code='" . $_POST['header_sub'] . "', 
                                     last_update_date='" . $v_date . "',
                                     last_updated_by= '" . $_SESSION['UserID'] .
                    "'
		               where pr_num='" . $_POST['pr_num'] . "'";
     
	 $Resultdelete1 = DB_query($lineSQL, $db);
	   DB_Txn_Commit($db);
 

    prnMsg(_('订单头修改成功！'), 'success');
 
	echo '<meta http-equiv="refresh" content="0; url=' . $RootPath .
                    '/PRUpdate2.php?New=Yes&Updatepo_num='. $_POST['pr_num'] . '" />';
 
 
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
if (isset($_POST['DeleteOrder'])) {
 
	$so_order_line=0;
    $sql = "select count(*)  so_order_line
	            from pr_lines_all    
		        where pr_num='" . $_POST['pr_num'] . "' and prtopo_quantity>0 "; 
    $result = DB_query($sql, $db);
    while  ($myrow = DB_fetch_array($result)) {
	  $so_order_line= $myrow['so_order_line'];
	}
    if ($so_order_line>0 ) {
       $msg = '请购单已转采购单不能删除！';
       prnMsg($msg, 'error'); 
	   echo '<meta http-equiv="refresh" content="0.5; url=' . $RootPath .'/PRUpdate2.php?New=Yes&Updatepo_num=' . $_POST['pr_num'] . '" />';
	
	}else {

       
    $HeaderSQL = "delete from pr_lines_all where pr_num='" . $_POST['pr_num'] . "'";
    
    $Result = DB_query($HeaderSQL, $db, $ErrMsg, $DbgMsg, true);

       

    $HeaderSQL2 = "delete from pr_headers_all where pr_num='" . $_POST['pr_num'] . "'";
     
    $Result2 = DB_query($HeaderSQL2, $db, $ErrMsg, $DbgMsg, true);

    $msg = '请购单删除！';
    prnMsg($msg, 'success');
    echo '<meta http-equiv="refresh" content="1; url=' . $RootPath .'/PRUpdate.php" />';
    echo '<br />';
    include ('includes/footer.inc');
    exit;
	}
}

 
if (isset($_GET['delete'])   ) {
   $time = time();
	$sql2 = "select count(*) as count from pr_lines_all   where  pr_num= '" . $_GET['pr_num'] . "' 
	   and po_line  = '" . $_GET['po_line'] . "' and prtopo_quantity>0  ";
	  $result = DB_query($sql2,$db);
	  $row=DB_fetch_array($result);
	 if($row['count']>0){
		 
		   
          prnMsg(_('请购单行已转采购单,不能删除'), 'error');
		 
		  echo '<meta http-equiv="refresh" content="0; url=' . $RootPath .
                    '/PRUpdate2.php?New=Yes&Updatepo_num='. $_GET['pr_num'] . '" />';

	 } else {

       $sql = "delete from pr_lines_all   where  line_id= '" . $_GET['line_id'] . "' ";
	   $result = DB_query($sql,$db);

	  $sql3 = "select sum(line_amount) sum_amount from pr_lines_all where pr_num= '" . $_GET['pr_num'] . "' ";
	   //echo $sql3;
	   $result3 = DB_query($sql3,$db);
	   while ($row=DB_fetch_array($result3)) {
	     $sum_amount=$row['sum_amount'];
	   }
 

	   $deletesql1 = "update pr_headers_all 
		              set all_amount=". $sum_amount." ,
					  status='INPROCESS',  
					  last_update_date='" . $time . "',
						  last_updated_by='" . $_SESSION['UserID'] . "',
					  status='INPROCESS' 
                       where pr_num='" . $_GET['pr_num'] . "'";
					  // echo $deletesql1;
        $Resultdelete1 = DB_query($deletesql1, $db);


     
    prnMsg(_('删除成功！'), 'success');
	echo '<meta http-equiv="refresh" content="0; url=' . $RootPath .
                    '/PRUpdate2.php?New=Yes&Updatepo_num='. $_GET['pr_num'] . '" />';
    //DB_Txn_Commit($db);
   
	 }
}




 

if (isset($_POST['Submit'])) {

    //var_dump($_POST['pr_num']);

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
		
           if (mb_substr($key,0,7)=='line_id') {
              $line_id =mb_substr($key,7);
			  $i = $_POST[$key];   
			//  echo $line_id ;
               //var_dump($i);
			  if ( $line_id>0 ) {
              //var_dump( $_POST['amount'.$line]);    
              $count = $count + 1;
              $linesql = "UPDATE pr_lines_all " . " 
			  set quantity=  " . $_POST['quantity'.$i] . ",
                                price  ='" . $_POST['price'.$i]  . "',
                                line_amount ='" . $_POST['line_amount'.$i]  . "', 
                                remark ='" . $_POST['remark'.$i]  . "',
                                last_update_date ='" . $v_date . "',
                                last_updated_by= '" . $_SESSION['UserID'] . "',
                                status= 'INPROCESS'
                        where line_id='" . $line_id . "'  ";
				//echo $linesql;
              $Result = DB_query($linesql, $db); 
			  }
              
           }
        }
             

            if ($count > 0) {
                //var_dump($sumamount);
         
 
		$deletesql1 = "update pr_headers_all 
		              set  status='INPROCESS', 
	                      all_amount='".$_POST['all_amount']."' ,  
						  last_update_date='" . $v_date . "',
						  last_updated_by='" . $_SESSION['UserID'] . "'
                       where pr_num='" . $_POST['pr_num']. "'"; 

                $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') .
                    ': ' . _('数据存入数据库，原因是：'); //'" . FormatDateForSQL($_SESSION['Contract'.$identifier]->DispatchDate) . "',
                $DbgMsg = _('The following SQL to insert the request header record was used');
                $Result = DB_query($deletesql1, $db, $ErrMsg, $DbgMsg, true);
                DB_Txn_Commit($db);
                $msg = '请购单修改成功！1秒后将跳转回主页！';
                prnMsg($msg, 'success');
               echo '<meta http-equiv="refresh" content="0; url=' . $RootPath .'/PRUpdate2.php?New=Yes&Updatepo_num='. $_POST['pr_num'] . '" />';
              
            } else {
                prnMsg(_('请购单行无数据，如果确定全部取消请选择：全部取消'), 'error');
                unset($_SESSION['Contract' . $identifier]);
            }
            DB_Txn_Commit($db);
        }
        include ('includes/footer.inc');
        exit;
    } else {
        header('Location: PRUpdate2.php');
    }
} elseif (isset($_POST['Cancel'])) {
    $pr_num = $_SESSION['Contract' . $identifier]->pr_num;
    $HeaderSQL = "update pr_headers_all   
                          set status= 'Cancel', last_update_date='" . $v_date . "',
						  last_updated_by='" . $_SESSION['UserID'] . "'
		        where pr_num='" . $pr_num . "'";
    $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') .
        ': ' . _('数据存入数据库，原因是：'); //'" . FormatDateForSQL($_SESSION['Contract'.$identifier]->DispatchDate) . "',
    $DbgMsg = _('The following SQL to insert the request header record was used');
    $Result = DB_query($HeaderSQL, $db, $ErrMsg, $DbgMsg, true);
    $HeaderSQL2 = "update pr_lines_all 
                          set status= 'Cancel',
                              quantity=0, last_update_date='" . $v_date . "',
						  last_updated_by='" . $_SESSION['UserID'] . "'
		        where pr_num='" . $pr_num . "'";
    $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') .
        ': ' . _('数据存入数据库，原因是：'); //'" . FormatDateForSQL($_SESSION['Contract'.$identifier]->DispatchDate) . "',
    $DbgMsg = _('The following SQL to insert the request header record was used');
    $Result2 = DB_query($HeaderSQL2, $db, $ErrMsg, $DbgMsg, true);

    $msg = '请购单取消成功！1秒后将跳转回上一页！';
    prnMsg($msg, 'success');
    echo '<meta http-equiv="refresh" content="1; url=' . $RootPath .
        '/PRUpdate.php" />';
    echo '<br />';
    include ('includes/footer.inc');
    exit;
} else {
    session_start() or die("session is not started");
    $_SESSION['num' . $identifier] = 400;
}
echo '	<div class="centre">
		<a href="' . $RootPath . '/PRUpdate.php">返回重新选择请购单</a>
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
$v_need_date = date('Y-m-d', $_SESSION['Contract' . $identifier]->need_date); 
$v_need_date = date('Y-m-d', $_SESSION['Contract' . $identifier]->need_date);
$v_create_date = date('Y-m-d', $_SESSION['Contract' . $identifier]->create_date);
echo '<div class="text-nav">
		<div class="text-nav-1"><div>' . _('请购单号') . ':</div><input type="text" readonly="readonly" value="' . $_SESSION['Contract' . $identifier]->pr_num . '" /></div> <input type="hidden" class="text"  name="pr_num" value="' . $_SESSION['Contract' . $identifier]->pr_num . '" />
<div class="text-nav-1"><div>创建日期：</div>' . '<input type="text" readonly="readonly" value="' . $v_create_date . '" /></div>
<div class="text-nav-1"><div>状态：</div>' . '<input type="text" readonly="readonly" value="' . $v_status . '" /></div>';

echo ' 
       
	<div class="text-nav-1"><div>期望到货日期：</div>
      <input type="text" onfocus="WdatePicker()"  name="need_date" maxlength="10" size="11" value="' .
    $v_need_date . '" /></div>
 
    <div class="text-nav-1"><div>' . _('使用部门') . ':</div>
	 
			<select name="depart_name" id="">'; 
				 
					$sql = "select depart_name from  hr_departs ";
					$result = DB_query($sql,$db);
					while ($v = DB_fetch_array($result)) {
						if ($v['depart_name']==$_SESSION['Contract' . $identifier]->depart_name ) {
				 
				echo '<option value="'.$v['depart_name'].'" selected="selected">'.$v['depart_name'].'</option>';
				  }else{ 
				echo '<option value="'.$v['depart_name'].'">'.$v['depart_name'].'</option>';
				 		}
					}
				 
			echo '</select>
		</div>';
?>
		<div class="text-nav-1 "><div>采购用途</div>

		<select name="pr_use" id="text_slect_pr_use">
				<?php
				
						if ($_SESSION['Contract' . $identifier]->pr_use == '研发') {
				?>
					<option value="研发" selected="selected">研发</option>
					<option value="生产" >生产</option>
				<?php }else{?>
					<option value="生产" selected="selected">生产</option>
					<option value="研发" >研发</option>
				<?php		}
					
				?>
			</select>
	</div>
		<div class="text-nav-1 required"><div>预计总金额（元）</div>
		<input type="text" name="all_amount" maxlength="20" size="10" onkeyup="this.value= this.value.match(/\d+(\.\d{0,2})?/) ? this.value.match(/\d+(\.\d{0,2})?/)[0] : ''" readonly="readonly" value="<?=$_SESSION['Contract' . $identifier]->all_amount?>" id="all_amount" ></div>
 
<?php
  
echo '  
<div class="text-nav-2"><div>请购单备注</div>   
          <input type="text"  name="remark"   maxlength="100" size="80"  value="'. $_SESSION['Contract' .
    $identifier]->remark . '"/></div>
        ';

 
 
 


echo '     <input type="hidden" name="all_amount_old" id="all_amount_old" value="'. $_SESSION['Contract'.$identifier]->all_amount . '"/>   
      ';
    
echo '</table>';

echo '<div class="centre">
	<input type="submit" name="Update" value="' . _('请购单头修改保存') . '" />&nbsp;&nbsp;&nbsp;
	
	<input type="submit" name="DeleteOrder" value="' . _('删除请购单') . '" />
	</div>
    </div>
	</form>';

if (!isset($_SESSION['Contract' . $identifier]->pr_num)) {
    include ('includes/footer.inc');
    exit;
}

$i = 0; //Line Item Array pointer
echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES,
    'UTF-8') . '" method="post"><input type="hidden" name = "identifier" value ="' .
    $identifier . '">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
//用隐藏域保存请购单表号
echo ' <input type="hidden" class="text"  name="pr_num" value="' . $_SESSION['Contract' . $identifier]->pr_num . '" />';
 
echo ' <input type="hidden" class="text"  name="all_amount" id="all_amount2" value="' . $_SESSION['Contract' . $identifier]->all_amount . '" />';  
echo '<br /> ';
  

echo '<table class="selection">
	<tr>
		<th>' . _('行') . '</th>
		<th   width=190>' . _('料号') . '</th>
		<th   width=150>' . _('名称') . '</th>	 
		<th   width=220>' . _('规格型号') . '</th>
		<th   width=50>' . _('单位') . '</th>   
                 <th   width=80>' . _('数量') . '</th>
                 <th   width=80>' . _('预估单价') . '</th>
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
	        <td>' . $myrow['stockid'] . '</td>
			<td>' . $myrow['item_name'] . '</td>
			<td>' . $myrow['item_desc'] . '</td>'; 
          echo '   <td>' . $myrow['uom']  . '</td>   ';
       echo ' <td><input id="quantity' .$i.'" onblur="checkall()" onkeyup="webdesign(' .$i.')"   style="background-color:yellow" type="text"  name="quantity'.$i.'" class="number" size="8"  value="' . sprintf("%.2f",$myrow['quantity'])  . '" /></td> ';
	   echo ' 
	   <td><input id="price' .$i.'" onblur="checkall()"  onkeyup="webdesign(' .$i.')" style="background-color:yellow" type="text"  name="price'.$i.'" class="number" size="8"  value="' . sprintf("%.2f",$myrow['price'])  . '" /></td>

	   <td><input id="line_amount' .$i.'" readonly="readonly"   type="text"  name="line_amount'.$i.'" class="number" size="8"  value="' . sprintf("%.2f",$myrow['line_amount'])  . '" /></td> 
		';
	   echo '
          <td><input  style="background-color:yellow" type="text"  name="remark'.$i.'"  size="12"  value="' . $myrow['remark']  . '" /> </td>
	   
	  <td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?pr_num=' .$_SESSION['Contract' . $identifier]->pr_num .'&line_id=' .$myrow['line_id'] . '&amp;delete=1" onclick="return confirm(\'' . _('是否要删除这个料号?') . '\');">' . _('删除')  . '</a></td>

	   <input type="hidden" name="line_id'.$myrow['line_id'].'" value="'.$i.'" /></td> ';
	 

       echo ' 
     </tr>';            
      $i++;
	echo ' </tr>';

}
echo '</table>
   
	
	<div class="centre">
                <input type="submit" id="submit" name="Submit" value="' . _('请购单行修改保存') .
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
								
		var all_amount_old=document.getElementById("all_amount_old").value; 
		var  all_amount= parseFloat(allamount)+parseFloat(all_amount_old);  
        		 
       document.getElementById("all_amount").value=Math.round(Number(all_amount)*100)/100;  
	   document.getElementById("all_amount_new").value= Math.round(Number(all_amount)*100)/100; 
     

  }


 function checkall(){                               
                      var allamount=0; 
                                
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

         	
		document.getElementById("all_amount").value= Math.round(Number(allamount)*100)/100; 
	   document.getElementById("all_amount2").value= Math.round(Number(allamount)*100)/100; 
  
 

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

<div id="CanvasDiv">
	<div id="BodyDiv">
		<div id="BodyWrapDiv">
			<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="新增请购单行" alt="新增请购单行">新增请购单行</p>
			<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST"><input type="hidden" name="time" value="<?=$time?>">
				<div>
			 
				 <input readonly="readonly" type="hidden"   name="pr_num"  value="<?=$_SESSION['Contract' . $identifier]->pr_num?>" size="55" maxlength="46"/> 
				 <td>  <input type="hidden" name="all_amount_new" id="all_amount_new" value="<?=$_SESSION['Contract' . $identifier]->all_amount?>"/> </td>
					<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
					 
					 
					<input type="hidden" name="PageOffset" value="1"/><br/>
					<?php
					if (isset($_SESSION['Contract' . $identifier]->pr_num) and $_SESSION['Contract' . $identifier]->pr_num != '') {
						?>
						<input id="purchase_table_lastRow" name="purchase_table_lastRow" type=hidden value="">

						<div class="centre"> 
                        <p id="Prompt" style="color: red;font-size: 20px"></p>
                    </div>

						<table id="purchase_table" cellpadding="2" class="selection">
							<tr id="list-top">
								<th  bgcolor="#87CEFA" width="230">料号</th>
								<th width="100">名称</th> 
								<th width="100">规格型号</th>
                                <th width="30">单位</th> 
								<th width="10">数量</th>
								<th width="10">预估单价</th>
								<th width="10">金额</th>
								<th width="30">备注</th>
								<th width="50" align="center">操作</th>
							</tr>
							<?php for($j=1;$j<=50;$j++){?>

								<tr id="purchase_table_<?=$j?>" <?php echo $j>3&&$_POST['stockid'.$j]==''?'style="display:none"':''?> class="mouse click">

									<td><input  style="background-color:#D2E9FF;" type="text" name="stockid<?=$j?>" id="text_slect_buliao<?=$j?>" value="<?=$_POST['stockid'.$j]?>" size="22" maxlength="25" onblur="sel_item(<?=$j?>)"/>
										<image class="select_img" src="img/search.png" id="btn_slect_buliao<?=$j?>"/> </td>
									<td ><input readonly="readonly" type="text" name="item_name<?=$j?>" id="text_slect_item_name<?=$j?>" value="<?=$_POST['item_name'.$j]?>" size="15" maxlength="15"/></td> 

									<td ><input  type="text" readonly="readonly"  name="item_desc<?=$j?>" id="text_slect_item_spec<?=$j?>" value="<?=$_POST['item_desc'.$j]?>" size="15" maxlength="15"/></td>

									<td><input readonly="readonly" type="text" name="UOM<?=$j?>" id="text_slect_units<?=$j?>" value="<?=$_POST['UOM'.$j]?>" size="4" maxlength="4"/></td>
									
									<td><input type="text" style="background-color:#D2E9FF;" class="number" id="add_quantity<?=$j?>"   name="quantity<?=$j?>" step="1"  min="0" onkeyup="this.value= this.value.match(/\d+(\.\d{0,2})?/) ? this.value.match(/\d+(\.\d{0,2})?/)[0] : ''"  value="<?=$_POST['quantity'.$j]?>" size="6" maxlength="10" onblur="checkaddall()"  /></td>
									
									<td><input type="text" style="background-color:#D2E9FF;" class="number" id="text_slect_unit_price<?=$j?>" onblur="checkaddall()"  step="1"  min="0" onkeyup="this.value= this.value.match(/\d+(\.\d{0,4})?/) ? this.value.match(/\d+(\.\d{0,4})?/)[0] : ''"   name="unitprice<?=$j?>" value="<?=$_POST['unitprice'.$j]?>" size="6" maxlength="10" /></td>
									<td><input type="text" readonly="readonly" id="lineamount<?=$j?>" class="number"  onkeyup="check(<?=$j?>)" 
									name="lineamount<?=$j?>" value="<?=$_POST['lineamount'.$j]?>" size="10" maxlength="10" /></td>
 


									<td class="list-text"><input type="text" name="remark<?=$j?>" value="<?=$_POST['remark'.$j]?>" size="15" maxlength="45"/></td>
									<td>  <a onclick="delettr($(this));" style="padding:0px 5px;" href="javascript:;">删除</a></td>

									<td><input  type="hidden" name="Subinventory_name<?=$j?>" id="text_slect_locationname<?=$j?>" value="<?=$_POST['Subinventory_name'.$j]?>" size="8" maxlength="25"/>
                                <input  type="hidden" name="last_price<?=$j?>" id="text_slect_last_price<?=$j?>" value="<?=$_POST['last_price'.$j]?>" 
								size="4" maxlength="4"/>
								</td>


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
			width: '800px',
			height: 470,
			content:'url:Searchbuliaoprmodify.php?fwValue=<?=$j?>&cat=buliao',
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
	<?php for($j=1;$j<=50;$j++){?> 
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