<?php
ob_start();
include('includes/session.inc');
// 是否为 AJAX 局部刷新请求（只返回右侧面板片段，避免整页重载重建左侧树）
$isAjax = (isset($_GET['ajax']) && $_GET['ajax'] === '1');
$Title = _('BOM建立');
$ViewTopic = 'BOM建立';
$BookMark = 'BOM建立';

/* ============================================================
 * 辅助函数
 * ============================================================ */
function esc($db, $v) {
    if (is_object($db) && method_exists($db, 'real_escape_string')) {
        return mysqli_real_escape_string($db, $v);
    }
    if (function_exists('mysql_real_escape_string')) {
        return mysql_real_escape_string($v, $db);
    }
    return addslashes($v);
}

// 取某母件最新版本的 BOM 头（与 SearchBOMDetail 取 max(bom_header_id) 一致）
// 业务约束：**原材料（M）不允许有 BOM 头**（但可被引用为子件）。M 物料永远返回 null。
function latestHeader($db, $assembly) {
    static $cache = array();
    static $hasCurrentCol = null;  // is_current 字段是否已迁移
    if (isset($cache[$assembly])) return $cache[$assembly];
    // M 原材料不允许有 BOM 结构——直接视为无（防历史脏数据误判）
    $c = DB_query("SELECT item_type FROM sf_item_no WHERE item_no='" . esc($db, $assembly) . "'", $db);
    $crow = DB_fetch_array($c);
    if ($crow && $crow['item_type'] === 'M') { $cache[$assembly] = false; return false; }
    // 优先 is_current=1（默认版本，供未锁定引用跟随）；无标记则按 bom_header_id DESC（最晚创建）
    if ($hasCurrentCol === null) {
        $chkCol = DB_query("SHOW COLUMNS FROM bom_headers_all LIKE 'is_current'", $db);
        $hasCurrentCol = DB_fetch_array($chkCol) ? 1 : 0;
    }
    if ($hasCurrentCol) {
        $sql = "SELECT bom_header_id, version, status, cost_price, approve_by, approve_remark, is_current
                FROM bom_headers_all WHERE assembly_item_no='" . esc($db, $assembly) . "'
                ORDER BY is_current DESC, bom_header_id DESC LIMIT 1";
    } else {
        $sql = "SELECT bom_header_id, version, status, cost_price, approve_by, approve_remark
                FROM bom_headers_all WHERE assembly_item_no='" . esc($db, $assembly) . "'
                ORDER BY bom_header_id DESC LIMIT 1";
    }
    $r = DB_query($sql, $db);
    $cache[$assembly] = DB_fetch_array($r);
    return $cache[$assembly];
}

// 按版本取 BOM 头：$version 为空 → 最新版本；否则精确取指定版本
function headerByVersion($db, $assembly, $version) {
    if ($version === '' || $version === null) return latestHeader($db, $assembly);
    $r = DB_query("SELECT bom_header_id, version, status, cost_price, approve_by, approve_remark
            FROM bom_headers_all WHERE assembly_item_no='" . esc($db, $assembly) . "'
            AND version='" . esc($db, $version) . "' LIMIT 1", $db);
    return DB_fetch_array($r);
}

// 按 bom_header_id 取 BOM 头（行级版本锁定用）
function headerById($db, $id) {
    if (!$id) return false;
    static $cache = array();
    if (isset($cache[$id])) return $cache[$id];
    $r = DB_query("SELECT bom_header_id, version, status, cost_price, approve_by, approve_remark
            FROM bom_headers_all WHERE bom_header_id='" . intval($id) . "' LIMIT 1", $db);
    $cache[$id] = DB_fetch_array($r);
    return $cache[$id];
}

// 子件行版本解析：行锁定(component_bom_header_id)优先，NULL 或锁定头已不存在 → 回退 latestHeader(默认版本)
function resolveChildHeader($db, $line) {
    $lock = isset($line['component_bom_header_id']) ? intval($line['component_bom_header_id']) : 0;
    if ($lock > 0) {
        $hdr = headerById($db, $lock);
        if ($hdr) return $hdr;
    }
    return latestHeader($db, $line['component_item']);
}

// 某物料所有 BOM 版本列表（供版本切换下拉）
function allVersions($db, $assembly) {
    $out = array();
    $r = DB_query("SELECT bom_header_id, version, status, approve_by, approve_remark, approve_date, creation_date, is_current
            FROM bom_headers_all WHERE assembly_item_no='" . esc($db, $assembly) . "'
            ORDER BY bom_header_id", $db);
    while ($row = DB_fetch_array($r)) { $out[] = $row; }
    return $out;
}

// 面包屑/上层查询已废弃（用户要求删去上层BOM提示，改以图标星标区分顶层），相关函数已移除以免死代码


// 取某 BOM 头下所有有效子件行
function getActiveLines($db, $bom_header_id) {
    static $cache = array();
    if (isset($cache[$bom_header_id])) return $cache[$bom_header_id];
    $sql = "SELECT component_sequence_id, component_item, item_num, component_quantity,
                   sunhao_rate, weizhi, operation_seq_num, component_remarks, effectivity_date,
                   component_bom_header_id
            FROM bom_lines_all WHERE bom_header_id='" . esc($db, $bom_header_id) . "'
            AND disable_date=0 ORDER BY item_num";
    $r = DB_query($sql, $db);
    $lines = array();
    while ($row = DB_fetch_array($r)) { $lines[] = $row; }
    $cache[$bom_header_id] = $lines;
    return $lines;
}

function itemName($db, $item_no) {
    static $cache = array();
    if (isset($cache[$item_no])) return $cache[$item_no];
    $sql = "SELECT item_name FROM sf_item_no WHERE item_no='" . esc($db, $item_no) . "'";
    $r = DB_query($sql, $db);
    $row = DB_fetch_array($r);
    $cache[$item_no] = $row ? $row['item_name'] : '';
    return $cache[$item_no];
}

function itemType($db, $item_no) {
    static $cache = array();
    if (isset($cache[$item_no])) return $cache[$item_no];
    $sql = "SELECT item_type FROM sf_item_no WHERE item_no='" . esc($db, $item_no) . "'";
    $r = DB_query($sql, $db);
    $row = DB_fetch_array($r);
    $cache[$item_no] = $row ? $row['item_type'] : '';
    return $cache[$item_no];
}

// BOM 树相关样式（弹出框加载用）。原在主页面 <style> 段里，引用BOM 弹窗需要单独加载
function getBomTreeStyles() {
    return '
.bom-tree,.bom-tree ul{list-style:none;margin:0;padding:0}
.bom-tree{font-size:12px;line-height:22px;color:#333;user-select:none}
.bom-tree ul.bom-sub{margin:0;padding:0}
.bom-node{display:flex;flex-direction:column;min-width:0}
.bom-row{display:flex;align-items:center;gap:0;cursor:pointer;padding:1px 4px;white-space:nowrap}
.bom-row:hover{background:#eef4fb}
.bom-glyphs{display:flex;align-items:center;flex:0 0 auto;min-width:20px;height:22px;position:relative}
.tree-cell{position:relative;display:inline-flex;align-items:center;justify-content:center;width:16px;height:22px}
.tree-vbar{position:absolute;top:0;bottom:0;left:50%;width:1px;background:#999}
.tree-hbar{position:absolute;left:0;right:0;top:50%;height:1px;background:#999}
.tree-cell.no-sibling .tree-vbar{display:none}
.icon-label{display:inline-flex;align-items:center;gap:0;flex:0 0 auto;margin-left:2px}
.item-icon{flex:0 0 auto;width:16px;height:16px;margin:0 2px 0 0;vertical-align:middle;display:inline-flex;align-items:center;justify-content:center}
.item-icon .bom-svg{width:16px;height:16px;display:block}
.bom-row .item-icon{margin:0 2px 0 0;padding:0;display:inline-flex;width:16px;height:16px;flex:0 0 auto}
.bom-row .item-icon.icon-lg{width:16px;height:16px}
.bom-row .item-icon .bom-svg{width:16px;height:16px;display:block}
.bom-node.leaf-node>.bom-row>.lbl{color:#555}
.hier-indent{display:inline-block;width:16px;vertical-align:middle}
.lbl{font-size:12px;color:#222;cursor:pointer}
.lbl:hover{color:#0d47a1}
.tw{position:absolute;right:2px;top:50%;transform:translateY(-50%);width:11px;height:11px;line-height:9px;border:1px solid #808890;background:#fff;font-size:9px;text-align:center;cursor:pointer;color:#333;z-index:2;font-family:"Courier New",monospace}
.leaf-node .node-cell .tw{display:none}
input[type="checkbox"]{margin:0 4px;cursor:pointer}
.icon-raw{color:#4caf50}.icon-half{color:#ff9800}.icon-finish{color:#2196F3}.icon-pack{color:#e91e63}.icon-other{color:#9e9e9e}
';
}

// 取物料主数据完整信息
function getItemInfo($db, $item_no) {
    static $cache = array();
    if (isset($cache[$item_no])) return $cache[$item_no];
    $sql = "SELECT * FROM sf_item_no WHERE item_no='" . esc($db, $item_no) . "'";
    $r = DB_query($sql, $db);
    $row = DB_fetch_array($r);
    if ($row) {
        // DB_fetch_array 默认同时返回数字索引和字段名索引，只保留字段名索引
        $clean = array();
        foreach ($row as $k => $v) {
            if (is_int($k)) continue;
            $clean[$k] = $v;
        }
        $cache[$item_no] = $clean;
    } else {
        $cache[$item_no] = false;
    }
    return $cache[$item_no];
}

// 物料类型中文名
function itemTypeName($t) {
    switch ($t) {
        case 'M': return '原材料';
        case 'B': return '半成品';
        case 'F': return '成品';
        default:  return $t;
    }
}

// 用途中文名
function itemUseName($u) {
    switch ($u) {
        case 'S': return '生产';
        case 'Y': return '研发';
        default:  return $u;
    }
}

// 统一图标：成品=箱子，半成品=六角螺钉，原材料=齿轮，包装物=礼盒
// 用内联 SVG 替代 emoji，且每个图形 path 严格填满 viewBox 0-16 范围，
// 避免 SVG 内部留白造成图标和文字之间的视觉间隙。
// $top=true 时在 SVG 右上角叠加黄色五角星角标，用于左侧 BOM 结构树的顶层节点。
function itemIcon($item_type, $size = 'sm', $top = false, $approved = null) {
    // 顶层右上角星标（黄底白星），半径 2.5 紧贴右上角
    $starPath = '<circle cx="13" cy="3" r="2.5" fill="#FBC02D" stroke="#fff" stroke-width="0.4"/>'
              . '<path d="M13 1.5 L13.5 2.6 L14.7 2.7 L13.8 3.5 L14.1 4.7 L13 4.05 L11.9 4.7 L12.2 3.5 L11.3 2.7 L12.5 2.6 Z" fill="#fff"/>';

    switch ($item_type) {
        case 'F': // 成品：立方体箱子（图形占 0.5-15.5 满 viewBox）
            $base = '<path d="M8 0.5 L15.5 4 L15.5 13 L8 16.5 L0.5 13 L0.5 4 Z" fill="#42A5F5" stroke="#1976D2" stroke-width="0.6"/>'
                  . '<path d="M0.5 4 L8 7.5 L15.5 4 M8 7.5 L8 16.5" fill="none" stroke="#1976D2" stroke-width="0.6"/>'
                  . '<path d="M2.5 5.5 L6.5 7.5" stroke="#fff" stroke-width="0.5" opacity="0.55"/>';
            $title = '成品';
            break;
        case 'B': // 半成品：六角头螺钉（头部横向占满 0.5-15.5）
            $base = '<polygon points="0.5,5 4,1 12,1 15.5,5 15.5,6.2 12,10.2 4,10.2 0.5,6.2" fill="#37474F" stroke="#1c1c1c" stroke-width="0.5"/>'
                  . '<rect x="1.5" y="4.9" width="13" height="0.9" fill="#0d0d0d"/>'
                  . '<rect x="6" y="10.2" width="4" height="4.5" fill="#546E7A" stroke="#1c1c1c" stroke-width="0.5"/>'
                  . '<path d="M6 11.2 L10 11.5 L10 11.9 L6 11.6 Z" fill="#1c1c1c"/>'
                  . '<path d="M6 12.5 L10 12.8 L10 13.2 L6 12.9 Z" fill="#1c1c1c"/>'
                  . '<path d="M6 13.8 L10 14.1 L10 14.5 L6 14.2 Z" fill="#1c1c1c"/>'
                  . '<polygon points="6,14.7 10,14.7 8,16" fill="#37474F" stroke="#1c1c1c" stroke-width="0.5"/>';
            $title = '半成品';
            break;
        case 'M': // 原材料：齿轮（8 齿填满 0-16）
            $base = '<path d="M8 0.3 L9.3 2.5 L11.7 2 L12.3 4.4 L14.7 4.7 L13.8 7.5 L15.7 8 L13.8 8.5 L14.7 11.3 L12.3 11.6 L11.7 14 L9.3 13.5 L8 15.7 L6.7 13.5 L4.3 14 L3.7 11.6 L1.3 11.3 L2.2 8.5 L0.3 8 L2.2 7.5 L1.3 4.7 L3.7 4.4 L4.3 2 L6.7 2.5 Z" fill="#FFA726" stroke="#F57C00" stroke-width="0.5"/>'
                  . '<circle cx="8" cy="8" r="2.5" fill="#fff" stroke="#F57C00" stroke-width="0.5"/>';
            $title = '原材料';
            break;
        default: // 未知：灰色圆点（占 1-15）
            $base = '<circle cx="8" cy="8" r="7" fill="#9E9E9E" stroke="#616161" stroke-width="0.6"/>';
            $title = '其他';
    }

    $inner = $base . ($top ? $starPath : '');
    $px = ($size === 'lg') ? 16 : 14;
    $cls = 'item-icon icon-' . $size . ($top ? ' top-icon' : '');
    $titleFull = $title . ($top ? '（顶层）' : '');
    // 审核状态深浅：已审核=全色（深）；未审核/待签核=半透明（浅）；null（叶子/无BOM）=全色
    $opacityCss = '';
    if ($approved === 1) { $opacityCss = 'opacity:1 !important;'; }
    elseif ($approved === 0) { $opacityCss = 'opacity:0.4 !important;'; }

    // 直接返回 SVG（无外层 span），SVG 作为 replaced element，width/height 属性强制生效。
// display:inline-block 是关键：左侧 BOM 树（.icon-label flex）中作为 inline-block flex item，
// 右侧 BOM 层级表（td 内联流）中作为 inline-block 与 .hier-code span 水平并排（不会被 td 强制换行）。
// 内联样式（含 !important）：display:inline-block + 固定尺寸 + 零边距，强制最高优先级
    return '<svg viewBox="0 0 16 16" width="' . $px . '" height="' . $px . '" class="bom-svg ' . $cls . '" title="' . $titleFull . '" aria-hidden="true" style="display:inline-block !important;width:' . $px . 'px !important;height:' . $px . 'px !important;min-width:' . $px . 'px !important;max-width:' . $px . 'px !important;margin:0 !important;padding:0 !important;line-height:0 !important;font-size:0 !important;vertical-align:middle !important;flex:0 0 auto;' . $opacityCss . '">'
         . $inner
         . '</svg>';
}

// 是否原材料（最小规格原料）：item_type='M' 视为叶子，禁止在其下建 BOM
function isRaw($db, $item_no) {
    static $cache = array();
    if (isset($cache[$item_no])) return $cache[$item_no];
    $sql = "SELECT item_type FROM sf_item_no WHERE item_no='" . esc($db, $item_no) . "'";
    $r = DB_query($sql, $db);
    $row = DB_fetch_array($r);
    $cache[$item_no] = ($row && $row['item_type'] == 'M');
    return $cache[$item_no];
}

// 循环引用检测
function createsCycle($db, $root, $target, $visited = array()) {
    if ($root == $target) return true;
    if (in_array($root, $visited, true)) return false;
    $visited[] = $root;
    $hdr = latestHeader($db, $root);
    if (!$hdr) return false;
    foreach (getActiveLines($db, $hdr['bom_header_id']) as $ln) {
        if (createsCycle($db, $ln['component_item'], $target, $visited)) return true;
    }
    return false;
}

// 递归级联删除 BOM（不删除料号主数据）
function deleteBOMRecursive($db, $assembly, $visited = array()) {
    if (in_array($assembly, $visited, true)) return;
    $visited[] = $assembly;
    $hdr = latestHeader($db, $assembly);
    if (!$hdr) return;
    $hid = $hdr['bom_header_id'];
    $lines = getActiveLines($db, $hid);
    $childAsms = array();
    foreach ($lines as $ln) {
        DB_query("DELETE FROM bom_substitutes_all WHERE component_sequence_id='" . esc($db, $ln['component_sequence_id']) . "'", $db);
        if (latestHeader($db, $ln['component_item'])) $childAsms[] = $ln['component_item'];
    }
    DB_query("DELETE FROM bom_lines_all WHERE bom_header_id='" . esc($db, $hid) . "'", $db);
    // 清理引用该头的父行版本绑定（该头删除后绑定悬空，置 NULL 由解析回退到该子件剩余版本）
    DB_query("UPDATE bom_lines_all SET component_bom_header_id=NULL, last_update_date='" . time() . "' WHERE component_bom_header_id='" . esc($db, $hid) . "'", $db);
    DB_query("DELETE FROM bom_headers_all WHERE bom_header_id='" . esc($db, $hid) . "'", $db);
    foreach (array_unique($childAsms) as $ca) {
        // 仅当子 BOM 不再被任何 BOM 行引用时才递归删除（避免误删被其他 BOM 共享的子 BOM）
        $ref = DB_query("SELECT 1 FROM bom_lines_all WHERE component_item='" . esc($db, $ca) . "' AND disable_date=0 LIMIT 1", $db);
        if (!DB_fetch_array($ref)) {
            deleteBOMRecursive($db, $ca, $visited);
        }
    }
}

// 参考物料级联引用：插入 srcAsm 行，并按其最新版本 BOM 递归展开子件（成品/半成品才展开，原材料只插自身）
// $curBomHdrId   当前母件 BOM 头 id（行要插入的目标）
// $curBomAsm     当前母件料号（用于行 assembly_item_no）
// $qty           父路径累计用量（外层用量 * 子件原始用量）
// $visited       防循环：已插入的物料集合
function expandInsertBomRef($db, $srcAsm, $curBomHdrId, $curBomAsm, $qty, $op_seq, $weizhi, $sunhao, $remark, $visited, $t, $uid){
    // 1) 插入 srcAsm 子件行（子件版本绑定：有 BOM 头则绑其最新版本，叶子/原材料绑 NULL）
    $bindH = latestHeader($db, $srcAsm);
    $bindVal = $bindH ? "'" . esc($db, $bindH['bom_header_id']) . "'" : 'NULL';
    $r = DB_query("SELECT MAX(item_num)+1 AS n FROM bom_lines_all WHERE bom_header_id='" . esc($db, $curBomHdrId) . "'", $db);
    $row = DB_fetch_array($r);
    $item_num = $row['n'] ? $row['n'] : 1;
    DB_query("INSERT INTO bom_lines_all(bom_header_id,assembly_item_no,item_num,operation_seq_num,
        component_item,component_bom_header_id,weizhi,component_quantity,sunhao_rate,component_remarks,effectivity_date,
        creation_date,created_by,last_update_date,last_updated_by)
        VALUES('" . esc($db, $curBomHdrId) . "','" . esc($db, $curBomAsm) . "','" . esc($db, $item_num) . "','" . esc($db, $op_seq) . "',
        '" . esc($db, $srcAsm) . "'," . $bindVal . ",'" . esc($db, $weizhi) . "','" . esc($db, $qty) . "','" . esc($db, $sunhao) . "','" . esc($db, $remark) . "','" . $t . "',
        '" . $t . "','" . esc($db, $uid) . "','" . $t . "','" . esc($db, $uid) . "')", $db);

    // 2) 查 srcAsm 是否是成品/半成品（原材料或无 BOM 头：不展开）
    $tmpRef = DB_query("SELECT item_type FROM sf_item_no WHERE item_no='" . esc($db, $srcAsm) . "'", $db);
    $irow = DB_fetch_array($tmpRef);
    if (!$irow || ($irow['item_type'] !== 'F' && $irow['item_type'] !== 'B')) return;
    $srcHdr = latestHeader($db, $srcAsm);
    if (!$srcHdr) return;

    // 3) 递归展开 srcAsm 头下的子件（数量相乘；所有展开行用同一组统一字段）
    $r = DB_query("SELECT component_item, component_quantity FROM bom_lines_all
        WHERE bom_header_id='" . esc($db, $srcHdr['bom_header_id']) . "' AND disable_date=0", $db);
    while ($line = DB_fetch_array($r)) {
        $child = $line['component_item'];
        if (in_array($child, $visited)) continue;  // 防循环
        $visited[] = $child;
        $newQty = $qty * (float)$line['component_quantity'];
        expandInsertBomRef($db, $child, $curBomHdrId, $curBomAsm, $newQty, $op_seq, $weizhi, $sunhao, $remark, $visited, $t, $uid);
    }
}

// 统计物料被多少个 BOM 头作为子件引用（缓存避免重复查询）。0 = 无引用，>=1 = 被父 BOM 引用
function refCount($db, $item_no) {
    static $cache = array();
    if (isset($cache[$item_no])) return $cache[$item_no];
    $r = DB_query("SELECT COUNT(DISTINCT bom_header_id) AS c FROM bom_lines_all
        WHERE component_item='" . esc($db, $item_no) . "' AND disable_date=0", $db);
    $row = DB_fetch_array($r);
    $cache[$item_no] = $row ? (int)$row['c'] : 0;
    return $cache[$item_no];
}

// 字段中文标签
function fieldLabel($f) {
    static $map = array(
        'item_no'=>'物料编码','item_name'=>'物料名称','item_desc'=>'规格型号','units'=>'单位',
        'item_category1'=>'分类','item_type'=>'类型','item_use'=>'用途','project_name'=>'项目名',
        'inspect_flag'=>'检验标志','gongyi'=>'工艺','min_order'=>'最小订购量','unit_price'=>'单价',
        'safe_qty'=>'安全库存','min_qty'=>'最小库存','max_qty'=>'最大库存','huohao'=>'货号',
        'manufacture_time'=>'制造周期','lead_time'=>'提前期','franchise_price'=>'加盟价',
        'po_price'=>'采购价','zhidao_price'=>'指导价','so_flag'=>'可售标志','sub_code'=>'仓库',
        'sub_locator'=>'库位','conditions'=>'状态','wendu'=>'温度','light'=>'光照','shidu'=>'湿度',
        'suoding_flag'=>'锁定','suoding_remark'=>'锁定备注','item_remark'=>'备注','youxiaoqi'=>'有效期',
        'disable_flag'=>'停用','item_status'=>'物料状态','pic_path'=>'图片路径','created_by'=>'创建人',
        'creation_date'=>'创建日期','last_updated_by'=>'最后更新人','last_update_date'=>'最后更新日期'
    );
    return isset($map[$f]) ? $map[$f] : $f;
}

/* ============================================================
 * POST 操作处理（在输出任何 HTML 之前完成）
 * ============================================================ */
$postOp = isset($_POST['op']) ? $_POST['op'] : '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $postOp != '') {

    $err = '';
    DB_Txn_Begin($db);
    try {
        if ($postOp == 'ref_save') {
            $assembly = $_POST['assembly'];
            $version  = $_POST['version'];
            // 批量物料（component_item[] 数组）；兼容单 item
            $components  = isset($_POST['component_item']) ? (array)$_POST['component_item'] : array();
            $componentQtys = isset($_POST['component_quantity']) ? (array)$_POST['component_quantity'] : array();
            $componentOpSeqs = isset($_POST['operation_seq_num']) ? (array)$_POST['operation_seq_num'] : array();
            $componentSunhaos = isset($_POST['sunhao_rate']) ? (array)$_POST['sunhao_rate'] : array();
            $componentPos = isset($_POST['weizhi']) ? (array)$_POST['weizhi'] : array();
            $componentRemarks = isset($_POST['component_remarks']) ? (array)$_POST['component_remarks'] : array();
            $err = '';
            if (count($components) == 0) { $err = '请选择要引用的物料！'; }
            else {
                foreach ($components as $idx => $c) {
                    $cQty = isset($componentQtys[$idx]) ? $componentQtys[$idx] : 1;
                    if ($cQty == '' || !is_numeric($cQty) || $cQty <= 0) { $err = '用量必须大于 0（第 ' . ($idx+1) . ' 项）！'; break; }
                    if ($c == $assembly) { $err = '不能引用母件自身（' . htmlspecialchars($c) . '）！'; break; }
                    if (createsCycle($db, $c, $assembly)) { $err = '不能引用自身或其上层 BOM（' . htmlspecialchars($c) . '），会造成循环引用！'; break; }
                }
            }
            if ($err == '') {
                // 已审核 BOM 完全冻结：不允许引用新物料（与删除/编辑/改用量一致，都走"复制BOM"建新版本）
                $phdrChk = latestHeader($db, $assembly);
                if ($phdrChk && $phdrChk['status'] == '已审核') {
                    $err = '该 BOM（v' . htmlspecialchars($phdrChk['version']) . '）已审核，不允许引用新物料！请通过 "复制BOM" 创建新版本后修改。';
                }
            }
            if ($err == '') {
                $hdr = latestHeader($db, $assembly);
                if (!$hdr) {
                    // 母件尚无 BOM 头（叶子节点引用场景），自动创建默认 BOM 头便于立即添加子件
                    // 业务约束：**原材料（M）不允许有 BOM 头**（但可被引用为子件加入 bom_lines）
                    $chkType = DB_query("SELECT item_type FROM sf_item_no WHERE item_no='" . esc($db, $assembly) . "'", $db);
                    $chkTypeRow = DB_fetch_array($chkType);
                    if ($chkTypeRow && $chkTypeRow['item_type'] === 'M') {
                        $err = '原材料（M 类）不允许建立 BOM 结构！';
                    } else {
                        $r = DB_query("SELECT 1 FROM bom_headers_all WHERE assembly_item_no='" . esc($db, $assembly) . "' AND version='" . esc($db, $version) . "'", $db);
                        if (DB_fetch_array($r)) { $err = '该物料已有 BOM（v' . htmlspecialchars($version) . '）！'; }
                        else {
                            $t = time();
                            DB_query("INSERT INTO bom_headers_all(assembly_item_no,version,status,approve_by,approve_date,approve_remark,creation_date,created_by,last_update_date,last_updated_by) VALUES('" . esc($db, $assembly) . "','" . esc($db, $version) . "','已签核','" . esc($db, $_SESSION['UserID']) . "','" . $t . "','引用物料时自动创建','" . $t . "','" . esc($db, $_SESSION['UserID']) . "','" . $t . "','" . esc($db, $_SESSION['UserID']) . "')", $db);
                            $hdr = latestHeader($db, $assembly);
                            if (!$hdr) { $err = '自动创建母件 BOM 头失败！'; }
                        }
                    }
                }
            }
            if ($err == '') {
                $t = time(); $uid = $_SESSION['UserID'];
                $curBomHdrId = $hdr['bom_header_id'];
                // 引用保存：每个物料只插一行（component_item=物料，qty 用户设定），不展开其 BOM。
                // 子件版本绑定：子件有 BOM 头则绑其最新版本，叶子/原材料绑 NULL（渲染走行绑定，不再全局跟随）
                foreach ($components as $idx => $comp) {
                    $qty = $componentQtys[$idx] !== '' ? (float)$componentQtys[$idx] : 1;
                    $op_seq = $componentOpSeqs[$idx] != '' ? $componentOpSeqs[$idx] : 1;
                    $sunhao = $componentSunhaos[$idx] != '' ? (float)$componentSunhaos[$idx] : 0;
                    $weizhi = isset($componentPos[$idx]) ? $componentPos[$idx] : '';
                    $remark = isset($componentRemarks[$idx]) ? $componentRemarks[$idx] : '';
                    $bindH = latestHeader($db, $comp);
                    $bindVal = $bindH ? "'" . esc($db, $bindH['bom_header_id']) . "'" : 'NULL';
                    $rq = DB_query("SELECT MAX(item_num)+1 AS n FROM bom_lines_all WHERE bom_header_id='" . esc($db, $curBomHdrId) . "'", $db);
                    $rrow = DB_fetch_array($rq);
                    $item_num = $rrow['n'] ? $rrow['n'] : 1;
                    DB_query("INSERT INTO bom_lines_all(bom_header_id,assembly_item_no,item_num,operation_seq_num,component_item,component_bom_header_id,weizhi,component_quantity,sunhao_rate,component_remarks,effectivity_date,creation_date,created_by,last_update_date,last_updated_by) VALUES('" . esc($db, $curBomHdrId) . "','" . esc($db, $assembly) . "','" . esc($db, $item_num) . "','" . esc($db, $op_seq) . "','" . esc($db, $comp) . "'," . $bindVal . ",'" . esc($db, $weizhi) . "','" . esc($db, $qty) . "','" . esc($db, $sunhao) . "','" . esc($db, $remark) . "','" . $t . "','" . $t . "','" . esc($db, $uid) . "','" . $t . "','" . esc($db, $uid) . "')", $db);
                }
                DB_Txn_Commit($db);
                echo "<script>var api=(window.frameElement&&window.frameElement.api)?window.frameElement.api:null,W=api?api.opener:null;(W||window).location.href='BOMSetup.php?view=" . urlencode($assembly) . "';api.close();</script>";
                exit;
            }

        } elseif ($postOp == 'new_save') {
            $assembly = $_POST['assembly'];
            $version  = $_POST['version'];
            $item_no  = trim($_POST['item_no']);
            $item_name= trim($_POST['item_name']);
            $item_desc= trim($_POST['item_desc']);
            $units    = trim($_POST['units']);
            $cat1     = trim($_POST['item_category1']);
            $item_type= $_POST['item_type'];
            $item_use = $_POST['item_use'];
            $project  = trim($_POST['project_name']);
            $inspect  = $_POST['inspect_flag'];
            $gongyi   = trim($_POST['gongyi']);
            $qty      = $_POST['component_quantity'];
            $weizhi   = $_POST['weizhi'];
            $remark   = $_POST['component_remarks'];
            $op_seq   = $_POST['operation_seq_num'] == '' ? 1 : $_POST['operation_seq_num'];
            $sunhao   = $_POST['sunhao_rate'] == '' ? 0 : $_POST['sunhao_rate'];
            if ($item_no == '') { $err = '请填写物料代码！'; }
            elseif ($item_name == '') { $err = '请填写物料名称！'; }
            elseif ($qty == '' || !is_numeric($qty) || $qty <= 0) { $err = '用量必须大于 0！'; }
            else {
                $chk = DB_query("SELECT item_no FROM sf_item_no WHERE item_no='" . esc($db, $item_no) . "'", $db);
                if (DB_num_rows($chk) > 0) { $err = '物料代码已存在！'; }
            }
            if ($err == '') {
                // 已审核 BOM 完全冻结：不允许新建子 BOM（结构修改走"复制BOM"建新版本）
                $phdrChk = latestHeader($db, $assembly);
                if ($phdrChk && $phdrChk['status'] == '已审核') {
                    $err = '该 BOM（v' . htmlspecialchars($phdrChk['version']) . '）已审核，不允许新建子 BOM！请通过 "复制BOM" 创建新版本后修改。';
                }
            }
            if ($err == '') {
                $hdr = latestHeader($db, $assembly);
                if (!$hdr) {
                    // 母件尚无 BOM 头（叶子节点上新建子 BOM 场景）：自动创建默认 BOM 头
                    // 业务约束：**原材料（M）不允许有 BOM 头**（但可被引用为子件加入 bom_lines）
                    $chkType = DB_query("SELECT item_type FROM sf_item_no WHERE item_no='" . esc($db, $assembly) . "'", $db);
                    $chkTypeRow = DB_fetch_array($chkType);
                    if ($chkTypeRow && $chkTypeRow['item_type'] === 'M') {
                        $err = '原材料（M 类）不允许建立 BOM 结构！';
                    } else {
                        $r = DB_query("SELECT 1 FROM bom_headers_all WHERE assembly_item_no='" . esc($db, $assembly) . "' AND version='" . esc($db, $version) . "'", $db);
                        if (DB_fetch_array($r)) { $err = '该物料已有 BOM（v' . htmlspecialchars($version) . '）！'; }
                        else {
                            $t0 = time();
                            DB_query("INSERT INTO bom_headers_all(assembly_item_no,version,status,approve_by,approve_date,approve_remark,creation_date,created_by,last_update_date,last_updated_by) VALUES('" . esc($db, $assembly) . "','" . esc($db, $version) . "','已签核','" . esc($db, $_SESSION['UserID']) . "','" . $t0 . "','新建子BOM时自动创建','" . $t0 . "','" . esc($db, $_SESSION['UserID']) . "','" . $t0 . "','" . esc($db, $_SESSION['UserID']) . "')", $db);
                            $hdr = latestHeader($db, $assembly);
                            if (!$hdr) { $err = '自动创建母件 BOM 头失败！'; }
                        }
                    }
                }
            }
            if ($err == '') {
                $t = time();
                DB_query("INSERT INTO sf_item_no(item_no,item_name,item_desc,units,item_category1,item_type,
                    item_use,project_name,inspect_flag,gongyi,disable_flag,creation_date,created_by,last_update_date,last_updated_by)
                    VALUES('" . esc($db, $item_no) . "','" . esc($db, $item_name) . "','" . esc($db, $item_desc) . "','" . esc($db, $units) . "',
                    '" . esc($db, $cat1) . "','" . esc($db, $item_type) . "','" . esc($db, $item_use) . "','" . esc($db, $project) . "',
                    '" . esc($db, $inspect) . "','" . esc($db, $gongyi) . "','Y','" . $t . "','" . esc($db, $_SESSION['UserID']) . "','" . $t . "','" . esc($db, $_SESSION['UserID']) . "')", $db);
                // 注意：不自动创建新物料的 BOM 头（否则空 BOM 头会让新节点显示折叠符但无子件）。
                // 新物料作为子件引用是叶子；以后在它下面新建子 BOM 时，new_save 的"母件无 BOM 头自动创建"逻辑会建头。
                $r = DB_query("SELECT MAX(item_num)+1 AS n FROM bom_lines_all WHERE bom_header_id='" . esc($db, $hdr['bom_header_id']) . "'", $db);
                $row = DB_fetch_array($r);
                $item_num = $row['n'] ? $row['n'] : 1;
                // 子件版本绑定：已有 BOM 头的物料绑其最新版本，新物料（叶子）绑 NULL
                $bindH = latestHeader($db, $item_no);
                $bindVal = $bindH ? "'" . esc($db, $bindH['bom_header_id']) . "'" : 'NULL';
                DB_query("INSERT INTO bom_lines_all(bom_header_id,assembly_item_no,item_num,operation_seq_num,
                    component_item,component_bom_header_id,weizhi,component_quantity,sunhao_rate,component_remarks,effectivity_date,
                    creation_date,created_by,last_update_date,last_updated_by)
                    VALUES('" . esc($db, $hdr['bom_header_id']) . "','" . esc($db, $assembly) . "','" . esc($db, $item_num) . "','" . esc($db, $op_seq) . "',
                    '" . esc($db, $item_no) . "'," . $bindVal . ",'" . esc($db, $weizhi) . "','" . esc($db, $qty) . "','" . esc($db, $sunhao) . "','" . esc($db, $remark) . "','" . $t . "',
                    '" . $t . "','" . esc($db, $_SESSION['UserID']) . "','" . $t . "','" . esc($db, $_SESSION['UserID']) . "')", $db);
                DB_Txn_Commit($db);
                echo "<script>var api=(window.frameElement&&window.frameElement.api)?window.frameElement.api:null,W=api?api.opener:null;(W||window).location.href='BOMSetup.php?view=" . urlencode($assembly) . "';api.close();</script>";
                exit;
            }

        } elseif ($postOp == 'edit_save') {
            $assembly = $_POST['assembly'];
            $version  = $_POST['version'];
            // 已审核的 BOM 不允许编辑：结构变更要走\"新增版本\"（复制BOM填新版本号）
            if ($assembly) {
                $chkHdr = latestHeader($db, $assembly);
                if ($chkHdr && $chkHdr['status'] == '已审核') {
                    $err = '该 BOM（v' . htmlspecialchars($chkHdr['version']) . '）已审核，不允许直接编辑！请通过 \"复制BOM\" 创建新版本后修改。';
                }
            }
            $cost     = $_POST['cost_price'] == '' ? 'NULL' : "'" . esc($db, $_POST['cost_price']) . "'";
            $hdr = latestHeader($db, $assembly);
            if (!$hdr) { $err = 'BOM 不存在！'; }
            if ($err == '') {
                // 审核字段已独立操作（op=approve），edit 只更新成本 + 最后更新信息
                DB_query("UPDATE bom_headers_all SET cost_price=" . $cost . ",
                    last_update_date='" . time() . "',last_updated_by='" . esc($db, $_SESSION['UserID']) . "'
                    WHERE bom_header_id='" . esc($db, $hdr['bom_header_id']) . "'", $db);
                DB_Txn_Commit($db);
                echo "<script>var api=(window.frameElement&&window.frameElement.api)?window.frameElement.api:null,W=api?api.opener:null;(W||window).location.href='BOMSetup.php?view=" . urlencode($assembly) . "';api.close();</script>";
                exit;
            }

        } elseif ($postOp == 'approve_save') {
            // 独立审核操作：权限占位（任何登录用户可审核，后续可加 role/permission 检查）
            $assembly = $_POST['assembly'];
            $version  = $_POST['version'];
            $status   = $_POST['status'];
            $approve  = $_POST['approve_by'];
            $arem     = $_POST['approve_remark'];
            $apdtInput = isset($_POST['approve_date']) ? trim($_POST['approve_date']) : '';
            $apdtVal = time();
            if ($apdtInput !== '') {
                $ts = strtotime($apdtInput);
                if ($ts !== false && $ts > 0) $apdtVal = $ts;
            }
            // 状态只能是预设值
            $validStatus = array('未审核', '待签核', '已审核', '已拒签');
            if (!in_array($status, $validStatus)) { $err = '状态值无效！'; }
            elseif ($assembly == '') { $err = '母件代码缺失！'; }
            if ($err == '') {
                $hdr = headerByVersion($db, $assembly, $version);
                if (!$hdr) { $err = '指定 BOM（v' . htmlspecialchars($version) . '）不存在！'; }
                else {
                    DB_query("UPDATE bom_headers_all SET status='" . esc($db, $status) . "',
                        approve_by='" . esc($db, $approve) . "',approve_date='" . $apdtVal . "',approve_remark='" . esc($db, $arem) . "',
                        last_update_date='" . time() . "',last_updated_by='" . esc($db, $_SESSION['UserID']) . "'
                        WHERE bom_header_id='" . esc($db, $hdr['bom_header_id']) . "'", $db);
                    DB_Txn_Commit($db);
                    echo "<script>var api=(window.frameElement&&window.frameElement.api)?window.frameElement.api:null,W=api?api.opener:null;(W||window).location.href='BOMSetup.php?view=" . urlencode($assembly) . "&version=" . urlencode($version) . "';api.close();</script>";
                    exit;
                }
            }

        } elseif ($postOp == 'set_current_version') {
            // 方案B「设为当前版本」：将选定版本提升为该 BOM 的“当前版本”，
            // 级联更新【所有】引用该子件的父 BOM 行（component_item = 本 BOM），
            // 使其“子件版本”绑定统一指向所选版本的 BOM 头。
            // 这样左侧树（view_hdr 跟随）与父 BOM 层级表的「子件版本」列都将显示新版本，保持一致。
            // 说明：下拉框切版本仍只做预览（不写库、不级联）；只有点“设为当前版本”按钮才执行本级联写库。
            $assembly = isset($_POST['assembly']) ? trim($_POST['assembly']) : '';
            $version  = isset($_POST['version']) ? trim($_POST['version']) : '';
            if ($assembly == '' || $version == '') { echo '{"ok":0,"msg":"参数缺失"}'; exit; }
            $hdr = headerByVersion($db, $assembly, $version);
            if (!$hdr) { echo '{"ok":0,"msg":"指定版本不存在"}'; exit; }
            $targetHdrId = (int)$hdr['bom_header_id'];
            // 级联：所有引用该子件的父行（含此前未绑定/NULL 的行）统一指向当前版本头；
            // 已指向该版本的行保持不变（避免无谓写库）。
            DB_query("UPDATE bom_lines_all SET component_bom_header_id='" . $targetHdrId . "',
                last_update_date='" . time() . "', last_updated_by='" . esc($db, $_SESSION['UserID']) . "'
                WHERE component_item='" . esc($db, $assembly) . "'
                  AND (component_bom_header_id IS NULL OR component_bom_header_id != '" . $targetHdrId . "')", $db);
            DB_Txn_Commit($db);
            header('Content-Type: application/json; charset=utf-8');
            echo '{"ok":1}';
            exit;

        } elseif ($postOp == 'set_line_version_bind') {
            // 层级表行级"切换子件版本"：仅更新本行的子件版本绑定，不影响其他 BOM
            $lineId = isset($_POST['line_id']) ? (int)$_POST['line_id'] : 0;
            $bindHdrId = isset($_POST['bind_hdr_id']) ? (int)$_POST['bind_hdr_id'] : 0;
            $err = '';
            if ($lineId <= 0) { $err = '参数错误！'; }
            else {
                $r = DB_query("SELECT component_item FROM bom_lines_all WHERE component_sequence_id='" . $lineId . "'", $db);
                $bln = DB_fetch_array($r);
                if (!$bln) { $err = '未找到该子件行！'; }
                elseif ($bindHdrId > 0) {
                    // 校验绑定头必须属于该行子件（防止绑到别的物料版本）
                    $r2 = DB_query("SELECT bom_header_id FROM bom_headers_all WHERE bom_header_id='" . $bindHdrId . "' AND assembly_item_no='" . esc($db, $bln['component_item']) . "'", $db);
                    if (!DB_fetch_array($r2)) { $err = '所选版本不属于该子件！'; }
                }
            }
            if ($err == '') {
                $bindVal = $bindHdrId > 0 ? "'" . $bindHdrId . "'" : 'NULL';
                DB_query("UPDATE bom_lines_all SET component_bom_header_id=" . $bindVal . ", last_update_date='" . time() . "', last_updated_by='" . esc($db, $_SESSION['UserID']) . "' WHERE component_sequence_id='" . $lineId . "'", $db);
                DB_Txn_Commit($db);
                echo "<script>var api=(window.frameElement&&window.frameElement.api)?window.frameElement.api:null,W=api?api.opener:null;if(W)W.location.reload();api.close();</script>";
                exit;
            }
            echo "<script>alert(" . json_encode($err) . ");history.back();</script>";
            exit;

        } elseif ($postOp == 'edit_qty_save') {
            // 点击层级表"用量"数字修改子件用量：只影响指定版本 BOM 头的该行
            $lineId  = isset($_POST['line_id']) ? $_POST['line_id'] : '';
            $view    = isset($_POST['view']) ? trim($_POST['view']) : '';
            $version = isset($_POST['version']) ? trim($_POST['version']) : '';
            $newQty  = isset($_POST['new_qty']) ? trim($_POST['new_qty']) : '';
            if ($lineId == '' || $newQty == '' || !is_numeric($newQty) || $newQty <= 0) { $err = '用量必须为大于 0 的数字！'; }
            elseif ($view == '') { $err = '父 BOM 缺失！'; }
            if ($err == '') {
                $hdr = headerByVersion($db, $view, $version);
                if (!$hdr) { $err = 'BOM（v' . htmlspecialchars($version) . '）不存在！'; }
                elseif ($hdr['status'] == '已审核') { $err = '该 BOM（v' . htmlspecialchars($hdr['version']) . '）已审核，不允许修改用量！请通过"复制BOM"创建新版本后修改。'; }
                else {
                    $upd = DB_query("UPDATE bom_lines_all SET component_quantity='" . esc($db, $newQty) . "',
                        last_update_date='" . time() . "',last_updated_by='" . esc($db, $_SESSION['UserID']) . "'
                        WHERE component_sequence_id='" . esc($db, $lineId) . "' AND bom_header_id='" . esc($db, $hdr['bom_header_id']) . "'", $db);
                    if (mysqli_affected_rows($db) > 0) {
                        DB_Txn_Commit($db);
                        echo "<script>var api=(window.frameElement&&window.frameElement.api)?window.frameElement.api:null,W=api?api.opener:null;(W||window).location.href='BOMSetup.php?view=" . urlencode($view) . "&version=" . urlencode($version) . "';api.close();</script>";
                        exit;
                    } else {
                        $err = '未找到对应的 BOM 行（可能已被删除）！';
                    }
                }
            }
        } elseif ($postOp == 'del') {
            $assembly = $_POST['assembly'];
            $parent   = isset($_POST['parent']) ? $_POST['parent'] : '';
            // 多版本删除：del_confirm 弹窗勾选 bom_header_ids[] 后提交（已审核已被弹窗禁用）
            $multiIds = isset($_POST['bom_header_ids']) && is_array($_POST['bom_header_ids']) ? $_POST['bom_header_ids'] : array();
            if (!empty($multiIds)) {
                DB_Txn_Begin($db);
                $deletedCnt = 0; $errHdrs = array();
                foreach ($multiIds as $hid) {
                    $hid = (int)$hid;
                    if ($hid <= 0) continue;
                    // 服务端再校验：拒绝已审核版本（防止绕过前端 disabled）
                    $chk = DB_query("SELECT bom_header_id, version, status, assembly_item_no FROM bom_headers_all WHERE bom_header_id='" . $hid . "'", $db);
                    $crow = DB_fetch_array($chk);
                    if (!$crow) continue;
                    if ($crow['status'] === '已审核') { $errHdrs[] = 'v' . $crow['version']; continue; }
                    // 级联删除 BOM（含所有子 BOM + 引用该物料的父行清理）
                    deleteBOMRecursive($db, $crow['assembly_item_no'], array(), $hid);
                    // 清理引用该物料的父 BOM 子件行
                    $r = DB_query("SELECT DISTINCT assembly_item_no FROM bom_lines_all WHERE component_item='" . esc($db, $crow['assembly_item_no']) . "' AND disable_date=0 AND assembly_item_no<>'" . esc($db, $crow['assembly_item_no']) . "'", $db);
                    while ($row = DB_fetch_array($r)) {
                        $p = $row['assembly_item_no'];
                        $phdr = latestHeader($db, $p);
                        if ($phdr) {
                            DB_query("DELETE FROM bom_lines_all WHERE assembly_item_no='" . esc($db, $p) . "' AND component_item='" . esc($db, $crow['assembly_item_no']) . "' AND disable_date=0", $db);
                            // 父 BOM 解除引用后即使变空也不再删除其 header（避免误删上级 BOM）
                        }
                    }
                    $deletedCnt++;
                }
                DB_Txn_Commit($db);
                $msg = '已删除 ' . $deletedCnt . ' 个未审核版本';
                if (!empty($errHdrs)) $msg .= '（' . implode('、', $errHdrs) . ' 已审核版本被跳过）';
                echo '<script>var api=(window.frameElement&&window.frameElement.api)?window.frameElement.api:null,W=api?api.opener:null;alert("' . $msg . '");(W||window).location.href="BOMSetup.php";api.close();</script>';
                exit;
            }
            $refCnt = refCount($db, $assembly);  // 该物料被几个 BOM 头作为子件引用
            if ($refCnt >= 2) {
                // 情况一：被多个母件引用 → 仅解除"当前父"的引用，BOM 数据保留
                if ($parent == '') {
                    $err = '该 BOM 被 ' . $refCnt . ' 个母件引用，且无法确定从哪个母件解除引用。请先从引用它的 BOM 中删除对应子件行。';
                } else {
                    $phdr = latestHeader($db, $parent);
                    if (!$phdr) { $err = '父 BOM（' . htmlspecialchars($parent) . '）不存在！'; }
                    else {
                        DB_query("DELETE FROM bom_lines_all WHERE assembly_item_no='" . esc($db, $parent) . "' AND component_item='" . esc($db, $assembly) . "' AND disable_date=0", $db);
                        // 当前父解除该引用后即使变空也不再删除其 header（避免误删上级 BOM）
                        DB_Txn_Commit($db);
                        echo "<script>var api=(window.frameElement&&window.frameElement.api)?window.frameElement.api:null,W=api?api.opener:null;(W||window).location.href='BOMSetup.php';api.close();</script>";
                        exit;
                    }
                }
            } else {
                // 情况二：只被 1 个（或 0 个）母件引用 → 级联删除该 BOM 及所有子 BOM
                // 同时清理所有引用它的父行（避免父 BOM 指向已删除的 BOM，数据架空）
                $r = DB_query("SELECT DISTINCT assembly_item_no FROM bom_lines_all
                    WHERE component_item='" . esc($db, $assembly) . "' AND disable_date=0
                    AND assembly_item_no<>'" . esc($db, $assembly) . "'", $db);
                while ($row = DB_fetch_array($r)) {
                    $p = $row['assembly_item_no'];
                    $phdr = latestHeader($db, $p);
                    if ($phdr) {
                        DB_query("DELETE FROM bom_lines_all WHERE assembly_item_no='" . esc($db, $p) . "' AND component_item='" . esc($db, $assembly) . "' AND disable_date=0", $db);
                        // 父 BOM 解除引用后即使变空也不再删除其 header（避免误删上级 BOM）
                    }
                }
                deleteBOMRecursive($db, $assembly);
                DB_Txn_Commit($db);
                echo "<script>var api=(window.frameElement&&window.frameElement.api)?window.frameElement.api:null,W=api?api.opener:null;(W||window).location.href='BOMSetup.php';api.close();</script>";
                exit;
            }

        } elseif ($postOp == 'edit_line_save') {
            $lineId = $_POST['line_id'];
            $parent = $_POST['parent'];
            $qty    = $_POST['component_quantity'];
            $op_seq = $_POST['operation_seq_num'] == '' ? 1 : $_POST['operation_seq_num'];
            $sunhao = $_POST['sunhao_rate'] == '' ? 0 : $_POST['sunhao_rate'];
            $weizhi = $_POST['weizhi'];
            $remark = $_POST['component_remarks'];
            if ($qty == '' || !is_numeric($qty) || $qty <= 0) { $err = '用量必须大于 0！'; }
            if ($err == '') {
                $phdr = latestHeader($db, $parent);
                if (!$phdr) { $err = '父 BOM 不存在！'; }
                elseif ($phdr['status'] == '已审核') { $err = '该 BOM（v' . htmlspecialchars($phdr['version']) . '）已审核，不允许编辑子件！请通过 \"复制BOM\" 创建新版本后修改。'; }
            }
            if ($err == '') {
                DB_query("UPDATE bom_lines_all SET operation_seq_num='" . esc($db, $op_seq) . "',
                    component_quantity='" . esc($db, $qty) . "',sunhao_rate='" . esc($db, $sunhao) . "',
                    weizhi='" . esc($db, $weizhi) . "',component_remarks='" . esc($db, $remark) . "',
                    last_update_date='" . time() . "',last_updated_by='" . esc($db, $_SESSION['UserID']) . "'
                    WHERE component_sequence_id='" . esc($db, $lineId) . "' AND bom_header_id='" . esc($db, $phdr['bom_header_id']) . "'", $db);
                DB_Txn_Commit($db);
                echo "<script>var api=(window.frameElement&&window.frameElement.api)?window.frameElement.api:null,W=api?api.opener:null;(W||window).location.href='BOMSetup.php?view=" . urlencode($parent) . "';api.close();</script>";
                exit;
            }

        } elseif ($postOp == 'del_line') {
            $lineId = $_POST['line_id'];
            $parent = $_POST['parent'];
            $phdr = latestHeader($db, $parent);
            if ($phdr && $phdr['status'] == '已审核') {
                $err = '该 BOM（v' . htmlspecialchars($phdr['version']) . '）已审核，不允许删除！请通过 "复制BOM" 创建新版本后修改。';
            } elseif ($phdr) {
                DB_query("DELETE FROM bom_substitutes_all WHERE component_sequence_id='" . esc($db, $lineId) . "'", $db);
                DB_query("DELETE FROM bom_lines_all WHERE component_sequence_id='" . esc($db, $lineId) . "' AND bom_header_id='" . esc($db, $phdr['bom_header_id']) . "'", $db);
                // 父 BOM 删除该行后即使变空也不再删除其 header（避免误删上级 BOM）
            }
            if ($err == '') {
                DB_Txn_Commit($db);
                echo "<script>var api=(window.frameElement&&window.frameElement.api)?window.frameElement.api:null,W=api?api.opener:null;(W||window).location.href='BOMSetup.php?view=" . urlencode($parent) . "';api.close();</script>";
                exit;
            }
            // $err 非空：走 line 826 统一错误提示（alert + 重新渲染确认表单），不执行跳转

        } elseif ($postOp == 'create_top_save') {
            $assembly = trim($_POST['assembly']);
            $version  = trim($_POST['version']) == '' ? '1' : trim($_POST['version']);
            if ($assembly == '') { $err = '请填写物料代码！'; }
            elseif (preg_match('/[\'\"\\\\<>\x00-\x1F]/', $assembly)) { $err = '物料代码不能包含引号、反斜杠、控制字符！'; }
            else {
                // BOM 是记录物料之间关系的，母件必须是真实存在的物料（sf_item_no 主数据）
                $mi = DB_query("SELECT item_no FROM sf_item_no WHERE item_no='" . esc($db, $assembly) . "'", $db);
                if (!DB_fetch_array($mi)) { $err = '物料 ' . htmlspecialchars($assembly) . ' 不存在，请先创建物料主数据！'; }
                else {
                    $hdr = latestHeader($db, $assembly);
                    if ($hdr) { $err = '该物料已有 BOM（v' . htmlspecialchars($hdr['version']) . '）！'; }
                    else {
                        // 检查 (assembly_item_no, version) 唯一性（依赖表 UNIQUE KEY）
                        $r = DB_query("SELECT bom_header_id FROM bom_headers_all WHERE assembly_item_no='" . esc($db, $assembly) . "' AND version='" . esc($db, $version) . "'", $db);
                        if (DB_fetch_array($r)) { $err = '已存在该物料同版本的 BOM（' . htmlspecialchars($assembly) . ' / v' . htmlspecialchars($version) . '）！'; }
                    }
                }
            }
            if ($err == '') {
                $t = time();
                DB_query("INSERT INTO bom_headers_all(assembly_item_no,version,creation_date,created_by,
                    last_update_date,last_updated_by,approve_by,approve_date,approve_remark)
                    VALUES('" . esc($db, $assembly) . "','" . esc($db, $version) . "',
                    '" . $t . "','" . esc($db, $_SESSION['UserID']) . "','" . $t . "','" . esc($db, $_SESSION['UserID']) . "',
                    '" . esc($db, $_SESSION['UserID']) . "','" . $t . "','待签核')", $db);
                DB_Txn_Commit($db);
                echo "<script>var api=(window.frameElement&&window.frameElement.api)?window.frameElement.api:null,W=api?api.opener:null;(W||window).location.href='BOMSetup.php?view=" . urlencode($assembly) . "';api.close();</script>";
                exit;
            }

        } elseif ($postOp == 'quick_item_new_save') {
            // 快捷添加物料：只创建物料主数据（不添加 BOM 行），成功后把物料代码写回父表单（create_top）
            $item_no  = trim($_POST['item_no']);
            $item_name= trim($_POST['item_name']);
            $item_desc= trim($_POST['item_desc']);
            $units    = trim($_POST['units']);
            $cat1     = trim($_POST['item_category1']);
            $item_type= $_POST['item_type'];
            $item_use = $_POST['item_use'];
            $project  = trim($_POST['project_name']);
            $inspect  = $_POST['inspect_flag'];
            $gongyi   = trim($_POST['gongyi']);
            if ($item_no == '') { $err = '请填写物料代码！'; }
            elseif ($item_name == '') { $err = '请填写物料名称！'; }
            elseif ($units == '') { $err = '请填写单位！'; }
            elseif ($cat1 == '') { $err = '请选择分类！'; }
            elseif ($item_type == '') { $err = '请选择类型！'; }
            elseif ($project == '') { $err = '请填写项目名！'; }
            else {
                $chk = DB_query("SELECT item_no FROM sf_item_no WHERE item_no='" . esc($db, $item_no) . "'", $db);
                if (DB_fetch_array($chk)) { $err = '物料代码 ' . htmlspecialchars($item_no) . ' 已存在！'; }
            }
            if ($err == '') {
                $t = time();
                DB_query("INSERT INTO sf_item_no(item_no,item_name,item_desc,units,item_category1,item_type,
                    item_use,project_name,inspect_flag,gongyi,disable_flag,creation_date,created_by,last_update_date,last_updated_by)
                    VALUES('" . esc($db, $item_no) . "','" . esc($db, $item_name) . "','" . esc($db, $item_desc) . "','" . esc($db, $units) . "',
                    '" . esc($db, $cat1) . "','" . esc($db, $item_type) . "','" . esc($db, $item_use) . "','" . esc($db, $project) . "',
                    '" . esc($db, $inspect) . "','" . esc($db, $gongyi) . "','Y','" . $t . "','" . esc($db, $_SESSION['UserID']) . "','" . $t . "','" . esc($db, $_SESSION['UserID']) . "')", $db);
                DB_Txn_Commit($db);
                // 写回父表单（create_top / 复制BOM）的物料代码输入框（用 target 参数定位），并关闭快捷添加弹窗
                $writeTarget = (isset($_POST['target']) && preg_match('/^[A-Za-z][A-Za-z0-9_]*$/', $_POST['target'])) ? $_POST['target'] : 'assembly';
                $writeNameTarget = (isset($_POST['name_target']) && preg_match('/^[A-Za-z][A-Za-z0-9_]*$/', $_POST['name_target'])) ? $_POST['name_target'] : ($writeTarget . 'Name');
                echo "<script>var api=(window.frameElement&&window.frameElement.api)?window.frameElement.api:null,W=api?api.opener:null;"
                    . "var inp=W.document.getElementById('" . $writeTarget . "');if(inp)inp.value='" . addslashes($item_no) . "';"
                    . "var ni=W.document.getElementById('" . $writeNameTarget . "');if(ni)ni.value='" . addslashes($item_name) . "';"
                    . "api.close();</script>";
                exit;
            }

        } elseif ($postOp == 'quick_item_edit_save') {
            // 修改物料主数据：从物料属性面板"修改物料"按钮打开的 quick_item 编辑模式提交
            $item_no = trim($_POST['item_no']);
            if ($item_no == '') { $err = '物料代码缺失！'; }
            else {
                $chk = DB_query("SELECT item_no FROM sf_item_no WHERE item_no='" . esc($db, $item_no) . "'", $db);
                if (!DB_fetch_array($chk)) { $err = '物料 ' . htmlspecialchars($item_no) . ' 不存在！'; }
            }
            if ($err == '') {
                $map = array(
                    'item_name'      => trim($_POST['item_name']),
                    'item_desc'      => trim($_POST['item_desc']),
                    'units'          => trim($_POST['units']),
                    'item_category1' => trim($_POST['item_category1']),
                    'item_type'      => trim($_POST['item_type']),
                    'item_use'       => trim($_POST['item_use']),
                    'project_name'   => trim($_POST['project_name']),
                    'inspect_flag'   => trim($_POST['inspect_flag']),
                    'gongyi'         => trim($_POST['gongyi']),
                    'unit_price'     => trim($_POST['unit_price']),
                    'min_order'      => trim($_POST['min_order']),
                    'sub_code'       => trim($_POST['sub_code']),
                    'youxiaoqi'      => trim($_POST['youxiaoqi']),
                    'wendu'          => trim($_POST['wendu']),
                    'light'          => trim($_POST['light']),
                    'shidu'          => trim($_POST['shidu']),
                    'conditions'     => trim($_POST['conditions']),
                    'so_flag'        => trim($_POST['so_flag']),
                    'disable_flag'   => trim($_POST['disable_flag']),
                    'suoding_flag'   => trim($_POST['suoding_flag']),
                    'item_status'    => trim($_POST['item_status']),
                );
                // 物料类型一致性校验：成品/半成品（F/B，可有 BOM）→ 原材料（M，最小单位，下面不能有子 BOM）
                $oldType = itemType($db, $item_no);
                $newType = $map['item_type'];
                if ($oldType != $newType && $newType == 'M') {
                    // 关键检查：该物料作为母件在 bom_lines_all 里有没有子件行（子 BOM）。
                    // 原材料是最小单位，其下不能再有子 BOM；只要有子件就不能改成原材料。
                    $cntR = DB_query("SELECT COUNT(*) AS c FROM bom_lines_all WHERE assembly_item_no='" . esc($db, $item_no) . "' AND disable_date=0", $db);
                    $cntRow = DB_fetch_array($cntR);
                    $childTotal = $cntRow ? (int)$cntRow['c'] : 0;
                    if ($childTotal > 0) {
                        $kidR = DB_query("SELECT component_item FROM bom_lines_all WHERE assembly_item_no='" . esc($db, $item_no) . "' AND disable_date=0 ORDER BY item_num LIMIT 5", $db);
                        $kids = array();
                        while ($kr = DB_fetch_array($kidR)) { $kids[] = $kr['component_item']; }
                        $typeLabel = ($oldType == 'B') ? '半成品' : (($oldType == 'F') ? '成品' : '其他类型');
                        $err = '该物料当前为' . $typeLabel . '，其 BOM 结构下共有 ' . $childTotal . ' 个子件（如：' . htmlspecialchars(implode('、', $kids)) . (count($kids) < $childTotal ? ' 等' : '') . '）。原材料是最小单位，其下不能再有子 BOM，因此不能修改为原材料。请先删除该物料的 BOM 结构（及其子件）后再修改类型。';
                    } else {
                        // 无子件但存在"空 BOM 头"（无子件的 BOM 头会让节点显示折叠符却无内容）：允许改类型，但清理空 BOM 头
                        $thdr = latestHeader($db, $item_no);
                        if ($thdr) {
                            $lineCQ = DB_query("SELECT COUNT(*) AS c FROM bom_lines_all WHERE bom_header_id='" . esc($db, $thdr['bom_header_id']) . "' AND disable_date=0", $db);
                            $lineC = DB_fetch_array($lineCQ);
                            if ($lineC && (int)$lineC['c'] == 0) {
                                DB_query("DELETE FROM bom_headers_all WHERE bom_header_id='" . esc($db, $thdr['bom_header_id']) . "'", $db);
                            }
                        }
                    }
                }
                // 原材料 → 成品/半成品：F/B 可有 BOM 结构，无破坏性限制，直接放行（用户可随后为其新建 BOM）
            }
            if ($err == '') {
                $sets = array();
                foreach ($map as $k => $v) {
                    $sets[] = $k . "='" . esc($db, $v) . "'";
                }
                $sets[] = "last_update_date='" . time() . "'";
                $sets[] = "last_updated_by='" . esc($db, $_SESSION['UserID']) . "'";
                DB_query("UPDATE sf_item_no SET " . implode(',', $sets) . " WHERE item_no='" . esc($db, $item_no) . "'", $db);
                DB_Txn_Commit($db);
                // 刷新父窗口（让物料属性 Tab 与左侧树显示最新值），并关闭弹窗
                echo "<script>var api=(window.frameElement&&window.frameElement.api)?window.frameElement.api:null,W=api?api.opener:null;W.location.reload();api.close();</script>";
                exit;
            }
        }
    } catch (Exception $e) {
        DB_Txn_Rollback($db);
        $err = '操作失败：' . $e->getMessage();
    }
    if ($err != '') {
        DB_Txn_Rollback($db);
        $op = isset($_POST['dialog_op']) ? $_POST['dialog_op'] : '';
        if (in_array($op, array('new','ref','edit','del_confirm','create_top','edit_line','del_line_confirm','quick_item'))) {
            include('includes/SQL_CommonFunctions.inc');
            echo "<script>alert(" . json_encode($err) . ");</script>";
            renderDialogForm($op, $_POST);
            exit;
        }
        prnMsg($err, 'error');
    }
}

/* ============================================================
 * 弹窗表单渲染（GET op=xxx），输出精简页面（用于 lhgdialog iframe）
 * ============================================================ */
// 随机生成 8 位物料代码（保证不与 sf_item_no 已有编号冲突），返回 JSON
if (isset($_GET['op']) && $_GET['op'] == 'gen_item_no') {
    include('includes/SQL_CommonFunctions.inc');
    header('Content-Type: application/json; charset=utf-8');
    $no = '';
    // 最多尝试 100 次，生成 8 位随机数（10000000-99999999）并查重
    for ($i = 0; $i < 100; $i++) {
        $candidate = (string)mt_rand(10000000, 99999999);
        $r = DB_query("SELECT item_no FROM sf_item_no WHERE item_no='" . esc($db, $candidate) . "'", $db);
        if (!DB_fetch_array($r)) { $no = $candidate; break; }
    }
    if ($no === '') { $no = (string)mt_rand(10000000, 99999999); } // 兜底
    echo json_encode(array('ok' => 1, 'item_no' => $no));
    exit;
}

// 物料详情弹窗（quick_item 列表"查看详情"按钮通过 $.dialog 打开）
if (isset($_GET['op']) && $_GET['op'] == 'quick_item_detail') {
    include('includes/SQL_CommonFunctions.inc');
    $item_no = isset($_GET['item_no']) ? $_GET['item_no'] : '';
    $info = getItemInfo($db, $item_no);
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>物料详情</title></head>';
    echo '<body style="margin:0;padding:14px;background:#fff;font-family:Verdana,Arial,sans-serif;font-size:13px;color:#333">';
    echo '<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:6px 18px">';
    if ($info) {
        foreach ($info as $k => $v) {
            if (in_array($k, array('item_id','password'), true)) continue;
            if (stripos($k, 'date') !== false && is_numeric($v)) $v = date('Y-m-d H:i:s', $v);
            $label = fieldLabel($k);
            if ($v === '' || $v === null) { $display = '—'; }
            elseif ($v === 'Y') { $display = '是'; }
            elseif ($v === 'N') { $display = '否'; }
            else { $display = $v; }
            echo '<div style="padding:4px 0;border-bottom:1px dotted #eee"><span style="color:#666">' . htmlspecialchars($label) . ':</span> <b>' . htmlspecialchars($display) . '</b></div>';
        }
    } else {
        echo '<div style="grid-column:1/-1;color:#c00">未找到物料信息。</div>';
    }
    echo '</div></body></html>';
    exit;
}

if (isset($_GET['op']) && in_array($_GET['op'], array('new','ref','edit','del_confirm','create_top','edit_line','del_line_confirm','quick_item','copy_bom','approve','edit_qty','upgrade','line_version'))) {
    include('includes/SQL_CommonFunctions.inc');
    renderDialogForm($_GET['op'], $_GET);
    exit;
}

// BOM 复制弹窗 POST 处理（集成到 BOMSetup）：复制当前 BOM 结构到目标料号
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['op']) && $_POST['op'] == 'upgrade_save') {
    // 升版：同一物料的新版本（未审核，不自动生效），完整复制源版本的头/行/替代件
    $asm   = trim($_POST['assembly']);
    $srcVer = trim($_POST['src_version']);
    $newVer = trim($_POST['new_version']);
    $err = '';
    if ($asm == '' || $newVer == '') { $err = '物料代码与新版本号不能为空！'; }
    else {
        $srcHdr = headerByVersion($db, $asm, $srcVer);
        if (!$srcHdr) { $err = '源 BOM（v' . htmlspecialchars($srcVer) . '）不存在！'; }
        else {
            $r = DB_query("SELECT bom_header_id FROM bom_headers_all WHERE assembly_item_no='" . esc($db, $asm) . "' AND version='" . esc($db, $newVer) . "'", $db);
            if (DB_fetch_array($r)) { $err = '物料 ' . htmlspecialchars($asm) . ' 已存在版本 v' . htmlspecialchars($newVer) . '！请换个版本号。'; }
        }
    }
    if ($err == '') {
        $t = time(); $uid = $_SESSION['UserID'];
        DB_query("INSERT INTO bom_headers_all(assembly_item_no,version,status,approve_by,approve_date,approve_remark,creation_date,created_by,last_update_date,last_updated_by,is_current)
            VALUES('" . esc($db, $asm) . "','" . esc($db, $newVer) . "','未审核','','0','由 v" . esc($db, $srcVer) . " 升版创建','" . $t . "','" . esc($db, $uid) . "','" . $t . "','" . esc($db, $uid) . "',0)", $db);
        $r = DB_query("SELECT bom_header_id FROM bom_headers_all WHERE assembly_item_no='" . esc($db, $asm) . "' AND version='" . esc($db, $newVer) . "'", $db);
        $row = DB_fetch_array($r);
        $newHdrId = $row['bom_header_id'];
        DB_query("INSERT INTO bom_lines_all(assembly_item_no,bom_header_id,item_num,operation_seq_num,component_item,component_bom_header_id,component_quantity,weizhi,sunhao_rate,component_remarks,effectivity_date,creation_date,created_by,last_update_date,last_updated_by)
            SELECT '" . esc($db, $asm) . "','" . esc($db, $newHdrId) . "',item_num,operation_seq_num,component_item,component_bom_header_id,component_quantity,weizhi,sunhao_rate,component_remarks,
            '" . $t . "','" . $t . "','" . esc($db, $uid) . "','" . $t . "','" . esc($db, $uid) . "'
            FROM bom_lines_all WHERE bom_header_id='" . esc($db, $srcHdr['bom_header_id']) . "' AND disable_date=0", $db);
        DB_query("INSERT INTO bom_substitutes_all(component_sequence_id,item_num,substitute_item,substitute_item_quantity,substitute_remarks,creation_date,created_by,last_update_date,last_updated_by)
            SELECT c.component_sequence_id, olds.item_num, olds.substitute_item, olds.substitute_item_quantity, olds.substitute_remarks,
            '" . $t . "','" . esc($db, $uid) . "','" . $t . "','" . esc($db, $uid) . "'
            FROM bom_substitutes_all olds
            JOIN bom_lines_all oldb ON olds.component_sequence_id = oldb.component_sequence_id
            JOIN bom_lines_all c ON c.assembly_item_no='" . esc($db, $asm) . "' AND c.bom_header_id='" . esc($db, $newHdrId) . "'
                AND oldb.item_num = c.item_num AND oldb.operation_seq_num = c.operation_seq_num AND oldb.component_item = c.component_item
            WHERE oldb.bom_header_id='" . esc($db, $srcHdr['bom_header_id']) . "' AND olds.status='生效'", $db);
        // 方案B：升版仅创建新版本，不自动改变任何父 BOM 的引用（父行绑定保持不变）。
        // 某父 BOM 如需引用新版本，在其层级表行级"子件版本"切换即可。
        DB_Txn_Commit($db);
        // 升版后跳到新版本（新版本未审核，需修改/审核后才可使用；某父 BOM 如需引用新版本，在其层级表点"子件版本"行级切换）
        echo "<script>var api=(window.frameElement&&window.frameElement.api)?window.frameElement.api:null,W=api?api.opener:null;(W||window).location.href='BOMSetup.php?view=" . urlencode($asm) . "&version=" . urlencode($newVer) . "';api.close();</script>";
        exit;
    } else {
        echo "<script>alert(" . json_encode($err) . ");history.back();</script>";
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['op']) && $_POST['op'] == 'copy_bom_save') {
    $srcNo = trim($_POST['src_item_no']);
    $dstNo = trim($_POST['dst_item_no']);
    $dstVersion = trim($_POST['dst_version']) == '' ? '1' : trim($_POST['dst_version']);
    $err = '';
    if ($srcNo == '' || $dstNo == '') { $err = '源料号与目标料号不能为空！'; }
    elseif ($srcNo == $dstNo) { $err = '源料号与目标料号不能相同！'; }
    else {
        $srcHdr = latestHeader($db, $srcNo);
        if (!$srcHdr) { $err = '源料号 ' . htmlspecialchars($srcNo) . ' 没有 BOM 结构，无法复制！'; }
        else {
            $mi = DB_query("SELECT item_no FROM sf_item_no WHERE item_no='" . esc($db, $dstNo) . "'", $db);
            if (!DB_fetch_array($mi)) { $err = '目标料号 ' . htmlspecialchars($dstNo) . ' 不存在，请先创建物料主数据！'; }
            else {
                $r = DB_query("SELECT bom_header_id FROM bom_headers_all WHERE assembly_item_no='" . esc($db, $dstNo) . "' AND version='" . esc($db, $dstVersion) . "'", $db);
                if (DB_fetch_array($r)) { $err = '目标料号已存在同版本 BOM（' . htmlspecialchars($dstNo) . ' / v' . htmlspecialchars($dstVersion) . '）！'; }
                else {
                    $r = DB_query("SELECT 1 FROM bom_lines_all WHERE assembly_item_no='" . esc($db, $srcNo) . "' AND component_item='" . esc($db, $dstNo) . "' AND disable_date=0", $db);
                    if (DB_fetch_array($r)) { $err = '目标料号已是源 BOM 的子件，复制会形成循环引用！'; }
                }
            }
        }
    }
    if ($err == '') {
        $t = time(); $uid = $_SESSION['UserID'];
        DB_query("INSERT INTO bom_headers_all(assembly_item_no,version,status,approve_by,approve_date,approve_remark,creation_date,created_by,last_update_date,last_updated_by)
            VALUES('" . esc($db, $dstNo) . "','" . esc($db, $dstVersion) . "','待签核','" . esc($db, $uid) . "','" . $t . "','BOM复制','" . $t . "','" . esc($db, $uid) . "','" . $t . "','" . esc($db, $uid) . "')", $db);
        $r = DB_query("SELECT bom_header_id FROM bom_headers_all WHERE assembly_item_no='" . esc($db, $dstNo) . "' AND version='" . esc($db, $dstVersion) . "'", $db);
        $row = DB_fetch_array($r);
        $dstBomHeaderId = $row['bom_header_id'];
        DB_query("INSERT INTO bom_lines_all(assembly_item_no,bom_header_id,item_num,operation_seq_num,component_item,component_bom_header_id,component_quantity,weizhi,sunhao_rate,component_remarks,effectivity_date,creation_date,created_by,last_update_date,last_updated_by)
            SELECT '" . esc($db, $dstNo) . "','" . esc($db, $dstBomHeaderId) . "',item_num,operation_seq_num,component_item,component_bom_header_id,component_quantity,weizhi,sunhao_rate,component_remarks,
            '" . $t . "','" . $t . "','" . esc($db, $uid) . "','" . $t . "','" . esc($db, $uid) . "'
            FROM bom_lines_all WHERE bom_header_id='" . esc($db, $srcHdr['bom_header_id']) . "' AND disable_date=0", $db);
        DB_query("INSERT INTO bom_substitutes_all(component_sequence_id,item_num,substitute_item,substitute_item_quantity,substitute_remarks,creation_date,created_by,last_update_date,last_updated_by)
            SELECT c.component_sequence_id, olds.item_num, olds.substitute_item, olds.substitute_item_quantity, olds.substitute_remarks,
            '" . $t . "','" . esc($db, $uid) . "','" . $t . "','" . esc($db, $uid) . "'
            FROM bom_substitutes_all olds
            JOIN bom_lines_all oldb ON olds.component_sequence_id = oldb.component_sequence_id
            JOIN bom_lines_all c ON c.assembly_item_no='" . esc($db, $dstNo) . "' AND c.bom_header_id='" . esc($db, $dstBomHeaderId) . "'
                AND oldb.item_num = c.item_num AND oldb.operation_seq_num = c.operation_seq_num AND oldb.component_item = c.component_item
            WHERE oldb.bom_header_id='" . esc($db, $srcHdr['bom_header_id']) . "' AND olds.status='生效'", $db);
        echo "<script>var api=(window.frameElement&&window.frameElement.api)?window.frameElement.api:null,W=api?api.opener:null;(W||window).location.href='BOMSetup.php?view=" . urlencode($dstNo) . "';api.close();</script>";
        exit;
    } else {
        echo "<script>alert(" . json_encode($err) . ");history.back();</script>";
        exit;
    }
}

// 结构图端点：递归展开指定 BOM，返回 JSON 树给前端渲染
if (isset($_GET['op']) && $_GET['op'] == 'mindmap') {
    header('Content-Type: application/json; charset=utf-8');
    $assembly = isset($_GET['assembly']) ? $_GET['assembly'] : '';
    $mver = isset($_GET['version']) ? $_GET['version'] : '';
    // 指定版本查看：解析为根 BOM 头 id 传入（根固定该版本，子件继续按行锁定/默认解析）
    $mrootHdrId = 0;
    if ($mver !== '') {
        $mh = headerByVersion($db, $assembly, $mver);
        if ($mh) $mrootHdrId = $mh['bom_header_id'];
    }
    echo json_encode(buildMindmapTree($db, $assembly, null, '', $mrootHdrId));
    exit;
}

// 结构图 HTML 页面端点（供 lhgdialog iframe 弹窗加载，页面内 JS 自行拉取 JSON 渲染）
if (isset($_GET['op']) && $_GET['op'] == 'mindmap_view') {
    $assembly = isset($_GET['assembly']) ? $_GET['assembly'] : '';
    $mmVer = isset($_GET['version']) ? $_GET['version'] : '';
    $RootPath = dirname(htmlspecialchars($_SERVER['PHP_SELF']));
    if ($RootPath == '/' || $RootPath == '\\') { $RootPath = ''; }
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>结构图</title>';
    echo '<script src="' . $RootPath . '/javascript/jquery-1.7.2.min.js"></script></head>';
    echo '<body style="margin:0;padding:8px;background:#fff;font-family:Verdana,Arial,sans-serif">';
    echo '<div id="mmContainer" style="width:100%;height:100%;overflow:auto"></div>';
    echo '<script>';
    // 跟随当前查看版本：把 ?version= 透传给 JSON 端点
    $mmUrl = 'BOMSetup.php?op=mindmap&assembly=' . urlencode($assembly);
    if ($mmVer !== '') $mmUrl .= '&version=' . urlencode($mmVer);
    $mmUrl .= '&_r=' . time();
    echo '$.getJSON("' . $mmUrl . '", function(tree){';
    echo '  if (!tree) { $("#mmContainer").html("<p style=color:#c00>加载失败</p>"); return; }';
    echo '  renderMindmap(tree, $("#mmContainer"));';
    echo '}).fail(function(){ $("#mmContainer").html("<p style=color:#c00>加载失败</p>"); });';
    echo <<<'MMJS'
function renderMindmap(root, container){
    var NS = 'http://www.w3.org/2000/svg';
    var NODE_W = 200, NODE_H = 76, GAP_X = 60, GAP_Y = 10, TOP = 20;
    var colorMap = {F:'#bbdefb', B:'#ffe0b2', M:'#c8e6c9', '':'#e0e0e0'};

    // 1) 布局只算一次（按"全展开"后序槽位分配）：叶子严格递增占 y 槽位，内部节点取子节点 y 均值居中。
    //    同一深度的兄弟节点槽位区间不重叠 → 完全展开必不覆盖；折叠/展开只切显隐，坐标永不变。
    var slot = 0;
    function assignY(n, depth){
        n._depth = depth;
        n._x = depth * (NODE_W + GAP_X) + 30;
        if (n.children && n.children.length){
            var ys = [];
            for (var i = 0; i < n.children.length; i++){
                assignY(n.children[i], depth + 1);
                ys.push(n.children[i]._y);
            }
            n._y = (ys[0] + ys[ys.length - 1]) / 2;
        } else {
            n._y = TOP + slot * (NODE_H + GAP_Y);
            slot++;
        }
    }
    assignY(root, 0);
    var maxX = 0, maxY = 0, maxDepth = 0;
    (function walk(n){
        if (n._x + NODE_W > maxX) maxX = n._x + NODE_W;
        if (n._y + NODE_H > maxY) maxY = n._y + NODE_H;
        if (n._depth > maxDepth) maxDepth = n._depth;
        if (n.children) for (var i = 0; i < n.children.length; i++) walk(n.children[i]);
    })(root);

    var svg = document.createElementNS(NS, 'svg');
    svg.setAttribute('viewBox', '0 0 ' + (maxX + 40) + ' ' + (maxY + 40));
    svg.setAttribute('width', maxX + 40);
    svg.setAttribute('height', maxY + 40);
    svg.style.background = '#fff';

    function buildNode(n){
        var g = document.createElementNS(NS, 'g');
        g.setAttribute('class', 'mg-node');
        g.style.cursor = 'pointer';
        var rect = document.createElementNS(NS, 'rect');
        rect.setAttribute('x', n._x);
        rect.setAttribute('y', n._y);
        rect.setAttribute('width', NODE_W);
        rect.setAttribute('height', NODE_H);
        rect.setAttribute('rx', n._depth === 0 ? 8 : 5);
        rect.setAttribute('fill', colorMap[n.item_type] || '#e0e0e0');
        rect.setAttribute('stroke', n._depth === 0 ? '#5e35b1' : (n.has_bom ? '#1976D2' : '#90a4ae'));
        rect.setAttribute('stroke-width', n._depth === 0 ? 2.5 : (n.has_bom ? 1.5 : 1));
        g.appendChild(rect);
        // 实体框内容（4 行）：物料编码 / 物料名称 / 用量(单位)+版本 / 分类
        var cx = n._x + NODE_W / 2;
        // 第1行：物料编码（粗体等宽字体）
        var t1 = document.createElementNS(NS, 'text');
        t1.setAttribute('x', cx); t1.setAttribute('y', n._y + 14);
        t1.setAttribute('text-anchor', 'middle'); t1.setAttribute('font-size', n._depth === 0 ? 13 : 12);
        t1.setAttribute('font-weight', 'bold'); t1.setAttribute('font-family', 'Consolas,monospace');
        t1.setAttribute('fill', '#222'); t1.textContent = n.item_no || '';
        g.appendChild(t1);
        // 第2行：物料名称（截断）
        var t2 = document.createElementNS(NS, 'text');
        t2.setAttribute('x', cx); t2.setAttribute('y', n._y + 28);
        t2.setAttribute('text-anchor', 'middle'); t2.setAttribute('font-size', 11);
        t2.setAttribute('fill', '#333');
        var nm = n.item_name || '';
        if (nm.length > 16) nm = nm.substring(0, 14) + '…';
        t2.textContent = nm;
        g.appendChild(t2);
        // 第3行：用量(单位) 或 版本(状态)
        var t3 = document.createElementNS(NS, 'text');
        t3.setAttribute('x', cx); t3.setAttribute('y', n._y + 45);
        t3.setAttribute('text-anchor', 'middle'); t3.setAttribute('font-size', 11);
        if (n._depth === 0) {
            // 根节点显示版本+状态
            if (n.has_bom) {
                var ver = n.version || '?';
                var st = n.status || '';
                t3.setAttribute('fill', st === '已审核' ? '#2e7d32' : '#ef6c00');
                t3.setAttribute('font-weight', '600');
                t3.textContent = 'v' + ver + (st ? ' · ' + st : '') + (n.is_current ? ' · 当前' : '');
            } else {
                t3.setAttribute('fill', '#888');
                t3.textContent = '（无 BOM）';
            }
        } else {
            // 子节点显示用量(单位)
            var q = (n.qty != null && n.qty !== '') ? n.qty : '';
            var u = n.units || '';
            t3.setAttribute('fill', '#1976D2'); t3.setAttribute('font-weight', '600');
            t3.setAttribute('font-family', 'Consolas,monospace');
            t3.textContent = (q !== '' ? (q + (u ? ' ' + u : '')) : '—');
        }
        g.appendChild(t3);
        // 第4行：分类
        var t4 = document.createElementNS(NS, 'text');
        t4.setAttribute('x', cx); t4.setAttribute('y', n._y + 62);
        t4.setAttribute('text-anchor', 'middle'); t4.setAttribute('font-size', 10);
        t4.setAttribute('fill', '#666');
        var cat = n.item_category1 || '';
        if (cat.length > 18) cat = cat.substring(0, 16) + '…';
        t4.textContent = cat || '—';
        g.appendChild(t4);

        if (n.children && n.children.length){
            var sub = document.createElementNS(NS, 'g');
            sub.setAttribute('class', 'mg-sub');
            for (var i = 0; i < n.children.length; i++){
                var c = n.children[i];
                var p = document.createElementNS(NS, 'path');
                var x1 = n._x + NODE_W, y1 = n._y + NODE_H / 2;
                var x2 = c._x, y2 = c._y + NODE_H / 2;
                var cx2 = (x1 + x2) / 2;
                p.setAttribute('d', 'M' + x1 + ' ' + y1 + ' C' + cx2 + ' ' + y1 + ' ' + cx2 + ' ' + y2 + ' ' + x2 + ' ' + y2);
                p.setAttribute('stroke', '#b478dc');
                p.setAttribute('stroke-width', '1.5');
                p.setAttribute('fill', 'none');
                p.style.pointerEvents = 'none';
                sub.appendChild(p);
                sub.appendChild(buildNode(c));
            }
            g.appendChild(sub);
            if (n._collapsed) sub.style.display = 'none';
            else {
                var t = g.querySelector('text');
                t.textContent = (n.item_name || n.item_no) + '  ▼';
            }
            g.addEventListener('click', function(e){
                e.stopPropagation();
                var t = g.querySelector('text');
                if (sub.style.display === 'none'){
                    sub.style.display = '';
                    n._collapsed = false;
                    t.textContent = (n.item_name || n.item_no) + '  ▼';
                    var cgs = sub.querySelectorAll('.mg-node');
                    for (var i = 0; i < n.children.length; i++){
                        var cn = n.children[i];
                        if (cn._collapsed){
                            var cs = cgs[i] ? cgs[i].querySelector('.mg-sub') : null;
                            if (cs) cs.style.display = 'none';
                        }
                    }
                } else {
                    sub.style.display = 'none';
                    n._collapsed = true;
                    t.textContent = (n.item_name || n.item_no) + '  ▶';
                }
            });
        }
        return g;
    }
    svg.appendChild(buildNode(root));
    container.empty().append(svg);
}
MMJS;
    echo '</script></body></html>';
    exit;
}

function buildMindmapTree($db, $assembly, $qty=null, $units='', $rootHdrId=0) {
    // 缓存键必须带锁定头：同一子件在不同父 BOM 下可能锁定不同版本，不能共用缓存
    static $cache = array();
    $ckey = $assembly . '#' . intval($rootHdrId);
    if (isset($cache[$ckey])) return $cache[$ckey];
    $info = getItemInfo($db, $assembly);
    // 根头锁定优先（结构图按指定版本查看 / 子件行锁定），否则跟随默认版本
    if (intval($rootHdrId) > 0) {
        $hdr = headerById($db, intval($rootHdrId));
        if (!$hdr) $hdr = latestHeader($db, $assembly);
    } else {
        $hdr = latestHeader($db, $assembly);
    }
    $hasBOM = $hdr ? true : false;
    $node = array(
        'item_no' => $assembly,
        'item_name' => $info ? $info['item_name'] : $assembly,
        'item_type' => $info ? $info['item_type'] : '',
        'item_category1' => $info ? $info['item_category1'] : '',
        'has_bom' => $hasBOM,
        'version' => $hdr ? $hdr['version'] : '',
        'status' => $hdr ? $hdr['status'] : '',
        'is_current' => $hdr ? ($hdr['is_current'] == 1) : false,
        'qty' => $qty,
        'units' => $units,
        'children' => array()
    );
    if ($hasBOM) {
        $lines = getActiveLines($db, $hdr['bom_header_id']);
        foreach ($lines as $ln) {
            $cunits = '';
            $ci = getItemInfo($db, $ln['component_item']);
            if ($ci && !empty($ci['units'])) $cunits = $ci['units'];
            $node['children'][] = buildMindmapTree($db, $ln['component_item'], $ln['component_quantity'], $cunits, $ln['component_bom_header_id']);
        }
    }
    $cache[$ckey] = $node;
    return $node;
}

function renderDialogForm($op, $p) {
    global $db;
    $assembly = isset($p['assembly']) ? $p['assembly'] : '';
    $version  = isset($p['version'])  ? $p['version']  : '';
    $lineId   = isset($p['line_id'])  ? $p['line_id']  : '';
    $parent   = isset($p['parent'])   ? $p['parent']   : '';
    $RootPath = dirname(htmlspecialchars($_SERVER['PHP_SELF']));
    if ($RootPath == '/' || $RootPath == '\\') { $RootPath = ''; }
    $Theme = isset($_SESSION['Theme']) ? $_SESSION['Theme'] : 'xenos';
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>BOM</title>';
    echo '<link href="' . $RootPath . '/css/' . $Theme . '/default.css" rel="stylesheet" type="text/css"/>';
    echo '<script src="' . $RootPath . '/javascript/jquery-1.7.2.min.js"></script>';
    echo '<script src="' . $RootPath . '/JXC/javascript/lhgdialog.min.js?self=true&skin=chrome"></script>';
    // 弹出框加载 BOM 树相关样式（默认 default.css 不含，原在主页面 <style> 段里）
    echo '<style type="text/css">' . getBomTreeStyles() . '</style>';
    echo '</head><body style="padding:10px">';

    // webERP 表单安全校验：所有 POST 必须携带 FormID（= $_SESSION['FormID']），否则 session.inc 报"此表单提交的ID不正确"
    $formIdField = '<input type="hidden" name="FormID" value="' . htmlspecialchars($_SESSION['FormID']) . '">';

    if ($op == 'ref') {
        $opts = '';
        $r = DB_query("SELECT item_no,item_name FROM sf_item_no WHERE disable_flag<>'N' ORDER BY item_no", $db);
        while ($row = DB_fetch_array($r)) {
            $opts .= '<option value="' . htmlspecialchars($row['item_no']) . '">' . htmlspecialchars($row['item_no'] . ' ' . $row['item_name']) . '</option>';
        }
        // 物料数据（按分类展示用，与 quick_item 同一套）
        $qiItems = array();
        // 一次性查物料 + 是否已在 bom_headers_all 里有头（LEFT JOIN 子查询去重，避免物料标记与 bom 头表不一致）
        // restrict=fb 时仅显示成品/半成品（F/B），用于 BOMUpload2 等仅选半成品的场景
        $qiWhere = $restrictFb ? " AND i.item_type IN ('F','B')" : '';
        $r = DB_query("SELECT i.item_no, i.item_name, i.item_type, i.item_category1, i.item_use,
                CASE WHEN i.item_type='M' OR h.assembly_item_no IS NULL THEN 0 ELSE 1 END AS has_bom,
                CASE WHEN i.item_type='M' OR h.assembly_item_no IS NULL THEN NULL
                     ELSE (SELECT MAX(h2.version) FROM bom_headers_all h2 WHERE h2.assembly_item_no = i.item_no) END AS latest_version,
                CASE WHEN i.item_type='M' OR h.assembly_item_no IS NULL THEN NULL
                     ELSE (SELECT h3.status FROM bom_headers_all h3 WHERE h3.assembly_item_no = i.item_no ORDER BY h3.is_current DESC, h3.bom_header_id DESC LIMIT 1) END AS latest_status
                FROM sf_item_no i
                LEFT JOIN (SELECT DISTINCT assembly_item_no FROM bom_headers_all) h ON h.assembly_item_no = i.item_no
                WHERE i.disable_flag<>'N'" . $qiWhere . "
                ORDER BY i.item_category1, i.item_no", $db);
        while ($row = DB_fetch_array($r)) { $qiItems[] = $row; }
        $qiCategories = array();
        foreach ($qiItems as $qiIt) {
            if ($qiIt['item_category1'] !== '' && !in_array($qiIt['item_category1'], $qiCategories)) {
                $qiCategories[] = $qiIt['item_category1'];
            }
        }
        $qiItemsJson = json_encode($qiItems, JSON_UNESCAPED_UNICODE);
        $qiCategoriesJson = json_encode($qiCategories, JSON_UNESCAPED_UNICODE);
        // 注：原"引用BOM（树状选择）"模式已移除——物料和BOM非一对一，引用物料时服务端自动查 bom_headers_all（按 is_current=1 优先）
        //      展开其子件结构到当前母件；纯物料（无 BOM 头）作为叶子直接插入。
        echo '<form id="refForm" method="post" action="BOMSetup.php">' . $formIdField . '
            <input type="hidden" name="op" value="ref_save">
            <input type="hidden" name="dialog_op" value="ref">
            <input type="hidden" name="assembly" value="' . htmlspecialchars($assembly) . '">
            <input type="hidden" name="version" value="' . htmlspecialchars($version) . '">
            <div style="font-family:Verdana,Arial,sans-serif;font-size:13px;color:#333">
            <table class="selection" style="margin-bottom:8px">
            <tr><td>母件</td><td><b>' . htmlspecialchars($assembly . ' (v' . $version . ')') . '</b><span style="margin-left:16px;color:#666;font-size:12px">引用物料后，未锁定版本的行按默认版本展开其内部子件结构</span></td></tr>
            </table>

            <!-- 顶部步骤指示 -->
            <div style="display:flex;gap:8px;align-items:center;margin-bottom:10px;padding:8px 12px;background:#eef4fb;border:1px solid #cfe0f3;border-radius:4px">
                <span id="refStepBadge1" style="padding:3px 12px;background:#1976D2;color:#fff;border-radius:3px;font-size:12px;font-weight:bold">1. 选择物料</span>
                <span style="color:#888">→</span>
                <span id="refStepBadge2" style="padding:3px 12px;background:#ddd;color:#fff;border-radius:3px;font-size:12px">2. 批量设置</span>
                <span style="flex:1"></span>
                <span id="refStepInfo" style="color:#666;font-size:12px">已选 0 项</span>
            </div>

            <div style="display:flex;gap:14px;align-items:stretch;height:520px">
                <!-- Step 1: 物料选择（多选） -->
                <div id="refStep1" style="flex:1;border:1px solid #ddd;padding:8px;background:#fff;border-radius:3px;overflow:hidden;display:flex;flex-direction:column">
                    <!-- 引用物料模式：按分类展示物料 -->
                    <div id="refModeItem" style="flex:1;display:flex;flex-direction:column;overflow:hidden">
                        <div style="font-weight:bold;color:#0d47a1;margin-bottom:6px">选择物料（点击左侧分类查看成员料，点击物料行多选）</div>
                        <input id="refItemFilter" type="text" placeholder="过滤物料代码/名称..." style="width:96%;padding:5px;margin-bottom:6px;border:1px solid #ccc;border-radius:2px" onkeyup="onRefSearch()">
                        <div style="display:flex;gap:8px;flex:1;overflow:hidden">
                            <div id="refCatList" style="flex:0 0 150px;border:1px solid #e0e8f0;border-radius:3px;overflow-y:auto;background:#f9fbfd"></div>
                            <div id="refMatList" style="flex:1;border:1px solid #e0e8f0;border-radius:3px;overflow-y:auto;background:#fff"></div>
                        </div>
                        <select id="refItemSelect" size="1" style="display:none">' . $opts . '</select>
                    </div>
                </div>

                <!-- Step 2: 批量设置（点击"下一步"后显示） -->
                <div id="refStep2" style="display:none;flex:1">
                    <div style="display:flex;gap:14px;align-items:flex-start">
                        <div style="flex:0 0 280px;border:1px solid #ddd;background:#fff;border-radius:3px;max-height:440px;overflow:auto">
                            <div style="padding:8px 12px;border-bottom:1px solid #e0e8f0;font-weight:bold;color:#0d47a1;background:#f9fbfd">已选物料（点击切换当前）</div>
                            <div id="refBatchList"></div>
                        </div>
                        <div style="flex:1;border:1px solid #ddd;padding:12px;background:#fafafa;border-radius:3px">
                            <div style="font-weight:bold;color:#0d47a1;margin-bottom:10px">
                                当前第 <span id="refBatchIdx" style="color:#c00;font-size:16px">1</span> /
                                <span id="refBatchTotal">0</span> 项：<span id="refBatchCurrentName" style="color:#333"></span>
                            </div>
                            <table class="selection">
                            <tr><td>用量*</td><td><input id="refBatchQty" value="1" size="10"></td></tr>
                            <tr><td>制程</td><td><input id="refBatchOp" value="1" size="10"></td></tr>
                            <tr><td>自损率</td><td><input id="refBatchLoss" value="0" size="10"></td></tr>
                            <tr><td>位置</td><td><input id="refBatchPos" size="30"></td></tr>
                            <tr><td>备注</td><td><input id="refBatchRem" size="30"></td></tr>
                            </table>
                            <div style="margin-top:8px;padding:6px;background:#fffbe6;border:1px solid #ffe58f;border-radius:3px;font-size:11px;color:#856404">
                            级联：成品/半成品且有 BOM 头 → 按**最新版本**子件递归展开（数量相乘）。原材料仅插入自身。
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 1 底部：下一步按钮 -->
            <div id="refStep1Footer" style="margin-top:12px;display:flex;justify-content:space-between;align-items:center">
                <div id="selectedItemBar" style="color:#888;font-size:13px">未选择物料（点击右侧物料行多选）</div>
                <button type="button" id="btnBatchNext" onclick="goStep2()" disabled style="padding:7px 28px;background:#1976D2;color:#fff;border:none;border-radius:3px;cursor:pointer;font-size:13px;opacity:0.4">下一步：批量设置 → <span id="refSelCount">0</span> 项</button>
            </div>

            <!-- Step 2 底部：上一步 / 应用到全部 / 保存 -->
            <div id="refStep2Footer" style="margin-top:12px;display:none;justify-content:space-between;align-items:center">
                <button type="button" id="btnBatchPrev" onclick="goStep1()" style="padding:7px 18px;background:#fff;border:1px solid #888;color:#555;border-radius:3px;cursor:pointer;font-size:13px">← 上一步</button>
                <button type="button" id="btnBatchApplyAll" onclick="applyAllBatch()" style="padding:7px 18px;background:#fff3cd;border:1px solid #ffc107;color:#856404;border-radius:3px;cursor:pointer;font-size:13px">应用到全部</button>
                <button type="button" id="btnBatchSave" onclick="batchSubmit()" style="padding:7px 28px;background:#1976D2;color:#fff;border:none;border-radius:3px;cursor:pointer;font-size:13px">保存引用</button>
            </div>
            </div>
            </form>
            <script>
            function updateSelectedBar(text) {
                var bar = document.getElementById(\'selectedItemBar\');
                if (text === \'物料下拉框中选中\' || text === \'未选择物料\') {
                    bar.innerHTML = text;
                    bar.style.color = \'#888\';
                    bar.style.fontWeight = \'normal\';
                } else {
                    bar.innerHTML = \'选中: <b style="color:#0d47a1">\' + text + \'</b>\';
                    bar.style.color = \'#333\';
                }
            }
            // 物料下拉框变化：写入选中物料
            function onRefItemSelectChange() {
                var sel = document.getElementById(\'refItemSelect\');
                updateSelectedBar(sel.options[sel.selectedIndex].text);
            }
            // 物料搜索过滤
            function filterRefItems() {
                var kw = document.getElementById(\'refItemFilter\').value.toLowerCase();
                $("#refItemSelect option").each(function(){
                    var t = $(this).text().toLowerCase();
                    $(this).toggle(t.indexOf(kw) >= 0);
                });
            }
            function getVal(id){ var e=document.getElementById(id); return e?e.value:\'\'; }
            function makeHidden(name,val){
                var i=document.createElement(\'input\'); i.type=\'hidden\'; i.name=name; i.value=val;
                return i;
            }
            function submitRefForm(){
                var form = document.getElementById(\'refForm\');
                form.querySelectorAll(\'input.dyn-hidden\').forEach(function(e){ e.remove(); });
                form.submit();
            }
            // (引用 BOM 树状模式已移除——引用物料时服务端自动查 bom_headers_all 展开)
            $("#refItemSelect").on("change", onRefItemSelectChange);
            $("#refItemFilter").on("keyup", filterRefItems);
            </script>';
        // 引用物料模式：按分类展示（heredoc 输出，避免 echo 单引号字符串内 JS 单引号闭合问题）
        echo <<<REFJS
        <script>
            var refItems = {$qiItemsJson};
            var refCats = {$qiCategoriesJson};
            var refCurCat = "";
            var refCurFilter = "";
            var refTypeColor = {F:"#bbdefb", B:"#ffe0b2", M:"#c8e6c9", P:"#f8bbd0", "":"#e0e0e0"};
            var refTypeName = {F:"成品", B:"半成品", M:"原材料", P:"包装物", "":"其他"};
            function renderRefCats(){
                var html = "";
                html += '<div class="ref-cat-li" data-cat="" style="padding:9px 12px;cursor:pointer;font-weight:bold;color:#0d47a1;border-bottom:1px solid #e0e8f0;background:#e3f2fd">全部 <span style="color:#888;font-weight:normal;font-size:12px;float:right">(' + refItems.length + ')</span></div>';
                refCats.forEach(function(c){
                    var cnt = 0; for (var i = 0; i < refItems.length; i++) if (refItems[i].item_category1 === c) cnt++;
                    html += '<div class="ref-cat-li" data-cat="' + c + '" style="padding:9px 12px;cursor:pointer;border-bottom:1px solid #f0f0f0">' + c + '<span style="color:#888;font-size:12px;float:right">(' + cnt + ')</span></div>';
                });
                $("#refCatList").html(html);
                $("#refCatList .ref-cat-li").off("click").on("click", function(){
                    $("#refCatList .ref-cat-li").css({"background":"#f9fbfd","font-weight":"normal","color":"#333"});
                    $(this).css({"background":"#e3f2fd","font-weight":"bold","color":"#0d47a1"});
                    refCurCat = $(this).data("cat");
                    renderRefMats();
                });
            }
            function renderRefMats(){
                var html = "";
                var list = [];
                for (var i = 0; i < refItems.length; i++){
                    var it = refItems[i];
                    if (refCurCat && it.item_category1 !== refCurCat) continue;
                    if (refCurFilter){
                        var t = (it.item_no + " " + it.item_name).toLowerCase();
                        if (t.indexOf(refCurFilter) < 0) continue;
                    }
                    list.push(it);
                }
                if (list.length === 0){
                    html = '<div style="padding:40px 20px;text-align:center;color:#888;font-size:13px">该分类下没有物料</div>';
                } else {
                    html += '<div style="display:flex;background:#eef4fb;border-bottom:1px solid #d0dceb;font-size:12px;font-weight:bold;color:#555;position:sticky;top:0;z-index:1">'
                        + '<div style="flex:0 0 22px;padding:6px 4px;text-align:center">选</div>'
                        + '<div style="flex:0 0 18px;padding:6px 4px"></div>'
                        + '<div style="flex:0 0 90px;padding:6px 8px">物料编码</div>'
                        + '<div style="flex:1 1 auto;min-width:140px;padding:6px 4px">物料名称</div>'
                        + '<div style="flex:0 0 60px;padding:6px 4px">类型</div>'
                        + '<div style="flex:0 0 90px;padding:6px 4px">用途</div>'
                        + '<div style="flex:0 0 100px;padding:6px 4px">分类</div>'
                        + '<div style="flex:0 0 64px;padding:6px 4px;text-align:center">BOM</div>'
                        + '<div style="flex:0 0 80px;padding:6px 4px;text-align:center">最新版本</div>'
                        + '</div>';
                    list.forEach(function(it){
                        var fill = refTypeColor[it.item_type] || "#e0e0e0";
                        // 有BOM标签：绿色"有 BOM" + 最新版本显示（审核状态着色：已审核绿 / 未审橙）
                        var hasBom = (it.has_bom == 1);
                        var bomTag = hasBom ? '<span style="display:inline-block;background:#e8f5e9;color:#2e7d32;border:1px solid #a5d6a7;border-radius:3px;padding:1px 6px;font-size:11px;font-weight:600;margin-left:4px">有 BOM</span>' : '';
                        var verText = hasBom ? ('v' + (it.latest_version || '?')) : '-';
                        var verColor = it.latest_status === '已审核' ? '#2e7d32' : (it.latest_status === '待签核' || it.latest_status === '未审核' ? '#ef6c00' : '#888');
                        html += '<div class="ref-mat-li" data-code="' + it.item_no + '" data-name="' + it.item_name + '" data-type="' + it.item_type + '" '
                            + 'style="display:flex;align-items:center;padding:7px 6px;border-bottom:1px solid #f0f0f0;cursor:pointer">'
                            + '<div style="flex:0 0 22px;text-align:center"><input type="checkbox" class="ref-mat-cb" data-code="' + it.item_no + '"></div>'
                            + '<div style="width:24px;height:18px;background:' + fill + ';border:1px solid #aaa;border-radius:3px;margin-right:8px;align-self:center"></div>'
                            + '<div style="flex:0 0 90px;font-weight:bold;font-family:Consolas,monospace">' + it.item_no + '</div>'
                            + '<div style="flex:1 1 auto;min-width:140px">' + it.item_name + bomTag + '</div>'
                            + '<div style="flex:0 0 60px;color:#666;font-size:12px">' + (refTypeName[it.item_type] || '其他') + '</div>'
                            + '<div style="flex:0 0 90px;color:#888;font-size:12px">' + (it.item_use==="S"?"生产":it.item_use==="Y"?"研发":"") + '</div>'
                            + '<div style="flex:0 0 100px;color:#888;font-size:12px">' + (it.item_category1 || '') + '</div>'
                            + '<div style="flex:0 0 64px;text-align:center">' + (hasBom ? '<span style="display:inline-block;background:#e8f5e9;color:#2e7d32;border:1px solid #a5d6a7;border-radius:3px;padding:1px 6px;font-size:11px;font-weight:600">有 BOM</span>' : '<span style="display:inline-block;background:#f5f5f5;color:#999;border:1px solid #e0e0e0;border-radius:3px;padding:1px 6px;font-size:11px">无</span>') + '</div>'
                            + '<div style="flex:0 0 80px;text-align:center;font-family:Consolas,monospace;color:' + verColor + ';font-size:12px">' + verText + '</div>'
                            + '</div>';
                    });
                }
                $("#refMatList").html(html);
                // 容器改 grid 布局（保证所有列严格对齐）
                $("#refMatList").css({
                    display: 'grid',
                    gridTemplateColumns: '22px 28px 90px minmax(140px,1fr) 60px 90px 100px 64px 80px',
                    gap: 0,
                    alignItems: 'stretch'
                });
                // 每个行 div 用 display:contents——子元素按容器 grid 列对齐
                $("#refMatList > div").css("display", "contents");
                // 多选：行点击切换 checkbox
                $("#refMatList").off("click", ".ref-mat-li").on("click", ".ref-mat-li", function(e){  // 先 off 防累积
                    // 点 checkbox 时不重复触发
                    if (e.target.type === 'checkbox') return;
                    var cb = $(this).find('.ref-mat-cb').first();
                    cb.prop('checked', !cb.prop('checked'));
                    updateRefSelection();
                });
                $("#refMatList .ref-mat-cb").off("change").on("change", updateRefSelection);
                // 恢复之前选中的高亮
                for (var code in refSelected) {
                    $("#refMatList .ref-mat-cb[data-code='" + code + "']").prop('checked', true);
                    $("#refMatList .ref-mat-li[data-code='" + code + "']").css('background', '#e3f2fd');
                }
                updateRefSelection();
            }
            function onRefSearch(){
                refCurFilter = $("#refItemFilter").val().toLowerCase();
                renderRefMats();
            }
            // 多选 + 批量引用：refSelected 全局累积，跨分类保留选中
            var refSelected = {};
            function updateRefSelection(){
                // 增量同步：物料 DOM checkbox 与 refSelected 双向校对
                $("#refMatList .ref-mat-cb").each(function(){
                    var code = $(this).attr("data-code");  // attr 保持字符串
                    var isChecked = $(this).prop('checked');
                    var li = $(this).closest(".ref-mat-li");
                    if (isChecked) {
                        if (!refSelected[code]) {
                            refSelected[code] = {name: li.data("name"), type: li.data("type")};
                        }
                    } else {
                        delete refSelected[code];
                    }
                });
                var n = Object.keys(refSelected).length;
                // Step 1：只控制"下一步"按钮和顶部步骤指示文字（不再显示 BOM 行字段，按用户要求"点击批量引用后再进行设置"）
                $("#refStepInfo").html('已选 <b style="color:#0d47a1;font-size:14px">' + n + '</b> 项 <span style="color:#888;font-size:12px">（点 <a href="javascript:void(0)" class="ref-del-all" style="color:#c00;text-decoration:none">全部清空</a>）</span>');
                if (n > 0) {
                    $("#btnBatchNext").prop('disabled', false).css('opacity', 1);
                    $("#refSelCount").text(n);
                    // 已选物料每项加 × 删除按钮（事件委托到 #refSelectedBar，点击 .ref-del-one 移除）
                    var list = Object.keys(refSelected).map(function(k){
                        return k + '(' + refSelected[k].name + ')<a href="javascript:void(0)" class="ref-del-one" data-code="' + k + '" style="margin-left:4px;color:#c00;font-weight:bold;text-decoration:none" title="取消选择">×</a>';
                    }).join('、');
                    if (n > 6) {
                        var ks = Object.keys(refSelected);
                        var showList = ks.slice(0,6).map(function(k){
                            return k + '(' + refSelected[k].name + ')<a href="javascript:void(0)" class="ref-del-one" data-code="' + k + '" style="margin-left:4px;color:#c00;font-weight:bold;text-decoration:none" title="取消选择">×</a>';
                        }).join('、');
                        list = showList + ' ... 共 ' + n + ' 项';
                    }
                    updateSelectedBar('<b style="color:#0d47a1">已选 ' + n + ' 项：</b>' + list);
                } else {
                    $("#btnBatchNext").prop('disabled', true).css('opacity', 0.4);
                    $("#refSelCount").text(0);
                    updateSelectedBar('未选择物料（点击右侧物料行多选）');
                }
            }
            // 删除单个已选物料（事件委托到 #refSelectedBar）：同时取消 checkbox（否则 updateRefSelection 会被补回来）
            $(document).on('click', '.ref-del-one', function(e){
                e.preventDefault();
                var code = $(this).attr('data-code');
                if (code) {
                    var cb = $('#refMatList .ref-mat-cb[data-code="' + code + '"]');
                    if (cb.length) cb.prop('checked', false);
                    delete refSelected[code];
                    updateRefSelection();
                }
            });
            // 全部清空
            $(document).on('click', '.ref-del-all', function(e){
                e.preventDefault();
                refSelected = {};
                updateRefSelection();
            });
            function updateSelectedBar(text){
                var bar = document.getElementById("selectedItemBar");
                bar.innerHTML = text;
                bar.style.color = text.indexOf('已选') >= 0 ? "#333" : "#888";
            }
            // ===== 步骤切换 =====
            var refBatchList = [];  // 已选物料数组（按选择顺序）
            var refBatchIdx = 0;   // 当前编辑位置
            var refBatchFields = {}; // {code: {qty,op,sunhao,pos,remark}}
            function goStep2(){
                if (refBatchList.length === 0) refBatchList = Object.keys(refSelected);
                if (refBatchList.length === 0) { alert('请先选择至少一个物料！'); return; }
                refBatchIdx = 0;
                $("#refStep1, #refStep1Footer").hide();
                $("#refStep2, #refStep2Footer").show();
                $("#refStepBadge1").css({"background":"#ccc","font-weight":"normal"});
                $("#refStepBadge2").css({"background":"#1976D2","font-weight":"bold"});
                renderBatchList();
                loadBatchFields(0);
            }
            function goStep1(){
                saveBatchFields(refBatchIdx);
                $("#refStep2, #refStep2Footer").hide();
                $("#refStep1, #refStep1Footer").show();
                $("#refStepBadge2").css({"background":"#ddd","font-weight":"normal"});
                $("#refStepBadge1").css({"background":"#1976D2","font-weight":"bold"});
            }
            function renderBatchList(){
                var html = "";
                for (var i = 0; i < refBatchList.length; i++){
                    var c = refBatchList[i];
                    var name = (refSelected[c] && refSelected[c].name) || c;
                    var sel = (i === refBatchIdx) ? "background:#e3f2fd;font-weight:bold;" : "";
                    html += '<div class="ref-batch-li" data-idx="' + i + '" style="padding:8px 12px;border-bottom:1px solid #f0f0f0;cursor:pointer;' + sel + '">'
                        + '<span style="color:#888;font-size:12px;margin-right:6px">' + (i+1) + '/' + refBatchList.length + '</span>'
                        + '<span style="font-family:Consolas,monospace;font-weight:bold">' + c + '</span> '
                        + '<span style="color:#666;font-size:12px">' + name + '</span></div>';
                }
                $("#refBatchList").html(html);
                $("#refBatchList .ref-batch-li").on("click", function(){
                    var idx = parseInt($(this).data("idx"));
                    saveBatchFields(refBatchIdx);
                    refBatchIdx = idx;
                    loadBatchFields(idx);
                    renderBatchList();
                });
            }
            function saveBatchFields(idx){
                var code = refBatchList[idx];
                refBatchFields[code] = {
                    qty: $("#refBatchQty").val(),
                    op_seq: $("#refBatchOp").val(),
                    sunhao: $("#refBatchLoss").val(),
                    pos: $("#refBatchPos").val(),
                    remark: $("#refBatchRem").val()
                };
            }
            function loadBatchFields(idx){
                var code = refBatchList[idx];
                var f = refBatchFields[code] || {qty:1, op_seq:1, sunhao:0, pos:'', remark:''};
                $("#refBatchQty").val(f.qty);
                $("#refBatchOp").val(f.op_seq || 1);
                $("#refBatchLoss").val(f.sunhao || 0);
                $("#refBatchPos").val(f.pos || '');
                $("#refBatchRem").val(f.remark || '');
                var name = (refSelected[code] && refSelected[code].name) || code;
                $("#refBatchCurrentName").html('<b style="font-family:Consolas,monospace;color:#0d47a1">' + code + '</b> ' + name);
                $("#refBatchIdx").text(idx + 1);
                $("#refBatchTotal").text(refBatchList.length);
            }
            // "应用到全部"：把当前物料字段复制到所有已选物料
            function applyAllBatch(){
                saveBatchFields(refBatchIdx);
                var cur = refBatchFields[refBatchList[refBatchIdx]] || {};
                for (var i = 0; i < refBatchList.length; i++){
                    refBatchFields[refBatchList[i]] = {
                        qty: cur.qty, op_seq: cur.op_seq, sunhao: cur.sunhao, pos: cur.pos, remark: cur.remark
                    };
                }
                alert('已把当前字段应用到全部 ' + refBatchList.length + ' 项');
            }
            // 提交：把每个物料的字段打包到 form（数组：component_item[], component_quantity[]...）
            function batchSubmit(){
                saveBatchFields(refBatchIdx);
                var form = document.getElementById('refForm');
                form.querySelectorAll('input.dyn-hidden').forEach(function(e){ e.remove(); });
                for (var i = 0; i < refBatchList.length; i++){
                    var code = refBatchList[i];
                    var f = refBatchFields[code] || {qty:1, op_seq:1, sunhao:0, pos:'', remark:''};
                    [
                        ['component_item[]', code],
                        ['component_quantity[]', f.qty || 1],
                        ['operation_seq_num[]', f.op_seq || 1],
                        ['sunhao_rate[]', f.sunhao || 0],
                        ['weizhi[]', f.pos || ''],
                        ['component_remarks[]', f.remark || '']
                    ].forEach(function(p){
                        var inp = document.createElement('input');
                        inp.type = 'hidden'; inp.name = p[0]; inp.value = p[1];
                        inp.className = 'dyn-hidden';
                        form.appendChild(inp);
                    });
                }
                form.submit();
            }
            // (引用 BOM 模式已移除——只有"引用物料"单模式)
            // 初始化：默认展开"引用物料"模式分类
            renderRefCats();
            renderRefMats();
        </script>
REFJS;

    } elseif ($op == 'new') {
        $cats = '';
        $r = DB_query("SELECT DISTINCT item_category1 FROM sf_item_no WHERE item_category1<>'' ORDER BY item_category1", $db);
        while ($row = DB_fetch_array($r)) { $cats .= '<option>' . htmlspecialchars($row['item_category1']) . '</option>'; }
        echo '<form method="post" action="BOMSetup.php">' . $formIdField . '
            <input type="hidden" name="op" value="new_save">
            <input type="hidden" name="dialog_op" value="new">
            <input type="hidden" name="assembly" value="' . htmlspecialchars($assembly) . '">
            <input type="hidden" name="version" value="' . htmlspecialchars($version) . '">
            <table class="selection">
            <tr><td colspan="2"><b>新建物料并作为子件引用到 ' . htmlspecialchars($assembly . ' (v' . $version . ')') . '</b></td></tr>
            <tr><td>物料代码*</td><td><input name="item_no" id="newItemNo" size="20"><button type="button" onclick="genItemNo(\'#newItemNo\')" style="margin-left:5px;padding:2px 8px">随机生成</button></td></tr>
            <tr><td>物料名称*</td><td><input name="item_name" size="40"></td></tr>
            <tr><td>规格型号</td><td><input name="item_desc" size="40"></td></tr>
            <tr><td>单位</td><td><input name="units" size="10"></td></tr>
            <tr><td>分类</td><td><select name="item_category1">' . $cats . '</select></td></tr>
            <tr><td>类型</td><td><select name="item_type"><option value="M">M原材料</option><option value="B">B半成品</option><option value="F" selected>F成品</option><option value="P">P</option></select></td></tr>
            <tr><td>用途</td><td><select name="item_use"><option value="S">S生产</option><option value="Y">Y研发</option></select></td></tr>
            <tr><td>项目名*</td><td><input name="project_name" size="20" value="常规"></td></tr>
            <tr><td>检验标志</td><td><select name="inspect_flag"><option value="Y">Y</option><option value="N">N</option></select></td></tr>
            <tr><td>工艺</td><td><input name="gongyi" size="30"></td></tr>
            <tr><td>子件用量*</td><td><input name="component_quantity" value="1" size="10"></td></tr>
            <tr><td>制程</td><td><input name="operation_seq_num" value="1" size="10"></td></tr>
            <tr><td>自损率</td><td><input name="sunhao_rate" value="0" size="10"></td></tr>
            <tr><td>位置</td><td><input name="weizhi" size="30"></td></tr>
            <tr><td>备注</td><td><input name="component_remarks" size="40"></td></tr>
            <tr><td colspan="2" class="centre"><input type="submit" value="保存新建"></td></tr>
            </table></form>';
        echo '<script>
        function genItemNo(sel){
            $.getJSON("BOMSetup.php?op=gen_item_no&_r=" + Date.now(), function(res){
                if (res && res.ok) { $(sel).val(res.item_no); }
                else { alert("随机生成失败，请重试"); }
            });
        }
        </script>';

    } elseif ($op == 'edit') {
        $hdr = latestHeader($db, $assembly);
        $status = $hdr ? $hdr['status'] : '';
        $cost   = $hdr ? $hdr['cost_price'] : '';
        $appr   = $hdr ? $hdr['approve_by'] : '';
        $arem   = $hdr ? $hdr['approve_remark'] : '';
        $apdt   = $hdr && $hdr['approve_date'] ? date('Y-m-d H:i', $hdr['approve_date']) : '';
        $ctdt   = $hdr && $hdr['creation_date'] ? date('Y-m-d H:i:s', $hdr['creation_date']) : '—';
        $ctby   = $hdr && $hdr['created_by'] ? $hdr['created_by'] : '—';
        $updt   = $hdr && $hdr['last_update_date'] ? date('Y-m-d H:i:s', $hdr['last_update_date']) : '—';
        $upby   = $hdr && $hdr['last_updated_by'] ? $hdr['last_updated_by'] : '—';
        // 状态彩色标签（不可编辑——审核独立操作）
        $statusColor = $status === '已审核' ? '#2e7d32' : ($status === '未审核' ? '#fb8c00' : '#666');
        $editBlocked = $status === '已审核';  // 已审核禁编辑结构
        echo '<form method="post" action="BOMSetup.php" id="editBomForm">' . $formIdField . '
            <input type="hidden" name="op" value="edit_save">
            <input type="hidden" name="dialog_op" value="edit">
            <input type="hidden" name="assembly" value="' . htmlspecialchars($assembly) . '">
            <input type="hidden" name="version" value="' . htmlspecialchars($version) . '">
            <table class="selection">
            <tr><td>母件</td><td><b>' . htmlspecialchars($assembly . ' (v' . $version . ')') . '</b></td></tr>
            <tr><td>状态</td><td><span style="display:inline-block;padding:2px 10px;background:' . $statusColor . '22;border:1px solid ' . $statusColor . ';color:' . $statusColor . ';border-radius:3px;font-weight:bold">' . htmlspecialchars($status) . '</span> <span style="color:#888;font-size:12px;margin-left:6px">审核请在左侧菜单【BOM审核】中操作</span></td></tr>
            <tr><td>成本</td><td><input name="cost_price" value="' . htmlspecialchars($cost) . '" size="10"></td></tr>
            <tr><td>审核人</td><td style="color:#666">' . htmlspecialchars($appr ?: '—') . '</td></tr>
            <tr><td>审核日期</td><td style="color:#666">' . htmlspecialchars($apdt ?: '—') . '</td></tr>
            <tr><td>审核备注</td><td style="color:#666">' . htmlspecialchars($arem ?: '—') . '</td></tr>
            <tr><td>创建信息</td><td style="color:#666">' . htmlspecialchars($ctby) . ' @ ' . htmlspecialchars($ctdt) . '</td></tr>
            <tr><td>最后更新</td><td style="color:#666">' . htmlspecialchars($upby) . ' @ ' . htmlspecialchars($updt) . '</td></tr>
            <tr><td colspan="2" class="centre"><input type="submit" value="保存修改"' . ($editBlocked ? ' disabled style="background:#ccc;cursor:not-allowed;opacity:0.5"' : '') . '>
                <button type="button" onclick="openBomUpgrade(\'' . htmlspecialchars($assembly) . '\',\'' . htmlspecialchars($version) . '\')" style="padding:6px 18px;background:#fb8c00;color:#fff;border:none;border-radius:3px;cursor:pointer;font-size:13px;margin-left:10px">升版 →</button></td></tr>
            </table></form>
            <script>
            // 审核入口已移至独立【BOM审核】菜单(BOMApprove.php)，BOM管理内不再提供审核按钮
            function openBomUpgrade(assembly, version){
                $.dialog({title:"BOM 升版 - " + assembly + " (v" + version + ")", width:600, height:420,
                    content:"url:BOMSetup.php?op=upgrade&assembly=" + encodeURIComponent(assembly) + "&version=" + encodeURIComponent(version) + "&_r=" + Date.now(), lock:true});
            }
            </script>';

    } elseif ($op == 'approve') {
        // 独立审核弹窗：状态/审核人/审核日期/审核备注 — 权限独立，与 BOM 编辑分离
        $hdr = headerByVersion($db, $assembly, $version);
        if (!$hdr) { echo '<div style="color:#c00">BOM（v' . htmlspecialchars($version) . '）不存在！</div>'; exit; }
        $status = $hdr['status'];
        $appr   = $hdr['approve_by'];
        $arem   = $hdr['approve_remark'];
        $apdt   = $hdr['approve_date'] ? date('Y-m-d\TH:i', $hdr['approve_date']) : '';
        // 权限占位：当前所有登录用户可审核（后续可加 www_users 角色字段 / 权限组）
        echo '<form method="post" action="BOMSetup.php" onsubmit="return validateApprove(this)">' . $formIdField . '
            <input type="hidden" name="op" value="approve_save">
            <input type="hidden" name="dialog_op" value="approve">
            <input type="hidden" name="assembly" value="' . htmlspecialchars($assembly) . '">
            <input type="hidden" name="version" value="' . htmlspecialchars($version) . '">
            <div style="font-family:Verdana,Arial,sans-serif;font-size:13px;color:#333">
            <div style="background:#fff3cd;border:1px solid #ffc107;padding:10px;margin-bottom:12px;border-radius:4px;font-size:12px;color:#856404">
            <b>权限说明：</b>审核由【指定审核人】负责（权限管理预留），当前版本所有登录用户均可操作。后续可按 www_users 加角色/部门限制。
            </div>
            <table class="selection">
            <tr><td>母件</td><td><b>' . htmlspecialchars($assembly . ' (v' . $version . ')') . '</b></td></tr>
            <tr><td><span style="color:#c00">*</span> 状态</td><td><select name="status" style="padding:4px 10px">
                <option value="未审核"' . ($status === '未审核' ? ' selected' : '') . '>未审核</option>
                <option value="待签核"' . ($status === '待签核' ? ' selected' : '') . '>待签核</option>
                <option value="已审核"' . ($status === '已审核' ? ' selected' : '') . '>已审核</option>
                <option value="已拒签"' . ($status === '已拒签' ? ' selected' : '') . '>已拒签</option>
            </select></td></tr>
            <tr><td><span style="color:#c00">*</span> 审核人</td><td><input name="approve_by" value="' . htmlspecialchars($appr ?: $_SESSION['UserID']) . '" size="20" required></td></tr>
            <tr><td>审核日期</td><td><input name="approve_date" type="datetime-local" value="' . htmlspecialchars($apdt) . '" size="20"><span style="color:#888;font-size:12px;margin-left:6px">留空=保存时取当前时间</span></td></tr>
            <tr><td>审核备注</td><td><textarea name="approve_remark" rows="3" cols="50" placeholder="如：审核意见、修改要求等">' . htmlspecialchars($arem) . '</textarea></td></tr>
            <tr><td colspan="2" class="centre"><input type="submit" value="提交审核" style="background:#1976D2;color:#fff;border:none;padding:6px 28px;border-radius:3px;cursor:pointer"></td></tr>
            </table>
            </div>
            </form>
            <script>
            function validateApprove(f){
                if (!f.status.value){ alert("请选择状态！"); return false; }
                if (!f.approve_by.value.trim()){ alert("请填写审核人！"); f.approve_by.focus(); return false; }
                return true;
            }
            </script>';

    } elseif ($op == 'edit_qty') {
        // 点击层级表"用量"数字的弹窗：修改当前展示版本 BOM 的子件用量
        $lineId  = isset($p['line_id']) ? $p['line_id'] : '';
        $view    = isset($p['view']) ? trim($p['view']) : '';
        $version = isset($p['version']) ? trim($p['version']) : '';
        $curQty = '';
        $asm    = '';
        $asmName = '';
        $hdr = headerByVersion($db, $view, $version);
        if ($hdr) {
            $lr = DB_query("SELECT component_item, component_quantity FROM bom_lines_all WHERE component_sequence_id='" . esc($db, $lineId) . "' AND bom_header_id='" . esc($db, $hdr['bom_header_id']) . "' AND disable_date=0", $db);
            $lrow = DB_fetch_array($lr);
            if ($lrow) {
                $curQty = $lrow['component_quantity'];
                $asm = $lrow['component_item'];
                $asmName = itemName($db, $asm);
            }
        }
        $blocked = ($hdr && $hdr['status'] == '已审核');
        echo '<form method="post" action="BOMSetup.php" onsubmit="return validateQtyEdit(this)">' . $formIdField . '
            <input type="hidden" name="op" value="edit_qty_save">
            <input type="hidden" name="dialog_op" value="edit_qty">
            <input type="hidden" name="line_id" value="' . htmlspecialchars($lineId) . '">
            <input type="hidden" name="view" value="' . htmlspecialchars($view) . '">
            <input type="hidden" name="version" value="' . htmlspecialchars($version) . '">
            <div style="font-family:Verdana,Arial,sans-serif;font-size:13px;color:#333;padding:4px">
            <table class="selection">
            <tr><td>母件</td><td><b>' . htmlspecialchars($view . ($version == '' ? '' : ' (v' . $version . ')')) . '</b></td></tr>
            <tr><td>子件</td><td>' . htmlspecialchars($asm . ' ' . $asmName) . '</td></tr>
            <tr><td>当前用量</td><td><b style="color:#c00">' . htmlspecialchars($curQty) . '</b></td></tr>
            <tr><td><span style="color:#c00">*</span> 新用量</td><td><input name="new_qty" value="' . htmlspecialchars($curQty) . '" size="10" required' . ($blocked ? ' disabled' : '') . '></td></tr>
            <tr><td colspan="2" class="centre">
                ' . ($blocked ? '<span style="color:#c00">已审核版本不允许修改用量！请复制为新版本后修改。</span>' : '<input type="submit" value="保存用量" style="background:#1976D2;color:#fff;border:none;padding:6px 28px;border-radius:3px;cursor:pointer">') . '
            </td></tr>
            </table>
            </div>
            </form>
            <script>
            function validateQtyEdit(f){
                if (!f.new_qty.value || !isFinite(f.new_qty.value) || parseFloat(f.new_qty.value) <= 0){ alert("用量必须为大于 0 的数字！"); f.new_qty.focus(); return false; }
                return true;
            }
            </script>';

    } elseif ($op == 'del_confirm') {
        // 查询该 BOM 被哪些母件引用，用于确认弹窗说明删除后果
        $refParents = array();
        $r = DB_query("SELECT DISTINCT l.assembly_item_no FROM bom_lines_all l
            WHERE l.component_item='" . esc($db, $assembly) . "' AND l.disable_date=0
            AND l.assembly_item_no<>'" . esc($db, $assembly) . "'", $db);
        while ($row = DB_fetch_array($r)) { $refParents[] = $row['assembly_item_no']; }
        $refCnt = count($refParents);
        // 查询该物料的所有 BOM 版本（按 is_current DESC, bom_header_id DESC 排序）
        $versions = array();
        $vr = DB_query("SELECT bom_header_id, version, status, is_current, approve_by FROM bom_headers_all
            WHERE assembly_item_no='" . esc($db, $assembly) . "'
            ORDER BY is_current DESC, bom_header_id DESC", $db);
        while ($row = DB_fetch_array($vr)) { $versions[] = $row; }
        $verCnt = count($versions);
        echo '<form method="post" action="BOMSetup.php" id="delBomForm">' . $formIdField . '
            <input type="hidden" name="op" value="del">
            <input type="hidden" name="dialog_op" value="del_confirm">
            <input type="hidden" name="assembly" value="' . htmlspecialchars($assembly) . '">
            <input type="hidden" name="parent" value="' . htmlspecialchars(isset($p['parent']) ? $p['parent'] : '') . '">';
        if ($verCnt > 1) {
            // 多版本：列出版本列表 + 已审核禁用 + 全部删除按钮
            echo '<p style="color:#c00;font-weight:bold">删除 BOM：' . htmlspecialchars($assembly) . '（共 <b>' . $verCnt . '</b> 个版本）</p>';
            echo '<p style="color:#555;font-size:12px;margin:4px 0 8px">勾选要删除的版本（<span style="color:#2e7d32">已审核</span>不允许删除，防止破坏生产依据）：</p>';
            echo '<table class="selection" style="width:100%;font-size:12px;margin-bottom:10px">';
            echo '<tr style="background:#eef4fb;font-weight:bold"><th style="padding:5px 6px;width:32px"></th><th style="padding:5px 6px;text-align:left">版本</th><th style="padding:5px 6px;text-align:left">状态</th><th style="padding:5px 6px;text-align:left">审核人</th><th style="padding:5px 6px;text-align:left">备注</th></tr>';
            foreach ($versions as $vv) {
                $isApproved = ($vv['status'] === '已审核');
                $stColor = $isApproved ? '#2e7d32' : '#ef6c00';
                echo '<tr>'
                    . '<td style="padding:5px 6px;text-align:center">'
                    . ($isApproved ? '<input type="checkbox" disabled title="已审核不允许删除">' : '<input type="checkbox" name="bom_header_ids[]" value="' . $vv['bom_header_id'] . '" class="del-version-cb">')
                    . '</td>'
                    . '<td style="padding:5px 6px;font-family:Consolas,monospace"><b>v' . htmlspecialchars($vv['version']) . '</b></td>'
                    . '<td style="padding:5px 6px;color:' . $stColor . ';font-weight:600">' . htmlspecialchars($vv['status']) . '</td>'
                    . '<td style="padding:5px 6px">' . htmlspecialchars($vv['approve_by'] ?: '-') . '</td>'
                    . '<td style="padding:5px 6px;color:#888">—</td>'
                    . '</tr>';
            }
            echo '</table>';
            echo '<div style="margin:6px 0">';
            echo '<label style="font-size:12px;cursor:pointer"><input type="checkbox" id="selectAllVersions" onclick="document.querySelectorAll(\'.del-version-cb\').forEach(function(c){c.checked=this.checked;},{event:event})"> 全选/取消全选</label> ';
            echo '</div>';
            // 查询本 BOM 子件行数 + 递归独占子 BOM 数 + 引用本 BOM 的父 BOM 数（给用户清楚看到影响范围）
            $hdrForCount = $versions[0]; // 取最新版本头
            $lineCntR = DB_query("SELECT COUNT(*) AS c FROM bom_lines_all WHERE bom_header_id='" . esc($db, $hdrForCount['bom_header_id']) . "' AND disable_date=0", $db);
            $lineCntRow = DB_fetch_array($lineCntR);
            $lineCnt = $lineCntRow['c'];
            $subBomR = DB_query("SELECT DISTINCT l.component_item FROM bom_lines_all l
                INNER JOIN bom_headers_all h ON h.assembly_item_no = l.component_item
                WHERE l.bom_header_id='" . esc($db, $hdrForCount['bom_header_id']) . "' AND l.disable_date=0", $db);
            $exclusiveSubBoms = array();
            while ($row = DB_fetch_array($subBomR)) {
                // 子 BOM 是否被其他 BOM 头引用？
                $chkR = DB_query("SELECT COUNT(DISTINCT bom_header_id) AS c FROM bom_lines_all WHERE component_item='" . esc($db, $row['component_item']) . "' AND disable_date=0 AND bom_header_id<>'" . esc($db, $hdrForCount['bom_header_id']) . "'", $db);
                $chkRow = DB_fetch_array($chkR);
                if ($chkRow['c'] == 0) $exclusiveSubBoms[] = $row['component_item'];
            }
            $exSubCnt = count($exclusiveSubBoms);
            // 级联影响清单
            echo '<div style="background:#fff8e1;border:1px solid #ffe58f;border-radius:4px;padding:8px 12px;margin:8px 0;font-size:12px;line-height:1.7;color:#5d4037">';
            echo '<b style="color:#c00">⚠️ 本次删除将影响（仅针对选中版本）：</b><br>';
            echo '① 选中版本自身的 BOM 头（bom_headers_all）：' . count($versions) . ' 行<br>';
            echo '② 选中版本下的子件行（bom_lines_all）：<b>' . $lineCnt . '</b> 条<br>';
            if ($exSubCnt > 0) {
                $sample = array_slice($exclusiveSubBoms, 0, 6);
                $more = $exSubCnt > 6 ? ' 等 <b>' . $exSubCnt . '</b> 个' : '';
                echo '③ <b>独占</b>子 BOM（仅被本 BOM 引用，会被级联删除）：' . htmlspecialchars(implode('、', $sample)) . $more . '<br>';
            } else {
                echo '③ <b>独占</b>子 BOM：无<br>';
            }
            if ($refCnt >= 2) {
                echo '④ 引用本 BOM 的 <b>' . $refCnt . '</b> 个母件：仅<b>解除当前父的子件引用</b>（父 BOM 保留）；其他 ' . ($refCnt - 1) . ' 个母件仍可使用该 BOM';
            } elseif ($refCnt == 1) {
                echo '④ 引用本 BOM 的 <b>1</b> 个母件 <b>' . htmlspecialchars($refParents[0]) . '</b>：仅<b>解除当前父的子件引用</b>（父 BOM 保留）';
            } else {
                echo '④ 引用本 BOM 的母件：无（顶层 BOM）';
            }
            echo '</div>';
            echo '<p class="centre" style="margin-top:14px">'
                . '<input type="submit" value="删除选中版本" onclick="return validateDelSelection();" style="background:#c00;color:#fff;border:none;padding:7px 24px;border-radius:3px;cursor:pointer;font-weight:600;margin-right:8px">'
                . '<input type="button" value="全部删除（含未审核）" onclick="delAllVersions();" style="background:#7b1fa2;color:#fff;border:none;padding:7px 24px;border-radius:3px;cursor:pointer;font-weight:600">'
                . '</p>';
            echo '</form>';
            echo '<script>
            function validateDelSelection(){
                var cbs = document.querySelectorAll(".del-version-cb");
                for (var i = 0; i < cbs.length; i++) if (cbs[i].checked) return true;
                alert("请至少勾选一个未审核版本！"); return false;
            }
            function delAllVersions(){
                document.querySelectorAll(".del-version-cb").forEach(function(c){ c.checked = true; });
                if (validateDelSelection()) {
                    if (confirm("确认删除该物料的所有未审核版本？\n（已审核版本不在列表中，将被保留）")) document.getElementById("delBomForm").submit();
                }
            }
            </script>';
        } else {
            // 单版本（原逻辑）
            echo '<p style="color:#c00;font-weight:bold">删除 BOM：' . htmlspecialchars($assembly) . '（v' . htmlspecialchars($version) . '）</p>';
            // 查询子件行数 + 独占子 BOM 数
            $lineCntR2 = DB_query("SELECT COUNT(*) AS c FROM bom_lines_all WHERE bom_header_id='" . esc($db, $hdr['bom_header_id']) . "' AND disable_date=0", $db);
            $lineCntRow2 = DB_fetch_array($lineCntR2);
            $lineCnt2 = $lineCntRow2['c'];
            $subBomR2 = DB_query("SELECT DISTINCT l.component_item FROM bom_lines_all l
                INNER JOIN bom_headers_all h ON h.assembly_item_no = l.component_item
                WHERE l.bom_header_id='" . esc($db, $hdr['bom_header_id']) . "' AND l.disable_date=0", $db);
            $exclusiveSubBoms2 = array();
            while ($row = DB_fetch_array($subBomR2)) {
                $chkR2 = DB_query("SELECT COUNT(DISTINCT bom_header_id) AS c FROM bom_lines_all WHERE component_item='" . esc($db, $row['component_item']) . "' AND disable_date=0 AND bom_header_id<>'" . esc($db, $hdr['bom_header_id']) . "'", $db);
                $chkRow2 = DB_fetch_array($chkR2);
                if ($chkRow2['c'] == 0) $exclusiveSubBoms2[] = $row['component_item'];
            }
            $exSubCnt2 = count($exclusiveSubBoms2);
            echo '<div style="background:#fff8e1;border:1px solid #ffe58f;border-radius:4px;padding:8px 12px;margin:8px 0;font-size:12px;line-height:1.7;color:#5d4037">';
            echo '<b style="color:#c00">⚠️ 本次删除将影响：</b><br>';
            echo '① 本 BOM 头（bom_headers_all）：1 行<br>';
            echo '② 本 BOM 下的子件行（bom_lines_all）：<b>' . $lineCnt2 . '</b> 条<br>';
            if ($exSubCnt2 > 0) {
                $sample2 = array_slice($exclusiveSubBoms2, 0, 6);
                $more2 = $exSubCnt2 > 6 ? ' 等 <b>' . $exSubCnt2 . '</b> 个' : '';
                echo '③ <b>独占</b>子 BOM（仅被本 BOM 引用，会被级联删除）：' . htmlspecialchars(implode('、', $sample2)) . $more2 . '<br>';
            } else {
                echo '③ <b>独占</b>子 BOM：无<br>';
            }
            if ($refCnt >= 2) {
                echo '④ 引用本 BOM 的 <b>' . $refCnt . '</b> 个母件：仅<b>解除当前父的子件引用</b>（父 BOM 保留）；其他 ' . ($refCnt - 1) . ' 个母件仍可使用该 BOM';
            } elseif ($refCnt == 1) {
                echo '④ 引用本 BOM 的 <b>1</b> 个母件 <b>' . htmlspecialchars($refParents[0]) . '</b>：仅<b>解除当前父的子件引用</b>（父 BOM 保留）';
            } else {
                echo '④ 引用本 BOM 的母件：无（顶层 BOM）';
            }
            echo '</div>';
            if ($verCnt == 1 && $versions[0]['status'] === '已审核') {
                echo '<p style="color:#c00;font-weight:bold">⚠️ 该版本已审核，不允许删除！请通过"复制BOM"创建新版本后修改。</p>';
                echo '<p class="centre"><input type="button" value="关闭" onclick="frameElement.api.close();" style="padding:6px 24px"></p>';
            } else {
                echo '<p class="centre"><input type="submit" value="确认删除" style="background:#c00;color:#fff;border:none;padding:7px 28px;border-radius:3px;cursor:pointer;font-weight:600"></p>';
            }
            echo '</form>';
        }

    } elseif ($op == 'copy_bom') {
        // 复制 BOM 弹窗：左侧树状选择源 BOM（参照"引用BOM"），右侧填目标表单；选择目标物料用新版分类展示 quick_item
        $srcNo = isset($p['assembly']) ? $p['assembly'] : '';
        // 顶层 BOM 列表（树状选择源 BOM 用）
        $topAsms = array();
        $r = DB_query("SELECT DISTINCT h.assembly_item_no FROM bom_headers_all h
            JOIN sf_item_no i ON i.item_no = h.assembly_item_no
            WHERE i.disable_flag<>'N'
            AND h.assembly_item_no NOT IN (SELECT DISTINCT component_item FROM bom_lines_all WHERE disable_date=0)
            ORDER BY h.assembly_item_no", $db);
        while ($row = DB_fetch_array($r)) { $topAsms[] = array('item' => $row['assembly_item_no'], 'line_id' => ''); }
        ob_start();
        echo '<ul class="bom-tree" id="copySrcTree">';
        renderForest($db, '', $topAsms, array(), array());
        echo '</ul>';
        $treeHtml = ob_get_clean();
        // 物料数据（目标料号选择用，按分类展示）+ has_bom（LEFT JOIN bom_headers_all）
        $qiItems = array();
        // restrict=fb 时仅显示成品/半成品（F/B），用于 BOMUpload2 等仅选半成品的场景
        $qiWhere = $restrictFb ? " AND i.item_type IN ('F','B')" : '';
        $r = DB_query("SELECT i.item_no, i.item_name, i.item_type, i.item_category1, i.item_use,
                CASE WHEN i.item_type='M' OR h.assembly_item_no IS NULL THEN 0 ELSE 1 END AS has_bom,
                CASE WHEN i.item_type='M' OR h.assembly_item_no IS NULL THEN NULL
                     ELSE (SELECT MAX(h2.version) FROM bom_headers_all h2 WHERE h2.assembly_item_no = i.item_no) END AS latest_version,
                CASE WHEN i.item_type='M' OR h.assembly_item_no IS NULL THEN NULL
                     ELSE (SELECT h3.status FROM bom_headers_all h3 WHERE h3.assembly_item_no = i.item_no ORDER BY h3.is_current DESC, h3.bom_header_id DESC LIMIT 1) END AS latest_status
                FROM sf_item_no i
                LEFT JOIN (SELECT DISTINCT assembly_item_no FROM bom_headers_all) h ON h.assembly_item_no = i.item_no
                WHERE i.disable_flag<>'N'" . $qiWhere . "
                ORDER BY i.item_category1, i.item_no", $db);
        while ($row = DB_fetch_array($r)) { $qiItems[] = $row; }
        $qiCategories = array();
        foreach ($qiItems as $qiIt) {
            if ($qiIt['item_category1'] !== '' && !in_array($qiIt['item_category1'], $qiCategories)) {
                $qiCategories[] = $qiIt['item_category1'];
            }
        }
        $qiItemsJson = json_encode($qiItems, JSON_UNESCAPED_UNICODE);
        $qiCategoriesJson = json_encode($qiCategories, JSON_UNESCAPED_UNICODE);
        echo '<form method="post" action="BOMSetup.php" onsubmit="return validateCopyBom(this)">' . $formIdField . '
            <input type="hidden" name="op" value="copy_bom_save">
            <input type="hidden" name="dialog_op" value="copy_bom">
            <div style="font-family:Verdana,Arial,sans-serif;font-size:13px;color:#333">
            <div style="display:flex;gap:14px;align-items:flex-start;height:660px">
                <!-- 左侧：源 BOM 树状选择 -->
                <div style="flex:0 0 480px;border:1px solid #e0e8f0;padding:10px;background:#fff;border-radius:4px;display:flex;flex-direction:column">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">
                        <div style="font-weight:bold;color:#0d47a1">选择源 BOM（点击节点选中）</div>
                        <div>
                            <button type="button" class="copyTreeAll" data-act="expand" style="padding:2px 10px;font-size:12px;border:1px solid #1976D2;background:#fff;color:#1976D2;border-radius:3px;cursor:pointer;margin-right:4px">全部展开</button>
                            <button type="button" class="copyTreeAll" data-act="collapse" style="padding:2px 10px;font-size:12px;border:1px solid #5e35b1;background:#fff;color:#5e35b1;border-radius:3px;cursor:pointer">全部折叠</button>
                        </div>
                    </div>
                    <div style="flex:1;overflow:auto;border:1px solid #f0f0f0;border-radius:3px">' . $treeHtml . '</div>
                </div>
                <!-- 右侧：复制目标表单 -->
                <div style="flex:1;border:1px solid #e0e8f0;padding:14px;background:#fafafa;border-radius:4px;overflow:auto">
                    <div style="font-weight:bold;color:#0d47a1;margin-bottom:10px;font-size:14px">复制目标</div>
                    <table class="selection" style="background:transparent">
                    <tr><td style="width:120px"><span style="color:#c00">*</span> 源 BOM</td><td><input name="src_item_no" id="copySrcNo" value="' . htmlspecialchars($srcNo) . '" size="22" readonly placeholder="点击左侧树节点选择" style="background:#f5f5f5"></td></tr>
                    <tr><td>源 BOM 版本</td><td><input name="src_version" id="copySrcVer" value="1" size="10"></td></tr>
                    <tr><td>源 BOM 名称</td><td><input name="src_item_name" id="copySrcName" size="30" readonly style="background:#f5f5f5"></td></tr>
                    <tr><td><span style="color:#c00">*</span> 目标料号</td><td><input name="dst_item_no" id="copyDstNo" size="22" placeholder="选择或输入目标料号" required>
                        <button type="button" id="btnPickCopyDst" style="padding:2px 10px;margin-left:6px">选择</button></td></tr>
                    <tr><td>目标 BOM 名称</td><td><input name="dst_item_name" id="copyDstName" size="30" placeholder="选择物料后自动填入，可修改"></td></tr>
                    <tr><td><span style="color:#c00">*</span> 目标版本</td><td><input name="dst_version" id="copyDstVer" value="1" size="10" required></td></tr>
                    </table>
                    <div style="margin-top:12px;padding:10px;background:#fffbe6;border:1px solid #ffe58f;border-radius:4px;font-size:12px;color:#856404;line-height:1.7">
                    <b>说明：</b>复制源 BOM 的完整结构（<b>头/全部子件/替代件</b>）到目标料号。<br>
                    • 目标 BOM 状态默认 <b>未审核</b>。<br>
                    • 目标料号不存在时：自动按\"目标 BOM 名称\"创建新物料（类型默认 F 成品）。
                    </div>
                    <div id="copySelectedBar" style="margin-top:10px;padding:8px 12px;background:#e3f2fd;border:1px solid #90caf9;border-radius:3px;color:#0d47a1;font-size:13px">未选择源 BOM（点击左侧树节点）</div>
                </div>
            </div>
            <div style="margin-top:14px;display:flex;justify-content:space-between;align-items:center">
                <div style="color:#888;font-size:13px"></div>
                <button type="button" id="btnSaveCopy" style="padding:8px 32px;background:#1976D2;color:#fff;border:none;border-radius:3px;cursor:pointer;font-size:14px;font-weight:bold">复制 BOM</button>
            </div>
            </div>
            </form>
            <script>
            function validateCopyBom(f){
                var src = document.getElementById("copySrcNo").value;
                var dst = document.getElementById("copyDstNo").value;
                if (!src) { alert("请先在左侧树中选择源 BOM！"); return false; }
                if (!dst) { alert("请填写/选择目标料号！"); return false; }
                if (src === dst) { alert("源料号与目标料号不能相同！"); return false; }
                return true;
            }
            // 树节点点击 → 选中源 BOM
            $("#copySrcTree .lbl, #copySrcTree input[type=checkbox]").on("click", function(e){
                e.stopPropagation();
                var li = $(this).closest(".bom-node");
                var code = li.attr("data-assembly");  // 用 attr 保持字符串（料号 0 会被 data() 转数字导致 falsy bug）
                var leaf = li.data("leaf");
                var version = li.data("version") || "1";
                var name = li.find(".lbl").first().text() || code;
                if (leaf == 1 || !version){ alert("该物料是叶子物料（没有 BOM 结构），不能作为复制源！请选择树中带 [v版本] 标记的节点！"); return; }
                if (code !== undefined && code !== null && code !== ""){
                    $("#copySrcNo").val(code);
                    $("#copySrcVer").val(version);
                    $("#copySrcName").val(name);
                    $("#copySelectedBar").html("选中源 BOM: <b style=\"color:#0d47a1\">" + name + " (" + code + ", v" + version + ")</b>");
                    $("#copySelectedBar").css("color", "#333");
                    $("#copySrcTree .bom-node").removeClass("selected");
                    li.addClass("selected");
                }
            });
            // 折叠/展开：点 .bom-glyphs 列任意位置（含 .tw 折叠符；checkbox 除外）触发。
            // .tw 是 .bom-glyphs 的子元素，点击会冒泡到这里，无需单独绑定，避免双重触发。
            $("#copySrcTree").on("click", ".bom-glyphs", function(e){
                if (e.target.type === "checkbox") return;  // checkbox 交给勾选逻辑
                var li = $(this).closest(".bom-node");
                if (li.hasClass("leaf-node")) return;      // 叶子无子分支
                var ul = li.children("ul.bom-sub");
                if (ul.length){
                    if (ul.is(":visible")){ ul.hide(); li.find("> .bom-row .tw").text("+"); }
                    else { ul.show(); li.find("> .bom-row .tw").text("-"); }
                }
            });
            // 全部展开/折叠（按钮在标题栏，与树不在同一 div，直接按 class 选择）
            $("button.copyTreeAll").on("click", function(){
                var act = $(this).data("act");
                $("#copySrcTree ul.bom-sub").each(function(){
                    if (act === "expand"){ $(this).show(); }
                    else { $(this).hide(); }
                });
                $("#copySrcTree .tw").each(function(){
                    $(this).text(act === "expand" ? "-" : "+");
                });
            });
            $("#copySrcTree .lbl").css("cursor", "pointer");
            $("#btnSaveCopy").on("click", function(){ $(this).closest("form").submit(); });
            // 目标料号选择（新版 quick_item 风格：分类 + 物料列表）
            $("#btnPickCopyDst").on("click", function(){
                $.dialog({title:"选择目标物料", width:1280, height:860,
                    content:"url:BOMSetup.php?op=quick_item&target=copyDstNo&nameTarget=copyDstName&_r=" + Date.now(), lock:true});
            });
            </script>';

    } elseif ($op == 'upgrade') {
        // 升版弹窗：同一物料的新版本（未审核，不自动生效），完整复制源版本结构；
        // 升版仅创建新版本，不改变任何父 BOM 的引用关系（父行绑定保持不变）
        $asm   = isset($p['assembly']) ? trim($p['assembly']) : '';
        $srcVer = isset($p['version']) ? trim($p['version']) : '';
        $srcHdr = headerByVersion($db, $asm, $srcVer);
        $curVer = $srcHdr ? $srcHdr['version'] : $srcVer;
        // 建议新版本号：含点则递增最后一段（1.2→1.3）；纯数字则整体+1（1→2）；否则后缀 -1
        $suggest = '1';
        if (strpos($curVer, '.') !== false) {
            if (preg_match('/^(.*\.)(\d+)$/', $curVer, $m)) { $suggest = $m[1] . ((int)$m[2] + 1); }
            else { $suggest = $curVer . '.1'; }
        } else {
            if (is_numeric($curVer)) { $suggest = (string)((int)$curVer + 1); }
            else { $suggest = $curVer . '-1'; }
        }
        $name = itemName($db, $asm);
        echo '<form method="post" action="BOMSetup.php" onsubmit="return validateUpgrade(this)">' . $formIdField . '
            <input type="hidden" name="op" value="upgrade_save">
            <input type="hidden" name="dialog_op" value="upgrade">
            <input type="hidden" name="assembly" value="' . htmlspecialchars($asm) . '">
            <input type="hidden" name="src_version" value="' . htmlspecialchars($curVer) . '">
            <div style="font-family:Verdana,Arial,sans-serif;font-size:13px;color:#333;padding:6px">
            <div style="background:#e3f2fd;border:1px solid #90caf9;padding:10px;margin-bottom:12px;border-radius:4px;font-size:12px;color:#0d47a1;line-height:1.7">
            <b>升版说明：</b>为物料 <b>' . htmlspecialchars($asm . ' ' . $name) . '</b> 创建新版本 v' . htmlspecialchars($suggest) . '。<br>
            新版本将<b>完整复制 v' . htmlspecialchars($curVer) . ' 的结构</b>（子件/替代件/用量），状态为<b>未审核</b>，<b>不影响任何现有 BOM 的引用关系</b>。<br>
            如需让某个父 BOM 引用新版本，回到该父 BOM 的层级表，点击该子件行的"子件版本"切换即可（仅影响那一行）。
            </div>
            <table class="selection">
            <tr><td>物料</td><td><b>' . htmlspecialchars($asm . ' ' . $name) . '</b></td></tr>
            <tr><td>源版本</td><td>v' . htmlspecialchars($curVer) . ' <span style="color:#888;font-size:12px">（将被完整复制）</span></td></tr>
            <tr><td><span style="color:#c00">*</span> 新版本号</td><td><input name="new_version" value="' . htmlspecialchars($suggest) . '" size="10" required style="padding:4px 8px"></td></tr>
            <tr><td colspan="2" class="centre"><input type="submit" value="创建新版本" style="background:#1976D2;color:#fff;border:none;padding:6px 28px;border-radius:3px;cursor:pointer;font-size:13px;font-weight:bold"></td></tr>
            </table>
            </div>
            </form>
            <script>
            function validateUpgrade(f){
                if (!f.new_version.value.trim()){ alert("请填写新版本号！"); f.new_version.focus(); return false; }
                return true;
            }
            </script>';

    } elseif ($op == 'line_version') {
        // 层级表行级"切换子件版本"：列出该行子件的所有 BOM 版本供选择（仅影响本行引用）
        $lineId = isset($p['line_id']) ? (int)$p['line_id'] : 0;
        $lrow = null;
        if ($lineId > 0) {
            $r = DB_query("SELECT component_item, component_bom_header_id FROM bom_lines_all WHERE component_sequence_id='" . $lineId . "'", $db);
            $lrow = DB_fetch_array($r);
        }
        if (!$lrow) { echo '<p style="color:#c00">未找到该子件行！</p></body></html>'; exit; }
        $lvAsm = $lrow['component_item'];
        $lvAll = allVersions($db, $lvAsm);
        $lvCur = (int)$lrow['component_bom_header_id'];
        $lvName = itemName($db, $lvAsm);
        echo '<form method="post" action="BOMSetup.php" onsubmit="return confirm(\'确定将该子件的引用版本切换为所选版本？仅影响本行。\')">' . $formIdField . '
            <input type="hidden" name="op" value="set_line_version_bind">
            <input type="hidden" name="line_id" value="' . $lineId . '">
            <div style="font-family:Verdana,Arial,sans-serif;font-size:13px;color:#333;padding:6px">
            <div style="background:#e3f2fd;border:1px solid #90caf9;padding:10px;margin-bottom:12px;border-radius:4px;font-size:12px;color:#0d47a1;line-height:1.7">
            切换子件 <b>' . htmlspecialchars($lvAsm . ' ' . $lvName) . '</b> 在本 BOM 中引用的版本。<br>
            仅影响<b>本行</b>的引用关系，其他 BOM 及版本关系不变。
            </div>
            <table class="selection">';
        if (count($lvAll) == 0) {
            echo '<tr><td style="text-align:left">该子件没有 BOM 版本（叶子/原材料无需绑定版本）。</td></tr>';
        } else {
            foreach ($lvAll as $lv) {
                $lvChecked = ($lvCur > 0 && $lv['bom_header_id'] == $lvCur) ? ' checked' : '';
                $lvMark = ($lvCur > 0 && $lv['bom_header_id'] == $lvCur) ? '（当前绑定）' : '';
                echo '<tr><td style="text-align:left"><label><input type="radio" name="bind_hdr_id" value="' . $lv['bom_header_id'] . '"' . $lvChecked . '> v' . htmlspecialchars($lv['version']) . ' · ' . htmlspecialchars($lv['status'] == '' ? '未审核' : $lv['status']) . ' ' . $lvMark . '</label></td></tr>';
            }
        }
        echo '<tr><td class="centre"><input type="submit" value="确定切换" style="background:#1976D2;color:#fff;border:none;padding:6px 28px;border-radius:3px;cursor:pointer;font-size:13px;font-weight:bold"></td></tr>
            </table></div></form>';

    } elseif ($op == 'quick_item') {
        // 快捷添加/修改物料：用于"新建顶层BOM"弹窗中快速选择已有物料或新建物料，把物料代码写回父表单；
        // 或由物料属性面板"修改物料"按钮打开编辑模式（?edit=1&item_no=xxx），更新物料主数据后刷新父窗口
        // 选中物料后写回父窗口的输入框 id（默认 assembly；复制BOM 传 target=copyDstNo）
        $qiTarget = isset($_GET['target']) && preg_match('/^[A-Za-z][A-Za-z0-9_]*$/', $_GET['target']) ? $_GET['target'] : 'assembly';
        // 名称写入的输入框 id（默认 <target>Name；复制BOM 传 nameTarget=copyDstName）
        $qiNameTarget = isset($_GET['nameTarget']) && preg_match('/^[A-Za-z][A-Za-z0-9_]*$/', $_GET['nameTarget']) ? $_GET['nameTarget'] : ($qiTarget . 'Name');
        $isEdit = !empty($_GET['edit']);
        $editItemNo = isset($_GET['item_no']) ? $_GET['item_no'] : '';
        $cur = $isEdit ? getItemInfo($db, $editItemNo) : null;
        // restrict=fb 时仅显示成品/半成品（F/B），用于 BOMUpload2 等仅选半成品的场景
        $restrictFb = isset($_GET['restrict']) && $_GET['restrict'] === 'fb';
        // 字段值数据源：新建模式错误回显时优先 $p（POST 数据），否则编辑用 $cur，否则空
        $isPostBack = !$isEdit && isset($p['op']) && $p['op'] === 'quick_item_new_save';
        $src = $isPostBack ? $p : ($cur ?: array());
        $qiCats = '';
        $r = DB_query("SELECT DISTINCT item_category1 FROM sf_item_no WHERE item_category1<>'' ORDER BY item_category1", $db);
        while ($row = DB_fetch_array($r)) { $qiCats .= '<option>' . htmlspecialchars($row['item_category1']) . '</option>'; }
        // 加载所有可用物料（含分类、类型、用途），JSON 嵌入供前端按分类筛选
        $qiItems = array();
        // 一次性查物料 + 是否已在 bom_headers_all 里有头（LEFT JOIN 子查询去重，避免物料标记与 bom 头表不一致）
        // restrict=fb 时仅显示成品/半成品（F/B），用于 BOMUpload2 等仅选半成品的场景
        $qiWhere = $restrictFb ? " AND i.item_type IN ('F','B')" : '';
        $r = DB_query("SELECT i.item_no, i.item_name, i.item_type, i.item_category1, i.item_use,
                CASE WHEN i.item_type='M' OR h.assembly_item_no IS NULL THEN 0 ELSE 1 END AS has_bom,
                CASE WHEN i.item_type='M' OR h.assembly_item_no IS NULL THEN NULL
                     ELSE (SELECT MAX(h2.version) FROM bom_headers_all h2 WHERE h2.assembly_item_no = i.item_no) END AS latest_version,
                CASE WHEN i.item_type='M' OR h.assembly_item_no IS NULL THEN NULL
                     ELSE (SELECT h3.status FROM bom_headers_all h3 WHERE h3.assembly_item_no = i.item_no ORDER BY h3.is_current DESC, h3.bom_header_id DESC LIMIT 1) END AS latest_status
                FROM sf_item_no i
                LEFT JOIN (SELECT DISTINCT assembly_item_no FROM bom_headers_all) h ON h.assembly_item_no = i.item_no
                WHERE i.disable_flag<>'N'" . $qiWhere . "
                ORDER BY i.item_category1, i.item_no", $db);
        while ($row = DB_fetch_array($r)) { $qiItems[] = $row; }
        $qiCategories = array();
        foreach ($qiItems as $qiIt) {
            if ($qiIt['item_category1'] !== '' && !in_array($qiIt['item_category1'], $qiCategories)) {
                $qiCategories[] = $qiIt['item_category1'];
            }
        }
        $qiItemsJson = json_encode($qiItems, JSON_UNESCAPED_UNICODE);
        $qiCategoriesJson = json_encode($qiCategories, JSON_UNESCAPED_UNICODE);
        // 保留隐藏的 qiList select（旧 setParentAssembly 兼容读取）
        $opts = '';
        foreach ($qiItems as $qiIt) {
            $opts .= '<option value="' . htmlspecialchars($qiIt['item_no']) . '">' . htmlspecialchars($qiIt['item_no'] . ' ' . $qiIt['item_name']) . '</option>';
        }
        // 下拉框预填：编辑模式 $cur，新建模式错误回显 $p，否则空
        $selVal = function($key) use ($src) { return isset($src[$key]) ? $src[$key] : ''; };
        $curCat = $selVal('item_category1');
        $curType = $selVal('item_type');
        $curUse = $selVal('item_use');
        $curInsp = $selVal('inspect_flag');
        $curSo = $selVal('so_flag');
        $curDis = $selVal('disable_flag');
        $curLock = $selVal('suoding_flag');
        $v = function($key) use ($src) { return isset($src[$key]) ? htmlspecialchars($src[$key]) : ''; };
        // 重写分类下拉（每个 option 单独判断 selected）
        $catOpts = '<option value=""' . ($curCat === '' ? ' selected' : '') . '></option>';
        $r = DB_query("SELECT DISTINCT item_category1 FROM sf_item_no WHERE item_category1<>'' ORDER BY item_category1", $db);
        while ($row = DB_fetch_array($r)) {
            $catOpts .= '<option' . ($row['item_category1'] === $curCat ? ' selected' : '') . '>' . htmlspecialchars($row['item_category1']) . '</option>';
        }
        echo '<div style="font-family:Verdana,Arial,sans-serif;font-size:13px;color:#333">';
        if (!$isEdit) {
            echo '<div style="font-weight:bold;font-size:14px;color:#0d47a1;margin-bottom:8px">选择已有物料（点击左侧分类查看成员料，点击物料使用）</div>';
            echo '<div style="margin-bottom:8px;display:flex;gap:8px;align-items:center">
                    <input id="qiSearch" type="text" placeholder="过滤物料代码/名称..." style="flex:1;padding:6px 10px;border:1px solid #ccc;border-radius:3px" onkeyup="onQiSearch()">
                    <button type="button" onclick="useQiItem()" style="padding:6px 18px;background:#1976D2;color:#fff;border:none;border-radius:3px;cursor:pointer">使用选中的物料</button>
                    <button type="button" onclick="viewQiItem()" style="padding:6px 14px;background:#fff;border:1px solid #888;color:#555;border-radius:3px;cursor:pointer">查看详情</button>
                  </div>';
            echo '<div style="display:flex;gap:10px;height:360px">';
            echo '<div id="qiCatList" style="flex:0 0 170px;border:1px solid #e0e8f0;border-radius:4px;overflow-y:auto;background:#f9fbfd"></div>';
            echo '<div id="qiMatList" style="flex:1;border:1px solid #e0e8f0;border-radius:4px;overflow-y:auto;background:#fff"></div>';
            echo '</div>';
            echo '<div style="margin-top:8px;padding:8px;background:#fffbe6;border:1px solid #ffe58f;border-radius:3px;font-size:13px">
                    <span id="qiCurrentName" style="color:#888">未选择物料（点击列表中的物料）</span>
                    <input type="hidden" id="qiSelectedItemNo" value="">
                    <select id="qiList" style="display:none">' . $opts . '</select>
                  </div>';
            echo '<div style="border-top:1px dashed #c5c5c5;margin:14px 0 8px;padding-top:8px;font-weight:bold;font-size:14px;color:#0d47a1">或新增物料（创建后自动填入物料代码）</div>';
        } else {
            echo '<div style="font-weight:bold;font-size:14px;color:#0d47a1;margin-bottom:8px">修改物料主数据：' . htmlspecialchars($editItemNo) . '</div>';
        }
        echo '<form method="post" action="BOMSetup.php" onsubmit="return validateQiNew(this)" style="overflow-x:auto;max-width:100%">' . $formIdField . '
            <input type="hidden" name="op" value="' . ($isEdit ? 'quick_item_edit_save' : 'quick_item_new_save') . '">
            <input type="hidden" name="dialog_op" value="quick_item">
            <input type="hidden" name="target" value="' . htmlspecialchars($qiTarget) . '">
            <input type="hidden" name="name_target" value="' . htmlspecialchars($qiNameTarget) . '">
            <table class="selection" style="font-size:13px;min-width:1000px;width:max-content">
            <tr><td><span style="color:#c00">*</span> 物料代码</td><td><input name="item_no" id="qiItemNo" size="20" value="' . ($isEdit ? htmlspecialchars($editItemNo) : $v('item_no')) . '" required' . ($isEdit ? ' readonly style="background:#f5f5f5"' : '') . '>' . ($isEdit ? '' : '<button type="button" onclick="genItemNo(\'#qiItemNo\')" style="margin-left:5px;padding:2px 8px">随机生成</button>') . '</td>
                <td><span style="color:#c00">*</span> 物料名称</td><td><input name="item_name" size="30" value="' . $v('item_name') . '" required></td></tr>
            <tr><td>规格型号</td><td><input name="item_desc" size="20" value="' . $v('item_desc') . '"></td>
                <td><span style="color:#c00">*</span> 单位</td><td><input name="units" size="10" value="' . $v('units') . '"' . ($isEdit ? '' : ' required') . '></td></tr>
            <tr><td><span style="color:#c00">*</span> 分类</td><td><select name="item_category1"' . ($isEdit ? '' : ' required') . '>' . $catOpts . '</select></td>
                <td><span style="color:#c00">*</span> 类型</td><td><select name="item_type"' . ($isEdit ? '' : ' required') . '><option value=""></option><option value="M"' . ($curType === 'M' ? ' selected' : '') . '>M原材料</option><option value="B"' . ($curType === 'B' ? ' selected' : '') . '>B半成品</option><option value="F"' . ($curType === 'F' ? ' selected' : '') . '>F成品</option></select></td></tr>
            <tr><td>用途</td><td><select name="item_use"><option value=""></option><option value="S"' . ($curUse === 'S' ? ' selected' : '') . '>S生产</option><option value="Y"' . ($curUse === 'Y' ? ' selected' : '') . '>Y研发</option></select></td>
                <td><span style="color:#c00">*</span> 项目名</td><td><input name="project_name" size="20" value="' . ($v('project_name') !== '' ? $v('project_name') : '常规') . '" required></td></tr>
            <tr><td>检验标志</td><td><select name="inspect_flag"><option value=""></option><option value="Y"' . ($curInsp === 'Y' ? ' selected' : '') . '>Y</option><option value="N"' . ($curInsp === 'N' ? ' selected' : '') . '>N</option></select></td>
                <td>工艺</td><td><input name="gongyi" size="20" value="' . $v('gongyi') . '"></td></tr>
            <tr><td>采购价</td><td><input name="unit_price" size="10" value="' . $v('unit_price') . '"></td>
                <td>最小订购量</td><td><input name="min_order" size="10" value="' . $v('min_order') . '"></td></tr>
            <tr><td>仓库</td><td><input name="sub_code" size="10" value="' . $v('sub_code') . '"></td>
                <td>有效期</td><td><input name="youxiaoqi" size="10" value="' . $v('youxiaoqi') . '"></td></tr>
            <tr><td>温度</td><td><input name="wendu" size="10" value="' . $v('wendu') . '"></td>
                <td>光照</td><td><input name="light" size="10" value="' . $v('light') . '"></td></tr>
            <tr><td>湿度</td><td><input name="shidu" size="10" value="' . $v('shidu') . '"></td>
                <td>状态</td><td><input name="conditions" size="10" value="' . $v('conditions') . '"></td></tr>
            <tr><td>可售标志</td><td><select name="so_flag"><option value=""></option><option value="Y"' . ($curSo === 'Y' ? ' selected' : '') . '>Y</option><option value="N"' . ($curSo === 'N' ? ' selected' : '') . '>N</option></select></td>
                <td>停用</td><td><select name="disable_flag"><option value=""></option><option value="Y"' . ($curDis === 'Y' ? ' selected' : '') . '>Y</option><option value="N"' . ($curDis === 'N' ? ' selected' : '') . '>N</option></select></td></tr>
            <tr><td>锁定</td><td><select name="suoding_flag"><option value=""></option><option value="Y"' . ($curLock === 'Y' ? ' selected' : '') . '>Y</option><option value="N"' . ($curLock === 'N' ? ' selected' : '') . '>N</option></select></td>
                <td>物料状态</td><td><input name="item_status" size="10" value="' . $v('item_status') . '"></td></tr>
            <tr><td colspan="4" class="centre"><input type="submit" value="' . ($isEdit ? '保存修改' : '新建物料并填入') . '"></td></tr>
            </table></form>';
        echo '</div>';
        // heredoc 一次性输出整个 <script> 块（避免 PHP 单引号字符串里 JS 单引号提前闭合的问题）
        echo <<<QIJS
        <script>
            var qiItems = {$qiItemsJson};
            var qiCategories = {$qiCategoriesJson};
            var qiCurCat = "";
            var qiCurFilter = "";
            var qiSelected = "";
            var qiTypeColor = {F:"#bbdefb", B:"#ffe0b2", M:"#c8e6c9", P:"#f8bbd0", "":"#e0e0e0"};
            var qiTypeName = {F:"成品", B:"半成品", M:"原材料", P:"包装物", "":"其他"};
            function renderQiCategories(){
                var html = "";
                var cntAll = qiItems.length;
                html += '<div class="qi-cat-li" data-cat="" style="padding:9px 12px;cursor:pointer;font-weight:bold;color:#0d47a1;border-bottom:1px solid #e0e8f0;background:#e3f2fd">全部 <span style="color:#888;font-weight:normal;font-size:12px;float:right">(' + cntAll + ')</span></div>';
                qiCategories.forEach(function(c){
                    var cnt = 0; for (var i = 0; i < qiItems.length; i++) if (qiItems[i].item_category1 === c) cnt++;
                    html += '<div class="qi-cat-li" data-cat="' + c + '" style="padding:9px 12px;cursor:pointer;border-bottom:1px solid #f0f0f0">' + c + '<span style="color:#888;font-size:12px;float:right">(' + cnt + ')</span></div>';
                });
                $("#qiCatList").html(html);
                $("#qiCatList .qi-cat-li").off("click").on("click", function(){
                    $("#qiCatList .qi-cat-li").css({"background":"#f9fbfd","font-weight":"normal","color":"#333"});
                    $(this).css({"background":"#e3f2fd","font-weight":"bold","color":"#0d47a1"});
                    qiCurCat = $(this).data("cat");
                    renderQiMaterials();
                });
            }
            function renderQiMaterials(){
                var html = "";
                var list = [];
                for (var i = 0; i < qiItems.length; i++){
                    var it = qiItems[i];
                    if (qiCurCat && it.item_category1 !== qiCurCat) continue;
                    if (qiCurFilter){
                        var t = (it.item_no + " " + it.item_name).toLowerCase();
                        if (t.indexOf(qiCurFilter) < 0) continue;
                    }
                    list.push(it);
                }
                if (list.length === 0){
                    html = '<div style="padding:40px 20px;text-align:center;color:#888;font-size:13px">该分类下没有物料</div>';
                } else {
                    html += '<div style="display:flex;background:#eef4fb;border-bottom:1px solid #d0dceb;font-size:12px;font-weight:bold;color:#555;position:sticky;top:0;z-index:1">'
                        + '<div style="flex:0 0 20px;padding:6px 4px"></div>'
                        + '<div style="flex:0 0 18px;padding:6px 4px"></div>'
                        + '<div style="flex:0 0 90px;padding:6px 8px">物料编码</div>'
                        + '<div style="flex:1 1 auto;min-width:140px;padding:6px 4px">物料名称</div>'
                        + '<div style="flex:0 0 60px;padding:6px 4px">类型</div>'
                        + '<div style="flex:0 0 90px;padding:6px 4px">用途</div>'
                        + '<div style="flex:0 0 100px;padding:6px 4px">分类</div>'
                        + '<div style="flex:0 0 64px;padding:6px 4px;text-align:center">BOM</div>'
                        + '<div style="flex:0 0 80px;padding:6px 4px;text-align:center">最新版本</div>'
                        + '</div>';
                    list.forEach(function(it){
                        var fill = qiTypeColor[it.item_type] || "#e0e0e0";
                        var sel = (qiSelected === it.item_no) ? "background:#e3f2fd;" : "";
                        var checked = (qiSelected === it.item_no) ? " checked" : "";
                        // 有 BOM 标签（绿/灰）+ 最新版本显示
                        var bomLabel = it.has_bom == 1
                            ? '<span style="display:inline-block;background:#e8f5e9;color:#2e7d32;border:1px solid #a5d6a7;border-radius:3px;padding:1px 6px;font-size:11px;font-weight:600">有 BOM</span>'
                            : '<span style="display:inline-block;background:#f5f5f5;color:#999;border:1px solid #e0e0e0;border-radius:3px;padding:1px 6px;font-size:11px">无</span>';
                        var verText = it.has_bom == 1 ? ('v' + (it.latest_version || '?')) : '-';
                        var verColor = it.latest_status === '已审核' ? '#2e7d32' : (it.latest_status === '待签核' || it.latest_status === '未审核' ? '#ef6c00' : '#888');
                        html += '<div class="qi-mat-li" data-code="' + it.item_no + '" '
                            + 'style="display:flex;align-items:center;padding:7px 6px;border-bottom:1px solid #f0f0f0;cursor:pointer;' + sel + '">'
                            + '<div style="flex:0 0 20px;text-align:center"><input type="checkbox" class="qi-mat-cb" data-code="' + it.item_no + '"' + checked + '></div>'
                            + '<div style="width:24px;height:18px;background:' + fill + ';border:1px solid #aaa;border-radius:3px;margin-right:8px;align-self:center"></div>'
                            + '<div style="flex:0 0 90px;font-weight:bold;font-family:Consolas,monospace">' + it.item_no + '</div>'
                            + '<div style="flex:1 1 auto;min-width:140px">' + it.item_name + '</div>'
                            + '<div style="flex:0 0 60px;color:#666;font-size:12px">' + (qiTypeName[it.item_type] || '其他') + '</div>'
                            + '<div style="flex:0 0 90px;color:#888;font-size:12px">' + (it.item_use==="S"?"生产":it.item_use==="Y"?"研发":"") + '</div>'
                            + '<div style="flex:0 0 100px;color:#888;font-size:12px">' + (it.item_category1 || '') + '</div>'
                            + '<div style="flex:0 0 64px;text-align:center">' + bomLabel + '</div>'
                            + '<div style="flex:0 0 80px;text-align:center;font-family:Consolas,monospace;color:' + verColor + ';font-size:12px">' + verText + '</div>'
                            + '</div>';
                    });
                }
                $("#qiMatList").html(html);
                // 容器改 grid 布局（保证表头/数据行所有列严格对齐——不受 padding/box-sizing 影响）
                $("#qiMatList").css({
                    display: 'grid',
                    gridTemplateColumns: '20px 28px 90px minmax(140px,1fr) 60px 90px 100px 64px 80px',
                    gap: 0,
                    alignItems: 'stretch'
                });
                // 每个行 div 用 display:contents——子元素按容器 grid 列对齐（不再受 padding 影响）
                $("#qiMatList > div").css("display", "contents");
                $("#qiMatList").off("click", ".qi-mat-li").on("click", ".qi-mat-li", function(){  // 先 off 防累积
                    qiSelected = $(this).attr("data-code");  // attr 保持字符串
                    $("#qiSelectedItemNo").val(qiSelected);
                    $("#qiList").val(qiSelected);
                    var it = null; for (var i = 0; i < qiItems.length; i++) if (qiItems[i].item_no === qiSelected) { it = qiItems[i]; break; }
                    $("#qiCurrentName").html('已选中: <b style="color:#0d47a1">' + it.item_no + ' ' + it.item_name + '</b>');
                    renderQiMaterials();
                });
                $("#qiMatList").off("dblclick", ".qi-mat-li").on("dblclick", ".qi-mat-li", function(){  // 先 off 防累积
                    qiSelected = $(this).attr("data-code");  // attr 保持字符串
                    setParentAssembly(qiSelected);
                });
            }
            function onQiSearch(){
                qiCurFilter = $("#qiSearch").val().toLowerCase();
                renderQiMaterials();
            }
            function filterQiList(kw){}
            function genItemNo(sel){
                $.getJSON("BOMSetup.php?op=gen_item_no&_r=" + Date.now(), function(res){
                    if (res && res.ok) { $(sel).val(res.item_no); }
                    else { alert("随机生成失败，请重试"); }
                });
            }
            function useQiItem(){
                if (!qiSelected) { alert("请先点击选择一个物料！"); return; }
                setParentAssembly(qiSelected);
            }
            function viewQiItem(){
                if (!qiSelected) { alert("请先点击选择一个物料！"); return; }
                $.dialog({title:"物料详情 " + qiSelected, width:720, height:500,
                    content:"url:BOMSetup.php?op=quick_item_detail&item_no=" + encodeURIComponent(qiSelected) + "&_r=" + Date.now(), lock:true});
            }
            function setParentAssembly(code){
                // 目标输入框所在窗口：lhgdialog 模式用 frameElement.api.opener；简易模态框模式（BOMUpload2）parent 即目标页
                var W;
                if (window.frameElement && window.frameElement.api) {
                    W = window.frameElement.api.opener;
                } else {
                    W = window.parent;
                }
                var inp = W.document.getElementById("{$qiTarget}");
                if (inp) inp.value = code;
                var nameInp = W.document.getElementById("{$qiNameTarget}");
                if (nameInp && code){
                    // 名称从 qiList（隐藏的完整物料下拉）取当前项的名称部分
                    var opt = $("#qiList option[value=\"" + code + "\"]").first();
                    if (opt.length){
                        var t = opt.text().split(" ");
                        t.shift();
                        nameInp.value = t.join(" ");
                    }
                }
                // 关闭：lhgdialog 模式 frameElement.api.close()；简易模态框模式调用父页面 closeQiDialog()
                if (window.frameElement && window.frameElement.api) {
                    window.frameElement.api.close();
                } else if (window.parent && typeof window.parent.closeQiDialog === 'function') {
                    window.parent.closeQiDialog();
                }
            }
            function validateQiNew(f){
                if (f.item_no.value.trim()==="") { alert("请填写物料代码！"); f.item_no.focus(); return false; }
                if (f.item_name.value.trim()==="") { alert("请填写物料名称！"); f.item_name.focus(); return false; }
                if (f.project_name.value.trim()==="") { alert("请填写项目名！"); f.project_name.focus(); return false; }
                return true;
            }
            renderQiCategories();
            renderQiMaterials();
        </script>
QIJS;

    } elseif ($op == 'create_top') {
        // 支持 ?assembly=xxx 预填（右键"新建BOM"带当前物料代码）
        $prefillAssembly = isset($p['assembly']) ? $p['assembly'] : '';
        echo '<form method="post" action="BOMSetup.php" onsubmit="return validateCreateTop(this)">' . $formIdField . '
            <input type="hidden" name="op" value="create_top_save">
            <input type="hidden" name="dialog_op" value="create_top">
            <table class="selection">
            <tr><td>物料代码</td><td>
                <input name="assembly" id="assembly" size="25" maxlength="100" required placeholder="输入物料代码" value="' . htmlspecialchars($prefillAssembly) . '">
                <button type="button" id="btnQuickItem" style="padding:3px 10px;margin-left:6px">快捷添加物料</button>
            </td></tr>
            <tr><td>版本</td><td><input name="version" value="1" size="10" maxlength="11" required pattern="[0-9a-zA-Z._-]*"></td></tr>
            <tr><td colspan="2" class="centre"><input type="submit" value="创建BOM"></td></tr>
            </table></form>
            <script>
            function validateCreateTop(f){
                var n = f.assembly.value.trim();
                if (n === "") { alert("请填写物料代码！"); f.assembly.focus(); return false; }
                if (/[\'\"\\\\<>\x00-\x1F]/.test(n)) { alert("物料代码不能包含引号、反斜杠、控制字符！"); f.assembly.focus(); return false; }
                return true;
            }
            $("#btnQuickItem").on("click", function(){
                $.dialog({title:"快捷添加物料", width:1280, height:860,
                    content:"url:BOMSetup.php?op=quick_item&_r=" + Date.now(), lock:true});
            });
            </script>';

    } elseif ($op == 'edit_line') {
        $ln = null;
        $phdr = latestHeader($db, $parent);
        if ($phdr) {
            $r = DB_query("SELECT component_item,component_quantity,operation_seq_num,sunhao_rate,weizhi,component_remarks
                FROM bom_lines_all WHERE component_sequence_id='" . esc($db, $lineId) . "' AND bom_header_id='" . esc($db, $phdr['bom_header_id']) . "'", $db);
            $ln = DB_fetch_array($r);
        }
        if (!$ln) {
            echo '<p style="color:#c00">子件行不存在或已被删除。</p>';
        } else {
            echo '<form method="post" action="BOMSetup.php">' . $formIdField . '
                <input type="hidden" name="op" value="edit_line_save">
                <input type="hidden" name="dialog_op" value="edit_line">
                <input type="hidden" name="line_id" value="' . htmlspecialchars($lineId) . '">
                <input type="hidden" name="parent" value="' . htmlspecialchars($parent) . '">
                <table class="selection">
                <tr><td>父件</td><td>' . htmlspecialchars($parent) . '</td></tr>
                <tr><td>子件料号</td><td>' . htmlspecialchars($ln['component_item']) . '</td></tr>
                <tr><td>用量</td><td><input name="component_quantity" value="' . htmlspecialchars($ln['component_quantity']) . '" size="10"></td></tr>
                <tr><td>制程</td><td><input name="operation_seq_num" value="' . htmlspecialchars($ln['operation_seq_num']) . '" size="10"></td></tr>
                <tr><td>自损率</td><td><input name="sunhao_rate" value="' . htmlspecialchars($ln['sunhao_rate']) . '" size="10"></td></tr>
                <tr><td>位置</td><td><input name="weizhi" value="' . htmlspecialchars($ln['weizhi']) . '" size="30"></td></tr>
                <tr><td>备注</td><td><input name="component_remarks" value="' . htmlspecialchars($ln['component_remarks']) . '" size="40"></td></tr>
                <tr><td colspan="2" class="centre"><input type="submit" value="保存修改"></td></tr>
                </table></form>';
        }

    } elseif ($op == 'del_line_confirm') {
        // 查询子件行信息 + 被删子件本身的引用情况，让用户知道该料号还在多少个 BOM 里被用
        $lnR = DB_query("SELECT bom_header_id, assembly_item_no, component_item FROM bom_lines_all WHERE component_sequence_id='" . esc($db, $lineId) . "'", $db);
        $ln = DB_fetch_array($lnR);
        // 已审核 BOM 提前拦截：不渲染删除表单，直接提示（避免走完整流程才告知）
        if ($ln) {
            $phdrCk = latestHeader($db, $ln['assembly_item_no']);
            if ($phdrCk && $phdrCk['status'] == '已审核') {
                echo '<p style="color:#c00;font-weight:bold">该 BOM（v' . htmlspecialchars($phdrCk['version']) . '）已审核，不允许删除子件！请通过 "复制BOM" 创建新版本后修改。</p>';
                echo '</body></html>';
                exit;
            }
        }
        echo '<form method="post" action="BOMSetup.php">' . $formIdField . '
            <input type="hidden" name="op" value="del_line">
            <input type="hidden" name="dialog_op" value="del_line_confirm">
            <input type="hidden" name="line_id" value="' . htmlspecialchars($lineId) . '">
            <input type="hidden" name="parent" value="' . htmlspecialchars($parent) . '">';
        if ($ln) {
            // 查被删子件（001-220-0001）本身被多少个 BOM 当子件引用——**不是查母件**，和删除操作无关
            $itemRefCount = refCount($db, $ln['component_item']);
            echo '<p style="color:#c00;font-weight:bold">删除子件行：' . htmlspecialchars($ln['component_item']) . '（在 ' . htmlspecialchars($ln['assembly_item_no']) . ' 下的引用）</p>';
            if ($itemRefCount >= 2) {
                echo '<p>该子件（' . htmlspecialchars($ln['component_item']) . '）还被 ' . $itemRefCount . ' 个 BOM 引用，<b>删除本行不会影响其他 BOM 中的引用</b>。</p>';
            }
            echo '<p>是否确定删除？</p>';
        } else {
            echo '<p style="color:#c00;font-weight:bold">未找到该子件行（可能已被删除）！</p>';
        }
        echo '<p class="centre"><input type="submit" value="确认删除" style="background:#c00;color:#fff"></p>
            </form>';
    }
    echo '</body></html>';
}

/* ============================================================
 * 主页面（BOM 树）
 * ============================================================ */

// 顶层 BOM：未被任何有效子件引用的母件
$topSql = "SELECT DISTINCT assembly_item_no FROM bom_headers_all
           WHERE assembly_item_no NOT IN (SELECT DISTINCT component_item FROM bom_lines_all WHERE disable_date=0)
           ORDER BY assembly_item_no";
$topRes = DB_query($topSql, $db);
$topAsms = array();
while ($row = DB_fetch_array($topRes)) { $topAsms[] = $row['assembly_item_no']; }

// 左侧 BOM 结构树：按参考图样式渲染。
// prefixLines: bool[]，长度=当前深度，表示每一级祖先是否有后续兄弟节点（有则画垂直虚线）
function renderForest($db, $parentAssembly, $items, $path, $prefixLines = array(), $pathApproved = 0, $parentStatus = '', $viewAsm = '', $viewHdrId = 0) {
    $count = count($items);
    $idx = 0;
    foreach ($items as $it) {
        $isLast = (++$idx === $count);
        $item = $it['item'];
        $lineId = isset($it['line_id']) ? $it['line_id'] : '';
        $lockHdrId = isset($it['lock_hdr']) ? intval($it['lock_hdr']) : 0;
        if (in_array($item, $path, true)) continue;
        // 节点 BOM 头解析优先级：view_hdr(查看版本，递归贯穿子树) > lock_hdr(行绑定) > 最新默认
        if ($item === $viewAsm && $viewHdrId > 0) {
            $hdr = headerById($db, $viewHdrId);
            if (!$hdr) $hdr = latestHeader($db, $item);
        } elseif ($lockHdrId > 0) {
            $hdr = headerById($db, $lockHdrId);
            if (!$hdr) $hdr = latestHeader($db, $item);
        } else {
            $hdr = latestHeader($db, $item);
        }
        $activeLines = $hdr ? getActiveLines($db, $hdr['bom_header_id']) : array();
        $isAsm = $hdr ? true : false;
        $raw = isRaw($db, $item) ? 1 : 0;
        $name = itemName($db, $item);
        $version = $isAsm ? $hdr['version'] : '';
        $status = $isAsm ? $hdr['status'] : '';
        $leaf = ($isAsm && count($activeLines) > 0) ? 0 : 1;
        $type = itemType($db, $item);
        $label = $name . ' (' . $item . ')' . ($isAsm ? ' [v' . $version . ']' : '');
        $isTop = empty($prefixLines);

        echo '<li class="bom-node ' . ($leaf ? 'leaf-node' : '') . ($isTop ? ' top-level' : '') . '"'
            . ' data-assembly="' . htmlspecialchars($item) . '"'
            . ' data-parent-assembly="' . htmlspecialchars($parentAssembly) . '"'
            . ' data-parent-hdr-id="' . (isset($it['parent_hdr']) ? intval($it['parent_hdr']) : 0) . '"'
            . ' data-version="' . htmlspecialchars($version) . '"'
            . ' data-status="' . htmlspecialchars($status) . '"'
            . ' data-parent-status="' . htmlspecialchars($parentStatus) . '"'
            . ' data-raw="' . $raw . '"'
            . ' data-leaf="' . $leaf . '"'
            . ' data-line-id="' . htmlspecialchars($lineId) . '"'
            . ' data-parent-ref-count="' . ($parentAssembly ? refCount($db, $parentAssembly) : 0) . '"'
            . ' data-ref-count="' . refCount($db, $item) . '">';

        // 节点内容单独一行（.bom-row），子 <ul> 在下一行
        echo '<div class="bom-row">';
        // 树线列：折叠符与虚线在同一列；同级节点严格左对齐
        echo '<span class="bom-glyphs">';
        foreach ($prefixLines as $hasNextSibling) {
            echo '<span class="tree-cell indent-cell' . ($hasNextSibling ? '' : ' no-sibling') . '">'
                . '<span class="tree-vbar"></span></span>';
        }
        echo '<span class="tree-cell node-cell">';
        if (!$isTop) {
            echo '<span class="tree-hbar"></span>';
        }
        if ($isAsm && !$leaf) {
            // checkbox + 折叠符（.tw）：有生效子件时才显示，空 BOM 头显示为叶子
            echo '<input type="checkbox">';
            echo '<span class="tw" title="展开/折叠">-</span>';
        }
        echo '</span>';
        echo '</span>';

        // 深色 = 自身 BOM 已审核 或 路径上父级已审核（方案 B：已审核结构整棵深色）
        $nodeDeep = ($pathApproved || ($hdr && $hdr['status'] == '已审核')) ? 1 : 0;
        echo '<span class="icon-label">'
            . itemIcon($type, 'lg', $isTop, $nodeDeep)
            . '<span class="lbl" title="' . htmlspecialchars($label) . '">' . htmlspecialchars($label) . '</span>'
            . '</span>';
        echo '</div>';

        if ($isAsm && !$leaf) {
            $childItems = array();
            foreach ($activeLines as $ln) {
                $childItems[] = array('item' => $ln['component_item'], 'line_id' => $ln['component_sequence_id'], 'lock_hdr' => $ln['component_bom_header_id'], 'parent_hdr' => $hdr['bom_header_id']);
            }
            if ($childItems) {
                $childPrefix = $prefixLines;
                // 若当前节点不是最后一个兄弟，则向下的垂直线需要延续给子树
                $childPrefix[] = !$isLast;
                echo '<ul class="bom-sub">';
                renderForest($db, $item, $childItems, array_merge($path, array($item)), $childPrefix, $nodeDeep, $status, $viewAsm, $viewHdrId);
                echo '</ul>';
            }
        }
        echo '</li>';
    }
}

// 渲染物料属性网格（Tab 内容）
function renderItemDocsPanel($db, $item_no) {
	// 渲染"图文文档"Tab 内容：当前物料关联的文档清单（来自文档工作区 doc_master）
	global $RootPath;
	$sql = "SELECT m.doc_id, m.doc_code, m.doc_name, m.doc_type, m.status, m.current_version, m.remark, m.created_by, m.creation_date,
	    f.file_patch, f.file_ext, f.file_size,
	    (SELECT folder_name FROM doc_folder df WHERE df.folder_id=m.folder_id) AS folder_name,
	    (SELECT action FROM doc_log WHERE doc_id=m.doc_id ORDER BY action_date DESC LIMIT 1) AS last_action
	    FROM doc_master m LEFT JOIN doc_file f ON f.doc_id=m.doc_id
	    AND f.file_id = (SELECT MAX(file_id) FROM doc_file df WHERE df.doc_id=m.doc_id AND df.is_current='Y')
	    WHERE m.item_no='" . $item_no . "' AND m.status<>'已删除' ORDER BY m.creation_date DESC";
	$r = DB_query($sql, $db);
	$docs = array();
	while ($row = DB_fetch_array($r)) { $docs[] = $row; }
	echo '<div class="hier-toolbar">';
	echo '<span class="version-tag">物料 <b>' . htmlspecialchars($item_no) . '</b> 关联文档 共 <b>' . count($docs) . '</b> 个</span>';
	echo '<a href="' . $RootPath . '/DocPLM.php?item=' . urlencode($item_no) . '" target="_blank" style="background:#2196F3;color:#fff;border:1px solid #1976D2;padding:5px 14px;border-radius:3px;font-size:12px;text-decoration:none;margin-left:auto;">⇧ 打开文档工作区上传</a>';
	echo '</div>';
	if (count($docs) == 0) {
		echo '<div class="empty-tip" style="padding:40px 20px;text-align:center;color:#999;font-size:13px;">该物料暂未关联任何文档<br><span style="color:#aaa;font-size:12px;">点上方"打开文档工作区上传"按钮，物料号自动填入</span></div>';
		return;
	}
	echo '<div style="flex:1;overflow:auto;border:1px solid #e0e0e0;border-radius:3px;margin-top:6px;">';
	echo '<table class="bom-hier-table"><thead><tr>'
		. '<th width="40">序号</th><th width="44">图标</th><th width="80">状态</th><th width="26%">文件名称</th><th width="56">后缀</th><th width="100">文件编码</th><th width="80">大小</th><th width="14%">所在目录</th><th width="120">最近操作</th><th width="140">操作</th>'
		. '</tr></thead><tbody>';
	$i = 1;
	foreach ($docs as $d) {
		$fext = strtolower($d['file_ext']);
		$iconHtml = in_array($fext, array('jpg','jpeg','png','gif','bmp')) ? '🖼' : ($fext === 'pdf' ? '📕' : '📄');
		$stClass = $d['status']==='正常' ? 'plm-tag st-ok' : ($d['status']==='已归档' ? 'plm-tag st-arch' : ($d['status']==='已废止' ? 'plm-tag st-abd' : 'plm-tag st-del'));
		echo '<tr>';
		echo '<td>' . $i . '</td>';
		echo '<td style="font-size:18px;text-align:center;">' . $iconHtml . '</td>';
		echo '<td><span class="' . $stClass . '">' . htmlspecialchars($d['status']) . '</span></td>';
		echo '<td class="left">' . htmlspecialchars($d['doc_name']) . ' <span style="color:#999;font-size:11px;">[' . htmlspecialchars($d['current_version']) . ']</span></td>';
		echo '<td>' . strtoupper($fext) . '</td>';
		echo '<td>' . htmlspecialchars($d['doc_code']) . '</td>';
		echo '<td>' . ($d['file_size'] > 0 ? (round($d['file_size'] / 1024, 1) . ' KB') : '—') . '</td>';
		echo '<td class="left">' . htmlspecialchars($d['folder_name']) . '</td>';
		echo '<td>' . ($d['last_action'] != '' ? htmlspecialchars($d['last_action']) : '—') . '</td>';
		echo '<td class="op">';
		echo '<a href="' . $RootPath . '/DocPLM.php?op=versions&doc_id=' . intval($d['doc_id']) . '" target="_blank" style="margin-right:6px;">历史版本</a>';
		if ($d['file_patch'] && file_exists($d['file_patch'])) {
			echo '<a href="' . $RootPath . '/' . $d['file_patch'] . '" download="' . htmlspecialchars($d['doc_name'] . ($fext !== '' ? '.' . $fext : '')) . '">下载</a>';
		}
		echo '</td>';
		echo '</tr>';
		$i++;
	}
	echo '</tbody></table></div>';
}

function renderItemAttrPanel($db, $item_no) {
    $info = getItemInfo($db, $item_no);
    echo '<div class="attr-panel"><div class="attr-grid">';
    if ($info) {
        // 不再过滤空值，统一展示所有字段（无值显示 -）；Y/N 状态字段转"是/否"更直观
        $skipKeys = array('item_id','password');
        foreach ($info as $k => $v) {
            if (in_array($k, $skipKeys, true)) continue;
            if (stripos($k, 'date') !== false && is_numeric($v)) $v = date('Y-m-d H:i:s', $v);
            $label = fieldLabel($k);
            if ($v === '' || $v === null) {
                $display = '—';
            } elseif ($v === 'Y') {
                $display = '是';
            } elseif ($v === 'N') {
                $display = '否';
            } else {
                $display = $v;
            }
            echo '<div class="attr-item"><span class="attr-label">' . htmlspecialchars($label) . ':</span><span class="attr-value">' . htmlspecialchars($display) . '</span></div>';
        }
        echo '</div>';
        // 底部"修改物料"按钮，打开 quick_item 编辑模式（带 item_no）
        echo '<div style="margin-top:14px;text-align:right;padding:8px;border-top:1px dashed #e0e0e0">'
           . '<button type="button" onclick="openEditItem(\'' . htmlspecialchars(addslashes($item_no)) . '\')" style="padding:6px 18px;background:#1976D2;color:#fff;border:none;border-radius:3px;cursor:pointer;font-size:13px">修改物料</button>'
           . '</div>';
        echo '<script>
        function openEditItem(code){
            $.dialog({title:"修改物料 " + code, width:1280, height:860,
                content:"url:BOMSetup.php?op=quick_item&edit=1&item_no=" + encodeURIComponent(code) + "&_r=" + Date.now(), lock:true});
        }
        </script>';
    } else {
        echo '<div class="attr-item"><span class="attr-value">未找到物料信息。</span></div>';
    }
    echo '</div>';
}

// 右侧层级：渲染为可折叠树形表；hierCode 如 0 / 1 / 1.1 / 2.1.3
function renderHierarchy($db, $assembly, $level, $hierCode, $path, $parentQty = 1, $version = '', $pathApproved = 0) {
    $hdr = headerByVersion($db, $assembly, $version);
    if (!$hdr) return;
    $lines = getActiveLines($db, $hdr['bom_header_id']);
    $idx = 0;
    foreach ($lines as $ln) {
        $idx++;
        $childCode = ($hierCode == '0') ? (string)$idx : $hierCode . '.' . $idx;
        $info = getItemInfo($db, $ln['component_item']);
        $nm = $info ? $info['item_name'] : '';
        $type = $info ? $info['item_type'] : '';
        $totalQty = $parentQty * (float)$ln['component_quantity'];
        $childHdr = resolveChildHeader($db, $ln);
        $hasChild = ($childHdr && count(getActiveLines($db, $childHdr['bom_header_id'])) > 0) ? 1 : 0;
        // 深色 = 自身 BOM 已审核 或 路径上父级已审核（方案 B：叶子随已审核母件变深）
        $rowDeep = ($pathApproved || ($childHdr && $childHdr['status'] == '已审核')) ? 1 : 0;
        $key = $childCode;
        // 缩进：每级一个等宽占位，不显示 ├─/│ 等分支字符
        $indentHtml = '';
        for ($i = 0; $i < $level; $i++) {
            $indentHtml .= '<span class="hier-indent"></span>';
        }
        // 有子 BOM 才显示折叠三角；原料/叶子不显示
        $toggle = $hasChild
            ? '<span class="tw2" title="展开/折叠">▾</span>'
            : '<span class="tw2 leaf"></span>';
        // 子件版本单元格：有 BOM 的子件显示当前绑定版本（可点击切换）；叶子显示 —
        $childVerTxt = '—';
        if ($childHdr) {
            $cv = ($childHdr['version'] === '' || $childHdr['version'] === null) ? '—' : $childHdr['version'];
            $childVerTxt = '<a href="javascript:void(0)" class="ver-bind" title="点击切换该子件引用的 BOM 版本" data-line-id="' . htmlspecialchars($ln['component_sequence_id']) . '" data-asm="' . htmlspecialchars($ln['component_item']) . '" style="color:#1976D2;text-decoration:underline;cursor:pointer;border-bottom:1px dashed #1976D2">v' . htmlspecialchars($cv) . '</a>';
        }
        echo '<tr class="' . ($hasChild ? 'bom-parent' : '') . '" data-level="' . ($level + 1) . '"'
            . ' data-key="' . htmlspecialchars($key) . '" data-parent="' . htmlspecialchars($hierCode) . '"'
            . ' data-line-id="' . htmlspecialchars($ln['component_sequence_id']) . '"'
            . ' data-asm="' . htmlspecialchars($ln['component_item']) . '"'
            . ' data-asm-name="' . htmlspecialchars($nm) . '">
            <td class="hier-level col-code">' . $indentHtml . $toggle . itemIcon($type, 'sm', false, $rowDeep) . '<span class="hier-code">' . htmlspecialchars($childCode) . '</span></td>
            <td class="hier-lvlnum col-level">' . ($level + 1) . '</td>
            <td class="number col-qty"><a href="javascript:void(0)" class="qty-edit" title="点击修改该子件用量" style="color:#1976D2;text-decoration:underline;cursor:pointer;border-bottom:1px dashed #1976D2">' . htmlspecialchars($ln['component_quantity']) . '</a></td>
            <td class="number col-totalQty">' . $totalQty . '</td>
            <td class="col-itemNo">' . htmlspecialchars($ln['component_item']) . '</td>
            <td class="col-childVer">' . $childVerTxt . '</td>
            <td class="col-itemName" title="' . htmlspecialchars($nm) . '">' . htmlspecialchars($nm) . '</td>
            <td class="col-spec">' . htmlspecialchars($info ? $info['item_desc'] : '') . '</td>
            <td class="col-unit">' . htmlspecialchars($info ? $info['units'] : '') . '</td>
            <td class="col-category">' . htmlspecialchars($info ? $info['item_category1'] : '') . '</td>
            <td class="col-type">' . htmlspecialchars(itemTypeName($type)) . '</td>
            <td class="col-use">' . htmlspecialchars($info ? itemUseName($info['item_use']) : '') . '</td>
            <td class="number col-lossRate">' . htmlspecialchars($ln['sunhao_rate']) . '</td>
            <td class="col-position">' . htmlspecialchars($ln['weizhi']) . '</td>
            <td class="col-process">' . htmlspecialchars($ln['operation_seq_num']) . '</td>
            <td class="col-remark">' . htmlspecialchars($ln['component_remarks']) . '</td>
            <td class="col-effDate">' . htmlspecialchars($ln['effectivity_date'] ? date('Y-m-d', $ln['effectivity_date']) : '') . '</td>
            </tr>';
        if ($hasChild && !in_array($ln['component_item'], $path, true)) {
            // 递归展开用解析后的子件头版本（行锁定版本优先），保证左树/层级/结构图一致
            renderHierarchy($db, $ln['component_item'], $level + 1, $childCode, array_merge($path, array($assembly)), $totalQty, $childHdr['version'], $rowDeep);
        }
    }
}

// 查询某料号作为子件被哪些父 BOM 引用（单层数据），并一次性 JOIN 父件名称避免 N+1。
// $parent 非空时只返回该父 BOM 下的那一行（即“该原材料对上一级BOM父件”的信息），
// 否则返回全部父 BOM（用于无父件上下文时降级展示）。
function renderLeafUsage($db, $item, $parent = '') {
    $where = "l.component_item = '" . esc($db, $item) . "' AND l.disable_date = 0";
    if ($parent !== '') {
        $where .= " AND h.assembly_item_no = '" . esc($db, $parent) . "'";
    }
    $sql = "SELECT l.component_quantity, l.sunhao_rate, l.weizhi, l.operation_seq_num,
                   l.component_remarks, l.effectivity_date,
                   h.assembly_item_no, h.version,
                   p.item_name AS parent_item_name
            FROM bom_lines_all l
            JOIN bom_headers_all h ON h.bom_header_id = l.bom_header_id
            LEFT JOIN sf_item_no p ON p.item_no = h.assembly_item_no
            WHERE $where
            ORDER BY h.assembly_item_no, l.component_sequence_id";
    $r = DB_query($sql, $db);
    $rows = array();
    while ($row = DB_fetch_array($r)) { $rows[] = $row; }
    return $rows;
}

// 性能说明：瓶颈在 bom_lines_all 缺索引（每次 getActiveLines / 顶层 NOT IN 子查询都全表扫描 1339 行）。
// 已通过给 bom_lines_all 加 (bom_header_id, disable_date, item_num) / (component_item, disable_date) /
// (disable_date) 三个索引解决，详见提交说明；不再使用此前无效的 preloadBOMHeaders 全局预加载。

// 渲染右侧面板（物料属性 + BOM 层级 + 图文文档）。供整页渲染与 AJAX 局部刷新复用。
// $version 为空 = 最新版本；否则渲染指定版本（历史版本切换查看）
// $activeTab = 'attr' / 'bom' / 'docs'（默认 'bom'）
function renderRightPanel($db, $view, $filterParent, $version = '', $activeTab = '') {
    if ($view !== '' && $view !== null) {
        $vhdr = headerByVersion($db, $view, $version);
        $vInfo = getItemInfo($db, $view);
        $hasBOM = $vhdr ? true : false;
        // 版本下拉：纯查看切换（?view=&version=），不写库、不影响其他 BOM 的引用版本
        $allVer = allVersions($db, $view);
        $curVer = $vhdr ? $vhdr['version'] : '';
        $verOptions = '<option value="">（最新版本）</option>';
        foreach ($allVer as $v) {
            $isSel = ($curVer !== '' && $v['version'] === $curVer) ? ' selected' : '';
            $verOptions .= '<option value="' . htmlspecialchars($v['version']) . '"' . $isSel . '>v' . htmlspecialchars($v['version']) . ' · ' . htmlspecialchars($v['status'] == '' ? '未审核' : $v['status']) . '</option>';
        }
        $versionText = $vhdr
            ? '版本：<select id="verSelect" style="padding:2px 6px;margin-left:4px">' . $verOptions . '</select>'
              . ' <button type="button" id="btnViewVersion" title="切换为该版本查看（纯 UI，左侧树会跟随此版本实时渲染）" style="margin-left:6px;padding:3px 12px;font-size:12px;background:#fff;border:1px solid #1976D2;color:#1976D2;border-radius:3px;cursor:pointer;font-weight:600">设为当前版本</button>'
            : '无下级 BOM（显示其作为子件被引用的单层数据）';
        // tab 激活状态：默认 bom；URL ?tab=docs/attr 切换
        $attrActive  = ($activeTab === 'attr') ? ' active' : '';
        $bomActive   = ($activeTab === '' || $activeTab === 'bom') ? ' active' : '';
        $docsActive  = ($activeTab === 'docs') ? ' active' : '';
        ?>
        <div class="bom-tabs">
            <div class="bom-tab<?php echo $attrActive; ?>" data-target="panel-attr">物料属性</div>
            <div class="bom-tab<?php echo $bomActive; ?>" data-target="panel-bom">BOM层级</div>
            <div class="bom-tab<?php echo $docsActive; ?>" data-target="panel-docs" title="查看该物料关联的图文档（来自文档工作区）">图文文档</div>
            <div class="bom-tabs-actions">
                <button id="btnMindmap" type="button" title="以结构图方式展示该 BOM 的树状结构（跟随当前查看版本）">结构图</button>
            </div>
        </div>

        <div id="panel-attr" class="bom-tab-panel<?php echo $attrActive; ?>">
            <?php renderItemAttrPanel($db, $view); ?>
        </div>

        <div id="panel-bom" class="bom-tab-panel<?php echo $bomActive; ?>">
            <div class="hier-toolbar">
                <span class="version-tag"><?php echo $versionText; ?></span>
                <button id="btnHierExpand" type="button">全部展开</button>
                <button id="btnHierCollapse" type="button">全部折叠</button>
                <button id="btnExportExcel" type="button" class="export-btn">导出Excel</button>
                <div class="col-settings">
                    <button id="btnColSettings" type="button">列设置</button>
                    <div class="col-settings-panel" id="colSettingsPanel">
                        <div class="col-settings-head">勾选要显示的列</div>
                        <div class="col-settings-body">
                            <label><input type="checkbox" data-col="parentItemNo" checked>父件编码</label>
                            <label><input type="checkbox" data-col="parentItemName" checked>父件名称</label>
                            <label><input type="checkbox" data-col="code" checked>层次码</label>
                            <label><input type="checkbox" data-col="level" checked>层级</label>
                            <label><input type="checkbox" data-col="qty" checked>用量</label>
                            <label><input type="checkbox" data-col="totalQty" checked>总用量</label>
                            <label><input type="checkbox" data-col="itemNo" checked>物料编码</label>
                            <label><input type="checkbox" data-col="childVer" checked>子件版本</label>
                            <label><input type="checkbox" data-col="itemName" checked>物料名称</label>
                            <label><input type="checkbox" data-col="spec" checked>规格型号</label>
                            <label><input type="checkbox" data-col="unit" checked>单位</label>
                            <label><input type="checkbox" data-col="category" checked>分类</label>
                            <label><input type="checkbox" data-col="type" checked>类型</label>
                            <label><input type="checkbox" data-col="use" checked>用途</label>
                            <label><input type="checkbox" data-col="lossRate" checked>自损率</label>
                            <label><input type="checkbox" data-col="position" checked>位置</label>
                            <label><input type="checkbox" data-col="process" checked>制程</label>
                            <label><input type="checkbox" data-col="remark" checked>备注</label>
                            <label><input type="checkbox" data-col="effDate" checked>生效日期</label>
                        </div>
                    </div>
                </div>
                <span class="icon-legend">
                    <span><?php echo itemIcon('F'); ?>成品</span>
                    <span><?php echo itemIcon('B'); ?>半成品</span>
                    <span><?php echo itemIcon('M'); ?>原材料</span>
                </span>
            </div>
            <?php if ($hasBOM): ?>
            <div class="hier-table-wrap">
                <table class="selection" id="hierTable">
                    <thead><tr><th data-col="code">层次码</th><th data-col="level">层级</th><th data-col="qty">用量</th><th data-col="totalQty">总用量</th><th data-col="itemNo">物料编码</th><th data-col="childVer">子件版本</th><th data-col="itemName">物料名称</th><th data-col="spec">规格型号</th><th data-col="unit">单位</th><th data-col="category">分类</th><th data-col="type">类型</th><th data-col="use">用途</th><th data-col="lossRate">自损率</th><th data-col="position">位置</th><th data-col="process">制程</th><th data-col="remark">备注</th><th data-col="effDate">生效日期</th></tr></thead>
                    <tbody>
                        <tr class="bom-root" data-level="0" data-key="0" data-parent="">
                            <td class="hier-level col-code"><span class="tw2" title="展开/折叠">▾</span><?php echo itemIcon($vInfo ? $vInfo['item_type'] : '', 'sm', false, ($vhdr && $vhdr['status'] == '已审核' ? 1 : 0)); ?><span class="hier-code">0</span></td>
                            <td class="hier-lvlnum col-level">0</td>
                            <td class="col-qty">-</td>
                            <td class="col-totalQty">-</td>
                            <td class="col-itemNo"><?php echo htmlspecialchars($view); ?></td>
                            <td class="col-childVer">—</td>
                            <td class="col-itemName" title="<?php echo htmlspecialchars($vInfo ? $vInfo['item_name'] : ''); ?>"><?php echo htmlspecialchars($vInfo ? $vInfo['item_name'] : ''); ?></td>
                            <td class="col-spec"><?php echo htmlspecialchars($vInfo ? $vInfo['item_desc'] : ''); ?></td>
                            <td class="col-unit"><?php echo htmlspecialchars($vInfo ? $vInfo['units'] : ''); ?></td>
                            <td class="col-category"><?php echo htmlspecialchars($vInfo ? $vInfo['item_category1'] : ''); ?></td>
                            <td class="col-type"><?php echo htmlspecialchars(itemTypeName($vInfo ? $vInfo['item_type'] : '')); ?></td>
                            <td class="col-use"><?php echo htmlspecialchars($vInfo ? itemUseName($vInfo['item_use']) : ''); ?></td>
                            <td class="col-lossRate">-</td><td class="col-position">-</td><td class="col-process">-</td><td class="col-remark">-</td><td class="col-effDate">-</td>
                        </tr>
                        <?php renderHierarchy($db, $view, 0, '0', array(), 1, $curVer, ($vhdr && $vhdr['status'] == '已审核' ? 1 : 0)); ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <?php $leafRows = renderLeafUsage($db, $view, $filterParent); ?>
            <div class="hier-table-wrap">
                <table class="selection" id="hierTable">
                    <thead><tr><th data-col="parentItemNo">父件编码</th><th data-col="parentItemName">父件名称</th><th data-col="code">层次码</th><th data-col="level">层级</th><th data-col="qty">用量</th><th data-col="totalQty">总用量</th><th data-col="itemNo">物料编码</th><th data-col="itemName">物料名称</th><th data-col="spec">规格型号</th><th data-col="unit">单位</th><th data-col="category">分类</th><th data-col="type">类型</th><th data-col="use">用途</th><th data-col="lossRate">自损率</th><th data-col="position">位置</th><th data-col="process">制程</th><th data-col="remark">备注</th><th data-col="effDate">生效日期</th></tr></thead>
                    <tbody>
                    <?php if ($leafRows): ?>
                        <?php foreach ($leafRows as $lr): ?>
                        <tr>
                            <td class="col-parentItemNo"><?php echo htmlspecialchars($lr['assembly_item_no']); ?></td>
                            <td class="col-parentItemName" title="<?php echo htmlspecialchars($lr['parent_item_name']); ?>"><?php echo htmlspecialchars($lr['parent_item_name']); ?></td>
                            <td class="hier-level col-code"><span class="tw2 leaf"></span><?php echo itemIcon($vInfo ? $vInfo['item_type'] : ''); ?><span class="hier-code">1</span></td>
                            <td class="hier-lvlnum col-level">1</td>
                            <td class="number col-qty"><?php echo htmlspecialchars($lr['component_quantity']); ?></td>
                            <td class="number col-totalQty"><?php echo htmlspecialchars($lr['component_quantity']); ?></td>
                            <td class="col-itemNo"><?php echo htmlspecialchars($view); ?></td>
                            <td class="col-itemName" title="<?php echo htmlspecialchars($vInfo ? $vInfo['item_name'] : ''); ?>"><?php echo htmlspecialchars($vInfo ? $vInfo['item_name'] : ''); ?></td>
                            <td class="col-spec"><?php echo htmlspecialchars($vInfo ? $vInfo['item_desc'] : ''); ?></td>
                            <td class="col-unit"><?php echo htmlspecialchars($vInfo ? $vInfo['units'] : ''); ?></td>
                            <td class="col-category"><?php echo htmlspecialchars($vInfo ? $vInfo['item_category1'] : ''); ?></td>
                            <td class="col-type"><?php echo htmlspecialchars(itemTypeName($vInfo ? $vInfo['item_type'] : '')); ?></td>
                            <td class="col-use"><?php echo htmlspecialchars($vInfo ? itemUseName($vInfo['item_use']) : ''); ?></td>
                            <td class="number col-lossRate"><?php echo htmlspecialchars($lr['sunhao_rate']); ?></td>
                            <td class="col-position"><?php echo htmlspecialchars($lr['weizhi']); ?></td>
                            <td class="col-process"><?php echo htmlspecialchars($lr['operation_seq_num']); ?></td>
                            <td class="col-remark"><?php echo htmlspecialchars($lr['component_remarks']); ?></td>
                            <td class="col-effDate"><?php echo htmlspecialchars($lr['effectivity_date'] ? date('Y-m-d', $lr['effectivity_date']) : ''); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="18" class="empty-tip">该料号尚未被任何 BOM 引用。</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>

        <div id="panel-docs" class="bom-tab-panel<?php echo $docsActive; ?>">
            <?php renderItemDocsPanel($db, $view); ?>
        </div>
        <?php
    } else {
        ?>
        <div class="bom-tabs">
            <div class="bom-tab" data-target="panel-attr">物料属性</div>
            <div class="bom-tab active" data-target="panel-bom">BOM层级</div>
        </div>
        <div class="empty-tip">请点击左侧 BOM 节点查看其物料属性与 BOM 层级；右键节点可进行 新建 / 引用 / 删除 / 编辑。</div>
        <?php
    }
}

// AJAX 局部刷新：只返回右侧面板片段，跳过 header.inc 与左侧树渲染，显著提升响应速度
if ($isAjax) {
    header('Content-Type: text/html; charset=utf-8');
    while (ob_get_level()) { ob_end_clean(); }
    $ajaxView = isset($_GET['view']) ? $_GET['view'] : '';
    $ajaxParent = isset($_GET['parent']) ? $_GET['parent'] : '';
    renderRightPanel($db, $ajaxView, $ajaxParent, isset($_GET['version']) ? $_GET['version'] : '', isset($_GET['tab']) ? $_GET['tab'] : '');
    exit;
}

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>BOM建立</title>
<meta http-equiv="Content-Type" content="application/html; charset=utf-8"/>
<script src="<?php echo $RootPath; ?>/JXC/javascript/lhgdialog.min.js?self=true&skin=chrome"></script>
<style type="text/css">
.bom-layout{display:flex;width:100%;height:calc(100vh - 130px);min-height:420px;gap:8px;margin-top:6px}
.bom-left{width:360px;border:1px solid #c5c5c5;background:#f7f9fc;border-radius:4px;padding:12px;height:100%;display:flex;flex-direction:column}
.bom-left-body{flex:1;overflow:auto;padding-bottom:8px}
.bom-left-head{display:flex;justify-content:space-between;align-items:center;padding-bottom:10px;margin-bottom:10px;border-bottom:1px solid #d9d9d9;font-size:14px;font-weight:bold;color:#333}
.left-actions{display:flex;gap:6px}
.left-actions button{background:#fff;color:#555;border:1px solid #c5c5c5;padding:4px 10px;border-radius:3px;cursor:pointer;font-size:12px;font-weight:normal}
.left-actions button:hover{background:#f0f0f0}
.left-actions button:last-child{background:#4a90e2;color:#fff;border-color:#4a90e2}
.left-actions button:last-child:hover{background:#357abd;border-color:#357abd}
.bom-tree,.bom-tree ul{list-style:none;margin:0;padding:0}
.bom-tree{font-size:12px;line-height:22px;color:#333}
.bom-tree ul.bom-sub{margin:0;padding:0}
.bom-node{display:flex;flex-direction:column;min-width:0}
.bom-row{display:flex;align-items:center;height:22px;white-space:nowrap;cursor:pointer;min-width:0;padding-right:4px}
.bom-row:hover{background:#f0f4f8}
.bom-node.active>.bom-row{background:#cfe3ff}
.bom-node.active>.bom-row .lbl{font-weight:bold}

/* 树线列：经典 Windows 资源管理器风格，折叠符与虚线在同一列 */
.bom-glyphs{display:inline-flex;align-items:center;height:22px;flex-shrink:0}
.tree-cell{width:20px;height:22px;position:relative;flex-shrink:0;box-sizing:border-box}

/* 祖先列：垂直虚线；无后续兄弟时只画到本行中部（拐角） */
.indent-cell .tree-vbar{position:absolute;left:50%;top:0;bottom:0;width:0;border-left:1px dotted #b0b8c0;transform:translateX(-50%)}
.indent-cell.no-sibling .tree-vbar{bottom:11px}

/* 当前节点列：水平虚线从父列连到折叠框；顶层节点不画 */
.node-cell{position:relative}
.node-cell .tree-hbar{position:absolute;left:0;right:13px;top:11px;border-top:1px dotted #999}

/* 折叠框：方框 +/-，靠右贴紧后续 label */
.tw{position:absolute;left:auto;right:2px;top:50%;transform:translateY(-50%);width:11px;height:11px;line-height:9px;border:1px solid #808890;background:#fff;font-size:9px;text-align:center;cursor:pointer;color:#333;z-index:2;font-family:"Courier New",monospace}
.tw:hover{border-color:#2196F3;color:#2196F3}
.leaf-node .node-cell .tw{display:none}

/* 图标与料号名称：block-level flex + gap:0 彻底消除任何水平空白 */
/* 使用 !important 防止外部 CSS 或缓存覆盖 */
.icon-label{display:flex !important;align-items:center !important;flex:1 !important;min-width:0;gap:0 !important}
/* SVG 图标容器：block 渲染，无任何 inline baseline 间隙 */
.bom-row .item-icon{margin:0 !important;padding:0 !important;display:block !important;flex:0 0 auto !important;line-height:0 !important;font-size:0 !important;width:16px !important;height:16px !important;min-width:16px !important;max-width:16px !important;position:relative}
.bom-row .item-icon.icon-lg{width:16px !important;height:16px !important}
.bom-row .item-icon .bom-svg{display:block !important;margin:0 !important;padding:0 !important;width:16px !important;height:16px !important;min-width:16px !important;max-width:16px !important}
/* .lbl 加 padding-left:2px 让文字与 SVG 边框之间有 2px 缓冲，避免文字被 SVG outline 触碰 */
.bom-row .lbl{flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;min-width:0;margin:0 !important;padding:0 0 0 2px !important;line-height:22px;text-align:left !important;text-indent:0 !important}
.bom-node.leaf-node>.bom-row>.lbl{color:#555}
/* 顶层节点星标已直接绘制在 SVG 内，无需 ::after 伪元素 */
.bom-right{flex:1;border:1px solid #c5c5c5;border-radius:4px;padding:12px;background:#fff;min-width:0;height:100%;display:flex;flex-direction:column}

/* Tab 页签 */
.bom-tabs{display:flex;border-bottom:2px solid #2196F3;margin-bottom:12px;align-items:center}
.bom-tab{padding:8px 22px;cursor:pointer;font-size:14px;color:#555;background:#f5f5f5;border:1px solid #d9d9d9;border-bottom:none;border-radius:4px 4px 0 0;margin-right:4px;position:relative;top:2px}
.bom-tab:hover{background:#e3f2fd;color:#1976D2}
.bom-tab.active{background:#2196F3;color:#fff;border-color:#2196F3;font-weight:bold}
.bom-tab-panel{display:none;flex-direction:column}
.bom-tab-panel.active{display:flex;flex:1;overflow:hidden}
.bom-tabs-actions{margin-left:auto;padding-right:6px}
.bom-tabs-actions button{padding:4px 14px;font-size:13px;border:1px solid #5e35b1;background:#ede7f6;color:#5e35b1;border-radius:3px;cursor:pointer}
.bom-tabs-actions button:hover{background:#5e35b1;color:#fff}

/* 工具栏 */
.hier-toolbar{display:flex;align-items:center;gap:10px;margin-bottom:10px;padding:8px;background:#f5f5f5;border:1px solid #e0e0e0;border-radius:3px;flex-wrap:wrap}
.hier-toolbar button{background:#fff;color:#555;border:1px solid #c5c5c5;padding:5px 12px;border-radius:3px;cursor:pointer;font-size:12px}
.hier-toolbar button:hover{background:#e3f2fd;border-color:#2196F3;color:#1976D2}
.hier-toolbar button.export-btn{background:#4caf50;color:#fff;border-color:#4caf50}
.hier-toolbar button.export-btn:hover{background:#43a047;border-color:#43a047}
.version-tag{display:inline-block;background:#4caf50;color:#fff;padding:4px 10px;border-radius:3px;font-size:12px;font-weight:bold;margin-right:6px}
.icon-legend{font-size:12px;color:#666;display:flex;gap:12px;align-items:center}
.icon-legend span{display:inline-flex;align-items:center;gap:3px}

#ctxMenu{position:absolute;display:none;background:#fff;border:1px solid #999;box-shadow:2px 2px 6px rgba(0,0,0,.2);z-index:9999;font-size:13px;border-radius:3px;overflow:hidden}
#ctxMenu div{padding:6px 18px;cursor:pointer}
#ctxMenu div:hover{background:#e8f0fe}
#ctxMenu div.disabled{color:#bbb;cursor:not-allowed}
#ctxMenu div.disabled:hover{background:#fff}
#ctxMenu .ctx-head{padding:6px 18px;font-weight:bold;border-bottom:1px solid #eee;background:#f5f9ff;cursor:default;white-space:nowrap;color:#333}
#ctxMenu .ctx-head:hover{background:#f5f9ff}

/* 列设置 */
.col-settings{position:relative;display:inline-block}
.col-settings-panel{display:none;position:absolute;left:0;top:36px;z-index:100;background:#fff;border:1px solid #c5c5c5;border-radius:4px;box-shadow:0 4px 12px rgba(0,0,0,.15);padding:10px 14px;width:280px}
.col-settings-panel.open{display:block}
.col-settings-head{font-weight:bold;font-size:13px;border-bottom:1px solid #e8e8e8;padding-bottom:6px;margin-bottom:8px;color:#333}
.col-settings-body{display:grid;grid-template-columns:repeat(2, 1fr);gap:6px 10px}
.col-settings-body label{display:flex;align-items:center;gap:5px;font-size:12px;color:#444;cursor:pointer;white-space:nowrap}
.col-settings-body input[type="checkbox"]{margin:0}

/* 右侧层级树形表 */
.hier-table-wrap{flex:1;overflow:auto;border:1px solid #e0e0e0;border-radius:3px}
#hierTable{width:auto;min-width:100%;border-collapse:separate;border-spacing:0;table-layout:auto}
#hierTable th{position:sticky;top:0;z-index:2;background:#2196F3;color:#fff;font-weight:bold;padding:8px 10px;text-align:center;white-space:nowrap;border-bottom:2px solid #1e88e5;border-right:1px solid #1e88e5}
#hierTable td{padding:6px 10px;text-align:center;font-size:13px;white-space:nowrap;vertical-align:middle;border-right:1px solid #e0e0e0;border-bottom:1px solid #e0e0e0}
#hierTable tr:hover{background:#f1f8ff}
#hierTable tr.bom-parent{background:#f5f9ff}
#hierTable tr.bom-root{background:#fff8e1;font-weight:bold}
/* 层级色带：按 data-level 给行左侧加彩色竖条，层次码单元格轻微背景 */
#hierTable tr[data-level="0"] td:first-child{border-left:4px solid #FBC02D;background:#fff8e1}
#hierTable tr[data-level="1"] td:first-child{border-left:4px solid #2196F3;background:#f1f8ff}
#hierTable tr[data-level="2"] td:first-child{border-left:4px solid #4CAF50;background:#f1f9f1}
#hierTable tr[data-level="3"] td:first-child{border-left:4px solid #9C27B0;background:#f7f3f9}
#hierTable tr[data-level="4"] td:first-child{border-left:4px solid #FF5722;background:#fff3ef}
#hierTable tr[data-level="5"] td:first-child{border-left:4px solid #00BCD4;background:#eafeff}
.tw2{display:inline-block;width:20px;height:22px;line-height:20px;color:#555;cursor:pointer;font-size:16px;margin-right:2px;text-align:center;vertical-align:middle;transition:color .12s}
.tw2:hover{color:#2196F3}
.tw2.leaf{visibility:hidden;pointer-events:none;cursor:default}
.hier-level{white-space:nowrap;text-align:left!important;padding-left:8px!important;vertical-align:middle}
.hier-code{font-weight:bold;color:#0d47a1;display:inline-block;min-width:24px;text-align:left;font-size:14px;letter-spacing:.3px}
.hier-indent{display:inline-block;width:16px;vertical-align:middle}
.hier-lvlnum{text-align:center}
.hier-level .item-icon{font-size:16px;margin-right:5px;vertical-align:middle;display:inline-flex;width:20px;height:20px;align-items:center;justify-content:center}
#hierTable td.col-itemName{text-align:left;max-width:160px;overflow:hidden;text-overflow:ellipsis}
.icon-raw{color:#4caf50}
.icon-half{color:#ff9800}
.icon-finish{color:#2196F3}
.icon-pack{color:#e91e63}
.icon-other{color:#9e9e9e}

/* 物料属性面板 */
.attr-panel{flex:1;overflow:auto;background:#f9fbfd;border:1px solid #d6e4f0;border-radius:4px;padding:12px 14px}
.bom-tab-panel .empty-tip,.bom-right>.empty-tip{flex:1;overflow:auto}
.attr-grid{display:grid;grid-template-columns:repeat(4, 1fr);gap:10px 16px}
.attr-item{display:flex;align-items:baseline;font-size:12px;line-height:1.6;background:#fff;border:1px solid #e8eef5;border-radius:3px;padding:5px 8px}
.attr-label{color:#666;min-width:5.5em;flex-shrink:0;margin-right:6px}
.attr-value{color:#333;font-weight:500;word-break:break-all}

.empty-tip{padding:60px 30px;text-align:center;color:#999;font-size:13px;line-height:1.8;cursor:default;background:transparent;border:0;box-shadow:none;font-weight:normal;user-select:none}
.leaf-note{padding:8px 10px;margin-bottom:8px;background:#eef4fb;border:1px solid #cfe0f3;border-radius:3px;font-size:13px;color:#444}
.bom-right.loading{opacity:.45;pointer-events:none;transition:opacity .12s}
.bom-right{position:relative}
</style>
</head>
<body>
<div id="CanvasDiv">
    <div id="BodyDiv">
        <div id="BodyWrapDiv">
            <div class="bom-layout">
                <div class="bom-left">
                    <div class="bom-left-head"><span>BOM结构树</span><div class="left-actions">
                        <button id="btnCreateTop" type="button">+ 新建BOM</button>
                        <button id="btnCopyBom" type="button" title="复制 BOM 结构到目标料号">复制BOM</button>
                        <button id="btnUploadBom" type="button" title="Excel 整批上传 BOM">上传BOM</button>
                        <button id="btnExpandAll" type="button">全部展开</button>
                        <button id="btnCollapseAll" type="button">全部折叠</button>
                    </div></div>
                    <div class="bom-left-body" id="bomLeftBody">
                        <?php if (count($topAsms) == 0): ?>
                            <div style="padding:10px;color:#888">暂无 BOM，请点击右上角“新建BOM”创建。</div>
                        <?php else: ?>
                            <ul id="bomTree" class="bom-tree">
                                <?php
                                // 左侧树跟随右侧"当前查看版本"：递归贯穿到任意深度的节点（包括子 BOM）
                                $viewAsmTree = isset($_GET['view']) ? $_GET['view'] : '';
                                $viewVerTree = isset($_GET['version']) ? $_GET['version'] : '';
                                $viewHdrIdTree = 0;
                                if ($viewAsmTree !== '' && $viewVerTree !== '') {
                                    $vhCur = headerByVersion($db, $viewAsmTree, $viewVerTree);
                                    if ($vhCur) $viewHdrIdTree = $vhCur['bom_header_id'];
                                }
                                $topItems = array();
                                foreach ($topAsms as $asm) { $topItems[] = array('item' => $asm, 'line_id' => ''); }
                                renderForest($db, '', $topItems, array(), array(), 0, '', $viewAsmTree, $viewHdrIdTree);
                                ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="bom-right" id="bomRight">
                    <?php renderRightPanel($db, isset($_GET['view']) ? $_GET['view'] : '', isset($_GET['parent']) ? $_GET['parent'] : '', isset($_GET['version']) ? $_GET['version'] : '', isset($_GET['tab']) ? $_GET['tab'] : ''); ?>
                </div>
            </div>

            <div id="ctxMenu">
                <div class="ctx-head" id="ctxHead"></div>
                <div data-act="new">新建BOM</div>
                <div data-act="ref">引用物料或BOM</div>
                <div data-act="edit">编辑BOM</div>
                <div data-act="upgrade">升版BOM</div>
                <div data-act="del">删除BOM</div>
            </div>
            <div id="dlgBox" style="display:none"></div>
        </div>
    </div>

    <div id="FooterDiv"><div id="FooterWrapDiv"></div></div>
</div>

<script type="text/javascript">
$(function(){
    var cur = {assembly:'', parentAssembly:'', version:'', raw:0, leaf:0, lineId:''};
    var hierTr = {};                 // 右侧层级表展开/折叠状态（AJAX 后重建）
    var activeTabTarget = 'panel-bom'; // 当前激活的右侧 Tab，AJAX 后恢复

    // ===== 右侧面板事件全部用 document 委托，AJAX 替换 .bom-right 后仍有效 =====

    // Tab 切换（委托到 document，因 .bom-right 会被 AJAX 替换）
    $(document).on('click', '.bom-tab', function(){
        var target = $(this).data('target');
        activeTabTarget = target;
        $('.bom-right .bom-tab').removeClass('active');
        $(this).addClass('active');
        $('.bom-right .bom-tab-panel').removeClass('active');
        $('#' + target).addClass('active');
    });

    function activateTab(target){
        $('.bom-right .bom-tab').removeClass('active');
        $('.bom-right .bom-tab[data-target="' + target + '"]').addClass('active');
        $('.bom-right .bom-tab-panel').removeClass('active');
        $('#' + target).addClass('active');
    }

    // 右侧层级树形表：重建展开/折叠映射（AJAX 后调用）
    function rebuildHierTr(){
        hierTr = {};
        $('#hierTable tr[data-key]').each(function(){
            hierTr[$(this).attr('data-key')] = { parent: $(this).attr('data-parent'), expanded: true };
        });
        hierRefresh();
    }
    function hierRefresh(){
        $('#hierTable tr[data-key]').each(function(){
            var pk = $(this).attr('data-parent'), vis = true;
            while (pk) {
                var p = hierTr[pk];
                if (!p) break;
                if (p.expanded === false) { vis = false; break; }
                pk = p.parent;
            }
            $(this).toggle(vis);
        });
    }
    $(document).on('click', '#hierTable .tw2:not(.leaf)', function(e){
        e.stopPropagation();
        var k = $(this).closest('tr').attr('data-key');
        hierTr[k].expanded = !hierTr[k].expanded;
        $(this).text(hierTr[k].expanded ? '▾' : '▸');
        hierRefresh();
    });

    // 列显示/隐藏控制
    function applyColSettings(){
        var key = 'bom_col_settings';
        var saved = localStorage.getItem(key);
        var defaults = {};
        $('#colSettingsPanel input[data-col]').each(function(){ defaults[$(this).data('col')] = $(this).is(':checked'); });
        var cfg = saved ? $.extend(defaults, JSON.parse(saved)) : defaults;
        $('#colSettingsPanel input[data-col]').each(function(){
            var col = $(this).data('col'), show = cfg[col] !== false;
            $(this).prop('checked', show);
            $('#hierTable .col-' + col).toggle(show);
            $('#hierTable th[data-col="' + col + '"]').toggle(show);
        });
    }
    $(document).on('click', '#btnColSettings', function(e){
        e.stopPropagation();
        $('#colSettingsPanel').toggleClass('open');
    });
    $(document).on('click', '#colSettingsPanel', function(e){ e.stopPropagation(); });
    $(document).on('click', function(){ $('#colSettingsPanel').removeClass('open'); });
    $(document).on('change', '#colSettingsPanel input[data-col]', function(){
        var col = $(this).data('col'), show = $(this).is(':checked');
        $('#hierTable .col-' + col).toggle(show);
        $('#hierTable th[data-col="' + col + '"]').toggle(show);
        var cfg = {};
        $('#colSettingsPanel input[data-col]').each(function(){ cfg[$(this).data('col')] = $(this).is(':checked'); });
        localStorage.setItem('bom_col_settings', JSON.stringify(cfg));
    });

    // 右侧层级表全部展开/折叠
    $(document).on('click', '#btnHierExpand', function(){
        $('#hierTable tr[data-key]').each(function(){ if (hierTr[$(this).attr('data-key')]) hierTr[$(this).attr('data-key')].expanded = true; });
        $('#hierTable .tw2:not(.leaf)').text('▾');
        hierRefresh();
    });

    // 版本下拉切换：仅刷新右侧面板（AJAX），不重建左侧树；点击"设为当前版本"后才整页跳转并让左侧树跟随
    $(document).on('change', '#verSelect', function(){
        var v = this.value;
        var view = new URLSearchParams(location.search).get('view') || '';
        if (!view) return;
        var url = 'BOMSetup.php?view=' + encodeURIComponent(view);
        if (v) url += '&version=' + encodeURIComponent(v);
        loadRightPanel(url + '&ajax=1', url);
    });
    $(document).on('click', '#btnViewVersion', function(){
        var v = $('#verSelect').val();
        var view = new URLSearchParams(location.search).get('view') || '';
        if (!view) { alert('请先选择物料'); return; }
        var url = 'BOMSetup.php?view=' + encodeURIComponent(view);
        if (v) url += '&version=' + encodeURIComponent(v);
        // 先写库：提升为当前版本，级联更新所有父 BOM 引用该子件的版本绑定
        $.post('BOMSetup.php', { op:'set_current_version', assembly: view, version: v }, function(){
            location.href = url;
        }).fail(function(){ alert('设为当前版本失败，请重试'); });
    });
    // 子件版本切换：点击层级表"子件版本" → 弹窗选择该子件的 BOM 版本（仅影响本行引用）
    $(document).on('click', '#hierTable .ver-bind', function(e){
        e.stopPropagation();
        var lineId = $(this).data('line-id');
        var asm = $(this).data('asm');
        $.dialog({title:'切换子件版本 - ' + asm, width:420, height:380,
            content:'url:BOMSetup.php?op=line_version&line_id=' + lineId + '&asm=' + encodeURIComponent(asm) + '&_r=' + Date.now(), lock:true});
    });
    // 用量修改：点击层级表"用量"数字 → 弹窗修改当前展示版本 BOM 的子件用量
    $(document).on('click', '#hierTable .qty-edit', function(e){
        e.stopPropagation();
        var tr = $(this).closest('tr');
        var lineId = tr.attr('data-line-id');
        var asm = tr.attr('data-asm');
        var asmName = tr.attr('data-asm-name') || '';
        var view = new URLSearchParams(location.search).get('view') || '';
        var ver = new URLSearchParams(location.search).get('version') || '';
        if (!lineId) { alert('该行缺少定位信息（component_sequence_id），无法修改！'); return; }
        $.dialog({title:'修改用量 - ' + asm + ' ' + asmName, width:520, height:340,
            content:'url:BOMSetup.php?op=edit_qty&line_id=' + encodeURIComponent(lineId) + '&view=' + encodeURIComponent(view) + '&version=' + encodeURIComponent(ver) + '&_r=' + Date.now(), lock:true});
    });
    $(document).on('click', '#btnHierCollapse', function(){
        $('#hierTable tr[data-key]').each(function(){ if (hierTr[$(this).attr('data-key')]) hierTr[$(this).attr('data-key')].expanded = false; });
        $('#hierTable .tw2:not(.leaf)').text('▸');
        hierRefresh();
    });

    // 导出当前层级表为 Excel
    $(document).on('click', '#btnExportExcel', function(){
        var rows = [];
        var headers = [];
        $('#hierTable thead th').each(function(){ headers.push($(this).text().replace(/\s+/g,'')); });
        rows.push(headers.join('\t'));
        $('#hierTable tbody tr').each(function(){
            var cells = [];
            $(this).find('td').each(function(){
                cells.push($(this).text().replace(/^\s+|\s+$/g,'').replace(/\t/g,' '));
            });
            if (cells.length) rows.push(cells.join('\t'));
        });
        var tsv = rows.join('\r\n');
        var blob = new Blob([tsv], {type: 'application/vnd.ms-excel;charset=utf-8'});
        var a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = 'BOM层级_' + ($('.attr-item:first .attr-value').text() || 'export') + '.xls';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    });

    // ===== 左侧树：折叠状态/选中状态跨整页跳转保留（修复“折叠后点击又全展开”）=====
    function saveCollapsed(){
        var set = [];
        $('#bomTree .bom-node').each(function(){
            var ul = $(this).children('ul.bom-sub');
            if (ul.length && ul.css('display')=='none') set.push($(this).data('assembly'));
        });
        try { sessionStorage.setItem('bom_collapsed', JSON.stringify(set)); } catch(e){}
    }
    function restoreCollapsed(){
        var raw; try { raw = sessionStorage.getItem('bom_collapsed'); } catch(e){ return; }
        if (!raw) return;
        var set = JSON.parse(raw) || [];
        $.each(set, function(i, asm){
            var node = $('#bomTree .bom-node[data-assembly="'+asm+'"]');
            var ul = node.children('ul.bom-sub');
            if (ul.length){ ul.hide(); node.find('> .bom-glyphs .tw').text('+'); }
        });
    }
    function restoreActive(){
        var asm; try { asm = sessionStorage.getItem('bom_active'); } catch(e){ return; }
        if (asm) $('#bomTree .bom-node[data-assembly="'+asm+'"]').addClass('active');
    }

    // 展开/折叠（叶子无子树，不响应）
    $('#bomTree').on('click', '.tw', function(e){
        e.stopPropagation();
        var ul = $(this).closest('.bom-node').children('ul.bom-sub');
        if (ul.length){
            if (ul.css('display')=='none'){ ul.show(); $(this).text('-'); }
            else { ul.hide(); $(this).text('+'); }
            saveCollapsed();
        }
    });

    // 左侧全部展开/折叠
    $('#btnExpandAll').on('click', function(){
        $('#bomTree ul.bom-sub').show();
        $('#bomTree .bom-node:not(.leaf-node) > .bom-glyphs .tw').text('-');
        saveCollapsed();
    });
    $('#btnCollapseAll').on('click', function(){
        $('#bomTree ul.bom-sub').hide();
        $('#bomTree .bom-node:not(.leaf-node) > .bom-glyphs .tw').text('+');
        saveCollapsed();
    });

    // AJAX 局部刷新右侧面板（不重建左侧树，显著提升响应速度）
    function loadRightPanel(ajaxUrl, viewUrl){
        $('#bomRight').addClass('loading');
        $.get(ajaxUrl, function(html){
            $('#bomRight').html(html);
            rebuildHierTr();
            applyColSettings();
            if (activeTabTarget) activateTab(activeTabTarget);
            $('#bomRight').removeClass('loading');
            if (history.replaceState) {
                try { history.replaceState(null, '', viewUrl); } catch(e){}
            }
        }).fail(function(){
            $('#bomRight').removeClass('loading');
        });
    }

    // 点击节点内容行 -> AJAX 刷新右侧 + 左侧高亮（状态跨跳转保留）
    $('#bomTree').on('click', '.bom-row', function(e){
        if ($(e.target).closest('.tw').length) return; // 折叠箭头不触发选中
        var node = $(this).closest('.bom-node');
        $('#bomTree .bom-node').removeClass('active');
        node.addClass('active');
        var a = node.data('assembly');
        var parent = node.data('parent-assembly') || '';
        try {
            sessionStorage.setItem('bom_active', a);
            sessionStorage.setItem('bom_scrollTop', $('#bomLeftBody').scrollTop());
        } catch(e){}
        var viewUrl = 'BOMSetup.php?view=' + encodeURIComponent(a);
        var urlParams = new URLSearchParams(location.search);
        var curView = urlParams.get('view') || '';
        var curVer  = urlParams.get('version') || '';
        var nodeVer = node.data('version') || '';
        if (a === curView && curVer) {
            // 点击的是当前已选中节点，保留 URL 中正在预览的版本（避免下拉框预览后被点回同一节点时回退）
            viewUrl += '&version=' + encodeURIComponent(curVer);
            loadRightPanel(viewUrl + '&ajax=1', viewUrl);
        } else {
            // 切换到其他节点：整页跳转，让左侧树重新渲染，避免旧视图的 view_hdr 覆盖残留在树上
            if (nodeVer) {
                viewUrl += '&version=' + encodeURIComponent(nodeVer);
            }
            // 叶子（原材料/包装物）点击时带上其上一级父 BOM，右侧只显示该父件下的单层数据
            if (node.hasClass('leaf-node') && parent) {
                viewUrl += '&parent=' + encodeURIComponent(parent);
            }
            location.href = viewUrl;
        }
    });

    // 页面加载后恢复折叠/选中/滚动位置
    restoreCollapsed();
    restoreActive();
    try {
        var st = sessionStorage.getItem('bom_scrollTop');
        if (st !== null) { $('#bomLeftBody').scrollTop(parseInt(st, 10)); }
    } catch(e){}
    // 初始右侧面板：重建层级表状态 + 应用列设置
    rebuildHierTr();
    applyColSettings();

    // 右键菜单
    $('#bomTree').on('contextmenu', '.bom-node', function(e){
        e.preventDefault();
        var node = $(this);
        cur.assembly = node.data('assembly');
        cur.parentAssembly = node.data('parent-assembly');
        cur.version  = node.data('version');
        cur.status   = node.data('status') || '';
        cur.parentStatus = node.data('parent-status') || '';
        cur.parentHdrId = node.data('parent-hdr-id') || '';
        cur.raw      = node.data('raw');
        cur.leaf     = node.data('leaf');
        cur.lineId   = node.data('line-id');
        cur.parentRefCount = parseInt(node.data('parent-ref-count')) || 0;
        cur.refCount = parseInt(node.data('ref-count')) || 0;
        var m = $('#ctxMenu');
        m.find('div').removeClass('disabled');
        $('#ctxHead').text('对【' + cur.assembly + (cur.version ? ' (v' + cur.version + ')' : '') + '】操作');
        if (cur.raw == 1){
            // 只有绑定物料是原材料（M）时才置灰新建/引用（成品、半成品、叶子物料都不置灰）
            m.find('div[data-act="new"]').addClass('disabled');
            m.find('div[data-act="ref"]').addClass('disabled');
        }
        if (!cur.version){
            // 自身没有 BOM 头：升版/编辑置灰（无版本可升、无结构可编辑）
            m.find('div[data-act="edit"]').addClass('disabled');
            m.find('div[data-act="upgrade"]').addClass('disabled');
            if (!cur.lineId) {
                // 既非 BOM 头、也不是任何父 BOM 的子件（无 lineId）→ 没有可删除对象才灰删除
                m.find('div[data-act="del"]').addClass('disabled');
            }
            // 若是父 BOM 的子件（有 lineId）→ 不灰删除，由下方 lineId 分支处理为"删除子件引用"
        }
        // 已审核 BOM 冻结结构变更（升版除外，升版会新建未审核版本）
        if (cur.status === '已审核') {
            m.find('div[data-act="new"]').addClass('disabled');
            m.find('div[data-act="ref"]').addClass('disabled');
            m.find('div[data-act="edit"]').addClass('disabled');
        }
        // 删除：BOM 头节点看自己审核状态；子件行看父 BOM 审核状态
        if (cur.lineId) {
            if (cur.parentStatus === '已审核') {
                m.find('div[data-act="del"]').addClass('disabled');
            }
            m.find('div[data-act="del"]').text('删除子件引用');
        } else {
            if (cur.status === '已审核') {
                m.find('div[data-act="del"]').addClass('disabled');
            }
            // BOM 头节点：菜单文案保持 "删除BOM"
            m.find('div[data-act="del"]').text('删除BOM');
        }
        m.css({left:e.pageX, top:e.pageY}).show();
        return false;
    });
    $(document).on('click', function(){ $('#ctxMenu').hide(); });

    $('#ctxMenu div[data-act]').on('click', function(){
        if ($(this).hasClass('disabled')){ return; }
        var act = $(this).data('act'), url, title, h, w;
        if (act == 'new') {
            // 新建BOM = 在选中节点下新建"子BOM"：新建物料（自动建 BOM 头）并作为该节点的子件
            url = 'BOMSetup.php?op=new&assembly=' + encodeURIComponent(cur.assembly)
                + '&version=' + encodeURIComponent(cur.version || '1') + '&_r=' + Date.now();
            title = '新建BOM'; h = 560; w = 620;
        } else if (act == 'del' && cur.lineId) {
            // 有 line_id = 子件行。若父 BOM 只被 1 个或 0 个 BOM 引用（不影响其他 BOM）→ 直接删除，不弹确认；
            // 否则（父被多 BOM 引用，删除会影响其他 BOM）→ 弹确认框
            if (cur.parentRefCount <= 1) {
                $.post('BOMSetup.php', {
                    op: 'del_line', line_id: cur.lineId, parent: cur.parentAssembly,
                    FormID: '<?php echo $_SESSION['FormID']; ?>'
                }).done(function(){ location.reload(); }).fail(function(){ alert('删除失败，请重试'); });
                return;
            }
            url = 'BOMSetup.php?op=del_line_confirm&line_id=' + encodeURIComponent(cur.lineId)
                + '&parent=' + encodeURIComponent(cur.parentAssembly) + '&_r=' + Date.now();
            title = '删除'; h = 200; w = 360;
        } else {
            // BOM 头右键操作：op=del 对应 del_confirm（白名单只有 del_confirm），其余 op=act
            var realOp = (act == 'del') ? 'del_confirm' : act;
            url = 'BOMSetup.php?op=' + realOp + '&assembly=' + encodeURIComponent(cur.assembly)
                + '&version=' + encodeURIComponent(cur.version)
                + '&parent=' + encodeURIComponent(cur.parentAssembly || '')
                + '&parent_hdr=' + encodeURIComponent(cur.parentHdrId || '')
                + '&_r=' + Date.now();
            if (act == 'del') {
                // 一律走 del_confirm 弹窗（让用户确认版本选择 + 已审核保护）
                title = '删除 BOM'; h = 520; w = 680;
            }
            else if (act == 'edit') { title = '编辑BOM'; h = 520; w = 640; }
            else if (act == 'upgrade') { title = 'BOM 升版'; h = 420; w = 600; }
            else if (act == 'ref') { title = '引用物料或BOM'; h = 860; w = 1280; }
        }
        $.dialog({title:title, width:w, height:h, content:'url:' + url, lock:true});
    });

    $('#btnCreateTop').on('click', function(){
        $.dialog({title:'新建顶层BOM', width:900, height:500,
            content:'url:BOMSetup.php?op=create_top&_r=' + Date.now(), lock:true});
    });

    // 复制BOM：弹窗（树状选择源 BOM，参照引用BOM弹窗）
    $('#btnCopyBom').on('click', function(){
        $.dialog({title:'复制BOM', width:1280, height:860,
            content:'url:BOMSetup.php?op=copy_bom&_r=' + Date.now(), lock:true});
    });

    // 上传BOM：iframe 弹窗加载 BOMUpload.php（整批 Excel 上传）
    $('#btnUploadBom').on('click', function(){
        $.dialog({title:'BOM整批上传', width:1000, height:600,
            content:'url:BOMUpload.php?_r=' + Date.now(), lock:true});
    });

    // 结构图：以当前选中 BOM 为根，弹窗 iframe 加载独立渲染页面（可折叠/展开），自动跟随当前查看版本
    $(document).on('click', '#btnMindmap', function(){
        var view = new URLSearchParams(location.search).get('view') || '';
        if (!view) { alert('请先在左侧选择一个 BOM 节点'); return; }
        var ver = new URLSearchParams(location.search).get('version') || '';
        var url = 'BOMSetup.php?op=mindmap_view&assembly=' + encodeURIComponent(view);
        if (ver) url += '&version=' + encodeURIComponent(ver);
        url += '&_r=' + Date.now();
        var mmTitle = '结构图：' + view + (ver ? ' (v' + ver + ')' : '');
        $.dialog({title:mmTitle, width:1180, height:780,
            content:'url:' + url, lock:true});
    });
});
</script>
<?php
include('includes/footer.inc');
?>
