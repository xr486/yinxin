<?php
/* 工序字典 —— 新增 / 编辑（操作 bom_parameters） */
include('includes/session.inc');
$Title = _('工序字典-编辑');

$edit = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$row = array('operation_code'=>'', 'operation_name'=>'', 'price'=>'0.00', 'use_status'=>'是', 'remark'=>'');

$feedback = '';
if ($edit > 0) {
    $r = DB_query("SELECT * FROM bom_parameters WHERE operation_id=" . $edit, $db);
    if ($tmp = DB_fetch_array($r)) $row = $tmp;
}

if (isset($_POST['save'])) {
    $code   = DB_escape_string(trim($_POST['operation_code']));
    $name   = DB_escape_string(trim($_POST['operation_name']));
    $price  = is_numeric($_POST['price']) ? (double)$_POST['price'] : 0;
    $status = DB_escape_string($_POST['use_status']);
    $remark = DB_escape_string(trim($_POST['remark']));
    $uid    = DB_escape_string($_SESSION['UserID']);
    $now    = time();

    if ($code == '' || $name == '') {
        $feedback = '工序编码和工序名称为必填项';
    } else {
        if ($edit > 0) {
            DB_query("UPDATE bom_parameters SET operation_code='$code', operation_name='$name', price=$price,
                      use_status='$status', remark='$remark', last_updated_by='$uid', last_update_date=$now
                      WHERE operation_id=$edit", $db);
        } else {
            DB_query("INSERT INTO bom_parameters (operation_code, operation_name, price, use_status, remark, created_by, creation_date)
                      VALUES ('$code','$name',$price,'$status','$remark','$uid',$now)", $db);
        }
        header('Location: ' . $RootPath . '/CraftOp.php');
        exit;
    }
    // 回填
    $row = array('operation_code'=>$_POST['operation_code'], 'operation_name'=>$_POST['operation_name'],
                 'price'=>$_POST['price'], 'use_status'=>$_POST['use_status'], 'remark'=>$_POST['remark']);
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="utf-8">
<link rel="stylesheet" href="<?php echo $RootPath;?>/css/xenos/tech.css">
<title><?php echo $Title;?></title>
</head>
<body>
<div class="tech-wrap">

  <div class="tech-pagehead">
    <h2><?php echo $edit>0 ? '编辑工序' : '新增工序';?></h2>
    <a class="btn btn-default" href="CraftOp.php" style="margin-left:auto;">返回列表</a>
  </div>

  <?php if ($feedback != '') { echo '<div class="tech-note blue">' . htmlspecialchars($feedback) . '</div>'; } ?>

  <div class="tech-card">
    <div class="tech-card-body">
      <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . ($edit>0?'?edit='.$edit:'');?>">
        <input type="hidden" name="FormID" value="<?php echo htmlspecialchars($_SESSION['FormID']);?>">
        <div class="tech-form">
          <div class="form-row">
            <label>工序编码<span class="req">*</span></label>
            <input type="text" name="operation_code" value="<?php echo htmlspecialchars($row['operation_code']);?>" placeholder="如 配制 / 分装 / 检验">
          </div>
          <div class="form-row">
            <label>工序名称<span class="req">*</span></label>
            <input type="text" name="operation_name" value="<?php echo htmlspecialchars($row['operation_name']);?>" placeholder="工序说明">
          </div>
          <div class="form-row">
            <label>工价</label>
            <input type="number" step="0.01" name="price" value="<?php echo htmlspecialchars($row['price']);?>">
          </div>
          <div class="form-row">
            <label>使用状态</label>
            <select name="use_status">
              <option value="是"<?php echo $row['use_status']=='是'?' selected':'';?>>启用</option>
              <option value="否"<?php echo $row['use_status']=='否'?' selected':'';?>>停用</option>
            </select>
          </div>
          <div class="form-row full">
            <label>备注</label>
            <textarea name="remark"><?php echo htmlspecialchars($row['remark']);?></textarea>
          </div>
        </div>
        <div class="form-actions">
          <button type="submit" name="save" class="btn btn-primary">保存</button>
          <a href="CraftOp.php" class="btn btn-default">取消</a>
        </div>
      </form>
    </div>
  </div>

</div>
</body>
</html>
