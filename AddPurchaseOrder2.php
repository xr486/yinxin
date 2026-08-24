<?php

/* $Id: SupplierTransInquiry.php 5785 2012-12-29 04:47:42Z daintree $ */

include('includes/session.inc');
$Title = '新建采购单';
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

        for ($i = 0; $i < count($_POST["purchase_qty"]); $i++) {
            //echo $_POST["vendor_name"][$i];
            if ($_POST["purchase_qty"][$i] != '') {
                if ($_POST["purchase_price"][$i] != '') {
                    if ($_POST["vendor_name"][$i] != '') {
                        if ($_POST["demand_date"][$i] != '') {
                            $InputError = 0;
                        }
                    }
                }
            }
        }
        if ($InputError == 1) {
            $_SESSION['num' . $identifier] = 400;
            $msg = '请输入数量、金额、供应商名称和需求日不能为空,再重新操作！';
            prnMsg($msg, 'error');
            echo '<br /><div class="centre"><a a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?identifier=' . $identifier . '">' . _('返回继续下PO') . '</a></div>';
        } else {
            for ($i = 0; $i < count($_POST["purchase_qty"]); $i++) {
                if ($_POST["purchase_qty"][$i] != '') {
                    DB_Txn_Begin($db);
                  
                    $v_date = strtotime(Date('Y-m-d H:i:s'));
                    $v_demand_date = strtotime($_POST["demand_date"][$i]);
                    $sql = "UPDATE sf_orders_all
		       SET purchase_qty       = '" . $_POST["purchase_qty"][$i] . "',
                           purchase_amount     = '" . $_POST["purchase_price"][$i] . "',
                           supplier_name      = '" . $_POST["vendor_name"][$i] . "',
                           purchase_date      = '" . $v_date . "',
                           purchase_desc      = '" . $_POST["purchase_desc"][$i] . "',
                           demand_date        = $v_demand_date,
                           purchase_man       = '" . $_SESSION['UserID'] . "'
		     WHERE order_id           = '" . $_POST["order_id"][$i] . "'";
                    $result = DB_query($sql, $db);
                    DB_Txn_Commit($db);
                }
            }

            $msg = '新建采购单成功！';
            prnMsg($msg, 'success');
// echo '<meta http-equiv="refresh" content="0.3" url=RequestReceive.php"/>';
        }
        unset($_POST['order_id']);
        unset($_POST['order_number']);
        unset($_POST['purchase_qty']);
        unset($_POST['purchase_price']);
        unset($_POST['vendor_name']);
    }
} else {
    session_start() or die("session is not started");
    $_SESSION['num' . $identifier] = 400;
}


echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . '新建采购单' .
 '" alt="" />' . ' ' . $Title . '
	</p>';

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post"><input type="hidden" name = "identifier" value ="' . $identifier . '">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
// <td>' . _('Type') . ':</td> 20
echo '<table class="selection">
		<tr>	
			<td>' . '订单号' . ':</td>
			<td><input   type="text" name="order_number" value="' . $_POST['order_number'] . '" /></td>
                         <td>' . '客户名称' . ':</td>
			<td ><input   type="text" name="customer_name" value="' . $_POST['customer_name'] . '" /></td>
                         <td>' . '客户代码' . ':</td>
			<td><input   type="text" name="customer_code"  value="' . $_POST['customer_code'] . '"/></td></tr>';
if (!isset($_POST['FromDate'])) {
    $_POST['FromDate'] = Date($_SESSION['DefaultDateFormat'], mktime(0, 0, 0, Date('m'), -30, Date('Y')));
}
if (!isset($_POST['ToDate'])) {
    $_POST['ToDate'] = Date($_SESSION['DefaultDateFormat']);
}
/*
  echo '<tr><td>' . '订单日期' . _('From') . ':</td>
  <td><input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="FromDate" maxlength="10" size="11" value="' . $_POST['FromDate'] . '" /></td>
  <td>' . _('To') . ':</td>
  <td><input type="text"  onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="ToDate" maxlength="10" size="11" value="' . $_POST['ToDate'] . '" /></td>
  </tr> ';
 */
echo'	</table>
	<br />
	<div class="centre">
		<input type="submit" name="ShowResults" value="' . '查询' . '" />
	</div>
	<br />
    </div>
	</form>';

$sql = "select order_id,
               order_number
          from sf_orders_all a 
         where purchase_flag = 'Y' 
           and gerber_flag = 'Y'
           and coordinate_flag = 'Y'
           and bom_flag = 'Y'
           and point_flag = 'Y'
           and purchase_qty is null
      ";
if (empty($_POST['order_number']) == 0) {
    $sql .= " AND a.order_number= '" . $_POST['order_number'] . "'";
}
if (empty($_POST['customer_name']) == 0) {

    $sql .= "  AND a.customer_name like '%" . $_POST['customer_name'] . "%'";
}
if (empty($_POST['customer_code']) == 0) {
    $sql .= " AND customer_number like  '%" . $_POST['customer_code'] . "%'";
}
/*
  if (Is_Date($_POST['FromDate'])) {
  $SQL_FromDate = strtotime( $_POST['FromDate']);
  $sql .= " and a.creation_date >= '" . $SQL_FromDate . "' ";
  }
  if (Is_Date($_POST['ToDate'])) {
  $SQL_ToDate = strtotime( $_POST['FromDate']) + 86400;
  $sql .= " and a.creation_date <='" . $SQL_ToDate . "' ";
  } */

$sql .= " ORDER BY order_number";
//echo $sql;
if (isset($_POST['ShowResults'])) {
    $TransResult = DB_query($sql, $db);
    $ErrMsg = _('订单查询错误，请查看所选订单') . ' - ' . DB_error_msg($db);
    $DbgMsg = _('The SQL that failed was');

    if (DB_num_rows($TransResult) == 0) {
        unset($TransResult);
        prnMsg(_('没有找到需要新建的采购单，请重新输入条件查询！'), 'info');
    } else {
        echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '"><input type="hidden" name = "identifier" value ="' . $identifier . '">';
        echo '<div>';
//echo '<div style="width:1300px;height:400px;overflow-x: hidden; overflow-y: scroll;">';
        echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
        echo '<table class="selection" align="center" >';
        $tableheader = '<tr>
	                                <th width = 120>' . '订单号' . '</th>
					<th  width = 100 >' . '数量' . '</th>
                                        <th width = 100 >' . '金额' . '</th>
                                        <th  width = 250 >' . '供应商名称' . '</th>
                                        <th  width = 100 >' . '需求日' . '</th>
                                        <th  width = 100 >' . '描述' . '</th>
                                        
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
            echo '<td><font color="red">' . $myrow['order_number'] . '</font></td>';
            echo '<td><input type="text" class="number"   name="purchase_qty[]" size="8" maxlength="20" /></td>';
            echo '<td><input type="text" class="number"   name="purchase_price[]" size="8" maxlength="20" /></td>';
           
            $sqla = "select vendor_name from vendors";

            $result = DB_query($sqla, $db);
            echo '<td width =200 ><select name="vendor_name[]">';
            while ($Vendorrow = DB_fetch_array($result)) {

                echo '<option selected="selected" value="' . $Vendorrow['vendor_name'] . '">' . $Vendorrow['vendor_name'] . '</option>';
            }

            echo '</select></td>';
            echo '<td><input type="text" onfocus="WdatePicker()"  name="demand_date[]" size="15" maxlength="20" /></td>';
            echo '<td><input type="text"  name="purchase_desc[]" size="20" maxlength="100" /></td>';
            // echo '<td><input type="text"  name="supplier_name[]" size="30" maxlength="30" /></td>';
            // echo '<td><input type="text"  name="supplier_number[]" size="15" maxlength="20" />   </td>
            echo '        <input type="hidden" name="order_id[]" value="' . $myrow['order_id'] . '" />
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