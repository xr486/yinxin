<?php

include('includes/session.inc');
$Title = _('成品工单退模具');

$ViewTopic= '成品工单退模具';
$BookMark = '成品工单退模具';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

unset($result);


if (isset($_POST['Save'])) {
    $errorflag = 0;
    $mojugeshu=0;//模具个数
    $mojuxinheshu=0;
    $dingdanzongliang=0;
    $time = strtotime(Date('Y-m-d H:i:s'));
    if ($errorflag == 0) {
        foreach ($_POST as $key => $value) {
            if (mb_substr($key, 0, 10) == 'UpdateLine') {
                $order_line_id = mb_substr($key, 10);
                $i = $_POST[$key];
                $mojugeshu++;
                $sql4 = "select mold_core_box_number  from wip_mold where mould_number='" . $_POST['moldid' . $i] . "' ";
                $result4 = DB_query($sql4, $db);//模具核心数
                $MYROW4= DB_fetch_array($result4);
				$mojuxinheshu=$MYROW4[0];
                $sql7="select model_input_quantity from  wip_jobs_all where wip_entity_name='".$_POST['wip_entity_name']."' ";
                $result7 = DB_query($sql7, $db);
                $myrow7=DB_fetch_array($result7);
                $dingdanzongliang = $myrow7['model_input_quantity'];
            }
        }
        if($mojugeshu==0||$mojuxinheshu==0){
            prnMsg( _('没有选择模具或者模具芯盒数为0'),'success');
        }else{
            $cishu=ceil($dingdanzongliang/($mojuxinheshu*$mojugeshu));
            foreach ($_POST as $key => $value) {
                if (mb_substr($key, 0, 10) == 'UpdateLine') {
                    $order_line_id = mb_substr($key, 10);
                    $i = $_POST[$key];
                    $sql6="update wip_mold a,wip_get_mold_detailed b set
                    a.stauts=0,
                    a.number_of_use=a.number_of_use+'".$cishu."',
                    b.isout=1,
                    a.who_get=''
                    where a.mould_number='".$_POST['moldid'.$i]."' and a.mold_class='".$_POST['mold_class']."' and b.wip_entity_name='".$_POST['wip_entity_name']."' and b.moldid='".$_POST['moldid'.$i]."'
                    ";
                    $result4 = DB_query($sql6,$db);
                }
            }
        }

    }
}
?>
<body>

<div id="CanvasDiv">
    <div id="BodyDiv">
        <div id="BodyWrapDiv">
            <p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="成品工单退模具" alt="成品工单退模具
">成品工单退模具</p>
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
                            <td>工单名称：</td>
                            <td><input type="text" required="required" name="wip_entity_name" id="wip_entity_name" value="<?=$_POST['wip_entity_name']?>">
                                <a class="btn btn-info btn-xs" id="btn_slect_customer<?=$i?>" hfre="###" title="选择客户">选择</a> </td>

                            <td>料号名称：</td>
                            <td><input type="text" required="required"  name="stockid" id="stockid" value="<?=$_POST['stockid']?>" ></td>

                            <td>开工数量：</td>
                            <td><input type="text" required="required"    name="quantity" id="quantity" value="<?=$_POST['quantity']?>" /></td>

                        </tr>
                        <tr>

                            <td>预计开工日期：</td>
                            <td><input type="text" required="required"    name="scheduled_start_date"  id="scheduled_start_date" value="<?=$_POST['scheduled_start_date']?>"/> </td>

                            <td>预计完成日期：</td>
                            <td><input type="text" required="required"    name="scheduled_completion_date"  id="scheduled_completion_date" value="<?=$_POST['scheduled_completion_date']?>"/> </td>


                            <td>工单建立日：</td>
                            <td><input type="text" required="required"    name="creation_date" id="creation_date" value="<?=$_POST['creation_date']?>" /> </td>

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
                        <input type="submit" name="Hearder" value="查询可退模具">
                    </div>
                    <input type="hidden" name="PageOffset" value="1"/><br/>
                    <input id="purchase_table_lastRow" name="purchase_table_lastRow" type=hidden value="">

                    <?php
                    if (isset($_POST['Hearder']) and $_POST['Hearder'] != '') {
                        ?>
                        <table id="purchase_table" cellpadding="2" class="selection">
                            <tr id="list-top">
                                <th width="100">模具图号</th>
                                <th width="100">模具类别</th>
                                <th width="100">使用次数</th>
                                <th  width="100" >是否领取</th>
                                <th  width="100" >是否退回</th>
                                <th  width="100">选择</th>
                            </tr>
                            <?php
                            if($_POST['mold_class']=="finished_mold"){
                                $sql2="
                                select a.moldid,b.mold_class,b.number_of_use,b.stauts,a.isout
                                from wip_get_mold_detailed a,wip_mold b
                                where a.wip_entity_name='".$_POST['wip_entity_name']."' and a.moldid=b.mould_number and b.mold_class='finished_mold' and a.isout=0   ";
                            }else{
                                $sql2="
                                select a.moldid,b.mold_class,b.number_of_use,b.stauts,a.isout
                                from wip_get_mold_detailed a,wip_mold b
                                where a.wip_entity_name='".$_POST['wip_entity_name']."' and a.moldid=b.mould_number and b.mold_class='mud_core_mold' and a.isout=0  ";
                            }
                            $result2 = DB_query($sql2,$db);
                            if(DB_num_rows($result2) <> 0){
                                $i = 0;
                                while ($myrow2 = DB_fetch_array($result2)){

                                    ?>

                                    <tr id="purchase_table_<?=$i?>" <?php echo $i>1&&$_POST['stockid'.$i]==''?'style="display:none"':''?> class="mouse click">

                                        <td><input  readonly="readonly"type="text" name="moldid<?=$i?>" id="moldid<?=$i?>" value="<?=$myrow2['moldid']?>" />
                                        </td>
                                        <td ><input readonly="readonly" type="text" name="mold_class<?=$i?>" id="mold_class<?=$i?>" value="<?=$myrow2['mold_class']=='mud_core_mold' ? '泥芯模具':'成品模具' ?>"/></td>
                                        <td><input readonly="readonly" type="text" name="number_of_use<?=$i?>" id="number_of_use<?=$i?>" value="<?=$myrow2['number_of_use']?>"/></td>
                                        <td><input readonly="readonly" type="text" name="stauts<?=$i?>" id="stauts<?=$i?>"  value="<?=$myrow2['stauts']==1 ? '领取':'未领取' ?>"/></td>
                                        <td><input readonly="readonly" type="text" name="isout<?=$i?>" id="isout<?=$i?>"  value="<?=$myrow2['isout']==0 ? '未退回':'退回'?>"/></td>
                                        <td><input  type="checkbox" name="UpdateLine<?=$i?>" value=<?=$i?> />
                                        <td><input  type="hidden" name="Subinventory_name<?=$i?>" id="text_slect_locationname<?=$i?>" value="<?=$_POST['Subinventory_name'.$i]?>" size="8" maxlength="25"/>

                                    </tr>


                                    <?php
                                    $i++;
                                }
                            }
                            ?>
                        </table>

                        <div class="centre">
                            <input type="submit" name="Save" value="退回">
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
            content:'url:BtnSearchWIPModify2.php?fwValue=&cat=buliao',
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
<?php
include('includes/footer.inc');
?>

