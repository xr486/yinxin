<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

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
$Title = _('查询生产单');
$ViewTopic = '查询生产单';
$BookMark = '查询生产单';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/CountriesArray.php');

echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/customer.png" title="' . _('生产单') .
 '" alt="" />' . ' ' . _('查询生产单') . '
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
	line_no,
	type,
	order_number,
	chuanghu_name,
	chuanghu_width,
	chuanghu_height,
	chuanghu_quantity,
	item,
	order_line_id,
	bangdai,
	beilv,
	dingxing,
	guagou,
	kaiguan,
	xinchen,
	zhe,yanse
FROM
	search_shengchan a,
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
                                        <th width =60 >' . '打印' . '</th>
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
                }
                 if ($myrow['dingxing'] == 'Y') {
                    $v_dingxing = '有锅';
                } else {
                    $v_dingxing = '无锅';
                }
                echo '
		     
                      <td>' . $myrow['chuanghu_name'] . "(" . $v_type . ")" . '</td>
                      <td>' . $myrow['item'] . '</td>
                      <td>' . $myrow['yanse'] . '</td>
                      <td>' . $myrow['chuanghu_height'] * 1000 . '</td>
                      <td>' . $myrow['chuanghu_width'] * 1000 . '</td>
                      <td>' . $myrow['zhe']  . '</td>
                      <td>' . $myrow['chuanghu_quantity'] . '</td>
                      <td>' . $myrow['guagou'] . '</td>
                      <td>' . $myrow['kaiguan'] . '</td>  
                      <td>' . '100+100' . '</td>
                      <td>' . $myrow['xinchen'] . '</td>  
                      <td>' . $myrow['beilv'] . '</td>  
                      <td>' . $myrow['bangdai'] . '</td>  
                      <td>' . $v_dingxing . '</td>  
                      <td><a href="' . $RootPath . '/PrintPDF.php?OrderNum=' . $myrow['type'].$myrow['order_line_id'] . '">' . '打印' . '</td>  
                      <input type="hidden" name="type[]" value="' . $myrow['type'] . '" />
                          
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
	
		  <input type="submit" name="return" value="' . "返回上一层" . '" />&nbsp;
                                 
</div>';
    }
    echo '</div>
          </form>';
}


if (isset($_POST['return'])) {
//    echo 'AAAAAAAAAA';
    header('Location: SearchManufacture.php');
}

include('includes/footer.inc');
?>
