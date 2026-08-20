<?php
/* 产品工艺审核 —— 操作老项目表 bom_routings_all
 * 列表：展示有待签核工艺路线的成品/半成品；点「签核」进入明细，可签核/拒签（整单翻转 route_status）
 * 逻辑对齐老项目 BOMRouteApprove/BOMRouteApprove2（UPDATE ... WHERE assembly_item_no）
 * 样式对齐 BOM管理（tech.css）
 */
include('includes/session.inc');
$Title = _('产品工艺审核');

function craftItemIcon($item_type, $size = 'sm') {
    switch ($item_type) {
        case 'F':
            $svg = '<path d="M8 0.5 L15.5 4 L15.5 13 L8 16.5 L0.5 13 L0.5 4 Z" fill="#42A5F5" stroke="#1976D2" stroke-width="0.6"/><path d="M0.5 4 L8 7.5 L15.5 4 M8 7.5 L8 16.5" fill="none" stroke="#1976D2" stroke-width="0.6"/>';
            $title = '成品';
            break;
        case 'B':
            $svg = '<polygon points="0.5,5 4,1 12,1 15.5,5 15.5,6.2 12,10.2 4,10.2 0.5,6.2" fill="#37474F" stroke="#1c1c1c" stroke-width="0.5"/><rect x="6" y="10.2" width="4" height="4.5" fill="#546E7A" stroke="#1c1c1c" stroke-width="0.5"/><polygon points="6,14.7 10,14.7 8,16" fill="#37474F" stroke="#1c1c1c" stroke-width="0.5"/>';
            $title = '半成品';
            break;
        default:
            $svg = '<circle cx="8" cy="8" r="7" fill="#9E9E9E" stroke="#616161" stroke-width="0.6"/>';
            $title = '其他';
    }
    $px = ($size === 'lg') ? 16 : 14;
    return '<svg viewBox="0 0 16 16" width="' . $px . '" height="' . $px . '" title="' . $title . '" style="display:block;width:' . $px . 'px;height:' . $px . 'px;">' . $svg . '</svg>';
}

// 处理签核 / 拒签（整单翻转该物料所有工艺路线行）
if (isset($_POST['Submit']) OR isset($_POST['Reject'])) {
    $newStatus = isset($_POST['Submit']) ? '已签核' : '已拒签';
    $item = DB_escape_string($_POST['assembly_item_no']);
    $v_date = time();
    DB_Txn_Begin($db);
    $sql = "UPDATE bom_routings_all
               SET route_status = '" . $newStatus . "',
                   approve_date  = '" . $v_date . "',
                   approved_by   = '" . $_SESSION['UserID'] . "'
             WHERE assembly_item_no = '" . $item . "'";
    DB_query($sql, $db);
    DB_Txn_Commit($db);
    $msg = isset($_POST['Submit']) ? '签核成功！' : '拒签成功！';
    header('Location: ' . $RootPath . '/CraftRouteApprove.php?msg=' . urlencode($msg));
    exit;
}

// 明细视图标识
$viewItem = isset($_GET['approve']) ? trim($_GET['approve']) : '';

// 列表查询：首访即列出待签核
$list = array();
if ($viewItem == '') {
    $sql = "SELECT b.item_no, b.item_name, b.item_desc, b.item_type,
                   (SELECT COUNT(*) FROM bom_routings_all r WHERE r.assembly_item_no=b.item_no AND r.disable_date IS NULL) AS route_count
            FROM sf_item_no b
            WHERE b.item_type IN ('F','B')
              AND b.item_no IN (SELECT assembly_item_no FROM bom_routings_all WHERE route_status='待签核' AND disable_date IS NULL)";
    $where = array();
    if (isset($_POST['Search']) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])) {
        if (isset($_POST['item_no']) && $_POST['item_no'] != '')    $where[] = "b.item_no LIKE '%" . DB_escape_string($_POST['item_no']) . "%'";
        if (isset($_POST['item_name']) && $_POST['item_name'] != '') $where[] = "b.item_name LIKE '%" . DB_escape_string($_POST['item_name']) . "%'";
    }
    if (count($where) > 0) $sql .= " AND " . implode(' AND ', $where);
    $sql .= " ORDER BY b.item_type, b.item_no";
    $result = DB_query($sql, $db);
    while ($r = DB_fetch_array($result)) $list[] = $r;
}

// 明细数据
$itemInfo = null;
$steps = array();
if ($viewItem != '') {
    $iq = DB_query("SELECT item_no, item_name, item_desc, item_type FROM sf_item_no WHERE item_no='" . DB_escape_string($viewItem) . "'", $db);
    $itemInfo = DB_fetch_array($iq);
    $rq = DB_query("SELECT a.route_id, a.operation_seq_num, a.operation_code,
                          (SELECT operation_name FROM bom_parameters p WHERE p.operation_code=a.operation_code LIMIT 1) AS operation_name,
                          a.rate, a.channeng, a.renli, a.remarks, a.creation_date, a.route_status
                    FROM bom_routings_all a
                    WHERE a.assembly_item_no='" . DB_escape_string($viewItem) . "' AND a.disable_date IS NULL
                    ORDER BY a.operation_seq_num", $db);
    while ($s = DB_fetch_array($rq)) $steps[] = $s;
}

$msg = isset($_GET['msg']) ? $_GET['msg'] : '';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="utf-8">
<link rel="stylesheet" href="<?php echo $RootPath;?>/css/xenos/tech.css">
<title><?php echo $Title;?></title>
<style>.op-name{white-space:normal !important;text-align:left;max-width:280px;}</style>
</head>
<body>
<div class="tech-wrap">

  <?php if ($msg != '') { echo '<div class="tech-note blue">' . htmlspecialchars($msg) . '</div>'; } ?>

  <?php if ($viewItem == '') { ?>
  <!-- 列表视图 -->
  <div class="tech-toolbar" style="margin-bottom:14px;">
    <span class="toolbar-title">产品工艺审核</span>
    <span style="font-size:12px;color:#666;">仅显示存在待签核工艺路线的成品/半成品</span>
  </div>

  <div class="tech-card">
    <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']);?>" class="tech-search">
      <input type="hidden" name="FormID" value="<?php echo htmlspecialchars($_SESSION['FormID']);?>">
      <div class="tech-field">
        <label>料号</label>
        <input type="text" name="item_no" value="<?php echo isset($_POST['item_no'])?htmlspecialchars($_POST['item_no']):'';?>">
      </div>
      <div class="tech-field">
        <label>料号名称</label>
        <input type="text" name="item_name" value="<?php echo isset($_POST['item_name'])?htmlspecialchars($_POST['item_name']):'';?>">
      </div>
      <div class="search-actions">
        <button type="submit" name="Search" class="btn btn-primary">查询</button>
        <a href="CraftRouteApprove.php" class="btn btn-default">重置</a>
      </div>
    </form>
  </div>

  <div class="tech-panel-wrap" style="background:#fff;">
    <table class="tech-panel-table" style="min-width:820px;">
      <thead>
        <tr>
          <th style="width:50px;">类型</th>
          <th style="width:130px;">料号</th>
          <th style="min-width:160px;">料号名称</th>
          <th style="min-width:140px;">规格型号</th>
          <th style="width:90px;">工序数</th>
          <th style="width:100px;">状态</th>
          <th style="width:110px;">操作</th>
        </tr>
      </thead>
      <tbody>
      <?php if (count($list) == 0) { ?>
        <tr class="empty-row"><td colspan="7">暂无待签核的工艺路线</td></tr>
      <?php } else {
        foreach ($list as $row) {
          echo '<tr>';
          echo '<td><span class="item-icon" style="display:inline-flex;vertical-align:middle;">' . craftItemIcon($row['item_type']) . '</span></td>';
          echo '<td>' . htmlspecialchars($row['item_no']) . '</td>';
          echo '<td class="left">' . htmlspecialchars($row['item_name']) . '</td>';
          echo '<td class="left">' . htmlspecialchars($row['item_desc']) . '</td>';
          echo '<td>' . htmlspecialchars($row['route_count']) . '</td>';
          echo '<td><span class="tag tag-orange">待签核</span></td>';
          echo '<td><a class="btn btn-sm btn-success" href="?approve=' . urlencode($row['item_no']) . '">签核</a></td>';
          echo '</tr>';
        }
      } ?>
      </tbody>
    </table>
  </div>

  <?php } else { ?>
  <!-- 明细视图 -->
  <div class="tech-toolbar" style="margin-bottom:14px;">
    <span class="toolbar-title">工艺审核明细</span>
    <div class="toolbar-actions">
      <a class="btn btn-default btn-sm" href="CraftRouteApprove.php">← 返回列表</a>
    </div>
  </div>

  <div class="tech-card">
    <div class="tech-card-body">
      <div class="tech-right-head" style="border-bottom:none;margin-bottom:0;padding-bottom:0;">
        <h3>
          <span class="item-icon" style="display:inline-flex;vertical-align:middle;margin-right:4px;"><?php echo craftItemIcon($itemInfo ? $itemInfo['item_type'] : '', 'lg');?></span>
          物料：<?php echo $itemInfo ? htmlspecialchars($itemInfo['item_no']) : htmlspecialchars($viewItem);?>
          <?php echo $itemInfo ? ' / ' . htmlspecialchars($itemInfo['item_name']) : '';?>
        </h3>
        <div class="meta"><span class="tag tag-orange">待签核</span></div>
      </div>
      <div style="font-size:13px;color:#666;margin-top:6px;">规格型号：<?php echo $itemInfo && $itemInfo['item_desc'] ? htmlspecialchars($itemInfo['item_desc']) : '—';?></div>
    </div>
  </div>

  <div class="tech-panel-wrap" style="background:#fff;flex:none;height:auto;">
    <table class="tech-panel-table" style="min-width:820px;">
      <thead>
        <tr>
          <th style="width:70px;">序号</th>
          <th style="width:120px;">工序编码</th>
          <th class="left">工序名称</th>
          <th style="width:90px;">工时</th>
          <th style="width:90px;">产能</th>
          <th style="width:110px;">人力</th>
          <th style="min-width:140px;">备注</th>
        </tr>
      </thead>
      <tbody>
      <?php if (count($steps) == 0) { ?>
        <tr class="empty-row"><td colspan="7">该物料暂无工艺路线</td></tr>
      <?php } else {
        foreach ($steps as $s) { ?>
        <tr>
          <td><?php echo htmlspecialchars($s['operation_seq_num']);?></td>
          <td><?php echo htmlspecialchars($s['operation_code']);?></td>
          <td class="left op-name"><?php echo htmlspecialchars($s['operation_name']);?></td>
          <td><?php echo htmlspecialchars($s['rate']);?></td>
          <td><?php echo htmlspecialchars($s['channeng']);?></td>
          <td><?php echo htmlspecialchars($s['renli']);?></td>
          <td class="left"><?php echo htmlspecialchars($s['remarks']);?></td>
        </tr>
        <?php }
      } ?>
      </tbody>
    </table>
  </div>

  <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']);?>" style="margin-top:14px;">
    <input type="hidden" name="FormID" value="<?php echo htmlspecialchars($_SESSION['FormID']);?>">
    <input type="hidden" name="assembly_item_no" value="<?php echo htmlspecialchars($viewItem);?>">
    <div style="display:flex;gap:10px;">
      <button type="submit" name="Submit" class="btn btn-success">签核</button>
      <button type="submit" name="Reject" class="btn btn-danger" onclick="return confirm('确定拒签该工艺路线？');">拒签</button>
    </div>
  </form>

  <?php } ?>

</div>
</body>
</html>
