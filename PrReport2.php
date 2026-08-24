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
if (isset($_GET['Updatepr_num'])) {
    $Updatepr_num = $_GET['Updatepr_num'];
} else {
    $Updatepr_num = '';
}
$Title = _('请购单信息');
$ViewTopic = '请购单信息';
$BookMark = '请购单信息';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/CountriesArray.php');

echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/customer.png" title="' . _('请购单') .
 '" alt="" />' . ' ' . _('请购单信息') . '
	</p>';
if (isset($Updatepr_num) and $Updatepr_num != '') {
    //CreditLimit,
    $sql = "select pr_num,status,creation_date,need_date,remark,depart_name,all_amount,pr_use from pr_headers_all where 1=1 and  pr_num ="."'". $Updatepr_num."'";
    $result = DB_query($sql, $db);
    $myrow = DB_fetch_array($result);
    $_POST['pr_num'] = $myrow['pr_num'];
    $_POST['status'] = $myrow['status']; 
    $_POST['creation_date'] = date('Y-m-d H:i:s',$myrow['creation_date']); 
    $_POST['need_date'] = date('Y-m-d',$myrow['need_date']);
    $_POST['remark'] = $myrow['remark'];
    $_POST['depart_name'] = $myrow['depart_name'];
    $_POST['all_amount'] = $myrow['all_amount'];
    $_POST['pr_use'] = $myrow['pr_use'];
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
                <div class="text-nav">
				<div class="text-nav-1 "><div>' . _('请购单号') . ':</div>
				<input type="text" readonly="readonly" value="' . $_POST['pr_num'] . '" />
                                <input  type="hidden" name="pr_num"  value="' . $_POST['pr_num'] . '" /></div>
			
                <div class="text-nav-1 "><div>' . _('状态') . ':</div>
				<input type="text" readonly="readonly" value="' . $v_status . '" /> </div>
				<div class="text-nav-1 "><div>' . _('期望到货日') . ':</div>
				<input type="text" readonly="readonly" value="' . $_POST['need_date'] . '" /> </div>
                <div class="text-nav-1 "><div>' . _('采购用途') . ':</div>
				<input type="text" readonly="readonly" value="' . $_POST['pr_use'] . '" /> </div>
                <div class="text-nav-1 "><div>' . _('预计总金额') . ':</div>
				<input type="text" readonly="readonly" value="' . $_POST['all_amount'] . '" /> </div>
				<div class="text-nav-1 "><div>' . _('建立日') . ':</div>
				<input type="text" readonly="readonly" value="' . $_POST['creation_date'] . '" /> </div> 
				<div class="text-nav-2 "><div>' . _('签核意见备注') . ':</div>
                <input type="text" readonly="readonly" size="50" maxlength="200" name="approve_remark" value=> ' . $_POST['approve_remark'] . ' </div>
                <div class="text-nav-1 "><div>' . _('使用部门') . ':</div>
				<input type="text" readonly="readonly" value="' . $_POST['depart_name'] . '" /> </div>
			</div> '
			;
        echo '</table>'; 
        echo '<br />';
        $sql2 = "SELECT p.wip_entity_name,pr_num,p.line,stockid,s.item_name,s.item_desc,uom,quantity,all_quantity,po_num,po_line,need_date,subinventory_code,po_num,po_line,prtopo_date,prtopo_quantity
                 ,chang,kuan,gao,p.price,p.line_amount,p.remark,p.vendor_code,(SELECT vendor_name FROM vendors v WHERE v.vendor_code=p.vendor_code) vendor_name
                  FROM pr_lines_all p,sf_item_no s
                 where 1=1 and p.stockid=s.item_no
				 and pr_num = " . "'".$Updatepr_num."'";
        $sql2 .= "  order by  p.line";
        $result2 = DB_query($sql2, $db);
        if (DB_num_rows($result2) == 0) {
            unset($result2);
            prnMsg(_('没有找到请购单详细信息，请重新登录查询！'), 'info');
        } else {
            echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '"><input type="hidden" name = "identifier" value ="' . $identifier . '">';
            echo '<div>';
            echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
            echo '<div class="text-nav-table"><table class="selection" align="center" >';
            $tableheader = '<tr>
	                           
	                                <th width =20>' . '行' . '</th>
                                        <th  >' . '料号' . '</th>
                                        <th  >' . '料号名称' . '</th>
                                        <th  >' . '规格型号' . '</th>
                                  
					                    <th  >' . '需求数量' . '</th> 
					                    <th  >' . '预估单价' . '</th> 
					                    <th  >' . '金额' . '</th> 
					                    <th  >' . '供应商简称' . '</th> 
					                    <th  >' . '供应商名称' . '</th> 
					                    <th  >' . '备注' . '</th> 
                                        <th  >' . '单位' . '</th>
                                       
										 <th  width =100>' . '需求日期' . '</th> 
										 <th  width =100>' . '新下采购单' . '</th> 
										 <th  width =50>' . '采购单行' . '</th> 
										 <th  width =100>' . '下单日期' . '</th> 
										 <th  width =80>' . '下单数量' . '</th> 
                                     
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
    $v_need_date= date('Y-m-d',$myrow['need_date']);
	if ($myrow['prtopo_date']>0) {
	   $prtopo_date=date('Y-m-d',$myrow['prtopo_date']);
	} else {
	 $prtopo_date='';
	}
    if ($myrow['uom']=='KG') {
        $myrow['item_desc']=$myrow['chang'].'*'.$myrow['kuan'].'*'.$myrow['gao'];
      } else {
        $myrow['item_desc']=$myrow['item_desc'];
      }
                echo '<td>' . $myrow['line'] . '</td>
		      <td>' . $myrow['stockid'] . '</td>
                      <td>' . $myrow['item_name'] . '</td>
                      <td>' . $myrow['item_desc'] . '</td> 
                 
                      <td>' . $myrow['quantity'] . '</td> 
                      <td>' . $myrow['price'] . '</td> 
                      <td>' . $myrow['line_amount'] . '</td> 
                      <td>' . $myrow['vendor_code'] . '</td> 
                      <td>' . $myrow['vendor_name'] . '</td> 
                      <td>' . $myrow['remark'] . '</td> 
                      <td>' . $myrow['uom'] . '</td>
                     
					  <td>' . $v_need_date . '</td>  	
					  <td>' . $myrow['po_num'] . '</td>
					  <td>' . $myrow['po_line'] . '</td>
					  <td>' . $prtopo_date. '</td>  
					  <td>' . $myrow['prtopo_quantity'] . '</td>  		 	
        </tr> <input type="hidden" name="pr_num" value="' . $myrow['pr_num'] . '" />
        ';
                $RowCounter++;
                If ($RowCounter == 500) {
                    $RowCounter = 1;
                    echo $tableheader;
                }
            }
            echo '</table> ';


            echo '</div></div>
          </form>';
        }

        echo '<br />
                                <input type="submit" name="return" value="' . "关闭当前页面" . '" />&nbsp;
                                 
</div>';
    }
    echo '</div>
          </form>';
}
if (isset($_POST['Reject'])) {
    DB_Txn_Begin($db);
     $v_date = strtotime(Date('Y-m-d H:i:s'));
    $sql1 = " update pr_headers_all 
                set status = 'REJECTED',
                    Approve_date  = '" . $v_date . "',
                    Approve_by  = '" . $_SESSION['UserID'] . "',
					approve_remark ='" . $_POST['approve_remark'] . "'	
               where pr_num ='" . $_POST['pr_num'] . "'";
    $result1 = DB_query($sql1, $db);
    $sql2 = " update pr_lines_all 
                set status = 'REJECTED'
              where pr_num ='" . $_POST['pr_num'] . "'"."and status<> 'Cancel'";
    $result2 = DB_query($sql2, $db);
    DB_Txn_Commit($db);
    $msg = '拒签成功！1秒后将跳转上一页！';
    prnMsg($msg, 'success');
    echo '<meta http-equiv="refresh" content="1; url=' . $RootPath . '/PRApproved.php" />';
    echo '<br />';
    echo '<br /><div class="centre"><a href="' . $RootPath . '/PRApproved.php">' . _('请购单签核') . '</a></div>';
}
if (isset($_POST['Submit'])) {
    DB_Txn_Begin($db);
    $v_date = strtotime(Date('Y-m-d H:i:s'));
    $sql1 = " update pr_headers_all 
                set status = 'APPROVED',
                    Approve_date  = '" . $v_date . "',
                    Approve_by  = '" . $_SESSION['UserID'] . "',
					approve_remark ='" . $_POST['approve_remark'] . "'					
              where pr_num ='" . $_POST['pr_num'] . "'";
    $result1 = DB_query($sql1, $db);
    $sql2 = " update pr_lines_all 
                set status = 'APPROVED'
              where pr_num ='" . $_POST['pr_num'] . "'"
            . "   and status<> 'Cancel' ";
    $result2 = DB_query($sql2, $db);
    DB_Txn_Commit($db);
    $msg = '签核成功！1秒后将跳转上一页！';
    prnMsg($msg, 'success');
    echo '<meta http-equiv="refresh" content="1; url=' . $RootPath . '/PRApproved.php" />';
    echo '<br />';
    echo '<br /><div class="centre"><a href="' . $RootPath . '/PRApproved.php">' . _('请购单签核') . '</a></div>';
}
if (isset($_POST['return'])) {
   echo '<script>window.close();</script>';
}
include('includes/footer.inc');
?>
