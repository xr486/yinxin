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
} elseif($_POST['order_number']) {
    $Updateorder_number = $_POST['order_number'];
}else{
    $Updateorder_number = '';
}
$Title = _('预测计划单信息签核');
$ViewTopic = '预测计划单信息签核';
$BookMark = '预测计划单信息签核';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
// include('includes/CountriesArray.php');

echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/customer.png" title="' . _('预测计划单') .
 '" alt="" />' . ' ' . _('预测计划单信息') . '
	</p>';
if (isset($Updateorder_number) and $Updateorder_number != '') {
    //CreditLimit,
    $sql = "SELECT
                    a.order_number,
                    a.status,
                    b.customer_name,
                    a.remark ,
                    a.last_update_date,
                    a.creation_date
                FROM
                    so_forecast_header a,
                    customers b 
                WHERE  a.customer_code = b.customer_code
                and a.order_number = '" .$Updateorder_number."'";
    $result = DB_query($sql, $db);
    $myrow = DB_fetch_array($result);
    $_POST['order_number'] = $myrow['order_number'];
    $_POST['status'] = $myrow['status'];
    $_POST['remark'] = $myrow['remark'];
    $_POST['customer_name']=$myrow['customer_name'];
    $_POST['last_update_date'] = $myrow['last_update_date'];
    $_POST['creation_date'] = $myrow['creation_date'];
    $creation_date = strtotime(Date('Y-m-d H:i:s'));
    if (isset($_POST['approve'])){
        $sql = "update so_forecast_header set status = '核准',
		approve_date=". $creation_date .",approve_remark='". $_POST['approve_remark']  ."',
		approve_by='" . $_SESSION['UserID'] . "'
		where order_number = '".$_POST['order_number']."' ";
        $result = DB_query($sql,$db);

		 $sql = "update so_forecast_line set status = '核准',
		last_update_date=". $creation_date .",
		last_updated_by='" . $_SESSION['UserID'] . "'
		where order_number = '".$_POST['order_number']."' ";
        $result = DB_query($sql,$db);
        prnMsg('预测计划单签核成功！',success);
        echo "<script>location.href='ForecastApprove.php';</script>";
    }
 
    if (isset($_POST['reject'])){
        $sql = "update so_forecast_header 
		set status = '拒签',
		approve_date=". $creation_date .",
		approve_remark='". $_POST['approve_remark']  ."',
		approve_by='" . $_SESSION['UserID'] . "'  where order_number = '".$_POST['order_number']."' ";
        $result = DB_query($sql,$db);
        prnMsg('预测计划单拒签成功！',success);
        echo "<script>location.href='ForecastApprove.php';</script>";
    }
    if (isset($_POST['cancel'])){
        $sql = "update so_forecast_header set status = '取消',approve_date=". $creation_date .",approve_remark='". $_POST['approve_remark']  ."',approve_by='" . $_SESSION['UserID'] . "'  where order_number = '".$_POST['order_number']."' ";
        $result = DB_query($sql,$db);
        prnMsg('预测计划单取消成功！',success);
        echo "<script>location.href='ForecastApprove.php';</script>";
    }


    if (!isset($_GET['delete'])) {
        echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">'
        . '<input type="hidden" name = "identifier" value ="' . $identifier . '">';
        echo '<div>';
        echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
     
        $v_last_update_date = date('Y-m-d',$_POST['last_update_date']);
        $v_creation_date = date('Y-m-d H:i:s',$_POST['creation_date']);
        echo '<table class="selection" id="SignFrame">
                       <tr class="EvenTableRows">
				<td>' . _('预测计划单编号') . ':</td>
				<td width = 120>' . $_POST['order_number'] . '</td>
                                <input  type="hidden" name="order_number"  value="' . $_POST['order_number'] . '" />
			
				<td>' . _('状态') . ':</td>
				<td width = 120> ' . $_POST['status']. ' </td>
				<td>' . _('建立日') . ':</td>
				<td  width = 150> ' . $v_creation_date . ' </td>
			</tr>
			
			<tr >
				<td>' . _('客户名称') . ':</td>
				<td colspan="3"> ' . $_POST['customer_name'] . ' </td>
					<td>' . _('最近修改日') . ':</td>
				<td> ' . $v_last_update_date . ' </td>
			</tr>
			';
        
        echo'
			 
 
			<tr >
				<td>' . _('计划单头备注') . ':</td>
				<td colspan="2"> ' . $_POST['remark'] . ' </td>
			 
				<td>' . _('签核意见备注') . ':</td>
				<td colspan="3" rowspan="2">  <input type="text"  style="background-color:#FFF68F" size="50" maxlength="200" name="approve_remark" value=> ' . $_POST['approve_remark'] . ' </td>
			</tr> ';
        echo '</table>';
        echo '<br />';
        $sql2 = "SELECT c.status,line,stockid,a.item_desc,uom,quantity,quantity_cancelled,remark,subinventory_code, status,c.last_update_date 
	FROM  so_forecast_line c,
	   sf_item_no a
        where  a.item_no=c.stockid
		and c.order_number = '" .$Updateorder_number."'
		order by line ";
        $result2 = DB_query($sql2, $db);
        if (DB_num_rows($result2) == 0) {
            unset($result2);
            prnMsg(_('没有找到预测计划单详细信息，请重新登录查询！'), 'info');
        } else {
            echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '"><input type="hidden" name = "identifier" value ="' . $identifier . '">';
            echo '<div>';
            echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
            echo '<table class="selection" align="center" >';
            $tableheader = '<tr>
	                           
                                        <th width =50 >' . '行' . '</th>
                                        <th  width =150>' . '料号' . '</th>
                                        <th width =200 >' . '料号描述' . '</th> 
										<th width =70 >' . '状态' . '</th>
                                        <th  width =50>' . '单位' . '</th>
                                        <th width =120 >' . '数量' . '</th> 
                                       <th width =80 >' . '取消数量' . '</th>
                                       <th width =80 >' . '仓库' . '</th>   
									   <th width =80 >' . '需求时间' . '</th>
                                       <th width =120 >' . '备注' . '</th> 
                                       
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

             $v_last_update_date = date('Y-m-d',$_POST['last_update_date']);
                echo '
				
		      <td>' . $myrow['line'] . '</td>
                      <td>' . $myrow['stockid'] . '</td>
                      <td>' . $myrow['item_desc'] . '</td> 
					  <td>' . $myrow['status'] . '</td> 
                      <td>' . $myrow['uom'] . '</td> 
                      <td class="number">' . $myrow['quantity'] . '</td> 
					  <td class="number">' . $myrow['quantity_cancelled'] . '</td> 
					  <td >' . $myrow['subinventory_code'] . '</td>   
					   <td>' .$v_last_update_date . '</td>     
                      <td>' .$myrow['remark'] . '</td>       

					 

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
                                <input type="submit" name="approve" value="核准" />&nbsp;&nbsp;&nbsp;<input type="submit" name="reject" value="拒绝" />&nbsp;&nbsp;&nbsp;<input type="submit" name="cancel" value="取消" />&nbsp;&nbsp;&nbsp;<input type="submit" name="return" value="返回上一层" />
                                 
</div>';
    }
    echo '</div>
          </form>';
}
if (isset($_POST['return'])) {
    header('Location: ForecastApprove.php');
}
include('includes/footer.inc');
?>
