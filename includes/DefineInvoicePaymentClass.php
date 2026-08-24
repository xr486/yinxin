<?php

Class InvoiceRequest {

	var $PaymentDate;
	var $PaymentNum;
	var $Vendor_code;
	var $Vendor_name;
	var $PaymentAmount;	
	var $TaxAmount;	
	var $Narrative;
	var $LineCounter=1;
	var $LineItems;

	function InvoiceRequest(){
	/*Constructor function initialises a new shopping cart */
		$this->PaymentDate = date($_SESSION['DefaultDateFormat']);
		$this->LineItems=array();
	}

	function AddLine($Invoicenum,
					 $Invoiceamount,
		             $Invoicedate,
		             $Waitamount,
		             $Alreadypaymentamount,
		             $Paymentamount,
                     $Taxamount, 
		             $LineNarrative,
					 $LineNumber=-1) {

		if ($LineNumber==-1){
			$LineNumber = $this->LineCounter;
		}
		$this->LineItems[$LineNumber]=new LineDetails($Invoicenum,
					 $Invoiceamount,
			         $Invoicedate,
			         $Waitamount,
		             $Alreadypaymentamount,
		             $Paymentamount,
                     $Taxamount,  
			         $LineNarrative,
					 $LineNumber);
		$this->LineCounter = $LineNumber + 1;
	}
}

Class LineDetails {
	var $Invoicenum;
	var $Invoiceamount;
	var $Invoicedate;
	var $Waitamount;
	var $Alreadypaymentamount;
	var $Paymentamount;
	var $Taxamount; 
	var $LineNarrative;
	var $LineNumber;    
	function LineDetails($Invoicenum,
						$Invoiceamount,
		                $Invoicedate,
		                $Waitamount,
		                $Alreadypaymentamount,
		                $Paymentamount,
		                $Taxamount, 
		                $LineNarrative,
						$LineNumber) {

		$this->LineNumber=$LineNumber;
		$this->Invoicenum=$Invoicenum;
		$this->Invoiceamount=$Invoiceamount;
		$this->Invoicedate=$Invoicedate;
		$this->Waitamount=$Waitamount;
		$this->Alreadypaymentamount=$Alreadypaymentamount;
		$this->Paymentamount=$Paymentamount;
		$this->Taxamount=$Taxamount;  
		$this->LineNarrative=$LineNarrative; 
	}

}

?>