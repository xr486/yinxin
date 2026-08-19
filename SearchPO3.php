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
if (isset($_GET['Updatepo_num'])) {
    $Updatepo_num = $_GET['Updatepo_num'];
} else {
    $Updatepo_num = '';
}
$Title = _('采购单信息');
$ViewTopic = '采购单信息';
$BookMark = '采购单信息';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/CountriesArray.php');

echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/customer.png" title="' . _('采购单') .
 '" alt="" />' . ' ' . _('采购单信息') . '
	</p>';
if (isset($Updatepo_num) and $Updatepo_num != '') {
    //CreditLimit,
    $sql = "SELECT pha.po_num, pha.status, pha.note, pha.need_date, pha.creation_date, pha.amount, pha.created_by, vendor_name, v.vendor_code
FROM po_headers_all pha, vendors v
WHERE v.vendor_code = pha.vendor_code
AND po_num = '" .$Updatepo_num."'";
    $result = DB_query($sql, $db);
    $myrow = DB_fetch_array($result);
    $_POST['po_num'] = $myrow['po_num'];
    $_POST['status'] = $myrow['status'];
    $_POST['note'] = $myrow['note'];
    $_POST['amount']=$myrow['amount'];
	$_POST['vendor_code']=$myrow['vendor_code'];
    $_POST['vendor_name']=$myrow['vendor_name'];
    $_POST['need_date'] = $myrow['need_date'];
    $_POST['create_date'] = $myrow['create_date'];
    $_POST['created_by'] = $myrow['created_by'];
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
				<td>' . _('采购单号') . ':</td>
				<td width = 120>' . $_POST['po_num'] . '</td>
                                <input  type="hidden" name="po_num"  value="' . $_POST['po_num'] . '" />
			
				<td>' . _('状态') . ':</td>
				<td width = 120> ' . $v_status . ' </td>
			</tr>';
        
        $v_need_date = date('Y-m-d',$_POST['need_date']);
        $v_create_date = date('Y-m-d H:i:s',$_POST['create_date']);
        echo'
			<tr >
				<td>' . _('需求日') . ':</td>
				<td> ' . $v_need_date . ' </td>
				<td>' . _('建立日') . ':</td>
				<td  width = 150> ' . $v_create_date . ' </td>
			</tr>
			<tr >
				<td>' . _('备注') . ':</td>
				<td colspan="3"> ' . $_POST['note'] . ' </td>
			</tr>
			 <tr >
				<td>' . _('供应商代号') . ':</td>
				<td> ' . $_POST['vendor_code'] . ' </td>
			</tr>
                        <tr >
				<td>' . _('供应商名称') . ':</td>
				<td colspan="3"> ' . $_POST['vendor_name'] . ' </td>
			</tr>
                        <tr >
				<td>' . _('总额') . ':</td>
				<td width = 150> ' . $_POST['amount']. ' </td>
                               <td>' . _('下单人员') . ':</td>
				<td width = 150> ' . $_POST['created_by']. ' </td>
			</tr>';
        echo '</table>';
        echo '<br />';
        $sql2 = "SELECT line,stockid,item_desc,uom,price,quantity,po_num,amount,creation_date,quantity_received,quantity_accepted,
            quantity_deliveried,quantity_cancelled,quantity_rejected,quantity_billed,created_by
                  FROM po_lines_all
                 where 1=1 and po_num = '" .$Updatepo_num."'";
        $result2 = DB_query($sql2, $db);
        if (DB_num_rows($result2) == 0) {
            unset($result2);
            prnMsg(_('没有找到采购单详细信息，请重新登录查询！'), 'info');
        } else {
            echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '"><input type="hidden" name = "identifier" value ="' . $identifier . '">';
            echo '<div>';
            echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
            echo '<table class="selection" align="center" >';
            $tableheader = '<tr>
	                           
	                               <th width =50 >' . '采购单号' . '</th>
                                        <th width =50 >' . '行' . '</th>
                                        <th  width =60>' . '料号' . '</th>
                                        <th width =250 >' . '料号描述' . '</th>
					<th  width =50>' . '数量' . '</th>
                                        <th width =40 >' . '单位' . '</th>
                                        <th width =50 >' . '单价' . '</th> 
                                       <th width =100 >' . '金额' . '</th>
                                       <th width =180 >' . '下单日' . '</th>                                
                                       <th width =80 >' . '来料报检数量' . '</th>
                                       <th width =80 >' . '验收数量' . '</th>
                                       <th width =80 >' . '入库数量' . '</th>
                                       <th width =80 >' . '取消数量' . '</th>
                                       <th width =80 >' . '拒收数量' . '</th>
                                       <th width =80 >' . '立账数量' . '</th>
                                       
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
                echo '<td>' . $myrow['po_num'] . '</td>
		      <td>' . $myrow['line'] . '</td>
                      <td>' . $myrow['stockid'] . '</td>
                      <td>' . $myrow['item_desc'] . '</td>
                      <td>' . $myrow['quantity'] . '</td>
                      <td>' . $myrow['uom'] . '</td>
                      <td>' . $myrow['price'] . '</td>
                          <td>' . $myrow['amount'] . '</td>
                      
                      <td>' . date('Y-m-d H:i:s', $myrow['creation_date']) . '</td>                      
                     <td>' . $myrow['quantity_received'] . '</td>
                    <td>' . $myrow['quantity_accepted'] . '</td>
                    <td>' . $myrow['quantity_deliveried'] . '</td>
                    <td>' . $myrow['quantity_cancelled'] . '</td>
                    <td>' . $myrow['quantity_rejected'] . '</td>
                    <td>' . $myrow['quantity_billed'] . '</td>

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
                                <input type="submit" name="return" value="' . "返回上一层" . '" />&nbsp;
                                 
</div>';
    }
    echo '</div>
          </form>';
}
if (isset($_POST['return'])) {
//    echo 'AAAAAAAAAA';
    header('Location: PrToPOSearch.php');
}
include('includes/footer.inc');
?>
