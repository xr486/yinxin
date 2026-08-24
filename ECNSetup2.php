<?php 
ob_start();
	include('includes/session.inc');
	$Title = _('确认/更改/变更/签证');
	$ViewTopic= '确认/更改/变更/签证';
	$BookMark = '确认/更改/变更/签证';
	include('includes/header.inc');
	include('includes/SQL_CommonFunctions.inc');
 require_once 'upload.class.php';
 if (isset($_GET['OrderNum'])) {
$_SESSION['OrderNum' . $identifier]=$_GET['OrderNum'];
 }


?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>确认/更改/变更/签证</title>
<style type="text/css">
#myleibie span{display:inline-block;height:20px;width:100px;padding:5px 8px ;margin:5px;border:1px solid #ccc;cursor:pointer;verticle-align:middle}
.on{display:inline-block;width:200px;padding:5px 8px;margin:3px;border:1px solid #ccc;cursor:pointer;background-color:#6FD4F8;} 
</style>

<script type="text/javascript">
//选择标签
$(function(){
	//定义一个从来存储标签的数组
	var arrStr = new Array();
	//每次点击标题在数字中增加或者删除一个标签
	$('#myleibie span').click(function(){
		var t = $('#leibie').val();			//获取text中已经存的标签内容
		var s = $(this).html();		   //获取点击的当前标签内容
		//如果当前点击的标签存在于标签数组数组中就删除 不存在就新增（目的点击增加删除切换）
		var num = $.inArray(s,arrStr);	/* $.inArray(要查找的字符串,数组集) jquery数组查找函数 */
		//如果$.inArray()返回负数表示数组中不存在，否则返回字符串存在于数组中的下标 0开始
		if(num>'-1'){ 
			//点击去色
			$(this).removeClass("on");
			//剔除标签
			arrStr.splice(num,1);
		}else{
			//点击上色
			$(this).addClass("on");
			//数组中追加标签
			arrStr.push(s);
		}
 
		//每次点击情况标签显示框
		$('#leibie').val(''); 
		//遍历出来标签数组  jquery遍历数组 $.each(数组集,function(k,v){……});
		$.each(arrStr,function(k,v){
			//获取原来的标签数据
			var oldData = $('#leibie').val() + v;
			//为标签添加分割符号
			if(k != arrStr.length-1) oldData += ',';
			//将最终数据遍历到显示框中
 			$('#leibie').val(oldData);
		});
 
		
	});
});
</script>
</head>
<?php
 $sql3 = "SELECT a.*, b.item_name, b.item_desc,b.gongyi
FROM bom_headers_all a , sf_item_no b
        where a.assembly_item_no = b.item_no and  assembly_item_no = '" . $_SESSION['OrderNum' . $identifier] . "'";
 
$result3 = DB_query($sql3, $db);
 $myrow3 = DB_fetch_array($result3);

	echo '<table cellpadding="3" class="selection">';
 
echo '<div class="text-nav">
		<div class="text-nav-1"><div>' . _('母件料号') . ':</div>
		<input type="text" readonly="readonly" name="assembly_item_no"  value="' . $myrow3['assembly_item_no']  . '" /> </div>
		<div class="text-nav-1"><div>料号名称</div>' . '<input type="text" readonly="readonly" value="' . $myrow3['item_name'] . '" /></div>
		<div class="text-nav-1"><div>规格型号</div>' . '<input type="text" readonly="readonly" value="' . $myrow3['item_desc'] . '" /></div>
		<div class="text-nav-1"><div>工艺</div>' . '<input type="text" readonly="readonly" value="' . $myrow3['gongyi'] . '" /></div>
		<!--测试-->
<body>
		';
 
 
echo '</table> ';

 $sql2 = "SELECT a.*,(select count(*) from bom_headers_all_file b where b.source_id=a.id and source_type='确认/更改/变更/签证')  file_count
FROM bom_huishang_all a
        where guanli_type='确认/更改/变更/签证' and order_number = '" . $_SESSION['OrderNum' . $identifier] . "'";

$result2 = DB_query($sql2, $db);

	echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/customer.png" title="' . _('确认/更改/变更/签证明细') .
		'" alt="" />' . ' ' . _('确认/更改/变更/签证明细') . '
	</p>';
	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '"><input type="hidden" name = "identifier" value ="' . $identifier . '">';
	echo '<div>';



	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<table class="selection" align="center" >';
	$tableheader = '<tr> 
						<th width =80 >' . '变更单号' . '</th>
						<th width =120>' . '变更名称' . '</th>
                        <th width =120>' . '变更类别' . '</th>
						<th width =250 >' . '变更内容' . '</th>
						<th width =80 >' . '版本' . '</th> 
						<th width =150 >' . '建立日期' . '</th>
						<th width =80 >' . '建立人员' . '</th> 
				</tr>';

	echo $tableheader;
	$RowCounter = 1;
	$k = 0; 
	while ($myrow = DB_fetch_array($result2)) {
		if ($k == 1) {
			echo '<tr class="EvenTableRows">';
			$k = 0;
		} else {
			echo '<tr class="EvenTableRows">';
			$k++;
		}

		echo ' <td>' . $myrow['change_name'] . '</td>
		<td>' . $myrow['change_type'] . '</td>
		<td>' . $myrow['leibie'] . '</td>
		              <td>' . $myrow['change_text'] . '</td>
					  <td>' . $myrow['version'] . '</td>
					  
                      <td>' . date('Y-m-d H:i:s', $myrow['creation_date']) . '</td>
					  <td>' . $myrow['created_by'] . '</td>
					
					



        </tr>';
		$RowCounter++;
		if ($RowCounter == 500) {
			$RowCounter = 1;
			echo $tableheader;
		}
	}
	echo '</table> ';


	echo '</div>
          </form>';

 
  echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . '新增确认/更改/变更/签证' .
 '" alt="" />' . ' ' .'新增确认/更改/变更/签证' . '
	</p>';
	

		$uploadflag = 1;

	if (isset($_POST['Save'])) {
		if($_POST['change_text'] == ''){
			prnMsg($value . '未填写变更内容，请填写变更内容！', error);
		}
	    if($_POST['change_text'] != ''){
			$date1 = date('Ymd');
		$date=substr($date1,2,6) ;
		if (!empty($_FILES["Pic"]["tmp_name"])) {
			if ((($_FILES["Pic"]["type"] == "image/gif")
				|| ($_FILES["Pic"]["type"] == "image/jpeg")
				|| ($_FILES["Pic"]["type"] == "image/pjpeg")
				|| ($_FILES["Pic"]["type"] == "image/png")
				|| ($_FILES["Pic"]["type"] == "image/bmp"))
				&& ($_FILES["Pic"]["size"] < 20*1024*1024)){
				
                  if ($_FILES["Pic"]["error"] > 0){
				    $msg = "错误: " . $_FILES["Pic"]["error"];
				    prnMsg( $msg, 'error');
				    $uploadflag = 2;
				  }
              echo $_FILES["Pic"]["type"];
              echo $_FILES["Pic"]["tmp_name"]; 
            echo $_FILES["Pic"]["type"];
			move_uploaded_file($_FILES["Pic"]["tmp_name"],"itempic/" . $_POST['ItemNo'] .".jpg");
            $_POST['PicPath'] = "itempic/" . $_POST['ItemNo'] .".jpg";
			} else
			{
			  $msg = "系统只支持gif,jpeg,pjpeg，png图片";
			  prnMsg( $msg, 'error');
			  $uploadflag = 2;
			}
			
		}else{
			$sql = "select pic_path from sf_item_no where item_id ='".$ItemID."' ";
			$result = DB_query($sql,$db);
			while ($v = DB_fetch_array($result)) {
				$_POST['PicPath'] = $v['pic_path'];
			}
		}
		$sql_num = "select lpad((max( substr(change_name, -3,3 ) ) +1 ) , 3, 0) po_num   from bom_huishang_all 
		where  substr(change_name,3,6) = '" . $date . "'  ";
		 
		$result_num = DB_query($sql_num, $db);
		$rownum = DB_num_rows($result_num);
		while ($v = DB_fetch_array($result_num)) {
			if ($v['po_num'] == null) {
				$change_name = 'CH'.$date . '001';
			} else {
				$change_name =  'CH'.$date . $v['po_num'];
			}
		}

				$time = time();
				$sql = "insert into bom_huishang_all (guanli_type,leibie,change_text,change_name,change_type,version,order_number,last_update_date,
				last_updated_by,creation_date,created_by) values ('确认/更改/变更/签证','".$_POST['leibie']."',
				'".$_POST['change_text']."','".$change_name."','".$_POST['change_type']."','".$_POST['version']."','".$_POST['OrderNum']."',
				'".$time."','".$_SESSION['UserID']."','".$time."','".$_SESSION['UserID']."')";
				$result = DB_query($sql,$db);
				 //echo $sql; 
				prnMsg( _('保存成功！'), 'success');
				$_POST['change_text']='';
				$_POST['change_type']=''; 
				echo '<br />';
				DB_Txn_Commit($db);
	

				header('Location: ECNSetup2.php?New=Yes&OrderNum=' . $_POST['OrderNum'] );
	

		}
			



        
	}


?>

<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST" enctype="multipart/form-data">
<div>
	<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
	<input type="hidden" name="OrderNum" value = "<?php echo $_SESSION['OrderNum' . $identifier]; ?>">
<br>

<table class="selection">
 <tr> 
   <td>  类别  </td>
    <td><input type="text" id="leibie" name="leibie" maxlength="1000" size="200" name="leibie"  value="<?=$_POST['leibie']?>" /></td>
   </tr> 
</table>

<table class="selection">
 
    <div class="text-nav">
   <div id="myleibie" style=" clear: both;width:100%;padding:30px 0 ;">
 <!--<input type="text" id="leibie" name="leibie" style="width:500px" />--> 
 <div style="float:left"><span>设计变更</span></div>
 <div style="float:left"><span>客户设变</span> </div>
 <div style="float:left"><span>验证</span></div>
 <div style="float:left"><span>工具</span></div>
 <div style="float:left"><span>DRMO备品</span> </div>
 <div style="float:left"><span>采购交期替换</span></div>
 <div style="float:left"><span>漏发</span></div>
 <div style="float:left"><span>调机损坏</span></div>
 <div style="float:left"><span>耗材</span></div>
 <div style="float:left"><span>料件丢失</span></div>
 <div style="float:left"><span>办公用品</span></div>
 <div style="float:left"><span>生活用品</span></div>

 </div>
</table>

<table class="selection">
	
  

 <div class="text-nav">
 <div class="text-nav-1 required"><div>变更名称</div>
  <input type="text"   maxlength="20" size="20" name="change_type"  value="<?=$_POST['change_type']?>" /> </div>
 

 
	<div class="text-nav-2 required"><div>变更内容：</div>
  <input type="text"   maxlength="200" size="40" name="change_text"  value="<?=$_POST['change_text']?>" /> </div>

  <div class="text-nav-1 required"><div>版本：</div>
  <input type="text"   maxlength="200" size="40" name="version"  value="<?=$_POST['version']?>" /> </div>
 

				
	
</table>

<div class="centre">
	<input type="submit" name="Save" value="保存" >
</div>
</form>
<?php
  include('includes/footer.inc');
?>