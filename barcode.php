<?php
//生成条形码
//https://www.barcodebakery.com参考网址
require_once('./barcodegen/class/BCGColor.php');
require_once('./barcodegen/class/BCGDrawing.php');
require_once('./barcodegen/class/BCGcode128.barcode.php');
if(isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"])=="xmlhttprequest"){
    // ajax 请求的处理方式
    $img=$_POST['number'];
    $filename='uplode';
    if(!file_exists($filename)){
        mkdir($filename);
    }
    $imgurl=$filename.'/'.$img.'.png';

    $colorFront = new \BCGColor(0, 0, 0);
    $colorBack = new \BCGColor(255, 255, 255);

    // Barcode Part
    $font = new \BCGFontFile('./barcodegen/font/Arial.ttf', 14);
    $code = new \BCGcode128();
    $code->setScale(2);
    $code->setFont($font);//文字大小
    $code->setColor($colorFront, $colorBack);//条形码颜色
    $code->parse($img);
	

    // Drawing Part
    $drawing = new \BCGDrawing('', $colorBack);
    $drawing->setBarcode($code);
    $rs=$drawing->setFilename($imgurl);//存入的地址
    $drawing->draw();
    $drawing->finish(\BCGDrawing::IMG_FORMAT_PNG);
    $out_arr['code']='000000';
    $out_arr['url']=$imgurl;
    echo json_encode($out_arr);die;
}else{
  // 正常请求的处理方式
    $colorFront = new \BCGColor(0, 0, 0);
    $colorBack = new \BCGColor(255, 255, 255);

    // Barcode Part
    $font = new \BCGFontFile('./barcodegen/font/Arial.ttf', 14);
    $code = new \BCGcode128();
    $code->setScale(2);
    $code->setFont($font);//文字大小
    $code->setColor($colorFront, $colorBack);//条形码颜色
    $code->parse('hello mother fuck');

    // Drawing Part
    $drawing = new \BCGDrawing('', $colorBack);
    $drawing->setBarcode($code);
    $drawing->draw();

    header('Content-Type: image/png');

    $drawing->finish(\BCGDrawing::IMG_FORMAT_PNG);
};

