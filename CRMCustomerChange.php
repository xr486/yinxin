<?php
include ('includes/session.inc');
$Title = _('查询客户');

$ViewTopic = '查询客户';
$BookMark = '查询客户';

include ('includes/header.inc');
include ('includes/SQL_CommonFunctions.inc');

unset($result);

if (isset($_POST['Go1']) or isset($_POST['Go2'])) {
    $_POST['PageOffset'] = (isset($_POST['Go1']) ? $_POST['PageOffset1'] : $_POST['PageOffset2']);
    $_POST['Go'] = '';
}
if (!isset($_POST['PageOffset'])) {
    $_POST['PageOffset'] = 1;
} else {
    if ($_POST['PageOffset'] == 0) {
        $_POST['PageOffset'] = 1;
    }
}
if (!isset($_POST['identifier'])) {
    $identifier = date('U');
} else {
    $identifier = $_POST['identifier'];
}
if (isset($_POST['Submit'])) {
    isset($_SESSION['num' . $identifier]);
     $sql7 = " select salestype from www_users where userid= '" . $_SESSION['UserID'] .
            "' "; //SQL排序
        $result7 = DB_query($sql7, $db);
        $myrow7 = DB_fetch_array($result7);
         if ($myrow7['salestype'] == '管理员') {
        prnMsg(_('管理员不得新建客户'), 'error');
    } elseif ($_SESSION['num' . $identifier] = 400) {
        $_SESSION['num' . $identifier] = 500;
        $InputError = 0;
        $checkQty = 0;
        $count = 0;
        //        $ErrPO_line='';
        DB_Txn_Begin($db);
        $v_date = strtotime(Date('Y-m-d H:i:s'));
        $date = date('Ymd');

        foreach ($_POST as $key => $value) {
            if (mb_substr($key, 0, 6) == 'status') {
                $count = $count + 1;
                $id_code = mb_substr($key, 6);
                $n = strpos($id_code, '_');
                if ($n) {
                    $customer_id = substr($id_code, 0, $n);
                    $customer_code = substr($id_code, $n + 1);
                    //                echo $po_num;
                    //                echo $line;
                    $receive_date = Date('Y-m-d H:i:s');
                    //                echo $vendor;
                    $locname = $_POST['locName' . $id_code];
                    $sql0 = "select current_salesman from customers where customer_id='" . $customer_id .
                        "' ";
                    $result0 = DB_query($sql0, $db);
                    $myrow0 = DB_fetch_array($result0);
                    $sql1 = "insert into assign_log (customer_id,new_sales,old_sales,last_updated_date,last_updated_by) VALUES
            ('" . $customer_id . "','" . $locname . "','" . $myrow0['current_salesman'] .
                        "','" . $v_date . "','" . $_SESSION['UserID'] . "') ";
                    $result1 = DB_query($sql1, $db);
                    $sql8 = "update customers set  current_salesman ='" . $locname .
                        "' where   customer_id='" . $customer_id . "' ";
                    $result8 = DB_query($sql8, $db);
                }
            }
        }
        DB_Txn_Commit($db);
        prnMsg(_('修改成功！' . $key), 'success');
    } else
        prnMsg(_('请选择要修改的客户'), 'error');
}

if (isset($_POST['Search']) or isset($_POST['Go']) or isset($_POST['Next']) or
    isset($_POST['Previous'])) {
    //日期格式化为SQL格式
    $SQL_FromDate = FormatDateForSQL($_POST['FromDate']);
    $SQL_ToDate = FormatDateForSQL($_POST['ToDate']);
    //查找数据的SQL
    $sql = 'select * from customers where 1=1 ';

    //SQL添加条件
    if (isset($_POST['customer_code']) and $_POST['customer_code'] != '') {
        $sql = $sql . " and customer_code like '%" . $_POST['customer_code'] . "%' ";
    }
    if (isset($_POST['customer_name']) and $_POST['customer_name'] != '') {
        $sql = $sql . " and customer_name like '%" . $_POST['customer_name'] . "%' ";
    }
    if (isset($_POST['resource_name']) and $_POST['resource_name'] != '') {
        $sql = $sql . " and personal_resource = '" . $_POST['resource_name'] . "' ";
    }

    if (isset($_POST['customers_type']) and $_POST['customers_type'] != '') {
        $sql = $sql . " and customers_type like '%" . $_POST['customers_type'] . "%' ";
    }
    if (isset($_POST['salesmancode']) and $_POST['salesmancode'] != '') {
        $sql = $sql . " and salesmancode like '%" . $_POST['salesmancode'] . "%' ";
    }


    $sql .= " ORDER BY customer_name   "; //SQL排序
    $result = DB_query($sql, $db);
    if (DB_num_rows($result) == 0) {
        unset($result);
        prnMsg(_('找不到数据，请重新输入条件查询！'), 'error');
    }
}
?>
<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/magnifier.png" title="查询客户" alt="查询客户">查询客户</p>
<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES,
'UTF-8'); ?>" method ="POST">
<input type="hidden" name = "identifier" value ="<?php echo $identifier; ?>">';
    <div>
        <input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
        <table cellpadding="3" class="selection">
            <tr>	
				<td>客户代码</td>
				<td><input ' . (in_array('customer_code', $Errors) ? 'class="inputerror"' : '' ) . ' type="text" name="customer_code"  autofocus="autofocus" value="<?= $_POST['customer_code'] ?>" size="16" maxlength="40" /></td>

				<td>客户名称</td>
				<td><input ' . (in_array('customer_name', $Errors) ? 'class="inputerror"' : '' ) . ' type="text" name="customer_name"  autofocus="autofocus" value="<?= $_POST['customer_name'] ?>" size="42" maxlength="40" /></td>
		
            </tr>
            <tr><td>	
                <?php
echo '个人入库来源</td>';
$sql = "SELECT resource_name FROM crm_personal_resource ";
$result1 = DB_query($sql, $db);
echo '<td><select name="resource_name">
    <option value=""> </option>';
while ($Salesmanrow = DB_fetch_array($result1)) {
    echo '<option value="' . $Salesmanrow['resource_name'] . '">' . $Salesmanrow['resource_name'] .
        '</option>';
}
echo '</select></td>';
?>		<td>性质</td><td>
				<select name="customers_type"> 
                <option value=""> </option>
				<option value="私营企业">私营企业 </option>
                <option value="国有企业"> 国有企业</option>
                <option value="外资企业"> 外资企业</option>
                </select></td>
            </tr>
            <tr>	
                <td>业务员姓名</td>
				<td ><input   type="text" name="salesmancode"  value="<?= $_POST['salesmancode'] ?>" size="10" maxlength="50"/></td>                
            </tr>
        </table>
        <div class="centre"><input type="submit" name="Search" value="查找"></div>

        <?php
if (isset($_POST['Search']) and isset($result) or isset($_POST['Go']) or isset($_POST['Next']) or
    isset($_POST['Previous'])) {
    $ListCount = DB_num_rows($result);
    $ListPageMax = ceil($ListCount / 10); //$_SESSION['DisplayRecordsMax']

    if (isset($_POST['Next'])) {
        if ($_POST['PageOffset'] < $ListPageMax) {
            $_POST['PageOffset'] = $_POST['PageOffset'] + 1;
        }
    }
    if (isset($_POST['Previous'])) {
        if ($_POST['PageOffset'] > 1) {
            $_POST['PageOffset'] = $_POST['PageOffset'] - 1;
        }
    }
?>
            <input type="hidden" name="PageOffset" value=<?= $_POST['PageOffset'] ?> />
            <input type="hidden" name="FormID" value="<?php echo $_SESSION['FormID']; ?>" /> 
            <table cellpadding="2" class="selection" align="center" >
                <tr>
                 
                    <th class="ascending" width = "80" >客户代码</th>
                    <th class="ascending" width = "200" >客户名称</th>
                    <th class="ascending" width = "120" >行业</th>
                    <th class="ascending" width = "120" >个人入库来源</th>
                    <th class="ascending" width = "110" >意向</th>
                    <th class="ascending" width = "80" >类型</th>
                    <th class="ascending" width = "80" >等级</th>
                    <th class="ascending" width = "120" >地区</th>
                    <th class="ascending" width = "100" >初始负责人</th>
                    <th class="ascending" width = "100" >当前负责人</th>
                    <th class="ascending" width = "100" >更改负责人</th>
                    <th class="ascending" width = "200" >备注</th>
                    <th class="ascending" width = "60" >选择</th>
                </tr>
                <?php
    $RowCounter = 1;
    $k = 0; //row colour counter

    while ($myrow = DB_fetch_array($result)) {

        if ($k == 1) {
            echo '<tr class="EvenTableRows">';
            $k = 0;
        } else {
            echo '<tr class="EvenTableRows">';
            $k++;
        }
        //
        echo '<td><input type="checkbox" name="status' . $myrow['customer_id'] . '_' . $myrow['customer_code'] .
            '" /></td>';
        echo '<td>' . $myrow['customer_name'] . '</td>';
        echo '<td><font color="red">' . $myrow['industry'] . '</font></td>';
        echo '<td>' . $myrow['personal_resource'] . ' </td>';
        echo '<td>' . $myrow['intent'] . ' </td>';
        echo '<td>' . $myrow['customers_type'] . ' </td>';
        echo '<td>' . $myrow['customers_level'] . ' </td>';
        echo '<td>' . $myrow['customers_area'] . ' </td>';
        echo '<td>' . $myrow['first_salesman'] . ' </td>';
        echo '<td>' . $myrow['current_salesman'] . ' </td>';
        $_POST['locName'] = $myrow['current_salesman'];
        $sql1 = "SELECT userid FROM www_users ";
        $result1 = DB_query($sql1, $db);
        echo '<td><select name="locName' . $myrow['customer_id'] . '_' . $myrow['customer_code'] .
            '">';
        while ($Salesmanrow = DB_fetch_array($result1)) {
            if (isset($_POST['locName']) and $_POST['locName'] == $Salesmanrow['loccode']) {
                echo '<option  selected="selected" value="' . $Salesmanrow['userid'] . '">' . $Salesmanrow['userid'] .
                    '</option>';
            } else {
                echo '<option value="' . $Salesmanrow['userid'] . '">' . $Salesmanrow['userid'] .
                    '</option>';
            }
        }
        echo '</select></td>';
        echo '<td>' . $myrow['remark'] . ' </td>';
        //            echo' <td width =100>' . date('Y-m-d', $myrow['need_date']) . ' </td>';
        echo '<td><input type="hidden"  name="quantity_rec' . $myrow['customer_id'] .
            '_' . $myrow['customer_code'] . '" value="' . $myrow['quantity_rec'] .
            '"  /></td>';
        echo '<td><input type="hidden"  name="vendor' . $myrow['customer_id'] . '_' . $myrow['customer_code'] .
            '" value="' . $myrow['vendor_code'] . '"  /></td>';
        echo '<td><input type="hidden"  name="c_salesman' . $myrow['customer_id'] . '_' .
            $myrow['customer_code'] . '" value="' . $myrow['stockid'] . '"  /></td>';
        echo '</tr>';

        $RowCounter++;
        if ($RowCounter == 500) {
            $RowCounter = 1;
            echo $tableheader;
        }
    }
    echo '<tr><td colspan="11"><p><input type="checkbox" name="selectall" onclick="checkall(this.form);"/>全选/取消全选</p></td></tr>';
    echo '</table> ';


    echo '</div>';
    echo '<div class="centre">
			<input type="submit" name="Submit" value="' . _('Enter Information') . '" />
                             <input type="submit" name="return" value="' .
        "返回上一层" . '" />
		</div>';
    echo '<script language="javascript" type="text/javascript">';
    echo 'function checkall(thisform){for(var i=0;i<thisform.elements.length;i++){if(thisform.elements[i].type=="checkbox"&&thisform.elements[i].checked==false&&thisform.elements[i].name!="selectall"){thisform.elements[i].checked=true;}else if(thisform.elements[i].type=="checkbox"&&thisform.elements[i].checked==true&&thisform.elements[i].name!="selectall"){thisform.elements[i].checked=false;}} }';
    echo '</script>';
} ?>

    </div>
</form>
<?php
include ('includes/footer.inc');
?>