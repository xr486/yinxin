<?php
include ('includes/session.inc');
$Title = _('修改部门信息');

$ViewTopic = '修改部门信息';
$BookMark = '修改部门信息';

include ('includes/header.inc');
include ('includes/SQL_CommonFunctions.inc');

if (isset($_GET['depart_code'])) {
    $depart_code = $_GET['depart_code'];
} else
    if (isset($_POST['depart_code'])) {
        $depart_code = $_POST['depart_code'];
    }

if (!isset($depart_code)) {
    header('Location: SearchDept.php');
}
if (isset($_POST['Save'])) {

    if (empty($_POST['depart_code'])) {
        prnMsg(_('部门编号不可不填'), 'error');
    }
    if (empty($_POST['depart_name'])) {
        prnMsg(_('部门名称不可不填'), 'error');
    }
    $time = time();
      $sql = "update hr_departs 
           set depart_name='" . $_POST['depart_name'] . "',
		   manager_person='" . $_POST['manager_person'] . "',
		   enable_flag='" . $_POST['enable_flag'] . "',
		   last_updated_by='" . $_SESSION['UserID'] . "'     
		   where depart_code='" . $_POST['depart_code'] .
            "' ";

    $result = DB_query($sql, $db);

    prnMsg(_('部门更新成功！'), 'success');
}


$sql = "select depart_code,depart_name,creation_date,created_by,enable_flag,manager_person  from hr_departs where depart_code ='" .
    $depart_code . "' ";
$sql = $sql . "  order by depart_code";
$result = DB_query($sql, $db);
while ($v = DB_fetch_array($result)) {
    $_POST['depart_code'] = $v['depart_code'];
    $_POST['depart_name'] = $v['depart_name'];
    $_POST['creation_date'] = $v['creation_date'];
    $_POST['created_by'] = $v['created_by'];
    $_POST['enable_flag'] = $v['enable_flag'];
 $_POST['manager_person'] = $v['manager_person'];
}

?>
<div class="centre"><a href="<?= $RootPath ?>/SearchDept.php">返回查找部门</a></div>
<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/maintenance.png" title="修改部门信息" alt="修改部门信息">修改部门信息</p>
<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES,
'UTF-8'); ?>" method ="POST" enctype="multipart/form-data">
<div>
	<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
<br>
<table class="selection">
<div class="text-nav">
<div class="text-nav-1"><div>部门编号：</div>
<input type="text" readonly="readonly" value="<?= $_POST['depart_code'] ?>" />
			<input type="hidden" name="depart_code" value="<?= $_POST['depart_code'] ?>"  > 
		</div>
		<div class="text-nav-1 required"><div>部门名称：</div>
		<input type="text" size="40" name="depart_name" value="<?= $_POST['depart_name'] ?>" required="required"></div>
         <div class="text-nav-1 required"><div>部门主管：</div>
		<input type="text" size="40" name="manager_person" value="<?= $_POST['manager_person'] ?>" required="required"></div>
		<div class="text-nav-1"><div>创建时间：</div>
		<input type="text" readonly="readonly" value="<?= date('Y-m-d H:i:s', $_POST['creation_date']) ?>" />
		</div>
		<div class="text-nav-1"><div>创建人：</div>
		<input type="text" readonly="readonly" value="<?=  $_POST['created_by']  ?>" /></div>
		 
		<div class="text-nav-1"><div>失效：</div>
			    <?php
			if ($_POST['enable_flag'] == 'N') {
			    ?>
			                        <input type="radio" name="enable_flag" value='Y' >是
			                        <input type="radio" name="enable_flag" value='N' checked=checked>否
			                        <?php
			                    } else {
			                        ?>
			                        <input type="radio" name="enable_flag" value='Y' checked=checked>是
			                        <input type="radio" name="enable_flag" value='N'>否
			                        <?php
			                    }
			                    ?>
			                </div>
                
            </div>

</table>
</div>
<div class="centre">
	<input type="submit" name="Save" value="保存" >
</div>
</form>
<?php
include ('includes/footer.inc');
?>