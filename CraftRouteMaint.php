<?php
/* 产品工艺路线维护 —— 操作老项目表 bom_routings_all
 * 左侧：物料大类 → 成品/半成品 树（对齐 BOM管理 左侧树风格）
 * 右侧：该物料的工序明细（可增删改/排序/保存）
 */
include('includes/session.inc');
$Title = _('产品工艺路线');

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

$item = isset($_GET['item']) ? trim($_GET['item']) : (isset($_POST['item']) ? trim($_POST['item']) : '');
$feedback = '';

if (isset($_POST['action']) && $item != '') {
    $act = $_POST['action'];
    $uid = DB_escape_string($_SESSION['UserID']);
    $now = time();
    if ($act == 'add') {
        $mx = DB_query("SELECT MAX(operation_seq_num) m FROM bom_routings_all WHERE assembly_item_no='" . DB_escape_string($item) . "' AND disable_date IS NULL", $db);
        $mxr = DB_fetch_array($mx); $next = ($mxr['m'] ? $mxr['m'] : 0) + 1;
        $fr = DB_query("SELECT operation_code FROM bom_parameters ORDER BY operation_id LIMIT 1", $db);
        $fcode = ($fr && $f = DB_fetch_array($fr)) ? $f['operation_code'] : '';
        DB_query("INSERT INTO bom_routings_all (assembly_item_no, route_status, operation_seq_num, operation_code,
                  creation_date, created_by, last_update_date, last_updated_by, rate, channeng, renli, remarks, approve_date, approved_by)
                  VALUES ('" . DB_escape_string($item) . "','待签核',$next,'" . DB_escape_string($fcode) . "',
                  $now,'$uid',$now,'$uid',0,'','','',0,'')", $db);
        $feedback = '已新增工序行';
    } elseif ($act == 'del') {
        $rid = (int)$_POST['rid'];
        DB_query("UPDATE bom_routings_all SET disable_date=$now WHERE route_id=$rid AND assembly_item_no='" . DB_escape_string($item) . "'", $db);
        $feedback = '已删除该工序行';
    } elseif ($act == 'move') {
        $rid = (int)$_POST['rid'];
        $dir = ($_POST['dir'] == 'up') ? -1 : 1;
        $cur = DB_fetch_array(DB_query("SELECT operation_seq_num s FROM bom_routings_all WHERE route_id=$rid", $db));
        $s = $cur['s'];
        $nb = DB_query("SELECT route_id, operation_seq_num s FROM bom_routings_all
                        WHERE assembly_item_no='" . DB_escape_string($item) . "' AND disable_date IS NULL
                        AND operation_seq_num=" . ($s + $dir) . " LIMIT 1", $db);
        if ($nb = DB_fetch_array($nb)) {
            DB_query("UPDATE bom_routings_all SET operation_seq_num=$s WHERE route_id=" . $nb['route_id'], $db);
            DB_query("UPDATE bom_routings_all SET operation_seq_num=" . $nb['s'] . " WHERE route_id=$rid", $db);
            $feedback = '已调整顺序';
        }
    } elseif ($act == 'save') {
        foreach ($_POST['rid'] as $k => $rid) {
            $rid  = (int)$rid;
            $seq  = (int)$_POST['seq'][$k];
            $op   = DB_escape_string($_POST['op'][$k]);
            $rate = is_numeric($_POST['rate'][$k]) ? (double)$_POST['rate'][$k] : 0;
            $ch   = DB_escape_string($_POST['ch'][$k]);
            $ren  = DB_escape_string($_POST['ren'][$k]);
            $rem  = DB_escape_string($_POST['rem'][$k]);
            DB_query("UPDATE bom_routings_all SET operation_seq_num=$seq, operation_code='$op', rate=$rate,
                      channeng='$ch', renli='$ren', remarks='$rem', last_update_date=$now, last_updated_by='$uid'
                      WHERE route_id=$rid AND assembly_item_no='" . DB_escape_string($item) . "'", $db);
        }
        $feedback = '工艺路线已保存';
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

// 右侧：选中物料的工序明细
$steps = array();
$routeStatus = '';
$activeItemName = '';
$activeItemType = '';
if ($item != '') {
    $rq = DB_query("SELECT * FROM bom_routings_all WHERE assembly_item_no='" . DB_escape_string($item) . "'
                    AND disable_date IS NULL ORDER BY operation_seq_num", $db);
    while ($s = DB_fetch_array($rq)) $steps[] = $s;
    if (count($steps) > 0) $routeStatus = $steps[0]['route_status'];
    if (isset($itemMap[$item])) {
        $activeItemName = $itemMap[$item]['item_name'];
        $activeItemType = $itemMap[$item]['item_type'];
    }
}

// 工序下拉
$ops = array();
$oq = DB_query("SELECT operation_code, operation_name FROM bom_parameters ORDER BY operation_id", $db);
while ($o = DB_fetch_array($oq)) $ops[] = $o;

// 默认只展开包含当前选中物料的分类
$activeCat = null;
if ($item != '') {
    foreach ($cats as $cKey => $cItems) {
        foreach ($cItems as $cIt) {
            if ($cIt['item_no'] == $item) { $activeCat = $cKey; break 2; }
        }
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
        <div class="tech-empty-tip">请从左侧选择成品或半成品，查看 / 维护其工艺路线。</div>
      <?php } else {
        $st = ($routeStatus == '已签核') ? '<span class="tag tag-green">已签核</span>' : (($routeStatus == '待签核') ? '<span class="tag tag-orange">待签核</span>' : '<span class="tag tag-gray">未设置</span>');
        ?>
        <div class="tech-right-head">
          <h3>
            <span class="item-icon" style="display:inline-flex;vertical-align:middle;margin-right:4px;"><?php echo craftItemIcon($activeItemType, 'lg');?></span>
            物料：<?php echo htmlspecialchars($item . ($activeItemName ? ' / ' . $activeItemName : ''));?>
          </h3>
          <div class="meta">状态：<?php echo $st;?></div>
        </div>

        <form id="routeForm" method="post" action="?item=<?php echo urlencode($item);?>">
          <input type="hidden" name="FormID" value="<?php echo htmlspecialchars($_SESSION['FormID']);?>">
          <input type="hidden" name="item" value="<?php echo htmlspecialchars($item);?>">
          <input type="hidden" name="action" value="">
          <input type="hidden" name="rid" value="">
          <input type="hidden" name="dir" value="">

          <div class="tech-toolbar">
            <span class="toolbar-title">工艺工序</span>
            <div class="toolbar-actions">
              <button type="button" class="btn btn-success btn-sm" onclick="doAction('add')">+ 新增工序</button>
              <button type="button" class="btn btn-primary btn-sm" onclick="doAction('save')">保存工艺</button>
            </div>
          </div>

          <div class="tech-panel-wrap">
            <table class="tech-panel-table tech-edit-table" style="min-width:860px;">
              <thead>
                <tr>
                  <th style="width:60px;">序号</th>
                  <th style="min-width:280px;">工序</th>
                  <th style="width:80px;">工时</th>
                  <th style="width:80px;">产能</th>
                  <th style="width:100px;">人力</th>
                  <th style="min-width:160px;">备注</th>
                  <th style="width:100px;">操作</th>
                </tr>
              </thead>
              <tbody>
              <?php if (count($steps) == 0) { ?>
                <tr class="empty-row"><td colspan="7">该物料暂无工艺路线，点击「新增工序」添加。</td></tr>
              <?php } else {
                foreach ($steps as $s) { ?>
                <tr>
                  <td><input type="hidden" name="rid[]" value="<?php echo $s['route_id'];?>"><input type="number" class="seq-input" name="seq[]" value="<?php echo $s['operation_seq_num'];?>"></td>
                  <td class="left">
                    <select name="op[]">
                      <?php foreach ($ops as $o) { $sel = ($o['operation_code'] == $s['operation_code']) ? ' selected' : ''; echo '<option value="' . htmlspecialchars($o['operation_code']) . '"' . $sel . '>' . htmlspecialchars($o['operation_code'] . ' - ' . $o['operation_name']) . '</option>'; } ?>
                    </select>
                  </td>
                  <td><input type="number" step="0.01" name="rate[]" value="<?php echo htmlspecialchars($s['rate']);?>"></td>
                  <td><input type="text" name="ch[]" value="<?php echo htmlspecialchars($s['channeng']);?>"></td>
                  <td><input type="text" name="ren[]" value="<?php echo htmlspecialchars($s['renli']);?>"></td>
                  <td><input type="text" name="rem[]" value="<?php echo htmlspecialchars($s['remarks']);?>"></td>
                  <td>
                    <div class="row-actions">
                      <button type="button" class="btn btn-sm btn-default" title="上移" onclick="doAction('move','<?php echo $s['route_id'];?>','up')">↑</button>
                      <button type="button" class="btn btn-sm btn-default" title="下移" onclick="doAction('move','<?php echo $s['route_id'];?>','down')">↓</button>
                      <button type="button" class="btn btn-sm btn-danger" title="删除" onclick="if(confirm('确定删除该工序行？')) doAction('del','<?php echo $s['route_id'];?>')">×</button>
                    </div>
                  </td>
                </tr>
                <?php }
              } ?>
              </tbody>
            </table>
          </div>
        </form>
      <?php } ?>
    </div>
  </div>

</div>
<script>
function doAction(act, rid, dir){
  var f = document.getElementById('routeForm');
  f.action.value = act;
  f.rid.value = rid || '';
  f.dir.value = dir || '';
  f.submit();
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

