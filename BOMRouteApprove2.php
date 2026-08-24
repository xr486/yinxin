<?php
 
 include ('includes/DefineBOMUpdateClass.php');
include ('includes/session.inc');
$Title = _('产品工艺审核');
$ViewTopic = '产品工艺审核';
$BookMark = '产品工艺审核';
include ('includes/header.inc');
include ('includes/SQL_CommonFunctions.inc');
 



if (isset($_GET['identifier'])) {
    $_POST['identifier'] = $_GET['identifier'];
}

if (!isset($_POST['identifier'])) {
    $identifier = date('U');
} else {
    $identifier = $_POST['identifier'];
}

if (isset($_POST['Submit'])) {
    DB_Txn_Begin($db);
    $v_date = strtotime(Date('Y-m-d H:i:s'));

    $sql1 = " update bom_routings_all 
                set route_status = '已签核',
                    approve_date  = '" . $v_date . "',
                    approved_by  = '" . $_SESSION['UserID'] . "'
              where assembly_item_no ='" . $_POST['assembly_item_no'] . "'";
    $result1 = DB_query($sql1, $db);
	DB_Txn_Commit($db); 
    $msg = '签核成功！';
    prnMsg($msg, 'success');
    echo '<meta http-equiv="refresh" content="0; url=' . $RootPath . '/BOMRouteApprove.php" />';
    

    
}
if (isset($_POST['Reject'])) {
    DB_Txn_Begin($db);
    $v_date = strtotime(Date('Y-m-d H:i:s'));
    $sql1 = " update bom_routings_all 
    set route_status = '已拒签',
        approve_date  = '" . $v_date . "',
        approved_by  = '" . $_SESSION['UserID'] . "'
  where assembly_item_no ='" . $_POST['assembly_item_no'] . "'";
    $result1 = DB_query($sql1, $db);

    DB_Txn_Commit($db);
    $msg = '拒签成功！';
    prnMsg($msg, 'success');
    echo '<meta http-equiv="refresh" content="0; url=' . $RootPath . '/BOMRouteApprove.php" />';

}


if (isset($_GET['New'])) {
 
 
    unset($_SESSION['Contract' . $identifier]);
   $_SESSION['Contract' . $identifier] = new ReceiveRequest();
    if (isset($_GET['UpdateBOMItem'])) {
        if (!isset($_SESSION['Contract' . $identifier]->assembly_item_no) or $_SESSION['Contract' .
            $identifier]->assembly_item_no == '') {
            $UpdateBOMItem = $_GET['UpdateBOMItem'];
            $sql = 'SELECT  b.item_id,b.item_no,  b.item_name,b.item_desc,b.creation_date,b.created_by,(select count(*) 
			from bom_routing_public_file bsa where bsa.item_no=b.item_no ) sub_count 
	FROM  sf_item_no b
WHERE b.item_id=' . "'" .  $UpdateBOMItem  . "'";
 //echo  $sql;
            $CustResult = DB_query($sql, $db);
            while ($myrow = DB_fetch_array($CustResult)) {
                $_SESSION['Contract' . $identifier]->assembly_item_no = $myrow['item_no'];
                $_SESSION['Contract' . $identifier]->item_id = $myrow['item_id'];
                
                $_SESSION['Contract' . $identifier]->status = $myrow['status'];
                $_SESSION['Contract' . $identifier]->version = $myrow['version'];
                $_SESSION['Contract' . $identifier]->item_name = $myrow['item_name'];
				$_SESSION['Contract' . $identifier]->item_desc = $myrow['item_desc'];
                $_SESSION['Contract' . $identifier]->create_date = $myrow['creation_date']; 
                $_SESSION['Contract' . $identifier]->created_by = $myrow['created_by'];             
                $_SESSION['Contract' . $identifier]->sub_count = $myrow['sub_count'];             
            }


            if (!isset($_POST['Update'])) {
                $sql = 'select   a.*,(select count(*) from bom_routing_all_file bsa where bsa.route_id=a.route_id ) route_count
				from bom_routings_all a  where  a.assembly_item_no = ' . "'" .$_SESSION['Contract' . $identifier]->assembly_item_no . "' 
				order by a.operation_seq_num"; 
				 //echo $sql;
                $resultline = DB_query($sql, $db);
                
            }																																																		
        }
    }
}


if (isset($_POST['Edit'])) {
    if ($_POST["quantity"] > $_SESSION['Contract' . $identifier]->LineItems[$_POST['LineNumber']]->
        quantity_received) {
        $_SESSION['Contract' . $identifier]->LineItems[$_POST['LineNumber']]->quantity =
            $_POST['quantity'];
        $_SESSION['Contract' . $identifier]->LineItems[$_POST['LineNumber']]->price = $_POST['price'];
        $_SESSION['Contract' . $identifier]->LineItems[$_POST['LineNumber']]->amount = $_POST['quantity'] *
            $_POST['price'];
        $line = $_SESSION['Contract' . $identifier]->LineItems[$_GET['Delete']]->line;
    } else {
        prnMsg(_('修改失败，输入量小于来料报检量！'), 'error');

    }
}

 
if (isset($_GET['delete'])   ) {
   $time = time();
	 
       $sql = "delete from   bom_routings_all 
	   where  route_id= '" . $_GET['route_id'] . "' ";
	   $result = DB_query($sql,$db);

	 
	 
    prnMsg(_('删除成功！'), 'success');
	echo '<meta http-equiv="refresh" content="0; url=' . $RootPath .
                    '/BOMRouteModify2.php?New=Yes&UpdateBOMItem='. $_GET['item_id'] . '" />';
    //DB_Txn_Commit($db);
   
	 
}

 
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme .
    '/images/supplier.png" title="' . _('Dispatch') . '" alt="" />' . ' ' . $Title .
    '</p>';



echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES,
    'UTF-8') . '" method="post"><input type="hidden" name = "identifier" value ="' .
    $identifier . '">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<div class="centre"> 
<p id="Prompt" style="color: red;font-size: 20px"></p>
                    </div>';
 $sql = "SELECT  *
	FROM   sf_item_no b
WHERE	 item_id ='" .$_SESSION['Contract' . $identifier]->item_id."'";
$result = DB_query($sql, $db); 
while ($myrow = DB_fetch_array($result)) {

echo '<div class="text-nav">
	<div class="text-nav-1"><div>' . _('料号') . ':</div> 
<input type="text"   autocomplete="off"  readonly="readonly"  name="assembly_item_no" value="' . $_SESSION['Contract' . $identifier]->assembly_item_no . '" />
<input type="hidden"   name="item_id" value="' . $_SESSION['Contract' . $identifier]->item_id . '" />
 
</div>

<div class="text-nav-2"><div>' . _('料号名称') . ':</div>
<input type="text"   autocomplete="off"   readonly="readonly" value="' .  $myrow['item_name']. '" /> 
</div>

<div class="text-nav-1"><div>' . _('规格型号') . ':</div>
<input type="text"   autocomplete="off"   readonly="readonly" value="' .  $myrow['item_desc']. '" /> 
</div>
 
<div class="text-nav-1"><div>' . _('建立日期') . ':</div>
<input type="text"   autocomplete="off"   readonly="readonly" value="' . date('Y-m-d', $myrow['creation_date']) . '" />
 
</div>

<div class="text-nav-1"><div>' . _('建立人员') . ':</div>
<input type="text"   autocomplete="off"   readonly="readonly" value="' .  $myrow['created_by']. '" /> 
</div> ';
	   
	}
	echo '</div></table>';
 
 
echo '
<div class="centre" >
               
				
				<a style="width: 40px;" href="' . $RootPath . '/SussCreateBOMPublic.php?OrderNum=' . $_SESSION['Contract' . $identifier]->item_id . '" target="_blank">共用指导书管理' . $_SESSION['Contract' . $identifier]->sub_count . ' </a>
				
				 
	</div>
    </div>
	</form>';


 echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES,
    'UTF-8') . '" method="post"><input type="hidden" name = "identifier" value ="' .
    $identifier . '">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
//用隐藏域保存采购单表号
echo ' <input type="hidden" class="text"  name="assembly_item_no" value="' . $_SESSION['Contract' . $identifier]->assembly_item_no . '" /><input type="hidden" class="text"  name="item_id" value="' . $_SESSION['Contract' . $identifier]->item_id . '" />';
 


echo '<div style="overflow:scroll">
<table class="selection">
	<tr>

		<th>' . _('工序') . '</th>
		<th   width=90>' . _('工艺代码') . '</th> 
                     <th bgcolor="#87CEFA"  width ="40">作业时间(h)</th>
                     <th bgcolor="#87CEFA"  width ="40">标准产能</th>
                     <th bgcolor="#87CEFA"  width ="40">工作内容</th>
                     <th bgcolor="#87CEFA"  width ="40">建立日期</th> 
					 <th bgcolor="#87CEFA"  width ="40">工序指导书</th>
	</tr>';

$k = 0;
$i = 1;

while ($myrow = DB_fetch_array($resultline)) {
    
    if ($k == 1) {
        echo '<tr class="EvenTableRows">';
        $k = 0;
    } else {
        echo '<tr class="OddTableRows">';
        $k++;
    }
                  $disable_date='';
				  if ($myrow['disable_date']<>0) {
				  $disable_date=date('Y-m-d',$myrow['disable_date']);
				  }
				 $item_num = $myrow['item_num'];
 
	 echo ' 
	 		 
       <td><input style="background-color:yellow" type="text"   autocomplete="off"   class="number" name="operation_seq_num'.$i.'" size="2"  value="' . $myrow['operation_seq_num']  . '" /> </td>
	   <td><input readonly="readonly"  type="text"   autocomplete="off"    name="operation_code'.$i.'" size="10"  value="' . $myrow['operation_code']  . '" /> </td> '; 

	   echo ' <td><input  id="rate' .$i.'"  type="text"   autocomplete="off"    name="rate'.$i.'" class="number" size="5"  value="' . $myrow['rate']  . '" /></td>
	     <td><input   type="text"    name="channeng'.$i.'"  size="3"  value="' . $myrow['channeng']  . '" /></td>
	 
	   <td><input   type="text"   autocomplete="off"    name="remarks'.$i.'"  size="35"  value="' . $myrow['remarks']  . '" /></td>
	 
	   <td><input   type="text"   readonly="readonly"   name="creation_date'.$i.'"  size="14"  
	   value="' . date('Y-m-d H:i:s',$myrow['creation_date']) . '" /></td>';

	
		
		echo '<td> <a style="width: 40px;" href="' . $RootPath . '/SussCreateBOM.php?assembly_item_no=' .$_SESSION['Contract' . $identifier]->item_id . '&route_id=' .$myrow['route_id'] . ' " target="_blank">管理' .$myrow['route_count'] . ' </a></td>';
		
	   

	  echo ' <input type="hidden" name="route_id'.$myrow['route_id'].'" value="'.$i.'" /></td> ';
    
    

       echo ' 
     </tr>';            
      $i++;
	echo ' </tr>';

}
echo '</table></div>
<div class="centre">
<input type="submit" name="Submit" value="' . "签核" . '" /> &nbsp;&nbsp;&nbsp;&nbsp;   <input type="submit" name="Reject" value="' . "拒签" . '" />&nbsp;&nbsp;&nbsp;&nbsp;   <input type="submit" name="return" value="' . "返回上一层" . '" />&nbsp;
</div>
       
    </div>
    </form>';
 
//*********************************************************************************************************
 

?>

<?php
if (isset($_POST['return'])) {
    echo '<script>window.close();</script>';
}
include ('includes/footer.inc');
?>