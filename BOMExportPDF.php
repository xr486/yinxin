<?php
header("Content-Type:text/html;charset=utf-8");
ob_start();
include('includes/session2.inc');

// 参数：母件编码 + 可选版本（不传则取最新版本）
$assembly = isset($_GET['assembly']) ? trim($_GET['assembly']) : '';
$version  = isset($_GET['version'])  ? trim($_GET['version'])  : '';

if ($assembly == '') {
    echo '缺少参数 assembly（母件编码）';
    include('includes/footer.inc');
    exit;
}

$db = $db; // session2.inc 已建立连接

// ---------- 取 BOM 头 ----------
function bomGetHeader($db, $assembly, $version) {
    if ($version != '') {
        $r = DB_query("SELECT h.bom_header_id, h.version, h.status, h.cost_price, h.approve_by,
                i.item_name, i.item_desc, i.units
            FROM bom_headers_all h
            LEFT JOIN sf_item_no i ON i.item_no = h.assembly_item_no
            WHERE h.assembly_item_no='" . DB_escape_string($assembly) . "' AND h.version='" . DB_escape_string($version) . "'", $db);
    } else {
        $r = DB_query("SELECT h.bom_header_id, h.version, h.status, h.cost_price, h.approve_by,
                i.item_name, i.item_desc, i.units
            FROM bom_headers_all h
            LEFT JOIN sf_item_no i ON i.item_no = h.assembly_item_no
            WHERE h.assembly_item_no='" . DB_escape_string($assembly) . "'
            ORDER BY h.bom_header_id DESC LIMIT 1", $db);
    }
    return DB_fetch_array($r);
}

function bomGetItemInfo($db, $item) {
    $r = DB_query("SELECT item_name, item_desc, units, item_type FROM sf_item_no WHERE item_no='" . DB_escape_string($item) . "'", $db);
    return DB_fetch_array($r);
}

function bomGetSubs($db, $seqId) {
    $r = DB_query("SELECT substitute_item, substitute_item_quantity, status
        FROM bom_substitutes_all WHERE component_sequence_id='" . DB_escape_string($seqId) . "'", $db);
    $arr = array();
    while ($s = DB_fetch_array($r)) { $arr[] = $s; }
    return $arr;
}

// ---------- 递归展开（连接跟随 + 防循环）----------
$rows = array();
$seq = 0;
$visited = array();
$levelSeq = array(); // 层级序号栈，用于生成 1/1.1/1.1.1 层次码
function bomCollect($db, $assembly, $version, $level, &$rows, &$seq, &$visited, &$levelSeq) {
    $key = $assembly . '|' . ($version ?: 'LATEST');
    if (in_array($key, $visited, true)) return; // 防循环
    $visited[] = $key;
    $hdr = bomGetHeader($db, $assembly, $version);
    if (!$hdr) return;

    // 进入本级时： deeper levels 归 0，本级 +1
    for ($i = $level + 1; $i < count($levelSeq) + 1; $i++) { $levelSeq[$i] = 0; }

    $r = DB_query("SELECT component_sequence_id, component_item, component_quantity, sunhao_rate, weizhi, component_remarks, operation_seq_num, component_bom_header_id
        FROM bom_lines_all WHERE bom_header_id='" . DB_escape_string($hdr['bom_header_id']) . "' AND disable_date=0 ORDER BY item_num", $db);
    while ($ln = DB_fetch_array($r)) {
        $levelSeq[$level] = isset($levelSeq[$level]) ? $levelSeq[$level] + 1 : 1;
        $codeParts = array();
        for ($i = 1; $i <= $level; $i++) { $codeParts[] = isset($levelSeq[$i]) ? $levelSeq[$i] : 1; }
        $levelCode = implode('.', $codeParts);

        $info = bomGetItemInfo($db, $ln['component_item']);
        $seq++;
        $rows[] = array(
            'level'     => $level,
            'levelCode' => $levelCode,
            'no'        => $seq,
            'item'      => $ln['component_item'],
            'name'      => $info ? $info['item_name'] : '',
            'desc'      => $info ? $info['item_desc'] : '',
            'uom'       => $info ? $info['units'] : '',
            'qty'       => $ln['component_quantity'],
            'weizhi'    => $ln['weizhi'],
            'remark'    => $ln['component_remarks'],
            'subs'      => bomGetSubs($db, $ln['component_sequence_id']),
        );
        // 子件为成品/半成品且有 BOM → 递归展开；优先用绑定的子版本
        if ($info && ($info['item_type'] == 'F' || $info['item_type'] == 'B')) {
            $childVer = '';
            if ($ln['component_bom_header_id']) {
                $cr = DB_query("SELECT version FROM bom_headers_all WHERE bom_header_id='" . DB_escape_string($ln['component_bom_header_id']) . "'", $db);
                $crow = DB_fetch_array($cr);
                $childVer = $crow ? $crow['version'] : '';
            }
            bomCollect($db, $ln['component_item'], $childVer, $level + 1, $rows, $seq, $visited, $levelSeq);
        }
    }
}

$hdr = bomGetHeader($db, $assembly, $version);
if (!$hdr) {
    echo '未找到 BOM：' . htmlspecialchars($assembly) . ($version != '' ? ' v' . htmlspecialchars($version) : '（最新版本）');
    include('includes/footer.inc');
    exit;
}
bomCollect($db, $assembly, $version, 1, $rows, $seq, $visited, $levelSeq);

// ---------- 生成 PDF ----------
require_once('includes/tcpdf/tcpdf.php');
// 抑制 E_NOTICE（tcpdf 在生成 XMP 元数据时访问未定义 $title 属性，会污染 PDF 输出流）
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);

$pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8');
$pdf->SetCreator('yixin ERP');
$pdf->SetAuthor('yixin');

// 嵌入中文字体（优先 simhei.ttf；Windows 开发机自带）
$fontName = 'helvetica';
$fontCandidates = array('C:/Windows/Fonts/simhei.ttf', 'C:/Windows/Fonts/STSONG.TTF', 'C:/Windows/Fonts/msyh.ttc');
foreach ($fontCandidates as $fp) {
    if (file_exists($fp)) {
        try {
            $f = $pdf->addTTFfont($fp, 'TrueTypeUnicode', '', 32);
            if ($f) { $fontName = $f; break; }
        } catch (Exception $e) { /* 尝试下一个 */ }
    }
}
$pdf->SetTitle('BOM结构 - ' . $assembly);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(true);
$pdf->SetMargins(10, 12, 10);
$pdf->SetAutoPageBreak(true, 14);
$pdf->SetFont($fontName, '', 9);
$pdf->AddPage();

// 头部信息
$info = '<table border="0" cellpadding="2" cellspacing="0" style="font-size:10pt">'
    . '<tr><td><b>母件编码：</b>' . htmlspecialchars($assembly) . '</td>'
    . '<td><b>名称：</b>' . htmlspecialchars($hdr['item_name']) . '</td>'
    . '<td><b>版本：</b>' . htmlspecialchars($hdr['version']) . '</td>'
    . '<td><b>状态：</b>' . htmlspecialchars($hdr['status']) . '</td>'
    . '<td><b>成本：</b>' . htmlspecialchars($hdr['cost_price']) . '</td></tr></table>';
$pdf->writeHTML($info, true, false, true, false, '');
$pdf->Ln(2);

// 明细表（固定单行高度 + 按字符数截断，彻底消除 CJK 在 getNumLines 下的列高错位/空白）
// 说明：本项目嵌入的 simhei 字体在 TCPDF getNumLines 下对中文换行计算不稳定，
// 故统一采用「单行 + 按列宽估算字符上限截断」策略，保证每行高度一致、无空白。
$cols = array(
    array('w'=>16, 'title'=>_('阶层'), 'align'=>'C', 'key'=>'levelCode', 'maxc'=>10),
    array('w'=>10, 'title'=>_('序号'), 'align'=>'C', 'key'=>'no',        'maxc'=>6),
    array('w'=>50, 'title'=>_('料号'),  'align'=>'L', 'key'=>'item',      'maxc'=>18),
    array('w'=>55, 'title'=>_('名称'),  'align'=>'L', 'key'=>'name',      'maxc'=>17),
    array('w'=>55, 'title'=>_('规格'),  'align'=>'L', 'key'=>'desc',      'maxc'=>17),
    array('w'=>16, 'title'=>_('用量'),  'align'=>'R', 'key'=>'qty',       'maxc'=>10),
    array('w'=>14, 'title'=>_('单位'),  'align'=>'C', 'key'=>'uom',       'maxc'=>6),
    array('w'=>22, 'title'=>_('位号'),  'align'=>'L', 'key'=>'weizhi',    'maxc'=>8),
    array('w'=>28, 'title'=>_('备注'),  'align'=>'L', 'key'=>'remark',    'maxc'=>10),
);
$lineH = 5.2;   // 固定行高（单行）
$fontSize = 8;
$headH = 7;

// 按字符数截断（中文按 1 计），超出加省略号，保证单行不溢出
function pdfFix($txt, $maxc) {
    $txt = trim($txt);
    if ($txt == '') return '';
    if (mb_strlen($txt, 'UTF-8') > $maxc) {
        return mb_substr($txt, 0, $maxc, 'UTF-8') . '...';
    }
    return $txt;
}

// 表头
$pdf->SetFont($fontName, 'B', $fontSize);
$pdf->SetFillColor(230, 240, 250);
foreach ($cols as $c) {
    $pdf->Cell($c['w'], $headH, $c['title'], 1, 0, $c['align'], true);
}
$pdf->Ln();
$pdf->SetFont($fontName, '', $fontSize);

$rowIdx = 0;
foreach ($rows as $row) {
    $rowIdx++;

    $cellData = array(
        'levelCode' => pdfFix($row['levelCode'], 10),
        'no'        => pdfFix($row['no'], 6),
        'item'      => pdfFix($row['item'], 18),
        'name'      => pdfFix($row['name'], 17),
        'desc'      => pdfFix($row['desc'], 17),
        'qty'       => pdfFix($row['qty'], 10),
        'uom'       => pdfFix($row['uom'], 6),
        'weizhi'    => pdfFix($row['weizhi'], 8),
        'remark'    => pdfFix($row['remark'], 10),
    );

    $x0 = $pdf->GetX();
    $y0 = $pdf->GetY();

    // 自动换页（含表头重复）
    $pageH = $pdf->getPageHeight();
    $bottomMargin = $pdf->getBreakMargin();
    if ($y0 + $lineH > $pageH - $bottomMargin) {
        $pdf->AddPage();
        $pdf->SetFont($fontName, 'B', $fontSize);
        $pdf->SetFillColor(230, 240, 250);
        foreach ($cols as $c) {
            $pdf->Cell($c['w'], $headH, $c['title'], 1, 0, $c['align'], true);
        }
        $pdf->Ln();
        $pdf->SetFont($fontName, '', $fontSize);
        $x0 = $pdf->GetX();
        $y0 = $pdf->GetY();
        $rowIdx = 1;
    }

    // 层级底色：越深层级灰度越深，同层级奇偶行微调，提升可读性
    $baseGray = max(255 - ($row['level'] - 1) * 9, 228);
    $fillGray = ($rowIdx % 2 == 0) ? max($baseGray - 5, 222) : $baseGray;
    $pdf->SetFillColor($fillGray, $fillGray, $fillGray);

    // 绘制一行（所有列统一高度 lineH，无空白）
    foreach ($cols as $c) {
        $txt = $cellData[$c['key']];
        $pdf->SetXY($x0, $y0);
        $pdf->Cell($c['w'], $lineH, $txt, 1, 0, $c['align'], true);
        $x0 += $c['w'];
    }
    $margins = $pdf->getMargins();
    $pdf->SetXY($margins['left'], $y0 + $lineH);
}

ob_clean();
$pdf->Output('BOM_' . $assembly . '_v' . $hdr['version'] . '.pdf', 'I');
