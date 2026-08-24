<?php
include('includes/session.inc');
$Title = _('新增砂箱');

$ViewTopic = '新增砂箱';
$BookMark = '新增砂箱';

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');



if (isset($_POST['Save'])) {
    $uploadflag = 1;
    $sqlid = "select sandboxid
              from wip_sandbox
              where sandboxid = '".$_POST['sandboxid']."'";
    $resultid = DB_query($sqlid, $db);
    if (mysqli_num_rows($resultid) > 0)
    {
        echo "该用户名已被使用，请重新输入！";
        $uploadflag = 0;
    }


    if ($uploadflag == 1){

        $sql = "insert into wip_sandbox (sandboxid,sandboxtype,sandboxname,sandboxsize,sandweight,sandboxnum,sandboxuse)
                values (                
                '" . $_POST['sandboxid'] . "',
                '否',
		        '" . $_POST['sandboxname'] . "',
		        '" . $_POST['sandboxsize'] . "',
		        '" . $_POST['sandweight'] . "',
	            '" . $_POST['sandboxnum'] . "',
		        '" . $_POST['sandboxuse'] . "'               
                ) ";
        $result = DB_query($sql, $db);

        prnMsg(_('砂箱建立成功！'), 'success');

        unset($_POST);
    }



//    }
}
?>
<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/maintenance.png" title="新增砂箱" alt="新增砂箱">新增砂箱</p>
<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST" enctype="multipart/form-data">
    <div>
        <input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
        <br>
        <table class="selection">
            <tr>
                <td>砂箱号码：</td>
                <td><input type="text"
                           name="sandboxid"
                           value="<?= $_POST['sandboxid'] ?>"
                           required="required"></td>
                <!--            </tr>-->
                <!--            <tr>-->
                <td>砂箱名字：</td>
                <td colspan="3"><input type="text"
                                       name="sandboxname"
                                       value="<?= $_POST['sandboxname'] ?>"
                                       required="required"></td>
            </tr>
            <tr>

                <td>砂箱规格型号</td>
                <td><input type="text"
                           name="sandboxsize"
                           value="<?= $_POST['sandboxsize'] ?>"
                           required="required"></td>
                <td>用砂重量</td>
                <td><input type="text"
                           name="sandweight"
                           value="<?= $_POST['sandweight'] ?>"
                           required="required"></td>
                <!--                <td>单位：</td>-->
                <!--                <td>-->
                <!--                    <select name="Units" id="">-->
                <!--                        --><?php
                //                        $sql = "select unitname from unitsofmeasure order by unitid";
                //                        $result = DB_query($sql, $db);
                //                        while ($v = DB_fetch_array($result)) {
                //                            if ($v['unitname'] == $_POST['Units']) {
                //                                ?>
                <!--                                <option value="--><?//= $v['unitname'] ?><!--" selected="selected">--><?//= $v['unitname'] ?><!--</option>-->
                <!--                            --><?php //} else { ?>
                <!--                                <option value="--><?//= $v['unitname'] ?><!--">--><?//= $v['unitname'] ?><!--</option>-->
                <!--                                --><?php
                //                            }
                //                        }
                //                        ?>
                <!--                    </select>-->
                <!--                </td>-->
            </tr>
            <tr>
                <!--                <td>料号类型：</td>-->
                <!--                <td>-->
                <!--                    <select name="Category" id="">-->
                <!--                        --><?php
                //                        $sql = "select unitname from sf_item_category order by unitid";
                //                        $result = DB_query($sql, $db);
                //                        while ($v = DB_fetch_array($result)) {
                //                            if ($v['unitname'] == $_POST['Category']) {
                //                                ?>
                <!--                                <option value="--><?//= $v['unitname'] ?><!--" selected="selected">--><?//= $v['unitname'] ?><!--</option>-->
                <!--                            --><?php //} else { ?>
                <!--                                <option value="--><?//= $v['unitname'] ?><!--">--><?//= $v['unitname'] ?><!--</option>-->
                <!--                                --><?php
                //                            }
                //                        }
                //                        ?>
                <!--                    </select>-->
                <!--                </td>-->

                <!--                <td>料号分类：</td>-->
                <!--                <td>-->
                <!--                    <select name="Item_Type" id="">-->
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
                <td>砂箱数量：</td>
                <td><input type="text"
                           name="sandboxnum"
                           value="<?= $_POST['sandboxnum'] ?>"
                           required="required"></td>
                <!--                </td>-->


            </tr>
            <tr>
                <td>已领用砂箱：</td>
                <td><input type="text"
                           name="sandboxuse"
                           value="<?= $_POST['sandboxuse'] ?>"
                           required="required"></td>

                <!--                <td>加盟商单价（元）：</td>-->
                <!--                <td><input type="text" name="franchise_price" value="--><?//= $_POST['franchise_price'] ?><!--" ></td>-->
                <!---->
                <!--                <td>采购单价:</td>-->
                <!--                <td><input type="text" name="po_price" value="--><?//= $_POST['po_price'] ?><!--" ></td>-->

            </tr>


            <!--            <tr>-->
            <!---->
            <!--                <td>采购周期</td>-->
            <!--                <td><input type="text" name="lead_time" value="--><?//= $_POST['lead_time'] ?><!--" ></td>-->
            <!--                <td>生产周期</td>-->
            <!--                <td><input type="text" name="manufacture_time" value="--><?//= $_POST['manufacture_time'] ?><!--" ></td>-->
            <!---->
            <!---->
            <!--                <td>最小订单量：</td>-->
            <!--                <td><input type="text" class="number" name="MinOty" value="--><?//= $_POST['MinOty'] ?><!--" ></td>-->
            <!--            </tr>-->
            <!--            <tr>-->
            <!--                <td>上传图片：</td>-->
            <!--                <td><input type="file" name="Pic"></td>-->
            <!--            </tr>-->
            <!--            <tr>-->
            <!--                <td colspan="4" align="center" ><div style="width:100px; height:100px;"><img src="--><?//= $_POST['PicPath'] ?><!--" alt="料号图片" width="100%" height="100%"></div></td>-->
            <!--            </tr>-->
            <!--            <tr>-->
            <!--                <td>是否生效：</td>-->
            <!--                <td>-->
            <!--                    --><?php
            //                    if ($_POST['Flag'] == 'N') {
            //                        ?>
            <!--                        <input type="radio" name="Flag" value='Y' >是-->
            <!--                        <input type="radio" name="Flag" value='N' checked=checked>否-->
            <!--                        --><?php
            //                    } else {
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
    </div>
</form>
<?php
include('includes/footer.inc');
?>
<!--/**-->
<!-- * Created by PhpStorm.-->
<!-- * User: KinCae-->
<!-- * Date: 2017/12/19-->
<!-- * Time: 15:50-->
<!-- */-->