<?php
/* $Id: SupplierTransInquiry.php 5785 2012-12-29 04:47:42Z daintree $ */

include('includes/session.inc');
$Title = '工单领料';
include('includes/header.inc');
if (isset($_GET['UpdateWIP_ENTITY_NAME'])) {
    $UpdateWIP_ENTITY_NAME = $_GET['UpdateWIP_ENTITY_NAME'];
} else {
    $UpdateWIP_ENTITY_NAME = '';
}
if (isset($_GET['subcode'])) {
    $subcode = $_GET['subcode'];
} else {
    $subcode = '';
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
		$errorflag = 0;
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
		and transaction_type='WIPISSUE' ";
        $result_num = DB_query($sql_num, $db);
        $rownum = DB_num_rows($result_num);
//    echo $rownum;
        while ($v = DB_fetch_array($result_num)) {
            if ($v['receipt_num'] == null) {
                $OrderNum ='WF'. $date . '01';
            } else {
                $OrderNum ='WF'.  $date . $v['receipt_num'];
            }
        }
        
        
         foreach ($_POST as $key => $value) {
            if (mb_substr($key, 0, 6) == 'status') {
                $count = $count + 1;
                $OPERATION_SEQ_NUM_SEGMENT1 = mb_substr($key, 6);
                $n = strpos($OPERATION_SEQ_NUM_SEGMENT1, '_');
                if ($n) {
                    $OPERATION_SEQ_NUM = substr($OPERATION_SEQ_NUM_SEGMENT1, 0, $n);
                    $SEGMENT1 = substr($OPERATION_SEQ_NUM_SEGMENT1, $n + 1);

                    $transaction_quantity = $_POST['receive' . $OPERATION_SEQ_NUM_SEGMENT1];
                    $rcv_Qty = $_POST['quantity_wait' . $OPERATION_SEQ_NUM_SEGMENT1];
		            $onhand_qty = $_POST['onhand_qty' . $OPERATION_SEQ_NUM_SEGMENT1];
                    $WIP_ENTITY_NAME = $_POST['WIP_ENTITY_NAME' . $OPERATION_SEQ_NUM_SEGMENT1];

                    $loccode=$_POST['loccode' . $OPERATION_SEQ_NUM_SEGMENT1];
                    if ($onhand_qty < $transaction_quantity) {
                         $errorflag = 1;
			        prnMsg($value.'领料数量'.$transaction_quantity.'大于库存量'.$onhand_qty.'，请确认！',error);
		   }		
                }
            }
        }


        foreach ($_POST as $key => $value) {
            if (mb_substr($key, 0, 6) == 'status' and $errorflag ==0) {
                $count = $count + 1;
                $OPERATION_SEQ_NUM_SEGMENT1 = mb_substr($key, 6);
                $n = strpos($OPERATION_SEQ_NUM_SEGMENT1, '_');
                if ($n) {
                    $OPERATION_SEQ_NUM = substr($OPERATION_SEQ_NUM_SEGMENT1, 0, $n);
                    $SEGMENT1 = substr($OPERATION_SEQ_NUM_SEGMENT1, $n + 1);

                    $transaction_quantity = $_POST['receive' . $OPERATION_SEQ_NUM_SEGMENT1];
                    $rcv_Qty = $_POST['quantity_wait' . $OPERATION_SEQ_NUM_SEGMENT1];
					$onhand_qty = $_POST['onhand_qty' . $OPERATION_SEQ_NUM_SEGMENT1];
                    $WIP_ENTITY_NAME = $_POST['WIP_ENTITY_NAME' . $OPERATION_SEQ_NUM_SEGMENT1];

                    $loccode=$_POST['loccode' . $OPERATION_SEQ_NUM_SEGMENT1];
                    //if ($transaction_quantity > $rcv_Qty) {
                   //     $checkQty = 1;
//                    $ErrPO_SEGMENT1=$ErrPO_SEGMENT1.$WIP_ENTITY_NAME_SEGMENT1+',';
                  //  }

				  
                    if ($transaction_quantity != '' && $checkQty != 1) {

						$sqlsubqty = "select sum(quantity) quantity
						from inv_onhand_quantity_all where stockid='" .$SEGMENT1. "' and subinventory_code ='" . $loccode  . "'";
          			  $result_subqty = DB_query($sqlsubqty, $db);
						while ($v = DB_fetch_array($result_subqty)) {
							$v_onhand_qty=  $v['quantity'];
						}


						if ($v_onhand_qty < $transaction_quantity) {
                            $errorflag = 1;
							prnMsg($value.'仓库领料数量'.$_POST['quantity'.$i].'大于库存量'.$v_onhand_qty.'，请确认！',error);
			           }

					$temp = $transaction_quantity;
         		   $sqlsubcode = "select id,stockid,quantity from inv_onhand_quantity_all where stockid='" .$SEGMENT1. "' and subinventory_code ='".$loccode . "'";
          		  $result_subcode = DB_query($sqlsubcode, $db);
          		  while ($v = DB_fetch_array($result_subcode)) {
                 if ($temp > 0) {
                    if ($v['quantity'] <= $temp) {
                        $UpdateSubCode = "delete from  inv_onhand_quantity_all where id=" . $v['id'] . "";
                        $result_updatesubcode = DB_query($UpdateSubCode, $db);
                        unset($UpdateSubCode);
                        $temp = $temp - $v['quantity'];
                    } else {
                        $UpdateSubCode1 = "Update inv_onhand_quantity_all set quantity=quantity-" . $temp . " where id=" . $v['id'] . "";

                        $result_updatesubcode1 = DB_query($UpdateSubCode1, $db);
                        $temp = 0;
                    }
                }
            }
                 
                        $sql1 = "UPDATE wip_material_requierments set 
                             QUANTITY_ISSUED=ifnull(QUANTITY_ISSUED,0)+" . $transaction_quantity . ",
                             last_update_date='" . $v_date . "' ,
						     last_updated_by='" . $_SESSION['UserID'] . "' 
							 where  WIP_ENTITY_NAME='" . $WIP_ENTITY_NAME . "'  
							 and  SEGMENT1='" . $SEGMENT1 . "'
							 and  OPERATION_SEQ_NUM='" . $OPERATION_SEQ_NUM . "'  ";
                        $result = DB_query($sql1, $db);
               

                        $sqltrancsation = "insert into  inv_transactions_all(trans_num,subinventory_from,WIP_ENTITY_NAME,item,OPERATION_SEQ_NUM,transaction_type,transaction_date,quantity,creation_date,created_by,last_updated_by,last_update_date) ";
                        $sqltrancsation.="values( '" . $OrderNum ."','" . $loccode . "','" . $WIP_ENTITY_NAME . "','" . $SEGMENT1 . "','" . $OPERATION_SEQ_NUM . "','WIPISSUE','" . $v_date . "','" .'-'. $transaction_quantity . "','" . $v_date . "','" . $_SESSION['UserID'] . "','" . $_SESSION['UserID'] . "','" . $v_date . "')";
                     
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
        if ($count != 0   and  $errorflag ==0) {

             if ($InputError == 1 ) {
                $msg = '没有选择领料行，或者领料数量大于需求量！';
                prnMsg($msg, 'error');
            } else {
                DB_Txn_Commit($db);
                $msg = '工单领料成功！领料单号：' . $OrderNum;
                prnMsg($msg, 'success');
                 echo '<br /><div class="centre"><a href="' . $RootPath . '/WIPMaterialInSub.php">' . _('继续工单领料作业') . '</a></div>';
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
		<img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . '工单领料' .
 '" alt="" />' . ' ' . $Title . '
	</p>';

if (isset($UpdateWIP_ENTITY_NAME) and $UpdateWIP_ENTITY_NAME != ''  and isset($subcode) and $subcode != '' ) {

$sqlsub = "select  loccode,locationname 	
 FROM  locations a 
WHERE loccode= '".$subcode."'";
 $SubResult = DB_query($sqlsub, $db);
 if (DB_num_rows($SubResult) == 0) {
        unset($SubResult);
        prnMsg(_('没有默认发料仓库，请确认仓库设置！'), 'info');
    }

	if (DB_num_rows($SubResult) > 1) {
        unset($SubResult);
        prnMsg(_('默认发料仓库超过1个，请联系管理员修改设置！'), 'info');
    }

	while ($submyrow = DB_fetch_array($SubResult)) {
       $subloccode=$submyrow['loccode'];
	   $sublocname=$submyrow['locationname'];
	  }
 


    $sql = "select 
a.WIP_ENTITY_NAME,
a.SEGMENT1,
a.OPERATION_SEQ_NUM,
b.item_desc,
a.DATE_REQUIRED,
a.REQUIRED_QUANTITY,
ifnull(a.QUANTITY_PER_ASSEMBLY,0) QUANTITY_PER_ASSEMBLY,
ifnull(a.QUANTITY_ISSUED,0) QUANTITY_ISSUED, 
ifnull(a.REQUIRED_QUANTITY,0)-ifnull(a.QUANTITY_ISSUED,0)  quantity_wait,
a.DATE_REQUIRED,(select  sum(quantity)  from inv_onhand_quantity_all moq where moq.stockid=a.segment1
             and subinventory_code='".$subloccode."') onhand_qty
FROM  wip_material_requierments a, 
      sf_item_no b 
WHERE   a.segment1=b.item_no   

      ";
	    // and ifnull(a.REQUIRED_QUANTITY,0)-ifnull(a.QUANTITY_ISSUED,0)>0
    $sql .= " AND a.WIP_ENTITY_NAME= '" . $UpdateWIP_ENTITY_NAME . "' ";
    $sql .= " ORDER BY a.WIP_ENTITY_NAME,a.SEGMENT1";
	//echo $sql;
    $TransResult = DB_query($sql, $db);

    $ErrMsg = _('采购单查询错误，请查看所选订单') . ' - ' . DB_error_msg($db);
    $DbgMsg = _('The SQL that failed was');
    if (DB_num_rows($TransResult) == 0) {
        unset($TransResult);
        prnMsg(_('没有找到需要发料的材料，请重新输入条件查询！'), 'info');
    } else {
        echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '"><input type="hidden" name = "identifier" value ="' . $identifier . '">';
        echo '<div>';
         

        echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
        echo '<table class="selection" align="center" >';
        $tableheader = '

    
<tr>
	                             <th width =50>' . '确认' . '</th>
                                        <th width =100 >' . '工单名称' . '</th>
	                                <th width =100>' . '料号' . '</th>
                                        <th width =150>' . '料号描述' . '</th>
                                        <th width =40>' . '制程' . '</th>
                                            <th width =100>' . '需求日期' . '</th>
											 <th  width = 80>' . '单位用量' . '</th> 
                                         <th  width = 80>' . '需求数量' . '</th> 
                                             <th  width = 80>' . '已领量' . '</th> 
                                         <th  width = 80>' . '待领量' . '</th> 
										 <th  width = 100>' . '仓库' . '</th> 
										 <th  width = 100>' . '库存量' . '</th> 
                                         <th width =100 >' . '本次领料量' . '</th>                                   
                                       
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
            if ($myrow['onhand_qty'] >0 ) {
			 $onhand_qty=$myrow['onhand_qty'];
			} else {
			 $onhand_qty=0;
			} 

            echo '<td><input type="checkbox" name="status' . $myrow['OPERATION_SEQ_NUM'] . '_' . $myrow['SEGMENT1'] . '" /></td>';          
            echo '<td><font color="red">' . $myrow['WIP_ENTITY_NAME'] . '</font></td>';
			echo '<td>' . $myrow['SEGMENT1'] . ' </td>';
			echo '<td>' . $myrow['item_desc'] . ' </td>';
			echo '<td>' . $myrow['OPERATION_SEQ_NUM'] . ' </td>';
            echo '<td>' . date('Y-m-d', $myrow['DATE_REQUIRED']). ' </td>';  
			echo '<td>' . round($myrow['QUANTITY_PER_ASSEMBLY'],4) . ' </td>';
            echo '<td>' . $myrow['REQUIRED_QUANTITY'] . ' </td>';
             echo '<td>' . $myrow['QUANTITY_ISSUED'] . ' </td>';
            echo '<td>' . round($myrow['quantity_wait'],4) . ' </td>'; 
			echo '<td>' . $sublocname . ' </td>'; 			
			echo '<td>' . $onhand_qty . ' </td>'; 
 
            echo'      
            <td><input type="text" class="number"   name="receive' . $myrow['OPERATION_SEQ_NUM'] . '_' . $myrow['SEGMENT1'] . '" size="12" maxlength="25"  value="' . round($myrow['quantity_wait'],4) . '" /></td>';

          
			echo'<td><input type="hidden"  name="loccode' . $myrow['OPERATION_SEQ_NUM'] . '_' . $myrow['SEGMENT1'] . '" value="' . $subloccode . '"  />';
      
            echo'<input type="hidden"  name="quantity_wait' . $myrow['OPERATION_SEQ_NUM'] . '_' . $myrow['SEGMENT1'] . '" value="' . $myrow['quantity_wait'] . '"  />';
            echo'<input type="hidden"  name="SEGMENT1' . $myrow['OPERATION_SEQ_NUM'] . '_' . $myrow['SEGMENT1'] . '" value="' . $myrow['SEGMENT1'] . '"  />';
			 echo'<input type="hidden"  name="onhand_qty' . $myrow['OPERATION_SEQ_NUM'] . '_' . $myrow['SEGMENT1'] . '" value="' . $onhand_qty . '"  />';

            echo'<input type="hidden"  name="WIP_ENTITY_NAME' . $myrow['OPERATION_SEQ_NUM'] . '_' . $myrow['SEGMENT1'] . '" value="' . $myrow['WIP_ENTITY_NAME'] . '"  /></td>';
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
    header('Location: WIPMaterialInSub.php');
}


echo '<script language="javascript" type="text/javascript">';
echo 'function checkall(thisform){for(var i=0;i<thisform.elements.length;i++){if(thisform.elements[i].type=="checkbox"&&thisform.elements[i].checked==false&&thisform.elements[i].name!="selectall"){thisform.elements[i].checked=true;}else if(thisform.elements[i].type=="checkbox"&&thisform.elements[i].checked==true&&thisform.elements[i].name!="selectall"){thisform.elements[i].checked=false;}} }';
echo '</script>';


//}
include('includes/footer.inc');
?>