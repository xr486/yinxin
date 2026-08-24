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
    $sql = "SELECT pha.po_num, pha.status,order_type, pha.note,pha.payment_term ,pha.order_date,pha.app_remark, pha.creation_date, pha.tax_amount,pha.tax_flag,pha.all_line_amount, pha.created_by, pha.youhui_amount, vendor_name,pha.po_all_amount,pha.vendor_code,pha.payment_type,pha.tax_name 
FROM po_headers_all pha, vendors v
WHERE v.vendor_code = pha.vendor_code
AND po_num= '" .$Updatepo_num."'";
    $result = DB_query($sql, $db);
    $myrow = DB_fetch_array($result);
    $_POST['po_num'] = $myrow['po_num'];
    $_POST['status'] = $myrow['status'];
    $_POST['note'] = $myrow['note'];
    $_POST['app_remark'] = $myrow['app_remark'];
    $_POST['amount']=$myrow['amount'];
    $_POST['order_type']=$myrow['order_type'];
    $_POST['vendor_code']=$myrow['vendor_code'];
    $_POST['vendor_name']=$myrow['vendor_name'];
    $_POST['order_date'] = $myrow['order_date'];
    $_POST['create_date'] = $myrow['creation_date'];
    $_POST['created_by'] = $myrow['created_by'];
    $_POST['payment_term'] = $myrow['payment_term'];
    $_POST['payment_type'] = $myrow['payment_type'];
    $_POST['tax_name'] = $myrow['tax_name'];
    $_POST['youhui_amount'] = $myrow['youhui_amount'];
    $_POST['po_all_amount'] = $myrow['po_all_amount'];
    $_POST['tax_amount'] = $myrow['tax_amount'];
    $_POST['tax_flag'] = $myrow['tax_flag'];
    $_POST['all_line_amount'] = $myrow['all_line_amount']; 
    if (!isset($_GET['delete'])) {
        echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">'
        . '<input type="hidden" name = "identifier" value ="' . $identifier . '">';
        echo '<div>';
        echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
     
		      $v_order_date = date('Y-m-d',$_POST['order_date']);
			 
			  $app_remark = $_POST['app_remark'];
        $v_create_date = date('Y-m-d H:i:s',$_POST['create_date']);
        echo '<table class="selection" id="SignFrame">
        <div class="text-nav">
        <div class="text-nav-1"><div>' . _('采购单号') . ':</div>
        <input type="text" readonly="readonly" value="' . $_POST['po_num'] . '" /></div>
                  <input  type="hidden" name="po_num"  value="' . $_POST['po_num'] . '" />
			
                <div class="text-nav-1"><div>' . _('状态') . ':</div>
				<input type="text" readonly="readonly" value="' . $_POST['status']. '" /> </div>
				<div class="text-nav-1"><div>' . _('建立日') . ':</div>
				<input type="text" readonly="readonly" value="' . $v_create_date . '" /> </div>
              
                <div class="text-nav-1"><div>' . _('签核意见') . ':</div>
				<input type="text" readonly="readonly" value="' .  $app_remark . '" /> </div>
				
		';
        
  
        echo'
					
				
                <div class="text-nav-1"><div>' . _('采购日期') . ':</div>
				<input type="text" readonly="readonly" value="' . $v_order_date . '" /> </div>
				
                <div class="text-nav-2"><div>' . _('供应商') . ':</div>
				<input type="text" readonly="readonly" value="' . $_POST['vendor_name'] . '" /> </div>
			';
           if ($_SESSION['price_flag']=='N') {
			echo ' <div class="text-nav-1"><div>' . _('含税金额') . ':</div>
				<input type="text" readonly="readonly" value="' . $_POST['po_all_amount']. '" /> </div>
                
                <div class="text-nav-1"><div>' . _('优惠金额') . ':</div>
				<input type="text" readonly="readonly" value="' . $_POST['youhui_amount']. '" /> </div>
				<div class="text-nav-1"><div>' . _('税金') . ':</div>
				<input type="text" readonly="readonly" value="' . $_POST['tax_amount']. '" /> </div>
                <div class="text-nav-1"><div>' . _('未税金额') . ':</div>
				<input type="text" readonly="readonly" value="' . $_POST['all_line_amount']. '" /> </div>
				';
		   }
               echo ' 
                
                <div class="text-nav-1"><div>' . _('下单人员') . ':</div>
				<input type="text" readonly="readonly" value="' . $_POST['created_by']. '" /> </div>
                <div class="text-nav-1"><div>' . _('付款条件') . ':</div>
				<input type="text" readonly="readonly" value="' . $_POST['payment_term']. '" /> </div>
			    <div class="text-nav-1"><div>' . _('付款方式') . ':</div>
				<input type="text" readonly="readonly" value="' . $_POST['payment_type']. '" /> </div>
				<div class="text-nav-1"><div>' . _('税别') . ':</div>
				<input type="text" readonly="readonly" value="' . $_POST['tax_name']. '" /> </div>
				<div class="text-nav-1"><div>' . _('是否含税') . ':</div>
				<input type="text" readonly="readonly" value="' . $_POST['tax_flag']. '" /> </div>
				<div class="text-nav-2"><div>' . _('备注') . ':</div>
                <input type="text" readonly="readonly" value="' . $_POST['note'] . '" /> </div>
                <div class="text-nav-1"><div>' . _('订单类型') . ':</div>
    <input type="text" readonly="readonly" value="' . $_POST['order_type']. '" /></div> 


			</div>';

			
		   

        echo '</table>';
        echo '<br />';
        $sql2 = "SELECT a.line,a.stockid,b.item_desc,b.item_name,a.uom,a.price,a.quantity,a.po_num,a.line_amount,a.last_update_date,a.creation_date,ifnull(quantity_received,0) quantity_received,ifnull(quantity_accepted,0) quantity_accepted ,
            ifnull(quantity_deliveried,0) quantity_deliveried,ifnull(quantity_cancelled,0) quantity_cancelled,ifnull(quantity_rejected,0) quantity_rejected,ifnull(quantity_billed,0) quantity_billed,a.created_by,(select locationname from locations d where d.loccode=a.subinventory_code) locationname,a.need_date
                  FROM po_lines_all a,sf_item_no b
                 where a.stockid=b.item_no   and po_num = '" .$Updatepo_num."' order by a.line";
        $result2 = DB_query($sql2, $db);
        if (DB_num_rows($result2) == 0) {
            unset($result2);
            prnMsg(_('没有找到采购单详细信息，请重新登录查询！'), 'info');
        } else {
            echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '"><input type="hidden" name = "identifier" value ="' . $identifier . '">';
            echo '<div>';
            echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
            echo '<div class="text-nav-table"> <table class="selection" align="center" >';
            $tableheader = '<tr>
	                           
	                               
                                        <th width =50 >' . '行' . '</th>
                                        <th>' . '料号' . '</th>
                                        <th   >' . '料号名称' . '</th>
                                         <th   >' . '规格型号' . '</th>
					                     <th  >' . '订单数量' . '</th> 
                                        <th width =40 >' . '单位' . '</th> 
										<th width =50 >' . '单价' . '</th> 
                                       <th width =50 >' . '金额' . '</th>  
                                        <th>' . '仓库' . '</th> 
                                       <th  width =80 >' . '入库数量' . '</th>
                                       <th  width =80 >' . '建立日期' . '</th>
                                       <th  width =80 >' . '最近修改日期' . '</th>

                                       
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
                      <td>' . $myrow['item_name'] . '</td>
				      <td>' . $myrow['item_desc'] . '</td>
                      <td>' . $myrow['quantity'] . '</td> 
                      <td>' . $myrow['uom'] . '</td>';
                      if ($_SESSION['price_flag']=='N') {
						 echo '  <td>' . $myrow['price'] . '</td>
                       <td>' . sprintf("%.2f",$myrow['line_amount']) . '</td>';
					  } else {
					  echo '  <td> </td>
                       <td> </td>';
					  }
                      echo ' <td>' . $myrow['locationname'] . '</td>                   
                     <td>' . $myrow['quantity_received'] . '</td>                
                     <td>' . date('Y-m-d H:i:s',$myrow['creation_date'] ). '</td>                
                     <td>' . date('Y-m-d H:i:s',$myrow['last_update_date'] ). '</td>


        </tr>';
                $RowCounter++;
                If ($RowCounter == 500) {
                    $RowCounter = 1;
                    echo $tableheader;
                }
            }
            echo '</table></div>';


            echo '</div>
          </form>';
        }

		 
$sql2 = "SELECT
	 	file_patch,creation_date,created_by,file_name
FROM po_headers_all_file  
        where  order_number = '" .$Updatepo_num."'";
        $result2 = DB_query($sql2, $db);
        if (DB_num_rows($result2) == 0) {
            unset($result2);
          //  prnMsg(_('无附件'), 'info');
        } else {
			echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/customer.png" title="' . _('采购单头信息') .
 '" alt="" />' . ' ' . _('采购单附件信息') . '
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

if (isset($_POST['uploadfile'])) {
header("Location: POUploadNewfile.php?OrderNum=$OrderNum");
}

include('includes/footer.inc');
?>
