<?php
/* $Id: SupplierTransInquiry.php 5785 2012-12-29 04:47:42Z daintree $ */

include('includes/session.inc');
$Title = '贴片异常维护';
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
        $InputError = 0;
        DB_Txn_Begin($db);
        foreach ($_POST as $key => $value) {
            if (mb_substr($key, 0, 6) == 'status') {
                $order_id = mb_substr($key, 6);
                // echo $order_id;
                $error_note = $_POST['error_note' . $order_id];
                //if ($_POST['error_note' . $order_id] != '') {
                    $sql = " UPDATE sf_orders_all
		       SET error_note       = '" . $error_note . "',
			    note_man       = '" . $_SESSION['UserID'] . "',
                            Exception_flag = 'Y'
		     WHERE order_id           = '" . $order_id . "'";
                    $result = DB_query($sql, $db);
                    //
               //} else {
                 //   $InputError = 1;
                //}
            }
        }
        if ($InputError == 1) {
            $msg = '请数据贴片异常资料！';
            prnMsg($msg, 'error');
        } else {
            DB_Txn_Commit($db);
            $msg = '维护成功！';
            prnMsg($msg, 'success');
            unset($_POST['order_id']);
            unset($_POST['order_number']);
        }
    }
} else {
    session_start() or die("session is not started");
    $_SESSION['num' . $identifier] = 400;
}


echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . '工单入库' .
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
                         <td>' . '客户代码' . ':</td>
			<td><input   type="text" name="customer_code"  /></td>'
 . '</tr><tr>';




/* echo '<td>' . _('From') . ':</td>
  <td><input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="FromDate" maxlength="10" size="11" value="' . $_POST['FromDate'] . '" /></td>
  <td>' . _('To') . ':</td>
  <td><input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="ToDate" maxlength="10" size="11" value="' . $_POST['ToDate'] . '" /></td>
  '; */
echo '</tr>
	</table>
	<br />
	<div class="centre">
		<input type="submit" name="ShowResults" value="' . '查询' . '" />
	</div>
	<br />
    </div>
	</form>';
if (isset($_POST['ShowResults'])) {
    // $SQL_FromDate = FormatDateForSQL($_POST['FromDate']);
    // $SQL_ToDate = FormatDateForSQL($_POST['ToDate']);
    $sql = "select order_id,
               order_number,
              start_work_date,
              end_work_date
          from sf_orders_all a where  complete_flag = 'Y'
          and Exception_flag is null
        
      ";
    //purchase_flag = 'Y',需要下PO,   purchase_flag = 'O',PO已下，purchase_flag = 'D',PO已入，

    if (empty($_POST['order_number']) == 0) {
        $sql .= " AND a.order_number= '" . $_POST['order_number'] . "'";
    }

    if (empty($_POST['customer_code']) == 0) {
        $sql .= " AND customer_number like  '%" . $_POST['customer_code'] . "%'";
    }
    $sql .= " ORDER BY order_number";



    $TransResult = DB_query($sql, $db);
    $ErrMsg = _('订单查询错误，请查看所选订单') . ' - ' . DB_error_msg($db);
    $DbgMsg = _('The SQL that failed was');
    if (DB_num_rows($TransResult) == 0) {
        unset($TransResult);
        prnMsg(_('没有找到需要入庫的工单，请重新输入条件查询！'), 'info');
    } else {
        echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '"><input type="hidden" name = "identifier" value ="' . $identifier . '">';
        echo '<div>';
        echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
        echo '<table class="selection" align="center" >';
        $tableheader = '<tr>
	                              <th width =50>' . '确认' . '</th>
	                                <th width =100>' . '订单号' . '</th>
					<th  width =100>' . '开工日期' . '</th>
                                        <th width =100 >' . '完工日期' . '</th>
                                       <th width =200 >' . '贴片异常' . '</th>
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
            echo '<td width =200>' . date('Y-m-d H:i:s', $myrow['start_work_date']) . ' </td>';
            echo '<td  width =200>' . date('Y-m-d H:i:s', $myrow['end_work_date']) . '</td>
          
        <td><input type="text"    name="error_note' . $myrow['error_note'] . '" size="25" maxlength="100"  value="' .$_POST['error_note'] . '"  /></td>
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
//}
include('includes/footer.inc');
?>