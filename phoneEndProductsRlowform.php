<?php



include('includes/session.inc');



$Title = _('成衣库存流水报表');



include('includes/header.inc');

include('includes/CountriesArray.php');





if (isset($_POST['Go1']) OR isset($_POST['Go2'])) {

    $_POST['PageOffset'] = (isset($_POST['Go1']) ? $_POST['PageOffset1'] : $_POST['PageOffset2']);

    $_POST['Go'] = '';

}

if (!isset($_POST['PageOffset'])) {
    if(isset($_GET['page'])){
        $_POST['PageOffset']=$_GET['page'];
    }else{
        $_POST['PageOffset'] = 1;
    }

} else {

    if ($_POST['PageOffset'] == 0) {

        $_POST['PageOffset'] = 1;

    }

}

if (!isset($_POST['time_start'])) {

    $_POST['time_start'] = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 30, date("Y")));

}

if (!isset($_POST['time_end'])) {

    $_POST['time_end'] = Date('Y-m-d', mktime(0, 0, 0, date("m"), date("d") + 1, date("Y")));

}

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';

echo '<div>';

echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('成衣库存流水报表') . '</p>';

// echo '<table cellpadding="3" class="selection">';





// echo '<tr><td >' . _('流水单号') . ':</td><td>';

// echo '<input type="text" name="transation_no" value="' . $_POST['transation_no'] . '" size="20" maxlength="25" /></td>';



// echo '<td>' . _('仓库编号') . ':</td>';

// echo '<td><input type="text" name="sub_code" value="' . $_POST['sub_code'] . '" size="20" maxlength="25" /></td>';

// echo '</tr>';



// echo '<tr><td >' . _('日期起') . ':</td><td>';

// echo '<input type="text" name="time_start" onfocus="WdatePicker()" value="' . $_POST['time_start'] . '" size="20" maxlength="25" /></td>';

// echo '<td>' . _('日期止') . ':</td>';

// echo '<td><input type="text" name="time_end" onfocus="WdatePicker()" value="' . $_POST['time_end'] . '" size="20" maxlength="25" /></td>';

// echo '</tr>';



// echo '<tr><td >' . _('供应商名称') . ':</td><td>';

// echo '<input type="text" name="supplier_code" value="' . $_POST['supplier_code'] . '" size="20" maxlength="25" /></td>';

// echo '<td>' . _('交易类型') . ':</td>';

// echo '<td>

//         <select name="transation_type">

//         <option></option>

//         <option>采购成衣入库</option>

//         <option>采购成衣退货</option>
//         <option>成衣仓库出货</option>

//         </select>

// </td>';

// echo '</tr>';







// echo '</table><div class="centre"><input type="submit" name="Search" value="查找"> </div>'

//     . '</br>';







    $sql = "SELECT  a.transation_type,

					a.transation_date,

					a.item_no,

                    sum(a.quantity) as quantity,

                    sum(a.price) as price,

                    a.supplier_code,

                    a.sub_code,

                    b.CLOTHING_NAME,

                    b.PIC_PATH,

                    a.transation_no

				FROM mtl_transation_all a,mtl_clothing_all b

            WHERE a.item_no=b.CLOTHING_CODE and a.transation_type in ('采购成衣入库','采购成衣退货','成衣仓库出货')

			";



    if (isset($_POST['sub_code']) and $_POST['sub_code'] != '') {

        $sql = $sql . " and a.sub_code " . LIKE . " '%" . $_POST['sub_code'] . "%' ";

    }

    if (isset($_POST['time_start']) and $_POST['time_start'] != '') {

        $SQL_FromDate = strtotime($_POST['time_start']);

        $sql = $sql . " and a.transation_date >= '" . $SQL_FromDate . "' ";

    }    

    if (isset($_POST['time_end']) and $_POST['time_end'] != '') {

        $SQL_EndDate = strtotime($_POST['time_end']);

        $sql = $sql . " and a.transation_date <= '" . $SQL_EndDate . "' ";

    }

    if (isset($_POST['transation_type']) and $_POST['transation_type'] != '') {

        $sql = $sql . " and a.transation_type " . LIKE . " '%" . $_POST['transation_type'] . "%' ";

    }   

    if (isset($_POST['supplier_code']) and $_POST['supplier_code'] != '') {
        $sql8="select VENDOR_CODE  from mtl_supplier_all where VENDOR_NAME like '%".$_POST['supplier_code']."%'  ";
        $result8= DB_query($sql8,$db);
        $myrow8=DB_fetch_array($result8);
        if($myrow8['VENDOR_CODE']!=""){
            $sql = $sql . " and a.supplier_code " . LIKE . " '%" . $myrow8['VENDOR_CODE'] . "%' ";
        }else{
            prnMsg(_('找不到该查询流水，请重新输入条件查询！'), 'error');
            exit();
        }
    }

    if (isset($_POST['transation_no']) and $_POST['transation_no'] != '') {

        $sql = $sql . " and a.transation_no " . LIKE . " '%" . $_POST['transation_no'] . "%' ";

    }  

        //echo $sql;

    $sql = $sql ."group by a.transation_no";

    $result = DB_query($sql, $db);

    if (@DB_num_rows($result) == 0) {

        unset($result);

        prnMsg(_('找不到该查询流水，请重新输入条件查询！'), 'error');

    }

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



if(@DB_num_rows($result) <> 0){    

    echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/supplier.png" title="' .

        _('成衣库存流水报表') . '" alt="" />' . ' ' . $Title . '</p> <input type="hidden" name="PageOffset" value=' . $_POST['PageOffset'] . ' />';

if ($ListPageMax > 1) {

    ?>

    <br/>



    <div class="centre">&nbsp;&nbsp;第&nbsp;<?= $_POST['PageOffset'] ?>&nbsp;页，共&nbsp;<?= $ListPageMax ?>&nbsp;页&nbsp;&nbsp;

        跳转至页:

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



        <input type="submit" name="Go1" value="跳转"/>

        <input type="submit" name="Previous" value="上一页"/>

        <input type="submit" name="Next" value="下一页"/>



    </div>

<?php } ?>

<?php

    echo '<table class="selection">';

    echo '<tr>

            <th class="ascending" width="200">流水单号</th>

			<th class="ascending" width="200">交易类型</th>

			<th class="ascending" width="200" >交易日期</th>

            <th class="ascending" width="200" >交易总量</th>

            <th class="ascending" width="200" >交易总价</th>

            <th class="ascending" width="200" >供应商</th>

            <th class="ascending" width="200" >仓库</th>

		</tr>';

    $i = 0;

    $RowIndex = 0;

    $k=0; //row colour counter

if (@DB_num_rows($result) <> 0 or isset($_POST['insub'])) {

    DB_data_seek($result, ($_POST['PageOffset'] - 1) * 10);

    while (($myrow = @DB_fetch_array($result)) AND ( $RowIndex <> 20)) {



        if ($k == 1) {

            echo '<tr class="EvenTableRows">';

            $k = 0;

        } else {

            echo '<tr class="OddTableRows">';

            $k = 1;

        }
        $sql8="select SUB_NAME  from mtl_sub_all where sub_code='".$myrow['sub_code']."'  ";
        $result8= DB_query($sql8,$db);
        $myrow8=DB_fetch_array($result8);

        $sql9="select VENDOR_NAME  from mtl_supplier_all where VENDOR_CODE='".$myrow['supplier_code']."'  ";
        $result9= DB_query($sql9,$db);
        $myrow9=DB_fetch_array($result9);
        echo '

            <tr>

                    <td style="text-align:center;"><a href="'.$RootPath.'/Flowdetails.php?id='.$myrow['transation_no'].'&type=chengyi&url=EndProductsRlowform.php&page='.$_POST['PageOffset'].'">' . $myrow['transation_no'] . '</a></td>

                	<td style="text-align:center;">' . $myrow['transation_type'] . '</td>

                	<td style="text-align:center;">' .  date('Y-m-d H:i:s',$myrow['transation_date']) . '</td>

                    <td style="text-align:center;">' . $myrow['quantity'] . '</td>

                    <td style="text-align:center;">' .$myrow['price'] . '</td>

                    <td style="text-align:center;"><a href="'.$RootPath.'/Querysupplier.php?VENDOR_CODE='.$myrow['supplier_code'].'&url=EndProductsRlowform.php">' .$myrow9['VENDOR_NAME'] . '</a></td>

                    <td style="text-align:center;"><a href="'.$RootPath.'/QuerySub.php?SUB_CODE='.$myrow['sub_code'].'&url=EndProductsRlowform.php">' .$myrow8['SUB_NAME'] . '</a></td>

                			

			</tr>

	';

        $i++;

        $RowIndex++;

    }

}


}




include('includes/footer.inc');

?>

