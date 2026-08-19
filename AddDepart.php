<?php

include ('includes/session.inc');
$Title = _('添加部门');

$ViewTopic = '添加部门';
$BookMark = '添加部门';
include ('includes/header.inc');
include ('includes/SQL_CommonFunctions.inc');
$uploadflag = 1;

if (isset($_POST['Save'])) {
    $sql = "SELECT count(*) FROM hr_departs
				WHERE depart_code = '" . $_POST['depart_code'] . "'
				";
    $result = DB_query($sql, $db);
    $myrow = DB_fetch_row($result);
    if ($myrow[0] > 0) {
        $uploadflag = 2;
        prnMsg('部门不能重命名,因为另一个具有相同名称已经存在', 'error');
    } else
        $time = time();
    $sql = "INSERT INTO hr_departs 
    (depart_code, 
    depart_name, 
    enable_flag, 
    creation_date, 
    created_by, 
    last_update_date, 
    last_updated_by) 
    VALUES
     ('" . $_POST['depart_code'] . "', 
     '" . $_POST['depart_name'] . "', 
      'Y', 
     '" . $time . "',
     '" . $_SESSION['UserID'] . "', 
     '" . $time . "', 
     '" . $_SESSION['UserID'] . "');";
$result = DB_query($sql, $db);

        prnMsg(_('部门建立成功！'), 'success');

        unset($_POST);


}

?>
<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/maintenance.png" title="添加部门" alt="添加部门">添加部门</p>
<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES,
'UTF-8'); ?>" method ="POST" enctype="multipart/form-data">
    <div>
        <input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
        <br>
        <table class="selection">
            <tr>
                <td>部门编号：</td>
                <td><input type="text" name="depart_code" value="<?= $_POST['depart_code'] ?>" required="required"></td>
            </tr>
            <tr>
                <td>部门名称</td>
                <td colspan="3"><input type="text"  size="30"  name="depart_name" value="<?= $_POST['depart_name'] ?>" required="required"></td>
            </tr>
            </table>
             </div>
  <div class="centre">
        <input type="submit" name="Save" value="保存" >
        
    </div>
</form>
<?php
include ('includes/footer.inc');
?>