<?php
/* 工序字典 —— 操作老项目表 bom_parameters
 * 样式对齐 BOM管理：工具栏 + 查询卡 + 面板表格
 */
include('includes/session.inc');
$Title = _('工序字典');

$feedback = '';
// 删除
if (isset($_POST['delete'])) {
    $id = (int)$_POST['operation_id'];
    DB_query("DELETE FROM bom_parameters WHERE operation_id=" . $id, $db);
    $feedback = '工序已删除';
}

// 查询：首访即列出全部；点查询按条件过滤
$sql = "SELECT operation_id, operation_code, operation_name, price, use_status, remark
        FROM bom_parameters WHERE 1=1";
$where = array();
$searched = (isset($_POST['Search']) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous']));
if ($searched) {
    if (isset($_POST['code']) && $_POST['code'] != '')   $where[] = "operation_code LIKE '%" . DB_escape_string($_POST['code']) . "%'";
    if (isset($_POST['name']) && $_POST['name'] != '')   $where[] = "operation_name LIKE '%" . DB_escape_string($_POST['name']) . "%'";
    if (isset($_POST['status']) && $_POST['status'] != '') $where[] = "use_status='" . DB_escape_string($_POST['status']) . "'";
}
if (count($where) > 0) $sql .= " AND " . implode(' AND ', $where);
$sql .= " ORDER BY operation_id";
$result = DB_query($sql, $db);
$rows = array();
while ($r = DB_fetch_array($result)) $rows[] = $r;
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="utf-8">
<link rel="stylesheet" href="<?php echo $RootPath;?>/css/xenos/tech.css">
<title><?php echo $Title;?></title>
<style>.op-name{white-space:normal !important;text-align:left;max-width:320px;}</style>
</head>
<body>
<div class="tech-wrap">

  <div class="tech-toolbar" style="margin-bottom:14px;">
    <span class="toolbar-title">工序字典</span>
    <div class="toolbar-actions">
      <a class="btn btn-primary" href="CraftOp2.php">+ 新增工序</a>
    </div>
  </div>

  <?php if ($feedback != '') { echo '<div class="tech-note blue">' . htmlspecialchars($feedback) . '</div>'; } ?>

  <div class="tech-card">
    <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']);?>" class="tech-search">
      <input type="hidden" name="FormID" value="<?php echo htmlspecialchars($_SESSION['FormID']);?>">
      <div class="tech-field">
        <label>工序编码</label>
        <input type="text" name="code" value="<?php echo isset($_POST['code'])?htmlspecialchars($_POST['code']):'';?>">
      </div>
      <div class="tech-field">
        <label>工序名称</label>
        <input type="text" name="name" value="<?php echo isset($_POST['name'])?htmlspecialchars($_POST['name']):'';?>">
      </div>
      <div class="tech-field">
        <label>使用状态</label>
        <select name="status">
          <option value="">全部</option>
          <option value="是"<?php echo (isset($_POST['status'])&&$_POST['status']=='是')?' selected':'';?>>启用</option>
          <option value="否"<?php echo (isset($_POST['status'])&&$_POST['status']=='否')?' selected':'';?>>停用</option>
        </select>
      </div>
      <div class="search-actions">
        <button type="submit" name="Search" class="btn btn-primary">查询</button>
        <a href="CraftOp.php" class="btn btn-default">重置</a>
      </div>
    </form>
  </div>

  <div class="tech-panel-wrap" style="background:#fff;">
    <table class="tech-panel-table" style="min-width:860px;">
      <thead>
        <tr>
          <th style="width:80px;">序号</th>
          <th style="width:110px;">工序编码</th>
          <th style="max-width:340px;">工序名称</th>
          <th style="width:100px;">工价</th>
          <th style="width:90px;">状态</th>
          <th style="min-width:140px;">备注</th>
          <th style="width:150px;">操作</th>
        </tr>
      </thead>
      <tbody>
      <?php if (count($rows) == 0) { ?>
        <tr class="empty-row"><td colspan="7"><?php echo $searched ? '未找到符合条件的工序' : '暂无工序数据';?></td></tr>
      <?php } else {
        $i = 1;
        foreach ($rows as $row) {
          $st = ($row['use_status'] == '是') ? '<span class="tag tag-green">启用</span>' : '<span class="tag tag-gray">停用</span>';
          echo '<tr>';
          echo '<td>' . $i++ . '</td>';
          echo '<td>' . htmlspecialchars($row['operation_code']) . '</td>';
          echo '<td class="left op-name">' . htmlspecialchars($row['operation_name']) . '</td>';
          echo '<td>' . htmlspecialchars($row['price']) . '</td>';
          echo '<td>' . $st . '</td>';
          echo '<td class="left">' . htmlspecialchars($row['remark']) . '</td>';
          echo '<td><div class="row-actions">';
          echo '<a class="btn btn-sm btn-primary" href="CraftOp2.php?edit=' . $row['operation_id'] . '">编辑</a>';
          echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF']) . '" style="display:inline;" onsubmit="return confirm(\'确定删除该工序？\');">';
          echo '<input type="hidden" name="FormID" value="' . htmlspecialchars($_SESSION['FormID']) . '">';
          echo '<input type="hidden" name="operation_id" value="' . $row['operation_id'] . '">';
          echo '<button type="submit" name="delete" class="btn btn-sm btn-danger">删除</button>';
          echo '</form>';
          echo '</div></td>';
          echo '</tr>';
        }
      } ?>
      </tbody>
    </table>
  </div>

</div>
</body>
</html>
