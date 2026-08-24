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
if (isset($_GET['searchitem_no'])) {
    $searchitem_no = $_GET['searchitem_no'];
} else {
    $searchitem_no = '';
}
$Title = _('工单用料明细');
$ViewTopic = '工单用料明细';
$BookMark = '工单用料明细';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc'); 

echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/customer.png" title="' . _('工单用料明细') .
 '" alt="" />' . ' ' . _('工单用料明细') . '
	</p>';
if (isset($searchitem_no) and $searchitem_no != '') {
    //CreditLimit,
    
        $sql2 = "SELECT b.date_required,b.required_quantity,b.quantity_issued,units,item_no,item_desc,item_name,operation_seq_num  
FROM wip_material_requierments b,sf_item_no a
        where a.item_no = b.segment1
		and b.wip_entity_name = '" .$searchitem_no."'";
        $result2 = DB_query($sql2, $db);
        if (DB_num_rows($result2) == 0) {
            unset($result2);
            prnMsg(_('没有找到工单用料明细，请重新登录查询！'), 'info');
        } else {
            echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '"><input type="hidden" name = "identifier" value ="' . $identifier . '">';
            echo '<div>';
            echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
            echo '<table class="selection" align="center" >';
            $tableheader = '<tr>
	                            
										<th width =50 >' . '工序' . '</th> 
                                        <th  width =140>' . '料号' . '</th>
										<th  width =200>' . '料号名称' . '</th>
										<th  width =200>' . '规格型号' . '</th>
                                        <th width =50 >' . '单位' . '</th>  
										<th width =70 >' . '需求量' . '</th> 
										<th width =70 >' . '已发量' . '</th> 
                                       <th width =160 >' . '需求日期' . '</th>  
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
				
				 
 
                echo ' 
					  <td >' . $myrow['operation_seq_num'] . '</td> 
                      <td>' . $myrow['item_no'] . '</td>
					  <td>' . $myrow['item_name'] . '</td>
					  <td>' . $myrow['item_desc'] . '</td>
                      <td>' . $myrow['units'] . '</td> 
                       <td class="number">' .$myrow['required_quantity'] . '</td>
					   <td class="number">' .$myrow['quantity_issued'] . '</td>
                      
					   <td>' . date('Y-m-d', $myrow['date_required']) . '</td> 
					  <td>' . $myrow['comments']  . '</td>
               </tr>';
                $RowCounter++;
                If ($RowCounter == 500) {
                    $RowCounter = 1;
                    echo $tableheader;
                }
            }
            echo '</table> ';


        }

        echo '<br />

                                <input type="submit" name="return" value="' . "关闭当前页面" . '" />&nbsp;
                                 
</div>';
    
    echo '</div>
          </form>';
}
if (isset($_POST['return'])) {
    echo 'AAAAAAAAAA';
    echo '<script>window.close();</script>'; 
}
include('includes/footer.inc');
?>
