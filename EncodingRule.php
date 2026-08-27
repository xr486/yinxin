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
include('includes/encoding_rule.inc');

/* session.inc 会对 $_POST/$_GET 执行 DB_escape_string（内含 htmlspecialchars），
 * 会把 segments JSON 的双引号转成 HTML 实体，这里按需恢复为原始 JSON。 */
function er_recover_http($v) { return stripslashes(html_entity_decode((string)$v, ENT_QUOTES, 'UTF-8')); }

if (!isset($RootPath)) {
    $RootPath = dirname(htmlspecialchars($_SERVER['PHP_SELF']));
    if ($RootPath == '/' OR $RootPath == '\\') $RootPath = '';
}

/* ============================================================
 * 1. 保存处理（POST，需带 FormID 满足 session.inc 全局校验）
 *    规则存 encoding_rule 表（segments JSON + status）
 * ============================================================ */
$saveMsg = '';
if (isset($_POST['act']) && $_POST['act'] == 'save') {
    $segmentsRaw = er_recover_http(trim(isset($_POST['segments']) ? $_POST['segments'] : ''));
    $status = (isset($_POST['status']) && $_POST['status'] == '1') ? 'Y' : 'N';
    $segments = json_decode($segmentsRaw, true);
    if (!is_array($segments)) {
        $saveMsg = '<span style="color:#c62828;">保存失败：编码段数据格式错误</span>';
    } else {
        $verr = er_validate_segments($segments);
        if ($verr != '') {
            $saveMsg = '<span style="color:#c62828;">保存失败：' . htmlspecialchars($verr) . '</span>';
        } else {
            $t = time();
            $uid = $_SESSION['UserID'];
            $segJson = addslashes(json_encode($segments, JSON_UNESCAPED_UNICODE));
            /* 一套规则语义：先清空再插入最新一条（低并发配置页可接受） */
            DB_query("DELETE FROM encoding_rule", $db);
            DB_query("INSERT INTO encoding_rule(rule_name,segments,status,creation_date,created_by,last_update_date,last_updated_by,dbid)
                      VALUES('默认规则','" . $segJson . "','" . DB_escape_string($status) . "','" . $t . "','" . DB_escape_string($uid) . "','" . $t . "','" . DB_escape_string($uid) . "',0)", $db);
            $saveMsg = '<span style="color:#2e7d32;">规则已保存，新建立的物料将按此规则自动编码</span>';
        }
    }
}

/* ============================================================
 * 1a. ajax 预览接口 GET op=preview&segments=JSON
 * ============================================================ */
if (isset($_GET['op']) && $_GET['op'] == 'preview') {
    while (ob_get_level()) ob_end_clean();
    header('Content-Type: application/json; charset=utf-8');
    $segs = json_decode(er_recover_http(isset($_GET['segments']) ? $_GET['segments'] : ''), true);
    if (!is_array($segs) || empty($segs)) { echo json_encode(array('ok' => false, 'msg' => '暂无可预览的编码段')); exit; }
    $verr = er_validate_segments($segs);
    if ($verr != '') { echo json_encode(array('ok' => false, 'msg' => $verr)); exit; }
    echo json_encode(array('ok' => true, 'code' => er_generate($db, $segs)), JSON_UNESCAPED_UNICODE);
    exit;
}

/* ============================================================
 * 1b. 一键导入旧 config 规则 GET op=import&ok=1
 * ============================================================ */
if (isset($_GET['op']) && $_GET['op'] == 'import' && isset($_GET['ok'])) {
    $curPrefix = ''; $curDigit = 6;
    $resC = DB_query("SELECT confname, confvalue FROM config WHERE confname IN ('encod_prefix','encod_digit')", $db);
    while ($c = DB_fetch_array($resC)) {
        if ($c['confname'] == 'encod_prefix') $curPrefix = $c['confvalue'];
        elseif ($c['confname'] == 'encod_digit') $curDigit = max(1, min(12, intval($c['confvalue'])));
    }
    $segs = array();
    if ($curPrefix !== '') $segs[] = array('type' => 'FIXED', 'value' => $curPrefix, 'sep' => '');
    $segs[] = array('type' => 'SERIAL', 'digits' => $curDigit, 'start' => 1, 'mode' => 'serial', 'sep' => '');
    $t = time(); $uid = $_SESSION['UserID'];
    DB_query("DELETE FROM encoding_rule", $db);
    DB_query("INSERT INTO encoding_rule(rule_name,segments,status,creation_date,created_by,last_update_date,last_updated_by,dbid)
              VALUES('默认规则','" . addslashes(json_encode($segs, JSON_UNESCAPED_UNICODE)) . "','Y','" . $t . "','" . DB_escape_string($uid) . "','" . $t . "','" . DB_escape_string($uid) . "',0)", $db);
    header('Location: EncodingRule.php');
    exit;
}

/* ============================================================
 * 2. 读取当前规则（encoding_rule 表）
 * ============================================================ */
$rule = er_load_rule($db);
$curSegments = $rule['segments'];
$curStatus   = $rule['status']; // 'Y' / 'N'

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

            <form method="POST" action="<?php echo $RootPath; ?>/EncodingRule.php" id="erForm">
                <input type="hidden" name="act" value="save">
                <input type="hidden" name="FormID" value="<?php echo $_SESSION['FormID']; ?>">
                <input type="hidden" name="segments" id="segmentsInput" value="">

                <div class="er-row">
                    <span class="er-label" style="width:70px">自动编码</span>
                    <label class="er-radio"><input type="radio" name="status" value="1" <?php echo $curStatus == 'Y' ? 'checked' : ''; ?>> 启用规则</label>
                    <label class="er-radio"><input type="radio" name="status" value="0" <?php echo $curStatus != 'Y' ? 'checked' : ''; ?>> 停用（退回 6 位纯数字）</label>
                </div>

                <div class="er-row">
                    <span class="er-label" style="width:70px">段编排</span>
                    <div id="erBar" class="er-bar"><span style="color:#999">（暂无规则，点击下方「添加段」开始配置）</span></div>
                </div>

                <div class="er-row" id="erCfgRow">
                    <span class="er-label" style="width:70px"></span>
                    <div id="erCfgWrap" style="flex:1;min-width:320px"></div>
                </div>

                <div class="er-row">
                    <span class="er-label" style="width:70px">实时预览</span>
                    <div class="er-preview" style="flex:1;min-width:300px">下一料号示例：<b id="erPreview"></b></div>
                </div>

                <div class="er-row" style="margin-top:8px">
                    <span class="er-label" style="width:70px"></span>
                    <select id="erAddType" class="er-input" style="width:150px">
                        <option value="FIXED">固定前缀</option>
                        <option value="DATE">日期段</option>
                        <option value="SERIAL">流水段</option>
                        <option value="RANDOM">随机段</option>
                    </select>
                    <button type="button" class="er-btn" onclick="erAdd()">+ 添加段</button>
                    <button type="submit" class="er-btn" style="background:#2e7d32">保存规则</button>
                    <button type="button" class="er-btn" style="background:#777" onclick="erImport()">一键导入旧规则</button>
                </div>
            </form>

            <div class="er-tip">
                <b>说明：</b><br>
                1. 自动编码：新建物料时<b>料号留空</b>，系统按本规则（分段）自动生成；手动输入不受影响。<br>
                2. 段可<b>自由添加 / 排序 / 删除</b>；日期、随机段可选。<br>
                3. 流水段支持<b>顺序递增</b>或<b>随机</b>；随机段自动查重，最多重试 5 次。<br>
                4. 修改规则只影响<b>之后新建</b>的物料，已有料号不变。停用后退回默认 <b>6 位纯数字流水</b>。<br>
                5. 至少需要一个<b>顺序流水段</b>以保证料号唯一递增。
            </div>
        </div>
    </div>
</div>

<style>
.er-bar{flex:1;min-width:320px;display:flex;flex-wrap:wrap;gap:6px;align-items:center;background:#f5f9fd;border:1px solid #d6e4f0;border-radius:4px;padding:8px 10px}
.er-chip{display:inline-flex;align-items:center;gap:6px;background:#fff;border:1px solid #bcd4ef;border-radius:3px;padding:3px 8px;font-size:13px}
.er-chip b{color:#0d47a1}
.er-chip .del{color:#c00;cursor:pointer;font-weight:bold}
.er-chip .up,.er-chip .down{color:#1976D2;cursor:pointer;font-size:12px}
.er-cfg{border:1px solid #e4e9f0;border-radius:4px;padding:10px 14px;margin-top:8px;background:#fafbfc}
</style>
<script type="text/javascript">
var SEGS = <?php echo json_encode($curSegments, JSON_UNESCAPED_UNICODE); ?>;
var SEP_OPTS = ['', '-', '_', '.', ' '];

function escH(s){ return String(s==null?'':s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
function erTypeName(t){ var m={'FIXED':'固定前缀','DATE':'日期段','SERIAL':'流水段','RANDOM':'随机段'}; return m[t]||t; }
function erSepSel(cur){
    var html='';
    for(var i=0;i<SEP_OPTS.length;i++){ var v=SEP_OPTS[i]; html+='<option value="'+escH(v)+'"'+(cur===v?' selected':'')+'>'+((v==='')?'无':escH(v))+'</option>'; }
    return html;
}
function erCfgHtml(s){
    var t=s.type, h='';
    h += '<div class="er-row" style="margin-bottom:6px"><span class="er-label" style="width:60px">类型</span><b style="color:#0d47a1">'+erTypeName(t)+'</b>'+
         '<span class="er-label" style="width:70px">后分隔符</span><select class="er-input" style="width:80px" onchange="s.sep=this.value;erRender();">'+erSepSel(s.sep||'')+'</select></div>';
    if(t==='FIXED') h += '<div class="er-row" style="margin-bottom:0"><span class="er-label" style="width:60px">固定值</span><input class="er-input" value="'+escH(s.value)+'" placeholder="如 W" oninput="s.value=this.value;erRender();"></div>';
    if(t==='DATE') h += '<div class="er-row" style="margin-bottom:0"><span class="er-label" style="width:60px">格式</span><select class="er-input" style="width:160px" onchange="s.format=this.value;erRender();">'+
        '<option value="YYYYMMDD"'+(s.format==='YYYYMMDD'?' selected':'')+'>YYYYMMDD</option>'+
        '<option value="YYYY-MM-DD"'+(s.format==='YYYY-MM-DD'?' selected':'')+'>YYYY-MM-DD</option>'+
        '<option value="YYMM"'+(s.format==='YYMM'?' selected':'')+'>YYMM</option>'+
        '<option value="YYMMDD"'+(s.format==='YYMMDD'?' selected':'')+'>YYMMDD</option></select></div>';
    if(t==='SERIAL'){
        var randSel = '<option value="serial"'+(s.mode!=='random'?' selected':'')+'>顺序递增</option><option value="random"'+(s.mode==='random'?' selected':'')+'>随机</option>';
        h += '<div class="er-row" style="margin-bottom:0"><span class="er-label" style="width:60px">模式</span><select class="er-input" onchange="s.mode=this.value;erRender();">'+randSel+'</select>';
        if(s.mode!=='random'){
            h += '<span class="er-label" style="width:45px">位数</span><input class="er-input" style="width:60px" type="number" min="1" max="12" value="'+(s.digits||6)+'" oninput="s.digits=parseInt(this.value)||1;erRender();">'+
                 '<span class="er-label" style="width:40px">起始</span><input class="er-input" style="width:60px" type="number" min="0" value="'+(s.start||1)+'" oninput="s.start=parseInt(this.value)||0;erRender();">';
        }
        h += '</div>';
    }
    if(t==='RANDOM') h += '<div class="er-row" style="margin-bottom:0"><span class="er-label" style="width:60px">位数</span><input class="er-input" style="width:60px" type="number" min="1" max="12" value="'+(s.digits||4)+'" oninput="s.digits=parseInt(this.value)||1;erRender();"></div>';
    return h;
}
function erChipText(s){
    var t=s.type;
    if(t==='FIXED') return '「'+(s.value||'')+'」';
    if(t==='SERIAL') return '('+(s.digits||6)+'位'+(s.mode==='random'?'随机':'流水')+')';
    if(t==='DATE') return '('+(s.format||'YYYYMMDD')+')';
    return ((s.digits||4)+'位随机');
}
function erRenderBar(){
    var bar=document.getElementById('erBar');
    if(!SEGS.length){ bar.innerHTML='<span style="color:#999">（暂无规则，点击下方「添加段」开始配置）</span>'; return; }
    var html='';
    for(var i=0;i<SEGS.length;i++){
        html+='<span class="er-chip"><b>'+escH(erTypeName(SEGS[i].type))+'</b><span style="color:#555">'+escH(erChipText(SEGS[i]))+'</span>'+
              (i>0?'<span class="up" onclick="erMove('+i+',-1)">↑</span>':'')+
              (i<SEGS.length-1?'<span class="down" onclick="erMove('+i+',1)">↓</span>':'')+
              '<span class="del" onclick="erDel('+i+')">×</span></span>';
    }
    bar.innerHTML=html;
}
function erRenderCfg(){
    var box=document.getElementById('erCfgWrap');
    if(!box) return;
    var html='';
    for(var i=0;i<SEGS.length;i++){ html+='<div class="er-cfg"><div style="font-weight:bold;color:#0d47a1;margin-bottom:6px">段 '+(i+1)+'：'+escH(erTypeName(SEGS[i].type))+'</div>'+erCfgHtml(SEGS[i])+'</div>'; }
    box.innerHTML=html;
}
function erSync(){ document.getElementById('segmentsInput').value = JSON.stringify(SEGS); }
function erPreview(){
    $.get('EncodingRule.php?op=preview&segments='+encodeURIComponent(JSON.stringify(SEGS))+'&_r='+Date.now(), function(d){
        var el=document.getElementById('erPreview');
        if(d && d.ok){ el.textContent=d.code; }
        else { el.textContent = (d&&d.msg)?('— '+d.msg):'—'; }
    },'json');
}
function erRender(){ erRenderBar(); erRenderCfg(); erSync(); erPreview(); }
function erAdd(){
    var t=document.getElementById('erAddType').value;
    if(t==='FIXED') SEGS.push({type:'FIXED',value:'',sep:''});
    else if(t==='DATE') SEGS.push({type:'DATE',format:'YYYYMMDD',sep:''});
    else if(t==='SERIAL') SEGS.push({type:'SERIAL',mode:'serial',digits:6,start:1,sep:''});
    else if(t==='RANDOM') SEGS.push({type:'RANDOM',digits:4,sep:''});
    erRender();
}
function erDel(i){
    var s=SEGS[i];
    if(s.type==='SERIAL' && s.mode!=='random'){ if(!confirm('删除流水段后编码可能无法保证唯一，仍要删除？')) return; }
    SEGS.splice(i,1); erRender();
}
function erMove(i,d){ var j=i+d; if(j<0||j>=SEGS.length) return; var t=SEGS[i]; SEGS[i]=SEGS[j]; SEGS[j]=t; erRender(); }
function erImport(){ if(confirm('将用旧版规则（前缀+流水）生成一条默认规则并覆盖当前配置，继续？')) window.location.href='EncodingRule.php?op=import&ok=1&_r='+Date.now(); }
if(typeof jQuery!=='undefined'){ erRender(); }
</script>

<?php include('includes/footer.inc'); ?>
