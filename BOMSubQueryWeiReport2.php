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
$Title = _('BOM及替代料零件位置查询');
$ViewTopic = 'BOM及替代料零件位置查询';
$BookMark = 'BOM及替代料零件位置查询';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc'); 




echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/customer.png" title="' . _('BOM及替代料零件位置查询') .
 '" alt="" />' . ' ' . _('BOM及替代料零件位置查询') . '
	</p>';
if (isset($searchitem_no) and $searchitem_no != '') {
    //CreditLimit,

	$sql = "SELECT b.item_no,b.item_name,b.item_desc,item_category1,b.gongyi,b.units
	FROM sf_item_no b
WHERE	  b.item_no ='" .$searchitem_no."'";
$result = DB_query($sql, $db); 
while ($myrow = DB_fetch_array($result)) {
echo '<table width="100%" border="1" cellpadding="0" cellspacing="0"> 
<div class="text-nav">
        <div class="text-nav-1"><div>母件料号:</div ><input type="text" readonly="readonly" value="' . $myrow['item_no'] . '" /></div>
        <div class="text-nav-1"><div>料号名称:</div ><input type="text" readonly="readonly" value="' . $myrow['item_name'] . '" /></div>
        <div class="text-nav-1"><div>规格型号:</div ><input type="text" readonly="readonly" value="' . $myrow['item_desc'] . '" /></div>
        <div class="text-nav-1"><div>单位:</div>
        <input type="text" readonly="readonly" value="' . $myrow['units'] . '" /></div>
<div class="text-nav-1"><div>分类:</div ><input type="text" readonly="readonly" value="' . $myrow['item_category1'] . '" /></div>
</div>
   </table>';

    
	}
    
        $sql2 = "SELECT b.component_sequence_id,b.assembly_item_no,b.operation_seq_num,b.component_quantity,b.sunhao_rate,a.item_no,b.effectivity_date,
	b.disable_date, component_remarks,a.units,change_notice,item_num,item_name,item_desc ,(select  operation_code from 
	bom_routings_all c 
		where b.assembly_item_no=c.assembly_item_no
		and b.operation_seq_num=c.operation_seq_num ) operation_code,(select count(*) 
		from bom_substitutes_all bsa where b.component_sequence_id=bsa.component_sequence_id ) sub_count
FROM bom_lines_all b,sf_item_no a
        where a.item_no = b.component_item
		and b.assembly_item_no = '" .$searchitem_no."'";
        $result2 = DB_query($sql2, $db);
        if (DB_num_rows($result2) == 0) {
            unset($result2);
            prnMsg(_('没有找到BOM及替代料零件位置查询，请重新登录查询！'), 'info');
        } else {
            echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '"><input type="hidden" name = "identifier" value ="' . $identifier . '">';
            echo '<div>';
            echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
            echo '<div class="text-nav-table"> <table class="selection" align="center" >';
            $tableheader = '<tr>
	                             <th width =50 >' . '序号' . '</th>
								<th width =50 >' . '工序' . '</th> 
                                        <th  width =140>' . '料号' . '</th>
                                        <th  width =40>' . '主替' . '</th>
										<th  width =200>' . '料号名称' . '</th>
										<th   >' . '规格型号' . '</th>
										<th width =100 >' . '用量' . '</th>  
                                        <th width =50 >' . '单位' . '</th>  
                                       <th width =160 >' . '生效时间' . '</th>
                                       <th width =160 >' . '失效时间' . '</th> 	 
									   <th width =120 >' . '备注' . '</th>     	 
									   <th width =120 >' . '零件位置' . '</th>                                                                             
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
				if   ($myrow['disable_date']!='' and $myrow['disable_date']!=0 ) {
				  $disable_date=date('Y-m-d H:i:s', $myrow['disable_date']);
				}
				$weizhi='';
				$sql6 = "SELECT weizhi FROM bom_weizhi_all a             
                WHERE  a.component_sequence_id  = '" .$myrow['component_sequence_id']."' 
				and status='生效'
				order by a.creation_date "; 
                $result6 = DB_query($sql6, $db);
			     while ($myrow6 = DB_fetch_array($result6)) {
				   $weizhi= $weizhi .'-'.$myrow6['weizhi'];
				   }

 
                echo '
		              <td >' . $myrow['item_num'] . '</td>
					  <td >' . $myrow['operation_seq_num'] . '</td> 
                      <td>' . $myrow['item_no'] . '</td>';
					 
					  echo '<td> 主</td>';
					 
					 echo '  
					  <td>' . $myrow['item_name'] . '</td>
					  <td>' . $myrow['item_desc'] . '</td>
                       <td class="number">' .$myrow['component_quantity'] . '</td> 
                      <td>' . $myrow['units'] . '</td> 
                      
					   <td>' . date('Y-m-d H:i:s', $myrow['effectivity_date']) . '</td>
					   <td>' .$disable_date . '</td> 				   
					  <td>' . $myrow['component_remarks']  . '</td>   
					  <td>' . substr($weizhi,1)  . '</td>
               </tr>';

			 $sql5 = "SELECT a.creation_date,item_no,item_desc,item_name,b.units,a.substitute_item_quantity,
	c.assembly_item_no,c.component_item,a.substitute_remarks,a.component_sequence_id,a.substitute_sequence_id,a.status
	 FROM bom_substitutes_all a,sf_item_no b,bom_lines_all c              
                WHERE a.substitute_item=b.item_no and a.component_sequence_id=c.component_sequence_id
				and   a.component_sequence_id  = '" .$myrow['component_sequence_id']."'   
				order by a.creation_date ";
        $result5 = DB_query($sql5, $db);
			   while ($myrow5 = DB_fetch_array($result5)) {
                 echo '
		              <td > </td>
					  <td > </td> 
                      <td>' . $myrow5['item_no'] . '</td>';
					 
					  echo '<td> 替</td>';
					 
					 echo '  
					  <td>' . $myrow5['item_name'] . '</td>
					  <td>' . $myrow5['item_desc'] . '</td>
                       <td class="number">' .$myrow5['substitute_item_quantity'] . '</td>
					   <td class="number"> </td>
                      <td>' . $myrow5['units'] . '</td> 
                      
					   <td> </td>
					   <td> </td> 				   
					  <td>' . $myrow5['substitute_remarks']  . '</td>
               </tr>';

			   }

                $RowCounter++;
                If ($RowCounter == 500) {
                    $RowCounter = 1;
                    echo $tableheader;
                }
            }
            echo '</table></div>';

 
        }
echo '<div>
        <a href="' . $RootPath . '/BOMSubQueryWeiReportExcel.php?item_no=' .$searchitem_no .' ">' .'资料导出Excel表' . '</a>
    </div>';
       
    echo '</div>
          </form>';
}
if (isset($_POST['return'])) {
    echo 'AAAAAAAAAA';
    echo '<script>window.close();</script>'; 
}
include('includes/footer.inc');
?>
