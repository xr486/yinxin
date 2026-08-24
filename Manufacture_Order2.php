<?php

ob_start();
include('includes/session.inc');
if (isset($_GET['identifier'])) {
    $_POST['identifier'] = $_GET['identifier'];
}

if (!isset($_POST['identifier'])) {
    $identifier = date('U');
} else {
    $identifier = $_POST['identifier'];
}
if (isset($_GET['Updateso_num'])) {
    $Updateso_num = $_GET['Updateso_num'];
} else {
    $Updateso_num = '';
}
$Title = _('新建生产单');
$ViewTopic = '新建生产单';
$BookMark = '新建生产单';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/CountriesArray.php');

echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/customer.png" title="' . _('订单') .
 '" alt="" />' . ' ' . _('新建生产单') . '
	</p>';
if (isset($Updateso_num) and $Updateso_num != '') {
    //CreditLimit,
    $sql2 = "SELECT
	a.order_number,
	a.status,
	b.realname,
	a.order_amount,
	a.description note,
	a.schedule_ship_date,
	a.creation_date,
        b.address
FROM
	sf_orders_all a,
	www_users b
WHERE
	1 = 1
AND a.created_by = b.userid
and a.order_number = '" . $Updateso_num . "'";
    $result2 = DB_query($sql2, $db);
    $myrow = DB_fetch_array($result2);
    $_POST['order_number'] = $myrow['order_number'];
    $_POST['status'] = $myrow['status'];
    $_POST['note'] = $myrow['note'];
    $_POST['order_amount'] = $myrow['order_amount'];
    $_POST['realname'] = $myrow['realname'];
    $_POST['schedule_ship_date'] = $myrow['schedule_ship_date'];
    $_POST['create_date'] = $myrow['creation_date'];
    $_POST['address'] = $myrow['address'];
    if (!isset($_GET['delete'])) {
        echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">'
        . '<input type="hidden" name = "identifier" value ="' . $identifier . '">';
        echo '<div>';
        echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
        $v_date = Date('Y-m-d');
        echo '<table class="selection" id="SignFrame">
                <tr class="OddTableRows">
				<td width = 90>' . _('发注者') . ':</td>
				<td width = 250> ' . $_POST['realname'] . ' </td>
                     <td  width = 90>' . _('订单号') . ':</td>
				<td width = 150>' . $_POST['order_number'] . '</td>
                                <input  type="hidden" name="order_number"  value="' . $_POST['order_number'] . '" />
			</tr>
                       <tr class="OddTableRows">
				
			
				<td>' . _('地址') . ':</td>
				<td  colspan="3"> ' . $_POST['address'] . ' </td>
			</tr>';

        $v_schedule_ship_date = date('Y-m-d', $_POST['schedule_ship_date']);

        echo'
			<tr class="OddTableRows" >
				<td>' . _('受注日') . ':</td>
				<td> ' . $v_date . ' </td>
				<td>' . _('担当者') . ':</td>
				<td  width = 150> ' . $_SESSION['UserID'] . ' </td>
			</tr>
			
                        <tr class="OddTableRows">
				<td>' . _('希望交货期') . ':</td>
				<td > ' . $v_schedule_ship_date . ' </td>
                                <td>' . _('实际交货期') . ':</td>
                                <td > ' . $v_schedule_ship_date . ' </td>   
				
			</tr>
                       ';//<td><input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="ShipDate" maxlength="10" size="11" value="" /></td>
        echo '</table>';
        echo '<br />';
        $sql2 = "SELECT
	a.line_no,
        type,
	a.item,
	a.chuanghu_height,
	a.chuanghu_width,
	a.chuanghu_name,
	a.chuanghu_quantity,
  b.yanse,
  order_line_id
FROM
	shengchan_v a,
	sf_item_no b
WHERE
	a.item = b.item_no
        and order_number = '" . $Updateso_num . "'";
        $sql2 = $sql2 . " order by line_no,type";
        $result2 = DB_query($sql2, $db);
        if (DB_num_rows($result2) == 0) {
            unset($result2);
            prnMsg(_('没有找到订单详细信息，请重新登录查询！'), 'info');
        } else {
            echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '"><input type="hidden" name = "identifier" value ="' . $identifier . '">';
            echo '<div>';
            echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
            echo '<table class="selection" align="center" >';
            $tableheader = '<tr class="OddTableRows">
	                           
                                       
                                        <th  width =120>' . '安置场所' . '</th>
                                        <th  width =100>' . '料号' . '</th>
                                        <th  width =40>' . '色' . '</th>
                                        <th  width =60>' . '宽(MM)' . '</th>
                                        <th width =60 >' . '长(MM)' . '</th>
					<th width =30 >' . '褶' . '</th>               
                                        <th  width =60>' . '数量' . '</th>
                                        <th width =40 >' . '挂钩' . '</th> 
                                       <th width =70 >' . '开关方向' . '</th>
                                       <th width =40 >' . '下摆' . '</th>                                
                                       <th width =50 >' . '芯衬' . '</th>
                                       <th width =50 >' . '倍率' . '</th>
                                       <th width =40 >' . '绑带' . '</th>
                                      <th width =60 >' . '定型' . '</th>
                                       
				</tr>';
            echo $tableheader;
            $RowCounter = 1;
            $k = 0; //row colour counter
            while ($myrow = DB_fetch_array($result2)) {
                if ($k == 1) {
                    echo '<tr class="OddTableRows">'; //EvenTableRows
                    $k = 0;
                } else {
                    echo '<tr class="OddTableRows">';
                    $k++;
                }
                if ($myrow['type'] == 'A') {
                    $v_type = '布';
                } else {
                    $v_type = '纱';
                }//<td><input type="text" class="number" name="kaiguan[]" value="1"size="3" maxlength="5" /></td>
                echo '
		     
                      <td>' . $myrow['chuanghu_name'] . "(" . $v_type . ")" . '</td>
                      <td>' . $myrow['item'] . '</td>
                      <td>' . $myrow['yanse'] . '</td>
                      <td>' . $myrow['chuanghu_height'] * 1000 . '</td>
                      <td>' . $myrow['chuanghu_width'] * 1000 . '</td>
                      <td><input type="text" class="number" name="zhe[]" value="3" size="3" maxlength="5"/></td>
                      <td>' . $myrow['chuanghu_quantity'] . '</td>
                      <td><select name="guagou[]">
                     <option  selected="selected" value="A">A</option>
                     <option   value="B">B</option>
                     </select></td>
                     
                     <td><select name="kaiguan[]">
                     <option  selected="selected" value="1">片左</option>
                     <option   value="1">片右</option>
                     <option   value="2">两</option>
                     </select></td>


                       <td>' . '100+100' . '</td>
                      <td><input type="text" class="number" name="xinchen[]" value="90" size="3" maxlength="5" /></td>
                       <td><input type="text" class="number" name="beilv[]" value="2"size="3" maxlength="5" /></td>
                       <td><input type="text" class="number" name="bangdai[]" value="2" size="3" maxlength="5" /></td>
                      <td><select name="dingxing[]">
                     <option  selected="selected" value="Y">有锅</option>
                     <option   value="N">无锅</option>
                     </select></td>
                      <input type="hidden" name="type[]" value="' . $myrow['type'] . '" />
                       <input type="hidden" name="order_line_id[]" value="' . $myrow['order_line_id'] . '" />
                          
        </tr>';
                $RowCounter++;
                If ($RowCounter == 500) {
                    $RowCounter = 1;
                    echo $tableheader;
                }
            }
            echo '</table> 
</div>
                
          </form>';
        }

        echo '<br />
                         ';
        echo '</br><div class="centre">
			<input type="submit" name="Submit" value="' . _('Enter Information') . '" />
		&nbsp;&nbsp;&nbsp;&nbsp;     <input type="submit" name="return" value="' . "返回上一层" . '" />&nbsp;
                                 
</div>';
    }
    echo '</div>
          </form>';
}
if (isset($_POST['Submit'])) {

    isset($_SESSION['num' . $identifier]) or die("no session");
    if ($_SESSION['num' . $identifier] == 400) {
        $_SESSION['num' . $identifier] = 500;
        $InputError = 0;
        for ($i = 0; $i < count($_POST["zhe"]); $i++) {
            if ($_POST["zhe"][$i] != '') {
                if ($_POST["kaiguan"][$i] != '') {
                    if ($_POST["xinchen"][$i] != '') {
                        if ($_POST["beilv"][$i] != '') {
                            if ($_POST["bangdai"][$i] != '') {
                                $InputError = 0;
                            } else {
                                $msg = '绑带不能为空！';
                                $InputError = 1;
                            }
                        } else {
                            $msg = '倍率不能为空！';
                            $InputError = 1;
                        }
                    } else {
                        $msg = '芯衬不能为空！';
                        $InputError = 1;
                    }
                } else {
                    $msg = '开关不能为空！';
                    $InputError = 1;
                }
            } else {
                $msg = '褶不能为空！';
                $InputError = 1;
            }
        }
        if ($InputError == 1) {
           
            prnMsg($msg, 'error');
            echo '<br /><div class="centre"><a a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?identifier=' . $identifier . '">' . _('返回重新入库') . '</a></div>';
        } else {
            for ($i = 0; $i < count($_POST["zhe"]); $i++) {
                    DB_Txn_Begin($db);
                    $v_date = strtotime(Date('Y-m-d H:i:s'));
                    if($_POST["type"][$i]=='A'){
                        $sql = "update sf_order_lines_all
                                   set buzhe         =  '" . $_POST["zhe"][$i]  . "',
                                       buguagou      =  '" . $_POST["guagou"][$i]  . "',
                                       bukaiguan     =  '" . $_POST["kaiguan"][$i]  . "', 
                                       buxinchen     =  '" . $_POST["xinchen"][$i]  . "',
                                       bubeilv       =  '" . $_POST["beilv"][$i]  . "',
                                       bubangdai     =  '" . $_POST["bangdai"][$i]  . "',
                                       budingxing    =  '" . $_POST["dingxing"][$i]  . "',
                                       bumanufacture_flag = 'A',
                                       last_updated_by = '" . $_SESSION['UserID'] . "',
                                       last_update_date = $v_date
                                where  order_line_id =  '" . $_POST["order_line_id"][$i]  . "'"
                            ;
                    }else{
                        $sql = "update sf_order_lines_all
                                   set shazhe      =  '" . $_POST["zhe"][$i]  . "',
                                       shaguagou   =  '" . $_POST["guagou"][$i]  . "',
                                       shakaiguan  =  '" . $_POST["kaiguan"][$i]  . "', 
                                       shaxinchen  =  '" . $_POST["xinchen"][$i]  . "',
                                       shabeilv    =  '" . $_POST["beilv"][$i]  . "',
                                       shabangdai  =  '" . $_POST["bangdai"][$i]  . "',
                                       shadingxing =  '" . $_POST["dingxing"][$i]  . "',
                                       shamanufacture_flag = 'A',
                                       last_updated_by = '" . $_SESSION['UserID'] . "',
                                       last_update_date = $v_date
                                       
                              where  order_line_id =  '" . $_POST["order_line_id"][$i]  . "' ";
                    }
                    //echo $_POST["type"][$i];
                    $result = DB_query($sql, $db);
                    DB_Txn_Commit($db);
              
            }

            $msg = '新建生产单成功';
            prnMsg($msg, 'success');
           echo '<br /><div class="centre"><a href="' . $RootPath . '/Manufacture_Order.php">' . _('继续建生产单') . '</a></div>';
        }
        unset($SelectedMeasureID);
        unset($_POST['order_line_id']);
        unset($_POST['dingxing']);
        unset($_POST['bangdai']);
        unset($_POST['beilv']);
        unset($_POST['kaiguan']);
        unset($_POST['xinchen']);
        unset($_POST['guagou']);
        unset($_POST['']);
       
    }
} else {
    session_start() or die("session is not started");
    $_SESSION['num' . $identifier] = 400;
}
if (isset($_POST['return'])) {
//    echo 'AAAAAAAAAA';
    header('Location: Manufacture_Order.php');
}
include('includes/footer.inc');
?>
