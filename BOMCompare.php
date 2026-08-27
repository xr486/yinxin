<?php
ob_start();
include('includes/session.inc');

// ============================================================
// AJAX：取某料号的全部 BOM 版本（GET，无需 FormID）
// ============================================================
if (isset($_GET['act']) && $_GET['act'] == 'get_versions') {
    header('Content-Type: application/json; charset=utf-8');
    $assembly = isset($_GET['assembly']) ? trim($_GET['assembly']) : '';
    $out = array();
    if ($assembly != '') {
        $r = DB_query("SELECT bom_header_id, version, status, is_current
            FROM bom_headers_all
            WHERE assembly_item_no='" . DB_escape_string($assembly) . "'
            ORDER BY is_current DESC, bom_header_id DESC", $db);
        while ($row = DB_fetch_array($r)) {
            $out[] = array(
                'bom_header_id' => $row['bom_header_id'],
                'version'       => $row['version'],
                'status'        => $row['status'],
                'is_current'    => $row['is_current']
            );
        }
    }
    echo json_encode($out);
    exit;
}

// ============================================================
// 辅助函数
// ============================================================

function bomcmp_esc($db, $v) {
    if (is_object($db) && method_exists($db, 'real_escape_string')) {
        return mysqli_real_escape_string($db, $v);
    }
    if (function_exists('mysql_real_escape_string')) {
        return mysql_real_escape_string($v, $db);
    }
    return addslashes($v);
}

// 取某料号的全部 BOM 版本（供初始渲染与 AJAX 复用）
function bomcmp_get_versions($db, $assembly) {
    $arr = array();
    if ($assembly == '') {
        return $arr;
    }
    $r = DB_query("SELECT bom_header_id, version, status, is_current
        FROM bom_headers_all
        WHERE assembly_item_no='" . DB_escape_string($assembly) . "'
        ORDER BY is_current DESC, bom_header_id DESC", $db);
    while ($row = DB_fetch_array($r)) {
        $arr[] = $row;
    }
    return $arr;
}

function bomcmp_version_options($versions, $selected) {
    $html = '<option value="">-- 选择版本 --</option>';
    foreach ($versions as $v) {
        $sel = ($selected != '' && $selected == $v['bom_header_id']) ? ' selected' : '';
        $cur = ($v['is_current'] == 1) ? ' ★' : '';
        $html .= '<option value="' . $v['bom_header_id'] . '"' . $sel . '>v'
            . htmlspecialchars($v['version']) . '（' . htmlspecialchars($v['status']) . '）' . $cur . '</option>';
    }
    return $html;
}

function bomcmp_assembly_options($assemblies, $selected) {
    $html = '<option value="">-- 选择料号 --</option>';
    foreach ($assemblies as $a) {
        $sel = ($selected != '' && $selected == $a['assembly_item_no']) ? ' selected' : '';
        $html .= '<option value="' . htmlspecialchars($a['assembly_item_no']) . '"' . $sel . '>'
            . htmlspecialchars($a['assembly_item_no']) . ' ' . htmlspecialchars($a['item_name']) . '</option>';
    }
    return $html;
}

// 单行替代料 -> 展示字符串
function bomcmp_sub_str($subs) {
    if (empty($subs)) {
        return '—';
    }
    $parts = array();
    foreach ($subs as $s) {
        $q = ($s['substitute_item_quantity'] != '' && $s['substitute_item_quantity'] != 0)
            ? '×' . $s['substitute_item_quantity'] : '';
        $st = ($s['status'] != '生效') ? '(' . htmlspecialchars($s['status']) . ')' : '';
        $parts[] = htmlspecialchars($s['substitute_item']) . $q . $st;
    }
    return implode('、', $parts);
}

// 单行替代料 -> 比对指纹
function bomcmp_sub_key($subs) {
    if (empty($subs)) {
        return '';
    }
    $arr = array();
    foreach ($subs as $s) {
        $arr[] = $s['substitute_item'] . ':' . $s['substitute_item_quantity'] . ':' . $s['status'];
    }
    sort($arr);
    return implode('|', $arr);
}

// 物料完整信息
function bomcmp_get_item_info($db, $item_no) {
    static $cache = array();
    if (isset($cache[$item_no])) return $cache[$item_no];
    $r = DB_query("SELECT * FROM sf_item_no WHERE item_no='" . bomcmp_esc($db, $item_no) . "'", $db);
    $row = DB_fetch_array($r);
    if ($row) {
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

// 按 bom_header_id 取 BOM 头
function bomcmp_header_by_id($db, $id) {
    if (!$id) return false;
    static $cache = array();
    if (isset($cache[$id])) return $cache[$id];
    $r = DB_query("SELECT bom_header_id, version, status, cost_price, approve_by, approve_remark
            FROM bom_headers_all WHERE bom_header_id='" . intval($id) . "' LIMIT 1", $db);
    $cache[$id] = DB_fetch_array($r);
    return $cache[$id];
}

// 取最新 BOM 头（按 bom_header_id 降序，is_current 已废弃）
function bomcmp_latest_header($db, $assembly) {
    static $cache = array();
    if (isset($cache[$assembly])) return $cache[$assembly];
    $c = DB_query("SELECT item_type FROM sf_item_no WHERE item_no='" . bomcmp_esc($db, $assembly) . "'", $db);
    $crow = DB_fetch_array($c);
    if ($crow && $crow['item_type'] === 'M') { $cache[$assembly] = false; return false; }
    $sql = "SELECT bom_header_id, version, status, cost_price, approve_by, approve_remark
            FROM bom_headers_all WHERE assembly_item_no='" . bomcmp_esc($db, $assembly) . "'
            ORDER BY bom_header_id DESC LIMIT 1";
    $r = DB_query($sql, $db);
    $cache[$assembly] = DB_fetch_array($r);
    return $cache[$assembly];
}

// 取 BOM 头下有效行
function bomcmp_get_active_lines($db, $bom_header_id) {
    static $cache = array();
    if (isset($cache[$bom_header_id])) return $cache[$bom_header_id];
    $sql = "SELECT component_sequence_id, component_item, item_num, component_quantity,
                   sunhao_rate, weizhi, operation_seq_num, component_remarks, effectivity_date,
                   component_bom_header_id
            FROM bom_lines_all WHERE bom_header_id='" . bomcmp_esc($db, $bom_header_id) . "'
            AND disable_date=0 ORDER BY item_num";
    $r = DB_query($sql, $db);
    $lines = array();
    while ($row = DB_fetch_array($r)) { $lines[] = $row; }
    $cache[$bom_header_id] = $lines;
    return $lines;
}

// 取单行的替代料
function bomcmp_get_substitutes($db, $component_sequence_id) {
    static $cache = array();
    if (isset($cache[$component_sequence_id])) return $cache[$component_sequence_id];
    $r = DB_query("SELECT substitute_item, substitute_item_quantity, status
        FROM bom_substitutes_all
        WHERE component_sequence_id='" . intval($component_sequence_id) . "'", $db);
    $out = array();
    while ($row = DB_fetch_array($r)) { $out[] = $row; }
    $cache[$component_sequence_id] = $out;
    return $out;
}

// 构建单棵 BOM 树（行级版本锁定优先）
function bomcmp_build_tree($db, $assembly, $line, $root_hdr_id) {
    $info = bomcmp_get_item_info($db, $assembly);
    $hdr = bomcmp_header_by_id($db, $root_hdr_id);
    $hasBOM = $hdr ? true : false;

    $node = array(
        'item_no' => $assembly,
        'item_name' => $info ? $info['item_name'] : $assembly,
        'item_type' => $info ? $info['item_type'] : '',
        'item_category1' => $info ? $info['item_category1'] : '',
        'spec' => $info ? $info['spec'] : '',
        'units' => $info ? $info['units'] : '',
        'has_bom' => $hasBOM,
        'version' => $hdr ? $hdr['version'] : '',
        'status' => $hdr ? $hdr['status'] : '',
        'cost_price' => $hdr ? $hdr['cost_price'] : '',
        'qty' => ($line && isset($line['component_quantity'])) ? $line['component_quantity'] : '',
        'weizhi' => ($line && isset($line['weizhi'])) ? $line['weizhi'] : '',
        'sunhao_rate' => ($line && isset($line['sunhao_rate'])) ? $line['sunhao_rate'] : '',
        'remarks' => ($line && isset($line['component_remarks'])) ? $line['component_remarks'] : '',
        'seq_id' => ($line && isset($line['component_sequence_id'])) ? $line['component_sequence_id'] : 0,
        'children' => array()
    );
    $node['substitutes'] = $node['seq_id'] ? bomcmp_get_substitutes($db, $node['seq_id']) : array();

    if ($hasBOM) {
        $lines = bomcmp_get_active_lines($db, $hdr['bom_header_id']);
        foreach ($lines as $ln) {
            $child_hdr_id = isset($ln['component_bom_header_id']) ? intval($ln['component_bom_header_id']) : 0;
            if ($child_hdr_id == 0) {
                $ch = bomcmp_latest_header($db, $ln['component_item']);
                $child_hdr_id = $ch ? $ch['bom_header_id'] : 0;
            }
            $node['children'][] = bomcmp_build_tree($db, $ln['component_item'], $ln, $child_hdr_id);
        }
    }
    return $node;
}

// 节点匹配键：component_item + weizhi（位号唯一性原则）
function bomcmp_node_key($node) {
    $w = ($node['weizhi'] === null) ? '' : $node['weizhi'];
    return $node['item_no'] . '|' . $w;
}

// 合并旧/新两棵 BOM 树为差异树
function bomcmp_merge_trees($oldNode, $newNode) {
    $diff = 'same';
    $changes = array();
    if ($oldNode === null) {
        $diff = 'add';
    } elseif ($newNode === null) {
        $diff = 'del';
    } else {
        if ((float)$oldNode['qty'] != (float)$newNode['qty']) {
            $changes[] = '用量';
        }
        if ((string)$oldNode['weizhi'] != (string)$newNode['weizhi']) {
            $changes[] = '位号';
        }
        if ((float)$oldNode['sunhao_rate'] != (float)$newNode['sunhao_rate']) {
            $changes[] = '损耗率';
        }
        if ((string)$oldNode['remarks'] != (string)$newNode['remarks']) {
            $changes[] = '备注';
        }
        if (bomcmp_sub_key($oldNode['substitutes']) != bomcmp_sub_key($newNode['substitutes'])) {
            $changes[] = '替代料';
        }
        if (count($changes) > 0) {
            $diff = 'mod';
        }
    }

    $node = array(
        'item_no' => $oldNode ? $oldNode['item_no'] : $newNode['item_no'],
        'item_name' => $oldNode ? $oldNode['item_name'] : $newNode['item_name'],
        'item_type' => $oldNode ? $oldNode['item_type'] : $newNode['item_type'],
        'old' => $oldNode,
        'new' => $newNode,
        'diff' => $diff,
        'changes' => $changes,
        'children' => array()
    );

    $oldChildren = $oldNode ? $oldNode['children'] : array();
    $newChildren = $newNode ? $newNode['children'] : array();
    $node['children'] = bomcmp_merge_children($oldChildren, $newChildren);
    return $node;
}

function bomcmp_merge_children($oldChildren, $newChildren) {
    $map = array();
    foreach ($oldChildren as $c) {
        $map[bomcmp_node_key($c)]['old'] = $c;
    }
    foreach ($newChildren as $c) {
        $map[bomcmp_node_key($c)]['new'] = $c;
    }
    $out = array();
    foreach ($map as $k => $v) {
        $old = isset($v['old']) ? $v['old'] : null;
        $new = isset($v['new']) ? $v['new'] : null;
        $out[] = bomcmp_merge_trees($old, $new);
    }
    return $out;
}

function bomcmp_count_diff($node, &$cnt) {
    if (!isset($cnt[$node['diff']])) $cnt[$node['diff']] = 0;
    $cnt[$node['diff']]++;
    foreach ($node['children'] as $c) {
        bomcmp_count_diff($c, $cnt);
    }
}

function bomcmp_flatten_tree($node, &$rows) {
    $rows[] = $node;
    foreach ($node['children'] as $c) {
        bomcmp_flatten_tree($c, $rows);
    }
}

// 核心：构建差异树
function bomcmp_compare_trees($db, $oldHdr, $newHdr) {
    $sql = "SELECT h.bom_header_id, h.assembly_item_no, h.version, h.status,
            i.item_name, i.item_desc
        FROM bom_headers_all h
        LEFT JOIN sf_item_no i ON i.item_no = h.assembly_item_no
        WHERE h.bom_header_id='" . intval($oldHdr) . "' LIMIT 1";
    $r = DB_query($sql, $db);
    $oldInfo = DB_fetch_array($r);

    $sql2 = "SELECT h.bom_header_id, h.assembly_item_no, h.version, h.status,
            i.item_name, i.item_desc
        FROM bom_headers_all h
        LEFT JOIN sf_item_no i ON i.item_no = h.assembly_item_no
        WHERE h.bom_header_id='" . intval($newHdr) . "' LIMIT 1";
    $r2 = DB_query($sql2, $db);
    $newInfo = DB_fetch_array($r2);

    $oldTree = bomcmp_build_tree($db, $oldInfo['assembly_item_no'], null, $oldHdr);
    $newTree = bomcmp_build_tree($db, $newInfo['assembly_item_no'], null, $newHdr);

    // 顶层料号相同：合并为一个根节点；不同：单根节点同时展示两个顶层
    $diffTree = bomcmp_merge_trees($oldTree, $newTree);
    if ((string)$oldInfo['assembly_item_no'] === (string)$newInfo['assembly_item_no']) {
        $rootChanges = array();
        if ((string)$oldInfo['version'] != (string)$newInfo['version']) {
            $rootChanges[] = '版本';
        }
        if ((string)$oldInfo['status'] != (string)$newInfo['status']) {
            $rootChanges[] = '状态';
        }
        if (count($rootChanges) > 0) {
            $diffTree['diff'] = 'mod';
            $diffTree['changes'] = $rootChanges;
        }
    } else {
        $oldName = $oldInfo['item_name'] ? $oldInfo['item_name'] : $oldInfo['assembly_item_no'];
        $newName = $newInfo['item_name'] ? $newInfo['item_name'] : $newInfo['assembly_item_no'];
        $diffTree['item_no'] = $oldInfo['assembly_item_no'] . ' / ' . $newInfo['assembly_item_no'];
        $diffTree['item_name'] = $oldName . ' / ' . $newName;
        $diffTree['diff'] = 'mod';
        $diffTree['changes'] = array('顶层料号不同');
    }

    $cnt = array('add' => 0, 'del' => 0, 'mod' => 0, 'same' => 0);
    bomcmp_count_diff($diffTree, $cnt);

    return array(
        'oldInfo' => $oldInfo,
        'newInfo' => $newInfo,
        'tree' => $diffTree,
        'cnt' => $cnt
    );
}

// ============================================================
// 页面
// ============================================================
$Title = _('BOM差异比较查询');
$ViewTopic = 'BOM差异比较查询';
$BookMark = 'BOM差异比较查询';
include('includes/header.inc');
echo '<link rel="stylesheet" href="' . $RootPath . '/css/bom_style.css">';
include('includes/SQL_CommonFunctions.inc');

// 所有有 BOM 头的料号
$asmR = DB_query("SELECT DISTINCT a.assembly_item_no, b.item_name
    FROM bom_headers_all a
    LEFT JOIN sf_item_no b ON b.item_no = a.assembly_item_no
    ORDER BY a.assembly_item_no", $db);
$assemblies = array();
while ($row = DB_fetch_array($asmR)) {
    $assemblies[] = $row;
}

// 当前选择
$oldAsm = isset($_POST['old_assembly']) ? $_POST['old_assembly'] : (isset($assemblies[0]) ? $assemblies[0]['assembly_item_no'] : '');
$newAsm = isset($_POST['new_assembly']) ? $_POST['new_assembly'] : (isset($assemblies[0]) ? $assemblies[0]['assembly_item_no'] : '');
$oldHdrSel = isset($_POST['old_hdr']) ? intval($_POST['old_hdr']) : '';
$newHdrSel = isset($_POST['new_hdr']) ? intval($_POST['new_hdr']) : '';

$oldVers = bomcmp_get_versions($db, $oldAsm);
$newVers = bomcmp_get_versions($db, $newAsm);

$compareResult = null;
if (isset($_POST['compare']) && $oldHdrSel != '' && $newHdrSel != '') {
    $compareResult = bomcmp_compare_trees($db, $oldHdrSel, $newHdrSel);
}

echo '<style>
.cmp-wrap{max-width:1400px;margin:0 auto;padding:0 10px;}
.cmp-bar{background:#eef4fb;border:1px solid #cfe0f5;border-radius:6px;padding:12px 16px;margin:10px 0;}
.cmp-bar table{width:100%;border-collapse:collapse;}
.cmp-bar th{text-align:left;font-size:13px;color:#345;padding:4px 8px;width:50%;}
.cmp-bar td{padding:4px 8px;}
.cmp-bar select{width:320px;padding:4px;}
.cmp-btn{padding:5px 18px;background:#1e73c2;color:#fff;border:0;border-radius:4px;cursor:pointer;font-size:13px;}
.cmp-summary{margin:10px 0;font-size:13px;}
.cmp-legend{display:inline-block;margin-left:12px;}
.cmp-legend span{display:inline-block;padding:1px 8px;border-radius:10px;font-size:11px;font-weight:bold;color:#fff;margin:0 2px;}
.badge-add{background:#2e7d32;}
.badge-del{background:#c62828;}
.badge-mod{background:#ef6c00;}
.badge-same{background:#9e9e9e;}
.cmp-tree-wrap{border:1px solid #e0e7ef;border-radius:6px;background:#fff;padding:10px;margin:10px 0;overflow:auto;min-height:320px;}
.cmp-tree-wrap svg{display:block;margin:0 auto;}
.cmp-table{width:100%;border-collapse:collapse;font-size:12px;margin-top:6px;}
.cmp-table th{background:#eef4fb;padding:6px 8px;text-align:left;border:1px solid #dfe7f2;}
.cmp-table td{padding:5px 8px;border:1px solid #e6ebf2;vertical-align:top;}
.cmp-add{background:#e8f5e9;}
.cmp-del{background:#ffebee;}
.cmp-del .old-cell{text-decoration:line-through;color:#c00;}
.cmp-mod{background:#fff8e1;}
.cmp-same{background:#fff;}
.chg{color:#ef6c00;font-weight:bold;}
.view-toggle{text-align:right;margin:6px 0;font-size:12px;}
.view-toggle a{color:#1e73c2;text-decoration:none;margin:0 6px;}
.view-toggle a.active{font-weight:bold;border-bottom:2px solid #1e73c2;}
#cmpTableView{display:none;}
</style>';

echo '<div class="cmp-wrap">';
echo '<div class="cmp-bar"><form method="post" action="BOMCompare.php" id="cmpForm">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '">';
echo '<table><tr><th>旧 BOM（基准）</th><th>新 BOM（对比）</th></tr>';
echo '<tr>';
echo '<td>料号：<select name="old_assembly" id="old_assembly" onchange="bomcmpLoadVers(\'old\')">'
    . bomcmp_assembly_options($assemblies, $oldAsm) . '</select><br>
    版本：<select name="old_hdr" id="old_hdr">' . bomcmp_version_options($oldVers, $oldHdrSel) . '</select></td>';
echo '<td>料号：<select name="new_assembly" id="new_assembly" onchange="bomcmpLoadVers(\'new\')">'
    . bomcmp_assembly_options($assemblies, $newAsm) . '</select><br>
    版本：<select name="new_hdr" id="new_hdr">' . bomcmp_version_options($newVers, $newHdrSel) . '</select></td>';
echo '</tr>';
echo '<tr><td colspan="2" style="text-align:center"><input type="submit" name="compare" value="对比差异" class="cmp-btn"></td></tr>';
echo '</table></form></div>';

// 结果展示
if ($compareResult) {
    $cr = $compareResult;
    if (empty($cr['oldInfo']) || empty($cr['newInfo'])) {
        prnMsg(_('所选 BOM 版本不存在，请重新选择。'), 'error');
    } else {
        $oi = $cr['oldInfo'];
        $ni = $cr['newInfo'];
        echo '<div class="cmp-summary">'
            . '<span class="badge badge-add">新增 ' . $cr['cnt']['add'] . '</span> '
            . '<span class="badge badge-del">删除 ' . $cr['cnt']['del'] . '</span> '
            . '<span class="badge badge-mod">修改 ' . $cr['cnt']['mod'] . '</span> '
            . '<span class="badge badge-same">不变 ' . $cr['cnt']['same'] . '</span>'
            . '<span class="cmp-legend">绿=新增 红=删除 黄=修改 灰=相同</span></div>';
        echo '<p style="font-size:12px;color:#456">旧：<b>' . htmlspecialchars($oi['assembly_item_no']) . '</b> '
            . htmlspecialchars($oi['item_name']) . ' · v' . htmlspecialchars($oi['version']) . '（' . htmlspecialchars($oi['status']) . '）'
            . ' ｜ 新：<b>' . htmlspecialchars($ni['assembly_item_no']) . '</b> '
            . htmlspecialchars($ni['item_name']) . ' · v' . htmlspecialchars($ni['version']) . '（' . htmlspecialchars($ni['status']) . '）</p>';

        echo '<div class="view-toggle">'
            . '<a href="javascript:void(0)" id="btnTreeView" class="active">结构图对比</a>'
            . '<a href="javascript:void(0)" id="btnTableView">表格对比</a></div>';

        // 结构图视图
        echo '<div class="cmp-tree-wrap" id="cmpTreeView"></div>';

        // 表格视图（保留原表格作为辅助）
        echo '<div id="cmpTableView">';
        echo '<table class="cmp-table"><tr>'
            . '<th style="width:64px">变更</th>'
            . '<th style="width:120px">物料</th>'
            . '<th>名称 / 规格</th>'
            . '<th>旧 · 用量/位号/替代料</th>'
            . '<th>新 · 用量/位号/替代料</th>'
            . '<th style="width:120px">变化字段</th></tr>';
        $badgeMap = array('add' => '新增', 'del' => '删除', 'mod' => '修改', 'same' => '不变');
        $flatRows = array();
        bomcmp_flatten_tree($cr['tree'], $flatRows);
        foreach ($flatRows as $row) {
            $t = $row['diff'];
            $o = $row['old'];
            $n = $row['new'];
            $item = $row['item_no'] ? $row['item_no'] : ($o ? $o['item_no'] : $n['item_no']);
            $name = $row['item_name'] ? $row['item_name'] : ($o ? $o['item_name'] : $n['item_name']);
            $spec = $o ? $o['spec'] : ($n ? $n['spec'] : '');
            $subOld = $o ? bomcmp_sub_str($o['substitutes']) : '—';
            $subNew = $n ? bomcmp_sub_str($n['substitutes']) : '—';
            $oldCell = $o
                ? ('用量 ' . $o['qty'] . ' / 位号 ' . ($o['weizhi'] ? htmlspecialchars($o['weizhi']) : '—') . ' / 替代 ' . $subOld)
                : '—';
            $newCell = $n
                ? ('用量 ' . $n['qty'] . ' / 位号 ' . ($n['weizhi'] ? htmlspecialchars($n['weizhi']) : '—') . ' / 替代 ' . $subNew)
                : '—';
            $chg = ($t == 'mod') ? implode('、', $row['changes']) : '';
            echo '<tr class="cmp-' . $t . '">'
                . '<td><span class="badge badge-' . $t . '">' . $badgeMap[$t] . '</span></td>'
                . '<td>' . htmlspecialchars($item) . '</td>'
                . '<td>' . htmlspecialchars($name) . ($spec ? ' / ' . htmlspecialchars($spec) : '') . '</td>'
                . '<td class="old-cell">' . $oldCell . '</td>'
                . '<td>' . $newCell . '</td>'
                . '<td class="chg">' . htmlspecialchars($chg) . '</td>'
                . '</tr>';
        }
        echo '</table></div>';

        // 输出差异树 JSON
        echo '<script>var DIFF_TREE = ' . json_encode($cr['tree']) . ';</script>';
    }
}
echo '</div>';

echo '<script>
function bomcmpLoadVers(side) {
    var asm = document.getElementById(side + "_assembly").value;
    var sel = document.getElementById(side + "_hdr");
    sel.innerHTML = "<option value=\\"\\">加载中...</option>";
    var xhr = new XMLHttpRequest();
    xhr.open("GET", "BOMCompare.php?act=get_versions&assembly=" + encodeURIComponent(asm) + "&_=" + Date.now(), true);
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200) {
            try {
                var arr = JSON.parse(xhr.responseText);
                var html = "<option value=\\"\\">-- 选择版本 --</option>";
                for (var i = 0; i < arr.length; i++) {
                    var v = arr[i];
                    var cur = (v.is_current == 1) ? " ★" : "";
                    html += "<option value=\\"" + v.bom_header_id + "\\">v" + v.version + "（" + v.status + "）" + cur + "</option>";
                }
                sel.innerHTML = html;
            } catch (e) {
                sel.innerHTML = "<option value=\\"\\">加载失败</option>";
            }
        }
    };
    xhr.send();
}

function renderDiffTree(root, container){
    var NS = "http://www.w3.org/2000/svg";
    var NODE_W = 280, NODE_H = 108, GAP_X = 70, GAP_Y = 12, TOP = 24;
    var diffFill = {add:"#e8f5e9", del:"#ffebee", mod:"#fff8e1", same:"#ffffff"};
    var diffStroke = {add:"#2e7d32", del:"#c62828", mod:"#ef6c00", same:"#90a4ae"};
    var diffLabel = {add:"新增", del:"删除", mod:"修改", same:""};
    var typeColor = {F:"#bbdefb", B:"#ffe0b2", M:"#c8e6c9", P:"#fff9c4", "":"#f5f5f5"};

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
    var maxX = 0, maxY = 0;
    (function walk(n){
        if (n._x + NODE_W > maxX) maxX = n._x + NODE_W;
        if (n._y + NODE_H > maxY) maxY = n._y + NODE_H;
        if (n.children) for (var i = 0; i < n.children.length; i++) walk(n.children[i]);
    })(root);

    var svg = document.createElementNS(NS, "svg");
    svg.setAttribute("viewBox", "0 0 " + (maxX + 50) + " " + (maxY + 50));
    svg.setAttribute("width", maxX + 50);
    svg.setAttribute("height", maxY + 50);
    svg.style.background = "#fafbfc";

    function fmtQty(q){ return (q === "" || q === null) ? "—" : q; }
    function fmtWz(w){ return w ? w : "—"; }
    function trunc(s, n){ return (s && s.length > n) ? s.substring(0, n-1) + "…" : (s || ""); }

    function buildNode(n){
        var g = document.createElementNS(NS, "g");
        g.setAttribute("class", "mg-node");
        g.style.cursor = (n.children && n.children.length) ? "pointer" : "default";

        var rect = document.createElementNS(NS, "rect");
        rect.setAttribute("x", n._x);
        rect.setAttribute("y", n._y);
        rect.setAttribute("width", NODE_W);
        rect.setAttribute("height", NODE_H);
        rect.setAttribute("rx", n._depth === 0 ? 8 : 5);
        rect.setAttribute("fill", diffFill[n.diff] || "#fff");
        rect.setAttribute("stroke", diffStroke[n.diff] || "#90a4ae");
        rect.setAttribute("stroke-width", n.diff === "same" ? 1 : 2.5);
        g.appendChild(rect);

        // 类型小色块（左上角）
        var typeDot = document.createElementNS(NS, "rect");
        typeDot.setAttribute("x", n._x + 2);
        typeDot.setAttribute("y", n._y + 2);
        typeDot.setAttribute("width", 14);
        typeDot.setAttribute("height", 14);
        typeDot.setAttribute("rx", 3);
        typeDot.setAttribute("fill", typeColor[n.item_type] || "#e0e0e0");
        g.appendChild(typeDot);

        var cx = n._x + NODE_W / 2;
        var isRoot = n._depth === 0;

        // 第1行：物料编码
        var t1 = document.createElementNS(NS, "text");
        t1.setAttribute("x", cx); t1.setAttribute("y", n._y + 17);
        t1.setAttribute("text-anchor", "middle"); t1.setAttribute("font-size", isRoot ? 13 : 12);
        t1.setAttribute("font-weight", "bold"); t1.setAttribute("font-family", "Consolas,monospace");
        t1.setAttribute("fill", "#222"); t1.textContent = n.item_no || "";
        g.appendChild(t1);

        // 第2行：物料名称
        var t2 = document.createElementNS(NS, "text");
        t2.setAttribute("x", cx); t2.setAttribute("y", n._y + 34);
        t2.setAttribute("text-anchor", "middle"); t2.setAttribute("font-size", 11);
        t2.setAttribute("fill", "#333");
        t2.textContent = trunc(n.item_name, isRoot ? 16 : 18);
        g.appendChild(t2);

        if (isRoot) {
            var t3 = document.createElementNS(NS, "text");
            t3.setAttribute("x", cx); t3.setAttribute("y", n._y + 52);
            t3.setAttribute("text-anchor", "middle"); t3.setAttribute("font-size", 11);
            t3.setAttribute("fill", "#c62828"); t3.setAttribute("font-weight", "600");
            var t4 = document.createElementNS(NS, "text");
            t4.setAttribute("x", cx); t4.setAttribute("y", n._y + 69);
            t4.setAttribute("text-anchor", "middle"); t4.setAttribute("font-size", 11);
            t4.setAttribute("fill", "#2e7d32"); t4.setAttribute("font-weight", "600");
            if (n.item_no === "" && n._depth === 0) {
                // 顶层料号不同的虚拟根：显示实际输入的料号
                var oldAsm = n.old && n.old.assembly_item_no ? n.old.assembly_item_no : "—";
                var newAsm = n.new && n.new.assembly_item_no ? n.new.assembly_item_no : "—";
                t3.textContent = "旧：" + oldAsm;
                t4.textContent = "新：" + newAsm;
            } else {
                // 普通根/顶层：显示版本+状态
                var oldVer = n.old ? (n.old.version + "（" + n.old.status + "）") : "—";
                var newVer = n.new ? (n.new.version + "（" + n.new.status + "）") : "—";
                t3.textContent = "旧：" + oldVer;
                t4.textContent = "新：" + newVer;
            }
            g.appendChild(t3);
            g.appendChild(t4);
        } else {
            var oldQty = n.old ? fmtQty(n.old.qty) : "—";
            var newQty = n.new ? fmtQty(n.new.qty) : "—";
            var oldWz = n.old ? fmtWz(n.old.weizhi) : "—";
            var newWz = n.new ? fmtWz(n.new.weizhi) : "—";

            var t3 = document.createElementNS(NS, "text");
            t3.setAttribute("x", cx); t3.setAttribute("y", n._y + 52);
            t3.setAttribute("text-anchor", "middle"); t3.setAttribute("font-size", 10);
            t3.setAttribute("fill", "#c62828");
            t3.textContent = "旧：用量 " + oldQty + " / 位号 " + oldWz;
            g.appendChild(t3);

            var t4 = document.createElementNS(NS, "text");
            t4.setAttribute("x", cx); t4.setAttribute("y", n._y + 69);
            t4.setAttribute("text-anchor", "middle"); t4.setAttribute("font-size", 10);
            t4.setAttribute("fill", "#2e7d32");
            t4.textContent = "新：用量 " + newQty + " / 位号 " + newWz;
            g.appendChild(t4);
        }

        // 差异标签（右下角）
        if (diffLabel[n.diff]) {
            var badgeW = 34, badgeH = 16;
            var bg = document.createElementNS(NS, "rect");
            bg.setAttribute("x", n._x + NODE_W - badgeW - 6);
            bg.setAttribute("y", n._y + NODE_H - badgeH - 5);
            bg.setAttribute("width", badgeW);
            bg.setAttribute("height", badgeH);
            bg.setAttribute("rx", 8);
            bg.setAttribute("fill", diffStroke[n.diff]);
            g.appendChild(bg);
            var bl = document.createElementNS(NS, "text");
            bl.setAttribute("x", n._x + NODE_W - badgeW / 2 - 6);
            bl.setAttribute("y", n._y + NODE_H - badgeH / 2 + 3 - 5);
            bl.setAttribute("text-anchor", "middle"); bl.setAttribute("font-size", 10);
            bl.setAttribute("fill", "#fff"); bl.setAttribute("font-weight", "bold");
            bl.textContent = diffLabel[n.diff];
            g.appendChild(bl);
        }

        // 变化字段提示（左下角，仅修改）
        if (n.diff === "mod" && n.changes && n.changes.length) {
            var ct = document.createElementNS(NS, "text");
            ct.setAttribute("x", n._x + 8);
            ct.setAttribute("y", n._y + NODE_H - 6);
            ct.setAttribute("font-size", 9);
            ct.setAttribute("fill", "#ef6c00");
            ct.textContent = "变化：" + n.changes.join("、");
            g.appendChild(ct);
        }

        if (n.children && n.children.length){
            var sub = document.createElementNS(NS, "g");
            sub.setAttribute("class", "mg-sub");
            for (var i = 0; i < n.children.length; i++){
                var c = n.children[i];
                var p = document.createElementNS(NS, "path");
                var x1 = n._x + NODE_W, y1 = n._y + NODE_H / 2;
                var x2 = c._x, y2 = c._y + NODE_H / 2;
                var cx2 = (x1 + x2) / 2;
                p.setAttribute("d", "M" + x1 + " " + y1 + " C" + cx2 + " " + y1 + " " + cx2 + " " + y2 + " " + x2 + " " + y2);
                p.setAttribute("stroke", n.diff === "del" ? "#ef9a9a" : (n.diff === "add" ? "#a5d6a7" : "#b478dc"));
                p.setAttribute("stroke-width", "1.5");
                p.setAttribute("fill", "none");
                p.style.pointerEvents = "none";
                sub.appendChild(p);
                sub.appendChild(buildNode(c));
            }
            g.appendChild(sub);
            if (n._collapsed) sub.style.display = "none";
            g.addEventListener("click", function(e){
                e.stopPropagation();
                if (sub.style.display === "none"){
                    sub.style.display = "";
                    n._collapsed = false;
                } else {
                    sub.style.display = "none";
                    n._collapsed = true;
                }
            });
        }
        return g;
    }
    svg.appendChild(buildNode(root));
    container.empty().append(svg);
}

if (typeof DIFF_TREE !== "undefined" && DIFF_TREE) {
    renderDiffTree(DIFF_TREE, jQuery("#cmpTreeView"));
}

jQuery("#btnTreeView").click(function(){
    jQuery("#cmpTreeView").show();
    jQuery("#cmpTableView").hide();
    jQuery("#btnTreeView").addClass("active");
    jQuery("#btnTableView").removeClass("active");
});
jQuery("#btnTableView").click(function(){
    jQuery("#cmpTreeView").hide();
    jQuery("#cmpTableView").show();
    jQuery("#btnTableView").addClass("active");
    jQuery("#btnTreeView").removeClass("active");
});
</script>';

include('includes/footer.inc');
?>
