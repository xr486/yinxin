<?php
include('includes/session.inc');
$Title = _('新增料号');

$ViewTopic = '新增料号';
$BookMark = '新增料号';

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

$uploadflag = 1;

if (isset($_POST['Save'])) {
    if (!empty($_FILES["Pic"]["tmp_name"])) {
        if ((($_FILES["Pic"]["type"] == "image/gif") || ($_FILES["Pic"]["type"] == "image/jpeg") || ($_FILES["Pic"]["type"] == "image/pjpeg")) && ($_FILES["Pic"]["size"] < 20 * 1024 * 1024)) {
            if ($_FILES["Pic"]["error"] > 0) {
                $msg = "错误: " . $_FILES["Pic"]["error"];
                prnMsg($msg, 'error');
                $uploadflag = 2;
            }
        } else {
            $msg = "系统只支持gif,jpeg,pjpeg图片";
            prnMsg($msg, 'error');
            $uploadflag = 2;
        }
    }
     $sql = "SELECT count(*) FROM sf_item_no
                WHERE item_no = '" . $_POST['ItemNo'] . "'
                ";
        $result = DB_query($sql, $db);
        $myrow = DB_fetch_row($result);
        if ($myrow[0] > 0) {
            $uploadflag = 2;
            prnMsg('料号不能重命名,因为另一个具有相同名称已经存在', 'error');
        } 
    if ($uploadflag == 1) {
        //move_uploaded_file($_FILES["Pic"]["tmp_name"], "itempic/" . $_FILES["Pic"]["name"]);
        move_uploaded_file($_FILES["Pic"]["tmp_name"],"itempic/" . $_POST['ItemNo'] .".jpg");
        $_POST['PicPath'] = "itempic/" . $_POST['ItemNo'] .".jpg";         
        $time = time();
        
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
        $sql = "INSERT INTO sf_item_no (
    item_no,
    item_desc,
    units,
    item_category,
    item_type,
    min_order,
    safe_qty,
    unit_price,franchise_price,po_price,
    disable_flag,
    pic_path,
    creation_date,
    created_by,
    last_update_date,
    last_updated_by,
        pinpai,
        lead_time,manufacture_time,
        yanse 
)
VALUES
    (
        '" . $_POST['ItemNo'] . "',
        '" . $_POST['ItemDesc'] . "',
        '" . $_POST['Units'] . "',
        '" . $_POST['Category'] . "',
        '" . $_POST['Item_Type'] . "',
        '" . $_POST['MinOty'] . "',
        '" . $_POST['SafeQty'] . "',
        '" . $_POST['UnitPrice'] . "',
        '" . $_POST['franchise_price'] . "',
        '" . $_POST['po_price'] . "',
        '" . $_POST['Flag'] . "',
        '" . $_POST['PicPath'] . "',
        '" . $time . "',
        '" . $_SESSION['UserID'] . "',
        '" . $time . "',
        '" . $_SESSION['UserID'] . "',
        '" . $_POST['pinpai'] . "',
        '" . $_POST['lead_time'] . "',
        '" . $_POST['manufacture_time'] . "',
        '" . $_POST['yanse'] . "' 
    )";
        $result = DB_query($sql, $db);

        prnMsg(_('料号建立成功！'), 'success');

        unset($_POST);
    }
}
?>
<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/maintenance.png" title="新增料号" alt="新增料号">新增料号</p>
<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST" enctype="multipart/form-data">
    <div>
        <input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
        <br>
        <table class="selection">
            <tr>
                <td>规格型号：</td>
                <td colspan="3"><input type="text" name="ItemNo" size="70" value="<?= $_POST['ItemNo'] ?>" required="required"></td>
            </tr>
            <tr>
                <td>料号名称：</td>
                <td colspan="3"><input type="text"  size="70"  name="ItemDesc" value="<?= $_POST['ItemDesc'] ?>" required="required"></td>
            </tr>
            <tr>

            <td>品牌</td>
                <td  ><input type="text" size="15"  name="pinpai" value="<?= $_POST['pinpai'] ?>" ></td>
           
                <td>单位：</td>
                <td>
                    <select name="Units" id="">
<?php
$sql = "select unitname from unitsofmeasure order by unitid";
$result = DB_query($sql, $db);
while ($v = DB_fetch_array($result)) {
    if ($v['unitname'] == $_POST['Units']) {
        ?>
                                <option value="<?= $v['unitname'] ?>" selected="selected"><?= $v['unitname'] ?></option>
                            <?php } else { ?>
                                <option value="<?= $v['unitname'] ?>"><?= $v['unitname'] ?></option>
                            <?php
                            }
                        }
                        ?>
                    </select>
                </td>
  </tr>
            <tr>
                <td>料号类型：</td>
                <td>
                    <select name="Category" id="">
<?php
$sql = "select unitname from sf_item_category order by unitid";
$result = DB_query($sql, $db);
while ($v = DB_fetch_array($result)) {
    if ($v['unitname'] == $_POST['Category']) {
        ?>
                                <option value="<?= $v['unitname'] ?>" selected="selected"><?= $v['unitname'] ?></option>
                            <?php } else { ?>
                                <option value="<?= $v['unitname'] ?>"><?= $v['unitname'] ?></option>
                            <?php
                            }
                        }
                        ?>
                    </select>
                </td>
          
                <td>料号分类：</td>
                <td>
                    <select name="Item_Type" id="">
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
                    <td>安全库存：</td>
                <td><input type="text" class="number" name="SafeQty" value="<?= $_POST['SafeQty'] ?>" ></td>
                </td>

                
            </tr>
            <tr>
                <td>客户单价（元）：</td>
                <td><input type="text" class="number" name="UnitPrice" value="<?= $_POST['UnitPrice'] ?>" ></td>

        
                <td>采购单价:</td>
                <td><input type="text" class="number"  name="po_price" value="<?= $_POST['po_price'] ?>" ></td>
                
            </tr>
               
            
           <tr>
           
            <td>采购周期</td>
                <td><input type="text" class="number" name="lead_time" value="<?= $_POST['lead_time'] ?>" ></td>
                <td>生产周期</td>
                <td><input type="text" class="number" name="manufacture_time" value="<?= $_POST['manufacture_time'] ?>" ></td>
 
                 
                 <td>最小订单量：</td>
                <td><input type="text" class="number" name="MinOty" value="<?= $_POST['MinOty'] ?>" ></td>
            </tr>
        
            
            <tr>
                <td>是否生效：</td>
                <td>
                    <?php
                    if ($_POST['Flag'] == 'N') {
                        ?>
                        <input type="radio" name="Flag" value='Y' >是
                        <input type="radio" name="Flag" value='N' checked=checked>否
                        <?php
                    } else {
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