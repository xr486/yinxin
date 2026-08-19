<?php

include('includes/session.inc');
$Title = _('工单领模具');

$ViewTopic= '工单领模具';
$BookMark = '工单领模具';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

unset($result);


if (isset($_POST['Save'])) {
    $errorflag = 1;
    $errorflag = 0;

    $time = strtotime(Date('Y-m-d H:i:s'));
    if ($errorflag == 0) {
        foreach ($_POST as $key => $value) {
            if (mb_substr($key, 0, 10) == 'UpdateLine') {
                $order_line_id = mb_substr($key, 10);
                $i = $_POST[$key];
                $sql3="insert into wip_get_mold_detailed(
                        wip_entity_name,
                        line,
                        productid,
                        moldid,
                        status,
                        last_update_date,
                        last_updated_by,
                        creation_date,
                        created_by
						)
						VALUES (
						'" . $_POST['wip_entity_name'] . "',
						'".$i."',
						'".$_POST['stockid']."',
						'" . $_POST['mould_number'. $i] . "',
						'1',
						'" . $time . "',
						'" . $_SESSION['UserID'] . "',
						'" . $time . "',
						'" . $_SESSION['UserID'] . "'
						)
						";
                $ErrMsg = _('更新不成功,原因');
                $result_invtrancsation1 = DB_query($sql3, $db, $ErrMsg);
                $sql4="update wip_mold set stauts='1',who_get='".$_POST['wip_entity_name']."' where mould_number='" . $_POST['mould_number'. $i] . "'";
                $ErrMsg = _('更新不成功,原因');
                $result_invtrancsation1 = DB_query($sql4, $db, $ErrMsg);
            }
        }


    }




}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>

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
        var metimgurl='/JXC/statics/base/images/';
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
            <p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="工单领模具" alt="工单领模具
">工单领模具</p>
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST"><input type="hidden" name="time"

                                                                                                                            value="<?=$time?>">
                <div>
                    <input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
                    <table class="selection">
                        <!--

                        /*
                         *
                                    '.$myrow['wip_entity_name'].':'.$myrow['stockid'].':'.$myrow['quantity'].':
                                    '.date("Y-m-d",$myrow['scheduled_start_date']).':'.date("Y-m-d",$myrow['scheduled_completion_date']).  ':'.$myrow['creation_date'].  '
                         */
                        -->
                        <tr>
                            <td>工单号码：</td>
                            <td><input type="text" required="required" name="wip_entity_name" id="wip_entity_name" value="<?=$_POST['wip_entity_name']?>">
                                <a class="btn btn-info btn-xs" id="btn_slect_customer<?=$i?>" hfre="###" title="选择客户">选择</a> </td>

                            <td>料号名称：</td>
                            <td><input type="text" readonly="readonly"  name="stockid" id="stockid" value="<?=$_POST['stockid']?>" ></td>

                            <td>开工数量：</td>
                            <td><input type="text" readonly="readonly"    name="quantity" id="quantity" value="<?=$_POST['quantity']?>" /></td>

                        </tr>
                        <tr>

                            <td>预计开工日期：</td>
                            <td><input type="text" readonly="readonly"    name="scheduled_start_date"  id="scheduled_start_date" value="<?=$_POST['scheduled_start_date']?>"/> </td>

                            <td>预计完成日期：</td>
                            <td><input type="text" readonly="readonly"    name="scheduled_completion_date"  id="scheduled_completion_date" value="<?=$_POST['scheduled_completion_date']?>"/> </td>



                            <td>工单建立日：</td>
                            <td><input type="text" readonly="readonly"   name="creation_date" id="creation_date" value="<?=$_POST['creation_date']?>" /> </td>


                            <td>模具类型：</td>
                            <td>
                                <select name="mold_class" id="mold_class" >
                                    <option  value="finished_mold" <?php if($_POST['mold_class']=='finished_mold'){ ?>selected="selected"<?php } ?> >成品模具</option>
                                    <option  value="mud_core_mold" <?php if($_POST['mold_class']=='mud_core_mold'){ ?>selected="selected"<?php } ?> >泥芯模具</option>
                                </select>
                            </td>
                        </tr>

                    </table>
                    <div class="centre">
                        <input type="submit" name="Hearder" value="查询可领模具">
                    </div>
                    <input type="hidden" name="PageOffset" value="1"/><br/>
                    <input id="purchase_table_lastRow" name="purchase_table_lastRow" type=hidden value="">

                    <?php
                    if (isset($_POST['Hearder']) and $_POST['Hearder'] != '') {
                        ?>
                        <table id="purchase_table" cellpadding="2" class="selection">
                            <tr id="list-top">
                                <th width="100">模具图号</th>
                                <th width="100">产品图号</th>
                                <th width="100">来模日期</th>
                                <th  width="100" >模具芯盒数</th>
                                <th  width="100" >模具本厂编号</th>
                                <th  width="100" >模具所有权</th>
                                <th  width="100" >使用次数</th>
                                <th  width="100" >领取状态</th>
                                <th  width="130" >当前工单领取状态</th>
                                <th  width="100">选择</th>
                            </tr>
                            <?php
                            if($_POST['mold_class']=='finished_mold'){
                                $sql2="
                                select DISTINCT a.mould_number,a.product_map_number,a.die_date,a.mold_core_box_number,a.mould_factory_no,a.mould_ownership,a.number_of_use,a.stauts,a.who_get from wip_mold a,wip_loamcore b
                        where a.product_number ='".$_POST['stockid']."' and a.mold_class='".$_POST['mold_class']."'
                         and b.workorder=0 and a.flag=1 ";
                            }else{
                                // $sql3="select lcid from wip_loamcore where p_id='".$_POST['stockid']."' and workorder=0 ";
                                // $result3 = DB_query($sql3,$db);
                                // $arr=array();
                                // $i=0;
                                // while ($myrow3 = DB_fetch_array($result3)) {
                                //     $arr[$i]=$myrow3['lcid'];
                                //     $i++;
                                // }
                                // var_dump($arr);
                        //         $sql2="
                        //         select a.mould_number,a.product_map_number,a.die_date,a.mold_core_box_number,a.mould_factory_no,a.mould_ownership,a.number_of_use,a.stauts from wip_mold a,wip_loamcore b
                        // where a.loamcoreid  in  (select lcid from wip_loamcore where p_id='".$_POST['stockid']."' and workorder=0) and a.mold_class='".$_POST['mold_class']."'
                        //  and b.workorder=0 
                          // ";

                                $sql2="
                                select DISTINCT mould_number,product_map_number,die_date,mold_core_box_number,mould_factory_no,mould_ownership,number_of_use,stauts,who_get from wip_mold 
                        where loamcoreid  in  (select lcid from wip_loamcore where p_id='".$_POST['stockid']."' and workorder=0)
                        and flag=1

                         ";

                            }
                            $result2 = DB_query($sql2,$db);
                            if(DB_num_rows($result2) <> 0){
                                $i = 0;
                                while ($myrow2 = DB_fetch_array($result2)){

                                    ?>

                                    <tr  class="mouse click">

                                        <td><input  readonly="readonly"type="text" name="mould_number<?=$i?>" id="mould_number<?=$i?>" value="<?=$myrow2['mould_number']?>" />
                                        </td>
                                        <td ><input readonly="readonly" type="text" name="product_map_number<?=$i?>" id="product_map_number<?=$i?>" value="<?=$myrow2['product_map_number']?>"/></td>
                                        <td><input readonly="readonly" type="text" name="die_date<?=$i?>" id="die_date<?=$i?>" value="<?=date('Y-m-d',$myrow2['die_date'])?>"/></td>
                                        <td><input readonly="readonly" type="text" name="mold_core_box_number<?=$i?>" id="mold_core_box_number<?=$i?>"  value="<?=$myrow2['mold_core_box_number']?>"/></td>
                                        <td><input readonly="readonly" type="text" name="mould_factory_no<?=$i?>" id="mould_factory_no<?=$i?>"  value="<?=$myrow2['mould_factory_no']?>"/></td>
                                        <td><input readonly="readonly" type="text" name="mould_ownership<?=$i?>" id="mould_ownership<?=$i?>"  value="<?=$myrow2['mould_ownership']?>"/></td>
                                        <td><input readonly="readonly" type="text" name="number_of_use<?=$i?>" id="number_of_use<?=$i?>"  value="<?=$myrow2['number_of_use']?>"/></td>
                                        <td><?=$myrow2['stauts']==0? '未被领取':'已被领取'?></td>
                                        <td><?php
                                        if($myrow2['who_get']==$_POST['wip_entity_name']){
                                            echo "自己领取";
                                        }elseif ($myrow2['who_get']==''||$myrow2['who_get']==null) {
                                            # code...
                                            echo "无人领取";
                                        }else{
                                            echo "他人领取";
                                        }?></td>
                                        <td><input  type="checkbox"  <?=$myrow2['stauts']==1? 'disabled="disabled"':'' ?> name="UpdateLine<?=$i?>" value=<?=$i?> />
                                        <td><input  type="hidden" name="Subinventory_name<?=$i?>" id="text_slect_locationname<?=$i?>" value="<?=$_POST['Subinventory_name'.$i]?>" size="8" maxlength="25"/>
                                        
                                    </tr>


                                    <?php
                                    $i++;
                                }
                            }
                            ?>
                        </table>

                        <div class="centre">
                            <input type="submit" name="Save" value="提交">
                        </div>
                        <?php
                    }
                    ?>
                    <input type="hidden" name="idcount" id='idcount' value="11"/>
                    <input type="hidden" name="JustSelectedACustomer" value="Yes"/>
                </div>
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



        $('#btn_slect_customer').dialog({
            title:'选择工单',
            width: '950px',
            height: 470,
            content:'url:BtnSearchWIPModify.php?fwValue=&cat=buliao',
            init:function(){
                this.content.document.getElementById('cat').value = 'buliao';
                this.content.document.getElementById('fwValue').value = '';
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

