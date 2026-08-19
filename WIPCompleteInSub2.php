<?php
/* $Id: SupplierTransInquiry.php 5785 2012-12-29 04:47:42Z daintree $ */

include ('includes/session.inc');
$Title = '工单完工成品入库处理';
include ('includes/header.inc');
if (isset($_GET['UpdateWIP_ENTITY_NAME'])) {
    $updatewip_entity_name = $_GET['UpdateWIP_ENTITY_NAME'];
} else {
    $updatewip_entity_name = '';
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
        //        $ErrPO_PRIMARY_ITEM='';
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
        ) receipt_num from inv_transactions_all where substr(trans_num,-10,8) = '" .
            $date . "'
		and transaction_type='WIPCOMLETE' ";
        $result_num = DB_query($sql_num, $db);
        $rownum = DB_num_rows($result_num);
        //    echo $rownum;
        while ($v = DB_fetch_array($result_num)) {
            if ($v['receipt_num'] == null) {
                $OrderNum = 'WR' . $date . '01';
            } else {
                $OrderNum = 'WR' . $date . $v['receipt_num'];
            }
        }


        foreach ($_POST as $key => $value) {
            if (mb_substr($key, 0, 6) == 'status') {
                $count = $count + 1;
                $wip_entity_name_primary_item = mb_substr($key, 6);
                $n = strpos($wip_entity_name_primary_item, '_');
                if ($n) {
                    $wip_entity_name = substr($wip_entity_name_primary_item, 0, $n);
                    $primary_item = substr($wip_entity_name_primary_item, $n + 1);
                    
                    $transaction_quantity = $_POST['receive' . $wip_entity_name_primary_item];
                    $quantity_completed = $myrow['quantity_completed'];
                    $start_quantity = $myrow['start_quantity'];
                    $rcv_qty = $_POST['quantity_wait' . $wip_entity_name_primary_item];
                    //                echo $Qty;
                    //                echo $receive_qty;

                    $locname = $_POST['locname' . $wip_entity_name_primary_item];

                    if ($transaction_quantity != '' && $quantity_completed + $transaction_quantity <=
                        $start_quantity) {

                        $sqlinsertinv = "insert into inv_onhand_quantity_all(stockid,quantity,subinventory_code,last_update_date,last_updated_by,creation_date,created_by) values('" .
                            $primary_item . "','" . $transaction_quantity . "','" . $locname . "','" . $v_date .
                            "','" . $_SESSION['UserID'] . "','" . $v_date . "','" . $_SESSION['UserID'] .
                            "')";
                        $result_inv = DB_query($sqlinsertinv, $db);

                        $sql1 = "UPDATE wip_jobs_all set 
                             quantity_completed=ifnull(quantity_completed,0)+" . $transaction_quantity . ",
                             last_update_date='" . $v_date . "' ,
						     last_updated_by='" . $_SESSION['UserID'] . "' 
							 where  wip_entity_name='" . $wip_entity_name . "'   ";
                        $result = DB_query($sql1, $db);


                        $sqltrancsation = "insert into  inv_transactions_all(trans_num,subinventory_from,wip_entity_name,item,transaction_type,transaction_date,quantity,creation_date,created_by,last_updated_by,last_update_date) ";
                        $sqltrancsation .= "values( '" . $ordernum . "','" . $locname . "','" . $wip_entity_name .
                            "','" . $primary_item . "','wipcomlete','" . $v_date . "','" . $transaction_quantity .
                            "','" . $v_date . "','" . $_SESSION['UserID'] . "','" . $_SESSION['UserID'] .
                            "','" . $v_date . "')";

                        $result_trancsation = DB_query($sqltrancsation, $db);
                        unset($sql1);
                        unset($result);
                        unset($sqlinsertrcvprimary_item);
                        unset($result_primary_item);
                        unset($sqltrancsation);
                        unset($result_trancsation);
                    } else
                        if ($QUANTITY_COMPLETED + $transaction_quantity >= $start_quantity) {
                            $InputError = 2;
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
            } else
                if ($InputError == 2) {
                    $msg = '超出可退量';
                    prnMsg($msg, 'error');
                } else {
                    DB_Txn_Commit($db);
                    $msg = '工单成品入库成功！入库单号：' . $OrderNum;
                    prnMsg($msg, 'success');
                    echo '<br /><div class="centre"><a href="' . $RootPath .
                        '/WIPCompleteInSub.php">' . _('继续其它工单成品入库处理') . '</a></div>';
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
		<img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' .
    '工单完工成品入库处理' . '" alt="" />' . ' ' . $Title . '
	</p>';

if (isset($updatewip_entity_name) and $updatewip_entity_name != '') {
    $sql = "select  a.primary_item ,wip_entity_name,a.status_type,start_quantity,plan_start_date,
	plan_end_date,a.creation_date,b.item_desc,a.so_header_number,a.quantity_completed,a.start_quantity-a.quantity_completed quantity_wait
				from wip_jobs_all a,sf_item_no b
where   a.status_type in ('开始') 
				and a.primary_item=b.item_no   ";
    $sql .= " and a.wip_entity_name= '" . $updatewip_entity_name . "' ";
	//echo $sql;
    $TransResult = DB_query($sql, $db);

    $ErrMsg = _('该工单不存在已产出未入库资料，请重新输入条件查询！') . ' - ' . DB_error_msg($db);
    $DbgMsg = _('The SQL that failed was');
    if (DB_num_rows($TransResult) == 0) {
        unset($TransResult);
        prnMsg(_('该工单不存在已产出未入库资料，请重新输入条件查询！'), 'info');
    } else {
        echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],
            ENT_QUOTES, 'UTF-8') . '"><input type="hidden" name = "identifier" value ="' . $identifier .
            '">';
        echo '<div>';

        //echo '<div style="width:1300px;height:400px;overflow-x: hidden; overflow-y: scroll;">';
        echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
        echo '<table class="selection" align="center" >';
        $tableheader = '<tr> <th width =50>' . '确认' . '</th>
		                       <th width =100 >' . '工单名称' . '</th>
	                                <th width =100>' . '料号' . '</th>
                                        <th width =100>' . '料号描述' . '</th>
											 <th  width = 80>' . '开工数量' . '</th>  
                                             <th  width = 100>' . '已入库量' .
            '</th> 
                                         <th  width = 100>' . '待入库量' . '</th> 
                                         <th width =120 >' . '本次入库量' . '</th>
                                         <th width =120 >' . '仓库' .
            '</th>                                        
                                       
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

            echo '<td><input type="checkbox" name="status' . $myrow['wip_entity_name'] . '_' .
                $myrow['primary_item'] . '" /></td>';
            echo '<td><font color="red">' . $myrow['wip_entity_name'] . '</font></td>';
            echo '<td>' . $myrow['primary_item'] . ' </td>';
            echo '<td>' . $myrow['item_desc'] . ' </td>';
            echo '<td>' . $myrow['start_quantity'] . ' </td>'; 
            echo '<td>' . $myrow['quantity_completed'] . ' </td>';
            echo '<td>' . $myrow['quantity_wait'] . ' </td>';
            echo '      
            <td><input type="text" class="number"   name="receive' . $myrow['wip_entity_name'] .
                '_' . $myrow['primary_item'] . '" size="12" maxlength="25"  value="' . $myrow['quantity_wait'] .
                '" /></td>';
             
            $_POST['locname'] = $myrow['subinventory_code'];
            $sql1 = "select loccode,locationname from locations order by loccode";
            $result1 = db_query($sql1, $db);
            echo '<td><select name="locname' . $myrow['wip_entity_name'] . '_' . $myrow['primary_item'] .
                '">';
            while ($salesmanrow = db_fetch_array($result1)) {
                if (isset($_POST['locname']) and $_POST['locname'] == $salesmanrow['loccode']) {
                    echo '<option  selected="selected" value="' . $salesmanrow['loccode'] . '">' . $salesmanrow['locationname'] .
                        '</option>';
                } else {
                    echo '<option value="' . $salesmanrow['loccode'] . '">' . $salesmanrow['locationname'] .
                        '</option>';
                }
            }
            echo '</select></td>';
            //            echo' <td width =100>' . date('y-m-d', $myrow['date_required']) . ' </td>';
            echo '<td><input type="hidden"  name="quantity_wait' . $myrow['wip_entity_name'] .
                '_' . $myrow['primary_item'] . '" value="' . $myrow['quantity_wait'] .
                '"  /></td>';
            echo '<td><input type="hidden"  name="primary_item' . $myrow['wip_entity_name'] .
                '_' . $myrow['primary_item'] . '" value="' . $myrow['primary_item'] .
                '"  /></td>';

            echo '<td><input type="hidden"  name="operation_seq_num' . $myrow['wip_entity_name'] .
                '_' . $myrow['primary_item'] . '" value="' . $myrow['operation_seq_num'] .
                '"  /></td>';
            echo '</tr>';

            $RowCounter++;
            if ($RowCounter == 500) {
                $RowCounter = 1;
                echo $tableheader;
            }
        }

        echo '</table> ';


        echo '</div>';
        echo '<div class="centre">
			<input type="submit" name="Submit" value="' . _('Enter Information') . '" />
                             <input type="submit" name="return" value="' .
            "返回上一层" . '" />
		</div>
           

          </form>';


    }
}
if (isset($_POST['return'])) {
    //    echo 'AAAAAAAAAA';
    header('Location: WIPCompleteInSub.php');
}


//}
include ('includes/footer.inc');
?>