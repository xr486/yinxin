<?php
/*
* To change this license header, choose License Headers in Project Properties.
* To change this template file, choose Tools | Templates
* and open the template in the editor.
*/

include ('includes/DefinePOUpdateClass.php');

include ('includes/session.inc');

$Title = _('送货单修改');
$ViewTopic = '送货单修改';
$BookMark = '送货单修改';
include ('includes/header.inc');
include ('includes/SQL_CommonFunctions.inc');
$_SESSION['DisplayRecordsMax'] = 10;
if (isset($_GET['identifier'])) {
    $_POST['identifier'] = $_GET['identifier'];
}

if (!isset($_POST['identifier'])) {
    $identifier = date('U');
} else {
    $identifier = $_POST['identifier'];
}

if (isset($_GET['New'])) {

    unset($_SESSION['Contract' . $identifier]);
    $_SESSION['Contract' . $identifier] = new ReceiveRequest();
    if (isset($_GET['Updatedelivery_num'])) {
        if (!isset($_SESSION['Contract' . $identifier]->delivery_num) or $_SESSION['Contract' .
            $identifier]->delivery_num == '') {
            $delivery_num = $_GET['Updatedelivery_num'];
            $sql = "SELECT
     b.customer_code,
    b.customer_name, 
    p.delivery_num,
    p.status,
    p.narrative, 
    b.customer_name,
    p.delivery_amount,
    p.delivery_date,
    
    p.creation_date,
    p.created_by,
    p.last_update_date,
    p.last_updated_by 
 FROM so_delivery_headers_all p,customers b 
WHERE b.customer_code = p.customer_code
and p.delivery_num = '". $delivery_num. "'";
 
            $CustResult = DB_query($sql, $db);
            while ($myrow = DB_fetch_array($CustResult)) {
                $_SESSION['Contract' . $identifier]->delivery_num = $myrow['delivery_num'];
                $_SESSION['Contract' . $identifier]->status = $myrow['status'];
                $_SESSION['Contract' . $identifier]->delivery_date = $myrow['delivery_date'];
                $_SESSION['Contract' . $identifier]->creation_date = $myrow['creation_date'];
                $_SESSION['Contract' . $identifier]->remark = $myrow['narrative'];
                $_SESSION['Contract' . $identifier]->customer_name = $myrow['customer_name'];
                $_SESSION['Contract' . $identifier]->delivery_amount = $myrow['delivery_amount'];
            }
            if (!isset($_POST['Update'])) {
                $sql = 'SELECT
delivery_num,
    deliveryline,
    stockid,item_desc,
    price,
    uom, 
    delivery_quantity,      
    line_amount,
   
    subinventory_code,                                      
  status
FROM  so_delivery_all c,sf_item_no d
        where c.stockid=d.item_no
        and c.delivery_num = ' . "'" . "$delivery_num" . "'";
                $CustResult = DB_query($sql, $db);
                while ($myrow = DB_fetch_array($CustResult)) {
                    $delivery_num = $myrow['delivery_num'];
                    $item_no = $myrow['stockid'];
                    $item_desc = $myrow['item_desc']; 
                    $uom = $myrow['uom'];
                    $quantity = $myrow['delivery_quantity'];
                    //                    $note = $myrow['note'];
                    $status = $myrow['status'];
                    $line = $myrow['deliveryline'];
                    $price = $myrow['price'];
                    $amount = $myrow['line_amount'];
                    $code = $myrow['subinventory_code'];
                    $quantity_shiped = $myrow['quantity_shiped'];
                    $quantity_cancelled = $myrow['quantity_cancelled'];
                    $quantity_billed = $myrow['quantity_billed'];
                 
                    $_SESSION['Contract' . $identifier]->AddLine($delivery_num, $item_no, $item_desc,$uom,
                        $quantity, $price, $line, $amount, $quantity_shiped, $quantity_billed, $remark,
                        $quantity_cancelled, $status,$code);
                }
            }
        }
    }
}
if (isset($_POST['Update'])) {
    $InputError = 0;
    //    if ($_POST['delivery_date'] == '') {
    //
    //        prnMsg(_('必须输入生效日期'), 'error');
    //        $InputError = 1;
    //    }


    if ($InputError == 0) {

        $_SESSION['Contract' . $identifier]->delivery_num = $_POST['delivery_num'];
        $_SESSION['Contract' . $identifier]->delivery_date = strtotime($_POST['delivery_date']);
        $_SESSION['Contract' . $identifier]->remark = $_POST['remark'];
        $_SESSION['Contract' . $identifier]->delivery_amount = $_POST['delivery_amount'];
    }
}

if (isset($_POST['Edit'])) {
    if ($_POST["quantity"] > $_SESSION['Contract' . $identifier]->LineItems[$_POST['LineNumber']]->
        quantity_received) {
        $_SESSION['Contract' . $identifier]->LineItems[$_POST['LineNumber']]->quantity =
            $_POST['quantity'];
        $_SESSION['Contract' . $identifier]->LineItems[$_POST['LineNumber']]->price = $_POST['price'];
        $_SESSION['Contract' . $identifier]->LineItems[$_POST['LineNumber']]->amount = $_POST['quantity'] *
            $_POST['price'];
        $line = $_SESSION['Contract' . $identifier]->LineItems[$_GET['Delete']]->line;
    } else {
        prnMsg(_('修改失败，输入量小于来料报检量！'), 'error');

    }
}

if (isset($_GET['Delete'])) {
    $identifier = $_GET['identifier'];
    $delivery_num = $_SESSION['Contract' . $identifier]->LineItems[$_GET['Delete']]->
        delivery_num;
    $line = $_SESSION['Contract' . $identifier]->LineItems[$_GET['Delete']]->line;
    $status = $_SESSION['Contract' . $identifier]->LineItems[$_GET['Delete']]->
        status;
    if (isset($line)) {
        $deletesql1 = "update  so_delivery_all
                      set quantity_cancelled=quantity,
                          status='Cancel'
                where delivery_num='" . $delivery_num . "' and line=" . "$line" . "  ";
        $Resultdelete = DB_query($deletesql1, $db);
     unset($_SESSION['Contract' . $identifier]->LineItems[$_GET['Delete']]);
      DB_Txn_Commit($db);
    }
    echo '<br />';
    prnMsg(_('删除成功！'), 'success');
    echo '<br />';
}

if (isset($_POST['Submit'])) {
    isset($_SESSION['num' . $identifier]) or die("no session");
    if ($_SESSION['num' . $identifier] == 400) {
        $_SESSION['num' . $identifier] = 500;
        DB_Txn_Begin($db);
        $InputError = 0;
        //        if ($_SESSION['Contract' . $identifier]->delivery_date == '') {
        //            prnMsg(_('必须输入生效日期'), 'error');
        //            echo '<br /><div class="centre"><a a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?identifier=' . $identifier . '">' . _('返回修改送货单') . '</a></div>';
        //            $InputError = 1;
        //        }

        $f_date = $_SESSION['Contract' . $identifier]->delivery_date;
        $delivery_num = $_SESSION['Contract' . $identifier]->delivery_num;
        $v_date = strtotime(Date('Y-m-d H:i:s'));
        $sumamount = 0.00;
        if ($InputError == 0) {
            foreach ($_SESSION['Contract' . $identifier]->LineItems as $LineItems) {
                if ($LineItems->status <> 'Cancel') {
                    $count = $count + 1;
                    $linesql = "UPDATE so_delivery_all " . "  set delivery_quantity=  " . $LineItems->quantity .
                        ",
                                price  ='" . $LineItems->price . "',
                                line_amount ='" . $LineItems->amount . "',
                                last_update_date ='" . $v_date . "',
                                last_updated_by= '" . $_SESSION['UserID'] . "',
                                status= 'INPROCESS'
                        where delivery_num='" . $delivery_num . "' and deliveryline =  " . $LineItems->
                        line . "
                         ";
                    $Result = DB_query($linesql, $db);
                }
                $sumamount = $sumamount + $LineItems->amount;
            }
            if ($count > 0) {
                $HeaderSQL = "update so_delivery_headers_all  
                                 set narrative= '" . $_SESSION['Contract' . $identifier]->
                    remark . "',
                                     delivery_date= '" . $f_date . "',
                                         delivery_amount='" . $sumamount . "',
                                     status='INPROCESS',
                                     last_update_date='" . $v_date . "',
                                     last_updated_by= '" . $_SESSION['UserID'] .
                    "'
                       where delivery_num='" . $delivery_num . "'";
                $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') .
                    ': ' . _('数据存入数据库，原因是：'); //'" . FormatDateForSQL($_SESSION['Contract'.$identifier]->DispatchDate) . "',
                $DbgMsg = _('The following SQL to insert the request header record was used');
                $Result = DB_query($HeaderSQL, $db, $ErrMsg, $DbgMsg, true);
                DB_Txn_Commit($db);
                $msg = '送货单修改成功！1秒后将跳转回主页！';
                prnMsg($msg, 'success');
                echo '<meta http-equiv="refresh" content="1; url=' . $RootPath .
                    '/UpdateDelivery.php" />';
                echo '<br />';
                echo '<br /><div class="centre"><a href="' . $RootPath . '/UpdateDelivery.php">' . _('重新选择送货单') .
                    '</a></div>';
                unset($_SESSION['Contract' . $identifier]);
            } else {
                prnMsg(_('送货单行无数据，如果确定全部取消请选择：全部取消'), 'error');
                unset($_SESSION['Contract' . $identifier]);
            }
            DB_Txn_Commit($db);
        }
        include ('includes/footer.inc');
        exit;
    } else {
        header('Location: UpdateDelivery.php');
    }
} elseif (isset($_POST['Cancel'])) {
    $delivery_num = $_SESSION['Contract' . $identifier]->delivery_num;
    $HeaderSQL = "update so_delivery_headers_all   
                          set status= 'Cancel'
                where delivery_num='" . $delivery_num . "'";
    $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') .
        ': ' . _('数据存入数据库，原因是：'); //'" . FormatDateForSQL($_SESSION['Contract'.$identifier]->DispatchDate) . "',
    $DbgMsg = _('The following SQL to insert the request header record was used');
    $Result = DB_query($HeaderSQL, $db, $ErrMsg, $DbgMsg, true);
    $HeaderSQL2 = "update so_delivery_all 
                          set status= 'Cancel',
                              quantity=0
                where delivery_num='" . $delivery_num . "'";
    $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') .
        ': ' . _('数据存入数据库，原因是：'); //'" . FormatDateForSQL($_SESSION['Contract'.$identifier]->DispatchDate) . "',
    $DbgMsg = _('The following SQL to insert the request header record was used');
    $Result2 = DB_query($HeaderSQL2, $db, $ErrMsg, $DbgMsg, true);

    $msg = '送货单取消成功！1秒后将跳转回上一页！';
    prnMsg($msg, 'success');
    echo '<meta http-equiv="refresh" content="1; url=' . $RootPath .
        '/POUpdate.php" />';
    echo '<br />';
    include ('includes/footer.inc');
    exit;
} else {
    session_start() or die("session is not started");
    $_SESSION['num' . $identifier] = 400;
}
echo '  <div class="centre">
        <a href="' . $RootPath . '/UpdateDelivery.php">返回重新选择送货单</a>
    </div>';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme .
    '/images/supplier.png" title="' . _('Dispatch') . '" alt="" />' . ' ' . $Title .
    '</p>';

if (isset($_GET['Edit'])) {
    $status2 = $_SESSION['Contract' . $identifier]->LineItems[$_GET['Edit']]->
        status;                           
    if ($status2 == 'D') {
        echo '<br />';
        prnMsg(_('此行已经取消，不可以修改，请重新添加行！'), 'error');
        echo '<br />';
    } else {
        $identifier = $_GET['identifier'];
        echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES,
            'UTF-8') . '" method="post"><input type="hidden" name = "identifier" value ="' .
            $identifier . '">';
        echo '<div>';
        echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
        echo '<table class="selection">';
        echo '<tr>
            <th colspan="2"><h4>' . _('修改送货单行信息') . '</h4></th>
        </tr>';
        echo '<tr>
            <td>' . _('Line number') . '</td>
            <td>' . $_SESSION['Contract' . $identifier]->LineItems[$_GET['Edit']]->
            LineNumber . '</td>
        </tr>
        <tr>
            <td width=100>' . _('规格型号:') . '</td>
            <td>' . $_SESSION['Contract' . $identifier]->LineItems[$_GET['Edit']]->
            item_no . '</td>
        </tr>
        <tr>
            <td width=100>' . _('产品名称:') . '</td>
            <td>' . $_SESSION['Contract' . $identifier]->LineItems[$_GET['Edit']]->
            item_desc . '</td>
        </tr>';
       
        

        echo '            
        <tr>
            <td>' . _('单位:') . '</td>
            <td>' . $_SESSION['Contract' . $identifier]->LineItems[$_GET['Edit']]->uom .
            '</td>
        </tr>   
                <tr>';
        echo '  <td width=100>' . _('数量:') . '</td>
            <td><input type="text" class="number" name="quantity" value="' . $_SESSION['Contract' .
            $identifier]->LineItems[$_GET['Edit']]->quantity . '" />（数量要大于0）</td>
        </tr>
                 <tr>';

        echo '  <td width=100>' . _('单价') . '</td>
            <td><input type="text" class="number" name="price" value="' . $_SESSION['Contract' .
            $identifier]->LineItems[$_GET['Edit']]->price . '" /></td>
        </tr>
                 <tr>';

        echo '<input type="hidden" name="LineNumber" value="' . $_SESSION['Contract' . $identifier]->
            LineItems[$_GET['Edit']]->LineNumber . '" />';
        echo '</table>
        <br />';
        echo '<div class="centre">
            <input type="submit" name="Edit" value="' . _('更新送货单行') . '" />
        </div>
        </div>
        </form>';
        include ('includes/footer.inc');
        exit;
    }
}

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES,
    'UTF-8') . '" method="post"><input type="hidden" name = "identifier" value ="' .
    $identifier . '">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<table class="selection">';
echo '<tr>
        <th colspan="4" height=" 0px"><h4>' . _('送货单头信息') . '</h4></th>
    </tr>';
if ($_SESSION['Contract' . $identifier]->status == 'INPROCESS') {
    $v_status = '待签核';
} elseif ($_SESSION['Contract' . $identifier]->status == 'APPROVED') {
    $v_status = '已签核';
} elseif ($_SESSION['Contract' . $identifier]->status == 'REJECTED') {
    $v_status = '已拒签';
} else {
    $v_status = '已取消';
}

echo '<tr class="EvenTableRows">
        <td >' . _('送货单') . ':</td>
                <td width="120">' . $_SESSION['Contract' . $identifier]->delivery_num .
    '
                <input type="hidden" class="text"  name="delivery_num" value="' . $_SESSION['Contract' .
    $identifier]->delivery_num . '" />
    </td>
    <td>状态：</td>' . '<td>' . $v_status . '</td></tr>';
$v_delivery_date = date('Y-m-d', $_SESSION['Contract' . $identifier]->delivery_date);
$v_create_date = date('Y-m-d', $_SESSION['Contract' . $identifier]->creation_date);
echo '<tr class="OddTableRows">' . '<td width=100>送货日期：</td>' .
    '<td><input type="text" onfocus="WdatePicker()"  name="delivery_date" maxlength="10" size="11" value="' .
    $v_delivery_date . '" /></td>' .  '<td>创建日期：</td>' . '<td>' . $v_create_date .
    '</td>' . '</tr>';

 
echo '  <tr class="EvenTableRows">
              <td width=100 >' . _('客户名称') . ':</td>
              <td colspan="3">' . $_SESSION['Contract' . $identifier]->customer_name . '</td>
        </tr>';
echo '  <tr class="EvenTableRows">
              <td width=100>' . _('备注') . ':</td>
              <td colspan="3"><textarea  name="remark" cols="40" rows="3" >' . $_SESSION['Contract' .
    $identifier]->remark . '</textarea></td>
        </tr>';
echo '</table>';

echo '<div class="centre">
    <input type="submit" name="Update" value="' . _('更新头信息') . '" />
    </div>
    </div>
    </form>';

if (!isset($_SESSION['Contract' . $identifier]->delivery_num)) {
    include ('includes/footer.inc');
    exit;
}

$i = 0; //Line Item Array pointer
echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES,
    'UTF-8') . '" method="post"><input type="hidden" name = "identifier" value ="' .
    $identifier . '">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<br /><div class="centre">
        <h4>' . _('送货单行维护') . '</h4>
      </div>';
echo '<table class="selection">
    <tr>
        <th>' . _('Line Number') . '</th>
        <th   width=100>' . _('规格型号') . '</th>
        <th   width=100>' . _('产品名称') . '</th>    
        <th  >' . _('单位') . '</th> 
        <th   width=100>' . _('送货单数量') . '</th>
        <th   width=100>' . _('单价') . '</th>
          <th   width=100>' . _('金额') . '</th>
    
                <th   width=100>' . _('编辑') . '</th> 
                <th   width=100>' . _('删除') . '</th> 
    </tr>';

$k = 0;

 
foreach ($_SESSION['Contract' . $identifier]->LineItems as $LineItems) {

    if ($k == 1) {
        echo '<tr class="EvenTableRows">';
        $k = 0;
    } else {
        echo '<tr class="OddTableRows">';
        $k++;
    }
    echo '<td>' . $LineItems->LineNumber . '</td>
      <td>' . $LineItems->item_no . '</td>                                    
          <td>' . $LineItems->item_desc . '</td>
      
      <td>' . $LineItems->uom . '</td>
      <td>' . $LineItems->quantity . '</td> 
            <td>' . $LineItems->price . '</td>
            <td>' . $LineItems->amount . '</td>
            ';

    //    if ($LineItems->status == 'INPROCESS') {
    //        $v_status = '待签核';
    //    } elseif ($LineItems->status == 'APPROVED') {
    //        $v_status = '已签核';
    //    } elseif ($LineItems->status == 'REJECTED') {
    //        $v_status = '已拒签';
    //    } else {
    //        $v_status = '已取消';
    //    }
    echo '
      <td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') .
        '?identifier=' . $identifier . '&Edit=' . $LineItems->LineNumber . '">' . _('Edit') .
        '</a></td>
      <td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') .
        '?identifier=' . $identifier . '&Delete=' . $LineItems->LineNumber . '">' . _('Delete') .
        '</a></td>
     </tr>';
}
echo '</table>
    <table><tr>
              <td width="200">
    
    <div class="centre">
                <input type="submit" name="Submit" value="' . _('确认修改') .
    '" />   
    </div>
        </td>  <td  width="200">
    <div class="centre">
                <input type="submit" name="Cancel" value="' . _('取消全部') . '" />
    </div>
        </td>
        </tr>
     </table> 
    </div>
    </form>';
echo '<br><br><br>';
//*********************************************************************************************************
include ('includes/footer.inc');
?>
