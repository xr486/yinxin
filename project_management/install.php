<?php
$PathPrefix = '../';
include('../includes/session.inc');

if (!isset($_SESSION['UserID']) || $_SESSION['UserID'] !== 'admin') {
    http_response_code(403);
    exit('Only the administrator can install the project management schema.');
}

$sql = file_get_contents(__DIR__ . '/schema.sql');
if (!mysqli_multi_query($db, $sql)) {
    http_response_code(500);
    exit('Installation failed: ' . htmlspecialchars(mysqli_error($db), ENT_QUOTES, 'UTF-8'));
}
do {
    if ($result = mysqli_store_result($db)) {
        mysqli_free_result($result);
    }
} while (mysqli_more_results($db) && mysqli_next_result($db));

header('Location: ../ProjectManagement.php?installed=1');
exit;
