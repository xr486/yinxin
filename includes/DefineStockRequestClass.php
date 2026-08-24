<?php

Class StockRequest {

    var $LineItems; /* array of objects of class LineDetails using the product id as the pointer */
    var $Need_date;
    var $Location;
    var $Department;
    var $Narrative;
    var $LineCounter = 1;
    var $status;
    var $pr;

    function StockRequest() {
        /* Constructor function initialises a new shopping cart */

        $this->LineItems = array();
    }

    function AddLine($StockID, $ItemDescription, $Quantity, $UOM, $LineNumber = -1) {

        if ($LineNumber == -1) {
            $LineNumber = $this->LineCounter;
        }
        $this->LineItems[$LineNumber] = new LineDetails($StockID, $ItemDescription, $Quantity, $UOM, $LineNumber);
        $this->LineCounter = $LineNumber + 1;
    }

}

Class LineDetails {

    var $StockID;
    var $ItemDescription;
    var $Quantity;
    var $UOM;
    var $LineNumber;

    function LineDetails($StockID, $ItemDescription, $Quantity, $UOM, $LineNumber) {

        $this->LineNumber = $LineNumber;
        $this->StockID = $StockID;
        $this->ItemDescription = $ItemDescription;
        $this->Quantity = $Quantity;
        $this->UOM = $UOM;
    }

}

?>