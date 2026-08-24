<?php

/**
 * Description of DefinePRToPOClass
 *
 * @author Xin
 */
class VendorHeader {
    //put your code here
    public $PO;
    public $vendor_code;
    public $vendor_name;
    public $need_date;
    public $description;
    public $amount;
    public $status;
    //下面的属性为line
    public $LineItems;
    public $line_counter;
            
    function VendorHeader(){
        $this->LineItems = array();
        $this->vendor_code = '';
        $this->status ;
        $this->line_counter = 1;
    }
    function AddLine($PR,
                      $Line,
                      $item_no,
                      $item_name,
                      $item_desc,
                      $Quantity,
                      $PR_quantity,
                      $Price,
                      $Units,
                      $Amount){

		if (isset($item_no) AND $Quantity!=0){
			$this->LineItems[$this->line_counter] = new VendorLine($this->line_counter,
                                                                                $PR,
                                                                                $Line,
                                                                                $item_no,
                                                                                $item_name,
                                                                                $item_desc,
                                                                                $Quantity,
                                                                                $PR_quantity,
                                                                                $Price,
                                                                                $Units,
                                                                                $Amount);
			$this->line_counter++;
			Return 1;
		}
		Return 0;
	}
}

Class VendorLine {
    public $LineNumber;
    public $item_no;
    public $item_name;
    public $Quantity;
    public $PR_quantity;
    public $Price;
    public $Units;
    public $PR;
    public $Line;
    public $Amount;
            function VendorLine ($line_code,
                            $PR,
                            $Line,
                            $item_no,
                            $item_name,
				            $item_desc,
                            $Quantity,
                            $PR_quantity,
                            $Price,
                            $Units,
                            $Amount){
            $this->LineNumber = $line_code;
            $this->item_no = $item_no;
            $this->item_name = $item_name;
            $this->item_desc = $item_desc;
            $this->Quantity = $Quantity;
            $this->PR_quantity =$PR_quantity;
            $this->Price= $Price;
            $this->Units = $Units;
            $this->PR=$PR;
            $this->Line=$Line;
            $this->Amount=$Amount; 

    }
}