<?php
include('includes/session.inc');
$Title = _('新增半成品泥芯');

$ViewTopic = '新增半成品泥芯';
$BookMark = '新增半成品泥芯';

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

$uploadflag = 1;

echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
			"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';

echo '<html xmlns="http://www.w3.org/1999/xhtml"><head><title>' . $Title . '</title>';
echo '<link rel="shortcut icon" href="'. $RootPath.'/favicon.ico" />';
echo '<link rel="icon" href="' . $RootPath.'/favicon.ico" />';
if ($StrictXHTML) {
    echo '<meta http-equiv="Content-Type" content="application/xhtml+xml; charset=utf-8" />';
} else {
    echo '<meta http-equiv="Content-Type" content="application/html; charset=utf-8" />';
}
echo '<link href="' . $RootPath . '/css/'. $_SESSION['Theme'] .'/default.css" rel="stylesheet" type="text/css" />';
echo '<script type="text/javascript" src = "'.$RootPath.'/javascript/jquery-1.7.2.min.js"></script>';
echo '<script type="text/javascript" src="/JXC/statics/base/js/iframes.js"></script>';
echo '<script type="text/javascript" src = "'.$RootPath.'/javascripts/MiscFunctions.js"></script>';
echo '<script src="/JXC/javascript/lhgdialog.min.js?self=true&skin=chrome"></script>';
echo '<script type="text/javascript" src = "'.$RootPath.'/javascripts/WdatePicker.js"></script>';
echo '</head>';
//进行表单提交处理
if (isset($_POST['Save'])) {
    // if (!empty($_FILES["img_path"]["tmp_name"])) {
    //     if ((($_FILES["img_path"]["type"] == "image/gif") || ($_FILES["img_path"]["type"] == "image/jpeg") || ($_FILES["img_path"]["type"] == "image/pjpeg")) && ($_FILES["img_path"]["size"] < 20 * 1024 * 1024)) {
    //         if ($_FILES["img_path"]["error"] > 0) {
    //             $msg = "错误: " . $_FILES["img_path"]["error"];
    //             prnMsg($msg, 'error');
    //             $uploadflag = 2;
    //         }
    //     } else {
    //         $msg = "系统只支持gif,jpeg,pjpeg图片";
    //         prnMsg($msg, 'error');
    //         $uploadflag = 2;
    //     }
    // }
    $sql = "SELECT count(*) FROM wip_loamcore
                WHERE lcid = '" . $_POST['lcid'] . "'
                ";
    $result = DB_query($sql,$db);
    $myrow = DB_fetch_row($result);
    if ($myrow[0] > 0) {
        $uploadflag = 2;
        prnMsg('料号不能重命名,因为另一个具有相同名称已经存在', 'error');
    }
    if ($uploadflag == 1) {
        //move_uploaded_file($_FILES["img_id"]["tmp_name"], "itempic/" . $_FILES["Pic"]["name"]);
        // move_uploaded_file($_FILES["img_path"]["tmp_name"],"itempic/" . $_POST['p_id'] .".jpg");
        //$_POST['PicPath'] = "itempic/" . $_POST['p_id'] .".jpg";         
        $time = time();
        if (empty($_POST['lcname'])) {
            $_POST['lcname'] ='';
        }
        if (empty($_POST['paint'])) {
            $_POST['paint']=0;
        }
        if (empty($_POST['cprice'])) {
            $_POST['cprice'] =0;
        }
        if (empty($_POST['trueweight'])) {
            $_POST['trueweight'] =0;
        }
        if (empty($_POST['weight'])) {
            $_POST['weight'] =0;
        }
        if (empty($_POST['sandboxid'])) {
            $_POST['sandboxid'] =0;
        }
        if (empty($_POST['workorder'])) {
            $_POST['workorder'] =0;
        }
        if (empty($_POST['p_id'])) {
            $_POST['p_id'] ='';
        }
        $sql = "INSERT INTO wip_loamcore (
        lcid,
        lcname,
        paint,
        cprice,
        trueweight,
        weight,
        sandboxid,
        workorder,
        p_id,
        required_count,
        flag
)
VALUES
    (
        '" . $_POST['lcid'] . "',
        '" . $_POST['lcname'] . "',
        '" . $_POST['paint'] . "',
        " . $_POST['cprice'] . ",
        " . $_POST['trueweight'] . ",
         " . $_POST['weight'] . ",
       '" . $_POST['sandboxid']. "',
         " . $_POST['workorder'] . ",
        '" . $_POST['p_id'] . "',
        '" . $_POST['required_count'] . "',
        '".$_POST['flag']."'

    )";
        $result = DB_query($sql, $db);

        prnMsg(_('半成品泥芯建立成功！'), 'success');

        unset($_POST);
    }
}

?>


    <p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/maintenance.png" title="新增半成品泥芯" alt="新增半成品泥芯">新增半成品泥芯</p>
    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST" enctype="multipart/form-data">
        <div>
            <input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
            <br>
            <table class="selection">
                <tr>
                    <td>半成品泥芯编号</td>
                    <td><input type="text" name="lcid" required="required"></td>

                    <td>名称</td>
                    <td><input type="text" name="lcname" required="required"></td>

                    <td>对应的成品料号</td>
                    <td>
                        <input type="text" id="p_id" name="p_id" readonly="readonly" value="<?=$_GET['stockid']?>" ></td>
                </tr>
                <tr>
                    <td>单个成品需要的数量</td>
                    <td>
                        <input type="text" id="required_count" required="required" class="number" name="required_count" >
                    </td>
                    <td>所用沙箱</td>
                    <td><input type="text" id="sandboxid"  name="sandboxid">
                        <a class="btn btn-info btn-xs" id="selectSandboxid" hfre="###" title="选择沙箱号">选择</a></td>
                    </td>

                    <td>铸件毛重</td>
                    <td>
                        <input type="text" class="number"  name="weight" >
                    </td>
                </tr>
                <tr>

                    <td>油漆</td>
                    <td><input type="text" name="paint" ></td>
                    <td>制芯单价</td>
                    <td><input type="text" class="number"  name="cprice" ></td>
                    <td>泥芯重量</td>
                    <td><input type="text" class="number"  name="trueweight" ></td>
                </tr>
                <tr>
                    <td>是否需要开工单</td>
                    <td><input type="radio" name="workorder" value="true" checked=checked>是
                        <input type="radio" name="workorder" value="false">否
                    </td>
                    <td>是否有效</td>
                    <td><input type="radio" name="flag" value="1" checked=checked>是
                        <input type="radio" name="flag" value="0">否
                    </td>
                </tr>

            </table>
            <script type="text/javascript">
                $(document).ready(function(){

                    $('#selectSandboxid').click(function(){
                        $('#selectSandboxid').dialog("open");
                    });
                    $('#selectSandboxid').dialog({
                        enable:true,
                        title:'选择沙箱',
                        width: '950px',
                        height: '470px',
                        content:'url:btnSearchSandBox.php?fwValue=<?=$i?>&cat=buliao',
                        init:function(){
                            this.content.document.getElementById('cat').value = 'buliao';
                            this.content.document.getElementById('fwValue').value = '<?=$i?>';
                        }
                    });
                    $('#selectPId').dialog({
                        enable:true,
                        title:'选择成品',
                        width: '950px',
                        height: '470px',
                        content:'url:btnSearchPId.php?fwValue=<?=$i?>&cat=buliao',
                        init:function(){
                            this.content.document.getElementById('cat').value = 'buliao';
                            this.content.document.getElementById('fwValue').value = '<?=$i?>';
                        }
                    });

                });
            </script>
            <div class="centre">
                <input type="submit" name="Save" value="保存" >
            </div>
    </form>
<?php
include('includes/footer.inc');
?>