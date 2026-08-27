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
        <h1 class="all_title">销售看板</h1>
    </div>
    <div class="kanban_all">
        <div class="kanban_all_position">
            <div class="kanban_item">
                <div class="kanban_line">
                    <div class="kanban_title">
                        近一月新增订单金额前五榜单
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
                        近一个月业务员新增订单金额前五榜单
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
                                    <li style="color:#73c0de">Top1:<span id="yewu_top1"></span></li>
                                    <li style="color:#fd6f6f">Top2:<span id="yewu_top2"></span></li>
                                    <li style="color:#fac858">Top3:<span id="yewu_top3"></span></li>
                                    <li style="color:#91cc75">Top4:<span id="yewu_top4"></span></li>
                                    <li style="color:#5470c6">Top5:<span id="yewu_top5"></span></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="kanban_item">
                <div class="kanban_line">
                    <div class="kanban_title">
                        近一周新增订单分析
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
                    <div class="left">
                        <div class="kanban_line">
                            <div class="kanban_title">
                                近一月产品新增订单金额前五榜单
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
                                            <li style="color:#73c0de">Top1:<span id="stockid_top1"></span></li>
                                            <li style="color:#fd6f6f">Top2:<span id="stockid_top2"></span></li>
                                            <li style="color:#fac858">Top3:<span id="stockid_top3"></span></li>
                                            <li style="color:#91cc75">Top4:<span id="stockid_top4"></span></li>
                                            <li style="color:#5470c6">Top5:<span id="stockid_top5"></span></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="middle"></div>
                    <div class="right">
                        <div class="kanban_line">
                            <div class="kanban_title">
                                近一月客户出货金额前五榜单
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
                </div>
            </div>
            <div class="kanban_item">
                <div class="kanban_line">
                    <div class="kanban_title">
                        逾期未出货排名
                    </div>
                    <div class="kanban_content scroll" style="overflow-y: scroll; max-height: 200px;">
                        <?php
                        $time = time();
                        $sql5 = "SELECT
                                    s.order_number,s.line,s.stockid,s.uom,s.quantity,s.price,s.zhidao_price,s.quantity_shiped,s.line_amount,s.wip_entity_name ,
                                    h.customer_code,h.creation_date,h.status,h.customer_order_number,h.tax_rate,s.line_remark,h.order_number, s.line_amount, 
                                    a.item_name,s.version,s.liaohao,e.employee_name,s.customer_item,h.qianding_date,s.need_date,(quantity-s.quantity_shiped ) wait_quantity
                                FROM sf_item_no a,
                                so_lines_all s,
                                so_headers_all h,
                                customers c, hr_employees e
                                WHERE  s.order_number = h.order_number and s.stockid=a.item_no and quantity>s.quantity_shiped
                                AND c.customer_code = h.customer_code  and e.employee_num=h.yewu and h.status='已签核' 
                                and s.need_date<= '" . $time . "' ";
                        $sql5 .= " order by h.creation_date desc";
                        $result5 = DB_query($sql5, $db);
                        // 检查查询是否成功
                        if ($result5) {
                            // 检查查询结果是否为空
                            if (mysqli_num_rows($result5) > 0) {
                        ?>
                                <table class="table1">
                                    <tr>
                                        <th class="th1">订单号</th>
                                        <th class="th2">客户</th>
                                        <th class="th3">数量</th>
                                        <th class="th3">已出量</th>
                                        <th class="th3">剩余量</th>
                                        <th class="th2">需求日期</th>
                                    </tr>
                                    <?php
                                    while ($row = mysqli_fetch_assoc($result5)) {
                                    ?>
                                        <tr>
                                            <td class="td1"><?php echo $row['order_number']; ?></td>
                                            <td class="td2"><?php echo $row['customer_code']; ?></td>
                                            <td class="td3"><?php echo $row['quantity']; ?></td>
                                            <td class="td3"><?php echo $row['quantity_shiped']; ?></td>
                                            <td class="td3"><?php echo $row['wait_quantity']; ?></td>
                                            <td class="td2"><?php echo date('Y-m-d', $row['need_date']); ?></td>
                                        </tr>
                                    <?php
                                    }
                                    ?>
                                </table>
                                <script>
                                    //悬停
                                    // JavaScript函数：停止滚动
                                    // function stopScroll() {
                                    //     var table = document.querySelector('.scroll table');
                                    //     table.style.animationPlayState = 'paused';
                                    // }
                                    // JavaScript函数：开始滚动
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
                                近一月业务员出货金额榜单
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
                                近一月产品出货金额榜单
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
// 近一月客户新增订单金额前五榜单
$sql = "SELECT customer_code, 
            SUM(order_all_amount) AS total_amount
        FROM so_headers_all
        WHERE creation_date >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 1 MONTH))
        GROUP BY customer_code
        ORDER BY total_amount 
        LIMIT 5
";
$result = DB_query($sql, $db);

while ($array = mysqli_fetch_assoc($result)) {
    $arrays[] = $array;
}

if (is_array($arrays)) {
    foreach ($arrays as $key => $value) {
        $customer[]  = $value['customer_code'];
        $amount[]  = $value['total_amount'];
    }
} else {
    $customer[]  = 0;
    $amount[]  = 0;
}
sort($amount);
echo "<script>";
echo ";   var customer = ";
echo json_encode($customer);
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
            name: '订单金额',
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
                name: '客户',
                type: 'category', //横坐标数据类型为类别型，适用于离散的数据
                data: customer,
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
        var dataLen = (option.series && option.series[0] && option.series[0].data) ? option.series[0].data.length : 0;
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

//近一月业务员新增榜单
$sql1 = "SELECT 	sl.yewu,em.employee_name,
        SUM(sl.order_all_amount) AS total_amount
        FROM so_headers_all sl,hr_employees em
        WHERE sl.creation_date >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 1 MONTH))
        AND sl.yewu = em.employee_num
        
        ORDER BY total_amount desc
        LIMIT 5
";
$result1 = DB_query($sql1, $db);
$index = 1;
while ($array1 = mysqli_fetch_assoc($result1)) {
    $name = $array1['employee_name'];
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
echo json_encode(isset($array_data1) ? $array_data1 : array());
// 对数据项数量进行判断
$arrayLength = count($array_data1);
for ($i = 1; $i <= 5; $i++) {
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

//计算近一周每天新增订单金额
$sql2 = "SELECT DATE_FORMAT(date_table.date, '%Y-%m-%d') AS date, COALESCE(SUM(so_headers_all.order_all_amount), 0) AS total_amount
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
        LEFT JOIN so_headers_all ON DATE(FROM_UNIXTIME(so_headers_all.creation_date)) = date_table.date
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
            name:'订单金额',
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
        var dataLen2 = (option2.series && option2.series[0] && option2.series[0].data) ? option2.series[0].data.length : 0;
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

//计算总的良品和不良
$sql3 = "   SELECT sl.stockid,sf.item_name,SUM(line_amount) AS total_amount
            FROM so_lines_all sl,sf_item_no sf
            WHERE sl.stockid = sf.item_no
            AND sl.creation_date >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 1 MONTH))
            GROUP BY stockid
            ORDER BY total_amount desc
            LIMIT 5
        ";
$result3 = DB_query($sql3, $db);
$index = 1;
while ($array3 = mysqli_fetch_assoc($result3)) {
    $name = $array3['stockid'];
    $value = $array3['total_amount'];
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
echo json_encode(isset($array_data3) ? $array_data3 : array());
// 对数据项数量进行判断
$arrayLength = count($array_data3);
for ($i = 1; $i <= 5; $i++) {
    //输出数据到前端显示
    echo "
    if (array3[" . ($i - 1) . "]) {
        document.getElementById('stockid_top" . $i . "').innerText = array3[" . ($i - 1) . "].name ;
    } else {
        document.getElementById('stockid_top" . $i . "').innerText = '--';
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
        var dataLen3 = (option3.series && option3.series[0] && option3.series[0].data) ? option3.series[0].data.length : 0;
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


// 近一月客户出货金额榜单
$sql4 = "   SELECT de.customer_code,cus.customer_name,SUM(delivery_amount) as delivery_amount
            FROM so_delivery_headers_all de,customers cus 
            WHERE de.customer_code = cus.customer_code
            AND de.delivery_date >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 1 MONTH))
            AND status = '完成'
            AND delivery_type = '出货'
            GROUP BY customer_code
            ORDER BY delivery_amount desc
            LIMIT 5
";
$result4 = DB_query($sql4, $db);
$index = 1;
while ($array4 = mysqli_fetch_assoc($result4)) {
    $name = $array4['customer_name'];
    $value = $array4['delivery_amount'];
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
echo json_encode(isset($array_data4) ? $array_data4 : array());
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
        var dataLen4 = (option4.series && option4.series[0] && option4.series[0].data) ? option4.series[0].data.length : 0;
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

//逾期未出货
// $sql5 = "SELECT
// s.order_number,s.line,s.stockid,s.uom,s.quantity,s.price,s.zhidao_price,s.quantity_shiped,s.line_amount,s.wip_entity_name ,
// h.customer_code,h.creation_date,h.status,h.customer_order_number,h.tax_rate,s.line_remark,h.order_number, s.line_amount, 
// a.item_name,s.version,s.liaohao,e.employee_name,s.customer_item,h.qianding_date,s.need_date,(quantity-s.quantity_shiped ) wait_quantity
// FROM sf_item_no a,
// so_lines_all s,
// so_headers_all h,
// customers c, hr_employees e
// WHERE  s.order_number = h.order_number and s.stockid=a.item_no and quantity>s.quantity_shiped
// AND c.customer_code = h.customer_code  and e.employee_num=h.yewu and h.status='已签核' 
// and s.need_date<= '" . $time . "' ";
// $sql5 .= " order by h.creation_date desc   ";
// $result5 = DB_query($sql5, $db);
// while ($array5 = mysqli_fetch_assoc($result5)) {
//     $arrays5[] = $array5;
// }
// echo "<script>";
// echo ";   var arrays5 = ";
// echo json_encode($arrays5);
// echo "console.log(" . json_encode($arrays5) . ");";
// echo "; 
//  </script>";




//近一月业务员出货金额榜单
$sql6 = "   SELECT de.created_by as employee_num,em.employee_name as employee_name,SUM(delivery_amount) as delivery_amount
            FROM so_delivery_headers_all de,hr_employees em 
            WHERE de.created_by = em.employee_num
            AND de.delivery_date >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 1 MONTH))
            AND status = '完成'
            AND delivery_type = '出货'
            GROUP BY employee_num
            ORDER BY delivery_amount desc
            limit 5
";
$result6 = DB_query($sql6, $db);
while ($array6 = mysqli_fetch_assoc($result6)) {
    $arrays6[] = $array6;
}
if (is_array($arrays6)) {
    foreach ($arrays6 as $key => $value) {
        $employee_name6[]  = $value['employee_name'];
        $delivery_amount6[]  = $value['delivery_amount'];
    }
} else {
    $employee_name6[]  = 0;
    $delivery_amount6[]  = 0;
}
echo "<script>";
echo ";   var employee_name6 = ";
echo json_encode($employee_name6);
echo ";   var delivery_amount6 = ";
echo json_encode($delivery_amount6);

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
            right: '1%',
            bottom: '1%',  // 增加bottom的值来创建间距
            containLabel: true, //自动计算并包含坐标轴标签、刻度和标题等内容在内。
        },
        xAxis: {
            data: employee_name6,
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
                barWidth: '10',
                data: delivery_amount6,
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
        var dataLen6 = (option6.series && option6.series[0] && option6.series[0].data) ? option6.series[0].data.length : 0;
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


//近一月产品出货金额榜单
$sql7 = "   SELECT line.stockid as item_no,sf.item_name as item_name,SUM(line_amount) as delivery_amount
            FROM so_delivery_all line,sf_item_no sf,so_delivery_headers_all de
            WHERE line.stockid = sf.item_no
            AND de.delivery_num = line.delivery_num
            AND de.delivery_date >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 1 MONTH))
            AND de.status = '完成'
            AND de.delivery_type = '出货'
            GROUP BY stockid
            ORDER BY delivery_amount desc
            limit 5
";
$result7 = DB_query($sql7, $db);
$index = 1;
while ($array7 = mysqli_fetch_assoc($result7)) {
    $name = $array7['item_no'];
    $value = $array7['delivery_amount'];
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
echo json_encode(isset($array_data7) ? $array_data7 : array());
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
        var dataLen7 = (option7.series && option7.series[0] && option7.series[0].data) ? option7.series[0].data.length : 0;
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
