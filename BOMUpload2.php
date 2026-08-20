<?php
putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");
ob_start();
include('includes/session.inc');
$Title = _('BOM整批上传确认');

$ViewTopic= 'BOM整批上传确认';
$BookMark = 'BOM整批上传确认';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

unset($result);

	
if (isset($_POST['Save'])) {
	$errorflag = 0;
 
	foreach ($_POST as $key => $value) {
		if ($value != '') {
			if (substr($key, 0,7)=='stockid') {
				 
				$i = substr($key, 7);
				if ($value != '') {
					if ($_POST['uom'.$i]=='') {
					$errorflag = 1;
					prnMsg($value.'未填写单位，请填写单位！',error);
					}
					// if ($_POST['remark'.$i]!='') {
					// $errorflag = 1;
					// prnMsg($value.'有错误,请排错后再处理！',error);
					// }

					
					if ($_POST['quantity'.$i] < 0 ) {
						$errorflag = 1;
						prnMsg($value.'数量不可以小于0，请填写数量！',error);
					}
					 

					
					if ($_POST['item_no']==$_POST['stockid'.$i]) {
						$errorflag = 1;
						prnMsg($value.'子料号与母料相同,请修改！',error);
					}

				}
			}
		}
	}
	$item_num=0;
	if ($errorflag == 0) {
	  DB_Txn_Begin($db);
		$time = time();

		$time=time();
			$sql21 = "select * from bom_lines_all where bom_header_id in (select bom_header_id from bom_headers_all 
			where  assembly_item_no = '".$_POST['item_no']."'
			and version = '".$_POST['version']."' ) "; 
			$result21 = DB_query($sql21,$db);
			if(DB_num_rows($result21) <> 0){
                 prnMsg($value.'BOM'.$_POST['item_no'].'版本'.$_POST['version'].'，的BOM已存在请确认！',error);
			} else {
			 $sql23 = " select bom_header_id from bom_headers_all 
			where  assembly_item_no = '".$_POST['item_no']."'
			and version = '".$_POST['version']."'"; 
			$result23 = DB_query($sql23,$db);
			if(DB_num_rows($result23) == 0){
			 $sql7 = "insert into bom_headers_all(assembly_item_no,version,creation_date,created_by,
            last_update_date,last_updated_by)
            values('".$_POST['item_no']."','".$_POST['version']."',
                    '".$time."',
                    '".$_SESSION['UserID']."',
                    '".$time."',
                    '".$_SESSION['UserID']."')";
            $result7 = DB_query($sql7,$db);
			}
  
			$sql22 = "select bom_header_id from bom_headers_all 
			where  assembly_item_no = '".$_POST['item_no']."'
			and version = '".$_POST['version']."'"; 
			$result22 = DB_query($sql22,$db);
			$myrow22 = DB_fetch_array($result22);
             $bom_header_id=$myrow22['bom_header_id'];
			 
			}


		foreach ($_POST as $key => $value) {
			if ($value != '') {
				if (substr($key, 0,7)=='stockid') {
					$i = substr($key, 7);
					

					 if ($_POST['sunhao_rate'.$i]=='') {
						$_POST['sunhao_rate'.$i] = 0;
					}
					if ($_POST['operation_seq_num'.$i]=='') {
						$_POST['operation_seq_num'.$i] = 1;
					}

				if ($_POST['tidai_type'.$i]!='替代') {
					$component_item=$_POST['stockid'.$i];
				    $operation_seq_num=$_POST['operation_seq_num'.$i];
				} else {
				 $sql99 = "select component_sequence_id from  bom_lines_all 
				where component_item='".$component_item."'
				and  assembly_item_no='".$_POST['item_no']."' 
				and  operation_seq_num='".$operation_seq_num."'  ";
				 $result99 = DB_query($sql99,$db); 
				 while ($v = DB_fetch_array($result99)) {
				   $component_sequence_id=  $v['component_sequence_id'];
			   }
				
				}
 	 	
				$sql9 = "select * from  sf_item_no where item_no='".$_POST['stockid'.$i]."'";
		        $result9 = DB_query($sql9,$db); 
				if  (DB_num_rows($result9) == 0) {

          
		 $sql = "INSERT INTO sf_item_no (
	so_flag, 		
	item_no,
	item_name,
	units,
    item_category1,  
	item_type,
	min_order,
	safe_qty,sub_code,
	disable_flag,
	pic_path,
	creation_date,
	created_by,
	last_update_date,
	last_updated_by, 
        lead_time,manufacture_time,
        yanse 
)
VALUES
	(
		'Y',
		'" .   rtrim($_POST['stockid'.$i]). "',
		'" . rtrim($_POST['item_name'.$i]) . "',
		'" . $_POST['uom'.$i] . "',	
        '', 
		'M', 
		'0',
		'0','" . $_POST['sub_code'] . "',
		'Y',
		'" . $_POST['PicPath'] . "',
		'" . $time . "',
		'" . $_SESSION['UserID'] . "',
		'" . $time . "',
		'" . $_SESSION['UserID'] . "', 
		'0',
		'0',
		'" . $_POST['yanse'] . "' 
	)";
		
				 $result = DB_query($sql, $db);
				}
       
                    if ($_POST['tidai_type'.$i]!='替代') {
						$item_num=$item_num+1;
					$sql2 = "insert into bom_lines_all(bom_header_id,assembly_item_no,item_num,weizhi,operation_seq_num,component_item,component_quantity,sunhao_rate,component_remarks,effectivity_date,creation_date,created_by,last_update_date,last_updated_by)
					values('".$bom_header_id."','".$_POST['item_no']."','".$item_num."','".$_POST['weizhi'.$i]."','".$_POST['operation_seq_num'.$i]."','".$_POST['stockid'.$i]."','".$_POST['quantity'.$i]."','".$_POST['sunhao_rate'.$i]."','".$_POST['component_remarks'.$i]."','".$time."','".$time."','".$_SESSION['UserID']."','".$time."','".$_SESSION['UserID']."') ";
					$result2 = DB_query($sql2,$db);
			          } else {

						   $sql4="insert into bom_substitutes_all(component_sequence_id,item_num,substitute_item,
                        substitute_remarks,substitute_item_quantity,creation_date,created_by,
                        last_update_date,last_updated_by)
                        values  ('".$component_sequence_id."','".$item_num."',
                              '".$_POST['stockid'.$i]."', 
                              '".$_POST['component_remarks'.$i] ."',
                              '".$_POST['quantity'.$i]."',
                              '".$time."',
                              '".$_SESSION['UserID']."',
                              '".$time."',
                              '".$_SESSION['UserID']."')";
                $result4 = DB_query($sql4,$db);
					  
					  }


				}
			}
		}
 
 

		DB_Txn_Commit($db);
		prnMsg('BOM'.$_POST['item_no'].'建立成功！',success);
		 
		 echo "<script>location.href='BOMUpload.php';</script>";
		 
		  
	}
}


 ?>
 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link rel="shortcut icon" href="/sherp/favicon.ico"/>
<link rel="icon" href="/sherp/favicon.ico"/>
<meta http-equiv="Content-Type" content="application/html; charset=utf-8"/>
<link href="/css/xenos/default.css" rel="stylesheet" type="text/css"/>
<style>
/* ===== BOM 上传确认页 · xenos 卡片风格覆盖（仅样式，不改业务逻辑） ===== */
body{background:#f2f4f8;overflow-x:auto}
.page_title_text{color:#0d47a1;font-size:18px;font-weight:bold;padding:14px 0;border-bottom:2px solid #e3ecf7;margin-bottom:16px}
#BodyWrapDiv{max-width:1600px;margin:0 auto;background:#fff;border:1px solid #e0e8f0;border-radius:10px;padding:20px 24px;box-shadow:0 2px 8px rgba(30,64,120,.06);overflow-x:auto}
/* 母件信息区：卡片化 */
.text-nav{display:flex;flex-wrap:wrap;gap:14px;background:#fafcff;border:1px solid #dbe5f2;border-radius:8px;padding:16px 18px;margin-bottom:14px}
.text-nav-1,.text-nav-2{display:flex;flex-direction:row;align-items:center;gap:8px;flex:1;min-width:220px}
.text-nav-1>div,.text-nav-2>div{font-size:13px;color:#37474f;font-weight:600;white-space:nowrap;flex:0 0 auto}
.text-nav-1.required>div:after,.text-nav-2.required>div:after{content:" *";color:#e53935}
.text-nav input[type=text]{border:1px solid #ccd7e4;border-radius:5px;padding:7px 10px;font-size:13px;background:#fff;transition:border-color .2s,box-shadow .2s;flex:1;min-width:0;width:auto}
.text-nav input[type=text]:focus{border-color:#1976D2;box-shadow:0 0 0 3px rgba(25,118,210,.12);outline:none}
.text-nav .select_img{cursor:pointer;flex:0 0 auto;margin-left:4px;vertical-align:middle}
/* 明细表格：卡片圆角表头 */
.text-nav-table{background:#fff;border:1px solid #e0e8f0;border-radius:8px;padding:4px;overflow-x:auto;overflow-y:hidden}
.text-nav-table table.selection{width:max-content;min-width:100%;white-space:nowrap;border-collapse:separate;border-spacing:0}
.text-nav-table table.selection th{background:linear-gradient(180deg,#f2f7fd,#e8f0fb);color:#0d47a1;font-size:13px;padding:9px 6px;border-bottom:2px solid #cfe0f3;white-space:nowrap}
.text-nav-table table.selection td{padding:5px 4px;border-bottom:1px solid #eef2f7;font-size:12px}
.text-nav-table table.selection tr:hover td{background:#f5f9ff}
.text-nav-table table.selection input[type=text]{border:1px solid #d8e0e9;border-radius:4px;padding:4px 5px;font-size:12px;background:#fff}
.text-nav-table table.selection input[type=text]:focus{border-color:#1976D2;outline:none}
.text-nav-table table.selection td:first-child input[type=text]{width:36px;text-align:center;color:#546e7a}
/* 说明列：非空即"料号名称不同"等差异提示 → 红色 */
.text-nav-table table.selection input[name^="remark"]{border-color:#ffcdd2;color:#c62828;background:#fff5f5;font-weight:600}
/* 按钮：彩色圆角 */
input[type=submit]{border-radius:6px;cursor:pointer;font-size:14px;transition:opacity .15s,transform .1s}
input[type=submit]:hover{opacity:.88}
input[type=submit]:active{transform:translateY(1px)}
input[type=submit][name="Save"]{background:#27ae60;color:#fff;border:none;padding:10px 46px;font-size:15px;font-weight:600}
input[type=submit][name="return"],input[type=submit][value="关闭当前页面"]{background:#fff;color:#455a64;border:1px solid #b9c4d0;padding:8px 24px}
/* 删除行链接 */
.text-nav-table table.selection a{color:#e53935;font-weight:600;text-decoration:none}
.text-nav-table table.selection a:hover{text-decoration:underline}
/* 成功/错误消息更醒目 */
.msg{display:block;margin:10px 0}
</style>
<script type="text/javascript" src ="/shanghai/javascripts/miscfunctions.js"></script>
<script type="text/javascript" src ="/shanghai/javascripts/wdatepicker.js"></script>
<script type="text/javascript">var basepath='/shanghai/statics/base/images';</script>
<script type="text/javascript" src="/shanghai/statics/base/js/metvar.js"></script>
<script type="text/javascript" src="/shanghai/statics/base/js/jQuery1.7.2.js"></script>
<script type="text/javascript" src="/shanghai/statics/base/js/uploadify/jquery.uploadify.v2.1.4.min.js"></script>
<script type="text/javascript" src="/shanghai/statics/base/js/iframes.js"></script>
<script type="text/javascript" src="/shanghai/statics/base/js/cookie.js"></script>
<script type="text/javascript" src="/shanghai/statics/base/js/jquery.livequery.js"></script>
<script src="/shanghai/javascript/jquery-1.7.2.min.js"></script>
<script src="/JXC/javascript/lhgdialog.min.js?self=true&skin=chrome"></script>
<!-- Include all compiled plugins (below), or include individual files as needed -->
<script src="/javascript/bootstrap.min.js"></script>
<script type="text/javascript">
/*ajax执行*/
var lang = 'cn';
var metimgurl='/shanghai/statics/base/images/';
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

function addsave() 
{

	var v = $('#idcount').val();
    $("#purchase_table_"+v).css("display","");
	var c = parseInt(v) + 1;
	$('#idcount').val(c);     
}

 </script>
</head>
<body>
 <?php 
 if(isset($OrderNum)){
    }else{
 ?>
<div id="CanvasDiv">
	<div id="BodyDiv">
		<div id="BodyWrapDiv">
		<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="BOM建立" alt="BOM建立">BOM建立</p>
			<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST"><input type="hidden" name="time" value="<?=$time?>">
				<div>
				<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
				<table class="selection">				 
		<div class="text-nav">
		<div class="text-nav-1 required">
		<div>半/成品物料:</div>
			<input type="text" required="required" name="item_no" id="text_slect_item_no" value="<?=$_POST['item_no']?>" size="60" maxlength="100" onblur="sel()"/>
					   <image class="select_img" src="img/search.png" id="btn_slect_item_no" style="cursor:pointer;vertical-align:middle" onclick="openQiPick()"/> </td>
		</div>

		<div class="text-nav-1 required">
		<div>版本:</div>
		<input  type="text" name="version" id="version" value="<?=$_POST['version']?>" size="10" maxlength="10"/></div>

      <div class="text-nav-2 required">
		<div>物料名称:</div>
		<input readonly="readonly" type="text" name="item_name" id="text_slect_item_name" value="<?=$_POST['item_name']?>" size="70" maxlength="250"/></div>
		<div class="text-nav-2">
		<div>规格型号:</div>
		<input readonly="readonly" type="text" name="item_desc" id="text_slect_item_desc" value="<?=$_POST['item_desc']?>" size="70" maxlength="250"/> </div>
	     </div>
		
		 
	</table>
	<input type="hidden" name="PageOffset" value="1"/><br/>

	<?php

		//  echo ' <table><tr> <td><a href="' . $RootPath . '/BOMUpload3.php" target="_blank">打印差异</td>
		//  </tr></table>';          
		if (1==1) {
	?>
		<div class="text-nav-table">
		<table id="purchase_table" cellpadding="2" class="selection" style="border:1px solid #e0e8f0">
			<tr id="list-top" style="background:#eef4fb">
				
				<th>序号</th>
				<th  width="220"><font color="red">子物料代码</font></th>
               
				<th  width="130">上传物料名称</th>
				<th  width="130">系统物料名称</th>
				<th  width="130">规格型号</th>
				<th  width="90"><font color="red">数量</font></th>
				<th  width="30" >单位</th>
				<th  width="30" >自损率</th>
				
				<th  width="100">备注</th>
				<th  width="100">说明</th>
				<th  width="50" align="center">操作</th>
			</tr>
<?php
$sql=" select a.*,replace(replace(replace(item_name,':',','),'：',','),'；',';') item_name from bom_upload a  where  a.created_by = '" . $_SESSION['UserID'] . "' ";
//echo $sql;
$result = DB_query($sql,$db); 
$i=1;
if  (DB_num_rows($result) == 0) {
	unset($result);
	prnMsg('请确认是否有上传最新文件',error);
}else{
	while  ($myrow = DB_fetch_array($result)) {
		$cnt=0; 
		$remark='';
		$item_name ='';

		if ( $myrow['stockid']) {
			$sql7=" select  replace(replace(replace(item_name,':',','),'：',','),'；',';')  item_name   from sf_item_no a where item_no = '" . $myrow['stockid']. "'  "; 
			$result7 = DB_query($sql7,$db); 
			while  ($myrow7 = DB_fetch_array($result7)) {
				$item_name =$myrow7['item_name'] ;
			}
			if ($item_name<>$myrow['item_name']) {
			$remark='料号名称不同';
			}
		
	}
	
?>


	<tr ><input type="hidden" name="s_num" value="<?=$result_array[1]?>" size="15" maxlength="45"/>
		 
		<td><input type="text" name="item_num<?=$i?>" id="text_slect_item_num<?=$i?>"  value="<?=$myrow['item_num'] ?>" size="2" maxlength="34"/></td>
		<td><input type="text" name="stockid<?=$i?>" id="text_slect_buliao<?=$i?>"  value="<?=$myrow['stockid'] ?>" size="22" maxlength="34"/></td>
		 
		<td><input  type="text" name="item_name<?=$i?>" id="text_slect_item_name<?=$i?>" value="<?=$myrow['item_name']  ?>" size="30" maxlength="160"/></td>
		<td><input  type="text" name="xitong_item_name<?=$i?>" id="text_slect_item_name<?=$i?>" value="<?=$item_name  ?>" size="30" maxlength="160"/></td>
		<td><input  type="text" name="item_desc<?=$i?>" id="text_slect_item_desc<?=$i?>" value="<?=$myrow['item_desc']  ?>" size="30" maxlength="160"/></td>
		<td><input type="text" class="number" name="quantity<?=$i?>" value="<?=$myrow['quantity']?>" size="6" maxlength="10"/> </td>
		<td><input  type="text" name="uom<?=$i?>"  value="<?=$myrow['uom'] ?>" size="3" maxlength="4"/></td>
		<td><input  type="text" name="sunhao_rate<?=$i?>"  value="<?=$myrow['sunhao_rate'] ?>" size="3" maxlength="4"/></td>
		
		<td><input type="text"  name="component_remarks<?=$i?>" value="<?=$myrow['component_remarks']?>" size="10" maxlength="100"/></td>
		<td><input type="text"  name="remark<?=$i?>" value="<?=$remark ?>" size="10" maxlength="100"/></td>
		<td>  <a onclick="delettr($(this));" style="padding:0px 5px;" href="javascript:;">删除</a></td>
	</tr>
		<?php
              $i=$i+1;
            }
					}
          ?>
		 
		
		</table>	
		</div>			
			<div class="centre">
	            <input type="submit" name="Save" value="保存" style="background:#27ae60;color:#fff;border:none;padding:7px 34px;border-radius:3px;cursor:pointer;font-size:14px" /> &nbsp;
			</div>
	<?php
		}
	?>
            <input type="hidden" name="idcount" id='idcount' value="1"/>
            <input type="hidden" name="JustSelectedACustomer" value="Yes"/>
		</div>
	</form>
	</div>
	</div>
	
	<div id="FooterDiv">
		<div id="FooterWrapDiv">
		 	 
		</div>
	</div>
</div>
<?php
}
?>
<script type="text/javascript">
$(document).ready(function(){
try {
var aaa,uuu;
$('.tdl1').each(function(){
	var dataId = $(this).attr('data-id');
	$('#btn_slect_tidai'+dataId).dialog({
		title:'选择替代料',
		width: '1200px',
		height: 600,
		content:'url:bomtidai.php?fwValue=<?=$_POST['item_no']?>',
		init:function(){
			aaa=$('#btn_slect_tidai'+dataId).parent().parent().children('td').eq(0).find('input').val();
			uuu=$('#btn_slect_tidai'+dataId).parent().parent().children('td').eq(2).find('input').val();
			this.content.document.getElementById('cat').value = aaa;
			this.content.document.getElementById('gongxu').value = uuu;
			this.content.document.getElementById('fwValue').value = '<?=$i?>';
		}
	});
});

$('.tdl2').each(function(){
	var dataId = $(this).attr('date-id');
	$('#btn_slect_weizhi'+dataId).dialog({
		title:'选择零件位置',
		width: '1200px',
		height: 600,
		content:'url:bomweizhi.php?fwValue=<?=$_POST['item_no']?>',
		init:function(){
			aaa=$('#btn_slect_weizhi'+dataId).parent().parent().children('td').eq(0).find('input').val();
			uuu=$('#btn_slect_weizhi'+dataId).parent().parent().children('td').eq(2).find('input').val();
			this.content.document.getElementById('cat').value = aaa;
			this.content.document.getElementById('gongxu').value = uuu;
			this.content.document.getElementById('fwValue').value = '<?=$i?>';
		}
	});
});

$('.divToilet table tr td a').click(function(){
	$(this).parent('td').toggleClass('highlight');
	if(!($(this).parent('td').hasClass('highlight'))) {
		$(this).next().val('0');
	}else {
		$(this).next().val('1');
	}
});

<?php for($i=1;$i<=50;$i++){?>
	$('#btn_slect_buliao<?=$i?>').dialog({
		title:'选择子料号',
		width: '900px',
		height: 470,
		content:'url:SearchBOMItem.php?fwValue=<?=$i?>&cat=buliao',
		init:function(){
			this.content.document.getElementById('cat').value = 'buliao';
			this.content.document.getElementById('fwValue').value = '<?=$i?>';
		}
	});
<?php }?>

$('#btn_slect_item_no').on('click', function(){
	// 复用 BOMSetup 的"快捷添加物料"弹窗（带分类+多列过滤），限定只显示成品/半成品（F/B）→ &restrict=fb
	var url = 'BOMSetup.php?op=quick_item&target=text_slect_item_no&nameTarget=text_slect_item_name&restrict=fb&_r=' + Date.now();
	openQiDialog(url);
});

} catch(e){ /* lhgdialog 未加载时忽略弹窗初始化错误，不影响其他功能 */ }
});

// 简易模态框：选择成/半成品料号（不依赖 lhgdialog——此环境 $.dialog 不可用）
// 放在 ready/try 之外，确保函数始终定义；image 用原生 onclick 调用（不依赖 jQuery ready 绑定）
function openQiPick(){
	var url = 'BOMSetup.php?op=quick_item&target=text_slect_item_no&nameTarget=text_slect_item_name&restrict=fb&_r=' + Date.now();
	openQiDialog(url);
}
function openQiDialog(url){
	if (document.getElementById('qiMask')) return; // 防止重复打开
	var mask = $('<div id="qiMask" style="position:fixed;left:0;top:0;right:0;bottom:0;background:rgba(0,0,0,.45);z-index:99990"></div>');
	var box = $('<div id="qiBox" style="position:fixed;left:50%;top:50%;transform:translate(-50%,-50%);width:1400px;max-width:96vw;height:820px;max-height:94vh;background:#fff;border-radius:10px;box-shadow:0 10px 40px rgba(0,0,0,.35);z-index:99991;display:flex;flex-direction:column;overflow:hidden"></div>');
	var head = $('<div style="padding:10px 16px;background:#f2f7fd;border-bottom:1px solid #dbe5f2;font-weight:bold;color:#0d47a1;display:flex;justify-content:space-between;align-items:center;flex:0 0 auto">'
		+ '<span>选择成/半成品料号</span>'
		+ '<button type="button" style="border:1px solid #b9c4d0;background:#fff;border-radius:4px;padding:3px 16px;cursor:pointer">关闭 ✕</button></div>');
	var frame = $('<iframe id="qiFrame" src="' + url + '" style="flex:1;border:none;width:100%;background:#fff"></iframe>');
	head.find('button').on('click', closeQiDialog);
	box.append(head, frame);
	$('body').append(mask, box);
}
function closeQiDialog(){
	$('#qiMask').remove();
	$('#qiBox').remove();
}

$(function(){
	$('#btn_slect_item_no').on('click', function(){
		// 复用 BOMSetup 的"快捷添加物料"弹窗（带分类+多列过滤），限定只显示成品/半成品（F/B）→ &restrict=fb
		var url = 'BOMSetup.php?op=quick_item&target=text_slect_item_no&nameTarget=text_slect_item_name&restrict=fb&_r=' + Date.now();
		openQiDialog(url);
	});
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

function checkall(thisform){
	for(var i=0;i<thisform.elements.length;i++){
		if(thisform.elements[i].type=="checkbox"&&thisform.elements[i].checked==false&&thisform.elements[i].name!="selectall"){
			thisform.elements[i].checked=true;
		}
		else if(thisform.elements[i].type=="checkbox"&&thisform.elements[i].checked==true&&thisform.elements[i].name!="selectall"){
			thisform.elements[i].checked=false;
		}
	} 
}


</script>
</body>

</html>
<?
 
include('includes/footer.inc');
?>

