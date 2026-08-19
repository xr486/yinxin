<?php
function filedownload($file_dir,$file_name)
{
    $file_dir = chop($file_dir);
    if($file_dir != '')
    {
        $file_path = $file_dir;
        if(substr($file_dir,strlen($file_dir)-1,strlen($file_dir)) != '/')
            $file_path .= '/';
        $file_path .= $file_name;
    }           
    else
        $file_path = $file_name;   
   
    if(!file_exists($file_path))
    {
        return false;
    }
    $file_size = filesize($file_path);
    $fp = fopen($file_path,"r");
    
    header("Content-type: application/octet-stream");
    header("Accept-Ranges: bytes");
    header("Accept-Length: $file_size");
    header("Content-Disposition: attachment; filename=".$file_name);
   
    $buffer_size = 1024;
    $cur_pos = 0;
    ob_clean();
    flush();
    while(!feof($fp) && $cur_pos<$file_size)
    {
        $buffer = fread($fp,$buffer_size);
        $cur_pos += $buffer_size;
        echo $buffer;
    }
  
    fclose($fp);
    return true;
}


?>