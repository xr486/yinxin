<?php
include('includes/session.inc');
$Title = _('查询半成品泥芯料号');

$ViewTopic = '查询半成品泥芯料号';
$BookMark = '查询半成品泥芯料号';

include('includes/header3.inc');
include('includes/SQL_CommonFunctions.inc');

unset($result);

if (isset($_POST['Go1']) OR isset($_POST['Go2'])) {
    $_POST['PageOffset'] = (isset($_POST['Go1']) ? $_POST['PageOffset1'] : $_POST['PageOffset2']);
    $_POST['Go'] = '';
}
if (!isset($_POST['PageOffset'])) {
    $_POST['PageOffset'] = 1;
} else {
    if ($_POST['PageOffset'] == 0) {
        $_POST['PageOffset'] = 1;
    }
}

    //日期格式化为SQL格式
    $SQL_FromDate = FormatDateForSQL($_POST['FromDate']);
    $SQL_ToDate = FormatDateForSQL($_POST['ToDate']);
    //查找数据的SQL
    $sql = 'select lcid,lcname,paint,cprice,trueweight,weight,sandboxid,workorder,p_id from wip_loamcore where workorder=0 ';

    //SQL添加条件
    if (isset($_POST['lcid']) and $_POST['lcid'] != '') {
        $sql = $sql . " and lcid like '%" . $_POST['lcid'] . "%' ";
    }
    if (isset($_POST['lcname']) and $_POST['lcname'] != '') {
        $sql = $sql . " and lcname like '%" . $_POST['lcname'] . "%' ";
    }
    if (isset($_POST['p_id']) and $_POST['p_id'] != '') {
        $sql = $sql . " and p_id = '" . $_POST['p_id'] . "' ";
    }

    $sql .= " ORDER BY lcid"; //SQL排序
    $result = DB_query($sql, $db);
    if (@DB_num_rows($result) == 0) {
        unset($result);
        prnMsg(_('找不到数据，请重新输入条件查询！'), 'error');
}
?>
    <p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/magnifier.png" title="查询半成品泥芯料号" alt="查询半成品泥芯料号">查询半成品泥芯料号</p>
    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST">
        <div>
            <input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
            <table cellpadding="3" class="selection">
                <tr>
                    <td>半成品泥芯编号</td>
                    <td><input type="text" name="lcid" value=<?= $_POST['lcid'] ?> >
                    </td>

                    <td>半成品名称</td>
                    <td><input type="text" name="lcname" value=<?= $_POST['lcname'] ?> >
                    </td>

                    <td>成品料号</td>
                    <td><input type="text" name="p_id" value=<?= $_POST['p_id'] ?> >
                    </td>
                </tr>


            </table>
            <div class="centre"><input type="submit" name="Search" value="查找"></div>

            <?php
                $ListCount = @DB_num_rows($result);
                $ListPageMax = ceil($ListCount / 10); //$_SESSION['DisplayRecordsMax']

                if (isset($_POST['Next'])) {
                    if ($_POST['PageOffset'] < $ListPageMax) {
                        $_POST['PageOffset'] = $_POST['PageOffset'] + 1;
                    }
                }
                if (isset($_POST['Previous'])) {
                    if ($_POST['PageOffset'] > 1) {
                        $_POST['PageOffset'] = $_POST['PageOffset'] - 1;
                    }
                }
                ?>
                <input type="hidden" name="PageOffset" value=<?= $_POST['PageOffset'] ?> />

                <br />
                <table cellpadding="2" class="selection">
                    <tr>
                        <th class="ascending" width = "120" >半成品泥芯编号</th>
                        <th class="ascending" width = "50" >名称</th>
                        <th class="ascending" width = "120" >对应的成品料号</th>
                        <th class="ascending" width = "80" >所用沙箱</th>
                        <th class="ascending" width = "40" >油漆</th>
                        <th class="ascending" width = "90" >制芯单价</th>
                        <th class="ascending" width = "80" >泥芯重量</th>
                        <th class="ascending" width = "90" >铸件毛重</th>
                        <th class="ascending" width = "90" >是否开单</th>
                        <th class="ascending" width = "80" >选择</th>
                    </tr>
                    <?php
                    $k = 0; //row counter to determine background colour
                    $RowIndex = 0;
                    if (@DB_num_rows($result) <> 0) {
                        DB_data_seek($result, ($_POST['PageOffset'] - 1) * 10); // $_SESSION['DisplayRecordsMax']
                        $i = 0; //counter for input controls
                        while (($myrow = DB_fetch_array($result)) AND ( $RowIndex <> 10)) {//$_SESSION['DisplayRecordsMax']
                            if ($k == 1) {
                                echo '<tr class="EvenTableRows">';
                                $k = 0;
                            } else {
                                echo '<tr class="OddTableRows">';
                                $k = 1;
                            }
                            ?>
                            <td>
                                <a href="<?= $RootPath ?>/updateLoamcore.php?lcid=<?= $myrow['lcid'] ?>"><?= $myrow['lcid'] ?>
                            </td>
                            <td><?= $myrow['lcname'] ?>   </td>
                            <td><?= $myrow['p_id'] ?> </td>
                            <td><?= $myrow['sandboxid'] ?> </td>
                            <td><?= $myrow['paint'] ?> </td>
                            <td><?= $myrow['cprice'] ?> </td>
                            <td><?= $myrow['trueweight']?> </td>
                            <td><?= $myrow['weight'] ?> </td>
                            <td><?= $myrow['workorder']=='0' ? '不开':'开' ?> </td>
                            <td><input name="a" type="radio" value="选择" class="coupons" rel="<?= $myrow['lcid'] ?>:<?= $myrow['lcname']?>
                            :<?= $myrow['p_id'] ?>"> </td>
                            </tr>
                            <?php
                            $i++;
                            $RowIndex++;
                        }
                        ?>
                    </table>
                    <?php
                }
                if (isset($ListPageMax) AND $ListPageMax > 1) {
                    ?>
                    <br />

                      <div class="centre">&nbsp;&nbsp;第&nbsp;<?= $_POST['PageOffset'] ?>&nbsp;页，共&nbsp;<?= $ListPageMax ?>&nbsp;页&nbsp;&nbsp; 跳转至页:
                        <select name="PageOffset1">
                            <?php
                            $ListPage = 1;
                            while ($ListPage <= $ListPageMax) {
                                if ($ListPage == $_POST['PageOffset']) {
                                    echo '<option value="' . $ListPage . '" selected="selected">' . $ListPage . '</option>';
                                } else {
                                    echo '<option value="' . $ListPage . '">' . $ListPage . '</option>';
                                }
                                $ListPage++;
                            }
                            ?>
                        </select>

                        <input type="submit" name="Go1" value="跳转" />
                        <input type="submit" name="Previous" value="上一页" />
                        <input type="submit" name="Next" value="下一页" />

                    </div>
        <?php
    } ?>

        </div>
    </form>



    <div style="display:none">
        <p><input type="hidden" name="cat" id="cat" value="<?=$cat?>"/></p>
        <p><input type="hidden" name="fwValue" value="<?=$fwValue?>" id="fwValue"/></p>
    </div>
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <link rel="shortcut icon" href="/JXC/favicon.ico"/>
        <link rel="icon" href="/JXC/favicon.ico"/>
        <meta http-equiv="Content-Type" content="application/html; charset=utf-8"/>
        <link href="/JXC/css/xenos/default.css" rel="stylesheet" type="text/css"/>
        <script type="text/javascript" src ="/JXC/javascripts/miscfunctions.js"></script>
        <script type="text/javascript" src ="/JXC/javascripts/wdatepicker.js"></script>
        <script src="/JXC/javascript/jquery-1.10.2.min.js"></script>
        <script src="/JXC/javascript/bootstrap.min.js"></script>
        <script src="/JXC/javascript/jquery.dataTables.js"></script>
        <script src="/JXC/javascript/jquery.livequery.js"></script>
    </head>
    <body>


    <script type="text/javascript">
        $(document).ready(function(){
            var api = frameElement.api, W = api.opener;
            api.button({
                id:'valueOk',
                name:'确定',
                focus: true,
                callback:ok
            });

            function ok()
            {
                switch ($('#cat').val()){
                    case 'buliao':
                        W.document.getElementById('lcid').value = $("label#lcid").text();
                        W.document.getElementById('p_id').value = $("label#p_id").text();
                        $("#xianshi").css("display","block");
                        break;
                    default :
                        alert('Data Post Error');
                }
            };


            $(".coupons").livequery("click", function() {
                var rel = this.getAttribute('rel');
                c = rel.split(":");
                $("#lcid").text(c[0]);
                $("#p_id").text(c[2]);
            });



        });


    </script>
    <!--

    -->
    <div style="display:none">
        <p><label class="text-info">lcid:</label>　<label id="lcid"></label></p>
    </div>

    <div style="display:none">
        <p><label class="text-info">lcname</label>　<label id="lcname"></label></p>
    </div>

    <div style="display:none">
        <p><label class="text-info">p_id</label>　<label id="p_id"></label></p>
    </div>

<?php
include('includes/footer.inc');
?>