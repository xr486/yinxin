<?php

ob_start();
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include('includes/session.inc');
$Title = _('销售退回审核');
$ViewTopic = '销售退回审核';
$BookMark = '销售退回审核';

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

 
  $sql="SELECT 
    b.customer_code,
    b.customer_name, 
    p.delivery_num,
    p.delivery_date,    
    p.creation_date,
    p.created_by,p.narrative,
    p.last_update_date,
    p.last_updated_by  
 FROM so_delivery_headers_all p,customers b   
 WHERE p.customer_code = b.customer_code 
 and status='开始'
 and delivery_type='退货' " ;
 
 

    if (isset($_POST['SO_from']) and $_POST['SO_from'] != '') {
        $sql = $sql . " and p.delivery_num >=  '" . $_POST['SO_from'] . "'";
    }
    if (isset($_POST['SO_to']) and $_POST['SO_to'] != '') {
        $sql = $sql . " and p.delivery_num <=  '" . $_POST['SO_to'] . "' ";
    }
    if (isset($_POST['customer_name']) and $_POST['customer_name'] != '') {
        $sql = $sql . " and b.customer_name " . LIKE . " '%" . $_POST['customer_name'] . "%' ";
    }
	if (isset($_POST['customer_code']) and $_POST['customer_code'] != '') {
        $sql = $sql . " and b.customer_code " . LIKE . " '%" . $_POST['customer_code'] . "%' ";
    }
   
    if (empty($_POST['FromDate']) == 0) {
        $SQL_FromDate = strtotime($_POST['FromDate']);
        $sql .= " and p.delivery_date >= '" . $SQL_FromDate . "' ";
    }
    if (empty($_POST['ToDate']) == 0) {
        $SQL_ToDate = strtotime($_POST['ToDate']) + 86400;
        //echo $SQL_ToDate;
        $sql .= " and p.delivery_date <='" . $SQL_ToDate . "' ";
    }
      
    $result = DB_query($sql, $db);
    if (DB_num_rows($result) == 0) {
        unset($result);
        prnMsg(_('找不到需要处理的退货单，请重新输入条件查询！'), 'error');
    }
}


echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('销售退回审核') . '</p>';
echo '<table cellpadding="3" class="selection">';
echo '<div class="text-nav">';
echo '<div class="text-nav-1">
<div>' . _('退货申请单起') . ':</div>';
echo '<input type="text" name="SO_from" value="' . $_POST['SO_from'] . '" size="20" maxlength="25" /></div>';
echo '<div class="text-nav-1">
<div>' . _('退货申请单止') . ':</div>
	';
echo '<input type="text" name="SO_to" value="' . $_POST['SO_to'] . '" size="20" maxlength="25" /></div>';
echo '<div class="text-nav-1">
<div>' . _('客户简称') . ':</div>';
echo '<input type="text" name="customer_code" value="' . $_POST['customer_code'] . '" size="20" maxlength="25" /></div>';
 echo '<div class="text-nav-1">
 <div>' . _('客户名称') . ':</div>';
echo '<input type="text" name="customer_name" value="' . $_POST['customer_name'] . '" size="20" maxlength="25" /></div>';
 
echo '<div class="text-nav-1">
<div>' . '退货日期' . _('From') . ':</div>
		<input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="FromDate" maxlength="10" size="11"  value="' . $_POST['FromDate'] . '" /></div>
		<div class="text-nav-1">
<div>' . _('To') . ':</div>
		<input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="ToDate" maxlength="10" size="11"  value="' . $_POST['ToDate'] . '" /></div>



        <div class="text-nav-1">
<div>' . _('仓库名称') . ':</div>
       

        <select type="text"   autocomplete="off"   required="required" name="insubinventory" id="text_slect_insubinventoryname" value="'. $_POST['insubinventory'] .'" onblur="sel()">';
            
            $sql3 = "select loccode,locationname from locations where managed='Y' ";
            $result3 = DB_query($sql3, $db);
            while ($v = DB_fetch_array($result3)) {
                if ($v['loccode'] == $_POST['insubinventory']) {
            
                    echo ' <option value="'.$v['loccode'] .'" selected="selected">'. $v['locationname'] .'</option>';
                }else { 
                    echo ' <option value="'.$v['loccode'] .'">'.$v['locationname'] .'</option>';
            		}
            }
            
            echo ' </select>
        </div>';

echo '</table><div class="centre"><input type="submit" name="Search" value="查找"> </div>'
 . '</br>';

if (isset($_POST['Search']) and isset($result) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])) {
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
                     <th class="ascending"width = 70>' . _('退货单号') . '</th>
                    <th class="ascending"width = 150>' . _('客户简称') . '</th>
					 <th class="ascending"width = 250>' . _('客户名称') . '</th>    
                    <th class="ascending"width = 100>' . _('退货日期') . '</th>    
                    <th class="ascending"width = 100>' . _('备注') . '</th>		 	
                    <th class="ascending"width = 90>' . _('建单者') . '</th>
                    <th class="ascending"width = 180>' . _('建单日期') . '</th>
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
            unset($v_delivery_num);
     
            echo '   <td><a target="_blank" href="' . $RootPath . '/OrderReturnApproved2.php?Updatedelivery_num=' . $myrow['delivery_num'] . '&subinventory='.$_POST['insubinventory'].'">' . $myrow['delivery_num'] . '</td> 
                    <td>' . $myrow['customer_code'] . '</td> 
					<td>' . $myrow['customer_name'] . '</td>  
                   <td>' . date('Y-m-d', $myrow['delivery_date']) . '</td>
                  <td>' . $myrow['narrative'] . '</td>
                  <td>' . $myrow['created_by'] . '</td>
				<td>' . date('Y-m-d H:i:s', $myrow['creation_date']) . '</td>
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
