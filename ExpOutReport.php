<?php
 ob_start();
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include('includes/session.inc');
$Title = _('费用支出查询查询');
$ViewTopic= '费用支出查询查询';
$BookMark = '费用支出查询查询';

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

if(isset($_POST['Search']) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])){
    $sql = "SELECT c.trans_num ,c.shipcode,c.request_date,c.request_person,c.head_remark,c.exp_type_name,c.exp_item,
	c.exp_object,c.exp_amount,c.exp_date,c.remark,h.employee_name,c.creation_date
	FROM  fin_exp_out_transactions_all c,hr_employees h where 
c.request_person =h.employee_num
and c.shipcode  =h.shipcode   
and  c.shipcode  = '" . $_SESSION['shipcode']. "'
  ";
  
   //and substr(sh.order_num,1,2)='CK'
    if(isset($_POST['exp_item']) and $_POST['exp_item'] != ''){
        $sql = $sql." and c.exp_item ".LIKE." '%".$_POST['exp_item']."%' ";
    }
    if(isset($_POST['exp_object']) and $_POST['exp_object'] != ''){
        $sql = $sql." and c.exp_object ".LIKE." '%".$_POST['exp_object']."%' ";
    }
    
	if(isset($_POST['exp_type_name']) and $_POST['exp_type_name'] != ''){
        $sql = $sql." and c.exp_type_name ".LIKE." '%".$_POST['exp_type_name']."%' ";
    }
    

    if(isset($_POST['FromDate']) and $_POST['FromDate'] != ''){
        $sql = $sql." and c.exp_date >=".strtotime($_POST['FromDate'])." ";
}
     if(isset($_POST['ToDate']) and $_POST['ToDate'] != ''){
       $sql = $sql." and c.exp_date <=".strtotime($_POST['ToDate'])." ";
    }
	

  
  
    $result = DB_query($sql,$db);
    if (DB_num_rows($result)==0) {
        unset($result);
        prnMsg(_('找不到该销售单，请重新输入条件查询！') ,'error');
    }


//汇总
	$sql = "SELECT sum(c.exp_amount) exp_amount
	FROM  fin_exp_out_transactions_all c,hr_employees h where 
c.request_person =h.employee_num
and c.shipcode  =h.shipcode   
and  c.shipcode  = '" . $_SESSION['shipcode']. "'
  ";
  
   if(isset($_POST['exp_item']) and $_POST['exp_item'] != ''){
        $sql = $sql." and c.exp_item ".LIKE." '%".$_POST['exp_item']."%' ";
    }
    if(isset($_POST['exp_object']) and $_POST['exp_object'] != ''){
        $sql = $sql." and c.exp_object ".LIKE." '%".$_POST['exp_object']."%' ";
    }
    
	if(isset($_POST['exp_type_name']) and $_POST['exp_type_name'] != ''){
        $sql = $sql." and c.exp_type_name ".LIKE." '%".$_POST['exp_type_name']."%' ";
    }
    

    if(isset($_POST['FromDate']) and $_POST['FromDate'] != ''){
        $sql = $sql." and c.exp_date >=".strtotime($_POST['FromDate'])." ";
}
     if(isset($_POST['ToDate']) and $_POST['ToDate'] != ''){
       $sql = $sql." and c.exp_date <=".strtotime($_POST['ToDate'])." ";
    }

   
   $result2 = DB_query($sql,$db);
    if (DB_num_rows($result2)==0) {
        unset($result2);
        prnMsg(_('找不到库存，请重新输入条件查询！') ,'error');
    }
	while ($myrow = DB_fetch_array($result2)) {
      $all_need_payment=$myrow['exp_amount'];
	  $all_actual_payment=$myrow['actual_payment'];	  
	  $all_discount_amount=$myrow['discount_amount'];
	}

}
 

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('支出记录查询') . '</p>';
echo '<table cellpadding="3" class="selection">';

echo '<tr><td  >' . _('支出类型') . ':</td><td>';
echo '<input type="text" name="exp_type_name" value="' . $_POST['exp_type_name'] . '" size="20" maxlength="25" /></td>';
echo ' <td  >' . _('支出项目') . ':</td><td>';
echo '<input type="text" name="exp_item" value="' . $_POST['exp_item'] . '" size="20" maxlength="25" /></td>';

 


echo '<td >' . _('对象') . ':</td><td>';
echo '<input type="text" name="exp_object" value="' . $_POST['exp_object'] . '" size="20" maxlength="25" /></td>';
echo ' <td >' . _('经办人') . ':</td><td>';
echo '<input type="text" name="employee_name" value="' . $_POST['employee_name'] . '" size="20" maxlength="25" /></td>';

 
echo '</tr><tr>';
 
 
if (!isset($_POST['FromDate'])) {
    $_POST['FromDate'] = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 30, date("Y")));
}
if (!isset($_POST['ToDate'])) {
    $_POST['ToDate'] = Date('Y-m-d', mktime(0, 0, 0, date("m"), date("d") + 1, date("Y")));
}
echo '<td  >' . '发生日期' . _('From') . ':</td>
		<td><input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="FromDate" maxlength="10" size="12" value="' . $_POST['FromDate'] . '" /></td>
		<td>' . _('To') . ':</td>
		<td><input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="ToDate" maxlength="10" size="12" value="' . $_POST['ToDate'] . '" /></td>
	</tr>';




 
echo '</table><div class="centre"><input type="submit" name="Search" value="查找"></div>';

if (isset($_POST['Search']) and isset($result) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])){
    $ListCount = DB_num_rows($result);
    $ListPageMax = ceil($ListCount / 20);
    
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
                    <table cellpadding="0" class="selection" >';

 

    echo '<tr><th   >' . '流水单号' . '</th>
	                  <th   >' . _('申请日期') . '</th> 
					  <th   >' . _('经办人') . '</th>
                        <th   >' . '备注' . '</th>
                        <th   >' . '支出类型' . '</th>						
										<th  >' . _('支出项目') . '</th> 
										<th  >' . _('对象') . '</th> 
										<th  >' . _('金额') . '</th> 
										<th  >' . _('发生日期') . '</th> 
										<th  >' . _('备注') . '</th> 
										<th  >' . _('单据建立日') . '</th> 
                                      
                                      
            </tr>';
    $k = 0; //row counter to determine background colour
    $RowIndex = 0;
    
    if (DB_num_rows($result) <> 0) {
        DB_data_seek($result, ($_POST['PageOffset'] - 1) * 20);
        $i = 0; //counter for input controls
        while (($myrow = DB_fetch_array($result)) AND ($RowIndex <> 20)) {
			if ($k == 1) {
				echo '<tr class="EvenTableRows">';
				$k = 0;
			} else {
				echo '<tr class="OddTableRows">';
				$k = 1;
			}	  
            
             echo '<td>' . $myrow['trans_num'] . '</td>'; 
			 echo '<td>' . date('Y-m-d',$myrow['request_date'])   . '</td>'; 
			echo '<td>' . $myrow['employee_name'] . '</td>';              
       
			echo '<td>' . $myrow['head_remark'] . '</td>';              
            echo '<td>' . $myrow['exp_type_name'] . ' </td>';
           
			echo '<td>' . $myrow['exp_item'] . '</td>';              
            	echo '<td>' . $myrow['exp_object'] . '</td>';
                	echo '<td>' . $myrow['exp_amount'] . '</td>';
                 echo '<td>' .date('Y-m-d',$myrow['exp_date'])   . '</td>';
                	echo '<td>' . $myrow['remark'] . '</td>';
						echo '<td>' .date('Y-m-d',$myrow['creation_date'])   . '</td>';
            
          //   H:i:s
			echo '
			</tr>';
			$i++;
			$RowIndex++;
			//end of page full new headings if
		} //end loop through vendors
		echo '</table>';



	echo '<table><tr  > <td> <b>总计:'.$ListCount.'条记录,总支出金额:' .$all_need_payment; 
	  
	echo '  </b></td></tr></table>'; 
		
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