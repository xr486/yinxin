<?php
 ob_start();
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include('includes/session.inc');
$Title = _('库存交易明细报表');
$ViewTopic= '库存交易明细报表';
$BookMark = '库存交易明细报表';

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

unset($result);

if (isset($_POST['Go1']) OR isset($_POST['Go2'])) {
	$_POST['PageOffset'] = (isset($_POST['Go1']) ? $_POST['PageOffset1'] : $_POST['PageOffset2']);
	$_POST['Go'] = '';
}
if (!isset($_POST['PageOffset'])) {
	$_POST['PageOffset'] = 1;
} else {
	if ($_POST['PageOffset'] == 0) {
		$_POST['PageOffset'] = 1;
	}
}
 
if (!isset($_POST['FromDate'])) {
    $_POST['FromDate'] = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 30, date("Y")));
}
if (!isset($_POST['ToDate'])) {
    $_POST['ToDate'] = Date('Y-m-d', mktime(0, 0, 0, date("m"), date("d") + 1, date("Y")));
}
  $sql = "SELECT a.trans_num,a.transaction_type,
                   c.item_no, a.uom, c.item_name,
                   a.subinventory_from,a.request_person,
                   d.loccode, d.locationname,  
                   a.request_person,(SELECT b.employee_name       
                                     FROM hr_employees b
                                     WHERE a.request_person = b.employee_num) 
                   employee_name, 
                   c.item_desc, a.quantity,
                   a.creation_date,a.remark,
                   a.transaction_date ,a.lot_num,(SELECT b.realname       
         FROM www_users b
         WHERE a.created_by = b.userid) 
                                     created_by
            FROM inv_transactions_all a,sf_item_no c, 
                 locations d
            WHERE   a.item_no = c.item_no 
            AND a.subinventory_from = d.loccode   ";
			 if(isset($_POST['FromDate']) and $_POST['FromDate'] != ''){
        $sql = $sql." and a.transaction_date >=".strtotime($_POST['FromDate'])." ";
    }
    if(isset($_POST['ToDate']) and $_POST['ToDate'] != ''){
        $sql = $sql." and a.transaction_date <=".strtotime($_POST['ToDate'])." ";
    }
			  $sql = $sql . " order by a.transaction_date desc";
			 $result = DB_query($sql,$db);
if(isset($_POST['Search']) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])){
    $sql = "SELECT a.trans_num,a.transaction_type,
                   c.item_no, a.uom, c.item_name,
                   a.subinventory_from,a.request_person,
                   d.loccode, d.locationname,  
                   a.request_person,(SELECT b.employee_name       
                                     FROM hr_employees b
                                     WHERE a.request_person = b.employee_num) 
                   employee_name, 
                   c.item_desc, a.quantity,
                   a.creation_date,a.remark,
                   a.transaction_date,a.lot_num,(SELECT b.realname       
         FROM www_users b
         WHERE a.created_by = b.userid)  
                                     created_by 
            FROM inv_transactions_all a,sf_item_no c, 
                 locations d
            WHERE   a.item_no = c.item_no 
            AND a.subinventory_from = d.loccode  ";
  
    if(isset($_POST['FromDate']) and $_POST['FromDate'] != ''){
        $sql = $sql." and a.transaction_date >=".strtotime($_POST['FromDate'])." ";
    }
    if(isset($_POST['ToDate']) and $_POST['ToDate'] != ''){
        $sql = $sql." and a.transaction_date <=".strtotime($_POST['ToDate'])." ";
    }
    if (isset($_POST['item_no']) and $_POST['item_no'] != '') { 		
        $sql = $sql." and c.item_no ".LIKE." '%".$_POST['item_no']."%' ";
    } 
    if (isset($_POST['item_name']) and $_POST['item_name'] != '') { 		
        $sql = $sql." and c.item_name ".LIKE." '%".$_POST['item_name']."%' ";
    }  
    if (isset($_POST['item_desc']) and $_POST['item_desc'] != '') { 		
        $sql = $sql." and c.item_desc ".LIKE." '%".$_POST['item_desc']."%' ";
    }  
    if (isset($_POST['trans_num_from']) and $_POST['trans_num_from'] != '') { 		
        $sql = $sql." and a.trans_num ".LIKE." '%".$_POST['trans_num_from']."%' ";
    }  if (isset($_POST['lot_num']) and $_POST['lot_num'] != '') { 		
        $sql = $sql." and a.lot_num ".LIKE." '%".$_POST['lot_num']."%' ";
    } 
    
    if (isset($_POST['loccode_from']) and $_POST['loccode_from'] != '') {	
        $sql = $sql." and a.subinventory_from ".LIKE." '%".$_POST['loccode_from']."%' "; 
    } 
    if (isset($_POST['transaction_type']) and $_POST['transaction_type'] != '' and $_POST['transaction_type'] != '全部') {
        $sql = $sql . " and  a.transaction_type =  '" . $_POST['transaction_type'] . "' ";
    } 
    $sql = $sql . " order by a.transaction_date desc";
    $result = DB_query($sql,$db);
    if (DB_num_rows($result)==0)
    {
        //unset($result);
        prnMsg(_('找不到该暂收单，请重新输入条件查询！') ,'error');
    }
}


echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '
        <div class="centre" style="margin-bottom: 5px; display: flex;justify-content: flex-start;align-items: center;">
        <input type="submit" name="Search" value="查找"> &nbsp;&nbsp;
          <div class="export" >
          <a href="' . $RootPath . '/InvTxnReportExcel.php?FromDate=' .$_POST['FromDate'] .'&ToDate=' .$_POST['ToDate'] .'&subinventory_code=' .$_POST['loccode_from'] .
           '&item_no=' .$_POST['item_no']. '&item_name=' .$_POST['item_name'] .'&trans_num=' .$_POST['trans_num_from'] .'&transaction_type=' .$_POST['transaction_type'] . '">
                ' .'导出' . '
                </a>
          </div>
        </div>
';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('库存交易明细报表') . '</p>';
echo '<table cellpadding="3" class="selection">';
echo '<div class="text-nav">';
 
if (!isset($_POST['FromDate'])) {
    $_POST['FromDate'] = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 30, date("Y")));
}
if (!isset($_POST['ToDate'])) {
    $_POST['ToDate'] = Date('Y-m-d', mktime(0, 0, 0, date("m"), date("d") + 1, date("Y")));
}
echo '<div class="text-nav-1"><div>' . '交易日期' . _('起') . ':</div>
		<input type="text"   autocomplete="off"   onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="FromDate" maxlength="10" size="11" value="' . $_POST['FromDate'] . '" /></div>
		<div class="text-nav-1"><div>' . _('交易日期止') . ':</div>
		<input type="text"   autocomplete="off"   onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="ToDate" maxlength="10" size="11" value="' . $_POST['ToDate'] . '" /></div>
	';
echo '<div class="text-nav-1"><div>' . _('仓库') . ':</div>';
echo '<input type="text"   autocomplete="off"   name="loccode_from" value="' . $_POST['loccode_from'] . '" size="20" maxlength="25" /></div>';
echo '<div class="text-nav-1"><div>' . _('料号') . ':</div>';
echo '<input type="text"   autocomplete="off"   id="text_slect_buliao" name="item_no" value="' . $_POST['item_no'] . '" size="20" maxlength="25" />
<image class="select_img" src="img/search.png" id="btn_slect_buliao"/>
</div>';
echo '<div class="text-nav-1"><div>' . _('料号名称') . ':</div>';
echo '<input type="text"   autocomplete="off"   id="text_slect_item_name" name="item_name" value="' . $_POST['item_name'] . '" size="20" maxlength="25" />
<image class="select_img" src="img/search.png" id="btn_slect_buliao2"/>
</div>';

echo '<div class="text-nav-1"><div>' . _('规格型号') . ':</div>';
echo '<input type="text"   autocomplete="off"   id="text_slect_item_desc" name="item_desc" value="' . $_POST['item_desc'] . '" size="20" maxlength="25" />
</div>';
echo '<div class="text-nav-1"><div>' . _('交易单号') . ':</div>';
echo '<input type="text"   autocomplete="off"   name="trans_num_from" value="' . $_POST['trans_num_from'] . '" size="20" maxlength="25" /></div>';
echo '<div class="text-nav-1"><div>' . _('批号') . ':</div>';
echo '<input type="text"   autocomplete="off"   name="lot_num" value="' . $_POST['lot_num'] . '" size="20" maxlength="25" /></div>';

echo '<div class="text-nav-1"><div>' . _('交易类型') . ':</div>';
$sql = "select '全部' transaction_type from dual union SELECT transaction_type FROM inv_transactions_all ";
$result1 = DB_query($sql, $db);
echo '<select name="transaction_type">';
while ($Salesmanrow = DB_fetch_array($result1)) {
    if($Salesmanrow['transaction_type']==$_POST['transaction_type']){
        
        echo '<option  value="' . $Salesmanrow['transaction_type'] . '" selected="selected">' . $Salesmanrow['transaction_type'] . '</option>';
    }else{

        echo '<option  value="' . $Salesmanrow['transaction_type'] . '">' . $Salesmanrow['transaction_type'] . '</option>';
    }
}

echo '</select></div>'; 
echo '</div>'; 
 
echo '</table><div class="centre"></div>';
//总计
if ( isset($result)) {
	$total_line=0;
	 while (($myrow2 = DB_fetch_array($result))  ) {
		        
						$total_line=$total_line+1;
                        
      }
  }


if (isset($_POST['Search']) or isset($result) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])){
    $ListCount = DB_num_rows($result);
    $ListPageMax = ceil($ListCount / $_SESSION['DisplayRecordsMax']);
    
    if (isset($_POST['Next'])) {
            if ($_POST['PageOffset'] < $ListPageMax) {
                    $_POST['PageOffset'] = $_POST['PageOffset'] + 1;
            }
    }
    if (isset($_POST['Previous'])) {
            if ($_POST['PageOffset'] > 1) {
                    $_POST['PageOffset'] = $_POST['PageOffset'] - 1;
            }
    }
    echo '<input type="hidden" name="PageOffset" value="' . $_POST['PageOffset'] . '" />';
    if ($ListPageMax > 1) {
        echo '<br /><div class="centre">&nbsp;&nbsp;' . _('第') . '' . $_POST['PageOffset'] . ' ' . _('页，共') . ' ' . $ListPageMax . ' ' . _('页') . '. ' . _('转到页') . ': ';
        echo '<select name="PageOffset1">';
            $ListPage = 1;
            while ($ListPage <= $ListPageMax) {
                    if ($ListPage == $_POST['PageOffset']) {
                            echo '<option value="' . $ListPage . '" selected="selected">' . $ListPage . '</option>';
                    } else {
                            echo '<option value="' . $ListPage . '">' . $ListPage . '</option>';
                    }
                    $ListPage++;
            }
            echo '</select>
            <input type="submit" name="Go1" value="' . _('转到') . '" />
            <input type="submit" name="Previous" value="' . _('上一页') . '" />
            <input type="submit" name="Next" value="' . _('下一页') . '" />';
            echo '</div>';
    }
    echo '			  <div class="text-nav-table">
                    <table cellpadding="2" class="selection" >';

    echo '<tr>                       
						<th width =30>' . '打印' . '</th>
						<th width =100>' . '交易单号' . '</th>
						<th  width = 100>' . _('交易类型') . '</th> 
						<th width =100>' . '料号' . '</th>
                        <th width =150>' . '料号名称' . '</th>
						<th width =150>' . '规格型号' . '</th>
						<th width =70 >' . '交易日期' . '</th> 
						<th width =70 >' . '交易数量' . '</th> 
						<th width =70 >' . '单位' . '</th> 
						<th width =60 >' . '仓库' . '</th>
						<th width =60 >' . '批号' . '</th>
						<th width =80 >' . '申请人工号' . '</th>
						<th  width = 100>' . _('申请人名称') . '</th> 										
						<th  width = 100>' . _('备注') . '</th> 
						<th  width = 100>' . _('单据建立日期') . '</th>  
						<th  width = 100>' . _('建立人员') . '</th>                                                             
            </tr>';
            $k = 0; //row counter to determine background colour
            $RowIndex = 0;
            $all_line=0; 
        
            if (DB_num_rows($result) <> 0) {
                DB_data_seek($result, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);
                $i = 0; //counter for input controls
                while (($myrow = DB_fetch_array($result)) and ($RowIndex <> $_SESSION['DisplayRecordsMax'])) {
                    if ($k == 1) {
                        echo '<tr class="EvenTableRows">';
                        $k = 0;
                    } else {
                        echo '<tr class="OddTableRows">';
                        $k = 1;
                    }
                    $all_line=$all_line+1;
		         if($myrow['transaction_type'] == '订单出货'){
                    echo '<td><a class="tz" href="' . $RootPath . '/PrintDeliveryPDF.php?Updatedelivery_num=' .$myrow['trans_num'] . '"  target="_blank" >打印</a></td>';
                 }elseif($myrow['transaction_type'] == '销售退回'){
                    echo '<td><a class="tz" href="' . $RootPath . '/PrintDeliveryOrder.php?Updatedelivery_num=' .$myrow['trans_num'] . '"  target="_blank" >打印</a></td>';
                 }elseif($myrow['transaction_type'] == 'POIN'){
                    echo '<td><a class="tz" href="' . $RootPath . '/InPODeliveryPDF.php?NUM=' .$myrow['trans_num'] . '"  target="_blank" >打印</a></td>';
                 }elseif($myrow['transaction_type'] == 'PORETURN') {
                    echo '<td><a class="tz" href="' . $RootPath . '/ReturnPODeliveryPDF.php?NUM=' .$myrow['trans_num'] . '"  target="_blank" >打印</a></td>';
                 }elseif($myrow['transaction_type']  ==  '制令单领料'  )  {
                    echo '<td><a class="tz" href="' . $RootPath . '/PrintWIPIssue.php?Updatedelivery_num=' .$myrow['trans_num'] . '"  target="_blank" >打印</a></td>';
                 } elseif($myrow['transaction_type'] == '定额损耗' )  {
                    echo '<td><a class="tz" href="' . $RootPath . '/PrintWIPIssue.php?Updatedelivery_num=' .$myrow['trans_num'] . '"  target="_blank" >打印</a></td>';
                 }elseif($myrow['transaction_type'] == '不良损耗' )  {
                    echo '<td><a class="tz" href="' . $RootPath . '/PrintWIPIssue.php?Updatedelivery_num=' .$myrow['trans_num'] . '"  target="_blank" >打印</a></td>';
                 } elseif($myrow['transaction_type'] == '制令单退料'){
                    echo '<td><a class="tz" href="' . $RootPath . '/PrintWIPReturn.php?Updatedelivery_num=' .$myrow['trans_num'] . '"  target="_blank" >打印</a></td>';
                 }elseif($myrow['transaction_type'] == '工单入库'){
                    echo '<td><a class="tz" href="' . $RootPath . '/WIPCompleteInSubPDF.php?OrderNum=' .$myrow['trans_num'] . '"  target="_blank" >打印</a></td>';
                 }elseif($myrow['quantity'] > 0){
                    
                    echo '<td><a class="tz" href="' . $RootPath . '/PrintInHouse.php?OrderNum=' .$myrow['trans_num'] . '"  target="_blank" >打印</a></td>';
                 }else{
                    echo '<td><a class="tz" href="' . $RootPath . '/PrintOutHouse.php?OrderNum=' .$myrow['trans_num'] . '"  target="_blank" >打印</a></td>';

                 }
			echo '<td>' . $myrow['trans_num'] . ' </td>';
			echo '<td>' . $myrow['transaction_type'] . '</td>';
		    echo '<td>' . $myrow['item_no'] . ' </td>';
            echo '<td>' . $myrow['item_name'] . ' </td>';
            echo '<td>' . $myrow['item_desc'] . ' </td>';
			echo '<td>' . date('Y-m-d',$myrow['transaction_date']) . '</td>';
			echo '<td>' . $myrow['quantity'] . ' </td>';
			echo '<td>' . $myrow['uom'] . ' </td>';
			echo '<td>' . $myrow['loccode']. ' </td>';
			echo '<td>' . $myrow['lot_num']. ' </td>';
            echo '<td>' . $myrow['request_person'] . '</td>';
			echo '<td>' . $myrow['employee_name'] . '</td>';
			echo '<td>' . $myrow['remark'] . '</td>';
			echo '<td>' . date('Y-m-d H:i:s',$myrow['creation_date']) . '</td>';
			echo '<td>' . $myrow['created_by'] . '</td>';
            
			echo '
			</tr>';
			$i++;
			$RowIndex++;
			//end of page full new headings if
		} //end loop through vendors
        echo'<tr> <td>小计</td> <td>笔数</td> <td>'.$all_line.'</td> <td></td> <td></td> <td></td> <td></td> <td></td> <td></td> <td></td> <td></td> <td></td> <td></td> <td></td> </tr>'; 
        echo'<tr> <td>总计</td> <td>笔数</td> <td>'.$total_line.'</td> <td></td> <td></td> <td></td> <td></td> <td></td> <td></td> <td></td> <td></td> <td></td> <td></td> <td></td> </tr>'; 

		echo '</table></div>';
		echo '<input type="hidden" name="JustSelectedAvendor" value="Yes" />';
	}

        if (isset($ListPageMax) AND $ListPageMax > 1) {
            echo '<br /><div class="centre">&nbsp;&nbsp;' . _('第') . '' . $_POST['PageOffset'] . ' ' . _('页，共') . ' ' . $ListPageMax . ' ' . _('页') . '. ' . _('转到页') . ': ';
            echo '<select name="PageOffset2">';
                $ListPage = 1;
                while ($ListPage <= $ListPageMax) {
                        if ($ListPage == $_POST['PageOffset']) {
                                echo '<option value="' . $ListPage . '" selected="selected">' . $ListPage . '</option>';
                        } 
                        else {
                                echo '<option value="' . $ListPage . '">' . $ListPage . '</option>';
                        }
                        $ListPage++;
                } 
                echo '</select>
                <input type="submit" name="Go2" value="' . _('转到') . '" />
                <input type="submit" name="Previous" value="' . _('上一页') . '" />
                <input type="submit" name="Next" value="' . _('下一页') . '" />';
                echo '</div>';
    }
    echo '<br>
          <div class="centre">
                
          </div>';
    //echo '' . $RootPath . '/InOutSOCheckListExcel.php?C1=' .$_POST['FromDate'] . '&C2=' .$_POST['ToDate'] . '&C3=' .$_POST['item_no'] . '&C4=' .$_POST['Stockid_to'] . '&C5=' .$_POST['trans_num_from'] . '&C6=' .$_POST['trans_num_to'] . '&C7=' .$_POST['loccode_from']. '&C8=' .$_POST['loccode_to']. '&C9=' .$_POST['transaction_type'] . '';
}

echo '</div></form>';
if (isset($_POST['add_new'])) {
    header('Location: AddSupplier.php');
}
include('includes/footer.inc');
?>
<script type="text/javascript" src ="./JXC/javascripts/miscfunctions.js"></script>
<script type="text/javascript" src ="./JXC/javascripts/wdatepicker.js"></script>
<script type="text/javascript">var basepath='./JXC/statics/base/images';</script>
<script type="text/javascript" src="./JXC/statics/base/js/metvar.js"></script>
<!-- <script type="text/javascript" src="./JXC/statics/base/js/jQuery1.7.2.js"></script> -->
<script type="text/javascript" src="./JXC/statics/base/js/uploadify/jquery.uploadify.v2.1.4.min.js"></script>
<script type="text/javascript" src="./JXC/statics/base/js/iframes.js"></script>
<script type="text/javascript" src="./JXC/statics/base/js/cookie.js"></script>
<script type="text/javascript" src="./JXC/statics/base/js/jquery.livequery.js"></script>
 

<script type="text/javascript" src="node_modules/jquery/dist/jquery.js"></script>

<link rel="stylesheet" href="jquery.ui.autocomplete.css">

<!-- UI -->

<script type="text/javascript" src="node_modules/jquery-ui/dist/jquery-ui.js"></script>

<!-- <script type="text/javascript" src="node_modules/jquery-ui/ui/core.js"></script> -->

<script type="text/javascript" src="node_modules/jquery-ui/ui/widget.js"></script>

<script type="text/javascript" src="node_modules/jquery-ui/ui/position.js"></script>

<script type="text/javascript" src="node_modules/jquery-ui/ui/widgets/menu.js"></script>

<script type="text/javascript" src="node_modules/jquery-ui/ui/widgets/autocomplete.js"></script>

<!-- <script src="./JXC/javascript/jquery-1.7.2.min.js"></script> -->
<script src="./JXC/javascript/lhgdialog.min.js?self=true&skin=chrome"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
<script src="./javascript/bootstrap.min.js"></script>
<script type="text/javascript">
  var menuLinks = document.querySelectorAll(".tz");
		menuLinks.forEach(function(link) {
			link.addEventListener("click", function(e) {
                e.preventDefault();
				var url = this.href;
				var tabName = this.innerText;
				window.parent.loadContent(url, tabName);
			});
		});
    $('#btn_slect_buliao').dialog({
        title: '选择产品',
        width: '1200px',
        height: 470,
        content: 'url:Searchbuliao517.php?fwValue=&cat=buliao',
        init: function() {
            this.content.document.getElementById('cat').value = 'buliao';
            this.content.document.getElementById('fwValue').value = '';
        }
    });
    $('#btn_slect_buliao2').dialog({
        title: '选择产品',
        width: '1200px',
        height: 470,
        content: 'url:Searchbuliao517.php?fwValue=&cat=buliao',
        init: function() {
            this.content.document.getElementById('cat').value = 'buliao';
            this.content.document.getElementById('fwValue').value = '';
        }
    });

    $("#text_slect_buliao").autocomplete({
                    source: "autosearchitem.php",
                    minLength: 1,
                    autoFocus: true,
            });
            $("#text_slect_item_name").autocomplete({
                    source: "autosearchitemname.php",
                    minLength: 1,
                    autoFocus: true,
            });
            $("#text_slect_item_desc").autocomplete({
                    source: "autosearchitemdesc.php",
                    minLength: 1,
                    autoFocus: true,
            });
</script>