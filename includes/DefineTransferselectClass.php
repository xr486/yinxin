<?php
//DefineTrunsferClassselect.php
Class ReceiveRequest {

	var $Transfer_code;
	var $effective_date;
        var $disable_date;
	var $customer_code;
	var $customer_name;
        var $compact_code; 
	var $amount;	
	var $description;	
	var $sales_man;
        var $LineItems;
	var $LineCounter=1;

	function ReceiveRequest(){
	/*Constructor function initialises a new shopping cart */
		$this->ReceiveDate = date($_SESSION['DefaultDateFormat']);
		$this->LineItems=array();
	}

	function AddLine( $Transfer_code,
                          $item_no,
			  $item_desc,
		          $uom,
                          $item_cost,
		          $quantity,
			  $disposal_type,
		          $amount,
                          $description ) {
		$this->LineItems[$this->LineCounter]=new LineDetails($Transfer_code,
		                                              $item_no,
			                                      $item_desc,
		                                              $uom,
                                                              $item_cost,
		                                              $quantity,
			                                      $disposal_type,
		                                              $amount,
                                                              $description,
                                                              $this->LineCounter);
		$this->LineCounter = $this->LineCounter + 1;
	}
}

Class LineDetails {
	var $Transfer_code;
	var $item_no;
	var $item_desc;
	var $uom;
	var $item_cost;
	var $quantity;
	var $disposal_type;  
        var $amount;
        var $description;
        var $LineNumber;   
	function LineDetails($Transfer_code,
	                      $item_no,
	                      $item_desc,
	                      $uom,
	                      $item_cost,
	                      $quantity,
	                      $disposal_type,
                              $amount,
                              $description,
                              $LineNumber) {

		$this->Transfer_code=$Transfer_code;
		$this->item_no=$item_no;
		$this->item_desc=$item_desc;
		$this->uom=$uom;
		$this->item_cost=$item_cost;
		$this->quantity=$quantity;
		$this->disposal_type=$disposal_type;
                $this->amount=$amount;
                $this->description=$description;
                $this->LineNumber=$LineNumber;
	}
}
?>