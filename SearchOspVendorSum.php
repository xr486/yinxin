<?php
 ob_start();
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
include('includes/session.inc');
$Title = _('外协采购单统计');
$ViewTopic= '外协采购单统计';
$BookMark = '外协采购单统计';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('外协采购单统计') . '</p>';
echo '<table cellpadding="3" class="selection">';

echo '<tr> ';
  if (!isset($_POST['FromDate'])) 
{
  $date = date('Ymd');
  $_POST['FromDate']=substr($date,0,4);
  $_POST['ToDate']=substr($date,4,2);
}

echo '<td>年</td>
		<td><input type="text"   autocomplete="off"     name="FromDate" maxlength="10" size="11" value="' . $_POST['FromDate'] . '" /></td>
		<td>月</td>
		<td> 
		<select name="ToDate" id="">'; 
				 
					$sql = "select type_name from  wip_types where type_code='月' ";
					$result = DB_query($sql,$db);
					while ($v = DB_fetch_array($result)) {
						if ($v['type_name']==$_POST['ToDate'] ) {
				 
				echo '<option value="'.$v['type_name'].'" selected="selected">'.$v['type_name'].'</option>';
				  }else{ 
				echo '<option value="'.$v['type_name'].'">'.$v['type_name'].'</option>';
				 		}
					}
				 
			echo '</select></td>

	</tr>';

echo '</table><div class="centre"><input type="submit" name="Search" value="查找"> </div>'
 . '</br>';

 

	

 $from=date('Ymd',strtotime($_POST['FromDate'].'-'.$_POST['ToDate'].'-'.'01') );
  
 
 $to=date('Ymd',strtotime($_POST['FromDate'].'-'.$_POST['ToDate'].'-'.'20') );
 
 $oldDate = strtotime(date('Y-m',strtotime("$from -10 day")).'-'.'21');
 $time = strtotime($to) + 86400;


$sql6 = "SELECT   a.vendor_code,b.vendor_name,sum(c.line_amount) as amount ,sum(e.osp_yugu_price*c.transaction_quantity)  as osp_yugu_amount
		  from po_rcv_receipt_header a,vendors b,po_rcv_receipt_line c,po_lines_all d,wip_operation_plan e
	     where a.delivery_date > ".$oldDate." and a.delivery_date < ".$time."
		 and a.vendor_code=b.vendor_code and a.receipt_num=c.receipt_num
		 and d.po_num=c.po_num 	 and  d.line=c.po_line 
and e.wip_entity_name=d.wip_entity_name and e.operation_seq_num=d.operation_seq_num
	     GROUP BY a.vendor_code,b.vendor_name
		 order by sum(c.line_amount) desc";
$result6 = DB_query($sql6,$db);
while($array6 = mysqli_fetch_assoc($result6)){
    $arrays6[] = $array6;
}
$all_ruku_amount=0;
if (is_array($arrays6)){
    foreach ($arrays6 as $key => $value) {
        $arramount6[]  = $value['amount']; 
        $arraospyugu6[]  = round($value['osp_yugu_amount'],2); 
		$all_ruku_amount=$all_ruku_amount + $value['amount']; 
		$arrallamount6[]  = $all_ruku_amount; 
        $arrvendor6[]  = $value['vendor_code'];
    }
}else{
    $arramount6[]  = 0; 
    $arraospyugu6[]  = 0;
    $arrvendor6[]  = 0;
	$arrallamount6[]  =0;
} 

 
$sql8 = "SELECT  a.vendor_code, b.vendor_name, sum(d.line_amount) as amount,sum(e.osp_yugu_price*d.quantity)  as osp_yugu_amount
		  from po_headers_all a,vendors b,po_lines_all d,wip_operation_plan e
	     where a.creation_date > ".$oldDate." and a.creation_date < ".$time."
		 and a.vendor_code=b.vendor_code 
		  and a.po_num=d.po_num  
and e.wip_entity_name=d.wip_entity_name and e.operation_seq_num=d.operation_seq_num
	     GROUP BY  a.vendor_code, b.vendor_name
		 order by sum(d.line_amount) desc";
 
$result8 = DB_query($sql8,$db);
while($array8 = mysqli_fetch_assoc($result8)){
    $arrays8[] = $array8;
}
$all_ruku_amount=0;
if (is_array($arrays8)){
    foreach ($arrays8 as $key => $value) {
        $arramount8[]  = $value['amount']; 
        $arraosp_yugu_amount8[]  = round($value['osp_yugu_amount'],2); 
		$all_ruku_amount=$all_ruku_amount + $value['amount']; 
		$arrallamount8[]=$all_ruku_amount;
        $arrvendor8[]  = $value['vendor_code'];
    }
}else{
    $arramount8[]  = 0; 
    $arraosp_yugu_amount8[]  = 0; 
	$arrallamount8[]=0;
    $arrvendor8[]  = 0;
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
          
     
           
//新的一组 begin
	  echo '<table class="selection" align="center"  >';	 
            $tableheader = '
			<tr><th colspan="4"> 外协下单金额汇总</th><tr>
			<tr>  <th width =150>' . '供应商编号' . '</th>
								 <th width =250>' . '供应商名称' . '</th> 
                                       <th width =100  >' . '外协核定金额' . '</th>
                                       <th width =100  >' . '下单金额' . '</th></tr>';
            echo $tableheader;
	 $result88 = DB_query($sql8,$db);
   while ($myrow88 = DB_fetch_array($result88)) {
	   $allosp_yugu_amount=$allosp_yugu_amount+round($myrow88['osp_yugu_amount'],2);
	   $all_amount=$all_amount + $myrow88['amount'];
	    echo '   <tr> <td>' . $myrow88['vendor_code'] . '</td>
		             <td>' . $myrow88['vendor_name'] . '</td>
                      <td class="number">' . round($myrow88['osp_yugu_amount'],2) . '</td> 
                      <td class="number">' . $myrow88['amount'] . '</td> 
					  </tr>
                     ';
    }  
	  echo '   <tr> <td>合计</td>
		             <td> </td>
                      <td class="number">' . round($allosp_yugu_amount,2) . '</td> 
                      <td class="number">' . $all_amount . '</td> 
					  </tr>
                     ';
 echo '</table>';
 
 
//  echo '<a href="' . $RootPath . '/SearchOspVendorSumSedExcel.php?FromDate=' . $_POST['FromDate'] . '&ToDate=' . $_POST['ToDate'] . '">' . '资料导出Excel表' . '</a>';

 
 

		 echo '	 <table style="margin-top:10px;" >  <tr>  
                <td><div id="waixie" style="width: 1300px;  height:250px;"></div></td>
                <td><div style="width: 30px;  height:250px;"></div></td>
            </tr>		
             </table>

			 ';
  //新的一组 end

  

  //新的一组 begin
	  echo '<table class="selection" align="center"  >';	 
            $tableheader = '
			<tr><th colspan="4"> 外协入库金额统计</th><tr>
			<tr>  <th width =150>' . '供应商编号' . '</th>
								 <th width =250>' . '供应商名称' . '</th> 
                                       <th width =100  >' . '外协核定金额' . '</th>
                                       <th width =100  >' . '入库金额' . '</th></tr>';
            echo $tableheader;
	 $result88 = DB_query($sql6,$db);
   while ($myrow88 = DB_fetch_array($result88)) {
	    $all_osp_yugu_amount=$all_osp_yugu_amount+round($myrow88['osp_yugu_amount'],2);
	   $all_amount=$all_amount + $myrow88['amount'];
	    echo '   <tr> <td>' . $myrow88['vendor_code'] . '</td>
		             <td>' . $myrow88['vendor_name'] . '</td>
                      <td class="number">' . round($myrow88['osp_yugu_amount'],2) . '</td> 
                      <td class="number">' . $myrow88['amount'] . '</td> 
					  </tr>
                     ';
    }  
	 echo '   <tr> <td>合计</td>
		             <td> </td>
                      <td class="number">' . round($all_osp_yugu_amount,2) . '</td> 
                      <td class="number">' . $all_amount . '</td> 
					  </tr>
                     ';
 echo '</table>   ';
 
 
//  echo '<a href="' . $RootPath . '/SearchOspVendorSumExcel.php?FromDate=' . $_POST['FromDate'] . '&ToDate=' . $_POST['ToDate'] . '">' . '资料导出Excel表' . '</a>';

		 echo '	 <table style="margin-top:10px;" >  <tr>  
                <td><div id="waixieruku" style="width: 1300px;  height:250px;"></div></td>
                <td><div style="width: 30px;  height:250px;"></div></td>
            </tr>		
             </table>';
  //新的一组 end
   echo '  </body>';

echo "<script>"; 
echo ";   var num6 = "; 
echo json_encode($arramount6); 

echo ";   var ospyugu6 = "; 
echo json_encode($arraospyugu6); 

echo ";   var all6 = "; 
echo json_encode($arrallamount6); 
echo ";   var num8 = "; 
echo json_encode($arramount8);
echo ";   var ospyugu8 = "; 
echo json_encode($arraosp_yugu_amount8);   
echo ";   var all8 = "; 
echo json_encode($arrallamount8); 

// 日期x轴
echo "; var customer6 = ";
echo json_encode($arrvendor6); 
echo "; var customer8 = ";
echo json_encode($arrvendor8); 
 

echo "; 
    var ydata = []
    
    var myChart6 = echarts.init(document.getElementById('waixieruku'));  
    var myChart8 = echarts.init(document.getElementById('waixie')); 
    
	option6 = {
        title: {
            text: '外协采购入库金额汇总'
        },
        color: ['#3398DB','#FF9F7F','#66CC99'], 
        tooltip: {
            trigger: 'axis',
            axisPointer: {            // 坐标轴指示器，坐标轴触发有效
                type: 'shadow'        // 默认为直线，可选为：'line' | 'shadow'
            }
        },
		legend: {
        data: ['入库金额','核定金额','累计入库金额'],
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
            name: '供应商',
            nameTextStyle: {
                padding: [0, 0, -25, -25]    // 四个数字分别为上右下左与原位置距离
            },
            type: 'category',
            data: customer6,
            axisLabel:{
                showMaxLabel:true,
            }
            
        }     ,
        yAxis: [
            {
                name: '入库金额',
                type: 'value'
            }
        ],
        series: [
            {
                name: '核定金额',
                type: 'bar',
                barWidth: '40%',
                data: ospyugu6,
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
            },{
                name: '入库金额',
                type: 'bar',
                barWidth: '40%',
                data: num6,
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
            },{
                name: '累计入库金额',
                type: 'line',
                barWidth: '60%',
                data: all6,
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


	option8 = {
        title: {
            text: '外协订单金额汇总'
        },
        color: ['#3398DB','#FF9F7F','#66CC99'], 
        tooltip: {
            trigger: 'axis',
            axisPointer: {            // 坐标轴指示器，坐标轴触发有效
                type: 'shadow'        // 默认为直线，可选为：'line' | 'shadow'
            }
        },
		legend: {
        data: ['订单金额','外协核定金额','订单累计金额'],
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
            name: '外协供应商',
            nameTextStyle: {
                padding: [0, 0, -25, -25]    // 四个数字分别为上右下左与原位置距离
            },
            type: 'category',
            data: customer8,
            axisLabel:{
                showMaxLabel:true,
            }
            
        }     ,
        yAxis: [
            {
                name: '金额',
                type: 'value'
            }
        ],
        series: [
            {
                name: '订单金额',
                type: 'bar',
                barWidth: '40%',
                data: num8,
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
            },{
                name: '外协核定金额',
                type: 'bar',
                barWidth: '40%',
                data: ospyugu8,
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
            },
			{
                name: '订单累计金额',
                type: 'line',
                barWidth: '60%',
                data: all8,
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
	
   
    
    myChart8.setOption(option8);  
    myChart6.setOption(option6);   
 
    </script>";
 
echo '</div></form>';
include('includes/footer.inc');