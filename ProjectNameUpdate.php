<?php
 ob_start();
include('includes/session.inc');
$Title = _('项目名称修改');
$ViewTopic = '项目名称修改';
$BookMark = '项目名称修改';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

unset($result);

if(isset($_GET['id'])){
    $sql="select * from project_name where type_id='".$_GET['id']."'";
    $result = DB_query($sql,$db);
    $data=mysqli_fetch_array($result);
}



//执行添加
if(isset($_POST['update'])){
    //id
    $type_id=$_POST['type_id'];
    //项目分类
    $project_type=$_POST['project_type'];
    //线别描述
    $project_name=$_POST['project_name'];
    if($project_name!=""){
        $sql="update project_name set project_type='".$project_type."',project_name='".$project_name."'
        where type_id='".$type_id."'";
        $result = DB_query($sql, $db);
         if($result){
             prnMsg(_('项目名称修改成功') ,'success');
         }else{
             prnMsg(_('项目名称修改失败') ,'error');
         }
    }else{
        prnMsg(_('项目名称不能为空') ,'error');
    }
}

?>

<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/magnifier.png" title="项目名称修改" alt="项目名称修改">项目名称修改</p>
<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST">
    <div>
        <input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
        <input type="hidden" name="type_id" value="<?php echo $data['type_id']?>">
            <div class="text-nav2">
                <div class="text-nav-1 ">
                <div>项目分类：</div>
                <input type="text" name="project_type" value="<?php echo $data['project_type']?>">
                </div>
                <div class="text-nav-2 ">
                <div>项目名称：</div>
                <input type="text" name="project_name" value="<?php echo $data['project_name']?>">
                </div>
            </div>
            <div class="centre">
                <input type="submit" name="update" value="修改">
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


