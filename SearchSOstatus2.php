<?php

/* $Id: vendors.php 6338 2013-09-28 05:10:46Z daintree $ */
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
if (isset($_GET['Updateorder_number'])) {
    $Updateorder_number = $_GET['Updateorder_number'];
} else {
    $Updateorder_number = '';
}

$Title = _('订单信息');
$ViewTopic = '订单信息';
$BookMark = '订单信息';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/CountriesArray.php');

echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/customer.png" title="' . _('订单') .
 '" alt="" />' . ' ' . _('订单信息') . '
	</p>';
if (isset($Updateorder_number) and $Updateorder_number != '') {
    //CreditLimit,
   
    $sql2 = "SELECT
	a.order_number,
	a.status,
	b.realname,
	a.order_amount,
	a.description note,
	a.schedule_ship_date,
	a.creation_date
FROM
	sf_orders_all a,
	www_users b
WHERE
	1 = 1
AND a.created_by = b.userid
and a.order_number = '" .$Updateorder_number."'";
    $result2 = DB_query($sql2, $db);
    $myrow = DB_fetch_array($result2);
    $_POST['order_number'] = $myrow['order_number'];
    $_POST['status'] = $myrow['status'];
    $_POST['note'] = $myrow['note'];
    $_POST['order_amount']=$myrow['order_amount'];
    $_POST['realname']=$myrow['realname'];
    $_POST['schedule_ship_date'] = $myrow['schedule_ship_date'];
    $_POST['create_date'] = $myrow['creation_date'];
    if (!isset($_GET['delete'])) {
        echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">'
        . '<input type="hidden" name = "identifier" value ="' . $identifier . '">';
        echo '<div>';
        echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
       if ($myrow['status'] == 'INPROCESS') {
                $v_status = '待签核';
            } elseif ($myrow['status'] == 'APPROVED') {
                $v_status = '待生管签核';
            } elseif ($myrow['status'] == 'P_APPROVED') {
                $v_status = '已签核';
            } elseif ($myrow['status'] == 'REJECTED') {
                $v_status = '已拒签';
            } elseif ($myrow['status'] == 'CANCELED') {
                $v_status = '已取消';
            } else {
                $v_status = '异常';
            }
        echo '<table class="selection" id="SignFrame">
                       <tr class="EvenTableRows">
				<td>' . _('订单号') . ':</td>
				<td width = 120>' . $_POST['order_number'] . '</td>
                                <input  type="hidden" name="order_number"  value="' . $_POST['order_number'] . '" />
			
				<td>' . _('状态') . ':</td>
				<td width = 120> ' . $v_status . ' </td>
			</tr>';
        
        $v_schedule_ship_date = date('Y-m-d',$_POST['schedule_ship_date']);
        $v_create_date = date('Y-m-d H:i:s',$_POST['create_date']);
        echo'
			<tr >
				<td>' . _('需求日') . ':</td>
				<td> ' . $v_schedule_ship_date . ' </td>
				<td>' . _('建立日') . ':</td>
				<td  width = 150> ' . $v_create_date . ' </td>
			</tr>
			<tr >
				<td>' . _('备注') . ':</td>
				<td colspan="3"> ' . $_POST['note'] . ' </td>
			</tr>
                        <tr >
				<td>' . _('加盟商') . ':</td>
				<td colspan="3"> ' . $_POST['realname'] . ' </td>
			</tr>
                        <tr >
				<td>' . _('总额') . ':</td>
				<td width = 150> ' . $_POST['order_amount']. ' </td>
                            
			</tr>';
        echo '</table>';
        echo '<br />';
        $sql2 = "SELECT
	line_no,
	chuanghu_name,
	chuanghu_width,
	chuanghu_height,
	line_amount,
	chuanghu_quantity,
	chuanghu_bu_item,
	chuanghu_bu_quantity,
	chuanghu_fucai_item,
	chuanghu_fucai_quantity,
	chuanghu_sha_item,
	chuanghu_sha_quantity,
  status
FROM
	sf_order_lines_all c
        where order_number = '" .$Updateorder_number."'";
        $result2 = DB_query($sql2, $db);
        if (DB_num_rows($result2) == 0) {
            unset($result2);
            prnMsg(_('没有找到订单详细信息，请重新登录查询！'), 'info');
        } else {
            echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '"><input type="hidden" name = "identifier" value ="' . $identifier . '">';
            echo '<div>';
            echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
            echo '<table class="selection" align="center" >';
             
            $tableheader = '<tr>
	                           
                                        <th width =50 >' . '行' . '</th>
                                            <th width =80 >' . '状态' . '</th>
                                        <th  width =140>' . '窗户名' . '</th>
                                        <th width =50 >' . '高' . '</th>
					 <th  width =50>' . '宽' . '</th>
                                        <th  width =50>' . '数量' . '</th>
                                        <th width =90 >' . '布料号' . '</th> 
                                       <th width =80 >' . '布数量' . '</th>
                                       <th width =100 >' . '纱料号' . '</th>                                
                                       <th width =80 >' . '纱数量' . '</th>
                                       <th width =100 >' . '辅材' . '</th>
                                       <th width =80 >' . '辅材数量' . '</th>
                                      <th width =100 >' . '总计' . '</th>
                                       
				</tr>';
            echo $tableheader;
            $RowCounter = 1;
            $k = 0; //row colour counter
            while ($myrow = DB_fetch_array($result2)) {
                if ($myrow['status'] == 'OK') {
            $v_status = '检测完成';
        } elseif ($myrow['status'] == 'COMPLETED') {
            $v_status = '已入库';
        }  else {
            $v_status = '生产中';
        }
                if ($k == 1) {
                    echo '<tr class="EvenTableRows">';
                    $k = 0;
                } else {
                    echo '<tr class="EvenTableRows">';
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
                echo '
		      <td>' . $myrow['line_no'] . '</td>
                          <td>' . $v_status . '</td>
                      <td>' . $myrow['chuanghu_name'] . '</td>
                      <td>' . $myrow['chuanghu_height'] . '</td>
                      <td>' . $myrow['chuanghu_width'] . '</td>
                      <td>' . $myrow['chuanghu_quantity'] . '</td>
                      <td>' . $chuanghu_bu_item . '</td>
                      <td>' . $myrow['chuanghu_bu_quantity'] . '</td>
                      <td>' . $chuanghu_sha_item . '</td>                      
                      <td>' .$myrow['chuanghu_sha_quantity'] . '</td>                      
                     <td>' . $chuanghu_fucai_item . '</td>
                    <td>' . $myrow['chuanghu_fucai_quantity'] . '</td>
					<td>' . $myrow['line_amount'] . '</td>

        </tr>';
                $RowCounter++;
                If ($RowCounter == 500) {
                    $RowCounter = 1;
                    echo $tableheader;
                }
            }
            echo '</table> ';


            echo '</div>
          </form>';
        }

        echo '<br />
                                <input type="submit" name="return" value="' . "返回上一层" . '" />&nbsp;
                                 
</div>';
    }
    echo '</div>
          </form>';
}
if (isset($_POST['return'])) {
//    echo 'AAAAAAAAAA';
    header('Location: SearchSOstatus.php');
}
include('includes/footer.inc');
?>
