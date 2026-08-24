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

	$sql5 = "SELECT a.version,b.item_no,b.item_name,b.item_desc,item_category1,b.gongyi,b.units
	FROM  sf_item_no b,bom_headers_all a
WHERE	b.item_no=a.assembly_item_no and a.bom_header_id ='" .$searchitem_no."'";
 
$result5 = DB_query($sql5, $db); 
while ($myrow = DB_fetch_array($result5)) {
echo '<table width="100%" border="1" cellpadding="0" cellspacing="0"> 
<div class="text-nav">
        <div class="text-nav-1"><div>母件料号:</div ><input type="text" readonly="readonly" value="' . $myrow['item_no'] . '" /></div>
        <div class="text-nav-1"><div>料号名称:</div ><input type="text" readonly="readonly" value="' . $myrow['item_name'] . '" /></div>
        <div class="text-nav-1"><div>规格型号:</div ><input type="text" readonly="readonly" value="' . $myrow['item_desc'] . '" /></div>
        <div class="text-nav-1"><div>版本:</div ><input type="text" readonly="readonly" value="' . $myrow['version'] . '" /></div>
        <div class="text-nav-1"><div>单位:</div ><input type="text" readonly="readonly" value="' . $myrow['units'] . '" /></div>
        <div class="text-nav-1"><div>分类:</div ><input type="text" readonly="readonly" value="' . $myrow['item_category1'] . '" /></div>
</div>
   </table>';
   }

    
        $sql2 = "SELECT b.assembly_item_no,b.operation_seq_num,b.component_quantity,a.item_no,b.effectivity_date,
	b.disable_date, component_remarks,units,weizhi,item_num,item_desc,item_name,b.sunhao_rate
FROM bom_lines_all b,sf_item_no a
        where a.item_no = b.component_item		
		and b.bom_header_id = '" .$searchitem_no."'";
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
	                                      <th width =50 >' . '序号' . '</th>
										<th width =50 >' . '工序' . '</th>
                                        <th  width =140>' . '料号' . '</th>
										<th  width =170>' . '料号名称' . '</th>
										<th  width =170>' . '规格型号' . '</th>
										<th width =90 >' . '单位用量' . '</th> 
										<th width =90 >' . '自损率' . '</th> 
                                        <th width =50 >' . '单位' . '</th>  
                                       <th width =160 >' . '生效时间' . '</th>
                                       <th width =160 >' . '失效时间' . '</th> 	 
									   <th width =120 >' . '备注' . '</th>
                                       
                                       
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

 
                echo '  
		              <td >' . $myrow['item_num'] . '</td>
					  <td >' . $myrow['operation_seq_num'] . '</td>
                      <td>' . $myrow['item_no'] . '</td>
					  <td>' . $myrow['item_name'] . '</td>
					  <td>' . $myrow['item_desc'] . '</td>
                       <td class="number">' .$myrow['component_quantity'] . '</td>
					    <td  class="number" >' .$myrow['sunhao_rate'] . '</td>
                      <td>' . $myrow['units'] . '</td> 
                      
					   <td>' . date('Y-m-d H:i:s', $myrow['effectivity_date']) . '</td>
					   <td>' .$disable_date . '</td> 				   
					  <td>' . $myrow['component_remarks']  . '</td>
                        

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
echo '<br /><div class="centre"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' . _('查询其它BOM') . '</a></div><br />';

        echo '<br />

                                <input type="submit" name="return" value="' . "返回上一层" . '" />&nbsp;
                                 
</div>';
    
    echo '</div>
          </form>';
}
if (isset($_POST['return'])) {
//    echo 'AAAAAAAAAA';
    header('Location: BOMQueryReport.php');
}
include('includes/footer.inc');
?>
