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
if (isset($_GET['Updateorder_number'])) {
    $Updateorder_number = $_GET['Updateorder_number'];
} else {
    $Updateorder_number = '';
}
$Title = _('工单详情');
$ViewTopic = '工单详情';
$BookMark = '工单详情';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/CountriesArray.php');

 
if (isset($Updateorder_number) and $Updateorder_number != '') {
    //CreditLimit,
  
    if (!isset($_GET['delete'])) {
        echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">'
        . '<input type="hidden" name = "identifier" value ="' . $identifier . '">';
        echo '<div>';
        echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
        
        
 
        echo '<br />';


		$sql ="SELECT  a.version,a.status_type, a.so_header_number,a.so_line_number,
	a.wip_entity_name,e.item_no,e.item_name,e.item_desc,a.plan_start_date,a.creation_date,
	e.units,a.start_quantity,a.quantity_completed,e.gongyi,a.quantity_completed,a.date_closed,a.created_by  from wip_jobs_all a, sf_item_no e  
	where   a.primary_item=e.item_no     
	and a.wip_entity_name = '" .$Updateorder_number."'";
    $result = DB_query($sql,$db);	
	while ($myrow2 = DB_fetch_array($result)) {
    echo '<table class="selection" >';
    echo '<tr> <td>工单名称</td> <td>' . $myrow2['wip_entity_name'] . '</td>
			    <td>产品料号</td> <td>' . $myrow2['item_no'] . '</td>
				<td>料号名称</td> <td>' . $myrow2['item_name'] . '</td>
				<td>规格型号</td> <td>' . $myrow2['item_desc'] . '</td>
				<td>版本</td> <td>' . $myrow2['version'] . '</td>
		<tr> </tr>
		<td>单位</td> <td>' . $myrow2['units'] . '</td>
		<td>开工数量</td> <td>' . $myrow2['start_quantity'] . '</td>
		<td>入库数量</td> <td>' . $myrow2['quantity_completed'] . '</td>
		<td>开工日期</td> <td>' . date('Y-m-d', $myrow2['plan_start_date']) . '</td>
		<td>关闭日期</td> <td>' . date('Y-m-d', $myrow2['date_closed']) . '</td></tr>	
		<td>状态</td> <td>' . $myrow2['status_type'] . '</td>	
		<td>订单号码</td> <td>' . $myrow2['so_header_number'] . '</td>
		<td>订单行</td> <td>' . $myrow2['so_line_number'] . '</td>
		<td>建立人</td> <td>' . $myrow2['created_by'] . '</td> 
		<td>建立时间</td> <td>' . date('Y-m-d H:i:s', $myrow2['creation_date']) . '</td> </tr>
		</table>';
	}

        $sql2 = "SELECT  a.operation_seq_num,a.seq_id,a.quantity_per_assembly,a.quantity_issued, a.date_required,a.required_quantity,
	a.wip_entity_name,e.item_no,e.item_name,e.item_desc,e.units,a.comments,a.creation_date,a.created_by,a.chaohao_quantity
	 from wip_material_requierments a,sf_item_no e
	where a.segment1=e.item_no
		and a.wip_entity_name = '" .$Updateorder_number."'";
        $result2 = DB_query($sql2, $db);
        if (DB_num_rows($result2) == 0) {
            unset($result2);
            prnMsg(_('没有找到工单用料信息！'), 'info');
        } else {
			echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/customer.png" title="' . _('工单用料详情') .
 '" alt="" />' . ' ' . _('工单用料详情') . '
	</p>';
            echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '"><input type="hidden" name = "identifier" value ="' . $identifier . '">';
          
            echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
            echo ' <div style="overflow:scroll"> 
			<table class="selection" align="center" >';
            $tableheader = '<tr><th   >' . _('变更') . '</th>   
	                        <th   >' . _('制程') . '</th>   
                     <th class="ascending" width =140 >' . _('料号') . '</th>
					<th  width =140 >' . _('料号名称') . '</th> 
					<th   >' . _('规格型号') . '</th> 
					<th   >' . _('单位') . '</th>
					 <th class="ascending" >' . _('需求日期') . '</th>	
					  <th  >' . _('需求数量') . '</th>
					 <th   >' . _('单位耗用') . '</th>	
					 <th >' . _('已发料量') . '</th>	
					 <th >' . _('超耗数量') . '</th>
					 <th >' . _('备注') . '</th>
					 <th >' . _('建立者') . '</th>
					 <th >' . _('建立日期') . '</th>
                                      
                                       
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
				if ($myrow['date_required']>1) {
                    $date_required= date('Y-m-d', $myrow['date_required']);
                } else {
                    $date_required='';
                }


                echo '<td><a target="_blank" href="' . $RootPath . '/WIPMaterialQuery4.php?Updateorder_number='.$myrow['wip_entity_name'].'&seq_id=' . $myrow['seq_id'] .'">
				查询</td> 
				<td>' . $myrow['operation_seq_num'] . '</td>
		              <td>' . $myrow['item_no'] . '</td>
			    <td>' . $myrow['item_name'] . '</td>
				<td>' . $myrow['item_desc'] . '</td>
			    <td>' . $myrow['units'] . '</td>
			   <td>' .  $date_required . '</td>		
			    <td>' . $myrow['required_quantity']  . '</td>
				<td>' . $myrow['quantity_per_assembly']  . '</td>
				<td>' . $myrow['quantity_issued']  . '</td> 	
				<td>' . $myrow['chaohao_quantity']  . '</td> 
				<td>' . $myrow['comments']  . '</td> 	
				<td>' . $myrow['created_by']  . '</td> 
				<td>' . date('Y-m-d H:i:s',$myrow['creation_date'])  . '</td> 
                        
 
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

		echo '<br>
          <div class="centre">
                <a href="' . $RootPath . '/WIPMaterialQueryExcel.php?wip_entity_name=' .$Updateorder_number  . '">
                ' .'资料导出Excel表' . '
                </a>
          </div>';
 
        echo '<br />
                                <input type="submit" name="return" value="' . "关闭当前页面" . '" />&nbsp;
                                 
</div>';
    }
    echo '</div>
          </form>';
}
if (isset($_POST['return'])) {
//    echo 'AAAAAAAAAA';
  echo '<script>window.close();</script>'; 
}
include('includes/footer.inc');
?>
