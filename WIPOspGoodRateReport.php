<?php
 ob_start();
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
include('includes/session.inc');
$Title = _('外协合格率');
$ViewTopic= '外协合格率';
$BookMark = '外协合格率';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '
        <div class="centre" style="margin-bottom: 5px; display: flex;justify-content: flex-start;align-items: center;">
        <input type="submit" name="Search" value="查找">  &nbsp;&nbsp;
          
        </div>
';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('外协合格率') . '</p>';
echo '<table cellpadding="3" class="selection">';

echo '<tr> ';
 

echo '<div class="text-nav">';
echo '<div class="text-nav-1"><div>' . _('工单号') . ':</div>';
echo '<input type="text"   autocomplete="off"   name="wip_entity_name" value="' . $_POST['wip_entity_name'] . '"  size="20" maxlength="25"/></div>';
 
echo '<div class="text-nav-1"><div>' . _('供应商代号') . ':</div>';
echo '<input type="text"   autocomplete="off"   name="vendor_code"   value="' . $_POST['vendor_code'] . '" size="20" maxlength="25" />';
echo '</div>';

echo '<div class="text-nav-1"><div>' . _('零件图号') . ':</div>';
echo '<input type="text"   autocomplete="off"   name="primary_item"   value="' . $_POST['primary_item'] . '" size="20" maxlength="25" />';
echo '</div>';

echo '<div class="text-nav-1"><div>' . _('零件名称') . ':</div>';
echo '<input type="text"   autocomplete="off"   name="item_name"   value="' . $_POST['item_name'] . '" size="20" maxlength="25" />';
echo '</div>';
 

 
 
if (!isset($_POST['FromDate'])) 
{
  $_POST['FromDate'] = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 30, date("Y")));
}
if (!isset($_POST['ToDate'])) 
{
  $_POST['ToDate'] = Date('Y-m-d');
}
if (!isset($_POST['baodao_date'])) {
  $_POST['baodao_date'] = Date('Y-m-d');
 }
 


echo '<div class="text-nav-1"><div>' . _('生产日期起') . ':</div>';
echo '<input type="text"   autocomplete="off"   onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="FromDate" maxlength="10" size="10" value="' . $_POST['FromDate'] . '" />';
echo '</div>';

echo '<div class="text-nav-1"><div>' . _('生产日期止') . ':</div>';
echo '<input type="text"   autocomplete="off"   onfocus="WdatePicker()" alt="' . $_SESSION['DefaultDateFormat'] . '" name="ToDate" maxlength="10" size="10" value="' . $_POST['ToDate'] . '" />';
echo '</div>';

echo '</table><div class="centre"></div>'
 . '</br>';

 
 

 $sql = "SELECT e.vendor_code,e.vendor_name, sum( transaction_quantity)  good_quantity, 
 (SELECT sum( bad_quantity )
FROM wip_bad_transactions d 
WHERE d.transaction_type = '外协异常'  ";

	if (empty($_POST['FromDate']) == 0) {
    $SQL_FromDate = strtotime($_POST['FromDate']);
    $sql .= " and a.transaction_date >= '" . $SQL_FromDate . "' ";
  }
  if (empty($_POST['ToDate']) == 0) {
    $SQL_ToDate = strtotime($_POST['ToDate']) + 86400;
    $sql .= " and a.transaction_date <='" . $SQL_ToDate . "' ";
  }


$sql = $sql . " AND d.wip_entity_name = a.wip_entity_name) bad_quantity
FROM wip_transactions a,so_headers_all b,so_lines_all c,vendors e
WHERE a.transaction_type = '外协入库' and a.line_code=e.vendor_code 
and a.wip_entity_name=c.wip_entity_name and b.order_number=c.order_number 
 ";

  if (isset($_POST['wip_entity_name']) and $_POST['wip_entity_name'] != '') {
    $sql = $sql . " and  a.wip_entity_name " . LIKE . " '%" . $_POST['wip_entity_name'] . "%' ";
  }
  if (isset($_POST['primary_item']) and $_POST['primary_item'] != '') {
    $sql = $sql . " and c.item_name " . LIKE . " '%" . $_POST['primary_item'] . "%' ";
  }

  if (isset($_POST['item_name']) and $_POST['item_name'] != '') {
    $sql = $sql . " and c.item_name " . LIKE . " '%" . $_POST['item_name'] . "%' ";
  }
 
 if (isset($_POST['vendor_code']) and $_POST['vendor_code'] != '') {
    $sql = $sql . " and a.line_code " . LIKE . " '%" . $_POST['vendor_code'] . "%' ";
  }
 

  if (empty($_POST['FromDate']) == 0) {
    $SQL_FromDate = strtotime($_POST['FromDate']);
    $sql .= " and a.transaction_date >= '" . $SQL_FromDate . "' ";
  }
  if (empty($_POST['ToDate']) == 0) {
    $SQL_ToDate = strtotime($_POST['ToDate']) + 86400;
    $sql .= " and a.transaction_date <='" . $SQL_ToDate . "' ";
  }

  $sql .= " GROUP BY e.vendor_code,e.vendor_name  ";
 
$result7 = DB_query($sql,$db);
while($array7 = mysqli_fetch_assoc($result7)){
    $arrays7[] = $array7;
}
$arrallamount =0;
if (is_array($arrays7)){
    foreach ($arrays7 as $key => $value) {
		$arrallamount=  $value['bad_quantity'] +$value['good_quantity'];
		if ($arrallamount >0) {
        $arramount7[]  =round( $value['good_quantity']*100/$arrallamount);  
		} else {
		 $arramount7[]  =0;
		}

        $arroperation_code7[]  = $value['vendor_name'];
    }
}else{
    $arramount7[]  = 0;  
    $arroperation_code7[]  = 0;
} 

 


 

echo '<!doctype html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport"
              content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>初始页面</title>
        <script src="./js/jquery-2.1.0.js" type="text/javascript"></script>
        <script src="./js/echarts.js" type="text/javascript"></script>  
    </head></head>
    <body  >';
          
        echo ' 
    <table class="selection" align="center" ">';
	 
            $tableheader = '
			<tr><th colspan="6">外协生产良率报表</th><tr>
			<tr>
	                         <th   >' . '供应商名称' . '</th>
		  <th   >' . '产出数量' . '</th>
		  <th   >' . '良品数量' . '</th>
		  <th   >' . '不良数量' . '</th>
		  <th  width = 50>' . '良品率' . '</th>   

                                       
				</tr>';
            echo $tableheader;
	 $result88 = DB_query($sql,$db);
	 $total_amount=0;
 while ($myrow88 = DB_fetch_array($result88)) {

   $all_bad_quantity = $all_bad_quantity+ $myrow88['bad_quantity'];

   $all_good_quantity = $all_good_quantity+$myrow88['good_quantity'];

	     $all_qty = $all_good_quantity+$all_bad_quantity['good_quantity+bad_quantity'];
	  
    if ($all_qty>0) {
	    $rate=round(($myrow88['good_quantity']*100/$all_qty),2);
	  }
	                    echo '   <tr> <td>' . $myrow88['vendor_name'] . '</td> 
                      <td  class="number">' .  $all_qty   . '</td> 
		                  <td>' . round($myrow88['good_quantity']) . '</td>
                      <td  class="number">' . round($myrow88['bad_quantity']) . '</td>
                      <td  class="number">' .$rate . '%'.'</td>  </tr>
                     ';
                  
	  
 }  

  echo '<tr> <td>合计</td>  <td>'.$all_qty.'</td> <td>'.$all_good_quantity.'</td> <td>'.$all_bad_quantity.'</td> <td></td>  </tr>'; 
  echo '</table>    ';
			
			 echo '
        <table  style="margin-top:10px;" > <tr>  
                <td><div id="poruku" style="width: 1300px;  height:250px;"></div></td>
                <td><div style="width: 30px;  height:150px;"></div></td> </tr> </table>';
           

  
   echo '  </body>';

echo "<script>"; 

echo ";   var num7 = "; 
echo json_encode($arramount7); 

// 日期x轴

echo "; var customer7 = ";
echo json_encode($arroperation_code7);
 
 

echo "; 
    var ydata = []
    
   
    var myChart7 = echarts.init(document.getElementById('poruku')); 
    
	
option7 = {
        title: {
            text: '外协生产良率报表'
        },
        color: ['#3398DB'], 
        tooltip: {
            trigger: 'axis',
            axisPointer: {            // 坐标轴指示器，坐标轴触发有效
                type: 'shadow'        // 默认为直线，可选为：'line' | 'shadow'
            }
        },
		legend: {
        data: ['良率'],
		top:0,
		left:'center',
        textStyle: {color: 'red'}     
        } ,
        grid: {
            left: '3%',
            right: '4%',
            bottom: '3%',
            containLabel: true
        },
         xAxis: {
            name: '工序',
            nameTextStyle: {
                padding: [0, 0, -25, -25]    // 四个数字分别为上右下左与原位置距离
            },
            type: 'category',
            data: customer7,
            axisLabel:{
                showMaxLabel:true,
            }
            
        }     ,
        yAxis: [
            {
                name: '良率',
                type: 'value'
            }
        ],
        series: [
            {
                name: '良率',
                type: 'bar',
                barWidth: '60%',
                data: num7,
				itemStyle: {
					normal: {
					 label: {
					  show: true,
					  position: 'top',
					  textStyle:{
						  color: 'black',
						  fontsize:16 
					  }
					 }
					}
				
				}
            } 
        ]
    };

	
	 
    myChart7.setOption(option7);    
 
    </script>";
 
echo '</div></form>';
include('includes/footer.inc');