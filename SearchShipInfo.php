<?php
ob_start();
 
include('includes/session.inc');

$Title = _('出货单查询');
$ViewTopic = 'Inventory';
$BookMark = 'FulfilRequest';

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
 
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

if(isset($_POST['Search']) OR isset($_POST['Go'])  or isset($_POST['Next']) OR isset($_POST['Previous'])){
    $sql ="SELECT  c.customer_code,
	c.customer_name,
	a.delivery_num, 
	a.tracking_number,
	a.trackingcompany,
	a.creation_date,
	a.narrative,(SELECT realname FROM www_users w WHERE w.userid=a.created_by) realname,
	a.created_by,a.delivery_date,a.print_type,a.delivery_type
FROM so_delivery_headers_all a,customers c
WHERE a.customer_code = c.customer_code and a.status <> '拒绝'  " ;
 


if (empty($_POST['FromDate'])==0) {
    $SQL_FromDate = strtotime( $_POST['FromDate']);
    //echo $SQL_FromDate;
    $sql .= " and  a.delivery_date >= '" . $SQL_FromDate . "' ";
}
if (empty($_POST['ToDate'])==0) {
    $SQL_ToDate = strtotime( $_POST['ToDate']) ;
     //echo $SQL_ToDate;
    $sql .= " and  a.delivery_date <='" . $SQL_ToDate . "' ";
}
 

 if (isset($_POST['customer_name']) and $_POST['customer_name']!= '') {
			  $sql = $sql." and c.customer_name ".LIKE." '%".$_POST['customer_name']."%' ";
		}
	 if (isset($_POST['created_by']) and $_POST['created_by']!= '') {
			  $sql = $sql." and a.created_by ".LIKE." '%".$_POST['created_by']."%' ";
		}

 if (isset($_POST['customer_code']) and $_POST['customer_code']!= '') {
			  $sql = $sql." and a.customer_code ".LIKE." '%".$_POST['customer_code']."%' ";
		}
 if (isset($_POST['delivery_num']) and $_POST['delivery_num']!= '') {
			  $sql = $sql." and a.delivery_num ".LIKE." '%".$_POST['delivery_num']."%' ";
		}

    $sql = $sql." order by a.creation_date desc";
 //echo $sql;
    $result = DB_query($sql,$db);
    if (DB_num_rows($result)==0) {
        unset($result);
        prnMsg(_('未能找到相关资料，请重新输入条件查询！') ,'error');
    }

	
}


echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('出货单查询') . '</p>';
echo '<div class="text-nav">';

echo '<div class="text-nav-1"><div>' . _('出货单号') . ':</div>
';
echo '<input type="text" name="delivery_num" value="' . $_POST['delivery_num'] . '" size="20" maxlength="25" /></div>';
echo '<div class="text-nav-1"><div>' . _('出货人') . ':</div>
';
echo '<input type="text" name="created_by" value="' . $_POST['created_by'] . '" size="20" maxlength="25" /></div>';


/* echo '<div class="text-nav-2"><div>' . _('客户名称') . ':</div>';
echo '<input type="text" id="text_slect_name" name="customer_name" value="' . $_POST['customer_name'] . '" size="20" maxlength="25" />
<a class="btn btn-info btn-xs" id="btn_slect_customer" hfre="###" title="选择客户">选</a>
</div>'; */
echo '<div class="text-nav-1"><div>' . _('客户名称') . ':</div>';
echo '<input type="text"  name="customer_name" value="' . $_POST['customer_name'] . '" size="20" maxlength="25" />
</div>';
echo '<div class="text-nav-1"><div>' . _('客户简称') . ':</div>';
echo '<input type="text"  name="customer_code" value="' . $_POST['customer_code'] . '" size="20" maxlength="25" />
</div>';

if (!isset($_POST['FromDate'])) {
        $_POST['FromDate'] = date( "Y-m-d", mktime(0,0,0,date("m"),date("d")-30,date("Y")));
    }
    if (!isset($_POST['ToDate'])) {
        $_POST['ToDate'] = Date('Y-m-d');
    }
     
 
echo '<div class="text-nav-1"><div>' . _('出货日期从') . ':</div>
';
echo '<input type="text" name="FromDate"   onfocus="WdatePicker()"  value="' . $_POST['FromDate'] . '" size="20" maxlength="25" /></div>';

echo '<div class="text-nav-1"><div>' . _('出货日期到') . ':</div>
';
echo '<input type="text" name="ToDate"  onfocus="WdatePicker()"  value="' . $_POST['ToDate'] . '" size="20" maxlength="25" /></div>';
echo '</div>';




echo '</table><div class="centre"><input type="submit" name="Search" value="查找"></div>';

 
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
         echo '<br /><div class="centre">&nbsp;&nbsp;' . _('第') . '' . $_POST['PageOffset'] . ' ' . _('页，共') . ' ' . $ListPageMax . ' ' . _('页') . '. ' . _('转到页') . ': ';
            //echo '<br /><div class="centre">&nbsp;&nbsp;' . $_POST['PageOffset'] . ' ' . _('of') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ': ';
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
                    <input type="submit" name="Go1" value="' . _('转到') . '" />
                    <input type="submit" name="Previous" value="' . _('上一页') . '" />
                    <input type="submit" name="Next" value="' . _('下一页') . '" />';
            echo '</div>';
    }
    echo '<br />
                    <table cellpadding="2" class="selection">';




    echo '<tr>   <th bgcolor="#87CEFA" class="ascending">' . _('行') . '</th>
	<th bgcolor="#87CEFA" class="ascending">' . _('客户代码') . '</th>
	            <th bgcolor="#87CEFA" class="ascending">' . _('客户名称') . '</th> 
                     <th bgcolor="#87CEFA" class="ascending">' . '出货单号码' . '</th>                    
						<th  bgcolor="#87CEFA" >' . '出货日期' . '</th>		
						
						
                         <th  bgcolor="#87CEFA" >' . _('出货人') . '</th> 
						 <th bgcolor="#87CEFA"  >' . '建单日期' . '</th>	
						<th  bgcolor="#87CEFA" >' . '打印' . '</th>
						<th  bgcolor="#87CEFA" >' . '详情' . '</th>
						   
						
            </tr>';
    $k = 0; //row counter to determine background colour
    $RowIndex = 0;
  
    if (DB_num_rows($result) <> 0) {
        DB_data_seek($result, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);
        $i = 1; //counter for input controls
        while (($myrow = DB_fetch_array($result)) AND ($RowIndex <> $_SESSION['DisplayRecordsMax'])) {
			if ($k == 1) {
				echo '<tr class="EvenTableRows">';
				$k = 0;
			} else {
				echo '<tr class="OddTableRows">';
				$k = 1;
			}
         
			 $creation_date= date('Y-m-d',$myrow['creation_date']);  
 	      
        	echo '   <td>' . $i  . '</td> 
			<td>' . $myrow['customer_code']  . '</td> 
			<td>' . $myrow['customer_name']  . '</td> 
			<td>' . $myrow['delivery_num']  . '</td>		
			<td>' . date('Y-m-d',$myrow['delivery_date']) . '</td> 			
		
			<td>' . $myrow['realname']  . '</td>
			<td>' . date('Y-m-d h:i:s',$myrow['creation_date'])  . '</td>';
		

                        if ($myrow['delivery_type']=='出货') {
                                if($myrow['print_type'] == '试剂'){
                         
                                 echo '<td><a href="' . $RootPath . '/PrintReagentDeliveryPDF.php?Updatedelivery_num=' .$myrow['delivery_num'] . '"  target="_blank" >打印</td>'; 
                         }else {
                                 echo '<td><a href="' . $RootPath . '/PrintInstrumentDeliveryPDF.php?Updatedelivery_num=' .$myrow['delivery_num'] . '"  target="_blank" >打印</td>'; 
                         }
                         }else{
                         
                           if($myrow['print_type'] == '试剂'){
                         
                             echo '<td><a href="' . $RootPath . '/PrintReagentDeliveryPDF.php?Updatedelivery_num=' .$myrow['delivery_num'] . '"  target="_blank" >打印</td>'; 
                         }else {
                             echo '<td><a href="' . $RootPath . '/PrintTInstrumentDeliveryPDF.php?Updatedelivery_num=' .$myrow['delivery_num'] . '"  target="_blank" >打印</td>'; 
                         }
                         }

			echo '<td><a href="' . $RootPath . '/SearchShipInfo2.php?delivery_id=' . $myrow['delivery_num'] . '" target="_blank" >详情</td>';
			?>
			
	
		 <input type="hidden"  name="ship_quantity<?=$i?>"   value="<?=$myrow['ship_quantity']?>" />
		 <input type="hidden"  name="delivery_num<?=$i?>"   value="<?=$myrow['delivery_num']?>" />
		 
		 <input type="hidden"  name="rack_no<?=$i?>"   value="<?=$myrow['rack_no']?>" />
		 <input type="hidden"  name="basket_no<?=$i?>"   value="<?=$myrow['basket_no']?>" />
		 
		 <input type="hidden"  name="id<?=$i?>" class="number"  value="<?=$myrow['id']?>" />
		 <input type="hidden"  name="onhand<?=$i?>" class="number"  value="<?=$_POST['onhand']?>" />
		 
		 
		 <?php
		 echo  '
			</tr>';
			echo ' <input type="hidden"  name="flag" class="number"  value="'.$i.'" />';
			$i++;
			$RowIndex++;
			//end of page full new headings if
		} //end loop through customers
		 
		echo '</table>'; 
                echo '<input type="hidden" name="JustSelectedACustomer" value="Yes" />';
                echo '<div>
                        <a href="' . $RootPath . '/SearchShipinfoReportExcel.php?customer_name=' .$_POST['customer_name'] .
                        '&customer_code=' .$_POST['customer_code'] .'&delivery_num='.$_POST['delivery_num'] .
                        '&FromDate=' .$_POST['FromDate'] . '&ToDate=' .$_POST['ToDate'] .' ">' .'资料导出Excel表' . '</a>
                    </div>';
	}

        if (isset($ListPageMax) AND $ListPageMax > 1) {
             echo '<br /><div class="centre">&nbsp;&nbsp;' . _('第') . '' . $_POST['PageOffset'] . ' ' . _('页，共') . ' ' . $ListPageMax . ' ' . _('页') . '. ' . _('转到页') . ': ';
                //echo '<br /><div class="centre">&nbsp;&nbsp;' . $_POST['PageOffset'] . ' ' . _('of') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ': ';
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
                        <input type="submit" name="Go2" value="' . _('转到') . '" />
                        <input type="submit" name="Previous" value="' . _('上一页') . '" />
                        <input type="submit" name="Next" value="' . _('下一页') . '" />';
                echo '</div>';
    }//end if results to show

}

echo '</div>
      </form>';
include('includes/footer.inc');
?>

