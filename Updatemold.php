<?php
include('includes/session.inc');
$Title = _('修改模具属性');

$ViewTopic= '修改模具属性';
$BookMark = '修改模具属性';

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

if (isset($_GET['work_id'])) {
    $ItemID = $_GET['work_id'];
}else if(isset($_POST['work_id'])){
    $ItemID = $_POST['work_id'];
}

if (!isset($ItemID)) {
    header('Location: Searchmold.php');
}

$uploadflag = 1;


if(isset($_POST['yes'])){
        $deleteSql = "update wip_mold set  flag=0  where work_id='". $_POST['work_id']."'";
        DB_query($deleteSql,$db);
        unset($_POST['yes']);
    echo '<div class="centre"><a href="'.$RootPath.'/Searchmold.php">返回查找模具</a></div>';
    prnMsg(_('修改成功！！！！'), 'success');
        include('includes/footer.inc');
        exit;
}
if(isset($_POST['no'])){
        $deleteSql = "update wip_mold set  flag=1  where work_id='". $_POST['work_id']."'";
        DB_query($deleteSql,$db);
        unset($_POST['no']);
    echo '<div class="centre"><a href="'.$RootPath.'/Searchmold.php">返回查找模具</a></div>';
    prnMsg(_('修改成功！！！！'), 'success');
    include('includes/footer.inc');
    exit;
}



if (isset($_POST['Save'])) {
    if (!empty($_FILES["Pic"]["tmp_name"])) {
        if ((($_FILES["Pic"]["type"] == "image/gif")
                || ($_FILES["Pic"]["type"] == "image/jpeg")
                || ($_FILES["Pic"]["type"] == "image/pjpeg"))
            && ($_FILES["Pic"]["size"] < 20*1024*1024)){
            if ($_FILES["Pic"]["error"] > 0){
                $msg = "错误: " . $_FILES["Pic"]["error"];
                prnMsg( $msg, 'error');
                $uploadflag = 2;
            }
        }
        else
        {
            $msg = "系统只支持gif,jpeg,pjpeg图片";
            prnMsg( $msg, 'error');
            $uploadflag = 2;
        }
        $_POST['PicPath'] = "itempic/" . $_POST['ItemNo'] .".jpg";
    }else{
        $sql = "select pic_path from wip_mold where work_id ='".$ItemID."' ";
        $result = DB_query($sql,$db);
        while ($v = DB_fetch_array($result)) {
            $_POST['PicPath'] = $v['pic_path'];
        }
    }

    if ($uploadflag == 1) {
        move_uploaded_file($_FILES["Pic"]["tmp_name"],"itempic/" . $_POST['ItemNo'] .".jpg");
        if (empty($_POST['mold_class'])) {
            $_POST['mold_class'] =0;
        }
        if (empty($_POST['mould_number'])) {
            $_POST['mould_number'] =0;
        }
        if (empty($_POST['product_map_number'])) {
            $_POST['product_map_number'] =0;
        }
        if (empty($_POST['mold_material'])) {
            $_POST['mold_material'] =0;
        }
        if (empty($_POST['version_drawing'])) {
            $_POST['version_drawing'] =0;
        }
        if (empty($_POST['die_date'])) {
            $_POST['die_date'] =0;
        }
        if (empty($_POST['mould_length'])) {
            $_POST['mould_length'] =0;
        }
        if (empty($_POST['mould_width'])) {
            $_POST['mould_width'] =0;
        }
        if (empty($_POST['mould_height'])) {
            $_POST['mould_height'] =0;
        }
        if (empty($_POST['mold_core_box_number'])) {
            $_POST['mold_core_box_number'] =0;
        }

        if (empty($_POST['mould_factory_no'])) {
            $_POST['mould_factory_no'] =0;
        }
        if (empty($_POST['mould_ownership'])) {
            $_POST['mould_ownership'] =0;
        }
        if (empty($_POST['finished_material'])) {
            $_POST['finished_material'] =0;
        }
        if (empty($_POST['modeling_method'])) {
            $_POST['modeling_method'] =0;
        }
        if (empty($_POST['note_taker'])) {
            $_POST['note_taker'] =0;
        }
        if (empty($_POST['number_of_use'])) {
            $_POST['number_of_use'] =0;
        }
        if (empty($_POST['product_number'])) {
            $_POST['product_number'] =0;
        }
        $date=strtotime($_POST['die_date']);
        $time = time();
        $sql = "update wip_mold
                                set mold_class='".$_POST['mold_class']."',
                                    mould_number='".$_POST['mould_number']."',
                                    version_drawing='".$_POST['version_drawing']."',
                                    product_map_number='".$_POST['product_map_number']."',
                                    mold_material='".$_POST['mold_material']."',
                                    die_date='".$date."',
                                    mould_length='".$_POST['mould_length']."',
                                    mould_width='".$_POST['mould_width']."',
									mould_height='".$_POST['mould_height']."',
				                    mold_core_box_number='".$_POST['mold_core_box_number']."',
                                    mould_factory_no='".$_POST['mould_factory_no']."',
                                    mould_ownership='".$_POST['mould_ownership']."',
                                    finished_material='".$_POST['finished_material']."',
                                    modeling_method='".$_POST['modeling_method']."',
                                    note_taker='".$_POST['note_taker']."',
                                    remarks='".$_POST['remarks']."',
                                    number_of_use='".$_POST['number_of_use']."',
                                    product_number='".$_POST['product_number']."',
                                    last_update_date='".$time."',
                                    last_updated_by='".$_SESSION['UserID']."',
                                    pic_path='".$_POST['PicPath']."'
                              where work_id = '".$ItemID."' ";
        $result = DB_query($sql,$db);

        prnMsg( _('料号更新成功！'), 'success');
        echo '<div class="centre"><a href="'.$RootPath.'/Searchmold.php">返回查找模具</a></div>';
        include('includes/footer.inc');
        exit;
    }

}
if($ItemID==''||$ItemID==null){
    $ItemID=$_POST['work_id'];
}
$sql = "select
	mold_class,
	mould_number,
	product_map_number,
	mold_material,
	version_drawing,
	die_date,
	mould_length,
	mould_width,
	mould_height,
	mold_core_box_number,
	mould_factory_no,
	mould_ownership,
	finished_material,
	modeling_method,
	note_taker,
	remarks,number_of_use,
	product_number,
	pic_path,flag from wip_mold where work_id ='".$ItemID."' ";

//$sql = $sql."  order by item_no";
$result = DB_query($sql,$db);
while ($v = DB_fetch_array($result)) {
    $_POST['mold_class'] = $v['mold_class'];
    $_POST['mould_number'] = $v['mould_number'];
    $_POST['product_map_number'] = $v['product_map_number'];
    $_POST['mold_material'] = $v['mold_material'];
    $_POST['version_drawing'] = $v['version_drawing'];
    $_POST['die_date'] = $v['die_date'];
    $_POST['mould_length'] = $v['mould_length'];
    $_POST['mould_width'] = $v['mould_width'];
    $_POST['mould_height'] = $v['mould_height'];
    $_POST['mold_core_box_number'] = $v['mold_core_box_number'];
    $_POST['mould_factory_no'] = $v['mould_factory_no'];
    $_POST['mould_ownership'] = $v['mould_ownership'];
    $_POST['finished_material'] = $v['finished_material'];
    $_POST['modeling_method'] = $v['modeling_method'];
    $_POST['note_taker'] = $v['note_taker'];
    $_POST['remarks'] = $v['remarks'];
    $_POST['number_of_use'] = $v['number_of_use'];
    $_POST['product_number'] = $v['product_number'];
    $_POST['pic_path'] = $v['pic_path'];
    $_POST['flag'] = $v['flag'];
}
if(!isset($_POST['Delete'])){
    ?>

    <div class="centre"><a href="<?=$RootPath?>/Searchmold.php">返回查找模具</a></div>
    <p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/maintenance.png" title="修改模具属性" alt="暂未上传图片">修改模具属性</p>
    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST" enctype="multipart/form-data">
        <div>
            <input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
            <br>
            <table class="selection">
                <tr>
                    <td>模具类别：</td>
                    <td>
                        <select name="mold_class" >
                            <?php
                            if($_POST['mold_class']=='finished_mold'){
                                ?>
                                <option selected="selected"  value="finished_mold">成品模具</option>

                                <?php
                            }else{
                                ?>
                                <option value="mud_core_mold"  selected="selected">泥芯模具</option>
                                <?php
                            }?>
                        </select>
                    </td>

                    <td>模具图号：</td>
                    <td><input type="text" readonly="readonlyA"  name="mould_number" value="<?=$_POST['mould_number'] ?>" ></td>

                    <td>产品图号：</td>
                    <td><input type="text"   name="product_map_number" value="<?=$_POST['product_map_number'] ?>"  ></td>
                </tr>

                <tr>

                    <td>模具材质</td>
                    <td><input type="text" name="mold_material" value="<?=$_POST['mold_material'] ?>"  ></td>
                    <td>图纸版本</td>
                    <td><input type="text" name="version_drawing" value="<?=$_POST['version_drawing'] ?>"  ></td>
                    <td>来模日期：</td>
                    <td><input type="text" onfocus="WdatePicker()"  name="die_date" value="<?=date('Y-m-d',$_POST['die_date']) ?>" /></td>
                </tr>
                <tr>
                    <td>模具长：</td>
                    <td><input type="text" name="mould_length" value="<?=$_POST['mould_length'] ?>" ></td>
                    <td>模具宽：</td>
                    <td><input type="text" name="mould_width" value="<?=$_POST['mould_width'] ?>" ></td>
                    <td>模具高：</td>
                    <td><input type="text" name="mould_height" value="<?=$_POST['mould_height'] ?>"  ></td>
                    </td>
                </tr>

                <tr>
                    <td>模具芯盒数：</td>
                    <td><input type="text" name="mold_core_box_number" value="<?=$_POST['mold_core_box_number'] ?>"  ></td>

                    <td>模具本厂编号：</td>
                    <td><input type="text" name="mould_factory_no" value="<?=$_POST['mould_factory_no'] ?>"  ></td>

                    <td>模具所有权:</td>
                    <td><input type="text" name="mould_ownership" value="<?=$_POST['mould_ownership'] ?>"  ></td>

                </tr>


                <tr>

                    <td>成品材质</td>
                    <td><input type="text" name="finished_material" value="<?=$_POST['finished_material'] ?>"  ></td>
                    <td>造型方式</td>
                    <td><input type="text" name="modeling_method" value="<?=$_POST['modeling_method'] ?>"  ></td>


                    <td>记录人：</td>
                    <td><input type="text" name="note_taker" value="<?=$_POST['note_taker'] ?>"  >
                        <input type="hidden" name="work_id" value="<?=$ItemID ?>">
                    </td>
                </tr>

                <tr>
                    <td>模具使用次数：</td>
                    <td><input type="text" name="number_of_use" value="<?=$_POST['number_of_use'] ?>"  ></td>
                    <td>对应料号：</td>
                    <td><input type="text"  name="product_number" value="<?=$_POST['product_number'] ?>"  ></td>
                    <td>上传图片：</td>
                    <td><input type="file" name="Pic" multiple="multiple">
                    </td>
                </tr>
                <tr>
                    <td>备注：</td>
                    <td colspan="3"><input type="text" value="<?=$_POST['remarks'] ?>" size="60" name="remarks" ></td>
                    <td>是否有效</td>
                    <td >
                        <div style="background-color: red;color: white;width:40px;">
                        <?= $_POST['flag']==1 ? '&nbsp;有效':'&nbsp;失效' ?></div>
                    </td>
                </tr>
                <tr>

                </tr>
            </table>

            <table>

                <td>模具图片：</td>
                <?php
                $pic=explode(',',$_POST['pic_path']);
                $picsize=sizeof($pic);
                if($picsize>1){
                    for($a=0;$a<$picsize-1;$a++){
                        ?>
                        <td><div style="width:100px; height:100px;"><img src="<?=$pic[$a]?>" alt="料号图片" width="100%" height="100%"></div></td>
                        <?php
                    }

                }else{
                    ?>
                    <td><div style="width:100px; height:100px;"><img src="<?=$_POST['pic_path']?>" alt="料号图片" width="100%" height="100%"></div></td>
                    <?php
                }?>

            </table>
            <div class="centre">
                <input type="submit" name="Save" value="保存" >
    <?php
    if($_POST['flag']==1){
        ?>
        <input id="delete" type="submit" name="yes" value="失效" >
        <?php
    }else{
        ?>
        <input id="delete" type="submit" name="no" value="有效" >
        <?php
    }
    ?>
    <script type="text/javascript">
        window.onload = function(){
            document.getElementById("delete").onclick = function(){
                var result = confirm("确认执行该操作么？");
                if(result == true){
                    return true;
                }
                return false;
            }
        }
    </script>
    <?php

    include('includes/footer.inc');
}
?>