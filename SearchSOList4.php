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
if (isset($_GET['Updateorder_number'])) {
    $Updateorder_number = $_GET['Updateorder_number'];
} else {
    $Updateorder_number = '';
}
$Title = _('订单详情');
$ViewTopic = '订单详情';
$BookMark = '订单详情';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/CountriesArray.php');

echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/customer.png" title="' . _('订单') .
 '" alt="" />' . ' ' . _('订单详情') . '
	</p>';
if (isset($Updateorder_number) and $Updateorder_number != '') {
    //CreditLimit,
    $sql2 = "SELECT
	a.order_number,
	a.status,
	b.customer_name,
	a.order_amount,
    a.header_remark,
    a.need_date,
	a.creation_date 
FROM so_headers_all a,
	customers b
WHERE  a.customer_code = b.customer_code
and a.so_quote='SO'
and a.order_number = '" .$Updateorder_number."'";
    $result2 = DB_query($sql2, $db);
    $myrow = DB_fetch_array($result2);
    $_POST['order_number'] = $myrow['order_number'];
    $_POST['status'] = $myrow['status'];
    $_POST['remark'] = $myrow['remark'];
    $_POST['order_amount']=$myrow['order_amount'];
    $_POST['customer_name']=$myrow['customer_name'];
    $_POST['need_date'] = $myrow['need_date'];
    $_POST['creation_date'] = $myrow['creation_date'];
    $_POST['transasfer_to_so_date'] = $myrow['transasfer_to_so_date'];
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
        }  else {
            $v_status = '已取消';
        }
        echo '<table class="selection" id="SignFrame">
                       <tr class="EvenTableRows">
				<td>' . _('订单号') . ':</td>
				<td width = 120>' . $_POST['order_number'] . '</td>
                <input  type="hidden" name="order_number"  value="' . $_POST['order_number'] . '" />
			
				<td>' . _('状态') . ':</td>
				<td width = 120> ' . $v_status . ' </td>
			</tr>';
        
        $v_need_date = date('Y-m-d',$_POST['need_date']);
        $v_creation_date = date('Y-m-d H:i:s',$_POST['creation_date']);
		if ( $_POST['transasfer_to_so_date']!='')  {
		   $v_transasfer_to_so_date = date('Y-m-d H:i:s',$_POST['transasfer_to_so_date']); 
		   }
// <tr > 
//                 <td>' . _('报价单转成订单人员') . ':</td>
//                 <td  > ' . $_POST['transasfer_to_so_person'] . ' </td>
//                 <td>' . _('报价单转成订单日期') . ':</td>
//                 <td   > ' . $v_transasfer_to_so_date . ' </td>
//             </tr>
        echo'
			<tr >
				<td>' . _('需求日期') . ':</td>
				<td> ' . $v_need_date . ' </td>
				<td>' . _('建立日期') . ':</td>
				<td  width = 150> ' . $v_creation_date . ' </td>
			</tr>
  

           
			

			 <tr >
				<td>' . _('客户名称') . ':</td>
				<td colspan="3"> ' . $_POST['customer_name'] . ' </td>
			</tr>
                        <tr >
				<td>' . _('总额') . ':</td>
				<td width = 150> ' . $_POST['order_amount']. ' </td>
                            
			</tr>';
        echo '</table>';
        echo '<br />';
        $sql2 = "SELECT
	line,
	stockid,item_desc,
	price,
	uom, 
	quantity_shiped,
	quantity_cancelled, 	 
	quantity_billed,
	amount,
	quantity,
	remark, 
  status
FROM so_lines_all c,sf_item_no d
        where c.stockid=d.item_no
		and order_number = '" .$Updateorder_number."'";
        $result2 = DB_query($sql2, $db);
        if (DB_num_rows($result2) == 0) {
            unset($result2);
            prnMsg(_('没有找到订单详细信息，请重新登录查询！'), 'info');
        } else {
            echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '"><input type="hidden" name = "identifier" value ="' . $identifier . '">';
            echo '<div>';
            echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
            echo '<table class="selection" align="center" >';
            $tableheader = '<tr>
	                           
                                        <th width =50 >' . '行' . '</th>
                                        <th  width =140>' . '规格型号' . '</th>
										<th  width =200>' . '产品名称' . '</th>
										<th width =120 >' . '数量' . '</th> 
                                        <th width =50 >' . '单位' . '</th>
					                    <th  width =50>' . '单价' . '</th>
                                        <th  width =100>' . '已出货数量' . '</th>
                                
                                       <th width =80 >' . '订单金额' . '</th>
									                                            
									   <th width =120 >' . '行备注' . '</th>
                                       
                                       
				</tr>';
                        // <th width =80 >' . '取消数量' . '</th> 
                        //                <th width =80 >' . '立账数量' . '</th>
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

             if  ($myrow['order_amount']=='' or $myrow['order_amount']=='NULL') {
			$order_amount='';			
			} else {
				$order_amount=$myrow['order_amount'];
			}


			if  ($myrow['quantity_cancelled']=='' or $myrow['quantity_cancelled']=='NULL') {
			$quantity_cancelled='';
			} else {
				$quantity_cancelled=$myrow['quantity_cancelled'];
			}

			if  ($myrow['remark']=='' or $myrow['remark']=='NULL') {
			$remark='';
			} else {
				$remark=$myrow['remark'];
			}

 
                      // <td class="number">' . $myrow['quantity_cancelled'] . '</td>
                      // <td class="number">' . $myrow['quantity_billed'] . '</td>
                echo '
		              <td>' . $myrow['line'] . '</td>
                      <td>' . $myrow['stockid'] . '</td>
					  <td>' . $myrow['item_desc'] . '</td>
                      <td class="number">' . $myrow['quantity'] . '</td> 
                      <td>' . $myrow['uom'] . '</td>
                      <td class="number">' . $myrow['price'] . '</td>
                      <td class="number">' . $myrow['quantity_shiped'] . '</td>
                      
					  <td class="number">' .$myrow['amount'] . '</td>  
					  <td>' . $myrow['remark']  . '</td>
                        

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
    header('Location: DeliveryReportList.php');
}
include('includes/footer.inc');
?>
