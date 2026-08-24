<?php

Class StockRequest {

	var $LineItems; /*array of objects of class LineDetails using the product id as the pointer */
	var $Need_date;
	var $loccode;
	var $vendor_code;
	var $Narrative;
	var $LineCounter=1;
        var $pr;

	function StockRequest(){
		
		$this->LineItems=array();
	}

	function AddLine($StockID,
                        $ItemDescription,
		                $UOM,
		                $price,
                        $Quantity,                        
                        $MRPQTY,
                        $LineNumber=-1) {

		if ($LineNumber==-1){
			$LineNumber = $this->LineCounter;
		}
            $this->LineItems[$LineNumber]=new LineDetails($StockID,
                                                        $ItemDescription,
				                                        $UOM,
		                                                $price,
                                                        $Quantity, 
                                                        $MRPQTY,
                                                        $LineNumber);
		$this->LineCounter = $LineNumber + 1;
	}
}

Class LineDetails {
	var $StockID;
	var $ItemDescription;
	var $UOM;
	var	$price;
	var $Quantity; 
    var $MRPQTY;
	var $LineNumber;

	function LineDetails($StockID,
						$ItemDescription,
		                $UOM,
		                $price,
						$Quantity,						
                        $MRPQTY,
						$LineNumber) {

		$this->LineNumber=$LineNumber;
		$this->StockID=$StockID;
		$this->ItemDescription=$ItemDescription;
		$this->UOM=$UOM;
		$this->price=$price;
		$this->Quantity=$Quantity;		
        $this->MRPQTY=$MRPQTY;
	}

}

?>