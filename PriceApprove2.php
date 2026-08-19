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
$Title = _('采购价格申请单信息');
$ViewTopic = '采购价格申请单信息';
$BookMark = '采购价格申请单信息';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/CountriesArray.php');

echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/customer.png" title="' . _('采购价格申请单') .
 '" alt="" />' . ' ' . _('采购价格申请单信息') . '
	</p>';
if (isset($Updatepo_num) and $Updatepo_num != '') {
    //CreditLimit,
    $sql = "SELECT a.*,b.vendor_name
FROM po_vendor_price_header a, vendors b
WHERE 1 =1
AND a.vendor_code = b.vendor_code  
AND po_num ='" . $Updatepo_num . "'";
    $result = DB_query($sql, $db);
    $myrow = DB_fetch_array($result);
    $_POST['po_num'] = $myrow['po_num'];
    $_POST['create_date'] = $myrow['creation_date'];
    $_POST['vendor_name'] = $myrow['vendor_name'];
    $_POST['vendor_code'] = $myrow['vendor_code'];
    $_POST['created_by'] = $myrow['created_by']; 
    
    if (!isset($_GET['delete'])) {
        echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">'
        . '<input type="hidden" name = "identifier" value ="' . $identifier . '">';
        echo '<div>';
        echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
     
        $v_create_date = date('Y-m-d H:i:s', $_POST['create_date']);
		
        echo '<table class="selection" id="SignFrame">
                       <div class="text-nav">
				<div class="text-nav-1"><div>' . _('采购价格申请单号') . ':</div>
				<input type="text" name="po_num" readonly="readonly" value="' . $_POST['po_num'] . '" /></div>
             
                <div class="text-nav-1"><div>' . _('申请日') . ':</div>
				<input type="text" readonly="readonly" value="' . $v_create_date . '" /> </div>
                <div class="text-nav-1"><div>' . _('申请人员') . ':</div>
				<input type="text" readonly="readonly" value="' . $_POST['created_by'] . '" /> </div>
				';
       
        echo'
               
				<div class="text-nav-1"><div>' . _('供应商代号') . ':</div>
				<input type="text" readonly="readonly" value="' . $_POST['vendor_code'] . '" /></div>
				 
				<div class="text-nav-2"><div>' . _('供应商名称') . ':</div>
				<input type="text" readonly="readonly" value="' . $_POST['vendor_name'] . '" /></div>
              
                <div class="text-nav-1"><div>' . _('签核备注') . ':</div>
                <input type="text" name="approve_remark"  value="' . $_POST['approve_remark'] . '" /> </div>
               
			</div>
					
				';
 
        echo '</table>';
        echo '<br />';
        $sql2 = "SELECT a.*,b.item_name,b.item_desc,b.units
                  FROM po_vendor_price_line a,sf_item_no b
                 where a.item_no=b.item_no and a.status='开始' and po_num = '" . $Updatepo_num . "'";
		//echo $sql2;
        $result2 = DB_query($sql2, $db);
        if (DB_num_rows($result2) == 0) {
            unset($result2);
            prnMsg(_('没有信息，请重新查询！'), 'info');
        } else {
            echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '"><input type="hidden" name = "identifier" value ="' . $identifier . '">';
            echo '<div>';
            echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
            echo '<div class="text-nav-table"> <table class="selection" align="center" >';
            $tableheader = '<tr>
	                           
	                                <th width =50>' . '行' . '</th>
                                        <th  width =120>' . '料号' . '</th>
                                        <th width =250 >' . '料号名称' . '</th>
										<th width =250 >' . '规格型号' . '</th> 
                                        <th width =50 >' . '单位' . '</th>
                                      <th width =50 >' . '上次单价' . '</th>
                                      <th width =50 >' . '单价' . '</th>   
                                           
				</tr>';
            echo $tableheader;
            $RowCounter = 1;
            $k = 0; //row colour counter
			$i =0 ;
            while ($myrow = DB_fetch_array($result2)) {
                if ($k == 1) {
                    echo '<tr class="EvenTableRows">';
                    $k = 0;
                } else {
                    echo '<tr class="EvenTableRows">';
                    $k++;
                }
        
				$i=$i+1;
                echo ' <tr bgcolor="LavenderBlush"><td>' . $i . '</td>
		      <td>' . $myrow['item_no'] . '</td>
                      <td>' . $myrow['item_name'] . '</td>
				    <td>' . $myrow['item_desc'] . '</td> 
                      <td>' . $myrow['units'] . '</td>
                      <td>' . sprintf("%.5f",$myrow['last_price']) . ' </td>
                      <td> ' . sprintf("%.5f",$myrow['price']) . ' </td>                  
                    
                      </tr> <input type="hidden" name="po_num" value="' . $myrow['po_num'] . '" />
                ';
			
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

        echo '<br />
          <input type="submit" name="Submit" value="' . "签核" . '" /> &nbsp;&nbsp;&nbsp;&nbsp;   
		  <input type="submit" name="Reject" value="' . "拒签" . '" />&nbsp;&nbsp;&nbsp;&nbsp;  
                                 
</div>';
	
    
    echo '</div>
          </form>';
}
}
//拒签
if (isset($_POST['Reject'])) {
    DB_Txn_Begin($db);
    $v_date = strtotime(Date('Y-m-d H:i:s'));
    $sql1 = " update po_vendor_price_header 
                set status = '拒绝',
				approve_remark = '".$_POST['approve_remark']."',
		     approve_date  = '" . $v_date . "',
              approved_by  = '" . $_SESSION['UserID'] . "'
               where po_num ='" . $_POST['po_num'] . "'";
    $result1 = DB_query($sql1, $db);
  
    $sql3 = " update po_vendor_price_line 
                set status = '拒绝',
		     last_update_date  = '" . $v_date . "',
              last_updated_by  = '" . $_SESSION['UserID'] . "'
               where po_num ='" . $_POST['po_num'] . "'";
    $result3 = DB_query($sql3, $db);

    DB_Txn_Commit($db);
    $msg = '拒签成功！1秒后将跳转上一页！';
    prnMsg($msg, 'success');
    echo '<meta http-equiv="refresh" content="1; url=' . $RootPath . '/PriceApprove.php" />';
    echo '<br />';
    echo '<br /><div class="centre"><a href="' . $RootPath . '/PriceApprove.php">' . _('采购价格申请单签核') . '</a></div>';
}
if (isset($_POST['Submit'])) {
    DB_Txn_Begin($db);
    $v_date = strtotime(Date('Y-m-d H:i:s'));

    $sql1 = " update po_vendor_price_header 
                set status = '核准',
				   approve_remark = '".$_POST['approve_remark']."',
                    approve_date  = '" . $v_date . "',
                    approved_by  = '" . $_SESSION['UserID'] . "'
              where po_num ='" . $_POST['po_num'] . "'";
    $result1 = DB_query($sql1, $db);
	DB_Txn_Commit($db); 
    $msg = '签核成功！1秒后将跳转上一页！';
    prnMsg($msg, 'success');
    echo '<meta http-equiv="refresh" content="1; url=' . $RootPath . '/PriceApprove.php" />';
    echo '<br />';
    echo '<br /><div class="centre"><a href="' . $RootPath . '/PriceApprove.php">' . _('采购价格申请单签核') . '</a></div>';
	
 

	$sql3 = " select a.*,b.vendor_code from  po_vendor_price_line a,po_vendor_price_header b 
                where a.po_num =b.po_num  and a.status = '开始' and a.po_num ='" . $_POST['po_num'] . "'";
    $result3 = DB_query($sql3, $db);

	while  ($myrow3 = DB_fetch_array($result3)) { 
    $time=time();

         $sql2 = "select  * from po_vendor_item_price where  	item_no = '" . $myrow3['item_no']. "'";
		          $result2 = DB_query($sql2, $db);
		          $rownum2 = DB_num_rows($result2);
                 if ( $rownum2==1 ) {
					 $sql2 = "update po_vendor_item_price 
					 set price = '" . $myrow3['price']. "',
					 last_price  = '" . $myrow3['last_price']. "',
					 vendor_code = '" . $myrow3['vendor_code']. "',
					 last_update_date = '" . $time. "', 
					 last_updated_by  = '" . $_SESSION['UserID']. "'
					 where  	item_no = '" . $myrow3['item_no']. "'";
		          $result2 = DB_query($sql2, $db);
                   
				 }  else {
				 $sql = "insert into po_vendor_item_price(vendor_code, item_no,
                    last_price,
                    price,  creation_date, created_by, last_update_date, last_updated_by 
                      )
                                 values('".$myrow3['vendor_code']."',
                                 '".$myrow3['item_no']."',
								 '".$myrow3['last_price']."',
                                 '".$myrow3['price']."',
														 '".$time."',
														 '".$_SESSION['UserID']."',
														 '".$time."',
														 '".$_SESSION['UserID']."'
														 )";  
					$result = DB_query($sql,$db);
				 
				 }

	}
    
    
}
if (isset($_POST['return'])) {
    header('Location: PriceApprove.php');
}
include('includes/footer.inc');
?>
