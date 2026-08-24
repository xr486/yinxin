<?php

Class ArReceiveRequest {

    var $Customer_code;	
    var $Customer_name;
	var $ReceiveNum;
	var $ReceiveAmount;
	var $TaxAmount;	
	var $ReceiveDate;
	var $Narrative;
	var $LineCounter=1;
	var $LineItems;

	function ArReceiveRequest(){
	/*Constructor function initialises a new shopping cart */
		$this->ReceiveDate = date($_SESSION['DefaultDateFormat']);
		$this->LineItems=array();
	}
 	
	function AddLine($Invoicenum,
					 $Invoiceamount,
		             $Invoicedate,
		             $Taxamount,
                     $AlreadyReceiveamount,
		             $Waitamount,
		             $Receiveamount, 
		             $LineNarrative,
					 $LineNumber=-1) {

		if ($LineNumber==-1){
			$LineNumber = $this->LineCounter;
		}
		$this->LineItems[$LineNumber]=new LineDetails($Invoicenum,
					 $Invoiceamount,
		             $Invoicedate,
		             $Taxamount,
                     $AlreadyReceiveamount,
		             $Waitamount,
			         $Receiveamount, 
		             $LineNarrative,
					 $LineNumber);
		$this->LineCounter = $LineNumber + 1;
	}
}

Class LineDetails { 
	var $Invoicenum;
	var $Invoiceamount;
	var $Invoicedate;
	var $Taxamount;
    var $AlreadyReceiveamount;
	var $Waitamount;
	var $Receiveamount; 
	var $LineNarrative;
	var $LineNumber;
	function LineDetails($Invoicenum,
					 $Invoiceamount,
		             $Invoicedate,
		             $Taxamount,
                     $AlreadyReceiveamount,
		             $Waitamount,	
		             $Receiveamount, 
		             $LineNarrative,
					 $LineNumber) {

		$this->LineNumber=$LineNumber;
		$this->Invoicenum=$Invoicenum;
		$this->Invoiceamount=$Invoiceamount;
		$this->Invoicedate=$Invoicedate;
		$this->Taxamount=$Taxamount;
		$this->AlreadyReceiveamount=$AlreadyReceiveamount; 
		$this->Waitamount=$Waitamount; 
		$this->Receiveamount=$Receiveamount;  
		$this->LineNarrative=$LineNarrative; 
	}

}

?>