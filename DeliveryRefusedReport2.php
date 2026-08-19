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
if (isset($_GET['Updatedelivery_num'])) {
    $Updatedelivery_num = $_GET['Updatedelivery_num'];
} elseif($_POST['delivery_num']) {
    $Updatedelivery_num = $_POST['delivery_num'];
}else{
    $Updatedelivery_num = '';
}
$Title = _('送货单信息');
$ViewTopic = '送货单信息';
$BookMark = '送货单信息';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
// include('includes/CountriesArray.php');

echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/customer.png" title="' . _('送货单') .
 '" alt="" />' . ' ' . _('送货单信息') . '
	</p>';
if (isset($Updatedelivery_num) and $Updatedelivery_num != '') {
    //CreditLimit,
    // $sql = " SELECT delivery_num,status,delivery_date,b.customer_code,delivery_amount,a.creation_date,narrative,a.created_by,b.customer_name  FROM so_delivery_headers_all a, customers b  WHERE 1 = 1 AND a.customer_code = b.customer_code  
   
    //             and a.delivery_num = '" .$Updatedelivery_num."'";
     $sql = " SELECT delivery_num,status,delivery_date,b.customer_code,delivery_amount,a.creation_date,narrative,a.approve_date ,a.approved_by,a.approve_remark,a.created_by,b.customer_name  FROM so_delivery_headers_all a, customers b  WHERE 1 = 1 AND a.customer_code = b.customer_code  
   
       and a.delivery_num = '" .$Updatedelivery_num."'";
          
    $result = DB_query($sql, $db);
    $myrow = DB_fetch_array($result);
    $_POST['delivery_num'] = $myrow['delivery_num'];
    $_POST['status'] = $myrow['status'];
    $_POST['remark'] = $myrow['narrative'];
    $_POST['delivery_date'] = $myrow['delivery_date'];
    $_POST['Approved_by'] = $myrow['Approved_by'];
    $_POST['Approve_date'] = $myrow['Approve_date'];
    $_POST['approve_remark'] = $myrow['approve_remark'];
    $_POST['delivery_amount']=$myrow['delivery_amount'];
    $_POST['customer_name']=$myrow['customer_name'];
    // $_POST['need_date'] = $myrow['need_date'];
   $_POST['creation_date'] = $myrow['creation_date'];
    // s$creation_date = strtotime(Date('Y-m-d H:i:s'));
    // var_dump($myrow);
 
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
				<td>' . _('送货单编号') . ':</td>
				<td width = 120>' . $_POST['delivery_num'] . '</td>
                 <input  type="hidden" name="delivery_num"  value="' . $_POST['delivery_num'] . '" />
			
				<td>' . _('状态') . ':</td>
				<td width = 120> ' . $v_status . ' </td>
			 
				<td>' . _('客户名称') . ':</td>
				<td  > ' . $_POST['customer_name'] . ' </td>
			</tr>
			';
        
        $v_delivery_date= date('Y-m-d',$_POST['delivery_date']);
        $v_creation_date = date('Y-m-d H:i:s',$_POST['creation_date']);
       
        echo'
			<tr >
				<td>' . _('送货日') . ':</td>
				<td> ' . $v_delivery_date . ' </td>
				<td>' . _('建立日') . ':</td>
				<td  width = 150> ' . $v_creation_date . ' </td>
			 
				<td>' . _('总额') . ':</td>
				<td  > <span font="red">' . $_POST['delivery_amount']. '<span/> </td>
                            
			</tr>
			<tr >
				<td>' . _('送货单头备注') . ':</td>
				<td colspan="2"> ' . $_POST['remark'] . ' </td>
			</tr>';
			if(!empty($_POST['Approved_by'])){
				 $v_approve_date = date('Y-m-d H:i:s',$_POST['Approve_date']);
				echo '<tr>
			<td>' . _('签核人') . ':</td>
				<td> ' .$_POST['Approved_by'] . ' </td>
				<td>' . _('签核日期') . ':</td>

				<td  width = 150> ' . $v_approve_date . ' </td>
			<tr/> 
			<tr>
				<td>' . _('签核意见备注') . ':</td>
				<td   rowspan="2">  ' . $_POST['approve_remark'] . ' </td>
			</tr> ';
			} else  {
				echo '</tr> ';
			}
			
        echo '</table>';

        echo '<br />';
      //   delivery_num,status,deliveryline,so_order_number,so_line_no,uom,price,
						// delivery_quantity,stockid,line_amount,customer_code,subinventory_code,
						// creation_date,created_by,last_update_date,last_updated_by
        $sql2 = "SELECT deliveryline,c.stockid,a.item_desc, c.price,uom,delivery_quantity,  line_amount, status  
	FROM  so_delivery_all c,
	   sf_item_no a
        where  a.item_no=c.stockid
		and c.delivery_num = '" .$Updatedelivery_num."'
		order by deliveryline ";
        
        $result2 = DB_query($sql2, $db);
        if (DB_num_rows($result2) == 0) {
            unset($result2);
            prnMsg(_('没有找到送货单详细信息，请重新登录查询！'), 'info');
        } else {
            echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '"><input type="hidden" name = "identifier" value ="' . $identifier . '">';
            echo '<div>';
            echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
            echo '<table class="selection" align="center" >';
            $tableheader = '<tr>
	                           
                                        <th width =50 >' . '行' . '</th>
                                        <th  width =100>' . '规格型号' . '</th>
                                        <th width =150 >' . '产品名称' . '</th>
                                      
				                      
                                        <th  width =50>' . '单位' . '</th>
                                        <th width =80 >' . '数量' . '</th>  
                                        <th  width =50>' . '单价' . '</th>                        
                                       <th width =80 >' . '金额' . '</th>
									   <th width =80 >' . '送货时间' . '</th>
                                      
                                       
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
                

             // $v_return_date = date('Y-m-d',$_POST['return_date']);
                if(empty($myrow['return_date'])){
                	$v_return_date = date('Y-m-d',$_POST['delivery_date']);
                }else{

                	$v_return_date = date('Y-m-d',$myrow['return_date']);

                }
                echo '
				
		      <td>' . $myrow['deliveryline'] . '</td>
                      <td>' . $myrow['stockid'] . '</td>
                      <td>' . $myrow['item_desc'] . '</td>
                   
                      
                      <td>' . $myrow['uom'] . '</td> 
                      <td class="number">' . $myrow['delivery_quantity'] . '</td>  
					<td class="number">' . $myrow['price'] . '</td>
					  <td class="number">' . $myrow['line_amount'] . '</td> 

					   <td>' .$v_return_date . '</td>       

					 

        </tr>';
        //<td>' . $myrow['chuanghu_fucai_quantity'] . '</td>
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
        <input type="submit" name="return" value="返回上一层" />
                                 
</div>';
    }
    echo '</div>
          </form>';
}
if (isset($_POST['return'])) {
    header('Location: DeliveryRefusedReport.php');
}
include('includes/footer.inc');
?>
