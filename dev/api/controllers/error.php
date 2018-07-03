<?php
  class Error {
   public function __construct() {
    header('Error', true, 404);
    echo json_encode(array("message"=>"Method not found!"));
   }
  }
?>