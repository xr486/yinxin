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
if (isset($_GET['searchitem_no'])) {
    $searchitem_no = $_GET['searchitem_no'];
} else {
    $searchitem_no = '';
}
$Title = _('BOM明细');
$ViewTopic = 'BOM明细';
$BookMark = 'BOM明细';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc'); 

echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/customer.png" title="' . _('BOM明细') .
 '" alt="" />' . ' ' . _('BOM明细') . '
	</p>';
if (isset($searchitem_no) and $searchitem_no != '') {
    //CreditLimit,
    
        $sql2 ="select b.operation_seq_num,a.operation_code,a.operation_name,b.creation_date,b.created_by,b.rate,b.effectivity_date,
		a.use_status,b.disable_date
       from bom_parameters a , bom_routings_all b
       where 1=1
        and a.operation_code=b.operation_code
		and b.assembly_item_no = '" .$searchitem_no."'";
		//echo $sql2;
        $result2 = DB_query($sql2, $db);
        if (DB_num_rows($result2) == 0) {
            unset($result2);
            prnMsg(_('没有找到BOM明细，请重新登录查询！'), 'info');
        } else {
            echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '"><input type="hidden" name = "identifier" value ="' . $identifier . '">';
            echo '<div>';
            echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
            echo '<table class="selection" align="center" >';
            $tableheader = '<tr>
	                           
                    <th class="ascending" width = 50>' . _('工序') . '</th>
                    <th   width = 100>' . _('工艺代号') . '</th>
                    <th  width = 150>' . _('工艺名称') . '</th>
                     <th  width =80>' . _('单位时间') . '</th>
					 <th  width = 80>' . _('生效日期') . '</th>
					 <th  width = 80>' . _('失效日期') . '</th>
                    <th  width = 80>' . _('建立日期') . '</th>
                   <th width = 100>' . _('建立人员') . '</th> 
                                       
                                       
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
				if   ($myrow['disable_date']!='' ) {
				  $disable_date=date('Y-m-d H:i:s', $myrow['creation_date']);
				}

 
               echo '  <td>' . $myrow['operation_seq_num'] . '</td>
			     <td>' . $myrow['operation_code'] . '</td>
				<td>' . $myrow['operation_name'] . '</td>				
				<td>' . $myrow['rate'] . '</td>
				<td>' . date('Y-m-d',$myrow['effectivity_date']). '</td> 
				<td>' . $disable_date. '</td> 	
				<td>' . date('Y-m-d',$myrow['creation_date']). '</td> 
				<td>' . $myrow['created_by'] . '</td> 
				</tr> 
				
				';
                $RowCounter++;
                If ($RowCounter == 500) {
                    $RowCounter = 1;
                    echo $tableheader;
                }
            }
            echo '</table> ';


        }

        echo '<br />

                                <input type="submit" name="return" value="' . "返回上一层" . '" />&nbsp;
                                 
</div>';
    
    echo '</div>
          </form>';
}
if (isset($_POST['return'])) {
    echo 'AAAAAAAAAA';
    header('Location: BOMRoutesReport.php');
}
include('includes/footer.inc');
?>
