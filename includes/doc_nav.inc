<?php
/* =====================================================================
 * 文档管理体系 顶部导航（DocPLM / DocPerm / DocBorrow / DocLock 共用）
 * 样式：浅蓝 Material（#2196F3 主色），与 DocPLM 文档工作区一致
 * 用法：include('includes/doc_nav.inc');  页面自动根据自身文件名高亮当前标签
 * ===================================================================== */
if (!isset($RootPath)) { $RootPath = dirname(htmlspecialchars($_SERVER['PHP_SELF'])); }
$docNavScript = basename($_SERVER['PHP_SELF']);
$docNavMode = isset($_GET['mode']) ? trim($_GET['mode']) : '';
$docNavItems = array(
	array('DocPLM.php', '', '文档工作区'),
	array('DocPLM.php', 'template', '文档模板'),
	array('DocFolderTpl.php', '', '目录模板'),
	array('DocPerm.php', '', '权限管理'),
	array('DocBorrow.php', '', '借阅管理'),
	array('DocLock.php', '', '冻结管理'),
	array('DocPLM.php', 'abolition', '废止区'),
	array('DocPLM.php', 'recycle', '回收站'),
);
?>
<style type="text/css">
/* ====== 文档模块顶部导航（复用 .doc-tabs 规范） ====== */
.doc-nav{display:flex;border-bottom:2px solid #2196F3;margin:0 0 12px 0;align-items:center;flex-wrap:wrap;gap:0}
.doc-nav .dn-tab{padding:7px 18px;cursor:pointer;font-size:13px;color:#555;background:#f5f5f5;border:1px solid #d9d9d9;border-bottom:none;border-radius:4px 4px 0 0;margin-right:4px;position:relative;top:2px;transition:all 0.12s;text-decoration:none;font-weight:500}
.doc-nav .dn-tab:hover{background:#e3f2fd;color:#1976D2;text-decoration:none}
.doc-nav .dn-tab.active{background:#2196F3;color:#fff;border-color:#2196F3;font-weight:bold}
.doc-nav .dn-space{flex:1}
.doc-nav .dn-note{font-size:12px;color:#888;margin-left:8px}
</style>
<div class="doc-nav">
<?php foreach ($docNavItems as $item) {
	$url = $RootPath . '/' . $item[0] . ($item[1] != '' ? '?mode=' . $item[1] : '');
	$active = ($docNavScript == $item[0] && $docNavMode == $item[1]) ? ' active' : '';
	echo '<a class="dn-tab' . $active . '" href="' . htmlspecialchars($url) . '">' . htmlspecialchars($item[2]) . '</a>';
} ?>
</div>
