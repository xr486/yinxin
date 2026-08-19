<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Cs extends CI_Controller {

	public function __construct()
	{
	    parent::__construct();
        $this->load->library('excel/excel');
	}
 
	public function index()
	{ 
	    $xls = './uploads/a.xls';  
	    $this->excel->setOutputEncoding('utf-8');  
		$this->excel->read($xls); 
		//print_r($this->excel->sheets);   打印数据集
		//print_r($this->excel->sheets[0]['cells']);   打印导入的数据集
		//$numrows = $this->excel->sheets[0]['numRows'];  //获取多少行数据
		$arr = $this->excel->sheets[0]['cells'];
		//print_r($this->excel->sheets[0]['cells']);
		//print_r($arr);
		$i=0;
		
		foreach($arr as $arry=>$row)
		{
		    $data[$i]['id'] = $row['1'];  
			$data[$i]['title'] = $row['2'];  
			$data[$i]['nr'] = $row['3'];  
			$i++;
		}
		$this->db->insert_batch('表', $data);   //批量入库
	}


}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */