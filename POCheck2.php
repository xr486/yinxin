<?php
/* $Id: SupplierTransInquiry.php 5785 2012-12-29 04:47:42Z daintree $ */

include('includes/session.inc');
$Title = '采购单进货检验结果查询';
include('includes/header.inc');
if (isset($_GET['NUM'])) {
    $NUM = $_GET['NUM'];
} else {
    $NUM = '';
}
if (isset($_GET['identifier'])) {
    $_POST['identifier'] = $_GET['identifier'];
}
if (!isset($_POST['identifier'])) {
    $identifier = date('U');
} else {
    $identifier = $_POST['identifier'];
}


echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . '采购单进货检验结果查询' .
 '" alt="" />' . ' ' . $Title . '
	</p>';

if (isset($NUM) and $NUM != '') {
    // $SQL_FromDate = FormatDateForSQL($_POST['FromDate']);
    // $SQL_ToDate = FormatDateForSQL($_POST['ToDate']);
    $sql = "select 
                rh.receipt_num,
                a.po_num,
                b.line,
                b.stockid,
                c.item_desc,
                c.item_name,
                a.vendor_code,
                d.vendor_name,
                rl.receipt_line,
                rl.quantity_received,
                (select  sum(transaction_quantity) 
                 from    po_rcv_transactions prt 
                 where   transaction_type='REJECT' 
                 and     prt.receipt_num=rl.receipt_num 
                 and     rl.receipt_line=prt.receipt_line  ) 
                 REJECT_qty ,
                (select  sum(transaction_quantity) 
                 from    po_rcv_transactions prt  
                 where   transaction_type='ACCEPT' 
                 and     prt.receipt_num=rl.receipt_num 
                 and     rl.receipt_line=prt.receipt_line ) 
                 ACCEPT_qty ,
                 ifnull(rl.quantity_received,0)-ifnull(rl.already_inspection_qty,0) qty,
                 rl.subinventory_code,
                 b.need_date,
                 rh.creation_date
                 FROM  po_headers_all a,
                       po_lines_all b,
			           po_rcv_receipt_header rh,
			           po_rcv_receipt_line  rl,
                       sf_item_no c,
                       vendors d
                 WHERE   a.po_num=b.po_num    
                 and     a.vendor_code=d.vendor_code
                 and     b.po_num=rl.po_num
                 and     b.line=rl.po_line
                 and     b.stockid=c.item_no 
                 and     rl.receipt_num=rh.receipt_num
      ";

//    if (empty($_POST['po_num_search']) == 0) {
//        $sql .= " AND a.po_num= '" . $_POST['po_num_search'] . "'";
//    }
    
        $sql .= " and rh.receipt_num= '" . $NUM . "' ";
//                . "or a.vendor_name like %'".$_POST['vendor'] ."'%";
//    }
//    if (Is_Date($_POST['FromDate'])) {
//        $SQL_FromDate = strtotime($_POST['FromDate']);
//        $sql .= " and a.purchase_date >= '" . $SQL_FromDate . "' ";
//    }
//    if (Is_Date($_POST['ToDate'])) {
//        $SQL_ToDate = strtotime($_POST['ToDate']) + 86400;
//        $sql .= " and a.purchase_date <='" . $SQL_ToDate . "' ";
//    }

    $sql .= " ORDER BY a.po_num,b.line";
    $TransResult = DB_query($sql, $db);
    
    $ErrMsg = _('来料报检单查询错误，请查看所选采购单') . ' - ' . DB_error_msg($db);
    $DbgMsg = _('The SQL that failed was');
    if (DB_num_rows($TransResult) == 0) {
        unset($TransResult);
        prnMsg(_('没有找到需要检测的来料报检单，请重新输入条件查询！'), 'info');
    } else {
        echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '"><input type="hidden" name = "identifier" value ="' . $identifier . '">';
        echo '<div>';
//echo '<div style="width:1300px;height:400px;overflow-x: hidden; overflow-y: scroll;">';
        echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
        echo '<table class="selection" align="center" >';
        $tableheader = '<tr>
                                          <th width =100>' . '来料报检单号' . '</th>
										  <th width =20>' . '行' . '</th>
                                         <th width =90 >' . '供应商编码' . '</th>
	                                     <th width =100>' . '采购单号' . '</th>
                                         <th width =20>' . '行' . '</th>
                                         <th width =150>' . '料号' . '</th>
                                         <th width =250>' . '料号名称' . '</th>
                                         <th width =250>' . '规格型号' . '</th>
                                         <th  width = 70>' . '收货量' . '</th> 
                                         <th width =70 >' . '合格量' . '</th>
                                         <th width =70 >' . '不合格量' . '</th>
                                         <th width =70 >' . '仓库' . '</th>
										 <th width =160 >' . '检验日期' . '</th>
                                        
                                       
				</tr>';
        echo $tableheader;

        $RowCounter = 1;
        $k = 0; //row colour counter

        while ($myrow = DB_fetch_array($TransResult)) {

            if ($k == 1) {
                echo '<tr class="EvenTableRows">';
                $k = 0;
            } else {
                echo '<tr class="EvenTableRows">';
                ;
                $k++;
            }
            echo '<td>' . $myrow['receipt_num'] . '</td>';
			echo '<td>' . $myrow['receipt_line'] . '</td>';
                echo '<td>' . $myrow['vendor_code'] . '</td>';
                echo '<td><font color="red">' . $myrow['po_num'] . '</font></td>';
            echo '<td>' . $myrow['line'] . ' </td>';
            echo '<td>' . $myrow['stockid'] . ' </td>';
            echo '<td>' . $myrow['item_name'] . ' </td>';
            echo '<td>' . $myrow['item_desc'] . ' </td>';

            echo '<td>' . $myrow['quantity_received'] . ' </td>';
            echo '<td>' . $myrow['ACCEPT_qty'] . ' </td>';
             echo '<td>' . $myrow['REJECT_qty'] . ' </td>';
            echo '<td>' . $myrow['subinventory_code'] . ' </td>';
			echo '<td>' . date('Y-m-d H:i:s',$myrow['creation_date']) . '</td>';

echo '</tr>';
            
            $RowCounter++;
            If ($RowCounter == 500) {
                $RowCounter = 1;
                echo $tableheader;
            }
        }
        echo '</table> ';
         echo '<div class="centre">
                             <input type="submit" name="return" value="' . "关闭当前页面" . '" />
		</div>';
        echo '</div>
          </form>';
    }
}
if (isset($_POST['return'])) {
//    echo 'AAAAAAAAAA';
  echo '<script>window.close();</script>'; 
}
//}
include('includes/footer.inc');
?>