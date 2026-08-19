<?php
include('includes/session.inc');
$Title = _('工单浇注完工处理');
$ViewTopic = '工单浇注完工处理';
$BookMark = '工单浇注完工处理';

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
$_SESSION['DisplayRecordsMax']=10;

//检测明细表中是否有进站的工单号，如果没有，则向明细表中添加该工单
$sql = "SELECT wip_entity_name from wip_jobs_all where casting_input_quantity > 0";
$result = DB_query($sql,$db);
if(DB_num_rows($result) > 0){
    while($row = DB_fetch_array($result)){
        $check = "select wip_entity_name 
                  from wip_transactions 
                  where wip_entity_name='".$row['wip_entity_name'] . "' 
                  and operation='浇注'";
        $result2 = DB_query($check,$db);
        //var_dump($result2);
        if(DB_num_rows($result2) <=0 ){
            /*工序,工单号,生产日期,工位
              良品数
              不良数
              不良原因
              报废数
              报废原因
              人员1
              人员2
            */
            DB_query("insert into wip_transactions values(
              '浇注',
              '".$row['wip_entity_name']."',
              0,
              '',
              0,
              0,
              '',
              0,
              '',
              '',
              '')
              ",$db);
        }
    }
}


//查询工单号，工单名，进站数量，出战数量，成品料号,日期
//查询工单表中，入站量大于0，并且出站量与报废的总和小于要生产的总量的工单数据
$sql ="SELECT wip_entity_id,
              last_update_date,
              wip_entity_name,
              start_quantity,
              casting_input_quantity,
              casting_output_quantity,
              pour_station,
              stockid
     from wip_jobs_all
    where  casting_input_quantity > 0 and casting_output_quantity + 
      (select sum(scrapCount) 
      from wip_transactions 
      where wip_jobs_all.wip_entity_name = wip_transactions.wip_entity_name 
      and operation='浇注')
      < casting_input_quantity";

$result = DB_query($sql,$db);
if (DB_num_rows($result)==0) {
    unset($result);
    prnMsg(_('没有需要检验的工单！') ,'error');
}


if (isset($_POST['UpdateStatus']) ) {

    $errorflag = 0;

    if ($errorflag == 0) {
        foreach ($_POST as $key => $value){

            if (mb_substr($key,0,10)=='UpdateLine') {
                $wip_entity_id =mb_substr($key,10);

                $i = $_POST[$key];

                $time = strtotime(Date('Y-m-d H:i:s'));
                $wip_entity_name = $_POST['wip_entity_name'.$wip_entity_id];
                $goodProductCount = $_POST['goodProductCount'.$wip_entity_id];
                $badCount=$_POST['badCount'.$wip_entity_id];
                $badReasonId=$_POST['badReasonId'.$wip_entity_id];
                $badCountReason=$_POST['badCountReason'.$wip_entity_id];
                $scrapCount=$_POST['scrapCount'.$wip_entity_id];
                $scrapReason=$_POST['scrapReason'.$wip_entity_id];
                $person1=$_POST['person1'.$wip_entity_id];
                $person2=$_POST['person2'.$wip_entity_id];
                $model_station=$_POST['model_station'.$wip_entity_id];
                //对用户输入空字符串时进行处理
                if(empty($goodProductCount)){
                    $goodProductCount=0;
                }
                if(empty($badCount)){
                    $badCount=0;
                }
                if(empty($badCountReason)){
                    $badCountReason='';
                }
                if(empty($scrapCount)){
                    $scrapCount=0;
                }
                if(empty($scrapReason)){
                    $scrapReason='';
                }
                if(empty($person)){
                    $person='';
                }


                $outputCount = $_POST['casting_output_quantity'.$wip_entity_id] +
                    $_POST['goodProductCount'.$wip_entity_id];


                //var_dump($_POST['goodProductCount'.$wip_entity_id]);
                //var_dump($_POST['badCount'.$wip_entity_id]);
                //更新后台数据
                $insertTransaction = "INSERT INTO wip_transactions
                                     (operation,
                                      wip_entity_name,
                                      transaction_date,
                                      model_station ,
                                      goodProductCount,
                                      badCount,
                                      badCountReason,
                                      scrapCount,
                                      scrapReason,
                                      person1,
                                      person2) 
                                     values ('浇注',
                                       '".$wip_entity_name."',
                                       '". $time ."',
                                       '".$model_station."',
                                       '".$goodProductCount."',
                                       '".$badCount."',
                                      '".$badCountReason."',
                                       '".$scrapCount."',
                                      '".$scrapReason."',
                                      '".$person1."',
                                      '".$person2."'
                                     )";
                $fectRow = DB_query($insertTransaction,$db,"插入失败");
                if($fectRow > 0){
                    prnMsg(_('插入成功！') ,'success');
                }
                //如果报废量大于0


                //var_dump($outputCount);
                $update_wip_jobs_all="UPDATE wip_jobs_all SET 
                        casting_output_quantity=".$outputCount.",
                       
						sandcheck_input_quantity=".$outputCount."

                        WHERE wip_entity_id='".$wip_entity_id. "'
                        ";
                DB_query($update_wip_jobs_all,$db,'更新失败');

                $select_wip_jobs_all_quantity_scrapped="select quantity_scrapped
                                                        from wip_jobs_all
                                                        where wip_entity_id='".$wip_entity_id. "'";
                $select_scrap_result = DB_query( $select_wip_jobs_all_quantity_scrapped,$db,'查找失败');
                $select_scrap = DB_fetch_array($select_scrap_result);

                //echo "这是报废量".$select_scrap['quantity_scrapped']."<br>";
                //echo "这是单次报废量".$scrapCount."<br>";
                $all_scrap_model = $select_scrap['quantity_scrapped'] + $scrapCount;
                //echo "这是总报废量".$all_scrap_model."<br>";
                $update_wip_jobs_all_quantity_scrapped ="update wip_jobs_all  
                                                         set quantity_scrapped  = '$all_scrap_model',
                                                         not_quantity = '$all_scrap_model'
                                                         where wip_entity_id='".$wip_entity_id. "'";
                DB_query($update_wip_jobs_all_quantity_scrapped,$db,'更新失败');


           //echo 1;

                //  $updateDetail = "UPDATE detail SET
                //        goodProductCount=". $_POST['goodProductCount'.$wip_entity_id] .",
                //        badCount=". $_POST['badCount'.$wip_entity_id] .",
                //        badCountReason='".$_POST['badCountReason'.$wip_entity_id] ."',
                //        scrapCount='". $_POST['scrapCount'.$wip_entity_id]."',
                //        scrapReason='". $_POST['scrapReason'.$wip_entity_id] ."',
                //        person='".$_POST['person'.$wip_entity_id] ."'
                //        WHERE wip_entity_id='".$wip_entity_id. "'
                //    ";
                // DB_query($updateDetail,$db);

                // else {
                //     //如果改表中不存在该工单id，则
                //     $insertSql = "insert into detail values(null,
                //                    ".$_POST['goodProductCount'.$wip_entity_id].",
                //                    ".$_POST['badCount'.$wip_entity_id].",
                //                    ".$_POST['badCountReason'.$wip_entity_id].",
                //                    ".$_POST['scrapCount'.$wip_entity_id].",
                //                    ".$_POST['scrapReason'.$wip_entity_id].",
                //                    ".$_POST['person'.$wip_entity_id].",
                //                    ".$wip_entity_id."
                //                  )";
                //     DB_query($insertSql,$db);
                // }

                //      $sql2="UPDATE so_forecast_line
                //         SET  quantity='0'
                //          ,status='在签核'
                //          ,last_update_date='" . $time. "'
                //         ,last_updated_by='" . $_SESSION['UserID'] . "'
                //         WHERE  order_line_id='".$order_line_id."'
                //         ";



                //         // exit;
                //   $ErrMsg = _('更新so_forecast_line不成功,原因');
                // $result_invtrancsation1 = DB_query($sql2, $db,$ErrMsg);

                //  $sql2="UPDATE so_forecast_header
                //         SET  last_update_date='" . $time. "'
                //         ,last_updated_by='" . $_SESSION['UserID'] . "'
                //         WHERE  order_number='".$_POST['order_number'.$i]."'
                //         ";

                // $ErrMsg = _('更新so_forecast_line不成功,原因');
                // $result_invtrancsation1 = DB_query($sql2, $db,$ErrMsg);

            }
        }
    }//插入交易表
}

//取消的foecast不再显示
if(isset($_POST['Search']) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])){


    // echo $sql;
    //从明细表中取出数据
    //$queryDetail = "select * from detail";
    if (isset($_POST['scheduled_start_date_search']) and $_POST['scheduled_start_date_search'] != '') {
        $sql = $sql . " and scheduled_start_date " . LIKE . " '%" . $_POST['scheduled_start_date_search'] . "%' ";
    }

    if (isset($_POST['wip_entity_name_search']) and $_POST['wip_entity_name_search'] != '') {
        $sql = $sql . " and wip_entity_name " . LIKE . " '%" . $_POST['wip_entity_name_search'] . "%' ";
    }

    if (isset($_POST['stockid_search']) and $_POST['stockid_search'] != '') {
        $sql = $sql . " and stockid " . LIKE . " '%" . $_POST['stockid_search'] . "%' ";
    }

    // if(isset($_POST['FromDate']) and $_POST['FromDate'] != ''){
    //     $sql = $sql." and a.schedule_arrive_time >=".strtotime($_POST['FromDate'])." ";
    // }
    //  if(isset($_POST['ToDate']) and $_POST['ToDate'] != ''){
    //     $sql = $sql." and a.schedule_arrive_time <=".strtotime($_POST['ToDate'])." ";
    // }
    $result = DB_query($sql,$db);
    //查询页面前三个属性的值
    if (DB_num_rows($result)==0) {
        unset($result);
        prnMsg(_('没有需要检验的工单，请重新输入条件查询！') ,'error');
    }

    //查询后面的值
    // $resultDetail = DB_query($queryDetail,$db);
    // if (DB_num_rows($resultDetail)==0) {
    //     unset($resultDetail);
    //     prnMsg(_('没有预测，请重新输入条件查询！') ,'error');
    // }
}

?>

<?php
echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('工单浇注完工处理') . '</p>';
echo '<table cellpadding="3" class="selection">';


echo '</tr>';
echo '<tr><td >' . _('预计开工日期') . ':</td><td>';
echo '<input type="text" name="scheduled_start_date_search"   
value="' . $_POST['scheduled_start_date_search'] . '" size="10" maxlength="25" />';

echo '<tr><td >' . _('工单号') . ':</td><td>';
echo '<input type="text" name="wip_entity_name_search"   
value="' . $_POST['wip_entity_name_search'] . '" size="10" maxlength="25" />';

echo ' <td >' . _('料号') . ':</td><td>';
echo '<input type="text" name="stockid_search"   
value="' . $_POST['stockid_search'] . '" size="10" maxlength="25" />
    ';
echo '</td>';
echo '</tr>';




echo '</table><div class="centre"><input type="submit" name="Search" value="查询"></div>';



if (isset($_POST['Search']) and isset($result) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])){
    $ListCount = DB_num_rows($result);
    $ListPageMax = ceil($ListCount / $_SESSION['DisplayRecordsMax']);

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
    echo '<input type="hidden" name="PageOffset" value="' . $_POST['PageOffset'] . '" />';
    if ($ListPageMax > 1) {
        echo '<br /><div class="centre">&nbsp;&nbsp;' . $_POST['PageOffset'] . ' ' . _('of') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ': ';
        echo '<select name="PageOffset1">';
        $ListPage = 1;
        while ($ListPage <= $ListPageMax) {
            if ($ListPage == $_POST['PageOffset']) {
                echo '<option value="' . $ListPage . '" selected="selected">' . $ListPage . '</option>';
            } else {
                echo '<option value="' . $ListPage . '">' . $ListPage . '</option>';
            }
            $ListPage++;
        }
        echo '</select>
                    <input type="submit" name="Go1" value="' . _('Go') . '" />
                    <input type="submit" name="Previous" value="' . _('Previous') . '" />
                    <input type="submit" name="Next" value="' . _('Next') . '" />';
        echo '</div>';
    }
    echo '<br />
                    <table cellpadding="2" class="selection">';


    echo '<tr>

                     <th>' . _('工单号') . '</th>   
                     <!--料号-->
                     <th>' . _('料号') . '</th>  
                     
                     <!--日期-->
                     <th>' . _('日期') . '</th> 
                     
                     <th>' . _('入站量') . '</th>           
                     <th>' . _('出站量') . '</th>
                     <!--待出站-->
                      <th>' . _('待出站') . '</th>
                     
                    
                      
                      <th>' . _('工位') . '</th>
                    

                     <th>' . _('良品数量') . '</th>  
                     <th>' . _('报废数量') . '</th>  
                     <th>' . _('报废原因') . '</th>
                     <th>' . _('多种报废') . '</th>                
                     <th>' . _('人员1') . '</th>
                     <th>' . _('人员2') . '</th>
                     <th width=40 >' . '选择' . '</th>
            </tr>';
    $k = 0; //row counter to determine background colour
    $RowIndex = 0;

    if (DB_num_rows($result) <> 0 ) {
        DB_data_seek($result, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);
        $i = 0; //counter for input controls
        //当工单表和明细表中还有行数据时，进行循环
        while (($myrow = DB_fetch_array($result))
        ) {
            if ($k == 1) {
                echo '<tr class="EvenTableRows">';
                $k = 0;
            } else {
                echo '<tr class="OddTableRows">';
                $k = 1;
            }
            $_SESSION['status_id' . $identifier]=100;

            $queryRejectNum = "select sum(scrapCount) as sum
                                from wip_transactions 
                                where wip_entity_name='".$myrow['wip_entity_name'] . "' 
                                and operation='浇注'";
            $resultNum = DB_query($queryRejectNum,$db);
            $rowNum = DB_fetch_array($resultNum);
            $rejectNum = $rowNum['sum'];
            //显示工单表中的
            //工单号
            //料号
            //日期
            //入站量
            //出站量
            //待出站量=入站量 - 出站量 - 报废量
            echo '  <td>' . $myrow['wip_entity_name'] . '
                      <input type="hidden" 
                              name="wip_entity_name'.$myrow['wip_entity_id'].'" 
                              value="'.$myrow['wip_entity_name'].'">
                    </td>
                <td>' . $myrow['stockid'] . '</td>
                <td>' . date("Y-m-d",$myrow['last_update_date']) . '</td>
                <td>' . $myrow['casting_input_quantity'] . '
                       <input type="hidden" 
                              name="casting_input_quantity'.$myrow['wip_entity_id'].'" 
                              value="'.$myrow['casting_input_quantity'].'"></td>
                <td>' . $myrow['casting_output_quantity'] . '
                        <input type="hidden" 
                              name="casting_output_quantity'.$myrow['wip_entity_id'].'" 
                              value="'.$myrow['casting_output_quantity'].'"></td>
                              
                <td id="outputCount'.$myrow['wip_entity_id'].'">' .
                ($myrow['casting_input_quantity'] - $myrow['casting_output_quantity'] - $rejectNum).'</td>';

            // echo '<td>' . $modelStationRow['model_station']  . '</td>';
            $sql5="select station 
                   from wip_modelstation  
                   where enable_flag=1";
            $result5 = DB_query( $sql5,$db);

            echo '<td>
                    <select name="model_station'.$myrow['wip_entity_id'].'">';

            while ($myrow5=DB_fetch_array($result5)) {
                echo '<option >'.$myrow5['station'].'</option >';
            }
            echo "  <option selected='selected'>".$myrow['pour_station']."</option>
                    </select>
                    </td>";


            //显示良品数量
            echo '<td><input 
                        type="text" id="goodProductCount'.$myrow['wip_entity_id'].'" 
                        name="goodProductCount'.$myrow['wip_entity_id'].'" 
                        size="8" 
                        value="' . '" />
             </td>';
            //显示报废数量
            echo '<td><input 
                        id="scrapCount'.$myrow['wip_entity_id'].'"
                        type="text" name="scrapCount'.$myrow['wip_entity_id'].'"       
                        size="8" 
                        value="'  . '" />
             </td> ';
            //显示报废原因
            echo '<td><input type="text" 
                       id="text_select_Scapreason'.$myrow['wip_entity_id'].'"
                       name="scrapReason'.$myrow['wip_entity_id'].'" 
                        size="8" 
                       value="'   . '" />
                      <input type="hidden" 
                       id="text_slect_ScapreasonId'.$myrow['wip_entity_id'].'"
                       name="scrapReasonID'.$myrow['wip_entity_id'].'" 
                        size="8" 
                       value="'   . '" />
                  <a class="btn btn-info btn-xs" 
                    id="selectscrapReason'.$myrow['wip_entity_id'].'" 
                    hfre="###" title="选择报废原因">选择</a>
             </td> ';
            //多种报废
            echo '<td>
                      <a href="addMoreScrapReason.php?id='.$myrow['wip_entity_id'].'&reason_type=pour">填写</a>
                   </td>';

            //显示人员id
            //a标签用来弹出对话框
            //$myrow['wip_entity_id']来标识属于哪个工单号的，用于在批量处理中区分不同的行
            echo '<td><input type="text" 
                      id="text_slect_employename'.$myrow['wip_entity_id'].'" 
                      name="personName'.$myrow['wip_entity_id'].'" 
                       size="8" 
                      value="'  . '" />
                      <input type="hidden" 
                      id="text_slect_employee'.$myrow['wip_entity_id'].'" 
                      name="person1'.$myrow['wip_entity_id'].'" 
                       size="8" 
                      value="'  . '" />
                 <a class="btn btn-info btn-xs" 
                    id="selectEmployee'.$myrow['wip_entity_id'].'" 
                    hfre="###" title="选择人员1">选择</a></td>
             </td>';

            echo '<td><input type="text" 
                      id="text_slect_employename1'.$myrow['wip_entity_id'].'" 
                      name="personName'.$myrow['wip_entity_id'].'" 
                       size="8" 
                      value="'  . '" />
                      <input type="hidden" 
                      id="text_slect_employee1'.$myrow['wip_entity_id'].'" 
                      name="person2'.$myrow['wip_entity_id'].'" 
                      size="8" 
                      value="'  . '" />
                 <a class="btn btn-info btn-xs" 
                    id="selectEmployee1'.$myrow['wip_entity_id'].'" 
                    hfre="###" title="选择人员2">选择</a></td>
             </td>';
            ?>
            <?php
            echo '<td><input type="checkbox" 
                      name="UpdateLine'.$myrow['wip_entity_id'].'" 
                      value="'.$i.'"/>
           </td>';
            echo '<input type="hidden" 
                   name="casting_input_quantity'.$myrow['wip_entity_id'].'" 
                   value="'.$myrow['casting_input_quantity'].'"/>
           ';
            echo '<input type="hidden" 
                   name="casting_output_quantity'.$myrow['wip_entity_id'].'" 
                   value="'.$myrow['casting_output_quantity'].'"/>
           ';
            echo  '
            </tr>';
            $i++;
            $RowIndex++;
            //为每一行的a标签都添加事件
            echo "<script type=\"text/javascript\">
           
     $(document).ready(function(){
           $('#submit').click(function(){
                return check();
           });
           $('#goodProductCount".$myrow['wip_entity_id']."').keyup(function(){
                check();
            });
            $('#badCount".$myrow['wip_entity_id']."').keyup(function(){
                if($('#badCount".$myrow['wip_entity_id']."').val() != null && 
                    $('#badCount".$myrow['wip_entity_id']."').val() != ''
                 ){
                     $('text_select_noGoodReason".$myrow['wip_entity_id']."').attr(
                        \"required\",\"required\"
                     );
                 }
                 check();          
            });
            $('#scrapCount".$myrow['wip_entity_id']."').keyup(function(){
               check();
            });
            function check(){
                var outputCount=$('#outputCount".$myrow['wip_entity_id']."').text();
                var goodProductCount=$('#goodProductCount".$myrow['wip_entity_id']."').val();
                var scrapCount=$('#scrapCount".$myrow['wip_entity_id']."').val();
                var num= goodProductCount*1 + scrapCount*1;
                if(outputCount * 1 < num){
                    alert('数值不符合实际');
                    return false;
                }
                return true;
            }
        
        
       
        $('#selectEmployee".$myrow['wip_entity_id']."').click(function(){
            $('#selectEmployee".$myrow['wip_entity_id']."').dialog(\"open\");
        });
        $('#selectEmployee1".$myrow['wip_entity_id']."').click(function(){
            $('#selectEmployee1".$myrow['wip_entity_id']."').dialog(\"open\");
        });
        $('#selectBadReason".$myrow['wip_entity_id']."').click(function(){
            $('#selectBadReason".$myrow['wip_entity_id']."').dialog(\"open\");
        });
         $('#selectscrapReason".$myrow['wip_entity_id']."').click(function(){
            $('#selectscrapReason".$myrow['wip_entity_id']."').dialog(\"open\");
        });
        $('#selectEmployee".$myrow['wip_entity_id']."').dialog({
            enable:true,
            title:'选择人员',
            width: '950px',
            height: '470px',
            content:'url:BtnSearchemployee.php?fwValue=" .$myrow['wip_entity_id']."&cat=buliao',
            init:function(){
                 this.content.document.getElementById('cat').value = 'buliao';
                 this.content.document.getElementById('fwValue').value = '".$myrow['wip_entity_id']."';
            }
        });
        $('#selectEmployee1".$myrow['wip_entity_id']."').dialog({
            enable:true,
            title:'选择人员',
            width: '950px',
            height: '470px',
            content:'url:BtnSearchemployee1.php?fwValue=" .$myrow['wip_entity_id']."&cat=buliao',
            init:function(){
                 this.content.document.getElementById('cat').value = 'buliao';
                 this.content.document.getElementById('fwValue').value = '".$myrow['wip_entity_id']."';
            }
        });

        $('#selectscrapReason".$myrow['wip_entity_id']."').dialog({
            enable:true,
            title:'选择报废原因',
            width: '950px',
            height: '470px',
            content:'url:btnSearchScapReasons.php?fwValue=" .$myrow['wip_entity_id']."&type=pour',
            init:function(){
                 this.content.document.getElementById('cat').value = 'buliao';
                 this.content.document.getElementById('fwValue').value = '".$myrow['wip_entity_id']."';
            }
        });
        
        $('#selectBadReason".$myrow['wip_entity_id']."').dialog({
            enable:true,
            title:'选择不良原因',
            width: '950px',
            height: '470px',
            content:'url:btnSearchBadReasons.php?fwValue=" .$myrow['wip_entity_id']."&type=pour',
            init:function(){
                 this.content.document.getElementById('cat').value = 'buliao';
                 this.content.document.getElementById('fwValue').value = '".$myrow['wip_entity_id']."';
            }
        });       
     });
  </script>";
            //end of page full new headings if
        } //end loop through customers
        echo '</table>';
        echo '<input type="hidden" name="JustSelectedACustomer" value="Yes" />';
    }

    if (isset($ListPageMax) AND $ListPageMax > 1) {
        echo '<br /><div class="centre">&nbsp;&nbsp;' . $_POST['PageOffset'] . ' ' . _('of') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ': ';
        echo '<select name="PageOffset2">';
        $ListPage = 1;
        while ($ListPage <= $ListPageMax) {
            if ($ListPage == $_POST['PageOffset']) {
                echo '<option value="' . $ListPage . '" selected="selected">' . $ListPage . '</option>';
            } //$ListPage == $_POST['PageOffset']
            else {
                echo '<option value="' . $ListPage . '">' . $ListPage . '</option>';
            }
            $ListPage++;
        } //$ListPage <= $ListPageMax
        echo '</select>
                        <input type="submit" name="Go2" value="' . _('Go') . '" />
                        <input type="submit" name="Previous" value="' . _('Previous') . '" />
                        <input type="submit" name="Next" value="' . _('Next') . '" />';
        echo '</div>';
    }//end if results to show

    echo '<a name="end"></a><br /><div class="centre"><input id="submit" type="submit" name="UpdateStatus"  value="修改确认" />
</div>  
  ';

}

?>
<?php
echo '</div>
      </form>';
include('includes/footer.inc');
?>

