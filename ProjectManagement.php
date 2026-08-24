<?php
$PathPrefix = '';
$PageSecurity = 0;
include('includes/session.inc');

$pmSchemaCheck = mysqli_query($db, "SHOW TABLES LIKE 'pm_projects'");
if (!$pmSchemaCheck || mysqli_num_rows($pmSchemaCheck) === 0) {
    if ($_SESSION['UserID'] === 'admin') {
        header('Location: project_management/install.php');
        exit;
    }
    exit('项目管理尚未安装，请联系管理员。');
}

function pm_h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function pm_q($value) {
    global $db;
    return mysqli_real_escape_string($db, trim((string)$value));
}

function pm_post($key, $default) {
    return isset($_POST[$key]) ? $_POST[$key] : $default;
}

function pm_get($key, $default) {
    return isset($_GET[$key]) ? $_GET[$key] : $default;
}

function pm_rows($sql) {
    global $db;
    $rows = array();
    $result = mysqli_query($db, $sql);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
    }
    return $rows;
}

function pm_row($sql) {
    $rows = pm_rows($sql . ' LIMIT 1');
    return count($rows) ? $rows[0] : null;
}

function pm_db_error_message($errno, $message) {
    if (intval($errno) === 1062) {
        if (strpos($message, 'uq_pm_task_code') !== false) {
            return '保存失败：同一项目内的任务编码不能重复。请检查任务编码是否为空或已被使用。';
        }
        if (strpos($message, 'uq_pm_project_code') !== false) {
            return '保存失败：项目编码已存在，请更换项目编码后重试。';
        }
        if (strpos($message, 'uq_pm_role_name') !== false) {
            return '保存失败：角色名称已存在，请更换名称后重试。';
        }
        return '保存失败：填写的内容已存在，请检查后重试。';
    }
    if (intval($errno) === 1451) {
        return '删除失败：该数据正在被其他内容使用，请先移除关联内容。';
    }
    if (intval($errno) === 1452) {
        return '保存失败：关联的数据不存在或已被删除，请刷新页面后重试。';
    }
    if (intval($errno) === 1048) {
        return '保存失败：必填内容不能为空，请检查后重试。';
    }
    if (intval($errno) === 1406) {
        return '保存失败：填写内容过长，请缩短后重试。';
    }
    if (intval($errno) === 1366) {
        return '保存失败：填写内容格式不正确，请检查后重试。';
    }
    return '数据库操作失败，请稍后重试或联系管理员。';
}

function pm_exec($sql) {
    global $db;
    if (!mysqli_query($db, $sql)) {
        $errno = mysqli_errno($db);
        $message = mysqli_error($db);
        error_log('[ProjectManagement] MySQL ' . $errno . ': ' . $message);
        throw new Exception(pm_db_error_message($errno, $message));
    }
    return mysqli_insert_id($db);
}

function pm_log($projectId, $taskId, $action, $detail) {
    $user = pm_q($_SESSION['UserID']);
    pm_exec("INSERT INTO pm_logs(project_id,task_id,action,detail,user_id,created_at) VALUES(" .
        ($projectId ? intval($projectId) : 'NULL') . ',' . ($taskId ? intval($taskId) : 'NULL') .
        ",'" . pm_q($action) . "','" . pm_q($detail) . "','$user',NOW())");
}

function pm_redirect($view, $extra) {
    header('Location: ProjectManagement.php?view=' . rawurlencode($view) . $extra);
    exit;
}

function pm_dt($value) {
    if (!$value) return 'NULL';
    return "'" . pm_q(str_replace('T', ' ', $value)) . "'";
}

function pm_status_name($status) {
    $map = array('TO_BE_START' => '待启动', 'STARTING' => '运行中', 'PAUSED' => '已暂停', 'FINISHED' => '已完成');
    return isset($map[$status]) ? $map[$status] : $status;
}

function pm_condition_name($value) {
    $map = array('MANUAL' => '手动启动', 'ANY' => '任一前置任务完成', 'ALL' => '全部前置任务完成');
    return isset($map[$value]) ? $map[$value] : $value;
}

function pm_invalid_task_parent($taskId, $parentId, $projectId) {
    if (!$parentId) return false;
    $visited = array();
    while ($parentId) {
        if ($taskId && $parentId === $taskId) return true;
        if (isset($visited[$parentId])) return true;
        $visited[$parentId] = true;
        $parent = pm_row('SELECT parent_id,project_id FROM pm_tasks WHERE id=' . intval($parentId));
        if (!$parent || intval($parent['project_id']) !== intval($projectId)) return true;
        $parentId = intval($parent['parent_id']);
    }
    return false;
}

function pm_invalid_template_parent($nodeId, $parentId, $library) {
    if (!$parentId) return false;
    $visited = array();
    while ($parentId) {
        if ($nodeId && $parentId === $nodeId) return true;
        if (isset($visited[$parentId])) return true;
        $visited[$parentId] = true;
        $parent = pm_row('SELECT parent_id,library FROM pm_template_nodes WHERE id=' . intval($parentId));
        if (!$parent || $parent['library'] !== $library) return true;
        $parentId = intval($parent['parent_id']);
    }
    return false;
}

function pm_allowed_template_types($library, $parentId) {
    if (!$parentId) {
        return $library === 'PROJECT' ? array('FOLDER', 'PROJECT') : array('FOLDER', 'TASK');
    }
    $parent = pm_row('SELECT library,node_type FROM pm_template_nodes WHERE id=' . intval($parentId));
    if (!$parent || $parent['library'] !== $library) return array();
    if ($library === 'PROJECT') {
        if ($parent['node_type'] === 'FOLDER') return array('FOLDER', 'PROJECT');
        if ($parent['node_type'] === 'PROJECT' || $parent['node_type'] === 'TASK') return array('TASK');
        return array();
    }
    if ($parent['node_type'] === 'FOLDER') return array('FOLDER', 'TASK');
    if ($parent['node_type'] === 'TASK') return array('TASK');
    return array();
}

function pm_template_branch_has_folders($nodeId) {
    $children = pm_rows('SELECT id,node_type FROM pm_template_nodes WHERE parent_id=' . intval($nodeId));
    foreach ($children as $child) {
        if ($child['node_type'] === 'FOLDER' || pm_template_branch_has_folders(intval($child['id']))) return true;
    }
    return false;
}

function pm_dependency_reaches($taskId, $targetId, &$visited) {
    if ($taskId === $targetId) return true;
    if (isset($visited[$taskId])) return false;
    $visited[$taskId] = true;
    $dependencies = pm_rows('SELECT predecessor_id FROM pm_task_dependencies WHERE task_id=' . intval($taskId));
    foreach ($dependencies as $dependency) {
        if (pm_dependency_reaches(intval($dependency['predecessor_id']), $targetId, $visited)) return true;
    }
    return false;
}

function pm_invalid_task_dependency($taskId, $predecessorId, $projectId) {
    if (!$taskId || !$predecessorId || $taskId === $predecessorId) return true;
    $tasks = pm_rows('SELECT id,project_id FROM pm_tasks WHERE id IN(' . intval($taskId) . ',' . intval($predecessorId) . ')');
    if (count($tasks) !== 2 || intval($tasks[0]['project_id']) !== intval($projectId) || intval($tasks[1]['project_id']) !== intval($projectId)) return true;
    $visited = array();
    return pm_dependency_reaches($predecessorId, $taskId, $visited);
}

function pm_template_dependency_reaches($taskId, $targetId, &$visited) {
    if ($taskId === $targetId) return true;
    if (isset($visited[$taskId])) return false;
    $visited[$taskId] = true;
    $dependencies = pm_rows('SELECT predecessor_id FROM pm_template_dependencies WHERE task_id=' . intval($taskId));
    foreach ($dependencies as $dependency) {
        if (pm_template_dependency_reaches(intval($dependency['predecessor_id']), $targetId, $visited)) return true;
    }
    return false;
}

function pm_invalid_template_dependency($taskId, $predecessorId) {
    if (!$taskId || !$predecessorId || $taskId === $predecessorId) return true;
    $nodes = pm_rows('SELECT id,library FROM pm_template_nodes WHERE id IN(' . intval($taskId) . ',' . intval($predecessorId) . ')');
    if (count($nodes) !== 2 || $nodes[0]['library'] !== $nodes[1]['library']) return true;
    $visited = array();
    return pm_template_dependency_reaches($predecessorId, $taskId, $visited);
}

function pm_copy_template_branch($sourceId, $parentId, $suffix) {
    $node = pm_row('SELECT * FROM pm_template_nodes WHERE id=' . intval($sourceId));
    if (!$node) return 0;
    $newName = $node['name'] . $suffix;
    $newId = pm_exec("INSERT INTO pm_template_nodes(library,node_type,parent_id,code,name,wbs,task_type,start_condition,duration,description,sort_order,created_by,created_at,updated_by,updated_at)
        VALUES('" . pm_q($node['library']) . "','" . pm_q($node['node_type']) . "'," . ($parentId ? intval($parentId) : 'NULL') . ",'" . pm_q($node['code']) . "','" . pm_q($newName) . "','" . pm_q($node['wbs']) . "','" . pm_q($node['task_type']) . "','" . pm_q($node['start_condition']) . "'," . floatval($node['duration']) . ",'" . pm_q($node['description']) . "'," . intval($node['sort_order']) . ",'" . pm_q($_SESSION['UserID']) . "',NOW(),'" . pm_q($_SESSION['UserID']) . "',NOW())");
    $children = pm_rows('SELECT id FROM pm_template_nodes WHERE parent_id=' . intval($sourceId) . ' ORDER BY sort_order,id');
    foreach ($children as $child) pm_copy_template_branch($child['id'], $newId, '');
    return $newId;
}

function pm_clone_template_branch($sourceId, $parentId, $targetLibrary, &$map) {
    $node = pm_row('SELECT * FROM pm_template_nodes WHERE id=' . intval($sourceId));
    if (!$node) return 0;
    $nodeType = $node['node_type'] === 'FOLDER' ? 'FOLDER' : 'TASK';
    $newId = pm_exec("INSERT INTO pm_template_nodes(library,node_type,parent_id,code,name,wbs,task_type,start_condition,duration,description,sort_order,created_by,created_at,updated_by,updated_at)
        VALUES('" . pm_q($targetLibrary) . "','" . pm_q($nodeType) . "'," . ($parentId ? intval($parentId) : 'NULL') . ",'" . pm_q($node['code']) . "','" . pm_q($node['name']) . "','" . pm_q($node['wbs']) . "','" . pm_q($node['task_type']) . "','" . pm_q($node['start_condition']) . "'," . floatval($node['duration']) . ",'" . pm_q($node['description']) . "'," . intval($node['sort_order']) . ",'" . pm_q($_SESSION['UserID']) . "',NOW(),'" . pm_q($_SESSION['UserID']) . "',NOW())");
    $map[intval($sourceId)] = $newId;
    $children = pm_rows('SELECT id FROM pm_template_nodes WHERE parent_id=' . intval($sourceId) . ' ORDER BY sort_order,id');
    foreach ($children as $child) pm_clone_template_branch($child['id'], $newId, $targetLibrary, $map);
    return $newId;
}

function pm_import_task_template($sourceId, $parentId) {
    $map = array();
    $newId = pm_clone_template_branch($sourceId, $parentId, 'PROJECT', $map);
    foreach ($map as $oldId => $mappedId) {
        $assignments = pm_rows('SELECT role_id,user_id FROM pm_template_assignments WHERE node_id=' . intval($oldId));
        foreach ($assignments as $assignment) pm_exec('INSERT IGNORE INTO pm_template_assignments(node_id,role_id,user_id) VALUES(' . intval($mappedId) . ',' . intval($assignment['role_id']) . ",'" . pm_q($assignment['user_id']) . "')");
        $dependencies = pm_rows('SELECT predecessor_id FROM pm_template_dependencies WHERE task_id=' . intval($oldId));
        foreach ($dependencies as $dependency) {
            $oldPredecessor = intval($dependency['predecessor_id']);
            if (isset($map[$oldPredecessor])) pm_exec('INSERT IGNORE INTO pm_template_dependencies(task_id,predecessor_id) VALUES(' . intval($mappedId) . ',' . intval($map[$oldPredecessor]) . ')');
        }
    }
    return $newId;
}

function pm_instantiate_task_nodes($templateParent, $projectId, $taskParent, &$map) {
    $nodes = pm_rows("SELECT * FROM pm_template_nodes WHERE parent_id=" . intval($templateParent) . " AND node_type='TASK' ORDER BY sort_order,id");
    foreach ($nodes as $node) {
        $taskId = pm_exec("INSERT INTO pm_tasks(project_id,template_id,parent_id,code,name,wbs,task_type,start_condition,status,planned_duration,description,sort_order,created_by,created_at,updated_by,updated_at)
            VALUES(" . intval($projectId) . ',' . intval($node['id']) . ',' . ($taskParent ? intval($taskParent) : 'NULL') . ",'" . pm_q($node['code']) . "','" . pm_q($node['name']) . "','" . pm_q($node['wbs']) . "','" . pm_q($node['task_type']) . "','" . pm_q($node['start_condition']) . "','TO_BE_START'," . floatval($node['duration']) . ",'" . pm_q($node['description']) . "'," . intval($node['sort_order']) . ",'" . pm_q($_SESSION['UserID']) . "',NOW(),'" . pm_q($_SESSION['UserID']) . "',NOW())");
        $map[$node['id']] = $taskId;
        pm_instantiate_task_nodes($node['id'], $projectId, $taskId, $map);
        
    }
}

function pm_instantiate_tasks($templateParent, $projectId, $taskParent) {
    $map = array();
    pm_instantiate_task_nodes($templateParent, $projectId, $taskParent, $map);
    foreach ($map as $templateTask => $taskId) {
        $deps = pm_rows('SELECT predecessor_id FROM pm_template_dependencies WHERE task_id=' . intval($templateTask));
        foreach ($deps as $dep) if (isset($map[$dep['predecessor_id']])) {
            pm_exec('INSERT IGNORE INTO pm_task_dependencies(task_id,predecessor_id) VALUES(' . intval($taskId) . ',' . intval($map[$dep['predecessor_id']]) . ')');
        }
    }
}

$view = pm_get('view', 'dashboard');
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!isset($_POST['FormID']) || $_POST['FormID'] !== $_SESSION['FormID']) throw new Exception('页面已过期，请刷新后重试。');
        $action = pm_post('action', '');
        $user = pm_q($_SESSION['UserID']);
        if ($action === 'save_role') {
            $id = intval(pm_post('id', 0));
            $name = pm_q(pm_post('name', ''));
            if ($name === '') throw new Exception('角色名称不能为空。');
            if ($id) pm_exec("UPDATE pm_roles SET name='$name',notes='" . pm_q(pm_post('notes', '')) . "',updated_by='$user',updated_at=NOW() WHERE id=$id AND nature='CUSTOM'");
            else pm_exec("INSERT INTO pm_roles(name,nature,notes,created_by,created_at,updated_by,updated_at) VALUES('$name','CUSTOM','" . pm_q(pm_post('notes', '')) . "','$user',NOW(),'$user',NOW())");
            pm_redirect('roles', '&ok=1');
        }
        if ($action === 'delete_role') {
            $id = intval(pm_post('id', 0));
            pm_exec("DELETE FROM pm_roles WHERE id=$id AND nature='CUSTOM'");
            pm_redirect('roles', '&ok=1');
        }
        if ($action === 'save_template') {
            $id = intval(pm_post('id', 0));
            $library = pm_post('library', 'PROJECT') === 'TASK' ? 'TASK' : 'PROJECT';
            $type = pm_post('node_type', 'TASK');
            if (!in_array($type, array('FOLDER','PROJECT','TASK'))) $type = 'TASK';
            $name = pm_q(pm_post('name', ''));
            if ($name === '') throw new Exception('名称不能为空。');
            $parent = intval(pm_post('parent_id', 0));
            if (pm_invalid_template_parent($id, $parent, $library)) throw new Exception('上级节点无效，不能选择当前节点、下级节点或其他模板库的节点。');
            $existingNode = null;
            if ($id) {
                $existingNode = pm_row('SELECT library,node_type,parent_id FROM pm_template_nodes WHERE id=' . $id);
                if (!$existingNode) throw new Exception('模板节点不存在。');
                if ($existingNode['library'] !== $library) throw new Exception('节点创建后不能更换模板库。');
                if ($existingNode['node_type'] !== $type) throw new Exception('节点创建后不能修改类型。');
            }
            $relationshipChanged = !$existingNode || intval($existingNode['parent_id']) !== $parent;
            if ($relationshipChanged && !in_array($type, pm_allowed_template_types($library, $parent), true)) throw new Exception('该上级节点不允许新建此类型。文件夹下只能建立文件夹和项目模板，项目模板或任务下只能建立任务。');
            $values = "library='$library',node_type='$type',parent_id=" . ($parent ? $parent : 'NULL') . ",code='" . pm_q(pm_post('code', '')) . "',name='$name',wbs='" . pm_q(pm_post('wbs', '')) . "',task_type='" . pm_q(pm_post('task_type', 'NORMAL')) . "',start_condition='" . pm_q(pm_post('start_condition', 'MANUAL')) . "',duration=" . floatval(pm_post('duration', 0)) . ",description='" . pm_q(pm_post('description', '')) . "',sort_order=" . intval(pm_post('sort_order', 0)) . ",updated_by='$user',updated_at=NOW()";
            if ($id) pm_exec("UPDATE pm_template_nodes SET $values WHERE id=$id");
            else $id = pm_exec("INSERT INTO pm_template_nodes(created_by,created_at,updated_by,updated_at,library,node_type,name) VALUES('$user',NOW(),'$user',NOW(),'$library','$type','$name')");
            if (!$id) $id = intval(mysqli_insert_id($db));
            if (!$id) throw new Exception('保存模板失败。');
            if (strpos($values, 'library=') === 0) pm_exec("UPDATE pm_template_nodes SET $values WHERE id=$id");
            pm_redirect($library === 'TASK' ? 'task_templates' : 'project_templates', '&selected=' . $id . '&ok=1');
        }
        if ($action === 'delete_template') {
            $id = intval(pm_post('id', 0));
            $node = pm_row('SELECT library FROM pm_template_nodes WHERE id=' . $id);
            $hasChildren = pm_row('SELECT id FROM pm_template_nodes WHERE parent_id=' . $id);
            if ($hasChildren) throw new Exception('请先删除下级节点。');
            pm_exec('DELETE FROM pm_template_assignments WHERE node_id=' . $id);
            pm_exec('DELETE FROM pm_template_dependencies WHERE task_id=' . $id . ' OR predecessor_id=' . $id);
            pm_exec('DELETE FROM pm_template_nodes WHERE id=' . $id);
            pm_redirect($node && $node['library'] === 'TASK' ? 'task_templates' : 'project_templates', '&ok=1');
        }
        if ($action === 'copy_template') {
            $id = intval(pm_post('id', 0));
            $node = pm_row('SELECT * FROM pm_template_nodes WHERE id=' . $id);
            $newId = pm_copy_template_branch($id, $node ? $node['parent_id'] : 0, ' - 副本');
            pm_redirect($node && $node['library'] === 'TASK' ? 'task_templates' : 'project_templates', '&selected=' . $newId . '&ok=1');
        }
        if ($action === 'move_template') {
            $id = intval(pm_post('id', 0));
            $direction = pm_post('direction', 'UP') === 'DOWN' ? 10 : -10;
            $node = pm_row('SELECT library FROM pm_template_nodes WHERE id=' . $id);
            pm_exec('UPDATE pm_template_nodes SET sort_order=sort_order+' . $direction . ",updated_by='$user',updated_at=NOW() WHERE id=$id");
            pm_redirect($node && $node['library'] === 'TASK' ? 'task_templates' : 'project_templates', '&selected=' . $id . '&ok=1');
        }
        if ($action === 'import_task_template') {
            $parentId = intval(pm_post('parent_id', 0));
            $sourceId = intval(pm_post('source_id', 0));
            if (!$parentId || !$sourceId) throw new Exception('请选择任务模板。');
            $targetNode = pm_row("SELECT node_type FROM pm_template_nodes WHERE id=$parentId AND library='PROJECT'");
            $sourceNode = pm_row("SELECT node_type FROM pm_template_nodes WHERE id=$sourceId AND library='TASK'");
            if (!$targetNode || !in_array($targetNode['node_type'], array('PROJECT','TASK'), true)) throw new Exception('任务模板只能引用到项目模板或任务节点下。');
            if (!$sourceNode || $sourceNode['node_type'] !== 'TASK' || pm_template_branch_has_folders($sourceId)) throw new Exception('引用内容必须是仅包含任务的任务模板。');
            $newId = pm_import_task_template($sourceId, $parentId);
            pm_redirect('project_templates', '&selected=' . $newId . '&ok=1');
        }
        if ($action === 'assign_template_role') {
            $nodeId = intval(pm_post('node_id', 0));
            pm_exec('INSERT IGNORE INTO pm_template_assignments(node_id,role_id,user_id) VALUES(' . $nodeId . ',' . intval(pm_post('role_id', 0)) . ",'" . pm_q(pm_post('user_id', '')) . "')");
            $node = pm_row('SELECT library FROM pm_template_nodes WHERE id=' . $nodeId);
            pm_redirect($node && $node['library'] === 'TASK' ? 'task_templates' : 'project_templates', '&selected=' . $nodeId . '&ok=1');
        }
        if ($action === 'remove_template_role') {
            $assignmentId = intval(pm_post('id', 0));
            $assignment = pm_row('SELECT node_id FROM pm_template_assignments WHERE id=' . $assignmentId);
            pm_exec('DELETE FROM pm_template_assignments WHERE id=' . $assignmentId);
            $nodeId = $assignment ? intval($assignment['node_id']) : 0;
            $node = pm_row('SELECT library FROM pm_template_nodes WHERE id=' . $nodeId);
            pm_redirect($node && $node['library'] === 'TASK' ? 'task_templates' : 'project_templates', '&selected=' . $nodeId . '&ok=1');
        }
        if ($action === 'add_template_dependency') {
            $taskId = intval(pm_post('task_id', 0));
            $predecessorId = intval(pm_post('predecessor_id', 0));
            if (pm_invalid_template_dependency($taskId, $predecessorId)) throw new Exception('前置任务无效，不能跨模板库或形成循环依赖。');
            pm_exec("INSERT IGNORE INTO pm_template_dependencies(task_id,predecessor_id) VALUES($taskId,$predecessorId)");
            $node = pm_row('SELECT library FROM pm_template_nodes WHERE id=' . $taskId);
            pm_redirect($node && $node['library'] === 'TASK' ? 'task_templates' : 'project_templates', '&selected=' . $taskId . '&ok=1');
        }
        if ($action === 'remove_template_dependency') {
            $taskId = intval(pm_post('task_id', 0));
            $predecessorId = intval(pm_post('predecessor_id', 0));
            pm_exec("DELETE FROM pm_template_dependencies WHERE task_id=$taskId AND predecessor_id=$predecessorId");
            $node = pm_row('SELECT library FROM pm_template_nodes WHERE id=' . $taskId);
            pm_redirect($node && $node['library'] === 'TASK' ? 'task_templates' : 'project_templates', '&selected=' . $taskId . '&ok=1');
        }
        if ($action === 'instantiate') {
            $templateId = intval(pm_post('template_id', 0));
            $template = pm_row("SELECT * FROM pm_template_nodes WHERE id=$templateId AND library='PROJECT' AND node_type='PROJECT'");
            if (!$template) throw new Exception('请选择项目模板。');
            $code = pm_q(pm_post('code', ''));
            $name = pm_q(pm_post('name', ''));
            if ($code === '' || $name === '') throw new Exception('项目编码和名称不能为空。');
            $projectId = pm_exec("INSERT INTO pm_projects(template_id,code,name,owner_id,status,planned_start,planned_end,description,created_by,created_at,updated_by,updated_at) VALUES($templateId,'$code','$name','" . pm_q(pm_post('owner_id', '')) . "','TO_BE_START'," . pm_dt(pm_post('planned_start', '')) . ',' . pm_dt(pm_post('planned_end', '')) . ",'" . pm_q(pm_post('description', $template['description'])) . "','$user',NOW(),'$user',NOW())");
            pm_instantiate_tasks($templateId, $projectId, 0);
            pm_exec("INSERT IGNORE INTO pm_project_roles(project_id,role_id,user_id) VALUES($projectId,1,'" . pm_q(pm_post('owner_id', '')) . "')");
            pm_log($projectId, 0, 'CREATE_PROJECT', '从模板“' . $template['name'] . '”创建项目');
            pm_redirect('instances', '&project=' . $projectId . '&ok=1');
        }
        if ($action === 'save_project') {
            $id = intval(pm_post('id', 0));
            $sql = "UPDATE pm_projects SET code='" . pm_q(pm_post('code', '')) . "',name='" . pm_q(pm_post('name', '')) . "',owner_id='" . pm_q(pm_post('owner_id', '')) . "',planned_start=" . pm_dt(pm_post('planned_start', '')) . ",planned_end=" . pm_dt(pm_post('planned_end', '')) . ",description='" . pm_q(pm_post('description', '')) . "',updated_by='$user',updated_at=NOW() WHERE id=$id";
            pm_exec($sql); pm_log($id, 0, 'UPDATE_PROJECT', '更新项目基本信息');
            pm_redirect('instances', '&project=' . $id . '&ok=1');
        }
        if ($action === 'assign_project_role') {
            $projectId = intval(pm_post('project_id', 0));
            pm_exec('INSERT IGNORE INTO pm_project_roles(project_id,role_id,user_id) VALUES(' . $projectId . ',' . intval(pm_post('role_id', 0)) . ",'" . pm_q(pm_post('user_id', '')) . "')");
            pm_log($projectId, 0, 'ASSIGN_PROJECT_ROLE', '分配项目角色');
            pm_redirect('instances', '&project=' . $projectId . '&ok=1');
        }
        if ($action === 'remove_project_role') {
            $id = intval(pm_post('id', 0));
            $assignment = pm_row('SELECT project_id FROM pm_project_roles WHERE id=' . $id);
            pm_exec('DELETE FROM pm_project_roles WHERE id=' . $id);
            $projectId = $assignment ? intval($assignment['project_id']) : 0;
            pm_log($projectId, 0, 'REMOVE_PROJECT_ROLE', '移除项目角色');
            pm_redirect('instances', '&project=' . $projectId . '&ok=1');
        }
        if ($action === 'transition_project') {
            $id = intval(pm_post('id', 0)); $status = pm_post('status', 'TO_BE_START');
            if (!in_array($status, array('TO_BE_START','STARTING','PAUSED','FINISHED'))) throw new Exception('无效状态。');
            if ($status === 'FINISHED' && pm_row("SELECT id FROM pm_tasks WHERE project_id=$id AND status<>'FINISHED'")) throw new Exception('项目仍有未完成任务，不能完成项目。');
            $extra = $status === 'STARTING' ? ',actual_start=IFNULL(actual_start,NOW())' : ($status === 'FINISHED' ? ',actual_end=NOW()' : '');
            pm_exec("UPDATE pm_projects SET status='" . pm_q($status) . "'$extra,updated_by='$user',updated_at=NOW() WHERE id=$id");
            pm_log($id, 0, 'PROJECT_STATUS', '项目状态变更为' . pm_status_name($status));
            pm_redirect('instances', '&project=' . $id . '&ok=1');
        }
        if ($action === 'delete_project') {
            $id = intval(pm_post('id', 0));
            $tasks = pm_rows('SELECT id FROM pm_tasks WHERE project_id=' . $id);
            foreach ($tasks as $task) { pm_exec('DELETE FROM pm_task_io WHERE task_id=' . intval($task['id'])); pm_exec('DELETE FROM pm_task_assignments WHERE task_id=' . intval($task['id'])); }
            pm_exec('DELETE d FROM pm_task_dependencies d INNER JOIN pm_tasks t ON t.id=d.task_id WHERE t.project_id=' . $id);
            pm_exec('DELETE FROM pm_tasks WHERE project_id=' . $id); pm_exec('DELETE FROM pm_project_roles WHERE project_id=' . $id); pm_exec('DELETE FROM pm_logs WHERE project_id=' . $id); pm_exec('DELETE FROM pm_projects WHERE id=' . $id);
            pm_redirect('instances', '&ok=1');
        }
        if ($action === 'save_task') {
            $id = intval(pm_post('id', 0)); $projectId = intval(pm_post('project_id', 0));
            $parentId = intval(pm_post('parent_id', 0));
            if (pm_invalid_task_parent($id, $parentId, $projectId)) throw new Exception('上级任务无效，不能选择当前任务、下级任务或其他项目的任务。');
            $values = "project_id=$projectId,parent_id=" . ($parentId ?: 'NULL') . ",code='" . pm_q(pm_post('code', '')) . "',name='" . pm_q(pm_post('name', '')) . "',wbs='" . pm_q(pm_post('wbs', '')) . "',task_type='" . pm_q(pm_post('task_type', 'NORMAL')) . "',start_condition='" . pm_q(pm_post('start_condition', 'MANUAL')) . "',planned_duration=" . floatval(pm_post('duration', 0)) . ",planned_start=" . pm_dt(pm_post('planned_start', '')) . ",planned_end=" . pm_dt(pm_post('planned_end', '')) . ",description='" . pm_q(pm_post('description', '')) . "',sort_order=" . intval(pm_post('sort_order', 0)) . ",updated_by='$user',updated_at=NOW()";
            if ($id) pm_exec("UPDATE pm_tasks SET $values WHERE id=$id");
            else $id = pm_exec("INSERT INTO pm_tasks(project_id,code,name,created_by,created_at,updated_by,updated_at) VALUES($projectId,'TEMP','TEMP','$user',NOW(),'$user',NOW())");
            pm_exec("UPDATE pm_tasks SET $values WHERE id=$id"); pm_log($projectId, $id, 'SAVE_TASK', '保存任务');
            pm_redirect('instances', '&project=' . $projectId . '&task=' . $id . '&ok=1');
        }
        if ($action === 'transition_task') {
            $id = intval(pm_post('id', 0)); $projectId = intval(pm_post('project_id', 0)); $status = pm_post('status', 'TO_BE_START');
            $task = pm_row('SELECT * FROM pm_tasks WHERE id=' . $id);
            if ($status === 'STARTING' && $task && $task['start_condition'] !== 'MANUAL') {
                $deps = pm_rows('SELECT t.status FROM pm_task_dependencies d JOIN pm_tasks t ON t.id=d.predecessor_id WHERE d.task_id=' . $id);
                $finished = 0; foreach ($deps as $dep) if ($dep['status'] === 'FINISHED') $finished++;
                if (count($deps) && (($task['start_condition'] === 'ALL' && $finished < count($deps)) || ($task['start_condition'] === 'ANY' && $finished === 0))) throw new Exception('前置任务尚未满足启动条件。');
            }
            $extra = $status === 'STARTING' ? ',actual_start=IFNULL(actual_start,NOW())' : ($status === 'FINISHED' ? ',actual_end=NOW()' : '');
            pm_exec("UPDATE pm_tasks SET status='" . pm_q($status) . "'$extra,updated_by='$user',updated_at=NOW() WHERE id=$id"); pm_log($projectId, $id, 'TASK_STATUS', '任务状态变更为' . pm_status_name($status));
            pm_redirect('instances', '&project=' . $projectId . '&task=' . $id . '&ok=1');
        }
        if ($action === 'delete_task') {
            $id = intval(pm_post('id', 0)); $projectId = intval(pm_post('project_id', 0));
            if (pm_row('SELECT id FROM pm_tasks WHERE parent_id=' . $id)) throw new Exception('请先删除下级任务。');
            pm_exec('DELETE FROM pm_task_dependencies WHERE task_id=' . $id . ' OR predecessor_id=' . $id); pm_exec('DELETE FROM pm_task_assignments WHERE task_id=' . $id); pm_exec('DELETE FROM pm_task_io WHERE task_id=' . $id); pm_exec('DELETE FROM pm_tasks WHERE id=' . $id); pm_log($projectId, 0, 'DELETE_TASK', '删除任务');
            pm_redirect('instances', '&project=' . $projectId . '&ok=1');
        }
        if ($action === 'assign_task') {
            $taskId = intval(pm_post('task_id', 0)); $projectId = intval(pm_post('project_id', 0));
            pm_exec('INSERT IGNORE INTO pm_task_assignments(task_id,role_id,user_id) VALUES(' . $taskId . ',' . intval(pm_post('role_id', 0)) . ",'" . pm_q(pm_post('user_id', '')) . "')"); pm_log($projectId, $taskId, 'ASSIGN_TASK', '分配任务角色');
            pm_redirect('instances', '&project=' . $projectId . '&task=' . $taskId . '&ok=1');
        }
        if ($action === 'remove_task_assignment') {
            $id = intval(pm_post('id', 0)); $taskId = intval(pm_post('task_id', 0)); $projectId = intval(pm_post('project_id', 0));
            pm_exec('DELETE FROM pm_task_assignments WHERE id=' . $id . ' AND task_id=' . $taskId); pm_log($projectId, $taskId, 'REMOVE_TASK_ASSIGNMENT', '移除任务角色');
            pm_redirect('instances', '&project=' . $projectId . '&task=' . $taskId . '&ok=1');
        }
        if ($action === 'move_task') {
            $taskId = intval(pm_post('id', 0)); $projectId = intval(pm_post('project_id', 0));
            $direction = pm_post('direction', 'UP') === 'DOWN' ? 10 : -10;
            pm_exec('UPDATE pm_tasks SET sort_order=sort_order+' . $direction . ",updated_by='$user',updated_at=NOW() WHERE id=$taskId AND project_id=$projectId"); pm_log($projectId, $taskId, 'MOVE_TASK', $direction < 0 ? '上移任务' : '下移任务');
            pm_redirect('instances', '&project=' . $projectId . '&task=' . $taskId . '&ok=1');
        }
        if ($action === 'add_dependency') {
            $taskId = intval(pm_post('task_id', 0)); $pred = intval(pm_post('predecessor_id', 0)); $projectId = intval(pm_post('project_id', 0));
            if (pm_invalid_task_dependency($taskId, $pred, $projectId)) throw new Exception('前置任务无效，不能跨项目或形成循环依赖。');
            pm_exec("INSERT IGNORE INTO pm_task_dependencies(task_id,predecessor_id) VALUES($taskId,$pred)"); pm_log($projectId, $taskId, 'ADD_DEPENDENCY', '添加前置任务');
            pm_redirect('instances', '&project=' . $projectId . '&task=' . $taskId . '&ok=1');
        }
        if ($action === 'remove_dependency') {
            $taskId = intval(pm_post('task_id', 0)); $pred = intval(pm_post('predecessor_id', 0)); $projectId = intval(pm_post('project_id', 0));
            pm_exec("DELETE FROM pm_task_dependencies WHERE task_id=$taskId AND predecessor_id=$pred"); pm_log($projectId, $taskId, 'REMOVE_DEPENDENCY', '移除前置任务');
            pm_redirect('instances', '&project=' . $projectId . '&task=' . $taskId . '&ok=1');
        }
        if ($action === 'add_io') {
            $taskId = intval(pm_post('task_id', 0)); $projectId = intval(pm_post('project_id', 0)); $direction = pm_post('direction', 'INPUT') === 'OUTPUT' ? 'OUTPUT' : 'INPUT';
            pm_exec("INSERT INTO pm_task_io(task_id,direction,name,reference,notes,created_by,created_at) VALUES($taskId,'$direction','" . pm_q(pm_post('name', '')) . "','" . pm_q(pm_post('reference', '')) . "','" . pm_q(pm_post('notes', '')) . "','$user',NOW())"); pm_log($projectId, $taskId, 'ADD_IO', '添加任务' . ($direction === 'INPUT' ? '输入' : '输出'));
            pm_redirect('instances', '&project=' . $projectId . '&task=' . $taskId . '&ok=1');
        }
        if ($action === 'remove_io') {
            $id = intval(pm_post('id', 0)); $taskId = intval(pm_post('task_id', 0)); $projectId = intval(pm_post('project_id', 0));
            pm_exec('DELETE FROM pm_task_io WHERE id=' . $id . ' AND task_id=' . $taskId); pm_log($projectId, $taskId, 'REMOVE_IO', '移除任务输入输出');
            pm_redirect('instances', '&project=' . $projectId . '&task=' . $taskId . '&ok=1');
        }
        if ($action === 'save_settings') {
            $calendar = pm_post('work_calendar', 'SINGLE_REST');
            if (!in_array($calendar, array('DOUBLE_REST','SINGLE_REST','NO_REST'))) $calendar = 'SINGLE_REST';
            pm_exec("INSERT INTO pm_settings(setting_key,setting_value,updated_by,updated_at) VALUES('work_calendar','" . pm_q($calendar) . "','$user',NOW()) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value),updated_by=VALUES(updated_by),updated_at=NOW()");
            pm_redirect('settings', '&ok=1');
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$users = pm_rows("SELECT userid,realname,depart_code FROM www_users WHERE blocked=0 ORDER BY realname");
$roles = pm_rows('SELECT * FROM pm_roles ORDER BY nature,name');
$projects = pm_rows("SELECT p.*,u.realname owner_name,(SELECT COUNT(*) FROM pm_tasks t WHERE t.project_id=p.id) task_count,(SELECT COUNT(*) FROM pm_tasks t WHERE t.project_id=p.id AND t.status='FINISHED') finished_count FROM pm_projects p LEFT JOIN www_users u ON u.userid=p.owner_id ORDER BY p.updated_at DESC");
$selectedProjectId = intval(pm_get('project', 0));
$selectedTaskId = intval(pm_get('task', 0));
$selectedProject = $selectedProjectId ? pm_row('SELECT * FROM pm_projects WHERE id=' . $selectedProjectId) : null;
$selectedTask = ($selectedTaskId && $selectedProjectId) ? pm_row('SELECT * FROM pm_tasks WHERE id=' . $selectedTaskId . ' AND project_id=' . $selectedProjectId) : null;
$statusFilter = pm_get('status', 'STARTING');
$viewTitles = array('dashboard'=>'项目总览','project_templates'=>'项目模板','task_templates'=>'任务模板','instances'=>'项目实例','track'=>'项目跟踪','roles'=>'项目角色','workflow'=>'项目流程','settings'=>'设置','gantt'=>'甘特图');
?>
<!doctype html>
<html lang="zh-CN">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>项目管理</title>
<script>if(window.self!==window.top){document.documentElement.className+=' pm-embedded';}</script>
<link rel="stylesheet" href="project_management/style.css?v=<?php echo filemtime(__DIR__ . '/project_management/style.css'); ?>">
</head>
<body>
<div class="pm-shell">
  <aside class="pm-sidebar">
    <div class="pm-brand"><span>PM</span><strong>项目管理</strong></div>
    <nav>
      <a class="<?php echo $view==='dashboard'?'active':''; ?>" href="?view=dashboard">总览</a>
      <div class="nav-label">模板</div>
      <a class="<?php echo $view==='project_templates'?'active':''; ?>" href="?view=project_templates">项目模板</a>
      <a class="<?php echo $view==='task_templates'?'active':''; ?>" href="?view=task_templates">任务模板</a>
      <div class="nav-label">执行</div>
      <a class="<?php echo $view==='instances'?'active':''; ?>" href="?view=instances">项目实例</a>
      <a class="<?php echo $view==='track'&&$statusFilter==='TO_BE_START'?'active':''; ?>" href="?view=track&amp;status=TO_BE_START">待启动项目</a>
      <a class="<?php echo $view==='track'&&$statusFilter==='STARTING'?'active':''; ?>" href="?view=track&amp;status=STARTING">运行中项目</a>
      <a class="<?php echo $view==='track'&&$statusFilter==='PAUSED'?'active':''; ?>" href="?view=track&amp;status=PAUSED&amp;scope=owner">已暂停项目</a>
      <a class="<?php echo $view==='track'&&$statusFilter==='FINISHED'?'active':''; ?>" href="?view=track&amp;status=FINISHED">已完成项目</a>
      <div class="nav-label">基础数据</div>
      <a class="<?php echo $view==='roles'?'active':''; ?>" href="?view=roles">项目角色</a>
      <a class="<?php echo $view==='workflow'?'active':''; ?>" href="?view=workflow">项目流程</a>
      <a class="<?php echo $view==='settings'?'active':''; ?>" href="?view=settings">设置</a>
    </nav>
  </aside>
  <main class="pm-main">
    <header class="pm-header"><div><h1><?php echo pm_h(isset($viewTitles[$view])?$viewTitles[$view]:$view); ?></h1><p>项目、任务和交付物统一协作</p></div><div class="pm-user"><?php echo pm_h($_SESSION['UsersRealName']); ?></div></header>
    <?php if ($error): ?><div class="alert error"><?php echo pm_h($error); ?></div><?php elseif (pm_get('ok','') || pm_get('installed','')): ?><div class="alert success">操作已完成</div><?php endif; ?>

<?php if ($view === 'dashboard'):
    $counts = array('TO_BE_START'=>0,'STARTING'=>0,'PAUSED'=>0,'FINISHED'=>0); foreach($projects as $p) if(isset($counts[$p['status']])) $counts[$p['status']]++;
    $myTasks = pm_rows("SELECT t.*,p.name project_name FROM pm_tasks t JOIN pm_projects p ON p.id=t.project_id JOIN pm_task_assignments a ON a.task_id=t.id WHERE a.user_id='" . pm_q($_SESSION['UserID']) . "' AND t.status<>'FINISHED' ORDER BY t.planned_end IS NULL,t.planned_end LIMIT 8"); ?>
  <section class="stats"><div><span>全部项目</span><strong><?php echo count($projects); ?></strong></div><div><span>待启动</span><strong><?php echo $counts['TO_BE_START']; ?></strong></div><div><span>运行中</span><strong><?php echo $counts['STARTING']; ?></strong></div><div><span>已暂停</span><strong><?php echo $counts['PAUSED']; ?></strong></div><div><span>已完成</span><strong><?php echo $counts['FINISHED']; ?></strong></div></section>
  <section class="panel"><div class="panel-head"><h2>我的未完成任务</h2><a class="btn secondary" href="?view=track&amp;status=STARTING&amp;scope=unfinished_tasks">查看全部</a></div><?php pm_render_task_table($myTasks, true); ?></section>
  <section class="panel"><div class="panel-head"><h2>最近项目</h2><a class="btn" href="?view=instances">项目实例</a></div><?php pm_render_project_table(array_slice($projects,0,8)); ?></section>

<?php elseif ($view === 'roles'): ?>
  <section class="panel"><div class="panel-head"><h2>项目角色</h2><button class="btn" data-open="role-form">新建角色</button></div>
  <form class="filters" method="get"><input type="hidden" name="view" value="roles"><input name="q" value="<?php echo pm_h(pm_get('q','')); ?>" placeholder="按名称搜索"><button class="btn secondary">查询</button></form>
  <table><thead><tr><th>名称</th><th>性质</th><th>备注</th><th>创建用户</th><th>更新时间</th><th></th></tr></thead><tbody><?php foreach($roles as $role): if(pm_get('q','') && mb_strpos($role['name'],pm_get('q',''))===false) continue; ?><tr><td><?php echo pm_h($role['name']); ?></td><td><span class="badge"><?php echo $role['nature']==='SYSTEM'?'系统内建':'用户定义'; ?></span></td><td><?php echo pm_h($role['notes']); ?></td><td><?php echo pm_h($role['created_by']); ?></td><td><?php echo pm_h($role['updated_at']); ?></td><td class="actions"><?php if($role['nature']==='CUSTOM'): ?><button class="link-btn" data-edit-role='<?php echo pm_h(json_encode($role)); ?>'>编辑</button><form method="post" onsubmit="return confirm('确认删除该角色？')"><input type="hidden" name="FormID" value="<?php echo pm_h($_SESSION['FormID']); ?>"><input type="hidden" name="action" value="delete_role"><input type="hidden" name="id" value="<?php echo intval($role['id']); ?>"><button class="link-btn danger">删除</button></form><?php endif; ?></td></tr><?php endforeach; ?></tbody></table></section>
  <dialog id="role-form"><form method="post"><div class="dialog-head"><h2>项目角色</h2><button type="button" data-close>×</button></div><input type="hidden" name="FormID" value="<?php echo pm_h($_SESSION['FormID']); ?>"><input type="hidden" name="action" value="save_role"><input type="hidden" name="id" value=""><label>名称<input name="name" required maxlength="80"></label><label>备注<textarea name="notes" maxlength="255"></textarea></label><div class="dialog-actions"><button type="button" class="btn secondary" data-close>取消</button><button class="btn">保存</button></div></form></dialog>

<?php elseif ($view === 'project_templates' || $view === 'task_templates'):
    $library = $view === 'task_templates' ? 'TASK' : 'PROJECT'; $nodes=pm_rows("SELECT * FROM pm_template_nodes WHERE library='$library' ORDER BY COALESCE(parent_id,0),sort_order,id"); $taskTemplateNodes=pm_rows("SELECT * FROM pm_template_nodes WHERE library='TASK' ORDER BY COALESCE(parent_id,0),sort_order,id"); $selectedId=intval(pm_get('selected',0)); $selected=$selectedId?pm_row("SELECT * FROM pm_template_nodes WHERE id=$selectedId AND library='$library'"):null; if(!$selected)$selectedId=0; ?>
  <div class="split"><section class="panel tree-panel"><div class="panel-head"><h2><?php echo $library==='PROJECT'?'项目模板':'任务模板'; ?></h2><div class="actions"><button type="button" class="btn secondary tree-action" data-tree-action="collapse">全部折叠</button><button type="button" class="btn secondary tree-action" data-tree-action="expand">全部展开</button><button class="btn" data-open-template data-parent-id="<?php echo $selectedId; ?>">新建</button></div></div><?php pm_render_template_tree($nodes,0,$selectedId); ?></section>
  <section class="panel detail-panel"><?php if(!$selected): ?><div class="empty">从左侧选择模板节点</div><?php else: ?><div class="panel-head"><div><span class="eyebrow"><?php echo pm_h($selected['node_type']); ?></span><h2><?php echo pm_h($selected['name']); ?></h2></div><div class="actions"><button class="btn secondary" data-open-template data-parent-id="<?php echo $selectedId; ?>">新建下级</button><button class="btn secondary" data-edit-template='<?php echo pm_h(json_encode($selected)); ?>'>编辑</button><form method="post"><input type="hidden" name="FormID" value="<?php echo pm_h($_SESSION['FormID']); ?>"><input type="hidden" name="action" value="copy_template"><input type="hidden" name="id" value="<?php echo $selectedId; ?>"><button class="btn secondary">复制</button></form><form method="post"><input type="hidden" name="FormID" value="<?php echo pm_h($_SESSION['FormID']); ?>"><input type="hidden" name="action" value="move_template"><input type="hidden" name="id" value="<?php echo $selectedId; ?>"><input type="hidden" name="direction" value="UP"><button class="btn secondary" title="上移">↑</button></form><form method="post"><input type="hidden" name="FormID" value="<?php echo pm_h($_SESSION['FormID']); ?>"><input type="hidden" name="action" value="move_template"><input type="hidden" name="id" value="<?php echo $selectedId; ?>"><input type="hidden" name="direction" value="DOWN"><button class="btn secondary" title="下移">↓</button></form><?php if($selected['node_type']==='PROJECT'): ?><button class="btn" data-open="instance-form">创建项目实例</button><?php endif; ?><form method="post" onsubmit="return confirm('确认删除？')"><input type="hidden" name="FormID" value="<?php echo pm_h($_SESSION['FormID']); ?>"><input type="hidden" name="action" value="delete_template"><input type="hidden" name="id" value="<?php echo $selectedId; ?>"><button class="btn danger">删除</button></form></div></div>
  <div class="detail-grid"><div><span>编码</span><strong><?php echo pm_h($selected['code']?:'-'); ?></strong></div><div><span>WBS</span><strong><?php echo pm_h($selected['wbs']?:'-'); ?></strong></div><div><span>预计工期</span><strong><?php echo pm_h($selected['duration']); ?> 天</strong></div><div><span>启动条件</span><strong><?php echo pm_h(pm_condition_name($selected['start_condition'])); ?></strong></div></div><div class="description"><?php echo nl2br(pm_h($selected['description'])); ?></div>
  <?php $assignments=pm_rows('SELECT a.*,r.name role_name,u.realname FROM pm_template_assignments a JOIN pm_roles r ON r.id=a.role_id LEFT JOIN www_users u ON u.userid=a.user_id WHERE a.node_id='.$selectedId); ?>
  <h3>项目角色</h3><div class="chips"><?php foreach($assignments as $a): ?><span><?php echo pm_h($a['role_name'].' · '.($a['realname']?:'未指定')); ?><form method="post"><input type="hidden" name="FormID" value="<?php echo pm_h($_SESSION['FormID']); ?>"><input type="hidden" name="action" value="remove_template_role"><input type="hidden" name="id" value="<?php echo intval($a['id']); ?>"><button>×</button></form></span><?php endforeach; ?></div>
  <form class="inline-form" method="post"><input type="hidden" name="FormID" value="<?php echo pm_h($_SESSION['FormID']); ?>"><input type="hidden" name="action" value="assign_template_role"><input type="hidden" name="node_id" value="<?php echo $selectedId; ?>"><?php pm_role_select($roles,'role_id'); pm_user_select($users,'user_id'); ?><button class="btn secondary">添加角色</button></form>
  <?php if($selected['node_type']==='TASK'): $templateDeps=pm_rows('SELECT n.* FROM pm_template_dependencies d JOIN pm_template_nodes n ON n.id=d.predecessor_id WHERE d.task_id='.$selectedId); ?><h3>前置任务</h3><div class="chips"><?php foreach($templateDeps as $dep): ?><span><?php echo pm_h($dep['wbs'].' '.$dep['name']); ?><form method="post"><input type="hidden" name="FormID" value="<?php echo pm_h($_SESSION['FormID']); ?>"><input type="hidden" name="action" value="remove_template_dependency"><input type="hidden" name="task_id" value="<?php echo $selectedId; ?>"><input type="hidden" name="predecessor_id" value="<?php echo intval($dep['id']); ?>"><button title="移除">×</button></form></span><?php endforeach; ?></div><form class="inline-form" method="post"><input type="hidden" name="FormID" value="<?php echo pm_h($_SESSION['FormID']); ?>"><input type="hidden" name="action" value="add_template_dependency"><input type="hidden" name="task_id" value="<?php echo $selectedId; ?>"><select name="predecessor_id"><option value="">选择前置任务</option><?php foreach($nodes as $n)if($n['node_type']==='TASK'&&intval($n['id'])!==$selectedId): ?><option value="<?php echo intval($n['id']); ?>"><?php echo pm_h($n['wbs'].' '.$n['name']); ?></option><?php endif; ?></select><button class="btn secondary">添加</button></form><?php endif; ?>
  <?php if($library==='PROJECT' && in_array($selected['node_type'],array('PROJECT','TASK'),true) && count($taskTemplateNodes)): ?><h3>引用任务模板</h3><form class="inline-form" method="post"><input type="hidden" name="FormID" value="<?php echo pm_h($_SESSION['FormID']); ?>"><input type="hidden" name="action" value="import_task_template"><input type="hidden" name="parent_id" value="<?php echo $selectedId; ?>"><select name="source_id"><option value="">选择任务模板</option><?php foreach($taskTemplateNodes as $n)if($n['node_type']==='TASK'&&!pm_template_branch_has_folders(intval($n['id']))): ?><option value="<?php echo intval($n['id']); ?>"><?php echo pm_h($n['wbs'].' '.$n['name']); ?></option><?php endif; ?></select><button class="btn secondary">引用</button></form><?php endif; ?>
  <?php endif; ?></section></div>
  <dialog id="template-form"><form method="post"><div class="dialog-head"><h2>模板节点</h2><button type="button" data-close>×</button></div><input type="hidden" name="FormID" value="<?php echo pm_h($_SESSION['FormID']); ?>"><input type="hidden" name="action" value="save_template"><input type="hidden" name="id" value=""><input type="hidden" name="library" value="<?php echo $library; ?>"><div class="form-grid"><label>类型<select name="node_type"><option value="FOLDER">文件夹</option><?php if($library==='PROJECT'): ?><option value="PROJECT">项目模板</option><?php endif; ?><option value="TASK">任务</option></select></label><label>上级<select name="parent_id"><option value="0" data-node-type="ROOT">根目录</option><?php foreach($nodes as $n): ?><option value="<?php echo intval($n['id']); ?>" data-node-type="<?php echo pm_h($n['node_type']); ?>"><?php echo pm_h($n['name']); ?></option><?php endforeach; ?></select></label><label>编码<input name="code" maxlength="40"></label><label>名称<input name="name" required maxlength="120"></label><label>WBS<input name="wbs" maxlength="40"></label><label>工期（天）<input name="duration" type="number" min="0" step="0.5"></label><label>任务类型<select name="task_type"><option value="NORMAL">普通任务</option><option value="PRODUCTION">生产任务</option><option value="REVIEW">评审任务</option><option value="MILESTONE">里程碑</option></select></label><label>启动条件<select name="start_condition"><option value="MANUAL">手动启动</option><option value="ANY">任一前置任务</option><option value="ALL">全部前置任务</option></select></label><label>排序<input name="sort_order" type="number" value="0"></label></div><label>描述<textarea name="description"></textarea></label><div class="dialog-actions"><button type="button" class="btn secondary" data-close>取消</button><button class="btn">保存</button></div></form></dialog>
  <?php if($selected && $selected['node_type']==='PROJECT'): ?><dialog id="instance-form"><form method="post"><div class="dialog-head"><h2>创建项目实例</h2><button type="button" data-close>×</button></div><input type="hidden" name="FormID" value="<?php echo pm_h($_SESSION['FormID']); ?>"><input type="hidden" name="action" value="instantiate"><input type="hidden" name="template_id" value="<?php echo $selectedId; ?>"><div class="form-grid"><label>项目编码<input name="code" required value="P<?php echo date('YmdHis'); ?>"></label><label>项目名称<input name="name" required value="<?php echo pm_h($selected['name']); ?>"></label><label>负责人<?php pm_user_select($users,'owner_id'); ?></label><label>计划开始<input name="planned_start" type="datetime-local"></label><label>计划结束<input name="planned_end" type="datetime-local"></label></div><label>项目描述<textarea name="description"><?php echo pm_h($selected['description']); ?></textarea></label><div class="dialog-actions"><button type="button" class="btn secondary" data-close>取消</button><button class="btn">创建</button></div></form></dialog><?php endif; ?>

<?php elseif ($view === 'instances'):
    $projectQuery=trim(pm_get('q','')); $projectStatus=pm_get('project_status','ALL'); $instanceProjects=array();
    foreach($projects as $p) {
        if($projectStatus!=='ALL' && $p['status']!==$projectStatus) continue;
        if($projectQuery!=='' && mb_strpos($p['name'].' '.$p['code'].' '.$p['owner_name'],$projectQuery)===false) continue;
        $instanceProjects[]=$p;
    } ?>
  <div class="split projects-split"><section class="panel tree-panel"><div class="panel-head"><h2>项目实例</h2></div><form class="instance-filter" method="get"><input type="hidden" name="view" value="instances"><input name="q" value="<?php echo pm_h($projectQuery); ?>" placeholder="搜索项目名称、编码、负责人"><select name="project_status"><option value="ALL">全部状态</option><option value="TO_BE_START" <?php echo $projectStatus==='TO_BE_START'?'selected':''; ?>>待启动</option><option value="STARTING" <?php echo $projectStatus==='STARTING'?'selected':''; ?>>运行中</option><option value="PAUSED" <?php echo $projectStatus==='PAUSED'?'selected':''; ?>>已暂停</option><option value="FINISHED" <?php echo $projectStatus==='FINISHED'?'selected':''; ?>>已完成</option></select><button class="btn secondary">查询</button></form><div class="project-list"><?php foreach($instanceProjects as $p): $pct=$p['task_count']?round($p['finished_count']*100/$p['task_count']):0; ?><a class="project-item <?php echo $selectedProjectId===intval($p['id'])?'active':''; ?>" href="?view=instances&amp;project=<?php echo intval($p['id']); ?>"><span class="status-dot <?php echo pm_h(strtolower($p['status'])); ?>"></span><div><strong><?php echo pm_h($p['name']); ?></strong><small><?php echo pm_h($p['code'].' · '.pm_status_name($p['status'])); ?></small><div class="progress"><i style="width:<?php echo $pct; ?>%"></i></div></div><b><?php echo $pct; ?>%</b></a><?php endforeach; ?><?php if(!count($instanceProjects)): ?><div class="empty compact">没有符合条件的项目</div><?php endif; ?></div></section>
  <section class="panel detail-panel"><?php if(!$selectedProject): ?><div class="empty">选择项目查看任务和进度</div><?php else: $tasks=pm_rows('SELECT * FROM pm_tasks WHERE project_id='.$selectedProjectId.' ORDER BY sort_order,wbs,id'); ?><div class="panel-head"><div><span class="status <?php echo pm_h(strtolower($selectedProject['status'])); ?>"><?php echo pm_h(pm_status_name($selectedProject['status'])); ?></span><h2><?php echo pm_h($selectedProject['name']); ?></h2></div><div class="actions"><button class="btn secondary" data-open="project-form">编辑</button><a class="btn secondary" href="?view=gantt&amp;project=<?php echo $selectedProjectId; ?>">甘特图</a><?php if($selectedProject['status']==='TO_BE_START'): ?><?php pm_transition_button('project',$selectedProjectId,$selectedProjectId,'STARTING','启动项目'); ?><?php elseif($selectedProject['status']==='STARTING'): ?><?php pm_transition_button('project',$selectedProjectId,$selectedProjectId,'PAUSED','暂停项目','danger'); ?><?php pm_transition_button('project',$selectedProjectId,$selectedProjectId,'FINISHED','完成项目'); ?><?php elseif($selectedProject['status']==='PAUSED'): ?><?php pm_transition_button('project',$selectedProjectId,$selectedProjectId,'STARTING','恢复运行'); ?><?php endif; ?><button class="btn" data-open-task data-parent-id="0">增补子任务</button></div></div>
  <div class="detail-grid"><div><span>编码</span><strong><?php echo pm_h($selectedProject['code']); ?></strong></div><div><span>负责人</span><strong><?php echo pm_h($selectedProject['owner_id']); ?></strong></div><div><span>计划开始</span><strong><?php echo pm_h($selectedProject['planned_start']?:'-'); ?></strong></div><div><span>计划结束</span><strong><?php echo pm_h($selectedProject['planned_end']?:'-'); ?></strong></div></div>
  <div class="tabs"><button class="active" data-tab="tasks">下级任务</button><button data-tab="project-roles">项目角色</button><button data-tab="logs">操作日志</button></div>
  <div data-tab-panel="tasks"><div class="task-tree-toolbar"><span>项目任务结构</span><div class="actions"><button type="button" class="btn secondary tree-action" data-tree-action="collapse">全部折叠</button><button type="button" class="btn secondary tree-action" data-tree-action="expand">全部展开</button></div></div><?php pm_render_task_tree($tasks,0,$selectedTaskId,$selectedProjectId); ?></div>
  <div data-tab-panel="project-roles" hidden><?php $projectRoles=pm_rows('SELECT pr.*,r.name role_name,u.realname FROM pm_project_roles pr JOIN pm_roles r ON r.id=pr.role_id LEFT JOIN www_users u ON u.userid=pr.user_id WHERE pr.project_id='.$selectedProjectId); ?><div class="chips"><?php foreach($projectRoles as $a): ?><span><?php echo pm_h($a['role_name'].' · '.$a['realname']); ?><form method="post"><input type="hidden" name="FormID" value="<?php echo pm_h($_SESSION['FormID']); ?>"><input type="hidden" name="action" value="remove_project_role"><input type="hidden" name="id" value="<?php echo intval($a['id']); ?>"><button>×</button></form></span><?php endforeach; ?></div><form class="inline-form" method="post"><input type="hidden" name="FormID" value="<?php echo pm_h($_SESSION['FormID']); ?>"><input type="hidden" name="action" value="assign_project_role"><input type="hidden" name="project_id" value="<?php echo $selectedProjectId; ?>"><?php pm_role_select($roles,'role_id');pm_user_select($users,'user_id'); ?><button class="btn secondary">添加角色</button></form></div>
  <div data-tab-panel="logs" hidden><?php $logs=pm_rows('SELECT * FROM pm_logs WHERE project_id='.$selectedProjectId.' ORDER BY created_at DESC LIMIT 100'); ?><div class="timeline"><?php foreach($logs as $log): ?><div><time><?php echo pm_h($log['created_at']); ?></time><strong><?php echo pm_h($log['user_id'].' · '.$log['action']); ?></strong><p><?php echo pm_h($log['detail']); ?></p></div><?php endforeach; ?></div></div>
  <?php if($selectedTask): pm_render_task_detail($selectedTask,$selectedProjectId,$tasks,$roles,$users); endif; ?>
  <dialog id="project-form"><form method="post"><div class="dialog-head"><h2>编辑项目</h2><button type="button" data-close>×</button></div><input type="hidden" name="FormID" value="<?php echo pm_h($_SESSION['FormID']); ?>"><input type="hidden" name="action" value="save_project"><input type="hidden" name="id" value="<?php echo $selectedProjectId; ?>"><div class="form-grid"><label>编码<input name="code" required value="<?php echo pm_h($selectedProject['code']); ?>"></label><label>名称<input name="name" required value="<?php echo pm_h($selectedProject['name']); ?>"></label><label>负责人<?php pm_user_select($users,'owner_id',$selectedProject['owner_id']); ?></label><label>计划开始<input type="datetime-local" name="planned_start" value="<?php echo pm_h(str_replace(' ','T',substr($selectedProject['planned_start'],0,16))); ?>"></label><label>计划结束<input type="datetime-local" name="planned_end" value="<?php echo pm_h(str_replace(' ','T',substr($selectedProject['planned_end'],0,16))); ?>"></label></div><label>描述<textarea name="description"><?php echo pm_h($selectedProject['description']); ?></textarea></label><div class="dialog-actions"><button type="button" class="btn secondary" data-close>取消</button><button class="btn">保存</button></div></form></dialog>
  <?php pm_task_dialog($selectedProjectId,$tasks); ?><form method="post" class="project-delete" onsubmit="return confirm('删除项目将同时删除任务、分配和日志，确认继续？')"><input type="hidden" name="FormID" value="<?php echo pm_h($_SESSION['FormID']); ?>"><input type="hidden" name="action" value="delete_project"><input type="hidden" name="id" value="<?php echo $selectedProjectId; ?>"><button class="link-btn danger">删除整个项目</button></form><?php endif; ?></section></div>

<?php elseif ($view === 'track'):
    $defaultScope=$statusFilter==='PAUSED'?'owner':($statusFilter==='STARTING'?'unfinished_tasks':'tasks');
    $scope=pm_get('scope',$defaultScope); $trackProjects=array(); foreach($projects as $p) if($p['status']===$statusFilter) $trackProjects[]=$p;
    if($statusFilter==='STARTING') $taskStatusCondition="p.status='STARTING' AND t.status".($scope==='finished_tasks'?"='FINISHED'":"<>'FINISHED'");
    elseif($statusFilter==='PAUSED') $taskStatusCondition="p.status='PAUSED' AND t.status<>'FINISHED'";
    else $taskStatusCondition="p.status='".pm_q($statusFilter)."' AND t.status='".pm_q($statusFilter)."'";
    $myTaskRows=pm_rows("SELECT DISTINCT t.*,p.name project_name FROM pm_tasks t JOIN pm_projects p ON p.id=t.project_id LEFT JOIN pm_task_assignments a ON a.task_id=t.id WHERE ".$taskStatusCondition." AND (a.user_id='".pm_q($_SESSION['UserID'])."' OR t.created_by='".pm_q($_SESSION['UserID'])."') ORDER BY t.planned_end IS NULL,t.planned_end"); ?>
  <section class="panel"><div class="panel-head"><h2><?php echo pm_h(pm_status_name($statusFilter)); ?>项目跟踪</h2></div><div class="scope-tabs"><?php if($statusFilter==='STARTING'): ?><a class="<?php echo $scope==='unfinished_tasks'?'active':''; ?>" href="?view=track&amp;status=STARTING&amp;scope=unfinished_tasks">未完成任务</a><a class="<?php echo $scope==='finished_tasks'?'active':''; ?>" href="?view=track&amp;status=STARTING&amp;scope=finished_tasks">已完成任务</a><?php else: ?><a class="<?php echo $scope==='tasks'?'active':''; ?>" href="?view=track&amp;status=<?php echo pm_h($statusFilter); ?>&amp;scope=tasks">我的任务</a><?php endif; ?><a class="<?php echo $scope==='owner'?'active':''; ?>" href="?view=track&amp;status=<?php echo pm_h($statusFilter); ?>&amp;scope=owner">我负责的项目</a><a class="<?php echo $scope==='created'?'active':''; ?>" href="?view=track&amp;status=<?php echo pm_h($statusFilter); ?>&amp;scope=created">我创建的项目</a></div><?php if(in_array($scope,array('tasks','unfinished_tasks','finished_tasks'))) pm_render_task_table($myTaskRows,true); else { $filtered=array();foreach($trackProjects as $p) if(($scope==='owner'&&$p['owner_id']===$_SESSION['UserID'])||($scope==='created'&&$p['created_by']===$_SESSION['UserID']))$filtered[]=$p;pm_render_project_table($filtered); } ?></section>

<?php elseif ($view === 'gantt' && $selectedProject): $tasks=pm_rows('SELECT * FROM pm_tasks WHERE project_id='.$selectedProjectId.' ORDER BY sort_order,wbs,id'); ?>
  <section class="panel"><div class="panel-head"><div><a class="back" href="?view=instances&amp;project=<?php echo $selectedProjectId; ?>">← 返回项目</a><h2><?php echo pm_h($selectedProject['name']); ?> · 甘特图</h2></div></div><?php pm_render_gantt($tasks); ?></section>

<?php elseif ($view === 'workflow'): ?>
  <section class="panel"><div class="panel-head"><h2>项目流程</h2></div><div class="workflow"><div><span>1</span><strong>待启动</strong><p>从模板创建项目，确认负责人、角色、计划时间和任务依赖。</p></div><i>→</i><div><span>2</span><strong>运行中</strong><p>按前置条件启动任务，登记输入输出，跟踪计划与实际进度。</p></div><i>→</i><div><span>3</span><strong>已完成</strong><p>全部交付物确认后完成任务和项目，保留完整操作日志。</p></div></div><div class="rule-list"><h3>任务启动规则</h3><p><b>手动启动：</b>负责人可直接启动。</p><p><b>任一前置任务：</b>至少一个前置任务完成后可以启动。</p><p><b>全部前置任务：</b>所有前置任务完成后才可以启动。</p></div></section>

<?php elseif ($view === 'settings'): $setting=pm_row("SELECT setting_value FROM pm_settings WHERE setting_key='work_calendar'"); ?>
  <section class="panel narrow"><div class="panel-head"><h2>基础设置</h2></div><form method="post"><input type="hidden" name="FormID" value="<?php echo pm_h($_SESSION['FormID']); ?>"><input type="hidden" name="action" value="save_settings"><label>项目管理日历<select name="work_calendar"><option value="DOUBLE_REST" <?php echo $setting&&$setting['setting_value']==='DOUBLE_REST'?'selected':''; ?>>双休</option><option value="SINGLE_REST" <?php echo !$setting||$setting['setting_value']==='SINGLE_REST'?'selected':''; ?>>单休</option><option value="NO_REST" <?php echo $setting&&$setting['setting_value']==='NO_REST'?'selected':''; ?>>无休</option></select></label><p class="hint">用于后续计划工期和工作日计算。</p><button class="btn">保存设置</button></form></section>
<?php endif; ?>
  </main>
</div>
<script src="project_management/app.js?v=<?php echo filemtime(__DIR__ . '/project_management/app.js'); ?>"></script>
</body></html>
<?php
function pm_user_select($users,$name,$selected='') { echo '<select name="'.pm_h($name).'" required><option value="">请选择</option>'; foreach($users as $u) echo '<option value="'.pm_h($u['userid']).'" '.($selected===$u['userid']?'selected':'').'>'.pm_h($u['realname'].' · '.$u['depart_code']).'</option>'; echo '</select>'; }
function pm_role_select($roles,$name) { echo '<select name="'.pm_h($name).'" required><option value="">请选择角色</option>'; foreach($roles as $r) echo '<option value="'.intval($r['id']).'">'.pm_h($r['name']).'</option>'; echo '</select>'; }
function pm_render_project_table($rows) { echo '<div class="table-wrap"><table><thead><tr><th>项目</th><th>负责人</th><th>状态</th><th>计划结束</th><th>进度</th></tr></thead><tbody>'; if(!count($rows))echo '<tr><td colspan="5" class="empty-cell">暂无数据</td></tr>'; foreach($rows as $p){$pct=$p['task_count']?round($p['finished_count']*100/$p['task_count']):0;echo '<tr><td><a href="?view=instances&amp;project='.intval($p['id']).'"><strong>'.pm_h($p['name']).'</strong><small>'.pm_h($p['code']).'</small></a></td><td>'.pm_h($p['owner_name']).'</td><td><span class="status '.pm_h(strtolower($p['status'])).'">'.pm_h(pm_status_name($p['status'])).'</span></td><td>'.pm_h($p['planned_end']?:'-').'</td><td>'.$pct.'%</td></tr>';} echo '</tbody></table></div>'; }
function pm_render_task_table($rows,$showProject,$projectId=0) { echo '<div class="table-wrap"><table><thead><tr>'.($showProject?'<th>项目</th>':'').'<th>WBS / 任务</th><th>类型</th><th>状态</th><th>计划结束</th><th>操作</th></tr></thead><tbody>'; if(!count($rows))echo '<tr><td colspan="6" class="empty-cell">暂无任务</td></tr>'; foreach($rows as $t){echo '<tr>'.($showProject?'<td>'.pm_h(isset($t['project_name'])?$t['project_name']:'').'</td>':'').'<td><a href="?view=instances&amp;project='.intval($t['project_id']).'&amp;task='.intval($t['id']).'"><strong>'.pm_h($t['wbs'].' '.$t['name']).'</strong><small>'.pm_h($t['code']).'</small></a></td><td>'.pm_h($t['task_type']).'</td><td><span class="status '.pm_h(strtolower($t['status'])).'">'.pm_h(pm_status_name($t['status'])).'</span></td><td>'.pm_h($t['planned_end']?:'-').'</td><td><a class="link-btn" href="?view=instances&amp;project='.intval($t['project_id']).'&amp;task='.intval($t['id']).'">详情</a></td></tr>';} echo '</tbody></table></div>'; }
function pm_node_icon($type) {
    $class = 'node-icon ' . strtolower($type);
    $icons = array(
        'FOLDER' => 'folder.png',
        'PROJECT' => 'project.webp',
        'TASK' => 'task.webp'
    );
    $file = isset($icons[$type]) ? $icons[$type] : $icons['TASK'];
    $version = @filemtime(__DIR__ . '/project_management/icons/' . $file);
    return '<img class="'.pm_h($class).'" src="project_management/icons/'.pm_h($file).'?v='.intval($version).'" alt="" aria-hidden="true">';
}
function pm_render_template_tree($nodes,$parent,$selected) {
    echo '<ul class="template-tree'.($parent?' tree-children':'').'">';
    $found=false;
    foreach($nodes as $n){
        $pid=$n['parent_id']===null?0:intval($n['parent_id']);
        if($pid!==intval($parent))continue;
        $found=true;
        $hasChildren=false;
        foreach($nodes as $child){
            if(intval($child['parent_id'])===intval($n['id'])){$hasChildren=true;break;}
        }
        echo '<li class="template-node'.($hasChildren?' has-children':'').'"><div class="tree-row">';
        echo $hasChildren?'<button type="button" class="tree-toggle" aria-expanded="true" title="折叠">−</button>':'<span class="tree-toggle-spacer"></span>';
        echo '<a class="'.($selected===intval($n['id'])?'active':'').'" href="?view='.($n['library']==='TASK'?'task_templates':'project_templates').'&amp;selected='.intval($n['id']).'">'.pm_node_icon($n['node_type']).'<span>'.pm_h(($n['wbs']?'['.$n['wbs'].'] ':'').$n['name']).'</span></a></div>';
        if($hasChildren)pm_render_template_tree($nodes,$n['id'],$selected);
        echo '</li>';
    }
    if(!$found&&$parent===0)echo '<li class="empty-cell">暂无模板，点击“新建”开始</li>';
    echo '</ul>';
}
function pm_render_task_tree($tasks,$parent,$selected,$projectId) {
    echo '<ul class="task-tree'.($parent?' tree-children':'').'">';
    $found=false;
    foreach($tasks as $task){
        $parentId=$task['parent_id']===null?0:intval($task['parent_id']);
        if($parentId!==intval($parent))continue;
        $found=true;
        $hasChildren=false;
        foreach($tasks as $child){
            if(intval($child['parent_id'])===intval($task['id'])){$hasChildren=true;break;}
        }
        $taskId=intval($task['id']);
        echo '<li class="task-node'.($hasChildren?' has-children':'').'"><div class="tree-row">';
        echo $hasChildren?'<button type="button" class="tree-toggle" aria-expanded="true" title="折叠">−</button>':'<span class="tree-toggle-spacer"></span>';
        echo '<a class="task-tree-link'.($selected===$taskId?' active':'').'" href="?view=instances&amp;project='.intval($projectId).'&amp;task='.$taskId.'"><span class="status-dot '.pm_h(strtolower($task['status'])).'"></span><span class="task-tree-name"><strong>'.pm_h(($task['wbs']?'['.$task['wbs'].'] ':'').$task['name']).'</strong><small>'.pm_h($task['code'].' · '.pm_status_name($task['status'])).'</small></span></a>';
        echo '<button type="button" class="task-add-child" data-open-task data-parent-id="'.$taskId.'" title="新增下级任务">+</button></div>';
        if($hasChildren)pm_render_task_tree($tasks,$taskId,$selected,$projectId);
        echo '</li>';
    }
    if(!$found&&$parent===0)echo '<li class="empty-cell">暂无任务，点击“新建任务”开始</li>';
    echo '</ul>';
}
function pm_transition_button($type,$id,$projectId,$status,$label,$style='') { echo '<form method="post"><input type="hidden" name="FormID" value="'.pm_h($_SESSION['FormID']).'"><input type="hidden" name="action" value="transition_'.$type.'"><input type="hidden" name="id" value="'.intval($id).'"><input type="hidden" name="project_id" value="'.intval($projectId).'"><input type="hidden" name="status" value="'.pm_h($status).'"><button class="btn'.($style?' '.pm_h($style):'').'">'.pm_h($label).'</button></form>'; }
function pm_task_dialog($projectId,$tasks) { ?><dialog id="task-form"><form method="post"><div class="dialog-head"><h2>任务</h2><button type="button" data-close>×</button></div><input type="hidden" name="FormID" value="<?php echo pm_h($_SESSION['FormID']); ?>"><input type="hidden" name="action" value="save_task"><input type="hidden" name="id" value=""><input type="hidden" name="project_id" value="<?php echo intval($projectId); ?>"><div class="form-grid"><label>上级任务<select name="parent_id"><option value="0">无</option><?php foreach($tasks as $t): ?><option value="<?php echo intval($t['id']); ?>"><?php echo pm_h($t['wbs'].' '.$t['name']); ?></option><?php endforeach; ?></select></label><label>编码<input name="code" required></label><label>名称<input name="name" required></label><label>WBS<input name="wbs"></label><label>类型<select name="task_type"><option value="NORMAL">普通任务</option><option value="PRODUCTION">生产任务</option><option value="REVIEW">评审任务</option><option value="MILESTONE">里程碑</option></select></label><label>启动条件<select name="start_condition"><option value="MANUAL">手动启动</option><option value="ANY">任一前置任务</option><option value="ALL">全部前置任务</option></select></label><label>计划工期<input type="number" step="0.5" name="duration"></label><label>计划开始<input type="datetime-local" name="planned_start"></label><label>计划结束<input type="datetime-local" name="planned_end"></label><label>排序<input type="number" name="sort_order" value="0"></label></div><label>任务描述<textarea name="description"></textarea></label><div class="dialog-actions"><button type="button" class="btn secondary" data-close>取消</button><button class="btn">保存</button></div></form></dialog><?php }
function pm_render_task_detail($task,$projectId,$tasks,$roles,$users) { $taskId=intval($task['id']);$assign=pm_rows('SELECT a.*,r.name role_name,u.realname FROM pm_task_assignments a JOIN pm_roles r ON r.id=a.role_id LEFT JOIN www_users u ON u.userid=a.user_id WHERE a.task_id='.$taskId);$deps=pm_rows('SELECT t.* FROM pm_task_dependencies d JOIN pm_tasks t ON t.id=d.predecessor_id WHERE d.task_id='.$taskId);$io=pm_rows('SELECT * FROM pm_task_io WHERE task_id='.$taskId.' ORDER BY direction,id'); ?>
<aside class="task-drawer"><div class="panel-head"><div><span class="eyebrow">任务详情</span><h2><?php echo pm_h($task['wbs'].' '.$task['name']); ?></h2></div><a class="close-drawer" href="?view=instances&amp;project=<?php echo $projectId; ?>">×</a></div><div class="detail-grid"><div><span>状态</span><strong><?php echo pm_h(pm_status_name($task['status'])); ?></strong></div><div><span>启动条件</span><strong><?php echo pm_h(pm_condition_name($task['start_condition'])); ?></strong></div><div><span>计划工期</span><strong><?php echo pm_h($task['planned_duration']); ?> 天</strong></div><div><span>实际开始</span><strong><?php echo pm_h($task['actual_start']?:'-'); ?></strong></div></div><div class="actions"><?php if($task['status']==='TO_BE_START')pm_transition_button('task',$taskId,$projectId,'STARTING','启动任务');if($task['status']==='STARTING')pm_transition_button('task',$taskId,$projectId,'FINISHED','完成任务'); ?><form method="post"><input type="hidden" name="FormID" value="<?php echo pm_h($_SESSION['FormID']); ?>"><input type="hidden" name="action" value="move_task"><input type="hidden" name="id" value="<?php echo $taskId; ?>"><input type="hidden" name="project_id" value="<?php echo $projectId; ?>"><input type="hidden" name="direction" value="UP"><button class="btn secondary" title="上移">↑</button></form><form method="post"><input type="hidden" name="FormID" value="<?php echo pm_h($_SESSION['FormID']); ?>"><input type="hidden" name="action" value="move_task"><input type="hidden" name="id" value="<?php echo $taskId; ?>"><input type="hidden" name="project_id" value="<?php echo $projectId; ?>"><input type="hidden" name="direction" value="DOWN"><button class="btn secondary" title="下移">↓</button></form><button class="btn secondary" data-edit-task='<?php echo pm_h(json_encode($task)); ?>'>编辑</button><form method="post" onsubmit="return confirm('确认删除任务？')"><input type="hidden" name="FormID" value="<?php echo pm_h($_SESSION['FormID']); ?>"><input type="hidden" name="action" value="delete_task"><input type="hidden" name="id" value="<?php echo $taskId; ?>"><input type="hidden" name="project_id" value="<?php echo $projectId; ?>"><button class="btn danger">删除</button></form></div><h3>执行角色</h3><div class="chips"><?php foreach($assign as $a): ?><span><?php echo pm_h($a['role_name'].' · '.$a['realname']); ?><form method="post"><input type="hidden" name="FormID" value="<?php echo pm_h($_SESSION['FormID']); ?>"><input type="hidden" name="action" value="remove_task_assignment"><input type="hidden" name="id" value="<?php echo intval($a['id']); ?>"><input type="hidden" name="task_id" value="<?php echo $taskId; ?>"><input type="hidden" name="project_id" value="<?php echo $projectId; ?>"><button title="移除">×</button></form></span><?php endforeach; ?></div><form class="inline-form" method="post"><input type="hidden" name="FormID" value="<?php echo pm_h($_SESSION['FormID']); ?>"><input type="hidden" name="action" value="assign_task"><input type="hidden" name="task_id" value="<?php echo $taskId; ?>"><input type="hidden" name="project_id" value="<?php echo $projectId; ?>"><?php pm_role_select($roles,'role_id');pm_user_select($users,'user_id',''); ?><button class="btn secondary">分配</button></form><h3>前置任务</h3><div class="chips"><?php foreach($deps as $d): ?><span><?php echo pm_h($d['wbs'].' '.$d['name']); ?><form method="post"><input type="hidden" name="FormID" value="<?php echo pm_h($_SESSION['FormID']); ?>"><input type="hidden" name="action" value="remove_dependency"><input type="hidden" name="task_id" value="<?php echo $taskId; ?>"><input type="hidden" name="predecessor_id" value="<?php echo intval($d['id']); ?>"><input type="hidden" name="project_id" value="<?php echo $projectId; ?>"><button title="移除">×</button></form></span><?php endforeach; ?></div><form class="inline-form" method="post"><input type="hidden" name="FormID" value="<?php echo pm_h($_SESSION['FormID']); ?>"><input type="hidden" name="action" value="add_dependency"><input type="hidden" name="task_id" value="<?php echo $taskId; ?>"><input type="hidden" name="project_id" value="<?php echo $projectId; ?>"><select name="predecessor_id"><?php foreach($tasks as $t)if($t['id']!=$taskId): ?><option value="<?php echo intval($t['id']); ?>"><?php echo pm_h($t['wbs'].' '.$t['name']); ?></option><?php endif; ?></select><button class="btn secondary">添加</button></form><h3>任务输入 / 输出</h3><div class="io-list"><?php foreach($io as $item): ?><div class="io-item"><span class="badge"><?php echo $item['direction']==='INPUT'?'输入':'输出'; ?></span><strong><?php echo pm_h($item['name']); ?></strong><form method="post"><input type="hidden" name="FormID" value="<?php echo pm_h($_SESSION['FormID']); ?>"><input type="hidden" name="action" value="remove_io"><input type="hidden" name="id" value="<?php echo intval($item['id']); ?>"><input type="hidden" name="task_id" value="<?php echo $taskId; ?>"><input type="hidden" name="project_id" value="<?php echo $projectId; ?>"><button class="link-btn danger">移除</button></form><a href="<?php echo pm_h($item['reference']); ?>" target="_blank"><?php echo pm_h($item['reference']); ?></a><p><?php echo pm_h($item['notes']); ?></p></div><?php endforeach; ?></div><form class="inline-form io-form" method="post"><input type="hidden" name="FormID" value="<?php echo pm_h($_SESSION['FormID']); ?>"><input type="hidden" name="action" value="add_io"><input type="hidden" name="task_id" value="<?php echo $taskId; ?>"><input type="hidden" name="project_id" value="<?php echo $projectId; ?>"><select name="direction"><option value="INPUT">输入</option><option value="OUTPUT">输出</option></select><input name="name" required placeholder="名称"><input name="reference" placeholder="文件或系统链接"><input name="notes" placeholder="说明"><button class="btn secondary">添加</button></form></aside><?php }
function pm_render_gantt($tasks){if(!count($tasks)){echo '<div class="empty">暂无任务</div>';return;}$start=null;$end=null;foreach($tasks as $t){if($t['planned_start']&&(!$start||$t['planned_start']<$start))$start=$t['planned_start'];if($t['planned_end']&&(!$end||$t['planned_end']>$end))$end=$t['planned_end'];}if(!$start||!$end){echo '<div class="empty">请先设置任务计划开始和结束时间</div>';return;}$s=strtotime($start);$e=strtotime($end);$span=max(86400,$e-$s);echo '<div class="gantt"><div class="gantt-head"><b>任务</b><span>'.pm_h(substr($start,0,10)).' 至 '.pm_h(substr($end,0,10)).'</span></div>';foreach($tasks as $t){$left=$t['planned_start']?max(0,(strtotime($t['planned_start'])-$s)*100/$span):0;$width=$t['planned_end']&&$t['planned_start']?max(1,(strtotime($t['planned_end'])-strtotime($t['planned_start']))*100/$span):1;echo '<div class="gantt-row"><strong>'.pm_h($t['wbs'].' '.$t['name']).'</strong><div><i class="'.pm_h(strtolower($t['status'])).'" style="left:'.$left.'%;width:'.$width.'%"><span>'.pm_h(pm_status_name($t['status'])).'</span></i></div></div>';}echo '</div>';}
?>
