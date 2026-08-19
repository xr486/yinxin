<?php
/* $Id: SupplierTransInquiry.php 5785 2012-12-29 04:47:42Z daintree $ */

include('includes/session.inc');
$Title = '客户对账处理';
include('includes/header.inc');
if (isset($_GET['customer_id'])) {
    $customer_id = $_GET['customer_id'];
} else {
    $customer_id = '';
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
        DB_Txn_Begin($db);

        foreach ($_POST as $key => $value) {
            if (mb_substr($key, 0, 6) == 'status') {
                $count = $count + 1;
                $delivery_num_line = mb_substr($key, 6);
                $n = strpos($delivery_num_line, '_');
                if ($n) {
                    $delivery_num = substr($delivery_num_line, 0, $n);
                    $line = substr($delivery_num_line, $n + 1);
             
                    $del_date = strtotime(Date('Y-m-d H:i:s')); 
                   
                    $delivery_amount = $_POST['delivery_amount' . $delivery_num_line];
 
                    $delivery_remark = $_POST['delivery_remark' . $delivery_num_line];
               
                    $check_amount=$_POST['check_amount' . $delivery_num_line];
				
                    if ($delivery_amount < $check_amount) {
                        $checkQty = 1;
                    }
                 
                    if ($delivery_amount != '' && $checkQty != 1) {
 
                        $sql1 = "UPDATE so_delivery_all set 
                           check_amount= " . $delivery_amount . " ,
						       check_date='" . $del_date . "' ,
							   check_flag='Y' ,
							   check_remark='" . $delivery_remark . "' ,
                               last_update_date='" . $del_date . "' ,
							   last_updated_by='" . $_SESSION['UserID'] . "
							   ' where   delivery_num='" . $delivery_num . "'  and  deliveryline 	='" . $line . "'";
                        $result = DB_query($sql1, $db);

 
                        unset($sql1);
                        unset($result);
                        unset($sqlUpdatercvline);
                        unset($result_line);
                        unset($sqltrancsation);
                        unset($result_trancsation);
                        unset($insertDel);
                        unset($result_insertDel);
                        unset($sqlinvtrancsation);
                        unset($result_invtrancsation);
                    } else {
                        $InputError = 1;
                    }
                }
            }
        }

//      $sqlinsertrcv="insert into po_rcv_receipt_header(receipt_num,vendor,create_date,created_by) values('".$OrderNum."','".$vendor."','". $_SESSION['UserID'] ."','".$v_date."')";
//     echo $sqlinsertrcv;
//     $result_header=DB_query($sqlinsertrcv, $db);
//        }
        if ($count != 0) {
            if ($InputError == 1) {
                $msg = '对账金额不可以小于出货金额';
                prnMsg($msg, 'error');
            } else {
                DB_Txn_Commit($db);
                $msg = '对账成功！';
                prnMsg($msg, 'success');
                 echo '<br /><div class="centre"><a href="' . $RootPath . '/AccountManage.php">' . _('继续下一个客户出货对账') . '</a></div>';
// echo '<meta http-equiv="refresh" content="0.3" url=RequestReceive.php"/>';
                unset($sql1);
                unset($sql_num);
                unset($result_num);
                unset($sqlinsertrcv);
                unset($rownum);
                unset($result_header);
            }
        } else {
            $msg = '请选中更改项';
            prnMsg($msg, 'error');
        }
    }
} else {
    session_start() or die("session is not started");
    $_SESSION['num' . $identifier] = 400;
}


echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . '出货单对账' .
 '" alt="" />' . ' ' . $Title . '
	</p>';
 
if (isset($customer_id) and $customer_id != '') {
    
    $sql = "SELECT c.customer_code, c.customer_name, a.delivery_num, tracking_number, a.trackingcompany, a.creation_date, a.narrative, a.created_by, b.so_order_number, b.so_line_no, b.delivery_quantity, b.deliveryline, d.stockid, s.item_desc, d.price,b.line_amount delivery_amount, d.subinventory_code
FROM so_delivery_headers_all a, so_delivery_all b, customers c, so_lines_all d, sf_item_no s
WHERE a.delivery_num = b.delivery_num
AND a.customer_code = b.customer_code
AND d.order_number = b.so_order_number
AND d.line = b.so_line_no
AND c.customer_code = a.customer_code
AND b.stockid = s.item_no
AND check_date IS NULL ";

 
    $sql .= " and c.customer_id= '" . $customer_id . "' ";
 

    $sql .= " ORDER BY a.delivery_num,b.deliveryline";
    $TransResult = DB_query($sql, $db);

    $ErrMsg = _('出货单查询错误，请查看所选客户') . ' - ' . DB_error_msg($db);
    $DbgMsg = _('The SQL that failed was');
    if (DB_num_rows($TransResult) == 0) {
        unset($TransResult);
        prnMsg(_('没有找到需要对账的出货单，请重新输入条件查询！'), 'info');
    } else {
        echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '"><input type="hidden" name = "identifier" value ="' . $identifier . '">';
        echo '<div>';
//echo '<div style="width:1300px;height:400px;overflow-x: hidden; overflow-y: scroll;">';
        echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
        echo '<table class="selection" align="center" >';
        $tableheader = '<tr>
	                              <th width =50>' . '确认' . '</th>
                                          <th width =100>' . '出货单' . '</th>
                                        <th width =60 >' . '行' . '</th>
	                                <th width =100>' . '订单号码' . '</th>
                                        <th width =60>' . '行' . '</th>
                                        <th width =100>' . '料号' . '</th>
                                            <th width =210>' . '料号描述' . '</th>
											 <th width =100 >' . '仓库' . '</th>
                                         <th  width = 80>' . '数量' . '</th> 
                                          <th width =80 >' . '单价' . '</th>
                                         <th width =80 >' . '出货金额' . '</th>
										 <th width =80 >' . '确认金额' . '</th> 
										 <th width =100 >' . '备注' . '</th>   
                                        
                                        
                                       
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
            echo '<td><input type="checkbox" name="status' . $myrow['delivery_num'] . '_' . $myrow['deliveryline'] . '" /></td>';
            echo '<td>' . $myrow['so_order_number'] . '</td>';
            echo '<td>' . $myrow['so_line_no'] . '</td>';
            echo '<td><font color="red">' . $myrow['delivery_num'] . '</font></td>';
            echo '<td>' . $myrow['deliveryline'] . ' </td>';
            echo '<td>' . $myrow['stockid'] . ' </td>';
            echo '<td>' . $myrow['item_desc'] . ' </td>';
			echo '<td>' . $myrow['subinventory_code'] . ' </td>';
            echo '<td>' . $myrow['delivery_quantity'] . ' </td>';
            echo '<td>' . $myrow['price'] . ' </td>';
            echo '<td>' . $myrow['delivery_amount'] . ' </td>'; 
            echo'      
            <td><input type="text" class="number"   name="delivery_amount' . $myrow['delivery_num'] . '_' . $myrow['deliveryline'] . '" size="12" maxlength="25"  value="' . $myrow['delivery_amount'] . '" /></td>';
            
			echo'      
            <td><input type="text"     name="delivery_remark' . $myrow['delivery_num'] . '_' . $myrow['deliveryline'] . '" size="20" maxlength="25"  " /></td>';
 
            echo'<td><input type="hidden"  name="check_amount' . $myrow['delivery_num'] . '_' . $myrow['deliveryline'] . '" value="' . $myrow['delivery_amount'] . '"  /></td>';
            
            echo '</tr>';

            $RowCounter++;
            If ($RowCounter == 500) {
                $RowCounter = 1;
                echo $tableheader;
            }
        }
         echo '<tr><td colspan="11"><p><input type="checkbox" name="selectall" onclick="checkall(this.form);"/>全选/取消全选</p></td></tr>';
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
    header('Location: InPODelivery.php');
}
echo '<script language="javascript" type="text/javascript">';
echo 'function checkall(thisform){for(var i=0;i<thisform.elements.length;i++){if(thisform.elements[i].type=="checkbox"&&thisform.elements[i].checked==false&&thisform.elements[i].name!="selectall"){thisform.elements[i].checked=true;}else if(thisform.elements[i].type=="checkbox"&&thisform.elements[i].checked==true&&thisform.elements[i].name!="selectall"){thisform.elements[i].checked=false;}} }';
echo '</script>'; 
//}
include('includes/footer.inc');
?>