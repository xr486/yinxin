<?php

include('includes/session.inc');
$Title = _('成品选择下属材料');

$ViewTopic= '成品选择下属材料';
$BookMark = '成品选择下属材料';
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
        if($_POST['subordinate']=="finished_mold"){
            foreach ($_POST as $key => $value) {
                if (mb_substr($key, 0, 10) == 'UpdateLine') {
                    $order_line_id = mb_substr($key, 10);
                    $i = $_POST[$key];
                    $sql4 = "update wip_mold set product_number='".$_POST['pid']."' where mould_number='".$_POST['mould_number'.$i]."'  ";
                    $result4 = DB_query($sql4, $db);//模具核心数
                    if (@DB_num_rows($result4) == 0) {
                        unset($result4);
                        prnMsg(_('修改失败'), 'error');
                    }else{
                        unset($result4);
                        prnMsg(_('修改成功'), 'error');
                    }
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
            <p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="成品选择下属材料" alt="成品选择下属材料
">成品选择下属材料</p>
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
                            <td>成品料号选择：</td>
                            <td><input type="text" required="required" name="pid" id="pid" value="<?=$_POST['pid']?>">
                                <a class="btn btn-info btn-xs" id="btn_slect_customer<?=$i?>" hfre="###" title="选择客户">选择</a> </td>


                        <td>下属选择：</td>
                        <td>
                            <select name="subordinate" id="subordinate" >
                                <option  value="finished_mold" <?php if($_POST['subordinate']=='finished_mold'){ ?>selected="selected"<?php } ?> >成品模具</option>
                                <option  value="loamcore" <?php if($_POST['subordinate']=='loamcore'){ ?>selected="selected"<?php } ?> >半成品泥芯</option>
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

                            <?php
                            if($_POST['subordinate']=="finished_mold"){
                                $sql2="
                                select mold_class,mould_number
                                from wip_mold
                                where  mold_class='finished_mold'  and product_number='' ";
                            ?>
                                <table id="purchase_table" cellpadding="2" class="selection">
                                <tr id="list-top">
                                    <th width="100">模具类别</th>
                                    <th width="100">模具图号</th>
                                    <th  width="100">选择</th>
                                </tr>

                                <?php
                                $result2 = DB_query($sql2,$db);
                                if(DB_num_rows($result2) <> 0){
                                    $i = 0;
                                    while ($myrow2 = DB_fetch_array($result2)){
                                ?>
                                    <tr>
                                        <td ><input readonly="readonly" type="text" name="mold_class<?=$i?>" id="mold_class<?=$i?>" value="<?=$myrow2['mold_class']=='mud_core_mold' ? '泥芯模具':'成品模具' ?>"/></td>
                                        <td><input  readonly="readonly"type="text" name="mould_number<?=$i?>" id="mould_number<?=$i?>" value="<?=$myrow2['mould_number']?>" />
                                        </td>
                                        <td><input  type="checkbox" name="UpdateLine<?=$i?>" value=<?=$i?>/>
                                     </tr>
                                        <?php
                                        $i++;
                                    }
                                }
                                ?>
                                </table>
                                <div class="centre">
                                    <input type="submit" name="Save" value="选择">
                                </div>

                            <?php
                            }else{
                                $sql2="
                                select a.moldid,b.mold_class,b.number_of_use,b.stauts,a.isout
                                from wip_get_mold_detailed a,wip_mold b
                                where a.wip_entity_name='".$_POST['wip_entity_name']."' and a.moldid=b.mould_number and b.mold_class='mud_core_mold' and a.isout=0  ";
                            }


                                    ?>


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
            content:'url:BtnSearchEndproduct.php?fwValue=&cat=buliao',
            init:function(){
                par
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

