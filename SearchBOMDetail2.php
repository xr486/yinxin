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
    
        $sql2 = "SELECT b.assembly_item_no, b.item_num, b.operation_seq_num, b.component_item,d.item_name component_item_name,d.item_desc component_item_desc, c.substitute_item, a.item_name substitute_item_name, a.item_desc substitute_item_desc, b.component_quantity, b.sunhao_rate, c.substitute_item_quantity, b.effectivity_date, b.disable_date, b.component_remarks, b.uom
FROM bom_lines_all b, sf_item_no a, sf_item_no d, bom_substitutes_all c
WHERE a.item_no = c.substitute_item
AND b.component_item = d.item_no
AND b.assembly_item_no = c.assembly_item_no
AND b.component_item = c.component_item
AND b.item_num = b.item_num
and b.disable_date=0
and c.disable_date=0
AND b.assembly_item_no = '" .$searchitem_no."'
UNION
SELECT b.assembly_item_no, b.item_num, b.operation_seq_num, b.component_item, a.item_name, a.item_desc, '', '','',b.component_quantity, b.sunhao_rate, '', b.effectivity_date, b.disable_date, b.component_remarks, b.uom
FROM bom_lines_all b, sf_item_no a
WHERE a.item_no = b.component_item
and b.disable_date=0
AND b.assembly_item_no =  '" .$searchitem_no."'
order by  item_num,component_item,substitute_item";
//echo $sql2 ;
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
	                           
                                        <th width =50 >' . '工序' . '</th>
										<th width =50 >' . '序号' . '</th>
										
										<th width =150 >' . '子料' . '</th>
                                        <th  width =140>' . '子料名称' . '</th>
                                        <th  width =140>' . '规格型号' . '</th>
										<th width =50 >' . '用量' . '</th> 
										<th width =70 >' . '损耗率' . '</th> 
                                        <th width =50 >' . '单位' . '</th>  
                                       <th width =160 >' . '生效时间' . '</th> 
									   <th width =150 >' . '替代料' . '</th>
                                        <th  width =140>' . '替代料名称' . '</th>
                                        <th  width =140>' . '规格型号' . '</th>
										<th width =70 >' . '替代用量' . '</th> 
                                       
                                       
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
			 
 
                echo '
					  <td >' . $myrow['operation_seq_num'] . '</td>
		              <td >' . $myrow['item_num'] . '</td> 
                      <td>' . $myrow['component_item'] . '</td>
					  <td>' . $myrow['component_item_name'] . '</td>
					  <td>' . $myrow['component_item_desc'] . '</td>
                       <td class="number">' .$myrow['component_quantity'] . '</td>
					   <td class="number">' .$myrow['sunhao_rate'] . '</td>
                      <td>' . $myrow['uom'] . '</td>                       
					   <td>' . date('Y-m-d H:i:s', $myrow['effectivity_date']) . '</td> 		 
					   <td>' . $myrow['substitute_item'] . '</td>
					  <td>' . $myrow['substitute_item_name'] . '</td>
					  <td>' . $myrow['substitute_item_desc'] . '</td>
                       <td class="number">' .$myrow['substitute_item_quantity'] . '</td>
               </tr>';
                $RowCounter++;
                If ($RowCounter == 500) {
                    $RowCounter = 1;
                    echo $tableheader;
                }
            }
            echo '</table> ';


        }

         echo '<br />
			<div class="centre">
			 
                                <input type="submit" name="return" value="' . "关闭当前页面" . '" />&nbsp;
                                 
</div>';
    
    echo '</div>
          </form>';
}
if (isset($_POST['return'])) {
     
   echo '<script>window.close();</script>'; 
}
include('includes/footer.inc');
?>
