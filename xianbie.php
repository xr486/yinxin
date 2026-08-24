<?php
 ob_start();
include('includes/session.inc');
$Title = _('线别维护');
$ViewTopic = '线别维护';
$BookMark = '线别维护';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

unset($result);
//执行删除
if(isset($_GET['id'])){
    $sql="delete from wip_lines where line_id='".$_GET['id']."'";
    $result = DB_query($sql,$db);
}
//执行查询
$data=array();
if(isset($_POST['search'])){
    $line_code=$_POST['line_code'];
    $line_desc=$_POST['line_desc'];
    $sql="select line_id,line_code,line_desc from wip_lines";
    if($line_code!=""){
        $sql.=" where line_code like '%".$line_code."%'";
    }
    if($line_desc!=""){
        $sql.=" where line_desc like '%".$line_desc."%'";
    }
    $result = DB_query($sql, $db);
    if (DB_num_rows($result) == 0) {
    unset($result);
    prnMsg(_('找不到线别，请重新输入条件查询！'), 'error');die;
    }
    $data=mysqli_fetch_all($result,MYSQL_ASSOC);
}
?>

<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/magnifier.png" title="线别维护" alt="线别维护">线别维护</p>
<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST">
    <div>
        <input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
            <div class="text-nav">
                <div class="text-nav-1 ">
                <div>线别：</div>
                <input type="text" name="line_code">
                </div>
                <div class="text-nav-1 ">
                <div>线别描述：</div>
                <input type="text" name="line_desc">
                </div>
            </div>
            <div class="centre">
                <input type="submit" name="search" value="查询">&nbsp;&nbsp;
                <button><a href="XianBieAdd.php" style="color:#fff;">新增线别</a></button>
            </div>

            <table cellpadding="2" class="selection">
                <?php
                if(!empty($data)):
                echo   '<tr>
                        <th class="ascending" width = "150" >线别</th>
                        <th class="ascending" width = "200" >线别描述</th>
                        <th class="ascending" width = "50" >编辑</th>
                        <th class="ascending" width = "50" >删除</th>
                        </tr>';
                endif;
                ?>
                <?php foreach ($data as $k => $v): ?>
                <tr>
                    <td><?php echo $v['line_code'];?></td>
                    <td><?php echo $v['line_desc'];?></td>
                    <td><a href="XianBieUpdate.php?id=<?php echo $v['line_id']?>">edit</a></td>
                    <td><a href="xianbie.php?id=<?php echo $v['line_id']?>">delete</a></td>
                </tr>
                <?php endforeach ?>
            </table>
    </div>
</form>
<?php
include('includes/footer.inc');
?>
