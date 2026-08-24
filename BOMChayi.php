<?php

ob_start();
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include('includes/session.inc');
$Title = _('BOM比对');
$ViewTopic = 'BOM比对';
$BookMark = 'BOM比对';
//$_SESSION['DisplayRecordsMax']=300;
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

if (isset($_POST['Search']) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])) {



    $sqla = "delete from bom_compare_all  where created_by ='".$_SESSION['UserID'] ."' ";
    $resulta = DB_query($sqla, $db);
    $sqla = "insert into bom_compare_all (assembly_item_no,operation_seq_num,component_item,item_num,component_quantity,sunhao_rate,weizhi,created_by)
    SELECT assembly_item_no,operation_seq_num,component_item,item_num,component_quantity,sunhao_rate,weizhi,'".$_SESSION['UserID'] ."'
	FROM bom_lines_all  WHERE disable_date=0 and   bom_header_id = '".$_POST['bom_header_ida'] ."' order by operation_seq_num,component_item ";
    $resulta = DB_query($sqla, $db);
    
    
  

$sqlb = "SELECT *
FROM bom_lines_all  WHERE disable_date = 0 and 	bom_header_id = '".$_POST['bom_header_idb'] ."'  order by operation_seq_num,component_item ";
     $resultb = DB_query($sqlb, $db);
     while  ($myrowb = DB_fetch_array($resultb) ) {
        $sql1 = "SELECT *
        FROM bom_compare_all  WHERE operation_seq_num = '".$myrowb['operation_seq_num'] ."' 
        and component_item = '".$myrowb['component_item'] ."'   ";
        $result1 = DB_query($sql1, $db);
        if (DB_num_rows($result1) == 0) {
            $sql2 = "insert into bom_compare_all (assembly_item_no2,operation_seq_num2,component_item2,item_num2,component_quantity2,sunhao_rate2,weizhi2,
            created_by) values ('".$myrowb['assembly_item_no'] ."','".$myrowb['operation_seq_num'] ."','".$myrowb['component_item'] ."','".$myrowb['item_num'] ."'
            ,'".$myrowb['component_quantity'] ."','".$myrowb['sunhao_rate'] ."','".$myrowb['weizhi'] ."','".$_SESSION['UserID'] ."' ) ";
            $result2 = DB_query($sql2, $db);
        }  else  {
            while  ($myrow1 = DB_fetch_array($result1) ) {
            $sqla = "update bom_compare_all 
            set assembly_item_no2 = '".$myrowb['assembly_item_no'] ."'
             ,operation_seq_num2 = '".$myrowb['operation_seq_num'] ."'
            ,component_item2 = '".$myrowb['component_item'] ."'
            ,item_num2 = '".$myrowb['item_num'] ."'
            ,component_quantity2 = '".$myrowb['component_quantity'] ."'
            ,sunhao_rate2 = '".$myrowb['sunhao_rate'] ."'
            ,weizhi2 = '".$myrowb['weizhi'] ."'
             WHERE  operation_seq_num = '".$myrowb['operation_seq_num'] ."' 
            and component_item = '".$myrowb['component_item'] ."'   ";
            $result2 = DB_query($sqla, $db);
        }  


    }
}

    $sql = "SELECT b.*,(select item_name from sf_item_no a where b.component_item=a.item_no) item_name,
    (select item_name from sf_item_no a where b.component_item2=a.item_no) item_name2
FROM bom_compare_all b  WHERE	created_by = '".$_SESSION['UserID']."' 
    AND (
        REPLACE(IFNULL(b.operation_seq_num,''),' ','') <> REPLACE(IFNULL(b.operation_seq_num2,''),' ','') 
        OR REPLACE(IFNULL(b.component_item,''),' ','') <> REPLACE(IFNULL(b.component_item2,''),' ','') 
        OR REPLACE(IFNULL(b.component_quantity,''),' ','') <> REPLACE(IFNULL(b.component_quantity2,''),' ','') 
        OR REPLACE(IFNULL(b.sunhao_rate,''),' ','') <> REPLACE(IFNULL(b.sunhao_rate2,''),' ','') 
        OR REPLACE(IFNULL(b.weizhi,''),' ','') <> REPLACE(IFNULL(b.weizhi2,''),' ','')
    )
    order by operation_seq_num,component_item "; 
     
    $result = DB_query($sql, $db);
    if (DB_num_rows($result) == 0) {
        unset($result);
        prnMsg(_('找不到该BOM，请重新输入条件查询！'), 'error');
    }
}


echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('BOM比对') . '</p>';
echo '<table cellpadding="3" class="selection">';

if ( $_POST['all_flag']=='') { 

    $_POST['all_flag']='Y';
}
    
echo '<div class="text-nav">';


echo '<div class="text-nav-1" style="color: blue;"><div>' . _('产品料号') . ':</div>';
echo '<input type="text"  required="required" id="text_slect_buliao" name="item_noa" value="' . $_POST['item_noa'] .
    '" size="20" maxlength="25" />
    <a class="btn btn-info btn-xs" id="btn_slect_buliaoa" hfre="###" title="选择产品">选</a>
    </div>';
 echo '<div class="text-nav-1" style="color: blue;"><div>' . _('料号名称') . ':</div>';
echo '<input type="text" id="text_slect_item_name" name="item_namea" value="' . $_POST['item_namea'] .
    '" size="20" maxlength="25" /> 
    </div>';
    echo '<div class="text-nav-1" style="color: blue;"><div>' . _('规格型号') . ':</div>';
    echo '<input type="text" id="item_desc" name="item_desca" value="' . $_POST['item_desca'] . '" size="50" maxlength="100" /></div>';
    echo '<div class="text-nav-1" style="color: blue;"><div>' . _('版本') . ':</div>';
echo '<input type="text" id="version" name="versiona" value="' . $_POST['versiona'] . '" size="50" maxlength="100" /></div>';



echo '<div class="text-nav-1" style="color: red;"><div>' . _('产品料号') . ':</div>';
echo '<input type="text"  required="required" id="text_slect_buliaob" name="item_nob" value="' . $_POST['item_nob'] .
    '" size="20" maxlength="25" />
    <a class="btn btn-info btn-xs" id="btn_slect_buliaob" hfre="###" title="选择产品">选</a>
    </div>';
echo '<div class="text-nav-1" style="color: red;"><div>' . _('料号名称') . ':</div>';
echo '<input type="text" id="text_slect_item_nameb" name="item_name" value="' . $_POST['item_name'] .
    '" size="20" maxlength="25" /> 
    </div>';
    echo '<div class="text-nav-1" style="color: red;"><div>' . _('规格型号') . ':</div>';
    echo '<input type="text" id="item_descb" name="item_desc" value="' . $_POST['item_desc'] . '" size="50" maxlength="100" /></div>';
    echo '<div class="text-nav-1" style="color: red;"><div>' . _('版本') . ':</div>';
echo '<input type="text" name="versionb" id="versionb" value="' . $_POST['versionb'] . '" size="50" maxlength="100" />
<input type="hidden" name="bom_header_ida" id="bom_header_id" value="' . $_POST['bom_header_ida'] . '" size="50" maxlength="100" />
<input type="hidden" name="bom_header_idb" id="bom_header_idb" value="' . $_POST['bom_header_idb'] . '" size="50" maxlength="100" /></div>';
 
echo '</div>';
echo '</table><div class="centre"><input type="submit" name="Search" value="查找"> </div>'
 . '</br>';

if (isset($_POST['Search']) and isset($result) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])) {
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
    echo ' <div class="text-nav-table">
                    <table cellpadding="2" class="selection" >';

    echo '<tr> <th  style="color: blue;" width = 20>' . _('工序') . '</th>
					<th style="color: blue;"  >' . _('序号') . '</th>
                    <th style="color: blue;"   >' . _('产品料号') . '</th>
                    <th style="color: blue;"   >' . _('料号名称') . '</th>
					<th style="color: blue;"  >' . _('单耗') . '</th>
                    <th style="color: blue;"   >' . _('损耗率') . '</th>  
                    <th style="color: blue;"   >' . _('位置') . '</th>               
					<th  width = 20>' . _('工序') . '</th>
					<th bgcolor="#87CEFA"  >' . _('序号') . '</th>
                    <th bgcolor="#87CEFA"   >' . _('料号') . '</th>
                    <th bgcolor="#87CEFA"   >' . _('料号名称') . '</th>
					<th bgcolor="#87CEFA"  >' . _('单耗') . '</th>
                    <th bgcolor="#87CEFA"   >' . _('损耗率') . '</th> 
                    <th bgcolor="#87CEFA"   >' . _('位置') . '</th> 
            </tr>';
    $k = 0; //row counter to determine background colour
    $RowIndex = 0;



    if (DB_num_rows($result) <> 0) {
        DB_data_seek($result, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);
        $i = 0; //counter for input controls
        while (($myrow = DB_fetch_array($result)) AND ( $RowIndex <> $_SESSION['DisplayRecordsMax'] )) {
            if ($k == 1) {
                echo '<tr class="EvenTableRows">';
                $k = 0;
            } else {
                echo '<tr class="OddTableRows">';
                $k = 1;
            }
            // 去掉空格后逐个栏位判断差异，序号(item_num)不作为对比，不标红
            $s1 = (str_replace(' ','',$myrow['operation_seq_num']) != str_replace(' ','',$myrow['operation_seq_num2'])) ? 'style="color: red;"' : '';
            $s2 = ''; // 序号不对比，永远不高亮
            $s3 = (str_replace(' ','',$myrow['component_item']) != str_replace(' ','',$myrow['component_item2'])) ? 'style="color: red;"' : '';
            $s4 = (str_replace(' ','',$myrow['item_name']) != str_replace(' ','',$myrow['item_name2'])) ? 'style="color: red;"' : '';
            $s5 = (str_replace(' ','',$myrow['component_quantity']) != str_replace(' ','',$myrow['component_quantity2'])) ? 'style="color: red;"' : '';
            $s6 = (str_replace(' ','',$myrow['sunhao_rate']) != str_replace(' ','',$myrow['sunhao_rate2'])) ? 'style="color: red;"' : '';
            $s7 = (str_replace(' ','',$myrow['weizhi']) != str_replace(' ','',$myrow['weizhi2'])) ? 'style="color: red;"' : '';
            $s8 = (str_replace(' ','',$myrow['operation_seq_num']) != str_replace(' ','',$myrow['operation_seq_num2'])) ? 'style="color: red;"' : '';
            $s9 = ''; // 序号不对比，永远不高亮
            $s10 = (str_replace(' ','',$myrow['component_item']) != str_replace(' ','',$myrow['component_item2'])) ? 'style="color: red;"' : '';
            $s11 = (str_replace(' ','',$myrow['item_name']) != str_replace(' ','',$myrow['item_name2'])) ? 'style="color: red;"' : '';
            $s12 = (str_replace(' ','',$myrow['component_quantity']) != str_replace(' ','',$myrow['component_quantity2'])) ? 'style="color: red;"' : '';
            $s13 = (str_replace(' ','',$myrow['sunhao_rate']) != str_replace(' ','',$myrow['sunhao_rate2'])) ? 'style="color: red;"' : '';
            $s14 = (str_replace(' ','',$myrow['weizhi']) != str_replace(' ','',$myrow['weizhi2'])) ? 'style="color: red;"' : '';

            echo '   
                <td ' . $s1 . '>' . $myrow['operation_seq_num'] . '</td> 
                <td ' . $s2 . '>' . $myrow['item_num'] . '</td>  	
                <td ' . $s3 . '>' . $myrow['component_item'] . '</td>   	
                <td ' . $s4 . '>' . $myrow['item_name'] . '</td>  	
                <td ' . $s5 . '>' . $myrow['component_quantity'] . '</td> 
                <td ' . $s6 . '>' . $myrow['sunhao_rate'] . '</td> 
                <td ' . $s7 . '>' . $myrow['weizhi'] . '</td> 
                <td ' . $s8 . '>' . $myrow['operation_seq_num2'] . '</td> 	
                <td ' . $s9 . '>' . $myrow['item_num2'] . '</td>  	
                <td ' . $s10 . '>' . $myrow['component_item2'] . '</td> 	
                <td ' . $s11 . '>' . $myrow['item_name2'] . '</td>  
                <td ' . $s12 . '>' . $myrow['component_quantity2'] . '</td> 
                <td ' . $s13 . '>' . $myrow['sunhao_rate2'] . '</td> 
                <td ' . $s14 . '>' . $myrow['weizhi2'] . '</td>   '; 

            echo '
			</tr>';
            $i++;
            $RowIndex++;
            //end of page full new headings if
        } //end loop through vendors
        echo '</table></div>';
        echo '<input type="hidden" name="JustSelectedAvendor" value="Yes" />';
    }

    if (isset($ListPageMax) AND $ListPageMax > 1) {
        echo '<br /><div class="centre">&nbsp;&nbsp;' . _('第') . '' . $_POST['PageOffset'] . ' ' . _('页，共') . ' ' . $ListPageMax . ' ' . _('页数') . '. ' . _('转到页') . ': ';
        echo '<select name="PageOffset2">';
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
        <input type="submit" name="Go2" value="' . _('转到') . '" />
        <input type="submit" name="Previous" value="' . _('上一页') . '" />
        <input type="submit" name="Next" value="' . _('下一页') . '" />';
        echo '</div>';
    }
}
echo '</div></form>';

include('includes/footer.inc');

?>

<script type="text/javascript">
$('#btn_slect_buliaoa').dialog({
    title: '选择产品',
    width: '1200px',
    height: 470,
    content: 'url:BtnSearchBOMA.php?fwValue=&cat=buliao',
    init: function() {
        this.content.document.getElementById('cat').value = 'buliao';
        this.content.document.getElementById('fwValue').value = '';
    }
});
$('#btn_slect_buliaob').dialog({
    title: '选择产品',
    width: '1200px',
    height: 470,
    content: 'url:BtnSearchBOMB.php?fwValue=&cat=buliao',
    init: function() {
        this.content.document.getElementById('cat').value = 'buliao';
        this.content.document.getElementById('fwValue').value = '';
    }
});
</script>