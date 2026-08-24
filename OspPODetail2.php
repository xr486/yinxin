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
if (isset($_GET['Updatewaixie_num'])) {
    $Updatewaixie_num = $_GET['Updatewaixie_num'];
} else {
    $Updatewaixie_num = '';
}
$Title = _('外协采购单信息');
$ViewTopic = '外协采购单信息';
$BookMark = '外协采购单信息';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/CountriesArray.php');

echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/customer.png" title="' . _('外协采购单') .
 '" alt="" />' . ' ' . _('外协采购单信息') . '
	</p>';
if (isset($Updatewaixie_num) and $Updatewaixie_num != '') {
    //CreditLimit,
    $sql = "SELECT wha.waixie_num, wha.status, wha.creation_date, wha.po_payment_amount,wha.po_invoice_amount, wha.created_by, wha.youhui_amount, vendor_name,wha.po_all_amount,wha.vendor_code,v.vendor_address,v.vendor_contacts,v.contacts_phone
FROM waixie_headers_all wha, vendors v
WHERE v.vendor_code = wha.vendor_code
AND waixie_num= '" .$Updatewaixie_num."'";
    $result = DB_query($sql, $db);
    $myrow = DB_fetch_array($result);
    $_POST['waixie_num'] = $myrow['waixie_num'];
    $_POST['status'] = $myrow['status'];
    $_POST['amount']=$myrow['amount'];
    $_POST['vendor_code']=$myrow['vendor_code'];
    $_POST['vendor_name']=$myrow['vendor_name'];
    $_POST['create_date'] = $myrow['creation_date'];
    $_POST['vendor_address']=$myrow['vendor_address'];
    $_POST['vendor_contacts']=$myrow['vendor_contacts'];
    $_POST['contacts_phone'] = $myrow['contacts_phone'];
    $_POST['created_by'] = $myrow['created_by'];
    $_POST['payment_term'] = $myrow['payment_term'];
    $_POST['youhui_amount'] = $myrow['youhui_amount'];
    $_POST['po_all_amount'] = $myrow['po_all_amount'];
    $_POST['po_payment_amount'] = $myrow['po_payment_amount'];
    $_POST['po_invoice_amount'] = $myrow['po_invoice_amount'];
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
		      $v_order_date = date('Y-m-d',$_POST['order_date']);
        $v_create_date = date('Y-m-d H:i:s',$_POST['create_date']);
        echo '<table class="selection" id="SignFrame">
                       <tr class="EvenTableRows">
				<td>' . _('外协采购单号') . ':</td>
				<td width = 120>' . $_POST['waixie_num'] . '</td>
                  <input  type="hidden" name="waixie_num"  value="' . $_POST['waixie_num'] . '" />
			
				<td>' . _('状态') . ':</td>
				<td width = 120> ' . $v_status . ' </td>
				<td>' . _('建立日') . ':</td>
				<td  width = 150> ' . $v_create_date . ' </td></tr>
                ';
        
  
        echo'
			<tr >		
				
				
				<td>' . _('供应商代号') . ':</td>
                <td> ' . $_POST['vendor_code'] . ' </td>

                <td>' . _('供应商名称') . ':</td>
                <td> ' . $_POST['vendor_name'] . ' </td>
            </tr>
            <tr>

                <td>' . _('联系人') . ':</td>
                <td> ' . $_POST['vendor_contacts'] . ' </td>

                <td>' . _('电话') . ':</td>
                <td> ' . $_POST['contacts_phone'] . ' </td>

                <td>' . _('地址') . ':</td>
                <td> ' . $_POST['vendor_address'] . ' </td>
                
			</tr>

			<tr >
				
			 <td>' . _('订单总金额') . ':</td>
				<td width = 120> ' . $_POST['po_all_amount']. ' </td>
                
                <td>' . _('优惠金额') . ':</td>
				<td width = 120> ' . $_POST['youhui_amount']. ' </td>
				<td>' . _('订单应付金额') . ':</td>
				<td width = 120> ' . $_POST['po_payment_amount']. ' </td>
                
                <tr>
                <td>' . _('订单应开票金额') . ':</td>
				<td width = 120> ' . $_POST['po_invoice_amount']. ' </td>
				<td>' . _('下单人员') . ':</td>
				<td width = 150> ' . $_POST['created_by']. ' </td>
                </tr>
			</tr>';

			
		   

        echo '</table>';
        echo '<br />';
        $sql2 = "SELECT a.waixie_line,a.line,a.po_line_id,a.order_number,a.uom,a.price,a.quantity,a.waixie_num,a.line_amount,a.operation_code,a.need_remark,gongshi,a.creation_date,
            a.created_by,ifnull(quantity_deliveried,0) quantity_deliveried,ifnull(quantity_cancelled,0) quantity_cancelled,ifnull(quantity_rejected,0) quantity_rejected,ifnull(quantity_billed,0) quantity_billed,
            (select locationname from locations d where d.loccode=a.subinventory_code) locationname,a.need_date
                  FROM waixie_lines_all a,so_lines_all b
                 where a.order_number=b.order_number   and a.line=b.line and waixie_num = '" .$Updatewaixie_num."' order by a.waixie_line";
        $result2 = DB_query($sql2, $db);
        if (DB_num_rows($result2) == 0) {
            unset($result2);
            prnMsg(_('没有找到外协采购单详细信息，请重新登录查询！'), 'info');
        } else {
            echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '"><input type="hidden" name = "identifier" value ="' . $identifier . '">';
            echo '<div>';
            echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
            echo '<table class="selection" align="center" >';
            $tableheader = '<tr>
	                           
	                               
                                        <th width =50 >' . '行' . '</th>
                                        <th>' . '业务订单' . '</th>
                                        <th width =50 >' . '订单行' . '</th>
                                        <th width =120 >' . '交期日期' . '</th>
                                        <th width =100 >' . '外协工序' . '</th>
                                         <th width =100 >' . '要求' . '</th>
					                     <th width =80 >' . '订单数量' . '</th>
                                        <th width =60 >' . '单位' . '</th>
                                        <th width =80 >' . '单价' . '</th> 
                                       <th width =80 >' . '金额' . '</th> 
                                       <th width =80 >' . '工时' . '</th>   
                                       <th width =80 >' . '入库数量' . '</th>

                                       
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
		      <td>' . $myrow['waixie_line'] . '</td>
                      <td>' . $myrow['order_number'] . '</td>
                      <td>' . $myrow['line'] . '</td>
                      <td>' . date('Y-m-d',$myrow['need_date']) . '</td>
                      <td>' . $myrow['operation_code'] . '</td>
				      <td>' . $myrow['need_remark'] . '</td>
                      <td>' . $myrow['quantity'] . '</td>
                      <td>' . $myrow['uom'] . '</td>
                      <td>' . $myrow['price'] . '</td>
                       <td>' . $myrow['line_amount'] . '</td>
                       <td>' . $myrow['gongshi'] . '</td>                 
                     <td>' . $myrow['quantity_deliveried'] . '</td>

                    

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

		 
/* $sql2 = "SELECT
	 	file_patch,creation_date,created_by,file_name
FROM waixie_headers_all_file  
        where  order_number = '" .$Updatewaixie_num."'";
        $result2 = DB_query($sql2, $db); */
        /* if (DB_num_rows($result2) == 0) {
            unset($result2);
          //  prnMsg(_('无附件'), 'info');
        } else { */
			/* echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/customer.png" title="' . _('外协采购单头信息') .
 '" alt="" />' . ' ' . _('外协采购单附件信息') . '
	</p>';
            echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '"><input type="hidden" name = "identifier" value ="' . $identifier . '">';
            echo '<div>';
            echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
            echo '<table class="selection" align="center" >';
            $tableheader = '<tr>
	                           
                                        <th width =150 >' . '附件名称' . '</th>
										<th width =190 >' . '上传时间' . '</th>
										<th width =80 >' . '上传人员' . '</th>
                                        <th  width =50>' . '下载' . '</th>
									 
                                       
                                       
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
 
                echo '
		              <td>' . $myrow['file_name'] . '</td>
                      <td>' . date('Y-m-d h:i:s',$myrow['creation_date']) . '</td>
					  <td>' . $myrow['created_by'] . '</td>                      
					  <td><a href="' . $RootPath . '/' . $myrow['file_patch'] . '" target="_blank">' . '下载' . '</td>
                     
                        

        </tr>';
                $RowCounter++;
                If ($RowCounter == 500) {
                    $RowCounter = 1;
                    echo $tableheader;
                }
            }
            echo '</table> ';


            echo '</div>
          </form>'; */
        /* } */


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

if (isset($_POST['uploadfile'])) {
header("Location: POUploadNewfile.php?OrderNum=$OrderNum");
}

include('includes/footer.inc');
?>
