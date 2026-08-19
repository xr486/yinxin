<?php 
	include('includes/session.inc');
	$Title = _('修改良品原因和报废原因');

	$ViewTopic= '修改良品原因和报废原因';
	$BookMark = '修改良品原因和报废原因';

	include('includes/header.inc');
	include('includes/SQL_CommonFunctions.inc');
   $reason_id = null;
	if (isset($_GET['reason_id'])) {
		$reason_id = $_GET['reason_id'];
	}else if(isset($_POST['reason_id'])){
		$reason_id = $_POST['reason_id'];
    }
    //var_dump($_GET['reason_id']);
	if (!isset($reason_id)) {
		header('Location:WIPSearchReasons.php');
	}    

    $uploadflag = 1;
   
    if(isset($_POST['delete'])){
         if(!empty($_POST['reason_id'])){

              $deleteSql = "delete from wip_reasons where reason_id='". $_POST['reason_id']."'";
              DB_query($deleteSql,$db);
              header('Location:WIPSearchReasons.php');
         }
    }
   

	if (isset($_POST['Save'])) {
	
	  if (empty($_POST['reason_detail'])) {
          $_POST['reason_detail'] ='';
       }	
      if (empty($_POST['reason_type'])) {
          $_POST['reason_type']=0;
       }
      if (empty($_POST['type'])) {
          $_POST['type'] =0;
       }
      if (empty($_POST['flag'])) {
          $_POST['flag'] =0;
       } 
       if($_POST['flag'] == '是'){
          $_POST['flag'] = 1;
       } else if($_POST['flag'] == '否'){
          $_POST['flag'] = 0;
       }
       $reason_detail = $_POST['reason_detail'];
       $reason_type = $_POST['reason_type'];
       $type = $_POST['type'];
       $flag = $_POST['flag'];

       // var_dump($reason_detail);
       // var_dump($reason_type);
       // var_dump($type);
       // var_dump($flag);
       //var_dump($reason_id);
			$time = time();
			$sql = "update wip_reasons set reason_detail='".$reason_detail."',
                                  reason_type='".$reason_type."',
                                  type='".$type."',
                                  flag='".$flag."'
                         where reason_id='".$reason_id."'";
			$result = DB_query($sql,$db);

			prnMsg( _('更新成功！'), 'success');
		

	}

	$sql = " select * from wip_reasons where reason_id= '".$reason_id . "'";
  //var_dump($_POST['reason_id']);
  $sql = $sql." order by reason_id";
	$result = DB_query($sql,$db);
	while ($v = DB_fetch_array($result)) {
		$_POST['reason_id'] = $v['reason_id'];
		$_POST['reason_detail'] = $v['reason_detail'];
		$_POST['reason_type'] = $v['reason_type'];
		$_POST['type'] = $v['type'];
		$_POST['flag'] = $v['flag'];
	} 
?>
<div class="centre"><a href="<?=$RootPath?>/WIPSearchReasons.php">返回查找良品原因和报废原因</a></div>
<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/maintenance.png" title="修改良品原因和报废原因" alt="修改良品原因和报废原因">修改良品原因和报废原因</p>
<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST" enctype="multipart/form-data">
<div>
	<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
<br>
<table class="selection">
	<tr>
		<td>原因编号</td>
		<td><?=$reason_id?>
			<input type="hidden" name="reason_id" value="<?=$reason_id?>">
		</td>
	</tr>
	<tr>
		<td>原因描述</td>
		<td><input type="text" name="reason_detail" value="<?=$_POST['reason_detail']?>" required="required"></td>
    <td>原因类型</td>
                <td>
                   <select name="reason_type">
                      <?php
                           if($_POST['reason_type'] == '不良'){
                            echo '
                             <option value="不良" selected="true">不良</option>
                             <option value="报废">报废</option>
                            ';
                           }
                           if($_POST['reason_type'] == '报废'){
                             echo '
                                <option value="不良">不良</option>
                             <option value="报废" selected="true">报废</option>
                             ';
                           }
                      ?>

                   </select>

                </td>

            
 
	</tr> 
    
       <tr>
           <td>工序</td>
                <td>
                  <select name="type">
                  <?php
                    
                     if($_POST['type']=='打磨'){
                         echo '
                       <option value="造型">造型</option>
                       <option value="浇铸">浇铸</option>
                       <option value="打磨" selected="true">打磨</option>
                       <option value="入库">入库</option>
                         ';
                     }
                     else if($_POST['type']=='造型'){
                        echo '
                       <option value="造型" selected="true">造型</option>
                       <option value="浇铸">浇铸</option>
                       <option value="打磨" >打磨</option>
                       <option value="入库">入库</option>
                         ';
                     }
                     else if($_POST['type']=='浇铸'){
                       echo '
                       <option value="造型">造型</option>
                       <option value="浇铸" selected="true">浇铸</option>
                       <option value="打磨" >打磨</option>
                       <option value="入库">入库</option>
                         ';
                     }

                    else if($_POST['type']=='入库'){
                        echo '
                       <option value="造型">造型</option>
                       <option value="浇铸">浇铸</option>
                       <option value="打磨" >打磨</option>
                       <option value="入库" selected="true">入库</option>
                         ';
                     }
                  ?>
                   </select>
                </td>
       </tr>
	         <tr>
                <td>是否生效</td>
                <td>
                  <select name="flag">
                  <?php
                      if(isset($_POST['flag']) &&  ($_POST['flag'] == 1)){
                          echo '<option selected="true">是</option>';
                          echo '<option>否</option>';
                      } else {
                         echo '<option selected="true">否</option>';
                          echo '<option >是</option>';
                      }
                  ?>
                  </select>
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
     });
   </script>
<div class="centre">
	<input type="submit" name="Save" value="保存" >
	<input id="delete" type="submit" name="delete" value="删除" >
	<script type="text/javascript">
		window.onload = function(){
			document.getElementById("delete").onclick = function(){
				var result = confirm("确认删除该不良或报废原因么？(删除无法恢复)");
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