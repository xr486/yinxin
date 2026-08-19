<?php

if (isset($_GET['pdata'])) {

    include_once("connect.php");
    $sql = "select tax_mount from tax_set a where tax_name = '" . $_GET['pdata'] . "'";
    $result_num = mysql_query($sql, $db);
    $res = mysql_fetch_assoc($result_num);
    echo $res['tax_mount'];
    return;
}
if (isset($_GET['data'])) {

    include_once("connect.php");
    $sql = "select * from vendors where vendor_code = '" . $_GET['data'] . "' and enable_flag='Y ";
    $result_num = mysql_query($sql, $db);
    $res = mysql_fetch_assoc($result_num);
    echo $res['vendor_name'] . ':' . $res['vendor_address'] . ':' . $res['vendor_contacts'] . ':' . $res['currencycode'];
    return;
}
if (isset($_GET['data2'])) {
    //$sql = "select * from customers where customer_name = '".$_GET['data']."'";
    include_once("connect.php");
    $sql = "select * from vendors where vendor_name = '" . $_GET['data2'] . "' and enable_flag='Y ";
    $result_num = mysql_query($sql, $db);
    $res_customer_name = mysql_fetch_assoc($result_num);
    echo $res_customer_name['vendor_code'] . ':' . $res_customer_name['vendor_address'] . ':' . $res_customer_name['vendor_contacts'] . ':' . $res_customer_name['currencycode'];
    return;
}
include('includes/session.inc');
$Title = _('生产外协申请');

$ViewTopic = '生产外协申请';
$BookMark = '生产外协申请';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

unset($result);


if (isset($_POST['Save'])) {
    $errorflag = 1;
    foreach ($_POST as $key => $value) {
        if ($value != '') {
            if (substr($key, 0, 7) == 'stockid') {
                $errorflag = 0;
                $i = substr($key, 7);
                if ($value != '') {
                    if ($_POST['uom' . $i] == '') {
                        $errorflag = 1;
                        prnMsg($value . '未填写单位，请填写单位！', error);
                    }

                    if ($_POST['quantity' . $i] == '') {
                        $errorflag = 1;
                        prnMsg($value . '未填写数量，请填写数量！', error);
                    }


                    $sql5 = "select count(*) cnt   from  pr_lines_all
		where wip_entity_name = '" . $_POST['wip_entity_name' . $i] . "'
		and operation_code = '" . $_POST['operation_code' . $i] . "' ";

                    $result5 = DB_query($sql5, $db);
                    $v5 = DB_fetch_array($result5);
                    if ($v5['cnt'] > 0) {
                        $errorflag = 1;
                        prnMsg($_POST['wip_entity_name' . $i] . '工单,制程' . $_POST['operation_code' . $i] . '已外协,不能重复！', error);

                    }


                }
            }
        }
    }


    if ($errorflag == 0) {
        foreach ($_POST as $key => $value) {
            if ($value != '') {
                if (substr($key, 0, 7) == 'stockid') {
                    $i = substr($key, 7);

                    $lineamount[$i] = $_POST['quantity' . $i] * $_POST['unitprice' . $i];

                }
            }
        }
    }

    $time = time();
    $time2 = $time - 10;
  
    if ($_SESSION['lastsearchtime'] > $time2) {
        $errorflag = 1;
        prnMsg($value . '重复提交！', error);
    }
    if ($errorflag == 0) {

        $sumamount = 0.00;
        $date1 = date('Ymd');
        $date = substr($date1, 2, 4);

        $sql_num = "select 	(
            CASE WHEN substr(max(pr_num) ,-2,1) = 0 THEN
                RIGHT (
                    '100' + (
                        max(substr(pr_num ,- 1)) + 1
                    ),
                    2
                )
            ELSE
                substr(max(pr_num),-2,2) + 1
            END
            ) pr_num from pr_headers_all where substr(pr_num,-10,8) = '" . $date1 . "'";
        $result_num = DB_query($sql_num, $db);
        $rownum = DB_num_rows($result_num);
        while ($v = DB_fetch_array($result_num)) {
            if ($v['pr_num'] == null) {
                $OrderNum = 'PR' . $date1 . '01';
            } else {
                $OrderNum = 'PR' . $date1 . $v['pr_num'];
            }
        }
        DB_Txn_Begin($db);
        $time = time();
        $date1 = date('Ymd');
        $date = substr($date1, 2, 6);
        $order_amount = 0;
        $line_num = 0;
        foreach ($_POST as $key => $value) {
            if ($value != '') {
                if (substr($key, 0, 7) == 'stockid') {
                    $i = substr($key, 7);
                    $lineamount[$i] = $_POST['quantity' . $i] * $_POST['unitprice' . $i];
                    if ($_POST['stockid' . $i] == '') {
                        $_POST['stockid' . $i] = 'NULL';
                        $bumishu[$i] = 0;
                    }
                    $line_num = $line_num + 1;
                    $estimate_date[$i] = 0;
                    $estimate_date[$i] = strtotime($_POST['estimate_date' . $i]);
                    $need_date[$i] = strtotime($_POST['need_date' . $i]);


                    $sql = "insert into pr_lines_all(status,pr_num,line,remark,stockid,uom,price,quantity,wip_entity_name,operation_code,line_amount,need_date,chang,kuan,gao,
                    creation_date,created_by,last_update_date,last_updated_by)
                    values('" . $status . "','" . $OrderNum . "','" . $line_num . "','" . $_POST['remark' . $i] . "','" . $_POST['stockid' . $i] . "','" . $_POST['UOM' . $i] . "','" . $_POST['unitprice' . $i] . "',
                    '" . $_POST['quantity' . $i] . "','" . $_POST['wip_entity_name' . $i] . "','" . $_POST['operation_code' . $i] . "','" . $lineamount[$i] . "','" . strtotime($_POST['need_date' . $i]) . "','" . $_POST['chang' . $i] . "','" . $_POST['kuan' . $i] . "','" . $_POST['gao' . $i] . "',
                    '" . $time . "','" . $_SESSION['UserID'] . "','" . $time . "','" . $_SESSION['UserID'] . "') ";


                    $result = DB_query($sql, $db);


                }
            }
        }
        $sql2 = "insert into pr_headers_all(status,pr_num,remark,order_type,need_date,depart_name,all_amount,
            creation_date,created_by,last_update_date,last_updated_by)
            value('" . $status . "','" . $OrderNum . "','" . $_POST['remark'] . "','" . $_POST['order_type'] . "','" . strtotime($_POST['need_date']) . "',
            '" . $_POST['depart_name'] . "','" . $all_amount . "','" . $time . "','" . $_SESSION['UserID'] . "',
            '" . $time . "','" . $_SESSION['UserID'] . "')";


        $result = DB_query($sql2, $db);
        $_SESSION['lastsearchtime'] = $time;
        DB_Txn_Commit($db);
        prnMsg('生产外协申请编号' . $OrderNum . '建立成功！', success);


        header("Location: SussCreate.php?OrderNum=" . $OrderNum . "&type=OspOrderCreate");
    }

}

?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>新建订单</title>
    <link rel="shortcut icon" href="/favicon.ico"/>
    <link rel="icon" href="/favicon.ico"/>
    <meta http-equiv="Content-Type" content="application/html; charset=utf-8"/>
    <link href="/css/xenos/default.css" rel="stylesheet" type="text/css"/>
    <script type="text/javascript" src="./JXC/javascripts/miscfunctions.js"></script>
    <script type="text/javascript" src="./JXC/javascripts/wdatepicker.js"></script>
    <script type="text/javascript">var basepath = './JXC/statics/base/images';</script>
    <script type="text/javascript" src="./JXC/statics/base/js/metvar.js"></script>
    <script type="text/javascript" src="./JXC/statics/base/js/jQuery1.7.2.js"></script>
    <script type="text/javascript" src="./JXC/statics/base/js/uploadify/jquery.uploadify.v2.1.4.min.js"></script>
    <script type="text/javascript" src="./JXC/statics/base/js/iframes.js"></script>
    <script type="text/javascript" src="./JXC/statics/base/js/cookie.js"></script>
    <script type="text/javascript" src="./JXC/statics/base/js/jquery.livequery.js"></script>

    <link rel="stylesheet" href="jquery.ui.autocomplete.css">
    <script type="text/javascript" src="ui/jquery.ui.core.js"></script>
    <script type="text/javascript" src="ui/jquery.ui.widget.js"></script>
    <script type="text/javascript" src="ui/jquery.ui.position.js"></script>
    <script type="text/javascript" src="ui/jquery.ui.autocomplete.js"></script>

    <script src="./JXC/javascript/jquery-1.7.2.min.js"></script>
    <script src="./JXC/javascript/lhgdialog.min.js?self=true&skin=chrome"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="/javascript/bootstrap.min.js"></script>

    <script type="text/javascript">
        /*ajax执行*/
        var lang = 'cn';
        var metimgurl = './JXC/statics/base/images/';
        var depth = '';
        $(document).ready(function () {
            ifreme_methei();
        });
    </script>
    <script type="text/javascript">
        function metreturn(url) {
            if (url) {
                location.href = url;
            } else if ($.browser.msie) {
                history.go(-1);
            } else {
                history.go(-1);
            }
        }

        function addsave() {

            var v = $('#idcount').val();
            $("#purchase_table_" + v).css("display", "");
            var c = parseInt(v) + 1;
            $('#idcount').val(c);
        }

    </script>
</head>
<body>

<div id="CanvasDiv">
    <div id="BodyDiv">
        <div id="BodyWrapDiv">
            <p class="page_title_text"><img
                        src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png"
                        title="生产外协申请" alt="生产外协申请">生产外协申请</p>
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method="POST">
                <input type="hidden" name="time" value="<?= $time ?>">
                <div>
                    <input type="hidden" name="FormID" value="<?php echo $_SESSION['FormID']; ?>">
                    <?php
                    if (!isset($_POST['schedule_payment_date'])) {
                        $_POST['schedule_payment_date'] = Date('Y-m-d');
                    }
                    if (!isset($_POST['need_date'])) {

                        $_POST['need_date'] = Date('Y-m-d', mktime(0, 0, 0, date("m"), date("d") + 1, date("Y")));
                    }
                    if (!isset($_POST['OrderDate'])) {
                        $_POST['OrderDate'] = Date('Y-m-d');
                    }
                    if (!isset($_POST['delivery_date'])) {
                        $_POST['delivery_date'] = Date('Y-m-d');
                    }
                    if (!isset($_POST['dangqian_date'])) {
                        $_POST['dangqian_date'] = Date('Y-m-d');
                    }
                    if (!isset($_POST['dangqian'])) {

                        $_POST['dangqian'] = Date('Y-m-d', mktime(0, 0, 0, date("m"), date("d") + 1, date("Y")));
                    }


                    ?>

                    <!-- <div class="centre">
                        <input type="submit" name="Hearder" value="确认采购单头信息">

                    </div> -->
                    <label id="alert" style="color:red;"></label>
                    <input type="hidden" name="PageOffset" value="1"/><br/>
                    <?php
                    // if (isset($_POST['vendorname']) and $_POST['vendorname'] != '') {
                    ?>
                    <input id="purchase_table_lastRow" name="purchase_table_lastRow" type=hidden value="">

                    <div class="centre">
                        <p id="Prompt" style="color: red;font-size: 20px"></p>
                    </div>
                    <div class="text-nav-table">
                        <table id="purchase_table" cellpadding="2" class="selection">
                            <tr id="list-top">
                                <th width="150">工单号</th>

                                <th width="80">料号</th>
                                <th>名称</th>
                                <th>规格型号</th>
                                <th width="10">单位</th>
                                <th width="10">工单单量</th>
                                <th width="30">工序名</th>
                                <th width="10">本次下单量</th>
                                <th width="20">需求日期</th>
                                <th width="20">备注</th>

                                <th width="50" align="center">操作</th>
                            </tr>
                            <?php for ($i = 1; $i <= 50; $i++) { ?>

                                <tr id="purchase_table_<?= $i ?>" <?php echo $i > 5 && $_POST['stockid' . $i] == '' ? 'style="display:none"' : '' ?>
                                    class="mouse click">
                                    <!--采购单号-->
                                    <td><input type="text" readonly="readonly" style="background-color:#D2E9FF;"
                                               name="wip_entity_name<?= $i ?>" id="wip_entity_name<?= $i ?>"
                                               value="<?= $_POST['wip_entity_name' . $i] ?>" size="20" maxlength="25"/>
                                        
                                           <image class="select_img" src="img/search.png" id="btn_slect_buliao<?= $i ?>"/>
                                    </td>


                                    <td><input type="text" readonly="readonly" name="stockid<?= $i ?>"
                                               id="stockid<?= $i ?>" value="<?= $_POST['stockid' . $i] ?>" size="18"
                                               maxlength="125"/>
                                    </td>


                                    <td><input readonly="readonly" type="text" autocomplete="off"
                                               name="item_name<?= $i ?>" id="item_name<?= $i ?>"
                                               value="<?= $_POST['item_name' . $i] ?>" size="5" maxlength="60"/></td>

                                    <td><input readonly="readonly" type="text" autocomplete="off"
                                               name="item_desc<?= $i ?>" id="item_desc<?= $i ?>"
                                               value="<?= $_POST['item_desc' . $i] ?>" size="5" maxlength="60"/></td>

                                    <td><input readonly="readonly" type="text" name="uom<?= $i ?>" id="uom<?= $i ?>"
                                               value="<?= $_POST['uom' . $i] ?>" size="1" maxlength="40"/></td>

                                    <td><input type="text" readonly="readonly" id="start_quantity<?= $i ?>"
                                               name="start_quantity<?= $i ?>"
                                               value="<?= $_POST['start_quantity' . $i] ?>" size="3" maxlength="10"/>
                                    </td>
                                    <td><input type="text" onblur="checkneedwip(<?= $i ?>)"
                                               style="background-color:#D2E9FF;" name="operation_code<?= $i ?>"
                                               id="operation_code<?= $i ?>" value="<?= $_POST['operation_code' . $i] ?>"
                                               size="4" maxlength="25"/></td>

                                    <td><input style="background-color:#D2E9FF;" id="quantity<?= $i ?>" type="text"
                                               autocomplete="off" name="quantity<?= $i ?>" class="number"
                                               value="<?= $_POST['quantity' . $i] ?>" size="5" maxlength="10"
                                               onkeyup="checkqty(<?= $i ?>)" onblur="checkall()"/></td>


                                    <td><input type="text" id="add_need_date<?= $i ?>" name="need_date<?= $i ?>"
                                               onfocus="WdatePicker()" value="<?= $_POST['need_date' . $i] ?>" size="9"
                                               maxlength="10"/></td>


                                    <td>
                                        <input type="text" name="remark<?= $i ?>" value="<?= $_POST['remark' . $i] ?>"
                                               size="10" maxlength="200"/>
                                    </td>


                                    <td><a onclick="delettr($(this));" style="padding:0px 5px;"
                                           href="javascript:;">删除</a></td>


                                    <input type="hidden" name="po_line_id<?= $i ?>" id="text_slect_line_id<?= $i ?>"
                                           value="<?= $_POST['po_line_id' . $i] ?>" size="8" maxlength="25"/>


                                </tr>

                            <?php } ?>
                        </table>
                    </div>


                    <div class="centre">
                        <a onclick="addsave();">添加行</a>

                    </div>

                    <div class="centre">
                        <input type="submit" id="submit" name="Save" value="保存">
                    </div>
                    <?php
                    // }
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


    function psel() {
        var name = $('#text_slect_tax_name').val()
        $.get("", "pdata=" + name, function (res) {
            name = res.split(":")
            $("#text_slect_tax_rate").val(name[0])

        })
        ;
        checkall();
    }

    window.onload = function () {
        document.getElementById("submit").onclick = function () {
            return check();
        }

        function check() {
            var po_nums = new Array();
            var lines = new Array();
            for (var i = 1; i < 50; i++) {
                var po_num = document.getElementById("text_slect_po_num" + i).value;
                var line = document.getElementById("text_slect_line" + i).value;
                //如果input中有数据
                if (po_num != null && po_num != "") {
                    for (var p = 1; p < po_nums.length; p++) {
                        //alert("xunhuan");
                        //如果采购单号相同
                        if (po_nums[p] == po_num) {
                            //alert('a');
                            //接着判断行数是否相同
                            if (lines[p] == line) {
                                document.getElementById("alert").innerHTML =
                                    "错误提醒: 不能选择相同的采购单号+行号.<br>采购单号为:" + po_num + ".<br>行号为：" + line;
                                ""
                                ;
                                return false;
                            } else {
                            }
                        }
                    }
                    po_nums[i] = po_num;
                    lines[i] = line;
                    //alert("continue");
                    continue;
                } else {
                    //下面数据退出循环
                    //alert("return");
                    return;
                }
            }
        }
    }


    function checkneedwip(s1) {

        var need_date = document.getElementById("need_date").value;

        document.getElementById("add_need_date" + s1).value = need_date;

    }

    function checkqty(s) {
        var wait_quantity = document.getElementById("wait_quantity" + s).value;
        var shuliang = document.getElementById("quantity" + s).value;
        var need_date = document.getElementById("need_date" + s).value;
        var price = document.getElementById("text_slect_unit_price" + s).value;
        var dangqian_date = document.getElementById("dangqian_date").value;
        var dangqian = document.getElementById("dangqian").value;
        if (parseFloat(shuliang) > parseFloat(wait_quantity)) {
            document.getElementById("Prompt").innerHTML = "数量不可以大于待转量！";
            document.getElementById("quantity" + s).value = null;
            document.getElementById("quantity" + s).focus();
        } else if (need_date < dangqian_date) {

            document.getElementById("need_date" + s).value = dangqian;

        } else if (parseFloat(shuliang) <= 0) {
            document.getElementById("Prompt").innerHTML = "数量必须大于0！";
            document.getElementById("quantity" + s).value = null;
            document.getElementById("quantity" + s).focus();
        } else if (parseFloat(price) < 0) {
            document.getElementById("Prompt").innerHTML = "价格不能小于0！";
            document.getElementById("text_slect_unit_price" + s).value = null;
            document.getElementById("text_slect_unit_price" + s).focus();
        } else {
            document.getElementById("Prompt").innerHTML = "";
        }
        document.getElementById("line_amount" + s).value = Math.round(Number(shuliang * price) * 100) / 100;

    }


    function checkall() {
        var allamount = 0;

        var tax_rate = document.getElementById("text_slect_tax_rate").value;
        var tax_flag = document.getElementById("text_slect_tax_flag").value;
        var all_rate = Number(1) + Number(tax_rate);
        for (var i = 1; i < 50; i++) {
            if (document.getElementById("line_amount" + i) == null) {
                p = 0;
            } else {

                var shuliang = document.getElementById("quantity" + i).value;
                var danjia = document.getElementById("text_slect_unit_price" + i).value;

                if (shuliang == "") {
                    shuliang = 0;
                }
                if (danjia == "") {
                    danjia = 0;
                }


                if (shuliang > 0) {
                    document.getElementById("line_amount" + i).value = Math.round(Number(shuliang) * Number(danjia) * 100) / 100;

                }
                var lineamount = 0
                var lineamount = document.getElementById("line_amount" + i).value;

                if (lineamount > 0) {
                    allamount = Number(allamount) + Number(lineamount);
                    //如果input中有数据
                }
            }
        }


        if (tax_flag == 'N') {
            var tax_amount = Math.round(Number(allamount) * Number(tax_rate) * 100) / 100;
            var no_tax_amount = allamount;
            var han_tax_amount = Number(tax_amount) + Number(allamount);

        } else {
            var no_tax_amount = Math.round(Number(allamount) / Number(all_rate) * 100) / 100;
            var tax_amount = Number(allamount) - Number(no_tax_amount);
            var han_tax_amount = allamount;
        }


        document.getElementById("po_all_amount").value = Math.round(Number(han_tax_amount) * 100) / 100;
        document.getElementById("tax_amount").value = Math.round(Number(tax_amount) * 100) / 100;
        document.getElementById("all_line_amount").value = Math.round(Number(no_tax_amount) * 100) / 100;


    }


    $(document).ready(function () {
        $('.divToilet table tr td a').click(function () {
            $(this).parent('td').toggleClass('highlight');
            if (!($(this).parent('td').hasClass('highlight'))) {
                $(this).next().val('0');
            } else {
                $(this).next().val('1');
            }
        });
        <?php for($i = 1;$i <= 50;$i++){?>
        $('#btn_slect_buliao<?=$i?>').dialog({
            title: '选择待下外协工单',
            width: '1060px',
            height: 470,
            content: 'url:Searchwaitosppo.php?fwValue=<?=$i?>&cat=<?=$_POST['vendorcode']?>',
            init: function () {
                this.content.document.getElementById('cat').value = 'buliao';
                this.content.document.getElementById('fwValue').value = '<?=$i?>';
            }
        });
        <?php }?>

        <?php for($i = 1;$i <= 50;$i++){?>
        $('#btn_slect_subcode<?=$i?>').dialog({
            title: '选择仓库',
            width: '600px',
            height: 370,
            content: 'url:Searchsubcode.php?fwValue=<?=$i?>&cat=buliao',
            init: function () {
                this.content.document.getElementById('cat').value = 'buliao';
                this.content.document.getElementById('fwValue').value = '<?=$i?>';
            }
        });
        <?php }?>


        $('#btn_slect_vendor').dialog({
            title: '选择供应商',
            width: '950px',
            height: 470,
            content: 'url:BtnSearchVendor.php?fwValue=&cat=buliao',
            init: function () {
                this.content.document.getElementById('cat').value = 'buliao';
                this.content.document.getElementById('fwValue').value = '';
            }
        });
        $('#btn_slect_vendor_a').dialog({
            title: '选择供应商',
            width: '950px',
            height: 470,
            content: 'url:BtnSearchVendor111.php?fwValue=&cat=buliao',
            init: function () {
                this.content.document.getElementById('cat').value = 'buliao';
                this.content.document.getElementById('fwValue').value = '';
            }
        });

        $('#btn_slect_term_name').dialog({
            title: '选择付款条件',
            width: '550px',
            height: 470,
            content: 'url:BtnSearchterm.php?fwValue=&cat=buliao',
            init: function () {
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
                for (var i = 0; i < strs.length; i++) {
                    theRequest[strs[i].split("=")[0]] = (strs[i].split("=")[1]);
                }
            }
            return theRequest;
        }


    });
    $(function () {
        $("#text_slect_vendor").autocomplete({
            source: "autosearchvendor.php",
            minLength: 2,
            autoFocus: true
        });
    });
    $(function () {
        $("#text_slect_name").autocomplete({
            source: "autosearchvendor2.php",
            minLength: 2,
            autoFocus: true
        });
    });

    function sel() {
        var name = $('#text_slect_vendor').val()
        $.get("", "data=" + name, function (res) {
            name = res.split(":")
            $("#text_slect_name").val(name[0])
            $("#text_slect_address").val(name[1])
            $("#text_slect_contacts").val(name[2])
            $("#text_slect_currency_code").val(name[3])
        })
    }

    function sel_name() {
        var name = $('#text_slect_name').val()
        $.get("", "data2=" + name, function (res_customer_name) {
            name = res_customer_name.split(":")
            $("#text_slect_vendor").val(name[0])
            $("#text_slect_address").val(name[1])
            $("#text_slect_contacts").val(name[2])
            $("#text_slect_currency_code").val(name[3])
        })
    }
</script>
</body>

</html>
<?
include('includes/footer.inc');
?>

