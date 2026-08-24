<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
include('includes/session.inc');
$Title = '订单开始生产确认';
include('includes/header.inc');
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
        $InputError = 1;
        DB_Txn_Begin($db);
        foreach ($_POST as $key => $value) {
            if (mb_substr($key, 0, 6) == 'status') {
                $order_number = mb_substr($key, 6);
                //echo $order_number;
                $v_date = strtotime(Date('Y-m-d H:i:s'));
                $line_no = $_POST['line_no' . $order_line_id];
                $sql1 = "Update sf_order_lines_all 
				set Start_Work_flag = 'Y',
				    last_update_date = $v_date,
                                    last_updated_by =  '" . $_SESSION['UserID'] . "'
		     WHERE order_line_id          = '" . $order_number . "'
                      ";
                $result = DB_query($sql1, $db);
                // $sql2 = "insert into wip_transactions(order_number) select order_number from sf_orders_all where order_number = '" . $order_number . "'";
                // $result2 = DB_query($sql2, $db);
                DB_Txn_Commit($db);
            }
        }
        DB_Txn_Commit($db);

        $msg = '资料确认成功！';
        prnMsg($msg, 'success');
// echo '<meta http-equiv="refresh" content="0.3" url=RequestReceive.php"/>';

        unset($_POST['order_line_id']);
        unset($_POST['order_number']);
        unset($_POST['$line_no']);
    }
} else {
    session_start() or die("session is not started");
    $_SESSION['num' . $identifier] = 400;
}


echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . '开始生产确认' .
 '" alt="" />' . ' ' . $Title . '
	</p>';

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post"><input type="hidden" name = "identifier" value ="' . $identifier . '">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
// <td>' . _('Type') . ':</td> 20
echo '<table class="selection">
		<tr>	
			<td>' . '订单号' . ':</td>
			<td><input   type="text" name="order_number"  /></td>
                     
                        </tr><tr>';
if (!isset($_POST['FromDate'])) {
    $_POST['FromDate'] = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 30, date("Y")));
}
if (!isset($_POST['ToDate'])) {
    $_POST['ToDate'] = Date('Y-m-d');
}
echo '<td>' . '下单日期' . _('From') . ':</td>
		<td><input type="text" onfocus="WdatePicker()"  name="FromDate" maxlength="10" size="11" value="' . $_POST['FromDate'] . '" /></td>
		<td>' . _('To') . ':</td>
		<td><input type="text" onfocus="WdatePicker()"  name="ToDate" maxlength="10" size="11" value="' . $_POST['ToDate'] . '" /></td>
	</tr></table><div class="centre"><input type="submit" name="Search" value="查找"></div>
	<br />
    </div>
	</form>';
if (isset($_POST['Search'])) {
    $sql = "SELECT
	a.order_number,
	b.chuanghu_bu_item,
	b.chuanghu_bu_quantity,
	b.chuanghu_fucai_item,
	b.chuanghu_fucai_quantity,
	b.chuanghu_sha_item,
	b.chuanghu_sha_quantity,
	b.line_no,
	a.creation_date,
	a. STATUS,
        a.order_id,
        b.order_line_id
FROM
	sf_orders_all a,
	sf_order_lines_all b
WHERE
a.order_number = b.order_number
and a.status = 'P_APPROVED'
and (ifnull(send_bu_quantity,0) >0
or ifnull(send_sha_quantity,0) >0)
and ifnull(Start_Work_flag,'N') = 'N'

            ";

    if (empty($_POST['order_number']) == 0) {
        $sql .= " AND a.order_number= '" . $_POST['order_number'] . "'";
    }
    if (empty($_POST['FromDate']) == 0) {
        $SQL_FromDate = strtotime($_POST['FromDate']);
        //echo $SQL_FromDate;
        $sql .= " and a.creation_date >= '" . $SQL_FromDate . "' ";
    }
    if (empty($_POST['ToDate']) == 0) {
        $SQL_ToDate = strtotime($_POST['ToDate']) + 86400;
        //echo $SQL_ToDate;
        $sql .= " and a.creation_date <='" . $SQL_ToDate . "' ";
    }

    $sql .= " ORDER BY order_number,line_no";
    $TransResult = DB_query($sql, $db);
    $ErrMsg = _('订单查询错误，请查看所选订单') . ' - ' . DB_error_msg($db);
    $DbgMsg = _('The SQL that failed was');
    if (DB_num_rows($TransResult) == 0) {
        unset($TransResult);
        prnMsg(_('没有找到需要确定的订单，请重新输入条件查询！'), 'info');
    } else {
        echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '"><input type="hidden" name = "identifier" value ="' . $identifier . '">';
        echo '<div>';
//echo '<div style="width:1300px;height:400px;overflow-x: hidden; overflow-y: scroll;">';
        echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
        echo '<table class="selection" align="center" >';
        $tableheader = '<tr>
                             <th width =50>' . '确认' . '</th>
                            <th width =100>' . '订单号' . '</th>
                            <th  width =50>' . '行' . '</th>
                            <th width =100 >' . '布料号' . '</th>
                            <th  width = 100>' . '布数量' . '</th>
                            <th  width = 100>' . '纱料号' . '</th>
                            <th  width = 100>' . '纱数量' . '</th>
                            <th  width = 100>' . '辅材料' . '</th>
                            <th  width = 100>' . '辅材数量' . '</th>
                            <th  width = 100>' . '下单日期' . '</th>
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

  if  ($myrow['chuanghu_fucai_item']=='' or $myrow['chuanghu_fucai_item']=='NULL') {
			$chuanghu_fucai_item='';			
			} else {
				$chuanghu_fucai_item=$myrow['chuanghu_fucai_item'];
			}


			if  ($myrow['chuanghu_bu_item']=='' or $myrow['chuanghu_bu_item']=='NULL') {
			$chuanghu_bu_item='';
			} else {
				$chuanghu_bu_item=$myrow['chuanghu_bu_item'];
			}

			if  ($myrow['chuanghu_sha_item']=='' or $myrow['chuanghu_sha_item']=='NULL') {
			$chuanghu_sha_item='';
			} else {
				$chuanghu_sha_item=$myrow['chuanghu_sha_item'];
			}
   
            echo '<td><input type="checkbox" name="status' . $myrow['order_line_id'] . '" /></td>
		<td><font color="red">' . $myrow['order_number'] . '</font></td>';
           echo '<td>' . $myrow['line_no'] . '</td>';
            echo '<td>' . $chuanghu_bu_item . ' </td>';
            echo '<td>' . $myrow['chuanghu_bu_quantity'] . ' </td>';
            echo '<td>' . $chuanghu_sha_item . ' </td>';
            echo '<td>' . $myrow['chuanghu_sha_quantity'] . ' </td>';
            echo '<td>' . $chuanghu_fucai_item . ' </td>';
            echo '<td>' . $myrow['chuanghu_fucai_quantity'] . ' </td>';
            echo '<td>' . date('Y-m-d', $myrow['creation_date']) . ' </td>
                <input type="hidden" class="number"   name="line_no' . $myrow['order_line_id'] . '" size="12" maxlength="25"  value="' . $myrow['line_no'] . '" />

            
        </tr>';
            $RowCounter++;
            If ($RowCounter == 500) {
                $RowCounter = 1;
                echo $tableheader;
            }
        }
        echo '</table> ';


        echo '</div>';
        echo '<div class="centre">
			<input type="submit" name="Submit" value="' . _('Enter Information') . '" />
		</div>
          </form>';
    }
}
//}
include('includes/footer.inc');
?>