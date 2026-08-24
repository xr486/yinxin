<?php
Class OrderHearder {

	var $LineItems; /*array of objects of class LineDetails using the product id as the pointer */
	var $OrderId;
	var $OrderNum;
	var $OrderMan;
	var $ManContact;
	var $Address;
	var $ScheduleDate;
	var $LineCounter=0;

	function OrderHearder(){
	/*Constructor function initialises a new shopping cart */
		$this->OrderNum = date('Ymd');
		$this->LineItems=array();
	}

	function AddLine($StockID,
					$ItemDescription,
					$Quantity,
					$UOM,
					$DecimalPlaces,
					$LineNumber=-1) {

		if ($LineNumber==-1){
			$LineNumber = $this->LineCounter;
		}
		$this->LineItems[$LineNumber]=new LineDetails($StockID,
												$ItemDescription,
												$Quantity,
												$UOM,
												$DecimalPlaces,
												$LineNumber);
		$this->LineCounter = $LineNumber + 1;
	}
}

Class LineDetails {
	var $StockID;
	var $ItemDescription;
	var $Quantity;
	var $UOM;
	var $LineNumber;

	function LineDetails($StockID,
						$ItemDescription,
						$Quantity,
						$UOM,
						$DecimalPlaces,
						$LineNumber) {

		$this->LineNumber=$LineNumber;
		$this->StockID=$StockID;
		$this->ItemDescription=$ItemDescription;
		$this->Quantity=$Quantity;
		$this->DecimalPlaces=$DecimalPlaces;
		$this->UOM=$UOM;
	}

}

?>