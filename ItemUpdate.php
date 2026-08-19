<?php
include('includes/session.inc');
$Title = _('修改材料 ');
$ViewTopic= '修改材料 ';
$BookMark = '修改材料 ';

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

if (isset($_GET['ItemID'])) {
    $ItemID = $_GET['ItemID'];
}else if(isset($_POST['ItemID'])){
    $ItemID = $_POST['ItemID'];
}

if (!isset($ItemID)) {
    header('Location: SearchItemNo.php');
}

$uploadflag = 1;

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
        $sql = "select pic_path from sf_item_no where item_id ='".$ItemID."' ";
        $result = DB_query($sql,$db);
        while ($v = DB_fetch_array($result)) {
            $_POST['PicPath'] = $v['pic_path'];
        }
    }

    if ($uploadflag == 1) {
        move_uploaded_file($_FILES["Pic"]["tmp_name"],"itempic/" . $_POST['ItemNo'] .".jpg");

        if (empty($_POST['MinOty'])) {
            $_POST['MinOty'] =0;
        }
        if (empty($_POST['SafeQty'])) {
            $_POST['SafeQty'] =0;
        }
        if (empty($_POST['UnitPrice'])) {
            $_POST['UnitPrice'] =0;
        }
        if (empty($_POST['franchise_price'])) {
            $_POST['franchise_price'] =0;
        }
        if (empty($_POST['po_price'])) {
            $_POST['po_price'] =0;
        }
        if (empty($_POST['SafeQty'])) {
            $_POST['SafeQty'] =0;
        }
        if (empty($_POST['SafeQty'])) {
            $_POST['SafeQty'] =0;
        }
        if (empty($_POST['SafeQty'])) {
            $_POST['SafeQty'] =0;
        }
        if (empty($_POST['lead_time'])) {
            $_POST['lead_time'] =0;
        }
        if (empty($_POST['manufacture_time'])) {
            $_POST['manufacture_time'] =0;
        }
        $time = time();
        $sql = "update sf_item_no
                                set item_desc='".$_POST['ItemDesc']."',
                                    units='".$_POST['Units']."',
                                    min_order='".$_POST['MinOty']."',
                                    safe_qty='".$_POST['SafeQty']."',
                                    pic_path='".$_POST['PicPath']."',
                                    unit_price='".$_POST['UnitPrice']."',
									item_type='".$_POST['item_type']."',
				                    franchise_price='".$_POST['franchise_price']."',
                                    po_price='".$_POST['po_price']."',
                                    item_category='".$_POST['Category']."',
                                    disable_flag='".$_POST['Flag']."',
                                    last_update_date='".$time."',
                                    last_updated_by='".$_SESSION['UserID']."' ,
                                    item_spec = '".$_POST['item_spec']."' ,
                                    lead_time = '".$_POST['lead_time']."' ,
									manufacture_time = '".$_POST['manufacture_time']."' ,
                                    yanse = '".$_POST['yanse']."'
                              where item_id = '".$_POST['ItemID']."' ";
        $result = DB_query($sql,$db);

        prnMsg( _('材料 更新成功！'), 'success');
    }

}

$sql = "select item_type,item_id,item_spec,franchise_price,yanse,lead_time,manufacture_time,item_no,item_desc,units,min_order,safe_qty,"
    . "    pic_path,unit_price,po_price,item_category,disable_flag "
    . "from sf_item_no where item_id ='".$ItemID."' ";
$sql = $sql."  order by item_no";
$result = DB_query($sql,$db);
while ($v = DB_fetch_array($result)) {
    $_POST['ItemID'] = $v['item_id'];
    $_POST['ItemNo'] = $v['item_no'];
    $_POST['item_type'] = $v['item_type'];
    $_POST['ItemDesc'] = $v['item_desc'];
    $_POST['Units'] = $v['units'];
    $_POST['MinOty'] = $v['min_order'];
    $_POST['SafeQty'] = $v['safe_qty'];
    $_POST['PicPath'] = $v['pic_path'];
    $_POST['UnitPrice'] = $v['unit_price'];
    $_POST['franchise_price'] = $v['franchise_price'];
    $_POST['po_price'] = $v['po_price'];
    $_POST['Category'] = $v['item_category'];
    $_POST['Flag'] = $v['disable_flag'];
    $_POST['item_spec'] = $v['item_spec'];
    $_POST['lead_time'] = $v['lead_time'];
    $_POST['manufacture_time'] = $v['manufacture_time'];
    $_POST['yanse'] = $v['yanse'];

}
?>
    <div class="centre"><a href="<?=$RootPath?>/SearchItemNo.php">返回查找材料 </a></div>
    <p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/maintenance.png" title="修改材料 " alt="修改材料 ">修改材料 </p>
    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST" enctype="multipart/form-data">
        <div>
            <input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
            <br>
            <table class="selection">
                <tr>
                    <td>材料料号 ：</td>
                    <td><?=$_POST['ItemNo']?>
                        <input type="hidden" name="ItemNo" value="<?=$_POST['ItemNo']?>">
                        <input type="hidden" name="ItemID" value="<?=$_POST['ItemID']?>">
                    </td>
                </tr>
                <tr>
                    <td>材料描述：</td>
                    <td colspan="3"> <input type="text" size="70" name="ItemDesc" value="<?=$_POST['ItemDesc']?>" required="required"></td>
                </tr>
                <tr>
                    <td>材料规格：</td>
                    <td><input type="text" name="item_spec" value="<?= $_POST['item_spec'] ?>"  ></td>

                    <td>颜色：</td>
                    <td><input type="text" name="yanse" value="<?= $_POST['yanse'] ?>" ></td>
                    <td>单位：</td>
                    <td>
                        <select name="Units" id="">
                            <?php
                            $sql = "select unitname from unitsofmeasure order by unitid";
                            $result = DB_query($sql,$db);
                            while ($v = DB_fetch_array($result)) {
                                if ($v['unitname']==$_POST['Units']) {
                                    ?>
                                    <option value="<?=$v['unitname']?>" selected="selected"><?=$v['unitname']?></option>
                                <?php }else{?>
                                    <option value="<?=$v['unitname']?>"><?=$v['unitname']?></option>
                                <?php		}
                            }
                            ?>
                        </select>
                    </td>


                </tr>
                <tr>

                    <td>材料类型：</td>
                    <td>
                        <select name="Category" id="">
                            <?php
                            $sql = "select unitname from sf_item_category order by unitid";
                            $result = DB_query($sql,$db);
                            while ($v = DB_fetch_array($result)) {
                                if ($v['unitname']==$_POST['Category']) {
                                    ?>
                                    <option value="<?=$v['unitname']?>" selected="selected"><?=$v['unitname']?></option>
                                <?php }else{?>
                                    <option value="<?=$v['unitname']?>"><?=$v['unitname']?></option>
                                <?php		}
                            }
                            ?>
                        </select>
                    </td>


                    <td>材料分类：</td>
                    <td>
                        <select name="item_type" id="">
                            <?php
                            $sql = "select item_type,type_name from sf_item_type order by type_name";
                            $result = DB_query($sql, $db);
                            while ($v = DB_fetch_array($result)) {
                                if ($v['item_type'] == $_POST['item_type']) {
                                    ?>
                                    <option value="<?= $v['item_type'] ?>" selected="selected"><?= $v['type_name'] ?></option>
                                <?php } else { ?>
                                    <option value="<?= $v['item_type'] ?>"><?= $v['type_name'] ?></option>
                                    <?php
                                }
                            }
                            ?>
                        </select>
                    </td>



                    <td>安全库存：</td>
                    <td><input type="text" name="SafeQty" value="<?= $_POST['SafeQty'] ?>"  ></td>
                </tr>
                <tr>
                    <td>客户单价（元）：</td>
                    <td><input type="text" name="UnitPrice" value="<?= $_POST['UnitPrice'] ?>"  ></td>
                    <td>加盟商单价（元）：</td>
                    <td><input type="text" name="franchise_price" value="<?= $_POST['franchise_price'] ?>"  ></td>
                    <td>采购单价（元）：</td>
                    <td><input type="text" name="po_price" value="<?= $_POST['po_price'] ?>"  ></td>
                </tr>
                <tr>
                    <td>采购周期：</td>
                    <td><input type="text" name="lead_time" value="<?= $_POST['lead_time'] ?>"  ></td>
                    <td>生产周期：</td>
                    <td><input type="text" name="manufacture_time" value="<?= $_POST['manufacture_time'] ?>"  ></td>
                    <td>最小订单量：</td>
                    <td><input type="text" class="number" name="MinOty" value="<?= $_POST['MinOty'] ?>"  ></td>
                </tr>

                <tr>
                    <td>上传图片：</td>
                    <td><input type="file" name="Pic"></td>
                </tr>
                <tr>
                    <td colspan="2" align="center" ><div style="width:100px; height:100px;"><img src="<?=$_POST['PicPath']?>" alt="材料图片：" width="100%" height="100%"></div></td>
                </tr>
                <tr>
                    <td>是否生效：</td>
                    <td>
                        <?php
                        if ($_POST['Flag']=='N') {
                            ?>
                            <input type="radio" name="Flag" value='Y' >是
                            <input type="radio" name="Flag" value='N' checked=checked>否
                            <?php
                        }else{
                            ?>
                            <input type="radio" name="Flag" value='Y' checked=checked>是
                            <input type="radio" name="Flag" value='N'>否
                            <?php
                        }
                        ?>
                    </td>
                </tr>
            </table>
        </div>
        <div class="centre">
            <input type="submit" name="Save" value="保存" >
        </div>
    </form>
<?php
include('includes/footer.inc');
?>