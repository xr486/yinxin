<?php

 
include('includes/session.inc');

$Title = _('加盟商对账订单查询');
$ViewTopic = 'Inventory';
$BookMark = 'FulfilRequest';

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
$_SESSION['DisplayRecordsMax']=10;
if(isset($_POST['Search']) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])){
    $sql ="SELECT a.order_number,c.order_line_id, a.status, b.realname,a.description note, a.schedule_ship_date, a.creation_date,line_no, chuanghu_name, chuanghu_width, chuanghu_height,chuanghu_quantity, chuanghu_bu_item, chuanghu_bu_quantity, chuanghu_fucai_item, chuanghu_fucai_quantity, chuanghu_sha_item, chuanghu_sha_quantity,c.reconciliation_amount
FROM sf_orders_all a, www_users b, sf_order_lines_all c
WHERE a.order_number = c.order_number
AND a.created_by = b.userid
and  c.reconciliation_flag='Y' 
AND a.created_by =  '" .$_SESSION['UserID'] . "'
		 AND 1=1 " ;
    
	if (isset($_POST['SO_from']) and $_POST['SO_from'] != '') {
        $sql = $sql . " and a.order_number >=  '" . $_POST['SO_from'] . "'";
    }
    if (isset($_POST['SO_to']) and $_POST['SO_to'] != '') {
        $sql = $sql . " and a.order_number <=  '" . $_POST['SO_to'] . "' ";
    }
   

	
    if (empty($_POST['FromDate']) == 0) {
        $SQL_FromDate = strtotime($_POST['FromDate']);
        $sql .= " and a.schedule_ship_date >= '" . $SQL_FromDate . "' ";
    }
    if (empty($_POST['ToDate']) == 0) {
        $SQL_ToDate = strtotime($_POST['ToDate']) + 86400;
        //echo $SQL_ToDate;
        $sql .= " and a.schedule_ship_date <='" . $SQL_ToDate . "' ";
    }

    $result = DB_query($sql,$db);
    if (DB_num_rows($result)==0) {
        unset($result);
        prnMsg(_('无对账记录，请重新输入条件查询！') ,'error');
    }

	
}


echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('查询已对账之订单') . '</p>';
echo '<table cellpadding="3" class="selection">';
echo '<tr><td >' . _('订单起') . ':</td><td>';
echo '<input type="text" name="SO_from" value="' . $_POST['SO_from'] . '" size="20" maxlength="25" /></td>';
echo '<td>' . _('订单止') . ':</td>
	<td>';
echo '<input type="text" name="SO_to" value="' . $_POST['SO_to'] . '" size="20" maxlength="25" /></td>';

if (!isset($_POST['FromDate'])) {
    $_POST['FromDate'] = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 30, date("Y")));
}
if (!isset($_POST['ToDate'])) {
    $_POST['ToDate'] = Date('Y-m-d');
}
echo '</tr><tr> <td>' . '预计出货日' . _('From') . ':</td>
		<td><input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="FromDate" maxlength="10" size="11" value="" /></td>
		<td>' . _('To') . ':</td>
		<td><input type="text" onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="ToDate" maxlength="10" size="11" value="" /></td>
	</tr>';
echo '</table><div class="centre"><input type="submit" name="Search" value="查找"></div>';

if (isset($_POST['UpdateAll'])) {
	    ;
        foreach ($_POST as $key => $value){

           if (mb_substr($key,0,10)=='UpdateLine') {
	        $order_line_id=mb_substr($key,10);
            $j = $_POST[$key]; 
            $Amount = $_POST['paymentamount' . $j];
	
			$sql="UPDATE sf_order_lines_all
					SET reconciliation_amount = '".$Amount."'
					 ,reconciliation_flag='Y'
					, last_update_date='" . Date('Y-m-d H:i:s'). "'
					,last_updated_by='" . $_SESSION['UserID'] . "'
					WHERE   order_line_id = '".$order_line_id."'";


            $ErrMsg = _('更新对账记录不成功,原因');
			$result=DB_query($sql, $db,$ErrMsg);	
        
			prnMsg( _('处置费用对账成功'),'success');
		}
        }
}

if (isset($_POST['Search']) and isset($result) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])){
    $ListCount = DB_num_rows($result);
    $ListPageMax = ceil($ListCount / $_SESSION['DisplayRecordsMax']);
    
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
            echo '<br /><div class="centre">&nbsp;&nbsp;' . $_POST['PageOffset'] . ' ' . _('of') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ': ';
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
    echo '<br />
                    <table cellpadding="2" class="selection">';

    echo '<tr>
                      <th class="ascending" width = 100>' . _('订单号') . '</th>
                    <th class="ascending"width = 90>' . _('状态') . '</th>
                    <th class="ascending"width = 100>' . _('预计出货日') . '</th>	 
					<th width =50 >' . '行' . '</th>
                                        <th  width =140>' . '窗户名' . '</th>
                                        <th width =50 >' . '高' . '</th>
					                   <th  width =50>' . '宽' . '</th>
                                        <th  width =50>' . '数量' . '</th>
                                        <th width =200 >' . '布料号' . '</th> 
                                       <th width =80 >' . '布数量' . '</th>
                                       <th width =200 >' . '纱料号' . '</th>                                
                                       <th width =80 >' . '纱数量' . '</th>
                                       <th width =200 >' . '辅材料' . '</th>
                                       <th width =80 >' . '辅材数量' . '</th> 
						<th width=60>' . _('对账金额') . '</a></th>
            </tr>';
    $k = 0; //row counter to determine background colour
    $RowIndex = 0;

    if (DB_num_rows($result) <> 0) {
        DB_data_seek($result, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);
        $i = 0; //counter for input controls
        while (($myrow = DB_fetch_array($result)) AND ($RowIndex <> $_SESSION['DisplayRecordsMax'])) {
			if ($k == 1) {
				echo '<tr class="EvenTableRows">';
				$k = 0;
			} else {
				echo '<tr class="OddTableRows">';
				$k = 1;
			}
			

			 unset($v_status);
            if ($myrow['status'] == 'INPROCESS') {
                $v_status = '待签核';
            } elseif ($myrow['status'] == 'APPROVED') {
                $v_status = '已签核';
            } elseif ($myrow['status'] == 'REJECTED') {
                $v_status = '已拒签';
            } elseif ($myrow['status'] == 'P_APPROVED') {
                $v_status = '生产已签核';
            }  elseif ($myrow['status'] == 'CANCELED') {
                $v_status = '取消';
            }else {
                $v_status = '已取消';
            }

		     $cailiaofei = 0;
	    	 $gongfei =0;
		     $zongji =0;

			 


			if  ($myrow['chuanghu_bu_item']=='' or $myrow['chuanghu_bu_item']=='NULL') {
			$chuanghu_bu_item='';
			} else {
				$chuanghu_bu_item=$myrow['chuanghu_bu_item'];
				$SQL = "select franchise_price
				from sf_item_no
			 where item_no='" . $chuanghu_bu_item . "'"  ;
        $SQLResult = DB_query($SQL, $db);
        $SQLRow = DB_fetch_array($SQLResult);
		$ListCount = DB_num_rows($SQLResult);
        if ($ListCount!=0) {
            $cailiaofei =$cailiaofei+  $SQLRow['franchise_price']*$myrow['chuanghu_bu_quantity'];  
			$gongfei = $gongfei + 20 * $myrow['chuanghu_bu_quantity']; 
        }
			}

			if  ($myrow['chuanghu_sha_item']=='' or $myrow['chuanghu_sha_item']=='NULL') {
			$chuanghu_sha_item='';
			} else {
				$chuanghu_sha_item=$myrow['chuanghu_sha_item'];
		 $SQL = "select franchise_price
				from sf_item_no
			 where item_no='" . $chuanghu_sha_item . "'"  ;
        $SQLResult = DB_query($SQL, $db);
        $SQLRow = DB_fetch_array($SQLResult);
		$ListCount = DB_num_rows($SQLResult);
        if ($ListCount!=0) {
            $cailiaofei =$cailiaofei+  $SQLRow['franchise_price']*$myrow['chuanghu_sha_quantity'];
			$gongfei = $gongfei + 20 * $myrow['chuanghu_sha_quantity'];       
        }
			}

        if  ($myrow['chuanghu_fucai_item']=='' or $myrow['chuanghu_fucai_item']=='NULL') {
			$chuanghu_fucai_item='';			
			} else {
				$chuanghu_fucai_item=$myrow['chuanghu_fucai_item'];

				$SQL = "select franchise_price
				from sf_item_no
			 where item_no='" . $chuanghu_fucai_item . "'"  ;
        $SQLResult = DB_query($SQL, $db);
        $SQLRow = DB_fetch_array($SQLResult);
		$ListCount = DB_num_rows($SQLResult);
        if ($ListCount!=0) {
            $cailiaofei =$cailiaofei+  $SQLRow['franchise_price']*$myrow['chuanghu_fucai_quantity'];
		    
        }
			}


		 $cailiaofei = $cailiaofei * $myrow['chuanghu_quantity'];
		 $gongfei = $gongfei *  $myrow['chuanghu_quantity'];
		 $zongji =$cailiaofei + $gongfei;

			echo ' <td>' . $myrow['order_number'] . '</td>
                        <td>' . $v_status . '</td>
                                <td>' . date('Y-m-d', $myrow['schedule_ship_date']) . '</td>
			 <td>' . $myrow['line_no'] . '</td>
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
			        <td>' . $myrow['reconciliation_amount'] . '</td>
					';
	 	 //  <td>' . $zongji. '</td>
         
          
          echo  '</td> 
			</tr>';
			$i++;
			$RowIndex++;

			//end of page full new headings if
		} //end loop through customers
		echo '</table>';
		echo '<input type="hidden" name="JustSelectedACustomer" value="Yes" />';
	}

        if (isset($ListPageMax) AND $ListPageMax > 1) {
                echo '<br /><div class="centre">&nbsp;&nbsp;' . $_POST['PageOffset'] . ' ' . _('of') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ': ';
                echo '<select name="PageOffset2">';
                $ListPage = 1;
                while ($ListPage <= $ListPageMax) {
                        if ($ListPage == $_POST['PageOffset']) {
                                echo '<option value="' . $ListPage . '" selected="selected">' . $ListPage . '</option>';
                        } //$ListPage == $_POST['PageOffset']
                        else {
                                echo '<option value="' . $ListPage . '">' . $ListPage . '</option>';
                        }
                        $ListPage++;
                } //$ListPage <= $ListPageMax
                echo '</select>
                        <input type="submit" name="Go2" value="' . _('Go') . '" />
                        <input type="submit" name="Previous" value="' . _('Previous') . '" />
                        <input type="submit" name="Next" value="' . _('Next') . '" />';
                echo '</div>';
    }//end if results to show

}

echo '</div>
      </form>';
include('includes/footer.inc');
?>

