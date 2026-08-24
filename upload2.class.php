<?php
/**
 * Created by PhpStorm.
 * User: DreamBoy
 * Date: 2016/4/9
 * Time: 9:24
 */
error_reporting(0);
class Upload2 {
    protected $fileName; //POST请求时文件的name值
    protected $maxSize; //文件上传的最大大小
    protected $allowMime; //允许上传的文件类型
    protected $allowExt; //允许上传的文件类型
    protected $uploadPath; //文件上传的路径
    protected $imgFlag; //标志是否要求上传的文件为真实图片
 
    protected $fileInfos; //所有文件信息
    protected $uploadRes; //上传文件的结果
 
    protected $error; //记录系统错误号
    protected $err = array( //错误号及错误类型
        '000' => '文件上传成功',
        '001' => '超过了PHP配置文件中upload_max_filesize选项值',
        '002' => '超过了表单中MAX_FILE_SIZE设置的值',
        '003' => '文件部分被上传',
        '004' => '没有选择上传文件',
        '005' => '没有找到临时目录',
        '006' => '文件不可写',
        '007' => '由于PHP的扩展程序中断文件上传',
        '008' => '上传文件过大',
        '009' => '不允许的文件类型',
        '010' => '不允许的文件MIME类型',
        '011' => '文件不是真实图片',
        '012' => '文件不是通过HTTP POST方式上传上来的',
        '013' => '文件移动失败',
        '014' => '系统错误：文件上传出错',
        );
 
    /**
     * Upload2 constructor.
     * @param string $fileName
     * @param string $uploadPath
     * @param bool $imgFlag
     * @param int $maxSize
     * @param array $allowExt
     * @param array $allowMime
     */
    public function __construct($fileName='myFile',$uploadPath='./uploads',$imgFlag=true,$maxSize=15242880,
                                $allowExt=array('jpeg','jpg','png','gif'),
                                $allowMime=array('image/jpeg','image/png','image/gif')) {
 
        $this->fileName = $fileName;
        $this->maxSize = $maxSize;
        $this->allowMime = $allowMime;
        $this->allowExt = $allowExt;
        $this->uploadPath = $uploadPath;
        $this->imgFlag = $imgFlag;
        $this->fileInfos = $this->getFileInfos();
    }
 
    /**
     * 获取上传的文件信息，并判断上传的文件是单文件还是多文件，设置上传文件的模式
     * @return mixed
     */
    protected function getFileInfos() {
        if(isset($_FILES[$this->fileName])) {
            $file = $_FILES[$this->fileName];
        } else {
            $this->error = '014';
            $this->showError();
        }
 
        $i = 0;
        //单文件或者多个单文件上传
        if(is_string($file['name'])) {
            $files[$i] = $file;
        } //多文件上传
        elseif(is_array($file['name'])) {
            foreach($file['name'] as $key=>$val) {
                $files[$i]['name'] = $file['name'][$key];
                $files[$i]['type'] = $file['type'][$key];
                $files[$i]['tmp_name'] = $file['tmp_name'][$key];
                $files[$i]['error'] = $file['error'][$key];
                $files[$i]['size'] = $file['size'][$key];
                $i++;
            }
        }
        return $files;
    }
 
    /**
     * 显示错误
     */
    protected function showError() {
        $e = $this->err[$this->error];
        exit('<span style="color:red">' . $e . '</span>');
    }
 
    /**
     * 为序号为$cur的文件设置上传结果信息
     * @param $cur
     * @param string $errno
     */
    protected function setError($cur, $errno='000') {
        // $this->uploadRes[$cur]['errno'] = $errno;
        $this->uploadRes[$cur]['error'] = $this->err[$errno];
        $this->uploadRes[$cur]['dest'] = '';
    }
 
    /**
     * 检测上传文件是否出错
     * @param int $cur
     * @return bool
     */
    protected function checkError($cur=0) {
        if(!is_null($this->fileInfos[$cur])) { //文件获取失败
       
 
        if($this->fileInfos[$cur]['error']>0) {
            switch($this->fileInfos[$cur]['error']) {
                case 1:
                    $curErr = '001';
                    break;
                case 2:
                    $curErr = '002';
                    break;
                case 3:
                    $curErr = '003';
                    break;
                case 4:
                    $curErr = '004';
                    break;
                case 6:
                    $curErr = '005';
                    break;
                case 7:
                    $curErr = '006';
                    break;
                case 8:
                    $curErr = '007';
                    break;
            }
 
            $this->setError($cur, $curErr);
            return false;
        }else{

            return true;
        }
    }else{
        $this->$curErr='文件上传出错';
        return false;
    }
    }
 
    /**
     * 检测上传文件的大小
     * @param int $cur
     * @return bool
     */
    protected function checkSize($cur=0) {
        if($this->fileInfos[$cur]['size'] > $this->maxSize) {
            $this->setError($cur, '008');
            return false;
        }
        return true;
    }
 
    /**
     * 获取序号为$cur文件的扩展名
     * @param int $cur
     * @return string
     */
    protected function getCurExt($cur=0) {
        return strtolower(pathinfo($this->fileInfos[$cur]['name'], PATHINFO_EXTENSION));
    }
 
    /**
     * 检测文件扩展名
     * @param int $cur
     * @return bool
     */
    protected function checkExt($cur=0) {
        $ext = $this->getCurExt($cur);
        if(!in_array($ext, $this->allowExt)) {
            $this->setError($cur, '009');
            return false;
        }
        return true;
    }
 
    /**
     * 检测文件的MIME类型
     * @param int $cur
     * @return bool
     */
    protected function checkMime($cur=0) {
        if(!in_array($this->fileInfos[$cur]['type'],$this->allowMime)) {
            $this->setError($cur, '010');
            return false;
        }
        return true;
    }
 
    /**
     * 检测文件是否为真实图片
     * @param int $cur
     * @return bool
     */
    protected function checkTrueImg($cur=0) {
        if($this->imgFlag) {
            if(!@getimagesize($this->fileInfos[$cur]['tmp_name'])) {
                $this->setError($cur, '011');
                return false;
            }
        }
        return true;
    }
 
    /**
     * 检测是否通过HTTP Post方式上传过来的
     * @param int $cur
     * @return bool
     */
    protected function checkHTTPPost($cur=0) {
        if(!is_uploaded_file($this->fileInfos[$cur]['tmp_name'])) {
            $this->error = '012';
            return false;
        }
        return true;
    }
 
    /**
     * 检测目录是否存在，如果不存在则进行创建
     */
    protected function checkUploadPath() {
        if(!file_exists($this->uploadPath)) {
            mkdir($this->uploadPath, 0777, true);
        }
    }
 
    /**
     * 产生唯一字符串
     * @return string
     */
    protected function getUniName() {
        return md5(uniqid(microtime(true),true));
    }
 
    /**
     * 上传文件
     * @return string
     */
    public function uploadFile() {
        foreach ($this->fileInfos as $key => $value) {
            if($this->checkError($key) && $this->checkSize($key)
                && $this->checkExt($key) && $this->checkMime($key)
                && $this->checkTrueImg($key) && $this->checkHTTPPost($key)) {
 
                $this->checkUploadPath();
 
                $uniName = $this->getUniName();
                $ext = $this->getCurExt($key);
 
                $destination = $this->uploadPath . '/' . $uniName . '.' . $ext;
                if(@move_uploaded_file($this->fileInfos[$key]['tmp_name'], $destination)) {
                    $this->setError($key);
                    $this->uploadRes[$key]['dest'] = $destination;
                } else {
                    $this->setError($key, '013');
                }
            }
        }
 
        return $this->uploadRes;
    }
}