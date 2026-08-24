<?php

//DefineTrunsferClassselect.php
Class ReceiveRequest {

    var $order_number;
    var $status;
    var $qianding_date;
    var $need_date;
    var $remark;
    var $creation_date;
    var $created_by;
    var $customer_code;
    var $customer_name;
    var $order_amount;  
    var $LineCounter = 1;

    function ReceiveRequest() {
        $this->LineItems = array();
    }

    function AddLine($order_number, 
                      $item_no, 
                      $item_desc,  
                      $uom, 
                      $quantity,
                      $price,
                      $line,
                      $amount,
                      $quantity_deliveried, 
                      $status,$item_spec,
		              $subinventory_code) {
        $this->LineItems[$this->LineCounter] = new LineDetails($order_number, 
                                                               $item_no, 
                                                               $item_desc, 
                                                               $uom, 
                                                               $quantity, 
                                                               $price,
                                                               $line, 
                                                               $amount,
                                                               $quantity_deliveried, 
                                                               $status, $item_spec,
			                                                   $subinventory_code,
                                                               $this->LineCounter);
        $this->LineCounter = $this->LineCounter + 1;
    }

}

Class LineDetails {

    var $order_number;
    var $item_no;
    var $item_desc; 
    var $uom;
    var $quantity;
    var $price;
    var $line;
    var $amount;
	var $quantity_deliveried;
    var $status;
	var $item_spec;
	var $subinventory_code;
    var $LineNumber;

    function LineDetails($order_number,
                           $item_no, 
                           $item_desc, 
                           $uom, 
                           $quantity,
                           $price,
                           $line, 
                           $amount,
                           $quantity_deliveried,
                      $status,
                      $item_spec, 
                           $subinventory_code, 
                           $LineNumber) {

        $this->order_number = $order_number;
        $this->item_no = $item_no;
        $this->item_desc = $item_desc; 
        $this->uom = $uom;
        $this->quantity = $quantity;
        $this->line = $line;
        $this->price = $price;
        $this->amount = $amount;
        $this->quantity_deliveried = $quantity_deliveried;
        $this->status = $status;
        $this->item_spec = $item_spec;
        $this->subinventory_code = $subinventory_code; 
        $this->LineNumber = $LineNumber;
    }

}

?>