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
   

       
             $sql = "update bom_headers_all set status = '已审核',approve_date='". $creation_date ."',approve_remark='". $_POST['approve_remark']  ."',approve_by='" . $_SESSION['UserID'] . "' where bom_header_id = '".$_POST['bom_header_id']."' ";
            //   echo $sql;
             $result = DB_query($sql,$db);
           


         prnMsg('BOM签核成功！',success);
        // header("Location: SussCreate.php?OrderNum=".$_POST['order_number']."&type=OrderApprove2");
         echo "<script>location.href='BOMApprove.php';</script>";
     }
  
     if (isset($_POST['reject'])){
         $sql = "update bom_headers_all set status = '已拒签',approve_date=". $creation_date .",approve_remark='". $_POST['approve_remark']  ."',approve_by='" . $_SESSION['UserID'] . "'  where bom_header_id = '".$_POST['bom_header_id']."' ";
         $result = DB_query($sql,$db);
         prnMsg('BOM拒签成功！',success);
           
          //header("Location: SussCreate.php?OrderNum=".$_POST['order_number']."&type=OrderApprove2");
        echo "<script>location.href='BOMApprove.php';</script>";
     }
     if (isset($_POST['cancel'])){
         $sql = "update bom_headers_all set status = '已取消',approve_date=". $creation_date .",approve_remark='". $_POST['approve_remark']  ."',approve_by='" . $_SESSION['UserID'] . "'  where bom_header_id = '".$_POST['bom_header_id']."' ";
        
         $result = DB_query($sql,$db);
         prnMsg('BOM取消成功！',success);
         header("Location: SussCreate.php?OrderNum=".$_POST['order_number']."&type=OrderApprove2");
         // header("Location: SussCreate.php?OrderNum=$$Updateorder_number");
         // echo "<script>location.href='SearchSoForApprove.php';</script>";
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

        // 母件信息卡片（BOMSetup 风格）
        echo '<div style="border:1px solid #d6e4f0;border-radius:6px;background:#f9fbfd;padding:12px 16px;margin-bottom:14px">';
        echo '<div style="font-weight:bold;color:#0d47a1;font-size:14px;margin-bottom:8px">BOM 审核信息</div>';
        echo '<table class="selection" style="border:none;background:transparent">';
        echo '<tr><td style="width:110px;color:#666">母件料号</td><td><b>' . htmlspecialchars($_POST['assembly_item_no']) . '</b></td>';
        echo '<td style="width:110px;color:#666">料号名称</td><td>' . htmlspecialchars($_POST['item_name']) . '</td></tr>';
        echo '<tr><td style="color:#666">规格型号</td><td>' . htmlspecialchars($_POST['item_desc']) . '</td>';
        echo '<td style="color:#666">状态</td><td><span style="background:#FFF3CD;color:#856404;padding:2px 10px;border-radius:10px;font-size:12px">未审核</span></td></tr>';
        echo '<tr><td style="color:#666">签核意见备注</td><td colspan="3"><textarea name="approve_remark" rows="2" cols="60" placeholder="填写签核意见（可选）">' . htmlspecialchars(isset($_POST['approve_remark']) ? $_POST['approve_remark'] : '') . '</textarea></td></tr>';
        echo '</table>';
        echo '<input type="hidden" name="bom_header_id" value="' . htmlspecialchars($_POST['bom_header_id']) . '">';
        echo '</div>';
        echo '</table>';
        echo '<br />';

        $sql = 'select b.item_no,b.item_name,b.item_desc,b.units,a.*
				from bom_lines_all a,sf_item_no b where a.component_item=b.item_no and bom_header_id = ' . "'" .$Updatesearchitem_no . "' 
				order by b.item_no"; 
                $resultline = DB_query($sql, $db);
        if (DB_num_rows($resultline) == 0) {
            unset($resultline);
            prnMsg(_('没有找到BOM详细信息，请重新登录查询！'), 'info');
        } else {
            echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '"><input type="hidden" name = "identifier" value ="' . $identifier . '">';
            echo '<div>';
            echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
            echo '<div style="overflow:auto;border:1px solid #e0e8f0;border-radius:6px"> <table class="selection" align="center" >';
		
            $tableheader = '<tr style="background:#eef4fb"> 
                            <th >行</th>
                            <th width="200">子料号</th>
                            <th width="200">料号名称</th>
                            <th width="200">规格型号</th>
                            <th width="60" >单位</th>
                            <th width="80">数量</th>
                            <th width="100">自损率</th>
                            <th width="120">生效时间</th>
                            <th width="120">失效时间</th>
                            <th width="120">备注</th>                 
                                            
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
        <div style="text-align:center;margin:16px 0">
        <input type="submit" name="approve" value="核准" style="background:#27ae60;color:#fff;border:none;padding:7px 34px;border-radius:3px;cursor:pointer;font-size:14px" />&nbsp;&nbsp;&nbsp;
        <input type="submit" name="reject" value="拒绝" style="background:#e74c3c;color:#fff;border:none;padding:7px 34px;border-radius:3px;cursor:pointer;font-size:14px" />&nbsp;&nbsp;&nbsp;
        <input type="button" value="返回" onclick="location.href=\'BOMApprove.php\'" style="background:#95a5a6;color:#fff;border:none;padding:7px 34px;border-radius:3px;cursor:pointer;font-size:14px" />
        </div>';
        '</div>';
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