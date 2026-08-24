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
    $sql = "SELECT pha.po_num, pha.status, pha.note, pha.need_date, pha.creation_date, pha.amount, pha.created_by, vendor_name,pha.vendor_code
FROM po_headers_all pha, vendors v
WHERE v.vendor_code = pha.vendor_code
AND po_num= '" .$Updatepo_num."'";
    $result = DB_query($sql, $db);
    $myrow = DB_fetch_array($result);
    $_POST['po_num'] = $myrow['po_num'];
    $_POST['status'] = $myrow['status'];
    $_POST['note'] = $myrow['note'];
    $_POST['amount']=$myrow['amount'];
    $_POST['vendor_name']=$myrow['vendor_name'];
	$_POST['vendor_code']=$myrow['vendor_code'];
    $_POST['need_date'] = $myrow['need_date'];
    $_POST['creation_date'] = $myrow['creation_date'];
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
		      $v_need_date = date('Y-m-d',$_POST['need_date']);
        $v_creation_date = date('Y-m-d H:i:s',$_POST['creation_date']);
        echo '<table class="selection" id="SignFrame">
                       <tr class="EvenTableRows">
				<td>' . _('采购单号') . ':</td>
				<td colspan="3">' . $_POST['po_num'] . '</td>
                <input  type="hidden" name="po_num"  value="' . $_POST['po_num'] . '" />
			
				<td>' . _('状态') . ':</td>
				<td width = 120> ' . $v_status . ' </td>
					</tr>';

        echo'
		     <tr >
				<td>' . _('需求日') . ':</td>
				<td colspan="3"> ' . $v_need_date . ' </td>

				<td>' . _('建立日') . ':</td>
				<td  width = 150> ' . $v_creation_date . ' </td>
			</tr>

              <tr >
				<td>' . _('供应商代码') . ':</td>
				<td colspan="3"> ' . $_POST['vendor_name'] . ' </td>
			
				<td>' . _('供应商名称') . ':</td>
				<td width = 150> ' . $_POST['vendor_code']. ' </td>
					
			</tr>

                <tr >
                  <td>' . _('下单人员') . ':</td>
				<td colspan="3"> ' . $_POST['created_by']. ' </td>
				<td>' . _('备注') . ':</td>
				<td  > ' . $_POST['note'] . ' </td>
			</tr>';
        echo '</table>';
        echo '<br />';
        $sql2 = "SELECT a.line,a.stockid,b.item_desc,b.item_spec,a.uom,a.price,a.quantity,a.po_num,a.amount,a.creation_date,ifnull(quantity_received,0) quantity_received,a.created_by
                  FROM po_lines_all a,sf_item_no b
                 where a.stockid=b.item_no   and po_num = '" .$Updatepo_num."'";
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
	                           
	                               
                                        <th width =50 >' . '行' . '</th>
                                        <th>' . '料号' . '</th>
                                        <th width =200 >' . '料号名称' . '</th>
                                         <th width =200 >' . '规格型号' . '</th>
					                     <th  width =50>' . '采购数量' . '</th>
                                        <th width =40 >' . '单位' . '</th>
                                        <th width =50 >' . '单价' . '</th> 
                                       <th width =50 >' . '金额' . '</th>                               
                                       <th width =80 >' . '收货数量' . '</th>
                                    
                                      
                                       
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
		      <td>' . $myrow['line'] . '</td>
                      <td>' . $myrow['stockid'] . '</td>
                      <td>' . $myrow['item_desc'] . '</td>
				      <td>' . $myrow['item_spec'] . '</td>
                      <td>' . $myrow['quantity'] . '</td>
                      <td>' . $myrow['uom'] . '</td>
                      <td>' . $myrow['price'] . '</td>
                       <td>' . $myrow['amount'] . '</td>                 
                     <td>' . $myrow['quantity_received'] . '</td>
                  
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
                                <input type="submit" name="return" value="' . "关闭当前页面" . '" />&nbsp;
                                 
</div>';
    }
    echo '</div>
          </form>';
}
if (isset($_POST['return'])) {
echo '<script>window.close();</script>'; 
}
include('includes/footer.inc');
?>
