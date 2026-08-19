<?php

Class InvoiceRequest {

	
	var $Supplierid;
	var $Suppname;
	var $InvoiceDate;
	var $InvoiceAmount;	
	var $TaxAmount;	
	var $PaymentAmount;	
	var $BankName;
	var $Narrative;
	var $LineCounter=1;
	var $LineItems;

	function InvoiceRequest(){
	/*Constructor function initialises a new shopping cart */
		$this->InvoiceDate = date($_SESSION['DefaultDateFormat']);
		$this->LineItems=array();
	}

	function AddLine($OrderNumber,
					 $ReceiveDate,
		             $ItemName,
		             $PurchaseAmount,
                     $PaymentVendorAmount,
					 $Amount,
		             $LineNarrative,
					 $LineNumber=-1) {

		if ($LineNumber==-1){
			$LineNumber = $this->LineCounter;
		}
		$this->LineItems[$LineNumber]=new LineDetails($OrderNumber,
						                             $ReceiveDate,
			                                         $ItemName,
			                                         $PurchaseAmount,
			                                         $PaymentVendorAmount,
					                                 $Amount, 
			                                         $LineNarrative,
						                             $LineNumber);
		$this->LineCounter = $LineNumber + 1;
	}
}

Class LineDetails {
	var $OrderNumber;
	var $ReceiveDate;
	var $ItemName;
	var $PurchaseAmount;
	var $PaymentVendorAmount;
	var $Amount;
	var $LineNarrative;
	var $LineNumber;    
	function LineDetails($OrderNumber,
						$ReceiveDate,
		                $ItemName,
		                $PurchaseAmount,
		                $PaymentVendorAmount,
						$Amount, 
		                $LineNarrative,
						$LineNumber) {

		$this->LineNumber=$LineNumber;
		$this->OrderNumber=$OrderNumber;
		$this->ReceiveDate=$ReceiveDate;
		$this->ItemName=$ItemName;
		$this->PurchaseAmount=$PurchaseAmount;
		$this->PaymentVendorAmount=$PaymentVendorAmount; 
		$this->Amount=$Amount; 
		$this->LineNarrative=$LineNarrative; 
	}

}

?>