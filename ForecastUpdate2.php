
<?php
/*
* To change this license header, choose License Headers in Project Properties.
* To change this template file, choose Tools | Templates
* and open the template in the editor.
*/

include ('includes/DefineForecastUpdateClass.php');

include ('includes/session.inc');

$Title = _('需求预测单修改');
$ViewTopic = '需求预测单修改';
$BookMark = '需求预测单修改';
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
            $sql = "SELECT
	a.order_number,
	a.status,
	b.customer_code,
	b.customer_name, 
	a.remark, 
	a.creation_date
FROM so_forecast_header a,
	customers b 
WHERE
	1 = 1
AND a.customer_code = b.customer_code
AND a.order_number='".$order_number ."'";
//echo  $sql;
            $CustResult = DB_query($sql, $db);
            while ($myrow = DB_fetch_array($CustResult)) {
                $_SESSION['Contract' . $identifier]->order_number = $myrow['order_number'];
                $_SESSION['Contract' . $identifier]->status = $myrow['status'];
                $_SESSION['Contract' . $identifier]->need_date = $myrow['need_date'];
                $_SESSION['Contract' . $identifier]->creation_date = $myrow['creation_date'];
                $_SESSION['Contract' . $identifier]->remark = $myrow['remark'];
				$_SESSION['Contract' . $identifier]->customer_name = $myrow['customer_name'];
            }
            if (!isset($_POST['Update'])) {
                $sql = 'SELECT
order_number,
	line,
	stockid,item_desc, 
	uom, 
	quantity, need_date,
	quantity_cancelled, 	 
	quantity,
	remark,subinventory_code,                                      
  status
FROM so_forecast_line c,sf_item_no d
        where c.stockid=d.item_no
		and c.order_number = ' . "'" . "$order_number" . "'";
                $CustResult = DB_query($sql, $db);
                while ($myrow = DB_fetch_array($CustResult)) {
                    $order_number = $myrow['order_number'];
                    $item_no = $myrow['stockid'];
                    $item_desc = $myrow['item_desc'];
                    $uom = $myrow['uom'];
                    $quantity = $myrow['quantity'];
                    $status = $myrow['status'];
                    $line = $myrow['line'];
                    $line_need_date = $myrow['need_date']; 
					$sub_code = $myrow['subinventory_code'];
                    $quantity_cancelled = $myrow['quantity_cancelled'];
                    $remark = $myrow['remark'];
                    $_SESSION['Contract' . $identifier]->AddLine($order_number,$line, $item_no, $item_desc, $uom,
                        $quantity,$quantity_cancelled, $line_need_date,  $remark, $status,$sub_code);
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
    }
}

if (isset($_POST['Edit'])) {
    if ($_POST["quantity"] > $_SESSION['Contract' . $identifier]->LineItems[$_POST['LineNumber']]->
        quantity_received) {
        $_SESSION['Contract' . $identifier]->LineItems[$_POST['LineNumber']]->quantity = $_POST['quantity'];
        $_SESSION['Contract' . $identifier]->LineItems[$_POST['LineNumber']]->line_need_date = strtotime($_POST['line_need_date']);
        $_SESSION['Contract' . $identifier]->LineItems[$_POST['LineNumber']]->remark =  $_POST['remark'];
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
        $deletesql1 = "update  so_forecast_line
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
      
        $f_date = $_SESSION['Contract' . $identifier]->need_date;
        $order_number = $_SESSION['Contract' . $identifier]->order_number;
        $v_date = strtotime(Date('Y-m-d H:i:s'));
        $sumamount = 0.00;
        if ($InputError == 0) {
            foreach ($_SESSION['Contract' . $identifier]->LineItems as $LineItems) {
                if ($LineItems->status <> 'Cancel') {
                    $count = $count + 1;
                    $linesql = "UPDATE so_forecast_line " . "  
					                 set quantity=  " . $LineItems->quantity . ",                               
									 need_date ='" . $LineItems->line_need_date . "',
									 remark ='" . $LineItems->remark . "',
                                last_update_date ='" . $v_date . "',
                                last_updated_by= '" . $_SESSION['UserID'] . "',
                                status= '在签核'
                        where order_number='" . $order_number . "' and line =  " . $LineItems->
                        line . "
                         ";
                    $Result = DB_query($linesql, $db);
                }
                $sumamount = $sumamount + $LineItems->amount;
            }
            if ($count > 0) {
                $HeaderSQL = "update so_forecast_header  
                                 set remark= '" . $_SESSION['Contract' . $identifier]-> remark . "',  
                                     status='在签核',
                                     last_update_date='" . $v_date . "',
                                     last_updated_by= '" . $_SESSION['UserID'] .
                    "'
		               where  order_number='" . $order_number . "'";
                $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') .
                    ': ' . _('数据存入数据库，原因是：'); //'" . FormatDateForSQL($_SESSION['Contract'.$identifier]->DispatchDate) . "',
                $DbgMsg = _('The following SQL to insert the request header record was used');
                $Result = DB_query($HeaderSQL, $db, $ErrMsg, $DbgMsg, true);
                DB_Txn_Commit($db);
                $msg = '需求预测单修改成功！1秒后将跳转回主页！';
                prnMsg($msg, 'success');
                echo '<meta http-equiv="refresh" content="1; url=' . $RootPath .
                    '/ForecastUpdate.php" />';
                echo '<br />';
                echo '<br /><div class="centre"><a href="' . $RootPath . '/ForecastUpdate.php">' . _('重新选择需求预测单') .
                    '</a></div>';
                unset($_SESSION['Contract' . $identifier]);
            } else {
                prnMsg(_('需求预测单行无数据，如果确定全部取消请选择：全部取消'), 'error');
                unset($_SESSION['Contract' . $identifier]);
            }
            DB_Txn_Commit($db);
        }
        include ('includes/footer.inc');
        exit;
    } else {
        header('Location: ForecastUpdate.php');
    }
} elseif (isset($_POST['Cancel'])) {
    $order_number = $_SESSION['Contract' . $identifier]->order_number;
    $HeaderSQL = "update so_forecast_header   
                          set status= '取消'
		        where order_number='" . $order_number . "'";
    $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') .
        ': ' . _('数据存入数据库，原因是：'); //'" . FormatDateForSQL($_SESSION['Contract'.$identifier]->DispatchDate) . "',
    $DbgMsg = _('The following SQL to insert the request header record was used');
    $Result = DB_query($HeaderSQL, $db, $ErrMsg, $DbgMsg, true);
    $HeaderSQL2 = "update so_forecast_line 
                          set status= '取消',
                              quantity=0
		        where order_number='" . $order_number . "'";
    $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') .
        ': ' . _('数据存入数据库，原因是：'); //'" . FormatDateForSQL($_SESSION['Contract'.$identifier]->DispatchDate) . "',
    $DbgMsg = _('The following SQL to insert the request header record was used');
    $Result2 = DB_query($HeaderSQL2, $db, $ErrMsg, $DbgMsg, true);

    $msg = '需求预测单取消成功！1秒后将跳转回上一页！';
    prnMsg($msg, 'success');
    echo '<meta http-equiv="refresh" content="1; url=' . $RootPath .
        '/ForecastUpdate.php" />';
    echo '<br />';
    include ('includes/footer.inc');
    exit;
} else {
    session_start() or die("session is not started");
    $_SESSION['num' . $identifier] = 400;
}
echo '	<div class="centre">
		<a href="' . $RootPath . '/ForecastUpdate.php">返回重新选择预测计划单</a>
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
			<th colspan="2"><h4>' . _('修改需求预测单行信息') . '</h4></th>
		</tr>';
        echo '<tr>
			<td>' . _('Line number') . '</td>
			<td>' . $_SESSION['Contract' . $identifier]->LineItems[$_GET['Edit']]->
            LineNumber . '</td>
		</tr>
		<tr>
			<td width=100>' . _('料号') . '</td>
			<td>' . $_SESSION['Contract' . $identifier]->LineItems[$_GET['Edit']]->
            item_no . '</td>
		</tr>
		<tr>
			<td width=100>' . _('料号描述') . '</td>
			<td>' . $_SESSION['Contract' . $identifier]->LineItems[$_GET['Edit']]->
            item_desc . '</td>
		</tr>';

        echo '            
		<tr>
			<td>' . _('单位') . '</td>
			<td>' . $_SESSION['Contract' . $identifier]->LineItems[$_GET['Edit']]->uom .
            '</td>
		</tr>	';
		  echo '            
		<tr>
			<td>' . _('仓库') . '</td>
			<td>' . $_SESSION['Contract' . $identifier]->LineItems[$_GET['Edit']]->sub_code .
            '</td>
		</tr>	';

        echo '	<td width=100>' . _('数量') . '</td>
			<td><input type="text" class="number" name="quantity" value="' . $_SESSION['Contract' .
            $identifier]->LineItems[$_GET['Edit']]->quantity . '" />（数量要大于0）</td>
		</tr>';


		  $v_line_need_date=date('Y-m-d',$_SESSION['Contract' . $identifier]->LineItems[$_GET['Edit']]->line_need_date); 

		echo '	 <tr><td width=100>' . _('需求日期') . '</td>
			<td><input type="text" onfocus="WdatePicker()"  name="line_need_date" value="' . $v_line_need_date . '" /> </td>
		</tr>';

		  echo '	<td width=100>' . _('备注') . '</td>
			<td><input type="text" name="remark" value="'.$_SESSION['Contract'.$identifier]->LineItems[$_GET['Edit']]->remark.'" /></td>
		</tr>';

      
        echo '<tr><input type="hidden" name="LineNumber" value="' . $_SESSION['Contract' . $identifier]->
            LineItems[$_GET['Edit']]->LineNumber . '" />';
        echo '</table>
		<br />';
        echo '<div class="centre">
			<input type="submit" name="Edit" value="' . _('更新需求预测单行') . '" />
		</div>
        </div>
		</form>';
        include ('includes/footer.inc');
        exit;
    }
}

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES,'UTF-8') . '" method="post"><input type="hidden" name = "identifier" value ="' .$identifier . '">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<table class="selection">';
 

$v_need_date = date('Y-m-d', $_SESSION['Contract' . $identifier]->need_date);
$v_create_date = date('Y-m-d', $_SESSION['Contract' . $identifier]->creation_date);
 
echo '<tr class="EvenTableRows">
		<td >' . _('需求预测单') . ':</td>
        <td  >' . $_SESSION['Contract' . $identifier]->order_number . '
          <input type="hidden" class="text"  name="order_number" value="'.$_SESSION['Contract'.$identifier]->order_number . '" />
	</td>';
	echo '<td>创建日期：</td>' . '<td>' . $v_create_date . '</td>' .  '</tr>';

echo '<tr class="OddTableRows">' . '<td>状态：</td>' . '<td>' . $_SESSION['Contract' . $identifier]->status . '</td>
      <td width=100>' . _('客户') . ':</td>
     <td>' . $_SESSION['Contract' . $identifier]->customer_name . '</td>
			  ';
echo ' <tr class="EvenTableRows"> ';
 
echo '  <td >' . _('备注') . ':</td>
         <td colspan="6"><input type="text"  name="remark"    maxlength="100" size="80"  value="'. $_SESSION['Contract' .
    $identifier]->remark . '"/></td>
        </tr>';
echo '</table>';

echo '<div class="centre">
	<input type="submit" name="Update" value="' . _('更新头信息') . '" />
	</div>
  </br>
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

echo '<table class="selection">
	<tr>
		<th>' . _('Line Number') . '</th>
		<th   width=150>' . _('料号') . '</th>
		<th   width=200>' . _('料号描述') . '</th>		
		<th  >' . _('单位') . '</th> 
		<th   width=100>' . _('数量') . '</th>
		<th   width=80>' . _('仓库') . '</th>
		<th   width=100>' . _('需求日期') . '</th>
		<th   width=160>' . _('备注') . '</th>
                          
                <th   width=60>' . _('编辑') . '</th> 
                <th   width=60>' . _('删除') . '</th> 
	</tr>';

$k = 0;

 
foreach ($_SESSION['Contract' . $identifier]->LineItems as $LineItems) {
    $v_line_need_date=date('Y-m-d',$LineItems->line_need_date);
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
	  <td>' . $LineItems->sub_code . '</td> 
	   <td>' . $v_line_need_date . '</td> 
	    <td>' . $LineItems->remark . '</td> 
			';

 
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
