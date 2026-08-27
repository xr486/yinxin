<?php
/* 零部件查重（防止重复建料 / 相似件检索）
 * 独立页面：输入候选物料特征（名称/型号/图号/规格/材质）+ 过滤条件（分类/类型）
 * 按多字段加权评分（名称45 型号20 图号10 规格10 材质5），动态归一化 → 相似度百分比
 * 相似算法：中文字符袋相似 + 双向包含判定（PHP 5.5 兼容，规避 levenshtein 字节坑）
 * 说明：自动排除"料号名称/料号规格"占位数据；停用物料降权×0.85 并标灰排后
 * 样式复用 MaterialManage 的 mm-* 规范（卡片 + 圆角 + 实心蓝按钮）
 */
include('includes/session.inc');
include('includes/SQL_CommonFunctions.inc');

/* $RootPath 在 header.inc 里定义，此处初始化（本页无 AJAX，仅用于构造链接） */
if (!isset($RootPath)) {
    $RootPath = dirname(htmlspecialchars($_SERVER['PHP_SELF']));
    if ($RootPath == '/' OR $RootPath == '\\') $RootPath = '';
}

/* ============================================================
 * 1. 入参
 * ============================================================ */
$kw_name     = isset($_GET['name']) ? trim($_GET['name']) : '';
$kw_desc     = isset($_GET['desc']) ? trim($_GET['desc']) : '';
$kw_draw     = isset($_GET['draw']) ? trim($_GET['draw']) : '';
$kw_spec     = isset($_GET['spec']) ? trim($_GET['spec']) : '';
$kw_material = isset($_GET['material']) ? trim($_GET['material']) : '';
$kw_cat      = isset($_GET['cat']) ? trim($_GET['cat']) : '';
$kw_type     = isset($_GET['type']) ? trim($_GET['type']) : '';
$submitted = (isset($_GET['do']) && $_GET['do'] == '1');
$hasFeature = ($kw_name != '' || $kw_desc != '' || $kw_draw != '' || $kw_spec != '' || $kw_material != '');
/* 含停用：首次进入默认勾选；提交后按复选框实际状态（未勾选 → 不含停用） */
$incDisabled = $submitted ? ((isset($_GET['inc_disabled']) && $_GET['inc_disabled'] == '1') ? 1 : 0) : 1;

$submitted = (isset($_GET['do']) && $_GET['do'] == '1');
$hasFeature = ($kw_name != '' || $kw_desc != '' || $kw_draw != '' || $kw_spec != '' || $kw_material != '');

/* ============================================================
 * 2. 分类 / 类型下拉数据 + 物料总数
 * ============================================================ */
$catList = array();
$resCat = DB_query("SELECT DISTINCT item_category1 FROM sf_item_no WHERE item_category1 IS NOT NULL AND item_category1 <> '' ORDER BY item_category1", $db);
while ($r = DB_fetch_array($resCat)) { $catList[] = $r['item_category1']; }

$resCnt = DB_query("SELECT COUNT(*) AS c FROM sf_item_no", $db);
$rowCnt = DB_fetch_array($resCnt);
$totalItems = intval($rowCnt['c']);

/* ============================================================
 * 3. 相似度评分函数
 * ============================================================ */
/* 纯字节级 UTF-8 拆字（不依赖 mb_internal_encoding / PCRE-u，CLI 与 Apache 行为一致） */
function dcUtf8Chars($str) {
    $chars = array();
    $len = strlen($str);
    $i = 0;
    while ($i < $len) {
        $c = ord($str[$i]);
        if ($c < 0x80) { $n = 1; }
        elseif (($c & 0xE0) == 0xC0) { $n = 2; }
        elseif (($c & 0xF0) == 0xE0) { $n = 3; }
        elseif (($c & 0xF8) == 0xF0) { $n = 4; }
        else { $n = 1; }
        $chars[] = substr($str, $i, $n);
        $i += $n;
    }
    return $chars;
}

/* 中文字符袋相似度（Jaccard 变体）：字符交集 / 并集 */
function dcSimChar($a, $b) {
    $a = trim($a); $b = trim($b);
    if ($a === '' || $b === '') return 0.0;
    if ($a === $b) return 1.0;
    $arrA = dcUtf8Chars($a);
    $arrB = dcUtf8Chars($b);
    if (count($arrA) == 0 || count($arrB) == 0) return 0.0;
    $cntA = array_count_values($arrA);
    $cntB = array_count_values($arrB);
    $inter = 0;
    foreach ($cntA as $ch => $n) {
        if (isset($cntB[$ch])) $inter += ($n < $cntB[$ch]) ? $n : $cntB[$ch];
    }
    $union = count($arrA) + count($arrB) - $inter;
    return $union > 0 ? $inter / $union : 0.0;
}

/* 通用文本相似度：包含判定（0.75~1.0）与字符袋取高 */
function dcSim($a, $b) {
    $a = trim($a); $b = trim($b);
    if ($a === '' || $b === '') return 0.0;
    if ($a === $b) return 1.0;
    $la = count(dcUtf8Chars($a)); $lb = count(dcUtf8Chars($b));
    if ($la == 0 || $lb == 0) return 0.0;
    $contains = (strpos($a, $b) !== false) || (strpos($b, $a) !== false);
    $charSim = dcSimChar($a, $b);
    if ($contains) {
        $ratio = ($la < $lb) ? $la / $lb : $lb / $la;
        $containScore = 0.75 + 0.25 * $ratio; // 0.75 ~ 1.0
        return ($containScore > $charSim) ? $containScore : $charSim;
    }
    return $charSim;
}

/* 图号相似（更严格）：完全一致 1.0，包含 0.7，否则字符袋×0.6 */
function dcSimDraw($a, $b) {
    $a = trim($a); $b = trim($b);
    if ($a === '' || $b === '') return 0.0;
    if ($a === $b) return 1.0;
    if (strpos($a, $b) !== false || strpos($b, $a) !== false) return 0.7;
    return dcSimChar($a, $b) * 0.6;
}

/* 单元格高亮：把命中的查询关键词包 <span class="dc-hl"> */
function dcHighlight($text, $kws) {
    $esc = htmlspecialchars((string)$text);
    foreach ($kws as $kw) {
        if ($kw === '') continue;
        $kwE = htmlspecialchars($kw);
        $esc = str_ireplace($kwE, '<span class="dc-hl">' . $kwE . '</span>', $esc);
    }
    return $esc;
}

/* ============================================================
 * 4. 粗筛 + 评分
 * ============================================================ */
$results = array();
$resultTotal = 0;
$errMsg = '';

if ($submitted) {
    if (!$hasFeature) {
        $errMsg = '请至少输入 名称 / 型号 / 图号 / 规格 / 材质 中的一项';
    } else {
        /* 粗筛：特征字段 OR 匹配，分类/类型 AND 过滤；排除占位数据 */
        $featureCond = array();
        if ($kw_name != '')     $featureCond[] = "i.item_name LIKE '%" . DB_escape_string($kw_name) . "%'";
        if ($kw_desc != '')     $featureCond[] = "i.item_desc LIKE '%" . DB_escape_string($kw_desc) . "%'";
        if ($kw_draw != '')     $featureCond[] = "i.drawing_no LIKE '%" . DB_escape_string($kw_draw) . "%'";
        if ($kw_spec != '')     $featureCond[] = "i.spec LIKE '%" . DB_escape_string($kw_spec) . "%'";
        if ($kw_material != '') $featureCond[] = "i.material LIKE '%" . DB_escape_string($kw_material) . "%'";

        $conds = array();
        if (count($featureCond) > 0) $conds[] = '(' . implode(' OR ', $featureCond) . ')';
        if ($kw_cat != '')  $conds[] = "i.item_category1 = '" . DB_escape_string($kw_cat) . "'";
        if ($kw_type != '') $conds[] = "i.item_type = '" . DB_escape_string($kw_type) . "'";
        $where = count($conds) > 0 ? implode(' AND ', $conds) : '1=1';
        $where .= " AND i.item_name <> '料号名称' AND i.item_desc <> '料号规格'";
        if ($incDisabled != 1) $where .= " AND i.able_flag = 'Y'";

        $sql = "SELECT i.item_id, i.item_no, i.item_name, i.item_desc, i.item_category1,
                       i.item_type, i.able_flag, i.units, i.drawing_no, i.spec,
                       i.material, i.model, i.supplier_code, i.huohao
                FROM sf_item_no i WHERE " . $where . " ORDER BY i.item_no LIMIT 500";
        $res = DB_query($sql, $db);
        $cands = array();
        while ($r = DB_fetch_array($res)) { $cands[] = $r; }

        /* 评分：仅统计用户填写了的字段权重，动态归一化 */
        $fieldMap = array(
            'name'     => 45,
            'desc'     => 20,
            'draw'     => 10,
            'spec'     => 10,
            'material' => 5,
        );
        $totalW = 0;
        foreach ($fieldMap as $f => $w) {
            if (${'kw_' . $f} != '') $totalW += $w;
        }

        $kws = array($kw_name, $kw_desc, $kw_draw, $kw_spec, $kw_material);

        foreach ($cands as $row) {
            $score = 0.0;
            foreach ($fieldMap as $f => $w) {
                $kw = ${'kw_' . $f};
                if ($kw == '') continue;
                $val = isset($row[$f]) ? trim($row[$f]) : '';
                if ($val == '') continue; // 候选该字段为空 → 不参与（不扣分）
                $s = ($f === 'draw') ? dcSimDraw($kw, $val) : dcSim($kw, $val);
                $score += $w * $s;
            }
            if ($totalW > 0) $score = $score / $totalW * 100;
            $score = round($score);

            $isDisabled = ($row['able_flag'] == 'N');
            if ($isDisabled) $score = round($score * 0.85); // 停用降权

            if ($score < 35) continue; // 低于阈值不显示
            $row['score'] = $score;
            $row['disabled'] = $isDisabled ? 1 : 0;
            $results[] = $row;
        }

        /* 排序：得分降序 → 停用排后 → 料号升序 */
        usort($results, function($a, $b) {
            if ($a['score'] != $b['score']) return ($a['score'] > $b['score']) ? -1 : 1;
            if ($a['disabled'] != $b['disabled']) return $a['disabled'] - $b['disabled'];
            return strcmp($a['item_no'], $b['item_no']);
        });
        $resultTotal = count($results);
        $results = array_slice($results, 0, 200); // 上限 200 条
    }
}

/* ============================================================
 * 5. 渲染
 * ============================================================ */
$Title = _('零部件查重');
$ViewTopic = '零部件查重';
$BookMark = '零部件查重';
include('includes/header.inc');
?>
<link href="<?php echo $RootPath; ?>/css/bom_style.css" rel="stylesheet" type="text/css"/>
<script src="<?php echo $RootPath; ?>/javascript/jquery-1.7.2.min.js"></script>
<script src="<?php echo $RootPath; ?>/JXC/javascript/lhgdialog.min.js?self=true&skin=chrome"></script>

<style type="text/css">
/* ====== 查重页布局（复用 MaterialManage mm-* 规范） ====== */
.dc-wrap{width:100%;max-width:1500px;margin:6px auto 0;box-sizing:border-box}
.dc-head{display:flex;align-items:center;gap:10px;padding:10px 14px;background:#fff;border:1px solid #c5c5c5;border-radius:4px;margin-bottom:10px;flex-wrap:wrap}
.dc-title{font-size:16px;font-weight:bold;color:#0d47a1}
.dc-sub{font-size:12px;color:#888;flex:1;min-width:200px}
.dc-head .dc-tip{font-size:11px;color:#999;background:#f5f7fa;border:1px dashed #d0d7de;border-radius:3px;padding:4px 8px;white-space:nowrap}

/* 查询表单（一行紧凑，与 MaterialManage mm-query-form 一致） */
.dc-query{display:flex;align-items:center;gap:6px;padding:10px 12px;background:#f5f9fd;border:1px solid #d6e4f0;border-radius:4px;margin-bottom:10px;flex-wrap:wrap}
.dc-qlabel{font-size:12px;color:#555;white-space:nowrap}
.dc-qinput{height:28px;padding:3px 8px;border:1px solid #c5d3e0;border-radius:3px;font-size:13px;outline:none;width:120px;background:#fff}
.dc-qinput-wide{width:170px}
.dc-qinput:focus,.dc-qselect:focus{border-color:#1976D2;box-shadow:0 0 0 2px rgba(25,118,210,0.15)}
.dc-qselect{height:28px;padding:3px 6px;border:1px solid #c5d3e0;border-radius:3px;font-size:13px;background:#fff;outline:none}
.dc-qbtn{height:28px;padding:0 14px;background:#fff;border:1px solid #c5d3e0;color:#555;border-radius:3px;cursor:pointer;font-size:13px;transition:all .15s;text-decoration:none;display:inline-flex;align-items:center;line-height:1}
.dc-qbtn:hover{background:#f0f0f0;text-decoration:none;color:#555}
.dc-qbtn-primary{background:#1976D2;color:#fff;border-color:#1976D2}
.dc-qbtn-primary:hover{background:#1565C0;border-color:#1565C0;color:#fff}
.dc-check{display:inline-flex;align-items:center;gap:3px;font-size:12px;color:#555;white-space:nowrap}
.dc-check input{vertical-align:-1px}

/* 结果区 */
.dc-result{background:#fff;border:1px solid #c5c5c5;border-radius:4px;overflow:hidden}
.dc-result-head{display:flex;align-items:center;gap:10px;padding:8px 12px;background:#fafbfc;border-bottom:1px solid #e4e9f0;font-size:13px;color:#555}
.dc-result-head b{color:#1976D2;padding:0 2px}
.dc-stat{margin-left:auto;font-size:12px;color:#888}
.dc-empty{padding:40px 20px;text-align:center;color:#999;font-size:13px}
.dc-error{background:#fdecea;border:1px solid #f5c6cb;color:#c62828;padding:10px 14px;border-radius:4px;font-size:13px;margin-bottom:10px}

/* 表格 */
.dc-table{width:100%;border-collapse:separate;border-spacing:0;font-size:12px;background:#fff}
.dc-table th{background:#2196F3;color:#fff;font-weight:bold;padding:8px 10px;text-align:center;border-right:1px solid #1e88e5;white-space:nowrap}
.dc-table th:last-child{border-right:none}
.dc-table td{padding:7px 10px;text-align:left;border-bottom:1px solid #e8e8e8;border-right:1px solid #e8e8e8;color:#333}
.dc-table td:last-child{border-right:none}
.dc-table tr:last-child td{border-bottom:none}
.dc-table tr:hover td{background:#f5f9ff}
.dc-table-scroll{overflow-x:auto}
.dc-row-disabled td{color:#999;background:#fafafa}
.dc-row-disabled:hover td{background:#f0f0f0}

/* 相似度列：评分条 + 百分比 + 标签 */
.dc-score-cell{white-space:nowrap;min-width:150px}
.dc-scorebar{display:inline-block;width:80px;height:8px;background:#eee;border-radius:4px;vertical-align:middle;margin-right:6px;overflow:hidden}
.dc-scorebar i{display:block;height:100%;border-radius:4px}
.dc-score-num{font-weight:bold;font-size:13px;vertical-align:middle;margin-right:6px}
.dc-tag{display:inline-block;padding:2px 8px;border-radius:3px;font-size:11px;vertical-align:middle}
.dc-tag-high{background:#fdecea;color:#c62828;border:1px solid #f5c6cb}
.dc-tag-mid{background:#fff3e0;color:#e65100;border:1px solid #ffcc80}
.dc-tag-low{background:#e3f2fd;color:#1565C0;border:1px solid #90caf9}

/* 高亮命中的关键词 */
.dc-hl{background:#fff59d;color:#333;border-radius:2px;padding:0 1px;font-weight:normal}
.dc-item-link{color:#1976D2;text-decoration:none;font-weight:500;cursor:pointer}
.dc-item-link:hover{text-decoration:underline}
.dc-status-ok{display:inline-block;padding:2px 8px;border-radius:3px;background:#e8f5e9;color:#2e7d32;border:1px solid #c8e6c9;font-size:11px}
.dc-status-stop{display:inline-block;padding:2px 8px;border-radius:3px;background:#fafafa;color:#888;border:1px solid #e0e0e0;font-size:11px;text-decoration:line-through}
.dc-ops{text-align:center;white-space:nowrap}
.dc-act{display:inline-block;width:24px;height:24px;line-height:24px;text-align:center;border-radius:3px;text-decoration:none;font-size:14px;color:#fff;margin:0 1px;cursor:pointer;border:none;vertical-align:middle}
.dc-act-view{background:#1976D2}
.dc-act-view:hover{background:#1565C0}
.dc-act-copy{background:#FB8C00}
.dc-act-copy:hover{background:#ef6c00}
.dc-act-note{font-size:11px;color:#aaa}
</style>

<div class="dc-wrap">

    <!-- 标题 -->
    <div class="dc-head">
        <span class="dc-title">🔍 零部件查重</span>
        <span class="dc-sub">防止重复建料 · 相似件检索 —— 输入候选物料信息，系统按 名称/型号/图号/规格/材质 加权评分，列出库中疑似重复物料</span>
        <span class="dc-tip">权重：名称45 · 型号20 · 图号10 · 规格10 · 材质5</span>
    </div>

    <!-- 查询表单 -->
    <form method="GET" action="<?php echo $RootPath; ?>/ItemDupCheck.php" class="dc-query" id="dcForm">
        <input type="hidden" name="do" value="1">
        <span class="dc-qlabel">物料名称</span>
        <input type="text" name="name" value="<?php echo htmlspecialchars($kw_name); ?>" class="dc-qinput dc-qinput-wide" placeholder="如：表带主体">
        <span class="dc-qlabel">型号</span>
        <input type="text" name="desc" value="<?php echo htmlspecialchars($kw_desc); ?>" class="dc-qinput dc-qinput-wide" placeholder="如：22mm">
        <span class="dc-qlabel">图号</span>
        <input type="text" name="draw" value="<?php echo htmlspecialchars($kw_draw); ?>" class="dc-qinput" placeholder="如：DWG-001">
        <span class="dc-qlabel">规格</span>
        <input type="text" name="spec" value="<?php echo htmlspecialchars($kw_spec); ?>" class="dc-qinput" placeholder="如：M3x10">
        <span class="dc-qlabel">材质</span>
        <input type="text" name="material" value="<?php echo htmlspecialchars($kw_material); ?>" class="dc-qinput" placeholder="如：不锈钢">
        <span class="dc-qlabel">分类</span>
        <select name="cat" class="dc-qselect">
            <option value="">全部</option>
            <?php foreach ($catList as $c) { ?>
                <option value="<?php echo htmlspecialchars($c); ?>" <?php echo $kw_cat == $c ? 'selected' : ''; ?>><?php echo htmlspecialchars($c); ?></option>
            <?php } ?>
        </select>
        <span class="dc-qlabel">类型</span>
        <select name="type" class="dc-qselect">
            <option value="">全部</option>
            <option value="M" <?php echo $kw_type=='M'?'selected':''; ?>>原材料</option>
            <option value="B" <?php echo $kw_type=='B'?'selected':''; ?>>半成品</option>
            <option value="F" <?php echo $kw_type=='F'?'selected':''; ?>>成品</option>
            <option value="P" <?php echo $kw_type=='P'?'selected':''; ?>>采购件</option>
        </select>
        <label class="dc-check"><input type="checkbox" name="inc_disabled" value="1" <?php echo $incDisabled==1?'checked':''; ?>> 含停用</label>
        <button type="submit" class="dc-qbtn dc-qbtn-primary">🔍 开始查重</button>
        <a href="<?php echo $RootPath; ?>/ItemDupCheck.php" class="dc-qbtn">↻ 清空</a>
    </form>

    <?php if ($errMsg != '') { ?>
        <div class="dc-error">⚠ <?php echo htmlspecialchars($errMsg); ?></div>
    <?php } ?>

    <!-- 结果 -->
    <div class="dc-result">
        <div class="dc-result-head">
            <?php if ($submitted) { ?>
                <span>🔎 查重结果（相似度 ≥ 35% 展示，≥75 高疑似 · 55-74 疑似 · 35-54 可能相似）</span>
                <span class="dc-stat">共匹配 <b><?php echo $resultTotal; ?></b> 条<?php echo $resultTotal > 200 ? '（仅显示前 200 条）' : ''; ?></span>
            <?php } else { ?>
                <span>📋 使用说明</span>
                <span class="dc-stat">库中现有 <b><?php echo number_format($totalItems); ?></b> 条物料</span>
            <?php } ?>
        </div>

        <?php if (!$submitted) { ?>
            <div class="dc-empty">
                <div style="font-size:22px;margin-bottom:8px">🔍</div>
                在上方输入候选物料的 <b>名称 / 型号 / 图号 / 规格 / 材质</b> 后点击「开始查重」<br>
                <span style="font-size:11px;color:#bbb">建议至少填 名称 或 图号；可配合 分类/类型 过滤缩小范围</span>
            </div>
        <?php } elseif (count($results) == 0) { ?>
            <div class="dc-empty">
                <div style="font-size:22px;margin-bottom:8px">✅</div>
                未找到相似度 ≥ 35% 的物料，<b>可以放心新建</b>
            </div>
        <?php } else { ?>
            <div class="dc-table-scroll">
            <table class="dc-table">
                <thead>
                <tr>
                    <th width="170">相似度</th>
                    <th width="110">物料编码</th>
                    <th width="170">物料名称</th>
                    <th width="150">型号</th>
                    <th width="110">分类</th>
                    <th width="70">类型</th>
                    <th width="70">状态</th>
                    <th width="110">主供应商</th>
                    <th width="90">操作</th>
                </tr>
                </thead>
                <tbody>
                <?php
                $TypeMap = array('M'=>'原材料','B'=>'半成品','F'=>'成品','P'=>'采购件');
                foreach ($results as $row):
                    $score = $row['score'];
                    $cls = $score >= 75 ? 'high' : ($score >= 55 ? 'mid' : 'low');
                    $tagTxt = $score >= 75 ? '高疑似' : ($score >= 55 ? '疑似' : '可能相似');
                    $barColor = $score >= 75 ? '#e53935' : ($score >= 55 ? '#fb8c00' : '#1976D2');
                    $tp = isset($TypeMap[$row['item_type']]) ? $TypeMap[$row['item_type']] : $row['item_type'];
                ?>
                <tr class="<?php echo $row['disabled'] ? 'dc-row-disabled' : ''; ?>">
                    <td class="dc-score-cell">
                        <span class="dc-scorebar"><i style="width:<?php echo $score; ?>%;background:<?php echo $barColor; ?>"></i></span>
                        <span class="dc-score-num" style="color:<?php echo $barColor; ?>"><?php echo $score; ?>%</span>
                        <span class="dc-tag dc-tag-<?php echo $cls; ?>"><?php echo $tagTxt; ?></span>
                    </td>
                    <td><a class="dc-item-link" href="javascript:void(0)" onclick="DcOpenView('<?php echo htmlspecialchars(addslashes($row['item_no'])); ?>')"><?php echo htmlspecialchars($row['item_no']); ?></a></td>
                    <td><?php echo dcHighlight($row['item_name'], $kws); ?></td>
                    <td><?php echo dcHighlight($row['item_desc'], $kws); ?></td>
                    <td><?php echo htmlspecialchars($row['item_category1']); ?></td>
                    <td><?php echo htmlspecialchars($tp); ?></td>
                    <td>
                        <?php if ($row['disabled']) { ?>
                            <span class="dc-status-stop">停用</span>
                        <?php } else { ?>
                            <span class="dc-status-ok">启用</span>
                        <?php } ?>
                    </td>
                    <td><?php echo $row['supplier_code'] != '' ? htmlspecialchars($row['supplier_code']) : '<span style="color:#bbb">—</span>'; ?></td>
                    <td class="dc-ops">
                        <a class="dc-act dc-act-view" href="javascript:void(0)" onclick="DcOpenView('<?php echo htmlspecialchars(addslashes($row['item_no'])); ?>')" title="查看详情">👁</a>
                        <a class="dc-act dc-act-copy" href="javascript:void(0)" onclick="DcCopyNo('<?php echo htmlspecialchars(addslashes($row['item_no'])); ?>')" title="复制料号">📋</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        <?php } ?>
    </div>

    <div style="height:20px"></div>
</div>

<script type="text/javascript">
var DC_ROOT = '<?php echo $RootPath; ?>';

/* 统一弹窗（与 MaterialManage mmOpenFrameDialog 同款：视口自适应，不超屏） */
function DcOpenFrameDialog(title, url) {
    var vw = (typeof window.jQuery !== 'undefined') ? jQuery(window).width() : window.innerWidth;
    var vh = (typeof window.jQuery !== 'undefined') ? jQuery(window).height() : window.innerHeight;
    var dlgW = Math.min(1160, vw - 40);
    var dlgH = Math.min(825, vh - 40);
    $.dialog({
        title: title,
        width: dlgW,
        height: dlgH,
        content: 'url:' + url,
        lock: true,
        max: false,
        min: false
    });
}

/* 查看物料详情 */
function DcOpenView(itemNo) {
    DcOpenFrameDialog('物料详情：' + itemNo, DC_ROOT + '/MaterialDetail.php?item_no=' + encodeURIComponent(itemNo) + '&embed=1');
}

/* 复制料号到剪贴板 */
function DcCopyNo(itemNo) {
    var done = function() { DcToast('已复制料号：' + itemNo, 'ok'); };
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(itemNo).then(done, function() { DcCopyNoFallback(itemNo, done); });
    } else {
        DcCopyNoFallback(itemNo, done);
    }
}
function DcCopyNoFallback(itemNo, done) {
    var ta = document.createElement('textarea');
    ta.value = itemNo;
    ta.style.position = 'fixed';
    ta.style.opacity = '0';
    document.body.appendChild(ta);
    ta.select();
    try { document.execCommand('copy'); done(); } catch(e) { DcToast('复制失败，请手动复制', 'err'); }
    document.body.removeChild(ta);
}

/* 轻提示 */
function DcToast(msg, type) {
    try {
        var bg = (type === 'err') ? '#c62828' : (type === 'warn' ? '#f57c00' : '#2e7d32');
        var $t = jQuery('<div class="dc-toast"></div>').text(msg).css({
            'position': 'fixed', 'top': '60px', 'left': '50%', 'transform': 'translateX(-50%)',
            'background': bg, 'color': '#fff', 'padding': '8px 18px', 'border-radius': '4px',
            'box-shadow': '0 2px 8px rgba(0,0,0,0.25)', 'font-size': '13px', 'z-index': '99999',
            'max-width': '80%', 'word-break': 'break-all'
        });
        jQuery('body').append($t);
        $t.hide().fadeIn(180);
        setTimeout(function(){ $t.fadeOut(220, function(){ jQuery(this).remove(); }); }, 1800);
    } catch(e) { try { alert(msg); } catch(e2) {} }
}

/* Enter 快捷提交 */
document.addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && e.target && e.target.tagName === 'INPUT') {
        var f = document.getElementById('dcForm');
        if (f) { e.preventDefault(); f.submit(); }
    }
});
</script>

<?php include('includes/footer.inc'); ?>
