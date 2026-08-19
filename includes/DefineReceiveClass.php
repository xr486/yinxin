<?php

Class ReceiveRequest {

	var $ReceiveNum;
	var $ReceiveDate;
	var $BankName;
	var $customer_code;
    var $customer_id;        
	var $customer_name;
	var $ReceiveAmount;	
	var $TaxAmount;	
	var $Narrative;
	var $LineCounter=1;
	var $LineItems;

	function ReceiveRequest(){
	/*Constructor function initialises a new shopping cart */
		$this->ReceiveDate = date($_SESSION['DefaultDateFormat']);
		$this->LineItems=array();
	}

	function AddLine($InvoiceNum,
		             $InvoiceDate,
			         $SchedulePaymentDate,
		             $InvoiceAmount,
                     $AlreadyReceiveAmount,
		             $WaitReceiveAmount,
					 $Amount,
		             $LineNarrative,
					 $LineNumber=-1) {

		if ($LineNumber==-1){
			$LineNumber = $this->LineCounter;
		}
		$this->LineItems[$LineNumber]=new LineDetails($InvoiceNum,
			                                          $InvoiceDate,
			                                          $SchedulePaymentDate,
			                                          $InvoiceAmount,
                                                      $AlreadyReceiveAmount,
		                                              $WaitReceiveAmount,
					                                 $Amount, 
			                                         $LineNarrative,
						                             $LineNumber);
		$this->LineCounter = $LineNumber + 1;
	}
}

Class LineDetails {
	var $InvoiceNum;
	var $InvoiceDate;
	var $SchedulePaymentDate;
	var $InvoiceAmount;
	var $AlreadyReceiveAmount;
	var $WaitReceiveAmount;
	var $Amount;
	var $LineNarrative;
	var $LineNumber;    
	function LineDetails($InvoiceNum,
		                 $InvoiceDate,
			            $SchedulePaymentDate,
						$InvoiceAmount,
		                $AlreadyReceiveAmount,
		                $WaitReceiveAmount,
						$Amount, 
		                $LineNarrative,
						$LineNumber) {

		$this->LineNumber=$LineNumber;
		$this->InvoiceNum=$InvoiceNum;
		$this->InvoiceDate=$InvoiceDate;
		$this->SchedulePaymentDate=$SchedulePaymentDate;
		$this->InvoiceAmount=$InvoiceAmount;
		$this->AlreadyReceiveAmount=$AlreadyReceiveAmount;
		$this->WaitReceiveAmount=$WaitReceiveAmount;
		$this->Amount=$Amount; 
		$this->LineNarrative=$LineNarrative; 
	}

}

?>