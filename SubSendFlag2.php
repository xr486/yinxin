<?php

/* $Id: vendors.php 6338 2013-09-28 05:10:46Z daintree $ */
ob_start();
include('includes/session.inc');
if (isset($_GET['identifier'])) {
    $_POST['identifier'] = $_GET['identifier'];
}

if (!isset($_POST['identifier'])) {
    $identifier = date('U');
} else {
    $identifier = $_POST['identifier'];
}
if (isset($_GET['Updateso_num'])) {
    $v_type = substr($_GET['Updateso_num'], 0, 1);

    $Updateso_num = substr($_GET['Updateso_num'], 1);
//    echo $Updateso_num;
//    echo $v_type;
} else {
    $Updateso_num = '';
}
$Title = _('订单信息');
$ViewTopic = '订单信息';
$BookMark = '订单信息';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/CountriesArray.php');

echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/customer.png" title="' . _('订单') .
 '" alt="" />' . ' ' . _('订单信息') . '
	</p>';
if (isset($Updateso_num) and $Updateso_num != '') {
    //CreditLimit,
    $sql2 = "SELECT
	a.order_number,
	a.status,
	b.realname,
	a.order_amount,
	a.description note,
	a.schedule_ship_date,
	a.creation_date
FROM
	sf_orders_all a,
	www_users b
WHERE
	1 = 1
AND a.created_by = b.userid
and EXISTS(select 1 from sf_order_lines_all c where  a.order_number = c.order_number
             and order_line_id  = '" . $Updateso_num . "')";
    $result2 = DB_query($sql2, $db);
    $myrow = DB_fetch_array($result2);
    $_POST['order_number'] = $myrow['order_number'];
    $_POST['status'] = $myrow['status'];
    $_POST['note'] = $myrow['note'];
    $_POST['realname'] = $myrow['realname'];
    $_POST['schedule_ship_date'] = $myrow['schedule_ship_date'];
    $_POST['create_date'] = $myrow['create_date'];
    if (!isset($_GET['delete'])) {
        echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">'
        . '<input type="hidden" name = "identifier" value ="' . $identifier . '">';
        echo '<div>';
        echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
        if ($_POST['status'] == 'INPROCESS') {
            $v_status = '待签核';
        } elseif ($_POST['status'] == 'APPROVED') {
            $v_status = '已签核';
        } elseif ($_POST['status'] == 'REJECTED') {
            $v_status = '已拒签';
        } else {
            $v_status = '已取消';
        }
        echo '<table class="selection" id="SignFrame">
                       <tr class="EvenTableRows">
				<td>' . _('订单号') . ':</td>
				<td width = 120>' . $_POST['order_number'] . '</td>
                                <input  type="hidden" name="order_number"  value="' . $_POST['order_number'] . '" />
			
				<td>' . _('状态') . ':</td>
				<td width = 120> ' . $v_status . ' </td>
			</tr>';

        $v_schedule_ship_date = date('Y-m-d', $_POST['schedule_ship_date']);
        $v_create_date = date('Y-m-d H:i:s', $_POST['create_date']);
        echo'
			<tr >
				<td>' . _('需求日') . ':</td>
				<td> ' . $v_schedule_ship_date . ' </td>
				<td>' . _('建立日') . ':</td>
				<td  width = 150> ' . $v_create_date . ' </td>
			</tr>
			<tr >
				<td>' . _('备注') . ':</td>
				<td colspan="3"> ' . $_POST['note'] . ' </td>
			</tr>
                        <tr >
				<td>' . _('加盟商') . ':</td>
				<td colspan="3"> ' . $_POST['realname'] . ' </td>
			</tr>
                     ';
        echo '</table>';
        echo '<br />';
        if ($v_type == 'A') {
            $sql2 = "SELECT
                        a.stockid item,
                        sum(a.quantity) sub_quantity,
                        a.subinventory_code,
                        b.chuanghu_bu_quantity quantity,
                        b.chuanghu_fucai_item,
                        b.chuanghu_fucai_quantity,
                        chuanghu_name,
                        chuanghu_height,
                        chuanghu_width,
                        chuanghu_quantity,
                        ifnull(send_bu_quantity,0) already_send,
                        order_line_id
                FROM
                        inv_onhand_quantity_all a,
                        sf_order_lines_all b
                WHERE
                        b.chuanghu_bu_item = stockid
                AND order_line_id =  '" . $Updateso_num . "'";
        } else {
            $sql2 = "SELECT
                        a.stockid item,
                        sum(a.quantity) sub_quantity,
                        a.subinventory_code,
                        b.chuanghu_sha_quantity quantity,
                        b.chuanghu_fucai_item,
                        b.chuanghu_fucai_quantity,
                        chuanghu_name,
                        chuanghu_height,
                        chuanghu_width,
                        chuanghu_quantity,
                        ifnull(send_sha_quantity,0) already_send,
                        order_line_id
                FROM
                        inv_onhand_quantity_all a,
                        sf_order_lines_all b
                WHERE
                        b.chuanghu_sha_item = stockid
                AND order_line_id =  '" . $Updateso_num . "'";
        }
        $result2 = DB_query($sql2, $db);
        if (DB_num_rows($result2) == 0) {
            unset($result2);
            prnMsg(_('没有找到订单详细信息，请重新登录查询！'), 'info');
        } else {
            echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '"><input type="hidden" name = "identifier" value ="' . $identifier . '">';
            echo '<div>';
            echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
            echo '<table class="selection" align="center" >';

            $tableheader = '<tr>
	                           
                                        <th  width =90>' . '窗户名' . '</th>
                                        <th width =50 >' . '高' . '</th>
					<th  width =50>' . '宽' . '</th>
                                        <th  width =50>' . '个数' . '</th>
                                        <th width =120 >' . '料号' . '</th> 
                                        <th width =80 >' . '数量' . '</th>
                                        <th width =80 >' . '已发量' . '</th>
                                         <th width =80 >' . '仓库' . '</th>
                                        <th width =80 >' . '库存数量' . '</th>
                                        <th width =80 >' . '领料数量' . '</th>
                                 
                                       
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
//
                echo '
	
                      <td>' . $myrow['chuanghu_name'] . '</td>
                      <td>' . $myrow['chuanghu_height'] . '</td>
                      <td>' . $myrow['chuanghu_width'] . '</td>
                      <td>' . $myrow['chuanghu_quantity'] . '</td>     
                      <td>' . $myrow['item'] . '</td>
                      <td>' . $myrow['quantity'] . '</td>     
                      <td>' . $myrow['already_send'] . '</td>
                      <td>' . $myrow['subinventory_code'] . '</td>
                      <td>' . $myrow['sub_quantity'] . '</td>
      
                     <td><input type="text" class="number"   name="send_qty' . $v_type . $myrow['order_line_id'] . '" size="12" maxlength="25"  value="' . $myrow['send_qty'] . '" /></td>
                    
                     <input type="hidden"   name="subinventory_code' . $v_type . $myrow['order_line_id'] . '"   value="' . $myrow['subinventory_code'] . '" />
                     <input type="hidden"   name="sub_quantity' . $v_type . $myrow['order_line_id'] . '"   value="' . $myrow['sub_quantity'] . '" />
                     <input type="hidden"   name="quantity' . $v_type . $myrow['order_line_id'] . '"   value="' . $myrow['quantity'] . '" />
                     <input type="hidden"   name="already_send' . $v_type . $myrow['order_line_id'] . '"   value="' . $myrow['already_send'] . '" />
                     <input type="hidden"   name="item' . $v_type . $myrow['order_line_id'] . '"   value="' . $myrow['item'] . '" />   
                      <input type="hidden"   name="item' . $v_type . $myrow['order_line_id'] . '"   value="' . $myrow['item'] . '" />    
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

        echo '<br />
                              <input type="submit" name="Submit" value="' . _('Enter Information') . '" />  <input type="submit" name="return" value="' . "返回上一层" . '" />&nbsp;
                                 
</div>';
    }
    echo '</div>
          </form>';
}
if (isset($_POST['Submit'])) {

    isset($_SESSION['num' . $identifier]) or die("no session");
    if ($_SESSION['num' . $identifier] == 400) {
        $_SESSION['num' . $identifier] = 500;
        $InputError = 0;
        $v_qty = 0;
        DB_Txn_Begin($db);
        foreach ($_POST as $key => $value) {
            if (mb_substr($key, 0, 8) == 'send_qty') {
                $v_type = mb_substr($key, 8, 1);
                $order_line_id = mb_substr($key, 8);

                $v_date = strtotime(Date('Y-m-d H:i:s'));
                $send_qty = $_POST['send_qty' . $order_line_id];
                $sub_quantity = $_POST['sub_quantity' . $order_line_id];
                $quantity = $_POST['quantity' . $order_line_id];
                $subinventory_code = $_POST['subinventory_code' . $order_line_id];
                $already_send = $_POST['already_send' . $order_line_id];

                if ($send_qty != '' and $send_qty <> 0) {
                    if ($send_qty > $sub_quantity) {
                        $InputError = 1;
                        $msg = '发料量大于库存！';
                        prnMsg($msg, 'error');
                        break;
                    } elseif ($send_qty > $quantity) {
                        $InputError = 1;
                        $msg = '发料量大于需求量！';
                        prnMsg($msg, 'error');
                        break;
                    } elseif ($send_qty < 0) {
                        $InputError = 1;
                        $msg = '发料量小于0！';
                        prnMsg($msg, 'error');
                        break;
                    } else {
                        $v_qty = $v_qty + $send_qty;
                        if ($v_qty > $quantity) {
                            $InputError = 1;
                            $msg = '发料量大于需求量！';
                            prnMsg($msg, 'error');
                            break;
                        } elseif ($already_send + $v_qty > $quantity) {
                            $InputError = 1;
                            $msg = '发料量大于需求量！';
                            prnMsg($msg, 'error');
                            break;
                        }
                    }
                }
            }
        }

        if ($InputError == 1) {
            $msg = $msg . '存在数据没有输入数量！';
            prnMsg($msg, 'error');
        } else {
            $v_qty_amount = 0;
            foreach ($_POST as $key => $value) {
                if (mb_substr($key, 0, 8) == 'send_qty') {
                    $v_type = mb_substr($key, 8, 1);
                    $order_line_id = mb_substr($key, 8);

                    $line_id2 = mb_substr($key, 9);
                    $v_date = strtotime(Date('Y-m-d H:i:s'));
                    $send_qty = $_POST['send_qty' . $order_line_id];
                    $v_send = 0 - $send_qty;
                    $sub_quantity = $_POST['sub_quantity' . $order_line_id];
                    $quantity = $_POST['quantity' . $order_line_id];
                    $subinventory_code = $_POST['subinventory_code' . $order_line_id];
                    $item = $_POST['item' . $order_line_id];
                    $sql = "insert into inv_onhand_quantity_all(stockid,QUANTITY,SUBINVENTORY_CODE,LAST_UPDATE_DATE,LAST_UPDATED_BY,CREATION_DATE,CREATED_BY)"
                            . "values('" . $item . "',$v_send,'" . $subinventory_code . "',$v_date, '" . $_SESSION['UserID'] . "',$v_date,'" . $_SESSION['UserID'] . "') ";
                    $result = DB_query($sql, $db);
                    $v_qty_amount = $send_qty + $v_qty_amount;
                }
            }
            if ($v_type == 'A') {
          
                $sql = "update sf_order_lines_all
                      set send_bu_quantity = ifnull(send_bu_quantity,0) + $v_qty_amount
                        where order_line_id = $line_id2";
              
                $result = DB_query($sql, $db);
            } else {
               
                $sql = "update sf_order_lines_all
                       set send_sha_quantity = ifnull(send_sha_quantity,0) + $v_qty_amount
                       where order_line_id = $line_id2";
           
                $result = DB_query($sql, $db);
            }

            DB_Txn_Commit($db);
            $msg = '输入成功！';
            prnMsg($msg, 'success');
             echo '<br /><div class="centre"><a href="' . $RootPath . '/SubSendFlag.php">' . _('继续发料') . '</a></div>';
// echo '<meta http-equiv="refresh" content="0.3" url=RequestReceive.php"/>';

            unset($_POST['order_line_id']);
            unset($_POST['order_number']);
            unset($_POST['line']);
        }
    }
} else {
    session_start() or die("session is not started");
    $_SESSION['num' . $identifier] = 400;
}
if (isset($_POST['return'])) {
//    echo 'AAAAAAAAAA';
    header('Location: SubSendFlag.php');
}
include('includes/footer.inc');
?>
