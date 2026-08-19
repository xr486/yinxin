<?php 
	include('includes/session.inc');
	$Title = _('修改成品料号');

	$ViewTopic= '修改成品料号';
	$BookMark = '修改成品料号';

	include('includes/header.inc');
	include('includes/SQL_CommonFunctions.inc');

	if (isset($_GET['p_id'])) {
		$p_id = $_GET['p_id'];
	}else if(isset($_POST['p_id'])){
		$p_id = $_POST['p_id'];
    }
	if (!isset($p_id)) {
		header('Location:searchEndProducts.php');
	}    

    $uploadflag = 1;

    if(isset($_POST['delete1'])){
         if(!empty($_POST['p_id'])){
              $deleteSql = "update  wip_endproducts set flag=0 where p_id='". $_POST['p_id']."'";
             $sql2="update wip_mold set flag=0 where  product_number='". $_POST['p_id']."' ";
             $sql3="update wip_loamcore set flag=0 where p_id='". $_POST['p_id']."' ";
//             $sql4="update wip_mold set falg=0 where loamcoreid='".."'";
//             $sql4="update wip_mold set flag=0 where "
             foreach($_POST['lcid'] as $k=>$v){
                 $sql4="update wip_mold set flag=0 where loamcoreid='".$v."' ";
                 DB_query($sql4,$db);
             }

              DB_query($deleteSql,$db);
             DB_query($sql2,$db);
             DB_query($sql3,$db);

         }
        unset($_POST['delete1']);
        echo '<div class="centre"><a href="'.$RootPath.'/searchEndProducts.php">返回查找成品料号</a></div>';
        prnMsg(_('修改成功！！！！'), 'success');
        include('includes/footer.inc');
        exit;
    }
if(isset($_POST['delete0'])){
    if(!empty($_POST['p_id'])){
        $deleteSql = "update  wip_endproducts set flag=1 where p_id='". $_POST['p_id']."'";
        $sql2="update wip_mold set flag=1 where  product_number='". $_POST['p_id']."' ";
        $sql3="update wip_loamcore set flag=1 where p_id='". $_POST['p_id']."' ";
        foreach($_POST['lcid'] as $k=>$v){
            $sql4="update wip_mold set flag=1 where loamcoreid='".$v."' ";
            DB_query($sql4,$db);
        }
        DB_query($deleteSql,$db);
        DB_query($sql2,$db);
        DB_query($sql3,$db);
        DB_query($sql4,$db);

    }
    unset($_POST['delete0']);
    echo '<div class="centre"><a href="'.$RootPath.'/searchEndProducts.php">返回查找成品料号</a></div>';
    prnMsg(_('修改成功！！！！'), 'success');
    include('includes/footer.inc');
    exit;
}

	if (isset($_POST['Save'])) {
		if (!empty($_FILES["p_path"]["tmp_name"])) {
			if ((($_FILES["p_path"]["type"] == "image/gif")
				|| ($_FILES["p_path"]["type"] == "image/jpeg")
				|| ($_FILES["p_path"]["type"] == "image/pjpeg"))
				&& ($_FILES["p_path"]["size"] < 20*1024*1024)){
				  if ($_FILES["p_path"]["error"] > 0){
				    $msg = "错误: " . $_FILES["p_path"]["error"];
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
			$_POST['PicPath'] = "itempic/" . $_POST['p_id'] .".jpg";
		}else{
			$sql = "select img_path from wip_endproducts where p_id ='".$p_id."' ";
			$result = DB_query($sql,$db);
			while ($v = DB_fetch_array($result)) {
				$_POST['PicPath'] = $v['img_path'];
			}
		}

	if ($uploadflag == 1) {
			move_uploaded_file($_FILES["img_path"]["tmp_name"],"itempic/" . $_POST['p_id'] .".jpg");
	  if (empty($_POST['p_name'])) {
          $_POST['p_name'] ='';
       }	
      if (empty($_POST['p_weight'])) {
          $_POST['p_weight']=0;
       }
      if (empty($_POST['material_quality'])) {
          $_POST['material_quality'] =0;
       }
      if (empty($_POST['model_station'])) {
          $_POST['model_station'] =0;
       }
       if (empty($_POST['model_unit_price'])) {
          $_POST['model_unit_price'] =0;
       }
       if (empty($_POST['polish_unit_price'])) {
          $_POST['polish_unit_price'] =0;
       }	
       if (empty($_POST['stock_count'])) {
          $_POST['stock_count'] =0;
       }
       if (empty($_POST['img_id'])) {
          $_POST['img_id'] ='';
       }	   
	   if(empty($_POST['sandboxid'])) {
          $_POST['sandboxid'] ='';
       }
       if(empty($_POST['sandboxweight'])) {
          $_POST['sandboxweight'] =0;
       }
       if (empty($_POST['production_oneday'])) {
          $_POST['production_oneday'] =0;
       }	   
	   if(empty($_POST['customer_require'])) {
          $_POST['customer_require'] ='';
       }	
			$time = time();
			$sql = "update wip_endproducts
                                set p_name='".$_POST['p_name']."',
                                    p_weight='".$_POST['p_weight']."',
                                    material_quality='".$_POST['material_quality']."',
                                    model_station='".$_POST['model_station']."',
                                    model_unit_price='".$_POST['model_unit_price']."',
									polish_unit_price='".$_POST['polish_unit_price']."',
                                    stock_count='".$_POST['stock_count']."',
                                    img_id='".$_POST['img_id']."',
                                    img_path='".$_POST['PicPath']."',
                                    sandboxid='".$_POST['sandboxid']."',
                                    sandboxweight='".$_POST['sandboxweight']."' ,
                                    production_oneday = '".$_POST['production_oneday']."' ,
                                    requied_paint = ".$_POST['requied_paint'].",
									customer_require = '".$_POST['customer_require']."' ,
                                    model = ".$_POST['model'].",
                                    cast = ".$_POST['cast']." ,
                                    polish = ".$_POST['polish'].",
                                    input = ".$_POST['input']." ,
                                    endproduct_unitprice=".$_POST['endproduct_unitprice']."
                              where p_id = '".$_POST['p_id']."' ";
			$result = DB_query($sql,$db);

			prnMsg( _('料号更新成功！'), 'success');
		}

	}

	$sql = "select p_id,p_name,p_weight,material_quality,model_station,model_unit_price,polish_unit_price,stock_count,img_id,img_path,sandboxid,sandboxweight,"
                . " production_oneday,requied_paint,customer_require,model,cast,polish,input,flag,endproduct_unitprice"
                . " from wip_endproducts where p_id='".$p_id."'";
        $sql = $sql." order by p_id";
	$result = DB_query($sql,$db);
	while ($v = DB_fetch_array($result)) {
		$_POST['p_id'] = $v['p_id'];
		$_POST['p_name'] = $v['p_name'];
		$_POST['p_weight'] = $v['p_weight'];
		$_POST['material_quality'] = $v['material_quality'];
		$_POST['model_station'] = $v['model_station'];
		$_POST['model_unit_price'] = $v['model_unit_price'];
		$_POST['polish_unit_price'] = $v['polish_unit_price'];
		$_POST['stock_count'] = $v['stock_count'];
		$_POST['img_id'] = $v['img_id'];
		$_POST['img_path'] = $v['img_path'];
        $_POST['sandboxid'] = $v['sandboxid'];
		$_POST['sandboxweight'] = $v['sandboxweight'];
		$_POST['production_oneday'] = $v['production_oneday'];
        $_POST['requied_paint'] = $v['requied_paint'];
		$_POST['customer_require'] = $v['customer_require'];
		$_POST['model'] = $v['model'];
		$_POST['cast'] = $v['cast'];
        $_POST['polish'] = $v['polish'];
        $_POST['input'] = $v['input'];
        $_POST['flag'] = $v['flag'];
        $_POST['endproduct_unitprice']=$v['endproduct_unitprice'];
	} 
?>
<div class="centre"><a href="<?=$RootPath?>/searchEndProducts.php">返回查找成品料号</a></div>
<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/maintenance.png" title="修改成品料号" alt="修改成品料号">修改成品料号</p>
<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST" enctype="multipart/form-data">
<div>
	<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
<br>
<table class="selection">
	<tr>
		<td>成品料号</td>
        <td><?=$_POST['p_id']?>
            <input type="hidden" name="p_id" required="required" value="<?=$_POST['p_id']?>"></td>

        <td>料号名称</td>
        <td><input type="text" name="p_name" required="required" value="<?=$_POST['p_name']?>" ></td>

        <td>重量</td>
        <td><input type="text"  class="number"  required="required" name="p_weight" value="<?= $_POST['p_weight'] ?>" ></td>

	</tr>
  <tr>
            <td>材质</td>
                <td><input type="text" required="required" name="material_quality" value="<?= $_POST['material_quality'] ?>"></td>
                <td>造型工位</td>
                <td><input type="text" required="required" name="model_station" value="<?= $_POST['model_station'] ?>"></td>
                <td>造型单价</td>
                <td><input type="text" required="required"  class="number"  name="model_unit_price" value="<?= $_POST['model_unit_price'] ?>"></td>
            </tr>
            <tr>
                <td>打磨单价</td>
                <td><input type="text" required="required"  class="number"  name="polish_unit_price" value="<?= $_POST['polish_unit_price'] ?>"></td>
                <td>库存数量</td>
                <td><input type="text" required="required"  class="number"  name="stock_count" value="<?= $_POST['stock_count'] ?>"></td>
                <td>成品图号</td>
                <td><input type="text" required="required" name="img_id"  value="<?= $_POST['img_id'] ?>"></td>
            </tr>

            <tr>
                <td>沙箱号</td>
                <td>
                     <select name="sandboxid" id="" >
                         <?php
                                $sql = "select sandboxid from wip_sandbox order by sandboxid";
                                $result = DB_query($sql, $db);
                                while ($v = DB_fetch_array($result)) {
                                    if ($v['sandboxid'] == $_POST['sandboxid']) {
                                        echo  $v['sandboxid'] . "==" . $_POST['sandboxid'];
                                ?>                      
                                <option value="<?= $v['sandboxid'] ?>" selected="selected"><?= $v['sandboxid'] ?></option>
                              <?php } else { 
                               echo "else"; 
                               ?>
                                <option value="<?= $v['sandboxid'] ?>"><?= $v['sandboxid'] ?></option>
                            <?php
                            }
                        }
                        ?>
                    </select>   
                </td>
                <td>用砂重量</td> 
                <td><input type="text"  required="required" class="number"  name="sandboxweight" value="<?= $_POST['sandboxweight'] ?>"></td>

                <td>一天生产个数</td>
                <td><input type="text" required="required"  class="number"  name="production_oneday" value="<?= $_POST['production_oneday'] ?>"></td>
            </tr>
            <tr>
                <td>客户需求</td>
                <td><input type="text" name="customer_require" required="required" value="<?= $_POST['customer_require'] ?>"></td>

                <td>单位报价</td>
                <td><input type="text" class="number"  required="required" name="endproduct_unitprice" value="<?= $_POST['endproduct_unitprice'] ?>"></td>
              
                <td>上传图片</td>
                <td><input type="file" name="img_path" ></td>

            </tr>

    </table>

    <table>
            <tr>
                <td>造型</td>
                <td style="border:solid 1px;">

                	<?php
                        if($_POST['model'] == 0){
                           echo '<input type="radio" name="model" value="true">是
                                <input type="radio" name="model" value="false" checked=checked>否';
                        } else {
                           echo '<input type="radio" name="model" value="true"checked=checked>是
                                <input type="radio" name="model" value="false">否';
                        }
                	?>
                	
                </td>
                <td>&nbsp;&nbsp;</td>
                <td>浇铸</td>
                <td style="border:solid 1px;">
                    <?php
                        if($_POST['cast'] == 0){
                           echo '<input type="radio" name="cast" value="true">是
                                <input type="radio" name="cast" value="false" checked=checked>否';
                        } else {
                           echo '<input type="radio" name="cast" value="true"checked=checked>是
                                <input type="radio" name="cast" value="false">否';
                        }
                	?>
                </td>
                <td>&nbsp;&nbsp;</td>
                <td>打磨</td>
                <td style="border:solid 1px;">
                    <?php
                        if($_POST['polish'] == 0){
                           echo '<input type="radio" name="polish" value="true">是
                                <input type="radio" name="polish" value="false" checked=checked>否';
                        } else {
                           echo '<input type="radio" name="polish" value="true"checked=checked>是
                                <input type="radio" name="polish" value="false">否';
                        }
                	?>
                </td>
                <td>&nbsp;&nbsp;</td>
                <td>入库</td>
                <td style="border:solid 1px;">
                     <?php
                        if($_POST['input'] == 0){
                           echo '<input type="radio" name="input" value="true">是
                                <input type="radio" name="input" value="false" checked=checked>否';
                        } else {
                           echo '<input type="radio" name="input" value="true"checked=checked>是
                                <input type="radio" name="input" value="false">否';
                        }
                	?>
                </td>
                <td>&nbsp;&nbsp;</td>
                <td>是否有效</td>
                <td style="border:solid 1px;background-color: red;color: white">
                    <?= $_POST['flag']==1 ? '有效':'失效' ?>
                </td>
                <td>&nbsp;&nbsp;</td>
                <td>是否需要油漆</td>
                <td>
                  <!--根据requied_paint的值来动态更改选中的内容
                        如果是0 则表示选择否
                        如果是1 则表示选择是
                  -->
                    <?php if($_POST['requied_paint'] == 0){
                           echo '<input type="radio" name="requied_paint" value="true">是
                                <input type="radio" name="requied_paint" value="false" checked=checked>否';
                        } else {
                           echo '<input type="radio" name="requied_paint" value="true"checked=checked>是
                                <input type="radio" name="requied_paint" value="false">否';
                       }
                   ?>
                </td>
            </tr>
</table>

    <table>
        <tr>
            <td  style="width: 100px"><img src="<?=$_POST['img_path'] ?>"  alt="暂未上传图片" height="100px"></td>
        </tr>
    </table>

</div>

    <div  class="centre" style="width:950px;">

        <table>
            <tr><td><a class="btn btn-info btn-xs"  href="<?=$RootPath?>/Addproductmold.php?stockid=<?=$_POST['p_id']?> " target="_blank" >新增成品模具</a></td>
            </tr>
            <tr>
                <th width="150" >模具图号</th>
                <th width="150">模具类别</th>
                <th width="150" >产品图号</th>
                <th width="150" >模具芯盒数</th>
                <th width="150" >领取状态</th>
                <th width="150" >有效状态</th>
            </tr>
            <?php
            $sql1="select mold_class,mould_number,product_map_number,mold_core_box_number,stauts,flag
                                from wip_mold
                                where  product_number='".$_POST['p_id']."'  ";
            $result2 = @DB_query($sql1,$db);
            if(@DB_num_rows($result2) <> 0){
                $i = 0;
                while ($myrow2 = @DB_fetch_array($result2)){
                    ?>
                    <tr>
                        <td><input  readonly="readonly"type="text" size="15"  name="mould_number" id="mould_number" value="<?=$myrow2['mould_number']?>" />
                        <td ><input readonly="readonly" type="text" size="15" name="mold_class" id="mold_class" value="<?=$myrow2['mold_class']=='mud_core_mold' ? '泥芯模具':'成品模具' ?>"/></td>
                        </td>
                        <td><input  readonly="readonly"type="text" size="15"  name="product_map_number" id="product_map_number" value="<?=$myrow2['product_map_number']?>" />
                        </td>
                        <td><input  readonly="readonly"type="text" size="15"  name="mold_core_box_number" id="mold_core_box_number" value="<?=$myrow2['mold_core_box_number']?>" />
                        </td>
                        <td><?=$myrow2['stauts']=='0' ? '未领取':'已领取'  ?>
                        </td>
                        <td><?=$myrow2['flag']==0 ? '失效':'有效'  ?>
                        </td>

                    </tr>
                    <?php
                    $i++;
                }
            }
            ?>

        </table>
        <table>
            <tr><td><a class="btn btn-info btn-xs"  href="<?=$RootPath?>/addLoamcore.php?stockid=<?=$_POST['p_id']?> " target="_blank" >新增半成品泥芯</a></td></tr>
            <tr>
                <th width="150">半成品泥芯编号</th>
                <th width="150">半成品泥芯名称</th>
                <th width="100">库存数量</th>
                <th width="150">单个成品所需个数</th>
                <th width="150">是否开单</th>
                <th width="150">有效状态</th>
            </tr>
            <?php
            $sql2="select 	lcid,lcname,quantity,required_count,workorder,flag
                                from wip_loamcore
                                where  p_id='".$_POST['p_id']."'    ";
            $result2 = @DB_query($sql2,$db);
            if(@DB_num_rows($result2) <> 0){
                $a = 0;
                while ($myrow2 = @DB_fetch_array($result2)){
                    ?>
                    <tr>
                        <td ><input readonly="readonly" type="text" size="15" name="lcid[]" id="lcid" value="<?=$myrow2['lcid']?>" /></td>
                        <td><input  readonly="readonly"type="text" size="15" name="lcname" id="lcname" value="<?=$myrow2['lcname']?>" />
                        <td><input  readonly="readonly"type="text" size="15" name="quantity" id="quantity" value="<?=$myrow2['quantity']?>" />
                        <td><input  readonly="readonly"type="text" size="15" name="required_count" id="required_count" value="<?=$myrow2['required_count']?>" />
                        <td><?= $myrow2['workorder']=='0' ? '不开单':'开单'  ?> </td>
                        </td>
                        <td><?=$myrow2['flag']==0 ? '失效':'有效'?>
                        </td>
                     </tr>
                    <?php
                    $a++;
                }
            }
            ?>
        </table>
    </div>


<div class="centre">
	<input type="submit" name="Save" value="保存" >
    <?php
    if($_POST['flag']==1){
    ?>
	<input id="delete" type="submit" name="delete1" value="失效" >
        <?php
    }else{
        ?>
        <input id="delete" type="submit" name="delete0" value="有效" >
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
</div>



</form>
<?php
  include('includes/footer.inc');
?>