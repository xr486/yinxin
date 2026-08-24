<?php
    //判断前端的参数
    if ($_GET['action'] == 'public_upload') {
        public_upload();
    } 
    //调用方法
    //上传公共文件
    function public_upload(){
        $file = $_FILES['file']; //获取小程序传来的图片
        $imgdirs = "../SO/";//文件夹名称(/SO)
        mkdirs($imgdirs);//创建$imgdirs文件夹
        //获取图片文件的名字
        $fileName = $_FILES["file"]["name"];
        // //获取图片类型
        $file_type = $_FILES["file"]["type"];
        $type = '';
        //判断是否是图片
        switch ($file_type) {
        case 'image/png':
            $type = '.png';
            break;
        case 'image/gif':
            $type = '.gif';
            break;
        case 'image/jpeg':
            $type = '.jpg';
            break;
        }
        //图片保存的路径
        $savepath = $imgdirs.$fileName; //文件路径
        // 临时文件移动到指定文件夹
        $rs = move_uploaded_file($_FILES["file"]["tmp_name"],$savepath);
        //成功上传文件
        // if($rs) {
        //     $url = 'SO/'.$fileName;
        //     echo json_encode($url, JSON_UNESCAPED_SLASHES);
        //     } 
        // else {
        //     $result=array('errno'=>1,'message'=>'失败信息');
        //     echo json_encode($result);
        // }
    }
    //创建文件夹 权限问题
    function mkdirs($dir, $mode = 0777){
        if (is_dir($dir) || @mkdir($dir, $mode)) return TRUE;
        if (!mkdirs(dirname($dir), $mode)) return FALSE;
        return @mkdir($dir, $mode);
    }
?>
