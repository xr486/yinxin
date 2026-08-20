<?php 
header("Content-Type:text/html;charset=utf-8");
putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");
set_time_limit(0); 
ob_start();
 date_default_timezone_set ('Asia/Shanghai');
ob_start();
include('includes/session.inc');
$Title = _('BOM整批上传');
$ViewTopic = 'BOM整批上传';
$BookMark = 'BOM整批上传';
include('includes/header.inc');
//include('includes/SQL_CommonFunctions.inc');
include("excel/excel.php"); 

echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . _('BOM整批上传') .
 '" alt="" />' . ' ' . $Title . '</p>';



if (isset($_FILES['userfile']) and $_FILES['userfile']['tmp_name']) { //start file processing

	// ===== 文件上传友好校验 =====
	$upErr = isset($_FILES['userfile']['error']) ? $_FILES['userfile']['error'] : 0;
	if ($upErr != 0) {
		$upErrMsg = array(
			1 => '文件大小超过服务器限制（php.ini upload_max_filesize）！',
			2 => '文件大小超过表单限制（MAX_FILE_SIZE）！',
			3 => '文件只上传了一部分，请重新上传！',
			4 => '未选择文件！',
			6 => '服务器临时目录不可用，请联系管理员！',
			7 => '文件写入磁盘失败，请重试！',
			8 => '上传被服务器扩展拦截，请联系管理员！'
		);
		$errMsg = isset($upErrMsg[$upErr]) ? $upErrMsg[$upErr] : '上传失败（错误码 ' . $upErr . '），请重试！';
		echo '<div style="border:1px solid #f5c6cb;background:#fdf0f0;border-radius:10px;padding:24px;text-align:center;margin:20px auto;max-width:640px;box-shadow:0 2px 8px rgba(220,53,69,.08)">';
		echo '<div style="font-size:17px;color:#c0392b;font-weight:bold;margin-bottom:8px">⚠️ 上传失败</div>';
		echo '<div style="color:#555;margin-bottom:16px">' . htmlspecialchars($errMsg) . '</div>';
		echo '<a href="' . $RootPath . '/BOMUpload.php" style="background:#1976D2;color:#fff;padding:9px 36px;border-radius:6px;text-decoration:none;font-size:14px;font-weight:600;display:inline-block">返回重新上传</a>';
		echo '</div>';
		include('includes/footer.inc');
		exit;
	}
	// 文件类型检查：仅允许 .xls/.xlsx
	$upName = isset($_FILES['userfile']['name']) ? $_FILES['userfile']['name'] : '';
	$upExt = strtolower(pathinfo($upName, PATHINFO_EXTENSION));
	if (!in_array($upExt, array('xls', 'xlsx'))) {
		echo '<div style="border:1px solid #f5c6cb;background:#fdf0f0;border-radius:10px;padding:24px;text-align:center;margin:20px auto;max-width:640px;box-shadow:0 2px 8px rgba(220,53,69,.08)">';
		echo '<div style="font-size:17px;color:#c0392b;font-weight:bold;margin-bottom:8px">⚠️ 文件格式不支持</div>';
		echo '<div style="color:#555;margin-bottom:16px">请上传 <b>.xls / .xlsx</b> 格式的 Excel 文件（当前文件：' . htmlspecialchars($upName ?: '未知') . '）。<br>请先下载标准格式模板填写后上传。</div>';
		echo '<a href="' . $RootPath . '/upload/BomUploadSample.xls" target="_blank" style="background:#e3f2fd;color:#0d47a1;padding:7px 18px;border-radius:6px;text-decoration:none;font-size:13px;border:1px solid #90caf9;display:inline-block;margin-right:10px">下载标准格式模板</a>';
		echo '<a href="' . $RootPath . '/BOMUpload.php" style="background:#1976D2;color:#fff;padding:9px 36px;border-radius:6px;text-decoration:none;font-size:14px;font-weight:600;display:inline-block">返回重新上传</a>';
		echo '</div>';
		include('includes/footer.inc');
		exit;
	}
	// 文件大小：<= 1MB（MAX_FILE_SIZE 前端已限，后端兜底）
	if (isset($_FILES['userfile']['size']) && $_FILES['userfile']['size'] > 1000000) {
		echo '<div style="border:1px solid #f5c6cb;background:#fdf0f0;border-radius:10px;padding:24px;text-align:center;margin:20px auto;max-width:640px;box-shadow:0 2px 8px rgba(220,53,69,.08)">';
		echo '<div style="font-size:17px;color:#c0392b;font-weight:bold;margin-bottom:8px">⚠️ 文件过大</div>';
		echo '<div style="color:#555;margin-bottom:16px">文件大小不能超过 <b>1MB</b>，请精简模板后重新上传。</div>';
		echo '<a href="' . $RootPath . '/BOMUpload.php" style="background:#1976D2;color:#fff;padding:9px 36px;border-radius:6px;text-decoration:none;font-size:14px;font-weight:600;display:inline-block">返回重新上传</a>';
		echo '</div>';
		include('includes/footer.inc');
		exit;
	}

	 $sql3 = "delete from bom_upload where created_by='".$_SESSION['UserID']."'";
		// echo $sql3;
		 
	    $result = DB_query($sql3,$db);

$excel = new Excel();       
 
$xls = $_FILES['userfile']['tmp_name']; 

$excel->setOutputEncoding('utf-8');

// 解析 Excel（失败时友好提示）
try {
	$excel->read($xls);
} catch (Exception $e) {
	echo '<div style="border:1px solid #f5c6cb;background:#fdf0f0;border-radius:10px;padding:24px;text-align:center;margin:20px auto;max-width:640px;box-shadow:0 2px 8px rgba(220,53,69,.08)">';
	echo '<div style="font-size:17px;color:#c0392b;font-weight:bold;margin-bottom:8px">⚠️ 文件解析失败</div>';
	echo '<div style="color:#555;margin-bottom:16px">无法读取该 Excel 文件，可能已损坏或不是有效的 Excel 格式。<br>请重新下载标准模板填写后再上传。</div>';
	echo '<a href="' . $RootPath . '/upload/BomUploadSample.xls" target="_blank" style="background:#e3f2fd;color:#0d47a1;padding:7px 18px;border-radius:6px;text-decoration:none;font-size:13px;border:1px solid #90caf9;display:inline-block;margin-right:10px">下载标准格式模板</a>';
	echo '<a href="' . $RootPath . '/BOMUpload.php" style="background:#1976D2;color:#fff;padding:9px 36px;border-radius:6px;text-decoration:none;font-size:14px;font-weight:600;display:inline-block">返回重新上传</a>';
	echo '</div>';
	include('includes/footer.inc');
	exit;
}
// 无数据行校验：Excel 首行可能是表头，需至少 1 行数据
if (!isset($excel->sheets[0]['cells']) || count($excel->sheets[0]['cells']) <= 1) {
	echo '<div style="border:1px solid #f5c6cb;background:#fdf0f0;border-radius:10px;padding:24px;text-align:center;margin:20px auto;max-width:640px;box-shadow:0 2px 8px rgba(220,53,69,.08)">';
	echo '<div style="font-size:17px;color:#c0392b;font-weight:bold;margin-bottom:8px">⚠️ 文件内容为空</div>';
	echo '<div style="color:#555;margin-bottom:16px">该 Excel 中没有可导入的数据行（首行为表头）。<br>请按标准格式填写料号、名称、数量、单位等列后重新上传。</div>';
	echo '<a href="' . $RootPath . '/upload/BomUploadSample.xls" target="_blank" style="background:#e3f2fd;color:#0d47a1;padding:7px 18px;border-radius:6px;text-decoration:none;font-size:13px;border:1px solid #90caf9;display:inline-block;margin-right:10px">下载标准格式模板</a>';
	echo '<a href="' . $RootPath . '/BOMUpload.php" style="background:#1976D2;color:#fff;padding:9px 36px;border-radius:6px;text-decoration:none;font-size:14px;font-weight:600;display:inline-block">返回重新上传</a>';
	echo '</div>';
	include('includes/footer.inc');
	exit;
}

$arr = $excel->sheets[0]['cells']; 
$i = 0;
$line=0;
$time = time();
foreach($arr as $arry=>$row){
    if($arry>1){
       $line=$line+1; 
	   $ri=substr($row['5'],0,2);
	   $yue=substr($row['5'],3,2);
	   $nian= substr($row['5'],6,4);
	   $heji= $nian.'/'.$yue.'/'.$ri;	

	   @$data[$i]['created_by'] =$_SESSION['UserID'];
	   @$data[$i]['item_num'] =$row['1'];
	   @$data[$i]['stockid'] = $row['2'];
	   @$data[$i]['item_name'] = $row['3'];
	   @$data[$i]['item_desc'] = $row['4'];
	   @$data[$i]['quantity'] = $row['5'];
	   @$data[$i]['uom'] = $row['6'];
	   @$data[$i]['sunhao_rate'] = $row['7'];
	   @$data[$i]['component_remarks'] = $row['8'];
	 $sql = "insert into bom_upload (item_num,stockid,item_name,item_desc,uom,sunhao_rate,operation_seq_num,quantity,component_remarks,created_by) 
			   values (
				   '".@$data[$i]['item_num']."',	
				   '".@$data[$i]['stockid']."',	
				   '".@$data[$i]['item_name']."',
				   '".@$data[$i]['item_desc']."',
				   '".@$data[$i]['uom']."',		
				   '".@$data[$i]['sunhao_rate']."',					    
				   '".@$data[$i]['operation_seq_num']."',  
				   '".@$data[$i]['quantity']."',
		
				   '".@$data[$i]['component_remarks']."',
				   '".@$data[$i]['created_by']."'
				     
			   )";
	   $result = DB_query($sql,$db);
		 
		 //echo $sql;
	}
	$i++;
}

// 解析后无有效数据行兜底提示（首行视为表头，$line 从第 2 行起计）
if ($line == 0) {
	echo '<div style="border:1px solid #f5c6cb;background:#fdf0f0;border-radius:10px;padding:24px;text-align:center;margin:20px auto;max-width:640px;box-shadow:0 2px 8px rgba(220,53,69,.08)">';
	echo '<div style="font-size:17px;color:#c0392b;font-weight:bold;margin-bottom:8px">⚠️ 没有可导入的数据</div>';
	echo '<div style="color:#555;margin-bottom:16px">该 Excel 中没有有效的 BOM 数据行（第 2 行起为数据）。<br>请按标准格式填写料号、名称、数量、单位等列后重新上传。</div>';
	echo '<a href="' . $RootPath . '/upload/BomUploadSample.xls" target="_blank" style="background:#e3f2fd;color:#0d47a1;padding:7px 18px;border-radius:6px;text-decoration:none;font-size:13px;border:1px solid #90caf9;display:inline-block;margin-right:10px">下载标准格式模板</a>';
	echo '<a href="' . $RootPath . '/BOMUpload.php" style="background:#1976D2;color:#fff;padding:9px 36px;border-radius:6px;text-decoration:none;font-size:14px;font-weight:600;display:inline-block">返回重新上传</a>';
	echo '</div>';
	include('includes/footer.inc');
	exit;
}
 

//echo '上传完成' ;
//exit;
unset($_SESSION['Request']);
echo '<div style="border:1px solid #d4edda;background:#f0f9f1;border-radius:10px;padding:24px;text-align:center;margin:20px auto;max-width:640px;box-shadow:0 2px 8px rgba(21,87,36,.08)">';
echo '<div style="font-size:17px;color:#155724;font-weight:bold;margin-bottom:8px">✅ 上传完成！</div>';
echo '<div style="color:#555;margin-bottom:18px">共解析 <b style="color:#155724;font-size:20px">' . $line . '</b> 笔资料，请进入确认界面核对后保存。</div>';
echo '<a href="' . $RootPath . '/BOMUpload2.php" style="background:#27ae60;color:#fff;padding:10px 42px;border-radius:6px;text-decoration:none;font-size:15px;font-weight:600;display:inline-block">进入上传资料确认界面 →</a>';
echo '</div>';

}  
 else  {	echo '<form action="BOMUpload.php" method="post" enctype="multipart/form-data">';
    echo '<div style="border:1px solid #d6e4f0;background:#fafcff;border-radius:10px;padding:26px;margin:20px auto;max-width:720px;box-shadow:0 2px 8px rgba(25,118,210,.08)">';
    echo '<div style="font-weight:bold;color:#0d47a1;font-size:17px;margin-bottom:10px">📥 BOM 整批上传</div>';
    echo '<div style="color:#5c6b7a;font-size:13px;line-height:1.9;margin-bottom:16px">1. 下载标准格式模板并按要求填写（料号、名称、数量、单位等列）。<br>2. 选择要上传的 Excel 文件（.xls），点击"确认上传"。<br>3. 上传后在确认界面核对数据并保存到 BOM。</div>';
    echo '<div style="margin-bottom:18px"><a href="' . $RootPath . '/upload/BomUploadSample.xls" target="_blank" style="background:#e3f2fd;color:#0d47a1;text-decoration:none;padding:7px 18px;border-radius:6px;font-size:13px;border:1px solid #90caf9;display:inline-block">⬇ 下载标准格式模板</a></div>';
    echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
    echo '<input type="hidden" name="MAX_FILE_SIZE" value="1000000" />';
    echo '<div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap;background:#f2f7fd;border:1px dashed #b8d4f5;border-radius:8px;padding:16px">';
    echo '<span style="color:#455a64;font-size:14px">选择需要上传的文件：</span>';
    echo '<input name="userfile" type="file" style="border:1px solid #cbd7e4;padding:6px 10px;border-radius:6px;background:#fff;font-size:13px" />';
    echo '<input type="submit" value="确认上传" style="background:#1976D2;color:#fff;border:none;padding:8px 32px;border-radius:6px;cursor:pointer;font-size:14px;font-weight:600" />';
    echo '</div></div>';
    echo '</form>';
		}
include('includes/footer.inc');

 
?>