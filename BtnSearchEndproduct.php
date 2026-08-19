<?php
include('includes/session.inc');
$Title = _('查询成品料号');

$ViewTopic = '查询成品料号';
$BookMark = '查询成品料号';

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
$sql = 'select p_id,p_name,material_quality,model_unit_price,   polish_unit_price,stock_count,production_oneday from wip_endproducts where 1=1 ';

//SQL添加条件
if (isset($_POST['p_id']) and $_POST['p_id'] != '') {
    $sql = $sql . " and p_id like '%" . $_POST['p_id'] . "%' ";
}
if (isset($_POST['p_name']) and $_POST['p_name'] != '') {
    $sql = $sql . " and p_name like '%" . $_POST['p_name'] . "%' ";
}
if (isset($_POST['material_quality']) and $_POST['material_quality'] != '') {
    $sql = $sql . " and material_quality = '" . $_POST['material_quality'] . "' ";
}

if (isset($_POST['polish_unit_price']) and $_POST['polish_unit_price'] != '') {
    $sql = $sql . " and polish_unit_price like '%" . $_POST['polish_unit_price'] . "%' ";
}
if (isset($_POST['stock_count']) and $_POST['stock_count'] != '') {
    $sql = $sql . " and stock_count like '%" . $_POST['stock_count'] . "%' ";
}
if (isset($_POST['production_oneday']) and $_POST['production_oneday'] != '') {
    $sql = $sql . " and production_oneday like '%" . $_POST['production_oneday'] . "%' ";
}

$sql .= " ORDER BY p_id"; //SQL排序
$result = DB_query($sql, $db);
if (@DB_num_rows($result) == 0) {
    unset($result);
    prnMsg(_('找不到数据，请重新输入条件查询！'), 'error');
}

?>
    <p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/magnifier.png" title="查询成品料号" alt="查询成品料号">查询料号</p>
    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST">
        <div>
            <input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
            <table cellpadding="3" class="selection">
                <tr>
                    <td>成品料号</td>
                    <td><input   type="text" name="p_id" value=<?= $_POST['p_id'] ?> >
                    </td>

                    <td>料号名称</td>
                    <td><input   type="text" name="p_name" value=<?= $_POST['p_name'] ?> >
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
                    <th class="ascending" width = "100" >成品料号</th>
                    <th class="ascending" width = "200" >成品名称</th>
                    <th class="ascending" width = "120" >材质</th>
                    <th class="ascending" width = "80" >造型单价</th>
                    <th class="ascending" width = "40" >打磨单价</th>
                    <th class="ascending" width = "90" >库存数量</th>
                    <th class="ascending" width = "80" >一天生产个数</th>
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
                        <?= $myrow['p_id'] ?>
                    </td>
                    <td><?= $myrow['p_name'] ?>   </td>
                    <td><?= $myrow['material_quality'] ?> </td>
                    <td><?= $myrow['model_unit_price'] ?> </td>
                    <td>  <?= $myrow['polish_unit_price'] ?> </td>
                    <td><?= $myrow['stock_count'] ?></td>
                    <td><?= $myrow['production_oneday'] ?></td>
                    <td><input name="a" type="radio" value="选择" class="coupons" rel="<?=$myrow['p_id']?>:'s">		</td>
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

            $fwValue = isset($_REQUEST['fwValue']) ? $_REQUEST['fwValue'] : '';
            $cat     = isset($_REQUEST['cat']) ? $_REQUEST['cat'] : 'buliao';
            $page    = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
            } ?>

        </div>
    </form>

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
                        W.document.getElementById('pid').value = $("label#form_id").text();
                        $("#xianshi").css("display","block");
                        break;
                    default :
                        alert('Data Post Error');
                }
            };


            $(".coupons").livequery("click", function() {
                var rel = this.getAttribute('rel');
                c = rel.split(":");
                $("#form_id").text(c[0]);
            });



        });
    </script>

    <div style="display:none">
        <p><label class="text-info">id:</label>　<label id="form_id"></label></p>
    </div>

    <div style="display:none">
        <p><input type="hidden" name="cat" id="cat" value="buliao"/></p>
        <p><input type="hidden" name="fwValue" value="<?=$fwValue?>" id="fwValue"/></p>
    </div>
<?php
include('includes/footer.inc');
?>