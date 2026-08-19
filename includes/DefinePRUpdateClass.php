<?php

//DefineTrunsferClassselect.php
Class ReceiveRequest {

    var $pr_num;
    var $status;
    var $need_date;
    var $remark;
	var $approve_remark;
    var $creation_date;
    var $created_by;
    var $approved_by;
    var $approved_date;
    var $need_stockid;
    var $need_customer_code;
    var $need_order_number;
    var $LineCounter = 1;

    function ReceiveRequest() {
        $this->LineItems = array();
    }

    function AddLine($pr_num, 
                      $item_no, 
                      $item_name,
		              $item_desc,
                      $uom, 
                      $quantity,
                      $price,
                      $line,
                      $line_amount,
                      $quantity_received,
                      $quantity_accepted,
                      $quantity_deliveried,
                      $quantity_cancelled,
                      $status) {
        $this->LineItems[$this->LineCounter] = new LineDetails($pr_num, 
                                                               $item_no, 
                                                               $item_name,
			                                                   $item_desc,
                                                               $uom, 
                                                               $quantity, 
                                                                $price,
                                                               $line, 
                                                               $line_amount,
                                                               $quantity_received,
                                                                $quantity_accepted,
                                                                  $quantity_deliveried,
                                                                  $quantity_cancelled,
                                                               $status, 
                                                               $this->LineCounter);
        $this->LineCounter = $this->LineCounter + 1;
    }

}

Class LineDetails {

    var $pr_num;
    var $item_no;
    var $item_name;
	var $item_desc;
    var $uom;
    var $quantity;
    var $price;
    var $line;
    var $line_amount;
    var $status;
    var $LineNumber;

    function LineDetails($pr_num,
                           $item_no,
                           $item_name,
		                   $item_desc,
                           $uom,
                           $quantity,
                           $price,
                           $line,
                           $line_amount,
                           $quantity_received,
                           $quantity_accepted,
                           $quantity_deliveried,
                           $quantity_cancelled,
                           $status,
                           $LineNumber) {

        $this->pr_num = $pr_num;
        $this->item_no = $item_no;
        $this->item_name = $item_name;
		$this->item_desc = $item_desc;
        $this->uom = $uom;
        $this->quantity = $quantity;
        $this->line = $line;
        $this->price = $price;
        $this->line_amount = $line_amount;
        $this->quantity_received = $quantity_received;
        $this->quantity_accepted = $quantity_accepted;
        $this->quantity_deliveried = $quantity_deliveried;
        $this->quantity_cancelled = $quantity_cancelled;
        $this->status = $status;
        $this->LineNumber = $LineNumber;
    }

}

?>