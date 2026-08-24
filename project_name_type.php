<?php
 ob_start();
include('includes/session.inc');
$Title = _('项目名称维护');
$ViewTopic = '项目名称维护';
$BookMark = '项目名称维护';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

unset($result);
//执行删除
if(isset($_GET['delete'])){

	$sql= "SELECT COUNT(*) FROM sf_item_no WHERE project_name = '".$_GET['project_name']."'";
		$result = DB_query($sql,$db);
		$myrow = DB_fetch_row($result);
		if ($myrow[0]>0) {
			prnMsg( _('不能删除这个项目名称，因为有属于该项目名称'),'warn');
			
		} else {
			$sql="delete from project_name where type_id='".$_GET['id']."'";
			$result = DB_query($sql,$db);
			prnMsg( $OldMeasureName . ' ' . _('项目名称删除成功') . '!','success');
		}
   
}
//执行查询
$data=array();
$sql="select type_id,project_type,project_name from project_name";
$sql.=" order by project_type";
$result = DB_query($sql, $db);
$data=mysqli_fetch_all($result,MYSQL_ASSOC);

if(isset($_POST['search'])){
    $project_name=$_POST['project_name'];
    $project_type=$_POST['project_type'];
    $sql="select type_id,project_type,project_name from project_name";
    if($project_name!=""){
        $sql.=" where project_name like '%".$project_name."%'";
    }
    if($project_type!=""){
        $sql.=" where project_type like '%".$project_type."%'";
    }
	$sql.=" order by project_type";

    $result = DB_query($sql, $db);
    if (DB_num_rows($result) == 0) {
    // unset($result);
    prnMsg(_('找不到项目名称，请重新输入条件查询！'), 'error');
    }
    $data=mysqli_fetch_all($result,MYSQL_ASSOC);
}
?>

<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/magnifier.png" title="项目名称维护" alt="项目名称维护">项目名称维护</p>
<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST">
    <div>
        <input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
            <div class="text-nav2">
                <div class="text-nav-1 ">
                <div>项目分类：</div>
                <input type="text" name="project_type" value="<?=$_POST['project_type'];?>">
                </div>
                <div class="text-nav-2 ">
                <div>项目名称：</div>
                <input type="text" name="project_name" value="<?=$_POST['project_name'];?>">
                </div>
            </div>
            <div class="centre">
                <input type="submit" name="search" value="查询">&nbsp;&nbsp;
				<input type="submit" name="add_new" value="新增">
            </div>

            <table cellpadding="2" class="selection">
                <?php
                if(!empty($data)):
                echo   '<tr>
                        <th class="ascending" width = "150" >项目分类</th>
                        <th class="ascending" width = "200" >项目名称</th>
                        <th class="ascending" width = "50" >编辑</th>
                        <th class="ascending" width = "50" >删除</th>
                        </tr>';
                endif;
                ?>
                <?php foreach ($data as $k => $v): ?>
                <tr>
                    <td><?php echo $v['project_type'];?></td>
                    <td><?php echo $v['project_name'];?></td>
                    <td><a href="ProjectNameUpdate.php?id=<?php echo $v['type_id']?>">edit</a></td>
					<?php
					 echo '<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?id=' .$v['type_id'] .'&project_name=' .$v['project_name'] . '&amp;delete=1" onclick="return confirm(\'' . _('是否要删除这个项目名称?') . '\');">' . _('delete')  . '</a></td>';
					?>
                   
                </tr>
                <?php endforeach ?>
            </table>
    </div>
</form>
<?php
if (isset($_POST['add_new'])) {
    header('Location: ProjectNameAdd.php');
}
include('includes/footer.inc');
?>
