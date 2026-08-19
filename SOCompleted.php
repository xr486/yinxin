<?php
/* $Id: SupplierTransInquiry.php 5785 2012-12-29 04:47:42Z daintree $ */

include('includes/session.inc');
$Title = '生产订单结案处理入库（生管）';
include('includes/header.inc');
if (isset($_GET['NUM'])) {
    $NUM = $_GET['NUM'];
} else {
    $NUM = '';
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
        $checkQty = 0;
        $count = 0;
        $tempcheck = 0;
        DB_Txn_Begin($db);

        foreach ($_POST as $key => $value) {
            if (mb_substr($key, 0, 6) == 'status') {
                $count = $count + 1;
                $updatedate = strtotime(Date('Y-m-d H:i:s'));
                $so_num_line = mb_substr($key, 6);
                $n = strpos($so_num_line, '_');
                if ($n) {
                    $so_num = substr($so_num_line, 0, $n);
                    $line = substr($so_num_line, $n + 1);
                    $sql1 = "UPDATE sf_order_lines_all set 
                           status='COMPLETED' ,
                               last_update_date='" . $updatedate . "' ,last_updated_by='" . $_SESSION['UserID'] . "' where 
                            order_number='" . $so_num . "'  and  line_no='" . $line . "'";
                    $result = DB_query($sql1, $db);
            } else {
                $InputError = 1;
            }
        }
     }


//      $sqlinsertrcv="insert into po_rcv_receipt_header(receipt_num,vendor,create_date,created_by) values('".$OrderNum."','".$vendor."','". $_SESSION['UserID'] ."','".$v_date."')";
//     echo $sqlinsertrcv;
//     $result_header=DB_query($sqlinsertrcv, $db);
//        }
        if ($count != 0) {
                if ($InputError == 1) {
                    unset($InputError);
                    $msg = '保存失败！';
                    prnMsg($msg, 'error');
                } else {
                    DB_Txn_Commit($db);
                     echo '<br /><div class="centre"><a href="' . $RootPath . '/InSOCompleted.php">' . _('生产订单结案处理入库（生管）') . '</a></div>';
// echo '<meta http-equiv="refresh" content="0.3" url=RequestReceive.php"/>';
                    unset($sql1);
                    unset($sql_num);
                    unset($result);
                    unset($count);
                     unset($InputError);
                }
        } else {
            echo '<br /><div class="centre"><a href="' . $RootPath . '/InSOCompleted.php"><font color="red">' . _('请选中更改项') . '</font></a></div>';
        }
    }
} else {
    session_start() or die("session is not started");
    $_SESSION['num' . $identifier] = 400;
}


echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . '生产订单结案处理入库（生管）' .
 '" alt="" />' . ' ' . $Title . '
	</p>';

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
and b.status='OK'
      ";

//    if (empty($_POST['po_num_search']) == 0) {
//        $sql .= " AND a.po_num= '" . $_POST['po_num_search'] . "'";
//    }

    $sql .= " and a.order_number='" . $NUM . "' ";
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

    $ErrMsg = _('来料报检单查询错误，请查看所选采购单') . ' - ' . DB_error_msg($db);
    $DbgMsg = _('The SQL that failed was');
    if (DB_num_rows($TransResult) == 0) {
        unset($TransResult);
        prnMsg(_('没有找到需要不良退货的来料报检单，请重新输入条件查询！'), 'info');
    } else {
        echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '"><input type="hidden" name = "identifier" value ="' . $identifier . '">';
        echo '<div>';
//echo '<div style="width:1300px;height:400px;overflow-x: hidden; overflow-y: scroll;">';
        echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
        echo '<table class="selection" align="center" >';
        $tableheader = '<tr>
	                              <th width =50>' . '确认' . '</th>
                                           <th width =100>' . '订单号' . '</th>
                                        <th width =30>' . '行' . '</th>
	                                <th width =100>' . '窗户' . '</th>
                                        <th width =80>' . '窗户宽' . '</th>
                                        <th width =80>' . '窗户高' . '</th>
                                            <th width =80>' . '数量' . '</th>
                                        
                                             <th  width = 100>' . '布' . '</th> 
                                         <th width =80 >' . '布数量' . '</th>
                                             <th width =100 >' . '纱' . '</th>
                                         <th width =80 >' . '纱数量' . '</th>
                                         <th  width =100>' . '辅材' . '</th>
                                             <th  width =80>' . '辅材数量' . '</th>
                                       
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

			if  ($myrow['chuanghu_bu_item']=='' or $myrow['chuanghu_bu_item']=='NULL') {
			$chuanghu_bu_item='';
			} else {
				$chuanghu_bu_item=$myrow['chuanghu_bu_item'];
			}

			if  ($myrow['chuanghu_sha_item']=='' or $myrow['chuanghu_sha_item']=='NULL') {
			$chuanghu_sha_item='';
			} else {
				$chuanghu_sha_item=$myrow['chuanghu_sha_item'];
			}

			if  ($myrow['chuanghu_fucai_item']=='' or $myrow['chuanghu_fucai_item']=='NULL') {
			$chuanghu_fucai_item='';			
			} else {
				$chuanghu_fucai_item=$myrow['chuanghu_fucai_item'];
			}

            echo '<td><input type="checkbox" name="status' . $myrow['order_number'] . '_' . $myrow['line_no'] . '" /></td>';
            echo '<td>' . $myrow['order_number'] . '</td>';
            echo '<td>' . $myrow['line_no'] . '</td>';
            echo '<td>' . $myrow['chuanghu_name'] . '</td>';
            echo '<td>' . $myrow['chuanghu_width'] . ' </td>';
            echo '<td>' . $myrow['chuanghu_height'] . ' </td>';
            echo '<td>' . $myrow['chuanghu_quantity'] . ' </td>';
//            echo '<td>' . $myrow['line_amount'] . ' </td>';
            echo '<td>' . $chuanghu_bu_item . ' </td>';
            echo '<td>' . $myrow['chuanghu_bu_quantity'] . ' </td>';
            echo '<td>' . $chuanghu_sha_item . ' </td>';
            echo '<td>' . $myrow['chuanghu_sha_quantity'] . ' </td>';
            echo '<td>' . $chuanghu_fucai_item . ' </td>';
            echo '<td>' . $myrow['chuanghu_fucai_quantity'] . ' </td>';

//            echo'<td><input type="hidden"  name="Qty'. $myrow['po_num'] .'_'.$myrow['line']. '" value="' . $myrow['this_return'] . '"  /></td>';
//            echo'<td><input type="hidden"  name="receipt_num'. $myrow['po_num'] .'_'.$myrow['line']. '" value="' . $myrow['receipt_num'] . '"  /></td>';
//            echo'<td><input type="hidden"  name="stockid'. $myrow['po_num'] .'_'.$myrow['line']. '" value="' . $myrow['stockid'] . '"  /></td>';       
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
			<input type="submit" name="Submit" value="' . _('Enter Information') . '" />
                            <input type="submit" name="return" value="' . "返回上一层" . '" />
		</div>
          </form>';
    }
}
if (isset($_POST['return'])) {
//    echo 'AAAAAAAAAA';
    header('Location: InSOCompleted.php');
}
//}
include('includes/footer.inc');
?>