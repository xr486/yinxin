<?php
/* 作业指导书 SOP —— 复用老项目表
 * bom_routing_public_file (按 item_no 绑定 共用指导书)
 * bom_routing_all_file   (按 route_id 绑定 工序指导书)
 * 上传复用老项目 upload2('Pic','SO')，文件存 yixin/SO/，file_patch 存相对路径
 * 左侧：物料大类 → 成品/半成品 树（对齐工艺模块其他页面）
 * 右侧：① 共用指导书（按零部件）② 各工序的工序指导书（按 route_id）
 */
include('includes/session.inc');
$Title = _('作业指导书');

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
        case 'M':
            $svg = '<path d="M8 0.3 L9.3 2.5 L11.7 2 L12.3 4.4 L14.7 4.7 L13.8 7.5 L15.7 8 L13.8 8.5 L14.7 11.3 L12.3 11.6 L11.7 14 L9.3 13.5 L8 15.7 L6.7 13.5 L4.3 14 L3.7 11.6 L1.3 11.3 L2.2 8.5 L0.3 8 L2.2 7.5 L1.3 4.7 L3.7 4.4 L4.3 2 L6.7 2.5 Z" fill="#FFA726" stroke="#F57C00" stroke-width="0.5"/><circle cx="8" cy="8" r="2.5" fill="#fff" stroke="#F57C00" stroke-width="0.5"/>';
            $title = '原材料';
            break;
        default:
            $svg = '<circle cx="8" cy="8" r="7" fill="#9E9E9E" stroke="#616161" stroke-width="0.6"/>';
            $title = '其他';
    }
    $px = ($size === 'lg') ? 16 : 14;
    return '<svg viewBox="0 0 16 16" width="' . $px . '" height="' . $px . '" title="' . $title . '" style="display:block;width:' . $px . 'px;height:' . $px . 'px;">' . $svg . '</svg>';
}

function craftIsImage($path) {
    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    return in_array($ext, array('jpg', 'jpeg', 'png', 'gif'));
}

$item = isset($_GET['item']) ? trim($_GET['item']) : (isset($_POST['item']) ? trim($_POST['item']) : '');
$feedback = isset($_GET['msg']) ? $_GET['msg'] : '';

require_once 'upload2.class.php';

// 上传允许类型：在老项目图片基础上扩展 PDF/Office（同一 upload2 组件，不修改组件本身）
$allowExt = array('jpeg', 'jpg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'zip', 'rar');
$allowMime = array(
    'image/jpeg', 'image/png', 'image/gif',
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.ms-excel',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'text/plain',
    'application/zip', 'application/x-zip-compressed',
    'application/octet-stream', 'application/x-rar-compressed'
);

if (isset($_POST['act']) && $item != '') {
    $act = $_POST['act'];
    $uid = DB_escape_string($_SESSION['UserID']);
    $now = time();
    if ($act == 'upload_public') {
        $upload = new upload2('Pic', 'SO', false, 15242880, $allowExt, $allowMime);
        $res = $upload->uploadFile();
        $ok = 0;
        foreach ($res as $idx => $r) {
            if (!empty($r['dest'])) {
                $fname = (isset($_POST['file_name'][$idx]) && trim($_POST['file_name'][$idx]) != '')
                    ? DB_escape_string(trim($_POST['file_name'][$idx]))
                    : DB_escape_string($_FILES['Pic']['name'][$idx]);
                DB_query("INSERT INTO bom_routing_public_file (file_name,item_no,file_patch,creation_date,created_by)
                          VALUES ('$fname','" . DB_escape_string($item) . "','" . DB_escape_string($r['dest']) . "','$now','$uid')", $db);
                $ok++;
            }
        }
        $feedback = $ok > 0 ? ('已上传 ' . $ok . ' 个共用指导书') : '未选择文件或上传失败';
        header('Location: ' . $RootPath . '/CraftRouteDoc.php?item=' . urlencode($item) . '&msg=' . urlencode($feedback));
        exit;
    } elseif ($act == 'upload_route') {
        $rid = (int)$_POST['rid'];
        $ckQ = DB_query("SELECT route_id FROM bom_routings_all WHERE route_id=$rid AND assembly_item_no='" . DB_escape_string($item) . "'", $db);
        $ck = DB_fetch_array($ckQ);
        if (!$ck) {
            $feedback = '工序不存在或不属于该物料';
            header('Location: ' . $RootPath . '/CraftRouteDoc.php?item=' . urlencode($item) . '&msg=' . urlencode($feedback));
            exit;
        }
        $upload = new upload2('Pic', 'SO', false, 15242880, $allowExt, $allowMime);
        $res = $upload->uploadFile();
        $ok = 0;
        foreach ($res as $idx => $r) {
            if (!empty($r['dest'])) {
                $fname = (isset($_POST['file_name'][$idx]) && trim($_POST['file_name'][$idx]) != '')
                    ? DB_escape_string(trim($_POST['file_name'][$idx]))
                    : DB_escape_string($_FILES['Pic']['name'][$idx]);
                DB_query("INSERT INTO bom_routing_all_file (file_name,item_no,route_id,file_patch,creation_date,created_by)
                          VALUES ('$fname','" . DB_escape_string($item) . "',$rid,'" . DB_escape_string($r['dest']) . "','$now','$uid')", $db);
                $ok++;
            }
        }
        $feedback = $ok > 0 ? ('已上传 ' . $ok . ' 个工序指导书') : '未选择文件或上传失败';
        header('Location: ' . $RootPath . '/CraftRouteDoc.php?item=' . urlencode($item) . '&msg=' . urlencode($feedback));
        exit;
    } elseif ($act == 'del_public') {
        $id = (int)$_POST['id'];
        $rowQ = DB_query("SELECT file_patch FROM bom_routing_public_file WHERE itemid=$id AND item_no='" . DB_escape_string($item) . "'", $db);
        $row = DB_fetch_array($rowQ);
        if ($row) {
            if (file_exists($row['file_patch'])) @unlink($row['file_patch']);
            DB_query("DELETE FROM bom_routing_public_file WHERE itemid=$id", $db);
            $feedback = '已删除共用指导书';
        }
        header('Location: ' . $RootPath . '/CraftRouteDoc.php?item=' . urlencode($item) . '&msg=' . urlencode($feedback));
        exit;
    } elseif ($act == 'del_route') {
        $id = (int)$_POST['id'];
        $rowQ = DB_query("SELECT file_patch FROM bom_routing_all_file WHERE itemid=$id AND item_no='" . DB_escape_string($item) . "'", $db);
        $row = DB_fetch_array($rowQ);
        if ($row) {
            if (file_exists($row['file_patch'])) @unlink($row['file_patch']);
            DB_query("DELETE FROM bom_routing_all_file WHERE itemid=$id", $db);
            $feedback = '已删除工序指导书';
        }
        header('Location: ' . $RootPath . '/CraftRouteDoc.php?item=' . urlencode($item) . '&msg=' . urlencode($feedback));
        exit;
    }
}

// 左侧树：成品(B)/半成品(F) 按 item_category1 分组
$cats = array();
$itemMap = array();
$q = DB_query("SELECT item_no, item_name, item_category1, item_type FROM sf_item_no
               WHERE item_type IN ('B','F') ORDER BY item_category1, item_no", $db);
while ($it = DB_fetch_array($q)) {
    $c = $it['item_category1'] ? $it['item_category1'] : '未分类';
    if (!isset($cats[$c])) $cats[$c] = array();
    $cats[$c][] = $it;
    $itemMap[$it['item_no']] = $it;
}

// 工序字典（用于显示工序名称）
$opsMap = array();
$oq = DB_query("SELECT operation_code, operation_name FROM bom_parameters ORDER BY operation_id", $db);
while ($o = DB_fetch_array($oq)) $opsMap[$o['operation_code']] = $o['operation_name'];

$activeCat = null;
if ($item != '') {
    foreach ($cats as $cKey => $cItems) {
        foreach ($cItems as $cIt) {
            if ($cIt['item_no'] == $item) { $activeCat = $cKey; break 2; }
        }
    }
}

// 右侧数据
$pubFiles = array();
$steps = array();
$activeItemName = '';
$activeItemType = '';
$tab = isset($_GET['tab']) ? trim($_GET['tab']) : 'public';
if ($tab != 'public' && $tab != 'route') $tab = 'public';
if ($item != '') {
    $pq = DB_query("SELECT * FROM bom_routing_public_file WHERE item_no='" . DB_escape_string($item) . "' ORDER BY creation_date DESC", $db);
    while ($p = DB_fetch_array($pq)) $pubFiles[] = $p;

    $rq = DB_query("SELECT * FROM bom_routings_all WHERE assembly_item_no='" . DB_escape_string($item) . "'
                    AND disable_date IS NULL ORDER BY operation_seq_num", $db);
    while ($s = DB_fetch_array($rq)) {
        $s['files'] = array();
        $fq = DB_query("SELECT * FROM bom_routing_all_file WHERE route_id=" . (int)$s['route_id'] . " ORDER BY creation_date DESC", $db);
        while ($f = DB_fetch_array($fq)) $s['files'][] = $f;
        $steps[] = $s;
    }
    if (isset($itemMap[$item])) {
        $activeItemName = $itemMap[$item]['item_name'];
        $activeItemType = $itemMap[$item]['item_type'];
    }
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

  <?php if ($feedback != '') echo '<div class="tech-note blue">' . htmlspecialchars($feedback) . '</div>'; ?>

  <div class="tech-layout">
    <!-- 左侧树 -->
    <div class="tech-left">
      <div class="tech-left-head">
        <span>物料分类</span>
        <div class="toolbar-actions">
          <button type="button" class="btn btn-sm btn-default" onclick="toggleAllCats(true)">全部展开</button>
          <button type="button" class="btn btn-sm btn-default" onclick="toggleAllCats(false)">全部折叠</button>
        </div>
      </div>
      <div class="tech-left-body">
        <div class="tech-tree" id="catTree">
          <?php if (count($cats) == 0) { echo '<div style="padding:10px;color:#888">暂无成品/半成品。</div>'; }
          foreach ($cats as $c => $items) {
            $collapsed = ($c != $activeCat);
            $disp = $collapsed ? '' : 'open';
            $arrow = $collapsed ? '▸' : '▾';
            ?>
            <div class="cat-row" onclick="toggleCat(this)">
              <span class="arrow"><?php echo $arrow;?></span>
              <span class="lbl"><?php echo htmlspecialchars($c);?> (<?php echo count($items);?>)</span>
            </div>
            <div class="children <?php echo $disp;?>">
              <?php foreach ($items as $it) {
                $active = ($it['item_no'] == $item) ? ' active' : '';
                echo '<a class="item-row' . $active . '" href="?item=' . urlencode($it['item_no']) . '" title="' . htmlspecialchars($it['item_no'] . ' ' . $it['item_name']) . '">'
                   . '<span class="item-icon">' . craftItemIcon($it['item_type']) . '</span>'
                   . '<span class="lbl">' . htmlspecialchars($it['item_no'] . ' ' . $it['item_name']) . '</span>'
                   . '</a>';
              } ?>
            </div>
          <?php } ?>
        </div>
      </div>
    </div>

    <!-- 右侧明细 -->
    <div class="tech-right">
      <?php if ($item == '') { ?>
        <div class="tech-empty-tip">请从左侧选择成品或半成品，维护其作业指导书（SOP）。</div>
      <?php } else { ?>
        <div class="tech-right-head">
          <h3>
            <span class="item-icon" style="display:inline-flex;vertical-align:middle;margin-right:4px;"><?php echo craftItemIcon($activeItemType, 'lg');?></span>
            物料：<?php echo htmlspecialchars($item . ($activeItemName ? ' / ' . $activeItemName : ''));?>
          </h3>
          <div class="meta">作业指导书管理</div>
        </div>

        <!-- Tab 切换：共用指导书 / 工序指导书 -->
        <div class="tech-tabs">
          <a class="tech-tab <?php echo $tab=='public'?'active':'';?>" href="?item=<?php echo urlencode($item);?>&tab=public">共用指导书 <span class="tab-count"><?php echo count($pubFiles);?></span></a>
          <a class="tech-tab <?php echo $tab=='route'?'active':'';?>" href="?item=<?php echo urlencode($item);?>&tab=route">工序指导书 <span class="tab-count"><?php echo count($steps);?></span></a>
        </div>

        <?php if ($tab == 'public') { ?>
        <!-- ① 共用指导书（按零部件） -->
        <div class="tech-toolbar">
          <div class="toolbar-actions">
            <button type="button" class="btn btn-success btn-sm" onclick="toggle('pubUpload')">+ 上传共用指导书</button>
          </div>
        </div>

        <div class="tech-panel-wrap">
          <table class="tech-panel-table" style="min-width:640px;">
            <thead>
              <tr>
                <th style="min-width:180px;">文件名称</th>
                <th style="width:150px;">上传时间</th>
                <th style="width:90px;">上传人</th>
                <th style="width:130px;">预览/下载</th>
                <th style="width:80px;">操作</th>
              </tr>
            </thead>
            <tbody>
            <?php if (count($pubFiles) == 0) { ?>
              <tr class="empty-row"><td colspan="5">暂无共用指导书，点击「上传共用指导书」添加。</td></tr>
            <?php } else {
              foreach ($pubFiles as $pf) { ?>
              <tr>
                <td class="left"><?php echo htmlspecialchars($pf['file_name']);?></td>
                <td><?php echo date('Y-m-d H:i:s', $pf['creation_date']);?></td>
                <td><?php echo htmlspecialchars($pf['created_by']);?></td>
                <td>
                  <?php if (craftIsImage($pf['file_patch'])) { ?>
                    <a href="<?php echo $RootPath . '/' . $pf['file_patch'];?>" target="_blank"><img class="doc-thumb" src="<?php echo $RootPath . '/' . $pf['file_patch'];?>"></a>
                  <?php } ?>
                  <a class="btn btn-sm btn-default" href="<?php echo $RootPath . '/' . $pf['file_patch'];?>" target="_blank">下载</a>
                </td>
                <td>
                  <form method="post" action="?item=<?php echo urlencode($item);?>" style="display:inline;">
                    <input type="hidden" name="FormID" value="<?php echo htmlspecialchars($_SESSION['FormID']);?>">
                    <input type="hidden" name="item" value="<?php echo htmlspecialchars($item);?>">
                    <input type="hidden" name="act" value="del_public">
                    <input type="hidden" name="id" value="<?php echo $pf['itemid'];?>">
                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('确定删除该共用指导书？')">删除</button>
                  </form>
                </td>
              </tr>
              <?php }
            } ?>
            </tbody>
          </table>
        </div>

        <!-- 共用指导书上传表单 -->
        <form class="tech-upload-form" id="pubUpload" method="post" action="?item=<?php echo urlencode($item);?>" enctype="multipart/form-data">
          <input type="hidden" name="FormID" value="<?php echo htmlspecialchars($_SESSION['FormID']);?>">
          <input type="hidden" name="item" value="<?php echo htmlspecialchars($item);?>">
          <input type="hidden" name="act" value="upload_public">
          <div class="upload-slots">
            <?php for ($j = 1; $j <= 6; $j++) { ?>
            <div class="upload-slot">
              <input type="text" name="file_name[]" placeholder="文件名称(可空，默认用原文件名)">
              <input type="file" name="Pic[]">
            </div>
            <?php } ?>
          </div>
          <div class="upload-actions">
            <button type="submit" class="btn btn-primary btn-sm">保存上传</button>
            <button type="button" class="btn btn-default btn-sm" onclick="toggle('pubUpload')">取消</button>
          </div>
        </form>

        <?php } else { ?>
        <?php if (count($steps) == 0) { ?>
          <div class="tech-empty-tip">该物料暂无工艺路线，请先在「产品工艺路线」中编制工序，再绑定工序指导书。</div>
        <?php } else {
          foreach ($steps as $s) {
            $opName = isset($opsMap[$s['operation_code']]) ? $opsMap[$s['operation_code']] : '';
            ?>
          <div class="route-card">
            <div class="route-card-head">
              工序 <?php echo $s['operation_seq_num'];?>
              ：<?php echo htmlspecialchars($s['operation_code'] . ($opName ? ' - ' . $opName : ''));?>
            </div>
            <div class="route-card-body">
              <table class="tech-panel-table" style="min-width:640px;">
                <thead>
                  <tr>
                    <th style="min-width:180px;">文件名称</th>
                    <th style="width:150px;">上传时间</th>
                    <th style="width:90px;">上传人</th>
                    <th style="width:130px;">预览/下载</th>
                    <th style="width:80px;">操作</th>
                  </tr>
                </thead>
                <tbody>
                <?php if (count($s['files']) == 0) { ?>
                  <tr class="empty-row"><td colspan="5">暂无工序指导书，点击「上传工序指导书」添加。</td></tr>
                <?php } else {
                  foreach ($s['files'] as $ff) { ?>
                  <tr>
                    <td class="left"><?php echo htmlspecialchars($ff['file_name']);?></td>
                    <td><?php echo date('Y-m-d H:i:s', $ff['creation_date']);?></td>
                    <td><?php echo htmlspecialchars($ff['created_by']);?></td>
                    <td>
                      <?php if (craftIsImage($ff['file_patch'])) { ?>
                        <a href="<?php echo $RootPath . '/' . $ff['file_patch'];?>" target="_blank"><img class="doc-thumb" src="<?php echo $RootPath . '/' . $ff['file_patch'];?>"></a>
                      <?php } ?>
                      <a class="btn btn-sm btn-default" href="<?php echo $RootPath . '/' . $ff['file_patch'];?>" target="_blank">下载</a>
                    </td>
                    <td>
                      <form method="post" action="?item=<?php echo urlencode($item);?>" style="display:inline;">
                        <input type="hidden" name="FormID" value="<?php echo htmlspecialchars($_SESSION['FormID']);?>">
                        <input type="hidden" name="item" value="<?php echo htmlspecialchars($item);?>">
                        <input type="hidden" name="act" value="del_route">
                        <input type="hidden" name="id" value="<?php echo $ff['itemid'];?>">
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('确定删除该工序指导书？')">删除</button>
                      </form>
                    </td>
                  </tr>
                  <?php }
                } ?>
                </tbody>
              </table>
              <div class="upload-actions">
                <button type="button" class="btn btn-sm btn-success" onclick="toggle('routeUpload_<?php echo $s['route_id'];?>')">+ 上传工序指导书</button>
              </div>
              <form class="tech-upload-form" id="routeUpload_<?php echo $s['route_id'];?>" method="post" action="?item=<?php echo urlencode($item);?>" enctype="multipart/form-data">
                <input type="hidden" name="FormID" value="<?php echo htmlspecialchars($_SESSION['FormID']);?>">
                <input type="hidden" name="item" value="<?php echo htmlspecialchars($item);?>">
                <input type="hidden" name="act" value="upload_route">
                <input type="hidden" name="rid" value="<?php echo $s['route_id'];?>">
                <div class="upload-slots">
                  <?php for ($j = 1; $j <= 6; $j++) { ?>
                  <div class="upload-slot">
                    <input type="text" name="file_name[]" placeholder="文件名称(可空，默认用原文件名)">
                    <input type="file" name="Pic[]">
                  </div>
                  <?php } ?>
                </div>
                <div class="upload-actions">
                  <button type="submit" class="btn btn-primary btn-sm">保存上传</button>
                  <button type="button" class="btn btn-default btn-sm" onclick="toggle('routeUpload_<?php echo $s['route_id'];?>')">取消</button>
                </div>
              </form>
            </div>
          </div>
          <?php } } ?>
        <?php } ?>
        <?php } ?>
    </div>
  </div>

</div>
<script>
function toggle(id){
  var el = document.getElementById(id);
  if (!el) return;
  el.classList.toggle('show');
}
function toggleCat(el){
  var children = el.nextElementSibling;
  var arrow = el.querySelector('.arrow');
  if (children.classList.contains('open')) {
    children.classList.remove('open');
    arrow.innerText = '▸';
  } else {
    children.classList.add('open');
    arrow.innerText = '▾';
  }
}
function toggleAllCats(expand){
  var tree = document.getElementById('catTree');
  var children = tree.querySelectorAll('.children');
  var arrows = tree.querySelectorAll('.cat-row .arrow');
  for (var i = 0; i < children.length; i++) {
    if (expand) children[i].classList.add('open');
    else children[i].classList.remove('open');
    arrows[i].innerText = expand ? '▾' : '▸';
  }
}
</script>
</body>
</html>
