<?php
/* $Id: SupplierTransInquiry.php 5785 2012-12-29 04:47:42Z daintree $ */
   
include('includes/session.inc');
$Title = '采购来料报检单修改';
include('includes/header.inc');
if (isset($_GET['NUM'])) {
    $NUM = $_GET['NUM'];
}else {
    $NUM=$_POST['NUM'];
}
if (isset($_GET['identifier'])) {
    $_POST['identifier'] = $_GET['identifier'];
}
if (!isset($_POST['identifier'])) {
    $identifier = date('U');
} else {
    $identifier = $_POST['identifier'];
}
if (isset($_POST['Submit'])) {
    $_SESSION['num' . $identifier] = 400;
    isset($_SESSION['num' . $identifier]) or die("no session");
    if ($_SESSION['num' . $identifier] == 400) {
        $_SESSION['num' . $identifier] = 500;
        $InputError = 0;
        $checkQty=0;
        $count = 0;
        DB_Txn_Begin($db);
        foreach ($_POST as $key => $value) {
            if (mb_substr($key, 0, 6) == 'status') {
				$count=2;
                $i = mb_substr($key, 6);
             
             
                if (($_POST['accept'.$i]+ $_POST['reject'.$i] > $_POST['wait_qty' . $i]) or ($_POST['accept'.$i]+$_POST['reject'.$i]==0) ) {
                    $InputError=1;
                }
            }
        }

		$time = time();
	$time2=$time - 5;

	if ($_SESSION['lastsearchtime'] > $time2 )  {
	$InputError = 1;
	prnMsg($value.'重复提交！',error);
	}


		if($InputError!=1){
     foreach ($_POST as $key => $value) {
            if (mb_substr($key, 0, 6) == 'status') {  
				 
                $count = $count + 1;
                $i = mb_substr($key, 6);
               
                
             
                $time = time();
                
                // if($_POST['accept'.$i]+$_POST['reject'.$i]>$_POST['wait_qty'.$i]){
                //     $checkQty=1;                    
                // }
			 
                if(($_POST['accept'.$i]!=''||$_POST['reject'.$i]!='') && $checkQty!=1){
//                    echo $receive_qty;


if($_POST['accept'.$i] > $_POST['old_accept'.$i]){

    $temp_accept = $_POST['accept'.$i] - $_POST['old_accept'.$i];

    $sql1="UPDATE po_lines_all set 
                               quantity_accepted=ifnull(quantity_accepted,0)+".$temp_accept." , 
                               last_update_date='".$time."' ,last_updated_by='".$_SESSION['UserID']."' 
							   where   po_num='".$_POST['po_num'.$i]."'  and  line='".$_POST['po_line'.$i]."'";
                    $result = DB_query($sql1, $db);

					$sqlUpdatercvline="update po_rcv_receipt_line set already_inspection_qty=ifnull(already_inspection_qty,0)+".$temp_accept.",
            wait_inspect_quantity=ifnull(wait_inspect_quantity,0)-".$temp_accept.",
			wait_delivery_quantity=ifnull(wait_delivery_quantity,0)+".$temp_accept.",
			last_update_date='".$time."',last_updated_by='".$_SESSION['UserID']."'  
            where receipt_num='".$_POST['receipt_num'.$i]."'   
			and  po_num='".$_POST['po_num'.$i]."'  and  po_line='".$_POST['po_line'.$i]."' 
			and receipt_line='".$_POST['receipt_line'.$i]."' ";
                $result_line = DB_query($sqlUpdatercvline, $db);

                $sqltrancsation="insert into po_rcv_transactions(receipt_num,receipt_line,qc_remark,po_num,po_line,stockid,transaction_type,transaction_date,transaction_quantity,creation_date,created_by,last_update_date,last_updated_by) ";
    $sqltrancsation.="values( '".$_POST['receipt_num'.$i]."','".$_POST['receipt_line'.$i]."','".$_POST['qc_remark'.$i]."','".$_POST['po_num'.$i]."','".$_POST['po_line'.$i]."','".$_POST['stockid'.$i]."','ACCEPT','".$time."','".$temp_accept."','".$time."','".$_SESSION['UserID']."','".$time."','".$_SESSION['UserID']."')";
//    echo $sqltrancsation;
    $result_trancsation = DB_query($sqltrancsation, $db); 
}else{
    $temp_accept = $_POST['old_accept'.$i] - $_POST['accept'.$i];

    $sql1="UPDATE po_lines_all set 
                               quantity_accepted=ifnull(quantity_accepted,0)-".$temp_accept." , 
                               last_update_date='".$time."' ,last_updated_by='".$_SESSION['UserID']."' 
							   where   po_num='".$_POST['po_num'.$i]."'  and  line='".$_POST['po_line'.$i]."'";
                    $result = DB_query($sql1, $db);

					$sqlUpdatercvline="update po_rcv_receipt_line set already_inspection_qty=ifnull(already_inspection_qty,0)-".$temp_accept.",
            wait_inspect_quantity=ifnull(wait_inspect_quantity,0)+".$temp_accept.",
			wait_delivery_quantity=ifnull(wait_delivery_quantity,0)-".$temp_accept.",
			last_update_date='".$time."',last_updated_by='".$_SESSION['UserID']."'  
            where receipt_num='".$_POST['receipt_num'.$i]."'   
			and  po_num='".$_POST['po_num'.$i]."'  and  po_line='".$_POST['po_line'.$i]."' 
			and receipt_line='".$_POST['receipt_line'.$i]."' ";
                $result_line = DB_query($sqlUpdatercvline, $db);

                $temp_accept2 = $temp_accept;
                $sqlsubcode = "select transaction_id,transaction_quantity from po_rcv_transactions where receipt_num='".$_POST['receipt_num'.$i]."'   
                and  po_num='".$_POST['po_num'.$i]."'  and  po_line='".$_POST['po_line'.$i]."' 
                and receipt_line='".$_POST['receipt_line'.$i]."' and transaction_type = 'ACCEPT'  order by transaction_id desc";
            
                $result_subcode = DB_query($sqlsubcode, $db);
                while ($v = DB_fetch_array($result_subcode)) {
                    if ($temp_accept2 > 0) {
                        if ($v['transaction_quantity'] <= $temp_accept2) {
                            $UpdateSubCode = "delete from  po_rcv_transactions where transaction_id=" . $v['transaction_id'] . "";
    //                echo $UpdateSubCode;
                            $result_updatesubcode = DB_query($UpdateSubCode, $db);
                            unset($UpdateSubCode);
                            $temp_accept2 = $temp_accept2 - $v['transaction_quantity'];
                        } else {  
                            $UpdateSubCode1 = "Update po_rcv_transactions set transaction_quantity=transaction_quantity-" . $temp_accept2 . " where transaction_id=" . $v['transaction_id'] . "";
                                // echo $UpdateSubCode1;
                            $result_updatesubcode1 = DB_query($UpdateSubCode1, $db);
                            $temp_accept2 = 0;
                        }
                    }
                }



}


                    
					if ($_POST['reject'.$i] > $_POST['old_reject'.$i])  {
    $temp_reject = $_POST['reject'.$i] - $_POST['old_reject'.$i];

                    $sql1="UPDATE po_lines_all set  
                               quantity_rejected=ifnull(quantity_rejected,0)+".$temp_reject." ,
                               last_update_date='".$time."' ,
							   last_updated_by='".$_SESSION['UserID']."' 
							   where  po_num='".$_POST['po_num'.$i]."'  
							and  line='".$_POST['po_line'.$i]."'";
                    $result = DB_query($sql1, $db);

					 $sqlUpdatercvline="update po_rcv_receipt_line set already_inspection_qty=ifnull(already_inspection_qty,0)+".$temp_reject.",
            wait_inspect_quantity=ifnull(wait_inspect_quantity,0)-".$temp_reject.",
            reject_area_quantity=ifnull(reject_area_quantity,0)+".$temp_reject.", last_update_date='".$time."',last_updated_by='".$_SESSION['UserID']."'  
            where receipt_num='".$_POST['receipt_num'.$i]."'   and  po_num='".$_POST['po_num'.$i]."'  and  po_line='".$_POST['po_line'.$i]."' and receipt_line='".$_POST['receipt_line'.$i]."' ";
               $result_line = DB_query($sqlUpdatercvline, $db);

               $sqltrancsation="insert into po_rcv_transactions(receipt_num,receipt_line,qc_remark,po_num,po_line,stockid,transaction_type,transaction_date,transaction_quantity,creation_date,created_by,last_update_date,last_updated_by) ";
    $sqltrancsation.="values( '".$_POST['receipt_num'.$i]."','".$_POST['receipt_line'.$i]."','".$_POST['qc_remark'.$i]."','".$_POST['po_num'.$i]."','".$_POST['po_line'.$i]."','".$_POST['stockid'.$i]."','REJECT','".$time."','".$temp_reject."','".$time."','".$_SESSION['UserID']."','".$time."','".$_SESSION['UserID']."')";
//    echo $sqltrancsation;
    $result_trancsation = DB_query($sqltrancsation, $db);
					}else{
                        $temp_reject = $_POST['old_reject'.$i] - $_POST['reject'.$i];

                    $sql1="UPDATE po_lines_all set  
                               quantity_rejected=ifnull(quantity_rejected,0)-".$temp_reject." ,
                               last_update_date='".$time."' ,
							   last_updated_by='".$_SESSION['UserID']."' 
							   where  po_num='".$_POST['po_num'.$i]."'  
							and  line='".$_POST['po_line'.$i]."'";
                    $result = DB_query($sql1, $db);

					 $sqlUpdatercvline="update po_rcv_receipt_line set already_inspection_qty=ifnull(already_inspection_qty,0)-".$temp_reject.",
            wait_inspect_quantity=ifnull(wait_inspect_quantity,0)+".$temp_reject.",
            reject_area_quantity=ifnull(reject_area_quantity,0)-".$temp_reject.", last_update_date='".$time."',last_updated_by='".$_SESSION['UserID']."'  
            where receipt_num='".$_POST['receipt_num'.$i]."'   and  po_num='".$_POST['po_num'.$i]."'  and  po_line='".$_POST['po_line'.$i]."' and receipt_line='".$_POST['receipt_line'.$i]."' ";
               $result_line = DB_query($sqlUpdatercvline, $db);

               $temp_reject2 = $temp_reject;
                $sqlsubcode = "select transaction_id,transaction_quantity from po_rcv_transactions where receipt_num='".$_POST['receipt_num'.$i]."'   
                and  po_num='".$_POST['po_num'.$i]."'  and  po_line='".$_POST['po_line'.$i]."' 
                and receipt_line='".$_POST['receipt_line'.$i]."' and transaction_type = 'REJECT'  order by transaction_id desc";
            
                $result_subcode = DB_query($sqlsubcode, $db);
                while ($v = DB_fetch_array($result_subcode)) {
                    if ($temp_reject2 > 0) {
                        if ($v['transaction_quantity'] <= $temp_reject2) {
                            $UpdateSubCode = "delete from  po_rcv_transactions where transaction_id=" . $v['transaction_id'] . "";
    //                echo $UpdateSubCode;
                            $result_updatesubcode = DB_query($UpdateSubCode, $db);
                            unset($UpdateSubCode);
                            $temp_reject2 = $temp_reject2 - $v['transaction_quantity'];
                        } else {  
                            $UpdateSubCode1 = "Update po_rcv_transactions set transaction_quantity=transaction_quantity-" . $temp_reject2 . " where transaction_id=" . $v['transaction_id'] . "";
                                // echo $UpdateSubCode1;
                            $result_updatesubcode1 = DB_query($UpdateSubCode1, $db);
                            $temp_reject2 = 0;
                        }
                    }
                }
                    }
                    
   
       unset($sql1);
       unset($result);
       unset($sqlUpdatercvline);
       unset($result_line);
       unset($sqltrancsation);
       unset($result_trancsation);
                     } else {
                    $InputError = 1;
                }
                }
                
            }
		}
 
            if($count!=0){
        if ($InputError == 1) {
            $msg = '存在数据没有输入数量或检测量超过范围,或者数量未输入！';
            $NUM=$_POST['NUM'];
            prnMsg($msg, 'error');
        } else {
			$_SESSION['lastsearchtime']=$time;
            DB_Txn_Commit($db);
            $msg = '检测成功！';
            prnMsg($msg, 'success');
             echo '<br /><div class="centre"><a href="' . $RootPath . '/InPOCheckModify.php">' . _('采购单进货检测') . '</a></div>';
 
            unset($sql1);
            unset($sql_num);
            unset($result_num);
            unset($sqlinsertrcv);
            unset($rownum);
            unset($result_header);
			unset($NUM);
            
        }
            }else {
                $msg = '请选中更改项';
                $NUM=$_POST['NUM'];
            prnMsg($msg, 'error');
        }
    }
} else {
    session_start() or die("session is not started");
    $_SESSION['num' . $identifier] = 400;
}


echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . '采购单进货检测' .
 '" alt="" />' . ' ' . $Title . '
	</p>';
if (isset($NUM) and $NUM != '') {
    // $SQL_FromDate = FormatDateForSQL($_POST['FromDate']);
    // $SQL_ToDate = FormatDateForSQL($_POST['ToDate']);
    $sql = "select 
rh.receipt_num,rl.receipt_line,rl.receipt_line_id,rl.lot_num,
a.po_num,
b.line,
b.stockid,
c.item_desc,
	c.item_name,
a.vendor_code,
d.vendor_name,
ifnull(b.quantity,0) quantity,
ifnull(rl.quantity_received,0) this_received,
ifnull(rl.wait_inspect_quantity,0) this_accept,
rl.subinventory_code,
b.need_date,ifnull(rl.reject_area_quantity,0) reject_area_quantity,ifnull(rl.wait_delivery_quantity,0) wait_delivery_quantity
FROM  po_headers_all a,
      po_lines_all b,
			po_rcv_receipt_header rh,
			po_rcv_receipt_line  rl,
                        sf_item_no c,vendors d
WHERE  a.po_num=b.po_num
and a.vendor_code=d.vendor_code
and b.po_num=rl.po_num
and b.line=rl.po_line
and rl.receipt_num=rh.receipt_num
and b.stockid=c.item_no
and ifnull(rl.delivery_quantity,0)=0
      ";


    
        $sql .= " and rh.receipt_num= '" . $NUM . "' ";

    $sql .= " ORDER BY rl.receipt_line_id";
	//echo $sql;
    $TransResult = DB_query($sql, $db);
 
    $ErrMsg = _('来料报检单查询错误，请查看所选采购单') . ' - ' . DB_error_msg($db);
    $DbgMsg = _('The SQL that failed was');
    if (DB_num_rows($TransResult) == 0 ) {
        unset($TransResult);
        prnMsg(_('没有找到需要检测的来料报检单，请重新输入条件查询！'), 'info');
    } else{
        echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '"><input type="hidden" name = "identifier" value ="' . $identifier . '">';
        echo '<div>';
//echo '<div style="width:1300px;height:400px;overflow-x: hidden; overflow-y: scroll;">';
        echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
        echo ' <div class="centre">
                                <p id="Prompt" style="color: red;font-size: 20px"></p>
                            </div>
        <div style="overflow:scroll;">
        <table class="selection" align="center" >';
        $tableheader = '<tr>
		<th  width =20> 选择 </th>
		<th  width =80> 来料报检单号 </th>
		<th  width =10> 行 </th>
		<th    > 供应商编码 </th>
		<th  width =100> 采购单号 </th>
		<th  width =10> 行 </th>
		<th  width =80> 料号 </th>
		<th  width = 50> 料号名称 </th>
		<th  width =50> 规格型号 </th>
		<th  width = 90> 来料报检量 </th>
		<th  width = 90> 待检测量 </th>
		<th  width =90   >  合格量<span style="color:red">*</span>' . ' </th>
		<th  width =90   > 不合格量<span style="color:red">*</span>' . '</font></th>
        <th  width = 90> 批号 </th>
		<th  width = 90> 检验备注 </th>
	 
		</tr>';
        echo $tableheader;

        $RowCounter = 1;
        $k = 0; //row colour counter

        while ($myrow = DB_fetch_array($TransResult)) {

            if ($k == 1) {
                echo '<tr class="EvenTableRows">';
                $k = 0;
            } else {
                echo '<tr class="EvenTableRows">';
                ;
                $k++;
            }
            echo '<td><input type="checkbox" name="status' . $myrow['receipt_line_id']. '" /></td>';
            echo '<td>' . $myrow['receipt_num'] . '</td>';
			echo '<td>' . $myrow['receipt_line'] . '</td>';
            echo '<td>' . $myrow['vendor_code'] . '</td>';
            echo '<td><font color="red">' . $myrow['po_num'] . '</font></td>';
            echo '<td><font color="red">' . $myrow['line'] . ' </td>';
            echo '<td>' . $myrow['stockid'] . ' </td>';
			echo '<td>' . $myrow['item_name'] . ' </td>';
            echo '<td>' . $myrow['item_desc'] . ' </td>';
            echo '<td>' . $myrow['this_received'] . ' </td>';
            echo '<td>' . $myrow['this_accept'] . ' </td>';
            echo'      
            <td><input type="text" onblur="check55(' . $myrow['receipt_line_id'] . ')"  id="accept'.$myrow['receipt_line_id'].'"  name="accept'.$myrow['receipt_line_id'].'" size="4" maxlength="25"  value="' . $myrow['wait_delivery_quantity'] . '" /></td>';
           echo'      
            <td><input type="text" onblur="check55(' . $myrow['receipt_line_id'] . ')" id="reject' . $myrow['receipt_line_id'].  '"  name="reject' . $myrow['receipt_line_id'].  '" size="4" maxlength="25"  value="' . $myrow['reject_area_quantity'] . '" /></td>';
            echo '<td>' . $myrow['lot_num'] . ' </td>'; 
			 echo'      
            <td><input type="text"  name="qc_remark' . $myrow['receipt_line_id'].  '" size="4" maxlength="105"  value="' . $_POST['qc_remark'] . '" /></td>';
            
            echo'<input type="hidden"  id="wait_qty'. $myrow['receipt_line_id']. '" name="wait_qty'. $myrow['receipt_line_id']. '" value="' . $myrow['this_accept'] . '"  />';
            echo'<input type="hidden"  id="this_received'. $myrow['receipt_line_id']. '" name="this_received'. $myrow['receipt_line_id']. '" value="' . $myrow['this_received'] . '"  />';
            echo'<input type="hidden"  name="receipt_num'. $myrow['receipt_line_id']. '" value="' . $myrow['receipt_num'] . '"  />';
            echo'<input type="hidden"  name="stockid'. $myrow['receipt_line_id']. '" value="' . $myrow['stockid'] . '"  />';  
			 echo'<input type="hidden"  name="receipt_line'. $myrow['receipt_line_id']. '" value="' . $myrow['receipt_line'] . '"  />'; 
			 echo'<input type="hidden"  name="po_num'. $myrow['receipt_line_id']. '" value="' . $myrow['po_num'] . '"  />'; 
			 echo'<input type="hidden"  name="po_line'. $myrow['receipt_line_id']. '" value="' . $myrow['line'] . '"  />'; 
             echo'      
             <td><input type="text"   id="old_accept'.$myrow['receipt_line_id'].'"  name="old_accept'.$myrow['receipt_line_id'].'" size="4" maxlength="25"  value="' . $myrow['wait_delivery_quantity'] . '" /></td>';
            echo'      
             <td><input type="text"  id="old_reject' . $myrow['receipt_line_id'].  '"  name="old_reject' . $myrow['receipt_line_id'].  '" size="4" maxlength="25"  value="' . $myrow['reject_area_quantity'] . '" /></td>';
echo '<input type="hidden" name="NUM" value="' . $myrow['receipt_num'] . '"/></tr>';
            
            $RowCounter++;
            If ($RowCounter == 500) {
                $RowCounter = 1;
                echo $tableheader;
            }
        }
         echo '<tr><td colspan="15"><p><input type="checkbox" name="selectall" onclick="checkall(this.form);"/>全选/取消全选</p></td></tr>';
        echo '</table> 
        </div>';


        echo '</div>';
        echo '<div class="centre">
			<input type="submit" name="Submit" value="' . _('Enter Information') . '" />
                            <input type="submit" name="return" value="' . "返回上一层" . '" />
		</div>
          </form>';
    }
}

if (isset($_POST['return'])) {
 
    header('Location: InPOCheck.php');
}
echo '<script language="javascript" type="text/javascript">';
echo 'function checkall(thisform){for(var i=0;i<thisform.elements.length;i++){if(thisform.elements[i].type=="checkbox"&&thisform.elements[i].checked==false&&thisform.elements[i].name!="selectall"){thisform.elements[i].checked=true;}else if(thisform.elements[i].type=="checkbox"&&thisform.elements[i].checked==true&&thisform.elements[i].name!="selectall"){thisform.elements[i].checked=false;}} }';
echo '</script>';
//}
include('includes/footer.inc');
?>
<script language="javascript" type="text/javascript">
  function check55(s1) { 
            var a = document.getElementById("this_received" + s1).value;
            var b = document.getElementById("accept" + s1).value;
            var c = document.getElementById("reject" + s1).value;
            var d =  Number(b) + Number(c);
            if (parseFloat(d) > parseFloat(a)) {
                document.getElementById("Prompt").innerHTML = "良品+不良品数量" + b + "不可以大于报检量！" + a;
                document.getElementById("accept" + s1).value = "";
                document.getElementById("accept" + s1).focus();
            } else if (parseInt(b) < 0) {
                document.getElementById("Prompt").innerHTML = "合格数量不可以小于0！" + a;
                document.getElementById("accept" + s1).value = "";
                document.getElementById("accept" + s1).focus();
            } else if (parseInt(c) < 0) {
                document.getElementById("Prompt").innerHTML = "不合格数量不可以小于0！" + a;
                document.getElementById("reject" + s1).value = "";
                document.getElementById("reject" + s1).focus();
            } else {
                document.getElementById("Prompt").innerHTML = "";
            }
        }
		</script> 