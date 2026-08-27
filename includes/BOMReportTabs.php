<?php
/* =====================================================================
 * includes/BOMReportTabs.php
 * BOM 模块「报表中心」顶部 Tab 导航。
 * 由 8 个 BOM 报表页（含料号查询）统一 include，实现 Tab 外壳整合。
 * 样式复刻 BOMSetup 右侧「物料属性 / BOM层级 / 图文文档」Tab 外观。
 * 仅输出 HTML，不含任何 PHP/SQL 业务逻辑；当前页按文件名自动高亮。
 * 依赖：调用前已 include('includes/session.inc')，故 $RootPath 可用。
 * ===================================================================== */
$BOMReportTabs = array(
    'Item_No.php'            => '料号查询',
    'BOMQueryReport.php'     => '当前BOM查询',
    'BOMQueryAllReport.php'  => '历史BOM查询',
    'BOMSubQueryReport.php'  => 'BOM及替代料查询',
    'BOMAllQueryReport.php'  => 'BOM多阶查询',
    'BOMWhereUsed.php'       => '料号用途查询',
    'BOMCostReport.php'      => 'BOM成本查询',
    'BOMRoutesReport.php'    => '产品工艺查询',
);
$currentReport = basename($_SERVER['PHP_SELF']);

echo '<div class="bom-tabs">';
foreach ($BOMReportTabs as $file => $label) {
    $active = ($file == $currentReport) ? ' active' : '';
    echo '<a class="bom-tab' . $active . '" href="' . $RootPath . '/' . $file . '">' . $label . '</a>';
}
echo '</div>';
