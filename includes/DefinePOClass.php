<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DefineCompactClass
 *
 * @author sheng_lu
 */
class CompactHeader {
    //put your code here
    public $compact_code;
    public $sales_man;
    public $vendor_code;
    public $vendor_name;
    public $Subinventory_code;
    public $status;
    public $description;
    public $effective_date;
    public $disable_date;
//    public $created_by;
//    public $creation_date;
//    public $last_updated_by;
//    public $last_update_date;
    public $amount;
    //下面的属性为合同的line
    public $LineItems;
    public $line_counter;
            
    function CompactHeader(){
        $this->LineItems = array();
        $this->vendor_code = '';
        $this->status = 0;
        $this->line_counter = 1;
    }
    function AddLine($StockID,
							    $ItemDescription,
							    $Quantity,
							    $POPrice,
							    $UOM,
                                                            $amount
                                                            ){

		if (isset($StockID) AND $Quantity!=0){
			$this->LineItems[$this->line_counter] = new CompactLine($this->line_counter,
																	$StockID,
																	$ItemDescription,
																	$Quantity,
																	$POPrice,
																	$UOM,
//                                                                                                                                        $DecimalPlaces,
																	
                                                                                                                                        $amount);
			$this->line_counter++;
			Return 1;
		}
		Return 0;
	}
        
        function AddLineToSelect(    $Linenum,$StockID,
							    $ItemDescription,
							    $Quantity,
							    $POPrice,
							    $UOM,
                                                            $amount){

		if (isset($StockID) AND $Quantity!=0){
			$this->LineItems[$Linenum] = new CompactLine($Linenum,
																	$StockID,
																	$ItemDescription,
																	$Quantity,
																	$POPrice,
																	$UOM,
//                                                                   $DecimalPlaces,
																	
                                                                        $amount);
			Return 1;
		}
		Return 0;
	}

	function Remove_ContractComponent($line_id){
		global $db;
		$result = DB_query("DELETE FROM compactline
											WHERE compact_code='" . $this->vendor_code . "'
											AND stock_id='" . $this->compact_line[$line_id]->StockID . "'",
											$db);
		unset($this->compact_line[$line_id]);
	}
}

Class CompactLine {
        
    public $LineNumber;
    public $StockID;
    public $ItemDescription;
    public $Quantity;
    public $POPrice;
    public $UOM;
    public $Type;
    public $amount;
    public $DecimalPlaces;
    
     
    function CompactLine ($line_code,
                                                            $StockID,
                                                            $ItemDescription,
                                                            $Quantity,
                                                            $POPrice,
                                                            $UOM,
                                                            $amount
           ){

/* Constructor function to add a new Contract Component object with passed params */
            $this->LineNumber = $line_code;
            $this->StockID = $StockID;
            $this->ItemDescription = $ItemDescription;
            $this->Quantity = $Quantity;
            $this->POPrice= $POPrice;
            $this->UOM = $UOM;
            $this->amount=$amount;
//            $this->DecimalPlaces=$DecimalPlaces;
//            $this->Type = $Type;

    }
}