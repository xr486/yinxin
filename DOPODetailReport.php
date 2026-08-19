<?php

ob_start();
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include('includes/session.inc');
$Title = _('报价申请明细报表');
$ViewTopic = '报价申请明细报表';
$BookMark = '报价申请明细报表';

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

unset($result);


if (isset($_POST['Go1']) OR isset($_POST['Go2'])) {
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
 

if (isset($_POST['Search']) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])) {
    $sql = "SELECT b.customer_code,a.*
	 from quote_requests_all a, 
	 customers b 
	where  a.customer_code=b.customer_code 
";

  if (isset($_POST['order_number']) and $_POST['order_number'] != '') { 
        $sql = $sql . " and a.request_name " . LIKE . " '%" . $_POST['order_number'] . "%' ";
    }

    if (isset($_POST['request_person']) and $_POST['request_person'] != '') { 
        $sql = $sql . " and a.request_person " . LIKE . " '%" . $_POST['request_person'] . "%' ";
    } 
    if (isset($_POST['customer_code']) and $_POST['customer_code'] != '') { 
        $sql = $sql . " and b.customer_code " . LIKE . " '%" . $_POST['customer_code'] . "%' ";
    }
    if (isset($_POST['customer_name']) and $_POST['customer_name'] != '') { 
        $sql = $sql . " and b.customer_name " . LIKE . " '%" . $_POST['customer_name'] . "%' ";
    }
     
    if(isset($_POST['FromDate']) and $_POST['FromDate'] != ''){
          $sql = $sql." and a.request_date >=".strtotime($_POST['FromDate'])." ";
      }
       if(isset($_POST['ToDate']) and $_POST['ToDate'] != ''){
          $sql = $sql." and a.request_date <=".strtotime($_POST['ToDate'])." ";
      }

    $result = DB_query($sql, $db);
    if (DB_num_rows($result) == 0) {
        unset($result);
        prnMsg(_('找不到待签核订单，请重新输入条件查询！'), 'error');
    }
}


echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('报价申请明细报表') . '</p>';
echo '<table cellpadding="3" class="selection">';
echo '<tr><td >' . _('申请单号') . ':</td><td>';

echo '<input type="text" name="order_number"   value="' . $_POST['order_number'] . '" size="10" maxlength="25" />';
echo ' <td >' . _('客户名称') . ':</td><td>';

echo '<input type="text" name="customer_name"   value="' . $_POST['customer_name'] . '" size="10" maxlength="25" />
    ';
    echo ' <td >' . _('客户编号') . ':</td><td>';

echo '<input type="text" name="customer_code"   value="' . $_POST['customer_code'] . '" size="5" maxlength="25" />
     ';
echo '</td>';
 

     echo ' <td >' . _('申请人员') . ':</td><td>';

echo '<input type="text" name="request_person"   value="' . $_POST['request_person'] . '" size="10" maxlength="25" />
     ';
echo '</td>'; 
echo '</tr>';
 if (!isset($_POST['FromDate'])) {
    $_POST['FromDate'] = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 30, date("Y")));
}
if (!isset($_POST['ToDate'])) {
    $_POST['ToDate'] = Date('Y-m-d', mktime(0, 0, 0, date("m"), date("d") + 15, date("Y")));
}
echo '<td  >' . '需求日期' . _('From') . ':</td>
		<td><input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="FromDate" maxlength="10" size="11" value="' . $_POST['FromDate'] . '" /></td>
		<td>' . _('To') . ':</td>
		<td><input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="ToDate" maxlength="10" size="11" value="' . $_POST['ToDate'] . '" /></td>
	</tr>';

 
     

echo '</table><div class="centre"><input type="submit" name="Search" value="查询申请"></div>';

if (isset($result) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])) {
    $ListCount = DB_num_rows($result);
    $ListPageMax = ceil($ListCount / 10);

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
    echo '<input type="hidden" name="PageOffset" value="' . $_POST['PageOffset'] . '" />';
    if ($ListPageMax > 1) {
        echo '<br /><div class="centre">&nbsp;&nbsp;' . _('第') . '' . $_POST['PageOffset'] . ' ' . _('页，共') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ': ';
        echo '<select name="PageOffset1">';
        $ListPage = 1;
        while ($ListPage <= $ListPageMax) {
            if ($ListPage == $_POST['PageOffset']) {
                echo '<option value="' . $ListPage . '" selected="selected">' . $ListPage . '</option>';
            } else {
                echo '<option value="' . $ListPage . '">' . $ListPage . '</option>';
            }
            $ListPage++;
        }
        echo '</select>
                    <input type="submit" name="Go1" value="' . _('Go') . '" />
                    <input type="submit" name="Previous" value="' . _('Previous') . '" />
                    <input type="submit" name="Next" value="' . _('Next') . '" />';
        echo '</div>';
    }
    echo '
                    <table cellpadding="2" class="selection" >';

    echo '<tr>
                    <th class="ascending" width = 60>' . _('客户') . '</th>	
                   <th class="ascending" >' . _('申请单号') . '</th>				
					<th class="ascending" >' . _('申请人员') . '</th>
					<th class="ascending" >' . _('需求参考料号') . '</th>
					<th class="ascending" >' . _('需求描述') . '</th>
					 <th class="ascending" >' . _('需求日期') . '</th>	
					 <th class="ascending" >' . _('接收日期') . '</th>	
					 <th class="ascending" >' . _('完成日期') . '</th>	
					 <th  >' . _('成品料号') . '</th>	
					  <th  >' . _('备注') . '</th>
					  <th  >' . _('完成人') . '</th>
            </tr>';
    $k = 0; //row counter to determine background colour
    $RowIndex = 0;



    if (DB_num_rows($result) <> 0) {
        DB_data_seek($result, ($_POST['PageOffset'] - 1) * 10);
        $i = 0; //counter for input controls
        while (($myrow = DB_fetch_array($result)) AND ( $RowIndex <> 10 )) {
            if ($k == 1) {
                echo '<tr class="EvenTableRows">';
                $k = 0;
            } else {
                echo '<tr class="OddTableRows">';
                $k = 1;
            }
            unset($v_status);
            // p($myrow);
			unset($finish_date);
            if ($myrow['finish_date']>0) {
				$finish_date=date('Y-m-d', $myrow['finish_date']);
				}
				unset($receipt_date);
            if ($myrow['receipt_date']>0) {
				$receipt_date=date('Y-m-d', $myrow['receipt_date']);
				}

              echo '  <td>' . $myrow['customer_code'] . '</td>
			    <td>' . $myrow['request_name'] . '</td>
				<td>' . $myrow['request_person'] . '</td>
			    <td>' . $myrow['reference_item'] . '</td>
			    <td>' . $myrow['request_remark']  . '</td>
			   <td>' .  date('Y-m-d', $myrow['request_date']) . '</td> 
			   <td>' . $receipt_date  . '</td>
			   <td>' . $finish_date  . '</td>
			   
			    <td>' . $myrow['finish_item']  . '</td>
				
			    <td>' . $myrow['remark']  . '</td>
				<td>' . $myrow['finish_by']  . '</td>
                ';


            echo '
			</tr>';
            $i++;
            $RowIndex++;
            //end of page full new headings if
        } //end loop through vendors
        echo '</table>';
        echo '<input type="hidden" name="JustSelectedAvendor" value="Yes" />';
    }

    if (isset($ListPageMax) AND $ListPageMax > 1) {
        echo '<br /><div class="centre">&nbsp;&nbsp;' . _('第') . '' . $_POST['PageOffset'] . ' ' . _('页，共') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ': ';
        echo '<select name="PageOffset2">';
        $ListPage = 1;
        while ($ListPage <= $ListPageMax) {
            if ($ListPage == $_POST['PageOffset']) {
                echo '<option value="' . $ListPage . '" selected="selected">' . $ListPage . '</option>';
            } else {
                echo '<option value="' . $ListPage . '">' . $ListPage . '</option>';
            }
            $ListPage++;
        }
        echo '</select>
                        <input type="submit" name="Go2" value="' . _('Go') . '" />
                        <input type="submit" name="Previous" value="' . _('Previous') . '" />
                        <input type="submit" name="Next" value="' . _('Next') . '" />';
        echo '</div>';
    }
}
echo '</div></form>';

include('includes/footer.inc');
