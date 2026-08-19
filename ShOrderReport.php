<?php

ob_start();
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
include('includes/session.inc');
$Title = _('售后服务单查询报表');
$ViewTopic = '售后服务单查询报表';
$BookMark = '售后服务单查询报表';

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
  $sql="SELECT 
    c.customer_code,
    c.customer_name, 
    c.customer_address,
    a.sh_order_num,  
    a.moju_num,
    a.moju_name, 
    a.creation_date,
    a.created_by
 FROM sh_order_headers_all a,sh_order_lines_all b,customers c   
 WHERE a.customer_code = c.customer_code 
 and a.sh_order_num=b.sh_order_num " ;

    if (isset($_POST['customer_code']) and $_POST['customer_code'] != '') {
        $sql = $sql . " and c.customer_code " . LIKE . " '%" . $_POST['customer_code'] . "%' ";
    }
    if (isset($_POST['customer_name']) and $_POST['customer_name'] != '') {
        $sql = $sql . " and c.customer_name " . LIKE . " '%" . $_POST['customer_name'] . "%' ";
    }
    if (isset($_POST['moju_num']) and $_POST['moju_num'] != '') {
        $sql = $sql . " and a.moju_num " . LIKE . " '%" . $_POST['moju_num'] . "%' ";
    }
    if (isset($_POST['moju_name']) and $_POST['moju_name'] != '') {
        $sql = $sql . " and a.moju_name " . LIKE . " '%" . $_POST['moju_name'] . "%' ";
    }
    if (isset($_POST['sh_order_num']) and $_POST['sh_order_num'] != '') {
        $sql = $sql . " and a.sh_order_num " . LIKE . " '%" . $_POST['sh_order_num'] . "%' ";
    }
    $sql .=" order by a.sh_order_num";
    $result = DB_query($sql, $db);
    if (DB_num_rows($result) == 0) {
        unset($result);
        prnMsg(_('找不到售后单报表，请重新输入条件查询！'), 'error');
    }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>售后服务单查询报表</title>
<link rel="shortcut icon" href="/favicon.ico"/>
<link rel="icon" href="/favicon.ico"/>
<meta http-equiv="Content-Type" content="application/html; charset=utf-8"/>
<link href="/css/xenos/default.css" rel="stylesheet" type="text/css"/>
<script type="text/javascript" src ="./JXC/javascripts/miscfunctions.js"></script>
<script type="text/javascript" src ="./JXC/javascripts/wdatepicker.js"></script>
<script type="text/javascript">var basepath='./JXC/statics/base/images';</script>
<script type="text/javascript" src="./JXC/statics/base/js/metvar.js"></script>
<script type="text/javascript" src="./JXC/statics/base/js/jQuery1.7.2.js"></script>
<script type="text/javascript" src="./JXC/statics/base/js/uploadify/jquery.uploadify.v2.1.4.min.js"></script>
<script type="text/javascript" src="./JXC/statics/base/js/iframes.js"></script>
<script type="text/javascript" src="./JXC/statics/base/js/cookie.js"></script>
<script type="text/javascript" src="./JXC/statics/base/js/jquery.livequery.js"></script>
<link rel="stylesheet" href="jquery.ui.autocomplete.css">
<script type="text/javascript" src="ui/jquery.ui.core.js"></script>
<script type="text/javascript" src="ui/jquery.ui.widget.js"></script>
<script type="text/javascript" src="ui/jquery.ui.position.js"></script>
<script type="text/javascript" src="ui/jquery.ui.autocomplete.js"></script>
<script src="./JXC/javascript/jquery-1.7.2.min.js"></script>
<script src="./JXC/javascript/lhgdialog.min.js?self=true&skin=chrome"></script>
<!-- Include all compiled plugins (below), or include individual files as needed -->
<script src="./javascript/bootstrap.min.js"></script>
<script type="text/javascript">
/*ajax执行*/
var lang = 'cn';
var metimgurl='./JXC/statics/base/images/';
var depth='';
$(document).ready(function(){
	ifreme_methei();
});
</script>
<script type="text/javascript">
function metreturn(url){
	if(url){
		location.href=url;
	}else if($.browser.msie){
		history.go(-1);
	}else{
		history.go(-1);
	}
} 

function addsave(){
	var v = $('#idcount').val();
	$("#purchase_table_"+v).css("display","");
	var c = parseInt(v) + 1;
	$('#idcount').val(c);
}

</script>
</head>
<body>
<script type="text/javascript">
	$(document).ready(function(){

		$('.divToilet table tr td a').click(function(){
			$(this).parent('td').toggleClass('highlight');
			if(!($(this).parent('td').hasClass('highlight'))) {
				$(this).next().val('0');
			}else {
				$(this).next().val('1');
			}
		});

//btn_slect_vendor
		$('#btn_slect_moju').dialog({
			title:'选择模具编号',
			width: '950px',
			height: 470,
			content:'url:BtnSearchMojuClosed.php?fwValue=&cat=buliao',
			init:function(){
				this.content.document.getElementById('cat').value = 'buliao';
				this.content.document.getElementById('fwValue').value = '';
			}
		});
		
		//Function to get URL arguments

		function getRequest() {
			var url = location.search; //获取url中"?"符后的字串
			var theRequest = new Object();
			if (url.indexOf("?") != -1) {
				var str = url.substr(1);
				strs = str.split("&");
				for(var i = 0; i < strs.length; i ++) {
					theRequest[strs[i].split("=")[0]]=(strs[i].split("=")[1]);
				}
			}
			return theRequest;
		}
	});

	
$(function(){
		$( "#text_slect_vendor" ).autocomplete({
			source: "autosearchvendor.php",
			minLength: 2,
			autoFocus: true
		});
	});
	$(function(){
		$( "#text_slect_name" ).autocomplete({
			source: "autosearchvendor2.php",
			minLength: 2,
			autoFocus: true
		});
	});
	<?php for($i=1;$i<=50;$i++){?> 
	$(function(){
		$( "#text_slect_buliao<?=$i?>" ).autocomplete({
			source: "autosearchstockpo.php",
			minLength: 2,
			autoFocus: true
		});
	});
	<?php }?>                             
                               
</script>
</body>

</html>
<?php
echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('售后服务单查询报表') . '</p>';
echo '<table cellpadding="3" class="selection">';

echo '<tr><td >' . _('模具编号') . ':</td><td>';
echo '<input type="text" id="text_slect_moju" name="moju_num" value="' . $_POST['moju_num'] . '" size="20" maxlength="25" />
<a class="btn btn-info btn-xs" id="btn_slect_moju" hfre="###" title="选择客户简称">选择</a></td>';
echo '<td >' . _('模具名称') . ':</td><td>';
echo '<input type="text" id="text_slect_name" name="moju_name" value="' . $_POST['moju_name'] . '" size="45" maxlength="50" /></td>';
echo '<td >' . _('售后单号') . ':</td><td>';
echo '<input type="text" id="text_slect_sh_order_num" name="sh_order_num" value="' . $_POST['sh_order_num'] . '" size="20" maxlength="25" /></td></tr>';
echo '<tr><td >' . _('客户简称') . ':</td><td>';
echo '<input type="text" id="text_slect_customer_code" name="customer_code" value="' . $_POST['customer_code'] . '" size="20" maxlength="25" /></td>';
echo '<td>' . _('客户名称') . ':</td><td>';
echo '<input type="text" id="text_slect_customer_name" name="customer_name" value="' . $_POST['customer_name'] . '" size="45" maxlength="50" /></td></tr>';

echo '</table><div class="centre"><input type="submit" name="Search" value="查找"> </div>'
 . '</br>';

if (isset($_POST['Search']) and isset($result) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])) {
    $ListCount = DB_num_rows($result);
    $ListPageMax = ceil($ListCount / 10);

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
        echo '<br /><div class="centre">&nbsp;&nbsp;' . _('第') . '' . $_POST['PageOffset'] . ' ' . _('页，共') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ': ';
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
                <input type="submit" name="Go1" value="' . _('Go') . '" />
                <input type="submit" name="Previous" value="' . _('Previous') . '" />
                <input type="submit" name="Next" value="' . _('Next') . '" />';
        echo '</div>';
    }
    echo '<table cellpadding="2" class="selection" >';

    echo '<tr>
            <th class="ascending" width = 100>' . _('售后单号') . '</th>
            <th class="ascending" width = 100>' . _('模具编号') . '</th>                          
            <th class="ascending" width = 200>' . _('模具名称') . '</th>
            <th class="ascending" width = 100>' . _('客户简称') . '</th>
            <th class="ascending" width = 250>' . _('客户地址') . '</th>
            <th class="ascending" width = 100>' . _('建单人员') . '</th>
            <th class="ascending" width = 200>' . _('建单日期') . '</th>
            </tr>';
    $k = 0; //row counter to determine background colour
    $RowIndex = 0;

    if (DB_num_rows($result) <> 0) {
        DB_data_seek($result, ($_POST['PageOffset'] - 1) * 10);
        $i = 0; //counter for input controls
        while (($myrow = DB_fetch_array($result)) AND ( $RowIndex <> 10 )) {
            if ($k == 1) {
                echo '<tr class="EvenTableRows">';
                $k = 0;
            } else {
                echo '<tr class="OddTableRows">';
                $k = 1;
            }
            unset($v_status);
     
            echo '<td>' . $myrow['sh_order_num'] . '</td> 
				<td>' . $myrow['moju_num'] . '</td> 
                <td>' . $myrow['moju_name'] . '</td> 
                <td>' . $myrow['customer_code'] . '</td>
				<td>' . $myrow['customer_address']. '</td>
                <td>' . $myrow['created_by'] . '</td>
				<td>' . date('Y-m-d H:i:s', $myrow['creation_date']) . '</td>
               ';
            echo '
			</tr>';
            $i++;
            $RowIndex++;
            //end of page full new headings if
        } //end loop through vendors
        echo '</table>';
        echo '<input type="hidden" name="JustSelectedAvendor" value="Yes" />';
        echo '<div>
          <a href="' . $RootPath . '/ShOrderReportExcel.php?customer_code=' .$_POST['customer_code'] .
            '&customer_name='.$_POST['customer_name'] .'&moju_num=' .$_POST['moju_num'] . 
            '&moju_name='.$_POST['moju_name'] .'&sh_order_num=' .$_POST['sh_order_num'] .' ">' .'资料导出Excel表' . '</a>
         </div>';
    }

    if (isset($ListPageMax) AND $ListPageMax > 1) {
        echo '<br /><div class="centre">&nbsp;&nbsp;' . _('第') . '' . $_POST['PageOffset'] . ' ' . _('页，共') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ': ';
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
                <input type="submit" name="Go2" value="' . _('Go') . '" />
                <input type="submit" name="Previous" value="' . _('Previous') . '" />
                <input type="submit" name="Next" value="' . _('Next') . '" />';
        echo '</div>';
    }
}
echo '</div></form>';

include('includes/footer.inc');
