<?php
/* ============================================================
 * BOM复制（二次优化版）：参照 BOMSetup.php 界面风格
 * 将源 BOM（模板料号+版本）的完整结构（头/行/替代件）复制到目标料号
 * ============================================================ */
include('includes/session.inc');
$Title = 'BOM复制';
$ViewTopic = 'BOM复制';
$BookMark = 'BOM复制';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

$err = '';
$srcNo = isset($_POST['src_item_no']) ? trim($_POST['src_item_no']) : '';
$srcVersion = isset($_POST['src_version']) ? trim($_POST['src_version']) : '';
$dstNo = isset($_POST['dst_item_no']) ? trim($_POST['dst_item_no']) : '';
$dstVersion = isset($_POST['dst_version']) ? trim($_POST['dst_version']) : '1';

if (isset($_POST['Save'])) {
    DB_Txn_Begin($db);
    if ($srcNo == '') { $err = '请填写/选择源 BOM 料号！'; }
    elseif ($dstNo == '') { $err = '请填写/选择目标料号！'; }
    elseif ($srcNo == $dstNo) { $err = '源料号与目标料号不能相同！'; }
    elseif ($dstVersion == '') { $err = '请填写目标版本！'; }
    if ($err == '') {
        // 源 BOM 头
        $srcHdr = latestHeader($db, $srcNo);
        if (!$srcHdr) { $err = '源料号 ' . htmlspecialchars($srcNo) . ' 没有 BOM 结构，无法复制！'; }
        else {
            $mi = DB_query("SELECT item_no FROM sf_item_no WHERE item_no='" . esc($db, $dstNo) . "'", $db);
            if (!DB_fetch_array($mi)) { $err = '目标料号 ' . htmlspecialchars($dstNo) . ' 不存在，请先创建物料主数据！'; }
            else {
                $r = DB_query("SELECT bom_header_id FROM bom_headers_all WHERE assembly_item_no='" . esc($db, $dstNo) . "' AND version='" . esc($db, $dstVersion) . "'", $db);
                if (DB_fetch_array($r)) { $err = '目标料号已存在同版本 BOM（' . htmlspecialchars($dstNo) . ' / v' . htmlspecialchars($dstVersion) . '），请更换版本号！'; }
                else {
                    // 循环引用校验：源 BOM 中不能包含目标料号
                    $r = DB_query("SELECT 1 FROM bom_lines_all WHERE assembly_item_no='" . esc($db, $srcNo) . "' AND component_item='" . esc($db, $dstNo) . "' AND disable_date=0", $db);
                    if (DB_fetch_array($r)) { $err = '目标料号已是源 BOM 的子件，复制会形成循环引用！'; }
                }
            }
        }
    }
    if ($err == '') {
        $t = time();
        $uid = $_SESSION['UserID'];
        // 1) 目标 BOM 头
        DB_query("INSERT INTO bom_headers_all(assembly_item_no,version,status,approve_by,approve_date,approve_remark,creation_date,created_by,last_update_date,last_updated_by)
            VALUES('" . esc($db, $dstNo) . "','" . esc($db, $dstVersion) . "','未审核','" . esc($db, $uid) . "','" . $t . "','BOM复制','" . $t . "','" . esc($db, $uid) . "','" . $t . "','" . esc($db, $uid) . "')", $db);
        $dstHdrId = latestHeader($db, $dstNo);
        // 2) 复制 BOM 行（源 → 目标）
        $r = DB_query("SELECT bom_header_id FROM bom_headers_all WHERE assembly_item_no='" . esc($db, $dstNo) . "' AND version='" . esc($db, $dstVersion) . "'", $db);
        $row = DB_fetch_array($r);
        $dstBomHeaderId = $row['bom_header_id'];
        DB_query("INSERT INTO bom_lines_all(assembly_item_no,bom_header_id,item_num,operation_seq_num,component_item,component_quantity,weizhi,sunhao_rate,component_remarks,effectivity_date,creation_date,created_by,last_update_date,last_updated_by)
            SELECT '" . esc($db, $dstNo) . "','" . esc($db, $dstBomHeaderId) . "',
            item_num,operation_seq_num,component_item,component_quantity,weizhi,sunhao_rate,component_remarks,
            '" . $t . "','" . $t . "','" . esc($db, $uid) . "','" . $t . "','" . esc($db, $uid) . "'
            FROM bom_lines_all WHERE bom_header_id='" . esc($db, $srcHdr['bom_header_id']) . "' AND disable_date=0", $db);
        // 3) 复制替代件（按 序号+子件 匹配新行）
        DB_query("INSERT INTO bom_substitutes_all(component_sequence_id,item_num,substitute_item,substitute_item_quantity,substitute_remarks,creation_date,created_by,last_update_date,last_updated_by)
            SELECT c.component_sequence_id, olds.item_num, olds.substitute_item, olds.substitute_item_quantity, olds.substitute_remarks,
            '" . $t . "','" . esc($db, $uid) . "','" . $t . "','" . esc($db, $uid) . "'
            FROM bom_substitutes_all olds
            JOIN bom_lines_all oldb ON olds.component_sequence_id = oldb.component_sequence_id
            JOIN bom_lines_all c ON c.assembly_item_no='" . esc($db, $dstNo) . "' AND c.bom_header_id='" . esc($db, $dstBomHeaderId) . "'
                AND oldb.item_num = c.item_num AND oldb.operation_seq_num = c.operation_seq_num
                AND oldb.component_item = c.component_item
            WHERE oldb.bom_header_id='" . esc($db, $srcHdr['bom_header_id']) . "' AND olds.status='生效'", $db);
        DB_Txn_Commit($db);
        prnMsg('BOM ' . htmlspecialchars($dstNo) . '（v' . htmlspecialchars($dstVersion) . '）复制成功！', 'success');
        $err = '';
        $srcNo = $dstNo = '';
        $srcVersion = '';
        $dstVersion = '1';
    } else {
        DB_Txn_Rollback($db);
        prnMsg($err, 'error');
    }
}

// 物料选择弹窗 URL（复用系统 BtnSearchNoBomItem 选择器）
echo '<div class="bom-layout" style="padding:16px">';
echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '">';
echo '<div class="bom-left-head" style="margin-bottom:10px"><span>BOM复制</span></div>';
echo '<table class="selection">';
echo '<tr><td style="width:120px">源 BOM 料号*</td><td>
    <input type="text" name="src_item_no" id="srcItemNo" value="' . htmlspecialchars($srcNo) . '" size="25" placeholder="输入或选择源 BOM 料号">
    <button type="button" id="btnPickSrc" style="padding:2px 10px;margin-left:6px">选择</button></td></tr>';
echo '<tr><td>源版本</td><td><input type="text" name="src_version" id="srcVersion" value="' . htmlspecialchars($srcVersion) . '" size="10" placeholder="留空=最新版本"></td></tr>';
echo '<tr><td>目标料号*</td><td>
    <input type="text" name="dst_item_no" id="dstItemNo" value="' . htmlspecialchars($dstNo) . '" size="25" placeholder="输入或选择目标料号">
    <button type="button" id="btnPickDst" style="padding:2px 10px;margin-left:6px">选择</button></td></tr>';
echo '<tr><td>目标版本*</td><td><input type="text" name="dst_version" value="' . htmlspecialchars($dstVersion) . '" size="10"></td></tr>';
echo '<tr><td colspan="2" class="centre"><input type="submit" name="Save" value="复制BOM" style="padding:6px 30px"></td></tr>';
echo '</table>';
echo '</form>';
echo '<div style="margin-top:14px;padding:10px;background:#eef4fb;border:1px solid #cfe0f3;border-radius:4px;font-size:13px;color:#444">';
echo '说明：将源 BOM 的完整结构（BOM 头、全部子件行、替代件）复制到目标料号下，生成 v' . htmlspecialchars($dstVersion) . ' 的新 BOM。';
echo '复制后目标 BOM 状态为<b>未审核</b>，可在 BOM 审核中审核。';
echo '</div>';
echo '</div>';

echo '<script>
$(function(){
    function pickItem(inputId){
        $.dialog({title:"选择物料", width:760, height:500,
            content:"url:BtnSearchNoBomItem2.php?fwValue=&cat=buliao&_r=" + Date.now(),
            init:function(){}, ok:function(){
                var ifr = this.iframe.contentDocument;
                var code = ifr ? $(ifr).find("input[name=\'item_no\']").val() || ifr.querySelector(".selected_item_no") : "";
                if (code) { $("#" + inputId).val(code); }
                return true;
            }});
    }
    $("#btnPickSrc").on("click", function(){ pickItem("srcItemNo"); });
    $("#btnPickDst").on("click", function(){ pickItem("dstItemNo"); });
});
</script>';
include('includes/footer.inc');
