<?php
putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");
ob_start();
include('includes/session.inc');
$Title = _('BOM替代料添加');

$ViewTopic= 'BOM替代料添加';
$BookMark = 'BOM替代料添加';
include('includes/header2.inc');
include('includes/SQL_CommonFunctions.inc');

if (isset($_GET['fwValue'])) {
    $_SESSION['fwValue'] = $_GET['fwValue'];
} 


 if (isset($_GET['item_num'])) {
    $_SESSION['item_num'] = $_GET['item_num'];
} 

if (isset($_POST['item_num'])) {
   $_SESSION['item_num'] = $_POST['item_num'];
}


if (isset($_GET['component_item'])) {
     $_SESSION['component_item'] = $_GET['component_item'];
}

if (isset($_POST['component_item'])) {
    $_SESSION['component_item']= $_POST['component_item'];
}

 
unset($result);



//替代料临时添加
    if (isset($_POST['Save'])) {
        if($_POST['component_item']=="" ||  $_POST['item_num']==""){
             prnMsg(_('子料和工序不能为空请关闭后选择'), 'error');die;
        }
        $errorflag = 0;
        $lineflag=0;

     for ($i=1;$i<=50;$i++){
          if($_POST['substitute_item'.$i]<>''){
            if  ($_POST['quantity'.$i] =='') {
                 prnMsg(_('数量请填写！'), 'error');
                $errorflag = 2;
                $lineflag=0;

            } else {
              $lineflag=1;
            }

        }
     }

    if  ( $lineflag == 0 ) {
        prnMsg(_('行资料请输入！'), 'error');
     }


    if ($errorflag == 0 and $lineflag == 1) {
            DB_Txn_Begin($db);
            $time = time();
            $LINE_NUM=0;
        for ($i=1;$i<=50;$i++){
            if($_POST['substitute_item'.$i]<>''&&$_POST['substitute_item'.$i]!=$_POST['component_item']){
                $line_num=$line_num+1;
                $sql="insert into bom_ts_substitutes(assembly_item_no,component_item,item_num,
                    substitute_item,uom,substitute_item_quantity,substitute_remarks)values
                        ('".$_POST['assembly_item_no']."',
                        '".$_POST['component_item']."',
                        '".$_POST['item_num']."',
                        '".$_POST['substitute_item'.$i]."',
                        '".$_POST['UOM'.$i]."',
                        '".$_POST['quantity'.$i]."',
                        '".$_POST['substitute_remarks'.$i]."')";
                         // echo $sql;
                         $result = DB_query($sql,$db);
					//	 echo $sql;
                    }
              }
            DB_Txn_Commit($db);
            prnMsg('BOM新增替代料成功',success);
                }
            }


            $sql2="select a.*,b.item_name,b.item_desc from bom_ts_substitutes a,sf_item_no b
                   where assembly_item_no='".$_SESSION['fwValue']."' 
				   and component_item='".$_SESSION['component_item']."' 
				   and item_num='".$_SESSION['item_num']."' 
				   and a.substitute_item=b.item_no
				    ";
			//echo $sql2;
            $result2 = DB_query($sql2,$db);
            $data2=mysqli_fetch_all($result2,MYSQL_ASSOC);

            //点击删除
            $sql3="delete from bom_ts_substitutes where id='".$_GET['id']."'";
            $result3 = DB_query($sql3,$db);
       ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>BOM替代料修改</title>
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
/*ajax执行*/
var lang = 'cn';
var metimgurl='./statics/base/images/';
var depth='';
$(document).ready(function(){
    ifreme_methei();
});
</script>
<script type="text/javascript">
function metreturn(url){
    if(url){
        location.href=url;
    }else if($.browser.msie){
        history.go(-1);
    }else{
        history.go(-1);
    }
}

function addsave()
{

    var v = $('#idcount').val();
    $("#purchase_table_"+v).css("display","");
    var c = parseInt(v) + 1;
    $('#idcount').val(c);
}

 </script>

</head>
<body>

 <div id="CanvasDiv">
    <div id="BodyDiv">
        <div id="BodyWrapDiv">
            <p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="BOM替代料修改" alt="BOM替代料添加">替代料添加</p>
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST"><input type="hidden" name="time" value="<?=$time?>">
                <div>
                <input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
                <div class="text-nav">
                <div class="text-nav-1"><div>母料号</div><input type="text" name="assembly_item_no" value="<?php echo $_SESSION['fwValue']; ?>" readonly="readonly">
                </div>
				<div class="text-nav-1"><div>序号</div><input type="text" id="item_num" name="item_num" value="" readonly="readonly"></div>
                <div class="text-nav-1"><div>子料号</div><input type="text" id="component_item" name="component_item" value="" readonly="readonly"></div>
                
                </div>




                <table id="purchase_table" cellpadding="2" class="selection">
                    <tr id="list-top">
                        
                        <th bgcolor="#87CEFA" width="50" >替代料号</th>
						 <th bgcolor="#87CEFA" width="50" >料号名称</th>
						  <th bgcolor="#87CEFA" width="50" >规格型号</th>
                        <th bgcolor="#87CEFA" width="50" >单位</th>
                        <th bgcolor="#87CEFA" width="50">数量</th>
                        <th bgcolor="#87CEFA" width="50">备注</th>
                        <th bgcolor="#87CEFA" width="60">操作</th>
                    </tr>
                    <!-- 循环遍历查询的数据 -->
                    <?php foreach ($data2 as $k => $v):?>
                    <tr>
                        
                        <td><?php echo $v['substitute_item']?></td>
                        <td><?php echo $v['item_name']?></td>
                        <td><?php echo $v['item_desc']?></td>
                        <td><?php echo $v['uom']?></td>
                        <td><?php echo $v['substitute_item_quantity']?></td>
                        <td><?php echo $v['substitute_remarks']?></td>
                        <td><a href="bomtidai.php?id=<?php echo $v['id'] ?>">删除</a></td>
                    </tr>
                    <?php endforeach;?>
                </table>






                 <span><center> <h4>新增替代料</h4></center> </span>
                    <input type="hidden" name="PageOffset" value="1"/>
                    <?php
                        if (1==1)  {
                    ?>
              <input id="purchase_table_lastRow" name="purchase_table_lastRow" type=hidden value="">
                    <div class="text-nav-table">


                    <table id="purchase_table" cellpadding="2" class="selection">
                    <tr id="list-top">
                    <th bgcolor="#87CEFA" width="250">替代料号</th>
                    <th bgcolor="#87CEFA" width="180">料号名称</th>
                    <th bgcolor="#87CEFA" width="180">规格型号</th>
                    <th bgcolor="#87CEFA" width="30" >单位</th>
                    <th bgcolor="#87CEFA" width="10">数量</th>
                    <th bgcolor="#87CEFA" width="150">备注</th>
                    <th bgcolor="#87CEFA" width="50" align="center">操作</th>
                    </tr>
                    <?php for($i=1;$i<=50;$i++){?>

                    <tr id="purchase_table_<?=$i?>" <?php echo $i>1&&$_POST['substitute_item'.$i]==''?'style="display:none"':''?> class="mouse click">

                <td><input type="text" name="substitute_item<?=$i?>" id="text_slect_item_no<?=$i?>" value="<?=$_POST['substitute_item'.$i]?>" size="20" maxlength="50">
               <a class="btn btn-info btn-xs" id="btn_slect_buliao<?=$i?>" hfre="###" title="选择材料">选择</a></td>


                      <td><input type="text" readonly="readonly" name="item_name<?=$i?>" id="text_slect_item_name<?=$i?>" value="<?=$_POST['item_name'.$i]?>" size="15" maxlength="150"></td>

                      <td><input type="text" readonly="readonly"  name="item_desc<?=$i?>" id="text_slect_item_desc<?=$i?>" value="<?=$_POST['item_desc'.$i]?>" size="15" maxlength="150"></td>

                       <td><input type="text" readonly="readonly"  name="UOM<?=$i?>" id="text_slect_units<?=$i?>" value="<?=$_POST['UOM'.$i]?>" size="4" maxlength="4"></td>

                        <td><input type="text"  class="number" name="quantity<?=$i?>" value="<?=$_POST['quantity'.$i]?>" size="6" maxlength="10"></td>

                        <td><input type="text"  name="substitute_remarks<?=$i?>" value="<?=$_POST['substitute_remarks'.$i]?>" size="18" maxlength="100">
                        <input type="hidden"  name="assembly_item<?=$i?>" value="<?= $_SESSION['fwValue'] ?>" size="18" maxlength="100">
                        <input type="hidden"  name="component_item<?=$i?>" value="<?= $_SESSION['component_item'] ?>" size="18" maxlength="100">
                        </td>


                      <td>  <a onclick="delettr($(this));" style="padding:0px 5px;" href="javascript:;">删除</a></td>



                    </tr>
                    <?php }?>

                    </table>
                    </div>
                   <div class="centre">
                    <a onclick="addsave();">添加行</a>
                    </div>
                    <div class="centre">
                    <input type="submit" name="Save" value="提交"/>
                    </div>
                    <input type="hidden" name="idcount" id='idcount' value="11"/>
                    <input type="hidden" name="JustSelectedACustomer" value="Yes"/>
                    <input type="hidden" name="fwValue" value="<?php echo $_SESSION['fwValue'] ?>"/>
                    <input type="hidden" name="item_num" value="<?php echo $_SESSION['item_num'] ?>"/>
					 <input type="hidden" name="component_item" value="<?php echo $_SESSION['component_item'] ?>"/>
                </div>


    <?php
        }
    ?>

            </form>
        </div>
    </div>

    <div id="FooterDiv">
        <div id="FooterWrapDiv">
        </div>
    </div>
</div>


    <script type="text/javascript">
    $(document).ready(function(){

        $('.divToilet table tr td a').click(function(){
            $(this).parent('td').toggleClass('highlight');
            if(!($(this).parent('td').hasClass('highlight'))) {
                $(this).next().val('0');
            }else {
                $(this).next().val('1');
            }
        });
        <?php for($i=1;$i<=50;$i++){?>
        $('#btn_slect_buliao<?=$i?>').dialog({
            title:'添加替代料',
            width: '1000px',
            height: 470,

            content:'url:Searchitemforbom.php?fwValue=<?=$i?>&cat=buliao',
            init:function(){
                this.content.document.getElementById('cat').value = 'buliao';
                this.content.document.getElementById('fwValue').value = '<?=$i?>';
            }
        });

        <?php }?>



        $('#btn_slect_vendor').dialog({
            title:'选择成品料号',
            width: '950px',
            height: 470,
            content:'url:BtnSearchFinishItem.php?fwValue=&cat=buliao',
            init:function(){
                this.content.document.getElementById('component_item').value = 'buliao';
                this.content.document.getElementById('fwValue').value = '';
                this.close();
            }
        });
        //Function to get URL arguments

        function getRequest() {
            var url = location.search; //获取url中"?"符后的字串
            var theRequest = new Object();
            if (url.indexOf("?") != -1) {
                var str = url.substr(1);
                strs = str.split("&");
                for(var i = 0; i < strs.length; i ++) {
                    theRequest[strs[i].split("=")[0]]=(strs[i].split("=")[1]);
                }
            }
            return theRequest;
        }


    });
</script>
</body>

</html>
<?
include('includes/footer.inc');
?>




