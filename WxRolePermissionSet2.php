<?php
ob_start();
include('includes/session.inc');
$Title = _('权限修改');
$ViewTopic = '权限修改';
$BookMark = '权限修改';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
//查询数据
//查询传递的用户id
if (isset($_GET['role_id'])) {
    $role_id = $_GET['role_id'];
}
//查询权限数据
$sql_modules = "
    SELECT p.module, p.permissions_id, p.permission_name
    FROM fa_permissions p
    WHERE 1=1
    GROUP BY p.module, p.permissions_id
";
$result_modules = DB_query($sql_modules, $db);
$modules = [];

while ($row = DB_fetch_assoc($result_modules)) {
    // 如果模块不存在于数组中，创建一个新的数组
    if (!isset($modules[$row['module']])) {
        $modules[$row['module']] = [];
    }
    // 将权限信息添加到对应模块的数组中
    $modules[$row['module']][] = [
        'permissions_id' => $row['permissions_id'],
        'permission_name' => $row['permission_name']
    ];
}
$sql_permissions = "SELECT permissions_id FROM fa_role_permissions WHERE roles_id = '" . $role_id . "'"; 
$result_permissions = DB_query($sql_permissions, $db);
$roles_permissions = array();
while ($permission = DB_fetch_assoc($result_permissions)) {
    $roles_permissions[] = ['permissions_id' => $permission['permissions_id']];
}

$permissions_ids = [];
foreach ($roles_permissions as $permission) {
    $permissions_ids[] = $permission['permissions_id'];
}

//表单提交
//取消
if (isset($_POST['cancel'])) {
    // 获取原有的查询参数
    $params = $_POST['params'];
    // 对参数进行URL编码以确保URL安全
    $encodedParams = urlencode($params);
    // 构建新的URL，包含原始查询参数
    $newUrl = $_SERVER['PHP_SELF'] . '?role_id=' . $encodedParams;
    // 清理输出缓冲区，确保header()能正确发送
    ob_clean();
    // 设置重定向头信息，延迟1秒后跳转
    header("Refresh: 1; URL=$newUrl");
    exit;
}
//确认
if (isset($_POST['sure'])) {
    //新数组
    $new_Permissions = isset($_POST['permissions_id']) ? $_POST['permissions_id'] : [];
    // print_r($selectedPermissions);
    //角色id
    $params = $_POST['params'];
    //查询该角色name
    $sql_selrolename = " select role_name from fa_roles where role_id = '".$params."'";
    $result_selrolename = DB_query($sql_selrolename, $db);
    $row_selrole = DB_fetch_assoc($result_selrolename);
    if ($row_selrole) {
        $rolename = $row_selrole['role_name'];
        // 现在 $role_name 包含了查询结果中的 role_name 值
    }
    //原始数组
    $sql_permissions1 = "SELECT permissions_id FROM fa_role_permissions WHERE roles_id = '" . $params . "'"; 
    $result_permissions1 = DB_query($sql_permissions1, $db);
    $old_Permissions = array();
    while ($permission1 = DB_fetch_assoc($result_permissions1)) {
        $old_Permissions[] = $permission1['permissions_id'];
    }
    // 初始化数组用于存储新增和删除的权限
    $added_Permissions = array();
    $deleted_Permissions = array();
    //新增数据
    foreach ($new_Permissions as $permission) {
        if (!in_array($permission, $old_Permissions)) {
            $added_Permissions[] = $permission;
        }
    }
    //删掉数据
    foreach ($old_Permissions as $permission) {
        if (!in_array($permission, $new_Permissions)) {
            $deleted_Permissions[] = $permission;
        }
    }
    // $difference = [
    //     'added' => $added_Permissions,
    //     'deleted' => $deleted_Permissions,
    // ];
    // print_r($difference);
    //插入新数据
    if ($added_Permissions) {
        $add_names = array(); // 初始化一个空数组来存储权限名称
        foreach ($added_Permissions as $permission_id) {    
            //新增到权限表
            $sql_newadd = "INSERT INTO fa_role_permissions
                    (
                        roles_id,permissions_id
                    ) 
                    VALUES 
                    (
                        '" . $params . "',
                        '" . $permission_id . "'
                    )
            ";
            $insert_newdata = DB_query($sql_newadd, $db);
            //查询对应的权限名称
            $sql_seladdname = " select * from fa_permissions where permissions_id = '".$permission_id."'";
            $result_seladdname = DB_query($sql_seladdname, $db);
            if (DB_num_rows($result_seladdname) != 0) {
                while ($seladdname = DB_fetch_assoc($result_seladdname)) {
                    $add_names[] = $seladdname['permission_name'];
                }
            }
        }     
        //新增到权限log表
        $add_string = implode(",", $add_names); //全部新增的菜单
        $sql_newaddlog = "INSERT INTO fa_role_permissions_log
                (
                    roleid_updated,menu_array,change_type,creation_date,created_by,role_remark
                ) 
                VALUES 
                (
                    '" . $params . "',
                    '" . $add_string . "',
                    '新增',
                    '" . time() . "',
                    '" . $_SESSION['UserID'] . "',
                    '" . $rolename . "'
                )
        ";
        $insert_newdatalog = DB_query($sql_newaddlog, $db); 
    }   
    //删除数据
    if ($deleted_Permissions) {
        foreach ($deleted_Permissions as $permission_id) {
            $sql_delete = "DELETE FROM fa_role_permissions WHERE permissions_id = '" . $permission_id . "' AND roles_id = '" . $params . "'";
            $del_olddata = DB_query($sql_delete, $db);
            //查询对应的权限名称
            $sql_seldelname = " select * from fa_permissions where permissions_id = '".$permission_id."'";
            $result_seldelname = DB_query($sql_seldelname, $db);
            if (DB_num_rows($result_seldelname) != 0) {
                while ($seldelname = DB_fetch_assoc($result_seldelname)) {
                    $del_names[] = $seldelname['permission_name'];
                }
            }
        }     
        //新增到权限log表
        $del_string = implode(",", $del_names); //全部新增的菜单
        $sql_newdellog = "INSERT INTO fa_role_permissions_log
                (
                    roleid_updated,menu_array,change_type,creation_date,created_by,role_remark
                ) 
                VALUES 
                (
                    '" . $params . "',
                    '" . $del_string . "',
                    '删除',
                    '" . time() . "',
                    '" . $_SESSION['UserID'] . "',
                    '" . $rolename . "'
                )
        ";
        $sql_newdellog = DB_query($sql_newdellog, $db); 
    }   

    // 对参数进行URL编码以确保URL安全
    $encodedParams = urlencode($params);
    // 构建新的URL，包含原始查询参数5
    $newUrl = $_SERVER['PHP_SELF'] . '?role_id=' . $encodedParams;
    // 清理输出缓冲区，确保header()能正确发送
    ob_clean();
    // 设置重定向头信息，延迟1秒后跳转
    header("Refresh: 1; URL=$newUrl");
    exit;
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <style>
        body {
            margin: 0;
            padding: 0;
        }

        .container {
            width: 90%;
            margin: 0 auto;
        }

        /* 按钮 */
        .button-container {
            text-align: center;
            margin: 1rem 0;
        }

        .confirm-button,
        .cancel-button {
            border: none !important;
            /* 去除边框 */
            box-shadow: none !important;
        }

        .confirm-button {
            background-color: #007bff;
        }

        .cancel-button {
            background-color: #ced4da;
        }

        .confirm-button:hover,
        .cancel-button:hover {
            opacity: 0.8;
        }

        /* 表格 */
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 4px;
        }

        tr>td:first-child {
            border-right: none;
        }

        tr:not(:last-child)>td:first-child~td {
            border-bottom: 1px solid #ccc;
        }

        /* 对包含二级菜单的单元格进行 Flexbox 布局 */
        td.second-menu-column {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
        }

        .menu-item {
            width: calc(20% - 10px);
            /* 根据需求调整宽度，假设每个菜单项占据总宽度的1/6减去间隔 */
            margin-right: 5px;
            margin-bottom: 10px;
            margin-top: 10px;
        }

        .menu-item label {
            display: block;
            width: 100%;
            box-sizing: border-box;
        }
    </style>
    <script>
        window.onload = function() {
            var childCheckboxes = document.querySelectorAll('.child-checkbox');
            var parentCheckboxes = document.querySelectorAll('.parent-checkbox');
            // 初始化所有父级复选框的状态
            // 为一级菜单的复选框添加 change 事件监听器
            parentCheckboxes.forEach(function(parentCheckbox) {
                // 获取当前父级的所有子级复选框
                var childrenOfParent = document.querySelectorAll('[data-parent-id="' + parentCheckbox.dataset.parentId + '"].child-checkbox');
                // 检查所有子级复选框是否被选中
                var allChildrenChecked = Array.from(childrenOfParent).every(child => child.checked);
                // 根据子级状态设置父级状态
                parentCheckbox.checked = allChildrenChecked;
                parentCheckbox.addEventListener('change', function(event) {
                    var isChecked = event.target.checked;
                    var childrenOfParent = document.querySelectorAll('[data-parent-id="' + event.target.dataset.parentId + '"].child-checkbox');
                    childrenOfParent.forEach(function(childCheckbox) {
                        childCheckbox.checked = isChecked;
                    });
                    updateParentCheckboxState(parentCheckbox);
                });
            });

            // 为二级菜单的复选框添加 change 事件监听器
            childCheckboxes.forEach(function(childCheckbox) {
                childCheckbox.addEventListener('change', function(event) {
                    var correspondingParentCheckbox = document.querySelector('[data-parent-id="' + event.target.dataset.parentId + '"].parent-checkbox');
                    var checkedChildren = document.querySelectorAll('[data-parent-id="' + event.target.dataset.parentId + '"].child-checkbox:checked');
                    // 更新当前变化的父菜单的状态
                    correspondingParentCheckbox.checked = checkedChildren.length > 0;
                    // 更新所有一级菜单状态
                    updateAllParentCheckboxes();
                });
            });
            // 更新所有一级菜单状态函数
            function updateAllParentCheckboxes() {
                parentCheckboxes.forEach(function(parentCheckbox) {
                    updateParentCheckboxState(parentCheckbox);
                });
            }
            // 更新父菜单状态函数（由于我们在每次子菜单改变时都调用了updateAllParentCheckboxes，此函数可以不用调用）
            function updateParentCheckboxState(parentCheckbox) {
                var childrenOfParent = document.querySelectorAll('[data-parent-id="' + parentCheckbox.dataset.parentId + '"].child-checkbox');
                var allChildrenChecked = Array.from(childrenOfParent).every(child => child.checked);
                parentCheckbox.checked = allChildrenChecked;
            }
        };
    </script>
</head>

<body>
    <div class="container">
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method="post">
            <input type="hidden" name="FormID" value="<?php echo htmlspecialchars($_SESSION['FormID'], ENT_QUOTES, 'UTF-8'); ?>" />
            <input type="hidden" name="JustSelectedACustomer" value="Yes" />
            <input type="hidden" name="params" value=<?php echo $role_id ?>>
            <!-- 添加确认和取消按钮 -->
            <div class="button-container">
                <button type="submit" name="sure" class="confirm-button">确认</button>
                <button type="submit" name="cancel" class="cancel-button">取消</button>
            </div>
            <table>
                <tbody>
                    <?php foreach ($modules as $module => $permissionNames) : ?>
                        <tr>
                            <td>
                                <!-- 添加class="parent-checkbox"和data-parent-id属性 -->
                                <label for="module_<?php echo htmlspecialchars($module); ?>">
                                    <input type="checkbox" id="module_<?php echo htmlspecialchars($module); ?>" name="module[]" value="<?php echo htmlspecialchars($module); ?>" class="parent-checkbox" data-parent-id="<?php echo htmlspecialchars($module); ?>">
                                    <?php echo htmlspecialchars($module); ?>
                                </label>
                            </td>
                            <td class="second-menu-column">
                                <?php foreach ($permissionNames as $permission) : ?>
                                    <div class="menu-item">
                                        <!-- 添加class="child-checkbox"和data-parent-id属性 -->
                                        <label for="permission_<?php echo htmlspecialchars($permission['permissions_id']); ?>">
                                            <input type="checkbox" id="permission_<?php echo htmlspecialchars($permission['permissions_id']); ?>" name="permissions_id[]" value="<?php echo htmlspecialchars($permission['permissions_id']); ?>" class="child-checkbox" data-parent-id="<?php echo htmlspecialchars($module); ?>" <?php if (in_array($permission['permissions_id'], $permissions_ids)) echo 'checked'; ?>>
                                            <?php echo htmlspecialchars($permission['permission_name']); ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </form>
    </div>
</body>


</html>