<?php

/* $Id: DefinePOClass.php 5857 2013-04-27 22:19:01Z daintree $ */
/* Definition of the PROrder class to hold all the information for a purchase order and delivery
 */

Class PROrder {

    var $LineItems; /* array of objects of class LineDetails using the product id as the pointer */
    var $Description;
    var $pr;
    var $pr_line;
    var $quantity;
    var $Item;
    var $status;

    function PROrder() {
        /* Constructor function initialises a new purchase order object */
        $this->LineItems = array();
    }

    function add_to_order($LineNo,
                            $Item, 
                            $PrNum, 
                            $pr_line, 
                            $Description, 
                            $quantity, 
                            $status
    ) {

        if (isset($status)) {

            $this->LineItems[$LineNo] = new LineDetails($LineNo, 
                                                        $Item, 
                                                        $PrNum, 
                                                        $pr_line, 
                                                        $Description, 
                                                        $quantity, 
                                                        $status
            );
            $this->LinesOnOrder++;
            Return 1;
        }
        Return 0;
    }

    function update_order_item(
                                 $LineNo, 
                                 $Item, 
                                 $PrNum, 
                                 $pr_line, 
                                 $Description, 
                                 $quantity, 
                                 $status) {

        $this->LineItems[$LineNo]->ItemDescription = $Item;
        $this->LineItems[$LineNo]->Quantity = $PrNum;
        $this->LineItems[$LineNo]->Price = $pr_line;
        $this->LineItems[$LineNo]->GLCode = $Description;
        $this->LineItems[$LineNo]->GLAccountName = $quantity;
        $this->LineItems[$LineNo]->ReqDelDate = $status;
    }
}
    Class LineDetails {
        /* PROrderDetails */
        Var $LineNo;
        Var $Item;
        Var $PrNum; 
        Var $pr_line;
        Var $Description;
        Var $quantity;
        Var $status;
        

        function LineDetails($LineNo, $Item, $PrNum, $pr_line, $Description, $quantity, $status ) {

            /* Constructor function to add a new LineDetail object with passed params */
            $this->LineNo = $LineNo;
            $this->Item = $Item;
            $this->PrNum = $PrNum;
            $this->pr_line = $pr_line;
            $this->Description = $Description;
            $this->quantity = $quantity;
            $this->status = $status;
        }

    }

?>