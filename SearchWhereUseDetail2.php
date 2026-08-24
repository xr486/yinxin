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
$Title = _('BOM用途查询');
$ViewTopic = 'BOM用途查询';
$BookMark = 'BOM用途查询';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc'); 

echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/customer.png" title="' . _('BOM用途查询') .
 '" alt="" />' . ' ' . _('BOM用途查询') . '
	</p>';
if (isset($searchitem_no) and $searchitem_no != '') {
       

        $sql2 = "SELECT '主' zhuti_type,c.version,b.assembly_item_no,b.operation_seq_num,b.component_quantity,a.item_no,b.effectivity_date,c.bom_header_id,
	b.disable_date, component_remarks,a.units,change_notice,item_num,item_name,item_desc
FROM bom_lines_all b,sf_item_no a,bom_headers_all c
        where a.item_no = b.assembly_item_no and b.bom_header_id=c.bom_header_id
		and disable_date =0
		and b.component_item = '" .$searchitem_no."'
		union all
		SELECT '替',c.version,b.assembly_item_no,b.operation_seq_num,d.substitute_item_quantity,a.item_no,b.effectivity_date,c.bom_header_id,
	b.disable_date, substitute_remarks,a.units,change_notice,b.item_num,item_name,item_desc
FROM bom_lines_all b,sf_item_no a,bom_headers_all c,bom_substitutes_all d
        where a.item_no = b.assembly_item_no and b.bom_header_id=c.bom_header_id
		and d.component_sequence_id=b.component_sequence_id
		and disable_date =0
		and d.status='生效' 
		and d.substitute_item = '" .$searchitem_no."'
		";
    // echo $sql2;
        $result2 = DB_query($sql2, $db);
        if (DB_num_rows($result2) == 0) {
            unset($result2);
            prnMsg(_('没有找到BOM用途查询，请重新登录查询！'), 'info');
        } else {
            echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '"><input type="hidden" name = "identifier" value ="' . $identifier . '">';
            echo '<div>';
            echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

			 $sql3 = "SELECT a.units,item_no,item_name,item_desc FROM sf_item_no a
        where item_no = '" .$searchitem_no."'";
		$result3 = DB_query($sql3, $db);
		$myrow3 = DB_fetch_array($result3);

    echo '<table  class="selection" align="center" >
	<tr>
	<td>料号</td>
	<td>' .$myrow3['item_no'].'</td>
	<td>料号描述</td>
	<td>' .$myrow3['item_name'].'</td>
	</tr>
	</table>
	';


            echo '<table class="selection" align="center" >';
            $tableheader = '<tr>
	                            <th bgcolor="#87CEFA" width =50 >' . '主替' . '</th>
                                        <th bgcolor="#87CEFA" width =50 >' . '序号' . '</th>
										<th bgcolor="#87CEFA" width =50 >' . '工序' . '</th>
                                        <th bgcolor="#87CEFA"  width =140>' . '母件料号' . '</th>
										<th bgcolor="#87CEFA"  width =160>' . '母件料号名称' . '</th>
										<th bgcolor="#87CEFA"  width =160>' . '规格型号' . '</th>
										<th bgcolor="#87CEFA" width =90 >' . '版本' . '</th> 
										<th bgcolor="#87CEFA" width =90 >' . '单位用量' . '</th> 
                                        <th bgcolor="#87CEFA" width =50 >' . '单位' . '</th>  
                                       <th bgcolor="#87CEFA" width =160 >' . '生效时间' . '</th>	 
									   <th bgcolor="#87CEFA" width =120 >' . '备注' . '</th>
                                       
                                       
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
				 $disable_date='';
				if   ($myrow['disable_date'] > 1 ) {
				  $disable_date=date('Y-m-d H:i:s', $myrow['disable_date']);
				}

 
                echo '
                <td >' . $myrow['zhuti_type'] . '</td>
		              <td >' . $myrow['item_num'] . '</td>
					  <td >' . $myrow['operation_seq_num'] . '</td>
                      <td><a href="' . $RootPath . '/BOMModify2.php?New=Yes&UpdateBOMItem=' . $myrow['bom_header_id'] . '" target="_blank" >' . $myrow['assembly_item_no'] . '</td>
					  <td>' . $myrow['item_name'] . '</td>
					  <td>' . $myrow['item_desc'] . '</td>
					  <td>' . $myrow['version'] . '</td>
                       <td class="number">' .$myrow['component_quantity'] . '</td>
                      <td>' . $myrow['units'] . '</td> 
                      
					   <td>' . date('Y-m-d H:i:s', $myrow['effectivity_date']) . '</td>
								   
					  <td>' . $myrow['component_remarks']  . '</td>
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
