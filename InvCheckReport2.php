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
if (isset($_GET['Updatecheck_num'])) {
    $Updatecheck_num = $_GET['Updatecheck_num'];
} else {
    $Updatecheck_num = '';
}
$Title = _('盘点差异明细');
$ViewTopic = '盘点差异明细';
$BookMark = '盘点差异明细';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/CountriesArray.php');
echo '	<div class="centre">
		<a href="' . $RootPath . '/InvCheckCreate.php">返回重新选择盘点单</a>
	</div>';
echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/customer.png" title="' . _('盘点差异明细') .
 '" alt="" />' . ' ' . _('盘点差异明细') . '
	</p>';
if (isset($Updatecheck_num) and $Updatecheck_num != '') {
    //CreditLimit,
    $sql = "SELECT *
FROM inv_check_headers_all a 
WHERE  check_num ='" . $Updatecheck_num . "'  ";
 
    $result = DB_query($sql, $db);
    $myrow = DB_fetch_array($result);
  
    
    if (!isset($_GET['delete'])) {
        echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">'
        . '<input type="hidden" name = "identifier" value ="' . $identifier . '">';
        echo '<div>';
        echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
     if ($myrow['approve_date']>1) {
				$approve_date=date('Y-m-d H:i:s',$myrow['approve_date']);
			}  else {
				$approve_date='';
			} 
        echo '<table class="selection" id="SignFrame">
      <div class="text-nav">
 
   <div class="text-nav-1"><div> ' . _('盘点单号') . ':</div>
				   <input  type="text" readonly="readonly" name="check_num"  value="' . $myrow['check_num'] . '" /></div> 
				   <div class="text-nav-1"> <div>仓库</div>
				   <input  type="text" readonly="readonly" name="subinventory_code"  value="' . $myrow['subinventory_code'] . '" /> </div>
				 
				<div class="text-nav-1"><div>' . _('仓管负责人') . ':</div>
				 <input  type="text" readonly="readonly" name="subinventory_person"  value="' . $myrow['subinventory_person'] . '" /> </div>
                <div class="text-nav-1"><div>' . _('盘点负责人') . ':</div>
				 <input  type="text" readonly="readonly" name="subinventory_person"  value="' . $myrow['check_person'] . '" />  </div>
                <div class="text-nav-1"><div>' . _('建立日') . ':</div>
				 <input  type="text" readonly="readonly" name="subinventory_person"  value="' . date('Y-m-d H:i:s', $myrow['creation_date']) . '" /> </div>
                 <div class="text-nav-1"><div>' . _('建立人员') . ':</div>
				  <input  type="text" readonly="readonly" name="subinventory_person"  value="' . $myrow['created_by'] . '" /> </div>
				  <div class="text-nav-1"><div>' . _('审核日期') . ':</div>
				  <input  type="text" readonly="readonly" name="subinventory_person"  value="' . $approve_date . '" /> </div>
				  <div class="text-nav-1"><div>' . _('审核人') . ':</div>
				  <input  type="text" readonly="readonly" name="subinventory_person"  value="' . $myrow['approve_by'] . '" /> </div>
				  <div class="text-nav-1"><div>' . _('审核备注') . ':</div>
				  <input  type="text" readonly="readonly" name="subinventory_person"  value="' . $myrow['approve_remark'] . '" /> </div>
				';
 
        echo '</div></table>';
        echo '<br />';
        $sql2 = "SELECT a.*,b.item_name,b.item_desc,b.units 
                  FROM inv_check_lines_all a,sf_item_no b 
                 where a.item_no=b.item_no and 1=1 and check_num = '" . $Updatecheck_num . "'  ";
        $result2 = DB_query($sql2, $db);
        if (DB_num_rows($result2) == 0) {
            unset($result2);
            prnMsg(_('盘点无差异！'), 'info');
        } else {
            echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '"><input type="hidden" name = "identifier" value ="' . $identifier . '">';
            echo '<div>';
            echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
            echo '<div style="overflow:auto">';
            echo '<table class="selection" align="center" >';
            $tableheader = '<tr>
	                             <th  >' . '状况' . '</th>
                                        <th  width =100>' . '料号' . '</th>
                                        <th width =250 >' . '料号名称' . '</th>
										<th width =100 >' . '规格型号' . '</th>
                                        <th width =40 >' . '单位' . '</th>
                                        <th width =40 >' . 'SN/批号' . '</th>
                                        <th width =40 >' . '生产日期' . '</th>

					                   <th  width =50>' . '库存数量' . '</th>
                                      <th width =50 >' . '实盘数量' . '</th>
                                      <th width =50 >' . '差异数量' . '</th>   
                                           
				</tr>';
            echo $tableheader;
            $RowCounter = 1;
            $k = 0; //row colour counter
            while ($myrow = DB_fetch_array($result2)) {
                if($myrow['shengchan_date'] > 0){
                    $shengchan_date = date('Y-m-d',$myrow['shengchan_date']);
                }else{
                    $shengchan_date = '';
                }
                
            
            if ( $myrow['stock_quantity']>$myrow['check_quantity']) {
                echo ' <tr bgcolor="red"> 
				<td>盘亏</td>
		      <td>' . $myrow['item_no'] . '</td>
                      <td>' . $myrow['item_name'] . '</td>
				    <td>' . $myrow['item_desc'] . '</td>
                      <td>' . $myrow['units'] . '</td>
                      <td>' . $myrow['lot_num'] . '</td>
                      <td>' . $shengchan_date . '</td>

                      <td>' . $myrow['stock_quantity'] . '</td>
                      <td>' . $myrow['check_quantity'] . ' </td>
                      <td> ' . ($myrow['check_quantity']-$myrow['stock_quantity']) . ' </td>
                                           
                    
                      </tr> <input type="hidden" name="po_num" value="' . $myrow['po_num'] . '" />
                ';
			} else  {
                echo ' <tr bgcolor="#87CEFA"> 
				<td>盘赢</td>
		      <td>' . $myrow['item_no'] . '</td>
                      <td>' . $myrow['item_name'] . '</td>
				    <td>' . $myrow['item_desc'] . '</td>
                      <td>' . $myrow['units'] . '</td>
                      <td>' . $myrow['lot_num'] . '</td>
                      <td>' . $shengchan_date . '</td>

                      <td>' . $myrow['stock_quantity'] . '</td>
                      <td>' . $myrow['check_quantity'] . ' </td>
                      <td> ' . ($myrow['check_quantity']-$myrow['stock_quantity']) . ' </td>
                                           
                    
                      </tr> <input type="hidden" name="po_num" value="' . $myrow['po_num'] . '" />
                ';
			}
			 

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

        
    }
    echo '</div>
          </form>';
}

include('includes/footer.inc');
?>
