<?php
include('includes/login2.inc');

function db_sql($sql, $type = 1)
{
    $arr = array();
    $sql = mysql_query($sql);
    // $row = mysql_fetch_array($sql);--这里执行会导致type=2再执行一次，里面第一行资料被捞过从第二行开始获取,结果少资料
    if ($type == 1) return mysql_fetch_array($sql);
    if ($type == 2) {
        while ($row = mysql_fetch_array($sql)) {
            $arr[] = $row;
        }
        return $arr;
    }
    if ($type == 3)
        return mysql_num_rows($sql);
    return array();
}



$fwValue = isset($_REQUEST['fwValue']) ? $_REQUEST['fwValue'] : '';
$cat     = isset($_REQUEST['cat']) ? $_REQUEST['cat'] : 'buliao';
$page    = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
$customercode  = isset($_REQUEST['customercode']) ? $_REQUEST['customercode'] : '';
$customername = isset($_REQUEST['customername']) ? $_REQUEST['customername'] : '';
$item_no = isset($_REQUEST['item_no']) ? $_REQUEST['item_no'] : '';
$lot_num = isset($_REQUEST['lot_num']) ? $_REQUEST['lot_num'] : '';
$where = '';
if ($customercode) {
    $where .= ' and customer_code like "%' . $customercode . '%"';
}

if ($customername) {
    $where .= ' and customer_name like "%' . $customername . '%"';
}
if ($item_no) {
    $where .= ' and item_no like "%' . $item_no . '%"';
}

if ($lot_num) {
    $where .= ' and lot_num like "%' . $lot_num . '%"';
}

$num = 20;
$off = $num * ($page - 1);

$count = db_sql('select a.item_no,a.lot_num,b.item_name,(select customer_code from so_delivery_headers_all c where a.delivery_num = c.delivery_num) customer_code,(select d.customer_name from so_delivery_headers_all c,customers d where a.delivery_num = c.delivery_num and c.customer_code = d.customer_code) customer_name
from inv_transactions_all a,sf_item_no b where a.item_no=b.item_no and b.item_type = "F" and (transaction_type in ("SALESHIP","仓库调出") or trans_num like "%ZC%") and quantity < 0 ' . $where . '', 3);

$pages = ceil($count / $num);
$sql = 'select a.item_no,a.lot_num,b.item_name,(select customer_code from so_delivery_headers_all c where a.delivery_num = c.delivery_num) customer_code,(select d.customer_name from so_delivery_headers_all c,customers d where a.delivery_num = c.delivery_num and c.customer_code = d.customer_code) customer_name
from inv_transactions_all a,sf_item_no b where a.item_no=b.item_no and b.item_type = "F" and (transaction_type in ("SALESHIP","仓库调出") or trans_num like "%ZC%") and quantity < 0 ' . $where . ' ORDER BY customer_code  desc limit ' . $off . ',' . $num . '';

echo $sql;

$list = db_sql($sql, 2);


function show_page($url, $page, $pages, $total, $t0 = '')
{
    $str = '';
    $page = $page > $pages ? $pages : $page;
    if ($page > 1) {
        $str .= '<a class="pre" href="' . $url . (1) . $t0 . '">上一页</a>&nbsp;';
    } else {
        $str .= '<a class="pre">上一页</a>&nbsp;';
    }
    if ($page < 5) $start = 1;
    $end = 5;
    if ($page >= 5) {
        $start = $page - 2;
        $end = $page + 3;
    }
    $end = $end > $pages ? $pages : $end;
    for ($i = $start; $i <= $end; $i++) {
        if ($i == $page) {
            $str .= '<span class="cur">' . $i . '</span>&nbsp;';
        } else {
            $str .= '<a href="' . $url . $i . $t0 . '">' . $i . '</a>&nbsp;&nbsp;';
        }
    }
    if ($page >= 1 && $page < $pages) {
        $str .= '<a href="' . $url . ($page + 1) . $t0 . '">下一页</a>&nbsp;';
    } else {
        $str .= '<a class="next">下一页</a>&nbsp;';
    }
    $str .= '<span class="pages_c">页次:' . $page . '/' . $pages . '&nbsp;&nbsp;&nbsp;总计:' . $total . ' </span>';
    return $str;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>

    <title>查询仪器</title>
    <link rel="shortcut icon" href="favicon.ico" />
    <link rel="icon" href="favicon.ico" />
    <meta http-equiv="Content-Type" content="application/html; charset=utf-8" />
    <link href="css/xenos/default.css" rel="stylesheet" type="text/css" />
    <script type="text/javascript" src="javascripts/miscfunctions.js"></script>
    <script type="text/javascript" src="javascripts/wdatepicker.js"></script>
    <script src="javascript/jquery-1.10.2.min.js"></script>
    <script src="javascript/bootstrap.min.js"></script>
    <script src="javascript/jquery.dataTables.js"></script>
    <script src="javascript/jquery.livequery.js"></script>
</head>

<body>


    <script type="text/javascript">
        $(document).ready(function() {
            var api = frameElement.api,
                W = api.opener;


            $("#ck_company tr").slice(1).click(function() {
                var chks = $("input[type='radio']", this);
                var tag = $(this).attr("tag");
                if (tag == "selected") {
                    // 之前已选中，设置为未选中
                    $(this).attr("tag", "");
                    chks.prop("checked", false);
                    console.log('on');
                } else {
                    var rel = $(this).children("td:last-child").children(".coupons").attr("rel");
                    console.log($(this).children("td:last-child").children(".coupons").attr("rel"));
                    c = rel.split(":");
                    $("#form_item_no").text(c[0]);
                    $("#form_lot_num").text(c[1]);
                    $("#form_item_name").text(c[2]);
                    $("#form_customer_code").text(c[3]);
                    $("#form_customer_name").text(c[4]);
                    W.document.getElementById('text_slect_yiqi' + $('#fwValue').val()).value = $("label#form_item_no").text();
                    W.document.getElementById('text_slect_yiqi_sn' + $('#fwValue').val()).value = $("label#form_lot_num").text();
                    W.document.getElementById('text_slect_yiqi_desc' + $('#fwValue').val()).value = $("label#form_item_name").text();
                    W.document.getElementById('text_slect_customer' + $('#fwValue').val()).value = $("label#form_customer_code").text();
                    W.document.getElementById('text_slect_name' + $('#fwValue').val()).value = $("label#form_customer_name").text();

                    // W.document.getElementById('text_slect_currency_code' + $('#fwValue').val()).value = $("label#form_currency_code").text();
                    // W.document.getElementById('text_slect_tax_name' + $('#fwValue').val()).value = $("label#form_tax_name").text();
                    // W.document.getElementById('text_slect_term_name' + $('#fwValue').val()).value = $("label#form_term_name").text();
                    // W.document.getElementById('text_slect_customer_contact' + $('#fwValue').val()).value = $("label#form_customer_contact").text();
                    // W.document.getElementById('text_slect_employee_num' + $('#fwValue').val()).value = $("label#form_employee_num").text();
                    // W.document.getElementById('text_slect_customer_address' + $('#fwValue').val()).value = $("label#form_customer_address").text();
                    // W.document.getElementById('text_slect_tax_rate' + $('#fwValue').val()).value = $("label#form_tax_rate").text();
                    $("#xianshi").css("display", "block");
                    api.close();
                }
            });

        });
    </script>
    <div id="CanvasDiv">

        <div id="BodyDiv">
            <div id="BodyWrapDiv">
                <p class="page_title_text">
                    <img src="/css/xenos/images/magnifier.png" title="查询仪器" alt="查询仪器">
                </p>
                <form action="./BtnSearchYiqi.php?fwValue=<?= $cat ?>&cat=<?= $cat ?>" method="POST">
                    <div>

                        <table cellpadding="3" class="selection">
                            <div class="text-nav">
                                <div class="text-nav-1">
                                    <div>客户编号：</div>
                                    <input type="text" name="customercode" value="<?= $customercode ?>">
                                </div>
                                <div class="text-nav-1 ">
                                    <div>客户名称：</div>
                                    <input type="text" name="customername" value="<?= $customername ?>">
                                </div>
                                <div class="text-nav-1 ">
                                    <div>料号：</div>
                                    <input type="text" name="item_no" value="<?= $item_no ?>">
                                </div>
                                <div class="text-nav-1 ">
                                    <div>SN：</div>
                                    <input type="text" name="lot_num" value="<?= $lot_num ?>">
                                </div>
                            </div>
                        </table>
                        <div class="centre">

                            <input type="submit" value="查找">
                        </div>
                </form>
                <br />
                <table cellpadding="2" class="selection" id="ck_company">
                    <tr>
                        <th>料号</th>
                        <th>SN</th>
                        <th>名称</th>
                        <th>客户编号</th>
                        <th>客户名称</th>



                    </tr>
                    <?php foreach ($list as $arr => $row2) { ?>
                        <tr class="EvenTableRows">
                            <td><?= $row2['item_no'] ?></td>
                            <td><?= $row2['lot_num'] ?></td>
                            <td><?= $row2['item_name'] ?></td>
                            <td><?= $row2['customer_code'] ?></td>
                            <td><?= $row2['customer_name'] ?>
                                <input name="a" type="hidden" value="选择" class="coupons" rel="<?= $row2['item_no'] ?>:<?= $row2['lot_num'] ?>:<?= $row2['item_name'] ?>:<?= $row2['customer_code'] ?>:<?= $row2['customer_name'] ?>">
                            </td>
                        </tr>
                    <?php } ?>


                </table>
                <br />
                <div class="centre">
                    <?= show_page('?page=', $page, $pages, $count, '&fwValue=' . $cat . '&cat=' . $cat . '&customercode=' . $customercode . '&customername=' . $customername . '&item_no=' . $item_no . '&lot_num=' . $lot_num . ''); ?>

                </div>
            </div>

        </div>
    </div>
    <div id="FooterDiv">
        <div id="FooterWrapDiv">

        </div>
    </div>
    <div style="display:none">
        <p><label class="text-info">item_no:</label>　<label id="form_item_no"></label></p>
    </div>
    <div style="display:none">
        <p><label class="text-info">lot_num:</label>　<label id="form_lot_num"></label></p>
    </div>
    <div style="display:none">
        <p><label class="text-info">item_name:</label>　<label id="form_item_name"></label></p>
    </div>
    <div style="display:none">
        <p><label class="text-info">customercode:</label>　<label id="form_customer_code"></label></p>
    </div>

    <div style="display:none">
        <p><label class="text-info">customername</label>　<label id="form_customer_name"></label></p>
    </div>
   

    <div style="display:none">
        <p><input type="hidden" name="cat" id="cat" value="<?= $cat ?>" /></p>
        <p><input type="hidden" name="fwValue" value="<?= $fwValue ?>" id="fwValue" /></p>
    </div>


</body>

</html>