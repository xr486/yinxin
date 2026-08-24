<?php

Class InvoiceRequest {

	var $InvoiceDate;
	var $SchedulePaymentDate;
	var $vendor_code;
	var $InvoiceAmount;	
	var $TaxAmount;	
	var $Narrative;
	var $LineCounter=1;
	var $LineItems;

	function InvoiceRequest(){
	/*Constructor function initialises a new shopping cart */
		$this->InvoiceDate = date($_SESSION['DefaultDateFormat']);
		$this->SchedulePaymentDate = date($_SESSION['DefaultDateFormat']);
		$this->LineItems=array();
	}

	function AddLine($PO_NUM,
					 $PO_LINE,
		             $ItemNo,
		             $DeliverQuantity,
                     $AlreadyBilledQuantity,
		             $Price,	
		             $BilledQuantity,
					 $BilledAmount,
		             $LineNarrative,
					 $LineNumber=-1) {

		if ($LineNumber==-1){
			$LineNumber = $this->LineCounter;
		}
		$this->LineItems[$LineNumber]=new LineDetails($PO_NUM,
					 $PO_LINE,
		             $ItemNo,
		             $DeliverQuantity,
                     $AlreadyBilledQuantity, 
		             $Price,
					 $BilledQuantity,
					 $BilledAmount,
			         $LineNarrative,
					 $LineNumber);
		$this->LineCounter = $LineNumber + 1;
	}
}

Class LineDetails {
	var $PO_NUM;
	var $PO_LINE;
	var $ItemNo;
	var $DeliverQuantity;
	var $AlreadyBilledQuantity;
	var $Price;
	var $BilledQuantity;
	var $BilledAmount;
	var $LineNarrative;
	var $LineNumber;    
	function LineDetails($PO_NUM,
						$PO_LINE,
		                $ItemNo,
		                $DeliverQuantity,
		                $AlreadyBilledQuantity,
						$Price, 
		                $BilledQuantity,
		                $BilledAmount, 
		                $LineNarrative,
						$LineNumber) {

		$this->LineNumber=$LineNumber;
		$this->PO_NUM=$PO_NUM;
		$this->PO_LINE=$PO_LINE;
		$this->ItemNo=$ItemNo;
		$this->DeliverQuantity=$DeliverQuantity;
		$this->AlreadyBilledQuantity=$AlreadyBilledQuantity; 
		$this->Price=$Price; 
		$this->BilledQuantity=$BilledQuantity;
		$this->BilledAmount=$BilledAmount; 
		$this->LineNarrative=$LineNarrative; 
	}

}

?>