<?php
include('includes/session.inc');
$Title = _('修改砂箱');

$ViewTopic= '修改砂箱';
$BookMark = '修改砂箱';

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

if (isset($_GET['ItemID'])) {
    $ItemID = $_GET['ItemID'];
}else if(isset($_POST['sandboxid'])){
    $ItemID = $_POST['sandboxid'];
}

if (!isset($ItemID)) {
    header('Location: SandBoxVindicate.php');
}

$uploadflag = 1;

if (isset($_POST['Save'])) {
    if (true) {
        if (empty($_POST['sandboxid'])) {
            $_POST['sandboxid'] =0;
        }
        if (empty($_POST['sandboxtype'])) {
            $_POST['sandboxtype'] =" ";
        }
        if (empty($_POST['sandboxname'])) {
            $_POST['sandboxname'] =0;
        }
        if (empty($_POST['sandboxsize'])) {
            $_POST['sandboxsize'] =0;
        }
        if (empty($_POST['sandweight'])) {
            $_POST['sandweight'] =0;
        }
        if (empty($_POST['sandboxnum'])) {
            $_POST['sandboxnum'] =0;
        }
        if (empty($_POST['sandboxuse'])) {
            $_POST['sandboxuse'] =0;
        }

        $time = time();


        $sql = "update wip_sandbox
                set    
                       sandboxtype = '".$_POST['sandboxtype']."' ,
                       sandboxname = '".$_POST['sandboxname']."' ,
                       sandboxsize = '".$_POST['sandboxsize']."' ,
					   sandweight = '".$_POST['sandweight']."' ,
                       sandboxnum = '".$_POST['sandboxnum']."' ,
                       sandboxuse = '".$_POST['sandboxuse']."'
                       
                where  sandboxid = '".$_POST['sandboxid']."' ";

        $result = DB_query($sql,$db);
        prnMsg( _('砂箱更新成功！'), 'success');
    }
}else if(isset($_POST['delete'])){
    $sql = "delete from wip_sandbox
            where sandboxid = '".$_POST['sandboxid']."'";

    $result = DB_query($sql,$db);
    prnMsg( _('砂箱删除成功！'), 'success');
}

$sql = "select  sandboxid,
                sandboxtype,
                sandboxname,
                sandboxsize,
                sandweight,
                sandboxnum,
                sandboxuse " .
       "from wip_sandbox
        where sandboxid ='".$ItemID."' ";
$sql = $sql."  order by sandboxid";
$result = DB_query($sql,$db);

while ($v = DB_fetch_array($result)) {
    $_POST['sandboxid'] = $v['sandboxid'];
    $_POST['sandboxtype'] = $v['sandboxtype'];
    $_POST['sandboxname'] = $v['sandboxname'];
    $_POST['sandboxsize'] = $v['sandboxsize'];
    $_POST['sandweight'] = $v['sandweight'];
    $_POST['sandboxnum'] = $v['sandboxnum'];
    $_POST['sandboxuse'] = $v['sandboxuse'];
}

//echo "失效：".$_POST['sandboxtype']."<br>";
?>
<div class="centre"><a href="<?=$RootPath?>/SandBoxVindicate.php">返回查找砂箱</a></div>
<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/maintenance.png" title="修改砂箱" alt="修改砂箱">修改砂箱</p>
<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST" enctype="multipart/form-data">
    <div>
        <input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
        <br>
        <table class="selection">
            <tr>
                <td>砂箱号码：</td>
                <td><?=$_POST['sandboxid']?>
                    <input type="hidden" name="sandboxid" value="<?=$_POST['sandboxid']?>">
                    <!--                    <input type="hidden" name="sandboxid" value="--><?//=$_POST['sandboxid']?><!--">-->
                </td>
            </tr>
            <tr>
                <td>砂箱名字：</td>
                <td > <input type="text"
                             name="sandboxname"
                             value="<?=$_POST['sandboxname']?>"
                             required="required">
                </td>
            </tr>
            <tr>

                <td>是否失效：</td>
                <td>

                    <select name="sandboxtype">
                        <option><?=$_POST['sandboxtype']?></option>
                        <option>是</option>
                        <option>否</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>用砂重量</td>
                <td><input type="text" name="sandweight" value="<?= $_POST['sandweight'] ?>" ></td></tr>
            <tr>
                <td>砂箱规格大小</td>
                <td><input type="text" name="sandboxsize" value="<?= $_POST['sandboxsize'] ?>"  ></td>

                <!--                <td>单位：</td>-->
                <!--                <td>-->
                <!--                    <select name="Units" id="">-->
                <!--                        --><?php
                //                        $sql = "select unitname from unitsofmeasure order by unitid";
                //                        $result = DB_query($sql,$db);
                //                        while ($v = DB_fetch_array($result)) {
                //                            if ($v['unitname']==$_POST['Units']) {
                //                                ?>
                <!--                                <option value="--><?//=$v['unitname']?><!--" selected="selected">--><?//=$v['unitname']?><!--</option>-->
                <!--                            --><?php //}else{?>
                <!--                                <option value="--><?//=$v['unitname']?><!--">--><?//=$v['unitname']?><!--</option>-->
                <!--                            --><?php	//	}
                //                        }
                //                        ?>
                <!--                    </select>-->
                <!--                </td>-->


            </tr>
            <tr>
                <!---->
                <!--                <td>料号类型：</td>-->
                <!--                <td>-->
                <!--                    <select name="Category" id="">-->
                <!--                        --><?php
                //                        $sql = "select unitname from sf_item_category order by unitid";
                //                        $result = DB_query($sql,$db);
                //                        while ($v = DB_fetch_array($result)) {
                //                            if ($v['unitname']==$_POST['Category']) {
                //                                ?>
                <!--                                <option value="--><?//=$v['unitname']?><!--" selected="selected">--><?//=$v['unitname']?><!--</option>-->
                <!--                            --><?php //}else{?>
                <!--                                <option value="--><?//=$v['unitname']?><!--">--><?//=$v['unitname']?><!--</option>-->
                <!--                            --><?php	//	}
                //                        }
                //                        ?>
                <!--                    </select>-->
                <!--                </td>-->
                <!---->
                <!---->
                <!--                <td>料号分类：</td>-->
                <!--                <td>-->
                <!--                    <select name="item_type" id="">-->
                <!--                        --><?php
                //                        $sql = "select item_type,type_name from sf_item_type order by type_name";
                //                        $result = DB_query($sql, $db);
                //                        while ($v = DB_fetch_array($result)) {
                //                            if ($v['item_type'] == $_POST['item_type']) {
                //                                ?>
                <!--                                <option value="--><?//= $v['item_type'] ?><!--" selected="selected">--><?//= $v['type_name'] ?><!--</option>-->
                <!--                            --><?php //} else { ?>
                <!--                                <option value="--><?//= $v['item_type'] ?><!--">--><?//= $v['type_name'] ?><!--</option>-->
                <!--                                --><?php
                //                            }
                //                        }
                //                        ?>
                <!--                    </select>-->
                <!--                </td>-->



                <td>砂箱数量：</td>
                <td><input type="text" name="sandboxnum" value="<?= $_POST['sandboxnum'] ?>"  ></td>
            </tr>
            <tr>
                <td>已领用砂箱：</td>
                <td><input type="text" name="sandboxuse" value="<?= $_POST['sandboxuse'] ?>"  ></td>
                <!--                <td>加盟商单价（元）：</td>-->
                <!--                <td><input type="text" name="franchise_price" value="--><?//= $_POST['franchise_price'] ?><!--"  ></td>-->
                <!--                <td>采购单价</td>-->
                <!--                <td><input type="text" name="po_price" value="--><?//= $_POST['po_price'] ?><!--"  ></td>-->
            </tr>
            <!--            <tr>-->
            <!--                <td>采购周期</td>-->
            <!--                <td><input type="text" name="lead_time" value="--><?//= $_POST['lead_time'] ?><!--"  ></td>-->
            <!--                <td>生产周期</td>-->
            <!--                <td><input type="text" name="manufacture_time" value="--><?//= $_POST['manufacture_time'] ?><!--"  ></td>-->
            <!--                <td>最小订单量：</td>-->
            <!--                <td><input type="text" class="number" name="MinOty" value="--><?//= $_POST['MinOty'] ?><!--"  ></td>-->
            <!--            </tr>-->

            <!--            <tr>-->
            <!--                <td>上传图片：</td>-->
            <!--                <td><input type="file" name="Pic"></td>-->
            <!--            </tr>-->
            <!--            <tr>-->
            <!--                <td colspan="2" align="center" ><div style="width:100px; height:100px;"><img src="--><?//=$_POST['PicPath']?><!--" alt="料号图片" width="100%" height="100%"></div></td>-->
            <!--            </tr>-->
            <!--            <tr>-->
            <!--                <td>是否生效：</td>-->
            <!--                <td>-->
            <!--                    --><?php
            //                    if ($_POST['Flag']=='N') {
            //                        ?>
            <!--                        <input type="radio" name="Flag" value='Y' >是-->
            <!--                        <input type="radio" name="Flag" value='N' checked=checked>否-->
            <!--                        --><?php
            //                    }else{
            //                        ?>
            <!--                        <input type="radio" name="Flag" value='Y' checked=checked>是-->
            <!--                        <input type="radio" name="Flag" value='N'>否-->
            <!--                        --><?php
            //                    }
            //                    ?>
            <!--                </td>-->
            <!--            </tr>-->
        </table>
    </div>
    <div class="centre">
        <input type="submit" name="Save" value="保存" >
        <input type="submit" name="delete" value="删除" >
    </div>
</form>
<?php
include('includes/footer.inc');
?>
<!--/**-->
<!-- * Created by PhpStorm.-->
<!-- * User: asus-->
<!-- * Date: 2017/12/20-->
<!-- * Time: 10:45-->
<!-- */-->