<?php
/* $Id: SupplierTransInquiry.php 5785 2012-12-29 04:47:42Z daintree $ */

include('includes/session.inc');
$Title = '工单退料';
include('includes/header.inc');
if (isset($_GET['UpdateWIP_ENTITY_NAME'])) {
    $UpdateWIP_ENTITY_NAME = $_GET['UpdateWIP_ENTITY_NAME'];
} else {
    $UpdateWIP_ENTITY_NAME = '';
}
if (isset($_GET['identifier'])) {
    $_POST['identifier'] = $_GET['identifier'];
}
if (!isset($_POST['identifier'])) {
    $identifier = date('U');
} else {
    $identifier = $_POST['identifier'];
}
if (isset($_POST['Submit'])) {
    isset($_SESSION['num' . $identifier]) or die("no session");
    if ($_SESSION['num' . $identifier] == 400) {
        $_SESSION['num' . $identifier] = 500;
        $InputError = 0;
        $checkQty = 0;
        $count = 0;
//        $ErrPO_SEGMENT1='';
        DB_Txn_Begin($db);
        $v_date = strtotime(Date('Y-m-d H:i:s'));
        $date = date('Ymd');
        $sql_num = "select 	(
		CASE WHEN substr(max(trans_num) ,-2,1) = 0 THEN
			RIGHT (
				'100' + (
					max(substr(trans_num ,- 1)) + 1
				),
				2
			)
		ELSE
			substr(max(trans_num),-2,2) + 1
		END
        ) receipt_num from inv_transactions_all where substr(trans_num,-10,8) = '" . $date . "'
		and transaction_type='WIPRETURN' ";
        $result_num = DB_query($sql_num, $db);
        $rownum = DB_num_rows($result_num);
//    echo $rownum;
        while ($v = DB_fetch_array($result_num)) {
            if ($v['receipt_num'] == null) {
                $OrderNum ='WT'. $date . '01';
            } else {
                $OrderNum ='WT'.  $date . $v['receipt_num'];
            }
        }


        foreach ($_POST as $key => $value) {
            if (mb_substr($key, 0, 6) == 'status') {
                $count = $count + 1;
                $WIP_ENTITY_NAME_SEGMENT1 = mb_substr($key, 6);
                $n = strpos($WIP_ENTITY_NAME_SEGMENT1, '_');
                if ($n) {
                    $WIP_ENTITY_NAME = substr($WIP_ENTITY_NAME_SEGMENT1, 0, $n);
                    $SEGMENT1 = substr($WIP_ENTITY_NAME_SEGMENT1, $n + 1);
//                echo $WIP_ENTITY_NAME;
//                echo $SEGMENT1; 
                    $v_transaction_quantity = $_POST['receive' . $WIP_ENTITY_NAME_SEGMENT1];
					$transaction_quantity = $v_transaction_quantity;
                    $rcv_Qty = $_POST['quantity_wait' . $WIP_ENTITY_NAME_SEGMENT1];
//                echo $Qty;
//                echo $receive_qty;
                    $OPERATION_SEQ_NUM = $_POST['OPERATION_SEQ_NUM' . $WIP_ENTITY_NAME_SEGMENT1];
 
                    $locname=$_POST['locName' . $WIP_ENTITY_NAME_SEGMENT1];
                   
                    if ($transaction_quantity != '') {

						$sqlinsertinv = "insert into inv_onhand_quantity_all(stockid,quantity,subinventory_code,last_update_date,last_updated_by,creation_date,created_by) values('" . $SEGMENT1 . "','" . $v_transaction_quantity . "','" . $locname . "','" . $v_date . "','" . $_SESSION['UserID'] . "','" . $v_date . "','" . $_SESSION['UserID'] . "')";
                         $result_inv = DB_query($sqlinsertinv, $db);
                 
                        $sql1 = "UPDATE wip_material_requierments set 
                             QUANTITY_ISSUED=ifnull(QUANTITY_ISSUED,0)-" . $transaction_quantity . ",
                             last_update_date='" . $v_date . "' ,
						     last_updated_by='" . $_SESSION['UserID'] . "' 
							 where  WIP_ENTITY_NAME='" . $WIP_ENTITY_NAME . "'  
							 and  SEGMENT1='" . $SEGMENT1 . "'
							 and  OPERATION_SEQ_NUM='" . $OPERATION_SEQ_NUM . "'  ";
                        $result = DB_query($sql1, $db);
               

                        $sqltrancsation = "insert into  inv_transactions_all(trans_num,subinventory_from,WIP_ENTITY_NAME,item,OPERATION_SEQ_NUM,transaction_type,transaction_date,quantity,creation_date,created_by,last_updated_by,last_update_date) ";
                        $sqltrancsation.="values( '" . $OrderNum ."','" . $locname . "','" . $WIP_ENTITY_NAME . "','" . $SEGMENT1 . "','" . $OPERATION_SEQ_NUM . "','WIPRETURN','" . $v_date . "','" . $transaction_quantity . "','" . $v_date . "','" . $_SESSION['UserID'] . "','" . $_SESSION['UserID'] . "','" . $v_date . "')";
                     
                        $result_trancsation = DB_query($sqltrancsation, $db);
                        unset($sql1);
                        unset($result);
                        unset($sqlinsertrcvSEGMENT1);
                        unset($result_SEGMENT1);
                        unset($sqltrancsation);
                        unset($result_trancsation);
                    } else {
                        $InputError = 1;
                    }
                }
            }
        }
        if ($count != 0) {
             if ($InputError == 1) {
                $msg = '没有选择退料行！';
                prnMsg($msg, 'error');
            } else {
                DB_Txn_Commit($db);
                $msg = '工单退料成功！领料单号：' . $OrderNum;
                prnMsg($msg, 'success');
                 echo '<br /><div class="centre"><a href="' . $RootPath . '/WIPMaterialOutSub.php">' . _('继续工单退料作业') . '</a></div>';
// echo '<meta http-equiv="refresh" content="0.3" url=RequestReceive.php"/>';
                unset($sql1);
                unset($sql_num);
                unset($result_num);
                unset($sqlinsertrcv);
                unset($rownum);
                unset($result_header);
            }
        } else {
            $msg = '请选中更改项';
            prnMsg($msg, 'error');
        }
    }
} else {
    session_start() or die("session is not started");
    $_SESSION['num' . $identifier] = 400;
}


echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . '工单退料' .
 '" alt="" />' . ' ' . $Title . '
	</p>';

if (isset($UpdateWIP_ENTITY_NAME) and $UpdateWIP_ENTITY_NAME != '') {
    $sql = "select 
a.WIP_ENTITY_NAME,
a.SEGMENT1,
a.OPERATION_SEQ_NUM,
b.item_desc,
a.DATE_REQUIRED,
a.REQUIRED_QUANTITY,
ifnull(a.QUANTITY_PER_ASSEMBLY,0) QUANTITY_PER_ASSEMBLY,
ifnull(a.QUANTITY_ISSUED,0) QUANTITY_ISSUED, 
ifnull(a.QUANTITY_ISSUED,0)   quantity_wait,
a.DATE_REQUIRED
FROM  wip_material_requierments a, 
      sf_item_no b 
WHERE   a.segment1=b.item_no   
      ";
    $sql .= " AND a.WIP_ENTITY_NAME= '" . $UpdateWIP_ENTITY_NAME . "' ";
    $sql .= " ORDER BY a.WIP_ENTITY_NAME,a.SEGMENT1";
    $TransResult = DB_query($sql, $db);

    $ErrMsg = _('采购单查询错误，请查看所选订单') . ' - ' . DB_error_msg($db);
    $DbgMsg = _('The SQL that failed was');
    if (DB_num_rows($TransResult) == 0) {
        unset($TransResult);
        prnMsg(_('没有找到需要来料报检的采购单，请重新输入条件查询！'), 'info');
    } else {
        echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '"><input type="hidden" name = "identifier" value ="' . $identifier . '">';
        echo '<div>';
         
//echo '<div style="width:1300px;height:400px;overflow-x: hidden; overflow-y: scroll;">';
        echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
        echo '<table class="selection" align="center" >';
        $tableheader = '

    
<tr>
	                             <th width =40>' . '确认' . '</th>
                                        <th width =100 >' . '工单名称' . '</th>
	                                <th width =150>' . '料号' . '</th>
                                        <th width =190>' . '料号描述' . '</th>
                                        <th width =50>' . '制程' . '</th>
                                            <th width =90>' . '需求日期' . '</th>
											 <th  width = 80>' . '单位用量' . '</th> 
                                         <th  width = 80>' . '需求量' . '</th> 
                                             <th  width = 80>' . '已领量' . '</th> 
                                         <th  width = 80>' . '可退量' . '</th> 
                                         <th width =80 >' . '本次退量' . '</th>
                                         <th width =120 >' . '仓库' . '</th>                                        
                                       
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
//
            echo '<td><input type="checkbox" name="status' . $myrow['WIP_ENTITY_NAME'] . '_' . $myrow['SEGMENT1'] . '" /></td>';          
            echo '<td><font color="red">' . $myrow['WIP_ENTITY_NAME'] . '</font></td>';
			echo '<td>' . $myrow['SEGMENT1'] . ' </td>';
			echo '<td>' . $myrow['item_desc'] . ' </td>';
			echo '<td>' . $myrow['OPERATION_SEQ_NUM'] . ' </td>';
            echo '<td>' . date('Y-m-d', $myrow['DATE_REQUIRED']). ' </td>';  
			echo '<td>' . round($myrow['QUANTITY_PER_ASSEMBLY'],4) . ' </td>';
            echo '<td>' . $myrow['REQUIRED_QUANTITY'] . ' </td>';
             echo '<td>' . $myrow['QUANTITY_ISSUED'] . ' </td>';
            echo '<td>' . $myrow['quantity_wait'] . ' </td>'; 
            echo'      
            <td><input type="text" class="number"   name="receive' . $myrow['WIP_ENTITY_NAME'] . '_' . $myrow['SEGMENT1'] . '" size="5" maxlength="25"  value="' . $myrow['quantity_wait'] . '" /></td>';
//                echo '<td>' . $myrow['subinventory_code'] . ' </td>';
            $_POST['locName']=$myrow['subinventory_code'];
            $sql1 = "SELECT loccode,locationname FROM locations ORDER by loccode";
            $result1 = DB_query($sql1, $db);
            echo '<td><select name="locName' . $myrow['WIP_ENTITY_NAME'] . '_' . $myrow['SEGMENT1'] . '">';
            while ($Salesmanrow = DB_fetch_array($result1)) {
                if (isset($_POST['locName']) AND $_POST['locName'] == $Salesmanrow['loccode']) {
                    echo '<option  selected="selected" value="' . $Salesmanrow['loccode'] . '">' .  $Salesmanrow['locationname'] . '</option>';
                } else {
                    echo '<option value="' . $Salesmanrow['loccode'] . '">' . $Salesmanrow['locationname'] . '</option>';
                }
            }
            echo '</select></td>';
//            echo' <td width =100>' . date('Y-m-d', $myrow['DATE_REQUIRED']) . ' </td>';
            echo'<td><input type="hidden"  name="quantity_wait' . $myrow['WIP_ENTITY_NAME'] . '_' . $myrow['SEGMENT1'] . '" value="' . $myrow['quantity_wait'] . '"  /></td>';
            echo'<td><input type="hidden"  name="SEGMENT1' . $myrow['WIP_ENTITY_NAME'] . '_' . $myrow['SEGMENT1'] . '" value="' . $myrow['SEGMENT1'] . '"  /></td>';

            echo'<td><input type="hidden"  name="OPERATION_SEQ_NUM' . $myrow['WIP_ENTITY_NAME'] . '_' . $myrow['SEGMENT1'] . '" value="' . $myrow['OPERATION_SEQ_NUM'] . '"  /></td>';
            echo '</tr>';

            $RowCounter++;
            If ($RowCounter == 500) {
                $RowCounter = 1;
                echo $tableheader;
            }
        }
        echo '<tr><td colspan="11"><p><input type="checkbox" name="selectall" onclick="checkall(this.form);"/>全选/取消全选</p></td></tr>';
        echo '</table> ';


        echo '</div>';
        echo '<div class="centre">
			<input type="submit" name="Submit" value="' . _('Enter Information') . '" />
                             <input type="submit" name="return" value="' . "返回上一层" . '" />
		</div>
           

          </form>';
       
                               
        
    }
}
if (isset($_POST['return'])) {
//    echo 'AAAAAAAAAA';
    header('Location: WIPMaterialOutSub.php');
}


echo '<script language="javascript" type="text/javascript">';
echo 'function checkall(thisform){for(var i=0;i<thisform.elements.length;i++){if(thisform.elements[i].type=="checkbox"&&thisform.elements[i].checked==false&&thisform.elements[i].name!="selectall"){thisform.elements[i].checked=true;}else if(thisform.elements[i].type=="checkbox"&&thisform.elements[i].checked==true&&thisform.elements[i].name!="selectall"){thisform.elements[i].checked=false;}} }';
echo '</script>';


//}
include('includes/footer.inc');
?>