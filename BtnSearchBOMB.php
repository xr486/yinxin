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
$ItemNo  = isset($_REQUEST['ItemNo']) ? $_REQUEST['ItemNo'] : '';
$ItemName = isset($_REQUEST['ItemName']) ? $_REQUEST['ItemName'] : '';
$where = "  ";
if ($ItemNo) {
    $where .= ' and a.item_no like "%' . $ItemNo . '%"';
}

if ($ItemName) {
    $where .= ' and a.item_name like "%' . $ItemName . '%"';
}



$num = 10;
$off = $num * ($page - 1);

$count = db_sql('select  a.*,b.bom_header_id,b.version
from sf_item_no  a,bom_headers_all b  where b.assembly_item_no=a.item_no    ' . $where . '', 3);

$pages = ceil($count / $num);
$sql = 'select  a.*,b.bom_header_id,b.version
from sf_item_no  a,bom_headers_all b  where b.assembly_item_no=a.item_no ' . $where . ' ORDER BY a.item_no  desc limit ' . $off . ',' . $num . '';

//echo $sql;

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

    <title>查询图号</title>
    <link rel="shortcut icon" href="/JXC/favicon.ico" />
    <link rel="icon" href="/JXC/favicon.ico" />
    <meta http-equiv="Content-Type" content="application/html; charset=utf-8" />
    <link href="css/xenos/default.css" rel="stylesheet" type="text/css" />
    <script type="text/javascript" src="/JXC/javascripts/miscfunctions.js"></script>
    <script type="text/javascript" src="/JXC/javascripts/wdatepicker.js"></script>
    <script src="/JXC/javascript/jquery-1.10.2.min.js"></script>
    <script src="/JXC/javascript/bootstrap.min.js"></script>
    <script src="/JXC/javascript/jquery.dataTables.js"></script>
    <script src="/JXC/javascript/jquery.livequery.js"></script>
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
                    $("#form_ItemNo").text(c[0]);
                    $("#form_units").text(c[1]);
                    $("#form_ItemDesc").text(c[2]);
                    $("#form_item_spec").text(c[3]);
                    $("#form_bom_header_id").text(c[4]);
                    $("#form_version").text(c[5]); 
                    W.document.getElementById('text_slect_buliaob' + $('#fwValue').val()).value = $("label#form_ItemNo").text();
                    // W.document.getElementById('text_slect_uom' + $('#fwValue').val()).value = $("label#form_units").text();
                    W.document.getElementById('text_slect_item_nameb'+$('#fwValue').val()).value = $("label#form_ItemDesc").text(); 
                    W.document.getElementById('item_descb'+$('#fwValue').val()).value = $("label#form_item_spec").text(); 
                    W.document.getElementById('bom_header_idb'+$('#fwValue').val()).value = $("label#form_bom_header_id").text(); 
                    W.document.getElementById('versionb'+$('#fwValue').val()).value = $("label#form_version").text(); 
                    api.close();
                }
            });

        });
    </script>
    <div id="CanvasDiv">

        <div id="BodyDiv">
            <div id="BodyWrapDiv">
                <p class="page_title_text">
                    <img src="./JXC/css/xenos/images/magnifier.png" title="" alt="">查询BOM
                </p>
                <form action="./BtnSearchBOMB.php?fwValue=<?= $cat ?>&cat=<?= $cat ?>" method="POST">
                    <div>

                        <table cellpadding="3" class="selection">
                            <div class="text-nav2">
                                <div class="text-nav-1 ">
                                    <div>料号</div>
                                    <input type="text" name="ItemNo" value="<?= $ItemNo ?>">
                                </div>
                                <div class="text-nav-1 ">
                                    <div>料号名称</div>
                                    <input type="text" name="ItemName" value="<?= $ItemName ?>">
                                </div>
                            </div>


                        </table>
                        <div class="centre">

                            <input type="submit" value="查找">
                        </div>
                </form>
                <br />
                <div class="text-nav-table">
                    <table cellpadding="2" class="selection" id="ck_company">
                        <tr>
                            <th class="ascending" width="290">
                                料号
                            </th>
                            <th class="ascending" width="250">
                                料号名称
                            </th>
                            <th class="ascending" width="300">
                                规格型号
                            </th>


                            <th width="50">
                                版本
                            </th>

                        </tr>
                        <?php foreach ($list as $arr => $row2) { ?>
                            <tr class="EvenTableRows">
                                <td>
                                    <?= $row2['item_no'] ?>
                                </td>
                                <td> <?= $row2['item_name'] ?>
                                </td>
                                <td> <?= $row2['item_desc'] ?>
                                </td>
                                <td> <?= $row2['version'] ?> </td>
                                
                                <td>
                                    <input name="a" type="hidden" value="选择" class="coupons" rel="<?= $row2['item_no'] ?>:<?= $row2['units'] ?>:<?= $row2['item_name'] ?>:<?= $row2['item_desc'] ?>:<?= $row2['bom_header_id'] ?>:<?= $row2['version'] ?>">
                                </td>
                            </tr>
                        <?php } ?>


                    </table>
                </div>
                <br />
                <div class="centre">
                    <?= show_page('?page=', $page, $pages, $count, '&fwValue=' . $cat . '&cat=' . $cat . '&ItemNo=' . $ItemNo . '&ItemName=' . $ItemName . ''); ?>

                </div>
            </div>

        </div>
    </div>
    <div id="FooterDiv">
        <div id="FooterWrapDiv">

        </div>
    </div>
    <div style="display:none">
        <p><label class="text-info">ItemNo:</label>　<label id="form_ItemNo"></label></p>
    </div>

    <div style="display:none">
        <p><label class="text-info">Units:</label>　<label id="form_units"></label></p>
    </div>
    <div style="display:none">
        <p><label class="text-info">ItemDesc:</label>　<label id="form_ItemDesc"></label></p>
    </div>
    <div style="display:none">
        <p><label class="text-info">item_spec:</label>　<label id="form_item_spec"></label></p>
    </div>
    <div style="display:none">
        <p><label class="text-info">bom_header_id:</label>　<label id="form_bom_header_id"></label></p>
    </div>
    <div style="display:none">
        <p><label class="text-info">version:</label>　<label id="form_version"></label></p>
    </div>
     


    <div style="display:none">
        <p><input type="hidden" name="cat" id="cat" value="<?= $cat ?>" /></p>
        <p><input type="hidden" name="fwValue" value="<?= $fwValue ?>" id="fwValue" /></p>
    </div>


</body>

</html>