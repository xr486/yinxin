<?php
set_time_limit(0);
ob_start();
include('includes/session.inc');
$Title = _('BOM成本批量导入');
$ViewTopic = 'BOM成本批量导入';
$BookMark = 'BOM成本批量导入';

// ===== 下载模板（必须在 header.inc 输出任何 HTML 之前执行，否则文件头会被污染）=====
if (isset($_GET['download_template']) && $_GET['download_template'] == '1') {
    // 先彻底清空所有输出缓冲，避免 session.inc 等前置代码产生的任何内容混入 xlsx 文件头
    while (ob_get_level()) { ob_end_clean(); }
    include_once('xlsxwriter.class.php');
    // 用 ASCII 文件名，避免中文在部分浏览器的 Content-Disposition 解析歧义
    $filename = 'BOM_cost_template.xlsx';
    header('Content-disposition: attachment; filename="' . $filename . '"');
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Transfer-Encoding: binary');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');

    $writer = new XLSXWriter();
    $writer->setAuthor('yixin ERP');
    $writer->writeSheetRow('Sheet1', array('物料编码', '版本（可留空）', '成本'));
    $writer->writeSheetRow('Sheet1', array('78000022', '1', '12.50'));
    $writer->writeSheetRow('Sheet1', array('78000023', '', '8.00'));
    // 用 writeToString + echo（实测比 writeToStdOut 的 readfile 更稳定，避免错误文本混入文件头）
    echo $writer->writeToString();
    exit(0);
}

header("Content-Type:text/html;charset=utf-8");
include('includes/header.inc');

// 统一读取 .xls / .xlsx：返回与 excel/excel.php 一致的 cells[row][col] 结构
function readExcelToCells($filePath, $ext) {
    $cells = array();
    $ext = strtolower($ext);
    if ($ext == 'xlsx') {
        require_once('PHPExcel/IOFactory.php');
        $reader = PHPExcel_IOFactory::createReader('Excel2007');
        $reader->setReadDataOnly(true);
        $excel = $reader->load($filePath);
        $sheet = $excel->getSheet(0);
        $maxRow = $sheet->getHighestRow();
        $maxCol = PHPExcel_Cell::columnIndexFromString($sheet->getHighestColumn());
        for ($r = 1; $r <= $maxRow; $r++) {
            for ($c = 0; $c < $maxCol; $c++) {
                $cell = $sheet->getCellByColumnAndRow($c, $r);
                $v = $cell->getValue();
                if (is_object($v)) { $v = (string)$v; }
                // 科学计数或浮点统一转字符串，保留最多 4 位小数
                if (is_numeric($v) && strpos($v, '.') !== false) { $v = rtrim(rtrim(sprintf('%.4f', $v), '0'), '.'); }
                $cells[$r][$c + 1] = ($v === null || $v === '') ? '' : trim($v);
            }
        }
    } else {
        // 历史 .xls 继续用项目自带 reader
        include("excel/excel.php");
        $excel = new Excel();
        $excel->setOutputEncoding('utf-8');
        $excel->read($filePath);
        $cells = isset($excel->sheets[0]['cells']) ? $excel->sheets[0]['cells'] : array();
    }
    return $cells;
}

echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('BOM成本批量导入') .
 '" alt="" />' . ' ' . $Title . '</p>';

// ===== 处理上传 =====
if (isset($_FILES['userfile']) and $_FILES['userfile']['tmp_name']) {

    // 文件上传友好校验
    $upErr = isset($_FILES['userfile']['error']) ? $_FILES['userfile']['error'] : 0;
    if ($upErr != 0) {
        $upErrMsg = array(
            1 => '文件大小超过服务器限制（php.ini upload_max_filesize）！',
            2 => '文件大小超过表单限制（MAX_FILE_SIZE）！',
            3 => '文件只上传了一部分，请重新上传！',
            4 => '未选择文件！',
            6 => '服务器临时目录不可用，请联系管理员！',
            7 => '文件写入磁盘失败，请重试！',
            8 => '上传被服务器扩展拦截，请联系管理员！'
        );
        $errMsg = isset($upErrMsg[$upErr]) ? $upErrMsg[$upErr] : '上传失败（错误码 ' . $upErr . '），请重试！';
        echo '<div style="border:1px solid #f5c6cb;background:#fdf0f0;border-radius:10px;padding:24px;text-align:center;margin:20px auto;max-width:640px;box-shadow:0 2px 8px rgba(220,53,69,.08)">';
        echo '<div style="font-size:17px;color:#c0392b;font-weight:bold;margin-bottom:8px">⚠️ 上传失败</div>';
        echo '<div style="color:#555;margin-bottom:16px">' . htmlspecialchars($errMsg) . '</div>';
        echo '<a href="' . $RootPath . '/BOMCostImport.php" style="background:#1976D2;color:#fff;padding:9px 36px;border-radius:6px;text-decoration:none;font-size:14px;font-weight:600;display:inline-block">返回重新上传</a>';
        echo '</div>';
        include('includes/footer.inc');
        exit;
    }
    $upName = isset($_FILES['userfile']['name']) ? $_FILES['userfile']['name'] : '';
    $upExt = strtolower(pathinfo($upName, PATHINFO_EXTENSION));
    if (!in_array($upExt, array('xls', 'xlsx'))) {
        echo '<div style="border:1px solid #f5c6cb;background:#fdf0f0;border-radius:10px;padding:24px;text-align:center;margin:20px auto;max-width:640px;box-shadow:0 2px 8px rgba(220,53,69,.08)">';
        echo '<div style="font-size:17px;color:#c0392b;font-weight:bold;margin-bottom:8px">⚠️ 文件格式不支持</div>';
        echo '<div style="color:#555;margin-bottom:16px">请上传 <b>.xls / .xlsx</b> 格式的 Excel 文件（当前文件：' . htmlspecialchars($upName ?: '未知') . '）。</div>';
        echo '<a href="' . $RootPath . '/BOMCostImport.php" style="background:#1976D2;color:#fff;padding:9px 36px;border-radius:6px;text-decoration:none;font-size:14px;font-weight:600;display:inline-block">返回重新上传</a>';
        echo '</div>';
        include('includes/footer.inc');
        exit;
    }
    if (isset($_FILES['userfile']['size']) && $_FILES['userfile']['size'] > 1000000) {
        echo '<div style="border:1px solid #f5c6cb;background:#fdf0f0;border-radius:10px;padding:24px;text-align:center;margin:20px auto;max-width:640px;box-shadow:0 2px 8px rgba(220,53,69,.08)">';
        echo '<div style="font-size:17px;color:#c0392b;font-weight:bold;margin-bottom:8px">⚠️ 文件过大</div>';
        echo '<div style="color:#555;margin-bottom:16px">文件大小不能超过 <b>1MB</b>，请精简后重新上传。</div>';
        echo '<a href="' . $RootPath . '/BOMCostImport.php" style="background:#1976D2;color:#fff;padding:9px 36px;border-radius:6px;text-decoration:none;font-size:14px;font-weight:600;display:inline-block">返回重新上传</a>';
        echo '</div>';
        include('includes/footer.inc');
        exit;
    }

    try {
        $cells = readExcelToCells($_FILES['userfile']['tmp_name'], $upExt);
    } catch (Exception $e) {
        echo '<div style="border:1px solid #f5c6cb;background:#fdf0f0;border-radius:10px;padding:24px;text-align:center;margin:20px auto;max-width:640px;box-shadow:0 2px 8px rgba(220,53,69,.08)">';
        echo '<div style="font-size:17px;color:#c0392b;font-weight:bold;margin-bottom:8px">⚠️ 文件解析失败</div>';
        echo '<div style="color:#555;margin-bottom:16px">无法读取该 Excel 文件，可能已损坏或不是有效的 Excel 格式。</div>';
        echo '<a href="' . $RootPath . '/BOMCostImport.php" style="background:#1976D2;color:#fff;padding:9px 36px;border-radius:6px;text-decoration:none;font-size:14px;font-weight:600;display:inline-block">返回重新上传</a>';
        echo '</div>';
        include('includes/footer.inc');
        exit;
    }

    if (count($cells) <= 1) {
        echo '<div style="border:1px solid #f5c6cb;background:#fdf0f0;border-radius:10px;padding:24px;text-align:center;margin:20px auto;max-width:640px;box-shadow:0 2px 8px rgba(220,53,69,.08)">';
        echo '<div style="font-size:17px;color:#c0392b;font-weight:bold;margin-bottom:8px">⚠️ 没有可导入的数据</div>';
        echo '<div style="color:#555;margin-bottom:16px">该 Excel 中没有有效的数据行（第 1 行为表头，第 2 行起为数据）。</div>';
        echo '<a href="' . $RootPath . '/BOMCostImport.php" style="background:#1976D2;color:#fff;padding:9px 36px;border-radius:6px;text-decoration:none;font-size:14px;font-weight:600;display:inline-block">返回重新上传</a>';
        echo '</div>';
        include('includes/footer.inc');
        exit;
    }

    $updated = 0;
    $skipped = 0;
    $skipRows = array();
    $t = time();
    $uid = $_SESSION['UserID'];

    // 第 1 行为表头，从第 2 行起处理
    foreach ($cells as $rowNo => $row) {
        if ($rowNo <= 1) continue;
        $itemNo = isset($row['1']) ? trim($row['1']) : '';
        $ver    = isset($row['2']) ? trim($row['2']) : '';
        $cost   = isset($row['3']) ? trim($row['3']) : '';
        if ($itemNo == '') { $skipped++; $skipRows[] = '第 ' . $rowNo . ' 行：物料编码为空，已跳过'; continue; }
        if ($cost == '' || !is_numeric($cost)) { $skipped++; $skipRows[] = '第 ' . $rowNo . ' 行（' . htmlspecialchars($itemNo) . '）：成本为空或非数字，已跳过'; continue; }

        // 定位目标 BOM 头：指定版本用该版本，否则用最新版本（bom_header_id 最大）
        if ($ver != '') {
            $hr = DB_query("SELECT bom_header_id FROM bom_headers_all WHERE assembly_item_no='" . DB_escape_string($itemNo) . "' AND version='" . DB_escape_string($ver) . "'", $db);
        } else {
            $hr = DB_query("SELECT bom_header_id FROM bom_headers_all WHERE assembly_item_no='" . DB_escape_string($itemNo) . "' ORDER BY bom_header_id DESC LIMIT 1", $db);
        }
        $hrow = DB_fetch_array($hr);
        if (!$hrow) {
            $skipped++;
            $skipRows[] = '第 ' . $rowNo . ' 行（' . htmlspecialchars($itemNo) . ($ver != '' ? ' v' . htmlspecialchars($ver) : ' 最新版本') . '）：找不到对应 BOM，已跳过';
            continue;
        }
        DB_query("UPDATE bom_headers_all SET cost_price='" . DB_escape_string($cost) . "',
            last_update_date='" . $t . "',last_updated_by='" . DB_escape_string($uid) . "'
            WHERE bom_header_id='" . $hrow['bom_header_id'] . "'", $db);
        $updated++;
    }

    echo '<div style="border:1px solid #d4edda;background:#f0f9f1;border-radius:10px;padding:24px;margin:20px auto;max-width:760px;box-shadow:0 2px 8px rgba(21,87,36,.08)">';
    echo '<div style="font-size:17px;color:#155724;font-weight:bold;margin-bottom:8px">✅ 导入完成！</div>';
    echo '<div style="color:#555;margin-bottom:6px">成功更新成本 <b style="color:#155724;font-size:20px">' . $updated . '</b> 条；跳过 <b style="color:#c0392b;font-size:20px">' . $skipped . '</b> 条。</div>';
    if (count($skipRows) > 0) {
        echo '<div style="margin-top:10px;max-height:200px;overflow:auto;border:1px solid #e0e0e0;background:#fff;border-radius:6px;padding:10px;font-size:12px;color:#666">';
        foreach ($skipRows as $s) { echo '<div>· ' . $s . '</div>'; }
        echo '</div>';
    }
    echo '<div style="margin-top:16px"><a href="' . $RootPath . '/BOMCostImport.php" style="background:#1976D2;color:#fff;padding:9px 36px;border-radius:6px;text-decoration:none;font-size:14px;font-weight:600;display:inline-block">继续导入</a></div>';
    echo '</div>';

} else {
    echo '<form action="BOMCostImport.php" method="post" enctype="multipart/form-data">';
    echo '<div style="border:1px solid #d6e4f0;background:#fafcff;border-radius:10px;padding:26px;margin:20px auto;max-width:720px;box-shadow:0 2px 8px rgba(25,118,210,.08)">';
    echo '<div style="font-weight:bold;color:#0d47a1;font-size:17px;margin-bottom:10px">📥 BOM 成本批量导入</div>';
    echo '<div style="color:#5c6b7a;font-size:13px;line-height:1.9;margin-bottom:16px">'
        . 'Excel 模板格式（第 1 行为表头，第 2 行起为数据）：<br>'
        . '<b>A 列：物料编码</b>　<b>B 列：版本</b>（可留空，留空则更新该物料最新版本）　<b>C 列：成本</b>（必须为数字，空或非数字该行跳过）<br>'
        . '更新目标表：<code>bom_headers_all.cost_price</code>。导入前建议先备份数据库。'
        . '</div>';
    echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
    echo '<input type="hidden" name="MAX_FILE_SIZE" value="1000000" />';
    echo '<div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap;background:#f2f7fd;border:1px dashed #b8d4f5;border-radius:8px;padding:16px">';
    echo '<span style="color:#455a64;font-size:14px">选择需要上传的文件：</span>';
    echo '<input name="userfile" type="file" style="border:1px solid #cbd7e4;padding:6px 10px;border-radius:6px;background:#fff;font-size:13px" />';
    echo '<input type="submit" value="确认导入" style="background:#1976D2;color:#fff;border:none;padding:8px 32px;border-radius:6px;cursor:pointer;font-size:14px;font-weight:600" />';
    echo '<a href="' . $RootPath . '/BOMCostImport.php?download_template=1" style="background:#fff;color:#1976D2;border:1px solid #1976D2;padding:7px 24px;border-radius:6px;text-decoration:none;font-size:14px;font-weight:600;display:inline-block">下载模板</a>';
    echo '</div></div>';
    echo '</form>';
}
include('includes/footer.inc');
