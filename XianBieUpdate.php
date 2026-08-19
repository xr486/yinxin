<?php
 ob_start();
include('includes/session.inc');
$Title = _('线别修改');
$ViewTopic = '线别修改';
$BookMark = '线别修改';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

unset($result);

if(isset($_GET['id'])){
    $sql="select * from wip_lines where line_id='".$_GET['id']."'";
    $result = DB_query($sql,$db);
    $data=mysqli_fetch_array($result);
}



//执行添加
if(isset($_POST['update'])){
    //id
    $line_id=$_POST['line_id'];
    //线别
    $line_code=$_POST['line_code'];
    //线别描述
    $line_desc=$_POST['line_desc'];
    if($line_code!=""){
        $sql="update wip_lines set line_code='".$line_code."',line_desc='".$line_desc."'
        where line_id='".$line_id."'";
        $result = DB_query($sql, $db);
         if($result){
             prnMsg(_('线别修改成功') ,'success');
         }else{
             prnMsg(_('线别修改失败') ,'error');
         }
    }else{
        prnMsg(_('线别和线别描述不能为空') ,'error');
    }
}

?>

<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/magnifier.png" title="线别修改" alt="线别修改">线别修改</p>
<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST">
    <div>
        <input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
        <input type="hidden" name="line_id" value="<?php echo $data['line_id']?>">
            <div class="text-nav">
                <div class="text-nav-1 ">
                <div>线别：</div>
                <input type="text" name="line_code" value="<?php echo $data['line_code']?>">
                </div>
                <div class="text-nav-1 ">
                <div>线别描述：</div>
                <input type="text" name="line_desc" value="<?php echo $data['line_desc']?>">
                </div>
            </div>
            <div class="centre">
                <input type="submit" name="update" value="修改">
                <button><a href="xianbie.php" style="color:#fff;">返回</a></button>
            </div>
    </div>
</form>
<?php
include('includes/footer.inc');
?>


