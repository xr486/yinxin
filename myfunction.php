<?php
// function number2chinese($num,$mode = true,$sim = true){ 
//        if(!is_numeric($num)) return '含有非数字非小数点字符！'; 
//        $char    = $sim ? array('零','壹','贰','叁','肆','伍','陆','柒','捌','玖')
//        : array('零','一','二','三','四','五','六','七','八','九') ; 
//        $unit    = $sim ? array('','十','百','千','','万','亿','兆')     
//        : array('','拾','佰','仟','','萬','億','兆');    
//         $retval  = $mode ? '元':'点'; 
      
//       if(strpos($num, '.')){         list($num,$dec) = explode('.', $num);         $dec = strval(round($dec,2));         if($mode){             $retval .= "{$char[$dec['0']]}角{$char[$dec['1']]}分";         }else{             for($i = 0,$c = strlen($dec);$i < $c;$i++) {                 $retval .= $char[$dec[$i]];             }         }     }  
//          $str = $mode ? strrev(intval($num)) : strrev($num);    
//           for($i = 0,$c = strlen($str);$i < $c;$i++) {         $out[$i] = $char[$str[$i]];       
//             if($mode){             $out[$i] .= $str[$i] != '0'? $unit[$i%4] : '';               
//               if($i>1 and $str[$i]+$str[$i-1] == 0){                 $out[$i] = '';             }             
//                   if($i%4 == 0){                 $out[$i] .= $unit[4+floor($i/4)];             }         }     }  
//                  $retval = join('',array_reverse($out)) . $retval;     return $retval;  } 

/**
*数字金额转换成中文大写金额的函数
*String Int $num 要转换的小写数字或小写字符串
*return 大写字母
*小数位为两位
**/
function number2chinese($num){
    $c1 = "零壹贰叁肆伍陆柒捌玖";
    $c2 = "分角元拾佰仟万拾佰仟亿";
    //精确到分后面就不要了，所以只留两个小数位
    $num = round($num, 2); 
    //将数字转化为整数
    $num = $num * 100;
    if (strlen($num) > 10) {
        return "金额太大，请检查";
    } 
    $i = 0;
    $c = "";
    while (1) {
        if ($i == 0) {
            //获取最后一位数字
            $n = substr($num, strlen($num)-1, 1);
        } else {
            $n = $num % 10;
        }
        //每次将最后一位数字转化为中文
        $p1 = substr($c1, 3 * $n, 3);
        $p2 = substr($c2, 3 * $i, 3);
        if ($n != '0' || ($n == '0' && ($p2 == '亿' || $p2 == '万' || $p2 == '元'))) {
            $c = $p1 . $p2 . $c;
        } else {
            $c = $p1 . $c;
        }
        $i = $i + 1;
        //去掉数字最后一位了
        $num = $num / 10;
        $num = (int)$num;
        //结束循环
        if ($num == 0) {
            break;
        } 
    }
    $j = 0;
    $slen = strlen($c);
    while ($j < $slen) {
        //utf8一个汉字相当3个字符
        $m = substr($c, $j, 6);
        //处理数字中很多0的情况,每次循环去掉一个汉字“零”
        if ($m == '零元' || $m == '零万' || $m == '零亿' || $m == '零零') {
            $left = substr($c, 0, $j);
            $right = substr($c, $j + 3);
            $c = $left . $right;
            $j = $j-3;
            $slen = $slen-3;
        } 
        $j = $j + 3;
    } 
    //这个是为了去掉类似23.0中最后一个“零”字
    if (substr($c, strlen($c)-3, 3) == '零') {
        $c = substr($c, 0, strlen($c)-3);
    }
    //将处理的汉字加上“整”
    if (empty($c)) {
        return "零元整";
    }else{
        return $c . "整";
    }
}
       
       
?>
       