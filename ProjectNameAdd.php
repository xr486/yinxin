<?php
 ob_start();
include('includes/session.inc');
$Title = _('项目名称添加');
$ViewTopic = '项目名称添加';
$BookMark = '项目名称添加';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

unset($result);
//执行添加
if(isset($_POST['add'])){
    //项目分类
    $project_type=$_POST['project_type'];
    //项目名称
    $project_name=$_POST['project_name'];
    //时间日期
    $last_update_date=time();
    $last_updated_by=$_SESSION['UserID'];
    $creation_date=time();
    $created_by=$_SESSION['UserID'];
    if($project_name!=""){
        $sql="insert into project_name(project_type,project_name)
                value('".$project_type."','".$project_name."')";
        $result = DB_query($sql, $db);
         if($result){
             prnMsg(_('项目名称添加成功') ,'success');
         }else{
             prnMsg(_('项目名称添加失败') ,'error');
         }
    }else{
        prnMsg(_('项目名称不能为空') ,'error');
    }
}

?>

<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/magnifier.png" title="项目名称添加" alt="项目名称添加">项目名称添加</p>
<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST">
    <div>
        <input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
            <div class="text-nav2">
                <div class="text-nav-1 ">
                <div>项目分类：</div>
                <input type="text" name="project_type">
                </div>
                <div class="text-nav-2 ">
                <div>项目名称：</div>
                <input type="text" name="project_name">
                </div>
            </div>
            <div class="centre">
                <input type="submit" name="add" value="添加">
                <input type="submit" name="return"  value="返回">
               
            </div>
    </div>
</form>
<?php
if (isset($_POST['return'])) {
    header('Location: project_name_type.php');
}
include('includes/footer.inc');
?>

