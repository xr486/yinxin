<?php

include('includes/session.inc');
$Title = _('采购成衣入库');

$ViewTopic= '采购成衣入库';
$BookMark = '采购成衣入库';
include('includes/SQL_CommonFunctions.inc');
 
 if(isset($_GET['order_number'])&&isset($_GET['line'])){
      $arr = array();
      // $sql = "select l.component_item,l.component_quantity,l.uom,i.unit_price,i.item_desc,i.item_spec
      //         from bom_headers_all h,bom_lines_all l,sf_item_no i 
      //         where h.assembly_item_no='" .$_GET['item_id']. "' and l.assembly_item_no=h.assembly_item_no and i.item_no=l.component_item
      //        ";
      $sql = "select item_no as component_item,item_name as item_desc,item_description as item_spec,uom,userd_per_quantity as component_quantity,pirce as unit_price
              from so_line_details_all
              where  order_number='".$_GET['order_number']."'
                 and line = '".$_GET['line']."'
              ";

      $result_num = DB_query($sql, $db);
      $rownum = DB_num_rows($result_num);
      if($rownum>0){
         while($row = DB_fetch_array($result_num,$db)){
            $arr[] = $row;
         }
      }
      $str = json_encode($arr); 
      echo $str;   
 }

 



?>