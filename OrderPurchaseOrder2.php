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
} elseif($_POST['po_num']) {
    $Updatepo_num = $_POST['po_num'];
}else{
    $Updatepo_num = '';
}
$Title = _('采购单信息');
$ViewTopic = '采购单信息';
$BookMark = '采购单信息';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
// include('includes/CountriesArray.php');

echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/customer.png" title="' . _('采购单') .
 '" alt="" />' . ' ' . _('采购单信息') . '
	</p>';
if (isset($Updatepo_num) and $Updatepo_num != '') {
    //CreditLimit,
    $sql = "SELECT
                    a.*,b.vendor_name
                FROM
                    po_headers_all a,
                    vendors b 
                WHERE
                    1 = 1
                AND a.vendor_code = b.vendor_code
                
                and a.po_num = '" .$Updatepo_num."'";
          
    $result = DB_query($sql, $db);
    $myrow = DB_fetch_array($result);
    $_POST['po_num'] = $myrow['po_num'];
    $_POST['status'] = $myrow['status'];
    $_POST['remark'] = $myrow['remark'];
    $_POST['approve_by'] = $myrow['approve_by'];
    $_POST['approve_date'] = $myrow['approve_date'];
    $_POST['approve_remark'] = $myrow['approve_remark'];
    $_POST['order_amount']=$myrow['amount'];
    $_POST['vendor_name']=$myrow['vendor_name'];
    $_POST['need_date'] = $myrow['need_date'];
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
				<td>' . _('采购单编号') . ':</td>
				<td width = 120>' . $_POST['po_num'] . '</td>
                 <input  type="hidden" name="po_num"  value="' . $_POST['po_num'] . '" />
			
				<td>' . _('状态') . ':</td>
				<td width = 120> ' . $v_status . ' </td>
			 
				<td>' . _('客户名称') . ':</td>
				<td  > ' . $_POST['vendor_name'] . ' </td>
			</tr>
			';
        
        $v_need_date = date('Y-m-d',$myrow['need_date']);
        $v_creation_date = date('Y-m-d H:i:s',$_POST['creation_date']);
        $v_approve_date = date('Y-m-d H:i:s',$_POST['approve_date']);
        echo'
			<tr >
				 
				<td>' . _('建立日') . ':</td>
				<td  width = 150> ' . $v_creation_date . ' </td>
			 
				<td>' . _('总额') . ':</td>
				<td  > <span font="red">' . $_POST['order_amount']. '<span/> </td>
                            
			</tr>';
			 
			if(!empty($_POST['approve_by'])){
				echo '<tr>
			<td>' . _('签核人') . ':</td>
				<td> ' .$_POST['approve_by'] . ' </td>
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
        $sql2 = "SELECT line,c.stockid,a.item_desc, c.price,uom,quantity,quantity_cancelled,note,subinventory_code,amount, status,need_date,quantity_cancelled
	FROM  po_lines_all c,
	   sf_item_no a
        where  a.item_no=c.stockid
		and c.po_num = '" .$Updatepo_num."'
		order by line ";
        
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
                                        <th  width =100>' . '规格型号' . '</th>
                                        <th width =150 >' . '产品名称' . '</th>
                                      
				                        <th  width =50>' . '单价' . '</th>
                                        <th  width =50>' . '单位' . '</th>
                                        <th width =120 >' . '数量' . '</th>  
                                        <th width =120 >' . '取消数量' . '</th>  
                                                                   
                                       <th width =80 >' . '金额' . '</th>
									   <th width =80 >' . '需求时间' . '</th>
                                        
                                       
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

             $v_need_date = date('Y-m-d',$myrow['need_date']);
                echo '
				
		      <td>' . $myrow['line'] . '</td>
                      <td>' . $myrow['stockid'] . '</td>
                      <td>' . $myrow['item_desc'] . '</td>
                   
                      <td class="number">' . $myrow['price'] . '</td>
                      <td>' . $myrow['uom'] . '</td> 
                      <td class="number">' . $myrow['quantity'] . '</td>  
                      <td class="number">' . $myrow['quantity_cancelled'] . '</td>  
					
					  <td class="number">' . $myrow['amount'] . '</td>   
					   <td>' .$v_need_date . '</td>     
                         

					 

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
    header('Location: OrderPurchaseOrder.php');
}
include('includes/footer.inc');
?>
