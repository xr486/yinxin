<?php
include('includes/session.inc');
$Title = _('新增半成品模具');

$ViewTopic = '新增半成品模具';
$BookMark = '新增半成品模具';

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

$uploadflag = 1;

if (isset($_POST['Save'])) {
    if (!empty($_FILES["Pic"]["tmp_name"][0])) {
        foreach($_FILES["Pic"]['type'] as $pic){
            if ((($pic == "image/gif") || ($pic == "image/jpeg") || ($pic == "image/pjpeg")) ) {
                foreach($_FILES["Pic"]['error'] as $error) {
                    if ($error > 0) {
                        $msg = "错误: " . $pic["error"];
                        prnMsg($msg, 'error');
                        $uploadflag = 2;
                    }
                }
            } else {
                $msg = "系统只支持gif,jpeg,pjpeg图片";
                prnMsg($msg, 'error');
                $uploadflag = 2;
            }
        }}

    $sql = "SELECT count(*) FROM wip_mold
				WHERE mould_number = '" . $_POST['mould_number'] . "'
				";
    $result = DB_query($sql, $db);
    $myrow = DB_fetch_row($result);
    if ($myrow[0] > 0) {
        $uploadflag = 2;
        prnMsg('料号不能重命名,因为另一个具有相同名称已经存在', 'error');
    }


    if ($uploadflag == 1) {
        // move_uploaded_file($_FILES["Pic"]["tmp_name"], "itempic/" . $_FILES["Pic"]["name"]);
        $i=0;
        foreach($_FILES["Pic"]["tmp_name"] as $pic){
            move_uploaded_file($pic,"itempic/" . $_POST['mould_number'].$i .".jpg");
            $filearr=$filearr."itempic/" .$_POST['mould_number'] .$i.".jpg".',';
            $i++;
        }
        $time = time();
        if (empty($_POST['mold_class'])) {
            $_POST['mold_class'] =0;
        }
        if (empty($_POST['mould_number'])) {
            $_POST['mould_number'] =0;
        }
        if (empty($_POST['product_map_number'])) {
            $_POST['product_map_number'] =0;
        }
        if (empty($_POST['franchise_price'])) {
            $_POST['franchise_price'] =0;
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
        if (empty($_POST['remarks'])) {
            $_POST['remarks'] =0;
        }
        if (empty($_POST['number_of_use'])) {
            $_POST['number_of_use'] =0;
        }
        $date=strtotime($_POST['die_date']);
        if($date==""){
            $date=0;
        }
        $sql = "INSERT INTO wip_mold (
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
	creation_date,
	created_by,
	last_update_date,
	last_updated_by,
	pic_path,loamcoreid,flag

)
VALUES
	(
		'" . $_POST['mold_class'] . "',
		'" . $_POST['mould_number'] . "',
		'" . $_POST['product_map_number'] . "',
		'" . $_POST['mold_material'] . "',
		'" . $_POST['version_drawing'] . "',
		'" . $date. "',
		'" . $_POST['mould_length'] . "',
		'" . $_POST['mould_width'] . "',
		'" . $_POST['mould_height'] . "',
		'" . $_POST['mold_core_box_number'] . "',
		'" . $_POST['mould_factory_no'] . "',
		'" . $_POST['mould_ownership'] . "',
		'" . $_POST['finished_material'] . "',
		'" . $_POST['modeling_method'] . "','" . $_POST['note_taker'] . "',
		'" . $_POST['remarks'] . "','" . $_POST['number_of_use'] . "','" . $_POST['p_id'] . "',
		'" . $time . "',
		'" . $_SESSION['UserID'] . "',
		'" . $time . "',
		'" . $_SESSION['UserID'] . "',
		'" .$filearr . "','" . $_POST['lcid'] . "','".$_POST['flag']."'
	)";
        $result = DB_query($sql, $db);

        prnMsg(_('新增半成品模具建立成功！'), 'success');

        unset($_POST);
    }
}
?>
    <p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/maintenance.png" title="新增半成品模具" alt="新增半成品模具">新增半成品模具</p>
    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST" enctype="multipart/form-data">
        <div>
            <input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
            <br>
            <table class="selection">
                <tr>
                    <td>模具类别：</td>
                    <td>
                        <select name="mold_class" >
                            <option selected="selected"   value="mud_core_mold">泥芯模具</option>
                        </select>
                    </td>

                    <td>模具图号：</td>
                    <td><input type="text" onblur="checknumber('mould_number')"  required="required" id="mould_number"    name="mould_number"  ></td>

                    <td>产品图号：</td>
                    <td><input type="text"   name="product_map_number"  ></td>
                </tr>

                <tr>

                    <td>模具材质</td>
                    <td><input type="text" name="mold_material"  ></td>
                    <td>图纸版本</td>
                    <td><input type="text" name="version_drawing"  ></td>
                    <td>来模日期：</td>
                    <td><input type="text" onfocus="WdatePicker()"  name="die_date" maxlength="10" size="11" /></td>
                </tr>
                <tr>
                    <td>模具长：</td>
                    <td><input type="text" onblur="check('mould_length')" id="mould_length" name="mould_length"  ></td>
                    <td>模具宽：</td>
                    <td><input type="text" onblur="check('mould_width')" id="mould_width" name="mould_width"  ></td>
                    <td>模具高：</td>
                    <td><input type="text" onblur="check('mould_height')" id="mould_height" name="mould_height"  ></td>
                    </td>
                </tr>

                <tr>
                    <td>模具芯盒数：</td>
                    <td><input required="required"  type="text" onblur="check('mold_core_box_number')" id="mold_core_box_number" name="mold_core_box_number"  ></td>

                    <td>模具本厂编号：</td>
                    <td><input type="text" name="mould_factory_no"  ></td>

                    <td>模具所有权:</td>
                    <td><input type="text" name="mould_ownership"  ></td>

                </tr>


                <tr>

                    <td>成品材质</td>
                    <td><input type="text" name="finished_material"  ></td>
                    <td>造型方式</td>
                    <td><input type="text" name="modeling_method"  ></td>


                    <td>记录人：</td>
                    <td><input type="text" name="note_taker"  ></td>
                </tr>

                <tr>
                    <td>模具使用次数：</td>
                    <td><input type="text" onblur="check('number_of_use')" id="number_of_use" name="number_of_use"  ></td>
                    <td>对应半成品料号：</td>
                    <td><input readonly="readonly" type="text"  id="lcid" name="lcid"  value="<?=$_GET['lcid']?>" ></td>
                    <td>上传图片：<input type="hidden" id="p_id" name="p_id"  ></td>
                    <td><input type="file" name="Pic[]"  multiple="multiple"  ></td>
                </tr>
                <tr>
                    <td>备注：</td>
                    <td colspan="3"><input type="text" size="60" name="remarks" ></td>
                    <td>是否有效</td>
                    <td><input type="radio" name="flag" value="1" checked=checked>是
                        <input type="radio" name="flag" value="0">否
                    </td>
                </tr>
            </table>
        </div>
        <div class="centre">
            <input type="submit" name="Save" value="保存" >
        </div>
    </form>
    <link rel="shortcut icon" href="./favicon.ico"/>
    <link rel="icon" href="./favicon.ico"/>
    <meta http-equiv="Content-Type" content="application/html; charset=utf-8"/>
    <link href="/css/xenos/default.css" rel="stylesheet" type="text/css"/>
    <script type="text/javascript" src ="./javascripts/miscfunctions.js"></script>
    <script type="text/javascript" src ="./javascripts/wdatepicker.js"></script>
    <script type="text/javascript">var basepath='./statics/base/images';</script>
    <script type="text/javascript" src="./statics/base/js/metvar.js"></script>
    <script type="text/javascript" src="./statics/base/js/jQuery1.7.2.js"></script>
    <script type="text/javascript" src="./statics/base/js/uploadify/jquery.uploadify.v2.1.4.min.js"></script>
    <script type="text/javascript" src="./statics/base/js/iframes.js"></script>
    <script type="text/javascript" src="./statics/base/js/cookie.js"></script>
    <script type="text/javascript" src="./statics/base/js/jquery.livequery.js"></script>



    <script src="./javascript/jquery-1.7.2.min.js"></script>
    <script src="./javascript/lhgdialog.min.js?self=true&skin=chrome"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="/javascript/bootstrap.min.js"></script>
    <script type="text/javascript">
//        $('#btn_slect_product').dialog({
//            title:'选择半成品料号',
//            width: '950px',
//            height: 470,
//            content:'url:BtnSearchLoamcore2.php?fwValue=&cat=buliao',
//            init:function(){
//                this.content.document.getElementById('cat').value = 'buliao';
//                this.content.document.getElementById('fwValue').value = '';
//            }
//        });

        function check(s1){
            var a=document.getElementById(s1).value;
            if(!IsNum(a)){
                alert("请输入数字！！");
                document.getElementById(s1).value="";
                document.getElementById(s1).focus();
            }

        }
        function IsNum(num){
            var reNum=/^\d*$/;
            return(reNum.test(num));
        }
        function checknumber(s1){
            var a=document.getElementById(s1).value;
            if (a.length>0&&a.match(/[\x01-\xFF]*/)==false){
                alert('不能输入中文！');
                document.getElementById(s1).value="";
                document.getElementById(s1).value.focus();
                return false;
            }
        }

    </script>


    <script src="./javascript/jquery-1.7.2.min.js"></script>
    <script src="./javascript/lhgdialog.min.js?self=true&skin=chrome"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="/javascript/bootstrap.min.js"></script>




<?php
include('includes/footer.inc');
?>