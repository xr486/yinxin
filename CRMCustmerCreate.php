<?php
 ob_start();
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include('includes/session.inc');
	$Title = _('查找客户');

	$ViewTopic= '查找客户';
	$BookMark = '查找客户';

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
if (isset($_POST['Create'])) {
        header("Location: CRMCustmerCreate1.php");
        
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
echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('查找客户') . '</p>';
echo '<table cellpadding="3" class="selection">
 <tr>	
				<td>客户代码</td>
				<td><input   type="text" name="customer_code"  autofocus="autofocus" value="'. $_POST['customer_code'] . '" size="16" maxlength="40" /></td>

				<td>客户名称</td>
				<td><input   type="text" name="customer_name"  autofocus="autofocus" value="'. $_POST['customer_name'] . '" size="42" maxlength="40" /></td>
		
            </tr>
            <tr><td>个人入库来源</td>';
$sql = "SELECT resource_name FROM crm_personal_resource ";
$result1 = DB_query($sql, $db);
echo '<td><select name="resource_name">
    <option value=""> </option>';
while ($Salesmanrow = DB_fetch_array($result1)) {
    echo '<option value="' . $Salesmanrow['resource_name'] . '">' . $Salesmanrow['resource_name'] .
        '</option>';
}
echo '</select></td>
		<td>性质</td><td>
				<select name="customers_type"> 
                <option value=""> </option>
				<option value="私营企业">私营企业 </option>
                <option value="国有企业"> 国有企业</option>
                <option value="外资企业"> 外资企业</option>
                </select></td>
            </tr>
            <tr>	
                <td>业务员姓名</td>
				<td ><input   type="text" name="salesmancode"  value="'. $_POST['salesmancode'] . '" ize="10" maxlength="50"/></td>                
            </tr>
</table><div class="centre"><input type="submit" name="Search" value="查找"> &nbsp;&nbsp; <input type="submit" name="Create" value="创建新客户"></div>';

if (isset($_POST['Search']) and isset($result) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])){
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
    echo '
                    <table cellpadding="2" class="selection" >';

    echo '<tr>
                 <th class="ascending" width = "200" >客户编号</th>
                    <th class="ascending" width = "120" >客户名称</th>
                    <th class="ascending" width = "120" >联系人</th>
                    <th class="ascending" width = "110" >创建人</th>   
                    <th class="ascending" width = "110" >操作</th>                  
            </tr>';
    $k = 0; //row counter to determine background colour
    $RowIndex = 0;
    


    if (DB_num_rows($result) <> 0) {
        DB_data_seek($result, ($_POST['PageOffset'] - 1) * 10);
        $i = 0; //counter for input controls
        while (($myrow = DB_fetch_array($result)) AND ($RowIndex <> 10)) {
			if ($k == 1) {
				echo '<tr class="EvenTableRows">';
				$k = 0;
			} else {
				echo '<tr class="OddTableRows">';
				$k = 1;
			}
		 echo '<td>' . $myrow['customer_code'] . '</td>';
        echo '<td>' . $myrow['customer_name'] . ' </td>';
        echo '<td>' . $myrow['customer_contacts'] . ' </td>';
        echo '<td>' . $myrow['created_by'] . ' </td>';
        if ( $_SESSION['UserID']== $myrow['current_salesman'] ){
            echo '<td><a href="' . $RootPath . '/CRMCustomerInfo.php?customer_id=' . $myrow['customer_id'] . '">查看详细 </td>';
        }

       
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
                        } 
                        else {
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
if (isset($_POST['add_new'])) {
    header('Location: AddSupplier.php');
}
include('includes/footer.inc');