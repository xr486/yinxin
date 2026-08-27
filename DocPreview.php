<?php
/* =====================================================================
 * 文档在线预览页（DocPLM 文档管理体系 · 二期）
 * ---------------------------------------------------------------------
 * 统一在新标签页中预览文档，按格式分流渲染：
 *   图片                 → 页面内直接显示
 *   pdf                  → 内嵌 iframe 显示
 *   txt/log/md/csv/inp/cas 等文本 → 读取并自动转码（GBK→UTF-8）避免乱码
 *   xls/xlsx             → SheetJS 前端在线渲染表格
 *   docx                 → docx-preview 前端在线渲染
 *   stp/step/igs/iges/brep → occt-import-js（WASM）解析 + Three.js 3D 渲染
 *   stl/obj              → Three.js STLLoader / OBJLoader 直接渲染
 *   仿真等专用软件格式   → 元数据卡片（建议软件提示）+ 提供下载
 * 权限：需要该文档的「读」权限（DocEffectivePerm）。
 * 参数：doc_id int 必填；ext 可选（缺省取文件实际扩展名）
 * ===================================================================== */
/* 允许页面访问：文档读权限由下方 DocEffectivePerm 单独校验（function_name='共用'）。
 * 同时设置 $DatabaseName，使未登录会话也能初始化 DB 连接（ConnectDB.inc 会据此包含
 * ConnectDB_mysqli.inc 并定义 DB_query 等函数）。
 * 预初始化 AllowedPageSecurityTokens，避免未登录时 session.inc 中的 in_array 触发 warning。 */
$AllowAnyone = true;
$DatabaseName = 'yixin';
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
if (!isset($_SESSION['AllowedPageSecurityTokens'])) { $_SESSION['AllowedPageSecurityTokens'] = array(); }
session_write_close();
include('includes/session.inc');
require_once 'includes/doc_perm.inc';
DocPermBootstrap($db);

function PlvDie($msg) {
	global $RootPath;
	while (ob_get_level()) { ob_end_clean(); }
	header('Content-Type: text/html; charset=utf-8');
	echo '<!DOCTYPE html><html lang="zh-CN"><head><meta charset="utf-8"><title>无法预览</title></head>';
	echo '<body style="font-family:Microsoft YaHei,sans-serif;background:#f7f9fc;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;">';
	echo '<div style="background:#fff;border:1px solid #d5d5d5;border-radius:8px;padding:40px 50px;text-align:center;box-shadow:0 4px 16px rgba(0,0,0,.08);">';
	echo '<div style="font-size:44px;margin-bottom:12px;">🔒</div>';
	echo '<div style="font-size:16px;color:#c62828;font-weight:bold;margin-bottom:8px;">无法预览</div>';
	echo '<div style="font-size:13px;color:#555;">' . htmlspecialchars($msg) . '</div>';
	echo '<div style="margin-top:20px;"><a href="' . $RootPath . '/DocPLM.php" style="display:inline-block;background:#2196F3;color:#fff;text-decoration:none;padding:6px 20px;border-radius:3px;font-size:13px;">返回文档工作区</a></div>';
	echo '</div></body></html>';
	exit;
}

/* 读取文本文件并转码为 UTF-8（修复 GBK 乱码） */
function PlvReadText($patch) {
	$c = @file_get_contents($patch);
	if ($c === false) { return ''; }
	$c = preg_replace('/^\xEF\xBB\xBF/', '', $c); // 去 BOM
	if (function_exists('mb_check_encoding') && !mb_check_encoding($c, 'UTF-8')) {
		$conv = @mb_convert_encoding($c, 'UTF-8', 'GB18030');
		if ($conv !== false && $conv !== '') { $c = $conv; }
	}
	return $c;
}

$docId = intval(isset($_GET['doc_id']) ? $_GET['doc_id'] : 0);
if ($docId <= 0) { PlvDie('参数错误：缺少文档 ID'); }

/* 校验文档存在 */
$r = DB_query("SELECT doc_id, doc_name, folder_id, status FROM doc_master WHERE doc_id=" . $docId, $db);
if (!($row = DB_fetch_array($r))) { PlvDie('文档不存在！'); }
if ($row['status'] == '已删除') { PlvDie('文档已被删除，无法预览！'); }

/* 定位当前版本文件 */
$rf = DB_query("SELECT file_id, file_name, file_patch, file_ext FROM doc_file WHERE doc_id=" . $docId . " AND is_current='Y' ORDER BY file_id DESC LIMIT 1", $db);
if (!($f = DB_fetch_array($rf))) { PlvDie('未找到该文档的文件记录！'); }
if ($f['file_patch'] == '' || !file_exists($f['file_patch'])) { PlvDie('文件不存在或已被删除！'); }

/* 权限校验（读） */
$deptName = DocUserDeptName($db, isset($_SESSION['UserID']) ? $_SESSION['UserID'] : '');
if (!DocEffectivePerm($db, 'doc', $docId, 'read', $deptName)) { PlvDie('您所在部门没有该文档的「读」权限！请联系管理员授权。'); }

$ext = strtolower(trim(isset($_GET['ext']) ? $_GET['ext'] : ''));
if ($ext == '') { $ext = strtolower($f['file_ext']); }

$viewUrl = $RootPath . '/DocDownload.php?doc_id=' . $docId . '&op=view';
$dlUrl   = $RootPath . '/DocDownload.php?doc_id=' . $docId . '&op=download';
$docName = htmlspecialchars($row['doc_name']);
$backUrl = $RootPath . '/DocPLM.php';

/* 不支持在线预览的格式 → 建议软件提示 */
$simHint = array(
	'mph'  => 'COMSOL Multiphysics',
	'cae'  => 'Abaqus / ANSYS Mechanical APDL',
	'slx'  => 'Siemens STAR-CCM+',
	'wbpj' => 'ANSYS Workbench',
	'db'   => 'Abaqus 结果数据库（.odb）或 ANSYS 数据库',
	'key'  => 'ANSYS APDL 输入文件',
);

$isImage  = in_array($ext, array('jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'tiff', 'svg'));
$isPdf    = ($ext == 'pdf');
$isText   = in_array($ext, array('txt', 'log', 'md', 'csv', 'ini', 'xml', 'json', 'rtf', 'inp', 'cas'));
$isExcel  = in_array($ext, array('xls', 'xlsx'));
$isDocx   = ($ext == 'docx');
$isCad    = in_array($ext, array('stp', 'step', 'igs', 'iges', 'brep', 'stl', 'obj'));
$isSim    = isset($simHint[$ext]);

/* 文本内容（若为文本类则读取转码） */
$textContent = $isText ? PlvReadText($f['file_patch']) : '';
if ($isText && $textContent === '') { $isText = false; }

$previewable = $isImage || $isPdf || $isText || $isExcel || $isDocx || $isCad;
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>文档预览 - <?php echo $docName; ?></title>
<style>
	*{box-sizing:border-box;margin:0;padding:0}
	body{font-family:"Microsoft YaHei","PingFang SC",Arial,sans-serif;background:#eef2f7;color:#333}
	.pv-top{position:sticky;top:0;z-index:10;display:flex;align-items:center;gap:10px;flex-wrap:wrap;padding:10px 16px;background:#fff;border-bottom:1px solid #e0e6ee;box-shadow:0 1px 4px rgba(0,0,0,.06)}
	.pv-name{font-size:14px;font-weight:600;color:#1c3d5a;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;flex:1;min-width:120px}
	.pv-name em{color:#8a94a6;font-weight:400;font-style:normal;font-size:12px}
	.pv-btn{display:inline-block;background:#fff;color:#1976D2;border:1px solid #b6d4f7;border-radius:4px;padding:5px 14px;font-size:12px;text-decoration:none;cursor:pointer}
	.pv-btn:hover{background:#e3f2fd}
	.pv-btn.primary{background:#1976D2;color:#fff;border-color:#1976D2}
	.pv-btn.primary:hover{background:#1565C0}
	.pv-body{padding:14px;min-height:calc(100vh - 58px)}
	.pv-center{text-align:center}
	.pv-img{max-width:100%;max-height:calc(100vh - 90px);box-shadow:0 2px 10px rgba(0,0,0,.15);border-radius:4px;background:#fff}
	.pv-frame{width:100%;height:calc(100vh - 86px);border:1px solid #dfe4ec;border-radius:4px;background:#fff}
	.pv-text{background:#fff;border:1px solid #dfe4ec;border-radius:4px;padding:14px 16px;font-family:Consolas,"Courier New",monospace;font-size:13px;line-height:1.6;white-space:pre-wrap;word-break:break-all;overflow:auto}
	.pv-note{background:#fff;border:1px solid #dfe4ec;border-radius:4px;padding:60px 20px;text-align:center;color:#666;font-size:14px}
	.pv-note .pv-dl{display:inline-block;margin-top:16px;background:#1976D2;color:#fff;padding:6px 18px;border-radius:4px;text-decoration:none;font-size:13px}
	.pv-sheet{background:#fff;border:1px solid #dfe4ec;border-radius:4px;padding:8px;overflow:auto}
	.pv-sheet table{border-collapse:collapse;font-size:12px}
	.pv-sheet td,.pv-sheet th{border:1px solid #d0d7de;padding:4px 8px;white-space:nowrap}
	.pv-sheet th{background:#f1f5f9;font-weight:600}
	.pv-loading{color:#888;text-align:center;padding:80px 0;font-size:13px}
	#cadViewer{width:100%;height:calc(100vh - 58px);background:#f5f7fb;border:1px solid #dfe4ec;border-radius:4px;overflow:hidden;position:relative}
	#cadViewer .pv-loading{height:100%;display:flex;align-items:center;justify-content:center;padding:0}
	#cadViewer canvas{display:block}
	.pv-cad-hint{position:absolute;left:10px;bottom:10px;background:rgba(255,255,255,.88);border:1px solid #dfe4ec;border-radius:4px;padding:4px 10px;font-size:12px;color:#555;z-index:5;pointer-events:none;box-shadow:0 1px 4px rgba(0,0,0,.06)}
	.pv-simcard{background:#fff;border:1px solid #dfe4ec;border-radius:4px;padding:40px 30px;text-align:center;color:#666;font-size:14px;max-width:560px;margin:0 auto}
	.pv-simcard .pv-ico{font-size:40px;margin-bottom:10px}
	.pv-simcard .pv-title{font-size:15px;font-weight:600;color:#333;margin-bottom:10px}
	.pv-simcard .pv-desc{font-size:13px;line-height:1.9;color:#555;margin-bottom:16px}
	.pv-simcard .pv-dl{display:inline-block;background:#1976D2;color:#fff;padding:6px 18px;border-radius:4px;text-decoration:none;font-size:13px}
</style>
</head>
<body>
<header class="pv-top">
	<span class="pv-name">📄 <?php echo $docName; ?> <em>.(<?php echo $ext; ?>)</em></span>
	<a class="pv-btn" href="<?php echo $dlUrl; ?>">下载原文件</a>
	<a class="pv-btn" href="<?php echo $backUrl; ?>" target="_top">返回文档工作区</a>
</header>
<div class="pv-body">
<?php if ($isImage) { ?>
	<div class="pv-center"><img class="pv-img" src="<?php echo $viewUrl; ?>" alt="<?php echo $docName; ?>" /></div>
<?php } elseif ($isPdf) { ?>
	<iframe class="pv-frame" src="<?php echo $viewUrl; ?>"></iframe>
<?php } elseif ($isText) { ?>
	<pre class="pv-text"><?php echo htmlspecialchars($textContent); ?></pre>
<?php } elseif ($isExcel || $isDocx) { ?>
	<div id="pvRender"><div class="pv-loading">正在加载预览…</div></div>
<?php } elseif ($isCad) { ?>
	<div id="cadViewer"><div class="pv-loading">正在加载 3D 模型…</div></div>
<?php } elseif ($isSim) { ?>
	<div class="pv-simcard">
		<div class="pv-ico">🧪</div>
		<div class="pv-title">仿真 / 专业软件格式（.<?php echo $ext; ?>）</div>
		<div class="pv-desc">
			该文件无法在网页中直接查看，建议使用 <b><?php echo htmlspecialchars($simHint[$ext]); ?></b> 打开。<br>
			文件名：<?php echo $docName; ?>（<?php echo number_format(@filesize($f['file_patch']) / 1024, 1); ?> KB）
		</div>
		<a class="pv-dl" href="<?php echo $dlUrl; ?>">下载原文件</a>
	</div>
<?php } else { ?>
	<div class="pv-note">
		<div>该格式（.<?php echo $ext; ?>）暂不支持在线预览，请下载后使用本地软件查看。</div>
		<a class="pv-dl" href="<?php echo $dlUrl; ?>">下载原文件</a>
	</div>
<?php } ?>
</div>
<?php if ($isExcel || $isDocx) { ?>
<script src="<?php echo $RootPath; ?>/js/xlsx.full.min.js"></script>
<script src="<?php echo $RootPath; ?>/js/jszip.min.js"></script>
<script src="<?php echo $RootPath; ?>/js/docx-preview.min.js"></script>
<script type="text/javascript">
(function () {
	var ext = '<?php echo $ext; ?>';
	var viewUrl = '<?php echo $viewUrl; ?>';
	var box = document.getElementById('pvRender');
	function fail(msg) {
		box.innerHTML = '<div class="pv-note">预览加载失败' + (msg ? '（' + msg + '）' : '') + '。</div>';
	}
	var x = new XMLHttpRequest();
	x.open('GET', viewUrl + '&_r=' + Date.now(), true);
	x.responseType = 'arraybuffer';
	/* 修复 mojibake：老版 .xls 内部 GBK 中文常被误按 UTF-8 字节→Latin1 显示成乱码，
	 * 若文本全是 Latin1 高字节且无 CJK，则按 UTF-8 重新解码。 */
	function fixMojibake(s) {
		if (!s || typeof s !== 'string' || typeof TextDecoder === 'undefined') { return s; }
		if (/[\u0080-\u00ff]/.test(s) && !/[\u4e00-\u9fff]/.test(s)) {
			try {
				var b = new Uint8Array(s.length);
				for (var i = 0; i < s.length; i++) { b[i] = s.charCodeAt(i) & 0xff; }
				var d = new TextDecoder('utf-8').decode(b);
				/* 修复成功条件：字节为合法 UTF-8（无 U+FFFD 替换符）、结果有变化且含非 ASCII。
				 * 不能只要求含中文——如折叠符号 ▾0 修复后无中文，但也必须接受。 */
				if (d.indexOf('\uFFFD') === -1 && d !== s && /[^\u0000-\u007f]/.test(d)) { return d; }
			} catch (e) {}
		}
		return s;
	}
	x.onload = function () {
		if (x.status !== 200) { fail('可能无读权限或文件已删除'); return; }
		try {
			if (ext === 'xls' || ext === 'xlsx') {
				if (typeof XLSX === 'undefined') { fail('缺少 SheetJS 组件'); return; }
				var wb = XLSX.read(new Uint8Array(x.response), { type: 'array' });
				if (!wb.SheetNames.length) { fail('未找到工作表'); return; }
				/* 逐单元格修复乱码后再渲染（sheet_to_html 显示用 w，故 v/w 都修） */
				for (var si = 0; si < wb.SheetNames.length; si++) {
					var sh = wb.Sheets[wb.SheetNames[si]];
					for (var k in sh) {
						if (k.charAt(0) === '!') { continue; }
						var cell = sh[k];
						if (!cell) { continue; }
						if (typeof cell.v === 'string') { cell.v = fixMojibake(cell.v); }
						if (typeof cell.w === 'string') { cell.w = fixMojibake(cell.w); }
					}
				}
				var html = XLSX.utils.sheet_to_html(wb.Sheets[wb.SheetNames[0]]);
				box.innerHTML = '<div class="pv-sheet">' + html + '</div>';
			} else if (ext === 'docx') {
				if (typeof docx === 'undefined' || typeof JSZip === 'undefined') { fail('缺少 docx-preview 组件'); return; }
				box.innerHTML = '<div class="pv-loading">正在渲染 Word 文档…</div>';
				var blob = new Blob([x.response], { type: 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' });
				docx.renderAsync(blob, box).then(function () {
					var ld = box.querySelector('.pv-loading');
					if (ld) { ld.remove(); }
				});
			}
		} catch (e) { fail(e && e.message ? e.message : e); }
	};
	x.onerror = function () { fail('请检查网络或权限'); };
	x.send();
})();
</script>
<?php } ?>
<?php if ($isCad) { ?>
<script src="<?php echo $RootPath; ?>/js/three.min.js"></script>
<script src="<?php echo $RootPath; ?>/js/OrbitControls.js"></script>
<script src="<?php echo $RootPath; ?>/js/STLLoader.js"></script>
<script src="<?php echo $RootPath; ?>/js/OBJLoader.js"></script>
<script src="<?php echo $RootPath; ?>/js/occt-import-js.js"></script>
<script type="text/javascript">
(function () {
	var ext = '<?php echo $ext; ?>';
	var viewUrl = '<?php echo $viewUrl; ?>';
	var dlUrl = '<?php echo $dlUrl; ?>';
	var box = document.getElementById('cadViewer');
	function fail(msg) {
		box.innerHTML = '<div class="pv-note">3D 预览加载失败' + (msg ? '（' + msg + '）' : '') + '。<br><a class="pv-dl" href="' + dlUrl + '">下载原文件</a></div>';
	}
	if (typeof THREE === 'undefined') { fail('缺少 Three.js 组件'); return; }
	/* --- 场景 / 相机 / 渲染器 --- */
	var w = box.clientWidth || (window.innerWidth - 30);
	var h = box.clientHeight || (window.innerHeight - 80);
	var scene = new THREE.Scene();
	scene.background = new THREE.Color(0xf5f7fb);
	var camera = new THREE.PerspectiveCamera(45, w / h, 0.1, 100000);
	var renderer = new THREE.WebGLRenderer({ antialias: true });
	renderer.setSize(w, h);
	renderer.setPixelRatio(window.devicePixelRatio || 1);
	box.innerHTML = '';
	box.appendChild(renderer.domElement);
	var hint = document.createElement('div');
	hint.className = 'pv-cad-hint';
	hint.textContent = '🖱 左键旋转 · 滚轮缩放 · 右键平移';
	box.appendChild(hint);
	/* 灯光 */
	scene.add(new THREE.AmbientLight(0xffffff, 0.6));
	var dl1 = new THREE.DirectionalLight(0xffffff, 0.85);
	dl1.position.set(120, 200, 160);
	scene.add(dl1);
	var dl2 = new THREE.DirectionalLight(0xffffff, 0.3);
	dl2.position.set(-120, -60, -140);
	scene.add(dl2);
	/* 控制器 */
	var controls = new THREE.OrbitControls(camera, renderer.domElement);
	controls.enableDamping = true;
	controls.dampingFactor = 0.15;
	(function animate() { requestAnimationFrame(animate); controls.update(); renderer.render(scene, camera); })();
	/* 模型自适应 */
	function fitView(group) {
		var b = new THREE.Box3().setFromObject(group);
		if (b.isEmpty()) { return; }
		var c = new THREE.Vector3(); b.getCenter(c);
		var sz = new THREE.Vector3(); b.getSize(sz);
		var maxDim = Math.max(sz.x, sz.y, sz.z) || 1;
		group.position.sub(c);
		camera.position.set(maxDim * 1.5, maxDim * 1.2, maxDim * 1.8);
		camera.near = maxDim / 1000;
		camera.far = maxDim * 100;
		camera.lookAt(0, 0, 0);
		camera.updateProjectionMatrix();
		controls.target.set(0, 0, 0);
		controls.update();
	}
	window.addEventListener('resize', function () {
		var w2 = box.clientWidth, h2 = box.clientHeight;
		if (!w2 || !h2) { return; }
		camera.aspect = w2 / h2;
		camera.updateProjectionMatrix();
		renderer.setSize(w2, h2);
	});
	function makeMesh(geom) {
		return new THREE.Mesh(geom, new THREE.MeshPhongMaterial({
			color: 0x82b1e3, specular: 0x222222, shininess: 40,
			side: THREE.DoubleSide, flatShading: (ext === 'stl')
		}));
	}
	/* --- 获取文件 --- */
	var x = new XMLHttpRequest();
	x.open('GET', viewUrl + '&_r=' + Date.now(), true);
	x.responseType = 'arraybuffer';
	x.onload = function () {
		if (x.status !== 200) { fail('可能无读权限或文件已删除'); return; }
		try {
			if (ext === 'stl') { loadStl(x.response); }
			else if (ext === 'obj') { loadObj(x.response); }
			else { loadCad(x.response); }
		} catch (e) { fail(e && e.message ? e.message : e); }
	};
	x.onerror = function () { fail('请检查网络或权限'); };
	x.send();
	/* STL：Three.js 直接解析 */
	function loadStl(data) {
		var geom = new THREE.STLLoader().parse(data);
		var g = new THREE.Group(); g.add(makeMesh(geom));
		scene.add(g); fitView(g);
	}
	/* OBJ：Three.js 直接解析 */
	function loadObj(data) {
		var text = new TextDecoder().decode(new Uint8Array(data));
		var obj = new THREE.OBJLoader().parse(text);
		var g = new THREE.Group(); g.add(obj);
		obj.traverse(function (c) {
			if (c.isMesh) { c.material = new THREE.MeshPhongMaterial({ color: 0x82b1e3, side: THREE.DoubleSide, specular: 0x222222, shininess: 40 }); }
		});
		scene.add(g); fitView(g);
	}
	/* STEP / IGES / BREP：occt-import-js 解析为三角网格 */
	function loadCad(data) {
		if (typeof occtimportjs === 'undefined') { fail('缺少 occt-import-js 组件'); return; }
		var buf = new Uint8Array(data);
		occtimportjs().then(function (occt) {
			var result;
			if (ext === 'igs' || ext === 'iges') { result = occt.ReadIgesFile(buf, null); }
			else if (ext === 'brep') { result = occt.ReadBrepFile(buf, null); }
			else { result = occt.ReadStepFile(buf, null); }
			if (!result || !result.success || !result.meshes || !result.meshes.length) { fail('模型解析失败，文件可能损坏或格式不支持'); return; }
			var g = new THREE.Group();
			for (var i = 0; i < result.meshes.length; i++) {
				var rm = result.meshes[i];
				if (!rm.attributes || !rm.attributes.position || !rm.attributes.position.array || !rm.attributes.position.array.length) { continue; }
				var geom = new THREE.BufferGeometry();
				geom.setAttribute('position', new THREE.Float32BufferAttribute(rm.attributes.position.array, 3));
				if (rm.attributes.normal && rm.attributes.normal.array && rm.attributes.normal.array.length) {
					geom.setAttribute('normal', new THREE.Float32BufferAttribute(rm.attributes.normal.array, 3));
				} else { geom.computeVertexNormals(); }
				if (rm.index && rm.index.array && rm.index.array.length) {
					geom.setIndex(new THREE.BufferAttribute(Uint32Array.from(rm.index.array), 1));
				}
				var mesh = makeMesh(geom);
				mesh.name = rm.name || ('模型' + (i + 1));
				g.add(mesh);
			}
			scene.add(g); fitView(g);
		}).catch(function (e) { fail(e && e.message ? e.message : e); });
	}
})();
</script>
<?php } ?>
</body>
</html>
