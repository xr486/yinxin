<?php



include('includes/session.inc');



$Title = _('加盟商销售报表');



include('includes/header.inc');

include('includes/CountriesArray.php');





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



echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';

echo '<div>';

echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('加盟商销售出货报表') . '</p>';

echo '<table cellpadding="3" class="selection">';



echo '<tr><td >' . _('加盟商名称') . ':</td><td>';

echo '<input type="text" name="fran_name" value="' . $_POST['fran_name'] . '" size="20" maxlength="25" /></td>';

echo '<td >' . _('成衣名称') . ':</td><td>';

echo '<input type="text" name="Endpro_name" value="' . $_POST['Endpro_name'] . '" size="20" maxlength="25" /></td>';


echo '</tr>';



echo '<tr>';

echo '<td>' . _('年') . ':</td>';

echo '<td>
        <select name="year" style="width: 180px" >
        <option></option>';
for($i=2018;$i<=2100;$i++){
    if($_POST['year']==$i){
        echo '<option selected >'.$i.'</option>';
    }else{
        echo '<option  >'.$i.'</option>';
    }
}
echo '
        </select>

</td>';




echo '<td>' . _('季度') . ':</td>';
echo '<td>
        <select name="quarter" style="width: 180px">
        <option></option>';
for($i=1;$i<=3;$i++){
    if($_POST['quarter']=='第'.$i){
        echo '<option selected >第'.$i.'</option>';
    }else{
        echo '<option  >第'.$i.'</option>';
    }
}
echo '
        </select>

</td>';
echo '</tr>';



echo '<tr>';
echo '<td >' . _('月') . ':</td><td>
        <select name="month" style="width: 180px">
        <option></option>';
for($i=1;$i<=12;$i++){
    if($_POST['month']==$i){
        echo '<option selected >'.$i.'</option>';
    }else{
        echo '<option  >'.$i.'</option>';
    }
}
echo '
        </select>

</td>';

echo '<td>' . _('日') . ':</td>';
echo '<td><input type="text" name="day" onfocus="WdatePicker()" value="' . $_POST['day'] . '" size="18" maxlength="25" /></td>';


echo '</tr>';


echo '<tr><td >' . _('日期起') . ':</td><td>';

echo '<input type="text" name="start" onfocus="WdatePicker()" value="' . $_POST['start'] . '" size="20" maxlength="25" /></td>';

echo '<td>' . _('日期止') . ':</td>';

echo '<td><input type="text" name="end" onfocus="WdatePicker()" value="' . $_POST['end'] . '" size="20" maxlength="25" /></td>';

echo '</tr>';




echo '</table><div class="centre"><input type="submit" name="Search" value="查找"> </div>'

    . '</br>';



if(!empty($_POST['year'])&&!empty($_POST['month'])&&empty($_POST['day'])&&empty($_POST['quarter'])&&empty($_POST['start'])&&empty($_POST['end'])){
    echo '
    <h1>'.$_POST['year'].'年-'.$_POST['month'].'月</h1>
    ';
}else if(!empty($_POST['year'])&&!empty($_POST['quarter'])&&empty($_POST['day'])&&empty($_POST['month'])&&empty($_POST['start'])&&empty($_POST['end'])){
    echo '
    <h1>'.$_POST['year'].'年-'.$_POST['quarter'].'季度</h1>
    ';
}else if(empty($_POST['year'])&&empty($_POST['quarter'])&&!empty($_POST['day'])&&empty($_POST['month'])&&empty($_POST['start'])&&empty($_POST['end'])){
    echo '
    <h1>'.$_POST['day'].'日</h1>
    ';
}else if(!empty($_POST['year'])&&empty($_POST['quarter'])&&empty($_POST['day'])&&empty($_POST['month'])&&empty($_POST['start'])&&empty($_POST['end'])){
    echo '
    <h1>'.$_POST['year'].'年</h1>
    ';
}else if(empty($_POST['year'])&&empty($_POST['quarter'])&&empty($_POST['day'])&&empty($_POST['month'])&&!empty($_POST['start'])&&empty($_POST['end'])){
    echo '
    <h1>'.$_POST['start'].'起</h1>
    ';
}else if(empty($_POST['year'])&&empty($_POST['quarter'])&&empty($_POST['day'])&&empty($_POST['month'])&&empty($_POST['start'])&&!empty($_POST['end'])){
    echo '
    <h1>'.$_POST['end'].'止</h1>
    ';
}else if(empty($_POST['year'])&&empty($_POST['quarter'])&&empty($_POST['day'])&&empty($_POST['month'])&&!empty($_POST['start'])&&!empty($_POST['end'])){
    echo '
    <h1>'.$_POST['start'].'起-'.$_POST['end'].'止</h1>
    ';
}

//
//echo '
//    <h1>'.$_POST['year'].'年  '.$_POST['quarter'].'季度 '.$_POST['month'].' 月'.$_POST['day'].'日</h1>
//';


$sql = 'select b.*,a.*,
        SUM(a.quantity) AS quantity,
        SUM(a.price) AS price
        from  mtl_transation_all a,mtl_franchisee_all b
        where a.franchisee_code=b.FRANCHISEE_CODE
        and a.transation_type="加盟商出货"
        ';

if (isset($_POST['fran_name']) and $_POST['fran_name'] != '') {
    $sql = $sql . " and b.FRANCHISEE_NAME " . LIKE . " '%" . $_POST['fran_name'] . "%' ";
}
if (isset($_POST['Endpro_name']) and $_POST['Endpro_name'] != '') {
    $sql = $sql . " and a.item_no " . LIKE . " '%" . $_POST['Endpro_name'] . "%' ";
}
//echo $sql;

$sql = $sql ." group by b.FRANCHISEE_NAME ,a.item_no";

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

        _('材料库存流水报表') . '" alt="" />' . ' ' . $Title . '</p> <input type="hidden" name="PageOffset" value=' . $_POST['PageOffset'] . ' />';



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

            <th width="200">加盟商名称</th>

            <th  width="200">成衣料号</th>

            <th  width="200">成衣名称</th>

            <th  width="100">成衣图片</th>

            <th  width="100">成衣尺码</th>

			<th  width="200">销售数量</th>

			<th  width="200">销售总价</th>
		</tr>';

    $i = 0;

    $RowIndex = 0;

    $k=0; //row colour counter

    if (@DB_num_rows($result) <> 0 ) {

        DB_data_seek($result, ($_POST['PageOffset'] - 1) * 10);

        while (($myrow = @DB_fetch_array($result)) AND ( $RowIndex <> 10)) {



            if ($k == 1) {

                echo '<tr class="EvenTableRows">';

                $k = 0;

            } else {

                echo '<tr class="OddTableRows">';

                $k = 1;

            }

            $sql9="select *  from mtl_clothing_all where CLOTHING_CODE='".$myrow['item_no']."'  ";
            $result9= DB_query($sql9,$db);
            $myrow9=DB_fetch_array($result9);
            $img=explode(',',  $myrow9['PIC_PATH']);
            echo '

            <tr>

                    <td style="text-align:center;">' . $myrow['FRANCHISEE_NAME'] . '</td>
                	<td style="text-align:center;">'. $myrow['item_no'].'</td>
                	
                    <td style="text-align:center;">' . $myrow9['CLOTHING_NAME'] . '</td>

                    <td style="text-align:center;"><img width="50" height="50" data-action="zoom"  alt="暂未上传" src=' . $img[0] . ' > </td>

                    <td style="text-align:center;">' . $myrow9['CHIMA'] . '</td>

                	<td style="text-align:center;">' . $myrow['quantity'] . '</td>

                	<td style="text-align:center;">' . $myrow['price'] . '</td>


			</tr>

	';

            $i++;

            $RowIndex++;

        }

    }

}







include('includes/footer.inc');

?>

