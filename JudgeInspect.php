<?php
/* $Id: PR_Approve.php 4576 2011-05-27 10:59:20Z daintree $ */

include('includes/session.inc');

$Title = _('检验判检');
$ViewTopic = 'Inventory';
$BookMark = 'AuthoriseRequest';
if (isset($_GET['identifier'])) {
    $_POST['identifier'] = $_GET['identifier'];
}

if (!isset($_POST['identifier'])) {
    $identifier = date('U');
} else {
    $identifier = $_POST['identifier'];
}
include('includes/header.inc');
$sql = "select use_flag from user_power where user_id = '".$_SESSION['UserID']."' and function_name = '检验判定' ";
$result = DB_query($sql,$db);
$flag = 0;
while ($myrow = DB_fetch_array($result)){
  $flag = $myrow['use_flag'];
}
if ($flag != 1){
  echo '<meta http-equiv="refresh" content="1; url=' . $RootPath . '/index.php" />';
  echo '<p class="page_title_text">您没有权限，请联系管理员，页面将在2秒后跳转回到主页面...</p>';
  exit;
}
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/transactions.png" title="' . $Title . '" alt="" />' . ' ' . $Title . '</p>';
if (isset($_POST['UpdateAll'])) {

    isset($_SESSION['num' . $identifier]) or die("no session");
    if ($_SESSION['num' . $identifier] == 400) {
        $_SESSION['num' . $identifier] = 500;
        DB_Txn_Begin($db);
        foreach ($_POST as $key => $value) {
            if (mb_substr($key, 0, 6) == 'status') {
                $Index = mb_substr($key, 6);
                if ($value == 'Y') {
                    $sql = " UPDATE carrequestlines
					SET accept_flag='Y',
                                            accept_flag_by= '" . $_SESSION ['UserID'] . "',
                                            accept_flag_date='" . Date('Y-m-d H:i:s') . "',
                                            last_update_date='" . Date('Y-m-d H:i:s') . "',
                                            last_updated_by= '" . $_SESSION['UserID'] . "',
                                            note='" . $_POST ['note' . $Index] . "',
                                            status ='IN'
					WHERE car_line_id='" . $Index . "'";

                    $result = DB_query($sql, $db);
                } elseif ($value == 'N') {
                    $RequestNo = mb_substr($key, 6);
                    $sql = "UPDATE carrequestlines
					SET accept_flag='N',
                                            accept_flag_by= '" . $_SESSION ['UserID'] . "',
                                            accept_flag_date='" . Date('Y-m-d H:i:s') . "',
                                            last_update_date='" . Date('Y-m-d H:i:s') . "',
                                            last_updated_by= '" . $_SESSION['UserID'] . "',
                                            note='" . $_POST ['note' . $Index] . "',
                                            status ='IN'
					WHERE car_line_id='" . $Index . "'";
                    $result = DB_query($sql, $db);
                }
               // echo $_POST ['note' . $Index];
               // echo $_POST ['car_header_code' . $Index];
               // echo '111';
                $checksql = "select count(*) from carrequestlines  
                    where status is  null
                    and car_header_code  =  '" . $_POST ['car_header_code' . $Index] . "'";
                $checkresult = DB_query($checksql, $db);
                $myrow = DB_fetch_row($checkresult);
                if ($myrow[0] <= 0) {
                    $updatecar = "update cars set status='FREE' where car_id=  '" . $_POST ['car_no' . $Index] . "'";
                    $carresult = DB_query($updatecar, $db);
                    $updated = "update drivers set status='FREE'where driver_id=  '" . $_POST ['driver_no' . $Index] . "'";
                    $dresult = DB_query($updated, $db);
                }
            }
        }


        DB_Txn_Commit($db);
        prnMsg(_('检验状态修改成功'), 'success');
    }
} else {
    session_start() or die("session is not started");
    $_SESSION['num' . $identifier] = 400;
}

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">'
 . '<input type="hidden" name = "identifier" value ="' . $identifier . '">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
// <td>' . _('Type') . ':</td> 20
echo '<table class="selection">
		<tr>	
			<td>' . '派车单号' . ':</td>
			<td><input   type="text" name="car_header_code" value="' . $_POST['car_header_code'] . '" /></td>';
if (!isset($_POST['FromDate'])) {
    $_POST ['FromDate'] = Date($_SESSION['DefaultDateFormat'], mktime(0, 0, 0, Date('m'), -30, Date('Y')));
}
if (!isset($_POST['ToDate'])) {
    $_POST['ToDate'] = Date($_SESSION['DefaultDateFormat']);
}
echo '<td>' . '料号' . ':</td>
			<td><input  type="text" name="item_no" value="' . $_POST ['item_no'] . '" /></td>
   
  <td>' . '客户名' . ':</td>
    <td><input   type="text" name="customername" value="' . $_POST ['customername'] . '" /></td>'
 . '</tr><tr>'
 . '<td>' . _('创建日期 从') . ':</td>
		<td><input type="text" class="date" alt="' . $_SESSION['DefaultDateFormat'] . '" name="FromDate" maxlength="10" size="11" value="' . $_POST ['FromDate'] . '" /></td>
		<td>' . _('To') . ':</td>
		<td><input type="text" class="date" alt="' . $_SESSION ['DefaultDateFormat'] . '" name="ToDate" maxlength="10" size="11" value="' . $_POST['ToDate'] . '" /></td>
	</tr>
	</table>
	<br />
	<div class="centre">
		<input type="submit" name="ShowResults" value="' . '查询' . '" />
	</div>
	<br />
    </div>
	</form>';
//-------------------------------------------------------------------------------
//if (isset($_POST['ShowResults'])) {
$SQL_FromDate = FormatDateForSQL($_POST['FromDate']);
$SQL_ToDate = FormatDateForSQL($_POST['ToDate']);
$sql = '     select  b.car_line_id,
                   a.car_header_code,
                   a.car_no,
                   a.driver_no,
                   b.car_line_num,
                   a.customer_code,
                   a.customer_name,
                   b.item_no,
                   b.disposal_type,
	           b.item_desc,
                   b.uom,
                   b.quantity
              from carrequestlines  b ,
                   carrequestheaders a
             where 1=1
             and a.status = \'IN\' ';
$sql.= " and a.car_header_code=b.car_header_code and b.accept_flag is null ";
if (empty($_POST['car_header_code']) == 0) {
    $sql .= " AND a.car_header_code= '" . $_POST['car_header_code'] . "'";
}
if (empty($_POST['item_no']) == 0) {
    $sql .= " AND b.item_no like '%" . $_POST['item_no'] . "%" . "'";
}

if (empty($_POST['customername']) == 0) {
    $sql .= " AND a.customer_name like  '%" . $_POST['customername'] . "%" . "'";
}
if (Is_Date($_POST['FromDate'])) {
    $sql .= " and a.creation_date >='" . $SQL_FromDate . "' ";
} if (Is_Date($_POST['ToDate'])) {
    $sql .= " and a.creation_date <=adddate('" . $SQL_ToDate . "',1) ";
}
$sql.=" order by a.car_header_code, b.car_line_num";
$result = DB_query($sql, $db);
if (DB_num_rows($result) == 0) {
    unset($result);
    prnMsg(_('没有找到需要判检派车单，请重新输入条件查询！'), 'info');
} else {
    echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">'
    . '<input type="hidden" name = "identifier" value ="' . $identifier . '">';
    echo '<div>';
    echo '<input type="hidden" name="FormID" value="' . $_SESSION ['FormID'] . '" />';
    echo '<table  class="selection" border="1" >';

    /* Create the table for the purchase order header */
    $tableheader = '<tr>
		 <th >' . '派车单号' . '</th>
					<th>' . '行' . '</th>
                                        
					<th  width=100>' . '客户代码' . '</th>
                                        <th width=170>' . '客户名' . '</th>    
					<th>' . '料号' . '</th>
                                        <th width=100>' . '料号描述' . '</th>
					<th >' . '单位' . '</th>
					<th width=50>' . '数量' . '</th>
                                        <th>' . '处置方式' . '</th>
                                        <th>' . '备注' . '</th>   
					<th >' . '需检验' . '</th>
                                        <th >' . '免验' . '</th>
					
	</tr>';
    echo $tableheader;
    $RowCounter = 1;
    $k = 0; //row colour counter
    $count = 0;
    while ($myrow = DB_fetch_array($result)) {
        if ($k == 1) {
            echo '<tr class="EvenTableRows">';
            $k = 0;
        } else {
            echo '<tr class="EvenTableRows">';
            ;
            $k ++;
        }
        $count = $count + 1;
        //<td><input type="radio" name="status1'.$myrow['car_line_id'].'" value="Y"/></td>
        echo'		<td>' . $myrow['car_header_code'] . '</td>
			<td>' . $myrow['car_line_num'] . '</td>
                        
			<td>' . $myrow ['customer_code'] . '</td>
                        <td>' . $myrow['customer_name'] . '</td>
			<td>' . $myrow['item_no'] . '</td>
                        <td>' . $myrow['item_desc'] . '</td>
			<td>' . $myrow ['uom'] . '</td>
			<td>' . $myrow['quantity'] . '</td>
                        <td>' . $myrow['disposal_type'] . '</td>
                        <td><input  type="text" name="note' . $myrow['car_line_id'] . '"  size="14" maxlength="14"  /></td>
                        <input  type="hidden" name="car_no' . $myrow['car_line_id'] . '" value="' . $myrow['car_no'] . '"    />
                        <input  type="hidden" name="driver_no' . $myrow['car_line_id'] . '" value="' . $myrow['driver_no'] . '"   />
                        <input  type="hidden" name="car_header_code' . $myrow['car_line_id'] . '"  value="' . $myrow['car_line_id'] . '" />
			<td><input type="radio" name="status' . $myrow ['car_line_id'] . '" value="Y" /></td>
			<td><input type="radio" name="status' . $myrow['car_line_id'] . '" value="N" /></td>
			<input type="hidden" name="SelectID' . $myrow['car_line_id'] . '" value="' . $myrow['car_line_id'] . '" />
		</td>		';
        $RowCounter++;
        If ($RowCounter == 50) {
            $RowCounter = 1;
            echo $tableheader;
        }
    }
    echo '</table>';
    echo '<br /><div class="centre"><input type="submit" name="UpdateAll" value="' . _('提交') . '" /></div>
      </div>
      </form>';
}

include('includes/footer.inc');
?>