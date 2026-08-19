<?php
/*
* To change this license header, choose License Headers in Project Properties.
* To change this template file, choose Tools | Templates
* and open the template in the editor.
*/

include ('includes/DefinePOUpdateClass.php');

include ('includes/session.inc');

$Title = _('订单修改');
$ViewTopic = '订单修改';
$BookMark = '订单修改';
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
    if (isset($_GET['Updateorder_number'])) {
        if (!isset($_SESSION['Contract' . $identifier]->order_number) or $_SESSION['Contract' .
            $identifier]->order_number == '') {
            $order_number = $_GET['Updateorder_number'];
            $sql = 'SELECT
    a.order_number,
    a.status,
    b.customer_code,
    b.customer_name,
    a.order_amount  ,
    a.header_remark,
    a.need_date, 
    a.creation_date
FROM so_headers_all a,
    customers b 
WHERE
    1 = 1
AND a.customer_code = b.customer_code

AND a.order_number=' . "'" . "$order_number" . "'";
$sql = $sql . " and a.so_quote='SO' ";
            $CustResult = DB_query($sql, $db);
            while ($myrow = DB_fetch_array($CustResult)) {
                $_SESSION['Contract' . $identifier]->order_number = $myrow['order_number'];
                $_SESSION['Contract' . $identifier]->status = $myrow['status'];
                $_SESSION['Contract' . $identifier]->need_date = $myrow['need_date'];
                $_SESSION['Contract' . $identifier]->creation_date = $myrow['creation_date'];
                $_SESSION['Contract' . $identifier]->header_remark = $myrow['header_remark'];
                $_SESSION['Contract' . $identifier]->customer_name = $myrow['customer_name'];
                $_SESSION['Contract' . $identifier]->order_amount = $myrow['order_amount'];
            }
            if (!isset($_POST['Update'])) {
                $sql = 'SELECT
order_number,
    line,
    stockid,item_desc,
    price,
    uom, 
    quantity,
    quantity_shiped,
    quantity_cancelled,      
    quantity_billed,   
    amount,
    quantity, 
    line_remark,subinventory_code,                                      
  status
FROM so_lines_all c,sf_item_no d
        where c.stockid=d.item_no
        and c.order_number = ' . "'" . "$order_number" . "'";
                $CustResult = DB_query($sql, $db);
                while ($myrow = DB_fetch_array($CustResult)) {
                    $order_number = $myrow['order_number'];
                    $item_no = $myrow['stockid'];
                    $item_desc = $myrow['item_desc']; 
                    $uom = $myrow['uom'];
                    $quantity = $myrow['quantity'];
                    //                    $note = $myrow['note'];
                    $status = $myrow['status'];
                    $line = $myrow['line'];
                    $price = $myrow['price'];
                    $amount = $myrow['amount'];
                    $code = $myrow['subinventory_code'];
                    $quantity_shiped = $myrow['quantity_shiped'];
                    $quantity_cancelled = $myrow['quantity_cancelled'];
                    $quantity_billed = $myrow['quantity_billed'];
                    $remark = $myrow['line_remark'];
                    $_SESSION['Contract' . $identifier]->AddLine($order_number, $item_no, $item_desc,$uom,
                        $quantity, $price, $line, $amount, $quantity_shiped, $quantity_billed, $remark,
                        $quantity_cancelled, $status,$code);
                }
            }
        }
    }
}
if (isset($_POST['Update'])) {
    $InputError = 0;
    //    if ($_POST['need_date'] == '') {
    //
    //        prnMsg(_('必须输入生效日期'), 'error');
    //        $InputError = 1;
    //    }


    if ($InputError == 0) {

        $_SESSION['Contract' . $identifier]->order_number = $_POST['order_number'];
        $_SESSION['Contract' . $identifier]->need_date = strtotime($_POST['need_date']);
        $_SESSION['Contract' . $identifier]->remark = $_POST['remark'];
        $_SESSION['Contract' . $identifier]->order_amount = $_POST['order_amount'];
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
    $order_number = $_SESSION['Contract' . $identifier]->LineItems[$_GET['Delete']]->
        order_number;
    $line = $_SESSION['Contract' . $identifier]->LineItems[$_GET['Delete']]->line;
    $status = $_SESSION['Contract' . $identifier]->LineItems[$_GET['Delete']]->
        status;
    if (isset($line)) {
        $deletesql1 = "update  so_lines_all
                      set quantity_cancelled=quantity,
                          status='Cancel'
                where order_number='" . $order_number . "' and line=" . "$line" . "  ";
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
        //        if ($_SESSION['Contract' . $identifier]->need_date == '') {
        //            prnMsg(_('必须输入生效日期'), 'error');
        //            echo '<br /><div class="centre"><a a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?identifier=' . $identifier . '">' . _('返回修改订单') . '</a></div>';
        //            $InputError = 1;
        //        }

        $f_date = $_SESSION['Contract' . $identifier]->need_date;
        $order_number = $_SESSION['Contract' . $identifier]->order_number;
        $v_date = strtotime(Date('Y-m-d H:i:s'));
        $sumamount = 0.00;
        if ($InputError == 0) {
            foreach ($_SESSION['Contract' . $identifier]->LineItems as $LineItems) {
                if ($LineItems->status <> 'Cancel') {
                    $count = $count + 1;
                    $linesql = "UPDATE so_lines_all " . "  set quantity=  " . $LineItems->quantity .
                        ",
                                price  ='" . $LineItems->price . "',
                                amount ='" . $LineItems->amount . "',
                                last_update_date ='" . $v_date . "',
                                last_updated_by= '" . $_SESSION['UserID'] . "',
                                status= 'INPROCESS'
                        where order_number='" . $order_number . "' and line =  " . $LineItems->
                        line . "
                         ";
                    $Result = DB_query($linesql, $db);
                }
                $sumamount = $sumamount + $LineItems->amount;
            }
            if ($count > 0) {
                $HeaderSQL = "update so_headers_all  
                                 set header_remark= '" . $_SESSION['Contract' . $identifier]->
                    note . "',
                                     need_date= '" . $f_date . "',
                                         order_amount='" . $sumamount . "',
                                     status='INPROCESS',
                                     last_update_date='" . $v_date . "',
                                     last_updated_by= '" . $_SESSION['UserID'] .
                    "'
                       where order_number='" . $order_number . "'";
                $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') .
                    ': ' . _('数据存入数据库，原因是：'); //'" . FormatDateForSQL($_SESSION['Contract'.$identifier]->DispatchDate) . "',
                $DbgMsg = _('The following SQL to insert the request header record was used');
                $Result = DB_query($HeaderSQL, $db, $ErrMsg, $DbgMsg, true);
                DB_Txn_Commit($db);
                $msg = '订单修改成功！1秒后将跳转回主页！';
                prnMsg($msg, 'success');
                echo '<meta http-equiv="refresh" content="1; url=' . $RootPath .
                    '/QuoteUpdate.php" />';
                echo '<br />';
                echo '<br /><div class="centre"><a href="' . $RootPath . '/QuoteUpdate.php">' . _('重新选择订单') .
                    '</a></div>';
                unset($_SESSION['Contract' . $identifier]);
            } else {
                prnMsg(_('订单行无数据，如果确定全部取消请选择：全部取消'), 'error');
                unset($_SESSION['Contract' . $identifier]);
            }
            DB_Txn_Commit($db);
        }
        include ('includes/footer.inc');
        exit;
    } else {
        header('Location: QuoteUpdate.php');
    }
} elseif (isset($_POST['Cancel'])) {
    $order_number = $_SESSION['Contract' . $identifier]->order_number;
    $HeaderSQL = "update so_headers_all   
                          set status= 'Cancel'
                where order_number='" . $order_number . "'";
    $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') .
        ': ' . _('数据存入数据库，原因是：'); //'" . FormatDateForSQL($_SESSION['Contract'.$identifier]->DispatchDate) . "',
    $DbgMsg = _('The following SQL to insert the request header record was used');
    $Result = DB_query($HeaderSQL, $db, $ErrMsg, $DbgMsg, true);
    $HeaderSQL2 = "update so_lines_all 
                          set status= 'Cancel',
                              quantity=0
                where order_number='" . $order_number . "'";
    $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') .
        ': ' . _('数据存入数据库，原因是：'); //'" . FormatDateForSQL($_SESSION['Contract'.$identifier]->DispatchDate) . "',
    $DbgMsg = _('The following SQL to insert the request header record was used');
    $Result2 = DB_query($HeaderSQL2, $db, $ErrMsg, $DbgMsg, true);

    $msg = '订单取消成功！1秒后将跳转回上一页！';
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
        <a href="' . $RootPath . '/UpdateSoForApprove.php">返回重新选择订单</a>
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
            <th colspan="2"><h4>' . _('修改订单行信息') . '</h4></th>
        </tr>';
        echo '<tr>
            <td>' . _('Line number') . '</td>
            <td>' . $_SESSION['Contract' . $identifier]->LineItems[$_GET['Edit']]->
            LineNumber . '</td>
        </tr>
        <tr>
            <td width=100>' . _('料号:') . '</td>
            <td>' . $_SESSION['Contract' . $identifier]->LineItems[$_GET['Edit']]->
            item_no . '</td>
        </tr>
        <tr>
            <td width=100>' . _('料号名称:') . '</td>
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
            <input type="submit" name="Edit" value="' . _('更新订单行') . '" />
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
        <th colspan="2"><h4>' . _('订单头信息') . '</h4></th>
    </tr>';


echo '<tr class="EvenTableRows">
        <td >' . _('订单') . ':</td>
                <td width="300">' . $_SESSION['Contract' . $identifier]->order_number .
    '
                <input type="hidden" class="text"  name="order_number" value="' . $_SESSION['Contract' .
    $identifier]->order_number . '" />
    </td></tr>';
$v_need_date = date('Y-m-d', $_SESSION['Contract' . $identifier]->need_date);
$v_create_date = date('Y-m-d', $_SESSION['Contract' . $identifier]->creation_date);
echo '<tr class="OddTableRows">' . '<td width=100>需求日期：</td>' .
    '<td><input type="text" onfocus="WdatePicker()"  name="need_date" maxlength="10" size="11" value="' .
    $v_need_date . '" /></td>' . '</tr>';
echo '<tr class="EvenTableRows">' . '<td>创建日期：</td>' . '<td>' . $v_create_date .
    '</td>' . '</tr>';
if ($_SESSION['Contract' . $identifier]->status == 'INPROCESS') {
    $v_status = '待签核';
} elseif ($_SESSION['Contract' . $identifier]->status == 'APPROVED') {
    $v_status = '已签核';
} elseif ($_SESSION['Contract' . $identifier]->status == 'REJECTED') {
    $v_status = '已拒签';
} else {
    $v_status = '已取消';
}
echo '<tr class="OddTableRows">' . '<td>状态：</td>' . '<td>' . $v_status . '</td>' .
    '</tr>';
echo '  <tr class="EvenTableRows">
              <td width=100>' . _('客户') . ':</td>
              <td>' . $_SESSION['Contract' . $identifier]->customer_name . '</td>
        </tr>';
echo '  <tr class="EvenTableRows">
              <td width=100>' . _('备注') . ':</td>
              <td><textarea  name="remark" cols="40" rows="3">' . $_SESSION['Contract' .
    $identifier]->remark . '</textarea></td>
        </tr>';
echo '</table>';

echo '<div class="centre">
    <input type="submit" name="Update" value="' . _('更新头信息') . '" />
    </div>
    </div>
    </form>';

if (!isset($_SESSION['Contract' . $identifier]->order_number)) {
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
        <h4>' . _('订单行维护') . '</h4>
      </div>';
echo '<table class="selection">
    <tr>
        <th>' . _('Line Number') . '</th>
        <th   width=100>' . _('料号') . '</th>
        <th   width=100>' . _('料号名称') . '</th>    
        <th  >' . _('单位') . '</th> 
        <th   width=100>' . _('订单数量') . '</th>
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
