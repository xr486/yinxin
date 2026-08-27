<?php
/* 编码规则配置（零部件编码生成：自动编码规则可配置）
 * 规则存 config 表（confname/confvalue 键值对，不新增表）：
 *   encod_prefix   前缀（如 W / 78，留空=无前缀）
 *   encod_digit    流水位数（1-12，默认 6）
 *   encod_separator 前缀与流水之间的分隔符（如 - _ .，留空=无）
 *   encod_auto     自动编码开关：Y=应用自定义规则；N=退回默认 6 位纯数字
 * 影响范围：AddItemNo.php item_use='Y' 的自动编码路径；item_use 非 Y 的手动编码不受影响
 */
include('includes/session.inc');
include('includes/SQL_CommonFunctions.inc');

if (!isset($RootPath)) {
    $RootPath = dirname(htmlspecialchars($_SERVER['PHP_SELF']));
    if ($RootPath == '/' OR $RootPath == '\\') $RootPath = '';
}

/* ============================================================
 * 1. 保存处理（POST，需带 FormID 满足 session.inc 全局校验）
 * ============================================================ */
$saveMsg = '';
if (isset($_POST['act']) && $_POST['act'] == 'save') {
    $prefix = trim(isset($_POST['prefix']) ? $_POST['prefix'] : '');
    $digit  = intval(isset($_POST['digit']) ? $_POST['digit'] : 6);
    $sep    = trim(isset($_POST['separator']) ? $_POST['separator'] : '');
    $auto   = (isset($_POST['auto']) && $_POST['auto'] == '1') ? 'Y' : 'N';

    $err = '';
    if (!preg_match('/^[A-Za-z0-9\-]{0,10}$/', $prefix)) $err = '前缀只能为字母/数字/短横线，最长 10 位';
    elseif ($digit < 1 || $digit > 12) $err = '流水位数需在 1-12 之间';
    elseif (!preg_match('/^[\-_\.]{0,3}$/', $sep)) $err = '分隔符只能为 - _ . ，最长 3 位';
    elseif (!preg_match('/^[A-Za-z0-9\-_\.]*$/', $prefix . $sep)) $err = '前缀+分隔符含非法字符';

    if ($err != '') {
        $saveMsg = '<span style="color:#c62828;">保存失败：' . htmlspecialchars($err) . '</span>';
    } else {
        $pairs = array(
            'encod_prefix'    => $prefix,
            'encod_digit'     => (string)$digit,
            'encod_separator' => $sep,
            'encod_auto'      => $auto,
        );
        foreach ($pairs as $k => $v) {
            DB_query("INSERT INTO config (confname, confvalue) VALUES ('" . $k . "', '" . DB_escape_string($v) . "') ON DUPLICATE KEY UPDATE confvalue='" . DB_escape_string($v) . "'", $db);
        }
        $saveMsg = '<span style="color:#2e7d32;">规则已保存，新建立的物料将按此规则自动编码</span>';
    }
}

/* ============================================================
 * 2. 读取当前规则
 * ============================================================ */
$curPrefix = ''; $curDigit = 6; $curSep = ''; $curAuto = 'Y';
$resC = DB_query("SELECT confname, confvalue FROM config WHERE confname IN ('encod_prefix','encod_digit','encod_separator','encod_auto')", $db);
while ($c = DB_fetch_array($resC)) {
    if ($c['confname'] == 'encod_prefix')       $curPrefix = $c['confvalue'];
    elseif ($c['confname'] == 'encod_digit')    $curDigit  = max(1, min(12, intval($c['confvalue'])));
    elseif ($c['confname'] == 'encod_separator')$curSep    = $c['confvalue'];
    elseif ($c['confname'] == 'encod_auto')     $curAuto   = $c['confvalue'];
}

/* ============================================================
 * 3. 渲染
 * ============================================================ */
$Title = _('编码规则配置');
$ViewTopic = '编码规则配置';
$BookMark = '编码规则配置';
include('includes/header.inc');
?>
<style type="text/css">
.er-wrap{width:100%;max-width:900px;margin:10px auto 0;box-sizing:border-box}
.er-card{background:#fff;border:1px solid #c5c5c5;border-radius:4px;overflow:hidden}
.er-head{display:flex;align-items:center;gap:10px;padding:12px 16px;background:#fafbfc;border-bottom:1px solid #e4e9f0}
.er-title{font-size:15px;font-weight:bold;color:#0d47a1}
.er-sub{font-size:12px;color:#888;flex:1}
.er-body{padding:20px 24px}
.er-row{display:flex;align-items:center;gap:10px;margin-bottom:16px;flex-wrap:wrap}
.er-label{width:110px;font-size:13px;color:#555;text-align:right;white-space:nowrap}
.er-label b{color:#c62828}
.er-input{height:30px;padding:4px 10px;border:1px solid #c5d3e0;border-radius:3px;font-size:13px;outline:none;width:200px;background:#fff}
.er-input:focus{border-color:#1976D2;box-shadow:0 0 0 2px rgba(25,118,210,0.15)}
.er-hint{font-size:11px;color:#999}
.er-radio{display:inline-flex;align-items:center;gap:4px;font-size:13px;color:#444;margin-right:16px}
.er-radio input{vertical-align:-1px}
.er-preview{background:#f5f9fd;border:1px solid #d6e4f0;border-radius:4px;padding:12px 16px;font-size:13px;color:#333;margin-top:6px}
.er-preview b{color:#1976D2;font-size:16px;letter-spacing:1px}
.er-btn{height:32px;padding:0 24px;background:#1976D2;color:#fff;border:none;border-radius:3px;cursor:pointer;font-size:14px}
.er-btn:hover{background:#1565C0}
.er-tip{background:#fff8e1;border:1px solid #ffe082;border-radius:4px;padding:10px 14px;font-size:12px;color:#8a6d1a;margin-top:16px;line-height:1.8}
.er-save-msg{padding:10px 14px;border-radius:4px;font-size:13px;background:#f5f5f5;border:1px solid #e0e0e0;margin-bottom:14px}
</style>

<div class="er-wrap">
    <div class="er-card">
        <div class="er-head">
            <span class="er-title">⚙ 编码规则配置</span>
            <span class="er-sub">零部件自动编码规则（影响 AddItemNo 中 item_use=生产 的自动编码；手动编码不受影响）</span>
        </div>
        <div class="er-body">
            <?php if ($saveMsg != '') { ?><div class="er-save-msg"><?php echo $saveMsg; ?></div><?php } ?>

            <form method="POST" action="<?php echo $RootPath; ?>/EncodingRule.php">
                <input type="hidden" name="act" value="save">
                <input type="hidden" name="FormID" value="<?php echo $_SESSION['FormID']; ?>">

                <div class="er-row">
                    <span class="er-label">前缀：</span>
                    <input type="text" name="prefix" id="erPrefix" class="er-input" value="<?php echo htmlspecialchars($curPrefix); ?>" placeholder="如：W 或 78">
                    <span class="er-hint">留空 = 无前缀；仅字母/数字/短横线</span>
                </div>
                <div class="er-row">
                    <span class="er-label">流水位数：</span>
                    <input type="number" name="digit" id="erDigit" class="er-input" value="<?php echo $curDigit; ?>" min="1" max="12" style="width:100px;">
                    <span class="er-hint">流水号补齐到该位数（1-12）</span>
                </div>
                <div class="er-row">
                    <span class="er-label">分隔符：</span>
                    <input type="text" name="separator" id="erSep" class="er-input" value="<?php echo htmlspecialchars($curSep); ?>" placeholder="如：-" style="width:80px;">
                    <span class="er-hint">前缀与流水之间，可留空</span>
                </div>
                <div class="er-row">
                    <span class="er-label">自动编码：</span>
                    <label class="er-radio"><input type="radio" name="auto" value="1" <?php echo $curAuto == 'Y' ? 'checked' : ''; ?>> 启用自定义规则</label>
                    <label class="er-radio"><input type="radio" name="auto" value="0" <?php echo $curAuto != 'Y' ? 'checked' : ''; ?>> 退回默认 6 位纯数字</label>
                </div>

                <div class="er-row">
                    <span class="er-label">预览：</span>
                    <div class="er-preview" style="flex:1;min-width:300px;">
                        下一料号示例：<b id="erPreview"></b>
                    </div>
                </div>

                <div class="er-row" style="margin-top:8px;">
                    <span class="er-label"></span>
                    <button type="submit" class="er-btn">💾 保存规则</button>
                </div>
            </form>

            <div class="er-tip">
                <b>说明：</b><br>
                1. 自动编码：新建物料时 <b>item_use=生产</b>，系统自动生成料号（按本规则：前缀+分隔符+流水号）。<br>
                2. 手动编码：item_use 选其他（如采购），料号手动输入，不受本规则影响。<br>
                3. 修改规则只影响<b>之后新建</b>的物料，已有料号不变。<br>
                4. 关闭自定义规则后，自动编码退回默认 <b>6 位纯数字流水</b>（原行为）。
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
/* 实时预览 */
function ErRender() {
    var p = document.getElementById('erPrefix').value;
    var d = parseInt(document.getElementById('erDigit').value, 10);
    var s = document.getElementById('erSep').value;
    if (isNaN(d) || d < 1) d = 6; if (d > 12) d = 12;
    var head = p + s;
    var seq = '';
    for (var i = 0; i < d; i++) seq += (i === 0) ? '1' : '0';
    document.getElementById('erPreview').textContent = head + seq;
}
document.getElementById('erPrefix').addEventListener('input', ErRender);
document.getElementById('erDigit').addEventListener('input', ErRender);
document.getElementById('erSep').addEventListener('input', ErRender);
ErRender();
</script>

<?php include('includes/footer.inc'); ?>
