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
$Title = _('销售订单审批处理');
$ViewTopic = '销售订单审批处理';
$BookMark = '销售订单审批处理';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
// include('includes/CountriesArray.php');

echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/customer.png" title="' . _('销售订单') .
 '" alt="" />' . ' ' . _('销售订单审批处理') . '
	</p>';
if (isset($Updateorder_number) and $Updateorder_number != '') {
    //CreditLimit,
    $sql = "SELECT
                    a.order_number,
                    a.status,
                    b.customer_name,
                    a.order_all_amount,a.youhui_amount,a.order_payment_amount,a.order_invoice_amount,
                    a.tax_name,
                    a.currency_code,
                    a.header_remark ,
                    a.need_date,a.qianding_date,
					b.customer_contacts,
		b.contacts_phone, a.tax_name,a.term_name,
                    a.creation_date,a.customer_code,a.subject,a.project,c.employee_name yewu,a.coycode,subject,project,jiaohuotiaojian,baozhuang,zhiliangbaozheng,mainfeifuwu,a.ship_address,a.youxiaoxing1,a.youxiaoxing2,yunfei_amount
                FROM
                    so_headers_all a,
                    customers b ,hr_employees c 
                WHERE b.customer_code= a.customer_code
				and a.status  in ( '高阶签核') 
                    and c.employee_num=a.yewu
                and a.order_number = '" .$Updateorder_number."'";
				 
    $result = DB_query($sql, $db);
    $myrow = DB_fetch_array($result);
    $_POST['order_number'] = $myrow['order_number'];
    $_POST['status'] = $myrow['status'];
    $_POST['remark'] = $myrow['header_remark'];
    $_POST['order_payment_amount'] = $myrow['order_payment_amount'];   
    $_POST['order_invoice_amount'] = $myrow['order_invoice_amount'];   
	$_POST['customer_code']=$myrow['customer_code'];
    $_POST['tax_name']=$myrow['tax_name'];
    $_POST['youhui_amount']=$myrow['youhui_amount'];
    $_POST['order_all_amount']=$myrow['order_all_amount'];
    $_POST['currency_code']=$myrow['currency_code'];
    $_POST['customer_name']=$myrow['customer_name'];
    $_POST['need_date'] = $myrow['need_date'];
    $_POST['qianding_date'] = $myrow['qianding_date'];
    $_POST['creation_date'] = $myrow['creation_date'];
	$_POST['yewu']=$myrow['yewu'] ;
	$_POST['ship_address']=$myrow['ship_address'] ;
	   $_POST['subject']=$myrow['subject'] ;
	   $_POST['project']=$myrow['project'] ;
	   $_POST['jiaohuotiaojian']=$myrow['jiaohuotiaojian'] ;
	   $_POST['baozhuang']=$myrow['baozhuang'] ;
	   $_POST['zhiliangbaozheng']=$myrow['zhiliangbaozheng'] ;
	   $_POST['mainfeifuwu']=$myrow['mainfeifuwu'] ;
	   $_POST['currency_code']=$myrow['currency_code'] ;
	   $_POST['tax_name']=$myrow['tax_name'] ;
	   $_POST['term_name']=$myrow['term_name'] ;
	   $_POST['coycode']=$myrow['coycode'] ; 
	   $_POST['yunfei_amount']=$myrow['yunfei_amount'] ; 
	   $_POST['youxiaoxing1']=$myrow['youxiaoxing1'] ;
	   $_POST['youxiaoxing2']=$myrow['youxiaoxing2'] ;
    $creation_date = strtotime(Date('Y-m-d H:i:s'));
    if (isset($_POST['approvedd'])){
		 
        $sql = "update so_headers_all set status = '已签核',approve_date=". $creation_date .",approve_remark='". $_POST['approve_remark']  ."',approved_by='" . $_SESSION['UserID'] . "' where order_number = '".$_POST['order_number']."' ";
       
        $result = DB_query($sql,$db);
        prnMsg('销售订单签核成功！',success);
       header("Location: ARFinApprove.php?OrderNum=$Updateorder_number");
        // echo "<script>location.href='SearchSoForApprove.php';</script>";
    }
 
    if (isset($_POST['reject'])){
        $sql = "update so_headers_all set status = '已拒签',approve_date=". $creation_date .",approve_remark='". $_POST['approve_remark']  ."',approved_by='" . $_SESSION['UserID'] . "'  where order_number = '".$_POST['order_number']."' ";
        $result = DB_query($sql,$db);
        prnMsg('销售订单拒签成功！',success);
         header("Location: ARFinApprove.php?OrderNum=$Updateorder_number");
        // echo "<script>location.href='SearchSoForApprove.php';</script>";
    }
    if (isset($_POST['cancel'])){
        $sql = "update so_headers_all set status = '已取消',approve_date=". $creation_date .",approve_remark='". $_POST['approve_remark']  ."',approved_by='" . $_SESSION['UserID'] . "'  where order_number = '".$_POST['order_number']."' ";
       
        $result = DB_query($sql,$db);
        prnMsg('销售订单取消成功！',success);
        header("Location: ARFinApprove.php?OrderNum=$Updateorder_number");
        // header("Location: SussCreate.php?OrderNum=$$Updateorder_number");
        // echo "<script>location.href='SearchSoForApprove.php';</script>";
    }


    if (!isset($_GET['delete'])) {
        echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">'
        . '<input type="hidden" name = "identifier" value ="' . $identifier . '">';
        echo '<div>';
        echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
       
        echo '<table class="selection" id="SignFrame">
                       <tr class="EvenTableRows">
				<td bgcolor="#87CEFA">' . _('销售订单编号') . ':</td>
				<td    >' . $_POST['order_number'] . '</td>
                                <input  type="hidden" name="order_number"  value="' . $_POST['order_number'] . '" />
			<td bgcolor="#87CEFA">' . _('客户') . ':</td>
				<td   > ' . $_POST['customer_code'] . ' </td>
				<td bgcolor="#87CEFA">' . _('客户名称') . ':</td>
				<td colspan="3"> ' . $_POST['customer_name'] . ' </td>
			
			</tr>
			
			<tr >
			<td bgcolor="#87CEFA">' . _('地址') . ':</td>
			 <td  colspan="6"> ' . $_POST['ship_address'] . ' </td>
			  <td bgcolor="#87CEFA">' . _('状态') . ':</td>
			 <td  > ' .   $_POST['status']. ' </td>
			</tr>
			';
        
        $v_need_date = date('Y-m-d',$_POST['need_date']);
		$v_qianding_date = date('Y-m-d',$_POST['qianding_date']);
        $v_creation_date = date('Y-m-d H:i:s',$_POST['creation_date']);
        echo'
			<tr >
			 <td bgcolor="#87CEFA">' . _('付款条件') . ':</td>
				<td   > ' . $_POST['term_name']. ' </td>
				<td bgcolor="#87CEFA">' . _('订单备注') . ':</td>
				<td colspan="2"> ' . $_POST['remark'] . ' </td>
				
				<td bgcolor="#87CEFA">' . _('签订日') . ':</td>
				<td> ' . $v_qianding_date . ' </td>
				<td bgcolor="#87CEFA">' . _('建立日') . ':</td>
				<td  > ' . $v_creation_date . ' </td>
			 </tr> 
			 
				
			 </tr> 
			
                
                <td bgcolor="#87CEFA">' . _('税别') . ':</td>
				<td  > ' . $_POST['tax_name']. ' </td>
                
                <td bgcolor="#87CEFA">' . _('币别') . ':</td>
				<td  > ' . $_POST['currency_code']. ' </td>
                <td bgcolor="#87CEFA">' . _('需求日') . ':</td>
				<td> ' . $v_need_date . ' </td>
				<td bgcolor="#87CEFA">' . _('应开票金额') . ':</td>
				<td  > ' . $_POST['order_invoice_amount']. ' </td>				 
                            
			</tr>
            
             </tr> 
			
                
                <td bgcolor="#87CEFA">' . _('总金额') . ':</td>
				<td  > ' . $_POST['order_all_amount']. ' </td>
                
                <td bgcolor="#87CEFA">' . _('优惠金额') . ':</td>
				<td   > ' . $_POST['youhui_amount']. ' </td>
				<td bgcolor="#87CEFA">' . _('运费') . ':</td>
				<td  > ' . $_POST['yunfei_amount']. ' </td>
                <td bgcolor="#87CEFA">' . _('实际应收金额') . ':</td>
				<td   > ' . $_POST['order_payment_amount']. ' </td>
            </tr>  
               
		 

		 
			<tr >
				<td bgcolor="#87CEFA">' . _('签核意见备注') . ':</td>
				<td colspan="3" rowspan="2">  <input type="text"  style="background-color:#FFF68F" size="50" maxlength="200" name="approve_remark" value=> ' . $_POST['approve_remark'] . ' </td>
			</tr> ';
        echo '</table>';
        echo '<br />';
 
		 $sql22 = "SELECT
	 	file_patch,creation_date,created_by,file_name
FROM so_headers_all_file  
        where  order_number = '" .$Updateorder_number."'";
        $result22 = DB_query($sql22, $db);
        if (DB_num_rows($result22) == 0) {
            unset($result22);
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
	                           
                                        <th bgcolor="#87CEFA" width =150 >' . '附件名称' . '</th>
										<th bgcolor="#87CEFA" width =190 >' . '上传时间' . '</th>
										<th bgcolor="#87CEFA" width =80 >' . '上传人员' . '</th>
                                        <th bgcolor="#87CEFA"  width =50>' . '下载' . '</th>
									 
                                       
                                       
				</tr>';
                       
            echo $tableheader;
            $RowCounter = 1;
            $k = 0; //row colour counter
            while ($myrow22 = DB_fetch_array($result22)) {
                if ($k == 1) {
                    echo '<tr class="EvenTableRows">';
                    $k = 0;
                } else {
                    echo '<tr class="EvenTableRows">';
                    $k++;
                }
 
                echo '
		              <td>' . $myrow22['file_name'] . '</td>
                      <td>' . date('Y-m-d h:i:s',$myrow22['creation_date']) . '</td>
					  <td>' . $myrow22['created_by'] . '</td>                      
					  <td><a href="' . $RootPath . '/' . $myrow22['file_patch'] . '" target="_blank">' . '下载' . '</td>
                     
                        

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

        $sql2 = "SELECT distinct p.order_number,c.line,c.stockid,a.item_desc,a.item_name,a.gongyi,price,uom,quantity,quantity_cancelled,line_remark,subinventory_code, line_amount,c.zhidao_price,c.other_price,c.price,c.customer_item
	FROM  so_lines_all c,so_headers_all p,
	   sf_item_no a
        where  a.item_no=c.stockid
        and p.order_number = c.order_number
		and p.status not in ( 'Cancel' ,'APPROVED') 
		and p.order_number = '" .$Updateorder_number."'
		order by line ";


        $result2 = DB_query($sql2, $db); 
        if (DB_num_rows($result2) == 0) {
            unset($result2);
            prnMsg(_('没有找到销售订单详细信息，请重新登录查询！'), 'info');
        } else {
            echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '"><input type="hidden" name = "identifier" value ="' . $identifier . '">';
            echo '<div>';
            echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
            echo '<div style="overflow:scroll"> <table class="selection" align="center" >';
		
            $tableheader = '<tr>
	                           
                                        <th bgcolor="#87CEFA" width =40 >' . '行' . '</th>
                                        <th bgcolor="#87CEFA"  width =180>' . '料号' . '</th>
                                        <th bgcolor="#87CEFA" width =150 >' . '料号名称' . '</th>
                                        <th bgcolor="#87CEFA" width =100 >' . '规格型号' . '</th> 
				                        <th bgcolor="#87CEFA"  width =80>' . '销售单价' . '</th>
                                        <th bgcolor="#87CEFA"  width =50>' . '单位' . '</th>
                                        <th bgcolor="#87CEFA" width =170 >' . '客户料号' . '</th> 
                                        <th bgcolor="#87CEFA" width =70 >' . '数量' . '</th> 
                                       <th bgcolor="#87CEFA" width =80 >' . '取消数量' . '</th>
                                       <th bgcolor="#87CEFA" width =80 >' . '仓库' . '</th>
                                                                 
                                       <th bgcolor="#87CEFA" width =80 >' . '金额' . '</th>
                                       <th bgcolor="#87CEFA" width =180 >' . '备注' . '</th> 
                                       
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
               

             $v_need_date = date('Y-m-d',$_POST['need_date']);
                echo '
				
		      <td>' . $myrow['line'] . '</td>
                      <td>' . $myrow['stockid'] . '</td>
                      <td>' . $myrow['item_name'] . '</td>
                      <td>' . $myrow['item_desc'] . '</td> 
                      <td class="number">' . $myrow['price'] . '</td>
                      <td>' . $myrow['uom'] . '</td> 
                      <td  >' . $myrow['customer_item'] . '</td> 
                      <td class="number">' . $myrow['quantity'] . '</td> 
					  <td class="number">' . $myrow['quantity_cancelled'] . '</td> 
					  <td class="number">' . $myrow['subinventory_code'] . '</td> 
				 
					  <td class="number">' . $myrow['line_amount'] . '</td>   
                      <td>' .$myrow['line_remark'] . '</td>       

					 

        </tr>';
        
                $RowCounter++;
                If ($RowCounter == 500) {
                    $RowCounter = 1;
                    echo $tableheader;
                }
            }
            echo '</table> </div>';


            echo '</div>
          </form>';
        }

        echo '<br />
                                <input type="submit" name="approvedd" value="核准" />&nbsp;&nbsp;&nbsp;
								<input type="submit" name="reject" value="拒绝" />&nbsp;&nbsp;&nbsp;<input type="submit" name="cancel" value="取消" />&nbsp;&nbsp;&nbsp;
                                 
</div>';
    }
    echo '</div>
          </form>';
}
if (isset($_POST['return'])) {
    header('Location: SearchSoForApprove.php');
}
include('includes/footer.inc');
?>
