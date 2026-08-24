<?php
/* Project management uses one module-level permission for all of its views. */
$projectManagementMenuAllowed = false;
$projectManagementPermissionSql = "SELECT 1 FROM user_power WHERE user_id='" .
    $_SESSION['UserID'] . "' AND function_name='项目管理' AND use_flag=1";
$projectManagementPermissionResult = DB_query($projectManagementPermissionSql, $db);
if ($projectManagementPermissionResult && DB_num_rows($projectManagementPermissionResult) > 0) {
    $projectManagementMenuAllowed = true;
}

if ($projectManagementMenuAllowed) {
    echo '<li class="two_li">
        <a href="javascript:;" class="two_lia">
            <img class="two_img" src="' . $RootPath . '/css/' . $Theme . '/images/jiaoyi.png" />
            ' . _('功能') . '
        </a>
        <ul class="titem three_ul"><br/>';

    $projectManagementCaptions = $MenuItems['project_management']['Transactions']['Caption'];
    $projectManagementUrls = $MenuItems['project_management']['Transactions']['URL'];
    foreach ($projectManagementCaptions as $projectManagementMenuIndex => $projectManagementCaption) {
        echo '<li class="three_li">
            <a class="three_lia" target="right" href="' . $RootPath .
            $projectManagementUrls[$projectManagementMenuIndex] . '">' .
            $projectManagementCaption . '</a>
        </li>';
    }

    echo '</ul></li><br/>';
}
