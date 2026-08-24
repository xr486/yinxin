<?php
include('includes/session.inc');
include('includes/SQL_CommonFunctions.inc');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/e-kanban/index.css">
    <script src="./js/jquery-2.1.0.js" type="text/javascript"></script>
    <script src="./js/echarts.js" type="text/javascript"></script>
    <title>电子看板</title>
</head>

<body>
    <div class="head">
        <h1 class="all_title">采购看板</h1>
    </div>
    <div class="kanban_all">
        <div class="kanban_all_position">
            <div class="kanban_item">
                <div class="kanban_line">
                    <div class="kanban_title">
                        近一月供应商新增采购单含税金额前五榜单
                    </div>
                    <div class="kanban_content">
                        <table>
                            <tr>
                                <td>
                                    <div id="poruku" style="width:100%;height:220px;"></div>
                                </td>
                                <td>
                                    <div style="width: 30px; height:220px;"></div>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="kanban_item">
                <div class="kanban_line">
                    <div class="kanban_title">
                        近一月不同采购类型的含税总金额
                    </div>
                    <div class="kanban_content">
                        <div class="kanban_content_left">
                            <table>
                                <tr>
                                    <td>
                                        <div id="poruku1" style="width:100%;height:220px;"></div>
                                    </td>
                                    <td>
                                        <div style="width: 30px; height:220px;"></div>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="kanban_content_right">
                            <div>
                                <ul>
                                    <li style="color:#73c0de"><span id="yewu_top1"></span></li>
                                    <li style="color:#fd6f6f"><span id="yewu_top2"></span></li>
                                    <li style="color:#fac858"><span id="yewu_top3"></span></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="kanban_item">
                <div class="kanban_line">
                    <div class="kanban_title">
                        近一周新增采购单含税金额分析
                    </div>
                    <div class="kanban_content">
                        <table>
                            <tr>
                                <td>
                                    <div id="poruku2" style="width:100%;height:220px;"></div>
                                </td>
                                <td>
                                    <div style="width: 30px; height:220px;"></div>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="kanban_item">
                <div class="kanban_line_double">
                    <div class="right">
                        <div class="kanban_line">
                            <div class="kanban_title">
                                近一月产品新增采购单含税金额前五榜单
                            </div>
                            <div class="kanban_content">
                                <div class="kanban_content_left1">
                                    <table>
                                        <tr>
                                            <td>
                                                <div id="poruku4" style="width:100%;height:220px;"></div>
                                            </td>
                                            <td>
                                                <div style="width: 30px; height:220px;"></div>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="kanban_content_right1">
                                    <div>
                                        <ul>
                                            <li style="color:#73c0de">Top1:<span id="cus_delivery_top1"></span></li>
                                            <li style="color:#fd6f6f">Top2:<span id="cus_delivery_top2"></span></li>
                                            <li style="color:#fac858">Top3:<span id="cus_delivery_top3"></span></li>
                                            <li style="color:#91cc75">Top4:<span id="cus_delivery_top4"></span></li>
                                            <li style="color:#5470c6">Top5:<span id="cus_delivery_top5"></span></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="middle"></div>
                    <div class="left">
                        <div class="kanban_line">
                            <div class="kanban_title">
                                近一月供应商入库金额前五榜单
                            </div>
                            <div class="kanban_content">
                                <div class="kanban_content_left1">
                                    <table>
                                        <tr>
                                            <td>
                                                <div id="poruku3" style="width:100%;height:220px;"></div>
                                            </td>
                                            <td>
                                                <div style="width: 30px; height:220px;"></div>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="kanban_content_right1">
                                    <div>
                                        <ul>
                                            <li style="color:#73c0de">Top1:<span id="vendor_top1"></span></li>
                                            <li style="color:#fd6f6f">Top2:<span id="vendor_top2"></span></li>
                                            <li style="color:#fac858">Top3:<span id="vendor_top3"></span></li>
                                            <li style="color:#91cc75">Top4:<span id="vendor_top4"></span></li>
                                            <li style="color:#5470c6">Top5:<span id="vendor_top5"></span></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="kanban_item">
                <div class="kanban_line">
                    <div class="kanban_title">
                        近一月未入库采购单
                    </div>
                    <div class="kanban_content scroll" style="overflow-y: auto; max-height: 200px;">
                        <?php
                        $time = time();
                        $sql5 = 'SELECT pha.po_num, pha.status, pha.note,  pla.chang,pla.kuan,pla.gao,pla.depart_name,pla.line_amount,pla.quantity,pla.line,pla.price,v.vendor_code, v.vendor_name, pla.need_date, pha.created_by,pha.creation_date,b.item_no,b.item_desc,b.item_name,b.units,
                                ifnull(pla.quantity_received,0) this_received
                                FROM po_headers_all pha, po_lines_all pla, vendors v,sf_item_no b
                                WHERE pla.po_num = pha.po_num
                                AND  b.item_no=pla.stockid
                                and pla.quantity-pla.quantity_received>0
                                AND v.vendor_code = pha.vendor_code ';
                        $sql5 .= " and pha.status = '已签核'";
                        $sql5 .= " order by pha.creation_date  DESC";
                        $result5 = DB_query($sql5, $db);
                        // 检查查询是否成功
                        if ($result5) {
                            // 检查查询结果是否为空
                            if (mysqli_num_rows($result5) > 0) {
                        ?>
                                <table class="table1">
                                    <tr>
                                        <th class="th1">供应商代码</th>
                                        <th class="th2">采购单号</th>
                                        <th class="th1">料号</th>
                                        <th class="th3">采购数量</th>
                                        <th class="th3">收货量</th>
                                        <th class="th3">待收量</th>
                                        <th class="th2">建立日期</th>
                                    </tr>
                                    <?php
                                    while ($row = mysqli_fetch_assoc($result5)) {
                                    ?>
                                        <tr>
                                            <td class="td1"><?php echo $row['vendor_code']; ?></td>
                                            <td class="td2"><?php echo $row['po_num']; ?></td>
                                            <td class="td3"><?php echo $row['item_no']; ?></td>
                                            <td class="td3"><?php echo $row['quantity']; ?></td>
                                            <td class="td3"><?php echo $row['this_received']; ?></td>
                                            <td class="td3"><?php echo ($row['quantity'] - $row['this_received']); ?></td>
                                            <td class="td2"><?php echo date('Y-m-d', $row['creation_date']); ?></td>
                                        </tr>
                                    <?php
                                    }
                                    ?>
                                </table>
                                <script>
                                    //悬停
                                    // // JavaScript函数：停止滚动
                                    // function stopScroll() {
                                    //     var table = document.querySelector('.scroll table');
                                    //     table.style.animationPlayState = 'paused';
                                    // }
                                    // // JavaScript函数：开始滚动
                                    // function startScroll() {
                                    //     var table = document.querySelector('.scroll table');
                                    //     table.style.animationPlayState = 'running';
                                    // }
                                </script>
                        <?php
                            } else {
                                echo "No results found.";
                            }
                        } else {
                            echo "Query failed.";
                        }
                        ?>
                    </div>
                </div>
            </div>
            <div class="kanban_item">
                <div class="kanban_line_double">
                    <div class="left">
                        <div class="kanban_line">
                            <div class="kanban_title">
                                近一月不同仓库入库数量前五榜单
                            </div>
                            <div class="kanban_content">
                                <table>
                                    <tr>
                                        <td>
                                            <div id="poruku6" style="width:100%;height:220px;"></div>
                                        </td>
                                        <td>
                                            <div style="width: 30px; height:220px;"></div>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="middle"></div>
                    <div class="right">
                        <div class="kanban_line">
                            <div class="kanban_title">
                                近一月产品入库数量前五榜单
                            </div>
                            <div class="kanban_content">
                                <div class="kanban_content_left1">
                                    <table>
                                        <tr>
                                            <td>
                                                <div id="poruku7" style="width:100%;height:220px;"></div>
                                            </td>
                                            <td>
                                                <div style="width: 30px; height:220px;"></div>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="kanban_content_right1">
                                    <div>
                                        <ul>
                                            <li style="color:#73c0de">Top1:<span id="product_delivery_top1"></span></li>
                                            <li style="color:#fd6f6f">Top2:<span id="product_delivery_top2"></span></li>
                                            <li style="color:#fac858">Top3:<span id="product_delivery_top3"></span></li>
                                            <li style="color:#91cc75">Top4:<span id="product_delivery_top4"></span></li>
                                            <li style="color:#5470c6">Top5:<span id="product_delivery_top5"></span></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
</body>

</html>




<?php
// 近一月供应商新增采购单含税金额
$sql = "SELECT po.vendor_code,ve.vendor_name, 
            SUM(po_all_amount) AS total_amount
        FROM po_headers_all po,vendors ve
        WHERE po.creation_date >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 1 MONTH))
        AND po.vendor_code = ve.vendor_code
        GROUP BY vendor_code
        ORDER BY total_amount desc
        LIMIT 5
";
$result = DB_query($sql, $db);

while ($array = mysqli_fetch_assoc($result)) {
    $arrays[] = $array;
}

if (is_array($arrays)) {
    foreach ($arrays as $key => $value) {
        $vendor[]  = $value['vendor_code'];
        $amount[]  = $value['total_amount'];
    }
} else {
    $vendor[]  = 0;
    $amount[]  = 0;
}
sort($amount);
echo "<script>";
echo ";   var vendor = ";
echo json_encode($vendor);
echo "; var amount = ";
echo json_encode($amount);
echo ";  
    var myChart = echarts.init(document.getElementById('poruku')); 
    option = {         
        //图形颜色
        color: [
            '#05c798'
        ],
        //提示框，鼠标悬停在图形上的注解
        tooltip: {
            trigger: 'axis',
            axisPointer: {            
                type: 'line'       
            },
            backgroundColor: 'rgba(255, 255, 255, 0.7)', // 设置提示条的背景颜色
            textStyle: {
                color: 'rgba(0, 0, 0)', // 设置提示条文本的颜色为白色
                fontSize: 14 // 设置提示条文本的字体大小为12px
            }
        },
        //配置网格组件，用于定义图表的位置和大小
        grid: {
            top: '15%',  // 增加top的值来创建间距
            left: '1%',
            right: '10%',
            bottom: '2%',  // 增加bottom的值来创建间距
            containLabel: true, //自动计算并包含坐标轴标签、刻度和标题等内容在内。
        },
        //横坐标
         xAxis: {
            name: '含税金额',
            type: 'value', //数据类型为数值型。
            axisLine: {
                lineStyle: {
                    color: '#6691b5'  // 设置 x 坐标轴线的颜色
                }
            },
            axisLabel: {
                fontSize: 14  // 设置横轴标签字体大小为14
            },
            nameTextStyle: {
                fontSize: 14  // 设置横轴名称字体大小为14
            },
            splitLine: {
                show: false  // 隐藏纵坐标轴的背景横线
            },   
        },
        //纵坐标
        yAxis: [
            {
                name: '供应商',
                type: 'category', //横坐标数据类型为类别型，适用于离散的数据
                data: vendor,
                axisLine: {
                    lineStyle: {
                        color: '#6691b5'  // 设置 x 坐标轴线的颜色
                    }
                },
                axisLabel: {
                    fontSize: 14  // 设置横轴标签字体大小为14
                },
                nameTextStyle: {
                    fontSize: 14  // 设置横轴名称字体大小为14
                }
            },
            
        ],
        series: [
            {
                type: 'bar',
                barWidth: '15',
                data: amount, //设置横坐标的数据，使用变量中的数据。   
                itemStyle: {
                    normal: {
                        label: {
                            show: true,
                            position: 'right',
                            textStyle: {
                                color: '#6691b5',
                                fontSize: 14
                            }
                        }
                    }
                }
            },
        ],
    };
    let currentIndex = -1;
    setInterval(function() {
        var dataLen = option.series[0].data.length;
        // 取消之前高亮的图形
        myChart.dispatchAction({
            type: 'downplay',
            seriesIndex: 0,
            dataIndex: currentIndex
        });
        currentIndex = (currentIndex + 1) % dataLen;
        // 高亮当前图形
        myChart.dispatchAction({
            type: 'highlight',
            seriesIndex: 0,
            dataIndex: currentIndex
        });
        // 显示 tooltip
        myChart.dispatchAction({
            type: 'showTip',
            seriesIndex: 0,
            dataIndex: currentIndex
        });
    }, 1000);
    myChart.setOption(option);    
    </script>";

//近一月不同采购类型的含税总金额
$sql1 = "SELECT po.order_type,po.po_all_amount,
        SUM(po.po_all_amount) AS total_amount
        FROM po_headers_all po
        WHERE po.creation_date >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 1 MONTH))
        GROUP BY po.order_type
        ORDER BY total_amount desc
        LIMIT 5
";
$result1 = DB_query($sql1, $db);
$index = 1;
while ($array1 = mysqli_fetch_assoc($result1)) {
    $name = $array1['order_type'];
    $value = $array1['total_amount'];
    $title = 'TOP' . $index;
    if (!$value) {
        $value = 0;
    }
    $array_data1[] = array(
        'name' => $name,
        'value' => $value,
        'title' => $title
    );
    $index++;
}
echo "<script>";
// echo "console.log(" . json_encode($array_data1) . ");";
echo ";   var array1 = ";
echo json_encode($array_data1);
// 对数据项数量进行判断
$arrayLength = count($array_data1);
for ($i = 1; $i <= 3; $i++) {
    //输出数据到前端显示
    echo "
    if (array1[" . ($i - 1) . "]) {
        document.getElementById('yewu_top" . $i . "').innerText = array1[" . ($i - 1) . "].name + ':' + array1[" . ($i - 1) . "].value;
    } else {
        document.getElementById('yewu_top" . $i . "').innerText = '--';
    }
    ";
}
echo "; 
    var ydata = []
    var myChart1 = echarts.init(document.getElementById('poruku1')); 
    option1 = {
        color:['#73c0de','#fd6f6f','#fac858','#91cc75','#5470c6'],
        tooltip: {
            trigger: 'item',
            formatter: function(params) {
                var name = params.data.name;
                var title = params.data.title;
                var value = params.value;
                var marker = params.marker; // 添加marker(小圆点)
                return marker + ' ' + title + '<br/>' + name + ' : ' + value;
            }
        },
        series: [
            {
                type: 'pie',
                data:array1,
                roseType: 'area',
                itemStyle: {
                    normal: {
                        label: {
                            show: true,
                            textStyle: {
                                fontSize: 16
                            }
                        }
                    }
                }
            }
        ]
    };  
    myChart1.setOption(option1);    
    </script>";

//近一周新增采购单含税金额分析
$sql2 = "   SELECT DATE_FORMAT(date_table.date, '%Y-%m-%d') AS date, COALESCE(SUM(po_headers_all.po_all_amount), 0) AS total_amount
            FROM (
                SELECT DATE_SUB(CURDATE(), INTERVAL n DAY) AS date
                FROM (
                    SELECT a.N + b.N * 10 AS n
                    FROM (SELECT 0 AS N UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS a
                    CROSS JOIN (SELECT 0 AS N UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS b
                    ORDER BY n DESC
                ) AS numbers
                WHERE n <= 6 -- 指定查询的天数
            ) AS date_table
            LEFT JOIN po_headers_all ON DATE(FROM_UNIXTIME(po_headers_all.creation_date)) = date_table.date
            GROUP BY date_table.date
            ORDER BY date_table.date
        ";
$result2 = DB_query($sql2, $db);
while ($array2 = mysqli_fetch_assoc($result2)) {
    $arrays2[] = $array2;
}
if (is_array($arrays2)) {
    foreach ($arrays2 as $key => $value) {
        $date2[]  = $value['date'];
        $total_amount2[]  = $value['total_amount'];
    }
} else {
    $date2[] = 0;
    $total_amount2[] = 0;
}

echo "<script>";
// echo "console.log(" . json_encode($date2) . ");";
// echo "console.log(" . json_encode($total_amount2) . ");";
echo ";   var date2 = ";
echo json_encode($date2);
echo ";   var total_amount2 = ";
echo json_encode($total_amount2);
echo "; 
    var ydata = []
    var myChart2 = echarts.init(document.getElementById('poruku2')); 
    option2 = {
        //配置网格组件，用于定义图表的位置和大小
        grid: {
            top: '15%',  // 增加top的值来创建间距
            left: '4%',
            right: '6%',
            bottom: '2%',  // 增加bottom的值来创建间距
            containLabel: true, //自动计算并包含坐标轴标签、刻度和标题等内容在内。
        },
        color:['#fd6f6f'],
        tooltip: {
            trigger: 'axis',
            axisPointer: {            
                type: 'line'       
            },
            backgroundColor: 'rgba(255, 255, 255, 0.7)', // 设置提示条的背景颜色
            textStyle: {
                color: 'rgba(0, 0, 0)', // 设置提示条文本的颜色为白色
                fontSize: 14 // 设置提示条文本的字体大小为12px
            }
        },
        xAxis: {
            name:'日期',
            data: date2,
            axisLine: {
                lineStyle: {
                    color: '#6691b5'  // 设置 x 坐标轴线的颜色
                }
            },
            // axisLabel: {
            //     fontSize: 14  // 设置横轴标签字体大小为14
            // },
            nameTextStyle: {
                fontSize: 14  // 设置横轴名称字体大小为14
            }
        },
        yAxis: {
            name:'含税金额',
            splitLine: {
                show: false  // 隐藏纵坐标轴的背景横线
            },
            axisLine: {
                lineStyle: {
                    color: '#6691b5'  // 设置 x 坐标轴线的颜色
                }
            },
            axisLabel: {
                fontSize: 14  // 设置横轴标签字体大小为14
            },
            nameTextStyle: {
                fontSize: 14  // 设置横轴名称字体大小为14
            }
        },
        series: [
            {              
                type: 'scatter',
                symbolSize: 22,   // 固定的散点大小，可以根据需要调整
                data: total_amount2,               
            },          
        ]
    };
    let currentIndex2 = -1;
    setInterval(function() {
        var dataLen2 = option2.series[0].data.length;
        // 取消之前高亮的图形
        myChart2.dispatchAction({
            type: 'downplay',
            seriesIndex: 0,
            dataIndex: currentIndex2
        });
        currentIndex2 = (currentIndex2 + 1) % dataLen2;
        // 高亮当前图形
        myChart2.dispatchAction({
            type: 'highlight',
            seriesIndex: 0,
            dataIndex: currentIndex2
        });
        // 显示 tooltip
        myChart2.dispatchAction({
            type: 'showTip',
            seriesIndex: 0,
            dataIndex: currentIndex2
        });
    }, 1000);
    myChart2.setOption(option2);    
    </script>";

//计算近一月供应商入库数量榜单
$sql3 = " SELECT * from (SELECT a.vendor_code,d.vendor_name,round(SUM(prt.transaction_quantity*b.price),2) as quantity
            FROM po_headers_all a, po_lines_all b, sf_item_no c, vendors d, po_rcv_transactions prt,po_rcv_receipt_line prr
            WHERE  a.po_num = b.po_num
            AND a.vendor_code = d.vendor_code
            AND b.po_num = prt.po_num
            AND prr.receipt_num=prt.receipt_num
            AND prt.po_num=prr.po_num
            AND prt.po_line=prr.po_line 
            AND b.stockid=prr.stockid
            AND prt.transaction_type in ('POIN')
            AND b.line = prt.po_line
            AND b.stockid = c.item_no  
            AND prt.transaction_date >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 1 MONTH))
            GROUP BY vendor_code
            
            ) aa where 1=1 ORDER BY aa.quantity desc LIMIT 5
";


$result3 = DB_query($sql3, $db);
$index = 1;
while ($array3 = mysqli_fetch_assoc($result3)) {
    $name = $array3['vendor_name'];
    $value = $array3['quantity'];
    $title = 'TOP' . $index;
    if (!$value) {
        $value = 0;
    }
    $array_data3[] = array(
        'name' => $name,
        'value' => $value,
        'title' => $title
    );
    $index++;
}
echo "<script>";
// echo "console.log(" . json_encode($array_data3) . ");";
echo ";   var array3 = ";
echo json_encode($array_data3);
// 对数据项数量进行判断
$arrayLength = count($array_data3);
for ($i = 1; $i <= 5; $i++) {
    //输出数据到前端显示
    echo "
    if (array3[" . ($i - 1) . "]) {
        document.getElementById('vendor_top" . $i . "').innerText = array3[" . ($i - 1) . "].name ;
    } else {
        document.getElementById('vendor_top" . $i . "').innerText = '--';
    }
    ";
}
echo "; 
    var ydata = []
    var myChart3 = echarts.init(document.getElementById('poruku3')); 
    option3 = {
        color:['#73c0de','#fd6f6f','#fac858','#91cc75','#5470c6'],
        tooltip: {
            trigger: 'item',
            formatter: function(params) {
                var name = params.data.name;
                var title = params.data.title;
                var value = params.value;
                var marker = params.marker; // 添加marker(小圆点)
                return marker + ' ' + title + '<br/>' + name + ' : ' + value;
            }
        },
        series: [
            {
                type: 'pie',
                radius: '60%',
                // center: ['50%', '60%'],
                data: array3,
                emphasis: {
                    itemStyle: {
                    shadowBlur: 10,
                    shadowOffsetX: 0,
                    shadowColor: 'rgba(0, 0, 0, 0.5)',
                    label: {
                            show: true
                        },
                        labelLine: {
                            show: false
                        }
                    }
                },  
                itemStyle: {
                    normal: {
                        label: {
                            show: false
                        },
                        labelLine: {
                            show: false
                        }
                    },
                }             
            }
        ]
    };
    let currentIndex3 = -1;
    setInterval(function() {
        var dataLen3 = option3.series[0].data.length;
        // 取消之前高亮的图形
        myChart3.dispatchAction({
            type: 'downplay',
            seriesIndex: 0,
            dataIndex: currentIndex3
        });
        currentIndex3 = (currentIndex3 + 1) % dataLen3;
        // 高亮当前图形
        myChart3.dispatchAction({
            type: 'highlight',
            seriesIndex: 0,
            dataIndex: currentIndex3
        });
        // 显示 tooltip
        myChart3.dispatchAction({
            type: 'showTip',
            seriesIndex: 0,
            dataIndex: currentIndex3
        });
    }, 1000);
    myChart3.setOption(option3);    
    </script>";


// 近一月产品新增采购单含税金额
$sql4 = "   SELECT po.stockid,sf.item_name,SUM(line_amount) AS total_amount
            FROM po_lines_all po,sf_item_no sf
            WHERE po.stockid = sf.item_no
            AND po.creation_date >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 1 MONTH))
            GROUP BY stockid
            ORDER BY total_amount desc
            LIMIT 5
        ";
$result4 = DB_query($sql4, $db);
$index = 1;
while ($array4 = mysqli_fetch_assoc($result4)) {
    $name = $array4['stockid'];
    $value = $array4['total_amount'];
    $title = 'TOP' . $index;
    if (!$value) {
        $value = 0;
    }
    $array_data4[] = array(
        'name' => $name,
        'value' => $value,
        'title' => $title
    );
    $index++;
}
echo "<script>";
// echo "console.log(" . json_encode($array_data4) . ");";
echo ";   var array4 = ";
echo json_encode($array_data4);
// 对数据项数量进行判断
$arrayLength = count($array_data4);
for ($i = 1; $i <= 5; $i++) {
    //输出数据到前端显示
    echo "
    if (array4[" . ($i - 1) . "]) {
        document.getElementById('cus_delivery_top" . $i . "').innerText = array4[" . ($i - 1) . "].name ;
    } else {
        document.getElementById('cus_delivery_top" . $i . "').innerText = '--';
    }
    ";
}
echo "; 
    var ydata = []
    var myChart4 = echarts.init(document.getElementById('poruku4')); 
    option4 = {
        color:['#73c0de','#fd6f6f','#fac858','#91cc75','#5470c6'],
        tooltip: {
            trigger: 'item',
            formatter: function(params) {
                var name = params.data.name;
                var title = params.data.title;
                var value = params.value;
                var marker = params.marker; // 添加marker(小圆点)
                return marker + ' ' + title + '<br/>' + name + ' : ' + value;
            }
        },
        series: [
            {
                type: 'pie',
                radius: '60%',
                // center: ['50%', '60%'],
                data: array4,
                emphasis: {
                    itemStyle: {
                    shadowBlur: 10,
                    shadowOffsetX: 0,
                    shadowColor: 'rgba(0, 0, 0, 0.5)',
                    label: {
                            show: true
                        },
                        labelLine: {
                            show: false
                        }
                    }
                },  
                itemStyle: {
                    normal: {
                        label: {
                            show: false
                        },
                        labelLine: {
                            show: false
                        }
                    },
                }             
            }
        ]
    };
    let currentIndex4 = -1;
    setInterval(function() {
        var dataLen4 = option4.series[0].data.length;
        // 取消之前高亮的图形
        myChart4.dispatchAction({
            type: 'downplay',
            seriesIndex: 0,
            dataIndex: currentIndex4
        });
        currentIndex4 = (currentIndex4 + 1) % dataLen4;
        // 高亮当前图形
        myChart4.dispatchAction({
            type: 'highlight',
            seriesIndex: 0,
            dataIndex: currentIndex4
        });
        // 显示 tooltip
        myChart4.dispatchAction({
            type: 'showTip',
            seriesIndex: 0,
            dataIndex: currentIndex4
        });
    }, 1000);
    myChart4.setOption(option4);    
    </script>";

//近一月不同仓库入库数量
$sql6 = "   SELECT prr.subinventory_code,SUM(prt.transaction_quantity) as quantity
            FROM po_headers_all a, po_lines_all b, sf_item_no c, vendors d, po_rcv_transactions prt,po_rcv_receipt_line prr
            WHERE  a.po_num = b.po_num
            AND a.vendor_code = d.vendor_code
            AND b.po_num = prt.po_num
            AND prr.receipt_num=prt.receipt_num
            AND prt.po_num=prr.po_num
            AND prt.po_line=prr.po_line 
            AND b.stockid=prr.stockid
            AND prt.transaction_type in ('RECEIVE')
            AND b.line = prt.po_line
            AND b.stockid = c.item_no  
            AND prt.transaction_date >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 1 MONTH))
            GROUP BY subinventory_code
            ORDER BY prt.transaction_quantity desc
            LIMIT 5
";
$result6 = DB_query($sql6, $db);
while ($array6 = mysqli_fetch_assoc($result6)) {
    $arrays6[] = $array6;
}
if (is_array($arrays6)) {
    foreach ($arrays6 as $key => $value) {
        $subinventory_code6[]  = $value['subinventory_code'];
        $quantity6[]  = $value['quantity'];
    }
} else {
    $subinventory_code6[]  = 0;
    $quantity6[]  = 0;
}
echo "<script>";
echo ";   var subinventory_code6 = ";
echo json_encode($subinventory_code6);
echo ";   var quantity6 = ";
echo json_encode($quantity6);

echo "; 
    var data = []; 
    var myChart6 = echarts.init(document.getElementById('poruku6')); 
    option6 = {
        tooltip: {
            trigger: 'axis',
            axisPointer: {            
                type: 'line'       
            },
            backgroundColor: 'rgba(255, 255, 255, 0.7)', // 设置提示条的背景颜色
            textStyle: {
                color: 'rgba(0, 0, 0)', // 设置提示条文本的颜色为白色
                fontSize: 14 // 设置提示条文本的字体大小为12px
            }
        },
        color:['#3097b9'],
        //配置网格组件，用于定义图表的位置和大小
        grid: {
            top: '15%',  // 增加top的值来创建间距
            left: '1%',
            right: '12%',
            bottom: '1%',  // 增加bottom的值来创建间距
            containLabel: true, //自动计算并包含坐标轴标签、刻度和标题等内容在内。
        },
        xAxis: {
            name:'数量',
            data: subinventory_code6,
            axisLine: {
                lineStyle: {
                    color: '#6691b5'  // 设置 x 坐标轴线的颜色
                }
            },
            axisLabel: {
                fontSize: 14,  // 设置横轴标签字体大小为14
                // rotate: 45, // 将标签文本旋转45度
            },
            nameTextStyle: {
                fontSize: 14  // 设置横轴名称字体大小为14
            },
            
        },
        yAxis: {
            name:'仓库',
            splitLine: {
                show: false  // 隐藏纵坐标轴的背景横线
            }, 
            axisLine: {
                lineStyle: {
                    color: '#6691b5'  // 设置 x 坐标轴线的颜色
                }
            },
            axisLabel: {
                fontSize: 12  // 设置横轴标签字体大小为12
            },
            nameTextStyle: {
                fontSize: 12  // 设置横轴名称字体大小为12
            }
        },  
        series: [
            {
                type: 'bar',
                barWidth: '12',
                data: quantity6,
                itemStyle: {
                    normal: {
                        label: {
                            show: true,
                            position: 'top',
                            textStyle: {
                                color: '#6691b5',
                                fontSize: 12
                            }
                        }
                    }
                }
            }
        ]
    };
    let currentIndex6 = -1;
    setInterval(function() {
        var dataLen6 = option6.series[0].data.length;
        // 取消之前高亮的图形
        myChart6.dispatchAction({
            type: 'downplay',
            seriesIndex: 0,
            dataIndex: currentIndex6
        });
        currentIndex6 = (currentIndex6 + 1) % dataLen6;
        // 高亮当前图形
        myChart6.dispatchAction({
            type: 'highlight',
            seriesIndex: 0,
            dataIndex: currentIndex6
        });
        // 显示 tooltip
        myChart6.dispatchAction({
            type: 'showTip',
            seriesIndex: 0,
            dataIndex: currentIndex6
        });
    }, 1000);
    myChart6.setOption(option6);    
    </script>";


//近一月产品入库数量榜单
$sql7 = "   SELECT b.stockid,SUM(prt.transaction_quantity) as quantity
            FROM po_headers_all a, po_lines_all b, sf_item_no c, vendors d, po_rcv_transactions prt,po_rcv_receipt_line prr
            WHERE  a.po_num = b.po_num
            AND a.vendor_code = d.vendor_code
            AND b.po_num = prt.po_num
            AND prr.receipt_num=prt.receipt_num
            AND prt.po_num=prr.po_num
            AND prt.po_line=prr.po_line 
            AND b.stockid=prr.stockid
            AND prt.transaction_type in ('RECEIVE')
            AND b.line = prt.po_line
            AND b.stockid = c.item_no  
            AND prt.transaction_date >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 1 MONTH))
            GROUP BY stockid
            ORDER BY quantity desc
            LIMIT 5
";
$result7 = DB_query($sql7, $db);
$index = 1;
while ($array7 = mysqli_fetch_assoc($result7)) {
    $name = $array7['stockid'];
    $value = $array7['quantity'];
    $title = 'TOP' . $index;
    if (!$value) {
        $value = 0;
    }
    $array_data7[] = array(
        'name' => $name,
        'value' => $value,
        'title' => $title
    );
    $index++;
}
echo "<script>";
echo ";  var array7 = ";
echo json_encode($array_data7);
// echo "console.log(" . json_encode($array_data7) . ");";
// 对数据项数量进行判断
$arrayLength = count($array_data7);
for ($i = 1; $i <= 5; $i++) {
    //输出数据到前端显示
    echo "
    if (array7[" . ($i - 1) . "]) {
        document.getElementById('product_delivery_top" . $i . "').innerText = array7[" . ($i - 1) . "].name ;
    } else {
        document.getElementById('product_delivery_top" . $i . "').innerText = '--';
    }
    ";
}
echo "; 
    var data = []; 
    var myChart7 = echarts.init(document.getElementById('poruku7')); 
    option7 = {
        color:['#73c0de','#fd6f6f','#fac858','#91cc75','#5470c6'],
        tooltip: {
            trigger: 'item',
            formatter: function(params) {
                var name = params.data.name;
                var title = params.data.title;
                var value = params.value;
                var marker = params.marker; // 添加marker(小圆点)
                return marker + ' ' + title + '<br/>' + name + ' : ' + value;
            }
        },
        series: [
            {
                type: 'pie',
                radius: ['50%', '70%'],
                data: array7,
                emphasis: {
                    itemStyle: {
                    shadowBlur: 10,
                    shadowOffsetX: 0,
                    shadowColor: 'rgba(0, 0, 0, 0.5)',
                    label: {
                            show: true
                        },
                        labelLine: {
                            show: false
                        }
                    }
                },  
                itemStyle: {
                    normal: {
                        label: {
                            show: false
                        },
                        labelLine: {
                            show: false
                        }
                    },
                }       
            }
        ]
    };
    let currentIndex7 = -1;
    setInterval(function() {
        var dataLen7 = option7.series[0].data.length;
        // 取消之前高亮的图形
        myChart7.dispatchAction({
            type: 'downplay',
            seriesIndex: 0,
            dataIndex: currentIndex7
        });
        currentIndex7 = (currentIndex7 + 1) % dataLen7;
        // 高亮当前图形
        myChart7.dispatchAction({
            type: 'highlight',
            seriesIndex: 0,
            dataIndex: currentIndex7
        });
        // 显示 tooltip
        myChart7.dispatchAction({
            type: 'showTip',
            seriesIndex: 0,
            dataIndex: currentIndex7
        });
    }, 1000);
    myChart7.setOption(option7);    
    </script>";
