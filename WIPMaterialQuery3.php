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
$Title = _('工单入库明细');
$ViewTopic = '工单入库明细';
$BookMark = '工单入库明细';
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


		$sql ="SELECT  a.status_type, a.so_header_number,a.so_line_number,
	a.wip_entity_name,e.item_no,e.item_name,e.item_desc,a.plan_start_date,a.creation_date,
	e.units,a.start_quantity,a.quantity_completed,e.gongyi,a.quantity_completed,a.date_closed,a.created_by  from wip_jobs_all a, sf_item_no e  
	where   a.primary_item=e.item_no     
	and a.wip_entity_name = '" .$Updateorder_number."'";
    $result = DB_query($sql,$db);	
	while ($myrow2 = DB_fetch_array($result)) {

        echo '<div class="text-nav">
        <div class="text-nav-1"><div>工单名称</div> <input type="text" readonly="readonly" value="' . $myrow2['wip_entity_name'] . '" /></div>
        <div class="text-nav-1"><div>产品料号</div> <input type="text" readonly="readonly" value="' . $myrow2['item_no'] . '" /></div>
        <div class="text-nav-1"><div>料号名称</div> <input type="text" readonly="readonly" value="' . $myrow2['item_name'] . '" /></div>
        <div class="text-nav-1"><div>规格型号</div> <input type="text" readonly="readonly" value="' . $myrow2['item_desc'] . '" /></div>
        <div class="text-nav-1"><div>单位</div> <input type="text" readonly="readonly" value="' . $myrow2['units'] . '" /></div>
        <div class="text-nav-1"><div>开工数量</div> <input type="text" readonly="readonly" value="' . $myrow2['start_quantity'] . '" /></div>
        <div class="text-nav-1"><div>入库数量</div> <input type="text" readonly="readonly" value="' . $myrow2['quantity_completed'] . '" /></div>
        <div class="text-nav-1"><div>状态</div> <input type="text" readonly="readonly" value="' . $myrow2['status_type'] . '" /></div>
        <div class="text-nav-1"><div>开工日期</div> <input type="text" readonly="readonly" value="' . date('Y-m-d', $myrow2['plan_start_date']) . '" /></div>
        <div class="text-nav-1"><div>关闭日期</div> <input type="text" readonly="readonly" value="' . date('Y-m-d', $myrow2['date_closed']) . '" /></div>
        <div class="text-nav-1"><div>订单号码</div> <input type="text" readonly="readonly" value="' . $myrow2['so_header_number'] . '" /></div>
        <div class="text-nav-1"><div>订单行</div> <input type="text" readonly="readonly" value="' . $myrow2['so_line_number'] . '" /></div>
     
    
        <div class="text-nav-1"><div>建立人</div> <input type="text" readonly="readonly" value="' . $myrow2['created_by'] . '" /></div> 
        <div class="text-nav-1"><div>建立时间</div> <input type="text" readonly="readonly" value="' . date('Y-m-d H:i:s', $myrow2['creation_date']) . '" /></div> </div>';

   
	}

        $sql2 = "select  b.item_no ,a.wip_entity_name,c.transaction_type,c.transaction_date,	a.creation_date,c.created_by,b.item_desc,b.item_name,c.quantity,c.subinventory_from,c.trans_num,b.gongyi,a.make_factory,c.remark,c.operation_seq_num
				FROM wip_jobs_all a,sf_item_no b,inv_transactions_all c
           WHERE   c.transaction_type in ('工单入库') 
				and c.item_no=b.item_no
				and a.wip_entity_name=c.wip_entity_name
				and b.item_no=c.item_no
		and a.wip_entity_name = '" .$Updateorder_number."'
		order by b.item_no";
        $result2 = DB_query($sql2, $db);
        if (DB_num_rows($result2) == 0) {
            unset($result2);
            prnMsg(_('没有找到工单入库明细信息，请重新查询！'), 'info');
        } else {
			echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/customer.png" title="' . _('工单入库明细') .
 '" alt="" />' . ' ' . _('工单入库明细') . '
	</p>';
            echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '"><input type="hidden" name = "identifier" value ="' . $identifier . '">';
            echo '<div>';
            echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
            echo '<table class="selection" align="center" >';
            $tableheader = '<tr>	                           
                     <th bgcolor="#87CEFA" class="ascending" width = 70>' . _('交易类型') . '</th>
                    <th bgcolor="#87CEFA"  class="ascending" width = 110>' . _('料号') . '</th>
					 <th bgcolor="#87CEFA"  width = 190>' . _('料号名称') . '</th>
					 <th bgcolor="#87CEFA"  width = 190>' . _('规格型号') . '</th> 
                    <th bgcolor="#87CEFA"   >' . _('交易数量') . '</th> 
					<th bgcolor="#87CEFA"   >' . _('仓库') . '</th>
					<th bgcolor="#87CEFA"  width = 80>' . _('交易单号') . '</th>      
                    <th bgcolor="#87CEFA" width = 180>' . _('备注') . '</th>   
                    <th bgcolor="#87CEFA" width = 80>' . _('做账人员') . '</th>
                    <th bgcolor="#87CEFA"  class="ascending" width = 160>' . _('做账日期') . '</th>                                                                           
				</tr>';
                       
            echo $tableheader;
            $RowCounter = 1;
            $k = 0; //row colour counter
            while ($myrow = DB_fetch_array($result2)) {
                if ($k == 1) {
                    echo '<tr class="EvenTableRows">';
                    $k = 0;
                } else {
                    echo '<tr class="OddTableRows">';
                    $k++;
                }


                echo '<td>' .  $myrow['transaction_type']. '</td>
                    <td>' . $myrow['item_no'] . '</td> 
					<td>' . $myrow['item_name'] . '</td> 
					<td>' . $myrow['item_desc'] . '</td>  
					<td>' . $myrow['quantity'] . '</td> 
					<td>' . $myrow['subinventory_from'] . '</td>
					<td>' . $myrow['trans_num'] . '</td> 
					<td>' . $myrow['remark'] . '</td> 
					<td>' . $myrow['created_by'] . '</td> 
                   <td>' . date('Y-m-d H:i:s', $myrow['transaction_date']) . '</td>
                                 
        </tr>';
               }
            echo '</table> ';


            echo '</div>
          </form>';
        }
 
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
