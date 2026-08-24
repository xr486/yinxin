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
if (isset($Updatepo_num) and $Updatepo_num != '') {
    //CreditLimit,
    $sql = "SELECT a.po_num, a.status,order_type,a.note, a.order_date, a.creation_date, a.po_all_amount,a.all_line_amount,a.tax_amount,a.app_remark, b.vendor_name, a.vendor_code, a.created_by,a.po_all_amount,a.youhui_amount,a.payment_type,a.tax_name,a.payment_term,a.delivery_coyname,a.delivery_date,a.tax_name
FROM po_headers_all a, vendors b
WHERE 1 =1
AND a.vendor_code = b.vendor_code  
AND po_num ='" . $Updatepo_num . "'";
    $result = DB_query($sql, $db);
    $myrow = DB_fetch_array($result);
    $_POST['po_num'] = $myrow['po_num'];
    $_POST['status'] = $myrow['status'];
    $_POST['note'] = $myrow['note'];
    $_POST['po_all_amount'] = $myrow['po_all_amount'];
	$_POST['app_remark'] = $myrow['app_remark'];
    $_POST['order_date'] = $myrow['order_date'];
    $_POST['create_date'] = $myrow['creation_date'];
    $_POST['vendor_name'] = $myrow['vendor_name'];
    $_POST['order_type'] = $myrow['order_type'];
    $_POST['vendor_code'] = $myrow['vendor_code'];
    $_POST['created_by'] = $myrow['created_by']; 
    $_POST['tax_name'] = $myrow['tax_name']; 
	$_POST['youhui_amount'] = $myrow['youhui_amount'];
	$_POST['po_all_amount'] = $myrow['po_all_amount'];
    $_POST['payment_term'] = $myrow['payment_term'];
    $_POST['tax_amount'] = $myrow['tax_amount'];
    $_POST['all_line_amount'] = $myrow['all_line_amount'];
    $_POST['payment_type'] = $myrow['payment_type'];
    $_POST['tax_name'] = $myrow['tax_name']; 
    $_POST['delivery_coyname'] = $myrow['delivery_coyname']; 
    $_POST['delivery_date'] = $myrow['delivery_date']; 
    
    if (!isset($_GET['delete'])) {
        echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">'
        . '<input type="hidden" name = "identifier" value ="' . $identifier . '">';
        echo '<div>';
        echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
       
		 $v_order_date = date('Y-m-d',$_POST['order_date']);
        $v_create_date = date('Y-m-d H:i:s', $_POST['create_date']);
		if ($_POST['delivery_date']>1) {
        $v_delivery_date = date('Y-m-d', $_POST['delivery_date']);
		}
        echo '<table class="selection" id="SignFrame">
                       <div class="text-nav">
				<div class="text-nav-1"><div>' . _('外协采购单号') . ':</div>
				<input type="text"   autocomplete="off"   name="po_num" readonly="readonly" value="' . $_POST['po_num'] . '" /></div>
                
			
                <div class="text-nav-1"><div>' . _('状态') . ':</div>
				<input type="text"   autocomplete="off"   readonly="readonly" value="' .  $_POST['status'] . '" />  </div>
                <div class="text-nav-1"><div>' . _('建立日') . ':</div>
				<input type="text"   autocomplete="off"   readonly="readonly" value="' . $v_create_date . '" /> </div>
                <div class="text-nav-1"><div>' . _('下单人员') . ':</div>
				<input type="text"   autocomplete="off"   readonly="readonly" value="' . $_POST['created_by'] . '" /> </div>
				';
       
        echo'
                <div class="text-nav-1"><div>' . _('采购日期') . ':</div>
				<input type="text"   autocomplete="off"   readonly="readonly" value="' . $v_order_date . '" /> </div>
				<div class="text-nav-1"><div>' . _('供应商代号') . ':</div>
				<input type="text"   autocomplete="off"   readonly="readonly" value="' . $_POST['vendor_code'] . '" /></div>
				 
				<div class="text-nav-1"><div>' . _('供应商名称') . ':</div>
				<input type="text"   autocomplete="off"   readonly="readonly" value="' . $_POST['vendor_name'] . '" /></div>
                <div class="text-nav-1"><div>' . _('付款条件') . ':</div>
				<input type="text"   autocomplete="off"   readonly="readonly" value="' . $_POST['payment_term'] . '" /></div>
                <div class="text-nav-1"><div>' . _('税金') . ':</div>
				<input type="text"   autocomplete="off"   readonly="readonly" value="' . $_POST['tax_amount'] . '" /> </div>
                <div class="text-nav-1"><div>' . _('未税金额') . ':</div>
				<input type="text"   autocomplete="off"   readonly="readonly" value="' . $_POST['all_line_amount'] . '" /> </div>        
                <div class="text-nav-1"><div>' . _('含税金额') . ':</div>
				<input type="text"   autocomplete="off"   name="po_all_amount" readonly="readonly" value="' . $_POST['po_all_amount'] . '" /></div>
				<div class="text-nav-1"><div>' . _('优惠金额') . ':</div>
				<input type="text"   autocomplete="off"   readonly="readonly" value="' . $_POST['youhui_amount'] . '" /></div>
                          
				
			    <div class="text-nav-1"><div>' . _('税别') . ':</div>
				<input type="text"   autocomplete="off"   readonly="readonly" value="' . $_POST['tax_name'] . '" /> </div>
                <div class="text-nav-1"><div>' . _('付款方式') . ':</div>
				<input type="text"   autocomplete="off"   readonly="readonly" value="' . $_POST['payment_type'] . '" /> </div>
				
				 <div class="text-nav-1"><div>' . _('订单类型') . ':</div>
                <input type="text"   autocomplete="off"   readonly="readonly" value="' . $_POST['order_type']. '" /></div> 
				<div class="text-nav-1"><div>' . _('交货日期') . ':</div>
				<input type="text"   autocomplete="off"   readonly="readonly" value="' . $v_delivery_date . '" /> </div>
				<div class="text-nav-2"><div>' . _('备注') . ':</div>
				<input type="text"   autocomplete="off"   readonly="readonly" value="' . $_POST['note'] . '" /> </div>
				<div class="text-nav-2"><div>' . _('审核备注') . ':</div>
                <input type="text"   autocomplete="off"   pattern="^[^?.\+<>!&’:,;?$\^]+$" name="app_remark" value="' . $_POST['app_remark']. '" size="35" maxlength="45"/></div>
               

               
               
			</div>
					
				';
   /*<div class="text-nav-2"><div>' . _('收货公司名称') . ':</div>
                <input type="text"   autocomplete="off"   readonly="readonly" value="' . $_POST['delivery_coyname'] . '" /> </div>
                <div class="text-nav-2"><div>' . _('交货地址') . ':</div>
                <input type="text"   autocomplete="off"   readonly="readonly" value="' . $_POST['delivery_address'] . '" /> </div>
				*/
        echo '</table>';
        echo '<br />';
        $sql2 = "SELECT a.po_num,a.line,a.stockid,c.item_name, c.units,a.quantity,a.price,a.last_price,a.line_amount,a.line_remark,a.creation_date,a.created_by,a.operation_code,a.operation_seq_num,a.wip_entity_name
                  from po_lines_all a,wip_jobs_all b,sf_item_no c where a.wip_entity_name=b.wip_entity_name and  a.stockid=c.item_no and po_num = '" . $Updatepo_num . "'";
        $result2 = DB_query($sql2, $db);
        if (DB_num_rows($result2) == 0) {
            unset($result2);
            prnMsg(_('没有找到外协采购单详细信息，请重新登录查询！'), 'info');
        } else {
            echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '"><input type="hidden" name = "identifier" value ="' . $identifier . '">';
            echo '<div>';
            echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
            echo '<div class="text-nav-table"> <table class="selection" align="center" >';
            $tableheader = '<tr>
	                           
	                                <th 0>' . '行' . '</th>
									<th   >' . _('工单') . '</th>
		<th   >' . _('工序名称') . '</th>
		<th   >' . _('料号') . '</th> 
                                        <th   >' . '料号名称' . '</th> 

					                   <th  width =100>' . '数量' . '</th>
                                        <th width =50 >' . '单位' . '</th>
                                      <th width =50 >' . '上次单价' . '</th>
                                      <th width =50 >' . '单价' . '</th>  
                                        <th  width =80 >' . '金额' . '</th>    
                                           
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
            if ($myrow['price']>$myrow['last_price']) {
                echo ' <tr bgcolor="LavenderBlush"><td>' . $myrow['line'] . '</td>
				 <td>' . $myrow['wip_entity_name'] . '</td>
	 <td>' . $myrow['operation_code'] . '</td>
		      <td>' . $myrow['stockid'] . '</td>
                      <td>' . $myrow['item_name'] . '</td> 

                      <td>' . $myrow['quantity'] . '</td>
                      <td>' . $myrow['units'] . '</td>
                      <td>' . $myrow['last_price'] . ' </td>
                      <td> ' . $myrow['price'] . ' </td>
                          <td>' . $myrow['line_amount'] . '</td>                     
                    
                      </tr> <input type="hidden" name="po_num" value="' . $myrow['po_num'] . '" />
                ';
			} else {
                echo '<tr ><td>' . $myrow['line'] . '</td>
				 <td>' . $myrow['wip_entity_name'] . '</td>
	 <td>' . $myrow['operation_seq_num'] . '</td>
	 <td>' . $myrow['operation_code'] . '</td>
		      <td>' . $myrow['stockid'] . '</td>
                      <td>' . $myrow['item_name'] . '</td> 
                      <td>' . $myrow['quantity'] . '</td>
                      <td>' . $myrow['units'] . '</td>
                      <td> ' . $myrow['last_price'] . ' </td>
                      <td> ' . $myrow['price'] . ' </td>
                          <td>' . $myrow['line_amount'] . '</td>                  
                    
                      </tr> <input type="hidden" name="po_num" value="' . $myrow['po_num'] . '" />
                ';
			}

                $RowCounter++;
                If ($RowCounter == 500) {
                    $RowCounter = 1;
                    echo $tableheader;
                }
            }
            echo '</table></div> ';


            echo '</div>
          </form>';
        }
   if ($_POST['status'] == 'INPROCESS')
        echo '<br />
          <input type="submit" name="Submit" value="' . "核准" . '" /> &nbsp;&nbsp;&nbsp;&nbsp;   <input type="submit" name="Reject" value="' . "拒签" . '" />&nbsp;&nbsp;&nbsp;&nbsp;   <input type="submit" name="return" value="' . "返回上一层" . '" />&nbsp;
                                 
</div>';
	} else {
	 echo '<br />
	        
           <input type="submit" name="return" value="' . "订单已是签核状态" . '" />&nbsp;
                                 
</div>';
	
	}
    
    echo '</div>
          </form>';
}
//拒签
if (isset($_POST['Reject'])) {
    DB_Txn_Begin($db);
    $v_date = strtotime(Date('Y-m-d H:i:s'));
    $sql1 = " update po_headers_all 
                set status = 'REJECTED',
		     Approve_date  = '" . $v_date . "',
              Approved_by  = '" . $_SESSION['UserID'] . "'
               where po_num ='" . $_POST['po_num'] . "'";
    $result1 = DB_query($sql1, $db);

    $sql2= "update po_headers_all set app_remark = '".$_POST['app_remark']."'
	 where po_num ='" . $_POST['po_num'] . "'";
	$result2 = DB_query($sql2,$db);


    $sql3 = " update po_lines_all 
                set status = 'REJECTED',
		     last_update_date  = '" . $v_date . "',
              last_updated_by  = '" . $_SESSION['UserID'] . "'
               where po_num ='" . $_POST['po_num'] . "'";
    $result3 = DB_query($sql3, $db);

    DB_Txn_Commit($db);
    $msg = '拒签成功！1秒后将跳转上一页！';
    prnMsg($msg, 'success');
    echo '<meta http-equiv="refresh" content="1; url=' . $RootPath . '/OspOrderApprove.php" />';
    echo '<br />';
    echo '<br /><div class="centre"><a href="' . $RootPath . '/OspOrderApprove.php">' . _('外协采购单签核') . '</a></div>';
}
if (isset($_POST['Submit'])) {
    DB_Txn_Begin($db);
    $v_date = strtotime(Date('Y-m-d H:i:s'));
	 
    $sql1 = " update po_headers_all 
                set status = 'APPROVED',
				   app_remark = '".$_POST['app_remark']."',
                    Approve_date  = '" . $v_date . "',
                    Approved_by  = '" . $_SESSION['UserID'] . "'
              where po_num ='" . $_POST['po_num'] . "'";
    $result1 = DB_query($sql1, $db);
	DB_Txn_Commit($db); 
    $msg = '签核成功！';
    prnMsg($msg, 'success');
 
    echo '<br /><div class="centre"><a href="' . $RootPath . '/OspOrderApprove.php">' . _('外协采购单签核') . '</a></div>';
	echo '<br /><div class="centre"><a href="' . $RootPath . '/PrintOSPPOQuote.php?Updatedelivery_num='.$_POST['po_num'].'" target="_blank"  >' . _('打印采购单') . '</a></div>';
 

	$sql3 = " update po_lines_all 
                set status = '高阶审核',
		     last_update_date  = '" . $v_date . "',
              last_updated_by  = '" . $_SESSION['UserID'] . "'
               where po_num ='" . $_POST['po_num'] . "'";
    $result3 = DB_query($sql3, $db);


    
    
}
if (isset($_POST['return'])) {
    header('Location: OspOrderApprove.php');
}
include('includes/footer.inc');
?>
