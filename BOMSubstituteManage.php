<?php
/* ============================================================
 * BOM替代件管理（BOMSubstituteManage.php）
 * 功能：按「母件 + 版本」查询某一 BOM 的子件行，对每条子件行管理其物料替代件。
 *       替代件数据存储在 bom_substitutes_all（按 component_sequence_id 归属到具体 BOM 版本的一行）。
 * 约定（与 BOM管理一致）：
 *   - 已审核(已签核) 的 BOM 版本冻结，替代件不可增删改，需通过「复制BOM」建新版本后修改。
 *   - 弹窗走 lhgdialog 的 content:"url:..."（AJAX 注入到父文档，复用父页 jQuery）。
 *   - 所有 POST 必须携带 FormID。
 * 说明：本页只操作 bom_substitutes_all，不改动 sf_item_no 与 BOM 头/行表。
 * ============================================================ */
ob_start();
include('includes/session.inc');
$Title = _('BOM替代件管理');
$ViewTopic = 'BOM替代件管理';
$BookMark = 'BOM替代件管理';

/* ---------------- 辅助函数（与 BOMSetup 同款，独立副本以便单文件维护） ---------------- */
function esc($db, $v) {
    if (is_object($db) && method_exists($db, 'real_escape_string')) {
        return mysqli_real_escape_string($db, $v);
    }
    if (function_exists('mysql_real_escape_string')) {
        return mysql_real_escape_string($v, $db);
    }
    return addslashes($v);
}
function getItemInfo($db, $item_no) {
    static $c = array();
    if (isset($c[$item_no])) return $c[$item_no];
    $r = DB_query("SELECT item_no,item_name,item_type,item_category1,units,item_desc FROM sf_item_no WHERE item_no='" . esc($db, $item_no) . "'", $db);
    $row = DB_fetch_array($r);
    $c[$item_no] = $row ? $row : false;
    return $c[$item_no];
}
function itemName($db, $item_no) {
    $i = getItemInfo($db, $item_no);
    return $i ? $i['item_name'] : '';
}
function itemTypeName($t) {
    $m = array('F' => '成品', 'B' => '半成品', 'M' => '原材料', 'P' => '包装物', '' => '其他');
    return isset($m[$t]) ? $m[$t] : '其他';
}
// 判断物料是否有 BOM 头（原材料 M 按业务约定不会有）
function hasBOM($db, $item_no) {
    static $c = array();
    if (isset($c[$item_no])) return $c[$item_no];
    $r = DB_query("SELECT bom_header_id FROM bom_headers_all WHERE assembly_item_no='" . esc($db, $item_no) . "' LIMIT 1", $db);
    $c[$item_no] = (DB_num_rows($r) > 0);
    return $c[$item_no];
}
// 取某母件指定版本的 BOM 头（version 空 = 最新创建版本）
function getHeaderByAV($db, $assembly, $version) {
    if ($version === '' || $version === null) {
        $r = DB_query("SELECT bom_header_id, version, status, assembly_item_no FROM bom_headers_all
                       WHERE assembly_item_no='" . esc($db, $assembly) . "' ORDER BY bom_header_id DESC LIMIT 1", $db);
    } else {
        $r = DB_query("SELECT bom_header_id, version, status, assembly_item_no FROM bom_headers_all
                       WHERE assembly_item_no='" . esc($db, $assembly) . "' AND version='" . esc($db, $version) . "' LIMIT 1", $db);
    }
    return DB_fetch_array($r);
}
function getVersions($db, $assembly) {
    $out = array();
    $r = DB_query("SELECT bom_header_id, version, status FROM bom_headers_all
                   WHERE assembly_item_no='" . esc($db, $assembly) . "' ORDER BY bom_header_id", $db);
    while ($row = DB_fetch_array($r)) { $out[] = $row; }
    return $out;
}
function getActiveLines($db, $bom_header_id) {
    $sql = "SELECT component_sequence_id, component_item, item_num, component_quantity, operation_seq_num, component_remarks
            FROM bom_lines_all WHERE bom_header_id='" . intval($bom_header_id) . "' AND disable_date=0 ORDER BY item_num";
    $r = DB_query($sql, $db);
    $lines = array();
    while ($row = DB_fetch_array($r)) { $lines[] = $row; }
    return $lines;
}
// 取子件行信息 + 所属 BOM 头的审核状态（用于冻结判定）
function getLineInfo($db, $line_id) {
    $r = DB_query("SELECT l.component_sequence_id, l.component_item, l.item_num, l.component_quantity, l.operation_seq_num,
                          l.component_remarks, h.bom_header_id, h.assembly_item_no, h.version, h.status AS header_status
                   FROM bom_lines_all l JOIN bom_headers_all h ON h.bom_header_id=l.bom_header_id
                   WHERE l.component_sequence_id='" . intval($line_id) . "'", $db);
    return DB_fetch_array($r);
}
function getSubstitutes($db, $line_id) {
    $out = array();
    $r = DB_query("SELECT * FROM bom_substitutes_all WHERE component_sequence_id='" . intval($line_id) . "' ORDER BY substitute_sequence_id", $db);
    while ($row = DB_fetch_array($r)) { $out[] = $row; }
    return $out;
}
// 渲染替代件表格 HTML（供初始渲染与 AJAX 刷新复用）
function renderSubstTable($db, $line_id, $frozen, $headerStatus = '') {
    $subs = getSubstitutes($db, $line_id);
    $auditLabel = ($headerStatus == '已审核')
        ? '<span style="display:inline-block;background:#ffebee;color:#c00;border:1px solid #ef9a9a;border-radius:3px;padding:1px 6px;font-size:11px;font-weight:600">已审核</span>'
        : '<span style="display:inline-block;background:#e8f5e9;color:#2e7d32;border:1px solid #a5d6a7;border-radius:3px;padding:1px 6px;font-size:11px">' . htmlspecialchars($headerStatus == '' ? '未审核' : $headerStatus) . '</span>';
    $html = '<table class="selection" style="width:100%;font-size:13px;border-collapse:collapse;table-layout:fixed">'
          . '<thead><tr style="background:#2196F3;color:#fff">'
          . '<th style="padding:7px 8px;text-align:left;width:14%">替代料编码</th><th style="padding:7px 8px;text-align:left;width:20%">替代料名称</th>'
          . '<th style="padding:7px 8px;text-align:right;width:9%">替代用量</th><th style="padding:7px 8px;text-align:left;width:8%">单位</th>'
          . '<th style="padding:7px 8px;text-align:center;width:9%">状态</th><th style="padding:7px 8px;text-align:left;width:14%">备注</th>'
          . '<th style="padding:7px 8px;text-align:center;width:9%">BOM审核状态</th><th style="padding:7px 8px;text-align:center;width:17%">操作</th></tr></thead><tbody>';
    if (empty($subs)) {
        $html .= '<tr><td colspan="8" style="padding:18px;text-align:center;color:#999">暂无替代件</td></tr>';
    } else {
        foreach ($subs as $s) {
            $sname = itemName($db, $s['substitute_item']);
            $sbom = hasBOM($db, $s['substitute_item']);
            $sbomTag = $sbom
                ? '<span style="display:inline-block;background:#e8f5e9;color:#2e7d32;border:1px solid #a5d6a7;border-radius:3px;padding:1px 6px;font-size:11px;font-weight:600;cursor:pointer;margin-left:4px" onclick="openMindmap(\'' . htmlspecialchars($s['substitute_item'], ENT_QUOTES) . '\',\'\')">有 BOM</span>'
                : '<span style="display:inline-block;background:#f5f5f5;color:#999;border:1px solid #e0e0e0;border-radius:3px;padding:1px 6px;font-size:11px;margin-left:4px">无</span>';
            $stColor = ($s['status'] == '生效') ? '#2e7d32' : '#888';
            $stBg = ($s['status'] == '生效') ? '#e8f5e9' : '#f5f5f5';
            $op = $frozen
                ? '<span style="color:#bbb">已冻结</span>'
                : '<a href="javascript:void(0)" onclick="showEditForm(' . intval($s['substitute_sequence_id']) . ')" style="color:#1976D2;text-decoration:underline">编辑</a>'
                . ' &nbsp; <a href="javascript:void(0)" onclick="postSubst(\'del\',' . intval($s['substitute_sequence_id']) . ')" style="color:#c00;text-decoration:underline">删除</a>';
            $html .= '<tr style="border-bottom:1px solid #eee">'
                  . '<td style="padding:6px 8px;font-family:Consolas,monospace;font-weight:bold">' . htmlspecialchars($s['substitute_item']) . '</td>'
                  . '<td style="padding:6px 8px">' . htmlspecialchars($sname) . $sbomTag . '</td>'
                  . '<td style="padding:6px 8px;text-align:right">' . htmlspecialchars($s['substitute_item_quantity']) . '</td>'
                  . '<td style="padding:6px 8px">' . htmlspecialchars($s['uom']) . '</td>'
                  . '<td style="padding:6px 8px;text-align:center"><span style="display:inline-block;background:' . $stBg . ';color:' . $stColor . ';border:1px solid #' . ($s['status'] == '生效' ? 'a5d6a7' : 'e0e0e0') . ';border-radius:3px;padding:1px 8px;font-size:12px">' . htmlspecialchars($s['status']) . '</span></td>'
                  . '<td style="padding:6px 8px">' . htmlspecialchars($s['substitute_remarks']) . '</td>'
                  . '<td style="padding:6px 8px;text-align:center">' . $auditLabel . '</td>'
                  . '<td style="padding:6px 8px;text-align:center;white-space:nowrap">' . $op . '</td>'
                  . '</tr>';
        }
    }
    $html .= '</tbody></table>';
    return $html;
}

$formIdField = '<input type="hidden" name="FormID" value="' . htmlspecialchars($_SESSION['FormID']) . '">';
$op = isset($_REQUEST['op']) ? $_REQUEST['op'] : '';

/* ===================== AJAX：物料单位 ===================== */
if ($op == 'item_uom') {
    while (ob_get_level()) ob_end_clean();
    header('Content-Type: application/json; charset=utf-8');
    $item = isset($_GET['item']) ? trim($_GET['item']) : '';
    $info = $item !== '' ? getItemInfo($db, $item) : false;
    echo json_encode(array('units' => $info ? $info['units'] : ''));
    exit;
}

/* ===================== AJAX：某行的替代件数 ===================== */
if ($op == 'subst_count') {
    while (ob_get_level()) ob_end_clean();
    header('Content-Type: text/html; charset=utf-8');
    $line_id = isset($_GET['line_id']) ? intval($_GET['line_id']) : 0;
    $r = DB_query("SELECT COUNT(*) AS c FROM bom_substitutes_all WHERE component_sequence_id='" . $line_id . "'", $db);
    $row = DB_fetch_array($r);
    echo intval($row['c']);
    exit;
}

/* ===================== 对话框内容：替代件管理 ===================== */
if ($op == 'subst_manage') {
    while (ob_get_level()) ob_end_clean();
    header('Content-Type: text/html; charset=utf-8');
    $line_id = isset($_GET['line_id']) ? intval($_GET['line_id']) : 0;
    $line = getLineInfo($db, $line_id);
    $RootPath = dirname(htmlspecialchars($_SERVER['PHP_SELF']));
    if ($RootPath == '/' || $RootPath == '\\') { $RootPath = ''; }
    $Theme = isset($_SESSION['Theme']) ? $_SESSION['Theme'] : 'xenos';
    if (!$line) {
        echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>替代件管理</title>';
        echo '<link href="' . $RootPath . '/css/' . $Theme . '/default.css" rel="stylesheet" type="text/css"/></head>';
        echo '<body style="padding:10px;margin:0"><div style="padding:30px;text-align:center;color:#c00">子件行不存在或已被删除。</div></body></html>';
        exit;
    }
    $frozen = ($line['header_status'] == '已审核');
    $compInfo = getItemInfo($db, $line['component_item']);
    $compName = $compInfo ? $compInfo['item_name'] : '';
    $compUnits = $compInfo ? $compInfo['units'] : '';
    $formId = htmlspecialchars($_SESSION['FormID']);
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>替代件管理</title>';
    echo '<link href="' . $RootPath . '/css/' . $Theme . '/default.css" rel="stylesheet" type="text/css"/>';
    echo '<script src="' . $RootPath . '/javascript/jquery-1.7.2.min.js"></script>';
    echo '<script src="' . $RootPath . '/JXC/javascript/lhgdialog.min.js?self=true&skin=chrome"></script>';
    echo '</head><body style="padding:10px;margin:0">';
    ?>
    <div style="font-family:Verdana,Arial,sans-serif;font-size:13px;color:#333;padding:4px 6px">
        <!-- 子件信息头 -->
        <div style="background:#f5f9ff;border:1px solid #d6e4f0;border-radius:4px;padding:10px 12px;margin-bottom:10px">
            <div style="font-weight:bold;font-size:14px;color:#0d47a1;margin-bottom:6px">子件信息</div>
            <div style="display:flex;flex-wrap:wrap;gap:14px;font-size:13px">
                <div><span style="color:#888">父件：</span><b><?php echo htmlspecialchars($line['assembly_item_no']); ?></b> <span style="color:#888">v<?php echo htmlspecialchars($line['version']); ?></span></div>
                <div><span style="color:#888">子件：</span><b style="font-family:Consolas,monospace"><?php echo htmlspecialchars($line['component_item']); ?></b> <?php echo htmlspecialchars($compName); ?></div>
                <div><span style="color:#888">用量：</span><?php echo htmlspecialchars($line['component_quantity']); ?> <?php echo htmlspecialchars($compUnits); ?></div>
                <div><span style="color:#888">BOM版本状态：</span>
                    <?php if ($frozen): ?>
                        <span style="display:inline-block;background:#ffebee;color:#c00;border:1px solid #ef9a9a;border-radius:3px;padding:1px 8px;font-weight:bold">已审核（冻结）</span>
                    <?php else: ?>
                        <span style="display:inline-block;background:#e8f5e9;color:#2e7d32;border:1px solid #a5d6a7;border-radius:3px;padding:1px 8px"><?php echo htmlspecialchars($line['header_status'] == '' ? '未审核' : $line['header_status']); ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php if ($frozen): ?>
        <div style="background:#fffbe6;border:1px solid #ffe58f;border-radius:3px;padding:8px 10px;color:#8a6d00;font-size:13px;margin-bottom:10px">
            该 BOM 版本已审核，替代件不可增删改。如需修改，请通过「复制BOM」创建新版本后再操作。
        </div>
        <?php endif; ?>

        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
            <div style="font-weight:bold;font-size:14px;color:#0d47a1">替代件列表</div>
            <button type="button" id="btnAddSubst" onclick="showAddForm()" <?php echo $frozen ? 'disabled style="background:#ccc;color:#fff;border:none;padding:5px 16px;border-radius:3px;cursor:not-allowed"' : 'style="background:#1976D2;color:#fff;border:none;padding:5px 16px;border-radius:3px;cursor:pointer;font-weight:bold"'; ?>>+ 新增替代件</button>
        </div>

        <div id="substTableWrap" style="max-height:240px;overflow:auto;border:1px solid #e0e0e0;border-radius:3px;margin-bottom:12px">
            <?php echo renderSubstTable($db, $line_id, $frozen, $line['header_status'] == '' ? '未审核' : $line['header_status']); ?>
        </div>

        <!-- 新增/编辑表单（默认隐藏） -->
        <div id="substFormPanel" style="display:none;border:1px solid #c5c5c5;border-radius:4px;padding:12px;background:#fafcff">
            <div id="substFormTitle" style="font-weight:bold;font-size:14px;color:#0d47a1;margin-bottom:8px">新增替代件</div>
            <form id="substForm" onsubmit="return doSave(this)" style="margin:0">
                <input type="hidden" name="op" value="subst_save">
                <input type="hidden" name="action" id="substAction" value="add">
                <input type="hidden" name="subst_id" id="substId" value="0">
                <input type="hidden" name="line_id" value="<?php echo intval($line_id); ?>">
                <input type="hidden" name="FormID" value="<?php echo $formId; ?>">
                <table class="selection" style="font-size:13px">
                    <tr>
                        <td style="white-space:nowrap"><span style="color:#c00">*</span> 替代物料</td>
                        <td>
                            <input type="text" id="substItem" name="substitute_item" size="22" style="padding:5px 8px;border:1px solid #ccc;border-radius:3px" placeholder="选择或输入物料代码">
                            <button type="button" class="bsm-btn" onclick="openSubstPicker()" style="padding:4px 12px;margin-left:4px">选择</button>
                            <input type="text" id="substName" readonly style="margin-left:6px;color:#666;border:none;background:transparent;width:160px" placeholder="物料名称">
                        </td>
                    </tr>
                    <tr>
                        <td style="white-space:nowrap"><span style="color:#c00">*</span> 替代用量</td>
                        <td><input type="text" id="substQty" name="substitute_item_quantity" size="14" style="padding:5px 8px;border:1px solid #ccc;border-radius:3px"> <span style="color:#888">（大于 0 的数字）</span></td>
                    </tr>
                    <tr>
                        <td style="white-space:nowrap">单位</td>
                        <td><input type="text" id="substUom" name="uom" size="14" style="padding:5px 8px;border:1px solid #ccc;border-radius:3px"></td>
                    </tr>
                    <tr>
                        <td style="white-space:nowrap">状态</td>
                        <td>
                            <select name="status" style="padding:5px 8px;border:1px solid #ccc;border-radius:3px">
                                <option value="生效" selected>生效</option>
                                <option value="失效">失效</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td style="white-space:nowrap">备注</td>
                        <td><input type="text" name="substitute_remarks" size="40" style="padding:5px 8px;border:1px solid #ccc;border-radius:3px"></td>
                    </tr>
                    <tr>
                        <td colspan="2" class="centre">
                            <input type="submit" value="保存" style="background:#1976D2;color:#fff;border:none;padding:6px 28px;border-radius:3px;cursor:pointer;font-weight:bold">
                            <button type="button" class="bsm-btn" onclick="hideSubstForm()" style="padding:6px 20px;margin-left:8px">取消</button>
                        </td>
                    </tr>
                </table>
            </form>
        </div>
    </div>

    <script>
    var curLineId = <?php echo intval($line_id); ?>;
    var curCompItem = '<?php echo htmlspecialchars($line['component_item'], ENT_QUOTES); ?>';

    function showAddForm() {
        document.getElementById('substFormTitle').innerHTML = '新增替代件';
        document.getElementById('substAction').value = 'add';
        document.getElementById('substId').value = '0';
        document.getElementById('substForm').reset();
        document.getElementById('substName').value = '';
        document.getElementById('substFormPanel').style.display = 'block';
    }
    function showEditForm(id) {
        // 通过接口取单条（稳健）：直接请求服务端回填
        $.get('BOMSubstituteManage.php?op=subst_one&subst_id=' + id + '&_r=' + Date.now(), function(d){
            if (!d || !d.substitute_item) { alert('读取失败'); return; }
            document.getElementById('substFormTitle').innerHTML = '编辑替代件';
            document.getElementById('substAction').value = 'edit';
            document.getElementById('substId').value = id;
            document.getElementById('substItem').value = d.substitute_item;
            document.getElementById('substName').value = d.substitute_name ? d.substitute_name : '';
            document.getElementById('substQty').value = d.substitute_item_quantity;
            document.getElementById('substUom').value = d.uom;
            document.getElementById('substForm').status.value = d.status;
            document.getElementById('substForm').substitute_remarks.value = d.substitute_remarks;
            document.getElementById('substFormPanel').style.display = 'block';
        }, 'json');
    }
    function hideSubstForm() {
        document.getElementById('substFormPanel').style.display = 'none';
    }
    function openSubstPicker() {
        $.dialog({
            title: '选择替代物料', width: 1280, height: 860, lock: true,
            content: 'url:BOMSetup.php?op=quick_item&target=substItem&nameTarget=substName&no_new=1&_r=' + Date.now(),
            close: function() { fillSubstUom(); }
        });
    }
    function fillSubstUom() {
        var code = document.getElementById('substItem').value.trim();
        if (!code) return;
        $.getJSON('BOMSubstituteManage.php?op=item_uom&item=' + encodeURIComponent(code) + '&_r=' + Date.now(), function(d){
            if (d && d.units) document.getElementById('substUom').value = d.units;
        });
    }
    function validateSubst(f) {
        var item = f.substitute_item.value.trim();
        var qty = f.substitute_item_quantity.value.trim();
        if (item === '') { alert('请选择/填写替代物料！'); f.substitute_item.focus(); return false; }
        if (item === curCompItem) { alert('替代物料不能与子件本身相同！'); f.substitute_item.focus(); return false; }
        if (qty === '' || isNaN(qty) || parseFloat(qty) <= 0) { alert('替代用量必须大于 0！'); f.substitute_item_quantity.focus(); return false; }
        return true;
    }
    function doSave(f) {
        if (!validateSubst(f)) return false;
        $.post('BOMSubstituteManage.php?op=subst_save&ajax=1', $(f).serialize(), function(resp){
            if (typeof resp === 'string' && resp.substr(0, 5) === 'ERR::') { alert(resp.substr(5)); return; }
            $('#substTableWrap').html(resp);
            hideSubstForm();
            if (window.updateSubstBadge) window.updateSubstBadge(curLineId);
        });
        return false;
    }
    function postSubst(action, id) {
        if (action === 'del') {
            if (!confirm('确定删除该替代件？')) return;
        }
        var fd = document.querySelector('#substForm input[name=FormID]').value;
        $.post('BOMSubstituteManage.php?op=subst_save&ajax=1',
            { FormID: fd, action: action, subst_id: id, line_id: curLineId },
            function(resp){
                if (typeof resp === 'string' && resp.substr(0, 5) === 'ERR::') { alert(resp.substr(5)); return; }
                $('#substTableWrap').html(resp);
                if (window.updateSubstBadge) window.updateSubstBadge(curLineId);
            });
    }
    // 打开 BOM 结构图弹窗（iframe 内调用）
    function openMindmap(assembly, version) {
        if (!assembly) { alert('物料代码为空'); return; }
        var url = 'BOMSetup.php?op=mindmap_view&assembly=' + encodeURIComponent(assembly);
        if (version) url += '&version=' + encodeURIComponent(version);
        url += '&_r=' + Date.now();
        var title = '结构图：' + assembly + (version ? ' (v' + version + ')' : '');
        $.dialog({title: title, width: 1180, height: 780, lock: true, content: 'url:' + url});
    }
    </script>
    <?php
    echo '</body></html>';
    exit;
}

/* ===================== AJAX：取单条替代件（编辑回填） ===================== */
if ($op == 'subst_one') {
    while (ob_get_level()) ob_end_clean();
    header('Content-Type: application/json; charset=utf-8');
    $subst_id = isset($_GET['subst_id']) ? intval($_GET['subst_id']) : 0;
    $r = DB_query("SELECT * FROM bom_substitutes_all WHERE substitute_sequence_id='" . $subst_id . "'", $db);
    $row = DB_fetch_array($r);
    if ($row) {
        echo json_encode(array(
            'substitute_item' => $row['substitute_item'],
            'substitute_name' => itemName($db, $row['substitute_item']),
            'substitute_item_quantity' => $row['substitute_item_quantity'],
            'uom' => $row['uom'],
            'status' => $row['status'],
            'substitute_remarks' => $row['substitute_remarks']
        ), JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(array());
    }
    exit;
}

/* ===================== 保存（新增/编辑/删除） ===================== */
if ($op == 'subst_save') {
    while (ob_get_level()) ob_end_clean();
    if (!isset($_POST['FormID']) || $_POST['FormID'] != $_SESSION['FormID']) {
        if (isset($_GET['ajax'])) { echo 'ERR::安全校验失败，请刷新页面重试'; }
        else { echo '<script>alert("安全校验失败，请刷新页面重试");</script>'; }
        exit;
    }
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    $line_id = isset($_POST['line_id']) ? intval($_POST['line_id']) : 0;
    $subst_id = isset($_POST['subst_id']) ? intval($_POST['subst_id']) : 0;
    $line = getLineInfo($db, $line_id);
    if (!$line) { echo (isset($_GET['ajax']) ? 'ERR::子件行不存在' : '<script>alert("子件行不存在");</script>'); exit; }
    // 冻结校验：已审核版本禁止任何写操作
    if ($line['header_status'] == '已审核') {
        echo (isset($_GET['ajax']) ? 'ERR::该 BOM 版本已审核，替代件不可修改（请复制BOM建新版本）' : '<script>alert("该 BOM 版本已审核，替代件不可修改");</script>');
        exit;
    }
    $isAjax = isset($_GET['ajax']) ? true : false;
    $err = '';

    if ($action == 'del') {
        DB_query("DELETE FROM bom_substitutes_all WHERE substitute_sequence_id='" . $subst_id . "' AND component_sequence_id='" . $line_id . "'", $db);
    } elseif ($action == 'add' || $action == 'edit') {
        $item = isset($_POST['substitute_item']) ? trim($_POST['substitute_item']) : '';
        $qty = isset($_POST['substitute_item_quantity']) ? trim($_POST['substitute_item_quantity']) : '';
        $uom = isset($_POST['uom']) ? trim($_POST['uom']) : '';
        $status = isset($_POST['status']) ? trim($_POST['status']) : '生效';
        $remarks = isset($_POST['substitute_remarks']) ? trim($_POST['substitute_remarks']) : '';
        // 校验
        if ($item === '') { $err = '请填写替代物料！'; }
        elseif ($item === $line['component_item']) { $err = '替代物料不能与子件本身相同！'; }
        elseif (!is_numeric($qty) || floatval($qty) <= 0) { $err = '替代用量必须大于 0！'; }
        else {
            $info = getItemInfo($db, $item);
            if (!$info) { $err = '替代物料「' . htmlspecialchars($item) . '」在物料主数据中不存在！'; }
            else {
                // 重复物料：同一子件行下不允许重复添加相同替代料（编辑时排除自身）
                $dupSql = "SELECT 1 FROM bom_substitutes_all WHERE component_sequence_id='" . intval($line_id) . "' AND substitute_item='" . esc($db, $item) . "'";
                if ($action == 'edit') { $dupSql .= " AND substitute_sequence_id<>'" . intval($subst_id) . "'"; }
                $dupSql .= " LIMIT 1";
                $dup = DB_query($dupSql, $db);
                if (DB_num_rows($dup) > 0) { $err = '替代物料「' . htmlspecialchars($item) . '」已存在，请勿重复添加！'; }
            }
        }
        if ($err === '') {
            $t = time();
            $uid = $_SESSION['UserID'];
            if ($action == 'add') {
                DB_query("INSERT INTO bom_substitutes_all(component_sequence_id,item_num,substitute_item,substitute_item_quantity,uom,substitute_remarks,status,creation_date,created_by,last_update_date,last_updated_by)
                          VALUES('" . $line_id . "','" . intval($line['item_num']) . "','" . esc($db, $item) . "','" . floatval($qty) . "','" . esc($db, $uom) . "','" . esc($db, $remarks) . "','" . esc($db, $status) . "','" . $t . "','" . esc($db, $uid) . "','" . $t . "','" . esc($db, $uid) . "')", $db);
            } else {
                DB_query("UPDATE bom_substitutes_all SET substitute_item='" . esc($db, $item) . "',substitute_item_quantity='" . floatval($qty) . "',uom='" . esc($db, $uom) . "',substitute_remarks='" . esc($db, $remarks) . "',status='" . esc($db, $status) . "',last_update_date='" . $t . "',last_updated_by='" . esc($db, $uid) . "' WHERE substitute_sequence_id='" . $subst_id . "' AND component_sequence_id='" . $line_id . "'", $db);
            }
        }
    } else {
        $err = '未知操作';
    }

    if ($isAjax) {
        if ($err !== '') { echo 'ERR::' . $err; exit; }
        // 重新渲染替代件表格
        echo renderSubstTable($db, $line_id, false, $line['header_status'] == '' ? '未审核' : $line['header_status']);
        exit;
    } else {
        if ($err !== '') { echo '<script>alert("' . htmlspecialchars($err, ENT_QUOTES) . '");</script>'; exit; }
        echo '<script>window.location.href="BOMSubstituteManage.php?op=subst_manage&line_id=' . $line_id . '";</script>';
        exit;
    }
}

/* ===================== 主页面 ===================== */
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

$selAssembly = isset($_GET['assembly']) ? trim($_GET['assembly']) : '';
$selVersion  = isset($_GET['version']) ? trim($_GET['version']) : '';
if ($selAssembly !== '' && !preg_match('/^[A-Za-z0-9_.\-]+$/', $selAssembly)) { $selAssembly = ''; }

$curHeader = $selAssembly !== '' ? getHeaderByAV($db, $selAssembly, $selVersion) : false;
$lines = array();
$verOptions = '';
$headerStatus = '';
if ($curHeader) {
    $headerStatus = $curHeader['status'] == '' ? '未审核' : $curHeader['status'];
    $lines = getActiveLines($db, $curHeader['bom_header_id']);
    // 版本下拉
    $vs = getVersions($db, $selAssembly);
    $verOptions = '<option value="">（最新版本）</option>';
    foreach ($vs as $v) {
        $sel = ($curHeader['version'] !== '' && $v['version'] === $curHeader['version']) ? ' selected' : '';
        $verOptions .= '<option value="' . htmlspecialchars($v['version']) . '"' . $sel . '>v' . htmlspecialchars($v['version']) . ' · ' . htmlspecialchars($v['status'] == '' ? '未审核' : $v['status']) . '</option>';
    }
}
// 预取每个子件行的替代件数
$substCountMap = array();
if (!empty($lines)) {
    $ids = array();
    foreach ($lines as $l) { $ids[] = intval($l['component_sequence_id']); }
    $r = DB_query("SELECT component_sequence_id, COUNT(*) AS c FROM bom_substitutes_all WHERE component_sequence_id IN (" . implode(',', $ids) . ") GROUP BY component_sequence_id", $db);
    while ($row = DB_fetch_array($r)) { $substCountMap[$row['component_sequence_id']] = intval($row['c']); }
}
?>
<script src="<?php echo $RootPath; ?>/javascript/jquery-1.7.2.min.js"></script>
<script src="<?php echo $RootPath; ?>/JXC/javascript/lhgdialog.min.js?self=true&skin=chrome"></script>
<style type="text/css">
.bsm-card{border:1px solid #c5c5c5;border-radius:4px;background:#fff;padding:14px;margin-bottom:12px}
.bsm-toolbar{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.bsm-toolbar input[type=text]{padding:6px 10px;border:1px solid #ccc;border-radius:3px;font-size:13px}
.bsm-toolbar select{padding:6px 8px;border:1px solid #ccc;border-radius:3px;font-size:13px}
.bsm-btn{padding:6px 18px;background:#1976D2;color:#fff;border:none;border-radius:3px;cursor:pointer;font-size:13px;font-weight:bold}
.bsm-btn:hover{background:#1565c0}
.bsm-btn-ghost{padding:6px 16px;background:#fff;border:1px solid #c5c5c5;border-radius:3px;cursor:pointer;font-size:13px}
.bsm-btn-ghost:hover{background:#f0f0f0}
.bsm-badge{display:inline-block;min-width:20px;text-align:center;background:#e3f2fd;color:#0d47a1;border:1px solid #bbdefb;border-radius:10px;padding:1px 8px;font-size:12px;font-weight:bold}
.bsm-status{display:inline-block;padding:2px 10px;border-radius:3px;font-size:12px;font-weight:bold}
#bsmTable th{background:#2196F3;color:#fff;padding:8px 10px;text-align:center;white-space:nowrap}
#bsmTable td{padding:7px 10px;border-bottom:1px solid #eee;font-size:13px;white-space:nowrap}
#bsmTable tr:hover{background:#f1f8ff}
.bsm-empty{padding:30px;text-align:center;color:#999}
.tip-box{background:#fffbe6;border:1px solid #ffe58f;border-radius:3px;padding:8px 12px;color:#8a6d00;font-size:13px;margin-bottom:12px}
</style>

<div style="font-family:Verdana,Arial,sans-serif;font-size:13px;color:#333">
    <h2 style="font-size:18px;color:#0d47a1;margin:6px 0 12px">BOM替代件管理</h2>

    <!-- 查询卡片 -->
    <div class="bsm-card">
        <div class="bsm-toolbar">
            <span style="font-weight:bold">母件：</span>
            <input type="text" id="asmInput" size="22" placeholder="输入物料代码" value="<?php echo htmlspecialchars($selAssembly); ?>">
            <button type="button" class="bsm-btn" onclick="openAsmPicker()">选择</button>
            <span id="asmName" style="color:#666"></span>
            <?php if ($curHeader): ?>
            <span style="font-weight:bold;margin-left:10px">版本：</span>
            <select id="verSelect" onchange="gotoVersion()"><?php echo $verOptions; ?></select>
            <?php endif; ?>
            <button type="button" class="bsm-btn" onclick="gotoQuery()">查询</button>
            <?php if ($selAssembly): ?><button type="button" class="bsm-btn" onclick="resetQuery()">重新选择</button><?php endif; ?>
        </div>
    </div>

    <?php if (!$selAssembly): ?>
        <div class="tip-box">请先输入或选择母件物料代码，再点击「查询」查看其 BOM 的子件与替代件。</div>
    <?php elseif (!$curHeader): ?>
        <div class="tip-box">该母件（<?php echo htmlspecialchars($selAssembly); ?>）暂无 BOM，无法管理替代件。</div>
    <?php else: ?>
        <!-- BOM 头信息 -->
        <div class="bsm-card">
            <div style="display:flex;flex-wrap:wrap;gap:18px;align-items:center">
                <div><span style="color:#888">母件：</span><b style="font-family:Consolas,monospace;font-size:14px"><?php echo htmlspecialchars($selAssembly); ?></b> <?php echo htmlspecialchars(itemName($db, $selAssembly)); ?></div>
                <div><span style="color:#888">版本：</span><b>v<?php echo htmlspecialchars($curHeader['version']); ?></b></div>
                <div><span style="color:#888">状态：</span>
                    <?php if ($headerStatus == '已审核'): ?>
                        <span class="bsm-status" style="background:#ffebee;color:#c00;border:1px solid #ef9a9a">已审核（替代件冻结）</span>
                    <?php else: ?>
                        <span class="bsm-status" style="background:#e8f5e9;color:#2e7d32;border:1px solid #a5d6a7"><?php echo htmlspecialchars($headerStatus); ?></span>
                    <?php endif; ?>
                </div>
                <div><button type="button" class="bsm-btn" style="padding:4px 14px" onclick="openMindmap('<?php echo htmlspecialchars($selAssembly, ENT_QUOTES); ?>','<?php echo htmlspecialchars($curHeader['version'], ENT_QUOTES); ?>')">查看结构图</button></div>
            </div>
            <?php if ($headerStatus == '已审核'): ?>
            <div class="tip-box" style="margin-top:10px">该 BOM 版本已审核，替代件不可增删改。如需修改，请通过「复制BOM」创建新版本。</div>
            <?php endif; ?>
        </div>

        <!-- 子件列表 -->
        <div class="bsm-card">
            <div style="font-weight:bold;font-size:14px;color:#0d47a1;margin-bottom:8px">子件列表（点击「管理替代件」维护该子件的替代料）</div>
            <?php if (empty($lines)): ?>
                <div class="bsm-empty">该 BOM 版本下暂无子件行。</div>
            <?php else: ?>
            <table class="selection" id="bsmTable" style="width:100%;border-collapse:collapse;table-layout:fixed">
                <thead><tr>
                    <th style="text-align:center;width:5%">序号</th><th style="text-align:left;width:10%">子件编码</th><th style="text-align:left;width:15%">子件名称</th><th style="text-align:left;width:6%">类型</th><th style="text-align:left;width:11%">物料类别</th><th style="text-align:right;width:6%">用量</th><th style="text-align:left;width:5%">单位</th><th style="text-align:center;width:6%">工序号</th><th style="text-align:center;width:7%">审核状态</th><th style="text-align:left;width:10%">备注</th><th style="text-align:center;width:6%">替代件数</th><th style="text-align:left;width:13%">操作</th>
                </tr></thead>
                <tbody>
                <?php
                $auditBadge = ($headerStatus == '已审核')
                    ? '<span style="display:inline-block;background:#ffebee;color:#c00;border:1px solid #ef9a9a;border-radius:3px;padding:1px 6px;font-size:11px;font-weight:600">已审核</span>'
                    : '<span style="display:inline-block;background:#e8f5e9;color:#2e7d32;border:1px solid #a5d6a7;border-radius:3px;padding:1px 6px;font-size:11px">' . htmlspecialchars($headerStatus == '' ? '未审核' : $headerStatus) . '</span>';
                foreach ($lines as $l):
                    $cnt = isset($substCountMap[$l['component_sequence_id']]) ? $substCountMap[$l['component_sequence_id']] : 0;
                    $lname = itemName($db, $l['component_item']);
                    $luom = '';
                    $ltype = '';
                    $lcat = '';
                    $li = getItemInfo($db, $l['component_item']);
                    if ($li) { $luom = $li['units']; $ltype = itemTypeName($li['item_type']); $lcat = $li['item_category1']; }
                    $lbom = hasBOM($db, $l['component_item']);
                    $lbomTag = $lbom
                        ? '<span style="display:inline-block;background:#e8f5e9;color:#2e7d32;border:1px solid #a5d6a7;border-radius:3px;padding:1px 6px;font-size:11px;font-weight:600;cursor:pointer;margin-left:4px" onclick="openMindmap(\'' . htmlspecialchars($l['component_item'], ENT_QUOTES) . '\',\'\')">有 BOM</span>'
                        : '<span style="display:inline-block;background:#f5f5f5;color:#999;border:1px solid #e0e0e0;border-radius:3px;padding:1px 6px;font-size:11px;margin-left:4px">无</span>';
                ?>
                    <tr>
                        <td style="text-align:center"><?php echo htmlspecialchars($l['item_num']); ?></td>
                        <td style="text-align:left;font-family:Consolas,monospace;font-weight:bold"><?php echo htmlspecialchars($l['component_item']); ?></td>
                        <td style="text-align:left"><?php echo htmlspecialchars($lname) . $lbomTag; ?></td>
                        <td style="text-align:left"><?php echo htmlspecialchars($ltype); ?></td>
                        <td style="text-align:left"><?php echo htmlspecialchars($lcat); ?></td>
                        <td style="text-align:right"><?php echo htmlspecialchars($l['component_quantity']); ?></td>
                        <td style="text-align:left"><?php echo htmlspecialchars($luom); ?></td>
                        <td style="text-align:center"><?php echo htmlspecialchars($l['operation_seq_num']); ?></td>
                        <td style="text-align:center"><?php echo $auditBadge; ?></td>
                        <td style="text-align:left;white-space:normal;word-break:break-all"><?php echo htmlspecialchars($l['component_remarks']); ?></td>
                        <td style="text-align:center"><span class="bsm-badge" id="badge-<?php echo intval($l['component_sequence_id']); ?>"><?php echo $cnt; ?></span></td>
                        <td style="text-align:left"><button type="button" class="bsm-btn" style="padding:4px 14px" onclick="openSubstDialog(<?php echo intval($l['component_sequence_id']); ?>,'<?php echo htmlspecialchars($l['component_item'], ENT_QUOTES); ?>')"><?php echo $headerStatus == '已审核' ? '查看替代件' : '管理替代件'; ?></button></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<script type="text/javascript">
function openAsmPicker() {
    $.dialog({
        title: '选择母件物料', width: 1280, height: 860, lock: true,
        content: 'url:BOMSetup.php?op=quick_item&target=asmInput&nameTarget=asmName&no_new=1&_r=' + Date.now(),
        close: function() {
            var v = document.getElementById('asmInput').value.trim();
            if (v !== '') { window.location.href = 'BOMSubstituteManage.php?assembly=' + encodeURIComponent(v); }
        }
    });
}
function gotoQuery() {
    var v = document.getElementById('asmInput').value.trim();
    if (v === '') { alert('请先输入或选择母件物料代码'); return; }
    window.location.href = 'BOMSubstituteManage.php?assembly=' + encodeURIComponent(v);
}
function gotoVersion() {
    var v = document.getElementById('asmInput').value.trim();
    var ver = document.getElementById('verSelect').value;
    window.location.href = 'BOMSubstituteManage.php?assembly=' + encodeURIComponent(v) + '&version=' + encodeURIComponent(ver);
}
function resetQuery() {
    window.location.href = 'BOMSubstituteManage.php';
}
function openSubstDialog(lineId, comp) {
    $.dialog({
        title: '替代件管理 - ' + comp,
        width: 1100, height: 780, lock: true,
        content: 'url:BOMSubstituteManage.php?op=subst_manage&line_id=' + lineId + '&_r=' + Date.now()
    });
}
// 对话框保存后刷新主页面上的替代件数徽标
function updateSubstBadge(lineId) {
    $.get('BOMSubstituteManage.php?op=subst_count&line_id=' + lineId + '&_r=' + Date.now(), function(c){
        var el = document.getElementById('badge-' + lineId);
        if (el) el.innerHTML = c;
    });
}
// 打开 BOM 结构图弹窗（参考 BOMSetup.php 结构图）
function openMindmap(assembly, version) {
    if (!assembly) { alert('物料代码为空'); return; }
    var url = 'BOMSetup.php?op=mindmap_view&assembly=' + encodeURIComponent(assembly);
    if (version) url += '&version=' + encodeURIComponent(version);
    url += '&_r=' + Date.now();
    var title = '结构图：' + assembly + (version ? ' (v' + version + ')' : '');
    $.dialog({title: title, width: 1180, height: 780, lock: true, content: 'url:' + url});
}
</script>

<?php
include('includes/footer.inc');
