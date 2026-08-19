<?php
/* $Id: SupplierTransInquiry.php 5785 2012-12-29 04:47:42Z daintree $ */

include('includes/session.inc');
$Title = 'PO修改';
include('includes/header.inc');
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
        $InputError = 1;
        DB_Txn_Begin($db);
        foreach ($_POST as $key => $value) {
            if (mb_substr($key, 0, 6) == 'status') {
                $order_id = mb_substr($key, 6);
                // echo $order_id;
                $purchase_qty = $_POST['purchase_qty' . $order_id];
                $purchase_price = $_POST['purchase_price' . $order_id];
                    $v_date = strtotime(Date('Y-m-d H:i:s'));
                $sql1 = " UPDATE sf_orders_all
		       SET purchase_qty       = '" . $purchase_qty . "',
                           purchase_price     = '" . $purchase_price . "',
                           purchase_date      = '" .$v_date . "'
                           
		     WHERE order_id           = '" . $order_id . "'";
                $result = DB_query($sql1, $db);
                //
            }
        }
        DB_Txn_Commit($db);
        $msg = '修改PO单成功！';
        prnMsg($msg, 'success');
// echo '<meta http-equiv="refresh" content="0.3" url=RequestReceive.php"/>';
    }
    unset($_POST['order_id']);
    unset($_POST['order_number']);
    unset($_POST['purchase_qty']);
    unset($_POST['purchase_price']);
} else {
    session_start() or die("session is not started");
    $_SESSION['num' . $identifier] = 400;
}


echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . '派车单入库' .
 '" alt="" />' . ' ' . $Title . '
	</p>';

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post"><input type="hidden" name = "identifier" value ="' . $identifier . '">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
// <td>' . _('Type') . ':</td> 20
echo '<table class="selection">
		<tr>	
			<td>' . '订单号' . ':</td>
			<td><input   type="text" name="order_number"  /></td>
                     
                         <td>' . '供应商名称' . ':</td>
			<td><input   type="text" name="customer_name"  /></td>
                        </tr><tr>';



if (!isset($_POST['FromDate'])) {
    $_POST['FromDate'] = Date($_SESSION['DefaultDateFormat'], mktime(0, 0, 0, Date('m'), -30, Date('Y')));
}
if (!isset($_POST['ToDate'])) {
    $_POST['ToDate'] = Date($_SESSION['DefaultDateFormat']);
}
echo '<td>' . '采购单' . _('From') . ':</td>
		<td><input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="FromDate" maxlength="10" size="11" value="' . $_POST['FromDate'] . '" /></td>
		<td>' . _('To') . ':</td>
		<td><input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="ToDate" maxlength="10" size="11" value="' . $_POST['ToDate'] . '" /></td>
	</tr>
	</table>
	<br />
	<div class="centre">
		<input type="submit" name="ShowResults" value="' . '查询' . '" />
	</div>
	<br />
    </div>
	</form>';
if (isset($_POST['ShowResults'])) {
    $SQL_FromDate = FormatDateForSQL($_POST['FromDate']);
    $SQL_ToDate = FormatDateForSQL($_POST['ToDate']);
    $sql = "select order_id,
               order_number,
               purchase_qty,
               purchase_price,
               supplier_name,
               supplier_number
          from sf_orders_all a where purchase_qty is not null and receive_qty is null
        
      ";
    if (empty($_POST['order_number']) == 0) {
        $sql .= " AND a.order_number= '" . $_POST['order_number'] . "'";
    }
    if (empty($_POST['customer_name']) == 0) {
        $sql .= " AND a.supplier_name like '%" . $_POST['customer_name'] . "%'";
    }
    if (Is_Date($_POST['FromDate'])) {
        $SQL_FromDate = strtotime($_POST['FromDate']);
        $sql .= " and a.purchase_date >= '" . $SQL_FromDate . "' ";
    }
    if (Is_Date($_POST['ToDate'])) {
        $SQL_ToDate = strtotime($_POST['ToDate']) + 86400;
        $sql .= " and a.purchase_date <='" . $SQL_ToDate . "' ";
    }
    $sql .= " ORDER BY order_number";

    $TransResult = DB_query($sql, $db);
    $ErrMsg = _('订单查询错误，请查看所选订单') . ' - ' . DB_error_msg($db);
    $DbgMsg = _('The SQL that failed was');
    if (DB_num_rows($TransResult) == 0) {
        unset($TransResult);
        prnMsg(_('没有找到需要修改PO的订单，请重新输入条件查询！'), 'info');
    } else {
        echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '"><input type="hidden" name = "identifier" value ="' . $identifier . '">';
        echo '<div>';
//echo '<div style="width:1300px;height:400px;overflow-x: hidden; overflow-y: scroll;">';
        echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
        echo '<table class="selection" align="center" >';
        $tableheader = '<tr>     <th width =100>' . '确认' . '</th>
	                                <th width =100>' . '订单号' . '</th>
					<th  width =100>' . '数量' . '</th>
                                        <th width =100 >' . '金额' . '</th>
                                         <th width =250 >' . '供应商名称' . '</th>
                                        
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
//
            echo '<td><input type="checkbox" name="status' . $myrow['order_id'] . '" /></td>
		<td><font color="red">' . $myrow['order_number'] . '</font></td>';
            echo '<td><input type="text" class="number"   name="purchase_qty' . $myrow['order_id'] . '" size="8" maxlength="8"  value="' . $myrow['purchase_qty'] . '"  /></td>';
            echo '<td><input type="text" class="number"   name="purchase_price' . $myrow['order_id'] . '" size="8" maxlength="8"  value="' . $myrow['purchase_price'] . '" /></td>';
            echo '<td>' . $myrow['supplier_name'] . '</td>
            
        </tr>';
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
		</div>
          </form>';
    }
}
include('includes/footer.inc');
?>