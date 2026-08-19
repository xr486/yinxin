<?php

//DefineTrunsferClassselect.php
Class ReceiveRequest {

    var $po_num;
    var $status;
    var $need_date;
    var $description;
    var $creation_date;
    var $created_by;
    var $approved_by;
    var $approved_date;
    var $amount;
    var $youhui_amount;
    var $LineCounter = 1;

    function ReceiveRequest() {
        $this->LineItems = array();
    }

    function AddLine($po_num, 
                      $item_no, 
                      $item_desc,
		              $item_spec,
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
        $this->LineItems[$this->LineCounter] = new LineDetails($po_num, 
                                                               $item_no, 
                                                               $item_desc,
			                                                   $item_spec,
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

    var $po_num;
    var $item_no;
    var $item_desc;
	var $item_spec;
    var $uom;
    var $quantity;
    var $price;
    var $line;
    var $line_amount;
    var $status;
    var $LineNumber;

    function LineDetails($po_num,
                           $item_no,
                           $item_desc,
		                   $item_spec,
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

        $this->po_num = $po_num;
        $this->item_no = $item_no;
        $this->item_desc = $item_desc;
		$this->item_spec = $item_spec;
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