<?php
/* $Id: SupplierTransInquiry.php 5785 2012-12-29 04:47:42Z daintree $ */

include('includes/session.inc');
$Title = '订单出货查询';
include('includes/header.inc');
if (isset($_GET['NUM'])) {
    $NUM = $_GET['NUM'];
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
    isset($_SESSION['num' . $identifier]) or die("no session");
    if ($_SESSION['num' . $identifier] == 400) {
        $_SESSION['num' . $identifier] = 500;
        $InputError = 0;
        $checkQty=0;
        $count = 0;
        DB_Txn_Begin($db);
        
        $updatedate = strtotime(Date('Y-m-d H:i:s'));
        $sql1="UPDATE sf_orders_all set 
                           status='APPROVED' ,
                           last_update_date='".$updatedate."' ,last_updated_by='".$_SESSION['UserID']."' where 
                            order_number='".$_POST['NUM']."'";
        $result = DB_query($sql1, $db);
//     foreach ($_POST as $key => $value) {
//            if (mb_substr($key, 0, 6) == 'status') {     
//                $count = $count + 1;
//                $po_num_line = mb_substr($key, 6);
//                $n=strpos($po_num_line,'_');
//                if($n){
//                $po_num= substr($po_num_line,0,$n);
//                $line=substr($po_num_line,$n+1);
////                echo $po_num;
////                echo $line;
//                $accept_date = strtotime(Date('Y-m-d H:i:s'));
//                $accept_qty = $_POST['accept' . $po_num_line];
//                $reject_qty = $_POST['reject' . $po_num_line];
//                $Qty=$_POST['Qty' . $po_num_line];
////                echo $receive_qty;
//                $stockid=$_POST['stockid'.$po_num_line];
////                echo $stockid;
//                $rec_num=$_POST['receipt_num'.$po_num_line];
////                echo $vendor;
//                if($accept_qty+$reject_qty>$Qty){
//                    $checkQty=1;                    
//                }
//                if(($accept_qty!=''||$reject_qty!='')&&$checkQty!=1){
////                    echo $receive_qty;
//                    $sql1="UPDATE po_lines_all set 
//                           quantity_accepted=ifnull(quantity_accepted,0)+".$accept_qty." ,
//                               quantity_rejected=ifnull(quantity_rejected,0)+".$reject_qty." ,
//                               last_update_date='".$accept_date."' ,last_updated_by='".$_SESSION['UserID']."' where 
//                            po_num='".$po_num."'  and  line='".$line."'";
//                    $result = DB_query($sql1, $db);
//                    
//                    
//    $sqlUpdatercvline="update po_rcv_receipt_line set already_inspection_qty=ifnull(already_inspection_qty,0)+".$accept_qty.",
//            wait_inspect_quantity=ifnull(wait_inspect_quantity,0)-".$accept_qty."-".$reject_qty.",wait_delivery_quantity=ifnull(wait_delivery_quantity,0)+".$accept_qty.",
//            reject_area_quantity=ifnull(reject_area_quantity,0)+".$reject_qty.", last_update_date='".$accept_date."',last_update_by='".$_SESSION['UserID']."'  
//            where receipt_num='".$rec_num."'   and  po_num='".$po_num."'  and  po_line='".$line."'";
//    
////    echo $sqlinsertrcvline;
//    $result_line = DB_query($sqlUpdatercvline, $db);
//    if($accept_qty!="0"){
//    $sqltrancsation="insert into po_rcv_transactions(receipt_num,po_num,po_line,stockid,transaction_type,transaction_date,transaction_quantity,create_date,created_by) ";
//    $sqltrancsation.="values( '".$rec_num."','".$po_num."','".$line."','".$stockid."','ACCEPT','".$accept_date."','".$accept_qty."','".$accept_date."','".$_SESSION['UserID']."')";
////    echo $sqltrancsation;
//    $result_trancsation = DB_query($sqltrancsation, $db); 
//    }
//    if($reject_qty!="0"){
//        $sqltrancsation="insert into po_rcv_transactions(receipt_num,po_num,po_line,stockid,transaction_type,transaction_date,transaction_quantity,create_date,created_by) ";
//    $sqltrancsation.="values( '".$rec_num."','".$po_num."','".$line."','".$stockid."','REJECT','".$accept_date."','".$reject_qty."','".$accept_date."','".$_SESSION['UserID']."')";
////    echo $sqltrancsation;
//    $result_trancsation = DB_query($sqltrancsation, $db);
//        
//    }
//       unset($sql1);
//       unset($result);
//       unset($sqlUpdatercvline);
//       unset($result_line);
//       unset($sqltrancsation);
//       unset($result_trancsation);
//                     } else {
//                    $InputError = 1;
//                }
//                }
//                }
//            }
            
//      $sqlinsertrcv="insert into po_rcv_receipt_header(receipt_num,vendor,create_date,created_by) values('".$OrderNum."','".$vendor."','". $_SESSION['UserID'] ."','".$v_date."')";
//     echo $sqlinsertrcv;
//     $result_header=DB_query($sqlinsertrcv, $db);
//        }
            DB_Txn_Commit($db);
            $msg = '检测成功！';
            prnMsg($msg, 'success');
// echo '<meta http-equiv="refresh" content="0.3" url=RequestReceive.php"/>';

    }
} else {
    session_start() or die("session is not started");
    $_SESSION['num' . $identifier] = 400;
}


echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . '订单出货查询' .
 '" alt="" />' . ' ' . $Title . '
	</p>';
//
//echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post"><input type="hidden" name = "identifier" value ="' . $identifier . '">';
//echo '<div>';
//echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
//// <td>' . _('Type') . ':</td> 20
//echo '<table class="selection">
//		<tr>	
//                         <td>' . '来料报检单号' . ':</td>
//			<td><input   type="text" name="rec_num" value="' . $_POST['rec_num'] . '" /></td>'
// . '</tr><tr>';



/* if (!isset($_POST['FromDate'])) {
  $_POST['FromDate'] = Date($_SESSION['DefaultDateFormat'], mktime(0, 0, 0, Date('m'), -30, Date('Y')));
  }
  if (!isset($_POST['ToDate'])) {
  $_POST['ToDate'] = Date($_SESSION['DefaultDateFormat']);
  }
  echo '<td>' . _('From') . ':</td>
  <td><input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="FromDate" maxlength="10" size="11" value="' . $_POST['FromDate'] . '" /></td>
  <td>' . _('To') . ':</td>
  <td><input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="ToDate" maxlength="10" size="11" value="' . $_POST['ToDate'] . '" /></td>';
 */
//echo '	</tr>
//	</table>
//	<br />
//	<div class="centre">
//		<input type="submit" name="ShowResults" value="' . '查询' . '" />
//	</div>
//	<br />
//    </div>
//	</form>';
if (isset($NUM) and $NUM != '') {
    // $SQL_FromDate = FormatDateForSQL($_POST['FromDate']);
    // $SQL_ToDate = FormatDateForSQL($_POST['ToDate']);
    $sql = "SELECT b.order_number,
b.line_no,
b.chuanghu_name,
b.chuanghu_width,
b.chuanghu_height,
b.line_amount,
b.chuanghu_quantity,
b.chuanghu_bu_item,
b.chuanghu_bu_quantity,
b.chuanghu_sha_item,
b.chuanghu_sha_quantity,
b.chuanghu_fucai_item,
b.chuanghu_fucai_quantity
FROM sf_orders_all a, sf_order_lines_all b
WHERE  a.order_number=b.order_number
and a.out_flag='1'
      ";

//    if (empty($_POST['po_num_search']) == 0) {
//        $sql .= " AND a.po_num= '" . $_POST['po_num_search'] . "'";
//    }
    
        $sql .= " and a.order_number= '" . $NUM . "' ";
//                . "or a.vendor_name like %'".$_POST['vendor'] ."'%";
//    }
//    if (Is_Date($_POST['FromDate'])) {
//        $SQL_FromDate = strtotime($_POST['FromDate']);
//        $sql .= " and a.purchase_date >= '" . $SQL_FromDate . "' ";
//    }
//    if (Is_Date($_POST['ToDate'])) {
//        $SQL_ToDate = strtotime($_POST['ToDate']) + 86400;
//        $sql .= " and a.purchase_date <='" . $SQL_ToDate . "' ";
//    }

    $sql .= " ORDER BY b.order_number,b.line_no";
    $TransResult = DB_query($sql, $db);
    
    $ErrMsg = _('订单查询错误，请查看所选订单') . ' - ' . DB_error_msg($db);
    $DbgMsg = _('The SQL that failed was');
    if (DB_num_rows($TransResult) == 0) {
        unset($TransResult);
        prnMsg(_('没有找到该订单，请重新输入条件查询！'), 'info');
    } else {
        echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '"><input type="hidden" name = "identifier" value ="' . $identifier . '">';
        echo '<div>';
//echo '<div style="width:1300px;height:400px;overflow-x: hidden; overflow-y: scroll;">';
        echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
        echo '<input type="hidden" name="NUM" value="' . $NUM . '" />';
        echo '<table class="selection" align="center" >';
        $tableheader = '<tr>
                                          <th width =100>' . '订单号' . '</th>
                                        <th width =100 >' . '行' . '</th>
	                                <th width =100>' . '窗户' . '</th>
                                        <th width =100>' . '窗户宽度' . '</th>
                                        <th width =100>' . '窗户高度' . '</th>
                                            <th width =100>' . '窗户数量' . '</th>
                                         <th  width = 100>' . '金额' . '</th> 
                                             <th  width = 100>' . '布' . '</th> 
                                         <th width =100 >' . '布数量' . '</th>
                                             <th width =100 >' . '纱' . '</th>
                                         <th width =100 >' . '纱数量' . '</th>
                                         <th  width =100>' . '辅材' . '</th>
                                             <th  width =100>' . '辅材数量' . '</th>
                                       
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
            
            echo '<td>' . $myrow['order_number'] . '</td>';
                echo '<td>' . $myrow['line_no'] . '</td>';
                echo '<td>' . $myrow['chuanghu_name'] . '</td>';
            echo '<td>' . $myrow['chaunghu_width'] . ' </td>';
            echo '<td>' . $myrow['chaunghu_height'] . ' </td>';
            echo '<td>' . $myrow['line_amount'] . ' </td>';
            echo '<td>' . $myrow['chuanghu_bu_item'] . ' </td>';
            echo '<td>' . $myrow['chuanghu_bu_quantity'] . ' </td>';
            echo '<td>' . $myrow['chuanghu_sha_item'] . ' </td>';
            echo '<td>' . $myrow['chuanghu_sha_quantity'] . ' </td>';
            echo '<td>' . $myrow['chuanghu_fucai_item'] . ' </td>';
            echo '<td>' . $myrow['chunaghu_fucai_quantity'] . ' </td>';
                 
echo '</tr>';
            
            $RowCounter++;
            If ($RowCounter == 500) {
                $RowCounter = 1;
                echo $tableheader;
            }
        }
        echo '</table> ';


        echo '</div>';
        echo '<div class="centre">
                            <input type="submit" name="return" value="' . "返回上一层" . '" />
		</div>
          </form>';
    }
}

if (isset($_POST['return'])) {
//    echo 'AAAAAAAAAA';
    header('Location: InSOOut.php');
}
//}
include('includes/footer.inc');
?>