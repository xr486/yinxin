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
if (isset($_GET['Updatepr_num'])) {
    $Updatepr_num = $_GET['Updatepr_num'];
} else {
    $Updatepr_num = '';
}
$Title = _('请购单信息');
$ViewTopic = '请购单信息';
$BookMark = '请购单信息';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/CountriesArray.php');

echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/customer.png" title="' . _('请购单') .
 '" alt="" />' . ' ' . _('请购单信息') . '
	</p>';
if (isset($Updatepr_num) and $Updatepr_num != '') {
    //CreditLimit,
    $sql = "select pr_num,status,description ,creation_date from pr_headers_all where 1=1 and pr_num = " . "$Updatepr_num";
    $result = DB_query($sql, $db);
    $myrow = DB_fetch_array($result);
    $_POST['pr_num'] = $myrow['pr_num'];
    $_POST['status'] = $myrow['status'];
    $_POST['description'] = $myrow['description'];
    //$_POST['need_date'] = $myrow['need_date'];
    $_POST['creation_date'] = $myrow['creation_date'];
    if (!isset($_GET['delete'])) {
        echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">'
        . '<input type="hidden" name = "identifier" value ="' . $identifier . '">';
        echo '<div>';
        echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
        if ($_POST['status'] == 'INPROCESS') {
            $v_status = '待签核';
        } elseif ($_POST['status'] == 'APPROVED') {
            $v_status = '已签核';
        } elseif ($_POST['status'] == 'REJECTED') {
            $v_status = '已拒签';
        } else {
            $v_status = '已取消';
        }
        echo '<table class="selection" id="SignFrame">
                       <tr class="EvenTableRows">
				<td>' . _('请购单号') . ':</td>
				<td width = 120>' . $_POST['pr_num'] . '</td>
                                <input  type="hidden" name="pr_num"  value="' . $_POST['pr_num'] . '" />
			
				<td>' . _('状态') . ':</td>
				<td width = 120> ' . $v_status . ' </td>
			</tr>';
       // $v_need_date = date('Y-m-d',$_POST['need_date']);
        $v_creation_date = date('Y-m-d H:i:s',$_POST['creation_date']);
        echo'
			<tr >
			<!--
	<td>' . _('需求日') . ':</td>
				<td> ' . $v_need_date . ' </td>
                -->
				<td>' . _('建立日') . ':</td>
				<td  width = 150> ' . $v_creation_date . ' </td>
			</tr>
			<tr >
				<td>' . _('备注') . ':</td>
				<td colspan="3"> ' . $_POST['description'] . ' </td>
			</tr>';
        echo '</table>';
        echo '<br />';
        $sql2 = "SELECT a.line, a.stockid, b.item_desc, a.uom, a.quantity, a.po_num, a.po_line
FROM pr_lines_all a, sf_item_no b
WHERE 1 =1
AND a.stockid = b.item_no
AND pr_num = " . "$Updatepr_num";
        $result2 = DB_query($sql2, $db);
        if (DB_num_rows($result2) == 0) {
            unset($result2);
            prnMsg(_('没有找到请购单详细信息，请重新登录查询！'), 'info');
        } else {
            echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '"><input type="hidden" name = "identifier" value ="' . $identifier . '">';
            echo '<div>';
            echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
            echo '<table class="selection" align="center" >';
            $tableheader = '<tr>
	                           
	                                <th width =100>' . '行' . '</th>
                                        <th  width =120>' . '料号' . '</th>
                                        <th width =250 >' . '料号描述' . '</th>
					<th  width =100>' . '数量' . '</th>
                                        <th width =80 >' . '单位' . '</th>
                                     
				</tr>';
            echo $tableheader;
            $RowCounter = 1;
            $k = 0; //row colour counter
            while ($myrow = DB_fetch_array($result2)) {
                if ($k == 1) {
                    echo '<tr class="EvenTableRows">';
                    $k = 0;
                } else {
                    echo '<tr class="EvenTableRows">';
                    $k++;
                }
//
                echo '<td>' . $myrow['line'] . '</td>
		      <td>' . $myrow['stockid'] . '</td>
                      <td>' . $myrow['item_desc'] . '</td>
                      <td>' . $myrow['quantity'] . '</td>
                      <td>' . $myrow['uom'] . '</td>
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
    echo 'AAAAAAAAAA';
    header('Location: SearchPR.php');
}
include('includes/footer.inc');
?>
