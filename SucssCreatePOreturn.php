<?php
include('includes/session.inc');
$Title = '采购单退货创建明细';
include('includes/header.inc');
$OrderNum=$_GET['OrderNum'];
$msg = '采购单退货编号'.$OrderNum.'建立成功！';
                prnMsg($msg, success);
                 echo '<br /><div class="centre"><a href="' . $RootPath . '/InDeliveryNo.php">' . _('继续采购单退货') . '</a></div>';
				
                 echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . '采购退货明细' .
 '" alt="" />' . ' ' . $Title . '
	</p>';

  $sql2 = "SELECT a.item_desc,c.receipt_num,c.receipt_line,c.stockid,c.quantity_received,c.remark,c.line_amount,c.unit_price,c.uom
	FROM  po_rcv_receipt_line c,
	   sf_item_no a
        where  a.item_no=c.stockid
		and c.receipt_num = '" .$OrderNum."'
		order by receipt_line ";
        
        $result2 = DB_query($sql2, $db);
        if (DB_num_rows($result2) == 0) {
            unset($result2);
            prnMsg(_('没有找到报价单详细信息，请重新登录查询！'), 'info');
        } else {
            echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '"><input type="hidden" name = "identifier" value ="' . $identifier . '">';
            echo '<div>';
            echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
            echo '<table class="selection" align="center" >';
            $tableheader = '<tr>  <th width =50 >' . '行' . '</th>
                                        <th  width =100>' . '规格型号' . '</th>
                                        <th width =150 >' . '产品名称' . '</th>                                   
				                         <th  width =50>' . '单位' . '</th>
                                        <th width =90 >' . '数量' . '</th> 
                                        <th  width =50>' . '单价' . '</th>                                   
                                       <th width =80 >' . '金额' . '</th> 
                                       <th width =120 >' . '备注' . '</th> 
                                       
				</tr>'; 
                // <th width =80 >' . '取消数量' . '</th>
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
				
		      <td>' . $myrow['receipt_line'] . '</td>
                      <td>' . $myrow['stockid'] . '</td>
                      <td>' . $myrow['item_desc'] . '</td>
                   
                     
                      <td>' . $myrow['uom'] . '</td> 
                      <td class="number">' . $myrow['quantity_received'] . '</td> 
                       <td class="number">' . $myrow['unit_price'] . '</td>
					
					
					  <td class="number">' . $myrow['line_amount'] . '</td>  
					  <td class="number">' . $myrow['huoqi'] . '</td>      
                      <td>' .$myrow['remark'] . '</td>       

					 

        </tr>';
          // <td class="number">' . $myrow['quantity_cancelled'] . '</td> 
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


?>