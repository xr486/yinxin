<?php
/* $Id: SupplierTransInquiry.php 5785 2012-12-29 04:47:42Z daintree $ */

include('includes/session.inc');
$Title = '仓库调拨';
include('includes/header.inc');
if (isset($_GET['item_no'])) {
    $item_no = $_GET['item_no'];
} else {
    $item_no = '';
}
if (isset($_GET['code'])) {
    $code = $_GET['code'];
} else {
    $code = "";
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
//    echo 11111;
    $InputError = 0;

//    if (trim($_POST['item']) == '') {
//        $InputError = 1;
//        prnMsg('料号不能为空', 'error');
//    }
    if (trim($_POST['locNameNext']) == trim($_POST['locName'])) {
        $InputError = 1;
        prnMsg('原仓库和调拨仓库不能为同一仓库', 'error');
    }
//    echo trim($_POST['locNameNext']);
//    echo trim($_POST['locName']);
    if (trim($_POST['qty']) == '') {
        $InputError = 1;
        prnMsg('数量不能为空', 'error');
    }
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
        ) trans_num from inv_transactions_all where substr(trans_num,-10,8) = '" . $date . "'";
    $result_num = DB_query($sql_num, $db);

    while ($v = DB_fetch_array($result_num)) {
        if ($v['trans_num'] == null) {
            $TransNum = 'TR' . $date . '01';
        } else {
            $TransNum = 'TR' . $date . $v['trans_num'];
        }
    }
    if ($InputError != 1) {
        DB_Txn_Begin($db);
        $sqlinv = "select ifnull(sum(ifnull(quantity,0)),0) Qty_sum from inv_onhand_quantity_all where stockid='" . trim($_POST['item']) . "' and subinventory_code ='" . trim($_POST['locName']) . "'";
        $resultsum = DB_query($sqlinv, $db);
        while ($v = DB_fetch_array($resultsum)) {
            $sum = $v['Qty_sum'];
        }
        if ($sum < $_POST['qty']) {
            prnMsg('库存数量不足', 'error');
        } else {
            $change_date = strtotime(Date('Y-m-d H:i:s'));
            $temp = $_POST['qty'];
            $sqlsubcode = "select id,stockid,quantity from inv_onhand_quantity_all where stockid='" . trim($_POST['item']) . "' and subinventory_code ='" . trim($_POST['locName']) . "'";
            $result_subcode = DB_query($sqlsubcode, $db);
            while ($v = DB_fetch_array($result_subcode)) {
                if ($temp > 0) {
                    if ($v['quantity'] <= $temp) {
                        $UpdateSubCode = "delete from  inv_onhand_quantity_all where id=" . $v['id'] . "";
//                echo $UpdateSubCode;
                        $result_updatesubcode = DB_query($UpdateSubCode, $db);
                        unset($UpdateSubCode);
                        $temp = $temp - $v['quantity'];
                    } else {
                        $UpdateSubCode1 = "Update inv_onhand_quantity_all set quantity=quantity-" . $temp . " where id=" . $v['id'] . "";
//                            echo $UpdateSubCode1;
                        $result_updatesubcode1 = DB_query($UpdateSubCode1, $db);
                        $temp = 0;
                    }
                }
            }
            $sqlinsertinv = "insert into inv_onhand_quantity_all(stockid,quantity,subinventory_code,creation_date,created_by) values('" . $_POST['item'] . "','" . $_POST['qty'] . "','" . trim($_POST['locNameNext']) . "','" . $change_date . "','" . $_SESSION['UserID'] . "')";
            $result_inv = DB_query($sqlinsertinv, $db);

            $sqlinvtrancsation = "insert into inv_transactions_all(transaction_type,quantity,item,subinventory,creation_date,create_by,trans_num) ";
            $sqlinvtrancsation.="values('SUBTRANSFER', '" . $_POST['qty'] . "','" . $_POST['item'] . "','" . trim($_POST['locNameNext']) . "','" . $change_date . "','" . $_SESSION['UserID'] . "','" . $TransNum . "')";
            $result_invtrancsation = DB_query($sqlinvtrancsation, $db);

            $sqlinvtrancsation1 = "insert into inv_transactions_all(transaction_type,quantity,item,subinventory,creation_date,create_by,trans_num) ";
            $sqlinvtrancsation1.="values('SUBTRANSFER', '-" . $_POST['qty'] . "','" . $_POST['item'] . "','" . trim($_POST['locName']) . "','" . $change_date . "','" . $_SESSION['UserID'] . "','" . $TransNum . "')";
            $result_invtrancsation1 = DB_query($sqlinvtrancsation1, $db);
            if ($temp == 0) {
                DB_Txn_Commit($db);
                $msg = '调拨成功！';
                prnMsg($msg, 'success');
                unset($sql1);
                unset($sqlsubcode);
                unset($result_subcode);
                unset($UpdateSubCode);
                unset($result_updatesubcode);
                unset($UpdateSubCode1);
                unset($result_updatesubcode1);
                unset($sqlinsertinv);
                unset($result_inv);
                unset($sqlinvtrancsation);
                unset($result_invtrancsation);
                unset($sqlinvtrancsation1);
                unset($result_invtrancsation1);
            } else {
                
            }
        }
    }
}


echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . '仓库调拨' .
 '" alt="" />' . ' ' . $Title . '
	</p>';
if (isset($item_no) and $item_no != '') {
    echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post"><input type="hidden" name = "identifier" value ="' . $identifier . '">';
    echo '<div>';
    echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
// <td>' . _('Type') . ':</td> 20

  $sql = "select  sum(quantity) quantity 
	     from  inv_onhand_quantity_all "; 
    $sql = $sql . " where subinventory_code=  '" . $code . "'";
	$sql = $sql . " and stockid=  '" . $item_no . "'";
    $result = DB_query($sql,$db);
	while ($Myrow = DB_fetch_array($result)) {
		$OnhandQty= $Myrow['quantity'] ; 
   }

    echo '<table class="selection">
		<tr>	
                         <td>' . '料号' . ':</td>
			<td>' . $item_no . '</td>'
    . '<td>' . '原仓库' . ':</td><td>' . $code . '</td><td>' . '库存量' . ':</td><td>' . $OnhandQty . '</td>';

    echo '<input type="hidden" name="item" value="' . $item_no . '" />';
    echo '<input type="hidden" name="locName" value="' . $code . '" />';

	 

//    $sql = "SELECT locationname FROM locations ORDER by loccode";
//    $result = DB_query($sql, $db);
//    echo '<td><select name="locName">';
//    while ($Salesmanrow = DB_fetch_array($result)) {
//        if (isset($_POST['locName']) AND $_POST['locName'] == $Salesmanrow['locationname']) {
//            echo '<option  selected="selected" value="' . $Salesmanrow['locationname'] . '">' . $Salesmanrow['locationname'] . '</option>';
//        } else {
//            echo '<option value="' . $Salesmanrow['locationname'] . '">' . $Salesmanrow['locationname'] . '</option>';
//        }
//    }
//
//    echo '</select></td>'
    
    echo '<td>' . '调拨仓库' . ':</td>';
    $sql1 = "SELECT locationname FROM locations ORDER by loccode";
    $result1 = DB_query($sql1, $db);
    echo '<td><select name="locNameNext">';
    while ($Salesmanrow1 = DB_fetch_array($result1)) {
        if (isset($_POST['locNameNext']) AND $_POST['locNameNext'] == $Salesmanrow1['locationname']) {
              echo '<option  selected="selected" value="' . $Salesmanrow['locationname'] . '">' . $Salesmanrow['locationname'] . '</option>';
        } else {
            echo '<option value="' . $Salesmanrow1['locationname'] . '">' . $Salesmanrow1['locationname'] . '</option>';
        }
    }

    echo '</select></td>'
    . '<td>' . '调拨数量' . ':</td>
			<td><input   type="text" name="qty" value="' . $_POST['qty'] . '" /></td>'
    . '</tr><tr>';



    /* if (!isset($_POST['FromDate'])) {
      $_POST['FromDate'] = Date($_SESSION['DefaultDateFormat'], mktime(0, 0, 0, Date('m'), -30, Date('Y')));
      }
      if (!isset($_POST['ToDate'])) {
      $_POST['ToDate'] = Date($_SESSION['DefaultDateFormat']);
      }
      echo '<td>' . _('From') . ':</td>
      <td><input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="FromDate" maxlength="10" size="11" value="' . $_POST['FromDate'] . '" /></td>
      <td>' . _('To') . ':</td>
      <td><input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="ToDate" maxlength="10" size="11" value="' . $_POST['ToDate'] . '" /></td>';
     */
    echo '	</tr>
	</table>
	<br />
	<div class="centre">
		<input type="submit" name="Submit" value="' . _('Enter Information') . '" />
                    <input type="submit" name="return" value="返回上一层" />
	</div>
	<br />
    </div>
	</form>';
}
if (isset($_POST['return'])) {
//    echo 'AAAAAAAAAA';
    header('Location: InchangeHouse.php');
}
include('includes/footer.inc');
?>