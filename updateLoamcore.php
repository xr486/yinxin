<?php 
	include('includes/session.inc');
	$Title = _('修改半成品泥芯料号');

	$ViewTopic= '修改半成品泥芯料号';
	$BookMark = '修改半成品泥芯料号';

	include('includes/header.inc');
	include('includes/SQL_CommonFunctions.inc');

	if (isset($_GET['lcid'])) {
		$lcid = $_GET['lcid'];
	}else if(isset($_POST['lcid'])){
		$lcid = $_POST['lcid'];
  }
	if (!isset($lcid)) {
		header('Location:searchEndProducts.php');
	}    

    $uploadflag = 1;
    if(isset($_POST['yes'])){
         if(!empty($_POST['lcid'])){
              $deleteSql = "update wip_loamcore set  flag=0  where lcid='". $_POST['lcid']."'";
             $sql="update wip_mold set flag=0 where loamcoreid='". $_POST['lcid']."' ";
              DB_query($deleteSql,$db);
             DB_query($sql,$db);

         }
        unset($_POST['yes']);

        echo '<div class="centre"><a href="'.$RootPath.'/searchLoamcore.php">返回查找半成品料号</a></div>';
        prnMsg(_('修改成功！！！！'), 'success');
        include('includes/footer.inc');
        exit;
    }

if(isset($_POST['no'])){
    if(!empty($_POST['lcid'])){
        $deleteSql = "update wip_loamcore set  flag=1  where lcid='". $_POST['lcid']."'";
        $sql="update wip_mold set flag=1 where loamcoreid='". $_POST['lcid']."' ";
        DB_query($deleteSql,$db);
        DB_query($sql,$db);

    }
    unset($_POST['no']);
    echo '<div class="centre"><a href="'.$RootPath.'/searchLoamcore.php">返回查找半成品料号</a></div>';
    prnMsg(_('修改成功！！！！'), 'success');
    include('includes/footer.inc');
    exit;
}
	if (isset($_POST['Save'])) {
			$time = time();
			$sql = "update wip_loamcore
                                set lcname='".$_POST['lcname']."',
                                    paint='".$_POST['paint']."',
                                    cprice=".$_POST['cprice'].",
                                    trueweight='".$_POST['trueweight']."',
                                    weight=".$_POST['weight'].",
									sandboxid ='".$_POST['sandboxid']."',
                                    workorder=".$_POST['workorder'].",
                                    p_id='".$_POST['p_id']."'
                              where lcid = '".$_POST['lcid']."'";
			$result = DB_query($sql,$db);

			prnMsg( _('料号更新成功！'), 'success');


	}

	$sql = "select lcid,lcname,paint,cprice,trueweight,weight,sandboxid,workorder,p_id,flag"
                
                . " from wip_loamcore where lcid='".$lcid."'";
        $sql = $sql." order by lcid";
	$result = DB_query($sql,$db);

	while ($v = DB_fetch_array($result)) {

		$_POST['lcid'] = $v['lcid'];
		$_POST['lcname'] = $v['lcname'];
		$_POST['paint'] = $v['paint'];
		$_POST['cprice'] = $v['cprice'];
		$_POST['trueweight'] = $v['trueweight'];
		$_POST['weight'] = $v['weight'];
		$_POST['sandboxid'] = $v['sandboxid'];
		$_POST['workorder'] = $v['workorder'];
		$_POST['p_id'] = $v['p_id'];
        $_POST['flag'] = $v['flag'];
	}
?>
<div class="centre"><a href="<?=$RootPath?>/searchLoamcore.php">返回查找半成品料号</a></div>
<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/maintenance.png" title="修改半成品料号" alt="修改半成品料号">修改半成品料号</p>
<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST" enctype="multipart/form-data">
<div>
	<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
<br>
<table class="selection">
	<tr>
		<td>半成品料号</td>
		<td><?=$_POST['lcid']?>
			<input type="hidden" name="lcid" value="<?=$_POST['lcid']?>">
		</td>
	</tr>
	 <tr>
                <td>名称</td>
                <td><input type="text" name="lcname" required="required" value="<?=$_POST['lcname']?>"></td>
                <td>对应的成品料号</td>
                <td>
                   <input type="text" id="p_id" name="p_id"  value="<?=$_POST['p_id']?>">
                   <a class="btn btn-info btn-xs" id="selectPId" hfre="###" title="选择成品料号">选择</a>
            
               </td>
                   
                </td>
                <td>所用沙箱</td>
                <td> <input type="text" id="sandboxid" name="sandboxid"  value="<?=$_POST['sandboxid']?>">
                    <a class="btn btn-info btn-xs" id="selectSandboxid" hfre="###" title="选择沙箱">选择</a></td>
            </tr>
            <tr>

            <td>油漆</td>
            <td><input type="text" name="paint" value="<?=$_POST['paint']?>"></td>
            <td>制芯单价</td>
            <td><input type="text" name="cprice" value="<?=$_POST['cprice']?>"></td>
            <td>泥芯重量</td>
            <td><input type="text" name="trueweight" value="<?=$_POST['trueweight']?>"></td>
            </tr>
            <tr>
                <td>铸件毛重</td>
                <td>
                    <input type="text" name="weight" value="<?=$_POST['weight']?>">
                </td>
                <td>是否需要开工单</td>
                <?php
                if($_POST['workorder'] == 0){
                    echo '<td><input type="radio" name="workorder" value="true"   >是
                        <input type="radio" name="workorder" value="false" checked=checked>否
                      </td>';
                } else {
                    echo '<td><input type="radio" name="workorder" value="true" checked=checked>是
                      <input type="radio" name="workorder" value="false">否
                      </td>';
                }
                ?>
                <td>是否有效</td>
                <td>
                    <div style="background-color: red;color: white;width:40px;">
                    <?= $_POST['flag']==0 ? '失效':'有效' ?></div>
                </td>
            </tr>
</table>
</div>
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

    <table>
        <tr><td><a class="btn btn-info btn-xs"  href="<?=$RootPath?>/Addloamcoremold.php?lcid=<?=$_POST['lcid']?> " target="_blank" >新增半成品泥芯模具</a></td>
        </tr>
        <tr>
            <th width="150">模具类别</th>
            <th width="150" >模具图号</th>
            <th width="150" >产品图号</th>
            <th width="150" >模具芯盒数</th>
            <th width="150" >领取状态</th>
            <th width="150" >有效状态</th>
        </tr>
        <?php
        $sql1="select mold_class,mould_number,product_map_number,mold_core_box_number,stauts,flag
                                from wip_mold
                                where mold_class='mud_core_mold' and   loamcoreid='".$_POST['lcid']."'  ";
        $result2 = @DB_query($sql1,$db);
        if(@DB_num_rows($result2) <> 0){
            $i = 0;
            while ($myrow2 = @DB_fetch_array($result2)){
                ?>
                <tr>
                    <td ><input readonly="readonly" type="text" size="15" name="mold_class" id="mold_class" value="<?=$myrow2['mold_class']=='mud_core_mold' ? '泥芯模具':'成品模具' ?>"/></td>
                    <td><input  readonly="readonly"type="text" size="15"  name="mould_number" id="mould_number" value="<?=$myrow2['mould_number']?>" />
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
</div>
</form>
<?php
  include('includes/footer.inc');
?>