<?php
/* 通用编码规则：承载 segments 解析、各段生成、流水/随机、校验。
 * 供 EncodingRule.php（预览/保存） 与 AddItemNo.php（自动生成） 共用。
 * 段类型：FIXED 固定前缀 / DATE 日期 / SERIAL 流水(顺序或随机) / RANDOM 随机串。
 * 注意：DB_escape_string 内含 htmlspecialchars，会破坏 JSON，因此 JSON 字段一律用 addslashes 转义。
 * ============================================================ */

// 读取唯一规则。返回 array('segments'=>array, 'status'=>'Y'/'N')；表空 status='N'、segments=array()。
function er_load_rule($db) {
    $r = DB_query("SELECT rule_id, segments, status FROM encoding_rule ORDER BY rule_id LIMIT 1", $db);
    $row = DB_fetch_array($r);
    if (!$row) return array('segments' => array(), 'status' => 'N');
    $segs = json_decode($row['segments'], true);
    if (!is_array($segs)) $segs = array();
    return array('segments' => $segs, 'status' => $row['status']);
}

// 在某前缀下取最大流水+1，补零 digits 位；无则从 start 起。
function er_next_serial($db, $prefix, $digits, $start) {
    $digits = max(1, min(12, intval($digits)));
    $start  = max(0, intval($start));
    $plen   = strlen($prefix);
    $sql = "SELECT lpad((max(cast(substr(item_no, ".($plen+1).", ".$digits.") as unsigned))+1), ".$digits.",0) po_num
            FROM sf_item_no
            WHERE item_no <> '999999'
              AND char_length(item_no) = ".($plen+$digits)."
              AND substr(item_no, 1, ".$plen.") = '".DB_escape_string($prefix)."'
              AND substr(item_no, ".($plen+1).", ".$digits.") REGEXP '^[0-9]+$'";
    $r = DB_query($sql, $db);
    while ($v = DB_fetch_array($r)) {
        if ($v['po_num'] !== null) return $v['po_num'];
    }
    return str_pad($start, $digits, '0', STR_PAD_LEFT);
}

// 随机段字符集（剔除易混淆 0/O/1/I）。
function er_rand_part($digits) {
    $chars = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
    $out = '';
    $n = max(1, min(12, intval($digits)));
    for ($i = 0; $i < $n; $i++) { $out .= $chars[mt_rand(0, strlen($chars) - 1)]; }
    return $out;
}

// 料号是否已存在（用于随机段查重）。
function er_item_exists($db, $item_no) {
    $r = DB_query("SELECT COUNT(*) c FROM sf_item_no WHERE item_no='" . DB_escape_string($item_no) . "'", $db);
    $row = DB_fetch_array($r);
    return $row && intval($row['c']) > 0;
}

// DATE 段格式映射。
function er_date_part($format) {
    switch ($format) {
        case 'YYMM':       return date('ym');
        case 'YYYY-MM-DD': return date('Y-m-d');
        case 'YYMMDD':     return date('ymd');
        default:           return date('Ymd'); // YYYYMMDD
    }
}

// 单段生成内容（供首拼与查重重拼用）。随机相关段返回 array(random=>true, part=>...)；其余返回 part。
function er_seg_part($db, $s, $buf) {
    $type = isset($s['type']) ? $s['type'] : 'FIXED';
    if ($type == 'FIXED') {
        return isset($s['value']) ? (string)$s['value'] : '';
    } elseif ($type == 'DATE') {
        return er_date_part(isset($s['format']) ? $s['format'] : 'YYYYMMDD');
    } elseif ($type == 'SERIAL') {
        $mode = isset($s['mode']) ? $s['mode'] : 'serial';
        if ($mode == 'random') return array('random' => true, 'part' => er_rand_part(isset($s['digits']) ? $s['digits'] : 4));
        return er_next_serial($db, $buf, isset($s['digits']) ? $s['digits'] : 6, isset($s['start']) ? $s['start'] : 1);
    } elseif ($type == 'RANDOM') {
        return array('random' => true, 'part' => er_rand_part(isset($s['digits']) ? $s['digits'] : 4));
    }
    return ''; // 未知段
}

// 按 segments 顺序逐段拼装。返回 string；含随机段时对整条查重（最多 5 次）。
function er_generate($db, $segs) {
    if (!is_array($segs) || empty($segs)) return '';
    $hasRandom = false;
    foreach ($segs as $s) {
        if (isset($s['type']) && $s['type'] == 'RANDOM') { $hasRandom = true; }
        if (isset($s['type']) && $s['type'] == 'SERIAL' && isset($s['mode']) && $s['mode'] == 'random') { $hasRandom = true; }
    }
    $build = function(&$segs, $db) {
        $buf = '';
        $n   = count($segs);
        foreach ($segs as $i => $s) {
            $part = er_seg_part($db, $s, $buf);
            if (is_array($part)) $part = $part['part'];
            $buf .= $part;
            /* 分隔符连接当前段与下一段：段尾拼接，末段不再拼 */
            $sep = (isset($s['sep'])) ? (string)$s['sep'] : '';
            if ($i < $n - 1 && $sep !== '') $buf .= $sep;
        }
        return $buf;
    };
    $buf = $build($segs, $db);
    if ($buf === '') return '';
    $tries = 0;
    while ($hasRandom && er_item_exists($db, $buf) && $tries < 5) {
        $buf = $build($segs, $db); // 重新生成随机相关段后再查重
        $tries++;
    }
    return $buf;
}

// 校验段结构；返回错误字符串，空串=合法。$maxLen 为料号列最大长度。
function er_validate_segments($segs, $maxLen = 30) {
    if (!is_array($segs) || empty($segs)) return '至少需要一个编码段';
    $len = 0;
    $serialCount = 0;
    foreach ($segs as $s) {
        if (!is_array($s) || !isset($s['type'])) return '段结构不完整';
        $type = $s['type'];
        if (!in_array($type, array('FIXED','DATE','SERIAL','RANDOM'), true)) return '未知段类型：' . htmlspecialchars($type);
        $sep = isset($s['sep']) ? (string)$s['sep'] : '';
        if (!preg_match('/^[\-_\.\s]{0,3}$/', $sep)) return '分隔符只能为 - _ . 空格，且不超过 3 位';
        if ($type == 'FIXED' && !preg_match('/^[A-Za-z0-9\-]{0,20}$/', (string)@$s['value'])) return '固定前缀含非法字符（仅字母/数字/短横线，≤20位）';
        if ($type == 'DATE' && !in_array(@$s['format'], array('YYYYMMDD','YYMM','YYYY-MM-DD','YYMMDD'), true)) return '日期格式不合法';
        if ($type == 'SERIAL') {
            $d = max(1, min(12, intval(@$s['digits'])));
            $mode = @$s['mode'] == 'random' ? 'random' : 'serial';
            $len += ($mode == 'random') ? $d : $d;
            if ($mode == 'serial') $serialCount++;
        }
        if ($type == 'RANDOM') { $len += max(1, min(12, intval(@$s['digits']))); }
        if ($type == 'FIXED')  { $len += strlen((string)@$s['value']); }
        if ($type == 'DATE')   { $len += (@$s['format'] == 'YYYY-MM-DD') ? 10 : ((@$s['format'] == 'YYYYMMDD') ? 8 : ((@$s['format'] == 'YYMMDD') ? 6 : 4)); }
        $len += strlen($sep);
        if ($len > $maxLen) return '编码总长度超过上限 ' . $maxLen . '，请减小位数或删除部分段';
    }
    if ($serialCount == 0) return '编码中至少需要一个顺序/流水段以保证唯一递增';
    return '';
}