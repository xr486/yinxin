<?php
/* $Id: SupplierTransInquiry.php 5785 2012-12-29 04:47:42Z daintree $ */

include('includes/session.inc');
$Title = 'PO入库量查询';
include('includes/header.inc');
if (isset($_GET['identifier'])) {
    $_POST['identifier'] = $_GET['identifier'];
}
if (!isset($_POST['identifier'])) {
    $identifier = date('U');
} else {
    $identifier = $_POST['identifier'];
}

echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . 'PO入库量查询' .
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


/*
if (!isset($_POST['FromDate'])) {
    $_POST['FromDate'] = Date($_SESSION['DefaultDateFormat'], mktime(0, 0, 0, Date('m'), -30, Date('Y')));
}
if (!isset($_POST['ToDate'])) {
    $_POST['ToDate'] = Date($_SESSION['DefaultDateFormat']);
}*/
if (!isset($_POST['FromDate'])) {
    $_POST['FromDate'] = date( "Y-m-d", mktime(0,0,0,date("m"),date("d")-30,date("Y")));
}
if (!isset($_POST['ToDate'])) {
    $_POST['ToDate'] = Date('Y-m-d');
}
echo '<td>' . _('From') . ':</td>
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
$SQL_FromDate = FormatDateForSQL($_POST['FromDate']);
$SQL_ToDate = FormatDateForSQL($_POST['ToDate']);
$sql = "select order_id,
               order_number,
               purchase_qty,
               purchase_price,
               supplier_name,
               supplier_number,
			   receive_qty
          from sf_orders_all a where 
          receive_qty is not null
        
      ";
if (empty($_POST['order_number']) == 0) {
    $sql .= " AND a.order_number= '" . $_POST['order_number'] . "'";
}
if (empty($_POST['customer_name']) == 0) {
    $sql .= " AND a.supplier_name like '%" . $_POST['customer_name'] . "%'";
}
if (empty($_POST['customer_code']) == 0) {
    $sql .= " AND a.supplier_number like '%" . $_POST['customer_code'] . "%'";
}
/*
if (Is_Date($_POST['FromDate'])) {
    $SQL_FromDate = strtotime( $_POST['FromDate']);
    $sql .= " and a.purchase_date >= '" . $SQL_FromDate . "' ";
}
if (Is_Date($_POST['ToDate'])) {
    $SQL_ToDate = strtotime( $_POST['ToDate']) + 86400;
    $sql .= " and a.purchase_date <='" . $SQL_ToDate . "' ";
}*/
if (empty($_POST['FromDate'])==0) {
    $SQL_FromDate = strtotime( $_POST['FromDate']);
    //echo $SQL_FromDate;
    $sql .= " and a.receive_date >= '" . $SQL_FromDate . "' ";
}
if (empty($_POST['ToDate'])==0) {
    $SQL_ToDate = strtotime( $_POST['ToDate']) + 86400;
     //echo $SQL_ToDate;
    $sql .= " and a.receive_date <='" . $SQL_ToDate . "' ";
}
$sql .= " ORDER BY order_number";

$TransResult = DB_query($sql, $db);
$ErrMsg = _('订单查询错误，请查看所选订单') . ' - ' . DB_error_msg($db);
$DbgMsg = _('The SQL that failed was');
if (DB_num_rows($TransResult) == 0) {
    unset($TransResult);
    prnMsg(_('没有找到采购单，请重新输入条件查询！'), 'info');
} else {
    echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '"><input type="hidden" name = "identifier" value ="' . $identifier . '">';
    echo '<div>';
//echo '<div style="width:1300px;height:400px;overflow-x: hidden; overflow-y: scroll;">';
    echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
    echo '<table class="selection" align="center" >';
    $tableheader = '<tr>
	                                <th >' . '订单号' . '</th>
									 <th width =250>' . '供应商名称' . '</th>
                                      
				
                                        <th >' . '金额' . '</th>
											<th>' . '採購数量' . '</th>
										<th >' . '入庫數量' . '</th>
                                        
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
        echo '<td width=100><font color="red">' . $myrow['order_number'] . '</font></td>';
		echo '<td width=250>' . $myrow['supplier_name'] . '</td>';
       
        echo '<td width=100>' . $myrow['purchase_price'] . '</td>';
        echo '<td width=100>' . $myrow['purchase_qty'] . '</td>
		 <td width=100>' . $myrow['receive_qty'] . '   </td>
             <input type="hidden" name="order_id[]" value="' . $myrow['order_id'] . '" />
        </tr>';
        $RowCounter++;
        If ($RowCounter == 500) {
            $RowCounter = 1;
            echo $tableheader;
        }
    }
    echo '</table> ';


    echo '</div>';
    echo '
          </form>';
}
//}
include('includes/footer.inc');
?>