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
		<img src="' . $RootPath . '/css/' . $Theme . '/images/customer.png" title="' . _('订单头信息') .
 '" alt="" />' . ' ' . _('订单头信息') . '
	</p>';
if (isset($Updateorder_number) and $Updateorder_number != '') {
    //CreditLimit,
    $sql2 = "SELECT
	a.order_number,
	a.status,b.customer_code,
	b.customer_name,
	a.order_payment_amount,
    a.youhui_amount,
    a.order_all_amount,
    a.header_remark,
    a.need_date,
	a.creation_date 
FROM so_headers_all a,
	customers b
WHERE  a.customer_code = b.customer_code
and a.order_number = '" .$Updateorder_number."'";
    $result2 = DB_query($sql2, $db);
    $myrow = DB_fetch_array($result2);
    $_POST['order_number'] = $myrow['order_number'];
    $_POST['status'] = $myrow['status'];
    $_POST['remark'] = $myrow['remark'];
    $_POST['order_payment_amount']=$myrow['order_payment_amount'];
     $_POST['youhui_amount']=$myrow['youhui_amount'];
      $_POST['order_all_amount']=$myrow['order_all_amount'];
    $_POST['customer_name']=$myrow['customer_name'];
    $_POST['customer_code']=$myrow['customer_code'];
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
			 ';
        
        $v_need_date = date('Y-m-d',$_POST['need_date']);
        $v_creation_date = date('Y-m-d H:i:s',$_POST['creation_date']);
		if ( $_POST['transasfer_to_so_date']!='')  {
		   $v_transasfer_to_so_date = date('Y-m-d H:i:s',$_POST['transasfer_to_so_date']); 
		   }
 
        echo'
			
				<td>' . _('需求日期') . ':</td>
				<td> ' . $v_need_date . ' </td>
				</tr > 
			 <tr >
               <td>' . _('客户代号') . ':</td>
				<td > ' . $_POST['customer_code'] . ' </td>
				<td>' . _('客户名称') . ':</td>
				<td colspan="3"> ' . $_POST['customer_name'] . ' </td>
                
                
				
			</tr>
             
                      <tr > 
                
                
                
                <td>' . _('建立日期') . ':</td>
				<td  width = 150> ' . $v_creation_date . ' </td>     
			</tr>';
        echo '</table>';

        echo '<br />';
        $sql2 = "SELECT
	c.line,
	stockid,d.item_desc,d.item_name,
	c.price,c.other_price ,c.zhidao_price,
	uom, 
	quantity_shiped,
	quantity_cancelled, 	 
	quantity_billed,
	line_amount,
	quantity,
	line_remark, 
  subinventory_code
FROM so_lines_all c,sf_item_no d
        where c.stockid=d.item_no 
		and order_number = '" .$Updateorder_number."'";
        $result2 = DB_query($sql2, $db);
        if (DB_num_rows($result2) == 0) {
            unset($result2);
            prnMsg(_('没有找到订单详细信息，请重新登录查询！'), 'info');
        } else {
			echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/customer.png" title="' . _('订单头信息') .
 '" alt="" />' . ' ' . _('订单行信息') . '
	</p>';
            echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '"><input type="hidden" name = "identifier" value ="' . $identifier . '">';
            echo '<div>';
            echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
            echo '<table class="selection" align="center" >';
            $tableheader = '<tr>
	                           
                                        <th width =50 >' . '行' . '</th>
                                        <th  width =140>' . '料号' . '</th>
										<th  width =200>' . '料号名称' . '</th>
										<th  width =200>' . '规格型号' . '</th>
                                         <th width =50 >' . '单位' . '</th>
										<th width =120 >' . '数量' . '</th>  								
                                        <th  width =100>' . '已出货数量' . '</th>									                                            
									   <th  >' . '仓库' . '</th>
									   <th width =120 >' . '行备注' . '</th>
                                       
                                       
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
		              <td>' . $myrow['line'] . '</td>
                      <td>' . $myrow['stockid'] . '</td>
					  <td>' . $myrow['item_name'] . '</td>
					  <td>' . $myrow['item_desc'] . '</td>
                      <td>' . $myrow['uom'] . '</td>
                      <td class="number">' . $myrow['quantity'] . '</td>  
                      
                      <td class="number">' . $myrow['quantity_shiped'] . '</td>
                      <td class="number">' . $myrow['subinventory_code'] . '</td>
					  <td>' . $myrow['line_remark']  . '</td>
                        

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

 $sql2 = "SELECT
	 	file_patch,creation_date,created_by,file_name
FROM so_headers_all_file  
        where  order_number = '" .$Updateorder_number."'";
        $result2 = DB_query($sql2, $db);
        if (DB_num_rows($result2) == 0) {
            unset($result2);
          //  prnMsg(_('无附件'), 'info');
        } else {
			echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/customer.png" title="' . _('订单头信息') .
 '" alt="" />' . ' ' . _('订单附件信息') . '
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
//    echo 'AAAAAAAAAA';
  echo '<script>window.close();</script>'; 
}
include('includes/footer.inc');
?>
