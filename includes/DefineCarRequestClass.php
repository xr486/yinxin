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
class CarRequestHeader {
    //put your code here
    public $CarHeaderCode;
    public $CustomerCode;
    public $CustomerName;
    public $DriverNo;
//    public $DriverName;
    public $CarNo;
    public $Address;
    public $ScheduleDate;
    public $ActualDate;
    public $Distance;
    public $Description;
    public $Hours;
    public $Min;
//    public $created_by;
//    public $creation_date;
//    public $last_updated_by;
//    public $last_update_date;
//    
    //下面的属性为合同的line
    public $LineItems;
    public $line_counter;
            
    function CarRequestHeader(){
        $this->LineItems = array();
        $this->line_counter = 1;
    }
    function AddLine( $RefTransferID,
                       $RefTransferCode,
                                                            $StockID,
							    $ItemDescription,
							    $Quantity,
							    $ItemCost,
							    $UOM,
                                                            $DecimalPlaces,
                                                            $Type){

		if (isset($StockID) AND $Quantity!=0){
			$this->LineItems[$this->line_counter] = new CarRequestLine($this->line_counter,$RefTransferID,$RefTransferCode,
																	$StockID,
																	$ItemDescription,
																	$Quantity,
																	$ItemCost,
																	$UOM,
                                                                                                                                        $DecimalPlaces,
																	$Type);
			$this->line_counter++;
			Return 1;
		}
		Return 0;
	}
}

Class CarRequestLine {
        
    public $LineNumber;
    public $RefTransferID;
    public $RefTransferCode;
    public $StockID;
    public $ItemDescription;
    public $Quantity;
    public $ItemCost;
    public $UOM;
    public $Type;
    public $DecimalPlaces;
                    function CarRequestLine ($line_code,
                                                            $RefTransferID,
                                                            $RefTransferCode,
                                                            $StockID,
                                                            $ItemDescription,
                                                            $Quantity,
                                                            $ItemCost,
                                                            $UOM,
                                                            $DecimalPlaces,
                                                            $Type){

/* Constructor function to add a new Contract Component object with passed params */
            $this->LineNumber = $line_code;
            $this->RefTransferID = $RefTransferID;
            $this->RefTransferCode = $RefTransferCode;
            $this->StockID = $StockID;
            $this->ItemDescription = $ItemDescription;
            $this->Quantity = $Quantity;
            $this->ItemCost= $ItemCost;
            $this->UOM = $UOM;
            $this->Type = $Type;
            $this->DecimalPlaces=$DecimalPlaces;
    }
}