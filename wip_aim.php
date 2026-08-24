<?php
include('includes/session.inc');
$Title = '生产目标设定';
include('includes/header.inc');
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' .
    _('Search') . '" alt="" />' . ' ' . $Title . '</p>';
//查询数据
$sql = "select * from public_aim";
$result = DB_query($sql, $db);
$row = DB_fetch_array($result);
//修改
if ($_POST['Save']) {
    //数据获取
    $wip_rate = $_POST['wip_rate'];
    $good_rate = $_POST['good_rate'];
    $id = $_POST['id'];

    // SQL 更新语句，确保做好SQL注入防护
    $sql_update = "UPDATE public_aim 
        SET wip_rate = '" . $wip_rate . "', good_rate = '" . $good_rate . "' 
        WHERE id = '" . $id . "'";
    $result_update = DB_query($sql_update, $db);
    prnMsg(_('修改成功!!!'), 'success');
    header('Refresh: 1; URL=' . $_SERVER['PHP_SELF']);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        td input {
            width: 80px !important;
        }
    </style>
</head>

<body>
    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method="post">
        <input type="hidden" name="FormID" value="<?php echo htmlspecialchars($_SESSION['FormID'], ENT_QUOTES, 'UTF-8'); ?>" />
        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
        <table>
            <tr>
                <th bgcolor="#87CEFA" class="ascending">生产效率目标设定</th>
                <th bgcolor="#87CEFA" class="ascending">良率目标设定</th>
            </tr>
            <tr>
                <td><input type="text" name="wip_rate" value="<?php echo $row['wip_rate'] ?>">%</td>
                <td><input type="text" name="good_rate" value="<?php echo $row['good_rate'] ?>">%</td>
            </tr>
        </table>
        <!-- 按钮 -->
        <div class="centre">
            <input type="submit" name="Save" value="保存" />
        </div>
    </form>
</body>

</html>

<?php
include('includes/footer.inc');
?>