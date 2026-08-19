<?php

Class ShipmentRequest {
    var $Customer_id;	
    var $Customer_code;	
    var $Customer_name;
	var $Delivery_num;
	var $ShipmentAmount;
	var $Tracking_Number;	
	var $ShipInvoiceNum;
	var $Trackingcompany;
	var $Narrative;
	var $LineCounter=1;
	var $LineItems;

	function ShipmentRequest(){
	/*Constructor function initialises a new shopping cart */
		$this->LineItems=array();
	}
 	
	function AddLine($so_num,
					 $Line, 
		             $DeliveryQuantity,
					 $Order_Quantity,  
                     $Quantity_shiped,
		             $WaitQuantity, 
		             $Stockid,
		             $Subinventory_Code,		  
					 $Onhand_Quantity,
		             $LineNarrative,
					 $LineNumber=-1) {

		if ($LineNumber==-1){
			$LineNumber = $this->LineCounter;
		}
		$this->LineItems[$LineNumber]=new LineDetails(
		             $so_num,
					 $Line, 
			         $DeliveryQuantity,
					 $Order_Quantity, 
                     $Quantity_shiped,
		             $WaitQuantity,
			         $Stockid,
			         $Subinventory_Code,		  
					 $Onhand_Quantity,
		             $LineNarrative,
					 $LineNumber);
		$this->LineCounter = $LineNumber + 1;
	}
}

Class LineDetails { 
	var $so_num;
	var $Line; 
	var $DeliveryQuantity;
	var $Order_Quantity; 
    var $Quantity_shiped;
	var $WaitQuantity;
	var $Stockid;
	var $Subinventory_Code;	  
	var $Onhand_Quantity;	
	var $LineNarrative;
	var $LineNumber;
	function LineDetails($so_num,
					 $Line, 
		             $DeliveryQuantity,
					 $Order_Quantity, 
                     $Quantity_shiped,
		             $WaitQuantity,	
		             $Stockid,
		             $Subinventory_Code,		  
					 $Onhand_Quantity,	             
		             $LineNarrative,
					 $LineNumber) { 
		$this->LineNumber=$LineNumber;
		$this->so_num=$so_num;
		$this->Line=$Line;  
		$this->DeliveryQuantity=$DeliveryQuantity;
		$this->Order_Quantity=$Order_Quantity; 
		$this->Quantity_shiped=$Quantity_shiped; 
		$this->WaitQuantity=$WaitQuantity;
		$this->Stockid=$Stockid;	  
		$this->Subinventory_Code=$Subinventory_Code;	  
		$this->Onhand_Quantity=$Onhand_Quantity; 		
		$this->LineNarrative=$LineNarrative; 
	}

}

?>