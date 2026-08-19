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
    $Updatesearchitem_no = $_GET['searchitem_no'];
} elseif($_POST['searchitem_no']) {
    $Updatesearchitem_no = $_POST['searchitem_no'];
}else{
    $Updatesearchitem_no = '';
}
$Title = _('BOM审批处理');
$ViewTopic = 'BOM审批处理';
$BookMark = 'BOM审批处理';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
// include('includes/CountriesArray.php');

echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/customer.png" title="' . _('BOM') .
 '" alt="" />' . ' ' . _('BOM审批处理') . '
	</p>';

    $creation_date = strtotime(Date('Y-m-d H:i:s'));
    if (isset($_POST['approve'])){
   

       
             $sql = "update bom_headers_all set status = '待签核',approve_date='',approve_remark='',approve_by='' where bom_header_id = '".$_POST['bom_header_id']."' ";
            //   echo $sql;
             $result = DB_query($sql,$db);
           


         prnMsg('BOM签核成功！',success);
        // header("Location: SussCreate.php?OrderNum=".$_POST['order_number']."&type=OrderApprove2");
         echo "<script>location.href='BOMApprove.php';</script>";
     }
  
if (isset($Updatesearchitem_no) and $Updatesearchitem_no != '') {
    //CreditLimit,
    $sql = "SELECT a.assembly_item_no,a.bom_header_id, b.item_name, b.item_desc ,b.gongyi, b.item_category1
    FROM bom_headers_all a ,sf_item_no b
    WHERE  bom_header_id='" . $Updatesearchitem_no . "'
    and b.item_no = a.assembly_item_no
    ";
    $result = DB_query($sql, $db); 
    $myrow = DB_fetch_array($result);
    $_POST['bom_header_id'] = $myrow['bom_header_id'];
    $_POST['assembly_item_no'] = $myrow['assembly_item_no'];
    $_POST['item_name'] = $myrow['item_name'];
    $_POST['item_desc'] = $myrow['item_desc'];
    $_POST['gongyi'] = $myrow['gongyi'];   
    $_POST['item_category1'] = $myrow['item_category1'];   

    if (!isset($_GET['delete'])) {
        echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">'
        . '<input type="hidden" name = "identifier" value ="' . $identifier . '">';
        echo '<div>';
        echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
       
        echo '<table class="selection" id="SignFrame">
                <div class="text-nav">
                    <div class="text-nav-1 ">
                        <div>' . _('母件料号') . ':</div>
                        <input  type="text" name="assembly_item_no"  value="' . $_POST['assembly_item_no'] . '" />
                        </div>
                        <input  type="hidden" name="bom_header_id"  value="' . $_POST['bom_header_id'] . '" />
			        <div class="text-nav-1 ">
                        <div>' . _('料号名称') . ':</div>
                        <input  type="text" name="item_name"  value="' . $_POST['item_name'] . '" />
                    </div>
				    <div class="text-nav-1 ">
                        <div>' . _('规格型号') . ':</div>
                        <input  type="text" name="item_desc"  value="' . $_POST['item_desc'] . '" />
                    </div>

			        <div class="text-nav-1 ">
                        <div>' . _('工艺') . ':</div>
                        <input  type="text" name="gongyi"  value="' . $_POST['gongyi'] . '" />
                    </div>
			        <div class="text-nav-1 ">
                        <div>' . _('分类') . ':</div>
                        <input  type="text" name="item_category1"  value="' . $_POST['item_category1'] . '" />
                    </div>

		';
        
        echo'
                    <div class="text-nav-2 ">
                        <div>' . _('签核意见备注') . ':</div>
				        <input type="text"  style="background-color:#FFF68F" size="50" maxlength="200" name="approve_remark" value= ' . $_POST['approve_remark'] . ' >
                    </div>';
        echo '</table>';
        echo '<br />';

        $sql = 'select b.item_no,b.item_name,b.item_desc,b.units,a.*
				from bom_lines_all a,sf_item_no b where a.component_item=b.item_no and assembly_item_no = ' . "'" .$_POST['assembly_item_no'] . "' 
				order by b.item_no"; 
                $resultline = DB_query($sql, $db);
        if (DB_num_rows($resultline) == 0) {
            unset($resultline);
            prnMsg(_('没有找到BOM详细信息，请重新登录查询！'), 'info');
        } else {
            echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '"><input type="hidden" name = "identifier" value ="' . $identifier . '">';
            echo '<div>';
            echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
            echo '<div style="overflow:scroll"> <table class="selection" align="center" >';
		
            $tableheader = '<tr> 
                            <th >行</th>
                            <th bgcolor="#87CEFA" width="220">子料号</th>
                            <th width="230">料号名称</th>
                            <th width="230">规格型号</th>
                            <th width="30" >单位</th>
                            <th bgcolor="#87CEFA" width="20">数量</th>
                            <th width="120">损耗率(0-1之间)</th>
                            <th width="120">生效时间</th>
                            <th width="120">失效时间</th>
                            <th width="100">备注</th>                 
                                            
				</tr>'; 
            echo $tableheader;
            $RowCounter = 1;
            $k = 0; //row colour counter
            $i = 1;
            while ($myrow = DB_fetch_array($resultline)) {
                if ($k == 1) {
                    echo '<tr class="EvenTableRows">';
                    $k = 0;
                } else {
                    echo '<tr class="EvenTableRows">';
                    $k++;
                }
                $disable_date='';
				  if ($myrow['disable_date']<>0) {
				  $disable_date=date('Y-m-d',$myrow['disable_date']);
				  }

                echo '
                      <td><input readonly="readonly" id="item_num'.$i.'"  type="text" autocomplete="off" name="item_num'.$i.'" class="number" size="1"  value="' .$myrow['item_num']  . '" /></td>
                      <td>' . $myrow['component_item'] . '</td>
                      <td>' . $myrow['item_name'] . '</td>
                      <td>' . $myrow['item_desc'] . '</td>
                      <td>' . $myrow['units'] . '</td>
                      <td class="number">' . $myrow['component_quantity'] . '</td>
                      <td class="number">' . $myrow['sunhao_rate'] . '</td>
                      <td>' . date('Y-m-d ',$myrow['effectivity_date']) . '</td>
                      <td>' . $disable_date . '</td>
                      <td>' .$myrow['component_remarks'] . '</td>     
                  
                   
                </tr>';
 
                $RowCounter++;
                If ($RowCounter == 500) {
                    $RowCounter = 1;
                    echo $tableheader;
                }
                $i++;
            }
            echo '
	<td ><input type="hidden" name="flag" value="<?=$i-1?>" size="15" maxlength="45"/></td> ';
            echo '</table> </div>';


            echo '</div>
          </form>';
        }

        echo '<br />
        
        <input type="submit" name="approve" value="反核准" />&nbsp;&nbsp;&nbsp;
  
        </div>';
    }
    //<input type="submit" name="cancel" value="取消" />&nbsp;&nbsp;&nbsp;
    echo '</div>
          </form>';
}
if (isset($_POST['return'])) {
    header('Location: SearchSoForApprove.php');
}
include('includes/footer.inc');
?>
<script>
function checkalla(thisform){for(var i=0;i<thisform.elements.length;i++){if(thisform.elements[i].type=="checkbox"&&thisform.elements[i].checked==false&&thisform.elements[i].name!="selectall"){thisform.elements[i].checked=true;}else if(thisform.elements[i].type=="checkbox"&&thisform.elements[i].checked==true&&thisform.elements[i].name!="selectall"){thisform.elements[i].checked=false;}} }
</script>