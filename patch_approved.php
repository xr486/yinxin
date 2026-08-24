<?php
$file = 'BOMSetup.php';
$c = file_get_contents($file);

// 1. edit_save (line ~447): 在校验前加审核状态判断
$old1 = "        } elseif (\$postOp == 'edit_save') {
            \$assembly = \$_POST['assembly'];
            \$version  = \$_POST['version'];
            \$status   = \$_POST['status'];";
$new1 = "        } elseif (\$postOp == 'edit_save') {
            \$assembly = \$_POST['assembly'];
            \$version  = \$_POST['version'];
            // 已审核的 BOM 不允许编辑：结构变更要走"新增版本"（复制BOM填新版本号）
            \$chkHdr = = \$assembly ? latestHeader(\$db, \$assembly) : null;
            if (\$chkHdr && \$chkHdr['status'] == '已审核') {
                \$err = '该 BOM（v' . htmlspecialchars(\$chkHdr['version']) . '）已审核，不允许直接编辑！请通过"复制BOM"创建新版本后修改。';
            }
            \$status   = \$_POST['status'];";
if (strpos($c, $old1) !== false) { $c = str_replace($old1, $new1, $c); echo "edit_save OK\n"; } else { echo "edit_save NOT FOUND\n"; }

// 2. edit_line_save: 在 phdr 查询后加审核状态判断
$old2 = "            if (\$err == '') {
                \$phdr = latestHeader(\$db, \$parent);
                if (!\$phdr) { \$err = '父 BOM 不存在！'; }
            }";
$new2 = "            if (\$err == '') {
                \$phdr = latestHeader(\$db, \$parent);
                if (!\$phdr) { \$err = '父 BOM 不存在！'; }
                elseif (\$phdr['status'] == '已审核') { \$err = '该 BOM（v' . htmlspecialchars(\$phdr['version']) . '）已审核，不允许编辑子件！请通过"复制BOM"创建新版本后修改。'; }
            }";
if (strpos($c, $old2) !== false) { $c = str_replace($old2, $new2, $c); echo "edit_line_save OK\n"; } else { echo "edit_line_save NOT FOUND\n"; }

// 3. del_line: 在 phdr 查询后加审核状态判断
$old3 = "            \$phdr = latestHeader(\$db, \$parent);
            if (\$phdr) {
                DB_query(\"DELETE FROM bom_substitutes_all WHERE component_sequence_id='\" . esc(\$db, \$lineId) . \"'\", \$db);";
$new3 = "            \$phdr = latestHeader(\$db, \$parent);
            if (\$phdr && \$phdr['status'] == '已审核') {
                \$err = '该 BOM（v' . htmlspecialchars(\$phdr['version']) . '）已审核，不允许删除！请通过"复制BOM"创建新版本后修改。';
            } elseif (\$phdr) {
                DB_query(\"DELETE FROM bom_substitutes_all WHERE component_sequence_id='\" . esc(\$db, \$lineId) . \"'\", \$db);";
if (strpos($c, $old3) !== false) { $c = str_replace($old3, $new3, $c); echo "del_line OK\n"; } else { echo "del_line NOT FOUND\n"; }

file_put_contents($file, $c);
